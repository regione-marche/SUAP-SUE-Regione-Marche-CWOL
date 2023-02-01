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
include_once(ITA_LIB_PATH . '/itaPHPIride/itaWsRicercheClient.class.php');

//include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFascicolazione.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

class proIrideRicerche extends itaWsRicercheClient {

    /**
     * Libreria di funzioni Generiche e Utility per Fascicolazione con Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($itaWsPostaClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHEENDPOINT', false);
        $itaWsPostaClient->setWebservices_uri($uri['CONFIG']);

        $wsdl = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHEWSDL', false);
        $itaWsPostaClient->setWebservices_wsdl($wsdl['CONFIG']);

        $itaWsPostaClient->setNameSpaces();

        $utente = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHEUTENTE', false);
        $itaWsPostaClient->setUtente($utente['CONFIG']);

        $ruolo = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHERUOLO', false);
        $itaWsPostaClient->setRuolo($ruolo['CONFIG']);

        $CodiceAmministrazione = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHECODICEAMMINISTRAZIONE', false);
        $itaWsPostaClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);

        $CodiceAOO = $devLib->getEnv_config('IRIDEWSRICERCHE', 'codice', 'WSIRIDERICERCHECODICEAOO', false);
        $itaWsPostaClient->setCodiceAOO($CodiceAOO['CONFIG']);
    }

    public function GetElencoFascicoli($elementi) {
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "ricerca fascicoli eseguita";
        $ritorno['RetValue'] = true;
        //
        $arrayFascicoli = array();
        $arrayFascicoli[0]['ID'] = "-14721";
        $arrayFascicoli[0]['NUMERO'] = "VI.3/000001";
        $arrayFascicoli[0]['ANNO'] = "2018";
        $arrayFascicoli[0]['DATA'] = "20180309";
        $arrayFascicoli[0]['OGGETTO'] = "CCCCCCCCC";
        $arrayFascicoli[0]['CLASSIFICA'] = "VI.3";
        $arrayFascicoli[1]['ID'] = "-14722";
        $arrayFascicoli[1]['NUMERO'] = "VI.3/000002";
        $arrayFascicoli[1]['ANNO'] = "2018";
        $arrayFascicoli[1]['DATA'] = "20180309";
        $arrayFascicoli[1]['OGGETTO'] = "PROVA CREAZIONE NUOVO FASCICOOLO";
        $arrayFascicoli[1]['CLASSIFICA'] = "VI.3";
        //
        $ritorno['arrayFascicoli'] = $arrayFascicoli;
        //
        return $ritorno;
    }

    public function CercaFascicolo($elementi) {
        $itaWsRicercheClient = new itaWsRicercheClient();
        $this->setClientConfig($itaWsRicercheClient);

        /*
         * Se c'è l'id fascicolo vado direttamente al metodo Fascicola, altrimenti creo il fascicolo
         */
        $param = array();
//        $param['AnnoFasc'] = "2017";
//        $param['NumeroFasc'] = "1";
        $param['Anno'] = "2017";
        $param['Numero'] = "1";
        
        $datiUtenti = array();
        $datiUtenti[0]['NomeTabella'] = "";
        $datiUtenti[0]['NomeCampo'] = "";
        $datiUtenti[0]['ValoreCampo'] = "";
        $datiUtenti[1]['NomeTabella'] = "";
        $datiUtenti[1]['NomeCampo'] = "";
        $datiUtenti[1]['ValoreCampo'] = "";
        $datiUtenti[2]['NomeTabella'] = "";
        $datiUtenti[2]['NomeCampo'] = "";
        $datiUtenti[2]['ValoreCampo'] = "";
        
        $param['datiUtenti'] = $datiUtenti;
        //
        $ret = $itaWsRicercheClient->ws_RicercaDocumentiString($param);
        if (!$ret) {
            if ($itaWsRicercheClient->getFault()) {
                $msg = $itaWsRicercheClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaWsRicercheClient->getError()) {
                $msg = $itaWsRicercheClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsRicercheClient->getResult();

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

//        if (!$elementi['DocNumber']) {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "ID Protocollo da fascicolare non trovato";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }

        $ritorno["Status"] = "0";
        //$ritorno["idFascicolo"] = $idFascicolo;
        $ritorno['datiFascicolo']['codiceFascicolo'] = $idFascicolo;
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_IRIDE;
    }

}

?>