<?php

/**
 *
 * IMPORTAZIONE DELLE PRATICHE NON ANCORA PROTOCOLLATE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <mchele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    03.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';

function praPermessiPassiPortlet() {
    $praPermessiPassiPortlet = new praPermessiPassiPortlet();
    $praPermessiPassiPortlet->parseEvent();
    return;
}

class praPermessiPassiPortlet extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $ITALWEB_DB;
    public $praLib;
    public $proLib;
    public $praPerms;
    public $utiEnte;
    public $accLib;
    public $praGobid;
    public $rowidAppoggio;
    public $rowidDipendente;
    public $rowidTipoPasso;
    public $nameForm = "praPermessiPassiPortlet";
    public $gridPassi = "praPermessiPassiPortlet_gridPassi";
    public $flag_assegnazioni;

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->proLib = new proLib();
        $this->utiEnte = new utiEnte();
        $this->praPerms = new praPerms();
        $this->accLib = new accLib();
        $this->praGobid = new praGobidManager();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
        if ($Filent_Rec_TabAss['FILVAL'] == 1) {
            $this->flagAssegnazioni = true;
        }



        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->rowidDipendente = App::$utente->getKey($this->nameForm . '_rowidDipendente');
        $this->rowidTipoPasso = App::$utente->getKey($this->nameForm . '_rowidTipoPasso');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_rowidDipendente', $this->rowidDipendente);
            App::$utente->setKey($this->nameForm . '_rowidTipoPasso', $this->rowidTipoPasso);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                Out::delContainer($_POST['context'] . "-wait");
                itaLib::openForm('praPermessiPassiPortlet', '', true, $container = $_POST['context'] . "-content");
                $this->CreaCombo();
                Out::hide($this->nameForm . "_divRadioTutti");
                Out::attributo($this->nameForm . "_TuttiM", "checked", "0", "checked");
                Out::attributo($this->nameForm . "_CaricoComp", "checked", "0", "checked");

                /*
                 * Lettura Parametro Gobid per cancellazione divIntestatario
                 */
                $urlRest = $this->praGobid->leggiParametro('RESTURL');
                if ($urlRest) {
                    TableView::showCol($this->gridPassi, "DATICURATORE");
                } else {
                    TableView::hideCol($this->gridPassi, "DATICURATORE");
                }


                $this->CaricaPassi("M_NI", "COMP_T");
                break;
            case 'dbClickRow':
                $propas_rec = $this->praLib->GetPropas($_POST['rowid'], "rowid");
                $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
                if (!$ret) {
                    if ($Utenti_rec['UTEANA__3'] != $propas_rec['PRORPA']) {
                        Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti");
                        break;
                    }
                }
                $bottoni = array();
                if ($propas_rec['PROVISIBILITA'] != "soloPasso") {
                    $bottoni['F8-Vai alla Pratica'] = array('id' => $this->nameForm . '_ApriPratica', 'model' => $this->nameForm, 'shortCut' => "f8");
                }
                $bottoni['F5-Vai al Passo'] = array('id' => $this->nameForm . '_ApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5");

                Out::msgQuestion("Passo seq. " . $propas_rec['PROSEQ'], $propas_rec['PRODPA'] . "<br><br>Cosa vuoi fare?", $bottoni);
                $this->rowidAppoggio = $_POST['rowid'];
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql($this->formData[$this->nameForm . "_TipiPasso"], $this->formData[$this->nameForm . "_Competenza"], $this->formData[$this->nameForm . "_PiantaOrganica"]);
                $ita_grid01 = new TableView($this->gridPassi, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('PRONUM');
                $ita_grid01->exportXLS('', 'passi_disponibili.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                $utente = "";
                if (substr($_POST[$this->nameForm . '_TipiPasso'], 0, 2) == "M_") {
                    $utente = App::$utente->getKey('nomeUtente');
                }
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array(
                    //"Sql" => $this->CreaSql($_POST[$this->nameForm . '_TipiPasso']), $_POST[$this->nameForm . '_Competenza'],
                    "Sql" => $this->CreaSql($this->formData[$this->nameForm . "_TipiPasso"], $this->formData[$this->nameForm . "_Competenza"], $this->formData[$this->nameForm . "_PiantaOrganica"]),
                    "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                    "Utente" => App::$utente->getKey('nomeUtente')
                );
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praPassiUte', $parameters);
                break;
            case 'onClickTablePager':
                $this->praLib->GetLegendaColoriPratiche($this->nameForm . '_daPortale', $this->nameForm . '_daPec', $this->nameForm . '_Altro');
                //
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'NUMERO') {
                    $ordinamento = 'PRONUM';
                }
                if ($ordinamento == 'RESPONSABILE') {
                    $ordinamento = 'RESPONSABILE';
                }
                if ($ordinamento == 'PROCEDIMENTO') {
                    $ordinamento = 'GESSTT ' . $_POST['sord'] . ' , GESATT';
                }

                //$sql = $this->CreaSql($_POST[$this->nameForm . '_TipiPasso'], $_POST[$this->nameForm . '_Competenza']);
                $sql = $this->CreaSql($this->formData[$this->nameForm . "_TipiPasso"], $this->formData[$this->nameForm . "_Competenza"], $this->formData[$this->nameForm . "_PiantaOrganica"]);
                if (!$sql) {
                    break;
                }
                TableView::disableEvents($this->gridPassi);
                TableView::clearGrid($this->gridPassi);
                $ita_grid01 = new TableView($this->gridPassi, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                TableView::enableEvents($this->gridPassi);

                $filent_rec = $this->praLib->GetFilent(24);
                foreach ($ita_grid01->getDataArray() as $proges_rec) {
                    $color = $this->praLib->GetColorPortlet($filent_rec, $proges_rec);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "NUMERO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px','background-color':'$color'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "GESDRI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROSEQ", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROINI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "DATIAGGIUNTIVI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "RESPONSABILE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PRODTP", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PRODPA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROGIO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                }

                break;
            case 'onClick';
                switch ($_POST['id']) {
                    case $this->nameForm . "_ApriPasso":
                        $model = 'praPasso';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $this->rowidAppoggio;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPermessiPasso';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . "_ApriPratica":

                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                        $model = 'praGestElenco';
                        itaLib::openForm($model);
                        /* @var $objModel praGest */
                        $objModel = itaModel::getInstance($model);
                        $_POST = array();
                        $objModel->setEvent('openform');
                        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                        $_POST['perms'] = $this->perms;
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . "_AssegnaPratica":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        //
                        $model = 'praAssegnaPraticaSimple';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['gesnum'] = $propas_rec['PRONUM'];
                        $_POST['rowidPasso'] = $this->rowidAppoggio;
                        $_POST['daPortlet'] = true;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnPraAssegnaPratica';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . "_Filtra":
                        $this->CaricaPassi($_POST[$this->nameForm . '_TipiPasso'], $_POST[$this->nameForm . '_Competenza']);
                        break;
                }
                break;
            //@TODO TOGLIERE SE NON SERVE
            case 'onChange';
                TableView::clearGrid($this->gridPassi);
                $this->CaricaPassi($_POST[$this->nameForm . '_TipiPasso'], $_POST[$this->nameForm . '_Competenza']);
                break;
            case 'returnPraAssegnaPratica':
                $this->CaricaPassi($_POST[$this->nameForm . '_TipiPasso'], $_POST[$this->nameForm . '_Competenza']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_rowidDipendente');
        App::$utente->removeKey($this->nameForm . '_rowidTipoPasso');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $this->close = true;
        if ($close)
            $this->close();
    }

    public function CaricaPassi($tipoPasso = "", $competenza = "") {
        TableView::enableEvents($this->gridPassi);
        TableView::reload($this->gridPassi);
        Out::setGridCaption($this->gridPassi, "Passi disponibili per l'utente " . App::$utente->getKey('nomeUtente'));
    }

    public function CreaSql($tipoPasso, $competenza, $piantaOrg) {
        $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
        switch ($competenza) {
            case "COMP_T":
                $whereMieiPassi = "AND (PROPAS.PROUTEEDIT = '" . App::$utente->getKey('nomeUtente') . "' OR PROPAS.PROUTEADD = '" . App::$utente->getKey('nomeUtente') . "' OR PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}') ";
                break;
            case "COMP_C":
                $whereMieiPassi = "AND PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}'";
                break;
            case "COMP_A":
                $whereMieiPassi = "AND ((PROPAS.PROUTEEDIT = '" . App::$utente->getKey('nomeUtente') . "' OR PROPAS.PROUTEADD = '" . App::$utente->getKey('nomeUtente') . "') AND PROPAS.PRORPA <> '{$Utenti_rec['UTEANA__3']}') ";
                break;
            default :
                $whereMieiPassi = "AND (PROPAS.PROUTEEDIT = '" . App::$utente->getKey('nomeUtente') . "' OR PROPAS.PROUTEADD = '" . App::$utente->getKey('nomeUtente') . "' OR PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}') ";
                break;
        }

        /*
         * Filtri per settore e servizio pianta organica
         */
        $profilo = proSoggetto::getProfileFromIdUtente();
        $Ananom_rec = $this->praLib->GetAnanom($profilo['COD_ANANOM']);
        $wherePiantaOrg = '';
        switch ($piantaOrg) {
            case "Miei":
                $wherePiantaOrg = "";
                break;
            case "Settore":
                $wherePiantaOrg = " AND PROPAS.PROSET = '" . $Ananom_rec['NOMSET'] . "' ";
                break;
            case "Servizio":
                $wherePiantaOrg = " AND PROPAS.PROSER = '" . $Ananom_rec['NOMSER'] . "' AND PROPAS.PROSET = '" . $Ananom_rec['NOMSET'] . "'";
                break;
        }
        if ($wherePiantaOrg) {
            $whereMieiPassi = "";
        }


        $wherePassi = '';
        switch ($tipoPasso) {
            case "M_I":
            case "I":
                $wherePassi = " AND T.QUANTIDEST>0 AND T.QUANTIDEST=T.QUANTISEND";
                break;
            case "M_IS":
            case "IS":
                $wherePassi = " AND T.QUANTIDEST>0 AND T.QUANTIDEST=T.QUANTISEND AND T.QUANTECONS<>T.QUANTISEND ";
                break;
            case "M_NI":
            case "NI":
                $wherePassi = " AND  (T.QUANTIDEST<>T.QUANTISEND OR T.QUANTIDEST=0 OR T.QUANTIDEST IS NULL)";
                break;
        }

        $whereVediChiusi = "AND PROGES.GESDCH = ''";
        if ($_POST[$this->nameForm . '_vediAncheChiusi'] == 1) {
            $whereVediChiusi = "";
        }

        //Filtri per ricerca tabella
        if ($_POST['NUMERO']) {
            $pratica = $_POST['NUMERO'];
            //$pratica_2 = addslashes($this->formData['NUMERO']);
            //  $whereNumero = " AND PRONUM LIKE '%" . addslashes($pratica) . "%' OR " . $this->PRAM_DB->strUpper('GESCODPROC') . " LIKE '%" . strtoupper($pratica_2) . "%'";
            $whereNumero = " AND SERIE LIKE '%" . addslashes($pratica) . "%'";
        }
        if ($_POST['GESDRI']) {
            if (strlen($_POST['GESDRI']) != 10) {
                Out::msgStop("Errore", "La data inserita non sembra essere formalmente corretta");
                return false;
            }
            $gesdri = substr($_POST['GESDRI'], 6, 4) . substr($_POST['GESDRI'], 3, 2) . substr($_POST['GESDRI'], 0, 2);
            $whereGesdri = " AND GESDRI LIKE '%$gesdri%'";
        }

        if ($_POST['PROCEDIMENTO']) {
            $descProc = addslashes(strtolower($this->formData['PROCEDIMENTO']));
            $whereDescProc = " AND (
                                " . $this->PRAM_DB->strLower('SETDES') . " LIKE '%$descProc%' OR 
                                " . $this->PRAM_DB->strLower('ATTDES') . " LIKE '%$descProc%'  OR 
                                " . $this->PRAM_DB->strLower('PRADES__1') . " LIKE '%$descProc%' OR
                                " . $this->PRAM_DB->strLower('PRADES__2') . " LIKE '%$descProc%' OR
                                " . $this->PRAM_DB->strLower('PRADES__3') . " LIKE '%$descProc%'
                                )";
        }
        if ($_POST['PROINI']) {
            if (strlen($_POST['PROINI']) != 10) {
                Out::msgStop("Errore", "La data inserita non sembra essere formalmente corretta");
                return false;
            }
            $proini = substr($_POST['PROINI'], 6, 4) . substr($_POST['PROINI'], 3, 2) . substr($_POST['PROINI'], 0, 2);
            $whereProini = " AND PROINI LIKE '%$proini%'";
        }
        if ($_POST['PRODPA']) {
            $descPasso = addslashes(strtolower($this->formData['PRODPA']));
            $whereDescPasso = " AND " . $this->PRAM_DB->strLower('PRODPA') . " LIKE '%$descPasso%'";
        }
        if ($_POST['PRODTP']) {
            $tipoPasso = strtolower($this->formData['PRODTP']);
            $whereTipoPasso = " AND " . $this->PRAM_DB->strLower('PRODTP') . " LIKE '%$tipoPasso%'";
        }
        if ($_POST['PROANN']) {
            $annotazioni = addslashes(strtolower($this->formData['PROANN']));
            $whereAnnotazioni = " AND " . $this->PRAM_DB->strLower('PROANN') . " LIKE '%$annotazioni%'";
            //$whereAnnotazioni = " AND LOWER(PROANN) LIKE '%$annotazioni%'";
        }
        if ($_POST['PROSEQ']) {
            $whereSeq = " AND PROSEQ = " . $this->formData['PROSEQ'];
        }
        if ($_POST['PROGIO']) {
            $wheregg = " AND PROGIO = " . $this->formData['PROGIO'];
        }
        if ($_POST['RESPONSABILE']) {
            $responsabile = addslashes(strtolower($_POST['RESPONSABILE']));
            $whereResp = " AND (" . $this->PRAM_DB->strLower('ANANOM.NOMCOG') . " LIKE '%$responsabile%' OR " . $this->PRAM_DB->strLower('ANANOM.NOMNOM') . " LIKE '%$responsabile%')";
        }
        if ($_POST['DATIAGGIUNTIVI']) {
            $whereAggiuntivi = " AND (
                                      " . $this->PRAM_DB->strLower('PRODAGDENOMIMPRESA.DAGVAL') . " LIKE '%" . addslashes(strtolower($_POST['DATIAGGIUNTIVI'])) . "%' OR
                                       " . $this->PRAM_DB->strLower('PRODAGCFIMPRESA.DAGVAL') . " LIKE '%" . addslashes(strtolower($_POST['DATIAGGIUNTIVI'])) . "%' OR
                                       " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESNOM') . " LIKE '%" . addslashes(strtolower($_POST['DATIAGGIUNTIVI'])) . "%'  OR
                                       " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESFIS') . " LIKE '%" . addslashes(strtolower($_POST['DATIAGGIUNTIVI'])) . "%' OR
                                       " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESPIVA') . " LIKE '%" . addslashes(strtolower($_POST['DATIAGGIUNTIVI'])) . "%')";
        }
        if ($_POST['DATICURATORE']) {
            $whereAggiuntivi = " AND (
                                      " . $this->PRAM_DB->strLower('PRODAGDENOMIMPRESA.DAGVAL') . " LIKE '%" . addslashes(strtolower($_POST['DATICURATORE'])) . "%' OR
                                      " . $this->PRAM_DB->strLower('PRODAGCFIMPRESA.DAGVAL') . " LIKE '%" . addslashes(strtolower($_POST['DATICURATORE'])) . "%' OR
                                      " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESNOM') . " LIKE '%" . addslashes(strtolower($_POST['DATICURATORE'])) . "%'  OR
                                      " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESFIS') . " LIKE '%" . addslashes(strtolower($_POST['DATICURATORE'])) . "%' OR
                                      " . $this->PRAM_DB->strLower('ANADESIMPRESA.DESPIVA') . " LIKE '%" . addslashes(strtolower($_POST['DATICURATORE'])) . "%'
                                    )";
        }
        $where_visibilita_arr = array();
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        $sql_visibilita_arr = array();
        /*
         * 
         * 
         * Vecchio
         * 
         */
//        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//            $sql_visibilita_arr[] = " (GESTSP = " . $retVisibilta['SPORTELLO'] . " OR T.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
//        }
        /*
         * 
         * 
         * Nuovo
         * 
         */
//        if (count($retVisibilta['SPORTELLI']) != 0) {
//            $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
//            $sql_visibilita_arr[] = " (GESTSP IN ($strSportelli) OR T.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
//        }
        if (count($retVisibilta['SPORTELLI']) != 0) {
            $arrSportelliAggregati = array();
            foreach ($retVisibilta['SPORTELLI'] as $key => $filtroSportello) {
                if (strpos($filtroSportello, '/') !== false) {
                    $arrSportelliAggregati[] = "'$filtroSportello'";
                    unset($retVisibilta['SPORTELLI'][$key]);
                }
            }
            $sqlArray = array();
            if (count($retVisibilta['SPORTELLI'])) {
                $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
                $sqlFiltroSportelli = "T.GESTSP IN ($strSportelli)";
                $sqlArray[] = $sqlFiltroSportelli;
            }
            if (count($arrSportelliAggregati)) {
                $strSportelliAggregati = implode(",", $arrSportelliAggregati);
                $sqlFiltroAggregati = "T.TSP_SPA IN ($strSportelliAggregati) ";
                $sqlArray[] = $sqlFiltroAggregati;
            }
            $sqlFiltro_tsp_spa = implode(" OR ", $sqlArray);
            $sql_visibilita_arr[] = " ($sqlFiltro_tsp_spa OR T.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
        }
        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
            $sql_visibilita_arr[] = " (GESSPA = " . $retVisibilta['AGGREGATO'] . " OR T.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
        }
        if (count($sql_visibilita_arr) == 0) {
            $where_visibilita_arr[] = " 1 = 1 ";
        }
        if (count($sql_visibilita_arr)) {
            $where_visibilita_arr[] = "(" . implode(" AND ", $sql_visibilita_arr) . ")";
        }

        $where_visibilita_arr[] = "
                        (
                            GESRES = '{$Utenti_rec['UTEANA__3']}' OR
                            T.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}' OR
                            T.ULT_RESP = '{$Utenti_rec['UTEANA__3']}'
                        )";

        if (count($where_visibilita_arr)) {
            $whereVisibilita = " AND (" . implode(' OR ', $where_visibilita_arr) . ")";
        }



        $annoSeguente = date('Y') - 1;

        //Per Senigallia
        $annoAncoraSeguente = date('Y') - 2;

        //Per Senigallia
        $annoAncoraPiuSeguente = date('Y') - 3;



        $sql = "SELECT * FROM ( 
                    SELECT  PROPAS.ROWID,
                            SETDES,
                            ATTDES,
                            PROGES.GESNUM AS GESNUM," .
                $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.DESCRIZIONE', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'", "PROGES.SERIEANNO") . " AS SERIE,
                            PROGES.GESTSP AS GESTSP,
                            PROGES.GESSPA AS GESSPA," .
                $this->PRAM_DB->strConcat('GESTSP', "'/'", 'GESSPA') . " AS TSP_SPA,
                            PROGES.GESDRE AS GESDRE, 
                            PROGES.GESDRI AS GESDRI, 
                            PROGES.GESPRO AS GESPRO, 
                            PROGES.GESDCH AS GESDCH, 
                            PROGES.GESRES AS GESRES, 
                            PROGES.GESSTT AS GESSTT, 
                            PROGES.GESATT AS GESATT, 
                            PROGES.GESPRA AS GESPRA, 
                            PROGES.GESCODPROC AS GESCODPROC, 
                            PROPAS.PRONUM AS PRONUM, 
                            PROPAS.PROSEQ AS PROSEQ, 
                            PROPAS.PRORIS AS PRORIS, 
                            PROPAS.PROANN AS PROANN, 
                            PROPAS.PROGIO AS PROGIO, 
                            PROPAS.PROTPA AS PROTPA, 
                            PROPAS.PRODTP AS PRODTP, 
                            PROPAS.PRODPA AS PRODPA, 
                            PROPAS.PROFIN AS PROFIN, 
                            PROPAS.PROVPA AS PROVPA, 
                            PROPAS.PROPUB AS PROPUB,
                            PROPAS.PROVPN AS PROVPN, 
                            PROPAS.PROPAK AS PROPAK, 
                            PROPAS.PROCTR AS PROCTR, 
                            PROPAS.PROALL AS PROALL, 
                            PROPAS.PRORPA AS PRORPA, 
                            PROPAS.PROVISIBILITA AS PROVISIBILITA, 
                            PROPAS.PROUTEADD AS PROUTEADD, 
                            PRODAGDENOMIMPRESA.DAGVAL AS DATIAGGIUNTIVI_IMPRESA, 
                            PROPAS.PROINI AS PROINI," .
                //ANADESIMPRESA.DESNOM AS ANADES_IMPRESA,
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
                            COUNT(PRAMITDESTBASE.ROWID) AS QUANTIDEST, 
                            COUNT(PRAMITDESTSEND.IDMAIL) AS QUANTISEND, 
                            COUNT(PRAMAILACC.ROWID) AS QUANTEACC, 
                            COUNT(PRAMAILCONS.ROWID) AS QUANTECONS,
                            (SELECT COUNT(ROWID) FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='A') AS RISCONTROA,
                            (SELECT COUNT(ROWID) FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='P') AS RISCONTROP,
                            (SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=PROGES.GESNUM AND PROOPE<>'')) AS ULT_RESP,
                            (SELECT PRORPA FROM PROPAS WHERE PRONUM=PROGES.GESNUM AND PRORPA='{$Utenti_rec['UTEANA__3']}' AND PROOPE='' GROUP BY PRORPA) AS FL_PRORPA," .
                $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2", "PRADES__3") . " AS DESC_PROC," .
                //(SELECT " . $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2", "PRADES__3") . " FROM ANAPRA WHERE PRANUM=PROPAS.PROPRO) AS DESC_PROC," .
                $this->PRAM_DB->strConcat("SETDES", "'\n'", "ATTDES", "'\n'", "PRADES__1", "PRADES__2", "PRADES__3") . " AS PROCEDIMENTO
                     

                    FROM 
                           (
                            SELECT
                                *
                            FROM
                                PROPAS
                            WHERE 
                                (
                                    " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '" . date('Y') . "' OR 
                                    " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '$annoSeguente' OR 
                                    " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '$annoAncoraSeguente' OR
                                    " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '$annoAncoraPiuSeguente'
                                ) AND PROPAS.PROFIN='' AND PROINI<>''
                                $whereMieiPassi 
                                $wherePiantaOrg
                            ) PROPAS 
                            LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA 
                            LEFT OUTER JOIN PROGES ON PROGES.GESNUM=PROPAS.PRONUM 
                            LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
                            LEFT OUTER JOIN ANASET ON PROGES.GESSTT=ANASET.SETCOD  
                            LEFT OUTER JOIN ANAATT ON PROGES.GESATT=ANAATT.ATTCOD  
                            LEFT OUTER JOIN ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM  
                            LEFT OUTER JOIN PRAMITDEST PRAMITDESTBASE ON PRAMITDESTBASE.KEYPASSO=PROPAS.PROPAK AND (PRAMITDESTBASE.TIPOCOM='D') 
                            LEFT OUTER JOIN PRAMITDEST PRAMITDESTSEND ON PRAMITDESTSEND.ROWID=PRAMITDESTBASE.ROWID AND PRAMITDESTSEND.IDMAIL<>'' 
                            LEFT OUTER JOIN PRAMAIL PRAMAILACC ON PRAMITDESTSEND.IDMAIL=PRAMAILACC.COMIDMAIL AND PRAMAILACC.TIPORICEVUTA='accettazione' 
                            LEFT OUTER JOIN PRAMAIL PRAMAILCONS ON PRAMITDESTSEND.IDMAIL=PRAMAILCONS.COMIDMAIL AND PRAMAILCONS.TIPORICEVUTA='avvenuta-consegna' 
                            LEFT OUTER JOIN PRODAG PRODAGDENOMIMPRESA ON PRODAGDENOMIMPRESA.DAGNUM=PROPAS.PRONUM AND PRODAGDENOMIMPRESA.DAGTIP='DenominazioneImpresa'
                            LEFT OUTER JOIN PRODAG PRODAGCFIMPRESA ON PRODAGCFIMPRESA.DAGNUM=PROPAS.PRONUM AND PRODAGCFIMPRESA.DAGTIP='Codfis_InsProduttivo'
                            LEFT OUTER JOIN ANADES ANADESIMPRESA ON ANADESIMPRESA.DESNUM=PROPAS.PRONUM AND (ANADESIMPRESA.DESRUO='0004' OR ANADESIMPRESA.DESRUO='0005' OR ANADESIMPRESA.DESRUO='0021')
                   WHERE PROPAS.PROOPE='' $whereVediChiusi $whereGesdri $whereProini $whereDescPasso $whereTipoPasso $whereSeq $wheregg $whereResp $whereAggiuntivi $whereAnnotazioni $whereDescProc
                   GROUP BY
	                  PROPAS.ROWID
             ) T 
           WHERE 1 $wherePassi $whereNumero $whereVisibilita";
        //Out::msgInfo("sql", $sql);
        return $sql;
    }

    public function elaboraRecord($passi_view) {
        if ($passi_view) {
            $count = count($passi_view);
            for ($keyPasso = 0; $keyPasso < $count; $keyPasso++) {
                $value = $passi_view[$keyPasso];
                //
                if ($value['PROFIN']) {
                    $passi_view[$keyPasso]['STATOPASSO'] = '<span class="ita-icon ita-icon-check-green-24x24">Passo Chiuso</span>';
                } elseif ($value['PROINI'] && $value['PROFIN'] == "") {
                    $passi_view[$keyPasso]['STATOPASSO'] = '<span class="ita-icon ita-icon-check-red-24x24">Passo Aperto</span>';
                }
                // $passi_view[$keyPasso]['NUMERO'] = $this->praLib->ElaboraProgesSerie($value['PRONUM']);
                $passi_view[$keyPasso]['NUMERO'] = $value['SERIE'];
                if ($value['GESCODPROC']) {
                    $passi_view[$keyPasso]['NUMERO'] .= "<br><b>" . $value['GESCODPROC'] . "</b>";
                }
                $passi_view[$keyPasso]['STATOCOM'] = $this->praLib->GetIconStatoCom($value['PROPAK']);
                if ($value['RISCONTROA'] && $value['RISCONTROP']) {
                    $passi_view[$keyPasso]['STATOCOM'] .= '<div style="background-color: blue;color: white;padding: 2px;margin: 2px;font-weight: bold;">Riscontro</div>'; // . $passi_view[$keyPasso]['STATOCOM'];
                }
                $datiInsProd = $this->praLib->DatiImpresa($value['PRONUM']);
                $passi_view[$keyPasso]['DATIAGGIUNTIVI'] = "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";

                $datiCuratore = $this->praLib->DatiSoggettoRuolo($value['PRONUM'], praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUOCOD']);
                $passi_view[$keyPasso]['DATICURATORE'] = "<div class=\"ita-Wordwrap\">" . $datiCuratore['DENOMINAZIONE'] . "</div><div class=\"ita-Wordwrap\">" . $datiCuratore['FISCALE'] . "</div>";
            }
        }
        return $passi_view;
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_PiantaOrganica', 1, "Miei", "1", "I miei Passi");
        Out::select($this->nameForm . '_PiantaOrganica', 1, "Settore", "0", "I Passi del Settore");
        Out::select($this->nameForm . '_PiantaOrganica', 1, "Servizio", "0", "I Passi del Servizio");
    }

}

?>