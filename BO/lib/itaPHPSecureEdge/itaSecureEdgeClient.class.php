<?php

/**
 *
 * Classe per collegamento ws cityportal per secureedge 
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPSecureEdge
 * @author     Luca Cardinali <l.cardinali@apra.it>
 * @version    21.01.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPCityportal/itaPHPCityportal.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class itaSecureEdgeClient {

    const WS_TIMEOUT_DEFAULT = 2400;
    const KEY_TIPO_TIMBRO_DIGITALE = 'timbroDigitale';
    const PATH_TIMBRO = 'ItaFrontOfficeServlet';
    const METODO_TIMBRO = 'timbroSecureEdge';

    private $cityportalUtils;
    private $cityportalEndpoint;
    private $timeout;
    private $result;
    private $error;

    public function __construct() {
        $devLib = new devLib();
        $endpoint = $devLib->getEnv_config('SECUREEDGE', 'codice', 'SECUREEDGE_ENDPOINT', false);
        $this->cityportalEndpoint = $endpoint['CONFIG'];
        $this->cityportalUtils = new itaPHPCityportal();
        $this->timeout = self::WS_TIMEOUT_DEFAULT;
    }

    /**
     * esegue il timbro secureedge
     * 
     * @param binary $testo binario del pdf del testo 
     * @param string $commento 
     * 
     * @return string
     */
    public function timbra($testo, $commento = '') {
        $this->clearResult();
        $timbroAttivo = $this->cityportalUtils->getParamByKey(self::KEY_TIPO_TIMBRO_DIGITALE);
        if ($timbroAttivo && intval($timbroAttivo['VALORE']) !== 1) {
            $this->handleError("Timbro secureEdge non attivo");
            return false;
        }
      
        if (!$testo) {
            $this->handleError("Testo mancante");
            return false;
        }

        $data = array(
            'METHOD' => self::METODO_TIMBRO,
            'COMMENTO' => $commento,
            'TESTO' => base64_encode($testo)
        );
        $response = $this->ws_call(self::PATH_TIMBRO, $data);
        if ($response) {
            $this->result = base64_decode($response);
            return true;
        }

        return false;
    }

    private function ws_call($path, $data) {
        if (!$this->cityportalEndpoint) {
            $this->handleError("Configurazione endpoint mancante");
            return false;
        }
        if (!$path) {
            $this->handleError("Configurazione path mancante");
            return false;
        }
        $restClient = new itaRestClient();
        $restClient->setTimeout($this->timeout);
        $restClient->setCurlopt_url($this->cityportalEndpoint);
        $response = $restClient->post($path, $data);
        if (!$response) {
            $this->handleError($restClient->getErrMessage());
            return false;
        }

        if ($restClient->getHttpStatus() != 200) {
            $this->handleError($restClient->getResult());
            return false;
        }

        return $restClient->getResult();
    }

    function getTimeout() {
        return $this->timeout;
    }

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function setResult($result) {
        $this->result = $result;
    }

    function setError($error) {
        $this->error = $error;
    }

    public function handleError($err) {
        $this->result = null;
        $this->error = $err;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
    }

}

?>
