<?php

require_once ITA_BASE_PATH . '/lib/itaPHPCore/itaCrypt.class.php';

class praNews extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    public $PROT_DB;
    private $rsnAuth;
    private $uploadError;

    public function __construct() {
        parent::__construct();

        try {
            if (file_exists(ITA_RSN_PATH . '/rsnAuth.php')) {
                require_once ITA_RSN_PATH . '/rsnAuth.php';
                $this->rsnAuth = new rsnAuth();
            }

            $this->uploadError = $_SESSION['praNewsUploadError'] ?: $this->uploadError;

            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->PROT_DB = ItaDB::DBOpen('PROT', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function __destruct() {
        $_SESSION['praNewsUploadError'] = $this->uploadError;
    }

    private function disegnaUploadError() {
        $html = new html;

        if ($this->uploadError) {
            $html->addAlert($this->uploadError, '', 'error');
        }

        $this->uploadError = null;

        return $html->getHtml();
    }

    public function parseEvent() {
        if ($this->rsnAuth) {
            $this->rsnAuth->parseEvent();
        }

        switch ($this->request['event']) {
            case "gestioneAllegato":
                if (isset($this->request['infoAllegato'])) {
                    $infoAllegato = json_decode(itaCrypt::decrypt($this->request['infoAllegato']), true);
                    $this->request = array_merge($this->request, $infoAllegato);

                    if (!$this->GestioneAllegato($this->request['fileId'])) {
                        output::addAlert('Allegato non disponibile.');
                        break;
                    }
                    break;
                }

                $this->request['fileId'] = frontOfficeApp::decrypt($this->request['fileIdE']);

                if (!$this->request['fileId']) {
                    output::addAlert('Allegato non disponibile.');
                    break;
                }

                if (!$this->GestioneAllegato($this->request['fileId'])) {
                    output::addAlert('Allegato non disponibile.');
                }
                break;

            case 'verificaSHA':
                $infoUpload = json_decode(itaCrypt::decrypt($this->request['infoUpload']), true);

                $idAllegato = $infoUpload['idAllegato'];
                $verificaAllegato = $_FILES['verificaAllegato'];

                $resultMessage = '';

                $sql = "SELECT PASKEY, PASSHA2 FROM PASDOC WHERE ROWID = '" . addslashes($idAllegato) . "'";
                $Pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                if (!$Pasdoc_rec) {
                    output::addAlert('Articolo non disponibile.');
                    break;
                }

                if (!$verificaAllegato['name']) {
                    $resultMessage = 'Richiesta non valida';

                    $dettaglioHref = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'dettaglioArticolo',
                                'ID' => $Pasdoc_rec['PASKEY']
                    ));

                    header('Location: ' . $dettaglioHref);
                    break;
                }

                if ($verificaAllegato['error'] !== UPLOAD_ERR_OK) {
                    $resultMessage = 'Errore durante l\'upload del file.';

                    switch ($verificaAllegato['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                            $max_size = ini_get('upload_max_filesize');
                            $resultMessage .= " Le dimensioni del file superano il valore massimo consentito di $max_size.";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $resultMessage .= ' Nessun file selezionato';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $resultMessage .= ' Cartella temporanea mancante.';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $resultMessage .= ' Errore di scrittura sul disco.';
                            break;
                    }

                    $dettaglioHref = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'dettaglioArticolo',
                                'ID' => $Pasdoc_rec['PASKEY']
                    ));

                    header('Location: ' . $dettaglioHref);
                    break;
                }

                $sha2file = hash_file('sha256', $verificaAllegato['tmp_name']);

                @unlink($verificaAllegato['tmp_name']);

                if ($sha2file !== $Pasdoc_rec['PASSHA2']) {
                    $resultMessage = 'Lo SHA del file caricato non corrisponde con quello dell\'allegato.';
                }

                $_SESSION[__CLASS__ . 'VerificaSHA'] = $resultMessage;

                $dettaglioHref = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'dettaglioArticolo',
                            'ID' => $Pasdoc_rec['PASKEY']
                ));

                header('Location: ' . $dettaglioHref);
                break;

            case "dettaglioArticolo":
                if (!$this->showDetails($this->request['ID'])) {
                    output::addAlert('Articolo non disponibile.');
                }
                break;

            case 'uploadAllegatoFirmato':
                $infoUpload = json_decode(itaCrypt::decrypt($this->request['infoUpload']), true);

                $this->caricaAllegatoFirmato($infoUpload);

                $dettaglioHref = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'dettaglioArticolo',
                            'ID' => $infoUpload['chiavePasso']
                ));

                header('Location: ' . $dettaglioHref);
                die;

            case "scaricaZipAllegati":
                $pramPath = ITA_PRATICHE . substr($this->request['propak'], 0, 4) . "/PASSO/" . $this->request['propak'];

                /*
                 * Se il file zip esiste, lo cancello
                 */
                $fileZip = ITA_FRONTOFFICE_TEMP . "allegati_" . $this->request['propak'] . ".zip";
                if (file_exists($fileZip)) {
                    if (!@unlink($fileZip)) {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0074', "Errore cancellazione file zip precedente: $fileZip", __CLASS__);
                    }
                }

                /*
                 * Creo il file zip
                 */
                $archiv = new ZipArchive();
                if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0072', "Errore creazione file zip: $fileZip", __CLASS__);
                }

                /*
                 * Mi trovo gli allegati del passo e li aggiungo allo zip
                 */
                $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . addslashes($this->request['propak']) . "' AND PASRIS = 0 ORDER BY ROWID"; //PASFIL";
                $dataDetail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                if ($dataDetail_tab) {
                    $repConnector = new praRep(ITA_PRATICHE);
                    $dataDetail_tab = $this->ControlloXHTML($dataDetail_tab, $this->request['propak']);
                    foreach ($dataDetail_tab as $key => $dataDetail_rec) {
                        $fileString = $repConnector->getFile($pramPath . "/" . $dataDetail_rec['PASFIL'], "", false, true);
                        if ($fileString === false) {
                            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0073', $repConnector->getErrorMessage(), __CLASS__);
                        } else {
                            if ($dataDetail_rec['PASNAME']) {
                                $fileOrig = $dataDetail_rec['PASNAME'];
                            } else {
                                $fileOrig = $dataDetail_rec['PASFIL'];
                            }
                            $archiv->addFromString($fileOrig, $fileString);
                        }
                    }
                }
                $archiv->close();

                /*
                 * Eseguo il downlaod el file
                 */
                $this->frontOfficeLib->scaricaFile($fileZip, $fileZip, false);

                /*
                 * Cancello lo zip dopo il download
                 */
                if (!@unlink($fileZip)) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0073', "Errore cancellazione file zip: $fileZip", __CLASS__);
                }
                exit();
                break;

            default:
                $news = $this->getNewsFromBo();
                output::appendHtml("<div>");
                foreach ($news as $key => $article) {
                    $href = ItaUrlUtil::GetPageUrl(array("event" => "dettaglioArticolo", 'ID' => $article['ID']));
                    $desc_node = "<a class=\"Titolo\" href=\"$href\">" . $article['TITOLO'] . "</a>";
                    output::appendHtml("<span>$desc_node</span><br>");
                    $dataInizio = mktime(0, 0, 0, substr($article['DATA'], 4, 2), substr($article['DATA'], -2), substr($article['DATA'], 0, 4));
                    output::appendHtml("<span>" . date("j F Y", $dataInizio) . " - " . $article['AUTORE'] . "</span><br><br>");
                    output::appendHtml("<span>" . $article['CONTENUTO'] . "</span><br>");
//                    $nomiAllegati = "";
//                    foreach ($article['ALLEGATI'] as $keyAlle => $allegato) {
//                        $nomiAllegati = $nomiAllegati . " " . $article['ALLEGATI'][$keyAlle];
//                    }
//                    if ($nomiAllegati) {
                    if ($article['ALLEGATI']) {
                        //output::appendHtml("<div class=\"divAllegati\">Allegati: $nomiAllegati</div>");
                        output::appendHtml("<div class=\"divAllegati\">Sono presenti " . count($article['ALLEGATI']) . ".<br>Per visualizzarli cliccare nel titolo dell'articolo.</div>");
                    }
                    output::appendHtml("<div class=\"divAllegati\">Classificazioni: " . $article['CLASSIFICAZIONE'] . "</div><br>");
                }
                output::appendHtml("</div>");
                break;

            case 'chiudiConferenzaServizi':
                $info = json_decode(itaCrypt::decrypt($this->request['info']), true);

                $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO = '" . addslashes($info['chiavePasso']) . "' ORDER BY SEQUENZA DESC";
                $pramitdest_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . addslashes($info['chiavePasso']) . "' AND PASFLCDS = '1'";
                $pasdoc_orig_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

                $pasdoc_firm_tab = array();

                foreach ($pasdoc_orig_tab as $k => $pasdoc_orig_rec) {
                    $pasdoc_firm_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE PASROWIDBASE = '{$pasdoc_orig_rec['ROWID']}' AND PASUTELOG = 'PRAMITTDEST:{$pramitdest_rec['ROWID']}'", false);

                    if (!$pasdoc_firm_rec) {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0078', "Impossibile effettuare la chiusura in quanto non sono state applicate tutte le firme", __CLASS__);
                    }

                    $pasdoc_firm_tab[$k] = $pasdoc_firm_rec;
                }

                foreach ($pasdoc_orig_tab as $k => $pasdoc_orig_rec) {
                    $pasdoc_firm_rec = $pasdoc_firm_tab[$k];

                    $filename = strtolower(pathinfo($pasdoc_orig_rec['PASFIL'], PATHINFO_FILENAME));
                    $extension = strtolower(pathinfo($pasdoc_orig_rec['PASFIL'], PATHINFO_EXTENSION));

                    $pramPathTmp = explode('://', ITA_PRATICHE);
                    $pramPath = end($pramPathTmp) . substr($pasdoc_orig_rec['PASKEY'], 0, 4) . '/PASSO/' . $pasdoc_orig_rec['PASKEY'];

                    if (in_array($extension, array('xhtml', 'docx'))) {
                        if (file_exists("$pramPath/$filename.pdf")) {
                            $extension = 'pdf';
                        }
                    }

                    copy($pramPath . '/' . $pasdoc_firm_rec['PASFIL'], "$pramPath/$filename.$extension.p7m");

                    $pasfil_orig = $pasdoc_orig_rec['PASFIL'];

                    $pasdoc_orig_rec['PASFIL'] = "$filename.$extension.p7m";
                    $pasdoc_orig_rec['PASLNK'] = "allegato://$filename.$extension.p7m";
                    $pasdoc_orig_rec['PASNAME'] .= '.p7m';
                    $pasdoc_orig_rec['PASDATAFIRMA'] = date('Ymd');
                    $pasdoc_orig_rec['PASTIPO'] = 'FIR_CDS_DEFINITIVO';

                    if (!ItaDB::DBUpdate($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_orig_rec)) {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0075', "Errore aggiornamento allegato '{$pasdoc_orig_rec['ROWID']}'", __CLASS__);
                    }

                    @unlink("$pramPath/$pasfil_orig");

                    /*
                     * Cancellazione allegati firmatari
                     */

                    $pasdoc_firm_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE PASROWIDBASE = '{$pasdoc_orig_rec['ROWID']}'");
                    foreach ($pasdoc_firm_tab as $pasdoc_firm_rec) {
                        if (!ItaDB::DBDelete($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_firm_rec['ROWID'])) {
                            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0076', "Errore cancellazione record allegato '{$pasdoc_firm_rec['ROWID']}'", __CLASS__);
                        }

                        if (!unlink("$pramPath/{$pasdoc_firm_rec['PASFIL']}")) {
                            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0077', "Errore cancellazione file allegato '$pramPath/{$pasdoc_firm_rec['PASFIL']}'", __CLASS__);
                        }
                    }
                }

                header('Location: ' . ItaUrlUtil::GetPageUrl(array('event' => 'dettaglioArticolo', 'ID' => $info['chiavePasso'])));
                die;
                break;

            case 'onClick':
                switch ($this->request['id']) {
                    case 'firma':
                        if (!$this->rsnAuth) {
                            break;
                        }

                        $arrayFirma = json_decode(itaCrypt::decrypt($this->request['input']), true);
                        foreach ($arrayFirma as $k => $allegato) {
                            $tempPath = ITA_FRONTOFFICE_TEMP . basename($allegato['INPUTFILEPATH']);
                            copy($allegato['INPUTFILEPATH'], $tempPath);
                            $arrayFirma[$k]['INPUTFILEPATH'] = $tempPath;
                        }

                        $this->rsnAuth->setAllegati($arrayFirma);

                        output::ajaxResponseDialog($this->rsnAuth->disegnaFormFirma(), array(
                            'title' => 'Firma remota',
                            'width' => 600
                        ));

                        output::ajaxSendResponse();
                        break;
                }
                break;

            case 'onSubmit':
                if (!$this->rsnAuth) {
                    break;
                }

                $result = $this->rsnAuth->firma(
                        $this->request['otpauth'], $this->request['otppass'], $this->request['utente'], $this->request['password']
                );

                if (!$result) {
                    $html = new html();

                    output::ajaxResponseDialog($html->getAlert($this->rsnAuth->getErrMessage(), '', 'error'), array(
                        'title' => 'Esito firma',
                        'width' => 400
                    ));
                } else {
                    $allegati = $this->rsnAuth->getAllegati();

                    foreach ($allegati as $allegato) {
                        $basename = basename($allegato['FILEFIRMATO'], '.p7m');
                        $filename = strtolower(pathinfo($basename, PATHINFO_FILENAME));
                        $extension = strtolower(pathinfo($basename, PATHINFO_EXTENSION));

                        $this->caricaAllegatoFirmato(array(
                            'idAllegato' => $allegato['IDALLEGATO'],
                            'idDestinatario' => $allegato['IDDESTINATARIO']
                                ), array(
                            'name' => "$filename {$allegato['IDDESTINATARIO']}.$extension.p7m",
                            'error' => UPLOAD_ERR_OK,
                            'tmp_name' => $allegato['OUTPUTFILEPATH']
                        ));
                    }

                    $this->rsnAuth->setAllegati(null);

                    output::ajaxResponseHtml($this->showDetails(trim($this->request['ID'], '#')));
                }

                output::ajaxSendResponse();
                break;
        }

        return output::$html_out;
    }

    public function getNewsFromBO($workDate = '') {
        $arr_ext_art = array();

        if (!$workDate) {
            $workDate = date('Ymd');
        }

        $sql = "SELECT
                    PROPAS.PROITK AS PROITK,            
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROPDADATA AS PROPDADATA,
                    PROPAS.PROPADDATA AS PROPADDATA,
                    PROPAS.PROPTIT AS PROPTIT,
                    PROPAS.PROPFLALLE AS PROPFLALLE,                    
                    PROPAS.PROPPASS AS PROPPASS,
                    PROPAS.PROPGRUP AS PROPGRUP,                    
                    PROPAS.PROPUSER AS PROPUSER,
                    PROPAS.PROCAR AS PROCAR,
                    PROGES.GESSTT AS SETTORE,
                    PROGES.GESATT AS ATTIVITA,
                    PROPAS.PROPCONT AS PROPCONT," .
                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS AUTORE
                FROM
                    PROPAS PROPAS
                    LEFT OUTER JOIN ANANOM ANANOM ON PROPAS.PRORES=ANANOM.NOMRES
                    LEFT OUTER JOIN ANAPRA ANAPRA ON PROPAS.PROPRO=ANAPRA.PRANUM
                    LEFT OUTER JOIN PROGES PROGES ON PROPAS.PRONUM=PROGES.GESNUM
                WHERE
                    PROPAS.PROPART = 1 ORDER BY PROPDADATA DESC, PROPADDATA"; //AND PROPAS.PROPDADATA <= '$workDate' AND PROPAS.PROPADDATA >= '$workDate'"; // ORDER BY PROPDADATA DES, PROPADDATA";

        $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        if ($Propas_tab) {
            $i = 0;
            foreach ($Propas_tab as $Propas_rec) {
                $Anaset_rec = $this->praLib->GetAnaset($Propas_rec['SETTORE'], 'codice', $this->PRAM_DB);
                $Anaatt_rec = $this->praLib->GetAnaatt($Propas_rec['ATTIVITA'], 'codice', $this->PRAM_DB);
                //
                if ($Propas_rec['PROPDADATA']) {
                    if ($Propas_rec['PROPADDATA']) {
                        if ($Propas_rec['PROPDADATA'] < $workDate && $Propas_rec['PROPADDATA'] < $workDate) {
                            continue;
                        } elseif ($Propas_rec['PROPDADATA'] > $workDate && $Propas_rec['PROPADDATA'] > $workDate) {
                            continue;
                        }
                    } else {
                        if ($Propas_rec['PROPDADATA'] > $workDate) {
                            continue;
                        }
                    }
                } else {
                    continue;
                }

                if ($this->config['categoria'] != "") {
                    if ($Propas_rec['PROCAR']) {
                        if ($Propas_rec['PROCAR'] != $this->config['categoria']) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                $arr_ext_art[$i]['ID'] = $Propas_rec['PROPAK'];
                $arr_ext_art[$i]['DATA'] = $Propas_rec['PROPDADATA'];
                $arr_ext_art[$i]['DADATA'] = $Propas_rec['PROPDADATA'];
                $arr_ext_art[$i]['ADDATA'] = $Propas_rec['PROPADDATA'];
                $arr_ext_art[$i]['TITOLO'] = $Propas_rec['PROPTIT'];
                $arr_ext_art[$i]['AUTORE'] = $Propas_rec['AUTORE'];
                $arr_ext_art[$i]['CONTENUTO'] = $Propas_rec['PROPCONT'];
                $arr_ext_art[$i]['CLASSIFICAZIONE'] = $Anaset_rec['SETDES'] . " - " . $Anaatt_rec['ATTDES'];
                $arr_ext_art[$i]['PASSWORD'] = $Propas_rec['PROPPASS'];
                $arr_ext_art[$i]['UTENTE'] = $Propas_rec['PROPUSER'];
                $arr_ext_art[$i]['GRUPPO'] = $Propas_rec['PROPGRUP'];
                $arr_ext_art[$i]['CATEGORIA'] = $Propas_rec['PROCAR'];

                if ($Propas_rec['PROPFLALLE'] == 1) {
                    $Pasdoc_tab = $this->praLib->GetPasdoc($Propas_rec['PROPAK'], 'codice', true, $this->PRAM_DB);
                    foreach ($Pasdoc_tab as $key => $Pasdoc_rec) {
                        if ($Pasdoc_rec['PASRIS']) {
                            continue;
                        }

                        $arr_ext_art[$i]['ALLEGATI'][$key] = $Pasdoc_rec['PASNOT'];
                    }
                }
                $i = $i + 1;
            }
        }

        return $arr_ext_art;
    }

    public function GestioneAllegato($fileId) {
        if ($fileId == 0) {
            return false;
        }

        $sql = "SELECT * FROM PASDOC WHERE ROWID = '" . addslashes($fileId) . "' AND PASRIS = '0'";
        $Pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Pasdoc_rec) {
            return false;
        }

        $id = $Pasdoc_rec['PASKEY'];
        if ($Pasdoc_rec['PASNAME']) {
            $fileOrig = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME);
        } else {
            $fileOrig = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
        }
        $ext = strtolower(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));

        $pramPath = ITA_PRATICHE . substr($id, 0, 4) . "/PASSO/" . $id;

        $file = $Pasdoc_rec['PASFIL'];

        if (strtolower($ext) == "xhtml" || strtolower($ext) == "docx") {
            if (file_exists($pramPath . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                $file = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m";
            } else if (file_exists($pramPath . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf")) {
                $file = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf";
            }
        }

        switch ($this->request['operation']) {
            case 'view':
                $md5File = md5($file);
                $md5Token = $this->request['htok'];
                if ($md5File !== $md5Token) {
                    return false;
                }

                if (!$tempFolder = $this->praLib->getCartellaTemporaryPratiche("TMP_PASSO_PRAT-" . $id)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . substr($Pasdoc_rec['PASKEY'], 0, 10), __CLASS__);
                    return false;
                }
                if (!file_exists($tempFolder . "/" . $file)) {
                    $repConnector = new praRep(ITA_PRATICHE);
                    if (!$repConnector->getFile(substr($id, 0, 4) . "/PASSO/" . $id . "/" . $file, $tempFolder . "/" . $file, false)) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0010', $repConnector->getErrorMessage(), __CLASS__);
                        return false;
                    }
                }

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (strtolower($ext) == "p7m") {
                    if ($Pasdoc_rec['PASNAME']) {
                        $fileOrig = $this->praLib->GetRicdocP7MNameOriginale($Pasdoc_rec['PASNAME']);
                    } else {
                        $fileOrig = $this->praLib->GetRicdocP7MNameOriginale($Pasdoc_rec['PASFIL']);
                    }

                    //Mi trovo l'estensione base del file
                    $Est_baseFile = $this->praLib->GetBaseExtP7MFile($file);
                    if ($Est_baseFile == "") {
                        $Est_baseFile = $this->praLib->GetBaseExtP7MFile($Pasdoc_rec['PASNAME']);
                    }
                    // Mi trovo e accodo tutte le estensioni p7m
                    $Est_tmp = $this->praLib->GetExtP7MFile($file);
                    $posPrimoPunto = strpos($Est_tmp, ".");
                    $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
                    $p7mExt = str_replace($delEst, "", $Est_tmp);
                    //Creo l'estensione finale del file
                    $ext = $Est_baseFile . "." . $p7mExt;
                }

                $file = $tempFolder . "/" . $file;
                $this->frontOfficeLib->vediAllegato($file, false);
                $this->removeTempDir($tempFolder);
                exit;

            case 'delete':
                if (!ItaDB::DBDelete($this->PRAM_DB, 'PASDOC', 'ROWID', $Pasdoc_rec['ROWID'])) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0011', "Cancellazione record allegato PASDOC ROWID '{$Pasdoc_rec['ROWID']}' passo '$id' fallita", __CLASS__);
                    return false;
                }

                unlink("$pramPath/$file");

                if ($this->request['returnTo']) {
                    header('Location: ' . $this->request['returnTo']);
                    die;
                }

                break;
        }
    }

    public function getNewsPasswd($id) {
        $sql = "SELECT * FROM PROPAS WHERE PROPAK = '" . addslashes($id) . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $Propas_rec['PROPPASS'];
    }

    public function showDetails($id, $currentDate = '', $currentHour = '') {
        if (!$currentDate) {
            $currentDate = date('Ymd');
        }

        if (!$currentHour) {
            $currentHour = date('H:i');
        }

        /*
         * Record Passo
         */
        $sql = "SELECT * FROM PROPAS WHERE PROPAK = '" . addslashes($id) . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        if (!$Propas_rec) {
            return false;
        }

        if ($Propas_rec['PROPDADATA']) {
            if ($currentDate < $Propas_rec['PROPDADATA']) {
                return false;
            }

            if ($currentDate == $Propas_rec['PROPDADATA'] && $Propas_rec['PROPDAORA'] && $currentHour < $Propas_rec['PROPDAORA']) {
                return false;
            }
        }

        if ($Propas_rec['PROPADDATA']) {
            if ($currentDate > $Propas_rec['PROPADDATA']) {
                return false;
            }

            if ($currentDate == $Propas_rec['PROPADDATA'] && $Propas_rec['PROPADORA'] && $currentHour > $Propas_rec['PROPADORA']) {
                return false;
            }
        }

        if (isset($_SESSION[__CLASS__ . 'VerificaSHA'])) {
            if ($_SESSION[__CLASS__ . 'VerificaSHA']) {
                output::addAlert($_SESSION[__CLASS__ . 'VerificaSHA'], '', 'error');
            } else {
                output::addAlert('Lo SHA del file corrisponde con quello dell\'allegato.', '', 'success');
            }

            unset($_SESSION[__CLASS__ . 'VerificaSHA']);
        }

        /*
         * Record Fascicolo
         */
        $sql = "SELECT * FROM PROGES WHERE GESNUM = '" . $Propas_rec['PRONUM'] . "'";
        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        //$numProt = $Metadati['DatiProtocollazione']['proNum']['value'];
        $numProt = substr($Proges_rec['GESNPR'], 4);
        if ($numProt) {
            $Metadati = unserialize($Proges_rec['GESMETA']);
            $dataProt = substr($Proges_rec['GESNPR'], 0, 4);
            if ($Metadati['DatiProtocollazione']['Data']['value']) {
                $dataProt = date("d/m/Y", strtotime($Metadati['DatiProtocollazione']['Data']['value']));
            }
        }

        output::appendHtml($this->disegnaUploadError());

        /*
         * Record Procedimento
         */
        $sql = "SELECT * FROM ANAPRA WHERE PRANUM = '" . $Proges_rec['GESPRO'] . "'";
        $Anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        output::appendHtml("<br>");
        //output::appendHtml("<div style=\"font-size:1.2em;\"><b>Fascicolo Elettronico N.:</b> " . substr($Proges_rec['GESNUM'], 4) . "/" . substr($Proges_rec['GESNUM'], 0, 4) . "</div>");
        $Serie_rec = $this->praLib->ElaboraProgesSerie($Proges_rec['GESNUM'], $Proges_rec['SERIECODICE'], $Proges_rec['SERIEANNO'], $Proges_rec['SERIEPROGRESSIVO'], $this->PRAM_DB, $this->PROT_DB);
        output::appendHtml("<div style=\"font-size:1.3em;text-decoration:underline;\"><b>RIEPILOGO FASCICOLO ELETTRONICO</b></div><br>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><b>Sigla / Numero / Anno:</b> " . $Serie_rec . "</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><b>Descrizione:</b> " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "</div>");
        if ($Proges_rec['GESOGG']) {
            output::appendHtml("<div style=\"font-size:1.2em;\"><b>Oggetto:</b> " . $Proges_rec['GESOGG'] . "</div>");
        }
        if ($numProt) {
            output::appendHtml("<div style=\"font-size:1.2em;\"><b>Protocollo Numero:</b> $numProt del $dataProt</div>");
        }


        output::appendHtml("<div class='content' style='padding-top:10px;padding-bottom:10px;'>");
        output::appendHtml($Propas_rec['PROPCONT']);
        output::appendHtml("</div><br>");

        $sql = "SELECT
                    PASDOC.*,
                    ANACLA.CLADES
                FROM
                    PASDOC
                LEFT OUTER JOIN
                    ANACLA ON ANACLA.CLACOD = PASDOC.PASCLAS
                WHERE
                    PASKEY = '" . addslashes($id) . "' AND PASPUB = 1 AND PASRIS = 0 ORDER BY PASCLAS DESC"; //PASFIL";

        $dataDetail_tabTmp = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($dataDetail_tabTmp) {
            $dataDetail_tab = $this->ControlloXHTML($dataDetail_tabTmp, $id);
        }
        output::appendHtml("<br>");
        //

        if ($Propas_rec['PROFLCDS'] == '1') {
            return $this->disegnaFirmaDocumento($Propas_rec);
        }

        output::appendHtml("<div>");
        /*
         * Mostro il bottone del file zip solo se ci sono gli allegati e se ce ne sono piu di uno
         */
        if (count($dataDetail_tab) > 1) {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'scaricaZipAllegati', 'propak' => $Propas_rec['PROPAK']));
            output::appendHtml("<div style=\"display:inline-block;\">
                                        <button style=\"cursor:pointer;padding:3px;\" name=\"scaricaZip\" class=\"italsoft-button\" type=\"button\" onclick=\"location.href='$href';\">
                                            <i class=\"icon ion-document italsoft-icon\"></i>
                                            <span>Scarica allegati in formato ZIP</span>
                                        </button>
                                    </div>");
        }

        /*
         * Mostro il bottone Esprimi parere solo se c'è la password nell'articolo e se è attivato dal BO
         */
        if ($Propas_rec['PROFLPARERE'] == 1 && $Propas_rec['PROPPASS']) {
            $Iteevt_rec = itaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $this->config['procedi'] . "'", false);

            if ($Iteevt_rec) {
                $href1 = ItaUrlUtil::GetPageUrl(array(
                            'p' => $this->config['parere_page'],
                            'propak' => $Propas_rec['PROPAK'],
                            'event' => 'openBlock',
                            'procedi' => $this->config['procedi'],
                            'subproc' => $Iteevt_rec['IEVCOD'],
                            'subprocid' => $Iteevt_rec['ROWID']
                ));

                output::appendHtml("&nbsp;<div style=\"display:inline-block;\">
                                        <button style=\"cursor:pointer;padding:3px;\" name=\"esprimiParere\" class=\"italsoft-button\" type=\"button\" onclick=\"window.open('$href1', '_blank');\">
                                            <i class=\"icon ion-speakerphone italsoft-icon\"></i>
                                            <span>Esprimi Parere/Richiedi Integrazione</span>
                                        </button>
                                    </div>");
            }
        }

        output::appendHtml("</div>");
        output::appendHtml("<br>");

        $extraParms['PRAM_DB'] = $this->PRAM_DB;


        /*
         * Disegno griglia allegati news
         */
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
        $praHtmlGridAllegati = new praHtmlGridAllegati();
        if (count($dataDetail_tab)) {
            // Tabella Allegati News
            output::appendHtml($praHtmlGridAllegati->GetGridAllegatiNews($dataDetail_tab, $extraParms));
        }

        /*
         * Disegno Griglia Pareri Espressi
         */
        $Pareri_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT
                                                                PROPAS.*,
                                                                PRACOM.COMDAT,
                                                                PRACOM.COMMLD,
                                                                PRACOM.COMNOM,
                                                                PRACOM.COMNOT
                                                          FROM 
                                                                PROPAS
                                                          LEFT OUTER JOIN PRACOM ON PROPAS.PROPAK = PRACOM.COMPAK AND COMTIP = 'A' 
                                                          WHERE
                                                                PROKPRE = '" . addslashes($id) . "' 
                                                          ORDER BY 
                                                                COMDAT DESC", true);
        //PROPART = 1
        if ($Pareri_tab) {
            output::appendHtml("<br>");
            output::appendHtml($praHtmlGridAllegati->GetGridPareri($Pareri_tab, $extraParms));
        }

        output::appendHtml("<br>");
        output::appendHtml("<div align=center><a href=\"javascript:history.go(-1)\" class=\"italsoft-button\">Indietro</a></div>");
        return output::$html_out;
    }

    private function disegnaFirmaDocumento($Propas_rec) {
        $html = new html();
        $id = $Propas_rec['PROPAK'];

        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . addslashes($id) . "' AND PASRIS = 0 AND PASFLCDS = '1'";
        $Pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        output::appendHtml('<h3>Conferenza di servizi</h3>');

        if (!count($Pasdoc_tab)) {
            output::appendHtml('<p>Nessun documento presente.</p>');
            return true;
        }

        $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO = '" . addslashes($Propas_rec['PROPAK']) . "' ORDER BY SEQUENZA ASC";
        $Pramitdest_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        $conferenzaTerminata = $chiusuraConferenza = true;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $extension = strtolower(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
            if ($extension !== 'p7m') {
                $conferenzaTerminata = false;
            }
        }

        if ($conferenzaTerminata) {
            output::addBr();
            output::addAlert('La conferenza di servizi è conclusa, tutte le firme sono state applicate.', '', 'success');
        }

        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $filename = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
            $nicename = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) ?: $filename;

            $pramPathTmp = explode('://', ITA_PRATICHE);
            $pramPath = end($pramPathTmp) . substr($id, 0, 4) . "/PASSO/$id";

            $fileFinaleGenerato = false;

            if (in_array($extension, array('xhtml', 'docx'))) {
                if (file_exists("$pramPath/$filename.pdf")) {
                    $extension = 'pdf';
                }
            }

            if ($extension === 'p7m') {
                $fileFinaleGenerato = true;
            }

            $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'gestioneAllegato',
                        'operation' => 'view',
                        'infoAllegato' => itaCrypt::encrypt(json_encode(array(
                            'fileId' => $Pasdoc_rec['ROWID'],
                            'htok' => md5($Pasdoc_rec['PASFIL'])
                        )))
            ));

            output::addBr();

            output::addButton('Download <b>' . $nicename . '.' . $extension . '</b>', $allegatoHref);

            output::addBr();

            $tableData = array(
                'header' => array(
                    '',
                    'Firmatario',
                    array('text' => 'Stato firma', 'attrs' => array('style' => 'width: 400px;')),
                    array('attrs' => array('style' => 'width: 75px;')),
                    array('attrs' => array('style' => 'width: 75px;')),
                    array('attrs' => array('style' => 'width: 75px;')),
                    array('attrs' => array('style' => 'width: 75px;'))
                ),
                'body' => array(),
                'style' => array('body' => array('text-align: center;'))
            );

            $allegatoPrecedente = array('PASNAME' => $nicename . '.' . $extension, 'PASFIL' => $filename . '.' . $extension);
            $disegnaUpload = true;

            $numFirmatario = 1;

            foreach ($Pramitdest_tab as $Pramitdest_rec) {
                $sql = "SELECT * FROM PASDOC WHERE PASROWIDBASE = '{$Pasdoc_rec['ROWID']}' AND PASUTELOG = 'PRAMITTDEST:{$Pramitdest_rec['ROWID']}'";
                $allegatoFirmato = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                if ($fileFinaleGenerato) {
                    $textStato = $html->getImage(frontOfficeLib::getIcon('shield-ok'), '16px') . ' <span style="display: inline-block; vertical-align: middle;">File firmato</span>';
                    $textStato .= '<br><small>' . $allegatoFirmato['PASNAME'] . '</small>';

                    $tableData['body'][] = array(
                        $numFirmatario++ . '.',
                        '<b>' . $Pramitdest_rec['NOME'] . '</b>',
                        $textStato,
                        '',
                        '',
                        '',
                        ''
                    );

                    continue;
                }

                $textStato = $html->getImage(frontOfficeLib::getIcon('shield'), '16px') . ' In attesa';
                $textDownload = $textCancel = $textUpload = $textRemota = '';

                if ($disegnaUpload) {
                    $textStato = $html->getImage(frontOfficeLib::getIcon('shield'), '16px') . ' Da caricare';

                    $formAction = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'uploadAllegatoFirmato',
                                'infoUpload' => itaCrypt::encrypt(json_encode(array(
                                    'chiavePasso' => $id,
                                    'idAllegato' => $Pasdoc_rec['ROWID'],
                                    'idDestinatario' => $Pramitdest_rec['ROWID']
                                )))
                    ));

                    $textUpload = '<div style="text-align: center; font-size: .7em;">';
                    $textUpload .= '<form method="POST" action="' . $formAction . '" enctype="multipart/form-data">';
                    $textUpload .= '<a href="#"><label style="cursor: pointer;">';
                    $textUpload .= '<input type="file" name="allegatoFirmato" style="display: none;" onchange="$(\'body\').addClass(\'italsoft-loading\'); this.form.submit();">';
                    $textUpload .= $html->getImage(frontOfficeLib::getIcon('upload'), '18px');
                    $textUpload .= '<br>Upload';
                    $textUpload .= '</label></a>';
                    $textUpload .= '</form>';
                    $textUpload .= '</div>';

                    $textRemota = '<div style="text-align: center; font-size: .7em;">';
                    $textRemota .= $html->getButton($html->getImage(frontOfficeLib::getIcon('pencil'), '18px') . '<br>Firma', '#', '', array(
                        'id' => 'firma',
                        'input' => itaCrypt::encrypt(json_encode(
                                        array(
                                            array(
                                                'FILEORIG' => $allegatoPrecedente['PASNAME'],
                                                'INPUTFILEPATH' => $pramPath . '/' . $allegatoPrecedente['PASFIL'],
                                                'IDALLEGATO' => $Pasdoc_rec['ROWID'],
                                                'IDDESTINATARIO' => $Pramitdest_rec['ROWID']
                                            )
                                        )
                        ))
                    ));
                    $textRemota .= '</div>';

                    $disegnaUpload = false;
                }

                if ($allegatoFirmato) {
                    $textStato = $html->getImage(frontOfficeLib::getIcon('shield-ok'), '16px') . ' <span style="display: inline-block; vertical-align: middle;">File firmato</span>';
                    $textStato .= '<br><small>' . $allegatoFirmato['PASNAME'] . '</small>';

                    $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'gestioneAllegato',
                                'operation' => 'view',
                                'infoAllegato' => itaCrypt::encrypt(json_encode(array(
                                    'fileId' => $allegatoFirmato['ROWID'],
                                    'htok' => md5($allegatoFirmato['PASFIL'])
                                )))
                    ));

                    $textDownload = '<a href="' . $allegatoHref . '">';
                    $textDownload .= '<div style="text-align: center; font-size: .7em;">';
                    $textDownload .= $html->getImage(frontOfficeLib::getIcon('download'), '18px');
                    $textDownload .= '<br>Download';
                    $textDownload .= '</div>';
                    $textDownload .= '</a>';

                    $textUpload = $textRemota = '';
                    $disegnaUpload = true;

                    $cancelHref = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'gestioneAllegato',
                                'operation' => 'delete',
                                'infoAllegato' => itaCrypt::encrypt(json_encode(array(
                                    'fileId' => $allegatoFirmato['ROWID'],
                                    'returnTo' => ItaUrlUtil::GetPageUrl(array(
                                        'event' => 'dettaglioArticolo',
                                        'ID' => $id
                                    ))
                                )))
                    ));

                    $textCancel = '<a href="' . $cancelHref . '">';
                    $textCancel .= '<div style="text-align: center; font-size: .7em;">';
                    $textCancel .= $html->getImage(frontOfficeLib::getIcon('error'), '18px');
                    $textCancel .= '<br>Rimuovi';
                    $textCancel .= '</div>';
                    $textCancel .= '</a>';

                    foreach ($tableData['body'] as $k => $arr) {
                        $tableData['body'][$k][count($tableData['body'][0]) - 1] = '';
                    }
                }

                $allegatoPrecedente = $allegatoFirmato;

                $tableData['body'][] = array(
                    $numFirmatario++ . '.',
                    '<b>' . $Pramitdest_rec['NOME'] . '</b>',
                    $textStato,
                    $textRemota,
                    $textUpload,
                    $textDownload,
                    $textCancel
                );
            }

            if ($fileFinaleGenerato || !$allegatoPrecedente) {
                $chiusuraConferenza = false;
            }

            if (!$fileFinaleGenerato && !$this->rsnAuth) {
                unset($tableData['header'][3]);

                foreach ($tableData['body'] as $k => $rec) {
                    unset($rec[3]);
                    $tableData['body'][$k] = $rec;
                }
            }

            if ($fileFinaleGenerato) {
                unset($tableData['header'][3]);
                unset($tableData['header'][4]);
                unset($tableData['header'][5]);
                unset($tableData['header'][6]);

                foreach ($tableData['body'] as $k => $rec) {
                    unset($rec[3]);
                    unset($rec[4]);
                    unset($rec[5]);
                    unset($rec[6]);

                    $tableData['body'][$k] = $rec;
                }
            }

            output::addTable($tableData);

            output::addBr();
        }

        if ($chiusuraConferenza) {
            /*
             * Tutte le firme presenti
             */

            $concludiCDS = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'chiudiConferenzaServizi',
                        'info' => itaCrypt::encrypt(json_encode(array(
                            'chiavePasso' => $id
                        )))
            ));

            output::appendHtml('<div style="text-align: right; font-size: .8em;">');
            output::addButton('Effettua chiusura', $concludiCDS);
            output::appendHtml('</div>');
        }

        return output::$html_out;
    }

    private function verificaAllegatoFirmato($allegatoFirmato, $tipoFirma, $numeroFirme, $shaOriginale) {
        $p7m = itaP7m::getP7mInstance($allegatoFirmato, $tipoFirma);

        if (!$p7m) {
            return array(false, 'Il file .p7m non è valido');
        }

        if (!$p7m->isFileVerifyPassed()) {
            $returnErr = $p7m->getMessageErrorFileAsString() ?: 'Il file .p7m non è valido';
            $p7m->cleanData();
            unset($p7m);

            return array(false, $returnErr);
        }

        $infoSummary = $p7m->getInfoSummary();
        $shaFile = $p7m->getContentSHA();

        $p7m->cleanData();
        unset($p7m);

        if ($shaFile !== $shaOriginale) {
            return array(false, 'Il file firmato non corrisponde al file originale');
        }

        if (count($infoSummary) !== $numeroFirme) {
            return array(false, 'Il numero di firme presenti non è corretto');
        }

        $ultimaFirma = reset($infoSummary);

        return array(true, $ultimaFirma['messageErrorSigner'], $ultimaFirma['signer']);
    }

    private function caricaAllegatoFirmato($infoUpload, $allegatoFirmato = false) {
        $isUpload = false;

        $idAllegato = $infoUpload['idAllegato'];
        $idDestinatario = $infoUpload['idDestinatario'];

        if (!$allegatoFirmato) {
            $isUpload = true;
            $allegatoFirmato = $_FILES['allegatoFirmato'];
        }

        $sql = "SELECT * FROM PASDOC WHERE ROWID = '" . addslashes($idAllegato) . "'";
        $Pasdoc_orig_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $chiavePasso = $Pasdoc_orig_rec['PASKEY'];

        /*
         * Per il conto delle firme
         */
        $sql = "SELECT * FROM PASDOC WHERE PASROWIDBASE = '" . addslashes($idAllegato) . "' AND PASTIPO = 'FIR_CDS'";
        $Pasdoc_fir_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $countFirme = count($Pasdoc_fir_tab) + 1;

        $filename = strtolower(pathinfo($Pasdoc_orig_rec['PASFIL'], PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($Pasdoc_orig_rec['PASFIL'], PATHINFO_EXTENSION));

        $pramPathTmp = explode('://', ITA_PRATICHE);
        $pramPath = end($pramPathTmp) . substr($chiavePasso, 0, 4) . "/PASSO/$chiavePasso";

        if (in_array($extension, array('xhtml', 'docx'))) {
            if (file_exists("$pramPath/$filename.pdf")) {
                $extension = 'pdf';
            }
        }

        if (!$allegatoFirmato['name']) {
            $this->uploadError = 'Richiesta non valida';
            return false;
        }

        if ($allegatoFirmato['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Errore durante l\'upload del file.';

            switch ($allegatoFirmato['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $max_size = ini_get('upload_max_filesize');
                    $this->uploadError .= " Le dimensioni del file superano il valore massimo consentito di $max_size.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->uploadError .= ' Nessun file selezionato';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->uploadError .= ' Cartella temporanea mancante.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->uploadError .= ' Errore di scrittura sul disco.';
                    break;
            }

            return false;
        }

        $fileDestinazione = "$pramPath/" . md5($Pasdoc_orig_rec['PASNAME'] . $idDestinatario . time()) . ".$extension.p7m";

        if ($isUpload) {
            move_uploaded_file($allegatoFirmato['tmp_name'], $fileDestinazione);
        } else {
            rename($allegatoFirmato['tmp_name'], $fileDestinazione);
        }

        $shaContenuto = sha1_file("$pramPath/$filename.$extension");
        $anapar_recTipoFirma = $this->praLib->GetAnapar('TIPO_VERIFICA_FIRMA', 'parkey', $this->PRAM_DB, false);

        $retFirma = $this->verificaAllegatoFirmato($fileDestinazione, $anapar_recTipoFirma['PARVAL'], $countFirme, $shaContenuto);

        if (!$retFirma[0]) {
            unlink($fileDestinazione);
            $this->uploadError = $retFirma[1];
            return false;
        }

        $pasdoc_insert_rec = array(
            'PASFIL' => basename($fileDestinazione),
            'PASLNK' => 'allegato://' . basename($fileDestinazione),
            'PASNOT' => "File Originale: " . $allegatoFirmato['name'],
            'PASNAME' => $allegatoFirmato['name'],
            'PASKEY' => $chiavePasso,
            'PASCLA' => 'ESTERNO',
            'PASTIPO' => 'FIR_CDS',
            'PASDATAFIRMA' => date('Ymd'),
            'PASUTELOG' => 'PRAMITTDEST:' . $idDestinatario,
            'PASDATADOC' => date('Ymd'),
            'PASORADOC' => date('H:i:s'),
            'PASROWIDBASE' => $idAllegato,
            'PASSUBTIPO' => 'E',
            'PASSHA2' => hash_file('sha256', $fileDestinazione)
        );

        if (!ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_insert_rec)) {
            $this->uploadError = 'Errore durante il salvataggio del record';
            return false;
        }

        /*
         * Verifico se è il definitivo
         */

        $sql = "SELECT ROWID FROM PRAMITDEST WHERE KEYPASSO = '$chiavePasso' ORDER BY SEQUENZA ASC";
        $Pramitdest_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if (count($Pramitdest_tab) === $countFirme) {
            copy($fileDestinazione, "$pramPath/$filename.$extension.p7m");
        }

        return true;
    }

    function removeTempDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->removeTempDir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function ControlloXHTML($allegati, $id) {
        $pramPath = ITA_PRATICHE . substr($id, 0, 4) . "/PASSO/" . $id . "/";
        foreach ($allegati as $key => $allegato) {
            $ext = strtolower(pathinfo($allegato['PASFIL'], PATHINFO_EXTENSION));
            if (strtolower($ext) == "xhtml" || strtolower($ext) == "docx") {
                $baseNameOrig = pathinfo($allegato['PASNAME'], PATHINFO_FILENAME);
                $baseName = pathinfo($allegato['PASFIL'], PATHINFO_FILENAME);
                if (file_exists($pramPath . $baseName . ".pdf.p7m")) {
                    $allegati[$key]['PASFIL'] = $baseName . ".pdf.p7m";
                    $allegati[$key]['PASNAME'] = $baseNameOrig . ".pdf.p7m";
                    $allegati[$key]['PASSHA2'] = hash_file('sha256', $pramPath . $baseName . ".pdf.p7m");
                    continue;
                } else if (file_exists($pramPath . $baseName . ".pdf")) {
                    $allegati[$key]['PASFIL'] = $baseName . ".pdf";
                    $allegati[$key]['PASNAME'] = $baseNameOrig . ".pdf";
                    $allegati[$key]['PASSHA2'] = hash_file('sha256', $pramPath . $baseName . ".pdf");
                    continue;
                }

                unset($allegati[$key]);
            }
        }
        return $allegati;
    }

}
