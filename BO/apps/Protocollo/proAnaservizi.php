<?php

/**
 *
 * ANAGRAFICA SETTORI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    07.05.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proAnaservizi() {
    $proAnaservizi = new proAnaservizi();
    $proAnaservizi->parseEvent();
    return;
}

class proAnaservizi extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proAnaservizi";
    public $divGes = "proAnaservizi_divGestione";
    public $divRis = "proAnaservizi_divRisultato";
    public $divRic = "proAnaservizi_divRicerca";
    public $gridAnaservizi = "proAnaservizi_gridAnaservizi";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
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
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaservizi:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaservizi:
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
                    case $this->gridAnaservizi:
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANASERVIZI[SERCOD]');
                        break;
                }
                break;

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(true), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaservizi', $parameters);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql(true);
                $ita_grid01 = new TableView($this->gridAnaservizi, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('SERDES');
                $ita_grid01->exportXLS('', 'Anaservizi.xls');
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql(true);
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANASERVIZI[SERRES]_butt':
                        proRic::proRicAnamed($this->nameForm, " WHERE MEDUFF<>''");
                        break;

                    case $this->nameForm . '_TornaElenco':
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql(true);
                        $ita_grid01 = new TableView($this->gridAnaservizi, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnaservizi]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('SERDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
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
                            TableView::enableEvents($this->gridAnaservizi);
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
                        Out::setFocus('', $this->nameForm . '_ANASERVIZI[SERCOD]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $anaservizi_rec = $this->proLib->getAnaservizi($_POST[$this->nameForm . '_ANASERVIZI']['SERCOD']);
                        if (!$anaservizi_rec) {
                            $anaservizi_rec = $_POST[$this->nameForm . '_ANASERVIZI'];
                            $insert_Info = 'Oggetto: ' . $anaservizi_rec['SERCOD'] . " " . $anaservizi_rec['SERDES'];
                            if ($this->insertRecord($this->PROT_DB, 'ANASERVIZI', $anaservizi_rec, $insert_Info)) {
                                Out::msgInfo("Registrazione Settore.", "Settore registrato correttamente.");
                                $anaservizi_rec = $this->proLib->getAnaservizi($anaservizi_rec['SERCOD']);
                                $this->Dettaglio($anaservizi_rec['ROWID']);
                            }
                        } else {
                            Out::msgInfo("Attenzione!", "Codice già  presente. Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANASERVIZI[SERCOD]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anaservizi_rec = $_POST[$this->nameForm . '_ANASERVIZI'];
                        $update_Info = 'Oggetto: ' . $anaservizi_rec['SERCOD'] . " " . $anaservizi_rec['SERDES'];
                        if ($this->updateRecord($this->PROT_DB, 'ANASERVIZI', $anaservizi_rec, $update_Info)) {
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
                        $delete_Info = 'Oggetto: ' . $anaservizi_rec['SERCOD'] . " " . $anaservizi_rec['SERDES'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANASERVIZI', $_POST[$this->nameForm . '_ANASERVIZI']['ROWID'], $delete_Info)) {
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
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        $anaservizi_rec = $this->proLib->getAnaservizi($codice);
                        if ($anaservizi_rec) {
                            $this->Dettaglio($anaservizi_rec['ROWID']);
                        }
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANASERVIZI[SERRES]':
                        if ($_POST[$this->nameForm . '_ANASERVIZI']['SERRES']) {
                            $Codice = str_pad($_POST[$this->nameForm . '_ANASERVIZI']['SERRES'], 6, '0', STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANASERVIZI[SERRES]', $Codice);
                            if ($this->proLib->GetAnamed($Codice, 'codice', 'no')) {
                                $this->decodAnamed($Codice, 'codice');
                            } else {
                                Out::msgInfo('Attenzione', 'Codice responsabile inserito inesistente.');
                                Out::valore($this->nameForm . '_ANASERVIZI[SERRES]', '');
                                Out::valore($this->nameForm . '_Responsabile', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_ANASERVIZI[SERRES]', '');
                            Out::valore($this->nameForm . '_Responsabile', '');
                        }
                        break;
                }
                break;
            case 'returnanamed':
                $this->decodAnamed($_POST['retKey']);
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

    function decodAnamed($codice, $tipo = 'rowid') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no');
        Out::valore($this->nameForm . '_ANASERVIZI[SERRES]', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_Responsabile', $anamed_rec['MEDNOM']);
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
        TableView::disableEvents($this->gridAnaservizi);
        TableView::clearGrid($this->gridAnaservizi);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANASERVIZI[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
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

    public function CreaSql($fl_stampa = false) {
        // Importo l'ordinamento del filtro
        $sql = "SELECT * FROM ANASERVIZI WHERE ROWID=ROWID";
        if ($fl_stampa) {
            $sql = "SELECT ANASERVIZI.*, MEDNOM FROM ANASERVIZI LEFT OUTER JOIN ANAMED ON ANASERVIZI.SERRES = ANAMED.MEDCOD WHERE 1=1 ";
        }
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND " . $this->PROT_DB->strUpper('SERCOD') . " LIKE '%" . strtoupper($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND " . $this->PROT_DB->strUpper('SERDES') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Descrizione'])) . "%'";
        }
        return $sql;
    }

    public function Dettaglio($rowid) {
        $anaservizi_rec = $this->proLib->getAnaservizi($rowid, 'rowid');
        $open_Info = 'Oggetto: ' . $anaservizi_rec['SERCOD'] . " " . $anaservizi_rec['SERDES'];
        $this->openRecord($this->PROT_DB, 'ANASERVIZI', $open_Info);
        $this->Nascondi();
        Out::valori($anaservizi_rec, $this->nameForm . '_ANASERVIZI');
        $this->decodAnamed($anaservizi_rec['SERRES'], 'codice');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_ANASERVIZI[SERDES]');
        TableView::disableEvents($this->gridAnaservizi);
    }

}

?>
