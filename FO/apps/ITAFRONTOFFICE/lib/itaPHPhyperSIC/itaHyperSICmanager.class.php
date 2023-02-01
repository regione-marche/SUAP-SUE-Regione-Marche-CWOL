<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    17.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPhyperSIC/itaHyperSICClient.class.php');

class itaHyperSICmanager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaHyperSICmanager();
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
     * Libreria di funzioni Generiche e Utility per Protocollo HyperSic
     */
    private function setClientConfig($hypersicClient) {
        $hypersicClient->setWebservices_uri($this->clientParam['WSHYPERSICENDPOINT']);
        $hypersicClient->setWebservices_wsdl($this->clientParam['WSHYPERSICWSDL']);
        $hypersicClient->setNameSpaces();
        $hypersicClient->setNamespace($this->clientParam['WSHYPERSICNAMESPACE']);
        $hypersicClient->setUsername($this->clientParam['WSHYPERSICUSERNAME']);
        $hypersicClient->setPassword($this->clientParam['WSHYPERSICPASSWORD']);
        $hypersicClient->setTimeout($this->clientParam['WSHYPERSICTIMEOUT']);
    }

    /**
     * 
     * @param type $param
     * @return type
     */
    function GetProtocolloGenerale($param) {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        $ret = $hypersicClient->ws_GetProtocolloGenerale($param);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();
        //gestione del messaggio d'errore
        if (isset($risultato['diffgram']['errori'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca protocollo: <br>" . $risultato['diffgram']['errori'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI COMPLETI DI LETTURA DEL PROTOCOLLO
            $ritorno["RetValue"]['Dati'] = $risultato;
            //DATI PER SALVATAGGIO NEI METADATI
            /*
             * si suppone una ricerca per un singolo protocollo, per cui la struttura della risposta è ['diffgram']['protocolli']['protocollo']['codice']
             * per risposte multiple la struttura è 
             * ['diffgram']['protocolli']['protocollo'][0]['codice']
             * ['diffgram']['protocolli']['protocollo'][1]['codice']
             * la 'data' è nel formato 02/01/2014 0.00.00
             */
            $ritorno["RetValue"]['DatiProtocollazione'] = array(
                'TipoProtocollo' => array('value' => 'HyperSIC', 'status' => true, 'msg' => 'OK'),
                'proNum' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['numero'], 'status' => true, 'msg' => ''),
                'codice' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['codice'], 'status' => true, 'msg' => ''),
                'data' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['data'], 'status' => true, 'msg' => ''),
                'oggetto' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['oggetto'], 'status' => true, 'msg' => ''),
                'classificazione' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['classificazione'], 'status' => true, 'msg' => ''),
                'anno_fascicolo' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['anno_fascicolo'], 'status' => true, 'msg' => ''),
                'tipologia' => array('value' => $risultato['diffgram']['protocolli']['protocollo']['tipologia'], 'status' => true, 'msg' => ''),
            );
            //DATI NORMALIZZATI PER RICERCA PROTOCOLLO
            $Allegati = $risultato['diffgram']['protocolli']['allegato'];
            if (!$Allegati[0]) {
                $Allegati = array($Allegati);
            }
            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['nome'];
            }
            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'HyperSIC',
                'NumeroProtocollo' => $risultato['diffgram']['protocolli']['protocollo']['numero'],
                'Data' => $risultato['diffgram']['protocolli']['protocollo']['data'],
                'DocNumber' => $risultato['diffgram']['protocolli']['protocollo']['codice'],
                'Segnatura' => '',
                'Anno' => substr($risultato['diffgram']['protocolli']['protocollo']['data'], 6, 4),
                'Classifica' => $risultato['diffgram']['protocolli']['protocollo']['classificazione'],
                'Oggetto' => $risultato['diffgram']['protocolli']['protocollo']['oggetto'],
                'DocumentiAllegati' => $DocumentiAllegati
            );
        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param
     * @return boolean
     */
    function GetCorrispondente($param) {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        $ret = $hypersicClient->ws_GetCorrispondente($param);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();
        /*
         * NORMALIZZARE -> torna string XML
         */
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($risultato);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore: lettura dati anagrafici del soggetto " . $param['descrizione'] . " non riuscita";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        $arrayNorm = array();
        foreach ($arrayXml as $chiave => $value) {
            $arrayNorm[$chiave] = $value[0]['@textNode'];
        }

        //gestione del messaggio d'errore
        if (isset($risultato['diffgram']['errori'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca anagrafica: <br>" . $risultato['Errore'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI COMPLETI DI LETTURA DEL NOMINATIVO
            $ritorno["RetValue"]['Dati'] = $arrayNorm;
        }
        return $ritorno;
    }

    public function InserisciProtocollo($elementi, $origine = "A") {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        //
        $dati = $elementi['dati'];
        $param = array();
        $protocollo = array();
        $mittente = array();
        $destinatari = array();

        $param['protocollo']['tipologia'] = $origine;
        $param['protocollo']['oggetto'] = $dati['Oggetto'];
        $param['protocollo']['classificazione'] = $dati['Classificazione'];
        $param['protocollo']['fascicolo'] = $dati['fascicoli'];
        $param['protocollo']['anno_fascicolo'] = $dati['fascicoli'] != "" ? date('Y') : "";
        $param['protocollo']['riservato'] = ""; //quale deve essere settato come livello di riservatezza?
        $param['protocollo']['codice_procedimento'] = ""; //??
        $param['protocollo']['numero_protocollo_precedente'] = "";
        $param['protocollo']['anno_protocollo_precedente'] = "";
        $param['protocollo']['numero_protocollo_riferimento'] = "";
        $param['protocollo']['data_protocollo_riferimento'] = "";
        $param['protocollo']['fogli_protocollo_riferimento'] = "";
        $param['protocollo']['annotazione'] = ""; //si potrebbe inserire una dicitura tipo "Protocollo tramite Web Service"

        /*
         * MITTENTE
         */
        $param['mittente'] = array();
        $param['mittente']['codice'] = "";
        $param['mittente']['descrizione'] = $dati['MittDest']['Denominazione'];
        $param['mittente']['indirizzo'] = $dati['MittDest']['Indirizzo'];
        $param['mittente']['cap'] = $dati['MittDest']['CAP'];
        $param['mittente']['codice_comune'] = "";
        $param['mittente']['descrizione_comune'] = $dati['MittDest']['Citta'];

        /*
         * DESTINATARIO
         */
        $destinatari = array();
        foreach ($elementi['destinatari'] as $destinatario) {
            $dest_rec = array();
            $dest_rec['codice'] = $destinatario['CodiceDestinatario'];
            $dest_rec['descrizione'] = $destinatario['Denominazione'];
            $dest_rec['indirizzo'] = $destinatario['Indirizzo'];
            $dest_rec['cap'] = $destinatario['CAP'];
            $dest_rec['codice_comune'] = "";
            $dest_rec['descrizione_comune'] = $destinatario['Citta'];
            $dest_rec['per_conoscenza'] = "";
            $dest_rec['codice_spedizione'] = "";
            $dest_rec['codice_ufficio'] = $destinatario['CodiceDestinatario']; //$elementi['dati']['InCaricoA'];
            $dest_rec['descrizione_ufficio'] = $destinatario['Denominazione']; 
            $dest_rec['codice_processo'] = ""; //??
            $destinatari[] = $dest_rec;
        }
        $param['destinatario'] = $destinatari;

        $dati = array();
        $dati['dsDati']['protocolli'] = $param;
        $dati['userName'] = ''; //potrei mettere l'utente preso dai parametri

        /*
         * QUI ESEGUIRE PROTOCOLLAZIONE E QUINDI ANDARE A LEGGERE LA RISPOSTA PER PRENDERE IL codice
         */
        $ret = $hypersicClient->ws_InsertProtocolloGenerale($dati);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();

        $retProtocollo = $risultato['diffgram']['protocolli']['protocollo'];

        /*
         * Verifico e inserisco Inserisco il corrispondente
         */
        $msgErrCorr = "";
        if ($retProtocollo['numero_protocollo']) {
            $retInsertCorrispondente = $this->insertCorrispondente($hypersicClient, $elementi['dati']['MittDest']);
            if ($retInsertCorrispondente['Status'] == "-1") {
                $msgErrCorr = "Corrispondente non inserito: " . $retInsertCorrispondente['Message'];
            }
        }

        /*
         * Elaboro Xml d'usicta
         */
        $Data = str_replace("/", "-", $retProtocollo['data_protocollo']);
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Protocollazione avvenuta con successo!";
        $ritorno["errStringProt"] = $msgErrCorr;
        $ritorno["RetValue"] = array(
            'DatiProtocollazione' => array(
                'TipoProtocollo' => array('value' => 'HyperSIC', 'status' => true, 'msg' => ""),
                'proNum' => array('value' => $retProtocollo['numero_protocollo'], 'status' => true, 'msg' => ''),
                'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                'DocNumber' => array('value' => $retProtocollo['codice'], 'status' => true, 'msg' => ''),
                'Anno' => array('value' => date('Y'), 'status' => true, 'msg' => '')
            )
        );



        /*
         * ALLEGATI
         * posso allegare un documento alla volta.
         */
        $err_allegati = array();
        $err_n = 0;
        if (isset($elementi['dati']['DocumentoPrincipale']) && isset($elementi['dati']['DocumentoPrincipale']['Nome'])) {
            $datiAllegato = array();
            $datiAllegato['codice'] = $retProtocollo['codice'];
            $datiAllegato['fileBytes'] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $datiAllegato['fileName'] = utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']);
            $datiAllegato['userName'] = '';
            $ret = $hypersicClient->ws_InsertAllegatoProtocolloGenerale($datiAllegato);
            if (!$ret) {
                if ($hypersicClient->getFault()) {
                    $msg = $hypersicClient->getFault();
                } elseif ($hypersicClient->getError()) {
                    $msg = $hypersicClient->getError();
                }
                $err_allegati[$err_n] = $elementi['dati']['DocumentoPrincipale']['Nome'];
                $err_n++;
            }
            //NON CI SONO VALORI DI RITORNO DA QUESTO METODO!!!! COME SI FA A SAPERE SE E' ANDATO A BUON FINE?
        }

        /*
         * ALTRI ALLEGATI
         */
        $arrayDoc = $elementi['dati']['DocumentiAllegati'];
        foreach ($arrayDoc as $documento) {
            $datiAllegato = array();
            $datiAllegato['codice'] = $retProtocollo['codice'];
            $datiAllegato['fileBytes'] = $documento['Documento']['Stream'];
            $datiAllegato['fileName'] = utf8_encode($documento['Documento']['Nome']);
            $datiAllegato['userName'] = '';

            $ret = $hypersicClient->ws_InsertAllegatoProtocolloGenerale($datiAllegato);
            if (!$ret) {
                if ($hypersicClient->getFault()) {
                    $msg = $hypersicClient->getFault();
                } elseif ($hypersicClient->getError()) {
                    $msg = $hypersicClient->getError();
                }
                $err_allegati[$err_n] = $msg . ": " . $documento['Documento']['Nome'];
                $err_n++;
            }
        }
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '\n';
            }
            $ritorno['strNoProt'] = $err_str . "<br>" . $hypersicClient->getResult();
        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param array('NumeroProtocollo', 'AnnoProtocollo', 'Allegati')
     */
    public function InsertAllegatoProtocolloGenerale($param) {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Documenti allegati con successo!";
        $ritorno["RetValue"] = true;
        if (!$param['arrayDoc']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Nessun documento da allegare";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $err_allegati = array();
        $err_n = 0;
        foreach ($param['arrayDoc']['Allegati'] as $documento) {
            $datiAllegato = array();
            //qui chiamata a GetProtocollo - da prendere dai metadati i parametri per la chiamata?

            $datiAllegato['codice'] = $param['DocNumber'];
            $datiAllegato['fileBytes'] = $documento['Documento']['Stream'];
            $datiAllegato['fileName'] = utf8_encode($documento['Documento']['Nome']);
            $datiAllegato['userName'] = '';

            $ret = $hypersicClient->ws_InsertAllegatoProtocolloGenerale($datiAllegato);
            if (!$ret) {
                if ($hypersicClient->getFault()) {
                    $msg = $hypersicClient->getFault();
                } elseif ($hypersicClient->getError()) {
                    $msg = $hypersicClient->getError();
                }
                $err_allegati[$err_n] = $documento['Documento']['Nome'];
                $err_n++;
            }
        }
        //gestione messaggio in caso di errori
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '\n';
            }
        }
        $ritorno['errString'] = $err_str;
        return $ritorno;
    }

    public function GetComune($param) {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        $ret = $hypersicClient->ws_GetComune($param);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();

        //gestione del messaggio d'errore
        if (isset($risultato['diffgram']['errori'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca comune: <br>" . $risultato['Errore'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI COMPLETI DI LETTURA DEL NOMINATIVO
            $ritorno["RetValue"]['Dati'] = $risultato['diffgram'];
        }
        return $ritorno;
    }

    public function GetFascicolo($param) {
        $hypersicClient = new itaHyperSICClient();
        $this->setClientConfig($hypersicClient);
        $ret = $hypersicClient->ws_GetFascicolo($param);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();

        //gestione del messaggio d'errore
        if (isset($risultato['diffgram']['errori'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca fascicolo: <br>" . $risultato['Errore'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI COMPLETI DI LETTURA DEL NOMINATIVO
            $ritorno["RetValue"]['Dati'] = $risultato['diffgram'];
        }
        return $ritorno;
    }

    private function insertCorrispondente($hypersicClient, $corrispondente) {
        $paramSearch = array();
        //$paramSearch['codice'] = $corrispondente['codice'];
        $paramSearch['codiceFiscale'] = $corrispondente['CF'];
        $ret = $hypersicClient->ws_GetCorrispondente($paramSearch);
        if (!$ret) {
            if ($hypersicClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault in fase di check corrispondente: <br>" . print_r($hypersicClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($hypersicClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error in fase di check corrispondente: <br>" . print_r($hypersicClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $hypersicClient->getResult();

        /*
         * Se non presente aggiungo il corrispondente
         */
        $message = "Corrispondente Trovato.";
        if (!isset($risultato['diffgram']['corrispondenti']['corrispondente'])) {
            $message = "Corrispondenti Inseriti.";
            $param = array();
            $param['codiceFiscale'] = $corrispondente['CF'];
            $param['descrizione'] = $corrispondente['Denominazione'];
            $param['indirizzo'] = $corrispondente['Indirizzo'];
            $param['cap'] = $corrispondente['CAP'];
            $param['codiceComune'] = $corrispondente['codiceComune'];
            $param['email'] = $corrispondente['Email'];
            $param['telefono'] = $corrispondente['Telefono'];
            $param['fax'] = $corrispondente['fax'];
            $param['userName'] = $corrispondente['userName'];
            //
            $ret = $hypersicClient->ws_InsertCorrispondente($param);
            if (!$ret) {
                if ($hypersicClient->getFault()) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Fault in fase di inserimento corrispondente: <br>" . print_r($hypersicClient->getFault(), true);
                    $ritorno["RetValue"] = false;
                } elseif ($hypersicClient->getError()) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Error in fase di inserimento corrispondente: <br>" . print_r($hypersicClient->getError(), true);
                    $ritorno["RetValue"] = false;
                }
                return $ritorno;
            }
            $ret = $hypersicClient->getResult();
            if ($ret['diffgram']['corrispondenti']['corrispondente']['codice'] == '') {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Errore nell'inserire il corrispondente " . $param['descrizione'];
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $message;
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

}

?>