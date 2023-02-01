<?php

/**
 *
 * ANAGRAFICA DISCIPLINE SANZIONATORIE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    09.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praDiscipline() {
    $praDiscipline = new praDiscipline();
    $praDiscipline->parseEvent();
    return;
}

class praDiscipline extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $disciplina;
    public $nameForm = "praDiscipline";
    public $divGes = "praDiscipline_divGestione";
    public $divRis = "praDiscipline_divRisultato";
    public $divRic = "praDiscipline_divRicerca";
    public $gridDiscipline = "praDiscipline_gridDiscipline";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->disciplina = App::$utente->getKey($this->nameForm . '_disciplina');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_disciplina', $this->disciplina);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                $this->CreaCombo();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDiscipline:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDiscipline:
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
                $ita_grid01 = new TableView($this->gridDiscipline,
                                array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DISDES');
                $ita_grid01->exportXLS('', 'discipline.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                //$Result_tab=$this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praDiscipline', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridDiscipline,
                                            array(
                                                'sqlDB' => $this->PRAM_DB,
                                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('DISDES');
                            $Result_tab = $ita_grid01->getDataArray();
                            //$Result_tab=$this->elaboraRecord($Result_tab);
                            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridDiscipline);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::attributo($this->nameForm . '_ANADIS[DISCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANADIS[DISCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANADIS']['DISCOD'];
                        $_POST[$this->nameForm . '_ANADIS']['DISCOD'] = $codice;
                        $Anadis_ric = $this->praLib->GetAnadis($codice);
                        if (!$Anadis_ric) {
                            $Anadis_ric = $_POST[$this->nameForm . '_ANADIS'];
                            try {
                                $Anadis_ric = $this->praLib->SetMarcaturaDisciplina($Anadis_ric, true);
                                $insert_Info = 'Oggetto: ' . $Anadis_ric['DISCOD'] . $Anadis_ric['DISDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANADIS', $Anadis_ric, $insert_Info)) {
                                    $anadis_rec = $this->praLib->getAnadis($codice);
                                    if ($anadis_rec) {
                                        $this->Dettaglio($anadis_rec['ROWID']);
                                    }
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANADIS[DISCOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        if (!$this->Aggiorna($_POST[$this->nameForm . '_ANADIS'])) {
                            Out::msgStop("Aggiornamento disciplina", "Errore in aggiornamento su ANADIS");
                            break;
                        }
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $anadis_rec = $_POST[$this->nameForm . '_ANADIS'];
                        try {
                            $delete_Info = 'Oggetto: ' . $anadis_rec['DISCOD'] . $anadis_rec['DISDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANADIS', $anadis_rec['ROWID'], $delete_Info)) {
                                //$this->cancellaTesto();
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA DISCIPLINE", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_ANADIS[DISFIL]_butt':
                        if ($_POST[$this->nameForm . '_ANADIS']['DISFIL'] != '') {
                            $ditta = App::$utente->getKey('ditta');
                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/discipline/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $_POST[$this->nameForm . '_ANADIS']['DISFIL'], $destinazione . $_POST[$this->nameForm . '_ANADIS']['DISFIL']
                                    )
                            );
                        }

                        break;
                    case $this->nameForm . '_FileLocaleTesto':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadFile";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Discod':
                        $codice = $_POST[$this->nameForm . '_Discod'];
                        if (trim($codice) != "") {
                            $anadis_rec = $this->praLib->GetAnadis($codice);
                            if ($anadis_rec) {
                                $this->Dettaglio($anadis_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Discod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANADIS[DISCOD]':
                        $codice = $_POST[$this->nameForm . '_ANADIS']['DISCOD'];
                        if (trim($codice) != "") {
                            Out::valore($this->nameForm . '_ANADIS[DISCOD]', $codice);
                        }
                        break;
                }
                break;
            case 'returnUploadFile':
                $this->AllegaTesto();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_disciplina');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "SELECT * FROM ANADIS WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm . '_Discod'] != "") {
            $sql .= " AND DISCOD LIKE '%" . addslashes($_POST[$this->nameForm . '_Discod']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Disdes'] != "") {
            $sql .= " AND DISDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Disdes']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDiscipline);
        TableView::clearGrid($this->gridDiscipline);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Discod');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_FileLocaleTesto');
    }

    public function Dettaglio($Indice) {
        $anadis_rec = $this->praLib->GetAnadis($Indice, 'rowid');
        $this->disciplina = $anadis_rec['DISCOD'];
        $open_Info = 'Oggetto: ' . $anadis_rec['DISCOD'] . " " . $anadis_rec['DISDES'];
        $this->openRecord($this->PRAM_DB, 'ANADIS', $open_Info);
        $this->visualizzaMarcatura($anadis_rec);
        $this->Nascondi();
        Out::valori($anadis_rec, $this->nameForm . '_ANADIS');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_FileLocaleTesto');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANADIS[DISDES]');
        Out::attributo($this->nameForm . '_ANADIS[DISCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridDiscipline);
        Out::setFocus('', $this->nameForm . '_ANADIS[DISDES]');
    }

    public function visualizzaMarcatura($anadis_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $anadis_rec['DISUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($anadis_rec['DISUPDDATE'])) . ' ' . $anadis_rec['DISUPDTIME'] . '  </span>');
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_ANANOR[NORTIP]', 1, "", "0", "");
        Out::select($this->nameForm . '_ANANOR[NORTIP]', 1, "Nazionale", "0", "NAZIONALE");
        Out::select($this->nameForm . '_ANANOR[NORTIP]', 1, "Regionale", "0", "REGIONALE");
        Out::select($this->nameForm . '_ANANOR[NORTIP]', 1, "Comunale", "0", "COMUNALE");
    }

    public function Aggiorna($anadis_rec) {
        $anadis_rec = $this->praLib->SetMarcaturaDisciplina($anadis_rec);
        $update_Info = 'Oggetto: ' . $anadis_rec['DISCOD'] . $anadis_rec['DISDES'];
        if (!$this->updateRecord($this->PRAM_DB, 'ANADIS', $anadis_rec, $update_Info)) {
            return false;
        }
        return true;
    }

    function AllegaTesto() {
        $origFile = $_POST['uploadedFile'];
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/discipline/';
        if (!is_dir($destinazione)) {
            Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: ' . $destinazione);
            return false;
        }
        $ext = "." . pathinfo($origFile, PATHINFO_EXTENSION);
        $nomeFile = $destinazione . $this->disciplina . $ext;
        if ($nomeFile != '') {
            if (!@rename($origFile, $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm . '_ANADIS[DISFIL]', $this->disciplina . $ext);
                $Anadis_rec = $this->praLib->GetAnadis($this->disciplina);
                $Anadis_rec["DISFIL"] = $this->disciplina . $ext;
                if (!$this->Aggiorna($Anadis_rec)) {
                    Out::msgStop("Aggiornamento disciplina", "Errore in aggiornamento su ANADIS");
                    return false;
                }
                Out::msgInfo("Aggiornamento Disciplina", "Testo Associato modificato e record aggiornato");
                $this->Dettaglio($Anadis_rec['ROWID']);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

}

?>