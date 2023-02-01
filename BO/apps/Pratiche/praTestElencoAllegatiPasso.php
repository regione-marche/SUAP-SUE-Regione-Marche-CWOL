<?php

/**
 *
 * TEST CARICAMENTO TAB ELENCO ALLEGTI PASSI SIMPLE
 *
 * PHP Version 5
 *
 * @category
 * @package    Partiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    05.03.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function praTestElencoAllegatiPasso() {
    $praTestElencoAllegatiPasso = new praTestElencoAllegatiPasso();
    $praTestElencoAllegatiPasso->parseEvent();
    return;
}

class praTestElencoAllegatiPasso extends itaModel {

    public $nameForm = "praTestElencoAllegatiPasso";

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
                $model = 'praSubPassoAllegatiSimple';
                $proSubAllegatiSimple = itaFormHelper::innerForm($model, $this->nameForm . '_paneAllegati');
                $proSubAllegatiSimple->setEvent('openform');
                $proSubAllegatiSimple->parseEvent();
                $this->praSubPassoAllegatiSimple = $proSubAllegatiSimple->nameForm;
                //
                $praSubPassoAllegatiSimple = itaModel::getInstance('praSubPassoAllegatiSimple', $this->praSubPassoAllegatiSimple);
                $praSubPassoAllegatiSimple->setKeyPasso('2019000037155180453030');
                $praSubPassoAllegatiSimple->CaricaAllegati();
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
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
