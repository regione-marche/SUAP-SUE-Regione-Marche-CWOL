<?php

interface cwbBtaSoggettoUtilsInterface {
    
    public function calcolaCodFisc($methodArgs=array());

    public function repDatidaCodFisc($methodArgs=array());

    public function getErrorCode();

    public function getErrorMessage();

    public function resetStatusError();
}
