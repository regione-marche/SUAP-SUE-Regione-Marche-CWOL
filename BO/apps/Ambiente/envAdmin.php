<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function envAdmin() {
    $envDomains = new envAdmin();
    $envDomains->parseEvent();
    return;
}

class envAdmin extends itaModel {

    public $nameForm = "envAdmin";

    function __construct() {
        parent::__construct();
        $this->private = false;
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::html($this->nameForm . '_divMessaggio', '
                    <center>
                        <span style="font-size:1.5em;font-style:italic;">Pannello di Amministrazione ambiente ItaEngine</span>
                        <br>
                        <span style="font-weight:bold;">' . ITA_BASE_PATH . '</span>
                    </center>');

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_envDomains_btn' :
                        $model = 'envDomains';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    
                    case $this->nameForm . '_envUpdater_btn':
                        $model = 'envUpdater';

                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    
                    case $this->nameForm . '_envPassword_btn':
                        $model = 'accEncryptPasswords';

                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
