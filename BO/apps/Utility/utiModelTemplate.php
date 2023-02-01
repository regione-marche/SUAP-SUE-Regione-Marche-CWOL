<?php
/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 **/

function utiModelTemplate() {
    $utiModelTemplate = new utiModelTemplate();
    $utiModelTemplate->parseEvent();
    return;
}

class utiModelTemplate extends itaModel {

    public $nameForm = "utiModelTemplate";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        // OVERRIDE DEGLI EVENTI
        switch ($_POST['event']) {
            case 'openform':
                return;
            case 'onBlur':
                return;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        return;
                }

                return;
        }
        
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;
            case 'onBlur':
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

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }
    
    

}

?>
