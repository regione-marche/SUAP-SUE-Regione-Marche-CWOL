<?php

/**
 *
 * LANCIATORE GESTIONE FASCICOLI ELETTRONICI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft sRL
 * @license
 * @version    04.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praLanciaGest() {
    $praLanciaGest = new praLanciaGest();
    $praLanciaGest->parseEvent();
    return;
}

class praLanciaGest extends itaModel {

    public $PRAM_DB;
    public $praLib;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib($this->ditta);
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $filent_rec = $this->praLib->GetFilent(47);
                $model = $filent_rec['FILVAL'];
                if ($model == "") {
                    $model = "praGest";
                }
                itaLib::openForm($model);
                $objModel = itaModel::getInstance($model);
                $objModel->setEvent("openform");
                $objModel->parseEvent();
                break;
        }
    }

    public function close() {
        $this->close = true;
        
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
