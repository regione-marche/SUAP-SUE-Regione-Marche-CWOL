<?php
class itaCityWare {

    private $provider;
    private $providerObj;

    function __construct($provider = 'CityWare') {
        $this->provider = $provider;
        $driver_provider = dirname(__FILE__) . "/provider.$this->provider.class.php";
        if (!file_exists($driver_provider)) {
            throw new Exception("Provider dati Anagrafe $driver_provider non trovato");
        }
        include_once($driver_provider);
        $classe = 'provider_' . $this->provider;
        $this->providerObj = new $classe();
    }

    public static function getProviderInstance($provider = 'CityWare') {
        try {
            return new itaCityWare($provider);
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

    function getCittadiniLista($param){
        return $this->providerObj->getCittadiniLista($param);
    }
    
    function getCittadinoFamiliari($param){
        return $this->providerObj->getCittadinoFamiliari($param);
    }

    function getCittadinoVariazioni($param){
        return $this->providerObj->getCittadinoVariazioni($param);
    }

    function getVie(){
        return $this->providerObj->getVie();
    }
    
    function getStatoMatrimonio($param){
        return $this->providerObj->getStatoMatrimonio($param);
    }
    

}

?>
