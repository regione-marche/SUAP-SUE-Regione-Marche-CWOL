<?php

/**
 *
 * GRAFICI
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

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praGraf() {
    $praGraf = new praGraf();
    $praGraf->parseEvent();
    return;
}

class praGraf extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $riepilogo;
    public $nameForm = "praGraf";
    public $divRis = "praGraf_divRisultato";
    public $divRic = "praGraf_divRicerca";
    public $gridGraf = "praGraf_gridGraf";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->riepilogo = App::$utente->getKey($this->nameForm . '_riepilogo');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_riepilogo', $this->riepilogo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridGraf:
                        $sql = $this->CreaSql();
                        if ($sql != false) {
                            $ordinamento = $_POST['sidx'];
                            if ($ordinamento == 'STATO') {
                                break;
                            }
                            $ita_grid01 = new TableView($this->gridGraf, array('sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows($_POST['rows']);
                            $ita_grid01->setSortIndex($ordinamento);
                            $ita_grid01->setSortOrder($_POST['sord']);
                            $Result_tab = $ita_grid01->getDataArray();
                            $Result_tab = $this->elaboraRecords($Result_tab);
                            $ita_grid01->getDataPageFromArray('json', $Result_tab);
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                //$sql = $this->CreaSqlXls();
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $this->elaboraRecordsXls($Result_tab1);
                $ita_grid02 = new TableView($this->gridGraf, array(
                    'arrayTable' => $Result_tab2));
                //$ita_grid02->setSortIndex('GESNUM');
                //$ita_grid02->setSortOrder('desc');
                $ita_grid02->exportXLS('', 'pratiche.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praPratiche', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        // Importo l'ordinamento del filtro
                        try {
                            $sql = $this->CreaSql();
                            $tab = $this->praLib->getGenericTab($sql);
                            if ($sql != false) {
                                $ita_grid01 = new TableView($this->gridGraf, array(
                                    'sqlDB' => $this->PRAM_DB,
                                    'sqlQuery' => $sql));
                                $ita_grid01->setPageNum(1);
                                $ita_grid01->setPageRows(14);
                                $ita_grid01->setSortIndex('GESNUM');
                                $ita_grid01->setSortOrder('desc');
                                Out::setFocus('', $this->nameForm . '_AltraRicerca');
                                $Result_tab = $ita_grid01->getDataArray();
                                $Result_tab = $this->elaboraRecords($Result_tab);
                                if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                                    Out::msgStop("Selezione", "Nessun record trovato.");
                                    $this->OpenRicerca();
                                } else {
                                    //// Visualizzo la ricerca
                                    $this->Nascondi();
                                    Out::hide($this->divRic, '');
                                    Out::show($this->divRis, '');
                                    Out::show($this->nameForm . "_boxGrafico");
                                    Out::show($this->nameForm . '_AltraRicerca');
                                    TableView::enableEvents($this->gridGraf);
                                }
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_Procedimento');
                        break;
                    case $this->nameForm . '_Responsabile_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Responsabile", '', $this->nameForm . '_Responsabile');
                        break;
                    case $this->nameForm . '_Esegui':
                        switch ($_POST[$this->nameForm . '_tipoGrafico']) {
                            case 'Agg':
                                $titolo = "PRATICHE PER SPORTELLO AGGREGATO";
                                $report = 'praGrafAgg';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Ser':
                                $titolo = "PRATICHE PER SERVIZIO";
                                $report = 'praGraf';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Set':
                                $titolo = "PRATICHE PER SETTORE PROCEDIMENTI";
                                $report = 'praGrafSett';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Att':
                                $titolo = "PRATICHE PER ATTIVITA' PROCEDIMENTI";
                                $report = 'praGrafAtt';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Tseg':
                                $titolo = "PRATICHE PER TIPOLOGIA SEGNALAZIONE ";
                                //$report = 'praGrafTseg';
                                $report = 'praGrafTsegNew';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Dreg':
                                $titolo = "PRATICHE PER DATA REGISTRZIONE ";
                                $report = 'praGrafDreg';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Dchi':
                                $titolo = "PRATICHE PER DATA CHIUSURA";
                                $report = 'praGrafDchi';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'Asseg':
                                $titolo = "PRATICHE PER ASSEGNATARIO";
                                $report = 'praGrafAssegnatario';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'SetPassi':
                                $titolo = "NUMERO PASSI PRATICHE PER SETTORE E ATTIVITA";
                                $report = 'praGrafSettAttPassi';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            case 'PassiMese':
                                $titolo = "NUMERO PASSI PRATICHE PER MESE";
                                $report = 'praGrafPassiMese';
                                $this->DisegnaGrafico($titolo, $report, $this->riepilogo);
                                break;
                            default:
                                Out::msgInfo('Attenzione.', 'Selezionare un tipo grafico.');
                                break;
                        }
                        break;
                    case $this->nameForm . '_pratip_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "RICERCA CATEGORIE", '', "pratip");
                        break;
                    case $this->nameForm . '_prastt_butt':
                        praRic::praRicAnaset($this->nameForm, "", 'prastt');
                        break;
                    case $this->nameForm . '_praatt_butt':
                        if ($_POST[$this->nameForm . '_prastt']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_prastt'] . "'";
                            praRic::praRicAnaatt($this->nameForm, $where, 'praatt');
                        } else {
                            Out::msgInfo("Attenzione!!!", "Scegliere prima un settore");
                        }
                        break;
                    case $this->nameForm . '_pratsp_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . "_Evento_butt":
                        praRic::ricAnaeventi($this->nameForm);
                        break;
                    case $this->nameForm . '_StatoPasso_butt':
                        praRic::praRicAnastp($this->nameForm, '', "STATOPASSO");
                        break;
                    case $this->nameForm . '_CodUtenteAss_butt':
                        $msgDetail = "Scegliere il soggetto di cui si vuol sapere le pratiche assegnate.";
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Soggetti", " WHERE NOMABILITAASS = 1 ", $this->nameForm . "_ricercaAss", false, null, $msgDetail, true);
                        break;
                    case $this->nameForm . "_SvuotaRicerca":
                        Out::clearFields($this->nameForm, $this->divRic);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Anno':
                        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
                        $Al_num = $_POST[$this->nameForm . '_Al_num'];
                        $Anno = $_POST[$this->nameForm . '_Anno'];
                        if ($Anno != '' && $Dal_num == $Al_num && $Dal_num != '') {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            $Proges_tab = $this->praLib->GetProges($Anno . $Dal_num, 'codice', true);
                            if (count($Proges_tab) == 1) {
                                Out::valore($this->nameForm . '_Dal_num', '');
                                Out::valore($this->nameForm . '_Al_num', '');
                                $this->Dettaglio($Proges_tab[0]['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_pratip':
                        $codice = $_POST[$this->nameForm . '_pratip'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatip_rec = $this->praLib->GetAnatip($codice);
                            if ($Anatip_rec) {
                                Out::valore($this->nameForm . '_pratip', $Anatip_rec['TIPCOD']);
                                Out::valore($this->nameForm . '_tipologia', $Anatip_rec['TIPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_pratsp':
                        $codice = $_POST[$this->nameForm . '_pratsp'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatsp_rec = $this->praLib->GetAnatsp($codice);
                            if ($Anatsp_rec) {
                                Out::valore($this->nameForm . '_pratsp', $Anatsp_rec['TSPCOD']);
                                Out::valore($this->nameForm . '_sportello', $Anatsp_rec['TSPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_prastt':
                        $Anaset_rec = $this->praLib->GetAnaset($_POST[$this->nameForm . '_prastt']);
                        if ($Anaset_rec) {
                            Out::valore($this->nameForm . '_prastt', $Anaset_rec["SETCOD"]);
                            Out::valore($this->nameForm . '_settoreAttivita', $Anaset_rec["SETDES"]);
                        }
                        break;
                    case $this->nameForm . '_praatt':
                        $Anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_praatt'], 'condizionato', false, $_POST[$this->nameForm . '_prastt']);
                        if ($Anaatt_rec) {
                            Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                            Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                        }
                        break;
                    case $this->nameForm . '_Procedimento':
                        $proc = $_POST[$this->nameForm . '_Procedimento'];
                        if ($proc) {
                            $proc = str_pad($proc, 6, "0", STR_PAD_LEFT);
                            $this->DecodAnapra($proc, $retid);
                        }
                        break;
                    case $this->nameForm . '_Responsabile':
                        $codice = $_POST[$this->nameForm . '_Responsabile'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $Ananom_rec = $this->praLib->GetAnanom($codice);
                        if ($Ananom_rec) {
                            Out::valore($this->nameForm . '_Responsabile', $Ananom_rec["NOMRES"]);
                            Out::valore($this->nameForm . '_Desc_resp', $Ananom_rec["NOMNOM"] . " " . $Ananom_rec["NOMCOG"]);
                        }
                        break;
                    case $this->nameForm . "_Evento":
                        Out::valore($this->nameForm . "_Evento", "");
                        Out::valore($this->nameForm . "_Desc_evento", "");
                        $codice = $_POST[$this->nameForm . '_Evento'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anaeventi_rec = $this->praLib->GetAnaeventi($codice);
                        if ($anaeventi_rec) {
                            Out::valore($this->nameForm . "_Evento", $anaeventi_rec['EVTCOD']);
                            Out::valore($this->nameForm . "_Desc_evento", $anaeventi_rec['EVTDESCR']);
                        }
                        break;
                    case $this->nameForm . '_StatoPasso':
                        if ($_POST[$this->nameForm . '_StatoPasso']) {
                            $codice = $_POST[$this->nameForm . '_StatoPasso'];
                            $anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($anastp_rec) {
                                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_Stato1', "");
                            }
                        } else {
                            Out::valore($this->nameForm . '_Stato1', "");
                        }
                        break;
                }
                break;
            case 'returnAnapra':
                $this->DecodAnapra($_POST['rowData']['ID_ANAPRA'], $_POST['retid'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodAnanom($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case "returnAnatsp":
                $Anatsp_rec = $this->praLib->GetAnatsp($_POST["retKey"], 'rowid');
                if ($Anatsp_rec) {
                    Out::valore($this->nameForm . '_pratsp', $Anatsp_rec['TSPCOD']);
                    Out::valore($this->nameForm . '_sportello', $Anatsp_rec['TSPDES']);
                    Out::setFocus('', $this->nameForm . '_pratsp');
                }
                break;
            case 'returnAnatip':
                $Anatip_rec = $this->praLib->GetAnatip($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_pratip', $Anatip_rec['TIPCOD']);
                Out::valore($this->nameForm . '_tipologia', $Anatip_rec['TIPDES']);
                break;
            case 'returnAnaset':
                $Anaset_rec = $this->praLib->GetAnaset($_POST["retKey"], 'rowid');
                if ($Anaset_rec) {
                    Out::valore($this->nameForm . '_prastt', $Anaset_rec["SETCOD"]);
                    Out::valore($this->nameForm . '_settoreAttivita', $Anaset_rec["SETDES"]);
                }
                break;
            case 'returnAnaatt':
                $Anaatt_rec = $this->praLib->GetAnaatt($_POST["retKey"], 'rowid');
                if ($Anaatt_rec) {
                    Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                    Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                }
                break;
            case 'returnAnaeventi':
                $anaeventi_rec = $this->praLib->GetAnaeventi($_POST['retKey'], 'rowid');
                if ($anaeventi_rec) {
                    Out::valore($this->nameForm . "_Evento", $anaeventi_rec['EVTCOD']);
                    Out::valore($this->nameForm . "_Desc_evento", $anaeventi_rec['EVTDESCR']);
                }
                break;
            case 'returnAnastp':
                if ($_POST['retid'] == "STATOPASSO") {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_riepilogo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $tipoGrafico = $_POST[$this->nameForm . '_tipoGrafico'];
        $testo = "";
        $Stato_proc = $_POST[$this->nameForm . '_Stato_proc'];
        if ($Stato_proc) {
            $testo .= "Stato Procedimento: $Stato_proc";
        }
        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
        if ($Dal_num) {
            $testo .= "Da N. $Dal_num ";
        }
        $Al_num = $_POST[$this->nameForm . '_Al_num'];
        if ($Al_num) {
            $testo .= "a N. $Al_num ";
        }
        $Anno = $_POST[$this->nameForm . '_Anno'];
        if ($Anno) {
            $testo .= "nell'Anno $Anno";
        }
        $Da_data = $_POST[$this->nameForm . '_Da_data'];
        $A_data = $_POST[$this->nameForm . '_A_data'];
        if ($Da_data != "" && $A_data != "") {
            $testo .= "Da Data Registrazione " . substr($Da_data, 6, 2) . "/" . substr($Da_data, 4, 2) . "/" . substr($Da_data, 0, 4) .
                    " A Data Registrazione " . substr($A_data, 6, 2) . "/" . substr($A_data, 4, 2) . "/" . substr($A_data, 0, 4) . "\n";
        }

        $Da_dataRic = $_POST[$this->nameForm . '_Da_dataRic'];
        $a_dataRic = $_POST[$this->nameForm . '_A_dataRic'];
        if ($Da_dataRic != "" && $a_dataRic != "") {
            $testo .= "Da Data Ricezione " . substr($Da_dataRic, 6, 2) . "/" . substr($Da_dataRic, 4, 2) . "/" . substr($Da_dataRic, 0, 4) .
                    " A Data Ricezione " . substr($a_dataRic, 6, 2) . "/" . substr($a_dataRic, 4, 2) . "/" . substr($a_dataRic, 0, 4) . "\n";
        }

        $Intestatario = $_POST[$this->nameForm . '_Intestatario'];
        if ($Intestatario) {
            $testo .= "Intestatario: $Intestatario";
        }
        $Da_data_chi = $_POST[$this->nameForm . '_Da_datach'];
        $A_data_chi = $_POST[$this->nameForm . '_A_datach'];
        if ($Da_data_chi && $A_data_chi) {
            $testo .= "Da data chi. $Da_data_chi A data chi. $A_data_chi";
        }
        $Procedimento = $_POST[$this->nameForm . '_Procedimento'];
        if ($Procedimento) {
            $testo .= "Procedimento: $Procedimento";
        }
        $Stato_passo = $_POST[$this->nameForm . '_Stato_passo'];
        $Passo = $_POST[$this->nameForm . '_Passo'];
        $Responsabile = $_POST[$this->nameForm . '_Responsabile'];
        if ($Responsabile) {
            $testo .= "Responsabile: $Responsabile";
        }
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];
        if ($StatoPasso) {
            $testo .= "Stato Passo: $StatoPasso";
            $joinStatoPasso = " INNER JOIN PROPAS ON PROPAS.PRONUM=PROGES.GESNUM AND PROPAS.PROSTATO = '$StatoPasso'";
        }
        $CodiceProcedura = $_POST[$this->nameForm . '_CodiceProcedura'];
        $Assegnatario = $_POST[$this->nameForm . '_CodUtenteAss'];

        if ($Intestatario != '') {
            $joinIntestatario = "INNER JOIN ANADES ON PROGES.GESNUM = ANADES.DESNUM";
        }

        if ($tipoGrafico == 'Asseg') {
            $fieldAssegnatario = "P.PRORPA AS COD_ASSEGNATARIO," .
                    $this->PRAM_DB->strConcat("A.NOMCOG", "' '", "A.NOMNOM") . " AS ASSEGNATARIO,";
            $joinAssegnatario = "INNER JOIN PROPAS P ON GESNUM=P.PRONUM AND P.ROWID = (SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=GESNUM AND PROOPE<>'')
            INNER JOIN ANANOM A ON P.PRORPA=A.NOMRES";
            if ($Assegnatario != '') {
                $where_assegnatario = " AND P.PRORPA = '$Assegnatario'";
            }
        }

        if ($Procedimento != '')
            $Procedimento = str_pad($Procedimento, 6, 0, STR_PAD_RIGHT);
        if ($Passo != '')
            $Passo = str_pad($Passo, 6, 0, STR_PAD_RIGHT);
        if ($Dal_num == '')
            $Dal_num = "0";
        if ($Al_num == '')
            $Al_num = "999999";
        if ($Dal_num != '')
            $Dal_num = $Anno . str_pad($Dal_num, 6, 0, STR_PAD_RIGHT);
        if ($Al_num != '')
            $Al_num = $Anno . str_pad($Al_num, 6, 0, STR_PAD_RIGHT);

        $this->riepilogo = $this->getRiepilogo($Stato_proc, $Dal_num, $Al_num, $Anno, $Da_data, $A_data, $Intestatario, $Da_data_chi, $A_data_chi, $Procedimento, $Responsabile);



        $sql = "SELECT
            PROGES.ROWID AS ROWID,
            PROGES.GESNUM AS GESNUM,
            PROGES.GESDRE AS GESDRE,
            PROGES.GESDRI AS GESDRI,
            PROGES.GESDCH AS GESDCH,
            PROGES.GESSER AS GESSER,
            PROGES.GESOPE AS GESOPE,
            PROGES.GESSPA AS GESSPA,
            PROGES.GESTSP AS GESTSP,
            PROGES.GESPRO AS GESPRO,
            PROGES.GESSTT AS GESSTT,
            PROGES.GESATT AS GESATT,
            PROGESSUB.SPORTELLO AS GESTSP_SUB,
            PROGESSUB.PROPRO AS GESPRO_SUB,
            PROGESSUB.SETTORE AS GESSTT_SUB,
            PROGESSUB.ATTIVITA AS GESATT_SUB,
            PROGES.GESRES AS GESRES," .
                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE," .
                $this->PRAM_DB->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3", "ANAPRA.PRADES__4") . " AS PROCEDIMENTO,
            PROGES.GESCODPROC AS GESCODPROC,
            ANAPRA.PRADES__1 AS PRADES__1,
            ANAUNI.UNIDES AS UNIDES,
            ANASET.SETDES AS SETDES,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '') AS NUMPASSI,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '01') AS NUMPASSI_GEN,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '02') AS NUMPASSI_FEB,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '03') AS NUMPASSI_MAR,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '04') AS NUMPASSI_APR,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '05') AS NUMPASSI_MAG,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '06') AS NUMPASSI_GIU,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '07') AS NUMPASSI_LUG,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '08') AS NUMPASSI_AGO,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '09') AS NUMPASSI_SET,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '10') AS NUMPASSI_OTT,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '11') AS NUMPASSI_NOV,
            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROPUB = 0 AND PROOPE = '' AND PROINI <> '' AND SUBSTRING(PROINI,5,2) = '12') AS NUMPASSI_DIC,
            " . $this->PRAM_DB->coalesce('PROGES.GESSEG', "''") . " AS GESSEG,
            " . $this->PRAM_DB->coalesce('ANASPA.SPADES', "''") . " AS SPADES,
            " . $this->PRAM_DB->coalesce('ANATSP.TSPDES', "''") . " AS TSPDES,
             $fieldAssegnatario
            ANAATT.ATTDES AS ATTDES
        FROM PROGES PROGES
            LEFT OUTER JOIN ANAUNI ANAUNI ON PROGES.GESSER=ANAUNI.UNISER AND PROGES.GESSTT=ANAUNI.UNISET AND ANAUNI.UNIOPE" . $this->PRAM_DB->isBlank() . "
            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            LEFT OUTER JOIN ANATIP ANATIP ON PROGES.GESTIP=ANATIP.TIPCOD
            LEFT OUTER JOIN ANASPA ANASPA ON ANASPA.SPACOD=PROGES.GESSPA
            LEFT OUTER JOIN PROGESSUB PROGESSUB ON PROGESSUB.PRONUM=PROGES.GESNUM
            LEFT OUTER JOIN ANATSP ANATSP ON PROGES.GESTSP=ANATSP.TSPCOD
            LEFT OUTER JOIN ANASET ANASET ON PROGES.GESSTT=ANASET.SETCOD
            LEFT OUTER JOIN ANAATT ANAATT ON PROGES.GESSTT=ANASET.SETCOD AND PROGES.GESATT=ANAATT.ATTCOD
            $joinStatoPasso
            $joinAssegnatario
            $joinIntestatario
        WHERE (GESNUM BETWEEN '$Dal_num' AND '$Al_num') $where_assegnatario";
        /*
          PROGESSUB.SPORTELLO AS GESTSP,
          PROGESSUB.PROPRO AS GESPRO,
          PROGESSUB.SETTORE AS GESSTT,
          PROGESSUB.ATTIVITA AS GESATT,
          LEFT OUTER JOIN PROGESSUB PROGESSUB ON PROGESSUB.PRONUM=PROGES.GESNUM

          LEFT OUTER JOIN ANATSP ANATSP ON PROGES.GESTSP=ANATSP.TSPCOD
          LEFT OUTER JOIN ANASET ANASET ON PROGES.GESSTT=ANASET.SETCOD
          LEFT OUTER JOIN ANAATT ANAATT ON PROGES.GESSTT=ANAATT.ATTSET AND PROGES.GESATT=ANAATT.ATTCOD

          LEFT OUTER JOIN ANATSP ANATSP ON PROGESSUB.SPORTELLO=ANATSP.TSPCOD
          LEFT OUTER JOIN ANASET ANASET ON PROGESSUB.SETTORE=ANASET.SETCOD
          LEFT OUTER JOIN ANAATT ANAATT ON PROGESSUB.ATTIVITA=ANAATT.ATTCOD AND PROGESSUB.SETTORE=ANAATT.ATTSET


         */
        if ($Stato_proc == 'A') {
            $sql .= " AND GESDCH = ''";
        } else if ($Stato_proc == 'C') {
            $sql .= " AND GESDCH <> ''";
        }
        if ($Da_data != '' && $A_data != '') {
            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$A_data')";
        }
        if ($Da_dataRic != '' && $a_dataRic != '') {
            $sql .= " AND (GESDRI BETWEEN '$Da_dataRic' AND '$a_dataRic')";
        }
        if ($Da_data_chi != '' && $A_data_chi != '') {
            $sql .= " AND (GESDCH BETWEEN '$Da_data_chi' AND '$A_data_chi')";
        }
        if ($Stato_passo == 'A') {
            $sql .= " AND PROPAS.PROINI <> '' AND PROPAS.PROFIN = ''";
        } else if ($Stato_passo == 'C') {
            $sql .= "PROPAS.PROFIN <> ''";
        }
        if ($Anno != '') {
            $sql .= " AND (PROGES.GESNUM LIKE '$Anno%')";
        }
        if ($Responsabile != '') {
            $sql .= " AND (PROGES.GESRES = '$Responsabile')";
        }
        if ($Procedimento) {
            $sql .= " AND (PROGES.GESPRO = '$Procedimento')";
        }

        if ($Intestatario != '') {
            $sql .= " AND (ANADES.DESNOM LIKE '%$Intestatario%')";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            $sql .= " AND GESTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            $sql .= " AND GESSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            $sql .= " AND GESATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            $sql .= " AND GESTSP=" . $_POST[$this->nameForm . '_pratsp'];
        }
        if ($_POST[$this->nameForm . '_praseg'] != "") {
            //$sql .= " AND PRASEG='" . $_POST[$this->nameForm . '_praseg'] . "'";
            $sql .= " AND GESSEG = '" . $_POST[$this->nameForm . '_praseg'] . "'";
        }
        if ($_POST[$this->nameForm . '_Evento'] != "") {
            $sql .= " AND GESEVE = '" . $_POST[$this->nameForm . '_Evento'] . "'";
        }
        if ($CodiceProcedura) {
            $sql .= " AND GESCODPROC LIKE '%" . $CodiceProcedura . "%'";
        }

        $sql .= " GROUP BY PROGES.ROWID";
        //Out::msgInfo("", $sql);
        return $sql;
    }

    function CreaSqlXls() {
        // Imposto il filtro file xls
        $anno = $_POST[$this->nameForm . '_Anno'];
        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
        $al_num = $_POST[$this->nameForm . '_Al_num'];
        $Da_data = $_POST[$this->nameForm . '_Da_data'];
        $a_data = $_POST[$this->nameForm . '_A_data'];
        $procedimento = $_POST[$this->nameForm . '_Procedimento'];
        $sportello = $_POST[$this->nameForm . '_pratsp'];
        $tipologia = $_POST[$this->nameForm . '_pratip'];
        $settore = $_POST[$this->nameForm . '_prastt'];
        $attivita = $_POST[$this->nameForm . '_praatt'];

        if ($Dal_num == '')
            $Dal_num = "0";
        if ($al_num == '')
            $al_num = "999999";
        if ($Dal_num != '')
            $Dal_num = $anno . str_pad($Dal_num, 6, 0, STR_PAD_RIGHT);
        if ($al_num != '')
            $al_num = $anno . str_pad($al_num, 6, 0, STR_PAD_RIGHT);
        if ($procedimento != '')
            $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);

        $sql = "SELECT * FROM PROGES WHERE (GESNUM BETWEEN '$Dal_num' AND '$al_num')";

//        if ($Da_data && $a_data) {
//            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
//        }
//        if ($procedimento) {
//            $sql.=" AND GESPRO = '" . $procedimento . "'";
//        }
//        if ($sportello) {
//            $sql.=" AND GESTSP = " . $sportello;
//        }
//        if ($tipologia) {
//            $sql.=" AND GESTIP = " . $tipologia;
//        }
//        if ($settore) {
//            $sql.=" AND GESSTT = " . $settore;
//        }
//        if ($attivita) {
//            $sql.=" AND GESATT = " . $attivita;
//        }

        if ($Stato_proc == 'A') {
            $sql .= " AND GESDCH <> ''";
        } else if ($Stato_proc == 'C') {
            $sql .= " AND GESDCH = ''";
        }
        if ($Da_data != '' && $A_data != '') {
            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$A_data')";
        }
        if ($Da_data_chi != '' && $A_data_chi != '') {
            $sql .= " AND (GESDCH BETWEEN '$Da_data_chi' AND '$A_data_chi')";
        }
        if ($Stato_passo == 'A') {
            $sql .= " AND PROPAS.PROINI <> '' AND PROPAS.PROFIN = ''";
        } else if ($Stato_passo == 'C') {
            $sql .= "PROPAS.PROFIN <> ''";
        }
        if ($Anno != '') {
            $sql .= " AND (PROGES.GESNUM LIKE '$Anno%')";
        }
        if ($Responsabile != '') {
            $sql .= " AND (PROGES.GESRES = '$Responsabile')";
        }

        if ($Intestatario != '') {
            $sql .= " AND (ANADES.DESNOM LIKE '%$Intestatario%')";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            $sql .= " AND GESTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            $sql .= " AND GESSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            $sql .= " AND GESATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praseg'] != "") {
            $sql .= " AND GESSEG='" . $_POST[$this->nameForm . '_praseg'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            $sql .= " AND GESTSP=" . $_POST[$this->nameForm . '_pratsp'];
        }



        $sql .= " ORDER BY GESNUM DESC";
        return $sql;
    }

//    function CreaSqlXls() {
//        // Imposto il filtro file xls
//        $anno = $_POST[$this->nameForm . '_Anno'];
//        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
//        $al_num = $_POST[$this->nameForm . '_Al_num'];
//        $Da_data = $_POST[$this->nameForm . '_Da_data'];
//        $a_data = $_POST[$this->nameForm . '_A_data'];
//        $procedimento = $_POST[$this->nameForm . '_Procedimento'];
//        $sportello = $_POST[$this->nameForm . '_pratsp'];
//        $tipologia = $_POST[$this->nameForm . '_pratip'];
//        $settore = $_POST[$this->nameForm . '_prastt'];
//        $attivita = $_POST[$this->nameForm . '_praatt'];
//
//        if ($Dal_num == '')
//            $Dal_num = "0";
//        if ($al_num == '')
//            $al_num = "999999";
//        if ($Dal_num != '')
//            $Dal_num = $anno . str_pad($Dal_num, 6, 0, STR_PAD_RIGHT);
//        if ($al_num != '')
//            $al_num = $anno . str_pad($al_num, 6, 0, STR_PAD_RIGHT);
//        if ($procedimento != '')
//            $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);
//
//        $sql = "SELECT
//            PROGES.GESNUM AS PRATICA,
//            PROGES.GESPRA AS RICHIESTA_ONLINE,
//            PROGES.GESDRE AS DATA," .
//                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
//            ANADES.DESNOM AS INTESTATARIO,
//            ANAPRA.PRADES__1 AS DESCRIZIONE,
//            PROGES.GESNOT AS NOTE,
//            ANASPA.SPADES AS AGGREGATO,
//            ANATSP.TSPDES AS SPORTELLO,
//            PROGES.GESDCH AS DATA_CHIUSURA
//        FROM PROGES PROGES
//            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
//            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
//            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM
//            LEFT OUTER JOIN ANASPA ANASPA ON PROGES.GESSPA=ANASPA.SPACOD
//            LEFT OUTER JOIN ANATSP ANATSP ON PROGES.GESTSP=ANATSP.TSPCOD
//        WHERE (GESNUM BETWEEN '$Dal_num' AND '$al_num')";
//
//        if ($Da_data && $a_data) {
//            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
//        }
//        if ($procedimento) {
//            $sql.=" AND GESPRO = '" . $procedimento . "'";
//        }
//        if ($sportello) {
//            $sql.=" AND GESTSP = " . $sportello;
//        }
//        if ($tipologia) {
//            $sql.=" AND GESTIP = " . $tipologia;
//        }
//        if ($settore) {
//            $sql.=" AND GESSTT = " . $settore;
//        }
//        if ($attivita) {
//            $sql.=" AND GESATT = " . $attivita;
//        }
//
//        $sql.=" GROUP BY GESNUM"; // Per non far vedere pratiche doppie a colpa della join con ANADES
//        return $sql;
//    }

    function OpenRicerca() {
        Out::hide($this->divRis);
        Out::show($this->divRic);
        //Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridGraf);
        TableView::clearGrid($this->gridGraf);
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . "_SvuotaRicerca");
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Uniset');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . "_boxGrafico");
        Out::hide($this->nameForm . "_SvuotaRicerca");
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_Stato_proc', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_proc', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_proc', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_Stato_passo', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_passo', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_passo', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_tipoGrafico', 1, "Agg", "1", "Aggregato");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Ser", "", "Servizio");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Set", "0", "Settore");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Att", "0", "Attività");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Tseg", "0", "Tipologia Segnalazione");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Dreg", "0", "Data Registrazione");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Dchi", "0", "Data Chiusura");
        Out::select($this->nameForm . '_tipoGrafico', 1, "Asseg", "0", "Assegnatario");
        Out::select($this->nameForm . '_tipoGrafico', 1, "SetPassi", "0", "N. Passi per Settore e Attività");
        Out::select($this->nameForm . '_tipoGrafico', 1, "PassiMese", "0", "N. Passi per Mese");

        foreach (praLib::$TIPO_SEGNALAZIONE as $k => $v) {
            Out::select($this->nameForm . '_praseg', '1', $k, '0', $v);
        }
    }

    function getRiepilogo($Stato_proc, $Dal_num, $Al_num, $Anno, $Da_data, $A_data, $Intestatario, $Da_data_chi, $A_data_chi, $Procedimento, $Responsabile) {
        $testo = "";
        if ($Stato_proc) {
            $testo .= "Stato Procedimento: $Stato_proc\n";
        }
        if ($Dal_num) {
            $Dal_num = substr($Dal_num, 4, 6) . "/" . substr($Dal_num, 0, 4);
        }
        if ($Al_num) {
            $Al_num = substr($Al_num, 4, 6) . "/" . substr($Al_num, 0, 4);
            //$testo .= "a N. $Al_num ";
        }
        if ($Anno) {
            $testo .= "Da N. $Dal_num a N. $Al_num\n";
        }
        if ($Da_data != "" && $A_data != "") {
            $testo .= "Da Data Registrazione " . substr($Da_data, 6, 2) . "/" . substr($Da_data, 4, 2) . "/" . substr($Da_data, 0, 4) .
                    " A Data Registrazione " . substr($A_data, 6, 2) . "/" . substr($A_data, 4, 2) . "/" . substr($A_data, 0, 4) . "\n";
        }
        if ($Intestatario) {
            $testo .= "Intestatario: $Intestatario\n";
        }
        if ($Da_data_chi && $A_data_chi) {
            $testo .= "Da Data Chiusura $Da_data_chi A Data Chiusura $A_data_chi\n";
        }
        if ($Procedimento) {
            $testo .= "Procedimento: $Procedimento\n";
        }
        if ($Responsabile) {
            $Ananom_rec = $this->DecodAnanom($Responsabile);
            $testo .= "Responsabile: " . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'] . "\n";
        }
        return $testo;
    }

    function DecodAnapra($Codice, $retid, $tipoRic = 'codice') {
        $Anapra_rec = $this->praLib->GetAnapra($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Procedimento":
                Out::valore($this->nameForm . '_Procedimento', $Anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc', $Anapra_rec['PRADES__1']);
                break;
            case "" :
                break;
        }
        return $Anapra_rec;
    }

    function DecodAnanom($Codice, $retid, $tipoRic = 'codice') {
        $Ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Responsabile":
                Out::valore($this->nameForm . '_Responsabile', $Ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_Desc_resp', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
                break;
            case $this->nameForm . "_ricercaAss":
                Out::valore($this->nameForm . '_CodUtenteAss', $Ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_UtenteAss', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
                break;
            case "" :
                break;
        }
        return $Ananom_rec;
    }

    function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]["GESNUM"] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            if ($Anades_rec) {
                $Result_tab[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $Result_tab[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
            }
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab[$key]['DATIIMPRESA'] = $datiInsProd['IMPRESA'] . "<br>" . $datiInsProd['FISCALE'] . "<br>" . $datiInsProd['INDIRIZZO'];
            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab[$key]['PRADES__1'] = $anaset_rec['SETDES'] . "<br>" . $anaatt_rec['ATTDES'] . "<br>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
            }
            $Result_tab[$key]['STATO'] = $this->GetImgStatoPratica($Result_rec);
        }
        return $Result_tab;
    }

    function elaboraRecordsXls($Result_tab) {
        $result_tab_def = array();
        foreach ($Result_tab as $key => $Result_rec) {
            $result_tab_def[$key]["PRATICA"] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $result_tab_def[$key]["DATA_REGISTRAZIONE"] = substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4);
            $result_tab_def[$key]["DATA_CHIUSURA"] = "";
            if ($Result_rec['GESDCH']) {
                $result_tab_def[$key]["DATA_CHIUSURA"] = substr($Result_rec['GESDCH'], 6, 2) . "/" . substr($Result_rec['GESDCH'], 4, 2) . "/" . substr($Result_rec['GESDCH'], 0, 4);
            }
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            if ($Anades_rec) {
                $result_tab_def[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $result_tab_def[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
            }
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $result_tab_def[$key]['DATIIMPRESA'] = $datiInsProd['IMPRESA'] . " - " . $datiInsProd['FISCALE'] . " - " . $datiInsProd['INDIRIZZO'];
            //
//            $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
//            $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
//            $anapra_rec = $this->praLib->GetAnapra($Result_rec['GESPRO']);
//            $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
//            $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
            //$result_tab_def[$key]['PROCEDIMENTO'] = $anaset_rec['SETDES'] . "<br>" . $anaatt_rec['ATTDES'] . "<br>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
            $result_tab_def[$key]['PROCEDIMENTO'] = $Result_rec['PROCEDIMENTO'];
            $result_tab_def[$key]['TIPO_SEGNALAZIONE'] = $Result_rec['PRASEG'];
            $result_tab_def[$key]['SPORTELLO'] = $Result_rec['TSPDES'];
            $result_tab_def[$key]['AGGREGATO'] = $Result_rec['SPADES'];
            $result_tab_def[$key]['SETTORE'] = $Result_rec['SETDES'];
            $result_tab_def[$key]['ATTIVITA'] = $Result_rec['ATTDES'];
//            $result_tab_def[$key]['PROCEDIMENTO'] = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
//            $result_tab_def[$key]['TIPO_SEGNALAZIONE'] = $anapra_rec['PRASEG'];
//            $result_tab_def[$key]['SPORTELLO'] = $anatsp_rec['TSPDES'];
//            $result_tab_def[$key]['AGGREGATO'] = $anaspa_rec['SPADES'];
//            $result_tab_def[$key]['SETTORE'] = $anaset_rec['SETDES'];
//            $result_tab_def[$key]['ATTIVITA'] = $anaatt_rec['ATTDES'];
        }
        return $result_tab_def;
    }

    function DisegnaGrafico($titolo, $report, $riepilogo) {
        $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $parameters = array(
            "Sql" => $this->CreaSql(),
            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
            "Titolo" => $titolo,
            "Riepilogo" => $riepilogo
        );
        $itaJR->runSQLReportPDF($this->PRAM_DB, $report, $parameters);
    }

    function GetImgStatoPratica($Result_rec) {
        if ($Result_rec['GESDCH']) {
            $prasta_rec = $this->praLib->GetPrasta($Result_rec['GESNUM']);
            if ($prasta_rec['STAFLAG'] == "Annullata") {
                $img = '<span class="ita-icon ita-icon-delete-24x24">Pratica Annullata</span>';
            } elseif ($prasta_rec['STAFLAG'] == "Chiusa Positivamente") {
                $img = '<span class="ita-icon ita-icon-check-green-24x24">Pratica chiusa positivamente</span>';
            } elseif ($prasta_rec['STAFLAG'] == "Chiusa Negativamente") {
                $img = '<span class="ita-icon ita-icon-check-red-24x24">Pratica chiusa negativamente</span>';
            }
        } else {
            $Propas_tab = $this->praLib->GetPropas($Result_rec['GESNUM'], "codice", true);
            if ($Propas_tab) {
                $passi_BO_aperti = $passi_BO_chiusi = $passi_BO_daAprire = $passi_FO = $passi_BO = array();
                foreach ($Propas_tab as $Propas_rec) {
                    if ($Propas_rec['PROPUB'] == 1) {
                        $passi_FO[] = $Propas_rec;
                    } else {
                        $passi_BO[] = $Propas_rec;
                        if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'] == "") {
                            $passi_BO_aperti[] = $Propas_rec;
                        }
                        if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN']) {
                            $passi_BO_chiusi[] = $Propas_rec;
                        }
                        if ($Propas_rec['PROINI'] == "" && $Propas_rec['PROFIN'] == "") {
                            $passi_BO_daAprire[] = $Propas_rec;
                        }
                    }
                }

                if ($passi_BO == $passi_BO_daAprire) {
                    $img = '<span class="ita-icon ita-icon-chiusagreen-24x24">Pratica caricata</span>';
                }
                if ($passi_BO_aperti) {
                    $img = '<span class="ita-icon ita-icon-apertagray-24x24">Pratica con passi aperti</span>';
                }
                if ($passi_BO_chiusi) {
                    $img = '<span class="ita-icon ita-icon-apertagreen-24x24">Pratica in corso</span>';
                }
            } else {
                $img = '<span class="ita-icon ita-icon-bullet-red-24x24">Passi non Presenti</span>';
            }
        }

        return $img;
    }

}
