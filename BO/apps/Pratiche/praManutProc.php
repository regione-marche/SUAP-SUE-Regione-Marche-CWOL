<?php

/**
 *  Utilità per manutenzione procedimenti
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Antimo Panetta
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    02.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praManutProc() {
    $praManutProc = new praManutProc();
    $praManutProc->parseEvent();
    return;
}

class praManutProc extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praManutProc";
    public $divRic = "praManutProc_divRicerca";

    function __construct() {
        parent::__construct();
        try {
            //
            // carico le librerie
            //
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AggiungiPasso':
                        $model = "praManutAggiungiPasso";
                        itaLib::openForm($model);
                        $obj = itaModel::getInstance($model);
                        $obj->setReturnModel($this->nameForm);
                        $obj->setEvent('openform');
                        $obj->parseEvent();
                        break;
                    case $this->nameForm . '_CambiaPDF':
                        $model = "praManutCambiaPDF";
                        itaLib::openForm($model);
                        $obj = itaModel::getInstance($model);
                        $obj->setReturnModel($this->nameForm);
                        $obj->setEvent('openform');
                        $obj->parseEvent();
                        break;
                    case $this->nameForm . '_ModificaControlli':
                        $model = "praManutModificaControlli";
                        itaLib::openForm($model);
                        $obj = itaModel::getInstance($model);
                        $obj->setReturnModel($this->nameForm);
                        $obj->setEvent('openform');
                        $obj->parseEvent();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function OpenRicerca() {
        $this->Nascondi();
        Out::show($this->nameForm);
    }

    public function Nascondi() {
    }

}

?>
