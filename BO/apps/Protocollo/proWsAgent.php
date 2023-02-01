<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft Srl
 * @license
 * @version    17.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php');
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');

class proWsAgent extends wsModel {

    private $PROT_DB;
    private $proLib;
    private $proLibTitolario;
    private $result;
    private $msgWarnig;
    private $errCode;
    private $errMessage;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibTitolario = new proLibTitolario();
            $this->PROT_DB = $this->proLib->getPROTDB();
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getMsgWarnig() {
        return $this->msgWarnig;
    }

    public function setMsgWarnig($msgWarnig) {
        $this->msgWarnig = $msgWarnig;
    }

    /**
     * 
     */
    protected function clearResult() {
        $messageResult = array();
        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = '';
        $this->result = array();
        $this->result['messageResult'] = $messageResult;
        $this->msgWarning = '';
    }

    protected function checkManutenzione() {
        if ($this->proLib->checkStatoManutenzione()) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Il protocollo è nello stato di manutenzione Azione non applicabile. Attendere la riabilitazione o contattare l\'assistenza.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        return false;
    }

}