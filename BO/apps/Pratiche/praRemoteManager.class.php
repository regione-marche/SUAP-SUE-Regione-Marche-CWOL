<?php

/**
 *
 * TEST PROTOCOLLAZIONE DIFFERITA
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    10.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsFrontOffice.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
include_once(ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praRemoteManager() {
    $praRemoteManager = new praRemoteManager();
    $praRemoteManager->parseEvent();
    return;
}

class praRemoteManager extends itaModel {

    public $praLib;
    public $token;
    public $wsClient;
    public $logger;
    public $domain;
    public $wsUser;
    public $wsPassword;

    public function getDomain() {
        return $this->domain;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getWsUser() {
        return $this->wsUser;
    }

    public function getWsPassword() {
        return $this->wsPassword;
    }

    public function setWsUser($wsUser) {
        $this->wsUser = $wsUser;
    }

    public function setWsPassword($wsPassword) {
        $this->wsPassword = $wsPassword;
    }

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->wsClient = new praWsFrontOffice();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    function setLogger($logger) {
        $this->logger = $logger;
    }

    /**
     * 
     * @return type
     */
    function getElencoRichiesteAttesaProtocollazione() {
        return $this->getElencoRichieste();
    }

    /**
     * 
     * @return boolean
     */
    function getElencoRichieste($statoAcquisizioneBo = "TUTTE", $statoRichieste = "ATTESA_PROTOCOLLAZIONE") {

        /*
         * Inizializzo array di ritorno
         */
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Ricerca richieste effettuata con successo";
        $ritorno["RetValue"] = true;

        /*
         * Istanzio il WS e prendo il Token
         */
        $retToken = $this->getToken();
        if ($retToken['Status'] == "-1") {
            return $retToken;
        }
        $token = $retToken['Token'];
        /*
         * XMLINFO
         */
        $params = Array();
        $params['itaEngineContextToken'] = $token;
        $params['domainCode'] = $this->domain;
        $params['statoAcquisizioneBO'] = $statoAcquisizioneBo;
        $params['statoRichieste'] = $statoRichieste;
        $wsCall = $this->wsClient->ws_ctrRichieste($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault in caso di ricerca richieste: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error in caso di ricerca richieste: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }
        $xmlResult = base64_decode($this->wsClient->getResult());
        $this->destroyToken($token);

        /*
         * Elaboro Xml d'usicta
         */

        if (!$xmlResult) {
            $ritorno['Richieste'] = array();
            $ritorno["Message"] = "Nessuna richiesta estratta";
            $ritorno["RetValue"] = true;
            return $ritorno;
        }

        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($xmlResult));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $Richieste_tab = array();
        foreach ($arrayXml['RECORD'] as $key => $proric_rec) {
            foreach ($proric_rec as $campo => $value) {
                $Richieste_tab[$key][$campo] = $value[0]['@textNode'];
            }
        }
        $ritorno['Richieste'] = $Richieste_tab;
        $ritorno['Fascicola'] = $arrayXml['FASCICOLA'][0]['@textNode'];
        return $ritorno;
    }

    function getArrayDati($richiesta) {
        /*
         * Inizializzo array di ritorno
         */
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Dati richiesta letti correttamente";
        $ritorno["RetValue"] = true;

        /*
         * Eseguo dei controlli sulla richiesta on-line
         */
        $retChk = $this->checkRichiesta($richiesta);

        $ritorno['Acquisizione'] = $retChk['Acquisizione'];
        $ritorno['Protocollazione'] = $retChk['Protocollazione'];
        $ritorno['GESNUM'] = $retChk['GESNUM'];
        $ritorno['PROPAK'] = $retChk['PROPAK'];

        /*
         * Se il fascicolo padre non è stato acquisito, lo acquisisco
         */
        if (!$retChk['AcquisizionePadre']) {
            $ritornoAcqPadre = $this->acquisizioneFascicoloPadre($retChk["RICRPA"]);
            if ($ritornoAcqPadre['Status'] == "-1") {
                return $ritornoAcqPadre;
            }
        }

        /*
         * Se la richiesta non risulta acquisita, eseguo le normali operazioni
         */
        if (!$retChk['Acquisizione']) {
            $ritorno = $this->getDatiRichiesta($richiesta);
        }
        return $ritorno;
    }

    function acquisizioneRichiesta($parm_aggiungi) {
        /*
         * Istanzio array di ritorno
         */
        $ret = array();
        $ret["Status"] = "0";
        $ret["Message"] = "Richiesta " . $parm_aggiungi['PRORIC_REC']['RICNUM'] . " acquisita correttamente.";
        $ret["RetValue"] = true;

        /*
         * Se è una richiesta on-line e se è un'integrazione, vedo se c'è il dato aggiuntivo della variante
         */
        $variante = false;
        if ($parm_aggiungi['PRORIC_REC']['RICPC'] == "1") {
            $variante = true;
        }

        /*
         * Acquisizione richiesta
         */
        $msgErr = "";
        if ($parm_aggiungi['PRORIC_REC']['RICRPA'] && !$variante) {
            $index = count($parm_aggiungi['ALLEGATI']);
            $xmlInfo = array(
                'rowid' => $index,
                'DATAFILE' => $parm_aggiungi['XMLINFO'],
                'FILENAME' => 'XMLINFO.xml',
                'FILEINFO' => 'XMLINFO.xml',
            );
            $parm_aggiungi['ALLEGATI'][$index] = $xmlInfo;
            $ret_aggiungi = $this->praLib->CaricaPassoIntegrazione($parm_aggiungi['PRORIC_REC'], $parm_aggiungi['ALLEGATI']);
            if (!$ret_aggiungi) {
                $msgErr = $this->praLib->getErrMessage();
            }
            $ret['PROPAK'] = $ret_aggiungi['PROPAK'];
            $ret['GESNUM'] = $ret_aggiungi['PRONUM'];
            $msgAggiungi = "Errore inserimento integrazione.";
        } elseif ($parm_aggiungi['PRORIC_REC']['PROPAK']) {
            $ret_aggiungi = $this->praLib->CaricaPassoIntegrazione($parm_aggiungi['PRORIC_REC'], $parm_aggiungi['ALLEGATI'], "", "", "", true);
            if (!$ret_aggiungi) {
                $msgErr = $this->praLib->getErrMessage();
            }
            $ret['PROPAK'] = $ret_aggiungi['PROPAK'];
            $ret['GESNUM'] = $ret_aggiungi['PRONUM'];
            $msgAggiungi = "Errore inserimento passo parere.";
        } else {
            /* @var $praLibPratica praLibPratica */
            $praLibPratica = praLibPratica::getInstance();
            $ret_aggiungi = $praLibPratica->aggiungi($this, $parm_aggiungi);
            if (!$ret_aggiungi) {
                $msgErr = $praLibPratica->getErrMessage();
            }
            $ret['GESNUM'] = $ret_aggiungi['GESNUM'];
            $msgAggiungi = "Errore nell'aggiungere la richiesta.";
        }



        if ($msgErr) {
            $ret["Status"] = "-1";
            $ret["Message"] = $msgAggiungi . " " . $parm_aggiungi['PRORIC_REC']['RICNUM'] . ": $msgErr";
            $ret["RetValue"] = false;
        }
        return $ret;
    }

    function protocollazionePasso($propak, $effettuata, $fascicola = "Si") {
        /*
         * Inizializzo array di ritorno
         */
        $retPrt = array();
        $retPrt["Status"] = "0";
        $retPrt["Message"] = "Passo $propak già protocollato";
        $retPrt["RetValue"] = true;

        /*
         * Leggo propas_rec
         */
        $propas_rec = $this->praLib->GetPropas($propak);

        /*
         * Rileggo pracom_rec e faccio l'unserialize dei metadati
         */
        $pracom_recA = $this->praLib->GetPracomA($propak);
        $retPrt['RetValue'] = unserialize($pracom_recA['COMMETA']);
        if ($effettuata == false) {
            /*
             * Get Array elementi
             */
            $praFascicolo = new praFascicolo($pracom_recA['COMNUM']);
            $praFascicolo->setChiavePasso($propak);
            $retElementi = $praFascicolo->getElementiProtocollazionePasso();
            if ($retElementi['Status'] == "-1") {
                return $retElementi;
            }
            $elementi = $retElementi['Elementi'];

            /*
             * Inserisco paramentro Fascicola in elementi
             */
            $elementi['Fascicola'] = $fascicola;

            /*
             * Protocollazione.
             * Se il settaggio errore di prot è andato a buon fine, torno l'errore di protocollazione, altrimenti torno l'errore del settaggio.
             */
            $retPrt = proWsClientHelper::lanciaProtocollazioneWS($elementi);
            if ($retPrt['Status'] == "-1") {
                $retErrPrt = $this->SetErroreProtocollazione($pracom_recA['COMNUM'], $retPrt);
                if ($retErrPrt['Status'] == "0") {
                    return $retPrt;
                } else {
                    return $retErrPrt;
                }
            }

            /*
             * Aggiorno i dati di protocollazione
             */
            $retUpd = $praFascicolo->updateDatiProtPracom($retPrt, $elementi['dati']['arrayDoc']);
            if ($retUpd['Status'] == "-1") {
                return $retUpd;
            }

            /*
             * Se Attiva, lancio la fascicolazione
             */
            $Filent_Rec = $this->praLib->GetFilent(29);
            if ($Filent_Rec['FILVAL'] == 1) {
                $ret = proWsClientHelper::lanciaFascicolazioneWS($elementi);
                if ($ret['Status'] == "-1") {
                    return $ret;
                }
            }

            /*
             * Alla fine di tutto setto il messaggio di avvenuta protocollazione
             */
            $retPrt["Message"] = "protocollazione avvenuta con successo passo con chiave n. $propak";
        }

        /*
         * Marcatura Richiesta
         */
        $retMarcatura = $this->MarcaturaRichiesta($propas_rec['PRORIN'], $retPrt);
        if ($retMarcatura['Status'] == "-1") {
            return $retMarcatura;
        }

        /*
         * cancello la cartella temporanea della richiesta on-line.
         */
        $retDel = itaLib::deleteAppsTempPath($propas_rec['PRORIN']);
        if (!$retDel) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore nel cancellare la cartella temporanea della richiesta n. " . $propas_rec['PRORIN'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        return $retPrt;
    }

    function protocollazionePratica($gesnum, $effettuata, $fascicola = "Si") {
        /*
         * Inizializzo array di ritorno
         */
        $retPrt = array();
        $retPrt["Status"] = "0";
        $retPrt["Message"] = "Pratica $gesnum già protocollata";
        $retPrt["RetValue"] = true;

        /*
         * Rileggo proges_rec e faccio l'unserialize dei metadati
         */
        $proges_rec = $this->praLib->GetProges($gesnum);
        $retPrt['RetValue'] = unserialize($proges_rec['GESMETA']);
        if ($effettuata == false) {

            /*
             * Get Array elementi
             */
            $praFascicolo = new praFascicolo($gesnum);
            $retElementi = $praFascicolo->getElementiProtocollazionePratica();
            if ($retElementi['Status'] == "-1") {
                return $retElementi;
            }
            $elementi = $retElementi['Elementi'];

            /*
             * Inserisco paramentro Fascicola in elementi
             */
            $elementi['Fascicola'] = $fascicola;

            /*
             * Protocollazione.
             * Se il settaggio errore di prot è andato a buon fine, torno l'errore di protocollazione, altrimenti torno l'errore del settaggio.
             */
            $retPrt = proWsClientHelper::lanciaProtocollazioneWS($elementi);
            if ($retPrt['Status'] == "-1") {
                $retErrPrt = $this->SetErroreProtocollazione($gesnum, $retPrt);
                if ($retErrPrt['Status'] == "0") {
                    return $retPrt;
                } else {
                    return $retErrPrt;
                }
            }

            /*
             * Aggiorno i dati di protocollazione
             */
            $retUpd = $praFascicolo->updateDatiProtProges($retPrt, $elementi['dati']['arrayDoc']);
            if ($retUpd['Status'] == "-1") {
                return $retUpd;
            }

            /*
             * Se Attiva, lancio la fascicolazione
             */
            $Filent_Rec = $this->praLib->GetFilent(29);
            if ($Filent_Rec['FILVAL'] == 1) {
                $ret = proWsClientHelper::lanciaFascicolazioneWS($elementi);
                if ($ret['Status'] == "-1") {
                    return $ret;
                }
            }

            /*
             * Alla fine di tutto setto il messaggio di avvenuta protocollazione
             */
            $retPrt["Message"] = "protocollazione avvenuta con successo pratica n. $gesnum";
        }

        /*
         * Marcatura Richiesta
         */
        $retMarcatura = $this->MarcaturaRichiesta($proges_rec['GESPRA'], $retPrt);
        if ($retMarcatura['Status'] == "-1") {
            return $retMarcatura;
        }

        /*
         * cancello la cartella temporanea della richiesta on-line.
         */
        $retDel = itaLib::deleteAppsTempPath($proges_rec['GESPRA']);
        if (!$retDel) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore nel cancellare la cartella temporanea della richiesta n. " . $proges_rec['GESPRA'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }


        return $retPrt;
    }

    function MarcaturaRichiesta($richiesta, $retPrt) {
        /*
         * IStanzio array di ritono
         */
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Marcatura richiesta $richiesta eseguita";
        $ritorno["RetValue"] = true;

        /*
         * Istanzio il WS e prendo il Token
         */
        $retToken = $this->getToken();
        if ($retToken['Status'] == "-1") {
            return $retToken;
        }
        $token = $retToken['Token'];

        /*
         * Marcatura Richiesta
         */
        $dataProtocollo = str_replace('-', '', $retPrt['RetValue']['DatiProtocollazione']['Data']['value']);
        $params = Array();
        $params['itaEngineContextToken'] = $token;
        $params['domainCode'] = $this->domain;
        $params['numeroRichiesta'] = substr($richiesta, 4);
        $params['annoRichiesta'] = substr($richiesta, 0, 4);
        $params['numeroProtocollo'] = $retPrt['RetValue']['DatiProtocollazione']['proNum']['value'];
        $params['dataProtocollo'] = $dataProtocollo;
        $params['metadatiProtocollazione'] = serialize($retPrt['RetValue']);
        $wsCall = $this->wsClient->ws_setMarcaturaRichiesta($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel settare la marcatura richiesta n. $richiesta - " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore nel settare la marcatura richiesta n. $richiesta - " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }

        $risultato = $this->wsClient->getResult();
        if (!$risultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Marcatura richiesta n. $richiesta fallita";
            $ritorno["RetValue"] = false;
        }
        $this->destroyToken($token);
        return $ritorno;
    }

    function SetErroreProtocollazione($gesnum, $retPrt) {
        /*
         * Rileggo PROGES_REC
         */
        $proges_rec = $this->praLib->GetProges($gesnum);

        /*
         * IStanzio array di ritono
         */
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "settato errore protocollazione richiesta " . $proges_rec['GESPRA'];
        $ritorno["RetValue"] = true;

        /*
         * Istanzio il WS e prendo il Token
         */
        $retToken = $this->getToken();
        if ($retToken['Status'] == "-1") {
            return $retToken;
        }
        $token = $retToken['Token'];

        /*
         * Set errore protocollazione Richiesta
         */
        $params = Array();
        $params['itaEngineContextToken'] = $token;
        $params['domainCode'] = $this->domain;
        $params['numeroRichiesta'] = substr($proges_rec['GESPRA'], 4);
        $params['annoRichiesta'] = substr($proges_rec['GESPRA'], 0, 4);
        $params['erroreProtocollazione'] = strip_tags($retPrt['Message']);
        $wsCall = $this->wsClient->ws_setErroreProtocollazione($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel settare l'errore di protocollazione richiesta n. " . $proges_rec['GESPRA'] . "-" . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore nel settare l'errore di protocollazione richiesta n. " . $proges_rec['GESPRA'] . "-" . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }
        $risultato = $this->wsClient->getResult();
        if (!$risultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Settaggio errore di protocollazione richiesta n. " . $proges_rec['GESPRA'] . " fallito";
            $ritorno["RetValue"] = false;
        }
        $this->destroyToken($token);
        return $ritorno;
    }

    /**
     * 
     * @param array $config
     * 
     */
    public function setClientConfig($config = null) {
        /* @var $WSClient praWsFrontOffice */
        $this->wsClient->setWebservices_uri($config['wsEndpoint']);
        $this->wsClient->setWebservices_wsdl($config['wsWsdl']);
        $this->wsClient->setNamespace($config['wsNamespace']);
        $this->wsClient->setTimeout(1200);
    }

    private function getToken() {
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Token valido";
        $ritorno["RetValue"] = true;
        $ritorno["Token"] = '';

        $params = array(
            'userName' => $this->wsUser,
            'userPassword' => $this->wsPassword,
            'domainCode' => $this->domain
        );

        $wsCall = $this->wsClient->ws_GetItaEngineContextToken($params);

        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault in fase di prenotazione del token: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore in fase di prenotazione del token: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $ritorno['Token'] = $this->wsClient->getResult();
        return $ritorno;
    }

    public function destroyToken($token) {
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Token distrutto corretamente";
        $ritorno["RetValue"] = true;
        //
        if (!$token) {
            return $ritorno;
        }
        $params = array(
            'token' => $token,
            'domainCode' => $this->domain
        );
        $wsCall = $this->wsClient->ws_DestroyItaEngineContextToken($params);

        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault in fase di distruzione del token: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore in fase di distruzione del token: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        return $ritorno;
    }

    function GetProric($richiesta, $token) {

        /*
         * Istanzio il WS e prendo il Token se è vuoto
         */
        if ($token == "") {
            $retToken = $this->getToken();
            if ($retToken['Status'] == "-1") {
                return $retToken;
            }
            $token = $retToken['Token'];
        }

        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Record Richiesta: $richiesta letto con successo";
        $ritorno["RetValue"] = true;


        $params = array();
        $params['itaEngineContextToken'] = $token;
        $params['domainCode'] = $this->domain;
        $params['chiave'] = "RICNUM";
        $params['valore'] = $richiesta;
        $wsCall = $this->wsClient->ws_getProric($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel leggere Proric_rec della richiesta $richiesta: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error nel leggere Proric_rec della richiesta $richiesta: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $proric_rec = json_decode(base64_decode($this->wsClient->getResult()), true);
        $ritorno['PRORIC_REC'] = itaLib::utf8_decode_recursive($proric_rec);
        return $ritorno;
    }

    function checkRichiesta($richiesta) {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Check Richiesta: $richiesta eseguito con successo";
        $ritorno["RetValue"] = true;
        $ritorno["Acquisizione"] = true;
        $ritorno["AcquisizionePadre"] = true;
        $ritorno["Protocollazione"] = true;
        $ritorno["GESNUM"] = "";
        $ritorno["PROPAK"] = "";
        $ritorno["RICRPA"] = "";

        //$this->setClientConfig();
        $retToken = $this->getToken();
        if ($retToken['Status'] == "-1") {
            return $retToken;
        }
        $token = $retToken['Token'];
        $retProric = $this->GetProric($richiesta, $token);
        if ($retProric['Status'] == '-1') {
            $this->destroyToken($token);
            return $retProric;
        }

        $this->destroyToken($token);

        $proric_rec = $retProric['PRORIC_REC'];

        if ($proric_rec['RICRPA'] || $proric_rec['PROPAK']) {

            /*
             * Controllo se esiste il fascicolo principale
             */
            if ($proric_rec['RICRPA']) {
                $CtrPadre_Proges_rec = $this->praLib->GetProges($proric_rec['RICRPA'], 'richiesta');
            } elseif ($proric_rec['PROPAK']) {
                $CtrPadre_Proges_rec = $this->praLib->GetProges(substr($proric_rec['PROPAK'], 0, 10));
            }
            if (!$CtrPadre_Proges_rec) {
                $ritorno["AcquisizionePadre"] = false;
                $ritorno["RICRPA"] = $proric_rec['RICRPA'];
            } else {
                $ritorno["GESNUM"] = $CtrPadre_Proges_rec['GESNUM'];
            }

            /*
             * Controllo se esiste già il passo
             */
            $CtrProrin_Propas_rec = $this->praLib->GetPropas($richiesta, "prorin");
            if (!$CtrProrin_Propas_rec) {
                $ritorno["Acquisizione"] = false;
                $ritorno["Protocollazione"] = false;
                return $ritorno;
            } else {
                $ritorno["GESNUM"] = $CtrProrin_Propas_rec['PRONUM'];
                $ritorno["PROPAK"] = $CtrProrin_Propas_rec['PROPAK'];
            }
            $pracom_recA = $this->praLib->GetPracomA($CtrProrin_Propas_rec['PROPAK']);
            if (!$pracom_recA) {
                $ritorno["Protocollazione"] = false;
            } else {
                if ($pracom_recA['COMPRT'] == 0) {
                    $ritorno["Protocollazione"] = false;
                }
            }
        } else {
            /*
             * Se il proges_rec non c'è, vuol dire che la richiesta non è stata acquista
             * quindi ritorno ed eseguo la normale procedura
             */
            $CtrGespra_Proges_rec = $this->praLib->GetProges($richiesta, "richiesta");
            if (!$CtrGespra_Proges_rec) {
                $ritorno["Acquisizione"] = false;
                $ritorno["Protocollazione"] = false;
                return $ritorno;
            }

            $ritorno["GESNUM"] = $CtrGespra_Proges_rec['GESNUM'];
            if ($CtrGespra_Proges_rec && $CtrGespra_Proges_rec['SERIECODICE'] == 9999) {
                $ritorno["Acquisizione"] = false;
                /*
                 * Rimando la mail d'errore
                 */
                $ret = array();
                $ret['Status'] = "-1";
                $ret['Message'] = "Verificare la richiesta n. $richiesta che risulta ancora avere progressivo n. 9999";
                $subject = $this->getSubject($richiesta);
                $body = $this->getBody($ret, $richiesta);
                $retSend = $this->sendErrorMail($body, $subject);
                if ($retSend['Status'] == "-1") {
                    $ritorno["Message"] .= "<br>" . $retSend["Message"];
                }
            } elseif ($CtrGespra_Proges_rec && $CtrGespra_Proges_rec['SERIECODICE'] != 9999 && $CtrGespra_Proges_rec['GESNPR'] == 0) {
                $ritorno["Protocollazione"] = false;
            }
        }
        return $ritorno;
    }

    function getDatiRichiesta($richiesta) {
        /*
         * Creo la cartella temporanea di lavoro
         */
        $tempPath = itaLib::createAppsTempPath($richiesta);
        if (!is_dir($tempPath)) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Creazione Directory di lavoro temporanea della richiesta $richiesta fallita.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }


        /*
         * Istanzio il WS e prendo il Token
         */
        //$this->setClientConfig();
        $retToken = $this->getToken();
        if ($retToken['Status'] == "-1") {
            return $retToken;
        }

        $token = $retToken['Token'];
        $this->debug("Token:$token");

        /*
         * Download XMLINFO
         */
        $params = Array();
        $params['itaEngineContextToken'] = $token;
        $params['domainCode'] = $this->domain;
        $params['richiesta'] = $richiesta;
        $wsCall = $this->wsClient->ws_getXmlInfo($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel prendere il file XMLINFO della richiesta $richiesta: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore nel prendere il file XMLINFO della richiesta $richiesta: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }
        $base64XmlInfo = $this->wsClient->getResult();
        //
        $contentFile = base64_decode($base64XmlInfo);
        $fileXML = $tempPath . "/XMLINFO.xml";
        file_put_contents($fileXML, $contentFile);

        /*
         * Download XMINFO delle richieste accorpate
         */
        $wsCall = $this->wsClient->ws_getXmlInfoAccorpate($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel prendere il file XMLINFO accorpate della richiesta $richiesta: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error nel prendere il file XMLINFO accorpate della richiesta $richiesta: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }
        $arrBase64XmlInfo = json_decode(base64_decode($this->wsClient->getResult()), true);
        //
        foreach ($arrBase64XmlInfo as $base64) {
            $contentFile = base64_decode($base64['Stream']);
            file_put_contents($tempPath . "/" . "XMLINFO_" . $base64['Richiesta'] . ".xml", $contentFile);
        }



        /*
         * Get PRORIC_REC
         */
        $retProric = $this->GetProric($richiesta, $token);
        if ($retProric['Status'] == "-1") {
            $this->destroyToken($token);
            return $retProric;
        }

        $Proric_rec = $retProric['PRORIC_REC'];

        /*
         * Get RICDOC_TAB
         */
        $params['chiave'] = "DOCNUM";
        $params['valore'] = $richiesta;
        $wsCall = $this->wsClient->ws_getRicdoc($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nel leggere Ricdoc_tab della richiesta $richiesta: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error nel leggere Ricdoc_tab della richiesta $richiesta " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
            }
            $this->destroyToken($token);
            return $ritorno;
        }
        $Ricdoc_tab = json_decode(base64_decode($this->wsClient->getResult()), true);
        $Ricdoc_tab = itaLib::utf8_decode_recursive($Ricdoc_tab);

        /*
         * Get RICDOC_TAB richieste accorpate
         */
        $AllegatiAcc = array();
        if ($arrBase64XmlInfo) {
            $AllegatiAcc = $this->GetAllegatiRichiesteAccorpate($arrBase64XmlInfo, $token, $tempPath);
        }

        /*
         * Download degli allegati della richiesta on-line
         */
        $Allegati = array();
        foreach ($Ricdoc_tab as $key => $Ricdoc_rec) {
            $params['rowid'] = $Ricdoc_rec['ROWID'];
            $wsCall = $this->wsClient->ws_getRichiestaAllegatoForRowid($params);
            if ($this->wsClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault nello scaricare l'allegato  " . $Ricdoc_rec['DOCNAME'] . " della richiesta $richiesta: " . $this->wsClient->getFault();
                $ritorno["RetValue"] = false;
                $this->destroyToken($token);
                return $ritorno;
            } elseif ($this->wsClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error nello scaricare l'allegato  " . $Ricdoc_rec['DOCNAME'] . " della richiesta $richiesta: " . $this->wsClient->getError();
                $ritorno["RetValue"] = false;
                $this->destroyToken($token);
                return $ritorno;
            }
            $base64 = $this->wsClient->getResult();
            //
            $contentFile = base64_decode($base64);
            file_put_contents($tempPath . "/" . $Ricdoc_rec['DOCNAME'], $contentFile);
            //
            $Allegati[$key]['rowid'] = $key;
            $Allegati[$key]['DATAFILE'] = $tempPath . "/" . $Ricdoc_rec['DOCNAME'];
            $Allegati[$key]['FILENAME'] = $Ricdoc_rec['DOCNAME'];
            $Allegati[$key]['FILEINFO'] = $Ricdoc_rec['DOCNAME'];
        }
        /*
         * 
         * Chiamate ws concluse distruggo il token  
         * 
         */
        $this->destroyToken($token);

        /*
         * Popolo PROGES_REC
         */
        $proges_rec = array();
        $proges_rec['GESDRE'] = date('Ymd');
        $proges_rec['GESPRO'] = str_pad($Proric_rec['RICPRO'], 6, "0", STR_PAD_LEFT);
        //$proges_rec['GESGIO'] = $anapra_rec['PRAGIO'];
        $proges_rec['GESDRI'] = $Proric_rec['RICDAT'];
        $proges_rec['GESORA'] = $Proric_rec['RICTIM'];
        $proges_rec['GESRES'] = str_pad($Proric_rec['RICRES'], 6, "0", STR_PAD_LEFT);
        $proges_rec['GESPRA'] = $Proric_rec['RICNUM'];
        $proges_rec['GESTSP'] = $Proric_rec['RICTSP'];
        $proges_rec['GESSPA'] = $Proric_rec['RICSPA'];
        $proges_rec['GESEVE'] = $Proric_rec['RICEVE'];
        $proges_rec['GESSEG'] = $Proric_rec['RICSEG'];

        /*
         * Popolo ANADES_REC
         */
        $anades_rec = array();
        $anades_rec['DESNOM'] = $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM'];
        $anades_rec['DESFIS'] = $Proric_rec['RICFIS'];
        $anades_rec['DESIND'] = $Proric_rec['RICVIA'];
        $anades_rec['DESCAP'] = $Proric_rec['RICCAP'];
        $anades_rec['DESCIT'] = $Proric_rec['RICCOM'];
        $anades_rec['DESPRO'] = $Proric_rec['RICPRV'];
        $anades_rec['DESEMA'] = $Proric_rec['RICEMA'];
        $anades_rec['DESRUO'] = "0001";

        /*
         * Leggo il parametro del progressivo
         *
         * Sempre false per altri FO dato deprecato utile solo per SUE Pesaro
         *
         *
         */
        $Filent_Rec = $this->praLib->GetFilent(1);
        $ProgressivoDaRichiesta = false;
        if ($Filent_Rec['FILVAL'] == 1) {
            $ProgressivoDaRichiesta = true;
        }


        $datiRichiesta = array(
            "PROGES_REC" => $proges_rec,
            "ANADES_REC" => $anades_rec,
            "PRORIC_REC" => $Proric_rec, // TODO: ANALIZZARE COME VALORIZZARE
            "XMLINFO" => $tempPath . "/XMLINFO.xml", // XMLINFO.xml
            "ALLEGATI" => $Allegati,
            "ALLEGATIACCORPATE" => $AllegatiAcc,
            "ALLEGATICOMUNICA" => array(), //
            "esterna" => false,
            "tipoInserimento" => "PECSUAP",
            "starweb" => false,
            "EscludiPassiFO" => false,
            "ProgressivoDaRichiesta" => $ProgressivoDaRichiesta
        );

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Dati richiesta $richiesta letti con successo";
        $ritorno["RetValue"] = true;
        $ritorno["DatiRichiesta"] = $datiRichiesta;
        return $ritorno;
    }

    public function notificaProtocollazioneRichiesta($gesnum) {
        /*
         * Reset del ritorno
         */
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Notifica di Avvenuta protocollazione effettuata con successo";
        $ritorno["RetValue"] = true;

        /*
         * Accesso ai dati pratica acquisita
         */
        $Proges_rec = $this->praLib->GetProges($gesnum);
        $msgPrefix = "Notifica protocollazione richiesta on-line N. {$Proges_rec['GESPRA']}:";
        if (!$Proges_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix Impossibile accedere alla pratica acquisita  n. " . $gesnum;
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Invio mail con itaMailer
         */
        include_once(ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php');

        /*
         * Istanza mail box per l'invio da Fascicoli elettronici
         */
        /* @var $emlMailBox emlMailBox */
        $emlMailBox = $this->praLib->getEmlMailBox();
        if (!$emlMailBox) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix Impossibile accedere alle funzioni dell'account di invio.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Preparo messaggio in uscita
         */
        /* @var $outgoingMessage emlOutgoingMessage */
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix Impossibile creare un nuovo messaggio in uscita.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Leggo il destinatario
         */
        include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
        $Anades_rec = $this->praLib->GetAnades($gesnum, "ruolo", false, praRuolo::getSystemSubjectCode('ESIBENTE'));
        if (!$Anades_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix Impossibile leggere i dati del destinatario della notifica (ESIBENTE)";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $destinatarioNotifica = ($Anades_rec['DESPEC']) ? $Anades_rec['DESPEC'] : $Anades_rec['DESEMA'];
        if (!$destinatarioNotifica) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix ESIBENTE senza mail definita.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /*
         * Leggo i l template per la mail  di notifica
         */
        $metaDati = proIntegrazioni::GetMetedatiProt($Proges_rec['GESNUM']);
        $oggetto = "Notifica protocollazione richiesta on-line n. " . substr($Proges_rec['GESPRA'], 4) . "/" . substr($Proges_rec['GESPRA'], 0, 4);
        $corpo = "Richiesta on-line n. " . substr($Proges_rec['GESPRA'], 4) . "/" . substr($Proges_rec['GESPRA'], 0, 4) . "<br> è stata ricevuta dall'ente con protocollo n. " . substr($Proges_rec['GESNPR'], 4) . " del " . $metaDati['Data'];
        $filent_rec_oggetto = $this->praLib->GetFilent(39);
        if ($filent_rec_oggetto['FILVAL']) {
            $oggetto = $filent_rec_oggetto['FILVAL'];
        }
        $filent_rec_corpo = $this->praLib->GetFilent(40);
        if ($filent_rec_corpo['FILVAL']) {
            $corpo = $filent_rec_corpo['FILVAL'];
        }

        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($gesnum);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggettoNotifica = $this->praLib->GetStringDecode($dictionaryValues, $oggetto);
        $corpoNotifica = $this->praLib->GetStringDecode($dictionaryValues, $corpo);
        //
        $outgoingMessage->setSubject($oggettoNotifica);
        $outgoingMessage->setBody($corpoNotifica);
        $outgoingMessage->setEmail($destinatarioNotifica);
        $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);
        if (!$mailArchivio_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "$msgPrefix Invio notifica di protocollazione fallito. " . $emlMailBox->getLastMessage();
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * 
         * QUI INSERIRE LA MARCATURA DELLA RICHIESTA COME NOTIFICATA.
         * 
         */

        $ritorno["Mail_Archivio"] = $mailArchivio_rec;
        return $ritorno;
    }

    /**
     * 
     * @return array
     */
    public function elaboraCoda() {
        /*
         * Reset Status
         * 
         */
        $retStatus = array(
            'Status' => true,
            'Message' => '',
            'detailedInfo' => array()
        );

        /*
         * Valorizzo l'oggetto per la mail d'errore
         */
        $utiEnte = new utiEnte();
        $parametriEnte_rec = $utiEnte->GetParametriEnte();
        $subject = "Acquisizione e protocollazione richieste on-line ente $this->domain - " . $parametriEnte_rec['DENOMINAZIONE'];

        /*
         * Lettura della coda remota
         */
        try {
            $elencoRichieste = $this->getElencoRichieste();
        } catch (Exception $e) {
            $retStatus = array(
                'Status' => false,
                'Message' => 'Eccezione in fase di download coda richieste: ' . $e->getMessage() . ".<br>",
            );
            $this->error($retStatus['Message']);
            $retSend = $this->sendErrorMail($retStatus['Message'], $subject);
            return $retStatus;
        }

        if ($elencoRichieste['Status'] == "-1") {
            $retStatus = array(
                'Status' => false,
                'Message' => 'Errore in fase di download coda richieste: ' . $elencoRichieste['Message'],
            );
            $this->error($retStatus['Message']);
            $retSend = $this->sendErrorMail($retStatus['Message'], $subject);
            return $retStatus;
        }

        if (!$elencoRichieste['Richieste']) {
            $retStatus = array(
                'Status' => true,
                'Message' => 'Non ci sono richieste on-line in attesa di protocollazione.'
            );
            return $retStatus;
        }

        $this->info("Download coda avvenuto con successo.");

        /*
         * Elaborazione della coda Remota scaricata
         */
        foreach ($elencoRichieste['Richieste'] as $Richieste_rec) {

            $retStatus['detailedInfo'][] = array(
                'Key' => $Richieste_rec['RICNUM'],
                'Status' => true,
                'Log' => array(),
            );
            $this->info("init status richiesta");
            $keylog = end(array_keys($retStatus['detailedInfo']));
            $this->setInfoLog($retStatus['detailedInfo'][$keylog], sprintf('Inizio elaborazione richiesta \'%s\' ok.', $Richieste_rec['RICNUM']));

            /*
             * Preparazione Array Dati il per processo di acquisizione
             */
            try {
                $arrayDati = $this->getArrayDati($Richieste_rec['RICNUM']);
            } catch (Exception $e) {
                $this->setErrorLog($retStatus['detailedInfo'][$keylog], 'Eccezione in fase di preparazione e controllo dati: ' . $e->getMessage());
                continue;
            }

            if ($arrayDati['Status'] == "-1") {
                $this->setErrorLog($retStatus['detailedInfo'][$keylog], 'Errore in fase di preparazione e controllo dati: ' . $arrayDati['Message']);
                continue;
            }

            $gesnum = $arrayDati['GESNUM'];
            $propak = $arrayDati['PROPAK'];
            $this->setInfoLog($retStatus['detailedInfo'][$keylog], sprintf('Get array richiesta \'%s\' ok.', $Richieste_rec['RICNUM']));
            if ($arrayDati['Acquisizione'] == false) {
                try {
                    $acquisizioneRichiesta = $this->acquisizioneRichiesta($arrayDati['DatiRichiesta']);
                } catch (Exception $e) {
                    $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Eccezione in fase di acquisizione richiesta: " . $e->getMessage());
                    continue;
                }

                if ($acquisizioneRichiesta['Status'] == "-1") {
                    $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Errore in fase di acquisizione richiesta: " . $acquisizioneRichiesta['Message']);
                    continue;
                }
                $gesnum = $acquisizioneRichiesta['GESNUM'];
                $propak = $acquisizioneRichiesta['PROPAK'];
                if ($propak) {
                    $this->setInfoLog($retStatus['detailedInfo'][$keylog][$keylog], sprintf('Acquisizione passo \'%s\' ok.', $acquisizioneRichiesta['PROPAK']));
                } else {
                    $this->setInfoLog($retStatus['detailedInfo'][$keylog][$keylog], sprintf('Acquisizione pratica \'%s\' ok.', $acquisizioneRichiesta['GESNUM']));
                }
            }

            if ($gesnum == "") {
                $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Numero Pratica non trovato");
                continue;
            }


            if ($propak) {
                try {
                    $protocollazioneRichiesta = $this->protocollazionePasso($propak, $arrayDati['Protocollazione'], $elencoRichieste['Fascicola']);
                } catch (Exception $e) {
                    $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Eccezione in fase di protocollazione passo $propak: " . $e->getMessage());
                    continue;
                }
                $msgProt = "Protocollazione passo $propak della pratica $gesnum ok.";
            } else {
                try {
                    $protocollazioneRichiesta = $this->protocollazionePratica($gesnum, $arrayDati['Protocollazione'], $elencoRichieste['Fascicola']);
                } catch (Exception $e) {
                    $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Eccezione in fase di protocollazione pratica $gesnum: " . $e->getMessage());
                    continue;
                }
                $msgProt = "Protocollazione pratica $gesnum ok.";
            }

            if ($protocollazioneRichiesta['Status'] == "-1") {
                $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Errore in fase di  protocollazione richiesta: " . $protocollazioneRichiesta['Message']);
                continue;
            }

            if ($retStatus['detailedInfo'][$keylog]['Status'] === true) {
                try {
                    $retNotifica = $this->notificaProtocollazioneRichiesta($gesnum);
                } catch (Exception $e) {
                    $this->setErrorLog($retStatus['detailedInfo'][$keylog], 'Eccezione in fase di notifica esibente ' . $e->getMessage());
                }
            }
            if ($retNotifica['Status'] == "-1") {
                $this->setErrorLog($retStatus['detailedInfo'][$keylog], "Errore in fase di notifica esibente:" . $retNotifica['Message']);
            }
            $this->setInfoLog($retStatus['detailedInfo'][$keylog], $msgProt);
        }

        $strErrBody = '';
        foreach ($retStatus['detailedInfo'] as $info) {
            foreach ($info['Log'] as $log) {
                if ($log['level'] == 'error') {
                    $strErrBody .= "Richiesta: {$info['Key']} - " . strip_tags($log['message']) . "\n";
                }
            }
        }
        if ($strErrBody) {
            $strErrBody = "<pre>$strErrBody</pre>";
            $retSend = $this->sendErrorMail($strErrBody, $subject);
        }
        $retStatus['Message'] = "Coda Elaborata.";
        return $retStatus;
    }

    private function setInfoLog(&$info, $message) {
        $info['Log'][] = array('message' => $message, 'level' => 'info');
        $this->info($message);
    }

    private function setErrorLog(&$info, $message) {
        $info['Log'][] = array('message' => $message, 'level' => 'error');
        $info['Status'] = false;
        $this->error($message);
    }

    private function info($msg) {
        if ($this->logger) {
            $this->logger->info($msg);
        }
    }

    private function debug($msg) {
        if ($this->logger) {
            $this->logger->debug($msg);
        }
    }

    private function error($msg) {
        if ($this->logger) {
            $this->logger->error($msg);
        }
    }

    function sendErrorMail($msg, $subject = null) {
        /*
         * Inizializzo array di ritorno
         */
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Mail d'errore inviata con successo";
        $ritorno["RetValue"] = true;

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $Account = $Destinatario = '';
        $devLib = new devLib();

        /*
         * Parametri di invio mail
         */
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        $ItaEngine_address_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ADDRESS', false);
        if ($ItaEngine_mail_rec['CONFIG'] && $ItaEngine_address_rec['CONFIG']) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
            $Destinatario = $ItaEngine_address_rec['CONFIG'];
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Parametri per invio mail d'errore mancanti";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Inizializzo l'oggetto mail
         */
        $emlMailBox = emlMailBox::getInstance($Account);
        if (!$emlMailBox) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore durante l'inizializzazione dell'oggetto mail per l'invio di errori";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Creoil nuovo messaggio in uscita
         */
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore invio mail d'errore: " . $emlMailBox->getLastMessage();
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        //$this->logger->info("Invia Mail a: {$ItaEngine_mail_rec['CONFIG']} Oggetto: $subject");
        $outgoingMessage->setSubject($subject);
        $outgoingMessage->setBody($msg);
        $outgoingMessage->setEmail($Destinatario);
        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
        if (!$mailSent) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore invio mail d'errore: " . $emlMailBox->getLastMessage();
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        return $ritorno;
    }

    function getSubject($richiesta) {
        $utiEnte = new utiEnte();
        $parametriEnte_rec = $utiEnte->GetParametriEnte();
        return "Segnalazione errore acquisizione ente " . $parametriEnte_rec['DENOMINAZIONE'] . "(" . App::$utente->getKey('ditta') . ") richiesta on-line remota n. $richiesta";
    }

    function getBody($ret, $richiesta) {
        $utiEnte = new utiEnte();
        $parametriEnte_rec = $utiEnte->GetParametriEnte();
        return "Ente: " . $parametriEnte_rec['DENOMINAZIONE'] . "<br>
                DB: $this->domain<br>
                In data " . date('d/m/Y') . " alle ore " . date("H:i:s") . " si è verificato il seguente errore per la richiesta n. $richiesta:<br>
                codice errore: " . $ret["Status"] . "<br>
                Messaggio    :<pre>" . $ret["Message"] . "</pre>";
    }

    function acquisizioneFascicoloPadre($richiesta) {
        $ritorno = $this->getDatiRichiesta($richiesta);
        if ($ritorno['Status'] == "-1") {
            return $ritorno;
        }
        return $this->acquisizioneRichiesta($ritorno["DatiRichiesta"]);
    }

    public function GetAllegatiRichiesteAccorpate($arrBase64XmlInfo, $token, $tempPath) {
        foreach ($arrBase64XmlInfo as $base64) {
            if ($base64['Richiesta']) {
                ///$params = Array();
                $params['itaEngineContextToken'] = $token;
                $params['domainCode'] = $this->domain;
                $params['chiave'] = "DOCNUM";
                $params['valore'] = $base64['Richiesta'];
                $wsCall = $this->wsClient->ws_getRicdoc($params);
                if (!$wsCall) {
                    if ($this->wsClient->getFault()) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Fault nel leggere Ricdoc_tab della richiesta " . $base64['Richiesta'] . ": " . $this->wsClient->getFault();
                        $ritorno["RetValue"] = false;
                    } elseif ($this->wsClient->getError()) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Error nel leggere Ricdoc_tab della richiesta " . $base64['Richiesta'] . ": " . $this->wsClient->getError();
                        $ritorno["RetValue"] = false;
                    }
                    $this->destroyToken($token);
                    return $ritorno;
                }
                $Ricdoc_tab = json_decode(base64_decode($this->wsClient->getResult()), true);
                //
                $Allegati = array();
                foreach ($Ricdoc_tab as $key => $Ricdoc_rec) {
                    $params['rowid'] = $Ricdoc_rec['ROWID'];
                    $wsCall = $this->wsClient->ws_getRichiestaAllegatoForRowid($params);
                    if ($this->wsClient->getFault()) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Fault nello scaricare l'allegato  " . $Ricdoc_rec['DOCNAME'] . " della richiesta " . $base64['Richiesta'] . ": " . $this->wsClient->getFault();
                        $ritorno["RetValue"] = false;
                        $this->destroyToken($token);
                        return $ritorno;
                    } elseif ($this->wsClient->getError()) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Error nello scaricare l'allegato  " . $Ricdoc_rec['DOCNAME'] . " della richiesta " . $base64['Richiesta'] . ": " . $this->wsClient->getError();
                        $ritorno["RetValue"] = false;
                        $this->destroyToken($token);
                        return $ritorno;
                    }
                    $base64 = $this->wsClient->getResult();
                    //
                    $contentFile = base64_decode($base64);
                    file_put_contents($tempPath . "/" . $Ricdoc_rec['DOCNAME'], $contentFile);
                    //
                    $Allegati[$Ricdoc_rec['DOCNUM']][$key]['rowid'] = $key;
                    $Allegati[$Ricdoc_rec['DOCNUM']][$key]['DATAFILE'] = $tempPath . "/" . $Ricdoc_rec['DOCNAME'];
                    $Allegati[$Ricdoc_rec['DOCNUM']][$key]['FILENAME'] = $Ricdoc_rec['DOCNAME'];
                    $Allegati[$Ricdoc_rec['DOCNUM']][$key]['FILEINFO'] = $Ricdoc_rec['DOCNAME'];
                }
            }
        }
        return $Allegati;
    }

}
