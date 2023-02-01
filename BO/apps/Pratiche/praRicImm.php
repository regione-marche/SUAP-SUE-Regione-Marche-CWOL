<?php

/**
 *
 * Ricerca Articoli nei Passi delle Pratiche
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praRicImm() {
    $praRicImm = new praRicImm();
    $praRicImm->parseEvent();
    return;
}

class praRicImm extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    public $utiEnte;
    public $sql;
    public $nameForm = "praRicImm";
    public $divRis = "praRicImm_divRisultato";
    public $divRic = "praRicImm_divRicerca";
    public $gridGest = "praRicImm_gridGest";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->sql = App::$utente->getKey($this->nameForm . '_sql');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_sql', $this->sql);
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
                switch ($_POST['id']) {
                    case $this->gridGest:
                        $model = 'praGest';
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->Dettaglio($_POST['rowid'], true);
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $this->elaboraRecordXLS($Result_tab1);
                $ita_grid01 = new TableView($this->gridGest, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid01->setSortOrder('desc');
                $ita_grid01->setSortIndex('SERIEPRATICA');
                $ita_grid01->exportXLS('', 'pratiche.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'PRATICA') {
                    $ordinamento = 'PRONUM';
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
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praImmobili', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $this->Elenca();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_StatoPasso_butt':
                        praRic::praRicAnastp($this->nameForm, '', "STATOPASSO");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Foglio':
                        $codice = $_POST[$this->nameForm . '_Foglio'];
                        if ($codice) {
                            $codice = str_pad($codice, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Foglio', $codice);
                        }
                        break;
                    case $this->nameForm . '_Numero':
                        $codice = $_POST[$this->nameForm . '_Numero'];
                        if ($codice) {
                            $codice = str_pad($codice, 5, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Numero', $codice);
                        }
                        break;
                    case $this->nameForm . '_Sub':
                        $codice = $_POST[$this->nameForm . '_Sub'];
                        if ($codice) {
                            $codice = str_pad($codice, 5, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Sub', $codice);
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
            case 'returnAnastp':
                $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_sql');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql($xls = false) {
        // Imposto il filtro di ricerca
        $daData = $_POST[$this->nameForm . '_DaData'];
        $aData = $_POST[$this->nameForm . '_AData'];
        $codice = $_POST[$this->nameForm . '_Codice'];
        $foglio = $_POST[$this->nameForm . '_Foglio'];
        $numero = $_POST[$this->nameForm . '_Numero'];
        $sub = $_POST[$this->nameForm . '_Sub'];
        $tipo = $_POST[$this->nameForm . '_Tipo'];
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];
        $Pratica = $_POST[$this->nameForm . '_Pratica'];

        if ($StatoPasso != '') {
            $joinStatoPasso = " INNER JOIN PROPAS PROPAS2 ON PROPAS2.PRONUM=PROGES.GESNUM AND PROPAS2.PROSTATO = '$StatoPasso'";
        }
        if ($xls == true) {
            $sql = "SELECT
            PROGES.GESNUM AS N_PRATICA,
            PROGES.SERIECODICE AS SERIECODICE,
            PRAIMM.CODICE AS CODICE,
            PRAIMM.FOGLIO AS FOGLIO,
            PRAIMM.PARTICELLA AS PARTICELLA,
            PRAIMM.SUBALTERNO AS SUBALTERNO,
            PROGES.GESDRE AS DATA_REGISTRAZIONE,
            PROGES.GESDRI AS DATA_INSERIMENTO,
            PROGES.GESORA AS ORA_INSERIMENTO,
            PROGES.GESDCH AS DATA_CHIUSURA,
            PROGES.GESPRA AS N_RICHIESTA,
            PROGES.GESNOT AS NOTE,
            PROGES.GESPRE AS GESPRE,
            PROGES.GESNPR AS N_PROTOCOLLO,
            PROGES.GESSTT AS GESSTT,
            PROGES.GESATT AS GESATT,
            PROGES.GESRES AS GESRES," .
                    $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
            PROGES.GESPRO AS PROCEDIMENTO,
            PROGES.GESSPA AS GESSPA,
            PROGES.GESTSP AS GESTSP,
            ANAPRA.PRADES__1 AS DESC_PROCEDIMENTO,
            ANADES.DESNOM AS INTESTATARIO,
            PRODAG.DAGVAL AS OGGETTO_DOMANDA," .
                    $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'", 'PROGES.SERIEANNO') . " AS SERIEPRATICA
         FROM PROGES PROGES
            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            LEFT OUTER JOIN PRAIMM PRAIMM ON PROGES.GESNUM=PRAIMM.PRONUM
            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM AND (ANADES.DESRUO='0001' OR ANADES.DESRUO='')
            LEFT JOIN PRODAG PRODAG ON PROGES.GESNUM=PRODAG.DAGNUM AND PRODAG.DAGKEY='OGGETTO_DOMANDA' AND PRODAG.DAGVAL <>'' AND PRODAG.DAGVAL IS NOT NULL
            LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
            $joinStatoPasso
        WHERE GESNUM IN(SELECT PRONUM FROM PRAIMM) ";
        } else {
            $sql = "SELECT
            PROGES.GESNUM AS GESNUM,
            PROGES.ROWID AS ROWID,
            PROGES.GESDRE AS GESDRE,
            PROGES.GESDRI AS GESDRI,
            PROGES.SERIECODICE AS SERIECODICE,
            PROGES.GESORA AS GESORA,
            PROGES.GESDCH AS GESDCH,
            PROGES.GESPRA AS GESPRA,
            PROGES.GESTSP AS GESTSP,
            PROGES.GESSPA AS GESSPA,            
            PROGES.GESNOT AS GESNOT,
            PROGES.GESPRE AS GESPRE,
            PROGES.GESNPR AS GESNPR,
            PROGES.GESSTT AS GESSTT,
            PROGES.GESATT AS GESATT,
            PROGES.GESRES AS GESRES," .
                    $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
            PROGES.GESPRO AS GESPRO,
            ANAPRA.PRADES__1 AS PRADES__1,
            ANADES.DESNOM AS DESNOM,
            PRAIMM.CODICE AS CODICE,
            PRAIMM.FOGLIO AS FOGLIO,
            PRAIMM.PARTICELLA AS PARTICELLA,
            PRAIMM.SUBALTERNO AS SUBALTERNO,
            PRODAG.DAGVAL AS OGGETTO_DOMANDA," .
                    $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'", 'PROGES.SERIEANNO') . " AS SERIEPRATICA
        FROM PROGES PROGES
            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            LEFT OUTER JOIN PRAIMM PRAIMM ON PROGES.GESNUM=PRAIMM.PRONUM
            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM AND (ANADES.DESRUO='0001' OR ANADES.DESRUO='')
            LEFT JOIN PRODAG PRODAG ON PROGES.GESNUM=PRODAG.DAGNUM AND PRODAG.DAGKEY='OGGETTO_DOMANDA' AND PRODAG.DAGVAL <>'' AND PRODAG.DAGVAL IS NOT NULL
            LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
            $joinStatoPasso
        WHERE GESNUM IN(SELECT PRONUM FROM PRAIMM) ";
        }
        if ($Pratica) {
            $sql .= " AND SERIEPRATICA LIKE '%$Pratica%'";
        }
        if ($tipo) {
            $sql .= " AND PRAIMM.TIPO = '$tipo'";
        }
        if ($codice) {
            $sql .= " AND PRAIMM.CODICE = '$codice'";
        }
        if ($foglio) {
            if (strlen($foglio) == 4) {
                $sql .= " AND PRAIMM.FOGLIO = '$foglio'";
            } else {
                $sql .= " AND PRAIMM.FOGLIO LIKE '%$foglio%'";
            }
        }
        if ($numero) {
            if (strlen($foglio) == 5) {
                $sql .= " AND PRAIMM.PARTICELLA = '$numero'";
            } else {
                $sql .= " AND PRAIMM.PARTICELLA LIKE '%$numero%'";
            }
        }
        if ($sub) {
            if (strlen($sub) == 4) {
                $sql .= " AND PRAIMM.SUBALTERNO = '$sub'";
            } else {
                $sql .= " AND PRAIMM.SUBALTERNO LIKE '%$sub%'";
            }
        }
        if ($daData && $aData) {
            $sql .= " AND (GESDRI BETWEEN '$daData' AND '$aData')";
        }


        if ($_POST['_search'] == true) {
            if ($_POST['PRATICA']) {
                $sql .= " AND ".$this->PRAM_DB->strConcat( "PROGES.SERIEPROGRESSIVO", "'/'", 'PROGES.SERIEANNO')." LIKE '%".addslashes($_POST['PRATICA'])."%'";
            }
            if ($_POST['GESNUM']) {
                $sql .= " AND GESNUM LIKE '%" . addslashes($_POST['GESNUM']) . "%'";
            }
            if ($_POST['GESPRA']) {
                $sql .= " AND GESPRA LIKE '%" . addslashes($_POST['GESPRA']) . "%'";
            }
            if ($_POST['DESNOM']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DESNOM') . " LIKE '%" . strtoupper(addslashes($_POST['DESNOM'])) . "%'";
            }
            if ($_POST['PRADES__1']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('PRADES__1') . " LIKE '%" . strtoupper(addslashes($_POST['PRADES__1'])) . "%'";
            }
            if ($_POST['CODICE']) {
                $sql .= " AND CODICE LIKE '%" . strtoupper(addslashes($_POST['CODICE'])) . "%'";
            }
            if ($_POST['FOGLIO']) {
                $sql .= " AND FOGLIO LIKE '%" . strtoupper(addslashes($_POST['FOGLIO'])) . "%'";
            }
            if ($_POST['PARTICELLA']) {
                $sql .= " AND PARTICELLA LIKE '%" . strtoupper(addslashes($_POST['PARTICELLA'])) . "%'";
            }
            if ($_POST['SUBALTERNO']) {
                $sql .= " AND SUBALTERNO LIKE '%" . strtoupper(addslashes($_POST['SUBALTERNO'])) . "%'";
            }
        }
        return $sql;
    }

    function Elenca() {
        if ($_POST['sql']) {
            $sql = $_POST['sql'];
        } else {
            $this->sql = $sql = $this->CreaSql();
        }
        try {   // Effettuo la FIND
            $ita_grid01 = new TableView($this->gridGest, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            $ita_grid01->setSortIndex('GESNUM');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                Out::msgStop("Selezione", "Nessun record trovato.");
                $this->OpenRicerca();
            } else {   // Visualizzo la ricerca
                Out::hide($this->divRic);
                Out::show($this->divRis);
                $this->Nascondi();
                Out::show($this->nameForm . '_AltraRicerca');
                Out::show($this->nameForm . '_Nuovo');
                Out::setFocus('', $this->nameForm . '_Nuovo');
                TableView::enableEvents($this->gridGest);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_Tipo', 1, "", "1", "Tutti");
        Out::select($this->nameForm . '_Tipo', 1, "T", "0", "Terreno");
        Out::select($this->nameForm . '_Tipo', 1, "F", "0", "Fabbricato");
    }

    function OpenRicerca() {
        Out::hide($this->divRis);
        Out::show($this->divRic);
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridGest);
        TableView::clearGrid($this->gridGest);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_DaData');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            // $Result_tab[$key]["GESNUM"] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Result_tab[$key]["PRATICA"] = $Result_rec['SERIEPRATICA'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND (DESRUO='0001' OR DESRUO='')", false);
            $Result_tab[$key]["DESNOM"] = "";
            if ($Anades_rec) {
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
            $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
            $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
            $Result_tab[$key]['PRADES__1'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div><div>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'] . "</div></div>";
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $aggregato = $anaspa_rec['SPADES'];
            }
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab[$key]["SPORTELLO"] = "<div class=\"ita-Wordwrap\">" . $anatsp_rec['TSPDES'] . "</div><div>$aggregato</div>";
            }
        }
        return $Result_tab;
    }

    public function elaboraRecordXLS($Result_tab) {
        $Result_tab_def = array();
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab_def[$key]["PRATICA"] = $Result_rec['SERIEPRATICA'];
            //$Result_tab_def[$key]["PROCEDIMENTO"] = $Result_rec['PRADES__1'];
            $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
            $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
            $Result_tab_def[$key]['PROCEDIMENTO'] = $anaset_rec['SETDES'] . "\r\n" . $anaatt_rec['ATTDES'] . "\r\n" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];

            $Result_tab_def[$key]["RESPONSABILE"] = $Result_rec['RESPONSABILE'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND (DESRUO='0001' OR DESRUO='')", false);
            $Result_tab_def[$key]["INTESTATARIO"] = "";
            if ($Anades_rec) {
                $Result_tab_def[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
            }
            if ($Result_rec['GESPRA'] != 0) {
                $Result_tab_def[$key]["N_RICHIESTA"] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            } else {
                $Result_tab_def[$key]["N_RICHIESTA"] = "";
            }
            if ($Result_rec['GESDRE'] != "") {
                $Result_tab_def[$key]["REGISTRAZIONE"] = substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4);
            } else {
                $Result_tab_def[$key]["REGISTRAZIONE"] = "";
            }

            if ($Result_rec['GESDRI'] != "" && $Result_rec['GESORA'] != "") {
                $Result_tab_def[$key]["RICEZIONE"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4) . " (" . $Result_rec['GESORA'] . ")";
            } else {
                $Result_tab_def[$key]["RICEZIONE"] = "";
            }
            $Result_tab_def[$key]["PROTOCOLLO"] = $Result_rec['GESNPR'];
            //
            $Result_tab_def[$key]["CODICE_IMMOBILE"] = $Result_rec['CODICE'];
            $Result_tab_def[$key]["FOGLIO"] = $Result_rec['FOGLIO'];
            $Result_tab_def[$key]["PARTICELLA"] = $Result_rec['PARTICELLA'];
            $Result_tab_def[$key]["SUB"] = $Result_rec['SUBALTERNO'];
            //
            $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $Result_rec['GESNUM'] . "'
                                  AND (DAGTIP = 'DenominazioneImpresa' OR DAGTIP = 'Codfis_InsProduttivo' OR DAGKEY = 'IC_DEMOM_IMPRESA' OR DAGKEY = 'IC_CODFIS_IMPRESA')", true);
            $Result_tab_def[$key]["IMPRESA"] = "";
            $Result_tab_def[$key]["FISCALE"] = "";
            if ($Prodag_tab) {
                foreach ($Prodag_tab as $Prodag_rec) {
                    if ($Prodag_rec['DAGKEY'] == "IC_DEMOM_IMPRESA" || $Prodag_rec['DAGTIP'] == "DenominazioneImpresa")
                        $Result_tab_def[$key]["IMPRESA"] = $Prodag_rec['DAGVAL'];
                    if ($Prodag_rec['DAGKEY'] == "IC_CODFIS_IMPRESA" || $Prodag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                        $Result_tab_def[$key]["FISCALE"] = $Prodag_rec['DAGVAL'];
                }
            }
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab_def[$key]["SPORTELLO"] = $anatsp_rec['TSPDES'];
            }
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $Result_tab_def[$key]["AGGREGATO"] = $anaspa_rec['SPADES'];
            }

            $Result_tab_def[$key]["OGGETTO_DOMANDA"] = $Result_rec['OGGETTO_DOMANDA'];
        }
        return $Result_tab_def;
    }

}

?>