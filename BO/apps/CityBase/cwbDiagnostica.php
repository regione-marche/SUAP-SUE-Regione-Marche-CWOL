<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaException/ItaException.php';
include_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaOmnis.class.php';
include_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once dirname(__FILE__) . '/../../lib/itaPHPUnit/itaPHPUnit.class.php';

include_once ITA_BASE_PATH . '/test/unit/framework/PathTest.php';
include_once ITA_BASE_PATH . '/test/unit/framework/AlfrescoTest.php';
include_once ITA_BASE_PATH . '/test/unit/framework/LibreOfficeTest.php';
include_once ITA_BASE_PATH . '/test/unit/framework/ModuleLoadTest.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';

function cwbDiagnostica() {
    $cwbDiagnostica = new cwbDiagnostica();
    $cwbDiagnostica->parseEvent();
    return;
}

class cwbDiagnostica extends itaFrontControllerCW {

    const CW_DB_TABLE_TEST_OWS = 'BWS_LOG';

    private $alfrescoTest;
    private $libreOfficeTest;
    private $pathTest;
    private $itaPHPUnit;

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->initialize();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_tuttiTest_button':
                        $this->allTests();
                        break;
                    case $this->nameForm . '_cache_button':
                        $this->cacheTests();
                        break;
                    case $this->nameForm . '_cityware_button':
                        $this->citywareTests();
                        break;
                    case $this->nameForm . '_smartagent_button':
                        $this->smartagentTest();
                        break;
                    case $this->nameForm . '_omnis_button':
                        $this->omnisTest();
                        break;
                    case $this->nameForm . '_omnis_connectiondb_button':
                        $this->omnisConnectionDbTest();
                        break;
                    case $this->nameForm . '_omnis_buttonInfo':
                        $this->omnisInfo();
                        break;
                    case $this->nameForm . '_rest_button':
                        $this->restTest();
                        break;
                    case $this->nameForm . '_alfresco_button':
                        $this->alfrescoTest();
                        break;
                    case $this->nameForm . '_moduli_button':
                        $this->moduliTest();
                        break;
                    case $this->nameForm . '_path_button':
                        $this->pathTest();
                        break;
                    case $this->nameForm . '_lock_button':
                        $this->lockTest();
                        break;
//                    case $this->nameForm . '_connectiondb_button':
//                        $this->connectionDbTest();
                        break;

                    case $this->nameForm . '_libreOffice_button':
                        $this->libreOfficeConvTest();
                        break;
                }
                break;
            case 'onHandshake':
                switch ($_POST['id']) {
                    case 'handshake':
                        $this->smartagentHandshakeReturn($_POST['data']);
                        break;
                }
                break;
        }
    }

    private function initialize() {
        $this->nameForm = "cwbDiagnostica";

        $this->initCache();
        $this->initCityware();
        $this->initOmnis();
        $this->initOmnisConnectionDb();
        $this->initRest();
        $this->initSmartagent();
        $this->initPath();
        $this->initAlfresco();
        $this->initModuli();
        $this->initLock();
        $this->initConnectiondb();
        $this->initLibreOfficeConv();
        Out::show($this->nameForm . '_divGestione');
    }

    private function allTests() {
        $this->initialize();
        $this->cacheTests();
        $this->citywareTests();
        $this->smartagentTest();
        $this->omnisTest();
        $this->omnisConnectionDbTest();
        $this->restTest();
        $this->pathTest();
        $this->alfrescoTest();
        $this->moduliTest();
        $this->lockTest();
        //$this->connectionDbTest();
    }

    private function initCache() {
        Out::hide($this->nameForm . '_cache_fail');
        Out::hide($this->nameForm . '_cache_pass');

        Out::hide($this->nameForm . '_cache_creaChiave_fail');
        Out::hide($this->nameForm . '_cache_creaChiave_pass');

        Out::hide($this->nameForm . '_cache_leggiChiave_fail');
        Out::hide($this->nameForm . '_cache_leggiChiave_pass');

        Out::hide($this->nameForm . '_cache_comparaChiave_fail');
        Out::hide($this->nameForm . '_cache_comparaChiave_pass');

        Out::hide($this->nameForm . '_cache_cancellaChiave_fail');
        Out::hide($this->nameForm . '_cache_cancellaChiave_pass');

        Out::hide($this->nameForm . '_cache_testResult');
    }

    private function cacheTests() {
        $this->initCache();

        Out::show($this->nameForm . '_cache_fail');
        Out::hide($this->nameForm . '_cache_pass');

        Out::show($this->nameForm . '_cache_creaChiave_fail');
        Out::hide($this->nameForm . '_cache_creaChiave_pass');

        Out::show($this->nameForm . '_cache_leggiChiave_fail');
        Out::hide($this->nameForm . '_cache_leggiChiave_pass');

        Out::show($this->nameForm . '_cache_comparaChiave_fail');
        Out::hide($this->nameForm . '_cache_comparaChiave_pass');

        Out::show($this->nameForm . '_cache_cancellaChiave_fail');
        Out::hide($this->nameForm . '_cache_cancellaChiave_pass');

        $cache = CacheFactory::newCache();
        if ($cache) {
            $value = "testValue12345@-";

            if ($cache->set("TestCache", $value)) {
                Out::hide($this->nameForm . '_cache_creaChiave_fail');
                Out::show($this->nameForm . '_cache_creaChiave_pass');

                if ($cache->get("TestCache")) {
                    Out::hide($this->nameForm . '_cache_leggiChiave_fail');
                    Out::show($this->nameForm . '_cache_leggiChiave_pass');

                    if ($cache->get("TestCache") === $value) {
                        Out::hide($this->nameForm . '_cache_comparaChiave_fail');
                        Out::show($this->nameForm . '_cache_comparaChiave_pass');

                        if ($cache->delete("TestCache")) {
                            Out::hide($this->nameForm . '_cache_cancellaChiave_fail');
                            Out::show($this->nameForm . '_cache_cancellaChiave_pass');

                            Out::hide($this->nameForm . '_cache_fail');
                            Out::show($this->nameForm . '_cache_pass');
                        }
                    }
                }
            }
        }
        Out::show($this->nameForm . '_cache_testResult');
    }

    private function initCityware() {
        Out::hide($this->nameForm . '_cityware_fail');
        Out::hide($this->nameForm . '_cityware_pass');

        Out::hide($this->nameForm . '_cityware_connessione_fail');
        Out::hide($this->nameForm . '_cityware_connessione_pass');

        Out::hide($this->nameForm . '_cityware_lettura_fail');
        Out::hide($this->nameForm . '_cityware_lettura_pass');

        Out::hide($this->nameForm . '_cityware_testResult');

        // mi leggo le info della connessione per poterle vedere sulla console
        try {
            Out::html($this->nameForm . '_cityware_info_value', "");
            $config = parse_ini_file(ITA_BASE_PATH . '/config/connections.ini', true);
            $connectionName = cwbLib::CONNECTION_NAME . cwbParGen::getSessionVar("ditta");
            if (array_key_exists($connectionName, $config)) {
                $host = $config[$connectionName]['host'];
                $db = $config[$connectionName]['database'];
            } else {
                $host = $config[cwbLib::CONNECTION_NAME]['host'];
                $db = $config[cwbLib::CONNECTION_NAME]['database'];
            }
            $connectionInfo = "Host:<b> " . $host . '</b> DB: <b>' . $db . '</b>';

            Out::html($this->nameForm . '_cityware_info_value', $connectionInfo);
        } catch (Exception $exc) {
            
        }
    }

    private function citywareTests() {
        $dbLib = new cwbLibDB_BTA();

        Out::show($this->nameForm . '_cityware_fail');
        Out::hide($this->nameForm . '_cityware_pass');

        Out::show($this->nameForm . '_cityware_connessione_fail');
        Out::hide($this->nameForm . '_cityware_connessione_pass');

        Out::show($this->nameForm . '_cityware_lettura_fail');
        Out::hide($this->nameForm . '_cityware_lettura_pass');

        $checkConnection = true;
        try {
            ItaDB::DBQuery($dbLib->getCitywareDB(), "SELECT * FROM BTA_GRUNAZ");
        } catch (Exception $e) {
            $checkConnection = false;
        }
        if ($checkConnection) {
            Out::hide($this->nameForm . '_cityware_connessione_fail');
            Out::show($this->nameForm . '_cityware_connessione_pass');

            try {
                $data = $dbLib->leggiBtaGrunaz();
            } catch (Exception $e) {
                $data = null;
            }
            $checkData = (isSet($data) && is_array($data) && !empty($data));

            if (!$checkData) {
                Out::show($this->nameForm . '_cityware_fail');
                Out::hide($this->nameForm . '_cityware_pass');
                Out::show($this->nameForm . '_cityware_lettura_fail');
                Out::hide($this->nameForm . '_cityware_lettura_pass');
            } else {
                Out::hide($this->nameForm . '_cityware_fail');
                Out::show($this->nameForm . '_cityware_pass');
                Out::hide($this->nameForm . '_cityware_lettura_fail');
                Out::show($this->nameForm . '_cityware_lettura_pass');
            }
        }

        Out::show($this->nameForm . '_cityware_testResult');
    }

    private function initSmartagent() {
        Out::hide($this->nameForm . '_smartagent_fail');
        Out::hide($this->nameForm . '_smartagent_pass');

        Out::hide($this->nameForm . '_smartagent_enabled_fail');
        Out::hide($this->nameForm . '_smartagent_enabled_pass');

        Out::hide($this->nameForm . '_smartagent_handshake_fail');
        Out::hide($this->nameForm . '_smartagent_handshake_pass');

        Out::hide($this->nameForm . '_smartagent_testResult');
    }

    private function smartagentTest() {
        $this->initSmartagent();

        Out::show($this->nameForm . '_smartagent_fail');
        Out::hide($this->nameForm . '_smartagent_pass');

        Out::show($this->nameForm . '_smartagent_enabled_fail');
        Out::hide($this->nameForm . '_smartagent_enabled_pass');

        Out::show($this->nameForm . '_smartagent_handshake_fail');
        Out::hide($this->nameForm . '_smartagent_handshake_pass');

        Out::show($this->nameForm . '_smartagent_testResult');

        $smartAgent = new SmartAgent();
        if ($smartAgent->isEnabled()) {
            Out::hide($this->nameForm . '_smartagent_enabled_fail');
            Out::show($this->nameForm . '_smartagent_enabled_pass');

            $smartAgent->handshake("cwbDiagnostica", 'handshake', onHandshake);
        }
    }

    private function smartagentHandshakeReturn($data) {
        if (stripos($data, "HANDSHAKE OK") === 0) {
            Out::hide($this->nameForm . '_smartagent_fail');
            Out::show($this->nameForm . '_smartagent_pass');

            Out::hide($this->nameForm . '_smartagent_handshake_fail');
            Out::show($this->nameForm . '_smartagent_handshake_pass');

            Out::show($this->nameForm . '_smartagent_testResult');
        }
    }

    private function initOmnis() {
        Out::hide($this->nameForm . '_omnis_fail');
        Out::hide($this->nameForm . '_omnis_pass');

        Out::hide($this->nameForm . '_omnis_connection_fail');
        Out::hide($this->nameForm . '_omnis_connection_pass');
        Out::innerHtml($this->nameForm . '_omnis_connection_errorMessage', '');
        Out::hide($this->nameForm . '_omnis_connection_errorMessage');

        Out::hide($this->nameForm . '_omnis_testResult');
    }

    private function initOmnisConnectionDb() {
        Out::hide($this->nameForm . '_omnis_connectiondb_fail');
        Out::hide($this->nameForm . '_omnis_connectiondb_pass');

        Out::hide($this->nameForm . '_omnis_connectiondb_connection_fail');
        Out::hide($this->nameForm . '_omnis_connectiondb_connection_pass');
        Out::innerHtml($this->nameForm . '_omnis_connectiondb_connection_errorMessage', '');
        Out::hide($this->nameForm . '_omnis_connectiondb_connection_errorMessage');

        Out::hide($this->nameForm . '_omnis_connectiondb_testResult');
    }

    // chiamata in get al servizio di cityportal per reperire le info di connessione
    private function omnisInfo() {
        $devLib = new devLib();
        $configProtocol = $devLib->getEnv_config('OMNIS', 'codice', 'PROTOCOL', false);
        $configWsUrl = $devLib->getEnv_config('OMNIS', 'codice', 'WEB_SERVER_URL', false);
        $configAppServerUrl = $devLib->getEnv_config('OMNIS', 'codice', 'APP_SERVER_URL', false);
        $configOmnisCGI = $devLib->getEnv_config('OMNIS', 'codice', 'OMNIS_CGI', false);
        $configDefaultLib = $devLib->getEnv_config('OMNIS', 'codice', 'DEFAULT_LIBRARY', false);
        $configRemoteTask = $devLib->getEnv_config('OMNIS', 'codice', 'REMOTE_TASK', false);

        $info = file_get_contents($configProtocol['CONFIG'] . "://" . $configWsUrl['CONFIG'] . $configOmnisCGI['CONFIG'] . "?OmnisServer=" . $configAppServerUrl['CONFIG'] . "&OmnisClass=" . $configRemoteTask['CONFIG'] . "&OmnisLibrary=" . $configDefaultLib['CONFIG'] . "&cityportal=1");
        $info = str_replace("Test Cityportal", "Test CWOL", $info);
        Out::msgInfo("Info", $info, 'auto', '1100px', 'desktopBody', false, true);
    }

    private function omnisConnectiondbInfo() {
        
    }

    private function omnisTest() {
        $this->initOmnis();

        Out::show($this->nameForm . '_omnis_fail');
        Out::hide($this->nameForm . '_omnis_pass');

        Out::show($this->nameForm . '_omnis_connection_fail');
        Out::hide($this->nameForm . '_omnis_connection_pass');

        Out::hide($this->nameForm . '_omnis_connection_errorMessage');


        $omnis = itaOmnis::getOmnisClient();

        $object = $_POST[$this->nameForm . '_omnis_object'];
        $method = $_POST[$this->nameForm . '_omnis_method'];
        $parameters = json_decode($_POST[$this->nameForm . '_omnis_parameters'], true);

        $result = $omnis->callExecute($object, $method, $parameters);
        if (isSet($result['RESULT']['EXITCODE'])) {

            Out::hide($this->nameForm . '_omnis_fail');
            Out::show($this->nameForm . '_omnis_pass');

            Out::hide($this->nameForm . '_omnis_connection_fail');
            Out::show($this->nameForm . '_omnis_connection_pass');

            $result = var_export($result, true);
            Out::innerHtml($this->nameForm . '_omnis_connection_errorMessage', $result);
            Out::show($this->nameForm . '_omnis_connection_errorMessage');
        }

        Out::show($this->nameForm . '_omnis_testResult');
    }

    private function omnisConnectionDbTest() {
        $this->initOmnisConnectionDb();

        Out::show($this->nameForm . '_omnis_connectiondb_fail');
        Out::hide($this->nameForm . '_omnis_connectiondb_pass');

        Out::show($this->nameForm . '_omnis_connectiondb_connection_fail');
        Out::hide($this->nameForm . '_omnis_connectiondb_connection_pass');

        Out::hide($this->nameForm . '_omnis_connectiondb_connection_errorMessage');


        $omnis = itaOmnis::getOmnisClient();

        $object = $_POST[$this->nameForm . '_omnis_connectiondb_object'];
        $method = $_POST[$this->nameForm . '_omnis_connectiondb_method'];
        $uniqueId = uniqid();
        $parameters = array($uniqueId);
        $erroMessage = "";
        $result = $omnis->callExecute($object, $method, $parameters);
        if (isSet($result['RESULT']['EXITCODE'])) {
            //effettuo la lettura della tabella log per chiave 
            $lib = new cwbLibDB_BWE();

            $key = $result["RESULT"]["LIST"]["ROW"]["KLOG"];
            if (!$key) {
                $erroMessage = "Errore durante inserimento della tabella BWE_LOG lato ommis";
            } else {
                $res = $lib->leggiBwsLogChiave($key);
                if (!$res) {
                    $erroMessage = "Errore durante la lettura della tabella BWE_LOG lato php";
                } else if (!is_resource($res["RXTX_DATA"]) || stream_get_contents($res["RXTX_DATA"]) !== $uniqueId) {
                    $erroMessage = "Non stai leggendo lo stesso database tra omnis e php";
                } else {

                    // Sostituisce campo strema per visualizzazione nel div dei risultati
                    unset($result['RESULT']['LIST']['ROW']["RXTX_DATA"]);
                    $result['RESULT']['LIST']['ROW']["RXTX_DATA"] = $uniqueId;

                    Out::hide($this->nameForm . '_omnis_connectiondb_fail');
                    Out::show($this->nameForm . '_omnis_connectiondb_pass');

                    Out::hide($this->nameForm . '_omnis_connectiondb_connection_fail');
                    Out::show($this->nameForm . '_omnis_connectiondb_connection_pass');

                    $result = var_export($result, true);
                    Out::innerHtml($this->nameForm . '_omnis_connectiondb_connection_errorMessage', $result);
                    Out::show($this->nameForm . '_omnis_connectiondb_connection_errorMessage');

                    // Cancella record
                    $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::CW_DB_TABLE_TEST_OWS));
                    $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                    $modelServiceData->addMainRecord(self::CW_DB_TABLE_TEST_OWS, $res);
                    $recordInfo = 'Cancellazione record BWS_LOG per test console diagnostica (ID=' . $res['KLOG'] . ')';
                    $modelService->deleteRecord($lib->getCitywareDB(), self::CW_DB_TABLE_TEST_OWS, $modelServiceData->getData(), $recordInfo);
                }
            }
        }
        if ($erroMessage) {
            Out::innerHtml($this->nameForm . '_omnis_connectiondb_connection_errorMessage', $erroMessage);
            Out::show($this->nameForm . '_omnis_connectiondb_connection_errorMessage');
        }
        Out::show($this->nameForm . '_omnis_connectiondb_testResult');
    }

    private function initRest() {
        Out::hide($this->nameForm . '_rest_errorDiv');

        Out::hide($this->nameForm . '_rest_fail');
        Out::hide($this->nameForm . '_rest_pass');

        Out::hide($this->nameForm . '_rest_login_fail');
        Out::hide($this->nameForm . '_rest_login_pass');
        Out::hide($this->nameForm . '_rest_login_errorMessage');

        Out::hide($this->nameForm . '_rest_checkToken_fail');
        Out::hide($this->nameForm . '_rest_checkToken_pass');
        Out::hide($this->nameForm . '_rest_checkToken_errorMessage');

        Out::hide($this->nameForm . '_rest_destroy_fail');
        Out::hide($this->nameForm . '_rest_destroy_pass');
        Out::hide($this->nameForm . '_rest_destroy_errorMessage');


        Out::hide($this->nameForm . '_rest_testResult');
    }

    private function restTest() {
        if (isSet($_POST[$this->nameForm . '_rest_username']) && trim($_POST[$this->nameForm . '_rest_username'] != '') &&
                isSet($_POST[$this->nameForm . '_rest_password']) && trim($_POST[$this->nameForm . '_rest_password'] != '') &&
                isSet($_POST[$this->nameForm . '_rest_ente']) && trim($_POST[$this->nameForm . '_rest_ente'] != '')) {
            $this->initRest();

            Out::show($this->nameForm . '_rest_fail');
            Out::hide($this->nameForm . '_rest_pass');

            Out::show($this->nameForm . '_rest_login_fail');
            Out::hide($this->nameForm . '_rest_login_pass');

            Out::show($this->nameForm . '_rest_checkToken_fail');
            Out::hide($this->nameForm . '_rest_checkToken_pass');

            Out::show($this->nameForm . '_rest_destroy_fail');
            Out::hide($this->nameForm . '_rest_destroy_pass');

            preg_match('/^.*(?=(\Start.php))/', $_SERVER['HTTP_REFERER'], $wsRestUri);
            $wsRestUri = $wsRestUri[0] . "wsrest/service.php";


            $restClient = new itaRestClient();
            $restClient->setTimeout(10);
            $restClient->setCurlopt_url($wsRestUri);

            //Login
            $data = array(
                'UserName' => $_POST[$this->nameForm . '_rest_username'],
                'Password' => $_POST[$this->nameForm . '_rest_password'],
                'DomainCode' => $_POST[$this->nameForm . '_rest_ente']
            );
            $restCall = $restClient->get("/login/GetItaEngineContextToken", $data);
            if ($restCall && $restClient->getHttpStatus() === 200 && trim($restClient->getResult()) != '') {
                $token = str_replace('"', '', $restClient->getResult());

                Out::hide($this->nameForm . '_rest_login_fail');
                Out::show($this->nameForm . '_rest_login_pass');

                $data = array('TokenKey' => $token);
                $restCall = $restClient->get("/login/CheckItaEngineContextToken", $data);
                if ($restCall && $restClient->getHttpStatus() === 200 && $restClient->getResult() == '"Valid"') {
                    Out::hide($this->nameForm . '_rest_checkToken_fail');
                    Out::show($this->nameForm . '_rest_checkToken_pass');

                    $data = array('TokenKey' => $token);
                    $restCall = $restClient->get("/login/DestroyItaEngineContextToken", $data);
                    if ($restCall && $restClient->getHttpStatus() === 200 && $restClient->getResult() == '"Success"') {
                        Out::hide($this->nameForm . '_rest_fail');
                        Out::show($this->nameForm . '_rest_pass');

                        Out::hide($this->nameForm . '_rest_destroy_fail');
                        Out::show($this->nameForm . '_rest_destroy_pass');
                    } else {
                        Out::innerHtml($this->nameForm . '_rest_destroy_errorMessage', $restClient->getResult());
                        Out::show($this->nameForm . '_rest_destroy_errorMessage');
                    }
                } else {
                    Out::innerHtml($this->nameForm . '_rest_checkToken_errorMessage', $restClient->getResult());
                    Out::show($this->nameForm . '_rest_checkToken_errorMessage');
                }
            } else {
                if ($restClient->getHttpStatus() === 200) {
                    Out::innerHtml($this->nameForm . '_rest_login_errorMessage', 'Non è stato restituito un token');
                } else {
                    Out::innerHtml($this->nameForm . '_rest_login_errorMessage', $restClient->getResult());
                }
                Out::show($this->nameForm . '_rest_login_errorMessage');
            }
            Out::show($this->nameForm . '_rest_testResult');
        } else {
            Out::show($this->nameForm . '_rest_errorDiv');
        }
    }

    private function pathTest() {
        $this->initPath();

        $this->pathTest = new PathTest();
        //Config.ini e ItaPath
        $message = "";
        $paths = $this->pathTest->pathProvider();

        foreach ($paths as $key => $path) {
            $errorMessage = $this->checkPathsOrFile($path);
            if ($errorMessage) {
                $message .= "<b> Key:" . $key . " Path:" . $path . "</b> Error:" . $errorMessage . '<br>';
            }
        }

        if ($message) {
            Out::hide($this->nameForm . '_path_pass');
            Out::show($this->nameForm . '_path_fail');
            Out::show($this->nameForm . '_path_error_result');
            Out::innerHtml($this->nameForm . '_path_error_result', $message);
        } else {
            Out::show($this->nameForm . '_path_pass');
            Out::hide($this->nameForm . '_path_fail');
            Out::hide($this->nameForm . '_path_error_result');
        }
    }

    private function checkPathsOrFile($path) {
        $message = "";
        try {
            $this->pathTest->testPathOrFile($path);
        } catch (\Exception $exc) {
            $message = $path . ":" . $exc->getMessage();
        }
        return $message;
    }

    private function initPath() {
        Out::hide($this->nameForm . '_path_fail');
        Out::hide($this->nameForm . '_path_pass');
    }

    private function libreOfficeConvTest() {
        $this->initLibreOfficeConv();

        $message = "";
        $this->libreOfficeTest = new LibreOfficeTest();
        $errorMessage = $this->libreOfficeConvParams();
        if ($errorMessage) {
            $message .= "Error:" . $errorMessage . '<br>';
            Out::hide($this->nameForm . '_libreOffice_parametri_pass');
            Out::show($this->nameForm . '_libreOffice_parametri_fail');
        } else {
            Out::show($this->nameForm . '_libreOffice_parametri_pass');
            Out::hide($this->nameForm . '_libreOffice_parametri_fail');

            $errorMessage = $this->libreOfficeConvExecute();
            if ($errorMessage) {
                $message .= "Error:" . $errorMessage . '<br>';
                Out::hide($this->nameForm . '_libreOffice_conv_pass');
                Out::show($this->nameForm . '_libreOffice_conv_fail');
            } else {
                Out::show($this->nameForm . '_libreOffice_conv_pass');
                Out::hide($this->nameForm . '_libreOffice_conv_fail');
            }
        }
        if ($message) {
            Out::hide($this->nameForm . '_libreOffice_pass');
            Out::show($this->nameForm . '_libreOffice_fail');
            Out::show($this->nameForm . '_libreOffice_error_result');
            Out::innerHtml($this->nameForm . '_libreOffice_error_result', $errorMessage);
        } else {
            Out::show($this->nameForm . '_libreOffice_pass');
            Out::hide($this->nameForm . '_libreOffice_fail');
            Out::hide($this->nameForm . '_libreOffice_error_result');
        }
    }

    private function libreOfficeConvParams() {
        try {
            $this->libreOfficeTest->test_Params();
        } catch (\Exception $exc) {
            $message = $exc->getMessage();
        }
        return $message;
    }

    private function libreOfficeConvExecute() {
        try {
            $this->libreOfficeTest->test_Conv();
        } catch (\Exception $exc) {
            $message = $exc->getMessage();
        }
        return $message;
    }

    private function initLibreOfficeConv() {
        Out::hide($this->nameForm . '_libreOffice_fail');
        Out::hide($this->nameForm . '_libreOffice_pass');

        Out::hide($this->nameForm . '_libreOffice_parametri_fail');
        Out::hide($this->nameForm . '_libreOffice_parametri_pass');

        Out::hide($this->nameForm . '_libreOffice_conv_fail');
        Out::hide($this->nameForm . '_libreOffice_conv_pass');

        Out::hide($this->nameForm . '_libreOffice_error_result');
    }

    private function alfrescoTest() {
        $this->initAlfresco();

        $message = "";
        $this->alfrescoTest = new AlfrescoTest();
        $errorMessage = $this->alcityVersion();
        if ($errorMessage) {
            $message .= "Error:" . $errorMessage . '<br>';
            Out::hide($this->nameForm . '_alfresco_alfcity_pass');
            Out::show($this->nameForm . '_alfresco_alfcity_fail');
        } else {
            Out::show($this->nameForm . '_alfresco_alfcity_pass');
            Out::hide($this->nameForm . '_alfresco_alfcity_fail');
        }
        $errorMessage = $this->alfrescoManageDoc();
        if ($errorMessage) {
            $message .= "Error:" . $errorMessage . '<br>';
            Out::hide($this->nameForm . '_alfresco_manage_pass');
            Out::show($this->nameForm . '_alfresco_manage_fail');
            Out::innerHtml($this->nameForm . '_alfresco_manage_error_result', $errorMessage);
        } else {
            Out::show($this->nameForm . '_alfresco_manage_pass');
            Out::hide($this->nameForm . '_alfresco_manage_fail');
        }
        if ($message) {
            Out::hide($this->nameForm . '_alfresco_pass');
            Out::show($this->nameForm . '_alfresco_fail');
        } else {
            Out::show($this->nameForm . '_alfresco_pass');
            Out::hide($this->nameForm . '_alfresco_fail');
            Out::hide($this->nameForm . '_alfresco_manage_error_result');
        }
    }

    private function initAlfresco() {
        Out::hide($this->nameForm . '_alfresco_fail');
        Out::hide($this->nameForm . '_alfresco_pass');

        Out::hide($this->nameForm . '_alfresco_alfcity_fail');
        Out::hide($this->nameForm . '_alfresco_alfcity_pass');

        Out::hide($this->nameForm . '_alfresco_manage_fail');
        Out::hide($this->nameForm . '_alfresco_manage_pass');
    }

    private function alcityVersion() {
        $message = "";
        try {
            $this->alfrescoTest->testVersion();
        } catch (\Exception $exc) {
            $message = $exc->getMessage();
        }
        return $message;
    }

    private function alfrescoManageDoc() {
        $message = "";
        try {
            $this->alfrescoTest->testCRUDDoc();
        } catch (\Exception $exc) {
            $message = $exc->getMessage();
        }
        return $message;
    }

    private function moduliTest() {
        $this->initModuli();

        $this->moduliTest = new ModuleLoadTest();
        $this->moduliTest->setSystemModulesLoaded(get_loaded_extensions());
        //Config.ini e ItaPath
        $message = "";
        $modules = $this->moduliTest->loadModulesToVerify();

        foreach ($modules as $module) {
            $errorMessage = $this->testModulo($module);
            if ($errorMessage) {
                $message .= "<b> Modulo:" . $module[0] . "</b> Error:" . $errorMessage . '<br>';
            }
        }
        if ($message) {
            Out::hide($this->nameForm . '_moduli_pass');
            Out::show($this->nameForm . '_moduli_fail');
            Out::show($this->nameForm . '_moduli_error_result');
            Out::innerHtml($this->nameForm . '_moduli_error_result', $message);
        } else {
            Out::show($this->nameForm . '_moduli_pass');
            Out::hide($this->nameForm . '_moduli_fail');
            Out::hide($this->nameForm . '_moduli_error_result');
        }
    }

    private function testModulo($modulo) {
        $message = "";
        try {
            $this->moduliTest->testLoadModule($modulo[0], $modulo[1]);
        } catch (\Exception $exc) {
            $message = $modulo[0] . ":" . $exc->getMessage();
        }
        return $message;
    }

    private function initModuli() {
        Out::hide($this->nameForm . '_moduli_fail');
        Out::hide($this->nameForm . '_moduli_pass');
    }

    private function lockTest() {
        $this->initLock();
        try {
            $lib_bdi = new cwbLibDB_BDI();
            $lib_bdi->leggiBdiIndici(array(), false);
            // se non ha dato errore la lettura della tabella 
            Out::show($this->nameForm . '_lock_pass');
            Out::hide($this->nameForm . '_lock_fail');
        } catch (Exception $ex) {
            Out::hide($this->nameForm . '_lock_pass');
            Out::show($this->nameForm . '_lock_fail');
        }
    }

    private function initLock() {
        Out::hide($this->nameForm . '_lock_fail');
        Out::hide($this->nameForm . '_lock_pass');
    }

//    private function connectionDbTest() {
//        $this->initConnectiondb();
//        $configs = parse_ini_file(ITA_BASE_PATH . '/config/connections.ini', true);
//        //Scorro tutti i config e provo a connettermi al db 
//        $message = "";
//        $status = true;
//        foreach ($configs as $name => $config) {
//            $errorMessage = $this->connDb($name);
//            if (strlen($errorMessage)) {
//                $status = false;
//            }
//            $message .= "ConnectionName: " . $name . " Status: " . (strlen($errorMessage) > 0 ? '<span style="color:red;">Fail</span>' : '<span style="color:green;">Pass</span>') . '<br>';
//        }
//        if (!$status) {
//            Out::hide($this->nameForm . '_connectiondb_pass');
//            Out::show($this->nameForm . '_connectiondb_fail');
//        } else {
//            Out::show($this->nameForm . '_connectiondb_pass');
//            Out::hide($this->nameForm . '_connectiondb_fail');
//        }
//        Out::show($this->nameForm . '_connectiondb_result');
//        Out::innerHtml($this->nameForm . '_connectiondb_result', $message);
//    }
//
//    private function connDb($name) {
//        $message = "";
//        try {
//            $conn = ItaDB::DBOpen($name);            
//            if (!$conn) {
//                return "Impossibile creare PDOManager per connessione $name";
//            }
//            $result = ItaDB::DBQuery($conn, 'SELECT 1');
//            if (!$result) {
//                return "Connessione al database $name fallita";
//            }
//        } catch (Exception $exc) {
//            $message = $exc->getTraceAsString();
//        }
//
//        return $message;
//    }

    private function initConnectiondb() {
        Out::hide($this->nameForm . '_connectiondb');
//        Out::hide($this->nameForm . '_connectiondb_fail');
//        Out::hide($this->nameForm . '_connectiondb_pass');
    }

}

