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
 * @version    12.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPELios/itaELiosClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPELios/itaELiosDizionarioClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proELios extends proWsClient {

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
            $managerObj = new proELios();
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
        if ($this->arrConfigParams) {
            $uri = "";
            $wsdl = "";
            $codiceDitta = "";
            $codiceAOO = "";
            $username = "";
            $password = "";
            $uriDizionario = "";
            $wsdlDizionario = "";
            $tipoInvio = "";
        } else {
            $keyConfigParam = proWsClientHelper::CLASS_PARAM_PROTOCOLLO_ELIOS;
            if ($this->keyConfigParam) {
                $keyConfigParam = $this->keyConfigParam;
            }
            $devLib = new devLib();
            $uri = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSENDPOINT', false);
            $wsdl = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSWSDL', false);
            $codiceDitta = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSCODICEENTE', false);
            $codiceAOO = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSCODICEAOO', false);
            $username = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSUSERNAME', false);
            $password = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSPASSWORD', false);
            $uriDizionario = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSDIZIONARIOENDPOINT', false);
            $wsdlDizionario = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSDIZIONARIOWSDL', false);
            $tipoInvio = $devLib->getEnv_config($keyConfigParam, 'codice', 'ELIOSWSTIPOINVIO', false);

            //
            $clientParam = array(
                "ELIOSWSENDPOINT" => $uri['CONFIG'],
                "ELIOSWSWSDL" => $wsdl['CONFIG'],
                "ELIOSWSCODICEENTE" => $codiceDitta['CONFIG'],
                "ELIOSWSCODICEAOO" => $codiceAOO['CONFIG'],
                "ELIOSWSUSERNAME" => $username['CONFIG'],
                "ELIOSWSPASSWORD" => $password['CONFIG'],
                "ELIOSWSDIZIONARIOENDPOINT" => $uriDizionario['CONFIG'],
                "ELIOSWSDIZIONARIOWSDL" => $wsdlDizionario['CONFIG'],
                "ELIOSWSTIPOINVIO" => $tipoInvio['CONFIG'],
            );
            $this->setClientParam($clientParam);
        }
        //
        $EliosClient->setWebservices_uri($uri['CONFIG']);
        $EliosClient->setWebservices_wsdl($wsdl['CONFIG']);
        $EliosClient->setWebservices_uriDizionario($uriDizionario['CONFIG']);
        $EliosClient->setWebservices_wsdlDizionario($wsdlDizionario['CONFIG']);
        $EliosClient->setCodiceDitta($codiceDitta['CONFIG']);
        $EliosClient->setCodiceAOO($codiceAOO['CONFIG']);
        $EliosClient->setUsername($username['CONFIG']);
        $EliosClient->setPassword($password['CONFIG']);
        $EliosClient->setNamespace("http://tempuri.org");
        $EliosClient->setNamespaces("tem");
        $EliosClient->setTipoInvio($tipoInvio['CONFIG']);
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
         * Se è una,comunicazione cerco subito il fascicolo altrimenti poi mi fa scadere l'altro token
         */
        $idFascicolo = $annoFascicolo = $descFascicolo = $titolarioFascicolo = "";

        $tipoProtAnt = $elementi['dati']['MetaDati']['DatiProtocollazione']['TipoProtocollo']['value'];

        if ($elementi['dati']['ChiavePasso'] && $tipoProtAnt == $this->getClientType()) {
            /*
             * Cerco il fascicolo della pratica principale
             */
            $risultato = $this->GetFascicolo($elementi['dati']['NumeroAntecedente'], $elementi['dati']['AnnoAntecedente']);
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

            if (isset($risultato['Fascicoli']['Fascicolo'][0])) {
                foreach ($risultato['Fascicoli']['Fascicolo'] as $fascicolo) {
                    if ($fascicolo['CodiceTitolario'] == $elementi['dati']['Classificazione']) {
                        $idFascicolo = $fascicolo['!id'];
                        $annoFascicolo = $fascicolo['!anno'];
                        $descFascicolo = $fascicolo['Nome'];
                        $titolarioFascicolo = $fascicolo['CodiceTitolario'];
                    }
                }
            } elseif (isset($risultato['Fascicoli']['Fascicolo'])) {
                $idFascicolo = $risultato['Fascicoli']['Fascicolo']['!id'];
                $annoFascicolo = $risultato['Fascicoli']['Fascicolo']['!anno'];
                $descFascicolo = $risultato['Fascicoli']['Fascicolo']['Nome'];
                $titolarioFascicolo = $risultato['Fascicoli']['Fascicolo']['CodiceTitolario'];
            }
        } else {
            $descFascicolo = "Pratica Suap N. " . $elementi['dati']['NumeroPratica'];
        }



//        if ($origine == "P") {
//            /*
//             * Cerco il fascicolo della pratica principale
//             */
//            $risultato = $this->GetFascicolo($elementi['dati']['NumeroAntecedente'], $elementi['dati']['AnnoAntecedente']);
//            if ($risultato['Status'] == "-1") {
//                return $risultato;
//            }
//            if ($risultato['lngErrNumber'] != 0) {
//                $msg = $risultato['strErrString'];
//                if ($msg == "") {
//                    $msg = $this->getErrorString($risultato['lngErrNumber']);
//                }
//                $ritorno["Status"] = "-1";
//                $ritorno["Message"] = "Attenzione!! Impossibile Impossibile trovare il fascicolo richiesto: <br>$msg";
//                $ritorno["RetValue"] = false;
//                return $ritorno;
//            }
//
//            if (isset($risultato['Fascicoli']['Fascicolo'])) {
//                $descFascicolo = $risultato['Fascicoli']['Fascicolo']['Nome'];
//                $idFascicolo = $risultato['Fascicoli']['Fascicolo']['!id'];
//                $annoFascicolo = $risultato['Fascicoli']['Fascicolo']['!anno'];
//                $descFascicolo = $risultato['Fascicoli']['Fascicolo']['Nome'];
//                $titolarioFascicolo = $risultato['Fascicoli']['Fascicolo']['CodiceTitolario'];
//            }
//        } elseif ($origine == "A") {
//            //Cerco il Fascicolo solo se è un passo di una comunicazione in arrivo
//            if ($elementi['dati']['ChiavePasso']) {
//                /*
//                 * Cerco il fascicolo della pratica principale
//                 */
//                $risultato = $this->GetFascicolo($elementi['dati']['NumeroAntecedente'], $elementi['dati']['AnnoAntecedente']);
//                if ($risultato['Status'] == "-1") {
//                    return $risultato;
//                }
//                if ($risultato['lngErrNumber'] != 0) {
//                    $msg = $risultato['strErrString'];
//                    if ($msg == "") {
//                        $msg = $this->getErrorString($risultato['lngErrNumber']);
//                    }
//                    $ritorno["Status"] = "-1";
//                    $ritorno["Message"] = "Attenzione!! Impossibile Impossibile trovare il fascicolo richiesto: <br>$msg";
//                    $ritorno["RetValue"] = false;
//                    return $ritorno;
//                }
//
//                if (isset($risultato['Fascicoli']['Fascicolo'][0])) {
//                    foreach ($risultato['Fascicoli']['Fascicolo'] as $fascicolo) {
//                        if ($fascicolo['CodiceTitolario'] == $elementi['dati']['Classificazione']) {
//                            $idFascicolo = $fascicolo['!id'];
//                            $annoFascicolo = $fascicolo['!anno'];
//                            $descFascicolo = $fascicolo['Nome'];
//                            $titolarioFascicolo = $fascicolo['CodiceTitolario'];
//                        }
//                    }
//                } elseif (isset($risultato['Fascicoli']['Fascicolo'])) {
//                    $idFascicolo = $risultato['Fascicoli']['Fascicolo']['!id'];
//                    $annoFascicolo = $risultato['Fascicoli']['Fascicolo']['!anno'];
//                    $descFascicolo = $risultato['Fascicoli']['Fascicolo']['Nome'];
//                    $titolarioFascicolo = $risultato['Fascicoli']['Fascicolo']['CodiceTitolario'];
//                }
//            } else {
//                $descFascicolo = "Pratica Suap N. " . $elementi['dati']['NumeroPratica'];
//            }
//        }

        /*
         * Se prende il fascicolo della pratica padre, prende anche lo stesso titolario.
         * Se titolario diverso, da Errore
         */
        $codiceClassifica = $elementi['dati']['Classificazione'];
        if ($titolarioFascicolo) {
            $codiceClassifica = $titolarioFascicolo;
        }

        $itaELiosClient = new itaELiosClient();
        $this->setClientConfig($itaELiosClient);
        $param = array();

        /*
         * Reperisco il Token
         */
        $param['strCodEnte'] = $this->clientParam['ELIOSWSCODICEENTE'];
        $param['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
        $param['strPassword'] = $this->clientParam['ELIOSWSPASSWORD'];
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
         * Aggiungo l'allegato Principale
         */
        $docPrinc = $elementi['dati']['DocumentoPrincipale'];
        $nome = $docPrinc['Nome'];
        $strem = $docPrinc['Stream'];
        if (!$docPrinc) {
            $docPrinc = $elementi['dati']['DocumentiAllegati'][0];
            $nome = $docPrinc['Documento']['Nome'];
            $strem = $docPrinc['Documento']['Stream'];
            unset($elementi['dati']['DocumentiAllegati'][0]); //Tolgo il primo allegato perchè è diventato il principale
        }
        $arrayDocPrinc = array();
        $idDocPrinc = "";
        if ($docPrinc) {
            if ($strem == '') {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Allegato principale non valido. (FILE VUOTO)";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            $param['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
            $param['strDST'] = $token;
            $param['strDocument'] = htmlspecialchars(utf8_encode($nome), ENT_COMPAT, 'UTF-8');
            $param['objDocument'] = $strem;
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
            $arrayDocPrinc[$idDocPrinc]['Nome'] = $nome;
            $arrayDocPrinc[$idDocPrinc]['Descrizione'] = $docPrinc['Descrizione'];
        }

        /*
         * Aggiungo altri Allegati
         */
        //unset($elementi['dati']['DocumentiAllegati'][0]); //Tolgo il primo allegato perchè è diventato il principale
        //
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $arrayId = array();
        foreach ($DocAllegati as $record) {
            if ($record['Documento']['Stream'] == '') {
                continue;
            }

            $param['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
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
        $param['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
        $param['strDST'] = $token;

        $param['Intestazione']['Oggetto'] = htmlspecialchars(utf8_encode($elementi['dati']['Oggetto']), ENT_COMPAT, 'UTF-8');
        //
        $param['Intestazione']['Identificatore']['CodiceAmministrazione'] = "1";
        $param['Intestazione']['Identificatore']['CodiceAOO'] = $this->clientParam['ELIOSWSCODICEAOO'];
        $param['Intestazione']['Identificatore']['NumeroRegistrazione'] = "0";
        $param['Intestazione']['Identificatore']['DataRegistrazione'] = "0";

        //
        if ($origine == "P") {
            $Persona = 'Mittente';
            if ($origine == "P" && !$elementi['dati']['destinatari']) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Destinatari non presenti.";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            $param['Intestazione']['Identificatore']['Flusso'] = "U";
            //Destinatari
            foreach ($elementi['dati']['destinatari'] as $key => $destinatario) {
                $param['Intestazione']['Destinatario'][$key]['Persona']['Nome'] = ""; //htmlspecialchars(utf8_encode($destinatario['Denominazione']), ENT_COMPAT, 'UTF-8');
                $param['Intestazione']['Destinatario'][$key]['Persona']['Cognome'] = htmlspecialchars(utf8_encode($destinatario['Denominazione']), ENT_COMPAT, 'UTF-8');
                $param['Intestazione']['Destinatario'][$key]['Persona']['CodiceFiscale'] = $destinatario['CF'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['IndirizzoTelematico'] = $destinatario['Email'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['Attributi'] = array("id" => $destinatario['CF']);
            }
            //Mittente Amministrazione
            $denom = $elementi['dati']['Mittente']['Denominazione'];
            $email = $elementi['dati']['Mittente']['Email'];
        } elseif ($origine == "A") {
            $desnom = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Denominazione']), ENT_COMPAT, 'UTF-8');
            $desnome = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Nome']), ENT_COMPAT, 'UTF-8');
            $descognome = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Cognome']), ENT_COMPAT, 'UTF-8');
            //
            $Persona = 'Destinatario';
            $param['Intestazione']['Identificatore']['Flusso'] = "E";
//            $param['Intestazione']['Mittente'][0]['Persona']['Nome'] = ""; //htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Denominazione']), ENT_COMPAT, 'UTF-8');
//            $param['Intestazione']['Mittente'][0]['Persona']['Cognome'] = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Denominazione']), ENT_COMPAT, 'UTF-8');
            $param['Intestazione']['Mittente'][0]['Persona']['Nome'] = $desnome ? $desnome : "";
            $param['Intestazione']['Mittente'][0]['Persona']['Cognome'] = $descognome ? $descognome : $desnom;
            $param['Intestazione']['Mittente'][0]['Persona']['Denominazione'] = $desnom ? $desnom : $descognome . " " . $desnome;
            $param['Intestazione']['Mittente'][0]['Persona']['CodiceFiscale'] = $elementi['dati']['MittDest']['CF'];
            $param['Intestazione']['Mittente'][0]['Persona']['IndirizzoTelematico'] = $elementi['dati']['MittDest']['Email'];
            $param['Intestazione']['Mittente'][0]['Persona']['Attributi'] = array("id" => $elementi['dati']['MittDest']['CF']);
            //Destinatario Amministrazione
            $denom = $elementi['destinatari'][0]['Denominazione'];
            $email = $elementi['destinatari'][0]['Email'];
        }

        if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
            $arrDest = explode("|", $elementi['dati']['InCaricoA']);
            foreach ($arrDest as $keyUniOpe => $uniOpe) {
                $param['Intestazione'][$Persona][$keyUniOpe]['Amministrazione']['Denominazione'] = $denom;
                $param['Intestazione'][$Persona][$keyUniOpe]['Amministrazione']['CodiceAmministrazione'] = $uniOpe;
                $param['Intestazione'][$Persona][$keyUniOpe]['Amministrazione']['IndirizzoTelematico'] = $email;
                $param['Intestazione'][$Persona][$keyUniOpe]['Amministrazione']['UnitaOrganizzativa']['Attributi'] = array("id" => $uniOpe);
                $param['Intestazione'][$Persona][$keyUniOpe]['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
                $param['Intestazione'][$Persona][$keyUniOpe]['AOO']['CodiceAOO'] = $this->clientParam['ELIOSWSCODICEAOO'];
            }
        } else {
            $param['Intestazione'][$Persona][0]['Amministrazione']['Denominazione'] = $denom;
            $param['Intestazione'][$Persona][0]['Amministrazione']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
            $param['Intestazione'][$Persona][0]['Amministrazione']['IndirizzoTelematico'] = $email;
            $param['Intestazione'][$Persona][0]['Amministrazione']['UnitaOrganizzativa']['Attributi'] = array("id" => $elementi['dati']['InCaricoA']);
            $param['Intestazione'][$Persona][0]['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
            $param['Intestazione'][$Persona][0]['AOO']['CodiceAOO'] = $this->clientParam['ELIOSWSCODICEAOO'];
        }

        //
        $param['Intestazione']['Classifica']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
        $param['Intestazione']['Classifica']['CodiceAOO'] = $this->clientParam['ELIOSWSCODICEAOO'];
        $param['Intestazione']['Classifica']['CodiceTitolario'] = $codiceClassifica;
        //$param['Intestazione']['Classifica']['CodiceTitolario'] = $elementi['dati']['Classificazione'];

        $arrSost1 = array("'", '"', "à", "è", "ì", "ò", "ù", "°");
        $arrSost2 = array(" ", "", "a", "e", "i", "o", "u", ".");


        /*
         * Insersico tag Fascicolo solo se Fascicola uguale Si
         */
        if ($elementi['Fascicola'] == "Si") {
            $descFascicolo = str_replace($arrSost1, $arrSost2, $descFascicolo);
            //
            $param['Intestazione']['Fascicolo']['Descrizione'] = $descFascicolo;
            $param['Intestazione']['Fascicolo']['Attributi'] = array(
                "numero" => $idFascicolo,
                "anno" => $annoFascicolo,
            );
        }

        /*
         * Documento Principale
         */
        $descAllegatoPrinc = str_replace($arrSost1, $arrSost2, $arrayDocPrinc[$idDocPrinc]['Descrizione']);
        $nomeAllegatoPrinc = str_replace($arrSost1, $arrSost2, $arrayDocPrinc[$idDocPrinc]['Nome']);
        $param['Descrizione']['Documento']['DescrizioneDocumento'] = htmlspecialchars(utf8_encode($descAllegatoPrinc), ENT_COMPAT, 'UTF-8'); //$arrayDocPrinc[$idDocPrinc]['Descrizione'];
        $param['Descrizione']['Documento']['TipoDocumento'] = "";
        $param['Descrizione']['Documento']['Attributi'] = array(
            "nome" => htmlspecialchars(utf8_encode($nomeAllegatoPrinc), ENT_COMPAT, 'UTF-8'), //$arrayDocPrinc[$idDocPrinc] ['Nome'],
            "id" => $idDocPrinc,
        );

        //Altri Allegati
        $i = 0;
        foreach ($arrayId as $id => $allegato) {
            $descAllegato = str_replace($arrSost1, $arrSost2, $allegato['Descrizione']);
            $nomeAllegato = str_replace($arrSost1, $arrSost2, $allegato['Nome']);
            $param['Descrizione']['Allegati']['Documento'][$i]['DescrizioneDocumento'] = htmlspecialchars(utf8_encode($descAllegato), ENT_COMPAT, 'UTF-8');
            $param['Descrizione']['Allegati']['Documento'][$i]['TipoDocumento'] = "";
            $param['Descrizione']['Allegati']['Documento'][$i]['Attributi'] = array(
                "nome" => htmlspecialchars(utf8_encode($nomeAllegato), ENT_COMPAT, 'UTF-8'), //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $allegato['Nome']),
                "id" => $id,
            );
            $i++;
        }

        /*
         * Parametri 
         */
        $arrParametri = array();
        if ($this->clientParam['ELIOSWSTIPOINVIO']) {
            $arrParametri["TipoInvio"]['Attributi'] = array(
                "nome" => "TipoInvio",
                "valore" => $this->clientParam['ELIOSWSTIPOINVIO'],
            );
        }
        $param['parametri'] = $arrParametri;

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
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''))
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
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . "

            : <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        return $ritorno;
    }

    function GetFascicolo($NumeroAntecedente, $AnnoAntecedente) {
        /*
         * Reperisco il Token per il dizionario
         */
        $itaELioDizClient = new itaELiosDizionarioClient();
        $this->setClientConfig($itaELioDizClient);
        $paramDiz = array();
        //
        $paramDiz['strCodEnte'] = $this->clientParam['ELIOSWSCODICEENTE'];
        $paramDiz['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
        $paramDiz['strPassword'] = $this->clientParam['ELIOSWSPASSWORD'];
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
        $paramDiz['strUserName'] = $this->clientParam['ELIOSWSUSERNAME'];
        $paramDiz['strDST'] = $tokenDiz;
        $paramDiz['codiceAOO'] = $this->clientParam['ELIOSWSCODICEAOO'];
        $paramDiz['numeroProtocollo'] = $NumeroAntecedente;
        $paramDiz['annoProtocollo'] = $AnnoAntecedente;
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

    public function getClientType() {
        return proWsClientHelper::CLIENT_ELIOS;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->InserisciProtocollo($elementi, 'P');
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
