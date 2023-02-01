<?php

/**
 *
 * Classe per collegamento con il CART
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaSelect
 * @author     Simone Franchi <simone.franchi@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    07.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoapmime.php');

class itaCartServiceClient {
//    private $namespace = "http://159.213.89.84:8080/cart/IntegrationManager?wsdl";
    private $webservices_wsdl = "";
    private $erog_namespacePrefix = "star";
    private $erog_uri = "";
    private $erog_username = "";
    private $erog_password = "";
    private $erog_timeout = "";
    private $frui_namespacePrefix = "star";
    private $frui_uri = "";
    private $frui_username = "";
    private $frui_password = "";
    private $frui_timeout = "";
    private $max_execution_time = 600;
    private $result;
    private $error;
    private $fault;
    private $pdelegata = "";
    private $codEnte = "";
    private $attachments = array();

    
    function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    function getErog_namespacePrefix() {
        return $this->erog_namespacePrefix;
    }

    function getErog_uri() {
        return $this->erog_uri;
    }

    function getErog_username() {
        return $this->erog_username;
    }

    function getErog_password() {
        return $this->erog_password;
    }

    function getErog_timeout() {
        return $this->erog_timeout;
    }

    function getFrui_namespacePrefix() {
        return $this->frui_namespacePrefix;
    }

    function getFrui_uri() {
        return $this->frui_uri;
    }

    function getFrui_username() {
        return $this->frui_username;
    }

    function getFrui_password() {
        return $this->frui_password;
    }

    function getFrui_timeout() {
        return $this->frui_timeout;
    }

    function getMax_execution_time() {
        return $this->max_execution_time;
    }

    function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    function setErog_namespacePrefix($erog_namespacePrefix) {
        $this->erog_namespacePrefix = $erog_namespacePrefix;
    }

    function setErog_uri($erog_uri) {
        $this->erog_uri = $erog_uri;
    }

    function setErog_username($erog_username) {
        $this->erog_username = $erog_username;
    }

    function setErog_password($erog_password) {
        $this->erog_password = $erog_password;
    }

    function setErog_timeout($erog_timeout) {
        $this->erog_timeout = $erog_timeout;
    }

    function setFrui_namespacePrefix($frui_namespacePrefix) {
        $this->frui_namespacePrefix = $frui_namespacePrefix;
    }

    function setFrui_uri($frui_uri) {
        $this->frui_uri = $frui_uri;
    }

    function setFrui_username($frui_username) {
        $this->frui_username = $frui_username;
    }

    function setFrui_password($frui_password) {
        $this->frui_password = $frui_password;
    }

    function setFrui_timeout($frui_timeout) {
        $this->frui_timeout = $frui_timeout;
    }

    function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
    }

    function getPdelegata() {
        return $this->pdelegata;
    }

    function getCodEnte() {
        return $this->codEnte;
    }

    function setPdelegata($pdelegata) {
        $this->pdelegata = $pdelegata;
    }

    function setCodEnte($codEnte) {
        $this->codEnte = $codEnte;
    }

    
    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $uri, $user, $password, $param = array()) {
        $this->clearResult();
        $client = new nusoap_client_mime($uri, false);
        //$client = new nusoap_client_mime($this->erog_uri, false);
        $client->timeout = 500;
        $client->response_timeout = 500;
        $client->soap_defencoding = 'UTF-8';
        $client->debugLevel = 0;
        $client->setCredentials($user, $password);
        // $client->setCredentials($this->erog_username, $this->erog_password);
        $result = $client->call("ser:".$operationName, $param, array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            "ser" => "http://services.pdd.openspcoop.org"
                )
        );

//        Out::msgInfo("User - Password" , $user . " - " . $password);
        
        //Out::msgInfo("request ws_call" , htmlspecialchars($client->request));
        //Out::msgInfo("response ws_call" , htmlspecialchars($client->response));
        
        if ($client->fault) {
//            Out::msgInfo("faultstring ws_call" , htmlspecialchars($client->faultstring));
            $this->fault = $client->faultstring;
            return false;
        } else {
//            Out::msgInfo("getError ws_call" , htmlspecialchars($client->getError));
            
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        
        
        return true;
    }

    /**
     * 
     * @param type $operationName -> nome dello stimolo da invocare
     * @param type $uri -> Url del Servizio
     * @param type $user -> Nome utente
     * @param type $password -> password
     * @param type $param -> Stringa XML con il messaggio da inviare
     * @param type $arrayFileAttach -> Array con i files da attaccare al messaggio
     * @return boolean
     */
    private function ws_callFruizione($operationName, $param = '', $arrayFileAttach = array()) {
        $uri = $this->frui_uri;
        $user = $this->frui_username;
        $password = $this->frui_password;
        
        $this->clearResult();
        $client = new nusoap_client_mime($uri, false);
        //$client = new nusoap_client_mime($this->erog_uri, false);
        $client->timeout = 500;
        $client->response_timeout = 500;
        $client->soap_defencoding = 'UTF-8';
        $client->debugLevel = 0;
        $client->setCredentials($user, $password);
        // $client->setCredentials($this->erog_username, $this->erog_password);

        
        if ($arrayFileAttach){
            foreach($arrayFileAttach as $fileAllegato){
                $nomeFile = $fileAllegato['FILEORIG'];
                $nomeFileCompleto = $fileAllegato['FILEPATH'];
                $contentType = $fileAllegato['CONTENTTYPE'];
                $cid = $fileAllegato['CID'];
                $dataFile = file_get_contents($nomeFileCompleto);
                $client->addAttachment($dataFile, $nomeFile, $contentType, $cid);
                //$client->addAttachment($dataFile, $filename = 'prova.pdf.p7m', $contenttype = 'application/x-pkcs7-mime', $cid = 'abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org');

//                $dataFile = file_get_contents("D:/ItalSoft/CART/prova.pdf.p7m");
//                $client->addAttachment($dataFile, $filename = 'prova.pdf.p7m', $contenttype = 'application/x-pkcs7-mime', $cid = 'abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org');
                
            }
        }
       
        
        //DA ATTIVARE SE SI fa inviaStimoloRequest per comunicazione
//        if ($operationName == 'inviaStimoloRequest'){
//            $dataFile = file_get_contents("D:/ItalSoft/CART/prova.pdf.p7m");
//            $client->addAttachment($dataFile, $filename = 'prova.pdf.p7m', $contenttype = 'application/x-pkcs7-mime', $cid = 'abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org');
//        }
        
//        Out::msgInfo("Parametro", htmlspecialchars($param));
//        Out::msgInfo("Operazione", $operationName);
        
        $result = $client->call("proc:".$operationName, $param, array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            "proc" => "http://www.suap.regione.toscana.it/sem/types/procedimento"
                ) , ' '
        );

//        $result = $client->call("proc:".$operationName, $param, array(
//            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
//            "proc" => "http://www.suap.regione.toscana.it/sem/types/procedimento"
//                ),'OpenSPCoop_PDRequest'
//        );
        
        
//        Out::msgInfo("request wscallFruizione" , htmlspecialchars($client->request));
//        Out::msgInfo("response wscallFruizione" , htmlspecialchars($client->response));
        
        if ($client->fault) {
            $this->fault = $client->faultstring;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        
        //$client->getAttachments();
        $this->attachments = $client->getAttachments();
        //file_put_contents("D:/works/testxxx.pdf.p7m",$this->attachments[0]['data']);
        // Out::msgInfo("Allegati", print_r($client->getAttachments(), true));

        return true;
    }


    /**
     * 
     * @param type $operationName -> nome dello stimolo da invocare
     * @param type $uri -> Url del Servizio
     * @param type $user -> Nome utente
     * @param type $password -> password
     * @param type $param -> Stringa XML con il messaggio da inviare
     * @param type $arrayFileAttach -> Array con i files da attaccare al messaggio
     * @return boolean
     */
    private function ws_callFruizioneTest($operationName, $uri, $user, $password, $param = '', $arrayFileAttach = array()) {
        $this->clearResult();
        $client = new nusoap_client_mime($uri, false);
        //$client = new nusoap_client_mime($this->erog_uri, false);
        $client->timeout = 500;
        $client->response_timeout = 500;
        $client->soap_defencoding = 'UTF-8';
        $client->debugLevel = 0;
        $client->setCredentials($user, $password);
        // $client->setCredentials($this->erog_username, $this->erog_password);
        
        if ($arrayFileAttach){
            foreach($arrayFileAttach as $fileAllegato){
                //TODO: Fare Attachment del File (Vedi Sotto)
            }
        }
        
        //DA ATTIVARE SE SI fa inviaStimoloRequest per comunicazione
        if ($operationName == 'inviaStimoloRequest'){
            $dataFile = file_get_contents("D:/ItalSoft/CART/prova.pdf.p7m");
            $client->addAttachment($dataFile, $filename = 'prova.pdf.p7m', $contenttype = 'application/x-pkcs7-mime', $cid = 'abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org');
        }
        
        //Out::msgInfo("Parametro", htmlspecialchars($param));
        
        $result = $client->call("proc:".$operationName, $param, array(
            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
            "proc" => "http://www.suap.regione.toscana.it/sem/types/procedimento"
                ), ' '
        );

//        $result = $client->call("proc:".$operationName, $param, array(
//            "soapenv" => "http://schemas.xmlsoap.org/soap/envelope/",
//            "proc" => "http://www.suap.regione.toscana.it/sem/types/procedimento"
//                ),'OpenSPCoop_PDRequest'
//        );
        
        
        Out::msgInfo("request wscallFruizione_test" , htmlspecialchars($client->request));
        Out::msgInfo("response wscallFruizione_test" , htmlspecialchars($client->response));
        
        if ($client->fault) {
            $this->fault = $client->faultstring;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        
        //$client->getAttachments();
        $this->attachments = $client->getAttachments();
        file_put_contents("D:/works/testxxx.pdf.p7m",$this->attachments[0]['data']);
        // Out::msgInfo("Allegati", print_r($client->getAttachments(), true));

        return true;
    }

    
    public function getAttachments(){
        return $this->attachments;
    }


    
    public function ws_getAllMessagesId() {
        return $this->ws_call('getAllMessagesId', $this->erog_uri, $this->erog_username, $this->erog_password);
    }

    public function ws_getMessage($param) {
//        $param = array(
//            'user' => $this->getUsername(),
//            'pwd' => $this->getPassword(),
//        );

        return $this->ws_call('getMessage', $this->erog_uri, $this->erog_username, $this->erog_password, $param);
    }


    public function ws_richiediAllegato($param) {

        return $this->ws_call('OpenSPCoop_PD', $this->erog_uri, $this->frui_username, $this->frui_password, $param);
    }
    
    public function ws_Invia($param) {
        return $this->ws_call('SetStatoPratica', $this->erog_username, $this->erog_password, $param);
    }
    
    public function ws_fruizioneTest($param, $operation, $arrayFileAttach = array()) {
        //Out::msgInfo("Parametro", htmlentities($param,true));
        //Out::msgInfo("Collegameto", "URL =" . $this->frui_uri . " Utente = " . $this->frui_username . " Password = " . $this->frui_password);
        return $this->ws_callFruizioneTest($operation, $this->frui_uri, $this->frui_username, $this->frui_password, $param, $arrayFileAttach);
//        return $this->ws_callFruizione('aggiornaStatoPraticaReq', $this->frui_uri, $this->frui_username, $this->frui_password, $param);
    }

    public function ws_fruizione($param, $operation, $arrayFileAttach = array()) {
        return $this->ws_callFruizione($operation, $param, $arrayFileAttach);
//        return $this->ws_callFruizione('aggiornaStatoPraticaReq', $this->frui_uri, $this->frui_username, $this->frui_password, $param);
    }

    
}
