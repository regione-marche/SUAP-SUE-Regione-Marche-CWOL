<?php

require_once('../lib/wsServer.class.php');
wsServer::load();
//
// Dichiaro i namespaces da usare
//
define("NAME_SPACE", "http://www.italsoft-mc.it/ws/praWsFO");
define("DEFAULT_NAME_SPACE", "http://www.w3.org/2001/XMLSchema");

$name = "praWsFO";
/* @var $server wsServer */
$server = wsServer::getWsServerInstance($name, NAME_SPACE);


/*
 * DEFINIZIONE TIPI COMPLESSI
 */

/*
 * Allegati
 */
$server->wsdl->addComplexType(
        'allegato', 'complexType', 'struct', 'all', '', array(
    'id' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'id', 'type' => 'xsd:string'),
    'tipoFile' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'tipoFile', 'type' => 'xsd:string'),
    'nomeFile' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'nomeFile', 'type' => 'xsd:string'),
    'estensione' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'estensione', 'type' => 'xsd:string'),
    'sha256digest' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'sha256digest', 'type' => 'xsd:string'),
    'stream' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'stream', 'type' => 'xsd:string'),
    'note' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'note', 'type' => 'xsd:string'),
    'classificazione' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'classificazione', 'type' => 'xsd:string')
        )
);

$server->wsdl->addComplexType(
        'allegati', 'complexType', 'array', '', 'SOAP-ENC:Array', array(), array(
    array(
        'ref' => 'SOAP-ENC:arrayType',
        'wsdl:arrayType' => 'tns:allegato[]'
    )
        ), 'tns:allegato'
);

/*
 * Messaggio di risultato
 */
$server->wsdl->addComplexType(
        'messageResult', 'complexType', 'struct', 'all', '', array(
    'descrizione' => array('name' => 'descrizione', 'type' => 'xsd:string'),
    'tipoRisultato' => array('name' => 'tipoRisultato', 'type' => 'xsd:string')
        )
);

/*
 * Elementi Passo
 */
$server->wsdl->addComplexType(
        'datiPasso', 'element', 'struct', 'all', '', array(
    'annotazione' => array('name' => 'annotazione', 'type' => 'xsd:string'),
    'descrizioneTipoPasso' => array('name' => 'descrizioneTipoPasso', 'type' => 'xsd:string'),
    'descrizionePasso' => array('name' => 'descrizionePasso', 'type' => 'xsd:string'),
    'dataApertura' => array('name' => 'dataApertura', 'type' => 'xsd:string'),
    'statoApertura' => array('name' => 'statoApertura', 'type' => 'xsd:string'),
    'dataChiusura' => array('name' => 'dataChiusura', 'type' => 'xsd:string'),
    'statoChiusura' => array('name' => 'statoChiusura', 'type' => 'xsd:string'),
    'pubblicaStatoPasso' => array('name' => 'pubblicaStatoPasso', 'type' => 'xsd:string'),
    'pubblicaAllegati' => array('name' => 'pubblicaAllegati', 'type' => 'xsd:string'),
        )
);

/*
 * destinatari
 */

/*
 * indica un Mittente o un Destinatario esterno
 */
$server->wsdl->addComplexType(
        'destinatario', 'element', 'struct', 'all', '', array(
    'codice' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'codice', 'type' => 'xsd:string'),
    'denominazione' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'nome', 'type' => 'xsd:string'),
    'indirizzo' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'indirizzo', 'type' => 'xsd:string'),
    'cap' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'cap', 'type' => 'xsd:string'),
    'citta' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'citta', 'type' => 'xsd:string'),
    'prov' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'prov', 'type' => 'xsd:string'),
    'codiceFiscale' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'codiceFiscale', 'type' => 'xsd:string'),
    'partitaIva' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'partitaIva', 'type' => 'xsd:string'),
    'telefono' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'telefono', 'type' => 'xsd:string'),
    'fax' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'fax', 'type' => 'xsd:string'),
    'email' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'email', 'type' => 'xsd:string'),
    'pec' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'pec', 'type' => 'xsd:string'),
    'ufficio' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'pec', 'type' => 'xsd:string')
        )
);

/*
 * Elenco dei destinatari
 */
$server->wsdl->addComplexType(
        'destinatari', 'complexType', 'array', '', 'SOAP-ENC:Array', array(), array(
    array(
        'ref' => 'SOAP-ENC:arrayType',
        'wsdl:arrayType' => 'tns:destinatario[]'
    )
        ), 'tns:destinatario'
);

$server->wsdl->addComplexType(
        'filtro', 'element', 'struct', 'all', '', array(
    'chiave' => array('minOccurs' => '0', 'maxOccurs' => '1', 'name' => 'chiave', 'type' => 'xsd:string'),
    'valore' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'valore', 'type' => 'xsd:string'),
        )
);

/*
 * Elenco filtri per ricerca
 */
$server->wsdl->addComplexType(
        'filtri', 'complexType', 'struct', 'sequence', '', array(
    'filtro' => array(
        'name' => 'filtro',
        'type' => 'tns:filtro',
        'minOccurs' => 0,
        'maxOccurs' => "unbounded"
    )
        )
);

//$server->wsdl->addComplexType(
//        'filtri', 'complexType', 'array', '', 'SOAP-ENC:Array', array(), array(
//    array(
//        'ref' => 'SOAP-ENC:arrayType',
//        'wsdl:arrayType' => 'tns:filtro[]'
//    )
//        ), 'tns:filtro'
//);


/*
 * Dati Richiesta
 */
$server->wsdl->addComplexType('datiRichiesta', 'complexType', 'struct', 'all', '', array(
    'codiceSportello' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceSportello', 'type' => 'xsd:string'),
    'codiceProcedimento' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceProcedimento', 'type' => 'xsd:string'),
    'codiceEvento' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'codiceEvento', 'type' => 'xsd:string')
));

/*
 * Dato Aggiuntivo Richiesta
 */
$server->wsdl->addComplexType('datoAggiuntivo', 'element', 'struct', 'all', '', array(
    'chiave' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'chiave', 'type' => 'xsd:string'),
    'valore' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'valore', 'type' => 'xsd:string')
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
    'note' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'note', 'type' => 'xsd:string')
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
$server->register('CtrRichieste', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'anno' => 'xsd:string',
    'procedimento' => 'xsd:string',
    'statoAcquisizioneBO' => 'xsd:string',
    'statoRichieste' => 'xsd:string',
    'statoConfermaAcquisizione' => 'xsd:string',
    'dataConfermaAcquisizione' => 'xsd:string',
    'contestoConfermaAcquisizione' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetRichiestaDati', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetStatoRichiesta', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
    'statoRichiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetStatoAcquisizioneRichiesta', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
    'dataAcquisizioneRichiesta' => 'xsd:string',
    'oraAcquisizioneRichiesta' => 'xsd:string',
    'contestoAcquisizioneRichiesta' => 'xsd:string',
    'infoAcquisizioneRichiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetMarcaturaRichiesta', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
    'numeroProtocollo' => 'xsd:string',
    'dataProtocollo' => 'xsd:string',
    'metadatiProtocollazione' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetErroreProtocollazione', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
    'erroreProtocollazione' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetRichiestaAllegatoForRowid', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('AcquisisciRichiesta', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('RicercaPratiche', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'filtri' => 'tns:filtri',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaDati', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPRORIC', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'chiave' => 'xsd:string',
    'valore' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetRICDOC', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'chiave' => 'xsd:string',
    'valore' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetXMLINFO', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'richiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetXMLINFOAccorpate', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'richiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetBodyFile', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'richiesta' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegatoForRowid', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('AppendPassoPraticaSimple', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string',
    'annotazione' => 'xsd:string',
    'descrizioneTipoPasso' => 'xsd:string',
    'descrizionePasso' => 'xsd:string',
    'dataApertura' => 'xsd:string',
    'statoApertura' => 'xsd:string',
    'dataChiusura' => 'xsd:string',
    'statoChiusura' => 'xsd:string',
    'pubblicaStatoPasso' => 'xsd:string',
    'pubblicaAllegati' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('AppendPassoPraticaArticolo', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string',
    'descrizioneTipoPasso' => 'xsd:string',
    'descrizionePasso' => 'xsd:string',
    'pubblicaAllegatiArticolo' => 'xsd:string',
    'utente' => 'xsd:string',
    'password' => 'xsd:string',
    'categoria' => 'xsd:string',
    'titolo' => 'xsd:string',
    'dadatapubbl' => 'xsd:string',
    'daorapubbl' => 'xsd:string',
    'adatapubbl' => 'xsd:string',
    'aorapubbl' => 'xsd:string',
    'corpo' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

//$server->register('AppendPassoPraticaComplex', array(
//    'itaEngineContextToken' => 'xsd:string',
//    'domainCode' => 'xsd:string',
//    'numeroPratica' => 'xsd:string',
//    'annoPratica' => 'xsd:string',
//    'datiPasso' => 'tns:datiPasso',
//    'allegato' => 'tns:allegato',
//    'destinatari' => 'tns:destinatari'
//        ), array('return' => 'xsd:string'), $ns
//);

$server->register('PutAllegatoPasso', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'chiavePasso' => 'xsd:string',
    'allegato' => 'tns:allegato',
    'pubblicato' => 'xsd:string',
        ), array(
    'return' => 'xsd:string'
        ), $ns, false, 'rpc', 'encoded'
);
/*
 * di ritorno solo messaggio
 */

//$server->register('PutAllegatoPasso', array(
//    'itaEngineContextToken' => 'xsd:string',
//    'domainCode' => 'xsd:string',
//    'chiavePasso' => 'xsd:string',
//    'allegato' => 'tns:allegato',
//    'pubblicato' => 'xsd:string',
//        ), array(
//    'return' => 'xsd:string'
//        ), $ns, false, 'rpc', 'encoded'
//);


$server->register('CancellaPratica', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetElencoPassi', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'stato' => 'xsd:string',
    'responsabile' => 'xsd:string',
    'tipoPasso' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetStatoPasso', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'codicePasso' => 'xsd:string',
    'stato' => 'xsd:string',
    'dataApertura' => 'xsd:string',
    'dataChiusura' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('ResetStatoAcquisizioneRichiesta', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroRichiesta' => 'xsd:string',
    'annoRichiesta' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetUrlPassoPratica', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'chiavePasso' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegatoFromTestoBase', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'nomeFile' => 'xsd:string',
        ), array(
    'stream' => 'xsd:string',
    'filename' => 'xsd:string'
        ), $ns
);

$server->register('SyncFascicolo', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'fascicolo' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SyncPasso', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'passo' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SyncAllegatiInfo', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'passo' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('DeleteAllegatoPasso', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'chiavePasso' => 'xsd:string',
    'allegatoSha2' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SyncAllegatiDelete', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'stream' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);


//
// CallBack Functions
//
function CtrRichieste($Token, $DomainCode, $anno = '', $procedimento = '', $statoAcquisizioneBO = '', $statoRichieste = '', $statoConfermaAcquisizione = '', $dataConfermaAcquisizione = '', $contestoConfermaAcquisizione = '') {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->CtrRichieste($anno, $procedimento, $statoAcquisizioneBO, $statoRichieste, $statoConfermaAcquisizione, $dataConfermaAcquisizione, $contestoConfermaAcquisizione);
    if ($ret === false) {
        return new nusoap_fault('ERR-CTRRICHIESTE', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function SetStatoAcquisizioneRichiesta($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta, $dataAcquisizione, $oraAcquisizione, $contestoAcquisizione, $infoAcquisizione) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SetAcquisizioneRichiesta($NumeroRichiesta, $AnnoRichiesta, $dataAcquisizione, $oraAcquisizione, $contestoAcquisizione, $infoAcquisizione);
    if (!$ret) {
        return new nusoap_fault('ERR-GETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}

function SetMarcaturaRichiesta($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta, $numeroProtocollo, $dataProtocollo, $metadatiProtocollazione) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SetMarcaturaRichiesta($NumeroRichiesta, $AnnoRichiesta, $numeroProtocollo, $dataProtocollo, $metadatiProtocollazione);
    if (!$ret) {
        return new nusoap_fault('ERR-GETMARCATURARICHIESTA', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}

function SetErroreProtocollazione($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta, $erroreProtocollazione) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SetErroreProtocollazione($NumeroRichiesta, $AnnoRichiesta, $erroreProtocollazione);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}

function SetStatoRichiesta($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta, $StatoRichiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SetStatoRichiesta($NumeroRichiesta, $AnnoRichiesta, $StatoRichiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-GETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return 'success';
    }
}

function GetRichiestaDati($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetRichiestaDati($NumeroRichiesta, $AnnoRichiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-GETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetPRORIC($Token, $DomainCode, $Chiave, $Valore) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetPRORIC($Chiave, $Valore);
    if (!$ret) {
        return new nusoap_fault('ERR-GETPRORIC', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetRICDOC($Token, $DomainCode, $Chiave, $Valore) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetRICDOC($Chiave, $Valore);
    if (!$ret) {
        return new nusoap_fault('ERR-GETRICDOC', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetXMLINFO($Token, $DomainCode, $Richiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetXMLINFO($Richiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-GETXMLINFO', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetXMLINFOAccorpate($Token, $DomainCode, $Richiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetXMLINFOAccorpate($Richiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-GETXMLINFOACC', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetBodyFile($Token, $DomainCode, $Richiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetBodyFile($Richiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-GETBODYFILE', get_class($wsAgent), $wsAgent->getErrMessage());
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
        return new nusoap_fault('ERR-GETRICHIESTAALLEGATOFORROWID', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function AcquisisciRichiesta($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    /* @var $wsAgent praWsAgent */
    try {
        $wsAgent = wsModel::getInstance("praWsAgent");
        $ret = $wsAgent->AcquisisciRichiesta($NumeroRichiesta, $AnnoRichiesta);
        if ($ret === false) {
            return new nusoap_fault('ERR-ACQUISISCIRICHIESTA', get_class($wsAgent), htmlentities($wsAgent->getErrMessage()));
        }
    } catch (Exception $exc) {
        return new nusoap_fault('ERR-ACQUISISCIRICHIESTA', get_class($wsAgent), htmlentities($exc->getMessage()));
    }

    return $ret;
}

function RicercaPratiche($Token, $DomainCode, $filtri) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->RicercaPratiche($filtri);
    if (!$ret) {
        return new nusoap_fault('ERR-RICERCAPRATICHE', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetPraticaDati($Token, $DomainCode, $NumeroPratica, $AnnoPratica) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetPraticaDati($NumeroPratica, $AnnoPratica);
    if (!$ret) {
        return new nusoap_fault('ERR-GETPRATICADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function GetPraticaAllegatoForRowid($Token, $DomainCode, $rowid) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetPraticaAllegatoForRowid($rowid);
    if (!$ret) {
        return new nusoap_fault('ERR-GETPRATICAALLEGATOFORROWID', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function AppendPassoPraticaSimple($Token, $DomainCode, $numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $dataApertura, $statoApertura, $dataChiusura, $statoChiusura, $pubblicaStatoPasso, $pubblicaAllegati) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->AppendPassoPraticaSimple($numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $dataApertura, $statoApertura, $dataChiusura, $statoChiusura, $pubblicaStatoPasso, $pubblicaAllegati);
    if (!$ret) {
        return new nusoap_fault('ERR-APPENDPASSOPRATICASIMPLE', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function AppendPassoPraticaArticolo(
        $Token, $DomainCode, $numeroPratica, $annoPratica, $descrizioneTipoPasso, $descrizionePasso, $pubblicaAllegatiArticolo, $utente, $password, $categoria, $titolo, $dadatapubbl, $daorapubbl, $adatapubbl, $aorapubbl, $corpo
) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->AppendPassoPraticaArticolo($numeroPratica, $annoPratica, '', $descrizioneTipoPasso, $descrizionePasso, $pubblicaAllegatiArticolo, $utente, $password, $categoria, $titolo, $dadatapubbl, $daorapubbl, $adatapubbl, $aorapubbl, $corpo);
    if (!$ret) {
        return new nusoap_fault('ERR-APPENDPASSOPRATICAARTICOLO', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

/*
 * In corso di sviluppo
 */

//function AppendPassoPraticaComplex($Token, $DomainCode, $numeroPratica, $annoPratica, $datiPasso, $allegato = array(), $destinatari = array()) {
//    global $server;
//    $retLogin = $server->login($Token, $DomainCode);
//    if ($retLogin !== true) {
//        return $retLogin;
//    }
//    /* @var $wsAgent praWsAgent */
//    $wsAgent = wsModel::getInstance("praWsAgent");
//    $ret = $wsAgent->AppendPassoPraticaPortale($numeroPratica, $annoPratica, $datiPasso, $allegato, $destinatari);
//    if (!$ret) {
//        return new nusoap_fault('ERR-APPENDPASSOPRATICASIMPLE', get_class($wsAgent), $wsAgent->getErrMessage());
//    }
//    return $ret;
//}


function PutAllegatoPasso($Token, $DomainCode, $ChiavePasso, $Allegato, $Pubblicato) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->PutAllegatoPassoForKeypasso($ChiavePasso, $Allegato, $Pubblicato);
    if (!$ret) {
        return new nusoap_fault('ERR-GETPRATICAALLEGATOFORROWID', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

function CancellaPratica($Token, $DomainCode, $NumeroPratica, $AnnoPratica) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    /* @var $wsAgent praWsAgent */
    try {
        $wsAgent = wsModel::getInstance("praWsAgent");
        $ret = $wsAgent->CancellaPratica($NumeroPratica, $AnnoPratica);
    } catch (Exception $exc) {
        return new nusoap_fault('ERR-CANCELLAPRATICA', get_class($wsAgent), $exc->getMessage());
    }

    return $ret;
}

function GetElencoPassi($Token, $DomainCode, $Stato, $Responsabile, $TipoPasso) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    if ($Stato == "" && $Responsabile == "" && $TipoPasso == "") {
        return "Inserire almeno uno dei filtri di ricerca.";
    }

    /* @var $wsAgent praWsAgent */
    try {
        $wsAgent = wsModel::getInstance("praWsAgent");
        $ret = $wsAgent->GetElencoPassi($Stato, $Responsabile, $TipoPasso);
    } catch (Exception $exc) {
        return new nusoap_fault('ERR-ELENCOPASSIBO', get_class($wsAgent), $exc->getMessage());
    }

    return $ret;
}

function SetStatoPasso($Token, $DomainCode, $codicePasso, $Stato, $DataApertura, $DataChiusura) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    if ($codicePasso == "") {
        return "Inserire il codice del passo da aggiornare.";
    }

    /* @var $wsAgent praWsAgent */
    try {
        $wsAgent = wsModel::getInstance("praWsAgent");
        $ret = $wsAgent->AggiornaStatoPasso($codicePasso, $Stato, $DataApertura, $DataChiusura);
        if (!$ret) {
            return $wsAgent->getErrMessage();
        }
    } catch (Exception $exc) {
        return new nusoap_fault('ERR-UPDSTATOPASSO', get_class($wsAgent), $exc->getMessage());
    }

    return $ret;
}

function ResetStatoAcquisizioneRichiesta($Token, $DomainCode, $NumeroRichiesta, $AnnoRichiesta) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->ResetAcquisizioneRichiesta($NumeroRichiesta, $AnnoRichiesta);
    if (!$ret) {
        return new nusoap_fault('ERR-RESETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}

function GetUrlPassoPratica($Token, $DomainCode, $ChiavePasso) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetUrlPassoPratica($ChiavePasso);
    if (!$ret) {
        return new nusoap_fault('ERR-RESETRICHIESTADATI', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return $ret;
    }
}

function GetPraticaAllegatoFromTestoBase($Token, $DomainCode, $NomeFile) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->GetPraticaAllegatoFromTestoBase($NomeFile);
    if (!$ret) {
        return new nusoap_fault('ERR-GETALLTESTOBASE', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return $ret;
    }
}

function SyncFascicolo($Token, $DomainCode, $FascicoloJason) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SyncFascicolo($FascicoloJason);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return $ret; //"success";
    }
}

function SyncPasso($Token, $DomainCode, $PassoJason) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SyncPasso($PassoJason);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return $ret; //"success";
    }
}

function SyncAllegatiInfo($Token, $DomainCode, $propak) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SyncAllegatiInfo($propak);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return $ret; //"success";
    }
}

function DeleteAllegatoPasso($Token, $DomainCode, $propak, $PasSha2) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->DeleteAllegatoPasso($propak, $PasSha2);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}

function SyncAllegatiDelete($Token, $DomainCode, $stream) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    /* @var $wsAgent praWsAgent */
    $wsAgent = wsModel::getInstance("praWsAgent");
    $ret = $wsAgent->SyncAllegatiDelete($stream);
    if (!$ret) {
        return new nusoap_fault('ERR-GETERRPREPROT', get_class($wsAgent), $wsAgent->getErrMessage());
    } else {
        return "success";
    }
}


// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
