<?php

/**
 *  Programma Inserimento campo aggiungivo Denom_fiera per poter agganciare il fasicolo alla fiera
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Andrea Bufarini
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    28.09.2017
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praInsertDenomFiera() {
    $praInsertDenomFiera = new praInsertDenomFiera();
    $praInsertDenomFiera->parseEvent();
    return;
}

class praInsertDenomFiera extends itaModel {

    public $praLib;
    public $PRAM_DB;

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
                $sql = "SELECT * FROM PROGES WHERE GESPRO = '000409' AND GESNUM NOT IN (SELECT DAGNUM FROM PRODAG WHERE DAGTIP = 'Denom_Fiera')";
                $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                $insert_Info = "Oggetto: Inserisco dato aggiuntivo Denom_Fiera";
                $eseguiti = 0;
                $totali = count($proges_tab);
                foreach ($proges_tab as $proges_rec) {
                    $prodag_rec = array();
                    $prodag_rec['DAGNUM'] = $proges_rec['GESNUM'];
                    $prodag_rec['DAGPAK'] = $proges_rec['GESNUM'];
                    $prodag_rec['DAGCOD'] = $proges_rec['GESPRO'];
                    $prodag_rec['DAGTIP'] = "Denom_Fiera";
                    $prodag_rec['DAGKEY'] = "DENOM_FIERA";
                    $prodag_rec['DAGDES'] = "Denominazione Fiera";
                    $prodag_rec['DAGSEQ'] = "10";
                    if (!$this->insertRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $insert_Info)) {
                        Out::msgStop("ATTENZIONE!", "Errore inserimento dato aggiuntivo Denom_Fiera pratica n. " . $proges_rec['GESNUM']);
                        break;
                    }
                    $eseguiti++;
                }
                out::msgInfo("Inserimento dato aggiuntivo", "Inserito dato aggiuntivo su $eseguiti di $totali");
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

}

?>
