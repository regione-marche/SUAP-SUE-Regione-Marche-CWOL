<?php

function accValidateController() {
    $accValidateController = new accValidateController();
    $accValidateController->parseEvent();
    return;
}

class accValidateController extends itaModel {

    public $private = false;
    public $nameForm = 'accValidateController';

    public function __construct() {
        parent::__construct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'containerLoad':
                break;
        }
    }

}
