<?php

abstract class proWsClientFascicolazione {

    protected $keyConfigParam;
    protected $arrConfigParams;

    protected abstract function getClientType();

    public function setKeyConfigParams($key) {
        $this->keyConfigParam = $key;
    }

    public function setArrConfigParams($clientParams) {
        $this->arrConfigParam = $clientParams;
    }

}

?>