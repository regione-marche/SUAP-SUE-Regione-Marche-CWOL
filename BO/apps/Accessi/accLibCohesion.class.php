<?php

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class accLibCohesion {

    private $accLib;
    private $errCode;
    private $errMessage;
    private $envParamKey = 'SSOCOHESION';

    public function __construct() {
        $this->accLib = new accLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    /**
     * 
     * @return \Cohesion2
     */
    protected function getCohesion() {
        include_once ITA_LIB_PATH . '/cohesion2/Cohesion2.php';
        return new Cohesion2;
    }

    protected function getEnvParam($key, $codiceEnte = 'ditta') {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $devLib->setITALWEB(ItaDB::DBOpen('ITALWEB', $codiceEnte));
        $envConfig_rec = $devLib->getEnv_config($this->envParamKey, 'codice', $key, false);
        return $envConfig_rec['CONFIG'];
    }

    public function getLoginURI($codiceEnte) {
        $cohesion = $this->getCohesion();
        $cohesion->useSSO(false);

        $authMethod = $this->getEnvParam('METHOD', $codiceEnte);
        if ($authMethod) {
            $cohesion->setAuthRestriction($authMethod);
        }

        $backUrl = dirname((isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") .
            '/StartRedirect.php?tmpToken=' . App::$tmpToken . '&ditta=' . $codiceEnte;

        return $cohesion->getCheckUrl($backUrl);
    }

    public function login($codiceEnte) {
        $cohesion = $this->getCohesion();

        try {
            if (!$cohesion->verify($_REQUEST['auth'])) {
                $this->errCode = -1;
                $this->errMessage = 'Impossibile effetturare autenticazione Cohesion.';
                return false;
            }

            if (!$cohesion->profile['codice_fiscale']) {
                $this->errCode = -1;
                $this->errMessage = 'Codice fiscale non presente.';
                return false;
            }

            $result = $this->accLib->getTokenFromCF($cohesion->profile['codice_fiscale'], $codiceEnte);
            if ($result['status'] != 0) {
                $this->errCode = -1;
                $this->errMessage = $result['messaggio'];
                return false;
            }

            return $result['token'];
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = $e->getMessage();
            return false;
        }
    }

    public function getButtonHtml($nameform) {
        $onClick = 'onclick="itaGo(\'ItaForm\', this, { event: \'onClick\', model: \'' . $nameform . '\', id: \'' . $nameform . '_Cohesion\' });"';

        $HtmlSpid = <<<SPID
<a href="#" $onClick style="vertical-align: middle; text-decoration: none; font-weight: 600; margin: 0 auto; background-color: #41a837; color: #fff; display: inline-block;">
    <img style="height: 36px; vertical-align: middle; margin: 0 .4em 0 1em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAA8CAMAAAAT6xnzAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAEgUExURQAAAP////////////////7+/v7+/v7+/v7+/v////////7+/vLO5vOFsf7+/v7W6v7+/v7+/v////7+/v7+/v////7+/v7+/v7+/vnG4v7+/v////7+/v7+/v7+/v7+/v////7+/v7+/v////7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v////7+/v7+/v7+/v7+/vnI3/7+/v7+/v3t9P7+/v7+/v7+/v////7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v////7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v7+/v36+/7+/v7+/v////7+/v7+/v7+/v39/f7+/v7+/v7+/v7+/v7+/v/9/f///0Y0SGQAAABedFJOUwAAAQMFBwsNDhAREhUXFxkZHB4jJygpKiwtLjAxNDc5PD9IUFNUW1xeX2dpdHV+gIKEi5GSk5SVlZeeoKOoqauutLq/xcrM0drc3d/g4+Tl6u3u7/Dz9vf5+vv8/f5aEm4mAAABm0lEQVRIx+2WR0MCMRCFfaio2Atix967Yi9YQRF7wwXf//8XHrawkNns7kE96Ltlkm8n2ZlkpgahVfOTSNvqZEcohLa6gyJFkusAULfH80BIhGTZkE0HQAxyxG3Kd/oiJbcTAAD9ECoIJobCegHwEdYLgGMd0qUgg06geCLHhazYeoEVmpMRlxsqahSQmTJCSXKO7WsIEUmZ5j57zQUA4EaHgNwAktaKF3uupEOWSMSsBQlnrmgasvIVIx/VTzK/DADTMlLr9UcBoEm++4ZJHEoZ0iwj8kkt7UjIq0mMemRiQTy+xgmAcQU5UpHMqRvZFEJZzSyQ5H2PM373ROzLOGWNo87CFi/EypZnaxQrf/vBA0mTV8Ci+Hhue3nxTvrPSuTOg2hwIe1nYlxc2lJiQzlhbOWFcF73V5WkMd3dBQC0Uqpi8bj9LK0JDHWFb5c0QiKIkhxWkIS+vBbVEzHlU5FnSV66DW9c8S3iBsmcM0qS9f5133xEnsyC42zUp1XodQfrIGh3cWsTuRANSWQgQ87/TtvzZ5F/fb++AM3hwK9bZbCzAAAAAElFTkSuQmCC">
    <span style="display: inline-block; border-left: 1px solid rgba( 255, 255, 255, .15 ); padding: .55em 1em; font-size: 1.5em; font-family: 'Titillium Web',HelveticaNeue,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;">Accedi con Cohesion</span>
</a>
SPID;

        return $HtmlSpid;
    }

}
