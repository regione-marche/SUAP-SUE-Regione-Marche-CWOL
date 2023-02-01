<?php

/* * 
 *
 * ANAGRAFICA TIPOLOGIE PROGRESSIVI
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    04.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praTipiProg() {
    $praTipiProg = new praTipiProg();
    $praTipiProg->parseEvent();
    return;
}

class praTipiProg extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praTipiProg";
    public $gridTipiProg = "praTipiProg_gridTipiProg";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->utiEnte = new utiEnte();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
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
                $this->creaSelect();
                $this->openRicerca();
                return;

            case 'dbClickRow':
            case 'editGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridTipiProg:
                        $this->OpenGestione($rowid);
                        break;
                }
                break;

            case 'addGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridTipiProg:
                        $this->OpenGestione();
                        break;
                }
                break;

            case 'delGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridTipiProg:
                        $this->OpenGestione($rowid);

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                        ));
                        break;
                }
                break;

            case 'exportTableToExcel':
                $ita_grid01 = new TableView(
                        $this->gridTipiProg, array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->sql())
                );
                $ita_grid01->setSortIndex('CODDOCREG');
                $ita_grid01->exportXLS('', 'TipiProg.xls');
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->sql(), "Ente" => $ParametriEnte_rec['ENTE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAnadoctipreg', $parameters);
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];

                switch ($id) {
                    case $this->gridTipiProg:
                        TableView::clearGrid($id);

                        $gridScheda = new TableView($id, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $this->sql()
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);

                        if (!$gridScheda->getDataPage('json')) {
                            Out::msgInfo("Ricerca", "Nessun record trovato");
                            $this->openRicerca();
                        }
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        if (($anadoctipreg_rec = $this->praLib->GetAnadoctipreg($_POST[$this->nameForm . '_Codice']))) {
                            $this->OpenGestione($anadoctipreg_rec['ROWID']);
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->openGestione();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $anadoctipreg_rec = $_POST[$this->nameForm . '_ANADOCTIPREG'];
                        $anadoctipreg_rec['CODDOCREG'] = $this->getProgressivo();
                        $insert_info = 'Oggetto: Inserisco Tipologia N. ' . $anadoctipreg_rec['CODDOCREG'];

                        if (!$this->insertRecord($this->PRAM_DB, 'ANADOCTIPREG', $anadoctipreg_rec, $insert_info)) {
                            break;
                        }

                        Out::msgBlock($this->nameForm . '_workSpace', 600, false, "Record aggiunto");

                        $rowid = $this->getLastInsertId();
                        $this->openGestione($rowid);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anadoctipreg_rec = $_POST[$this->nameForm . '_ANADOCTIPREG'];
                        $update_info = 'Oggetto: Aggiorno tipologia ' . $anadoctipreg_rec['CODDOCREG'] . " - " . $anadoctipreg_rec['DESDOCREG'];

                        if (!$this->updateRecord($this->PRAM_DB, 'ANADOCTIPREG', $anadoctipreg_rec, $update_info)) {
                            break;
                        }

                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                        ));
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $anadoctipreg_rec = $_POST[$this->nameForm . '_ANADOCTIPREG'];
                        $delete_info = 'Oggetto: Cancello Tipologia rowid ' . $anadoctipreg_rec['ROWID'];

                        if (!$this->controllaCancella($anadoctipreg_rec['CODDOCREG'])) {
                            break;
                        }

                        if (!$this->deleteRecord($this->PRAM_DB, 'ANADOCTIPREG', $anadoctipreg_rec['ROWID'], $delete_info)) {
                            break;
                        }

                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_Elenca':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_TornaElenco':
                        $this->openRisultato();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        return;
                }

                return;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function sql() {
        $sql = "SELECT
                    *
                FROM
                    ANADOCTIPREG
                WHERE
                    1 = 1";

        $Codice = $_POST[$this->nameForm . '_Codice'];
        $Descrizione = $_POST[$this->nameForm . '_Desc'];
        $Attivo = $_POST[$this->nameForm . '_Attivo'];

        if ($Codice) {
            $sql .= " AND CODDOCREG = $Codice";
        }

        if ($Descrizione) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('DESDOCREG') . " LIKE '%" . addslashes(strtoupper($Descrizione)) . "%'";
        }

        if ($Attivo) {
            $sql .= " AND FL_ATTIVO = $Attivo";
        }


        return $sql;
    }

    public function creaSelect() {
        Out::select($this->nameForm . '_ANADOCTIPREG[TIPOPDOCPROG]', 1, "", 0, "");
        Out::select($this->nameForm . '_ANADOCTIPREG[TIPOPDOCPROG]', 1, "Annuale", 0, "Annuale");
        Out::select($this->nameForm . '_ANADOCTIPREG[TIPOPDOCPROG]', 1, "Assoluto", 0, "Assoluto");
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_TornaElenco');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openRicerca() {
        $this->mostraForm('divRicerca');
        Out::clearFields($this->nameForm);
        $this->mostraButtonBar(array('Nuovo', 'Elenca'));

        TableView::disableEvents($this->gridTipiProg);
        TableView::clearGrid($this->gridTipiProg);

        Out::setFocus($this->nameForm, $this->nameForm . '_Codice');
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('Nuovo', 'AltraRicerca'));

        TableView::enableEvents($this->gridTipiProg);
        TableView::reload($this->gridTipiProg);
    }

    public function openGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');

        Out::setFocus($this->nameForm, $this->nameForm . '_ANADOCTIPREG[DESDOCREG]');

        if (!$rowid) {
            /*
             * Nuovo
             */

            $this->mostraButtonBar(array('Aggiungi', 'AltraRicerca'));
        } else {
            /*
             * Dettaglio
             */

            $this->mostraButtonBar(array('Aggiorna', 'Cancella', 'AltraRicerca', 'TornaElenco'));

            $anadoctipreg_rec = $this->praLib->GetAnadoctipreg($rowid, 'rowid');
            Out::valore($this->nameForm . '_CODDOCREG', $anadoctipreg_rec['CODDOCREG']);
            Out::valori($anadoctipreg_rec, $this->nameForm . '_ANADOCTIPREG');
        }
    }

    public function getProgressivo() {
        $sql = "SELECT MAX(CODDOCREG) AS PROG FROM ANADOCTIPREG LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec['PROG'] ? intval($rec['PROG']) + 1 : 1;
    }

    public function controllaCancella($code) {
        $relations = array(
            "PROPAS" => "PRODOCTIPREG"
        );

        foreach ($relations as $table => $field) {
            $rel_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM $table WHERE $field = '$code'", false);
            if ($rel_rec) {
                Out::msgStop("Errore", "Record presente nella tabella $table ROWID " . $rel_rec['ROWID'] . ". Impossibile cancellare");
                return false;
            }
        }

        return true;
    }

}
