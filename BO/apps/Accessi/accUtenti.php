<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proOrgLayout.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Timbrature/timRic.class.php';
include_once ITA_BASE_PATH . '/apps/Timbrature/timLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibCityWare.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR_UTENTI.class.php';

function accUtenti() {
    $accUtenti = new accUtenti();
    $accUtenti->parseEvent();
    return;
}

class accUtenti extends itaModel {

    public $ITW_DB;
    public $WORD_DB;
    public $LIQ_DB;
    public $SS_DB;
    public $SEGR_DB;
    public $ISCW_DB;
    public $PRAM_DB;
    public $ITALWEB_DB;
    public $ITALWEB;
    public $nameForm = "accUtenti";
    public $divGes = "accUtenti_divGestione";
    public $divRis = "accUtenti_divRisultato";
    public $divRic = "accUtenti_divRicerca";
    public $gridRisultato = "accUtenti_gridUtenti";
    public $gridOggetti = "accUtenti_gridOggetti";
    public $gridBorUteliv = "accUtenti_gridBorUteliv";
    public $ExtGru = array();
    public $WordPers = array();
    public $praLib;
    public $proLib;
    public $accLib;
    public $envLib;
    public $utiEnte;
    public $accLibCityWare;
    public $libBorUtenti;
    public $PROT_DB;
    public $timLib;
    public $rowid;
    private $dataBorUteliv;
    private $dataBorLivelli;
    private $dataBorUtelivDelete;
    private $dataBorUtefirImmagine;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->accLib = new accLib();
        $this->praLib = new praLib();
        $this->timLib = new timLib();
        $this->envLib = new envLib();
        $this->utiEnte = new utiEnte();
        $this->accLibCityWare = new accLibCityWare();
        $this->libBorUtenti = new cwbLibDB_BOR_UTENTI();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITW_DB = $this->accLib->getITW();
        $this->ITALWEB = $this->accLib->getITALWEB();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->SetExtGru();
        $this->dataBorUteliv = App::$utente->getKey($this->nameForm . '_dataBorUteliv');
        $this->dataBorLivelli = App::$utente->getKey($this->nameForm . '_dataBorLivelli');
        $this->dataBorUtelivDelete = App::$utente->getKey($this->nameForm . '_dataBorUtelivDelete');
        $this->dataBorUtefirImmagine = App::$utente->getKey($this->nameForm . '_dataBorUtefirImmagine');
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_dataBorUteliv', $this->dataBorUteliv);
            App::$utente->setKey($this->nameForm . '_dataBorLivelli', $this->dataBorLivelli);
            App::$utente->setKey($this->nameForm . '_dataBorUtelivDelete', $this->dataBorUtelivDelete);
            App::$utente->setKey($this->nameForm . '_dataBorUtefirImmagine', $this->dataBorUtefirImmagine);
        }
    }

    public function setRowid($rowid) {
        $this->rowid = $rowid;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $sql = "SHOW DATABASES LIKE 'GEPR" . App::$utente->getKey('ditta') . "'";
                $lista = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);
                if (!$lista) {
                    $sql = "SHOW DATABASES LIKE 'itadb_GEPR" . App::$utente->getKey('ditta') . "'";
                    $lista = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);
                    if (!$lista) {
                        Out::tabRemove($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabTimbrature");
                    }
                }
                Out::tabRemove($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabWord");
                //Out::tabRemove($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabCds");
                Out::tabRemove($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabParSpeciali");
                Out::tabRemove($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabvari");
                Out::hide($this->nameForm . '_FlagInterno');
                Out::hide($this->nameForm . '_FlagEsterno');
                Out::hide($this->nameForm . '_FlagInterno_lbl');
                Out::hide($this->nameForm . '_FlagEsterno_lbl');
                Out::hide($this->nameForm . '_UTENTI[UTEFIL__3]_field');

                $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Paleo') {
                    Out::hide($this->nameForm . "_divDatiPaleo");
                }
                if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Infor') {
                    Out::hide($this->nameForm . "_divDatiInfor");
                }
                if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Infor') {
                    Out::hide($this->nameForm . "_divDatiInfor");
                }
                if ($PARMENTE_rec['TIPOPROTOCOLLO'] != 'Italsoft-remoto' && $PARMENTE_rec['TIPOPROTOCOLLO'] != 'Italsoft-remoto-allegati') {
                    Out::hide($this->nameForm . "_divDatiProtremoto");
                }

                if ($this->rowid) {
                    $this->Dettaglio($this->rowid);
                } else {
                    $this->OpenRicerca();
                    TableView::disableEvents($this->gridRisultato);
                    Out::attributo($this->nameForm . "_FlagInterno", "checked", "0", "checked");
                    Out::setFocus('', $this->nameForm . '_Utente');
                }
                //                Out::setFocus('',$this->nameForm.'_Utelog');
                //
                //$this->SetParword();
                //$this->SetParliq();
                //$this->SetParss();
                //$this->SetParsegr();
                //$this->SetParuni();

                /*
                 * Controllo FLAG amministratore
                 */
                $utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                if ($utenti_rec['UTEFLADMIN'] != '1') {
                    Out::removeElement($this->nameForm . '_CambiaFiscale');
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridRisultato:
                        $this->Dettaglio($_POST['rowid']);
                        break;

                    case $this->gridBorUteliv:
                        $this->loadBorLivelli();
                        $idx = intval($_POST['rowid']);
                        $this->dettaglioBorUteliv($idx);
                        break;
                }
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $sql = $this->CreaSql() . " ORDER BY UTELOG ASC";
                $parameters = array("Sql" => $sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITW_DB, 'accUtenti', $parameters);
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridRisultato, array(
                    'sqlDB' => $this->ITW_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('UTELOG');
                $ita_grid01->exportXLS('', 'Anacat.xls');
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    default:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITW_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPageFromArray('json', $this->elaboraRecords($ita_grid01->getDataArray()));
                        break;

                    case $this->gridBorUteliv:
                        TableView::clearGrid($this->gridBorUteliv);
                        $this->loadBorLivelli();
                        $dataGridBorUteliv = $this->dataBorUteliv;
                        foreach ($dataGridBorUteliv as &$dataGridBorUteliv_rec) {
                            $dataIniz = strtotime($dataGridBorUteliv_rec['DATAINIZ']);
                            $dataGridBorUteliv_rec['DATAINIZ'] = ($dataIniz ? date('Ymd', $dataIniz) : '');
                            $dataFine = strtotime($dataGridBorUteliv_rec['DATAFINE']);
                            $dataGridBorUteliv_rec['DATAFINE'] = ($dataFine ? date('Ymd', $dataFine) : '');

                            $dataGridBorUteliv_rec['FLAGVALID'] = $dataGridBorUteliv_rec['FLAG_VALID'] ? 'v' : '';
                            $dataGridBorUteliv_rec['DESCRIZIONE'] = $this->dataBorLivelli[$dataGridBorUteliv_rec['IDLIVELL']]['DES_LIVELL'];
                        }
                        $gridScheda = new TableView($this->gridBorUteliv, array('arrayTable' => $dataGridBorUteliv, 'rowIndex' => 'idx'));
                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPage('json');
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridOggetti:
                        proRic::proRicOgg($this->nameForm, '', 'proAnaogg', $_POST);
                        break;

                    case $this->gridBorUteliv:
                        $this->loadBorLivelli();
                        $this->dettaglioBorUteliv();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridOggetti:
                        ItaDB::DBDelete($this->PROT_DB, 'OGGUTENTI', 'ROWID', $_POST['rowid']);
                        $this->caricaOggetti($_POST[$this->nameForm . '_UTENTI']['UTELOG']);
                        break;

                    case $this->gridBorUteliv:
                        $idx = intval($_POST['rowid']);

                        if ($this->dataBorUteliv[$idx]['IDUTELIV']) {
                            $this->dataBorUtelivDelete[] = $this->dataBorUteliv[$idx];
                        }

                        unset($this->dataBorUteliv[$idx]);
                        TableView::reload($this->gridBorUteliv);
                        break;
                }
                break;

            case 'returnanamed':
                $Anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_UTENTI[UTEANA__1]', $Anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_Mednom', $Anamed_rec['MEDNOM']);
                //                Out::codice('closeCurrDialog();');
                break;

            case 'returnDipe':
                $dipeRec = $this->timLib->GetDipana($_POST['retKey'], 'rowid');
                if ($dipeRec) {
                    Out::valore($this->nameForm . '_UTENTI[UTEANN__8]', $dipeRec['DIPCOD']);
                }
                break;

            case 'returnPraDipe':
                $Ananom_rec = $this->praLib->GetAnanom($_POST['retKey'], 'codice');
                Out::msgInfo($titolo, print_r($Ananom_rec['NOMRES'], true));
                Out::valore($this->nameForm . '_UTENTI[UTEANA__3]', $Ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_DecodDipendente', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
                break;
            case 'returnUnires':
                if ($_POST['dialogEvent'] == 'addGridRow') {
                    $extraData = $_POST['extraData'];
                    $_POST = array();
                    $model = "praDipe";
                    $_POST[$model . '_returnField'] = "";
                    $_POST[$model . '_returnModel'] = $this->nameForm;
                    $_POST[$model . '_returnEvent'] = 'returnPraDipe';
                    $_POST[$model . '_returnId'] = $this->nameForm . "_UTENTI[UTEANA__3]";
                    $_POST['event'] = 'openform';
                    $_POST['openMode'] = 'newFromUtenti';
                    if ($extraData) {
                        foreach ($extraData as $key => $value) {
                            $_POST[$key] = $value;
                        }
                    }
                    itaLib::openDialog($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                } else {
                    $Ananom_rec = $this->praLib->GetAnanom($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_UTENTI[UTEANA__3]', $Ananom_rec['NOMRES']);
                    Out::valore($this->nameForm . '_DecodDipendente', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
                }
                break;

            case 'returngru':
                $sql = "SELECT GRUCOD, GRUDES FROM GRUPPI WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anagru_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
                    if (count($Anagru_tab) == 1) {
                        Out::valore($this->nameForm . $_POST['retid'], $Anagru_tab[0]['GRUCOD']);
                        switch ($_POST['retid']) {
                            case '_UTENTI[UTEGRU]':
                                $desId = '_Desgru1';
                                break;
                            case '_UTENTI[UTEGEX__1]':
                                $desId = '_Desgru2';
                                break;
                            case '_UTENTI[UTEGEX__2]':
                                $desId = '_Desgru3';
                                break;
                            case '_UTENTI[UTEGEX__3]':
                                $desId = '_Desgru4';
                                break;
                            case '_UTENTI[UTEGEX__4]':
                                $desId = '_Desgru5';
                                break;
                            case '_UTENTI[UTEGEX__5]':
                                $desId = '_Desgru6';
                                break;
                            case '_UTENTI[UTEGEX__6]':
                                $desId = '_Desgru7';
                                break;
                            case '_UTENTI[UTEGEX__7]':
                                $desId = '_Desgru8';
                                break;
                            case '_UTENTI[UTEGEX__8]':
                                $desId = '_Desgru9';
                                break;
                            case '_UTENTI[UTEGEX__9]':
                                $desId = '_Desgru10';
                                break;
                        }
                        Out::valore($this->nameForm . $desId, $Anagru_tab[0]['GRUDES']);
                    }
                    //                    Out::codice('closeCurrDialog();');
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                    App::log($e->getMessage());
                }
                break;
            case 'returndog':
                $anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
                $oggutenti_check = $this->proLib->GetOggUtenti($_POST['retid'][$this->nameForm . '_UTENTI']['UTELOG'], $anadog_rec['DOGCOD']);
                if ($anadog_rec && !$oggutenti_check) {
                    $oggutenti_rec = array(
                        'DOGCOD' => $anadog_rec['DOGCOD'],
                        'UTELOG' => $_POST['retid'][$this->nameForm . '_UTENTI']['UTELOG']
                    );
                    ItaDB::DBInsert($this->PROT_DB, 'OGGUTENTI', 'ROWID', $oggutenti_rec);
                    $this->caricaOggetti($oggutenti_rec['UTELOG']);
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_nessunaScadenza':
                        $this->decodeFlagScadenza($_POST[$this->nameForm . '_nessunaScadenza']);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CreaCookie':
                        $fields = array(
                            array(
                                'label' => array(
                                    'value' => "Logname"
                                ),
                                'id' => $this->nameForm . '_logname',
                                'name' => $this->nameForm . '_logname',
                                'value' => "",
                                'type' => 'ita-edit',
                                'class' => 'text'
                            ),
                            array(
                                'label' => array(
                                    'value' => "Cod. Fis"
                                ),
                                'id' => $this->nameForm . '_cf',
                                'name' => $this->nameForm . '_cf',
                                'value' => "",
                                'type' => 'ita-edit',
                                'class' => 'text'
                            )
                        );

                        Out::msgInput('Crea Cookie', $fields, array(
                            'Crea Cookie' => array(
                                'id' => $this->nameForm . '_Crea',
                                'model' => $this->nameForm,
                                'class' => 'ita-edit',
                                'shortCut' => "f5"
                            ),
                            'Annulla' => array(
                                'id' => $this->nameForm . '_Annulla',
                                'model' => $this->nameForm,
                                'class' => 'ita-button',
                                'shortCut' => "f5"
                            )), $this->nameForm . "_workSpace"
                        );
                        Out::valore($this->nameForm . '_logname', $_POST[$this->nameForm . '_UTENTI']['UTELOG']);

                        if ($_POST[$this->nameForm . '_UTENTI']['UTEANN__8']) {
                            $sql = "SELECT * FROM DIPANA WHERE DIPCOD='" . $_POST[$this->nameForm . '_UTENTI']['UTEANN__8'] . "'";
                            $Dipana = ItaDB::DBSQLSelect($this->timLib->getGEPRDB(), $sql, false);
                            Out::valore($this->nameForm . '_cf', $Dipana['DIPCDF']);
                        }
                        break;

                    case $this->nameForm . '_Crea':
                        if ($_POST[$this->nameForm . '_logname'] == "") {
                            Out::msgInfo("Attenzione", "Insrire il parametro logname");
                            break;
                        } else if ($_POST[$this->nameForm . '_cf'] == "") {
                            Out::msgInfo("Attenzione", "Insrire il parametro cf");
                            break;
                        } else {
                            $time = time() + 60 * 60 * 24 * 365;
                            if (!setcookie(str_replace(".", "_", $_POST[$this->nameForm . '_logname']), $_POST[$this->nameForm . '_cf'], $time, '', '', App::isConnectionSecure(), true)) {
                                Out::msgStop("Attenzione", "Cookie non creato");
                                break;
                            }
                            $sql = "SELECT * FROM UTENTI WHERE UTELOG='" . addslashes($_POST[$this->nameForm . '_logname']) . "'";

                            if (!$Utenti = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false)) {
                                Out::msgStop("Attenzione", "Utelog non trovato in UTENTI");
                                break;
                            }

                            $sql = "SELECT * FROM DIPANA WHERE DIPCOD='" . $Utenti['UTEANN__8'] . "'";
                            if ($Dipana = ItaDB::DBSQLSelect($this->timLib->getGEPRDB(), $sql, false)) {
                                if (!$Dipana['DIPCDF']) {
                                    $Dipana['DIPCDF'] = $_POST[$this->nameForm . '_cf'];
                                    ItaDB::DBUpdate($this->timLib->getGEPRDB(), 'DIPANA', 'ROWID', $Dipana);
                                    Out::msgInfo("", "Codice Fiscale inserito nell'anagrafica dipendente");
                                } else {
                                    if ($Dipana['DIPCDF'] != $_POST[$this->nameForm . '_cf']) {
                                        Out::msgInfo("Attenzione", "Il codice Fiscale inserito non concide con quello in anagrafica, controllare il cf nell'anagrafica dipendente");
                                    }
                                }
                            } else {
                                Out::msgInfo("", "Dipendente non trovato in anagrafica, controlare anagrafica dipendenti");
                            }
                            Out::msgInfo("", "Cookie Creato Correttamente");
                        }
                        break;

                    case $this->nameForm . '_UTENTI[UTEGRU]_butt':
                        $cod_id = '_UTENTI[UTEGRU]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__1]_butt':
                        $cod_id = '_UTENTI[UTEGEX__1]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__2]_butt':
                        $cod_id = '_UTENTI[UTEGEX__2]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__3]_butt':
                        $cod_id = '_UTENTI[UTEGEX__3]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__4]_butt':
                        $cod_id = '_UTENTI[UTEGEX__4]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__5]_butt':
                        $cod_id = '_UTENTI[UTEGEX__5]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__6]_butt':
                        $cod_id = '_UTENTI[UTEGEX__6]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__7]_butt':
                        $cod_id = '_UTENTI[UTEGEX__7]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__8]_butt':
                        $cod_id = '_UTENTI[UTEGEX__8]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__9]_butt':
                        $cod_id = '_UTENTI[UTEGEX__9]';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;
                    // Ricerca gruppi
                    case $this->nameForm . '_Gruppo_butt':
                        $cod_id = '_Gruppo';
                        accRic::accRicGru("accUtenti", $cod_id, '');
                        break;

                    case $this->nameForm . '_UTENTI[UTEANA__1]_butt':
                        accRic::accRicAnamed("accUtenti", "WHERE MEDUFF<>''");
                        break;
                    case $this->nameForm . '_UTENTI[UTEANA__3]_butt':
                        $datiUtente = array(
                            "COGNOME" => $_POST[$this->nameForm . '_RICHUT']['RICCOG'],
                            "NOME" => $_POST[$this->nameForm . '_RICHUT']['RICNOM'],
                            "MAIL" => $_POST[$this->nameForm . '_RICHUT']['RICMAI']
                        );
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "UTENTI[UTEANA__3]", true, array('openMode' => 'newFromUtenti', 'datiUtente' => $datiUtente));
                        break;

                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridRisultato, array(
                            'sqlDB' => $this->ITW_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows((isSet($_POST['rows']) ? $_POST['rows'] : 10000));
                        $ita_grid01->setSortIndex('UTELOG');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPageFromArray('json', $this->elaboraRecords($ita_grid01->getDataArray()))) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes, '', 0);
                            Out::hide($this->divRic, '', 0);
                            Out::show($this->divRis, '', 0);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltroUtente');
                            Out::show($this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridRisultato);
                        }
                        break;
                    case $this->nameForm . '_AltroUtente':
                        $this->OpenRicerca();
                        Out::attributo($this->nameForm . "_FlagInterno", "checked", "0", "checked");
                        break;

                    case $this->nameForm . '_Duplica':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltroUtente');
                        Out::hide($this->nameForm . '_Password');
                        Out::clearFields($this->nameForm, $this->nameForm . '_divAppoggio');
                        Out::enableField($this->nameForm . '_UTENTI[UTELOG]');
                        Out::valore($this->nameForm . '_UTENTI[UTECOD]', '');
                        Out::valore($this->nameForm . '_UTENTI[UTELOG]', '');
                        Out::valore($this->nameForm . '_UTENTI[UTEFIS]', '');
                        Out::valore($this->nameForm . '_UTENTI[UTELDAP]', '');
                        Out::valore($this->nameForm . '_UTENTI[UTEFLADMIN]', 0);
                        Out::valore($this->nameForm . '_UTENTI[DATAINIZ]', date('Ymd'));
                        Out::valore($this->nameForm . '_UTENTI[DATAFINE]', '');
                        Out::valore($this->nameForm . '_UTENTI[UTEDATAULUSO]', '');
                        Out::valore($this->nameForm . '_FirmaRemota[Utente]', '');
                        Out::valore($this->nameForm . '_FirmaRemota[Password]', '');
                        Out::valore($this->nameForm . '_Utepas', '');
                        Out::attributo($this->nameForm . '_UTENTI[UTECOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::hide($this->nameForm . '_Aggiorna');
                        Out::hide($this->nameForm . '_Cancella');
                        //    Out::hide($this->nameForm.'_Password');
                        Out::setFocus('', $this->nameForm . '_UTENTI[UTELOG]');

                        $this->ssoCitywareFields();
                        break;

                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltroUtente');
                        Out::hide($this->nameForm . '_Password');
                        Out::valore($this->nameForm . '_UTENTI[ROWID]', '');
                        Out::attributo($this->nameForm . '_UTENTI[UTECOD]', 'readonly', '1');
                        Out::enableField($this->nameForm . '_UTENTI[UTELOG]');
                        Out::clearFields($this->nameForm, '');
                        Out::valore($this->nameForm . '_ProtRemoto[ROWID]', "");
                        Out::valore('Desgru1', '');
                        Out::valore('Desgru2', '');
                        Out::valore('Desgru3', '');
                        Out::valore('Desgru4', '');
                        Out::valore('Desgru5', '');
                        Out::valore('Desgru6', '');
                        Out::valore('Desgru7', '');
                        Out::valore('Desgru8', '');
                        Out::valore('Desgru9', '');
                        Out::valore('Desgru10', '');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::hide($this->nameForm . '_Aggiorna');
                        Out::hide($this->nameForm . '_Cancella');
                        //    Out::hide($this->nameForm.'_Password');
                        Out::setFocus('', $this->nameForm . '_UTENTI[UTELOG]');

                        $this->ssoCitywareFields();
                        break;
                    case $this->nameForm . '_Aggiungi':

                        /*
                         * Controllo unicità del logname applicata case insensitive
                         * Per peroblemi con SSO
                         * 
                         * @michele.moscioni 27/09/2019 
                         * 
                         */
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $ctr_utelog = strtoupper(trim($utenti_rec['UTELOG']));
                        $sql = "SELECT UTELOG FROM UTENTI WHERE " . $this->ITW_DB->strUpper('UTELOG') . "='" . addslashes($ctr_utelog) . "'";
                        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
                        if (count($utenti_rec) != 0) {
                            Out::msgStop("Avviso", "Logname gia' presente.");
                            Out::setFocus('', $this->nameForm . '_UTENTI[UTELOG]');
                            break;
                        }

                        /*
                         * Controllo esistenza su Cityware
                         */
                        if ($this->accLib->isSSOCitywareEnabled()) {
                            $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($ctr_utelog);
                            if (count($libBorUtenti_rec)) {
                                Out::msgStop("Avviso", "Logname già presente su database Cityware. Contattare l'assistenza.");
                                Out::setFocus('', $this->nameForm . '_UTENTI[UTELOG]');
                                break;
                            }
                        }

                        // Lettura del progressivo
                        $sql = "SELECT MAX(UTECOD) AS ULTIMO FROM UTENTI";
                        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                        if (!$utenti_rec) {
                            $Ultimo = "1";
                        } else {
                            $Ultimo = $utenti_rec['ULTIMO'] + 1;
                        }
                        $sql = "SELECT UTECOD FROM UTENTI WHERE UTECOD='$Ultimo'";
                        try {   // Effettuo la FIND
                            $utenti_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);

                            if (count($utenti_tab) != 0) {
                                Out::msgStop("Codice già  presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_UTENTI[UTECOD]');
                                break;
                            }

                            $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];

                            /*
                             * Forzatura parametrica case ustente
                             * 
                             * @michele.moscioni 27/09/2019
                             * 
                             */
                            $utenti_rec['UTELOG'] = $this->accLib->setUserNameCase($utenti_rec['UTELOG']);
                            $utenti_rec['UTECOD'] = $Ultimo;
                            $utenti_rec['UTEUPA'] = date("Ymd");
                            if ($utenti_rec['UTEFIL__2'] == '' || $utenti_rec['UTEFIL__2'] == '0') {
                                $utenti_rec['UTEFIL__2'] = '20';
                            }
                            if ($utenti_rec['UTEDPA'] == '' || $utenti_rec['UTEDPA'] == '0') {
                                $utenti_rec['UTEDPA'] = '180';
                            }
                            if ($_POST[$this->nameForm . '_nessunaScadenza'] == '1') {
                                $utenti_rec['UTEDPA'] = '9999';
                            }
                            $utenti_rec['UTESPA'] = date('Ymd');

                            /*
                             * Verifica esistenza record RICHUT.
                             * Se già esiste un record per il nuovo RICCOD,
                             * lo ripulisco e riutilizzo lo stesso ROW_ID.
                             */

                            $richut_rec = $_POST[$this->nameForm . '_RICHUT'];
                            $richut_rec['RICCOD'] = $Ultimo;

                            $richut_check_rec = $this->accLib->GetRichut($Ultimo);
                            if ($richut_check_rec) {
                                $richut_rec['ROWID'] = $richut_check_rec['ROWID'];
                                $richut_rec = array_merge(array_fill_keys(array_keys($richut_check_rec), ''), $richut_rec);
                                $nRows = ItaDB::DBUpdate($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                            } else {
                                $nRows = ItaDB::DBInsert($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                            }

                            //
                            //aggiunta parametri Fascicolo
                            //
                            $parmFascicolo = array();
                            $parmFascicolo['Caps'] = $_POST[$this->nameForm . '_ParmFascicolo']['Caps'];
                            $parmFascicolo['Vis'] = $_POST[$this->nameForm . '_ParmFascicolo']['Vis'];
                            $fascicolo_rec = array();
                            $fascicolo_rec['UTECOD'] = $Ultimo;
                            $fascicolo_rec['METAKEY'] = "ParmFascicolo";
                            $Metavalue = array(
                                'ParmFascicolo' => $parmFascicolo
                            );
                            $fascicolo_rec['METAVALUE'] = serialize($Metavalue);
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $fascicolo_rec);


                            $fascicolo_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmFascicolo');
                            if ($fascicolo_rec) {
                                $meta = unserialize($fascicolo_rec['METAVALUE']);
                                Out::valore($this->nameForm . '_ParmFascicolo[ROWID]', $fascicolo_rec['ROWID']);
                                Out::valori($meta['ParmFascicolo'], $this->nameForm . '_ParmFascicolo');
                            }

                            //
                            //aggiunta per Paleo
                            //
                                $operatore_rec = $_POST[$this->nameForm . '_OperatorePaleo'];
                            $OperatorePaleo['CodiceUO'] = $operatore_rec['CodiceUO'];
                            $OperatorePaleo['Cognome'] = $operatore_rec['Cognome'];
                            $OperatorePaleo['Nome'] = $operatore_rec['Nome'];
                            $OperatorePaleo['Ruolo'] = $operatore_rec['Ruolo'];
                            $op_rec = array();
                            $op_rec['UTECOD'] = $Ultimo;
                            $op_rec['METAKEY'] = "ParmPaleo";
                            $Metavalue = array(
                                'OperatorePaleo' => $OperatorePaleo
                            );
                            $op_rec['METAVALUE'] = serialize($Metavalue);
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $op_rec);
                            //
                            //aggiunta per Infor
                            //
                                $infor_rec = $_POST[$this->nameForm . '_Infor'];
                            $DatiInfor['User'] = $infor_rec['User'];
                            $DatiInfor['Corrispondente'] = $infor_rec['Corrispondente'];
                            $inf_rec = array();
                            $inf_rec['UTECOD'] = $Ultimo;
                            $inf_rec['METAKEY'] = "ParmInfor";
                            $Metavalue = array(
                                'DatiInfor' => $DatiInfor
                            );
                            $inf_rec['METAVALUE'] = serialize($Metavalue);
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);
                            //
                            //aggiunta per Protocollo Remoto
                            //
                                $inf_rec = array();
                            $inf_rec['UTECOD'] = $Ultimo;
                            $inf_rec['METAKEY'] = "ParmProtRemoto";
                            $inf_rec['METAVALUE'] = serialize(array(
                                'ProtRemoto' => $_POST[$this->nameForm . '_ProtRemoto']
                            ));
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);

                            if ($this->accLib->isSSOCitywareEnabled()) {
                                /*
                                 * Fix case UTELDAP con CW
                                 */

                                $utenti_rec['UTELDAP'] = strtoupper($utenti_rec['UTELDAP']);
                            }

                            try {
                                $nRows = ItaDB::DBInsert($this->ITW_DB, 'UTENTI', 'ROWID', $utenti_rec);
                                //$this->AggParspec($utenti_rec['UTECOD'],App::$utente-> getKey('ditta'));
                                //  Out::msgInfo("Atenzione", "Aggiunto il nuovo utente: " . $utenti_rec['UTELOG']);
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }

                            $lastid = ItaDB::DBLastId($this->ITW_DB);

                            /*
                             * Aggiornamento Dati CityWare
                             */

                            if ($this->accLib->isSSOCitywareEnabled()) {
                                /*
                                 * Prendo i record itaEngine
                                 */
                                $UtenteIE_rec = array(
                                    'UTENTI' => $this->accLib->GetUtenti($utenti_rec['UTECOD']),
                                    'RICHUT' => $this->accLib->GetRichut($utenti_rec['UTECOD'])
                                );

                                /*
                                 * Trasformo il record in formato CW
                                 */
                                $UtenteCW_rec = $this->accLibCityWare->convertUserIE2CW($UtenteIE_rec);

                                /*
                                 * Leggo i campi aggiuntivi dalla form
                                 * e li integro al record finale
                                 */
                                $bor_utenti_rec = $_POST[$this->nameForm . '_BOR_UTENTI'];
                                $bor_utefir_rec = $_POST[$this->nameForm . '_BOR_UTEFIR'];
                                $UtenteCW_rec['BOR_UTENTI'] = array_merge($UtenteCW_rec['BOR_UTENTI'], $bor_utenti_rec);
                                $UtenteCW_rec['BOR_UTEFIR'] = array_merge($UtenteCW_rec['BOR_UTEFIR'], $bor_utefir_rec);

                                /*
                                 * WIP modifica immagine
                                 */
                                $UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'] = $this->dataBorUtefirImmagine;
                                $UtenteCW_rec['BOR_UTEFIR']['WIDTH'] = intval($UtenteCW_rec['BOR_UTEFIR']['WIDTH']);
                                $UtenteCW_rec['BOR_UTEFIR']['HEIGHT'] = intval($UtenteCW_rec['BOR_UTEFIR']['HEIGHT']);

                                /*
                                 * additionalInfo per immagine
                                 */
                                $UtenteCW_rec['additionalInfo'] = array();
                                $UtenteCW_rec['additionalInfo']['pictureWidth'] = $UtenteCW_rec['BOR_UTEFIR']['WIDTH'];
                                $UtenteCW_rec['additionalInfo']['pictureHeight'] = $UtenteCW_rec['BOR_UTEFIR']['HEIGHT'];

                                // $UtenteCW_rec['BOR_FRMUTE'] = array_merge($libBorUtenti_rec['BOR_FRMUTE'], $UtenteCW_rec['BOR_FRMUTE']);

                                if ($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE'] == false) {
                                    unset($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE']);
                                }

                                foreach ($this->dataBorUteliv as $borUteliv) {
                                    $borUteliv['CODUTENTE'] = $UtenteCW_rec['BOR_UTENTI']['CODUTE'];
                                    unset($borUteliv['DES_LIVELL']);
                                    $UtenteCW_rec['BOR_UTELIV'][] = $borUteliv;
                                }

                                foreach ($this->dataBorUtelivDelete as $borUtelivDelete) {
                                    $borUtelivDelete['operation'] = itaModelService::OPERATION_DELETE;
                                    unset($borUtelivDelete['DES_LIVELL']);
                                    $UtenteCW_rec['BOR_UTELIV'][] = $borUtelivDelete;
                                }

                                /*
                                 * Password temporanea nel caso debba essere reimpostata
                                 */
                                if (!$UtenteCW_rec['BOR_UTENTI']['PWDUTE']) {
                                    $UtenteCW_rec['BOR_UTENTI']['PWDUTE'] = $this->ssoCitywareTempPassword();
                                } else {
                                    /*
                                     * Svuoto la password in quanto potrebbe essere criptata.
                                     * L'aggiornamento di quest'ultima avviene in accPassword.
                                     */
                                    unset($UtenteCW_rec['BOR_UTENTI']['PWDUTE']);
                                }

                                if (!$this->ssoCitywareValidate($UtenteCW_rec)) {
                                    break;
                                }

                                /*
                                 * Ricreo nel formato originale
                                 */
                                $libBorUtenti_rec_update = array(
                                    $utenti_rec['UTELOG'] => $UtenteCW_rec
                                );

                                $errorMessages = false;
                                $this->libBorUtenti->inserisciUtente($utenti_rec['UTELOG'], $libBorUtenti_rec_update, $errorMessages);

                                if ($errorMessages) {
                                    Out::msgStop("Errore", $errorMessages);
                                    break;
                                }
                            }

                            if ($utenti_rec['UTEPAS'] == '') {
                                $this->cambiaPassword($utenti_rec['UTECOD'], $utenti_rec['UTELOG'], 'nuovo');
                            }

                            $this->Dettaglio($lastid);
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su ANAGRAFICA UTENTI.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_ConfermaAggiornamentoCFCityware':
                        $confermaAggiornamentoCFCityware = true;
                    /*
                     * Procedo all'aggiorna senza break
                     */

                    case $this->nameForm . '_Aggiorna':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];

                        if ($this->accLib->isSSOCitywareEnabled()) {
                            /*
                             * Verifica del Codice Fiscale Cityware
                             */

                            $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utenti_rec['UTELOG']);
                            if (count($libBorUtenti_rec)) {
                                $libBorUtenti_rec = reset($libBorUtenti_rec);

                                if ($utenti_rec['UTEFIS'] == '' && $libBorUtenti_rec['BOR_UTENTI']['CODFISCALE'] != $utenti_rec['UTEFIS']) {
                                    /*
                                     * Se non è presente un CF su CWOL ma è presente su CW, salvo automaticamente quest'ultimo
                                     */
                                    $utenti_rec['UTEFIS'] = $libBorUtenti_rec['BOR_UTENTI']['CODFISCALE'];

                                    $this->insertAudit($this->ITW_DB, 'UTENTI', "UTELOG '{$utenti_rec['UTELOG']}' allineamento CF da Cityware: {$utenti_rec['UTEFIS']}");
                                } elseif ($utenti_rec['UTEFIS'] != '' && $libBorUtenti_rec['BOR_UTENTI']['CODFISCALE'] != $utenti_rec['UTEFIS']) {
                                    if (!isset($confermaAggiornamentoCFCityware)) {
                                        /*
                                         * Se è presente un CF su CWOL e non coincide con quello CW, chiedo conferma
                                         */
                                        $msgQuestion = 'Il codice fiscale inserito non coincide con quello presente nel database Cityware, quest\'ultimo verrà sovrascritto. Procedere?';
                                        $msgQuestion .= '<br><br>Codice fiscale inserito: ' . $utenti_rec['UTEFIS'];
                                        $msgQuestion .= '<br>Codice fiscale Cityware: ' . $libBorUtenti_rec['BOR_UTENTI']['CODFISCALE'];
                                        Out::msgQuestion('Aggiornamento codice fiscale', $msgQuestion, array(
                                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAggiornamentoCFCityware', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAggiornamentoCFCityware', 'model' => $this->nameForm, 'shortCut' => 'f5')
                                                )
                                        );

                                        break;
                                    }

                                    $this->insertAudit($this->ITW_DB, 'UTENTI', "UTELOG '{$utenti_rec['UTELOG']}' sovrascrittura CF Cityware: {$libBorUtenti_rec['BOR_UTENTI']['CODFISCALE']} => {$utenti_rec['UTEFIS']}");
                                }
                            }

                            /*
                             * Fix case UTELDAP con CW
                             */

                            $utenti_rec['UTELDAP'] = strtoupper($utenti_rec['UTELDAP']);
                        }

                        if ($utenti_rec['UTEDPA'] == '' || $utenti_rec['UTEDPA'] == '0') {
                            $utenti_rec['UTEDPA'] = '180';
                        }

                        if ($_POST[$this->nameForm . '_nessunaScadenza'] == '1') {
                            $utenti_rec['UTEDPA'] = '9999';
                        }

                        $flagResetPassword = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'FlagResetPassword');
                        if (!$flagResetPassword) {
                            /*
                             * Se non è in corso il reset della password, aggiorno la data di scadenza
                             */
                            if ($utenti_rec['UTEDPA'] == '9999') {
                                $utenti_rec['UTESPA'] = '';
                            } else {
                                $sql = "SELECT UTEUPA FROM UTENTI WHERE UTECOD='" . $utenti_rec['UTECOD'] . "'";
                                $utenti_appoggio = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);

                                if ($utenti_appoggio['UTEUPA']) {
                                    $dataScadenza = new DateTime(substr($utenti_appoggio['UTEUPA'], 6, 2) . '-' . substr($utenti_appoggio['UTEUPA'], 4, 2) . '-' . substr($utenti_appoggio['UTEUPA'], 0, 4));
                                    $dataScadenza->add(new DateInterval('P' . $utenti_rec['UTEDPA'] . 'D'));
                                    $utenti_rec['UTESPA'] = $dataScadenza->format('Ymd');
                                } else {
                                    $utenti_rec['UTESPA'] = '';
                                }
                            }
                        }

                        $richut_rec = $_POST[$this->nameForm . '_RICHUT'];

                        if ($richut_rec['ROWID'] == '') {
                            $richut_rec['RICCOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                        }

                        //
                        //aggiunta per Paleo
                        //
                        $operatore_rec = $_POST[$this->nameForm . '_OperatorePaleo']; //prende i campi CodiceUO, Cognome, Nome, Ruolo
                        $OperatorePaleo['CodiceUO'] = $operatore_rec['CodiceUO'];
                        $OperatorePaleo['Cognome'] = $operatore_rec['Cognome'];
                        $OperatorePaleo['Nome'] = $operatore_rec['Nome'];
                        $OperatorePaleo['Ruolo'] = $operatore_rec['Ruolo'];
                        $op_rec = array();
                        $op_rec['ROWID'] = $operatore_rec['ROWID'];
                        $op_rec['METAKEY'] = "ParmPaleo";
                        $Metavalue = array(
                            'OperatorePaleo' => $OperatorePaleo
                        );
                        $op_rec['METAVALUE'] = serialize($Metavalue);
                        if ($operatore_rec['ROWID'] == '') {
                            $op_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $op_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $op_rec);
                        }
                        //
                        //aggiunta per Infor
                        //
                        $infor_rec = $_POST[$this->nameForm . '_Infor']; //prende i campi User, Corrispondente
                        $dati_rec['User'] = $infor_rec['User'];
                        $dati_rec['Corrispondente'] = $infor_rec['Corrispondente'];
                        $inf_rec = array();
                        $inf_rec['ROWID'] = $infor_rec['ROWID'];
                        $inf_rec['METAKEY'] = "ParmInfor";
                        $Metavalue = array(
                            'DatiInfor' => $dati_rec
                        );
                        $inf_rec['METAVALUE'] = serialize($Metavalue);
                        if (!$inf_rec['ROWID']) {
                            $inf_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);
                        }
                        //
                        //aggiunta per Protocollo Remoto
                        //
                        $dati_rec = array();
                        $dati_rec['User'] = $_POST[$this->nameForm . '_ProtRemoto']['User'];
                        $inf_rec = array();
                        $inf_rec['ROWID'] = $_POST[$this->nameForm . '_ProtRemoto']['ROWID'];
                        $inf_rec['METAKEY'] = "ParmProtRemoto";
                        $inf_rec['METAVALUE'] = serialize(array(
                            'ProtRemoto' => $dati_rec
                        ));
                        if ($inf_rec['ROWID'] == '') {
                            $inf_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $inf_rec);
                        }

                        //
                        //aggiunto per Codice Utente CityWare
                        //
                        $utenteCityWare_rec = array();
                        $utenteCityWare_rec['Utente'] = $_POST[$this->nameForm . '_CityWare']['Utente'];
                        $this->envLib->setEnvUtemeta('UTE_CITYWARE', $utenteCityWare_rec, $utenti_rec['UTECOD']);
                        //
                        //aggiunta per Firma Remota
                        //
                        $datiFirma_rec = array();
                        $datiFirma_rec['Utente'] = $_POST[$this->nameForm . '_FirmaRemota']['Utente'];
                        $datiFirma_rec['Password'] = $_POST[$this->nameForm . '_FirmaRemota']['Password'];
                        $firma_rec = array();
                        $firma_rec['ROWID'] = $_POST[$this->nameForm . '_FirmaRemota']['ROWID'];
                        $firma_rec['METAKEY'] = "ParmFirmaRemota";
                        $firma_rec['METAVALUE'] = serialize(array(
                            'FirmaRemota' => $datiFirma_rec
                        ));
                        if ($firma_rec['ROWID'] == '') {
                            $firma_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $firma_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $firma_rec);
                        }

                        //
                        //aggiunta per Ente Protocollo
                        //
                        $datiEnteProt_rec = array();
                        $datiEnteProt_rec['URLREMOTO'] = $_POST[$this->nameForm . '_EnteProt']['Ente'];
                        $datiEnteProt_rec['TIPO'] = $_POST[$this->nameForm . '_EnteProt']['Tipo'];
                        $datiEnteProt_rec['DITTA'] = $_POST[$this->nameForm . '_EnteProt']['Ditta'];
                        $datiEnteProt_rec['COLLEGAMENTO'] = $_POST[$this->nameForm . '_EnteProt']['Collegamento'];
                        $enteProt_rec = array();
                        $enteProt_rec['ROWID'] = $_POST[$this->nameForm . '_EnteProt']['ROWID'];
                        $enteProt_rec['METAKEY'] = "ITALSOFTPROTREMOTO";
                        $enteProt_rec['METAVALUE'] = serialize($datiEnteProt_rec);
                        if ($enteProt_rec['ROWID'] == '') {
                            $enteProt_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $enteProt_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $enteProt_rec);
                        }
                        /*
                         * Aggiunta per Profilo Segreteria
                         */
                        $SegrParm_rec = array();
                        $SegrParm_rec = $_POST[$this->nameForm . '_SEGR'];
                        $firma_rec = array();
                        // Segr Abilitata:
                        $abilitasegr_rec['ROWID'] = $SegrParm_rec['ABILITA_ROWID'];
                        $abilitasegr_rec['METAKEY'] = "SEGR_ABILITATI";
                        $abilitasegr_rec['METAVALUE'] = $SegrParm_rec['ABILITA'];
                        if ($abilitasegr_rec['ROWID'] == '') {
                            $abilitasegr_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $abilitasegr_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $abilitasegr_rec);
                        }
                        // Segr Visibilita:
                        $vissegr_rec = array();
                        $vissegr_rec['ROWID'] = $SegrParm_rec['VISIBILITA_ROWID'];
                        $vissegr_rec['METAKEY'] = "VIS_SEGRETERIA";
                        $vissegr_rec['METAVALUE'] = $SegrParm_rec['VISIBILITA'];
                        if ($vissegr_rec['ROWID'] == '') {
                            $vissegr_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $vissegr_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $vissegr_rec);
                        }
                        /*
                         * Aggiunta per Livello Anagrafe
                         */
                        $AnagLiv_rec = array();
                        $AnagLiv_rec = $_POST[$this->nameForm . '_ANAG'];
                        $livelloAnagrafe_rec['ROWID'] = $AnagLiv_rec['LIVELLO_ROWID'];
                        $livelloAnagrafe_rec['METAKEY'] = "LIVELLO_ANAGRAFE";
                        $livelloAnagrafe_rec['METAVALUE'] = $AnagLiv_rec['LIVELLO'];
                        if ($livelloAnagrafe_rec['ROWID'] == '') {
                            $livelloAnagrafe_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $livelloAnagrafe_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $livelloAnagrafe_rec);
                        }
                        /*
                         * Aggiunta per Operatore ZTL
                         */
                        $ZTL_rec = array();
                        $ZTL_rec = $_POST[$this->nameForm . '_ZTL'];
                        $ParmZTL_rec['ROWID'] = $ZTL_rec['PARM_ROWID'];
                        $ParmZTL_rec['METAKEY'] = "ParmZTL";
                        $ParmZTL_rec['METAVALUE'] = serialize(array(
                            'OperatoreZTL' => $ZTL_rec['OPERATORE']
                        ));
                        if ($ParmZTL_rec['ROWID'] == '') {
                            $ParmZTL_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $ParmZTL_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $ParmZTL_rec);
                        }
                        /*
                         * Aggiunta per Operatore ISTAT Incidenti
                         */
                        $rec = array();
                        $rec = $_POST[$this->nameForm . '_GASIN'];
                        $Parm_rec['ROWID'] = $rec['PARM_ROWID'];
                        $Parm_rec['METAKEY'] = "ParmGasin";
                        $Parm_rec['METAVALUE'] = serialize(array(
                            'LivelloOperatore' => $rec['LIVELLO'],
                            'IDRispondente' => $rec['ID_RISPONDENTE']
                        ));
                        if ($Parm_rec['ROWID'] == '') {
                            $Parm_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $Parm_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $Parm_rec);
                        }
                        /*
                         * Aggiunta per Validazione Movimenti CRI
                         */
                        $CRI_rec = array();
                        $CRI_rec = $_POST[$this->nameForm . '_CRI'];
                        $ParmCRI_rec['ROWID'] = $CRI_rec['PARM_ROWID'];
                        $ParmCRI_rec['METAKEY'] = "ParmCRI";
                        $ParmCRI_rec['METAVALUE'] = serialize(array(
                            'ValidazioneCRI' => $CRI_rec['VALIDAZIONE']
                        ));
                        if ($ParmCRI_rec['ROWID'] == '') {
                            $ParmCRI_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $ParmCRI_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $ParmCRI_rec);
                        }
                        /*
                         * Aggiunta per Profilo Fascicoli
                         */
                        $FascicoloParm_rec = array();
                        $FascicoloParm_rec = $_POST[$this->nameForm . '_FASCICOLO'];
                        $firma_rec = array();
                        // Fasicolazione Abilitata:
                        $abilitafascicolo_rec['ROWID'] = $FascicoloParm_rec['ABILITA_ROWID'];
                        $abilitafascicolo_rec['METAKEY'] = "FASCICOLO_ABILITATI";
                        $abilitafascicolo_rec['METAVALUE'] = $FascicoloParm_rec['ABILITA'];
                        if ($abilitafascicolo_rec['ROWID'] == '') {
                            $abilitafascicolo_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $abilitafascicolo_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $abilitafascicolo_rec);
                        }
                        // Fascicolo Visibilita:
                        $visfasicolo_rec = array();
                        $visfasicolo_rec['ROWID'] = $FascicoloParm_rec['VISIBILITA_ROWID'];
                        $visfasicolo_rec['METAKEY'] = "VIS_FASCICOLO";
                        $visfasicolo_rec['METAVALUE'] = $FascicoloParm_rec['VISIBILITA'];
                        if ($visfasicolo_rec['ROWID'] == '') {
                            $visfasicolo_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $visfasicolo_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $visfasicolo_rec);
                        }
                        //
                        //aggiunta max numero notifiche 
                        //
                        $datiNotifiche_rec = array();
                        $datiNotifiche_rec['MaxNumNotifiche'] = $_POST[$this->nameForm . '_Notifiche']['MaxNumNotifiche'];
                        $datiNotifiche_rec['NotMail'] = $_POST[$this->nameForm . '_Notifiche']['NotMail'];
                        $notifiche_rec = array();
                        $notifiche_rec['ROWID'] = $_POST[$this->nameForm . '_Notifiche']['ROWID'];
                        $notifiche_rec['METAKEY'] = "ParmNotifiche";
                        $notifiche_rec['METAVALUE'] = serialize(array(
                            'Notifiche' => $datiNotifiche_rec
                        ));
                        if ($notifiche_rec['ROWID'] == '') {
                            $notifiche_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $notifiche_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $notifiche_rec);
                        }

                        // Protocollo Param Visibilita:
                        $ProtoParam_rec = array();
                        $ProtoParam_rec = $_POST[$this->nameForm . '_PROTO'];
                        $provis_rec = array();
                        $provis_rec['ROWID'] = $ProtoParam_rec['OGGRIS_ROWID'];
                        $provis_rec['METAKEY'] = "PROTO_OGGRIS";
                        $provis_rec['METAVALUE'] = $ProtoParam_rec['OGGRIS_VISIBILITA'];
                        if ($provis_rec['ROWID'] == '') {
                            $provis_rec['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $provis_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $provis_rec);
                        }
                        //
                        //Cambio password
                        //
                        try {
                            $nRows = ItaDB::DBUpdate($this->ITW_DB, 'UTENTI', 'ROWID', $utenti_rec);
                            $sql = "SELECT UTEPAS FROM UTENTI WHERE UTECOD='" . $utenti_rec['UTECOD'] . "'";
                            $utenti_password = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                            if ($utenti_password['UTEPAS'] == '') {
                                $this->cambiaPassword($utenti_rec['UTECOD'], $utenti_rec['UTELOG'], 'gestione');
                            }

//                            $this->OpenRicerca();
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Aggiornamento su ANAGRAFICA Utenti", $e->getMessage());
                        }

                        // Protocollo Param Stampante Zebra:
                        $ProtoParam_rec = array();
                        $ProtoParam_rec = $_POST[$this->nameForm . '_PROTO'];
                        $proeticute = array();
                        $proeticute['ROWID'] = $ProtoParam_rec['ETICUTE_ROWID'];
                        $proeticute['METAKEY'] = "PROTO_ETICUTE";
                        $proeticute['METAVALUE'] = $ProtoParam_rec['ETICUTE_STAMPANTE'];
                        if ($proeticute['ROWID'] == '') {
                            $proeticute['UTECOD'] = $utenti_rec['UTECOD'];
                            $nRows = ItaDB::DBInsert($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $proeticute);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $proeticute);
                        }

                        /*
                         * Aggiornamento Dati CityWare
                         */

                        if ($this->accLib->isSSOCitywareEnabled()) {
                            $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utenti_rec['UTELOG']);
                            if (count($libBorUtenti_rec)) {
                                $libBorUtenti_rec = reset($libBorUtenti_rec);
                            } else {
                                $libBorUtenti_rec = false;
                            }

                            /*
                             * Prendo i record itaEngine
                             */
                            $UtenteIE_rec = array(
                                'UTENTI' => $this->accLib->GetUtenti($utenti_rec['UTECOD']),
                                'RICHUT' => $this->accLib->GetRichut($utenti_rec['UTECOD'])
                            );

                            /*
                             * Trasformo il record in formato CW
                             */
                            $UtenteCW_rec = $this->accLibCityWare->convertUserIE2CW($UtenteIE_rec, 'upper');

                            /*
                             * Leggo i campi aggiuntivi dalla form
                             * e li integro al record finale
                             */
                            $bor_utenti_rec = $_POST[$this->nameForm . '_BOR_UTENTI'];
                            $bor_utefir_rec = $_POST[$this->nameForm . '_BOR_UTEFIR'];
                            $UtenteCW_rec['BOR_UTENTI'] = array_merge($UtenteCW_rec['BOR_UTENTI'], $bor_utenti_rec);
                            $UtenteCW_rec['BOR_UTEFIR'] = array_merge($UtenteCW_rec['BOR_UTEFIR'], $bor_utefir_rec);

                            /*
                             * WIP modifica immagine
                             */
                            $UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'] = $this->dataBorUtefirImmagine;
                            $UtenteCW_rec['BOR_UTEFIR']['WIDTH'] = intval($UtenteCW_rec['BOR_UTEFIR']['WIDTH']);
                            $UtenteCW_rec['BOR_UTEFIR']['HEIGHT'] = intval($UtenteCW_rec['BOR_UTEFIR']['HEIGHT']);

                            /*
                             * additionalInfo per immagine
                             */
                            $UtenteCW_rec['additionalInfo'] = array();
                            $UtenteCW_rec['additionalInfo']['pictureWidth'] = $UtenteCW_rec['BOR_UTEFIR']['WIDTH'];
                            $UtenteCW_rec['additionalInfo']['pictureHeight'] = $UtenteCW_rec['BOR_UTEFIR']['HEIGHT'];

                            if (false !== $libBorUtenti_rec) {
                                $libBorUtenti_rec['BOR_FRMUTE'] = ($libBorUtenti_rec['BOR_FRMUTE'] != false ? $libBorUtenti_rec['BOR_FRMUTE'] : array());

                                $UtenteCW_rec['BOR_FRMUTE'] = array_merge($libBorUtenti_rec['BOR_FRMUTE'], $UtenteCW_rec['BOR_FRMUTE']);
                            }

                            if ($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE'] == false) {
                                unset($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE']);
                            }

                            foreach ($this->dataBorUteliv as $borUteliv) {
                                $borUteliv['CODUTENTE'] = $UtenteCW_rec['BOR_UTENTI']['CODUTE'];
                                unset($borUteliv['DES_LIVELL']);
                                $UtenteCW_rec['BOR_UTELIV'][] = $borUteliv;
                            }

                            foreach ($this->dataBorUtelivDelete as $borUtelivDelete) {
                                $borUtelivDelete['operation'] = itaModelService::OPERATION_DELETE;
                                unset($borUtelivDelete['DES_LIVELL']);
                                $UtenteCW_rec['BOR_UTELIV'][] = $borUtelivDelete;
                            }

                            /*
                             * Password temporanea nel caso debba essere reimpostata
                             * o se ancora non è stato inserito l'utente in CW.
                             */
                            if (
                                !$UtenteCW_rec['BOR_UTENTI']['PWDUTE'] ||
                                $libBorUtenti_rec === false
                            ) {
                                $UtenteCW_rec['BOR_UTENTI']['PWDUTE'] = $this->ssoCitywareTempPassword();
                            } else {
                                /*
                                 * Svuoto la password in quanto potrebbe essere criptata.
                                 * L'aggiornamento di quest'ultima avviene in accPassword.
                                 */
                                unset($UtenteCW_rec['BOR_UTENTI']['PWDUTE']);
                            }

                            if (!$this->ssoCitywareValidate($UtenteCW_rec)) {
                                break;
                            }

                            /*
                             * Ricreo nel formato originale
                             */
                            $libBorUtenti_rec_update = array(
                                $utenti_rec['UTELOG'] => $UtenteCW_rec
                            );

                            $errorMessages = false;

                            if (false !== $libBorUtenti_rec) {
                                $this->libBorUtenti->aggiornaUtente($utenti_rec['UTELOG'], $libBorUtenti_rec_update, $errorMessages);
                            } else {
                                $this->libBorUtenti->inserisciUtente($utenti_rec['UTELOG'], $libBorUtenti_rec_update, $errorMessages);
                            }

                            if ($errorMessages) {
                                Out::msgStop("Errore", $errorMessages);
                                break;
                            }
                        }
                        
                        Out::msgBlock($this->nameForm, 1200, false, 'Utente aggiornato');

                        $this->Dettaglio($utenti_rec['ROWID']);
                        break;

                    /*
                     * Gestione Upload Immagine CityWare
                     */
                    case $this->nameForm . '_BorUtefirImmagineUpload_upld':
                        if ('success' !== $_POST['response']) {
                            Out::msgStop("Errore", "Caricamento file {$_POST['file']} non riuscito.<br>{$_POST['response']}");
                            break;
                        }

                        $sourceFile = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-' . $_POST['file'];
                        include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
                        $imageSize = getimagesize($sourceFile);
                        $this->dataBorUtefirImmagine = file_get_contents($sourceFile);
                        Out::valore($this->nameForm . '_BorUtefirImmagineUpload', $_POST['file']);
                        Out::valore($this->nameForm . '_BOR_UTEFIR[WIDTH]', $imageSize[0]);
                        Out::valore($this->nameForm . '_BOR_UTEFIR[HEIGHT]', $imageSize[1]);
                        Out::attributo($this->nameForm . '_BorUtefirImmagine', 'src', 0, itaImg::base64src($sourceFile));
                        @unlink($sourceFile);
                        break;

                    case $this->nameForm . '_BorUtefirImmagineCancella':
                        $this->dataBorUtefirImmagine = '';
                        Out::valore($this->nameForm . '_BorUtefirImmagineUpload', '');
                        Out::valore($this->nameForm . '_BOR_UTEFIR[WIDTH]', '0');
                        Out::valore($this->nameForm . '_BOR_UTEFIR[HEIGHT]', '0');
                        Out::attributo($this->nameForm . '_BorUtefirImmagine', 'src', 1);
                        break;

                    case $this->nameForm . '_BOR_UTEFIR[PROGSOGG]_butt':
                        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
                        $cwbLib = new cwbLib();
                        $cwbLib->apriFinestraRicerca('cwbBtaSogg', $this->nameForm, 'onClick', $this->nameForm . '_BOR_UTEFIR[PROGSOGG]_return');
                        break;

                    case $this->nameForm . '_BOR_UTEFIR[PROGSOGG]_return':
                        $formData = $this->getFormData();
                        Out::valore($this->nameForm . '_BOR_UTEFIR[PROGSOGG]', $formData['returnData']['PROGSOGG']);
                        Out::valore($this->nameForm . '_PROGSOGG_decod', "{$formData['returnData']['COGNOME']} {$formData['returnData']['NOME']}");
                        break;

                    case $this->nameForm . '_BOR_UTENTI[COD_MAILS]_butt':
                        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
                        $cwbLibDB_BGE = new cwbLibDB_BGE();
                        $BgeMails = $cwbLibDB_BGE->leggiBgeMails(array());

                        $model = 'utiRicDiag';
                        $gridOptions = array(
                            "Caption" => "Server di posta",
                            "width" => '420',
                            "height" => '200',
                            "rowNum" => '20',
                            "rowList" => '[]',
                            "arrayTable" => $BgeMails,
                            "colNames" => array(
                                "Server SMTP",
                                "Porta SMTP",
                                "Server POP3",
                                "Porta POP3"
                            ),
                            "colModel" => array(
                                array("name" => 'SMTP_SERV', "width" => 160),
                                array("name" => 'SMTP_PORT', "width" => 40),
                                array("name" => 'POP3_SERV', "width" => 160),
                                array("name" => 'POP3_PORT', "width" => 40),
                            ),
                            "pgbuttons" => 'false',
                            "pginput" => 'false'
                        );

                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['gridOptions'] = $gridOptions;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = $this->nameForm . '_BOR_UTENTI[COD_MAILS]_return';
                        $_POST['returnKey'] = 'retKey';
                        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_borUtelivConferma':
                        $borUteliv = $_POST[$this->nameForm . '_BOR_UTELIV'];

                        if (!$borUteliv['DATAINIZ']) {
                            Out::msgStop('Errore', 'Data Inizio obbligatoria.');
                            break;
                        }

                        if (isset($_POST[$this->nameForm . '_BOR_UTELIV_IDX'])) {
                            $this->dataBorUteliv[intval($_POST[$this->nameForm . '_BOR_UTELIV_IDX'])] = array_merge($this->dataBorUteliv[intval($_POST[$this->nameForm . '_BOR_UTELIV_IDX'])], $borUteliv);
                        } else {
                            $this->dataBorUteliv[] = $borUteliv;
                        }

                        Out::closeCurrentDialog();
                        TableView::reload($this->gridBorUteliv);
                        break;

                    case $this->nameForm . '_borUtelivAnnulla':
                        Out::closeCurrentDialog();
                        break;

                    case $this->nameForm . '_Cancella':
                        if ($this->accLib->isSSOCitywareEnabled()) {
                            $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                            $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utenti_rec['UTELOG']);
                            if (count($libBorUtenti_rec)) {
                                /*
                                 * Disabilitato
                                 */
                                break;
                            }
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];

                        if ($this->accLib->isSSOCitywareEnabled()) {
                            $libBorUtenti_rec = $this->libBorUtenti->leggiUtenti($utenti_rec['UTELOG']);
                            if (count($libBorUtenti_rec)) {
                                /*
                                 * Disabilitato
                                 */
                                break;
                            }
                        }

                        try {
                            $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTENTI', 'ROWID', $utenti_rec['ROWID']);
                            //Out::msgInfo("Cancellato", "Cancellate:$nRows Righe");
                            $this->OpenRicerca();
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA UTENTI", $e->getMessage());
                            break;
                        }
                        
                        /*
                         * Aggiunta per Paleo
                         */
                        $operatore_rec = $_POST[$this->nameForm . '_OperatorePaleo'];

                        if ($operatore_rec['ROWID'] != '') {
                            try {
                                $nRows = ItaDB::DBDelete($this->ITALWEB, 'ENV_UTEMETA', 'ROWID', $operatore_rec['ROWID']);
                                //Out::msgInfo("Cancellato", "Cancellate:$nRows Righe");
                                $this->OpenRicerca();
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Cancellazione su ANAGRAFICA UTENTI", $e->getMessage());
                                break;
                            }
                        }

                        /*
                         * Controllo presenza RICHUT
                         */

                        $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
                        if ($richut_rec) {
                            /*
                             * Eliminazione dati su RICHUT (richieste utenti)
                             * Carlo, 27.01.2015
                             */
                            if (!ItaDB::DBDelete($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec['ROWID'])) {
                                Out::msgStop("Errore", "Errore nell'eliminazione del record utente su RICHUT");
                                break;
                            }
                        }
                        break;

                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltroUtente');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridRisultato);
                        TableView::reload($this->gridRisultato);
                        break;
                    case $this->nameForm . '_Password':
                        Out::msgQuestion("Annulla Password", "Confermi l'annullamento della Password?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPassword', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnnullaPassword', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAnnullaPassword':
                        $sql = "SELECT * FROM UTENTI WHERE UTECOD='" . $_POST[$this->nameForm . '_UTENTI']['UTECOD'] . "'";
                        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                        if ($utenti_rec) {
                            $this->cambiaPassword($utenti_rec['UTECOD'], $utenti_rec['UTELOG'], 'reset');
                        }
                        //
                        //                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        //                        $utenti_rec['UTEUPA'] = date("Ymd");
                        //                        $utenti_rec['UTESPA'] = date("Ymd");
                        //                        try {
                        //                            $nRows = ItaDB::DBUpdate($this->ITW_DB, 'UTENTI', 'ROWID', $utenti_rec);
                        //                            Out::msgInfo("Aggiornato", "Aggiornate:$nRows Righe");
                        //                            $this->OpenRicerca();
                        //                        } catch (Exception $e) {
                        //                            Out::msgStop("Errore in Aggiornamento su ANAGRAFICA Utenti", $e->getMessage());
                        //                        }
                        break;
                    case $this->nameForm . '_EditDipendente':
                        $datiUtente = array(
                            "COGNOME" => $_POST[$this->nameForm . '_RICHUT']['RICCOG'],
                            "NOME" => $_POST[$this->nameForm . '_RICHUT']['RICNOM'],
                            "MAIL" => $_POST[$this->nameForm . '_RICHUT']['RICMAI']
                        );
                        $codiceResponsabile = $_POST[$this->nameForm . '_UTENTI']['UTEANA__3'];
                        $model = 'praDipe';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['openMode'] = "editFromUtenti";
                        $_POST['datiUtente'] = $datiUtente;
                        $_POST['NOMRES'] = $codiceResponsabile;
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();

                        break;

                    case $this->nameForm . '_UTENTI[UTEANN__8]_butt':
                        timRic::timRicDip($this->nameForm, "returnDipe");
                        break;

                    case $this->nameForm . '_CambiaFiscale':
                        $fields = array(array(
                                'label' => array(
                                    'value' => 'Codice fiscale',
                                    'style' => 'margin-top: 12px;'
                                ),
                                'id' => $this->nameForm . '_CODICEFISCALE',
                                'name' => $this->nameForm . '_CODICEFISCALE',
                                'size' => 20,
                                'maxlength' => 16,
                                'type' => 'text',
                                'class' => 'ita-edit-uppercase required',
                                'style' => 'margin: 12px 0 0 10px;',
                                'value' => $_POST[$this->nameForm . '_UTENTI']['UTEFIS']
                            )
                        );

                        $msgText = '<b>ATTENZIONE</b><br><br>La modifica del codice fiscale può comportare l\'alterazione<br>di alcuni comportamenti operativi. Procedere con cautela.';

                        Out::msgInput('Modifica del codice fiscale', $fields, array(
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCodiceFiscale',
                                'model' => $this->nameForm,
                                'class' => 'ita-button-validate',
                                'shortCut' => "f5"
                            )), $this->nameForm . '_workSpace', 'auto', 'auto', true, $msgText
                        );
                        break;

                    case $this->nameForm . '_ConfermaCodiceFiscale':
                        $codiceFiscale = $_POST[$this->nameForm . '_CODICEFISCALE'];

                        if ($this->accLib->isSSOCitywareEnabled()) {
                            /*
                             * Se è Cityware non effettuo i controlli strict
                             */
                            Out::closeCurrentDialog();
                            Out::valore($this->nameForm . '_UTENTI[UTEFIS]', $codiceFiscale);

                            if (strlen($codiceFiscale) !== 16 || !preg_match('/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/', $codiceFiscale)) {
                                Out::msgInfo('Attenzione', 'Il codice fiscale inserito non è conforme.');
                            }
                            break;
                        }

                        if (strlen($codiceFiscale) !== 16 || !preg_match('/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/', $codiceFiscale)) {
                            Out::msgStop('Errore', 'Codice fiscale non valido');
                            break;
                        }

                        if ($this->accLib->GetUtenti($codiceFiscale, 'codicefiscale')) {
                            Out::msgStop('Errore', 'Codice fiscale già presente');
                            break;
                        }

                        Out::valore($this->nameForm . '_UTENTI[UTEFIS]', $codiceFiscale);
                        Out::closeCurrentDialog();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case $this->nameForm . '_BOR_UTENTI[COD_MAILS]_return':
                Out::valore($this->nameForm . '_BOR_UTENTI[COD_MAILS]', $_POST['rowData']['PROGRECORD']);
                Out::valore($this->nameForm . '_COD_MAILS_decod', $_POST['rowData']['SMTP_SERV']);
                break;

            case 'returnAccPassword':
                $utecod = $_POST['returnUtecod'];
                if (!$utecod) {
                    break;
                }

                $utenti_rec = $this->accLib->GetUtenti($utecod);
                if ($utenti_rec) {
                    $this->Dettaglio($utenti_rec['ROWID']);
                } else {
                    $this->OpenRicerca();
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_UTEFIR[PROGSOGG]':
                        $this->decodeBorUtefirProgsogg($_POST[$this->nameForm . '_BOR_UTEFIR']['PROGSOGG']);
                        break;

                    case $this->nameForm . '_BOR_UTENTI[COD_MAILS]':
                        $this->decodeBorUtentiCodmails($_POST[$this->nameForm . '_BOR_UTENTI']['COD_MAILS']);
                        break;

                    case $this->nameForm . '_UTENTI[UTEGRU]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGRU'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru1', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru1', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__1]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__1'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru2', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru2', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__2]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__2'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru3', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru3', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__3]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__3'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru4', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru4', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__4]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__4'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru5', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru5', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__5]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__5'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru6', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru6', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__6]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__6'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru7', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru7', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__7]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__7'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru8', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru8', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__8]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__8'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru9', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru9', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEGEX__9]':
                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $codice = $utenti_rec['UTEGEX__9'];
                        if ($codice != '') {
                            $Anagru_tab = $this->GetGruppo($codice);
                            if (count($Anagru_tab) == 1) {
                                Out::valore($this->nameForm . '_Desgru10', $Anagru_tab[0]['GRUDES']);
                            } else {
                                Out::valore($_POST['id'], '');
                                Out::valore($this->nameForm . '_Desgru10', 'Gruppo non valido');
                                Out::setFocus('', $_POST['id']);
                            }
                        }
                        break;
                    case $this->nameForm . '_UTENTI[UTEANA__1]':
                        $codice = $_POST[$this->nameForm . '_UTENTI']['UTEANA__1'];
                        if (is_numeric($codice)) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        }
                        $Anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'si');
                        Out::valore($this->nameForm . '_UTENTI[UTEANA__1]', $Anamed_rec['MEDCOD']);
                        Out::valore($this->nameForm . '_Mednom', $Anamed_rec['MEDNOM']);
                        break;
                    case $this->nameForm . '_Utente':
                        $sql = "SELECT * FROM UTENTI WHERE UTELOG='" . addslashes($_POST[$this->nameForm . '_Utente']) . "'";
                        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                        if ($utenti_rec) {
                            $this->Dettaglio($utenti_rec['ROWID']);
                        }
                        break;

                    case $this->nameForm . '_CodiceFiscale':
                        if ($_POST[$this->nameForm . '_CodiceFiscale']) {
                            $sql = "SELECT ROWID FROM UTENTI WHERE " . $this->ITW_DB->strUpper('UTEFIS') . " = " . $this->ITW_DB->strUpper("'" . addslashes($_POST[$this->nameForm . '_CodiceFiscale']) . "'") . "";
                            $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                            if ($utenti_rec) {
                                $this->Dettaglio($utenti_rec['ROWID']);
                            }
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_dataBorUteliv');
        App::$utente->removeKey($this->nameForm . '_dataBorLivelli');
        App::$utente->removeKey($this->nameForm . '_dataBorUtelivDelete');
        App::$utente->removeKey($this->nameForm . '_dataBorUtefirImmagine');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::valore($this->nameForm . '_Utente', '');
        Out::valore($this->nameForm . '_Gruppo', '');
        Out::hide($this->divRis);
        Out::show($this->divRic);
        Out::hide($this->divGes);
        TableView::disableEvents($this->gridRisultato);
        TableView::clearGrid($this->gridRisultato);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '');
        Out::setFocus('', $this->nameForm . '_Utente');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        //  Out::hide($this->nameForm.'_Password');
        Out::hide($this->nameForm . '_AltroUtente');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Duplica');
        Out::tabDisable($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabProtocollo");
    }

    public function CreaSql() {
        $where = "";
        // Importo l'ordinamento del filtro
        $sql = "SELECT  UTENTI.ROWID AS ROWID,
                UTENTI.UTECOD AS UTECOD,
                UTENTI.UTELOG AS UTELOG,
                GRUPPI.GRUDES AS GRUDES,
                RICHUT.RICCOG AS RICCOG,
                RICHUT.RICNOM AS RICNOM,
                UTENTI.UTEUPA AS UTEUPA,
                UTENTI.UTEDPA AS UTEDPA,
                UTENTI.UTESPA AS UTESPA,
                UTENTI.UTEFIL__1 AS UTEFIL__1,
                UTENTI.UTEFIL__2 AS UTEFIL__2,
                UTENTI.UTEFIA__1 AS UTEFIA__1,
                UTENTI.UTEFIS AS UTEFIS
                FROM UTENTI 
                LEFT OUTER JOIN GRUPPI ON UTENTI.UTEGRU=GRUPPI.GRUCOD
                LEFT OUTER JOIN RICHUT ON UTENTI.UTECOD=RICHUT.RICCOD";

        //        if (count($this->ExtGru) != 0) {
        //            if ($_POST[$this->nameForm . '_TipoUte'] == 'I') {
        //                $where .= "NOT (GRUCOD IN(" . implode(",", $this->ExtGru) . "))";
        //            } else {
        //                $where .= "GRUCOD IN(" . implode(",", $this->ExtGru) . ")";
        //            }
        //        }

        if ($_POST[$this->nameForm . '_Utente'] != "") {
            $where .= " AND " . $this->ITW_DB->strUpper('UTELOG') . " LIKE " . $this->ITW_DB->strUpper("'%" . addslashes($_POST[$this->nameForm . '_Utente']) . "%'") . "";
        }
        if ($_POST[$this->nameForm . '_CodiceFiscale'] != "") {
            $where .= " AND " . $this->ITW_DB->strUpper('UTEFIS') . " LIKE " . $this->ITW_DB->strUpper("'%" . addslashes($_POST[$this->nameForm . '_CodiceFiscale']) . "%'") . "";
        }
        if ($_POST[$this->nameForm . '_Gruppo'] != "") {
            $Gruppo = $_POST[$this->nameForm . '_Gruppo'];
            $where .= " AND ( UTEGRU = '$Gruppo' "
                    . " OR UTEGEX__1 = '$Gruppo' OR UTEGEX__2 = '$Gruppo' "
                    . " OR UTEGEX__3 = '$Gruppo' OR UTEGEX__4 = '$Gruppo' "
                    . " OR UTEGEX__5 = '$Gruppo' OR UTEGEX__6 = '$Gruppo' "
                    . " OR UTEGEX__7 = '$Gruppo' OR UTEGEX__8 = '$Gruppo' "
                    . " OR UTEGEX__9 = '$Gruppo' OR UTEGEX__10 = '$Gruppo' "
                    . "  )";
        }
        if ($where != '')
            $sql .= ' WHERE 1 ' . $where;
//        App::log($sql);

        /*
         * Aggiunto "group by" per problema con record doppi
         */
        $sql .= " GROUP BY UTECOD";

        return $sql;
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "", "1", "");
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "tls", "0", "tls");
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "ssl", "0", "ssl");
        Out::select($this->nameForm . '_UTENTI[UTEANA__4]', 1, "", "1", "Arrivo/Partenza");
        Out::select($this->nameForm . '_UTENTI[UTEANA__4]', 1, "1", "0", "Arrivo");
        Out::select($this->nameForm . '_UTENTI[UTEANA__4]', 1, "2", "0", "Partenza");
        Out::select($this->nameForm . '_UTENTI[UTEANA__4]', 1, "3", "0", "Nega");
        Out::select($this->nameForm . '_UTENTI[UTEANA__5]', 1, "", "1", "Solo per ufficio");
        Out::select($this->nameForm . '_UTENTI[UTEANA__5]', 1, "1", "0", "Tutti");
        Out::select($this->nameForm . '_UTENTI[UTEANA__5]', 1, "2", "2", "Tutti-senza riservato");
        Out::hide($this->nameForm . '_UTENTI[UTEANA__5]_field');
        $orgLayout = proOrgLayout::getInstance($this->proLib);
        $sel = "1";
        foreach ($orgLayout->getLayout() as $orgNode) {
            Out::select($this->nameForm . '_UTENTI[UTEANA__10]', 1, $orgNode, $sel, $orgNode);
            // @TODO Prevedere un segr layout ? 
            if ($orgNode == 'SETTORE') {
                continue;
            }
            Out::select($this->nameForm . '_SEGR[VISIBILITA]', 1, $orgNode, $sel, $orgNode);
            $sel = "0";
        }

        $sel = "1";
        foreach ($orgLayout->getLayout() as $orgNode) {
            Out::select($this->nameForm . '_FASCICOLO[VISIBILITA]', 1, $orgNode, $sel, $orgNode);
            if ($orgNode == 'SETTORE') {
                continue;
            }
            $sel = "0";
        }

        Out::select($this->nameForm . '_UTENTI[UTEFIL__3]', 1, "1", "1", "1");
        Out::select($this->nameForm . '_UTENTI[UTEFIL__3]', 1, "2", "0", "2");
        Out::select($this->nameForm . '_UTENTI[UTEFIL__3]', 1, "3", "0", "3");

        Out::select($this->nameForm . '_UTENTI[UTEANA__6]', 1, "", "1", "Disabilita blocco");
        Out::select($this->nameForm . '_UTENTI[UTEANA__6]', 1, "1", "0", "Arrivo e Partenza");
        Out::select($this->nameForm . '_UTENTI[UTEANA__6]', 1, "2", "0", "Arrivo");
        Out::select($this->nameForm . '_UTENTI[UTEANA__6]', 1, "3", "0", "Partenza");

        Out::select($this->nameForm . '_UTENTI[UTEDIS]', 1, "", "0", "Scegli Dispositivo");
        Out::select($this->nameForm . '_UTENTI[UTEDIS]', 1, "1", $sel1, "Palmare");
        Out::select($this->nameForm . '_UTENTI[UTEDIS]', 1, "2", $sel2, "Minicomputer");

        Out::select($this->nameForm . '_SEGR[ABILITA]', 1, "", "0", "Abilita");
        Out::select($this->nameForm . '_SEGR[ABILITA]', 1, "2", "0", "Nega");

        Out::select($this->nameForm . '_FASCICOLO[ABILITA]', 1, "", "1", "Consultazione");
        Out::select($this->nameForm . '_FASCICOLO[ABILITA]', 1, "1", "0", "Archivistica");
        Out::select($this->nameForm . '_FASCICOLO[ABILITA]', 1, "2", "0", "Completa");
        Out::select($this->nameForm . '_FASCICOLO[ABILITA]', 1, "3", "0", "Movimentazione");

        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Manuale", "1", "Manuale");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Italsoft", "1", "Italsoft");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Italsoft-remoto", "1", "Italsoft Remoto");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Italsoft-remoto-allegati", "1", "Italsoft Remoto + Allegati");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Italsoft-ws", "1", "Italsoft Web Service");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Paleo", "0", "Paleo");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "WSPU", "0", "WSPU");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Infor", "0", "Infor jProtocollo");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Iride", "0", "Iride");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "Jiride", "0", "Jiride");
        Out::select($this->nameForm . '_EnteProt[Tipo]', 1, "HyperSIC", "0", "HyperSIC");

        Out::select($this->nameForm . '_ANAG[LIVELLO]', 1, "1", "0", "Livello Base");
        Out::select($this->nameForm . '_ANAG[LIVELLO]', 1, "2", "0", "Livello Intermedio");
        Out::select($this->nameForm . '_ANAG[LIVELLO]', 1, "3", "0", "Livello Completo");

        Out::select($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 1, "0", "0", "Come da impostazioni applicativo");
        Out::select($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 1, "1", "0", "Da utente");
        Out::select($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 1, "2", "0", "Da ruolo");
    }

    public function Dettaglio($indice) {
        $risultato = $this->envLib->getAttivazioneMail();  // controllo verifica se bisogna vedere la parte gestione flag Notifiche via Mail
        if ($risultato == '1') {
            Out::show($this->nameForm . '_Notifiche[NotMail]_lbl');
            Out::show($this->nameForm . '_Notifiche[NotMail]');
        } else {
            Out::hide($this->nameForm . '_Notifiche[NotMail]_lbl');
            Out::hide($this->nameForm . '_Notifiche[NotMail]');
        }

        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE ROWID='$indice'", false);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ProtRemoto[ROWID]', "");
        Out::clearFields($this->nameForm, $this->nameForm . '_divAppoggio');
        $this->Nascondi();
        Out::valori($utenti_rec, $this->nameForm . '_UTENTI');
        Out::valore($this->nameForm . '_CODFIS', $utenti_rec['UTEFIS']);
        $this->decodeFlagScadenza($utenti_rec['UTEDPA'] == '9999');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_AltroUtente');
        Out::show($this->nameForm . '_Duplica');
        Out::show($this->nameForm . '_Password');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        Out::attributo($this->nameForm . '_UTENTI[UTECOD]', 'readonly', '0');
        Out::tabSelect($this->nameForm . "_tabUtente", $this->nameForm . "_tabGenerale");
        Out::disableField($this->nameForm . '_UTENTI[UTELOG]');
        Out::setFocus('', $this->nameForm . '_RICHUT[RICCOG]');
        Out::attributo($this->nameForm . "_FlagInterno", "checked", "0", "checked");
        if ($utenti_rec['UTEPAS'] != '') {
            Out::valore($this->nameForm . '_Utepas', 'Password Presente');
            if ($utenti_rec['UTESPA'] <= date("Ymd")) {
                $flagResetPassword = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'FlagResetPassword');

                if ($flagResetPassword) {
                    Out::valore($this->nameForm . '_Utepas', 'Password Annullata');
                }

                if ($utenti_rec['UTEDPA'] !== '9999') {
                    if ($flagResetPassword) {
                        Out::valore($this->nameForm . '_Utepas', 'Password Annullata');
                    } else {
                        Out::valore($this->nameForm . '_Utepas', 'Password Scaduta');
                    }
                }
            }
        } else {
            Out::valore($this->nameForm . '_Utepas', 'Password NON Presente');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGRU']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru1', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru1', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__1']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru2', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru2', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__2']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru3', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru3', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__3']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru4', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru4', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__4']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru5', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru5', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__5']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru6', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru6', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__6']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru7', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru7', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__7']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru8', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru8', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__8']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru9', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru9', '');
        }
        $Anagru_tab = $this->GetGruppo($utenti_rec['UTEGEX__9']);
        if (count($Anagru_tab) == 1) {
            Out::valore($this->nameForm . '_Desgru10', $Anagru_tab[0]['GRUDES']);
        } else {
            Out::valore($this->nameForm . '_Desgru10', '');
        }
        $Anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1'], 'codice', 'si');
        Out::valore($this->nameForm . '_Mednom', $Anamed_rec['MEDNOM']);
        $Ananom_rec = $this->praLib->GetAnanom($utenti_rec['UTEANA__3'], 'codice');
        Out::valore($this->nameForm . '_DecodDipendente', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);



        //  CERCO LE WORD PARAMETRIZZATE PER L'UTENTE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::valore($this->nameForm . '_WP_' . $j, '');
        }
        TableView::disableEvents($this->gridRisultato);

        //CERCO SU RICHUT
        $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
        Out::valori($richut_rec, $this->nameForm . '_RICHUT');


        //CERCO SU ENV_UTEMETA
        $op_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmPaleo');
        if ($op_rec) {
            $meta = unserialize($op_rec['METAVALUE']);
            Out::valore($this->nameForm . '_OperatorePaleo[ROWID]', $op_rec['ROWID']);
            Out::valori($meta['OperatorePaleo'], $this->nameForm . '_OperatorePaleo');
        }

        $inf_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', "ParmInfor");
        if ($inf_rec) {
            $meta = unserialize($inf_rec['METAVALUE']);
            Out::valore($this->nameForm . '_Infor[ROWID]', $inf_rec['ROWID']);
            Out::valori($meta['DatiInfor'], $this->nameForm . '_Infor');
        }

        $rem_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', "ParmProtRemoto");
        if ($rem_rec) {
            $meta = unserialize($rem_rec['METAVALUE']);
            Out::valore($this->nameForm . '_ProtRemoto[ROWID]', $rem_rec['ROWID']);
            Out::valori($meta['ProtRemoto'], $this->nameForm . '_ProtRemoto');
        }

        $firmaRec_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmFirmaRemota');
        if ($firmaRec_rec) {
            $meta = unserialize($firmaRec_rec['METAVALUE']);
            Out::valore($this->nameForm . '_FirmaRemota[ROWID]', $firmaRec_rec['ROWID']);
            Out::valori($meta['FirmaRemota'], $this->nameForm . '_FirmaRemota');
        }

        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            Out::valore($this->nameForm . '_EnteProt[ROWID]', $enteProtRec_rec['ROWID']);
            Out::valore($this->nameForm . '_EnteProt[Ente]', $meta['URLREMOTO']);
            Out::valore($this->nameForm . '_EnteProt[Tipo]', $meta['TIPO']);
            Out::valore($this->nameForm . '_EnteProt[Ditta]', $meta['DITTA']);
            Out::valore($this->nameForm . '_EnteProt[Collegamento]', $meta['COLLEGAMENTO']);
        }

        $paramNotifiche_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmNotifiche');
        if ($paramNotifiche_rec) {
            $meta = unserialize($paramNotifiche_rec['METAVALUE']);
            Out::valore($this->nameForm . '_Notifiche[ROWID]', $paramNotifiche_rec['ROWID']);
            Out::valori($meta['Notifiche'], $this->nameForm . '_Notifiche');
        }

        $abilitasegr_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'SEGR_ABILITATI');
        if ($abilitasegr_rec) {
            Out::valore($this->nameForm . '_SEGR[ABILITA_ROWID]', $abilitasegr_rec['ROWID']);
            Out::valore($this->nameForm . '_SEGR[ABILITA]', $abilitasegr_rec['METAVALUE']);
        }

        $livelloanagrafe_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'LIVELLO_ANAGRAFE');
        if ($livelloanagrafe_rec) {
            Out::valore($this->nameForm . '_ANAG[LIVELLO_ROWID]', $livelloanagrafe_rec['ROWID']);
            Out::valore($this->nameForm . '_ANAG[LIVELLO]', $livelloanagrafe_rec['METAVALUE']);
        }

        $vissegr_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'VIS_SEGRETERIA');
        if ($vissegr_rec) {
            Out::valore($this->nameForm . '_SEGR[VISIBILITA_ROWID]', $vissegr_rec['ROWID']);
            Out::valore($this->nameForm . '_SEGR[VISIBILITA]', $vissegr_rec['METAVALUE']);
        }

        $ztl_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmZTL');
        if ($ztl_rec) {
            $meta = unserialize($ztl_rec['METAVALUE']);
            Out::valore($this->nameForm . '_ZTL[PARM_ROWID]', $ztl_rec['ROWID']);
            Out::valore($this->nameForm . '_ZTL[OPERATORE]', $meta['OperatoreZTL']);
        }
        $cri_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmCRI');
        if ($cri_rec) {
            $meta = unserialize($cri_rec['METAVALUE']);
            Out::valore($this->nameForm . '_CRI[PARM_ROWID]', $cri_rec['ROWID']);
            Out::valore($this->nameForm . '_CRI[VALIDAZIONE]', $meta['ValidazioneCRI']);
        }

        $gasin_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmGasin');
        if ($gasin_rec) {
            $meta = unserialize($gasin_rec['METAVALUE']);
            Out::valore($this->nameForm . '_GASIN[PARM_ROWID]', $ztl_rec['ROWID']);
            Out::valore($this->nameForm . '_GASIN[LIVELLO]', $meta['LivelloOperatore']);
            Out::valore($this->nameForm . '_GASIN[ID_RISPONDENTE]', $meta['IDRispondente']);
        }


//        $fascicolo_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmFascicolo');
//        if ($fascicolo_rec) {
//            $meta = unserialize($fascicolo_rec['METAVALUE']);
//            Out::valore($this->nameForm . '_ParmFascicolo[ROWID]', $fascicolo_rec['ROWID']);
//            Out::valori($meta['ParmFascicolo'], $this->nameForm . '_ParmFascicolo');
//        }

        $abilitafascicolo_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'FASCICOLO_ABILITATI');
        if ($abilitafascicolo_rec) {
            Out::valore($this->nameForm . '_FASCICOLO[ABILITA_ROWID]', $abilitafascicolo_rec['ROWID']);
            Out::valore($this->nameForm . '_FASCICOLO[ABILITA]', $abilitafascicolo_rec['METAVALUE']);
        }

        $visfascicolo_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'VIS_FASCICOLO');
        if ($visfascicolo_rec) {
            Out::valore($this->nameForm . '_FASCICOLO[VISIBILITA_ROWID]', $visfascicolo_rec['ROWID']);
            Out::valore($this->nameForm . '_FASCICOLO[VISIBILITA]', $visfascicolo_rec['METAVALUE']);
        }

        $utenteCityware_rec = $this->envLib->GetEnvUtemeta('UTE_CITYWARE', $utenti_rec['UTECOD']);
        if ($utenteCityware_rec) {
            Out::valori($utenteCityware_rec, $this->nameForm . '_CityWare');
        }


        $proto_oggris = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'PROTO_OGGRIS');
        if ($proto_oggris) {
            Out::valore($this->nameForm . '_PROTO[OGGRIS_ROWID]', $proto_oggris['ROWID']);
            Out::valore($this->nameForm . '_PROTO[OGGRIS_VISIBILITA]', $proto_oggris['METAVALUE']);
        }
        /*
         * Dati stampante etichetta protocollo.
         */
        $proto_eticute = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'PROTO_ETICUTE');
        if ($proto_oggris) {
            Out::valore($this->nameForm . '_PROTO[ETICUTE_ROWID]', $proto_eticute['ROWID']);
            Out::valore($this->nameForm . '_PROTO[ETICUTE_STAMPANTE]', $proto_eticute['METAVALUE']);
        }

        $this->caricaOggetti($utenti_rec['UTELOG']);
        Out::tabEnable($this->nameForm . "_divTabApplicativi", $this->nameForm . "_tabProtocollo");

        /*
         * Caricamento Dati CityWare
         */

        $this->ssoCitywareFields($utenti_rec);
    }

    private function loadBorLivelli() {
        $this->dataBorLivelli = array();
        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
        $cwbLibDB_BOR = new cwbLibDB_BOR();
        $borLivelli_tab = $cwbLibDB_BOR->leggiBorLivell(array());

        foreach ($borLivelli_tab as $borLivelli_rec) {
            $this->dataBorLivelli[$borLivelli_rec['IDLIVELL']] = $borLivelli_rec;
        }
    }

    private function dettaglioBorUteliv($idx = false) {
        $borUtelivOptions = array();

        if ($idx !== false) {
            $currBorUteliv = $this->dataBorUteliv[$idx];
        }

        $usedIdlivell = array_map(function($v) {
            return $v['IDLIVELL'];
        }, $this->dataBorUteliv);

        foreach ($this->dataBorLivelli as $borLivelli_rec) {
            if ($idx !== false && $currBorUteliv['IDLIVELL'] === $borLivelli_rec['IDLIVELL']) {
                $borUtelivOptions[] = array($borLivelli_rec['IDLIVELL'], $borLivelli_rec['DES_LIVELL'], true);
                continue;
            }

            if (in_array($borLivelli_rec['IDLIVELL'], $usedIdlivell)) {
                continue;
            }

            $borUtelivOptions[] = array($borLivelli_rec['IDLIVELL'], $borLivelli_rec['DES_LIVELL']);
        }

        if (!count($borUtelivOptions)) {
            Out::msgStop("Errore", "Nessun livello da inserire.");
            return false;
        }

        $dettaglioFields = array(array(
                'label' => array(
                    'value' => "Descrizione",
                    'style' => 'width: 80px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_BOR_UTELIV[IDLIVELL]',
                'name' => $this->nameForm . '_BOR_UTELIV[IDLIVELL]',
                'class' => 'required',
                'type' => 'select',
                'options' => $borUtelivOptions,
                'style' => 'margin-left: 5px;'
            ), array(
                'label' => array(
                    'value' => "Validità",
                    'style' => 'width: 80px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_BOR_UTELIV[FLAG_VALID]',
                'name' => $this->nameForm . '_BOR_UTELIV[FLAG_VALID]',
                'type' => 'checkbox',
                'style' => 'margin-left: 5px;'
            ), array(
                'label' => array(
                    'value' => "Data Inizio",
                    'style' => 'width: 80px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_BOR_UTELIV[DATAINIZ]',
                'name' => $this->nameForm . '_BOR_UTELIV[DATAINIZ]',
                'class' => 'required ita-datepicker',
                'type' => 'text',
                'size' => '12',
                'style' => 'margin-left: 5px;'
            ), array(
                'label' => array(
                    'value' => "Data Fine",
                    'style' => 'width: 80px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_BOR_UTELIV[DATAFINE]',
                'name' => $this->nameForm . '_BOR_UTELIV[DATAFINE]',
                'class' => 'ita-datepicker',
                'type' => 'text',
                'size' => '12',
                'style' => 'margin-left: 5px;'
            )
        );

        $labelOK = 'Inserisci';
        if ($idx !== false) {
            $labelOK = 'Aggiorna';

            if ($currBorUteliv['FLAG_VALID']) {
                $dettaglioFields[1]['checked'] = 1;
            }

            $dettaglioFields[2]['value'] = $currBorUteliv['DATAINIZ'];
            $dettaglioFields[3]['value'] = $currBorUteliv['DATAFINE'];
            $dettaglioFields[] = array(
                'id' => $this->nameForm . '_BOR_UTELIV_IDX',
                'name' => $this->nameForm . '_BOR_UTELIV_IDX',
                'value' => $idx,
                'type' => 'hidden'
            );
        }

        Out::msgInput('Livello', $dettaglioFields, array(
            "F5 - $labelOK" => array(
                'id' => $this->nameForm . '_borUtelivConferma',
                'model' => $this->nameForm,
                'shortCut' => 'f5'
            ),
            'F8 - Annulla' => array(
                'id' => $this->nameForm . '_borUtelivAnnulla',
                'model' => $this->nameForm,
                'shortCut' => 'f8'
            )
                ), $this->nameForm, 'auto', 'auto', true, '', '', false);
    }

    function GetGruppo($_Codice) {
        $sql = "SELECT GRUCOD, GRUDES FROM GRUPPI WHERE GRUCOD='$_Codice'";
        $Gruppo_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        return $Gruppo_tab;
    }

    function SetExtGru() {
        $Tabpar_tab = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM TABPAR WHERE TABCOD IN (10,20,21,22,44,45,52)");
        foreach ($Tabpar_tab as $Tabpar_rec) {
            if ($Tabpar_rec['TABPAR'] != '') {
                $this->ExtGru[] = $Tabpar_rec['TABPAR'];
            }
        }
    }

    function SetParword() {
        //  VALORIZZO LE 10 SELECT CON LA PRIMA RIGA DI NON SELEZIONE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::select($this->nameForm . '_WP_' . $j, 1, '', '', '');
        }
        for ($db = 1; $db <= 99; $db++) {
            $db = str_repeat("0", 2 - strlen(trim($db))) . trim($db);
            $db_test = 'WP' . $db;
            try { // Apro il DB
                $ce_word = 1;
                $this->WORD_DB = ItaDB::DBOpen($db_test);
                if (!$this->WORD_DB) {
                    $ce_word = 0;
                }
            } catch (Exception $e) {
                $ce_word = 0;
            }
            if ($ce_word == 1) {
                $sql = "SELECT * FROM AUSILI WHERE ROWID = 5";
                $word_rec = ItaDB::DBSQLSelect($this->WORD_DB, $sql, false);
                $des_word = $word_rec['LOC'];
                if ($des_word == '')
                    $des_word .= 'WP DEL SERVIZIO ' . $db;
                for ($j = 1; $j <= 10; $j++) {
                    $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                    Out::select($this->nameForm . '_WP_' . $j, 1, $db_test, $sel, $db_test . ' ' . $des_word);
                }
            }
        }
    }

    function SetParliq() {
        //  VALORIZZO LE 10 SELECT CON LA PRIMA RIGA DI NON SELEZIONE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::select($this->nameForm . '_LS_' . $j, 1, '', '', '');
        }
        for ($db = 1; $db <= 99; $db++) {
            $db = str_repeat("0", 2 - strlen(trim($db))) . trim($db);
            $db_test = 'LS' . $db;
            try { // Apro il DB
                $ce_ls = 1;
                $this->LIQ_DB = ItaDB::DBOpen($db_test);
                if (!$this->LIQ_DB) {
                    $ce_ls = 0;
                }
            } catch (Exception $e) {
                $ce_ls = 0;
            }
            if ($ce_ls == 1) {
                $sql = "SELECT * FROM AUSIL WHERE ROWID = 3";
                $liq_rec = ItaDB::DBSQLSelect($this->LIQ_DB, $sql, false);
                $des_liq = $liq_rec['DESCR'];
                if ($des_liq == '')
                    $des_liq .= 'LIQUID. DEL SERVIZIO ' . $db;
                for ($j = 1; $j <= 10; $j++) {
                    $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                    Out::select($this->nameForm . '_LS_' . $j, 1, $db_test, $sel, $db_test . ' ' . $des_liq);
                }
            }
        }
    }

    function SetParss() {
        //  VALORIZZO LE 10 SELECT CON LA PRIMA RIGA DI NON SELEZIONE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::select($this->nameForm . '_SS_' . $j, 1, '', '', '');
        }
        for ($db = 1; $db <= 99; $db++) {
            $db = str_repeat("0", 2 - strlen(trim($db))) . trim($db);
            $db_test = 'SS' . $db;
            try { // Apro il DB
                $ce_ss = 1;
                $this->SS_DB = ItaDB::DBOpen($db_test);
                if (!$this->SS_DB) {
                    $ce_ss = 0;
                }
            } catch (Exception $e) {
                $ce_ss = 0;
            }
            if ($ce_ss == 1) {
                $sql = "SELECT * FROM AUSIL WHERE ROWID = 60";
                $ss_rec = ItaDB::DBSQLSelect($this->SS_DB, $sql, false);
                $des_ss = $ss_rec['AUSDES'];
                if ($des_ss == '')
                    $des_ss .= 'STATO CIVILE ' . $db;
                for ($j = 1; $j <= 10; $j++) {
                    $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                    Out::select($this->nameForm . '_SS_' . $j, 1, $db_test, $sel, $db_test . ' ' . $des_ss);
                }
            }
        }
    }

    function SetParsegr() {
        //  VALORIZZO LE 10 SELECT CON LA PRIMA RIGA DI NON SELEZIONE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::select($this->nameForm . '_SER_' . $j, 1, '', '', '');
            Out::select($this->nameForm . '_CLA_' . $j, 1, '', '', '');
        }
        try {
            $this->SEGR_DB = ItaDB::DBOpen('SEGR');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM ANASER WHERE CODSER <> ''";
        $word_rec = ItaDB::DBSQLSelect($this->SEGR_DB, $sql);
        $indice = 0;
        for ($y = 1; $y <= count($word_rec); $y++) {
            for ($j = 1; $j <= 10; $j++) {
                $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                Out::select($this->nameForm . '_SER_' . $j, 1, trim($word_rec[$indice]['CODSER']), '', $word_rec[$indice]['CODSER'] . ' ' . $word_rec[$indice]['DESCSE']);
            }
            $indice++;
        }
        $sql = "SELECT * FROM ANACLA WHERE IMCLAS <> ''";
        $word_rec = ItaDB::DBSQLSelect($this->SEGR_DB, $sql);
        $indice = 0;
        for ($y = 1; $y <= count($word_rec); $y++) {
            for ($j = 1; $j <= 10; $j++) {
                $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                Out::select($this->nameForm . '_CLA_' . $j, 1, trim($word_rec[$indice]['IMCLAS']), '', $word_rec[$indice]['IMCLAS'] . ' ' . $word_rec[$indice]['DESCLA']);
            }
            $indice++;
        }
    }

    function SetParuni() {
        //  VALORIZZO LE 10 SELECT CON LA PRIMA RIGA DI NON SELEZIONE
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            Out::select($this->nameForm . '_UD_' . $j, 1, '', '', '');
        }
        try {
            $this->ISCW_DB = ItaDB::DBOpen('ISCW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        if (!$this->ISCW_DB) {
            return;
        }
        $sql = "SELECT * FROM ANAUDI";
        $iscw_rec = ItaDB::DBSQLSelect($this->ISCW_DB, $sql);
        $indice = 0;
        for ($y = 1; $y <= count($iscw_rec); $y++) {
            for ($j = 1; $j <= 10; $j++) {
                $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
                Out::select($this->nameForm . '_UD_' . $j, 1, 'RT01' . trim($iscw_rec[$indice]['UDICOD']), '', $iscw_rec[$indice]['UDICOD'] . ' ' . $iscw_rec[$indice]['UDIDES']);
            }
            $indice++;
        }
    }

    function AggParspec($utente, $Ente) {
        //  AGGIORNAMENTO DEI PARAMETRI SPECIALI

        $utente = str_repeat("0", 6 - strlen(trim($utente))) . trim($utente);

        //  SEGRETERIA

        $sql = "SELECT * FROM UTEPSP WHERE UTSPCD='" . $utente . "' AND UTSPAP='SG'";
        $itw_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        for ($j = 0; $j <= count($itw_tab); $j++) {
            try {
                $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTEPSP', 'ROWID', $itw_tab[$j]['ROWID']);
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Parametri SEGRETERIA", $e->getMessage());
            }
        }
        $Def = $_POST[$this->nameForm . '_DEF_SEGR'];
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            $Ser = $_POST[$this->nameForm . '_SER_' . $j];
            $Cla = $_POST[$this->nameForm . '_CLA_' . $j];
            if (trim($Ser) != "" || trim($Cla) != "") {
                $utenti_rec['UTSPCD'] = $utente;
                $utenti_rec['UTSPAP'] = "SG";
                $utenti_rec['UTSPTP'] = "01";
                if (trim($Ser) == "" && trim($Cla) != "")
                    $utenti_rec['UTSPTP'] = "02";
                if (trim($Ser) != "" && trim($Cla) != "")
                    $utenti_rec['UTSPTP'] = "03";
                $Ser = trim($Ser) . str_repeat(" ", 2 - strlen($Ser));
                $Cla = str_repeat(" ", 4 - strlen(trim($Cla))) . trim($Cla);
                $utenti_rec['UTSPTX'] = $Ser . $Cla;
                $utenti_rec['UTSPFL'] = "";
                if ($Def == $j) {
                    $utenti_rec['UTSPFL'] = "*";
                }
                $this->RegParspec($utenti_rec);
            }
        }
        //  WORD

        $sql = "SELECT * FROM UTEPSP WHERE UTSPCD='" . $utente . "' AND UTSPAP='WP'";
        $itw_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        for ($j = 0; $j <= count($itw_tab); $j++) {
            try {
                $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTEPSP', 'ROWID', $itw_tab[$j]['ROWID']);
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Parametri WORD", $e->getMessage());
            }
        }
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            $Wp = substr($_POST[$this->nameForm . '_WP_' . $j], 2, 2);
            if (trim($Wp) != "") {
                $utenti_rec['UTSPCD'] = $utente;
                $utenti_rec['UTSPAP'] = "WP";
                $utenti_rec['UTSPTP'] = "01";
                $utenti_rec['UTSPTX'] = $Wp . $Ente;
                $this->RegParspec($utenti_rec);
            }
        }
        //  LIQUIDAZIONI

        $sql = "SELECT * FROM UTEPSP WHERE UTSPCD='" . $utente . "' AND UTSPAP='LS'";
        $itw_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        for ($j = 0; $j <= count($itw_tab); $j++) {
            try {
                $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTEPSP', 'ROWID', $itw_tab[$j]['ROWID']);
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Parametri LIQUIDAZIONI", $e->getMessage());
            }
        }
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            $Ls = substr($_POST[$this->nameForm . '_LS_' . $j], 2, 2);
            if (trim($Ls) != "") {
                $utenti_rec['UTSPCD'] = $utente;
                $utenti_rec['UTSPAP'] = "LS";
                $utenti_rec['UTSPTP'] = "01";
                $utenti_rec['UTSPTX'] = $Ls . $Ente;
                $this->RegParspec($utenti_rec);
            }
        }
        //  STATO CIVILE

        $sql = "SELECT * FROM UTEPSP WHERE UTSPCD='" . $utente . "' AND UTSPAP='SS'";
        $itw_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        for ($j = 0; $j <= count($itw_tab); $j++) {
            try {
                $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTEPSP', 'ROWID', $itw_tab[$j]['ROWID']);
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Parametri STATO CIVILE", $e->getMessage());
            }
        }
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            $Ss = substr($_POST[$this->nameForm . '_SS_' . $j], 2, 2);
            if (trim($Ss) != "") {
                $utenti_rec['UTSPCD'] = $utente;
                $utenti_rec['UTSPAP'] = "SS";
                $utenti_rec['UTSPTP'] = "01";
                $utenti_rec['UTSPTX'] = $Ss . $Ente;
                $this->RegParspec($utenti_rec);
            }
        }
        //  UNITA' DIDATTICHE

        $sql = "SELECT * FROM UTEPSP WHERE UTSPCD='" . $utente . "' AND UTSPAP='RT'";
        $itw_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql);
        for ($j = 0; $j < count($itw_tab); $j++) {
            try {
                $nRows = ItaDB::DBDelete($this->ITW_DB, 'UTEPSP', 'ROWID', $itw_tab[$j]['ROWID']);
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione Parametri UNITA' DIDATTICHE", $e->getMessage());
            }
        }
        for ($j = 1; $j <= 10; $j++) {
            $j = str_repeat("0", 2 - strlen(trim($j))) . trim($j);
            $Ud = substr($_POST[$this->nameForm . '_UD_' . $j], 4, 2);
            if (trim($Ud) != "") {
                $utenti_rec['UTSPCD'] = $utente;
                $utenti_rec['UTSPAP'] = "RT";
                $utenti_rec['UTSPTP'] = "01";
                $utenti_rec['UTSPTX'] = $Ud;
                $this->RegParspec($utenti_rec);
            }
        }
    }

    function RegParspec($utenti_rec) {
        try {
            ItaDB::DBInsert($this->ITW_DB, 'UTEPSP', 'ROWID', $utenti_rec);
        } catch (Exception $e) {
            Out::msgStop("Errore in Inserimento Parametri Speciali", $e->getMessage(), '600', '600');
        }
    }

    function cambiaPassword($codiceUtente, $nomeUtente, $modo) {

        $model = "accPassword";
        itaLib::openForm($model);
        $formObj = itaModel::getInstance($model);
        if (!$formObj) {
            Out::msgStop("Errore", "Apertura dettaglio fallita");
            return false;
        }
        $formObj->setEvent('openform');
        if ($modo == 'reset') {
            $formObj->setReturnModel($this->nameForm);
            $formObj->setReturnEvent('returnAccPassword');
        }
        $formObj->setModo($modo);
        $formObj->setNomeUtenteGestito($nomeUtente);
        $formObj->setCodiceUtenteGestito($codiceUtente);
        $formObj->parseEvent();


//        $model = 'accPassword';
//        $_POST = array();
//        $_POST['event'] = 'openform';
//        $_POST['modo'] = $modo;
//        $_POST['UTECOD'] = $codiceUtente;
//        $_POST['UTELOG'] = $nomeUtente;
//        itaLib::openForm($model);
//        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//        $model();
    }

    function caricaOggetti($utelog) {
        TableView::clearGrid($this->gridOggetti);
        $ita_grid01 = new TableView($this->gridOggetti, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => "SELECT * FROM OGGUTENTI WHERE UTELOG='" . addslashes($utelog) . "'"));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->setSortIndex('DOGCOD');
        $ita_grid01->setSortOrder('asc');
        $elenco = $ita_grid01->getDataArray();
        $ita_grid01->getDataPageFromArray('json', $elenco);
        TableView::enableEvents($this->gridOggetti);
    }

    public function decodeBorUtefirProgsogg($PROGSOGG) {
        if (!empty($PROGSOGG)) {
            include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
            $cwbLibDB_BTA_SOGG = new cwbLibDB_BTA_SOGG();
            $btaSogg_rec = $cwbLibDB_BTA_SOGG->leggiBtaSogg(array('PROGSOGG' => $PROGSOGG), false);

            if ($btaSogg_rec) {
                Out::valore($this->nameForm . '_BOR_UTEFIR[PROGSOGG]', $btaSogg_rec['PROGSOGG']);
                Out::valore($this->nameForm . '_PROGSOGG_decod', "{$btaSogg_rec['COGNOME']} {$btaSogg_rec['NOME']}");
            } else {
                Out::valore($this->nameForm . '_BOR_UTEFIR[PROGSOGG]', '');
                Out::valore($this->nameForm . '_PROGSOGG_decod', '');
            }
        } else {
            Out::valore($this->nameForm . '_BOR_UTEFIR[PROGSOGG]', '');
            Out::valore($this->nameForm . '_PROGSOGG_decod', '');
        }
    }

    public function decodeBorUtentiCodmails($PROGRECORD) {
        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
        $cwbLibDB_BGE = new cwbLibDB_BGE();
        $bgeMails_rec = $cwbLibDB_BGE->leggiBgeMails(array('PROGRECORD' => $PROGRECORD), false);

        if ($bgeMails_rec) {
            Out::valore($this->nameForm . '_BOR_UTENTI[COD_MAILS]', $bgeMails_rec['PROGRECORD']);
            Out::valore($this->nameForm . '_COD_MAILS_decod', $bgeMails_rec['SMTP_SERV']);
        } else {
            Out::valore($this->nameForm . '_BOR_UTENTI[COD_MAILS]', '');
            Out::valore($this->nameForm . '_COD_MAILS_decod', '');
        }
    }

    public function decodeFlagScadenza($value) {
        if ($value == '1') {
            Out::valore($this->nameForm . '_UTENTI[UTEDPA]', '');
            Out::valore($this->nameForm . '_nessunaScadenza', '1');
            Out::disableField($this->nameForm . '_UTENTI[UTEDPA]');
        } else {
            Out::enableField($this->nameForm . '_UTENTI[UTEDPA]');
            Out::setFocus('', $this->nameForm . '_UTENTI[UTEDPA]');
        }
    }

    private function ssoCitywareFields($utenti_rec = false) {
        Out::tabDisable($this->nameForm . '_tabUtente', $this->nameForm . '_tabCityWare');
        TableView::disableEvents($this->gridBorUteliv);
        Out::required($this->nameForm . '_BOR_UTENTI[SIGLAUTE]', false, false);
        Out::required($this->nameForm . '_BOR_UTENTI[UTEDB]', false, false);
        Out::required($this->nameForm . '_BOR_UTENTI[PWDB]', false, false);
        Out::required($this->nameForm . '_UTENTI[DATAINIZ]', false, false);

        if ($this->accLib->isSSOCitywareEnabled()) {
            $this->dataBorUteliv = array();
            $this->dataBorUtelivDelete = array();
            $this->dataBorUtefirImmagine = '';

            Out::attributo($this->nameForm . '_BorUtefirImmagine', 'src', 1);
            Out::tabEnable($this->nameForm . "_tabUtente", $this->nameForm . "_tabCityWare");
            TableView::enableEvents($this->gridBorUteliv);
            TableView::reload($this->gridBorUteliv);

            Out::required($this->nameForm . '_BOR_UTENTI[SIGLAUTE]', true, true);
            Out::required($this->nameForm . '_BOR_UTENTI[UTEDB]', true, true);
            Out::required($this->nameForm . '_BOR_UTENTI[PWDB]', true, true);
            Out::required($this->nameForm . '_UTENTI[DATAINIZ]', true, true);

            if (!$utenti_rec) {
                /*
                 * Campi svuotati in caso di duplicazione
                 */

                Out::valori(array_fill_keys(array_keys($_POST[$this->nameForm . '_BOR_UTEFIR']), ''), $this->nameForm . '_BOR_UTEFIR');
                Out::valori(array_fill_keys(array_keys($_POST[$this->nameForm . '_BOR_FRMUTE']), ''), $this->nameForm . '_BOR_FRMUTE');
                return;
            }

            $UtenteCW_rec = $this->libBorUtenti->leggiUtenti($utenti_rec['UTELOG']);

            if (count($UtenteCW_rec)) {
                Out::hide($this->nameForm . '_Cancella');

                $UtenteCW_rec = reset($UtenteCW_rec);

                if ($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE']) {
                    $mimeType = false;

                    if (strpos($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'], "\xFF\xD8\xFF") === 0)
                        $mimeType = 'image/jpg';

                    if (strpos($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'], "GIF") === 0)
                        $mimeType = 'image/gif';

                    if (strpos($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'], "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a") === 0)
                        $mimeType = 'image/png';

                    if ($mimeType) {
                        $this->dataBorUtefirImmagine = $UtenteCW_rec['BOR_UTEFIR']['IMMAGINE'];
                        Out::attributo($this->nameForm . '_BorUtefirImmagine', 'src', 0, "data:$mimeType;base64," . base64_encode($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE']));
                    } else {
                        Out::msgStop("Errore", "Impossibile decodificare il campo IMMAGINE in BOR_UTEFIR");
                    }

                    unset($UtenteCW_rec['BOR_UTEFIR']['IMMAGINE']);
                }

                Out::valori(array_map('trim', $UtenteCW_rec['BOR_UTENTI']), $this->nameForm . '_BOR_UTENTI');
                Out::valori(array_map('trim', $UtenteCW_rec['BOR_UTEFIR']), $this->nameForm . '_BOR_UTEFIR');

                if (is_array($UtenteCW_rec['BOR_UTELIV'])) {
                    $this->dataBorUteliv = $UtenteCW_rec['BOR_UTELIV'];
                }

                $this->decodeBorUtentiCodmails($UtenteCW_rec['BOR_UTENTI']['COD_MAILS']);
                $this->decodeBorUtefirProgsogg($UtenteCW_rec['BOR_UTEFIR']['PROGSOGG']);

                Out::valore($this->nameForm . '_CityWare[Utente]', $utenti_rec['UTELOG']);
                Out::disableField($this->nameForm . '_UTENTI[UTELOG]');

                if ($UtenteCW_rec['BOR_UTEFIR']['LDAP_USER'] && !$utenti_rec['UTELDAP']) {
                    /*
                     * Allineo il valore
                     */

                    Out::valore($this->nameForm . '_UTENTI[UTELDAP]', $UtenteCW_rec['BOR_UTEFIR']['LDAP_USER']);

                    $utenti_update_rec = array('ROWID' => $utenti_rec['ROWID'], 'UTELDAP' => $UtenteCW_rec['BOR_UTEFIR']['LDAP_USER']);
                    $this->updateRecord($this->ITW_DB, 'UTENTI', $utenti_update_rec, "Allineamento utente LDAP per '{$utenti_rec['UTELOG']}' => '{$UtenteCW_rec['BOR_UTEFIR']['LDAP_USER']}'");
                }
            }
        }
    }

    private function ssoCitywareTempPassword() {
        return $this->accLibCityWare->getTempPassword();
    }

    private function ssoCitywareValidate($libBorUtenti_rec) {
        $validateErrors = array();

        if (!$libBorUtenti_rec['BOR_UTENTI']['CODUTE']) {
            $validateErrors[] = "Campo 'Codice utente' mancante.";
        }

//        if (!$libBorUtenti_rec['BOR_UTENTI']['PWDUTE']) {
//            $validateErrors[] = "Campo 'Password' mancante.";
//        }

        if (!$libBorUtenti_rec['BOR_UTENTI']['SIGLAUTE']) {
            $validateErrors[] = "Campo 'Sigla' mancante.";
        }

        if (!$libBorUtenti_rec['BOR_UTENTI']['DATAINIZ']) {
            $validateErrors[] = "Campo 'Data inizio' mancante.";
        }

        if (!$libBorUtenti_rec['BOR_UTENTI']['UTEDB']) {
            $validateErrors[] = "Campo 'Codice utente database' mancante.";
        }

        if (!$libBorUtenti_rec['BOR_UTENTI']['NOMEUTE']) {
            $validateErrors[] = "Campo 'Nominativo' mancante.";
        }

        if (count($validateErrors)) {
            Out::msgStop("Errore", "Errore validazione utente Cityware.\n\n" . implode("\n", $validateErrors));
            return false;
        }

        return true;
    }

    public function elaboraRecords($utenti_tab) {
        foreach ($utenti_tab as $k => $utenti_rec) {
            if ($utenti_rec['UTEDPA'] === '9999') {
                $utenti_tab[$k]['UTEDPA'] = '';
            }

            if (!isset($utenti_rec['UTEPAS'])) {
                $sql = "SELECT UTEPAS FROM UTENTI WHERE UTECOD = '" . $utenti_rec['UTECOD'] . "'";
                $utenti_appoggio = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                $utenti_rec['UTEPAS'] = $utenti_appoggio['UTEPAS'];
            }

            $flagResetPassword = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'FlagResetPassword');

            if ($utenti_rec['UTEDPA'] === '9999') {
                $utenti_tab[$k]['UTESPA'] = '<i style="color: blue;">Nessuna scadenza</i>';
            }

            if ($utenti_rec['UTESPA'] <= date('Ymd')) {
                if ($utenti_rec['UTEDPA'] !== '9999') {
                    $utenti_tab[$k]['UTESPA'] = '<i style="color: red;">Password scaduta</i>';
                }

                if ($flagResetPassword) {
                    $utenti_tab[$k]['UTESPA'] = '<i style="color: red;">Password annullata</i>';
                }
            }

            if (!$utenti_rec['UTEPAS']) {
                $utenti_tab[$k]['UTESPA'] = '<i style="color: red;">Nessuna password</i>';
            }
        }

        return $utenti_tab;
    }

}
