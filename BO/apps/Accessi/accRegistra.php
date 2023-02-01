<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function accRegistra() {
    $accRegistra = new accRegistra();
    $accRegistra->parseEvent();
    return;
}

class accRegistra extends itaModel {

    public $ITW_DB;
    public $ditta;
    public $nameForm = "accRegistra";

    function __construct() {
        parent::__construct();
        try {
            $this->private = false;
            $this->ditta = App::$utente->getKey($this->nameForm . '_ditta');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ditta', $this->ditta);
        }
    }

    function setDitta($value) {
        $this->ditta = $value;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . "_Invia":
                        $richut_rec = $_POST[$this->nameForm . '_RICHUT'];
                        $richut_rec['RICSTA'] = accLib::RICHIESTA_NUOVA;
                        $richut_rec['RICDAT'] = date('Ymd');
                        $richut_rec['RICTIM'] = date('H:i:s');
                        $richut_rec['RICIIP'] = $_SERVER['REMOTE_ADDR'];
                        ItaDB::DBInsert(ItaDB::DBOpen('ITW', $this->ditta), 'RICHUT', 'ROWID', $richut_rec);

                        /* @var $accLib accLib */
                        $accLib = new accLib();
                        $accLib->inviaMailRichiestaUtente($richut_rec, $this->ditta);
                        Out::msgInfo("Conferma invio richiesta", "Richiesta inviata con successo", "auto", "auto", "");
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_ditta');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
