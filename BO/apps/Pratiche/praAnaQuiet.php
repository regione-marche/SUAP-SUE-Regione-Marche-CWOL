<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praAnaQuiet() {
    $praAnaQuiet = new praAnaQuiet();
    $praAnaQuiet->parseEvent();
    return;
}

class praAnaQuiet extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praAnaQuiet";
    public $gridAnaquiet = "praAnaQuiet_gridAnaquiet";
    public $Codifica = array(
        'IBAN' => 'IBAN',
        'CCP' => 'CCP',
        'BOLLETTARIO' => 'BOLLETTARIO',
        'PAGOPA' => 'PAGOPA (IUV)',
        'ALTRO' => 'ALTRO'
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
                    case $this->gridAnaquiet:
                        $this->OpenGestione($rowid);
                        break;
                }
                break;

            case 'addGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridAnaquiet:
                        $this->OpenGestione();
                        break;
                }
                break;

            case 'delGridRow':
                $id = $_POST['id'];
                $rowid = $_POST['rowid'];

                switch ($id) {
                    case $this->gridAnaquiet:
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
                        $this->gridAnaquiet, array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->sql())
                );
                $ita_grid01->setSortIndex('CODQUIET');
                $ita_grid01->exportXLS('', 'Anaquiet.xls');
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->sql(), "Ente" => $ParametriEnte_rec['ENTE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAnaQuiet', $parameters);
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];

                switch ($id) {
                    case $this->gridAnaquiet:
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
                        if (($anaquiet_rec = $this->praLib->GetAnaquiet($_POST[$this->nameForm . '_Codice']))) {
                            $this->OpenGestione($anaquiet_rec['ROWID']);
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
                        $anaquiet_rec = $_POST[$this->nameForm . '_ANAQUIET'];
                        $anaquiet_rec['CODQUIET'] = $this->getProgressivo();
                        $insert_info = 'Oggetto: ' . $anatipimpo_rec['QUIETANZATIPO'];

                        if (!$this->insertRecord($this->PRAM_DB, 'ANAQUIET', $anaquiet_rec, $insert_info)) {
                            break;
                        }

                        Out::msgBlock($this->nameForm . '_workSapce', 600, false, "Record aggiunto");

                        $rowid = $this->getLastInsertId();
                        $this->openGestione($rowid);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anaquiet_rec = $_POST[$this->nameForm . '_ANAQUIET'];
                        $update_info = 'Oggetto: ' . $anaquiet_rec['ROWID'] . " " . $anaquiet_rec['QUIETANZATIPO'];

                        if (!$this->updateRecord($this->PRAM_DB, 'ANAQUIET', $anaquiet_rec, $update_info)) {
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
                        $anaquiet_rec = $_POST[$this->nameForm . '_ANAQUIET'];
                        $delete_info = 'Oggetto: ' . $anaquiet_rec['ROWID'] . " " . $anaquiet_rec['QUIETANZATIPO'];

                        if (!$this->controllaCancella($_POST[$this->nameForm . '_CODQUIET'])) {
                            break;
                        }

                        if (!$this->deleteRecord($this->PRAM_DB, 'ANAQUIET', $anaquiet_rec['ROWID'], $delete_info)) {
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
                    ANAQUIET
                WHERE
                    1 = 1";

        $Codice = $_POST[$this->nameForm . '_Codice'];
        $Descrizione = $_POST[$this->nameForm . '_QuietanzaTipo'];
        $Codifica = $_POST[$this->nameForm . '_IdentificazioneTipo'];
        $Identificazione = $_POST[$this->nameForm . '_Identificazione'];

        if ($Codice) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('CODQUIET') . " LIKE '%" . addslashes(strtoupper($Codice)) . "%'";
        }

        if ($Descrizione) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('QUIETANZATIPO') . " LIKE '%" . addslashes(strtoupper($Descrizione)) . "%'";
        }

        if ($Codifica) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('IDENTIFICAZIONETIPO') . " LIKE '%" . addslashes(strtoupper($Codifica)) . "%'";
        }

        if ($Identificazione) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('IDENTIFICAZIONE') . " LIKE '%" . addslashes(strtoupper($Identificazione)) . "%'";
        }

        return $sql;
    }

    public function elabora($table) {
        foreach ($table as &$record) {
            $record['IDENTIFICAZIONETIPO'] = $this->Codifica[$record['IDENTIFICAZIONETIPO']];
        }

        return $table;
    }

    public function creaSelect() {
        foreach ($this->Codifica as $k => $v) {
            Out::select($this->nameForm . '_IdentificazioneTipo', 1, $k, 0, $v);
            Out::select($this->nameForm . '_ANAQUIET[IDENTIFICAZIONETIPO]', 1, $k, 0, $v);
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

        TableView::disableEvents($this->gridAnaquiet);
        TableView::clearGrid($this->gridAnaquiet);

        Out::setFocus($this->nameForm, $this->nameForm . '_Codice');
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('Nuovo', 'AltraRicerca'));

        TableView::enableEvents($this->gridAnaquiet);
        TableView::reload($this->gridAnaquiet);
    }

    public function openGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');

        Out::setFocus($this->nameForm, $this->nameForm . '_ANAQUIET[QUIETANZATIPO]');

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

            $anaquiet_rec = $this->praLib->GetAnaquiet($rowid, 'rowid');
            Out::valore($this->nameForm . '_CODQUIET', $anaquiet_rec['CODQUIET']);
            Out::valori($anaquiet_rec, $this->nameForm . '_ANAQUIET');
        }
    }

    public function getProgressivo() {
        $sql = "SELECT MAX(CODQUIET) AS PROG FROM ANAQUIET LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec['PROG'] ? intval($rec['PROG']) + 1 : 1;
    }

    public function controllaCancella($code) {
        $relations = array(
            "PROCONCILIAZIONE" => "QUIETANZA"
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
