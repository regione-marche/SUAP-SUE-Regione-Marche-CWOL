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
 * */
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';

function envMenuProgrammatore() {
    $envMenuProgrammatore = new envMenuProgrammatore();
    $envMenuProgrammatore->parseEvent();
    return;
}

class envMenuProgrammatore extends itaModel {

    public $nameForm = "envMenuProgrammatore";
    public $menLib;

    function __construct() {
        parent::__construct();
        $this->menLib = new menLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;
            
            case 'onBlur':
                break;
            
            case 'onClick':
                switch ($_POST['id']) {
                    default:
                        if ( substr( $_POST['id'], -3 ) === 'btn' ) {
                            Out::closeDialog($this->nameForm);
                            $model = substr( $_POST['id'], strlen($this->nameForm) + 1, -4 );

                            itaLib::openForm($model);
                            /* @var $formObj itaModel */
                            $formObj = itaModel::getInstance($model);
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        }
                        break;
                    
                    case 'close-portlet':
                        $this->returnToParent();
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
