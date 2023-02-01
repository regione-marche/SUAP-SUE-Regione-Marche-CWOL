<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaException/ItaException.php';

function cwbCitywareDbTest() {
    $cwbCitywareDbTest = new cwbCitywareDbTest();
    $cwbCitywareDbTest->parseEvent();
    return;
}

class cwbCitywareDbTest extends itaFrontControllerCW {

    public function parseEvent() {
        try {
            $libDbBta = new cwbLibDB_BTA();
            $results = $libDbBta->leggiBtaGrunaz(array());
            Out::msgInfo("Risultati", print_r($results, true));
        } catch (ItaException $ex) {
            Out::msgStop("Errore", $ex->getNativeErroreDesc());
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }    
    
}

?>