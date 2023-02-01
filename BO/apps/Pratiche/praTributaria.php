<?php

/**
 *
 * ANAGRAFICA ATTIVITA' COMMERCIALI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praTributaria() {
    $praTributaria = new praTributaria();
    $praTributaria->parseEvent();
    return;
}

class praTributaria extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm = "praTributaria";
    public $divRis = "praTributaria_divRisultato";
    public $divRic = "praTributaria_divRicerca";
    public $gridTributaria = "praTributaria_gridTributaria";
    public $procFissa;
    public $procSab;
    public $procPubb;
    public $procEdilizia;
    public $wcoLib;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->wcoLib = new wcoLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->procFissa = App::$utente->getKey($this->nameForm . '_procFissa');
            $this->procSab = App::$utente->getKey($this->nameForm . '_procSab');
            $this->procPubb = App::$utente->getKey($this->nameForm . '_procPubb');
            $this->procEdilizia = App::$utente->getKey($this->nameForm . '_procEdilizia');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_procFissa', $this->procFissa);
            App::$utente->setKey($this->nameForm . '_procSab', $this->procSab);
            App::$utente->setKey($this->nameForm . '_procPubb', $this->procPubb);
            App::$utente->setKey($this->nameForm . '_procEdilizia', $this->procEdilizia);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridSettori, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('ATTDES');
                $ita_grid01->exportXLS('', 'attivita.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'RICEZ') {
                    $ordinamento = 'GESDRI';
                }
                if ($ordinamento == 'PRADES__1') {
                    $ordinamento = 'GESPRO';
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAttivita', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        if ($_POST[$this->nameForm . "_ProcSedeFissa"] == "" && $_POST[$this->nameForm . "_ProcSab"] == "" && $_POST[$this->nameForm . "_ProcAreePub"] == "" && $_POST[$this->nameForm . "_ProcEdilizia"] == "") {
                            Out::msgInfo("Creazione file anagrafe tributaria", "Selezionare almeno un procedimento in una delle seguenti categorie");
                            break;
                        }


                        $this->procFissa = $_POST[$this->nameForm . "_ProcSedeFissa"];
                        $this->procSab = $_POST[$this->nameForm . "_ProcSab"];
                        $this->procPubb = $_POST[$this->nameForm . "_ProcAreePub"];
                        $this->procEdilizia = $_POST[$this->nameForm . "_ProcEdilizia"];


                        /*
                          if (isset($_POST[$this->nameForm . "_Aggregato"])) {
                          if ($_POST[$this->nameForm . "_Aggregato"] == "") {
                          Out::msgInfo("Creazione file anagrafe tributaria", "Selezionare uno sportello aggregato");
                          break;
                          }
                          }
                         */
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridTributaria, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(30);
                            $ita_grid01->setSortIndex('GESNUM');
                            $ita_grid01->setSortOrder('desc');
                            $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                //$this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_StampaFile');
                                TableView::enableEvents($this->gridTributaria);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_StampaFile':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Creazione file anagrafe tributaria", "Creazione ambiente di lavoro temporaneo fallita.");
                                break;
                            }
                        }
                        //
                        //Assegno il nome del file
                        //
                        $Nome_file = itaLib::getPrivateUploadPath() . "/anagrafeTributaria.txt";
                        $File = fopen($Nome_file, "w+");

                        $paramTrib = $this->GetParamAnagrafeTrib();



                        $progressivo = $this->getProgressivo($paramTrib['Progressivo']);
                        if (!$progressivo) {
                            return false;
                        }
                        $H_012 = date("Y") . str_pad($progressivo, 3, "0", STR_PAD_LEFT);

                        //
                        //Riporto la stringa della riga di testata
                        //
                        $rigaTestata = $this->ScriviRigaInizioFine($H_012, "inizio", "", $paramTrib);

                        //
                        //Riporto l'array con le righe intermedio e l'ultimo n di riga
                        //
                        $arrayIntermedio = $this->ScriviRighe($H_012);
                        $H_013 = str_pad(trim($arrayIntermedio['conta']), 7, "0", STR_PAD_LEFT);

                        //
                        //Riporto la stringa con la riga finale
                        //
                        $rigaFinale = $this->ScriviRigaInizioFine($H_012, "fine", $H_013, $paramTrib);

                        //
                        //Scrvio il file
                        //
                        fwrite($File, $rigaTestata . $arrayIntermedio['righe'] . $rigaFinale);
                        fclose($File);
                        Out::openDocument(utiDownload::getUrl("anagrafeTributaria.txt", $Nome_file, true));

                        break;
                    case $this->nameForm . '_AltraRicerca':
                        //$this->OpenRicerca();
                        Out::hide($this->divRis, '');
                        Out::show($this->divRic, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Elenca');
                        break;
                    case $this->nameForm . '_buttProcSedeFissa':
                        //praRic::praRicAnapraMulti($this->nameForm, "FISSA", " WHERE ANAPRA.PRASTT=1 AND PRAAVA=''", "", $_POST[$this->nameForm . "_Aggregato"]);
                        praRic::praRicAnapraMulti($this->nameForm, "FISSA", " WHERE ITEEVT.IEVSTT=1 AND PRAOFFLINE=0");
                        break;
                    case $this->nameForm . '_buttProcSab':
                        //praRic::praRicAnapraMulti($this->nameForm, "SUB", " WHERE ANAPRA.PRASTT=5 AND PRAAVA=''", "", $_POST[$this->nameForm . "_Aggregato"]);
                        praRic::praRicAnapraMulti($this->nameForm, "SUB", " WHERE ITEEVT.IEVSTT=5 AND PRAOFFLINE=0");
                        break;
                    case $this->nameForm . '_buttAreePubb':
                        //praRic::praRicAnapraMulti($this->nameForm, "PUBB", " WHERE ANAPRA.PRASTT=4 AND PRAAVA=''", "", $_POST[$this->nameForm . "_Aggregato"]);
                        praRic::praRicAnapraMulti($this->nameForm, "PUBB", " WHERE ITEEVT.IEVSTT=4 AND PRAOFFLINE=0");
                        break;
                    case $this->nameForm . '_buttEdilizia':
                        praRic::praRicAnapraMulti($this->nameForm, "EDILIZIA", " WHERE ITEEVT.IEVSTT=10 AND PRAOFFLINE=0");
                        break;
                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa($this->nameForm);
                        break;
                    case $this->nameForm . '_buttDelSedeFissa':
                        $this->procFissa = "";
                        Out::valore($this->nameForm . "_ProcSedeFissa", "");
                        break;
                    case $this->nameForm . '_buttDelSab':
                        $this->procSab = "";
                        Out::valore($this->nameForm . "_ProcSab", "");
                        break;
                    case $this->nameForm . '_buttDelAreePubb':
                        $this->procPubb = "";
                        Out::valore($this->nameForm . "_ProcAreePub", "");
                        break;
                    case $this->nameForm . '_buttDelEdilizia':
                        $this->procEdilizia = "";
                        Out::valore($this->nameForm . "_ProcEdilizia", "");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnAnaspa':
                $anaspa_rec = $this->praLib->GetAnaspa($_POST["retKey"], "rowid");
                Out::valore($this->nameForm . '_Aggregato', $anaspa_rec['SPACOD']);
                Out::valore($this->nameForm . '_Aggregato_desc', $anaspa_rec['SPADES']);
                break;
            case 'returnAnapra':
                $arrayRowid = explode(",", $_POST['retKey']);
                switch ($_POST["retid"]) {
                    case "FISSA":
                        foreach ($arrayRowid as $rowid) {
                            $iteevt_rec = $this->praLib->GetIteevt($rowid, 'rowid');
                            $this->procFissa .= $iteevt_rec['ITEPRA'] . ";";
                        }
                        Out::valore($this->nameForm . "_ProcSedeFissa", $this->procFissa);
                        break;
                    case "SUB":
                        foreach ($arrayRowid as $rowid) {
                            $iteevt_rec = $this->praLib->GetIteevt($rowid, 'rowid');
                            $this->procSab .= $iteevt_rec['ITEPRA'] . ";";
                        }
                        Out::valore($this->nameForm . "_ProcSab", $this->procSab);
                        break;
                    case "PUBB":
                        foreach ($arrayRowid as $rowid) {
                            $iteevt_rec = $this->praLib->GetIteevt($rowid, 'rowid');
                            $this->procPubb .= $iteevt_rec['ITEPRA'] . ";";
                        }
                        Out::valore($this->nameForm . "_ProcAreePub", $this->procPubb);
                        break;
                    case "EDILIZIA":
                        foreach ($arrayRowid as $rowid) {
                            $iteevt_rec = $this->praLib->GetIteevt($rowid, 'rowid');
                            $this->procEdilizia .= $iteevt_rec['ITEPRA'] . ";";
                        }
                        Out::valore($this->nameForm . "_ProcEdilizia", $this->procEdilizia);
                        break;
                    default:
                        break;
                }

                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_procFissa');
        App::$utente->removeKey($this->nameForm . '_procSab');
        App::$utente->removeKey($this->nameForm . '_procPubb');
        App::$utente->removeKey($this->nameForm . '_procEdilizia');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca

        $Procedimenti = $this->procFissa . $this->procSab . $this->procPubb . $this->procEdilizia;
        if ($Procedimenti) {
            $arrayFissa = explode(";", $Procedimenti);
            $query = "";
            foreach ($arrayFissa as $proc) {
                if ($proc) {
                    if ($query)
                        $query .= " OR ";
                    $query .= " GESPRO = '$proc' ";
                }
            }
            $queryProc = " AND ($query)";
        }

        if ($_POST[$this->nameForm . '_TipoInvio'] == "A") {
            $whereEvento = " AND GESEVE = '000001'";
        } elseif ($_POST[$this->nameForm . '_TipoInvio'] == "C") {
            $whereEvento = " AND GESEVE = '000002'";
        } elseif ($_POST[$this->nameForm . '_TipoInvio'] == "E") {
            $whereEvento = " AND GESEVE = '000006'";
        }

        $sql = "SELECT * FROM   PROGES  WHERE  ROWID = ROWID $whereEvento";

        if ($_POST[$this->nameForm . '_Anno'] != "") {
            $sql .= " AND " . $this->PRAM_DB->subString('GESDRI', 1, 4) . " = '" . $_POST[$this->nameForm . '_Anno'] . "'";
        }
        if ($_POST[$this->nameForm . '_Aggregato'] != "") {
            $sql .= " AND GESSPA = '" . $_POST[$this->nameForm . '_Aggregato'] . "'";
        }
        if ($queryProc) {
            $sql .= $queryProc;
        }
        App::log($sql);
        return $sql;
    }

    function GetParamAnagrafeTrib() {
        $filent_rec = $this->praLib->GetFilent(11);
        return array(
            "Progressivo" => $filent_rec['FILDE2'],
            "Natura" => $filent_rec['FILDE3'],
        );
    }

    function ScriviRigaInizioFine($H_012, $tipo, $H_013 = "", $paramTrib = array()) {
        if ($tipo == "inizio") {
            $i = "0";
            $f_fill = str_pad("", 26, " ", STR_PAD_RIGHT);
        } elseif ($tipo == "fine") {
            $f_fill = str_pad("", 19, " ", STR_PAD_RIGHT);
            $i = "9";
        }
        if ($_POST[$this->nameForm . "_Aggregato"]) {
            $Anaspa_rec = $this->praLib->GetAnaspa($_POST[$this->nameForm . "_Aggregato"]);
            $denominazione = $Anaspa_rec['SPADES'];
            $comune = $Anaspa_rec['SPACOM'];
            $provincia = $Anaspa_rec['SPAPRO'];
            $indirizzo = $Anaspa_rec['SPAIND'];
            $cap = $Anaspa_rec['SPACAP'];
        } else {
            $Anatsp_rec = $this->praLib->GetAnatsp("000001");
            $denominazione = $Anatsp_rec['TSPDES'];
            $comune = $Anatsp_rec['TSPCOM'];
            $provincia = $Anatsp_rec['TSPPRO'];
            $indirizzo = $Anatsp_rec['TSPIND'];
            $cap = $Anatsp_rec['TSPCAP'];
        }
        $t1 = str_pad($_POST[$this->nameForm . "_Fiscale"], 11, " ", STR_PAD_RIGHT); //CF
        $t2 = str_pad($denominazione, 60, " ", STR_PAD_RIGHT); //DENOMINAZIONE
        $t3 = str_pad($comune, 35, " ", STR_PAD_RIGHT); //INDIRIZZO
        $t4 = str_pad($provincia, 2, " ", STR_PAD_RIGHT);  //PROVINCIA
        $t5 = str_pad($indirizzo, 35, " ", STR_PAD_RIGHT); //INDIRIZZO
        $t6 = str_pad($cap, 5, " ", STR_PAD_RIGHT);  //CAP
        $t7 = str_pad($paramTrib['Natura'], 2, " ", STR_PAD_RIGHT);  //UFFICIO
        $t8 = str_pad($_POST[$this->nameForm . '_Anno'], 4, " ", STR_PAD_RIGHT);  //ANNO DATI
        return $i . $t1 . $t2 . $t3 . $t4 . $t5 . $t6 . $t7 . $H_013 . $t8 . "CC" . $H_012 . date("dmY") . "O" . $f_fill . "\r\n";
    }

    function ScriviRighe($H_012) {
        $sql = $this->CreaSql();
        $Proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $conta = 0;
        $righe = "";
        foreach ($Proges_tab as $Proges_rec) {
            $impresa = $fiscale = "";

            $impresa = $this->GetImpresa($Proges_rec['GESNUM']);
            $fiscale = $this->GetFiscale($Proges_rec['GESNUM']);

            $Anaspa_rec = $this->praLib->GetAnaspa($Proges_rec['GESSPA']);
            $comune = $Anaspa_rec['SPACOM'];
            $provincia = $Anaspa_rec['SPAPRO'];
            if (!$Anaspa_rec) {
                $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
                $comune = $Anatsp_rec['TSPCOM'];
                $provincia = $Anatsp_rec['TSPPRV'];
            }
            $H_tipo = 1;
            $H_001 = $H_002 = $H_003 = $H_004 = $H_005 = $H_006 = $H_007 = $H_008 = $H_009 = $H_010 = $H_011 = $H_013 = $H_014 = "";
            //$H_001 = str_pad($fiscale, 16, " ", STR_PAD_RIGHT);
            if (strlen(trim($fiscale)) == 11) {
                $H_001 = str_pad($fiscale, 16, " ", STR_PAD_LEFT);
            } elseif (strlen($fiscale) == 16) {
                $H_001 = $fiscale;
            }
            $impresa_60_char = substr($impresa, 0, 60);
            $H_002 = str_pad($impresa_60_char, 60, " ", STR_PAD_RIGHT);
            $H_006 = str_pad($comune, 35, " ", STR_PAD_RIGHT);
            $H_007 = str_pad($provincia, 2, " ", STR_PAD_RIGHT);

            if ($_POST[$this->nameForm . "_TipoInvio"] == "A") {
                $H_008 = "D1";
            } else {
                $H_008 = "D3";
            }
            if (strpos($this->procFissa, $Proges_rec['GESPRO'] . ";") !== false) {
                if ($_POST[$this->nameForm . "_TipoInvio"] == "A") {
                    $H_008 = "D1";
                } else {
                    $H_008 = "D3";
                }
            }

            if (strpos($this->procSab, $Proges_rec['GESPRO'] . ";") !== false) {
                if ($_POST[$this->nameForm . "_TipoInvio"] == "A") {
                    $H_008 = "F1";
                } else {
                    $H_008 = "F3";
                }
            }

            if (strpos($this->procPubb, $Proges_rec['GESPRO'] . ";") !== false) {
                if ($_POST[$this->nameForm . "_TipoInvio"] == "A") {
                    $H_008 = "M1";
                } else {
                    $H_008 = "M2";
                }
            }

            if (strpos($this->procEdilizia, $Proges_rec['GESPRO'] . ";") !== false) {
                if ($_POST[$this->nameForm . "_TipoInvio"] == "A") {
                    $H_008 = "M1";
                } else {
                    $H_008 = "M2";
                }
            }

            $H_010 = $H_011 = substr($Proges_rec['GESDRI'], 6, 2) . substr($Proges_rec['GESDRI'], 4, 2) . substr($Proges_rec['GESDRI'], 0, 4);
            if ($_POST[$this->nameForm . "_TipoInvio"] == "C") {
                $dataCess = "";
                //H_011 è la data fine, quindi in caso di CESSAZIONE cerco il dato aggiuntivo mappato per la DATA CESSAZIONE
                $prodag_recDataCess = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $Proges_rec['GESNUM'] . "' AND DAGKEY = 'MODELLO_ESERCIZIO_CESSAZIONEDATA' AND DAGVAL<>''", false);
                $H_011 = substr($Proges_rec['GESDRI'], 6, 2) . substr($Proges_rec['GESDRI'], 4, 2) . substr($Proges_rec['GESDRI'], 0, 4);
                if ($prodag_recDataCess && $prodag_recDataCess['DAGVAL']) {
                    $dataCess = date('Ymd', strtotime($prodag_recDataCess['DAGVAL']));
                    if ($dataCess >= $Proges_rec['GESDRI']) {
                        $H_011 = substr($prodag_recDataCess['DAGVAL'], 0, 2) . substr($prodag_recDataCess['DAGVAL'], 3, 2) . substr($prodag_recDataCess['DAGVAL'], 6); // è nel formato dd/mm/YYY
                    }
                }
            }

            //Se la pratica SUAP è collegata al Commercio, prendo come N. PROVVEDIMENTO il num. Autorizzazione
            $H_009 = str_pad(intval($Proges_rec['GESNUM']), 16, " ", STR_PAD_RIGHT);
            if ($this->checkExistDB("COMM")) {
                $comsua_rec = $this->wcoLib->getComsua($Proges_rec['GESNUM'], "pratica");
                if ($comsua_rec) {
                    $comlic_rec = $this->wcoLib->GetComlic($comsua_rec['SUAPRO']);
                    if ($comlic_rec) {
                        $H_009 = str_pad(intval($comlic_rec['LICAUT']), 16, " ", STR_PAD_RIGHT);
                    } else {
                        $H_009 = str_pad(intval($Proges_rec['GESNUM']), 16, " ", STR_PAD_RIGHT);
                    }
                } else {
                    $H_009 = str_pad(intval($Proges_rec['GESNUM']), 16, " ", STR_PAD_RIGHT);
                }
            }

            if ($H_011 == "")
                $H_011 = str_pad($H_011, 8, "0", STR_PAD_RIGHT);

            $H_014 = str_pad(" ", 38, " ", STR_PAD_RIGHT);
            $conta += 1;
            $H_013 = str_pad($conta, 6, "0", STR_PAD_LEFT);

            $righe .= $H_tipo . $H_001 . $H_002 . $H_006 . $H_007 . $H_008 . $H_009 . $H_010 . $H_011 . $H_012 . $H_013 . $H_014 . "\r\n";
            if ($conta == count($Proges_tab))
                $inc = $conta;
        }
        return array("righe" => $righe, "conta" => $inc);
    }

    function getProgressivo($progressivo) {
        $filent_rec = $this->praLib->GetFilent(11);
        if (!$progressivo) {
            Out::msgStop("Attenzione!!!", "Progressivo non configurato correttamente");
            return false;
        }
        $progressivo = $progressivo + 1;
        $filent_rec['FILDE2'] = $progressivo;
        $update_Info = "Oggetto: Aggiorno progressivo FILENT al valore $progressivo";
        if (!$this->updateRecord($this->PRAM_DB, 'FILENT', $filent_rec, $update_Info)) {
            Out::msgStop("Errore in Aggionamento", "Aggiornamento progressivo fallito.");
            return false;
        }
        return $progressivo;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridTributaria);
        TableView::clearGrid($this->gridTributaria);
        $this->Nascondi();
        $this->procFissa = $this->procPubb = $this->procSab = $this->procEdilizia = "";
//        Out::attributo($this->nameForm . '_ProcSedeFissa', "readonly", '0');
//        Out::attributo($this->nameForm . '_ProcSab', "readonly", '0');
//        Out::attributo($this->nameForm . '_ProcAreePub', "readonly", '0');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        $Anaspa_tab = $this->praLib->getGenericTab("SELECT * FROM ANASPA");
        if (!$Anaspa_tab) {
            Out::removeElement($this->nameForm . "_Aggregato_field");
            Out::removeElement($this->nameForm . "_Aggregato_desc");
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaFile');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function Dettaglio($Indice) {
        $Anaatt_rec = $this->praLib->GetAnaatt($Indice, 'rowid');
        $Anaset_rec = $this->praLib->GetAnaset($Anaatt_rec['ATTSET']);
        $open_Info = 'Oggetto: ' . $Anaatt_rec['ATTCOD'] . " " . $Anaatt_rec['ATTDES'];
        $this->openRecord($this->PRAM_DB, 'ANAATT', $open_Info);
        $this->visualizzaMarcatura($Anaatt_rec);
        $this->Nascondi();
        Out::valori($Anaatt_rec, $this->nameForm . '_ANAATT');
        Out::valore($this->nameForm . '_SETTORE', $Anaset_rec["SETDES"]);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANAATT[ATTDES]');
        Out::attributo($this->nameForm . '_ANAATT[ATTCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridAttivita);
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_TipoInvio', 1, "A", "1", "Apertura");
        Out::select($this->nameForm . '_TipoInvio', 1, "C", "0", "Chiusura");
        Out::select($this->nameForm . '_TipoInvio', 1, "E", "0", "Altro (per edilizia)");
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]["GESNUM"] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO='0001'", false);
            if ($Anades_rec) {
                $Result_tab[$key]["DESNOM"] = $Anades_rec['DESNOM'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $Result_tab[$key]["DESNOM"] = $Anades_rec['DESNOM'];
            }
            if ($Result_rec['GESPRA'] != 0) {
                $Result_tab[$key]["GESPRA"] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            } else {
                $Result_tab[$key]["GESPRA"] = "";
            }
            if ($Result_rec['GESDRI'] != "" && $Result_rec['GESORA'] != "") {
                $Result_tab[$key]["RICEZ"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4) . " (" . $Result_rec['GESORA'] . ")";
            } else {
                $Result_tab[$key]["RICEZ"] = "";
            }
            $Anapra_rec = $this->praLib->GetAnapra($Result_rec['GESPRO']);
            $Result_tab[$key]["PRADES__1"] = $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'];

            $Anades_rec_impresa = $this->praLib->GetAnades($Result_rec['GESNUM'], "ruolo", false, "0004");
            if ($Anades_rec_impresa) {
                $Result_tab[$key]["IMPRESA"] = $Anades_rec_impresa['DESNOM'];
                $Result_tab[$key]["FISCALE"] = $Anades_rec_impresa['DESFIS'];
            } else {
                $Anades_rec_impresa_ind = $this->praLib->GetAnades($Result_rec['GESNUM'], "ruolo", false, "0005");
                if ($Anades_rec_impresa_ind) {
                    $Result_tab[$key]["IMPRESA"] = $Anades_rec_impresa_ind['DESNOM'];
                    $Result_tab[$key]["FISCALE"] = $Anades_rec_impresa_ind['DESPIVA'];
                    if (!$Result_tab[$key]["FISCALE"]) {
                        $Result_tab[$key]["FISCALE"] = $Anades_rec_impresa_ind['DESFIS'];
                    }
                } else {
                    $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $Result_rec['GESNUM'] . "'
                                  AND (DAGTIP = 'DenominazioneImpresa' OR DAGTIP = 'Codfis_InsProduttivo' OR DAGKEY = 'DENOMINAZIONE_IMPRESA' OR DAGKEY = 'CF_IMPRESA')", true);
                    if ($Prodag_tab) {
                        foreach ($Prodag_tab as $Prodag_rec) {
                            if ($Prodag_rec['DAGKEY'] == "DENOMINAZIONE_IMPRESA" || $Prodag_rec['DAGTIP'] == "DenominazioneImpresa")
                                $Result_tab[$key]["IMPRESA"] = $Prodag_rec['DAGVAL'];
                            if ($Prodag_rec['DAGKEY'] == "CF_IMPRESA" || $Prodag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                                $Result_tab[$key]["FISCALE"] = $Prodag_rec['DAGVAL'];
                        }
                    }
                }
            }
        }
        return $Result_tab;
    }

    public function checkExistDB($db, $dittaCOMM = "") {
        try {
            if ($dittaCOMM) {
                $DB = ItaDB::DBOpen($db, $dittaCOMM);
            } else {
                $DB = ItaDB::DBOpen($db);
            }
            $arrayTables = $DB->listTables();
        } catch (Exception $exc) {
            return false;
        }
        if ($DB == "") {
            return false;
        } else {
            if (!$arrayTables) {
                return false;
            }
        }
        return true;
    }

    function GetFiscale($gesnum) {
        $Anades_rec_impresa = $this->praLib->GetAnades($gesnum, "ruolo", false, "0004");
        if ($Anades_rec_impresa) {
            if ($Anades_rec_impresa['DESPIVA'] && is_numeric($Anades_rec_impresa['DESPIVA'])) {
                $fiscale = $Anades_rec_impresa['DESPIVA'];
            } else {
                //$fiscale = $Anades_rec_impresa['DESFIS'];
                $Anades_rec_dichiarante = $this->praLib->GetAnades($gesnum, "ruolo", false, "0002");
                if ($Anades_rec_dichiarante) {
                    $fiscale = $Anades_rec_dichiarante['DESFIS'];
                }
            }
        }

//        if ($fiscale == "") {
//            $Anades_rec_impresa_ind = $this->praLib->GetAnades($gesnum, "ruolo", false, "0005");
//            if ($Anades_rec_impresa_ind) {
//                $fiscale = $Anades_rec_impresa_ind['DESPIVA'];
//                if (!$fiscale) {
//                    $fiscale = $Anades_rec_impresa_ind['DESFIS'];
//                }
//            }
//        }

        if ($fiscale == "") {
            $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "'
                                  AND (DAGTIP = 'Codfis_InsProduttivo' OR DAGKEY = 'CF_IMPRESA')", true);
            if ($Prodag_tab) {
                foreach ($Prodag_tab as $Prodag_rec) {
                    if ($Prodag_rec['DAGKEY'] == "CF_IMPRESA" || $Prodag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                        $fiscale = $Prodag_rec['DAGVAL'];
                }
            }
        }
        return $fiscale;
    }

    function GetImpresa($gesnum) {
        $Anades_rec_impresa = $this->praLib->GetAnades($gesnum, "ruolo", false, "0004");
        if ($Anades_rec_impresa) {
//            $impresa = $Anades_rec_impresa['DESNOM'];
//            if ($impresa == "") {
//                $impresa = $Anades_rec_impresa['DESCOGNOME'] . " " . $Anades_rec_impresa['DESCOGNOME'];
//            }
            if ($Anades_rec_impresa['DESPIVA'] && is_numeric($Anades_rec_impresa['DESPIVA'])) {
                $impresa = $Anades_rec_impresa['DESNOM'];
            } else {
                $Anades_rec_dichiarante = $this->praLib->GetAnades($gesnum, "ruolo", false, "0002");
                if ($Anades_rec_dichiarante) {
                    $impresa = $Anades_rec_dichiarante['DESNOM'];
                }
            }
        }

//        if ($impresa == "") {
//            $Anades_rec_impresa_ind = $this->praLib->GetAnades($gesnum, "ruolo", false, "0005");
//            if ($Anades_rec_impresa_ind) {
//                $impresa = trim($Anades_rec_impresa_ind['DESNOM']);
//                if ($impresa == "") {
//                    $impresa = $Anades_rec_impresa_ind['DESCOGNOME'] . " " . $Anades_rec_impresa_ind['DESNOME'];
//                }
//            }
//        }

        if ($impresa == "") {
            $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "'
                                  AND (DAGTIP = 'DenominazioneImpresa' OR DAGKEY = 'DENOMINAZIONE_IMPRESA')", true);
            if ($Prodag_tab) {
                foreach ($Prodag_tab as $Prodag_rec) {
                    if ($Prodag_rec['DAGKEY'] == "DENOMINAZIONE_IMPRESA" || $Prodag_rec['DAGTIP'] == "DenominazioneImpresa")
                        $impresa = $Prodag_rec['DAGVAL'];
                }
            }
        }
        return $impresa;
    }

}

?>