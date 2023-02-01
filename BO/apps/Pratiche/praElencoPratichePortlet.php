<?php

/**
 *
 * IMPORTAZIONE DELLE PRATICHE NON ANCORA PROTOCOLLATE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    31.01.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praElencoPratichePortlet() {
    $praElencoPratichePortlet = new praElencoPratichePortlet();
    $praElencoPratichePortlet->parseEvent();
    return;
}

class praElencoPratichePortlet extends itaModel {

    public $PRAM_DB;
    public $ITALWEB_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praElencoPratichePortlet";
    public $gridCtrRichieste = "praElencoPratichePortlet_gridCtrRichieste";
    public $proric_rec = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->utiEnte = new utiEnte();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm('praElencoPratichePortlet', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");
                Out::attributo($this->nameForm . "_NoStarweb", "checked", "0", "checked");
                $this->CaricaRichieste("NS");
                break;
            case 'dbClickRow':
                $model = 'praCtrRichieste';
                $rowid = $_POST['rowid'];
                $_POST = array();
                $_POST['event'] = 'dbClickRow';
                $_POST['id'] = "praCtrRichieste_gridCtrRichieste";
                $_POST['rowid'] = $rowid;
                $_POST['daPortlet'] = "true";
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnEvent'] = 'returnPraElencoPratiche';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridCtrRichieste, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('RICNUM');
                $ita_grid01->exportXLS('', 'procedimenti_online.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praCtrProc', $parameters);
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'NUMERO') {
                    $ordinamento = 'RICNUM';
                }
                if ($ordinamento == 'INTESTATARIO') {
                    $ordinamento = 'RICCOG';
                }
                if ($ordinamento == 'RICEZ') {
                    $ordinamento = 'RICDAT';
                }
                if ($ordinamento == 'DESC_PRO') {
                    $ordinamento = 'RICPRO';
                }
                if ($ordinamento == 'SPORTELLO_AGGREGATO') {
                    $ordinamento = 'RICSPA';
                }
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql($_POST[$this->nameForm . '_TipoInvio']);
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($tableSortOrder);
                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'returnPraElencoPratiche';
                $model = "praGestElenco";
                $datMail = $_POST['datiMail'];
                $daPortlet = $_POST['daPortlet'];
                $_POST = array();
                //$_POST['event'] = "returnCtrRichieste";
                //$_POST['model'] = $model;
                $_POST['daPortlet'] = $daPortlet;
                $_POST['datiMail'] = $datMail;
                $_POST['tipoReg'] = 'consulta';
                itaLib::openForm($model);
                $objModel = itaModel::getInstance($model);
                $objModel->setEvent("returnCtrRichieste");
                $objModel->parseEvent();
                $this->CaricaRichieste();
                break;
            case 'onChange';
                TableView::clearGrid($this->gridCtrRichieste);
                $this->CaricaRichieste($_POST[$this->nameForm . '_TipoInvio']);
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

    public function CaricaRichieste($tipoInvio) {
        $sql = $this->CreaSql($tipoInvio);
        try {
            $ita_grid01 = new TableView($this->gridCtrRichieste, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(10000);
            $ita_grid01->setSortIndex('RICNUM');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
            $ita_grid01->getDataPageFromArray('json', $Result_tab);
            TableView::enableEvents($this->gridCtrRichieste);
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    public function CreaSql($tipoInvio) {
        switch ($tipoInvio) {
            case "T":
                $sqlStato = "";
                break;
            case "NS":
                $sqlStato = " PRORIC.RICSTA='01' AND ";
                break;
            case "S":
                $sqlStato = " PRORIC.RICSTA='91' AND ";
                break;
        }
//        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//        $whereVisibilita = '';
//        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//            $whereVisibilita.=" AND RICTSP = " . $retVisibilta['SPORTELLO'];
//        }
//        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
//            $whereVisibilita.=" AND RICSPA = " . $retVisibilta['AGGREGATO'];
//        }
        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();
        $sql = "SELECT
            PRORIC.RICNUM AS RICNUM,
            PRORIC.ROWID AS ROWID,
            PRORIC.RICRES AS RICRES,
            PRORIC.RICTIM AS RICTIM,
            PRORIC.RICSPA AS RICSPA,
            PRORIC.RICDAT AS RICDAT,
            PRORIC.RICRPA AS RICRPA,
            PRORIC.RICSTA AS RICSTA,
            ANAPRA.PRADES__1 AS PRADES__1,
            PRORIC.RICDRE AS RICDRE," .
                $this->PRAM_DB->strConcat("RICCOG", "' '", "RICNOM") . " AS INTESTATARIO
            FROM PRORIC PRORIC
              LEFT OUTER JOIN ANAPRA ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
              LEFT OUTER JOIN PROGES PROGES ON PROGES.GESPRA=PRORIC.RICNUM
              LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM
            WHERE
              (RICSTA = 01 OR RICSTA = 91) AND
              RICRUN = '' AND
              PROGES.GESPRA IS NULL AND
              $sqlStato
              PROPAS.PRORIN IS NULL"
                . $whereVisibilita;


//        $sql = "SELECT
//            PRORIC.RICNUM AS RICNUM,
//            PRORIC.ROWID AS ROWID,
//            PRORIC.RICRES AS RICRES,
//            PRORIC.RICTIM AS RICTIM,
//            PRORIC.RICSPA AS RICSPA,
//            PRORIC.RICDAT AS RICDAT,
//            PRORIC.RICRPA AS RICRPA,
//            PRORIC.RICSTA AS RICSTA,
//            ANAPRA.PRADES__1 AS PRADES__1,
//            PRORIC.RICDRE AS RICDRE," .
//                $this->PRAM_DB->strConcat("RICCOG", "' '", "RICNOM") . " AS INTESTATARIO
//            FROM PRORIC PRORIC
//              LEFT OUTER JOIN ANAPRA ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
//            WHERE (RICSTA = 01 OR RICSTA = 91) AND
//              RICNUM NOT IN (SELECT GESPRA FROM PROGES WHERE GESPRA<>'') AND
//              RICNUM NOT IN (SELECT PRORIN FROM PROPAS WHERE PRORIN<>'')" 
//                . $whereVisibilta;
        return $sql;
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $Result_rec['RICNUM'] . "'
                                  AND (DAGTIP = 'DenominazioneImpresa' OR DAGKEY = 'IC_DEMOM_IMPRESA')", false);
            $color = "";
            if ($Result_rec['RICSTA'] != "91" && $Result_rec['RICRPA'] == "") {
                $Result_tab[$key]["NUMERO"] = substr($Result_rec['RICNUM'], 4) . "/" . substr($Result_rec['RICNUM'], 0, 4);
                if ($Result_rec['RICDAT'] != "" && $Result_rec['RICTIM'] != "") {
                    $Result_tab[$key]["RICEZ"] = substr($Result_rec['RICDAT'], 6, 2) . "/" . substr($Result_rec['RICDAT'], 4, 2) . "/" . substr($Result_rec['RICDAT'], 0, 4) . " (" . $Result_rec['RICTIM'] . ")";
                } else {
                    $Result_tab[$key]["RICEZ"] = "";
                }
                if ($Result_rec['RICSPA'] != 0) {
                    $Anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
                    $Result_tab[$key]["SPORTELLO_AGGREGATO"] = $Anaspa_rec['SPADES'];
                }
                if ($Ricdag_rec) {
                    $Result_tab[$key]["IMPRESA"] = $Ricdag_rec['RICDAT'];
                }
            } else {
                if ($Result_rec['RICSTA'] == "91") {
                    $color = "red";
                } else if ($Result_rec['RICRPA']) {
                    $color = "blue";
                }
                $Result_tab[$key]["NUMERO"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec['RICNUM'], 4) . "/" . substr($Result_rec['RICNUM'], 0, 4) . "</span>";
                $Result_tab[$key]["RICDRE"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec['RICDRE'], 6, 2) . "/" . substr($Result_rec['RICDRE'], 4, 2) . "/" . substr($Result_rec['RICDRE'], 0, 4) . "</span>";
                if ($Result_rec['RICDAT'] != "" && $Result_rec['RICTIM'] != "") {
                    $Result_tab[$key]["RICEZ"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec['RICDAT'], 6, 2) . "/" . substr($Result_rec['RICDAT'], 4, 2) . "/" . substr($Result_rec['RICDAT'], 0, 4) . " (" . $Result_rec['RICTIM'] . ")" . "</span>";
                } else {
                    $Result_tab[$key]["RICEZ"] = "";
                }
                $Result_tab[$key]["INTESTATARIO"] = "<span style=\"color:$color;font-weight:bold;\">" . $Result_rec['INTESTATARIO'] . "</span>";
                if ($Result_rec['RICSPA'] != 0) {
                    $Anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
                    $Result_tab[$key]["SPORTELLO_AGGREGATO"] = "<span style=\"color:$color;font-weight:bold;\">" . $Anaspa_rec['SPADES'] . "</span>";
                }
                $Result_tab[$key]["PRADES__1"] = "<span style=\"color:$color;font-weight:bold;\">" . $Result_rec['PRADES__1'] . "</span>";
                if ($Ricdag_rec) {
                    $Result_tab[$key]["IMPRESA"] = "<span style=\"color:$color;font-weight:bold;\">" . $Ricdag_rec['RICDAT'] . "</span>";
                }
            }
        }
        return $Result_tab;
    }

}

?>
