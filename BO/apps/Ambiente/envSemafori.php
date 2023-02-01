<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Cds/cdsRic.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';

function envSemafori() {
    $envSemafori = new envSemafori();
    $envSemafori->parseEvent();
    return;
}

class envSemafori extends itaModel {

    public $ITALWEB_DB;
    public $cdsLib;
    public $nameForm = "envSemafori";
    public $gridSemafori = "envSemafori_gridSemafori";
    public $rowidDaCancellare;

    function __construct() {
        parent::__construct();
        try {
            $this->cdsLib = new cdsLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->rowidDaCancellare = App::$utente->getKey($this->nameForm . '_rowidDaCancellare');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidDaCancellare', $this->rowidDaCancellare);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRisultato();
                break;
            case 'editGridRow':
            case 'dbClickRow':
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridSemafori:
                        $Semafori_rec = array();
                        $Semafori_rec['ROWID'] = "";
                        //Codice Utente
                        $Semafori_rec['UTENTE'] = "";
                        $this->insertRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec, '', 'ROWID', false);
                        $this->OpenRisultato();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridSemafori:
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
                            break;
                        }
                        $this->rowidDaCancellare = $_POST['rowid'];
                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridSemafori:
                        App::log($_POST);
                        if ($_POST['cellname'] == 'CHIAVE') {
                            if ($_POST['value']) {
                                $Valore = $_POST['value'];
                                //CONTROLLO LA CHIAVE
                                $sql = "SELECT * FROM ENV_SEMAFORI WHERE CHIAVE = '$Valore' ";
                                $SemaforiTest_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                                if ($SemaforiTest_rec && $SemaforiTest_rec['ROWID'] != $_POST['rowid']) {
                                    Out::msgStop("Attenzione", "Chiave $Valore già presente.");
                                    $this->OpenRisultato();
                                    break;
                                }
                            }
                            $Semafori_rec[$_POST['cellname']] = $Valore;
                            $Semafori_rec['ROWID'] = $_POST['rowid'];
                            $this->updateRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec, '', 'ROWID', false);
                            break;
                        }

                        if ($_POST['cellname'] == 'DATA') {
                            if ($_POST['value']) {
                                //Controllo FormatoData
                                $Data = explode("/", $_POST['value']);
                                App::log($Data);
                                $Valore = $Data[2] . $Data[1] . $Data[0];
                                if (count($Data) < 3 || strlen($Valore) != 8) {
                                    Out::msgInfo('Attenzione', "La data deve essere formato: GG/MM/AAAA");
                                    break;
                                }
                                $Semafori_rec[$_POST['cellname']] = $Valore;
                                $Semafori_rec['ROWID'] = $_POST['rowid'];
                                $this->updateRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec, '', 'ROWID', false);
                                break;
                            }
                        }
                        // SE ALTRO CAMPO AGGIORNO
                        $Semafori_rec[$_POST['cellname']] = $_POST['value'];
                        $Semafori_rec['ROWID'] = $_POST['rowid'];
                        $this->updateRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec, '', 'ROWID', false);

                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridSemafori:
                        $Parametr_rec = $this->cdsLib->getParametr();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Parametr_rec['ENTE']);
                        $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envSemafori', $parameters);
                        break;
                }
                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridSemafori:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridSemafori, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('CHIAVE');
                        $ita_grid01->exportXLS('', 'envSemafori.xls');
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {

                    case $this->gridSemafori:
                        $sql = $this->CreaSql();
                        TableView::disableEvents($this->gridSemafori);
                        $ita_grid01 = new TableView($this->gridSemafori, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageRows(1000000);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json', true);
                        TableView::enableEvents($this->gridSemafori);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancella':
                        $Semafori_rec['ROWID'] = $this->rowidDaCancellare;
                        $delete_Info = 'Oggetto: Cancellazione Semafori';
                        if ($this->deleteRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec['ROWID'], $delete_Info)) {
                            Out::msgBlock('', 1000, false, "Cancellazione avvenuta con successo.");
                            $this->OpenRisultato();
                        }
                        break;

                    case $this->nameForm . '_Svuota':
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
                            break;
                        }
                        $this->rowidDaCancellare = $_POST['rowid'];
                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione di tutta la tabella?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaSvuota', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaSvuota', 'model' => $this->nameForm)
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaSvuota':
                        $sql = "SELECT * FROM ENV_SEMAFORI ";
                        $Semafori_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
                        foreach ($Semafori_tab as $Semafori_rec) {
                            $delete_Info = 'Oggetto: Cancellazione Tabella Semafori - Chiave: ' . $Semafori_rec['CHIAVE'];
                            $this->deleteRecord($this->ITALWEB_DB, 'ENV_SEMAFORI', $Semafori_rec['ROWID'], $delete_Info);
                        }
                        Out::msgBlock('', 1000, false, "La tabella è stata cancellata.");
                        $this->OpenRisultato();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_rowidDaCancellare');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function CreaSql() {
        $sql = "SELECT * FROM ENV_SEMAFORI ";
        return $sql;
    }

    function ConvertiData($data) {
        $retData = substr($data, 6, 2) . "/" . substr($data, 4, 2) . "/" . substr($data, 0, 4);
        return $retData;
    }

    function OpenRisultato() {
        $sql = $this->CreaSql();
        TableView::disableEvents($this->gridSemafori);
        TableView::clearGrid($this->gridSemafori);
        $ita_grid01 = new TableView($this->gridSemafori, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageRows(1000000);
        $ita_grid01->setSortIndex('CHIAVE');
        $ita_grid01->setSortOrder('asc');
        $ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridSemafori);
    }

}

?>
