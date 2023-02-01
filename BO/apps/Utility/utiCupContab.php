<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';

function utiCupContab() {
    $utiCupContab = new utiCupContab();
    $utiCupContab->parseEvent();
    return;
}

class utiCupContab extends itaModel {

    public $CITYWARE_DB;
    public $nameForm = "utiCupContab";
    public $segLib;
    public $gridElencoCup = "utiCupContab_gridElencoCup";
    public $cig;

    function __construct() {
        parent::__construct();
        $this->segLib = new segLib();
        $this->CITYWARE_DB = $this->segLib->getCITYWARE();
        $this->cup = App::$utente->getKey($this->nameForm . '_cup');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_cup', $this->cup);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Nascondi();
                Out::show($this->nameForm . '_Elenca');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->elencaCup();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->Nascondi();
                        Out::show($this->nameForm . '_divGestione');
                        Out::hide($this->nameForm . '_divRisultato');
                        TableView::clearGrid($this->gridElencoCup);
                        Out::show($this->nameForm . '_Elenca');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridElencoCup:
                        $this->caricaGriglia($this->gridElencoCup, $this->cup, 2);
                        break;
                }
                break;
            case 'dbClickRow':
                $indice = $_POST['rowid'];
                if ($indice >= 0) {
                    $model = $this->returnModel;
                    $_POST = array();
                    $_POST['event'] = $this->returnEvent;
                    $_POST['daticup'] = $this->cup[$indice];
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                    $this->returnToParent();
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_cup');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divRisultato');
    }

    public function elencaCup() {
        $codCup = $_POST[$this->nameForm . '_CUP'];
        $desBreve = $_POST[$this->nameForm . '_DES_BREVE'];
        $desCup = $_POST[$this->nameForm . '_DES_CUP'];
        $dataInizio = $_POST[$this->nameForm . '_DATAINIZIO'];
        $dataFine = $_POST[$this->nameForm . '_DATAFINE'];
        $sql_cupcontab = $this->segLib->sqlCupContabilita();
        $sql = "
            SELECT
                   *
            FROM
                ($sql_cupcontab) CUP
            WHERE 1=1";
        if ($codCup != '') {
            $sql.=" AND CUP.COD_CUP LIKE '%$codCup%'";
        }
        if ($desBreve != '') {
            $sql.=" AND CUP.DES_BREVE LIKE '%$desBreve%' ";
        }
        if ($desCup != '') {
            $sql.=" AND CUP.DES_CUP LIKE '%$desCup%'";
        }
        if ($dataInizio != '') {
            $sql.=" AND CUP.DATAINIZ >= $dataInizio";
        }
        if ($dataFine != '') {
            $dataFine = date('Y-m-d', strtotime($dataFine));
            $sql.=" AND CUP.DATAFINE <= $dataFine";
        }

        $this->cup = ItaDB::DBSQLSelect($this->CITYWARE_DB, $sql, true);

        if ($this->cup) {
            $this->caricaGriglia($this->gridElencoCup, $this->cup);
            $this->Nascondi();
            Out::show($this->nameForm . '_AltraRicerca');
            Out::hide($this->nameForm . '_divGestione');
            Out::show($this->nameForm . '_divRisultato');
        } else {
            Out::msgInfo('AVVISO', 'Non trovati cup da visualizzare.');
        }
    }

    public function CaricaGriglia($griglia, $dati, $tipo = '1', $pageRows = '22') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $dati,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows($pageRows);
        if ($tipo != '1') {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setSortIndex($_POST['sidx']);
            $ita_grid01->setSortOrder($_POST['sord']);
        } else {
            $ita_grid01->setPageNum(1);
        }
        TableView::enableEvents($griglia);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($griglia);
        }
    }

}

?>
