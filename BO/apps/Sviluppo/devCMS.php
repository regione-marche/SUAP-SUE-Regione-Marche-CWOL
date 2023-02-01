<?php

/**
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Sviluppo
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 * */
function devCMS() {
    $devCMS = new devCMS();
    $devCMS->parseEvent();
    return;
}

class devCMS extends itaModel {

    public $nameForm = 'devCMS';

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
                Out::openDocument('./cms/admin');
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
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

}
