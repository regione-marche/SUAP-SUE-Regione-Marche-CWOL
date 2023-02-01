<?php

/**
 *
 * Gestione Elenco registri giornalieri
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPRestClient
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    09.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';

function proConservazione() {
    $proConservazione = new proConservazione();
    $proConservazione->parseEvent();
    return;
}

class proConservazione extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibAllegati;
    public $proLibTabDag;
    public $proLibConservazione;
    public $nameForm = "proConservazione";
    public $divRis = "proConservazione_divRisultato";
    public $gridConservazioni = "proConservazione_gridConservazioni";
    public $gridConservazioniFascicoli = "proConservazione_gridConservazioniFascicoli";
    public $workDate;
    public $workYear;
    public $eqAudit;
    public $elencoConservazioni = array();
    public $elencoConservazioniFascicoli = array();
    public $tipoConservazione;
    public $keyFascicoloSelezionato;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibTabDag = new proLibTabDag();
            $this->proLibConservazione = new proLibConservazione();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->eqAudit = new eqAudit();
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($data));
            $this->elencoConservazioni = App::$utente->getKey($this->nameForm . '_elencoConservazioni');
            $this->tipoConservazione = App::$utente->getKey($this->nameForm . '_tipoConservazione');
            $this->elencoConservazioniFascicoli = App::$utente->getKey($this->nameForm . '_elencoConservazioniFascicoli');
            $this->keyFascicoloSelezionato = App::$utente->getKey($this->nameForm . '_keyFascicoloSelezionato');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_elencoConservazioni', $this->elencoConservazioni);
            App::$utente->setKey($this->nameForm . '_tipoConservazione', $this->tipoConservazione);
            App::$utente->setKey($this->nameForm . '_elencoConservazioniFascicoli', $this->elencoConservazioniFascicoli);
            App::$utente->setKey($this->nameForm . '_keyFascicoloSelezionato', $this->keyFascicoloSelezionato);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->openForm();
                $this->CaricaLegenda();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridConservazioni:
                        $rowid = $_POST['rowid'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        $model = 'proArri';
                        $_POST['tipoProt'] = $Anapro_rec['PROPAR'];
                        $_POST['event'] = 'openform';
                        $_POST['proGest_ANAPRO']['ROWID'] = $Anapro_rec['ROWID'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridConservazioni:
                    case $this->gridConservazioniFascicoli:
                        $rowid = $_POST['rowid'];
                        switch ($_POST['colName']) {
                            case 'DETTAGLIO':
                                itaLib::openForm('proDettConservazione');
                                /* @var $proDettConservazione proDettConservazione */
                                $proDettConservazioneObj = itaModel::getInstance('proDettConservazione');
                                $proDettConservazioneObj->setEvent('openDettaglio');
                                $proDettConservazioneObj->setIndiceRowid($rowid);
                                $proDettConservazioneObj->setReturnModel($this->nameForm);
                                $proDettConservazioneObj->setReturnEvent('returnProDettConservazione');
                                $proDettConservazioneObj->setReturnId('');
                                $proDettConservazioneObj->parseEvent();
                                break;
                            case 'DETDOC':

                                /*
                                 * 1 Decodifico Fascicolo
                                 */
                                $this->DecodificaFascicolo($rowid);


                                /*
                                 * 2 Mostro protocolli in quel fascicolo e il loro stato
                                 */
                                break;
                        }
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridConservazioni:
                        if ($this->elencoConservazioni) {
                            $this->CaricaGrigliaGenerica($this->gridConservazioni, $this->elencoConservazioni);
                        } else {
                            $this->CaricaElenco();
                        }
                        break;
                    case $this->gridConservazioniFascicoli:
                        if ($this->elencoConservazioniFascicoli) {
                            $this->CaricaGrigliaGenerica($this->gridConservazioniFascicoli, $this->elencoConservazioniFascicoli);
                        } else {
                            $this->CaricaElencoFascicoli();
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                $this->EsportaExcel();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->Nascondi();
                        $this->tipoConservazione = $_POST[$this->nameForm . '_RICERCA']['TIPOCONSERVAZIONE'];
                        Out::show($this->nameForm . '_divRisultato');
                        Out::show($this->nameForm . '_MenuFunzioni');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_divLegenda');
                        $this->MostraNascondiColonne();
                        if ($this->tipoConservazione == '1') {
                            Out::hide($this->nameForm . '_divGridProtocolli');
                            Out::show($this->nameForm . '_divGridFascicoli');
                            TableView::clearGrid($this->gridConservazioniFascicoli);
                            TableView::enableEvents($this->gridConservazioniFascicoli);
                            TableView::reload($this->gridConservazioniFascicoli);
                        } else {
                            Out::show($this->nameForm . '_divGridProtocolli');
                            Out::hide($this->nameForm . '_divGridFascicoli');
                            TableView::clearGrid($this->gridConservazioni);
                            TableView::enableEvents($this->gridConservazioni);
                            TableView::reload($this->gridConservazioni);
                        }
                        break;

                    case $this->nameForm . '_TornaElencoFasc':
                        $this->keyFascicoloSelezionato = '';
                        Out::hide($this->nameForm . '_divInfoFascicolo');
                        Out::hide($this->nameForm . '_divGridProtocolli');
                        Out::show($this->nameForm . '_divGridFascicoli');
                        TableView::clearGrid($this->gridConservazioniFascicoli);
                        TableView::enableEvents($this->gridConservazioniFascicoli);
                        TableView::reload($this->gridConservazioniFascicoli);
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        Out::clearFields($this->nameForm . '_divRicerca');
                        Out::clearFields($this->nameForm . '_divAppoggio');
                        Out::valore($this->nameForm . '_RICERCA[ANNO]', date('Y'));
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->Nascondi();
                        Out::show($this->nameForm . '_divRicerca');
                        Out::show($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_SvuotaRicerca');
                        $this->elencoConservazioni = array();
                        $this->elencoConservazioniFascicoli = array();
                        $this->keyFascicoloSelezionato = '';
                        break;

                    case $this->nameForm . '_MenuFunzioni':
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        if (!$rowid || $rowid == null || $rowid == 'null') {
                            Out::msgInfo("Attenzione", "Selezionare un elemento.");
                            break;
                        }
                        $arrBottoni = $this->proLibConservazione->getMenuFunzioniConservazione($this->nameForm, $rowid);
                        if (!$arrBottoni) {
                            Out::msgInfo("Attenzione", "Non ci sono funzioni disponibili.");
                            break;
                        }
                        Out::msgQuestion("Menu Funzioni", "Seleziona la funzione da utilizzare", $arrBottoni, 'auto', 'auto', 'true', false, true, true);
                        break;

                    case $this->nameForm . '_Conserva':
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');

//                        // TEST
//                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariazioni.class.php';
//                        $proLibVariazioni = new proLibVariazioni();
//                        $AnaproSave_rec = $proLibVariazioni->getLastAnaproSave($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], '20171116');
//                        $Variazioni = $proLibVariazioni->getVariazioni($AnaproSave_rec);
//                        Out::msginfo('variazioni', print_r($Variazioni, true));
//                        return;


                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Protocollo inesistente");
                            return;
                        }
                        /* Controllo se protocllo già conservato */
                        if ($this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                            Out::msgStop("Attenzione", "Il protocollo risulta già conservato.");
                            return;
                        }
                        /* Controllo Protocollo Conservabile */
                        if (!$this->proLibConservazione->CheckProtocolloConservabile($Anapro_rec)) {
                            Out::msgStop("Attenzione", "Il protocollo non è conservabile." . $this->proLibConservazione->getErrMessage());
                            return;
                        }
                        $this->ConservaProt();
                        $this->elencoConservazioni = array();
                        TableView::reload($this->gridConservazioni);
                        break;

                    case $this->nameForm . '_AggConserva':
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Protocollo inesistente");
                            return;
                        }
                        /* Controllo se protocllo già conservato */
                        if (!$this->proLibConservazione->CheckProtocolloDaAggiornare($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                            Out::msgStop("Attenzione", "Il protocollo non risulta da aggiornare.");
                            return;
                        }
                        /* Controllo Protocollo Conservabile */
                        if (!$this->proLibConservazione->CheckProtocolloConservabile($Anapro_rec)) {
                            Out::msgStop("Attenzione", "Il protocollo non è conservabile." . $this->proLibConservazione->getErrMessage());
                            return;
                        }
                        $this->AggiornaConservaProt();
                        $this->elencoConservazioni = array();
                        TableView::reload($this->gridConservazioni);
                        break;

                    case $this->nameForm . '_ScaricaLog':
                        $FilePathLog = $this->proLib->GetFilePathLogConservazione();
                        if ($FilePathLog) {
                            $basename = pathinfo($FilePathLog, PATHINFO_BASENAME);
                            Out::openDocument(utiDownload::getUrl($basename, $FilePathLog));
                        } else {
                            Out::msgInfo('Attenzione', 'File di Log non definito nei parametri.');
                        }
                        break;

                    case $this->nameForm . '_ControllaRDV':
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Protocollo inesistente");
                            return;
                        }
                        /* Controllo se protocllo già conservato */
                        $ProConser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                        if (!$ProConser_rec) {
                            Out::msgStop("Attenzione", "Il protocollo non risulta essere versato in conservazione.");
                            return;
                        }
                        if (!$ProConser_rec['UUIDSIP']) {
                            Out::msgStop("Attenzione", "Il protocollo non risulta essere versato in conservazione.");
                            return;
                        }
                        /*
                         * Controllo se RDV già verificato e salvato.
                         */
                        if ($ProConser_rec['DOCRDV']) {
                            Out::msgStop("Attenzione", "Controllo RDV già effettuato.");
                            return;
                        }
                        $ObjManager = proConservazioneManagerFactory::getManager();
                        if (!$ObjManager) {
                            Out::msgStop('Attenzione', 'Errore in istanza Manager.');
                            return;
                        }
                        $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);
                        /*
                         * Setto Chiavi Anapro
                         */
                        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
                        $ObjManager->setAnapro_rec($Anapro_rec);
                        $ObjManager->setUnitaDocumentaria($UnitaDoc);
                        //
                        if (!$ObjManager->parseXmlRDV($ProConser_rec['UUIDSIP'], $ProConser_rec['PENDINGUUID'])) {
                            Out::msgStop('Attenzione', 'Errore controllo RDV. ' . $ObjManager->getErrMessage());
                            return;
                        }
                        Out::msgInfo("Controllo RDV", "Verifica della Ricevuta di Versamento effettuata correttamente.");
                        $this->elencoConservazioni = array();
                        TableView::reload($this->gridConservazioni);
                        break;

                    case $this->nameForm . '_SbloccaConservazione':
                        $this->ChiediMotivazione('Sblocco');
                        break;


                    case $this->nameForm . '_ConfermaInputSblocco':
                        if (!$_POST[$this->nameForm . '_motivazione']) {
                            $this->ChiediMotivazione('Sblocco');
                            break;
                        }
                        $MotivoSblocco = $_POST[$this->nameForm . '_motivazione'];
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Protocollo inesistente");
                            return;
                        }
                        /* Controllo se protocllo già conservato */
                        $ProConser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                        $ProConserBloccato_rec = $this->proLibConservazione->CheckProtocolloBloccato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                        if (!$ProConser_rec && !$ProConserBloccato_rec) {
                            Out::msgStop("Attenzione", "Il protocollo non risulta essere versato in conservazione.");
                            return;
                        }
                        /*
                         * Controllo se è possibile lo sblocco:
                         *  Se è versamento negativo
                         *  se è conservazione negativa
                         *  se è bloccato
                         */
                        if ($ProConserBloccato_rec || $ProConser_rec['ESITOVERSAMENTO'] == proLibConservazione::ESITO_NEGATIVO || $ProConser_rec['ESITOCONSERVAZIONE'] == proLibConservazione::CONSER_ESITO_NEGATIVO) {
                            /*
                             * Istanzio Oggetto Manager
                             */
                            $ObjManager = proConservazioneManagerFactory::getManager();
                            if (!$ObjManager) {
                                Out::msgStop('Attenzione', 'Errore in istanza Manager.');
                                return;
                            }
                            $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);
                            /*
                             * Setto Chiavi Anapro
                             */
                            $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
                            $ObjManager->setAnapro_rec($Anapro_rec);
                            $ObjManager->setUnitaDocumentaria($UnitaDoc);
                            /*
                             * 1. Storicizzo il ProConser
                             */
                            if (!$ObjManager->StoricizzaProconser($MotivoSblocco)) {
                                Out::msgStop('Attenzione', 'Errore in Sblocco Protocollo. ' . $ObjManager->getErrMessage());
                                return;
                            }
                            /*
                             * Verifico e Storicizzo PROUPDATECONSER:
                             */
                            $ProUpdateConser_rec = $this->proLibConservazione->GetProUpdateConser($Anapro_rec['PRONUM'], 'codice', $Anapro_rec['PROPAR']);
                            if ($ProUpdateConser_rec) {
                                $ObjManager->setUnitaDocumentaria($ProUpdateConser_rec['UPDATETIPO']);
                                if (!$ObjManager->StoricizzaProUpdateConser()) {
                                    Out::msgStop('Attenzione', 'Errore Sblocco Update. ' . $ObjManager->getErrMessage());
                                    return;
                                }
                            }
                            Out::msgInfo("Sblocco", "Protocollo sbloccato correttamente.");
                            $this->elencoConservazioni = array();
                            TableView::reload($this->gridConservazioni);
                        } else {
                            Out::msgStop("Attenzione", "Nessun esito da sbloccare per il protocollo selezionato.");
                            return;
                        }
                        break;


                    case $this->nameForm . '_BloccaConservazioneProt':
                        $this->ChiediMotivazione('Blocco');
                        break;

                    case $this->nameForm . '_ConfermaInputBlocco':
                        if (!$_POST[$this->nameForm . '_motivazione']) {
                            $this->ChiediMotivazione('Blocco');
                            break;
                        }
                        $motivazione = $_POST[$this->nameForm . '_motivazione'];
                        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Protocollo inesistente");
                            return;
                        }
                        /*
                         * Istanzio Manager
                         */
                        $ObjManager = proConservazioneManagerFactory::getManager();
                        if (!$ObjManager) {
                            Out::msgStop('Attenzione', 'Errore in istanza Manager.');
                            return;
                        }
                        $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);
                        /*
                         * Setto Chiavi Anapro
                         */
                        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
                        $ObjManager->setAnapro_rec($Anapro_rec);
                        $ObjManager->setUnitaDocumentaria($UnitaDoc);
                        /*
                         * Cerco Proconser esistente
                         */
                        $ProConser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                        if ($ProConser_rec) {
                            // Se è già conservato con esito positivo non posso bloccarlo..
                            if ($ProConser_rec['ESITOVERSAMENTO'] == proLibConservazione::ESITO_POSTITIVO && $ProConser_rec['ESITOCONSERVAZIONE'] == proLibConservazione::CONSER_ESITO_POSTITIVO) {
                                Out::msgStop("Attenzione", "Il protocollo risulta già conservato con esito positivo.");
                                return;
                            }
                            /*
                             * 1. Storicizzo il ProConser
                             */
                            if (!$ObjManager->StoricizzaProconser()) {
                                Out::msgStop('Attenzione', 'Errore in Sblocco Protocollo. ' . $ObjManager->getErrMessage());
                                return;
                            }
                        }
                        /*
                         * Inserisco nuovo proconser
                         */
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Protocollo Bloccato e non Conservabile.'));
                        $ArrDatiConser = array();
                        $ArrDatiConser[proConservazioneManager::CHIAVE_ESITO_DATAVERSAMENTO] = date('Ymd');
                        $ArrDatiConser[proConservazioneManager::CHIAVE_ESITO_ESITO] = proLibConservazione::ESITO_BLOCCATO;
                        $ArrDatiConser[proConservazioneManager::CHIAVE_NOTECONSERV] = 'Motivo Blocco: ' . $motivazione;
                        if (!$ObjManager->SalvaMetadatiConservazione($ArrDatiConser)) {
                            Out::msgStop('Attenzione', 'Errore in Salvataggio Dati Protocollo. ' . $ObjManager->getErrMessage());
                            return;
                        }
                        $this->elencoConservazioni = array();
                        TableView::reload($this->gridConservazioni);
                        break;

                    case $this->nameForm . '_RICERCA[FASCICOLO]_butt':
                        $where = '';
                        if ($_POST[$this->nameForm . '_RICERCA']['ANNO']) {
                            $where = " WHERE ORGANN = " . $_POST[$this->nameForm . '_RICERCA']['ANNO'];
                        }
                        proRic::proRicOrgFas($this->nameForm, $where);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RICERCA[TIPOCONSERVAZIONE]':
                        if ($_POST[$this->nameForm . '_RICERCA']['TIPOCONSERVAZIONE'] == '1') {
                            Out::hide($this->nameForm . '_RICERCA[TIPO]_field');
                        } else {
                            Out::show($this->nameForm . '_RICERCA[TIPO]_field');
                        }
                        break;
                    case $this->nameForm . '_RICERCA[FASCICOLO]':
                        $Anaorg_rec = $this->proLib->GetAnaorg($_POST[$this->nameForm . '_RICERCA']['FASCICOLO'], 'orgkey');
                        if ($Anaorg_rec) {
                            Out::valore($this->nameForm . '_RICERCA[PROFASKEY]', $Anaorg_rec['ORGKEY']);
                            Out::valore($this->nameForm . '_DESC_FASCICOLO', $Anaorg_rec['ORGDES']);
                            Out::valore($this->nameForm . '_SEG_FASCICOLO', $Anaorg_rec['ORGSEG']);
                        } else {
                            if ($_POST[$this->nameForm . '_RICERCA']['FASCICOLO']) {
                                Out::msgInfo("Attenzione", "Codice Fascicolo errato.");
                            }
                            Out::valore($this->nameForm . '_RICERCA[FASCICOLO]', '');
                            Out::valore($this->nameForm . '_RICERCA[PROFASKEY]', '');
                            Out::valore($this->nameForm . '_SEG_FASCICOLO', '');
                            Out::valore($this->nameForm . '_DESC_FASCICOLO', '');
                        }

                        break;
                    case $this->nameForm . '_RICERCA[TIPO]':
                        if ($_POST[$this->nameForm . '_RICERCA']['TIPO'] == 'I') {
                            Out::hide($this->nameForm . '_RICERCA[DANUMERO]_field');
                            Out::hide($this->nameForm . '_RICERCA[ANUMERO]_field');
                        } else {
                            Out::show($this->nameForm . '_RICERCA[DANUMERO]_field');
                            Out::show($this->nameForm . '_RICERCA[ANUMERO]_field');
                        }
                        break;
                }
                break;

            case 'returnProDettConservazione':
                break;

            case 'returnorgfas':
                if ($_POST['retKey']) {
                    $Anaorg_rec = $this->proLib->GetAnaorg($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_RICERCA[FASCICOLO]', $Anaorg_rec['ORGKEY']);
                    Out::valore($this->nameForm . '_RICERCA[PROFASKEY]', $Anaorg_rec['ORGKEY']);
                    Out::valore($this->nameForm . '_SEG_FASCICOLO', $Anaorg_rec['ORGSEG']);
                    Out::valore($this->nameForm . '_DESC_FASCICOLO', $Anaorg_rec['ORGDES']);
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_elencoConservazioni');
        App::$utente->removeKey($this->nameForm . '_tipoConservazione');
        App::$utente->removeKey($this->nameForm . '_elencoConservazioniFascicoli');
        App::$utente->removeKey($this->nameForm . '_keyFascicoloSelezionato');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
        Out::show('menuapp');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_divInformazioni');
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');

        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_MenuFunzioni');
        Out::hide($this->nameForm . '_SvuotaRicerca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_divLegenda');
        Out::hide($this->nameForm . '_divInfoFascicolo');
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_RICERCA[TIPO]', 1, "", "1", "Tutti");
        Out::select($this->nameForm . '_RICERCA[TIPO]', 1, "A", "0", "Arrivi");
        Out::select($this->nameForm . '_RICERCA[TIPO]', 1, "P", "0", "Partenze");
        Out::select($this->nameForm . '_RICERCA[TIPO]', 1, "C", "0", "Doc. Formali");
        Out::select($this->nameForm . '_RICERCA[TIPO]', 1, "I", "0", "Doc. Pratiche Amministrative");

        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "", "1", "Tutti");
        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "1", "0", "Versati");
        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "2", "0", "Non Versati");
        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "3", "0", "Con Esito Negativo");
        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "4", "0", "Non Conservabili");
        Out::select($this->nameForm . '_RICERCA[STATOVERSAMENTO]', 1, "5", "0", "Bloccati");

        Out::select($this->nameForm . '_RICERCA[STATOCONSERVAZIONE]', 1, "", "1", "Tutti");
        Out::select($this->nameForm . '_RICERCA[STATOCONSERVAZIONE]', 1, "1", "0", "Conservati");
        Out::select($this->nameForm . '_RICERCA[STATOCONSERVAZIONE]', 1, "2", "0", "Non Conservati");
        Out::select($this->nameForm . '_RICERCA[STATOCONSERVAZIONE]', 1, "3", "0", "Con Esito Negativo");

        Out::select($this->nameForm . '_RICERCA[TIPOCONSERVAZIONE]', 1, "", "1", "PROTOCOLLI");
        Out::select($this->nameForm . '_RICERCA[TIPOCONSERVAZIONE]', 1, "1", "0", "FASCICOLI ELETTRONICI");
    }

    public function openForm() {
        $this->Nascondi();
        Out::hide($this->nameForm . '_divRicTipoConservazione');
//        Out::show($this->nameForm . '_divInformazioni');
        Out::show($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_SvuotaRicerca');
        Out::valore($this->nameForm . '_RICERCA[ANNO]', date('Y'));
    }

    public function CaricaInformazioni() {
        // Qui carica divInformazioni con
        // riepilogo delle conservazioni.

        /*
         * Conservazioni Positive
         * 
         * Conservazioni con Errori
         * 
         * Conteggio da Conservare
         * 
         * Anomalie/Bloccate N. Tentativi
         * 
         */
    }

    public function creaSql() {
        /*
         * Estrazione di protocollo estratto
         * Tramite PROCONSER
         */
        $ParamRicerca = $_POST[$this->nameForm . '_RICERCA'];
        $anaent_53 = $this->proLib->GetAnaent('53');
        $anaent_59 = $this->proLib->GetAnaent('59');

        if ($this->tipoConservazione == '1') {
            $sql = $this->proLibConservazione->getSqlBaseConservazione();
            if (!$this->keyFascicoloSelezionato) {
                $sql.=" WHERE ANAPRO.PROPAR = 'F' AND ANAPRO.PROCODTIPODOC = 'PRAM' ";
            } else {
                /*
                 * Se selezionato un fascicolo deve estrarre solo gli elementi
                 */
                $sql.=" WHERE ANAPRO.PROPAR = 'I' AND ANAPRO.PROFASKEY = '$this->keyFascicoloSelezionato' "; // "I" si potrebbe rimuovere
            }
        } else {
            $sql = $this->proLibConservazione->getSqlProtocolliConservazione(true, false);
        }
        switch ($ParamRicerca['STATOVERSAMENTO']) {
            case "1":
                $sql.=" AND (PROCONSER.ESITOVERSAMENTO =  '" . proLibConservazione::ESITO_POSTITIVO . "' OR  PROCONSER.ESITOVERSAMENTO =  '" . proLibConservazione::ESITO_WARNING . "') "; // Warning
                $sql.=" AND PROCONSER.PRONUM IS NOT NULL ";
                break;

            case "2":
//                $sql.=" AND PROCONSER.ROWID IS NULL "; // Che quindi non ha un legame di conservazione.
                $sql.=" AND PROCONSER.PRONUM IS NULL "; // Che quindi non ha un legame di conservazione.
                break;

            case "3":
                $sql.=" AND PROCONSER.ESITOVERSAMENTO =  '" . proLibConservazione::ESITO_NEGATIVO . "' ";
                $sql.=" AND PROCONSER.PRONUM IS NOT NULL ";
                break;
            case '4':
                $sql.=" AND PROCONSER.PRONUM IS NULL "; // aggiunta una opzione in elaborazione record...
                // Valutare se in futuro potranno esserci prot conservati, variati ma non conservabili.
                break;
            case '5':
                $sql.=" AND PROCONSER.ESITOVERSAMENTO =  '" . proLibConservazione::ESITO_BLOCCATO . "' ";
                break;
            default:
                // $sql.=" WHERE 1 ";
                break;
        }


        /*
         * Stato Conservazione
         */
        switch ($ParamRicerca['STATOCONSERVAZIONE']) {
            case "1":
                $sql.=" AND PROCONSER.ESITOCONSERVAZIONE =  '" . proLibConservazione::CONSER_ESITO_POSTITIVO . "' ";
                break;

            case "2":
                $sql.=" AND (PROCONSER.PRONUM IS NULL OR PROCONSER.ESITOCONSERVAZIONE = '') ";
                break;

            case "3":
                $sql.=" AND PROCONSER.ESITOCONSERVAZIONE =  '" . proLibConservazione::CONSER_ESITO_NEGATIVO . "' ";
                break;
            default:
                break;
        }


        /* Where */
        /*
         *  DATA INIZIO LIMITE PARAMETRI:
         */
        if ($anaent_53['ENTDE2']) {
            $sql.=" AND PRODAR >= " . $anaent_53['ENTDE2'];
        }
        /*
         * Tipo Protocollo.
         */
        if ($ParamRicerca['TIPO'] != '') {
            $sql.=" AND ANAPRO.PROPAR = '" . $ParamRicerca['TIPO'] . "' ";
        }
        if ($ParamRicerca['TIPO'] == 'I') {
            $sql.=" AND ANAPRO.PROCODTIPODOC = '" . $anaent_59['ENTDE1'] . "' ";
        }


        /*
         * Anno Protocollo
         */
        if ($ParamRicerca['ANNO']) {
            $sql.=" AND ANAPRO.PRONUM LIKE '" . $ParamRicerca['ANNO'] . "%' ";
        }
        /*
         * Da/A Data Protocollo
         */
        if ($ParamRicerca['DADATA']) {
            $sql.=" AND PRODAR >= " . $ParamRicerca['DADATA'];
        }
        if ($ParamRicerca['ADATA']) {
            $sql.=" AND PRODAR <= " . $ParamRicerca['ADATA'];
        }
        /*
         * Ricerca Da Numero a Numero
         */
        if ($ParamRicerca['DANUMERO']) {
            $Anno = $ParamRicerca['ANNO'];
            if ($Anno) {
                $DaNumero = $Anno . str_pad($ParamRicerca['DANUMERO'], 6, '0', STR_PAD_LEFT);
                $sql.=" AND ANAPRO.PRONUM >= " . $DaNumero;
            }
        }
        if ($ParamRicerca['ANUMERO']) {
            $Anno = $ParamRicerca['ANNO'];
            if ($Anno) {
                $ANumero = $Anno . str_pad($ParamRicerca['ANUMERO'], 6, '0', STR_PAD_LEFT);
                $sql.=" AND ANAPRO.PRONUM <= " . $ANumero;
            }
        }
        if ($ParamRicerca['PROFASKEY']) {
            $sql.=" AND ANAPRO.PROFASKEY = '" . $ParamRicerca['PROFASKEY'] . "' ";
        }

        /*
         * Precisazione:
         * Come verificare se c'è uno storico conservato?s
         *  
         */
//        Out::msginfo('sql', $sql);
        return $sql;
    }

    public function CaricaElenco() {
        $sql = $this->creaSql();
        $appoggio = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $this->CaricaConteggiLegenda($appoggio);
        //Out::msgInfo('tab', print_r($appoggio, true));
        $this->ApplicaFiltriAggiuntivi($appoggio);
        $this->elencoConservazioni = $appoggio;
        $this->CaricaGrigliaGenerica($this->gridConservazioni, $appoggio);
        return;
    }

    public function CaricaElencoFascicoli() {
        $sql = $this->creaSql();
        $appoggio = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $this->CaricaConteggiLegenda($appoggio);
        $this->ApplicaFiltriAggiuntivi($appoggio);
        $this->elencoConservazioniFascicoli = $appoggio;
        $this->CaricaGrigliaGenerica($this->gridConservazioniFascicoli, $appoggio);
        return;
    }

    public function ApplicaFiltriAggiuntivi(&$appoggio) {
        //Filtro Aggiuntivo Protocollo non conservabile.
        $ParamRicerca = $_POST[$this->nameForm . '_RICERCA'];
        if ($ParamRicerca['STATOVERSAMENTO'] == '4') {
            foreach ($appoggio as $key => $appoggio_rec) {
                if ($appoggio_rec['ESITOVERSAMENTO'] != proLibConservazione::CTR_NON_CONSERVABILE) {
                    unset($appoggio[$key]);
                }
            }
        }
        //Filtri per I
    }

    function CaricaGrigliaGenerica($griglia, $appoggio) {
        if (is_null($appoggio)) {
            $appoggio = array();
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );

        $ita_grid01->setPageNum($_POST['page']);
        $ita_grid01->setPageRows($_POST['rows']);
        $order = 'desc';
        if ($_POST['sord']) {
            $order = $_POST['sord'];
        }

        $ordinamento = $_POST['sidx'];
        switch ($ordinamento) {
            case 'ANNO':
            case 'CODICE':
            case 'STATOVERSAMENTO':
                $ordinamento = 'PRONUM ' . $order . ', PROPAR';
                break;
            default:
                break;
        }
        if (!$ordinamento) {
            $ordinamento = 'PRONUM ' . $order . ', PROPAR';
        }

        $ita_grid01->setSortIndex($ordinamento);
        $ita_grid01->setSortOrder($order);

        TableView::clearGrid($griglia);
        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
        $ita_grid01->getDataPageFromArray('json', $result_tab);
        TableView::enableEvents($griglia);
        return;
    }

    function elaboraRecords($result_tab) {
        $anaent_59 = $this->proLib->GetAnaent('59');

        foreach ($result_tab as $key => $result_rec) {
            $result_tab[$key]['CODICE'] = substr($result_rec['PRONUM'], 4, 6);
            $result_tab[$key]['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $ArrRetStatoVers = $this->getStatoVersamento($result_rec);
            $result_tab[$key]['STATOVERSAMENTO'] = $ArrRetStatoVers['ICONASTATO'];
            $result_tab[$key]['DESCRSTATOVERSAMENTO'] = $ArrRetStatoVers['DESCSTATO'];
            $result_tab[$key]['STATOCONSERVAZIONE'] = $this->getStatoConservazione($result_rec);
            // Controllo Prot Annullato:
            $ini_tag = $fin_tag = '';
            if ($result_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
                $fin_tag = "</p>";
            }
            $result_tab[$key]['CODICE'] = $ini_tag . $result_tab[$key]['CODICE'] . $fin_tag;
            $result_tab[$key]['ANNO'] = $ini_tag . $result_tab[$key]['ANNO'] . $fin_tag;
            $result_tab[$key]['TIPO'] = $result_tab[$key]['PROPAR'];
            $result_tab[$key]['PROPAR'] = $ini_tag . $result_tab[$key]['PROPAR'] . $fin_tag;
            $result_tab[$key]['OGGETTO'] = $result_tab[$key]['OGGOGG'];
            $result_tab[$key]['OGGOGG'] = $ini_tag . $result_tab[$key]['OGGOGG'] . $fin_tag;
            // Dettaglio Fascicolo
            if ($result_rec['PROPAR'] == 'F') {
                $Anaorg_rec = $this->proLib->getAnaorg($result_rec['PROFASKEY'], 'orgkey');
                $result_tab[$key]['CODICE'] = $ini_tag . $Anaorg_rec['ORGSEG'] . $fin_tag;
                $result_tab[$key]['DETDOC'] = "<span style=\"margin-left:10px; display:inline-block;\" class=\"ita-icon ita-icon-open-folder-24x24\"></span>" . "<span  class=\"ita-icon ita-icon-cerca-24x24\" style = \"margin-left:-12px; display:inline-block;\"></span>";
            }
            // Dettaglio Documento Pratica Amministrativa.
            if ($result_rec['PROPAR'] == 'I' && $result_rec['PROCODTIPODOC'] == $anaent_59['ENTDE1']) {
//                $Anaorg_rec = $this->proLib->getAnaorg($result_rec['PROFASKEY'], 'orgkey');
//                $Segnatura = $Anaorg_rec['ORGSEG'];
//                $Tabdag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $result_rec['ROWID'], 'MITTENTE_SEGNATURAPROTOCOLLO');
//                $Prot = $Tabdag_rec['TDAGVAL'] ? ' - Prot: ' . $Tabdag_rec['TDAGVAL'] : '';
//                $Descr = 'Chiave Fascicolo: ' . $result_rec['PROFASKEY'] . '' . $Prot;
//                $result_tab[$key]['PROFASKEY'] = "<div class=\"ita-html\"><span class=\" ita-tooltip\"  title =\"" . $Descr . "\">$Segnatura</span></div>";
                /* Dati Pratica: 
                 * 1 Dati Protocollo:
                 */
                $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $result_rec['ROWID'], 'PROTOCOLLO', '', false, '');
                $Protocollo = $TabDag_rec['TDAGVAL'];
                $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $result_rec['ROWID'], 'TIPOPROTOCOLLO', '', false, '');
                $TipoProtocollo = $TabDag_rec['TDAGVAL'];
                $result_tab[$key]['CODICE'] = $ini_tag . $Protocollo . '/' . $TipoProtocollo . $fin_tag;
                /* 2 Dati Pratica */
                $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $result_rec['ROWID'], 'IDENTIFICATIVOPROCEDIMENTO', '', false, '');
                $IdProcedimento = $TabDag_rec['TDAGVAL'];
                $AnnoPratica = substr($IdProcedimento, 0, 4);
                $Pratica = substr($IdProcedimento, 4, 6);
                $result_tab[$key]['PRATICA'] = $ini_tag . $Pratica . $fin_tag;
                $result_tab[$key]['ANNO'] = $ini_tag . $AnnoPratica . $fin_tag;
            }

            // Dettaglio:
            $result_tab[$key]['DETTAGLIO'] = "<span style=\"margin-left:10px;\" class=\"ita-icon ita-icon-cerca-24x24\"></span>";
        }
        return $result_tab;
    }

    public function getStatoVersamento($result_rec) {
        $styleDiv = "border:1px solid black; display:inline-block; width:15px; height:15px;  -moz-border-radius: 22px; border-radius: 22px;box-shadow:0px 0px 3px #CCCCCC; ";
        $colore = 'background-color:green;';
        $Descr = 'Nessuna Anomalia';
        $spanBloccato = "";
        $ArrRet = array();

        if (!$result_rec['ROWID_ANAPRO']) {
            $colore = 'background-color:' . proLibConservazione::$ElencoColoriCtrEsito[proLibConservazione::CTR_NON_CONSERVATO] . ';';
            $Descr = proLibConservazione::$ElencoDescrCtrEsito[proLibConservazione::CTR_NON_CONSERVATO];
            $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \"></div></div>";
        } else {
            if ($result_rec['DATAVARIAZIONE']) {
                $colore = 'background-color:' . proLibConservazione::$ElencoColoriCtrEsito[proLibConservazione::CTR_VARIATO] . ';';
                $DescData = ' IL: ' . date('d/m/Y', strtotime($result_rec['DATAVARIAZIONE']));
                $Descr = proLibConservazione::$ElencoDescrCtrEsito[proLibConservazione::CTR_VARIATO] . $DescData;
            } else {
                $colore = 'background-color:' . proLibConservazione::$ElencoColoriCtrEsito[$result_rec['ESITOVERSAMENTO']] . ';';
                $Descr = proLibConservazione::$ElencoDescrCtrEsito[$result_rec['ESITOVERSAMENTO']];
            }
            if ($result_rec['NOTECONSER']) {
                $Descr.=':<br>' . $result_rec['NOTECONSER'];
            }
            if ($result_rec['ESITOVERSAMENTO'] == proLibConservazione::ESITO_BLOCCATO) {
                $spanBloccato = "<span style=\"margin-left:20px; display:inline-block;\" class=\"ita-icon ita-icon-divieto-16x16\"></span>";
            }
            $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \">$spanBloccato</div></div>";
        }
        /*
         * Controllo Prot. Non conservabile
         */
        if ($result_rec['ESITOVERSAMENTO'] == proLibConservazione::CTR_NON_CONSERVABILE) {
            $colore = 'background-color:' . proLibConservazione::$ElencoColoriCtrEsito[proLibConservazione::CTR_NON_CONSERVABILE] . ';';
            $Descr = proLibConservazione::$ElencoDescrCtrEsito[proLibConservazione::CTR_NON_CONSERVABILE] . ':<br> ' . $result_rec['DESC_NONCONSERVABILE'];
            $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \"></div></div>";
        }
//        /*
//         * Controllo Protococllo Bloccato:
//         */
//        if ($result_rec['ESITOVERSAMENTO'] == proLibConservazione::ESITO_BLOCCATO) {
//
//            $colore = 'background-color:' . proLibConservazione::$ElencoColoriCtrEsito[$result_rec['ESITOVERSAMENTO']] . ';';
//            $Descr = proLibConservazione::$ElencoDescrCtrEsito[$result_rec['ESITOVERSAMENTO']];
//            if ($result_rec['NOTECONSER']) {
//                $Descr.=':<br>' . $result_rec['NOTECONSER'];
//            }
//            $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \"></div></div>";
//        }
        $ArrRet['ICONASTATO'] = $Stato;
        $ArrRet['DESCSTATO'] = $Descr;
        return $ArrRet;
    }

    public function getStatoConservazione($result_rec) {
        //CONSER_ESITO_POSTITIVO
        $styleDiv = "border:1px solid black; display:inline-block; width:15px; height:15px;  -moz-border-radius: 22px; border-radius: 22px;box-shadow:0px 0px 3px #CCCCCC; ";

        $colore = 'background-color:' . proLibConservazione::$ElencoColoriConserEsito[$result_rec['ESITOCONSERVAZIONE']] . ';';
        $Descr = proLibConservazione::$ElencoDescrConserEsito[$result_rec['ESITOCONSERVAZIONE']] . ':<br> ' . $result_rec['MESSAGGIOCONSERVAZIONE'];

        $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \"></div></div>";
        return $Stato;
    }

    public function ConservaProt() {
        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        if (!$Anapro_rec) {
            Out::msgStop("Attenzione", "Protocollo inesistente" . $rowid);
            return;
        }
        /*
         * Istanzio il Manager
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            Out::msgStop('Attenzione', 'Errore in istanza Manager.');
            return;
        }
        /*
         * Lettura unità documentaria
         */
        $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);

        /*
         * Setto Chiavi Anapro
         */
        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
        $ObjManager->setAnapro_rec($Anapro_rec);
        $ObjManager->setUnitaDocumentaria($UnitaDoc);
        /*
         *  Lancio la conservazione
         */
        if (!$ObjManager->conservaAnapro()) {
            Out::msgStop("Attenzione", $ObjManager->getErrMessage());
        } else {
            Out::msgInfo('Versamento in Conservazione', 'Esito Versamento:' . $ObjManager->getRetEsito());
        }

        /*
         * Chiamo funzione di conservaione
         */
    }

    public function AggiornaConservaProt() {
        $rowid = $_POST[$this->gridConservazioni]['gridParam']['selarrrow'];
        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        if (!$Anapro_rec) {
            Out::msgStop("Attenzione", "Protocollo inesistente" . $rowid);
            return;
        }
        /*
         * Istanzio il Manager
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            Out::msgStop('Attenzione', 'Errore in istanza Manager.');
            return;
        }
        /*
         * Setto Chiavi Anapro
         */
        $Proconser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if (!$Proconser_rec) {
            Out::msgStop('Attenzione', 'Il protocollo non risulta essere conservato.');
            return;
        }
        $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);
        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
        $ObjManager->setUnitaDocumentaria($UnitaDoc);
        $ObjManager->setDataVersamento($Proconser_rec['DATAVERSAMENTO']);
        $ObjManager->setOraVersamento($Proconser_rec['ORAVERSAMENTO']);
        /*
         * Estrazione Base dati Conservata
         */
        $baseDatiConservata = $ObjManager->getBaseDati();
        if (!$baseDatiConservata) {
            Out::msginfo('BaseDati Conservata', print_r($ObjManager->getErrMessage(), true));
        } else {
            //Out::msginfo('BaseDati Conservata', print_r($baseDatiConservata, true));
        }
        /*
         * Estrazione base dati attuale
         */
        $ObjManager->setDataVersamento(null);
        $ObjManager->setOraVersamento(null);
        $baseDatiAttuale = $ObjManager->getBaseDati();
        if (!$baseDatiAttuale) {
            Out::msginfo('Errore BaseDati Attuale', print_r($ObjManager->getErrMessage(), true));
            return false;
        } else {
            //Out::msginfo('BaseDati Attuale', print_r($baseDatiAttuale, true));
        }

        /*
         *  Lancio la conservazione
         */
        if (!$ObjManager->conservaAnapro()) {
            Out::msgStop("Attenzione", $ObjManager->getErrMessage());
            return false;
        } else {
            Out::msgInfo('Conservazione', 'Esito Conservazione:' . $ObjManager->getRetEsito());
        }


        return true;
    }

    public function CaricaLegenda($ConteggiVers = array(), $ConteggiCons = array()) {
        $styleDiv = "border:1px solid black; display:inline-block; width:12px; height:12px;  -moz-border-radius: 20px; border-radius: 20px; ";
        $Descrizione = " <div style=\"display:inline-block;\"> Nessuna Anomalia</div>";
        $html = '<b> - Esiti Versamento:</b>';
        foreach (proLibConservazione::$ElencoColoriCtrEsito as $key => $ColoreStato) {
            $colore = 'background-color:' . $ColoreStato . ';';
            if ($ConteggiVers[$key]) {
                $Legenda = $ConteggiVers[$key] . ': ';
            } else {
                $Legenda = '0: ';
            }
            $Descrizione = " <div style=\"display:inline-block; width:85%; padding:2px;\">" . $Legenda . ' ' . proLibConservazione::$ElencoDescrCtrEsito[$key] . "</div>";
            $html.= "<div><div style=\"display:inline-block; margin-left:2px; $styleDiv $colore \"></div>$Descrizione</div>";
        }
        $html.= '<br><b> - Esiti Conservazione:</b>';
        foreach (proLibConservazione::$ElencoColoriConserEsito as $key => $ColoreStato) {
            $colore = 'background-color:' . $ColoreStato . ';';
            if ($ConteggiCons[$key]) {
                $Legenda = $ConteggiCons[$key] . ': ';
            } else {
                $Legenda = '0: ';
            }
            $Descrizione = " <div style=\"display:inline-block; width:85%; padding:2px;\">" . $Legenda . ' ' . proLibConservazione::$ElencoDescrConserEsito[$key] . "</div>";
            $html.= "<div><div style=\"display:inline-block; margin-left:2px; $styleDiv $colore \"></div>$Descrizione</div>";
        }

        Out::html($this->nameForm . '_divLegendaStato', $html);
    }

    public function CaricaConteggiLegenda(&$appoggio) {
        $ConteggiVers = array();
        $ConteggiCons = array();
        foreach ($appoggio as &$result_rec) {
            if (!$result_rec['ROWID_ANAPRO']) {
                $ConteggiVers[proLibConservazione::CTR_NON_CONSERVATO] ++;
            } else {
                $ConteggiVers[$result_rec['ESITOVERSAMENTO']] ++;
                if ($result_rec['DATAVARIAZIONE']) {
                    $ConteggiVers[proLibConservazione::CTR_VARIATO] ++;
                }
            }
            /*
             *  Controllo se è conservabile:
             *  se è bloccato non serve che controllo se è conservabile.
             */
            if ($result_rec['ESITOVERSAMENTO'] != proLibConservazione::ESITO_BLOCCATO) {
                if (!$this->proLibConservazione->CheckProtocolloConservabile($result_rec)) {
                    $ConteggiVers[proLibConservazione::CTR_NON_CONSERVABILE] ++;
                    $result_rec['ESITOVERSAMENTO'] = proLibConservazione::CTR_NON_CONSERVABILE;
                    $result_rec['DESC_NONCONSERVABILE'] = $this->proLibConservazione->getErrMessage();
                }
            }
            /*
             * Controllo Conservazione
             */
            if ($result_rec['ESITOCONSERVAZIONE']) {
                $ConteggiCons[$result_rec['ESITOCONSERVAZIONE']] ++;
            } else {
                $ConteggiCons[proLibConservazione::CONSER_ESITO_NON_VERIFICATO] ++;
            }
        }
        $this->CaricaLegenda($ConteggiVers, $ConteggiCons);
    }

    public function ChiediMotivazione($TipoMotivo = 'Sblocco') {
        $valori[] = array(
            'label' => array(
                'value' => "Motivo di " . $TipoMotivo . " del protocollo.",
                'style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_motivazione',
            'name' => $this->nameForm . '_motivazione',
            'type' => 'text',
            'style' => 'margin:2px;width:300px;',
            'value' => '',
            'class' => 'required'
        );
        Out::msgInput(
                $TipoMotivo . ' del protocollo', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaInput' . $TipoMotivo, 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace"
        );
    }

    public function EsportaExcel() {
        /*
         * Elaboro i Dati:
         */
        $ValoriTabella = array();
        $result_tab = $this->elaboraRecords($this->elencoConservazioni);
        foreach ($result_tab as $key => $result_rec) {
            $ValoriTabella[$key]['TIPO'] = $result_rec['TIPO'];
            $ValoriTabella[$key]['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $ValoriTabella[$key]['NUMERO'] = intval(substr($result_rec['PRONUM'], 4));
            $ValoriTabella[$key]['OGGETTO'] = utf8_encode($result_rec['OGGETTO']);
            $ValoriTabella[$key]['ANNULLATO'] = 'NO';
            if ($result_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ValoriTabella[$key]['ANNULLATO'] = 'SI';
            }
            $ValoriTabella[$key]['DATA_VERSAMENTO'] = $result_rec['DATAVERSAMENTO'] ? date('d/m/Y', strtotime($result_rec['DATAVERSAMENTO'])) : "";
            $ValoriTabella[$key]['ORA_VERSAMENTO'] = $result_rec['ORAVERSAMENTO'];
            $ValoriTabella[$key]['ESITO_VERSAMENTO'] = $result_rec['ESITOVERSAMENTO'];
            $ValoriTabella[$key]['DESCRIZIONE_VERSAMENTO'] = utf8_encode(str_replace("<br>", " ", $result_rec['DESCRSTATOVERSAMENTO']));
            $ValoriTabella[$key]['ESITO_CONSERVAZIONE'] = $result_rec['ESITOCONSERVAZIONE'];
            $ValoriTabella[$key]['DESCRIZIONE_CONSERVAZIONE'] = utf8_encode(str_replace("<br>", " ", $result_rec['MESSAGGIOCONSERVAZIONE']));
        }
        $ita_grid01 = new TableView($this->gridConservazioni, array(
            'arrayTable' => $ValoriTabella));
        $ita_grid01->setSortIndex('ANNO ASC, NUMERO');
        $ita_grid01->exportXLS('', 'ElencoConservazioni.xls');
    }

    public function DecodificaFascicolo($rowid) {
        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        $this->keyFascicoloSelezionato = $Anapro_rec['PROFASKEY'];
        $Anaorg_rec = $this->proLib->GetAnaorg($Anapro_rec['PROFASKEY'], 'orgkey');
        $Proges_rec = $this->proLib->GetProges($Anapro_rec['PROFASKEY'], 'geskey');
        //
        Out::show($this->nameForm . '_divInfoFascicolo');
        Out::valore($this->nameForm . '_InfoFascicolo', $Anaorg_rec['ORGSEG']);
        Out::valore($this->nameForm . '_OggettoFascicolo', $Proges_rec['GESOGG']);
        //
        Out::show($this->nameForm . '_divGridProtocolli');
        Out::hide($this->nameForm . '_divGridFascicoli');
        TableView::clearGrid($this->gridConservazioni);
        TableView::enableEvents($this->gridConservazioni);
        TableView::reload($this->gridConservazioni);
    }

    public function MostraNascondiColonne() {
        /*
         * Nascondo Campi specifici.
         */
        TableView::showCol($this->gridConservazioni, "PROPAR");
        TableView::hideCol($this->gridConservazioni, "PRATICA");
        if ($_POST[$this->nameForm . '_RICERCA']['TIPO'] == 'I') {
            TableView::hideCol($this->gridConservazioni, "PROPAR");
            TableView::showCol($this->gridConservazioni, "PRATICA");
        }
    }

}

?>
