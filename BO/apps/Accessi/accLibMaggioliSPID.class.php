<?php

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class accLibMaggioliSPID {

    private $accLib;
    private $devLib;
    private $errCode;
    private $errMessage;
    private $envParamKey = 'SSOSPIDMAGGIOLI';

    const PLUGIN_SESSION_TOKEN = 'authservice-spid-session-token';
    const PLUGIN_SESSION_USERDATA = 'authservice-spid-session-userdata';
    const URI_AUTH = 'https://spid.comune-online.it/AuthServiceSPID/auth.jsp';
    const URI_WSDL = 'https://spid.comune-online.it/AuthServiceSPID/services/AuthService?wsdl';

    public function __construct() {
        $this->accLib = new accLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getSessionToken() {
        return isset($_SESSION[self::PLUGIN_SESSION_TOKEN]) ? ($_SESSION[self::PLUGIN_SESSION_TOKEN] ?: false) : false;
    }

    private function setSessionToken($token) {
        $_SESSION[self::PLUGIN_SESSION_TOKEN] = $token;
    }

    public function getSessionUserData() {
        return isset($_SESSION[self::PLUGIN_SESSION_USERDATA]) ? ($_SESSION[self::PLUGIN_SESSION_USERDATA] ?: false) : false;
    }

    private function setSessionUserData($userData) {
        $_SESSION[self::PLUGIN_SESSION_USERDATA] = $userData;
    }

    private function getEnvParam($key, $codiceEnte = 'ditta') {
        if (!$this->devLib) {
            include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
            $this->devLib = new devLib();
            $this->devLib->setITALWEB(ItaDB::DBOpen('ITALWEB', $codiceEnte));
        }

        $envConfig_rec = $this->devLib->getEnv_config($this->envParamKey, 'codice', $key, false);
        return $envConfig_rec['CONFIG'];
    }

    private function SOAP($method, $arguments = array()) {
        if (!isset($this->clientSOAP)) {
            $this->clientSOAP = new SoapClient(self::URI_WSDL);
        }

        try {
            return $this->clientSOAP->{$method}($arguments);
        } catch (Exception $e) {
            $this->errMessage = $e->getMessage();
            return false;
        }
    }

    private function getAuthId() {
        return $this->SOAP('getAuthId')->getAuthIdReturn;
    }

    private function retrieveUserData() {
        return $this->SOAP('retrieveUserData', array(
                'authId' => $this->getSessionToken()
            ))->retrieveUserDataReturn;
    }

    private function getEntityId($idp) {
        switch ($idp) {
            case 'arubaid':
                return 'https://loginspid.aruba.it';

            case 'infocertid':
                return 'https://identity.infocert.it';

            case 'namirialid':
                return 'https://idp.namirialtsp.com/idp';

            case 'posteid':
                return 'https://posteid.poste.it';

            case 'spiditalia':
                return 'https://spid.register.it';

            case 'sielteid':
                return 'https://identity.sieltecloud.it';

            case 'timid':
                return 'https://login.id.tim.it/affwebservices/public/saml2sso';
        }
    }

    public function getLoginURI($idp, $codiceEnte) {
        $this->setSessionToken($this->getAuthId());

        $backUrl = dirname((isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") .
            '/StartRedirect.php?tmpToken=' . App::$tmpToken . '&ditta=' . $codiceEnte;

        $redirect_uri = self::URI_AUTH . '?' . http_build_query(array(
                'backUrl' => $backUrl,
                'authSystem' => 'spid',
                'authId' => $this->getSessionToken(),
                'serviceProvider' => $this->getEnvParam('SERVICE_PROVIDER', $codiceEnte),
                'authLevel' => 'https://www.spid.gov.it/Spid' . $this->getEnvParam('AUTH_LEVEL', $codiceEnte),
                'idp' => $this->getEntityId($idp)
        ));

        return $redirect_uri;
    }

    public function login($codiceEnte) {
        if (!$this->getSessionUserData() && $this->getSessionToken()) {
            $userData = $this->retrieveUserData();
            $this->setSessionToken(null);
            $this->setSessionUserData($userData);
        }

        $userData = $this->getSessionUserData();

        if (!$userData || !$userData->codiceFiscale) {
            return false;
        }

        try {
            $this->accLib->spLogin($userData->codiceFiscale, $codiceEnte);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public function getButtonHtml($nameform) {
        Out::codice("itaGetLib('libs/spid-sp-access-button/js/spid-sp-access-button.min.js', 'SPIDAccessButton');");
        Out::codice("itaGetLib('libs/spid-sp-access-button/css/spid-sp-access-button.min.css');");

        $public = './public/libs/spid-sp-access-button';
        $onClick = 'onclick="itaGo(\'ItaForm\', this, { event: \'onClick\', model: \'' . $nameform . '\', id: \'' . $nameform . '_SPID\', idp: this.dataset.idp });"';

        $HtmlSpid = <<<SPID
<a href="#" class="italia-it-button italia-it-button-size-m button-spid" spid-idp-button="#spid-idp-button-medium-get" aria-haspopup="true" aria-expanded="false" style="vertical-align: middle;">
    <span class="italia-it-button-icon"><img src="$public/img/spid-ico-circle-bb.svg" onerror="this.src='$public/img/spid-ico-circle-bb.png'; this.onerror=null;" alt="" /></span>
    <span class="italia-it-button-text">Entra con SPID</span>
</a>
<div id="spid-idp-button-medium-get" class="spid-idp-button spid-idp-button-tip spid-idp-button-relative" style="text-align: left;">
    <ul id="spid-idp-list-medium-root-get" class="spid-idp-button-menu" aria-labelledby="spid-idp" style="margin: 0;">
        <li class="spid-idp-button-link" data-idp="arubaid">
            <a $onClick data-idp="arubaid"><span class="spid-sr-only">Aruba ID</span><img src="$public/img/spid-idp-arubaid.svg" onerror="this.src='$public/img/spid-idp-arubaid.png'; this.onerror=null;" alt="Aruba ID" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="infocertid">
            <a $onClick data-idp="infocertid"><span class="spid-sr-only">Infocert ID</span><img src="$public/img/spid-idp-infocertid.svg" onerror="this.src='$public/img/spid-idp-infocertid.png'; this.onerror=null;" alt="Infocert ID" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="namirialid">
            <a $onClick data-idp="namirialid"><span class="spid-sr-only">Namirial ID</span><img src="$public/img/spid-idp-namirialid.svg" onerror="this.src='$public/img/spid-idp-namirialid.png'; this.onerror=null;" alt="Namirial ID" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="posteid">
            <a $onClick data-idp="posteid"><span class="spid-sr-only">Poste ID</span><img src="$public/img/spid-idp-posteid.svg" onerror="this.src='$public/img/spid-idp-posteid.png'; this.onerror=null;" alt="Poste ID" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="spiditalia">
            <a $onClick data-idp="spiditalia"><span class="spid-sr-only">SPIDItalia Register.it</span><img src="$public/img/spid-idp-spiditalia.svg" onerror="this.src='$public/img/spid-idp-spiditalia.png'; this.onerror=null;" alt="SpidItalia" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="sielteid">
            <a $onClick data-idp="sielteid"><span class="spid-sr-only">Sielte ID</span><img src="$public/img/spid-idp-sielteid.svg" onerror="this.src='$public/img/spid-idp-sielteid.png'; this.onerror=null;" alt="Sielte ID" /></a>
        </li>
        <li class="spid-idp-button-link" data-idp="timid">
            <a $onClick data-idp="timid"><span class="spid-sr-only">Tim ID</span><img src="$public/img/spid-idp-timid.svg" onerror="this.src='$public/img/spid-idp-timid.png'; this.onerror=null;" alt="Tim ID" /></a>
        </li>
        <li class="spid-idp-support-link">
            <a href="https://www.spid.gov.it" target="_blank">Maggiori informazioni</a>
        </li>
        <li class="spid-idp-support-link">
            <a href="https://www.spid.gov.it/richiedi-spid" target="_blank">Non hai SPID?</a>
        </li>
        <li class="spid-idp-support-link">
            <a href="https://www.spid.gov.it/serve-aiuto" target="_blank">Serve aiuto?</a>
        </li>
    </ul>
</div>
SPID;

        return $HtmlSpid;
    }

}
