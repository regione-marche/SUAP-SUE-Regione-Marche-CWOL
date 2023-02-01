<?php

require_once('../lib/wsServer.class.php');
wsServer::load();


//
// Dichiaro i namespaces da usare
//
define("NAME_SPACE", "http://www.italsoft-mc.it/ws/praWsAG");
define("DEFAULT_NAME_SPACE", "http://www.w3.org/2001/XMLSchema");

$name = "praWsAG";
/* @var $server wsServer */
$server = wsServer::getWsServerInstance($name, NAME_SPACE);

//
// Dichiaro i Metodi
//
$server->register('GetRichiestaDatiAG', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'idSUAP' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
    'hashRichiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetRichiestaAllegatoForRowid', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

//
// CallBack Functions
//
function GetRichiestaDatiAG($Token, $DomainCode, $idSUAP, $NumeroRichiesta, $AnnoRichiesta, $hashRichiesta, $idSUAP) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    App::setDebugFile("/tmp/GetRichiestaDati.log");
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $richiestaAgenzia='CNA';
    $ret = $wsAgent->GetRichiestaDati($NumeroRichiesta, $AnnoRichiesta, $richiestaAgenzia, $hashRichiesta, $idSUAP);
    if (!$ret) {
        return new soap_fault('ERR-GETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetRichiestaAllegatoForRowid($Token, $DomainCode, $rowid) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetRichiestaAllegatoForRowid($rowid);
    if (!$ret) {
        return new soap_fault('ERR-GETRICHIESTAALLEGATOFORROWID', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}


// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
