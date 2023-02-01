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
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proSici extends proWsClient {

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

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo")
     * @return type
     */
    function LeggiProtocollo($param) {
        $siciClient = new itaSiciClient();
        $this->setClientConfig($siciClient);
        //

        $ret = $siciClient->ws_LeggiProtocollo($param);
        if (!$ret) {
            if ($siciClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($siciClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($siciClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($siciClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $siciClient->getResult();

        //gestione del messaggio d'errore
        if ($risultato['Result'] == "true") {
            //
            //Elaboro Xml d'usicta
            //
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($risultato['XML_RETURN']);
            //$retXml = $xmlObj->setXmlFromString(html_entity_decode($risultato['XML_RETURN']));
            if (!$retXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            if (!$arrayXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Lettura XML Inserisci Protocollo: Impossibile estrarre i dati";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            //DATI PER SALVATAGGIO NEI METADATI
            $ritorno["Status"] = "0";
            $ritorno["Message"] = $risultato['MSG_RETURN'];
            $ritorno["RetValue"]['DatiProtocollazione'] = array(
                'TipoProtocollo' => array('value' => 'Sici', 'status' => true, 'msg' => $risultato['MSG_RETURN']),
                'proNum' => array('value' => $arrayXml['SegnaturaSK'][0]['NumeroRegistrazione'][0]['@textNode'], 'status' => true, 'msg' => ''),
                'Data' => array('value' => $arrayXml['SegnaturaSK'][0]['DataRegistrazione'][0]['@textNode'], 'status' => true, 'msg' => ''), //2017-06-05
            );
            //DATI NORMALIZZATI PER RICERCA PROTOCOLLO
            $Allegati = $arrayXml['SegnaturaSK'][0]['Documenti'][0]['Documento'];
            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['@attributes']['nome'];
            }
            $categoria = $arrayXml['SegnaturaSK'][0]['Classificazioni'][0]['Classificazione'][0]['Categoria'][0]['@textNode'];
            $classe = $arrayXml['SegnaturaSK'][0]['Classificazioni'][0]['Classificazione'][0]['Classe'][0]['@textNode'];
            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'Sici',
                'NumeroProtocollo' => $arrayXml['SegnaturaSK'][0]['NumeroRegistrazione'][0]['@textNode'],
                'Data' => $arrayXml['SegnaturaSK'][0]['DataRegistrazione'][0]['@textNode'],
                'Anno' => substr($arrayXml['SegnaturaSK'][0]['DataRegistrazione'][0]['@textNode'], 0, 4),
                'Classifica' => $categoria . "." . $classe,
                'Oggetto' => $arrayXml['SegnaturaSK'][0]['Oggetto'][0]['@textNode'],
                'NumeroFascicolo' => $arrayXml['SegnaturaSK'][0]['Classificazioni'][0]['Classificazione'][0]['NumeroFascicolo'][0]['@textNode'],
                'AnnoFascicolo' => $arrayXml['SegnaturaSK'][0]['Classificazioni'][0]['Classificazione'][0]['AnnoFascicolo'][0]['@textNode'],
                'DocumentiAllegati' => $DocumentiAllegati
            );
        }
        return $ritorno;
    }

    //@TODO:rendere privato
    public function InserisciProtocollo($elementi, $origine = "A") {
        $siciClient = new itaSiciClient();
        $this->setClientConfig($siciClient);
        //
        $param['Accompagnatoria'] = "";

        /*
         * Se un passo in arrivo o in partenza setto il numero protocollo antecedente
         */
        if ($elementi['dati']['NumeroAntecedente']) {
            $param['NumeroProtocolloDiProvenienza'] = $elementi['dati']['NumeroAntecedente'];
            $param['DataRegistrazioneProtocolloDiProvenienza'] = $elementi['dati']['MetaDati']['DatiProtocollazione']['Data']['value'];
        }

        if ($origine == "A") {
            $param['Flusso'] = "E";
        } else {
            $param['Flusso'] = "U";
            $param['eMail'] = "N"; //Capire cos'è e decidere come prenderlo
        }

        $param['Data'] = date("d/m/Y");
        //classificazione
        $classificazione = $elementi['dati']['Classificazione'];
        if ($classificazione) {
            $arrClassifica = explode(".", $classificazione);
            if (!$arrClassifica) {
                Out::msgStop("Errore", "Classificazione formalmente errata. La procedura sarà interrotta");
                return;
            }
            $param['Classifica'][] = array(
                'Categoria' => $arrClassifica[0],
                'Classe' => $arrClassifica[1],
            );
        } else {
            Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
            return;
        }

        /*
         * Assegnazioni
         */
        $param['Assegnazioni'][] = array(
            'AssegnatoA' => $elementi['dati']['InCaricoA'],
            'AssegnatoDa' => "",
        );

        /*
         * Oggetto del protocollo
         */
        $param['Oggetto'] = utf8_encode($elementi['dati']['Oggetto']);


        /*
         * MITTENTI DESTINATARI
         */
        $MittDestSource_tab = array();
        switch ($origine) {
            case "A":
                $MittDestSource_tab[] = $elementi['dati']['MittDest'];
                break;
            case "P":
                $MittDestSource_tab = $elementi['dati']['destinatari'];
                break;
        }

        $MittDest = array();
        foreach ($MittDestSource_tab as $MittDestSource_rec) {
            if ($MittDestSource_rec['Denominazione']) {
                $MD = new itaMittenteDestinatario();
                $Denominazione = utf8_encode($MittDestSource_rec['Denominazione']);
                $Indirizzo = utf8_encode($MittDestSource_rec['Desind']);
                if ($Indirizzo == "") {
                    $Indirizzo = utf8_encode($MittDestSource_rec['Indirizzo']);
                }
                $cap = $MittDestSource_rec['CAP'];
                $citta = $MittDestSource_rec['Citta'];
                $prov = $MittDestSource_rec['Provincia'];
                $email = $MittDestSource_rec['Email'];
                $cf = $MittDestSource_rec['CF'];
                //
                if ($email) {
                    $MD->setEmail($email);
                }

                if ($elementi['dati']['DenomComune']) {
                    $MD->setDenominazione($elementi['dati']['DenomComune']);
                }

                $MD->setCodiceAmministrazione($siciClient->getCodiceAmm());
                $MD->setCodiceAOO($siciClient->getCodiceAOO());

                if ($Denominazione) {
                    $MD->setCognomeNome($Denominazione);
                }
                $MD->setCodiceFiscale($cf);
                if ($Indirizzo) {
                    //$MD->setIndirizzo($Indirizzo);
                    $posIspazio = strpos($Indirizzo, " ");
                    $dug = substr($Indirizzo, 0, $posIspazio);
                    $toponimo = substr($Indirizzo, $posIspazio + 1);
                    $MD->setDug($dug);
                    $MD->setToponimo($toponimo);
                    $MD->setCivico($MittDestSource_rec['Civico']);
                }
                if ($citta) {
                    $MD->setComune($citta);
                }
                if ($cap) {
                    $MD->setCap($cap);
                }
                if ($prov) {
                    $MD->setProvincia($prov);
                }

                //$MD->setNazione("IT");
                $MD->setFlusso($param['Flusso']);

                $MittDest[] = $MD;
            }
        }

        if ($MittDest) {
            $param['MittentiDestinatari'] = $MittDest;
        }
        if ($origine == "P" && !$MittDest) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Destinatari non presenti.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * ALLEGATI
         */
        $Allegati = array();
        if (isset($elementi['dati']['DocumentoPrincipale'])) {
            $allegato = $this->getArrayAllegato($elementi['dati']['DocumentoPrincipale'], $siciClient, "S");
            $Allegati[] = $allegato;
        }

        if (isset($elementi['dati']['DocumentiAllegati'])) {
            foreach ($elementi['dati']['DocumentiAllegati'] as $alle) {
                $allegato = $this->getArrayAllegato($alle, $siciClient, "N");
                $Allegati[] = $allegato;
            }
        }

        if (count($Allegati) > 0) {
            $param['NumeroAllegati'] = count($Allegati);
            $param['Allegati'] = $Allegati;
        }

        $ret = $siciClient->ws_InserisciProtocollo($param);
        if (!$ret) {
            if ($siciClient->getFault()) {
                $msg = $siciClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($siciClient->getError()) {
                $msg = $siciClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
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
                $ritorno["Message"] = "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            if (!$arrayXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Lettura XML Inserisci Protocollo: Impossibile estrarre i dati";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Sici', 'status' => true, 'msg' => $risultato['MSG_RETURN']),
                    'proNum' => array('value' => $arrayXml['NUMERO'][0]['@textNode'], 'status' => true, 'msg' => ''),
                    'Data' => array('value' => date("Y-m-d"), 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $arrayXml['ANNO'][0]['@textNode'], 'status' => true, 'msg' => '')
                )
            );
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['MSG_RETURN'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        return $ritorno;
    }

    function getArrayAllegato($sorgFile, $siciClient, $principale) {
        $descrizione = $sorgFile['Descrizione'];
        if ($principale != "S") {
            $sorgFile = $sorgFile['Documento'];
        }

        $loadAllegato = array();
        $loadAllegato['nome'] = utf8_encode($sorgFile['Nome']);
        $loadAllegato['stream'] = $sorgFile['Stream'];
        $ret = $siciClient->ws_LoadFile($loadAllegato);
        if (!$ret) {
            if ($siciClient->getFault()) {
                $msg = $siciClient->getFault();
            } elseif ($siciClient->getError()) {
                $msg = $siciClient->getError();
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $msg;
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $risultato = $siciClient->getResult();
            if ($risultato['Result'] != "true") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = $risultato['MSG_RETURN'];
                $ritorno["RetValue"] = false;
                return $ritorno;
            } else {
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
                $xmlObj = new itaXML;
                $retXml = $xmlObj->setXmlFromString(html_entity_decode($risultato['XML_RETURN']));
                if (!$retXml) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                if (!$arrayXml) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Lettura XML Inserisci Protocollo: Impossibile estrarre i dati";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
        }


        $allegato = array();
        $allegato['Commento'] = utf8_encode($descrizione);
        $allegato['Attributi'] = array("nome" => utf8_encode($sorgFile['Nome']), "principale" => $principale, "telematico" => "N", "gdoc_id" => $arrayXml['GDOC_ID'][0]['@textNode'], "gdoc_appl" => "PRO", "gdoc_vers" => "1");
        return $allegato;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_SICI;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->InserisciProtocollo($elementi, "P");
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->InserisciProtocollo($elementi, "A");
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->InserisciDocumentoEAnagrafiche($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}

?>