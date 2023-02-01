<?php

interface cwbBtaControlloLicenzeUtilsInterface {

    public function checkLicenza($methodArgs = array());

    public function getErrorCode();

    public function getErrorMessage();

    public function resetStatusError();
}
