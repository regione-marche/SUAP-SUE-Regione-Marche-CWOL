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
    public $portletMenu = "PT_MEN";    
    public $mobileMenu = "MOB_MEN";    
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
                Out::hide($this->nameForm . '_applicativo');
                Out::hide($this->nameForm . '_applicativo_lbl');
                Out::hide($this->nameForm . '_griglia');
                $this->showGroupSelect();
                $this->showMenuSelect();                
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gruppo':
                        if ($_POST[$this->nameForm . '_gruppo'] == '') {
                            Out::hide($this->nameForm . '_applicativo');
                            Out::hide($this->nameForm . '_applicativo_lbl');
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }
                        Out::valore($this->nameForm . '_applicativo', "");
                        Out::hide($this->nameForm . '_griglia');
                        Out::show($this->nameForm . '_applicativo');
                        Out::show($this->nameForm . '_applicativo_lbl');
                        break;
                    case $this->nameForm . '_applicativo':
                        if ($_POST[$this->nameForm . '_applicativo'] == '') {
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }
                        Out::show($this->nameForm . '_griglia');
                        $this->showGrid($_POST[$this->nameForm . '_applicativo']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        break;
                }
                break;
            case 'afterSaveCell':
                $this->saveCheckDb();
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close=true) {
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
        if ($this->defaultVis == 1) {
            $node = $this->iconaInlineCons.'Visualizza: consenti';
        } else {
            $node = $this->iconaInlineNega.'Visualizza: nega';
        }
        Out::html($this->nameForm . "_defVisual", $node);
        
        if ($this->defaultAcc == 1) {
            $node = $this->iconaInlineCons.'Accesso: consenti';
        } else {
            $node = $this->iconaInlineNega.'Accesso: nega';
        }
        Out::html($this->nameForm . "_defAccesso", $node);
        
        if ($this->defaultMod == 1) {
            $node = $this->iconaInlineCons.'Modifica: consenti';
        } else {
            $node = $this->iconaInlineNega.'Modifica: nega';
        }
        Out::html($this->nameForm . "_defModifica", $node);
        
        if ($this->defaultIns == 1) {
            $node = $this->iconaInlineCons.'Inserimento: consenti';
        } else {
            $node = $this->iconaInlineNega.'Inserimento: nega';
        }
        Out::html($this->nameForm . "_defInserimento", $node);
        
        if ($this->defaultDel == 1) {
            $node = $this->iconaInlineCons.'Cancella: consenti';
        } else {
            $node = $this->iconaInlineNega.'Cancella: nega';
        }
        Out::html($this->nameForm . "_defCancella", $node);
    }

    /**
     *  Riempie la select delle voci dei menu
     */
    function showMenuSelect() {

        // Acquisisco me_id
        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $this->rootMenu . "'";
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);

        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = '" . $Ita_menu_rec['me_id'] . "'";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);

        Out::select($this->nameForm . '_applicativo', '1', "", 1, "Seleziona......");
        Out::select($this->nameForm . '_applicativo', '1', $this->rootMenu, 0, "Menu Principale");

        foreach ($Ita_puntimenu_tab as $key => $Ita_puntimenu_rec) {
            Out::select($this->nameForm . '_applicativo', '1', $Ita_puntimenu_rec['pm_voce'], 0, $Ita_puntimenu_rec['pm_descrizione']);
        }
        
        Out::select($this->nameForm . '_applicativo', '1', $this->portletMenu, 0, 'Menu PORTLET',"color:white;background-color:darkRed;");
        
        Out::select($this->nameForm . '_applicativo', '1', $this->mobileMenu, 0, "Menu Mobile","color:white;background-color:darkGreen;");        
    }

    /**
     *  Riempie la griglia con gli opportuni valori
     */
    function showGrid($voceMenu) {
        $gruppo = $_POST[$this->nameForm . '_gruppo'];
        $this->tree = $this->menLib->getMenu($voceMenu, $only_menu = false, $gruppo, $return_model = 'adjacency', $filtro = false);
        $arr = array('arrayTable' => $this->tree,
            'rowIndex' => 'idx');

        $griglia = new TableView($this->tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows('1000');
        TableView::enableEvents($this->tableId);
        TableView::clearGrid($this->tableId);
        Out::setGridCaption($this->tableId, $this->tree[0]['pm_descrizione']);
        $griglia->getDataPage('json');
        return;
    }

    /**
     *  Salva il click della checkbox nel database e modifica altre eventuali
     *   occorrenze all'interno della griglia albero
     */
    function saveCheckDb() {
        $colonna = $_POST['cellname'];
        $riga = $_POST['rowid'];
        $riga_id = false;
        $me_id = false;
        $pm_voce = false;
        $valore = $_POST['value'];
        $descrizione = '';

        $riga_id = $this->tree[$riga]['pm_id'];

        if ($riga_id == -1) {  // Se è root non fare nessuna modifica
            return;
        }

        $pm_voce = $this->tree[$riga]['pm_voce'];
        $me_id = $this->tree[$riga]['me_id'];
        $descrizione = $this->tree[$riga]['pm_descrizione'];

        // Salva l'impostazione nel database
        if ($riga !== false) {
            if ($_POST['id'] == $this->nameForm . '_gridPermessi') {
                $sql = "SELECT * FROM ita_menu WHERE me_id = '" . $me_id . "'";
                $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                $gruppo = $_POST[$this->nameForm . '_gruppo'];
                $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $Ita_menu_rec['me_menu'] . "' AND PER_VME = '" . $pm_voce . "'";
                $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                if ($Men_permessi_rec && $valore == '') {
                    TableView::setCellValue($this->tableId, $riga, 'PER_FLAGVIS', menLib::decodeFlag());
                    TableView::setCellValue($this->tableId, $riga, 'PER_FLAGACC', menLib::decodeFlag());
                    TableView::setCellValue($this->tableId, $riga, 'PER_FLAGEDT', menLib::decodeFlag());
                    TableView::setCellValue($this->tableId, $riga, 'PER_FLAGINS', menLib::decodeFlag());
                    TableView::setCellValue($this->tableId, $riga, 'PER_FLAGDEL', menLib::decodeFlag());
                    $this->menLib->cancellaPermessi($gruppo, $Ita_menu_rec['me_menu'], $pm_voce);
                    $icona = "";

                    $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_rec($riga_id, 'pm_id');
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGVIS' , $this->defaultVis);
                    TableView::setCellValue($this->tableId, $riga, 'iconaVis', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGACC' , $this->defaultAcc);
                    TableView::setCellValue($this->tableId, $riga, 'iconaAcc', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaultMod);
                    TableView::setCellValue($this->tableId, $riga, 'iconaEdt', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaultIns);
                    TableView::setCellValue($this->tableId, $riga, 'iconaIns', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaultDel);
                    TableView::setCellValue($this->tableId, $riga, 'iconaDel', $this->getIcona($privilegio));
                    return;
                }

                try {
                    if (!$Men_permessi_rec) {
                        $Men_permessi_rec['PER_GRU'] = $gruppo;
                        $Men_permessi_rec['PER_MEN'] = $Ita_menu_rec['me_menu'];
                        $Men_permessi_rec['PER_VME'] = $pm_voce;
                        $Men_permessi_rec['PER_FLAGVIS'] = $valore;
                        $Men_permessi_rec['PER_FLAGACC'] = $valore;
                        $Men_permessi_rec['PER_FLAGEDT'] = $valore;
                        $Men_permessi_rec['PER_FLAGINS'] = $valore;
                        $Men_permessi_rec['PER_FLAGDEL'] = $valore;
                        //$Men_permessi_rec[$colonna] = $valore;
                        ItaDB::DBInsert($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec);

                        TableView::setCellValue($this->tableId, $riga, 'PER_FLAGVIS', menLib::decodeFlag($valore));
                        TableView::setCellValue($this->tableId, $riga, 'PER_FLAGACC', menLib::decodeFlag($valore));
                        TableView::setCellValue($this->tableId, $riga, 'PER_FLAGEDT', menLib::decodeFlag($valore));
                        TableView::setCellValue($this->tableId, $riga, 'PER_FLAGINS', menLib::decodeFlag($valore));
                        TableView::setCellValue($this->tableId, $riga, 'PER_FLAGDEL', menLib::decodeFlag($valore));
                    } else {
                        $Men_permessi_rec[$colonna] = $valore;
                        $nrows = ItaDB::DBUpdate($this->ITALWEB_DB, 'MEN_PERMESSI', "ROWID", $Men_permessi_rec);
                        if ($nrows == 0) {
                            Out::msgStop("Aggiornamento Flag", "Riga non Aggiornata");
                        }
                    }
                    $icona = "";
                    $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_rec($riga_id, 'pm_id');
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGVIS', $this->defaultVis);
                    TableView::setCellValue($this->tableId, $riga, 'iconaVis', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGACC', $this->defaulAcc);
                    TableView::setCellValue($this->tableId, $riga, 'iconaAcc', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaulMod);
                    TableView::setCellValue($this->tableId, $riga, 'iconaEdt', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaulIns);
                    TableView::setCellValue($this->tableId, $riga, 'iconaIns', $this->getIcona($privilegio));
                    
                    $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaulDel);
                    TableView::setCellValue($this->tableId, $riga, 'iconaDel', $this->getIcona($privilegio));

                    // Generazione del log
//                    $update_Info = 'Aggiornamento dei permessi dell\'utente: ';
//                    switch ($colonna) {
//                        case 'PER_FLAGVIS':
//                            if ($valore == "0")
//                                $update_Info .= 'Voce di menu "' . $descrizione . '" abilitata.';
//                            else
//                                $update_Info .= 'Voce di menu "' . $descrizione . '" disabilitata.';
//                            break;
//                        case 'PER_FLAGACC':
//                            if ($valore == "0")
//                                $update_Info .= 'Accesso all\'applicazione "' . $descrizione . '" disabilitato.';
//                            else
//                                $update_Info .= 'Accesso all\'applicazione "' . $descrizione . '" abilitato.';
//                            break;
//                        case 'PER_FLAGEDT':
//                            if ($valore == "0")
//                                $update_Info .= 'Applicazione "' . $descrizione . '" con funzione di Edit.';
//                            else
//                                $update_Info .= 'Applicazione "' . $descrizione . '" senza funzione di Edit.';
//                            break;
//                        case 'PER_FLAGINS':
//                            if ($valore == "0")
//                                $update_Info .= 'Applicazione "' . $descrizione . '" con funzione di Inserimento.';
//                            else
//                                $update_Info .= 'Applicazione "' . $descrizione . '" senza funzione di Inserimento.';
//                            break;
//                        case 'PER_FLAGDEL':
//                            if ($valore == "0")
//                                $update_Info .= 'Applicazione "' . $descrizione . '" con funzione di Cancellazione.';
//                            else
//                                $update_Info .= 'Applicazione "' . $descrizione . '" senza funzione di Cancellazione.';
//                            break;
//                        default:
//                            break;
//                    }
                    //$this->updateRecord($this->ITW_DB, 'APLGRU', $Aplgru_rec, $update_Info);  //.. e qui!!
                } catch (Exception $exc) {
                    Out::msgStop("Errore in Aggiornamento", $exc->getMessage());
                }
            }
        }

        // Aggiorna le voci identiche
        // Non funziona bene!! Problemi nel javascript da identificare bene.
//        foreach ($this->tree as $key => $row) {
//            if ($row['ROWID'] == $riga_id) {
////                if ($row['INDICE'] == $riga) {
////                    App::log($riga);
////                    continue;
////                }
//                $this->tree[$key][$colonna] = $valore;
//                TableView::setCellValue($this->tableId, $this->tree[$key]['INDICE'],$colonna, $valore,'');
//            }
//        }
        //TableView::setRowData($this->tableId, $rowdata['INDICE'],$rowdata); 
        //TableView::setRowData('$this->tableId', '$riga', '', "{'background-color':'#FF6F6F'}");
    }
    
    public function getIcona($permesso) {
        if ($permesso) {
            return $this->iconaCons;
        } else {
            return $this->iconaNega;
        }
    }

}

?>
