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

function praSubPassoDatiAggiuntivi() {
    $praSubPassoDatiAggiuntivi = new praSubPassoDatiAggiuntivi();
    $praSubPassoDatiAggiuntivi->parseEvent();
    return;
}

class praSubPassoDatiAggiuntivi extends praSubPasso {

    public $nameForm = 'praSubPassoDatiAggiuntivi';

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

    public function returnToParent($close = true) {
        parent::returnToParent($close);
    }

}
