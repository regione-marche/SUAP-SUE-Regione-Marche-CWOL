<?php

/**
 *  Explorer per il menu
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Moscioni
 * @author     Michele Accattoli
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    10.11.2011
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Menu/menLib.class.php';

function menExplorer() {
    $menExplorer = new menExplorer();
    $menExplorer->parseEvent();
    return;
}

class menExplorer extends itaModel {

    public $menLib;
    public $nameForm = "menExplorer";
    public $rootMenu = "TI_MEN";
    public $ITALSOFT_DB;
    public $albero;
    public $punti_menu;
    public $percorso;
    public $divPercorso = "menExplorer_divPercorso";

    function __construct() {
        parent::__construct();
        $this->menLib = new menLib();
        try {
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->albero = App::$utente->getKey($this->nameForm . '_albero');
        $this->punti_menu = App::$utente->getKey($this->nameForm . '_punti_menu');
        $this->percorso = App::$utente->getKey($this->nameForm . '_percorso');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_albero', $this->albero);
            App::$utente->setKey($this->nameForm . '_punti_menu', $this->punti_menu);
            App::$utente->setKey($this->nameForm . '_percorso', $this->percorso);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenMenu();
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridMenu':
                        $this->dettaglioMenu($this->albero[$_POST['rowid']]['pm_voce']);
                        $this->creaPercorso($_POST['rowid'], 'menu');
                        break;
                    case $this->nameForm . '_gridMostraPunti':
                        $this->clickPuntoMenu($_POST['rowid'] - 1);
                        break;
                }
                break;
            case 'onClick':
                if ($_POST['menu']) {
                    $this->clickMenu();
                }
                switch ($_POST['id']) {
                    case $this->nameForm . '_buttonBack':
                        $this->clickBack();
                        break;
                    case $this->nameForm . '_buttonPreferiti':
                        $rowid = $_POST[$this->nameForm . "_gridMostraPunti"]['gridParam']['selrow'];
                        $rec = $this->punti_menu[$rowid - 1];
                        if ($rec && $rec['pm_categoria']!='ME') {
                            Out::msgQuestion("Attenzione.", "Vuoi aggiungere il programma al menu dei preferiti?", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPreferito', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPreferito', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaPreferito':
                        $this->clickPreferiti();
                        break;
                }
                break;
        }
    }

    /**
     * Inizializzazioni
     */
    private function OpenMenu() {
        $this->Nascondi(array('divGridMostraPunti'));
        $tableId = $this->nameForm . "_gridMenu";
        $this->albero = $this->menLib->getMenu($this->rootMenu, $only_menu = true, $filtro = true);

        $arr = array('arrayTable' => $this->albero,
                'rowIndex' => 'idx');
        $griglia = new TableView($tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows('1000');
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $griglia->getDataPage('json');
        $this->creaPercorso('_ROOT_', '');
        $this->dettaglioMenu($this->rootMenu);
        Out::hide($this->nameForm . '_gridMenu_pm_descrizione');
        Out::hide($this->nameForm . '_gridMostraPunti_pm_descrizione');
        Out::hide($this->nameForm . '_gridMostraPunti_icona');
    }

    /**
     * Gestisce il doppio click nella griglia di sinistra
     */
    private function dettaglioMenu($me_menu) {
        //$sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $me_menu . "'";
        $Ita_menu_rec = $this->menLib->GetIta_menu($me_menu);
        if (!$Ita_menu_rec) {
            Out::msgInfo('Attenzione!', 'Menu inesistente');
            return 0;
        }
        $descrizione = $Ita_menu_rec['me_descrizione'];

        $Ita_puntimenu_tab = $this->menLib->menuFiltrato($Ita_menu_rec['me_id']);

        $Ita_puntimenu_def = array();
        $i = 0;
        /////////
        foreach ($Ita_puntimenu_tab as $key => $Ita_puntimenu_rec) {
            if ($Ita_puntimenu_rec['pm_categoria'] == 'ME') {
//                $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Ita_puntimenu_rec['pm_voce'] . "'";
                $Ita_menu_giu_rec = $this->menLib->GetIta_menu($Ita_puntimenu_rec['pm_voce']);
                if ($Ita_menu_giu_rec) {
                    $Ita_puntimenu_giu_tab = $this->menLib->GetIta_puntimenu($Ita_menu_giu_rec['me_id']);
                    if ($Ita_puntimenu_giu_tab) {
                        $Ita_puntimenu_def[$i] = $Ita_puntimenu_rec;
                        $i++;
                    }
                }
            } else {
                $Ita_puntimenu_def[$i] = $Ita_puntimenu_rec;
                $i++;
            }
        }
        foreach ($Ita_puntimenu_def as $key => $Ita_puntimenu_rec) {
            $Ita_puntimenu_def[$key]['icona'] = '';
            if ($Ita_puntimenu_rec['pm_categoria'] == 'ME') {
                $Ita_puntimenu_def[$key]['icona'] = '<span class="ita-icon ita-icon-view-tree-16x16"></span>';
            }
        }

        $this->punti_menu = $Ita_puntimenu_def;
        $tableId = $this->nameForm . "_gridMostraPunti";
        $arr = array('arrayTable' => $this->punti_menu,
                'rowIndex' => 'idx');
        $griglia = new TableView($tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows(10000);
        
        TableView::clearGrid($tableId);
        Out::setGridCaption($tableId, $descrizione);
        $griglia->getDataPage('json');
        TableView::enableEvents($tableId);
        $this->Mostra(array('divGridMostraPunti'));
        return true;
    }

    /**
     * Apre il programma o mostra il menu a seconda di quello che si clicca
     */
    private function clickPuntoMenu($rowid) {
        $rec = $this->punti_menu[$rowid];
        switch ($rec['pm_categoria']) {
            case 'ME':
                $arr = $this->percorso;
                $this->creaPercorso($rowid + 1, 'puntimenu');
                // Se non va a buon fine, ripristina il percorso
                if (!$this->dettaglioMenu($rec['pm_voce'])) {
                    $this->percorso = $arr;
                    $this->disegnaPercorso($arr);
                }
                break;
            case 'PR':
                $Ita_menu_rec = $this->menLib->GetIta_menu($rec['me_id'], 'me_id');
                $menu = $Ita_menu_rec['me_menu'];
                $prog = $rec['pm_voce'];
                $this->menLib->lanciaProgramma($menu, $prog);
                break;
        }
    }

    /**
     *  Crea il percorso cliccabile sopra la griglia dei punti menu
     * @param type $pm_voce Il menu cliccato
     */
    private function creaPercorso($rowid, $griglia) {
        // All'apertura della finestra e al doppio click sulla radice..
        if ($rowid == '_ROOT_' || ($griglia == 'menu' && $this->albero[$rowid]['level'] == 0)) {
            $htmlPath = '<a href="#menExplorer?menu=' . $this->albero[0]['pm_voce'] . '" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});">' . $this->albero[0]['pm_descrizione'] . '</a>';
            $this->percorso = '';
            $this->percorso[0]['voce'] = $this->rootMenu;
            $this->percorso[0]['descrizione'] = $this->albero[0]['pm_descrizione'];
            Out::html($this->divPercorso, $htmlPath);
            return;
        }

        $arr = array();
        $i = 0;
        $tmp = $rowid;
        // Click nella griglia di sinistra
        if ($griglia == 'menu') {
            while (true) {
                $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $this->albero[$tmp]['pm_voce'] . "'";
                $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                $arr[$i]['descrizione'] = $Ita_menu_rec['me_descrizione'];
                $arr[$i]['voce'] = $Ita_menu_rec['me_menu'];
                // Risale di un livello
                $tmp = $this->albero[$tmp]['parent'];
                $i++;
                // Inserisce la radice e chiude il ciclo
                if ($this->albero[$tmp]['level'] == '0') {
                    $arr[$i]['descrizione'] = $this->albero[0]['pm_descrizione'];
                    $arr[$i]['voce'] = $this->albero[0]['pm_voce'];
                    break;
                }
            }
            $arr = array_reverse($arr);
        }

        // Click nella griglia di destra
        if ($griglia == 'puntimenu') {
            $arr = $this->percorso;
            // Se è un menu, aggiungi un elemento nell'array
            if ($this->punti_menu[$rowid - 1]['pm_categoria'] == 'ME') {
                $cnt = count($arr);
                $arr[$cnt]['descrizione'] = $this->punti_menu[$rowid - 1]['pm_descrizione'];
                $arr[$cnt]['voce'] = $this->punti_menu[$rowid - 1]['pm_voce'];
            }
        }

        // inserisco in html
        $this->percorso = $arr;
        $this->disegnaPercorso($arr);
    }

    /**
     *  Disegna un percorso (dato un array di coppie 'voce' e 'descrizione')
     * @param Array $arr i dati per il percorso
     */
    private function disegnaPercorso($arr) {
        $html = "";
        $cnt = 1;
        foreach ($arr as $key => $val) {
            $html .= '<a href="#menExplorer?menu=' . $val['voce'] . '" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});">' .
                    $val['descrizione'] . '</a>';
            $cnt++;
            if ($cnt > count($arr)) {
                break;
            }
            $html .= ' => ';
        }
        Out::html($this->divPercorso, $html);
    }

    /**
     *  Gestisce il click di una voce di menu
     */
    private function clickMenu() {
        $this->dettaglioMenu($_POST['menu']);
        $i = 1;
        foreach ($this->percorso as $val) {
            if ($val['voce'] == $_POST['menu']) {
                break;
            }
            $i++;
        }
        $this->percorso = array_slice($this->percorso, 0, $i);
        $this->disegnaPercorso($this->percorso);
    }

    /**
     *  Gestisce il click del pulsante back
     */
    private function clickBack() {
        $cnt = count($this->percorso);
        if ($cnt == 1 || $cnt == 0) {
            return;
        }
        $this->percorso = array_slice($this->percorso, 0, $cnt - 1);
        $this->disegnaPercorso($this->percorso);
        $this->dettaglioMenu($this->percorso[$cnt - 2]['voce']);
    }

    /**
     *  Gestisce il click del pulsante preferiti
     */
    private function clickPreferiti() {
        $rowid = $_POST[$this->nameForm . "_gridMostraPunti"]['gridParam']['selrow'];
        $rec = $this->punti_menu[$rowid - 1];
        $sql = "SELECT * FROM ita_menu WHERE me_id = '" . $rec['me_id'] . "'";
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $param = array(
                'MENU' => $Ita_menu_rec['me_menu'],
                'PROG' => $rec['pm_voce']
        );
        $this->menLib->setBookmark($param);
    }

    /**
     * Espande tutto il menu
     */
//    private function espandiTutto() {
//        foreach ($this->albero as $i => $membro) {
//            $membro['expanded'] = 'true';
//            $this->albero[$i] = $membro;
//        }
//      
//        $tableId = $this->nameForm . "_gridMenu";
//        $arr = array('arrayTable' => $this->albero,
//                     'rowIndex' => 'idx');
//        $griglia = new TableView($tableId, $arr);
//        
//        $griglia->setPageNum(1);
//        $griglia->setPageRows('1000');
//        TableView::enableEvents($tableId);
//        TableView::clearGrid($tableId);
//        $griglia->getDataPage('json');
//    }

    /**
     * Mostra gli elementi HTML indicati dagli ID degli array
     * @param Array $arr Array di ID di elmeneti HTML da mostrare
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Mostra($arr) {
        foreach ($arr as $value) {
            Out::show($this->nameForm . '_' . $value);
        }
    }

    /**
     * Nascondi gli elementi HTML indicati dagli ID degli array
     * @param Array $arr Array di ID di elmeneti HTML da nascondere
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Nascondi($arr) {
        foreach ($arr as $value) {
            Out::hide($this->nameForm . '_' . $value);
        }
    }

}

?>
