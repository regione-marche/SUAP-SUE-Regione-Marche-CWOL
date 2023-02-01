<?php

/**
 *
 * DOCUMENTI BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    Documenti
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function docDocumenti() {
    $docDocumenti = new docDocumenti();
    $docDocumenti->parseEvent();
    return;
}

class docDocumenti extends itaModel {

    public $arrTipi = array(
        "XHTML" => "XHTML",
        "MSWORDXML" => "MS WORD XML",
        "MSWORDHTML" => "MS WORD HTML",
        "DOCX" => "DOCX",
        "RTF" => "RTF",
        "TXT" => "TXT",
        "JREPORT" => "JASPER REPORT",
        "PDF" => "PDF"
    );
    public $arrSuffix = array(
        "XHTML" => "xhtml",
        "MSWORDXML" => "doc",
        "MSWORDHTML" => "htm",
        "DOCX" => "docx",
        "RTF" => "rtf",
        "TXT" => "txt",
        "PDF" => "pdf",
    );
    public $ITALWEB;
    public $docLib;
    public $utiEnte;
    public $nameForm = "docDocumenti";
    public $divGes = "docDocumenti_divGestione";
    public $divRis = "docDocumenti_divRisultato";
    public $divRic = "docDocumenti_divRicerca";
    public $gridDocumenti = "docDocumenti_gridDocumenti";
    public $aggiornaFile = false;
    public $classificazione;
    public $FixedFields;
    public $TipoAperturaDocumento;
    private $dictionaryLegendFixed = array();
    private $lockedDoc;
    private $dictionaryLegend;
    public $versionePrecedente;
    public $allegato;

    function __construct() {
        parent::__construct();
        $this->aggiornaFile = App::$utente->getKey($this->nameForm . '_aggiornaFile');
        $this->classificazione = App::$utente->getKey($this->nameForm . '_classificazione');
        $this->FixedFields = App::$utente->getKey($this->nameForm . '_FixedFields');
        $this->dictionaryLegendFixed = App::$utente->getKey($this->nameForm . '_dictionaryLegendFixed');
        $this->TipoAperturaDocumento = App::$utente->getKey($this->nameForm . '_TipoAperturaDocumento');
        $this->lockedDoc = App::$utente->getKey($this->nameForm . '_lockedDoc');
        $this->dictionaryLegend = App::$utente->getKey($this->nameForm . '_dictionaryLegend');
        $this->versionePrecedente = App::$utente->getKey($this->nameForm . '_versionePrecedente');
        $this->allegato = App::$utente->getKey($this->nameForm . '_allegato');
        // Apro il DB
        try {
            $this->docLib = new docLib();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB = $this->docLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_aggiornaFile', $this->aggiornaFile);
            App::$utente->setKey($this->nameForm . '_classificazione', $this->classificazione);
            App::$utente->setKey($this->nameForm . '_FixedFields', $this->FixedFields);
            App::$utente->setKey($this->nameForm . '_dictionaryLegendFixed', $this->dictionaryLegendFixed);
            App::$utente->setKey($this->nameForm . '_TipoAperturaDocumento', $this->TipoAperturaDocumento);
            App::$utente->setKey($this->nameForm . '_lockedDoc', $this->lockedDoc);
            App::$utente->setKey($this->nameForm . '_dictionaryLegend', $this->dictionaryLegend);
            App::$utente->setKey($this->nameForm . '_versionePrecedente', $this->versionePrecedente);
            App::$utente->setKey($this->nameForm . '_allegato', $this->allegato);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->dictionaryLegendFixed = array();
                $this->TipoAperturaDocumento = '';
                $this->lockedDoc = false;
                $this->dictionaryLegend = false;
                $docPath = $this->docLib->setDirectory();
                $this->classificazione = $_POST['classificazione'];
                $FixedField = $_POST['FixedFields'];
                $this->FixedFields = $FixedField;
                //
                Out::html($this->nameForm . '_divOperatoreTipo', '');
                Out::hide($this->nameForm . '_divOperatoreTipo');
                if ($this->docLib->getParamTipoOperatore() == "MASTER") {
                    Out::show($this->nameForm . '_divOperatoreTipo');
                    Out::html($this->nameForm . '_divOperatoreTipo', "Modalità SVILUPPATORE");
                }
                //
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'OpenFixField':
                $this->TipoAperturaDocumento = $_POST['TipoAperturaDocumento'];
                $FixedField = $_POST['FixedFields'];
                $this->FixedFields = $FixedField;
                $this->classificazione = $FixedField['CLASSIFICAZIONE'];
                $this->lockedDoc = false;
                $this->dictionaryLegend = $this->FixedFields['DICTIONARYLEGEND'];
                $this->CreaCombo();
                break;
            case 'OpenLockedDoc':
                $this->TipoAperturaDocumento = $_POST['TipoAperturaDocumento'];
                $FixedField = $_POST['FixedFields'];
                $this->FixedFields = $FixedField;
                $this->classificazione = $FixedField['CLASSIFICAZIONE'];
                $this->lockedDoc = true;
                $this->dictionaryLegend = $this->FixedFields['DICTIONARYLEGEND'];
                $this->CreaCombo();
                $Doc_documenti_rec = $this->docLib->getDocumenti($this->FixedFields['CODICE'], 'codice');
                if ($Doc_documenti_rec) {
                    $this->Dettaglio($Doc_documenti_rec['ROWID']);
                } else {
                    $this->NuovoDocumento();
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
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
                $ita_grid01 = new TableView($this->gridDocumenti, array(
                    'sqlDB' => $this->ITALWEB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('OGGETTO');
                $ita_grid01->exportXLS('', 'documenti.xls');
                break;
            case 'printTableToHTML':
                $tableSortOrder = $_POST['sidx'] . " " . $_POST['sord'];
                $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql($tableSortOrder), "Ente" => $parametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALWEB, 'docDocumenti', $parameters);
                break;
            case 'onClickTablePager':
                TableView::clearGrid($_POST['id']);
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $ita_grid01->getDataArray();
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_classificazione':
                        $this->creaComboFunzione($_POST[$this->nameForm . "_classificazione"], 'funzione');
                        break;
                    case $this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]':
                        if ($_POST[$this->nameForm . "_DOC_DOCUMENTI"]['CLASSIFICAZIONE'] == "COMMERCIO") {
                            $this->showCampi();
                        } else {
                            $this->HideCampi();
                        }
                        $this->creaComboFunzione($_POST[$this->nameForm . "_DOC_DOCUMENTI"]['CLASSIFICAZIONE']);
                        break;
                    case $this->nameForm . '_DOC_DOCUMENTI[TIPO]':
                        $this->setTipo($_POST[$this->nameForm . '_DOC_DOCUMENTI']['TIPO']);
                        break;
                    case $this->nameForm . '_modelloXhtml':
                        $codiceModello = $_POST[$this->nameForm . "_modelloXhtml"];
                        if ($codiceModello == "PERSONALIZZATO") {
                            $this->unlockLayout(true);
                        } else {
                            $this->unlockLayout();
                            $this->caricaLayout($codiceModello);
                            $this->lockLayout();
                        }
                        break;
                    case $this->nameForm . '_classificazione':
                        if ($codiceModello == "PERSONALIZZATO") {
                            $this->unlockLayout(true);
                        } else {
                            $this->unlockLayout();
                            $this->caricaLayout($codiceModello);
                            $this->lockLayout();
                        }
                        break;

                    case $this->nameForm . '_DOC_DOCUMENTI[MAPPATURA]':
                        if ($_POST[$this->nameForm . '_DOC_DOCUMENTI']['MAPPATURA']) {
                            Out::show($this->nameForm . '_AnteprimaMap');
                        } else {
                            Out::hide($this->nameForm . '_AnteprimaMap');
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Preview':
                        $documenti_rec = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');
                        if ($documenti_rec['TIPO'] == "JREPORT") {
                            include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                            $itaJR = new itaJasperReport();
                            $parameters = array("CODICE" => $documenti_rec['CODICE']);
                            $itaJR->runSQLReportPDF($this->docLib->getITALWEB(), $documenti_rec['CONTENT'], $parameters);
                        } else {
                            $this->previewXHTML($documenti_rec);
                        }
                        break;

                    case $this->nameForm . '_ApriVisualizza':
                        $documenti_rec = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');
                        switch ($documenti_rec['TIPO']) {
                            case "MSWORDHTML":
                            case "DOCX":

                                /* @var $docParametri docParametri */
                                $devLib = new devLib();
                                $valueOO = $devLib->getEnv_config('SEGPARAMVARI', 'codice', 'SEG_OPENOO_DOCX', false);
                                $valueOO = $valueOO['CONFIG'];
                                if ($valueOO == 1) {
                                    $docPath = $this->docLib->setDirectory();
                                    $filePath = $docPath . $documenti_rec['URI'];
                                    $this->docLib->openOODocument($documenti_rec['ROWID'], $filePath, $documenti_rec['OGGETTO'], $documenti_rec['CLASSIFICAZIONE']);
                                } else {
                                    $moddir = $devLib->getEnv_config('SEGPARAMVARI', 'codice', 'SEG_MODDIR_DOCX', false);
                                    $moddir = $moddir['CONFIG'];
                                    if (!$moddir || ($this->FixedFields['SCARICA_DOCX'] && $documenti_rec['TIPO'] == 'DOCX')) {
                                        $docPath = $this->docLib->setDirectory();
                                        $NomeFile = $documenti_rec['CODICE'] . '.' . pathinfo($documenti_rec['URI'], PATHINFO_EXTENSION);
                                        Out::openDocument(utiDownload::getUrl($NomeFile, $docPath . $documenti_rec['URI']));
                                        //faccio la copia del documento a storico
                                        $detFile = $docPath . $documenti_rec['NUMREV'] . "_" . $documenti_rec['URI'];
                                        if (!$this->docLib->CopiaTesto($docPath . $documenti_rec['URI'], $detFile, 'word-html')) {
                                            Out::msgStop("Errore", $this->docLib->getErrMessage());
                                            break 2;
                                        }
                                        break;
                                    }
                                    $docPath = Config::getPath('general.fileEnte_share') . '\\ente' . App::$utente->getKey('ditta') . '\\documenti';
                                    $this->docLib->CaricaApplet($docPath, $documenti_rec['URI']);
                                    //faccio la copia del documento a storico
                                    $docPath = $this->docLib->setDirectory();
                                    $detFile = $docPath . $documenti_rec['NUMREV'] . "_" . $documenti_rec['URI'];
                                    if (!$this->docLib->CopiaTesto($docPath . $documenti_rec['URI'], $detFile, 'word-html')) {
                                        Out::msgStop("Errore", $this->docLib->getErrMessage());
                                        break 2;
                                    }
                                }
                                break;
                        }
                        break;

                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridDocumenti, array(
                            'sqlDB' => $this->ITALWEB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST[$this->gridDocumenti]['gridParam']['page'] ?: 1);
                        $ita_grid01->setPageRows($_POST[$this->gridDocumenti]['gridParam']['rowNum'] ?: 50);
                        $ita_grid01->setSortIndex('CODICE');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $ita_grid01->getDataArray();
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            Out::clearFields($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Export');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridDocumenti);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        Out::clearFields($this->nameForm, $this->divGes);
                        TableView::enableEvents($this->gridDocumenti);
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->NuovoDocumento();
                        break;

                    case $this->nameForm . '_Copia':
                        $this->allegato = "";
                        $model = 'utiRicDiag';
                        $gridOptions = array(
                            "Caption" => "Documenti",
                            "width" => '600',
                            "height" => '500',
                            "rowNum" => '20',
                            "rowList" => '[]',
                            "filterToolbar" => 'true',
                            "colNames" => array(
                                "Codice",
                                "Descrizione",
                                "Tipo"
                            ),
                            "colModel" => array(
                                array("name" => 'CODICE', "width" => 150),
                                array("name" => 'OGGETTO', "width" => 300),
                                array("name" => 'TIPO', "width" => 70, "search" => "true", "stype" => "'select'", 'editoptions' => "{ value: {} }"),
                            ),
                            "dataSource" => array(
                                'sqlDB' => 'ITALWEB',
                                'sqlQuery' => $this->CreaSql()
                            )
                        );
                        $filterName = array('CODICE', 'OGGETTO', 'TIPO');
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['gridOptions'] = $gridOptions;
                        $_POST['filterName'] = $filterName;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnCopiaDocumento';
                        $_POST['returnKey'] = 'retKey';
                        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
                        $utiRic = itaModel::getInstance($model);
                        $utiRic->parseEvent();

                        /*
                         * Imposto la select con i tipi di docDocumenti
                         */
                        TableView::tableSetFilterSelect($utiRic->nameGrid, "TIPO", 1, "", 0, "");
                        foreach ($this->arrTipi as $key => $value) {
                            TableView::tableSetFilterSelect($utiRic->nameGrid, "TIPO", 1, $key, 0, $value);
                        }
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $ret = $this->docLib->aggiungi(
                                $_POST[$this->nameForm . '_DOC_DOCUMENTI'], $_POST[$this->nameForm . '_varHtml'], $_POST[$this->nameForm . '_varFile'], $this->aggiornaFile, $this, false, $this->allegato);

                        switch ($ret['COD_ERR']) {
                            case 0:
                                $this->Dettaglio($ret['DATA']['ROWID']);
                                break;
                            case 1:
                                Out::msgStop($ret['MSG_LBL'], $ret['MSG_ERR']);
                                break;
                            case 2:
                                Out::msgInfo($ret['MSG_LBL'], $ret['MSG_ERR']);
                                Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[CODICE]');
                                break;
                        }
                        break;

                    case $this->nameForm . '_ConfermaAggiornaVersione':
                        $this->versionePrecedente = "";
                    case $this->nameForm . '_ConfermaValidazione':
                        $doc_documenti_r = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
                        $doc_info = $doc_documenti_r['ROWID'] . '/' . $doc_documenti_r['CODICE'] . '/' . $doc_documenti_r['CLASSIFICAZIONE'];
                        $this->insertAudit($this->ITALWEB, 'DOC_DOCUMENTI', "Salvataggio documento con errori in validazione: $doc_info", $doc_documenti_r['ROWID'], eqAudit::OP_GENERIC_WARNING);

                        $skipValidazione = true;

                    case $this->nameForm . '_Aggiorna':
                        $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];

                        if (!isset($skipValidazione) || $skipValidazione === false) {
                            if ($documenti_rec['TIPO'] == 'XHTML') {
                                if (($errval = $this->validateXHTMLInput()) !== true) {
                                    Out::msgQuestion("Errore di validazione", "Sono stati rilevati errori nella validazione del documento.<br><br>$errval<br><br>Proseguire con il salvataggio?", array(
                                        'F8-Annulla' => array(
                                            'id' => $this->nameForm . '_AnnullaValidazione',
                                            'model' => $this->nameForm,
                                            'shortCut' => "f8"
                                        ),
                                        'F5-Conferma' => array(
                                            'id' => $this->nameForm . '_ConfermaValidazione',
                                            'model' => $this->nameForm,
                                            'shortCut' => "f5"
                                    )));
                                    break;
                                }
                            }
                        }

                        if (isset($this->versionePrecedente) && $this->versionePrecedente != '') {
                            Out::msgQuestion("Attenzione", "È stata caricata la versione {$this->versionePrecedente}. Aggiornando verrà creata una nuova versione con le modifiche apportate.<br/>Continuare col salvataggio?", array(
                                'NO' => array(
                                    'id' => $this->nameForm . '_AnnullaAggiornaversione',
                                    'model' => $this->nameForm,
                                    'shortCut' => "f8"
                                ),
                                'SI' => array(
                                    'id' => $this->nameForm . '_ConfermaAggiornaVersione',
                                    'model' => $this->nameForm,
                                    'shortCut' => "f5"
                            )));
                            break;
                        }

                        $ret = $this->docLib->aggiorna(
                                $_POST[$this->nameForm . '_DOC_DOCUMENTI'], $_POST[$this->nameForm . '_varHtml'], $_POST[$this->nameForm . '_varFile'], $this->aggiornaFile, $this);

                        switch ($ret['COD_ERR']) {
                            case 0:
                                $this->Dettaglio($ret['DATA']['ROWID']);
                                break;
                            case 1:
                                Out::msgStop($ret['MSG_LBL'], $ret['MSG_ERR']);
                                break;
                        }
                        if ($this->returnModel) {
                            $this->returnToParent();
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
                        $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
                        if (!$this->docLib->deleteDocumenti($documenti_rec['ROWID'])) {
                            Out::msgStop("Errore in cancellazione", $this->docLib->getErrMessage());
                        }
                        //cancellazione dello storico
                        $sql = "SELECT * FROM DOC_STORICO WHERE CLASSIFICAZIONE = '" . addslashes($documenti_rec['CLASSIFICAZIONE']) . "' AND CODICE = '" . addslashes($documenti_rec['CODICE']) . "'";
                        $tab_storico = ItaDB::DBSQLSelect($this->ITALWEB, $sql, true);
                        foreach ($tab_storico as $storico_rec) {
                            if (!$this->docLib->deleteStoricoDocumenti($storico_rec['ROWID'])) {
                                Out::msgStop("Errore in cancellazione", $this->docLib->getErrMessage());
                                break 2;
                            }
                        }
                        $this->OpenRicerca();
//                        try {
//                            $delete_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
//                            if ($this->deleteRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec['ROWID'], $delete_Info)) {
//                                $this->OpenRicerca();
//                            }
//                        } catch (Exception $e) {
//                            Out::msgStop("Errore in Cancellazione su DOCUMENTI BASE", $e->getMessage());
//                        }
                        break;

                    case $this->nameForm . '_varFile_butt':
                        if ($_POST[$this->nameForm . "_DOC_DOCUMENTI"]['TIPO'] == "JREPORT") {
                            Out::msgInput(
                                    "Scegli il report", array(
                                array(
                                    'label' => array('value' => "Report"),
                                    'id' => $this->nameForm . '_NomeReport',
                                    'name' => $this->nameForm . '_NomeReport',
                                    'type' => 'text',
                                    'size' => '20',
                                )
                                    ), array(
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaReport', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    ), $this->nameForm
                            );
                        } else {
                            $this->appoggioPost = $_POST;
                            $model = 'utiUploadDiag';
                            $_POST = Array();
                            $_POST['event'] = 'openform';
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnEvent'] = "returnUpload";
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_ConfermaReport':
                        Out::valore($this->nameForm . '_varFile', $_POST[$this->nameForm . '_NomeReport']);
                        break;
                    case $this->nameForm . '_VediTesto':
                        if ($_POST[$this->nameForm . '_varFile'] != '') {
                            if ($this->aggiornaFile == true) {
                                $nomeFile = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['CODICE'] . '.' . $this->arrSuffix[$_POST[$this->nameForm . '_DOC_DOCUMENTI']['TIPO']];
                                $file = $_POST[$this->nameForm . '_varFile'];
                            } else {
                                $docPath = $this->docLib->setDirectory();
                                $nomeFile = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['CONTENT'] . '.' . $this->arrSuffix[$_POST[$this->nameForm . '_DOC_DOCUMENTI']['TIPO']];
                                $file = $docPath . $nomeFile;
                            }
//                            Out::msgInfo("", $file);
                            if (file_exists($file)) {
                                Out::openDocument(utiDownload::getUrl($nomeFile, $file));
                            }
                        }
                        break;

                    case $this->nameForm . '_Sostituisci':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadDocumento";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_ApriVars':
                        $Classe = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['CLASSIFICAZIONE'];
                        $Funzione = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['FUNZIONE'];
                        $this->embedVars($Classe, $Funzione);
                        break;

                    case $this->nameForm . '_VersioniPrecedenti':
                        $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
//                        $sqlStorico = "SELECT * FROM DOC_STORICO WHERE CODICE = '" . addslashes($documenti_rec['CODICE']) . "' AND CLASSIFICAZIONE = '" . addslashes($documenti_rec['CLASSIFICAZIONE']) . "'";
//                        $storico_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sqlStorico, false);
                        include_once (ITA_BASE_PATH . '/apps/Documenti/docRic.class.php');
                        docRic::docRicDocumentiStorico($this->nameForm, $documenti_rec['CLASSIFICAZIONE'], $documenti_rec['CODICE']);
                        break;

                    case $this->nameForm . '_AnteprimaMap':
                        $documenti_rec = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');

                        $tempFilename = md5(microtime() . $documenti_rec['URI']) . '.' . pathinfo($documenti_rec['URI'], PATHINFO_EXTENSION);
                        $previewFile = itaLib::getAppsTempPath() . '/' . $tempFilename;

                        if (!$this->docLib->getDocumentoMappato($documenti_rec['CODICE'], $previewFile, $_POST[$this->nameForm . '_DOC_DOCUMENTI']['MAPPATURA'])) {
                            Out::msgStop('Errore', $this->docLib->getErrMessage());
                            break;
                        }

                        $NomeFile = $documenti_rec['CODICE'] . '.' . pathinfo($documenti_rec['URI'], PATHINFO_EXTENSION);
                        Out::openDocument(utiDownload::getUrl($NomeFile, $previewFile));
                        break;

                    case $this->nameForm . '_Import':
                        $this->importDocs();
                        break;

                    case $this->nameForm . '_Export':
                        $this->exportDocs();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'embedVars':
                $Classe = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['CLASSIFICAZIONE'];
                $Funzione = $_POST[$this->nameForm . '_DOC_DOCUMENTI']['FUNZIONE'];
                $this->embedVars($Classe, $Funzione);
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_codice':
                        $codice = $_POST[$this->nameForm . '_codice'];
                        if ($codice != '') {
                            $documenti_rec = $this->docLib->getDocumenti($codice);
                            if ($documenti_rec) {
                                $this->Dettaglio($documenti_rec['ROWID']);
                            }
                        }
                        break;
                }
                break;

            case 'returnUpload':
                $sourceFile = $_POST['uploadedFile'];
                if (file_exists($sourceFile)) {
                    if ($this->formData[$this->nameForm . '_DOC_DOCUMENTI']['TIPO'] == "DOCX") {
                        //se il tipo è DOCX verifico l'estensione del file caricato
                        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
                        if (strtolower($ext) != "docx") {
                            Out::msgStop("Attenzione!", "L'estensione del file selezionato non corrisponde ad un .docx");
                            break;
                        }
                    }
                    $this->aggiornaFile = true;
                    Out::valore($this->nameForm . '_varFile', $sourceFile);
                } else {
                    Out::msgStop("Errore", "Errore nel caricamento file.");
                }
                break;

            case 'returnUploadDocumento':
                $sourceFile = $_POST['uploadedFile'];
                if (file_exists($sourceFile)) {
                    if ($this->formData[$this->nameForm . '_DOC_DOCUMENTI']['TIPO'] == 'DOCX') {
                        /*
                         * Se il tipo è DOCX verifico l'estensione del file caricato
                         */

                        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
                        if (strtolower($ext) != 'docx') {
                            Out::msgStop("Attenzione!", "L'estensione del file selezionato non corrisponde ad un .docx");
                            break;
                        }

                        $docPath = $this->docLib->setDirectory();
                        $documenti_rec = $this->docLib->getDocumenti($this->formData[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');

                        if (!rename($sourceFile, $docPath . $documenti_rec['URI'])) {
                            Out::msgStop("Errore", "Errore nel caricamento file. (1)");
                        }
                        chmod($docPath . $documenti_rec['URI'], 0777);
                        Out::msgInfo("Sostituzione", "File caricato correttamente.");
                    }
                    if ($this->formData[$this->nameForm . '_DOC_DOCUMENTI']['TIPO'] == 'PDF') {
                        /*
                         * Se il tipo è PDF verifico l'estensione del file caricato
                         */

                        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
                        if (strtolower($ext) != 'pdf') {
                            Out::msgStop("Attenzione!", "L'estensione del file selezionato non corrisponde ad un .pdf");
                            break;
                        }

                        $docPath = $this->docLib->setDirectory();
                        $documenti_rec = $this->docLib->getDocumenti($this->formData[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');

                        if (!rename($sourceFile, $docPath . $documenti_rec['URI'])) {
                            Out::msgStop("Errore", "Errore nel caricamento file. (1)");
                        }
                        chmod($docPath . $documenti_rec['URI'], 0777);
                        Out::msgInfo("Sostituzione", "File caricato correttamente.");
                    }
                } else {
                    Out::msgStop("Errore", "Errore nel caricamento file.");
                }
                break;

            case 'returnCopiaDocumento':
                $documenti_rec = $this->docLib->getDocumenti($_POST['retKey'], 'rowid');

                /*
                 * Se operatore uguale a vuoto o CLIENTE, controllo il codice
                 */
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[CODICE]', "");
                Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', "readonly", '1');
                //
                $tipoOperatore = $this->docLib->getParamTipoOperatore();
                if ($tipoOperatore != 'MASTER') {
                    $retCodice = $this->docLib->checkCodice($documenti_rec['CODICE']);
                    if ($retCodice['Status'] == false) {
                        Out::valore($this->nameForm . '_DOC_DOCUMENTI[CODICE]', "cust_" . $documenti_rec['CODICE']);
                        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', "readonly", '0');
                    }
                }

                /*
                 * Se esiste, mi salvo l'allegato fisico
                 */
                $pathDocumenti = $this->docLib->setDirectory();
                if (file_exists($pathDocumenti . $documenti_rec['URI'])) {
                    $this->allegato = $documenti_rec['URI'];
                }


                $this->creaComboFunzione($documenti_rec['CLASSIFICAZIONE']);
                //non importo il codice, copia di tutti gli altri dati
                unset($documenti_rec['CODICE']);
                unset($documenti_rec['ROWID']);
                Out::valori($documenti_rec, $this->nameForm . '_DOC_DOCUMENTI');
                $this->setTipo($documenti_rec['TIPO'], $documenti_rec);
                // Resetto i valori non necessari
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[ROWID]', '');
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[NUMREV]', '');
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[DATAREV]', '');
                break;

            case 'returnDocumentiStorico':
                if (!$_POST['retKey']) {
                    break;
                }
                $sql = "SELECT * FROM DOC_STORICO WHERE ROWID = " . $_POST['retKey'];
                $documenti_storico = ItaDB::DBSQLSelect($this->ITALWEB, $sql, false);
                if (!$documenti_storico) {
                    break;
                }
                $this->versionePrecedente = $documenti_storico['NUMREV'];
                switch ($documenti_storico['TIPO']) {
                    case 'XHTML':
                        Out::valore($this->nameForm . '_varHtml', $documenti_storico['CONTENT']);
                        break;
                    case "JREPORT":
                        //
                        break;
                    case "MSWORDXML":
                    case "RTF":
                    case "ODT":
                    case "XML":
                    case "TXT":
                    case "PDF":
                        $docPath = $this->docLib->setDirectory();
                        $nomeFile = $documenti_storico['CONTENT'] . '.' . $this->arrSuffix[$this->formData[$this->nameForm . '_DOC_DOCUMENTI']['TIPO']];
                        $file = $docPath . $documenti_storico['NUMREV'] . "_" . $nomeFile;
//                        Out::msgInfo("", $file);
                        if (file_exists($file)) {
                            Out::openDocument(utiDownload::getUrl($nomeFile, $file));
                        }
                        break;
                    case "MSWORDHTML":
                    case "DOCX":
                        $docPath = $this->docLib->setDirectory();
                        $nomeFile = $documenti_storico['CONTENT'] . '.' . $this->arrSuffix[$this->formData[$this->nameForm . '_DOC_DOCUMENTI']['TIPO']];
                        $file = $docPath . $documenti_storico['NUMREV'] . "_" . $nomeFile;
//                        Out::msgInfo("", $file);
                        if (file_exists($file)) {
                            Out::openDocument(utiDownload::getUrl($nomeFile, $file));
                        }

                        break;
                }
                Out::show($this->nameForm . "_divVersioneTesto");
                Out::html($this->nameForm . "_divVersioneTesto", "TESTO IN USO: VERSIONE N. $this->versionePrecedente");
                Out::msgInfo("Versione Precedente", "È stato caricato il testo della versione {$this->versionePrecedente}. Prestare attenzione.");
                break;

            case 'returnUploadZipDocs':
                $this->confirmImportDocs($_POST['uploadedFile']);
                break;

            case 'returnSelezioneDocumenti':
                $this->executeImportDocs($_POST['selectedDocs']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_aggiornaFile');
        App::$utente->removeKey($this->nameForm . '_classificazione');
        App::$utente->removeKey($this->nameForm . '_FixedFields');
        App::$utente->removeKey($this->nameForm . '_dictionaryLegendFixed');
        App::$utente->removeKey($this->nameForm . '_TipoAperturaDocumento');
        App::$utente->removeKey($this->nameForm . '_lockedDoc');
        App::$utente->removeKey($this->nameForm . '_dictionaryLegend');
        App::$utente->removeKey($this->nameForm . '_versionePrecedente');
        App::$utente->removeKey($this->nameForm . '_allegato');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            $_POST = array();
            $returnModel = $this->returnModel;
            $_POST['id'] = $this->returnId;
            $_POST['event'] = $this->returnEvent;
            $_POST['model'] = $returnModel;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
            $returnModel();
        }
        if ($close)
            $this->close();
    }

    public function OpenRicerca() {
        $this->versionePrecedente = "";
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        // Out::hide($this->nameForm . '_funzione_field');
        $this->AzzeraVariabili();
        $this->Nascondi();
        if ($this->docLib->getParamTipoOperatore() !== "MASTER") {
            Out::show($this->nameForm . '_Import');
        }
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        if ($valueTipoOeratore == "MASTER") {
            Out::hide($this->nameForm . '_Import');
        } else {
            Out::show($this->nameForm . '_Import');
        }
        Out::show($this->nameForm);
        $this->setTipo('');
        Out::setFocus('', $this->nameForm . '_codice');
        Out::valore($this->nameForm . '_tipo', '');
        if ($this->classificazione != '') {
            Out::hide($this->nameForm . '_divClass');
            Out::valore($this->nameForm . '_classificazione', $this->classificazione);
        }
    }

    public function setDictionaryLegendFixed($DictionaryLegend) {
        $this->dictionaryLegendFixed = $DictionaryLegend;
        return $this->dictionaryLegendFixed;
    }

    public function getDictionaryLegendFixed($DictionaryLegend) {
        return $this->dictionaryLegendFixed;
    }

    function AzzeraVariabili() {
        $this->aggiornaFile = false;
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDocumenti);
        TableView::clearGrid($this->gridDocumenti);
        $this->allegato = "";
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Export');
        Out::hide($this->nameForm . '_Import');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Copia');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Preview');
        Out::hide($this->nameForm . '_ApriVisualizza');
        Out::hide($this->nameForm . '_Sostituisci');
        Out::hide($this->nameForm . '_ApriVars');
        Out::hide($this->nameForm . '_divHtml');
        Out::hide($this->nameForm . '_divFile');
        Out::hide($this->nameForm . '_CercaVar1');
        Out::hide($this->nameForm . '_VersioniPrecedenti');
        Out::hide($this->nameForm . '_divVersioneTesto');
    }

    public function CreaSql($tableSortOrder = '') {
        //
        // Categorie non gestibili se non richiamate esplicitamente da fixed fields
        //
        $protected_where = '';
        if ($this->FixedFields['CLASSIFICAZIONE'] == '') {
            $protected_where = $this->docLib->getProtectedCatsWhere();
        }

        $sql = "SELECT * FROM DOC_DOCUMENTI WHERE 1=1 $protected_where";
        if ($_POST[$this->nameForm . '_codice'] != "") {
            $codice = $_POST[$this->nameForm . '_codice'];
            $sql .= " AND CODICE = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_oggetto'] != "") {
            $sql .= " AND LOWER(OGGETTO) LIKE '%" . strtolower(addslashes($_POST[$this->nameForm . '_oggetto'])) . "%'";
        }
        if ($_POST[$this->nameForm . '_tipo'] != "") {
            $codice = $_POST[$this->nameForm . '_tipo'];
            $sql .= " AND TIPO = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_caratteristica'] != "") {
            $sql .= " AND CARATTERISTICA = '" . $_POST[$this->nameForm . '_caratteristica'] . "'";
        }

        if ($this->classificazione) {
            $codice_cl = $this->classificazione;
        } else {
            if ($_POST[$this->nameForm . '_classificazione'] != "") {
                $codice_cl = $_POST[$this->nameForm . '_classificazione'];
            }
        }

        if ($codice_cl) {
            $sql .= " AND CLASSIFICAZIONE = '" . $codice_cl . "'";
        }

        $codici_fn = array();
        if ($this->FixedFields['FUNZIONE']) {
            if (is_array($this->FixedFields['FUNZIONE'])) {
                foreach ($this->FixedFields['FUNZIONE'] as $key => $codice_fn) {
                    $codici_fn[] = "FUNZIONE = '" . $codice_fn . "'";
                }
            } else {
                $codici_fn[] = "FUNZIONE = '" . $this->FixedFields['FUNZIONE'] . "'";
            }
        } else if ($_POST[$this->nameForm . '_funzione'] != "") {
            $codici_fn[] = "FUNZIONE = '" . $_POST[$this->nameForm . '_funzione'] . "'";
        }

        if ($codici_fn) {
            $sql .= " AND (" . implode(" OR ", $codici_fn) . ")";
        }



        if ($_POST['_search'] == true) {
            if ($_POST['CLASSIFICAZIONE']) {
                $sql .= " AND UPPER(CLASSIFICAZIONE) LIKE UPPER('%" . addslashes($_POST['CLASSIFICAZIONE']) . "%')";
            }

            if ($_POST['FUNZIONE']) {
                $sql .= " AND UPPER(FUNZIONE) LIKE UPPER('%" . addslashes($_POST['FUNZIONE']) . "%')";
            }

            if ($_POST['CODICE']) {
                $sql .= " AND UPPER(CODICE) LIKE UPPER('%" . addslashes($_POST['CODICE']) . "%')";
            }

            if ($_POST['OGGETTO']) {
                $sql .= " AND UPPER(OGGETTO) LIKE UPPER('%" . addslashes($_POST['OGGETTO']) . "%')";
            }

            if ($_POST['TIPO']) {
                $sql .= " AND UPPER(TIPO) LIKE UPPER('%" . addslashes($_POST['TIPO']) . "%')";
            }
        }

//        if ($codice_fn) {
//            $sql .= " AND FUNZIONE = '" . $codice_fn . "'";
//        }
        if ($tableSortOrder) {
            $sql .= ' ORDER BY ' . $tableSortOrder;
        }
        return $sql;
    }

    public function Dettaglio($indice) {
        $this->versionePrecedente = "";
        $documenti_rec = $this->docLib->getDocumenti($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
        $this->openRecord($this->ITALWEB, 'DOC_DOCUMENTI', $open_Info);
        $this->Nascondi();
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);

        $this->creaComboFunzione($documenti_rec['CLASSIFICAZIONE']);
        if ($this->FixedFields['FUNZIONE']) {
            $this->creaComboFunzioneCustom();
        }
        Out::valori($documenti_rec, $this->nameForm . '_DOC_DOCUMENTI');

        $this->showCampi();
        if ($documenti_rec['CLASSIFICAZIONE'] != "COMMERCIO") {
            $this->HideCampi();
        }
        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
        //Out::attributo($this->nameForm . '_DOC_DOCUMENTI[DATASCAD]', 'disabled', '1', 'disabled');
        //Out::disableField($this->nameForm . '_DOC_DOCUMENTI[DATASCAD]');
        Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[OGGETTO]');
        TableView::disableEvents($this->gridDocumenti);

        $this->setTipo($documenti_rec['TIPO'], $documenti_rec);
        $this->aggiornaFile = false;

        Out::html($this->nameForm . '_DOC_DOCUMENTI[TIPO]', '');
        Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, $documenti_rec['TIPO'], '0', $this->arrTipi[$documenti_rec['TIPO']]);

        if ($this->FixedFields) {
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_Torna');
            Out::show($this->nameForm . '_AltraRicerca');
            Out::show($this->nameForm . '_Export');
            //
            // Pulisco le combo
            //
            Out::html($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', '');
            // Out dei valori
            //Out::valore($this->nameForm . '_DOC_DOCUMENTI[CODICE]', $this->FixedFields['CODICE']);
            //Out::valore($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', $this->FixedFields['OGGETTO']);
            Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $this->FixedFields['CLASSIFICAZIONE'], '1', $this->FixedFields['CLASSIFICAZIONE']);
            //Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
            // Il Tipo lo pulisco e faccio l'out del valore solo se ce l'ho.
            if ($this->FixedFields['TIPO']) {
                Out::html($this->nameForm . '_DOC_DOCUMENTI[TIPO]', '');
                Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, $this->FixedFields['TIPO'], '1', $this->FixedFields['TIPO']);
                //Out::attributo($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 'readonly', '0');
            }
            // La funzione la pulisco e faccio l'out del valore solo se ce l'ho.
        } else {
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_Cancella');
            Out::show($this->nameForm . '_AltraRicerca');
            Out::show($this->nameForm . '_Export');
            Out::show($this->nameForm . '_Torna');

            if ($this->classificazione != '') {
                Out::show($this->nameForm . '_divClassificazione');
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', $this->classificazione);
            }
        }

        //visualizzo sempre il bottone per le versioni precedenti
        Out::show($this->nameForm . '_VersioniPrecedenti');

//        if ($documenti_rec['DATASCAD'] <= date('Ymd') && $documenti_rec['DATASCAD']) {
//            $this->ToggleVisualizzazioneScaduto(true);
//        } else {
//            $this->ToggleVisualizzazioneScaduto(false);
//        }
        $this->setLockDocFields();
    }

    private function ToggleVisualizzazioneScaduto($enable) {
        if ($enable == true) {
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', 'readonly', '0');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CARATTERISTICA]', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_modelloXhtml', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[SOCI]', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OBBLIGATORIO]', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_varHtml', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_varHtmlHeader', 'disabled', '0', 'disabled');
            Out::attributo($this->nameForm . '_varHtmlFooter', 'disabled', '0', 'disabled');
            Out::hide($this->nameForm . '_Aggiorna');
        } else if ($enable == false) {
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', 'readonly', '1');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 'disabled', '1');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CARATTERISTICA]', 'disabled', '1');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 'disabled', '1');
            Out::attributo($this->nameForm . '_modelloXhtml', 'disabled', '1');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[SOCI]', 'disabled', '1');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OBBLIGATORIO]', 'disabled', '1');
            Out::attributo($this->nameForm . '_varHtml', 'disabled', '1');
            Out::show($this->nameForm . '_Aggiorna');
        }
    }

    function HideCampi() {
        Out::hide($this->nameForm . "_DOC_DOCUMENTI[CARATTERISTICA]_field");
        Out::hide($this->nameForm . "_DOC_DOCUMENTI[SOCI]_field");
        Out::hide($this->nameForm . "_DOC_DOCUMENTI[OBBLIGATORIO]_field");
    }

    function showCampi() {
        Out::show($this->nameForm . "_DOC_DOCUMENTI[CARATTERISTICA]_field");
        Out::show($this->nameForm . "_DOC_DOCUMENTI[SOCI]_field");
        Out::show($this->nameForm . "_DOC_DOCUMENTI[OBBLIGATORIO]_field");
    }

    function creaComboFunzione($classificazione, $element = 'DOC_DOCUMENTI[FUNZIONE]') {
        $element_id = $this->nameForm . '_' . $element;
        switch ($classificazione) {
            case 'SERV_ECON' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, 'INCLUDE', '0', 'TESTI INCLUDE');
                Out::select($element_id, 1, 'FATTEN', '0', 'FATTURA ATTIVA');
                Out::select($element_id, 1, 'LIQATT', '0', 'ATTI LIQUIDAZIONE/RISCOSSIONE');
                Out::select($element_id, 1, 'ESTCON', '0', 'ESTRATTO CONTO CLIENTI/FORNITORI');
                Out::select($element_id, 1, 'RICBIL', '0', 'RICHIESTE DI BILANCIO');
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'CDS' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "SOLLECITI PAGAMENTO", '0', "SOLLECITI PAGAMENTO");
                Out::select($element_id, 1, "STAMPEVERBALI", '0', "STAMPE VERBALI");
                Out::select($element_id, 1, "SANZIONI ACCESSORIE", '0', "SANZIONI ACCESSORIE");
                Out::select($element_id, 1, "ULTIMO", '0', "ULTIMI AVVISI");
                Out::select($element_id, 1, "STAMPEVARIE", '0', "STAMPE VARIE");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'CDR' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "SOLLECITI PAGAMENTO", '0', "SOLLECITI PAGAMENTO");
                Out::select($element_id, 1, "STAMPEVERBALI", '0', "STAMPE VERBALI");
                Out::select($element_id, 1, "SANZIONI ACCESSORIE", '0', "SANZIONI ACCESSORIE");
                Out::select($element_id, 1, "STAMPEVARIE", '0', "STAMPE VARIE");
                Out::select($element_id, 1, "STAMPEINGIUNZIONI", '0', "STAMPE INGIUNZIONI");
                Out::select($element_id, 1, "ULTIMO", '0', "ULTIMI AVVISI");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'SEGRETERIA' :
                Out::html($element_id, '');
//                Out::select($element_id, 1, "COMPOSIZIONE", '0', "COMPOSIZIONE DOCUMENTI");
                Out::select($element_id, 1, "TESTIBASE", '1', "TESTI BASE DOCUMENTALE");
                Out::select($element_id, 1, "ESITOITER", '0', "TESTO ESITO ITER");
                Out::select($element_id, 1, "ESITODELIBERATO_C", '0', "TESTO ESITO DELIBERATO C");
                Out::select($element_id, 1, "ESITODELIBERATO_G", '0', "TESTO ESITO DELIBERATO G");
                Out::select($element_id, 1, "ESITODELIBERATO_LD", '0', "TESTO ESITO DELIBERATO LD");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'ALBO' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "ADEMPIMENTO_PUBB", '0', "ADEMPIMENTO_PUBBLICAZIONE");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'FIERE' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "COMUNICAZIONEMERCATO", '0', "COMUNICAZIONE MERCATO");
                Out::select($element_id, 1, "CONVOCAZIONEFIERA", '0', "CONVOCAZIONE FIERA");
                Out::select($element_id, 1, "CONCESSIONEFIERA", '0', "CONCESSIONE FIERA");
                Out::select($element_id, 1, "DINIEGOFIERA", '0', "DINIEGO FIERA");
                Out::select($element_id, 1, "COMUNICAZIONEFIERA", '0', "COMUNICAZIONE FIERA");
                Out::select($element_id, 1, "ALTREFIERA", '0', "ALTRE FIERA");
                Out::select($element_id, 1, "ALTRELETTEREDEC", '0', "ALTRE LETTERE DECENNALI");
                Out::select($element_id, 1, "DITTELIC", '0', "RILASCIO LICENZE");
                Out::select($element_id, 1, "COSAP", '0', "COSAP");
                Out::select($element_id, 1, "SOLLECITI", '0', "SOLLECITI");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'ZTL' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "PERMESSI", '0', "PERMESSI");
                Out::select($element_id, 1, "INVIO_DOCUMENTI", '0', "INVIO_DOCUMENTI");
                Out::select($element_id, 1, "MAIL", '0', "MAIL");
                Out::show($this->nameForm . "_{$element_id}_field");
                break;
            case 'BDAP' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "GARE", '0', "GARE");
                Out::show($this->nameForm . "_{$element_id}_field");

                break;
            case 'TRIBUTI-CW' :
                Out::html($element_id, '');
                Out::select($element_id, 1, '', '1', '');
                Out::select($element_id, 1, "TRIBUTI", '0', "Tributi");
                Out::select($element_id, 1, "IMUTASI", '0', "IMU-TASI");
                Out::show($this->nameForm . "_{$element_id}_field");

                break;
            default:
                Out::html($element_id, '');
                Out::hide($this->nameForm . "_{$element_id}_field");
                break;
        }
    }

    private function creaComboFunzioneCustom() {
        Out::html($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', '');
        if (is_array($this->FixedFields['FUNZIONE'])) {
            $sel = '1';
            foreach ($this->FixedFields['FUNZIONE'] as $key => $funzione) {
                Out::select($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 1, $funzione, $sel, $funzione);
                $sel = '';
            }
        } else {
            Out::select($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 1, $this->FixedFields['FUNZIONE'], '1', $this->FixedFields['FUNZIONE']);
        }
    }

    private function CreaComboTipi($arrTipi) {
        $arrTipi = $this->arrTipi;

        /*
         * Pulisco le varie select del TIPO
         */
        Out::html($this->nameForm . '_tipo', '');
        TableView::tableSetFilterHtml($this->gridDocumenti, "TIPO", '');
        Out::html($this->nameForm . '_DOC_DOCUMENTI[TIPO]', '');

        /*
         * Popolo le select del TIPO
         */
        Out::select($this->nameForm . '_tipo', 1, "", '0', "");
        TableView::tableSetFilterSelect($this->gridDocumenti, "TIPO", 1, "", "0", "");
        Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, "", '0', "");
        foreach ($arrTipi as $key => $value) {
            Out::select($this->nameForm . '_tipo', 1, $key, '0', $value);
            TableView::tableSetFilterSelect($this->gridDocumenti, "TIPO", 1, $key, '0', $value);
            Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, $key, '0', $value);
            //Nuova per permettere solo file XHTML e DOCX Per gli utenti BDAP
            if ($this->classificazione == "BDAP") {
                if ($value != "XHTML" && $value != "DOCX") {
                    Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 0, $key, '0', $value);
                }
            }
        }
    }

    private function CreaComboClassificazioni() {
        $cla_tab = docLib::getElencoClassificazioni();

        /*
         * Pulisco le varie select della CLASSIFICAZIONE
         */
        Out::html($this->nameForm . '_classificazione', '');
        TableView::tableSetFilterHtml($this->gridDocumenti, "CLASSIFICAZIONE", '');
        Out::html($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', '');


        /*
         * Select CLASSIFICAZIONI nella form di Detaglio
         */
        if ($cla_tab) {
            Out::select($this->nameForm . '_classificazione', 1, "", '0', "");
            TableView::tableSetFilterSelect($this->gridDocumenti, "CLASSIFICAZIONE", 1, "", '0', "");
            Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, "", '0', "");
            foreach ($cla_tab as $keyClas => $desc) {
                Out::select($this->nameForm . '_classificazione', 1, $keyClas, '0', $desc);
                TableView::tableSetFilterSelect($this->gridDocumenti, "CLASSIFICAZIONE", 1, $keyClas, '0', $desc);
                Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $keyClas, '0', $desc);
            }
        } else {
            Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $this->classificazione, '0', $this->classificazione);
        }
    }

    function CreaCombo() {
        $this->CreaComboTipi();
        $this->CreaComboClassificazioni();

        Out::select($this->nameForm . '_caratteristica', 1, '', '1', 'Tutti');
        Out::select($this->nameForm . '_caratteristica', 1, "I", '0', "Interni");
        Out::select($this->nameForm . '_caratteristica', 1, "E", '0', "Esterni");


        Out::select($this->nameForm . '_formato', 1, 'A3', '0', 'A3');
        Out::select($this->nameForm . '_formato', 1, 'A4', '1', 'A4');
        Out::select($this->nameForm . '_formato', 1, 'A5', '0', 'A5');
        Out::select($this->nameForm . '_formato', 1, 'A6', '0', 'A6');
        Out::select($this->nameForm . '_modelloXhtml', 1, "PERSONALIZZATO", '1', 'Personalizzato', "color:white;background-color:darkRed;");
        $sqlCombo = "SELECT * FROM DOC_DOCUMENTI WHERE TIPO = 'XLAYOUT'";
        if ($_POST[$this->nameForm . '_classificazione'] != "") {
            $codice = $_POST[$this->nameForm . '_classificazione'];
            $sqlCombo .= " AND CLASSIFICAZIONE = '" . $codice . "'";
        }
        $Doc_documenti_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sqlCombo, true);
        if ($Doc_documenti_tab) {
            foreach ($Doc_documenti_tab as $Doc_documenti_rec) {
                Out::select($this->nameForm . '_modelloXhtml', 1, $Doc_documenti_rec['CODICE'], '0', $Doc_documenti_rec['OGGETTO']);
            }
        }

        Out::select($this->nameForm . '_DOC_DOCUMENTI[CARATTERISTICA]', 1, "I", '1', "Interno");
        Out::select($this->nameForm . '_DOC_DOCUMENTI[CARATTERISTICA]', 1, "E", '0', "Esterno");

        $mapanag_tab = ItaDB::DBSQLSelect($this->ITALWEB, "SELECT ROW_ID, DESCRIZIONE FROM DOC_MAP_ANAG", true);
        Out::select($this->nameForm . '_DOC_DOCUMENTI[MAPPATURA]', 1, '0', '1', 'Nessuna');
        foreach ($mapanag_tab as $mapanag_rec) {
            Out::select($this->nameForm . '_DOC_DOCUMENTI[MAPPATURA]', 1, $mapanag_rec['ROW_ID'], '0', $mapanag_rec['DESCRIZIONE']);
        }
    }

    function setTipo($tipo, $documenti_rec = array()) {
        $valore = $documenti_rec['CONTENT'];
        Out::show($this->nameForm . '_divHtml');
        Out::html($this->nameForm . '_varFile_lbl', "Testo base");
        Out::tabSelect($this->nameForm . "_tabHtml", $this->nameForm . "_paneData");
        switch ($tipo) {
            case "XHTML":
                $this->unlockLayout();
                $metadati = unserialize($documenti_rec['METADATI']);
                unset($documenti_rec['METADATI']);
                Out::valore($this->nameForm . '_modelloXhtml', $metadati['MODELLOXHTML']);
                if ($metadati['MODELLOXHTML'] == 'PERSONALIZZATO') {
                    $this->unlockLayout(true);
                    Out::valore($this->nameForm . '_varHtmlHeader', $metadati['HEADERCONTENT']);
                    Out::valore($this->nameForm . '_varHtmlFooter', $metadati['FOOTERCONTENT']);
                    switch ($metadati['ORIENTATION']) {
                        case "V":
                            Out::attributo($this->nameForm . '_radioVerticale', 'checked', 0, 'checked');
                            Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 1);
                            break;
                        case "O":
                            Out::attributo($this->nameForm . '_radioverticale', 'checked', 1);
                            Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 0, 'checked');
                            break;
                    }
                    Out::valore($this->nameForm . '_Orientamento', $metadati['ORIENTATION']);
                    Out::valore($this->nameForm . '_formato', $metadati['FORMAT']);
                    Out::valore($this->nameForm . '_Superiore', $metadati['MARGIN-TOP']);
                    Out::valore($this->nameForm . '_Intestazione', $metadati['MARGIN-HEADER']);
                    Out::valore($this->nameForm . '_Sinistro', $metadati['MARGIN-LEFT']);
                    Out::valore($this->nameForm . '_Destro', $metadati['MARGIN-RIGHT']);
                    Out::valore($this->nameForm . '_Inferiore', $metadati['MARGIN-BOTTOM']);
                    Out::valore($this->nameForm . '_Piepagina', $metadati['MARGIN-FOOTER']);
                } else {
                    $this->caricaLayout($metadati['MODELLOXHTML']);
                    $this->lockLayout();
                }
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varHtml', $valore);
                }
                //Out::show($this->nameForm . '_divHtml');
                $this->setHtmlTab(true);
                Out::show($this->nameForm . '_divDocume');
                Out::show($this->nameForm . '_Preview');
                Out::hide($this->nameForm . '_divFile');
                Out::hide($this->nameForm . '_ApriVisualizza');
                Out::hide($this->nameForm . '_ApriVars');
                //Out::codice('tinyActivate("' . $this->nameForm . '_varHtml");');
                break;
            case "JREPORT":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varFile', $valore);
                }
                $this->setHtmlTab(false);
                Out::hide($this->nameForm . '_divDocume');
                Out::show($this->nameForm . '_Preview');
                Out::hide($this->nameForm . '_ApriVisualizza');
                Out::hide($this->nameForm . '_ApriVars');
                Out::show($this->nameForm . '_divFile');
                break;
            case "MSWORDXML":
            case "RTF":
            case "ODT":
            case "XML":
            case "TXT":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varFile', $valore);
                }
                $this->setHtmlTab(false);
                Out::hide($this->nameForm . '_divDocume');
                Out::hide($this->nameForm . '_Preview');
                Out::hide($this->nameForm . '_ApriVisualizza');
                Out::hide($this->nameForm . '_ApriVars');
                Out::show($this->nameForm . '_divFile');
                break;
            case "MSWORDHTML":
            case "DOCX":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varHtml', $valore);
                }

                $devLib = new devLib();
                $valueOO = $devLib->getEnv_config('SEGPARAMVARI', 'codice', 'SEG_OPENOO_DOCX', false);
                $valueOO = $valueOO['CONFIG'];

                if ($_POST[$this->nameForm . '_DOC_DOCUMENTI']['NUMREV'] != '' || $documenti_rec) {
                    Out::show($this->nameForm . '_ApriVisualizza');
                    Out::show($this->nameForm . '_Sostituisci');
                }
                $this->setHtmlTab(false);
                Out::hide($this->nameForm . '_divDocume');
                Out::hide($this->nameForm . '_Preview');
                Out::show($this->nameForm . '_ApriVars');
                Out::hide($this->nameForm . '_divFile');

                if ($valueOO == 1) {
                    Out::hide($this->nameForm . '_ApriVars');
                    Out::hide($this->nameForm . '_Sostituisci');
                }

                break;
            case "PDF":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varFile', $valore);
                }
                $this->setHtmlTab(false);
                Out::hide($this->nameForm . '_divDocume');
                Out::hide($this->nameForm . '_Preview');
                if ($_POST[$this->nameForm . '_DOC_DOCUMENTI']['NUMREV'] != '' || $documenti_rec) {
                    Out::show($this->nameForm . '_Sostituisci');
                }
                Out::hide($this->nameForm . '_ApriVars');
                Out::show($this->nameForm . '_divFile');
                break;
            case '':
                break;
        }

        return;
    }

    private function setHtmlTab($enable) {
        if ($enable) {
            Out::tabEnable($this->nameForm . '_tabHtml', $this->nameForm . '_paneContent');
            Out::tabEnable($this->nameForm . '_tabHtml', $this->nameForm . '_panePage');
            Out::tabEnable($this->nameForm . '_tabHtml', $this->nameForm . '_paneModel');
        } else {
            Out::tabDisable($this->nameForm . '_tabHtml', $this->nameForm . '_paneContent');
            Out::tabDisable($this->nameForm . '_tabHtml', $this->nameForm . '_panePage');
            Out::tabDisable($this->nameForm . '_tabHtml', $this->nameForm . '_paneModel');
        }
    }

    function embedVars($Classe, $Funzione = '') {
        if (!$this->dictionaryLegend) {
            switch ($Classe) {
                case docLib::CLASSIFICAZIONE_SERVECONOMICI:
                    include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibVariabili.class.php';
                    include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                    $praLibVar = new praLibVariabili();
                    $dictionaryLegend = $praLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_PRATICHE:
                    include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
                    include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                    $praLibVar = new praLibVariabili();
                    $dictionaryLegend = $praLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_FIERE:
                    include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLibVariabili.class.php';
                    include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLib.class.php';
                    $gfmLibVar = new gfmLibVariabili();
                    $dictionaryLegend = $gfmLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_ZTL:
                    include_once ITA_BASE_PATH . '/apps/ZTL/ztlLibVariabili.class.php';
                    include_once ITA_BASE_PATH . '/apps/ZTL/ztlLib.class.php';
                    $ztlLibVar = new ztlLibVariabili();
                    $dictionaryLegend = $ztlLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_COMMERCIO:
                    include_once ITA_BASE_PATH . '/apps/Commercio/wcoLibVariabili.class.php';
                    include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
                    $wcoLibVar = new wcoLibVariabili();
                    $dictionaryLegend = $wcoLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_TRIBUTI:
                    include_once (ITA_BASE_PATH . '/apps/Tributi/triLibVariabili.class.php');
                    include_once (ITA_BASE_PATH . '/apps/Tributi/triLib.class.php');
                    $triLibVar = new triLibVariabili();
                    $dictionaryLegend = $triLibVar->getLegendaAvviso('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_CDS:
                    switch ($Funzione) {
                        case "STAMPEVERBALI":
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLibVariabili.class.php');
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
                            $cdsLibVar = new cdsLibVariabili();
                            $dictionaryLegend = $cdsLibVar->getLegendaVerbale('adjacency', 'smarty');
                            break;
                        case "ULTIMO":
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLibVariabili.class.php');
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
                            $cdsLibVar = new cdsLibVariabili();
                            $dictionaryLegend = $cdsLibVar->getLegendaUltimo('adjacency', 'smarty');
                            break;
                        case "SANZIONI ACCESSORIE":
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLibVariabili.class.php');
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
                            $cdsLibVar = new cdsLibVariabili();
                            $dictionaryLegend = $cdsLibVar->getLegendaSanziacc('adjacency', 'smarty');
                            break;
                        default :
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLibVariabili.class.php');
                            include_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
                            $cdsLibVar = new cdsLibVariabili();
                            $dictionaryLegend = $cdsLibVar->getLegendaCds('adjacency', 'smarty');
                            break;
                    }
                    break;
                case docLib::CLASSIFICAZIONE_CDR:
                    include_once (ITA_BASE_PATH . '/apps/Cdr/cdrLibVariabili.class.php');
                    include_once (ITA_BASE_PATH . '/apps/Cdr/cdrLib.class.php');
                    $cdrLibVar = new cdrLibVariabili();
                    switch ($Funzione) {
                        case "STAMPEVERBALI":
                            $dictionaryLegend = $cdrLibVar->getLegendaVerbale('adjacency', 'smarty');
                            break;
                        case "SANZIONI ACCESSORIE":
                            $dictionaryLegend = $cdrLibVar->getLegendaSanziacc('adjacency', 'smarty');
                            break;
                        case "ULTIMO":
                            $dictionaryLegend = $cdrLibVar->getLegendaUltimo('adjacency', 'smarty');
                            break;
                        case "ULTIMO":
                            $dictionaryLegend = $cdrLibVar->getLegendaUltimo('adjacency', 'smarty');
                            break;
                        case "STAMPEINGIUNZIONI":
                        default :
                            $dictionaryLegend = $cdrLibVar->getLegendaCdr('adjacency', 'smarty');
                            break;
                    }
                    break;
                case docLib::CLASSIFICAZIONE_ALBO:
                    if (file_exists(ITA_BASE_PATH . '/apps/AlboPretorio/albLibVariabili.class.php')) {
                        include_once (ITA_BASE_PATH . '/apps/AlboPretorio/albLibVariabili.class.php');
                        include_once (ITA_BASE_PATH . '/apps/AlboPretorio/albLib.class.php');

                        $albLibVar = new albLibVariabili();

                        $dictionaryLegend = $albLibVar->getLegendaAlbo('adjacency', 'smarty');
                    }
                    break;
                case docLib::CLASSIFICAZIONE_SEGRETERIA:
                    include_once ITA_BASE_PATH . '/apps/Segreteria/segLibVariabili.class.php';
                    $segLibVariabili = new segLibVariabili();
                    $dictionaryLegend = $segLibVariabili->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_BDAP:
                    include_once ITA_BASE_PATH . '/apps/Bdap/bdaLibVariabili.class.php';
                    $bdaLibVar = new bdaLibVariabili();
                    if ($Funzione == 'GARE') {
                        $dictionaryLegend = $bdaLibVar->getLegendaModuloGare('adjacency', 'smarty');
                    } else {
                        $dictionaryLegend = $bdaLibVar->getLegendaGenerico('adjacency', 'smarty');
                    }
                    break;

                case docLib::CLASSIFICAZIONE_AMMTRASPARENTE:
                    include_once ITA_BASE_PATH . '/apps/Macerie/macLibVariabili.class.php';
                    $macLibVar = new macLibVariabili();
                    $dictionaryLegend = $macLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;

                case docLib::CLASSIFICAZIONE_INCIDENTI:
                    include_once ITA_BASE_PATH . '/apps/Gasin/gasLibVariabili.class.php';
                    $gasLibVar = new gasLibVariabili();
                    $dictionaryLegend = $gasLibVar->getLegendaGenerico('adjacency', 'smarty');
                    break;

                case docLib::CLASSIFICAZIONE_GAP:
                    include_once ITA_BASE_PATH . '/apps/Gapace/gapLibVariabili.class.php';
                    $gapLibVar = new gapLibVariabili();
                    $dictionaryLegend = $gapLibVar->getLegendaGap('adjacency', 'smarty');
                    break;

                case docLib::CLASSIFICAZIONE_PROTOCOLLO:
                    include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
                    $proLibVariabili = new proLibVariabili();
                    $dictionaryLegend = $proLibVariabili->getLegendaCampiProtocollo('adjacency', 'smarty');
//                  Per test sui valori.
//                    $proLibVariabili->setCodiceProtocollo('2016000262');
//                    $proLibVariabili->setTipoProtocollo('P');
//                    $dictionaryLegend = $proLibVariabili->getVariabiliProtocollo()->exportAdjacencyModel('adjacency');
                    break;
                case docLib::CLASSIFICAZIONE_TRIBUTICW:
                    include_once ITA_BASE_PATH . '/apps/CityTax/taxLibVariabili.class.php';
                    $taxLibVar = new taxLibVariabili();
                    $dictionaryLegend = $taxLibVar->getLegendaInvioPec('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_ANAGRAFE:
                    include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibVariabili.class.php';
                    $varAnagrafe = new cwdLibVariabili();
                    $dictionaryLegend = $varAnagrafe->getLegendaGenerico('adjacency', 'smarty');
                    break;
                case docLib::CLASSIFICAZIONE_ELETTORALE:
                    include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibVariabiliEle.class.php';
                    $varElettorale = new cwdLibVariabiliEle();
                    $dictionaryLegend = $varElettorale->getLegendaGenerico('adjacency', 'smarty');
                    break;

                default:
                    Out::msgInfo("Variabili Dizionario", "Dizionario non presente per questa classe di testo.", 'auto', 'auto', $this->nameForm);
                    return false;
            }
        } else {
            $dictionaryLegend = $this->dictionaryLegend;
        }
        $model = 'docVarsBrowser';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['dictionaryLegend'] = $dictionaryLegend;
        $_POST['editorId'] = $this->nameForm . '_varHtml';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        return true;
    }

    function lockLayout() {
        Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtmlHeader");');
        Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtmlFooter");');
        Out::codice('$("#' . $this->nameForm . '_paneModel :input").attr("disabled",true);');
        Out::codice('$("#' . $this->nameForm . '_panePage :input").attr("disabled",true);');
        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlHeader");');
        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlFooter");');
        Out::attributo($this->nameForm . '_modelloXhtml', 'disabled', '1');
    }

    function unlockLayout($recreateTiny) {
        if ($recreateTiny == true) {
            Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtmlHeader");');
            Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtmlFooter");');
        }
        Out::codice('$("#' . $this->nameForm . '_paneModel :input").removeAttr("disabled");');
        Out::codice('$("#' . $this->nameForm . '_panePage :input").removeAttr("disabled");');
        if ($recreateTiny == true) {
            Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlHeader");');
            Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlFooter");');
        }
    }

    function caricaLayout($codiceLayout) {
        $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CODICE = '$codiceLayout' AND TIPO = 'XLAYOUT'";
        $Doc_documenti_rec = ItaDB::DBSQLSelect($this->ITALWEB, $sql, False);
        $metadatiLayout = unserialize($Doc_documenti_rec['METADATI']);
        $contentLayout = unserialize($Doc_documenti_rec['CONTENT']);
        Out::valore($this->nameForm . '_varHtmlHeader', $contentLayout['XHTML_HEADER']);
        Out::valore($this->nameForm . '_varHtmlFooter', $contentLayout['XHTML_FOOTER']);
        switch ($metadatiLayout['ORIENTATION']) {
            case "V":
                Out::attributo($this->nameForm . '_radioVerticale', 'checked', 0, 'checked');
                Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 1);
                break;
            case "O":
                Out::attributo($this->nameForm . '_radioverticale', 'checked', 1);
                Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 0, 'checked');
                break;
        }
        Out::valore($this->nameForm . '_formato', $metadatiLayout['FORMAT']);
        Out::valore($this->nameForm . '_Superiore', $metadatiLayout['MARGIN-TOP']);
        Out::valore($this->nameForm . '_Intestazione', $metadatiLayout['MARGIN-HEADER']);
        Out::valore($this->nameForm . '_Sinistro', $metadatiLayout['MARGIN-LEFT']);
        Out::valore($this->nameForm . '_Destro', $metadatiLayout['MARGIN-RIGHT']);
        Out::valore($this->nameForm . '_Inferiore', $metadatiLayout['MARGIN-BOTTOM']);
        Out::valore($this->nameForm . '_Piepagina', $metadatiLayout['MARGIN-FOOTER']);
    }

    function setMetadati() {
        $metadati = array();
        $metadati['MODELLOXHTML'] = $_POST[$this->nameForm . '_modelloXhtml'];
        if ($metadati['MODELLOXHTML'] == 'PERSONALIZZATO') {
            $metadati['HEADERCONTENT'] = $_POST[$this->nameForm . '_varHtmlHeader'];
            $metadati['FOOTERCONTENT'] = $_POST[$this->nameForm . '_varHtmlFooter'];
            $metadati['ORIENTATION'] = $_POST[$this->nameForm . '_Orientamento'];
            $metadati['FORMAT'] = $_POST[$this->nameForm . '_formato'];
            $metadati['MARGIN-TOP'] = $_POST[$this->nameForm . '_Superiore'];
            $metadati['MARGIN-HEADER'] = $_POST[$this->nameForm . '_Intestazione'];
            $metadati['MARGIN-LEFT'] = $_POST[$this->nameForm . '_Sinistro'];
            $metadati['MARGIN-RIGHT'] = $_POST[$this->nameForm . '_Destro'];
            $metadati['MARGIN-BOTTOM'] = $_POST[$this->nameForm . '_Inferiore'];
            $metadati['MARGIN-FOOTER'] = $_POST[$this->nameForm . '_Piepagina'];
        }
        return serialize($metadati);
    }

    function previewXHTML($documenti_rec, $extraParam = array()) {
        if (!$documenti_rec) {
            return;
        }

        $bodyValue = $documenti_rec['CONTENT'];
        $unserMetadata = unserialize($documenti_rec['METADATI']);
        if ($unserMetadata['MODELLOXHTML'] == 'PERSONALIZZATO') {
            $headerContent = $unserMetadata['HEADERCONTENT'];
            $footerContent = $unserMetadata['FOOTERCONTENT'];
            $orientation = $unserMetadata['ORIENTATION'];
            $format = $unserMetadata['FORMAT'];
            $marginTop = $unserMetadata['MARGIN-TOP'] + $unserMetadata['MARGIN-HEADER'];
            $marginHeader = $unserMetadata['MARGIN-HEADER'];
            $marginLeft = $unserMetadata['MARGIN-LEFT'];
            $marginRight = $unserMetadata['MARGIN-RIGHT'];
            $marginBottom = $unserMetadata['MARGIN-BOTTOM'] + $unserMetadata['MARGIN-FOOTER'];
            $marginFooter = $unserMetadata['MARGIN-FOOTER'];
            if ($orientation == "O") {
                $orientation = "landscape";
            } else if ($orientation == "V") {
                $orientation = "portrait";
            }
        } else {
            $codiceLayout = $unserMetadata['MODELLOXHTML'];
            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CODICE = '$codiceLayout' AND TIPO = 'XLAYOUT'";
            $Doc_documenti_rec = ItaDB::DBSQLSelect($this->ITALWEB, $sql, False);
            $unserContent = unserialize($Doc_documenti_rec['CONTENT']);
            $metadatiLayout = unserialize($Doc_documenti_rec['METADATI']);
            if ($metadatiLayout) {
                $headerContent = $unserContent['XHTML_HEADER'];
                $footerContent = $unserContent['XHTML_FOOTER'];
                $orientation = $metadatiLayout['ORIENTATION'];
                $format = $metadatiLayout['FORMAT'];
                $marginTop = $metadatiLayout['MARGIN-TOP'] + $metadatiLayout['MARGIN-HEADER'];
                $marginHeader = $metadatiLayout['MARGIN-HEADER'];
                $marginLeft = $metadatiLayout['MARGIN-LEFT'];
                $marginRight = $metadatiLayout['MARGIN-RIGHT'];
                $marginBottom = $metadatiLayout['MARGIN-BOTTOM'] + $metadatiLayout['MARGIN-FOOTER'];
                $marginFooter = $metadatiLayout['MARGIN-FOOTER'];
                if ($orientation == "O") {
                    $orientation = "landscape";
                } else if ($orientation == "V") {
                    $orientation = "portrait";
                }
            }
        }
        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $bodyValue);
        $itaSmarty->assign('documentheader', $headerContent);
        $itaSmarty->assign('documentfooter', $footerContent);
        $itaSmarty->assign('headerHeight', $marginHeader);
        $itaSmarty->assign('footerHeight', $marginFooter);
        $itaSmarty->assign('marginTop', $marginTop);
        $itaSmarty->assign('marginBottom', $marginBottom);
        $itaSmarty->assign('marginLeft', $marginLeft);
        $itaSmarty->assign('marginRight', $marginRight);
        $itaSmarty->assign('pageFormat', $format);
        $itaSmarty->assign('pageOrientation', $orientation);
        //eventuali parametri extra
        foreach ($extraParam as $campo => $valore) {
            $itaSmarty->assign($campo, $valore);
        }

        $documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';
        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layoutTemplate.xhtml";
        if (!copy($layoutTemplate, $documentLayout)) {
            Out::msgStop("Errore", "Copia template layout Fallita");
            return;
        }
        $contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
        $pdfPreview = itaLib::getAppsTempPath() . '/documentpreview.pdf';

        if (!file_put_contents($documentPreview, $contentPreview)) {
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return;
        }
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $documentPreview . ' ' . $pdfPreview;
        exec($command, $outvar, $return_var);
        Out::openDocument(utiDownload::getUrl(
                        App::$utente->getKey('TOKEN') . "-preview.pdf", $pdfPreview
                )
        );
    }

    public function NuovoDocumento() {
        $this->ToggleVisualizzazioneScaduto(false);

        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '1');
        Out::enableField($this->nameForm . '_DOC_DOCUMENTI[DATASCAD]');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();

        $this->CreaComboTipi($this->arrTipi);

        $this->setTipo('');
        $this->creaComboFunzione($this->classificazione);
        if ($this->FixedFields) {
            $this->HideCampi();
            Out::show($this->nameForm . '_Aggiungi');
            Out::show($this->nameForm . '_Copia');
            //
            // Pulisco le combo
            //
            Out::html($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', '');
            // Out dei valori
            if ($this->FixedFields['CODICE']) {
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[CODICE]', $this->FixedFields['CODICE']);
                Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
            }
            Out::valore($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', $this->FixedFields['OGGETTO']);
            Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $this->FixedFields['CLASSIFICAZIONE'], '1', $this->FixedFields['CLASSIFICAZIONE']);

            // Il Tipo lo pulisco e faccio l'out del valore solo se ce l'ho.
            if ($this->FixedFields['TIPO']) {
                Out::html($this->nameForm . '_DOC_DOCUMENTI[TIPO]', '');
                Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, $this->FixedFields['TIPO'], '1', $this->FixedFields['TIPO']);
                //Out::attributo($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 'disabled', '0');
                $this->setTipo($this->FixedFields['TIPO']);
            }
            // La funzione la pulisco e faccio l'out del valore solo se ce l'ho.
            if ($this->FixedFields['FUNZIONE']) {
                $this->creaComboFunzioneCustom();

////                Out::show($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]');
//                Out::html($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', '');
//                Out::select($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 1, $this->FixedFields['FUNZIONE'], '1', $this->FixedFields['FUNZIONE']);
//                Out::attributo($this->nameForm . '_DOC_DOCUMENTI[FUNZIONE]', 'readonly', '0');
            }
        } else {
            Out::show($this->nameForm . '_Aggiungi');
            Out::show($this->nameForm . '_Copia');
            Out::show($this->nameForm . '_AltraRicerca');
            Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[CODICE]');
            $this->HideCampi();
            if ($this->classificazione != '') {
                Out::valore($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', $this->classificazione);
            }
        }
        $this->setLockDocFields();
    }

    private function setLockDocFields() {
        if ($this->lockedDoc) {
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', 'readonly', '0');
            Out::enableField($this->nameForm . '_DOC_DOCUMENTI[DATASCAD]');
            Out::hide($this->nameForm . '_Torna');
            Out::hide($this->nameForm . '_AltraRicerca');
        }
    }

    private function bloccaValori() {
        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', 'readonly', '0');
        Out::enableField($this->nameForm . '_DOC_DOCUMENTI[DATASCAD]');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        $this->HideCampi();
        $this->creaComboFunzione($this->classificazione);
        Out::show($this->nameForm . '_Aggiungi');
        //
        // Pulisco le combo
        //
        Out::html($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', '');
        Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $this->FixedFields['CLASSIFICAZIONE'], '1', $this->FixedFields['CLASSIFICAZIONE']);
        // Out dei valori
        Out::valore($this->nameForm . '_DOC_DOCUMENTI[CODICE]', $this->FixedFields['CODICE']);
        Out::valore($this->nameForm . '_DOC_DOCUMENTI[OGGETTO]', $this->FixedFields['OGGETTO']);
        // Il Tipo lo pulisco e faccio l'out del valore solo se ce l'ho.
        if ($this->FixedFields['TIPO']) {
            Out::html($this->nameForm . '_DOC_DOCUMENTI[TIPO]', '');
            Out::select($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 1, $this->FixedFields['TIPO'], '1', $this->FixedFields['TIPO']);
            Out::attributo($this->nameForm . '_DOC_DOCUMENTI[TIPO]', 'disabled', '0');
            $this->setTipo($this->FixedFields['TIPO']);
        }
        $sqlCombo = "SELECT * FROM DOC_DOCUMENTI WHERE TIPO = 'XLAYOUT' AND CLASSIFICAZIONE = '" . $this->FixedFields['CLASSIFICAZIONE'] . "'";
        $Doc_documenti_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sqlCombo, true);
        if ($Doc_documenti_tab) {
            foreach ($Doc_documenti_tab as $Doc_documenti_rec) {
                Out::select($this->nameForm . '_modelloXhtml', 1, $Doc_documenti_rec['CODICE'], '0', $Doc_documenti_rec['OGGETTO']);
            }
        }
    }

    private function validateXHTMLInput() {
        $varhtml = $this->validateXHTML($_POST[$this->nameForm . '_varHtml']);
        $varhtml_head = $this->validateXHTML($_POST[$this->nameForm . '_varHtmlHeader']);
        $varhtml_foot = $this->validateXHTML($_POST[$this->nameForm . '_varHtmlFooter']);

        $errmsg = '';

        if ($varhtml !== true) {
//            Out::msgStop("Errore parsing Testo", $varhtml);
            $errmsg .= "<b>Errore validazione Testo</b>:<br><br>" . $varhtml;
        } else if ($varhtml_head !== true) {
//            Out::msgStop("Errore parsing Intestazione", $varhtml_head);
            $errmsg .= ($errmsg === '' ? '' : '<br><br>') . "<b>Errore validazione Intestazione</b>:<br><br>" . $varhtml_head;
        } else if ($varhtml_foot !== true) {
//            Out::msgStop("Errore parsing Piè di pagina", $varhtml_foot);
            $errmsg .= ($errmsg === '' ? '' : '<br><br>') . "<b>Errore validazione Piè di pagina</b>:<br><br>" . $varhtml_foot;
        }

        if ($errmsg === '') {
            return true;
        } else {
            $doc_documenti = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
            $docinfo = $doc_documenti['ROWID'] . '/' . $doc_documenti['CODICE'] . '/' . $doc_documenti['CLASSIFICAZIONE'];
            $this->insertAudit($this->ITALWEB, 'DOC_DOCUMENTI', "Errore in validazione documento: $docinfo", $doc_documenti['ROWID'], eqAudit::OP_GENERIC_WARNING);
            return $errmsg;
        }
    }

    private function validateXHTML($xml) {
        /*
         * Controllo variabili Smarty
         */

//        $var_opening_count = substr_count($xml, '@{');
//        $var_closing_count = substr_count($xml, '}@');

        $chars_to_show = 30;
        $expecting_close_tag = false;

        preg_match_all('/(@{|}@)/s', $xml, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $match) {
            $string = $match[0];
            $position = $match[1];
            $code_string = htmlentities(trim(substr($xml, $position - $chars_to_show, $chars_to_show))) . '<b><u>' . htmlentities(trim(substr($xml, $position, 2))) . '</u></b>' . htmlentities(trim(substr($xml, $position + 2, $chars_to_show)));

            if (!$expecting_close_tag && $string !== '@{') {
                return "Trovata '}@' senza apertura, posizione $position:<br /><code style=\"font-size: 1.2em;\">..." . $code_string . "...</code>";
            }

            if ($expecting_close_tag && $string !== '}@') {
                return "Trovata '@{' dopo un'altra apertura, posizione $position:<br /><code style=\"font-size: 1.2em;\">..." . $code_string . "...</code>";
            }

            $expecting_close_tag = !$expecting_close_tag;
        }

        if ($expecting_close_tag) {
            return "Trovata '@{' senza chiusura, posizione $position:<br /><code style=\"font-size: 1.2em;\">..." . $code_string . "...</code>";
        }

        /*
         * Metto un blocco esterno per validare eventuali blocchi divisi
         */
        $xml = '<root>' . $xml . '</root>';

        /*
         * Sostituisco gli if di template con tag XML
         */
        $xml = preg_replace(array("/(@{if.*?}@)/", "/(@{else}@)/", "/(@{\/if}@)/"), array('<ifblock>', '</ifblock><ifblock>', '</ifblock>'), $xml);

        /*
         * Preparo la stringa da parsare con codifica UTF8
         * v Metodo precedente, utf8_encode dovrebbe essere inutile in questo caso quindi è stato tolto
         * $parse = utf8_encode(html_entity_decode($xml, ENT_COMPAT || ENT_HTML401, 'utf-8'));
         */
        $parse = html_entity_decode($xml, ENT_COMPAT || ENT_HTML401, 'utf-8');

        /*
         * Abilito gli errori di libxml
         */
        libxml_use_internal_errors(true);

        /* @var $dom DOMDocument */
        $dom = DOMDocument::loadXML($parse);

        if (!$dom) {
            /*
             * Leggo e ripulisco gli errori di libxml
             */
            $errors = libxml_get_errors();
            libxml_clear_errors();

            /*
             * Esplodo l'xml originale per linea così da avere un array con la referenza per ogni linea
             */
            $xml = explode("\n", $xml);

            /* @var $error LibXMLError */
            foreach ($errors as $error) {
                $errmes = ( isset($errmes) ? $errmes . "<br><br><div style=\"height: 1px; background-color: #bbb;\"></div><br>" : '' ) . "Errore in linea {$error->line}: {$error->message}<br><code style=\"font-size: 1.2em;\">" . htmlentities(trim($xml[$error->line - 1])) . "</code>";
            }

            return $errmes;
        }

        $preg = preg_match_all("/(@{if.*?}@|@{else}@|@{\/if}@)/", $dom->textContent, $matches);

        if ($preg === false || $preg > 0) {
            foreach ($matches[1] as $k => &$match)
                $match = ($k + 1) . '. ' . $match;

            return "Errore nella formattazione dei blocchi di condizione.<br>Blocchi errati:<br><br>" . implode('<br>', $matches[1]);
        }

        return true;
    }

    private function importDocs() {
        $model = "utiUploadDiag";
        itaLib::openForm($model);
        $objForm = itaModel::getInstance($model);
        $objForm->setEvent('openform');
        $objForm->setReturnEvent('returnUploadZipDocs');
        $objForm->setReturnModel($this->nameForm);
        $objForm->parseEvent();
    }

    private function confirmImportDocs($sourceFile) {
        // Controlla estensione
        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
        if (strtolower($ext) != "zip") {
            Out::msgStop("Attenzione!", "L'estensione del file selezionato non corrisponde ad un .zip");
            return;
        }

        // Apre dialog per selezione dei file da importare
        $model = "docImportDialog";
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model);
        if (!$formObj) {
            Out::msgStop("Errore", "apertura finestra selezione documenti fallita");
            return;
        }
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnEvent('returnSelezioneDocumenti');
        $formObj->setPathZip($sourceFile);
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

    private function executeImportDocs($selectedDocs) {
        // Verifica precondizioni
        if (count($selectedDocs) === 0) {
            return;
        }

        // Azzera contatori
        $numDocImp = 0;
        $numDocImpUpd = 0;
        $numErroriImpIns = 0;
        $numErroriImpUpd = 0;
        $arrMesgErr = array();

        // Effettua importazione
        foreach ($selectedDocs as $doc) {
            // Rimuove campi che non devono essere coinvolti nell'aggiornamento del database
            unset($doc['SELECTED']);
            unset($doc['IS_NEW_DOC']);
            unset($doc['CODICE_FMT']);
            $rawData = $doc['RAWDATA'];
            unset($doc['RAWDATA']);
            $_POST[$this->nameForm . '_varHtml'] = $doc['CONTENT'];

            // Verifica se il record è già presente sul database
            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CODICE = '{$doc['CODICE']}'";
            $oldDoc = ItaDB::DBSQLSelect($this->ITALWEB, $sql, false);
            if ($oldDoc) {
                // Aggiorna documento
                $doc['ROWID'] = $oldDoc['ROWID'];
                $ret = $this->docLib->aggiorna(
                        $doc, $_POST[$this->nameForm . '_varHtml'], $_POST[$this->nameForm . '_varFile'], $this->aggiornaFile, $this);

                if ($ret['COD_ERR'] !== 0) {
                    $numErroriImpUpd++;
                    $arrMesgErr[] = $ret['MSG_ERR'];
                } else {
                    $docPath = $this->docLib->setDirectory();
                    $filePath = $docPath . $doc['URI'];
                    if (file_put_contents($filePath, base64_decode($rawData))) {
                        $numDocImpUpd++;
                    } else {
                        $numErroriImpUpd++;
                    }
                }
            } else {
                // Inserisce documento
                unset($doc['ROWID']);
                $ret = $this->docLib->aggiungi(
                        $doc, $_POST[$this->nameForm . '_varHtml'], $_POST[$this->nameForm . '_varFile'], $this->aggiornaFile, $this, true);

                if ($ret['COD_ERR'] !== 0) {
                    $numErroriImpIns++;
                    $arrMesgErr[] = $ret['MSG_ERR'];
                } else {
                    $doc_documenti_rec = $this->docLib->getDocumenti($doc['CODICE']);
                    $docPath = $this->docLib->setDirectory();
                    $filePath = $docPath . $doc_documenti_rec['URI'];
                    if (file_put_contents($filePath, base64_decode($rawData))) {
                        $numDocImp++;
                    } else {
                        $numErroriImpIns++;
                    }
                }
            }
        }

        // Notifica avvenuta operazione
        $messaggioNotifica = 'Nuovi documenti importati: <b>' . $numDocImp . '</b>';
        $messaggioNotifica .= '<br>Documenti aggiornati: <b>' . $numDocImpUpd . '</b>';
        if ($numErroriImpIns > 0) {
            $messaggioNotifica .= '<br>Errori riscontrati in inserimento: <b>' . $numErroriImpIns . '</b>';
        }
        if ($numErroriImpUpd > 0) {
            $messaggioNotifica .= '<br>Errori riscontrati in aggiornamento: <b>' . $numErroriImpUpd . '</b>';
        }
        if (count($arrMesgErr)) {
            $messaggioNotifica .= '<br>Elenco errori riscontrati <br><pre>' . print_r($arrMesgErr, true) . '</pre>';
        }
        Out::msgInfo("Import", $messaggioNotifica);
    }

    private function exportDocs() {
        // Ricava elenco documenti da esportare        
        $documentoCorrente = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['CODICE']);
        if ($documentoCorrente) {
            $documenti = array($documentoCorrente);
        } else {
            $sql = $this->CreaSql() . ' ORDER BY CODICE';
            $documenti = ItaDB::DBSQLSelect($this->ITALWEB, $sql, true);
        }

        // Controlla se ci sono documenti da esportare
        if (count($documenti) == 0) {
            Out::msgInfo("Export", "Nessun documento da esportare!");
            return;
        }

        // Crea cartella temporanea per elaborazione
        $tmpPathKey = 'export-docs' . uniqid();
        $tmpPath = itaLib::createAppsTempPath($tmpPathKey);

        // Esporta ogni singolo documento su un file xml
        $documentiEsportati = array();
        $erroriEsportazione = array();
        foreach ($documenti as $documento) {
            $result = $this->exportDoc($documento, $tmpPath);
            if ($result['esito']) {
                $documentiEsportati[] = $result;
            } else {
                $erroriEsportazione[] = $result;
            }
        }

        // Se presenti errori, crea file degli errori
        $pathFileErrori = null;
        if (count($erroriEsportazione) > 0) {
            $pathFileErrori = $this->exportDocsCreaFileErrori($erroriEsportazione, $tmpPath);
        }

        // Crea uno zip con tutti i file esportati e, se presente, il file degli errori
        $zipFilename = 'docs-' . time() . '.zip';
        $pathZip = $this->exportDocsCreaZip($documentiEsportati, $pathFileErrori, $tmpPath, $zipFilename);
        if (!$pathZip) {
            Out::msgStop("Export", "Errore nella creazione file zip!");
            return;
        }

        // Effettua il download dello zip
        $urlDownload = utiDownload::getUrl($zipFilename, $pathZip);
        Out::openDocument($urlDownload, true);

        // Notifica avvenuta operazione
        $messaggioNotifica = 'Documenti esportati: <b>' . count($documentiEsportati) . '</b>';
        if (count($erroriEsportazione) > 0) {
            $messaggioNotifica .= '<br>Errori riscontrati: <b>' . count($erroriEsportazione) . '</b>';
        }
        Out::msgInfo("Export", $messaggioNotifica);
    }

    private function exportDoc($documento, $tmpPath) {
        $name = $documento['CODICE'] . '.xml';
        $result = array();
        $result['name'] = $name;
        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = TRUE;

            // Campi
            $root = $doc->createElement("ROW");
            foreach ($documento as $fieldName => $fieldValue) {
                $child = $doc->createElement($fieldName);
                $textNode = $doc->createTextNode($fieldValue);
                $child->appendChild($textNode);
                $root->appendChild($child);
            }

            // Contenuto
            $child = $doc->createElement('RAWDATA');
            $docPath = $this->docLib->setDirectory();
            $filePath = $docPath . $documento['URI'];
            $textNode = $doc->createTextNode(base64_encode(file_get_contents($filePath)));
            $child->appendChild($textNode);
            $root->appendChild($child);

            // Salva file e aggiorna esito
            $doc->appendChild($root);
            $path = $tmpPath . "/$name";
            $doc->save($path);
            $result['esito'] = true;
            $result['path'] = $path;
        } catch (Exception $ex) {
            $result['esito'] = false;
            $result['messaggio'] = $ex->getMessage();
        }

        return $result;
    }

    private function exportDocsCreaFileErrori($erroriEsportazione, $tmpPath) {
        try {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = TRUE;

            // Campi
            $root = $doc->createElement("ERRORI");
            foreach ($erroriEsportazione as $err) {
                $errElement = $doc->createElement("ERRORE");

                // name
                $child = $doc->createElement('NOME_DOC');
                $textNode = $doc->createTextNode($err['name']);
                $child->appendChild($textNode);
                $errElement->appendChild($child);

                // messaggio
                $child = $doc->createElement('MESSAGGIO');
                $textNode = $doc->createTextNode($err['messaggio']);
                $child->appendChild($textNode);
                $errElement->appendChild($child);

                $root->appendChild($errElement);
            }

            // Salva file e aggiorna esito
            $doc->appendChild($root);
            $path = $tmpPath . "/err.xml";
            $doc->save($path);

            return $path;
        } catch (Exception $ex) {
            return null;
        }
    }

    private function exportDocsCreaZip($documentiEsportati, $pathFileErrori, $tmpPath, $zipFilename) {
        $zipArchive = new ZipArchive;
        $zipFilename = $tmpPath . '/' . $zipFilename;
        $result = $zipArchive->open($zipFilename, ZipArchive::CREATE);
        if (!$result) {
            return false;
        }

        // Scorre lista dei documenti esportati
        foreach ($documentiEsportati as $doc) {
            $zipArchive->addFile($doc['path'], $doc['name']);
        }

        // Aggiunge file errori se presente
        if ($pathFileErrori) {
            $zipArchive->addFile($pathFileErrori, basename($pathFileErrori));
        }

        $zipArchive->close();

        return $zipFilename;
    }

}
