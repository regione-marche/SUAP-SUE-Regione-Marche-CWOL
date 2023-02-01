<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';

function utiCapContab() {
    $utiCapContab = new utiCapContab();
    $utiCapContab->parseEvent();
    return;
}

class utiCapContab extends itaModel {

    public $CITYWARE_DB;
    public $nameForm = "utiCapContab";
    public $segLib;
    public $gridElencoCapitoli = "utiCapContab_gridElencoCapitoli";
    public $capitoli;

    function __construct() {
        parent::__construct();
        $this->segLib = new segLib();
        $this->CITYWARE_DB = $this->segLib->getCITYWARE();
        $this->capitoli = App::$utente->getKey($this->nameForm . '_capitoli');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_capitoli', $this->capitoli);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Nascondi();
                $this->CreaCombo();
                Out::show($this->nameForm . '_Elenca');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->elencaCapitoli();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->Nascondi();
                        Out::show($this->nameForm . '_divGestione');
                        Out::hide($this->nameForm . '_divRisultato');
                        TableView::clearGrid($this->gridElencoCapitoli);
                        Out::show($this->nameForm . '_Elenca');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridElencoCapitoli:
                        $this->caricaGriglia($this->gridElencoCapitoli, $this->capitoli, 2);
                        break;
                }
                break;
            case 'dbClickRow':
                $indice = $_POST['rowid'];
                if ($indice >= 0) {
                    $model = $this->returnModel;
                    $_POST = array();
                    $_POST['event'] = $this->returnEvent;
                    $_POST['capitolo'] = $this->capitoli[$indice];
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
        App::$utente->removeKey($this->nameForm . '_capitoli');
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

    public function CreaCombo() {
        $AnnoCorr = date('Y');
        $AnnoCorr = $AnnoCorr + 2;
        $AnnoIniz = $AnnoCorr - 17;
        Out::select($this->nameForm . '_ANNO', 1, "", "", "");
        for ($i = $AnnoIniz; $i <= $AnnoCorr; $i++) {
            Out::select($this->nameForm . '_ANNO', 1, $i, "0", $i);
        }
    }

    public function elencaCapitoli() {
        $codMec = $_POST[$this->nameForm . '_CODMEC'];
        $codCap = $_POST[$this->nameForm . '_CODCAP'];
        $desCap = $_POST[$this->nameForm . '_DESCAP'];
        $anno = $_POST[$this->nameForm . '_ANNO'];
        $sql_contab = $this->segLib->sqlContabilita();

        $sql = "
            SELECT
               *
            FROM
                ($sql_contab) CAPITOLI
            WHERE 1=1";
        if ($codMec != '') {
            $sql.=" AND CAPITOLI.COD_MECCAN = '$codMec'";
        }
        if ($codCap != '') {
            $sql.=" AND CAPITOLI.CAPITOLO = '$codCap' ";
        }
        if ($desCap != '') {
            $sql.=" AND CAPITOLI.DESCRIZIONE LIKE '%$desCap%'";
        }
        if ($anno != '') {
            $sql.=" AND CAPITOLI.ESERCIZIO = $anno";
        }
        $sql.= " ORDER BY CAPITOLI.E_S, CAPITOLI.ESERCIZIO, CAPITOLI.CAPITOLO";

        $this->capitoli = ItaDB::DBSQLSelect($this->CITYWARE_DB, $sql, true);

        if ($this->capitoli) {
            $this->caricaGriglia($this->gridElencoCapitoli, $this->capitoli);
            $this->Nascondi();
            Out::show($this->nameForm . '_AltraRicerca');
            Out::hide($this->nameForm . '_divGestione');
            Out::show($this->nameForm . '_divRisultato');
        } else {
            Out::msgInfo('AVVISO', 'Non trovati capitoli da visualizzare.');
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
