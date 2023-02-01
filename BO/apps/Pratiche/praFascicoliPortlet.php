<?php

/**
 *
 * PORTLET VISUALIZZAZIONE PRATICHE PER UTENTE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    20.04.2015
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
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function praFascicoliPortlet() {
    $praFascicoliPortlet = new praFascicoliPortlet();
    $praFascicoliPortlet->parseEvent();
    return;
}

class praFascicoliPortlet extends itaModel {

    public $PRAM_DB;
    public $ITALWEB_DB;
    public $praLib;
    public $praPerms;
    public $utiEnte;
    public $accLib;
    public $nameForm = "praFascicoliPortlet";
    public $gridGest = "praFascicoliPortlet_gridGest";
    public $flag_assegnazioni;

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->utiEnte = new utiEnte();
        $this->praPerms = new praPerms();
        $this->accLib = new accLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
        if ($Filent_Rec_TabAss['FILVAL'] == 1) {
            $this->flagAssegnazioni = true;
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':

                Out::delContainer($_POST['context'] . "-wait");
                itaLib::openForm($this->nameForm, '', true, $container = $_POST['context'] . "-content");
                Out::attributo($this->nameForm . "_Miei", "checked", "0", "checked");
                $this->CaricaFascicoli();
                break;
            case 'dbClickRow':
                $proges_rec = $this->praLib->GetProges($_POST['rowid'], "rowid");
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
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $this->elaboraRecordsXls($Result_tab1);
                $ita_grid02 = new TableView($this->gridGest, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->setSortIndex('PRATICA');
                $ita_grid02->setSortOrder('desc');
                $ita_grid02->exportXLS('', 'Fascicoli_disponibili.xls');
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
                    "Sql" => $this->CreaSql($_POST[$this->nameForm . '_TipiPasso']), $_POST[$this->nameForm . '_Competenza'],
                    "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                    "Utente" => App::$utente->getKey('nomeUtente')
                );
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praPassiUte', $parameters);
                break;
            case 'onClickTablePager':
                $this->praLib->GetLegendaColoriPratiche($this->nameForm . '_daPortale', $this->nameForm . '_daPec', $this->nameForm . '_Altro');
                //
                $ordinamento = $_POST['sidx'];
                if ($ordinamento != 'IMPRESA' && $ordinamento != 'FISCALE') {
                    if ($ordinamento == 'PRIORITA_RICH') {
                        if ($_POST['sord'] == 'desc') {
                            $tmpSord = "DESC";
                            $_POST['sord'] = 'desc';
                        } else {
                            $tmpSord = "ASC";
                            $_POST['sord'] = 'asc';
                        }
                        $ordinamento = "PRIORITA_RICH $tmpSord, GESNUM";
                    }
                    if ($ordinamento == 'RICEZ') {
                        $ordinamento = "GESDRI";
                    }
                    if ($ordinamento == 'DESCPROC') {
                        $ordinamento = "PRADES__1";
                    }
                    if ($ordinamento == 'NOTE') {
                        $ordinamento = "GESNOT";
                    }
                    if ($ordinamento == 'GIORNI') {
                        $ordinamento = "NUMEROGIORNI";
                    }
                    if ($ordinamento == 'SPORTELLO') {
                        $ordinamento = "GESTSP";
                    }
                    //
                    $sql = $this->CreaSql();
                    TableView::disableEvents($this->gridGest);
                    TableView::clearGrid($this->gridGest);
                    $ita_grid01 = new TableView($this->gridGest, array(
                        'sqlDB' => $this->PRAM_DB,
                        'sqlQuery' => $sql));
                    $ita_grid01->setPageNum($_POST['page']);
                    $ita_grid01->setPageRows($_POST['rows']);
                    $ita_grid01->setSortIndex($ordinamento);
                    $ita_grid01->setSortOrder($_POST['sord']);
                    $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                    $ita_grid01->getDataPageFromArray('json', $Result_tab);
                    TableView::enableEvents($this->gridGest);

                    $filent_rec = $this->praLib->GetFilent(24);
                    foreach ($ita_grid01->getDataArray() as $proges_rec) {
                        $color = $this->praLib->GetColorPortlet($filent_rec, $proges_rec);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESNUM", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px','background-color':'$color'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "ANTECEDENTE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESPRA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESDRE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "RICEZ", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "DESNOM", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "IMPRESA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "DESCPROC", "", "{'padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "NOTE", "", "{'padding-top':'2px','padding-right':'2px'}", "", false);
                        TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "SPORTELLO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                        //TableView::setRowData($this->gridGest, $proges_rec['ROWID'], $proges_rec, "{'color':'$color'}");
                    }
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
                    case $this->nameForm . "_FiltraFasc":
                        $this->CaricaFascicoli();
                        break;
                    case $this->nameForm . "_Cerca":
                        $model = 'praGestElenco';
                        itaLib::openForm($model);
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $this->close = true;
        if ($close)
            $this->close();
    }

    public function CaricaFascicoli() {
        TableView::enableEvents($this->gridGest);
        TableView::reload($this->gridGest);
        Out::setGridCaption($this->gridGest, "Fascicoli disponibili per l'utente " . App::$utente->getKey('nomeUtente'));
    }

    public function CreaSql() {
        $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));

        /*
         * Filtro per visibilita
         * 
         * Visibilta per permessi sportello on-line
         */
        $whereVisibilitaSportello = $whereVisibilitaSportelloTmp = '';
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        $sql_visibilita_arr = array();

        /*
         * 
         * 
         * Nuovo
         * 
         */
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
                $sqlFiltroSportelli = "U.GESTSP IN ($strSportelli)";
                $sqlArray[] = $sqlFiltroSportelli;
            }
            if (count($arrSportelliAggregati)) {
                $strSportelliAggregati = implode(",", $arrSportelliAggregati);
                $sqlFiltroAggregati = "U.TSP_SPA IN ($strSportelliAggregati) ";
                $sqlArray[] = $sqlFiltroAggregati;
            }
            $sqlFiltro_tsp_spa = implode(" OR ", $sqlArray);
            $sql_visibilita_arr[] = " ($sqlFiltro_tsp_spa OR U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
        }
        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
            $sql_visibilita_arr[] = " GESSPA = " . $retVisibilta['AGGREGATO'];
        }
        if (count($sql_visibilita_arr)) {
            $whereVisibilitaSportelloTmp = " (" . implode(" AND ", $sql_visibilita_arr) . ")";
            $whereVisibilitaSportello = " OR $whereVisibilitaSportelloTmp";
        }

        /*
         * Visibilta per sistema assegnazioni
         */
        $whereVisibilitaAssegnazioni = "
                        (
                            U.GESRES = '{$Utenti_rec['UTEANA__3']}' OR
                            U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}' OR
                            U.ULTRES = '{$Utenti_rec['UTEANA__3']}'
                        )";


        /*
         * M = i miei fascicoli, quelli di cui sono responsabile o assegnatario o responsabile passo
         * T = i miei fascicoli, vedi M o quelli dove ho visibilita di sportello
         * 
         */
        switch ($_POST[$this->nameForm . '_TipiFascicoli']) {
            case "M":
                $whereVisibilita = $whereVisibilitaAssegnazioni;
                break;
            case "V":
                //$whereVisibilita = "$whereVisibilitaAssegnazioni OR $whereVisibilitaSportello";
                $whereVisibilita = "$whereVisibilitaAssegnazioni $whereVisibilitaSportello";
                break;
            case "T":
                $whereVisibilita = "1";
                break;
        }

        $annoSeguente = date('Y') - 1;

        //Per Senigallia
        $annoAncoraSeguente = date('Y') - 2;

        $oggi = date('Ymd');
        $sql = "
            SELECT
                *
            FROM 
            (
                SELECT
                    *,
                    (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=P.GESNUM AND PROOPE<>'') AS N_ASSEGNAZIONI,
                    (SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=P.GESNUM AND PROOPE<>'')) AS ULTRES,
                    (SELECT PRORPA FROM PROPAS WHERE PRONUM=P.GESNUM AND PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROOPE='' GROUP BY PRORPA) AS FL_PRORPA
                FROM
                (
                    SELECT
                        DISTINCT PROGES.ROWID AS ROWID,
                        PROGES.GESNUM AS GESNUM," .
                $this->PRAM_DB->strConcat("SERIEANNO", "LPAD(SERIEPROGRESSIVO, 6, '0')") . " AS ORDER_GESNUM,
                        PROGES.GESDRE AS GESDRE,
                        PROGES.GESDRE AS ORDER_GESDRE,
                        PROGES.GESDRI AS GESDRI," .
                $this->PRAM_DB->dateDiff(
                        $this->PRAM_DB->coalesce(
                                $this->PRAM_DB->nullIf("GESDCH", "''"), "'$oggi'"
                        ), 'GESDRI'
                ) . " AS NUMEROGIORNI,
                        PROGES.GESORA AS GESORA,
                        PROGES.GESDCH AS GESDCH,
                        PROGES.GESPRA AS GESPRA,
                        PROGES.GESPRA AS ORDER_GESPRA,
                        PROGES.GESTSP AS GESTSP,
                        PROGES.GESSPA AS GESSPA," .
                $this->PRAM_DB->strConcat("GESTSP", "'/'", "GESSPA") . " AS TSP_SPA,
                        PROGES.GESNOT AS GESNOT,
                        PROGES.GESPRE AS GESPRE,
                        PROGES.GESDSC AS GESDSC,
                        PROGES.GESSTT AS GESSTT,
                        PROGES.GESATT AS GESATT,
                        PROGES.GESOGG AS GESOGG,
                        PROGES.GESNPR AS GESNPR,
                        CAST(PROGES.GESNPR AS UNSIGNED),
                        PROGES.GESRES AS GESRES," .
                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
                        PROGES.GESPRO AS GESPRO,
                        PROGES.GESCODPROC AS GESCODPROC,
                        ANAPRA.PRADES__1 AS PRADES__1
                    FROM 
                        PROGES PROGES
                    LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
                    LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                    WHERE 
                        (" . $this->PRAM_DB->subString('GESNUM', 1, 4) . " = '" . date('Y') . "' OR " . $this->PRAM_DB->subString('GESNUM', 1, 4) . " = '$annoSeguente' OR " . $this->PRAM_DB->subString('GESNUM', 1, 4) . " = '$annoAncoraSeguente') AND PROGES.GESDCH = ''
                         GROUP BY PROGES.ROWID
                ) P
            ) U WHERE 1 AND $whereVisibilita";
        return $sql;
    }

    function elaboraRecordsXls($Result_tab) {
        $Result_tab_new = array();
        foreach ($Result_tab as $key => $Result_rec) {
            //$Result_tab_new[$key]['PRATICA'] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Result_tab_new[$key]['PRATICA'] = $this->praLib->ElaboraProgesSerie($Result_rec['GESNUM']);
            $Result_tab_new[$key]['RICHIESTA_ONLINE'] = "";
            if ($Result_rec['GESPRA']) {
                $Result_tab_new[$key]['RICHIESTA_ONLINE'] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            }
            $Result_tab_new[$key]["DATA_REGISTRAZIONE"] = substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4);
            $Result_tab_new[$key]["DATA_RICEZIONE"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4);
            $Result_tab_new[$key]["RESPONSABILE"] = $Result_rec['RESPONSABILE'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            $Result_tab_new[$key]["INTESTATARIO"] = "";
            if ($Anades_rec) {
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'] . "<br>" . $Anades_rec['DESTEL'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'] . "<br>" . $Anades_rec['DESTEL'];
            }
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab_new[$key]['IMPRESA'] = $datiInsProd['IMPRESA'] . "<br>" . $datiInsProd['FISCALE'];

            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab_new[$key]['SETTORE'] = $anaset_rec['SETDES'];
                $Result_tab_new[$key]['ATTIVITA'] = $anaatt_rec['ATTDES'];
                $Result_tab_new[$key]['PROCEDIMENTO'] = $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
                $Result_tab_new[$key]['OGGETTO'] = $Result_rec['GESOGG'];
            }

            $Result_tab_new[$key]["NOTE"] = $Result_rec['GESNOT'];
            $Result_tab_new[$key]["AGGREGATO"] = $Result_tab[$key]["SPORTELLO"] = "";
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab_new[$key]["SPORTELLO"] = $anatsp_rec['TSPDES'];
            }
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $Result_tab_new[$key]["AGGREGATO"] = $anaspa_rec['SPADES'];
            }

            $Result_tab[$key]["NUMERO_GIORNI"] = $Result_rec['NUMEROGIORNI'];
            $Result_tab_new[$key]["DATA_CHIUSURA"] = "";
            if ($Result_rec['GESDCH']) {
                $Result_tab_new[$key]["DATA_CHIUSURA"] = substr($Result_rec['GESDCH'], 6, 2) . "/" . substr($Result_rec['GESDCH'], 4, 2) . "/" . substr($Result_rec['GESDCH'], 0, 4);
            }
        }
        return $Result_tab_new;
    }

    function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]["GESNUM"] = "<div><b>" . $this->praLib->ElaboraProgesSerie($Result_rec['GESNUM']) . "</b></div>";
            // $Result_tab[$key]["GESNUM"] = "<div><b>" . intval(substr($Result_rec['GESNUM'], 4, 6)) . "/" . substr($Result_rec['GESNUM'], 0, 4) . "</b></div>";

            $gespra = "<div> </div>";
            if ($Result_rec['GESPRA']) {
                $gespra = "<div>" . intval(substr($Result_rec['GESPRA'], 4, 6)) . "/" . substr($Result_rec['GESPRA'], 0, 4) . "</div>";
            }

            $gesnpr = "<div> </div>";
            if ($Result_rec['GESNPR'] != 0) {
                $gesnpr = "<div>" . intval(substr($Result_rec['GESNPR'], 4, 6)) . "/" . substr($Result_rec['GESNPR'], 0, 4) . "</div>";
            }
            $gescodproc = "<div> </div>";
            if ($Result_rec['GESCODPROC']) {
                $gescodproc = "<div><b>" . $Result_rec['GESCODPROC'] . "</b></div>";
            }
            $Result_tab[$key]["GESNUM"] .= $gespra . $gesnpr . $gescodproc;

            $Result_tab[$key]["ORDER_GESNUM"] = $Result_rec['GESNUM'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            if ($Anades_rec) {
                $Result_tab[$key]["DESNOM"] = "<div style =\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div></div>";
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $Result_tab[$key]["DESNOM"] = "<div style =\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div></div>";
            }
            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab[$key]['DESCPROC'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div><div>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'] . "</div><div>" . $Result_rec['GESOGG'] . "</div></div>";
            }

            if ($Result_rec['GESNOT']) {
                $Result_tab[$key]['NOTE'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\">" . $Result_rec['GESNOT'] . "</div>";
            }
            if ($Result_rec['GESPRA'] != 0) {
                $Result_tab[$key]["GESPRA"] = intval(substr($Result_rec['GESPRA'], 4, 6)) . "/" . substr($Result_rec['GESPRA'], 0, 4);
                $Result_tab[$key]["ORDER_GESPRA"] = $Result_rec['GESPRA'];
            } else {
                $Result_tab[$key]["GESPRA"] = "";
            }
            if ($Result_rec['GESDRI'] != "" && $Result_rec['GESORA'] != "") {
                $Result_tab[$key]["RICEZ"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4) . "<br>(" . $Result_rec['GESORA'] . ")";
            } else {
                $Result_tab[$key]["RICEZ"] = "";
            }
            $Result_tab[$key]["ORDER_GESDRE"] = $Result_rec['GESDRE'];
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $aggregato = $anaspa_rec['SPADES'];
            }
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab[$key]["SPORTELLO"] = "<div class=\"ita-Wordwrap\">" . $anatsp_rec['TSPDES'] . "</div><div>$aggregato</div>";
            }
            $opacity = "";
            if (!$Result_rec['GESDCH']) {
                $opacity1 = (($Result_tab[$key]["NUMEROGIORNI"] <= 60) ? $Result_tab[$key]["NUMEROGIORNI"] * (100 / 60) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
            $Result_tab[$key]["GIORNI"] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $Result_tab[$key]["NUMEROGIORNI"] . '</span></div>';

            if ($Result_rec['GESPRE']) {
                $Result_tab[$key]['ANTECEDENTE'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
            }

            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $Result_rec['GESNUM'] . "%' AND PASFIL NOT LIKE '%info'";
            $pasdoc_tab = $this->praLib->getGenericTab($sql, true);
            if ($pasdoc_tab) {
                $non_valido = false;
                $validi = array();
                foreach ($pasdoc_tab as $pasdoc_rec) {
                    if ($pasdoc_rec['PASSTA'] == "N") {
                        $non_valido = true;
                    } elseif ($pasdoc_rec['PASSTA'] == "V") {
                        $validi[] = $pasdoc_rec;
                    }
                }
                if ($non_valido === true) {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-red-24x24\">Ci sono allegati non validi</span>";
                } else {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-grey-24x24\">Ci sono allegati da controllare</span>";
                }
                if ($validi == $pasdoc_tab) {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-green-24x24\">Tutti gli allegati sono stati validati</span>";
                }
            }

            //
            //valorizzo impresa e codice fiscale sulla tabella
            //
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab[$key]['IMPRESA'] = "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";
        }
        return $Result_tab;
    }

}

?>