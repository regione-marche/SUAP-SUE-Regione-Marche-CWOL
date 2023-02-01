<?php

/**
 *
 * Archivio Cittadinanze
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    10.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Base/basLib.class.php';
include_once './apps/Utility/utiEnte.class.php';

function basTabCittadinanze() {
    $basTabCittadinanze = new basTabCittadinanze();
    $basTabCittadinanze->parseEvent();
    return;
}

class basTabCittadinanze extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basTabCittadinanze";
    public $divGes = "basTabCittadinanze_divGestione";
    public $divRis = "basTabCittadinanze_divRisultato";
    public $divRic = "basTabCittadinanze_divRicerca";
    public $gridCittadinanze = "basTabCittadinanze_gridCittadinanze";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->COMUNI_DB = $this->basLib->getCOMUNIDB();
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
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridCittadinanze:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridCittadinanze:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridCittadinanze,
                                array(
                                    'sqlDB' => $this->COMUNI_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('CITTADINANZA');
                $ita_grid01->exportXLS('', 'cittadinanze.xls');
                break;
            case 'printTableToHTML':
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters=array("Sql"=>$this->CreaSql().' ORDER BY CITTADINANZA',"Ente"=>$ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB,'wcoCittadinanze', $parameters);
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->COMUNI_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridCittadinanze,
                                        array(
                                            'sqlDB' => $this->COMUNI_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridCittadinanze]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('CITTADINANZA');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridCittadinanze);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_CITTADINANZA[CODICE]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $cittadinanaza_rec = $this->basLib->getCittadinanze($_POST[$this->nameForm . '_CITTADINANZA']['CODICE']);
                        if (!$cittadinanaza_rec) {
                            $cittadinanaza_rec = $_POST[$this->nameForm . '_CITTADINANZA'];
                            unset($cittadinanaza_rec['ROWID']);
                            $codice = $cittadinanaza_rec['CODICE'];
                            if (strlen($codice) < 6) {
                                $cittadinanaza_rec['CODICE'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $insert_Info = 'Oggetto: ' . $cittadinanaza_rec['CITTADINANZA'];
                            if ($this->insertRecord($this->COMUNI_DB, 'CITTADINANZA', $cittadinanaza_rec, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_CITTADINANZA[CODICE]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $cittadinanza_rec = $_POST[$this->nameForm . '_CITTADINANZA'];
                        $update_Info = 'Oggetto: ' . $cittadinanza_rec['CITTADINANZA'];
                        if ($this->updateRecord($this->COMUNI_DB, 'CITTADINANZA', $cittadinanza_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $cittadinanza_rec = $_POST[$this->nameForm . '_CITTADINANZA'];
                        $delete_Info = 'Oggetto: ' . $cittadinanza_rec['CITTADINANZA'];
                        if ($this->deleteRecord($this->COMUNI_DB, 'CITTADINANZA', $cittadinanza_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridCittadinanze);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_CITTADINANZE[ROWID]', '');
        TableView::disableEvents($this->gridCittadinanze);
        TableView::clearGrid($this->gridCittadinanze);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    function CreaSql() {
        $sql = "SELECT * FROM CITTADINANZA";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " WHERE CODICE LIKE '%" . addslashes($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Cittadinanza'] != "") {
            $sql .= " WHERE CITTADINANZA LIKE '%" . addslashes(strtoupper($_POST[$this->nameForm . '_Cittadinanza'])) . "%'";
        }
        App::log($sql);
        return $sql;
    }

    function Dettaglio($indice) {
        $cittadinanza_rec = $this->basLib->getCittadinanze($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $cittadinanza_rec['CITTADINANZA'];
        $this->openRecord($this->COMUNI_DB, 'CITTADINANZA', $open_Info);
        $this->Nascondi();
        Out::valori($cittadinanza_rec, $this->nameForm . '_CITTADINANZA');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_CITTADINANZA[_CITTADINANZA]');
        TableView::disableEvents($this->gridCittadinanze);
    }

}

?>