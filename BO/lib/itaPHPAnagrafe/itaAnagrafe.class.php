<?php

require_once ITA_LIB_PATH . '/itaPHPAnagrafe/provider.Abstract.class.php';

class itaAnagrafe {

    private $provider;
    private $providerObj;

    function __construct($provider = 'Italsoft') {
        $this->provider = $provider;
        $driver_provider = dirname(__FILE__) . "/provider.$this->provider.class.php";
        if (!file_exists($driver_provider)) {
            throw new Exception("Provider dati Anagrafe $driver_provider non trovato");
        }
        include_once($driver_provider);
        $classe = 'provider_' . $this->provider;
        $this->providerObj = new $classe();
    }

    public static function getProviderInstance($provider = 'Italsoft') {
        try {
            return new itaAnagrafe($provider);
        } catch (Exception $e) {
            return null;
        }
    }

    function getProviderType() {
        return $this->providerObj->getProviderType();
    }

    function getLastExitCode() {
        return $this->providerObj->getlastExitCode();
    }

    function getLastMessage() {
        return $this->providerObj->getlastMessage();
    }

    public function getReturnModel() {
        return $this->providerObj->getReturnModel();
    }

    public function setReturnModel($returnModel) {
        return $this->providerObj->setReturnModel($returnModel);
    }

    function getCittadiniLista($param) {
        return $this->providerObj->getCittadiniLista($param);
    }

    function getCittadinoFamiliari($param) {
        return $this->providerObj->getCittadinoFamiliari($param);
    }

    function getCittadinoVariazioni($param) {
        return $this->providerObj->getCittadinoVariazioni($param);
    }

    function getVie() {
        return $this->providerObj->getVie();
    }

    function getStatoMatrimonio($param) {
        return $this->providerObj->getStatoMatrimonio($param);
    }

}

?>
