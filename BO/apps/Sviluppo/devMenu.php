<?php

/**
 *  Gestione Menu di lancio Models ItaEngine
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Moscioni
 * @author     Michele Accattoli
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    01.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 */
function devMenu() {
    $devMenu = new devMenu();
    $devMenu->parseEvent();
    return;
}

include_once './apps/Utility/utiEnte.class.php';
include_once './apps/Menu/menLib.class.php';

class devMenu extends itaModel {

    public $nameForm = "devMenu";
    public $divGes = "devMenu_divGestione";
    public $divRis = "devMenu_divRisultato";
    public $divRic = "devMenu_divRicerca";
    public $ITALSOFT_DB;
    public $elenco;
    public $utiEnte;
    public $me_id;

    function __construct() {
        parent::__construct();
        try {
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->utiEnte = new utiEnte();
        $this->utiEnte->getITALWEB_DB();
        $this->elenco = App::$utente->getKey($this->nameForm . '_elenco');
        $this->me_id = App::$utente->getKey($this->nameForm . '_me_id');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_elenco', $this->elenco);
            App::$utente->setKey($this->nameForm . '_me_id', $this->me_id);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->elencaMenu();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->nuovoMenu();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->aggiungiMenu();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->aggiornaMenu();
                        break;

                    case $this->nameForm . '_Compila':
                        /* @var $menLib menLib */
                        $menLib = new menLib();

                        $mid = $_POST[$this->nameForm . '_ita_menu']['me_id'];
                        $codice = $_POST[$this->nameForm . '_mCod'];
                        $descrizione = $_POST[$this->nameForm . '_mDes'];
                        $descrizione_pm = $_POST[$this->nameForm . '_mDesPm'];
                        $voce_pm = $_POST[$this->nameForm . '_mVocPm'];

                        if ($mid) {
                            $menu = $menLib->GetIta_menu($mid, 'me_id');
                            $compmsg = "Confermi la compilazione del menu {$menu['me_menu']}?";
                        } else if ($codice || $descrizione || $descrizione_pm || $voce_pm) {
                            $selmenu = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $this->creaSqlRicerca());
                            $compmsg = "Confermi la compilazione di " . count($selmenu) . " menu?";
                        } else {
                            $compmsg = "Confermi la compilazione massiva di tutti i menu?";
                        }

                        Out::msgQuestion("Compilazione", $compmsg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCompilazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCompilazione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCompilazione':
                        /* @var $menLib menLib */
                        $menLib = new menLib();

                        $mid = $_POST[$this->nameForm . '_ita_menu']['me_id'];

                        if ($mid) {
                            $menu = $menLib->GetIta_menu($mid, 'me_id');
                            $this->compilaMenu($menu['me_menu']);
                        } else {
                            $selmenu = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $this->creaSqlRicerca());
                            foreach ($selmenu as $menu) {
                                $this->compilaMenu($menu['me_menu']);
                            }
                        }

                        Out::msgInfo("Compilazione", "Compilazione avvenuta con successo");
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaMenu', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaMenu', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_Importa':
                        $this->importaMenu();
                        break;
                    case $this->nameForm . '_ConfermaCancellaMenu':
                        if ($this->confermaCancellaMenu()) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_AnnullaCancellaMenu':
                        break;

                    case $this->nameForm . '_ConfermaCancellaPuntoMenu':
                        $this->confermaCancellaPuntoMenu();
                        break;
                    case $this->nameForm . '_AnnullaCancellaPuntoMenu':
                        break;
                }
                break;
            case 'dbClickRow':case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridRisultati':
                        $this->dettaglio($_POST['rowid']);
                        break;
                    case $this->nameForm . '_gridMenu':
                        $this->dettaglioPuntoMenu();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridRisultati':
                        $this->nuovoMenu();
                        break;
                    case $this->nameForm . '_gridMenu':
                        $this->nuovoPuntoMenu();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridMenu':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaPuntoMenu', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaPuntoMenu', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->creaSqlRicerca(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALSOFT_DB, 'devMenu', $parameters);
                break;
            case 'onClickTablePager':
                $this->ordinaTabella($_POST['id']);
                break;
            case 'returnUploadINI':
                $this->returnIni();
                break;
            case 'returnFromPuntiMenu':
                $this->refreshGridMenu();
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
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    /**
     *  Apre il modulo di ricerca
     */
    public function OpenRicerca() {
        Out::clearFields($this->nameForm . '_divAppoggio');
        $this->Pannello('ricerca');
        $this->Nascondi(array('AltraRicerca', 'Aggiungi', 'Aggiorna', 'Cancella', 'Importa'));
        $this->Mostra(array('Nuovo', 'Elenca', 'Compila'));
        $this->Pulisci(array('mCod', 'mDes', 'mDesPm', 'mVocPm'));
    }

    /**
     *  Mostra i risultati della ricerca
     */
    function elencaMenu() {
        Out::clearFields($this->nameForm . '_divAppoggio');
        $sql = $this->creaSqlRicerca();
        $tableId = $this->nameForm . "_gridRisultati";
        $griglia = new TableView($tableId, array(
            'sqlDB' => $this->ITALSOFT_DB,
            'sqlQuery' => $sql));
        $griglia->setPageNum(1);
        $griglia->setPageRows($_POST[$tableId]['gridParam']['rowNum']);
        $griglia->setSortIndex('me_menu');
        $griglia->setSortOrder('asc');
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $griglia->getDataPage('json');
        $this->Pannello('risultati');
        $this->Nascondi(array('Elenca'));
        $this->Mostra(array('AltraRicerca', 'Compila'));
    }

    /**
     * Mostra il modulo per la creazione di un nuovo menu
     */
    function nuovoMenu() {
        Out::clearFields($this->nameForm . '_divAppoggio');
        $this->Pannello('gestione');
        $this->Nascondi(array('Nuovo', 'Elenca', 'Aggiorna', 'Cancella', 'divGridMenu', 'Compila'));
        $this->Mostra(array('Aggiungi', 'AltraRicerca', 'Importa'));
        $this->Pulisci(array('ita_menu[me_menu]', 'ita_menu[me_descrizione]'));
        // Svuota griglia
        $tableId = $this->nameForm . "_gridMenu";
        TableView::clearGrid($tableId);
        Out::setGridCaption($tableId, '-');
    }

    /**
     * Mostra le voci del menu selezionato
     */
    function dettaglio($me_id) {
        // Dati menu da caricare
        $this->me_id = $me_id;
        $sql = "SELECT * FROM ita_menu WHERE me_id = " . $me_id;
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $descrizione = $Ita_menu_rec['me_descrizione'];

        Out::valori($Ita_menu_rec, $this->nameForm . '_ita_menu');

        // Elenco dei punti menu
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $me_id . " ORDER BY pm_sequenza";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql);

        // Inserisco le icone 'tree'
        foreach ($Ita_puntimenu_tab as $key => $Ita_puntimenu_rec) {
            $Ita_puntimenu_tab[$key]['icona'] = '';
            if ($Ita_puntimenu_rec['pm_categoria'] == 'ME') {
                $Ita_puntimenu_tab[$key]['icona'] = '<span class="ita-icon ita-icon-view-tree-16x16"></span>';
            }
        }
        $tableId = $this->nameForm . "_gridMenu";
        $arr = array('arrayTable' => $Ita_puntimenu_tab,
            'rowIndex' => 'idx');

        $griglia = new TableView($tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows('1000');
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $griglia->getDataPage('json');
        $caption = $descrizione;
        Out::setGridCaption($tableId, $caption);
        //Out::setFocus('', $this->nameForm . '_gridMenu_pm_sequenza');

        $this->Pannello('gestione');
        $this->Mostra(array('Aggiorna', 'Cancella', 'AltraRicerca', 'divGridMenu', 'Compila'));
        $this->Nascondi(array('Nuovo', 'Elenca', 'Aggiungi'));
    }

    /**
     *  Crea una nuova voce di menu
     */
    function aggiungiMenu() {
        $codice = $_POST[$this->nameForm . '_ita_menu[me_menu]'];
        $descrizione = $_POST[$this->nameForm . '_ita_menu']['me_descrizione'];
        // Verifico se il record è gia esistente
        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $codice . "'";
        $Ita_menu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql);
        if (count($Ita_menu_tab) > 0) {
            Out::msgInfo('Attenzione', 'Codice menu gia esistente');
            return;
        }

        // Inserisco il record
        $Ita_menu_rec = $_POST[$this->nameForm . '_ita_menu'];
        if (!$this->insertRecord($this->ITALSOFT_DB, 'ita_menu', $Ita_menu_rec, '', 'me_id')) {
            Out::msgInfo('Attenzione', 'Errore nell\'inserimento del menu.');
            return;
        }

        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Ita_menu_rec['me_menu'] . "'";
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
//        $this->me_id = $Ita_menu_rec['me_id'];
//        $this->Mostra(array('Aggiorna', 'Cancella', 'AltraRicerca','divGridMenu'));
//        $this->Nascondi(array('Nuovo','Elenca', 'Aggiungi', 'Importa'));
//        //$me_id = $_POST['rowid'];
//        $tableId = $this->nameForm . "_gridMenu";
//        Out::setGridCaption($tableId, $descrizione);
//        // ASSEGNARE il valore a POST['rowid']
        $this->dettaglio($Ita_menu_rec['me_id']);
        //return true;
    }

    /**
     *  Aggiorna voce di menu
     */
    function aggiornaMenu() {
        //$id = $_POST[$this->nameForm . '_ita_menu']['me_id'];
        $id = $this->me_id;
        $me_menu_new = $_POST[$this->nameForm . '_ita_menu']['me_menu'];
        // E' stato modificato il codice?
        $sql = "SELECT * FROM ita_menu WHERE me_id = " . $id;
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $me_menu_old = $Ita_menu_rec['me_menu'];
        // ..se si.. verifica!
        if ($me_menu_old != $me_menu_new) {
            $sql = "SELECT * FROM ita_puntimenu WHERE pm_voce = '" . $me_menu_old . "'";
            $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
            if ($Ita_puntimenu_tab) {
                Out::msgInfo('Attenzione', 'Non puoi modificare questo codice menu.<br>Già usato come sottomenu.');
                Out::valore($this->nameForm . '_ita_menu[me_menu]', $me_menu_old);
                return false;
            }
        }

        $Ita_menu_rec = $_POST[$this->nameForm . '_ita_menu'];
        if (!$this->updateRecord($this->ITALSOFT_DB, 'ita_menu', $Ita_menu_rec, '', 'me_id')) {
            Out::msgInfo('Attenzione', 'Errore nell\'aggiornamento del menu.');
            return false;
        }
    }

    /**
     *  Apre una 'finestra' per eseguire l'upload di un file *.ini
     */
    function importaMenu() {
        $model = 'utiUploadDiag';
        $_POST = Array();
        $_POST['event'] = 'openform';
        $_POST[$model . '_returnModel'] = $this->nameForm;
        $_POST[$model . '_returnEvent'] = "returnUploadINI";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     *  Gestione del file .ini per l'importazione del menu
     */
    function returnIni() {
        $INIfile = $_POST['uploadedFile'];
        if (file_exists($INIfile)) {
            if (pathinfo($INIfile, PATHINFO_EXTENSION) == "ini") {
                $menu_ini = parse_ini_file($INIfile, true);

                // Informazioni per il menu
                $file = $_POST['file'];
                $file = explode('.', $file);
                $file = $file[0];
                $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $file . "'";
                $Ita_menu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
                if ($Ita_menu_tab) {
                    Out::msgInfo('Attenzione', 'Il menu è gia presente.');
                    return;
                }

                $descr_menu = $menu_ini['Config']['Title'];
                if ($descr_menu == '') {
                    Out::msgInfo('Attenzione', 'Il file non è ben formato.');
                    return;
                }
                unset($menu_ini['Config']);

                // Informazioni per i punti menu
                $puntiMenu = Array();
                $i = 0;
                foreach ($menu_ini as $key => $value) {
                    if ($value['eqType'] != 'prog' && $value['eqType'] != 'menu' && $value['eqType'] != 'eqprog') {
                        Out::msgInfo('Attenzione', $key . ': Il file non è ben formato. eqType sbagliato.');
                        return;
                    }
                    if ($value['eqDesc'] == '' || $value['eqProg'] == '') {
                        Out::msgInfo('Attenzione', $key . ': Il file non è ben formato. eqProg o eqDesc assenti.');
                        return;
                    }
                    switch ($value['eqType']) {
                        case 'prog':
                            $puntiMenu[$i]['pm_categoria'] = 'PR';
                            break;
                        case 'menu':
                            $puntiMenu[$i]['pm_categoria'] = 'ME';
                            break;
                        case 'eqprog';
                            $puntiMenu[$i]['pm_categoria'] = 'EQ';
                            break;
                    }
                    $puntiMenu[$i]['pm_sequenza'] = $value['eqNMen'];
                    $puntiMenu[$i]['pm_descrizione'] = $value['eqDesc'];
                    $puntiMenu[$i]['pm_voce'] = $key;
                    $puntiMenu[$i]['pm_datamod'] = date("Ymd");
                    $puntiMenu[$i]['pm_model'] = $value['model'];
                    $puntiMenu[$i]['pm_post'] = $value['post'];
                    $puntiMenu[$i]['pm_flagvis'] = -1;
                    $i++;
                }

                // Crea menu
                $Ita_menu_rec = array();
                $Ita_menu_rec['me_menu'] = $file;
                $Ita_menu_rec['me_descrizione'] = $descr_menu;
                if (!$this->insertRecord($this->ITALSOFT_DB, 'ita_menu', $Ita_menu_rec, '', 'me_id')) {
                    Out::msgInfo('Attenzione', 'Errore nell\'inserimento del menu.');
                    return;
                } else {
                    $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $file . "'";
                    $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                    $me_id = $Ita_menu_rec['me_id'];
                    // Crea punti menu
                    foreach ($puntiMenu as $rec) {
                        $rec['me_id'] = $me_id;
                        if (!$this->insertRecord($this->ITALSOFT_DB, 'ita_puntimenu', $rec, '', 'pm_id')) {
                            Out::msgInfo('Attenzione', 'Errore nell\'inserimento dei punti menu.');
                            return;
                        }
                    }
                    Out::msgInfo('Attenzione', 'Menu creato correttamente.');
                    $this->dettaglio($me_id);
                }
                return;
            } else {
                Out::msgInfo('Attenzione', 'Il file non ha estensione .ini');
            }
        } else {
            Out::msgInfo('Attenzione', 'Il file non esiste!');
        }
    }

    /**
     *  Conferma la cancellazione del menu
     * @return type
     */
    function confermaCancellaMenu() {
        $id = $_POST[$this->nameForm . '_ita_menu']['me_id'];

        //$id = $_POST[$this->nameForm . '_ita_puntimenu']['pm_id'];
        if ($this->haPuntiMenu($id)) {
            Out::msgInfo('Attenzione', 'Questo menu ha dei punti menu.');
            return false;
        }
        if (!$this->deleteRecord($this->ITALSOFT_DB, 'ita_menu', $id, '', 'me_id')) {
            Out::msgInfo('Attenzione', 'Errore nella cancellazione del menu.');
            return false;
        }
        Out::msgInfo('Messaggio', 'Record menu cancellato correttamente');
        return true;
    }

    /**
     *  visualizza ed eventualmente modifica un punto menu
     */
    function dettaglioPuntoMenu() {
        $pm_id = $_POST['rowid'];
        $_POST = array();
        $model = 'devPuntiMenu';
        $_POST['event'] = 'openform';
        $_POST['modo'] = 'edit';
        $_POST['pm_id'] = $pm_id;
        $_POST[$model . '_returnModel'] = $this->nameForm;
        $_POST[$model . '_returnEvent'] = 'returnFromPuntiMenu';

        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     * Crea un nuovo punto menu
     */
    function nuovoPuntoMenu() {
        //$me_id = $_POST[$this->nameForm . '_ita_menu']['me_id'];
        //????
        //if (!$me_id) {
        $me_id = $this->me_id;
        //}
        $_POST = array();
        $model = 'devPuntiMenu';
        $_POST['event'] = 'openform';
        $_POST['modo'] = 'new';
        $_POST['me_id'] = $me_id;

        $_POST[$model . '_returnModel'] = $this->nameForm;
        $_POST[$model . '_returnEvent'] = 'returnFromPuntiMenu';

        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     * Cancella un punto menu
     */
    function confermaCancellaPuntoMenu() {
        $id = $_POST[$this->nameForm . '_gridMenu']['gridParam']['selrow'];

        $sql = "SELECT * FROM ita_puntimenu WHERE pm_id = " . $id;
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $pm_voce = $Ita_menu_rec['pm_voce'];
        $me_id_padre = $Ita_menu_rec['me_id'];

        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $pm_voce . "'";
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $me_id = $Ita_menu_rec['me_id'];

        if (!$this->deleteRecord($this->ITALSOFT_DB, 'ita_puntimenu', $id, '', 'pm_id')) {
            Out::msgInfo('Attenzione', 'Errore nella cancellazione del punto menu.');
            return false;
        }
        //Out::msgInfo('Messaggio', 'Punto menu cancellato correttamente');
        // Riordino sequenza
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $me_id_padre . " ORDER BY pm_sequenza";
        $Ita_puntimenu_tab_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        $i = 10;
        foreach ($Ita_puntimenu_tab_seq as $key => $Ita_puntimenu_rec_seq) {
            $Ita_puntimenu_rec_seq['pm_sequenza'] = $i;
            $this->updateRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec_seq, '', 'pm_id');
            $i += 10;
        }

        // Refresh griglia
        $tableId = $this->nameForm . '_gridMenu';
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $me_id_padre . " ORDER BY pm_sequenza";
        $ita_grid01 = new TableView($tableId, array('sqlDB' => $this->ITALSOFT_DB, 'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(100000);
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $ita_grid01->getDataPage('json');

        return true;
    }

    function creaSqlRicerca() {
        $sql = "SELECT DISTINCT me.me_id, me.me_menu, me.me_descrizione FROM ita_menu me, ita_puntimenu pm WHERE 1=1";
        $codice = $_POST[$this->nameForm . '_mCod'];
        $descrizione = $_POST[$this->nameForm . '_mDes'];
        $descrizione_pm = $_POST[$this->nameForm . '_mDesPm'];
        $voce_pm = $_POST[$this->nameForm . '_mVocPm'];
        if ($codice != "") {
            $sql .= " AND me.me_menu LIKE '%" . $codice . "%'";
        }
        if ($descrizione != "") {
            $sql .= " AND me.me_descrizione LIKE '%" . $descrizione . "%'";
        }
        if ($descrizione_pm != "") {
            $sql .= " AND pm.pm_descrizione LIKE '%" . $descrizione_pm . "%'";
        }
        if ($voce_pm != "") {
            $sql .= " AND pm.pm_voce LIKE '%" . $voce_pm . "%'";
        }
        if ($descrizione_pm != "" || $voce_pm != "") {
            $sql .= " AND pm.me_id = me.me_id";
        }
        return $sql;
    }

    function ordinaTabella($id) {
        $ordinamento = $_POST['sidx'];
        $sql = "";
        switch ($id) {
            case $this->nameForm . '_gridRisultati':
                $sql = $this->creaSqlRicerca();
                break;
            case $this->nameForm . '_gridMenu':
                $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $_POST[$this->nameForm . '_ita_menu']['me_id'];
                break;
        }
        $griglia = new TableView($_POST['id'], array('sqlDB' => $this->ITALSOFT_DB, 'sqlQuery' => $sql));
        $griglia->setPageNum($_POST['page']);
        $griglia->setPageRows($_POST['rows']);
        $griglia->setSortIndex($ordinamento);
        $griglia->setSortOrder($_POST['sord']);
        $griglia->getDataPage('json');
    }

    function refreshGridMenu() {
        $tableId = $this->nameForm . '_gridMenu';
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $_POST['id_padre'] . " ORDER BY pm_sequenza";
        $ita_grid01 = new TableView($tableId, array('sqlDB' => $this->ITALSOFT_DB, 'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(100000);
//        $ita_grid01->setSortIndex($_POST['sidx']);
//        $ita_grid01->setSortOrder($_POST['sord']);
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $ita_grid01->getDataPage('json');
    }

    private function haPuntiMenu($me_id) {
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = '" . $me_id . "'";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        if (count($Ita_puntimenu_tab) > 0) {
            return true;
        }
        return false;
    }

// INIZIO FUNZIONI DI UTULITA

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

    /**
     * Mostra un div e nasconde gli altri
     * @param String $div Il pannello da mostrare
     */
    function Pannello($div) {
        switch ($div) {
            case 'ricerca':
                Out::show($this->divRic);
                Out::hide($this->divRis);
                Out::hide($this->divGes);
                break;
            case 'risultati':
                Out::show($this->divRis);
                Out::hide($this->divRic);
                Out::hide($this->divGes);
                break;
            case 'gestione':
                Out::hide($this->divRis);
                Out::hide($this->divRic);
                Out::show($this->divGes);
                break;
        }
    }

    /**
     * Assegna "" (stringa vuota) a tutti i campi indicati dagli ID passati
     * @param Array $arr Array di ID di elmeneti HTML di campi da impostare a ""
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Pulisci($arr) {
        foreach ($arr as $value) {
            Out::valore($this->nameForm . '_' . $value, '');
        }
    }

    private function compilaMenu($base = false) {
        if (!$base) {
            $base = 'TI_MEN';
        }

        $ini = array();

        /* @var $menLib menLib */
        $menLib = new menLib();

        $menu = $menLib->GetIta_menu($base);

        if (!$menu) {
            return false;
        }

        $ini['Config'] = array();
        $ini['Config']['me_descrizione'] = $menu['me_descrizione'];

        $ini['Info'] = array();
        $ini['Info']['date'] = date('d/m/Y');
        $ini['Info']['time'] = date('H:i:s');
        $ini['Info']['user'] = App::$utente->getKey('nomeUtente');

        $subm = $menLib->GetIta_puntimenu($menu['me_id']);

        if ($subm) {
            foreach ($subm as $punt) {
                $ini[$punt['pm_voce']] = array();
                $ini[$punt['pm_voce']]['pm_descrizione'] = $punt['pm_descrizione'];
                $ini[$punt['pm_voce']]['pm_sequenza'] = $punt['pm_sequenza'];
                $ini[$punt['pm_voce']]['pm_categoria'] = $punt['pm_categoria'];
                $ini[$punt['pm_voce']]['pm_datamod'] = $punt['pm_datamod'];
                $ini[$punt['pm_voce']]['pm_abilitato'] = $punt['pm_abilitato'];
                $ini[$punt['pm_voce']]['pm_model'] = $punt['pm_model'];
                $ini[$punt['pm_voce']]['pm_post'] = $punt['pm_post'];
                $ini[$punt['pm_voce']]['pm_flagvis'] = $punt['pm_flagvis'];
                $ini[$punt['pm_voce']]['pm_color'] = $punt['pm_color'];
                $ini[$punt['pm_voce']]['pm_background'] = $punt['pm_background'];
                $ini[$punt['pm_voce']]['pm_icon'] = $punt['pm_icon'];

                if ($punt['pm_categoria'] == 'ME') {
                    $this->compilaMenu($punt['pm_voce']);
                }
            }
        }

        itaLib::writeIniFile($menLib->getIniPath() . "/{$menu['me_menu']}.ini", $ini);
    }

}

?>
