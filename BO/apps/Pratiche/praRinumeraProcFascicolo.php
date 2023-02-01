<?php

/**
 *
 * CAMBIO CODICE PROCEDIMENTO NEI FASCICOLI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praRinumeraProcFascicolo() {
    $praRinumeraProcFascicolo = new praRinumeraProcFascicolo();
    $praRinumeraProcFascicolo->parseEvent();
    return;
}

class praRinumeraProcFascicolo extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praRinumeraProcFascicolo";
    public $divRis = "praRinumeraProcFascicolo_divRisultato";
    public $divRic = "praRinumeraProcFascicolo_divRicerca";
    public $gridFascicoli = "praRinumeraProcFascicolo_gridFascicoli";

    function __construct() {
        parent::__construct();

        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
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
                $this->OpenRicerca();
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                $sord = $_POST['sord'];
                if ($ordinamento != 'IMPRESA' && $ordinamento != 'FISCALE') {
                    $arrayOrd = $this->praLib->GetOrdinamentoGridGest($ordinamento, $sord);
                    $ordinamento = $arrayOrd['sidx'];
                    $sord = $arrayOrd['sord'];
                    $sql = $this->CreaSql();
                    TableView::disableEvents($this->gridFascicoli);
                    $ita_grid01 = new TableView($this->gridFascicoli, array(
                        'sqlDB' => $this->PRAM_DB,
                        'sqlQuery' => $sql));
                    $ita_grid01->setPageNum($_POST['page']);
                    $ita_grid01->setPageRows($_POST['rows']);
                    $ita_grid01->setSortIndex($ordinamento);
                    $ita_grid01->setSortOrder($sord);
                    $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                    $ita_grid01->getDataPageFromArray('json', $Result_tab);
                    TableView::enableEvents($this->gridFascicoli);
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $proges_tab_ctr = $this->praLib->getGenericTab($this->CreaSql());
                        if (!$proges_tab_ctr) {
                            Out::msgInfo("Ricerca Fascicoli", "Fascicoli non trovati.");
                            break;
                        }
                        TableView::clearGrid($this->gridFascicoli);
                        TableView::enableEvents($this->gridFascicoli);
                        TableView::reload($this->gridFascicoli);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Conferma');
                        Out::show($this->nameForm . '_AltraRicerca');
                        break;
                    case $this->nameForm . '_Conferma':
                        $oldProc = $_POST[$this->nameForm . '_pranum'];

                        /*
                         * Controllo valorizzazione Procediemnto Sorgente
                         */
                        if ($oldProc == "") {
                            Out::msgInfo("Attenzione", "Procedimento Sorgente non trovato.");
                            break;
                        }

                        /*
                         * Select di Controllo
                         */
                        $proges_tab_ctr = $this->praLib->getGenericTab("SELECT * FROM PROGES WHERE GESPRO = '$oldProc'");
                        $propas_tab_ctr = $this->praLib->getGenericTab("SELECT * FROM PROPAS WHERE PROPRO = '$oldProc'");
                        $prodag_tab_ctr = $this->praLib->getGenericTab("SELECT * FROM PRODAG WHERE DAGCOD = '$oldProc'");
                        $prasta_tab_ctr = $this->praLib->getGenericTab("SELECT * FROM PRASTA WHERE STAPRO = '$oldProc'");

                        /*
                         * Preparazione Messaggio
                         */
                        $htmlHeader = "<div>FASCICOLI: " . count($proges_tab_ctr) . "</div>";
                        $htmlHeader .= "<div>PASSI: " . count($propas_tab_ctr) . "</div>";
                        $htmlHeader .= "<div>DATI AGGIUNTIVI: " . count($prodag_tab_ctr) . "</div>";
                        $htmlHeader .= "<div>STATI FASCICOLI: " . count($prasta_tab_ctr) . "</div>";
                        $header = "<div style=\"font-size:1.2em;\" class=\"ita-box-highlight ui-widget-content ui-corner-all ui-state-highlight\"><b>Confermando verranno rinumerati:<br><br>$htmlHeader<br> dal Procedimento $oldProc al nuovo Procedimento che verrà scelto.</b></div>";
                        //
                        Out::msgInput(
                                'Scelta Nuovo Procedimento', array(
                            array(
                                'id' => $this->nameForm . '_CodiceProc',
                                'name' => $this->nameForm . '_CodiceProc',
                                'class' => "ita-edit-lookup ita-readonly",
                                'size' => '7',
                                'maxlength' => '6'),
                            array(
                                'id' => $this->nameForm . '_DescProc',
                                'name' => $this->nameForm . '_DescProc',
                                'class' => "ita-readonly",
                                'size' => '50'),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaRinumera', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm, "auto", "auto", true, $header
                        );

                        break;
                    case $this->nameForm . '_ConfermaRinumera':
                        $oldProc = $_POST[$this->nameForm . '_pranum'];
                        $newProc = $_POST[$this->nameForm . '_CodiceProc'];

                        /*
                         * Controllo valorizzazione Procediemnto Sorgente
                         */
                        if ($oldProc == "") {
                            Out::msgInfo("Attenzione", "Procedimento Sorgente non trovato.");
                            break;
                        }

                        /*
                         * Controllo valorizzazione Nuovo Codice Procediemnto
                         */
                        if ($newProc == "") {
                            Out::msgInfo("Attenzione", "Scegliere Un Codice Procedimento.");
                            break;
                        }

                        /*
                         * Controllo che il Procedimento sorgente e destino siano diversi
                         */
                        if ($newProc == $oldProc) {
                            Out::msgInfo("Attenzione", "E' stato selezionato lo stesso procedimento della fase di ricerca.<br>Scegliere un procedimento diverso.");
                            break;
                        }

                        /*
                         * Ricontrollo Presenza Fascicoli
                         */
                        $proges_tab_ctr = $this->praLib->getGenericTab("SELECT * FROM PROGES WHERE GESPRO = '$oldProc'");
                        if (!$proges_tab_ctr) {
                            Out::msgInfo("Ricerca Fascicoli", "Fascicoli con procedimento $oldProc non trovati.");
                            break;
                        }

                        /*
                         * UPDATE
                         */
                        try {
                            ItaDB::DBSQLExec($this->PRAM_DB, "UPDATE PROGES SET GESPRO = '$newProc' WHERE GESPRO = '$oldProc'");
                            ItaDB::DBSQLExec($this->PRAM_DB, "UPDATE PROPAS SET PROPRO = '$newProc' WHERE PROPRO = '$oldProc'");
                            ItaDB::DBSQLExec($this->PRAM_DB, "UPDATE PRODAG SET DAGCOD = '$newProc' WHERE DAGCOD = '$oldProc'");
                            ItaDB::DBSQLExec($this->PRAM_DB, "UPDATE PRASTA SET STAPRO = '$newProc' WHERE STAPRO = '$oldProc'");
                        } catch (Exception $exc) {
                            Out::msgStop("Errore", "Errore Update: " . $exc->getMessage());
                            break;
                        }

                        $this->OpenRicerca();
                        Out::msgBlock("", 3000, true, "Procedimento $newProc aggiornato correttamente su " . count($proges_tab_ctr) . " Fascicoli");
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_pranum_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", "RICERCA");
                        break;
                    case $this->nameForm . '_CodiceProc_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", "MODIFICA");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_pranum':
                        $codice = $_POST[$this->nameForm . '_pranum'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if ($Anapra_rec) {
                                Out::valore($this->nameForm . '_pranum', $Anapra_rec["PRANUM"]);
                                Out::valore($this->nameForm . '_desc', $Anapra_rec["PRADES__1"] . ' ' . $Anapra_rec["PRADES__1"]);
                            }
                        }
                        break;
                }
                break;
            case 'returnAnapra':
                $anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ID_ANAPRA'], 'rowid');
                switch ($_POST['retid']) {
                    case "RICERCA":
                        Out::valore($this->nameForm . '_pranum', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_desc', $anapra_rec['PRADES__1']);
                        break;
                    case "MODIFICA":
                        Out::valore($this->nameForm . '_CodiceProc', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DescProc', $anapra_rec['PRADES__1']);
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::setFocus('', $this->nameForm . '_pranum');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divDup);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        //if ($svuotaElenco == true) {
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridFascicoli);
        TableView::clearGrid($this->gridFascicoli);
        //}
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function CreaSql() {
        $sql = "SELECT
                    DISTINCT PROGES.ROWID AS ROWID,
                    PROGES.GESNUM AS GESNUM," .
                $this->PRAM_DB->strConcat("SERIEANNO", "LPAD(SERIEPROGRESSIVO, 6, '0')") . " AS ORDER_GESNUM,
                    PROGES.SERIEANNO AS SERIEANNO,
                    PROGES.SERIEPROGRESSIVO AS SERIEPROGRESSIVO,
                    PROGES.SERIECODICE AS SERIECODICE,
                    PROGES.GESKEY AS GESKEY,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESDRE AS ORDER_GESDRE,
                    PROGES.GESDRI AS GESDRI,
                    PROGES.GESORA AS GESORA,
                    PROGES.GESDCH AS GESDCH,
                    PROGES.GESCODPROC AS GESCODPROC,
                    PROGES.GESPRA AS GESPRA,
                    PROGES.GESPRA AS ORDER_GESPRA,
                    PROGES.GESTSP AS GESTSP,
                    PROGES.GESSPA AS GESSPA,
                    PROGES.GESNOT AS GESNOT,
                    PROGES.GESPRE AS GESPRE,
                    PROGES.GESDSC AS GESDSC,
                    PROGES.GESSTT AS GESSTT,
                    PROGES.GESATT AS GESATT,
                    PROGES.GESOGG AS GESOGG,
                    PROGES.GESNPR AS GESNPR,
                    CAST(PROGES.GESNPR AS UNSIGNED),
                    PROGES.GESRES AS GESRES,
                    PROGES.GESPRO AS GESPRO,
                    ANAPRA.PRADES__1 AS PRADES__1,
                    ANADES.DESNOM AS DESNOM
                FROM
                    PROGES
                LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM
                WHERE 
                    GESPRO = '" . $_POST[$this->nameForm . '_pranum'] . "'
                GROUP BY 
                    GESNUM";
        return $sql;
    }

    function elaboraRecords($Result_tab) {
        $presenteGesKey = false;
        foreach ($Result_tab as $key => $Result_rec) {
            $aggregato = "";
            $Serie_rec = $this->praLib->ElaboraProgesSerie($Result_rec['GESNUM'], $Result_rec['SERIECODICE'], $Result_rec['SERIEANNO'], $Result_rec['SERIEPROGRESSIVO']);
            if (intval(substr($Result_rec['GESNUM'], 4, 6)) !== 0) {
                $Result_tab[$key]["GESNUM"] = "<div><b>" . $Serie_rec . "</b></div>";
            } else {
                $Result_tab[$key]["GESNUM"] = "<div><b>" . $Serie_rec . "</b></div>";
            }
            $gespra = "<div> </div>";
            $richiesta = '';
            if ($Result_rec['GESPRA']) {
                $richiesta = substr($Result_rec['GESPRA'], 4) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            }
            if ($Result_rec['GESKEY']) {
                $richiesta = $Result_rec['GESKEY'];
                $presenteGesKey = true;
            }
            if ($richiesta) {
                $gespra = "<div style=\"background-color:DodgerBlue;color:white;\"><b>" . $richiesta . "</b></div>";
                //$gespra = "<div style=\"background-color:DodgerBlue;color:white;\"><b>" . intval(substr($Result_rec['GESPRA'], 4, 6)) . "/" . substr($Result_rec['GESPRA'], 0, 4) . "</b></div>";
            }
            $gesnpr = "<div> </div>";
            if ($Result_rec['GESNPR'] != 0) {
                $gesnpr = "<div style=\"color:DodgerBlue;\"><b>" . intval(substr($Result_rec['GESNPR'], 4, 6)) . "/" . substr($Result_rec['GESNPR'], 0, 4) . "</b></div>";
            }
            $Result_tab[$key]["GESNUM"] .= $gespra . $gesnpr;
//
            $Result_tab[$key]["ORDER_GESNUM"] = $Result_rec['GESNUM'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'] . "'", false);
            if ($Anades_rec) {
                $Result_tab[$key]["DESNOM"] = "<div style =\"height:65px;overflow:auto;text-align:center;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div><div><b>" . $Result_rec['GESCODPROC'] . "</b></div></div>";
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUOCOD'] . "'", false);
                if ($Anades_rec) {
                    $Result_tab[$key]["DESNOM"] = "<div style =\"height:65px;overflow:auto;text-align:center;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div><div><b>" . $Result_rec['GESCODPROC'] . "</b></div></div>";
                } else {
                    $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                    $Result_tab[$key]["DESNOM"] = "<div style =\"height:65px;overflow:auto;text-align:center;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div><div><b>" . $Result_rec['GESCODPROC'] . "</b></div></div>";
                }
            }

            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab[$key]['DESCPROC'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div><div>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'] . "</div><div>" . $Result_rec['GESOGG'] . "</div></div>";
            }

            $Result_tab[$key]['NOTE'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\">" . $Result_rec['GESNOT'] . "</div>";

            if ($Result_rec['GESPRA'] != 0) {
                $Result_tab[$key]["GESPRA"] = intval(substr($Result_rec['GESPRA'], 4, 6)) . "/" . substr($Result_rec['GESPRA'], 0, 4);
                $Result_tab[$key]["ORDER_GESPRA"] = $Result_rec['GESPRA'];
            } else {
                $Result_tab[$key]["GESPRA"] = "";
            }
            if ($Result_rec['GESDRI'] != "" && $Result_rec['GESORA'] != "") {
                $Result_tab[$key]["RICEZ"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4) . "<br>(" . $Result_rec['GESORA'] . ")";
            } else {
                $Result_tab[$key]["RICEZ"] = "";
            }
            $Result_tab[$key]["ORDER_GESDRE"] = $Result_rec['GESDRE'];
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $aggregato = $anaspa_rec['SPADES'];
            }
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab[$key]["SPORTELLO"] = "<div class=\"ita-Wordwrap\">" . $anatsp_rec['TSPDES'] . "</div><div>$aggregato</div>";
            }

            /*
             * valorizzo impresa e codice fiscale sulla tabella
             */
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab[$key]['IMPRESA'] = "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";
        }

        return $Result_tab;
    }

}