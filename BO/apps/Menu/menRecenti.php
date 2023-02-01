<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//
// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menLib.class.php';

function menRecenti() {
    $menRecenti = new menRecenti();
    $menRecenti->parseEvent();
    return;
}

class menRecenti extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = 'menRecenti';
    public $gridRecenti = 'menRecenti_gridRecenti';

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm('menRecenti', '', true, $container = $_POST['context']);
                Out::delContainer($_POST['context'] . "-wait");
                $Men_Recenti_tab = $this->PreparaDati();
                $this->CaricaGriglia($this->gridRecenti, $Men_Recenti_tab, '1', 10);
                break;
            case 'refresh':
                $Men_Recenti_tab = $this->PreparaDati();
                $this->CaricaGriglia($this->gridRecenti, $Men_Recenti_tab, '1', 10);
                break;
            case 'editGridRow': case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridRecenti:
                        $this->LanciaProgramma($_POST['rowid']);
                        break;
                }
                break;
        }
    }

    function PreparaDati() {
        $Men_Recenti_tab = menLib::loadRecent();
        foreach ($Men_Recenti_tab as $key => $Men_Recenti_rec) {
            $sql = "SELECT * FROM ita_menu WHERE me_menu = '".$Men_Recenti_rec['MENU']."'";
            $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = ".$Ita_menu_rec['me_id']." AND pm_voce = '".$Men_Recenti_rec['PROG']."'";
            $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $Men_Recenti_tab[$key]['DESCRIZIONE_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Recenti_tab[$key]['DESCRIZIONE_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }
        return $Men_Recenti_tab;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo='1', $pageRows=20, $caption='') {
        if ($caption) {
            out::codice("$('#$_griglia').setCaption('$caption');");
        }
        if (is_null($_appoggio))
            $_appoggio = array();
        $ita_grid01 = new TableView(
                        $_griglia,
                        array('arrayTable' => $_appoggio,
                            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    function LanciaProgramma($rowid) {
        $Men_Recenti_rec = menLib::getRecent($rowid);
        $_POST = array();
        $_POST['event'] = "onClick";
        $_POST['menu'] = $Men_Recenti_rec["MENU"];
        $_POST['prog'] = $Men_Recenti_rec["PROG"];
        $_POST['noSave'] = true;
        $model = 'menButton';
        //itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>
