<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Utils per il soggetto 
 *
 * @author l.pergolini
 */
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';  //istanzia_Omnis Client
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaControlloLicenzeUtilsInterface.php';

class cwbBtaControlloLicenzeUtils implements cwbBtaControlloLicenzeUtilsInterface {

    private $clientUtils;

    const OMNIS_TYPE = 'omnis';

    public function __construct($type = null) {
        $this->clientUtils = null;
        //defult utilizzo di omnis 
        if ($type == null || self::OMNIS_TYPE === $type) {
            include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
            $this->clientUtils = new itaOmnisClient();
        }
    }

    public function checkLicenza($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BWE_CW2', 'checkLicenza', $methodArgs, 'CITYWARE', false);
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
