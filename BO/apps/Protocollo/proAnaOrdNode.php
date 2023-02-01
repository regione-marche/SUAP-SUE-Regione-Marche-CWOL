<?php

/**
 *
 * GESTIONE EMAIL
 *
 * PHP Version 5
 *
 * @category
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    06.05.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proTipoNode.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function proAnaOrdNode() {
    $proAnaOrdNode = new proAnaOrdNode();
    $proAnaOrdNode->parseEvent();
    return;
}

class proAnaOrdNode extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $utiEnte;
    public $nameForm = "proAnaOrdNode";
    public $divGes = "proAnaOrdNode_divGestione";
    public $divRis = "proAnaOrdNode_divRisultato";
    public $divRic = "proAnaOrdNode_divRicerca";
    public $gridOrdNode = "proAnaOrdNode_gridOrdNode";

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
                if (!proTipoNode::initSistemSubjectNode($this->proLib)) {
                    Out::msgStop("Attenzione!!!", "Errore inizializzazione Organi");
                    break;
                }
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridOrdNode:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridOrdNode:
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
                $ita_grid01 = new TableView($this->gridOrdNode, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DESDES');
                $ita_grid01->exportXLS('', 'TipoNodi.xls');
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
                        $AnaOrdNode_rec = $_POST[$this->nameForm . '_ANAORDNODE'];
                        $delete_Info = 'Oggetto: ' . $AnaOrdNode_rec['TIPONODE'] . $AnaOrdNode_rec['SEQORD'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANAORDNODE', $AnaOrdNode_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onChange':
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
        $sql = "SELECT * FROM ANAORDNODE WHERE 1 = 1";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND TIPONODE = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }
        if ($_POST[$this->nameForm . '_Ordine'] != "") {
            $sql .= " AND SEQORD = '" . $_POST[$this->nameForm . '_Ordine'] . "'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridOrdNode);
        TableView::clearGrid($this->gridOrdNode);
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
        $AnaOrdNode_rec = $this->proLib->GetAnaTipoNode($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $AnaOrdNode_rec['TIPONODE'] . " " . $AnaOrdNode_rec['SEQORD'];
        $this->openRecord($this->PROT_DB, 'ANAORDNODE', $open_Info);
        $this->Nascondi();
        Out::valori($AnaOrdNode_rec, $this->nameForm . '_ANAORDNODE');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANAORDNODE[SEQORD]');
        Out::attributo($this->nameForm . '_ANAORDNODE[TIPONODE]', 'readonly', '0');
        TableView::disableEvents($this->gridOrdNode);
    }

    private function Elenca() {
        $sql = $this->CreaSql();
        try {
            $ita_grid01 = new TableView($this->gridOrdNode, array(
                'sqlDB' => $this->PROT_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(10000);
            $ita_grid01->setSortIndex('SEQORD');
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
                TableView::enableEvents($this->gridOrdNode);
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
        Out::attributo($this->nameForm . '_ANAORDNODE[TIPONODE]', 'readonly', '1');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_ANAORDNODE[TIPONODE]');
    }

    private function Aggiungi() {
        $codice = $_POST[$this->nameForm . '_ANAORDNODE']['TIPONODE'];
        $AnaTipoDoc_ric = $this->proLib->GetAnaTipoNode($codice, 'codice');
        if (!$AnaTipoDoc_ric) {
            $AnaTipoDoc_ric = $_POST[$this->nameForm . '_ANAORDNODE'];
            $insert_Info = 'Oggetto: ' . $AnaTipoDoc_ric['TIPONODE'] . $AnaTipoDoc_ric['SEQORD'];
            if ($this->insertRecord($this->PROT_DB, 'ANAORDNODE', $AnaTipoDoc_ric, $insert_Info)) {
                $this->Elenca();
            }
        } else {
            Out::msgInfo("Codice giра presente", "Inserire un nuovo codice.");
            Out::setFocus('', $this->nameForm . '_ANAORDNODE[TIPONODE]');
        }
    }

    private function Aggiorna() {
        $AnaOrdNode_rec = $_POST[$this->nameForm . '_ANAORDNODE'];
        $update_Info = 'Oggetto: ' . $AnaOrdNode_rec['TIPONODE'] . $AnaOrdNode_rec['SEQORD'];
        if ($this->updateRecord($this->PROT_DB, 'ANAORDNODE', $AnaOrdNode_rec, $update_Info)) {
            $this->Elenca();
        }
    }

}

?>