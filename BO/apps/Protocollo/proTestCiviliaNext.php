<?php

/**
 *
 * TEST PALESO WS-CLIENT
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    21.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_LIB_PATH . '/itaPHPOAuth2Client/itaPHPOAuth2Client.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCiviliaNext/itaPHPCiviliaNextClient.class.php');

function proTestCiviliaNext() {
    $proTestCiviliaNext = new proTestCiviliaNext();
    $proTestCiviliaNext->parseEvent();
    return;
}

class proTestCiviliaNext extends itaModel {

    public $name_form = "proTestCiviliaNext";

    function __construct() {

        parent::__construct();
    }

    function __destruct() {

        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->creaComboTrueFalse('_isFromModificaAllegato');
                $this->creaComboTrueFalse('_isFromModificaCorrispondente');
                $this->creaComboTrueFalse('_isPrincipaleA1');
                $this->creaComboTrueFalse('_isPrincipaleA2');
                $this->creaComboTrueFalse('_assegnata');
                $this->creaComboTrueFalse('_inviaMail');
                $this->creaComboTrueFalse('_emailInviata');
                $this->creaComboTrueFalse('_getByteArrayA');
                $this->creaComboTrueFalse('_getByteArray');
                $this->creaComboTrueFalse('_isPrincipale1');
                $this->creaComboTrueFalse('_isPrincipale2');
                Out::show($this->name_form);
                Out::setFocus('', $this->name_form . "_OAuthFlow");

                include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
                $devLib = new devLib();
                $clientID = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CLIENTID', false);
                $clientSecret = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CLIENTSECRET', false);
                $urlAuthorize = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'URLAUTHORIZE', false);
                $urlAccessToken = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'URLACCESSTOKEN', false);
                $utlResourceOwnerDetail = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'URLRESOURCEOWNERDETAIL', false);
                $scope = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'SCOPE', false);
                $endpoint = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'ENDPOINT', false);
                $codiceOrganigramma = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'CODICEORGANIGRAMMA', false);
                $idOperatore = $devLib->getEnv_config('CIVILIANEXTWSCONNECTION', 'codice', 'IDOPERATORE', false);

                Out::valore($this->name_form . '_ClientIdentification', $clientID['CONFIG']);
                Out::valore($this->name_form . '_ClientSecret', $clientSecret['CONFIG']);
                Out::valore($this->name_form . '_AccessTokenURL', $urlAccessToken['CONFIG']);
                Out::valore($this->name_form . '_Scope', $scope['CONFIG']);
                Out::valore($this->name_form . '_Endpoint', $endpoint['CONFIG']);
                Out::valore($this->name_form . '_CodiceOrganigramma', $codiceOrganigramma['CONFIG']);
                Out::valore($this->name_form . '_IDOperatore', $idOperatore['CONFIG']);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->name_form . '_getToken':

                        $client = new itaPHPOAuth2Client();
                        $this->setClientConfigAuth($client);
                        $token = $client->getToken();
                        Out::valore($this->name_form . '_Token', $token);
//                        Out::msgInfo("token", $token);
                        break;
                    case $this->name_form . '_protocolla':
                        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
                        $this->setClientConfig($itaCiviliaNextClient);
                        $itaCiviliaNextClient->setToken($this->formData[$this->name_form . '_Token']);

                        $param = array();
                        $param['oggetto'] = htmlspecialchars(utf8_encode($this->formData[$this->name_form . '_oggetto']), ENT_COMPAT, 'UTF-8');
                        $param['isFromModificaAllegato'] = false; //nel caso si tratti di un nuovo protocollo.
                        $param['isFromModificaCorrispondente'] = false; //nel caso si tratti di un nuovo protocollo.
                        $param['tipoProtocollo'] = "INGRESSO";

                        /*
                         * Mittente
                         */
                        $listaCorrispondenti = array();
                        $denom = $this->formData[$this->name_form . '_denominazione1'];
                        $listaCorrispondenti[0]['denominazione'] = $denom;
                        $listaCorrispondenti[0]['email'] = $this->formData[$this->name_form . '_email1'];
                        $listaCorrispondenti[0]['tipoIndividuoProtocollo'] = $this->formData[$this->name_form . '_tipoIndividuoProtocollo1'];

                        $param['CorrispondentiList'] = $listaCorrispondenti;

                        $param['protocollatoDa'] = $this->formData[$this->name_form . '_protocollatoDa']; // webappfatturazione|civiliaweb|civiliaopen
                        $param['codiceLivelloOrganigramma'] = $this->formData[$this->name_form . '_CodiceOrganigramma'];
                        $param['idCodiceAOO'] = ""; // capire se esiste una sola AOO

                        $ret = $itaCiviliaNextClient->Protocolla($param);
                        $risultato = json_decode($ret, true);
                        Out::msgInfo("risultato", print_r($risultato, true));
                        break;
                    case $this->name_form . '_cerca':
                        $itaCiviliaNextClient = new itaPHPCiviliaNextClient();
                        $this->setClientConfig($itaCiviliaNextClient);
                        $itaCiviliaNextClient->setToken($this->formData[$this->name_form . '_Token']);
                        //
                        $param = array();
                        $param['idPratica'] = $this->formData[$this->name_form . '_idPraticaP'];
                        $ret = $itaCiviliaNextClient->CercaPratiche($param);
                        $risultato = json_decode($ret, true);
                        Out::msgInfo("risultato", print_r($risultato, true));
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
//        App::$utente->removeKey($this->name_Form . '_StreamDocumentoPrincipale');
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function creaCombo() {
        Out::select($this->name_form . '_OAuthFlow', 1, "1", $sel1, "Authorization Code Grant");
        Out::select($this->name_form . '_OAuthFlow', 1, "2", $sel2, "Implicit Grant");
        Out::select($this->name_form . '_OAuthFlow', 1, "3", $sel3, "Resource Owner Password Credential Grant");
        Out::select($this->name_form . '_OAuthFlow', 1, "4", $sel4, "Client Credentials Grant");
    }

    function creaComboTrueFalse($combo) {
        Out::select($this->name_form . $combo, 1, "1", $sel1, "true");
        Out::select($this->name_form . $combo, 1, "0", $sel2, "false");
    }

    function setClientConfigAuth($client) {
        $client->setClientId($this->formData[$this->name_form . '_ClientIdentification']);
        $client->setClientSecret($this->formData[$this->name_form . '_ClientSecret']);
        $client->setUrlAuthorize($this->formData[$this->name_form . '_AccessTokenURL']);
        $client->setUrlAccessToken($this->formData[$this->name_form . '_AccessTokenURL']);
        $client->setUrlResourceOwnerDetails('');
        $client->setScope($this->formData[$this->name_form . '_Scope']);
    }

    private function setClientConfig($client) {
        $client->setEndpoint($this->formData[$this->name_form . '_Endpoint']);
        $client->setCodiceOrganigramma($this->formData[$this->name_form . '_CodiceOrganigramma']);
        $client->setIdOperatore($this->formData[$this->name_form . '_IDOperatore']);
    }

}
