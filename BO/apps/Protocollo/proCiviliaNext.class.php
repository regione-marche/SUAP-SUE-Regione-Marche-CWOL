<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    12.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPOAuth2Client/itaPHPOAuth2Client.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCiviliaNext/itaPHPCiviliaNextClient.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proCiviliaNext extends proWsClient {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new proCiviliaNext();
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
    private function setClientConfigAuth($CiviliaClient) {
        $devLib = new devLib();
        $idClient = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CLIENTID', false);
        $clientSecret = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CLIENTSECRET', false);
        $urlAccessToken = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'URLACCESSTOKEN', false);
        $scope = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'SCOPE', false);
        //
        $clientParam = array(
            "CLIENTID" => $idClient['CONFIG'],
            "CLIENTSECRET" => $clientSecret['CONFIG'],
            "URLACCESSTOKEN" => $urlAccessToken['CONFIG'],
            "SCOPE" => $scope['CONFIG'],
        );
        $this->setClientParam($clientParam);
        //
        $CiviliaClient->setClientId($idClient['CONFIG']);
        $CiviliaClient->setClientSecret($clientSecret['CONFIG']);
        $CiviliaClient->setUrlAccessToken($urlAccessToken['CONFIG']);
        $CiviliaClient->setScope($scope['CONFIG']);
    }

    private function setClientConfig($CiviliaClient) {
        $devLib = new devLib();
        $endPoint = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'ENDPOINT', false);
        $codOrganigramma = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CODICEORGANIGRAMMA', false);
        $idOperatore = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'IDOPERATORE', false);
        //
        $clientParam = array(
            "ENDPOINT" => $endPoint['CONFIG'],
            "CODICEORGANIGRAMMA" => $codOrganigramma['CONFIG'],
            "IDOPERATORE" => $idOperatore['CONFIG'],
        );
        $this->setClientParam($clientParam);
        //
        $CiviliaClient->setEndpoint($endPoint['CONFIG']);
        $CiviliaClient->setCodiceOrganigramma($codOrganigramma['CONFIG']);
        $CiviliaClient->setIdOperatore($idOperatore['CONFIG']);
    }

    /**
     * 
     */
    function LeggiProtocollo($param) {
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

        if ($param['DataProtocollo']) {
            $param['DataProtocollo'] = date("Y-m-d", strtotime($param['DataProtocollo']));
        }


        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
        $this->setClientConfig($itaCiviliaNextClient);
        $itaCiviliaNextClient->setToken($token);

        $ret = $itaCiviliaNextClient->CercaPratiche($param);

        $risultato = json_decode($ret, true);

        if (!isset($risultato['resultType'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in cerca pratica:<br>" . $risultato['message'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultType'] != "1") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in cerca pratica:<br>" . $risultato['resultDescription'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (!isset($risultato['result'][0])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Protocollo non trovato";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultDescription'] == "OK") {
            if ($risultato['totalCount'] > 1) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione! sono stati trovati piu protocolli corrispondenti ai parametri di ricerca.<br> Verificare.";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            //DATI COMPLETI DI LETTURA DEL PROTOCOLLO
            $ritorno["RetValue"]['Dati'] = $risultato;
            //DATI PER SALVATAGGIO NEI METADATI
            $ritorno["RetValue"]['DatiProtocollazione'] = array(
                'TipoProtocollo' => array('value' => 'CiviliaNext', 'status' => true, 'msg' => $risultato['result'][0]['tipoProtocollo']),
                'proNum' => array('value' => $risultato['result'][0]['numeroProtocollo'], 'status' => true, 'msg' => ''),
                'Data' => array('value' => $risultato['result'][0]['dataProtocollo'], 'status' => true, 'msg' => ''),
                'Anno' => array('value' => date("Y"), 'status' => true, 'msg' => ''),
                'DocNumber' => array('value' => $risultato['result'][0]['idPratica'], 'status' => true, 'msg' => '')
            );

            /*
             * Leggo i corrispondenti
             */
            $mittDest = array();
            foreach ($risultato['result'][0]['idCorrispondentiList'] as $key => $nominativo) {
                $soggetto_rec['Denominazione'] = $nominativo['denominazione'];
                $soggetto_rec['Nome'] = $nominativo['nome'];
                $soggetto_rec['Cognome'] = $nominativo['cognome'];
                $soggetto_rec['IdSoggetto'] = $nominativo['id'];
                $soggetto_rec['CodiceFiscale'] = $nominativo['codiceFiscale'];
                $soggetto_rec['DescrizioneComuneDiNascita'] = $nominativo['cittaNascita'];
                //$soggetto_rec['DataDiNascita'] = substr($anagrafica_rec['DataDiNascita'], 6, 4) . substr($anagrafica_rec['DataDiNascita'], 3, 2) . substr($anagrafica_rec['DataDiNascita'], 0, 2);
                $soggetto_rec['DataDiNascita'] = $nominativo['dataNascita'];
                $soggetto_rec['Sesso'] = $nominativo['sesso'];
                $soggetto_rec['Email'] = $nominativo['email'];
                $soggetto_rec['Indirizzo'] = $nominativo['indirizzo'];
                $soggetto_rec['CapComuneDiResidenza'] = $nominativo['cap'];
                $soggetto_rec['DescrizioneComuneDiResidenza'] = $nominativo['cittaResidenza'];
                $soggetto_rec['NaturaGiuridica'] = $nominativo['tipoIndividuoFG'];
                $mittDest[] = $soggetto_rec;
            }

            $paramAlle = array();
            $paramAlle['Docnumber'] = $risultato['result'][0]['idPratica'];
            $retAllegati = $this->getAllegati($paramAlle, $itaCiviliaNextClient);
            //$retAllegati = $this->getAllegati($param, $itaCiviliaNextClient);
            if ($retAllegati['Status'] == "-1") {
                return $retAllegati;
            }
            $Allegati = $retAllegati['allegati'];
            $arrayDoc = array();
            foreach ($Allegati as $key => $Allegato) {
                $arrayDoc[$key]['Stream'] = $Allegato['file'];
                $arrayDoc[$key]['Estensione'] = pathinfo($Allegato['nomeFile'], PATHINFO_EXTENSION);
                //$arrayDoc[$key]['SottoEstensione'] = $Allegato['SottoEstensione'];
                $arrayDoc[$key]['NomeFile'] = $Allegato['nomeFile'];
                $arrayDoc[$key]['Note'] = $Allegato['descrizione'];
            }

            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['nomeFile'] . " (" . $Allegato['descrizione'] . ")";
            }

            switch ($risultato['result'][0]['tipoProtocollo']) {
                case "Uscita":
                    $origine = "P";
                    break;
                case "Ingresso":
                    $origine = "A";
                    break;
                case "Interno":
                    $origine = "I";
                    break;
            }

            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'CiviliaNext',
                'NumeroProtocollo' => $risultato['result'][0]['numeroProtocollo'],
                'Data' => $risultato['result'][0]['dataProtocollo'],
                'DocNumber' => $risultato['result'][0]['idPratica'],
                'Segnatura' => '',
                'Anno' => substr($risultato['result'][0]['dataProtocollo'], 0, 4),
                'Classifica' => "",
                'Oggetto' => $risultato['result'][0]['oggetto'],
                'Origine' => $origine,
                'DocumentiAllegati' => $DocumentiAllegati,
                'MittentiDestinatari' => $mittDest,
                'NumeroFascicolo' => "",
                'Allegati' => $arrayDoc,
            );
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore in cerca pratica:<br>" . $risultato['resultDescription'];
            $ritorno["RetValue"] = false;
        }

        return $ritorno;
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi, $origine = "A") {
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

        $listaCorrispondenti = array();
        if ($origine == "A") {
            $param['tipoProtocollo'] = "INGRESSO";

            /*
             * Mittente
             */
            $denom = $elementi['dati']['MittDest']['Denominazione'];
            if ($denom == "") {
                $denom = $elementi['dati']['MittDest']['Cognome'] . " " . $elementi['dati']['MittDest']['Nome'];
            }
            $listaCorrispondenti[0]['denominazione'] = htmlspecialchars(utf8_encode($denom), ENT_COMPAT, 'UTF-8');
            $listaCorrispondenti[0]['email'] = $elementi['dati']['MittDest']['Email'];
            $listaCorrispondenti[0]['tipoIndividuoProtocollo'] = "noncertificato";
            $listaCorrispondenti[0]['nome'] = $elementi['dati']['MittDest']['Nome'];
            $listaCorrispondenti[0]['cognome'] = $elementi['dati']['MittDest']['Cognome'];
            $listaCorrispondenti[0]['codiceFiscale'] = $elementi['dati']['MittDest']['CF'];
            $listaCorrispondenti[0]['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
            $param['CorrispondentiList'] = $listaCorrispondenti;
        }if ($origine == "P") {
            $param['tipoProtocollo'] = "USCITA";

            /*
             * Destinatari
             */
            foreach ($elementi['dati']['destinatari'] as $key => $dest) {
                $listaCorrispondenti[$key]['denominazione'] = htmlspecialchars(utf8_encode($dest['Denominazione']), ENT_COMPAT, 'UTF-8');
                $listaCorrispondenti[$key]['email'] = $dest['Email'];
                $listaCorrispondenti[$key]['tipoIndividuoProtocollo'] = "noncertificato";
                $listaCorrispondenti[$key]['nome'] = $dest['dati']['MittDest']['Nome'];
                $listaCorrispondenti[$key]['cognome'] = $dest['dati']['MittDest']['Cognome'];
                $listaCorrispondenti[$key]['codiceFiscale'] = $dest['dati']['MittDest']['CF'];
                $listaCorrispondenti[$key]['indirizzo'] = $dest['dati']['MittDest']['Indirizzo'];
            }
            $param['CorrispondentiList'] = $listaCorrispondenti;
        }

        /*
         * Allegati
         */
        $listaAllegati = array();
        if (isset($elementi['dati']['DocumentoPrincipale']) && $elementi['dati']['DocumentoPrincipale']) {
            $listaAllegati[0]["nomeFile"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["file"] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $listaAllegati[0]["mimeType"] = itaMimeTypeUtils::estraiEstensione($elementi['dati']['DocumentoPrincipale']['Nome']);
            $listaAllegati[0]["titolo"] = "";
            $listaAllegati[0]["descrizione"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["isPrincipale"] = 1;
            $listaAllegati[0]["idSingolaFattura"] = "";
        } else {
            $listaAllegati[0]["nomeFile"] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["file"] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
            $listaAllegati[0]["mimeType"] = itaMimeTypeUtils::estraiEstensione($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']);
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
            $listaAllegati[$j]["mimeType"] = itaMimeTypeUtils::estraiEstensione($doc['Documento']['Nome']);
            $listaAllegati[$j]["titolo"] = "";
            $listaAllegati[$j]["descrizione"] = htmlspecialchars(utf8_encode($doc['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[$j]["isPrincipale"] = 0;
            $listaAllegati[$j]["idSingolaFattura"] = "";
            $j++;
        }
        $param['AllegatiList'] = $listaAllegati;

        $param['protocollatoDa'] = "ALTRO"; // webappfatturazione|civiliaweb|civiliaopen

        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
        $this->setClientConfig($itaCiviliaNextClient);
        $itaCiviliaNextClient->setToken($token);

//        $retAssegna = $this->assegnaPratica($itaCiviliaNextClient, "123");
//        $ritorno["Status"] = "-1";
//        $ritorno["Message"] = "LOG";
//        $ritorno["RetValue"] = false;
//        return $ritorno;


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
                $ritorno["errString"] = $retAssegna['Message'];
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
        //Out::msgInfo("risu", print_r($ritorno, true));
        return $ritorno;
    }

    /**
     * 
     */
    public function AggiungiAllegati($elementi) {
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

        $param['Anno'] = $elementi['AnnoProtocollo'];
        $param['Numero'] = $elementi['NumeroProtocollo'];
        $param['codiceAOO'] = "A01"; //$elementi['codiceAOO'];

        $listaAllegati = array();
        if (isset($elementi['arrayDoc']['Principale']) && $elementi['arrayDoc']['Principale']) {
            $listaAllegati[0]["nomeFile"] = $elementi['arrayDoc']['Principale']['Nome'];
            $listaAllegati[0]["file"] = $elementi['arrayDoc']['Principale']['Stream'];
            $listaAllegati[0]["mimeType"] = itaMimeTypeUtils::estraiEstensione($elementi['arrayDoc']['Principale']['Nome']);
            $listaAllegati[0]["titolo"] = "";
            $listaAllegati[0]["descrizione"] = htmlspecialchars(utf8_encode($elementi['arrayDoc']['Principale']['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[0]["isPrincipale"] = 1;
        }

        $j = 1;
        foreach ($elementi['arrayDoc']['Allegati'] as $key => $doc) {
            $listaAllegati[$j]["nomeFile"] = $doc['Documento']['Nome'];
            $listaAllegati[$j]["file"] = $doc['Documento']['Stream'];
            $listaAllegati[$j]["mimeType"] = itaMimeTypeUtils::estraiEstensione($doc['Documento']['Nome']);
            $listaAllegati[$j]["titolo"] = "";
            $listaAllegati[$j]["descrizione"] = htmlspecialchars(utf8_encode($doc['Descrizione']), ENT_COMPAT, 'UTF-8');
            $listaAllegati[$j]["isPrincipale"] = 0;
            $j++;
        }
        $param['AllegatiList'] = $listaAllegati;

        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
        $this->setClientConfig($itaCiviliaNextClient);
        $itaCiviliaNextClient->setToken($token);

        $ret = $itaCiviliaNextClient->AllegaAPratica($param);

        $risultato = json_decode($ret, true);
//        Out::msgInfo("ris", print_r($risultato, true));
//        $ritorno["Status"] = "-1";
//        $ritorno["Message"] = "LOG";
//        $ritorno["RetValue"] = false;
//        return $ritorno;

        $strError = "";
        if (isset($risultato[0])) {
            foreach ($risultato as $errore) {
                $strError .= $errore . "<br>";
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore Aggiunta Allegati:<br>$strError";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato["statusCode"] != "200") {
            foreach ($risultato["errorList"] as $codice => $err) {
                $strError .= $risultato["statusCode"] . ": " . $codice . "-->" . $err . "<br>";
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore Aggiunta Allegati:<br>$strError";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Inseriti correttamente " . count($param['AllegatiList']) . " allegati.";
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

    public function getAllegati($param, $itaCiviliaNextClient) {
        $elementi = array();
        $elementi['idPratica'] = $param['Docnumber'];
        $elementi['getByteArray'] = true;

        $ret = $itaCiviliaNextClient->GetAllegati($elementi);

        $risultato = json_decode($ret, true);

        if (!isset($risultato['resultType'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore nel cercare gli allegati:<br>" . $risultato['message'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultType'] != "1") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore nel cercare gli allegati:<br>" . $risultato['resultDescription'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($risultato['resultDescription'] == "OK") {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Allegati trovati correttamente";
            $ritorno["RetValue"] = true;
            $ritorno["allegati"] = $risultato['result'];
            return $ritorno;
        }
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_CIVILIANEXT;
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
