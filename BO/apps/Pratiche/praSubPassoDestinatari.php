<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';

function praSubPassoDestinatari() {
    $praSubPassoDestinatari = new praSubPassoDestinatari();
    $praSubPassoDestinatari->parseEvent();
    return;
}

class praSubPassoDestinatari extends praSubPasso {

    public $nameForm = 'praSubPassoDestinatari';
    public $destinatari = array();

    function __construct() {
        parent::__construct();
    }

    public function postInstance() {
        parent::postInstance();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
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
    }

    public function returnToParent($propak, $close = true) {
        parent::returnToParent($close);
    }

    public function getDestinatari(){
        return $this->destinatari;
    }
}
