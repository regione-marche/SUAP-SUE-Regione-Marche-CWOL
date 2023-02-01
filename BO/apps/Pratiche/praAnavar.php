<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

function praAnavar() {
    $praAnavar = new praAnavar();
    $praAnavar->parseEvent();
    return;
}

class praAnavar extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $docLib;
    public $nameForm = "praAnavar";
    public $divGes = "praAnavar_divGestione";
    public $divRis = "praAnavar_divRisultato";
    public $divRic = "praAnavar_divRicerca";
    public $gridAnavar = "praAnavar_gridAnavar";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->docLib = new docLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB = $this->docLib->getITALWEB();
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
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnavar:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnavar:
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
                $ita_grid01 = new TableView($this->gridAnavar, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('VARDES');
                $ita_grid01->exportXLS('', 'Anavar.xls');
                break;
            case 'printTableToHTML':
                $Ausili_rec = $this->praLib->GetAusili('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Ausili_rec['DESCR']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAnavar', $parameters);
                break;
            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $ita_grid01->getDataArray();
                $result_tab = $this->elaboraRecords($result_tab);
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAVAR[VARTIP]':
                        $this->setTipo($_POST[$this->nameForm . '_ANAVAR']['VARTIP']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                        Out::clearFields($this->nameForm, $this->divGes);
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnavar, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(15);
                        $ita_grid01->setSortIndex('VARCOD');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $ita_grid01->getDataArray();
                        $result_tab = $this->elaboraRecords($result_tab);
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes, '');
                            Out::hide($this->divRic, '');
                            Out::show($this->divRis, '');
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnavar);
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANAVAR[VARCOD]', 'readonly', '1');
                        Out::hide($this->divRic, '');
                        Out::hide($this->divRis, '');
                        Out::show($this->divGes, '');
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAVAR[VARCOD]');
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $anavar_rec = $this->praLib->GetAnavar($_POST[$this->nameForm . '_ANAVAR']['VARCOD']);
                        if (!$anavar_rec) {
                            $anavar_rec = $_POST[$this->nameForm . '_ANAVAR'];
                            switch ($anavar_rec['VARTIP']) {
                                case '01':
                                    $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varHtml'];
                                    break;
                                case '02':
                                    $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varDataset'];
                                    break;
                                case '03':
                                    $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varTestoBase'];
                                    break;
                                case '04':
                                    $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varTabella'];
                                    break;
                            }
                            $insert_Info = 'Oggetto: ' . $anavar_rec['VARCOD'] . " " . $anavar_rec['VARDES'];
                            if ($this->insertRecord($this->PRAM_DB, 'ANAVAR', $anavar_rec, $insert_Info)) {
                                $anavar_rec = $this->praLib->GetAnavar($anavar_rec['VARCOD']);
                                $this->Dettaglio($anavar_rec['ROWID']);
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANAVAR[VARCOD]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anavar_rec = $_POST[$this->nameForm . '_ANAVAR'];

                        switch ($anavar_rec['VARTIP']) {
                            case '01':
                                $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varHtml'];
                                break;
                            case '02':
                                $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varDataset'];
                                break;
                            case '03':
                                $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varTestoBase'];
                                break;
                            case '04':
                                $anavar_rec['VAREXP'] = $_POST[$this->nameForm . '_varTabella'];
                                break;
                        }
                        $update_Info = 'Oggetto: ' . $anavar_rec['VARCOD'] . " " . $anavar_rec['VARDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAVAR', $anavar_rec, $update_Info)) {
                            $this->Dettaglio($anavar_rec['ROWID']);
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
                        $anavar_rec = $_POST[$this->nameForm . '_ANAVAR'];

                        if (!$this->cancellaDocumento($anavar_rec['VAREXPDOCX'])) {
                            break;
                        }

                        try {
                            $delete_Info = 'Oggetto: ' . $anavar_rec['VARCOD'] . " " . $anavar_rec['VARDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAVAR', $anavar_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA PARERI", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_CercaVar1':
                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFromAnavar(true);
                        $arrayLegenda = $praLibVar->getLegendaGenerico();
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliHtml', true);
                        break;

                    case $this->nameForm . '_CercaVar2':
                        $praLibVar = new praLibVariabili();
                        $arrayLegenda = $praLibVar->getLegendaGenerico();
                        praRic::ricVariabili($arrayLegenda, $this->nameForm);
                        break;

                    case $this->nameForm . '_CercaVar3':
                        $praLibVar = new praLibVariabili();
                        $arrayLegenda = $praLibVar->getLegendaGenerico();
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabiliTabella');
                        break;

                    case $this->nameForm . '_VediTesto':
                        if ($_POST[$this->nameForm . '_varTestoBase'] != '') {
                            $ditta = App::$utente->getKey('ditta');
                            $segr = Config::getPath('general.itaSegr');
                            $testo = substr($_POST[$this->nameForm . '_varTestoBase'], 10);
                            $tipo = $this->segLib->GetPercorsoTesti('15');
                            $percorsoCompleto = $segr . "segr" . $ditta . "/$tipo/" . $testo;
                            if (file_exists($percorsoCompleto)) {
                                Out::openDocument(utiDownload::getUrl($testo, $percorsoCompleto));
                            }
                        }
                        break;

                    case $this->nameForm . '_ANAVAR[VAREXPDOCX]_butt':
                        $this->openDocumento('DOCX');
                        break;

                    case $this->nameForm . '_CancellaDOCX':
                        if (!$_POST[$this->nameForm . '_ANAVAR']['VAREXPDOCX']) {
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione del DOCX?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancellaDOCX',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancellaDOCX',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                        ));
                        break;

                    case $this->nameForm . '_ConfermaCancellaDOCX':
                        $anavar_rec = $this->praLib->GetAnavar($_POST[$this->nameForm . '_ANAVAR']['ROWID'], 'ROWID');
                        $cod_docx = $_POST[$this->nameForm . '_ANAVAR']['VAREXPDOCX'];
                        $anavar_rec['VAREXPDOCX'] = '';

                        if (!$this->updateRecord($this->PRAM_DB, 'ANAVAR', $anavar_rec, '')) {
                            Out::msgStop("Errore", 'Errore durante l\'update del record.');
                        }

                        Out::valore($this->nameForm . '_ANAVAR[VAREXPDOCX]', '');

                        if (!$this->cancellaDocumento($cod_docx)) {
                            break;
                        }
                        break;
                    case $this->nameForm . "_ImportVariabili":
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadJSON";
                        itaLib::openForm($model);
                        $obj = itaModel::getInstance($model);
                        $obj->parseEvent();
                        break;
                    case $this->nameForm . "_ExportVariabili":
                        praRic::praRicAnavar($this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'embedVars':
                $praLibVar = new praLibVariabili();
                $praLibVar->setFromAnavar(true);
                $arrayLegenda = $praLibVar->getLegendaGenerico();

                $_POST['editorId'] = $_POST['id'];
                $_POST['dictionaryLegend'] = $arrayLegenda;

                $model = 'docVarsBrowser';
                itaLib::openForm($model);
                /* @var $docVarsBrowser docVarsBrowser */
                $docVarsBrowser = itaModel::getInstance($model);
                $docVarsBrowser->setEvent('openform');
                $docVarsBrowser->parseEvent();
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_varcod':
                        $codice = $_POST[$this->nameForm . '_varcod'];
                        if ($codice != '') {
                            $anavar_rec = $this->praLib->GetAnavar($codice);
                            if ($anavar_rec) {
                                $this->Dettaglio($anavar_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAVAR[VARCOD]':
//                        $praLibVar = new praLibVariabili();
//                        $ditta = App::$utente->getKey('ditta');
//                        $segr = Config::getPath('general.itaSegr');
//                        $testo = substr($_POST[$this->nameForm . '_varTestoBase'], 10);
//                        $tipo = $this->segLib->GetPercorsoTesti('15');
//                        $percorsoCompleto = $segr . "segr" . $ditta . "/$tipo/" . $testo;
//                        $variab = $segLibVar->getCorpoTesto($percorsoCompleto);
                        break;
                }
                break;

            case 'returnDocDocumenti':
                $anavar_rec = $this->praLib->GetAnavar($_POST['id'], 'ROWID');
                Out::valore($this->nameForm . '_ANAVAR[VAREXPDOCX]', $anavar_rec['VAREXPDOCX']);
                break;

            case 'returnVariabiliHtml':
                //Out::codice('tinyInsertContent("' . $this->nameForm . '_varHtml","' . $_POST['rowData']['chiave'] . '");');
                Out::codice('tinyInsertContent("' . $this->nameForm . '_varHtml","' . $_POST['rowData']['markupkey'] . '");');
                break;
            case 'returnVariabili':
                Out::codice("$('#" . $this->nameForm . '_varDataset' . "').replaceSelection('" . $_POST['rowData']['chiave'] . "', true);");
                break;
            case 'returnVariabiliTabella':
                Out::codice('tinyInsertContent("' . $this->nameForm . '_varTabella","' . $_POST['rowData']['chiave'] . '");');
                break;
            case 'returnIndice':
                $indice_rec = $this->segLib->GetIndice($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_varTestoBase', 'TESTOBASE:' . $indice_rec['IDELIB'] . '.htm');
                break;
            case 'returnAnavar':
                //$Anavar_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAVAR", true);
                $rowidSel = $_POST['retKey'];
                if ($rowidSel == "") {
                    Out::msgInfo("Export Variabili", "Variabili non trovate");
                    break;
                }

                $rowidSel = $_POST['retKey'];
                $arrRowidSel = explode(",", $rowidSel);

                $docPath = $this->docLib->setDirectory();

                $Anavar_tab = array();
                foreach ($arrRowidSel as $key => $rowid) {
                    $anavar_rec = $this->praLib->GetAnavar($rowid, "rowid");
                    if ($anavar_rec) {
                        $Anavar_tab[$key] = $anavar_rec;
                        if ($anavar_rec['VAREXPDOCX']) {
                            $documenti_rec = $this->docLib->getDocumenti($anavar_rec['VAREXPDOCX']);
                            $filePath = $docPath . $documenti_rec['URI'];
                            $fp = @fopen($filePath, "rb", 0);
                            if ($fp) {
                                $binFile = fread($fp, filesize($filePath));
                                $Anavar_tab[$key]['STREAM'] = base64_encode($binFile);
                            }
                        }
                    }
                }

                $strJson = json_encode($Anavar_tab);
                $nomeFile = "exportAnavar_" . date("Ymd") . ".json";
                file_put_contents(itaLib::getAppsTempPath() . '/' . $nomeFile, $strJson);
                Out::openDocument(
                        utiDownload::getUrl(
                                $nomeFile, itaLib::getAppsTempPath() . '/' . $nomeFile
                        )
                );

                break;
            case 'returnUploadJSON':
                $fileJson = $_POST['uploadedFile'];
                if (file_exists($fileJson)) {
                    if (pathinfo($fileJson, PATHINFO_EXTENSION) == "json") {
                        $strJson = file_get_contents($fileJson);
                        $Anavar_tab = json_decode($strJson, true);
                        $docPath = $this->docLib->setDirectory();
                        $insert_Info = "Inserisco variabili Anavar";
                        $err = false;
                        $trovato = false;
                        $strEsistenti = "";
                        foreach ($Anavar_tab as $key => $Anavar_rec) {
                            $ctr_Anavar_rec = $this->praLib->GetAnavar($Anavar_rec['VARCOD']);
                            if ($ctr_Anavar_rec) {
                                $trovato = true;
                                $strEsistenti .= $Anavar_rec['VARCOD'] . " - " . $Anavar_rec['VARDES'] . "<br>";
                                continue;
                            }
                            $Anavar_new_rec = $Anavar_rec;
                            $Anavar_new_rec['ROWID'] = 0;
                            unset($Anavar_new_rec['STREAM']);

                            /*
                             * Inserisco record su ANAVAR
                             */
                            if (!$this->insertRecord($this->PRAM_DB, 'ANAVAR', $Anavar_new_rec, $insert_Info)) {
                                Out::msgStop("Inserimento Anavar", "Inserimento su ANAVAR fallito per la variabile " . $Anavar_new_rec['VARDES']);
                                $err = true;
                                break;
                            }

                            /*
                             * Se esiste, copio il file fisico
                             */
                            if (isset($Anavar_rec['STREAM']) && $Anavar_rec['STREAM']) {
                                $nomeFile = md5($Anavar_rec['VAREXPDOCX']);
                                if (file_put_contents($docPath . $nomeFile . '.docx', base64_decode($Anavar_rec['STREAM'])) === false) {
                                    Out::msgStop("Importazione Variabili Anavar", "Errore in salvataggio del file allegato $nomeFile.docx --- " . $Anavar_rec['VARDES']);
                                    $err = true;
                                    break;
                                }

                                /*
                                 * Creo e Inserisco il record su DOC_DOCUMENTI
                                 */
                                //$documenti_rec = $this->docLib->getDocumenti($Anavar_rec['VAREXPDOCX']);
                                //if (!$documenti_rec) {
                                $documenti_new_rec = array();
                                $documenti_new_rec['CODICE'] = $Anavar_rec['VAREXPDOCX'];
                                $documenti_new_rec['OGGETTO'] = $Anavar_rec['VARDES'];
                                $documenti_new_rec['CLASSIFICAZIONE'] = 'PRATICHE';
                                $documenti_new_rec['CONTENT'] = $nomeFile;
                                $documenti_new_rec['URI'] = $nomeFile . '.docx'; // . $this->arrSuffix[$documenti_rec['TIPO']];
                                $documenti_new_rec['FUNZIONE'] = 'VARIABILE';
                                $documenti_new_rec['TIPO'] = "DOCX";
                                $documenti_new_rec['DATAREV'] = date('Ymd');
                                $documenti_new_rec['NUMREV'] = 1;
                                //
                                $insert_Info = 'Oggetto: ' . $documenti_new_rec['CODICE'] . " " . $documenti_new_rec['OGGETTO'];
                                if (!$this->insertRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_new_rec, $insert_Info)) {
                                    Out::msgStop("Importazione Variabili Anavar", "Errore inserimento record DOC_DOCUMENTI del documento " . $documenti_new_rec['CODICE'] . " " . $documenti_new_rec['OGGETTO']);
                                    $err = true;
                                    break;
                                }
                                //}
                            }
                        }
                    }
                    if ($strEsistenti) {
                        $msgEsistenti = "<br>Le seguenti variabili non sono state imporatte perchè già presenti:<br>$strEsistenti";
                    }
                    if (!$err) {
                        Out::msgInfo("Importazione Variabili", "La procedura si è conclusa correttamente.$msgEsistenti");
                    } else {
                        Out::msgInfo("Importazione Variabili", "La procedura si è interrotta a causa di errori.$msgEsistenti");
                    }
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
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_varcod');
        Out::valore($this->nameForm . '_vartip', '');
        $Anavar_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAVAR", true);
        Out::show($this->nameForm . '_ImportVariabili');
        if ($Anavar_tab) {
            Out::show($this->nameForm . '_ExportVariabili');
        }
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridAnavar);
        TableView::clearGrid($this->gridAnavar);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_divHtml');
        Out::hide($this->nameForm . '_divDataset');
        Out::hide($this->nameForm . '_divTestoBase');
        Out::hide($this->nameForm . '_divTabella');
        Out::hide($this->nameForm . '_CercaVar1');
        Out::hide($this->nameForm . '_CercaVar2');
        Out::hide($this->nameForm . '_CercaVar3');
        Out::hide($this->nameForm . '_ExportVariabili');
        Out::hide($this->nameForm . '_ImportVariabili');
    }

    public function CreaSql() {
        $sql = "SELECT * FROM ANAVAR WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_varcod'] != "") {
            $codice = $_POST[$this->nameForm . '_varcod'];
            $sql .= " AND VARCOD = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_vardes'] != "") {
            $sql .= " AND VARDES LIKE '%" . addslashes($_POST[$this->nameForm . '_vardes']) . "%'";
        }
        if ($_POST[$this->nameForm . '_vartip'] != "") {
            $codice = $_POST[$this->nameForm . '_vartip'];
            $sql .= " AND VARTIP = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_varcla'] != "") {
            $codice = $_POST[$this->nameForm . '_varcla'];
            $sql .= " AND VARCLA = '" . $codice . "'";
        }
        App::log($sql);
        return $sql;
    }

    public function Dettaglio($indice) {
        $anavar_rec = $this->praLib->GetAnavar($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $anavar_rec['VARCOD'] . " " . $anavar_rec['VARDES'];
        $this->openRecord($this->PRAM_DB, 'ANAVAR', $open_Info);
        $this->Nascondi();
        Out::valori($anavar_rec, $this->nameForm . '_ANAVAR');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::attributo($this->nameForm . '_ANAVAR[VARCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANAVAR[VARDES]');
        TableView::disableEvents($this->gridAnavar);
        $this->setTipo($anavar_rec['VARTIP'], $anavar_rec['VAREXP']);
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_vartip', 1, '', '1', 'TUTTI');
        Out::select($this->nameForm . '_vartip', 1, '01', '0', 'HTML');
        Out::select($this->nameForm . '_vartip', 1, '02', '0', 'VARIABILE');
        Out::select($this->nameForm . '_vartip', 1, '03', '0', 'TESTO BASE');
        Out::select($this->nameForm . '_vartip', 1, '04', '0', 'TABELLA');
        Out::select($this->nameForm . '_ANAVAR[VARTIP]', 1, '01', '0', 'HTML');
        Out::select($this->nameForm . '_ANAVAR[VARTIP]', 1, '02', '0', 'VARIABILE');
        Out::select($this->nameForm . '_ANAVAR[VARTIP]', 1, '03', '0', 'TESTO BASE');
        Out::select($this->nameForm . '_ANAVAR[VARTIP]', 1, '04', '0', 'TABELLA');

        Out::select($this->nameForm . '_ANAVAR[VARFONTE]', 1, '', '0', 'Nessuna');
        foreach (praLibVariabili::$VAR_FONTI_DESCR as $key => $descrizione) {
            Out::select($this->nameForm . '_ANAVAR[VARFONTE]', 1, $key, '0', $descrizione);
        }
    }

    function setTipo($tipo, $valore = '') {
        switch ($tipo) {
            case "01":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varHtml', $valore);
                }
                Out::show($this->nameForm . '_divHtml');
                Out::hide($this->nameForm . '_divDataset');
                Out::hide($this->nameForm . '_divTestoBase');
                Out::hide($this->nameForm . '_divTabella');
                Out::show($this->nameForm . '_CercaVar1');
                Out::hide($this->nameForm . '_CercaVar2');
                Out::hide($this->nameForm . '_CercaVar3');
                Out::codice('tinyActivate("' . $this->nameForm . '_varHtml");');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varTabella");');
                break;
            case "02":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varDataset', $valore);
                }
                Out::hide($this->nameForm . '_divHtml');
                Out::show($this->nameForm . '_divDataset');
                Out::hide($this->nameForm . '_divTestoBase');
                Out::hide($this->nameForm . '_divTabella');
                Out::hide($this->nameForm . '_CercaVar1');
                Out::show($this->nameForm . '_CercaVar2');
                Out::hide($this->nameForm . '_CercaVar3');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtml");');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varTabella");');
                break;
            case "03":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varTestoBase', $valore);
                }
                Out::hide($this->nameForm . '_divHtml');
                Out::hide($this->nameForm . '_divDataset');
                Out::show($this->nameForm . '_divTestoBase');
                Out::hide($this->nameForm . '_divTabella');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtml");');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varTabella");');
                break;
            case "04":
                if ($valore != "") {
                    Out::valore($this->nameForm . '_varTabella', $valore);
                } else {
                    Out::valore($this->nameForm . '_varTabella', $this->modelloVarGriglia());
                }
                Out::hide($this->nameForm . '_divHtml');
                Out::hide($this->nameForm . '_divDataset');
                Out::hide($this->nameForm . '_divTestoBase');
                Out::show($this->nameForm . '_divTabella');
                Out::hide($this->nameForm . '_CercaVar1');
                Out::hide($this->nameForm . '_CercaVar2');
                Out::show($this->nameForm . '_CercaVar3');
                Out::codice('tinyActivate("' . $this->nameForm . '_varTabella");');
                Out::codice('tinyDeActivate("' . $this->nameForm . '_varHtml");');
                break;
        }
        return;
    }

    private function elaboraRecords($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            switch ($result_rec['VARTIP']) {
                case '01':
                    $result_tab[$key]['VARTIP'] = 'HTML';
                    break;
                case '02':
                    $result_tab[$key]['VARTIP'] = 'VARIABILE';
                    break;
                case '03':
                    $result_tab[$key]['VARTIP'] = 'TESTO BASE';
                    break;
                case '04':
                    $result_tab[$key]['VARTIP'] = 'TABELLA';
                    break;
                default:
                    $result_tab[$key]['VARTIP'] = 'NON DEFINITO';
                    break;
            }
        }
        return $result_tab;
    }

    private function modelloVarGriglia() {
        return '<table border="0" frame="box" rules="all" style="width: 465px; height: 69px; border: 0pt solid rgb(0, 0, 0);" class="mceItemTable"><tbody><tr valign="" lang="" height="" bgcolor="" background="" align="" dir="" style="background-color: #ffffcc;" _mce_style="background-color: #ffffcc;"><td style="text-align: center;">Colonna 1</td><td style="text-align: center;">Colonna 2</td><td style="text-align: center;">Colonna 3</td>
            <td style="text-align: center;">Colonna 4</td><td style="text-align: center;">Colonna 5</td></tr><tr><td>@{$variabile1}@</td><td>@{$variabile2}@</td><td>@{$variabile3}@</td>
            <td>@{$variabile4}@</td><td>@{$variabile5}@</td></tr></tbody></table>';
//        return '<table border="0" width="465" height="69"><tbody><tr><td>Colonna 1</td><td>Colonna 2</td><td>Colonna 3</td>
//            <td>Colonna 4</td><td>Colonna 5</td></tr><tr><td>@{$variabile1}@</td><td>@{$variabile2}@</td><td>@{$variabile3}@</td>
//            <td>@{$variabile4}@</td><td>@{$variabile5}@</td></tr></tbody></table>';
    }

    /**
     * Apertura documento esterno
     * @param type $tipo 'MSWORDHTML' | 'XHTML' | 'DOCX'
     * @return boolean
     */
    private function openDocumento($tipo) {
        $anavar_rec = $this->praLib->GetAnavar($_POST[$this->nameForm . '_ANAVAR']['VARCOD']);

        switch ($tipo) {
            case 'DOCX':
                $campo = 'VAREXPDOCX';
                break;
        }

        if (!isset($campo)) {
            return false;
        }

        $codice = $anavar_rec[$campo];

        if (!$codice) {
            $codice = md5($tipo . $anavar_rec['ROWID'] . microtime());
            $anavar_rec[$campo] = $codice;
            if (!$this->updateRecord($this->PRAM_DB, 'ANAVAR', $anavar_rec, '')) {
                Out::msgStop("Errore", "Aggiornamento variabile fallito.");
                return false;
            }
        }

        $praLibVariabili = new praLibVariabili();
        $dictionaryLegend = $praLibVariabili->getLegendaGenerico();

        $FixedFields['CODICE'] = $codice;
        $FixedFields['OGGETTO'] = $anavar_rec['VARDES']; //OGGETTO
        $FixedFields['CLASSIFICAZIONE'] = 'PRATICHE'; //CLASSIFICAZIONE
        $FixedFields['FUNZIONE'] = 'VARIABILE';
        $FixedFields['DICTIONARYLEGEND'] = $dictionaryLegend;
        $FixedFields['TIPO'] = $tipo; //TIPO
        $FixedFields['SCARICA_DOCX'] = false;

        // Apro Form documenti
        $_POST = array();
        $_POST['FixedFields'] = $FixedFields;
        $_POST['TipoAperturaDocumento'] = 'SEGRETERIA';
        $model = 'docDocumenti';
        itaLib::openDialog($model);
        /* @var $objForm docDocumenti */
        $objForm = itaModel::getInstance($model);
        $objForm->setEvent('OpenLockedDoc');
        $objForm->setReturnModel($this->nameForm);
        $objForm->setReturnEvent('returnDocDocumenti');
        $objForm->setReturnId($anavar_rec['ROWID']);
        $objForm->parseEvent();
        return true;
    }

    private function cancellaDocumento($codice) {
        if ($codice) {
            include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
            if ($this->docLib->getDocumenti($codice, 'codice') && !$this->docLib->deleteDocumenti($codice, 'codice')) {
                Out::msgStop('Errore', $this->docLib->getErrMessage());
                return false;
            }
        }

        return true;
    }

}
