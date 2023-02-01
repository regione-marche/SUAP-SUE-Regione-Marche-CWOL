<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    30.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPSici/itaSiciClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPSici/itaMittenteDestinatario.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFascicolazione.class.php';

class proSiciFascicolazione extends proWsClientFascicolazione {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Sici Studio K
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($siciClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSENDPOINT', false);
        $siciClient->setUri($uri['CONFIG']);
        $wsdl = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSWSDL', false);
        $siciClient->setWsdl($wsdl['CONFIG']);
        $applicativo = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSAPPLICATIVO', false);
        $siciClient->setApplicativo($applicativo['CONFIG']);
        $ente = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSENTE', false);
        $siciClient->setEnte($ente['CONFIG']);
        $password = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSPASSWORD', false);
        $siciClient->setPassword($password['CONFIG']);
        $codiceAmm = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODICEAMM', false);
        $siciClient->setCodiceAmm($codiceAmm['CONFIG']);
        $codiceAOO = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODICEAOO', false);
        $siciClient->setCodiceAOO($codiceAOO['CONFIG']);
        $utente = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODUTE', false);
        $siciClient->setUtente($utente['CONFIG']);
        $nameSpaces = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSNAMESPACES', false);
        $siciClient->setNameSpaces($nameSpaces['CONFIG']);
        $namespace = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSNAMESPACE', false);
        $siciClient->setNameSpace($namespace['CONFIG']);
    }

    public function CreaFascicolo($elementi) {
        $siciClient = new itaSiciClient();
        $this->setClientConfig($siciClient);
        //
        $param = array();
        $param['Anno'] = date("Y");
        $classificazione = $elementi['dati']['Classificazione'];
        if ($classificazione) {
            $arrClassifica = explode(".", $classificazione);
            if (!$arrClassifica) {
                Out::msgStop("Errore", "Classificazione formalmente errata. La procedura sarà interrotta");
                return;
            }
        } else {
            Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
            return;
        }
        $param['Categoria'] = $arrClassifica[0];
        $param['Classe'] = $arrClassifica[1];
        $param['Oggetto'] = $elementi['dati']['Fascicolazione']['Oggetto'];

        //
        $ret = $siciClient->ws_CreaFascicolo($param);
        if (!$ret) {
            if ($siciClient->getFault()) {
                $msg = $siciClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di creazione del fascicolo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($siciClient->getError()) {
                $msg = $siciClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di creazione del fascicolo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $siciClient->getResult();
        if ($risultato['Result'] == "true") {
            //
            //Elaboro Xml d'usicta
            //
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString(html_entity_decode($risultato['XML_RETURN']));
            if (!$retXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "File XML Crea Fascicolo: Impossibile leggere il testo nell'xml";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            if (!$arrayXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Lettura XML Crea Fascicolo: Impossibile estrarre i dati";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }


            //$idFascicolo = $arrayXml['Fascicolo'][0]['NumeroFascicolo'][0]['@textNode'];
            foreach ($arrayXml['Fascicolo'][0] as $elemento => $value) {
                if ($elemento == "NumeroFascicolo") {
                    $idFascicolo = $value[0]["@textNode"];
                }
                if ($elemento == "AnnoFascicolo") {
                    $annoFascicolo = $value[0]["@textNode"];
                }
            }

            if (!$idFascicolo) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "ID Fascicolo non trovato";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            $ritorno["Status"] = "0";
            $ritorno['datiFascicolo']['codiceFascicolo'] = $idFascicolo;
            $ritorno['datiFascicolo']['annoFascicolo'] = $annoFascicolo;
//            $ritorno["idFascicolo"] = $idFascicolo;
//            $ritorno["annoFascicolo"] = $annoFascicolo;
            $ritorno["RetValue"] = true;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['MSG_RETURN'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        return $ritorno;
    }

    public function FascicolaDocumento($elementi) {
        $siciClient = new itaSiciClient();
        $this->setClientConfig($siciClient);
        //
        $param = array();
        $param['AnnoProt'] = $elementi['dati']['Fascicolazione']['Anno'];
        $param['NumProt'] = $elementi['dati']['Fascicolazione']['Numero'];
        $classificazione = $elementi['dati']['Classificazione'];
        if ($classificazione) {
            $arrClassifica = explode(".", $classificazione);
            if (!$arrClassifica) {
                Out::msgStop("Errore", "Classificazione formalmente errata. La procedura sarà interrotta");
                return;
            }
        } else {
            Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
            return;
        }
        $param['Categoria'] = $arrClassifica[0];
        $param['Classe'] = $arrClassifica[1];

        /*
         * Se c'è già un fascicolo preimpostato nell'anagrafica dei procedimenti, deve avere il formato NNNxx/AAAA e poi viene separato con l'explode
         * Se i valori mi vengono dal CreaFascicolo li ho già separati.
         */
        if (strpos($elementi['dati']['Fascicolazione']['CodiceFascicolo'], "/") !== false) {
            $arrFascicolo = explode("/", $elementi['dati']['Fascicolazione']['CodiceFascicolo']);
            $numeroFascicolo = $arrFascicolo[0];
            $annoFascicolo = $arrFascicolo[1];
        } else {
            $annoFascicolo = $elementi['dati']['Fascicolazione']['AnnoFascicolo'];
            $numeroFascicolo = $elementi['dati']['Fascicolazione']['CodiceFascicolo'];
        }


        $param['Anno'] = $annoFascicolo;
        $param['Fascicolo'] = $numeroFascicolo;
        //
        $ret = $siciClient->ws_AssegnaFascicolo($param);
        if (!$ret) {
            if ($siciClient->getFault()) {
                $msg = $siciClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di fascicolazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($siciClient->getError()) {
                $msg = $siciClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di fascicolazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $siciClient->getResult();
        if ($risultato['Result'] == "true") {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = $risultato['MSG_RETURN'];
            $ritorno["RetValue"] = true;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['MSG_RETURN'];
            $ritorno["RetValue"] = false;
        }
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
     * }
     */
    function checkFascicolo($codiceFascicolo) {
        /*
         * In attesa di sapere se esiste un metodo per leggere il fasciolo, fascicolo sempre
         */
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "";
        $ritorno['fascicola'] = true;
        return $ritorno;
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
        $ritorno["AnnoFascicolo"] = $risultato['RetValue']['DatiProtocollo']['AnnoFascicolo'];
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_SICI;
    }

}

?>