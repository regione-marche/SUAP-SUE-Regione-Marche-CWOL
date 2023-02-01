<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';

function utiCercaAnel() {
    $utiCercaAnel = new utiCercaAnel();
    $utiCercaAnel->parseEvent();
    return;
}

class utiCercaAnel extends itaModel {

    public $ANEL_DB;
    public $nameForm = "utiCercaAnel";
    public $divGes = "utiCercaAnel_divGestione";
    public $gridAnagra = "utiCercaAnel_gridAnagra";

    function __construct() {
        parent::__construct();
        try {
            $this->catLib = new catLib();
            $this->ANEL_DB = $this->catLib->getANELDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
//                $this->dettaglioSoggetto($this->cf);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagra:
                        $model = 'utiVediAnel';
                        $rowid = $_POST['rowid'];
                        $sql = "SELECT * FROM ANAGRA WHERE ROWID = $rowid";
                        $Anagra_rec = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
                        // PRENDO DA LAVORO
                        $sql = " SELECT * FROM LAVORO WHERE CODTRI = " . $Anagra_rec['CODTRI'];
                        $Lavoro_rec = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);

                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['cf'] = $Lavoro_rec['FISCAL'];
                        $_POST['returnModel'] = $this->nameForm;
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
//                        Out::closeDialog($this->nameForm);
                        break;
                }
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnagra:
                        $Cognome = $_POST[$this->nameForm . '_ric_cognome'];
                        $Nome = $_POST[$this->nameForm . '_ric_nome'];

                        TableView::clearGrid($this->gridAnagra);
                        $sql = "SELECT * FROM ANAGRA WHERE COGNOM LIKE '$Cognome%'";
                        if ($Nome) {
                            $sql.=" AND NOME LIKE '$Nome%' ";
                        }

                        $ita_grid10 = new TableView($this->gridAnagra, array(
                            'sqlDB' => $this->ANEL_DB,
                            'sqlQuery' => $sql));
                        $ita_grid10->setPageRows(1000000);
                        $ordine = $_POST['sidx'];
                        if ($_POST['sidx'] == 'EVENTO') {
                            $ordine = 'CODTRI';
                        }
                        if ($_POST['sidx'] == 'DATANASCITA') {
                            $ordine = 'AANAT,MMNAT,GGNAT';
                        }
                        $ita_grid10->setSortIndex($ordine);
                        $ita_grid10->setSortOrder($_POST['sord']);
                        // Elabora il risultato
                        $Result_tab_tmp = $ita_grid10->getDataArray();
                        $Result_tab = $this->elaboraRecords($Result_tab_tmp);
                        if ($ita_grid10->getDataPageFromArray('json', $Result_tab)) {
                            TableView::enableEvents($this->gridAnagra);
                        }
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->Elenca();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divRisultato', '', 0);
        Out::show($this->nameForm . '_divRicerca', '', 200);
        Out::clearFields($this->nameForm, $this->nameForm . '_divRicerca');
        Out::setFocus('', $this->nameForm . '_ric_cognome');
    }

    public function Elenca() {
        $Cognome = $_POST[$this->nameForm . '_ric_cognome'];
        $Nome = $_POST[$this->nameForm . '_ric_nome'];

        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_divRisultato', '', 200);
        Out::hide($this->nameForm . '_divRicerca', '', 0);


        TableView::clearGrid($this->gridAnagra);
        $sql = "SELECT * FROM ANAGRA WHERE COGNOM LIKE '$Cognome%'";
        if ($Nome) {
            $sql.=" AND NOME LIKE '$Nome%' ";
        }

        $ita_grid10 = new TableView($this->gridAnagra, array(
            'sqlDB' => $this->ANEL_DB,
            'sqlQuery' => $sql));
        $ita_grid10->setPageRows(10000);
        $ita_grid10->setSortIndex('CODTRI');
        $ita_grid10->setSortOrder('asc');

        // Elabora il risultato
        $Result_tab_tmp = $ita_grid10->getDataArray();
        $Result_tab = $this->elaboraRecords($Result_tab_tmp);
        if ($ita_grid10->getDataPageFromArray('json', $Result_tab)) {
            TableView::enableEvents($this->gridAnagra);
        }
    }

    function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $mm = str_pad($Result_rec['MMNAT'], 2, "0", STR_PAD_LEFT);
            $gg = str_pad($Result_rec['GGNAT'], 2, "0", STR_PAD_LEFT);
            $data = $Result_rec['AANAT'] . $mm . $gg;
            $Result_tab[$key]['DATANASCITA'] = $data;
            // EVENTO !!! 
            $evento = "";
            if ($Result_rec['AAMOR']){
                $evento ="Deceduto";
            } else {
                $sql = "SELECT * FROM IMMIGR WHERE CODTRI = ". $Result_rec['CODTRI'];
                $Immgr_rec = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
                if ($Immgr_rec){
                    $dataImm = $Immgr_rec['AAIMMI'] . str_pad($Immgr_rec['MMIMMI'], 2, "0", STR_PAD_LEFT) . str_pad($Immgr_rec['GGIMMI'], 2, "0", STR_PAD_LEFT);
                    $dataEmi = $Immgr_rec['AAEMIG'] . str_pad($Immgr_rec['MMEMIG'], 2, "0", STR_PAD_LEFT) . str_pad($Immgr_rec['GGEMIG'], 2, "0", STR_PAD_LEFT);
                    if ($dataEmi > $dataImm){
                        $evento ="Emigrato";
                    }
                }
            }
            $Result_tab[$key]['EVENTO'] = $evento;
        }
        return $Result_tab;
    }

}

?>
