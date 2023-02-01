<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
// CARICO LE LIBRERIE NECESSARIE

//include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proStart() {
    $proStart = new proStart();
    $proStart->parseEvent();
    return;
}

class proStart extends itaModel {

//    public $PROT_DB;
    public $nameForm = "proStart";

    function __construct() {
        parent::__construct();
        // Apro il DB
//        try {
//            $this->PROT_DB = ItaDB::DBOpen('PROT');
//        } catch (Exception $e) {
//            Out::msgStop("Errore", $e->getMessage());
//        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'run':
                $_POST=array();
                $_POST['event']='openform';
                $model='utiSetDate';
                itaLib::openForm($model);
                Out::show($model);
                $phpURL = App::getConf('modelBackEnd.php');
                $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                $model();
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

}

?>