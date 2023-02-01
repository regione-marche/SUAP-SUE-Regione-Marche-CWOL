<?php

/**
 *
 * Sistema di controllo per SmartAgent
 *
 *  * PHP Version 5
 *
 * @category   
 * @package    /apps/Ambiente
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2015 Italsoft
 * @license 
 * @version    01.12.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
function envCheckAgent() {
    $envCheckAgent = new envCheckAgent();
    $envCheckAgent->parseEvent();
    return;
}

class envCheckAgent extends itaModel {

    public $nameForm = "envCheckAgent";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'returnSmartAgent':
                $status = $_POST['statusText'];

                if ($status !== 'success') {
                    Out::msgStop("Attenzione", "SmartAgent non è attivo.\n\nAttivare SmartAgent e riprovare per proseguire.");
//                    $buttons = array();
//
//                    $buttons['Chiudi'] = array(
//                        'id' => $this->nameForm . '_Chiudi',
//                        'model' => $this->nameForm
//                    );
//
//                    if (ITA_IFRAME === 'active') {
//                        $buttons['Utilizza Applet'] = array(
//                            'id' => $this->nameForm . '_Applet',
//                            'model' => $this->nameForm
//                        );
//                    }
//
//                    Out::msgQuestion("Attenzione", "SmartAgent non è attivo.\n\nAttivare SmartAgent e riprovare per proseguire.", $buttons, 'auto', 'auto', 'true', false, true, false, "ItaCall");
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
