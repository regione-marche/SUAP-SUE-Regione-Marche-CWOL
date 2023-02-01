<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praAnaImpo() {
    $praAnaImpo = new praAnaImpo();
    $praAnaImpo->parseEvent();
    return;
}

class praAnaImpo extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praAnaImpo";
    public $gridAnatipimpo = "praAnaImpo_gridAnatipimpo";
    public $Classificazioni = array(
        '1' => 'Diritti',
        '2' => 'Oneri',
        '3' => 'Imposte bollo',
        '4' => 'Sanzioni',
        '99' => 'Altro'
    );

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
                    case $this->gridAnatipimpo:
                        $this->OpenGestione($rowid);
                        break;
                }
                break;

            case 'addGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridAnatipimpo:
                        $this->OpenGestione();
                        break;
                }
                break;

            case 'delGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridAnatipimpo:
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
                        $this->gridAnatipimpo, array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->sql())
                );
                $ita_grid01->setSortIndex('CODTIPOIMPO');
                $ita_grid01->exportXLS('', 'Anatipimpo.xls');
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->sql(), "Ente" => $ParametriEnte_rec['ENTE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAnaImpo', $parameters);
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];

                switch ($id) {
                    case $this->gridAnatipimpo:
                        TableView::clearGrid($id);

                        $gridScheda = new TableView($id, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $this->sql()
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);

                        $data = $this->elabora($gridScheda->getDataArray());

                        if (!$gridScheda->getDataPageFromArray('json', $data)) {
                            Out::msgInfo("Ricerca", "Nessun record trovato");
                            $this->openRicerca();
                        }
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        if (($anatipimpo_rec = $this->praLib->GetAnatipimpo($_POST[$this->nameForm . '_Codice']))) {
                            $this->OpenGestione($anatipimpo_rec['ROWID']);
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
                        $anatipimpo_rec = $_POST[$this->nameForm . '_ANATIPIMPO'];
                        $anatipimpo_rec['CODTIPOIMPO'] = $this->getProgressivo();
                        $insert_info = 'Oggetto: ' . $anatipimpo_rec['DESCTIPOIMPO'];

                        if (!$this->insertRecord($this->PRAM_DB, 'ANATIPIMPO', $anatipimpo_rec, $insert_info)) {
                            break;
                        }

                        Out::msgBlock($this->nameForm . '_workSapce', 600, false, "Record aggiunto");

                        $rowid = $this->getLastInsertId();
                        $this->openGestione($rowid);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anatipimpo_rec = $_POST[$this->nameForm . '_ANATIPIMPO'];
                        $update_info = 'Oggetto: ' . $anatipimpo_rec['ROWID'] . " " . $anatipimpo_rec['DESCTIPOIMPO'];

                        if (!$this->updateRecord($this->PRAM_DB, 'ANATIPIMPO', $anatipimpo_rec, $update_info)) {
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
                        $anatipimpo_rec = $_POST[$this->nameForm . '_ANATIPIMPO'];
                        $delete_info = 'Oggetto: ' . $anatipimpo_rec['ROWID'] . " " . $anatipimpo_rec['DESCTIPOIMPO'];

                        if (!$this->controllaCancella($_POST[$this->nameForm . '_CODTIPOIMPO'])) {
                            break;
                        }

                        if (!$this->deleteRecord($this->PRAM_DB, 'ANATIPIMPO', $anatipimpo_rec['ROWID'], $delete_info)) {
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
                    ANATIPIMPO
                WHERE
                    1 = 1";

        $Codice = $_POST[$this->nameForm . '_Codice'];
        $Descrizione = $_POST[$this->nameForm . '_Descrizione'];
        $Classificazione = $_POST[$this->nameForm . '_Classificazione'];

        if ($Codice) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('CODTIPOIMPO') . " LIKE '%" . addslashes(strtoupper($Codice)) . "%'";
        }

        if ($Descrizione) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('DESCTIPOIMPO') . " LIKE '%" . addslashes(strtoupper($Descrizione)) . "%'";
        }

        if ($Classificazione) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('CLASTIPOIMPO') . " LIKE '%" . addslashes(strtoupper($Classificazione)) . "%'";
        }

        return $sql;
    }

    public function elabora($table) {
        foreach ($table as &$record) {
            $record['CLASTIPOIMPO'] = $this->Classificazioni[$record['CLASTIPOIMPO']];
        }

        return $table;
    }

    public function creaSelect() {
        foreach ($this->Classificazioni as $k => $v) {
            Out::select($this->nameForm . '_Classificazione', 1, $k, 0, $v);
            Out::select($this->nameForm . '_ANATIPIMPO[CLASTIPOIMPO]', 1, $k, 0, $v);
        }
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

        TableView::disableEvents($this->gridAnatipimpo);
        TableView::clearGrid($this->gridAnatipimpo);

        Out::setFocus($this->nameForm, $this->nameForm . '_Codice');
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('Nuovo', 'AltraRicerca'));

        TableView::enableEvents($this->gridAnatipimpo);
        TableView::reload($this->gridAnatipimpo);
    }

    public function openGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');

        Out::setFocus($this->nameForm, $this->nameForm . '_ANATIPIMPO[DESCTIPOIMPO]');

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

            $anatipimpo_rec = $this->praLib->GetAnatipimpo($rowid, 'rowid');
            Out::valore($this->nameForm . '_CODTIPOIMPO', $anatipimpo_rec['CODTIPOIMPO']);
            Out::valori($anatipimpo_rec, $this->nameForm . '_ANATIPIMPO');
        }
    }

    public function getProgressivo() {
        $sql = "SELECT MAX(CODTIPOIMPO) AS PROG FROM ANATIPIMPO LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec['PROG'] ? intval($rec['PROG']) + 1 : 1;
    }

    public function controllaCancella($code) {
        $relations = array(
            "PROIMPO" => "IMPOCOD"
        );

        foreach ($relations as $table => $field) {
            $rel_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM $table WHERE $field = '$code'", false);
            if ($rel_rec) {
                Out::msgStop("Errore", "Record presente nella tabella $table. Impossibile cancellare");
                return false;
            }
        }

        return true;
    }

}
