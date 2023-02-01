<?php

/**
 *
 * IMPORTAZIONE DELLE PRATICHE NON ANCORA PROTOCOLLATE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Simone Franchi
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    02.06.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';

function praCtrRichiesteFO() {
    $praCtrRichiesteFO = new praCtrRichiesteFO();
    $praCtrRichiesteFO->parseEvent();
    return;
}

class praCtrRichiesteFO extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praCtrRichiesteFO";
    public $divGes = "praCtrRichiesteFO_divGestione";
    public $divRis = "praCtrRichiesteFO_divRisultato";
    public $gridCtrRichieste = "praCtrRichiesteFO_gridCtrRichieste";
    public $gridAllegati = "praCtrRichiesteFO_gridAllegati";
    public $returnModel;
    public $returnEvent;
    public $allegati = array();
    public $allegatiInfocamere = array();
    public $allegatiTabella = array();
    public $prafolist_rec = array();
    public $idPraFoList;
    public $proric_rec = array();
    public $emlInfocamere;
    public $daPortlet;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            /*
             * Istanza risorse oggetti esterni
             */
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();

            /*
             * Rilettura delle varuabili in session
             */
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
            $this->prafolist_rec = App::$utente->getKey($this->nameForm . '_prafolist_rec');
            $this->allegatiTabella = App::$utente->getKey($this->nameForm . '_allegatiTabella');
            $this->idPraFoList = App::$utente->getKey($this->nameForm . '_idPraFoList');
            $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
            $this->allegatiInfocamere = App::$utente->getKey($this->nameForm . '_allegatiInfocamere');
            $this->emlInfocamere = App::$utente->getKey($this->nameForm . '_emlInfocamere');
            $this->daPortlet = App::$utente->getKey($this->nameForm . '_daPortlet');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            /*
             * Salvo variabili in session
             */
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_prafolist_rec', $this->prafolist_rec);
            App::$utente->setKey($this->nameForm . '_allegatiTabella', $this->allegatiTabella);
            App::$utente->setKey($this->nameForm . '_idPraFoList', $this->idPraFoList);
            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
            App::$utente->setKey($this->nameForm . '_allegatiInfocamere', $this->allegatiInfocamere);
            App::$utente->setKey($this->nameForm . '_emlInfocamere', $this->emlInfocamere);
            App::$utente->setKey($this->nameForm . '_daPortlet', $this->daPortlet);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                /*
                 * Inizializzo l'aspetto iniziale della form
                 * e popolo eventuali campi dei default
                 */
//                $this->CreaCombo();
                $this->allegati = $this->allegatiInfocamere = array();
                $this->idPraFoList = $this->emlInfocamere = "";
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->CaricaRichieste();
                $this->CreaComboTipi();
                $this->CreaComboStimoli();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridCtrRichieste:
                        $this->daPortlet = $_POST['daPortlet'];
                        if ($this->returnModel == "") {
                            $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                            $this->returnEvent = $_POST[$this->nameForm . "_returnEvent"];
                        }
                        $this->allegatiInfocamere = $_POST["allegatiInfocamere"];
                        $this->emlInfocamere = $_POST["emlInfocamere"];
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAllegati:
                        $prafolist_rec = $this->praLib->GetPrafolist($this->idPraFoList);
                        $arrAllegato = praFrontOfficeManager::getAllegatoRichiesta($prafolist_rec, $_POST['rowid'], $this->allegatiInfocamere);
                        if (!$arrAllegato) {
                            Out::msgStop("Apertura Allegato", praFrontOfficeManager::$lasErrMessage);
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl(
                                        $arrAllegato['FILENAME'], $arrAllegato['DATAFILE'], true
                                )
                        );

                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $this->elaboraRecord($Result_tab1);
                $ita_grid02 = new TableView($this->gridCtrRichieste, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->setSortIndex('FOTIPO');
                $ita_grid02->exportXLS('', 'procedimenti_online.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridCtrRichieste:
                        $this->CaricaRichieste();
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praCtrProc', $parameters);
                break;
            case 'cellSelect': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $prafolist_rec = $this->praLib->GetPrafolist($this->idPraFoList);
                        $allegatoFirmato = praFrontOfficeManager::getAllegatoRichiesta($prafolist_rec, $_POST['rowid'], $this->allegatiInfocamere);
                        if (!$allegatoFirmato) {
                            Out::msgStop("Visualizza Firme", praFrontOfficeManager::$lasErrMessage);
                            break;
                        }
                        $ext = pathinfo($allegatoFirmato['FILENAME'], PATHINFO_EXTENSION);
                        if (strtolower($ext) == "p7m") {
                            $model = "utiP7m";
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $_POST['event'] = "openform";
                            $_POST['file'] = $allegatoFirmato['DATAFILE'];
                            $_POST['fileOriginale'] = $allegatoFirmato['FILENAME'];
                            $model();
                        }
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {

                    case $this->nameForm . '_Carica':
                    case $this->nameForm . '_ConfermaCarica':
                        //$prafolist_rec = $this->praLib->GetPrafolist($this->formData[$this->nameForm . '_PRAFOLIST']['ROW_ID']);
                        $prafolist_rec = $this->praLib->GetPrafolist($_POST[$this->nameForm . '_PRAFOLIST']['ROW_ID']);
                        if (!$prafolist_rec) {
                            Out::msgStop("Errore di acquisizione", "Record principale non trovato.");
                            break;
                        }

                        /*
                         * Check pre-conditions parametriche per tipo FO
                         *
                         */
                        $retPreconditions = $this->checkPreconditions($prafolist_rec);

                        /**
                         * Se uscita è negativa significa che non ci sono possibilità di soddisfare le precondizioni
                         *
                         * Nel Messaggio si descrive il problema.
                         *
                         */
                        if (!$retPreconditions) {
                            break;
                        }


                        $ret_esito = praFrontOfficeManager::caricaRichiesta($prafolist_rec, $_POST['datiMail']['Dati'], $this->allegatiInfocamere);
                        if ($ret_esito === false) {
                            Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
                            break;
                        }

//                        switch ($prafolist_rec['FOTIPO']) {
//                            case praFrontOfficeManager::TYPE_BO_ITALSOFT_WS:
//                                praFrontOfficeManager::openFormPraGestDatiEssenziali($prafolist_rec);
//                                break;
//                            case praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL:
//                            case praFrontOfficeManager::TYPE_FO_ITALSOFT_WS:
//                                $proric_rec = praFrontOfficeManager::getProric($prafolist_rec);
//                                if ($proric_rec['RICSTA'] == "91" && !$this->allegatiInfocamere) {
//                                    Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
//                                        'F8-No' => array('id' => $this->nameForm . '_NoConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                                        'F5-Si' => array('id' => $this->nameForm . '_SiConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                            ), "auto", "auto", "false"
//                                    );
//                                } else if ($proric_rec['RICRPA'] || $proric_rec['PROPAK']) {
//                                    $ret_esito = null;
//                                    if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito, $_POST['datiMail']['Dati'])) {
//                                        Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
//                                        break;
//                                    }
//                                } else {
//                                    praFrontOfficeManager::openFormPraGestDatiEssenziali($prafolist_rec);
//                                }
//                                break;
//                            default:
//                                $ret_esito = null;
//                                if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito)) {
//                                    Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
//                                    break;
//                                }
//                                break;
//                        }
                        if ($ret_esito[0]['GESNUM'] || $ret_esito[0]['PROPAK']) {
                            Out::msgInfo("Acquisizione Pratiche", $ret_esito[0]['ExtendedMessageHtml']);
                            $this->returnToParent($ret_esito[0]);
                        }
                        break;

                    case $this->nameForm . '_Torna':
                        //$this->CaricaRichieste();
                        Out::show($this->divRis, '');
                        Out::hide($this->divGes, '');
                        //Out::hide($this->nameForm . "_buttonBar", '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Scarica');
                        TableView::enableEvents($this->gridCtrRichieste);
                        break;
                    case $this->nameForm . '_Scarica':
                        $arrayFo = $this->praLib->getArrayTipiFO();

                        if ($arrayFo) {
                            $retScarica = praFrontOfficeManager::scaricaPraticheFO($arrayFo);

                            //                        $FOManager = praFrontOfficeFactory::getFrontOfficeManagerInstance();
                            //                        $FOManager->scaricaPraticheNuove();
                            //                        Out::msgInfo("Elaborazione Terminata", print_r($FOManager->getRetStatus(), true));

                            $this->CaricaRichieste();
                        }


                        break;
                    case $this->nameForm . "_AnnullaConfermaMail":
                    case $this->nameForm . "_NoConfermaMail":
                        if ($this->daPortlet == 'true') {
                            Out::closeDialog($this->nameForm);
                        } else {
                            Out::show($this->divRis);
                            $this->Nascondi();
                            TableView::enableEvents($this->gridCtrRichieste);
                        }
                        break;
                    case $this->nameForm . "_SiConfermaMail":
                        $prafolist_rec = $this->praLib->GetPrafolist($this->idPraFoList);
                        $proric_rec = praFrontOfficeManager::getProric($prafolist_rec);
                        if ($this->daPortlet == 'true') {
                            $arrModel = $this->praLib->getModelCtrRichieste();
                            $modelChiamante = $arrModel['model'];
                        } else {
                            $modelChiamante = $this->returnModel;
                        }
                        $_POST = array();
                        $_POST['model'] = $modelChiamante;
                        $_POST['datiMail']['PRORIC_REC'] = $proric_rec;
                        $_POST['tipoReg'] = 'consulta';
                        $_POST['daPortlet'] = $this->daPortlet;
                        $_POST['rowidChiamante'] = $this->idPraFoList;
                        if ($this->daPortlet == 'true')
                            itaLib::openForm($modelChiamante);
                        $objModel = itaModel::getInstance($modelChiamante);
                        $objModel->setEvent("onClick");
                        $objModel->setElementId($modelChiamante . "_Infocamere");
                        $objModel->parseEvent();
                        Out::desktopTabSelect($modelChiamante);
                        $this->close();
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;

            case 'AssociaProcedimenti':
                $key = $_POST[id];
                $keyProcedimentoStar = str_replace('-', '/', $key);

                $model = 'praFoDecodeGest';
                itaLib::openDialog($model);
                /* @var $modelObj praAssegnaPraticaSimple */
                $modelObj = itaModel::getInstance($model);
                $modelObj->setReturnModel($this->nameForm);
                $modelObj->setReturnEvent('returnAssociaProcedimenti');
                $modelObj->setEvent('openform');
                $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_STAR_WS);
                //Da valorizzare, prendendola da $retDecode, quando si gestisce
                $modelObj->setChiaveFo($keyProcedimentoStar);
                $modelObj->setModifica(false);
                $modelObj->setDialog(true);
                $modelObj->parseEvent();

                break;

            case 'DettaglioProcedimento':
                $key = $_POST[id];
                $keyProcedimentoStar = str_replace('-', '/', $key);

                $model = 'praFoDecodeGest';
                itaLib::openDialog($model);
                /* @var $modelObj praAssegnaPraticaSimple */
                $modelObj = itaModel::getInstance($model);
                $modelObj->setReturnModel($this->nameForm);
                $modelObj->setReturnEvent('returnAssociaProcedimenti');
                //$modelObj->setReturnId($param['returnId']);
                $modelObj->setEvent('openform');
                $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_STAR_WS);
                //Da valorizzare, prendendola da $retDecode, quando si gestisce
                $modelObj->setChiaveFo($keyProcedimentoStar);
                $modelObj->setModifica(true);
                $modelObj->setDialog(true);
                $modelObj->parseEvent();

                break;

            case 'AssociaProcedimentiCart':
                $key = $_POST[id];
                $keyProcedimentoStar = str_replace('-', '/', $key);

                $model = 'praFoDecodeGest';
                itaLib::openDialog($model);
                /* @var $modelObj praAssegnaPraticaSimple */
                $modelObj = itaModel::getInstance($model);
                $modelObj->setReturnModel($this->nameForm);
                $modelObj->setReturnEvent('returnAssociaProcedimenti');
                $modelObj->setEvent('openform');
                $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_CART_WS);
                //Da valorizzare, prendendola da $retDecode, quando si gestisce
                $modelObj->setChiaveFo($keyProcedimentoStar);
                $modelObj->setModifica(false);
                $modelObj->setDialog(true);
                $modelObj->parseEvent();

                break;

            case 'DettaglioProcedimentoCart':
                $key = $_POST[id];
                $keyProcedimentoStar = str_replace('-', '/', $key);

                $model = 'praFoDecodeGest';
                itaLib::openDialog($model);
                /* @var $modelObj praAssegnaPraticaSimple */
                $modelObj = itaModel::getInstance($model);
                $modelObj->setReturnModel($this->nameForm);
                $modelObj->setReturnEvent('returnAssociaProcedimenti');
                //$modelObj->setReturnId($param['returnId']);
                $modelObj->setEvent('openform');
                $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_CART_WS);
                //Da valorizzare, prendendola da $retDecode, quando si gestisce
                $modelObj->setChiaveFo($keyProcedimentoStar);
                $modelObj->setModifica(true);
                $modelObj->setDialog(true);
                $modelObj->parseEvent();

                break;


            case 'returnAssociaProcedimenti':
                $this->Dettaglio($this->idPraFoList);
                break;
            case 'returnDatiEssenziali':
                //$prafolist_rec = $this->praLib->GetPrafolist($this->formData[$this->nameForm . '_PRAFOLIST']['ROW_ID']);
                $prafolist_rec = $_POST['datiMail']['Dati']['PRAFOLIST_REC'];
                if (!$prafolist_rec) {
                    Out::msgStop("Errore di acquisizione", "Record principale non trovato.");
                    break;
                }

                /*
                 * Check pre-conditions parametriche per tipo FO
                 *
                 */
                $retPreconditions = $this->checkPreconditions($prafolist_rec);
                if (!$retPreconditions) {
                    break;
                }

                if ($this->allegatiInfocamere) {
                    $_POST['datiMail']['Dati']['ALLEGATICOMUNICA'] = $this->allegatiInfocamere;
                }
                $_POST['datiMail']['Dati']['daPortlet'] = $this->daPortlet;
                if ($this->emlInfocamere) {
                    $_POST['datiMail']['Dati']['FILENAME'] = $this->emlInfocamere;
                }

                $ret_esito = null;
                //if (!praFrontOfficeManager::caricaFascicoloFromDatiEssenziali($prafolist_rec['ROW_ID'], $_POST['datiMail']['Dati'], $ret_esito)) {
                if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito, $_POST['datiMail']['Dati'])) {
                    Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
                    break;
                }
                if ($ret_esito[0]['GESNUM']) {
                    Out::msgInfo("Acquisizione Pratiche", $ret_esito[0]['ExtendedMessageHtml']);
                    $this->returnToParent($ret_esito[0]);
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_prafolist_rec');
        App::$utente->removeKey($this->nameForm . '_allegatiTabella');
        App::$utente->removeKey($this->nameForm . '_allegatiInfocamere');
        App::$utente->removeKey($this->nameForm . '_daPortlet');
        App::$utente->removeKey($this->nameForm . '_emlInfocamere');
        App::$utente->removeKey($this->nameForm . '_idPraFoList');
        App::$utente->removeKey($this->nameForm . '_proric_rec');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($ret, $close = true) {
        if ($this->returnModel == "")
            $this->returnModel = 'praGestElenco';
        if ($this->returnEvent == "")
            $this->returnEvent = 'returnCtrRichiesteFO';
        $_POST = array();
        $_POST['datiAcquisizione'] = $ret;
        Out::desktopTabSelect($this->returnModel);
        $objModel = itaModel::getInstance($this->returnModel);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Carica');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Scarica');
    }

    public function CaricaRichieste() {
        $this->Nascondi();
        Out::hide($this->divGes, '');
        Out::show($this->divRis, '');
        Out::show($this->nameForm . '_Scarica');
        $sql = $this->CreaSql();
        try {
            $ita_grid01 = new TableView($this->gridCtrRichieste, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $num = $_POST['page'] ? $_POST['page'] : 1;
            $ita_grid01->setPageNum($num);
            $rows = $_POST['rows'] ? $_POST['rows'] : 1000;
            $ita_grid01->setPageRows($rows);
            $sidx = $_POST['sidx'] ? $_POST['sidx'] : 'FOPRADATA';
            if ($sidx == 'SOGGETTI') {
                $sidx = 'FOESIBENTE';
            }
            $ita_grid01->setSortIndex($sidx);
            $sord = $_POST['sord'] ? $_POST['sord'] : 'desc';
            $ita_grid01->setSortOrder($sord);
            $Result_tab = $ita_grid01->getDataArray();
            $Result_tab = $this->elaboraRecord($Result_tab);
            $ita_grid01->getDataPageFromArray('json', $Result_tab);
            TableView::enableEvents($this->gridCtrRichieste);
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    public function CaricaGrigliaAllegati($Result_tab) {
        $Result_tab = $this->elaboraRecordAllegati($Result_tab);
        $ita_grid01 = new TableView($this->gridAllegati, array('arrayTable' => $Result_tab,
            'rowIndex' => 'idx'));

        $num = $_POST['page'] ? $_POST['page'] : 1;
        $ita_grid01->setPageNum($num);
        $rows = $_POST['rows'] ? $_POST['rows'] : 1000;
        $ita_grid01->setPageRows($rows);
        $sidx = $_POST['sidx'] ? $_POST['sidx'] : 'FILEID';
        $ita_grid01->setSortIndex($sidx);
        $sord = $_POST['sord'] ? $_POST['sord'] : 'desc';
        $ita_grid01->setSortOrder($sord);
        TableView::clearGrid($this->gridAllegati);
        TableView::enableEvents($this->gridAllegati);
        $ita_grid01->getDataPage('json');
    }

    public function CreaSql() {
        $sql = "SELECT * FROM PRAFOLIST WHERE FOGESNUM = ''";
        // $sql = "SELECT * FROM PRAFOLIST WHERE 1";
        if ($_POST['_search'] == true) {
            if ($_POST['FOTIPO']) {
                $sql .= " AND FOTIPO = '" . $_POST['FOTIPO'] . "'";
            }
            if ($_POST['FOTIPOSTIMOLO']) {
                $sql .= " AND UPPER(FOTIPOSTIMOLO) LIKE '%" . addslashes(strtoupper($_POST['FOTIPOSTIMOLO'])) . "%'";
            }
            if ($_POST['FOIDPRATICA']) {
                $sql .= " AND UPPER(FOIDPRATICA) LIKE '%" . addslashes(strtoupper($_POST['FOIDPRATICA'])) . "%'";
            }
            if ($_POST['FOPRAKEY']) {
                $sql .= " AND UPPER(FOPRAKEY) LIKE '%" . addslashes(strtoupper($_POST['FOPRAKEY'])) . "%'";
            }
            if ($_POST['FOPRASPACATA']) {
                $sql .= " AND UPPER(FOPRASPACATA) LIKE '%" . addslashes(strtoupper($_POST['FOPRASPACATA'])) . "%'";
            }
            if ($_POST['FOPRADESC']) {
                $sql .= " AND UPPER(FOPRADESC) LIKE '%" . addslashes(strtoupper($_POST['FOPRADESC'])) . "%'";
            }
            if ($_POST['SOGGETTI']) {
                $sql .= " AND ( UPPER(FOESIBENTE) LIKE '%" . addslashes(strtoupper($_POST['SOGGETTI'])) . "%' "
                        . " OR UPPER(FODICHIARANTE) LIKE '%" . addslashes(strtoupper($_POST['SOGGETTI'])) . "%' )";
            }
            if ($_POST['FOALTRORIFERIMENTO']) {
                $sql .= " AND UPPER(FOALTRORIFERIMENTO) LIKE '%" . addslashes(strtoupper($_POST['FOALTRORIFERIMENTO'])) . "%'";
            }
            if ($_POST['FOALTRORIFERIMENTOIND']) {
                $sql .= " AND UPPER(FOALTRORIFERIMENTOIND) LIKE '%" . addslashes(strtoupper($_POST['FOALTRORIFERIMENTOIND'])) . "%'";
            }
        }
        return $sql;
    }

    public function Dettaglio($Indice) {
        $this->CreaCombo();
        $this->idPraFoList = $Indice;
        $prafolist_rec = $this->praLib->GetPrafolist($Indice);
        Out::valori($prafolist_rec, $this->nameForm . '_PRAFOLIST');
        $this->Nascondi();
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_Carica');

        Out::disableField($this->nameForm . '_PRAFOLIST[FOTIPO]');
        Out::disableField($this->nameForm . '_PRAFOLIST[FOPRADESC]');
        Out::disableField($this->nameForm . '_PRAFOLIST[FOPRADATA]');
        Out::disableField($this->nameForm . '_PRAFOLIST[FOPRAORA]');

        $descrizione = praFrontOfficeManager::getDescrizioneGeneraleRichiesta($prafolist_rec, $this->nameForm, $this->allegatiInfocamere);
        Out::html($this->nameForm . '_divSoggetto', $descrizione);

        $allegati = praFrontOfficeManager::getAllegatiRichiesta($prafolist_rec, $this->allegatiInfocamere);
        $this->CaricaGrigliaAllegati($allegati);
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['SOGGETTI'] = $Result_rec['FODICHIARANTE'] . "<br/>" . $Result_rec['FOESIBENTE'];
            $bold = "";
            $color = $this->getColorRow($Result_rec);
            if ($color && $color != "black") {
                $bold = "font-weight:bold;";
            }
            $Result_tab[$key]['FOTIPO'] = "<span style=\"color:$color;$bold\">" . $Result_rec ['FOTIPO'] . "</span>";
            $Result_tab[$key]['FOTIPOSTIMOLO'] = "<span style=\"color:$color;$bold\">" . $Result_rec ['FOTIPOSTIMOLO'] . "</span>";
            $Result_tab[$key]['FOIDPRATICA'] = "<span style=\"color:$color;$bold\">" . $Result_rec ['FOIDPRATICA'] . "</span>";
            $Result_tab[$key]['FOPRAKEY'] = "<span style=\"color:$color;$bold\">" . $Result_rec ['FOPRAKEY'] . "</span>";
        }
        return $Result_tab;
    }

    public function elaboraRecordAllegati($Result_tab) {
        $this->allegati = array();
        foreach ($Result_tab as $key => $Result_rec) {
            if (strtolower(pathinfo($Result_rec['FILEFIL'], PATHINFO_EXTENSION)) == 'p7m') {
                $Result_tab[$key]['FILEFIL'] = "<span class=\"ita-icon ita-icon-shield-blue-24x24\" title=\"Verifica firma\"></span>";
            } else {
                $Result_tab[$key]['FILEFIL'] = "";
            }
            $this->allegati[$Result_rec['ROW_ID']] = $Result_rec;
        }
        return $Result_tab;
    }

    private function CreaCombo() {
        $sql = "SELECT DISTINCT FOTIPO FROM PRAFOLIST";
        $tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);


        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES as $key => $value) {
            //foreach ($tab as $rec) {
            Out::select($this->nameForm . '_PRAFOLIST[FOTIPO]', 1, $key, "0", $value);
        }
    }

    private function getAnno($fotipo, $foprakey) {
        $anno = null;
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        $sql = "SELECT * FROM PRAFOLIST "
                . " WHERE PRAFOLIST.FOTIPO = '" . $fotipo . "'"
                . " AND PRAFOLIST.FOPRAKEY = '" . $foprakey . "'";


        $praFoList_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        if ($praFoList_rec) {
            //Out::msgInfo("Valore FOPRADATA", print_r($praFoList_rec['FOPRADATA'], true));
            $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);
            // Out::msgInfo("Valore Anno", print_r($anno, true));
        }

        return $anno;
    }

    private function checkPreconditions($prafolist_rec) {
        $param = array(
            'returnModel' => $this->nameForm,
            'returnEvent' => 'onClick',
            'returnId' => 'ConfermaCarica',
            'prafolist_rec' => $prafolist_rec
        );

        return praFrontOfficeManager::checkAcqPreconditions($param);
    }

    private function getColorRow($Result_rec) {
        $arrayStimoli = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[$Result_rec['FOTIPO']];
        $indice = $color = "";
        foreach ($arrayStimoli as $keyStimolo => $stimolo) {
            if ($stimolo == $Result_rec['FOTIPOSTIMOLO']) {
                $indice = $keyStimolo;
                break;
            }
        }
        if ($indice) {
            $color = praFrontOfficeManager::$FRONT_OFFICE_COLORS_STIMOLI[$indice];
        }
        return $color;
    }

    private function CreaComboTipi() {
        TableView::tableSetFilterHtml($this->gridCtrRichieste, "FOTIPO", '');

        TableView::tableSetFilterSelect($this->gridCtrRichieste, "FOTIPO", 1, "", '0', "");
        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES as $key => $tipo) {
            TableView::tableSetFilterSelect($this->gridCtrRichieste, "FOTIPO", 1, $key, '0', $key);
        }
    }

    private function CreaComboStimoli() {
        TableView::tableSetFilterHtml($this->gridCtrRichieste, "FOTIPOSTIMOLO", '');

        TableView::tableSetFilterSelect($this->gridCtrRichieste, "FOTIPOSTIMOLO", 1, "", '0', "");
        foreach (praFrontOfficeManager::$FRONT_OFFICE_STIMOLI as $key => $tipo) {
            TableView::tableSetFilterSelect($this->gridCtrRichieste, "FOTIPOSTIMOLO", 1, $key, '0', $tipo);
        }
    }

}
