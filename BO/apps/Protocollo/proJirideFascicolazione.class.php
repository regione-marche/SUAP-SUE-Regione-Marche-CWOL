<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    05.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPJiride/itaWsFascicolazioneClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFascicolazione.class.php';

class proJirideFascicolazione extends proWsClientFascicolazione {

    /**
     * Libreria di funzioni Generiche e Utility per Fascicolazione con Protocollo JIride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($jirideClient) {
//        $devLib = new devLib();
//        $uri = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLIENDPOINT', false);
//        $itaWsPostaClient->setWebservices_uri($uri['CONFIG']);
//
//        $wsdl = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLIWSDL', false);
//        $itaWsPostaClient->setWebservices_wsdl($wsdl['CONFIG']);
//
//        $itaWsPostaClient->setNameSpaces();
//
//        $utente = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLIUTENTE', false);
//        $itaWsPostaClient->setUtente($utente['CONFIG']);
//
//        $ruolo = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLIRUOLO', false);
//        $itaWsPostaClient->setRuolo($ruolo['CONFIG']);
//
//        $CodiceAmministrazione = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLICODICEAMMINISTRAZIONE', false);
//        $itaWsPostaClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);
//
//        $CodiceAOO = $devLib->getEnv_config('JIRIDEWSFASCICOLAZIONE', 'codice', 'WSJIRIDEFASCICOLICODICEAOO', false);
//        $itaWsPostaClient->setCodiceAOO($CodiceAOO['CONFIG']);

        if ($this->arrConfigParams) {
            $uri = "";
            $wsdl = "";
            $ns = "";
            $utente = "";
            $ruolo = "";
            $CodiceAmministrazione = "";
            $CodiceAOO = "";
        } else {
            $keyConfigParam = proWsClientHelper::CLASS_PARAM_FASCICOLAZIONE_JIRIDE;
            if ($this->keyConfigParam) {
                $keyConfigParam = $this->keyConfigParam;
            }
            $devLib = new devLib();
            $uri = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLIENDPOINT', false);
            $wsdl = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLIWSDL', false);
            $ns = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSIRIDENAMESPACE', false);
            $utente = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLIUTENTE', false);
            $ruolo = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLIRUOLO', false);
            $CodiceAmministrazione = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLICODICEAMMINISTRAZIONE', false);
            $CodiceAOO = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEFASCICOLICODICEAOO', false);
        }
        $jirideClient->setWebservices_uri($uri['CONFIG']);
        $jirideClient->setWebservices_wsdl($wsdl['CONFIG']);
        $jirideClient->setNameSpaces();
        $jirideClient->setNamespace($ns['CONFIG']);
        $jirideClient->setUtente($utente['CONFIG']);
        $jirideClient->setRuolo($ruolo['CONFIG']);
        $jirideClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);
        $jirideClient->setCodiceAOO($CodiceAOO['CONFIG']);
    }

    public function CreaFascicolo($elementi) {
        $itaWsFascicolazioneClient = new itaWsFascicolazioneClient();
        $this->setClientConfig($itaWsFascicolazioneClient);

        /*
         * Se c'è l'id fascicolo vado direttamente al metodo Fascicola, altrimenti creo il fascicolo
         */
        $param = array();
        $param['Anno'] = "";
        $param['Numero'] = "";
        $param['Data'] = "";
        $param['Oggetto'] = $elementi['dati']['Fascicolazione']['Oggetto'];
        $param['Classifica'] = $elementi['dati']['Classificazione'];
        $param['AltriDati'] = "";
        $param['Eterogeneo'] = "";
        $param['DataChiusura'] = "";
        $param['DatiAggiuntivi'] = "";
        $param['Applicazione'] = "";
        $param['Aggiornamento'] = "";
        $param['AnagraficaCf'] = "";
        $param['AnagraficaPiva'] = "";
        //
        $ret = $itaWsFascicolazioneClient->ws_CreaFascicoloString($param);
        if (!$ret) {
            if ($itaWsFascicolazioneClient->getFault()) {
                $msg = $itaWsFascicolazioneClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaWsFascicolazioneClient->getError()) {
                $msg = $itaWsFascicolazioneClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsFascicolazioneClient->getResult();

        /*
         * Elaboro Xml d'usicta
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $risultatoUtf8 = utf8_encode($risultato); //Fatto l'encode per descrizone classifica come segue: «COMMERCIO»
        $retXml = $xmlObj->setXmlFromString($risultatoUtf8);
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

        foreach ($arrayXml as $elemento => $value) {
            if ($elemento == "Id") {
                $idFascicolo = $value[0]["@textNode"];
            }
            /*
              if ($elemento == "Numero") {
              $numeroClaFasc = $value[0]["@textNode"];
              }
              if ($elemento == "Anno") {
              $annoFascicolo = $value[0]["@textNode"];
              }
              if ($elemento == "NumeroSenzaClassifica") {
              $numeroFascicolo = $value[0]["@textNode"];
              }
              if ($elemento == "Data") {
              $dataFascicolo = $value[0]["@textNode"]; // gg/mm/aaaa
              }
             * 
             */
        }

        if (!$idFascicolo) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "ID Fascicolo non trovato";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (!$elementi['DocNumber']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "ID Protocollo da fascicolare non trovato";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $ritorno["Status"] = "0";
        $ritorno['datiFascicolo']['codiceFascicolo'] = $idFascicolo;
        //$ritorno["idFascicolo"] = $idFascicolo;
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

    function FascicolaDocumento($elementi) {
        $itaWsFascicolazioneClient = new itaWsFascicolazioneClient();
        $this->setClientConfig($itaWsFascicolazioneClient);
        //
        $param = array();
        $param['IDFascicolo'] = $elementi['dati']['Fascicolazione']['CodiceFascicolo'];
        $param['IDDocumento'] = $elementi['DocNumber'];
        $param['AggiornaClassifica'] = "N";
        $param['Principale'] = "S";
        //
        $ret = $itaWsFascicolazioneClient->ws_FascicolaDocumento($param);
        if (!$ret) {
            if ($itaWsFascicolazioneClient->getFault()) {
                $msg = $itaWsFascicolazioneClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di fascicolazione del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaWsFascicolazioneClient->getError()) {
                $msg = $itaWsFascicolazioneClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di fascicolazione del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsFascicolazioneClient->getResult();
        if ($risultato['Esito'] == "false") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['Errore'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $risultato['Messaggio'];
        $ritorno["RetValue"] = true;
        return $ritorno;

        /*
          [Esito] => true
          [Messaggio] => Operazione eseguita con successo! Il documento con id [213184] ? stato inserito nel fascicolo : N.66/2016 ?PROVA OGGETTO FASCICOLO RICHIESTA N. 2016000584?
         */
    }

    //function LeggiFascicolo($idFascicolo) {
    function LeggiFascicolo($elementi) {
        $itaWsFascicolazioneClient = new itaWsFascicolazioneClient();
        $this->setClientConfig($itaWsFascicolazioneClient);
        //
        $param = array();
        //$param['IDFascicolo'] = $idFascicolo;
        $param['IDFascicolo'] = $elementi['idFascicolo'];
        $param['Anno'] = $elementi['Anno'];
        $param['Numero'] = $elementi['Numero'];
        $ret = $itaWsFascicolazioneClient->ws_LeggiFascicoloString($param);
        if (!$ret) {
            if ($itaWsFascicolazioneClient->getFault()) {
                $msg = $itaWsFascicolazioneClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di lettura del fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaWsFascicolazioneClient->getError()) {
                $msg = $itaWsFascicolazioneClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di lettura del fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsFascicolazioneClient->getResult();

        /*
         * Elaboro Xml d'usicta
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $risultatoUtf8 = utf8_encode($risultato); //Fatto l'encode per descrizone classifica come segue: «COMMERCIO»
        $retXml = $xmlObj->setXmlFromString($risultatoUtf8);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Fascicolo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Fascicolo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($arrayXml['Errore'][0]['@textNode']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Fascicolo: " . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Preparazione array di ritorno dati fascicolo 
         */
        foreach ($arrayXml as $campo => $value) {
            if ($campo == "Anno") {
                $annoFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "Numero") {
                $numeroFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "Oggetto") {
                $oggettoFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "Data") {
                $dataFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "Classifica") {
                $classificaFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "DataDiInserimento") {
                $dataInserimentoFascicolo = $value[0]['@textNode'];
            }
            if ($campo == "DataDiChiusura") {
                $dataChiusuraFascicolo = $value[0]['@textNode'];
            }
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Lettura Fascicolo eseguita con successo";
        $ritorno["RetValue"]['DatiFascicolo'] = array(
            "CodiceFascicolo" => $elementi['idFascicolo'], //$idFascicolo,
            "Anno" => $annoFascicolo,
            "Numero" => $numeroFascicolo,
            "Oggetto" => $oggettoFascicolo,
            "Data" => $dataFascicolo,
            "Classifica" => $classificaFascicolo,
            "DataInserimento" => $dataInserimentoFascicolo,
            "DataChiusura" => $dataChiusuraFascicolo,
        );
        return $ritorno;
    }

    /**
     * 
     * @param string $codiceFascicolo
     * @return array $risultato {
     *     [description]
     *
     *     @option string  "Status" [stringa che vale 0 o 1 in base all'esito positivo o negativo dell'operazione]
     *     @option string  "Message" [messaggio esito operazione]
     *     @option boolean "RetValue" [flag che vale true o false in base all'esito positivo o negativo dell'operazione]
     *     @option boolean "fascicola" [flag che indica se fascicolare oppure no]
     *     @option string  "DataChiusuraFascicolo" [data chiusura del fascicolo]
     * }
     */
    function checkFascicolo($codiceFascicolo) {
        $risultato = $this->LeggiFascicolo($codiceFascicolo);
        $risultato['fascicola'] = false;
        if ($risultato['Status'] == "0") {
            if ($risultato["RetValue"]['DatiFascicolo']['DataChiusura'] == "") {
                $risultato['fascicola'] = true;
            } else {
                $risultato['Message'] = "Fascicolazione non avvenuta. Il fascicolo n. $codiceFascicolo risulta chiuso in data " . $risultato["RetValue"]['DatiFascicolo']['DataChiusura'];
            }
        }
        return $risultato;
    }

    /**
     * 
     * @param array $elementi array contenente tutti i dati di protocollazione
     * @return string codice fascicolo
     */
    function getCodiceFascicolo($elementi) {
        /*
         * Inizializzo il driver della protocollazione
         */
        $proObject = proWSClientFactory::getInstance();
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Uso il LeggiProtocollo per trovare il codice del fascicolo del protocollo antecedente
         */
        $param = array();
        $param['NumeroProtocollo'] = $elementi['dati']['NumeroAntecedente'];
        $param['AnnoProtocollo'] = $elementi['dati']['AnnoAntecedente'];
        $risultato = $proObject->LeggiProtocollo($param);
        if ($risultato['Status'] == "-1") {
            return $risultato;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Lettura codice fascicolo terminata con successo";
        $ritorno["RetValue"] = true;
        $ritorno["CodiceFascicolo"] = $risultato['RetValue']['DatiProtocollo']['NumeroFascicolo'];
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_JIRIDE;
    }

}

?>