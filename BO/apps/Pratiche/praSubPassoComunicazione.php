<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once ITA_BASE_PATH . '/apps/AlboPretorio/albRic.class.php';

function praSubPassoComunicazione() {
    $praSubPassoComunicazione = new praSubPassoComunicazione();
    $praSubPassoComunicazione->parseEvent();
    return;
}

class praSubPassoComunicazione extends praSubPasso {

    public $nameForm = 'praSubPassoComunicazione';
    public $destinatari = array();
    public $mettiAllaFirma;
    public $flagAssegnazioniPasso;
    private $tipoProtocollo;
    public $datiRubricaWS = array();
    public $idCorrispondente;
    public $utiEnte;
    public $accLib;
    public $praPerms;
    public $praLibPasso;
    public $allegatiComunicazione = array();

    function __construct() {
        parent::__construct();

        try {
            $this->accLib = new accLib();
            $this->utiEnte = new utiEnte();
            $this->praPerms = new praPerms();
            $this->praLibPasso = new praLibPasso();
            
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->mettiAllaFirma = App::$utente->getKey($this->nameForm . '_mettiAllaFirma');
            $this->flagAssegnazioniPasso = App::$utente->getKey($this->nameForm . '_flagAssegnazioniPasso');
            $this->tipoProtocollo = App::$utente->getKey($this->nameForm . '_tipoProtocollo');
            $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
            $this->idCorrispondente = App::$utente->getKey($this->nameForm . '_idCorrispondente');
            $this->allegatiComunicazione = App::$utente->getKey($this->nameForm . '_allegatiComunicazione');

        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
            
        
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
            App::$utente->setKey($this->nameForm . '_mettiAllaFirma', $this->mettiAllaFirma);
            App::$utente->setKey($this->nameForm . '_flagAssegnazioniPasso', $this->flagAssegnazioniPasso);
            App::$utente->setKey($this->nameForm . '_tipoProtocollo', $this->tipoProtocollo);
            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
            App::$utente->setKey($this->nameForm . '_idCorrispondente', $this->idCorrispondente);
            App::$utente->setKey($this->nameForm . '_allegatiComunicazione', $this->allegatiComunicazione);
        }
        
        
    }

    public function postInstance() {
        parent::postInstance();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Dettaglio($this->keyPasso);

                $this->flagAssegnazioniPasso = $this->CheckAssegnazionePasso();
                
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ProtocollaPartenza':
                        $this->mettiAllaFirma = "";
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            return false;
                        }
                        $arrayButton = $arrayCampi = array();
                        if (is_object($proObject)) {
                            switch ($proObject->getClientType()) {
                                case proWsClientHelper::CLIENT_ITALPROT:
                                    $arrayCampi = array(
                                        'label' => array('style' => "width:100px;", 'value' => 'Metti Alla Firma'),
                                        'id' => $this->nameForm . '_MettiAllaFirma',
                                        'name' => $this->nameForm . '_MettiAllaFirma',
                                        'type' => 'checkbox',
                                    );
                                    $arrayButton = array(
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Documento Formale' => array('id' => $this->nameForm . '_ConfermaDocumentoFormale', 'model' => $this->nameForm)
                                    );
                                    break;
                                case proWsClientHelper::CLIENT_JIRIDE:
                                    /*
                                     * inserito array con almeno un campo nascosto perchè l'array vuoto dei campi crea problemi
                                     */
                                    $arrayCampi = array(
                                        'id' => $this->nameForm . '_campoVuoto',
                                        'type' => 'hidden',
                                    );
                                    $arrayButton = array(
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Metti alla Firma' => array('id' => $this->nameForm . '_ConfermaMettiAllaFirma', 'model' => $this->nameForm),
                                    );
                                    break;
                                case proWsClientHelper::CLIENT_PALEO4:
                                    /*
                                     * inserito array con almeno un campo nascosto perchè l'array vuoto dei campi crea problemi
                                     */
                                    $arrayCampi = array(
                                        'id' => $this->nameForm . '_campoVuoto',
                                        'type' => 'hidden',
                                    );
                                    $arrayButton = array(
                                        'Protocolla Partenza' => array('id' => $this->nameForm . '_ConfermaProtPartenza', 'model' => $this->nameForm),
                                        'Documento Formale' => array('id' => $this->nameForm . '_ConfermaMettiAllaFirma', 'model' => $this->nameForm)
                                    );
                                    break;
                            }
                        }

                        if ($arrayButton) {
                            Out::msgInput("Quale operazione vuoi effettuare?", $arrayCampi, $arrayButton, $this->nameForm, 'auto', 'auto', true, '');
                            break;
                        }
                        break;
                    

                    case $this->nameForm . '_ConfermaProtocollazionePartenza':
                        if (!$this->ControllaDati()) {
                            break;
                        }

                        $this->AggiornaPartenzaDaPost();
                        $this->RegistraDestinatari();
                        $this->AggiornaArrivo();
                        if (!$this->AggiornaRecord()) {
                            break;
                        }
//
                        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] != '') {
                            Out::msgStop("Protocolla in partenza", "Protocollo già inserito");
                            break;
                        }
//                        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                        if ($this->tipoProtocollo == 'Italsoft-remoto') {
                            $accLib = new accLib();
                            $utenteWs = $accLib->GetUtenteProtRemoto(App::$utente->getKey('idUtente'));
                            if (!$utenteWs) {
                                Out::msgStop("Protocollo Remoto", "Utente remoto non definito!");
                                break;
                            }
                            $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $this->keyPasso . "' AND COMTIP='P'", false);
                            $model = 'utiIFrame';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnIFrame';
                            $_POST['retid'] = $this->nameForm . '_protocollaRemotoPartenza';
                            $envLibProt = new envLibProtocolla();
                            $url_param = $envLibProt->getParametriProtocolloRemoto();
//$devLib = new devLib();
//$parametro = $devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
//$url_param = $parametro['CONFIG'];
                            $_POST['src_frame'] = $url_param . "&access=direct&accessreturn=&accesstoken=nobody&model=menDirect&menu=PR_HID&prog=PR_WSPRA&topbar=0&homepage=0&noSave=1&utenteWs=" . $utenteWs . "&azione=CP&passo=" . $pracomP_rec['ROWID'];
                            $_POST['title'] = "Protocollazione Remota Comunicazione in Partenza";
                            $_POST['returnKey'] = 'protocollaWS';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                            break;
                        } elseif ($this->tipoProtocollo == 'Italsoft') {
                            $elementi = $this->protocollaPartenza();
                            $propas_rowid = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
                            $_POST = Array();
                            $model = 'proItalsoft.class';
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $proItalsoft = new proItalsoft();
                            $valore = $proItalsoft->protocollazione($elementi);
                            if ($valore['status'] === true) {
                                $propas_rec = $this->praLib->GetPropas($propas_rowid, 'rowid');
                                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                                Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', substr($valore['value'], 4));
                                Out::valore($this->nameForm . '_ANNOPROT_DESTINATARIO', substr($valore['value'], 0, 4));
                                Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                                $pracom_rec = array();
                                $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                                $pracom_rec['COMPRT'] = $valore['value'];
                                $pracom_rec['COMDPR'] = $this->workDate;
                                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                                    break;
                                }
                                Out::msgBlock('', 3000, false, "Protocollazione avvenuta con successo al n. " . substr($valore['value'], 4));
                                $MetadatiPartenza = array();
                                $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $MetadatiPartenza);
                                $this->Dettaglio($this->keyPasso, "propak");
                            } else {
                                Out::msgStop("Errore in Protocollazione", $valore['msg']);
                            }
                            break;
                        }

                        switch ($this->tipoProtocollo) {
                            case 'Paleo4':
                                $tipoWs = 'Paleo4';
                                break;
                            case 'Paleo':
                                $tipoWs = 'Paleo';
                                break;
                            case 'WSPU':
                                $tipoWs = 'WSPU';
                                break;
                            case 'Infor':
                                $tipoWs = 'Infor';
                                break;
                            case 'Iride':
                                $tipoWs = 'Iride';
                                break;
                            case 'Jiride':
                                $tipoWs = 'Jiride';
                                break;
                            case 'HyperSIC':
                                $tipoWs = 'HyperSIC';
                                break;
                            case 'Italsoft-ws':
                                $tipoWs = 'Italsoft-ws';
                                break;
                            default:
                                $tipoWs = '';
                                break;
                        }
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocWsRicevute = array();
                        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
                            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
                            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
                        }
                        if ($arrayDoc) {
                            $arrayDocWs = array();
                            foreach ($arrayDoc['pasdoc_rec'] as $key => $documento) {
                                $arrayDocWs[] = $documento['ROWID'];
                            }
                            $msgInfo .= "<br><b>E' possibile spostare gli allegati in alto o in basso secondo l'ordine desiderato.<br>Il primo allegato spuntato sarà inserito come allegato principale del protocollo.</b>";
                            praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, $this->nameForm, 'returnAllegatiWs', $msgInfo);
                        } else {
                            $this->lanciaProtocollaWS();
                            $this->Dettaglio($this->keyPasso, "propak");
                        }
                        break;
                        
                    case $this->nameForm . '_ConfermaProtPartenza':
                        if ($_POST[$this->nameForm . '_MettiAllaFirma'] == 1) {
                            $this->mettiAllaFirma = "true";
                        }
                        if ($_POST[$this->nameForm . '_PROPAS']['PRORPA'] == "") {
                            Out::msgStop("Protocollazione Pratica", "Responsabile Passo non Presente");
                            break;
                        }

                        if (!$this->destinatari) {
                            Out::msgInfo("Protocollazione Pratica", "Destinatari non presenti.<br>Inserire almeno un destinatario.");
                            break;
                        }
//chiede la conferma prima della protocollazione
                        Out::msgQuestion("ATTENZIONE!", "L'operazione protocollerà la pratica con procedura " . $this->tipoProtocollo . ". Vuoi continuare?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaProtocollazionePartenza', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaProtocollazionePartenza', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                        
                    case $this->nameForm . '_ConfermaDocumentoFormale':
                        if ($_POST[$this->nameForm . '_MettiAllaFirma'] == 1) {
                            $this->mettiAllaFirma = "true";
                        }
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            return false;
                        }
                        $this->getRicAllegatiWs($proObject->getClientType(), 'returnAllegatiWsDocumentoFormale');
                        break;
                    case $this->nameForm . '_ConfermaMettiAllaFirma':
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        $praFascicolo->setChiavePasso($this->keyPasso);
                        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
                        $arrayDocWsRicevute = array();
                        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
                            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
                            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
                        }
                        if (!$arrayDoc) {
                            Out::msgInfo("Metti alla firma", "Allegati passo non trovati");
                            break;
                        }
                        $arrayDocWs = array();
                        foreach ($arrayDoc['pasdoc_rec'] as $key => $documento) {
                            $arrayDocWs[] = $documento['ROWID'];
                        }
                        praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, $this->nameForm, 'returnAllegatiWsMettiAllaFirma', $msgInfo);
                        break;

                    case $this->nameForm . '_ConfermaRubricaWSP':
                        $this->idCorrispondente = $this->datiRubricaWS['codice'];
                        if ($this->datiRubricaWS['codiceFiscale'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $this->datiRubricaWS['codiceFiscale']);
                        }
                        if ($this->datiRubricaWS['partitaIva'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_DESTINATARIO', $this->datiRubricaWS['partitaIva']);
                        }
                        $this->ProtocolloICCSP();
                        break;
                    case $this->nameForm . '_ConfermaRubricaWSA':
                        $this->idCorrispondente = $this->datiRubricaWS['codice'];
                        if ($this->datiRubricaWS['codiceFiscale'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $this->datiRubricaWS['codiceFiscale']);
                        }
                        if ($this->datiRubricaWS['partitaIva'] != '') {
                            Out::valore($this->nameForm . '_CODFISC_MITTENTE', $this->datiRubricaWS['partitaIva']);
                        }
                        $this->ProtocolloICCSA();
                        break;
                    case $this->nameForm . '_InserisciRubricaWSP':
                        $this->inserisciRubricaWS('P');
                        break;
                    case $this->nameForm . '_InserisciRubricaWSA':
                        $this->inserisciRubricaWS('A');
                        break;
                        
                    case $this->nameForm . '_PROTRIC_DESTINATARIO_butt':
                        $anno = $this->workYear;
                        $where = '';
                        if ($_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'] != '') {
                            $anno = substr($_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'], 0, 4);
                            $data = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
                            $where = ' AND (PRODAR=' . $data . ')';
                        }
                        $numero = $_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'];
                        if ($numero != '') {
                            $numero = str_repeat("0", 6 - strlen(trim($numero))) . trim($numero);
                            $where = ' AND (PRONUM=' . $anno . $numero . ')';
                        }
                        albRic::albRicAnapro($this->nameForm, $anno, $where, 'dest');
                        break;
                        
                    
                }
                break;
        }
    }

    public function close() {
        parent::close();
        
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_mettiAllaFirma');
        App::$utente->removeKey($this->nameForm . '_flagAssegnazioniPasso');
        App::$utente->removeKey($this->nameForm . '_tipoProtocollo');
        App::$utente->removeKey($this->nameForm . '_datiRubricaWS');
        App::$utente->removeKey($this->nameForm . '_idCorrispondente');
        App::$utente->removeKey($this->nameForm . '_allegatiComunicazione');
    
    }

    public function returnToParent($propak, $close = true) {
        parent::returnToParent($close);
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();

        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $this->tipoProtocollo = $PARMENTE_rec['TIPOPROTOCOLLO'];

        //Out::msgInfo("idUtente", App::$utente->getKey('idUtente'));
        
        /*
         * Controllo se l'utente ha configurati i parametri per protocollare in altro ente
         */
        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            if ($meta['TIPO'] && $meta['URLREMOTO']) {
                $this->tipoProtocollo = $meta['TIPO'];
            }
        }
        
        if ($this->allegatiComunicazione == false) {
            Out::hide($this->divAllegatiCom);
        }
        
        $this->destinatari = $this->getParentObj()->getDestinatari();
        
        
        $this->GetHtmlRiepilogoDest();

        /*
         * Se è stata inviata la mail il partenza, mostro il messaggio se ci sono delle ricevute da protocollare
         */
        if ($this->destinatari[0]['IDMAIL']) {
            $pramail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAMAIL WHERE COMPAK = '$this->keyPasso' AND FLPROT=0 AND (TIPORICEVUTA = 'accettazione' OR TIPORICEVUTA = 'avvenuta-consegna')", true);
            if ($pramail_tab) {
                $html = "<div style=\"font-size:1.5em;padding-bottom:5px;\"><b>Attenzione! Ci sono " . count($pramail_tab) . " Ricevute, tra Accettazioni e Avvenute-Consegne, da protocollare</b></div>";
                Out::show($this->nameForm . "_divAlertRicevute");
                Out::html($this->nameForm . "_divAlertRicevute", $html);
            }
        }

        
        if ($inGestione == false) {
            if ($visibilitaPasso != "Aperto") {
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                    $perms = $this->praPerms->impostaPermessiPasso($propas_rec);
                    Out::checkDataButton($this->nameForm, $perms);
                    if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                        Out::attributo($this->nameForm . "_PROPAS[PROVISIBILITA]", "disabled");
                        Out::hide($this->nameForm . "_Apri");
                        Out::hide($this->nameForm . "_SbloccaCom");
                        Out::hide($this->nameForm . "_Invia");
                        Out::hide($this->nameForm . "_Risposta");
                        Out::hide($this->gridAllegati . "_delGridRow");
                        Out::hide($this->gridAllegati . "_addGridRow");
                        Out::hide($this->nameForm . "_divBottoniAllega");
                        Out::hide($this->nameForm . "_ProtocollaPartenza");
                        Out::hide($this->nameForm . "_PROTRIC_DESTINATARIO_butt");
                        Out::hide($this->nameForm . "_RimuoviProtocollaP");
                        Out::hide($this->nameForm . "_ProtocollaArrivo");
                        Out::hide($this->nameForm . "_RimuoviProtocollaA");
                        Out::hide($this->nameForm . "_PROTRIC_MITTENTE_butt");
                        Out::hide($this->nameForm . '_CreaNuovoPasso');
                        Out::hide($this->nameForm . '_NuovaComDaProt');
                    }
                }
            }
        }
        
        
        

        if ($this->praReadOnly == true) {
            $this->HideButton();
        }

        
    }
    private function AzzeraVariabili() {

        
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_divAlertRicevute');
        Out::hide($this->nameForm . '_ProtocollaPartenza');
        
    }
        
    function HideButton() {
        Out::hide($this->nameForm . '_ProtocollaPartenza');
        Out::hide($this->nameForm . '_RimuoviProtocollaP');
        Out::hide($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_ProtocollaArrivo');
        Out::hide($this->nameForm . '_RimuoviProtocollaA');
    }

    
    function GetHtmlRiepilogoDest() {
        
        if ($this->destinatari) {
            $html = "<div style=\"font-size:1.5em;text-decoration:underline;padding-bottom:5px;\"><b>RIEPILOGO DESTINATARI:</b></div>";
            foreach ($this->destinatari as $destinatario) {
                $html .= "<span style=\"font-size:1.2em;color:blue;\">" . $destinatario['NOME'] . ": " . $destinatario['MAIL'] . "</span><br>";
            }
            Out::html($this->nameForm . "_divRiepilogoDest", $html);
        }
    }


    private function switchIconeProtocolloP($Prot, $Metadati) {
        if (is_array($Prot)) {
            $numeroProt = $Prot['protocollo'];
            $idDocumento = $Prot['documento'];
        } else {
            $numeroProt = $Prot;
        }
        $numeroProt = (String) $numeroProt;
//
        $tipoProt = $this->tipoProtocollo;
        if ($tipoProt != 'Manuale') {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::show($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_RimuoviDocumentoP');
                Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::show($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                Out::show($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                Out::show($this->nameForm . '_DATAPROT_DESTINATARIO_field');
                if ($idDocumento) {
                    Out::hide($this->nameForm . '_ProtocollaPartenza');
                    Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                    Out::hide($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                    Out::hide($this->nameForm . '_DATAPROT_DESTINATARIO_field');
                }
            } elseif ($numeroProt != '' && $numeroProt != '0' && $Metadati) { //c'è il protocollo e ci sono sono i metadati -> sparisce l'icona, i campi non sono editabili
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::show($this->nameForm . '_RimuoviProtocollaP');

                Out::show($this->nameForm . '_PROTRIC_DESTINATARIO_field');
                Out::show($this->nameForm . '_ANNOPROT_DESTINATARIO_field');
                Out::show($this->nameForm . '_DATAPROT_DESTINATARIO_field');

                Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_butt');
//Out::show($this->nameForm . "_VediProtPartenza");
                Out::show($this->nameForm . "_GestioneProtocolloPartenza");
//                Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
//                Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
//                Out::hide($this->nameForm . '_RimuoviDocumentoP');
//                Out::hide($this->nameForm . '_VediIdDocPartenza');
//                if (($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') && $numeroProt != '') {
//                    Out::show($this->nameForm . "_BloccaAllegatiProt");
//                }
//                if ($idDocumento) {
//                    Out::attributo($this->nameForm . '_PRACOM[COMIDDOC]', "readonly", '0');
//                    Out::attributo($this->nameForm . '_PRACOM[COMDATADOC]', "readonly", '0');
//                    Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
//                    Out::show($this->nameForm . '_PRACOM[COMIDDOC]_field');
//                    Out::show($this->nameForm . '_PRACOM[COMDATADOC]_field');
//                    Out::show($this->nameForm . '_RimuoviDocumentoP');
//                    Out::show($this->nameForm . '_VediIdDocPartenza');
//                    if (($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') && $numeroProt != '') {
//                        Out::show($this->nameForm . "_BloccaAllegatiDoc");
//                    }
//                }
            } else { //c'è il protocollo, ma non ci sono i metadati -> sparisce icona +, campi non editabili, compare un cestino per cancellarli
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_ProtocollaPartenza'); //ridondante, l'hide viene fatto nella funzione Nascondi()
            }
            if ($idDocumento) {
                Out::attributo($this->nameForm . '_PRACOM[COMIDDOC]', "readonly", '0');
                Out::attributo($this->nameForm . '_PRACOM[COMDATADOC]', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::show($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::show($this->nameForm . '_RimuoviDocumentoP');
                Out::show($this->nameForm . '_VediIdDocPartenza');
                if ($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') {
                    Out::show($this->nameForm . "_BloccaAllegatiDoc");
                }
            } else {
                Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
                Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
                Out::hide($this->nameForm . '_RimuoviDocumentoP');
                Out::hide($this->nameForm . '_VediIdDocPartenza');
            }
//            if (($tipoProt == 'Iride' || $tipoProt == 'Jiride' || $tipoProt == 'Italsoft-remoto-allegati' || $tipoProt == 'Paleo4' || $tipoProt == 'Italsoft-ws') && $numeroProt != '') {
//                Out::show($this->nameForm . "_BloccaAllegati");
//                Out::html($this->nameForm . "_BloccaAllegati_lbl", "Aggiungi Allegati $nameButtonAddAlle");
//            }
            if (($tipoProt == 'Jiride' || $tipoProt == 'Paleo4') && $Metadati['DatiProtocollazione']['idMail']) {
                Out::show($this->nameForm . "_VerificaMailWs");
            }
        } else {
            Out::hide($this->nameForm . '_PRACOM[COMIDDOC]_field');
            Out::hide($this->nameForm . '_PRACOM[COMDATADOC]_field');
            Out::hide($this->nameForm . '_RimuoviDocumentoP');
            Out::hide($this->nameForm . '_VediIdDocPartenza');
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::show($this->nameForm . '_InviaProtocolloPar');
            } else {
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_ProtocollaPartenza'); //ridondante, l'hide viene fatto nella funzione Nascondi()
                if (isset($Metadati['DatiProtocollazione']['IdMailRichiesta'])) {
                    Out::show($this->nameForm . '_InviaProtocolloPar');
                }
            }
        }


        /*
         * Controllo Finale se utente abilitato al protocollo
         */

        /*
         * Verifico se è stato attivato l'utilizzo dei profili valore parametro=1
         */
        $filent_rec = $this->praLib->GetFilent(26);
        if ($filent_rec["FILDE1"] == 1) {
            $this->profilo = proSoggetto::getProfileFromIdUtente();

            /*
             * Utente disabilitato da profilo (solo arrivo o nega)
             */
            if ($this->profilo['PROT_ABILITATI'] == '1' || $this->profilo['PROT_ABILITATI'] == '3') {
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_RimuoviProtocollaP');
                Out::hide($this->nameForm . '_InviaProtocolloPar');
                Out::hide($this->nameForm . '_BloccaAllegatiProt');
                Out::hide($this->nameForm . '_PROTRIC_DESTINATARIO_butt');
                Out::attributo($this->nameForm . '_PROTRIC_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_ANNOPROT_DESTINATARIO', "readonly", '0');
                Out::attributo($this->nameForm . '_DATAPROT_DESTINATARIO', "readonly", '0');
            }
        }
    }

    public function protocollaPartenza() {
        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . '_PROPAS']['ROWID'], 'rowid');
        if (!$propas_rec) {
// Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
            $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
        $pramitDest_tab = $this->praLib->GetPraDestinatari($propas_rec['PROPAK'], 'codice', true);
        $oggetto = $this->praLib->GetOggettoProtPartenza($this->currGesnum, $this->keyPasso);
        $elementi['tipo'] = 'P';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $pracomP_rec['COMDAT'];
        $elementi['dati']['DenomComune'] = $denomComune;

        /*
         * Per retro compatibilità nel caso ci sia un vecchio proPaleo.class.php
         */
        $elementi['dati']['MittDest']['Denominazione'] = $pramitDest_tab[0]['NOME'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pramitDest_tab[0]['INDIRIZZO'];
        $elementi['dati']['MittDest']['CAP'] = $pramitDest_tab[0]['CAP'];
        $elementi['dati']['MittDest']['Citta'] = $pramitDest_tab[0]['COMUNE'];
        $elementi['dati']['MittDest']['Provincia'] = $pramitDest_tab[0]['PROVINCIA'];
        $elementi['dati']['MittDest']['Email'] = $pramitDest_tab[0]['MAIL'];
        $elementi['dati']['MittDest']['CF'] = $pramitDest_tab[0]['FISCALE'];

        /*
         * Nuovo tag MITTENTE dal 01/12/2016 (Principalmente per E-Lios)-->Prende i dati dello sportello on-line
         */
        $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
        $elementi['dati']['Mittente']['Denominazione'] = $anatsp_rec['TSPDEN'];
        $elementi['dati']['Mittente']['Indirizzo'] = $anatsp_rec['TSPIND'] . " " . $anatsp_rec['TSPNCI'];
        $elementi['dati']['Mittente']['CAP'] = $anatsp_rec['TSPCAP'];
        $elementi['dati']['Mittente']['Citta'] = $anatsp_rec['TSPCOM'];
        $elementi['dati']['Mittente']['Provincia'] = $anatsp_rec['TSPPRO'];
        $elementi['dati']['Mittente']['Email'] = $anatsp_rec['TSPPEC'];
        $elementi['dati']['Mittente']['CF'] = "";

        /*
         * Nuova versione destinatari multipli
         */
        $elementi['dati']['destinatari'] = array();
        foreach ($pramitDest_tab as $pramitDest_rec) {
            $destinatario = array();
            $destinatario['Denominazione'] = $pramitDest_rec['NOME'];
            $destinatario['Indirizzo'] = $pramitDest_rec['INDIRIZZO'];
            $destinatario['CAP'] = $pramitDest_rec['CAP'];
            $destinatario['Citta'] = $pramitDest_rec['COMUNE'];
            $destinatario['Provincia'] = $pramitDest_rec['PROVINCIA'];
            $destinatario['Email'] = $pramitDest_rec['MAIL'];
            $destinatario['CF'] = $pramitDest_rec['FISCALE'];
            $elementi['dati']['destinatari'][] = $destinatario;
        }

        $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
        if ($proges_rec['GESMETA']) {
            $metaDati = unserialize($proges_rec['GESMETA']);
            $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
            $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
        }
        $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $elementi['dati']['MittenteInterno'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoPar($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
//
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
//
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    function ControllaDati() {
//Implementiamo i controlli
        if (!$this->ControllaDataEdit()) {
            return false;
        }
        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] && strlen($_POST[$this->nameForm . '_ANNOPROT_DESTINATARIO']) != 4) {
            Out::msgStop("Errore", "Anno protocollo Partenza non corretto o mancante");
            return false;
        }
        if ($_POST[$this->nameForm . '_PROTRIC_MITTENTE'] && strlen($_POST[$this->nameForm . '_ANNORIC_MITTENTE']) != 4) {
            Out::msgStop("Errore", "Anno protocollo Arrivo non corretto o mancante");
            return false;
        }
        if ($this->flagAssegnazioniPasso) {
            if (!$this->ControlloDatiProtocolloPasso()) {
                return false;
            }
        }
        return true;
    }

    public function ControlloDatiProtocolloPasso() {
        /* Controllo responsabile alla firma variato */
        if (!$this->checkFrimaAllegato()) {
            Out::msgStop("Errore", "Non è possibile modificare Responsabile con Allegato alla Frima.<br>Rimuovere il documento alla firma.");
            return false;
        }
        /* Controllo firmatario configurato correttamente */
        $praFascicolo = new praFascicolo($this->currGesnum);
        $destinatario = $praFascicolo->setDestinatarioProtocollo($_POST[$this->nameForm . '_PROPAS']['PRORPA']);
        if (!$destinatario) {
            Out::msgStop("Errore", "Profilo responsabile del passo incompleto: codice soggetto interno mancante.");
            return false;
        }
        $uffici = $praFascicolo->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        if (!$uffici) {
            Out::msgStop("Errore", "Profilo responsabile del passo incompleto: ufficio soggetto interno mancante.");
            return false;
        }
        /* Controllo ufficio utente creatore configurato */
        $ufficioDefault = $this->proLib->GetUfficioUtentePredef();
        if (!$ufficioDefault) {
            Out::msgStop("Errore", "Profilo utente incompleto: ufficio o soggetto interno mancante.");
            return false;
        }
        return true;
    }
    
    
    function ControllaDataEdit() {
        /*
         * Controllo se il passo è stato aggiornato durante l'edit in corso
         */
        if ($this->keyPasso) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso);
            if ($propas_rec['PRODATEEDIT'] !== $this->iniDateEdit) {
                Out::msgStop("Errore", "Dati Modificati esternamente da altra sessione di lavoro durante la gestione.<br> Ricaricare il passo.");
                return false;
            }
        }
        return true;
    }
    
    public function CheckAssegnazionePasso() {
        $flagAssegnazionePasso = $this->praLibPasso->getFlagAssegnazionePasso();
        if ($flagAssegnazionePasso == false) {
            return false; // Flag Su Parametri vari Fascicolo Spento
        }
        if (!$this->keyPasso) {
            return true;
        }
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        if ($propas_rec['PROCLT']) {
            $clt_rec = $this->praLib->GetPraclt($propas_rec['PROCLT']);
            if ($clt_rec['CLTGESTPANEL'] == 0) {
                return true; // parametro gestione pannelli spento su tipo passo
            }
            $Param_rec = $this->praLib->decodParametriPasso($clt_rec['CLTMETAPANEL']);
            $AssegnazionePasso = false;
            foreach ($Param_rec as $Param) {
                if ($Param['DESCRIZIONE'] == 'Assegnazioni' && $Param['DEF_STATO'] == '1') {
                    $AssegnazionePasso = true;
                }
            }
            return $AssegnazionePasso;
        }
        return true; // Tipo Passo non settato
    }
    
    public function checkFrimaAllegato($prorpa = true, $Rowid = '') {
        if ($Rowid == 0) {
            return true;
        }
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        if ($prorpa == true) {
            if ($_POST[$this->nameForm . '_PROPAS']['PRORPA'] != $propas_rec['PRORPA']) {
                if (!$this->ControllaFrimaAllegato($propas_rec, $Rowid)) {
                    return false;
                }
            }
        } else {
            if (!$this->ControllaFrimaAllegato($propas_rec, $Rowid)) {
                return false;
            }
        }

        return true;
    }

    public function ControllaFrimaAllegato($propas_rec, $Rowid) {
        $proLibAllegati = new proLibAllegati();
        // $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        $count = 0;
        if ($Rowid) {
            $pasdoc_rec = $this->praLib->GetPasdoc($Rowid, "ROWID");
            $keyLink = 'PRAM.PASDOC.' . $Rowid;
            $AnaDoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
            $docfirma_check = $proLibAllegati->GetDocfirma($AnaDoc_rec['ROWID'], 'rowidanadoc');
            if ($docfirma_check && $docfirma_check['FIRDATA'] == '') {
                return false;
            }
            return true;
        }
        foreach ($this->passAlle as $key => $allegato) {
            if (!$allegato['ROWID']) {
                continue;
            }  // Controllare di ogni allegato se è presente il blocco alla firma se è stato modificato il responsabile del passo
            $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], "ROWID");
            $keyLink = 'PRAM.PASDOC.' . $pasdoc_rec['ROWID'];
            $AnaDoc_rec = $this->proLib->GetAnaDocFromDocLink($propas_rec['PASPRO'], $propas_rec['PASPAR'], $keyLink);
            $docfirma_check = $proLibAllegati->GetDocfirma($AnaDoc_rec['ROWID'], 'rowidanadoc');
            if ($docfirma_check && $docfirma_check['FIRDATA'] == '') {
                $count++;
                break;
            }
        }
        if ($count > 0) {
            return false;
        }
        return true;
    }

    function AggiornaPartenzaDaPost() {
//$rowid = null;
        $partenza_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '$this->keyPasso' AND COMTIP = 'P'", false);
//        if ($partenza_rec) {
//            $rowid = $partenza_rec['ROWID'];
//            $partenza_rec = $_POST[$this->nameForm . '_PRACOM'];
//            $partenza_rec['ROWID'] = $rowid;
//        } 
//        else {
//            $partenza_rec["COMMLD"] = $this->destinatari[0]['MAIL'];
//            $partenza_rec["COMIND"] = $this->destinatari[0]['INDIRIZZO'];
//            $partenza_rec["COMCAP"] = $this->destinatari[0]['CAP'];
//            $partenza_rec["COMCIT"] = $this->destinatari[0]['COMUNE'];
//            $partenza_rec["COMPRO"] = $this->destinatari[0]['PROVINCIA'];
//            $partenza_rec["COMNOM"] = strip_tags($this->destinatari[0]['NOME']);
//            $partenza_rec["COMCDE"] = $this->destinatari[0]['CODICE'];
//            $partenza_rec["COMFIS"] = $this->destinatari[0]['FISCALE'];
//            $partenza_rec["COMDAT"] = $this->destinatari[0]['DATAINVIO'];
//            $partenza_rec["COMORA"] = $this->destinatari[0]['ORAINVIO'];
//            $partenza_rec["COMIDMAIL"] = $this->destinatari[0]['IDMAIL'];
//        }
        $partenza_rec["COMMLD"] = $this->destinatari[0]['MAIL'];
        $partenza_rec["COMIND"] = $this->destinatari[0]['INDIRIZZO'];
        $partenza_rec["COMCAP"] = $this->destinatari[0]['CAP'];
        $partenza_rec["COMCIT"] = $this->destinatari[0]['COMUNE'];
        $partenza_rec["COMPRO"] = $this->destinatari[0]['PROVINCIA'];
        $partenza_rec["COMNOM"] = strip_tags($this->destinatari[0]['NOME']);
        $partenza_rec["COMCDE"] = $this->destinatari[0]['CODICE'];
        $partenza_rec["COMFIS"] = $this->destinatari[0]['FISCALE'];
        $partenza_rec["COMDAT"] = $this->destinatari[0]['DATAINVIO'];
        $partenza_rec["COMORA"] = $this->destinatari[0]['ORAINVIO'];
        $partenza_rec["COMIDMAIL"] = $this->destinatari[0]['IDMAIL'];
//
        $partenza_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNOPROT_DESTINATARIO'] . $_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'];
        $partenza_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_DESTINATARIO'];
//
        $partenza_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_PARTENZA'];
        $partenza_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_DESTINATARIO'];
        $partenza_rec['COMGRS'] = $_POST[$this->nameForm . '_PRACOM']['COMGRS'];
        $partenza_rec['COMFSA'] = $_POST[$this->nameForm . '_PRACOM']['COMFSA'];
        if ($partenza_rec['ROWID']) {
//            if ($partenza_rec['COMDFI'] == "") {
//                if ($partenza_rec['COMGRS']) {
//                    if ($partenza_rec['COMDRI'] && $partenza_rec['COMDAT']) {
//                        $da_ta = $partenza_rec['COMDAT'];
//                    }
//                    if ($partenza_rec['COMDRI'] == "" && $partenza_rec['COMDAT']) {
//                        $da_ta = $partenza_rec['COMDAT'];
//                    }
//                    if ($partenza_rec['COMDRI'] && $partenza_rec['COMDAT'] == "") {
//                        $da_ta = $partenza_rec['COMDRI'];
//                    }
//                    $partenza_rec['COMDFI'] = $this->proLib->AddGiorniToData($da_ta, $partenza_rec['COMGRS']);
//                }
//            }
            $updateP_Info = "Oggetto: Aggiornamento comunicazione in partenza su PRACOM n. " . $partenza_rec['ROWID'] . " del passo " . $partenza_rec['COMPAK'];
            if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $partenza_rec, $updateP_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento su Comunicazione.");
                return false;
            }
        } else {
            $retInsertPartenza = $this->InsertPartenza();
            if (!$retInsertPartenza) {
                Out::msgStop("ATTENZIONE!", "Errore di Inizializzazione Comunicazione in Partenza");
                return false;
            }
        }
        return true;
    }

    function RegistraDestinatari() {
        $new_seq = 0;
        foreach ($this->destinatari as $dest) {
            if ($dest['ROWID'] == 0) {
                $new_seq += 10;
                $dest['KEYPASSO'] = $this->keyPasso;
                $dest['TIPOCOM'] = "D";
                $dest['SEQUENZA'] = $new_seq;
                // Tolgo il colore Arancione dal nome
                $dest['NOME'] = strip_tags($dest['NOME']);
//Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM PARTENZA
                $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
                if ($pracom_recP) {
                    $dest['ROWIDPRACOM '] = $pracom_recP['ROWID'];
                    $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO'], $dest['DATARISCONTRO']);
                }
                $insert_Info = 'Oggetto: Inserimento destinatario ' . $dest['NOME'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $insert_Info)) {
                    return false;
                }
            } else {
                $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
                if ($pracom_recP) {
                    $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO'], $dest['DATARISCONTRO']);
                }
                unset($dest['ACCETTAZIONE']);
                unset($dest['CONSEGNA']);
                unset($dest['SBLOCCA']);
                unset($dest['VEDI']);
                unset($dest['FIRMATOCDS']);
                $update_Info = 'Oggetto: Aggiornamento destinatario ' . $dest['NOME'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $dest, $update_Info)) {
                    return false;
                }
            }
        }
        $this->ordinaDestinatari();
        return true;
    }

    function ordinaDestinatari() {
        $new_seq = 0;
        foreach ($this->destinatari as $key => $destinatario) {
            $new_seq += 10;
            $this->destinatari[$key]['SEQUENZA'] = $new_seq;
        }
    }

    function AggiornaArrivo() {
        if ($_POST[$this->nameForm . '_DESC_MITTENTE'] != '') {
            $arrivo_rec = $this->praLib->GetPracomA($this->keyPasso);
            if (!$arrivo_rec) {
// Preparo Inserimento PRACOM rec
//
                $pracomA_rec['COMTIP'] = 'A';
                $pracomA_rec['COMNUM'] = $this->currGesnum;
                $pracomA_rec['COMPAK'] = $this->keyPasso;
                $pracomA_rec['COMDAT'] = $_POST[$this->nameForm . '_dataArrivo'];
                $pracomA_rec['COMORA'] = $_POST[$this->nameForm . '_oraArrivo'];
                $pracomA_rec['COMCDE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $pracomA_rec['COMNOM'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $pracomA_rec['COMFIS'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $pracomA_rec['COMMLD'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $pracomA_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNORIC_MITTENTE'] . $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
                $pracomA_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_MITTENTE'];
                $pracomA_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $pracomA_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_MITTENTE'];
                $pracomA_rec['COMRIF'] = $_POST[$this->nameForm . '_RIFERIMENTO'];
                $pracomA_rec['COMIDMAIL'] = $this->daMail['IDMAIL'];
                $insertA_Info = "Oggetto: Inserimento comunicazione in arrivo su PRACOM del passo " . $pracomA_rec['COMPAK'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $pracomA_rec, $insertA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRACOM.");
                    return false;
                }
//
// Preparo Inserimento PRAMITDEST rec
//
                $arrivo_rec = $this->praLib->GetPracomA($this->keyPasso);
                $praMitDest_rec['TIPOCOM'] = 'M';
                $praMitDest_rec['KEYPASSO'] = $this->keyPasso;
                $praMitDest_rec['DATAINVIO'] = $_POST[$this->nameForm . '_dataArrivo'];
                $praMitDest_rec['ORAINVIO'] = $_POST[$this->nameForm . '_oraArrivo'];
                $praMitDest_rec['CODICE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $praMitDest_rec['NOME'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $praMitDest_rec['FISCALE'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $praMitDest_rec['MAIL'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $praMitDest_rec['TIPOINVIO'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
// Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM ARRIVO
                $pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
                if ($pracom_recA) {
                    $praMitDest_rec['ROWIDPRACOM '] = $pracom_recA['ROWID'];
                }
                $praMitDest_rec['IDMAIL'] = $this->daMail['IDMAIL'];
                $insertA_Info = "Oggetto: Inserimento comunicazione in arrivo su PRAMITDEST del passo " . $praMitDest_rec['KEYPASSO'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $insertA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Inserimento Arrivo su PRAMITDEST.");
                    return false;
                }
            } else {
//
// Preparo Aggiornamento PRACOM rec
//
                $rowid = $arrivo_rec['ROWID'];
                $arrivo_rec['COMNUM'] = $this->currGesnum;
                $arrivo_rec['COMDAT'] = $_POST[$this->nameForm . '_dataArrivo'];
                $arrivo_rec['COMORA'] = $_POST[$this->nameForm . '_oraArrivo'];
                $arrivo_rec['COMCDE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $arrivo_rec['COMNOM'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $arrivo_rec['COMFIS'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $arrivo_rec['COMMLD'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $arrivo_rec['COMPRT'] = $_POST[$this->nameForm . '_ANNORIC_MITTENTE'] . $_POST[$this->nameForm . '_PROTRIC_MITTENTE'];
                $arrivo_rec['COMDPR'] = $_POST[$this->nameForm . '_DATAPROT_MITTENTE'];
                $arrivo_rec['COMTIN'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $arrivo_rec['COMNOT'] = $_POST[$this->nameForm . '_NOTE_MITTENTE'];
                $arrivo_rec['ROWID'] = $rowid;
                $updateA_Info = "Oggetto: Aggiornamento comunicazione in arrivo su PRACOM n. " . $arrivo_rec['ROWID'] . " del passo " . $arrivo_rec['COMPAK'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $arrivo_rec, $updateA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Arrivo PRACOM.");
                    return false;
                }
//
// Preparo Aggiornamento PRAMITDEST rec
//
                $praMitDest_rec = $this->praLib->GetPraArrivo($this->keyPasso);
                $praMitDest_rec['CODICE'] = $_POST[$this->nameForm . '_MITTENTE'];
                $praMitDest_rec['NOME'] = $_POST[$this->nameForm . '_DESC_MITTENTE'];
                $praMitDest_rec['DATAINVIO'] = $_POST[$this->nameForm . '_dataArrivo'];
                $praMitDest_rec['ORAINVIO'] = $_POST[$this->nameForm . '_oraArrivo'];
                $praMitDest_rec['FISCALE'] = $_POST[$this->nameForm . '_CODFISC_MITTENTE'];
                $praMitDest_rec['MAIL'] = $_POST[$this->nameForm . '_EMAIL_MITTENTE'];
                $praMitDest_rec['TIPOINVIO'] = $_POST[$this->nameForm . '_TIPO_ARRIVO'];
                $praMitDest_rec['ROWIDPRACOM '] = $arrivo_rec['ROWID'];
                $updateA_Info = "Oggetto: Aggiornamento comunicazione in arrivo su PRAMITDEST del passo " . $praMitDest_rec['KEYPASSO'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $updateA_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Arrivo su PRAMITDEST.");
                    return false;
                }
            }
            if ($this->allegatiComunicazione) {
                if (!$this->RegistraAllegatiCom($this->keyPasso)) {
                    Out::msgStop("ERRORE", "Aggiornamento Allegati Comunicazione fallito");
                    return false;
                }
            }
            $this->chiudiForm = true;
        } else {
            if ($_POST[$this->nameForm . '_dataArrivo']) {
                Out::msgStop("ERRORE!!!", "Mittente Obbligatorio");
                $this->chiudiForm = false;
                return false;
            }
            return false;
        }
        return true;
    }
    

    function RegistraAllegatiCom($keyPasso) {
        foreach ($this->allegatiComunicazione as $allegato) {
            if ($allegato['ROWID'] == 0) {
                $destinazione = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
                if (!$destinazione) {
                    Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                    return false;
                }
                $pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
                if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                    return false;
                }
                $pasdoc_rec = array();
                $pasdoc_rec['PASKEY'] = $keyPasso;
                $pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
                $pasdoc_rec['PASLNK'] = "allegato://" . $allegato['FILENAME'];
                $pasdoc_rec['PASUTC'] = "";
                $pasdoc_rec['PASUTE'] = "";
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
//$pasdoc_rec['PASCLA'] = $allegato['PROVENIENZA'];
                $pasdoc_rec['PASCLA'] = "COMUNICAZIONE " . $pracom_recA['ROWID'];
                $pasdoc_rec['PASNAME'] = $allegato['FILEORIG'];
                $pasdoc_rec['PASDATADOC'] = date("Ymd");
                $pasdoc_rec['PASORADOC'] = date("H:i:s");
                $pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $pasdoc_rec['PASFIL']);
                $insert_Info = "Oggetto: inserimento allegato comunicazione  " . $pasdoc_rec['PASLNK'] . " del passo " . $pasdoc_rec['PASKEY'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                    Out::msgStop("ATTENZIONE!", "1 - Errore di Inserimento Allegato Comunicazione.");
                    return false;
                }
            } else {
                $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], 'ROWID');
                $pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
                $update_Info = "Oggetto: Aggiornamento allegati comunicazione: " . $pasdoc_rec['PASKEY'] . " - " . $pasdoc_rec['PASLNK'];
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Aggiornamento Allegato Comunicazione.");
                    return false;
                }
            }
        }

        return true;
    }

    function getRicAllegatiWs($tipoWs, $returnEvent) {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($tipoWs, false, "P"); //non aggiungo filtri  non serve estrarre il base64
        $arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->tipoProtocollo, false); //non aggiungo filtri  non serve estrarre il base64
        $arrayDocWsRicevute = array();
        foreach ($arrayDocRicevute['pramail_rec'] as $ricevuta) {
            $arrayDocWsRicevute[] = $ricevuta['ROWID'];
            $msgInfo = "Attenzione! Ci sono " . count($arrayDocWsRicevute) . " ricevute, tra Accettazioni e Avvenute-Consegna, da protocollare.<br>Verranno protocollate anche se non verrà selezionato nessun allegato di seguito.";
        }
        if (!$arrayDoc) {
            Out::msgInfo("Metti alla firma", "Allegati passo non trovati");
            return false;
        }
        $arrayDocWs = array();
        foreach ($arrayDoc['pasdoc_rec'] as $documento) {
            $arrayDocWs[] = $documento['ROWID'];
        }
        praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, $this->nameForm, $returnEvent, $msgInfo);
    }


    private function lanciaProtocollaWS() {
        $idScelti = array();
        if ($_POST['retKey']) {
            $idAllegatiScelti = explode(",", $_POST['retKey']);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }
//ripeto l'estrazione degli allegati filtrando solo quelli non selezionati dall'array
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, true, "P", $idScelti); //aggiungo i filtri alla selezione!
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
//
        $Proges_rec = $this->praLib->GetProges($this->currGesnum, 'codice');

        /*
         * Carico un array con i dati di protocollazione dell'aggregato
         */
        $arrDatiProtAggr = array();
        if ($Proges_rec['GESSPA'] != 0) {
            $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($Proges_rec['GESSPA']);
        }

        /*
         * Se c'è il tipo protocolli nell'aggregato, sovrascrivo il tipo protocollo dell'ente
         */
        if ($arrDatiProtAggr['TipoProtocollo']) {
            $this->tipoProtocollo = $arrDatiProtAggr['TipoProtocollo'];
        }

        switch ($this->tipoProtocollo) {
            case "Paleo4":
                $arrayDocFiltrati = $praFascicolo->GetAllegatiNonProt($arrayDoc, "Paleo4");
                $elementi = $this->protocollaPartenza();
                $model = 'proPaleo4.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDocFiltrati['arrayDoc']) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDocFiltrati['arrayDoc']['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDocFiltrati['arrayDoc']['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proPaleo4.class.php';
                $proPaleo = new proPaleo4();
                $valore = $proPaleo->protocollazionePartenza($elementi);
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgBlock('', 3000, false, "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDocFiltrati['arrayDoc']['pasdoc_rec'], "P");
                    if ($arrayDocFiltrati['strNoProt']) {
                        Out::msgInfo("Protocollazione Partenza", $arrayDocFiltrati['strNoProt']);
                    }
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Paleo":
                $elementi = $this->protocollaPartenza();
                $model = 'proPaleo.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }

                include_once ITA_BASE_PATH . '/apps/Protocollo/proPaleo.class.php';
                $proPaleo = new proPaleo();
                $valore = $proPaleo->protocollazionePartenza($elementi);
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgBlock('', 3000, false, "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "WSPU":
                $this->ProtocolloICCSP($idAllegatiScelti);
                break;
            case "Infor":
                $elementi = $this->protocollaPartenza();
                $model = 'proInforJProtocollo.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proInforJProtocollo.class.php';
                $proInforJProtocollo = new proInforJProtocollo();
                $valore = $proInforJProtocollo->inserisciPartenza($elementi);
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        //Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                        Out::msgBlock('', 3000, false, "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Iride":
                $elementi = $this->protocollaPartenza();
                $model = 'proIride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proIride.class.php';
                $proIride = new proIride();
                $valore = $proIride->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $elementi['DocNumber'] = $valore['RetValue']['DatiProtocollazione']['DocNumber']['value'];
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Jiride":
                $elementi = $this->protocollaPartenza();
                $model = 'proJiride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                    $elementi['dati']['DocumentiRicevute'] = $arrayDoc['Ricevute'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proJiride.class.php';
                $proIride = new proJiride();
                $proIride->setKeyConfigParams($arrDatiProtAggr['MetadatiProtocollo']['CLASSIPARAMETRI'][$this->tipoProtocollo]['KEYPARAMWSPROTOCOLLO']);
                $valore = $proIride->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $elementi['DocNumber'] = $valore['RetValue']['DatiProtocollazione']['DocNumber']['value'];
                    $elementi['dati']['Fascicolazione']['Anno'] = $anno;
                    $elementi['dati']['Fascicolazione']['Numero'] = $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $pracom_rec['COMAMMPR'] = $valore['RetValue']['DatiProtocollazione']['CodAmm']['value'];
                    $pracom_rec['COMAOOPR'] = $valore['RetValue']['DatiProtocollazione']['CodAoo']['value'];
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                    $praFascicolo->bloccaRicevute($this, $arrayDoc['pramail_rec']);
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                    return false;
                }
                break;
            case "HyperSIC":
                $elementi = $this->protocollaPartenza();
                $model = 'proHyperSIC.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proHyperSIC.class.php';
                $proHyperSIC = new proHyperSIC();
                $valore = $proHyperSIC->InsertProtocolloGenerale($elementi, "P");
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Italsoft-remoto-allegati":
                $idAllegati = array();
                $this->allegatiPrtSel = array();
                $idAllegatiStr = "";
                if ($arrayDoc) {
                    foreach ($arrayDoc["pasdoc_rec"] as $pasdoc_rec) {
                        $idAllegati[] = $pasdoc_rec['ROWID'];
                        $this->allegatiPrtSel[]['ROWID'] = $pasdoc_rec['ROWID'];
                    }
                    $idAllegatiStr = implode("|", $idAllegati);
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                $accLib = new accLib();
                $utenteWs = $accLib->GetUtenteProtRemoto(App::$utente->getKey('idUtente'));
                if (!$utenteWs) {
                    Out::msgStop("Protocollo Remoto", "Utente remoto non definito!");
                    break;
                }
                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $this->keyPasso . "' AND COMTIP='P'", false);
                $model = 'utiIFrame';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['returnModel'] = $this->nameForm;
                $_POST['returnEvent'] = 'returnItalsoftRemotoAllegatiP';
                $_POST['retid'] = $this->nameForm . '_protocollaRemotoPartenza';
//                $devLib = new devLib();
//                $parametro = $devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
//                $url_param = $parametro['CONFIG'];
                $envLibProt = new envLibProtocolla();
                $url_param = $envLibProt->getParametriProtocolloRemoto();
                $_POST['src_frame'] = $url_param . "&access=direct&accessreturn=&accesstoken=nobody&model=menDirect&menu=PR_HID&prog=PR_WSPRA&topbar=0&homepage=0&noSave=1&utenteWs=" . $utenteWs . "&azione=CPALL&numPro=" . $pracomP_rec['COMPRT'] . "&idall=$idAllegatiStr&passo=" . $pracomP_rec['ROWID'];
                $_POST['title'] = "Protocollazione Remota Comunicazione in Partenza";
                $_POST['returnKey'] = 'protocollaWS';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case "E-Lios":
                $elementi = $this->protocollaPartenza();
                $model = 'proELios.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proELios.class.php';
                $proELios = new proELios();
                $valore = $proELios->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }

                break;
            case "Italsoft-ws":
                $elementi = $this->protocollaPartenza();
                if ($this->mettiAllaFirma) {
                    $elementi['dati']['mettiAllaFirma'] = $this->mettiAllaFirma;
                }
                $model = 'proItalprot.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                    $elementi['dati']['pasdoc_rec'] = $arrayDoc['pasdoc_rec'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proItalprot.class.php';
                $proItalprot = new proItalprot();
                $valore = $proItalprot->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $elementi['dati']['Fascicolazione']['Anno'] = $anno;
                    $elementi['dati']['Fascicolazione']['Numero'] = $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $arrayDoc['pasdoc_rec'] = $valore['rowidAllegati'];
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Sici":
                $elementi = $this->protocollaPartenza();
                $model = 'proSici.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proSici.class.php';
                $proSici = new proSici();
                $valore = $proSici->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = $valore['RetValue']['DatiProtocollazione']['Anno']['value'];
                    $elementi['dati']['Fascicolazione']['Numero'] = $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $elementi['dati']['Fascicolazione']['Anno'] = $anno;
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            case "Leonardo":
                $elementi = $this->protocollaPartenza();
                $model = 'proELios.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proLeonardo.class.php';
                $proLeonardo = new proLeonardo();
                $valore = $proLeonardo->InserisciProtocollo($elementi, "P");
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
//                    else {
//                        Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
//                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }

                break;
            case "Kibernetes":
                $elementi = $this->protocollaPartenza();
                $model = 'proKibernetesProt.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proELios.class.php';
                $proKibernetes = new proKibernetesProt();
                $valore = $proKibernetes->inserisciProtocollazionePartenza($elementi);
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }

                break;
            case "CiviliaNext":
                $elementi = $this->protocollaPartenza();
                $model = 'proCiviliaNext.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                if ($arrayDoc) {
                    $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
                    $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
                }
                include_once ITA_BASE_PATH . '/apps/Protocollo/proCiviliaNext.class.php';
                $proCivilia = new proCiviliaNext();
                $valore = $proCivilia->inserisciProtocollazionePartenza($elementi);
                if ($valore['Status'] == "0") {
                    $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                    Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']); //03.10.2013 Mario
                    Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
                    $pracom_rec = array();
                    $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                    $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                    $anno = date("Y");
                    $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                    $pracom_rec['COMDPR'] = $this->workDate;
                    $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                    if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                        Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                        break;
                    }
                    $this->switchIconeProtocolloP($pracom_rec['COMPRT'], $valore['RetValue']);
                    $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                } else {
                    Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
                }
                break;
            default:
                break;
        }

        /*
         * Se ci sono i metadati e il tipo protocollo nell'aggreggato, li inserisco nell'array elementi
         */
        $elementi['TipoProtocollo'] = $this->tipoProtocollo;
        if ($arrDatiProtAggr) {
            $elementi['MetaDatiProtocollo'] = $arrDatiProtAggr['MetadatiProtocollo'];
        }

        /*
         * Se Attiva lancio la fascicolazione
         */
        $Filent_Rec = $this->praLib->GetFilent(29);
        if ($Filent_Rec['FILVAL'] == 1) {
            $ret = $this->lanciaFascicolazioneWS($elementi, "P");
            if ($ret['Status'] == "-1") {
                Out::msgStop("Errore in Fascicolazione", $ret['Message']);
            }
        }

        /*
         * Se la protocollazione è andata a bun fine, mostro il messaggio
         */
        if ($valore['Status'] == "0") {
            Out::msgInfo("Protocollazione Partenza", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value'] . ".<br>" . $ret['Message'] . "<br><br><span style=\"color:red;\"><b>" . $valore['errString'] . "</b></span>");
        }

        $this->Dettaglio($this->keyPasso, "propak");
    }

    
    public function bloccaAllegati($chiave, $rowidArr = array(), $tipo = "A") {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->bloccaAllegati($chiave, $rowidArr, $tipo);
    }

    public function ProtocolloICCSA() {

        if ($_POST[$this->nameForm . '_PROTRIC_MITTENTE'] == '') {
//$elementi = $this->protocollaPartenza();
            $elementi = $this->protocollaArrivo();
            $propas_rowid = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
            $model = 'proHWS.class';
//$model = 'proGest';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//gestione documenti allegati
            $praFascicolo = new praFascicolo($this->currGesnum);
            $praFascicolo->setChiavePasso($this->keyPasso);
            $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("WSPU", true, "A");

//controllo che ci siano gli allegati
            if ($arrayDoc) {
//$elementi['dati']['Principale'] = $arrayDoc['Principale'];
                $elementi['dati']['Allegati'] = $arrayDoc['Allegati'];
//fine documenti allegati
            }
//qui parte relativa alla ricerca anagrafe
            if (isset($this->idCorrispondente) && $this->idCorrispondente != '') {
                $elementi['dati']['corrispondente'] = $this->idCorrispondente;
//una volta settato il corrispondente lo svuoto per essere pronto per la prossima protocollazione
                $this->idCorrispondente = '';
            } else {
                /**
                 * se non c'è il codice del corrispondente non proseguo con la protocollazione
                 * faccio la ricerca del corrispondente
                 * dalla fase di ricerca si rilancia la protocollazione col codice settato
                 */
                $this->ControllaAnagrafeICCS($elementi, 'A');
                return;
            }
//fine parte relativa alla ricerca anagrafe

            $proHWS = new proHWS();
            $elementi['dati']['ufficio'] = $this->praLib->getUfficioHWS($_POST[$this->nameForm . '_PROGES']['GESNUM']);
            $valore = $proHWS->protocollazioneIngresso($elementi);
            if ($valore['Status'] == "0") {
                $propas_rec = $this->praLib->GetPropas($propas_rowid, 'rowid');
                if (!$propas_rec) {
                    $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
                }
//$pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
//                $pracomA_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK']
//                                . "' AND COMTIP='A' AND COMRIF='" . $pracomP_rec['ROWID'] . "'", false);
//Out::valore($this->nameForm . '_PROTRIC_MITTENTE', substr($valore['RetValue']['DatiProtocollazione']['proNum']['value'], 4));
                Out::valore($this->nameForm . '_PROTRIC_MITTENTE', $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                Out::valore($this->nameForm . '_DATAPROT_MITTENTE', $this->workDate);
                $pracom_rec = array();
//salvo i metadati
                $meta = array();
                $meta['Arrivo'] = $valore['RetValue']; //in previsione di fare eventualmente una distinzione tra dati salvati in partenza o in arrivo
                $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                $pracom_rec['ROWID'] = $pracomA_rec['ROWID'];
                $anno = date("Y");
                $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                $pracom_rec['COMDPR'] = $this->workDate;
                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                    return;
                }
                $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "A");
                Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                $this->Dettaglio($propas_rec['PROPAK']);
//$this->switchIconeProtocolloA($pracom_rec['COMPRT'], $valore['RetValue']);
            } else {
                Out::msgStop("Errore in Protocollazione", $valore['Message']);
            }
        }
    }
    
    
    public function ProtocolloICCSP($idAllegatiScelti = array()) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso);
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);

//        if ($_POST[$this->nameForm . '_PROTRIC_DESTINATARIO'] == '') {
        if ($pracomP_rec['COMPRT'] == '') {
            $elementi = $this->protocollaPartenza();

            $model = 'proHWS.class';

            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//gestione documenti allegati
            $praFascicolo = new praFascicolo($this->currGesnum);
            $praFascicolo->setChiavePasso($this->keyPasso);
            foreach ($idAllegatiScelti as $id) {
                $idScelti[] = substr($id, 1);
            }
            if (!$idAllegatiScelti) {
                $idAllegatiScelti = "NO";
            }
            $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione("WSPU", true, "P", $idScelti);

            if ($arrayDoc) {
                $elementi['dati']['Allegati'] = $arrayDoc['Allegati'];
            }
//qui parte relativa alla ricerca anagrafe
            if (isset($this->idCorrispondente) && $this->idCorrispondente != '') {
                $elementi['dati']['corrispondente'] = $this->idCorrispondente;
//una volta settato il corrispondente lo svuoto per essere pronto per la prossima protocollazione
                $this->idCorrispondente = '';
            } else {
                /**
                 * se non c'è il codice del corrispondente non proseguo con la protocollazione
                 * faccio la ricerca del corrispondente
                 * dalla fase di ricerca si rilancia la protocollazione col codice settato
                 */
                $this->ControllaAnagrafeICCS($elementi, 'P');
                return;
            }

            $proHWS = new proHWS();
            $elementi['dati']['ufficio'] = $this->praLib->getUfficioHWS($_POST[$this->nameForm . '_PROGES']['GESNUM']);
            $valore = $proHWS->protocollazioneUscita($elementi);


            if ($valore['Status'] == "0") {
                if (!$propas_rec) {
                    $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
                }
                $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
                Out::valore($this->nameForm . '_PROTRIC_DESTINATARIO', $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                Out::valore($this->nameForm . '_DATAPROT_DESTINATARIO', $this->workDate);
//salvo i metadati
//                $meta = array();
                $pracom_rec = array();
                $anno = date("Y");
                $pracom_rec['COMMETA'] = serialize($valore['RetValue']);
                $pracom_rec['ROWID'] = $pracomP_rec['ROWID'];
                $pracom_rec['COMPRT'] = $anno . $valore['RetValue']['DatiProtocollazione']['proNum']['value'];
                $pracom_rec['COMDPR'] = $this->workDate;
                $update_Info = "Oggetto rowid:" . $pracom_rec['ROWID'] . ' num:' . $pracom_rec['PRONUM'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                    Out::msgStop("Errore in Aggionamento", "Aggiornamento dopo inserimento Protocollo fallito.");
                    return;
                }
                Out::msgInfo("OK", "Protocollazione avvenuta con successo al n. " . $valore['RetValue']['DatiProtocollazione']['proNum']['value']);
                $this->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], "P");
                $this->Dettaglio($propas_rec['PROPAK']);
            } else {
                Out::msgStop("Errore in Protocollazione Partenza", $valore['Message']);
            }
        }
    }

    function ControllaAnagrafeICCS($elementi = array(), $tipo = 'P') {
        if (!$elementi['dati']) {
            Out::msgStop("Attenzione", "Specificare dei parametri per la protocollazione");
            return false;
        }
        $model = 'proHWS.class';
        if ($tipo == 'P') {
            $elementi = $this->protocollaPartenza();
        } else {
            $elementi = $this->protocollaArrivo();
        }
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        $ricerca = array();
        if (isset($elementi['dati']['MittDest']['Denominazione'])) {
            $ricerca['descrizione'] = $elementi['dati']['MittDest']['Denominazione'];
        }
        if (isset($elementi['dati']['MittDest']['CF'])) {
            $ricerca['idfiscale'] = $elementi['dati']['MittDest']['CF'];
        }
        if (isset($elementi['dati']['MittDest']['Indirizzo'])) {
            $ricerca['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
        }
        if (isset($elementi['dati']['MittDest']['CAP'])) {
            $ricerca['cap'] = $elementi['dati']['MittDest']['CAP'];
        }
        if (isset($elementi['dati']['MittDest']['Citta'])) {
            $ricerca['citta'] = $elementi['dati']['MittDest']['Citta'];
        }
        if (isset($elementi['dati']['MittDest']['Provincia'])) {
            $ricerca['provincia'] = $elementi['dati']['MittDest']['Provincia'];
        }
        if (isset($elementi['dati']['MittDest']['Email'])) {
            $ricerca['email'] = $elementi['dati']['MittDest']['Email'];
        }
        $ritorno = $proHWS->cercaRubrica($ricerca);
        /**
         * gestione del risultato della ricerca
         */
        if ($ritorno['Status'] != '0') {
            Out::msgStop("Errore", $ritorno['Message']);
            return false;
        }
//se non ci sono record trovati procedo con l'inserimento in anagrafica
        if (!$ritorno['RetValue']) {
            $this->inserisciRubricaWS($tipo);
            return;
        }
        if (count($ritorno['RetValue']) == 1) {
//se è stato trovato un solo record
            $this->datiRubricaWS = $ritorno['RetValue'][0];
            if ($this->datiRubricaWS['codiceFiscale'] == $elementi['dati']['MittDest']['CF'] && $this->datiRubricaWS['indirizzo'] == $elementi['dati']['MittDest']['Indirizzo'] && $this->datiRubricaWS['citta'] == $elementi['dati']['MittDest']['Citta']) {
//se i dati corrispondono...
                $this->idCorrispondente = $this->datiRubricaWS['codice'];
                if ($tipo == 'P') {
                    $this->ProtocolloICCSP();
                } else {
                    $this->ProtocolloICCSA();
                }
            } else {
//se i dati non corrispondono del tutto...
                if ($tipo == 'P') {
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica: <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Inserisci Nuovo' => array('id' => $this->nameForm . '_InserisciRubricaWSP', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWSP', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                } else {
                    Out::msgQuestion("Selezione", "&Egrave; stata trovata la seguente anagrafica: <br>
                Nominativo: " . $ritorno['RetValue'][0]['nome'] . " " . $ritorno['RetValue'][0]['cognome'] . "<br>
                Rag. Sociale: " . $ritorno['RetValue'][0]['ragioneSociale'] . "<br>
                Cod. Fisc.: " . $ritorno['RetValue'][0]['codiceFiscale'] . "<br>
                P. Iva: " . $ritorno['RetValue'][0]['partitaIva'] . "<br>
                Indirizzo: " . $ritorno['RetValue'][0]['indirizzo'] . "<br>
                " . $ritorno['RetValue'][0]['cap'] . " " . $ritorno['RetValue'][0]['citta'] . " (" . $ritorno['RetValue'][0]['prov'] . ")<br>
                Scegliere L'opzione desiderata.", array(
                        'F8-Inserisci Nuovo' => array('id' => $this->nameForm . '_InserisciRubricaWSA', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma Selezione' => array('id' => $this->nameForm . '_ConfermaRubricaWSA', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
            }
        } else {
//se invece ci sono più record
            praRic::praRubricaWS($ritorno['RetValue'], $this->nameForm, 'returnRubricaWS' . $tipo);
        }

        /**
         * fine gestione del risultato della ricerca
         */
    }


    public function protocollaArrivo() {
        $propas_rec = $this->praLib->GetPropas($_POST[$this->nameForm . '_PROPAS']['ROWID'], 'rowid');
//
// Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
//
        if (!$propas_rec) {
            $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }
        $pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
        $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
        $elementi['tipo'] = 'A';

        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(5);
        $praLibVar->setCodicePratica($this->currGesnum);
        $praLibVar->setChiavePasso($this->keyPasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggetto = $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
        $elementi['tipo'] = 'A';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DenomComune'] = $denomComune;
        $elementi['dati']['DataArrivo'] = $pracomA_rec['COMDAT'];
        $elementi['dati']['ChiavePasso'] = $this->keyPasso;
        $elementi['dati']['MittDest']['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['MittDest']['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['MittDest']['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['MittDest']['Provincia'] = $pracomA_rec['COMPRO'];
        if ($pracomP_rec['COMPRT']) {
            $elementi['dati']['NumeroAntecedente'] = substr($pracomP_rec['COMPRT'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($pracomP_rec['COMPRT'], 0, 4);
            if ($pracomP_rec['COMMETA']) {
                $metaDati = unserialize($pracomP_rec['COMMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "P"; //Tipo protocollo passo partenza 
        } else {
            $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
            if ($proges_rec['GESMETA']) {
                $metaDati = unserialize($proges_rec['GESMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        }
        $elementi['dati']['MittDest']['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['MittDest']['CF'] = $pracomA_rec['COMFIS'];

        $elementi['dati']['destinatari'] = array();
        $elementi['dati']['destinatari'][0]['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['destinatari'][0]['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['destinatari'][0]['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['destinatari'][0]['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['destinatari'][0]['Provincia'] = $pracomA_rec['COMPRO'];
        $elementi['dati']['destinatari'][0]['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['destinatari'][0]['CF'] = $pracomA_rec['COMFIS'];
//
        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        $elementi['destinatari'][0] = $destinatario;
        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoArr($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
//
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
//
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    public function inserisciRubricaWS($tipo = 'P') {
        $model = 'proHWS.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proHWS = new proHWS();
        if ($tipo == 'P') {
            $elementi = $this->protocollaPartenza();
        } else {
            $elementi = $this->protocollaArrivo();
        }
        $dati = array();
        if (isset($elementi['dati']['MittDest']['Denominazione'])) {
            $dati['ragioneSociale'] = $elementi['dati']['MittDest']['Denominazione'];
        }
        if (isset($elementi['dati']['MittDest']['CF'])) {
            $dati['codiceFiscale'] = $elementi['dati']['MittDest']['CF'];
        }
        if (isset($elementi['dati']['MittDest']['Indirizzo'])) {
            $dati['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
        }
        if (isset($elementi['dati']['MittDest']['CAP'])) {
            $dati['cap'] = $elementi['dati']['MittDest']['CAP'];
        }
        if (isset($elementi['dati']['MittDest']['Citta'])) {
            $dati['citta'] = $elementi['dati']['MittDest']['Citta'];
        }
        if (isset($elementi['dati']['MittDest']['Provincia'])) {
            $dati['prov'] = $elementi['dati']['MittDest']['Provincia'];
        }
        if (isset($elementi['dati']['MittDest']['Email'])) {
            $dati['email'] = $elementi['dati']['MittDest']['Email'];
        }
        $ritorno = $proHWS->salvaVoceRubrica($dati);
        /**
         * gestione del risultato dell'inserimento
         */
        if ($ritorno['Status'] != '0') {
            Out::msgStop("Errore", $ritorno['Message']);
            return false;
        }
//se non ci sono record trovati procedo con l'inserimento in anagrafica
        if ($ritorno['Status'] == '0') {
            $this->idCorrispondente = $ritorno['RetValue'][0]['codice'];
            if ($tipo == 'P') {
                $this->ProtocolloICCSP();
            } else {
                $this->ProtocolloICCSA();
            }
        } else {
            
        }
        /**
         * fine gestione dell'inserimento in rubrica
         */
        return;
    }

    
}
