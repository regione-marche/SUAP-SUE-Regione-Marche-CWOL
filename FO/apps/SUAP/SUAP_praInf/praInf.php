<?php

require_once ITA_SUAP_PATH . '/SUAP_praInf/praSchedaTemplate.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praElenco.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praReq.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praAdempi.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praNor.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praResp.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praTer.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praInq.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praOneri.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praModu.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praDis.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praProcCorr.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praAlle.class.php';
require_once ITA_SUAP_PATH . '/SUAP_praInf/praLink.class.php';

class praInf extends itaModelFO {

    public $praErr;
    public $praLib;
    public $praLibEventi;
    public $PRAM_DB;
    public $PRAM_DB_R;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
            $this->praLibEventi = new praLibEventi();

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->PRAM_DB_R = $this->praLib->GetPramMaster($this->PRAM_DB);
        } catch (Exception $e) {
            
        }
    }

    private function verificaAccorpaRichiesta($numeroRichiesta) {
        if (strlen($numeroRichiesta) != 10) {
            return false;
        }

        $Proric_rec = $this->praLib->GetProric(addslashes($numeroRichiesta), 'codice', $this->PRAM_DB);
        if (!$Proric_rec || $Proric_rec['RICFIS'] != frontOfficeApp::$cmsHost->getCodFisFromUtente()) {
            return false;
        }

        $Ricite_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . addslashes($numeroRichiesta) . "' AND ITERICUNI = '1'", false);
        if (!$Ricite_rec) {
            return false;
        }

        if ($this->praLib->checkEsecuzionePasso($Proric_rec, $Ricite_rec)) {
            return false;
        }

        return true;
    }

    public function parseEvent() {
        output::$html_out = '';

        parent::parseEvent();

        if (!frontOfficeApp::$cmsHost->autenticato()) {
            $this->config['Elenco'] = 0;
        }

        if (isset($this->request['accorpa'])) {
            if (!$this->verificaAccorpaRichiesta($this->request['accorpa'])) {
                $this->request['accorpa'] = false;
            } else {
                $html = new html;
                $Proric_rec = $this->praLib->GetProric(addslashes($this->request['accorpa']), 'codice', $this->PRAM_DB);
                $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], 'codice', $this->PRAM_DB);

                $textRichiesta = intval(substr($Proric_rec['RICNUM'], 4)) . '/' . substr($Proric_rec['RICNUM'], 0, 4) . ' - ';
                $textRichiesta .= $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4'];
                $textAlert = 'Si sta selezionando un procedimento che andrà accorpato alla seguente richiesta:<br><b>' . $textRichiesta . '</b>';

                if ($this->config['online_page']) {
                    $urlRichiesta = ItaUrlUtil::GetPageUrl(array('p' => $this->config['online_page'], 'event' => 'navClick', 'ricnum' => $Proric_rec['RICNUM'], 'direzione' => 'primoAcc'));
                    $textAlert .= '<br><br>' . $html->getButton('<i class="icon ion-reply italsoft-icon"></i><span>Torna alla richiesta</span>', $urlRichiesta);
                }

                output::addAlert($textAlert, 'Procedimento da accorpare', 'info');
            }
        }

        switch ($this->request['event']) {
            default:
            case 'openBlock':
                $dati = $this->prendiDati();

                if (!$dati) {
                    break;
                }

                $this->disegnaPagina($dati);
                break;

            case 'vediAllegato':
                $Anapra_rec = $this->praLib->GetAnapra(addslashes($this->request['procedi']), 'codice', $this->PRAM_DB);
                $repositoryUrl = ITA_PROC_REPOSITORY;

                if ($Anapra_rec['PRASLAVE'] != 1) {
                    $PRAM_SOURCE = $this->PRAM_DB;
                    $repositoryUrl = ITA_PROC_REPOSITORY;
                } elseif ($Anapra_rec['PRASLAVE'] == 1 && $this->PRAM_DB_R !== $this->PRAM_DB && ITA_MASTER_REPOSITORY) {
                    $PRAM_SOURCE = $this->PRAM_DB_R;
                    $repositoryUrl = ITA_MASTER_REPOSITORY;
                }

                switch ($this->request['type']) {
                    case 'nor':
                        $cartella = 'normativa';
                        $fileOpen = $this->request['normativa'];
                        break;

                    case 'req':
                        $cartella = 'requisiti';
                        $fileOpen = $this->request['requisito'];
                        break;

                    case 'dis':
                        $cartella = "discipline";
                        $fileOpen = $this->request['disciplina'];
                        break;

                    case 'doc':
                        $cartella = 'allegati/' . $Anapra_rec['PRANUM'];
                        $fileOpen = $this->request['allegato'];
                        break;

                    case 'all':
                        $repositoryUrl = ITA_PROC_REPOSITORY;
                        $cartella = 'allegati/' . $Anapra_rec['PRANUM'];
                        $fileOpen = $this->request['allegato'];
                        break;
                }

                $fileOpen = frontOfficeApp::decrypt($fileOpen);
                $file = $repositoryUrl . $cartella . "/" . $fileOpen;

                if (!file_exists($file)) {
                    output::addAlert("L'allegato informativo del procedimento " . $this->request['procedi'] . " non è stato trovato.", 'Attenzione', 'error');
                    break;
                }

                $taskEncrypt = false;
                $Anatsp_rec = $this->praLib->GetAnatsp(addslashes($this->request['sportello']), 'codice', $this->PRAM_DB);
                if (pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                    if ($Anatsp_rec['TSPBLOCK'] == 1) {
                        if (!$tempFolder = $this->praLib->getCartellaTemporaryPratiche()) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . $this->request['ricnum'], __CLASS__);
                            return false;
                        }

                        $file_enc = $tempFolder . pathinfo($this->request['allegato'], PATHINFO_FILENAME) . "-enc-" . time() . ".pdf";
                        if (ITA_JVM_PATH != "" && file_exists(ITA_JVM_PATH)) {
                            $xmlEncrypt = $file_enc . ".xml";
                            $FileXml = fopen($xmlEncrypt, "w");
                            if (!file_exists($xmlEncrypt)) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0060', "File $xmlEncrypt non trovato", __CLASS__);
                                return false;
                            } else {
                                $input = $file;
                                $output = $file_enc;
                                $xml = $this->praLib->CreaXmlEncrypt($input, $output);
                                fwrite($FileXml, $xml);
                                fclose($FileXml);
                                exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlEncrypt ", $ret);
                                foreach ($ret as $value) {
                                    $arrayExec = explode("|", $value);
                                    if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                                        $taskEncrypt = true;
                                        break;
                                    }
                                }
                                unlink($xmlEncrypt);
                            }
                        }
                    }
                }

                if ($taskEncrypt == true) {
                    if ($file_enc) {
                        $this->frontOfficeLib->vediAllegato($file_enc);
                        unlink($file_enc);
                    } else {
                        output::addAlert("L'allegato informativo del procedimento " . $this->request['procedi'] . " non è stato trovato.", 'Attenzione', 'error');
                        break;
                    }
                } else {
                    if ($file) {
                        $this->frontOfficeLib->vediAllegato($file);
                    }
                    output::addAlert("L'allegato informativo del procedimento " . $this->request['procedi'] . " non è stato trovato.", 'Attenzione', 'error');
                    break;
                }
                break;
        }

        return output::$html_out;
    }

    public function prendiDati() {
        $dati = array();

        if ($this->request['procedi'] == "" && frontOfficeApp::$cmsHost->getUserName() == "") {
            return false;
        }

        if (!$this->request['procedi']) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0032', "Codice procedimento mancante", __CLASS__);
            return false;
        }

        $dati['Codice'] = str_pad($this->request['procedi'], 6, "0", STR_PAD_LEFT);

        $Anapra_rec = $this->praLib->GetAnapra(addslashes($dati['Codice']), 'codice', $this->PRAM_DB);

        if (!$Anapra_rec) {
            //praInf::$html_out = $this->praErr->parseError(__FILE__, 'E0033', "Record procedimento ".$dati['Codice']." mancane su ANAPRA", __CLASS__);
            return false;
        }

        $codice_tipologia = $Anapra_rec['PRATIP'];
        $codice_settore = $Anapra_rec['PRASTT'];
        $codice_attivita = $Anapra_rec['PRAATT'];
        $codice_sportello = $Anapra_rec['PRATSP'];

        $evento_procedimento = $this->request['subproc'];
        $eventoid_procedimento = $this->request['subprocid'];

        if ($eventoid_procedimento) {
            $Iteevt_rec = $this->praLib->GetIteevt(addslashes($eventoid_procedimento), 'rowid', $this->PRAM_DB);
            if ($Iteevt_rec) {
                $codice_tipologia = $Iteevt_rec['IEVTIP'];
                $codice_settore = $Iteevt_rec['IEVSTT'];
                $codice_attivita = $Iteevt_rec['IEVATT'];
                $codice_sportello = $Iteevt_rec['IEVTSP'];
            }
        }

        if ($this->request['settore']) {
            $codice_settore = $this->request['settore'];
        }

        if ($this->request['attivita']) {
            $codice_attivita = $this->request['attivita'];
        }

        $repositoryUrl = ITA_PROC_REPOSITORY;
        $PRAM_SOURCE = $this->PRAM_DB;

        if ($Anapra_rec['PRASLAVE'] == 1 && $this->PRAM_DB_R !== $this->PRAM_DB && ITA_MASTER_REPOSITORY) {
            $PRAM_SOURCE = $this->PRAM_DB_R;
            $repositoryUrl = ITA_MASTER_REPOSITORY;
        }

        $Itepas_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, "SELECT * FROM ITEPAS WHERE ITECOD = '" . $dati['Codice'] . "'", true);
        $sql = "SELECT * FROM ANASET WHERE SETCOD='" . addslashes($codice_settore) . "'";
        $Anaset_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $sql = "SELECT * FROM ANAATT WHERE ATTCOD='" . addslashes($codice_attivita) . "'";
        $Anaatt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        
        $sql = "SELECT * FROM ANATIP WHERE TIPCOD='" . addslashes($codice_tipologia) . "'";
        $Anatip_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $sql = "SELECT * FROM ANATSP WHERE TSPCOD='" . $codice_sportello . "'";
        $Anatsp_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $sql = "SELECT * FROM ANAUNI WHERE UNIADD='" . $Anapra_rec['PRARES'] . "'";
        $Anauni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $Anpdoc_rec = ItaDB::DBSQLSelect($PRAM_SOURCE, "SELECT * FROM ANPDOC WHERE ANPKEY='" . $Anapra_rec['PRANUM'] . "' AND ANPCLA = 'DOC'", false);

        $dati['Anapra_rec'] = $Anapra_rec;
        $dati['Iteevt_rec'] = $Iteevt_rec;
        $dati['Anaset_rec'] = $Anaset_rec;
        $dati['Anaatt_rec'] = $Anaatt_rec;
        $dati['Anatip_rec'] = $Anatip_rec;
        $dati['Itepas_tab'] = $Itepas_tab;
        $dati['Anatsp_rec'] = $Anatsp_rec;
        $dati['Anpdoc_rec'] = $Anpdoc_rec;
        $dati['Anauni_rec'] = $Anauni_rec;
        $dati['fiscale'] = frontOfficeApp::$cmsHost->getCodFisFromUtente();

        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');
        /* @var $praLibEventi praLibEventi */
        $praLibEventi = new praLibEventi();
        $dati['Oggetto'] = $praLibEventi->getOggetto($this->PRAM_DB, $Anapra_rec, $Iteevt_rec);
        $dati_compila_href = array(
            'p' => $this->config['online_page'],
            'event' => 'openBlock',
            'procedi' => $dati['Anapra_rec']['PRANUM']
        );

        if ($evento_procedimento) {
            $dati_compila_href['subproc'] = $evento_procedimento;
            $dati_compila_href['subprocid'] = $this->praLib->getEventId($dati['Codice'], $evento_procedimento, $eventoid_procedimento, $this->PRAM_DB); //$eventoid_procedimento;
            $dati_compila_href['settore'] = $codice_settore;
            $dati_compila_href['attivita'] = $codice_attivita;
        }

        if ($this->request['accorpa']) {
            $dati_compila_href['accorpa'] = $this->request['accorpa'];
        }

        $dati['CompilaHref'] = ItaUrlUtil::GetPageUrl($dati_compila_href);

        $dati['repositoryUrl'] = $repositoryUrl;
        $dati['pramSource'] = $PRAM_SOURCE;

        /*
         * Dizionario procedimento PRAINF
         */

        require_once(ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
        $praVar = new praVars();
        $praVar->setPRAM_DB($this->PRAM_DB);
        $praVar->setDati($dati);
        $praVar->loadVariabiliInformativa();
        
        $dati['Dizionario_Procedimento'] = $praVar->getVariabiliProcedimento();

        return $dati;
    }

    public function disegnaPagina($dati) {
        //$html = new html();

        output::addForm('form1', 'praInf.php');
        output::appendHtml("<div class=\"divInfo\">");

        //
        // DIV DESCRIZONE PROCEDIMENTO 
        //
        output::appendHtml('<div class="ui-widget ui-widget-content ui-corner-all infoHead">'); //1
        output::appendHtml('<div style="display: inline-block; padding: 1em; max-width: 600px;">'); //2
        output::appendHtml('<span class="infoText"><b>Settore di appartenenza</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Anaset_rec']['SETDES'] . '</span>');
        output::appendHtml('<span class="infoText"><b>Tipo di attivita\'</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Anaatt_rec']['ATTDES'] . '</span>');
        output::appendHtml('<span class="infoText"><b>Oggetto della domanda</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Oggetto'] . '</span>');
        output::appendHtml('</div>'); // \2

        output::openTag('div', array('style' => 'float: right;'));

        /*
         * <div> Modello informativo
         */
        if ($dati['Anpdoc_rec']) {
            $T_docu = $dati['Anpdoc_rec']['ANPFIL'];
            $Img = frontOfficeLib::getFileIcon($T_docu);
            $allegato = frontOfficeApp::encrypt($T_docu);

            $href = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'vediAllegato',
                    'procedi' => $dati["Anapra_rec"]['PRANUM'],
                    'allegato' => $allegato,
                    'type' => 'doc',
                    "sportello" => $dati['Iteevt_rec']['IEVTSP']
            ));

            output::openTag('div', array('class' => 'divPdfInf', 'style' => 'vertical-align: top;'));
            output::openTag('a', array('href' => $href, 'target' => '_blank'));
            output::addImage($Img, '56px');
            output::addBr(2);
            output::appendHtml('<span class="infoCompile">Modello</span>');
            output::closeTag('a');
            output::closeTag('div');
        }

        /*
         * <div> Compila online
         */
        if ($dati['Itepas_tab']) {
            $href = $dati['CompilaHref'];

            output::openTag('div', array('class' => 'divCompile'));
            output::openTag('a', array('href' => $href));
            output::addImage(frontOfficeLib::getIcon('notepad'), '56px');
            output::addBr(2);
            output::appendHtml('<span class="infoCompile">Compila<br />on-line</span>');
            output::closeTag('a');
            output::closeTag('div');
        }

        output::closeTag('div');

        output::appendHtml('<div style="clear: both;"></div>');

        output::appendHtml("</div>"); //\1
        //
        output::appendHtml("</div>"); //\0
        //
//        output::appendHtml("<div id=\"infoTabs\" class=\"infoTabs\">");
//        output::appendHtml("<ul>");

        $treeViewData = array();

        if ($this->isCPortal && $this->config['Elenco'] == 1) {
            $elencoObj = new praElenco();
            $elencoObj->setConfig($this->config);
            $elencoHtml = $elencoObj->getHtml($dati);
            if ($elencoHtml !== false) {
                output::appendHtml('<div class="italsoft-tabs">');
                output::appendHtml('<ul style="border-left-width: 1px; border-right-width: 1px; margin: 0;">');
                output::appendHtml('<li style="margin: 0;"><a href="#tab-elenco-richieste">Elenco Richieste</a></li>');
                output::appendHtml('<li style="margin: 0;"><a href="#tab-informazioni">Informazioni</a></li>');
                output::appendHtml('</ul>');
                output::appendHtml('<div id="tab-elenco-richieste">');
//                $treeViewData['Elenco Richieste'] = array('childs' => array($elencoHtml => array()));
                output::appendHtml($elencoHtml);
                output::appendHtml('</div>');
                output::appendHtml('<div id="tab-informazioni">');
            }
        }

        if ($this->config['Inquadramento'] == 1) {
            $inqObj = new praInq();
            $inqHtml = $inqObj->getHtml($dati);
            if ($inqHtml !== false) {
                $treeViewData['Inquadramento'] = array('childs' => array($inqHtml => array()), 'active' => true);
            }

//            $siAlle = false;
//            $alleObj = new praAlle();
//            $alleObj->setConfig($this->config);
////            $alleObj->setParam(self::$param);
//            if ($alleObj->getHtml($dati) !== false) {
//                $siAlle = true;
//            }
        }

        if ($this->config['Normativa'] == 1) {
            $norObj = new praNor();
            $norHtml = $norObj->getHtml($dati);
            if ($norHtml !== false) {
                $treeViewData['Normativa'] = array('childs' => array($norHtml => array()));
            }
        }

        if ($this->config['Requisiti'] == 1) {
            $reqObj = new praReq();
            $reqHtml = $reqObj->getHtml($dati);
            if ($reqHtml !== false) {
                $treeViewData['Requisiti'] = array('childs' => array($reqHtml => array()));
            }
        }

        if ($this->config['Adempimenti'] == 1) {
            $adpObj = new praAdempi();
            $adpHtml = $adpObj->getHtml($dati);
            if ($adpHtml !== false) {
                $treeViewData['Adempimenti'] = array('childs' => array($adpHtml => array()));
            }
        }

        if ($this->config['Termini'] == 1) {
            $terObj = new praTer();
            $terHtml = $terObj->getHtml($dati);
            if ($terHtml !== false) {
                $treeViewData['Termini del Procedimento'] = array('childs' => array($terHtml => array()));
            }
        }

        if ($this->config['Oneri'] == 1) {
            $onrObj = new praOneri();
            $onrHtml = $onrObj->getHtml($dati);
            if ($onrHtml !== false) {
                $treeViewData['Oneri'] = array('childs' => array($onrHtml => array()));
            }
        }

        if ($this->config['Responsabile'] == 1) {
            $resObj = new praResp();
            $resHtml = $resObj->getHtml($dati);
            if ($resHtml !== false) {
                $treeViewData['Responsabile'] = array('childs' => array($resHtml => array()));
            }
        }

        if ($this->config['open_accordion'] == 1) {
            $treeViewData[key($treeViewData)]['active'] = 1;
        }

        output::addTreeView($treeViewData, html::TREEVIEW_ACCORDION);


        if ($this->isCPortal && $this->config['Elenco'] == 1 && $elencoHtml !== false) {
            output::appendHtml('</div>'); // #tab-informazioni
            output::appendHtml('</div>'); // #tabs
        }

        output::appendHtml("</form>");

        return true;
    }

}
