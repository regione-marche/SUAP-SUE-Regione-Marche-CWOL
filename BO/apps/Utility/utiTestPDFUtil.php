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
function utiTestPDFUtil() {
    $utiTestPDFUtil = new utiTestPDFUtil();
    $utiTestPDFUtil->parseEvent();
    return;
}

class utiTestPDFUtil extends itaModel {

    public $nameForm = "utiTestPDFUtil";
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
                include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
                $pdfUtils = new itaPDFUtils();
                $ret=$pdfUtils->marcaPDF("C:/Users/michele/Documents/myPDFTest/terzo.pdf", 'xxxxxxxxxx', '20', '500', 90);
                if($ret){
                    Out::msgInfo("Risultato", $pdfUtils->getRisultato());
                }else{
                    Out::msgStop("Errore", $pdfUtils->getMessaggioErrore());                    
                }
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
        App::$utente->removeKey($this->nameForm . '_path');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
