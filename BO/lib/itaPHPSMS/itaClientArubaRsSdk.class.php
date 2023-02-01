<?php

/**
 *
 * Implementazione di 
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/
 * @author
 * @copyright  
 * @license
 * @version    30.03.2017
 * @link
 * @see
 * 
 */
require_once ITA_LIB_PATH . '/itaPHPSMS/itaSMSClient.php';
require_once ITA_LIB_PATH . '/RSSDK/sendsms.php';

class itaClientArubaRsSdk implements itaSMSClient {

    private $parameters;
    private $lastError;

    public function __construct($parameters) {
        $this->parameters = $parameters;
    }

    function getParameters() {
        return $this->parameters;
    }

    function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    public function sendSMS() {
        /*
         * @param array di parametri di chiamata
         *      - Tipo (string)
         *      - Mittente (string)
         *      - Destinatari (array di stringhe contenente i numeri dei destinatari)
         *      - Messaggio (string)
         *      - ID
         * @return $ret = array(
          'Esito',
          'ID'
         * 'Messaggio'
          );
         */
        if (!$this->parameters['Tipo']) {
//            $this->parameters['Tipo'] = SMSTYPE_STANDARD;
            $this->parameters['Tipo'] = SMSTYPE_ALTA;
        }

        try {
            $sms = new Sdk_SMS();
            $sms->sms_type = $this->parameters['Tipo'];
            foreach ($this->parameters['Destinatari'] as $numero_destinatario) {
                $sms->add_recipient($numero_destinatario);
//                $sms->add_recipient('+393479876543');
            }
            $sms->message = $this->parameters['Messaggio'];
            $sms->sender = $this->parameters['Mittente'];
            $sms->set_immediate(); // or sms->set_scheduled_delivery($unix_timestamp)
            if ($this->parameters['ID']) {
                $sms->order_id = $this->parameters['ID'];
            }
            if ($sms->validate()) {
                $res = $sms->send();
                if ($res['ok']) {
                    $ret['Esito'] = "OK";
                    $ret['ID'] = $res['order_id'];
                    $ret['Messaggio'] = $this->parameters['Messaggio'];
                } else {
                    $ret['Esito'] = 'Errore';
                    $this->setLastError($res['errmsg']);
                }
            } else {
                $ret['Esito'] = 'Errore';
                $this->setLastError($sms->problem());
            }
        } catch (Exception $exc) {
            $ret['Esito'] = 'Errore';
            $this->setLastError($exc->getMessage());
        }
        return $ret;
    }

    public function getSMSStatus($safeMode = false) {
        try {
            $this->resetLastError();
            $this->git->cloneRemoteRepository($safeMode);
            return true;
        } catch (Exception $exc) {
            $this->setLastError($exc);
            return false;
        }
    }

    public function validate() {
        try {
            $this->resetLastError();
            return $this->git->fetch(false);
        } catch (Exception $exc) {
            $this->setLastError($exc);
            return false;
        }
    }

    public function getCredit() {
        try {
            $this->resetLastError();
            $preResult = $this->git->fetch(false);
            $this->git->pull();
            return $preResult;
        } catch (Exception $exc) {
            $this->setLastError($exc);
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function setLastError($error) {
        $this->lastError = $error;
    }

    private function resetLastError(){
        $this->lastError = "";
    }
}
