<?php

/**
 *
 * ANAGRAFICA TIPI PASSO
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    02.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';

function praClt() {
    $praClt = new praClt();
    $praClt->parseEvent();
    return;
}

class praClt extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $ITALWEB_DB;
    public $workDate;
    public $nameForm = "praClt";
    public $divGes = "praClt_divGestione";
    public $divRis = "praClt_divRisultato";
    public $divRic = "praClt_divRicerca";
    public $gridPraclt = "praClt_gridPraclt";
    public $gridDati = "praClt_gridDati";
    public $gridPannelli = "praClt_gridPannelli";
    public $altriDati = array();
    public $metaFunBO = array();
    public $metaFunFO = array();

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
            $this->altriDati = App::$utente->getKey($this->nameForm . '_altriDati');
            $this->metaFunBO = App::$utente->getKey($this->nameForm . '_metaFunBO');
            $this->metaFunFO = App::$utente->getKey($this->nameForm . '_metaFunFO');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_altriDati', $this->altriDati);
            App::$utente->setKey($this->nameForm . '_metaFunBO', $this->metaFunBO);
            App::$utente->setKey($this->nameForm . '_metaFunFO', $this->metaFunFO);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'dbClickRow': case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridPraclt:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridPannelli:
                        $this->DettaglioPannello($_POST['rowid'], 'Edit');
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridDati:
                        praRic::praRicPraidc($this->nameForm, 'returnPraidc', $_POST[$this->nameForm . '_PRACLT']['CLTCOD']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridPraclt:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridDati:
                        if (array_key_exists($_POST['rowid'], $this->altriDati) == true) {
                            unset($this->altriDati[$_POST['rowid']]);
                        }
                        $this->CaricaGriglia($this->gridDati, $this->altriDati);
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridPraclt, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('CLTDES');
                $ita_grid01->exportXLS('', 'Praclt.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE'], 'stampaDatiAgg' => 'SI');
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praClt', $parameters);
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridPraclt:
                        $tableSortOrder = $_POST['sord'];
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridPraclt, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(1000);
                        $ita_grid01->setSortIndex('CLTDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
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
                            TableView::enableEvents($this->gridPraclt);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->altriDati = array();
                        Out::attributo($this->nameForm . '_PRACLT[CLTCOD]', 'readonly', '1');
                        Out::hide($this->divRic, '');
                        Out::hide($this->divRis, '');
                        Out::show($this->divGes, '');
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_PRACLT[CLTCOD]');
                        Out::codice('tinyActivate("' . $this->nameForm . '_Messaggio");');
                        Out::codice('tinyActivate("' . $this->nameForm . '_MsgBoxFO");');
                        Out::hide($this->nameForm . '_divMetaFunBO');
                        Out::hide($this->nameForm . '_divMetaFunFO');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_PRACLT']['CLTCOD'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $_POST[$this->nameForm . '_PRACLT']['CLTCOD'] = $codice;
                        try {   // Effettuo la FIND
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            if (!$Praclt_rec) {
                                $Praclt_rec = $_POST[$this->nameForm . '_PRACLT'];
                                if (!$this->InserisciDatiAggiuntivi($Praclt_rec['CLTCOD'])) {
                                    Out::msgStop("Errore", "inserimento dati aggiunti fallito");
                                    break;
                                }


                                $arrMetadati = "";
                                if ($_POST[$this->nameForm . '_Messaggio']) {
                                    $arrMetadati['MESSAGGIOFO'] = $_POST[$this->nameForm . '_Messaggio'];
                                }
                                if ($_POST[$this->nameForm . '_MsgBoxFO']) {
                                    $arrMetadati['MSGBOXFO'] = $_POST[$this->nameForm . '_MsgBoxFO'];
                                }

                                if ($Praclt_rec['CLTOPE']) {
                                    $arrMetadati['METAOPE'] = $this->SalvaMetadatiFunzionePassoBO($Praclt_rec['CLTOPE']);
                                }

                                if ($Praclt_rec['CLTOPEFO']) {
                                    $arrMetadati['METAOPEFO'] = $this->SalvaMetadatiFunzionePassoFO($Praclt_rec['CLTOPEFO']);
                                }

                                if (is_array($arrMetadati)) {
                                    $Praclt_rec['CLTMETA'] = serialize($arrMetadati);
                                }


                                $Praclt_rec = $this->praLib->SetMarcaturaTipoPasso($Praclt_rec, true);
                                if ($Praclt_rec['CLTGESTPANEL'] == 1) {
                                    $Praclt_rec['CLTMETAPANEL'] = json_encode(praLibPasso::$PANEL_LIST);
                                }
                                $insert_Info = 'Oggetto: ' . $Praclt_rec['CLTCOD'] . " " . $Praclt_rec['CLTDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'PRACLT', $Praclt_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } else {
                                Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_PRACLT[CLTCOD]');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su Tipi di Passi.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Praclt_rec = $_POST[$this->nameForm . '_PRACLT'];
                        $Praclt_rec_ctr = $this->praLib->GetPraclt($Praclt_rec['ROWID'], 'rowid');
                        $codice = $Praclt_rec['CLTCOD'];
                        $Praclt_rec['CLTCOD'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        if (!$this->InserisciDatiAggiuntivi($Praclt_rec['CLTCOD'])) {
                            Out::msgStop("Errore", "inserimento dati aggiunti fallito");
                            break;
                        }

                        $metadati = unserialize($Praclt_rec_ctr['CLTMETA']);
                        //if ($_POST[$this->nameForm . '_Messaggio']) {
                        $metadati['MESSAGGIOFO'] = $_POST[$this->nameForm . '_Messaggio'];
                        $metadati['MSGBOXFO'] = $_POST[$this->nameForm . '_MsgBoxFO'];
                        //}
                        if ($Praclt_rec['CLTOPE']) {
                            $metadati['METAOPE'] = $this->SalvaMetadatiFunzionePassoBO($Praclt_rec['CLTOPE']);
                        } else {
                            unset($metadati['METAOPE']);
                        }
                        if ($Praclt_rec['CLTOPEFO']) {
                            $metadati['METAOPEFO'] = $this->SalvaMetadatiFunzionePassoFO($Praclt_rec['CLTOPEFO']);
                        } else {
                            unset($metadati['METAOPEFO']);
                        }
                        if (is_array($metadati)) {
                            $Praclt_rec['CLTMETA'] = serialize($metadati);
                        }
                        if ($Praclt_rec_ctr['CLTMETAPANEL'] && $Praclt_rec['CLTGESTPANEL'] == 0) {
                            $Praclt_rec['CLTMETAPANEL'] = '';
                        }
                        if (!$Praclt_rec_ctr['CLTMETAPANEL'] && $Praclt_rec['CLTGESTPANEL'] == 1) {
                            $Praclt_rec['CLTMETAPANEL'] = json_encode(praLibPasso::$PANEL_LIST);
                        }

                        $Praclt_rec = $this->praLib->SetMarcaturaTipoPasso($Praclt_rec);
                        $update_Info = 'Oggetto: ' . $Praclt_rec['CLTCOD'] . " " . $Praclt_rec['CLTDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'PRACLT', $Praclt_rec, $update_Info)) {
                            $this->Dettaglio($Praclt_rec['ROWID']);
                            //  $this->OpenRicerca();
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
                        $Praclt_rec = $_POST[$this->nameForm . '_PRACLT'];
                        try {
                            $this->CancellaDatiAggiuntivi($Praclt_rec['CLTCOD']);
                            $delete_Info = 'Oggetto: ' . $Praclt_rec['CLTCOD'] . " " . $Praclt_rec['CLTDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'PRACLT', $Praclt_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su Tipi di Passi.", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_ConfermaPannello':
                        $_POST[$this->nameForm . '_FlagAttiva'];
                        $record = $this->AggiornaParametri($_POST[$this->nameForm . '_rowid'], $_POST[$this->nameForm . '_FlagAttiva']);
                        $this->CaricaGriglia($this->gridPannelli, $record);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Cltcod':
                        $codice = $_POST[$this->nameForm . '_Cltcod'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            if ($Praclt_rec) {
                                $this->Dettaglio($Praclt_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_PRACLT[CLTCOD]':
                        $codice = $_POST[$this->nameForm . '_PRACLT']['CLTCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_PRACLT[CLTCOD]', $codice);
                        }
                        break;
                    case $this->nameForm . '_Idckey':
                        foreach ($this->altriDati as $dati) {
                            if ($dati['PASIDC'] == $_POST[$this->nameForm . '_Idckey']) {
                                $trovato = true;
                                break;
                            }
                        }
                        if (!$trovato) {
                            $Praidc_rec = $this->praLib->GetPraidc($_POST[$this->nameForm . '_Idckey'], 'codice');
                            if ($Praidc_rec) {
                                $nuovo_campo = array();
                                $nuovo_campo["PASIDC"] = $Praidc_rec['IDCKEY'];
                                $nuovo_campo["PASSEQ"] = $Praidc_rec['IDCSEQ'];
                                $nuovo_campo["IDCDES"] = $Praidc_rec['IDCDES'];
                                $this->altriDati[] = $nuovo_campo;
                                $this->CaricaGriglia($this->gridDati, $this->altriDati);
                            }
                        }
                        Out::valore($this->nameForm . '_Idckey', '');
                        Out::setFocus('', $this->nameForm . '_Idckey');
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PRACLT[CLTGESTPANEL]':
                        if ($_POST[$this->nameForm . '_PRACLT']['CLTGESTPANEL'] == 1) {
                            $Praclt_rec = $this->praLib->GetPraclt($_POST[$this->nameForm . '_PRACLT']['CLTCOD']);
                            if ($Praclt_rec['CLTMETAPANEL']) {

                                $valueAnagrafica = $this->praLib->decodParametriPasso($Praclt_rec['CLTMETAPANEL'], 'Anagrafica');
                            } else {
                                $valueAnagrafica = praLibPasso::$PANEL_LIST;
                            }
                            Out::show($this->nameForm . '_divModuli');
                            $this->CaricaGriglia($this->gridPannelli, $valueAnagrafica);
                        } else {
                            Out::hide($this->nameForm . '_divModuli');
                        }
                        break;

                    case $this->nameForm . '_PRACLT[CLTOPE]':
                        $valueFunzione = $_POST[$this->nameForm . '_PRACLT']['CLTOPE'];
                        if (!$valueFunzione) {
                            Out::hide($this->nameForm . '_divMetaFunBO');
                            break;
                        }

                        $this->CaricaMetadatiFunzionePassoBO($valueFunzione);
                        if (!count($this->metaFunBO)) {
                            Out::hide($this->nameForm . '_divMetaFunBO');
                            break;
                        }

                        Out::show($this->nameForm . '_divMetaFunBO');
                        $this->CaricaGriglia($this->nameForm . '_gridMetaFunBO', $this->metaFunBO, 1, 1000);
                        break;

                    case $this->nameForm . '_PRACLT[CLTOPEFO]':
                        $valueFunzione = $_POST[$this->nameForm . '_PRACLT']['CLTOPEFO'];
                        if (!$valueFunzione) {
                            Out::hide($this->nameForm . '_divMetaFunFO');
                            break;
                        }

                        $this->CaricaMetadatiFunzionePassoFO($valueFunzione);
                        if (!count($this->metaFunFO)) {
                            Out::hide($this->nameForm . '_divMetaFunFO');
                            break;
                        }

                        Out::show($this->nameForm . '_divMetaFunFO');
                        $this->CaricaGriglia($this->nameForm . '_gridMetaFunFO', $this->metaFunFO, 1, 1000);
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridMetaFunBO':
                        $rowid = $_POST['rowid'];
                        $cellname = $_POST['cellname'];
                        $this->metaFunBO[$rowid][$cellname] = $_POST['value'];
                        break;

                    case $this->nameForm . '_gridMetaFunFO':
                        $rowid = $_POST['rowid'];
                        $cellname = $_POST['cellname'];
                        $this->metaFunFO[$rowid][$cellname] = $_POST['value'];
                        break;
                }
                break;
            case 'returnPraidc':
                $Praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], 'rowid');
                foreach ($this->altriDati as $dati) {
                    if ($dati['PASIDC'] == $Praidc_rec['IDCKEY']) {
                        $trovato = true;
                        break;
                    }
                }
                if (!$trovato) {
                    $nuovo_campo = array();
                    $nuovo_campo["PASIDC"] = $Praidc_rec['IDCKEY'];
                    $nuovo_campo["PASSEQ"] = $Praidc_rec['IDCSEQ'];
                    $nuovo_campo["IDCDES"] = $Praidc_rec['IDCDES'];
                    $this->altriDati[] = $nuovo_campo;
                    $this->CaricaGriglia($this->gridDati, $this->altriDati);
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Cltdes':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $praclt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE " . $this->PRAM_DB->strLower('CLTDES') . " LIKE '%" . strtolower(addslashes(itaSuggest::getQuery()))
                                        . "%'", true);

                        foreach ($praclt_tab as $praclt_rec) {
                            itaSuggest::addSuggest($praclt_rec['CLTDES'], array($this->nameForm . "_Cltcod" => $praclt_rec['CLTCOD']));
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'embedVars':
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                $praLibVar = new praLibVariabili();
                $dictionaryLegend = $praLibVar->getLegendaPratica('adjacency', 'smarty');

                switch ($_POST['id']) {
                    case $this->nameForm . '_Messaggio':
                        docRic::ricVariabili($dictionaryLegend, $this->nameForm, "returnRicVariabili", true);
                        break;
                    case $this->nameForm . '_MsgBoxFO':
                        docRic::ricVariabili($dictionaryLegend, $this->nameForm, "returnRicVarBoxFO", true);
                        break;
                }
                break;
            case "returnRicVariabili":
                Out::codice('tinyInsertContent("' . $this->nameForm . '_Messaggio","' . $_POST["rowData"]['markupkey'] . '");');
                break;
            case "returnRicVarBoxFO":
                Out::codice('tinyInsertContent("' . $this->nameForm . '_MsgBoxFO","' . $_POST["rowData"]['markupkey'] . '");');
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_altriDati');
        App::$utente->removeKey($this->nameForm . '_metaFunFO');
        App::$utente->removeKey($this->nameForm . '_metaFunBO');
        $this->close = true;
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
        Out::html($this->nameForm . "_PRACLT[CLTOPE]", '');
        Out::html($this->nameForm . "_PRACLT[CLTOPEFO]", '');
        praRic::ricComboFunzioni_base($this->nameForm . "_PRACLT[CLTOPE]");
        praRic::ricComboFunzioni_front_office($this->nameForm . "_PRACLT[CLTOPEFO]");
        Out::setFocus('', $this->nameForm . '_Cltcod');
    }

    function AzzeraVariabili() {
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridPraclt);
        TableView::clearGrid($this->gridPraclt);
        TableView::disableEvents($this->gridDati);
        TableView::clearGrid($this->gridDati);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_divModuli');
    }

    public function CreaSql() {
        $sql = "SELECT * FROM PRACLT WHERE CLTCOD=CLTCOD";
        if ($_POST[$this->nameForm . '_Cltcod'] != "") {
            $sql .= " AND CLTCOD = '" . $_POST[$this->nameForm . '_Cltcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Cltdes'] != "") {
            $sql .= " AND " . $this->PRAM_DB->strUpper('CLTDES') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Cltdes'])) . "%'";
        }
        return $sql;
    }

    public function Dettaglio($_Indice) {
        $Praclt_rec = $this->praLib->GetPraclt($_Indice, 'rowid');
        $metadati = unserialize($Praclt_rec['CLTMETA']);
        $open_Info = 'Oggetto: ' . $Praclt_rec['CLTCOD'] . " " . $Praclt_rec['CLTDES'];
        $this->openRecord($this->PRAM_DB, 'PRACLT', $open_Info);
        $this->visualizzaMarcatura($Praclt_rec);
        $this->Nascondi();
        Out::valori($Praclt_rec, $this->nameForm . '_PRACLT');
        Out::valore($this->nameForm . '_Messaggio', $metadati['MESSAGGIOFO']);
        Out::valore($this->nameForm . '_MsgBoxFO', $metadati['MSGBOXFO']);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        $this->CaricoCampiAggiuntivi($Praclt_rec['CLTCOD']);
        Out::attributo($this->nameForm . '_PRACLT[CLTCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_PRACLT[CLTDES]');
        TableView::disableEvents($this->gridPraclt);
        Out::codice('tinyActivate("' . $this->nameForm . '_Messaggio");');
        Out::codice('tinyActivate("' . $this->nameForm . '_MsgBoxFO");');
        if ($Praclt_rec['CLTGESTPANEL'] == 1) {
            if ($Praclt_rec['CLTMETAPANEL']) {
                $valueAnagrafica = $this->praLib->decodParametriPasso($Praclt_rec['CLTMETAPANEL'], 'Anagrafica');
            } else {
                $valueAnagrafica = praLibPasso::$PANEL_LIST;
            }
            Out::show($this->nameForm . '_divModuli');
            // Out::msgInfo('record elaborato', print_r($valueAnagrafica,true));
            $this->CaricaGriglia($this->gridPannelli, $valueAnagrafica);
        }

        Out::hide($this->nameForm . '_divMetaFunBO');
        if ($Praclt_rec['CLTOPE']) {
            $this->CaricaMetadatiFunzionePassoBO($Praclt_rec['CLTOPE']);

            if (count($this->metaFunBO)) {
                Out::show($this->nameForm . '_divMetaFunBO');

                foreach ($this->metaFunBO as $k => $metafunbo_rec) {
                    if ($metadati['METAOPE'][$metafunbo_rec['CHIAVE']]) {
                        $this->metaFunBO[$k]['VALORE'] = $metadati['METAOPE'][$metafunbo_rec['CHIAVE']];
                    }
                }

                $this->CaricaGriglia($this->nameForm . '_gridMetaFunBO', $this->metaFunBO, 1, 1000);
            }
        }

        Out::hide($this->nameForm . '_divMetaFunFO');
        if ($Praclt_rec['CLTOPEFO']) {
            $this->CaricaMetadatiFunzionePassoFO($Praclt_rec['CLTOPEFO']);

            if (count($this->metaFunFO)) {
                Out::show($this->nameForm . '_divMetaFunFO');

                foreach ($this->metaFunFO as $k => $metafunfo_rec) {
                    if ($metadati['METAOPEFO'][$metafunfo_rec['CHIAVE']]) {
                        $this->metaFunFO[$k]['VALORE'] = $metadati['METAOPEFO'][$metafunfo_rec['CHIAVE']];
                    }
                }

                $this->CaricaGriglia($this->nameForm . '_gridMetaFunFO', $this->metaFunFO, 1, 1000);
            }
        }
    }

    public function visualizzaMarcatura($Praclt_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Praclt_rec['CLTUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Praclt_rec['CLTUPDDATE'])) . ' ' . $Praclt_rec['CLTUPDTIME'] . '  </span>');
    }

    public function CaricaMetadatiFunzionePassoBO($funzione) {
        $this->metaFunBO = array();

        if (!praFunzionePassi::$FUNZIONI_BASE[$funzione]) {
            return false;
        }

        foreach (praFunzionePassi::$FUNZIONI_BASE[$funzione]['METADATI'] as $key => $description) {
            $this->metaFunBO[] = array(
                'CHIAVE' => $key,
                'DESCRIZIONE' => $description
            );
        }
    }

    public function CaricaMetadatiFunzionePassoFO($funzioneFO) {
        $this->metaFunFO = array();

        if (!praFunzionePassi::$FUNZIONI_FRONT_OFFICE[$funzioneFO]) {
            return false;
        }

        foreach (praFunzionePassi::$FUNZIONI_FRONT_OFFICE[$funzioneFO]['METADATI'] as $key => $description) {
            $this->metaFunFO[] = array(
                'CHIAVE' => $key,
                'DESCRIZIONE' => $description
            );
        }

        switch ($funzioneFO) {
            case praFunzionePassi::FUN_FO_ANA_SOGGETTO:
                foreach (array_keys(praRuolo::$SUBJECT_BASE_FIELDS) as $keyCampo) {
                    $this->metaFunFO[] = array(
                        'CHIAVE' => 'ALIAS_' . $keyCampo,
                        'DESCRIZIONE' => "Alias per il campo $keyCampo"
                    );
                }
                break;
        }
    }

    public function SalvaMetadatiFunzionePassoBO($funzioneBO) {
        $metadatiCLTOPE = array();

        if (!praFunzionePassi::$FUNZIONI_BASE[$funzioneBO]) {
            return false;
        }

        foreach ($this->metaFunBO as $metafunbo_rec) {
            $metadatiCLTOPE[$metafunbo_rec['CHIAVE']] = $metafunbo_rec['VALORE'];
        }

        return $metadatiCLTOPE;
    }

    public function SalvaMetadatiFunzionePassoFO($funzioneFO) {
        $metadatiCLTOPEFO = array();

        if (!praFunzionePassi::$FUNZIONI_FRONT_OFFICE[$funzioneFO]) {
            return false;
        }

        foreach ($this->metaFunFO as $metafunfo_rec) {
            $metadatiCLTOPEFO[$metafunfo_rec['CHIAVE']] = $metafunfo_rec['VALORE'];
        }

        return $metadatiCLTOPEFO;
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

    public function CaricoCampiAggiuntivi($codice) {
        $sql = "SELECT
                PASDAG.ROWID AS ROWID,
                PASDAG.PASIDC AS PASIDC,
                PASDAG.PASSEQ AS PASSEQ,
                PRAIDC.IDCDES AS IDCDES
            FROM
                PASDAG
            LEFT OUTER JOIN PRAIDC  ON PRAIDC.IDCKEY=PASDAG.PASIDC
                WHERE PASCOD = '" . $codice . "' ORDER BY PASSEQ";
        $this->altriDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->CaricaGriglia($this->gridDati, $this->altriDati);
    }

    function CancellaDatiAggiuntivi($codice) {
        $sql = "SELECT * FROM PASDAG WHERE PASCOD ='$codice'";
        $Pasdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        foreach ($Pasdag_tab as $Pasdag_rec) {
//            $nrow = ItaDB::DBDelete($this->PRAM_DB, "PASDAG", "ROWID", $Pasdag_rec['ROWID']);

            $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo ' . $Pasdag_rec['PASCOD'] . " - " . $Pasdag_rec['PASIDC'];
            $this->deleteRecord($this->PRAM_DB, 'PASDAG', $Pasdag_rec['ROWID'], $delete_Info);
        }
    }

    function InserisciDatiAggiuntivi($codice) {
        $this->CancellaDatiAggiuntivi($codice);
        $seq = 0;
        foreach ($this->altriDati as $dati) {
            $seq += 10;
            $Pasdag_rec = array();
            $Pasdag_rec['PASCOD'] = $codice;
            $Pasdag_rec['PASIDC'] = $dati['PASIDC'];
            $Pasdag_rec['PASSEQ'] = $seq;
            $insert_Info = 'Oggetto : Inserisco dato aggiuntivo ' . $Pasdag_rec['PASDIC'];
            if (!$this->insertRecord($this->PRAM_DB, 'PASDAG', $Pasdag_rec, $insert_Info)) {
                return false;
            }
        }
        return true;
    }

    public function DettaglioPannello($rowid, $tipo = '') {

        $Param_rec = praLibPasso::$PANEL_LIST[$rowid];
        $Clt_rec = $this->praLib->GetPraclt($_POST[$this->nameForm . '_PRACLT']['CLTCOD'], 'codice');
        if ($Clt_rec['CLTMETAPANEL']) {
            $metadato = $this->praLib->decodParametriPasso($Clt_rec['CLTMETAPANEL']);
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

    public function AggiornaParametri($rowid, $flag) {
        $Clt_rec = $this->praLib->GetPraclt($_POST[$this->nameForm . '_PRACLT']['CLTCOD'], 'codice');
        if (!$Clt_rec['CLTMETAPANEL']) {
            $parametro = praLibPasso::$PANEL_LIST;
            $parametro[$rowid]['DEF_STATO'] = $flag;
        } else {
            $parametro = $this->praLib->decodParametriPasso($Clt_rec['CLTMETAPANEL'], 'Anagrafica');
            $parametro[$rowid]['DEF_STATO'] = $flag;
        }
        $Clt_rec['CLTMETAPANEL'] = json_encode($parametro);
        if (!$this->updateRecord($this->PRAM_DB, 'PRACLT', $Clt_rec, $update_Info)) {
            Out::msgStop('ATTANZIONE', 'Salvataggio Parametri non riuscito');
            return false;
        }
        return $parametro;
    }

}
