<?php

/**
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Alessandro Mucci
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    23.01.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envDBUtil.mysql.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/lib/AppDefinitions/SchemaDefinition.class.php';
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

function envDomains() {
    $envDomains = new envDomains();
    $envDomains->parseEvent();
    return;
}

class envDomains extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = "envDomains";
    public $divGes = "envDomains_divGestione";
    public $divRis = "envDomains_divRisultato";
    public $divRic = "envDomains_divRicerca";
    public $divAppInit = "envDomains_DivAppInit";
    public $gridTabella = "envDomains_gridTabella";
    public $gridParametri = "envDomains_gridParametri";
    public $arrLog = array();
    public $eqAudit;
    public $utiEnte;
    public $currentDomain;
    public $admin;
    public $praLib;
    public $tabParametri;
    static public $itaPath = '';
    static public $config = '';
    static public $enti = '';
    static public $printers = '';
    static public $apps = '';
    static public $models = array();
    static private $dizionarioTipiApplicativo = array(
        array(
            'CHIAVE' => 'PROTOCOLLO',
            'CLASSE' => 'TIPIAPPLICATIVO',
            'CONFIG' => '',
            'DESCRIZIONE' => 'Tipo Protocollo'
        ),
        array(
            'CHIAVE' => 'ANAGRAFE',
            'CLASSE' => 'CONNESSIONI',
            'CONFIG' => '',
            'DESCRIZIONE' => 'Tipo Anagrafe'
        ),
        array(
            'CHIAVE' => 'CONTABILITA',
            'CLASSE' => 'TIPIAPPLICATIVO',
            'CONFIG' => '',
            'DESCRIZIONE' => 'Tipo Contabilità'
        )
    );
    static private $dizionarioTipiApplicativoDefaults = array(
        'PROTOCOLLO' => array(
        ),
        'ANAGRAFE' => array(
            array('value' => '', 'text' => 'Nessuna'),
            array('value' => 'Italsoft', 'text' => 'Italsoft'),
            array('value' => 'CityWare', 'text' => 'CityWare'),
            array('value' => 'CityWareOnLine', 'text' => 'CityWareOnLine'),
            array('value' => 'SolClient', 'text' => 'SolClient'),
        ),
        'CONTABILITA' => array(
            array('value' => '', 'text' => 'Nessuna'),
            array('value' => 'Italsoft', 'text' => 'Italsoft'),
            array('value' => 'CityWareOnLine', 'text' => 'CityWareOnLine')
        )
    );

    function __construct() {
        parent::__construct();
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEBDB', false);
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
        try {
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
        $this->eqAudit = new eqAudit();
        $this->utiEnte = new utiEnte();
        $this->praLib = new praLib();
        $this->currentDomain = App::$utente->getKey($this->nameForm . '_currentDomain');
        $this->admin = App::$utente->getKey($this->nameForm . '_admin');
        $this->tabParametri = App::$utente->getKey($this->nameForm . '_tabParametri');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_currentDomain', $this->currentDomain);
        App::$utente->setKey($this->nameForm . '_admin', $this->admin);
        App::$utente->setKey($this->nameForm . '_tabParametri', $this->tabParametri);
    }

    public function getCurrentDomain() {
        return $this->currentDomain;
    }

    public function setCurrentDomain($currentDomain) {
        $this->currentDomain = $currentDomain;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->openRisultato();
                $this->controllaAccesso();
                $this->spegniBottoni();
                $this->tipoAccesso();
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridTabella:
                        Out::clearFields($this->nameForm, '');
                        $this->Dettaglio($_POST['rowid']);
                        $this->spegniDiv();
//Out::hide($this->nameForm . '_DivCopia');
                        Out::hide($this->nameForm . '_NuovaPassword_field');
                        Out::delClass($this->nameForm . '_CodiceTemplate', 'required');
                        break;
                }
                break;
            case 'editGridRow':
                break;
            case 'addGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'onClickTablePager':
            case $this->gridTabella:
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridTabella:
// Temporaneamente rimosso
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridTabella, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('SEQUENZA');
                        $ita_grid01->exportXLS('', 'Domains.xls');
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CodiceTemplate':
                        IF ($_POST[$this->nameForm . "_CodiceTemplate"] != '') {
                            $ret = $this->CheckTemplate($_POST[$this->nameForm . "_CodiceTemplate"], '');
                            if ($ret['status'] == -1) {
                                Out::msgStop("Attenzione", $ret['message']);
                                Out::setFocus('', $this->nameForm . '_CodiceTemplate');
                                break;
                            }
                        }
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        $this->MsgNuovo();
                        break;
                    case $this->nameForm . '_Importa':
                        $enti = self::$enti = parse_ini_file(ITA_CONFIG_PATH . '/enti.ini', true);
                        $Key = 0;
                        foreach ($enti as $keyEnte => $propsEnte) {
                            $Key = $Key + 10;
                            $Enti_tab[$Key]['CODICE'] = $propsEnte['codice'];
                            $Enti_tab[$Key]['DESCRIZIONE'] = $keyEnte;
                            $Enti_tab[$Key]['SEQUENZA'] = $Key;
                            try {
                                $this->insertRecord($this->ITALWEB_DB, 'DOMAINS', $Enti_tab[$Key], $insert_Info);
                            } catch (Exception $e) {
                                Out::msgStop("Errore di inserimento ENTI.", $e->getMessage());
                            }
                        }
                        $this->openRisultato();
                        break;
                    case $this->nameForm . '_AnnullaInput':
                        $this->openRisultato();
                        break;
                    case $this->nameForm . '_ConfermaInput':
                        if (ereg("^[a-zA-Z0-9]{2,16}$", $_POST[$this->nameForm . "_ricCodice"])) {
                            if ($_POST[$this->nameForm . "_ricCodice"]) {
                                $ret = $this->CheckCode($_POST[$this->nameForm . "_ricCodice"], 'Conferma');
                                if ($ret['status'] == -1) {
                                    Out::msgStop("Attenzione", $ret['message']);
                                    $this->openRisultato();
                                    break;
                                }
                                Out::valore($this->nameForm . '_Codice', $_POST[$this->nameForm . "_ricCodice"]);
                                Out::setFocus('', $this->nameForm . '_Descrizione');
                                Out::valore($this->nameForm . '_Sequenza', $this->CalcolaMaxSequenza());
                            } else {
                                Out::msgStop('Attenzione', 'Il codice ente non può essere vuoto.');
                                $this->MsgNuovo();
                            }
                        } else {
                            Out::msgStop('Attenzione', 'Il codice ente non può contenere caratteri speciali.');
                            $this->MsgNuovo();
                        }
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;
                    case $this->nameForm . '_Torna':
                        $this->Nascondi();
                        Out::hide($this->divGes);
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Domains_rec['ROWID'] = $_POST[$this->nameForm . '_DOMAINS']['ROWID'];
                        $CodiceEnte = $_POST[$this->nameForm . '_Codice'];
                        $Domains_rec['DESCRIZIONE'] = $_POST[$this->nameForm . '_Descrizione'];
                        if ($_POST[$this->nameForm . '_Sequenza']) {
                            $Domains_rec['SEQUENZA'] = $_POST[$this->nameForm . '_Sequenza'];
                        } else {
                            $sql = "SELECT MAX(SEQUENZA)AS SEQUENZA FROM DOMAINS";
                            $Domains_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                            $Domains_rec['SEQUENZA'] = $Domains_tab['SEQUENZA'];
                        }
                        $Parametriente_rec = $_POST[$this->nameForm . '_PARAMETRIENTE'];
//                        App::log($Domains_rec);
//                        App::log($Parametriente_rec);
                        $update_Info = "Oggetto: Aggiornamento della tabella DOMAINS dell'ente " . $CodiceEnte;
                        if ($this->updateRecord($this->ITALWEB_DB, 'DOMAINS', $Domains_rec, $update_Info)) {
                            $Check_parametriente = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE = '$CodiceEnte'", false);
                            if ($Check_parametriente) {
                                $update_Info = "Oggetto: Aggiornamento della tabella PARAMETRIENTE dell'ente " . $Parametriente_rec['DENOMINAZIONE'];
                                $this->updateRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $Parametriente_rec, $update_Info);
                            } else {
                                $Parametriente_rec['CODICE'] = $CodiceEnte;
                                $update_Info = "Oggetto: Inserimento della tabella PARAMETRIENTE dell'ente " . $Parametriente_rec['DENOMINAZIONE'];
                                $this->insertRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $Parametriente_rec, $update_Info);
                                Out::valori($Parametriente_rec, $this->nameForm . '_PARAMETRIENTE');
                            }

                            $this->aggiornaParametri($CodiceEnte);

                            $this->riordinaSequenza();
                            //$this->openRisultato();
                        }
                        TableView::setSelection($this->gridTabella, $_POST[$this->gridTabella]['gridParam']['selrow']);
                        $this->spegniBottoni();
                        break;
                    case $this->nameForm . '_btnInitPra':
                        $ret = $this->CheckTemplateSuapMaster($_POST[$this->nameForm . "_CodiceTemplate"], '');
                        if ($ret['status'] == -1) {
                            Out::msgStop("Attenzione", $ret['message']);
                            Out::setFocus('', $this->nameForm . '_CodiceTemplate');
                            break;
                        }
                        if (!$this->inizializzaSuap($_POST[$this->nameForm . '_CodiceTemplate'], $_POST[$this->nameForm . "_Codice"], substr($_POST[$this->nameForm . "_Descrizione"], 0, 36))) {
                            $this->openRisultato();
                            break;
                        }
                        Out::msgInfo("Inizializzazione SUAP", $_POST[$this->nameForm . '_CodiceTemplate'] . '-->' . $_POST[$this->nameForm . "_Codice"] . '<BR> Eseguita.');
                        $this->openRisultato();
                        break;
                    case $this->nameForm . '_btnInitBda':
                        if (!$this->inizializzaBdap($_POST[$this->nameForm . '_CodiceTemplate'], $_POST[$this->nameForm . "_Codice"], substr($_POST[$this->nameForm . "_Descrizione"], 0, 36))) {
                            $this->openRisultato();
                            break;
                        }
                        Out::msgInfo("Inizializzazione BDAP", $_POST[$this->nameForm . '_CodiceTemplate'] . '-->' . $_POST[$this->nameForm . "_Codice"] . '<BR> Eseguita.');
                        break;
                    case $this->nameForm . '_btnInitCds':
                        if (!$_POST[$this->nameForm . '_CodiceTemplate']) {
                            Out::msgStop("Attenzione", "Valorizzare un codice ditta sorgente");
                            Out::setFocus('', $this->nameForm . '_CodiceTemplate');
                            break;
                        }
                        if (!$this->inizializzaCds($_POST[$this->nameForm . '_CodiceTemplate'], $_POST[$this->nameForm . "_Codice"])) {
                            $this->openRisultato();
                            break;
                        }
                        Out::msgInfo("Inizializzazione CDS", $_POST[$this->nameForm . '_CodiceTemplate'] . '-->' . $_POST[$this->nameForm . "_Codice"] . '<BR> Eseguita.');
                        break;
                    case $this->nameForm . '_btnInitGfm':
                        if (!$_POST[$this->nameForm . '_CodiceTemplate']) {
                            Out::msgStop("Attenzione", "Valorizzare un codice ditta sorgente");
                            Out::setFocus('', $this->nameForm . '_CodiceTemplate');
                            break;
                        }
                        if (!$this->inizializzaGfm($_POST[$this->nameForm . '_CodiceTemplate'], $_POST[$this->nameForm . "_Codice"])) {
                            $this->openRisultato();
                            break;
                        }
                        Out::msgInfo("Inizializzazione GFM", $_POST[$this->nameForm . '_CodiceTemplate'] . '-->' . $_POST[$this->nameForm . "_Codice"] . '<BR> Eseguita.');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridParametri:
                        if ($_POST['value'] != 'undefined') {
                            Out::msgInfo($_POST['cellname'], $_POST['value']);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currentDomain');
        App::$utente->removeKey($this->nameForm . '_admin');
        App::$utente->removeKey($this->nameForm . '_tabParametri');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function inizializzaBdap($CodiceSor, $CodiceDes, $Descrizione) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

        $DB_Alias = "BDAP";
        $ditta_SOR = $CodiceSor;
        $ditta_DES = $CodiceDes;



        $DB_DES = "BDAP" . $CodiceDes;
        $DB_SOR = "BDAP" . $CodiceSor;


        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }

//
// Apro BDAP
//
        $daCopiare = array(
            "c10_stepproceduraaggiudicazione",
            "c11_tipoproceduraaggiudicazione",
            "c12_tipologiadifinanziamento",
            "c13_fontedifinanziamento",
            "c14_vocidispesa",
            "c15_tipoentesiope",
            "c1_settorecpt",
            "c2_intestaistituzionale",
            "c3_strumentoattuativo",
            "c4_tiponorma",
            "c5_indicatori",
            "c6_fasiprocedurali",
            "c7_motivorevoca",
            "c8_formagiuridica",
            "c9_settoreattivitaeconomica",
            "lp_categorie_opere",
            "lp_documenti_opera",
            "lp_documenti_opera_dettaglio",
            "lp_istat_comuni", "lp_istat_provincie",
            "lp_istat_regioni",
            "lp_ruolo",
            "lp_ruoloavcp",
            "lp_simog_condizionipn_no_bando",
            "lp_simog_criteriaggiudicazione",
            "lp_simog_motivazionivariante",
            "lp_simog_tipologialavoro",
            "lp_simog_tiporaggruppamento",
            "lp_tipoaffidamentoavcp",
            "lp_tipoattoamministrativo",
            "lp_vociappalto",
            "lp_vocispesa_tipoimporto",
            "pt_tabella1",
            "pt_tabella2",
            "pt_tabella3",
            "pt_tabella4",
            "pt_tabella5",
            "pt_tabella6",
            "sim_artesclusione",
            "sim_categsat",
            "sim_classeimporto",
            "sim_coltipodoc",
            "sim_modogara",
            "sim_modoindizione",
            "sim_modorealizzazione",
            "sim_modoriaggiud",
            "sim_motivicancellazione",
            "sim_motivivariazione",
            "sim_motivivariazionesat",
            "sim_motivointerruzione",
            "sim_motivorisoluzione",
            "sim_motivosospensione",
            "sim_motivovariante",
            "sim_ruoloaggiudicatario",
            "sim_statoavcpass",
            "sim_statocig",
            "sim_tipoappalto",
            "sim_tipofinanziamento",
            "sim_tipologiaprocedura",
            "sim_tipologiasat",
            "sim_tipoprestazione",
            "sim_tipostrumento"
        );
        foreach ($daCopiare as $key => $tabella) {
            $retCopy = envDBUtils::copyTableData($DB_Alias, $ditta_SOR, $tabella, $ditta_DES, $tabella);
            if ($retCopy['status'] == -1) {
                Out::msgStop("Errore Copia Dati", "$DB_SOR.$tabella non copiato in $DB_DES.$tabella");
                return false;
            }
        }

        $DB_DES = "ITALWEB" . $CodiceDes;
        $DB_SOR = "ITALWEB" . $CodiceSor;
        $DB_Alias = "ITALWEB";
        $tabella = "DOC_DOCUMENTI";
        $retCopy = envDBUtils::copyTableData($DB_Alias, $ditta_SOR, $tabella, $ditta_DES, $tabella, $where = "CLASSIFICAZIONE='BDAP");
        if ($retCopy['status'] == -1) {
            Out::msgStop("Errore Copia Dati", "$DB_SOR.$tabella non copiato in $DB_DES.$tabella");
            return false;
        }


        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di $DB_SOR su $DB_DES");
        return true;
    }

    public function inizializzaSuap($CodiceSor, $CodiceDes, $Descrizione) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $DB_Alias = "PRAM";
        $ditta_SOR = $CodiceSor;
        $ditta_DES = $CodiceDes;

//
// Apro SUAP
//
        $daCopiare = array(
            "ANAARC",
            "ANAATT",
            "ANANOM",
            "ANANOR",
            "ANAPAR",
            "ANAPRA",
            "ANAREQ",
            "ANASET",
            "ANASPA",
            "ANASTP",
            "ANATIP",
            "ANATSP",
            "ANAUNI",
            "ANPDOC",
            "FILENT",
            "ITEDAG",
            "ITENOR",
            "ITEPAS",
            "ITEREQ",
            "PRACLT",
            "PRAIDC",
        );
        foreach ($daCopiare as $key => $tabella) {
            $retCopy = envDBUtils::copyTableData($DB_Alias, $ditta_SOR, $tabella, $ditta_DES, $tabella);
            if ($retCopy['status'] == -1) {
                Out::msgStop("Errore Copia Dati", "$DB_SOR.$tabella non copiato in $DB_DES.$tabella");
                return false;
            }
        }

        $fl_errore = false;
        $PRAM_DB = itaDB::DBOpen("PRAM", $CodiceDes);
        $Anapra_tab = itaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ANAPRA", true);
        if ($Anapra_tab) {
            foreach ($Anapra_tab as $key => $Anapra_rec) {
                $Anapra_rec['PRASLAVE'] = 1;
                $update_Info = "Oggetto: Trasforma procedimento: " . $Anapra_tab['PRANUM'] . " in slave." . $Anapra_rec['PRANUM'];
                try {
                    if (!$this->updateRecord($PRAM_DB, 'ANAPRA', $Anapra_rec, $update_Info)) {
                        Out::msgStop("Errore modifica procedimento", "Modifca procedimento:" . $Anapra_rec['PRANUM'] . " in slave fallito");
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore modifica procedimento", "Modifca procedimento:" . $Anapra_rec['PRANUM'] . " in slave fallito");
                    return false;
                }
            }
        }

//
// Setup Parametri vari
//
        $praLib = new praLib($CodiceDes);
        $Filent_rec = $praLib->GetFilent(1);
        $Filent_rec['FILDE1'] = '0';
        $Filent_rec['FILDE3'] = $CodiceSor;
        $Filent_rec['FILDE4'] = 'S';
        $Filent_rec['FILDE6'] = $Descrizione;
        $update_Info = 'Oggetto: Aggiorno parametri fascicoli';
        try {
            $this->updateRecord($praLib->getPRAMDB(), 'FILENT', $Filent_rec, $update_Info);
        } catch (Exception $exc) {
            Out::msgStop("Errore", "Configurazione Parametri Fascicoli Fallita:<br>" . $exc->getTraceAsString());
            return false;
        }
        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di $DB_SOR su $DB_DES");

        $pathSorg = Config::getPath('general.itaProc') . 'ente' . $CodiceSor . '/';
        $pathDest = Config::getPath('general.itaProc') . 'ente' . $CodiceDes . '/';
        if (!$this->copyRecursive($pathSorg, $pathDest)) {
            $this->log('', '', eqAudit::OP_MISC_AUDIT, "Copia cartella $pathSorg in $pathDest Fallita.....");
        } else {
            $this->log('', '', eqAudit::OP_MISC_AUDIT, "Copiata cartella $pathSorg in $pathDest");
        }
        return true;
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        Out::hide($this->divAppInit);
    }

    public function CreaSql() {
        $where = $sql = "";
        $sql = "SELECT * FROM DOMAINS";
        if ($this->currentDomain) {
            $sql .= " WHERE CODICE = '" . $this->currentDomain . "'";
        }
        return $sql;
    }

    public function CalcolaMaxSequenza() {
        $sql = "SELECT MAX(SEQUENZA) AS SEQUENZA FROM DOMAINS";
        $MaxSequenza = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $MaxSequenza = $MaxSequenza['SEQUENZA'] + 10;
        return $MaxSequenza;
    }

    public function Dettaglio($_Indice) {
        $Tabella_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM DOMAINS WHERE ROWID='$_Indice'", false);
        $open_Info = 'Oggetto: Apertura della tabella DOMAINS ' . $Tabella_rec['CODICE'];
        $this->openRecord($this->ITALWEB_DB, 'DOMAINS', $open_Info);
        $codice = $Tabella_rec['CODICE'];
        $Parametriente_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE='$codice'", false);
        Out::valori($Parametriente_rec, $this->nameForm . '_PARAMETRIENTE');

        $this->tabParametri = $this->preparaParametri($Tabella_rec['CODICE']);
        //$this->tabParametri = $this->elaboraTabellaParametri($this->tabParametri);
        $this->CaricaGriglia($this->gridParametri, $this->elaboraTabellaParametri($this->tabParametri));

        $this->Nascondi();
        Out::valore($this->nameForm . '_Codice', $Tabella_rec['CODICE']);
        Out::valore($this->nameForm . '_Descrizione', $Tabella_rec['DESCRIZIONE']);
        Out::valore($this->nameForm . '_Sequenza', $Tabella_rec['SEQUENZA']);
        Out::valori($Tabella_rec, $this->nameForm . '_DOMAINS');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->divGes, '', 0);
        Out::hide($this->nameForm . '_DivCopia');
        Out::show($this->divAppInit, '', 0);
        Out::setFocus('', $this->nameForm . '_Codice');
        TableView::disableEvents($this->gridTabella);
    }

    function openRisultato() {
        TableView::disableEvents($this->gridTabella);
        $sql = $this->CreaSql();
        $ita_grid01 = new TableView($this->gridTabella, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageRows(1000000);
        $ita_grid01->setSortIndex('SEQUENZA');
        $ita_grid01->setSortOrder('asc');
        if ($ita_grid01->getDataPage('json', true)) {
            $this->Nascondi();
            Out::show($this->divRis);
            Out::show($this->nameForm . '_Nuovo');
            Out::setFocus('', $this->nameForm . '_Nuovo');
            TableView::enableEvents($this->gridTabella);
        } else {
            $this->Nascondi();
            Out::show($this->divRis);
            Out::show($this->nameForm . '_Nuovo');
            if (file_exists(ITA_CONFIG_PATH . '/enti.ini')) {
                Out::show($this->nameForm . '_Importa');
            }
            Out::setFocus('', $this->nameForm . '_Nuovo');
            TableView::enableEvents($this->gridTabella);
        }
    }

    function Nuovo() {
        Out::clearFields($this->nameForm, '');
        $this->Nascondi();
        Out::show($this->divGes);
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_DivCopia');

        $this->tabParametri = $this->elaboraTabellaParametri(self::$dizionarioTipiApplicativo);
        $this->CaricaGriglia($this->gridParametri, $this->tabParametri);

        Out::delClass($this->nameForm . '_CodiceTemplate', 'required');
        Out::addClass($this->nameForm . '_CodiceTemplate', 'required');
        Out::show($this->nameForm . '_NuovaPassword_field');
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function MsgNuovo() {
        $header = '<center><p style="font-size:1.2em;font-wheight:bold;color:red;">Inserire un codice che non sia già presente tra gli enti creati.<br>Quindi procedere alla compilazione dei campi successivi.</p></center>';
        $bottoni = array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaInput', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaInput', 'model' => $this->nameForm));
//        App::log($bottoni);

        Out::msgInput(
                'Nuovo Ente', array(
            'label' => 'Codice Ente da Creare<br>',
            'id' => $this->nameForm . '_ricCodice',
            'name' => $this->nameForm . '_ricCodice',
            'type' => 'text',
            'size' => '20',
            'maxlength' => '20'), $bottoni, $this->divGes, 'auto', 'auto', 'false', $header);
    }

    public function Aggiungi() {

        /*
         * Controlli generali e conferma
         */
        $ret = $this->CheckCode($_POST[$this->nameForm . "_Codice"]);
        if ($ret['status'] == -1) {
            Out::msgStop("Attenzione", $ret['message']);
            Out::setFocus('', $this->nameForm . '_Descrizione');
            return false;
        }
        $fl_err = false;
        while (true) {
            if (!$_POST[$this->nameForm . "_Descrizione"]) {
                $fl_err = true;
                break;
            }
            if (!$_POST[$this->nameForm . "_CodiceTemplate"]) {
                $fl_err = true;
                break;
            }

            if (!$_POST[$this->nameForm . "_PARAMETRIENTE"]["DENOMINAZIONE"]) {
                $fl_err = true;
                break;
            }
            break;
        }
        if ($fl_err) {
            Out::msgStop("Attenzione", "Campi Obbligatori mancanti");
            Out::setFocus('', $this->nameForm . '_Descrizione');
            return false;
        }


        /*
         * Definisco gli enti sorgente e destino.
         */
        $ditta_SOR = $_POST[$this->nameForm . '_CodiceTemplate'];
        $ditta_DES = $_POST[$this->nameForm . "_Codice"];

        /*
         * Creo ITW
         */
        $DB_Alias = "ITW";
        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }

        switch ($this->formData[$this->nameForm . '_CopiaUtenti']) {
            case 'Tutti':
                $daCopiare = array("GRUPPI", "UTENTI", "RICHUT");
                if (!$this->copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES)) {
                    Out::msgStop("Errore", "Copia tabelle fallita");
                    return false;
                }
                break;
            default:
//case 'Default'
                $ITW_DB = itaDB::DBOpen("ITW", $_POST[$this->nameForm . "_Codice"]);
                $sqlGruppi = file_get_contents(ITA_BASE_PATH . '/schema/mysql/ITW.GRUPPI.init.sql');
                $pos = strpos($sqlGruppi, "INSERT INTO");
                $sqlGruppi = substr($sqlGruppi, $pos);
                $pos = strpos($sqlGruppi, ");");
                $sqlGruppi = substr($sqlGruppi, 0, $pos + 2);
                try {
                    ItaDB::DBSQLExec($ITW_DB, $sqlGruppi);
                } catch (Exception $exc) {
                    Out::msgStop("Errore", "Errore in importazione GRUPPI: " . $exc->getMessage());
                    return false;
                }
                $sqlUtenti = file_get_contents(ITA_BASE_PATH . '/schema/mysql/ITW.UTENTI.init.sql');
                $pos = strpos($sqlUtenti, "INSERT INTO");
                $sqlUtenti = substr($sqlUtenti, $pos);
                $pos = strpos($sqlUtenti, ");");
                $sqlUtenti = substr($sqlUtenti, 0, $pos + 2);
                try {
                    ItaDB::DBSQLExec($ITW_DB, $sqlUtenti);
                } catch (Exception $exc) {
                    Out::msgStop("Errore", "Errore in importazione UTENTI: " . $exc->getMessage());
                    return false;
                }
                break;
        }

        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di  $DB_Alias.$ditta_SOR su $DB_Alias.$ditta_DES");

        if ($_POST[$this->nameForm . "_NuovaPassword"]) {
            $ITW_DB = itaDB::DBOpen("ITW", $_POST[$this->nameForm . "_Codice"]);
            $Utenti_tab = itaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTELOG NOT IN ('ADMIN','italsoft','admin')", true);
            foreach ($Utenti_tab as $key => $Utenti_rec) {
                $Utenti_rec['UTEUPA'] = date("Ymd");
                $Utenti_rec['UTESPA'] = date("Ymd");
                $Utenti_rec['UTEPAS'] = $_POST[$this->nameForm . "_NuovaPassword"];
                $update_Info = "Oggetto: Reset password Utente: " . $Utenti_rec['UTELOG'];
                try {
                    if (!$this->updateRecord($ITW_DB, 'UTENTI', $Utenti_rec, $update_Info)) {
                        Out::msgStop("Errore reset Password", "Resets password per l'utente:" . $Utenti_rec['UTELOG'] . " fallito");
                        $this->openRisultato();
                        break;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore reset Password", "Resets password per l'utente:" . $Utenti_rec['UTELOG'] . " fallito");
                    $this->openRisultato();
                    break;
//echo $exc->getTraceAsString();
                }
            }
        }

        /*
         * Creo ITALWEB
         */
        $DB_Alias = 'ITALWEB';
        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }
        switch ($this->formData[$this->nameForm . '_CopiaUtenti']) {
            case 'Tutti':
                $daCopiare = array("MEN_PERMESSI", "ENV_UTEMETA", "ENV_CONFIG", "ANA_COMUNE");
                break;
            default:
                $daCopiare = array("ENV_CONFIG", "ANA_COMUNE");
                break;
        }
        if (!$this->copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES)) {
            Out::msgStop("Errore", "Copia tabelle fallita");
            return false;
        }
        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di  $DB_Alias.$ditta_SOR su $DB_Alias.$ditta_DES");

        $DB_Alias = "ITALWEB";
        $tabella = "DOC_DOCUMENTI";
        $retCopy = envDBUtils::copyTableData($DB_Alias, $ditta_SOR, $tabella, $ditta_DES, $tabella);
        if ($retCopy['status'] == -1) {
            Out::msgStop("Errore Copia Dati", "$DB_SOR.$tabella non copiato in $DB_DES.$tabella");
            return false;
        }
        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di  $DB_Alias.$ditta_SOR.$tabella su $DB_Alias.$ditta_DES.$tabella");

        /*
         * Attivo il nuovo Ente
         */
        try {
            $Domains_rec['CODICE'] = $_POST[$this->nameForm . '_Codice'];
            $Domains_rec['DESCRIZIONE'] = $_POST[$this->nameForm . '_Descrizione'];
            $Domains_rec['RISERVATO'] = $_POST[$this->nameForm . '_Riservato'];
//Sequenza
            if ($_POST[$this->nameForm . '_Sequenza']) {
                $Domains_rec['SEQUENZA'] = $_POST[$this->nameForm . '_Sequenza'];
            } else {
                $sql = "SELECT MAX(SEQUENZA)AS SEQUENZA FROM DOMAINS";
                $Domains_max = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                $Domains_rec['SEQUENZA'] = $Domains_max['SEQUENZA'] + 10;
            }
            $insert_Info = "Oggetto: Aggiunta alla tabella DOMAINS dell'ente " . $Domains_rec['DESCRIZIONE'];
            $this->insertRecord($this->ITALWEB_DB, 'DOMAINS', $Domains_rec, $insert_Info);
        } catch (Exception $e) {
            Out::msgStop("Errore di Inserimento su DOMAINS.", $e->getMessage());
            $this->openRisultato();
            return false;
        }

        try {
            $ParametriEnte_rec = $_POST[$this->nameForm . '_PARAMETRIENTE'];
            $ParametriEnte_rec['CODICE'] = $_POST[$this->nameForm . '_Codice'];
            $insert_Info = "Oggetto: Aggiunta alla tabella PARAMETRIENTE dell'ente " . $ParametriEnte_rec['DENOMINAZIONE'];
            $this->insertRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $ParametriEnte_rec, $insert_Info);
        } catch (Exception $e) {
            Out::msgStop("Errore di Inserimento su DOMAINS.", $e->getMessage());
            $this->openRisultato();
            return false;
        }
        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Attivato ente " . $Domains_rec['CODICE'] . " su DOMAINS e PARAMETRIENTE");

        /*
         * Creo DB obbligatori
         */
        $Db_list = SchemasDefinition::getTenantSchemas();
        foreach ($Db_list as $DB_Alias => $DB_rec) {
            if (!$DB_rec['OBBLIGATORIO']) {
                continue;
            }
            $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
            if ($ret_copia['status']) {
                Out::msgStop($ret_copia['title'], $ret_copia['message']);
                $this->openRisultato();
                break;
            }
        }

        $this->aggiornaParametri($_POST[$this->nameForm . "_Codice"]);

        Out::msgInfo("Crea Ente", "Ente: " . $Domains_rec['CODICE'] . "<pre>" . implode("\n", $this->arrLog) . "</pre><br>" . $Domains_rec['DESCRIZIONE'] . " creato con successo.");
        $this->openRisultato();
    }

    function CheckCode($Codice) {
        $ret = array(
            'status' => 0,
            'message' => ''
        );

        $MessaggiErrori = array();
// Controllo Enti.ini
        if (file_exists(ITA_CONFIG_PATH . '/enti.ini')) {
            $MessaggiErrori[] = "E' presente enti.ini.";
        }
        $sql = "SHOW DATABASES LIKE 'ITALWEB" . $Codice . "'";
        $Italweb_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($Italweb_db) {
            $MessaggiErrori[] = "E' presente ITALWEB" . $Codice;
        }
        $sql = "SHOW DATABASES LIKE 'ITW" . $Codice . "'";
        $ITW_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($ITW_db) {
            $MessaggiErrori[] = "E' presente ITW" . $Codice;
        }

        $sql = "SHOW DATABASES LIKE 'PROT" . $Codice . "'";
        $PROT_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($PROT_db) {
            $MessaggiErrori[] = "E' presente PROT" . $Codice;
        }

        $sql = "SHOW DATABASES LIKE 'PRAM" . $Codice . "'";
        $PRAM_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($PRAM_db) {
            $MessaggiErrori[] = "E' presente PRAM" . $Codice;
        }

        $sql = "SELECT * FROM DOMAINS WHERE CODICE='$Codice'";
        $Domains_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($Domains_tab) {
            $MessaggiErrori[] = "Nella tabella DOMAINS è già presente un ente con il codice: " . $Codice;
        }

        $sql = "SELECT * FROM PARAMETRIENTE WHERE CODICE='$Codice'";
        $ParametriEnte_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($ParametriEnte_tab) {
            $MessaggiErrori[] = "Nella tabella PARAMETRIENTE è già presente un ente con il codice: " . $Codice;
        }

        $cartellaProcedimenti = Config::getPath('general.itaProc') . 'ente' . $Codice . '/';
        if (file_exists($cartellaProcedimenti)) {
            $MessaggiErrori[] = "Cartella Procedimenti fascicoli elettronici già presente.";
        }

        if ($Codice == 'DB' || $Codice == 'db' || $Codice == 'Db' || $Codice == 'dB') {
            $MessaggiErrori[] = "Non è possibile creare un ente con il codice  " . $Codice;
        }

        if (count($MessaggiErrori)) {
            $ret['status'] = -1;
            $ret['message'] = "Non è stato possibile procedere con la creazione di un nuovo ente per i seguienti motivi:<br>" . implode("<br>", $MessaggiErrori);
        }
        return $ret;
    }

    function CheckTemplate($Codice) {
        $ret = array(
            'status' => 0,
            'message' => ''
        );

        $MessaggiErrori = array();
//
// Controllo ITALWEB<xxxx>
//
        $sql = "SHOW DATABASES LIKE 'ITALWEB" . $Codice . "'";
        $Italweb_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$Italweb_db) {
            $MessaggiErrori[] = "Non esiste ITALWEB" . $Codice;
        }
//
// Controllo ITW<xxxx>
//
        $sql = "SHOW DATABASES LIKE 'ITW" . $Codice . "'";
        $ITW_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$ITW_db) {
            $MessaggiErrori[] = "Non esiste ITW" . $Codice;
        }

//
// Controllo PROT<xxxx>
//
        $sql = "SHOW DATABASES LIKE 'PROT" . $Codice . "'";
        $PROT_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$PROT_db) {
            $MessaggiErrori[] = "Non esiste PROT" . $Codice;
        }

//
// Controllo PRAM<xxxx>
//
        $sql = "SHOW DATABASES LIKE 'PRAM" . $Codice . "'";
        $PRAM_db = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$PRAM_db) {
            $MessaggiErrori[] = "Non esiste PRAM" . $Codice;
        }
        return $ret;
    }

    function CheckTemplateSuapMaster($Codice) {
        $ret = array(
            'status' => 0,
            'message' => ''
        );

        $MessaggiErrori = array();

//
// Controllo Se l'ente è Master
//
        $pralib = new praLib($Codice);
        $Filent_rec = $pralib->GetFilent(1);
        if ($Filent_rec['FILDE4'] != "M") {
            $MessaggiErrori[] = "Ente $Codice non è di tipo Master, non utilizzabile";
        }
//
// Controllo La cartella testiAssociati del master
//
        $pathSorg = Config::getPath('general.itaProc') . 'ente' . $_POST[$this->nameForm . "_CodiceTemplate"] . '/';
        if (!is_dir($pathSorg)) {
            $MessaggiErrori[] = "Cartella $pathSorg Non disponibile";
        }

        if (count($MessaggiErrori)) {
            $ret['status'] = -1;
            $ret['message'] = "Non possibile procedere con la creazione di un nuovo ente per i seguienti motivi:<br>" . implode("<br>", $MessaggiErrori);
        }


        return $ret;
    }

    public function copiaDB($DB_Alias, $ditta_SOR, $ditta_DES) {
        $DB_DES = $DB_Alias . $_POST[$this->nameForm . "_Codice"];
        $DB_SOR = $DB_Alias . $_POST[$this->nameForm . '_CodiceTemplate'];

        $ret_copia = array(
            'status' => 0,
            'title' => '',
            'message' => ''
        );

//controllo se il db esiste già
        if (envDBUtils::checkExistDB($DB_Alias, $ditta_DES)) {
            return $ret_copia;
        }

        $retCrea = envDBUtils::creaDB($DB_Alias, $ditta_DES);
        if ($retCrea['status'] == -1) {
            $ret_copia['status'] = -1;
            $ret_copia['title'] = "Creazione DATABASE";
            $ret_copia['message'] = "Creazione Data Base $DB_Alias$ditta_DES Fallita. {$retCrea['message']}";
            return $ret_copia;
        }

        $retClona = envDBUtils::cloneSchema($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($retClona['status'] == -1) {
            $ret_copia['status'] = -1;
            $ret_copia['title'] = "Creazione Schema";
            $ret_copia['message'] = "Creazione SChema $DB_Alias$ditta_DES Fallita. {$retClona['message']}";
            return $ret_copia;
        }

        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Copiato $DB_SOR su $DB_DES");
        return $ret_copia;
    }

    public function log($DB_LOG, $DSET_LOG, $Operazione, $Estremi) {

        $this->arrLog[] = "[" . date('d/m/Y') . ":" . date('H:i:s') . "] " . $Estremi;
        $this->eqAudit->logEqEvent(
                $this, array(
            'DB' => $DB_LOG,
            'DSet' => $DSET_LOG,
            'Operazione' => $Operazione,
            'Estremi' => $Estremi
        ));
    }

    public function copyRecursive($source, $dest) {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            if (file_exists($dest)) {
                $sourcefolder = basename($source);
                if (!@mkdir($dest . "/" . $sourcefolder)) {
                    return false;
                }
                $dest = $dest . "/" . $sourcefolder;
            } else {
                if (!@mkdir($dest)) {
                    return false;
                }
            }

            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!$this->copyRecursive($source . "/" . $file, $dest . "/" . $file)) {
                            closedir($dir_handle);
                            return false;
                        }
                    } else {
                        if (!@copy($source . "/" . $file, $dest . "/" . $file)) {
                            closedir($dir_handle);
                            return false;
                        }
                    }
                }
            }
            closedir($dir_handle);
        } else {
            if (!@copy($source, $dest)) {
                return false;
            }
        }
        return true;
    }

    function riordinaSequenza() {
        $sql = " SELECT * FROM DOMAINS ORDER BY SEQUENZA";
        $Domains_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        $Key = 0;
        foreach ($Domains_tab as $Domains_rec) {
            $Key = $Key + 10;
            $Domains_rec['SEQUENZA'] = $Key;
            $update_Info = "Oggetto: Riaggiornamento sequenza DOMAINS " . $Domains_rec['ROWID'];
            $this->updateRecord($this->ITALWEB_DB, 'DOMAINS', $Domains_rec, $update_Info);
        }
        return true;
    }

    function creaCombo() {
        Out::select($this->nameForm . '_CopiaUtenti', 1, "Tutti", "1", "Tutti");
        Out::select($this->nameForm . '_CopiaUtenti', 1, "Default", "0", "Solo default");
    }

    public function inizializzaCds($CodiceSor, $CodiceDes) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        $ditta_SOR = $CodiceSor;
        $ditta_DES = $CodiceDes;
        /*
         * CODSTRADA
         */
        $DB_Alias = "CODSTRADA";

        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }

//apro CODSTRADA
        $CDS_DB = ItaDB::DBOpen("CODSTRADA", $CodiceDes);

        $daCopiare = array(
            "ALIASCOMUNI",
            "ANABOLLETTINI",
            "ANACOMUNI",
            "ANADITTE",
            "ANAMANCO",
            "ANAQUIET",
            "ANASCART",
            "ANASTRAD",
            "ANATIPAN",
            "ANATIPAR",
            "ANATIPMZ",
            "ANATIPNOT",
            "ANATIPO",
            "ANATIPSO",
            "AUTO",
            "AUTOALIA",
            "AUTORITA",
            "CODICI142",
            "COMUNICF",
            "CONVERSIONE",
            "ERRORIMCTC",
            "FMLPARM",
            "FTPPARM",
            "GRPVGENE",
            "ISTAT",
            "ISTATARTNOAGG",
            "ISTATMAS",
            "LEGGEPUNTI",
            "LEXART",
            "LEXCDS",
            "LEXFONTI",
            "LEXPRIV",
            "NAZIONINOTIFICA",
            "PARAMETR",
            "PARAMETR02",
            "PARAMETRIESTERO",
            "PARAMETRIMCTC",
            "PARAMETRIPCCSA",
            "PARAMETRIPUNTI",
            "PARAMETRIQR",
            "PARMVGENE",
            "REGFMAIL",
            "REGSPESE",
            "SANZIACC",
            "SOSPENSIONIDETT",
            "SOSPENSIONIMAST",
            "STATO",
            "STATOVER",
            "TABFESTE",
            "TIPODOC",
            "TIPODOCP",
            "TIPOGRAFIE",
            "TIPOMOD",
            "TIPOREPO",
            "TIPORESTITUZIONE",
            "TIPOVERB",
            "TRACCNOTIFICHE",
            "TRACCSTAMPAPDF",
            "VALIDITA",
        );
        if (!$this->copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES)) {
            Out::msgStop("Errore", "Copia tabelle fallita");
            return false;
        }

        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di $DB_Alias.$ditta_SOR su $DB_Alias.$ditta_DES");

//PARAMETRIPUNTI - tolgo i valori
        $sql = "SELECT * FROM PARAMETRIPUNTI";
        $tab = ItaDB::DBSQLSelect($CDS_DB, $sql, true);
        foreach ($tab as $rec) {
            $rec['VALORE'] = '';
            try {
                ItaDB::DBUpdate($CDS_DB, "PARAMETRIPUNTI", "ROWID", $rec);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in aggiornamento PARAMETRIPUNTI: " . $exc->getMessage());
                return false;
            }
        }
//PARAMETRIMCTC - tolgo utenti e password
        $sql = "SELECT * FROM PARAMETRIMCTC";
        $tab = ItaDB::DBSQLSelect($CDS_DB, $sql, true);
        foreach ($tab as $rec) {
            if ($rec['CAMPO'] == 'VPNUSER' || $rec['CAMPO'] == 'VPNPASSWORD' || $rec['CAMPO'] == 'MATRICOLA' || $rec['CAMPO'] == 'CODICESEGRETO') {
                $rec['VALORE'] = '';
            } else {
                continue;
            }
            try {
                ItaDB::DBUpdate($CDS_DB, "PARAMETRIMCTC", "ROWID", $rec);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in aggiornamento PARAMETRIMCTC: " . $exc->getMessage());
                return false;
            }
        }
//PARAMETRIPCCSA - tolgo utenti e password
        $sql = "SELECT * FROM PARAMETRIPCCSA";
        $tab = ItaDB::DBSQLSelect($CDS_DB, $sql, true);
        foreach ($tab as $rec) {
            if ($rec['CAMPO'] == 'USER' || $rec['CAMPO'] == 'PASSWD') {
                $rec['VALORE'] = '';
            } else {
                continue;
            }
            try {
                ItaDB::DBUpdate($CDS_DB, "PARAMETRIPCCSA", "ROWID", $rec);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in aggiornamento PARAMETRIMCTC: " . $exc->getMessage());
                return false;
            }
        }

        /*
         * CDSRUOLI
         */

        $DB_Alias = "CDSRUOLI";

        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }

//apro CDSRUOLI
        $RUOLI_DB = ItaDB::DBOpen("CDSRUOLI", $CodiceDes);

        $daCopiare = array(
            "PARAMETRIRUOLO"
        );
        if (!$this->copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES)) {
            Out::msgStop("Errore", "Copia tabelle fallita");
            return false;
        }

        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di $DB_Alias.$ditta_SOR su $DB_Alias.$ditta_DES");

//PARAMETRIRUOLO - tolgo codici relativi all'ente
        $sql = "SELECT * FROM PARAMETRIRUOLO";
        $tab = ItaDB::DBSQLSelect($RUOLI_DB, $sql, true);
        foreach ($tab as $rec) {
            if ($rec['CAMPO'] == 'CODICEENTEIMPOSITORE' || $rec['CAMPO'] == 'CODICEPROVINCIACNC' || $rec['CAMPO'] == 'CODICECOMUNECNC') {
                $rec['VALORE'] = '';
            } else {
                continue;
            }
            try {
                ItaDB::DBUpdate($RUOLI_DB, "PARAMETRIRUOLO", "ROWID", $rec);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in aggiornamento PARAMETRIRUOLO: " . $exc->getMessage());
                return false;
            }
        }
        /*
         * ACCERT
         */
        $DB_Alias = "ACCERT";
        $DB_DES = "ACCERT" . $CodiceDes;
        $DB_SOR = "ACCERT" . $CodiceSor;

        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }


        return true;
    }

    public function inizializzaGfm($CodiceSor, $CodiceDes) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        $ditta_SOR = $CodiceSor;
        $ditta_DES = $CodiceDes;
        /*
         * GAFIERE
         */
        $DB_Alias = "GAFIERE";

        $ret_copia = $this->copiaDB($DB_Alias, $ditta_SOR, $ditta_DES);
        if ($ret_copia['status']) {
            Out::msgStop($ret_copia['title'], $ret_copia['message']);
            $this->openRisultato();
            return false;
        }

//apro CODSTRADA
        $CDS_DB = ItaDB::DBOpen("GAFIERE", $CodiceDes);

        $daCopiare = array(
            "ANAEVENTI",
            "TABORDFI",
            "TABORDME",
            "TIPOAUTORITA",
            "TIPOAUTR",
            "TIPOCOMU",
            "TIPOCONC",
            "TIPOFIER",
            "TIPOITER",
            "TIPOMERC",
            "TIPOMOD",
            "TIPOPOST",
            "TIPOPRES",
            "TIPOREGIONE",
            "TIPOSEME"
        );
        if (!$this->copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES)) {
            Out::msgStop("Errore", "Copia tabelle fallita");
            return false;
        }

        $this->log($DB_DES, '', eqAudit::OP_MISC_AUDIT, "Caricate le tabelle base di $DB_Alias.$ditta_SOR su $DB_Alias.$ditta_DES");

        return true;
    }

    public function copiaTabelle($daCopiare, $DB_Alias, $ditta_SOR, $ditta_DES) {
        $DB_DES = $DB_Alias . $CodiceDes;
        $DB_SOR = $DB_Alias . $CodiceSor;

        $DB = ItaDB::DBOpen($DB_Alias, $ditta_DES);
        foreach ($daCopiare as $tabella) {
//se la tabella non è vuota non procedo
            $sql = "SELECT COUNT(*) AS TOT FROM $tabella";
            $count_tab = ItaDB::DBSQLSelect($DB, $sql, false);
            if ($count_tab['TOT'] > 0) {
                continue;
            }
            $retCopy = envDBUtils::copyTableData($DB_Alias, $ditta_SOR, $tabella, $ditta_DES, $tabella);
            if ($retCopy['status'] == -1) {
                Out::msgStop("Errore Copia Dati", "$DB_SOR.$tabella non copiato in $DB_DES.$tabella: " . $retCopy['message']);
                return false;
            }
        }
        return true;
    }

    public function controllaAccesso() {
        $this->admin = true;
        if ($this->currentDomain) {
            $this->admin = false;
        }
    }

    public function tipoAccesso() {
        if ($this->admin !== true) {
            $domain = ItaDB::DBSQLSelect($this->ITALWEB_DB, $this->CreaSql(), false);
            if ($domain) {
                $this->Dettaglio($domain['ROWID']);
                $this->spegniDiv();
                Out::hide($this->nameForm . '_NuovaPassword_field');
                Out::delClass($this->nameForm . '_CodiceTemplate', 'required');
            }
        }
    }

    public function spegniBottoni() {
        if ($this->admin !== true) {
            Out::hide($this->nameForm . '_Nuovo');
        }
    }

    public function spegniDiv() {
        if ($this->admin !== true) {
            Out::hide($this->nameForm . '_Torna');
            Out::hide($this->nameForm . '_divSeqRiv');
            Out::hide($this->nameForm . '_DivCopia');
            Out::hide($this->nameForm . '_DivAppInit');
            //Out::hide($this->nameForm . '_DivParametri');
        }
    }

    public function CaricaGriglia($griglia, $dati, $sidx = '', $sord = '') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $dati,
            'rowIndex' => 'idx')
        );
        if ($sidx != '') {
            $ita_grid01->setSortIndex($sidx);
        }
        if ($sord != '') {
            $ita_grid01->setSortOrder($sord);
        }
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($griglia);

        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($griglia);
        }
    }

    public function preparaParametri($ente) {
        $devLib = new devLib();
        $this->tabParametri = array();
        $this->tabParametri = self::$dizionarioTipiApplicativo;
        foreach ($this->tabParametri as $key => $parametro) {
            if ($parametro['CHIAVE'] == 'PROTOCOLLO') {
                App::$utente->setKey('ditta', $ente);
                $paramProtocollo = $this->leggiParametroProtocollo();
                $this->tabParametri[$key]['CONFIG'] = $paramProtocollo['TIPOPROTOCOLLO'];
                $this->tabParametri[$key]['ROWID'] = $paramProtocollo['ROWID'];
            } else {
                $config_rec = $devLib->getEnv_config($parametro['CLASSE'], 'codice', $parametro['CHIAVE'], false);
                $this->tabParametri[$key]['CONFIG'] = $config_rec['CONFIG'];
                $this->tabParametri[$key]['ROWID'] = $config_rec['ROWID'];
            }
        }
        return $this->tabParametri;
    }

    public function elaboraTabellaParametri($tabParametri) {
        foreach ($tabParametri as $key => $parametro) {
            $currentValue = $tabParametri[$key]['CONFIG'];
            $optionsArray = array();
            switch ($parametro['CLASSE'] . $parametro['CHIAVE']) {
                case 'CONNESSIONIANAGRAFE':
                    $optionsArray = self::$dizionarioTipiApplicativoDefaults['ANAGRAFE'];
                    break;
                case 'TIPIAPPLICATIVOCONTABILITA':
                    $optionsArray = self::$dizionarioTipiApplicativoDefaults['CONTABILITA'];
                    break;
                case 'TIPIAPPLICATIVOPROTOCOLLO':
                    include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
                    $arrProtocolli = proWSClientHelper::getElencoProtocolliRemoti();
                    foreach ($arrProtocolli as $protocollo) {
                        $prot = array('value' => $protocollo, 'text' => $protocollo);
                        $optionsArray[] = $prot;
                    }
                    break;
            }

            foreach ($optionsArray as $k => $option) {
                if ($option['value'] == $currentValue) {
                    $optionsArray[$k]['selected'] = true;
                }
            }

            $tabParametri[$key]['CONFIG'] = itaComponents::getHtmlItaSelect(array(
                        'properties' => array(
                            'id' => $this->nameForm . '_' . $parametro['CLASSE'] . $parametro['CHIAVE'],
                            'name' => $this->nameForm . '_' . $parametro['CLASSE'] . $parametro['CHIAVE'],
                            'style' => 'width: 200px;'
                        ),
                        'options' => $optionsArray
            ));
        }
        return $tabParametri;
    }

    public function aggiornaParametri($ente) {
        $devLib = new devLib();
        foreach ($this->tabParametri as $parametro) {
            if ($parametro['CHIAVE'] == 'PROTOCOLLO') {
                App::$utente->setKey('ditta', $ente);
                $paramProtocollo = $this->leggiParametroProtocollo();
                $paramProtocollo['TIPOPROTOCOLLO'] = $_POST[$this->nameForm . '_' . $parametro['CLASSE'] . $parametro['CHIAVE']];
                $update_Info = "Oggetto: Aggiornamento parametro " . $parametro['DESCRIZIONE'] . "dell'ente $ente";
                $this->updateRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $paramProtocollo, $update_Info);
            } else {
                $config_rec = $devLib->getEnv_config($parametro['CLASSE'], 'codice', $parametro['CHIAVE'], false);
                if ($config_rec) {
                    $config_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $parametro['CLASSE'] . $parametro['CHIAVE']];
                    $update_Info = "Oggetto: Aggiornamento parametro " . $parametro['DESCRIZIONE'] . "dell'ente $ente";
                    $this->updateRecord($this->ITALWEB, 'ENV_CONFIG', $config_rec, $update_Info);
                } else {
                    $config_rec['CHIAVE'] = $parametro['CHIAVE'];
                    $config_rec['CLASSE'] = $parametro['CLASSE'];
                    $config_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $parametro['CLASSE'] . $parametro['CHIAVE']];
                    $insert_Info = "Oggetto: Inserimento parametro " . $parametro['DESCRIZIONE'] . "dell'ente $ente";
                    $this->insertRecord($this->ITALWEB, 'ENV_CONFIG', $config_rec, $insert_Info);
                }
            }
        }
    }

    public function leggiParametroProtocollo() {
        return $this->utiEnte->GetParametriEnte();
    }

}
