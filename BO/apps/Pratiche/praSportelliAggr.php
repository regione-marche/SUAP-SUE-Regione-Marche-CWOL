<?php

/**
 *
 * ANAGRAFICA SPORTELLI AGGREGATI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    14.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

function praSportelliAggr() {
    $praSportelliAggr = new praSportelliAggr();
    $praSportelliAggr->parseEvent();
    return;
}

class praSportelliAggr extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $accLib;
    public $emlLib;
    public $nameForm = "praSportelliAggr";
    public $divGes = "praSportelliAggr_divGestione";
    public $divRis = "praSportelliAggr_divRisultato";
    public $divRic = "praSportelliAggr_divRicerca";
    public $gridAggregati = "praSportelliAggr_gridAggregati";
    public $funzione;
    public $returnModel;
    public $returnMethod;
    public $appoggio;
    public $classProtocollo;
    public $classFascicolazione;
    public $classMail;
    public $classProcediMarche;

    const PROCEDI_MARCHE = "PROCEDIMARCHE";

//    public $currSportello;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->accLib = new accLib();
            $this->emlLib = new emlLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->funzione = App::$utente->getKey($this->nameForm . '_funzione');
            $this->appoggio = App::$utente->getKey($this->nameForm . "_appoggio");
            $this->classProtocollo = App::$utente->getKey($this->nameForm . '_classProtocollo');
            $this->classFascicolazione = App::$utente->getKey($this->nameForm . '_classFascicolazione');
            $this->classMail = App::$utente->getKey($this->nameForm . '_classMail');
            $this->classProcediMarche = App::$utente->getKey($this->nameForm . '_classProcediMarche');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_funzione', $this->funzione);
            App::$utente->setKey($this->nameForm . "_appoggio", $this->appoggio);
            App::$utente->setKey($this->nameForm . '_classProtocollo', $this->classProtocollo);
            App::$utente->setKey($this->nameForm . '_classFascicolazione', $this->classFascicolazione);
            App::$utente->setKey($this->nameForm . '_classMail', $this->classMail);
            App::$utente->setKey($this->nameForm . '_classProcediMarche', $this->classProcediMarche);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->CreaComboProcediMarche();
                switch ($_POST['funzione']) {
                    case 'modifica':
                        $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                        $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                        $this->funzione = $_POST['funzione'];
//                        $this->currSportello = $_POST['SPATSP'];

                        $this->Dettaglio($_POST['ROWID']);
                        break;
                    case 'nuovo':
                        $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                        $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                        $this->funzione = $_POST['funzione'];
//                        $this->currSportello = $_POST['SPATSP'];

                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divGes);
//                        Out::valore($this->nameForm . '_ANASPA[SPATSP]', $_POST['SPATSP']);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::setFocus('', $this->nameForm . '_ANASPA[SPACOD]');
                        break;
                    default:
                        $this->OpenRicerca();
                        break;
                }
                break;

            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAggregati, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;


            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAggregati:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAggregati:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridAggregati:
                        Out::msgInfo("Sportello Aggregato", "Cancella");
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAggregati:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::attributo($this->nameForm . '_ANASPA[SPACOD]', 'readonly', '1');
                        Out::setFocus('', $this->nameForm . '_ANASPA[SPACOD]');
                        break;
                }
                break;



            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridAggregati, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('SPADES');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {
                                // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridAggregati);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::attributo($this->nameForm . '_ANASPA[SPACOD]', 'readonly', '1');
                        Out::setFocus('', $this->nameForm . '_ANASPA[SPACOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANASPA']['SPACOD'];
                        $Anaspa_rec = $_POST[$this->nameForm . '_ANASPA'];
                        if (!$this->controlli($Anaspa_rec)) {
                            break;
                        }

                        $Anaspa_rec['SPAMETAPROT'] = '';
                        $arrayMeta = $this->preparaClassiParametri($Anaspa_rec);
                        if ($arrayMeta) {
                            $Anaspa_rec['SPAMETAPROT'] = serialize($arrayMeta);
                        }

                        $sql = "SELECT SPACOD FROM ANASPA WHERE SPACOD = '" . $codice . "'";
                        try {   // Effettuo la FIND
                            $Anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
                            if (count($Anaspa_tab) == 0) {
                                try {
                                    $insert_Info = 'Oggetto: ' . $Anaspa_rec['SPACOD'] . " " . $Anaspa_rec['SPADES'];
                                    if ($this->insertRecord($this->PRAM_DB, 'ANASPA', $Anaspa_rec, $insert_Info)) {
                                        $this->OpenRicerca();
                                    }
                                } catch (Exception $e) {
                                    Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                                }
                            } else {
                                Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_ANASPA[SPACOD]');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su ARCHIVIO SPORTELLI AGGREGATI.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anaspa_rec = $_POST[$this->nameForm . '_ANASPA'];
                        if (!$this->controlli($Anaspa_rec)) {
                            break;
                        }

                        $Anaspa_rec['SPAMETAPROT'] = '';
                        $arrayMeta = $this->preparaClassiParametri($Anaspa_rec);
                        if ($arrayMeta) {
                            $Anaspa_rec['SPAMETAPROT'] = serialize($arrayMeta);
                        }

                        try {
                            $update_Info = 'Oggetto: ' . $Anaspa_rec['SPACOD'] . " " . $Anaspa_rec['SPADES'];
                            if ($this->updateRecord($this->PRAM_DB, 'ANASPA', $Anaspa_rec, $update_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Aggiornamento su sportelli aggregati", $e->getMessage());
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
                        $Anaspa_rec = $_POST[$this->nameForm . '_ANASPA'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anaspa_rec['SPACOD'] . " " . $_POST[$this->nameForm . '_SPADES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANASPA', $Anaspa_rec['ROWID'], $delete_Info)) {
                                Out::msgInfo("Cancellazione", "Sportello Aggregato cancellato.");
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su SPORTELLI AGGREGATI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_ANASPA[SPASUPERADMIN]_butt':
                        accRic::accRicGru($this->nameForm);
                        break;
                    case $this->nameForm . '_ANASPA[SPARES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "spares");
                        break;
                    case $this->nameForm . '_SelezionaAccount':
                        emlRic::emlRicAccount($this->nameForm, '', 'Smtp');
                        break;
                    case $this->nameForm . '_SvuotaAccount':
                        Out::valore($this->nameForm . '_ANASPA[SPAPEC]', '');
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmpt':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_ANASPA[SPAPEC]', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANASPA[SPATIPOPROT]':
                        $tipoProt = $_POST[$this->nameForm . "_ANASPA"]["SPATIPOPROT"];
                        //
                        $this->classProtocollo = proWsClientHelper::getClassName("PROTOCOLLO", $tipoProt);
                        $this->classFascicolazione = proWsClientHelper::getClassName("FASCICOLAZIONE", $tipoProt);
                        $this->classMail = proWsClientHelper::getClassName("MAIL", $tipoProt);
                        //
                        $this->creaComboIstanze("istanzaProtocollazione", $this->classProtocollo);
                        $this->creaComboIstanze("istanzaFascicolazione", $this->classFascicolazione);
                        $this->creaComboIstanze("istanzaMail", $this->classMail);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Spacod':
                        $codice = $_POST[$this->nameForm . '_Spacod'];
                        if (trim($codice) != "") {
                            $Anaspa_rec = $this->praLib->getAnaspa($codice);
                            if ($Anaspa_rec) {
                                $this->Dettaglio($Anaspa_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Spacod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANASPA[SPARES]':
                        $codice = $_POST[$this->nameForm . '_ANASPA']['SPARES'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANASPA[SPASUPERADMIN]':
                        $codice = $_POST[$this->nameForm . '_ANASPA']['SPASUPERADMIN'];
                        if ($codice) {
                            $Anagru_rec = $this->accLib->GetGruppi($codice);
                            Out::valore($this->nameForm . "_ANATSP[SPSAUPERADMIN]", $Anagru_rec['GRUCOD']);
                            Out::valore($this->nameForm . "_Superadmin", $Anagru_rec['GRUDES']);
                        }
                        break;
                }
                break;
            case 'returngru':
                $Anagru_rec = $this->accLib->GetGruppi($_POST["retKey"], 'rowid');
                if ($Anagru_rec) {
                    Out::valore($this->nameForm . "_ANASPA[SPASUPERADMIN]", $Anagru_rec['GRUCOD']);
                    Out::valore($this->nameForm . "_Superadmin", $Anagru_rec['GRUDES']);
                }
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    $this->DecodResponsabile($Ananom_rec);
                }
                break;
            case 'returnAccountSmtp':
                $this->appoggio = $_POST['retKey'];
                $valori[] = array(
                    'label' => array(
                        'value' => "Password:",
                        'style' => 'margin-top:10px;width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                    ),
                    'id' => $this->nameForm . '_passwordAccount',
                    'name' => $this->nameForm . '_passwordAccount',
                    'type' => 'password',
                    'style' => 'margin-top:10px;width:550px;',
                    'value' => ''
                );
                $messaggio = "Inserisci la password dell'account selezionato";
                Out::msgInput(
                        'Account Email', $valori
                        , array(
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordSmpt', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_funzione');
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_classProtocollo');
        App::$utente->removeKey($this->nameForm . '_classFascicolazione');
        App::$utente->removeKey($this->nameForm . '_classMail');
        App::$utente->removeKey($this->nameForm . '_classProcediMarche');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function DecodResponsabile($Ananom_rec) {
        Out::valore($this->nameForm . '_ANASPA[SPARES]', $Ananom_rec["NOMRES"]);
        Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::html($this->nameForm . "_istanzaProtocollazione", "");
        Out::html($this->nameForm . "_istanzaFascicolazione", "");
        Out::html($this->nameForm . "_istanzaMail", "");
        TableView::disableEvents($this->gridAggregati);
        TableView::clearGrid($this->gridAggregati);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        $this->classProtocollo = "";
        $this->classFascicolazione = "";
        $this->classMail = "";
        Out::setFocus('', $this->nameForm . '_Spacod');
    }

    public function Dettaglio($_Indice, $chiave = 'ROWID') {
        $Anaspa_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE " . $chiave . " = '$_Indice'", false);
        $open_Info = 'Oggetto: ' . $Anaspa_rec['SPACOD'] . " " . $Anaspa_rec['SPASDES'];
        $this->openRecord($this->PRAM_DB, 'ANASPA', $open_Info);
        $this->Nascondi();
        Out::valori($Anaspa_rec, $this->nameForm . '_ANASPA');
        if ($Anaspa_rec['SPASUPERADMIN']) {
            $Gruppi_rec = $this->accLib->GetGruppi($Anaspa_rec['SPASUPERADMIN']);
            Out::valore($this->nameForm . "_Superadmin", $Gruppi_rec['GRUDES']);
        }
        $Ananom_rec = $this->praLib->GetAnanom($Anaspa_rec['SPARES']);
        if ($Ananom_rec) {
            $this->DecodResponsabile($Ananom_rec);
        }
        if (!$this->emlLib->getMailAccountList()) {
            Out::hide($this->nameForm . "_SelezionaAccount");
            Out::hide($this->nameForm . "_SvuotaAccount");
            Out::attributo($this->nameForm . "_ANASPA[SPAPEC]", 'readonly', '1');
        } else {
            Out::show($this->nameForm . "_SelezionaAccount");
            Out::show($this->nameForm . "_SvuotaAccount");
            Out::attributo($this->nameForm . "_ANASPA[SPAPEC]", 'readonly', '0');
        }

        /*
         * Se presente il tipo protocollo, mi trovo le varie classi di parametri
         */
        if ($Anaspa_rec['SPATIPOPROT']) {
            $this->classProtocollo = proWsClientHelper::getClassName("PROTOCOLLO", $Anaspa_rec['SPATIPOPROT']);
            $this->classFascicolazione = proWsClientHelper::getClassName("FASCICOLAZIONE", $Anaspa_rec['SPATIPOPROT']);
            $this->classMail = proWsClientHelper::getClassName("MAIL", $Anaspa_rec['SPATIPOPROT']);
        }

        /*
         * Se trova la classe e le istanze disegna le select, altrimenti nasconde il campo
         */
        $this->creaComboIstanze("istanzaProtocollazione", $this->classProtocollo);
        $this->creaComboIstanze("istanzaFascicolazione", $this->classFascicolazione);
        $this->creaComboIstanze("istanzaMail", $this->classMail);

        /*
         * Se presenti i metadati, li decodifico e popolo le varie select
         */
        if ($Anaspa_rec['SPAMETAPROT']) {
            $arrayMeta = unserialize($Anaspa_rec['SPAMETAPROT']);
            Out::valore($this->nameForm . "_istanzaProtocollazione", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSPROTOCOLLO']);
            Out::valore($this->nameForm . "_istanzaFascicolazione", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSFASCICOLAZIONE']);
            Out::valore($this->nameForm . "_istanzaMail", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSMAIL']);
//            Out::valore($this->nameForm . "_istanzaProtocollazione", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']][$this->classProtocollo]);
//            Out::valore($this->nameForm . "_istanzaFascicolazione", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']][$this->classFascicolazione]);
//            Out::valore($this->nameForm . "_istanzaMail", $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']][$this->classMail]);

            Out::valore($this->nameForm . "_istanzaProcediMarche", $arrayMeta['CLASSIPARAMETRI'][$this->classProcediMarche]['KEYPARAMWSPROCEDIMARCHE']);
        }

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANASPA[SPADES]');
        Out::attributo($this->nameForm . '_ANASPA[SPACOD]', 'readonly', '0');
        TableView::disableEvents($this->gridAggregati);
    }

    function CreaSql() {
        $sql = "SELECT * FROM ANASPA";
        return $sql;
    }

    function controlli($Anaspa_rec) {
        $Ananom_rec = $this->praLib->GetAnanom($Anaspa_rec['SPARES']);
        if (!$Ananom_rec) {
            return false;
        }

        if (!$Ananom_rec['NOMEML']) {
            Out::msgStop("Errrore", "Il responsabile scelto non ha un indirizzo mail!");
            return false;
        }
        return true;
    }

    public function CreaCombo() {
        $this->praLib->creaComboTipiProt($this->nameForm . '_ANASPA[SPATIPOPROT]');
    }

    public function CreaComboProcediMarche() {
        $this->classProcediMarche = self::PROCEDI_MARCHE;
        $this->creaComboIstanze("istanzaProcediMarche", $this->classProcediMarche);
    }

    function creaComboIstanze($Campo, $Classe) {
        Out::html($this->nameForm . "_$Campo", "");
        $istanze = $this->praLib->getArrayIstanze($Classe);
        if ($istanze) {
            Out::show($this->nameForm . "_" . $Campo . "_field");
            Out::select($this->nameForm . "_$Campo", 1, "", "1", "");
            foreach ($istanze as $istanza) {
                $desc = $istanza['DESCRIZIONE_ISTANZA'];
                if ($desc == "") {
                    $desc = $istanza['CLASSE'];
                }
                Out::select($this->nameForm . "_$Campo", 1, $istanza['CLASSE'], "0", $desc);
            }
        } else {
            Out::hide($this->nameForm . "_" . $Campo . "_field");
            if ($Campo == 'istanzaProcediMarche') {
                Out::hide($this->nameForm . "_divProcediMarche");
            }
        }
    }

    function preparaClassiParametri($Anaspa_rec) {
        $arrayMeta = array();
        if ($Anaspa_rec['SPATIPOPROT']) {
            $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSPROTOCOLLO'] = $_POST[$this->nameForm . '_istanzaProtocollazione'];
            $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSFASCICOLAZIONE'] = $_POST[$this->nameForm . '_istanzaFascicolazione'];
            $arrayMeta['CLASSIPARAMETRI'][$Anaspa_rec['SPATIPOPROT']]['KEYPARAMWSMAIL'] = $_POST[$this->nameForm . '_istanzaMail'];
        }

        if ($_POST[$this->nameForm . '_istanzaProcediMarche']) {
            $arrayMeta['CLASSIPARAMETRI'][$this->classProcediMarche]['KEYPARAMWSPROCEDIMARCHE'] = $_POST[$this->nameForm . '_istanzaProcediMarche'];
        }

        return $arrayMeta;
    }

}

?>