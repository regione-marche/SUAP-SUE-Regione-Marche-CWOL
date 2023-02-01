<?php

include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';

function basFestivita() {
    $basFestivita = new basFestivita();
    $basFestivita->parseEvent();
    return;
}

class basFestivita extends itaModel {

    public $ITALWEB_DB;
    public $basLib;
    public $nameForm = "basFestivita";
    public $divRis = "basFestivita_divRisultato";
    public $gridGiorniFestivi = "basFestivita_gridGiorniFestivi";

    function __construct() {
        parent::__construct();
        try {
            $this->basLib = new basLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
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

                $this->openRisultato();
                break;

            case 'addGridRow':
                $this->Nuovo();
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridGiorniFestivi:
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Cancellazione Non Abilitata");
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array
                            ('F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")));

                        Out::setFocus('', $this->nameForm . 'F5-Conferma');
                        break;
                }
                break;

            case 'afterSaveCell':

                switch ($_POST['id']) {
                    case $this->gridGiorniFestivi:
                        $Valore = $_POST['value'];
                        if ($_POST['cellname'] == 'NOMEFESTA') {
                            $sql = "SELECT * FROM CAL_TABFESTA WHERE NOMEFESTA= ' " . $_POST['value'] . " ' ";
                            $GiorniFestivi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
                            if ($GiorniFestivi_rec && $GiorniFestivi_rec['rowid'] != $_POST['rowid']) {
                                Out::msgStop("Attenzione", "Documento già presente");
                                $this->OpenRisultato();
                                break;
                            }
                        }
                        if ($_POST['cellname'] == 'DATAFESTA') {
                            if ($_POST['value']) {
                                //Controllo FormatoData
                                $Data = explode("/", $_POST['value']);
                                $Valore = $Data[2] . $Data[1] . $Data[0];
                                if (count($Data) < 3 || strlen($Valore) != 8) {
                                    Out::msgInfo('Attenzione', "La data deve essere formato: GG/MM/AAAA");
                                    $this->OpenRisultato();
                                    break;
                                }
                            }
                        }
                        if ($_POST['cellname'] == 'TIPOFESTA') {
                            if ($_POST['value'] != "F" && $_POST['value'] != "P") {
                                Out::msginfo("", "Inserire il tipo Festività F=Festivo P=Prefestivo");
                                $this->OpenRisultato();
                                break;
                            }
                        }

                        $GiorniFestivi_rec[$_POST['cellname']] = $Valore;
                        $GiorniFestivi_rec['ROWID'] = $_POST['rowid'];
                        $this->updateRecord($this->ITALWEB_DB, 'CAL_TABFESTA', $GiorniFestivi_rec, '', 'ROWID');
                        $this->OpenRisultato();
                        break;
                }
                break;

            case 'printTableToHTML':
                $Parametr_rec = $this->praLib->getParametr();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Parametr_rec['ENTE']);
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridGiorniFestivi, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DATAFESTA');
                $ita_grid01->exportXLS('', 'GiorniFestivi.xls');
                break;

            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum(1);
                $ita_grid01->setPageRows(1000000);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        break;

                    case $this->nameForm . '_Cancella':
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Cancellazione Non Abilitata");
                            break;
                        }
                        if ($_POST[$this->gridGiorniFestivi]['gridParam']['selarrrow'] == 'null' || $_POST[$this->gridGiorniFestivi]['gridParam']['selarrrow'] == '') {
                            Out::msgInfo("Attenzione", "Selezionare una riga");
                        } else {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array
                                ('F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")));
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $rowid = $_POST[$this->gridGiorniFestivi]['gridParam']['selarrrow'];
                        $GiorniFestivi_rec = $_POST[$this->nameForm . '_CAL_TABFESTA'];
                        $delete_Info = 'Oggetto: ' . $GiorniFestivi_rec['NOMEFESTA'] . " " . $GiorniFestivi_rec['DATAFESTA'] . " ";
                        if ($this->deleteRecord($this->ITALWEB_DB, 'CAL_TABFESTA', $rowid, $delete_Info)) {
                            $this->openRisultato();
                        } else {
                            Out::msgStop("Errore", "Errore in Cancellazione su tabella GIORNI FESTIVI");
                        }
                        break;
                    case $this->nameForm . '_RicalcolaAnno':
                        Out::msgQuestion("Modifica Anno", "Confermi la modifica dell'anno?", array
                            ('F8-Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaRicalcolaAnno', 'model' => $this->nameForm, 'shortCut' => "f5")));
                        break;

                    case $this->nameForm . '_ConfermaRicalcolaAnno':
                        $sql = "SELECT * FROM CAL_TABFESTA ";
                        $GiorniFestivi_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
                        foreach ($GiorniFestivi_tab as $GiorniFestivi_rec) {
                            $Anno = substr($GiorniFestivi_rec['DATAFESTA'], 0, 4);
                            $Anno = $Anno + 1;
                            $GiorniFestivi_rec['DATAFESTA'] = $Anno . substr($GiorniFestivi_rec['DATAFESTA'], 4);
                            if (!$this->updateRecord($this->ITALWEB_DB, 'CAL_TABFESTA', $GiorniFestivi_rec, '')) {
                                Out::msgStop("Errore", "Errore in aggiornamento");
                                break;
                            }
                        }

                        $this->OpenRisultato();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    function openRisultato() {
        TableView::disableEvents($this->gridGiorniFestivi);
        $sql = $this->CreaSql();
        $ita_grid01 = new TableView($this->gridGiorniFestivi, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
        $ita_grid01->setPageRows(1000000);
        $ita_grid01->setSortIndex('DATAFESTA');
        $ita_grid01->setSortOrder('asc');

        if ($ita_grid01->getDataPage('json', true)) {
            Out::show($this->divRis, '', 0);
            $this->Nascondi();
            Out::show($this->nameForm . '_Nuovo');
            Out::show($this->nameForm . '_Cancella');
            Out::show($this->nameForm . '_RicalcolaAnno');
            Out::setFocus('', $this->nameForm . '_Nuovo');
            TableView::enableEvents($this->gridGiorniFestivi);
        } else {
            Out::msgInfo("Attenzione", "Nessun record trovato");
            return false;
        }
    }

    public function CreaSql() {
        $sql = "SELECT * FROM CAL_TABFESTA";
        return $sql;
    }

    function Nuovo() {
        $sql = "SELECT * FROM CAL_TABFESTA WHERE DATAFESTA= '' ";
        $GiorniFestivi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        if ($GiorniFestivi_rec) {
            Out::msgStop("Attenzione", "E' già esistente una nuova riga.");
            return;
        }
        $GiorniFestivi_rec = array();

        $insert_Info = 'Oggetto: ' . $GiorniFestivi_rec['DATAFESTA'] . " " . $GiorniFestivi_rec['NOMEFESTA'];
        if ($this->insertRecord($this->ITALWEB_DB, 'CAL_TABFESTA', $GiorniFestivi_rec, $insert_Info)) {
            $this->OpenRisultato();
        } else {
            Out::msgStop("Errore", "Errore inserimento su tabella GIORNI FESTIVI");
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_RicalcolaAnno');
    }

}

?>
