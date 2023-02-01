<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function proTestVisibilitaRuoli() {
    $proTestVisibilitaRuoli = new proTestVisibilitaRuoli();
    $proTestVisibilitaRuoli->parseEvent();
    return;
}

class proTestVisibilitaRuoli extends itaModel {

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $time1 = microtime(true);
                $proSoggetto = new proSoggetto();
                $arrayVisibilita = proSoggetto::getVisibilitaRuoliFromCodiceSoggetto('001198');
                //$arrayVisibilita = proSoggetto::getVisibilitaRuoliFromCodiceSoggetto('000004');
                $time2 = microtime(true);
                Out::msgInfo("Array Visibilita: $time1 - $time2 Elementi: " . count($arrayVisibilita), print_r($arrayVisibilita, true));
                break;
        }
    }

    public function close() {
        Out::closeDialog('proTest');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

}
