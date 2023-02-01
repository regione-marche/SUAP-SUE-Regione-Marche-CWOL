<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DATI A SITO FRONT OFFICE DI CART TOSCANA
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi / Michele Moscioni
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    03.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsFrontOffice.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRemoteManager.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaFileUtils.class.php');

class praFrontOfficeItalsoftWs extends praFrontOfficeManager {

    private $wsClient;

    /**
     *
     */
    public function scaricaPraticheNuove($istanza) {

        $this->retStatus = array(
            'Status' => true,
            'Lette' => 0,
            'Scaricate' => 0,
            'Errori' => 0,
            'Messages' => array()
        );

        /*
         * Istanzio i parametri del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);



        /*
         * Recupero le richieste on-line da scaricare
         */
        $praRemoteManager = itaModel::getInstance('praRemoteManager');
        $praRemoteManager = $this->getRemoteManager($istanza);
        $result = $praRemoteManager->getElencoRichieste("NON_ACQUISITE_BO", "01,91");
        if ($result['Status'] == "-1") {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $result['Message'];
            return false;
        }
        $richieste_tab_totali = $result["Richieste"];

        /*
         * Controllo le richieste da inserire
         */
        $richieste_tab = $this->controlloPratiche($richieste_tab_totali, $istanza);

        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if (!$tokenKey) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare un token valido." . $this->getErrMessage();
            return false;
        }

        /*
         * Recupero la ditta dal token
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare il codice ditta." . $this->getErrMessage();

            $this->destroyToken($tokenKey);
            return false;
        }

        /*
         * Chiamo scarica pratica e preparo l'array di PRAFOLIST
         */
        $this->retStatus['Lette'] = count($richieste_tab);
        foreach ($richieste_tab as $richieste_rec) {
            if (!$this->scaricaPratica($richieste_rec, $istanza, $tokenKey, $domainCode)) {
                $this->retStatus['Errori'] += 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = $this->getErrMessage();
            } else {
                $this->retStatus['Scaricate'] += 1;
            }
        }
        $this->destroyToken($tokenKey);
    }

    /**
     * 
     * @param type $richieste_tab
     * @param type $istanza
     * @return type
     */
    private function controlloPratiche($richieste_tab, $istanza) {
        $richieste_tab_da_inserire = array();

        /*
         * Prendo il codice istanza
         */
        list($classe, $codiceIStanza) = explode("_", $istanza);

        $sql = "SELECT * FROM PRAFOLIST WHERE FOTIPO = '" . praFrontOfficeManager::TYPE_FO_ITALSOFT_WS . "' AND FOPRAKEY LIKE '$codiceIStanza%'";
        $prafolist_tab = $this->praLib->GetGenericTab($sql);
        $array1 = array_column($richieste_tab, "RICNUM");
        $array2 = array_column($prafolist_tab, "FOIDPRATICA");
        $arrDiff = array_diff($array1, $array2);
        foreach ($arrDiff as $key => $idpratica) {
            $richieste_tab_da_inserire[] = $richieste_tab[$key];
        }
        return $richieste_tab_da_inserire;
    }

    /**
     * 
     * @param type $richieste_rec Record di PRORIC
     */
    public function scaricaPratica($richieste_rec, $istanza, $tokenKey, $domainCode) {

        /*
         * Creo la cartella temporanea di lavoro
         */
        $tempPath = itaLib::createAppsTempPath($richieste_rec['RICNUM']);
        if (!is_dir($tempPath)) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Creazione Directory di lavoro temporanea della richiesta " . $richieste_rec['RICNUM'] . " fallita.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Prendo il codice istanza
         */
        list($classe, $codiceIStanza) = explode("_", $istanza);

        $metadati = array();
        $metadati[$codiceIStanza] = array("ISTANZA" => $istanza, "DESCRIZIONE" => $this->getDescrizioneIstanza($istanza));

        /*
         * Get dati Richiesta
         */
        $params = Array();
        $params['itaEngineContextToken'] = $tokenKey;
        $params['domainCode'] = $domainCode;
        $params['numeroRichiesta'] = substr($richieste_rec['RICNUM'], 4);
        $params['annoRichiesta'] = substr($richieste_rec['RICNUM'], 0, 4);
        $wsCall = $this->wsClient->ws_getRichiestaDati($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Fault nel leggere i dati della richiesta " . $richieste_rec['RICNUM'] . ": " . $this->wsClient->getFault() . "\n";
            } elseif ($this->wsClient->getError()) {
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Errore nel leggere i dati della richiesta " . $richieste_rec['RICNUM'] . ": " . $this->wsClient->getError() . "\n";
            }
            return false;
        }

        $xmlDatiRichiesta = base64_decode($this->wsClient->getResult());


        /*
         * Estrazione dati da XML
         */
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlDatiRichiesta);

        $proric_xml_rec = $domDocument->getElementsByTagName('PRORIC')->item(0);
        $ricdoc_xml_tab = $domDocument->getElementsByTagName('RICDOC')->item(0);
        $ricdag_xml_tab = $domDocument->getElementsByTagName('RICDAG')->item(0);
        $proric_rec_tmp = $this->getRecords($proric_xml_rec);
        $proric_rec = $proric_rec_tmp[0];
        $ricdoc_tab = $this->getRecords($ricdoc_xml_tab);
        $ricdag_tab = $this->getRecords($ricdag_xml_tab);

        /*
         * Leggo i dati dei Soggetti
         */
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';
        $praLibRichiesta = praLibRichiesta::getInstance();
        $arrAggiuntivi = $praLibRichiesta->getSoggettiRichiesta($ricdag_tab);

        $metadati["PRORIC_REC"] = $proric_rec;

        /*
         * Costruzione array Allegati
         */
        $arrAllegati = array();
        foreach ($ricdoc_tab as $ricdoc_rec) {
            $arrAllegati[$ricdoc_rec['ROWID']]['FILENAME'] = $ricdoc_rec['DOCNAME'];
            $arrAllegati[$ricdoc_rec['ROWID']]['ROW_ID'] = $ricdoc_rec['ROWID'];
            $arrAllegati[$ricdoc_rec['ROWID']]['FILEFIL'] = $ricdoc_rec['DOCUPL'];
        }
        $metadati["ALLEGATI"] = $arrAllegati;

        /*
         * Preparo l'array PRAFOLIST
         */
        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_ITALSOFT_WS,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $codiceIStanza . "-" . $richieste_rec['RICNUM'],
            'FOIDPRATICA' => $richieste_rec['RICNUM'],
            'FOTIPOSTIMOLO' => $this->getStimolo($proric_rec),
            'FOPRASPACATA' => $richieste_rec['RICTSP'],
            'FOPRADESC' => $richieste_rec['PRADES'],
            'FOPRADATA' => $richieste_rec['RICDAT'],
            'FOPRAORA' => $richieste_rec['RICTIM'],
            'FOPROTDATA' => $richieste_rec['RICDPR'],
            'FOPROTORA' => "",
            'FOPROTNUM' => substr($richieste_rec['RICNPR'], 4),
            'FOESIBENTE' => $proric_rec['RICCOG'] . " " . $proric_rec['RICNOM'],
            'FODICHIARANTE' => $arrAggiuntivi['DICHIARANTE']['COGNOME'] . " " . $arrAggiuntivi['DICHIARANTE']['NOME'],
            'FODICHIARANTECF' => $arrAggiuntivi['DICHIARANTE']['FISCALE'],
            'FODICHIARANTEQUALIFICA' => $arrAggiuntivi['DICHIARANTE']['QUALIFICA'],
            'FOALTRORIFERIMENTODESC' => "Denominazione Impresa",
            'FOALTRORIFERIMENTO' => $arrAggiuntivi['IMPRESA']['DENOMINAZIONE'],
            'FOALTRORIFERIMENTOIND' => $arrAggiuntivi['IMPRESA']['VIA'] . " " . $arrAggiuntivi['IMPRESA']['CIVICO'],
            'FOALTRORIFERIMENTOCAP' => $arrAggiuntivi['IMPRESA']['CAP'],
            'FOMETADATA' => serialize($metadati),
            'FOCODICEPRATICASW' => $richieste_rec['CODICEPRATICASW']
        );

        if ($richieste_rec['RICUUID']) {
            $praFoList_rec['FOUUIDRICHIESTA'] = $richieste_rec['RICUUID'];
        }


        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => array(),
        );

        /*
         * Salvo l'array su PRAFOLIST e PRAFOFILES
         */
        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $data array che contine il recordo PRAFOLIST
     * @return boolean
     */
    public function salvaPratica($data) {

        /*
         * Salvo record su PRAFOLIST
         */
        $praFoList_rec = $data['PRAFOLIST'];
        try {
            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAFOLIST', 'ROWID', $praFoList_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento su PRAFOLIST non avvenuto.");
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Pratica " . $praFoList_rec['FOPRAKEY'] . " già riletta dal sistema: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getDescrizioneGeneraleRichiestaFo($prafolist_rec) {

        /*
         * Recupero l'istanza
         */
        $posCodice = strpos($prafolist_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($prafolist_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        $istanza = $metaDati[$codiceIStanza]['ISTANZA'];

        /*
         * Istanzio i parametri del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if (!$tokenKey) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare un token valido." . $this->getErrMessage();
            return false;
        }

        /*
         * Recupero la ditta dal token
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare il codice ditta." . $this->getErrMessage();
            $this->destroyToken($tokenKey);
            return false;
        }

        /*
         * Download body.txt
         */
        $params = Array();
        $params['itaEngineContextToken'] = $tokenKey;
        $params['domainCode'] = $domainCode;
        $params['richiesta'] = $prafolist_rec['FOIDPRATICA'];
        $wsCall = $this->wsClient->ws_GetBodyFile($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Fault nel prendere il file body.txt della richiesta " . $prafolist_rec['FOIDPRATICA'] . ": " . $this->wsClient->getFault() . "\n";
            } elseif ($this->wsClient->getError()) {
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Errore nel prendere il file body.txt della richiesta " . $prafolist_rec['FOIDPRATICA'] . ": " . $this->wsClient->getError() . "\n";
            }
            $this->destroyToken($tokenKey);
            return false;
        }

        $base64BodyTxt = $this->wsClient->getResult();

        $this->destroyToken($tokenKey);

        $contentFileBody = base64_decode($base64BodyTxt);
        return $contentFileBody;
    }

    public function getAllegatiRichiestaFo($prafolist_rec, $allegatiInfocamere) {
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        $allegati = $metaDati['ALLEGATI'];
        if ($allegatiInfocamere) {
            foreach ($allegatiInfocamere as $key => $allegato) {
                $allegatiInfocamere[$key]['ROW_ID'] = $key;
                $allegatiInfocamere[$key]['FILEFIL'] = $allegato['FILEINFO'];
                $allegatiInfocamere[$key]['DATAFILE'] = $allegato['FILEPATH'];
                $allegatiInfocamere[$key]['FILENAME'] = $allegato['FILENAME'];
                $allegatiInfocamere[$key]['FOPRAKEY'] = $prafolist_rec['FOPRAKEY'];
                $allegatiInfocamere[$key]['FOTIPO'] = $prafolist_rec['FOTIPO'];
            }

            $allegatiTabella = array_merge($allegati, $allegatiInfocamere);
        } else {
            $allegatiTabella = $allegati;
        }



        return $allegatiTabella;
    }

    public function checkFoAcqPreconditions($param) {
        return true;
    }

    private function getStimolo($richieste_rec) {
        $stimolo = "";
        if ($richieste_rec ['RICSTA'] != "91" && $richieste_rec ['RICRPA'] == "" && $richieste_rec['PROPAK'] == "") {
            $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA];
        } else {
            if ($richieste_rec['RICSTA'] == "91") {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_COMUNICA];
            } else if ($richieste_rec['RICRPA']) {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_INVIO_INTEGRAZIONI];
                if ($richieste_rec['RICPC'] == "1") {
                    $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_RICHIESTA_COLLEGATA];
                }
            } else if ($richieste_rec['PROPAK']) {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_PARERI_ESTERNI];
            }
        }
        return $stimolo;
    }

    private function setClientConfig($istanza) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config($istanza, 'codice', 'URI', false);
        $wsdl = $devLib->getEnv_config($istanza, 'codice', 'WSDL', false);
        $ns = $devLib->getEnv_config($istanza, 'codice', 'NAMESPACE', false);

        /* @var $WSClient praWsFrontOffice */
        $this->wsClient->setWebservices_uri($uri['CONFIG']);
        $this->wsClient->setWebservices_wsdl($wsdl['CONFIG']);
        $this->wsClient->setNamespace($ns);
        $this->wsClient->setTimeout(1200);
    }

    private function getToken($istanza) {
        $devLib = new devLib();
        $utente = $devLib->getEnv_config($istanza, 'codice', 'UTENTE', false);
        $pwd = $devLib->getEnv_config($istanza, 'codice', 'PASSWORD', false);
        $ente = $devLib->getEnv_config($istanza, 'codice', 'ENTE', false);

        $config['wsUser'] = $utente['CONFIG'];
        $config['wsPassword'] = $pwd['CONFIG'];
        $config['wsDomain'] = $ente['CONFIG'];
        
        $params = array(
            'userName' => $config['wsUser'],
            'userPassword' => $config['wsPassword'],
            'domainCode' => $config['wsDomain']
        );

        $wsCall = $this->wsClient->ws_GetItaEngineContextToken($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di prenotazione del token: " . $this->wsClient->getFault());
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di prenotazione del token: " . $this->wsClient->getError());
                return false;
            }
        }
        return $this->wsClient->getResult();
    }

    public function destroyToken($tokenKey) {
        //
        if (!$tokenKey) {
            $this->setErrCode(-1);
            $this->setErrMessage("Token non indicato");
            return false;
        }
        //
        list($token, $domainCode) = explode("-", $tokenKey);
        //
        $params = array(
            'token' => $tokenKey,
            'domainCode' => $domainCode
        );
        $wsCall = $this->wsClient->ws_DestroyItaEngineContextToken($params);

        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di distruzione del token: " . $this->wsClient->getFault());
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di distruzione del token: " . $this->wsClient->getError());
                return false;
            }
        }
        return true;
    }

    private function getRecords($node_tab) {
        $table = array();

        if (!$node_tab) {
            return $table;
        }

        foreach ($node_tab->getElementsByTagName('RECORD') as $node_rec) {
            $record = array();

            foreach ($node_rec->childNodes as $node_col) {
                if ($node_col->nodeType === XML_ELEMENT_NODE) {
                    $record[$node_col->nodeName] = $node_col->nodeValue;
                }
            }

            $table[] = $record;
        }

        return $table;
    }

    public function getDataModelAcq($praFoList_rec, $datiCaricamento) {

        /*
         * Recupero l'istanza
         */
        $posCodice = strpos($praFoList_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($praFoList_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($praFoList_rec['FOMETADATA']);
        $istanza = $metaDati[$codiceIStanza]['ISTANZA'];

        /*
         * Recupero l'array dati della richiesta
         */
        $praRemoteManager = itaModel::getInstance('praRemoteManager');
        $praRemoteManager = $this->getRemoteManager($istanza);
        $result = $praRemoteManager->getDatiRichiesta($praFoList_rec['FOIDPRATICA']);
        if ($result['Status'] == "-1") {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $result['Message'];
            return false;
        }
        $arrDatiRichiesta = $result["DatiRichiesta"];

        /*
         * Aggiungo elementi di PROGES che non vengono valorizzati nella funzione getDatiRichiesta.
         * In caso di Variante/integrazione $datiCaricamento è vuoto quindi valorizzo dati da PRORIC
         */
        if ($datiCaricamento) {
            $arrDatiRichiesta['PROGES_REC']['GESWFPRO'] = $datiCaricamento['PROGES']['GESWFPRO'];
            $arrDatiRichiesta['PROGES_REC']['GESCODPROC'] = $datiCaricamento['PROGES']['GESCODPROC'];
            $arrDatiRichiesta['PROGES_REC']['GESRES'] = $datiCaricamento['PROGES']['GESRES'];
        } else {
            $arrDatiRichiesta['PROGES_REC']['GESRES'] = $arrDatiRichiesta['PRORIC_REC']['RICRES'];
        }
        $arrDatiRichiesta['PROGES_REC']['GESPAR'] = "A";
        $arrDatiRichiesta['PROGES_REC']['GESNPR'] = $arrDatiRichiesta['PRORIC_REC']['RICNPR'];
        $arrDatiRichiesta['PROGES_REC']['GESMETA'] = $arrDatiRichiesta['PRORIC_REC']['RICMETA'];

        /*
         * Aggiungo altri elementi all'array dati
         */
        $arrDatiRichiesta['PRAFOLIST_REC'] = $praFoList_rec;
        $arrDatiRichiesta["DatiAssegnazione"] = $datiCaricamento['Assegnazione'];
        $arrDatiRichiesta["ALLEGATICOMUNICA"] = $datiCaricamento['ALLEGATICOMUNICA'];
        $arrDatiRichiesta["daPortlet"] = $datiCaricamento['daPortlet'];
        $arrDatiRichiesta["FILENAME"] = $datiCaricamento['FILENAME'];
        $arrDatiRichiesta["EscludiPassiFO"] = true;
        $arrDatiRichiesta["tipoReg"] = "consulta";
        if ($arrDatiRichiesta["PRORIC_REC"]['RICSTA'] == "91" && $arrDatiRichiesta["ALLEGATICOMUNICA"]) {
            $arrDatiRichiesta["tipoReg"] = "infocamere";
        }
        if (isset($datiCaricamento['PRAMAIL_REC'])) {
            $arrDatiRichiesta['PRAMAIL_REC'] = $datiCaricamento['PRAMAIL_REC'];
        }
        if (isset($datiCaricamento['archivio'])) {
            $arrDatiRichiesta['archivio'] = $datiCaricamento['archivio'];
        }
        if (isset($datiCaricamento['IDMAIL'])) {
            $arrDatiRichiesta['IDMAIL'] = $datiCaricamento['IDMAIL'];
        }
        return array($arrDatiRichiesta);
    }

    public function openFormDatiEssenziali($praFoList_rec, $dati = array()) {
        $metaDati = unserialize($praFoList_rec['FOMETADATA']);
        $proric_rec = $metaDati['PRORIC_REC'];
        $model = 'praGestDatiEssenziali';
        $_POST['returnModel'] = "praCtrRichiesteFO";
        $_POST['returnEvent'] = 'returnDatiEssenziali';
        $_POST['datiMail']['Dati'] = $dati;
        $_POST['datiMail']['Dati']['PRORIC_REC'] = $proric_rec;
        $_POST['datiMail']['Dati']['PRAFOLIST_REC'] = $praFoList_rec;
        $_POST['isFrontOffice'] = true;
        itaLib::openForm($model);
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent("openform");
        $objModel->parseEvent();
    }

    private function getRemoteManager($istanza) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config($istanza, 'codice', 'URI', false);
        $wsdl = $devLib->getEnv_config($istanza, 'codice', 'WSDL', false);
        $utente = $devLib->getEnv_config($istanza, 'codice', 'UTENTE', false);
        $pwd = $devLib->getEnv_config($istanza, 'codice', 'PASSWORD', false);
        $ente = $devLib->getEnv_config($istanza, 'codice', 'ENTE', false);
        //$ns = $devLib->getEnv_config($istanza, 'codice', 'NAMESPACE', false);

        $config['wsEndpoint'] = $uri['CONFIG'];
        $config['wsWsdl'] = $wsdl['CONFIG'];
        $config['wsNamespace'] = "";

        $praRemoteManager = new praRemoteManager();
        $praRemoteManager->setDomain($ente['CONFIG']);
        $praRemoteManager->setWsUser($utente['CONFIG']);
        $praRemoteManager->setWsPassword($pwd['CONFIG']);
        $praRemoteManager->setClientConfig($config);
        return $praRemoteManager;
    }

    public function getProricRec($praFoList_rec) {
        $metaDati = unserialize($praFoList_rec['FOMETADATA']);
        return $metaDati['PRORIC_REC'];
    }

    public function getAllegato($prafolist_rec, $rowidAlle, $allegatiInfocamere) {

        /*
         * Se allegato infocamere, lo prendo direttamente dall'array
         */
        if ($allegatiInfocamere) {
            $alle = $allegatiInfocamere[$rowidAlle];
            if ($alle) {
                return array('FILENAME' => $alle['FILENAME'], 'DATAFILE' => $alle['FILEPATH']);
            }
        }

        /*
         * Recupero l'istanza
         */
        $posCodice = strpos($prafolist_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($prafolist_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        $istanza = $metaDati[$codiceIStanza]['ISTANZA'];

        /*
         * Istanzio i parametri del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        $allegato = $metaDati['ALLEGATI'][$rowidAlle];

        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if ($tokenKey == "") {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'] = "Impossibile prendere un token valido: " . $this->getErrMessage();
            return false;
        }

        /*
         * Prendo il DomainCode
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'] = "Impossibile recuperare il codice ditta: " . $this->getErrMessage();
            $this->destroyToken($tokenKey);
            return false;
        }

        $params = array();
        $params['itaEngineContextToken'] = $tokenKey;
        $params['domainCode'] = $domainCode;
        $params['rowid'] = $rowidAlle;
        $wsCall = $this->wsClient->ws_getRichiestaAllegatoForRowid($params);

        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'] = "Fault in caso di download Allegato: " . $this->wsClient->getFault();
                $this->destroyToken($tokenKey);
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'] = "Error in caso di download Allegato: " . $this->wsClient->getError();
                $this->destroyToken($tokenKey);
                return false;
            }
            $this->destroyToken($tokenKey);
            return false;
        }
        $content = base64_decode($this->wsClient->getResult());

        $this->destroyToken($tokenKey);

        /*
         * Creo la cartella temporanea di lavoro
         */
        $tempPath = itaLib::createAppsTempPath($prafolist_rec['FOIDPRATICA']);
        if (!is_dir($tempPath)) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'] = "Creazione Directory di lavoro temporanea fallita.";
            return false;
        }
        $filepath = $tempPath . "/" . $allegato['FILENAME'];
        file_put_contents($filepath, $content);
        return array('FILENAME' => $allegato['FILENAME'], 'DATAFILE' => $filepath);
    }

    public function caricaRichiestaFO($prafolist_rec, $dati, $allegatiInfocamere) {
        $proric_rec = $this->getProricRec($prafolist_rec);
        $variante = false;
        if ($proric_rec['RICPC'] == "1") {
            $variante = true;
        }

        if ($proric_rec['RICSTA'] == "91" && !$allegatiInfocamere) {
            Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
                'F8-No' => array('id' => 'praCtrRichiesteFO_NoConfermaMail', 'model' => 'praCtrRichiesteFO', 'shortCut' => "f8"),
                'F5-Si' => array('id' => 'praCtrRichiesteFO_SiConfermaMail', 'model' => 'praCtrRichiesteFO', 'shortCut' => "f5")
                    ), "auto", "auto", "false"
            );
            return true;
        } else if (($proric_rec['RICRPA'] && !$variante) || $proric_rec['PROPAK']) {
            $ret_esito = null;
            if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito, $dati)) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'] = "Errore di acquisizione: " . praFrontOfficeManager::$lasErrMessage;
                return false;
            }
        } else {
            $this->openFormDatiEssenziali($prafolist_rec, $dati);
            return true;
        }
        return $ret_esito;
    }

    public function pubblicazioneArticoli($gesnum, $propak, $istanza, $tipoOperazione, $pubArticolo, $pubbAllegati) {
        
        
        /*
         * Istanzio i parametri del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        
        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if (!$tokenKey) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare un token valido." . $this->getErrMessage();

            return false;
        }

        
        /*
         * Recupero la ditta dal token
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare il codice ditta." . $this->getErrMessage();

            $this->destroyToken($tokenKey);
            return false;
        }

        
        if ($tipoOperazione == 'Insert'){

            // Si sincronizza il fascicolo con il metodo SyncFascicolo
            $esito = $this->syncFascicolo($gesnum, $tokenKey, $domainCode);
            if (!$esito){
                return false;
            }
            
            // Si sincronizza il passo con il servizio web SyncPasso
            $esito = $this->SyncPasso($propak, $tokenKey, $domainCode);
            if (!$esito){
                return false;
            }

            // E' flaggato il Check: Pubblica Articolo nei dati principali
            if ($pubArticolo){
                // E' stato scelto di Pubblicare gli allegati
                if ($pubbAllegati){
                    // Si sincronizzano gli allegati con il servizio web SyncAllegatiInfo
                    $this->syncAllegatiInfo($propak, $tokenKey, $domainCode);
                } 
                //E' stato scelto di NON Pubblicare gli allegati
                else {  
                    // Si cancellano gli eventuali allegati del passo sul FO con il servizio web DeleteAllegatoPasso
                    $this->deleteAllegatiPasso($propak, $tokenKey, $domainCode);
                }
            }
            else {
                
                // Se l'articolo non è da pubblicare, si cancellano gli allegati con il servizio web DeleteAllegatoPasso
                $esito = $this->deleteAllegatiPasso($propak, $tokenKey, $domainCode);
                if (!$esito){
                    return false;
                }

                // Svuoto il campo PROPAS.PRODATEPUBART
                $this->azzeraDataPubblicazione($propak);
            }
            

            
        }
        else if ($tipoOperazione == 'Delete'){
            
            // Si cancellano gli eventuali allegati del passo sul FO con il servizio web DeleteAllegatoPasso
            $this->deleteAllegatiPasso($propak, $tokenKey, $domainCode);

            // Si sincronizza il fascicolo con il metodo SyncFascicolo
            //$this->syncFascicolo($gesnum, $tokenKey, $domainCode);
            
            // Svuoto il campo PROPAS.PRODATEPUBART
            $this->azzeraDataPubblicazione($propak);

            
            // Si sincronizza il passo (mettendo PROPAS.PROPART = false attraverso il servizio web SyncPasso
            $this->SyncPasso($propak, $tokenKey, $domainCode, $tipoOperazione);
            
        }
        
        
        $this->destroyToken($tokenKey);
        
        
        return true;
    }

    public function azzeraDataPubblicazione($propak){
        $propas_rec = $this->praLib->GetPropas($propak, 'propak');
        if ($propas_rec){

            $propas_rec['PRODATEPUBART'] = null;
            $propas_rec['PROTIMEPUBART'] = '';

            $update_Info = "Oggetto: Azzero i campi PRODATEPUBART e PROTIMEPUBART del passo $propak";
            if (!$this->updateRecord($this->praLib->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
                return false;
            }
            
        }
        
    }

    public function deleteAllegatiPasso($propak, $tokenKey, $domainCode) {
        
        $pasdoc_tab = $this->praLib->getPasdoc($propak, 'codice', true);
        if ($pasdoc_tab){
            foreach($pasdoc_tab as $pasdoc_rec){
                if ($pasdoc_rec['PASSHA2']){
                    $params = array(
                        'itaEngineContextToken' => $tokenKey,
                        'domainCode' => $domainCode,
                        'chiavePasso' => $propak,
                        'allegatoSha2' => $pasdoc_rec['PASSHA2'],
                    );

//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                    $wsCall = $this->wsClient->ws_DeleteAllegatoPasso($params);
                    if (!$wsCall) {
                        if ($this->wsClient->getFault()) {
                            $Message = "Fault nel leggere gli allegati: " . $this->wsClient->getFault();
                        } elseif ($this->wsClient->getError()) {
                            $Message = "Errore nel leggere gli allegati: " . $this->wsClient->getError();
                        }
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $Message;

//                        Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                        return false;
                    }
                    $risposta = $this->wsClient->getResult();

                    if ($risposta != 'success'){
                        return false;
                    }
                }
                
            }
        }
        
        return true;
        
    }

    public function syncFascicolo($gesnum, $tokenKey, $domainCode) {

        $proges_rec = $this->praLib->GetProges($gesnum, 'codice');
        if (!$proges_rec){
            //Out::msgInfo("Attenzione", "Non trovato il fascicolo con il numero " . $gesnum);
            return false;;
        }

        $arrayProges = $proges_rec;
        unset($arrayProges['ROWID']);
                        
                        
        $dati = array();
        $dati['PROGES'][0] = $arrayProges;
                        
                        
        $fascicoloJason = json_encode(itaLib::utf8_encode_recursive($dati));

        $fascicolo = base64_encode($fascicoloJason);


        $params = array(
            'itaEngineContextToken' => $tokenKey,
            'domainCode' => $domainCode,
            'fascicolo' => $fascicolo,
        );


        $wsCall = $this->wsClient->ws_SyncFascicolo($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $Message = "Fault nel riportare il fascicolo: " . $this->wsClient->getFault();
            } elseif ($this->wsClient->getError()) {
                $Message = "Errore nel riportare il fascicolo: " . $this->wsClient->getError();
            }
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $Message;
//            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
            return false;
        }
        $risposta = $this->wsClient->getResult();

        if ($risposta < 1) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Aggiornamento Fascicolo n. " . $gesnum . " non riuscito";
            return false;
        }

        return true;
        
    }

    public function syncPasso($propak, $tokenKey, $domainCode, $tipoOperazione='Insert') {

        $propas_rec = $this->praLib->GetPropas($propak, 'propak');
        if (!$propas_rec){
            //Out::msgInfo("Attenzione", "Non trovato il passo con propak = " . $propak);
            return false;
        }

        
        $arrayPropas = $propas_rec;
        unset($arrayPropas['ROWID']);

        if ($tipoOperazione == "Delete"){
            $arrayPropas['PROPART'] = false;
        }
                        
        $dati = array();
        $dati['PROPAS'][0] = $arrayPropas;
                        
        $passoJason = json_encode(itaLib::utf8_encode_recursive($dati));
        
        $passo = base64_encode($passoJason);
                        
        $params = array(
            'itaEngineContextToken' => $tokenKey,
            'domainCode' => $domainCode,
            'passo' => $passo,
        );

        $wsCall = $this->wsClient->ws_SyncPasso($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $Message = "Fault nel riportare il passo: " . $this->wsClient->getFault();
            } elseif ($this->wsClient->getError()) {
                $Message = "Errore nel riportare il passo: " . $this->wsClient->getError();
            }
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $Message;

//            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
            return false;
        }
        $risposta = $this->wsClient->getResult();
                        
        if ($risposta < 1) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Aggiornamento Passo n. " . $propak . " non riuscito";
            
            return false;
        }

        
        //Aggiorno il campo PROPAS.PRODATEPUBART
//        $dt = new DateTime();
//        $propas_rec['PRODATEPUBART'] = $dt->format('Y-m-d H:i:s');

        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $propas_rec['PRODATEPUBART'] = $currentDate;
        $propas_rec['PROTIMEPUBART'] = $currentTime;

        
        $update_Info = "Oggetto: Aggiorno campo PRODATEPUBART " . $propas_rec['PRODATEPUBART'] . " e il campo PROTIMEPUBART " . $propas_rec['PROTIMEPUBART'] . " del passo " . $propak;
        if (!$this->updateRecord($this->praLib->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Aggiornamento data e ora pubblicazione articolo non riuscita";
            return false;
        }
        
        
        return true;
        
    }

    public function syncAllegatiInfo($propak, $tokenKey, $domainCode) {
        
        $propas_rec = $this->praLib->GetPropas($propak, 'propak');
        if (!$propas_rec){
//            Out::msgInfo("Attenzione", "Non trovato il passo con propak = " . $propak);
            return false;
        }

        $params = array(
            'itaEngineContextToken' => $tokenKey,
            'domainCode' => $domainCode,
            'passo' => $propak,
        );

        $wsCall = $this->wsClient->ws_SyncAllegatiInfo($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $Message = "Fault nel leggere gli allegati: " . $this->wsClient->getFault();
            } elseif ($this->wsClient->getError()) {
                $Message = "Errore nel leggere gli allegati: " . $this->wsClient->getError();
            }
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $Message;

//            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
            return false;
        }
        
        $risposta = $this->wsClient->getResult();
        
        if (!$risposta){
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Aggiornamento allegati del Passo n. " . $propak . " non riuscito";
            return false;
        }
        
        $dati = base64_decode($risposta);

        $arrayDati = itaLib::utf8_decode_recursive(json_decode($dati,true));

        if (!is_array($arrayDati)){
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Risposta sincronizzazione allegati inaspettata per il Passo n. " . $propak ;
            return false;
        }

        $numero = $arrayDati['NUMERO'][0];

        $allegati = $arrayDati['PASDOC'];
                        
        $esito = $this->sistemaAllegati($propak, $numero, $allegati, $tokenKey, $domainCode);
        
        return $esito;
        
    }

    private function sistemaAllegati($propak, $numero, $arrayAllegatiFO, $tokenKey, $domainCode){
        // Controlla gli allegati riletti e se trovi allegati mancanti vanno inviati

        $arrayAllegatiDel = array();
        $arrayAllegatiIns = array();
        
        $pasDocBO_tab = $this->praLib->GetPasdoc($propak, 'codice', true);
        if ($pasDocBO_tab){
            // Si scorrono gli allegati del BO e si controlla se è prresente nel FO
            foreach($pasDocBO_tab as $pasDocBO_rec){
                // Se allegato non è da pubblicare 
                if (!$pasDocBO_rec['PASPUB']){
                    // Questo controllo non va fatto, perchè se l'allegato era stato pubblicato, nel
                    // ciclo sotto sugli allegti FO, lo metterebbe tra gli allegti da eliminare
//                    // Vedere se presente nel FO e cancellarlo
//                    $trovato = false;
//                    foreach($arrayAllegatiFO as $allegatoFO){
//
//                        if ($allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']){
//                            $trovato = true;
//                            break;
//                        }
//
//                    }
//                    
//                    if ($trovato){
//                        $arrayAllegatiDel[] = $allegatoFO;
//                    }
                    
                    continue;
                }
                
                $trovato = false;
                foreach($arrayAllegatiFO as $allegatoFO){
                    
                    if ($allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']){
                        $trovato = true;
                        if ($allegatoFO['PASNOT'] != $pasDocBO_rec['PASNOT']){
                            // Se le note del docukento sono cambiate, si cancella e si reinserisce
                            $arrayAllegatiDel[] = $pasDocBO_rec;
                            
                            $arrayAllegatiIns[] = $pasDocBO_rec;
                            
                        }

                        break;
                    }
                    
                }

                if (!$trovato){
                    $arrayAllegatiIns[] = $pasDocBO_rec;
                }
                
            }

            // Scorre gli allegati del FO e controlla se qualcuno è stato eliminato 
            foreach($arrayAllegatiFO as $allegatoFO){
                $trovato = false;
                foreach($pasDocBO_tab as $pasDocBO_rec){
                    
                    if ($pasDocBO_rec['PASPUB'] && $allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']){
                        $trovato = true;
                        break;
                    }
                }
                
                if (!$trovato){
                    $arrayAllegatiDel[] = $allegatoFO;
                }
                
            }
            
        }
        else {
            $arrayAllegatiDel = $arrayAllegatiFO;
        }

//        Out::msgInfo("+ Allegati INS", print_r($arrayAllegatiIns,true));
//
//        Out::msgInfo("- Allegati DEL", print_r($arrayAllegatiDel,true));
        
//        return;

        // Gli allegati presenti in $arrayAllegatiDel si cancellano con il metodo DeleteAllegatoPasso
        if ($arrayAllegatiDel){
            foreach($arrayAllegatiDel as $allegato){
                
                $params = array(
                    'itaEngineContextToken' => $tokenKey,
                    'domainCode' => $domainCode,
                    'chiavePasso' => $allegato['PASKEY'],
                    'allegatoSha2' => $allegato['PASSHA2'],
                );

                
//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                $wsCall = $this->wsClient->ws_DeleteAllegatoPasso($params);
                if (!$wsCall) {
                    if ($this->wsClient->getFault()) {
                        $Message = "Fault nella cancellazione degli allegati: " . $this->wsClient->getFault();
                    } elseif ($this->wsClient->getError()) {
                        $Message = "Errore nella cancellazione  degli  allegati: " . $this->wsClient->getError();
                    }
                    $this->retStatus['Errori'] += 1;
                    $this->retStatus['Status'] = false;
                    $this->retStatus['Messages'][] = $Message;

//                    Out::msgInfo("Errore Cancellazione", '<pre style="font-size:1.5em">' . $Message . '</pre>');
//                    break;
                    
                    return false;
                    
                }
                $risposta = $this->wsClient->getResult();
                
                
            }

        }
        
        // Gli allegati presenti in $arrayAllegatiIns vanno inviati con il metodo putAllegatoPasso
        if ($arrayAllegatiIns){
            foreach($arrayAllegatiIns as $allegato){
  
                $dir = $this->praLib->SetDirectoryPratiche(substr($propak, 0, 4), $propak, "PASSO", false);
                
                $filename = $dir . "/" . $allegato['PASFIL'];
                
                if (file_exists($filename)){
                    $fileBinario = file_get_contents($filename);
                    
                    
                    $allegatoSped = array();
                    $allegatoSped['nomeFile'] = $allegato['PASNAME'];
                    $allegatoSped['sha256digest'] = $allegato['PASSHA2'];
                    $allegatoSped['stream'] = base64_encode($fileBinario);
                    $allegatoSped['note'] = $allegato['PASNOT'];

//                    Out::msgInfo("Allegati da inserire", print_r($allegatoSped, true));
                    
                    $params = array(
                        'itaEngineContextToken' => $tokenKey,
                        'domainCode' => $domainCode,
                        'chiavePasso' => $propak,
                        'allegato' => $allegatoSped,
                        'pubblicato' => $allegato['PASPUB'],
                    );

    //                        Out::msgInfo("Parametri", print_r($params,true));
    //                        return;

                    $wsCall = $this->wsClient->ws_putAllegatoPasso($params);
                    if (!$wsCall) {
                        if ($this->wsClient->getFault()) {
                            $Message = "Fault nell'invio degli allegati: " . $this->wsClient->getFault();
                        } elseif ($this->wsClient->getError()) {
                            $Message = "Errore nell'invio degli  allegati: " . $this->wsClient->getError();
                        }
                        
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $Message;
                        return false;
//                        Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
//                        break;
                    }
                    $risposta = $this->wsClient->getResult();
                    
//                    Out::msgInfo("Risposta", print_r($risposta,true));
                    
                }
                
                
                
            }
        }
        
        return true;
        
    }

    public function aggiornaArticoliFO() {
        
//        $praLib = new praLib();
        $tipoOperazione = 'Insert';

        // Si controlla se Configurato italsoft-ws
        $arrayFo = $this->praLib->getArrayTipiFO();

        foreach ($arrayFo as $foTrovato) {
            if ($foTrovato['TIPO'] == 'italsoft-ws' && $foTrovato['ATTIVO'] == 1) {
                $istanza = $foTrovato['ISTANZA'];
                break;
            }
        }
        
        
        // Non trovata configurazione per italsoft-ws
        if (!$istanza) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Configurazione Front Office Remota non trovata o non attiva." ;
            
            return false;
        }

        
        // Si prendono i record di PROPAS 
        $sql = "SELECT * FROM PROPAS  WHERE (DATAOPER > PRODATEPUBART) OR (DATAOPER = PRODATEPUBART AND TIMEOPER > PROTIMEPUBART) OR (DATAOPER IS NULL AND PRODATEPUBART IS NOT NULL)";
        $propas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        
        if ($propas_tab){
            foreach ($propas_tab as $propas_rec){
                $pubArticolo = $propas_rec['PROPART'];
                $pubAllegati = $propas_rec['PROPFLALLE'];
                
                $this->pubblicazioneArticoli($propas_rec['PRONUM'], $propas_rec['PROPAK'], $istanza, $tipoOperazione, $pubArticolo, $pubbAllegati);
            }
            
        }
        
        return true;
        
    }
    
    public function deleteArticoliFO() {
        // Si controlla se Configurato italsoft-ws
        $arrayFo = $this->praLib->getArrayTipiFO();

        foreach ($arrayFo as $foTrovato) {
            if ($foTrovato['TIPO'] == 'italsoft-ws' && $foTrovato['ATTIVO'] == 1) {
                $istanza = $foTrovato['ISTANZA'];
                break;
            }
        }
        
        
        // Non trovata configurazione per italsoft-ws
        if (!$istanza) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Configurazione Front Office Remota non trovata o non attiva." ;
            
            return false;
        }

        /*
         * Istanzio i parametri del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        
        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if (!$tokenKey) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare un token valido." . $this->getErrMessage();

            return false;
        }

        
        /*
         * Recupero la ditta dal token
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile recuperare il codice ditta." . $this->getErrMessage();

            $this->destroyToken($tokenKey);
            return false;
        }


        $sql = "SELECT * FROM PROPAS WHERE PROPART = 1 ORDER BY PROPAK";
        $prapas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);

        // Salvo il file con i PROPAK nella directory di appoggio D:\works\phpDev\data\tmp\itaEngine
        $pathFile = itaLib::createAppsTempPath('propak');
//                        $pathFile = itaLib::createAppsTempPath('tmp' . $IdPratica);
        $nomeFile = $pathFile . "/PROPAK.txt";

//        Out::msgInfo($pathFile, $nomeFile);

        unlink($nomeFile);

        // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
        //$dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);

        foreach ($prapas_tab as $prapas_rec) {
            file_put_contents($nomeFile, $prapas_rec['PROPAK'] . "\n", FILE_APPEND);
        }


        // istanza della classe ZipArchive
        $zip = new ZipArchive();
        // nome del file zip che voglio creare
        $nomeZip = $pathFile . "/PROPAK.zip";

        unlink($nomeZip);

        // creo il zip
        if ($zip->open($nomeZip, ZIPARCHIVE::CREATE) !== TRUE) {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Impossibile creare il file zip.";
            return false;
        }
        // aggiungo al file zip il file 'file1.txt'
        $zip->addFile($nomeFile, "propak.txt");
        // chiudo il file zip e salvo tutte le modifiche fatte ad esso
        $zip->close();

        
        $params = array(
            'itaEngineContextToken' => $tokenKey,
            'domainCode' => $domainCode,
            'stream' => base64_encode(file_get_contents($nomeZip)),
        );
        
        $wsCall = $this->wsClient->ws_SyncAllegatiDelete($params);

        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $Message = "Fault nel sincronizzare gli articoli cancellati: " . $this->wsClient->getFault();
            } elseif ($this->wsClient->getError()) {
                $Message = "Errore nel sincronizzare gli articoli cancellati: " . $this->wsClient->getError();
            }
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = $Message;
            return false;
            
        }
        $risposta = $this->wsClient->getResult();

        // Cancella directory utilizzata per salvare i files
        itaFileUtils::removeDir($pathFile);

        if ($risposta != 'success') {
            $this->retStatus['Errori'] += 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Errore nella spubblicazione degli Articoli cancellati " . $risposta ;
            return false;
        }

        return true;
        
    }


    
}
