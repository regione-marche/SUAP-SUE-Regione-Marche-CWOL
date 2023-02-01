<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function basTabAutomezzi() {
    $basTabAutomezzi = new basTabAutomezzi();
    $basTabAutomezzi->parseEvent();
    return;
}

class basTabAutomezzi extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = "basTabAutomezzi";
    public $divGes = "basTabAutomezzi_divGestione";
    public $divRis = "basTabAutomezzi_divRisultato";
    public $divRic = "basTabAutomezzi_divRicerca";
    public $gridAutomezzi = "basTabAutomezzi_gridAutomezzi";
    public $Codice;
    public $Targa;
    public $Descrizione;
    public $chiamata;
    public $returnMethod;
    public $returnModel;

    function __construct() {
        parent::__construct();
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->Codice = App::$utente->getKey($this->nameForm . '_Codice');
            $this->Targa = App::$utente->getKey($this->nameForm . '_Targa');
            $this->Descrizione = App::$utente->getKey($this->nameForm . '_Descrizione');
            $this->chiamata = App::$utente->getKey($this->nameForm . '_chiamata');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_Codice', $this->Codice);
            App::$utente->setKey($this->nameForm . '_Targa', $this->Targa);
            App::$utente->setKey($this->nameForm . '_Descrizione', $this->Descrizione);
            App::$utente->setKey($this->nameForm . '_chiamata', $this->chiamata);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->chiamata = '';
                $this->OpenRicerca();
                break;
            case 'InserisciMezzo':
                $this->Nascondi();
                Out::show($this->divGes);
                Out::show($this->nameForm . '_Aggiungi');
                Out::valore($this->nameForm . '_ANA_AUTOMEZZI[TARGA]', $_POST['TARGA']);
                Out::valore($this->nameForm . '_ANA_AUTOMEZZI[DESCAUTO]', $_POST['TIPO']);
                $this->chiamata = 'InserisciMezzo';
                $this->returnMethod = $_POST['returnEvent'];
                $this->returnModel = $_POST['returnModel'];
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAutomezzi:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAutomezzi:
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Cancellazione Non Abilitata.");
                            break;
                        }
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
                    case $this->gridAutomezzi:
                        $this->Nascondi();
                        Out::show($this->divGes);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridAutomezzi:
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        $parameters = array("Sql" => $this->CreaSql($this->Codice, $this->Descrizione, $this->Targa),
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'basTabAutomezzi', $parameters);
                        break;
                }
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql($this->Codice, $this->Descrizione, $this->Targa);
                $ita_grid01 = new TableView($this->gridAutomezzi, array(
                    'sqlDB' => $this->ITALWEB_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->exportXLS('', 'Automezzi.xls');
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAutomezzi:
                        $this->CaricaTabella();
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->Nascondi();
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_Nuovo');
                        Out::show($this->nameForm . '_AltraRicerca');
                        TableView::enableEvents($this->gridAutomezzi);
                        TableView::clearGrid($this->gridAutomezzi);
                        $this->Codice = $_POST[$this->nameForm . '_Codice'];
                        $this->Descrizione = $_POST[$this->nameForm . '_Descrizione'];
                        $this->Targa = $_POST[$this->nameForm . '_Targa'];
                        $this->CaricaTabella($this->Codice, $this->Descrizione, $this->Targa);
                        //TableView::reload($this->gridAutomezzi);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->Nascondi();
                        Out::show($this->divGes);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $Automezzi_rec = $_POST[$this->nameForm . '_ANA_AUTOMEZZI'];
                        $sql_controllo = "SELECT * FROM ANA_AUTOMEZZI WHERE AUTO=" . $Automezzi_rec['AUTO'];
                        $Ana_automezzi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql_controllo, false);
                        if ($Ana_automezzi_rec) {
                            Out::msgInfo("Attenzione", "Codice già presente in anagrafica, selezionare un altro codice");
                            break;
                        } else {
                            $Automezzi_rec['CODUTE'] = App::$utente->getKey('nomeUtente');
                            $Automezzi_rec['DATAOPER'] = date('Ymd');
                            $Automezzi_rec['TIMOPER'] = date('H:i:s');
                            $insert_Info = 'Inserimento Codice ' . $Automezzi_rec['AUTO'] . ' in anagrafica automezzi';
                            if ($this->insertRecord($this->ITALWEB_DB, 'ANA_AUTOMEZZI', $Automezzi_rec, $insert_Info, 'ID')) {
                                if ($this->chiamata == 'InserisciMezzo') {
                                    $this->returnToParent();
                                } else {
                                    Out::msgBlock($this->nameForm, 1000, true, "Inserito Correttamente");
                                    $this->Dettaglio($this->lastInsertId);
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Automezzi_rec = $_POST[$this->nameForm . '_ANA_AUTOMEZZI'];
                        $sql_controllo = "SELECT * FROM ANA_AUTOMEZZI WHERE AUTO=" . $Automezzi_rec['AUTO'] . " AND ID<>" . $Automezzi_rec['ID'];
                        $Ana_automezzi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql_controllo, false);
                        if ($Ana_automezzi_rec) {
                            Out::msgInfo("Attenzione", "Codice già presente in anagrafica, selezionare un altro codice");
                            break;
                        } else {
                            $Automezzi_rec['CODUTE'] = App::$utente->getKey('nomeUtente');
                            $Automezzi_rec['DATAOPER'] = date('Ymd');
                            $Automezzi_rec['TIMOPER'] = date('H:i:s');
                            $insert_Info = 'Aggiornamento Codice ' . $Automezzi_rec['AUTO'] . ' in anagrafica automezzi';
                            if ($this->updateRecord($this->ITALWEB_DB, 'ANA_AUTOMEZZI', $Automezzi_rec, $insert_Info, 'ID')) {
                                Out::msgBlock($this->nameForm, 1000, true, "Aggiornato Correttamente");
                                // $this->OpDenRicerca();
                            }
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
                        $Automezzi_rec = $_POST[$this->nameForm . '_ANA_AUTOMEZZI'];
                        try {
                            $delete_Info = 'Cancellato codice ' . $Automezzi_rec['AUTO'];
                            if ($this->deleteRecord($this->ITALWEB_DB, 'ANA_AUTOMEZZI', $Automezzi_rec['ID'], $delete_Info, 'ID')) {
                                Out::msgBlock($this->nameForm, 1000, true, "Cancellato Correttamente");
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su TABELLA AUTOMEZZI'", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_Codice');
        App::$utente->removeKey($this->nameForm . '_Descrizione');
        App::$utente->removeKey($this->nameForm . '_Targa');
        App::$utente->removeKey($this->nameForm . '_chiamata');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_returnModel');
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnMethod;
        $_POST['model'] = $this->returnModel;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::hide($this->divGes);
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_ANA_AUTOMEZZI[CODICEPRESTAZIONE]_lbl');
        Out::hide($this->nameForm . '_ANA_AUTOMEZZI[TIPMEZZO]_lbl');
    }

    public function OpenRicerca() {
        $this->Nascondi();
        Out::show($this->divRic);
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::clearFields($this->nameForm);
    }

    public function CreaSql($Codice, $Descrizione, $Targa) {
        $sql = "SELECT * FROM ANA_AUTOMEZZI WHERE 1";
        if ($Codice) {
            $sql.=" AND AUTO=$Codice";
        }
        if ($Descrizione) {
            $Descrizione = strtoupper($Descrizione);
            $sql.=" AND ".$this->ITALWEB_DB->strUpper('DESCAUTO')." LIKE '%".addslashes($Descrizione)."%'";
        }
        if ($Targa) {
            $Targa = strtoupper($Targa);
            $sql.=" AND ".$this->ITALWEB_DB->strUpper('TARGA')." LIKE '%$Targa%'";
        }
        return $sql;
    }

    public function Dettaglio($Rowid) {
        Out::clearFields($this->nameForm);
        $sql = "SELECT * FROM ANA_AUTOMEZZI WHERE ID=$Rowid";
        $Automezzi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $this->Nascondi();
        Out::show($this->divGes);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::valori($Automezzi_rec, $this->nameForm . '_ANA_AUTOMEZZI');
    }

    public function CaricaTabella($Codice, $Descrizione, $Targa) {
        TableView::clearGrid($this->gridAutomezzi);
        $sql = $this->CreaSql($Codice, $Descrizione, $Targa);
        $ita_grid01 = new TableView($this->gridAutomezzi, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(100000);
        //$ita_grid01->setSortIndex($_POST['sidx']);
        //$ita_grid01->setSortOrder($_POST['sord']);
        $ita_grid01->getDataPage('json');
    }

}

?>
