<?php

/**
 *
 * ANAGRAFICA REGISTRI ARCHIVISTICI
 *
 * PHP Version 5
 *
 * @category
 * @author 
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version 
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibRegistro.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function proAnaregistriarc() {
    $proAnaregistriarc = new proAnaregistriarc();
    $proAnaregistriarc->parseEvent();
    return;
}

class proAnaregistriarc extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibRegistro;
    public $nameForm = "proAnaregistriarc";
    public $progregistro;
    public $lockRegistro;
    public $divGes = "proAnaregistriarc_divGestione";
    public $divRis = "proAnaregistriarc_divRisultato";
    public $divRic = "proAnaregistriarc_divRicerca";
    public $divDet = "proAnaregistriarc_divDettaglio";
    public $gridAnaregistroarc = "proAnaregistriarc_gridAnaregistroarc";
    public $gridAnaregistrodett = "proAnaregistriarc_gridAnaregistrodett";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibRegistro = new proLibRegistro();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->progregistro = App::$utente->getKey($this->nameForm . '_progregistro');
            $this->lockRegistro = App::$utente->getKey($this->nameForm . '_lockRegistro');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_progregistro', $this->progregistro);
        App::$utente->setKey($this->nameForm . '_lockRegistro', $this->lockRegistro);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                Out::hide($this->nameForm . '_divDettaglioRegistro');
                Out::hide($this->nameForm . '_divSegnaturaRegistro');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaregistroarc:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAnaregistrodett:
                        $this->DettaglioRegistro($_POST['rowid'], 'Edit');
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaregistroarc:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                    case $this->gridAnaregistrodett:
                        $this->progregistro = $_POST['rowid'];
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaRegistro', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaregistroarc:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::hide($this->divDet);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAREGISTRIARC[CODICE]');
                        break;
                    case $this->gridAnaregistrodett:
                        $this->DettaglioRegistro();
                        break;
                }
                break;

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaregistriarc', $parameters);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaregistroarc, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESCRIZIONE');
                $ita_grid01->exportXLS('', 'ANAREGISTRIARC.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnaregistroarc:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                        $ita_grid01->getDataPageFromArray('json', $result_tab);
                        break;
                    case $this->gridAnaregistrodett:
                        //$where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANAREGISTRIARC']['CODICE'];
                        $this->CreaSqldettaglio($_POST[$this->nameForm . '_ANAREGISTRIARC']['CODICE']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Torna':
                        $this->unlockregistro($this->lockRegistro);
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnaregistroarc, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnaregistroarc]['gridParam']['rowNum']);
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
                            TableView::enableEvents($this->gridAnaregistroarc);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->unlockregistro($this->lockRegistro);
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
                        Out::setFocus('', $this->nameForm . '_ANAREGISTRIARC[CODICE]');
                        Out::show($this->nameForm . '_ANAREGISTRIARC[PROGRESSIVO]_field');
                        Out::attributo($this->nameForm . '_ANAREGISTRIARC[CODICE]', "readonly", '1');
                        Out::hide($this->nameForm . '_gridAnaregistrodett_delGridRow');
                        Out::hide($this->nameForm . '_gridAnaregistrodett_addGridRow');
                        TableView::clearGrid($this->gridAnaregistrodett);
                        Out::hide($this->nameForm . '_divDettaglioRegistro');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $anaregistroarc_rec = $this->proLib->getAnaregistroarc($_POST[$this->nameForm . '_ANAREGISTRIARC']['SIGLA']);
                        if (!$anaregistroarc_rec) {
                            $anaregistroarc_rec = $_POST[$this->nameForm . '_ANAREGISTRIARC'];
                            $insert_Info = 'Oggetto: ' . $anaregistroarc_rec['SIGLA'] . " " . $anaregistroarc_rec['DESCRIZIONE'];
                            if ($this->insertRecord($this->PROT_DB, 'ANAREGISTRIARC', $anaregistroarc_rec, $insert_Info)) {
                                Out::msgBlock('', 1500, true, "Registro Archivistico registrato correttamente.");
                                $anaregistroarc_rec = $this->proLib->getAnaregistroarc($anaregistroarc_rec['SIGLA']);
                                $this->Dettaglio($anaregistroarc_rec['ROW_ID']);
                                $this->CreaSqldettaglio($anaregistroarc_rec['ROW_ID']);
                            }
                        } else {
                            Out::msgInfo("Attenzione!", "Sigla già  presente. Inserire una nuova sigla.");
                            Out::setFocus('', $this->nameForm . '_ANAREGISTRIARC[SIGLA]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anaregistroarc_rec = $_POST[$this->nameForm . '_ANAREGISTRIARC'];
                        $update_Info = 'Oggetto: ' . $anaregistroarc_rec['CODICE'] . " " . $anaregistroarc_rec['DESCRIZIONE'];
                        if ($_POST[$this->nameForm . '_ANAREGISTRIARC']['TIPOPROGRESSIVO'] != 'ANNUALE') {
                            $progregistro_rec = $this->proLib->getProgregistro($_POST[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID']);
                            if ($progregistro_rec) {
                                Out::msgStop("ERRORE", "Trovati progressivi Annuali per la registro impossibile aggiornare la Tipologia Progressivo a " . $_POST[$this->nameForm . '_ANAREGISTRIARC']['TIPOPROGRESSIVO'] . " salvataggio dati non risucito");
                                break;
                            }
                        }

                        if ($this->updateRecord($this->PROT_DB, 'ANAREGISTRIARC', $anaregistroarc_rec, $update_Info)) {
                            Out::msgBlock('', 1000, true, "Registro Archivistico modificata correttamente.");
                            $this->Dettaglio($anaregistroarc_rec['ROW_ID']);
                        }
                        break;
                    case $this->nameForm . '_ConfermaAggiornaRegistro':
                        $campi = array('ANNO' => $_POST[$this->nameForm . '_Annoregistro'], 'PROGRESSIVO' => $_POST[$this->nameForm . '_Progressivoregistro'], 'FLAG_CHIUSO' => $_POST[$this->nameForm . '_Flagregistro'], 'ROW_ID' => $this->progregistro);
                        $progregistro_tab = $this->proLib->getProgregistro($_POST[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID'], 'codice', true);
                        $i = 0;
                        foreach ($progregistro_tab as $value) {
                            if ($value['ANNO'] == $_POST[$this->nameForm . '_Annoregistro'] && $value['ROW_ID'] != $this->progregistro) {
                                Out::msgStop("Errore", "Anno già esistente per la registro");
                                $i++;  // CONTROLLO CHE SE L'ANNO è NUOVO NON DEVE ESSERE UN DUPLICATO PER QUELLA REGISTRO
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
                        $registro = $this->proLib->getProgregistro($_POST[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID'], 'codice');
                        if (!empty($registro)) {
                            Out::msgStop("ERRORE", "Eliminare i sottoelementi registro annuale prima di procedere");
                            return false;
                        }
                        $delete_Info = 'Oggetto: ' . $anaregistroarc_rec['SIGLA'] . " " . $anaregistroarc_rec['DESCRIZIONE'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANAREGISTRIARC', $_POST[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID'], $delete_Info, 'ROW_ID')) {
                            Out::msgBlock('', 1000, true, "Registro Archivistico eliminata correttamente.");
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancellaRegistro':
                        if ($this->deleteRecord($this->PROT_DB, 'ANAREGISTRIPROG', $this->progregistro, $delete_Info, 'ROW_ID')) {
                            //$where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANAREGISTRIARC']['CODICE'];
                            $this->CreaSqldettaglio($this->formData[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID']);
                            unset($this->progregistro);
                        }
                        break;
                    case $this->nameForm . '_segnaturaFasVar':
                        $this->embedVarsFas("returnModelloSegnauraFas");
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
                        $anaregistroarc_rec = $this->proLib->getAnaregistroarc($codice);
                        if ($anaregistroarc_rec) {
                            $this->Dettaglio($anaregistroarc_rec['ROW_ID']);
                        }
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAREGISTRIARC[TIPOPROGRESSIVO]':
                        if ($_POST[$this->nameForm . '_ANAREGISTRIARC']['TIPOPROGRESSIVO'] == 'ASSOLUTO') {
                            Out::show($this->nameForm . '_ANAREGISTRIARC[PROGRESSIVO]_field');
                        } else {
                            Out::hide($this->nameForm . '_ANAREGISTRIARC[PROGRESSIVO]_field');
                        }
                        if ($_POST[$this->nameForm . '_ANAREGISTRIARC']['TIPOPROGRESSIVO'] == 'ANNUALE') {
                            // $where = "WHERE CODICE =" . $_POST[$this->nameForm . '_ANAREGISTRIARC']['CODICE'];
                            $this->CreaSqldettaglio($this->formData[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID']);
                            Out::show($this->nameForm . '_divDettaglioRegistro');
                        } else {
                            Out::hide($this->nameForm . '_divDettaglioRegistro');
                        }
                        break;
                }
                break;
            case 'returnModelloSegnauraFas':
                Out::codice("$(protSelector('#" . $this->nameForm . '_ANAREGISTRIARC[SEGTEMPLATE]' . "')).replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        $this->unlockregistro($this->lockRegistro);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_ANAREGISTRIARC[TIPOPROGRESSIVO]', 1, 'ANNUALE', 1, 'ANNUALE');
        Out::select($this->nameForm . '_ANAREGISTRIARC[TIPOPROGRESSIVO]', 1, 'ASSOLUTO', 0, 'ASSOLUTO');
        Out::select($this->nameForm . '_ANAREGISTRIARC[TIPOPROGRESSIVO]', 1, 'MANUALE', 0, 'MANUALE');
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
        Out::setFocus('', $this->nameForm . '_Sigla');
        TableView::disableEvents($this->gridAnaregistroarc);
        TableView::clearGrid($this->gridAnaregistroarc);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANAREGISTRIARC[ROW_ID]', '');
        Out::clearFields($this->nameForm);
        Out::valore($this->nameForm . '_ANAREGISTRIARC[TIPOPROGRESSIVO]', 'ANNUALE');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function CreaSql() {
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANAREGISTRIARC WHERE 1=1";
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
            TableView::clearGrid($this->gridAnaregistrodett);
            Out::hide($this->nameForm . '_gridAnaregistrodett_delGridRow');
            Out::hide($this->nameForm . '_gridAnaregistrodett_addGridRow');
            return false;
        }
        TableView::clearGrid($this->gridAnaregistrodett);
        $sql = "SELECT * FROM ANAREGISTRIPROG WHERE ROWID_ANAREGISTRO = " . $codice;
        $ita_grid01 = new TableView($this->gridAnaregistrodett, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$this->gridAnaregistrodett]['gridParam']['rowNum']);
        $ita_grid01->setSortIndex('');
        $ita_grid01->setSortOrder('asc');
        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
        $ita_grid01 = new TableView($this->gridAnaregistrodett, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setSortIndex($_POST['sidx']);
        $ita_grid01->setSortOrder($_POST['sord']);
        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
            TableView::clearGrid($this->gridAnaregistrodett);
        } else {
            TableView::enableEvents($this->gridAnaregistrodett);
        }
        Out::show($this->nameForm . '_gridAnaregistrodett_delGridRow');
        Out::show($this->nameForm . '_gridAnaregistrodett_addGridRow');
    }

    public function Dettaglio($rowid) {
        $this->lockRegistro = $this->proLibRegistro->lockRegistro($rowid); // BLOCCO LA REGISTRO
        if (!$this->lockRegistro) {
            Out::msgStop("ERRORE", $this->proLibRegistro->getErrMessage());
            return false;
        }
        //$anaregistroarc_rec = $this->proLib->getAnaregistroarc($rowid, 'rowid');
        $anaregistroarc_rec = $this->proLibRegistro->GetAnaRegistriArc($rowid);
        $open_Info = 'Oggetto: ' . $anaregistroarc_rec['CODICE'] . " " . $anaregistroarc_rec['DESCRIZIONE'];
        $this->openRecord($this->PROT_DB, 'ANAREGISTRIARC', $open_Info);
        $this->Nascondi();
        Out::valori($anaregistroarc_rec, $this->nameForm . '_ANAREGISTRIARC');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divDet);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_ANAREGISTRIARC[DESCRIZIONE]');
        if ($anaregistroarc_rec['TIPOPROGRESSIVO'] == 'ASSOLUTO') {
            Out::show($this->nameForm . '_ANAREGISTRIARC[PROGRESSIVO]_field');
        } else {
            Out::hide($this->nameForm . '_ANAREGISTRIARC[PROGRESSIVO]_field');
        }
        if ($anaregistroarc_rec['TIPOPROGRESSIVO'] == 'ANNUALE') {
            //$where = "WHERE CODICE =" . $anaregistroarc_rec['CODICE'];
            $this->CreaSqldettaglio($anaregistroarc_rec['ROW_ID']);
            Out::show($this->nameForm . '_divDettaglioRegistro');
        } else {
            Out::hide($this->nameForm . '_divDettaglioRegistro');
        }
        TableView::disableEvents($this->gridAnaregistroarc);
        Out::attributo($this->nameForm . '_ANAREGISTRIARC[CODICE]', "readonly", '0');
    }

    public function DettaglioRegistro($rowid = '', $tipo = '') {
        if ($rowid) {
            $anaregistro_rec = $this->proLib->getProgregistro($rowid, 'rowid');
        }
        if ($anaregistro_rec['FLAG_CHIUSO'] == 0) {
            $check = '';
        } else {
            $check = 'checked';
        }
        if ($tipo == 'Edit') {
            $readonly = 'readonly';
        }
        $this->progregistro = $anaregistro_rec['ROW_ID'];
        $valori = array();
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Anno '),
            'id' => $this->nameForm . '_Annoregistro',
            'name' => $this->nameForm . '_Annoregistro',
            'value' => $anaregistro_rec['ANNO'],
            'type' => 'text',
            'width' => '20',
            'size' => '6',
            $readonly => $readonly,
            'maxlength' => '4');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Ultimo Progressivo '),
            'id' => $this->nameForm . '_Progressivoregistro',
            'name' => $this->nameForm . '_Progressivoregistro',
            'value' => $anaregistro_rec['PROGRESSIVO'],
            'type' => 'text',
            'width' => '20',
            'size' => '15',
            'maxlength' => '11');
        $valori[] = array(
            'label' => array('style' => 'width:150px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Flag di chiusura '),
            'id' => $this->nameForm . '_Flagregistro',
            'name' => $this->nameForm . '_Flagregistro',
            $check => $check,
            'type' => 'checkbox',
            'class' => 'ita-check-box',
        );

        Out::msgInput(
                "Progressivi Annuali", $valori, array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiornaRegistro', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                ), $this->nameForm
        );
    }

    public function Aggiorna($campi) {

        $progregistro_rec = $this->proLib->getProgregistro($this->progregistro, 'rowid');
        if ($progregistro_rec) {
            $this->updateRecord($this->PROT_DB, 'ANAREGISTRIPROG', $campi, '', 'ROW_ID', false);
        } else {
            $campi['ROWID_ANAREGISTRO'] = $this->formData[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID'];
            $this->insertRecord($this->PROT_DB, 'ANAREGISTRIPROG', $campi, '', 'ROW_ID', false);
        }
        $this->CreaSqldettaglio($this->formData[$this->nameForm . '_ANAREGISTRIARC']['ROW_ID']);
        unset($this->progregistro);
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

    private function unlockregistro($retLock) {
        if (empty($retLock)) {
            return false;
        }
        $unlockregistro = $this->proLibRegistro->unlockRegistro($retLock); // SBLOCCO LA REGISTRO ALL'USCITA
        if (!$unlockregistro) {
            Out::msgStop("ERRORE", $this->proLibRegistro->getErrMessage());
            return false;
        }
        unset($this->lockRegistro);
        return true;
    }

}

?>
