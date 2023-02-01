<?php

/**
 *
 * Classe per collegamento Task services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaTask
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    21.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaGetPost.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaGetType.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaGetMetaValue.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaTaxonomy.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaUser.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaDeletePost.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaInsertPost.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaUpdatePost.class.php');
require_once(ITA_BASE_PATH . '/lib/itaPHPTaskTrasp/itaGetPosts.class.php');
require_once(ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');

class itaTaskClient {

    private $nameSpaces = array('urn' => "urn:localhost-hw");
    private $namespace;
    private $namespacePrefix = "urn";
    private $webservices_uri;
    private $webservices_wsdl;
    private $max_execution_time;
    private $result;
    private $error;
    private $fault;

    public function __construct() {
        $devLib = new devLib();
        
        $parmWs = $devLib->getEnv_config('AMMT', 'codice', 'WSAMMTNAMESPACE', false);
        $this->setNamespace($parmWs['CONFIG']);

        $parmWs = $devLib->getEnv_config('AMMT', 'codice', 'WSAMMTENDPOINT', false);
        $this->setWebservices_uri($parmWs['CONFIG']);
        
        $parmWs = $devLib->getEnv_config('AMMT', 'codice', 'WSAMMTWSDL', false);
        $this->setWebservices_wsdl($parmWs['CONFIG']);
        
        $parmWs = $devLib->getEnv_config('AMMT', 'codice', 'WSAMMTTIMEOUT', false);
        $this->setMax_execution_time($parmWs['CONFIG']);
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($nameSpaces) {
        $this->nameSpaces = $nameSpaces;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setMax_execution_time($max_execution_time) {
        $this->max_execution_time = $max_execution_time;
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

    private function ws_call($operationName, $param, $soapAction) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->useHTTPPersistentConnection();
        $client->debugLevel = 0;
        $client->timeout = 200;
        $client->response_timeout = $client->timeout;
        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->namespace . "/" . $operationName;
//        $soapAction = "urn:localhost-hwh#authenticate";
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents("C:/Works/param_.xml", print_r($param, true));
//        file_put_contents("C:/Works/request_.xml", $client->request);
//        file_put_contents("C:/Works/response_.xml", $client->response);
        $time = time();
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
        return $result;
    }

    public function ws_Authenticate($Email, $Password) {
        $EmailSoapval = new soapval('email', 'email', $Email, false, false);
        $PasswordSoapval = new soapval('password', 'password', $Password, false, false);
        $paramL = $EmailSoapval->serialize() . $PasswordSoapval->serialize();
        $soapAction = "urn:localhost-hwh#authenticate";
        return $this->ws_call("urn:authenticate", $paramL, $soapAction);
    }

    public function getPost($token, $id) {
        $GetPost = new itaGetPost();
        $GetPost->setToken($token);
        $GetPost->setPostId($id);
        $soapAction = "urn:localhost-hwh#getPost";
        return $this->ws_call("urn:getPost", $GetPost->getRichiesta(), $soapAction);
    }

    public function getType($token, $type, $def) {
        $GetType = new itaGetType();
        $GetType->setToken($token);
        $GetType->setType($type);
        $GetType->setDef($def);
        $soapAction = "urn:localhost-hwh#getType";
        return $this->ws_call("urn:getType", $GetType->getRichiesta(), $soapAction);
    }

    public function getMetaValue($token, $meta_key) {
        $GetMetaValue = new itaGetMetaValue();
        $GetMetaValue->setToken($token);
        $GetMetaValue->setMeta($meta_key);
        $soapAction = "urn:localhost-hwh#getMetaValue";
        return $this->ws_call("urn:getMetaValue", $GetMetaValue->getRichiesta(), $soapAction);
    }

    public function getTaxonomy($token, $taxonomy) {
        $GetTaxonomy = new itaTaxonomy();
        $GetTaxonomy->setToken($token);
        $GetTaxonomy->setTaxonomy($taxonomy);
        $soapAction = "urn:localhost-hwh#getTaxonomy";
        return $this->ws_call("urn:getTaxonomy", $GetTaxonomy->getRichiesta(), $soapAction);
    }

    public function getUser($token, $email) {
        $GetUser = new itaUser();
        $GetUser->setToken($token);
        $GetUser->setEmail($email);
        $soapAction = "urn:localhost-hwh#getUser";
        return $this->ws_call("urn:getUser", $GetUser->getRichiesta(), $soapAction);
    }

    public function deletePost($token, $id) {
        $deletePost = new itaDeletePost();
        $deletePost->setToken($token);
        $deletePost->setPostId($id);
        $soapAction = "urn:localhost-hwh#deletePost";
        return $this->ws_call("urn:deletePost", $deletePost->getRichiesta(), $soapAction);
    }

    public function InsertPost($token, $valori) {
        $InsertPost = new itaInsertPost();
        $InsertPost->setToken($token);
        $InsertPost->setValori($valori);
        $soapAction = "urn:localhost-hwh#insertPost";
        return $this->ws_call("urn:insertPost", $InsertPost->getRichiesta(), $soapAction);
    }

    public function UpdatePost($token, $id, $valori) {
        $UpdatePost = new itaUpdatePost();
        $UpdatePost->setToken($token);
        $UpdatePost->setValori($valori);
        $UpdatePost->setPostId($id);
        $soapAction = "urn:localhost-hwh#updatePost";
        return $this->ws_call("urn:updatePost", $UpdatePost->getRichiesta(), $soapAction);
    }
    
    public function getPosts($token, $post_meta) {
        $GetPost = new itaGetPosts();
        $GetPost->setToken($token);
        $GetPost->setPostMeta($post_meta);
        $soapAction = "urn:localhost-hwh#getPosts";
        return $this->ws_call("urn:getPosts", $GetPost->getRichiesta(), $soapAction);
    }

    public function insert_extra($token, $name_function, $lista_valori, $def) {
        $tokenValue = new soapval('token', 'token', $token, false, false);
        $nameFunctionValue = new soapval('name_function', 'name_function', $name_function, false, false);
        $listaValoriValue = new soapval('lista_valori', 'lista_valori', $lista_valori, false, false);
        $defValue = new soapval('def', 'def', $def, false, false);
        
        $listaValoriValue->charencoding = false;
        
        $param = $tokenValue->serialize() . $nameFunctionValue->serialize() . $listaValoriValue->serialize() . $defValue->serialize();
        return $this->ws_call("urn:insert_extra", $param, 'urn:localhost-hwh#insert_extra');
    }
    

}
