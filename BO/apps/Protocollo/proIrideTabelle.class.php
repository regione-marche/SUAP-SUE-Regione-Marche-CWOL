<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    06.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPIride/itaWsTabelleClient.class.php');

//include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFascicolazione.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

class proIrideTabelle extends itaWsTabelleClient {

    /**
     * Libreria di funzioni Generiche e Utility per Fascicolazione con Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($itaWsTabelleClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLEENDPOINT', false);
        $itaWsTabelleClient->setWebservices_uri($uri['CONFIG']);

        $wsdl = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLEWSDL', false);
        $itaWsTabelleClient->setWebservices_wsdl($wsdl['CONFIG']);

        $itaWsTabelleClient->setNameSpaces("sch");

        $utente = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLEUTENTE', false);
        $itaWsTabelleClient->setUtente($utente['CONFIG']);

        $ruolo = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLERUOLO', false);
        $itaWsTabelleClient->setRuolo($ruolo['CONFIG']);

        $CodiceAmministrazione = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLECODICEAMMINISTRAZIONE', false);
        $itaWsTabelleClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);

        $CodiceAOO = $devLib->getEnv_config('IRIDEWSTABELLE', 'codice', 'WSIRIDETABELLECODICEAOO', false);
        $itaWsTabelleClient->setCodiceAOO($CodiceAOO['CONFIG']);
    }

    public function GetElencoTitolario($elementi) {
        $itaWsTabelleClient = new itaWsTabelleClient();
        $this->setClientConfig($itaWsTabelleClient);
        //
        $filtro = "";
        $arrayTitolari = array();
        //
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "ricerca titolari eseguita";
        $ritorno['RetValue'] = true;
        //
        $ret = $itaWsTabelleClient->wm_classifiche($filtro);
        if (!$ret) {
            if ($itaWsTabelleClient->getFault()) {
                $msg = $itaWsTabelleClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di ricerca classifiche:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaWsTabelleClient->getError()) {
                $msg = $itaWsTabelleClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di ricerca classifiche:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsTabelleClient->getResult();

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

        if (isset($arrayXml['cod_err'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "codice errore " . $arrayXml['cod_err'][0]['@textNode'] . " - " . $arrayXml['des_err'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        foreach ($arrayXml['classifiche'] as $key => $classifica) {
            $arrayTitolari[$key]['CODICE'] = $classifica['codice'][0]['@textNode'];
            $arrayTitolari[$key]['DESCRIZIONE'] = $classifica['descrizione'][0]['@textNode'];
        }
        //
        $ritorno['arrayTitolari'] = $arrayTitolari;
        //
        return $ritorno;
    }

    public function GetElencoOperatori($elementi) {
        
//        $itaWsTabelleClient = new itaWsTabelleClient();
//        $this->setClientConfig($itaWsTabelleClient);
//        //
//        $filtro = "";
//        $arrayOperatori = array();
//        //
//        $ritorno = array();
//        $ritorno['Status'] = "0";
//        $ritorno['Message'] = "ricerca operatori eseguita";
//        $ritorno['RetValue'] = true;
//        //
//        $ret = $itaWsTabelleClient->wm_struttura($filtro);
//        if (!$ret) {
//            if ($itaWsTabelleClient->getFault()) {
//                $msg = $itaWsTabelleClient->getFault();
//                $ritorno["Status"] = "-1";
//                $ritorno["Message"] = "Rilevato un fault in fase di ricerca operatori:<br>$msg";
//                $ritorno["RetValue"] = false;
//            } elseif ($itaWsTabelleClient->getError()) {
//                $msg = $itaWsTabelleClient->getError();
//                $ritorno["Status"] = "-1";
//                $ritorno["Message"] = "Rilevato un errore in fase di ricerca operatori:<br>$msg";
//                $ritorno["RetValue"] = false;
//            }
//            return $ritorno;
//        }
//        $risultato = $itaWsTabelleClient->getResult();
//
//        /*
//         * Elaboro Xml d'usicta
//         */
//        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
//        $xmlObj = new itaXML;
//        $risultatoUtf8 = utf8_encode($risultato); //Fatto l'encode per descrizone classifica come segue: «COMMERCIO»
//        $retXml = $xmlObj->setXmlFromString($risultatoUtf8);
//        if (!$retXml) {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }
//        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//        if (!$arrayXml) {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }
//
//        if (isset($arrayXml['cod_err'])) {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "codice errore " . $arrayXml['cod_err'][0]['@textNode'] . " - " . $arrayXml['des_err'][0]['@textNode'];
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }
        
        //
        $arrayOperatori = array();
        $arrayOperatori[0]['CODICE'] = "SERV-SPUNI";
        $arrayOperatori[0]['DESCRIZIONE'] = "SERVIZIO SPORTELLO DELLE IMPRESE";
        $arrayOperatori[1]['CODICE'] = "UO-COMAPRI";
        $arrayOperatori[1]['DESCRIZIONE'] = "U.O. COMMERCIO SU AREE PRIVATE";
        $arrayOperatori[2]['CODICE'] = "UO-FIMA";
        $arrayOperatori[2]['DESCRIZIONE'] = "U.O. FIERE E MANIFESTAZIONI";
        $arrayOperatori[3]['CODICE'] = "UO-PUBESE";
        $arrayOperatori[3]['DESCRIZIONE'] = "U.O. PUBBLICI ESERCIZI E POLIZIA AMMINISTRATIVA";
        $arrayOperatori[4]['CODICE'] = "UO-SANTRAS";
        $arrayOperatori[4]['DESCRIZIONE'] = "U.O. SANITA' TRASPORTI TULPS";
        $arrayOperatori[5]['CODICE'] = "UO-TECAMM";
        $arrayOperatori[5]['DESCRIZIONE'] = "U.O. TECNICO - AMMINISTRATIVA";
        //
        $ritorno['arrayOperatori'] = $arrayOperatori;
        //
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_IRIDE;
    }

}

?>