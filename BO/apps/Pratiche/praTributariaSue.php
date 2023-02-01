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
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibTributaria.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDecodCodiceFiscale.class.php';

function praTributariaSue() {
    $praTributariaSue = new praTributariaSue();
    $praTributariaSue->parseEvent();
    return;
}

class praTributariaSue extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $basLib;
    public $proLibSerie;
    public $praLibTributaria;
    public $utiEnte;
    public $nameForm = "praTributariaSue";
    public $divRis = "praTributariaSue_divRisultato";
    public $divRic = "praTributariaSue_divRicerca";
    public $gridTributaria = "praTributariaSue_gridTributaria";
    public $procSue;
    public $fiscale;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->basLib = new basLib();
            $this->praLibTributaria = new praLibTributaria();
            $this->proLibSerie = new proLibSerie();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->procSue = App::$utente->getKey($this->nameForm . '_procSue');
            $this->fiscale = App::$utente->getKey($this->nameForm . '_fiscale');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_procSue', $this->procSue);
            App::$utente->setKey($this->nameForm . '_fiscale', $this->fiscale);
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

                        /*
                         * Controllo la scelta dei procedimenti
                         */
                        if ($_POST[$this->nameForm . "_ProcSue"] == "") {
                            Out::msgInfo("Creazione file anagrafe tributaria", "Selezionare almeno un procedimento.");
                            break;
                        }

                        /*
                         * Controllo la presenza dell'aggregato
                         */
                        if (isset($_POST[$this->nameForm . "_Aggregato"])) {
                            if ($_POST[$this->nameForm . "_Aggregato"] == "") {
                                Out::msgInfo("Creazione file anagrafe tributaria", "Selezionare uno sportello aggregato");
                                break;
                            }
                        }

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
                        /*
                         * Assegno il nome del file
                         */
                        $Nome_file = itaLib::getPrivateUploadPath() . "/anagrafeTributariaEdilizia.txt";
                        $File = fopen($Nome_file, "w+");

                        /*
                         * Riporto la stringa della riga di testata
                         */
                        //$rigaTestata = $this->ScriviRigaInizioFine($H_012, "inizio", "", $paramTrib);
                        $arrRigaTestata = $this->ScriviRigaInizioFine("inizio");
                        if ($arrRigaTestata['msgErr']) {
                            Out::msgInfo("Attenzione", $arrRigaTestata['msgErr']);
                            break;
                        }
                        $rigaTestata = $arrRigaTestata['rigaInizioFine'];

                        /*
                         * Riporto le righe intermedie 
                         */
                        $righeDettaglio = $this->ScriviDettaglio();
                        if ($righeDettaglio === false) {
                            break;
                        }

                        /*
                         * Riporto la stringa con la riga finale
                         */
                        $arrRigaTestata = $this->ScriviRigaInizioFine("fine");
                        $rigaFinale = $arrRigaTestata['rigaInizioFine'];

                        /*
                         * Scrvio il file
                         */
                        //fwrite($File, $rigaTestata . $arrayIntermedio['righe'] . $rigaFinale);
                        fwrite($File, $rigaTestata . $righeDettaglio . $rigaFinale);
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
                    case $this->nameForm . '_Sportello_butt':
                        praRic:: praRicAnatsp($this->nameForm, "", "2");
                        break;
                    case $this->nameForm . '_Sett_butt':
                        praRic:: praRicAnaset($this->nameForm);
                        break;
                    case $this->nameForm . '_buttProcSue':
                        $whereSettore = $whereSportello = "";
                        if ($_POST[$this->nameForm . "_Sett"]) {
                            $whereSettore = " AND ITEEVT.IEVSTT = " . $_POST[$this->nameForm . "_Sett"];
                        }
                        if ($_POST[$this->nameForm . "_Sportello"]) {
                            $whereSportello = " AND ITEEVT.IEVTSP = " . $_POST[$this->nameForm . "_Sportello"];
                        }
                        //praRic::praRicAnapraMulti($this->nameForm, "SUE", " WHERE ITEEVT.IEVTSP=6 AND PRAOFFLINE = 0");
                        praRic::praRicAnapraMulti($this->nameForm, "SUE", " WHERE 1 $whereSettore $whereSportello AND PRAOFFLINE = 0");
                        break;
                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa($this->nameForm);
                        break;
                    case $this->nameForm . '_buttDelSue':
                        $this->procSue = "";
                        Out::valore($this->nameForm . "_ProcSue", "");
                        break;
                    case $this->nameForm . '_ric_siglaserie_butt':
                        proRic::proRicSerieArc($this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ric_siglaserie':
                        if ($_POST[$this->nameForm . '_ric_siglaserie']) {
                            $AnaserieArc_tab = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_ric_siglaserie'], 'sigla', true);
                            if (!$AnaserieArc_tab) {
                                Out::msgStop("Attenzione", "Sigla Inesistente.");
                                Out::valore($this->nameForm . '_ric_codiceserie', '');
                                Out::valore($this->nameForm . '_ric_siglaserie', '');
                                Out::valore($this->nameForm . '_descRicSerie', '');
                                break;
                            }
                            $result = count($AnaserieArc_tab);
                            if ($result > 1) {
                                $where = "WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($AnaserieArc_tab[0]['SIGLA']) . "'";
                                proRic::proRicSerieArc($this->nameForm, $where);
                                break;
                            }
                            Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_tab[0]['CODICE']);
                            Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_tab[0]['SIGLA']);
                            Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_tab[0]['DESCRIZIONE']);
                            break;
                        }
                        Out::valore($this->nameForm . '_ric_codiceserie', '');
                        Out::valore($this->nameForm . '_ric_siglaserie', '');
                        Out::valore($this->nameForm . '_descRicSerie', '');

                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Sett':
                        $codice = $_POST[$this->nameForm . '_Sett'];
                        if ($codice) {
                            $this->DecodAnaset($codice);
                        }
                        break;
                    case $this->nameForm . '_Sportello':
                        $codice = $_POST[$this->nameForm . '_Sportello'];
                        if ($codice) {
                            $this->DecodAnatsp($codice);
                        }
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
                    case "SUE":
                        foreach ($arrayRowid as $rowid) {
                            $Iteevt_rec = $this->praLib->GetIteevt($rowid, 'rowid');
                            $this->procSue .= $Iteevt_rec['ITEPRA'] . ";";
                        }
                        Out::valore($this->nameForm . "_ProcSue", $this->procSue);
                        break;
                    default:
                        break;
                }
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                }
            case "returnAnaset":
                $this->DecodAnaset($_POST['retKey'], 'rowid');
                break;
            case 'returnAnatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_procSue');
        App::$utente->removeKey($this->nameForm . '_fiscale');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function DecodAnaset($Codice, $tipoRic = 'codice') {
        $anaset_rec = $this->praLib->GetAnaset($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Sett", $anaset_rec['SETCOD']);
        Out::valore($this->nameForm . "_Desc_sett", $anaset_rec['SETDES']);
        return $anaset_rec;
    }

    function DecodAnatsp($Codice, $tipoRic = 'codice') {
        $anatsp_rec = $this->praLib->GetAnatsp($Codice, $tipoRic);
        Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_Desc_spor', $anatsp_rec['TSPDES']);
        return $anatsp_rec;
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $codiceserie = $_POST[$this->nameForm . '_ric_codiceserie'];
        $Dal_numserie = $_POST[$this->nameForm . '_Dal_numserie'];
        $al_numserie = $_POST[$this->nameForm . '_Al_numserie'];
        $annoserie = $_POST[$this->nameForm . '_Annoserie'];
        $Da_dataRic = $_POST[$this->nameForm . '_Da_dataRic'];
        $a_dataRic = $_POST[$this->nameForm . '_A_dataRic'];
        //
        $Procedimenti = $this->procSue;
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


        $sql = "SELECT
                    PROGES.*
                FROM
                    PROGES
                LEFT OUTER JOIN PRASTA ON PRASTA.STANUM = PROGES.GESNUM
                WHERE
                    PROGES.ROWID = PROGES.ROWID AND
                    STAFLAG <> 'Annullata'";

        if ($_POST[$this->nameForm . '_Anno'] != "") {
            $sql .= " AND " . $this->PRAM_DB->subString('GESDRI', 1, 4) . " = '" . $_POST[$this->nameForm . '_Anno'] . "'";
        }
        if ($_POST[$this->nameForm . '_Aggregato'] != "") {
            $sql .= " AND GESSPA = '" . $_POST[$this->nameForm . '_Aggregato'] . "'";
        }
        if ($Dal_numserie && $al_numserie) {
            $sql .= " AND (SERIEPROGRESSIVO BETWEEN '$Dal_numserie' AND '$al_numserie')";
        }
        if ($codiceserie) {
            $sql .= " AND SERIECODICE = $codiceserie";
        }
        if ($annoserie) {
            $sql .= " AND SERIEANNO = $annoserie";
        }
        if ($Da_dataRic && $a_dataRic) {
            $sql .= " AND (GESDRI BETWEEN '$Da_dataRic' AND '$a_dataRic')";
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

    function ScriviRigaInizioFine($tipo) {
        $msgErr = "";
        if ($tipo == "inizio") {
            $i = "0";
        } elseif ($tipo == "fine") {
            $i = "9";
        }

        $fiscale = $this->formData[$this->nameForm . "_Fiscale"];

        if (isset($this->formData[$this->nameForm . "_Aggregato"])) {
            $Anaspa_rec = $this->praLib->GetAnaspa($_POST[$this->nameForm . "_Aggregato"]);
            $denominazione = $Anaspa_rec['SPADES'];
            $comune = $Anaspa_rec['SPACOM'];
            $catastale = $Anaspa_rec['SPACCA'];
        } else {
            $Anatsp_rec = $this->praLib->GetAnatsp("6");
            $denominazione = $Anatsp_rec['TSPDES'];
            $comune = $Anatsp_rec['TSPCOM'];
            $catastale = $Anatsp_rec['TSPCCA'];
        }

        if ($denominazione == "" || $catastale == "") {
            $msgErr .= "Mancano la denominazione o il codice catastale dell'ente<br>";
        }

        $t1 = "DIAXX";
        $t2 = "29";
        if (strlen($fiscale) == 11) {
            //$t3 = str_pad($fiscale, 16, " ", STR_PAD_LEFT); //PIVA
            $t3 = str_pad($fiscale, 16, " ", STR_PAD_RIGHT); //PIVA
        } elseif (strlen($fiscale) == 16) {
            $t3 = $fiscale; //CF
        }
        $t4 = str_pad("", 26, " ", STR_PAD_RIGHT); // NOME
        $t5 = str_pad("", 25, " ", STR_PAD_RIGHT); // COGNOME
        $t6 = " ";                                // SESSO
        $t7 = str_pad("", 8, " ", STR_PAD_RIGHT);  // DATA DI NASCITA
        $t8 = str_pad("", 4, " ", STR_PAD_RIGHT);  // CODICE CATASTALE COMUNE DI NASCITA
        $t9 = str_pad($denominazione . " " . $comune, 60, " ", STR_PAD_RIGHT); //DENOMINAZIONE
        $t10 = $catastale; //CODICE CATASTALE
        $t11 = str_pad($this->formData[$this->nameForm . '_Anno'], 4, " ", STR_PAD_RIGHT);  //ANNO DATI
        //
        $t12 = str_pad("", 211, " ", STR_PAD_RIGHT);
        $t13 = "A";
        $t14 = chr(13) . chr(10);
        $rigaInizioFine = $i . $t1 . $t2 . $t3 . $t4 . $t5 . $t6 . $t7 . $t8 . $t9 . $t10 . $t11 . $t12 . $t13 . $t14;
        //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record$tipo.log", $rigaInizioFine, FILE_APPEND);
        return array('rigaInizioFine' => $rigaInizioFine, 'msgErr' => $msgErr);
    }

    function ScriviDettaglio() {
        $sql = $this->CreaSql();
        $Proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $msgErrTot = "";
        $rigaTot = "";
        foreach ($Proges_tab as $Proges_rec) {
            $arrRichiesta = $this->rigaDatiRichiesta($Proges_rec);
            if (!$arrRichiesta) {
                Out::msgStop("Attenzione", "Dichiarante / Esibente non trovato per la pratica " . $Proges_rec['GESNUM']);
                return false;
            }
            if ($arrRichiesta['msgErr']) {
                $msgErrTot .= $arrRichiesta['msgErr'] . "<br>";
            } else {
                $rigaTot .= $arrRichiesta['rigaRichiesta'];
            }

            $arrBeneficiari = $this->rigaDatiBeneficiari($Proges_rec);
            if ($arrBeneficiari['msgErr']) {
                $msgErrTot .= $arrBeneficiari['msgErr'] . "<br>";
            } else {
                $rigaTot .= $arrBeneficiari['righeBeneficiario'];
            }

            $arrCatastali = $this->rigaDatiCatastali($Proges_rec);
            if ($arrCatastali['msgErr']) {
                $msgErrTot .= $arrCatastali['msgErr'] . "<br>";
            } else {
                $rigaTot .= $arrCatastali['righeImmobile'];
            }

            $arrProfessionisti = $this->rigaDatiProfessionisti($Proges_rec);
            if ($arrProfessionisti['msgErr']) {
                $msgErrTot .= $arrProfessionisti['msgErr'] . "<br>";
            } else {
                $rigaTot .= $arrProfessionisti['righeProfessionisti'];
            }

            $arrImprese = $this->rigaDatiImpresa($Proges_rec);
            if ($arrImprese['msgErr']) {
                $msgErrTot .= $arrImprese['msgErr'] . "<br>";
            } else {
                $rigaTot .= $arrImprese['righeImpresa'];
            }
        }

        if ($msgErrTot) {
            Out::msgStop("Attenzione", $msgErrTot);
            return false;
        }

        return $rigaTot;
    }

    function rigaDatiRichiesta($Proges_rec) {
        $msgErr = "";
        $H_tipo = "1";
        $this->fiscale = "";

        /*
         * Record Dichiarante
         */
        $anades_recDich = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD']);
        if (!$anades_recDich) {
            $anades_recDich = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']);
            if (!$anades_recDich) {
                $msgErr = "Dichiarante / Esibente non trovato per la pratica " . $Proges_rec['GESNUM'];
                return array('msgErr' => $msgErr);
            }
        }

        /*
         * Record Localizzazione Intervento
         */
        $anades_recLoc = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']);
        if ($anades_recLoc) {
            $indirizzo = utf8_decode($anades_recLoc['DESIND']);
            $civico = $anades_recLoc['DESCIV'];
        } else {
            $indirizzo = $this->praLibTributaria->GetIndirizzoRichiesta($Proges_rec['GESNUM']);
            $civico = $this->praLibTributaria->GetCivicoRichiesta($Proges_rec['GESNUM']);
        }
        if ($indirizzo == "" && $civico == "") {
            $msgErr = "Indirizzo area interessata non trovato per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            return array('msgErr' => $msgErr);
        }

        $indirizzo = substr($indirizzo, 0, 30);
        $civico = substr($civico, 0, 4);

        $fisica = true;
        if (strlen($anades_recDich['DESFIS']) == 16) {
            $this->fiscale = $anades_recDich['DESFIS'];
        } elseif (strlen($anades_recDich['DESFIS']) == 11) {
            $this->fiscale = str_pad($anades_recDich['DESFIS'], 16, " ", STR_PAD_RIGHT);
        } elseif (strlen($anades_recDich['DESPIVA']) == 11) {
            $this->fiscale = str_pad($anades_recDich['DESPIVA'], 16, " ", STR_PAD_RIGHT);
            $fisica = false;
        }

        $H_001 = $this->fiscale;
        if ($this->fiscale == "") {
            $msgErr = "Codice Fiscale richiesta non trovato per la pratica " . $Proges_rec['GESNUM'] . "<br>";
            return array('msgErr' => $msgErr);
        }
        if (strlen($this->fiscale) != 16 && strlen($this->fiscale) != 11) {
            $msgErr = "Codice Fiscale Dichiarante non conforme per la pratica " . $Proges_rec['GESNUM'] . "<br>";
            return array('msgErr' => $msgErr);
        }
        $decodCF = new utiDecodCodiceFiscale();
        $decodFiscale = $decodCF->DecodCF($this->fiscale);

        if ($fisica == true) {
            $H_002 = $this->praLibTributaria->GetCognomeDichiarante($anades_recDich);  // COGNOME 
            if ($H_002 == "") {
                $msgErr = "Cognome Dichiarante Richiesta non trovato per la pratica numero " . $Proges_rec['GESNUM'] . " proc " . $Proges_rec['GESPRO'] . "<br>";
                return array('msgErr' => $msgErr);
            }
            $H_003 = $this->praLibTributaria->GetNomeDichiarante($anades_recDich); // NOME 
            if ($H_003 == "") {
                $msgErr = "Nome Dichiarante Richiesta non trovato per la pratica numero " . $Proges_rec['GESNUM'] . " proc " . $Proges_rec['GESPRO'] . "<br>";
                return array('msgErr' => $msgErr);
            }
            $H_004 = $this->praLibTributaria->GetSessoRichiesta($anades_recDich); // SESSO 
            if ($H_004 == "") {
                $H_004 = $this->praLibTributaria->GetSessoByCF($this->fiscale); // SESSO 
                if ($H_004 == "") {
                    $msgErr = "Sesso Richiesta non trovato per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                    return array('msgErr' => $msgErr);
                }
            }
            if ($anades_recDich['DESNASDAT']) {
                $dataNascita = substr($anades_recDich['DESNASDAT'], 6, 2) . substr($anades_recDich['DESNASDAT'], 4, 2) . substr($anades_recDich['DESNASDAT'], 0, 4);
            } else {
                $dataNascita = substr($decodFiscale['datanascita'], 6, 2) . substr($decodFiscale['datanascita'], 4, 2) . substr($decodFiscale['datanascita'], 0, 4);
            }
            if ($dataNascita == "") {
                $msgErr = "Data Nascita non trovata per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                return array('msgErr' => $msgErr);
            }
            $H_005 = $dataNascita; // DATA NASCITA
            $H_006 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE COMUNE NASCITA 
            $H_007 = str_pad("", 60, " ", STR_PAD_RIGHT); // DENOMINAZIONE 
            $H_008 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE SEDE LEGALE 
        } else {
            $H_002 = str_pad("", 26, " ", STR_PAD_RIGHT); // COGNOME  
            $H_003 = str_pad("", 25, " ", STR_PAD_RIGHT); // NOME  
            $H_004 = " "; // SESSO
            $H_005 = str_pad("", 8, " ", STR_PAD_RIGHT); // DATA NASCITA  
            $H_006 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE COMUNE NASCITA 
            $H_007 = str_pad($anades_recDich['DESNOM'], 60, " ", STR_PAD_RIGHT); // DENOMINAZIONE 
            $H_008 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE SEDE LEGALE 
        }
        $H_009 = $this->praLibTributaria->GetQualifica($Proges_rec['GESNUM']); // QUALIFICA
        if ($H_009 == "") {
            $msgErr = "Qualifica della richiesta non trovata per la pratica numero " . $Proges_rec['GESNUM'] . " proc " . $Proges_rec['GESPRO'] . "<br>";
            return array('msgErr' => $msgErr);
        }

        $H_010 = $this->formData[$this->nameForm . "_TipoRichiesta"]; //$this->praLibTributaria->GetTipoRichiesta($Proges_rec['GESPRO']); // TIPO RICHIESTA
        $H_011 = "7"; // TIPOLOGIA INTERVENTO
        $H_012 = $this->praLibTributaria->GetProtocollo($Proges_rec); //N. PROTOCOLLO
        $H_013 = $this->formData[$this->nameForm . "_TipoInvio"]; //$this->praLibTributaria->GetTipologiaRichiesta($Proges_rec['GESPRO']); // TIPOLOGIA RICHIESTA
        $H_014 = substr($Proges_rec['GESDRI'], 6, 2) . substr($Proges_rec['GESDRI'], 4, 2) . substr($Proges_rec['GESDRI'], 0, 4); // DATA PRESENTAZIONE RICHIESTA
        $H_015 = substr($Proges_rec['GESDRI'], 6, 2) . substr($Proges_rec['GESDRI'], 4, 2) . substr($Proges_rec['GESDRI'], 0, 4); // DATA INIZIO LAVORI
        $H_016 = str_pad("", 8, " ", STR_PAD_RIGHT); // DATA FINE LAVORI
        $H_017 = str_pad($indirizzo . " " . $civico, 35, " ", STR_PAD_RIGHT); // INDIRIZZO LAVORI
        //
        $H_018 = str_pad("", 139, " ", STR_PAD_RIGHT);
        $H_019 = "A";
        $H_020 = chr(13) . chr(10);
        //
        $rigaRichiesta = $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008 . $H_009 . $H_010 . $H_011 . $H_012 . $H_013 . $H_014 . $H_015 . $H_016 . $H_017 . $H_018 . $H_019 . $H_020;
        //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record1.log", $Proges_rec['GESNUM'] . "|" . $rigaRichiesta, FILE_APPEND);

        return array('rigaRichiesta' => $rigaRichiesta, 'msgErr' => $msgErr);
    }

    function rigaDatiBeneficiari($Proges_rec) {
        /*
         * Record Beneficiari
         */
        $msgErr = "";
        $anades_tab = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", true, praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD']);
        if (!$anades_tab) {
            $msgErr .= "Beneficiari non trovati per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            return;
        }

        $righeBeneficiario = "";
        foreach ($anades_tab as $anades_rec) {
            $H_tipo = "2";
            $H_001 = $this->fiscale; // FISCALE RICHIEDENTE
            $H_002 = $this->praLibTributaria->GetProtocollo($Proges_rec); //N. PROTOCOLLO
            //
            $fisica = true;
            if (strlen($anades_rec['DESFIS']) == 16) {
                $H_003 = $anades_rec['DESFIS']; //FISCALE BENEFICIARIO
                $decodCF = new utiDecodCodiceFiscale();
                $decodFiscale = $decodCF->DecodCF($anades_rec['DESFIS']);
            }
            if ($H_003 == "") {
                if (strlen($anades_rec['DESPIVA']) == 11) {
                    //$H_003 = str_pad($anades_rec['DESPIVA'], 11, " ", STR_PAD_LEFT); //PARTITA IVA BENEFICIARIO 
                    $H_003 = str_pad($anades_rec['DESPIVA'], 16, " ", STR_PAD_RIGHT); //PARTITA IVA BENEFICIARIO 
                    $fisica = false;
                }
            }
            if ($H_003 == "") {
                $msgErr .= "Codice Fiscale Beneficiario non trovato per la pratica " . $Proges_rec['GESNUM'] . "<br>";
            }

            if (strlen($H_003) != 16 && strlen($H_003) != 11) {
                $msgErr .= "Codice Fiscale Beneficiario non conforme per la pratica " . $Proges_rec['GESNUM'] . "<br>";
            }
            if ($fisica == true) {
                $arrDesnom = explode(" ", $anades_rec['DESNOM']);
                $cogn = $anades_rec['DESCOGNOME']; //$this->praLibTributaria->GetCognomeDichiarante($anades_rec);  // COGNOME 
                if ($cogn == "") {
                    $cogn = $arrDesnom[0];
                    if ($cogn == "") {
                        $msgErr .= "Cognome beneficiario non trovato per la pratica numero " . $Proges_rec['GESNUM'] . " proc " . $Proges_rec['GESPRO'] . "<br>";
                    }
                }
                $H_004 = str_pad(trim($cogn), 26, " ", STR_PAD_RIGHT);

                $nome = $anades_rec['DESNOME']; //$this->praLibTributaria->GetNomeDichiarante($anades_rec); // NOME 
                if ($nome == "") {
                    $nome = $arrDesnom[1];
                    if ($nome == "") {
                        $msgErr .= "Nome beneficiario non trovato per la pratica numero " . $Proges_rec['GESNUM'] . " proc " . $Proges_rec['GESPRO'] . "<br>";
                    }
                }
                $H_005 = str_pad(trim($nome), 25, " ", STR_PAD_RIGHT);

                $H_006 = $this->praLibTributaria->GetSessoRichiesta($anades_rec); // SESSO BENEFICIARIO
                if ($H_006 == "") {
                    $H_006 = $this->praLibTributaria->GetSessoByCF($H_003); // SESSO 
                    if ($H_006 == "") {
                        $msgErr .= "Sesso non trovato per il beneficiario della pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                    }
                }
                if ($anades_rec['DESNASDAT']) {
                    $dataNascita = substr($anades_rec['DESNASDAT'], 6, 2) . substr($anades_rec['DESNASDAT'], 4, 2) . substr($anades_rec['DESNASDAT'], 0, 4);
                } else {
                    $dataNascita = substr($decodFiscale['datanascita'], 6, 2) . substr($decodFiscale['datanascita'], 4, 2) . substr($decodFiscale['datanascita'], 0, 4);
                }
                if ($dataNascita == "") {
                    $msgErr .= "Data Nascita Beneficiari non trovata per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                }
                $H_007 = $dataNascita; // DATA NASCITA BENEFICIARIO
                $H_008 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE COMUNE NASCITA BENEFICIARIO
                $H_009 = str_pad("", 60, " ", STR_PAD_RIGHT); // DENOMINAZIONE BENEFICIARIO
                $H_010 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE SEDE LEGALE BENEFICIARIO
            } else {
                $H_004 = str_pad("", 26, " ", STR_PAD_RIGHT); // COGNOME BENEFICIARIO 
                $H_005 = str_pad("", 25, " ", STR_PAD_RIGHT); // NOME BENEFICIARIO 
                $H_006 = " "; // SESSO
                $H_007 = str_pad("", 8, " ", STR_PAD_RIGHT); // DATA NASCITA BENEFICIARIO 
                $H_008 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE COMUNE NASCITA 
                $H_009 = str_pad($anades_rec['DESNOM'], 60, " ", STR_PAD_RIGHT); // DENOMINAZIONE BENEFICIARIO
                $H_010 = str_pad("", 4, " ", STR_PAD_RIGHT); // COD. CATASTALE SEDE LEGALE BENEFICIARIO
            }
            $H_011 = $this->praLibTributaria->GetQualifica($Proges_rec['GESNUM']); // QUALIFICA BENEFICAIRIO
            //
            $H_012 = str_pad("", 185, " ", STR_PAD_RIGHT);
            $H_013 = "A";
            $H_014 = chr(13) . chr(10);
            $righeBeneficiario .= $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008 . $H_009 . $H_010 . $H_011 . $H_012 . $H_013 . $H_014;
        }
        //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record2.log", $Proges_rec['GESNUM'] . "|" . $righeBeneficiario, FILE_APPEND);
        return array('righeBeneficiario' => $righeBeneficiario, 'msgErr' => $msgErr);
    }

    function rigaDatiCatastali($Proges_rec) {
        $righeImmobile = $msgErr = "";
        $praimm_tab = $this->praLib->GetPraimm($Proges_rec['GESNUM'], "codice", true);
        if (!$praimm_tab) {
            $msgErr .= "Immobili non trovati per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            return;
        }
        foreach ($praimm_tab as $praimm_rec) {
            $arrDatiCatastali = array();
            $H_tipo = "3";
            $H_001 = $this->fiscale; // FISCALE RICHIEDENTE
            $H_002 = $this->praLibTributaria->GetProtocollo($Proges_rec); //N. PROTOCOLLO
            $H_003 = $praimm_rec['TIPO']; // TIPO IMMOBILE
            if ($H_003 == "") {
                $H_003 = "F";
            }

            $H_004 = str_pad($praimm_rec['SEZIONE'], 3, " ", STR_PAD_RIGHT); // SEZIONE IMMOBILE
            if ($praimm_rec['FOGLIO'] == "" && $praimm_rec['PARTICELLA'] == "") {
                $arrDatiCatastali = $this->praLibTributaria->GetDatiCatastali($Proges_rec['GESNUM']);
                $H_005 = str_pad($arrDatiCatastali['FOGLIOPRINC'], 5, " ", STR_PAD_RIGHT); // FOGLIO IMMOBILE
                $H_006 = str_pad($arrDatiCatastali['PARTICELLAPRINC'], 5, " ", STR_PAD_RIGHT); // PARTICELLA IMMOBILE
            } else {
                $H_005 = str_pad($praimm_rec['FOGLIO'], 5, " ", STR_PAD_RIGHT); // FOGLIO IMMOBILE
                $H_006 = str_pad($praimm_rec['PARTICELLA'], 5, " ", STR_PAD_RIGHT); // PARTICELLA IMMOBILE
            }
            if ($H_005 == "") {
                $msgErr .= "Foglio non trovato per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            }
            if ($H_006 == "") {
                $msgErr .= "Particella non trovata per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            }
            $H_007 = str_pad("", 4, " ", STR_PAD_RIGHT); // ESTENSIONE PARTICELLA IMMOBILE
            $H_008 = " "; // TIPO PARTICELLA IMMOBILE (E/F)
            $H_009 = str_pad($praimm_rec['SUBALTERNO'], 4, " ", STR_PAD_RIGHT); // SUBALTERNO IMMOBILE
            //
            $H_010 = str_pad("", 307, " ", STR_PAD_RIGHT);
            $H_011 = "A";
            $H_012 = chr(13) . chr(10);
            $righeImmobile .= $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008 . $H_009 . $H_010 . $H_011 . $H_012;
        }
        //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record3.log", $righeImmobile, FILE_APPEND);
        return array('righeImmobile' => $righeImmobile, 'msgErr' => $msgErr);
    }

    function rigaDatiProfessionisti($Proges_rec) {

        /*
         * Record Tecnico Progettista
         */
        $anades_recTecPrg = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['TECPRG']['RUOCOD']);
        if (!$anades_recTecPrg) {
            $anades_recTecPrg = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['TECSTR']['RUOCOD']);
            if (!$anades_recTecPrg) {
                $anades_recTecPrg = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['TECNICO']['RUOCOD']);
                if (!$anades_recTecPrg) {
                    $anades_recTecPrg = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", true, praRuolo::$SISTEM_SUBJECT_ROLES['ALTRITECNICI']['RUOCOD']);
                    if (!$anades_recTecPrg) {
                        $anades_recTecPrg = array();
                    }
                }
            }
        }

//        if (!$anades_recTecPrg) {
//            $msgErr .= "Tecnico/Professionista non trovato per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
//        }

        if ($anades_recTecPrg) {
            $H_007 = "0"; //QUALIFICA PROGETTISTA
        }

        /*
         * Record Direttore dei Lavori
         */
        $anades_recDirLav = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DIRARC']['RUOCOD']);
        if (!$anades_recDirLav) {
            $anades_recDirLav = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DIRSTR']['RUOCOD']);
            if (!$anades_recDirLav) {
                $anades_recDirLav = array(); // per il merge altrimenti non funziona
            }
        }
        if ($anades_recDirLav) {
            $H_007 = "1"; //DIRETTORE DEI LAVORI
        }

        /*
         * Record Procuratore
         */
        $anades_recProcuratore = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['PROCURATORE']['RUOCOD']);
        if (!$anades_recProcuratore) {
            $anades_recProcuratore = array(); // per il merge altrimenti non funziona
        } else {
            $H_007 = "1"; //DIRETTORE DEI LAVORI
        }

        /*
         * Se sono la stessa persona setto il tipo 2
         */
        if ($anades_recDirLav) {
            if ($anades_recTecPrg['DESFIS'] == $anades_recDirLav['DESFIS'] || $anades_recTecPrg['DESPIVA'] == $anades_recDirLav['DESPIVA']) {
                $H_007 = "2"; //ENTRAMBI
            }
        }

        $arrayProf = array();
        /*
         * Mergio i 3 array
         */
        $arrayProfTemp = array_merge($anades_recTecPrg, $anades_recDirLav, $anades_recProcuratore);
        if (!$arrayProfTemp) {
            $arrayProfTemp = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']);
            $H_007 = "1"; //DIRETTORE DEI LAVORI
        }

        if (!isset($arrayProfTemp[0])) {
            $arrayProf[0] = $arrayProfTemp;
        } else {
            $arrayProf = $arrayProfTemp;
        }

        $H_003 = "";
        $righeProfessionisti = $msgErr = "";
        foreach ($arrayProf as $professionista) {
            $H_tipo = "4";
            $H_001 = $this->fiscale; // FISCALE RICHIEDENTE
            $H_002 = $this->praLibTributaria->GetProtocollo($Proges_rec); //N. PROTOCOLLO
            //
            if ($professionista['DESRUO'] == praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']) {
                $H_003 = $professionista['DESFIS']; //FISCALE
            } else {
                if (strlen($professionista['DESFIS']) == 16) {
                    $H_003 = $professionista['DESFIS']; //FISCALE
                }
                if ($H_003 == "") {
                    if (strlen($professionista['DESPIVA']) == 11) {
                        //$H_003 = str_pad($professionista['DESPIVA'], 16, " ", STR_PAD_RIGHT); //PARTITA IVA
                        $H_003 = $professionista['DESPIVA']; //PARTITA IVA
                    }
                }
            }
            if ($H_003 == "") {
                $msgErr .= "Codice Fiscale o P. Iva Professionista non trovato per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            } else {
                $H_003 = str_pad($H_003, 16, " ", STR_PAD_RIGHT); //PARTITA IVA
            }
            if (strlen($H_003) != 16 && strlen($H_003) != 11) {
                $msgErr .= "Codice Fiscale Professionista non conforme per la pratica " . $Proges_rec['GESNUM'] . "<br>";
            }
            $H_004 = $this->praLibTributaria->GetNomeAlbo($Proges_rec['GESNUM'], $professionista['DESRUO']); //NOME ALBO
            $H_005 = "AN";
            if ($professionista['DESPRO'] && strlen($professionista['DESPRO']) == 2) {
                $provincia_rec = $this->basLib->getGenericTab("SELECT * FROM PROVINCE WHERE SIGLA = '" . strtoupper($professionista['DESPRO']) . "'", false, 'COMUNI');
                if (!$provincia_rec) {
                    $msgErr .= "Provincia iscrizione albo non trovata in anagrafica per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                } else {
                    $H_005 = $professionista['DESPRO']; //PROVINCIA ISCRIZIONE ALBO
                }
            } else {
                $msgErr .= "Provincia iscrizione albo non valida per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
            }
            $numIscr = $this->praLibTributaria->GetNumIscrizioneAlbo($professionista); //NUMERO ISCRIZIONE ALBO
//            if ($numIscr == "") {
//                $numIscr = "2";
//            }
            $H_006 = str_pad($numIscr, 10, " ", STR_PAD_RIGHT);

            //
            $H_008 = str_pad("", 300, " ", STR_PAD_RIGHT);
            $H_009 = "A";
            $H_010 = chr(13) . chr(10);
            $righeProfessionisti .= $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008 . $H_009 . $H_010;
        }
        //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record4.log", $righeProfessionisti, FILE_APPEND);
        return array('righeProfessionisti' => $righeProfessionisti, 'msgErr' => $msgErr);
    }

    function rigaDatiImpresa($Proges_rec) {
        $msgErr = "";
        $anades_tab = $this->praLib->GetAnades($Proges_rec['GESNUM'], "ruolo", true, praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESAESEC']['RUOCOD']);
        if ($anades_tab) {
            $righeImpresa = "";
            foreach ($anades_tab as $anades_rec) {
                $denom = trim(substr($anades_rec['DESNOM'], 0, 50));
                if ($denom == "") {
                    $msgErr .= "Denominazione Impresa Esecutrice non trovata per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                }

                $H_tipo = "5";
                $H_001 = $this->fiscale; // FISCALE RICHIEDENTE
                $H_002 = $this->praLibTributaria->GetProtocollo($Proges_rec); //N. PROTOCOLLO
                $H_003 = $this->praLibTributaria->GetPartitaIvaImpresa($anades_rec); // PARTIVA IVA
                if (strlen($H_003) != 11 || !is_numeric($H_003)) {
                    //$msgErr .= "Partita Iva Impresa Esecutrice non conforme per la pratica numero " . $Proges_rec['GESNUM'] . "<br>";
                    continue;
                }
                $H_004 = str_pad($denom, 50, " ", STR_PAD_RIGHT); // DENOMINAZIONE
                $H_005 = str_pad("", 4, " ", STR_PAD_RIGHT); // CODICE CATASTALE COMUNE SEDE LEGALE
                //
                $H_006 = str_pad("", 265, " ", STR_PAD_RIGHT);
                $H_007 = "A";
                $H_008 = chr(13) . chr(10);
                $righeImpresa .= $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008;
                //$righeImpresa .= $Proges_rec['GESNUM'] . "|" . $H_tipo . $H_001 . $H_002 . $H_003 . $H_004 . $H_005 . $H_006 . $H_007 . $H_008;
            }
            //file_put_contents("/dati2/assistenza/install/appoggio_20180418/tributaria/record5.log", $righeImpresa, FILE_APPEND);
            return array('righeImpresa' => $righeImpresa, 'msgErr' => $msgErr);
        }
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
        $this->procSue = "";
        Out::attributo($this->nameForm . '_ProcSedeFissa', "readonly", '0');
        Out::attributo($this->nameForm . '_ProcSab', "readonly", '0');
        Out::attributo($this->nameForm . '_ProcAreePub', "readonly", '0');
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
        Out::select($this->nameForm . '_TipoRichiesta', 1, "0", "1", "PDC, Agibilità o altro atto di assenso");
        Out::select($this->nameForm . '_TipoRichiesta', 1, "1", "0", "Denuncia di Inizio Attività (DIA)");
        //
        Out::select($this->nameForm . '_TipoInvio', 1, "0", "1", "Rilascio");
        Out::select($this->nameForm . '_TipoInvio', 1, "1", "0", "Cessazione");
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

}

?>