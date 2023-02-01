<?php

/**
 *
 * GESTIONE EMAIL
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.02.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proTipoDoc.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function proAnaTipoDoc() {
    $proAnaTipoDoc = new proAnaTipoDoc();
    $proAnaTipoDoc->parseEvent();
    return;
}

class proAnaTipoDoc extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $utiEnte;
    public $nameForm = "proAnaTipoDoc";
    public $divGes = "proAnaTipoDoc_divGestione";
    public $divRis = "proAnaTipoDoc_divRisultato";
    public $divRic = "proAnaTipoDoc_divRicerca";
    public $gridDocumenti = "proAnaTipoDoc_gridDocumenti";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
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
                if (!proTipoDoc::initSistemSubjectDoc($this->proLib)) {
                    Out::msgStop("Attenzione!!!", "Errore inizializzazione ruoli");
                    break;
                }
                $this->OpenRicerca();
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
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESDES');
                $ita_grid01->exportXLS('', 'ruoli.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaTipoDoc', $parameters);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $this->Elenca();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $AnaTipoDoc_rec = $_POST[$this->nameForm . '_ANATIPODOC'];
                        $delete_Info = 'Oggetto: ' . $AnaTipoDoc_rec['CODICE'] . $AnaTipoDoc_rec['DESCRIZIONE'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANATIPODOC', $AnaTipoDoc_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_ANATIPODOC[OGGASSOCIATO]_butt':
                        proRic::proRicOgg($this->nameForm, '');
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
                        if (trim($codice) != "") {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($codice);
                            if ($AnaTipoDoc_rec) {
                                $this->Dettaglio($AnaTipoDoc_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Codice', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANATIPODOC[CODICE]':
                        $codice = $_POST[$this->nameForm . '_ANATIPODOC']['CODICE'];
                        if (trim($codice) != "") {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANATIPODOC[CODICE]', $codice);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANATIPODOC[OGGASSOCIATO]':
                        if ($_POST[$this->nameForm . '_ANATIPODOC']['OGGASSOCIATO']) {
                            $Anadog_rec = $this->proLib->GetAnadog($_POST[$this->nameForm . '_ANATIPODOC']['OGGASSOCIATO'], 'codice');
                            if ($Anadog_rec) {
                                Out::valore($this->nameForm . '_ANATIPODOC[OGGASSOCIATO]', $Anadog_rec['DOGCOD']);
                            } else {
                                Out::msgInfo('Attenzione', 'Codice Oggetto inesistente.');
                                Out::valore($this->nameForm . '_ANATIPODOC[OGGASSOCIATO]', '');
                            }
                        }
                        break;
                }
                break;

            case 'returndog':
                $Anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ANATIPODOC[OGGASSOCIATO]', $Anadog_rec['DOGCOD']);
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

    function CreaSql() {
        $sql = "SELECT * FROM ANATIPODOC WHERE 1 = 1";
        if ($_POST[$this->nameForm . '_Ruood'] != "") {
            $sql .= " AND CODICE = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND DESCRIZIONE LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDocumenti);
        TableView::clearGrid($this->gridDocumenti);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
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

    public function Dettaglio($Indice) {
        $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $AnaTipoDoc_rec['CODICE'] . " " . $AnaTipoDoc_rec['DESCRIZIONE'];
        $this->openRecord($this->PROT_DB, 'ANATIPODOC', $open_Info);
        $this->Nascondi();
        Out::valori($AnaTipoDoc_rec, $this->nameForm . '_ANATIPODOC');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANATIPODOC[DESCRIZIONE]');
        Out::attributo($this->nameForm . '_ANATIPODOC[CODICE]', 'readonly', '0');
        TableView::disableEvents($this->gridDocumenti);
    }

    private function Elenca() {
        $sql = $this->CreaSql();
        try {
            $ita_grid01 = new TableView($this->gridDocumenti, array(
                'sqlDB' => $this->PROT_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(10000);
            $ita_grid01->setSortIndex('DESCRIZIONE');
            $Result_tab = $ita_grid01->getDataArray();
            //$Result_tab=$this->elaboraRecord($Result_tab);
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                Out::msgStop("Selezione", "Nessun record trovato.");
                $this->OpenRicerca();
            } else {
                Out::hide($this->divGes, '');
                Out::hide($this->divRic, '');
                Out::show($this->divRis, '');
                $this->Nascondi();
                Out::show($this->nameForm . '_AltraRicerca');
                Out::show($this->nameForm . '_Nuovo');
                Out::setFocus('', $this->nameForm . '_Nuovo');
                TableView::enableEvents($this->gridDocumenti);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    private function Nuovo() {
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::attributo($this->nameForm . '_ANATIPODOC[CODICE]', 'readonly', '1');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_ANATIPODOC[CODICE]');
    }

    private function Aggiungi() {
        $codice = str_pad($_POST[$this->nameForm . '_ANATIPODOC']['CODICE'], 4, "0", STR_PAD_LEFT);
//        if (!proTipoDoc::isConfigurable($codice)) {
//            Out::msgInfo("Attenzione!!", "il codice $codice non può essere utilizzato.<br>Utilizzare un codice compreso tra " . proTipoDoc::$CONFIGURABLE_DOC_FROM_CODE . " e " . proTipoDoc::$CONFIGURABLE_DOC_TO_CODE . "");
//            return false;
//        }
        $AnaTipoDoc_ric = $this->proLib->GetAnaTipoDoc($codice, 'codice');
        if (!$AnaTipoDoc_ric) {
            $AnaTipoDoc_ric = $_POST[$this->nameForm . '_ANATIPODOC'];
            $insert_Info = 'Oggetto: ' . $AnaTipoDoc_ric['CODICE'] . $AnaTipoDoc_ric['DESCRIZIONE'];
            if ($this->insertRecord($this->PROT_DB, 'ANATIPODOC', $AnaTipoDoc_ric, $insert_Info)) {
                $this->OpenRicerca();
            }
        } else {
            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
            Out::setFocus('', $this->nameForm . '_ANATIPODOC[CODICE]');
        }
    }

    private function Aggiorna() {
        $AnaTipoDoc_rec = $_POST[$this->nameForm . '_ANATIPODOC'];
        $update_Info = 'Oggetto: ' . $AnaTipoDoc_rec['CODICE'] . $AnaTipoDoc_rec['DESCRIZIONE'];
        if ($this->updateRecord($this->PROT_DB, 'ANATIPODOC', $AnaTipoDoc_rec, $update_Info)) {
            $this->OpenRicerca();
        }
    }

}

?>