<?php

/**
 *
 * IMPORTAZIONE DELLE PRATICHE NON ANCORA PROTOCOLLATE
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
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praCtrRichieste() {
    $praCtrRichieste = new praCtrRichieste();
    $praCtrRichieste->parseEvent();
    return;
}

class praCtrRichieste extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praCtrRichieste";
    public $divGes = "praCtrRichieste_divGestione";
    public $divRis = "praCtrRichieste_divRisultato";
    public $gridCtrRichieste = "praCtrRichieste_gridCtrRichieste";
    public $returnModel;
    public $returnEvent;
    public $daPortlet;
    public $allegati = array();
    public $allegatiTabella = array();
    public $allegatiInfocamere = array();
    public $proric_rec = array();
    public $emlInfocamere;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            /*
             * Istanza risorse oggetti esterni
             */
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();

            /*
             * Rilettura delle varuabili in session
             */
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
            $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
            $this->allegatiTabella = App::$utente->getKey($this->nameForm . '_allegatiTabella');
            $this->allegatiInfocamere = App::$utente->getKey($this->nameForm . '_allegatiInfocamere');
            $this->daPortlet = App::$utente->getKey($this->nameForm . '_daPortlet');
            $this->emlInfocamere = App::$utente->getKey($this->nameForm . '_emlInfocamere');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            /*
             * Salvo variabili in session
             */
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
            App::$utente->setKey($this->nameForm . '_allegatiTabella', $this->allegatiTabella);
            App::$utente->setKey($this->nameForm . '_allegatiInfocamere', $this->allegatiInfocamere);
            App::$utente->setKey($this->nameForm . '_daPortlet', $this->daPortlet);
            App::$utente->setKey($this->nameForm . '_emlInfocamere', $this->emlInfocamere);
        }
    }

    function getAllegati() {
        return $this->allegati;
    }

    function getAllegatiInfocamere() {
        return $this->allegatiInfocamere;
    }

    function setAllegati($allegati) {
        $this->allegati = $allegati;
    }

    function setAllegatiInfocamere($allegatiInfocamere) {
        $this->allegatiInfocamere = $allegatiInfocamere;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                /*
                 * Inizializzo l'aspetto iniziale della form
                 * e popolo eventuali campi dei default
                 */
                $this->allegati = array();
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $retVisibilta = $this->praLib->GetVisibiltaSportello();
                $nuovoTitolo = "Elenco Richieste da Acquisire:";
//                if ($retVisibilta['SPORTELLO'] != 0) {
//                    $nuovoTitolo .= ' Sportello ' . $retVisibilta['SPORTELLO_DESC'];
//                }
                if (count($retVisibilta['SPORTELLI']) != 0) {
                    $nuovoTitolo .= ' Sportello/i ' . $retVisibilta['SPORTELLO_DESC'];
                }
                if ($retVisibilta['AGGREGATO'] != 0) {
                    $nuovoTitolo .= ' Aggregato ' . $retVisibilta['AGGREGATO_DESC'];
                }
                $this->CaricaRichieste();
                Out::setDialogTitle($this->nameForm, $nuovoTitolo);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridCtrRichieste:
                        $this->daPortlet = $_POST['daPortlet'];
                        if ($this->returnModel == "") {
                            $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                            $this->returnEvent = $_POST[$this->nameForm . "_returnEvent"];
                        }
                        $this->allegatiInfocamere = $_POST["allegatiInfocamere"];
                        $this->emlInfocamere = $_POST["emlInfocamere"];
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->nameForm . "_gridAllegati":
                        if ($this->allegatiInfocamere) {
                            $allegatiTabella = array_merge($this->allegati, $this->allegatiInfocamere);
                        } else {
                            $allegatiTabella = $this->allegati;
                        }
                        if (array_key_exists($_POST['rowid'], $allegatiTabella) == true) {
                            Out::openDocument(utiDownload::getUrl(
                                            $allegatiTabella[$_POST['rowid']]['FILENAME'], $allegatiTabella[$_POST['rowid']]['DATAFILE']
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $this->elaboraRecord($Result_tab1);
                $ita_grid02 = new TableView($this->gridCtrRichieste, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->setSortIndex('RICNUM');
                $ita_grid02->exportXLS('', 'procedimenti_online.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridCtrRichieste:
                        $ordinamento = $_POST['sidx'];
                        $ord = $_POST['sord'] != '' ? $_POST['sord'] : "asc";
                        if ($ordinamento == 'NUMERO') {
                            $ordinamento = 'RICNUM';
                        }
                        if ($ordinamento == 'INTESTATARIO') {
                            $ordinamento = 'RICCOG';
                        }
                        if ($ordinamento == 'RICEZ') {
                            $ordinamento = "RICDAT $ord, RICTIM";
                        }
                        if ($ordinamento == 'DESC_PRO') {
                            $ordinamento = 'RICPRO';
                        }
                        if ($ordinamento == 'SPORTELLO_AGGREGATO') {
                            $ordinamento = 'RICSPA';
                        }
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($ord);
                        $Result_tab = $ita_grid01->getDataArray();
                        $Result_tab = $this->elaboraRecord($Result_tab);
                        $ita_grid01->getDataPageFromArray('json', $Result_tab);
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praCtrProc', $parameters);
                break;
            case 'cellSelect': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridAllegati':
                        $allegatoFirmato = $this->allegati[$_POST['rowid']];
                        App::log($allegatoFirmato);

                        $ext = pathinfo($allegatoFirmato['FILENAME'], PATHINFO_EXTENSION);
                        if (strtolower($ext) == "p7m") {
                            $this->praLib->VisualizzaFirme($allegatoFirmato['DATAFILE'], $allegatoFirmato['FILENAME']);
                        }
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Carica':
                        $variante = false;
                        if ($this->proric_rec['RICPC'] == "1") {
                            $variante = true;
                        }
                        if ($this->proric_rec['RICSTA'] == "91" && !$this->allegatiInfocamere) {
                            Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
                                'F8-No' => array('id' => $this->nameForm . '_NoConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Si' => array('id' => $this->nameForm . '_SiConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    ), "auto", "auto", "false"
                            );
                        } elseif (($this->proric_rec['RICRPA'] && !$variante) || $this->proric_rec['PROPAK']) {
                            $this->returnToParent();
                        } else {
                            $model = 'praGestDatiEssenziali';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnCaricaMail';
                            $_POST['datiMail']['Dati']['PRORIC_REC'] = $this->proric_rec;
                            $_POST['isFrontOffice'] = true;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        //$this->CaricaRichieste();
                        Out::show($this->divRis, '');
                        Out::hide($this->divGes, '');
                        Out::hide($this->nameForm . "_buttonBar", '');
                        TableView::enableEvents($this->gridCtrRichieste);
                        break;
                    case $this->nameForm . "_AnnullaConfermaMail":
                    case $this->nameForm . "_NoConfermaMail":
                        if ($this->daPortlet == 'true') {
                            Out::closeDialog($this->nameForm);
                        } else {
                            Out::show($this->divRis);
                            $this->Nascondi();
                            TableView::enableEvents($this->gridCtrRichieste);
                        }
                        break;
                    case $this->nameForm . "_SiConfermaMail":
                        //$this->GetDatiPratica();
                        if ($this->daPortlet == 'true') {
                            $modelChiamante = "praGestElenco";
                        } else {
                            $modelChiamante = $this->returnModel;
                        }
                        $_POST = array();
                        $_POST['id'] = $modelChiamante . "_Infocamere";
                        $_POST['model'] = $modelChiamante;
                        $_POST['datiMail']['ELENCOALLEGATI'] = $this->allegati;
                        $_POST['datiMail']['PRORIC_REC'] = $this->proric_rec;
                        $_POST['tipoReg'] = 'consulta';
                        $_POST['rowidChiamante'] = $this->proric_rec['ROWID'];
                        $_POST['daPortlet'] = $this->daPortlet;
                        $phpURL = App::getConf('modelBackEnd.php');
                        if ($this->daPortlet == 'true')
                            itaLib::openForm($modelChiamante);
                        $objModel = itaModel::getInstance($modelChiamante);
                        $objModel->setEvent("onClick");
                        $objModel->parseEvent();
                        $this->close();
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case "returnCaricaMail":
                if ($ditta == '') {
                    $ditta = App::$utente->getKey('ditta');
                }
                if ($this->proric_rec ['RICSTA'] == "91" && !$this->allegatiInfocamere) {
                    Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
                        'F8-No' => array('id' => $this->nameForm . '_NoConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Si' => array('id' => $this->nameForm . '_SiConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f5")), "auto", "auto", "false"
                    );
                } else {
                    //$this->GetDatiPratica();
                    $this->returnToParent();
                }
                break

                ;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_proric_rec');
        App::$utente->removeKey($this->nameForm . '_allegatiTabella');
        App::$utente->removeKey($this->nameForm . '_allegatiInfocamere');
        App::$utente->removeKey($this->nameForm . '_daPortlet');
        App::$utente->removeKey($this->nameForm . '_emlInfocamere');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $datiAssegnazione = $_POST['datiMail']['Dati']['Assegnazione'];
        $proges_rec = $_POST['datiMail']['Dati']['PROGES'];
        $_POST = array();
        $_POST['daPortlet'] = $this->daPortlet;
        $_POST['datiMail']['ELENCOALLEGATI'] = $this->allegati;
        //$_POST['datiMail']['ALLEGATIINFOCAMERE'] = $this->allegatiInfocamere;
        $_POST['datiMail']['ALLEGATICOMUNICA'] = $this->allegatiInfocamere;
        $_POST['datiMail']['PRORIC_REC'] = $this->proric_rec;
        $_POST['datiMail']['PROGES'] = $proges_rec;
        $_POST['datiMail']['Assegnazione'] = $datiAssegnazione;
        if ($this->emlInfocamere) {
            $_POST['datiMail']['FILENAME'] = $this->emlInfocamere;
        }
        $_POST['tipoReg'] = 'consulta';
        $objModel = itaModel::getInstance($this->returnModel);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();

        if ($close)
            $this->close();
    }

    public function GetDatiPratica($Indice) {
        $this->allegati = array();
        $this->proric_rec = $this->praLib->GetProric($Indice, 'rowid');
        $this->allegati = $this->praLib->GetAllegatiPratica($this->proric_rec[
                'RICNUM']);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Carica');
        Out::hide($this->nameForm .
                '_Torna');
    }

    public function CaricaRichieste() {
        $sql = $this->CreaSql();
        try {
            $ita_grid01 = new TableView($this->gridCtrRichieste, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            $ita_grid01->setSortIndex('RICNUM');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $ita_grid01->getDataArray();
            $Result_tab = $this->elaboraRecord($Result_tab);
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                $this->Nascondi();
                Out::msgStop("Selezione", "Nessun record trovato.");
                Out::hide($this->divGes, '');
            } else {   // Visualizzo la ricerca
                Out::hide($this->divGes, '');
                Out::hide($this->nameForm . "_buttonBar", '');
                Out::show($this->divRis, '');
                $this->Nascondi();
                TableView::enableEvents($this->gridCtrRichieste);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage())

            ;
        }
    }

    public function CreaSql() {
//        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//        $whereVisibilta = '';
//        if ($retVisibilta ['SPORTELLO'] != 0) {
//            $whereVisibilta .= " AND RICTSP = " . $retVisibilta['SPORTELLO'];
//        }
//
//        if ($retVisibilta ['AGGREGATO'] != 0) {
//            $whereVisibilta .= " AND RICSPA = " . $retVisibilta['AGGREGATO'];
//        }
        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();

        $sql = "SELECT
            PRORIC.RICNUM AS RICNUM,
            PRORIC.ROWID AS ROWID,
            PRORIC.RICRES AS RICRES,
            PRORIC.RICTIM AS RICTIM,
            PRORIC.RICSPA AS RICSPA,
            PRORIC.RICDAT AS RICDAT,
            PRORIC.RICSTA AS RICSTA,
            PRORIC.RICRPA AS RICRPA,
            ANAPRA.PRADES__1 AS PRADES__1,
            PRORIC.RICSTT AS RICSTT,
            PRORIC.RICATT AS RICATT,
            PRORIC.PROPAK AS PROPAK,
            PRORIC.RICPC AS RICPC,
            PRORIC.RICDRE AS RICDRE," .
                $this->PRAM_DB->strConcat("RICCOG", "' '", "RICNOM") . " AS INTESTATARIO,
            PROGES.GESPRA AS GESPRA,
            PROPAS.PRORIN AS PRORIN
            FROM PRORIC PRORIC
               LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
               LEFT OUTER JOIN PROGES PROGES ON RICNUM=PROGES.GESPRA
               LEFT OUTER JOIN PROPAS PROPAS ON RICNUM=PROPAS.PRORIN
            WHERE (RICSTA = 01 OR RICSTA = 91)
            AND RICRUN = ''
            AND PROGES.GESPRA IS NULL
            AND PROPAS.PRORIN IS NULL" . $whereVisibilita;

        return $sql;
    }

    public function Dettaglio($Indice) {
        $this->GetDatiPratica($Indice);
        $Proric_rec = $this->proric_rec;
        if ($ditta == '') {
            $ditta = App::$utente->getKey('ditta');
        }
        $pathAllegatiRichieste = $this->praLib->getPathAllegatiRichieste();
        $bodyResponsabile = $pathAllegatiRichieste . "attachments/" . $Proric_rec['RICNUM'] . "/body.txt";
        Out::html($this->nameForm . '_divSoggetto', file_get_contents($bodyResponsabile));
        $open_Info = 'Oggetto: ' . $Proric_rec ['RICNUM'] . " " . $Proric_rec['RICDAT'];
        $this->openRecord($this->PRAM_DB, 'PRORIC', $open_Info);
        Out::valori($Proric_rec, $this->nameForm . '_PRORIC');
        Out::valore($this->nameForm . "_Numero_procedimento", substr($Proric_rec ['RICNUM'], 4) . "/" . substr($Proric_rec['RICNUM'], 0, 4));
        $this->Nascondi();

        Out::show($this->nameForm . '_Carica');
        if ($this->daPortlet != 'true') {
            Out::show($this->nameForm . '_Torna');
        } Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::show($this->nameForm . "_buttonBar", '');
        Out::setFocus('', $this->nameForm . '_ANAUNI[UNISET]');
        TableView::disableEvents($this->gridCtrRichieste);
        if ($this->allegatiInfocamere) {
            $allegatiTabella = array_merge($this->allegati, $this->allegatiInfocamere);
        } else {
            $allegatiTabella = $this->allegati;
        }

        $this->CaricaGriglia($this->nameForm . "_gridAllegati", $allegatiTabella);

        $tabelle = $this->PRAM_DB->listTables();
        foreach ($tabelle as $tabella) {
            if ($tabella == 'PRAMAIL') {
                //TODO: da riattivare quando sarà definitivo
                //Out::hide($this->nameForm . '_Carica');
            }
        }
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $color = "";
            if ($Result_rec ['RICSTA'] != "91" && $Result_rec ['RICRPA'] == "" && $Result_rec['PROPAK'] == "") {
                $Result_tab[$key]["NUMERO"] = substr($Result_rec ['RICNUM'], 4) . "/" . substr($Result_rec['RICNUM'], 0, 4);
                if ($Result_rec ['RICDAT'] != "" && $Result_rec['RICTIM'] != "") {
                    $Result_tab[$key]["RICEZ"] = substr($Result_rec ['RICDAT'], 6, 2) . "/" . substr($Result_rec ['RICDAT'], 4, 2) . "/" . substr($Result_rec ['RICDAT'], 0, 4) . " (" . $Result_rec['RICTIM'] . ")";
                } else {
                    $Result_tab[$key]["RICEZ"] = "";
                }
                if ($Result_rec['RICSPA'] != 0) {
                    $Anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
                    $Result_tab[$key]["SPORTELLO_AGGREGATO"] = $Anaspa_rec['SPADES'];
                }
            } else {
                if ($Result_rec['RICSTA'] == "91") {
                    $color = "red";
                } else if ($Result_rec['RICRPA']) {
                    $color = "blue";
                    if ($Result_rec['RICPC'] == "1") {
                        $color = "navy";
                    }
                } else if ($Result_rec['PROPAK']) {
                    $color = "green";
                }
                $Result_tab[$key]["NUMERO"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec ['RICNUM'], 4) . "/" . substr($Result_rec ['RICNUM'], 0, 4) . "</span>";
                $Result_tab[$key]["RICDRE"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec ['RICDRE'], 6, 2) . "/" . substr($Result_rec ['RICDRE'], 4, 2) . "/" . substr($Result_rec ['RICDRE'], 0, 4) . "</span>";
                if ($Result_rec ['RICDAT'] != "" && $Result_rec['RICTIM'] != "") {
                    $Result_tab[$key]["RICEZ"] = "<span style=\"color:$color;font-weight:bold;\">" . substr($Result_rec ['RICDAT'], 6, 2) . "/" . substr($Result_rec ['RICDAT'], 4, 2) . "/" . substr($Result_rec ['RICDAT'], 0, 4) . " (" . $Result_rec ['RICTIM'] . ")" . "</span>";
                } else {
                    $Result_tab[$key]["RICEZ"] = "";
                }
                $Result_tab[$key]["INTESTATARIO"] = "<span style=\"color:$color;font-weight:bold;\">" . $Result_rec ['INTESTATARIO'] . "</span>";
                if ($Result_rec['RICSPA'] != 0) {
                    $Anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
                    $Result_tab[$key]["SPORTELLO_AGGREGATO"] = "<span style=\"color:$color;font-weight:bold;\">" . $Anaspa_rec['SPADES'] . "</span>";
                } else {
                    $Result_tab[$key]["SPORTELLO_AGGREGATO"] = "";
                }
                $Result_tab[$key]["PRADES__1"] = "<span style=\"color:$color;font-weight:bold;\">" . $Result_rec ['PRADES__1'] . "</span>";
            }

            $Anaset_rec = $this->praLib->GetAnaset($Result_rec['RICSTT']);
            $Anaatt_rec = $this->praLib->GetAnaatt($Result_rec['RICATT']);
            $Result_tab[$key]["SETTORE"] = $Anaset_rec['SETDES'];
            $Result_tab[$key]["ATTIVITA"] = $Anaatt_rec['ATTDES'];
            $Ricdag_tab = ItaDB:: DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $Result_rec['RICNUM'] . "'
                                  AND DAGTIP<>'' AND (DAGTIP = 'DenominazioneImpresa' OR DAGTIP = 'Codfis_InsProduttivo')
                                  OR (DAGNUM='" . $Result_rec['RICNUM'] . "' AND DAGKEY LIKE 'ESIBENTE_%')", true);

            $Result_tab[$key]["IMPRESA"] = "";
            $Result_tab[$key]["FISCALE"] = "";
            $Result_tab[$key]["CMSUSER"] = "";
            $Result_tab[$key]["TELEFONO"] = "";
            if ($Ricdag_tab) {
                foreach ($Ricdag_tab as $Ricdag_rec) {
                    if ($Ricdag_rec['DAGTIP'] == "DenominazioneImpresa")
                        $Result_tab[$key]["IMPRESA"] = $Ricdag_rec['RICDAT'];
                    if ($Ricdag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                        $Result_tab[$key]["FISCALE"] = $Ricdag_rec['RICDAT'];
                    if ($Ricdag_rec['DAGKEY'] == "ESIBENTE_CMSUSER")
                        $Result_tab[$key]["CMSUSER"] = $Ricdag_rec['DAGVAL'];
                    if ($Ricdag_rec['DAGKEY'] == "ESIBENTE_TELEFONO")
                        $Result_tab[$key]["TELEFONO"] = $Ricdag_rec['DAGVAL'];
                }
            }
        }
        return

                $Result_tab;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1') {
        $ita_grid01 = new TableView($_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx'));
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($_appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

}
