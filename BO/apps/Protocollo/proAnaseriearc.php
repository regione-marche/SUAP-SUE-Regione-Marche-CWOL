<?php

/**
 *
 * ANAGRAFICA SERIE ARCHIVISTICHE
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
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function proAnaseriearc() {
    $proAnaseriearc = new proAnaseriearc();
    $proAnaseriearc->parseEvent();
    return;
}

class proAnaseriearc extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSerie;
    public $nameForm = "proAnaseriearc";
    public $progserie;
    public $lockSerie;
    public $divGes = "proAnaseriearc_divGestione";
    public $divRis = "proAnaseriearc_divRisultato";
    public $divRic = "proAnaseriearc_divRicerca";
    public $divDet = "proAnaseriearc_divDettaglio";
    public $gridAnaseriearc = "proAnaseriearc_gridAnaseriearc";
    public $gridAnaseriedett = "proAnaseriearc_gridAnaseriedett";
    public $gridPannelli = "proAnaseriearc_gridPannelli";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibSerie = new proLibSerie();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->progserie = App::$utente->getKey($this->nameForm . '_progserie');
            $this->lockSerie = App::$utente->getKey($this->nameForm . '_lockSerie');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_progserie', $this->progserie);
        App::$utente->setKey($this->nameForm . '_lockSerie', $this->lockSerie);
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
                    case $this->gridAnaseriearc:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAnaseriedett:
                        $this->DettaglioSerie($_POST['rowid'], 'Edit');
                        break;
                    case $this->gridPannelli:
                        $this->DettaglioPannello($_POST['rowid'], 'Edit');
                        break;
                    
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaseriearc:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                    case $this->gridAnaseriedett:
                        $this->progserie = $_POST['rowid'];
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaSerie', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaseriearc:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::hide($this->divDet);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANASERIEARC[CODICE]');
                        break;
                    case $this->gridAnaseriedett:
                        $this->DettaglioSerie();
                        break;
                }
                break;

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaseriearc', $parameters);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaseriearc, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESCRIZIONE');
                $ita_grid01->exportXLS('', 'ANASERIEARC.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnaseriearc:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                        $ita_grid01->getDataPageFromArray('json', $result_tab);
                        break;
                    case $this->gridAnaseriedett:
                        //$where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANASERIEARC']['CODICE'];
                        $this->CreaSqldettaglio($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Torna':
                        $this->unlockserie($this->lockSerie);
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnaseriearc, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnaseriearc]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('DESCRIZIONE');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::hide($this->divDet);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnaseriearc);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->unlockserie($this->lockSerie);
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::hide($this->divDet);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANASERIEARC[CODICE]');
                        Out::show($this->nameForm . '_ANASERIEARC[PROGRESSIVO]_field');
                        Out::attributo($this->nameForm . '_ANASERIEARC[CODICE]', "readonly", '1');
                        Out::hide($this->nameForm . '_gridAnaseriedett_delGridRow');
                        Out::hide($this->nameForm . '_gridAnaseriedett_addGridRow');
                        TableView::clearGrid($this->gridAnaseriedett);
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $anaseriearc_rec = $this->proLib->getAnaseriearc($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
                        if (!$anaseriearc_rec) {
                            $anaseriearc_rec = $_POST[$this->nameForm . '_ANASERIEARC'];

                            if ($anaseriearc_rec['GESTPANEL'] == 1) {
                                $anaseriearc_rec['METAPANEL'] = json_encode(proLibSerie::$PANEL_LIST);
                            }
                            
                            $insert_Info = 'Oggetto: ' . $anaseriearc_rec['CODICE'] . " " . $anaseriearc_rec['DESCRIZIONE'];
                            if ($this->insertRecord($this->PROT_DB, 'ANASERIEARC', $anaseriearc_rec, $insert_Info)) {
                                Out::msgBlock('', 1500, true, "Serie Archivistica registrata correttamente.");
                                $anaseriearc_rec = $this->proLib->getAnaseriearc($anaseriearc_rec['CODICE']);
                                $this->Dettaglio($anaseriearc_rec['ROWID']);
                                $this->CreaSqldettaglio($anaseriearc_rec['CODICE']);
                            }
                        } else {
                            Out::msgInfo("Attenzione!", "Codice già  presente. Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANASERIEARC[CODICE]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anaseriearc_rec = $_POST[$this->nameForm . '_ANASERIEARC'];
                        $update_Info = 'Oggetto: ' . $anaseriearc_rec['CODICE'] . " " . $anaseriearc_rec['DESCRIZIONE'];
                        if ($_POST[$this->nameForm . '_ANASERIEARC']['TIPOPROGRESSIVO'] != 'ANNUALE') {
                            $progserie_rec = $this->proLib->getProgserie($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
                            if ($progserie_rec) {
                                Out::msgStop("ERRORE", "Trovati progressivi Annuali per la serie impossibile aggiornare la Tipologia Progressivo a " . $_POST[$this->nameForm . '_ANASERIEARC']['TIPOPROGRESSIVO'] . " salvataggio dati non risucito");
                                break;
                            }
                        }

                        $anaseriearc_rec_ctrl = $this->proLib->getAnaseriearc($anaseriearc_rec['CODICE']);
                        if ($anaseriearc_rec_ctrl['METAPANEL'] && $anaseriearc_rec['GESTPANEL'] == 0) {
                            $anaseriearc_rec['METAPANEL'] = '';
                        }
                        if (!$anaseriearc_rec_ctrl['METAPANEL'] && $anaseriearc_rec['GESTPANEL'] == 1) {
                            $anaseriearc_rec['METAPANEL'] = json_encode(proLibSerie::$PANEL_LIST);
                        }

                        
                        if ($this->updateRecord($this->PROT_DB, 'ANASERIEARC', $anaseriearc_rec, $update_Info)) {
                            Out::msgBlock('', 1000, true, "Serie Archivistica modificata correttamente.");
                            $this->Dettaglio($anaseriearc_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_ConfermaAggiornaSerie':
                        $campi = array('ANNO' => $_POST[$this->nameForm . '_Annoserie'], 'PROGRESSIVO' => $_POST[$this->nameForm . '_Progressivoserie'], 'FLAG_CHIUSO' => $_POST[$this->nameForm . '_Flagserie'], 'ROWID' => $this->progserie);
                        $progserie_tab = $this->proLib->getProgserie($_POST[$this->nameForm . '_ANASERIEARC']['CODICE'], 'codice', true);
                        $i = 0;
                        foreach ($progserie_tab as $value) {
                            if ($value['ANNO'] == $_POST[$this->nameForm . '_Annoserie'] && $value['ROWID'] != $this->progserie) {
                                Out::msgStop("Errore", "Anno già esistente per la serie");
                                $i++;  // CONTROLLO CHE SE L'ANNO è NUOVO NON DEVE ESSERE UN DUPLICATO PER QUELLA SERIE
                                break;
                            }
                        }
                        if ($i == 0) {
                            $this->Aggiorna($campi);
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
                        $serie = $this->proLib->getProgserie($_POST[$this->nameForm . '_ANASERIEARC']['CODICE'], 'codice');
                        if (!empty($serie)) {
                            Out::msgStop("ERRORE", "Eliminare i sottoelementi serie annuale prima di procedere");
                            return false;
                        }
                        $delete_Info = 'Oggetto: ' . $anaseriearc_rec['CODICE'] . " " . $anaseriearc_rec['DESCRIZIONE'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANASERIEARC', $_POST[$this->nameForm . '_ANASERIEARC']['ROWID'], $delete_Info)) {
                            Out::msgBlock('', 1000, true, "Serie Archivistica eliminata correttamente.");
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancellaSerie':
                        if ($this->deleteRecord($this->PROT_DB, 'PROGSERIE', $this->progserie, $delete_Info)) {
                            //$where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANASERIEARC']['CODICE'];
                            $this->CreaSqldettaglio($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
                            unset($this->progserie);
                        }
                        break;
                    case $this->nameForm . '_segnaturaFasVar':
                        $this->embedVarsFas("returnModelloSegnauraFas");
                        break;

                    case $this->nameForm . '_ConfermaPannello':
                        $_POST[$this->nameForm . '_FlagAttiva'];
                        $record = $this->AggiornaParametri($_POST[$this->nameForm . '_rowid'], $_POST[$this->nameForm . '_FlagAttiva']);
                        $this->CaricaGriglia($this->gridPannelli, $record);
                        break;
                    
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        $anaseriearc_rec = $this->proLib->getAnaseriearc($codice);
                        if ($anaseriearc_rec) {
                            $this->Dettaglio($anaseriearc_rec['ROWID']);
                        }
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANASERIEARC[GESTPANEL]':
                        if ($_POST[$this->nameForm . '_ANASERIEARC']['GESTPANEL'] == 1) {
                        
                            $anaseriearc_rec = $this->proLib->getAnaseriearc($_POST[$this->nameForm . '_ANASERIEARC']['ROWID'], 'rowid');
                            if ($anaseriearc_rec['METAPANEL']) {
                                $valueAnagrafica = $this->proLibSerie->decodParametriPanelFascicolo($anaseriearc_rec['METAPANEL'], 'Anagrafica');
                            } else {
                                $valueAnagrafica = proLibSerie::$PANEL_LIST;
                            }
                            Out::show($this->nameForm . '_divModuli');
                            $this->CaricaGriglia($this->gridPannelli, $valueAnagrafica);
                        } else {
                            Out::hide($this->nameForm . '_divModuli');
                        }
                        break;
                
                
                    case $this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]':
                        if ($_POST[$this->nameForm . '_ANASERIEARC']['TIPOPROGRESSIVO'] == 'ASSOLUTO') {
                            Out::show($this->nameForm . '_ANASERIEARC[PROGRESSIVO]_field');
                        } else {
                            Out::hide($this->nameForm . '_ANASERIEARC[PROGRESSIVO]_field');
                        }
                        if ($_POST[$this->nameForm . '_ANASERIEARC']['TIPOPROGRESSIVO'] == 'ANNUALE') {
                            // $where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANASERIEARC']['CODICE'];
                            $this->CreaSqldettaglio($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
                            Out::show($this->nameForm . '_divDettaglioSerie');
                        } else {
                            Out::hide($this->nameForm . '_divDettaglioSerie');
                        }
                        break;
                }
                break;
            case 'returnModelloSegnauraFas':
                Out::codice("$(protSelector('#" . $this->nameForm . '_ANASERIEARC[SEGTEMPLATE]' . "')).replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        $this->unlockserie($this->lockSerie);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]', 1, 'ANNUALE', 1, 'ANNUALE');
        Out::select($this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]', 1, 'ASSOLUTO', 0, 'ASSOLUTO');
        Out::select($this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]', 1, 'MANUALE', 0, 'MANUALE');
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divDet);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
        TableView::disableEvents($this->gridAnaseriearc);
        TableView::clearGrid($this->gridAnaseriearc);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANASERIEARC[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]', 'ANNUALE');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divModuli');
        
    }

    public function CreaSql() {
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANASERIEARC WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND " . $this->PROT_DB->strUpper('CODICE') . " LIKE '%" . strtoupper($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND " . $this->PROT_DB->strUpper('DESCRIZIONE') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%'";
        }
        return $sql;
    }

    public function CreaSqldettaglio($codice = '') {
        if (!$codice) {
            TableView::clearGrid($this->gridAnaseriedett);
            Out::hide($this->nameForm . '_gridAnaseriedett_delGridRow');
            Out::hide($this->nameForm . '_gridAnaseriedett_addGridRow');
            return false;
        }
        $sql = "SELECT * FROM PROGSERIE WHERE CODICE = " . $codice;
        $ita_grid01 = new TableView($this->gridAnaseriedett, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$this->gridAnaseriedett]['gridParam']['rowNum']);
//        $ita_grid01->setSortIndex('');
//        $ita_grid01->setSortOrder('asc');
//        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());

//        $ita_grid01 = new TableView($this->gridAnaseriedett, array(
//            'sqlDB' => $this->PROT_DB,
//            'sqlQuery' => $sql));
        $ita_grid01->setSortIndex($_POST['sidx']);
        $ita_grid01->setSortOrder($_POST['sord']);
        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
            TableView::clearGrid($this->gridAnaseriedett);
        } else {
            TableView::enableEvents($this->gridAnaseriedett);
        }
        Out::show($this->nameForm . '_gridAnaseriedett_delGridRow');
        Out::show($this->nameForm . '_gridAnaseriedett_addGridRow');
    }

    public function Dettaglio($rowid) {
        $this->lockSerie = $this->proLibSerie->lockSerie($rowid); // BLOCCO LA SERIE
        if (!$this->lockSerie) {
            Out::msgStop("ERRORE", $this->proLibSerie->getErrMessage());
            return false;
        }
        $anaseriearc_rec = $this->proLib->getAnaseriearc($rowid, 'rowid');
        $open_Info = 'Oggetto: ' . $anaseriearc_rec['CODICE'] . " " . $anaseriearc_rec['DESCRIZIONE'];
        $this->openRecord($this->PROT_DB, 'ANASERIEARC', $open_Info);
        $this->Nascondi();
        Out::valori($anaseriearc_rec, $this->nameForm . '_ANASERIEARC');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divDet);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_ANASERIEARC[DESCRIZIONE]');
        if ($anaseriearc_rec['TIPOPROGRESSIVO'] == 'ASSOLUTO') {
            Out::show($this->nameForm . '_ANASERIEARC[PROGRESSIVO]_field');
        } else {
            Out::hide($this->nameForm . '_ANASERIEARC[PROGRESSIVO]_field');
        }
        if ($anaseriearc_rec['TIPOPROGRESSIVO'] == 'ANNUALE') {
            //$where = "WHERE CODICE =" . $anaseriearc_rec['CODICE'];
            $this->CreaSqldettaglio($anaseriearc_rec['CODICE']);
            Out::show($this->nameForm . '_divDettaglioSerie');
        } else {
            Out::hide($this->nameForm . '_divDettaglioSerie');
        }
        
        if ($anaseriearc_rec['GESTPANEL'] == 1) {
            if ($anaseriearc_rec['METAPANEL']) {
                $valueAnagrafica = $this->proLibSerie->decodParametriPanelFascicolo($anaseriearc_rec['METAPANEL'], 'Anagrafica');
            } else {
                $valueAnagrafica = proLibSerie::$PANEL_LIST;
            }
            Out::show($this->nameForm . '_divModuli');
            // Out::msgInfo('record elaborato', print_r($valueAnagrafica,true));
            $this->CaricaGriglia($this->gridPannelli, $valueAnagrafica);
        }
        
        
        TableView::disableEvents($this->gridAnaseriearc);
        Out::attributo($this->nameForm . '_ANASERIEARC[CODICE]', "readonly", '0');
    }

    public function DettaglioSerie($rowid = '', $tipo = '') {
        if ($rowid) {
            $anaserie_rec = $this->proLib->getProgserie($rowid, 'rowid');
        }
        if ($anaserie_rec['FLAG_CHIUSO'] == 0) {
            $check = '';
        } else {
            $check = 'checked';
        }
        if ($tipo == 'Edit') {
            $readonly = 'readonly';
        }
        $this->progserie = $anaserie_rec['ROWID'];
        $valori = array();
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Anno '),
            'id' => $this->nameForm . '_Annoserie',
            'name' => $this->nameForm . '_Annoserie',
            'value' => $anaserie_rec['ANNO'],
            'type' => 'text',
            'width' => '20',
            'size' => '6',
            $readonly => $readonly,
            'maxlength' => '4');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Ultimo Progressivo '),
            'id' => $this->nameForm . '_Progressivoserie',
            'name' => $this->nameForm . '_Progressivoserie',
            'value' => $anaserie_rec['PROGRESSIVO'],
            'type' => 'text',
            'width' => '20',
            'size' => '15',
            'maxlength' => '11');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Flag di chiusura '),
            'id' => $this->nameForm . '_Flagserie',
            'name' => $this->nameForm . '_Flagserie',
            $check => $check,
            'type' => 'checkbox',
            'class' => 'ita-check-box',
        );

        Out::msgInput(
                "Progressivi Annuali", $valori, array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiornaSerie', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                ), $this->nameForm
        );
    }

    public function Aggiorna($campi) {

        $progserie_rec = $this->proLib->getProgserie($this->progserie, 'rowid');
        if ($progserie_rec) {
            $this->updateRecord($this->PROT_DB, 'PROGSERIE', $campi, '', 'ROWID', false);
        } else {
            $campi['CODICE'] = $_POST[$this->nameForm . '_ANASERIEARC']['CODICE'];
            $this->insertRecord($this->PROT_DB, 'PROGSERIE', $campi, '', 'ROWID', false);
        }
        $this->CreaSqldettaglio($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
        unset($this->progserie);
    }

    private function elaboraRecords($result_tab) {
        return $result_tab;
    }

    private function embedVarsFas($ritorno) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabiliFascicolo.class.php';
        $proLibVarFascicolo = new proLibVariabiliFascicolo();
        $dictionaryLegend = $proLibVarFascicolo->getLegenda('adjacency', 'smarty');
        docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
        return true;
    }

    private function unlockserie($retLock) {
        if (empty($retLock)) {
            return false;
        }
        $unlockserie = $this->proLibSerie->unlockSerie($retLock); // SBLOCCO LA SERIE ALL'USCITA
        if (!$unlockserie) {
            Out::msgStop("ERRORE", $this->proLibSerie->getErrMessage());
            return false;
        }
        unset($this->lockSerie);
        return true;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = '20') {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }
    
    private function DettaglioPannello($rowid, $tipo = '') {

        $Param_rec = proLibSerie::$PANEL_LIST[$rowid];

        $anaseriearc_rec = $this->proLib->getAnaseriearc($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
        if ($anaseriearc_rec['METAPANEL']) {
            $metadato = $this->proLibSerie->decodParametriPanelFascicolo($anaseriearc_rec['METAPANEL']);
            $Param_rec['DEF_STATO'] = $metadato[$rowid]['DEF_STATO'];
        }

        if ($Param_rec['DEF_STATO'] == 0) {
            $check = '';
        } else {
            $check = 'checked';
        }
        if ($tipo == 'Edit') {
            $readonly = 'readonly';
        }

        $valori = array();
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;visibility:hidden;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Rowid'),
            'id' => $this->nameForm . '_rowid',
            'name' => $this->nameForm . '_rowid',
            'value' => $_POST['rowid'],
            'type' => 'text',
            'width' => '20',
            'size' => '6',
            'style' => 'visibility:hidden;',
            $readonly => $readonly,
            'maxlength' => '');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Sequenza '),
            'id' => $this->nameForm . '_Sequenza',
            'name' => $this->nameForm . '_Sequenza',
            'value' => $Param_rec['EF_SEQ'],
            'type' => 'text',
            'width' => '20',
            'size' => '6',
            $readonly => $readonly,
            'maxlength' => '');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Descrizione'),
            'id' => $this->nameForm . '_DescTab',
            'name' => $this->nameForm . '_DescTab',
            'value' => $Param_rec['DESCRIZIONE'],
            'type' => 'text',
            'width' => '20',
            'size' => '15',
            $readonly => $readonly,
            'maxlength' => '');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Attivo'),
            'id' => $this->nameForm . '_FlagAttiva',
            'name' => $this->nameForm . '_FlagAttiva',
            $check => $check,
            'type' => 'checkbox',
            'class' => 'ita-check-box',
        );
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'File Xml'),
            'id' => $this->nameForm . '_File_xml',
            'name' => $this->nameForm . '_File_xml',
            'value' => $Param_rec['FILE_XML'],
            'type' => 'text',
            'width' => '20',
            'size' => '15',
            $readonly => $readonly,
            'maxlength' => '');

        Out::msgInput(
                "Progressivi Annuali", $valori, array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaPannello', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                ), $this->nameForm
        );
    }

    private function AggiornaParametri($rowid, $flag) {
        $anaseriearc_rec = $this->proLib->getAnaseriearc($_POST[$this->nameForm . '_ANASERIEARC']['CODICE']);
        if (!$anaseriearc_rec['METAPANEL']) {
            $parametro = proLibSerie::$PANEL_LIST;
            $parametro[$rowid]['DEF_STATO'] = $flag;
        } else {
            $parametro = $this->proLibSerie->decodParametriPanelFascicolo($anaseriearc_rec['METAPANEL'], 'Anagrafica');
            $parametro[$rowid]['DEF_STATO'] = $flag;
        }

        $anaseriearc_rec['METAPANEL'] = json_encode($parametro);
        $update_Info = 'Oggetto: ' . $anaseriearc_rec['CODICE'] . " " . $anaseriearc_rec['DESCRIZIONE'];
        
        if (!$this->updateRecord($this->PROT_DB, 'ANASERIEARC', $anaseriearc_rec, $update_Info)) {
            Out::msgStop('ATTANZIONE', 'Salvataggio Parametri non riuscito');
            return false;
        }
        return $parametro;
    }

    
}


