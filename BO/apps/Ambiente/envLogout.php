<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
function envLogout() {
    $envLogout = new envLogout();
    $envLogout->parseEvent();
    return;
}

class envLogout extends itaModel {

    public $nameForm = "envLogout";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::html($this->nameForm . '_divMessaggio', '
                    <center>
                        <span style="font-style:italic;">Accesso in corso con nome utente:</span>
                        <br>
                        <span style="font-weight:bold;">' . App::$utente->getKey('nomeUtente') . '</span>
                    </center>');
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Termina':
                        App::close();

                        if (App::$clientEngine == 'itaMobile') {
                            Out::codice('mobileCambia();');
                        } else {
                            Out::codice('location.reload();');
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
