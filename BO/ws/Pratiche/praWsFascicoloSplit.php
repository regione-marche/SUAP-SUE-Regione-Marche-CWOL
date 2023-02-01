<?php

/* * 
 *
 * WS PROTOCOLLAZIONE REMOTA SUAP
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    23.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */

require_once('../lib/wsServer.class.php');
wsServer::load();

require_once(ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
//
// Dichiaro i namespaces da usare
//
define("NAME_SPACE", "http://www.italsoft-mc.it/ws/praWsFascicolo");
define("DEFAULT_NAME_SPACE", "http://www.w3.org/2001/XMLSchema");

$name = "praWsFascicolo";
$server = wsServer::getWsServerInstance($name, NAME_SPACE);

$server->register('GetElementiProtocollaPratica', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegatoForRowid', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);


$server->register('SetProtocolloPratica', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string',
    'numeroProtocollo' => 'xsd:string',
    'annoProtocollo' => 'xsd:string',
    'datiProtocollazione' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);


$server->register('GetElementiProtocollaComunicazione', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'idComunicazione' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('SetProtocolloComunicazione', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'idComunicazione' => 'xsd:string',
    'datiProtocollazione' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

function GetElementiProtocollaPratica($Token, $DomainCode, $NumeroPratica, $AnnoPratica) {
    if ($Token == '') {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }

    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($Token);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        // vado avanti
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }

    $utenti_rec = $itaToken->getUtentiRec();

    App::$utente->setKey('ditta', $DomainCode);
    App::$utente->setKey('TOKEN', $Token);
    App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);

    $CodicePratica = $AnnoPratica . str_pad($NumeroPratica, 6, "0", STR_PAD_LEFT);
    $model = 'praFascicolo.class';
    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
    require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
    $praFascicolo = new praFascicolo($CodicePratica);
    $elementi = $praFascicolo->getElementiProtocollaPratica();
    $allegati = $praFascicolo->getAllegatiProtocollaPratica('', false);
    if ($allegati) {
        $elementi['allegati'] = $allegati;
    }
    return base64_encode(serialize($elementi));
}

function GetPraticaAllegatoForRowid($Token, $DomainCode, $rowid) {
    if ($Token == '') {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }

    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($Token);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        // vado avanti
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }

    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA', '', 'Accesso al DB fallito');
    }

    $praLib = new praLib($DomainCode);

    $praLib->setPRAMDB($PRAM_DB);


    $sql = "SELECT * FROM PASDOC WHERE ROWID = " . $rowid;

    $Pasdoc_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if (!$Pasdoc_rec) {
        return new soap_fault('ERRGETALLEGATO' , '',  'Allegato non disponibile');
    }
    $ext = pathinfo($dataDetail_rec['PASFIL'], PATHINFO_EXTENSION);
    $keyPasso = $Pasdoc_rec['PASKEY'];
    $genPath   = $praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), substr($Pasdoc_rec['PASKEY'],0,10), 'PROGES', false,$DomainCode);    
    $passoPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);
    
    //$pramPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);
    if(strlen($Pasdoc_rec['PASKEY']) == 10){
        $pramPath = $genPath;
    }else{
        $pramPath = $passoPath;
    }
    $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];

    $fh = fopen($Pasdoc_rec['FILEPATH'], 'rb');
    if ($fh) {
        $binary = fread($fh, filesize($Pasdoc_rec['FILEPATH']));
        fclose($fh);
        return base64_encode($binary);
    } else {
        return new soap_fault('ERRGETALLEGATO', '', 'lettura Allegato: '.$Pasdoc_rec['FILEPATH'].' fallita', '');
    }
}

function SetProtocolloPratica($Token, $DomainCode, $NumeroPratica, $AnnoPratica, $numeroProtocollo, $annoProtocollo, $datiProtocollazione) {
    if ($Token == '') {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }
    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($Token);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        // vado avanti
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }

    $utenti_rec = $itaToken->getUtentiRec();

    App::$utente->setKey('ditta', $DomainCode);
    App::$utente->setKey('TOKEN', $Token);
    App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);

    $gesnum = $AnnoPratica . $NumeroPratica;
    $datiProtocollazione = unserialize(base64_decode($datiProtocollazione));

    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }
    $praLib = new praLib($DomainCode);
    $praLib->setPRAMDB($PRAM_DB);

    $sql = "SELECT * FROM PROGES WHERE GESNUM = '" . $gesnum . "'";
    $proges_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if (!$proges_rec) {
        return new soap_fault('ERRSETPRATICA' . 'record PRATICA NON TROVATO', '');
    }
    $proges_rec['GESNPR'] = $datiProtocollazione['annoProtocollo'] . $datiProtocollazione['numeroProtocollo'];
    $proges_rec['GESPAR'] = $datiProtocollazione['tipoProtocollo'];
    $meta = array();
    $meta['DatiProtocollazione'] = array(
        'TipoProtocollo' => array('value' => 'Italsoft-remoto', 'status' => true, 'msg' => 'Protocollazione Pratica'),
        'proNum' => array('value' => $numeroProtocollo, 'status' => true, 'msg' => ''),
        'Data' => array('value' => $datiProtocollazione['dataProtocollo'], 'status' => true, 'msg' => ''),
        'Anno' => array('value' => $annoProtocollo, 'status' => true, 'msg' => ''),
        'Oggetto' => array('value' => $datiProtocollazione['Oggetto'], 'status' => true, 'msg' => ''),
        'Segnatura' => array('value' => $datiProtocollazione['Segnatura'], 'status' => true, 'msg' => '')        
        
    );
    $metadati = serialize($meta);
    $proges_rec['GESMETA'] = $metadati;

    try {
        ItaDB::DBUpdate($PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
    } catch (Exception $e) {
        return new soap_fault('ERRSETPRATICA' . 'Aggiornamento record al DB fallito', '');
    }

    return "1";
}

function GetElementiProtocollaComunicazione($Token, $DomainCode, $idComunicazione) {
    if ($Token == '') {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }

    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($Token);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        // vado avanti
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }

    $utenti_rec = $itaToken->getUtentiRec();

    App::$utente->setKey('ditta', $DomainCode);
    App::$utente->setKey('TOKEN', $Token);
    App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);

    $elementi = array();

    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }
    $praLib = new praLib($DomainCode);
    $praLib->setPRAMDB($PRAM_DB);

    $pracom_rec = $praLib->GetPracom($idComunicazione, 'rowid');
    if (!$pracom_rec) {
        return new soap_fault('ERRGETCOMUNICAZIONE' . 'record COMUNICAZIONE NON TROVATO', '');
    }

    $propas_rec = $praLib->GetPropas($pracom_rec['COMPAK'], 'propak');

    $model = 'praFascicolo.class';
    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
    require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
    $praFascicolo = new praFascicolo(substr($pracom_rec['COMPAK'], 0, 10));
    $praFascicolo->setChiavePasso($pracom_rec['COMPAK']);
    if ($pracom_rec['COMTIP'] == 'P') {
        $elementi = $praFascicolo->getElementiProtocollaComunicazioneP();
    } else {
        $elementi = $praFascicolo->getElementiProtocollaComunicazioneA();
    }

    $elementi['allegati'] = $praFascicolo->getAllegatiProtocollaComunicazione('', false);

    return base64_encode(serialize($elementi));
}

function SetProtocolloComunicazione($Token, $DomainCode, $idComunicazione, $datiProtocollazione) {
    if ($Token == '') {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }
    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($Token);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        // vado avanti
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }

    $utenti_rec = $itaToken->getUtentiRec();

    App::$utente->setKey('ditta', $DomainCode);
    App::$utente->setKey('TOKEN', $Token);
    App::$utente->setKey('nomeUtente', $utenti_rec['UTELOG']);

    $datiProtocollazione = unserialize(base64_decode($datiProtocollazione));
    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }
    $praLib = new praLib($DomainCode);
    $praLib->setPRAMDB($PRAM_DB);

    $sql = "SELECT * FROM PRACOM WHERE ROWID = " . $idComunicazione;
    $pracom_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if (!$pracom_rec) {
        return new soap_fault('ERRSETCOMUNICAZIONE' . 'record COMUNICAZIONE NON TROVATO', '');
    }
    $pracom_rec['COMPRT'] = $datiProtocollazione['annoProtocollo'].$datiProtocollazione['numeroProtocollo'];
    $pracom_rec['COMDPR'] = $datiProtocollazione['dataProtocollo'];
    
    $meta = array();
    $meta['DatiProtocollazione'] = array(
        'TipoProtocollo' => array('value' => 'Italsoft-remoto', 'status' => true, 'msg' => 'Protocollazione Pratica'),
        'proNum' => array('value' => $datiProtocollazione['numeroProtocollo'], 'status' => true, 'msg' => ''),
        'Data' => array('value' => $datiProtocollazione['dataProtocollo'], 'status' => true, 'msg' => ''),
        'Anno' => array('value' => substr($datiProtocollazione['dataProtocollo'], 0, 4), 'status' => true, 'msg' => ''),
        'Oggetto' => array('value' => $datiProtocollazione['Oggetto'], 'status' => true, 'msg' => ''),
        'Segnatura' => array('value' => $datiProtocollazione['Segnatura'], 'status' => true, 'msg' => '')        
        
    );
    $metadati = serialize($meta);
    $pracom_rec['COMMETA'] = $metadati;
    try {
        ItaDB::DBUpdate($PRAM_DB, 'PRACOM', 'ROWID', $pracom_rec);
    } catch (Exception $e) {
        return new soap_fault('ERRSETCOMUNICAZIONE' . 'Aggiornamento record al DB fallito', '');
    }
    return "1";
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
