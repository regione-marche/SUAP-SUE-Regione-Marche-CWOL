<?php

interface cwbBgeFepaUtilsInterface {

    public function fepa_fraziona_flusso($methodArgs = array());

    public function fepa_esito_committente($methodArgs = array());

    public function getErrorCode();

    public function getErrorMessage();

    public function resetStatusError();
}
