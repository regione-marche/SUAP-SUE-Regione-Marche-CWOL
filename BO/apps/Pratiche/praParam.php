<?php

/**
 *
 * PARAMETRI ENTE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Marco Camilleti <marco.camilletti@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praParam() {

    $praParam = new praParam();
    $praParam->parseEvent();
    return;
}

class praParam extends itaModel {

    public $nameForm = "praParam";
    public $utiEnte;
    public $praLib;
    public $ITALWEB_DB;

    function __construct() {
        parent::__construct();
        $this->utiEnte = new utiEnte();
        $this->praLib = new praLib();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
                if ($PARMENTE_rec == false) {
                    $PARMENTE_rec = Array();
                    $PARMENTE_rec['CODICE'] = App::$utente->getKey('ditta');
                    $insert_Info = 'Inizializzazione Parametri';
                    if (!$this->insertRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $PARMENTE_rec, $insert_Info)) {
                        $this->returnToParent();
                        break;
                    }
                    Out::msgInfo("Gestione Parametri", "Parametri Nuovo Ente" . App::$utente->getKey('ditta') . " Inizializzati.");
                }
                Out::valori($PARMENTE_rec, $this->nameForm . "_PARAMETRIENTE");
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
//                        if($_POST[$this->nameForm.'_PARAMETRIENTE']['TIPOPROTOCOLLO'] == 'Paleo') {
//                            Out::msgStop("Errore", "Parametri Mancanti");
//                            break;
//                        }
                        $PARAMETRIENTE_rec = $_POST[$this->nameForm . '_PARAMETRIENTE'];
                        $update_Info = 'Oggetto: ' . $PARAMETRIENTE_rec['CODICE'] . ' ' . $PARAMETRIENTE_rec['DENOMINAZIONE'];
                        if (!$this->updateRecord($this->ITALWEB_DB, 'PARAMETRIENTE', $PARAMETRIENTE_rec, $update_Info)) {
                            $this->returnToParent();
                            break;
                        }
                        Out::msgInfo("Gestione Parametri", "Parametri Ente " . App::$utente->getKey('ditta') . " Aggiornati.");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_Esci':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function CreaCombo() {
        $this->praLib->creaComboTipiProt($this->nameForm . '_PARAMETRIENTE[TIPOPROTOCOLLO]');
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

?>
