<?php

/**
 *
 * RICERCA PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    15.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

function proGest() {
    $proGest = new proGest();
    $proGest->parseEvent();
    return;
}

class proGest extends itaModel {

    public $PROT_DB;
    public $ITW_DB;
    public $nameForm = "proGest";
    public $divRic = "proGest_divRicerca";
    public $divRis = "proGest_divRisultato";
    public $gridGest = "proGest_gridGest";
    public $gridRicUffici = "proGest_gridRicUffici";
    public $tipoProt;
    public $proLib;
    public $proLibMail;
    public $proLibTitolario;
    public $accLib;
    public $consultazione;
    public $condizioni;
    public $Dadata;
    public $adata;
    public $workDate;
    public $workYear;
    public $uffici;
    public $proLibAllegati;
    public $proLibFascicolo;
    public $visOggRiservati = '';

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibMail = new proLibMail();
            $this->proLibTitolario = new proLibTitolario();
            $this->accLib = new accLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITW_DB = $this->accLib->getITW();
            $this->consultazione = App::$utente->getKey($this->nameForm . '_consultazione');
            $this->condizioni = App::$utente->getKey($this->nameForm . '_condizioni');
            $this->Dadata = App::$utente->getKey($this->nameForm . '_dadata');
            $this->Adata = App::$utente->getKey($this->nameForm . '_Adata');
            $this->visOggRiservati = App::$utente->getKey($this->nameForm . '_visOggRiservati');
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($this->workDate));
            $this->uffici = App::$utente->getKey($this->nameForm . '_uffici');
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibFascicolo = new proLibFascicolo();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_consultazione', $this->consultazione);
            App::$utente->setKey($this->nameForm . '_condizioni', $this->condizioni);
            App::$utente->setKey($this->nameForm . '_dadata', $this->Dadata);
            App::$utente->setKey($this->nameForm . '_adata', $this->Adata);
            App::$utente->setKey($this->nameForm . '_uffici', $this->uffici);
            App::$utente->setKey($this->nameForm . '_visOggRiservati', $this->visOggRiservati);
        }
    }

    public function getConsultazione() {
        return $this->consultazione;
    }

    public function setConsultazione($consultazione) {
        $this->consultazione = $consultazione;
    }

    public function getCondizioni() {
        return $this->condizioni;
    }

    public function setCondizioni($condizioni) {
        $this->condizioni = $condizioni;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
// ita-edit-uppercase ***
                $anaent_37 = $this->proLib->GetAnaent('37');
                if ($anaent_37['ENTDE3'] == 1) {
                    Out::addClass($this->nameForm . '_Oggetto', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_Mednom', "ita-edit-uppercase");
                }
                $this->CreaCombo();
                $this->OpenRicerca();
                /* Defautl Titolario corrente */
                Out::valore($this->nameForm . '_Versione', $this->proLib->GetTitolarioCorrente());
                Out::valore($this->nameForm . '_Versione_sec', $this->proLib->GetTitolarioCorrente());
                $anaent_56 = $this->proLib->GetAnaent('56');
                if (!$anaent_56['ENTDE5']) {
                    Out::removeElement($this->nameForm . '_divRuolo');
                }

                break;
            case'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridRicUffici:
                        $this->uffici = $_POST[$this->nameForm . '_Uffcod'];
                        proRic::proRicAnauff($this->nameForm);
                        break;
                }
                break;
            case'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridRicUffici:
                        $ExplodeUffici = explode('|', $this->uffici);
                        unset($ExplodeUffici[$_POST['rowid']]);
                        $this->uffici = '';
                        if ($ExplodeUffici) {
                            $this->uffici = implode('|', $ExplodeUffici);
                        }
                        Out::valore($this->nameForm . '_Uffcod', $this->uffici);
                        $this->CaricaRicUffici();
                        break;
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $anaproctr_rec = $this->proLib->GetAnapro($_POST['rowid'], 'rowid');
                        if (!$this->returnModel) {
                            $model = 'proArri';
                            itaLib::openForm($model);
                        } else {
                            $model = $this->returnModel;
                        }
                        if (!$this->returnEvent) {
                            $event = 'openform';
                        } else {
                            $event = $this->returnEvent;
                        }
                        if (!$this->returnId) {
                            $returnId = '';
                        } else {
                            $returnId = $this->returnId;
                        }
                        $this->returnModel = null;
                        $this->returnEvent = null;
                        $this->returnId = null;
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
                        $_POST['event'] = $event;
                        $_POST['id'] = $returnId;
                        $_POST[$this->nameForm . '_ANAPRO']['ROWID'] = $_POST['rowid'];
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        if ($model != 'proArri') {
                            $this->returnToParent();
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
//                $sql = $this->CreaSql();
//                // Elaborazione Array Excel:
//                $ita_grid01 = new TableView($this->gridGest, array(
//                    'sqlDB' => $this->PROT_DB,
//                    'sqlQuery' => $sql));
//                $ita_grid01->setSortIndex('PRONUM');
//                $ita_grid01->exportXLS('', 'Anapro.xls');
                // Campi visivi:
                // Excell pulito di campi inutili. Visualizzati gli stessi in elenco.
                $this->EsportaExcel();
                break;
            case 'printTableToHTML':
                $anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(true),
                    "Ente" => $anaent_rec['ENTDE1'],
                    "Titolo" => "ELENCO RICERCA PROTOCOLLI",
                    "Utente" => App::$utente->getKey('nomeUtente'));
//                $itaJR->runSQLReportPDF($this->PROT_DB, 'proRegistro', $parameters);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proGestRegistro', $parameters);
                $this->svuotaDatiStampa();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $sql = $this->CreaSql();
                        if ($sql != false) {
                            $ordinamento = $_POST['sidx'];
                            $sord = $_POST['sord'];
                            if ($_POST['sidx'] == 'CODICE' || $_POST['sidx'] == 'ANNO' || $_POST['sidx'] == 'PRONUM') {
                                $ordinamento = 'PRODAR DESC,PRONUM';
                            }
                            $ita_grid01 = new TableView($this->gridGest, array('sqlDB' => $this->PROT_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows($_POST['rows']);
                            $ita_grid01->setSortIndex($ordinamento);
                            $ita_grid01->setSortOrder($sord);
// Elabora il risultato
                            $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                            $ita_grid01->getDataPageFromArray('json', $result_tab);
                        }
                        break;
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        if (trim($_POST[$this->nameForm . '_pronum']) == '' || $this->consultazione) {
                            if ($this->controllaCampi()) {
                                $this->elenca();
                            }
                        } else {
                            $pronum = substr(str_pad($_POST[$this->nameForm . '_pronum'], 6, "0", STR_PAD_LEFT), -6);
                            $anno = $_POST[$this->nameForm . '_annopronum'];
                            $tipo = $_POST[$this->nameForm . '_tipo'];
                            $this->vaiAlProtocollo($pronum, $anno, $tipo);
                        }
                        break;
                    case $this->nameForm . '_ConfermaElenca': // Evento bottone Elenca
                        $this->elenca();
                        break;
                    case $this->nameForm . '_Uffcod_butt':
                        $this->uffici = $_POST[$this->nameForm . '_Uffcod'];
                        proRic::proRicAnauff($this->nameForm);
                        break;
                    case $this->nameForm . '_Medcod_butt':
                        $where = '';
                        proRic::proRicAnamed($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_Procat_butt':
                        //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t);
                        break;
                    case $this->nameForm . '_Procat_sec_butt':
//                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, '', 'returnTitolario_sec');
                        $versione_t = $_POST[$this->nameForm . '_Versione_sec'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, '', 'returnTitolario_sec');
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        if ($_POST[$this->nameForm . '_Procat']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_Procat'] . "'");
                        }
//                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where);
                        break;
                    case $this->nameForm . '_Clacod_sec_butt':
                        if ($_POST[$this->nameForm . '_Procat_sec']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_Procat_sec'] . "'");
                        }
//                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolario_sec');
                        $versione_t = $_POST[$this->nameForm . '_Versione_sec'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where, 'returnTitolario_sec');
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        if ($_POST[$this->nameForm . '_Procat']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_Procat'] . "'";
                            if ($_POST[$this->nameForm . '_Clacod']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_Procat']
                                        . $_POST[$this->nameForm . '_Clacod'] . "'";
                            }
                        }
//                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        $versione_t = $_POST[$this->nameForm . '_Versione'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where);
                        break;
                    case $this->nameForm . '_Fascod_sec_butt':
                        if ($_POST[$this->nameForm . '_Procat_sec']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_Procat_sec'] . "'";
                            if ($_POST[$this->nameForm . '_Clacod_sec']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_Procat_sec']
                                        . $_POST[$this->nameForm . '_Clacod_sec'] . "'";
                            }
                        }
//                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolario_sec');
                        $versione_t = $_POST[$this->nameForm . '_Versione_sec'];
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where, 'returnTitolario_sec');
                        break;
                    case $this->nameForm . '_Proarg_butt':
                        $Anno = $_POST[$this->nameForm . '_AnnoFascicolo'];
                        $where = " WHERE (ORGANN='' OR ORGANN='$Anno') AND ORGCCF='" . $_POST[$this->nameForm . '_Procat_sec'] . $_POST[$this->nameForm . '_Clacod_sec'] . $_POST[$this->nameForm . '_Fascod_sec'] . "'";
                        proric::proRicOrg($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Svuota':
                        $this->AzzeraVariabili();
                        break;
                    case $this->nameForm . '_Stampa':
                        devRic::devElencoReport($this->nameForm, $_POST, " WHERE CODICE<>'' AND CATEGORIA='PROTOCOLLIELENCO'");
                        // devRic::devElencoReport_ini($this->nameForm, $_POST, 'PROTOCOLLIELENCO');
                        break;
                    case $this->nameForm . '_NuovoArrivo':
                    case $this->nameForm . '_NuovoParte':
                        $model = 'proArri';
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['event'] = 'openform';
                        $_POST['tipoProt'] = 'A';
                        if ($_POST['id'] == $this->nameForm . '_NuovoParte')
                            $_POST['tipoProt'] = 'P';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_AbbinaAllegati':
// Abbina Allegati
                        $model = 'proAbbinaAlle';
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Comunicazioni':
// Abbina Allegati
                        $model = 'proArri';
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['event'] = 'openform';
                        $_POST['tipoProt'] = 'C';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Richiesta':
//
// Funzione Sospesa
// 
//
                        break;
                        $model = 'proRichiesta';
                        $_POST['consultazione'] = $this->consultazione;
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ReqClient':
//
// Funzione Sospesa
// 
//
                        break;
                        $model = 'proReqClient';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Ruocod_butt':
                        proRic::proRicAnaruoli($this->nameForm);
                        break;
                    case $this->nameForm . '_Proute_butt':
                        accRic::accRicUtenti($this->nameForm);
                        break;

                    case $this->nameForm . '_Tipodoc_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm);
                        break;
                    case $this->nameForm . '_VediOggettiRiservati':
                        $this->visOggRiservati = true;
//                        $this->elenca();
                        TableView::enableEvents($this->gridGest);
                        TableView::reload($this->gridGest);
                        Out::msgInfo("Riservatezza", "Le informazioni dei protocolli riservati sono state rese visibili.");
                        Out::hide($this->nameForm . '_VediOggettiRiservati');
                        break;

                    case $this->nameForm . '_TrasmDest_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnAnamedTrasm');
                        break;

                    case $this->nameForm . '_DocAllaFirma':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAtto();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Procat':
                        $codice = $_POST[$this->nameForm . '_Procat'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacat($codice);
                        break;
                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Procat'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Procat'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacat($codice);
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Procat'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafas($codice1 . $codice2 . $codice3, 'fasccf');
                        } else {
                            $codice = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = $_POST[$this->nameForm . '_Procat'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        }
                        break;

                    case $this->nameForm . '_Procat_sec':
                        $tipoTit = '_sec';
                        $codice = $_POST[$this->nameForm . '_Procat_sec'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacat($codice, 'codice', $tipoTit);
                        break;
                    case $this->nameForm . '_Clacod_sec':
                        $tipoTit = '_sec';
                        $codice = $_POST[$this->nameForm . '_Clacod_sec'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Procat_sec'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2, 'codice', $tipoTit);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Procat_sec'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacat($codice, 'codice', $tipoTit);
                        }
                        break;
                    case $this->nameForm . '_Fascod_sec':
                        $tipoTit = '_sec';
                        $codice = $_POST[$this->nameForm . '_Fascod_sec'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Procat_sec'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod_sec'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafas($codice1 . $codice2 . $codice3, 'fasccf', $tipoTit);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Clacod_sec'];
                            $codice1 = $_POST[$this->nameForm . '_Procat_sec'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2, 'codice', $tipoTit);
                        }
                        break;

                    case $this->nameForm . '_Proarg':
                        $codice = str_pad($_POST[$this->nameForm . '_Proarg'], 6, "0", STR_PAD_LEFT);
                        $codiceCcf = $_POST[$this->nameForm . '_Procat_sec'] . $_POST[$this->nameForm . '_Clacod_sec']
                                . $_POST[$this->nameForm . '_Fascod_sec'];
                        $Anno = $_POST[$this->nameForm . '_AnnoFascicolo'];
                        $this->DecodAnaorg($codice, 'codice', $codiceCcf, $Anno);
                        break;

                    case $this->nameForm . '_Tipodoc':
                        if (!$_POST[$this->nameForm . '_Tipodoc']) {
                            Out::valore($this->nameForm . '_descr_tipodoc', '');
                        } else {
                            $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST[$this->nameForm . '_Tipodoc'], 'codice');
                            if ($AnaTipoDoc_rec) {
                                Out::valore($this->nameForm . '_Tipodoc', $AnaTipoDoc_rec['CODICE']);
                                Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
                            } else {
                                Out::valore($this->nameForm . '_Tipodoc', '');
                                Out::valore($this->nameForm . '_descr_tipodoc', '');
                            }
                        }
                        break;

                    case $this->nameForm . '_Versione':
                        Out::valore($this->nameForm . '_Procat', '');
                        Out::valore($this->nameForm . '_Clacod', '');
                        Out::valore($this->nameForm . '_Fascod', '');
                        Out::valore($this->nameForm . '_TitolarioDecod', '');
                        break;
                    case $this->nameForm . '_Versione_sec':
                        Out::valore($this->nameForm . '_Procat_sec', '');
                        Out::valore($this->nameForm . '_Clacod_sec', '');
                        Out::valore($this->nameForm . '_Fascod_sec', '');
                        Out::valore($this->nameForm . '_TitolarioDecod_sec', '');
                        break;

                    case $this->nameForm . '_Al_periodo';
                    case $this->nameForm . '_Dal_periodo';
                        if ($_POST[$this->nameForm . '_Dal_periodo'] || $_POST[$this->nameForm . '_Al_periodo']) {
                            Out::valore($this->nameForm . '_Anno', '');
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Medcod':
                        $codice = $_POST[$this->nameForm . '_Medcod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnamed($codice);
                        }
                        break;
                    case $this->nameForm . '_TrasmDest':
                        $codice = $_POST[$this->nameForm . '_TrasmDest'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnamedTrasm($codice);
                        }
                        break;
                    case $this->nameForm . '_Ruocod':
                        $codice = $_POST[$this->nameForm . '_Ruocod'];
                        $this->decodRuoli($codice);
                        break;
                    case $this->nameForm . '_Proute':
                        $this->decodUtenti($_POST[$this->nameForm . '_Proute']);
                        break;
                    case $this->nameForm . '_Anno':
                        if ($this->consultazione && $this->returnModel != '') {
                            break;
                        }
                        $Dal_prot = $_POST[$this->nameForm . '_Dal_prot'];
                        $Dal_prot = str_repeat("0", 6 - strlen(trim($Dal_prot))) . trim($Dal_prot);
                        $al_prot = $_POST[$this->nameForm . '_Al_prot'];
                        $al_prot = str_repeat("0", 6 - strlen(trim($al_prot))) . trim($al_prot);
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        $tipo = $_POST[$this->nameForm . '_Arr_par'];
                        if ($Dal_prot != '' && $al_prot != '' && $Dal_prot == $al_prot && $tipo) {
                            if ($anno == '') {
                                $anno = $this->workYear;
                            }
                            $this->vaiAlProtocollo($Dal_prot, $anno, $tipo);
                        }
                        break;
                    case $this->nameForm . '_annopronum':
                        if ($_POST[$this->nameForm . '_pronum'] != '' && $_POST[$this->nameForm . '_tipo']) {
                            $pronum = substr(str_pad($_POST[$this->nameForm . '_pronum'], 6, "0", STR_PAD_LEFT), -6);
                            $anno = $_POST[$this->nameForm . '_annopronum'];
                            $tipo = $_POST[$this->nameForm . '_tipo'];
                            $this->vaiAlProtocollo($pronum, $anno, $tipo);
                        }
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        switch ($_POST['colName']) {
                            case 'LEGAME':
                                if ($_POST['cellContent'] == '<span class="ui-icon ui-icon-folder-open">true</span>') {
                                    $anapro_rec = $this->proLib->GetAnapro($_POST['rowid'], 'rowid');
                                    proRic::proRicLegame($this->proLib, $this->nameForm, 'returnLegame', $this->PROT_DB, $anapro_rec);
                                }
                                break;
                            case 'FASCICOLO':
                                if ($_POST['cellContent'] == '<span class="ui-icon ui-icon-folder-open">true</span>') {
                                    $anapro_rec = $this->proLib->GetAnapro($_POST['rowid'], 'rowid');
                                    if ($anapro_rec['PROFASKEY']) {
//                                        proRic::proRicFascicolo($this->proLib, $this->nameForm, 'returnTreeFascicolo', $this->PROT_DB, $anapro_rec);
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;

            case 'returnanamed':
                $this->DecodAnamed($_POST['retKey'], 'rowid');
                break;
            case 'returnanaruoli':
                $this->decodRuoli($_POST['retKey'], 'rowid');
                break;
            case 'returnutenti':
                $this->decodUtenti($_POST['retKey'], 'rowid');
                break;
            case 'returnElencoReport':
                $tabella_rec = $_POST['rowData'];
                $_POST = $_POST['retid'];
                $anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                switch ($tabella_rec['CODICE']) {
                    case 'proRegData':
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $anaent_rec['ENTDE1']);
                        break;
                    case 'proRegDataGra':
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $anaent_rec['ENTDE1'],
                            "daData" => $this->Dadata,
                            "aData" => $this->Adata);
                        break;
                    case 'proRegUfficio':
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $anaent_rec['ENTDE1']);
                        break;
                    case 'proRegUfficioGra':
//                        // UTILIZZARE STESSI FILTRI/CONTROLLI USATI PER LA TABELLA

                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $anaent_rec['ENTDE1'],
                            "daData" => $this->Dadata,
                            "aData" => $this->Adata);
                        break;
                    /* Scadenziario */
                    case 'proGestRegistroScad':
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Titolo" => "ELENCO PROTOCOLLI SCADENZIARIO",
                            "Ente" => $anaent_rec['ENTDE1']);
                        break;
                    /* Scadenziario */
                    case 'proGestRegScadExcel':
                        $this->GetScadenzeExcel();
                        break 2;
                    case 'proGestProDestinatariCompleto':
                        $this->GetElencoProtMittDestiAll();
                        return;
                    default:
                        $parameters = array(
                            "Sql" => $this->CreaSql(),
                            "Titolo" => "ELENCO RICERCA PROTOCOLLI",
                            "Utente" => App::$utente->getKey('nomeUtente'),
                            "Ente" => $anaent_rec['ENTDE1']
                        );
                        break;
                }
//                App::log($tabella_rec);
//                App::log($parameters);
//                break;
                $itaJR->runSQLReportPDF($this->PROT_DB, $tabella_rec['CODICE'], $parameters);
                break;
            case 'returnanauff':
                $sql = "SELECT UFFCOD, UFFDES FROM ANAUFF WHERE ROWID='" . $_POST['retKey'] . "'";
                $anauff_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
                if ($anauff_rec) {
                    if ($this->uffici != '') {
                        $this->uffici = $this->uffici . "|" . $anauff_rec['UFFCOD'];
                    } else {
                        $this->uffici = $anauff_rec['UFFCOD'];
                    }
                    Out::valore($this->nameForm . '_Uffcod', $this->uffici);
                    $this->CaricaRicUffici();
                }
                break;

//            case 'returnanauff':
//                $this->DecodAnauff($_POST['retKey'], 'rowid');
//                break;
//            case 'returncat':
//                $this->DecodAnacat('', $_POST['retKey'], 'rowid');
//                break;
//            case 'returncla':
//                $this->DecodAnacla('', $_POST['retKey'], 'rowid');
//                break;
//            case 'returnfas':
//                $this->DecodAnafas('', $_POST['retKey'], 'rowid');
//                break;
//            case 'returnorg':
//                $anaorg_rec = $this->DecodAnaorg($_POST['retKey'], '', 'rowid');
//                if ($anaorg_rec) {
//                    $this->DecodAnacat('', substr($anaorg_rec['ORGCCF'], 0, 4));
//                    $this->DecodAnacla('', substr($anaorg_rec['ORGCCF'], 0, 8));
//                    $this->DecodAnafas('', substr($anaorg_rec['ORGCCF'], 0, 12));
//                }
//                break;
//            case 'returndog':
//                $this->DecodAnaogg($_POST['retKey'], 'rowid');
//                break;
            case 'returnorg':
                $this->DecodAnaorg($_POST['retKey'], 'rowid');
                break;
            case 'returnLegame':
                if ($this->consultazione && $this->returnModel != '') {
                    break;
                }
                $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $_POST['retKey']);
                if (!$anaproctr_rec) {
                    Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
                    break;
                }
                $model = 'proArri';
                $_POST['consultazione'] = $this->consultazione;
                $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
                $_POST['event'] = 'openform';
                $_POST[$this->nameForm . '_ANAPRO']['ROWID'] = $_POST['retKey'];
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnTreeFascicolo':
                if ($this->consultazione && $this->returnModel != '') {
                    break;
                }
                list($skip, $rowid) = explode("@", $_POST['retKey']);
                $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $rowid);
                if (!$anaproctr_rec) {
                    Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
                    break;
                }
                $model = 'proArri';
                $_POST['consultazione'] = $this->consultazione;
                $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
                $_POST['event'] = 'openform';
                $_POST[$this->nameForm . '_ANAPRO']['ROWID'] = $rowid;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;

            case "returnAnaTipoDoc":
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Tipodoc', $AnaTipoDoc_rec['CODICE']);
                Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
                break;


            case 'returnTitolario_sec':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_Procat_sec', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_Clacod_sec', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_Fascod_sec', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_TitolarioDecod_sec', $retTitolario['DECOD_DESCR']);
                break;
            case 'returnTitolario':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_Procat', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_Clacod', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_Fascod', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_TitolarioDecod', $retTitolario['DECOD_DESCR']);
                break;

            case 'returnAnamedTrasm':
                $this->DecodAnamedTrasm($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_consultazione');
        App::$utente->removeKey($this->nameForm . '_dadata');
        App::$utente->removeKey($this->nameForm . '_adata');
        App::$utente->removeKey($this->nameForm . '_uffici');
        App::$utente->removeKey($this->nameForm . '_condizioni');
        App::$utente->removeKey($this->nameForm . '_visOggRiservati');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    private function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        TableView::disableEvents($this->gridGest);
        TableView::clearGrid($this->gridGest);
        $this->visOggRiservati = false;
        $this->Nascondi();
        if ($this->consultazione == true) {
            Out::hide($this->nameForm . '_toolBar');
            Out::hide($this->nameForm . '_toolBar1');
            Out::hide($this->nameForm . '_NuovoParte');
            Out::hide($this->nameForm . '_NuovoArrivo');
            Out::hide($this->nameForm . '_AbbinaAllegati');
            Out::hide($this->nameForm . '_Comunicazioni');
            Out::hide($this->nameForm . '_DocAllaFirma');
            Out::hide($this->nameForm . '_divRicRapida');
        } else {
            Out::show($this->nameForm . '_toolBar');
            Out::hide($this->nameForm . '_toolBar1');
        }
        $profilo = proSoggetto::getProfileFromIdUtente();
        Out::hide($this->nameForm . '_DocAllaFirma');
        if ($this->consultazione) {
            Out::valore($this->nameForm . '_Uffcod', '');
        }
        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Procat_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_TitolarioDecod');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Fascod_field');
        }
        if ($profilo['PROT_ABILITATI'] == '1') {
            Out::hide($this->nameForm . '_NuovoParte');
        } else if ($profilo['PROT_ABILITATI'] == '2') {
            Out::hide($this->nameForm . '_NuovoArrivo');
        } else if ($profilo['PROT_ABILITATI'] == '3') {
            Out::hide($this->nameForm . '_NuovoParte');
            Out::hide($this->nameForm . '_NuovoArrivo');
            Out::hide($this->nameForm . '_AbbinaAllegati');
        }
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Svuota');
        /*
         * Controllo Doc. Alla Firma Abilitati
         */
        Out::hide($this->nameForm . '_DocAllaFirma');
        $anaent_55 = $this->proLib->GetAnaent('55');
        if ($anaent_55['ENTDE2'] == '1') {
            Out::show($this->nameForm . '_DocAllaFirma');
        }
        if ($this->consultazione == true) {
            Out::hide($this->nameForm . '_DocAllaFirma');
        }


        if ($this->condizioni['TITOLARIO']) {
            if ($this->condizioni['TITOLARIO']['NASCONDI'] === true) {
                Out::hide($this->nameForm . '_divTitolario');
            }
            if ($this->condizioni['TITOLARIO']['VALORE']) {
                $cat = substr($this->condizioni['TITOLARIO']['VALORE'], 0, 4);
                $cla = substr($this->condizioni['TITOLARIO']['VALORE'], 4, 4);
                $fas = substr($this->condizioni['TITOLARIO']['VALORE'], 8, 4);
                Out::valore($this->nameForm . '_Procat', $cat);
                Out::valore($this->nameForm . '_Clacod', $cla);
                Out::valore($this->nameForm . '_Fascod', $fas);
                Out::hide($this->nameForm . '_Svuota');
                // Qui decodifico
                if ($cat) {
                    $this->DecodAnacat($cat, 'codice');
                }
                if ($cla) {
                    $this->DecodAnacla($cat . $cla, 'codice');
                }
                if ($fas) {
                    $this->DecodAnafas($cat . $cla . $fas, 'fasccf');
                }
            }
        }

        Out::show($this->nameForm);
        $AnnoAttuale = date('Y');
        Out::valore($this->nameForm . '_annopronum', $AnnoAttuale);
        if ($this->event == 'openform') {
            Out::valore($this->nameForm . '_Anno', $AnnoAttuale);
        }
        if ($_POST[$this->nameForm . '_Anno']) {
            Out::valore($this->nameForm . '_Anno', $_POST[$this->nameForm . '_Anno']);
        }
        if ($_POST[$this->nameForm . '_annopronum']) {
            Out::valore($this->nameForm . '_annopronum', $_POST[$this->nameForm . '_annopronum']);
        }
        /* Scadenziario */
        Out::hide($this->nameForm . '_divScadTrasm');
        $anaent47_rec = $this->proLib->GetAnaent('47');
        if ($anaent47_rec['ENTDE6'] == 1) {
            Out::show($this->nameForm . '_divScadTrasm');
        }

        Out::setFocus('', $this->nameForm . '_pronum');
    }

    private function CreaCombo() {
        foreach ($this->caricaUof() as $Uof_rec) {
            Out::select($this->nameForm . '_Ruolo', 1, $Uof_rec['UFFCOD'], $Uof_rec['SELECT'], $Uof_rec['UFFDES']);
        }

        Out::select($this->nameForm . '_Arr_par', 1, "", "1", "");
        Out::select($this->nameForm . '_Arr_par', 1, "A", "0", "Arrivo");
        Out::select($this->nameForm . '_Arr_par', 1, "P", "0", "Partenza");
        Out::select($this->nameForm . '_Arr_par', 1, "C", "0", "Documento Formale");
        Out::select($this->nameForm . '_Arr_par', 1, "X", "0", "Arrivi/Partenze");
//
        Out::select($this->nameForm . '_tipo', 1, "A", "0", "Arrivo");
        Out::select($this->nameForm . '_tipo', 1, "P", "0", "Partenza");
        Out::select($this->nameForm . '_tipo', 1, "C", "0", "Documento Formale");
        Out::select($this->nameForm . '_tipo', 1, "X", "1", "Arrivi/Partenze");
//
        Out::select($this->nameForm . '_Lris', 1, "", "1", "");
        Out::select($this->nameForm . '_Lris', 1, "1", "0", "1");
        Out::select($this->nameForm . '_Lris', 1, "2", "0", "2");
        Out::select($this->nameForm . '_Lris', 1, "3", "0", "3");
//
        Out::select($this->nameForm . '_MednomTipoFind', 1, "", "1", "Contiene");
        Out::select($this->nameForm . '_MednomTipoFind', 1, "1", "0", "Inizia");
        Out::select($this->nameForm . '_MednomTipoFind', 1, "2", "0", "Esatta");
        Out::select($this->nameForm . '_MednomTipoFind', 1, "3", "0", "Contiene Tutte");
//
        Out::select($this->nameForm . '_MailTipoFind', 1, "", "1", "Contiene");
        Out::select($this->nameForm . '_MailTipoFind', 1, "1", "0", "Inizia");
        Out::select($this->nameForm . '_MailTipoFind', 1, "2", "0", "Esatta");
//
        Out::select($this->nameForm . '_OggettoTipoFind', 1, "", "0", "Contiene");
        Out::select($this->nameForm . '_OggettoTipoFind', 1, "1", "0", "Inizia");
        Out::select($this->nameForm . '_OggettoTipoFind', 1, "2", "0", "Esatta");
        Out::select($this->nameForm . '_OggettoTipoFind', 1, "3", "1", "Contiene Tutte");

        Out::select($this->nameForm . '_VisAnnullati', 1, "", "1", "Tutti i Protocolli");
        Out::select($this->nameForm . '_VisAnnullati', 1, "1", "0", "Solo i Protocolli Annullati");
        Out::select($this->nameForm . '_VisAnnullati', 1, "2", "0", "Solo i Protocolli non Annullati");
        Out::select($this->nameForm . '_VisAnnullati', 1, "3", "0", "Solo i Protocolli Riservati ");
        // Tipo Find Numero Mittente
        Out::select($this->nameForm . '_Num_mit_TipoFind', 1, "", "1", "Contiene");
        Out::select($this->nameForm . '_Num_mit_TipoFind', 1, "1", "0", "Esatta");
        // Versioni Titolario:
        Out::html($this->nameForm . '_Versione', '');
        $Versioni_tab = $this->proLibTitolario->GetVersioni();
        foreach ($Versioni_tab as $Versioni_rec) {
            Out::select($this->nameForm . '_Versione', 1, $Versioni_rec['VERSIONE_T'], "0", $Versioni_rec['VERSIONE_T'] . ' - ' . $Versioni_rec['DESCRI_B']);
            Out::select($this->nameForm . '_Versione_sec', 1, $Versioni_rec['VERSIONE_T'], "0", $Versioni_rec['VERSIONE_T'] . ' - ' . $Versioni_rec['DESCRI_B']);
        }
        // Tipologie trasmissioni
        Out::select($this->nameForm . '_TrasmTipo', 1, "", "1", "Tutte");
        Out::select($this->nameForm . '_TrasmTipo', 1, "1", "0", "Assegnazioni");
        Out::select($this->nameForm . '_TrasmTipo', 1, "2", "0", "Ritrasmissioni");

        // Tipo Spedizione
        Out::select($this->nameForm . '_TipoSpedizione', 1, "", "1", "Tutte");
        $sql = "SELECT * FROM ANATSP ORDER BY TSPDES ";
        $Anatsp_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        foreach ($Anatsp_tab as $Anatsp_rec) {
            Out::select($this->nameForm . '_TipoSpedizione', 1, $Anatsp_rec['TSPCOD'], "0", $Anatsp_rec['TSPCOD'] . ' - ' . $Anatsp_rec['TSPDES']);
        }
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::valore($this->nameForm . '_tipo', 'X');
        Out::valore($this->nameForm . '_annopronum', date('Y'));
        Out::valore($this->nameForm . '_Anno', date('Y'));
        Out::valore($this->nameForm . '_MednomTipoFind', '');
        Out::valore($this->nameForm . '_OggettoTipoFind', '');
        Out::valore($this->nameForm . '_MailTipoFind', '');
        Out::valore($this->nameForm . '_OggettoTipoFind', '3');
        $this->uffici = '';
        Out::valore($this->nameForm . '_Uffcod', '');
        /* Defautl Titolario corrente */
        Out::valore($this->nameForm . '_Versione', $this->proLib->GetTitolarioCorrente());
        Out::valore($this->nameForm . '_Versione_sec', $this->proLib->GetTitolarioCorrente());

        TableView::clearGrid($this->gridRicUffici);
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_toolBar');
        Out::hide($this->nameForm . '_toolBar1');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Svuota');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_divCat');
        Out::hide($this->nameForm . '_divCla');
        Out::hide($this->nameForm . '_divFas');
        Out::hide($this->nameForm . '_divOrg');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_Richiesta');
        Out::hide($this->nameForm . '_VediOggettiRiservati');
    }

    private function whereBaseSql() {
        $D_gio = date('Ymd');
        $Paar = $_POST[$this->nameForm . '_Arr_par'];
        $Pro = $_POST[$this->nameForm . '_Num_mit'];
        $Dap = $_POST[$this->nameForm . '_Dal_periodo'];
        $alp = $_POST[$this->nameForm . '_Al_periodo'];
        //$Dpr = $_POST[$this->nameForm . '_Dal_prot'];
        $Dpr = substr($_POST[$this->nameForm . '_Dal_prot'], -6);
        //$apr = $_POST[$this->nameForm . '_Al_prot'];
        $apr = substr($_POST[$this->nameForm . '_Al_prot'], -6);
        $anno = $_POST[$this->nameForm . '_Anno'];
        $AnnoFas = $_POST[$this->nameForm . '_AnnoFascicolo'];
// NEW
        $Da_arrinv = $_POST[$this->nameForm . '_Dal_arrinv'];
        $Al_arrinv = $_POST[$this->nameForm . '_Al_arrinv'];
//

        $Tipodoc = $_POST[$this->nameForm . '_Tipodoc'];
        $Uff = $_POST[$this->nameForm . '_Uffcod'];
        $ruocod = $_POST[$this->nameForm . '_Ruocod'];
        $segnatura = $_POST[$this->nameForm . '_Segnatura'];
        $proute = $_POST[$this->nameForm . '_Proute'];
        $Cod = $_POST[$this->nameForm . '_Medcod'];
        $Nom = $_POST[$this->nameForm . '_Mednom'];
        $Org = $_POST[$this->nameForm . '_Proarg'];
        $catcod = $_POST[$this->nameForm . '_Procat'];
        $clacod = $_POST[$this->nameForm . '_Clacod'];
        $fascod = $_POST[$this->nameForm . '_Fascod'];
        $Versione = $_POST[$this->nameForm . '_Versione'];
        $Oggetto = $_POST[$this->nameForm . '_Oggetto'];
        $lris = $_POST[$this->nameForm . '_Lris'];
        $doc_name = $_POST[$this->nameForm . '_DocAllegato'];
        $docDaFirmare = $_POST[$this->nameForm . '_DocDaFirmare'];
        $mednomTipoFind = $_POST[$this->nameForm . '_MednomTipoFind'];
        $mailTipoFind = $_POST[$this->nameForm . '_MailTipoFind'];
        $VisAnnullati = $_POST[$this->nameForm . '_VisAnnullati'];
        $mail = $_POST[$this->nameForm . '_Mail'];
        $SoloNonFascicolati = $_POST[$this->nameForm . '_SoloNonFascicolati'];
        $oggettoTipoFind = $_POST[$this->nameForm . '_OggettoTipoFind'];
        $num_mit_TipoFind = $_POST[$this->nameForm . '_Num_mit_TipoFind'];
        /* Scadenziario Data Trasmissione per Cosmari */
        $daDataTrasm = $_POST[$this->nameForm . '_DaDataTrasm'];
        $aDataTrasm = $_POST[$this->nameForm . '_ADataTrasm'];
        // Titolario Secondario:
        $catcod_sec = $_POST[$this->nameForm . '_Procat_sec'];
        $clacod_sec = $_POST[$this->nameForm . '_Clacod_sec'];
        $fascod_sec = $_POST[$this->nameForm . '_Fascod_sec'];
        $Versione_sec = $_POST[$this->nameForm . '_Versione_sec'];
        $CFAnamed = $_POST[$this->nameForm . '_CFAnamed'];
        // Trasmissioni:
        $TrasmDest = $_POST[$this->nameForm . '_TrasmDest'];
        $TrasmTipo = $_POST[$this->nameForm . '_TrasmTipo'];
        $ProTipoSped = $_POST[$this->nameForm . '_TipoSpedizione'];
        $CapMittDest = $_POST[$this->nameForm . '_CapMittDest'];

        $chiaveFascicoloPrinc = '';
        $chiaveFascicoloSec = '';
        if ($catcod && $Org) {
            $Categoria = str_pad($catcod, 4, "0", STR_PAD_LEFT);
            $Classe = $clacod ? str_pad($clacod, 4, "0", STR_PAD_LEFT) : '';
            $SottoClasse = $fascod ? str_pad($fascod, 4, "0", STR_PAD_LEFT) : '';
            $chiaveFascicoloPrinc = $Categoria . $Classe . $SottoClasse . '.' . $AnnoFas . '.' . $Org;
        }
        if ($catcod_sec && $Org) {
            $Categoria = str_pad($catcod_sec, 4, "0", STR_PAD_LEFT);
            $Classe = $clacod_sec ? str_pad($clacod_sec, 4, "0", STR_PAD_LEFT) : '';
            $SottoClasse = $fascod_sec ? str_pad($fascod_sec, 4, "0", STR_PAD_LEFT) : '';
            $chiaveFascicoloSec = $Categoria . $Classe . $SottoClasse . '.' . $AnnoFas . '.' . $Org;
        }



        if ($alp <> "" && $alp > $D_gio || strlen($Dap) <> 8 && $Dap <> "" || strlen($alp) <> 8 && $alp <> "") {
            Out::msgStop("Attenzione!", "Impossibile effettuare l'elaborazione.<br> Controllare le date inserite");
            return false;
        }
        $this->Dadata = substr($Dap, 6, 2) . "/" . substr($Dap, 4, 2) . "/" . substr($Dap, 0, 4);
        $this->Adata = substr($alp, 6, 2) . "/" . substr($alp, 4, 2) . "/" . substr($alp, 0, 4);
        if (strlen($this->Dadata) < 8) {
            $this->Dadata = "01/01/" . $this->workYear;
        }
        if (strlen($this->Adata) < 8) {
            $this->Adata = substr($this->workDate, 6, 2) . "/" . substr($this->workDate, 4, 2) . "/" . substr($this->workDate, 0, 4);
        }

//
// WHERE
//
        $sql = '';
        if ($doc_name != '' || $docDaFirmare == 1) {
            $sql .= " LEFT OUTER JOIN ANADOC ANADOC ON ANADOC.DOCNUM =ANAPRO.PRONUM AND ANADOC.DOCPAR=ANAPRO.PROPAR";
        }
        $sql .= " WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C') "; // OR ANAPRO.PROPAR='AA' OR ANAPRO.PROPAR='PA' OR ANAPRO.PROPAR='CA'
        if ($Tipodoc) {
            $sql .= " AND ANAPRO.PROCODTIPODOC ='$Tipodoc'";
        }

        if ($Dap) {
            if ($alp == '') {
                $alp = $this->workDate + 1;
            }
            $sql .= " AND (ANAPRO.PRODAR BETWEEN '$Dap' AND '$alp')";
        } else if ($alp) {
            $Dap = $this->workYear . "0101";
            $sql .= " AND (ANAPRO.PRODAR BETWEEN '$Dap' AND '$alp')";
        } else {
            // QUI MODIFICA: ANNO NON PASSATO E' STATO PRE CONTROLLATO.
//            if ($anno == '') {
//                $anno = $this->workYear;
//            }
        }
        /*
         * 
         */
        if ($anno) {
            $anno2 = $anno + 1;
            $sql .= " AND (ANAPRO.PRONUM BETWEEN '" . $anno . "000000' AND '" . $anno2 . "000000')";
            if ($Dpr) {
                $Dpr = $anno * 1000000 + $Dpr;
                if ($apr) {
                    $apr = $anno * 1000000 + $apr;
                } else {
                    $apr = $anno * 1000000 + 999999;
                }
                $sql .= " AND (ANAPRO.PRONUM BETWEEN $Dpr AND $apr)";
            } else if ($apr) {
                $Dpr = $anno * 1000000 + 1;
                $apr = $anno * 1000000 + $apr;
                $sql .= " AND (ANAPRO.PRONUM BETWEEN $Dpr AND $apr)";
            }
        }

//NEW
        if ($Da_arrinv && !$Al_arrinv) {
            $Al_arrinv = $this->workDate + 1;
        } elseif (!$Da_arrinv && $Al_arrinv) {
            $Da_arrinv = $this->workYear . "0101";
        }

        if ($Da_arrinv) {
            $sql .= " AND (ANAPRO.PRODAA BETWEEN '$Da_arrinv' AND '$Al_arrinv')";
        }
//        
        if ($lris != '') {
            $sql .= " AND ANAPRO.PROLRIS = '" . $lris . "'";
        }
        if ($Paar == 'X') {
//            $sql.=" AND (SUBSTRING(ANAPRO.PROPAR, 1, 1) = 'A' OR SUBSTRING(ANAPRO.PROPAR, 1, 1) = 'P')";
            $sql .= " AND (ANAPRO.PROPAR = 'A' OR ANAPRO.PROPAR = 'P')";
        } else if ($Paar != '') {
//            $sql.=" AND (SUBSTRING(ANAPRO.PROPAR, 1, 1) BETWEEN '$Paar' AND '$Paar')";
            $sql .= " AND (ANAPRO.PROPAR BETWEEN '$Paar' AND '$Paar')";
        }
//        if ($Pro != '') {
//            $sql.=" AND (PRONPA BETWEEN '$Pro' AND '$Pro')";
//        }
        if (trim($Uff) != '') {
            $sql .= " AND (UFFPRO.UFFCOD <> UFFPRO.UFFCOD";
            $uffici = explode("|", $Uff);
            foreach ($uffici as $ufficio) {
                if (trim($ufficio) != '') {
                    $sql .= " OR UFFPRO.UFFCOD = '$ufficio'";
                }
            }
            $sql .= ")";
        }
        if ($Cod != '') {
            $Nom = '';
            if (is_numeric($Cod)) {
                $Cod = str_repeat("0", 6 - strlen(trim($Cod))) . trim($Cod);
            }
            $sql .= " AND ANAPRO.PROCON = '" . $Cod . "'";
        }
        if ($ruocod != '') {
            $sql .= " AND ANADES.DESRUOLO = '" . $ruocod . "'";
        }
        if ($proute != '') {
            if (substr($proute, 1, 3) == '999') {
                $sql .= " AND ANAPRO.PROLOG LIKE '" . $proute . "%'";
            } else {
                $sql .= " AND ANAPRO.PROUTE = '" . $proute . "'";
            }
        }

        /*
         * Ricerca i Mitt/Dest, escludo gli ANADES delle trasmissioni tipo "T"
         */

        $ric_chiavi_nom = '';

        if ($Nom != "") {
            switch ($mednomTipoFind) {
                case '1':
                    $sql .= " AND ((" . $this->PROT_DB->strUpper('ANANOM.NOMNOM') . " LIKE '" . addslashes(strtoupper($Nom)) . "%') OR (" . $this->PROT_DB->strUpper('ANADES.DESNOM') . " LIKE '" . addslashes(strtoupper($Nom)) . "%' AND ANADES.DESTIPO <> 'T' ) OR (" . $this->PROT_DB->strUpper('PROMITAGG.PRONOM') . " LIKE '" . addslashes(strtoupper($Nom)) . "%'))";
                    break;
                case '2':
                    $sql .= " AND ((" . $this->PROT_DB->strUpper('ANANOM.NOMNOM') . " = '" . addslashes(strtoupper($Nom)) . "') OR (" . $this->PROT_DB->strUpper('ANADES.DESNOM') . " = '" . addslashes(strtoupper($Nom)) . "' AND ANADES.DESTIPO <> 'T' ) OR (" . $this->PROT_DB->strUpper('PROMITAGG.PRONOM') . " = '" . addslashes(strtoupper($Nom)) . "'))";
                    break;
                case '3':
                    $parole_chiavi = explode("\"", $Nom);
                    $AnanomWhere = '';
                    $DesnomWhere = '';
                    $PronomWhere = '';
                    foreach ($parole_chiavi as $key => $parte) {
                        $parole = explode(" ", trim($parte));
                        foreach ($parole as $parola) {
                            if ($parola) {
                                $AnanomWhere .= " " . $this->PROT_DB->strUpper('ANANOM.NOMNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ";
                                $DesnomWhere .= " " . $this->PROT_DB->strUpper('ANADES.DESNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ";
                                $PronomWhere .= " " . $this->PROT_DB->strUpper('PROMITAGG.PRONOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ";
                            }
                        }
                        $sql .= " AND (( " . substr($AnanomWhere, 0, - 4) . ") OR ( " . substr($DesnomWhere, 0, - 4) . " AND ANADES.DESTIPO <> 'T' ) OR ( " . substr($PronomWhere, 0, - 4) . ")) ";
                    }
                    break;

                default:
                    $parole_chiavi = explode("\"", $Nom);
                    foreach ($parole_chiavi as $key => $parte) {
                        if ($key % 2 == 0) {
                            $parole = explode(" ", trim($parte));
                            foreach ($parole as $parola) {
                                if ($ric_chiavi_nom != '' && $parola != '') {
                                    $ric_chiavi_nom .= " AND ";
                                }
                                $ric_chiavi_nom .= $parola != '' ? " ((" . $this->PROT_DB->strUpper('ANANOM.NOMNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%') OR (" . $this->PROT_DB->strUpper('ANADES.DESNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ANADES.DESTIPO <> 'T' ) OR (" . $this->PROT_DB->strUpper('PROMITAGG.PRONOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'))" : "";
                            }
                        } else {
                            if ($ric_chiavi_nom != '' && $parte != '') {
                                $ric_chiavi_nom .= " AND ";
                            }
                            $ric_chiavi_nom .= $parte != '' ? " ((" . $this->PROT_DB->strUpper('ANANOM.NOMNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%') OR (" . $this->PROT_DB->strUpper('ANADES.DESNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ANADES.DESTIPO <> 'T' ) OR (" . $this->PROT_DB->strUpper('PROMITAGG.PRONOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'))" : "";
                        }
                    }
                    $ric_chiavi_nom = $ric_chiavi_nom != '' ? " AND (" . $ric_chiavi_nom . ")" : "";
                    $sql .= $ric_chiavi_nom;
                    break;
            }
        }

        if ($mail) {
            if ($mailTipoFind === '1') {
                $sql .= " AND (" . $this->PROT_DB->strUpper('ANAPRO.PROMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "%' OR " . $this->PROT_DB->strUpper('ANADES.DESMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "%' OR " . $this->PROT_DB->strUpper('PROMITAGG.PROMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "%')";
            } else if ($mailTipoFind === '2') {
                $sql .= " AND (" . $this->PROT_DB->strUpper('ANAPRO.PROMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "' OR " . $this->PROT_DB->strUpper('ANADES.DESMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "' OR " . $this->PROT_DB->strUpper('PROMITAGG.PROMAIL') . " LIKE '" . addslashes(strtoupper($mail)) . "')";
            } else {
                $sql .= " AND (" . $this->PROT_DB->strUpper('ANAPRO.PROMAIL') . " LIKE '%" . addslashes(strtoupper($mail)) . "%' OR " . $this->PROT_DB->strUpper('ANADES.DESMAIL') . " LIKE '%" . addslashes(strtoupper($mail)) . "%' OR " . $this->PROT_DB->strUpper('PROMITAGG.PROMAIL') . " LIKE '%" . addslashes(strtoupper($mail)) . "%')";
            }
        }

        /*
         * Cap Mitt/Dest [esclusi gli interni]
         */
        if ($CapMittDest) {
            $sql .= " AND (" . $this->PROT_DB->strUpper('ANAPRO.PROCAP') . " LIKE '" . addslashes(strtoupper($CapMittDest)) . "%' OR (" . $this->PROT_DB->strUpper('ANADES.DESCAP') . " LIKE '" . addslashes(strtoupper($CapMittDest)) . "%'  AND DESTIPO='D' )OR " . $this->PROT_DB->strUpper('PROMITAGG.PROCAP') . " LIKE '" . addslashes(strtoupper($CapMittDest)) . "%')";
        }


        if ($catcod) {
            $sql .= " AND ANAPRO.PROCCF LIKE '$catcod$clacod$fascod%'";
            $sql .= " AND ANAPRO.VERSIONE_T = '$Versione' ";
        }
        if ($Org != '') {
            $Org = is_numeric($Org) ? str_pad(trim($Org), 6, "0", STR_PAD_LEFT) : str_pad(trim($Org), 6, " ", STR_PAD_LEFT);
            $sql .= " AND ANAPRO.PROARG = '" . $Org . "'";
        }
        if ($segnatura) {
            $sql .= " AND ANAPRO.PROSEG LIKE '%" . addslashes($segnatura) . "%'";
        }

        if ($SoloNonFascicolati) {
            $sql .= " AND ANAPRO.PROFASKEY = '' AND ORGCONN.PRONUM IS NULL ";
        }
        if ($chiaveFascicoloSec) {
            $sql .= " AND ANAPROFAS.PROFASKEY = '$chiaveFascicoloSec'  ";
        }
        if ($chiaveFascicoloPrinc) {
            $sql .= " AND ANAPROFAS.PROFASKEY = '$chiaveFascicoloPrinc'  ";
        }

        /*
         * Ricerca CF Anamed principale
         */
        if ($CFAnamed) {
            $sql .= " AND (ANAMED.MEDFIS LIKE '%$CFAnamed%' OR ANADES.DESFIS LIKE '%$CFAnamed%' OR PROMITAGG.PROFIS LIKE '%$CFAnamed%' OR ANAPRO.PROFIS LIKE '%$CFAnamed%' ) ";
        }



//
// Ricerca per oggetto
//
        $ric_chiavi = '';
        if ($Oggetto != "") {
            switch ($oggettoTipoFind) {
                case '1':
                    $sql .= " AND " . $this->PROT_DB->strUpper('OGGOGG') . " LIKE '" . addslashes(strtoupper($Oggetto)) . "%'";
                    break;
                case '2':
                    $sql .= " AND " . $this->PROT_DB->strUpper('OGGOGG') . " = '" . addslashes(strtoupper($Oggetto)) . "'";
                    break;
                case '3':
                    $parole_chiavi = explode("\"", $Oggetto);
                    $OggettoWhere = '';
                    foreach ($parole_chiavi as $key => $parte) {
                        $parole = explode(" ", trim($parte));
                        foreach ($parole as $parola) {
                            if ($parola) {
                                $OggettoWhere .= " " . $this->PROT_DB->strUpper('OGGOGG') . " LIKE '%" . addslashes(strtoupper($parola)) . "%' AND ";
                            }
                        }
                        $sql .= " AND ( " . substr($OggettoWhere, 0, - 4) . ")";
                    }
                    break;
                default:
                    $parole_chiavi = explode("\"", $Oggetto);
                    foreach ($parole_chiavi as $key => $parte) {
                        if ($key % 2 == 0) {
                            $parole = explode(" ", trim($parte));
                            foreach ($parole as $parola) {
                                if ($ric_chiavi != '' && $parola != '') {
                                    $ric_chiavi .= " OR ";
                                }
                                $ric_chiavi .= $parola != '' ? " " . $this->PROT_DB->strUpper('OGGOGG') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'" : "";
                            }
                        } else {
                            if ($ric_chiavi != '' && $parte != '') {
                                $ric_chiavi .= " AND ";
                            }
                            $ric_chiavi .= $parte != '' ? " " . $this->PROT_DB->strUpper('OGGOGG') . " LIKE '%" . addslashes(strtoupper(trim($parte))) . "%'" : "";
                        }
                    }

                    $ric_chiavi = $ric_chiavi != '' ? " AND (" . $ric_chiavi . ")" : "";
                    $sql .= $ric_chiavi;
                    break;
            }
        }

//
// Ricerca per documento allegato
//
        if ($doc_name != '') {
            $sql .= " AND ANADOC.DOCSERVIZIO=0 AND " . $this->PROT_DB->strUpper('ANADOC.DOCNAME') . " LIKE '%" . addslashes(strtoupper(trim($doc_name))) . "%'";
        }
        if ($docDaFirmare == 1) {
            $sql .= " AND ANADOC.DOCDAFIRM = 1";
        }
        if ($this->condizioni['EXTRA']) {
            $sql .= $this->condizioni['EXTRA'];
        }

        //Visualizza Annullati/Tutti/Solo non Annullati
        switch ($VisAnnullati) {
            case '1':
//                $sql .= " AND (ANAPRO.PROPAR = 'AA' OR ANAPRO.PROPAR = 'PA' OR ANAPRO.PROPAR = 'CA' ) ";
                $sql .= " AND ANAPRO.PROSTATOPROT = " . proLib::PROSTATO_ANNULLATO . " ";
                break;
            case '2':
//                $sql .= " AND (ANAPRO.PROPAR <> 'AA' AND ANAPRO.PROPAR <> 'PA' AND ANAPRO.PROPAR <> 'CA' ) ";
                $sql .= " AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
            case '3':
//                $sql .= " AND (ANAPRO.PROPAR <> 'AA' AND ANAPRO.PROPAR <> 'PA' AND ANAPRO.PROPAR <> 'CA' ) ";
                $sql .= " AND ANAPRO.PRORISERVA = '1' ";
                break;
            default:
                break;
        }

        /* Ricerca Protocollo Mittenti Contiene */
        $ric_chiavi = '';
        if ($Pro != "") {
            if ($num_mit_TipoFind == '1') {
                $sql .= " AND (ANAPRO.PRONPA BETWEEN '$Pro' AND '$Pro')";
            } else {
                $sql .= " AND ANAPRO.PRONPA LIKE '%" . $Pro . "%'";
            }
        }

        /* Scadenziario Data Trasmissione per Cosmari */
        //$daDataTrasm  $aDataTrasm
        if ($daDataTrasm) {
            $sql .= " AND ARCITE.ITETERMINE >= '$daDataTrasm' ";
        }
        if ($aDataTrasm) {
            $sql .= " AND ARCITE.ITETERMINE <= '$aDataTrasm' ";
        }
        /* Ricerca Titolario Secondario */
        if ($catcod_sec) {
            $sql .= " AND ANAPROFAS.PROCAT = '$catcod_sec' ";
        }
        if ($clacod_sec) {
            $codice = $catcod_sec . $clacod_sec;
            $sql .= " AND ANAPROFAS.PROCCA = '$codice' ";
        }
        if ($fascod_sec) {
            $codice = $catcod_sec . $clacod_sec . $fascod_sec;
            $sql .= " AND ANAPROFAS.PROCCF = '$codice' ";
        }
        if ($catcod_sec || $clacod_sec || $fascod_sec) {
            $sql .= " AND ANAPROFAS.VERSIONE_T = '$Versione_sec' ";
        }

        /*
         * Ricerca Trasmissioni
         * RICORDARSI DI INCLUSERE TRX: Visbilita..
         */
        if ($TrasmDest) {
            switch ($TrasmTipo) {
                case '1':
                    $sql .= " AND (ARCITE.ITEDES = '$TrasmDest' AND ARCITE.ITENODO='ASS') ";
                    break;
                case '2':
                    $sql .= " AND (ARCITE.ITEDES = '$TrasmDest' AND ARCITE.ITENODO='TRX') ";
                    break;

                default:
                    // Entrambe
                    $sql .= " AND ( ARCITE.ITEDES = '$TrasmDest' AND (ARCITE.ITENODO='TRX' OR ARCITE.ITENODO='ASS'))";
                    break;
            }
        }

        /*
         *  Tipo Spedizione
         */
        if ($ProTipoSped) {
            $sql .= " AND ANAPRO.PROTSP = '$ProTipoSped' ";
        }


        return $sql;
    }

    private function CreaSql($stampa = false) {
        $sql = $this->proLib->getSqlRegistro();
//
// Collego tabelle secondarie
//
        //
        // Mittenti
//
        $sql .= " LEFT OUTER JOIN ANANOM ANANOM ON ANAPRO.PRONUM=ANANOM.NOMNUM AND ANAPRO.PROPAR=ANANOM.NOMPAR";
//
// Destinatari
//
        $sql .= " LEFT OUTER JOIN ANADES ANADES FORCE INDEX(I_DESPAR) ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";
//
// Mitt Agg
//
        $sql .= " LEFT OUTER JOIN PROMITAGG PROMITAGG ON ANAPRO.PRONUM=PROMITAGG.PRONUM AND ANAPRO.PROPAR=PROMITAGG.PROPAR";
//
// Prime assegnazioni su Arcite
//
        $sql .= " LEFT OUTER JOIN ARCITE ARCITE FORCE INDEX(I_ITEPRO) ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR";


        if ($_POST[$this->nameForm . '_Procat_sec'] || $_POST[$this->nameForm . '_Clacod_sec'] || $_POST[$this->nameForm . '_Fascod_sec'] || $_POST[$this->nameForm . '_SoloNonFascicolati']) {
            $sql .= " LEFT OUTER JOIN ORGCONN ORGCONN ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR AND ORGCONN.CONNDATAANN = '' ";
            $sql .= " LEFT OUTER JOIN ANAPRO ANAPROFAS ON ORGCONN.ORGKEY=ANAPROFAS.PROFASKEY AND ANAPROFAS.PROPAR='F' ";
        }

        $whereBase = $this->whereBaseSql();
        if ($whereBase) {
            $sql .= " " . $whereBase;
        } else {
            return false;
        }
        /* Scadenziario */
        $TipoRicerca = '';
        if ($_POST[$this->nameForm . '_DaDataTrasm'] || $_POST[$this->nameForm . '_ADataTrasm']) {
            $TipoRicerca = 'vedi_trasmessi';
        }
        /*
         * Attivo visibilità trasmissioni forzatamente: 
         * l'accesso viene controllato in un secondo momento: se non attivo trx non può gestirlo.
         * Se vedi tutti o trx
         */
        if ($_POST[$this->nameForm . '_TrasmDest']) {
            if ($_POST[$this->nameForm . '_TrasmTipo'] != '1') {
                $TipoRicerca = 'vedi_trasmessi';
            }
        }

        /* Imposto di voler vedere anche i riservati, 
         * se attiva opzione di "vedi oggetti riservati" */
        $extraParam = array();
        $extraParam['VEDI_OGGRISERVATI'] = 1;
        $extraParam['FILTRA_RUOLO'] = $_POST[$this->nameForm . '_Ruolo'];

        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, $TipoRicerca, $extraParam);
        $sql .= " AND $where_profilo";

        if ($stampa) {
            $sql .= " ORDER BY ANAPRO.PRONUM,ANAPRO.PROPAR ";
        }

        /*
         * Aggiungo sempre la conta delle mail collegate al protocollo,
         * per mostrare successivamente l'icona della busta.
         */
        $wherePecTrx = " WHERE 1 = 1 ";
        if ($_POST[$this->nameForm . '_PECTrx']) {
            $wherePecTrx .= " AND T.QUANTE_MAIL>0 OR T.PROTSP LIKE '%PEC%' ";
        }
        $sql = "
                SELECT
                    *
                FROM (
                    SELECT
                        ANAPRO_R.*,
                        (SELECT COUNT(ANADOC.DOCIDMAIL) FROM ANADOC WHERE ANADOC.DOCNUM=ANAPRO_R.PRONUM AND ANAPRO_R.PROPAR=ANADOC.DOCPAR AND ANADOC.DOCSERVIZIO=0 AND DOCTIPO ='' AND ANADOC.DOCIDMAIL<>'')+
                        (SELECT COUNT(ANADES.ROWID) FROM ANADES FORCE INDEX(I_DESPAR) WHERE ANADES.DESNUM=ANAPRO_R.PRONUM AND ANAPRO_R.PROPAR=ANADES.DESPAR AND ANADES.DESTIPO='D' AND DESIDMAIL<>'')+
                        (SELECT COUNT(ANAPRO.ROWID) FROM ANAPRO FORCE INDEX(I_PROPAR)  WHERE ANAPRO.PRONUM=ANAPRO_R.PRONUM AND ANAPRO.PROPAR=ANAPRO_R.PROPAR AND ANAPRO.PROPAR='P' AND ANAPRO.PROIDMAILDEST <> '') AS QUANTE_MAIL
                    FROM (" . $sql . ") ANAPRO_R 
                ) T $wherePecTrx 
            ";
        return $sql;
    }

    private function svuotaDatiStampa() {
        $utente = App::$utente->getKey('nomeUtente');
        $tmppro_tabfin = $this->proLib->getGenericTab("SELECT ROWID FROM TMPPRO WHERE UTENTE='$utente'");
        if ($tmppro_tabfin) {
            foreach ($tmppro_tabfin as $tmppro_del) {
                if (!$this->deleteRecord($this->PROT_DB, 'TMPPRO', $tmppro_del['ROWID'], '', 'ROWID', false)) {
                    break;
                }
            }
        }
    }

    private function DecodAnamed($codice, $_tipoRic = 'codice', $_tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $_tipoRic, $_tutti);
        Out::valore($this->nameForm . '_Medcod', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_Mednom', $anamed_rec['MEDNOM']);
        return $anamed_rec;
    }

    private function DecodAnamedTrasm($codice, $_tipoRic = 'codice', $_tutti = 'no') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $_tipoRic, $_tutti);
        Out::valore($this->nameForm . '_TrasmDest', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_TrasmDest_Desc', $anamed_rec['MEDNOM']);
        return $anamed_rec;
    }

    private function decodRuoli($codice, $tipo = 'codice') {
        $anaruoli_rec = $this->proLib->getAnaruoli($codice, $tipo);
        Out::valore($this->nameForm . '_Ruocod', $anaruoli_rec['RUOCOD']);
        Out::valore($this->nameForm . '_Ruodes', $anaruoli_rec['RUODES']);
        return $anaruoli_rec;
    }

    private function decodUtenti($codice, $tipo = 'utelog') {
        $utenti_rec = $this->accLib->GetUtenti($codice, $tipo);
        Out::valore($this->nameForm . '_Proute', $utenti_rec['UTELOG']);
        return $utenti_rec;
    }

    private function controllaCampi() {
        return true;
    }

    private function elenca() {
        if (!$this->ControllaPrerequisitiRicerca()) {
            return false;
        }

        try {
            $sql = $this->CreaSql();
            if ($sql != false) {
                $ita_grid01 = new TableView($this->gridGest, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum(1);
                $ita_grid01->setPageRows($_POST[$this->gridGest]['gridParam']['rowNum']);
                $ita_grid01->setSortIndex('PRODAR DESC,PRONUM');
                $ita_grid01->setSortOrder('desc');
                Out::setFocus('', $this->nameForm . '_AltraRicerca');
// Elabora il risultato
                $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                    Out::msgStop("Selezione", "Nessun record trovato.");
                    $this->OpenRicerca();
                } else {   // Visualizzo la ricerca
                    Out::hide($this->divRic, '');
                    Out::show($this->divRis, '');
                    $this->Nascondi();
                    Out::show($this->nameForm . '_AltraRicerca');
                    Out::show($this->nameForm . '_Stampa');
                    $profilo = proSoggetto::getProfileFromIdUtente();
                    if ($profilo['OGGRIS_VISIBILITA'] === 1) {
                        Out::show($this->nameForm . '_VediOggettiRiservati');
                    }
//                    Out::show($this->nameForm.'_toolBar1');
                    TableView::enableEvents($this->gridGest);
                }
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
            App::log($e->getMessage());
        }
        App::log('Fine: time');
        App::log(microtime(true));
    }

    private function elaboraRecords($result_tab) {
        $anaent_32 = $this->proLib->GetAnaent('32');
        foreach ($result_tab as $key => $result_rec) {
            $ini_tag = $fin_tag = '';
            $result_tab[$key]['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $result_tab[$key]['CODICE'] = intval(substr($result_rec['PRONUM'], 4));
            $allegati = $this->proLibAllegati->checkPresenzaAllegati($result_rec['PRONUM'], $result_rec['PROPAR']);
            $prodar = $result_tab[$key]['PRODAR'];
            if ($result_rec['PROCAT'] == "0100" || $result_rec['PROCCA'] == "01000100") {
                $ini_tag = "<p style = 'background-color:yellow;'>";
                $fin_tag = "</p>";
            }
            if ($allegati) {
                $result_tab[$key]['PRONAF'] = '<span class="ui-icon ui-icon-document">Con Allegati</span><p></p>';
            } else {
                if ($anaent_32['ENTDE4'] == 1) {
                    $ini_tag = "<p style = 'background-color:yellow;'>";
                    $fin_tag = "</p>";

                    $result_tab[$key]['PRONAF'] = '<span class="ui-icon ui-icon-alert">Senza Allegati</span><p></p>';
                }
            }
//            if (substr($result_rec['PROPAR'], 1, 1) == 'A') {
            if ($result_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
                $fin_tag = "</p>";
            }
            $ini_tagOggetto = $ini_tag;
            $fin_tagOggetto = $fin_tag;
            if (trim(strtoupper($result_rec['PROTSP'])) == 'PEC' || $result_rec['QUANTE_MAIL'] > 0) {
                $ini_tagOggetto = '<div class="ita-html" style="width:18px; display:inline-block;vertical-align:top;"><span class="ita-tooltip ui-icon ui-icon-mail-closed" title="Da PEC"></span></div><div style="display:inline-block;vertical-align:top;">' . $ini_tagOggetto;
                $fin_tagOggetto = $fin_tagOggetto . '</div>';
            }
            //Se il protocollo è una "C" la provenienza è il Firmatario.
//            if (substr($result_rec['PROPAR'], 0, 1) == 'C') {
            if ($result_rec['PROPAR'] == 'C') {
                $result_tab[$key]['PRONOM'] = $result_rec['DESNOM_FIRMATARIO'];
            }

            $result_tab[$key]['OGGOGG'] = $ini_tagOggetto . $result_tab[$key]['OGGOGG'] . $fin_tagOggetto;
            $result_tab[$key]['PRONOM'] = $ini_tag . $result_tab[$key]['PRONOM'] . $fin_tag;
            $result_tab[$key]['PRODAS'] = $ini_tag . $result_tab[$key]['PRODAS'] . $fin_tag;
            $result_tab[$key]['PROPRE'] = $ini_tag . $result_tab[$key]['PROPRE'] . $fin_tag;
            $result_tab[$key]['PROORA'] = $ini_tag . $result_tab[$key]['PROORA'] . $fin_tag;
            $result_tab[$key]['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($prodar)) . $fin_tag;

            if ($result_rec['PROPRE'] > 0 && $result_rec['PROPARPRE'] != '') {
                $result_tab[$key]['LEGAME'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
            } else {
                if ($this->proLib->checkRiscontro($result_tab[$key]['ANNO'], $result_tab[$key]['CODICE'], $result_tab[$key]['PROPAR'])) {
                    $result_tab[$key]['LEGAME'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
                } else {
                    $result_tab[$key]['LEGAME'] = '';
                }
            }

            /* Vecchia Verifica presenza in fascicolo
              if ($result_rec['PROFASKEY']) {
              $result_tab[$key]['FASCICOLO'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
              } else {
              $result_tab[$key]['FASCICOLO'] = '';
              }
             */
            $retCheckFas = $this->proLibFascicolo->CheckProtocolloInFascicolo($result_rec['PRONUM'], $result_rec['PROPAR']);
            if ($retCheckFas) {
                $result_tab[$key]['FASCICOLO'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
            } else {
                $result_tab[$key]['FASCICOLO'] = '';
            }

            $visibile = 0;
            if ($this->proLib->checkRiservatezzaProtocollo($result_rec)) {
                $visibile = 1;
            }
            $result_tab[$key]['PROPAR'] = $ini_tag . $result_tab[$key]['PROPAR'] . $fin_tag;
            $result_tab[$key]['ANNO'] = $ini_tag . $result_tab[$key]['ANNO'] . $fin_tag;
            $result_tab[$key]['CODICE'] = $ini_tag . $result_tab[$key]['CODICE'] . $fin_tag;
            if ($visibile == 1 || $visibile == 2) {
                if ($this->visOggRiservati) {
//                    $result_tab[$key]['OGGOGG'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">" . $result_rec['OGGOGG'] . "</div></div>";
//                    $result_tab[$key]['PRONOM'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">" . $result_rec['PRONOM'] . "</div></div>";
                    $result_tab[$key]['OGGOGG'] = "<div style = \" background-color:lightgrey;\"><div style=\"border-radius: 10px;float:left;display:inline-block;background-color:lightgrey;color:black;\"> <i>R</i> </div></div>" . $result_tab[$key]['OGGOGG'];
                    $result_tab[$key]['PRONOM'] = "<div style = \" background-color:lightgrey;\"><div style=\"border-radius: 10px;float:left;display:inline-block;background-color:lightgrey;color:black;\"> <i>R</i> </div></div>" . $result_tab[$key]['PRONOM'];
                } else {
                    $result_tab[$key]['OGGOGG'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div></div>";
                    $result_tab[$key]['PRONOM'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div></div>";
                }
            }
            if ($result_rec['PROEME']) {
                $result_tab[$key]['PROPAR'] = "<p style = 'background-color:#FF6D6D;'>" . $result_tab[$key]['PROPAR'] . " (E)</p>";
            }
            $NotificheMail = $this->proLibMail->GetElencoNotifichePecProt($result_rec['PRONUM'], $result_rec['PROPAR']);
            if ($NotificheMail['INDICE_ANOMALIE']) {
                $result_tab[$key]['OGGOGG'] = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>" . $result_tab[$key]['OGGOGG'];
            }
        }
        return $result_tab;
    }

    private function decodTitolario($rowid, $tipoArc, $tipoTit = '') {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_Procat' . $tipoTit, $cat);
        Out::valore($this->nameForm . '_Clacod' . $tipoTit, $cla);
        Out::valore($this->nameForm . '_Fascod' . $tipoTit, $fas);
        Out::valore($this->nameForm . '_TitolarioDecod' . $tipoTit, $des);
    }

    private function DecodAnacat($codice, $tipo = 'codice', $tipoTit = '') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT', $tipoTit);
        } else {
            Out::valore($this->nameForm . '_Procat' . $tipoTit, '');
            Out::valore($this->nameForm . '_Clacod' . $tipoTit, '');
            Out::valore($this->nameForm . '_Fascod' . $tipoTit, '');
            Out::valore($this->nameForm . '_TitolarioDecod' . $tipoTit, '');
        }
        return $anacat_rec;
    }

    private function DecodAnacla($codice, $tipo = 'codice', $tipoTit = '') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA', $tipoTit);
        } else {
            Out::valore($this->nameForm . '_Clacod' . $tipoTit, '');
            Out::valore($this->nameForm . '_Fascod' . $tipoTit, '');
        }
        return $anacla_rec;
    }

    private function DecodAnafas($codice, $tipo = 'codice', $tipoTit = '') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS', $tipoTit);
        } else {
            Out::valore($this->nameForm . '_Fascod' . $tipoTit, '');
        }
        return $anafas_rec;
    }

    private function vaiAlProtocollo($pronum, $anno, $tipo) {
        if ($anno == '') {
            $anno = $this->workYear;
        }
        $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $anno . $pronum, $tipo);
        if ($anapro_rec) {
            Out::valore($this->nameForm . '_pronum', '');
            Out::setFocus('', $this->nameForm . '_pronum');
            if (!$this->returnModel) {
                $model = 'proArri';
            } else {
                $model = $this->returnModel;
            }
            if (!$this->returnEvent) {
                $event = 'openform';
            } else {
                $event = $this->returnEvent;
            }
            if (!$this->returnId) {
                $returnId = '';
            } else {
                $returnId = $this->returnId;
            }
            $_POST = array();
            $_POST['consultazione'] = $this->consultazione;
            $_POST['tipoProt'] = $anapro_rec['PROPAR'];
            $_POST['event'] = $event;
            $_POST['id'] = $returnId;
            $_POST[$this->nameForm . '_ANAPRO']['ROWID'] = $anapro_rec['ROWID'];
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        } else {
            $anapro_test_rec = $this->proLib->CheckEsistenzaProto($anno . $pronum, $tipo);
            if (!$anapro_test_rec) {
                Out::msgStop("Attenzione!", "Protocollo insesistente.");
            } else {
                Out::msgStop("Attenzione!", "Protocollo non accessibile.");
            }
            return;
        }
    }

    private function DecodAnaorg($codice, $tipo = 'codice', $codiceCcf = '', $anno = '') {
        $anaorg_rec = $this->proLib->GetAnaorg($codice, $tipo, $codiceCcf, $anno);
        Out::valore($this->nameForm . '_Proarg', $anaorg_rec['ORGCOD']);
        Out::valore($this->nameForm . '_FascicoloDecod', $anaorg_rec['ORGDES']);
        return $anaorg_rec;
    }

    public function CreaSqlUffGrafico() {
        return "SELECT DISTINCT ANAPRO.ROWID AS ROWID, ANAPRO.PROUOF, ANAUFF.UFFCOD, ANAUFF.UFFDES
            FROM ANAPRO ANAPRO 
            LEFT OUTER JOIN ANAUFF ANAUFF ON ANAPRO.PROUFF=ANAUFF.UFFCOD";
    }

    private function CaricaRicUffici() {
        $ArrUffici = array();
        $ExplodeUffici = array();
        if ($this->uffici) {
            $ExplodeUffici = explode('|', $this->uffici);
        }
        if ($ExplodeUffici) {
            foreach ($ExplodeUffici as $Ufficio) {
                $Anauff_rec = $this->proLib->GetAnauff($Ufficio, 'codice');
                $ArrUfficio['CODUFF'] = $Ufficio;
                $ArrUfficio['UFFICIO'] = $Anauff_rec['UFFDES'];
                $ArrUffici[] = $ArrUfficio;
            }
        }
        $ita_grid01 = new TableView(
                $this->gridRicUffici, array('arrayTable' => $ArrUffici,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows('10000');
        TableView::enableEvents($this->gridRicUffici);
        TableView::clearGrid($this->gridRicUffici);
        $ita_grid01->getDataPage('json');
    }

    private function GetUfficiProto($pronum, $propar) {
        $sql = "SELECT DISTINCT ANADES.DESCUF AS DESCUF, ANAUFF.UFFDES AS UFFDES FROM ANADES ANADES
                    LEFT OUTER JOIN ANAUFF ANAUFF ON ANADES.DESCUF=ANAUFF.UFFCOD
                    WHERE ANADES.DESNUM=$pronum AND ANADES.DESPAR='$propar' ";
        return ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
    }

    private function GetlIterScadenzeProto($pronum, $propar) {
        $sql = "SELECT DISTINCT(ITETERMINE) AS ITETERMINE,ITEFIN FROM ARCITE
                    WHERE ARCITE.ITEPRO=$pronum AND ARCITE.ITEPAR='$propar' AND ITEANNULLATO <> 1  AND (ARCITE.ITENODO='TRX' OR ARCITE.ITENODO='ASS') AND ITETERMINE <> ''
                    ORDER BY ITETERMINE ASC";
        return ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
    }

    public function GetScadenzeExcel() {
        $sql = $this->CreaSql();
        $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $ScadenzeTab = array();
        foreach ($Anapro_tab as $Anapro_rec) {

            $ArrScadenze = array();
            $ArrScadenze['CODICE'] = substr($Anapro_rec['PRONUM'], 4);
            $ArrScadenze['DATA REGISTRAZIONE'] = substr($Anapro_rec['PRODAR'], 6, 2) . '/' . substr($Anapro_rec['PRODAR'], 4, 2) . '/' . substr($Anapro_rec['PRODAR'], 0, 4);
            $ArrScadenze['ORA'] = $Anapro_rec['PROORA'];
            $ArrScadenze['TIPO'] = $Anapro_rec['PROPAR']; //str 0,1
            // Uffici
            $Uffici = '';
            $Uffici_tab = $this->GetUfficiProto($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            foreach ($Uffici_tab as $Uffici_rec) {
                $Uffici .= $Uffici_rec['UFFDES'] . "; ";
            }
            $ArrScadenze['UFFICI'] = substr($Uffici, 0, -2);
            $Oggetto = $Anapro_rec['OGGOGG'];
            if ($Anapro_rec['PROTSO'] == '1' || $Anapro_rec['PRORISERVA'] == '1') {
                $Oggetto = 'RISERVATO';
            }
            $ArrScadenze['OGGETTO'] = $Oggetto;
            $Arcite_tab = $this->GetlIterScadenzeProto($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            $Scadenze = '';
            $Stato = '';
            $IteFin = '';
            foreach ($Arcite_tab as $Arcite_rec) {
                if ($Arcite_rec['ITETERMINE']) {
                    $Scadenze .= substr($Arcite_rec['ITETERMINE'], 6, 2) . '/' . substr($Arcite_rec['ITETERMINE'], 4, 2) . '/' . substr($Arcite_rec['ITETERMINE'], 0, 4) . "<br>";
                    if ($Arcite_rec['ITEFIN']) {
                        $Stato .= 'Chiuso' . "<br>";
                        $IteFin .= substr($Arcite_rec['ITEFIN'], 6, 2) . '/' . substr($Arcite_rec['ITEFIN'], 4, 2) . '/' . substr($Arcite_rec['ITEFIN'], 0, 4) . "<br>";
                    } else {
                        $Stato .= 'Aperto' . "<br>";
                        $IteFin .= $Arcite_rec['ITEFIN'] . "<br>";
                    }
//                    $Scadenze.=$Stato . "<br>";
                }
            }
            $ArrScadenze['SCADENZA'] = $Scadenze;
            $ArrScadenze['STATO'] = $Stato;
            $ArrScadenze['CHIUSO IL'] = $IteFin;
            $ScadenzeTab[] = $ArrScadenze;
        }

        $ita_grid01 = new TableView('griglia', array('arrayTable' => $ScadenzeTab,
            'rowIndex' => 'idx'));
        $ita_grid01->setSortOrder('CODICE ASC,TIPO');
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        $ita_grid01->exportXLS('', 'ElencoScadenziario.xls');
    }

    /**
     * Esporta in Excel 
     * Nuova funzione per esportare solo le colonne visibili:
     *  Non serve esportare tutto.
     */
    public function EsportaExcel() {
        $sql = $this->CreaSql();
        $result_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $ValoriTabella = array();
        foreach ($result_tab as $key => $result_rec) {
            $ValoriTabella[$key]['TIPO'] = $result_rec['PROPAR'];
            $ValoriTabella[$key]['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $ValoriTabella[$key]['NUMERO'] = intval(substr($result_rec['PRONUM'], 4));
            $ValoriTabella[$key]['DATA_REG'] = date("d/m/Y", strtotime($result_rec['PRODAR']));
            $ValoriTabella[$key]['ORA'] = $result_rec['PROORA'];
            $ValoriTabella[$key]['PROTOCOLLO_PRECEDENTE'] = $result_rec['PROPRE'];
            $ValoriTabella[$key]['DATA_CARTE'] = date("d/m/Y", strtotime($result_rec['PRODAR']));
            $ValoriTabella[$key]['PROVENIENZA-DESTINATARIO'] = $result_rec['PRONOM'];
            if ($result_rec['PROPAR'] == 'C') {
                $ValoriTabella[$key]['PROVENIENZA-DESTINATARIO'] = $result_rec['DESNOM_FIRMATARIO'];
            }
            $ValoriTabella[$key]['INDIRIZZO'] = $result_rec['PROIND'];
            $ValoriTabella[$key]['CITTA'] = $result_rec['PROCIT'];
            $ValoriTabella[$key]['PROVINCIA'] = $result_rec['PROPRO'];
            $ValoriTabella[$key]['CAP'] = $result_rec['PROCAP'];
            $ValoriTabella[$key]['CODICE-FISCALE'] = $result_rec['MEDFIS'];
            $ValoriTabella[$key]['EMAIL'] = $result_rec['PROMAIL'];
            $ValoriTabella[$key]['OGGETTO'] = $result_rec['OGGOGG'];
            if ($result_rec['PRORISERVA'] == 1 || $result_rec['PROTSO'] == 1) {
                $ValoriTabella[$key]['OGGETTO'] = 'RISERVATO';
                $ValoriTabella[$key]['PROVENIENZA-DESTINATARIO'] = 'RISERVATO';
                $ValoriTabella[$key]['INDIRIZZO'] = 'RISERVATO';
                $ValoriTabella[$key]['CITTA'] = 'RISERVATO';
                $ValoriTabella[$key]['PROVINCIA'] = 'RISERVATO';
                $ValoriTabella[$key]['CAP'] = 'RISERVATO';
                $ValoriTabella[$key]['EMAIL'] = 'RISERVATO';
                $ValoriTabella[$key]['CODICE-FISCALE'] = 'RISERVATO';
            }
        }
        $ita_grid01 = new TableView($this->gridGest, array(
            'arrayTable' => $ValoriTabella));
        $ita_grid01->setSortIndex('PRONUM');
        $ita_grid01->exportXLS('', 'Anapro.xls');
    }

    public function GetElencoProtMittDestiAll() {
        $sql = $this->CreaSql();

        $result_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

        $ValoriTabella = array();
        foreach ($result_tab as $key => $result_rec) {
            $ValoriRiga = array();
            $ValoriRiga['TIPO'] = $result_rec['PROPAR'];
            $ValoriRiga['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $ValoriRiga['NUMERO'] = intval(substr($result_rec['PRONUM'], 4));
            $ValoriRiga['DATA_REG'] = date("d/m/Y", strtotime($result_rec['PRODAR']));
            $ValoriRiga['ORA'] = $result_rec['PROORA'];
            $ValoriRiga['PROTOCOLLO_PRECEDENTE'] = $result_rec['PROPRE'];
            $ValoriRiga['DATA_CARTE'] = date("d/m/Y", strtotime($result_rec['PRODAR']));
            $ValoriRiga['PROVENIENZA-DESTINATARIO'] = $result_rec['PRONOM'];
            if ($result_rec['PROPAR'] == 'C') {
                $ValoriRiga['PROVENIENZA-DESTINATARIO'] = $result_rec['DESNOM_FIRMATARIO'];
            }
            $ValoriRiga['INDIRIZZO'] = $result_rec['PROIND'];
            $ValoriRiga['CITTA'] = $result_rec['PROCIT'];
            $ValoriRiga['PROVINCIA'] = $result_rec['PROPRO'];
            $ValoriRiga['CAP'] = $result_rec['PROCAP'];
            $ValoriRiga['CODICE-FISCALE'] = $result_rec['PROFIS'];
            if (!$result_rec['PROFIS']) {
                $ValoriRiga['CODICE-FISCALE'] = $result_rec['MEDFIS'];
            }
            $ValoriRiga['EMAIL'] = $result_rec['PROMAIL'];
            $ValoriRiga['OGGETTO'] = $result_rec['OGGOGG'];

            if ($result_rec['PRORISERVA'] == 1 || $result_rec['PROTSO'] == 1) {
                $ValoriRiga = $this->SetRiservatezzaRiga($ValoriRiga);
            }
            $ValoriTabella[] = $ValoriRiga;
            /*
             * Elaboro Destinatari:
             */
            $DestinatariAgg = $this->proLib->caricaAltriDestinatari($result_rec['PRONUM'], $result_rec['PROPAR'], false);
            foreach ($DestinatariAgg as $Destinatario) {
                $ValoriRiga['PROVENIENZA-DESTINATARIO'] = $Destinatario['DESNOM'];
                $ValoriRiga['INDIRIZZO'] = $Destinatario['DESIND'];
                $ValoriRiga['CITTA'] = $Destinatario['DESCIT'];
                $ValoriRiga['PROVINCIA'] = $Destinatario['DESPRO'];
                $ValoriRiga['CAP'] = $Destinatario['DESCAP'];
                $ValoriRiga['CODICE-FISCALE'] = $Destinatario['DESFIS'];
                $ValoriRiga['EMAIL'] = $Destinatario['DESMAIL'];
                if ($result_rec['PRORISERVA'] == 1 || $result_rec['PROTSO'] == 1) {
                    $ValoriRiga = $this->SetRiservatezzaRiga($ValoriRiga);
                }
                $ValoriTabella[] = $ValoriRiga;
            }
            /*
             * Elaboro i Mittenti:
             */
            $MittentiAgg = $this->proLib->getPromitagg($result_rec['PRONUM'], 'codice', true, $result_rec['PROPAR']);
            foreach ($MittentiAgg as $Mittente) {
                $ValoriRiga['PROVENIENZA-DESTINATARIO'] = $Mittente['PRONOM'];
                $ValoriRiga['INDIRIZZO'] = $Mittente['PROIND'];
                $ValoriRiga['CITTA'] = $Mittente['PROCIT'];
                $ValoriRiga['PROVINCIA'] = $Mittente['PROPRO'];
                $ValoriRiga['CAP'] = $Mittente['PROCAP'];
                $ValoriRiga['CODICE-FISCALE'] = $Mittente['PROFIS'];
                $ValoriRiga['EMAIL'] = $Mittente['PROMAIL'];
                if ($result_rec['PRORISERVA'] == 1 || $result_rec['PROTSO'] == 1) {
                    $ValoriRiga = $this->SetRiservatezzaRiga($ValoriRiga);
                }
                $ValoriTabella[] = $ValoriRiga;
            }
        }



        $ita_grid01 = new TableView('griglia', array('arrayTable' => $ValoriTabella,
            'rowIndex' => 'idx'));
        $ita_grid01->setSortOrder('CODICE ASC,TIPO');
        $ita_grid01->setPageRows('100000');
        $ita_grid01->setPageNum(1);
        $ita_grid01->exportXLS('', 'ElencoProt_MittDestCompleto.xls');
    }

    public function SetRiservatezzaRiga($ValoriRiga) {
        $ValoriRiga['OGGETTO'] = 'RISERVATO';
        $ValoriRiga['PROVENIENZA-DESTINATARIO'] = 'RISERVATO';
        $ValoriRiga['INDIRIZZO'] = 'RISERVATO';
        $ValoriRiga['CITTA'] = 'RISERVATO';
        $ValoriRiga['PROVINCIA'] = 'RISERVATO';
        $ValoriRiga['CAP'] = 'RISERVATO';
        $ValoriRiga['EMAIL'] = 'RISERVATO';
        $ValoriRiga['CODICE-FISCALE'] = 'RISERVATO';
        return $ValoriRiga;
    }

    private function caricaUof() {
        $arrayUof = array();
        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        $prouof = '';
        if ($anamed_rec) {
            $arrayUof[] = array(
                'UFFCOD' => '',
                'SELECT' => '0',
                'UFFDES' => 'Tutti'
            );
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD'], 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
            $select = "1";
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $arrayUof[] = array(
                        'UFFCOD' => $uffdes_rec['UFFCOD'],
                        'SELECT' => $select,
                        'UFFDES' => $anauff_rec['UFFDES']
                    );
                    $select = '';
                }
            }
        }
        return $arrayUof;
    }

    public function ControllaPrerequisitiRicerca() {

        /*
         * Controllo Anno:
         */
        $Anno = $_POST[$this->nameForm . '_Anno'];
        $AnnoFas = $_POST[$this->nameForm . '_AnnoFascicolo'];
        $Dap = $_POST[$this->nameForm . '_Dal_periodo'];
        $Alp = $_POST[$this->nameForm . '_Al_periodo'];
        $Oggetto = $_POST[$this->nameForm . '_Oggetto'];
        // Mittente Destinatario e Interno
        $Nom = $_POST[$this->nameForm . '_Mednom'];
        $TrasmDest = $_POST[$this->nameForm . '_TrasmDest'];
        // N Protocollo
        $Dpr = substr($_POST[$this->nameForm . '_Dal_prot'], -6);
        $Apr = substr($_POST[$this->nameForm . '_Al_prot'], -6);
        // Numero fascicolo
        $Org = $_POST[$this->nameForm . '_Proarg'];
        if ($Anno) {
            //Controllo periodo e anno valorizzati insieme
            if ($Dap || $Alp) {
                $AnnoDap = $Dap ? substr($Dap, 0, 4) : $Anno;
                $AnnoAlp = $Alp ? substr($Alp, 0, 4) : $Anno;
                if ($Anno > $AnnoAlp || $Anno < $AnnoDap) {
                    Out::msgInfo("Attenzione", "L'anno inserito non coincide con il periodo indicato. ");
                    return false;
                }
            }
            return true;
        }
        // Avviso di valorizzare l'anno di riferimento se si cerca da numero a numero1
        if ($Dpr || $Apr) {
            Out::msgInfo("Attenzione", "Per la ricerca da numero a numero è richiesto l'anno di riferimento.");
            return false;
        }

        if (!$Dap || !$Alp) {
            Out::msgInfo("Attenzione", "Anno non valorizzato, occorre indicare il Periodo di ricerca.");
            return false;
        }
        $giorni = itaDate::dateDiffDays($Alp, $Dap);
        if ($giorni > 730) {
            // Controllo valorizzato almeno oggetto/ mitt dest e interni
            if (!$Nom && !$TrasmDest && !$Oggetto) {
                $Messaggio = "Il periodo indicato è superiore ai <b>2 anni</b>. <br>Occorre valorizare almeno uno dei seguenti campi:<br><div style=\"padding:5px\">";
                $Messaggio.="&bull; Oggetto<br>&bull; Mittente/Destinatario<br>&bull; Trasmissione interna<br></div>";
                Out::msgInfo("Attenzione", $Messaggio);
                return false;
            }
        }
        // Ricerca per fascicolo va dietro all'anno
        if ($Org && !$AnnoFas) {
            Out::msgInfo("Attenzione", "Per poter cercare un fascicolo occorre valorizzare l'Anno del fascicolo.");
            return false;
        }


        /*
         * Altrimenti il periodo è corretto
         */
        return true;
    }

}
