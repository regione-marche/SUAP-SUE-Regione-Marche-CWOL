<?php

include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function envClassEventi() {
    $envClassEventi = new envClassEventi();
    $envClassEventi->parseEvent();
    return;
}

class envClassEventi extends itaModel {

    public $ITALWEB;
    public $devLib;
    public $envLib;
    public $nameForm = "envClassEventi";
    public $divGes = "envClassEventi_divGestione";
    public $divRis = "envClassEventi_divRisultato";
    public $divRic = "envClassEventi_divRicerca";
    public $gridEventi = "envClassEventi_gridEventi";

    function __construct() {
        parent::__construct();
        try {
            $this->devLib = new devLib();
            $this->envLib = new envLib();
            $this->ITALWEB = $this->devLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Elenca();
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        $this->Dettaglio($_POST['rowid'], 'rowid');
                        break;
                }
                break;
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql() . " ORDER BY CODICE ",
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->ITALWEB, $this->nameForm, $parameters);
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        $ita_grid01 = new TableView(
                                        $this->gridEventi, array(
                                    'sqlDB' => $this->ITALWEB,
                                    'sqlQuery' => $this->CreaSql()
                                        )
                        );
                        $ita_grid01->setSortIndex('CODICE');
                        $ita_grid01->exportXLS('', 'AnagraficaEventi.xls');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridEventi:
                        TableView::disableEvents($this->gridEventi);
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridEventi, array(
                                    'sqlDB' => $this->ITALWEB,
                                    'sqlQuery' => $sql));
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        TableView::enableEvents($this->gridEventi);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        if ($this->perms['noEdit']) {
                            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
                            break;
                        }
                        Out::hide($this->nameForm . '_divRisultato', '', 0);
                        Out::hide($this->nameForm . '_divRicerca', '', 0);
                        Out::show($this->nameForm . '_divGestione', '', 0);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::attributo($this->nameForm . '_ENV_TIPI[CODICE]', 'readonly', '1');
                        Out::setFocus('', $this->nameForm . '_ENV_TIPI[CODICE]');
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->Elenca();
                        break;
                    case $this->nameForm . '_TornaElenco':
                        $this->Nascondi();
                        $this->Elenca();
                        Out::show($this->nameForm . '_Nuovo');
                        break;
                    case $this->nameForm . '_Aggiorna':
                        if ($this->perms['noEdit']) {
                            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
                            break;
                        }
                        $env_tipi = $_POST[$this->nameForm . '_ENV_TIPI'];
                        $update_Info = 'Oggetto Aggiornamento Classificazione Evento: ' . $env_tipi['CODICE'];
                        if ($this->updateRecord($this->ITALWEB, 'ENV_TIPI', $env_tipi, $update_Info)) {
                            $this->Elenca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Cancellazione Non Abilitata.");
                            break;
                        }
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . "_ConfermaCancella":
                        $env_tipi = $_POST[$this->nameForm . '_ENV_TIPI'];
                        $result = $this->controllaUsoEvento($env_tipi['CODICE']);
                        if ($result === false) {
                            $delete_Info = 'Oggetto: cancello Classificazione Evento' . $env_tipi['CODICE'];
                            if ($this->deleteRecord($this->ITALWEB, 'ENV_TIPI', $env_tipi['ROWID'], $delete_Info)) {
                                $this->Elenca();
                            }
                        } else {
                            Out::msgStop('ATTENZIONE', 'Codice in uso su Attività/Eventi.<br>Cancellazione non eseguibile.');
                            $this->Elenca();
                        }

                        break;
                    case $this->nameForm . '_Aggiungi':
                        if ($this->perms['noEdit']) {
                            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
                            break;
                        }
                        $risultato = $this->ControllaCodice($_POST[$this->nameForm . '_ENV_TIPI']['CODICE']);
                        if ($risultato == true) {
                            Out::msgStop("Attenzione", "Codice già presente");
                            Out::setFocus('', $this->nameForm . '_ENV_TIPI[CODICE]');
                            break;
                        }
                        $env_tipi = $_POST[$this->nameForm . '_ENV_TIPI'];
                        $insert_Info = 'Oggetto Inserimento Classificazione Evento: ' . $env_tipi['CODICE'];

                        if ($this->insertRecord($this->ITALWEB, 'ENV_TIPI', $env_tipi, $insert_Info)) {
                            $this->Elenca();
                        }
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'onBlur':
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::clearFields($this->nameForm, $this->nameForm . "_divRicerca");
        Out::clearFields($this->nameForm, $this->nameForm . "_divGestione");
        TableView::disableEvents($this->gridEventi);
        TableView::clearGrid($this->gridEventi);
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_divSpiega');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_TornaElenco');
    }

    public function Elenca() {
        $this->Nascondi();
        Out::show($this->nameForm . '_divRisultato', '', 0);
        Out::hide($this->nameForm . '_divRicerca', '', 0);
        Out::hide($this->nameForm . '_divGestione', '', 0);
        Out::hide($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Nuovo');
        $sql = $this->CreaSql($this->gridFilters);
        $ita_grid01 = new TableView($this->gridEventi, array(
                    'sqlDB' => $this->ITALWEB,
                    'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$this->gridEventi]['gridParam']['rowNum']);
        $ita_grid01->setSortIndex('CODICE');
        $ita_grid01->setSortOrder('asc');
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridEventi);
        }
    }

    public function CreaSql() {
        $sql = "SELECT * FROM ENV_TIPI";
        return $sql;
    }

    public function Dettaglio($codice, $tipo) {
        Out::hide($this->nameForm . '_divRisultato', '', 0);
        Out::hide($this->nameForm . '_divRicerca', '', 0);
        Out::show($this->nameForm . '_divGestione', '', 0);
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_TornaElenco');
        Out::show($this->nameForm . '_Aggiorna');
        $env_tipi = $this->envLib->getClassEvento($codice, $tipo);
        Out::attributo($this->nameForm . '_ENV_TIPI[CODICE]', 'readonly', '0');
        Out::valori($env_tipi, $this->nameForm . '_ENV_TIPI');
        Out::setFocus('', $this->nameForm . '_ENV_TIPI[DESCRIZIONE]');
    }

    public function ControllaCodice($codice) {
        $env_tipi = $this->envLib->getClassEvento($codice, 'codice');
        if ($env_tipi) {
            $risultato = true;
        } else {
            $risultato = false;
        }
        return $risultato;
    }

    public function controllaUsoEvento($codice) {
        $risultato = false;
        $cal_eventi = $this->envLib->getEventoCAL_EVENTI($codice, 'codice');
        $cal_attivita = $this->envLib->getEventoCAL_ATTIVITA($codice, 'codice');
        if ($cal_attivita || $cal_eventi){
            $risultato = true;
        }
        return $risultato;
    }

}

?>
