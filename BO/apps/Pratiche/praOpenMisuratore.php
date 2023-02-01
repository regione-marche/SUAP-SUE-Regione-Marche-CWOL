<?php

/**
 *
 * MODEL MISURATORE PLANIMETRIE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    19.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praOpenMisuratore() {
    $praOpenMisuratore = new praOpenMisuratore();
    $praOpenMisuratore->parseEvent();
    return;
}

class praOpenMisuratore extends itaModel {

    public $praLib;
    public $nameForm = "praOpenMisuratore";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $fileTIF = $_POST['fileTIF'];
                //$fileTIF = "file1.tif";
                $Formato = $_POST["Formato"].$_POST["Orientamento"];
                $Scala = $_POST["Scala"];
                Out::show($this->nameForm);
                $Filent_rec_7_Url = $this->praLib->GetFilent(7);
                $arraySearch = array("$.FILE$", "$.FORMATO$", "$.SCALA$");
                $arraySost = array($fileTIF, $Formato, $Scala);
                $src = str_replace($arraySearch, $arraySost, $Filent_rec_7_Url['FILVAL']);
                $html = '<iframe id="utiIFrame_frame" style="overflow:auto;" class="ita-frame" frameborder="0" width="100%" height="95%" src="' . $src . '"></iframe>';
                Out::html($this->nameForm, $html);
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

}

?>