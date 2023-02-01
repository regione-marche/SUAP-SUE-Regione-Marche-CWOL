<?php

/**
 *
 * GESTIONE ITER TRASMISSIONI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    02.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicPratiche.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proHalley.class.php';

function proGestIter() {
    $proGestIter = new proGestIter();
    $proGestIter->parseEvent();
    return;
}

class proGestIter extends itaModel {

    public $nameForm = "proGestIter";
    public $PROT_DB;
    public $ITW_DB;
    public $workDate;
    public $proLib;
    public $proLibDeleghe;
    public $proLibTitolario;
    public $proLibPratica;
    public $proLibFascicolo;
    public $proLibMail;
    public $proLibTagDag;
    public $accLib;
    public $segLib;
    public $segLibAllegati;
    public $proIterAlle = array();
    public $proIterDest = array();
    public $proIterDestProt = array();
    public $proIter = array();
    public $gridAllegati = "proGestIter_gridAllegati";
    public $gridNote = "proGestIter_gridNote";
    public $gridDestinatari = "proGestIter_gridDestinatari";
    public $gridIter = "proGestIter_gridIter";
    public $gridRisultatoIter = "proGestIter_gridRisultatoIter";
    public $gridCampiAggiuntivi = "proGestIter_gridCampiAggiuntivi";
    public $gridFascicoli = "proGestIter_gridFascicoli";
    public $Destinatario = '';
    public $statoIter = 0;
    public $tipoProt;
    public $tabella;
    public $appoggio;
    public $visualizzazione;
    public $codiceDest;
    public $recordTrasmissioneSelezionato;
    public $currAllegato;
    public $currArcite; // ARCITE CORRENTE
    public $elencoDestinatari;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $proLibAllegati;
    public $datiAggiuntivi;
    public $proLibGiornaliero;
    public $proLibSdi;
    public $returnData = array();
    public $ElencoFascicoli = array();
    public $prmsEditNote;
    public $praSubPassoAllegatiSimple;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibDeleghe = new proLibDeleghe();
        $this->proLibTitolario = new proLibTitolario();
        $this->proLibPratica = new proLibPratica();
        $this->proLibFascicolo = new proLibFascicolo();
        $this->proLibMail = new proLibMail();
        $this->proLibTagDag = new proLibTabDag();
        $this->accLib = new accLib();
        $this->segLib = new segLib();
        $this->segLibAllegati = new segLibAllegati();
        $this->proLibGiornaliero = new proLibGiornaliero();
        $this->proLibSdi = new proLibSdi();
// Apro il DB
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITW_DB = $this->accLib->getITW();
        $this->workDate = date('Ymd');
        $this->proIterAlle = App::$utente->getKey($this->nameForm . '_proIterAlle');
        $this->proIterDest = App::$utente->getKey($this->nameForm . '_proIterDest');
        $this->proIterDestProt = App::$utente->getKey($this->nameForm . '_proIterDestProt');
        $this->proIter = App::$utente->getKey($this->nameForm . '_proIter');
        $this->Destinatario = App::$utente->getKey($this->nameForm . '_Destinatario');
        $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
        $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
        $this->appoggio = App::$utente->getKey($this->nameForm . '_appoggio');
        $this->visualizzazione = App::$utente->getKey($this->nameForm . '_visualizzazione');
        $this->codiceDest = App::$utente->getKey($this->nameForm . '_codiceDest');
        $this->recordTrasmissioneSelezionato = App::$utente->getKey($this->nameForm . '_recordTrasmissioneSelezionato');
        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
        $this->currArcite = App::$utente->getKey($this->nameForm . '_currArcite');
        $this->elencoDestinatari = App::$utente->getKey($this->nameForm . '_elencoDestinatari');
        $this->returnData = App::$utente->getKey($this->nameForm . '_returnData');
        $this->praSubPassoAllegatiSimple = App::$utente->getKey($this->nameForm . '_praSubPassoAllegatiSimple');
        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
        $this->datiAggiuntivi = unserialize(App::$utente->getKey($this->nameForm . '_datiAggiuntivi'));
        $this->ElencoFascicoli = unserialize(App::$utente->getKey($this->nameForm . '_ElencoFascicoli'));
        $this->prmsEditNote = unserialize(App::$utente->getKey($this->nameForm . '_prmsEditNote'));
        $this->proLibAllegati = new proLibAllegati();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proIterAlle', $this->proIterAlle);
            App::$utente->setKey($this->nameForm . '_proIterDest', $this->proIterDest);
            App::$utente->setKey($this->nameForm . '_proIterDestProt', $this->proIterDestProt);
            App::$utente->setKey($this->nameForm . '_proIter', $this->proIter);
            App::$utente->setKey($this->nameForm . '_Destinatario', $this->Destinatario);
            App::$utente->setKey($this->nameForm . '_tipoProt', $this->tipoProt);
            App::$utente->setKey($this->nameForm . '_tabella', $this->tabella);
            App::$utente->setKey($this->nameForm . '_appoggio', $this->appoggio);
            App::$utente->setKey($this->nameForm . '_visualizzazione', $this->visualizzazione);
            App::$utente->setKey($this->nameForm . '_codiceDest', $this->codiceDest);
            App::$utente->setKey($this->nameForm . '_recordTrasmissioneSelezionato', $this->recordTrasmissioneSelezionato);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_currArcite', $this->currArcite);
            App::$utente->setKey($this->nameForm . '_elencoDestinatari', $this->elencoDestinatari);
            App::$utente->setKey($this->nameForm . '_datiAggiuntivi', $this->datiAggiuntivi);
            App::$utente->setKey($this->nameForm . '_returnData', $this->returnData);
            App::$utente->setKey($this->nameForm . '_praSubPassoAllegatiSimple', $this->praSubPassoAllegatiSimple);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_ElencoFascicoli', serialize($this->ElencoFascicoli));
            App::$utente->setKey($this->nameForm . '_prmsEditNote', serialize($this->prmsEditNote));
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
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            $this->close();
            return;
        }
        switch ($_POST['event']) {
            case 'openform':
                Out::tabRemove($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneDest");
                $this->visualizzazione = false;

                $this->codiceDest = proSoggetto::getCodiceSoggettoFromIdUtente();
                if (!$this->codiceDest) {
                    Out::msgInfo("ATTENZIONE !", "<br><br><h1>CODICE DESTINATARIO NON CONFIGURATO, CONTATTARE L'AMMINISTRATORE DEL SISTEMA</h1><br>");
                    $this->pToParent();
                    break;
                }
                $this->CreaCombo();
                if ($_POST['tipoOpen'] == 'visualizzazione') {
                    $this->visualizzazione = true;
                    $this->openDettaglio($_POST['rowidIter']);
                } else if ($_POST['tipoOpen'] == 'gestioneFascicolo') {
                    $arcite_check = $this->proLib->GetArcite($_POST['rowidIter'], 'rowid');
                    $arcite_recCheck = $this->proLib->getGenericTab(
                            "SELECT *
                                FROM ARCITE 
                                WHERE ITEPRO = {$arcite_check['ITEPRO']} AND ITEPAR = '{$arcite_check['ITEPAR']}' AND ITEDES = '$this->codiceDest'", false);
                    if ($arcite_recCheck) {
                        $this->openDettaglio($arcite_recCheck['ROWID']);
                        Out::hide($this->nameForm . '_Rifiuta');
                    } else {
                        $this->openDettaglio($_POST['rowidIter']); //, true);
                        Out::show($this->nameForm . '_InCarico');
                    }
                } else if ($_POST['rowidIter']) {
                    $this->openDettaglio($_POST['rowidIter']);
                } else {
                    $this->OpenRicerca();
                }
                if ($_POST['aggiornaFatturaHalley']) {
                    $this->AggiornaFatturaHalley();
                }
                if ($_POST['prmsEditNote']) {
                    $this->prmsEditNote = true;
                }
                /*
                 * Disattivo Campo Settore
                 */
                $anaent_57 = $this->proLib->GetAnaent('57');
                if ($anaent_57['ENTDE3']) {
                    Out::removeElement($this->nameForm . '_divCodiceServizio');
                    Out::removeElement($this->nameForm . "_Dest_cod_butt");
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->EditAllegati();
                        break;
                    case $this->gridRisultatoIter:
                        $this->openDettaglio($_POST['rowid']);
                        break;
                    case $this->gridNote:
                        if (!$this->visualizzazione || $this->prmsEditNote) {
                            $dati = $this->noteManager->getNota($_POST['rowid']);
                            $readonly = false;
                            if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                                Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                                $readonly = true;
                            }
                            if ($this->visualizzazione) {
                                $readonly = true;
                            }
                            $propar = $_POST[$this->nameForm . '_ANAPRO']['PROPAR'];
                            $pronum = $_POST[$this->nameForm . '_ANAPRO']['PRONUM'];
                            $arcite_tab = $this->proLib->getGenericTab("SELECT DISTINCT(ITEDES) AS ITEDES  FROM ARCITE WHERE ITEPAR='$propar' AND ITEPRO=" . $pronum . " ORDER BY ROWID DESC");
                            $destinatari = array();
                            foreach ($arcite_tab as $arcite_rec) {
                                $destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE MEDCOD='{$arcite_rec['ITEDES']}' AND MEDANN=0", false);
                            }
                            $model = 'proDettNote';
                            itaLib::openForm($model);
                            $formObj = itaModel::getInstance($model);
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setReturnEvent('returnProDettNote');
                            $formObj->setReturnId('');
                            $rowid = $_POST['rowid'];
                            $_POST = array();
                            $_POST['dati'] = $dati;
                            $_POST['rowid'] = $rowid;
                            $_POST['destinatari'] = $destinatari;
                            $_POST['readonly'] = $readonly;
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        }
                        break;
                    case $this->gridIter:
                        if (!$this->visualizzazione) {
                            $itekey = $_POST['rowid'];
                            $arcite_rec = $this->proLib->GetArcite($itekey, 'itekey');
                            $this->recordTrasmissioneSelezionato = $arcite_rec;
                            if ($arcite_rec['ITEDLE'] == '' && $arcite_rec['ITEFIN'] == '') {
                                $checked = 'checked';
                                if (!$arcite_rec['ITEGES']) {
                                    $checked = '';
                                }
                                $campi[] = array(
                                    'label' => array(
                                        'value' => 'Annotazioni',
                                        'style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                    ),
                                    'id' => $this->nameForm . '_AnnotazioneNew',
                                    'name' => $this->nameForm . '_AnnotazioneNew',
                                    'type' => 'text',
                                    'size' => '100',
                                    'value' => $arcite_rec['ITEANN'],
                                    'maxchars' => '5');
                                $campi[] = array(
                                    'label' => array(
                                        'value' => 'Data di Termine',
                                        'style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                    ),
                                    'id' => $this->nameForm . '_termineNew',
                                    'name' => $this->nameForm . '_termineNew',
                                    'type' => 'text',
                                    'size' => '15',
                                    'class' => "ita-date",
                                    'value' => $arcite_rec['ITETERMINE'],
                                    'maxchars' => '5');
                                $campi[] = array(
                                    'label' => array(
                                        'value' => 'Gestione',
                                        'style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                    ),
                                    'id' => $this->nameForm . '_gestioneNew',
                                    'name' => $this->nameForm . '_gestioneNew',
                                    'type' => 'checkbox',
                                    'class' => "ita-checkbox",
                                    $checked => $checked);
                                Out::msgInput(
                                        'Modifica Dati Trasmissione', $campi, array(
                                    'F6-Modifica Dati' => array('id' => $this->nameForm . '_ModificaDati', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                    'F7-Annulla Invio' => array('id' => $this->nameForm . '_ConfermaAnnullaInvio', 'model' => $this->nameForm, 'shortCut' => "f7")
                                        ), $this->nameForm
                                );
                            } else {
                                if ($arcite_rec['ITEDLE']) {
                                    Out::msgInfo("Attenzione", "Non è possibile modificare la trasmissione perchè è già stata letta.");
                                } else {
                                    Out::msgInfo("Attenzione", "Non è possibile modificare la trasmissione perchè è chiusa.");
                                }
                            }
                        }
                        break;

                    case $this->gridFascicoli:
                        $rowid = $_POST['rowid'];
                        $orgkey = $this->ElencoFascicoli[$rowid]['ORGKEY'];
                        if ($orgkey) {
                            $this->ApriGestioneFascicolo($orgkey);
                        }
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        if (!$this->visualizzazione || $this->prmsEditNote) {
                            $propar = $_POST[$this->nameForm . '_ANAPRO']['PROPAR'];
                            $pronum = $_POST[$this->nameForm . '_ANAPRO']['PRONUM'];
                            $arcite_tab = $this->proLib->getGenericTab("SELECT DISTINCT(ITEDES) AS ITEDES  FROM ARCITE WHERE ITEPAR='$propar' AND ITEPRO=" . $pronum . " ORDER BY ROWID DESC");
                            $destinatari = array();
                            foreach ($arcite_tab as $arcite_rec) {
                                $destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE MEDCOD='{$arcite_rec['ITEDES']}' AND MEDANN=0", false);
                            }

                            $model = 'proDettNote';
                            itaLib::openForm($model);
                            $formObj = itaModel::getInstance($model);
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setReturnEvent('returnProDettNote');
                            $formObj->setReturnId('');
                            $rowapp = $_POST[$this->nameForm . '_ARCITE']['ROWID'];
                            $_POST = array();
                            $_POST['idRitorno'] = $rowapp;
                            $_POST['destinatari'] = $destinatari;
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDestinatari:
                        if (array_key_exists($_POST['rowid'], $this->proIterDest) == true) {
                            unset($this->proIterDest[$_POST['rowid']]);
                        }
                        $this->caricaGriglia($this->gridDestinatari, $this->proIterDest);
                        break;
                    case $this->gridNote:
                        if (!$this->visualizzazione || $this->prmsEditNote) {
                            $dati = $this->noteManager->getNota($_POST['rowid']);
                            if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                                Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                                break;
                            }

                            $this->noteManager->cancellaNota($_POST['rowid']);
                            $this->noteManager->salvaNote();
                            $this->caricaNote();
                        }
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridDestinatari:
                        $this->proIterDest[$_POST['rowid']]['DESGESADD'] = $_POST['value'];
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridDestinatari:
                        if (array_key_exists($_POST['rowid'], $this->proIterDest) == true) {
                            switch ($_POST['colName']) {
                                case 'TERMINE':
                                    $this->appoggio = $this->proIterDest[$_POST['rowid']]['INDICE'] + 1;
                                    Out::msgInput(
                                            'Data Termine', array(
                                        'label' => 'Data<br>',
                                        'id' => $this->nameForm . '_Termine',
                                        'name' => $this->nameForm . '_Termine',
                                        'type' => 'text',
                                        'size' => '15',
                                        'class' => "ita-date",
                                        'maxchars' => '12'), array(
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaDataTermine', 'model' => $this->nameForm)
                                            ), $this->nameForm . '_divGestione'
                                    );
                                    Out::valore($this->nameForm . '_Termine', $this->proIterDest[$_POST['rowid']]['TERMINE']);
                                    break;
                            }
                        }
                        break;
                    case $this->gridAllegati:
                        $doc = $this->proIterAlle[$_POST['rowid']];
                        switch ($_POST['colName']) {
                            case 'FIRMA':
                                if (!$doc['FIRMA']) {
                                    break;
                                }
                                $ext = pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION);
                                if (strtolower($ext) == "p7m") {
                                    $FilePathCopy = $this->proLibAllegati->CopiaDocAllegato($doc['ROWID'], '', true);
                                    if (!$FilePathCopy) {
                                        Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                                        break;
                                    }
//                                    $this->proLibAllegati->VisualizzaFirme($doc['FILEPATH'], $doc['DOCNAME']);
                                    $this->proLibAllegati->VisualizzaFirme($FilePathCopy, $doc['DOCNAME']);
                                } else if (!$this->visualizzazione) {
                                    $this->VaiAllaFirma();
                                }
                                break;
                            case 'SDI':
                                if (!$doc['SDI']) {
                                    break;
                                }
                                $proLibSdi = new proLibSdi();
                                $DocPath = $this->proLibAllegati->GetDocPath($doc['ROWID']);
//                                $FileSdi = array('LOCAL_FILEPATH' => $doc['FILEPATH'], 'LOCAL_FILENAME' => $doc['DOCNAME']);
                                $FileSdi = array('LOCAL_FILEPATH' => $DocPath['DOCPATH'], 'LOCAL_FILENAME' => $doc['DOCNAME']);
                                $ExtraParam = array('PARSEALLEGATI' => true);
                                $objProSdi = proSdi::getInstance($FileSdi, $ExtraParam);
                                if (!$objProSdi) {
                                    Out::msgStop("Attenzione", "Errore nell'istanziare proSdi.");
                                    break;
                                }
                                if ($objProSdi->getErrCode() == -9) {
                                    Out::msgInfo('Attenzione', $objProSdi->getErrMessage());
                                }
//                                if ($objProSdi->isZipFatturaPA()) {
//                                    //@TODO DA FARE: Usare Ric? (però ogni volta deve sbustare tutto ecc...)
//                                    Out::msgInfo('Da implementare', 'Da implementare');
//                                }
                                if ($objProSdi->isMessaggioSdi()) {
                                    $Xmlstyle = proSdi::$ElencoStiliMessaggio[$objProSdi->getTipoMessaggio()];
                                    $FilePath = $objProSdi->getFilePathMessaggio();
                                    if ($Xmlstyle && $FilePath) {
                                        $proLibSdi->VisualizzaXmlConStile($Xmlstyle, $FilePath);
                                    }
                                    break;
                                }

                                if ($objProSdi->isFatturaPA()) {
                                    $FilePathFattura = $objProSdi->getFilePathFattura();
                                    $Xmlstyle = proSdi::StileFattura;
                                    $FilePath = $FilePathFattura[0];
                                    if ($Xmlstyle && $FilePath) {
                                        //$proLibSdi->VisualizzaXmlConStile($Xmlstyle, $FilePath);
                                    }
                                    $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                                    $proLibSdi->openInfoFattura($objProSdi, $anapro_rec);
                                }
                                break;
                        }
                        break;
                    case $this->nameForm . '_gridDestinatariProtocollo':
                        switch ($_POST['colName']) {
                            case 'MAILDEST':
                                if (array_key_exists($_POST['rowid'], $this->elencoDestinatari) == true) {
                                    if (!$this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                                        break;
                                    }
                                    if (!$this->proLibMail->CheckStatoMail($this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL'])) {
                                        Out::msgInfo('Stato Mail', $this->proLibMail->getMailAvviso());
                                        break;
                                    }

                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'ACCETTAZIONE':
                                if (array_key_exists($_POST['rowid'], $this->elencoDestinatari) == true) {
                                    if ($this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRic($this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL']);
                                    if (!$retRic['ACCETTAZIONE']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $retRic['ACCETTAZIONE'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'CONSEGNA':
                                if (array_key_exists($_POST['rowid'], $this->elencoDestinatari) == true) {
                                    if ($this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRicMulti($this->elencoDestinatari[$_POST['rowid']]['DESIDMAIL']);
                                    if (!$retRic['CONSEGNA']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $retRic['CONSEGNA'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                        }
                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridRisultatoIter:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proElencoIterPortlet', $parameters);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRisultatoIter:
                        if ($this->tabella != false) {
                            $ita_grid01 = new TableView($this->gridRisultatoIter, array('arrayTable' => $this->tabella, 'rowIndex' => 'idx'));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows(18);
                            $ita_grid01->clearGrid($this->gridRisultatoIter);
                            $ita_grid01->getDataPage('json');
                        }
                        break;
                    case $this->gridNote:
                        $this->noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $this->currArcite['ITEPRO'], "PROPAR" => $this->currArcite['ITEPAR']));
                        $this->caricaNote();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                    case $this->nameForm . '_Invia':

                        $controllaPresenza = $this->checkPresenzaDestinatari();
                        if ($controllaPresenza !== false) {
                            Out::msgQuestion("ATTENZIONE!", "Uno o Più destinatari sono presenti nell'elenco delle trasmissioni. Vuoi continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaInvioTrasm', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaInvioTrasmStd', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    /* Estensione di visibilità è riservata al 
                     * programma "gestioen Fascicoli" 
                     * visto che la fascicolazione multipla di un 
                     * protocollo renderebbe troppo complesso il processo 
                     */
//                    case $this->nameForm . '_ConfermaInvioTrasm':
//                        if ($this->proIterDest) {
//                            $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
//                            if ($anapro_rec['PROFASKEY'] && !($anapro_rec['PROPAR'] == 'F' || $anapro_rec['PROPAR'] == 'N')) {
//                                Out::msgQuestion("Protocollo Fascicolato.", "Il protocollo è stato inserito in un fascicolo. Estendere la visibilità del fascicolo ai destinatari di questa trasmissione?", array(
//                                    'F8-Continua senza Visibilità' => array('id' => $this->nameForm . '_ConfermaInvioTrasmStd', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                                    'F5-Estendi Visibilità' => array('id' => $this->nameForm . '_ConfermaEstendiVisibilita', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                        )
//                                );
//                                break;
//                            }
//                        }
                    case $this->nameForm . '_ConfermaInvioTrasmStd':
                        $this->inviaTrasmissione();
                        break;
                    /* INIZIO PATCH */
                    case $this->nameForm . '_InviaInVisione':
                        $controllaPresenza = $this->checkPresenzaDestinatari();
                        if ($controllaPresenza !== false) {
                            Out::msgQuestion("ATTENZIONE!", "Uno o Più destinatari sono presenti nell'elenco delle trasmissioni. Vuoi continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaInvioInVisione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaInvioInVisione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ConfermaInvioInVisione':
                        foreach ($this->proIterDest as $key => $proDest) {
                            $this->proIterDest[$key]['DESGESADD'] = 0;
                        }
                        $this->inviaTrasmissione();
                        break;
                    /* FINE PATCH */

                    /* Estensione di visibilità è riservata al 
                     * programma "gestioen Fascicoli" 
                     * visto che la fascicolazione multipla di un 
                     * protocollo renderebbe troppo complesso il processo 
                     */
//                    case $this->nameForm . '_ConfermaEstendiVisibilita':
//                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
//                        $anapro_fas = $this->proLib->getGenericTab("SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='{$anapro_rec['PROFASKEY']}'", false);
//
//                        $arcite_fas = $this->proLib->GetArcite($anapro_fas['PRONUM'], 'codice', false, $anapro_fas['PROPAR']);
//                        foreach ($this->proIterDest as $proDest) {
//                            $destinatario = array(
//                                "DESCUF" => $proDest['ITEUFF'],
//                                "DESCOD" => $proDest['DESCODADD'],
//                                "DESGES" => $proDest['DESGESADD'],
//                                "ITEBASE" => 0,
//                                "DESTERMINE" => $proDest['TERMINE']
//                            );
//                            $extraParm = array(
//                                "NOTE" => "VISIBILITA' FASCICOLO",
//                                "NODO" => "ASF"
//                            );
//                            $iter = proIter::getInstance($this->proLib, $anapro_fas);
//                            $iterNode = $iter->insertIterNode($destinatario, $arcite_fas, $extraParm);
//                            // Appena inserito chiudo il nodo
//                            if ($iterNode) {
//                                $iter->chiudiIterNode($iterNode);
//                            }
//                        }
//                        $this->inviaTrasmissione();
//                        break;
                    case $this->nameForm . '_ContinuaHalley':
                        $this->close();
                        Out::setFocus('', 'gbox_proElencoIterPortlet_gridIter #gs_NUMERO');
                        break;

                    case $this->nameForm . '_Chiudi':
                        $this->chiudiIter($_POST[$this->nameForm . '_Annotazioni']);
                        //$this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        $this->ricaricaPortlet();
                        $this->close();
                        Out::desktopTabSelect('ita-home');
                        Out::desktopTabSelect('proElencoIterPortlet');
                        break;
                    case $this->nameForm . '_Riapri':
                        /**
                         * aggiunto bottone per riaprire un protocollo chiuso
                         * Mario - 30.07.2013
                         */
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $arcite_rec['ITEFIN'] = '';
                        $arcite_rec['ITEFLA'] = '';
                        $arcite_rec['ITEANN'] = $arcite_rec['ITEANN'] ? 'RIAPERTO - ' . $arcite_rec['ITEANN'] : 'RIAPERTO'; //Se presenti annotazioni: accodo.
                        $update_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'];
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $update_Info);
                        if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {
                            Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                            break;
                        }
                        $this->ricaricaFascicolo();
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        $this->ricaricaPortlet();
                        break;
                    case $this->nameForm . '_Protocolla':
                        //
                        // Funzione Sospesa
                        //
                        break;
                        $model = 'proArri';
                        $_POST['event'] = 'openform';
                        $_POST['tipoProt'] = 'C';
                        $_POST['datiComu'] = $_POST[$this->nameForm . '_ANAPRO']['PRONUM'];
                        itaLib::openForm($model);
                        Out::hide($this->nameForm);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->close();
                        break;
                    case $this->nameForm . '_Procedimento':
                        //
                        // Attualmente il button che implementa la chiamata è accesso fisso controllare se attivate
                        // integrazioni con fascicoli elettronici
                        //
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
                        $model = 'praGest';
                        $_POST['event'] = 'openform';
                        $_POST['DaProtocollo'] = 'true';
                        $_POST['DatiProt'] = $anapro_rec;
                        $where = " CATCOD='" . $anapro_rec['PROCAT'] . "' AND CLACOD='" . substr($anapro_rec['PROCCA'], 4, 4)
                                . "' AND FASCOD='" . substr($anapro_rec['PROCCF'], 8, 4) . "'";
                        $titproc_rec = $this->proLib->GetTitproc($where, 'where');
                        $_POST['DatiProt']['PRANUM'] = $titproc_rec['PRANUM'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->close();
                        break;
                    case $this->nameForm . '_Acquisisci':
                        $valori[] = array(
                            'label' => array(
                                'value' => "Annotazioni sull'acquisizione della trasmissione?",
                                'style' => 'width:350px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_motivazioneAcq',
                            'name' => $this->nameForm . '_motivazioneAcq',
                            'type' => 'text',
                            'style' => 'margin:2px;width:350px;',
                            'value' => ''
                        );
                        Out::msgInput(
                                'Note acquisizione', $valori
                                , array(
                            'Acquisisci' => array('id' => $this->nameForm . '_AcqNote',
                                'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaAcq',
                                'model' => $this->nameForm)
                                ), $this->nameForm
                        );
                        Out::setFocus('', $this->nameForm . '_motivazione');

                        break;
                    case $this->nameForm . '_Rifiuta':
                    case $this->nameForm . '_RifiutaMotivo':
                        if ($_POST[$this->nameForm . '_motivazione'] == '') {
                            $docfirma_check = $this->proLibAllegati->GetDocfirma($this->currArcite['ROWID'], 'rowidarcite');
                            if ($docfirma_check) {
                                $valori[] = array(
                                    'label' => array(
                                        'value' => "Perché rifiuti di firmare gli allegati?",
                                        'style' => 'width:350px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                    ),
                                    'id' => $this->nameForm . '_motivazione',
                                    'name' => $this->nameForm . '_motivazione',
                                    'type' => 'text',
                                    'style' => 'margin:2px;width:350px;',
                                    'value' => ''
                                );
                            } else {
                                $valori[] = array(
                                    'label' => array(
                                        'value' => "Perché rifiuti la gestione della Trasmissione?",
                                        'style' => 'width:350px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                    ),
                                    'id' => $this->nameForm . '_motivazione',
                                    'name' => $this->nameForm . '_motivazione',
                                    'type' => 'text',
                                    'style' => 'margin:2px;width:350px;',
                                    'value' => ''
                                );
                            }
                            Out::msgInput(
                                    'Motivo del Rifiuto.', $valori
                                    , array(
                                'Rifiuta' => array('id' => $this->nameForm . '_RifiutaMotivo',
                                    'model' => $this->nameForm)
                                    ), $this->nameForm
                            );
                            Out::setFocus('', $this->nameForm . '_motivazione');
                            break;
                        }
                        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $iter->rifiutaIterNode($arcite_rec, $_POST[$this->nameForm . '_motivazione']);
                        $this->openDettaglio($this->currArcite['ROWID']);
                        $this->ricaricaPortlet();
                        break;
                    case $this->nameForm . '_NoRifiuta':
                        Out::msgQuestion("ATTENZIONE.", "Vuoi riprendere in carico il protocollo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaNoRifiuta', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNoRifiuta', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaNoRifiuta':
                        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $iter->riprendiIterNode($arcite_rec);
                        $this->openDettaglio($this->currArcite['ROWID']);
                        $this->ricaricaPortlet();
                        break;
                    case $this->nameForm . '_AnnullaInvio':
                        Out::msgQuestion("ATTENZIONE.", "Vuoi annullare l'invio?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAnnullaInvio', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnnullaInvio', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAnnullaInvio':
                        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        $arcite_rec = $this->recordTrasmissioneSelezionato;
                        $iter->annullaIterNode($arcite_rec);
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;
                    case $this->nameForm . '_ModificaDati':
                        $Arcite_rec = $this->recordTrasmissioneSelezionato;
                        $Arcite_rec['ITEANN'] = $_POST[$this->nameForm . '_AnnotazioneNew'];
                        $Arcite_rec['ITETERMINE'] = $_POST[$this->nameForm . '_termineNew'];
                        $Arcite_rec['ITEGES'] = $_POST[$this->nameForm . '_gestioneNew'];
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $Arcite_rec, 'aggiorna ITEANN');
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;
                    case $this->nameForm . '_AcqNote':
                        if ($_POST[$this->nameForm . '_motivazioneAcq'] == '') {
                            Out::msgInfo("Acquisizione", "Note sull'acquisizione obbligatorie. Riprova.");
                            break;
                        }
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $soggetto = proSoggetto::getInstance($this->proLib, proSoggetto::getCodiceSoggettoFromIdUtente(), $arcite_rec['ITEUFF']);
                        if (!$soggetto->getSoggetto()) {
                            Out::msgInfo("Acquisizione", "Il soggetto che acquisisce non ha ruolo nell'ufficio:{$arcite_rec['ITEUFF']}");
                            break;
                        }
                        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        // Test Delega.
                        $extraParams = array(
                            'DELESCRIVANIA' => 1,
                            'DELESRCUFF' => $arcite_rec['ITEUFF'],
                            'DELESRCCOD' => $arcite_rec['ITEDES']
                        );

                        /*
                         *  Controllo se è una acquisizione di richiesta firma.
                         */
                        $arcite_padre_delega = $this->proLib->GetArcite($this->currArcite['ITEPRE'], 'itekey');
                        if ($arcite_rec['ITETIP'] == proIter::ITETIP_ALLAFIRMA || $arcite_padre_delega['ITETIP'] == proIter::ITETIP_ALLAFIRMA) {
                            if ($this->currArcite['ITEDES'] == $this->codiceDest) {
                                if ($this->currArcite['ITETIP'] == proIter::ITETIP_PARERE_DELEGA) {
                                    if ($arcite_padre_delega['ITETIP'] == proIter::ITETIP_ALLAFIRMA && $arcite_padre_delega['ITEFIN'] == '') {
                                        $arcite_acquisito = $iter->acquisisciFirmaIterNode($arcite_rec, $_POST[$this->nameForm . '_motivazioneAcq'], $soggetto);
                                        if (!$arcite_acquisito) {
                                            Out::msgStop("Attenzione", "Errore in acquisizione Firma.");
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $delegaScrivaniaAttiva = $this->proLibDeleghe->getDelegheAttive(proSoggetto::getCodiceSoggettoFromIdUtente(), $extraParams, proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);
                                if (count($delegaScrivaniaAttiva)) {
                                    $extraParm = array('DESCTRASM' => "AQUISIZIONE DA SCRIVANIA DELEGATA DI");
                                    $Sostituto_rec = array('DELEDSTCOD' => $this->codiceDest, 'DELEDSTUFF' => $arcite_rec['ITEUFF']);
                                    $RowidArcite = $iter->InsertSostituto($Sostituto_rec, $arcite_rec, $extraParm);
                                    $NewArcite_rec = $this->proLib->GetArcite($RowidArcite, 'rowid');
                                    $arcite_acquisito = $iter->acquisisciFirmaIterNode($NewArcite_rec, $_POST[$this->nameForm . '_motivazioneAcq'], $soggetto);
                                    if (!$arcite_acquisito) {
                                        Out::msgStop("Attenzione", "Errore in acquisizione Firma.");
                                        break;
                                    }
                                }
                            }
                        } else {
                            /*
                             * Controllo se è una acquisizione di una scrivania per 
                             * deleghe attive o se si tratta di una normale acquisizione.
                             */
                            $delegaScrivaniaAttiva = $this->proLibDeleghe->getDelegheAttive(proSoggetto::getCodiceSoggettoFromIdUtente(), $extraParams, proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);
                            if (count($delegaScrivaniaAttiva)) {
                                $extraParm = array('DESCTRASM' => "AQUISIZIONE DA SCRIVANIA DELEGATA DI", "NOTE" => "Acquisito da Delega.");
                                $Sostituto_rec = array('DELEDSTCOD' => $this->codiceDest, 'DELEDSTUFF' => $arcite_rec['ITEUFF']);
                                $RowidArcite = $iter->InsertSostituto($Sostituto_rec, $arcite_rec, $extraParm);
                                $arcite_acquisito['ROWID'] = $RowidArcite;
                            } else {
                                $arcite_acquisito = $iter->acquisisciIterNode($arcite_rec, $_POST[$this->nameForm . '_motivazioneAcq'], $soggetto);
                            }
                        }
                        $this->openDettaglio($arcite_acquisito['ROWID']);
                        break;
                    case $this->nameForm . '_InCarico':
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $arcite_rec['ITEDATACC'] = date('Ymd');
                        $arcite_rec['ITENOTEACC'] = $_POST[$this->nameForm . '_motivazione'];
                        $arcite_rec['ITESTATO'] = proIter::ITESTATO_INCARICO;
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false);
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;

                    case $this->nameForm . '_InCaricoAcquisizione':
                        /* Controllo dati */
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        if ($arcite_rec['ITEDATACQ']) {
                            Out::msgStop("Acquisizione", "Attenzione richiesta già Acquisita.");
                            break;
                        }
                        /* Lettura dati Soggetto */
                        $soggetto = proSoggetto::getInstance($this->proLib, proSoggetto::getCodiceSoggettoFromIdUtente(), $arcite_rec['ITEUFF']);
                        if (!$soggetto->getSoggetto()) {
                            Out::msgInfo("Acquisizione", "Il soggetto che acquisisce non ha ruolo nell'ufficio:{$arcite_rec['ITEUFF']}");
                            break;
                        }
                        /* Lettura ITER */
                        $NotaAcquisizione = 'ACQUISITA TRASMISSIONE DI UFFICIO';
                        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        $arcite_acquisito = $iter->acquisisciIterNode($arcite_rec, $NotaAcquisizione, $soggetto);
                        $arcite_acquisito['ITEDATACC'] = date('Ymd');
                        $arcite_acquisito['ITENOTEACC'] = $NotaAcquisizione;
                        $arcite_acquisito['ITESTATO'] = proIter::ITESTATO_INCARICO;
                        /* Aggiorno Arcite di Acquisizione */
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_acquisito, 'AGGIORNA DATI DA ACQUISIZIONE');
                        $this->openDettaglio($arcite_acquisito['ROWID']);
                        $this->ricaricaPortlet();
                        break;

                    case $this->nameForm . '_PresaVisione':
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $arcite_rec['ITEFIN'] = date("Ymd");
                        $arcite_rec['ITEFLA'] = proIter::ITEFLA_CHIUSO;
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false);
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        $this->ricaricaPortlet();
                        /*
                         * Se non è attivo parametro per invio trasmissioni in visione posso
                         * chiudere automaticamente. Altrimenti deve poter inviare il protocollo
                         */
                        $anaent_rec = $this->proLib->GetAnaent('40');
                        if (!$anaent_rec['ENTDE5']) {
                            $this->close();
                            Out::desktopTabSelect('ita-home');
                            Out::desktopTabSelect('proElencoIterPortlet');
                        }
                        break;
                    case $this->nameForm . '_Evidenza':
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        if ($arcite_rec['ITEEVIDENZA'] == 1) {
                            $evidenza = 0;
                            Out::html($this->nameForm . "_Evidenza_lbl", "Metti Evidenza");
                        } else {
                            $evidenza = 1;
                            Out::html($this->nameForm . "_Evidenza_lbl", "Togli Evidenza");
                        }
                        $arcite_rec['ITEEVIDENZA'] = $evidenza;
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false);
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->elencaRisultato();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_ConfermaDataTermine':
                        $this->proIterDest[$this->appoggio - 1]['TERMINE'] = $_POST[$this->nameForm . '_Termine'];
                        $this->caricaGriglia($this->gridDestinatari, $this->proIterDest);
                        break;
//                    case $this->nameForm . '_ANAPRO[PROARG]_butt':
                    case $this->nameForm . '_AnnullaMultiFascicolo':
                        $QuestionFatta = true;
                    case $this->nameForm . '_addFascicolo':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
                        /*
                         * 1. Prima controllo se il protocollo collegato è in un fascicolo e il protocollo non è fascicolato.
                         */

                        if ($anapro_rec['PROPRE'] && $QuestionFatta !== true) {
                            if ($this->proLibFascicolo->CtrProtPreFascicolato($anapro_rec['ROWID'])) {
                                $AnaproPre_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
                                if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                                    Out::msgQuestion("Fascicolo.", "Il protocollo collegato risulta fascicolato.<br>Vuoi inserire il protocollo in uno o più fascicoli del protocollo collegato? ", array(
                                        'Altro Fascicolo' => array('id' => $this->nameForm . '_AnnullaMultiFascicolo', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaMultiFascicolo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                    break;
                                }
                            }
                        }
                        $sel_procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $sel_clacod = $_POST[$this->nameForm . '_Clacod'];
                        $sel_fascod = $_POST[$this->nameForm . '_Fascod'];
                        $Titolario['VERSIONE_T'] = $anapro_rec['VERSIONE_T'];
                        $Titolario['PROCAT'] = $sel_procat;
                        $Titolario['CLACOD'] = $sel_clacod;
                        $Titolario['FASCOD'] = $sel_fascod;
                        $Titolario['ORGANN'] = $_POST[$this->nameForm . '_Organn'];
                        $this->ApriSelezioneFascicolo($Titolario);
                        break;

                    case $this->nameForm . '_ConfermaMultiFascicolo':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
                        $AnaproPre_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
                        $this->proLibFascicolo->ApriSelezioneFascicoloFromProt($AnaproPre_rec['ROWID'], $this->nameForm, 'returnMultiSelezioneFascicolo', $anapro_rec);
                        break;

// -------  VECCHIE FUNZIONI NON PIU' UTILIZZATE  -----------             
//                        if ($anapro_rec['PROPRE']) {
//                            $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                            if ($anapro_pre['PROFASKEY']) {
//                                $fascicolo_rec = $this->proLib->GetAnaorg($anapro_pre['PROFASKEY'], 'orgkey');
//                                $this->appoggio = array();
//                                $this->appoggio['ROWID'] = $fascicolo_rec['ROWID'];
//                                $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                                $propas_rec = $this->proLib->getGenericTab("SELECT
//                                    PROPAS.PRODPA AS PRODPA 
//                                    FROM PROPAS PROPAS 
//                                    LEFT OUTER JOIN PAKDOC PAKDOC
//                                    ON PROPAS.PROPAK = PAKDOC.PROPAK
//                                    WHERE PROPAS.PRONUM='{$progest_rec['GESNUM']}' AND PAKDOC.PRONUM={$anapro_rec['PROPRE']} AND PAKDOC.PROPAR='{$anapro_rec['PROPARPRE']}'", false);
//                                $catcod = substr($fascicolo_rec['ORGCCF'], 0, 4);
//                                $clacod = substr($fascicolo_rec['ORGCCF'], 4, 8);
//                                $fascod = substr($fascicolo_rec['ORGCCF'], 8, 12);
//                                $titolario = (int) $catcod;
//                                if ($clacod) {
//                                    $titolario .= "." . (int) $clacod;
//                                }
//                                if ($fascod) {
//                                    $titolario .= "." . (int) $fascod;
//                                }
//                                $messaggio = "<br>Il protocollo collegato è del fascicolo {$fascicolo_rec['ORGCOD']} titolario $titolario - {$fascicolo_rec['ORGDES']}";
//                                if ($propas_rec) {
//                                    $messaggio .= "<br>Collegare l'Azione '{$propas_rec['PRODPA']}' a questo protocollo?";
//                                    $bottoni = array(
//                                        'Seleziona un altro Fascicolo' => array('id' => $this->nameForm . '_SelezionaFascicolo', 'model' => $this->nameForm),
//                                        'Assegna questo Documento a un altra Azione' => array('id' => $this->nameForm . '_SelezionaAltroPasso', 'model' => $this->nameForm),
//                                        'Assegna questo Documento all\'Azione' => array('id' => $this->nameForm . '_ImpostaPasso', 'model' => $this->nameForm),
//                                    );
//                                } else {
//                                    $bottoni = array(
//                                        'Seleziona un altro Fascicolo' => array('id' => $this->nameForm . '_SelezionaFascicolo', 'model' => $this->nameForm),
//                                        'Assegna questo Documento a un\'Azione di questo Fascicolo' => array('id' => $this->nameForm . '_SelezionaAltroPasso', 'model' => $this->nameForm),
//                                    );
//                                }
//                                Out::msgQuestion("Fascicolazione.", $messaggio, $bottoni);
//                                break;
//                            }
//                        }
//                    case $this->nameForm . '_SelezionaFascicolo':
//
//                        $where = " WHERE ORGDAT='' AND ORGCCF='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT']
//                                . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'] . "'";
//                        if ($_POST[$this->nameForm . '_Organn']) {
//                            $where .= " AND ORGANN='{$_POST[$this->nameForm . '_Organn']}'";
//                        }
//                        proric::proRicOrgFas($this->nameForm, $where);
//                        break;
//                    case $this->nameForm . '_ImpostaPasso':
//                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
//                        $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                        $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                        $propas_rec = $this->proLib->getGenericTab("SELECT
//                                    PROPAS.* 
//                                    FROM PROPAS PROPAS 
//                                    LEFT OUTER JOIN PAKDOC PAKDOC
//                                    ON PROPAS.PROPAK = PAKDOC.PROPAK
//                                    WHERE PROPAS.PRONUM='{$progest_rec['GESNUM']}' AND PAKDOC.PRONUM={$anapro_rec['PROPRE']} AND PAKDOC.PROPAR='{$anapro_rec['PROPARPRE']}'", false);
//                        $this->proLibFascicolo->insertPakdoc($this, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $propas_rec['PROPAK']);
//                        $this->passoSelezionato($propas_rec);
//                        break;
//                    case $this->nameForm . '_SelezionaAltroPasso':
//                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
//                        if ($anapro_rec['PROPRE']) {
//                            $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                            if ($anapro_pre['PROFASKEY']) {
//                                $fascicolo_rec = $this->proLib->GetAnaorg($anapro_pre['PROFASKEY'], 'orgkey');
//                                $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                                if (!$progest_rec['GESNUM']) {
//                                    Out::msgStop("Attenzione!", "Errore in Selezione del procedimento.1");
//                                    break;
//                                }
//                                proRicPratiche::proRicPropas($this->nameForm, " WHERE PRONUM='{$progest_rec['GESNUM']}' AND PROFIN='' AND PROPUB=''");
//                                break;
//                            }
//                        }
//                        Out::msgStop("Attenzione!", "Errore in Selezione dell'Azione.");
//                        break;
//                    case $this->nameForm . '_AggiungiFascicolo':
//                        $procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
//                        $procla = $_POST[$this->nameForm . '_Clacod'];
//                        $profas = $_POST[$this->nameForm . '_Fascod'];
//                        if ($procat == '100' || $procla == '100') {
//                            break;
//                        }
//                        $fl_fascicola = false;
//                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
//                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
//                        $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//                        /*
//                         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
//                         */
//                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
//                            $fl_fascicola = true;
//                        }
//                        if ($fl_fascicola !== true) {
//                            Out::msgStop("Attenzione", "Non hai il permesso di aggiungere un fascicolo.");
//                            break;
//                        }
//                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
//                        $dati = array();
//                        $dati['livello1'] = $procat;
//                        $dati['livello2'] = $procla;
//                        $dati['livello3'] = $profas;
//                        $descTit = $_POST[$this->nameForm . '_catdes'];
//                        if ($_POST[$this->nameForm . '_clades']) {
//                            $descTit .= '-' . $_POST[$this->nameForm . '_clades'];
//                            if ($_POST[$this->nameForm . '_fasdes']) {
//                                $descTit .= '-' . $_POST[$this->nameForm . '_fasdes'];
//                            }
//                        }
//                        $dati['descTitolo'] = $descTit;
//                        $dati['prouof'] = $arcite_rec['ITEUFF'];
//                        $dati['tipoInserimento'] = 'nuovo';
//                        $dati['rowid_protocollo'] = $anapro_rec['ROWID'];
//                        $_POST = array();
//                        $model = 'proFascicola';
//                        itaLib::openForm($model);
//                        $formObj = itaModel::getInstance($model);
//                        $formObj->setReturnModel($this->nameForm);
//                        $formObj->setReturnEvent('onClick');
//                        $formObj->setReturnId($this->nameForm . '_ConfermaInputFasc2');
//                        $formObj->setDati($dati);
//                        $formObj->setEvent('openform');
//                        $formObj->parseEvent();
//                        break;
                    case $this->nameForm . '_AggiungiFascicolo':
                        $versione_t = $this->formData[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $procat = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $procla = $this->formData[$this->nameForm . '_Clacod'];
                        $profas = $this->formData[$this->nameForm . '_Fascod'];
//                        if ($procat == '100' || $procla == '100') {
//                            break;
//                        }
                        $fl_fascicola = false;
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                        /*
                         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
                         */
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'] || $permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA])) {
                            $fl_fascicola = true;
                        }
                        if ($fl_fascicola !== true) {
                            Out::msgStop("Attenzione", "Non hai il permesso di aggiungere un fascicolo.");
                            break;
                        }
                        $arcite_rec = $this->proLib->GetArcite($this->formData[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $dati = array();
                        $dati['versione'] = $versione_t;
                        $dati['livello1'] = $procat;
                        $dati['livello2'] = $procla;
                        $dati['livello3'] = $profas;
                        $descTit = $this->formData[$this->nameForm . '_catdes'];
                        if ($this->formData[$this->nameForm . '_clades']) {
                            $descTit .= '-' . $this->formData[$this->nameForm . '_clades'];
                            if ($this->formData[$this->nameForm . '_fasdes']) {
                                $descTit .= '-' . $this->formData[$this->nameForm . '_fasdes'];
                            }
                        }

                        $dati['FASCICOLO'] = array();
                        $dati['FASCICOLO']['versione'] = $this->returnData['TITOLARIO']['VERSIONE_T'];
                        $dati['FASCICOLO']['livello1'] = $this->returnData['TITOLARIO']['PROCAT'];
                        $dati['FASCICOLO']['livello2'] = $this->returnData['TITOLARIO']['CLACOD'];
                        $dati['FASCICOLO']['livello3'] = $this->returnData['TITOLARIO']['FASCOD'];
                        if ($dati['FASCICOLO']['livello1'] == '100' || $dati['FASCICOLO']['livello2'] == '100') {
                            break;
                        }

                        $dati['descTitolo'] = $descTit;
                        $dati['prouof'] = $arcite_rec['ITEUFF'];
                        $dati['tipoInserimento'] = 'nuovo';
                        $dati['rowid_protocollo'] = $anapro_rec['ROWID'];
                        $_POST = array();
                        $model = 'proFascicola';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('onClick');
                        $formObj->setReturnId($this->nameForm . '_ConfermaInputFasc2');
                        $formObj->setDati($dati);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaInputFasc2':
                        $dati = $_POST['dati'];
                        // Vecchio modo senza controllo Errato
                        //$this->AggiornaTitolario($this->formData[$this->nameForm . '_ANAPRO']['PRONUM'], $this->formData[$this->nameForm . '_ANAPRO']['PROPAR']);
                        $arcite_rec = $this->proLib->GetArcite($this->formData[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {
                            Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                            return;
                        }
                        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
                        $descrizione = $dati['descrizione'];
                        $codiceProcedimento = $dati['procedimento'];
                        $UfficioGest = $dati['prouof'];
                        $Ufficio = $arcite_rec['ITEUFF'];
                        $Respon = $arcite_rec['ITEDES'];
                        if ($dati['RES'] && $dati['UFF']) {
                            $Respon = $dati['RES'];
                            $Ufficio = $dati['UFF'];
                        }
                        /* Lettura della serie */
                        $Serie_rec = $dati['SERIE'];
                        $Dati_Anaorg = $dati['DATI_ANAORG'];

                        $newVersione = $dati['FASCICOLO']['versione'];
                        $newClassFascicolo = $dati['FASCICOLO']['livello1'] . $dati['FASCICOLO']['livello2'] . $dati['FASCICOLO']['livello3'];
                        $esitoFasciolo = $this->proLibFascicolo->creaFascicolo(
                                $this, array('TITOLARIO' => $newClassFascicolo, 'VERSIONE_T' => $newVersione, 'UFF' => $Ufficio, 'RES' => $Respon, 'GESPROUFF' => $UfficioGest, 'SERIE' => $Serie_rec, 'DATI_ANAORG' => $Dati_Anaorg), $descrizione, $codiceProcedimento
                        );
                        if (!$esitoFasciolo) {
                            Out::msgStop("Attenzione!", $this->proLibFascicolo->getErrMessage());
                            break;
                        }
                        $proges_rec = $this->proLibPratica->GetProges($esitoFasciolo, 'rowid');


                        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($proges_rec['GESKEY']);
                        if ($this->proLibFascicolo->insertDocumentoFascicolo($this, $proges_rec['GESKEY'], $arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'], $anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR'])) {
                            $this->ricaricaFascicolo();
                            $this->openDettaglio($arcite_rec['ROWID']);
                            $this->ApriGestioneFascicolo($proges_rec['GESKEY']);
                            Out::msgBlock('', 2000, false, "Nuovo fascicolo creato correttamente");
                        }
                        break;
                    case $this->nameForm . '_VisualizzaEml':
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $FilePathDest = $this->proLibAllegati->CopiaDocAllegato($this->currAllegato['Rowid'], '', true);
                        if (!$FilePathDest) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                            break;
                        }
//                        $_POST['codiceMail'] = $this->currAllegato['FileDati'];// Vecchia
                        $_POST['codiceMail'] = $FilePathDest;
                        $this->currAllegato = null;
                        $_POST['tipo'] = 'file';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ScaricaEml':
                        $this->proLibAllegati->OpenDocAllegato($this->currAllegato['Rowid'], true);
                        //Out::openDocument(utiDownload::getUrl($this->currAllegato['FileAllegato'], $this->currAllegato['FileDati']));// Vecchia
                        $this->currAllegato = null;
                        break;
                    case $this->nameForm . '_ANAPRO[PROCAT]_butt':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['BLOC_TITOLARIO'] == '1') {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $anapro_rec['VERSIONE_T']);
                        } else {
                            //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $anapro_rec['VERSIONE_T']);
                        }
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['BLOC_TITOLARIO'] == '1') {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $anapro_rec['VERSIONE_T']);
                        } else {
                            if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                                $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT'] . "'");
                            }
                            //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $anapro_rec['VERSIONE_T'], $where);
                        }
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['BLOC_TITOLARIO'] == '1') {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $anapro_rec['VERSIONE_T']);
                        } else {
                            if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                                $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT'] . "'";
                                if ($_POST[$this->nameForm . '_Clacod']) {
                                    $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT']
                                            . $_POST[$this->nameForm . '_Clacod'] . "'";
                                }
                            }
                            //proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $anapro_rec['VERSIONE_T'], $where);
                        }
                        break;
                    case $this->nameForm . '_ConfermaTermineNew':
                        $Arcite_rec = $this->recordTrasmissioneSelezionato;
                        $Arcite_rec['ITETERMINE'] = $_POST[$this->nameForm . '_termineNew'];
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $Arcite_rec, 'aggiorna ITEANN');
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;
                    case $this->nameForm . '_ConfermaAnnotazioneNew':
                        $Arcite_rec = $this->recordTrasmissioneSelezionato;
                        $Arcite_rec['ITEANN'] = $_POST[$this->nameForm . '_AnnotazioneNew'];
                        $this->updateRecord($this->PROT_DB, 'ARCITE', $Arcite_rec, 'aggiorna ITEANN');
                        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;
                    case $this->nameForm . '_Legami':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
                        proRic::proRicLegame($this->proLib, $this->nameForm, 'returnLegame', $this->PROT_DB, $anapro_rec);
                        break;
                    case $this->nameForm . '_Dest_cod_butt':
                        proRic::proRicDestinatari($this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_Uff_cod_butt':
                        itaLib::openForm('proSeleTrasmUffici');
                        /* @var $proSeleTrasmUffici proSeleTrasmUffici */
                        $proSeleTrasmUffici = itaModel::getInstance('proSeleTrasmUffici');
                        $proSeleTrasmUffici->setEvent('openform');
                        $proSeleTrasmUffici->setReturnModel($this->nameForm);
                        $proSeleTrasmUffici->setReturnId('');
                        $proSeleTrasmUffici->parseEvent();
                        break;
                    case $this->nameForm . '_sercod_butt':
                        proRic::proRicAnaservizi($this->nameForm);
                        break;
                    case $this->nameForm . '_Ricevuta':
                        $anaent_rec = $this->proLib->GetAnaent('2');
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $sql = "SELECT * FROM ANAPRO WHERE PRONUM={$this->currArcite['ITEPRO']} AND PROPAR='{$this->currArcite['ITEPAR']}'";
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                        $oggetto = $anaogg_rec['OGGOGG'];
                        if ($anapro_rec['PROPAR'] == 'A') {
                            $mittente = $anapro_rec['PRONOM'];
                            $indirizzo = $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
                            if ($anapro_rec['PROCAP'] != 0) {
                                $indirizzo .= ' ' . $anapro_rec['PROCAP'];
                            }
                            if (trim($indirizzo) != '') {
                                $mittente .= ' - ' . $indirizzo;
                            }
                            $parameters = array("Sql" => $sql, "Ente" => $anaent_rec['ENTDE1'], "Oggetto" => $oggetto, "Mittente" => $mittente);
                            $itaJR->runSQLReportPDF($this->PROT_DB, 'proRicevuta', $parameters);
                        } else if ($anapro_rec['PROPAR'] == 'P') {
                            $destinatario = $anapro_rec['PRONOM'];
                            $indirizzo = $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
                            if ($anapro_rec['PROCAP'] != 0) {
                                $indirizzo .= ' ' . $anapro_rec['PROCAP'];
                            }
                            if (trim($indirizzo) != '') {
                                $destinatario .= ' - ' . $indirizzo;
                            }
                            $parameters = array("Sql" => $sql, "Ente" => $anaent_rec['ENTDE1'], "Oggetto" => $oggetto, "Destinatario" => $destinatario);
                            $itaJR->runSQLReportPDF($this->PROT_DB, 'proRicevutaPartenza', $parameters);
                        }
                        break;
                    case $this->nameForm . '_GestPratica':
                        $this->ApriGestioneFascicolo();
                        break;
                    case $this->nameForm . '_VaiAllaFirma':
                        $this->VaiAllaFirma();
                        break;
                    case $this->nameForm . '_EsprimiParere':
                        $this->EsprimiParere();
                        break;

                    case $this->nameForm . '_Mail':
                        $this->inviaMail(true);
                        break;

                    case $this->nameForm . '_Esporta':
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        $anaent_41 = $this->proLib->GetAnaent('41');
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        // FATTURAPA
//                        if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
//                            /* @var $proLibSdi proLibSdi */
//                            $proLibSdi = new proLibSdi();
//                            $ret = $proLibSdi->ExportArrivoSDI($anapro_rec);
//                            if (!$ret) {
//                                Out::msgStop("Attenzione", "Errore in Esportazione File della Fattura Elettronica:<br>" . $proLibSdi->getErrMessage());
//                            } else {
//                                Out::msgInfo("Esportazione", "Esportazione File della Fattura Elettronica terminata con successo.");
//                            }
//                        }
                        // Nuovo export FatturaPA
                        $proLibSdi = new proLibSdi();
                        $retStatus = $proLibSdi->AllegatiSDI2Repository($anapro_rec);
                        if ($retStatus['ESPORTAZIONE']) {
                            $OutMsg = 'msgInfo';
                        } else {
                            $OutMsg = 'msgStop';
                        }
                        Out::$OutMsg($retStatus['RISULTATO'], $retStatus['MESSAGGIO']);

                        // REGISTRO GIORNALIERO
                        if ($anapro_rec['PROCODTIPODOC'] == $anaent_41['ENTVAL']) {
                            if (!$this->proLibGiornaliero->ExportFileRegistrioGiornaliero($anapro_rec)) {
                                Out::msgStop("Salvataggio Registrio Giornaliero", $this->proLibGiornaliero->getErrMessage());
                            } else {
                                Out::msgInfo("Esportazione", "Esportazione File Firmatio Registrio del Protocollo Giornaliero terminata con successo.");
                            }
                        }
                        break;

                    case $this->nameForm . '_Riscontro':
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        $anaent_39 = $this->proLib->GetAnaent('39');
                        $anaent_45 = $this->proLib->GetAnaent('45');
                        $anaent_49 = $this->proLib->GetAnaent('49');
                        $anaent_55 = $this->proLib->GetAnaent('55');
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        if (($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] || $anapro_rec['PROCODTIPODOC'] == $anaent_45['ENTDE5']) && $anapro_rec['PROPAR'] == 'A' && $anapro_rec['PROCODTIPODOC']) {
                            if ($anaent_39['ENTDE2'] != '' && $anaent_39['ENTDE2'] != '3') {
                                Out::msgInfo("Attenzione", "Il riscontro sulle Fatture Elettroniche non è abilitato per questo programma.");
                                break;
                            }

                            /* Se EFAA e attivo Riscontri solo su EFAS: */
                            $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                            if ($anaent_49['ENTDE3'] && $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                                if ($this->proLib->CaricaElencoEFASCollegati($anapro_rec)) {
                                    proRic::proRicEFASCollegati($this->nameForm, $anapro_rec);
                                    break;
                                }
                            }

                            //Rilegge anapro prima di passarlo, o passa il rowid e ci pensa openRiscontroFattura ad aprire il record?
                            $anapro_pretab = $this->proLib->checkRiscontro(substr($anapro_rec['PRONUM'], 0, 4), substr($anapro_rec['PRONUM'], 4), $anapro_rec['PROPAR']);
                            if ($anapro_pretab) {
                                Out::msgQuestion("Riscontro.", "Risultano già collegati dei riscontri a questa Fattura Elettronica.<br>  Vuoi caricare un altro riscontro?", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaRiscontroFattura', 'model' => $this->nameForm),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaRiscontroFattura', 'model' => $this->nameForm),
                                        )
                                );
                            } else {
                                $proLibSdi = new proLibSdi();
                                $ret = $proLibSdi->openRiscontroFattura($anapro_rec);
                                if (!$ret) {
                                    Out::msgStop("Attenzione", $proLibSdi->getErrMessage());
                                }
                            }
                            break;
                        }
                        /* Riscontro Protocollo Da proGestIter */
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        $arrBottoni = array();
                        $arrBottoni['F8-Annulla'] = array('id' => $this->nameForm . '_nessunRiscontro', 'model' => $this->nameForm, 'shortCut' => "f8");
                        $arrBottoni['F9-Doc.Formale'] = array('id' => $this->nameForm . '_RiscontroDocFormale', 'model' => $this->nameForm, 'shortCut' => "f9");

                        switch ($profilo['PROT_ABILITATI']) {
                            case '1':
                                $arrBottoni['F5-Arrivo'] = array('id' => $this->nameForm . '_RiscontroArrivo', 'model' => $this->nameForm, 'shortCut' => "f5");
                                break;
                            case '2':
                                $arrBottoni['F8-Partenza'] = array('id' => $this->nameForm . '_RiscontroPartenza', 'model' => $this->nameForm, 'shortCut' => "f8");
                                break;
                            case '3':
                                break;

                            default:
                                $arrBottoni['F5-Arrivo'] = array('id' => $this->nameForm . '_RiscontroArrivo', 'model' => $this->nameForm, 'shortCut' => "f5");
                                $arrBottoni['F8-Partenza'] = array('id' => $this->nameForm . '_RiscontroPartenza', 'model' => $this->nameForm, 'shortCut' => "f8");
                                break;
                        }
                        /*
                         * Controllo se attivare doc alla firma
                         */
                        if ($anaent_55['ENTDE2']) {
                            $arrBottoni['Documento Alla Firma'] = array('id' => $this->nameForm . '_RiscontroDocumento',
                                'model' => $this->nameForm);
                        }

                        Out::msgQuestion("Seleziona il tipo di Protocollo.", "Che tipo di Protocollo vuoi creare?", $arrBottoni);
                        break;

                    case $this->nameForm . '_RiscontroDocumento':
                        $Bottoni['Partenza'] = array('id' => $this->nameForm . '_RiscontroDocumentoPartenza',
                            'model' => $this->nameForm);
                        $Bottoni['Doc. Formale'] = array('id' => $this->nameForm . '_RiscontroDocumentoDocFormale',
                            'model' => $this->nameForm);
                        Out::msgQuestion("Documento alla Firma", "Che tipo di documento vuoi predisporre?", $Bottoni);
                        break;

                    case $this->nameForm . '_RiscontroDocumentoPartenza':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAttoRiscontro($anapro_rec, 'P');
                        break;
                    case $this->nameForm . '_RiscontroDocumentoDocFormale':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAttoRiscontro($anapro_rec, 'C');
                        break;

                    case $this->nameForm . '_ConfermaRiscontroFattura':
                        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        $proLibSdi = new proLibSdi();
                        $ret = $proLibSdi->openRiscontroFattura($anapro_rec);
                        if (!$ret) {
                            Out::msgStop("Attenzione", $proLibSdi->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_VisualizzaProtocollo':
                        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                        if (!$anaproctr_rec) {
                            Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
                            break;
                        }
                        $model = 'proArri';
                        $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
                        $_POST['event'] = 'openform';
                        $_POST['proGest_ANAPRO']['ROWID'] = $anaproctr_rec['ROWID'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
//                        itaLib::openForm($model);
//                        /* @var $proArri proArri */
//                        $proArri = itaModel::getInstance($model);
//                        $proArri->setEvent('openform');
//                        $proArri->setReturnModel($this->nameForm);
//                        $proArri->setReturnId('');
//                        $proArri->parseEvent();
//                        $proArri->Modifica($anaproctr_rec['ROWID']);
                        break;

                    case $this->nameForm . '_VisualizzaDocumentale':
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
                        $retCtrAccess = $this->ControlloAccessibilitaAtto($Indice_rec['ROWID']);
                        if (!$retCtrAccess) {
                            Out::msgInfo("Attenzione", "Atto non accessibile");
                            break;
                        }
                        $segLibDocumenti = new segLibDocumenti();
                        if (!$segLibDocumenti->ApriAtto($this->nameForm, $Indice_rec)) {
                            Out::msgStop("Attenzione", $segLibDocumenti->getErrMessage());
                        }
                        break;

                    /* RISCONTRO DA proGestIter */
                    case $this->nameForm . '_RiscontroPartenza':
                        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        if (!$this->proLib->ApriRiscontro($Anapro_rec, 'P')) {
                            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
                        }
                        break;
                    case $this->nameForm . '_RiscontroArrivo':
                        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        if (!$this->proLib->ApriRiscontro($Anapro_rec, 'A')) {
                            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
                        }
                        break;
                    case $this->nameForm . '_RiscontroDocFormale':
                        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        if (!$this->proLib->ApriRiscontro($Anapro_rec, 'C')) {
                            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
                        }
                        break;
                    /* Inizio WS Kibernetes */
                    case $this->nameForm . '_CaricaFattura':
                        Out::msgQuestion("Caricamento.", "Confermare il caricamento in contabilità?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCaricaFattura', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCaricaFattura', 'model' => $this->nameForm),
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCaricaFattura':
                        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                        if ($Anapro_rec) {
                            include_once ITA_BASE_PATH . '/apps/Protocollo/proKibernetes.class.php';
                            $proKibernetes = new proKibernetes();
                            $Protocollo = array();
                            $Protocollo = array('AnnoProtocollo' => substr($Anapro_rec['PRONUM'], 0, 4), 'NumeroProtocollo' => substr($Anapro_rec['PRONUM'], 4), 'TipoProtocollo' => $Anapro_rec['PROPAR']);
                            $RetCarica = $proKibernetes->CaricaFatturaWithArgs_11($Protocollo);
                            /* Elaboro messaggi di ritorno */
                            $RisultatoMessaggio = $RetCarica['Messaggio'];
                            if ($RetCarica['Risultati']) {
                                $RisultatoMessaggio .= "<br>Fatture caricate:<br> - ";
                                $RisultatoMessaggio .= implode("<br> - ", $RetCarica['Risultati']);
                            }
                            if ($RetCarica['Anomalia']) {
                                $RisultatoMessaggio .= "<br><br>Anomalie:<br>";
                                $RisultatoMessaggio .= $RetCarica['Anomalia'];
                            }
                            if ($RetCarica['Status'] < 0) {
                                Out::msgStop('Caricamento', $RisultatoMessaggio);
                            } else {
                                Out::msgInfo('Caricamento', $RisultatoMessaggio);
                            }
                            $this->caricaDatiAggiuntivi($Anapro_rec);
                            //Out::msgInfo('Ritorno', print_r($RetCarica, true));
                        }
                        break;
                    /* Fine WS Kibernetes */
                    case $this->nameForm . '_VisualizzaPasso':
                        /*
                         * Candidata ad Helper?
                         */
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praWorkflowHelper.class.php';
                        $wfHeper = new praWorkflowHelper();
                        $ret = $wfHeper->apriGestionePassoDaAssegnazione($this, $this->currArcite);
                        if($ret['ESITO'] != 0){
                            Out::msgStop('Errore', $ret['MESSAGGIO']);
                        }
                        break;
                    case $this->nameForm . '_VisualizzaPasso_old':
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                        $praLib = new praLib();
                        $propas_rec = $praLib->GetPropas($this->currArcite['ITEPRO'], "paspro", false, $this->currArcite['ITEPAR']);
                        $_POST = array();
                        $model = 'praPasso';
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent('openform');
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST['rowid'] = $propas_rec['ROWID'];
                        $_POST['daTrasmissioni'] = true;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_VisualizzaPratica':
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                        $praLib = new praLib();
                        $propas_rec = $praLib->GetPropas($this->currArcite['ITEPRO'], "paspro", false, $this->currArcite['ITEPAR']);
                        $proges_rec = $praLib->GetProges($propas_rec['PRONUM']);
                        $model = 'praGestElenco';
                        itaLib::openForm($model);
                        /* @var $objModel praGest */
                        $objModel = itaModel::getInstance($model);
                        $_POST = array();
                        $objModel->setEvent('openform');
                        $_POST['daTrasmissioni'] = true;
                        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                        $_POST['perms'] = $this->perms;
                        $objModel->parseEvent();
                        break;

                    case $this->nameForm . '_AggiornaFatturaHalley':
                        $this->AggiornaFatturaHalley();
                        break;

                    case $this->nameForm . '_TornaAProtocollo':

                        break;

                    case $this->nameForm . '_ProtocollaDocumentale':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $anaent_rec = $this->proLib->GetAnaent('55');

                        /* Lettura Arcite */
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
                        /*
                         * Controllo se gia protocollato
                         */
                        $ProDocProt_rec = $this->proLib->GetProDocProt($arcite_rec['ITEPRO'], 'sorgnum', $arcite_rec['ITEPAR']);
                        if ($ProDocProt_rec) {
                            Out::msgInfo("Attenzione", "Il documento risulta già essere protocollato con numero: " . $ProDocProt_rec['DESTNUM'] . ' ' . $ProDocProt_rec['DESTTIP']);
                            return;
                        }

                        if (!$proLibDocumentale->ProtocollaDocumentale($Indice_rec['IDELIB'])) {
                            Out::msgInfo('Protocollazione', $proLibDocumentale->getErrMessage());
                            return;
                        }
                        /* Chiusura iter dopo la protocollazione */
                        if ($anaent_rec['ENTDE3'] != '1') {
                            $this->chiudiIter('DOCUMENTO FIRMATO E PREDISPOSTO PER IL PROTOCOLLO', false);
                            $this->openDettaglio($arcite_rec['ROWID']);
                        }
                        $Anapro_rec = $proLibDocumentale->getLastAnapro_rec();
                        if (!$Anapro_rec) {
                            Out::msgStop("Attenzione", "Errore in lettura Protocollo Creato.");
                            return false;
                        }
                        /* Rimozione allegato dalla firma */
                        if (!$this->TogliDallaFirmaDocumenti($Anapro_rec, true, false)) {
                            return false;
                        }
                        /*
                         * Cerco iter del firmatario non annullato.
                         */

                        $AnadesFir_rec = $this->proLib->GetAnades($Anapro_rec['PRONUM'], 'codice', false, $Anapro_rec['PROPAR'], 'M');
                        $sql = "SELECT * FROM ARCITE "
                                . " WHERE ITEPRO = " . $Anapro_rec['PRONUM'] . " AND "
                                . " ITEPAR = '" . $Anapro_rec['PROPAR'] . "' AND "
                                . " ITETIP = '" . proIter::ITETIP_ALLAFIRMA . "' "
                                . " AND ITEANNULLATO = '0' AND ITEDES = '" . $AnadesFir_rec['DESCOD'] . "' AND ITEUFF = '" . $AnadesFir_rec['DESCUF'] . "'";
                        $Arcite_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

                        /*
                         * Dettaglio 
                         */
                        $this->openDettaglio($Arcite_rec['ROWID']);

                        /*
                         * Sposto fascicoli:
                         */
                        if (!$this->proLibFascicolo->SpostaFascicoli($this, $Indice_rec['INDPRO'], $Indice_rec['INDPAR'], $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                            Out::msgStop("Informazione", "Fascicolazione automatica non riuscita, terminare l'operzione manualmente. " . $this->proLibFascicolo->getErrMessage());
                        }
                        /*
                         * Rendo Indice Definitivo
                         */
                        $Indice_rec['INDDEF'] = 1;
                        $update_Info = "Rendo atto predisposto per la firma definitivo. " . $Indice_rec['IDELIB'];
                        if (!$this->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
                            return false;
                        }
                        /*
                         * Dettaglio ed invio mail
                         */
                        $this->openDettaglio($Arcite_rec['ROWID']);
                        $this->inviaMail(true);
                        $this->chiudiIter("Firme effettuate correttamente.", false);
                        break;

                    case $this->nameForm . '_salvaTitolario':
                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
                        if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {
                            Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                            return;
                        } else {
                            Out::msgBlock('', 2000, false, "Titolario aggiornato correttamente.");
                        }
                        break;

                    case $this->nameForm . '_VisualizzaFascicolo':

                        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
                        $chiaveFascicolo = $anapro_rec['PROFASKEY'];
                        $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $chiaveFascicolo);
                        if (!$anapro_check) {
                            Out::msgStop("Attenzione", "Non hai accesso al Fascicolo.");
                            return;
                        }
                        $model = 'proGestPratica';
                        itaLib::openForm($model);
                        $proGestPratica = itaModel::getInstance($model, $model);
                        $proGestPratica->setEvent('openform');
                        $proGestPratica->setOpenGeskey($chiaveFascicolo);
                        $proGestPratica->parseEvent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_cod':
                        $this->scaricaDest($_POST[$this->nameForm . '_Dest_cod']);
                        break;
                    case $this->nameForm . '_Uff_cod':
                        $this->scaricaDaCodiceUfficio($_POST[$this->nameForm . '_Uff_cod']);
                        break;
                    case $this->nameForm . '_sercod':
                        $this->scaricaServizio($_POST[$this->nameForm . '_sercod']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAPRO[PROCAT]':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['BLOC_TITOLARIO'] == '1') {
                            $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice);
                            if (!$titolario_tab) {
                                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
                                Out::valore($this->nameForm . '_Clacod', '');
                                Out::valore($this->nameForm . '_Fascod', '');
                                Out::valore($this->nameForm . '_DescTitolario', '');
                            } else {
                                $this->checkCatFiltrato($codice);
                            }
                        } else {
                            $this->DecodAnacat($versione_t, $codice);
                        }
                        break;
                    case $this->nameForm . '_Clacod':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            if ($profilo['BLOC_TITOLARIO'] == '1') {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice1, $codice2);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_DescTitolario', '');
                                    $this->checkCatFiltrato($codice1);
                                } else {
                                    $this->checkClaFiltrato($codice1, $codice2);
                                }
                            } else {
                                $this->DecodAnacla($versione_t, $codice1 . $codice2);
                            }
                        } else {
                            $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            if ($profilo['BLOC_TITOLARIO'] == '1') {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_DescTitolario', '');
                                } else {
                                    $this->checkCatFiltrato($codice);
                                }
                            } else {
                                $this->DecodAnacat($versione_t, $codice);
                            }
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            if ($profilo['BLOC_TITOLARIO'] == '1') {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice1, $codice2, $codice3);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_DescTitolario', '');
                                    $this->checkClaFiltrato($codice1, $codice2);
                                } else {
                                    $this->DecodAnafas($versione_t, $codice1 . $codice2 . $codice3);
                                }
                            } else {
                                $this->DecodAnafas($versione_t, $codice1 . $codice2 . $codice3);
                            }
                        } else {
                            $codice = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            if ($profilo['BLOC_TITOLARIO'] == '1') {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice1, $codice2);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_DescTitolario', '');
                                } else {
                                    $this->checkClaFiltrato($codice1, $codice2);
                                }
                            } else {
                                $this->DecodAnacla($versione_t, $codice1 . $codice2);
                            }
                        }
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_nome':
                        /* new suggest */
                        $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAMED WHERE MEDANN<>1 AND $filtroUff AND " . $where;
                        $anamed_tab = $this->proLib->getGenericTab($sql);
                        if (count($anamed_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anamed_tab as $anamed_rec) {
                                itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_Dest_cod" => $anamed_rec['MEDCOD']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_Uff_des':
                        /* new suggest */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAUFF WHERE " . $where;
                        $anauff_tab = $this->proLib->getGenericTab($sql);
                        if (count($anauff_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anauff_tab as $anauff_rec) {
                                itaSuggest::addSuggest($anauff_rec['UFFDES'], array($this->nameForm . "_Uff_cod" => $anauff_rec['UFFCOD']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_serdes':
                        /* new suggest */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('SERDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANASERVIZI WHERE " . $where;
                        $anaservizi_tab = $this->proLib->getGenericTab($sql);
                        if (count($anaservizi_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anaservizi_tab as $anaservizi_rec) {
                                itaSuggest::addSuggest($anaservizi_rec['SERDES'], array($this->nameForm . "_sercod" => $anaservizi_rec['SERCOD']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "UPDATE_NOTE_ANAPRO":
                        if ($_POST["msgData"]["PRONUM"] && $_POST["msgData"]["PROPAR"] && $_POST["msgData"]["PRONUM"] === $this->currArcite['ITEPRO'] && $_POST["msgData"]["PROPAR"] === $this->currArcite['ITEPAR']) {
                            $this->openDettaglio($this->currArcite['ROWID']);
                        }
                        break;
                }
                break;
            case 'returnProDettNote':
                $dati = array(
                    'OGGETTO' => $_POST['oggetto'],
                    'TESTO' => $_POST['testo'],
                    'CLASSE' => proNoteManager::NOTE_CLASS_ITER,
                    'CHIAVE' => $this->formData["{$this->nameForm}_ITEKEY"]
                );
                $tipoNotifica = "CARICATA";
                if ($_POST['NON_AGGIORNA'] !== true) {
                    if ($_POST['NOTE_ROWID'] === '') {
                        $tipoNotifica = "INSERITA";
                        $this->noteManager->aggiungiNota($dati);
                    } else {
                        $tipoNotifica = "MODIFICATA";
                        $this->noteManager->aggiornaNota($_POST['NOTE_ROWID'], $dati);
                    }
                    $this->noteManager->salvaNote();
                }
                $OggTesto = $_POST['oggetto'] . "\n" . $_POST['testo'];
                foreach ($_POST['destinatari'] as $destinatario) {
                    $oggetto = "$tipoNotifica UNA NOTA AL PROTOCOLLO NUM. " . (int) substr($this->formData[$this->nameForm . '_ITEKEY'], 4, 10) . " / " . substr($this->formData[$this->nameForm . '_ITEKEY'], 0, 4);
                    $this->inserisciNotifica($oggetto, $OggTesto, $destinatario);
                }
                $this->caricaNote();
                break;

            case 'returnTitolario':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_Clacod', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_Fascod', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_DescTitolario', $retTitolario['DECOD_DESCR']);
                break;
            case 'returnTitolarioFiltrato':
                $cat = $_POST['rowData']['CATCOD'];
                $cla = $_POST['rowData']['CLACOD'];
                $fas = $_POST['rowData']['FASCOD'];
                if ($cat) {
                    $anacat_rec = $this->proLib->GetAnacat('', $cat, 'codice');
                    Out::valore($this->nameForm . '_catdes', $anacat_rec['CATDES']);
                }
                if ($cla) {
                    $anacla_rec = $this->proLib->GetAnacla('', $cat . $cla, 'codice');
                    Out::valore($this->nameForm . '_clades', $anacla_rec['CLADE1'] . $anacla_rec['CLADE2']);
                }
                if ($fas) {
                    $anafas_rec = $this->proLib->GetAnafas('', $cat . $cla . $fas, 'fasccf');
                    Out::valore($this->nameForm . '_fasdes', $anafas_rec['FASDES']);
                }
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $cat);
                Out::valore($this->nameForm . '_Clacod', $cla);
                Out::valore($this->nameForm . '_Fascod', $fas);
                break;
            case 'returnUfficiPerDestinatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                $this->caricaDestinatari($this->appoggio, $anauff_rec['UFFCOD']);
                $this->appoggio = '';
                break;
            case 'returnDestinatari':
                /* INIZIO PATCH */
                TableView::showCol($this->gridDestinatari, 'DESGESADD');
                if ($this->currArcite['ITEGES'] == 0) {
                    TableView::hideCol($this->gridDestinatari, 'DESGESADD');
                }
                /* FINE PATCH */
                if ($_POST['retKey']) {
                    $rowid_sel = explode(",", $_POST['retKey']);
                }
                //---controllo che non ci siano nominativi doppi
                $rowid_anamed = array();
                $rowid_err = array();
                $fl_msg = false;
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr1 = explode('-', $rowids);
                    if (array_search($rowid_arr1[2], $rowid_anamed) === false) {
                        $rowid_anamed[] = $rowid_arr1[2];
                    } else {
                        $rowid_err[] = $rowid_arr1[2];
                    }
                }
                if ($rowid_err) {
                    $nomi = "";
                    foreach ($rowid_err as $rowid) {
                        $anamed_rec = $this->proLib->GetAnamed($rowid, 'rowid');
                        $nomi .= "\n" . $anamed_rec['MEDNOM'];
                    }
                    $fl_msg = true;
                    //Out::msgStop("Attenzione", "I seguenti nominativi risultano selezionati più volte:\n\r" . $nomi);
                }
                //----
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr = explode('-', $rowids);
                    //$anaservizi_rec = $this->proLib->getAnaservizi($rowid_arr[0], 'rowid');
                    $anauff_rec = $this->proLib->GetAnauff($rowid_arr[1], 'rowid');
                    $anamed_rec = $this->proLib->GetAnamed($rowid_arr[2], 'rowid');
                    //$anaruo_rec = $this->proLib->getAnaruoli($rowid_arr[3], 'rowid');
                    if (!$anamed_rec) {
                        continue;
                    }
                    $inserisci = true;
                    if (array_search($rowid_arr[2], $rowid_err) !== false) {
                        $inserisci = false;
                    }
                    foreach ($this->proArriDest as $value) {
                        if ($anamed_rec['MEDCOD'] == $value['DESCOD']) {
                            $inserisci = false;
                            break;
                        }
                    }
                    if ($inserisci == true) {
                        $this->caricaDestinatari($anamed_rec['MEDCOD'], $anauff_rec['UFFCOD']);
                    }
                    Out::valore($this->nameForm . '_Dest_cod', "");
                    Out::valore($this->nameForm . "_Dest_nome", "");
                }
                if ($fl_msg) {
                    Out::msgStop("Attenzione", "I seguenti nominativi risultano selezionati più volte:\n\r" . $nomi);
                }
                break;
            case 'returnLegame':
                $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $_POST['retKey']);
                if (!$anaproctr_rec) {
                    Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
                    break;
                }
                $model = 'proArri';
                $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
                $_POST['event'] = 'openform';
                $_POST['proGest_ANAPRO']['ROWID'] = $_POST['retKey'];
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnanauff':
                $this->scaricaDaCodiceUfficio($_POST['retKey'], 'rowid');
                break;

            case 'returnFromSeleTrasmUfficio':
                $TipoSelezione = $_POST['tipoSelezione'];
                $retUffici = $_POST['retUffici'];

                if ($TipoSelezione == 'Persona') {
                    foreach ($retUffici as $anauff_rec) {
                        if ($anauff_rec) {
                            $this->scaricaDaCodiceUfficio($anauff_rec['ROWID'], 'rowid');
                        }
                    }
                } else {
                    foreach ($retUffici as $anauff_rec) {
                        if ($anauff_rec) {
                            $this->scaricaUfficio($anauff_rec, true);
                        }
                    }
                }
                break;

            case 'returnanaservizi':
                $this->scaricaServizio($_POST['retKey'], 'rowid');
                break;
            case 'returntoDestinatariInterni':
                if (!array_key_exists($_POST['retKey'], $this->proIterDest)) {
                    $this->caricaDestinatari($_POST['retKey']);
                }
                break;
            case 'returnorgfas':
                $this->appoggio = array();
                $this->appoggio['ROWID'] = $_POST['retKey'];
                $sql = "SELECT PROGES.* FROM ANAORG LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY WHERE ANAORG.ROWID=" . $_POST['retKey'];
                $risultato = $this->proLib->getGenericTab($sql, false);
                $matrice = $this->proLibFascicolo->getAlberoFascicolo($risultato['GESNUM'], array('PROTOCOLLI' => ' OR 1<>1 '));
                proric::proRicAlberoFascicolo($this->nameForm, $matrice);
                break;

            case 'returnAlberoFascicolo':
                $pronumR = substr($_POST['retKey'], 4, 10);
                $proparR = substr($_POST['retKey'], 14);
                $this->appoggio = array();
                $this->appoggio['ROWID'] = $this->returnData['ROWID_ANAORG'];
                $fascicolo_rec = $this->proLib->GetAnaorg($this->appoggio['ROWID'], 'rowid');
                $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
                $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);

                if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {//
                    Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                    return;
                }
                if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $fascicolo_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $pronumR, $proparR)) {
                    Out::msgStop("Attenzione! Nuovo Fascicolo", $this->proLibFascicolo->getErrMessage());
                } else {
                    $this->openDettaglio($arcite_rec['ROWID']);
//                    Out::msgBlock('', 2000, false, "Nuovo fascicolo creato correttamente");
                }
                break;

            case 'returnPropas':
                $sql = "SELECT PROGES.GESNUM FROM ANAORG LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY WHERE ANAORG.ROWID=" . $this->appoggio['ROWID'];
                $proges_rec = $this->proLib->getGenericTab($sql, false);
                if (!$proges_rec) {
                    Out::msgStop("Fascicolazione Azione Pratica", "Errore di accesso alla pratica.");
                }
                $model = 'proPassoPratica';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $appoggioPost = $_POST;
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['listaAllegati'] = array();
                $_POST['perms'] = $this->perms;
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnMethod'] = 'returnProPassoPratica';
                $_POST[$model . '_title'] = 'Gestione Azione proveniente dalla pratica: ' . (int) substr($proges_rec['GESNUM'], 14, 6) . "/" . substr($proges_rec['GESNUM'], 10, 4);
                $datiInfo = "Caricamento Azione attività per la pratica " . (int) substr($proges_rec['GESNUM'], 14, 6) . "/" . substr($proges_rec['GESNUM'], 10, 4);
                if ($appoggioPost['retKey'] == '') {
                    $datiInfo .= '<br>Inserire i dati dell\'Azione prima di Aggiungere.';
                    $_POST['procedimento'] = $proges_rec['GESNUM'];
                    $_POST['modo'] = "add";
                } else {
                    $_POST['rowid'] = $appoggioPost['retKey'];
                    $_POST['modo'] = "edit";
                }
                $_POST[$model . '_fascicolaDocumento'] = array(
                    "PRONUM" => $this->formData[$this->nameForm . '_ANAPRO']['PRONUM'],
                    "PROPAR" => $this->formData[$this->nameForm . '_ANAPRO']['PROPAR']
                );
                $_POST['datiInfo'] = $datiInfo;
                $model();
                break;
            case 'returnProPassoPratica':
//                $propas_rec = $this->proLibPratica->GetPropas($_POST['keyPasso']);
                $this->passoSelezionato();
                break;
            case "returnFromSignAuth";
                if ($_POST['result'] === true) {
                    if ($_POST['returnAllegati']) {
                        foreach ($_POST['returnAllegati'] as $key => $allegato) {
                            if ($allegato['SIGNRESULT'] === 'OK') {
                                App::log('multi');
                                $this->salvaDocumentoFirmato($key, $allegato['OUTPUTFILEPATH'], $allegato['INPUTFILEPATH'], $allegato['FILENAMEFIRMATO']);
                            }
                        }
                    } else {
                        $this->salvaDocumentoFirmato($this->appoggio['ROWID'], $_POST['outputFilePath'], $_POST['inputFilePath'], $_POST['fileNameFirmato']);
                    }
                    Out::msgBlock('', 2000, true, "Firma Avvenuta con successo");
                    $this->caricaAllegati($this->currArcite['ITEPRO'], $this->currArcite['ITEPAR'], true);
                    if ($this->currArcite['ITEPAR'] == 'W') {
                        $this->chiudiIter("Firme effettuate correttamente.");
                    }
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;
            case 'returnMail':
                $destinatari = array();
                $destinatariKey = explode(',', $_POST['valori']['Destinatari']);
                $destMap = $_POST['valori']['DestinatariOriginari'];
                foreach ($destinatariKey as $key => $value) {
                    $destinatari[] = $destMap[$value];
                }
                if ($_POST['valori']['Inviata']) {
                    $valori = array(
                        'destMap' => $destinatari,
                        'Oggetto' => $_POST['valori']['Oggetto'],
                        'Corpo' => $_POST['valori']['Corpo'],
                        'allegati' => $_POST['allegati']
                    );
                    $ForzaDaMail = '';
                    if ($_POST['valori']['ForzaDaMail']) {
                        $ForzaDaMail = $_POST['valori']['ForzaDaMail'];
                    }
                    $result = $this->proLibMail->servizioInvioMail($this, $valori, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR'], array(), $this->appoggio['proArriDest'], $this->appoggio['proAltriDestinatari'], $ForzaDaMail);
                    if (!$result) {
                        Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                        break;
                    }

                    $this->chiudiIter("Firme effettuate correttamente.", false);

                    $arcite_pre = $this->proLib->GetArcite($this->currArcite['ITEPRE'], 'itekey');
                    if ($arcite_pre['ITEDES'] != $this->currArcite['ITEDES']) {
                        $oggetto = "Invio mail ai destinatari per il protocollo: " . (int) substr($this->currArcite['ITEPRO'], 4) . " / " . substr($this->currArcite['ITEPRO'], 0, 4) . " - " . $this->currArcite['ITEPAR'];
                        $testo = "Invio avvenuto con successo ai destinatari del protocollo.";
                        $utente = $this->proLib->getLoginDaMedcod($this->currArcite['ITEANT']);
                        if ($utente) {
                            $this->inserisciNotifica($oggetto, $testo, $utente['UTELOG']);
                        }
                    }
                    $this->openDettaglio($this->currArcite['ROWID']);
                }
                break;

            case 'returnMultiSelezioneFascicolo':
                $FascicoliSelezionati = $_POST['FASCICOLI_SELEZIONATI'];
                $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                if (!$this->proLibFascicolo->FascicolaProtInElencoFascicoli($this, $Anapro_rec, $FascicoliSelezionati)) {
                    Out::msgStop('Attenzione', 'Errore in fascicolazione.<br>' . $this->proLibFascicolo->getErrMessage());
                    $this->openDettaglio($this->currArcite['ROWID']);
                    break;
                }
                Out::msgInfo('Fascicolazione', 'Protocollo fascicolato correttamente.');
                $this->openDettaglio($this->currArcite['ROWID']);
                break;

            case 'returnEFAS':
                $RowData = $_POST['rowData'];
                //$this->Modifica($RowData['ROWID']);
                $ret = $this->proLibSdi->openRiscontroFattura($RowData);
                if (!$ret) {
                    Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                }
                break;

            case 'returnFromSignAuthDocumentale':
                $result = $_POST['result'];
                if ($result == 'cancel') {
                    break;
                }
                $this->FirmaDocumentale($result);
                break;

            case 'returnConfermaHalley':
                $ExtraParam = array();
                $ExtraParam['OGGETTOFATTURA'] = $_POST['OggettoSelezionato'];
                $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
                if ($Anapro_rec) {
                    $proHalley = new proHalley();
                    if (!$proHalley->AggiornaDatiContabilita($Anapro_rec, $ExtraParam)) {
                        Out::msgStop('Errore', $proHalley->getErrMessage());
                        break;
                    }
                    $msgLog = implode("<br> - ", $proHalley->getMsgLog());
                    //$messaggio = "Aggiornamento fatture effettuato con successo.<br>Riepilogo Operazioni:<br> - " . $msgLog;
                    //Out::msgInfo("Aggiornamento fatture Elettroniche", $messaggio);
                    //Out::msgInfo("Aggiornamento fatture Elettroniche", $msgInfoLog, '400', '600');
                    if ($proHalley->getRecUpdateLog()) {
                        $bottoni = array();
                        if (!$proHalley->getErrLog()) {
                            $bottoni['Continua'] = array('id' => $this->nameForm . '_ContinuaHalley', 'model' => $this->nameForm);
                        } else {
                            $bottoni['Torna a Trasmissione'] = array('id' => $this->nameForm . '_TornaTrasmissione', 'model' => $this->nameForm);
                        }
                        $msgInfoLog = $proHalley->GetInfoUpdatedRec();
                        Out::msgQuestion("Aggiornamento fatture Elettroniche", $msgInfoLog, $bottoni, 'auto', 'auto', 'false');
                        if (!$proHalley->getErrLog()) {
                            $this->chiudiIter('Aggiornato in contabilita halley.');
                            Out::desktopTabSelect('ita-home');
                            Out::desktopTabSelect('proElencoIterPortlet');
                            TableView::clearToolbar('proElencoIterPortlet_gridIter');
                            $this->ricaricaPortlet();
                        }
                    }
                    if ($proHalley->getErrLog()) {
                        $messaggio = '<div style="display: inline-block; margin-left: 20px; margin-top: -15px;">';
                        $messaggio .= "Non è stato possibile aggiornare alcune fatture per i seguenti motivi:<br>" . implode("<br> - ", $proHalley->getErrLog()) . "<br><br><u>Occorre procedere manualmente con la registrazione</u>.</div>";
                        Out::msgStop("ATTENZIONE ANOMALIE!", $messaggio);
                    }
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_proIterAlle');
        App::$utente->removeKey($this->nameForm . '_proIterDest');
        App::$utente->removeKey($this->nameForm . '_proIterDestProt');
        App::$utente->removeKey($this->nameForm . '_Destinatario');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_proIter');
        App::$utente->removeKey($this->nameForm . '_tabella');
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_codiceDest');
        App::$utente->removeKey($this->nameForm . '_recordTrasmissioneSelezionato');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_currArcite');
        App::$utente->removeKey($this->nameForm . '_elencoDestinatari');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_datiAggiuntivi');
        App::$utente->removeKey($this->nameForm . '_returnData');
        App::$utente->removeKey($this->nameForm . '_ElencoFascicoli');
        App::$utente->removeKey($this->nameForm . '_prmsEditNote');
        App::$utente->removeKey($this->nameForm . '_praSubPassoAllegatiSimple');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_tipoTrasm', 1, "A", "0", "Arrivi");
        Out::select($this->nameForm . '_tipoTrasm', 1, "C", "0", "Documenti Formali");
        Out::select($this->nameForm . '_tipoTrasm', 1, "", "1", "Arrivi/Documenti Formali");

        $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest);
        Out::select($this->nameForm . '_selectUffici', 1, "*", "0", '<p style="color:green;">Tutti</p>');
        foreach ($uffdes_tab as $uffdes_rec) {
            $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
            Out::select($this->nameForm . '_selectUffici', 1, $uffdes_rec['UFFCOD'], '0', substr($anauff_rec['UFFDES'], 0, 30));
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_statoAcquisizione');
        Out::hide($this->nameForm . '_divBottoniAcquisisci');
        Out::hide($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_AnnullaInvio');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Riapri');
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_Procedimento');
        Out::hide($this->nameForm . '_Rifiuta');
        Out::hide($this->nameForm . '_NoRifiuta');
        Out::hide($this->nameForm . '_InCarico');
        Out::hide($this->nameForm . '_InCaricoAcquisizione');
        Out::hide($this->nameForm . '_Evidenza');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Legami');
        Out::hide($this->nameForm . '_PresaVisione');
        Out::hide($this->nameForm . '_statoTrasmissione');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Ricevuta');
        Out::hide($this->nameForm . '_GestPratica');
        Out::hide($this->nameForm . '_VaiAllaFirma');
        Out::hide($this->nameForm . '_ProtocollaDocumentale');
        Out::hide($this->nameForm . '_EsprimiParere');
        Out::hide($this->nameForm . '_Mail');
        Out::hide($this->nameForm . '_Riscontro');
        Out::hide($this->nameForm . '_Esporta');
        Out::hide($this->nameForm . '_VisualizzaProtocollo');
        Out::hide($this->nameForm . '_VisualizzaDocumentale');
        Out::hide($this->nameForm . '_VisualizzaPasso');
        Out::hide($this->nameForm . '_VisualizzaPratica');
        Out::hide($this->nameForm . '_sottofascicolo_field');
        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_ANAPRO[PROCAT]_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_DescTitolario');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Fascod_field');
        }
        Out::hide($this->nameForm . '_InviaInVisione'); /* INIZIO PATCH */
        Out::hide($this->nameForm . '_CaricaFattura'); /* WS Kibernetes */
        Out::hide($this->nameForm . '_AggiungiFascicolo'); /* Aggiunta Fascicolo da SelezioneFascicoli */
        Out::hide($this->nameForm . '_addFascicolo');
        Out::hide($this->nameForm . '_divInfoDatiProto');
        Out::hide($this->nameForm . '_AggiornaFatturaHalley'); /* PATCH HALLEY */
        Out::hide($this->nameForm . '_salvaTitolario'); /* PATCH HALLEY */
        Out::hideTab($this->nameForm . '_paneAllegatiPasso');
        Out::hide($this->nameForm . '_VisualizzaFascicolo');
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->nameForm . '_divRicerca');
        Out::clearFields($this->nameForm, $this->nameForm . '_divGestione');
        Out::valore($this->nameForm . "_tipoTrasm", '');
        $this->recordTrasmissioneSelezionato = null;
        $this->currArcite = null;
    }

    private function OpenRicerca() {
        Out::show($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
//        Out::hide($this->nameForm . '_AltraRicerca');

        Out::setFocus('', $this->nameForm . '_dalPeriodo');
    }

    private function elencaRisultato() {
        $this->Nascondi();
        $this->caricaRisultatiGriglia();
        $this->CaricaGriglia($this->gridRisultatoIter, $this->tabella, '1', 20);
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
    }

    public function openDettaglio($rowid) {
        Out::removeElement($this->nameForm . '_AggiungiFascicolo'); /* Aggiunta Fascicolo da SelezioneFascicoli */
        $this->proIterAlle = array();
        $this->proIterDest = array();
        $this->noteManager = null;
        $this->recordTrasmissioneSelezionato = null;
        TableView::clearGrid($this->gridDestinatari);
        TableView::clearGrid($this->gridFascicoli);

        Out::valore($this->nameForm . '_Annotazioni', '');
        $this->Nascondi();
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_statoTrasmissione');
        $this->currArcite = $arcite_rec = $this->proLib->GetArcite($rowid, 'rowid'); // ARCITE CORRENTE
        /*
         * Nuovo controllo:
         * Non permetto modifiche se trasmissione è annullata.
         */
        if ($arcite_rec['ITEANNULLATO']) {
            $this->visualizzazione = true;
            Out::msgInfo("Attenzione", "Questa trasmissione sembra essere annullata. Non è possibile gestirla.");
        }

        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $anapro_rec['ROWID']);
        $TipoProt = $anapro_rec['PROPAR'];
        if ($TipoProt == 'A' || $TipoProt == 'P' || $TipoProt == 'C') {
            if ($anaproctr_rec) {
                Out::show($this->nameForm . '_VisualizzaProtocollo');
            }
        }
        if ($TipoProt == 'I') {
            if ($anaproctr_rec) {
                Out::show($this->nameForm . '_VisualizzaDocumentale');
            }
        }
        if ($TipoProt == 'F' || $TipoProt == 'N') {
            if ($anaproctr_rec) {
                Out::show($this->nameForm . '_VisualizzaFascicolo');
            }
        }
        if ($TipoProt == 'W') {
            if ($anaproctr_rec) {
                Out::show($this->nameForm . '_VisualizzaPasso');
                //
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                $praLib = new praLib();
                $propas_rec = $praLib->GetPropas($anaproctr_rec['PRONUM'], "paspro", false, $anaproctr_rec['PROPAR']);
                if ($propas_rec['PROVISIBILITA'] != "soloPasso") {
                    Out::show($this->nameForm . '_VisualizzaPratica');
                }
            }
        }

        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_41 = $this->proLib->GetAnaent('41');
        $anaent_57 = $this->proLib->GetAnaent('57');

        if ($anapro_rec['PROPAR'] == 'A' || $anapro_rec['PROPAR'] == 'P') {
            Out::show($this->nameForm . '_Ricevuta');
        }

        $arcite_pre = $this->proLib->GetArcite($arcite_rec['ITEPRE'], 'itekey');
        $arcite_sus = $this->proLib->GetArciteSus($arcite_rec['ITEKEY']);
        $this->noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $arcite_rec['ITEPRO'], "PROPAR" => $arcite_rec['ITEPAR']));
        /*
         * Decodifica Trasmissioni
         */
        $this->DecodificaTrasmissione($arcite_rec, $arcite_pre, $anapro_rec);


        if ($anapro_rec['PROPRE'] > 0) {
            Out::show($this->nameForm . '_Legami');
        } else {
            $anno = substr($anapro_rec['PRONUM'], 0, 4);
            $codice = intval(substr($anapro_rec['PRONUM'], 4));
            if ($this->proLib->checkRiscontro($anno, $codice, $anapro_rec['PROPAR'])) {
                Out::show($this->nameForm . '_Legami');
            } else {
                Out::hide($this->nameForm . '_Legami');
            }
        }
        /*
         *  Nascondo div Indice e Visualizzo div Fascicolo come Default
         */
        Out::show($this->nameForm . "_divDatiFascicolo");
        Out::hide($this->nameForm . "_divDatiIndice");
        Out::html($this->nameForm . '_Pronum_lbl', 'N.Prot');
        $this->tipoProt = $arcite_rec['ITEPAR'];
        if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
            $this->caricaGrigliePartenza($anapro_rec);
        } else if ($this->tipoProt == 'I') {
            $this->caricaGrigliePartenza($anapro_rec);
            // Out::hide($this->nameForm . "_divSoggetti");
            Out::hide($this->nameForm . "_divMittentiProt");
            Out::show($this->nameForm . "_divDestProt");
            Out::show($this->nameForm . "_divSoggetti");
            Out::hide($this->nameForm . "_divDatiFascicolo");

            //Carico Indice e Visualizzo
            $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
            Out::show($this->nameForm . "_divDatiIndice");
            $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
            Out::valore($this->nameForm . "_NumeroIndice", $dizionarioIdelib['PROGRESSIVO']);
            Out::valore($this->nameForm . "_AnnoIndice", substr($Indice_rec['IDATDE'], 0, 4));
            Out::valore($this->nameForm . "_TipoIndice", $Indice_rec['INDTIPODOC']);
            Out::valore($this->nameForm . "_DataIndice", $Indice_rec['IDATDE']);
            Out::html($this->nameForm . '_Pronum_lbl', 'ID');
            if ($Indice_rec['INDPREPAR']) {
                switch ($Indice_rec['INDPREPAR']) {
                    case 'P':
                        Out::show($this->nameForm . '_divInfoDatiProto');
                        $MsgInfo = '<span style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                        $MsgInfo .= "Documento predisposto per la creazione di un nuovo protocollo in <u>Partenza</u></span>";
                        Out::html($this->nameForm . '_divInfoDatiProto', $MsgInfo);
                        break;

                    case 'C':
                        Out::show($this->nameForm . '_divInfoDatiProto');
                        $MsgInfo = '<span style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                        $MsgInfo .= "Documento predisposto per la creazione di un nuovo <u>Documento Formale</u></span>";
                        Out::html($this->nameForm . '_divInfoDatiProto', $MsgInfo);
                        break;

                    default:
                        break;
                }
            }

            $ParametriVari = $this->segLib->GetParametriVari();
            if ($ParametriVari['SEG_ATTIVA_CLAFAS']) {
                Out::show($this->nameForm . "_divDatiFascicolo");
                Out::show($this->nameForm . '_divGestioneFascicoli');
                Out::hide($this->nameForm . '_divDescFascSottoFasc');
                $this->ElencoFascicoli = $this->proLibFascicolo->CaricaFascicoliProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_rec['PROFASKEY']);
                $this->CaricaGriglia($this->gridFascicoli, $this->ElencoFascicoli);
                $tot = count($this->ElencoFascicoli);
                Out::tabSetTitle($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneFascicoli", 'Fascicoli <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');
            }
        } else if ($this->tipoProt == 'W') {
            Out::hide($this->nameForm . "_divMittentiProt");
            Out::hide($this->nameForm . "_divDatiTitolario");
            Out::hide($this->nameForm . "_divDatiFascicolo");
            Out::show($this->nameForm . "_divDestProt");
            Out::show($this->nameForm . "_divSoggetti");
            $this->caricaGrigliePartenza($anapro_rec);
            $this->caricaGrigliaAllegatiPasso($propas_rec['PROPAK']);
        } else {
            $this->caricaGriglieArrivo($anapro_rec);
        }

        $this->caricaNote();

        Out::hide($this->nameForm . '_divAllGrid');
        Out::hide($this->nameForm . '_divDestGrid');

        $open_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'] . " " . $arcite_rec['ITEANN'];
        $this->openRecord($this->PROT_DB, 'ARCITE', $open_Info);
        $spanInfo = '<span style="display:inline-block; color:green !important; padding-bottom:2px;" class="ui-icon ui-icon-info"></span>';
        if ($arcite_rec['ITETIP'] == proIter::ITETIP_ALLAFIRMA) {
            if ($arcite_rec['ITEGES'] == 0) {
                $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Procedura delle Firme Rifiutata.' . $pre_c);
                Out::show($this->nameForm . '_statoTrasmissione');
                Out::hide($this->nameForm . '_Rifiuta');
                $this->Nascondi();
            } else {
                $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $spanInfo . $pre_a . 'Firma gli allegati.' . $pre_c);
                Out::hide($this->nameForm . '_Ricevuta');
                Out::tabSelect($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegati");
            }
            if ($this->currArcite['ITESTATO'] != '1' && $this->currArcite['ITESTATO'] != '3' && $this->currArcite['ITEGES'] != 0) {
                if ($arcite_rec['ITEFIN'] != '' && $arcite_rec['ITEFLA'] == '2') {
                    Out::show($this->nameForm . '_Riapri');
                } else {
                    Out::show($this->nameForm . '_Chiudi');
                }
            }
            // Controllo se è un STRG
            if ($anaent_41['ENTVAL'] && $anapro_rec['PROCODTIPODOC'] == $anaent_41['ENTVAL'] && $anapro_rec['PROPAR'] == 'C' && $anaent_41['ENTDE3']) {
                Out::show($this->nameForm . '_Esporta');
            }
        } else if ($arcite_rec['ITETIP'] == proIter::ITETIP_PARERE) {
            if ($arcite_rec['ITEGES'] == 0) {
                $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Procedura Pareri.' . $pre_c);
                Out::show($this->nameForm . '_statoTrasmissione');
                Out::hide($this->nameForm . '_Rifiuta');
                $this->Nascondi();
            } else {
                $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Esprimi il parere.' . $pre_c);
                Out::hide($this->nameForm . '_Ricevuta');
                Out::show($this->nameForm . '_EsprimiParere');
                Out::tabSelect($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegati");
            }
            if ($this->currArcite['ITESTATO'] != proIter::ITESTATO_RIFIUTATO && $this->currArcite['ITESTATO'] != '3' && $this->currArcite['ITEGES'] != 0) {
                if ($arcite_rec['ITEFIN'] != '' && $arcite_rec['ITEFLA'] == '2') {
                    Out::show($this->nameForm . '_Riapri');
                    Out::hide($this->nameForm . '_EsprimiParere');
                } else {
                    Out::show($this->nameForm . '_Chiudi');
                }
            }
            // Termine non piu consideratoper poter gestire anche gli scaduti.
            //} else if ($arcite_rec['ITEFIN'] == '' && $arcite_rec['ITESUS'] == '' && $arcite_rec['ITEGES'] == 1 && ($arcite_rec['ITETERMINE'] == '' || $arcite_rec['ITETERMINE'] >= date("Ymd"))) {
        } else if ($arcite_rec['ITEFIN'] == '' && $arcite_rec['ITESUS'] == '' && $arcite_rec['ITEGES'] == 1) {
            if ($arcite_rec['ITESTATO'] != 2) {
                if ($arcite_rec['ITESTATO'] == 1 || $arcite_rec['ITESTATO'] == 3) {
                    $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                    $pre_c = '</pre>';
                    Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Trasmissione Rifiutata.' . $pre_c);
                    Out::show($this->nameForm . '_statoTrasmissione');
                } else {
                    $pre_a = '<pre style="display:inline-block;color:red;font-weight:bold;text-align:center;font-size:120%;">';
                    $pre_c = '</pre>';
                    $htmlMsg = $pre_a . 'Accetta o Rifiuta<br>la Trasmissione.' . $pre_c;
                    if ($anapro_rec['PROFASKEY'] == '') {// TODO@ Cosa sarebbe?
                        $htmlMsg .= "<br>Accetta la Trasmissione per poter Aprire il fascicolo";
                    }
                    Out::html($this->nameForm . '_statoTrasmissione', $htmlMsg);
                    Out::show($this->nameForm . '_statoTrasmissione');
                    Out::show($this->nameForm . '_InCarico');
                    if (!$arcite_pre['ITEDATRIF']) {
                        Out::show($this->nameForm . '_Rifiuta');
                    }
                }
                $this->bloccaDati();

                $this->DisabilitaTitolario();
            } else {
                if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100" || $anapro_rec['PROCCF'] == "" || $anaent_57['ENTDE5']) {
                    $this->AbilitaTitolario();
                } else {
                    $this->DisabilitaTitolario();
                }
                Out::show($this->nameForm . '_Invia');

                if ($arcite_rec['ITEFIN'] == '') {
                    Out::show($this->nameForm . '_Chiudi');
                }
                //Visualizzo Riscontro solo se FatturaElettronica.
                if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] && $anapro_rec['PROPAR'] == 'A') {
                    Out::show($this->nameForm . '_Riscontro');
                }



                // Visualizzo "Esporta" se esportabile per Fattura Elettronica
                if ($this->proLibSdi->CheckAnaproEsportabile($anapro_rec)) {
                    Out::show($this->nameForm . '_Esporta');
                }
                // Controllo se è un STRG
                if ($anaent_41['ENTVAL'] && $anapro_rec['PROCODTIPODOC'] == $anaent_41['ENTVAL'] && $anapro_rec['PROPAR'] == 'C' && $anaent_41['ENTDE3']) {
                    Out::show($this->nameForm . '_Esporta');
                }
                /* Inizio WS Kibernetes */
                $anaent_46 = $this->proLib->GetAnaent('46');
                if ($anaent_46['ENTVAL'] && $anapro_rec['PROCODTIPODOC'] && $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                    if ($anaent_46['ENTDE2']) {
                        Out::show($this->nameForm . '_CaricaFattura');
                    }
                }
                /* Fine WS Kibernetes */
                /*
                 * HALLEY CONTABILITA Se attivo ed è una fattura.
                 */
                $anaent_55 = $this->proLib->GetAnaent('55');
                if ($anaent_55['ENTVAL'] && $anapro_rec['PROCODTIPODOC'] && $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                    Out::show($this->nameForm . '_AggiornaFatturaHalley');
                }
                Out::show($this->nameForm . '_Evidenza');
                if ($arcite_rec['ITEEVIDENZA'] == 1) {
                    Out::html($this->nameForm . "_Evidenza_lbl", "Togli Evidenza");
                } else {
                    Out::html($this->nameForm . "_Evidenza_lbl", "Metti Evidenza");
                }
                Out::valore($this->nameForm . "_Annotazioni", '');
                $pre_a = '<pre style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Trasmissione in Carico.' . $pre_c);
                $this->sbloccaDati();
            }
            /*
             * HALLEY CONTABILITA Sempre Attivo: Senza presa in carico.
             */
            $anaent_55 = $this->proLib->GetAnaent('55');
            if ($anaent_55['ENTVAL'] && $anapro_rec['PROCODTIPODOC'] && $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                Out::show($this->nameForm . '_AggiornaFatturaHalley');
            }
        } else {
            if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100" || $anapro_rec['PROCCF'] == "" || $anaent_57['ENTDE5']) {
                $this->AbilitaTitolario();
            } else {
                $this->DisabilitaTitolario();
            }
            Out::hide($this->nameForm . '_statoTrasmissione');
            if ($arcite_rec['ITESUS'] && $arcite_rec['ITEDATRIF'] == '' && $arcite_rec['ITEFIN'] == '') { //la visualizzazione del bottone aggiorna è possibile solo se non rifiutato - Mario - 30.07.2013
                Out::show($this->nameForm . '_Aggiorna');
                Out::show($this->nameForm . '_Chiudi');
                /*
                 *  Sblocco se è in gestione e non è chiuuso/rifiutato
                 *  e anche se ha già una trasmissione successiva. 
                 *  Alle - 03.10.2016
                 */
                if ($arcite_rec['ITEGES']) {
                    $this->sbloccaDati();
                }
            } else {
                $this->bloccaDati();
            }

            /**
             * Controllo se il protocollo è stato rifiutato, ma ancora non letto.
             * In questo caso si attiva il bottone _NoRifiuta
             * Mario - 30.07.2013
             */
            if ($arcite_rec['ITESUS'] != '' && $arcite_rec['ITEDATRIF'] != '') {
                if ($arcite_sus['ITEDLE'] == '' && $arcite_sus['ITEFIN'] == '') {
                    Out::show($this->nameForm . '_NoRifiuta');
                    Out::hide($this->nameForm . '_Rifiuta');
                }
            }
            /**
             * Controllo se c'è la data di chiusura per il protocollo.
             * In questo caso si attiva il bottone _Riapri
             * Solo se ITEGES == 1
             * Mario - 30.07.2013
             */
            if ($arcite_rec['ITEFIN'] != '' && $arcite_rec['ITEFLA'] == '2' && $arcite_rec['ITEGES'] == '1') {
                Out::show($this->nameForm . '_Riapri');
            }
        }

        //Controllo per rifiuta attivo qui?
        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($arcite_rec['ITEGES'] == 0 && $arcite_rec['ITEFIN'] == '' && $arcite_rec['ITEDATRIF'] == '') {
            Out::show($this->nameForm . '_PresaVisione');
            if ($anaent_37['ENTDE6']) {
                Out::show($this->nameForm . '_Rifiuta');
            }
        }

        // se attivo "trasmetti protocollo in visione"  
        $anaent_40 = $this->proLib->GetAnaent('40');
        if ($arcite_rec['ITEGES'] == 0 && $anaent_40['ENTDE5'] && $arcite_rec['ITEFIN'] != '') {
            Out::show($this->nameForm . '_InviaInVisione');
            $this->sbloccaDati(); // Corretto il 26.02.2016
            TableView::hideCol($this->gridDestinatari, 'DESGESADD');
        }

        /*
         * Invia acceso per le trasmissioni in gestione, prese in carico.
         */
        if ($arcite_rec['ITEGES'] == 1 && $arcite_rec['ITEFIN'] == '' && $arcite_rec['ITESTATO'] = proIter::ITESTATO_INCARICO) {
            // Out::show($this->nameForm . '_Invia');
        }


        if ($arcite_rec['ITEGES'] == 0 && $arcite_rec['ITEFIN'] != '' && $arcite_rec['ITEDATRIF'] == '') {
            if ($anaent_37['ENTDE6']) {
                Out::show($this->nameForm . '_Rifiuta');
            }
        }
        /* FORZATO RISCONTRO ATTIVO QUANDO HO IN GESTIONE IL PROTO, NON E' CHIUSO E CE L'HO IN CARICO */
        if ($arcite_rec['ITEFIN'] == '' && $arcite_rec['ITEGES'] == 1 && $arcite_rec['ITESTATO'] == proIter::ITESTATO_INCARICO) {
            Out::show($this->nameForm . '_Riscontro');
        }
        Out::tabSelect($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneArrivi");

        if ($anapro_rec['PRODAS'] == 0) {
            $anapro_rec['PRODAS'] = '';
        }
        Out::valori($anapro_rec, $this->nameForm . '_ANAPRO');
        Out::valori($arcite_rec, $this->nameForm . '_ARCITE');
        Out::valore($this->nameForm . "_Pronum", substr($anapro_rec['PRONUM'], 4));
        Out::valore($this->nameForm . "_ITEKEY", $arcite_rec['ITEKEY']);

        $anaprosave_tab = $this->proLib->getGenericTab("SELECT ROWID, PROUTE FROM ANAPROSAVE WHERE PRONUM=" . $anapro_rec['PRONUM'] . " AND PROPAR='" . $anapro_rec['PROPAR'] . "' ORDER BY ROWID");
        if ($anaprosave_tab) {
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', $anaprosave_tab[0]['PROUTE']);
        } else {
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', $anapro_rec['PROUTE']);
        }

        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        Out::valore($this->nameForm . "_Oggetto", $anaogg_rec['OGGOGG']);
        $this->caricaAllegati($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);


        $proges_rec = $this->proLibPratica->GetProges($anapro_rec['PROFASKEY'], 'geskey');
        if ($proges_rec['GESDCH']) {
            $this->visualizzazione = true;
        }

        if (!$this->visualizzazione) {
            $this->impostoIter($arcite_rec['ITEKEY']);
        }

        $this->caricaIter($arcite_rec['ITEPRO'], $arcite_rec['ITEKEY']);

        $this->DecodAnacat($anapro_rec['VERSIONE_T'], $anapro_rec['PROCAT']);
        $this->DecodAnacla($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCA']);
        $this->DecodAnafas($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCF']);
        $this->DecodAnaorg($anapro_rec['PROFASKEY'], 'orgkey');

        /* Nuovo Caricamento Fascicoli */
        $TipoProt = $anapro_rec['PROPAR'];
        if ($TipoProt == 'A' || $TipoProt == 'P' || $TipoProt == 'C') {
            Out::show($this->nameForm . '_divGestioneFascicoli');
            Out::hide($this->nameForm . '_divDescFascSottoFasc');
            $this->ElencoFascicoli = $this->proLibFascicolo->CaricaFascicoliProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_rec['PROFASKEY']);
            $this->CaricaGriglia($this->gridFascicoli, $this->ElencoFascicoli);
            $tot = count($this->ElencoFascicoli);
            Out::tabSetTitle($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneFascicoli", 'Fascicoli <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');

            TableView::setLabel($this->gridFascicoli, 'FASCICOLO', 'Fascicoli: ' . count($this->ElencoFascicoli));
        } else if ($TipoProt == 'F' || $TipoProt == 'N') {
            Out::hide($this->nameForm . '_divGestioneFascicoli');
            Out::show($this->nameForm . '_divDescFascSottoFasc');
        }
        if ($arcite_rec['ITESTATO'] != 2) {
            //Out::hide($this->nameForm . '_AggiungiFascicolo');
            Out::hide($this->nameForm . '_ANAPRO[PROARG]_butt');
            Out::hide($this->nameForm . '_addFascicolo');
            Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
            Out::attributo($this->nameForm . '_Organn', "readonly", '0');
        } else {
            if ($anapro_rec['PROFASKEY'] != '') {
                Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
                Out::attributo($this->nameForm . '_Organn', "readonly", '0');
            }

            /*
             * Ulteriore controllo per stabilire se ha la gesitone: 
             * forse inutile perchè lo vedrebbe solo se è in carico..
             */
            $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();

            /*
             * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
             */
            $fl_movimenta = false;
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
                $fl_movimenta = true;
            }

            /*
             * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può movimentare
             */

            if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
                $fl_movimenta = true;
            }

            /* Se è archivista può sempre fascicolare */
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
                $fl_movimenta = true;
            }

            if ($fl_movimenta) {
                Out::show($this->nameForm . '_addFascicolo');
                Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '1');
                Out::attributo($this->nameForm . '_Organn', "readonly", '1');
            } else {
                Out::hide($this->nameForm . '_addFascicolo');
                Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
                Out::attributo($this->nameForm . '_Organn', "readonly", '0');
            }
        }

        Out::show($this->nameForm . "_divBottoniPersonal");
        Out::hide($this->nameForm . "_divBottoniAcquisisci");
        if ($arcite_rec['ITEDES'] !== proSoggetto::getCodiceSoggettoFromIdUtente() && $arcite_rec['ITEDES'] != '') {
            Out::hide($this->nameForm . "_divBottoniPersonal");
            Out::hide($this->nameForm . "_divBottoniAcquisisci");
            $this->bloccaDati();
            $soggetto_attuale = proSoggetto::getInstance($this->proLib, proSoggetto::getCodiceSoggettoFromIdUtente(), $arcite_rec['ITEUFF']);
            $soggetto_attuale->getSoggetto();


            $spiegazione = '';
            $soggetto_originale = proSoggetto::getInstance($this->proLib, $arcite_rec['ITEDES'], $arcite_rec['ITEUFF']);
            $soggetto_originale_dati = $soggetto_originale->getSoggetto();
            if ($arcite_rec['ITEORGWORKLIV'] > 0) {
                $pre_a = '<pre style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                $spiegazione = "La trasmissione selezionata è stata inviata a: {$soggetto_originale_dati['DESCRIZIONESOGGETTO']}<br>";
                $spiegazione .= "La trasmissione può essere acquisita dai componenti dell'ufficio: {$soggetto_originale_dati['DESCRIZIONEUFFICIO']}<br>";
                $spiegazione .= "Con il bottone acquisisci potrai avere la possibiltà di prendere in carico la trasmissione.";
                Out::html($this->nameForm . '_statoTrasmissione', $spiegazione);
                Out::show($this->nameForm . "_statoTrasmissione");
                Out::show($this->nameForm . "_divBottoniAcquisisci");
            }

            $extraParams = array(
                'DELESCRIVANIA' => 1,
                'DELESRCUFF' => $arcite_rec['ITEUFF'],
                'DELESRCCOD' => $arcite_rec['ITEDES']
            );
            $delegaScrivaniaAttiva = $this->proLibDeleghe->getDelegheAttive(proSoggetto::getCodiceSoggettoFromIdUtente(), $extraParams, proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);

            if (count($delegaScrivaniaAttiva)) {
                $pre_a = '<pre style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                $spiegazione = "La trasmissione selezionata appartine a {$soggetto_originale_dati['DESCRIZIONESOGGETTO']}.<br>";
                $spiegazione .= "La scrivania: {$soggetto_originale_dati['DESCRIZIONEUFFICIO']} è stata delegata.<br>";
                $spiegazione .= "Con il bottone acquisisci potrai avere la possibiltà di prendere in carico la trasmissione.";
                Out::html($this->nameForm . '_statoTrasmissione', $spiegazione);
                Out::show($this->nameForm . "_divBottoniAcquisisci");
                Out::show($this->nameForm . "_statoTrasmissione");
            }
        }
        // $this->caricaDivTrasmissioni($arcite_pre, $arcite_rec);
        if ($this->tipoProt == 'F' || $this->tipoProt == 'N') {
            $numprat = substr($proges_rec['GESNUM'], 14) . " / " . substr($proges_rec['GESNUM'], 10, 4);
            Out::valore($this->nameForm . "_pratica", $numprat);
            $prasta_rec = $this->proLibPratica->GetPrasta($proges_rec['GESNUM'], 'codice');
            Out::html($this->nameForm . "_statoFascicolo", $prasta_rec['STADES']);
            Out::hide($this->nameForm . '_divIntProt');
            Out::show($this->nameForm . '_divIntFasc');
            $infoRis = '<div class="ita-icon ita-icon-open-folder-64x64" Title="Fascicolo">&nbsp;</div>';

            if ($this->tipoProt == 'N') {
                $subF = substr($anapro_rec['PROSUBKEY'], strpos($anapro_rec['PROSUBKEY'], '-') + 1);
                Out::valore($this->nameForm . "_sottofascicolo", $subF);
                Out::show($this->nameForm . '_sottofascicolo_field');
                $infoRis = '<div class="ita-icon ita-icon-sub-folder-64x64" Title="Fascicolo">&nbsp;</div>';
            }
        } else if ($this->tipoProt == 'I') {
            $infoRis = '<div class="ita-icon ita-icon ita-icon-register-document-green-64x64" Title="Indice">&nbsp;</div>';
            Out::show($this->nameForm . '_divIntProt');
            Out::hide($this->nameForm . '_divIntFasc');
        } else if ($this->tipoProt == 'W') {
            $infoRis = '<div class="ita-icon ita-icon ita-icon-footsteps-64x64" Title="Passo">&nbsp;</div>';
            Out::show($this->nameForm . '_divIntProt');
            Out::hide($this->nameForm . '_divIntFasc');
            Out::html($this->nameForm . '_divInformazioniIter', '');
            Out::hide($this->nameForm . '_Evidenza');
            Out::hide($this->nameForm . '_Riscontro');
            //
            Out::tabRemove($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneFascicoli");
            Out::tabRemove($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneDatiAggiuntivi");
        } else {
            Out::show($this->nameForm . '_divIntProt');
            Out::hide($this->nameForm . '_divIntFasc');
            $infoRis = '<div class="ita-icon ita-icon-register-document-64x64" Title="Protocollo">&nbsp;</div>';
        }

        Out::html($this->nameForm . '_divIcona', $infoRis);

        if ($this->visualizzazione) {
            $this->Nascondi();
            $this->bloccaDati();
            // Out::hide($this->nameForm . '_AggiungiFascicolo');
            Out::hide($this->nameForm . '_addFascicolo');
            Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
            Out::attributo($this->nameForm . '_Organn', "readonly", '0');
            $this->DisabilitaTitolario();
        }
        if ($proges_rec['GESDCH']) {
            $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-divieto-24x24" Title="Fascicolo Chiuso">&nbsp;</div><div style="display:inline-block;"> &nbsp; Fascicolo Chiuso</div>';
            Out::html($this->nameForm . '_statoTrasmissione', $infoRis);
            Out::show($this->nameForm . '_statoTrasmissione');
            Out::hide($this->gridNote . "_addGridRow");
            Out::hide($this->gridNote . "_editGridRow");
            Out::hide($this->gridNote . "_delGridRow");
        }

        // Out Descrizione Versione Titolario:
        Out::hide($this->nameForm . '_VersioneTitolario');
        if (!$this->proLibTitolario->CheckVersioneUnica()) {
            Out::show($this->nameForm . '_VersioneTitolario');
            $Versione_rec = $this->proLibTitolario->GetVersione($anapro_rec['VERSIONE_T'], 'codice');
            Out::html($this->nameForm . '_VersioneTitolario', '(' . $Versione_rec['DESCRI_B'] . ')');
        }


        if ($anapro_rec['PROFASKEY']) {
            //Out::show($this->nameForm . '_GestPratica');
        }
        /*
         * Dati Fascicolo Principale
         */
        Out::valore($this->nameForm . '_DescFascicoloPrincipale', '');
        foreach ($this->ElencoFascicoli as $Fascicoli_rec) {
            if ($anapro_rec['PROFASKEY'] != '' && $Fascicoli_rec['ORGKEY'] == $anapro_rec['PROFASKEY']) {
                $DescFasPrinc = $Fascicoli_rec['ORGKEY'] . ' : ' . $Fascicoli_rec['OGGOGG_FASCICOLO'];
                Out::valore($this->nameForm . '_DescFascicoloPrincipale', $DescFasPrinc);
            }
        }
        $this->datiAggiuntivi = array();
        $this->caricaDatiAggiuntivi($anapro_rec);

        /*
         * Verifico se è un trasmissione per ufficio non acquisita.
         * ITESUS: indica se trasmisisone acquisita. ITEDES vuoto indica 
         * una trasmissione per ufficio se ITEUFF valorizzato.
         */
        if ($arcite_rec['ITEDES'] == '' && $arcite_rec['ITEUFF'] <> '') {
            if ($arcite_rec['ITESUS'] == '') {
                // Presa in carico con acquisizione.
                Out::hide($this->nameForm . '_InCarico');
                Out::hide($this->nameForm . '_PresaVisione');
                Out::show($this->nameForm . '_InCaricoAcquisizione');
                Out::show($this->nameForm . "_divBottoniPersonal");
            }
        }
        if ($TipoProt == 'F' || $TipoProt == 'N') {
            Out::hide($this->nameForm . '_addFascicolo');
        }

        /*
         * Per evitare lentezza in apertura, non ricarica il portlet
         * In previsione di funzione che rielabora nel portlet solo il record appena aperto.
         */
        //$this->ricaricaPortlet();
        // Controllo documenti da firmare
        $retCheck = $this->CheckAllegatiDaFirmare();
        if ($retCheck === false) {
            $this->close();
        }

        if ($TipoProt == 'I' && App::$utente->getKey('nomeUtente') == 'gcostantino') {
            $ProDocProt_rec = $this->proLib->GetProDocProt($anapro_rec['PRONUM'], 'sorgnum', $anapro_rec['PROPAR']);
            if (!$ProDocProt_rec) {
                $this->caricaAllegati($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
            }
        }

        /*
         * Verifica Delega di Richiesta Firma:
         * - se Iter in gestione al destinatario corrente ed è una delega
         * - se il padre è una richiesta di firma non ancora gestita [ITEFIN non chiuso]
         * [Altrimenti rimangono i bottoni di gestione ordinari (presa in carico, rifiuto ecc.) ]
         */
        if ($this->currArcite['ITETIP'] == proIter::ITETIP_PARERE_DELEGA) {
            if ($arcite_rec['ITEGES'] == 1 && $this->currArcite['ITEDES'] == $this->codiceDest) {
                $arcite_padre_delega = $this->proLib->GetArcite($this->currArcite['ITEPRE'], 'itekey');
                // $arcite_padre_delega = $iter->getIterNode($this->currArcite['ITEPRE'],'itekey');
                if ($arcite_padre_delega['ITETIP'] == '1' && $arcite_padre_delega['ITEFIN'] == '') {
                    Out::hide($this->nameForm . "_divBottoniPersonal");
                    Out::show($this->nameForm . "_divBottoniAcquisisci");
                }
            }
            //$arcite_padre = $iter->getIterNode($arcite_padre_delega['ITEPRE']);
        }



        return $arcite_rec;
    }

    function DisabilitaTitolario() {
        Out::hide($this->nameForm . '_ANAPRO[PROCAT]_butt');
        Out::hide($this->nameForm . '_Clacod_butt');
        Out::hide($this->nameForm . '_Fascod_butt');
        Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '0');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
        Out::delClass($this->nameForm . '_ANAPRO[PROCAT]', "ita-decode ui-state-highlight");
        Out::delClass($this->nameForm . '_Clacod', "ita-decode ui-state-highlight");
        Out::delClass($this->nameForm . '_Fascod', "ita-decode ui-state-highlight");
    }

    function AbilitaTitolario() {
        Out::show($this->nameForm . '_ANAPRO[PROCAT]_butt');
        Out::show($this->nameForm . '_Clacod_butt');
        Out::show($this->nameForm . '_Fascod_butt');
        Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '1');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '1');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '1');
        Out::addClass($this->nameForm . '_ANAPRO[PROCAT]', "ita-decode ui-state-highlight");
        Out::addClass($this->nameForm . '_Clacod', "ita-decode ui-state-highlight");
        Out::addClass($this->nameForm . '_Fascod', "ita-decode ui-state-highlight");

        $anaent_57 = $this->proLib->GetAnaent('57');
        if ($anaent_57['ENTDE5']) {// Cehck Conservazione?
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
            $proLibConservazione = new proLibConservazione();
            $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
            $Anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
            $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            if (!$ProConser_rec) {
                Out::show($this->nameForm . '_salvaTitolario');
            }
        }
    }

    function AggiornaTitolario($Itepro, $Tipo) {
        $anapro_rec = $this->proLib->GetAnapro($Itepro, 'codice', $Tipo);
        if (!$anapro_rec) {
            return false;
        }
        $anapro_rec['PROCAT'] = $this->formData[$this->nameForm . "_ANAPRO"]['PROCAT'];
        $anapro_rec['PROCCA'] = $anapro_rec['PROCAT'] . $this->formData[$this->nameForm . "_Clacod"];
        $anapro_rec['PROCCF'] = $anapro_rec['PROCCA'] . $this->formData[$this->nameForm . "_Fascod"];
        $update_Info = "Oggetto: Aggiorno il titolario del protocollo n. $Itepro tipo $Tipo";
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }
        return true;
    }

    private function controllaTitolario($uffcod, $catcod, $clacod = '', $fascod = '') {
        $sql = "SELECT * FROM UFFTIT WHERE UFFCOD='$uffcod' AND CATCOD='$catcod'";
        if ($clacod) {
            $sql .= " AND CLACOD='$clacod'";
        }
        if ($fascod) {
            $sql .= " AND FASCOD='$fascod'";
        }
        $ufftit_test = $this->proLib->getGenericTab($sql);
        if ($ufftit_test) {
            return $ufftit_test;
        }
        return false;
    }

    private function checkCatFiltrato($codice) {
        $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice);
        Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $codice);
        Out::valore($this->nameForm . '_catdes', '');
        foreach ($titolario_tab as $titolario_rec) {
            if ($titolario_rec['CLACOD'] == '' && $titolario_rec['FASCOD'] == '') {
                $this->DecodAnacat('', $codice);
            }
        }
    }

    private function checkClaFiltrato($codice1, $codice2) {
        $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $codice1, $codice2);
        Out::valore($this->nameForm . '_Clacod', $codice2);
        Out::valore($this->nameForm . '_clades', '');
        foreach ($titolario_tab as $titolario_rec) {
            if ($titolario_rec['FASCOD'] == '') {
                $this->DecodAnacla('', $codice1 . $codice2);
            }
        }
    }

    private function caricaIter($codice, $itekey) {
        $this->statoIter = 0;
        $arrayIter = $this->caricaTreeIter($arrayIter, $codice);
        for ($i = 0; $i < 30; $i++) {
            $arrayIter = $this->calcolaDataIter($arrayIter, $i);
        }

        $totGiorni = $this->calcolaTotaleGiorni($arrayIter);
        if ($this->statoIter == 1) {
            Out::html($this->nameForm . "_StatoIter", 'ITER APERTO<br>Tot.Giorni ' . $totGiorni);
            Out::hide($this->nameForm . '_Protocolla');
        } else {
            Out::html($this->nameForm . "_StatoIter", 'ITER CHIUSO<br>Tot.Giorni ' . $totGiorni);
        }
        foreach ($arrayIter as $key => $iter) {
            if ($iter['ITEKEY'] == $itekey) {
                $arrayIter[$key]['ITERDESTINATARIO'] = "<p style = 'font-weight:900;color:blue;'>{$iter['ITERDESTINATARIO']}</p>";
            }
        }

        $this->proIter = $arrayIter;
        $this->caricaGriglia($this->gridIter, $arrayIter);
    }

    private function caricaTreeIter($arrayIter, $codice, $itekey = NULL, $level = 0) {
        if ($itekey == NULL) {
            $arcite_tab = $this->proLib->getGenericTab(
                    "SELECT * FROM ARCITE WHERE ITEPRO =$codice AND ITEPRE='' AND ITEPAR='$this->tipoProt' ORDER BY ITEDAT,ITEFIN");
        } else {
            $arcite_tab = $this->proLib->getGenericTab(
                    "SELECT * FROM ARCITE WHERE ITEPRO =$codice AND ITEPRE='$itekey' AND ITEPAR='$this->tipoProt' ORDER BY ITEDAT, ITEFIN");
        }
        if (count($arcite_tab) > 0) {
            for ($i = 0; $i < count($arcite_tab); $i++) {
                $style = "";
                $inc = count($arrayIter) + 1;
                if ($arcite_tab[$i]['ITEANNULLATO']) {
                    $style = ' style="background-color:gray;color:white;font-wheight:bold;" ';
                }
                $arrayIter[$inc]['ITEKEY'] = $arcite_tab[$i]['ITEKEY'];
                $arrayIter[$inc]['ITEPRE'] = $arcite_tab[$i]['ITEPRE'];
                $arrayIter[$inc]['ITERDATA'] = $arcite_tab[$i]['ITEDAT'];
                $arrayIter[$inc]['ITERGIORNI'] = '';
                $arrayIter[$inc]['ITERCODDEST'] = "<p $style>{$arcite_tab[$i]['ITEDES']}</p>";
                $soggetto = proSoggetto::getInstance($this->proLib, $arcite_tab[$i]['ITEDES'], $arcite_tab[$i]['ITEUFF']);
                if (!$soggetto) {
                    continue;
                }
                $record = $soggetto->getSoggetto();
                $arrayIter[$inc]['ITERDESTINATARIO'] = $record['DESCRIZIONESOGGETTO'];
                if ($record['RUOLO']) {
                    $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['RUOLO'];
                }
                $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['DESCRIZIONEUFFICIO'];
                if ($record['SERVIZIO']) {
                    $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['SERVIZIO'];
                }
                if (!$record) {

                    if ($arcite_tab[$i]['ITEDES']) {
                        if ($arcite_tab[$i]['ITEUFF']) {
                            $anamed_rec = $this->proLib->GetAnamed($arcite_tab[$i]['ITEDES'], 'codice', 'no', false, true);
                            $anauff_rec = $this->proLib->GetAnauff($arcite_tab[$i]['ITEUFF']);
                            $arrayIter[$inc]['ITERDESTINATARIO'] = "<p>{$anamed_rec['MEDNOM']} - {$anauff_rec['UFFDES']}</p>";
                        } else {
                            $style = 'style = "font-weight:900;color:#BE0000;"';
                            $anamed_rec = $this->proLib->GetAnamed($arcite_tab[$i]['ITEDES'], 'codice', 'no', false, true);
                            $arrayIter[$inc]['ITERDESTINATARIO'] = "<p $style>{$anamed_rec['MEDNOM']} - Trasmissione a persona</p>";
                        }
                    } else if ($arcite_tab[$i]['ITEUFF']) {
                        $style = 'style = "font-weight:900;background:#B2F7DC;"';
                        $anauff_rec = $this->proLib->GetAnauff($arcite_tab[$i]['ITEUFF']);
                        $arrayIter[$inc]['ITERDESTINATARIO'] = "<p $style>{$anauff_rec['UFFDES']} - Trasmissione a ufficio</p>";
                    }
                }

                $annotazioni = $arcite_tab[$i]['ITEANN'] . $arcite_tab[$i]['ITEAN2'];
                $arrayIter[$inc]['ITERANNOTAZIONI'] = "<p $style>$annotazioni</p>";
                $arrayIter[$inc]['ITEDLE'] = $arcite_tab[$i]['ITEDLE'];
                $arrayIter[$inc]['ITERCHIUSO'] = $arcite_tab[$i]['ITEFIN'];
                $arrayIter[$inc]['ITETERMINE'] = $arcite_tab[$i]['ITETERMINE'];
                $arrayIter[$inc]['ITERACCRIF'] = '';
                $arrayIter[$inc]['ITERMOTIVO'] = '';
                if ($arcite_tab[$i]['ITEDATACC']) {
                    $arrayIter[$inc]['ITERACCRIF'] = $arcite_tab[$i]['ITEDATACC'];
                    $arrayIter[$inc]['ITERMOTIVO'] = $arcite_tab[$i]['ITENOTEACC'];
                }
                if ($arcite_tab[$i]['ITEDATRIF']) {
                    $arrayIter[$inc]['ITERACCRIF'] = $arcite_tab[$i]['ITEDATRIF'];
                    $arrayIter[$inc]['ITERMOTIVO'] = $arcite_tab[$i]['ITEMOTIVO'];
                }
                $arrayIter[$inc]['ITERGEST'] = 0;
                if ($arcite_tab[$i]['ITEGES'] == 1) {
                    $arrayIter[$inc]['ITERGEST'] = 1;
                }
                $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-green-24x24"></span>';
                if ($arcite_tab[$i]['ITETERMINE'] <> '' && $arcite_tab[$i]['ITETERMINE'] < date("Ymd")) {
                    $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-lock-24x24"></span>';
                } else if ($arcite_tab[$i]['ITESTATO'] == '1' || $arcite_tab[$i]['ITESTATO'] == '3') {
                    $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-divieto-24x24"></span>';
                } else {
                    if ($arcite_tab[$i]['ITESUS'] != '') {
                        $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-grey-24x24"></span>';
                    } else {
                        if ($arcite_tab[$i]['ITEFIN'] != '' || $arcite_tab[$i]['ITEGES'] != '1') {
                            $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-red-24x24"></span>';
                        } else {
                            $this->statoIter = 1;
                        }
                    }
                }
                /*
                 * Ragione Trasmissione (Motivo)
                 */
                if ($arcite_tab[$i]['ITECODRAGIONE']) {
                    $RagioneTrasm_rec = $this->proLib->GetRagioneTrasm($arcite_tab[$i]['ITECODRAGIONE']);
                    if ($RagioneTrasm_rec) {
                        $Ragione = '[' . $RagioneTrasm_rec['CODICE'] . '] - ' . $RagioneTrasm_rec['DESCRAGIONE'] . ' ';
                        $arrayIter[$inc]['ITERMOTIVO'] = $Ragione . $arrayIter[$inc]['ITERMOTIVO'];
                    }
                }

                $arrayIter[$inc]['level'] = $level;
                $arrayIter[$inc]['parent'] = $itekey;
                $arrayIter[$inc]['isLeaf'] = 'false';
                $arrayIter[$inc]['expanded'] = 'true';
                $arrayIter[$inc]['loaded'] = 'true';
                $save_count = count($arrayIter);
                $arrayIter = $this->caricaTreeIter($arrayIter, $codice, $arrayIter[$inc]['ITEKEY'], $level + 1);
                if ($save_count == count($arrayIter)) {
                    $arrayIter[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $arrayIter;
    }

    private function calcolaDataIter($arrayIter, $level = 0) {
        foreach ($arrayIter as $key => $record1) {
            if ($record1['level'] == $level) {
                $data1 = $record1['ITERDATA'];
                $data2 = 0;
                if ($record1['ITERCHIUSO'] != '') {
                    $data2 = $record1['ITERCHIUSO'];
                }
                if ($data2 == 0) {
                    foreach ($arrayIter as $record2) {
                        if ($record2['level'] == $level + 1 && $record1['ITEKEY'] == $record2['ITEPRE']) {
                            $data2 = $record2['ITERDATA'];
                        }
                    }
                }
                if ($data2 > 0) {
                    $arrayIter[$key]['ITERGIORNI'] = $this->proLib->Diff_Date_toGiorni($data1, $data2) + 1;
                } else {
                    $arrayIter[$key]['ITERGIORNI'] = 1;
                }
            }
        }
        return $arrayIter;
    }

    private function calcolaTotaleGiorni($arrayIter) {
        $giorni = 0;
        $data1 = 99999999;
        $data2 = 0;
        foreach ($arrayIter as $record) {
            if ($record['ITERDATA'] < $data1 && $record['ITERDATA'] != '') {
                $data1 = $record['ITERDATA'];
            }
            if ($record['ITERCHIUSO'] > $data2) {
                $data2 = $record['ITERCHIUSO'];
            }
        }
        if ($data1 == 99999999 || $data2 == 99999999) {
            return 0;
        }
        if ($data2 > 0) {
            $giorni = $this->proLib->Diff_Date_toGiorni($data1, $data2) + 1;
        }
        return $giorni;
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
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
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaAllegati($numeroProtocollo, $tipoProt, $daFirma = false) {
        $proLibSdi = new proLibSdi();
        $visualizzaDaFirmare = false;
        //$destinazione = $this->proLib->SetDirectory($numeroProtocollo, $tipoProt);
        $sql = "SELECT * FROM ANADOC WHERE DOCKEY LIKE '" . $numeroProtocollo . $tipoProt . "%' ORDER BY DOCKEY ASC";
        $anadoc_tab = $this->proLib->getGenericTab($sql);
        $AnaproRec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', $tipoProt);
        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_45 = $this->proLib->GetAnaent('45');

        $fattEleSdi = '';
        if ($AnaproRec['PROCODTIPODOC']) {
            if ($anaent_38['ENTDE1'] == $AnaproRec['PROCODTIPODOC'] || $anaent_38['ENTDE2'] == $AnaproRec['PROCODTIPODOC'] ||
                    $anaent_38['ENTDE3'] == $AnaproRec['PROCODTIPODOC'] || $anaent_38['ENTDE4'] == $AnaproRec['PROCODTIPODOC'] || $AnaproRec['PROCODTIPODOC'] == $anaent_45['ENTDE5']) {
                $fattEleSdi = "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
            }
        }

        $AllegatiFirmati = false;
        foreach ($anadoc_tab as $anadoc_rec) {
            if (!$anadoc_rec['DOCSERVIZIO']) {
                $firma = "";
                $rowid = $anadoc_rec['ROWID'];
                $this->proIterAlle[$rowid] = $anadoc_rec;
//                $this->proIterAlle[$rowid]['FILEPATH'] = $destinazione . '/' . $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILEPATH'] = $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILENAME'] = $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILEINFO'] = $anadoc_rec['DOCNOT'];
                if ($anadoc_rec['DOCNAME'] == '') {
                    $anadoc_rec['DOCNAME'] = $anadoc_rec['DOCFIL'];
                }
                $ext = pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
                if (strtolower($ext) == "p7m") {
                    $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                }
//                else 
                if (!$this->visualizzazione && $this->currArcite['ITESTATO'] != '1' && $this->currArcite['ITESTATO'] != '3' && $this->currArcite['ITEGES'] != 0) {
                    $profilo = proSoggetto::getProfileFromIdUtente();
                    $docfirma_rec = $this->proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', false, " AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
                    //Controllo se questa è la trasmissione alla firma.
                    if ($docfirma_rec && $docfirma_rec['ROWIDARCITE'] == $this->currArcite['ROWID']) {
                        $firma = "<span class=\"ita-icon ita-icon-sigillo-24x24\">Da Firmare</span>";
                        $this->proIterAlle[$rowid]['DAFIRMARE'] = true;
                        $visualizzaDaFirmare = true;
                        if (strtolower($ext) == "p7m" && $docfirma_rec['FIRDATA'] != '') {
                            $this->proIterAlle[$rowid]['DAFIRMARE'] = false;
                            $visualizzaDaFirmare = false;
                            $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                            $AllegatiFirmati = true;
                        }
                    } else if (strtolower($ext) == "p7m") {
                        $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                    }
                }

                $this->proIterAlle[$rowid]['PROVENIENZAALLE'] = $anadoc_rec['DOCUTE'];
                $this->proIterAlle[$rowid]['FIRMA'] = $firma;
                $isFatturaPA = $proLibSdi->isAnadocFileFattura($AnaproRec['ROWID'], $anadoc_rec['DOCNAME']);
                $isMessaggioFatturaPA = $proLibSdi->isAnadocFileMessaggio($AnaproRec['ROWID'], $anadoc_rec['DOCNAME']);
                if ($isFatturaPA || $isMessaggioFatturaPA) {
                    $this->proIterAlle[$rowid]['SDI'] = $fattEleSdi;
                } else {
                    $this->proIterAlle[$rowid]['SDI'] = '';
                }
            }
        }
        if ($visualizzaDaFirmare) {
            Out::show($this->nameForm . '_VaiAllaFirma');
            $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATA<>''");
            if (!$docfirma_tab) {
                Out::show($this->nameForm . '_Rifiuta');
            } else {
                Out::hide($this->nameForm . '_Rifiuta');
            }
        } else {
            Out::hide($this->nameForm . '_VaiAllaFirma');
            Out::hide($this->nameForm . '_statoTrasmissione');
            // Solo se non sono il firmatario che ha firmato posso rifiutarla.
            $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATA<>'' AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
            if ($docfirma_tab) {
                Out::hide($this->nameForm . '_Rifiuta');
            }
        }
        /*
         * Controllo per allegati firmati: verifico se è documentale alla firma.
         */
        Out::hide($this->nameForm . '_ProtocollaDocumentale');
        if ($AllegatiFirmati) {
            if ($AnaproRec['PROPAR'] == 'I') {
                $Indice_rec = $this->segLib->GetIndice($AnaproRec['PRONUM'], 'anapro', false, $AnaproRec['PROPAR']);
                if ($Indice_rec['INDPREPAR']) {
                    // Visualizzo bottone: se non è già stato protocollato.
                    $ProDocProt_rec = $this->proLib->GetProDocProt($AnaproRec['PRONUM'], 'sorgnum', $AnaproRec['PROPAR']);
                    if (!$ProDocProt_rec) {
                        Out::show($this->nameForm . '_ProtocollaDocumentale');
                    }
                }
            }
        }


        $this->caricaGriglia($this->gridAllegati, $this->proIterAlle);
        /*
         * Conteggio Allegati
         */
        $tot = count($this->proIterAlle);
        $dafirmare = "";
        if ($tipoProt == "W")
            $dafirmare = "alla firma";
        Out::tabSetTitle($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegati", "Allegati $dafirmare <span style=\"color:red; font-weight:bold;\">($tot) </span>");

        /**
         * DA SISTEMARE
         */
        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATARICH<>''");
        if ($docfirma_tab) {
            Out::show($this->nameForm . '_Mail');
            $apri = true;
            foreach ($docfirma_tab as $docfirma_rec) {
                if (!$docfirma_rec['FIRDATA']) {
                    Out::hide($this->nameForm . '_Mail');
                    $apri = false;
                }
            }
            $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
            $proArriDest = $this->proLib->caricaDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $proAltriDestinatari = $this->proLib->caricaAltriDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], true);
            $destMap = $this->proLibMail->checkInvioAvvenuto($anapro_rec, $proArriDest, $proAltriDestinatari);
            if (!$destMap) {
                Out::hide($this->nameForm . '_Mail');
                $apri = false;
            }
            /*
             *  Lo eseguo solo se non è un passo 
             */

            if ($daFirma === true && $apri === true) {
                if ($tipoProt != 'W') {
                    $this->inviaMail();
                }
            }

//            else if ($daFirma === true) {
//                /* 19.05.2016
//                 * Se destinatario della trasmissione ha firmato, il suo iter viene chiuso.
//                 */
//                $this->chiudiIter("Firme effettuate correttamente.");
//            }
        }
    }

    private function impostoIter($codice) {
        $arcite_rec = $this->proLib->GetArcite($codice, $Tipo = 'itekey');

        $iter = proIter::getInstance($this->proLib, $arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
        $iter->leggiIterNode($arcite_rec);
    }

    private function EditAllegati() {
        if (array_key_exists($_POST[$this->gridAllegati]['gridParam']['selrow'], $this->proIterAlle) == true) {
            $fileName = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILENAME'];
            $filepath = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILEPATH'];
            $FileAllegato = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILENAME'];
            $rowid = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['ROWID'];
            if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                Out::msgQuestion("Download", "Cosa vuoi fare con il file eml selezionato?", array(
                    'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                    'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        )
                );
                $this->currAllegato = array("FileAllegato" => $FileAllegato, "FileDati" => $filepath, 'Rowid' => $rowid);
                return;
            }
            $force = false;
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) == 'xml') {
                $force = true;
            }
            $rowid = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['ROWID'];
            $Allegato = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']];
            if ($Allegato['DOCPAR'] != 'I') {
                $this->proLibAllegati->OpenDocAllegato($rowid, $force);
            } else {
                $this->segLibAllegati->openDocumento($this, $Allegato);
            }
        }
    }

    function caricaRisultatiGriglia() {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $sql = "SELECT * FROM ARCITE 
                    LEFT OUTER JOIN ANAPRO ANAPRO ON ARCITE.ITEPRO = ANAPRO.PRONUM AND ARCITE.ITEPAR = ANAPRO.PROPAR
                WHERE  ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " AND ITEDES<>'' AND ITESTATO<>1 AND ITESTATO<>3"; // NON PIU USATO.
        if ($_POST[$this->nameForm . '_chiusi'] <> '1') {
            $sql .= " AND ITEFIN=''";
        }
        if ($_POST[$this->nameForm . '_inviati'] <> '1') {
            $sql .= " AND ITESUS=''";
        }
        if ($_POST[$this->nameForm . '_dalPeriodo'] <> '') {
            $sql .= " AND ITEDAT>='" . $_POST[$this->nameForm . '_dalPeriodo'] . "'";
        }
        if ($_POST[$this->nameForm . '_alPeriodo'] <> '') {
            $sql .= " AND ITEDAT<='" . $_POST[$this->nameForm . '_alPeriodo'] . "'";
        }

        $anno = $_POST[$this->nameForm . '_annoProt'];
        $dpr = $_POST[$this->nameForm . '_dalProt'];
        $apr = $_POST[$this->nameForm . '_alProt'];
        if ($anno == '') {
            $anno = date("Y");
        }
        $anno2 = $anno + 1;
        $sql .= " AND (ITEPRO BETWEEN '" . $anno . "000000' AND '" . $anno2 . "000000')";
        if ($dpr) {
            $dpr = $anno * 1000000 + $dpr;
            if ($apr) {
                $apr = $anno * 1000000 + $apr;
            } else {
                $apr = $anno * 1000000 + 999999;
            }
            $sql .= " AND (ITEPRO BETWEEN $dpr AND $apr)";
        } else if ($apr) {
            $dpr = $anno * 1000000 + 1;
            $apr = $anno * 1000000 + $apr;
            $sql .= " AND (ITEPRO BETWEEN $dpr AND $apr)";
        }

        if ($_POST[$this->nameForm . '_tipoTrasm'] <> '') {
            $sql .= " AND ITEPAR='" . $_POST[$this->nameForm . '_tipoTrasm'] . "'";
        }
        if ($_POST[$this->nameForm . '_scaduti'] <> '1') {
            $sql .= " AND (ITETERMINE='' OR ITETERMINE>='$this->workDate')";
        }
        $sql .= " AND (ITEDES BETWEEN '" . $profilo['COD_SOGGETTO'] . "' AND '" . $profilo['COD_SOGGETTO'] . "')";
        $sql .= " GROUP BY ITEPRO,ITEPAR ORDER BY ITEEVIDENZA DESC, ITEDAT DESC, ITEPRO DESC";
        $arcite_tab = $this->proLib->getGenericTab($sql);
        $this->tabella = array();
        foreach ($arcite_tab as $arcite_rec) {
            $record = array();
            $codice = $arcite_rec['ITEPRO'];
            $record['ROWID'] = $arcite_rec['ROWID'];
            $record['ITEPAR'] = $arcite_rec['ITEPAR'];
            $record['ANNO'] = substr($codice, 0, 4);
            $record['NUMERO'] = intval(substr($codice, 4));
            $record['ITEDAT'] = $arcite_rec['ITEDAT'];
            $record['ITETERMINE'] = '';
            $anaogg_rec = $this->proLib->GetAnaogg($codice, $arcite_rec['ITEPAR']);
            $record['OGGETTO'] = $anaogg_rec['OGGOGG'];
            $anapro_rec = $this->proLib->GetAnapro($codice, 'codice', $arcite_rec['ITEPAR']);
            $record['PROVENIENZA'] = $anapro_rec['PRONOM'];
            if ($arcite_rec['ITEDLE'] != '') {
                if ($arcite_rec['ITEGES'] != 1) {
                    $record['STATO'] = '<span class="ita-icon ita-icon-apertagray-24x24"></span>';
                } else {
                    $record['STATO'] = '<span class="ita-icon ita-icon-apertagreen-24x24"></span>';
                }
            } else {
                if ($arcite_rec['ITEGES'] != 1) {
                    $record['STATO'] = '<span class="ita-icon ita-icon-chiusagray-24x24"></span>';
                } else {
                    $record['STATO'] = '<span class="ita-icon ita-icon-chiusagreen-24x24"></span>';
                }
            }
            if ($arcite_rec['ITESUS'] != '') {
                $record['STATO'] = '<span class="ita-icon ita-icon-inoltrata-24x24"></span>';
            }
            if ($arcite_rec['ITETERMINE'] <> '' && $arcite_rec['ITETERMINE'] < $this->workDate) {
                $record['STATO'] = '<span class="ita-icon ita-icon-lock-24x24"></span>';
            }
            if ($arcite_rec['ITEFLA'] == 2) {
                $record['STATO'] = '<span class="ita-icon ita-icon-check-red-24x24"></span>';
            }
            $anades_tab = $this->proLib->GetAnades($codice, 'codice', true, $arcite_rec['ITEPAR'], 'T');
            $record['NDESTPROT'] = count($anades_tab);
            $arcitecount_tab = $this->proLib->GetArcite($codice, 'codice', true, $arcite_rec['ITEPAR']);
            $record['NDESTITER'] = count($arcitecount_tab);
            $ini_tag = "<p style = 'font-weight:lighter;'>";
            $fin_tag = "</p>";
            if ($arcite_rec['ITEEVIDENZA'] == 1) {
                $ini_tag = "<p style = 'font-weight:900;color:#BE0000;'>";
                $fin_tag = "</p>";
            }
            $record['ITEPAR'] = $ini_tag . $record['ITEPAR'] . $fin_tag;
            $record['ANNO'] = $ini_tag . $record['ANNO'] . $fin_tag;
            $record['NUMERO'] = $ini_tag . $record['NUMERO'] . $fin_tag;
            $record['ITEDAT'] = $ini_tag . date('d/m/Y', strtotime($record['ITEDAT'])) . $fin_tag;
            $record['OGGETTO'] = $ini_tag . $record['OGGETTO'] . $fin_tag;
            $record['PROVENIENZA'] = $ini_tag . $record['PROVENIENZA'] . $fin_tag;
            $record['NDESTPROT'] = $ini_tag . $record['NDESTPROT'] . $fin_tag;
            $record['NDESTITER'] = $ini_tag . $record['NDESTITER'] . $fin_tag;
            if ($record['ITETERMINE']) {
                $record['ITETERMINE'] = substr($record['ITETERMINE'], 6) . '/' . substr($record['ITETERMINE'], 4, 2) . '/' . substr($record['ITETERMINE'], 0, 4);
            }
            $record['ITETERMINE'] = $ini_tag . $record['ITETERMINE'] . $fin_tag;
            $this->tabella[] = $record;
            //}
        }
    }

    private function caricaDestinatari($codiceSoggetto, $codiceUfficio, $desges = 1) {
        $soggetto = proSoggetto::getInstance($this->proLib, $codiceSoggetto, $codiceUfficio);
        if (!$soggetto) {
            return false;
        }
        $record = $soggetto->getSoggetto();
        $inserisci = true;
        foreach ($this->proIterDest as $value) {
            if ($record['CODICESOGGETTO'] == $value['DESCODADD']) {
                $inserisci = false;
                break;
            }
        }
        if ($inserisci == true) {
            /*
             * Check Anamed Valido: se annullato lo salto.
             */
            $Anamed_rec = $this->proLib->GetAnamed($record['CODICESOGGETTO'], 'codice', 'no');
            if (!$Anamed_rec) {
                return true;
            }
            $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
            $risultato = $iter->checkEsistenzaUtente($record['CODICESOGGETTO']);
            if ($risultato == proIter::ITECHECK_NONCREARE) {
                Out::msgStop("Attenzione!", "Il Destinatario selezionato non ha un accesso al sistema, non può essere caricato.");
                return false;
            }

            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESCODADD'] = $record['CODICESOGGETTO'];
            $salvaDest['DESNOMADD'] = $record['DESCRIZIONESOGGETTO'];
            if ($record['RUOLO']) {
                $salvaDest['DESNOMADD'] .= " - " . $record['RUOLO'];
            }
            $salvaDest['DESNOMADD'] .= " - " . $record['DESCRIZIONEUFFICIO'];
            if ($record['SERVIZIO']) {
                $salvaDest['DESNOMADD'] .= " - " . $record['SERVIZIO'];
            }

            $salvaDest['ITEUFF'] = $record['CODICEUFFICIO'];
            $salvaDest['ITERUO'] = $record['CODICERUOLO'];
            $salvaDest['ITESETT'] = $record['CODICESERVIZIO'];

            $salvaDest['DESGESADD'] = $desges;
            if (count($this->proIterDest) > 0) {
                // $salvaDest['DESGESADD'] = 0;
            }
            $salvaDest['TERMINE'] = '';
            $this->proIterDest[] = $salvaDest;
            Out::show($this->nameForm . '_divDestGrid');
            $this->CaricaGriglia($this->gridDestinatari, $this->proIterDest);
        }
    }

    private function caricaTrasmUfficio($codiceUfficio, $desges = 1) {
        $inserisci = true;
        foreach ($this->proIterDest as $value) {
            if ($codiceUfficio == $value['ITEUFF']) {
                $inserisci = false;
                break;
            }
        }

        if ($inserisci == true) {
            $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
            $Anauff_rec = $this->proLib->GetAnauff($codiceUfficio, 'codice');

            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESCODADD'] = '';
            $salvaDest['DESNOMADD'] = '<p style = " background:#B2F7DC;">TRASMISSIONE A INTERO UFFICIO';
            $salvaDest['DESNOMADD'] .= " - " . $Anauff_rec['UFFDES'] . "</p>";
            $salvaDest['ITEUFF'] = $codiceUfficio;
            $salvaDest['DESGESADD'] = $desges;
            $salvaDest['TRASMUFFICIO'] = $desges;
            if (count($this->proIterDest) > 0) {
                // $salvaDest['DESGESADD'] = 0;
            }
            $salvaDest['TERMINE'] = '';
            $this->proIterDest[] = $salvaDest;
            Out::show($this->nameForm . '_divDestGrid');
            $this->CaricaGriglia($this->gridDestinatari, $this->proIterDest);
        }
    }

    private function DecodAnacat($versione_t, $codice, $tipo = 'codice') {
        $anacat_rec = $this->proLib->GetAnacat($versione_t, $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
            Out::valore($this->nameForm . '_DescTitolario', '');
        }
        return $anacat_rec;
    }

//    function DecodAnacla($codice, $tipo = 'codice') {
//        Out::valore($this->nameForm . '_Clacod', '');
//        Out::valore($this->nameForm . '_Fascod', '');
//        Out::valore($this->nameForm . '_DescTitolario', '');
//        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
//        if ($anacla_rec) {
//            Out::valore($this->nameForm . '_Clacod', $anacla_rec['CLACOD']);
//            Out::valore($this->nameForm . '_clades', $anacla_rec['CLADE1'] . $anacla_rec['CLADE2']);
////            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
//        }
//        return $anacla_rec;
//    }

    private function DecodAnacla($versione_t, $codice, $tipo = 'codice') {
        $anacla_rec = $this->proLib->GetAnacla($versione_t, $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anacla_rec;
    }

//    function DecodAnafas($codice, $tipo = 'fasccf') {
//        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
//        Out::valore($this->nameForm . '_Fascod', $anafas_rec['FASCOD']);
//        Out::valore($this->nameForm . '_fasdes', $anafas_rec['FASDES']);
////            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
//        return $anafas_rec;
//    }

    private function DecodAnafas($versione_t, $codice, $tipo = 'fasccf') {
        $anafas_rec = $this->proLib->GetAnafas($versione_t, $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anafas_rec;
    }

    function DecodAnaorg($codice, $tipo = 'codice', $codiceCcf = '') {
        $anaorg_rec = $this->proLib->GetAnaorg($codice, $tipo, $codiceCcf);
        if ($anaorg_rec) {
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', $anaorg_rec['ORGCOD']);
            Out::valore($this->nameForm . '_Organn', $anaorg_rec['ORGANN']);
            Out::valore($this->nameForm . '_FascicoloDecod', $anaorg_rec['ORGDES']);
            $retChkSottoFas = $this->proLibFascicolo->ChkProtoInSottoFascicolo($this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
            Out::valore($this->nameForm . '_CodSottofascicolo', $retChkSottoFas['NUMERO']);
            Out::valore($this->nameForm . '_SottoFascicoloDecod', $retChkSottoFas['DESCRIZIONE']);
        } else {
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
            Out::valore($this->nameForm . '_Organn', '');
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            Out::valore($this->nameForm . '_CodSottofascicolo', '');
            Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
        }
        return $anaorg_rec;
    }

//    function OlddecodTitolario($chiave, $tipoArc, $tipoChiave = 'rowid') {
////        $cat = $cla = $fas = $org = $des = '';
//        Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
//        Out::valore($this->nameForm . '_Clacod', '');
//        Out::valore($this->nameForm . '_Fascod', '');
//        Out::valore($this->nameForm . '_catdes', '');
//        Out::valore($this->nameForm . '_clades', '');
//        Out::valore($this->nameForm . '_fasdes', '');
//        switch ($tipoArc) {
//            case 'ANACAT':
//                $this->DecodAnacat($chiave, $tipoChiave);
////                $anacat_rec = $this->proLib->GetAnacat('', $chiave, $tipoChiave);
////                $cat = $anacat_rec['CATCOD'];
////                Out::valore($this->nameForm . '_catdes', $anacat_rec['CATDES']);
//                break;
//            case 'ANACLA':
//                $anacla_rec = $this->proLib->GetAnacla('', $chiave, $tipoChiave);
//                $this->DecodAnacat($anacla_rec['CLACAT'], 'codice');
//                $this->DecodAnacla($chiave, $tipoChiave);
////                $anacla_rec = $this->proLib->GetAnacla('', $chiave, $tipoChiave);
////                $cat = $anacla_rec['CLACAT'];
////                $cla = $anacla_rec['CLACOD'];
////                Out::valore($this->nameForm . '_clades', $anacla_rec['CLADE1'] . $anacla_rec['CLADE2']);
//                break;
//            case 'ANAFAS':
//                $anafas_rec = $this->proLib->GetAnafas('', $chiave, $tipoChiave);
//                $this->DecodAnacla($anafas_rec['FASCCA'], 'codice');
//                $this->DecodAnacat(substr($anafas_rec['FASCCA'], 0, 4), 'codice');
//                $this->DecodAnafas($chiave, $tipoChiave);
////                $anafas_rec = $this->proLib->GetAnafas('', $chiave, $tipoChiave);
////                $cat = substr($anafas_rec['FASCCA'], 0, 4);
////                $cla = substr($anafas_rec['FASCCA'], 4);
////                $fas = $anafas_rec['FASCOD'];
////                Out::valore($this->nameForm . '_fasdes', $anafas_rec['FASDES']);
//                break;
//        }
////        Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $cat);
////        Out::valore($this->nameForm . '_Clacod', $cla);
////        Out::valore($this->nameForm . '_Fascod', $fas);
//    }

    private function decodTitolario($rowid, $tipoArc, $tipoChiave = 'rowid') {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, $tipoChiave);
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, $tipoChiave);
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, $tipoChiave);
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        // Da testare bene. @TODO - Alle
        if (!$cat && !$cla && !$fas && $des) {
            Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
            Out::valore($this->nameForm . '_DescTitolario', '');
        } else {
            if ($cat) {
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $cat);
            }
            if ($cla) {
                Out::valore($this->nameForm . '_Clacod', $cla);
            }
            if ($fas) {
                Out::valore($this->nameForm . '_Fascod', $fas);
            }
            if ($des) {
                Out::valore($this->nameForm . '_DescTitolario', $des);
            }
        }
    }

    private function checkPresenzaDestinatari() {
        $pronum = $_POST[$this->nameForm . '_ANAPRO']['PRONUM'];
        $propar = $_POST[$this->nameForm . '_ANAPRO']['PROPAR'];
        foreach ($this->proIterDest as $destinatario) {
            $risultato = $this->proLib->getGenericTab(
                    "SELECT ROWID FROM ARCITE WHERE ITEPRO=$pronum AND ITEPAR='$propar' AND ITEDES='{$destinatario['DESCODADD']}' AND ITENODO<>'INS' AND ITENODO<>'ANN' AND ITENODO<>'MAN'"
                    , false);
            if ($risultato) {
                return true;
            }
        }
        return false;
    }

    private function caricaGriglieArrivo($anapro_rec) {
        TableView::setLabel($this->nameForm . '_gridMittentiProt', 'MITNOME', 'Mittenti');
        $mittenti_tab = $this->proLib->getGenericTab("SELECT PRONOM AS MITNOME FROM PROMITAGG WHERE PROPAR='{$anapro_rec['PROPAR']}' AND PRONUM=" . $anapro_rec['PRONUM']);
        array_unshift($mittenti_tab, array('MITNOME' => $anapro_rec['PRONOM']));
        $this->CaricaGriglia($this->nameForm . '_gridMittentiProt', $mittenti_tab);

        $destinatari_tab = $this->proLib->getGenericTab(
                "SELECT DESNOM AS DESNOME, DESTIPO, DESIDMAIL,DESCOD,DESCUF FROM ANADES WHERE DESTIPO='T' AND DESPAR='{$anapro_rec['PROPAR']}' AND DESNUM={$anapro_rec['PRONUM']} ORDER BY DESTIPO ASC");
        foreach ($destinatari_tab as $key => $value) {
            if ($value['DESIDMAIL']) {
                $destinatari_tab[$key]['MAILDEST'] = '<span class="ui-icon ui-icon-mail-closed"></span>';
                $retRic = $this->proLib->checkMailRic($value['DESIDMAIL']);
                if ($retRic['ACCETTAZIONE']) {
                    $destinatari_tab[$key]['ACCETTAZIONE'] = '<span class="ui-icon ui-icon-check"></span>';
                    $destinatari_tab[$key]['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                }
                if ($retRic['CONSEGNA']) {
                    $destinatari_tab[$key]['CONSEGNA'] = '<span class="ui-icon ui-icon-check"></span>';
                    $destinatari_tab[$key]['IDCONSEGNA'] = $retRic['CONSEGNA'];
                }
            }
            /* Trasmissione ufficio, specifico quale */
            if (!$value['DESCOD'] && $value['DESCUF']) {
                $Anauff_rec = $this->proLib->GetAnauff($value['DESCUF'], 'codice');
                $destinatari_tab[$key]['DESNOME'] .= ' - ' . $Anauff_rec['UFFDES'];
            }
        }
        $this->elencoDestinatari = $destinatari_tab;
        $this->CaricaGriglia($this->nameForm . '_gridDestinatariProtocollo', $this->elencoDestinatari);
    }

    private function caricaGrigliePartenza($anapro_rec) {
//        Out::codice("$('#{$this->nameForm}_gridMittentiProt').jqGrid('setLabel', 0, 'Firmatari');");
//        Out::codice("$('#{$this->nameForm}_gridMittentiProt').jqGrid('setLabel', 'MITNOME', 'Firmatari');");
        TableView::setLabel($this->nameForm . '_gridMittentiProt', 'MITNOME', 'Firmatari');

        $firmatari_tab = $this->proLib->getGenericTab("SELECT PRONOM AS MITNOME FROM PROMITAGG WHERE PROPAR='{$anapro_rec['PROPAR']}' AND PRONUM=" . $anapro_rec['PRONUM']);

        $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');

        array_unshift($firmatari_tab, array('MITNOME' => $anades_mitt['DESNOM']));
        $this->CaricaGriglia($this->nameForm . '_gridMittentiProt', $firmatari_tab);

        $destinatari_tab = $this->proLib->getGenericTab("SELECT DESNOM AS DESNOME, DESTIPO, DESIDMAIL FROM ANADES WHERE (DESTIPO='D' OR DESTIPO='T') AND DESPAR='{$anapro_rec['PROPAR']}' AND DESNUM=" . $anapro_rec['PRONUM'] . " ORDER BY DESTIPO ASC");
        if ($this->tipoProt == 'P') {
            array_unshift($destinatari_tab, array('DESNOME' => $anapro_rec['PRONOM'], 'DESTIPO' => 'D', 'DESIDMAIL' => $anapro_rec['PROIDMAILDEST']));
        }
        foreach ($destinatari_tab as $key => $value) {
            if ($value['DESIDMAIL']) {
                $destinatari_tab[$key]['MAILDEST'] = '<span class="ui-icon ui-icon-mail-closed"></span>';
                $retRic = $this->proLib->checkMailRic($value['DESIDMAIL']);
                if ($retRic['ACCETTAZIONE']) {
                    $destinatari_tab[$key]['ACCETTAZIONE'] = '<span class="ui-icon ui-icon-check"></span>';
                    $destinatari_tab[$key]['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                }
                if ($retRic['CONSEGNA']) {
                    $destinatari_tab[$key]['CONSEGNA'] = '<span class="ui-icon ui-icon-check"></span>';
                    $destinatari_tab[$key]['IDCONSEGNA'] = $retRic['CONSEGNA'];
                }
            }
        }
        $this->elencoDestinatari = $destinatari_tab;
        $this->CaricaGriglia($this->nameForm . '_gridDestinatariProtocollo', $this->elencoDestinatari);
    }

    private function caricaDivTrasmissioni($arcite_pre, $arcite_rec) {
        $mitt = proSoggetto::getInstance($this->proLib, $arcite_pre['ITEDES'], $arcite_pre['ITEUFF']);
        $mitt_dati = $mitt->getSoggetto();

        $mittenteTrasm = $mitt_dati['DESCRIZIONESOGGETTO'];

        if ($mittenteTrasm) {
            if ($mitt_dati['RUOLO']) {
                $mittenteTrasm .= ' - ' . $mitt_dati['RUOLO'];
            }
            $mittenteTrasm .= ' - ' . $mitt_dati['DESCRIZIONEUFFICIO'];
            if ($mitt_dati['SERVIZIO']) {
                $mittenteTrasm .= ' - ' . $mitt_dati['SERVIZIO'];
            }
        }
        $dataTrasm = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
        $scadenza = "";

        if ($arcite_rec['ITETERMINE']) {
            $scadenza = "Scadenza: " . date("d/m/Y", strtotime($arcite_rec['ITETERMINE'])) . ". ";
        }
        $gest = "Consultazione";
        if ($arcite_rec['ITEGES'] == '1') {
            $gest = "Gestione";
        }

        $annotazioni = '&nbsp;';
        if ($arcite_rec['ITEANN']) {
            $annotazioni = $arcite_rec['ITEANN'];
        }


        $infoTrasmissione = "
            <table border=\"0\">
                <tr>
                    <td style=\"width:150px\"> Mittente Trasmissione: </td><td style=\"width:330px\" class=\"ui-state-highlight\"> $mittenteTrasm </td>
                </tr>
                <tr>
                    <td style=\"width:150px\"> Data Trasmissione: </td><td style=\"width:330px\" class=\"ui-state-highlight\"> $dataTrasm </td>
                </tr>
                <tr>
                    <td style=\"width:150px\"> Oggetto Trasmissione: </td><td style=\"width:330px\" class=\"ui-state-highlight\"> $annotazioni </td>
                </tr>
                <tr>
                    <td style=\"width:150px\"> $scadenza </td><td style=\"width:330px\" class=\"ui-state-highlight\"> Protocollo in $gest </td>
                </tr>
            </table>
                ";
        Out::html($this->nameForm . "_divDatiTrasm", $infoTrasmissione);
    }

    private function scaricaDest($codice, $tipo = 'codice') {
        if (trim($codice) != "") {
            if ($tipo == 'codice') {
                if (is_numeric($codice)) {
                    $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                }
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no', false, true);
            if ($anamed_rec) {
                $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
                $risultato = $iter->checkEsistenzaUtente($anamed_rec['MEDCOD']);
                if ($risultato == proIter::ITECHECK_NONCREARE) {
                    Out::msgStop("Attenzione!", "Il Destinatario selezionato non ha un accesso al sistema, non può essere caricato.");
                    return;
                }
                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND UFFDES.UFFCESVAL='' AND ANAUFF.UFFANN=0");
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    foreach ($this->proArriDest as $key => $destinatario) {
                        if ($destinatario['DESCOD'] == $codice) {
                            $this->proArriDest[$key]['DESCUF'] = $anauff_rec['UFFCOD'];
                            break;
                        }
                    }
                    $this->caricaDestinatari($codice, $anauff_rec['UFFCOD']);
                } else {
                    $this->appoggio = $codice;
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', $codice);
                }
            }
        }
        Out::valore($this->nameForm . "_Dest_cod", "");
        Out::valore($this->nameForm . '_Dest_nome', "");
    }

    private function scaricaDaCodiceUfficio($codice, $tipo = 'codice') {
        if (trim($codice) != "") {
            if ($tipo == 'codice') {
                if (is_numeric($codice)) {
                    $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                } else {
                    $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                }
            }
            $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        } else {
            return;
            ;
        }
        if ($anauff_rec) {
            $this->scaricaUfficio($anauff_rec);
        }
        Out::valore($this->nameForm . '_Uff_des', "");
        Out::valore($this->nameForm . "_Uff_cod", "");
    }

    function scaricaUfficio($anauff_rec, $trasmUfficio = false) {
        if ($anauff_rec['UFFANN'] == 1) {
            Out::msgInfo("Decodifica Ufficio", "ATTENZIONE.<BR>Uffico " . $anauff_rec['UFFCOD'] . "  " . $anauff_rec['UFFDES'] . " non più utilizzabile. Annullato.");
        } else if ($anauff_rec) {
            if ($trasmUfficio) {
                $this->caricaTrasmUfficio($anauff_rec['UFFCOD']);
            } else {
                $uffdes_tab = $this->proLib->GetUffdes($anauff_rec['UFFCOD'], 'uffcod', true, ' ORDER BY UFFFI1__3 DESC', true);
                foreach ($uffdes_tab as $uffdes_rec) {
                    if ($uffdes_rec['UFFSCA']) {
                        $ges = $uffdes_rec['UFFFI1__1'];
                        if (!$trasmUfficio) {
                            $this->caricaDestinatari($uffdes_rec['UFFKEY'], $anauff_rec['UFFCOD'], $ges);
                        }
                    }
                }
            }
        }
    }

    private function scaricaServizio($codice, $tipo = 'codice') {
        $anaservizi_rec = $this->proLib->getAnaservizi($codice, $tipo);
        if ($anaservizi_rec) {
            $anauff_tab = $this->proLib->GetAnauff($anaservizi_rec['SERCOD'], 'uffser');
            foreach ($anauff_tab as $anauff_rec) {
                $this->scaricaUfficio($anauff_rec);
            }
        }
        Out::valore($this->nameForm . '_sercod', "");
        Out::valore($this->nameForm . "_serdes", "");
    }

    private function bloccaDati() {
        Out::block($this->nameForm . '_divInsert', '#000000', '0.1');
        Out::addClass($this->nameForm . '_Dest_cod', "ita-readonly");
        Out::addClass($this->nameForm . '_Dest_nome', "ita-readonly");
        Out::addClass($this->nameForm . '_Uff_cod', "ita-readonly");
        Out::addClass($this->nameForm . '_Uff_des', "ita-readonly");
        Out::addClass($this->nameForm . '_sercod', "ita-readonly");
        Out::addClass($this->nameForm . '_serdes', "ita-readonly");
        Out::attributo($this->nameForm . '_Dest_cod', "readonly", '0');
        Out::attributo($this->nameForm . '_Dest_nome', "readonly", '0');
        Out::attributo($this->nameForm . '_Uff_cod', "readonly", '0');
        Out::attributo($this->nameForm . '_Uff_des', "readonly", '0');
        Out::attributo($this->nameForm . '_sercod', "readonly", '0');
        Out::attributo($this->nameForm . '_serdes', "readonly", '0');
    }

    private function sbloccaDati() {
        Out::unBlock($this->nameForm . '_divInsert');
        Out::delClass($this->nameForm . '_Dest_cod', "ita-readonly");
        Out::delClass($this->nameForm . '_Dest_nome', "ita-readonly");
        Out::delClass($this->nameForm . '_Uff_cod', "ita-readonly");
        Out::delClass($this->nameForm . '_Uff_des', "ita-readonly");
        Out::delClass($this->nameForm . '_sercod', "ita-readonly");
        Out::delClass($this->nameForm . '_serdes', "ita-readonly");
        Out::attributo($this->nameForm . '_Dest_cod', "readonly", '1');
        Out::attributo($this->nameForm . '_Dest_nome', "readonly", '1');
        Out::attributo($this->nameForm . '_Uff_cod', "readonly", '1');
        Out::attributo($this->nameForm . '_Uff_des', "readonly", '1');
        Out::attributo($this->nameForm . '_sercod', "readonly", '1');
        Out::attributo($this->nameForm . '_serdes', "readonly", '1');
    }

    private function caricaNote() {
        $datiGrigliaNote = array();
        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $datiGrigliaNote[$key]['NOTE'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 7px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';
            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
                if (strlen($testo) > 45) {
                    $testo = substr($testo, 0, 45);
                }
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 9px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }

        $tot = count($this->noteManager->getNote());
        Out::tabSetTitle($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneNote", 'Note <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    private function inserisciNotifica($oggetto, $testo, $uteins) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        $env_notifiche = array();
        $env_notifiche['OGGETTO'] = $oggetto;
        $env_notifiche['TESTO'] = $testo;
        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
        $env_notifiche['MODELINS'] = $this->nameForm;
        $env_notifiche['DATAINS'] = date("Ymd");
        $env_notifiche['ORAINS'] = date("H:i:s");
        $env_notifiche['UTEDEST'] = $uteins;
        $env_notifiche['ACTIONMODEL'] = 'proOpenProtDaNotifica';
        $env_notifiche['ACTIONPARAM'] = serialize(array('setOpenMode' => array('OpenProtocollo'), 'setOpenRowid' => array($Anapro_rec['ROWID']), 'setTipoOpen' => array('visualizzazione')));
        $insert_Info = 'Oggetto notifica: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
        $this->insertRecord($ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $insert_Info);
    }

    private function inviaTrasmissione() {
        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
        if ($this->tipoProt == 'F' || $this->tipoProt == 'N') {
            $Note = "ESTENSIONE VISIBILITA' FASCICOLO. ";
            if ($this->tipoProt == 'N') {
                $Note = "ESTENSIONE VISIBILITA' SOTTOFASCICOLO. ";
            }
            $Note .= $_POST[$this->nameForm . '_Annotazioni'];
            $extraParm = array(
                "NOTE" => $Note,
                "NODO" => "ASF"
            );
        } else {
            $extraParm = array(
                "NOTE" => $_POST[$this->nameForm . '_Annotazioni']
            );
        }


        if (count($this->proIterDest) > 0) {

            foreach ($this->proIterDest as $proDest) {
                $destinatario = array(
                    "DESCUF" => $proDest['ITEUFF'],
                    "DESCOD" => $proDest['DESCODADD'],
                    "DESGES" => $proDest['DESGESADD'],
                    "DESTERMINE" => $proDest['TERMINE'],
                    "ITEBASE" => 0
                );

                $iter = proIter::getInstance($this->proLib, $arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
                $iter->insertIterNode($destinatario, $arcite_rec, $extraParm);
                $arcite_rec = $this->proLib->GetArcite($arcite_rec['ROWID'], 'rowid');
            }
            $this->trasmettiPerConsegnaCartaceo();
        } else {
//            Out::msgBlock('', 2000, true, "Nessun destinatario è stato selezionato.");
            Out::msgStop("Attenzione!!!", "Nessun destinatario è stato selezionato.");
        }
        if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {
            Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
            return;
        }

        $this->ricaricaFascicolo();
        $this->openDettaglio($_POST[$this->nameForm . '_ARCITE']['ROWID']);
        $this->ricaricaPortlet();
    }

    private function passoSelezionato() {//$propas_rec) {
        $fascicolo_rec = $this->proLib->GetAnaorg($this->appoggio['ROWID'], 'rowid');
        $anapro_rec = $this->proLib->GetAnapro($this->formData[$this->nameForm . '_ANAPRO']['PRONUM'], 'codice', $this->formData[$this->nameForm . '_ANAPRO']['PROPAR']);
        $anapro_rec['PROCAT'] = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'];
        $anapro_rec['PROCCA'] = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'] . $this->formData[$this->nameForm . '_Clacod'];
        $arcite_rec = $this->proLib->GetArcite($this->formData[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
        $anapro_rec['PROARG'] = $fascicolo_rec['ORGCOD'];
        $anapro_rec['PROFASKEY'] = $fascicolo_rec['ORGKEY'];
        $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info);
        $this->openDettaglio($arcite_rec['ROWID']);
    }

    private function ricaricaFascicolo() {
        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        if ($anapro_rec['PROFASKEY']) {
            Out::broadcastMessage($this->nameForm, "UPDATE_FASCICOLO", array('GESKEY' => $anapro_rec['PROFASKEY'], 'PRONUM' => $anapro_rec['PRONUM'], 'PROPAR' => $anapro_rec['PROPAR']));
        }
    }

    private function ricaricaPortlet() {
        Out::broadcastMessage($this->nameForm, "RELOAD_GRID_PORTLET", array('a' => 'a'));
    }

    private function salvaDocumentoFirmato($rowidAnadoc, $outputFilePath, $inputFilePath, $FilenameFirmato) {
        $anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, "ROWID");
        $Anapro_rec = $this->proLib->GetAnapro($anadoc_rec['DOCNUM'], 'codice', $anadoc_rec['DOCPAR']);
        $DocFil_Input = $anadoc_rec['DOCFIL'];
        /*
         * Sposto il file firmato:
         */
        if ($anadoc_rec['DOCPAR'] == 'I') {
            $Indice_rec = $this->segLib->GetIndice($anadoc_rec['DOCNUM'], 'anapro', false, $anadoc_rec['DOCPAR']);
            $protPath = $this->segLibAllegati->SetDirectory($Indice_rec, $anadoc_rec['DOCTIPO'], false);
        } else {
            $protPath = $this->proLib->SetDirectory($anadoc_rec['DOCNUM'], $anadoc_rec['DOCPAR']);
        }


        $fileName = $anadoc_rec['DOCNAME'] . '.p7m';
        $FileNameDest = $anadoc_rec['DOCFIL'] . '.p7m';

        $FileDest = $protPath . "/" . $FileNameDest;
        // Sposto dalla cartella temporanea alla cartella del prot.
        if (!@rename($outputFilePath, $FileDest)) {
            Out::msgStop("Salvataggio File", "Errore in salvataggio del file " . $fileName . " !");
            return false;
        }
        if (!$FilenameFirmato) {
            $FilenameFirmato = $fileName;
        }

        /*
         *  Inserisco l'anadocsave
         */
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anadocSave_rec = $anadoc_rec;
        $anadocSave_rec['ROWID'] = '';
        $anadocSave_rec['SAVEDATA'] = $savedata;
        $anadocSave_rec['SAVEORA'] = $saveora;
        $anadocSave_rec['SAVEUTENTE'] = $saveutente;
        if (!$this->insertRecord($this->PROT_DB, 'ANADOCSAVE', $anadocSave_rec, '', 'ROWID', false)) {
            Out::msgStop("Firma File", "Errore in salvataggio ANADOCSAVE.");
            return false;
        }
        $anadoc_rec['DOCUUID'] = '';
        /* Se attivo parametri alfresco - salvo su alfresco */
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1'] && $anadoc_rec['DOCPAR'] != 'I') {
            $anapro_rec = $this->proLib->getAnapro($anadoc_rec['DOCNUM'], 'codice', $anadoc_rec['DOCPAR']);
            $Uuid = $this->proLibAllegati->AggiungiAllegatoAlfresco($anapro_rec, $FileDest, $FilenameFirmato);
            if (!$Uuid) {
                Out::msgStop('Attenzione', 'Errore in salvataggio file firmato.');
                return false;
            }
            $anadoc_rec['DOCUUID'] = $Uuid;
        }
        if (strpos('allegato://', $anadoc_rec['DOCLNK']) !== false) {
            $anadoc_rec['DOCLNK'] = "allegato://" . pathinfo($FileDest, PATHINFO_BASENAME);
        }
        $anadoc_rec['DOCFIL'] = pathinfo($FileDest, PATHINFO_BASENAME);
        $anadoc_rec['DOCNAME'] = $FilenameFirmato;
        $anadoc_rec['DOCDATAFIRMA'] = date("Ymd");
        $anadoc_rec['DOCNOT'] = 'Firmato: ' . $anadoc_rec['DOCNOT'];
        $anadoc_rec['DOCMD5'] = md5_file($FileDest);
        $anadoc_rec['DOCSHA2'] = hash_file('sha256', $FileDest);
        $update_Info = 'Oggetto: Aggiornamento allegato ' . $anadoc_rec['DOCNAME'];
        if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
            Out::msgStop("Firma remota", "Aggiornamento dati documento " . $anadoc_rec['DOCNAME'] . " fallito");
            return false;
        }
        // @TODO se ci sarà firma di un p7m, controllare l'unlink o farlo prima del rename.
//        $FileInInput = $protPath . "/" . $DocFil_Input;
//        if (!@unlink($FileInInput)) {
//            Out::msgStop("Firma remota", "cancellazione file " . $FileInInput . " fallita.... il procedimento continua.");
//        }
        /*
         * Controllo se è un registro giornaliero firmato, e salvo il metadato.
         */
        if ($Anapro_rec['PROPAR'] == 'C') {
            $anaent_41 = $this->proLib->GetAnaent('41');
            if ($anaent_41['ENTVAL'] && $anaent_41['ENTVAL'] == $Anapro_rec['PROCODTIPODOC']) {
                App::log('valorizzazione per agg tabdag');
                $ArrDati = array();
                $ArrDati[proLibGiornaliero::CHIAVE_DATA_FIRMA] = date('Ymd');
                if (!$this->proLibTagDag->AggiornamentoTagGiornaliero($Anapro_rec, $ArrDati)) {
                    Out::msgStop("Aggiornamento Metadati Giornaliero", $this->proLibTagDag->getErrMessage());
                }
                if (!$this->proLibGiornaliero->ExportFileRegistrioGiornaliero($Anapro_rec)) {
                    Out::msgStop("Salvataggio Registrio Giornaliero", $this->proLibGiornaliero->getErrMessage());
                }
            }
        }
        // Salvataggio allegato su fascicoli:
        if ($this->currArcite['ITEPAR'] == 'W') {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAssegnazionePassi.class.php';
            $praLibAssegnazionePassi = new praLibAssegnazionePassi();
            $allegato = array();
            $allegato['FILEPATH'] = $FileDest;
            $allegato['FILENAME'] = $anadoc_rec['DOCFIL'];
            $allegato['DOCNAME'] = $anadoc_rec['DOCNAME'];
            $rowidAllegato = str_replace('PRAM.PASDOC.', '', $anadoc_rec['DOCLNK']);
            if (!$praLibAssegnazionePassi->AggiornaAllegatoPasso($allegato, $rowidAllegato)) {
                Out::msgStop("Salvataggio Allegato Passo", "Errore in salvataggio allegato firmato sul passo" . $praLibAssegnazionePassi->getErrMessage());
            }
        }

        $profilo = proSoggetto::getProfileFromIdUtente();
        $docfirma_rec = $this->proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', false, " AND ROWIDARCITE={$this->currArcite['ROWID']} AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
        if ($docfirma_rec) {
            App::log('docfirma_rec trovato');
            $docfirma_rec['FIRDATA'] = date('Ymd');
            $docfirma_rec['FIRORA'] = date('H:i:s');
            $update_Info = "Oggetto: Documento firmato da soggetto " . $profilo['COD_SOGGETTO'];
            if (!$this->updateRecord($this->PROT_DB, 'DOCFIRMA', $docfirma_rec, $update_Info)) {
                Out::msgStop("Firma remota", "Aggiornamento dati documento da firmare " . $anadoc_rec['DOCNAME'] . " fallito");
                return false;
            }
        }
    }

    private function inviaMail($obligoInviomail = false) {
        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        $proArriDest = $this->proLib->caricaDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $proAltriDestinatari = $this->proLib->caricaAltriDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], true);
        $proArriAlle = $this->proLib->caricaAllegatiProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $allegati = array();
        foreach ($proArriAlle as $allegato) {
            if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0) {
                // Sovrascrivo la path. Attenzione, potrebbero occupare molto spazio le copie prima dell'invio.
                $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], '', true);
                if (!$CopyPathFile) {
                    Out::msgStop("Invio mail protocolo a Destinatari", "Errore in caricamento allegato: " . $this->proLibAllegati->getErrMessage());
                    $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Anadoc Rowid:  " . $allegato['ROWID'] . ". Errore: " . $this->proLibAllegati->getErrMessage()));
                    return false;
                }
                /* Controllo Impronta Sha256 del file */
                $Anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'rowid');
                if (!$Anadoc_rec) {
                    Out::msgStop("Invio mail protocolo a Destinatari", "Errore in caricamento allegato: Record su ANADOC non trovato.");
                    $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Record su ANADOC non trovato. Anadoc Rowid:  " . $allegato['ROWID'] . ". "));
                    return false;
                }
                $sha256 = hash_file('sha256', $CopyPathFile);
                if ($sha256 != $Anadoc_rec['DOCSHA2']) {
                    Out::msgStop("Invio mail protocolo a Destinatari", "Errore in controllo allegato, Impronta file non corrispondente. " . $allegato['DOCNAME'] . " Non è possibile procedere con l'invio.");
                    $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Impronta non corrispondente. Anadoc Rowid:  " . $allegato['ROWID'] . ". Calcolata: " . $sha256 . ' Su DB: ' . $Anadoc_rec['DOCSHA2']));
                    return false;
                }
                $allegato['FILEPATH'] = $CopyPathFile;
                $allegati[] = $allegato;
            }
        }

        $this->appoggio = array('proArriDest' => $proArriDest, 'proAltriDestinatari' => $proAltriDestinatari);
        $risultato = $this->proLibMail->inviaMailDestinatari($this->nameForm, $proArriDest, $proAltriDestinatari, $allegati, $anapro_rec['PRONUM'], 'codice', $anapro_rec['PROPAR'], $obligoInviomail);
        if (!$risultato) {
            if ($this->proLibMail->getErrCode() != -1) {
                Out::msgInfo("Notifica Destinatari", $this->proLibMail->getErrMessage());
            } else {
                Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
            }
        }
    }

    private function chiudiIter($annotazioni, $aggiornaTit = true) {
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        $arcite_rec['ITEFIN'] = $this->workDate;
        $arcite_rec['ITEFLA'] = '2';
        $arcite_rec['ITEANN'] = $annotazioni ? $annotazioni . ' - ' . $arcite_rec['ITEANN'] : $arcite_rec['ITEANN']; //Se aggiunte annotazioni le accodo.
        $update_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'];
        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $update_Info);
        if ($aggiornaTit) {
            if (!$this->AggiornaTitolario($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR'])) {
                Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento del titolario protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                return;
            }
        }
        $this->ricaricaFascicolo();
    }

    private function trasmettiPerConsegnaCartaceo() {
        if ($this->currArcite['ITENODO'] != 'ASS') {
            if ($this->currArcite['ITETIP'] != proIter::ITETIP_PARERE_DELEGA) {
                return;
            }
        }
        $anaent_35 = $this->proLib->GetAnaent('35');
        if (!$anaent_35['ENTDE6']) {
            return;
        }
        $assOriginale_check = $this->proLib->getGenericTab("SELECT ROWID FROM ANADES WHERE DESORIGINALE<>'' AND DESTIPO='T' AND DESNUM='{$this->currArcite['ITEPRO']}' AND DESPAR='{$this->currArcite['ITEPAR']}'", false);
        if ($assOriginale_check) {
            return;
        }
        $iter = proIter::getInstance($this->proLib, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR']);
        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        if ($this->currArcite['ITETIP'] == proIter::ITETIP_PARERE_DELEGA) {
            $arcite_padre_delega = $iter->getIterNode($this->currArcite['ITEPRE']);
            $arcite_padre = $iter->getIterNode($arcite_padre_delega['ITEPRE']);
        } else {
            $arcite_padre = $iter->getIterNode($this->currArcite['ITEPRE']);
        }
        $anamed_ant = $this->proLib->GetAnamed($arcite_padre['ITEDES'], 'codice');
        $checkAvvisato = $this->proLib->getGenericTab("
            SELECT ROWID FROM ARCITE WHERE ITEDES='{$arcite_padre['ITEDES']}' AND ITEORGWORKLIV='1' AND ITEFIN<>'' AND ITEPRO='{$this->currArcite['ITEPRO']}' AND ITEPAR='{$this->currArcite['ITEPAR']}'
                ", false);
        if ($checkAvvisato) {
            return;
        }

        if ($arcite_padre['ITEBASE'] && $this->currArcite['ITEPAR'] == 'A' && $this->proLib->checkRiservatezzaProtocollo($anapro_rec) == false) {
            $termine = '';
            if ($anaent_35['ENTVAL']) {
                $termine = $this->proLib->AddGiorniToData(date('Ymd'), $anaent_35['ENTVAL']);
            }
            // CREO TRX DI AVVISO CONSEGNA CARTACEO
            $iter->insertIterNodeFromAnamed($anamed_ant, $this->currArcite, array(
                "UFFICIO" => $arcite_padre['ITEUFF'],
                "NOTE" => "TRASMISSIONE PER CONSEGNA CARTACEO",
                "GESTIONE" => 0,
                "LIVELLO" => 1,
                "TERMINE" => $termine,
                "ITETIP" => ''
            ));
        }
    }

    private function EsprimiParere() {


        $pareriRow_rec = $this->segLib->GetPareri($this->currArcite['ROWID'], 'rowidarcite');
        $pareri_tab = $this->segLib->GetPareri($pareriRow_rec['CODTESTO'], 'codtesto');
        $precedenti = true;
        $successivi = true;
        foreach ($pareri_tab as $key => $pareri_rec) {
            if ($pareri_rec['SEQUENZA'] < $pareriRow_rec['SEQUENZA'] && $pareri_rec['DATAPARERE'] == '') {
                $precedenti = false;
            }
            if ($pareri_rec['SEQUENZA'] > $pareriRow_rec['SEQUENZA'] && $pareri_rec['DATAPARERE'] != '') {
                $successivi = false;
            }
        }
        if (!$precedenti) {
            $avviso = "Parere non abilitato. Deve essere eseguita la successione dei pareri.";
        }
        if (!$successivi) {
            $avviso = "Impossibile modificare il parere, ne è già stato dato uno successivo.";
        }

        itaLib::openForm('segGesPareri', true);
        /* @var $segGesPareri segGesPareri */
        $segGesPareri = itaModel::getInstance('segGesPareri');
        $segGesPareri->setEvent('setParere');
        $segGesPareri->setReturnEvent('returnParere');
        $segGesPareri->setReturnModel($this->nameForm);
        $segGesPareri->setRowidParereSelezionato($pareriRow_rec['ROWID']);
        $segGesPareri->SetidelibChiamante($pareriRow_rec['CODTESTO']);
        $segGesPareri->setAvviso($avviso);
        $segGesPareri->parseEvent();
    }

    public function VaiAllaFirma() {
        /*
         * Nuovo controllo:
         * Se iter annullato non posso gestire la firma.
         */
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        if ($arcite_rec['ITEANNULLATO']) {
            $this->visualizzazione = true;
            $this->openDettaglio($this->currArcite['ROWID']);
            return;
        }


        Out::tabSelect($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegati");
        $daFirmare = $this->GetAllegatiDaFirmare();
        $FirmaDocumentale = false;
        if ($arcite_rec['ITEPAR'] == 'I') {
            $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
            if ($Indice_rec['INDPREPAR']) {
                $FirmaDocumentale = true;
            }
        }

        if ($FirmaDocumentale) {
            $return = "returnFromSignAuthDocumentale";
            itaLib::openForm('rsnAuth', true);
            /* @var $rsnAuth rsnAuth */
            $rsnAuth = itaModel::getInstance('rsnAuth');
            $rsnAuth->setEvent('openform');
            $rsnAuth->setReturnEvent($return);
            $rsnAuth->setReturnModel($this->nameForm);
            $rsnAuth->setReturnId('');
            $rsnAuth->setReturnMultiFile(true);
            $rsnAuth->setMultiFile(false);
            $rsnAuth->setAllegati($daFirmare);
            $rsnAuth->setChiediCredenziali(true);
            $rsnAuth->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
            $rsnAuth->parseEvent();
        } else {
            //
            // Apertura Nuova Form
            //
            $return = "returnFromSignAuth";
            itaLib::openForm('proFirma', true);
            /* @var $proFirma proFirma */
            $proFirma = itaModel::getInstance('proFirma');
            $proFirma->setEvent('openform');
            $proFirma->setReturnEvent($return);
            $proFirma->setReturnModel($this->nameForm);
            $proFirma->setReturnId('');
            $proFirma->setReturnMultiFile(true);
            $proFirma->setMultiFile(false);
            $proFirma->setAllegati($daFirmare);
            $proFirma->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
            $proFirma->parseEvent();
        }
    }

    public function GetAllegatiDaFirmare() {
        $daFirmare = array();

        $subPath = "segFirma-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);

        foreach ($this->proIterAlle as $key => $allegato) {
            if ($allegato['DAFIRMARE']) {
                $daFirmare[$key]['FILEORIG'] = $allegato['DOCNAME'];
                $baseName = pathinfo($allegato['FILEPATH'], PATHINFO_BASENAME);
                $ext = strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION));
                $InputFileTemporaneo = $tempPath . "/" . $baseName;
                if ($allegato['DOCPAR'] != 'I') {
                    $FilePathCopy = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], $InputFileTemporaneo);
                    if (!$FilePathCopy) {
                        Out::msgStop("Attenzione", "Errore durante la copia del file nell'ambiente temporaneo di lavoro." . $this->proLibAllegati->getErrMessage());
                        return false;
                    }
                } else {
                    $FilePathCopy = $this->segLibAllegati->CopiaDocAllegato($allegato['ROWID'], $InputFileTemporaneo);
                }
                $daFirmare[$key]['INPUTFILEPATH'] = $InputFileTemporaneo;
                $daFirmare[$key]['OUTPUTFILEPATH'] = $InputFileTemporaneo . ".p7m";
                if ($ext == 'p7m') {
                    $daFirmare[$key]['OUTPUTFILEPATH'] = $InputFileTemporaneo;
                }
                $daFirmare[$key]['SIGNRESULT'] = '';
                $daFirmare[$key]['SIGNMESSAGE'] = '';
            }
        }
        return $daFirmare;
    }

    public function caricaDatiAggiuntivi($Anapro_rec) {
        $this->caricaDatiAggiuntiviTree($Anapro_rec);
        $this->CaricaGriglia($this->gridCampiAggiuntivi, $this->datiAggiuntivi, '1', 500);
    }

    public function caricaDatiAggiuntiviTree($Anapro_rec) {
        $this->datiAggiuntivi = array();
        /**
         * Estraggo tutti i dataset coinvolti per fonte
         * 
         */
        $sql = "
            SELECT
                TDAGFONTE,
                TDPROG
            FROM
                TABDAG
            WHERE
                TDCLASSE = 'ANAPRO' AND 
                TDROWIDCLASSE = {$Anapro_rec['ROWID']}
            GROUP BY
                TDAGFONTE,
                TDPROG";
        $DataSet_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        //Out::msgInfo("", print_r($DataSet_tab, true));

        /**
         *  creo una root per ogni dataset
         */
        $inc = 0;
        foreach ($DataSet_tab as $DataSet_rec) {
            $inc++;
            $arrayData = array();
            $arrayData['TDAGSET'] = $DataSet_rec['TDAGFONTE'] . "-" . $DataSet_rec['TDPROG'];
            $arrayData['TDAGCHIAVE'] = $DataSet_rec['TDPROG'] . ') ' . $DataSet_rec['TDAGFONTE'];
            $arrayData['TDAGVAL'] = "";
            $arrayData['level'] = 0;
            $arrayData['parent'] = null;
            $arrayData['isLeaf'] = 'false';
            $arrayData['expanded'] = 'true';
            $arrayData['loaded'] = 'true';
            $this->datiAggiuntivi[$inc] = $arrayData;
            $sqlDettaglio = "
                SELECT
                    * 
                FROM
                    TABDAG 
                WHERE
                    TDCLASSE = 'ANAPRO' AND 
                    TDROWIDCLASSE = '{$Anapro_rec['ROWID']}' AND 
                    TDAGFONTE = '{$DataSet_rec['TDAGFONTE']}' AND                        
                    TDPROG = {$DataSet_rec['TDPROG']}
                ORDER BY
                    TDAGSEQ ASC ";
            $Tabdag_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlDettaglio, true);
            /*
             * Esplodo le voci di dettaglio
             */
            foreach ($Tabdag_tab as $Tabdag_rec) {
                $arrayDataCampo = array();
                $inc++;
                $arrayDataCampo['TDAGSET'] = $DataSet_rec['TDAGFONTE'] . "-" . $DataSet_rec['TDPROG'] . "-" . $Tabdag_rec['ROWID'];
                $arrayDataCampo['TDAGCHIAVE'] = $Tabdag_rec['TDAGCHIAVE'];
                //$arrayDataCampo['ROWID'] = 0;
                $Valore = $Tabdag_rec['TDAGVAL'];
                if (is_numeric($Tabdag_rec['TDAGVAL']) && strlen($Tabdag_rec['TDAGVAL']) == 8) {
                    $Valore = date("d/m/Y", strtotime($Tabdag_rec['TDAGVAL']));
                }
                $arrayDataCampo['TDAGVAL'] = $Valore;
                $arrayDataCampo['level'] = 1;
                $arrayDataCampo['parent'] = $DataSet_rec['TDAGFONTE'] . "-" . $DataSet_rec['TDPROG'];
                $arrayDataCampo['isLeaf'] = 'true';
                $arrayDataCampo['expanded'] = 'true';
                $arrayDataCampo['loaded'] = 'true';
                $this->datiAggiuntivi[$inc] = $arrayDataCampo;
            }
        }
    }

    public function ApriGestioneFascicolo($chiaveFascicolo = '') {
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
        if (!$chiaveFascicolo) {
            $chiaveFascicolo = $anapro_rec['PROFASKEY'];
        }
        $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $chiaveFascicolo);
        if (!$anapro_check) {
            Out::msgStop("Attenzione", "Non hai accesso al Fascicolo.");
            return;
        }
        $model = 'proGestPratica';
        itaLib::openForm($model);
        /* @var $proGestPratica proGEstPratica */
        $proGestPratica = itaModel::getInstance($model, $model);
        $proGestPratica->setEvent('openform');
        $proGestPratica->setOpenGeskey($chiaveFascicolo);
        $proGestPratica->parseEvent();
    }

    public function ApriSelezioneFascicolo($Titolario) {
        $model = 'proSeleFascicolo';
        itaLib::openForm($model);
        /* @var $proSeleFascicolo proSeleFascicolo */
        $proSeleFascicolo = itaModel::getInstance($model);
        $proSeleFascicolo->setEvent('openform');
        $proSeleFascicolo->setReturnModel($this->nameForm);
        $proSeleFascicolo->setTitolario($Titolario);
        $proSeleFascicolo->setReturnEvent('returnAlberoFascicolo');
        $proSeleFascicolo->setReturnId($this->nameForm . '_AggiungiFascicolo');
        $proSeleFascicolo->setAbilitaCreazione(true);
        $proSeleFascicolo->parseEvent();
    }

    public function CheckAllegatiDaFirmare() {
        /*  1 Controllo se il documento è effettivamente da firmare.
         *  2 Controllo se il parametro è attivo: 
         *      Documento visibile solo dopo la firma
         * 3. Blocco se chi sta aprendo la trasmissione non è il firmatario.
         *
         */
        $anaent48_rec = $this->proLib->GetAnaent('48');
        if ($anaent48_rec['ENTDE2']) {
            foreach ($this->proIterAlle as $allegato) {
                $docfirma_check = $this->proLibAllegati->GetDocfirma($allegato['ROWID'], 'rowidanadoc');
                if ($docfirma_check) {
                    if (!$docfirma_check['FIRDATA'] && !$docfirma_check['FIRANN']) {
                        $CodiceUtente = proSoggetto::getCodiceSoggettoFromIdUtente();
                        if ($CodiceUtente != $docfirma_check['FIRCOD']) {
                            //Controllo rifiutato.
                            $ArciteFirma_rec = $this->proLib->GetArcite($docfirma_check['ROWIDARCITE'], 'rowid');
                            if ($ArciteFirma_rec['ITESTATO'] == proIter::ITESTATO_RIFIUTATO) {
                                return true;
                            }
                            Out::msgInfo('Trasmissione non accessibile', " Occorre attendere che il firmatario apponga la firma al Documento prima di poter procedere.");
                            return false;
                        } else {
                            return true;
                        }
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function FirmaDocumentale($result) {

        $SignMethod = $result['SIGNMETHOD'];

        /* Controllo Metodo Firma */
        if (!$SignMethod) {
            Out::msgInfo("Informazione", "Metodo di firma non definito");
            return false;
        }
        /*
         * Controllo altri parametri mancanti.
         */
        if (!$result['TYPEOTPAUTH'] || !$result['OTPPWD'] || !$result['USER'] || !$result['PASSWORD']) {
            Out::msgInfo("Informazione", "Parametri firma mancanti.");
            return false;
        }

        /*
         * Protocollazione Documentale.
         */

        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
        $proLibDocumentale = new proLibDocumentale();
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
        $AnaproIndice_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);


        $Signer = new rsnSigner();
        $Signer->setTypeOtpAuth($result['TYPEOTPAUTH']);
        $Signer->setOtpPwd($result['OTPPWD']);
        $Signer->setUser($result['USER']);
        $Signer->setPassword($result['PASSWORD']);
        $Sessionid = $Signer->openSession();
        if (!$Sessionid) {
            Out::msgInfo("Attenzione", $Signer->getMessage());
            return false;
        }
        // Contorllo se devo firmare e poi protocollare il doc
        $anaent_rec = $this->proLib->GetAnaent('55');
        if ($anaent_rec['ENTDE3'] != '1') {
            $daFirmare = $this->GetAllegatiDaFirmare();
            if (count($daFirmare) > 1) {
                if (!$this->SignMulti($Signer, $daFirmare, $SignMethod, $Sessionid)) {
                    return false;
                }
            } else {
                if (!$this->SignSingle($Signer, $daFirmare, $SignMethod, $Sessionid)) {
                    return false;
                }
            }
            if (!$this->TogliDallaFirmaDocumenti($AnaproIndice_rec, false)) {
                return false;
            }
            // Rimosso chiusura iter e fatto dopo la protocollazione
        }

        if (!$proLibDocumentale->ProtocollaDocumentale($Indice_rec['IDELIB'])) {
            Out::msgInfo('Protocollazione', $proLibDocumentale->getErrMessage());
            $this->openDettaglio($arcite_rec['ROWID']);
            return;
        }

        /* Chiusura iter dopo la protocollazione */
        if ($anaent_rec['ENTDE3'] != '1') {
            $this->chiudiIter('DOCUMENTO FIRMATO E PREDISPOSTO PER IL PROTOCOLLO', false);
            $this->openDettaglio($arcite_rec['ROWID']);
        }

        $Anapro_rec = $proLibDocumentale->getLastAnapro_rec();
        if (!$Anapro_rec) {
            Out::msgStop("Attenzione", "Errore in lettura Protocollo Creato.");
            return false;
        }
        /*
         * Cerco iter del firmatario non annullato.
         */

        $AnadesFir_rec = $this->proLib->GetAnades($Anapro_rec['PRONUM'], 'codice', false, $Anapro_rec['PROPAR'], 'M');
        $sql = "SELECT * FROM ARCITE "
                . " WHERE ITEPRO = " . $Anapro_rec['PRONUM'] . " AND "
                . " ITEPAR = '" . $Anapro_rec['PROPAR'] . "' AND "
                . " ITETIP = '" . proIter::ITETIP_ALLAFIRMA . "' "
                . " AND ITEANNULLATO = '0' AND ITEDES = '" . $AnadesFir_rec['DESCOD'] . "' AND ITEUFF = '" . $AnadesFir_rec['DESCUF'] . "'";
        $Arcite_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        /*
         * Dettaglio 
         */
        $this->openDettaglio($Arcite_rec['ROWID']);
        /*
         * Firmo documenti solo se vanno firmati dopo la creazione del protocollo.
         */
        if ($anaent_rec['ENTDE3'] == '1') {
            $daFirmare = $this->GetAllegatiDaFirmare();
            if (count($daFirmare) > 1) {
                if (!$this->SignMulti($Signer, $daFirmare, $SignMethod, $Sessionid)) {
                    return false;
                }
            } else {
                if (!$this->SignSingle($Signer, $daFirmare, $SignMethod, $Sessionid)) {
                    return false;
                }
            }
            if (!$this->TogliDallaFirmaDocumenti($AnaproIndice_rec, true)) {
                return false;
            }
        } else {
            if (!$this->TogliDallaFirmaDocumenti($Anapro_rec, true, false)) {
                return false;
            }
            Out::hide($this->nameForm . '_VaiAllaFirma');
        }


        /*
         * Sposto fascicoli:
         */
        if (!$this->proLibFascicolo->SpostaFascicoli($this, $Indice_rec['INDPRO'], $Indice_rec['INDPAR'], $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
            Out::msgStop("Informazione", "Fascicolazione automatica non riuscita, terminare l'operzione manualmente. " . $this->proLibFascicolo->getErrMessage());
        }
        /*
         * Rendo Indice Definitivo
         */
        $Indice_rec['INDDEF'] = 1;
        $update_Info = "Rendo atto predisposto per la firma definitivo. " . $Indice_rec['IDELIB'];
        if (!$this->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
            return false;
        }
        /*
         * Dettaglio ed invio mail
         */
        $this->openDettaglio($Arcite_rec['ROWID']);
        $this->inviaMail(true);
        $this->chiudiIter("Firme effettuate correttamente.", false);
        return true;
    }

    public function SignSingle($Signer, $daFirmare, $SignMethod, $Sessionid = '') {
        foreach ($daFirmare as $key => $Allegato) {
            $Signer->setInputFilePath($Allegato['INPUTFILEPATH']);
            $Signer->setOutputFilePath($Allegato['OUTPUTFILEPATH']);
            /**
             * Lancio la corretta procedura di firma
             * 
             */
            switch ($SignMethod) {
                case rsnSigner::TYPE_SIGN_CADES:
                    if (strtolower((pathinfo($Signer->getInputFilePath(), PATHINFO_EXTENSION))) == 'p7m') {
                        $ret = $Signer->addPkcs7sign($Sessionid);
                    } else {
                        $ret = $Signer->signPkcs7($Sessionid);
                    }
                    break;
                case rsnSigner::TYPE_SIGN_PADES:
                    break;
                case rsnSigner::TYPE_SIGN_XADES:
                    $ret = $Signer->signXades();
                    break;
            }
            if (!$ret) {
                Out::msgInfo("Firma remota... Fallita!", $Signer->getReturnCode() . "-" . $Signer->getMessage());
                return false;
            }
            if (!file_exists($Allegato['INPUTFILEPATH'])) {
                Out::msgInfo("Firma remota... Fallita!", "File non trovato");
            }
            $FileNameFirmato = pathinfo($Allegato['FILEORIG'], PATHINFO_BASENAME) . ".p7m";
            /* Se già p7m, non serve aggiungere estenzione .p7m */
            if (strtolower((pathinfo($Signer->getInputFilePath(), PATHINFO_EXTENSION))) == 'p7m') {
                $FileNameFirmato = pathinfo($Allegato['FILEORIG'], PATHINFO_BASENAME);
            }
            $this->salvaDocumentoFirmato($key, $Allegato['OUTPUTFILEPATH'], $Allegato['INPUTFILEPATH'], $FileNameFirmato);
        }
        return true;
    }

    public function SignMulti($Signer, $DaFirmare, $SignMethod, $Sessionid = '') {
        $multiSignFilePaths = array();
        foreach ($DaFirmare as $key => $allegato) {
            $multiSignFilePaths[$key] = array(
                'inputFilePath' => $allegato['INPUTFILEPATH'],
                'outputFilePath' => $allegato['OUTPUTFILEPATH'],
                'fileNameFirmato' => $allegato['OUTPUTFILEPATH']
            );
        }
        $Signer->setMultiSignFilePaths($multiSignFilePaths);
        /**
         * Lancio la corretta procedura di firma
         * 
         */
        switch ($SignMethod) {
            case rsnSigner::TYPE_SIGN_CADES:
                $ret = $Signer->multiSignPkcs7(true, $Sessionid);
                BREAK;
            case rsnSigner::TYPE_SIGN_PADES:
                break;
            case rsnSigner::TYPE_SIGN_XADES:
                $ret = $Signer->multiSignXades();
                break;
        }
        /**
         * Parse del risultato
         */
        if (!$ret) {
            Out::msgInfo("Firma remota... Fallita!", $Signer->getReturnCode() . "-" . $Signer->getMessage());
            return false;
        }
        foreach ($DaFirmare as $key => $Allegato) {
            $FileNameFirmato = pathinfo($Allegato['FILEORIG'], PATHINFO_BASENAME) . ".p7m";
            /* Se file firmato è già p7m, non aggiungo l'estensione */
            if (strtolower((pathinfo($Allegato['FILEORIG'], PATHINFO_EXTENSION))) == 'p7m') {
                $FileNameFirmato = pathinfo($Allegato['FILEORIG'], PATHINFO_BASENAME);
            }
            $this->salvaDocumentoFirmato($key, $Allegato['OUTPUTFILEPATH'], $Allegato['INPUTFILEPATH'], $FileNameFirmato);
        }
        return true;
    }

    public function ControlloAccessibilitaAtto($rowid) {
        if (!$this->currArcite['ITEGES']) {
            return false;
        }
        $sql = $this->segLib->getSqlIndice() . " WHERE INDICE.ROWID = $rowid ";
        $where_profilo = proSoggetto::getSecureWhereIndiceFromIdUtente($this->proLib);
        $sql .= " AND $where_profilo";
        $IndiceTest_rec = ItaDB::DBSQLSelect($this->segLib->getSEGRDB(), $sql, false);
        if (!$IndiceTest_rec) {
            return false;
        }
        return true;
    }

    public function DecodificaTrasmissione($arcite_rec, $arcite_pre, $anapro_rec) {
        /*
         * 1. Info Dati Trasmissione:
         */
        $mitt = proSoggetto::getInstance($this->proLib, $arcite_pre['ITEDES'], $arcite_pre['ITEUFF']);
        $mitt_dati = $mitt->getSoggetto();
        $mittenteTrasm = $mitt_dati['DESCRIZIONESOGGETTO'];
        $dataTrasm = date("d/m/Y", strtotime($arcite_rec['ITEDAT']));
        $scadenza = "";
        if ($arcite_rec['ITETERMINE']) {
            $scadenza = " <b>Scadenza:</b> " . date("d/m/Y", strtotime($arcite_rec['ITETERMINE'])) . ". ";
        }
        $annotazioni = '&nbsp;';
        if ($arcite_rec['ITEANN']) {
            $annotazioni = $arcite_rec['ITEANN'];
        }
        $DatiTrasmissione = "<span style=\"display: inline-block; width:120px;\"><b>Mittente Trasmissione:</b></span> $mittenteTrasm <b>del</b> $dataTrasm $scadenza";
        Out::html($this->nameForm . '_DatiTrasmissione', $DatiTrasmissione);
        /*
         * 2. Oggetto Trasmssione
         */
        $OggettoTrasmissione = "<span style=\"display: inline-block; width:120px;\"><b>Oggetto Trasmissione:</b></span> $annotazioni ";
        Out::html($this->nameForm . '_OggettoTrasmissione', $OggettoTrasmissione);
        /*
         * 3. Dati Protocollo/Documento/Pratica
         */
        $DatiProtocollo = "";
        $Pronum = intval(substr($arcite_rec['ITEPRO'], 4));
        $Anno = substr($arcite_rec['ITEPRO'], 0, 4);
        $Propar = $arcite_rec['ITEPAR'];
        $DataProt = date("d/m/Y", strtotime($anapro_rec['PRODAR']));
        switch ($arcite_rec['ITEPAR']) {
            case 'A':
            case 'P':
                $DatiProtocollo = "<span style=\"display: inline-block; width:120px;\"><b>Protocollo:</b></span> $Pronum / $Anno - $Propar <b>del</b> " . $DataProt;
                break;
            case 'C':

                $DatiProtocollo = "<span style=\"display: inline-block; width:120px;\"><b>Doc. Formale:</b></span> $Pronum / $Anno $Propar <b>del</b> " . $DataProt;
                break;
            case 'I':
                $Indice_rec = $this->segLib->GetIndice($arcite_rec['ITEPRO'], 'anapro', false, $arcite_rec['ITEPAR']);
                $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                $TipoDocumento = segLibDocumenti::$ElencoTipi[$Indice_rec['INDTIPODOC']];
                $DataAtto = date("d/m/Y", strtotime($Indice_rec['IDATDE']));
                $DatiProtocollo = "<span style=\"display: inline-block; width:120px;\"><b>$TipoDocumento:</b></span>" . $dizionarioIdelib['PROGRESSIVO'] . " <b>del</b> " . $DataAtto;
                if ($Indice_rec['INDPREPAR']) {
                    if ($Indice_rec['INDPREPAR'] == 'P') {
                        $DatiProtocollo .= " predisposto per una <b>Partenza</b>";
                    } else {
                        $DatiProtocollo .= " predisposto per <b>Documento Formale</b>";
                    }
                }
                break;
            case 'W':
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                $praLib = new praLib();
                $propas_rec = $praLib->GetPropas($arcite_rec['ITEPRO'], "paspro", false, $arcite_rec['ITEPAR']);
                $Serie_rec = $praLib->ElaboraProgesSerie($propas_rec['PRONUM']);
                $anastp_rec = $praLib->GetAnastp($propas_rec['PROSTATO']);
                $DatiProtocollo = "<span style=\"display: inline-block; width:120px;\"><b>Passo Pratica:</b></span> $Serie_rec <span style=\"display: inline-block; margin-left:30px;\"><b>Sequenza </b>" . $propas_rec['PROSEQ'] . "</span><br> ";
                if ($anastp_rec) {
                    $DatiProtocollo .= "<span style=\"display: inline-block; width:120px;\"><b>Stato:</b></span> " . $anastp_rec['STPDES'];
                }
                break;
            default:
                break;
        }
        Out::html($this->nameForm . '_DatiProtocollo', $DatiProtocollo);
    }

    public function TogliDallaFirmaDocumenti($AnaproIndice_rec, $DeleteDocFirma = false, $SincronizzaIte = true) {
        /*
         * Tolgo Alla firma il Documento Indice.
         */
        $anades_mitt = $this->proLib->GetAnades($AnaproIndice_rec['PRONUM'], 'codice', false, $AnaproIndice_rec['PROPAR'], 'M');
        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($AnaproIndice_rec['PRONUM'], $AnaproIndice_rec['PROPAR'], true, " AND FIRCOD='{$anades_mitt['DESCOD']}'");
        $iter = proIter::getInstance($this->proLib, $AnaproIndice_rec);
        foreach ($docfirma_tab as $docfirma_rec) {
            if ($DeleteDocFirma) {
                $delete_Info = 'Oggetto: Cancellazione Richiesta di firma ' . $docfirma_rec['ROWIDARCITE'];
                if (!$this->deleteRecord($this->PROT_DB, "DOCFIRMA", $docfirma_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Attenzione", "Cancellazione Richieste di firma non avvenuta.");
                    return false;
                }
            }
            if ($SincronizzaIte) {
                $iter->sincronizzaIterFirma('cancella', $docfirma_rec['ROWIDARCITE']);
            }
        }
        return true;
    }

    public function AggiornaFatturaHalley() {
        // Aggiorno dati presa in carico.
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        $arcite_rec['ITEDATACC'] = date('Ymd');
        $arcite_rec['ITENOTEACC'] = 'Presa in carico per aggiornamento fattura halley';
        $arcite_rec['ITESTATO'] = proIter::ITESTATO_INCARICO;
        Out::hide($this->nameForm . '_InCarico');
        $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false);
        // 
        $Anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        itaLib::openForm('proConfermaHalley', true);
        /* @var $proConfermaHalley proConfermaHalley */
        $proConfermaHalley = itaModel::getInstance('proConfermaHalley');
        $proConfermaHalley->setEvent('openform');
        $proConfermaHalley->setReturnEvent('returnConfermaHalley');
        $proConfermaHalley->setReturnModel($this->nameForm);
        $proConfermaHalley->setAnapro_rec($Anapro_rec);
        $proConfermaHalley->parseEvent();
        return true;
    }

    public function caricaGrigliaAllegatiPasso($propak) {
        if ($propak == "") {
            return;
        }

        /*
         * Visualizzo Tab Allegati del Passo
         */
        Out::showTab($this->nameForm . '_paneAllegatiPasso');

        /*
         * Inietta la griglia allegati del passso
         */
        include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';
        $model = 'praSubPassoAllegatiSimple';
        $proSubAllegatiSimple = itaFormHelper::innerForm($model, $this->nameForm . '_paneAllegatiPasso');
        $proSubAllegatiSimple->setEvent('openform');
        $proSubAllegatiSimple->parseEvent();
        $this->praSubPassoAllegatiSimple = $proSubAllegatiSimple->nameForm;
        //
        $praSubPassoAllegatiSimple = itaModel::getInstance('praSubPassoAllegatiSimple', $this->praSubPassoAllegatiSimple);
        $praSubPassoAllegatiSimple->setKeyPasso($propak);
        $praSubPassoAllegatiSimple->CaricaAllegati();

        /*
         * Set tab title
         */
        $tot = count($praSubPassoAllegatiSimple->getPassAlleSimple());
        Out::tabSetTitle($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegatiPasso", 'Allegati <span style="color:red; font-weight:bold;">(' . $tot . ') </span>');
    }

    /*
     * Predisposizione funzione di controllo e abilita bottone.
     * Attualmente se un documento alla firma si blocca, si trova 
     * tra i chiusi e solo il responsabile può fare click su "protocolla"
     */

    private function checkDocumentaleAllaFirmaInterrotto($AnaproRec) {
        if ($AnaproRec['PROPAR'] != 'I') {
            return;
        }
        $AllegatiFirmati = false;
        foreach ($this->proIterAlle as $allegato) {
            $ext = pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION);
            $docfirma_rec = $this->proLibAllegati->GetDocfirma($allegato['ROWID'], 'rowidanadoc', false, "");
            if ($docfirma_rec && $docfirma_rec['ROWIDARCITE'] == $this->currArcite['ROWID']) {
                if (strtolower($ext) == "p7m" && $docfirma_rec['FIRDATA'] != '') {
                    $AllegatiFirmati = true;
                }
            }
        }
        if ($AllegatiFirmati) {
            if ($AnaproRec['PROPAR'] == 'I') {
                $Indice_rec = $this->segLib->GetIndice($AnaproRec['PRONUM'], 'anapro', false, $AnaproRec['PROPAR']);
                if ($Indice_rec['INDPREPAR']) {
                    // Visualizzo bottone: se non è già stato protocollato.
                    $ProDocProt_rec = $this->proLib->GetProDocProt($AnaproRec['PRONUM'], 'sorgnum', $AnaproRec['PROPAR']);
                    if (!$ProDocProt_rec) {
                        // Solo se è utente creatore del documento alla firma
                        if ($AnaproRec['PROUTE'] == App::$utente->getKey('nomeUtente')) {
                            Out::show($this->nameForm . '_ProtocollaDocumentale');
                        }
                    }
                }
            }
        }
    }

}

?>
