<?php

/**
 *
 * Ricerca Passi delle Pratiche
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    11.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praPassoRic() {
    $praPassoRic = new praPassoRic();
    $praPassoRic->parseEvent();
    return;
}

class praPassoRic extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    public $nameForm = "praPassoRic";
    public $divRis = "praPassoRic_divRisultato";
    public $divRic = "praPassoRic_divRicerca";
    public $gridPassi = "praPassoRic_gridPassi";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
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
                $this->OpenRicerca();
                $this->CreaCombo();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['modo'] = "edit";
                        $_POST['rowid'] = $rowid;
                        itaLib::openForm($model);
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'PRPARTENZA' || $ordinamento == 'PRARRIVO' || $ordinamento == 'STATOPASSO' || $ordinamento == "STATOCOM") {
                    break;
                }
                if ($ordinamento == 'PRATICA') {
                    $ordinamento = 'GESNUM';
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                if ($ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                    TableView::enableEvents($this->gridPassi);
                }
                break;
            case 'exportTableToExcel':
                if($_POST['sord'] == "desc"){
                    $sord = SORT_DESC;
                }
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab1 = $this->praLib->array_sort($Result_tab1, $_POST['sidx'], $sord);
                $Result_tab2 = $this->elaboraRecordsXls($Result_tab1);
                $ita_grid02 = new TableView($this->gridPassi, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->exportXLS('', 'passi.xls');
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
//                        if ($_POST[$this->nameForm . "_CodiceSogg"] == "" && $_POST[$this->nameForm . "_Denominazione"] == "") {
//                            Out::msgInfo("Attenzione!!", "Complilare almeno un campo di ricerca");
//                            break;
//                        }
                        TableView::clearGrid($this->gridPassi);
                        TableView::enableEvents($this->gridPassi);
                        TableView::reload($this->gridPassi);
                        Out::hide($this->divRic, '');
                        Out::show($this->divRis, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        $this->CreaCombo();
                        break;
                    case $this->nameForm . "_CodiceSogg_butt":
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            proRic::proRicAnamed($this->nameForm);
                        } else {
                            praRic::praRicAnades($this->nameForm);
                        }
                        break;
                    case $this->nameForm . "_CodiceResp_butt":
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm);
                        break;
                    case $this->nameForm . "_CodTipoPasso_butt":
                        praRic::praRicPraclt("praPassoRic", "RICERCA Tipo Passo", "returnPraclt");
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
                    case $this->nameForm . '_CodiceSogg':
                        if ($_POST[$this->nameForm . '_CodiceSogg']) {
                            $codice = str_pad($_POST[$this->nameForm . '_CodiceSogg'], 6, "0", STR_PAD_LEFT);
                            $Filent_Rec = $this->praLib->GetFilent(18);
                            if ($Filent_Rec['FILVAL'] == 1) {
                                $this->DecodAnamedComP($codice);
                            } else {
                                $this->decodAnades($codice, "", "codiceSogg");
                            }
                        }
                        break;
                    case $this->nameForm . '_Denominazione':
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $anamed_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), "SELECT * FROM ANAMED WHERE MEDNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_Denominazione']) . "%' AND MEDANN=0", false);
                            $this->DecodAnamedComP($anamed_rec['MEDCOD']);
                        } else {
                            $anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_ANADES']["DESNOM"]) . "%'", false);
                            $this->decodAnades($anades_rec['ROWID'], "", "rowid");
                        }
                        break;
                    case $this->nameForm . '_CodiceResp':
                        if ($_POST[$this->nameForm . '_CodiceResp']) {
                            $codice = str_pad($_POST[$this->nameForm . '_CodiceResp'], 6, "0", STR_PAD_LEFT);
                            $this->DecodAnanom($codice);
                        }
                        break;
                    case $this->nameForm . '_CodTipoPasso':
                        $codice = $_POST[$this->nameForm . '_CodTipoPasso'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->DecodTipoPasso($codice);
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
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stato':
                        if ($_POST[$this->nameForm . "_Stato"] == "" || $_POST[$this->nameForm . "_Stato"] == "D") {
                            Out::valore($this->nameForm . "_DallaData", "");
                            Out::valore($this->nameForm . "_AllaData", "");
                            Out::hide($this->nameForm . "_DallaData_field");
                            Out::hide($this->nameForm . "_AllaData_field");
                        } else {
                            Out::show($this->nameForm . "_DallaData_field");
                            Out::show($this->nameForm . "_AllaData_field");
                        }
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Denominazione':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE " . $this->praLib->getPRAMDB()->strUpper('MEDNOM') . " LIKE '%"
                                    . addslashes(strtoupper(itaSuggest::getQuery())) . "%' AND MEDANN=0");
                            foreach ($anamed_tab as $anamed_rec) {
                                $indirizzo = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCIT'] . " " . $anamed_rec['MEDPRO'];
                                if (trim($indirizzo) != '') {
                                    $indirizzo = " - " . $indirizzo;
                                } else {
                                    $indirizzo = '';
                                }
                                itaSuggest::addSuggest($anamed_rec['MEDNOM']);
                            }
                        } else {
                            $anades_tab = $this->praLib->getGenericTab("SELECT * FROM ANADES WHERE " . $this->praLib->getPRAMDB()->strUpper('DESNOM') . " LIKE '%"
                                    . addslashes(strtoupper(itaSuggest::getQuery())) . "%'");
                            foreach ($anades_tab as $anades_rec) {
                                $indirizzo = $anades_rec['DESIND'] . " " . $anades_rec['DESCIT'] . " " . $anades_rec['DESPRO'];
                                if (trim($indirizzo) != '') {
                                    $indirizzo = " - " . $indirizzo;
                                } else {
                                    $indirizzo = '';
                                }
                                itaSuggest::addSuggest($anades_rec['DESNOM']);
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnAnades':
                $this->decodAnades($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnanamed':
                $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodAnanom($_POST['retKey'], 'rowid');
                break;
            case "returnPraclt":
                $this->DecodTipoPasso($_POST["retKey"], 'rowid');
                break;
            case 'returnAnastp':
                $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
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
        $whereProt = $whereID = "";
        // Imposto il filtro di ricerca
        $Descrizione = $_POST[$this->nameForm . '_Descrizione'];
        $CodiceSogg = $_POST[$this->nameForm . '_CodiceSogg'];
        $Denominazione = $_POST[$this->nameForm . '_Denominazione'];
        $CodiceResp = $_POST[$this->nameForm . '_CodiceResp'];
        $Nprot = $_POST[$this->nameForm . '_Nprot'];
        $AnnoProt = $_POST[$this->nameForm . '_AnnoProt'];
        $Ndoc = $_POST[$this->nameForm . '_Ndoc'];
        $DataDoc = $_POST[$this->nameForm . '_DataDoc'];
        $Stato = $_POST[$this->nameForm . '_Stato'];
        $DallaData = $_POST[$this->nameForm . '_DallaData'];
        $AllaData = $_POST[$this->nameForm . '_AllaData'];
        $Tipo = $_POST[$this->nameForm . '_Tipo'];
        $TipoPasso = $_POST[$this->nameForm . '_CodTipoPasso'];
        $progPasso = $_POST[$this->nameForm . '_progPasso'];
        $validoDa = $_POST[$this->nameForm . '_validoDa'];
        $validoAl = $_POST[$this->nameForm . '_validoAl'];
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];
        $conProtocollo = $_POST[$this->nameForm . '_siProt'];
        $conID = $_POST[$this->nameForm . '_siDoc'];

        if ($CodiceSogg) {
            $joinPramitDest1 = "INNER JOIN PRAMITDEST PRAMITDESTCOD ON PROPAS.PROPAK = PRAMITDESTCOD.KEYPASSO AND PRAMITDESTCOD.CODICE = '$CodiceSogg'";
        }
        if ($Denominazione) {
            $joinPramitDest2 = "INNER JOIN PRAMITDEST PRAMITDESTDEN ON PROPAS.PROPAK = PRAMITDESTDEN.KEYPASSO AND " . $this->PRAM_DB->strUpper('PRAMITDESTDEN.NOME') . " = '" . strtoupper($Denominazione) . "'";
        }

        if ($Nprot && $AnnoProt) {
            $joinPracom1 = "INNER JOIN PRACOM PRACOM ON PROPAS.PROPAK = PRACOM.COMPAK AND PRACOM.COMPRT = '$AnnoProt$Nprot'";
        } else {
            if ($Nprot) {
                // $joinPracom2 = "INNER JOIN PRACOM PRACOMNPROT ON PROPAS.PROPAK = PRACOMNPROT.COMPAK AND ".$this->PRAM_DB->subString('PRACOMNPROT.COMPRT', 5)." LIKE '%$Nprot%'";
                $joinPracom2 = "INNER JOIN PRACOM PRACOMNPROT ON PROPAS.PROPAK = PRACOMNPROT.COMPAK AND SUBSTRING(PRACOMNPROT.COMPRT, 5) LIKE '%$Nprot%'";
            }
            if ($AnnoProt) {
                $joinPracom3 = "INNER JOIN PRACOM PRACOMANNOPROT ON PROPAS.PROPAK = PRACOMANNOPROT.COMPAK AND " . $this->PRAM_DB->subString('PRACOMANNOPROT.COMPRT', 1, 4) . " = '$AnnoProt'";
            }
        }

        if ($Ndoc && $DataDoc) {
            $joinPracom1 = "INNER JOIN PRACOM PRACOM ON PROPAS.PROPAK = PRACOM.COMPAK AND PRACOM.COMIDDOC = '$Ndoc' AND COMDATADOC = '$DataDoc'";
        } else {
            if ($Ndoc) {
                $joinPracom2 = "INNER JOIN PRACOM PRACOMNPROT ON PROPAS.PROPAK = PRACOMNPROT.COMPAK AND PRACOMNPROT.COMIDDOC = '$Ndoc'";
            }
            if ($DataDoc) {
                $joinPracom3 = "INNER JOIN PRACOM PRACOMANNOPROT ON PROPAS.PROPAK = PRACOMANNOPROT.COMPAK AND PRACOMANNOPROT.COMDATADOC = '$DataDoc'";
            }
        }

        if ($conProtocollo == 1 || $conID == 1) {
            if ($conProtocollo && $conID) {
                $whereProtId = "AND PRACOMPROTID.COMPRT <> '' AND PRACOMPROTID.COMIDDOC <> ''";
            }
            if ($conProtocollo == 1 && $conID == 0) {
                $whereProtId = "AND PRACOMPROTID.COMPRT <> '' AND PRACOMPROTID.COMIDDOC = ''";
            }
            if ($conProtocollo == 0 && $conID == 1) {
                $whereProtId = "AND PRACOMPROTID.COMPRT = '' AND PRACOMPROTID.COMIDDOC <> ''";
            }
            //$joinPracom4 = "INNER JOIN PRACOM PRACOMPROTID ON PROPAS.PROPAK = PRACOMPROTID.COMPAK $whereProtId";
        } else {
            if ($joinPracom1 == "" && $joinPracom2 == "" && $joinPracom3 == "") {
                $whereProtId = "AND PRACOMPROTID.COMPRT = '' AND PRACOMPROTID.COMIDDOC = ''";
            }
        }
        $joinPracom4 = "LEFT OUTER JOIN PRACOM PRACOMPROTID ON PROPAS.PROPAK = PRACOMPROTID.COMPAK $whereProtId";

        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PRONUM AS PRONUM,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROVPA AS PROVPA,
                    PROPAS.PROVPN AS PROVPN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROQST AS PROQST,
                    PROPAS.PROPUB AS PROPUB,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSTAP AS PROSTAP,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROSTCH AS PROSTCH,
                    PROPAS.PROSTATO AS PROSTATO,                    
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PRODOCPROG AS PRODOCPROG,
                    PROPAS.PRODOCINIVAL AS PRODOCINIVAL,
                    PROPAS.PRODOCFINVAL AS PRODOCFINVAL,
                    PROPAS.PROINI AS PROINI,
                    PROGES.GESNUM AS GESNUM,
                    PROGES.GESDRI AS GESDRI,".
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,".
                $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'",'PROGES.SERIEANNO') . " AS SERIEPRATICA
                FROM PROPAS
                    LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
                    LEFT OUTER JOIN PROGES ON PROGES.GESNUM=PROPAS.PRONUM
                LEFT OUTER JOIN ".$this->PROT_DB->getDB().".ANASERIEARC ON ". $this->PROT_DB->getDB().".ANASERIEARC.CODICE = ".$this->PRAM_DB->getDB().".PROGES.SERIECODICE 
                    $joinPramitDest1
                    $joinPramitDest2
                    $joinPracom1
                    $joinPracom2
                    $joinPracom3
                    $joinPracom4
               WHERE 
                    PROPAS.PROPUB = 0 ";

        if ($Descrizione) {
            $sql .= " AND " . $this->PRAM_DB->strLower('PRODPA') . " LIKE '%" . strtolower($Descrizione) . "%'";
        }
        if ($CodiceResp) {
            $sql .= " AND PRORPA = '$CodiceResp'";
        }
        if ($Stato == "D") {
            $sql .= " AND PROINI = '' AND PROFIN = ''";
        }
        if ($Stato == "A") {
            $sql .= " AND PROINI <> '' AND PROFIN = ''";
        }
        if ($Stato == "C") {
            $sql .= " AND PROINI <> '' AND PROFIN <> ''";
        }
        if ($DallaData && $AllaData) {
            if ($Stato == "A") {
                $sql .= " AND (PROINI BETWEEN '$DallaData' AND '$AllaData')";
            }
            if ($Stato == "C") {
                $sql .= " AND (PROFIN BETWEEN '$DallaData' AND '$AllaData')";
            }
        }
        if ($Tipo) {
            $sql .= " AND PROPAS.PROOPE = '$Tipo'";
        } else {
            $sql .= " AND PROPAS.PROOPE = ''";
        }
        if ($TipoPasso) {
            $praclt_rec = $this->praLib->GetPraclt($TipoPasso);
            $sql .= " AND PROPAS.PRODTP LIKE '%" . addslashes($praclt_rec['CLTDES']) . "%'";
        }
        if ($progPasso) {
            $sql .= " AND PROPAS.PRODOCPROG = '$progPasso'";
        }
        if ($validoDa) {
            if ($validoAl == "") {
                $validoAl = $this->workYear . "1231";
            }
        }
        if ($validoAl) {
            if ($validoDa == "") {
                $validoDa = $this->workYear . "0101";
            }
        }
        if ($validoAl && $validoDa) {
            $sql .= " AND (PRODOCINIVAL >= '$validoDa' AND PRODOCFINVAL <= '$validoAl')";
        }
        if ($StatoPasso) {
            $sql .= " AND PROPAS.PROSTATO = $StatoPasso";
        }
        return $sql;
    }

    public function CreaCombo() {
        Out::html($this->nameForm . "_Stato", '');
        Out::select($this->nameForm . '_Stato', 1, "", "1", "");
        Out::select($this->nameForm . '_Stato', 1, "D", "0", "Da aprire");
        Out::select($this->nameForm . '_Stato', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato', 1, "C", "0", "Chiusi");
        //
        Out::html($this->nameForm . "_Tipo", '');
        praRic::ricComboFunzioni_base($this->nameForm . "_Tipo");
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridPassi);
        TableView::clearGrid($this->gridPassi);
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::hide($this->nameForm . "_DallaData_field");
        Out::hide($this->nameForm . "_AllaData_field");
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $keyPasso => $passi_view) {
            $msgStato = $icon = $acc = $cons = $dal = $al = "";
            //$Result_tab[$keyPasso]["PRATICA"] = intval(substr($passi_view['PRONUM'], 4, 6)) . "/" . substr($passi_view['PRONUM'], 0, 4);
            $Proges_rec = $this->praLib->GetProges($passi_view['PRONUM']);
            $Anaset_rec = $this->praLib->GetAnaset($Proges_rec['GESSTT']);
            $Anaatt_rec = $this->praLib->GetAnaatt($Proges_rec['GESATT']);
            $Anapra_rec = $this->praLib->GetAnapra($Proges_rec['GESPRO']);
            $msgTooltip = $Anaset_rec["SETDES"] . "<br>" . $Anaatt_rec['ATTDES'] . "<br>" . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES_3'];
            //$Result_tab[$keyPasso]["PRATICA"] = "<div class=\"ita-html\"><span title=\"$msgTooltip\" class=\"ita-tooltip\">" . intval(substr($passi_view['PRONUM'], 4, 6)) . "/" . substr($passi_view['PRONUM'], 0, 4) . "</span></div>";
            $Result_tab[$keyPasso]["PRATICA"] = "<div class=\"ita-html\"><span title=\"$msgTooltip\" class=\"ita-tooltip\">" . $passi_view['SERIEPRATICA'] . "</span></div>";
            if ($passi_view['PROSTATO'] != 0) {
                $Anastp_rec = $this->praLib->GetAnastp($passi_view['PROSTATO']);
                $msgStato = $Anastp_rec['STPFLAG'];
            }
            if ($passi_view['PROFIN']) {
                if ($msgStato == 'In corso') {
                    $msgStato = "";
                }
                $msgStato = $Anastp_rec['STPDES'];
                $Result_tab[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-green-24x24\">Passo Chiuso</span><span style=\"vertical-align:top;display:inline-block;\">$msgStato</span>";
            } elseif ($passi_view['PROINI']) {
                $Result_tab[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-red-24x24\">Passo Aperto</span><span style=\"display:inline-block;\">$msgStato</span>";
            }

            $pracomP_rec = $this->praLib->GetPracomP($passi_view['PROPAK']);
            if ($pracomP_rec['COMIDDOC']) {
                $numDoc = $pracomP_rec['COMIDDOC'];
                $dataDoc = substr($pracomP_rec['COMDATADOC'], 6, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 4, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 0, 4);
                $Result_tab[$keyPasso]['PRPARTENZA'] = $numDoc . "<br>" . $dataDoc;
            }
            if ($pracomP_rec['COMPRT']) {
                $numProt = substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                $dataProt = substr($pracomP_rec['COMDPR'], 6, 2) . "/" . substr($pracomP_rec['COMDPR'], 4, 2) . "/" . substr($pracomP_rec['COMDPR'], 0, 4);
                $Result_tab[$keyPasso]['PRPARTENZA'] = $numProt . "<br>" . $dataProt;
            }

            $pracomA_rec = $this->praLib->GetPracomA($passi_view['PROPAK']);
            if ($pracomA_rec['COMPRT']) {
                $numProt = substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                $dataProt = substr($pracomA_rec['COMDPR'], 6, 2) . "/" . substr($pracomA_rec['COMDPR'], 4, 2) . "/" . substr($pracomA_rec['COMDPR'], 0, 4);
                $Result_tab[$keyPasso]['PRARRIVO'] = $numProt . "<br>" . $dataProt;
            }
            if ($passi_view['PRODOCINIVAL']) {
                $dal = substr($passi_view['PRODOCINIVAL'], 6, 2) . "/" . substr($passi_view['PRODOCINIVAL'], 4, 2) . "/" . substr($passi_view['PRODOCINIVAL'], 0, 4);
            }
            if ($passi_view['PRODOCFINVAL']) {
                $al = substr($passi_view['PRODOCFINVAL'], 6, 2) . "/" . substr($passi_view['PRODOCFINVAL'], 4, 2) . "/" . substr($passi_view['PRODOCFINVAL'], 0, 4);
            }
            if ($dal || $al) {
                $Result_tab[$keyPasso]['VALIDITA'] = "Dal $dal<br>Al $al";
            }
            if ($passi_view['PROVPA'] != '' || $passi_view['PROVPN'] != '') {
                $Result_tab[$keyPasso]['VAI'] = '<span class="ita-icon ita-icon-arrow-green-dx-16x16">Passo Domanda</span>';
            }
            if ($passi_view['PROPART'] != 0) {
                $Result_tab[$keyPasso]['ARTICOLO'] = '<span class="ita-icon ita-icon-rtf-24x24">Articolo</span>';
            }
            if ($passi_view['PROCTR'] != '') {
                $Result_tab[$keyPasso]['PROCEDURA'] = '<span class="ita-icon ita-icon-ingranaggio-16x16">Procedura di Controllo</span>';
            }
            if ($passi_view['PROALL']) {
                $Result_tab[$keyPasso]['ALLEGATI'] = '<span class="ita-icon ita-icon-clip-16x16">allegati</span>';
            }
            $Result_tab[$keyPasso]['STATOCOM'] = $this->praLib->GetIconStatoCom($passi_view['PROPAK']);
        }

        return $Result_tab;
    }

    public function elaboraRecordsXls($Result_tab) {
        $Result_tab_new = array();
        foreach ($Result_tab as $keyPasso => $passi_view) {
            $dataProtP = $dataProtA = $msgStato = $icon = $acc = $cons = "";
            $Proges_rec = $this->praLib->GetProges($passi_view['PRONUM']);
            $Result_tab_new[$keyPasso]["PRATICA"] = $passi_view['SERIEPRATICA'] ;
            $Result_tab_new[$keyPasso]["DATAPRATICA"] = substr($passi_view['GESDRI'], 6, 2) . "/" . substr($passi_view['GESDRI'], 4, 2) . "/" . substr($passi_view['GESDRI'], 0, 4); 
            $Result_tab_new[$keyPasso]["RICHIESTA"] = "";
            if ($Proges_rec['GESPRA']) {
                $Result_tab_new[$keyPasso]["RICHIESTA"] = intval(substr($Proges_rec['GESPRA'], 4, 6)) . "/" . substr($Proges_rec['GESPRA'], 0, 4);
            }
            $Result_tab_new[$keyPasso]["RICEZIONE"] = "";
            if ($Proges_rec['GESDRI']) {
                $Result_tab_new[$keyPasso]["RICEZIONE"] = substr($Proges_rec['GESDRI'], 6, 2) . "/" . substr($Proges_rec['GESDRI'], 4, 2) . "/" . substr($Proges_rec['GESDRI'], 0, 4);
            }
            $Result_tab_new[$keyPasso]["SEQUENZA"] = $passi_view['PROSEQ'];
            $Result_tab_new[$keyPasso]["APERTO"] = substr($passi_view['PROINI'], 6, 2) . "/" . substr($passi_view['PROINI'], 4, 2) . "/" . substr($passi_view['PROINI'], 0, 4);
            $Result_tab_new[$keyPasso]["CHIUSO"] = "";
            if ($passi_view['PROFIN']) {
                $Result_tab_new[$keyPasso]["CHIUSO"] = substr($passi_view['PROFIN'], 6, 2) . "/" . substr($passi_view['PROFIN'], 4, 2) . "/" . substr($passi_view['PROFIN'], 0, 4);
            }
            //
            $pracomP_rec = $this->praLib->GetPracomP($passi_view['PROPAK']);
            $Result_tab_new[$keyPasso]['NPROT_PARTENZA'] = "";
            $Result_tab_new[$keyPasso]['DATAPROT_PARTENZA'] = "";
            if ($pracomP_rec['COMPRT']) {
                $numProt = substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                $dataProtP = substr($pracomP_rec['COMDPR'], 6, 2) . "/" . substr($pracomP_rec['COMDPR'], 4, 2) . "/" . substr($pracomP_rec['COMDPR'], 0, 4);
                $Result_tab_new[$keyPasso]['NPROT_PARTENZA'] = $numProt;
                $Result_tab_new[$keyPasso]['DATAPROT_PARTENZA'] = $dataProtP;
            }
            $Result_tab_new[$keyPasso]['NPROT_ARRIVO'] = "";
            $Result_tab_new[$keyPasso]['DATAPROT_ARRIVO'] = "";
            $pracomA_rec = $this->praLib->GetPracomA($passi_view['PROPAK']);
            if ($pracomA_rec['COMPRT']) {
                $numProt = substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                $dataProtA = substr($pracomA_rec['COMDPR'], 6, 2) . "/" . substr($pracomA_rec['COMDPR'], 4, 2) . "/" . substr($pracomA_rec['COMDPR'], 0, 4);
                $Result_tab_new[$keyPasso]['NPROT_ARRIVO'] = $numProt;
                $Result_tab_new[$keyPasso]['DATAPROT_ARRIVO'] = $dataProtA;
            }
            //
            $Result_tab_new[$keyPasso]['ADDETTO'] = $passi_view['RESPONSABILE'];
            $Result_tab_new[$keyPasso]['TIPO_PASSO'] = $passi_view['PRODTP'];
            $Result_tab_new[$keyPasso]['DESCRIZIONE'] = $passi_view['PRODPA'];
            $Result_tab_new[$keyPasso]['GIORNI'] = $passi_view['PROGIO'];

            //$Anaset_rec = $this->praLib->GetAnaset($Proges_rec['GESSTT']);
            //$Anaatt_rec = $this->praLib->GetAnaatt($Proges_rec['GESATT']);
            //$Anapra_rec = $this->praLib->GetAnapra($Proges_rec['GESPRO']);
            //$msgTooltip = $Anaset_rec["SETDES"] . "<br>" . $Anaatt_rec['ATTDES'] . "<br>" . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES_3'];
            //1$Result_tab[$keyPasso]["PRATICA"] = "<div class=\"ita-html\"><span title=\"$msgTooltip\" class=\"ita-tooltip\">" . intval(substr($passi_view['PRONUM'], 4, 6)) . "/" . substr($passi_view['PRONUM'], 0, 4) . "</span></div>";
            if ($passi_view['PROSTATO'] != 0) {
                $Anastp_rec = $this->praLib->GetAnastp($passi_view['PROSTATO']);
                $msgStato = $Anastp_rec['STPFLAG'];
            }
            if ($passi_view['PROFIN']) {
                if ($msgStato == 'In corso') {
                    $msgStato = "";
                }
                $msgStato = $Anastp_rec['STPDES'];
                $Result_tab_new[$keyPasso]['STATOPASSO'] = $msgStato;
            } elseif ($passi_view['PROINI']) {
                $Result_tab_new[$keyPasso]['STATOPASSO'] = $msgStato;
            }

            //$Result_tab[$keyPasso]['STATOCOM'] = $this->praLib->GetIconStatoCom($passi_view['PROPAK']);
        }

        return $Result_tab_new;
    }

    public function decodAnades($Codice, $retid, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        Out::valore($this->nameForm . "_CodiceSogg", $anades_rec['DESCOD']);
        Out::valore($this->nameForm . "_Denominazione", $anades_rec['DESNOM']);
    }

    public function DecodAnamedComP($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            Out::valore($this->nameForm . "_CodiceSogg", $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . "_Denominazione", $anamed_rec['MEDNOM']);
        }
        return $anamed_rec;
    }

    function DecodAnanom($Codice, $tipoRic = 'codice') {
        $ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        Out::valore($this->nameForm . '_CodiceResp', $ananom_rec['NOMRES']);
        Out::valore($this->nameForm . '_Responsabile', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
    }

    function DecodTipoPasso($codice, $tipo = 'codice') {
        $praclt_rec = $this->praLib->GetPraclt($codice, $tipo);
        Out::valore($this->nameForm . '_CodTipoPasso', $praclt_rec['CLTCOD']);
        Out::valore($this->nameForm . '_TipoPasso', $praclt_rec['CLTDES']);
    }

}