<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ERROR);

require_once('../Config.inc.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/Config.class.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/snoopy/Snoopy.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaPHP.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaModel.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/ItaToken.class.php');
require_once(ITA_LIB_PATH . '/DB/DB.php');
require_once(ITA_LIB_PATH . '/DB/ItaDB.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php');
require_once(ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');

/* carico le configurazioni di base di itaEngine */
Config::load();

$ns = "http://www.italsoft-mc.it/ws/praWs";
$xsns = "http://www.w3.org/2001/XMLSchema";

//
//
//definiamo il nostro namespace privato come abbiamo fatto anche nel capitolo WSDL
//define("NAMESPACE", "http://www.html.it/guida_ai_Web services");
//
//creiamo un istanza del server fornito da nusoap
//
$server = new soap_server();
//
//disattiviamo il debug
//
$server->debug_flag = false;
//
// Diamo un nome al Web service ed impostiamo il nostro namespace
//
// Initialize WSDL support
$server->configureWSDL('praWs', 'urn:praWs');
//$server->configureWSDL('praWs', $ns);  
//
// impostiamo il nostro namespace anche come target dello schema WSDL (come abbiamo fatto nell'esempio WSDL)
//
$server->wsdl->schemaTargetNamespace = $ns;

$server->register('GetItaEngineContextToken', array(
    'userName' => 'xsd:string',
    'userPassword' => 'xsd:string',
    'domainCode' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('CheckItaEngineContextToken', array(
    'tokenKey' => 'xsd:string',
    'domainCode' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('DestroyItaEngineContextToken', array(
    'tokenKey' => 'xsd:string',
    'domainCode' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);


$server->register('GetPraticaDati', array(
    'itaEngineContextToken' => 'xsd:string',
    'domainCode' => 'xsd:string',
    'numeroPratica' => 'xsd:string',
    'annoPratica' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

$server->register('GetPraticaAllegati', array(
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
    'pubblicaStatoPasso' => 'xsd:string'
        ), array('return' => 'xsd:string'), $ns
);

function GetItaEngineContextToken($UserName, $UserPassword, $DomainCode) {

    if (!$UserName) {
        return "Errore";
    }

    $ret_verpass = ita_verpass($DomainCode, $UserName, $UserPassword);


    if (!$ret_verpass) {
        return new soap_fault('ERRLOGINGEN', '', 'Autenticazione Annullata', '');
    }

    if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
        return new soap_fault('ERRLOGIN' . $ret_verpass['status'], '', $ret_verpass['messaggio'], '');
    }
    if ($ret_verpass['status'] == '-99') {
        return new soap_fault('ERRLOGIN' . $ret_verpass['status'], '', "Errore generale", '');
    }

    $cod_ute = $ret_verpass['codiceUtente'];

    $itaToken = new ItaToken($DomainCode);

    $ret_token = $itaToken->createToken($cod_ute);
    if ($ret_token['status'] == '0') {
        return $itaToken->getTokenKey();
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }
}

function CheckItaEngineContextToken($TokenKey, $DomainCode) {
    if (!$TokenKey) {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }

    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($TokenKey);
    $ret_token = $itaToken->checkToken();
    if ($ret_token['status'] == '0') {
        return "Valid";
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }
}

function DestroyItaEngineContextToken($TokenKey, $DomainCode) {
    if (!$TokenKey) {
        return new soap_fault('ERRTOKEN', '', "Token Mancante", '');
    }

    if (!$DomainCode) {
        return new soap_fault('ERRTOKEN', '', "Codice Ente/Organizzazione Mancante", '');
    }

    $itaToken = new ItaToken($DomainCode);
    $itaToken->setTokenKey($TokenKey);
    $ret_token = $itaToken->destroyToken();
    if ($ret_token['status'] == '0') {
        return "Success";
    } else {
        return new soap_fault('ERRTOKEN' . $ret_token['status'], '', $ret_token['messaggio'], '');
    }
}

function GetPraticaDati($Token, $DomainCode, $NumeroPratica, $AnnoPratica) {
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
    $praLib = new praLib($DomainCode);

    $cdata_a = "<![CDATA[";
    $cdata_c = "]]>";
    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }

    
    $xml = "";
    $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
    $CodicePratica = $AnnoPratica . str_pad($NumeroPratica, 6, "0", STR_PAD_LEFT);
    $Proges_rec = $praLib->GetProges($CodicePratica);
    if (!$Proges_rec) {
        return new soap_fault('ERRGETPRATICA' . 'Pratica non disponibile', '');
    }
    $utenti_rec = $itaToken->getUtentiRec();

//    $ret=$praLib->checkVisibilitaSportello(array('SPORTELLO' => $Proges_rec['GESTSP'], 'AGGREGATO' => $Proges_rec['GESSPA']),$praLib->GetVisibiltaSportello($utenti_rec['UTECOD']));
//    if (!$ret) {
//        return new soap_fault('ERRGETPRATICA' . 'Pratica non visibile', '');
//    }

    
    $xml = $xml . "<PRATICADATI id=\"$CodicePratica\">\r\n";
    $xml = $xml . "<PROGES>\r\n";
    $xml = $xml . "<RECORD>\r\n";
    foreach ($Proges_rec as $Chiave => $Campo) {
        //$xml = $xml . "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
        $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
    }
    $xml = $xml . "</RECORD>\r\n";
    $xml = $xml . "</PROGES>\r\n";

    $sql = "SELECT * FROM ANADES WHERE DESNUM='" . $CodicePratica . "'";
    $Anades_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if ($Anades_rec) {
        $xml = $xml . "<ANADES>\r\n";
        $xml = $xml . "<RECORD>\r\n";
        foreach ($Anades_rec as $Chiave => $Campo) {
            $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
        }
        $xml = $xml . "</RECORD>\r\n";
        $xml = $xml . "</ANADES>\r\n";
    }
    //
    // Passi
    //
    $campi = array();
    $campi[] = "PRONUM";
    $campi[] = "PROPRO";
    $campi[] = "PROPAK";
    $campi[] = "PRODTP";
    $campi[] = "PRODPA";
    $chiavi = array();
    $chiavi_attach = array();
    $sql = "SELECT * FROM PROPAS WHERE PRONUM='" . $CodicePratica . "' AND PROPUB = 1 AND (PROUPL = 1 OR PROMLT = 1 OR PRODAT = 1)";
    $Propas_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
    if ($Propas_tab) {
        $xml = $xml . "<PROPAS>\r\n";
        foreach ($Propas_tab as $Propas_rec) {
            $chiavi[] = "'" . $Propas_rec['PROPAK'] . "'";
            if ($Propas_rec['ITEIDR'] !== 1) {
                $chiavi_attach[] = "'" . $Propas_rec['PROPAK'] . "'";
            }
            $xml = $xml . "<RECORD>\r\n";
            foreach ($campi as $Chiave => $Campo) {
                $xml = $xml . "<$Campo>$cdata_a" . $Propas_rec[$Campo] . "$cdata_c</$Campo>\r\n";
            }
            $xml = $xml . "</RECORD>\r\n";
        }
        $xml = $xml . "</PROPAS>\r\n";

        $campi = array();
        $campi[] = "DAGNUM";
        $campi[] = "DAGPAK";
        $campi[] = "DAGKEY";
        $campi[] = "DAGSET";
        $campi[] = "DAGVAL";
        $sql = "SELECT * FROM PRODAG WHERE DAGPAK IN (" . implode(",", $chiavi) . ") AND DAGNUM = '" . $CodicePratica . "'";
        $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        if ($Prodag_tab) {
            $xml = $xml . "<PRODAG>\r\n";
            foreach ($Prodag_tab as $Prodag_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    $xml = $xml . "<$Campo>$cdata_a" . $Prodag_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRODAG>\r\n";
        }

        //
        // Indice Allegati
        //
        $campi = array();
        $campi[] = "ROWID";
        $campi[] = "PASKEY";
        $campi[] = "PASFIL";
        $campi[] = "PASNOT";
        $sql = "SELECT * FROM PASDOC WHERE PASKEY IN (" . implode(",", $chiavi) . ") ORDER BY PASKEY";
        $Propas_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        if ($Propas_tab) {
            $xml = $xml . "<PASDOC>\r\n";
            foreach ($Propas_tab as $Propas_rec) {
                if (strtolower(pathinfo($Propas_rec['PASFIL'], PATHINFO_EXTENSION)) == 'info') {
                    continue;
                }
                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    $xml = $xml . "<$Campo>$cdata_a" . $Propas_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PASDOC>\r\n";
        }
    }

    $xml = $xml . "</PRATICADATI>\r\n";
    $xmlB64 = base64_encode($xml);
    return $xmlB64;
    //return $xml;
}

function GetPraticaAllegati($Token, $DomainCode, $numeroPratica, $annoPratica) {
    return new soap_fault('ERRGETALLEGATI' . 'Non Implemenato', '');
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
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }

    $praLib = new praLib($DomainCode);

    $praLib->setPRAMDB($PRAM_DB);


    $sql = "SELECT * FROM PASDOC WHERE ROWID = " . $rowid;

    $Pasdoc_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if (!$Pasdoc_rec) {
        return new soap_fault('ERRGETALLEGATO' . 'Allegato non disponibile', '');
    }
    $ext = pathinfo($dataDetail_rec['PASFIL'], PATHINFO_EXTENSION);
    $keyPasso = $Pasdoc_rec['PASKEY'];
    $pramPath = $praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, $DomainCode);
    $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
    
    $fh = fopen($Pasdoc_rec['FILEPATH'], 'rb');
    if ($fh) {
        $binary = fread($fh, filesize($Pasdoc_rec['FILEPATH']));
        fclose($fh);
        return base64_encode($binary);
    } else {
        return new soap_fault('ERRGETALLEGATO' . 'lettura Allegato fallita', '');
    }
}

function AppendPassoPraticaSimple($Token, $DomainCode, $numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $dataApertura, $statoApertura, $dataChiusura, $statoChiusura, $pubblicaStatoPasso) {
    try {
        $PRAM_DB = ItaDB::DBOpen('PRAM', $DomainCode);
    } catch (Exception $e) {
        return new soap_fault('ERRGETPRATICA' . 'Accesso al DB fallito', '');
    }

    $praLib = new praLib($DomainCode);

    $praLib->setPRAMDB($PRAM_DB);
    //
    // Sanity check
    //

    //
    // Esiste pratica ?
    //
    $CodicePratica = $annoPratica . str_pad($numeroPratica, 6, "0", STR_PAD_LEFT);
    $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $CodicePratica . "'";

    $Proges_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    if (!$Proges_rec) {
        return new soap_fault('ERRGETPRATICA' . 'Pratica non disponibile', '');
    }
    //
    // Controllo validità degli stati
    //

    if ($statoApertura) {
        $Anastp_apri_rec = $praLib->GetAnastp($statoApertura);
        if (!$Anastp_apri_rec) {
            return new soap_fault('ERRGETSTATO' . 'Stato Apertura: ' . $statoApertura . ' non disponibile', '');
        }
    }

    if ($statoChiusura) {
        $Anastp_chiudi_rec = $praLib->GetAnastp($statoChiusura);
        if (!$Anastp_chiudi_rec) {
            return new soap_fault('ERRGETSTATO' . 'Stato Chiusura: ' . $statoChiusura . ' non disponibile', '');
        }
    }

    //
    // Valorizzo campi
    //
    $Propas_rec = array(); //$_POST[$this->nameForm . '_PROPAS'];
    $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
    $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
    $Propas_rec['PROSEQ'] = 99999;
    $Propas_rec['PRORES'] = $Proges_rec['GESRES'];
    $Propas_rec['PRORPA'] = $Proges_rec['GESRES'];

    $Anamon_rec = $praLib->GetAnanom($Propas_rec['PRORES']);

    if ($Ananom_rec) {
        $Propas_rec['PROUOP'] = $Ananom_rec['NOMOPE'];
        $Propas_rec['PROSET'] = $Ananom_rec['NOMSET'];
        $Propas_rec['PROSER'] = $Ananom_rec['NOMSER'];
    }
    $Propas_rec['PRODTP'] = $descrizioneTipoPasso;
    $Propas_rec['PRODPA'] = $descrizionePasso;
    $Propas_rec['PROPAK'] = $praLib->PropakGenerator($Proges_rec['GESNUM']);
    $Propas_rec['PROINI'] = $dataApertura;
    $Propas_rec['PROSTAP'] = $statoApertura;
    $Propas_rec['PROFIN'] = $dataChiusura;
    $Propas_rec['PROSTCH'] = $statoChiusura;
    $Propas_rec['PROANN'] = $annotazione;
    $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
    $Propas_rec['PROUTEADD'] = ""; // da desumere dal token
    $Propas_rec['PROPST'] = $pubblicaStatoPasso;
    $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date("H:i:s");

    try {
        $nrow = ItaDB::DBInsert($PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec);
        if ($nrow != 1) {
            return new soap_fault('ERRAPPENDPASSO' . 'Errore Inseriemnto passo', '');
        }
    } catch (Exception $e) {
        return new soap_fault('ERRAPPENDPASSO' . 'Errore Inseriemnto passo:' . $e->getMessage(), '');
    }

    $praLib->ordinaPassi($Proges_rec['GESNUM']);
    $praLib->sincronizzaStato($Proges_rec['GESNUM']);
    return 'Success';
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
