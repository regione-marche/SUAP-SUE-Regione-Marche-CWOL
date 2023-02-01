<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DELLE PRATICHE NEL BO ITALSOFT REMOTO
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    15.07.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsFrontOffice.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class praFrontOfficeItalsoftBoWs extends praFrontOfficeManager {

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
         * Chiamo il metodo getElencoPassi
         */
        $params = Array();
        $params['itaEngineContextToken'] = $tokenKey;
        $params['domainCode'] = $domainCode;

        /*
         * Prendo i filtri di ricerca
         */
        $arrFiltri = $this->getFiltri($istanza);
        $params['tipoPasso'] = $arrFiltri['tipoPasso'];
        $params['stato'] = $arrFiltri['statoAcq'];
        $wsCall = $this->wsClient->ws_getElencoPassi($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Fault in caso di elenco passi: " . $this->wsClient->getFault();
                $this->destroyToken($tokenKey);
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = "Error in caso di elenco passi: " . $this->wsClient->getError();
                $this->destroyToken($tokenKey);
                return false;
            }
            $this->destroyToken($tokenKey);
            return false;
        }
        $xmlResult = base64_decode($this->wsClient->getResult());

        /*
         * Elaboro Xml d'usicta
         */
        if (!$xmlResult) {
            $this->retStatus['Errori'] = 0;
            $this->retStatus['Status'] = true;
            $this->retStatus['Messages'][] = "Nessuna richiesta estratta";
            $this->destroyToken($tokenKey);
            return true;
        }
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($xmlResult));
        if (!$retXml) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "File XML Richieste: Impossibile leggere il testo nell'xml";
            $this->destroyToken($tokenKey);
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'][] = "Lettura XML Richieste: Impossibile estrarre i dati";
            $this->destroyToken($tokenKey);
            return false;
        }

        /*
         * Normalizzazinoe array Pratiche
         */
        $arrayPratiche_totali = array();
        foreach ($arrayXml['RECORD'] as $key => $pratica) {
            foreach ($pratica as $campo => $value) {
                if ($value) {
                    $arrayPratiche_totali[$key][$campo] = $value[0]['@textNode'];
                }
            }
        }

        /*
         * Controllo le richieste da inserire
         */
        $arrayPratiche = $this->controlloPratiche($arrayPratiche_totali, $istanza);

        /*
         * Chiamo scarica pratica e preparo l'array di PRAFOLIST
         */
        $this->retStatus['Lette'] = count($arrayPratiche);
        foreach ($arrayPratiche as $datiPratica) {
            if (!$this->scaricaPratica($datiPratica, $tokenKey, $domainCode, $istanza)) {
                $this->retStatus['Errori'] += 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = $this->getErrMessage();
            } else {
                $this->retStatus['Scaricate'] += 1;
            }
        }
        $this->destroyToken($tokenKey);

        return $arrayXml;
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

    /**
     * 
     * @param type $richieste_tab array delle richieste
     * @return type array con solo le pratiche da inserire
     */
    private function controlloPratiche($richieste_tab, $istanza) {
        $richieste_tab_da_inserire = array();

        /*
         * Prendo il codice istanza
         */
        list($classe, $codiceIStanza) = explode("_", $istanza);

        $sql = "SELECT * FROM PRAFOLIST WHERE FOTIPO = '" . praFrontOfficeManager::TYPE_BO_ITALSOFT_WS . "' AND FOPRAKEY LIKE '$codiceIStanza%'";
        $prafolist_tab = $this->praLib->GetGenericTab($sql);
        $array1 = array_column($richieste_tab, "GESNUM");
        $array2 = array_column($prafolist_tab, "FOIDPRATICA");
        $arrDiff = array_diff($array1, $array2);
        foreach ($arrDiff as $key => $idpratica) {
            $richieste_tab_da_inserire[] = $richieste_tab[$key];
        }
        return $richieste_tab_da_inserire;
    }

    /**
     * 
     * @param type $datiPratica Campi provenienti
     *  dal metodo ws getElencoPassi
     */
    public function scaricaPratica($datiPratica, $token, $domainCode, $istanza) {

        /*
         * Leggo l'xml dei dati della pratica
         */
        $xmlResult = $this->getXmlPraticaDati($datiPratica['GESNUM'], $token, $domainCode);
        if (!$xmlResult) {
            $this->setErrCode(-2);
            $this->setErrMessage("Nessuna richiesta estratta." . $this->getErrMessage());
            $this->destroyToken($token);
            return false;
        }

        /*
         * Elaboro Xml d'uscita per salvarlo con json nei metadati
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($xmlResult));
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML Richieste: Impossibile leggere il testo nell'xml");
            $this->destroyToken($token);
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML Richieste: Impossibile estrarre i dati");
            $this->destroyToken($token);
            return false;
        }

        /*
         * Elaboro Xml d'usicta per trovarmi i record Principali per
         * l'asseganzione dei campi di PRAFOLIST
         */
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlResult);

        $anades_esibente = false;
        $anades_dichiarante = false;
        $anades_impresa = false;
        $proges_xml_rec = $domDocument->getElementsByTagName('PROGES')->item(0);
        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);
        $anades_tab = $this->getRecords($anades_xml_tab);
        $proges_rec_tmp = $this->getRecords($proges_xml_rec);
        $proges_rec = $proges_rec_tmp[0];
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('ESIBENTE')) {
                $anades_esibente = $anades_rec;
                break;
            }
        }
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('DICHIARANTE')) {
                $anades_dichiarante = $anades_rec;
                break;
            }
        }
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('IMPRESA')) {
                $anades_impresa = $anades_rec;
                break;
            }
        }
        if (!$anades_impresa) {
            foreach ($anades_tab as $anades_rec) {
                if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('IMPRESA_INDIVIDUALE')) {
                    $anades_impresa = $anades_rec;
                    break;
                }
            }
        }

        /*
         * Decodifico la data protocollo dai metadati di PROGES
         */
        $dataProt = $time = "";
        $metadati_proges = array();
        if ($proges_rec['GESMETA']) {
            $metadati_proges = unserialize($proges_rec['GESMETA']);
            if (isset($metadati_proges['DatiProtocollazione'])) {
                $time = strtotime($metadati_proges['DatiProtocollazione']['Data']['value']);
                $dataProt = date('Ymd', $time);
            }
        }

        /*
         * Prendo il codice istanza
         */
        list($classe, $codiceIStanza) = explode("_", $istanza);

        $metadati[$codiceIStanza] = array("ISTANZA" => $istanza, "DESCRIZIONE" => $this->getDescrizioneIstanza($istanza));

        /*
         * Inserisco gli allegati nei metadati
         */
        $pasdoc_xml_tab = $domDocument->getElementsByTagName('PASDOC')->item(0);
        $pasdoc_tab = $this->getRecords($pasdoc_xml_tab);
        $arrAllegati = array();
        foreach ($pasdoc_tab as $pasdoc_rec) {
            $arrAllegati[$pasdoc_rec['ROWID']]['FILENAME'] = $pasdoc_rec['PASNAME'];
            $arrAllegati[$pasdoc_rec['ROWID']]['ROW_ID'] = $pasdoc_rec['ROWID'];
            $arrAllegati[$pasdoc_rec['ROWID']]['FILEFIL'] = $pasdoc_rec['PASFIL'];
        }
        $metadati["ALLEGATI"] = $arrAllegati;

        /*
         * Preparo l'array praFoList
         */
        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_BO_ITALSOFT_WS,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $codiceIStanza . "-" . $proges_rec['GESNUM'],
            'FOIDPRATICA' => $proges_rec['GESNUM'],
            'FOTIPOSTIMOLO' => praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_BO_ITALSOFT_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA],
            'FOPRASPACATA' => $proges_rec['GESTSP'],
            'FOPRADESC' => $proges_rec['PRADES'],
            'FOPRADATA' => $proges_rec['GESDRI'],
            'FOPRAORA' => $proges_rec['GESORA'],
            'FOPROTDATA' => $dataProt,
            'FOPROTORA' => "",
            'FOPROTNUM' => substr($proges_rec['GESNPR'], 4),
            'FOESIBENTE' => $anades_esibente['DESNOM'],
            'FODICHIARANTE' => $anades_dichiarante['DESNOM'],
            'FODICHIARANTECF' => $anades_dichiarante['DESFIS'] ? $anades_dichiarante['DESFIS'] : $anades_dichiarante['DESPIVA'],
            'FODICHIARANTEQUALIFICA' => $anades_dichiarante['DESQUALIFICA'],
            'FOALTRORIFERIMENTODESC' => "Denominazione Impresa",
            'FOALTRORIFERIMENTO' => $anades_impresa['DESNOM'],
            'FOALTRORIFERIMENTOIND' => $anades_impresa['DESIND'],
            'FOALTRORIFERIMENTOCAP' => $anades_impresa['DESCAP'],
            'FOMETADATA' => serialize($metadati)//json_encode(itaLib::utf8_encode_recursive($arrayXml))
        );

        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => array(),
        );

        /*
         * Salvo l'array su PRAFOLIST
         */
        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            $this->destroyToken($token);
            return false;
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

    /**
     * 
     * @param type $data array che contine il recordo PRAFOLIST
     * @return boolean
     */
    public function salvaPratica($data) {
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
        $posCodice = strpos($prafolist_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($prafolist_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        $dataRicez = substr($prafolist_rec['FOPRADATA'], 6, 2) . "/" . substr($prafolist_rec['FOPRADATA'], 4, 2) . "/" . substr($prafolist_rec['FOPRADATA'], 0, 4);
        $dataProt = substr($prafolist_rec['FOPROTDATA'], 6, 2) . "/" . substr($prafolist_rec['FOPROTDATA'], 4, 2) . "/" . substr($prafolist_rec['FOPROTDATA'], 0, 4);
        $descrizione = '<div style="padding:5px;">';
        $descrizione .= "Back Office Remoto: " . $metaDati[$codiceIStanza]['DESCRIZIONE'] . "<br/><br/>";
        $descrizione .= "Il fascicolo: " . $prafolist_rec['FOIDPRATICA'] . " del $dataRicez <br/>"
                . " con protocollo n. " . $prafolist_rec['FOPROTNUM'] . " del $dataProt <br/> "
                . " è stata ricevuto. <br/> "
                . "Di seguito si riporta il riepilogo del fascicolo da acquisire: <br/> <br/>"
                . "DATI RICHIEDENTE <br/>"
                . "Qualifica: " . $prafolist_rec['FODICHIARANTEQUALIFICA'] . "<br/>"
                . "Nominativo: " . $prafolist_rec['FODICHIARANTE'] . "<br/>"
                . "Codice Fiscale: " . $prafolist_rec['FODICHIARANTECF'] . "<br/>";

        $descrizione = $descrizione . "<br/>"
                . "DATI IMPRESA <br/>"
                . "Ragione Sociale: " . $prafolist_rec['FOALTRORIFERIMENTO'] . "<br/>"
                . "Indirizzo: " . $prafolist_rec['FOALTRORIFERIMENTOIND'] . "<br/>"
                . "CAP: " . $prafolist_rec['FOALTRORIFERIMENTOCAP'] . "<br/>";
        return $descrizione;
    }

    public function getAllegatiRichiestaFo($prafolist_rec) {
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        return $metaDati['ALLEGATI'];
    }

    public function checkFoAcqPreconditions($param) {
        return true;
    }

    public function getDataModelAcq($praFoList_rec, $datiCaricamento) {

        /*
         * Rileggo l'istanza dal DB
         */
        $posCodice = strpos($praFoList_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($praFoList_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($praFoList_rec['FOMETADATA']);
        $istanza = $metaDati[$codiceIStanza]['ISTANZA'];

        /*
         * Leggo i parametri  del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if ($tokenKey == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile prendere un token valido.");
            return false;
        }

        /*
         * Prendo il DomainCode
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile recuperare il codice ditta.");
            $this->destroyToken($tokenKey);
            return false;
        }

        /*
         * Prendo l'xml dei dati del fascicolo
         */
        $xmlResult = $this->getXmlPraticaDati($praFoList_rec['FOIDPRATICA'], $tokenKey, $domainCode);
        if (!$xmlResult) {
            $this->setErrCode(-2);
            $this->setErrMessage("Nessuna richiesta estratta");
            $this->destroyToken($tokenKey);
            return true;
        }

        $this->destroyToken($tokenKey);

        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlResult);

        $anades_esibente = false;
        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);


        $anades_tab = $this->getRecords($anades_xml_tab);
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('ESIBENTE')) {
                $anades_esibente = $anades_rec;
                unset($anades_esibente['ROWID']);
                break;
            }
        }

        if (!$anades_esibente) {
            $this->setErrCode(-1);
            $this->setErrMessage("Soggetto 'ESIBENTE' non trovato.");
            return false;
        }

        $datamodelArray = array();
        $datamodelArray['PROGES_REC'] = $datiCaricamento['PROGES'];
        $datamodelArray['ANADES_REC'] = $anades_esibente;
        $datamodelArray['ITEEVT_REC'] = $this->praLib->GetIteevt($datiCaricamento['ITEEVT']['ROWID'], "rowid");
        $datamodelArray['DatiAssegnazione'] = $datiCaricamento['Assegnazione'];
        $datamodelArray['tipoInserimento'] = "daAnagrafica";
        $datamodelArray['EscludiPassiFO'] = true;
        $datamodelArray['PRAFOLIST_REC'] = $praFoList_rec;
        return array($datamodelArray);
    }

    public function acquisisciDatiFascicolo($prafolist_rec) {
        $gesnum = $prafolist_rec['FOGESNUM'];

        /*
         * Rileggo l'istanza dal DB
         */
        $posCodice = strpos($prafolist_rec['FOPRAKEY'], "-");
        $codiceIStanza = substr($prafolist_rec['FOPRAKEY'], 0, $posCodice);
        $metaDati = unserialize($prafolist_rec['FOMETADATA']);
        $istanza = $metaDati[$codiceIStanza]['ISTANZA'];


        /*
         * Leggo i parametri  del ws
         */
        $this->wsClient = new praWsFrontOffice();
        $this->setClientConfig($istanza);

        /*
         * Prendo il Token
         */
        $tokenKey = $this->getToken($istanza);
        if ($tokenKey == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile prendere un token valido.");
            return false;
        }

        /*
         * Prendo il DomainCode
         */
        list($token, $domainCode) = explode("-", $tokenKey);
        if ($domainCode == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile recuperare il codice ditta.");
            $this->destroyToken($tokenKey);
            return false;
        }

        /*
         * Prendo l'xml dei dati del fascicolo
         */
        $xmlFascicolo = $this->getXmlPraticaDati($prafolist_rec['FOIDPRATICA'], $tokenKey, $domainCode);
        if (!$xmlFascicolo) {
            $this->setErrCode(-2);
            $this->setErrMessage("Nessuna richiesta estratta");
            $this->destroyToken($tokenKey);
            return true;
        }

        /*
         * Prendo i filtri di ricerca
         */
        $arrFiltri = $this->getFiltri($istanza);

        /*
         * Rilego il nuovo PROGES_REC acquisito
         */
        $proges_rec = $this->praLib->getProges($gesnum);

        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlFascicolo);

        $proges_xml_rec = $domDocument->getElementsByTagName('PROGES')->item(0);
        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);
        $propas_xml_tab = $domDocument->getElementsByTagName('PROPAS')->item(0);
        $prodag_xml_tab = $domDocument->getElementsByTagName('PRODAG')->item(0);
        $pasdoc_xml_tab = $domDocument->getElementsByTagName('PASDOC')->item(0);
        $praimm_xml_tab = $domDocument->getElementsByTagName('PRAIMM')->item(0);
        $pramitdest_xml_tab = $domDocument->getElementsByTagName('PRAMITDEST')->item(0);
        $pracom_xml_tab = $domDocument->getElementsByTagName('PRACOM')->item(0);

        $mappaturaChiavi = array();

        /*
         * Aggiorno Alcuni Dati di PROGES
         */
        $proges_fromXml_rec = $this->getRecords($proges_xml_rec);
        $proges_rec['GESDRI'] = $proges_fromXml_rec[0]['GESDRI'];
        $proges_rec['GESORA'] = $proges_fromXml_rec[0]['GESORA'];
        $proges_rec['GESNPR'] = $proges_fromXml_rec[0]['GESNPR'];
        $proges_rec['GESPAR'] = $proges_fromXml_rec[0]['GESPAR'];
        $proges_rec['GESMETA'] = $proges_fromXml_rec[0]['GESMETA'];
        $numPratica = substr($proges_fromXml_rec[0]['GESNUM'], 4) . "/" . substr($proges_fromXml_rec[0]['GESNUM'], 0, 4);
        if ($proges_fromXml_rec[0]['GESPRA']) {
            $numRichiesta = substr($proges_fromXml_rec[0]['GESPRA'], 4) . "/" . substr($proges_fromXml_rec[0]['GESPRA'], 0, 4);
            $msgRichiesta = ", Richiesta on-line n. $numRichiesta";
        }
        $proges_rec['GESNOT'] = "Acquisita Pratica n. $numPratica $msgRichiesta da Back Office remoto " . $this->getDescrizioneIstanza($istanza) . "\n\n" . $proges_fromXml_rec[0]['GESNOT'];
        try {
            ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PROGES', 'ROWID', $proges_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento PROGES: " . $e->getMessage());
            $this->destroyToken($tokenKey);
            return false;
        }


        /*
         * Caricamento ANADES
         */
        $anades_tab = $this->getRecords($anades_xml_tab);
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('ESIBENTE')) {
                continue;
            }

            unset($anades_rec['ROWID']);
            $anades_rec['DESNUM'] = $gesnum;

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ANADES', 'ROWID', $anades_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert ANADES: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }

        /*
         * Caricamento PROPAS
         */
        $propas_tab = $this->getRecords($propas_xml_tab);
        foreach ($propas_tab as $propas_rec) {

            /*
             * Escludo i passi Assegnazione
             */
            if ($propas_rec['PROOPE']) {
                continue;
            }

            $propasRicerca_rec = array();
            if ($propas_rec['PROCLT'] == $arrFiltri['tipoPasso']) {
                $propasRicerca_rec = $propas_rec;
                continue;
            }

            $mappaturaChiavi[$propas_rec['PRONUM']] = $gesnum;
            $mappaturaChiavi[$propas_rec['PROPAK']] = $this->praLib->PropakGenerator($gesnum);

            unset($propas_rec['ROWID']);
            $propas_rec['PRONUM'] = $gesnum;
            $propas_rec['PROPAK'] = $mappaturaChiavi[$propas_rec['PROPAK']];

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PROPAS', 'ROWID', $propas_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PROPAS: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }

        /*
         * Caricamento PRODAG
         */
        $prodag_tab = $this->getRecords($prodag_xml_tab);
        foreach ($prodag_tab as $prodag_rec) {
            unset($prodag_rec['ROWID']);
            $dagpak = $mappaturaChiavi[$prodag_rec['DAGPAK']];
            $prodag_rec['DAGNUM'] = $gesnum;
            $prodag_rec['DAGPAK'] = $dagpak;
            $prodag_rec['DAGSET'] = $dagpak . substr($prodag_rec['DAGSET'], -3);
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRODAG', 'ROWID', $prodag_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PRODAG: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }

        /*
         * Caricamento PRAIMM
         */
        $praimm_tab = $this->getRecords($praimm_xml_tab);
        foreach ($praimm_tab as $praimm_rec) {
            unset($praimm_rec['ROWID']);
            $praimm_rec['PRONUM'] = $gesnum;
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAIMM', 'ROWID', $praimm_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PRAIMM: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }

        /*
         * Caricamento PRACOM
         */
        $pracom_tab = $this->getRecords($pracom_xml_tab);
        foreach ($pracom_tab as $pracom_rec) {
            $oldRowid = $pracom_rec['ROWID'];
            unset($pracom_rec['ROWID']);
            $pracom_rec['COMNUM'] = $gesnum;
            $pracom_rec['COMPAK'] = $mappaturaChiavi[$pracom_rec['COMPAK']];
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRACOM', 'ROWID', $pracom_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PRACOM: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
            $newPracomRowid = $this->praLib->getPRAMDB()->getLastId();
            $mappaturaChiavi[$oldRowid] = $newPracomRowid;
        }

        /*
         * Caricamento PRAMITDEST
         */
        $pramitdest_tab = $this->getRecords($pramitdest_xml_tab);
        foreach ($pramitdest_tab as $pramitdest_rec) {
            unset($pramitdest_rec['ROWID']);
            $pramitdest_rec['KEYPASSO'] = $mappaturaChiavi[$pramitdest_rec['KEYPASSO']];
            $pramitdest_rec['ROWIDPRACOM'] = $mappaturaChiavi[$oldRowid];
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAMITDEST', 'ROWID', $pramitdest_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PRAMITDEST: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }

        /*
         * Caricamento PASDOC
         */
        $pasdoc_tab = $this->getRecords($pasdoc_xml_tab);
        foreach ($pasdoc_tab as $pasdoc_rec) {
            $streamAllegato = $this->getAllegatoFascicolo($tokenKey, $domainCode, $pasdoc_rec['ROWID']);
            if (!$streamAllegato) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile caricare l'allegato " . $pasdoc_rec['PASNAME'] . ": " . $this->getErrMessage());
                return false;
            }

            unset($pasdoc_rec['ROWID']);
            $pasdoc_rec['PASKEY'] = $mappaturaChiavi[$pasdoc_rec['PASKEY']];
            if ($pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                $pasdoc_rec['PASPRTROWID'] = $proges_rec['ROWID'];
            } elseif ($pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                $pasdoc_rec['PASPRTROWID'] = $mappaturaChiavi[$oldRowid];
            }
            //$destinazioneAllegati = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec['PASKEY'], 0, 4), $pasdoc_rec['PASKEY']);
            if (strlen($pasdoc_rec["PASKEY"]) == 10) {
                $destinazioneAllegati = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec["PASKEY"], 0, 4), $pasdoc_rec["PASKEY"], 'PROGES');
            } elseif (strlen($pasdoc_rec["PASKEY"]) > 10) {
                $destinazioneAllegati = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec["PASKEY"], 0, 4), $pasdoc_rec["PASKEY"], "PASSO");
            }

            if (!file_put_contents($destinazioneAllegati . '/' . $pasdoc_rec['PASFIL'], $streamAllegato)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile copiare l'allegato in " . $destinazioneAllegati . '/' . $pasdoc_rec['PASFIL']);
                return false;
            }

            /*
             * Se è un Testo Base controllo i pdf o p7m allegati
             */
            if ($pasdoc_rec['PASCLA'] == 'TESTOBASE') {
                $arrAlleTB = $this->getAllegatoGeneratoFromTestiBase($tokenKey, $domainCode, $pasdoc_rec['PASFIL']);
                if (isset($arrAlleTB['stream'])) {
                    $streamAllegatoTB = base64_decode($arrAlleTB['stream']);
                    if (!$streamAllegatoTB) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore in decodifica del file " . $pasdoc_rec['PASNAME']);
                        $this->destroyToken($tokenKey);
                        return false;
                    }

                    /*
                     * Copio l'allegato del Testo Base
                     */
                    if (!file_put_contents($destinazioneAllegati . '/' . $arrAlleTB['filename'], $streamAllegatoTB)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Impossibile copiare l'allegato del testo base in " . $destinazioneAllegati . '/' . $arrAlleTB['filename']);
                        $this->destroyToken($tokenKey);
                        return false;
                    }
                }
            }

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PASDOC', 'ROWID', $pasdoc_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore insert PASDOC: " . $e->getMessage());
                $this->destroyToken($tokenKey);
                return false;
            }
        }
        /*
         * Aggiorno e chiudo il passo remoto di ricerca
         */
        $params = array();
        $params['itaEngineContextToken'] = $tokenKey;
        $params['domainCode'] = $domainCode;
        $params['codicePasso'] = $propasRicerca_rec['PROPAK'];
        $params['stato'] = $arrFiltri['statoChi'];
        $params['dataApertura'] = $propasRicerca_rec['PROINI'];
        $params['dataChiusura'] = date('Ymd');
        $wsCall = $this->wsClient->ws_SetStatoPasso($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di aggiornamento del passo: " . $propasRicerca_rec['PROPAK'] . "-" . $this->wsClient->getFault());
                $this->destroyToken($tokenKey);
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di aggiornamento del passo: " . $propasRicerca_rec['PROPAK'] . "-" . $this->wsClient->getError());
                $this->destroyToken($tokenKey);
                return false;
            }
        }
        $result = $this->wsClient->getResult();
        if ($result != "Success") {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento del passo: " . $propasRicerca_rec['PROPAK'] . " fallito");
            $this->destroyToken($tokenKey);
            return false;
        }

        $this->destroyToken($tokenKey);
        return true;
    }

    public function getAllegatoFascicolo($contextToken, $domainCode, $rowid) {
        $wsCall = $this->wsClient->ws_getPraticaAllegatoForRowid(array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => $domainCode,
            'rowid' => $rowid
        ));
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di download allegato: " . $this->wsClient->getFault());
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di download allegato: " . $this->wsClient->getError());
                return false;
            }
        }

        $resultGetAllegato = $this->wsClient->getResult();
        if (!$resultGetAllegato) {
            return false;
        }

        $streamAllegato = base64_decode($resultGetAllegato);

        if (!$streamAllegato) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in decodifica risposta 'ws_getPraticaAllegatoForRowid'");
            return false;
        }

        return $streamAllegato;
    }

    private function getXmlPraticaDati($gesnum, $tokenKey, $domainCode) {
        $params = array(
            'itaEngineContextToken' => $tokenKey,
            'domainCode' => $domainCode,
            'numeroPratica' => substr($gesnum, 4),
            'annoPratica' => substr($gesnum, 0, 4)
        );

        $wsCall = $this->wsClient->ws_getPraticaDati($params);
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di lettura pratica dati: " . $this->wsClient->getFault());
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di lettura pratica dati: " . $this->wsClient->getError());
                return false;
            }
        }

        return base64_decode($this->wsClient->getResult());
    }

    public function openFormDatiEssenziali($praFoList_rec, $dati = array()) {
        $model = 'praGestDatiEssenziali';
        $_POST['returnModel'] = "praCtrRichiesteFO";
        $_POST['returnEvent'] = 'returnDatiEssenziali';
        $_POST['isFrontOfficeAvanzato'] = true;
        $_POST['datiMail']['Dati'] = $dati;
        $_POST['datiMail']['Dati']['PRAFOLIST_REC'] = $praFoList_rec;
        itaLib::openForm($model);
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent("openform");
        $objModel->parseEvent();
    }

    private function getFiltri($istanza) {
        $devLib = new devLib();
        $tipoPasso = $devLib->getEnv_config($istanza, 'codice', 'TIPOPASSO', false);
        $statoAcq = $devLib->getEnv_config($istanza, 'codice', 'STATOPASSOACQ', false);
        $statoChi = $devLib->getEnv_config($istanza, 'codice', 'STATOPASSOCHI', false);
        return array(
            'tipoPasso' => $tipoPasso['CONFIG'],
            'statoAcq' => $statoAcq['CONFIG'],
            'statoChi' => $statoChi['CONFIG']
        );
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

    public function getAllegato($prafolist_rec, $rowidAlle) {

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
        $wsCall = $this->wsClient->ws_getPraticaAllegatoForRowid($params);
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

    public function caricaRichiestaFO($prafolist_rec) {
        $this->openFormDatiEssenziali($prafolist_rec);
        return true;
    }

    public function getAllegatoGeneratoFromTestiBase($contextToken, $domainCode, $pasfil) {
        $wsCall = $this->wsClient->ws_GetPraticaAllegatoFromTestoBase(array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => $domainCode,
            'nomeFile' => $pasfil
        ));
        if (!$wsCall) {
            if ($this->wsClient->getFault()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di download allegato del testo base: " . $this->wsClient->getFault());
                return false;
            } elseif ($this->wsClient->getError()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fault in fase di download allegato del testo base: " . $this->wsClient->getError());
                return false;
            }
        }

        $arrAllegatoTB = $this->wsClient->getResult();
        return $arrAllegatoTB;
    }

}
