<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';

function praStart() {
    $praStart = new praStart();
    $praStart->parseEvent();
    return;
}

class praStart extends itaModel {

//    public $ANEL_DB;
    public $nameForm = "praStart";

    function __construct() {
        parent::__construct();
        // Apro il DB
//        try {
//            $this->ANEL_DB = ItaDB::DBOpen('ANEL');
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
                $_POST = array();
                $_POST['event'] = 'openform';
                $model = 'utiSetDate';
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
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

}

?>