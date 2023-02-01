<?php

/**
 * Permette di impostare quali voci del menu sono visibili da ogni gruppo di utenti
 *    (gestione dei permessi)
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Accattoli
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Menu/menLib.class.php';

function menAuthConfig() {
    $menAuthConfig = new menAuthConfig();
    $menAuthConfig->parseEvent();
    return;
}

class menAuthConfig extends itaModel {

    public $nameForm = "menAuthConfig";
    public $ITW_DB;
    public $ITALSOFT_DB;
    public $ITALWEB_DB;
    public $tree;
    public $tableId = 'menAuthConfig_gridPermessi';
    public $rootMenu = "TI_MEN";
    public $rootDefaults = array('TI_MEN', 'PT_MEN', 'MOB_MEN');
    //public $rootMenu = "PAL_MEN";
//    public $portletMenu = "PT_MEN";
//    public $mobileMenu = "MOB_MEN";
    public $menLib;
    public $defaultVis;
    public $defaultAcc;
    public $defaultMod;
    public $defaultIns;
    public $defaultDel;
    public $iconaCons = '<span class="ita-icon ita-icon-check-green-16x16"></span>';
    public $iconaNega = '<span class="ita-icon ita-icon-check-red-16x16"></span>';
    public $iconaInlineCons = '<span style="margin-right:4px;display:inline-block;float:left;" class="ita-icon ita-icon-check-green-16x16"></span>';
    public $iconaInlineNega = '<span style="margin-right:4px;display:inline-block;float:left;" class="ita-icon ita-icon-check-red-16x16"></span>';

    function __construct() {
        parent::__construct();
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->menLib = new menLib();
        $this->tree = App::$utente->getKey($this->nameForm . '_tree');

        $this->defaultVis = App::getConf("Menu.visibilityDefault");
        $this->defaultAcc = App::getConf("Menu.accessoDefault");
        $this->defaultMod = App::getConf("Menu.modificaDefault");
        $this->defaultIns = App::getConf("Menu.inserimentoDefault");
        $this->defaultDel = App::getConf("Menu.cancellaDefault");
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tree', $this->tree);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm . '_root');
                Out::hide($this->nameForm . '_root_lbl');
                Out::hide($this->nameForm . '_applicativo');
                Out::hide($this->nameForm . '_applicativo_lbl');
                Out::hide($this->nameForm . '_griglia');
                $this->showGroupSelect();
                $this->showRootSelect();
                break;

            case 'onClickTablePager':
                $this->showGrid();
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gruppo':
                        if ($_POST[$this->nameForm . '_gruppo'] == '') {
                            $this->hideSelect($this->nameForm . '_root');

                            Out::hide($this->nameForm . '_applicativo');
                            Out::hide($this->nameForm . '_applicativo_lbl');

                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }

                        Out::valore($this->nameForm . '_root', "");
                        Out::valore($this->nameForm . '_applicativo', "");

                        $this->showSelect($this->nameForm . '_root');
                        $this->hideSelect($this->nameForm . '_applicativo');
                        Out::hide($this->nameForm . '_griglia');
                        break;

                    case $this->nameForm . '_root':
                        Out::html($this->nameForm . '_applicativo', '');

                        if ($_POST[$this->nameForm . '_root'] == '') {
                            Out::hide($this->nameForm . '_applicativo');
                            Out::hide($this->nameForm . '_applicativo_lbl');
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }

                        $this->rootMenu = $_POST[$this->nameForm . '_root'];
                        $this->showMenuSelect();

                        Out::valore($this->nameForm . '_applicativo', "");

                        $this->showSelect($this->nameForm . '_applicativo');
                        Out::hide($this->nameForm . '_griglia');
                        break;

                    case $this->nameForm . '_applicativo':
                        if ($_POST[$this->nameForm . '_applicativo'] == '') {
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }

                        Out::show($this->nameForm . '_griglia');
                        $this->showGrid();
                        break;

                    case $this->nameForm . '_PERMS[VIS]':
                        $value = $_POST[$this->nameForm . '_PERMS']['VIS'];
                        
                        $arrayPermessi = array(
                            'ACC' => $value,
                            'INS' => $value,
                            'EDT' => $value,
                            'DEL' => $value
                        );

                        Out::valori($arrayPermessi, $this->nameForm . '_PERMS');
                        break;
                }
                break;

            case 'expandNode':
                switch ($_POST['id']) {
                    case $this->tableId:
                        if ($_POST['treeNodeHasChilds'] == 'false') {
                            $gruppo = $_POST[$this->nameForm . '_gruppo'];
                            $voce_menu = $this->tree[$_POST['rowid']];
                            $subtree = $this->menLib->getMenu_ini($voce_menu['pm_voce'], false, $gruppo, 'adjacency', false, 1, intval($voce_menu['level']), count($this->tree) - 1, $voce_menu['INDICE']);
                            $subtree = $this->elaboraRecords($subtree);
                            $this->tree = array_merge($this->tree, $subtree);
                            TableView::treeTableAddChildren($this->tableId, $subtree, 'idx');
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaPermChilds':
                        $gruppo = $_POST[$this->nameForm . '_gruppo'];
                        $voce_menu = $this->tree[$_POST['rowid']];
                        $voce_childs = $this->menLib->getMenu_ini($voce_menu['pm_voce'], false, $gruppo, 'adjacency', false);

                        /*
                         * Rimuovo il nodo oggetto della selezione.
                         */
                        unset($voce_childs[0]);

                        foreach ($voce_childs as $voce_submenu) {
                            if (!$this->updatePermsVoce($gruppo, $voce_submenu, $_POST[$this->nameForm . '_PERMS'])) {
                                break;
                            }
                        }

                        Out::closeCurrentDialog();
                        Out::msgInfo('Aggiornamento permessi', 'Aggiornate ' . count($voce_childs) . ' voci di menu');
                        break;

                    case 'close-portlet':
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['colName']) {
                    case 'PERM_CHILDS':
                        if ($_POST['rowid'] == 0 || $this->tree[$_POST['rowid']]['isLeaf'] === 'true') {
                            break;
                        }

                        $fields = array(
                            array(
                                'id' => 'rowid',
                                'name' => 'rowid',
                                'type' => 'hidden',
                                'value' => $_POST['rowid']
                            ),
                            array(
                                'label' => array(
                                    'value' => "Visualizza",
                                    'style' => 'width:100px;display:block;float:left;text-align:right;margin-top:20px;'
                                ),
                                'id' => $this->nameForm . '_PERMS[VIS]',
                                'name' => $this->nameForm . '_PERMS[VIS]',
                                'type' => 'select',
                                'style' => 'margin-left:5px;width:120px;margin-top:20px;',
                                'class' => 'ita-edit-onchange',
                                'options' => array(array('', 'Seleziona..'), array('1', 'Consenti'), array('0', 'Nega'))
                            ),
                            array(
                                'label' => array(
                                    'value' => "Accesso",
                                    'style' => 'width:100px;display:block;float:left;text-align:right;'
                                ),
                                'id' => $this->nameForm . '_PERMS[ACC]',
                                'name' => $this->nameForm . '_PERMS[ACC]',
                                'type' => 'select',
                                'style' => 'margin-left:5px;width:120px;',
                                'options' => array(array('', 'Seleziona..'), array('1', 'Consenti'), array('0', 'Nega'))
                            ),
                            array(
                                'label' => array(
                                    'value' => "Inserimento",
                                    'style' => 'width:100px;display:block;float:left;text-align:right;'
                                ),
                                'id' => $this->nameForm . '_PERMS[INS]',
                                'name' => $this->nameForm . '_PERMS[INS]',
                                'type' => 'select',
                                'style' => 'margin-left:5px;width:120px;',
                                'options' => array(array('', 'Seleziona..'), array('1', 'Consenti'), array('0', 'Nega'))
                            ),
                            array(
                                'label' => array(
                                    'value' => "Modifica",
                                    'style' => 'width:100px;display:block;float:left;text-align:right;'
                                ),
                                'id' => $this->nameForm . '_PERMS[EDT]',
                                'name' => $this->nameForm . '_PERMS[EDT]',
                                'type' => 'select',
                                'style' => 'margin-left:5px;width:120px;',
                                'options' => array(array('', 'Seleziona..'), array('1', 'Consenti'), array('0', 'Nega'))
                            ),
                            array(
                                'label' => array(
                                    'value' => "Cancella",
                                    'style' => 'width:100px;display:block;float:left;text-align:right;'
                                ),
                                'id' => $this->nameForm . '_PERMS[DEL]',
                                'name' => $this->nameForm . '_PERMS[DEL]',
                                'type' => 'select',
                                'style' => 'margin-left:5px;width:120px;',
                                'options' => array(array('', 'Seleziona..'), array('1', 'Consenti'), array('0', 'Nega'))
                            )
                        );

                        Out::msgInput('Modifica permessi sottonodi', $fields, array(
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaPermChilds',
                                'model' => $this->nameForm,
                                'class' => 'ita-button-validate',
                                'shortCut' => "f5"
                            )), $this->nameForm . "_workSpace", 'auto', 'auto', true, 'Nodo: <b>' . $this->tree[$_POST['rowid']]['pm_descrizione'] . '</b>'
                        );
                        break;
                }
                break;

            case 'exportTableToExcel':
                $root = $_POST[$this->nameForm . '_applicativo'];
                $gruppo = $_POST[$this->nameForm . '_gruppo'];
                $this->GetCompleteMenu_ini($root, $gruppo);

//                        $sql = $this->CreaSqlExportXls();
//                        $ita_grid01 = new TableView($this->gridAnaauto, array(
//                            'sqlDB' => $this->CDS_DB,
//                            'sqlQuery' => $sql));
//                        $ita_grid01->setSortIndex('TIPO');
//                        $ita_grid01->exportXLS('', 'Anaauto.xls');
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->tableId:
                        $this->saveCheckDb();
                        break;
                }
                break;

            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {

        App::$utente->removeKey($this->nameForm . '_tree');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function hideSelect($id) {
        Out::hide($id);
        Out::hide($id . '_lbl');
    }

    public function showSelect($id) {
        Out::show($id);
        Out::show($id . '_lbl');
    }

    /**
     *  Riempie la select dei gruppi utenti
     */
    function showGroupSelect() {
        $sql = "SELECT * FROM GRUPPI";
        $Gruppi_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql, true);

        Out::select($this->nameForm . '_gruppo', '1', "", 1, "Seleziona......");

        foreach ($Gruppi_tab as $keyGruppi => $Gruppi_rec) {
            Out::select($this->nameForm . '_gruppo', '1', $Gruppi_rec['GRUCOD'], 0, $Gruppi_rec['GRUDES']);
        }

        // Mostra le scritte di default
        $node = $this->defaultVis == 1 ? $this->iconaInlineCons . 'Visualizza: consenti' : $this->iconaInlineNega . 'Visualizza: nega';
        Out::html($this->nameForm . "_defVisual", $node);

        $node = $this->defaultAcc == 1 ? $this->iconaInlineCons . 'Accesso: consenti' : $this->iconaInlineNega . 'Accesso: nega';
        Out::html($this->nameForm . "_defAccesso", $node);

        $node = $this->defaultMod == 1 ? $this->iconaInlineCons . 'Modifica: consenti' : $this->iconaInlineNega . 'Modifica: nega';
        Out::html($this->nameForm . "_defModifica", $node);

        $node = $this->defaultIns == 1 ? $this->iconaInlineCons . 'Inserimento: consenti' : $this->iconaInlineNega . 'Inserimento: nega';
        Out::html($this->nameForm . "_defInserimento", $node);

        $node = $this->defaultDel == 1 ? $this->iconaInlineCons . 'Cancella: consenti' : $this->iconaInlineNega . 'Cancella: nega';
        Out::html($this->nameForm . "_defCancella", $node);
    }

    function showRootSelect() {
        Out::select($this->nameForm . '_root', '1', "", 1, "Seleziona......");

        $fromIni = false;

        $iniFiles = glob(ITA_BASE_PATH . '/apps/Menu/resources/*.ini');

        foreach ($iniFiles as $iniFile) {
            $me_menu = basename($iniFile, '.ini');

            $iniData = parse_ini_file($iniFile, true);

            if ($iniData && isset($iniData['Config']['me_root_menu']) && $iniData['Config']['me_root_menu']) {
                Out::select($this->nameForm . '_root', '1', $me_menu, 0, $iniData['Config']['me_descrizione'] . " ($me_menu)");
                $fromIni = true;
            }
        }

        if (!$fromIni) {
            foreach ($this->rootDefaults as $rootMenu) {
                Out::select($this->nameForm . '_root', '1', $rootMenu, 0, $rootMenu);
            }
        }
    }

    /**
     *  Riempie la select delle voci dei menu
     */
    function showMenuSelect() {
        $Ita_puntimenu_tab = $this->menLib->GetIta_puntimenu_ini($this->rootMenu);

        Out::select($this->nameForm . '_applicativo', '1', "", 1, "Seleziona......");
        Out::select($this->nameForm . '_applicativo', '1', $this->rootMenu, 0, "Menu Principale");

        foreach ($Ita_puntimenu_tab as $Ita_puntimenu_rec) {
            if ($Ita_puntimenu_rec['pm_categoria'] != 'ME') {
                continue;
            }

            Out::select($this->nameForm . '_applicativo', '1', $Ita_puntimenu_rec['pm_voce'], 0, $Ita_puntimenu_rec['pm_descrizione']);
        }

//        Out::select($this->nameForm . '_applicativo', '1', $this->portletMenu, 0, 'Menu PORTLET', "color:white;background-color:darkRed;");
//
//        Out::select($this->nameForm . '_applicativo', '1', $this->mobileMenu, 0, "Menu Mobile", "color:white;background-color:darkGreen;");
    }

    /**
     *  Riempie la griglia con gli opportuni valori
     */
    function showGrid() {
        $root = $_POST[$this->nameForm . '_applicativo'];
        $gruppo = $_POST[$this->nameForm . '_gruppo'];

        $this->tree = $this->menLib->getMenu_ini($root, false, $gruppo, 'adjacency', false, 1);
        $this->tree = $this->elaboraRecords($this->tree, true);
        TableView::enableEvents($this->tableId);
        TableView::clearGrid($this->tableId);
        Out::setGridCaption($this->tableId, $this->tree[0]['pm_descrizione']);

        $griglia = new TableView($this->tableId, array('arrayTable' => $this->tree, 'rowIndex' => 'idx'));
        $griglia->setPageNum(1);
        $griglia->setPageRows('100000');
        $griglia->getDataPage('json');
        return true;
    }

    /**
     * Elabora i record contenenti i punti di menu.
     */
    private function elaboraRecords($menuTree, $hasRoot = false) {
        foreach ($menuTree as $i => $menu_rec) {
            if ($hasRoot && !$i) {
                /*
                 * Salto il nodo principale
                 */
                continue;
            }

            if ($menu_rec['isLeaf'] === 'false') {
                $menuTree[$i]['PERM_CHILDS'] = '<div style="text-align: center;"><i class="ui-icon ui-icon-gears"></i></div>';
            }
        }

        return $menuTree;
    }

    /**
     *  Salva il click della checkbox nel database e modifica altre eventuali
     *   occorrenze all'interno della griglia albero
     */
    function saveCheckDb() {
        $root = $_POST[$this->nameForm . '_applicativo'];
        $gruppo = $_POST[$this->nameForm . '_gruppo'];
        $colonna = $_POST['cellname'];
        $valore = $_POST['value'];
        $riga = $_POST['rowid'];

        $puntimenu_rec = $this->tree[$riga];
        $me_menu = $puntimenu_rec['me_menu'];
        $pm_voce = $puntimenu_rec['pm_voce'];

        if (!$me_menu) {
            return false;
        }

        $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '$gruppo' AND PER_MEN = '$me_menu' AND PER_VME = '$pm_voce'", false);
        if (($Men_permessi_rec && $valore == '') || !$Men_permessi_rec) {
            if (!$Men_permessi_rec) {
                $Men_permessi_rec['PER_GRU'] = $gruppo;
                $Men_permessi_rec['PER_MEN'] = $me_menu;
                $Men_permessi_rec['PER_VME'] = $pm_voce;
                $Men_permessi_rec['PER_FLAGVIS'] = $valore;
                $Men_permessi_rec['PER_FLAGACC'] = $valore;
                $Men_permessi_rec['PER_FLAGEDT'] = $valore;
                $Men_permessi_rec['PER_FLAGINS'] = $valore;
                $Men_permessi_rec['PER_FLAGDEL'] = $valore;

                if (!ItaDB::DBInsert($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec)) {
                    Out::msgStop("Aggiornamento Flag", "Riga non inserita");
                }
            } else {
                $this->menLib->cancellaPermessi($gruppo, $me_menu, $pm_voce);
            }

            $decodedFlag = menLib::decodeFlag($valore);

            TableView::setCellValue($this->tableId, $riga, 'PER_FLAGVIS', $decodedFlag);
            TableView::setCellValue($this->tableId, $riga, 'PER_FLAGACC', $decodedFlag);
            TableView::setCellValue($this->tableId, $riga, 'PER_FLAGEDT', $decodedFlag);
            TableView::setCellValue($this->tableId, $riga, 'PER_FLAGINS', $decodedFlag);
            TableView::setCellValue($this->tableId, $riga, 'PER_FLAGDEL', $decodedFlag);

            $this->tree[$riga]['PER_FLAGVIS'] = $decodedFlag;
            $this->tree[$riga]['PER_FLAGACC'] = $decodedFlag;
            $this->tree[$riga]['PER_FLAGEDT'] = $decodedFlag;
            $this->tree[$riga]['PER_FLAGINS'] = $decodedFlag;
            $this->tree[$riga]['PER_FLAGDEL'] = $decodedFlag;
        } else {
            $Men_permessi_rec[$colonna] = $valore;
            if ($colonna == 'PER_FLAGVIS') {
                $Men_permessi_rec['PER_FLAGACC'] = $valore;
                $Men_permessi_rec['PER_FLAGEDT'] = $valore;
                $Men_permessi_rec['PER_FLAGINS'] = $valore;
                $Men_permessi_rec['PER_FLAGDEL'] = $valore;

                $decodedFlag = menLib::decodeFlag($valore);
                $this->tree[$riga]['PER_FLAGACC'] = $decodedFlag;
                $this->tree[$riga]['PER_FLAGEDT'] = $decodedFlag;
                $this->tree[$riga]['PER_FLAGINS'] = $decodedFlag;
                $this->tree[$riga]['PER_FLAGDEL'] = $decodedFlag;

                TableView::setCellValue($this->tableId, $riga, 'PER_FLAGACC', $decodedFlag);
                TableView::setCellValue($this->tableId, $riga, 'PER_FLAGEDT', $decodedFlag);
                TableView::setCellValue($this->tableId, $riga, 'PER_FLAGINS', $decodedFlag);
                TableView::setCellValue($this->tableId, $riga, 'PER_FLAGDEL', $decodedFlag);
            }

            if (!ItaDB::DBUpdate($this->ITALWEB_DB, 'MEN_PERMESSI', "ROWID", $Men_permessi_rec)) {
                Out::msgStop("Aggiornamento Flag", "Riga non Aggiornata");
            }
        }

        $this->tree[$riga]['iconaVis'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $puntimenu_rec, array($gruppo), 'PER_FLAGVIS', $this->defaultVis));
        $this->tree[$riga]['iconaAcc'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $puntimenu_rec, array($gruppo), 'PER_FLAGACC', $this->defaultAcc));
        $this->tree[$riga]['iconaEdt'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $puntimenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaultMod));
        $this->tree[$riga]['iconaIns'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $puntimenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaultIns));
        $this->tree[$riga]['iconaDel'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $puntimenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaultDel));

        TableView::setCellValue($this->tableId, $riga, 'iconaVis', $this->tree[$riga]['iconaVis']);
        TableView::setCellValue($this->tableId, $riga, 'iconaAcc', $this->tree[$riga]['iconaAcc']);
        TableView::setCellValue($this->tableId, $riga, 'iconaEdt', $this->tree[$riga]['iconaEdt']);
        TableView::setCellValue($this->tableId, $riga, 'iconaIns', $this->tree[$riga]['iconaIns']);
        TableView::setCellValue($this->tableId, $riga, 'iconaDel', $this->tree[$riga]['iconaDel']);

        return true;
    }

    private function updatePermsVoce($gruppo, $vocemenu_rec, $perms) {
        $me_menu = $vocemenu_rec['me_menu'];
        $pm_voce = $vocemenu_rec['pm_voce'];

        if (!$me_menu) {
            return false;
        }

        $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '$gruppo' AND PER_MEN = '$me_menu' AND PER_VME = '$pm_voce'", false);

        if ($perms['VIS'] === '' || $perms['ACC'] === '' || $perms['EDT'] === '' || $perms['INS'] === '' || $perms['DEL'] === '') {
            $perms['VIS'] = '';
            $perms['ACC'] = '';
            $perms['EDT'] = '';
            $perms['INS'] = '';
            $perms['DEL'] = '';

            if ($Men_permessi_rec) {
                $this->menLib->cancellaPermessi($gruppo, $me_menu, $pm_voce);
            }
        } else {
            if (!$Men_permessi_rec) {
                $Men_permessi_rec['PER_GRU'] = $gruppo;
                $Men_permessi_rec['PER_MEN'] = $me_menu;
                $Men_permessi_rec['PER_VME'] = $pm_voce;

                $Men_permessi_rec['PER_FLAGVIS'] = $perms['VIS'];
                $Men_permessi_rec['PER_FLAGACC'] = $perms['ACC'];
                $Men_permessi_rec['PER_FLAGEDT'] = $perms['EDT'];
                $Men_permessi_rec['PER_FLAGINS'] = $perms['INS'];
                $Men_permessi_rec['PER_FLAGDEL'] = $perms['DEL'];

                if (!ItaDB::DBInsert($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec)) {
                    Out::msgStop('Aggiornamento permessi', 'Errore durante l\'aggiornamento del menu <b>' . $vocemenu_rec['pm_descrizione'] . '</b>');
                    return false;
                }
            } else {
                if (
                    $Men_permessi_rec['PER_FLAGVIS'] != $perms['VIS'] ||
                    $Men_permessi_rec['PER_FLAGACC'] != $perms['ACC'] ||
                    $Men_permessi_rec['PER_FLAGEDT'] != $perms['EDT'] ||
                    $Men_permessi_rec['PER_FLAGINS'] != $perms['INS'] ||
                    $Men_permessi_rec['PER_FLAGDEL'] != $perms['DEL']
                ) {
                    $Men_permessi_rec['PER_FLAGVIS'] = $perms['VIS'];
                    $Men_permessi_rec['PER_FLAGACC'] = $perms['ACC'];
                    $Men_permessi_rec['PER_FLAGEDT'] = $perms['EDT'];
                    $Men_permessi_rec['PER_FLAGINS'] = $perms['INS'];
                    $Men_permessi_rec['PER_FLAGDEL'] = $perms['DEL'];

                    if (!ItaDB::DBUpdate($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec)) {
                        Out::msgStop('Aggiornamento permessi', 'Errore durante l\'aggiornamento del menu <b>' . $vocemenu_rec['pm_descrizione'] . '</b>');
                        return false;
                    }
                }
            }
        }

        foreach ($this->tree as $rowid => $voce_rec) {
            if ($voce_rec['pm_voce'] === $pm_voce && $voce_rec['me_menu'] === $me_menu) {
                $this->tree[$rowid]['PER_FLAGVIS'] = menLib::decodeFlag($perms['VIS']);
                $this->tree[$rowid]['PER_FLAGACC'] = menLib::decodeFlag($perms['ACC']);
                $this->tree[$rowid]['PER_FLAGEDT'] = menLib::decodeFlag($perms['EDT']);
                $this->tree[$rowid]['PER_FLAGINS'] = menLib::decodeFlag($perms['INS']);
                $this->tree[$rowid]['PER_FLAGDEL'] = menLib::decodeFlag($perms['DEL']);
                TableView::setCellValue($this->tableId, $rowid, 'PER_FLAGVIS', $this->tree[$rowid]['PER_FLAGVIS']);
                TableView::setCellValue($this->tableId, $rowid, 'PER_FLAGACC', $this->tree[$rowid]['PER_FLAGACC']);
                TableView::setCellValue($this->tableId, $rowid, 'PER_FLAGEDT', $this->tree[$rowid]['PER_FLAGEDT']);
                TableView::setCellValue($this->tableId, $rowid, 'PER_FLAGINS', $this->tree[$rowid]['PER_FLAGINS']);
                TableView::setCellValue($this->tableId, $rowid, 'PER_FLAGDEL', $this->tree[$rowid]['PER_FLAGDEL']);

                $this->tree[$rowid]['iconaVis'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $vocemenu_rec, array($gruppo), 'PER_FLAGVIS', $this->defaultVis));
                $this->tree[$rowid]['iconaAcc'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $vocemenu_rec, array($gruppo), 'PER_FLAGACC', $this->defaultAcc));
                $this->tree[$rowid]['iconaEdt'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $vocemenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaultMod));
                $this->tree[$rowid]['iconaIns'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $vocemenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaultIns));
                $this->tree[$rowid]['iconaDel'] = $this->getIcona($this->menLib->privilegiPuntoMenu($me_menu, $vocemenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaultDel));
                TableView::setCellValue($this->tableId, $rowid, 'iconaVis', $this->tree[$rowid]['iconaVis']);
                TableView::setCellValue($this->tableId, $rowid, 'iconaAcc', $this->tree[$rowid]['iconaAcc']);
                TableView::setCellValue($this->tableId, $rowid, 'iconaEdt', $this->tree[$rowid]['iconaEdt']);
                TableView::setCellValue($this->tableId, $rowid, 'iconaIns', $this->tree[$rowid]['iconaIns']);
                TableView::setCellValue($this->tableId, $rowid, 'iconaDel', $this->tree[$rowid]['iconaDel']);
            }
        }

        return true;
    }

    public function getIcona($permesso) {
        if ($permesso) {
            return $this->iconaCons;
        } else {
            return $this->iconaNega;
        }
    }

    public function GetCompleteMenu_ini($root, $gruppo) {
        $completeTree = $this->menLib->getMenu_ini($root, false, $gruppo, 'adjacency', false, -1);
        $xls_data = array();

        $xls_header = array(
            'Codice Menu' => 'string',
            'Codice Procedura' => 'string',
            'Model Sorgente' => 'string',
            'Descrizione Procedura' => 'string',
            'Livello Annidamento' => 'integer'
        );


        $xls_tab = array();
        foreach ($completeTree as $key => $value) {
            if ($value['isLeaf'] == 'true') {
                $prg_key = $value['me_menu'] . $value['pm_voce'];
                $xls_rec = array();
                $xls_rec['me_menu'] = $value['me_menu'];
                $xls_rec['pm_voce'] = $value['pm_voce'];
                $xls_rec['pm_model'] = $value['pm_model'];
                $xls_rec['pm_descrizione'] = $value['pm_descrizione'];
                $xls_rec['level'] = $value['level'];

                if (!array_key_exists($prg_key, $xls_tab)) {
                    $xls_tab[$prg_key] = $xls_rec;
                }
            }
        }

        $xls_data['tab'] = $xls_tab;
        $xls_data['headers'] = $xls_header;

        $ita_grid_xls = new TableView('grid_xls', array(
            'arrayTable' => $xls_tab
            ), null, null, null, null);

        $ita_grid_xls->setXLSHeaders($xls_header);
        $ita_grid_xls->exportXLS('', 'PuntiMenu.xls');

        return $xls_data;
    }

}

?>