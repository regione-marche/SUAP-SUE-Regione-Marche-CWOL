<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    23.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibElenco {

    public $PRAM_DB;
    public $praLib;
    private $errMessage;
    private $errCode;

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                $this->praLib = new praLib($ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                $this->praLib = new praLib();
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
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

    public function caricaDatiPraticaNew($model, $returnModel, $returnEvent, $daProtocollo = false) {
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent('openform');
        $_POST['daProtocollo'] = $daProtocollo;
        itaLib::openForm($model);
        $objModel->parseEvent();
        $objModel->setReturnModel($returnModel);
        $objModel->setReturnEvent($returnEvent);
    }

    public function openControllaRichiesteFO($returnModel, $perms) {
        $Filent_Rec = $this->praLib->GetFilent(42);
        if ($Filent_Rec['FILVAL'] == 1) {
            $model = 'praCtrRichiesteFO';
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent('openform');
            $_POST['perms'] = $perms;
            itaLib::openForm($model);
            $objModel->parseEvent();
            $objModel->setReturnModel($returnModel);
            $objModel->setReturnEvent('returnCtrRichiesteFO');
        } else {
            $model = 'praCtrRichieste';
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent('openform');
            $_POST['perms'] = $perms;
            itaLib::openForm($model);
            $objModel->parseEvent();
            $objModel->setReturnModel($returnModel);
            $objModel->setReturnEvent('returnCtrRichieste');
        }
    }

}
