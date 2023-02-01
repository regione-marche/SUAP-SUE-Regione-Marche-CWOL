<?php

/**
 *
 * UTILITA 
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    25.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praDevUtils() {
    $praDevUtils = new praDevUtils();
    $praDevUtils->parseEvent();
    return;
}

class praDevUtils extends itaModel {

    public $nameForm = "praDevUtils";
    public $praLib;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ditta_sorgente', $this->ditta_sorgente);
            App::$utente->setKey($this->nameForm . '_ditta_destino', $this->ditta_destino);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RinumeraProcFascicolo':
                        $model = 'praRinumeraProcFascicolo';
                        itaLib::openForm($model);
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
