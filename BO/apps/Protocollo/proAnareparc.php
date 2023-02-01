<?php

/**
 *
 * ANAGRAFICA REPERTORI ARCHIVISTICI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    26.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';

function proAnareparc() {
    $proAnareparc = new proAnareparc();
    $proAnareparc->parseEvent();
    return;
}

class proAnareparc extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibFascicolo;
    public $nameForm = "proAnareparc";
    public $divGes = "proAnareparc_divGestione";
    public $divRis = "proAnareparc_divRisultato";
    public $divRic = "proAnareparc_divRicerca";
    public $gridAnareparc = "proAnareparc_gridAnareparc";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->proLibFascicolo = new proLibFascicolo();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnareparc:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnareparc:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnareparc:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAREPARC[CODICE]');
                        break;
                }
                break;

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnareparc', $parameters);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnareparc,
                                array(
                                    'sqlDB' => $this->PROT_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESCRIZIONE');
                $ita_grid01->exportXLS('', 'ANAREPARC.xls');
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnareparc,
                                        array(
                                            'sqlDB' => $this->PROT_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnareparc]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('DESCRIZIONE');
                        $ita_grid01->setSortOrder('asc');

                        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());

                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnareparc);
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
                        Out::setFocus('', $this->nameForm . '_ANAREPARC[CODICE]');
                        Out::attributo($this->nameForm . '_ANAREPARC[CODICE]', "readonly", '1');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $anareparc_rec = $this->proLib->getAnareparc($_POST[$this->nameForm . '_ANAREPARC']['CODICE']);
                        if (!$anareparc_rec) {
                            $anareparc_rec = $_POST[$this->nameForm . '_ANAREPARC'];

                            $anareparc_rec['CODICE'] = str_pad($anareparc_rec['CODICE'], 10, '0', STR_PAD_LEFT);

                            $insert_Info = 'Oggetto: ' . $anareparc_rec['CODICE'] . " " . $anareparc_rec['DESCRIZIONE'];
                            if ($this->insertRecord($this->PROT_DB, 'ANAREPARC', $anareparc_rec, $insert_Info)) {
                                Out::msgBlock('', 1500, true, "Repertorio registrato correttamente.");
                                $anareparc_rec = $this->proLib->getAnareparc($anareparc_rec['CODICE']);
                                $this->Dettaglio($anareparc_rec['ROWID']);
                            }
                        } else {
                            Out::msgInfo("Attenzione!", "Codice già  presente. Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANAREPARC[CODICE]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anareparc_rec = $_POST[$this->nameForm . '_ANAREPARC'];
                        $update_Info = 'Oggetto: ' . $anareparc_rec['CODICE'] . " " . $anareparc_rec['DESCRIZIONE'];
                        if ($this->updateRecord($this->PROT_DB, 'ANAREPARC', $anareparc_rec, $update_Info)) {
                            Out::msgBlock('', 1000, true, "Repertorio modificato correttamente.");
                            $this->Dettaglio($anareparc_rec['ROWID']);
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
                        $delete_Info = 'Oggetto: ' . $anareparc_rec['CODICE'] . " " . $anareparc_rec['DESCRIZIONE'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANAREPARC', $_POST[$this->nameForm . '_ANAREPARC']['ROWID'], $delete_Info)) {
                            Out::msgBlock('', 1000, true, "Repertorio eliminato correttamente.");
                            $this->OpenRicerca();
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $codice = str_pad($_POST[$this->nameForm . '_Codice'], 10, '0', STR_PAD_LEFT);
                        $anareparc_rec = $this->proLib->getAnareparc($codice);
                        if ($anareparc_rec) {
                            $this->Dettaglio($anareparc_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_ANAREPARC[CODICE]':
                        $codice = str_pad($_POST[$this->nameForm . '_ANAREPARC']['CODICE'], 10, '0', STR_PAD_LEFT);
                        Out::valore($this->nameForm . '_ANAREPARC[CODICE]', $codice);
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_ANAREPARC[CARATTERE]', 1, 'G', 1, 'GENERALE');
        Out::select($this->nameForm . '_ANAREPARC[CARATTERE]', 1, 'P', 0, 'PARTICOLARE');
        Out::select($this->nameForm . '_ANAREPARC[TIPOPROGRESSIVO]', 1, 'ANNUALE', 1, 'ANNUALE');
        Out::select($this->nameForm . '_ANAREPARC[TIPOPROGRESSIVO]', 1, 'ASSOLUTO', 0, 'ASSOLUTO');
    }

    public function OpenRicerca() {
        $this->proLib->checkRepertorioProtetto();
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
        TableView::disableEvents($this->gridAnareparc);
        TableView::clearGrid($this->gridAnareparc);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANAREPARC[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ANAREPARC[CARATTERE]', 'G');
        Out::valore($this->nameForm . '_ANAREPARC[TIPOPROGRESSIVO]', 'ANNUALE');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
    }

    public function CreaSql() {
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANAREPARC WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND ".$this->PROT_DB->strUpper('CODICE')." LIKE '%" . strtoupper($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND ".$this->PROT_DB->strUpper('DESCRIZIONE')." LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%'";
        }
        return $sql;
    }

    public function Dettaglio($rowid) {
        $anareparc_rec = $this->proLib->getAnareparc($rowid, 'rowid');
        $open_Info = 'Oggetto: ' . $anareparc_rec['CODICE'] . " " . $anareparc_rec['DESCRIZIONE'];
        $this->openRecord($this->PROT_DB, 'ANAREPARC', $open_Info);
        $this->Nascondi();
        Out::valori($anareparc_rec, $this->nameForm . '_ANAREPARC');
        Out::show($this->nameForm . '_Aggiorna');

        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_ANAREPARC[DESCRIZIONE]');
        TableView::disableEvents($this->gridAnareparc);
        Out::attributo($this->nameForm . '_ANAREPARC[CODICE]', "readonly", '0');

        if ($anareparc_rec['CODICE'] !== $this->proLibFascicolo->repertoriofascicoli) {
            Out::show($this->nameForm . '_Cancella');
        }
    }

    private function elaboraRecords($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            if ($result_rec['CARATTERE'] == 'G') {
                $result_tab[$key]['CARATTERE'] = 'GENERALE';
            } else if ($result_rec['CARATTERE'] == 'P') {
                $result_tab[$key]['CARATTERE'] = 'PARTICOLARE';
            }
        }
        return $result_tab;
    }

//    private function checkRepertorioProtetto() {
//        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
//        $proLibFascicolo = new proLibFascicolo();
//        $repertorio_rec = $this->proLib->getAnareparc($proLibFascicolo->repertoriofascicoli);
//        if (!$repertorio_rec) {
//            $repertorio_rec = array();
//            $repertorio_rec['CODICE'] = $proLibFascicolo->repertoriofascicoli;
//            $repertorio_rec['DESCRIZIONE'] = "REPERTORIO DEI PROCEDIMENTI AMMINISTRATIVI";
//            $repertorio_rec['CARATTERE'] = "G";
//            $repertorio_rec['PROGRESSIVO'] = 0;
//            $repertorio_rec['TIPOPROGRESSIVO'] = "ANNUALE";
//            $insert_Info = 'Oggetto: ' . $repertorio_rec['CODICE'] . " " . $repertorio_rec['DESCRIZIONE'];
//            $this->insertRecord($this->PROT_DB, 'ANAREPARC', $repertorio_rec, $insert_Info);
//        }
//    }
}

?>
