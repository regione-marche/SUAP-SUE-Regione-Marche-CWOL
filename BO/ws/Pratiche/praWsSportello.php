<?php

require_once '../lib/wsSportello.class.php';
wsSportello::load();

//
// Dichiaro i namespaces da usare
//

define('NAME_SPACE', 'http://www.italsoft-mc.it/ws/praWsSportello');
define('DEFAULT_NAME_SPACE', 'http://www.w3.org/2001/XMLSchema');

$wsName = 'praWsSportello';
/* @var $server wsSportello */
$server = wsSportello::getWsSportelloInstance($wsName, NAME_SPACE);

/*
 * DEFINIZIONE TIPI COMPLESSI
 */

/*
 * Dati Richiesta
 */
$server->wsdl->addComplexType('datiRichiesta', 'complexType', 'struct', 'all', '', array(
    'codiceSportello' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceSportello', 'type' => 'xsd:string'),
    'codiceAggregato' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceAggregato', 'type' => 'xsd:string'),
    'codiceProcedimento' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceProcedimento', 'type' => 'xsd:string'),
    'codiceEvento' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceEvento', 'type' => 'xsd:string'),
    'esibente' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'esibente', 'type' => 'tns:esibente')
));

/*
 * Esibente
 */
$server->wsdl->addComplexType('esibente', 'element', 'struct', 'all', '', array(
    'nome' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'nome', 'type' => 'xsd:string'),
    'cognome' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'cognome', 'type' => 'xsd:string'),
    'codiceFiscale' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceFiscale', 'type' => 'xsd:string')
));

/*
 * Dato Aggiuntivo Richiesta
 */
$server->wsdl->addComplexType('datoAggiuntivo', 'element', 'struct', 'all', '', array(
    'chiave' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'chiave', 'type' => 'xsd:string'),
    'valore' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'valore', 'type' => 'xsd:string'),
    'dataset' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'dataset', 'type' => 'xsd:string')
));

/*
 * Dati Aggiuntivi Richiesta
 */
$server->wsdl->addComplexType('datiAggiuntivi', 'complexType', 'struct', 'sequence', '', array(
    'datoAggiuntivo' => array('name' => 'datoAggiuntivo', 'type' => 'tns:datoAggiuntivo', 'minOccurs' => 1, 'maxOccurs' => 'unbounded')
));

/*
 * Allegato Richiesta
 */
$server->wsdl->addComplexType('allegatoRichiesta', 'element', 'struct', 'all', '', array(
    'id' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'id', 'type' => 'xsd:string'),
    'hash' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'hash', 'type' => 'xsd:string'),
    'nomeFile' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'nomeFile', 'type' => 'xsd:string'),
    'note' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'note', 'type' => 'xsd:string'),
    'noteAggiuntive' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'noteAggiuntive', 'type' => 'xsd:string'),
    'allegatoPrincipale' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'allegatoPrincipale', 'type' => 'xsd:string')
));

/*
 * Allegati Richiesta
 */
$server->wsdl->addComplexType('allegatiRichiesta', 'complexType', 'struct', 'sequence', '', array(
    'allegatoRichiesta' => array('name' => 'allegatoRichiesta', 'type' => 'tns:allegatoRichiesta', 'minOccurs' => 1, 'maxOccurs' => 'unbounded')
));

//
// Dichiaro i Metodi
//

$server->register(
    'LeggiDati', array(
    'token' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'codiceRichiesta' => 'xsd:string'
    ), array(
    'result' => 'xsd:string'
    ), $ns
);

$server->register('InsertDocumentoRichiesta', array(
    'token' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'nomeFile' => 'xsd:string',
    'stream' => 'xsd:string',
    'impronta' => 'xsd:string'
    ), array(
    'id' => 'xsd:string',
    'hash' => 'xsd:string'
    ), $ns);

$server->register('PutRichiesta', array(
    'token' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'datiRichiesta' => 'tns:datiRichiesta',
    'datiAggiuntivi' => 'tns:datiAggiuntivi',
    'allegatiRichiesta' => 'tns:allegatiRichiesta'
    ), array(
    'codiceRichiesta' => 'xsd:string'
    ), $ns);

function LeggiDati($token, $domainCode, $codiceRichiesta = '') {
    global $server;

    $retLogin = $server->login($token, $domainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    $resLoad = $server::loadPlugins($domainCode);
    if ($resLoad instanceof nusoap_fault) {
        return $resLoad;
    }

    require_once ITA_SUAP_PATH . '/SUAP_praMup/praMup.php';
    $praMup = new praMup();

    return print_r($praMup->prendiDati($codiceRichiesta), true);
}

function InsertDocumentoRichiesta($token, $domainCode, $nomeFile = '', $stream = '', $impronta = '') {
    global $server;

    $retLogin = $server->login($token, $domainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    $resLoad = $server::loadPlugins($domainCode);
    if ($resLoad instanceof nusoap_fault) {
        return $resLoad;
    }

    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praWsAgent.php';
    /* @var $wsAgent praWsAgent */
    $wsAgent = new praWsAgent;
    $returnWSAgent = $wsAgent->InsertDocumentoRichiesta($nomeFile, $stream, $impronta);

    if ($returnWSAgent === false) {
        return new nusoap_fault('ERR-INSERTDOCUMENTORIC', get_class($wsAgent), $wsAgent->getErrMessage());
    }

    return $returnWSAgent;
}

function PutRichiesta($token, $domainCode, $datiRichiesta, $datiAggiuntivi, $allegatiRichiesta) {
    global $server;

    $retLogin = $server->login($token, $domainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    $resLoad = $server::loadPlugins($domainCode);
    if ($resLoad instanceof nusoap_fault) {
        return $resLoad;
    }

    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praWsAgent.php';
    /* @var $wsAgent praWsAgent */
    $wsAgent = new praWsAgent;
    $returnWSAgent = $wsAgent->PutRichiesta($datiRichiesta, $datiAggiuntivi['datoAggiuntivo'], $allegatiRichiesta['allegatoRichiesta'], wsSportello::$userCode);

    if ($returnWSAgent === false) {
        ob_clean();
        return new nusoap_fault('ERR-PUTRICHIESTA', get_class($wsAgent), $wsAgent->getErrMessage());
    }

    return $returnWSAgent;
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
