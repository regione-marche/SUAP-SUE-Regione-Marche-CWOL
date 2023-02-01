<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menGetinidata.php';
include_once './apps/Menu/menLib.class.php';

function menPersonal() {
    $menPersonal = new menPersonal();
    $menPersonal->parseEvent();
    return;
}

class menPersonal extends itaModel {

    public $ITALWEB_DB;
    public $ITALSOFT_DB;
    public $nameForm = 'menPersonal';
    public $gridRecenti = 'menPersonal_gridRecenti';
    public $gridFrequenti = 'menPersonal_gridFrequenti';
    public $gridPreferiti = 'menPersonal_gridPreferiti';
    public $menLib;

    function __construct() {
        parent::__construct();
        if (!$this->ITALSOFT_DB) {
            try {
                $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        $this->menLib = new menLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openButton':
                ob_clean();
                $menuId = $_POST['menuId'];
                $menuKey = $_POST['rootMenu'];
                header("Content-Type: text/html; charset=ISO-8859-1\n");

                echo '<ul class="col" id="' . $this->nameForm . '_listRecenti">';
                echo $this->getHtmlPopupMenu();
                echo '</ul>';
                exit;

            case 'openportlet':
                itaLib::openForm('menPersonal', '', true, $container = $_POST['context']);
                Out::delContainer($_POST['context'] . "-wait");
                $Men_Recenti_tab = $this->PreparaDati_ini();
                $Men_Frequenti_tab = $this->PreparaDatiFrequenti_ini();
                $Men_Preferiti_tab = $this->PreparaDatiPreferiti_ini();
                $this->CaricaGriglia($this->gridRecenti, $Men_Recenti_tab, '1', 10);
                $this->CaricaGrigliaFrequenti($this->gridFrequenti, $Men_Frequenti_tab, '1', 100);
                $this->CaricaGrigliaPreferiti($this->gridPreferiti, $Men_Preferiti_tab, '1', 1000);
                break;

            case 'refresh':
                $Men_Recenti_tab = $this->PreparaDati_ini();
                $Men_Frequenti_tab = $this->PreparaDatiFrequenti_ini();
                $Men_Preferiti_tab = $this->PreparaDatiPreferiti_ini();
                $this->CaricaGriglia($this->gridRecenti, $Men_Recenti_tab, '1', 10);
                $this->CaricaGrigliaFrequenti($this->gridFrequenti, $Men_Frequenti_tab, '1', 100);
                $this->CaricaGrigliaPreferiti($this->gridPreferiti, $Men_Preferiti_tab, '1', 1000);

                /*
                 * Per menu a tendina
                 */
                Out::codice('$( "#' . $this->nameForm . '_listRecenti" ).html(\'' . str_replace("'", "\'", $this->getHtmlPopupMenu()) . '\');');
                Out::codice('$( "#' . $this->nameForm . '_listRecenti" ).menu( "refresh" );');
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridRecenti:

                        break;
                    case $this->gridFrequenti:

                        break;
                    case $this->gridPreferiti:
                        $this->CancellaProgrammaPreferiti($_POST['rowid']);
                        break;
                }

                break;
            case 'editGridRow': case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridRecenti:
                        $this->LanciaProgrammaRecenti($_POST['rowid']);
                        break;
                    case $this->gridFrequenti:
                        $this->LanciaProgrammaFrequenti($_POST['rowid']);
                        break;
                    case $this->gridPreferiti:
                        $this->LanciaProgrammaPreferiti($_POST['rowid']);
                        break;
                }
                break;

            case 'onClick':
                if (isset($_POST['menu']) && isset($_POST['prog'])) {
                    $this->LanciaProgramma($_POST['menu'], $_POST['prog']);
                    break;
                }

                $this->menLib->lanciaProgrammaModel('menDeskConfig');
                break;
        }
    }

    private function getHtmlPopupMenu() {
        $html = '';

        $html .= '<li style="padding: 5px;" class="ui-widget-header ui-corner-all"><b>Recenti</b></li>';
        foreach ($this->PreparaDati_ini() as $menRecenti) {
            $html .= '<li><a href="#' . $this->nameForm . '?menu=' . $menRecenti['MENU'] . '&prog=' . $menRecenti['PROG'] . '">';
            $html .= '<span style="display: inline-block; width: 140px; margin-right: 5px; overflow: hidden; opacity: .7; vertical-align: bottom;">' . $menRecenti['DESCRIZIONE_MENU'] . '</span> ';
            $html .= $menRecenti['DESCRIZIONE_PROG'];
            $html .= '</a></li>';
        }

        return $html;
    }

    function PreparaDati() {
        $Men_Recenti_tab = menLib::loadRecent();
        foreach ($Men_Recenti_tab as $key => $Men_Recenti_rec) {
            $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Men_Recenti_rec['MENU'] . "'";
            $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_menu_rec['me_id'] . " AND pm_voce = '" . $Men_Recenti_rec['PROG'] . "'";
            $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $Men_Recenti_tab[$key]['DESCRIZIONE_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Recenti_tab[$key]['DESCRIZIONE_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }
        return $Men_Recenti_tab;
    }

    function PreparaDati_ini() {
        $Men_Recenti_tab = menLib::loadRecent();
        foreach ($Men_Recenti_tab as $key => $Men_Recenti_rec) {
            $Ita_menu_rec = $this->menLib->GetIta_menu_ini($Men_Recenti_rec['MENU']);
            $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_ini($Men_Recenti_rec['MENU'], $Men_Recenti_rec['PROG']);
            $Men_Recenti_tab[$key]['DESCRIZIONE_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Recenti_tab[$key]['DESCRIZIONE_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }
        return $Men_Recenti_tab;
    }

    function PreparaDatiFrequenti() {
        $Men_Frequenti_tab = menLib::loadFrequent();
        foreach ($Men_Frequenti_tab as $key => $Men_Frequenti_rec) {
            $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Men_Frequenti_rec['FR_MENU'] . "'";
            $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            if ($Ita_menu_rec) {
                $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_menu_rec['me_id'] . " AND pm_voce = '" . $Men_Frequenti_rec['FR_PROG'] . "'";
                $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                $Men_Frequenti_tab[$key]['DESC_FR_MENU'] = $Ita_menu_rec['me_descrizione'];
                $Men_Frequenti_tab[$key]['DESC_FR_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
            }
        }
        return $Men_Frequenti_tab;
    }

    function PreparaDatiFrequenti_ini() {
        $Men_Frequenti_tab = menLib::loadFrequent();
        foreach ($Men_Frequenti_tab as $key => $Men_Frequenti_rec) {
            $Ita_menu_rec = $this->menLib->GetIta_menu_ini($Men_Frequenti_rec['FR_MENU']);
            if ($Ita_menu_rec) {
                $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_ini($Men_Frequenti_rec['FR_MENU'], $Men_Frequenti_rec['FR_PROG']);
                $Men_Frequenti_tab[$key]['DESC_FR_MENU'] = $Ita_menu_rec['me_descrizione'];
                $Men_Frequenti_tab[$key]['DESC_FR_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
            }
        }
        return $Men_Frequenti_tab;
    }

    function PreparaDatiPreferiti() {
        $Men_Preferiti_tab = menLib::loadBookMark();
        foreach ($Men_Preferiti_tab as $key => $Men_Preferiti_rec) {
            $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Men_Preferiti_rec['PR_MENU'] . "'";
            $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_menu_rec['me_id'] . " AND pm_voce = '" . $Men_Preferiti_rec['PR_PROG'] . "'";
            $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            $Men_Preferiti_tab[$key]['DESC_PR_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Preferiti_tab[$key]['DESC_PR_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }
        return $Men_Preferiti_tab;
    }

    function PreparaDatiPreferiti_ini() {
        $Men_Preferiti_tab = menLib::loadBookMark();
        foreach ($Men_Preferiti_tab as $key => $Men_Preferiti_rec) {
            $Ita_menu_rec = $this->menLib->GetIta_menu_ini($Men_Preferiti_rec['PR_MENU']);
            $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_ini($Men_Preferiti_rec['PR_MENU'], $Men_Preferiti_rec['PR_PROG']);
            $Men_Preferiti_tab[$key]['DESC_PR_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Preferiti_tab[$key]['DESC_PR_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }
        return $Men_Preferiti_tab;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = 20, $caption = '') {
        if ($caption) {
            out::codice("$('#$_griglia').setCaption('$caption');");
        }
        if (is_null($_appoggio))
            $_appoggio = array();
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
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

    function CaricaGrigliaFrequenti($_griglia, $_appoggio, $_tipo = '1', $pageRows = 20, $caption = '') {
        if ($caption) {
            out::codice("$('#$_griglia').setCaption('$caption');");
        }
        if (is_null($_appoggio))
            $_appoggio = array();
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
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

    function CaricaGrigliaPreferiti($_griglia, $_appoggio, $_tipo = '1', $pageRows = 20, $caption = '') {
        if ($caption) {
            out::codice("$('#$_griglia').setCaption('$caption');");
        }
        if (is_null($_appoggio))
            $_appoggio = array();
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
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

    function LanciaProgramma($menu, $prog) {


        $this->menLib->lanciaProgramma_ini($menu, $prog);



//        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $menu . "'";
//        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
//        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_menu_rec['me_id'] . " AND pm_voce = '" . $prog . "'";
//        $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
//
//        $Tab_id = "tab-" . $prog;
//        $Tab_tit = $Ita_puntimenu_rec["pm_descrizione"];
//        $_POST = array();
//        $_POST['event'] = "onClick";
//        $_POST['menu'] = $menu;
//        $_POST['prog'] = $prog;
//        $_POST['noSave'] = true;
//        $model = 'menButton';
//        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//        $model();
    }

    function LanciaProgrammaPreferiti($rowid) {
        $Men_rec = menLib::getPreferiti($rowid);
        $this->LanciaProgramma($Men_rec['PR_MENU'], $Men_rec['PR_PROG']);
    }

    function CancellaProgrammaPreferiti($rowid) {
        $Men_rec = menLib::getPreferiti($rowid);
        $this->menLib->unSetBookmark(array("MENU" => $Men_rec['PR_MENU'], "PROG" => $Men_rec['PR_PROG']));
    }

    function refreshPreferiti() {
        $Men_Preferiti_tab = $this->PreparaDatiPreferiti_ini();
        $this->CaricaGrigliaPreferiti($this->gridPreferiti, $Men_Preferiti_tab, '1', 1000);
    }

    function LanciaProgrammaRecenti($rowid) {
        $Men_rec = menLib::getRecent($rowid);
        $this->LanciaProgramma($Men_rec['MENU'], $Men_rec['PROG']);
    }

    function LanciaProgrammaFrequenti($rowid) {
        $Men_rec = menLib::getFrequenti($rowid);
        $this->LanciaProgramma($Men_rec['FR_MENU'], $Men_rec['FR_PROG']);
    }

}

?>
