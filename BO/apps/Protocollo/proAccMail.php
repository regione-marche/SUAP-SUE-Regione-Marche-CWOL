<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    26.10.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proAccMail() {
    $proAccMail = new proAccMail();
    $proAccMail->parseEvent();
    return;
}

class proAccMail extends itaModel {

    public $PROT_DB;
    public $nameForm = "proAccMail";
    public $proLib;
    public $proLibAllegati;
    public $mailAccount = array();
    public $IndiceMail = null;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->IndiceMail = App::$utente->getKey($this->nameForm . '_IndiceMail');
        $this->mailAccount = App::$utente->getKey($this->nameForm . '_mailAccount');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_IndiceMail', $this->IndiceMail);
            App::$utente->setKey($this->nameForm . '_mailAccount', $this->mailAccount);
        }
    }

    public function getIndiceMail() {
        return $this->IndiceMail;
    }

    public function setIndiceMail($IndiceMail) {
        $this->IndiceMail = $IndiceMail;
    }

    public function setMailAccount($mailAccount) {
        $this->mailAccount = $mailAccount;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $this->openDettaglio();
                break;
            case 'editGridRow':
            case 'dbClickRow':
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {

        parent::close();
        App::$utente->removeKey($this->nameForm . '_IndiceMail');
        App::$utente->removeKey($this->nameForm . '_mailAccount');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $returnModelObj = itaModel::getInstance($this->returnModel);
        if ($returnModelObj) {
            $Account = $_POST[$this->nameForm . '_ACCOUNT'];
            $_POST = array();
            $_POST['ACCOUNT'] = $Account;
            $_POST['INDICEMAIL'] = $this->IndiceMail;
            $returnModelObj->setEvent($this->returnEvent);
            $returnModelObj->parseEvent();
        }

        if ($close)
            $this->close();

        Out::show($this->returnModel);
    }

    public function openDettaglio() {
        if (is_null($this->IndiceMail)) {
            Out::msgInfo("Attenzione", "Nessun Account mail selezionato.");
            Out::hide($this->nameForm . '_Conferma');
            return false;
        }
        $anaent_28 = $this->proLib->GetAnaent('28');
        if (!$this->mailAccount) {
            $Account = unserialize($anaent_28['ENTVAL']);
            $AccountMail = $Account[$this->IndiceMail];
        } else {
            $AccountMail = $this->mailAccount[$this->IndiceMail];
        }
        $AccountMail['ID'] = $this->IndiceMail;
        Out::valori($AccountMail, $this->nameForm . '_ACCOUNT');
    }

}

?>
