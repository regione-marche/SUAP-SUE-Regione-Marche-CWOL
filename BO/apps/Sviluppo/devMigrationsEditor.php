<?php

/**
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Ambiente
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 * */
function devMigrationsEditor() {
    $devMigrationsEditor = new devMigrationsEditor();
    $devMigrationsEditor->parseEvent();
    return;
}

class devMigrationsEditor extends itaModel {

    public $nameForm = 'devMigrationsEditor';

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
                Out::show($this->nameForm . '_divRicerca');
                Out::hide($this->nameForm . '_divGestione');
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
