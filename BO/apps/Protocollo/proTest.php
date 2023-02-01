<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proTest() {
    $proTest = new proTest();
    $proTest->parseEvent();
    return;
}

class proTest extends itaModel {

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
                Out::msgInfo("Apertura Dialog", "Aperta");
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog('proTest');
    }

    public function returnToParent($close=true) {
        if ($close) {
            $this->close();
        }
        Out::show('menuapp');
    }

}

?>
