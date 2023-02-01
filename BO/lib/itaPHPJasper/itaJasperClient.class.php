<?php

/**
 *
 * itaJasperClient
 *
 * @author   Michele Moscioni
 * @version  1.0
 * @access   public
 */

//require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
//require_once(ITA_LIB_PATH . '/nusoap/nusoapmime.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/xmlManager.class.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/resourceDescriptor.class.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/operationResult.class.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/resourceProperty.class.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/requestDescriptor.class.php');
require_once('SOAP/Client.php');

class itaJasperClient {

   
    //private $namespace = "http://www.jaspersoft.com/namespaces/php";
    private $namespace = "http://www.jaspersoft.com/client";    
    private $webservices_uri = "http://localhost:8080/jasperserver/services/repository";
    private $username = "jasperadmin";
    private $password = "jasperadmin";
    private $max_execution_time = 3600;
    private $jr_locale_default = 'it';
    private $result;
    private $attachments;
    private $error;
    private $fault;

    private function clearResult() {
        $this->result = null;
        $this->attachments = null;
        $this->error = null;
        $this->fault = null;
    }

    public function getResult() {
        return $this->result;
    }

    public function getAttachments() {
        return $this->attachments;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setpassword($password) {
        $this->password = $password;
    }

    private function ws_call_nusoap($request) {
        $this->clearResult;
        $client = new nusoap_client_mime($this->webservices_uri, false, false, false, false, false, $this->max_execution_time, $this->max_execution_time);
        $client->setCredentials($this->username, $this->password);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $client->debugLevel = 0;
        $params = array("request" => $request->toXML());
        $attachments = $request->getAttachments();
        if ($attachments) {
            foreach ($attachments as $key => $attachment) {
                $data = "";
                $contenttype = "";
                $cid = "";
                foreach ($attachment as $type => $value) {
                    switch ($type) {
                        case 'data':
                            $data = $value;
                            break;
                        case 'file':
                            $fh = fopen($value, "rb");
                            $data = fread($fh, filesize($value));
                            fclose($fh);
                            break;
                        case 'contentype':
                            $contenttype = $value;
                            break;
                        case 'cid':
                            $cid = $value;
                            break;
                    }
                    //$client->addAttachment($data,$contenttype,$cid);
                }
                $client->addAttachment($data, $contenttype, $cid);
            }
        }
//        if ($request->getOperationName() == "runReport") {
//            App::log("request nusoap:");
//            App::log($params);
//        }
        $response = $client->call($request->getOperationName(), $params, $this->namespace);
        if ($client->fault) {
            $this->fault = $client->fault;
            throw new Exception("Request Fault:" . $this->fault);
            return false;
        } else {
            if ($client->getError()) {
                $this->error = $client->error;
                throw new Exception("Request Error:" . $client->getError());
                return false;
            } else {
                if ($request->getOperationName() == "get" || $request->getOperationName() == "runReport") {
                    $this->attachments = $client->getAttachments();
                }

                $xml = new xmlManager($response);
                $this->result = $xml->getOperationResultFormXml();
                return true;
            }
        }
    }
    
    
    private function ws_call($request) {
        $this->clearResult;
        $connection_params = array("user" => $this->username, "pass" => $this->username, "timeout" => $this->max_execution_time);
        $client = new SOAP_client($this->webservices_uri, false, false, $connection_params);
        $params = array("request" => $request->toXML());
        $attachments = $request->getAttachments();
        if ($attachments) {
            $soapAttachments = array();
            foreach ($attachments as $key => $attachment) {
                $data = "";
                $contenttype = "application/octet-stream";
                $cid = "123456";
                foreach ($attachment as $type => $value) {
                    switch ($type) {
                        case 'data':
                            $data = $value;
                            break;
                        case 'file':
                            $fh = fopen($value, "rb");
                            $data = fread($fh, filesize($value));
                            fclose($fh);
                            break;
                        case 'contentype':
                            $contenttype = $value;
                            break;
                        case 'cid':
                            $cid = $value;
                            break;
                    }
                }
                $soapAttachments[] = array("body" => $data, "content_type" => $contenttype, "cid" => $cid);
            }
            $client->_options['attachments'] = 'Dime';
            $client->_attachments = array($soapAttachments);
        }

//        if ($request->getOperationName() == "runReport") {
//            App::log("Parametri:");
//            App::log($params);
//        }
        $response = $client->call($request->getOperationName(), $params, $this->namespace);
//        if ($request->getOperationName() == "get") {
//            App::log("Response:");
//            App::log($response);
//            App::log("Attach:");
//            App::log($client->_soap_transport->attachments);
//        }

        if (is_object($response) && get_class($response) == 'SOAP_Fault') {
            $this->fault = $response->getFault()->faultstring;
            throw new Exception("Request Fault:" . $this->fault);
            return false;
        } else {
            if ($request->getOperationName() == "get" || $request->getOperationName() == "runReport") {
                $this->attachments = array(array('data' => $client->_soap_transport->attachments["cid:report"]));
            }
            $xml = new xmlManager($response);
            $this->result = $xml->getOperationResultFormXml();
            return true;
        }
    }

    public function ws_checkUsername() {
        $request = new requestDescriptor();
        $rd = new resourceDescriptor();

        $rd->setName("");
        $rd->setWsType("folder");
        $rd->setUriString("");
        $rd->setIsNew("false");

        $request->setOperationName('list');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments(array());
        $request->setResourceDescriptor($rd);
        return $this->ws_call($request);
    }

    public function ws_list($uri, $args = array()) {
        $request = new requestDescriptor();
        $rd = new resourceDescriptor();

        $rd->setName("");
        $rd->setWsType("folder");
        $rd->setUriString($uri);
        $rd->setIsNew("false");

        $request->setOperationName('list');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments($args);
        $request->setResourceDescriptor($rd);
        return $this->ws_call($request);
    }

    function ws_get($uri, $args = array()) {
        $max_execution_time = $this->max_execution_time;
        $request = new requestDescriptor();
        $rd = new resourceDescriptor();
        $rd->setUriString($uri);

        $request->setOperationName('get');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments($args);
        $request->setResourceDescriptor($rd);
        return $this->ws_call($request);
    }

    function ws_put($resourceDescriptor, $attachments=null,$args=array()) {
        $max_execution_time = $this->max_execution_time;
        $request = new requestDescriptor();

        $request->setOperationName('put');
        $request->setLocale($this->jr_locale_default);
        //$args['USE_DIME_ATTACHMENTS'] = "0";
        $request->setArguments($args);
        $request->setAttachments($attachments);
        $request->setResourceDescriptor($resourceDescriptor);
        return $this->ws_call($request);
    }

    function ws_copy($source_rd, $destination_uri) {
        $request = new requestDescriptor();

        $request->setOperationName('copy');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments(array(resourceDescriptor::DESTINATION_URI => $destination_uri));
        $request->setResourceDescriptor($source_rd);
        return $this->ws_call($request);
    }

    function ws_move($source_rd, $destination_uri) {
        $request = new requestDescriptor();

        $request->setOperationName('move');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments(array(resourceDescriptor::DESTINATION_URI => $destination_uri));
        $request->setResourceDescriptor($source_rd);
        return $this->ws_call($request);
    }

    function ws_delete($source_rd, $args=array()) {
        $request = new requestDescriptor();
        $request->setOperationName('delete');
        $request->setLocale($this->jr_locale_default);
        $request->setArguments($args);
        $request->setResourceDescriptor($source_rd);
        return $this->ws_call($request);
    }

    function ws_runReport($uri, $report_params, $output_params) {
        $max_execution_time = $this->max_execution_time;
        $request = new requestDescriptor();
        $rd = new resourceDescriptor();
        $rd->setUriString($uri);
        $rd->setParameters($report_params);
        $rd->setWsType(resourceDescriptor::TYPE_REPORTUNIT);
        $rd->setIsNew(resourceDescriptor::VALUE_FALSE);
        $request->setOperationName('runReport');
        $request->setLocale($this->jr_locale_default);
        // PEAR
        $output_params["USE_DIME_ATTACHMENTS"] = "<![CDATA[1]]>";
        
        // NUSOAP
        //$output_params["USE_DIME_ATTACHMENTS"] = "<![CDATA[0]]>";
        
        $request->setArguments($output_params);
        $request->setResourceDescriptor($rd);
        return $this->ws_call($request);
    }

}

?>
