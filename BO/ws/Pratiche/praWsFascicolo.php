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
    'stream' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'stream', 'type' => 'xsd:string'),
    'note' => array('minOccurs' => '1', 'maxOccurs' => '1', 'name' => 'note', 'type' => 'xsd:string')
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
    'pubblicaStatoPasso' => array('name' => 'pubblicaStatoPasso', 'type' => 'xsd:string')
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

/*
 * Metodi del servizio
 */
$server->register('GetElementiProtocollaPratica', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string',
    'rowidSoggetto' => 'xsd:double'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegatoForRowid', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegatoForRowidSplit', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'rowid' => 'xsd:string',
    'part' => 'xsd:string',
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

$server->register('GetElencoPraticheDaCatasto', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'xmlSearch' => 'xsd:string',
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaDati', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string'
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

$server->register('RicercaPratiche', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'filtri' => 'tns:filtri',
        ), array('return' => 'xsd:string'), $ns
);

function GetElementiProtocollaPratica($Token, $DomainCode, $NumeroPratica, $AnnoPratica, $rowidSoggetto = 0) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    itaLib::createAppsTempPath();

    $CodicePratica = $AnnoPratica . str_pad($NumeroPratica, 6, "0", STR_PAD_LEFT);
    $model = 'praFascicolo.class';
    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
    require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
    $praFascicolo = new praFascicolo($CodicePratica, $rowidSoggetto);
    $elementi = $praFascicolo->getElementiProtocollaPratica();
    $allegati = $praFascicolo->getAllegatiProtocollaPratica('', false);
    if ($allegati) {
        $elementi['allegati'] = $allegati;
    }
    return base64_encode(serialize($elementi));
}

function GetPraticaAllegatoForRowid($Token, $DomainCode, $rowid) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
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
        return new soap_fault('ERRGETALLEGATO', '', 'Allegato non disponibile');
    }

    $keyPasso = $Pasdoc_rec['PASKEY'];
    $genPath = $praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), substr($Pasdoc_rec['PASKEY'], 0, 10), 'PROGES', false, $DomainCode);
    $passoPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);

    if (strlen($Pasdoc_rec['PASKEY']) == 10) {
        $pramPath = $genPath;
    } else {
        $pramPath = $passoPath;
    }
    $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];

    /*
     * Per file in partenza generati con  XHTML
     */
    $infoFile = pathinfo($Pasdoc_rec['FILEPATH']);
    if (strtolower($infoFile['extension']) == 'xhtml') {
        $pdf_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf';
        $p7m_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.p7m';
        $p7m2_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m';
        $p7m3_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m.p7m';
        if (file_exists($p7m3_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m3_file;
        } elseif (file_exists($p7m2_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m2_file;
        } elseif (file_exists($p7m_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m_file;
        } elseif (file_exists($pdf_file)) {
            $Pasdoc_rec['FILEPATH'] = $pdf_file;
        }
    }
    /*
     * Fine Correzione
     */

    $fh = fopen($Pasdoc_rec['FILEPATH'], 'rb');
    if ($fh) {
        $binary = fread($fh, filesize($Pasdoc_rec['FILEPATH']));
        fclose($fh);
        return base64_encode($binary);
    } else {
        return new soap_fault('ERRGETALLEGATO', '', 'lettura Allegato: ' . $Pasdoc_rec['FILEPATH'] . ' fallita', '');
    }
}

function GetPraticaAllegatoForRowidSplit($Token, $DomainCode, $rowid, $part) {

    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
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
        return new soap_fault('ERRGETALLEGATO', '', 'Allegato non disponibile');
    }
    //$ext = pathinfo($dataDetail_rec['PASFIL'], PATHINFO_EXTENSION);
    $keyPasso = $Pasdoc_rec['PASKEY'];
    $genPath = $praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), substr($Pasdoc_rec['PASKEY'], 0, 10), 'PROGES', false, $DomainCode);
    $passoPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);

    //$pramPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);
    if (strlen($Pasdoc_rec['PASKEY']) == 10) {
        $pramPath = $genPath;
    } else {
        $pramPath = $passoPath;
    }
    $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];

    /*
     * Per file in partenza generati con  XHTML
     */
    $infoFile = pathinfo($Pasdoc_rec['FILEPATH']);
    if (strtolower($infoFile['extension']) == 'xhtml') {
        $pdf_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf';
        $p7m_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.p7m';
        $p7m2_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m';
        $p7m3_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m.p7m';
        if (file_exists($p7m3_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m3_file;
        } elseif (file_exists($p7m2_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m2_file;
        } elseif (file_exists($p7m_file)) {
            $Pasdoc_rec['FILEPATH'] = $p7m_file;
        } elseif (file_exists($pdf_file)) {
            $Pasdoc_rec['FILEPATH'] = $pdf_file;
        }
    }
    /*
     * Fine Correzione
     */
    $fh = fopen($Pasdoc_rec['FILEPATH'], 'rb');
    if ($fh) {
        $binary = fread($fh, filesize($Pasdoc_rec['FILEPATH']));
        fclose($fh);
        $binary = base64_encode($binary);

        $length = 2000000;
        $start = $length * ($part - 1);
        if ($start > strlen($binary)) {
            return '';
        }
        return trim(substr($binary, $start, $length));
    } else {
        return new soap_fault('ERRGETALLEGATO', '', 'lettura Allegato: ' . $Pasdoc_rec['FILEPATH'] . ' fallita', '');
    }
}

function SetProtocolloPratica($Token, $DomainCode, $NumeroPratica, $AnnoPratica, $numeroProtocollo, $annoProtocollo, $datiProtocollazione) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

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
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    itaLib::createAppsTempPath();

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
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }
    //
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
    $pracom_rec['COMPRT'] = $datiProtocollazione['annoProtocollo'] . $datiProtocollazione['numeroProtocollo'];
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

function GetElencoPraticheDaCatasto($Token, $DomainCode, $arrayCampi) {
    global $server;
    $retLogin = $server->login($Token, $DomainCode);
    if ($retLogin !== true) {
        return $retLogin;
    }

    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
        $ITW_DB = ItaDB::DBOpen('ITW', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito. ---->' . $e->getMessage(), '');
    }
    $praLib = new praLib($DomainCode);
    $praLib->setPRAMDB($PRAM_DB);
    $praLib->setITWDB($ITW_DB);
    $sql = $praLib->CreaSqlFascicoli($arrayCampi);
    try {
        $Proges_tab = itaDB::DBSQLSelect($PRAM_DB, $sql);
    } catch (Exception $exc) {
        return new soap_fault('ERRGETPRATICA' . 'Query Fascicoli fallita. ---->' . $exc->getMessage(), '');
    }


    if ($Proges_tab) {
        $cdata_a = ""; //<![CDATA[";
        $cdata_c = ""; //]]>";
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<FASCICOLI>\r\n";
        foreach ($Proges_tab as $Proges_rec) {
            $xml = $xml . "<RECORD>\r\n";
            foreach ($Proges_rec as $Chiave => $Valore) {
                $xml = $xml . "<$Chiave>$cdata_a" . htmlspecialchars($Valore, ENT_COMPAT, "ISO-8859-1") . "$cdata_c</$Chiave>\r\n";
            }
            $xml = $xml . "</RECORD>\r\n";
        }
        $xml = $xml . "</FASCICOLI>\r\n";
        $xmlB64 = base64_encode($xml);
        file_put_contents("/users/pc/dos2ux/Andrea/GetElencoPraticheDaCatasto.xml", base64_decode($xmlB64));
        return $xmlB64;
    } else {
        return "Fascicoli non trovati";
    }
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
        return new soap_fault('ERR-GETPRATICADATI', get_class($wsAgent), $wsAgent->getErrMessage());
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
        return new soap_fault('ERR-APPENDPASSOPRATICASIMPLE', get_class($wsAgent), $wsAgent->getErrMessage());
    }
    return $ret;
}

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
        return new soap_fault('ERR-GETPRATICAALLEGATOFORROWID', get_class($wsAgent), $wsAgent->getErrMessage());
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

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
