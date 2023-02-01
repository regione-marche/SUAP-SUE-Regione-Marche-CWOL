<?php
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

function ita_getToken($wsdl, $UserName, $UserPassword, $DomainCode) {
    $client = new nusoap_client($wsdl, true);
    $err = $client->getError();
    if ($err) {
        return "Errore: " . $err;
    }
    $result2 = $client->call('GetItaEngineContextToken', array('UserName' => $UserName, 'UserPassword' => $UserPassword, 'DomainCode' => $DomainCode));
    if ($client->fault) {
        $err = $client->getError();
        if ($err) {
            return("Errore: " . $err );
        } else {
            return("Errore: Client Fault");
        }
    } else {
        $err = $client->getError();
        if ($err) {
            return ("Errore: " . $err );
        } else {
            return $result2;
        }
    }
}

?>
