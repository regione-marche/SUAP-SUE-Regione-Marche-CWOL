<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    29.06.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPLeonardo/itaLeonardoClient.class.php');

class itaLeonardoManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaLeonardoManager();
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
    private function setClientConfig($LeonardoClient) {
        $LeonardoClient->setWebservices_uri($this->clientParam['LEONARDOWSENDPOINT']);
        $LeonardoClient->setWebservices_wsdl($this->clientParam['LEONARDOWSWSDL']);
        $LeonardoClient->setCodiceDitta($this->clientParam['LEONARDOWSENTE']);
        //$LeonardoClient->setCodiceDitta($this->clientParam['LEONARDOWSCODICEENTE']);
        $LeonardoClient->setCodiceAOO($this->clientParam['LEONARDOWSCODICEAOO']);
        //$LeonardoClient->setUsername($this->clientParam['LEONARDOWSUSERNAME']);
        $LeonardoClient->setUsername($this->clientParam['LEONARDOWSCODUTE']);
        $LeonardoClient->setPassword($this->clientParam['LEONARDOWSPASSWORD']);
        $LeonardoClient->setNamespace("http://tempuri.org");
        $LeonardoClient->setNamespaces("tem");
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
         * Verifica presenza allegati, perchè il protocollo vuole almeno un allegato altrimenti torna errore I/O
         */
        if (!$elementi['dati']['DocumentoPrincipale'] && !$elementi['dati']['DocumentiAllegati']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Non ci sono documenti da allegare al protocollo.<br>Selezionare almeno un documento.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $itaLeonardoClient = new itaLeonardoClient();
        $this->setClientConfig($itaLeonardoClient);
        $param = array();

        /*
         * Reperisco il Token
         */
        $param['CodiceEnte'] = $this->clientParam['LEONARDOWSENTE'];
        $param['Utente'] = $this->clientParam['LEONARDOWSCODUTE'];
        $param['Password'] = $this->clientParam['LEONARDOWSPASSWORD'];
        $ret = $itaLeonardoClient->ws_Login($param);
        if (!$ret) {
            if ($itaLeonardoClient->getFault()) {
                $msg = $itaLeonardoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaLeonardoClient->getError()) {
                $msg = $itaLeonardoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaLeonardoClient->getResult();
        if ($risultato['IngErrNumber'] != 0) {
            $msg = $risultato['strErrString'];
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
        $arrayDocPrinc = $idDocPrinc = "";
        if ($docPrinc) {
            $param = array();
            $param['Username'] = $this->clientParam['LEONARDOWSCODUTE'];
            $param['DSTLogin'] = $token;
            $param['FileBinario'] = $strem;
            $ret = $itaLeonardoClient->ws_Inserimento($param);
            if (!$ret) {
                if ($itaLeonardoClient->getFault()) {
                    $msg = $itaLeonardoClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire il documento principale: <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($itaLeonardoClient->getError()) {
                    $msg = $itaLeonardoClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire il documento principale: <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $itaLeonardoClient->getResult();
            if ($risultato['IngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire il documento principale: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $idDocPrinc = $risultato['IngDocID'];
            $arrayDocPrinc[$idDocPrinc]['Nome'] = $nome;
            $arrayDocPrinc[$idDocPrinc]['Descrizione'] = $docPrinc['Descrizione'];
        }

        /*
         * Aggiungo altri Allegati
         */
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $arrayId = array();
        foreach ($DocAllegati as $record) {
            $param = array();
            $param['Username'] = $this->clientParam['LEONARDOWSCODUTE'];
            $param['DSTLogin'] = $token;
            $param['FileBinario'] = $record['Documento']['Stream'];
            $ret = $itaLeonardoClient->ws_Inserimento($param);
            if (!$ret) {
                if ($itaLeonardoClient->getFault()) {
                    $msg = $itaLeonardoClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($itaLeonardoClient->getError()) {
                    $msg = $itaLeonardoClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
            $risultato = $itaLeonardoClient->getResult();
            if ($risultato['IngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $arrayId[$risultato['IngDocID']]['Nome'] = $record['Documento']['Nome'];
            $arrayId[$risultato['IngDocID']]['Descrizione'] = $record['Descrizione'];
        }

        /*
         * Inserisco Protocollo
         */
        $param['Username'] = $this->clientParam['LEONARDOWSCODUTE'];
        $param['DSTLogin'] = $token;

        $param['Intestazione']['Oggetto'] = htmlspecialchars(utf8_encode($elementi['dati']['Oggetto']), ENT_COMPAT, 'UTF-8');
        //
        $param['Intestazione']['Identificatore']['CodiceAmministrazione'] = "1";
        $param['Intestazione']['Identificatore']['CodiceAOO'] = $this->clientParam['LEONARDOWSCODICEAOO'];
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
                $param['Intestazione']['Destinatario'][$key]['Persona']['Nome'] = $destinatario['Denominazione'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['Cognome'] = $destinatario['Denominazione'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['Denominazione'] = $destinatario['Denominazione'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['CodiceFiscale'] = $destinatario['CF'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['IndirizzoTelematico'] = $destinatario['Email'];
                $param['Intestazione']['Destinatario'][$key]['Persona']['Attributi'] = array("id" => $destinatario['CF']);
            }
            //Mittente Amministrazione
            $denom = $elementi['dati']['Mittente']['Denominazione'];
            $email = $elementi['dati']['Mittente']['Email'];
        } elseif ($origine == "A") {
            $Persona = 'Destinatario';
            $param['Intestazione']['Identificatore']['Flusso'] = "E";
            $param['Intestazione']['Mittente'][0]['Persona']['Denominazione'] = $elementi['dati']['MittDest']['Denominazione'];
            $param['Intestazione']['Mittente'][0]['Persona']['Nome'] = $elementi['dati']['MittDest']['Nome'];
            $param['Intestazione']['Mittente'][0]['Persona']['Cognome'] = $elementi['dati']['MittDest']['Cognome'];
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
                $param['Intestazione'][$Persona][$keyUniOpe]['AOO']['CodiceAOO'] = $this->clientParam['LEONARDOWSCODICEAOO'];
            }
        } else {
            $param['Intestazione'][$Persona][0]['Amministrazione']['Denominazione'] = $denom;
            $param['Intestazione'][$Persona][0]['Amministrazione']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
            $param['Intestazione'][$Persona][0]['Amministrazione']['IndirizzoTelematico'] = $email;
            $param['Intestazione'][$Persona][0]['Amministrazione']['UnitaOrganizzativa']['Attributi'] = array("id" => $elementi['dati']['InCaricoA']);
            $param['Intestazione'][$Persona][0]['IndirizzoTelematico'] = $elementi['destinatari'][0]['Email'];
            $param['Intestazione'][$Persona][0]['AOO']['CodiceAOO'] = $this->clientParam['LEONARDOWSCODICEAOO'];
        }

        //
        $param['Intestazione']['Classifica']['CodiceAmministrazione'] = $elementi['dati']['InCaricoA'];
        $param['Intestazione']['Classifica']['CodiceAOO'] = $this->clientParam['LEONARDOWSCODICEAOO'];
        $param['Intestazione']['Classifica']['CodiceTitolario'] = $elementi['dati']['Classificazione'];
        //
//        $param['Intestazione']['Fascicolo']['Descrizione'] = $descFascicolo;
//        $param['Intestazione']['Fascicolo']['Attributi'] = array(
//            "numero" => $idFascicolo,
//            "anno" => $annoFascicolo,
//        );
        //
        //Documento Principale
        if ($arrayDocPrinc) {
            $param['Descrizione']['Documento']['DescrizioneDocumento'] = $arrayDocPrinc[$idDocPrinc]['Descrizione'];
            $param['Descrizione']['Documento']['Attributi'] = array(
                "nome" => htmlspecialchars(utf8_encode($arrayDocPrinc[$idDocPrinc]['Nome']), ENT_COMPAT, 'UTF-8'), //$arrayDocPrinc[$idDocPrinc] ['Nome'],
                "id" => $idDocPrinc,
            );
        }

        //Altri Allegati
        $i = 0;
        $arrSost1 = array("'", '"', "à", "è", "ì", "ò", "ù", "°");
        $arrSost2 = array(" ", "", "a", "e", "i", "o", "u", ".");
        if ($arrayId) {
            foreach ($arrayId as $id => $allegato) {
                $descAllegato = str_replace($arrSost1, $arrSost2, $allegato['Descrizione']);
                $nomeAllegato = str_replace($arrSost1, $arrSost2, $allegato['Nome']);
                $param['Descrizione']['Allegati']['Documento'][$i]['DescrizioneDocumento'] = $descAllegato;
                $param['Descrizione']['Allegati']['Documento'][$i]['Attributi'] = array(
                    "nome" => htmlspecialchars(utf8_encode($nomeAllegato), ENT_COMPAT, 'UTF-8'), //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $allegato['Nome']),
                    "id" => $id,
                );
                $i++;
            }
        }

        //
        $ret = $itaLeonardoClient->ws_Protocollazione($param);
        if (!$ret) {
            if ($itaLeonardoClient->getFault()) {
                $msg = $itaLeonardoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile protocollare la richiesta " . $elementi['dati']['NumRichiesta'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaLeonardoClient->getError()) {
                $msg = $itaLeonardoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile protocolalre la richiesta " . $elementi['dati']['NumRichiesta'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $itaLeonardoClient->getResult();
        if ($risultato['IngNumPG'] != 0) {
            $Da_ta = str_replace("/", "-", $risultato['StrDataPG']); //è nel formato 01/02/2017
            $Data = date("Y-m-d", strtotime($Da_ta));
            $proNum = $risultato['IngNumPG'];
            $Anno = $risultato['IngAnnoPG'];
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Leonardo', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''))
            );
            if ($risultato['IngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                $ritorno["errString"] = $msg;
            }
        } else {
            if ($risultato['IngErrNumber'] != 0) {
                $msg = $risultato['strErrString'];
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . "

            : <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        return $ritorno;
    }

}

?>