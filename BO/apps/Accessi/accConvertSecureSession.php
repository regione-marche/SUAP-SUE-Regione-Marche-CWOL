<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_LIB_PATH . '/itaPHPCore/itaCrypt.class.php';

function accConvertSecureSession() {
    $accConvertSecureSession = new accConvertSecureSession();
    $accConvertSecureSession->parseEvent();
    return;
}

class accConvertSecureSession extends itaModel {

    public $nameForm = 'accConvertSecureSession';
    private $ITALWEBDB;

    function __construct() {
        parent::__construct();
        $this->ITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $accessi_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, 'SELECT * FROM ACCESSI');
                foreach ($accessi_tab as $accessi_rec) {
                    if ($accessi_rec['PWDSESSION']) {
                        $accessi_rec['SECSESSION'] = itaCrypt::encrypt($accessi_rec['PWDSESSION']);
                        $accessi_rec['PWDSESSION'] = '';
                        $this->updateRecord($this->ITALWEBDB, 'ACCESSI', $accessi_rec, '');
                    }
                }
                break;
        }
    }

}
