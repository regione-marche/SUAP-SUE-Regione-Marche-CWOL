<?php

abstract class proWsClient {

    protected $keyConfigParam;
    protected $arrConfigParams;

    protected abstract function leggiProtocollazione($params);

    protected abstract function inserisciProtocollazionePartenza($elementi);

    protected abstract function inserisciProtocollazioneArrivo($elementi);

    protected abstract function inserisciDocumentoInterno($elementi);

    protected abstract function leggiDocumentoInterno($params);

    protected abstract function getClientType();

    public function setKeyConfigParams($key) {
        $this->keyConfigParam = $key;
    }

    public function setArrConfigParams($clientParams) {
        $this->arrConfigParam = $clientParams;
    }

}

?>