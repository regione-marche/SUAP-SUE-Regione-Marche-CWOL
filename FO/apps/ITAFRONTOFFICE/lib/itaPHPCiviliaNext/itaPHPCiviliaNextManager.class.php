<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    21.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPCiviliaNext/itaPHPCiviliaNextClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPOAuth2Client/itaPHPOAuth2Client.class.php');

class itaCiviliaNextManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaCiviliaNextManager();
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
     * Libreria di funzioni Generiche e Utility per Protocollo Civilia Next
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfigAuth($client) {
        $client->setClientId($this->clientParam['CIVILIANEXTCLIENTID']);
        $client->setClientSecret($this->clientParam['CIVILIANEXTCLIENTSECRET']);
        $client->setUrlAccessToken($this->clientParam['CIVILIANEXTURLACCESSTOKEN']);
        $client->setScope($this->clientParam['CIVILIANEXTSCOPE']);
    }

    private function setClientConfig($client) {
        $client->setEndpoint($this->clientParam['CIVILIANEXTENDPOINT']);
        $client->setCodiceOrganigramma($this->clientParam['CIVILIANEXTCODORGANIGRAMMA']);
        $client->setIdOperatore($this->clientParam['CIVILIANEXTIDOPERATORE']);
    }

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo")
     * @return type
     */
    function LeggiProtocollo($elementi) {
        return $ritorno;
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi) {
        $ritorno = array();
        //
        $client = new itaPHPOAuth2Client();
        $this->setClientConfigAuth($client);
        $token = $client->getToken();
        if ($token == "") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile reperire il token";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        //
        $param = array();
        $param['oggetto'] = htmlspecialchars(utf8_encode($elementi['dati']['Oggetto']), ENT_COMPAT, 'UTF-8');
        $param['isFromModificaAllegato'] = false; //nel caso si tratti di un nuovo protocollo.
        $param['isFromModificaCorrispondente'] = false; //nel caso si tratti di un nuovo protocollo.
        $param['tipoProtocollo'] = "INGRESSO";

        /*
         * Mittente
         */
        $listaCorrispondenti = array();
        $denom = $elementi['dati']['MittDest']['Denominazione'];
        if ($denom == "") {
            $denom = $elementi['dati']['MittDest']['Cognome'] . " " . $elementi['dati']['MittDest']['Nome'];
        }
        $listaCorrispondenti[0]['denominazione'] = htmlspecialchars(utf8_encode($denom), ENT_COMPAT, 'UTF-8');
        $listaCorrispondenti[0]['email'] = $elementi['dati']['MittDest']['Email'];
        $listaCorrispondenti[0]['tipoIndividuoProtocollo'] = "noncertificato";
        //$listaCorrispondenti[0]['id'] = $CorrispondenteElement['id'];

        $param['CorrispondentiList'] = $listaCorrispondenti;

        /*
         * Allegati
         */
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php');
        $praLib = new praLib();
        $listaAllegati = array();

        if (isset($elementi['dati']['DocumentoPrincipale']) && isset($elementi['dati']['DocumentoPrincipale']['Nome'])) {
            $listaAllegati[0]["nomeFile"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["file"] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $listaAllegati[0]["mimeType"] = $praLib->getMimeType($elementi['dati']['DocumentoPrincipale']['Nome']);
            $listaAllegati[0]["titolo"] = "";
            $listaAllegati[0]["descrizione"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["isPrincipale"] = 1;
            $listaAllegati[0]["idSingolaFattura"] = "";
        } else {
            $listaAllegati[0]["nomeFile"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["file"] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
            $listaAllegati[0]["mimeType"] = $praLib->getMimeType($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']);
            $listaAllegati[0]["titolo"] = "";
            $listaAllegati[0]["descrizione"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["isPrincipale"] = 1;
            $listaAllegati[0]["idSingolaFattura"] = "";
            unset($elementi['dati']['DocumentiAllegati'][0]);
        }

        $j = 1;
        foreach ($elementi['dati']['DocumentiAllegati'] as $key => $doc) {
            $listaAllegati[$j]["nomeFile"] = htmlspecialchars(utf8_encode($doc['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[$j]["file"] = $doc['Documento']['Stream'];
            $listaAllegati[$j]["mimeType"] = $praLib->getMimeType($doc['Documento']['Nome']);
            $listaAllegati[$j]["titolo"] = "";
            $listaAllegati[$j]["descrizione"] = htmlspecialchars(utf8_encode($doc['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[$j]["isPrincipale"] = 0;
            $listaAllegati[$j]["idSingolaFattura"] = "";
            $j++;
        }
        $param['AllegatiList'] = $listaAllegati;



        $param['protocollatoDa'] = "ALTRO"; // webappfatturazione|civiliaweb|civiliaopen
        $param['idCodiceAOO'] = ""; // capire se esiste una sola AOO

        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
        $this->setClientConfig($itaCiviliaNextClient);
        $itaCiviliaNextClient->setToken($token);
        $ret = $itaCiviliaNextClient->Protocolla($param);

        $risultato = json_decode($ret, true);

        $strError = "";
        if (isset($risultato[0])) {
            foreach ($risultato as $errore) {
                $strError .= $errore . "<br>";
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in protocollazione:<br>$strError";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (!isset($risultato['resultType'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in protocollazione:<br>" . $risultato['message'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultType'] != "1") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in protocollazione:<br>" . $risultato['resultDescription'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultDescription'] == "OK") {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'CiviliaNext', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $risultato['result']['numeroProtocollo'], 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $risultato['result']['dataRegistrazione'], 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => date("Y"), 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $risultato['result']['id'], 'status' => true, 'msg' => '')
                )
            );
            
            /*
             * Assegna Pratica
             */
            $retAssegna = $this->assegnaPratica($itaCiviliaNextClient, $risultato['result']['id']);
            if ($retAssegna['Status'] == "-1") {
                $ritorno["errStringProt"] = $retAssegna['Message'];
            }
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in protocollazione:<br>" . $risultato['resultDescription'];
            $ritorno["RetValue"] = false;
        }

        return $ritorno;
    }

    private function assegnaPratica($itaCiviliaNextClient, $idPratica) {
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Pratica assegnata";
        $ritorno["RetValue"] = true;
        //
        $listaAssegnatari = array();
        $paramAssegan = array();
        $paramAssegan['idPratica'] = $idPratica;
        //
        $listaAssegnatari[0]['idIndividuoAssegnatario'] = "";
        $listaAssegnatari[0]['attributo'] = "";
        $listaAssegnatari[0]['path'] = "";
        $listaAssegnatari[0]['ruoloAssegnatario'] = "";
        $listaAssegnatari[0]['isAssegnatario'] = true;
        //
        $paramAssegan['Assegnatari'] = $listaAssegnatari;
        $ret = $itaCiviliaNextClient->AssegnaPratica($paramAssegan);
        $risultato = json_decode($ret, true);
        if ($risultato['IsOk'] == false) {
            $msgErr = "Pratica non assegnata: ";
            foreach ($risultato['ErrorList'] as $key => $value) {
                $msgErr .= $key . ": $value ";
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $msgErr;
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }
    
    /**
     * 
     * @param type $param array('NumeroProtocollo', 'AnnoProtocollo', 'Allegati')
     */
    public function AggiungiAllegati($param) {
        return $ritorno;
    }

}

?>