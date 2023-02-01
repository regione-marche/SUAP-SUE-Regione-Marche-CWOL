<?php

/**
 *
 * DOCUMENTI BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    Documenti
 * @author     Moscioni Michele
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.01.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function docVarsBrowser() {
    $docVarsBrowser = new docVarsBrowser();
    $docVarsBrowser->parseEvent();
    return;
}

class docVarsBrowser extends itaModel {

    public $nameForm = "docVarsBrowser";
    public $editorId;

    function __construct() {
        parent::__construct();
        $this->editorId = App::$utente->getKey($this->nameForm . '_editorId');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_editorId', $this->editorId);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->editorId=$_POST['editorId'];
                docRic::ricVariabili($_POST['dictionaryLegend'], $this->nameForm, 'returnVariabili', true);
                break;
            case 'returnVariabili':
                Out::codice('tinyInsertRawHTML("' . $this->editorId . '",\'' . $_POST['rowData']['markupkey'] . '\');');
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close)
            $this->close();
    }

}

?>
