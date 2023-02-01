<?php

/**
 *
 * 
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.12.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Pratiche/praLib.class.php';

function praDiagrammaViewer() {
    $praAttivita = new praDiagrammaViewer();
    $praAttivita->parseEvent();
    return;
}

class praDiagrammaViewer extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $datiWf;
    public $nameForm = "praDiagrammaViewer";
    public $divRis = "praDiagrammaViewer_divRisultato";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    
    public function getDatiWf() {
        return $this->datiWf;
    }

    public function setDatiWf($datiWf) {
        $this->datiWf = $datiWf;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': 
                $praCompDiagramma = itaFormHelper::innerForm('praCompDiagramma', $this->nameForm . '_divContainer');
                $praCompDiagramma->setEvent('openform');
                $praCompDiagramma->setReturnModel($this->nameForm);
                $praCompDiagramma->setReturnEvent('returnFromDiagramma');
                $praCompDiagramma->setDatiWorkflow($this->datiWf);
                $praCompDiagramma->parseEvent();
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

}
