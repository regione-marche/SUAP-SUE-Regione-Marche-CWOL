<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    15.01.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class envLibProtocolla {

    public $ITALWEB_DB;
    private $devLib;
    private $accLib;
    private $errMessage;
    private $errCode;

    function __construct() {
        $this->devLib = new devLib();
        $this->accLib = new accLib();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEB_DB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function getParametriProtocolloRemoto() {
        $msgErr = "";
        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        $meta = unserialize($enteProtRec_rec['METAVALUE']);
        if ($meta['TIPO'] && $meta['DITTA'] && $meta['URLREMOTO']) {
            $msgErr = "Vedere le configiurazioni dell'utente";
            if ($meta['TIPO']) {
                $url = $meta['URLREMOTO'];
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage("Tipo Protocollo non definito. $msgErr");
                return false;
            }
        } else {
            $env_config_rec = $this->devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
            $url = $env_config_rec['CONFIG'];
        }
        if (!$url) {
            $this->setErrCode(-1);
            $this->setErrMessage("Url remoto non definito. $msgErr");
            return false;
        }
//        if (!$daUtente) {
//            $env_config_rec = $this->devLib->getEnv_config('ITALSOFTPROTREMOTO', 'codice', 'URLREMOTO', false);
//            $url = $env_config_rec['CONFIG'];
//        } else {
//            $msgErr = "Vedere le configiurazioni dell'utente";
//            $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
//            $meta = unserialize($enteProtRec_rec['METAVALUE']);
//            $url = $meta['URLREMOTO'];
//        }
//        if (!$url) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("Url remoto non definito. $msgErr");
//            return false;
//        }
        return $url;
    }

}

?>