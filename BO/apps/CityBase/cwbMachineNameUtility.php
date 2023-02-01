<?php
/**
 * Classi di utilita' richiamata dalla callback dello smartagent per settare il nome 
 * della macchinain sessione utente
 */
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';

function cwbMachineNameUtility() {
    $cwbMachineNameUtility = new cwbMachineNameUtility();
    $cwbMachineNameUtility->parseEvent();
    return;
}

class cwbMachineNameUtility extends itaFrontControllerCW {

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbMachineNameUtility';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onMachineNameCallback':
                if ($_POST["status"] == 200) {
                    cwbParGen::setSessionVar('machineName', $_POST["data"]);
                }
                break;
        }
    }

}
