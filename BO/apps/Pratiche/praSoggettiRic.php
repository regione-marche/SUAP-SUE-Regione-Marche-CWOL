<?php

/**
 *
 * Ricerca Soggetti delle Pratiche
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    21.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praSoggettiRic() {
    $praSoggettiRic = new praSoggettiRic();
    $praSoggettiRic->parseEvent();
    return;
}

class praSoggettiRic extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    public $nameForm = "praSoggettiRic";
    public $divRis = "praSoggettiRic_divRisultato";
    public $divRic = "praSoggettiRic_divRicerca";
    public $gridGest = "praSoggettiRic_gridGest";

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
                $Result_tab2 = $this->elaboraRecordsXls($Result_tab1);
                $ita_grid02 = new TableView($this->gridGest, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->setSortIndex('PRATICA');
                $ita_grid02->setSortOrder('desc');
                $ita_grid02->exportXLS('', 'pratiche.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'PRATICA') {
                    $ordinamento = 'SERIEPRATICA';
                }
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $Result_tab = $this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        if ($_POST[$this->nameForm . "_Ruolo"] == "" && $_POST[$this->nameForm . "_Nominativo"] == "") {
                            Out::msgInfo("Attenzione!!", "Complilare almeno un campo di ricerca");
                            break;
                        }
                        $this->Elenca();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . "_Ruolo_butt":
                        praRic::praRicRuoli($this->nameForm);
                        break;
                    case $this->nameForm . "_Nominativo_butt":
                        praRic::praRicAnades($this->nameForm);
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
                    case $this->nameForm . '_Ruolo':
                        $ruolo = $_POST[$this->nameForm . '_Ruolo'];
                        if ($ruolo) {
                            $ruolo = str_pad($ruolo, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Ruolo', $ruolo);
                            $this->DecodAnaruo($ruolo);
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
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nominativo':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Anades_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT DESNOM FROM ANADES WHERE " . $this->PRAM_DB->strLower('DESNOM') . " LIKE '%" . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Anades_tab as $Anades_rec) {
                            itaSuggest::addSuggest($Anades_rec['DESNOM']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnAnaruo':
                $this->DecodAnaruo($_POST['retKey'], 'rowid');
                break;
            case 'returnAnades':
                $this->DecodAnades($_POST['retKey'], 'rowid');
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
        // Imposto il filtro di ricerca
        $Ruolo = $_POST[$this->nameForm . '_Ruolo'];
        $Nominativo = $_POST[$this->nameForm . '_Nominativo'];
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];
        if ($StatoPasso != '') {
            $joinStatoPasso = " INNER JOIN PROPAS PROPAS2 ON PROPAS2.PRONUM=PROGES.GESNUM AND PROPAS2.PROSTATO = '$StatoPasso'";
        }


        $sql = "SELECT
            PROGES.ROWID AS ROWID,
            PROGES.GESNUM AS GESNUM,".
            $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'",'PROGES.SERIEANNO') . " AS SERIEPRATICA,
            PROGES.GESDRE AS GESDRE,
            PROGES.GESDRI AS GESDRI,
            PROGES.GESORA AS GESORA,
            PROGES.GESDCH AS GESDCH,
            PROGES.GESPRA AS GESPRA,
            PROGES.GESTSP AS GESTSP,
            PROGES.GESSPA AS GESSPA,            
            PROGES.GESNOT AS GESNOT,
            PROGES.GESPRE AS GESPRE,
            PROGES.GESNPR AS GESNPR,
            ANADES.DESNOM AS DESNOM,
            PROGES.GESRES AS GESRES,
            PROGES.SERIECODICE AS SERIECODICE,
            PROGES.SERIEANNO AS SERIEANNO,
            PROGES.SERIEPROGRESSIVO AS SERIEPROGRESSIVO," .
                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
            PROGES.GESPRO AS GESPRO,
            ANAPRA.PRADES__1 AS PRADES__1
        FROM PROGES PROGES
            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM 
             LEFT OUTER JOIN ".$this->PROT_DB->getDB().".ANASERIEARC ON ". $this->PROT_DB->getDB().".ANASERIEARC.CODICE = ".$this->PRAM_DB->getDB().".PROGES.SERIECODICE 
            $joinStatoPasso
        WHERE 1";

        if ($Nominativo != '') {
            $sql.=" AND (" . $this->PRAM_DB->strLower('ANADES.DESNOM') . " LIKE '%" . strtolower($Nominativo) . "%')";
        }
        if ($Ruolo != '') {
            $sql.=" AND ANADES.DESRUO = '$Ruolo'";
        }
        app::log($sql);
        return $sql;
    }

    function Elenca() {
        try {   // Effettuo la FIND
            $ita_grid01 = new TableView($this->gridGest, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $this->CreaSql()));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            $ita_grid01->setSortIndex('SERIEPRATICA');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $ita_grid01->getDataArray();
            $Result_tab = $this->elaboraRecord($Result_tab);
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                Out::msgStop("Selezione", "Nessun record trovato.");
                $this->OpenRicerca();
            } else {   // Visualizzo la ricerca
                Out::hide($this->divRic, '');
                Out::show($this->divRis, '');
                $this->Nascondi();
                Out::show($this->nameForm . '_AltraRicerca');
                Out::setFocus('', $this->nameForm . '_Nuovo');
                TableView::enableEvents($this->gridGest);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    function elaboraRecordsXls($Result_tab) {
        $Result_tab_new = array();
        foreach ($Result_tab as $key => $Result_rec) {
            //$Result_tab_new[$key]['PRATICA'] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Result_tab_new[$key]['PRATICA'] = $Result_rec['SERIEPRATICA'];
            $Result_tab_new[$key]['RICHIESTA_ONLINE'] = "";
            if ($Result_rec['GESPRA']) {
                $Result_tab_new[$key]['RICHIESTA_ONLINE'] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            }
            $Result_tab_new[$key]["DATA"] = substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4);
            $Result_tab_new[$key]["RESPONSABILE"] = $Result_rec['RESPONSABILE'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            $Result_tab_new[$key]["INTESTATARIO"] = "";
            $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = "";
            if ($Anades_rec) {
                //$Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'] . "<br>" . $Anades_rec['DESTEL'];
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = $Anades_rec['DESTEL'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                //$Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'] . "<br>" . $Anades_rec['DESTEL'];
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = $Anades_rec['DESTEL'];
            }
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            //$Result_tab_new[$key]['IMPRESA'] = $datiInsProd['IMPRESA'] . "<br>" . $datiInsProd['INDIRIZZO'] . "<br>" . $datiInsProd['FISCALE'];
            $Result_tab_new[$key]['IMPRESA'] = $datiInsProd['IMPRESA'];
            $Result_tab_new[$key]['INDIRIZZO_IMPRESA'] = $datiInsProd['INDIRIZZO'];
            $Result_tab_new[$key]['FISCALE_IMPRESA'] = $datiInsProd['FISCALE'];

            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab_new[$key]['SETTORE'] = $anaset_rec['SETDES'];
                $Result_tab_new[$key]['ATTIVITA'] = $anaatt_rec['ATTDES'];
                $Result_tab_new[$key]['PROCEDIMENTO'] = $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
                $Result_tab_new[$key]['OGGETTO'] = $Result_rec['GESOGG'];
            }

            $Result_tab_new[$key]["NOTE"] = $Result_rec['GESNOT'];
            $Result_tab_new[$key]["AGGREGATO"] = $Result_tab[$key]["SPORTELLO"] = "";
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab_new[$key]["SPORTELLO"] = $anatsp_rec['TSPDES'];
            }
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $Result_tab_new[$key]["AGGREGATO"] = $anaspa_rec['SPADES'];
            }

            $Result_tab[$key]["NUMERO_GIORNI"] = $Result_rec['NUMEROGIORNI'];
            $Result_tab_new[$key]["DATA_CHIUSURA"] = "";
            if ($Result_rec['GESDCH']) {
                $Result_tab_new[$key]["DATA_CHIUSURA"] = substr($Result_rec['GESDCH'], 6, 2) . "/" . substr($Result_rec['GESDCH'], 4, 2) . "/" . substr($Result_rec['GESDCH'], 0, 4);
            }
        }
        return $Result_tab_new;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridGest);
        TableView::clearGrid($this->gridGest);
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Ruolo');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]["GESNUM"] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO='0001'", false);
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
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $Result_tab[$key]["AGGREGATO"] = $anaspa_rec['SPADES'];
            }

            if ($Result_rec['GESDCH']) {
                $prasta_rec = $this->praLib->GetPrasta($Result_rec['GESNUM']);
                if ($prasta_rec['STAFLAG'] == "Annullata") {
                    $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-delete-24x24">Pratica Annullata</span>';
                } elseif ($prasta_rec['STAFLAG'] == "Chiusa Positivamente") {
                    $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-check-green-24x24">Pratica chiusa positivamente</span>';
                } elseif ($prasta_rec['STAFLAG'] == "Chiusa Negativamente") {
                    $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-check-red-24x24">Pratica chiusa negativamente</span>';
                }
            } else {
                $Propas_tab = $this->praLib->GetPropas($Result_rec['GESNUM'], "codice", true);
                if ($Propas_tab) {
                    foreach ($Propas_tab as $Propas_rec) {
                        if ($Propas_rec['PROPUB'] == 0) {
                            if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'] == "") {
                                $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagray-24x24">Pratica con passi aperti</span>';
                            } else if (($Propas_rec['PROINI'] && $Propas_rec['PROFIN']) || ($Propas_rec['PROINI'] = "" && $Propas_rec['PROFIN'] = "")) {
                                $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagreen-24x24">Pratica in corso</span>';
                            }
                        } else {
                            $Result_tab[$key]['STATO'] = '<span class="ita-icon ita-icon-apertagreen-24x24">Pratica in corso</span>';
                        }
                    }
                }
            }
            if ($Result_rec['GESPRE']) {
                $Result_tab[$key]['ANTECEDENTE'] = '<span class="ui-icon ui-icon-folder-open">true</span>';
            }

            $pasdoc_tab = $this->praLib->GetPasdoc($Result_rec['GESNUM'], 'pratica', true);
            if ($pasdoc_tab) {
                $non_valido = false;
                $validi = array();
                foreach ($pasdoc_tab as $pasdoc_rec) {
                    if ($pasdoc_rec['PASSTA'] == "N") {
                        $non_valido = true;
                    } elseif ($pasdoc_rec['PASSTA'] == "V") {
                        $validi[] = $pasdoc_rec;
                    }
                }
                if ($non_valido === true) {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-red-24x24\">Ci sono allegati non validi</span>";
                } else {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-grey-24x24\">Ci sono allegati da controllare</span>";
                }
                if ($validi == $pasdoc_tab) {
                    $Result_tab[$key]['STATOALL'] = "<span class=\"ita-icon ita-icon-check-green-24x24\">Tutti gli allegati sono stati validati</span>";
                }
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
        }
        return $Result_tab;
    }

    function DecodAnaruo($Codice, $tipoRic = 'codice') {
        $anaruo_rec = $this->praLib->GetAnaruo($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Ruolo", $anaruo_rec['RUOCOD']);
        Out::valore($this->nameForm . "_descRuolo", $anaruo_rec['RUODES']);
        return $anaruo_rec;
    }

    function DecodAnades($Codice, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Nominativo", $anades_rec['DESNOM']);
    }

}

?>