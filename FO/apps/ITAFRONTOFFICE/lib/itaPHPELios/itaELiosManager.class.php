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
 * @version    08.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPELios/itaELiosClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPELios/itaELiosDizionarioClient.class.php');

class itaELiosManager {

    private $clientParam;
    private $arrayError = array(
        "-101" => "Fascicolo specificato non esistente",
        "-107" => "Documento principale non presente nell?XML di profilazione",
        "-102" => "Id del documento non esistente (documento specificato non inserito)",
        "-555" => "Codice Titolario non presente su database",
        "-108" => "In un flusso in entrata il codice del mittente è legato a più persone nell?anagrafica",
        "-109" => "Errore di scrittura nel database per l?inserimento di un nuovo mittente nei flussi in entrata",
        "-110" => "In un flusso in entrata il codice destinatario è legato a più uffici",
        "-111" => "In un flusso in entrata il codice destinatario non è legato ad un ufficio presente nel database",
        "-112" => "In un flusso in uscita il codice del destinatario è legato a più persone nell?anagrafica",
        "-113" => "Errore di scrittura nel database per l?inserimento di un nuovo destinatario nei flussi in uscita",
        "-114" => "In un flusso in uscita il codice mittente è legato a più uffici",
        "-115" => "In un flusso in uscita il codice mittente non è legato ad un ufficio presente nel database",
        "-116" => "La tipologia di invio della pratica del protocollo è errata",
        "993" => "Errore di scrittura nel database durante la fase di protocollazione",
        "998" => "Errore di scrittura nel database durante la fase di protocollazione",
        "997" => "Errore di scrittura nel database durante la fase di protocollazione",
        "300" => "Errore di inserimento nel sistema di gestione documentale Halley per il documento principale",
        "301" => "Errore di inserimento nel sistema di gestione documentale Halley per il documento principale",
        "302" => "Errore di inserimento nel sistema di gestione documentale Halley per il documento principale",
        "303" => "Errore di inserimento nel sistema di gestione documentale Halley per il documento principale",
        "304" => "Errore di inserimento nel sistema di gestione documentale Halley per il documento principale",
        "992" => "Errore di scrittura nel database nella fase di collegamento fra documento principale e protocollo",
        "310" => "Errore di inserimento nel sistema di gestione documentale Halley per un allegato",
        "311" => "Errore di inserimento nel sistema di gestione documentale Halley per un allegato",
        "312" => "Errore di inserimento nel sistema di gestione documentale Halley per un allegato",
        "313" => "Errore di inserimento nel sistema di gestione documentale Halley per un allegato",
        "314" => "Errore di inserimento nel sistema di gestione documentale Halley per un allegato",
        "996" => "Errore di scrittura nel database nella fase di collegamento fra un allegato e protocollo",
        "995" => "Errore di scrittura nel database nella fase di collegamento fra protocollo ed ufficio",
        "994" => "Errore di scrittura nel database nella fase di collegamento fra protocollo ed anagrafica",
    );

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaELiosManager();
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
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($EliosClient) {
        $EliosClient->setWebservices_uri($this->clientParam['WSHALLEYENDPOINT']);
        $EliosClient->setWebservices_wsdl($this->clientParam['WSHALLEYWSDL']);
        $EliosClient->setWebservices_uriDizionario($this->clientParam['WSHALLEYENDPOINTDIZ']);
        $EliosClient->setWebservices_wsdlDizionario($this->clientParam['WSHALLEYWSDLDIZ']);
        $EliosClient->setCodiceDitta($this->clientParam['WSHALLEYCODICEDITTA']);
        $EliosClient->setCodiceAOO($this->clientParam['WSHALLEYCODICEAOO']);
        $EliosClient->setUsername($this->clientParam['WSHALLEYUSERNAME']);
        $EliosClient->setPassword($this->clientParam['WSHALLEYPASSWORD']);
        $EliosClient->setNamespace("http://tempuri.org");
        $EliosClient->setNamespaces("tem");
    }

    /**
     * 
     */
    function LeggiProtocollo() {
        return;
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi, $origine = "A") {
        /*
         * Se è un'integrazione cerco subito il fascicolo altrimenti poi mi fa scadere l'altro token
         */
        $idFascicolo = $annoFascicolo = $descFascicolo = "";
        if (isset($elementi['dati']['numeroProtocolloAntecedente']) && $elementi['dati']['numeroProtocolloAntecedente']) {
            /*
             * Cerco il fascicolo della pratica principale
             */
            $risultato = $this->GetFascicolo($elementi['dati']['numeroProtocolloAntecedente'], $elementi['dati']['dataProtocolloAntecedente']);
            if ($risultato['Status'] == "-1") {
                return $risultato;
            }
            if ($risultato['lngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                if ($msg == "") {
                    $msg = $this->getErrorString($risultato['lngErrNumber']);
                }
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile Impossibile trovare il fascicolo richiesto: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            if (isset($risultato['Fascicoli']['Fascicolo'])) {
                $CodiceTitolario = $risultato['Fascicoli']['Fascicolo']['CodiceTitolario'];
                $idFascicolo = $risultato['Fascicoli']['Fascicolo']['!id'];
                $annoFascicolo = $risultato['Fascicoli']['Fascicolo']['!anno'];
            }
        } else {
            $descFascicolo = "Richiesta On-Line N. " . substr($elementi['dati']['NumRichiesta'], 4) . " - " . substr($elementi['dati']['NumRichiesta'], 0, 4);
        }

        /*
         * Inizializzo il Client E-Lios
         */
        $itaELiosClient = new itaELiosClient();
        $this->setClientConfig($itaELiosClient);
        $param = array();

        /*
         * Reperisco il Token
         */
        $param['strCodEnte'] = $this->clientParam['WSHALLEYCODICEDITTA'];
        $param['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
        $param['strPassword'] = $this->clientParam['WSHALLEYPASSWORD'];
        $ret = $itaELiosClient->ws_Login($param);
        if (!$ret) {
            if ($itaELiosClient->getFault()) {
                $msg = $itaELiosClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaELiosClient->getError()) {
                $msg = $itaELiosClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaELiosClient->getResult();
        if ($risultato['lngErrNumber'] != 0) {
            $msg = $risultato['strErrString'];
            if ($msg == "") {
                $msg = $this->getErrorString($risultato['lngErrNumber']);
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione!! Impossibile reperire un token valido: <br>$msg";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $token = $risultato['strDST'];

        /*
         * Se non c'è il doc principale (Integrazione), metto uno degli altri allegati come principale e lo tolgo dall'array dei documenti
         * perchè il principale è obbligatorio
         */
        if (!$elementi['dati']['DocumentoPrincipale']) {
            $elementi['dati']['DocumentoPrincipale']['Nome'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome'];
            $elementi['dati']['DocumentoPrincipale']['Stream'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
            $elementi['dati']['DocumentoPrincipale']['Descrizione'] = $elementi['dati']['DocumentiAllegati'][0]['Descrizione'];
            unset($elementi['dati']['DocumentiAllegati'][0]);
        }

        /*
         * Aggiungo l'allegato Principale
         */
        $param = array();
        $arrayDocPrinc = $idDocPrinc = "";
        if ($elementi['dati']['DocumentoPrincipale']) {
            $param['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
            $param['strDST'] = $token;
            $param['strDocument'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']), ENT_COMPAT, 'UTF-8'); //$elementi['dati']['DocumentoPrincipale']['Nome'];
            $param['objDocument'] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $ret = $itaELiosClient->ws_Inserimento($param);
            if (!$ret) {
                if ($itaELiosClient->getFault()) {
                    $msg = $itaELiosClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire il documento principale: <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($itaELiosClient->getError()) {
                    $msg = $itaELiosClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire il documento principale: <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $itaELiosClient->getResult();
            if ($risultato['lngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                if ($msg == "") {
                    $msg = $this->getErrorString($risultato['lngErrNumber']);
                }
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire il documento principale: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $idDocPrinc = $risultato['lngDocID'];
            $arrayDocPrinc[$idDocPrinc]['Nome'] = $elementi['dati']['DocumentoPrincipale']['Nome'];
            $arrayDocPrinc[$idDocPrinc]['Descrizione'] = $elementi['dati']['DocumentoPrincipale']['Descrizione'];
        }

        /*
         * Aggiungo altri Allegati
         */
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $arrayId = array();
        foreach ($DocAllegati as $record) {
            $param['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
            $param['strDST'] = $token;
            $param['strDocument'] = htmlspecialchars(utf8_encode($record['Documento']['Nome']), ENT_COMPAT, 'UTF-8'); //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $record['Documento']['Nome']);
            $param['objDocument'] = $record['Documento']['Stream'];
            $ret = $itaELiosClient->ws_Inserimento($param);
            if (!$ret) {
                if ($itaELiosClient->getFault()) {
                    $msg = $itaELiosClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($itaELiosClient->getError()) {
                    $msg = $itaELiosClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
            $risultato = $itaELiosClient->getResult();
            if ($risultato['lngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                if ($msg == "") {
                    $msg = $this->getErrorString($risultato['lngErrNumber']);
                }
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            //$arrayId[] = $risultato['lngDocID'];
            $arrayId[$risultato['lngDocID']]['Nome'] = $record['Documento']['Nome'];
            $arrayId[$risultato['lngDocID']]['Descrizione'] = $record['Descrizione'];
        }

        /*
         * Inserisco Protocollo
         */
        $param = array();
        $param['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
        $param['strDST'] = $token;

        //$param['Intestazione']['Oggetto'] = $elementi['dati']['Oggetto'];
        $param['Intestazione']['Oggetto'] = htmlspecialchars(utf8_encode($elementi['dati']['Oggetto']), ENT_COMPAT, 'UTF-8');
        //
        $param['Intestazione']['Identificatore']['CodiceAmministrazione'] = "1";
        $param['Intestazione']['Identificatore']['CodiceAOO'] = $this->clientParam['WSHALLEYCODICEAOO'];
        $param['Intestazione']['Identificatore']['NumeroRegistrazione'] = "0";
        $param['Intestazione']['Identificatore']['DataRegistrazione'] = "0";

        //
        $param['Intestazione']['Identificatore']['Flusso'] = "E";
        //Mittente  
        $param['Intestazione']['Mittente'][0]['Persona']['Nome'] = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Nome']), ENT_COMPAT, 'UTF-8');
        $param['Intestazione']['Mittente'][0]['Persona']['Cognome'] = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Cognome']), ENT_COMPAT, 'UTF-8');
        $param['Intestazione']['Mittente'][0]['Persona']['CodiceFiscale'] = $elementi['dati']['MittDest']['CF'];
        $param['Intestazione']['Mittente'][0]['Persona']['IndirizzoTelematico'] = $elementi['dati']['MittDest']['Email'];
        $param['Intestazione']['Mittente'][0]['Persona']['Attributi'] = array("id" => $elementi['dati']['MittDest']['CF']);
        //Destinatario Amministrazione
        if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
            $arrDest = explode("|", $elementi['dati']['InCaricoA']);
            foreach ($arrDest as $keyUniOpe => $uniOpe) {
                $param['Intestazione']['Destinatario'][$keyUniOpe]['Amministrazione']['Denominazione'] = $elementi['destinatari'][0]['Denominazione'];
                $param['Intestazione']['Destinatario'][$keyUniOpe]['Amministrazione']['CodiceAmministrazione'] = $uniOpe;
                $param['Intestazione']['Destinatario'][$keyUniOpe]['Amministrazione']['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
                $param['Intestazione']['Destinatario'][$keyUniOpe]['Amministrazione']['UnitaOrganizzativa']['Attributi'] = array("id" => $uniOpe);
                $param['Intestazione']['Destinatario'][$keyUniOpe]['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
                $param['Intestazione']['Destinatario'][$keyUniOpe]['AOO']['CodiceAOO'] = $this->clientParam['WSHALLEYCODICEAOO'];
            }
        } else {
            $param['Intestazione']['Destinatario'][0]['Amministrazione']['Denominazione'] = $elementi['destinatari'][0]['Denominazione'];
            $param['Intestazione']['Destinatario'][0]['Amministrazione']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
            $param['Intestazione']['Destinatario'][0]['Amministrazione']['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
            $param['Intestazione']['Destinatario'][0]['Amministrazione']['UnitaOrganizzativa']['Attributi'] = array("id" => $elementi['dati']['InCaricoA']);
            $param['Intestazione']['Destinatario'][0]['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
            $param['Intestazione']['Destinatario'][0]['AOO']['CodiceAOO'] = $this->clientParam['WSHALLEYCODICEAOO'];
        }
        //
        $param['Intestazione']['Classifica']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
        $param['Intestazione']['Classifica']['CodiceAOO'] = $this->clientParam['WSHALLEYCODICEAOO'];
        if ($CodiceTitolario) {
            $param['Intestazione']['Classifica']['CodiceTitolario'] = $CodiceTitolario;
        } else {
            $param['Intestazione']['Classifica']['CodiceTitolario'] = $elementi['dati']['Classificazione'];
        }

        //
        $param['Intestazione']['Fascicolo']['Descrizione'] = $descFascicolo;
        $param['Intestazione']['Fascicolo']['Attributi'] = array(
            "numero" => $idFascicolo,
            "anno" => $annoFascicolo,
        );
        //
        //Documento Principale
        $param['Descrizione']['Documento']['DescrizioneDocumento'] = $arrayDocPrinc[$idDocPrinc]['Descrizione'];
        $param['Descrizione']['Documento']['TipoDocumento'] = "";
        $param['Descrizione']['Documento']['Attributi'] = array(
            "nome" => htmlspecialchars(utf8_encode($arrayDocPrinc[$idDocPrinc]['Nome']), ENT_COMPAT, 'UTF-8'),
            "id" => $idDocPrinc,
        );

        //Altri Allegati
        $i = 0;
        $arrSost1 = array("'", '"', "à", "è", "ì", "ò", "ù");
        $arrSost2 = array(" ", "", "a", "e", "i", "o", "u");
        foreach ($arrayId as $id => $allegato) {
            $descAllegato = str_replace($arrSost1, $arrSost2, $allegato['Descrizione']);
            //$param['Descrizione']['Allegati']['Documento'][$i]['DescrizioneDocumento'] = preg_replace("/[^a-zA-Z0-9\s._-]/", "", $allegato['Descrizione']);
            $param['Descrizione']['Allegati']['Documento'][$i]['DescrizioneDocumento'] = $descAllegato;
            $param['Descrizione']['Allegati']['Documento'][$i]['TipoDocumento'] = "";
            $param['Descrizione']['Allegati']['Documento'][$i]['Attributi'] = array(
                "nome" => htmlspecialchars(utf8_encode($allegato['Nome']), ENT_COMPAT, 'UTF-8'), //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $allegato['Nome']),
                "id" => $id,
            );
            $i++;
        }

        //
        $ret = $itaELiosClient->ws_Protocollazione($param);
        if (!$ret) {
            if ($itaELiosClient->getFault()) {
                $msg = $itaELiosClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile protocollare la richiesta " . $elementi['dati']['NumRichiesta'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaELiosClient->getError()) {
                $msg = $itaELiosClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile protocolalre la richiesta " . $elementi['dati']['NumRichiesta'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $itaELiosClient->getResult();
        if ($risultato['lngNumPG'] != 0) {
            $Data = $risultato['strDataPG']; //è nel formato 2012-05-10
            $proNum = $risultato['lngNumPG'];
            $Anno = $risultato['lngAnnoPG'];
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'E-Lios', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
            if ($risultato['lngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                if ($msg == "") {
                    $msg = $this->getErrorString($risultato['lngErrNumber']);
                }
                $ritorno["errString"] = $msg;
            }
        } else {
            if ($risultato['lngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                if ($msg == "") {
                    $msg = $this->getErrorString($risultato['lngErrNumber']);
                }
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        return $ritorno;
    }

    function GetFascicolo($NumeroAntecedente, $DataAntecedente) {
        /*
         * Reperisco il Token per il dizionario
         */
        $itaELioDizClient = new itaELiosDizionarioClient();
        $this->setClientConfig($itaELioDizClient);
        $paramDiz = array();
        //
        $paramDiz['strCodEnte'] = $this->clientParam['WSHALLEYCODICEDITTA'];
        $paramDiz['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
        $paramDiz['strPassword'] = $this->clientParam['WSHALLEYPASSWORD'];
        $ret = $itaELioDizClient->ws_Login($paramDiz);
        if (!$ret) {
            if ($itaELioDizClient->getFault()) {
                $msg = $itaELioDizClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido per il dizionario: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaELioDizClient->getError()) {
                $msg = $itaELioDizClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido per il dizionario: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaELioDizClient->getResult();
        if ($risultato['lngErrNumber'] != 0) {
            $msg = $risultato['strErrString'];
            if ($msg == "") {
                $msg = $this->getErrorString($risultato['lngErrNumber']);
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione!! Impossibile reperire un token valido: <br>$msg";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $tokenDiz = $risultato['strDST'];

        /*
         * Cerco il Fasciolo nel ws dizionario
         */
        $paramDiz['strUserName'] = $this->clientParam['WSHALLEYUSERNAME'];
        $paramDiz['strDST'] = $tokenDiz;
        $paramDiz['codiceAOO'] = $this->clientParam['WSHALLEYCODICEAOO'];
        $paramDiz['numeroProtocollo'] = $NumeroAntecedente;
        $paramDiz['annoProtocollo'] = substr($DataAntecedente, 0, 4);
        $ret = $itaELioDizClient->ws_SearchFascicoli($paramDiz);
        if (!$ret) {
            if ($itaELioDizClient->getFault()) {
                $msg = $itaELioDizClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile trovare il fascicolo richiesto per la pratica: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaELioDizClient->getError()) {
                $msg = $itaELioDizClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile trovare il fascicolo richiesto per la pratica: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $itaELioDizClient->getResult();
        return $risultato;
    }

    public function getErrorString($errorCode) {
        if (array_key_exists($errorCode, $this->arrayError)) {
            return $this->arrayError[$errorCode];
        } else {
            return "errore indefinito";
        }
        return;
    }

    /**
     * 
     */
    public function AggiungiAllegati() {
        return;
    }

}

?>