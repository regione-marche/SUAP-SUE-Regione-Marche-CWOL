<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    21.11.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPJiride/itaWsFascicolazioneClient.class.php');

class itaJIrideFascicolazione {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaJIrideFascicolazione();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($jirideFascicoloClient) {
        $jirideFascicoloClient->setWebservices_uri($this->clientParam['WSJIRIDEFASCICOLIENDPOINT']);
        $jirideFascicoloClient->setWebservices_wsdl($this->clientParam['WSJIRIDEFASCICOLIWSDL']);
        $jirideFascicoloClient->setNameSpaces();
        $jirideFascicoloClient->setUtente($this->clientParam['WSJIRIDEFASCICOLIUTENTE']);
        $jirideFascicoloClient->setRuolo($this->clientParam['WSJIRIDEFASCICOLIRUOLO']);
        $jirideFascicoloClient->setCodiceAmministrazione($this->clientParam['WSJIRIDEFASCICOLICODICEAMMINISTRAZIONE']);
        $jirideFascicoloClient->setCodiceAOO($this->clientParam['WSJIRIDEFASCICOLICODICEAOO']);
    }

    public function CreaFascicolo($elementi) {
        $itaWsFascicolazioneClient = new itaWsFascicolazioneClient();
        $this->setClientConfig($itaWsFascicolazioneClient);

        /*
         * Se c'è l'id fascicolo vado direttamente al metodo Fascicola, altrimenti creo il fascicolo
         */
//        if ($elementi['idFascicolo']) {
//            $idFascicolo = $elementi['idFascicolo'];
//        } else {
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
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
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
        $ritorno["idFascicolo"] = $idFascicolo;
        $ritorno["RetValue"] = true;
        return $ritorno;

//        }
        /*
         * Fascicolo il documento (Inserisco il protocollo nel fascicolo)
         */
//        return $this->FascicolaDocumento($itaWsFascicolazioneClient, $idFascicolo, $elementi['DocNumber']);
    }

    //function FascicolaDocumento($itaWsFascicolazioneClient, $idFascicolo, $idProtocollo) {
    function FascicolaDocumento($idFascicolo, $idProtocollo) {
        $itaWsFascicolazioneClient = new itaWsFascicolazioneClient();
        $this->setClientConfig($itaWsFascicolazioneClient);
        //
        $param = array();
        $param['IDFascicolo'] = $idFascicolo;
        $param['IDDocumento'] = $idProtocollo;
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

}

?>