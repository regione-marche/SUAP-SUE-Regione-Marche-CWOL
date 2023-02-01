<?php

/**
 *
 * GESTIONE ABBINAMENTO IMMAGINI SCANNER A PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    28.10.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/lib/itaPHPCore/itaComponents.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proProtocollo.class.php');
include_once (ITA_LIB_PATH . '/QXml/QXml.class.php');

function proAbbinaAlle() {
    $proAbbinaAlle = new proAbbinaAlle();
    $proAbbinaAlle->parseEvent();
    return;
}

class proAbbinaAlle extends itaModel {

    public $PROT_DB;
    public $nameForm = "proAbbinaAlle";
    public $divRis = "proAbbinaAlle_divRisultato";
    public $griAbbina = "proAbbinaAlle_gridAbbina";
    public $elencoFile;
    public $proLib;
    public $proLibAllegati;
    public $workDate;
    public $nElemento;
    private $arrErroriAbbina;
    private $enabledExtensions = array(
        'pdf',
        'jpg',
        'gif',
        'png'
    );

    function __construct() {
        parent::__construct();
        try {
            $this->workDate = date('Ymd');
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->elencoFile = App::$utente->getKey($this->nameForm . '_elencoFile');
            $this->nElemento = App::$utente->getKey($this->nameForm . '_nElemento');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_elencoFile', $this->elencoFile);
            App::$utente->setKey($this->nameForm . '_nElemento', $this->nElemento);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->elencoFile = array();
                $this->apriRisultato();
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridAbbina':
                        $Acq_model = 'utiAcqrMen';
                        Out::closeDialog($Acq_model);
                        $_POST = array();
                        $_POST[$Acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$Acq_model . '_returnField'] = $this->nameForm . '_CaricaGridAllegati';
                        $_POST[$Acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$Acq_model . '_title'] = 'Carica Allegati';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($Acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($Acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $Acq_model . '.php';
                        $Acq_model();
                        break;
                }
                break;
            case 'onClickTablePager':
                $this->caricaTabella();
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->griAbbina :
                        Out::msgQuestion("Cancellazione.", "L'iimagine acuisita sarà cancellata. L'operazione non è reversibile. Confermii la cancellazione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCanc', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCanc', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->griAbbina :
                        Out::hide($this->nameForm . '_divRisultato');
                        Out::show($this->nameForm . '_divDettaglio');
                        Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_Cancella');
                        Out::hide($this->nameForm . '_Modifica');
                        Out::hide($this->nameForm . '_Abbina');
                        Out::hide($this->nameForm . '_divOcr');
                        $this->nElemento = $_POST['rowid'];
                        $this->Modifica();
                        break;
                }
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CaricaGridAllegati':
                        $errore = false;
                        foreach ($_POST['retList'] as $allegato) {
                            $appsTempPath = $this->proLib->SetDirectory('', "ABBINA");
                            if (!@rename($allegato['FILEPATH'], $appsTempPath . "/" . $allegato['FILENAME'])) {
                                Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                                $errore = true;
                                break;
                            }
                        }
                        if (!$errore) {
                            $this->caricaTabella();
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TipoOCR':
                        if ($this->formData[$this->nameForm . '_TipoOCR'] === '0') {
                            Out::hide($this->nameForm . '_CaricaOcr');
                        } else {
                            Out::show($this->nameForm . '_CaricaOcr');
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->apriRisultato();
                        break;
                    case $this->nameForm . '_CaricaOcr':
                        $this->caricaTabella(true, $_POST[$this->nameForm . '_TipoOCR']);
                        break;
                    case $this->nameForm . '_Modifica':
                        Out::hide($this->nameForm . '_divRisultato');
                        Out::show($this->nameForm . '_divDettaglio');
                        Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_Cancella');
                        Out::hide($this->nameForm . '_Modifica');
                        Out::hide($this->nameForm . '_Abbina');
                        Out::hide($this->nameForm . '_divOcr');
                        $this->nElemento = 1;
                        $this->Modifica();
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione.", "L'iimagine acuisita sarà cancellata. L'operazione non è reversibile. Confermii la cancellazione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCanc', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCanc', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_Precedente':
                        if ($this->nElemento != 1) {
                            $this->nElemento -= 1;
                            $this->Modifica();
                        }
                        break;
                    case $this->nameForm . '_Successivo':
                        if ($this->nElemento != count($this->elencoFile)) {
                            $this->nElemento += 1;
                            $this->Modifica();
                        }
                        break;
                    case $this->nameForm . '_Abbina':
                        $abbinati = 0;
                        $daAbbinare = count($this->elencoFile);
                        $audit_Info = "Inizio abbinamento elenco, record totali: $daAbbinare";
                        $this->insertAudit($this->PROT_DB, '', $audit_Info);
                        $this->arrErroriAbbina = array();
                        foreach ($this->elencoFile as $rowidAllegato => $allegato) {
                            $numeroProtocollo = $allegato['PROTOCOLLO'];
                            if ($numeroProtocollo == '') {
                                $audit_Info = "File: {$allegato['FILENAME']} con protocollo da abbinare non definito.";
                                $this->insertAudit($this->PROT_DB, '', $audit_Info);
                                continue;
                            }

                            $Anapro_rec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', '', " (PROPAR='A' OR PROPAR='P' OR PROPAR='C')");
                            if (!$Anapro_rec) {

                                $this->addErrore("Archiviazione File", "Errore Protocollo N. $numeroProtocollo non trovato.");
                                //Out::msgStop("Archiviazione File", "Errore Protocollo N. $numeroProtocollo non trovato.");
                                continue;
                            }
                            $destinazione = $this->proLib->SetDirectory($numeroProtocollo, $Anapro_rec['PROPAR']);
                            if (!$destinazione) {
                                $this->addErrore("Archiviazione File", "Errore nella cartella di destinazione.");
                                //Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                                continue;
                            }

                            $model = 'proProtocollo.class';
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

                            $protocollo = proProtocollo::getInstanceForRowid($this->proLib, $Anapro_rec['ROWID']);
                            if (!$protocollo) {
                                $this->setErrCode('Error');
                                $this->addErrore("Archiviazione File", 'Oggetto Protocollo non istanziato.');
                                //Out::msgStop("Archiviazione File", 'Oggetto Protocollo non istanziato.');
                                continue;
                            }
                            $Anapro_rec = $protocollo->getAnapro_rec();

                            if ($allegato['FILEERRINFO'] !== 1) {
                                $Allegati_tab = $protocollo->getAllegati_tab();
                                if (count($Allegati_tab) > 0) {
                                    $this->addErrore("Archiviazione File", "Allegati già presenti per il protocollo  N: $numeroProtocollo .");
                                    //Out::msgStop("Archiviazione File", "Allegati già presenti per il protocollo  N. $numeroProtocollo .");
                                    continue;
                                }
                            }

                            $stream = base64_encode(file_get_contents($allegato['FILEPATH']));

                            // Preparo Elementi di Base
                            $elementi = array();
                            $elementi['tipo'] = $Anapro_rec['PROPAR'];
                            $elementi['dati'] = array();

                            $elementi['allegati']['Principale'] = array();
                            $elementi['allegati']['Principale']['Nome'] = $allegato['FILENAME'];
                            $elementi['allegati']['Principale']['Stream'] = $stream;
                            /**
                             * Istanza Oggetto ProtoDatiProtocollo
                             */
                            $model = 'proDatiProtocollo.class';
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $proDatiProtocollo = new proDatiProtocollo();
                            $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
                            if ($ret_id === false) {
                                $this->addErrore("Archiviazione File", $proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
                                //Out::msgStop("Archiviazione File", $proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
                                continue;
                            }

                            /**
                             * Utilizzo il protocollatore per aggiungere l'allegato.
                             */
                            $model = 'proProtocolla.class';
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $proProtocolla = new proProtocolla();

                            $motivo = 'Aggiunta allegato da abbinamento allegati. ';
                            $addAllegato = $proProtocolla->aggiungiAllegati($Anapro_rec['PROPAR'], $motivo, $proDatiProtocollo, $Anapro_rec['PRONUM']);
                            if (!$addAllegato) {
                                $this->addErrore("Archiviazione File", $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
                                //Out::msgStop("Archiviazione File", $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
                                continue;
                            }

                            $retRitorno = $proProtocolla->getRisultatoRitorno();
                            $rowdAllegato = $retRitorno['ROWIDAGGIUNTI'][0];
                            $anadoc_rec = $this->proLib->GetAnadoc($rowdAllegato, 'rowid', false);
                            if (!$anadoc_rec) {
                                $this->addErrore("Archiviazione File", $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
                                //Out::msgStop("Archiviazione File", $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
                                continue;
                            }
                            $audit_Info = "Abbinato file: {$allegato['FILENAME']} al protocollo n.: $numeroProtocollo";
                            $this->insertAudit($this->PROT_DB, '', $audit_Info);
                            $abbinati++;
                            $this->cancellaFileDaAbbinare($rowidAllegato);
                            $this->elencoFile[$rowidAllegato]['FILEERRINFO'] = 1;
                        }
                        if (count($this->arrErroriAbbina)) {
                            $htmlErrori = '';
                            foreach ($this->arrErroriAbbina as $key => $erroreAbbina) {
                                $htmlErrori .= "<br>{$erroreAbbina['TITOLO']}:  {$erroreAbbina['MESSAGGIO']}";
                            }
                            Out::msgStop("Abbina Allegati al Protocollo", "Abbinati $abbinati allegati su $daAbbinare. <br><br><br> Errori presenti:$htmlErrori");
                            $audit_Info = "Abbinamento concluso con Errori. Abbinati $abbinati allegati su $daAbbinare.";
                            $this->insertAudit($this->PROT_DB, '', $audit_Info);
                        } else {
                            Out::msgInfo("Abbina Allegati al Protocollo", "Abbinati $abbinati allegati su $daAbbinare");
                            $audit_Info = "Abbinamento concluso senza errori. Abbinati $abbinati allegati su $daAbbinare";
                            $this->insertAudit($this->PROT_DB, '', $audit_Info);
                        }
                        $this->caricaTabella();
                        Out::show($this->nameForm . '_divRisultato');
                        Out::hide($this->nameForm . '_divDettaglio');
                        Out::hide($this->nameForm . '_Elenca');
                        Out::hide($this->nameForm . '_Cancella');
                        Out::show($this->nameForm . '_Modifica');
                        Out::show($this->nameForm . '_Abbina');
                        Out::show($this->nameForm . '_divOcr');
                        Out::attributo($this->nameForm . "_Immagine", 'src', '0', '');
                        Out::valore($this->nameForm . '_Numero', '');
                        Out::valore($this->nameForm . '_Anno', '');

                        break;
                    case $this->nameForm . '_Esci':
                        break;
                    case $this->nameForm . '_ConfermaEsci':
                        $audit_Info = "Uscita da abbinamento con protocolli valorizzati.";
                        $this->insertAudit($this->PROT_DB, '', $audit_Info);
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_ConfermaCanc':
                        $rowid = $this->formData[$this->nameForm . '_gridAbbina']['gridParam']['selrow'];
                        if ($rowid !== null) {
                            $rowid = $this->elencoFile[$this->nElemento]['ROWID'];
                        }
                        if ($rowid) {
                            $this->cancellaRiga($rowid);
                        }
                        break;
                    case $this->nameForm . '_Anno_butt':
                        $data = date('Ymd');
                        $newdata = date('Ymd', strtotime('-90 day', strtotime($data)));
                        $where = "PRODAR BETWEEN '" . $newdata . "' AND '" . $data . "' AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);
                        proRic::proRicNumAntecedenti($this->nameForm, $where);
                        break;
                    case 'before-close-portlet':
                        $valorizzato = false;
                        foreach ($this->elencoFile as $record) {
                            if ($record['PROTOCOLLO'] != '' && $record['FILEERRINFO'] == 0)
                                $valorizzato = true;
                        }
                        if ($valorizzato) {
                            Out::msgQuestion("Esci.", "I dati inseriti non saranno memorizzati, sei sicuro di continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaEsci', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaEsci', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            $this->returnToParent(true);
                        }

                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Descrizione':
                        $this->AssegnaDescrizione($_POST[$this->nameForm . '_Descrizione']);
                        break;
                    case $this->nameForm . '_Anno':
                        if (trim($_POST[$this->nameForm . '_Numero']) != '' && trim($_POST[$this->nameForm . '_Anno']) != '' && trim($_POST[$this->nameForm . '_Anno']) != '0') {
                            $CodiceN = $_POST[$this->nameForm . '_Numero'];
                            $CodiceN = str_repeat("0", 6 - strlen(trim($CodiceN))) . trim($CodiceN);
                            Out::valore($this->nameForm . '_Numero', $CodiceN);
                            $Codice = $_POST[$this->nameForm . '_Anno'] . $CodiceN;
                            $Anapro_rec = $this->proLib->GetAnapro($Codice, 'codice', '', " (PROPAR='A' OR PROPAR='P' OR PROPAR='C')");
                            if (!$Anapro_rec) {
                                Out::msgInfo("Attenzione", "Numero e Anno del Protocollo non esiste. Inserirne uno esistente!");
                                Out::valore($this->nameForm . '_Numero', '');
                                Out::valore($this->nameForm . '_Anno', '');
                                Out::setFocus('', $this->nameForm . "_Numero");
                            } else {
                                $this->AssegnaProtocollo($CodiceN, $_POST[$this->nameForm . '_Anno']);
                                $this->visualizzaCampi();
                            }
                        } else {
                            $this->PulisciProtocollo();
                        }
                        break;
                }
                break;
            case 'returnNumAnte':
                $Anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Numero', substr($Anapro_rec['PRONUM'], 4));
                Out::valore($this->nameForm . '_Anno', substr($Anapro_rec['PRONUM'], 0, 4));
                $this->AssegnaProtocollo(substr($Anapro_rec['PRONUM'], 4), substr($Anapro_rec['PRONUM'], 0, 4));
                $this->visualizzaCampi();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_elencoFile');
        App::$utente->removeKey($this->nameForm . '_nElemento');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('proGest');
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_TipoOCR', 1, 0, 1, 'Nessuno');
        Out::select($this->nameForm . '_TipoOCR', 1, 1, 0, 'MicroREI V. <= 2.1.3.9');
        Out::select($this->nameForm . '_TipoOCR', 1, 2, 0, 'MicroREI V. > 2.1.3.9');
    }

    private function caricaTabella($ricaricaElenco = true, $tipoOcr = '0') {
        try {
            if ($ricaricaElenco) {
                $this->GetFileList();
                if ($tipoOcr != '0') {
                    $this->caricaOcr($tipoOcr);
                }
            }
            $ita_grid01 = new TableView($this->griAbbina, array('arrayTable' => $this->elaboraRecords($this->elencoFile), 'rowIndex' => 'idx'));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000000);
            TableView::enableEvents($this->griAbbina);
            TableView::clearGrid($this->griAbbina);
            $ita_grid01->getDataPage('json');
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    private function GetFileList() {
        $appsTempPath = $this->proLib->SetDirectory('', "ABBINA");
        if (!$dh = opendir($appsTempPath)) {
            return false;
        }
        while (($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            if (!in_array(strtolower(pathinfo($obj, PATHINFO_EXTENSION)), $this->enabledExtensions)) {
                continue;
            }

            $rowid = $this->cercaFile($obj, $this->elencoFile);
            if (!$rowid) {
//                $rowid = 0;
//                if (count($this->elencoFile)) {
//                    $rowid = key(end($this->elencoFile));
//                }
//                $rowid = count($this->elencoFile) + 1;
                end($this->elencoFile);
                $key = key($this->elencoFile);
                $rowid = $key + 1;
            } else {
                continue;
            }
            $fileJson = $appsTempPath . '/' . pathinfo($obj, PATHINFO_FILENAME) . '.json';
            if (!file_exists($fileJson)) {
                $fileJson = '';
            }

            $fileArr = array(
                'ROWID' => $rowid,
                'FILENAME' => $obj,
                'TIPO' => '',
                'FILEPATH' => $appsTempPath . '/' . $obj,
                'PROTOCOLLO' => "",
                'FILEINFO' => 'Scansione massiva',
                'FILEJSON' => $fileJson
            );
            $this->elencoFile[$rowid] = $fileArr;
        }
        closedir($dh);
        return true;
    }

    private function visualizzaCampi() {
        Out::valore($this->nameForm . '_Filename', $this->elencoFile[$this->nElemento]['FILENAME']);
        Out::valore($this->nameForm . '_Descrizione', $this->elencoFile[$this->nElemento]['FILEINFO']);
        Out::valore($this->nameForm . '_Elementi', $this->nElemento . ' di ' . count($this->elencoFile));
        $fileInfo = $this->elencoFile[$this->nElemento]['FILEJSON'];
        $arrInfo = json_decode(file_get_contents($fileInfo), true);
        $infoHtml = '';
        $infoHtml .= ($arrInfo['data_acquisizione']) ? '<span style="font-weight:bold">Data: </span>' . $arrInfo['data_acquisizione'] : '';
        $infoHtml .= ($arrInfo['ora_acquisizione']) ? '<span style="font-weight:bold"> Ora: </span>' . $arrInfo['ora_acquisizione'] : '';
        $infoHtml .= ($arrInfo['idProtocollo']) ? '<span style="font-weight:bold">Bar-Code: </span>' . $arrInfo['idProtocollo'] : '';
        $infoHtml .= ($arrInfo['numeroPagine']) ? '<span style="font-weight:bold"> Pagine: </span>' . $arrInfo['numeroPagine'] . '<br/>' : '';
        $infoHtml .= ($arrInfo['extraInfo']['messaggio_acquisizione']) ? '<span style="font-weight:bold">Info: </span>' . $arrInfo['extraInfo']['messaggio_acquisizione'] : '';

        Out::html($this->nameForm . '_InfoAcquisizione', $infoHtml);
        Out::valore($this->nameForm . '_Numero', substr($this->elencoFile[$this->nElemento]['PROTOCOLLO'], 4));
        Out::valore($this->nameForm . '_Anno', substr($this->elencoFile[$this->nElemento]['PROTOCOLLO'], 0, 4));

        Out::valore($this->nameForm . '_Oggetto', '');
        Out::html($this->nameForm . '_Messaggio_protocollo', '');
        Out::hide($this->nameForm . '_Messaggio_protocollo');

        $retPrecondizioni = $this->getPrecondizioniAbbinamanto($this->elencoFile[$this->nElemento]);
        $protocollo = $retPrecondizioni['protocollo'];
        if ($protocollo) {
            $Anaogg_rec = $protocollo->getAnaogg_rec();
            Out::valore($this->nameForm . '_Oggetto', $Anaogg_rec['OGGOGG']);
        }

        if ($retPrecondizioni['contenuto_messaggio']) {
            Out::html($this->nameForm . '_Messaggio_protocollo', $retPrecondizioni['contenuto_messaggio']);
            Out::show($this->nameForm . '_Messaggio_protocollo');
//            $this->elencoFile[$this->nElemento]['FILEERRICON'] = itaComponents::getHtmlIcon('ui-icon ui-icon-alert', 24, 'red', $retPrecondizioni['contenuto_messaggio']);
//            $this->elencoFile[$this->nElemento]['FILEERRINFO'] = -1;
            $this->caricaTabella();
        } else {
//            $this->elencoFile[$this->nElemento]['FILEERRICON'] = '';
//            $this->elencoFile[$this->nElemento]['FILEERRINFO'] = 0;
            $this->caricaTabella(false);
        }
    }

    private function Modifica() {
        Out::show($this->nameForm . "_Precedente");
        Out::show($this->nameForm . "_Successivo");
        Out::show($this->nameForm . "_Elementi");
        $this->visualizzaCampi();
        $filePath = $this->elencoFile[$this->nElemento]['FILEPATH'];
        $baseName = basename($filePath);
        include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
        $url = utiDownload::getUrl($baseName, $filePath);

        switch (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            case 'jpg':
            case 'png':
            case 'gif':
                Out::html($this->nameForm . "_divURL", '');
                Out::hide($this->nameForm . "_divURL");
                Out::attributo($this->nameForm . "_Immagine", 'src', '0', $url);
                Out::show($this->nameForm . "_divImmagine");
                break;
            case 'pdf':
                Out::hide($this->nameForm . "_divImmagine");
                Out::attributo($this->nameForm . "_Immagine", 'src', '0', "");
                Out::show($this->nameForm . "_divURL");
                Out::html($this->nameForm . '_divURL', '<embed src="' . $url . '" width="99%" height="490">');
                break;
        }
        Out::setFocus('', $this->nameForm . '_Numero');
    }

    private function AssegnaProtocollo($numero, $anno) {
        $this->elencoFile[$this->nElemento]['PROTOCOLLO'] = $anno . $numero;
        $this->elencoFile[$this->nElemento]['PROTOCOLLOVIEW'] = $numero . '/' . $anno;
    }

    private function PulisciProtocollo() {
        $this->elencoFile[$this->nElemento]['PROTOCOLLO'] = '';
        $this->elencoFile[$this->nElemento]['PROTOCOLLOVIEW'] = '';
    }

    private function AssegnaDescrizione($descrizione) {
        $this->elencoFile[$this->nElemento]['FILEINFO'] = $descrizione;
    }

    private function caricaOcr($tipoOcr) {
        $ocrPath = $this->proLib->SetDirectory('', "ABBINAOCR");
        if (!$dh = @opendir($ocrPath . 'Ocr'))
            return false;
        $rowid = count($this->elencoFile);
        $nomefile = "ocr_";
        $incrementale = 1;
        $estensione = ".xml";
        $protocollo = '';
        /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * DA SISTEMARE LA LETTURA DEI FILE NON IN MANIERA SEQUENZIALE DAL NUMERO '1' MA DAL + PICCOLO 
          !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
        while (is_file($ocrPath . 'Ocr/' . $nomefile . $incrementale . $estensione) == true) {
            $file = $ocrPath . 'Ocr/' . $nomefile . $incrementale . $estensione;
            $arrayXml = $this->leggiXml($file);


            $barcodes = $this->getDatiXml($tipoOcr, $arrayXml);

            foreach ($barcodes as $barcode) {
//                if (isset($barcode['value']['@textNode'])) {
//                    $codiceProt = $barcode['value']['@textNode'];
                if ($barcode) {
                    $textNodeExplode = explode('@', $barcode);
                    if (substr($textNodeExplode[1], 1) != '') {
                        $protocollo = substr($textNodeExplode[1], 1);
                    }
                }
            }
            if ($protocollo == '') {
                $incrementale += 1;
                continue;
            }
            $nomeAllegato = 'img_' . $incrementale . '.pdf';
            if (is_file($ocrPath . 'Img/' . $nomeAllegato) == true) {
                $allegato = $ocrPath . 'Img/' . $nomeAllegato;
                $rowid += 1;
                $this->elencoFile[$rowid] = array(
                    'ROWID' => $rowid,
                    'FILENAME' => $nomeAllegato,
                    'FILEPATH' => $allegato,
                    'TIPO' => 'ocr',
                    'INCREMENTALE' => $incrementale,
                    'PROTOCOLLO' => $protocollo,
                    'FILEINFO' => "Allegato da Scanner: " . $nomeAllegato,
                    'FILEJSON' => "Allegato da Scanner: " . $nomeAllegato
                );
            }
            $incrementale += 1;
        }
        closedir($dh);
    }

    private function leggiXml($file) {
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($file);
        $arrayXml = $xmlObj->getArray();
        return $arrayXml;
    }

    private function getDatiXml($tipoOcr, $arrayXml) {
        $barcodes = array();
        switch ($tipoOcr) {
            case '1':
                $elencoBarcode = $arrayXml['ocr']['document']['barcode_field']['sub_field'];
                if (!isset($elencoBarcode[0])) {
                    $barcodes[] = $elencoBarcode['value']['@textNode'];
                } else {
                    foreach ($elencoBarcode as $barcode) {
                        $barcodes[] = $barcode['value']['@textNode'];
                    }
                }
                break;
            case '2':
                $elencoBarcode = $arrayXml['boost_serialization']['ocr']['documents']['item']['barcodeFields']['item'];
                foreach ($elencoBarcode as $barcode) {
                    if (!isset($barcode['elements']['item'][0])) {
                        $barcodes[] = $barcode['elements']['item']['value']['@textNode'];
                    } else {
                        $nBarcodes = $barcode['elements']['item'];
                        foreach ($nBarcodes as $nBarcode) {
                            $barcodes[] = $nBarcode['value']['@textNode'];
                        }
                    }
                }
                break;
        }
//        App::log('$barcodes');
//        App::log($barcodes);
        return $barcodes;
    }

    private function CancellaRiga($rowid) {
        if ($this->elencoFile[$rowid]['TIPO'] == 'ocr') {
            Out::msgStop("Abbina Allegati", "Non è possibile cancellare questo file.");
        } else {
            if (!@unlink($this->elencoFile[$rowid]['FILEPATH'])) {
                Out::msgStop("Abbina Allegati", "Errore in cancellazione file.");
            } else {
                if (file_exists($this->elencoFile[$rowid]['FILEJSON'])) {
                    unlink($this->elencoFile[$rowid]['FILEJSON']);
                }

                $fileCancellato = $this->elencoFile[$rowid]['FILEPATH'];
                unset($this->elencoFile[$rowid]);
                $audit_Info = "Cancellazione file: " . $fileCancellato . " da repository di abbinamento.";
                $this->insertAudit($this->PROT_DB, '', $audit_Info, '');
                $this->apriRisultato();
            }
        }
    }

    private function CancellaFileDaAbbinare($rowid) {
        if ($this->elencoFile[$rowid]['TIPO'] == 'ocr') {
            return false;
        } else {
            if (!@unlink($this->elencoFile[$rowid]['FILEPATH'])) {
                return false;
            }
            if (file_exists($this->elencoFile[$rowid]['FILEJSON'])) {
                if (!@unlink($this->elencoFile[$rowid]['FILEJSON'])) {
                    return false;
                }
            }
        }
        return true;
    }

    private function elaboraRecords($elencoFiles) {
        foreach ($elencoFiles as $fileKey => $file) {
            $arrInfo = array();
            if ($file['FILEJSON']) {
                $arrInfo = json_decode(file_get_contents($file['FILEJSON']), true);
            }
            $elencoFiles[$fileKey]['FILEINFOICON'] = itaComponents::getHtmlIcon('ui-icon ui-icon-info', 24, 'black', $arrInfo['extraInfo']['messaggio_acquisizione']);
            $elencoFiles[$fileKey]['PROTOCOLLO_FORMATTED'] = ($elencoFiles[$fileKey]['PROTOCOLLO']) ? substr($elencoFiles[$fileKey]['PROTOCOLLO'], 4) . ' / ' . substr($elencoFiles[$fileKey]['PROTOCOLLO'], 0, 4) : '';
            // Contorllo precondizioni abbinamento
            /* SE FILE NON ABBINATO CONTROLLO PRECONDIZIONI ABBINAMENTO */
            if ($elencoFiles[$fileKey]['FILEERRINFO'] != 1) {
                $retPrecondizioni = $this->getPrecondizioniAbbinamanto($elencoFiles[$fileKey]);
                if ($retPrecondizioni['contenuto_messaggio']) {
                    $elencoFiles[$fileKey]['FILEERRICON'] = itaComponents::getHtmlIcon('ui-icon ui-icon-alert', 24, 'red', $retPrecondizioni['contenuto_messaggio']);
                    $elencoFiles[$fileKey]['FILEERRINFO'] = -1;
                } else {
                    $elencoFiles[$fileKey]['FILEERRICON'] = '';
                    $elencoFiles[$fileKey]['FILEERRINFO'] = 0;
                }
            } else {
                $elencoFiles[$fileKey]['FILEERRICON'] = itaComponents::getHtmlIcon('ui-icon ui-icon-check', 24, 'green');
            }
        }
        return $elencoFiles;
    }

    private function apriRisultato($caricaTabella = true) {
        $this->CreaCombo();
        $this->caricaTabella($caricaTabella);
        Out::show($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Modifica');
        Out::show($this->nameForm . '_Abbina');
        Out::show($this->nameForm . '_divOcr');
        Out::attributo($this->nameForm . "_Immagine", 'src', '0', '');
        Out::valore($this->nameForm . '_Numero', '');
        Out::valore($this->nameForm . '_Anno', '');
        Out::hide($this->nameForm . "_Precedente");
        Out::hide($this->nameForm . "_Successivo");
        Out::hide($this->nameForm . "_Elementi");
    }

    private function getPrecondizioniAbbinamanto($allegato) {
        $retArray = array(
            'status' => false,
            'titolo_messaggio' => '',
            'contenuto_messaggio' => '',
            'Anadoc_rec' => false,
            'protocoollo' => null,
            'destinazione' => ''
        );

        $numeroProtocollo = $allegato['PROTOCOLLO'];
        if ($numeroProtocollo == '') {
            return $retArray;
        }

        $Anapro_rec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', '', " (PROPAR='A' OR PROPAR='P' OR PROPAR='C')");
        if (!$Anapro_rec) {
            $retArray['titolo_messaggio'] = 'Archiviazione File';
            $retArray['contenuto_messaggio'] = "Errore Protocollo N. $numeroProtocollo non trovato.";
            return $retArray;
        }

        $model = 'proProtocollo.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

        $protocollo = proProtocollo::getInstanceForRowid($this->proLib, $Anapro_rec['ROWID']);
        if (!$protocollo) {
            $retArray['titolo_messaggio'] = 'Archiviazione File';
            $retArray['contenuto_messaggio'] = 'Oggetto Protocollo non istanziato.';
            return $retArray;
        }
        $Anapro_rec = $protocollo->getAnapro_rec();
        $retArray['Anapro_rec'] = $Anapro_rec;
        $retArray['protocollo'] = $protocollo;

        $destinazione = $this->proLib->SetDirectory($numeroProtocollo, $Anapro_rec['PROPAR']);
        if (!$destinazione) {
            $retArray['titolo_messaggio'] = 'Archiviazione File';
            $retArray['contenuto_messaggio'] = "Errore nella cartella di destinazione: non definita.";
            return $retArray;
        }

        $retArray['destinaione'] = $destinazione;

        $Allegati_tab = $protocollo->getAllegati_tab();
        if ($allegato['FILEERRINFO'] != 1) {
            if (count($Allegati_tab) > 0) {
                $retArray['titolo_messaggio'] = 'Archiviazione File';
                $retArray['contenuto_messaggio'] = "Allegati già presenti per il protocollo  N. $numeroProtocollo .";
                return $retArray;
            }
        }

        $retArray['status'] = true;
        return $retArray;
    }

    private function cercaFile($fileName, $array) {
        foreach ($array as $key => $val) {
            if ($val['FILENAME'] === $fileName) {
                return $key;
            }
        }
        return null;
    }

    private function addErrore($titolo, $contenuto) {
        $audit_Info = "Errore abbinamento. $titolo: $contenuto";
        $this->insertAudit($this->PROT_DB, '', $audit_Info);

        $this->arrErroriAbbina[] = array(
            "TITOLO" => $titolo,
            "MESSAGGIO" => $contenuto
        );
    }

}
