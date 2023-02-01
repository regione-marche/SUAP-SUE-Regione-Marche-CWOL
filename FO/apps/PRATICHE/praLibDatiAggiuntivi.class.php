<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';

class praLibDatiAggiuntivi {

    private $praLib;
    private $PRAM_DB;
    private $errCode;
    private $errMessage;

    public function __construct() {
        $this->praLib = new praLib();
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
    }

    function getPRAM_DB() {
        return $this->PRAM_DB;
    }

    function setPRAM_DB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
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

    public function controllaValidoSe($controllo, $valore, $dagrev = '') {
        switch ($controllo) {
            case 'CodiceFiscale':
//                $regExp = '/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-7LMNPQRST]{1}[0-9LMNPQRSTUV]{1}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/i';
                $regExp = '/^[A-Z]{6}[0-9]{2}[ABCDEHLMPRST]{1}[0-7]{1}[0-9]{1}[A-Z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1}$/i';
                break;

            case 'PartitaIva':
                $regExp = '/^[0-9]{11}$/';
                break;

            case 'email':
                $regExp = "/^[a-z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i";
                break;

            case 'RegularExpression':
                $regExp = $dagrev;
                break;

            case 'Numeri':
                $regExp = '/^[0-9]+$/';
                break;

            case 'Lettere':
                $regExp = '/^[A-Za-z\à\è\ì\ò\ù]+$/';
                break;

            case 'Data':
                $format = 'd/m/Y';
                $d = DateTime::createFromFormat($format, $valore);

                if (!($d && $d->format($format) === $valore)) {
                    return false;
                }

                return true;

            case 'Importo':
                $regExp = '/^(0|[1-9]\d*)([\.|,]\d{1,2})?$/';
                break;

            case 'Iban':
                $regExp = '^IT\d{2}[ ][a-zA-Z]\d{3}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{3}$|^IT\d{2}[a-zA-Z]\d{22}$';
                break;

            case 'CodiceFiscalePiva':
                $regExp = '/^([a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]|[0-9]{11})$/';
                break;

            default:
                break;
        }

        return (boolean) preg_match($regExp, $valore);
    }

}
