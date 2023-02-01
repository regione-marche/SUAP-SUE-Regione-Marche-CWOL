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
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function utiTest1AcqrMen() {
    $utiTest1AcqrMen = new utiTest1AcqrMen();
    $utiTest1AcqrMen->parseEvent();
    return;
}

class utiTest1AcqrMen extends itaModel {

    public $nameForm = "utiTest1AcqrMen";
    private $path;

    function __construct() {
        parent::__construct();
        $this->path = App::$utente->getKey($this->nameForm . '_path');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_path', $this->path);
    }

    public function parseEvent() {

        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':


                $model = 'utiAcqrMen';
                /* @var $formObj utiAcqrMen */
                $formObj = itaModel::getInstance($model);
                $formObj->setReturnEvent('returnIndicatori');
                $formObj->setReturnId('');
                $formObj->setPath($this->choosePathProtocollo());

                if (!$formObj->getPath()) {
                    $formObj->setPath($this->choosePathGenerale());
                }

                $formObj->setOpenModeWait();
          
                itaLib::openForm($model);
                $formObj->parseEvent();

                $formObj->setReturnModel($this->nameForm);
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
            case 'returnIndicatori' :
                Out::MsgInfo("", print_r($_POST,true));
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_path');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function choosePathProtocollo() {
        $proLib = new proLib();
        $Anaent_rec = $proLib->GetAnaent(57);
        return $Anaent_rec['ENTVAL'];
    }

    public function setHead($formObj) {

        Out::html($formObj->nameForm . '_divHead', "Path di importazione : " . $formObj->getPath());
        Out::html($formObj->nameForm . '_divMsg', '');
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getPath() {

        return $this->path;
    }

}
