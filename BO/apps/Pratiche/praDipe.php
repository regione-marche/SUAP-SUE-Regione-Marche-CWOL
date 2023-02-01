<?php

/**
 *
 * ANAGRAFICA DIPENDENTI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');

function praDipe() {
    $praDipe = new praDipe();
    $praDipe->parseEvent();
    return;
}

class praDipe extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $utiEnte;
    public $ITALWEB_DB;
    public $praLib;
    public $nameForm = "praDipe";
    public $divGes = "praDipe_divGestione";
    public $divRis = "praDipe_divRisultato";
    public $divRic = "praDipe_divRicerca";
    public $gridDipe = "praDipe_gridDipe";
    public $gridUteComm = "praDipe_gridUteCommercio";
    public $openMode;
    public $returnModel;
    public $returnEvent;
    public $returnId;
    public $page;
    public $rows;
    public $sidx;
    public $sord;
    public $utentiComm = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->openMode = App::$utente->getKey('praDipe_openMode');
        $this->returnModel = App::$utente->getKey('praDipe_returnModel');
        $this->returnEvent = App::$utente->getKey('praDipe_returnEvent');
        $this->returnId = App::$utente->getKey('praDipe_returnId');
        $this->page = App::$utente->getKey('praDipe_page');
        $this->rows = App::$utente->getKey('praDipe_rows');
        $this->sidx = App::$utente->getKey('praDipe_sidx');
        $this->sord = App::$utente->getKey('praDipe_sord');
        $this->utentiComm = App::$utente->getKey('praDipe_utentiComm');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey('praDipe_openMode', $this->openMode);
            App::$utente->setKey('praDipe_returnModel', $this->returnModel);
            App::$utente->setKey('praDipe_returnEvent', $this->returnEvent);
            App::$utente->setKey('praDipe_returnId', $this->returnId);
            App::$utente->setKey('praDipe_page', $this->page);
            App::$utente->setKey('praDipe_rows', $this->rows);
            App::$utente->setKey('praDipe_sidx', $this->sidx);
            App::$utente->setKey('praDipe_sord', $this->sord);
            App::$utente->setKey('praDipe_utentiComm', $this->utentiComm);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca

                switch ($_POST['openMode']) {
                    case 'newFromUtenti':
                        $this->openMode = $_POST['openMode'];
                        $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                        $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                        $this->returnId = $_POST[$this->nameForm . '_returnId'];
                        $this->page = $_POST['page'];
                        $this->rows = $_POST['rows'];
                        $this->sidx = $_POST['sidx'];
                        $this->sord = $_POST['sord'];
                        $this->openNuovo();
                        Out::valore($this->nameForm . '_ANANOM[NOMCOG]', $_POST['datiUtente']['COGNOME']);
                        Out::valore($this->nameForm . '_ANANOM[NOMNOM]', $_POST['datiUtente']['NOME']);
                        Out::valore($this->nameForm . '_ANANOM[NOMEML]', $_POST['datiUtente']['MAIL']);
                        break;
                    case 'editFromUtenti':
                        $this->openMode = $_POST['openMode'];
                        $Ananom_rec = $this->praLib->GetAnanom($_POST['NOMRES']);
                        $this->Dettaglio($Ananom_rec['ROWID']);
                        break;
                    default:
                        $this->openMode = "";
                        $this->OpenRicerca();
                        break;
                }

                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDipe:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridUteComm:
                        Out::msgInput(
                                'Scelta Utente per Collegamento al Commercio', array(
                            array(
                                'label' => array('style' => "width:70px;", 'value' => 'Utente  '),
                                'id' => $this->nameForm . '_Utente',
                                'name' => $this->nameForm . '_Utente',
                                'value' => $this->utentiComm[$_POST['rowid']]['UTENTE'],
                                'size' => '20'),
                            array(
                                'label' => array('style' => "width:70px;", 'value' => 'Aggregato  '),
                                'id' => $this->nameForm . '_DescAggregato',
                                'name' => $this->nameForm . '_DescAggregato',
                                'class' => "ita-edit-lookup",
                                'value' => $this->utentiComm[$_POST['rowid']]['AGGREGATO'],
                                'size' => '20'),
                            array(
                                'id' => $this->nameForm . '_CodAggregato',
                                'name' => $this->nameForm . '_CodAggregato',
                                'class' => "ita-hidden",
                                'value' => $this->utentiComm[$_POST['rowid']]['CODAGGREGATO']),
                            array(
                                'id' => $this->nameForm . '_Rowid',
                                'name' => $this->nameForm . '_Rowid',
                                'class' => "ita-hidden",
                                'value' => $_POST['rowid']),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiComm', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );

                        break;
                }
                break;
            case 'addGridRow':
                if ($_POST[$this->nameForm . "_ANANOM"]['NOMSPA']) {
                    if (count($this->utentiComm == 1) && $this->utentiComm[0]['CODAGGREGATO'] == $_POST[$this->nameForm . "_ANANOM"]['NOMSPA']) {
                        Out::msgInfo("Utenti Commercio", "Utente già inserito per l'aggregato " . $this->utentiComm[0]['AGGREGATO']);
                        break;
                    }
                    $codAggr = $_POST[$this->nameForm . "_ANANOM"]['NOMSPA'];
                    $anaspa_rec = $this->praLib->GetAnaspa($_POST[$this->nameForm . "_ANANOM"]['NOMSPA']);
                    $Aggr = $anaspa_rec['SPADES'];
                }
                Out::msgInput(
                        'Scelta Utente per Collegamento al Commercio', array(
                    array(
                        'label' => array('style' => "width:70px;", 'value' => 'Utente  '),
                        'id' => $this->nameForm . '_Utente',
                        'name' => $this->nameForm . '_Utente',
                        'value' => "",
                        'size' => '20'),
                    array(
                        'label' => array('style' => "width:70px;", 'value' => 'Aggregato  '),
                        'id' => $this->nameForm . '_DescAggregato',
                        'name' => $this->nameForm . '_DescAggregato',
                        'class' => "ita-edit-lookup",
                        'value' => $Aggr,
                        'size' => '20'),
                    array(
                        'id' => $this->nameForm . '_CodAggregato',
                        'name' => $this->nameForm . '_CodAggregato',
                        'class' => "ita-hidden",
                        'value' => $codAggr),
                    array(
                        'id' => $this->nameForm . '_Rowid',
                        'name' => $this->nameForm . '_Rowid',
                        'class' => "ita-hidden",
                        'value' => ""),
                        ), array(
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiComm', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ), $this->nameForm
                );
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDipe:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridUteComm:
                        unset($this->utentiComm[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridUteComm, $this->utentiComm);
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDipe, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('NOMCOG');
                $ita_grid01->exportXLS('', 'Ananom.xls');
                break;
            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praDipe', $parameters);
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nomres':
                        $codice = $_POST[$this->nameForm . '_Nomres'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->getAnanom($codice);
                            if ($Ananom_rec) {
                                $this->Dettaglio($Ananom_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Nomres', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMRES]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMRES'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMRES]', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMSET]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMSET'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_set = $this->praLib->getAnauni($codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMSET]', $codice);
                            Out::valore($this->nameForm . '_SETTORE', $Anauni_set['UNIDES']);
                            Out::valore($this->nameForm . '_ANANOM[NOMSER]', '');
                            Out::valore($this->nameForm . '_SERVIZIO', '');
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMSER]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMSER'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_ser = $this->praLib->GetAnauniServ($_POST[$this->nameForm . '_ANANOM']['NOMSET'], $codice);
                            if ($Anauni_ser) {
                                Out::valore($this->nameForm . '_ANANOM[NOMSER]', $codice);
                                Out::valore($this->nameForm . '_SERVIZIO', $Anauni_ser['UNIDES']);
                            } else {
                                Out::valore($this->nameForm . '_ANANOM[NOMSER]', '');
                                Out::valore($this->nameForm . '_SERVIZIO', '');
                                Out::setFocus($this->nameForm . '_ANANOM[NOMSER]');
                                Out::msgInfo('ATTENZIONE', 'Codice Servizio non corretto per il Settore ' . $_POST[$this->nameForm . '_ANANOM']['NOMSET']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMQUA]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMQUA'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anaarc_rec = $this->praLib->getAnaarc("QU" . $codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMQUA]', substr($Anaarc_rec['ARCCOD'], 2));
                            Out::valore($this->nameForm . '_QUALIFICA', $Anaarc_rec['ARCDES']);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMPRO]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMPRO'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anaarc_rec = $this->praLib->getAnaarc("PP" . $codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMPRO]', substr($Anaarc_rec['ARCCOD'], 2));
                            Out::valore($this->nameForm . '_PROFILO', $Anaarc_rec['ARCDES']);
                        }
                        break;
                    case $this->nameForm . '_ANANOM[NOMDEP]':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMDEP'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $Anamed_rec = $this->proLib->getAnamed($codice);
                            Out::valore($this->nameForm . '_ANANOM[NOMDEP]', $Anamed_rec['MEDCOD']);
                            Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec['MEDNOM']);
                        }
                        break;
                }
                break;
            case 'returncat':
                $sql = "SELECT CATCOD, CATDES FROM ANACAT WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anacat_tab) != 0) {
                        Out::valore($this->nameForm . '_Catcod', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_ANACLA[CLACAT]', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                    }
//                    Out::codice('closeCurrDialog();');
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Storico':
                        if ($_POST[$this->nameForm . '_Storico'] == 0) {
                            Out::valore($this->nameForm . '_Valido', '');
                        }
                        break;
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridDipe, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridDipe]['gridParam']['rowNum']);
                            $ita_grid01->setSortIndex('NOMCOG');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridDipe);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridDipe);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->openNuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANANOM']['NOMRES'];
                        if ($codice == '') {
                            $codice = $this->prenotaCodice();
                            if (!$codice) {
                                break;
                            }
                        }
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $_POST[$this->nameForm . '_ANANOM']['NOMRES'] = $codice;
                        $Ananom_ric = $this->praLib->GetAnanom($codice);
                        if (!$Ananom_ric) {
                            $Ananom_rec = $_POST[$this->nameForm . '_ANANOM'];
                            $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                            if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Paleo') {
                                $operatore_rec = serialize($_POST[$this->nameForm . '_OperatorePaleo']);
                                $Ananom_rec['NOMMETA'] = $operatore_rec;
                            }
                            if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'WSPU') {
                                $DatiWS['ufficio'] = serialize($_POST[$this->nameForm . '_UFFICIOHWS']);
                                $Ananom_rec['NOMMETA'] = $DatiWS;
                            }

//                            $Filent_Rec_TabAss = $this->praLib->GetFilent(41);
//                            if ($Filent_Rec_TabAss['FILVAL'] == 1) {
//                                Out::addClass($this->nameForm . "_ANANOM[NOMDEP]", "required");
//                            }


                            $insert_Info = 'Oggetto: ' . $Ananom_rec['NOMRES'] . " " . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
                            if (!$this->insertRecord($this->PRAM_DB, 'ANANOM', $Ananom_rec, $insert_Info)) {
                                Out::msgStop("Attenzione!!", "Errore Inserimento dipendente");
                                break;
                            }
                            Out::msgInfo("Inserimento", "Inserito Dipendente N." . $codice);
                            if ($this->returnModel) {
                                $model = $this->returnModel;
                                $_POST = array();
                                $_POST['event'] = $this->returnEvent;
                                $_POST['id'] = $this->returnId;
                                $_POST['retKey'] = $codice;
                                $_POST['page'] = $this->page;
                                $_POST['rows'] = $this->rows;
                                $_POST['sidx'] = $this->sidx;
                                $_POST['sord'] = $this->sord;
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                                Out::closeDialog($this->nameForm);
                            } else {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANANOM[NOMRES]');
                        }

                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Ananom_rec = $_POST[$this->nameForm . '_ANANOM'];
                        $codice = $Ananom_rec['NOMRES'];
                        $Anacon_rec['CONCOD'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);

                        //aggiunte WS
                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Paleo') {
                            //aggiunta per Paleo
                            $operatore_rec = $_POST[$this->nameForm . '_OperatorePaleo']; //prende i campi CodiceUO, Cognome, Nome, Ruolo
                            $OperatorePaleo['CodiceUO'] = $operatore_rec['CodiceUO'];
                            $OperatorePaleo['Cognome'] = $operatore_rec['Cognome'];
                            $OperatorePaleo['Nome'] = $operatore_rec['Nome'];
                            $OperatorePaleo['Ruolo'] = $operatore_rec['Ruolo'];
                            $Ananom_rec['NOMMETA'] = serialize($OperatorePaleo);
                        }
                        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'WSPU') {
                            //aggiunta per WS ICCS
                            $DatiWS['ufficio'] = $_POST[$this->nameForm . '_UFFICIOHWS']; //prende i campi CodiceUO, Cognome, Nome, Ruolo
                            $Ananom_rec['NOMMETA'] = serialize($DatiWS);
                        }

                        //Se ci sono aggiungo ai metadati gli utenti del commercio
                        if ($this->utentiComm) {
                            $arrayMeta = unserialize($Ananom_rec['NOMMETA']);
                            $arrayMeta['Utenti'] = $this->utentiComm;
                            $Ananom_rec['NOMMETA'] = serialize($arrayMeta);
                        }

                        //fine aggiunta per Paleo
                        $update_Info = 'Oggetto: ' . $Ananom_rec['NOMRES'] . " " . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANANOM', $Ananom_rec, $update_Info)) {
                            if ($this->openMode == 'editFromUtenti') {
                                $this->close();
                            } else {
                                $this->OpenRicerca();
                            }
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Ananom_rec = $_POST[$this->nameForm . '_ANANOM'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Ananom_rec['NOMRES'] . " " . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANANOM', $Ananom_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA DIPENDENTI", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_SvuotaTsp':
                        Out::valore($this->nameForm . '_ANANOM[NOMTSP]', '');
                        Out::valore($this->nameForm . '_Sportello', '');
                        Out::valore($this->nameForm . '_ANANOM[NOMSPA]', '');
                        Out::valore($this->nameForm . '_Aggregato', '');

                        break;
                    case $this->nameForm . '_SvuotaSpa':
//                        if (count($this->utentiComm == 1) && $this->utentiComm[0]['CODAGGREGATO'] == $_POST[$this->nameForm . "_ANANOM"]['NOMSPA']) {
//                            Out::msgInfo("Utenti Commercio", "Utente presente per l'aggregato " . $this->utentiComm[0]['AGGREGATO'] . ".<br>Cancellare prima l'utente.");
//                            break;
//                        }
                        Out::valore($this->nameForm . '_ANANOM[NOMSPA]', '');
                        Out::valore($this->nameForm . '_Aggregato', '');
                        break;

                    case $this->nameForm . '_ConfermaDatiComm':
                        if ($_POST[$this->nameForm . "_Utente"] == "" || $_POST[$this->nameForm . "_DescAggregato"] == "") {
                            Out::msgInfo("Controllo Utenti", "Compilare entrambi i campi");
                            break;
                        }
                        if ($_POST[$this->nameForm . "_CodAggregato"] == "") {
                            Out::msgInfo("Controllo Utenti", "Aggregato non valido, utilizzare la lentina");
                            break;
                        }
                        if ($_POST[$this->nameForm . "_Rowid"] == "") {
                            $trovato = $this->CheckUtenti();
                            if ($trovato) {
                                Out::msgInfo("Controllo Utenti", "E' stato già scelto un utente per questo aggregato");
                                break;
                            }
                            $i = 0;
                            if ($this->utentiComm) {
                                $i = count($this->utentiComm);
                            }
                            $this->utentiComm[$i]['UTENTE'] = $_POST[$this->nameForm . "_Utente"];
                            $this->utentiComm[$i]['AGGREGATO'] = $_POST[$this->nameForm . "_DescAggregato"];
                            $this->utentiComm[$i]['CODAGGREGATO'] = $_POST[$this->nameForm . "_CodAggregato"];
                        } else {
                            $this->utentiComm[$_POST[$this->nameForm . "_Rowid"]]['UTENTE'] = $_POST[$this->nameForm . "_Utente"];
                            $this->utentiComm[$_POST[$this->nameForm . "_Rowid"]]['AGGREGATO'] = $_POST[$this->nameForm . "_DescAggregato"];
                            $this->utentiComm[$_POST[$this->nameForm . "_Rowid"]]['CODAGGREGATO'] = $_POST[$this->nameForm . "_CodAggregato"];
                        }
                        $this->CaricaGriglia($this->gridUteComm, $this->utentiComm);
                        break;
                    case $this->nameForm . '_ANANOM[NOMSET]_butt':
                        praRic::praRicAnauni("praDipe", "RICERCA SETTORE", "returnSettore");
                        break;
                    case $this->nameForm . '_ANANOM[NOMSER]_butt':
                        praRic::praRicAnaSer($this->PRAM_DB, "praDipe", "RICERCA SERVIZIO", "AND UNISET = '" . $_POST[$this->nameForm . '_ANANOM']['NOMSET'] . "'", "returnServizio");
                        break;
                    case $this->nameForm . '_ANANOM[NOMQUA]_butt':
                        praRic::praRicAnaarc("praDipe", "RICERCA QUALIFICHE", "returnNomqua", "WHERE " . $this->PRAM_DB->subString('ARCCOD', 1, 2) . "='QU'");
                        break;
                    case $this->nameForm . '_ANANOM[NOMPRO]_butt':
                        praRic::praRicAnaarc("praDipe", "RICERCA PROFILI PROFESSIONALI", "returnNompro", "WHERE " . $this->PRAM_DB->subString('ARCCOD', 1, 2) . "='PP'");
                        break;
                    case $this->nameForm . '_ANANOM[NOMDEP]_butt':
                        $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed("praDipe", "WHERE $filtroUff");
                        break;
                    case $this->nameForm . '_ANANOM[NOMTSP]_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . '_DescAggregato_butt':
                        praRic::praRicAnaspa($this->nameForm, "", "UTECOMM");
                        break;
                    case $this->nameForm . '_ANANOM[NOMSPA]_butt':
//                        praRic::praRicAnaspa($this->nameForm, "WHERE SPATSP=" . $_POST[$this->nameForm . '_ANANOM']['NOMTSP']);
                        praRic::praRicAnaspa($this->nameForm, "");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case "returnAnaarc":
                switch ($_POST["retid"]) {
                    case "returnNomqua":
                        $Anaarc_rec = $this->praLib->GetAnaarc($_POST["retKey"], 'rowid');
                        if ($Anaarc_rec) {
                            Out::valore($this->nameForm . '_ANANOM[NOMQUA]', substr($Anaarc_rec["ARCCOD"], 2));
                            Out::valore($this->nameForm . '_QUALIFICA', $Anaarc_rec["ARCDES"]);
                        }
                        break;
                    case "returnNompro":
                        $Anaarc_rec = $this->praLib->GetAnaarc($_POST["retKey"], 'rowid');
                        if ($Anaarc_rec) {
                            Out::valore($this->nameForm . '_ANANOM[NOMPRO]', substr($Anaarc_rec["ARCCOD"], 2));
                            Out::valore($this->nameForm . '_PROFILO', $Anaarc_rec["ARCDES"]);
                        }
                        break;
                }
                break;
            case "returnUniset":
                $Anauni_set = $this->praLib->getAnauni($_POST["retKey"], 'rowid');
                if ($Anauni_set) {
                    Out::valore($this->nameForm . '_ANANOM[NOMSET]', $Anauni_set['UNISET']);
                    Out::valore($this->nameForm . '_SETTORE', $Anauni_set['UNIDES']);
                    Out::valore($this->nameForm . '_ANANOM[NOMSER]', '');
                    Out::valore($this->nameForm . '_SERVIZIO', '');
                }
                break;
            case "returnUniser":
                $Anauni_ser = $this->praLib->getAnauni($_POST["retKey"], 'rowid');
                if ($Anauni_ser) {
                    Out::valore($this->nameForm . '_ANANOM[NOMSER]', $Anauni_ser['UNISER']);
                    Out::valore($this->nameForm . '_SERVIZIO', $Anauni_ser['UNIDES']);
                }
                break;
            case "returnanamed":
                $Anamed_rec = $this->proLib->GetAnamed($_POST["retKey"], 'rowid');
                if ($Anamed_rec) {
                    Out::valore($this->nameForm . '_ANANOM[NOMDEP]', $Anamed_rec["MEDCOD"]);
                    Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec["MEDNOM"]);
                }
                break;

            case "returnAnatsp":
                $Anatsp_rec = $this->praLib->GetAnatsp($_POST["retKey"], 'rowid');
                if ($Anatsp_rec) {
                    Out::valore($this->nameForm . '_ANANOM[NOMTSP]', $Anatsp_rec['TSPCOD']);
                    Out::valore($this->nameForm . '_Sportello', $Anatsp_rec['TSPDES']);
                }
                break;
            case "returnAnaspa":
                $Anaspa_rec = $this->praLib->GetAnaspa($_POST["retKey"], 'rowid');
                if ($Anaspa_rec) {
                    Out::valore($this->nameForm . '_ANANOM[NOMSPA]', $Anaspa_rec['SPACOD']);
                    Out::valore($this->nameForm . '_Aggregato', $Anaspa_rec['SPADES']);
                }
                break;
            case "returnAnaspaUTECOMM":
                App::log($_POST);
                $Anaspa_rec = $this->praLib->GetAnaspa($_POST["retKey"], 'rowid');
                if ($Anaspa_rec) {
                    Out::valore($this->nameForm . '_CodAggregato', $Anaspa_rec['SPACOD']);
                    Out::valore($this->nameForm . '_DescAggregato', $Anaspa_rec['SPADES']);
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey('praDipe_openMode');
        App::$utente->removeKey('praDipe_returnModel');
        App::$utente->removeKey('praDipe_returnEvent');
        App::$utente->removeKey('praDipe_returnId');
        App::$utente->removeKey('praDipe_page');
        App::$utente->removeKey('praDipe_rows');
        App::$utente->removeKey('praDipe_sidx');
        App::$utente->removeKey('praDipe_sord');
        App::$utente->removeKey('praDipe_utentiComm');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function CheckUtenti() {
        foreach ($this->utentiComm as $utente) {
            if ($utente["CODAGGREGATO"] == $_POST[$this->nameForm . "_CodAggregato"]) {
                return true;
            }
        }
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "";
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANANOM WHERE ROWID=ROWID";

        if ($_POST[$this->nameForm . '_Nomres'] != "") {
            $sql .= " AND NOMRES = '" . $_POST[$this->nameForm . '_Nomres'] . "'";
        }

        if ($_POST[$this->nameForm . '_Nomcog'] != "") {
            $sql .= " AND NOMCOG LIKE '%" . addslashes($_POST[$this->nameForm . '_Nomcog']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Nomnom'] != "") {
            $sql .= " AND NOMNOM LIKE '%" . addslashes($_POST[$this->nameForm . '_Nomnom']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDipe);
        TableView::clearGrid($this->gridDipe);
        TableView::disableEvents($this->gridUteComm);
        TableView::clearGrid($this->gridUteComm);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Nomres');
        $this->utentiComm = array();

        /*
         * Se attiva l'asseganzione dei passi, rendo obbligatorio il campo destinatario protocollo
         */
        Out::required($this->nameForm . "_ANANOM[NOMDEP]", false, false);
        $Filent_Rec_TabAss = $this->praLib->GetFilent(41);
        if ($Filent_Rec_TabAss['FILVAL'] == 1) {
            Out::required($this->nameForm . "_ANANOM[NOMDEP]");
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Paleo') {
            Out::show($this->nameForm . '_divDatiPaleo');
        } else {
            Out::hide($this->nameForm . '_divDatiPaleo');
        }
        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'WSPU') {
            Out::show($this->nameForm . '_divDatiICCS');
        } else {
            Out::hide($this->nameForm . '_divDatiICCS');
        }
    }

    public function Dettaglio($_Indice) {
        $Ananom_rec = $this->praLib->GetAnanom($_Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Ananom_rec['NOMRES'] . " " . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
        $this->openRecord($this->PRAM_DB, 'ANANOM', $open_Info);
        $this->Nascondi();
        Out::valori($Ananom_rec, $this->nameForm . '_ANANOM');
        Out::show($this->nameForm . '_Aggiorna');
        if ($this->openMode == 'editFromUtenti') {
            Out::hide($this->nameForm . '_Cancella');
            Out::hide($this->nameForm . '_AltraRicerca');
        } else {
            Out::show($this->nameForm . '_Cancella');
            Out::show($this->nameForm . '_AltraRicerca');
            Out::show($this->nameForm . '_Torna');
        }
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::attributo($this->nameForm . '_ANANOM[NOMRES]', 'readonly', '0');

        //Dati Paleo
        $op_rec = unserialize($Ananom_rec['NOMMETA']);
        Out::valori($op_rec, $this->nameForm . '_OperatorePaleo');
        //Dati ICCS
        $ufficio_rec = unserialize($Ananom_rec['NOMMETA']);
        Out::valore($this->nameForm . '_UFFICIOHWS', $ufficio_rec['ufficio']);
        //Utenti Commercio
        $arrayMeta = unserialize($Ananom_rec['NOMMETA']);
        if ($arrayMeta['Utenti']) {
            $this->utentiComm = $arrayMeta['Utenti'];
            $this->CaricaGriglia($this->gridUteComm, $this->utentiComm);
        }
        //
        $Anauni_set = $this->praLib->getAnauni($Ananom_rec['NOMSET']);
        Out::valore($this->nameForm . '_SETTORE', $Anauni_set['UNIDES']);
        $Anauni_ser = $this->praLib->GetAnauniServ($Ananom_rec['NOMSET'], $Ananom_rec['NOMSER']);
        Out::valore($this->nameForm . '_SERVIZIO', $Anauni_ser['UNIDES']);
        $AnaarcQU_rec = $this->praLib->getAnaarc("QU" . $Ananom_rec['NOMQUA']);
        Out::valore($this->nameForm . '_QUALIFICA', $AnaarcQU_rec['ARCDES']);
        $AnaarcPP_rec = $this->praLib->getAnaarc("PP" . $Ananom_rec['NOMPRO']);
        Out::valore($this->nameForm . '_PROFILO', $AnaarcPP_rec['ARCDES']);
        $Anamed_rec = $this->proLib->getAnamed($Ananom_rec['NOMDEP']);
        Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec['MEDNOM']);

        $Anatsp_rec = $this->praLib->getAnatsp($Ananom_rec['NOMTSP']);
        Out::valore($this->nameForm . '_Sportello', $Anatsp_rec['TSPDES']);

        $Anaspa_rec = $this->praLib->getAnaspa($Ananom_rec['NOMSPA']);
        Out::valore($this->nameForm . '_Aggregato', $Anaspa_rec['SPADES']);

        Out::setFocus('', $this->nameForm . '_ANANOM[NOMCOG]');
        TableView::disableEvents($this->gridDipe);

//        $op_rec['ROWID'] = $operatore_rec['ROWID'];
//        Out::valori($op_rec, $this->nameForm . '_OperatorePaleo');
    }

    function GetAnacla($_Cond, $_Codice) {
        $sql = "SELECT ROWID FROM ANACLA WHERE $_Cond AND CLACCA='$_Codice'";
        $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anacla_tab;
    }

    function openNuovo() {
        Out::attributo($this->nameForm . '_ANANOM[NOMRES]', 'readonly', '1');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_ANANOM[NOMRES]');
    }

    function prenotaCodice() {
        $retLock = ItaDB::DBLock($this->PRAM_DB, "FILENT", "1", "", 20);
        if (!$retLock) {
            return false;
        }
        $sql = "SELECT MAX(NOMRES) AS NOMRES FROM ANANOM";
        $Ananom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($Ananom_rec['NOMRES'] == '999999')
            $Ananom_rec['NOMRES'] = '000000';
        $codice = $Ananom_rec['NOMRES'] + 1;
        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            Out::msgStop('Errore', 'Sblocco Tabella DIPENDENTI non Riuscito.');
            return false;
        }
        return $codice;
    }

    function lockFilent($rowid) {
        $retLock = ItaDB::DBLock($this->getPRAMDB(), "FILENT", $rowid, "", 20);
        if ($retLock['status'] != 0) {
            Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVI PRATICHE non Riuscito.');
            return false;
        }
        return $retLock;
    }

    function unlockFilent($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            Out::msgStop('Errore', 'Sblocco Tabella PROGRESSIVI PRATICHE non Riuscito.');
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

}

?>