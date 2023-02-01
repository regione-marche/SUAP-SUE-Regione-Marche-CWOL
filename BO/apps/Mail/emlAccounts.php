<?php

/**
 *
 * Archivio Gestione Account Email
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    26.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';

function emlAccounts() {
    $emlAccounts = new emlAccounts();
    $emlAccounts->parseEvent();
    return;
}

class emlAccounts extends itaModel {

    public $ITALWEB;
    public $emlLib;
    public $nameForm = "emlAccounts";
    public $divGes = "emlAccounts_divGestione";
    public $divRis = "emlAccounts_divRisultato";
    public $divRic = "emlAccounts_divRicerca";
    public $gridMail = "emlAccounts_gridMail";
    public $gridFiltri = "emlAccounts_gridFiltri";
    public $gridMailAutoriz = "emlAccounts_gridMailAutoriz";
    public $filtri = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = $this->emlLib->getITALWEB();
            Out::block($this->nameForm . '_divBlock');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->filtri = App::$utente->getKey($this->nameForm . '_filtri');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_filtri', $this->filtri);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                Out::tabRemove($this->nameForm . "_tabAccount", $this->nameForm . "_paneFiltri");
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridMail:
                        $this->Dettaglio($_POST['rowid']);
                        Out::show($this->nameForm . '_Torna');
                        break;
                    case $this->gridFiltri:
                        $this->DettaglioFiltri($_POST['rowid']);
                        Out::show($this->nameForm . "_divFormFiltri");
                        break;
                    case $this->gridMailAutoriz:
                        $Mail_Autorizzazioni_rec = $this->emlLib->GetMailAutorizzazioni($_POST['rowid'], 'rowid');
                        $this->GetCampiAutorizzazioni($Mail_Autorizzazioni_rec);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridFiltri:
                        $this->AzzeraVariabiliFiltri();
                        Out::show($this->nameForm . "_divFormFiltri");
                        break;
                    case $this->gridMailAutoriz:
                        $this->GetCampiAutorizzazioni();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridMail:
                        $this->Dettaglio($_POST['rowid']);
                        Out::show($this->nameForm . '_Torna');
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridFiltri:
                        Out::msgQuestion("Cancellazione", "L'operazione è irreversibile. Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaFiltro', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaFiltro', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridMail:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                    case $this->gridFiltri:
                        break;
                    case $this->gridMailAutoriz:
                        $mailaddr = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['MAILADDR'];
                        $this->CaricaGrigliaMailAutoriz($mailaddr);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_MAIL_ACCOUNT[DOMAIN]':
                        $domain = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['DOMAIN'];
                        $this->setDomainValue($domain);
                        break;
                    case $this->nameForm . '_MAIL_ACCOUNT[MAILADDR]':
                        $mailaddr = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['MAILADDR'];
                        $mailaddrExp = explode('@', $mailaddr);
                        if (isset($mailaddrExp[1])) {
                            $this->setDomainValue($mailaddrExp[1]);
                        }
                        break;
                    case $this->nameForm . '_MAIL_DOMAIN[DELMSG]':
                        if ($_POST[$this->nameForm . '_MAIL_DOMAIN']['DELMSG'] == 0) {
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '');
                            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '0');
                        } else {
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '7');
                            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '1');
                        }
                        break;
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridMail, array(
                            'sqlDB' => $this->ITALWEB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridMail]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('NAME');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridMail);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['MAILADDR'];
                        $mailAccount_rec = $this->emlLib->getMailAccount($codice);
                        if (!$mailAccount_rec) {
                            $mailAccount_rec = $_POST[$this->nameForm . '_MAIL_ACCOUNT'];
                            $mailAccount_rec['CUSTOMHEADERS'] = $this->setCustomHeaders();
                            $insert_Info = 'Oggetto: ' . $mailAccount_rec['MAILADDR'] . " "
                                    . $mailAccount_rec['NAME'];
                            if ($this->insertRecord($this->ITALWEB, 'MAIL_ACCOUNT', $mailAccount_rec, $insert_Info)) {
                                $this->Dettaglio($mailAccount_rec['MAILADDR'], 'indirizzo');
                            }
                        } else {
                            Out::msgInfo("Indirizzo email già  presente", "Inserire un nuovo indirizzo.");
                            Out::setFocus('', $this->nameForm . '_MAIL_ACCOUNT[MAILADDR]');
                        }
                        break;
                    case $this->nameForm . '_Registra':
                        $mailAccount_rec = $_POST[$this->nameForm . '_MAIL_ACCOUNT'];
                        if (!$this->RegistraFiltri()) {
                            Out::msgStop("Attenzione!!", "Errore aggiornamento filtro");
                            break;
                        }
                        $this->emlLib->ordinaFiltri($mailAccount_rec['MAILADDR']);
                        $this->Dettaglio($mailAccount_rec['ROWID']);
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $mailFiltri_rec = $_POST[$this->nameForm . "_MAIL_FILTRI"];
                        if ($mailFiltri_rec['NOME']) {
                            Out::msgInfo("Attenzione!!", "E' presente un filtro non salvato.<br>Per salvarlo premere il bottone registra.");
                            break;
                        }
                        $mailAccount_rec = $_POST[$this->nameForm . '_MAIL_ACCOUNT'];
                        if ($mailAccount_rec['DELMSG'] == 1 && $mailAccount_rec['DELWAIT'] < 7) {
                            $mailAccount_rec['DELWAIT'] = 7;
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[WAITMSG]', 7);
                        }
                        $mailAccount_rec['CUSTOMHEADERS'] = $this->setCustomHeaders();

                        $update_Info = 'Oggetto: ' . $mailAccount_rec['MAILADDR'] . " " . $mailAccount_rec['NAME'];
                        if ($this->updateRecord($this->ITALWEB, 'MAIL_ACCOUNT', $mailAccount_rec, $update_Info)) {

                            $this->Dettaglio($mailAccount_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancellaFiltro':
                        $mailFiltri_rec = $this->emlLib->getMailFiltri($_POST[$this->gridFiltri]['gridParam']['selrow'], 'rowid');
                        $delete_Info = 'Oggetto: cancello filtro ' . $mailFiltri_rec['NOME'] . " di " . $mailFiltri_rec['ACCOUNT'];
                        if (!$this->deleteRecord($this->ITALWEB, 'MAIL_FILTRI', $mailFiltri_rec['ROWID'], $delete_Info)) {
                            Out::msgStop("Attenzione!!", "cancellazione filtro fallita");
                            break;
                        }
                        $this->Dettaglio($_POST[$this->nameForm . "_MAIL_ACCOUNT"]['ROWID']);
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $mailAccount_rec = $_POST[$this->nameForm . '_MAIL_ACCOUNT'];
                        $delete_Info = 'Oggetto: ' . $mailAccount_rec['MAILADDR'] . " " . $mailAccount_rec['NAME'];
                        if ($this->deleteRecord($this->ITALWEB, 'MAIL_ACCOUNT', $mailAccount_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_ApriAttivaExpr':
                        $model = 'emlFiltri';
                        $metadata = $_POST[$this->nameForm . "_MAIL_FILTRI"]['METADATA'];
                        $_POST = array();
                        $_POST['dati'] = array(
                            "CAMPI" => array(
                                'mittente' => "Mittente",
                                'pecTipo' => "PecTipo",
                                'oggetto' => "Oggetto"
                            ),
                            "OPERATORI" => array(
                                'uguale' => "Uguale a",
                                'contiene' => "Contiene"
                            ),
                            "METADATA" => $metadata
                        );
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnAttivaExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridMail);
                        break;

                    case $this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]_butt':
                        include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
                        accRic::accRicUtenti($this->nameForm);
                        break;

                    case $this->nameForm . '_ConfermaAutoMail':
                        $Mail_Autorizzazioni = $_POST[$this->nameForm . '_MAIL_AUTORIZZAZIONI'];
                        if (!$Mail_Autorizzazioni['LOGIN'] || !$Mail_Autorizzazioni['DADATA']) {
                            Out::msgInfo('Attenzione', "Utente e Data inizio validità sono obbligatori.");
                            break;
                        }
                        /*
                         * Check utente Valido
                         */
                        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
                        $accLib = new accLib();
                        if (!$accLib->GetUtenti($Mail_Autorizzazioni['LOGIN'], 'utelog')) {
                            Out::msgStop('Attenzione', "Utente " . $Mail_Autorizzazioni['LOGIN'] . " non valido.");
                            break;
                        }
                        /*
                         * Chiudo Finestra Input
                         */
                        Out::closeCurrentDialog();
                        /* Controllo Presenza Account Mail */
                        $Mail = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['MAILADDR'];
                        if (!$Mail) {
                            Out::msgStop('Attenzione', "Mail account non definito.");
                            break;
                        }
                        /*
                         * Controllo se Inserimento o Aggiornamento
                         */
                        if ($Mail_Autorizzazioni['ROWID']) {
                            /* Aggiornamento Dati */
                            $update_info = "Oggetto: aggiorno mail atuorizzazioni: " . $Mail_Autorizzazioni['LOGIN'] . ' ROWID: ' . $Mail_Autorizzazioni['ROWID'];
                            if (!$this->updateRecord($this->ITALWEB, 'MAIL_AUTORIZZAZIONI', $Mail_Autorizzazioni, $update_info)) {
                                Out::msgStop("Inserimento filtro", "Inserimento data set MAIL_AUTORIZZAZIONI fallito");
                                return false;
                            }
                        } else {
                            /* Inserimento Record: */
                            $Mail_Autorizzazioni['MAIL'] = $Mail;
                            $insert_Info = "Oggetto: inserisco mail atuorizzazioni: " . $Mail_Autorizzazioni['LOGIN'] . ' Account: ' . $Mail;
                            if (!$this->insertRecord($this->ITALWEB, 'MAIL_AUTORIZZAZIONI', $Mail_Autorizzazioni, $insert_Info)) {
                                Out::msgStop("Inserimento filtro", "Inserimento data set MAIL_AUTORIZZAZIONI fallito");
                                return false;
                            }
                        }
                        $this->CaricaGrigliaMailAutoriz($Mail);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_TestInvio':
                        $this->TestInvio();
                        break;
                    case $this->nameForm . '_ConfermaTestInvio':
                        $this->ConfermaTestInvio();
                        break;
                }
                break;
            case "returnAttivaExpr":
                Out::valore($this->nameForm . '_AttivaEspressione', $this->emlLib->DecodificaControllo($_POST['dati']['METADATA']));
                Out::valore($this->nameForm . '_MAIL_FILTRI[METADATA]', $_POST['dati']['METADATA']);
                break;

            case "returnutenti":
                include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
                $accLib = new accLib();
                $Utente_rec = $accLib->GetUtenti($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]', $Utente_rec['UTELOG']);
                break;
        }
    }

    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_filtri');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, '', "1", '');
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, 'ssl', "0", 'ssl');
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, 'tls', "0", 'tls');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, '', "1", '');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, 'ssl', "0", 'ssl');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, 'tls', "0", 'tls');
        $domain_tab = $this->emlLib->getGenericTab("SELECT * FROM MAIL_DOMAIN");
        $selezionato = '1';
        foreach ($domain_tab as $domain_rec) {
            Out::select($this->nameForm . '_MAIL_ACCOUNT[DOMAIN]', 1, $domain_rec['NAME'], $selezionato, $domain_rec['NAME']);
            $selezionato = '0';
        }

        Out::select($this->nameForm . '_MAIL_FILTRI[CLASSIFICA]', 1, '', "1", '');
        Out::select($this->nameForm . '_MAIL_FILTRI[CLASSIFICA]', 1, '@SPORTELLO_DA_CONTROLLARE@', "1", 'Sportello da Controllare');
        Out::select($this->nameForm . '_MAIL_FILTRI[CLASSIFICA]', 1, '@SPORTELLO_SCARTATO@', "1", 'Sportello Scartato');
        Out::select($this->nameForm . '_MAIL_FILTRI[CLASSIFICA]', 1, 'BLOCCA_FILTRI', "1", 'Blocca Filtri');
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        Out::hide($this->nameForm . '_TestInvio');
        $this->AzzeraVariabili();
        $this->AzzeraVariabiliFiltri();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_MAIL_ACCOUNT[ROWID]', '');
        TableView::disableEvents($this->gridMail);
        TableView::clearGrid($this->gridMail);
        TableView::disableEvents($this->gridFiltri);
        TableView::clearGrid($this->gridFiltri);
        TableView::clearGrid($this->gridMailAutoriz);
    }

    function AzzeraVariabiliFiltri() {
        Out::clearFields($this->nameForm, $this->nameForm . "_divFormFiltri");
        Out::valore($this->nameForm . '_MAIL_FILTRI[ROWID]', '');
        Out::valore($this->nameForm . '_MAIL_FILTRI[METADATA]', '');
//        TableView::disableEvents($this->gridFiltri);
//        TableView::clearGrid($this->gridFiltri);
        $this->filtri = array();
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_TestInvio');
    }

    function Nuovo() {
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->AzzeraVariabili();
        $this->AzzeraVariabiliFiltri();
        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_MAIL_ACCOUNT[NAME]');
    }

    function CreaSql() {
        $sql = "SELECT * FROM MAIL_ACCOUNT WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND ACCOUNT LIKE '%" . addslashes($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND MAILADDR LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }
//        App::log($sql);
        return $sql;
    }

    public function CreaSqlMailAutoriz($MailAccount = '') {
        $sql = "SELECT * FROM MAIL_AUTORIZZAZIONI WHERE MAIL='$MailAccount' ";
        return $sql;
    }

    function DettaglioFiltri($indice, $tipo = 'rowid') {
        $mailFiltri_rec = $this->emlLib->getMailFiltri($indice, $tipo);
        $open_Info = 'Oggetto: ' . $mailFiltri_rec['NOME'] . " " . $mailFiltri_rec['ACCOUNT'];
        $this->openRecord($this->ITALWEB, 'MAIL_FILTRI', $open_Info);
        Out::valori($mailFiltri_rec, $this->nameForm . '_MAIL_FILTRI');
        Out::valore($this->nameForm . '_AttivaEspressione', $this->emlLib->DecodificaControllo($mailFiltri_rec['METADATA']));
    }

    function Dettaglio($indice, $tipo = 'rowid') {
        Out::clearFields($this->nameForm . "_divFormFiltri");
        $mailAccount_rec = $this->emlLib->getMailAccount($indice, $tipo);
        $open_Info = 'Oggetto: ' . $mailAccount_rec['NAME'] . " " . $mailAccount_rec['MAILADDR'];
        $this->openRecord($this->ITALWEB, 'MAIL_ACCOUNT', $open_Info);
        $this->Nascondi();
        Out::valori($mailAccount_rec, $this->nameForm . '_MAIL_ACCOUNT');
        Out::valori($mailAccount_rec, $this->nameForm . '_MAIL_DOMAIN');
        $customHeaders = unserialize($mailAccount_rec['CUSTOMHEADERS']);
        $ricevutaBreve = 0;
        foreach ($customHeaders as $key => $header) {
            if ($key == emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA && $header == emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE) {
                $ricevutaBreve = 1;
                break;
            }
        }
        Out::valore($this->nameForm . '_AccettazioneBreve', $ricevutaBreve);
        if ($mailAccount_rec['ISPEC'] == 1) {
            Out::show($this->nameForm . '_AccettazioneBreve_field');
        } else {
            Out::hide($this->nameForm . '_AccettazioneBreve_field');
        }
        if ($mailAccount_rec['DELMSG'] == 0) {
            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '');
            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '0');
        } else {
            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '1');
        }

        $this->CaricaGrigliaMailAutoriz($mailAccount_rec['MAILADDR']);

        $this->caricaFiltri($mailAccount_rec['MAILADDR']);
        if ($this->checkExistProtocollo()) {
            Out::tabDisable($this->nameForm . "_tabAccount", $this->nameForm . "_paneFiltri");
        } else {
            Out::tabEnable($this->nameForm . "_tabAccount", $this->nameForm . "_paneFiltri");
        }

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->nameForm . "_divFormFiltri");
        Out::show($this->divGes);
        Out::show($this->nameForm . '_TestInvio');

        TableView::disableEvents($this->gridMail);
        Out::setFocus('', $this->nameForm . '_MAIL_ACCOUNT[NAME]');
    }

    public function checkExistProtocollo() {
        try {
            $protDB = ItaDB::DBOpen('PROT');
            $record = ItaDB::DBSQLSelect($protDB, "SHOW TABLES FROM " . $protDB->getDB() . " LIKE 'ANAENT'");
        } catch (Exception $exc) {
            //out::msgStop("Attenzione", $exc->getMessage());
        }
        if ($protDB == "") {
            return false;
        } else {
            if (!$record) {
                return false;
            } else {
                if (!file_exists(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php')) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    public function caricaFiltri($account) {
        $this->filtri = $this->emlLib->getMailFiltri($account, "account", true);
        $filtri = $this->filtri;
        foreach ($filtri as $key => $filtro) {
            if ($filtro['CLASSIFICA'] == "@SPORTELLO_SCARTATO@") {
                $filtri[$key]['DESC_CLASSIFICA'] = "Sportello Scartato";
            } else if ($filtro['CLASSIFICA'] == "@SPORTELLO_DA_CONTROLLARE@") {
                $filtri[$key]['DESC_CLASSIFICA'] = "Sportello da Controllare";
            } else if ($filtro['CLASSIFICA'] == "BLOCCA_FILTRI") {
                $filtri[$key]['DESC_CLASSIFICA'] = "Blocca Filtri";
            }
        }
        $this->CaricaGriglia($this->gridFiltri, $filtri);
    }

    public function RegistraFiltri() {
        $mailFiltri_rec = $_POST[$this->nameForm . "_MAIL_FILTRI"];
        if ($mailFiltri_rec) {
            $mailFiltri_rec['ACCOUNT'] = $_POST[$this->nameForm . "_MAIL_ACCOUNT"]['MAILADDR'];
            if ($mailFiltri_rec['SEQUENZA'] == "") {
                $mailFiltri_rec['SEQUENZA'] = 99999;
            }
            if ($mailFiltri_rec['ROWID'] == "") {
                $insert_Info = "Oggetto: inserisco filtro " . $mailFiltri_rec['NOME'] . " per account " . $mailFiltri_rec['ACCOUNT'];
                if (!$this->insertRecord($this->ITALWEB, 'MAIL_FILTRI', $mailFiltri_rec, $insert_Info)) {
                    Out::msgStop("Inserimento filtro", "Inserimento data set MAIL_FILTRI fallito");
                    return false;
                }
            } else {
                $update_Info = "Oggetto: aggiorno filtro " . $mailFiltri_rec['NOME'] . " per account " . $mailFiltri_rec['ACCOUNT'];
                if (!$this->updateRecord($this->ITALWEB, 'MAIL_FILTRI', $mailFiltri_rec, $update_Info)) {
                    Out::msgStop("Aggiornamento filtro", "Aggiornamento data set MAIL_FILTRI fallito");
                    return false;
                }
            }
        }
        return true;
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1' || $_POST['page'] == 0) {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    public function CaricaGrigliaMailAutoriz($MailAccount = '') {
        $Mail_Autorizzazioni_tab = $this->emlLib->GetMailAutorizzazioni($MailAccount, 'mail', false, true);
        $this->CaricaGriglia($this->gridMailAutoriz, $Mail_Autorizzazioni_tab, '1');
    }

    private function setDomainValue($domain) {
        if ($domain != '') {
            $domain_rec = $this->emlLib->getMailDomain($domain);
            if ($domain_rec) {
                Out::valore($this->nameForm . '_MAIL_DOMAIN[SMTPHOST]', $domain_rec['SMTPHOST']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3HOST]', $domain_rec['POP3HOST']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[SMTPPORT]', $domain_rec['SMTPPORT']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3PORT]', $domain_rec['POP3PORT']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', $domain_rec['SMTPSECURE']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', $domain_rec['POP3SECURE']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3REALM]', $domain_rec['POP3REALM']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3AUTHM]', $domain_rec['POP3AUTHM']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[POP3WORKST]', $domain_rec['POP3WORKST']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[DELMSG]', $domain_rec['DELMSG']);
                Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', $domain_rec['DELWAIT']);
                Out::valore($this->nameForm . '_MAIL_ACCOUNT[DOMAIN]', $domain);
                if ($domain_rec['ISPEC'] == 1) {
                    Out::show($this->nameForm . '_AccettazioneBreve_field');
                } else {
                    Out::hide($this->nameForm . '_AccettazioneBreve_field');
                }

                return $domain_rec;
            }
        }
        return false;
    }

    private function setCustomHeaders() {
        $ret = '';
        $customHeaders = array();
        if ($_POST[$this->nameForm . '_AccettazioneBreve']) {
            $customHeaders[emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA] = emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE;
        }
        if (count($customHeaders)) {
            $ret = serialize($customHeaders);
        }
        return $ret;
    }

    private function GetCampiAutorizzazioni($Mail_Autorizzazioni = array()) {
        // ROWID
        $valori[] = array(
            'label' => array(
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[ROWID]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[ROWID]',
            'type' => 'text',
            'class' => 'invisible',
            'style' => 'width:50px;',
            'value' => $Mail_Autorizzazioni['ROWID']
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Utente:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]',
            'type' => 'text',
            'class' => 'ita-edit-lookup',
            'style' => 'width:140px;',
            'value' => $Mail_Autorizzazioni['LOGIN']
        );
        $valori[] = array(
            'label' => array(
                'value' => "<b>Da Data:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[DADATA]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[DADATA]',
            'type' => 'text',
            'class' => 'ita-datepicker',
            'style' => 'width:100px;',
            'value' => $Mail_Autorizzazioni['DADATA']
        );
        $valori[] = array(
            'label' => array(
                'value' => "<b>A Data:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[ADATA]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[ADATA]',
            'type' => 'text',
            'class' => 'ita-datepicker',
            'style' => 'width:100px;',
            'value' => $Mail_Autorizzazioni['ADATA']
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Abilita Invio:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_SEND]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_SEND]',
            'type' => 'checkbox',
            'class' => 'ita-edit ita-checkbox',
            'style' => ''
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Abilita Protocollazione:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_REC]',
            'name' => $this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_REC]',
            'type' => 'checkbox',
            'class' => 'ita-edit ita-checkbox',
            'style' => ''
        );

        $messaggio = "Indicare l'utente che deve essere autorizzato all'utilizzo di questa PEC/Mail.";
        Out::msgInput(
                'Autorizzazioni Mail', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAutoMail', 'class' => 'ita-button-validate', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAutoMail', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '400', true, "<span style=\"font-size:1.0em;font-weight:bold;\">$messaggio</span>"
        );
        Out::valore($this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_REC]', $Mail_Autorizzazioni['PERM_REC']);
        Out::valore($this->nameForm . '_MAIL_AUTORIZZAZIONI[PERM_SEND]', $Mail_Autorizzazioni['PERM_SEND']);
        Out::setFocus('', $this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]');
        Out::hide($this->nameForm . '_MAIL_AUTORIZZAZIONI[ROWID]_field');
        if ($Mail_Autorizzazioni['ROWID']) {
            Out::disableField($this->nameForm . '_MAIL_AUTORIZZAZIONI[LOGIN]');
        }
    }

    private function TestInvio() {
        Out::unBlock($this->nameForm . '_divBlock');
        $fields = array(
            array(
                'label' => array(
                    'value' => "Destinatario",
                    'style' => 'width:150px;'
                ),
                'id' => $this->nameForm . '_DESTINATARIO_TESTINVIO',
                'name' => $this->nameForm . '_DESTINATARIO_TESTINVIO',
                'class' => 'ita-edit',
                'size' => 60
            )
        );

        Out::msgInput('Inserisci Destinatario', $fields, array(
            'Invia' => array(
                'id' => $this->nameForm . '_ConfermaTestInvio',
                'model' => $this->nameForm,
                'class' => 'ita-button'
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_AnnullaTestInvio',
                'model' => $this->nameForm,
                'class' => 'ita-button'
            )), $this->nameForm . "_workSpace"
        );
    }

    private function ConfermaTestInvio() {
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';

        $mittente = $_POST[$this->nameForm . '_MAIL_ACCOUNT']['MAILADDR'];
        $destinatario = $_POST[$this->nameForm . '_DESTINATARIO_TESTINVIO'];

        if (!$mittente) {
            Out::msgStop("Errore", "Indirizzo Mittente non inserito");
            return false;
        }
        if (!$destinatario) {
            Out::msgStop("Errore", "Indirizzo Destinatario non inserito");
            return false;
        }

        $emlMailBox = emlMailBox::getInstance($mittente);

        if (!$emlMailBox) {
            Out::msgStop("Errore", "Account non trovato per indirizzo " . $mittente);
            return;
        }

        // prende email
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            Out::msgStop("Errore", "Errore creazione newEmlOutgoingMessage");

            return;
        }
        $outgoingMessage->setSubject("Test invio");
        $outgoingMessage->setBody("Test invio");
        $outgoingMessage->setEmail($destinatario);
        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
        if ($mailSent) {
            Out::msgInfo("Mail Inviata", "Mail Inviata");
        } else {
            Out::msgStop("Errore", $emlMailBox->getLastMessage());
        }
    }

}

?>