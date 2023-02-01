<?php
/**
 * Utils per lo spacchetamento utilizzando omnis 
 * @author l.pergolini
 */
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeFepaUtilsInterface.php';

class cwbBgeFepaUtils implements cwbBgeFepaUtilsInterface {

    private $clientUtils;

    const OMNIS_TYPE = 'omnis';

    public function __construct($type=null) {
        $this->clientUtils = null;
        //defult utilizzo di omnis 
        if ($type == null || self::OMNIS_TYPE === $type) {
            include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
            $this->clientUtils = new itaOmnisClient();
            $this->clientUtils->setParametersStatic();
        }
    }

    public function fepa_fraziona_flusso($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BWE_CW2', 'fepa_fraziona_flusso', $methodArgs);
    }

    public function fepa_esito_committente($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BWE_CW2', 'fepa_esito_committente', $methodArgs);
    }

    public function getErrorCode() {
        return $this->clientUtils->getErrcode();
    }

    public function getErrorMessage() {
        return $this->clientUtils->getErrMessage();
    }

    public function resetStatusError() {
        $this->clientUtils->setErrcode(0);
        $this->clientUtils->setErrMessage('');
    }

}
