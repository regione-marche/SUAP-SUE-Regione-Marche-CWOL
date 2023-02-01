<?php

/**
 *
 * PORTLET TRASMISSIONI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    20.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php');
include_once (ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php');
include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';

function proElencoIterPortlet() {
    $proElencoIterPortlet = new proElencoIterPortlet();
    $proElencoIterPortlet->parseEvent();
    return;
}

class proElencoIterPortlet extends itaModel {

    public $PROT_DB;
    public $SEGR_DB;
    public $PRIV_DB;
    public $ITW_DB;
    public $nameForm = "proElencoIterPortlet";
    public $divElenco = "proElencoIterPortlet_divElenco";
    public $gridIter = "proElencoIterPortlet_gridIter";
    public $proLib;
    public $proLibFascicolo;
    public $segLib;
    public $accLib;
    public $utente;
    public $codiceDest;
    public $arrSelezionati = array();
    private $giorniTermineDefault = 999;
    public $proLibAllegati;
    public $pageSelected;
    public $datiInvio = array();
    public $sqlSelezioneInvio = '';
    public $chiaviSelezionate = array();
    public $returnData = array();
    public $CaricamentoContabilitaHalley = '';
    public $flagAssegnazionePasso;
    public $delegheAttive;
    public $visDelegheAttive = array();
    public $visTutteDeleghe;

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->proLib = new proLib();
            $this->segLib = new segLib();
            $this->accLib = new accLib();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->PRIV_DB = itaDB::DBOpen('PRIV');
            $this->ITW_DB = $this->accLib->getITW();
            $this->utente = App::$utente->getKey($this->nameForm . '_utente');
            $this->codiceDest = App::$utente->getKey($this->nameForm . '_codiceDest');
            $this->arrSelezionati = App::$utente->getKey($this->nameForm . '_arrSelezionati');
            $this->giorniTermineDefault = App::$utente->getKey($this->nameForm . '_giorniTermineDefault');
            $this->pageSelected = App::$utente->getKey($this->nameForm . '_pageSelected');
            $this->datiInvio = App::$utente->getKey($this->nameForm . '_datiInvio');
            $this->sqlSelezioneInvio = App::$utente->getKey($this->nameForm . '_sqlSelezioneInvio');
            $this->chiaviSelezionate = App::$utente->getKey($this->nameForm . '_chiaviSelezionate');
            $this->returnData = App::$utente->getKey($this->nameForm . '_returnData');
            $this->CaricamentoContabilitaHalley = App::$utente->getKey($this->nameForm . '_CaricamentoContabilitaHalley');
            $this->flagAssegnazionePasso = App::$utente->getKey($this->nameForm . '_flagAssegnazionePasso');
            $this->delegheAttive = App::$utente->getKey($this->nameForm . '_delegheAttive');
            $this->visDelegheAttive = App::$utente->getKey($this->nameForm . '_visDelegheAttive');
            $this->visTutteDeleghe = App::$utente->getKey($this->nameForm . '_visTutteDeleghe');
            $this->proLibAllegati = new proLibAllegati();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_utente', $this->utente);
            App::$utente->setKey($this->nameForm . '_codiceDest', $this->codiceDest);
            App::$utente->setKey($this->nameForm . '_arrSelezionati', $this->arrSelezionati);
            App::$utente->setKey($this->nameForm . '_giorniTermineDefault', $this->giorniTermineDefault);
            App::$utente->setKey($this->nameForm . '_pageSelected', $this->pageSelected);
            App::$utente->setKey($this->nameForm . '_datiInvio', $this->datiInvio);
            App::$utente->setKey($this->nameForm . '_sqlSelezioneInvio', $this->sqlSelezioneInvio);
            App::$utente->setKey($this->nameForm . '_chiaviSelezionate', $this->chiaviSelezionate);
            App::$utente->setKey($this->nameForm . '_returnData', $this->returnData);
            App::$utente->setKey($this->nameForm . '_CaricamentoContabilitaHalley', $this->CaricamentoContabilitaHalley);
            App::$utente->setKey($this->nameForm . '_flagAssegnazionePasso', $this->flagAssegnazionePasso);
            App::$utente->setKey($this->nameForm . '_delegheAttive', $this->delegheAttive);
            App::$utente->setKey($this->nameForm . '_visDelegheAttive', $this->visDelegheAttive);
            App::$utente->setKey($this->nameForm . '_visTutteDeleghe', $this->visTutteDeleghe);
        }
    }

    public function getReturnData() {
        return $this->returnData;
    }

    public function setReturnData($returnData) {
        $this->returnData = $returnData;
    }

    public function parseEvent() {

        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':

                /*
                 * Leggo il flag per vedere se sono attive le trasmissioni
                 */
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
                $praLibPasso = new praLibPasso();
                $this->flagAssegnazionePasso = $praLibPasso->getFlagAssegnazionePasso();

                itaLib::openForm('proElencoIterPortlet', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");

            case 'openportletapp':
                $this->utente = App::$utente->getKey('nomeUtente');
                $this->codiceDest = proSoggetto::getCodiceSoggettoFromIdUtente();
                if (!$this->codiceDest) {
                    Out::html($this->divElenco, "<br><br><h1>CODICE DESTINATARIO NON CONFIGUARTO CONTATTATRE L'AMMINISTRATRE DEL SISTEMA</h1><br><br>");
                    break;
                }
                // Check Ufficio Valido:
                $Ctrruoli = proSoggetto::getRuoliFromCodiceSoggetto($this->codiceDest);
                if (!$Ctrruoli) {
                    Out::html($this->divElenco, "<br><br><h1>CODICE DESTINATARIO NON CONFIGUARTO CONTATTATRE L'AMMINISTRATRE DEL SISTEMA</h1><br><br>");
                    break;
                }


                $this->CreaCombo();
                $this->AttivaRadio();
                Out::valore($this->nameForm . "_Giorni", '');
                Out::hide($this->nameForm . '_divLimiteVis');
                Out::hide($this->nameForm . '_GestAltraScrivania');
                Out::hide($this->nameForm . '_GestScrivaniaGroup');

                $this->loadModelConfig();
                $this->caricaConfigurazioni();
                Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});"); //,context:'$this->id',model:'$this->model'});");
                break;
            case 'onClickTablePager':
                if ($_POST[$this->nameForm . '_OpzioniVisualizzazione'] == 2) {
                    if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
                        if (!$_POST[$this->nameForm . '_trasmessoDal']) {
                            Out::msgInfo("Attenzione", "Occorre indicare un periodo di ricerca.");
                            Out::setFocus('', $this->nameForm . '_trasmessoDal');
                            break;
                        }
                    }
                }
                $this->checkDelegheAttive();
                $this->CaricaTrasmissioniNew($this->gridIter);
                $this->ContaDaFirmare();
                break;
            case 'editGridRow':
            case 'dbClickRow':
                if ($_POST['rowid'] != '0') {
                    $rowId = $_POST['rowid'];
                    $this->DettaglioTrasmissione($rowId);
//                    TableView::enableEvents($this->gridIter);
//                    TableView::reload($this->gridIter);
//                    $this->caricaDatiGriglia();
                    // $this->CaricaTrasmissioni($this->gridIter, '2');
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proElencoIterPortlet', $parameters);
                        break;
                }
                break;
            case 'onChange': // Evento onChange
                switch ($_POST['id']) {
                    case $this->nameForm . '_Carico':
                    case $this->nameForm . '_Chiusi':
                    case $this->nameForm . '_Scaduti':
                    case $this->nameForm . '_Rifiutati':
                    case $this->nameForm . '_DaFirmare':
                        if ($_POST['id'] == $this->nameForm . '_Chiusi') {
                            Out::show($this->nameForm . '_divLimiteVis');
                            Out::hide($this->nameForm . '_divDateTrasmissioni');
                            Out::valore($this->nameForm . '_LimiteVis', "");
                        } else {
                            Out::hide($this->nameForm . '_divLimiteVis');
                            Out::show($this->nameForm . '_divDateTrasmissioni');
                        }
                        Out::hide($this->nameForm . '_chkAperti_field');
                        Out::hide($this->nameForm . '_chkChiusi_field');
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;

                    case $this->nameForm . '_Inviati':
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;

                    case $this->nameForm . '_selectUffici':
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;

                    case $this->nameForm . '_selectGesVis':
                    case $this->nameForm . '_selectVisLetti':
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;

                    case $this->nameForm . '_LimiteVis':
                        if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
                            Out::show($this->nameForm . '_divDateTrasmissioni');
                            Out::setFocus('', $this->nameForm . '_trasmessoDal');
                        } else {
                            Out::hide($this->nameForm . '_divDateTrasmissioni');
                        }
                        break;
                }
                break;
            case 'onClick': // Evento onClick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Calcola2':
                        $this->giorniTermineDefault = $_POST[$this->nameForm . '_Giorni'];
                        if ($_POST[$this->nameForm . '_OpzioniVisualizzazione'] == 2) {
                            if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
                                if (!$_POST[$this->nameForm . '_trasmessoDal']) {
                                    Out::msgInfo("Attenzione", "Occorre indicare un periodo di ricerca.");
                                    Out::setFocus('', $this->nameForm . '_trasmessoDal');
                                    break;
                                }
                            }
                        }

                        //$this->CaricaTrasmissioni($this->gridIter);
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;
                    case $this->nameForm . '_SalvaImpostazioni':
                        $this->setConfig();
                        $this->loadModelConfig();
                        Out::msgInfo("Profili Documenti in Carico", "<br><br>Il profilo preferito è stato salvato.<br>");
                        break;
                    case $this->nameForm . '_Info':
                        $where = "ITEPRO LIKE '" . date('Y') . "%'";
                        if ($_POST[$this->nameForm . '_trasmessoDal']) {
                            $where .= " AND ITEDAT >= '" . $_POST[$this->nameForm . '_trasmessoDal'] . "'";
                        }
                        if ($_POST[$this->nameForm . '_trasmessoAl']) {
                            $where .= " AND ITEDAT <= '" . $_POST[$this->nameForm . '_trasmessoAl'] . "'";
                        }
                        $sql = "SELECT DISTINCT(" . $this->PROT_DB->strConcat('ITEPRO', 'ITEPAR') . ") AS NUM
                            FROM ARCITE
                            WHERE ITENODO<>'INS' AND $where AND ITEDES='$this->codiceDest'";
                        $risultatoTot = $this->proLib->getGenericTab($sql . " GROUP BY ITEPRO, ITEPAR");

                        $condizione = " AND (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
                        $condizione .= " AND ARCITE.ITEFIN=''"; //non chiuso
                        $condizione .= " AND ARCITE.ITESUS=''"; //non inviato
                        $condizione .= " AND (ARCITE.ITETERMINE='' OR ARCITE.ITETERMINE>='" . date("Ymd") . "')"; //non scaduto
                        $risultatoIC = $this->proLib->getGenericTab($sql . $condizione . " GROUP BY ITEPRO, ITEPAR");

                        $condizione = " AND (ARCITE.ITESTATO='1' OR ARCITE.ITEFLA='2')";
                        $condizione .= " AND ARCITE.ITEFIN<>''";
                        $condizione .= " AND ARCITE.ITESUS=''"; //non inviato
                        $condizione .= " AND (ARCITE.ITETERMINE='' OR ARCITE.ITETERMINE>='" . date("Ymd") . "')"; //non scaduto
                        $risultatoC = $this->proLib->getGenericTab($sql . $condizione . " GROUP BY ITEPRO, ITEPAR");

                        $condizione = " AND ARCITE.ITESTATO='2'";
                        $condizione .= " AND ARCITE.ITESUS<>''";
                        $condizione .= " AND ARCITE.ITEFIN=''"; //non chiuso
                        $condizione .= " AND (ARCITE.ITETERMINE='' OR ARCITE.ITETERMINE>='" . date("Ymd") . "')"; //non scaduto
                        $risultatoI = $this->proLib->getGenericTab($sql . $condizione . " GROUP BY ITEPRO, ITEPAR");

                        $condizione = " AND ARCITE.ITESTATO='0'";
                        $condizione .= " AND ARCITE.ITETERMINE<>'' AND ARCITE.ITETERMINE<'" . date("Ymd") . "'";
                        $condizione .= " AND ARCITE.ITEFIN=''"; //non chiuso
                        $condizione .= " AND ARCITE.ITESUS=''"; //non inviato
                        $risultatoS = $this->proLib->getGenericTab($sql . $condizione . " GROUP BY ITEPRO, ITEPAR");

                        $condizione = " AND ARCITE.ITESTATO='1'";
                        $condizione .= " AND ARCITE.ITEDATRIF<>''";
                        $risultatoR = $this->proLib->getGenericTab($sql . $condizione . " GROUP BY ITEPRO, ITEPAR");

                        $messaggio = "<br><br>Totale delle trasmissioni per il periodo selezionato: " . count($risultatoTot)
                                . "<br>Dei quali:<br>In Carico = " . count($risultatoIC)
                                . "<br>Chiusi = " . count($risultatoC)
                                . "<br>Inviati = " . count($risultatoI)
                                . "<br>Scaduti = " . count($risultatoS)
                                . "<br>Rifiutati = " . count($risultatoR);

                        Out::msgInfo("Informazioni Generali.", $messaggio);
                        break;
                    case $this->nameForm . '_Accetta':
                        $this->selezionaDaAccettare();
                        break;
                    case $this->nameForm . '_Chiudi':
                        $this->selezionaDaChiudere();
                        break;
                    case $this->nameForm . '_Invia':
                        $this->datiInvio = array();
                        $this->sqlSelezioneInvio = '';
                        $this->openInviaTrasmissioni();
                        break;
                    case $this->nameForm . '_Fascicola':
                        $this->openFascicolaProtocolli();
                        break;

                    case $this->nameForm . '_TrasmDest_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnAnamedTrasm');
                        break;
                    case $this->nameForm . '_SvuotaFiltri':
                        Out::valore($this->nameForm . '_TrasmDest', '');
                        Out::valore($this->nameForm . '_TrasmDescr', '');
                        Out::valore($this->nameForm . '_trasmessoDal', '');
                        Out::valore($this->nameForm . '_trasmessoAl', '');
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;


                    case $this->nameForm . '_GestAltraScrivania':
                        if ($this->visDelegheAttive) {
                            $this->disattivaScrivanieDelegate();
                            TableView::enableEvents($this->gridIter);
                            TableView::reload($this->gridIter);
                        } else {
                            proRic::proRicDelegheAttive($this->nameForm, $this->delegheAttive);
                        }
                        break;

                    case $this->nameForm . '_GestScrivaniaGroup':
                        if ($this->visTutteDeleghe) {
                            $this->disattivaScrivanieDelegate();
                        } else {
                            $this->visTutteDeleghe = true;
                            $this->visDelegheAttive = $this->delegheAttive;
                            $html = '<div style="display:block; width:100%; float:right; background-image:linear-gradient(to left, #F6CECE 0%, #FFFFFF 100%)"><span style="float:right;"><b>Stai visualizzando anche le scrivanie dei tuoi deleganti.</b></span></div><br>';
                            Out::html($this->nameForm . '_divInfoVisScrivania', $html);
                            Out::show($this->nameForm . '_divInfoVisScrivania');
                        }
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TrasmDest':
                        $codice = $_POST[$this->nameForm . '_TrasmDest'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnamedTrasm($codice);
                        } else {
                            Out::valore($this->nameForm . '_TrasmDescr', '');
                        }
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "RELOAD_GRID_PORTLET":
                        TableView::enableEvents($this->gridIter);
                        TableView::reload($this->gridIter);
                        //Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});");
                        break;
                }
                break;
            case "returnMultiselectGeneric":
                switch ($_POST['retid']) {
                    case 'DaAccettare':
                        if ($_POST['retKey'] != '') {
                            $arcite_tab = $this->arrSelezionati;
                            $this->arrSelezionati = array();
                            $chiavi = explode(',', $_POST['retKey']);
                            foreach ($chiavi as $chiave) {
                                $arcite_rec = $this->proLib->GetArcite($arcite_tab[$chiave]['ROWID'], 'rowid');
                                $arcite_rec['ITESTATO'] = 2;
                                $arcite_rec['ITEDATACC'] = date('Ymd');
                                $arcite_rec['ITEDLE'] = date('Ymd');
                                $arcite_rec['ITEDLEORA'] = date('H:i:s');
                                $arcite_rec['ITEDATACCORA'] = date('H:i:s');
                                if (!$arcite_rec['ITEGES']) {
                                    $arcite_rec['ITEFIN'] = date("Ymd");
                                    $arcite_rec['ITEFLA'] = proIter::ITEFLA_CHIUSO;
                                }
                                $messaggio = 'Accettato ROWID: ' . $arcite_tab[$chiave]['ROWID'];
                                $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $messaggio, 'ROWID');
                            }
                            Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});");
                        }
                        break;
                    case 'DaChiudere':
                        if ($_POST['retKey'] != '') {
                            $arcite_tab = $this->arrSelezionati;
                            $this->arrSelezionati = array();
                            $chiavi = explode(',', $_POST['retKey']);
                            foreach ($chiavi as $chiave) {
                                $arcite_rec = $this->proLib->GetArcite($arcite_tab[$chiave]['ROWID'], 'rowid');
                                if (!$arcite_rec['ITEDLE']) {
                                    $arcite_rec['ITEDLE'] = date('Ymd');
                                    $arcite_rec['ITEDLEORA'] = date('H:i:s');
                                }
                                $arcite_rec['ITEFLA'] = proIter::ITEFLA_CHIUSO;
                                $arcite_rec['ITEFIN'] = date('Ymd');
                                $arcite_rec['ITEFINORA'] = date('H:i:s');
                                $messaggio = 'Da Chiudere ROWID: ' . $arcite_tab[$chiave]['ROWID'];
                                $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $messaggio, 'ROWID');
                            }
                            Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});");
                        }
                        break;
                    case 'DaInviare':
                        if ($_POST['retKey'] != '') {
                            $this->inviaTrasmissioni($chiavi);
                            Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});");
                        }
                        break;

                    case 'DaFascicolare':
                        $this->chiaviSelezionate = array();
                        if ($_POST['retKey'] != '') {
                            $chiavi = explode(',', $_POST['retKey']);
                            $this->chiaviSelezionate = $chiavi;
                        }
                        $arcite_tab = $this->arrSelezionati;
                        // Prendo il titolario del primo protocollo:
                        foreach ($this->chiaviSelezionate as $chiave) {
                            $arcite_rec = $this->proLib->GetArcite($arcite_tab[$chiave]['ROWID'], 'rowid');
                            $Anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
                            break;
                        }


                        /*
                         * Prendo Titolario dell'ultimo protoocollo:
                         */
                        if ($Anapro_rec) {
                            $Titolario['VERSIONE_T'] = $Anapro_rec['VERSIONE_T'];
                            $Titolario['PROCAT'] = substr($Anapro_rec['PROCCF'], 0, 4);
                            $Titolario['CLACOD'] = substr($Anapro_rec['PROCCF'], 4, 8);
                            $Titolario['FASCOD'] = substr($Anapro_rec['PROCCF'], 8, 12);
                            $Titolario['ORGANN'] = $_POST[$this->nameForm . '_Organn'];

                            $this->ApriSelezioneFascicolo($Titolario);
                        }
                        break;
                }
                break;

            case 'returnAnamedTrasm':
                $this->DecodAnamedTrasm($_POST['retKey'], 'rowid');
                break;

            case 'returnInviaTrasmissioni':
                $this->datiInvio['destinatari'] = $_POST['destinatari'];
                $this->datiInvio['annotazioni'] = $_POST['annotazioni'];
                $this->selezionaDaInviare();
                break;

            case 'returnAlberoFascicolo':
                $pronumR = substr($_POST['retKey'], 4, 10);
                $proparR = substr($_POST['retKey'], 14);
                $retFascicolo = array();
                $retFascicolo['ROWID'] = $this->returnData['ROWID_ANAORG'];

                $fascicolo_rec = $this->proLib->GetAnaorg($retFascicolo['ROWID'], 'rowid');
                $arcite_tab = $this->arrSelezionati;
                $infoFascicolazione = array();
                foreach ($this->chiaviSelezionate as $chiave) {
                    $arcite_rec = $this->proLib->GetArcite($arcite_tab[$chiave]['ROWID'], 'rowid');
                    $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
                    if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $fascicolo_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $pronumR, $proparR)) {
                        $infoFascicolazione[] = "Protocollo " . $anapro_rec['PRONUM'] . ' ' . $anapro_rec['PROPAR'] . ' non fascicolato: ' . $this->proLibFascicolo->getErrMessage();
                    }
                }
                $Messaggio = "Fascicolazione dei protocolli terminata.<br>";
                if ($infoFascicolazione) {
                    $Messaggio .= "Sono state riscontrate le seguenti anomalie:<br>";
                    $Messaggio .= implode("<br>", $infoFascicolazione);
                }
                Out::msginfo('Fascilazione', $Messaggio);
                Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});");
                break;
            case 'AggiornaFatturaHalley':
                $this->DettaglioTrasmissione($_POST['id'], true);
                break;
            case 'VediAllegatiProt':
                $rowid = $_POST['id'];
                $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                if ($Anapro_rec) {
                    if ($Anapro_rec['PROPAR'] == 'I') {
                        include_once (ITA_BASE_PATH . '/apps/Segreteria/segLibAllegati.class.php');
                        $segLibAllegati = new segLibAllegati();
                        $Indice_rec = $this->segLib->GetIndice($Anapro_rec['PRONUM'], 'anapro', false, $Anapro_rec['PROPAR']);
                        $AllegatiProtocollo = $this->segLib->caricaAllegatiOrdinanze($Indice_rec);
                        foreach ($AllegatiProtocollo as $Key => $AllegatoProtocollo) {
                            $path = $segLibAllegati->SetDirectory($Indice_rec, $AllegatoProtocollo['DOCTIPO'], false);
                            $AllegatiProtocollo[$Key]['INFO'] = '';
                            $AllegatiProtocollo[$Key]['FILEPATH'] = $path . $AllegatoProtocollo['DOCFIL'];
                            $AllegatiProtocollo[$Key]['FILENAME'] = $AllegatoProtocollo['DOCFIL'];
                            $AllegatiProtocollo[$Key]['FILEINFO'] = $AllegatoProtocollo['DOCNOT'];
                            $AllegatiProtocollo[$Key]['NOMEFILE'] = $AllegatoProtocollo['DOCNAME'];
                            $AllegatiProtocollo[$Key]['FILEORIG'] = $AllegatoProtocollo['DOCNAME'];
                        }
                    } else {
                        $AllegatiProtocollo = $this->proLib->caricaAllegatiProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                    }
                    if (!$AllegatiProtocollo) {
                        Out::msginfo('Informazione', 'Non sono presenti allegati per questo protocollo.');
                        break;
                    }

                    itaLib::openForm('proArriAllegati');
                    /* @var $proArriAllegati proArriAllegati */
                    $proArriAllegati = itaModel::getInstance('proArriAllegati');
                    $proArriAllegati->setEvent('openDettaglio');
                    $proArriAllegati->setIndiceRowid($rowid);
                    $proArriAllegati->setProArriAlle($AllegatiProtocollo);
                    $proArriAllegati->setReturnModel($this->nameForm);
                    $proArriAllegati->setReturnEvent('returnFromGestioneAllegati');
                    $proArriAllegati->setReturnId('');
                    $proArriAllegati->parseEvent();
                }
                break;

            case 'returnDelegheAttive':
                $Delegha_rec = $this->proLib->GetDelegheIter($_POST['retKey']);
                $Anamed_rec = $this->proLib->GetAnamed($Delegha_rec['DELESRCCOD']);
                $Anauff_rec = $this->proLib->GetAnauff($Delegha_rec['DELESRCUFF']);
                $descDelega = $Anamed_rec['MEDNOM'] . " per l'ufficio " . $Anauff_rec['UFFDES'];
                $this->visDelegheAttive[] = $Delegha_rec;
                TableView::enableEvents($this->gridIter);
                TableView::reload($this->gridIter);
                $html = '<div style="display:block; width:100%; float:right; background-image:linear-gradient(to left, #F6CECE 0%, #FFFFFF 100%)"><span style="float:right;"><b>Stai visualizzando anche la scrivania di ' . $descDelega . '</b></span></div><br>';
                Out::html($this->nameForm . '_divInfoVisScrivania', $html);
                Out::show($this->nameForm . '_divInfoVisScrivania');
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_returnData');
    }

    public function returnToParent($close = true) {
        App::$utente->removeKey($this->nameForm . '_utente');
        App::$utente->removeKey($this->nameForm . '_codiceDest');
        App::$utente->removeKey($this->nameForm . '_arrSelezionati');
        App::$utente->removeKey($this->nameForm . '_giorniTermineDefault');
        App::$utente->removeKey($this->nameForm . '_pageSelected');
        App::$utente->removeKey($this->nameForm . '_datiInvio');
        App::$utente->removeKey($this->nameForm . '_sqlSelezioneInvio');
        App::$utente->removeKey($this->nameForm . '_chiaviSelezionate');
        App::$utente->removeKey($this->nameForm . '_CaricamentoContabilitaHalley');
        App::$utente->removeKey($this->nameForm . '_flagAssegnazionePasso');
        App::$utente->removeKey($this->nameForm . '_delegheAttive');
        App::$utente->removeKey($this->nameForm . '_visDelegheAttive');
        App::$utente->removeKey($this->nameForm . '_visTutteDeleghe');
        parent::close();
        if ($close) {
            $this->close();
        }
    }

    private function CaricaTrasmissioniNew($griglia) {
        $sql = $this->creaSql();
        $ita_grid01 = new TableView($griglia, array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));

        TableView::disableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->setPageNum($_POST['page']);


        if ($_POST['rows'] != '') {
            $ita_grid01->setPageRows($_POST['rows']);
        } else {
            $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        }
        if ($_POST['sidx'] != 'NDESTITER' && $_POST['sidx'] != 'NDESLETTI') {
            if ($_POST['sidx'] == '') {
                $_POST['sidx'] = 'GIORNITERMINE';
            }
            if ($_POST['sidx'] == 'NUMERO') {
                $_POST['sidx'] = 'ITEPRO';
            }
            $ita_grid01->setSortIndex($_POST['sidx']);
            $ita_grid01->setSortOrder($_POST['sord']);
        }

        $sortOrder = $_POST['sord'];
        if ($_POST['sidx'] == 'ITEDAT') {
            $ita_grid01->setSortIndex('ITEDAT ' . $sortOrder . ', ITEPRO ');
            $ita_grid01->setSortOrder($sortOrder);
        }

        $datiArray = $ita_grid01->getDataArray();

        /*
         * Spengo di default le 2 colonne per info Trasmissioni
         */
        TableView::hideCol($this->gridIter, "PROCEDIMENTO");
        TableView::hideCol($this->gridIter, "DATIAGGIUNTIVI");

        /*
         * Se attivo il parametro delle trasmissioni, accendo le 2 colonne
         */
        if ($this->flagAssegnazionePasso) {
            TableView::showCol($this->gridIter, "PROCEDIMENTO");
            TableView::showCol($this->gridIter, "DATIAGGIUNTIVI");
        }

        /*
         * Faccio il resize per sistemare la posizione delle colonne
         */
        TableView::resizeGrid($this->nameForm . "_divGridIter", false, true);

        foreach ($datiArray as $key => $dati) {
            $risultato = $this->elaboraRecord($dati);
            $datiArray[$key] = $risultato;
        }
        $ita_grid01->getDataPageFromArray('json', $datiArray);
        TableView::enableEvents($griglia);
    }

    private function elaboraRecord($arcite_rec) {
        $itedat = $arcite_rec['ITEDAT'];
        $dataAcc = "";
        $record = array();
        $codice = $arcite_rec['ITEPRO'];
        $anapro_rec = $this->proLib->GetAnapro($codice, 'codice', $arcite_rec['ITEPAR']);
        //$anaogg_rec = $this->proLib->GetAnaogg($codice, $arcite_rec['ITEPAR']);
        $record['ROWID'] = $arcite_rec['ROWID'];
        $tooltipitepar = '';
        $iconaDocumento = 'ita-icon-register-document-16x16';
        /*
         * Inizzializza tag Inizio/Fine stili.
         */
        $fontLettura = 'font-weight: bold;';
        if ($arcite_rec['ITEDLE']) {
            $fontLettura = 'font-weight: normal;';
        }

        /*
         * Decodifica di tipo Protocollo.
         */
        switch ($arcite_rec['ITEPAR']) {
            case 'P':
                $tooltipitepar = 'PROTOCOLLO IN PARTENZA';
                break;
            case 'C':
                $tooltipitepar = 'DOCUMENTO FORMALE';
                break;
            case 'A':
                $tooltipitepar = 'PROTOCOLLO IN ARRIVO';
                break;
            case 'I':
                $iconaDocumento = 'ita-icon-register-document-green-16x16';
                $tooltipitepar = 'INDICE DOCUMENTALE';
                break;
            case 'W':
                $iconaDocumento = 'ita-icon-footsteps-16x16';
                $tooltipitepar = 'PASSO';
                break;
        }

        if ($this->visDelegheAttive) {
            if ($arcite_rec['ITEDES'] != $this->codiceDest && $arcite_rec['ITEDES']) {
                //Aggiungo descrizione delegante:                    
                $soggetto_originale = proSoggetto::getInstance($this->proLib, $arcite_rec['ITEDES'], $arcite_rec['ITEUFF']);
                $soggetto_originale_dati = $soggetto_originale->getSoggetto();
                $DescDelega = '';
                $DescDelega.="<br>Delegante: " . $soggetto_originale_dati['DESCRIZIONESOGGETTO'];
                $DescDelega.="<br>Ufficio: " . $soggetto_originale_dati['DESCRIZIONEUFFICIO'];
                $tooltipitepar = $tooltipitepar . $DescDelega;
            }
        }



        $record['ITEPAR'] = "<span title=\"$tooltipitepar\" class=\"ita-tooltip \" style=\"display:inline-block;vertical-align:bottom;width:10px;\">{$arcite_rec['ITEPAR']}</span>";
        if ($arcite_rec['PROCODTIPODOC'] == 'EFAA' || $arcite_rec['PROCODTIPODOC'] == 'EFAP' || $arcite_rec['PROCODTIPODOC'] == 'SDIA' || $arcite_rec['PROCODTIPODOC'] == 'SDIP' || $arcite_rec['PROCODTIPODOC'] == 'EFAS') {
            $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
        }
        if ($arcite_rec['ITEPAR'] == 'F') {
            $record['ITEPAR'] .= "<span title=\"Tipo {$arcite_rec['ITEPAR']}\" style=\"display:inline-block;\" class=\"ita-tooltip ita-icon ita-icon-open-folder-16x16\"></span>";
        } else if ($arcite_rec['ITEPAR'] == 'N') {
            $subF = substr($anapro_rec['PROSUBKEY'], strpos($anapro_rec['PROSUBKEY'], '-') + 1);
            $record['ITEPAR'] .= "<span style=\"display:inline-block;\" title=\"Tipo " . $arcite_rec['ITEPAR'] . " - " . $subF . "\" class=\"ita-tooltip ita-icon ita-icon-sub-folder-16x16\"></span>";
        } else if ($arcite_rec['ITEPAR'] == 'T') {
            $record['ITEPAR'] .= "<span style=\"display:inline-block;\" title=\"Tipo {$arcite_rec['ITEPAR']}\" class=\"ita-tooltip ita-icon ita-icon-edit-16x16\"></span>";
        } else {
            $itepar_text = " Tipo {$arcite_rec['ITEPAR']} ";
            if ($arcite_rec['ITEANN']) {
                $itepar_text = $arcite_rec['ITEANN'];
                if ($arcite_rec['ITEMOTIVOP']) {
                    $itepar_text .= ' - ' . $arcite_rec['ITEMOTIVOP'];
                }
            }
            $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon $iconaDocumento\"></span>";

            if ($arcite_rec['ITETIP'] == proIter::ITETIP_ALLAFIRMA) {
                $profilo = proSoggetto::getProfileFromIdUtente();
                $docfirma_tab = $this->proLibAllegati->GetDocfirma($arcite_rec['ROWID'], 'rowidarcite', true, " AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
                if ($docfirma_tab) {
                    $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon ita-icon-sigillo-16x16\"></span>";
                }
//                } else {
//                    $record['ITEPAR'] = "<span style=\"display:inline-block;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon ita-icon-register-document-16x16\"></span><span style=\"display:inline-block;\">{$arcite_rec['ITEPAR']}</span>";
//                }
            }
        }
        $record['OGGETTO'] = $arcite_rec['OGGETTO'];

        if ($arcite_rec['ITEPAR'] == 'F' || $arcite_rec['ITEPAR'] == 'N') {
            $record['NUMERO'] = $anapro_rec['PROFASKEY'];
        } else if ($arcite_rec['ITEPAR'] == 'I') {
            $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
            $record['NUMERO'] = intval(substr($Indice_rec['IDELIB'], 2));
        } else {
            $record['NUMERO'] = intval(substr($codice, 4)) . ' / ' . substr($codice, 0, 4);
        }
        /*
         * Passo:
         */
        if ($arcite_rec['ITEPAR'] == 'W') {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
            $praLib = new praLib();
            $propas_rec = $praLib->GetPropas($arcite_rec['ITEPRO'], "paspro", false, $arcite_rec['ITEPAR']);
            $Serie_rec = $praLib->ElaboraProgesSerie($propas_rec['PRONUM']);
            $record['NUMERO'] = $Serie_rec;
            //$anastp_rec = $praLib->GetAnastp($propas_rec['PROSTATO']);
            //$DatiProtocollo = "<span style=\"display: inline-block; width:120px;\"><b>Passo Pratica:</b></span> $Serie_rec <span style=\"display: inline-block; margin-left:30px;\"><b>Sequenza </b>" . $propas_rec['PROSEQ'] . "</span><br> ";
        }

        if ($arcite_rec['ITEDAT']) {
            $itedat = $arcite_rec['ITEDAT'];
        } else {
            $itedat = $anapro_rec['PRODAR'];
        }
        if (!$anapro_rec) {
            return false;
        }
        $ev = "";
        if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100") {
            $ev = "background-color:yellow;";
        }
        if ($arcite_rec["ITEDES"] != proSoggetto::getCodiceSoggettoFromIdUtente()) {
            $ev = "background-color:orange;";
        }

        if ($this->proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $record['PROVENIENZA'] = "<p style=\"display:inline-block;background-color:lightgrey; $fontLettura\">RISERVATO</p>";
        } else {
            $record['PROVENIENZA'] = $anapro_rec['PRONOM'];
            if ($arcite_rec['ITEPAR'] == 'C') {
                $record['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
            }
        }
        $rifiutaImg = "";
        if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
            $rifiutaImg = "<span style=\"display:inline-block\" class=\"ita-icon ita-icon-divieto-16x16\"></span>";
        }
        $record['STATO'] = "";
        if ($arcite_rec['ITEDLE'] != '') {
            if ($arcite_rec['ITEGES'] != 1) {
                $dataLet = substr($arcite_rec['ITEDLE'], 6, 2) . "/" . substr($arcite_rec['ITEDLE'], 4, 2) . "/" . substr($arcite_rec['ITEDLE'], 0, 4);
                $record['STATO'] = "<span style=\"display:inline-block\" title=\"Letto in data $dataLet\" class=\"ita-icon ita-icon-apertagray-16x16\"></span>";
            } else {
                if ($arcite_rec['ITESTATO'] == 2) {
                    if ($arcite_rec['ITEDATACC']) {
                        $dataAcc = "in data " . substr($arcite_rec['ITEDATACC'], 6, 2) . "/" . substr($arcite_rec['ITEDATACC'], 4, 2) . "/" . substr($arcite_rec['ITEDATACC'], 0, 4);
                    }
                    $record['STATO'] = "<span style=\"display:inline-block\" title=\"Preso in carico $dataAcc\" class=\"ita-icon ita-icon-mail-green-verify-16x16\"></span>";
                } else {
                    $dataLet = substr($arcite_rec['ITEDLE'], 6, 2) . "/" . substr($arcite_rec['ITEDLE'], 4, 2) . "/" . substr($arcite_rec['ITEDLE'], 0, 4);
                    $record['STATO'] = "<span style=\"display:inline-block\" title=\"Letto in data $dataLet\" class=\"ita-icon ita-icon-apertagreen-16x16\"></span>";
                }
            }
        } else {
            if ($arcite_rec['ITEGES'] != 1) {
                $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-chiusagray-16x16"></span>';
            } else {
                $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-chiusagreen-16x16"></span>';
            }
        }
        if ($arcite_rec['ITESUS'] != '') {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-inoltrata-16x16"></span>';
        }
        if ($arcite_rec['ITETERMINE'] != '' && $arcite_rec['ITETERMINE'] < date("Ymd")) {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-lock-16x16"></span>';
        }
        if ($arcite_rec['ITEDATRIF'] != '') {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-divieto-16x16"></span>';
        }


        if ($arcite_rec['ITEFLA'] == 2) {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-check-red-16x16"></span>';
        }
        $record['STATO'] .= $rifiutaImg;
        if ($arcite_rec['ITEORGWORKLIV'] == 1) {
            $record['STATO'] .= "<span style=\"display:inline-block\" class=\"ita-tooltip ita-icon ita-icon-group-16x16\" title=\"Trasmesso all'ufficio\"></span>";
        }

//        $anades_rec = $this->proLib->getGenericTab("SELECT COUNT(ROWID) AS CONTA FROM ANADES WHERE DESNUM=$codice AND DESPAR='" . $arcite_rec['ITEPAR'] . "' AND DESTIPO='T'", false);
//        $record['NDESTPROT'] = $anades_rec['CONTA'];
        $retConteggi = $this->ConteggiIter($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
        $record['NDESTITER'] = $retConteggi['NDESTITER'];
        $record['NDESLETTI'] = $retConteggi['NDESLETTI'];
        /*
         * Check Stato ed Evidenzia Protocollo:
         */
        $ini_tag = "<span style = 'display:inline-block; $fontLettura'>";
        $fin_tag = "</span>";
        if ($arcite_rec['ITEEVIDENZA'] == 1) {
            $ini_tag = "<span style = 'display:inline-block; color:#BE0000; $fontLettura'>";
            $fin_tag = "</span>";
        }
        if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            $ini_tag = "<span style = 'display:inline-block; color:white;background-color:black; $fontLettura'>";
            $fin_tag = "</span>";
        }




        $record['ITEPAR'] = '<div class="ita-html ">' . $ini_tag . $record['ITEPAR'] . $fin_tag . '</div>';
        $record['NUMERO'] = $ini_tag . $record['NUMERO'] . $fin_tag;
        $record['ITEDAT'] = $ini_tag . date('d/m/Y', strtotime($itedat)) . $fin_tag;

        // Controllo speizione PEC!
        if (trim(strtoupper($anapro_rec['PROTSP'])) == 'PEC') {
            $ini_tagOggetto .= '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-mail-closed" title="Da PEC"></span>';
            $fin_tagOggetto = '';
        } else {
            $ini_tagOggetto .= '<span style="width:18px; display:inline-block;"></span>';
            $fin_tagOggetto = '';
        }

        if ($this->CaricamentoContabilitaHalley && $anapro_rec['PROCODTIPODOC'] == 'EFAA') {
            $tagHalley = '<div style="width:18px; display:inline-block;" title="Aggiorna Fattura su Contabilita"  class="ita-tooltip"><a href="#" id="' . $record['ROWID'] . '" class="ita-hyperlink {event:\'AggiornaFatturaHalley\'}">';
            $tagHalley .= '<span style="display:inline-block;vertical-align:bottom;" class="ita-icon ita-icon-euro-blue-16x16"></span><span class="ita-icon ita-icon-edit-16x16" style = " margin-left:-8px; display:inline-block;"></span></a></div>';
            $ini_tagOggetto = $tagHalley . $ini_tagOggetto;
        }


        if ($this->proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $record['OGGETTO'] = $ini_tagOggetto . "<p style=\"display:inline-block; background-color:lightgrey;$fontLettura\">RISERVATO</p>" . $fin_tagOggetto;
        } else {
            $record['OGGETTO'] = $ini_tagOggetto . $ini_tag . $record['OGGETTO'] . $fin_tag . $fin_tagOggetto;
        }
        $record['OGGETTO'] = '<div class="ita-html">' . $record['OGGETTO'] . '</div>';


        $record['PROVENIENZA'] = $ini_tag . $record['PROVENIENZA'] . $fin_tag;
//        $record['NDESTPROT'] = $ini_tag . $record['NDESTPROT'] . $fin_tag;
        if ($arcite_rec['GIORNITERMINE'] > 1000) {
            $arcite_rec['GIORNITERMINE'] = '';
        }
        $opacity = "";
        if ($arcite_rec['GIORNITERMINE']) {

            if ($arcite_rec['GIORNITERMINE'] <= 15) {
                $delta = 15 - $arcite_rec['GIORNITERMINE'];
                $opacity1 = (($delta <= 15) ? $delta * (100 / 15) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
        }
        $record['GIORNITERMINE'] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $arcite_rec['GIORNITERMINE'] . '</span></div>';
        $nofas = '';
        if ($arcite_rec['ITEPAR'] != 'W') {
            if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                $nonFas = '<div class="ita-html" style="display:inline-block;" ><span style="display:inline-block" title="Non Fascicolato" class="ita-tooltip ui-icon ui-icon-notice">Non Fascicolato </span></div>';
            }
        }
        /*
         * Aggiunte Informazioni utente
         */
        $sql = "SELECT ROWID, PROUTE FROM ANAPROSAVE WHERE PRONUM=" . $anapro_rec['PRONUM'] . " AND PROPAR='" . $anapro_rec['PROPAR'] . "' ORDER BY ROWID";
        $anaprosave_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        $Utente = $anapro_rec['PROUTE'];
        if ($anaprosave_rec) {
            $Utente = $anaprosave_rec['PROUTE'];
        }
        $InfoUtente = '<div style="display:inline-block;" ><span style="display:inline-block" title="Creato Da ' . $Utente . '" class="ita-tooltip ui-icon ui-icon-person">Creato Da </span></div>';

        //$record['GIORNITERMINE'] = $ini_tag . $arcite_rec['GIORNITERMINE'] . $fin_tag;
        $record['NDESTITER'] = $ini_tag . $record['NDESTITER'] . $fin_tag;
        $record['NDESLETTI'] = $ini_tag . $record['NDESLETTI'] . $fin_tag;
        $protColleg = '';
        if ($anapro_rec['PROPRE'] > 0 && $anapro_rec['PROPARPRE'] != '') {
            $protColleg = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
        } else {
            $Anno = substr($anapro_rec['PRONUM'], 0, 4);
            $Numero = substr($anapro_rec['PRONUM'], 4);
            if ($this->proLib->checkRiscontro($Anno, $Numero, $anapro_rec['PROPAR'])) {
                $protColleg = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
            }
        }
        // Delegato:
        $StatoDelega = $this->checkStatoDelega($arcite_rec);
        $InfoAggiuntive = ' ' . $StatoDelega . $nonFas . $InfoUtente . $protColleg;
        $record['STATO'] = '<div class="ita-html ">' . $record['STATO'] . $InfoAggiuntive . '</div>';
        $record['ALLEGATI'] = '<div style="padding:3px;" class="ita-html "><a href="#" id="' . $anapro_rec['ROWID'] . '" class="ita-hyperlink {event:\'VediAllegatiProt\'}"><span style="" title="Vedi Allegati"  class="ita-tooltip ita-icon ita-icon-clip-16x16"></span></div>';
        /*
         * Trasmissione per Ufficio:
         */
        $ini_tagUff = $fin_tagUff = '';
        if (!$arcite_rec['ITEDES'] && $arcite_rec['ITEUFF']) {
            $ini_tagUff = "<div style= 'background:#B2F7DC;'>";
            $fin_tagUff = "</div>";
            $record['OGGETTO'] = $ini_tagUff . $record['OGGETTO'] . $fin_tagUff;
            $record['PROVENIENZA'] = $ini_tagUff . $record['PROVENIENZA'] . $fin_tagUff;
        }
        if ($this->visDelegheAttive) {
            if ($arcite_rec['ITEDES'] != $this->codiceDest && $arcite_rec['ITEDES']) {
                $ini_tagUff = "<div style= 'background:#F6CECE;'>";
                $fin_tagUff = "</div>";
                $record['OGGETTO'] = $ini_tagUff . $record['OGGETTO'] . $fin_tagUff;
                $record['PROVENIENZA'] = $ini_tagUff . $record['PROVENIENZA'] . $fin_tagUff;
            }
        }

        /*
         * Valorizzo le colonne Procedimento ed Info della Pratica
         */
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
        $praLibPratica = praLibPratica::getInstance();
        $record['PROCEDIMENTO'] = $praLibPratica->getHtmlInfoProcedimento($propas_rec['PRONUM']);
        $record['DATIAGGIUNTIVI'] = $praLibPratica->getHtmlInfoDatiAggiuntivi($propas_rec['PRONUM']);

        return $record;
    }

    private function checkStatoDelega($arcite_rec) {
        $InfoTrx = '';
        $TitoloTrx = '';
        $DelegaArcite_rec = array();
        /*
         * Controllo se è delegato o delegante:
         */
        if ($arcite_rec['ITETIP'] == proIter::ITETIP_PARERE_DELEGA) {
            $TitoloTrx .= "<center>ITER RICEVUTO PER DELEGA</center>";
            $DelegaArcite_rec = $this->proLib->GetArcite($arcite_rec['ITEPRE'], 'itekey');
        } else {
            // Controllo se il protocollo inviato al delegato è stato già gestito.
            $TitoloTrx .= "<center>ITER DELEGATO</center>";
            $sql = "SELECT * FROM ARCITE WHERE ITEPRE = '" . $arcite_rec['ITEKEY'] . "' AND ITETIP = '" . proIter::ITETIP_PARERE_DELEGA . "' ";
            $DelegaArcite_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        }
        if ($DelegaArcite_tab) {
            foreach ($DelegaArcite_tab as $DelegaArcite_rec) {
                $InfoTrx = $InfoTrx . ' ' . $this->GetDelegaInfoTrx($DelegaArcite_rec, $TitoloTrx, true);
            }
        } else {
            if ($DelegaArcite_rec) {
                $InfoTrx = $this->GetDelegaInfoTrx($DelegaArcite_rec, $TitoloTrx, false);
            }
        }

        return $InfoTrx;
    }

    private function GetDelegaInfoTrx($DelegaArcite_rec, $TitoloTrx, $Delegato = false) {
        $InfoTrx = '';
        $Anamed_rec = $this->proLib->GetAnamed($DelegaArcite_rec['ITEDES'], 'codice');
        if ($Delegato) {
            $TitoloTrx .= 'Assegnato a: ' . $Anamed_rec['MEDNOM'] . '<br>';

            if ($DelegaArcite_rec['ITEDLE'] != '') {
                /*
                 * In Visione:
                 */
                if ($DelegaArcite_rec['ITEGES'] != 1) {
                    $dataLet = substr($DelegaArcite_rec['ITEDLE'], 6, 2) . "/" . substr($DelegaArcite_rec['ITEDLE'], 4, 2) . "/" . substr($DelegaArcite_rec['ITEDLE'], 0, 4);
                    $TitoloTrx .= 'Letto in data ' . $dataLet;
                    $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip ita-icon ita-icon-apertagray-16x16\"></span>";
                } else {
                    /*
                     * In Gestione
                     */
                    if ($DelegaArcite_rec['ITESTATO'] == 2) {
                        /* Controllo protocollo preso in carico. */
                        if ($DelegaArcite_rec['ITEDATACC']) {
                            $dataAcc = "in data " . substr($DelegaArcite_rec['ITEDATACC'], 6, 2) . "/" . substr($DelegaArcite_rec['ITEDATACC'], 4, 2) . "/" . substr($DelegaArcite_rec['ITEDATACC'], 0, 4);
                        }
                        $TitoloTrx .= "Preso in carico $dataAcc";
                        $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip ita-icon ita-icon-mail-green-verify-16x16\"></span>";
                    } else {
                        /* Protocollo Letto */
                        $dataLet = substr($DelegaArcite_rec['ITEDLE'], 6, 2) . "/" . substr($DelegaArcite_rec['ITEDLE'], 4, 2) . "/" . substr($DelegaArcite_rec['ITEDLE'], 0, 4);
                        $TitoloTrx .= "Letto in data $dataLet";
                        $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip ita-icon ita-icon-apertagreen-16x16\"></span>";
                    }
                }
                /* Verifico se inviato */
                if ($DelegaArcite_rec['ITESUS']) {
                    // Inviato Dopo la gestione o In visione e ritrasmesso.
                    if ($DelegaArcite_rec['ITESTATO'] == '2' || ($DelegaArcite_rec['ITESTATO'] == '0' && $DelegaArcite_rec['ITEGES'] == 0)) {
                        $TitoloTrx .= "<br>Trasmissione Inviata ";
                        $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip ita-icon ita-icon-check-red-16x16\"></span>";
                    }
                }
                /*
                 * Verifico trasmissione chiusa
                 */
                if ($DelegaArcite_rec['ITEFIN']) {
                    $dataFin = substr($DelegaArcite_rec['ITEFIN'], 6, 2) . "/" . substr($DelegaArcite_rec['ITEFIN'], 4, 2) . "/" . substr($DelegaArcite_rec['ITEFIN'], 0, 4);
                    if ($DelegaArcite_rec['ITEGES']) {
                        $TitoloTrx .= "<br>Trasmissione chiusa il $dataFin";
                    } else {
                        $TitoloTrx .= "<br>Presa in carico il $dataFin";
                    }
                    $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip ita-icon ita-icon-check-red-16x16\"></span>";
                }
                /* Verifico se rifiutato? */
            } else {
                $TitoloTrx .= "Trasmissione non gestita";
                if ($DelegaArcite_rec['ITEGES'] != 1) {
                    $InfoTrx = '<span style="display:inline-block" title="' . $TitoloTrx . '" class="ita-tooltip ita-icon ita-icon-chiusagray-16x16"></span>';
                } else {
                    $InfoTrx = '<span style="display:inline-block" title="' . $TitoloTrx . '"  class="ita-tooltip ita-icon ita-icon-chiusagreen-16x16"></span>';
                }
            }
        } else {
            $TitoloTrx .= 'Delegato da: ' . $Anamed_rec['MEDNOM'] . '<br>';
            $InfoTrx = "<span style=\"display:inline-block\" title=\"$TitoloTrx\" class=\"ita-tooltip\"></span>";
        }
        if ($InfoTrx) {
            $InfoTrx = $InfoTrx . "<span  class=\"ita-tooltip ita-icon ita-icon-user-16x16\" style = \"margin-left:-22px; display:inline-block; height:13px; \" title=\"$TitoloTrx\"></span> ";
        }
        return $InfoTrx;
    }

    private function creaSql() {
        $sql0 = '';
        switch ($_POST[$this->nameForm . '_OpzioniVisualizzazione']) {
            case 2:
                /*
                 * In questo caso vengono viste solo le voci chiuse e non inviate
                 * Caso cosmari non si vederebbero tutte. DA verificare.
                 */
                $sql0 .= " (";
                $sql0 .= " (ARCITE.ITESTATO='1' OR ARCITE.ITEFLA='2')";
                $sql0 .= " AND ARCITE.ITEFIN<>''";
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                $sql0 .= ")";
                break;
            case 3:
                /*
                 * Nel caso sia stato chiuso oltre che inviato non viene visto tra gli inviati, 
                 * Cosmari invia e chiude. Eliminare il filtro nello stato in modo da vedere tutti gli inviati anche se chiusi?.
                 *
                 */
                $sql0 .= " ( (ARCITE.ITESTATO='2' AND ARCITE.ITESUS<>'') OR "; /* PATCH  inviato dopo gestione */
                $sql0 .= "   (ARCITE.ITESTATO='0' AND ARCITE.ITESUS<>'' AND ARCITE.ITEGES = 0 )) "; /* PATCH * inviato senza gestione, solo visione */
                break;
            case 4:
                $sql0 .= " ARCITE.ITESTATO='0'";
                $sql0 .= " AND ARCITE.ITEFIN=''"; //non chiuso
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                $sql0 .= " AND ARCITE.ITETERMINE<>'' AND ARCITE.ITETERMINE<'" . date('Ymd') . "'"; //non inviato
                break;
            case 5:
                $sql0 .= " ARCITE.ITESTATO='1'";
                $sql0 .= " AND ARCITE.ITEDATRIF<>''";
                break;
            case 6:
                $sql0 .= " (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
                $sql0 .= " AND ARCITE.ITEFIN=''"; //non chiuso
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                $sql0 .= " AND ARCITE.ITETIP='" . proIter::ITETIP_ALLAFIRMA . "' AND ARCITE.ITEGES='1'"; //da firmare
                break;
            case 1:
            default:
                $sql0 .= " (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
                $sql0 .= " AND ARCITE.ITEFIN=''"; //non chiuso
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                break;
        }

        switch ($_POST[$this->nameForm . "_selectUffici"]) {
            case '*':
                $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, '', true);
                break;
            default:
                $uffdes_tab = array(array('UFFCOD' => $_POST[$this->nameForm . "_selectUffici"]));
                $filtroUff = "ARCITE.ITEUFF='{$_POST[$this->nameForm . "_selectUffici"]}' AND ";
                break;
        }

        $sqlLimite = '';
        $limiteVis = $_POST[$this->nameForm . '_LimiteVis'];
        if ($_POST[$this->nameForm . '_OpzioniVisualizzazione'] == 2) {
            // Setto Default.
            $limiteGiorni = '30';
            switch ($limiteVis) {
                case "":
                    $limiteGiorni = '30';
                    break;
                case "DATE":
                    if ($_POST[$this->nameForm . '_trasmessoDal']) {
                        $limiteGiorni = '';
                        $dataLimite = $_POST[$this->nameForm . '_trasmessoDal'];
                    }
                    break;
                default:
                    $limiteGiorni = $limiteVis;
                    break;
            }
            if ($limiteGiorni) {
                $dataLimite = date('Ymd', strtotime('-' . $limiteGiorni . ' day', strtotime(date("Ymd"))));
            }
            $sqlLimite = " AND ARCITE.ITEDAT >= '$dataLimite' ";
        }

        $sqlBaseSoggetto = "
            SELECT
                * 
            FROM
                ARCITE
            WHERE
                ARCITE.ITEDES='" . $this->codiceDest . "' AND 
                $filtroUff
                $sql0 AND
                ARCITE.ITEBASE=0 $sqlLimite         
            ";
        $sqlBaseUffici = array();
        foreach ($uffdes_tab as $uffdes_rec) {

            $sqlBaseUffici[] = "
            SELECT
                * 
            FROM
                ARCITE 
            WHERE
               (
                  (  
                    ARCITE.ITEUFF='{$uffdes_rec['UFFCOD']}' AND 
                    ARCITE.ITEORGWORKLIV=1 AND
                    ARCITE.ITEDES<>'" . $this->codiceDest . "' 
                   )   
                  OR
                  ( 
                    ARCITE.ITEDES = '' AND ARCITE.ITEUFF='{$uffdes_rec['UFFCOD']}' AND ARCITE.ITESUS='' 
                   )
                )    
                AND $sql0 AND ARCITE.ITEBASE=0  $sqlLimite
            ";
        }
        /*
         * Sql Base Deleghe:
         * - Deve vedere ciò che è assegnato al delegante:
         */
        if ($this->visDelegheAttive) {
            foreach ($this->visDelegheAttive as $Delega_rec) {
                $sqlBaseDeleghe[] = "
                SELECT
                    *
                FROM
                    ARCITE
                WHERE 
                    ARCITE.ITEUFF='{$Delega_rec['DELESRCUFF']}' AND
                    ARCITE.ITEDES='{$Delega_rec['DELESRCCOD']}' AND
                    $sql0 AND
                    ARCITE.ITEBASE=0 $sqlLimite AND
                    (SELECT COUNT(ROWID) FROM ARCITE ARCITE_ASSEGNATI WHERE ARCITE_ASSEGNATI.ITEPRE=ARCITE.ITEKEY AND ARCITE_ASSEGNATI.ITEDES='{$this->codiceDest}')=0";
            }
        }
        $sqlBase = "$sqlBaseSoggetto UNION ALL " . implode(' UNION ALL ', $sqlBaseUffici);
        if (count($sqlBaseDeleghe)) {
            $sqlBase .= '  UNION ALL ' . implode(' UNION ALL ', $sqlBaseDeleghe);
        }
        $JoinIndice = $WhereIndice = $WhereIdelibNumero = '';
        if ($this->segLib->checkExistDB('SEGR')) {
            $segrDB = $this->segLib->getSEGRDB()->getDB();
            $JoinIndice = " LEFT OUTER JOIN $segrDB.INDICE INDICE ON $segrDB.INDICE.INDPRO=ARCITE.ITEPRO AND $segrDB.INDICE.INDPAR=ARCITE.ITEPAR AND $segrDB.INDICE.INDTIPODOC = '" . segLibDocumenti::TIPODOC_DOCUMENTO . "'  ";
            $WhereIndice = " AND ( ($segrDB.INDICE.INDTIPODOC IS NOT NULL AND ARCITE.ITETIP='" . proIter::ITETIP_ALLAFIRMA . "') OR ARCITE.ITEPAR <> 'I') ";
            $WhereIdelibNumero = " OR ($segrDB.INDICE.IDELIB LIKE '%" . $_POST['NUMERO'] . "%') ";
        } else {
            $WhereIndice = " AND ARCITE.ITEPAR <> 'I' ";
        }
        // JOIN PRATICA SOLO SE FILTRO PER OGGETTO:
        $JoinPratica = $WherePraticaNumero = "";
        if ($_POST['NUMERO']) {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
            $praLib = new praLib();
            $pramDB = $praLib->getPRAMDB()->getDB();

            $JoinPratica = ""
                    . "  LEFT OUTER JOIN $pramDB.PROPAS ON $pramDB.PROPAS.PASPRO = ARCITE.ITEPRO AND $pramDB.PROPAS.PASPAR = ARCITE.ITEPAR "
                    . "  LEFT OUTER JOIN $pramDB.PROGES ON $pramDB.PROPAS.PRONUM=$pramDB.PROGES.GESNUM"
                    . "  LEFT OUTER JOIN  ANASERIEARC ON $pramDB.PROGES.SERIECODICE = ANASERIEARC.CODICE ";
            $campiSeparati = explode('/', $_POST['NUMERO']);
            if (count($campiSeparati) > 1) {
                $WherePraticaNumero = " OR ($pramDB.PROGES.SERIEANNO LIKE '%" . $campiSeparati[2] . "%' OR  $pramDB.PROGES.SERIEPROGRESSIVO LIKE '%" . $campiSeparati[1] . "%' OR $pramDB.PROGES.SERIECODICE LIKE '%" . $campiSeparati[0] . "%' OR  ANASERIEARC.SIGLA LIKE '%" . $campiSeparati[0] . "%' )";
            } else {
                $WherePraticaNumero = " OR ($pramDB.PROGES.SERIEANNO LIKE '%" . $_POST['NUMERO'] . "%' OR  $pramDB.PROGES.SERIEPROGRESSIVO LIKE '%" . $_POST['NUMERO'] . "%' OR $pramDB.PROGES.SERIECODICE LIKE '%" . $_POST['NUMERO'] . "%' OR  ANASERIEARC.SIGLA LIKE '%" . $_POST['NUMERO'] . "%' )";
            }
        }

        $oggi = date('Ymd');
        $sql = "SELECT
                    ARCITE.ROWID AS ROWID,
                    ARCITE.ITEDAT,
                    ARCITE.ITEDES,
                    ARCITE.ITEUFF,
                    ARCITE.ITEPRO,
                    ARCITE.ITEPAR,
                    " . $this->PROT_DB->subString('ARCITE.ITEPRO', 1, 4) . " AS ANNO,
                    " . $this->PROT_DB->subString('ARCITE.ITEPRO', 5, 6) . " AS NUMERO,
                    ARCITE.ITEDLE,
                    ARCITE.ITESTATO,
                    ARCITE.ITEDATACC,
                    ARCITE.ITEGES,
                    ARCITE.ITESUS,
                    ARCITE.ITETERMINE,
                    " . $this->PROT_DB->dateDiff($this->PROT_DB->coalesce($this->PROT_DB->nullIf("ARCITE.ITETERMINE", "''"), "'20681231'"), "'$oggi'") . " AS GIORNITERMINE,                    
                    ARCITE.ITEDATRIF,
                    ARCITE.ITEFLA,
                    ARCITE.ITEEVIDENZA,
                    ARCITE.ITEORGWORKLIV,
                    ARCITE.ITEANN,
                    ARCITE.ITETIP,
                    ARCITE.ITENTRAS,
                    ARCITE.ITENLETT,
                    ARCITE.ITEKEY,
                    ARCITE.ITEPRE,
                    ARCITEPADRE.ITESTATO AS STATOPADRE,
                    ARCITEPADRE.ITEMOTIVO AS ITEMOTIVOP,
                    ANAOGG.OGGOGG AS OGGETTO,
                    ANAPRO.PRONOM AS PROVENIENZA,
                    ANAPRO.PROCAT,
                    ANAPRO.PROCCA,
                    ANAPRO.PRORISERVA,
                    ANAPRO.PROTSO,
                    ANAPRO.PRONOM,
                    ANAPRO.PROCODTIPODOC,
                    ANADES_FIRMATARIO.DESNOM AS DESNOM_FIRMATARIO,
                    0 AS NDESTPROT
                FROM ($sqlBase) ARCITE
                    LEFT OUTER JOIN ANAOGG FORCE INDEX(I_OGGPAR) ON ARCITE.ITEPRO=ANAOGG.OGGNUM AND ARCITE.ITEPAR=ANAOGG.OGGPAR
                    LEFT OUTER JOIN ANAPRO FORCE INDEX(I_PROPAR) ON ARCITE.ITEPRO=ANAPRO.PRONUM AND ARCITE.ITEPAR=ANAPRO.PROPAR
                    LEFT OUTER JOIN ARCITE ARCITEPADRE FORCE INDEX(I_ITEKEY) ON ARCITE.ITEPRE = ARCITEPADRE.ITEKEY
                    LEFT OUTER JOIN PROGES PROGES FORCE INDEX(I_GESKEY) ON PROGES.GESKEY = ANAPRO.PROFASKEY
                    LEFT OUTER JOIN ANADES ANADES_FIRMATARIO FORCE INDEX(I_DESPAR) ON ANAPRO.PRONUM=ANADES_FIRMATARIO.DESNUM AND ANAPRO.PROPAR=ANADES_FIRMATARIO.DESPAR AND (ANADES_FIRMATARIO.DESPAR = 'C' OR ANADES_FIRMATARIO.DESPAR = 'P' OR ANADES_FIRMATARIO.DESPAR = 'I') AND ANADES_FIRMATARIO.DESTIPO = 'M'
                    $JoinIndice $JoinPratica
                    WHERE ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO; // . " AND ARCITE.ITEPAR<>'I'";

        $where = '';
        if ($_POST['ITEPAR']) {
            $where .= " AND " . $this->PROT_DB->strUpper('ARCITE.ITEPAR') . " = '" . addslashes(strtoupper($_POST['ITEPAR'])) . "'";
        }
        $where .= $WhereIndice;
        if ($_POST['NUMERO']) {
            if ($_POST['ITEPAR'] == 'F' || $_POST['ITEPAR'] == 'N') {
                $where .= " AND ANAPRO.PROFASKEY='%{$_POST['NUMERO']}%'";
            } else if ($_POST['ITEPAR']) {
                $campiSeparati = explode('/', $_POST['NUMERO']);
                if (count($campiSeparati) == 2) {
                    $where .= " AND ARCITE.ITEPRO LIKE '" . (int) $campiSeparati[1] . str_pad((int) $campiSeparati[0], 6, "0", STR_PAD_LEFT) . "'";
                } else {
                    $where .= " AND ARCITE.ITEPRO LIKE '%{$_POST['NUMERO']}%' $WherePraticaNumero $WhereIdelibNumero";
                }
            } else {
                $campiSeparati = explode('/', $_POST['NUMERO']);
                if (count($campiSeparati) == 2) {
                    $where .= " AND ARCITE.ITEPRO LIKE '" . (int) $campiSeparati[1] . str_pad((int) $campiSeparati[0], 6, "0", STR_PAD_LEFT) . "' ";
                } else {
                    $where .= " AND (ARCITE.ITEPRO LIKE '%{$_POST['NUMERO']}%' OR ANAPRO.PROFASKEY LIKE '%{$_POST['NUMERO']}%' $WherePraticaNumero $WhereIdelibNumero)";
                }
            }
        }

        if ($_POST['ITEDAT']) {
            if (strlen($_POST['ITEDAT']) == 8) {
                $data = substr($_POST['ITEDAT'], 4) . substr($_POST['ITEDAT'], 2, 2) . substr($_POST['ITEDAT'], 0, 2);
            } else if (strlen($_POST['ITEDAT']) == 10) {
                $data = substr($_POST['ITEDAT'], 6) . substr($_POST['ITEDAT'], 3, 2) . substr($_POST['ITEDAT'], 0, 2);
            }
            if ($data) {
                $where .= " AND ARCITE.ITEDAT= '" . $data . "'";
            }
        }
        if ($_POST['OGGETTO']) {
            $where .= " AND " . $this->PROT_DB->strUpper('ANAOGG.OGGOGG') . " LIKE '%" . addslashes(strtoupper($_POST['OGGETTO'])) . "%'";
        }
        if ($_POST['PROVENIENZA']) {
            $where .= " AND " . $this->PROT_DB->strUpper('ANAPRO.PRONOM') . " LIKE '%" . addslashes(strtoupper($_POST['PROVENIENZA'])) . "%'";
        }
        if ($_POST['id'] == $this->nameForm . '_Chiudi' || $_POST['id'] == $this->nameForm . '_Accetta' || $_POST['id'] == $this->nameForm . '_Invia' || $_POST['id'] == $this->nameForm . '_Fascicola') {
            if ($_POST[$this->nameForm . '_DaDataElenco'] != '') {
                $where .= " AND ARCITE.ITEDAT >= '" . $_POST[$this->nameForm . '_DaDataElenco'] . "'";
            }
            if ($_POST[$this->nameForm . '_ADataElenco'] != '') {
                $where .= " AND ARCITE.ITEDAT <= '" . $_POST[$this->nameForm . '_ADataElenco'] . "'";
            }
        }

        $utente = proSoggetto::getCodiceSoggettoFromIdUtente();
        $where .= " AND ((ANAPRO.PRORISERVA<>'1' OR ANAPRO.PROTSO<>'1' AND ARCITE.ITEDES<>'$utente') OR ARCITE.ITEDES='$utente')";
        $sql .= $where;
        if ($_POST[$this->nameForm . '_trasmessoDal']) {
            $sql .= " AND ARCITE.ITEDAT >= '" . $_POST[$this->nameForm . '_trasmessoDal'] . "'";
        }
        if ($_POST[$this->nameForm . '_trasmessoAl']) {
            $sql .= " AND ARCITE.ITEDAT <= '" . $_POST[$this->nameForm . '_trasmessoAl'] . "'";
        }
        $anaent_41 = $this->proLib->GetAnaent('41');
        // Visualizza Gestione, In Visione o Tutti .
        switch ($_POST[$this->nameForm . '_selectGesVis']) {
            case '0':
                $sql .= " AND (ARCITE.ITEGES ='0' OR ARCITE.ITEGES ='') ";
                break;
            case '1':
                $sql .= " AND ARCITE.ITEGES ='1'";
                break;
            case '2':
                $sql .= " AND (ANAPRO.PROCODTIPODOC ='EFAA' OR ANAPRO.PROCODTIPODOC ='EFAP' OR ANAPRO.PROCODTIPODOC ='SDIA' OR ANAPRO.PROCODTIPODOC ='SDIP' OR ANAPRO.PROCODTIPODOC ='EFAS') ";
                break;
            case '3':
                if ($anaent_41['ENTVAL']) {
                    $sql .= " AND ANAPRO.PROCODTIPODOC ='" . $anaent_41['ENTVAL'] . "' ";
                }
                break;
            default:
                break;
        }

        switch ($_POST[$this->nameForm . '_selectVisLetti']) {
            case '1':
                $sql .= " AND ARCITE.ITEDLE <> '' ";
                break;
            case '2':
                $sql .= " AND ARCITE.ITEDLE = '' ";
                break;
            default:
                break;
        }

        // Controllo quello che l'utente ha inviato al dest:
        if ($_POST[$this->nameForm . '_TrasmDest']) {
            $sql .= " AND ARCITE.ITESUS ='" . $_POST[$this->nameForm . '_TrasmDest'] . "' ";
        }
        if ($_POST[$this->nameForm . '_CreatoDa']) {
            //  $sql.=" AND ANAPRO.PROUTE ='" . $_POST[$this->nameForm . '_CreatoDa'] . "' ";
        }

        $sql .= " GROUP BY ARCITE.ROWID ";

        if (!$this->giorniTermineDefault) {
            $this->giorniTermineDefault = '99999999999';
        }


        $anaent_48 = $this->proLib->GetAnaent('48');
        // Solo quando vede protocolli in carico?
        if ($anaent_48['ENTDE2']) {
            $sql = "SELECT A.*,
                            DOCFIRMA.FIRDATA,   
                            DOCFIRMA.FIRDATARICH
                     FROM ($sql) A 
                     LEFT OUTER JOIN ANADOC FORCE INDEX(I_DOCNUM) ON ANADOC.DOCNUM = A.ITEPRO AND ANADOC.DOCPAR=A.ITEPAR 
                     LEFT OUTER JOIN DOCFIRMA FORCE INDEX(ROWIDANADOC) ON DOCFIRMA.ROWIDANADOC = ANADOC.ROWID AND DOCFIRMA.FIRDATA IS NOT NULL
                     LEFT OUTER JOIN ARCITE ARCITEFIRMA ON DOCFIRMA.ROWIDARCITE = ARCITEFIRMA.ROWID AND DOCFIRMA.FIRDATA IS NOT NULL
                        WHERE (
                                (DOCFIRMA.FIRDATARICH IS NULL AND (SELECT COUNT(ANADOC.ROWID) FROM ANADOC LEFT OUTER JOIN DOCFIRMA ON ANADOC.ROWID = DOCFIRMA.ROWIDANADOC WHERE DOCNUM=A.ITEPRO AND ANADOC.DOCPAR=A.ITEPAR AND DOCFIRMA.FIRDATA IS NOT NULL) = 0)
                                OR (DOCFIRMA.FIRDATA <> '' AND DOCFIRMA.FIRDATA IS NOT NULL ) 
                                OR (DOCFIRMA.FIRDATARICH IS NOT NULL AND DOCFIRMA.FIRCOD = '$this->codiceDest')
                                OR (DOCFIRMA.FIRDATA = '' AND ARCITEFIRMA.ITEDATRIF <> '' )
                               )
                    AND A.GIORNITERMINE <= " . $this->giorniTermineDefault . "
                    GROUP BY A.ROWID ";
        } else {
            $sql = "SELECT * FROM ($sql) A ";
            $sql .= " WHERE A.GIORNITERMINE <= " . $this->giorniTermineDefault;
        }

        // Out::msgInfo("SqlBase", $sql);
        return $sql;
    }

    private function selezionaDaAccettare() {
        $_POST[$this->nameForm . '_OpzioniVisualizzazione'] = "1";
        $arcite_tab = $this->proLib->getGenericTab($this->creaSql());
        if (count($arcite_tab) > 300) {
            Out::msgStop("Attenzione", "Selezione troppo ampia, trovati " . count($arcite_tab) . " risultati. Sono gestibili fino a 300 risultati. Restringere gli intervalli di date.");
            return;
        }
        if (!$arcite_tab) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }
        foreach ($arcite_tab as $key => $arcite_rec) {
            $arcite_tab[$key]['NUMERO'] = ((int) substr($arcite_rec['ITEPRO'], 4)) . '/' . substr($arcite_rec['ITEPRO'], 0, 4);
            $arcite_tab[$key]['ITEDAT'] = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
            $anaogg_rec = $this->proLib->GetAnaogg($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
            $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
            if ($arcite_tab[$key]['PRORISERVA'] == '1' || $arcite_tab[$key]['PROTSO']) {
                $arcite_tab[$key]['OGGETTO'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
                $arcite_tab[$key]['PROVENIENZA'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
//                unset($arcite_tab[$key]);
//                continue;
            } else {
                // Le trasmissioni di rifiuto devono essere escluse:
                if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
                    unset($arcite_tab[$key]);
                    continue;
                }
                $arcite_tab[$key]['OGGETTO'] = $anaogg_rec['OGGOGG'];
                $arcite_tab[$key]['PROVENIENZA'] = $anapro_rec['PRONOM'];
                if ($arcite_rec['ITEPAR'] == 'C') {
                    $arcite_tab[$key]['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
                }
            }
            $arcite_tab[$key]['LEGAME'] = '';
            if ($anapro_rec['PROPRE'] > 0 && $anapro_rec['PROPARPRE'] != '') {
                $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
            } else {
                $Anno = substr($anapro_rec['PRONUM'], 0, 4);
                $Numero = substr($anapro_rec['PRONUM'], 4);
                if ($this->proLib->checkRiscontro($Anno, $Numero, $anapro_rec['PROPAR'])) {
                    $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
                }
            }
            $chiave = "@" . $key;
            $arcite_tab2[$chiave] = $arcite_tab[$key];
        }
        if (!$arcite_tab2) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }
        $this->arrSelezionati = $arcite_tab2;
        $colNames = array(
            "",
            "Numero",
            "Data",
            "Oggetto",
            "Provenienza",
            "In Gestione",
            "Prot. Collegato"
        );
        $colModel = array(
            array("name" => 'ITEPAR', "width" => 20),
            array("name" => 'NUMERO', "width" => 65),
            array("name" => 'ITEDAT', "width" => 75),
            array("name" => 'OGGETTO', "width" => 320),
            array("name" => 'PROVENIENZA', "width" => 220),
            array("name" => 'ITEGES', "width" => 70, "formatter" => "'checkbox'", "formatoptions" => "{disabled:true}"),
            array("name" => 'LEGAME', "width" => 50)
        );
        $dim = array('width' => '890', 'height' => '400');

        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        $msgDetail = "<span style=\"font-size:1.4em;color:green;\"><b>I protocolli selezionati saranno accettati.</b></span>";
        proRic::proMultiselectGeneric(
                $arcite_tab2, $this->nameForm, 'DaAccettare', 'Seleziona le Trasmissioni da Accettare', $colNames, $colModel, $msgDetail, $dim
        );
    }

    private function selezionaDaChiudere() {
        $_POST[$this->nameForm . '_OpzioniVisualizzazione'] = "1";
        $arcite_tab = $this->proLib->getGenericTab($this->creaSql());
        if (count($arcite_tab) > 300) {
            Out::msgStop("Attenzione", "Selezione troppo ampia, trovati " . count($arcite_tab) . " risultati. Sono gestibili fino a 300 risultati. Restringere gli intervalli di date.");
            return;
        }
        if (!$arcite_tab) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }
        foreach ($arcite_tab as $key => $arcite_rec) {
            $arcite_tab[$key]['NUMERO'] = ((int) substr($arcite_rec['ITEPRO'], 4)) . '/' . substr($arcite_rec['ITEPRO'], 0, 4);
            $arcite_tab[$key]['ITEDAT'] = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
            $anaogg_rec = $this->proLib->GetAnaogg($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
            $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
            if ($arcite_tab[$key]['PRORISERVA'] == '1' || $arcite_tab[$key]['PROTSO']) {
                $arcite_tab[$key]['OGGETTO'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
                $arcite_tab[$key]['PROVENIENZA'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
                unset($arcite_tab[$key]);
                continue;
            } else {
                // Le trasmissioni di rifiuto devono essere escluse:
                if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
                    unset($arcite_tab[$key]);
                    continue;
                }
                $arcite_tab[$key]['OGGETTO'] = $anaogg_rec['OGGOGG'];
                $arcite_tab[$key]['PROVENIENZA'] = $anapro_rec['PRONOM'];
                if ($arcite_rec['ITEPAR'] == 'C') {
                    $arcite_tab[$key]['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
                }
            }
            /*
             * Trasmissioni a Intero ufficio non si possono chiudere:
             */
            if ($arcite_rec['ITEDES'] == "") {
                unset($arcite_tab[$key]);
                continue;
            }

            if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
                $arcite_tab[$key]['STATO'] = "<span style=\"display:inline-block\" title=\"Da Rifiuto\" class=\"ita-icon ita-icon-divieto-16x16\"></span> " . $arcite_tab[$key]['STATO'];
            } else {
                $arcite_tab[$key]['STATO'] = "";
            }

            $arcite_tab[$key]['LEGAME'] = '';
            if ($anapro_rec['PROPRE'] > 0 && $anapro_rec['PROPARPRE'] != '') {
                $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
            } else {
                $Anno = substr($anapro_rec['PRONUM'], 0, 4);
                $Numero = substr($anapro_rec['PRONUM'], 4);
                if ($this->proLib->checkRiscontro($Anno, $Numero, $anapro_rec['PROPAR'])) {
                    $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
                }
            }
            $chiave = "@" . $key;
            $arcite_tab2[$chiave] = $arcite_tab[$key];
        }
        if (!$arcite_tab2) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }
        $this->arrSelezionati = $arcite_tab2;
        $colNames = array(
            "",
            "Numero",
            "Data",
            "Oggetto",
            "Provenienza",
            "In Gestione",
            "Prot. Collegato",
            " "
        );
        $colModel = array(
            array("name" => 'ITEPAR', "width" => 20),
            array("name" => 'NUMERO', "width" => 65),
            array("name" => 'ITEDAT', "width" => 75),
            array("name" => 'OGGETTO', "width" => 320),
            array("name" => 'PROVENIENZA', "width" => 220),
            array("name" => 'ITEGES', "width" => 70, "formatter" => "'checkbox'", "formatoptions" => "{disabled:true}"),
            array("name" => 'LEGAME', "width" => 50),
            array("name" => 'STATO', "width" => 20)
        );
        $dim = array('width' => '900', 'height' => '400');
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        $msgDetail = "<span style=\"font-size:1.4em;color:red;\"><b>I protocolli selezionati saranno chiusi.</b></span>";
        proRic::proMultiselectGeneric(
                $arcite_tab2, $this->nameForm, 'DaChiudere', 'Seleziona le Trasmissioni da Chiudere', $colNames, $colModel, $msgDetail, $dim
        );
    }

    private function selezionaDaInviare() {
        if (!$this->datiInvio['destinatari']) {
            Out::msgInfo("Informazione", "Nessun destinatario selezionato");
            return false;
        }

        $arcite_tab = $this->proLib->getGenericTab($this->sqlSelezioneInvio);
        foreach ($arcite_tab as $key => $arcite_rec) {
            $arcite_tab[$key]['NUMERO'] = ((int) substr($arcite_rec['ITEPRO'], 4)) . '/' . substr($arcite_rec['ITEPRO'], 0, 4);
            $arcite_tab[$key]['ITEDAT'] = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
            $anaogg_rec = $this->proLib->GetAnaogg($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
            $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
            if ($arcite_tab[$key]['PRORISERVA'] == '1' || $arcite_tab[$key]['PROTSO']) {
                unset($arcite_tab[$key]);
                continue;
            } else {
                // Le trasmissioni di rifiuto devono essere escluse:
                if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
                    unset($arcite_tab[$key]);
                    continue;
                }
                /*
                 * Trasmissioni a Intero ufficio non si possono chiudere:
                 */
                if ($arcite_rec['ITEDES'] == "") {
                    unset($arcite_tab[$key]);
                    continue;
                }
                $arcite_tab[$key]['OGGETTO'] = $anaogg_rec['OGGOGG'];
                $arcite_tab[$key]['PROVENIENZA'] = $anapro_rec['PRONOM'];
                if ($arcite_rec['ITEPAR'] == 'C') {
                    $arcite_tab[$key]['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
                }
            }
            $arcite_tab[$key]['LEGAME'] = '';
            if ($anapro_rec['PROPRE'] > 0 && $anapro_rec['PROPARPRE'] != '') {
                $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
            } else {
                $Anno = substr($anapro_rec['PRONUM'], 0, 4);
                $Numero = substr($anapro_rec['PRONUM'], 4);
                if ($this->proLib->checkRiscontro($Anno, $Numero, $anapro_rec['PROPAR'])) {
                    $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
                }
            }
            $chiave = "@" . $key;
            $arcite_tab2[$chiave] = $arcite_tab[$key];
        }
        if (!$arcite_tab2) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }
        $this->arrSelezionati = $arcite_tab2;
        $colNames = array(
            "",
            "Numero",
            "Data",
            "Oggetto",
            "Provenienza",
            "In Gestione",
            "Prot. Collegato",
        );
        $colModel = array(
            array("name" => 'ITEPAR', "width" => 20),
            array("name" => 'NUMERO', "width" => 65),
            array("name" => 'ITEDAT', "width" => 75),
            array("name" => 'OGGETTO', "width" => 320),
            array("name" => 'PROVENIENZA', "width" => 220),
            array("name" => 'ITEGES', "width" => 70, "formatter" => "'checkbox'", "formatoptions" => "{disabled:true}"),
            array("name" => 'LEGAME', "width" => 50)
        );
        $dim = array('width' => '890', 'height' => '400');
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

        $msgDest = $eleDest = '';
        foreach ($this->datiInvio['destinatari'] as $destinatario) {
            $eleDest .= $destinatario['DESNOMEUFF'] . '<br>';
        }
        $msgDest = '<div style="margin-left:40px;">' . $eleDest . '</div>';
        $msgDetail = "<span style=\"font-size:1.4em;color:red;\"><b>Le trasmissioni selezionate saranno inviate a:<br></b></span>" . $msgDest;

        proRic::proMultiselectGeneric(
                $arcite_tab2, $this->nameForm, 'DaInviare', 'Seleziona le Trasmissioni da Inviare', $colNames, $colModel, $msgDetail, $dim
        );
    }

    private function AttivaRadio() {
        Out::attributo($this->nameForm . "_Carico", "checked", "0", "checked");
        Out::hide($this->nameForm . '_chkAperti_field');
        Out::hide($this->nameForm . '_chkChiusi_field');
        Out::attributo($this->nameForm . "_chkAperti", "checked", "0", "checked");
        Out::attributo($this->nameForm . "_chkChiusi", "checked", "0", "checked");
    }

    private function CreaCombo() {
        $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
//        $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest);
        Out::select($this->nameForm . '_selectUffici', 1, "*", "0", '<p style="color:green;">Tutti</p>');
        foreach ($uffdes_tab as $uffdes_rec) {
            $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
            Out::select($this->nameForm . '_selectUffici', 1, $uffdes_rec['UFFCOD'], '0', substr($anauff_rec['UFFDES'], 0, 30));
        }
        // Tipo Visualizzazione: Visione o Gestione o Tutti
        Out::select($this->nameForm . '_selectGesVis', 1, ' ', '0', 'Tutti');
        Out::select($this->nameForm . '_selectGesVis', 1, '1', '0', 'In Gestione');
        Out::select($this->nameForm . '_selectGesVis', 1, '0', '0', 'In Visione');
        Out::select($this->nameForm . '_selectGesVis', 1, '2', '0', 'Fatture Elettroniche');
        Out::select($this->nameForm . '_selectGesVis', 1, '3', '0', 'Registri Giornalieri');

        Out::select($this->nameForm . '_selectVisLetti', 1, ' ', '0', 'Tutti');
        Out::select($this->nameForm . '_selectVisLetti', 1, '1', '0', 'Letti');
        Out::select($this->nameForm . '_selectVisLetti', 1, '2', '0', 'Non Letti');

        // Limite Vis
        Out::select($this->nameForm . '_LimiteVis', 1, "", "0", 'Ultimi 30 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "60", "0", 'Ultimi 60 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "90", "0", 'Ultimi 90 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "DATE", "0", 'Periodo Specifico');
    }

    private function caricaConfigurazioni() {
        $parametri = $this->getCustomConfig('CAMPIDEFAULT/DATI');
        foreach ($parametri as $key => $valore) {
            if ($key == $this->nameForm . '_OpzioniVisualizzazione') {
                switch ($valore) {
                    case '2':
                        Out::attributo($this->nameForm . "_Chiusi", "checked", "0", "checked");
                        break;
                    case '3':
                        Out::attributo($this->nameForm . "_Inviati", "checked", "0", "checked");
                        break;
                    case '4':
                        Out::attributo($this->nameForm . "_Scaduti", "checked", "0", "checked");
                        break;
                    case '5':
                        Out::attributo($this->nameForm . "_Rifiutati", "checked", "0", "checked");
                        break;
                    case '6':
                        Out::attributo($this->nameForm . "_DaFirmare", "checked", "0", "checked");
                        break;
                    case '1':
                    default :
                        Out::attributo($this->nameForm . "_Carico", "checked", "0", "checked");
                        break;
                }
            } else {
                if ($key == $this->nameForm . '_Giorni') {
                    $this->giorniTermineDefault = $valore;
                }
                Out::valore($key, $valore);
            }
        }
        $this->LeggiCaricamentoContabilitaHalley();
    }

    private function setConfig() {
        $parametri = array(
            $this->nameForm . '_selectUffici' => $_POST[$this->nameForm . '_selectUffici'],
            $this->nameForm . '_Giorni' => $_POST[$this->nameForm . '_Giorni'],
            $this->nameForm . '_OpzioniVisualizzazione' => $_POST[$this->nameForm . '_OpzioniVisualizzazione']
        );

        $this->setCustomConfig("CAMPIDEFAULT/DATI", $parametri);
        $this->saveModelConfig();
    }

    public function getPareriColor($arcite_rec) {
        $daDare = '<span class="ita-icon ita-icon-bullet-red-16x16" style="margin-left:1px;display:inline-block;"></span>';
        $dato = '<span class="ita-icon ita-icon-bullet-green-16x16" style="margin-left:1px;display:inline-block;"></span>';
        $datoNegativo = '<span class="ita-icon ita-icon-bullet-yellow-16x16" style="margin-left:1px;display:inline-block;"></span>';

        $daDare16 = '<span class="ita-icon ita-icon-bullet-red-16x16" style="height:8px; width:8px;background-size:50%;vertical-align:bottom;margin-left:1px;display:inline-block;"></span>';
        $dato16 = '<span class="ita-icon ita-icon-bullet-green-16x16" style="height:8px;width:8px;background-size:50%;vertical-align:bottom;margin-left:1px;display:inline-block;"></span>';
        $datoNegativo16 = '<span class="ita-icon ita-icon-bullet-yellow-16x16" style="height:8px;width:8px;background-size:50%;vertical-align:bottom;margin-left:1px;display:inline-block;"></span>';
        $pareri_rec = $this->segLib->GetPareri($arcite_rec['ROWID'], 'rowidarcite');
        App::log($pareri_rec);
        if ($pareri_rec) {
            $pareriProp_tab = $this->segLib->GetPareri($pareri_rec['CODTESTO'], 'codtesto');
            $pareriColor = ' ';
            foreach ($pareriProp_tab as $pareriProp_rec) {
                if ($pareri_rec['ROWID'] == $pareriProp_rec['ROWID']) {
                    if ($pareriProp_rec['DATAPARERE'] == '') {
                        $pareriColor .= $daDare;
                    } else {
                        if ($pareriProp_rec['ESITO'] == 0) {
                            $pareriColor .= $datoNegativo;
                        } else {
                            $pareriColor .= $dato;
                        }
                    }
                } else {
                    if ($pareriProp_rec['DATAPARERE'] == '') {
                        $pareriColor .= $daDare16;
                    } else {
                        if ($pareriProp_rec['ESITO'] == 0) {
                            $pareriColor .= $datoNegativo16;
                        } else {
                            $pareriColor .= $dato16;
                        }
                    }
                }
            }
        }
        return $pareriColor;
    }

    public function ContaDaFirmare() {
        $where = " (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
        $where .= " AND ARCITE.ITEFIN=''"; //non chiuso
        $where .= " AND ARCITE.ITESUS=''"; //non inviato
        $where .= " AND ARCITE.ITETIP='" . proIter::ITETIP_ALLAFIRMA . "' AND ARCITE.ITEGES='1'"; //da firmare
        $sql = "
            SELECT
                COUNT(*) AS TOTDAFIRMARE
            FROM
                ARCITE
                LEFT OUTER JOIN ANAPRO ANAPRO ON ARCITE.ITEPRO = ANAPRO.PRONUM AND ARCITE.ITEPAR = ANAPRO.PROPAR
            WHERE
                ARCITE.ITEDES='" . $this->codiceDest . "' AND 
                $where AND
                ARCITE.ITEBASE=0 AND
                ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO; // . " AND ARCITE.ITEPAR<>'I'
        //   ";
        $DaFirmare_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
//        Out::msgInfo('da firmare', print_r($DaFirmare_rec,true));
    }

    private function DecodAnamedTrasm($codice, $_tipoRic = 'codice', $_tutti = 'no') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $_tipoRic, $_tutti);
        Out::valore($this->nameForm . '_TrasmDest', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_TrasmDescr', $anamed_rec['MEDNOM']);
        return $anamed_rec;
    }

    public function openInviaTrasmissioni() {
        $_POST[$this->nameForm . '_OpzioniVisualizzazione'] = "1";
        $this->sqlSelezioneInvio = $this->creaSql();
        $arcite_tab = $this->proLib->getGenericTab($this->sqlSelezioneInvio);
        if (count($arcite_tab) > 300) {
            Out::msgStop("Attenzione", "Selezione troppo ampia, trovati " . count($arcite_tab) . " risultati. Sono gestibili fino a 300 risultati. Restringere gli intervalli di date.");
            return;
        }
        if (!$arcite_tab) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return;
        }

        $model = 'proInviaTrasmissioni';
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnEvent('returnInviaTrasmissioni');
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

    private function inviaTrasmissioni() {
        $arcite_tab = $this->arrSelezionati;
        $this->arrSelezionati = array();
        $chiavi = explode(',', $_POST['retKey']);
        foreach ($chiavi as $chiave) {
            $arcite_rec = $this->proLib->GetArcite($arcite_tab[$chiave]['ROWID'], 'rowid');

            if ($arcite_rec['ITEPAR'] == 'F' || $arcite_rec['ITEPAR'] == 'N') {
                $Note = "ESTENSIONE VISIBILITA' FASCICOLO. ";
                if ($arcite_rec['ITEPAR'] == 'N') {
                    $Note = "ESTENSIONE VISIBILITA' SOTTOFASCICOLO. ";
                }
                $Note .= $this->datiInvio['annotazioni'];
                $extraParm = array(
                    "NOTE" => $Note,
                    "NODO" => "ASF"
                );
            } else {
                $extraParm = array(
                    "NOTE" => $this->datiInvio['annotazioni'],
                );
            }

            /*
             * Setto presa in carico:
             */
            $arcite_rec['ITEDATACC'] = date('Ymd');
            $arcite_rec['ITESTATO'] = proIter::ITESTATO_INCARICO;
            if (!$this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false)) {
                Out::msgStop("Attenzione", 'Errore in aggiornamento Iter.');
                return false;
            }

            /*
             * Assegno.
             */
            if (count($this->datiInvio['destinatari']) > 0) {
                foreach ($this->datiInvio['destinatari'] as $proDest) {
                    $desGes = $proDest['DESGESADD'];
                    if ($proDest['DESGESADD']) {
                        if ($arcite_rec['ITEGES'] != 1) {
                            $desGes = 0;
                        }
                    }
                    $destinatario = array(
                        "DESCUF" => $proDest['ITEUFF'],
                        "DESCOD" => $proDest['DESCODADD'],
                        "DESGES" => $desGes,
                        "DESTERMINE" => $proDest['TERMINE'],
                        "ITEBASE" => 0
                    );
                    $iter = proIter::getInstance($this->proLib, $arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
                    $iter->insertIterNode($destinatario, $arcite_rec, $extraParm);
                }
            }
        }
        //$this->trasmettiPerConsegnaCartaceo();
    }

    /*
     *  Conteggio trasmissioni e letti: annullati non conteggiati.
     */

    public function ConteggiIter($Itepro, $itepar) {
        // Conteggio Trasmissioni:
        $retConteggi = array();
        $sql = "SELECT * FROM ARCITE WHERE ITEPRO = '$Itepro' AND ITEPAR = '$itepar' AND ITEANNULLATO = '0' ";
        $sql .= " AND (ITENODO = 'TRX' OR ITENODO = 'ASS') ";
        $Trasmissioni_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        // Conteggio trasmissioni lette:
        $retConteggi['NDESTITER'] = count($Trasmissioni_tab);
        $Nletti = 0;
        foreach ($Trasmissioni_tab as $Trasmissioni_rec) {
            if ($Trasmissioni_rec['ITEDLE']) {
                $Nletti++;
            }
        }
        $retConteggi['NDESLETTI'] = $Nletti;
        return $retConteggi;
    }

    public function GetTabSelezione() {
        $_POST[$this->nameForm . '_OpzioniVisualizzazione'] = "1";
        $arcite_tab = $this->proLib->getGenericTab($this->creaSql());
        if (count($arcite_tab) > 300) {
            Out::msgStop("Attenzione", "Selezione troppo ampia, trovati " . count($arcite_tab) . " risultati. Sono gestibili fino a 300 risultati. Restringere gli intervalli di date.");
            return false;
        }
        if (!$arcite_tab) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return false;
        }
        foreach ($arcite_tab as $key => $arcite_rec) {
            $arcite_tab[$key]['NUMERO'] = ((int) substr($arcite_rec['ITEPRO'], 4)) . '/' . substr($arcite_rec['ITEPRO'], 0, 4);
            $arcite_tab[$key]['ITEDAT'] = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
            $anaogg_rec = $this->proLib->GetAnaogg($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
            $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
            if ($arcite_tab[$key]['PRORISERVA'] == '1' || $arcite_tab[$key]['PROTSO']) {
                $arcite_tab[$key]['OGGETTO'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
                $arcite_tab[$key]['PROVENIENZA'] = "<p style=\"display:inline-block;background-color:lightgrey;\">RISERVATO</p>";
//                unset($arcite_tab[$key]);
//                continue;
            } else {
                // Le trasmissioni di rifiuto devono essere escluse:
                if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
                    unset($arcite_tab[$key]);
                    continue;
                }
                $arcite_tab[$key]['OGGETTO'] = $anaogg_rec['OGGOGG'];
                $arcite_tab[$key]['PROVENIENZA'] = $anapro_rec['PRONOM'];
                if ($arcite_rec['ITEPAR'] == 'C') {
                    $arcite_tab[$key]['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
                }
            }
            $arcite_tab[$key]['LEGAME'] = '';
            if ($anapro_rec['PROPRE'] > 0 && $anapro_rec['PROPARPRE'] != '') {
                $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
            } else {
                $Anno = substr($anapro_rec['PRONUM'], 0, 4);
                $Numero = substr($anapro_rec['PRONUM'], 4);
                if ($this->proLib->checkRiscontro($Anno, $Numero, $anapro_rec['PROPAR'])) {
                    $arcite_tab[$key]['LEGAME'] = '<div style="display:inline-block;" ><span style="display:inline-block" title="Legame con altro protocollo" class="ita-tooltip ui-icon ui-icon-folder-open"> </span></div>';
                }
            }
            $chiave = "@" . $key;
            $arcite_tab2[$chiave] = $arcite_tab[$key];
        }
        if (!$arcite_tab2) {
            Out::msgInfo("Attenzione", "Nessun risultato visibile secondo i parametri di ricerca");
            return false;
        }
        return $arcite_tab2;
    }

    public function openFascicolaProtocolli() {
        $arcite_tab2 = $this->GetTabSelezione();
        if (!$arcite_tab2) {
            return false;
        }
        foreach ($arcite_tab2 as $key => $arcite_rec) {
            switch ($arcite_rec['ITEPAR']) {
                case 'W':
                case 'N':
                case 'F':
                    unset($arcite_tab2[$key]);
                    break;
            }
        }

        $this->arrSelezionati = $arcite_tab2;
        $colNames = array(
            "",
            "Numero",
            "Data",
            "Oggetto",
            "Provenienza",
            "In Gestione",
            "Prot. Collegato"
        );
        $colModel = array(
            array("name" => 'ITEPAR', "width" => 20),
            array("name" => 'NUMERO', "width" => 65),
            array("name" => 'ITEDAT', "width" => 75),
            array("name" => 'OGGETTO', "width" => 320),
            array("name" => 'PROVENIENZA', "width" => 220),
            array("name" => 'ITEGES', "width" => 70, "formatter" => "'checkbox'", "formatoptions" => "{disabled:true}"),
            array("name" => 'LEGAME', "width" => 50)
        );
        $dim = array('width' => '890', 'height' => '400');
        include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
        $msgDetail = "<span style=\"font-size:1.4em;color:red;\"><b>I protocolli selezionati saranno fascicolati.</b></span>";
        proRic::proMultiselectGeneric(
                $arcite_tab2, $this->nameForm, 'DaFascicolare', 'Seleziona le Trasmissioni da Fascicolare', $colNames, $colModel, $msgDetail, $dim
        );
    }

    public function ApriSelezioneFascicolo($Titolario) {
        $model = 'proSeleFascicolo';
        itaLib::openForm($model);
        /* @var $proSeleFascicolo proSeleFascicolo */
        $proSeleFascicolo = itaModel::getInstance($model, $model);
        $proSeleFascicolo->setEvent('openform');
        $proSeleFascicolo->setReturnModel($this->nameForm);
        $proSeleFascicolo->setTitolario($Titolario);
        $proSeleFascicolo->setReturnEvent('returnAlberoFascicolo');
        $proSeleFascicolo->setReturnId($this->nameForm . '_AggiungiFascicolo');
        $proSeleFascicolo->setAbilitaCreazione(true);
        $proSeleFascicolo->parseEvent();
    }

    private function LeggiCaricamentoContabilitaHalley() {
        /*
         * HALLEY CONTABILITA Sempre Attivo: Senza presa in carico.
         */
        $anaent_55 = $this->proLib->GetAnaent('55');
        $this->CaricamentoContabilitaHalley = $anaent_55['ENTVAL'];
    }

    private function DettaglioTrasmissione($rowId, $AggHalley = false) {
        $model = 'proGestIter';
        $page = $_POST[$this->gridIter]['gridParam']['page'];
        $_POST = array();
        $_POST['fromportlet'] = true;
        $_POST['page'] = $page;
        $_POST['event'] = 'openform';
        $_POST['rowidIter'] = $rowId;
        if ($AggHalley) {
            $_POST['aggiornaFatturaHalley'] = '1';
        }
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        $_POST = $this->formData;
    }

    private function checkDelegheAttive() {
        $proLibDeleghe = new proLibDeleghe();
        $this->delegheAttive = $proLibDeleghe->getDelegheAttive($this->codiceDest, array('DELESCRIVANIA' => 1), proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);
        if (count($this->delegheAttive)) {
            Out::show($this->nameForm . '_GestAltraScrivania');
            Out::show($this->nameForm . '_GestScrivaniaGroup');
        } else {
            Out::hide($this->nameForm . '_GestAltraScrivania');
            Out::hide($this->nameForm . '_GestScrivaniaGroup');
            $this->disattivaScrivanieDelegate();
        }
    }

    private function disattivaScrivanieDelegate() {
        $this->visDelegheAttive = array();
        $this->visTutteDeleghe = false;
        Out::html($this->nameForm . '_divInfoVisScrivania', '');
        Out::hide($this->nameForm . '_divInfoVisScrivania');
    }

}
