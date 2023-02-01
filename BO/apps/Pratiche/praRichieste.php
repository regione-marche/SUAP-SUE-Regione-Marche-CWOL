<?php

/**
 *
 * Gestione delle richieste on-line
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    03.04.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEstrazione.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRiservato.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAcl.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';

function praRichieste() {
    $praRichieste = new praRichieste();
    $praRichieste->parseEvent();
    return;
}

class praRichieste extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praLibAcl;
    public $praLibEstrazione;
    public $arrExpr = array();
    public $utiEnte;
    public $passi;
    public $datiAgg;
    public $allegati;
    public $currPranum;
    public $currRicnum;
    public $rowidAppoggio;
    public $nameForm = "praRichieste";
    public $divGes = "praRichieste_divGestione";
    public $divRis = "praRichieste_divRisultato";
    public $divRic = "praRichieste_divRicerca";
    public $gridRichieste = "praRichieste_gridRichieste";
    public $gridRichiesteAccorpate = "praRichieste_gridRichiesteAccorpate";
    public $gridPassi = "praRichieste_gridPassi";
    public $gridDati = "praRichieste_gridDati";
    public $gridAllegati = "praRichieste_gridAllegati";
    public $gridAcl = "praRichieste_gridAcl";
    public $praAggiuntiviSel;
    public $altriDati = array();
    public $eqAudit;
    public $acl = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->praLibAcl = new praLibAcl();
            $this->praLibEstrazione = new praLibEstrazione();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->passi = App::$utente->getKey($this->nameForm . '_passi');
        $this->datiAgg = App::$utente->getKey($this->nameForm . '_datiAgg');
        $this->arrExpr = App::$utente->getKey($this->nameForm . '_arrExpr');
        $this->currPranum = App::$utente->getKey($this->nameForm . '_currPranum');
        $this->currRicnum = App::$utente->getKey($this->nameForm . '_currRicnum');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
        $this->praAggiuntiviSel = App::$utente->getKey($this->nameForm . '_praAggiuntiviSel');
        $this->altriDati = App::$utente->getKey($this->nameForm . '_altriDati');
        $this->acl = App::$utente->getKey($this->nameForm . '_acl');
        $this->eqAudit = new eqAudit();
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_passi', $this->passi);
        App::$utente->setKey($this->nameForm . '_datiAgg', $this->datiAgg);
        App::$utente->setKey($this->nameForm . '_currPranum', $this->currPranum);
        App::$utente->setKey($this->nameForm . '_currRicnum', $this->currRicnum);
        App::$utente->setKey($this->nameForm . '_arrExpr', $this->arrExpr);
        App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
        App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
        App::$utente->setKey($this->nameForm . '_praAggiuntiviSel', $this->praAggiuntiviSel);
        App::$utente->setKey($this->nameForm . '_altriDati', $this->altriDati);
        App::$utente->setKey($this->nameForm . '_acl', $this->acl);
    }

    public function parseEvent() {
        parent::parseEvent();
        
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PRORIC[RICSTA]':
                        
                        if ($_POST[$this->nameForm . '_PRORIC']['RICSTA'] == 99){
                            //$this->visualizzaScadenza($_POST[$this->nameForm . '_PRORIC']['ROWID'] , true);
                            $this->visualizzaScadenza($_POST[$this->nameForm . '_PRORIC']['RICSTA'],
                                    $_POST[$this->nameForm . '_PRORIC']['RICDRE'], 
                                    $_POST[$this->nameForm . '_PRORIC']['RICTSP'], true);
                        }
                        else {
                            //$this->visualizzaScadenza($_POST[$this->nameForm . '_PRORIC']['ROWID'], false);
                            $this->visualizzaScadenza($_POST[$this->nameForm . '_PRORIC']['RICSTA'],
                                    $_POST[$this->nameForm . '_PRORIC']['RICDRE'], 
                                    $_POST[$this->nameForm . '_PRORIC']['RICTSP'], false);
                            
                        }
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRichieste:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridPassi:
                        $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                        $model = 'praPassoRich';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['page'] = $this->page;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoRich';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridAllegati:
                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $destinazione = $destinazione . "attachments/" . $this->currRicnum;
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }

                        if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                            Out::openDocument(utiDownload::getUrl(
                                            $this->allegati[$_POST['rowid']]['DOCNAME'], $destinazione . "/" . $this->allegati[$_POST['rowid']]['DOCUPL']
                                    )
                            );
                        }

                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $model = 'praPassoRich';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $this->currPranum;
                        $_POST['pratica'] = $this->currRicnum;
                        $_POST['modo'] = "add";
                        $_POST['page'] = $this->page;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoRich';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il passo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                }
                break;
            case 'exportTableToExcel':
                $this->praAggiuntiviSel = "";
                Out::msgQuestion("Estrazione Excel", "Scegli il tipo di Estrazione", array(
                    'Inserisci Campi Aggiuntivi' => array('id' => $this->nameForm . '_XlsInsertCampi', 'model' => $this->nameForm),
                    'Default' => array('id' => $this->nameForm . '_XlsDefault', 'model' => $this->nameForm)
                        )
                );
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRichieste:
                        TableView::clearGrid($this->gridRichieste);
                        $ordinamento = $_POST['sidx'];
                        if ($ordinamento == 'PRATICA') {
                            $ordinamento = 'RICNUM';
                        }
                        if ($ordinamento == 'PROCEDIMENTO') {
                            $ordinamento = 'RICPRO';
                        }
                        if ($ordinamento == 'INIZIO') {
                            $ordinamento = 'RICDRE';
                        }
                        if ($ordinamento == 'INOLTRO') {
                            $ordinamento = 'RICDAT';
                        }
                        if ($ordinamento == 'DIFFERITA') {
                            $ordinamento = 'RICDATARPROT';
                        }
                        if ($ordinamento == 'AGGREGATO') {
                            $ordinamento = 'RICSPA';
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
                        if (!$ita_grid01->getDataPageFromArray('json', $Result_tab) && $_POST['_search'] !== 'true') {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        }

                        foreach ($ita_grid01->getDataArray() as $proric_rec) {
                            if ($proric_rec['RICSTA'] == "91") {
                                TableView::setCellValue($this->gridRichieste, $proric_rec['ROWID'], "STATO", "", "{'background-color':'blue'}", "", false);
                            }
                        }

                        break;

                    case $this->gridAllegati:
                        $this->caricaAllegati($this->currRicnum);
                        break;

                    case $this->gridDati:
                        //$this->CaricoCampiAggiuntivi($this->nameForm . "_PRORIC[RICPRO]", $this->currRicnum);
                        $this->CaricoCampiAggiuntivi($this->currPranum, $this->currRicnum);
                        break;

                    case $this->gridPassi:
                        $this->caricaPassi($this->currRicnum);
                        break;
                    case $this->gridAcl:
                        $this->caricaAcl($this->currRicnum);
                        break;

                    case $this->gridRichiesteAccorpate:
                        TableView::clearGrid($this->gridRichiesteAccorpate);

                        $ordinamento = $_POST['sidx'];

                        if ($ordinamento == 'PRATICA') {
                            $ordinamento = 'RICNUM';
                        }

                        if ($ordinamento == 'PROCEDIMENTO') {
                            $ordinamento = 'RICPRO';
                        }

                        $tableSortOrder = $_POST['sord'];
                        $sql = "SELECT * FROM PRORIC WHERE PRORIC.RICRUN = '" . $this->currRicnum . "'";
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $Result_tab = $ita_grid01->getDataArray();
                        $Result_tab = $this->elaboraRecord($Result_tab);
                        if (!$ita_grid01->getDataPageFromArray('json', $Result_tab) && $_POST['_search'] !== 'true') {
                            
                        }
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praRichieste', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $this->Elenca();
                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_Procedimento');
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $proges_rec = $this->praLib->GetProges($this->currRicnum, "richiesta");
                        if ($proges_rec) {
                            $data = substr($proges_rec['GESDRE'], 6, 2) . "/" . substr($proges_rec['GESDRE'], 4, 2) . "/" . substr($proges_rec['GESDRE'], 0, 4);
                            Out::msgStop("Attenzione!!!!", "Impossibile aggiornare la richiesta on-line n. $this->currRicnum perchè è stata già acquisita in data $data con n. pratica " . $proges_rec['GESNUM']);
                            break;
                        }

                        /*
                         * Verifico la modifica dello stato e se la pratica ha dei figli collegati (pratica unica)
                         */
                        $proric_rec_original = $this->praLib->GetProric($_POST[$this->nameForm . '_PRORIC']['ROWID'], 'rowid');
                        $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $proric_rec_original['RICNUM']);

                        if ($_POST[$this->nameForm . '_PRORIC']['RICSTA'] === '98' && $proric_rec_original['RICSTA'] !== $_POST[$this->nameForm . '_PRORIC']['RICSTA']) {
                            if (count($proric_tab_accorpate)) {
                                $descrizionePratica_tab = array_map(function($element) {
                                    return '- ' . substr($element['RICNUM'], 4, 6) . '/' . substr($element['RICNUM'], 0, 4) . ' <i>' . $element['PRADES'] . '</i>';
                                }, $proric_tab_accorpate);

                                Out::msgQuestion("ATTENZIONE!", "<br /><br />La Pratica che si sta modificando incorpora le seguenti Pratiche:<br /><br />" . implode('<br />', $descrizionePratica_tab) . "<br /><br /><span style='color: red; font-weight: bold;'>Annullando la Pratica, tutte le Pratiche in elenco verranno scollegate. Procedere?</span>", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                                break;
                            }

                            if ($proric_rec_original['RICRUN']) {
                                Out::msgQuestion("ATTENZIONE!", "<br /><br />La Pratica che si sta modificando è incorporata nella pratica <b>" . substr($proric_rec_original['RICRUN'], 4, 6) . '/' . substr($proric_rec_original['RICRUN'], 0, 4) . "</b>.<br /><span style='color: red; font-weight: bold;'>Annullandola, la Pratica verrà scollegata. Procedere?</span>", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                                break;
                            }
                        }
                    // NON INSERIRE ALTRI CASE QUI FRA L'AGGIORNA E IL CONFERMA AGGIORNA
                    case $this->nameForm . '_ConfermaAggiorna':
                        $giorni = $this->CalcolaGiorni($this->currRicnum);
                        Out::valore($this->nameForm . '_PRORIC[RICGIO]', $giorni);
                        $proric_rec = $_POST[$this->nameForm . '_PRORIC'];
                        $proric_rec['RICNUM'] = $this->currRicnum;

                        /*
                         * Verifico la presenza della variabile $proric_rec_original.
                         * Se è definita, significa che i controlli dell'evento _Aggiorna sono passati e non è necessario
                         * modificare nulla per quanto riguarda l'incorporazione delle Pratiche.
                         */
                        if (!isset($proric_rec_original)) {
                            $proric_rec_original = $this->praLib->GetProric($_POST[$this->nameForm . '_PRORIC']['ROWID'], 'rowid');
                            $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $proric_rec_original['RICNUM']);

                            /*
                             * Verifico che sia cambiato lo stato da "In corso"
                             */
                            if ($_POST[$this->nameForm . '_PRORIC']['RICSTA'] === '98' && $proric_rec_original['RICSTA'] !== $_POST[$this->nameForm . '_PRORIC']['RICSTA']) {
                                if (count($proric_tab_accorpate)) {
                                    foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                                        if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $proric_rec_accorpate['RICNUM'])) {
                                            Out::msgStop("Aggiornamento richiesta on-line", "Errore nello scollegamento della Pratica " . substr($proric_rec_accorpate['RICNUM'], 4, 6) . '/' . substr($proric_rec_accorpate['RICNUM'], 0, 4));
                                            break;
                                        }
                                    }
                                }

                                if ($proric_rec_original['RICRUN']) {
                                    $this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $proric_rec_original['RICNUM']);
                                }
                            }
                        }

                        /*
                         * Effettuo l'unset dei dati di acquisizione in quanto
                         * gestiti dal pulsante apposito "Aggiorna Dati Acquisizione".
                         */
                        unset($proric_rec['RICCONFDATA']);
                        unset($proric_rec['RICCONFORA']);
                        unset($proric_rec['RICCONFUTE']);
                        unset($proric_rec['RICCONFCONTEXT']);
                        unset($proric_rec['RICCONFINFO']);

                        $update_Info = "Oggetto: Aggiornamento richiesta on line n. $this->currRicnum";
                        if (!$this->updateRecord($this->PRAM_DB, 'PRORIC', $proric_rec, $update_Info)) {
                            Out::msgStop("Aggiornamento richiesta on-line", "Errore nell'aggiornamento della richiesta $this->currRicnum");
                            break;
                        }

                        $this->Dettaglio($proric_rec['ROWID']);
                        Out::msgInfo("Aggiornamento richiesta on-line", "Richiesta n. $this->currRicnum aggiornata correttamente");
                        break;

                    case $this->nameForm . '_AggiornaDatiAcquisizione':
                        $this->praLib->GetMsgInputPassword($this->nameForm, 'Aggiornamento dati acquisizione richiesta on-line', 'AggiornaDatiAcquisizione');
                        break;

                    case $this->nameForm . '_returnPasswordAggiornaDatiAcquisizione':
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }

                        $proric_rec = array();
                        $proric_rec['RICNUM'] = $this->currRicnum;
                        $proric_rec['ROWID'] = $_POST[$this->nameForm . '_PRORIC']['ROWID'];
                        $proric_rec['RICCONFDATA'] = $_POST[$this->nameForm . '_PRORIC']['RICCONFDATA'];
                        $proric_rec['RICCONFORA'] = $_POST[$this->nameForm . '_PRORIC']['RICCONFORA'];
                        $proric_rec['RICCONFUTE'] = $_POST[$this->nameForm . '_PRORIC']['RICCONFUTE'];
                        $proric_rec['RICCONFCONTEXT'] = $_POST[$this->nameForm . '_PRORIC']['RICCONFCONTEXT'];
                        $proric_rec['RICCONFINFO'] = $_POST[$this->nameForm . '_PRORIC']['RICCONFINFO'];

                        $update_Info = "Oggetto: Aggiornamento dati acquisizione richiesta on line n. $this->currRicnum";
                        if (!$this->updateRecord($this->PRAM_DB, 'PRORIC', $proric_rec, $update_Info)) {
                            Out::msgStop("Aggiornamento dati acquisizione richiesta on-line", "Errore nell'aggiornamento dei dati di acquisizione della richiesta $this->currRicnum");
                            break;
                        }

                        $this->Dettaglio($proric_rec['ROWID']);
                        Out::msgInfo("Aggiornamento dati acquisizione richiesta on-line", "Dati acquisizione richiesta n. $this->currRicnum aggiornati correttamente.");
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        $this->AzzeraVariabili();
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Bttn_log':

                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $destinazione = $destinazione . "log/";
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl(
                                        'errorLog.txt', $destinazione . 'errorLog.txt', true
                                )
                        );
                        break;

                    case $this->nameForm . '_bttn_Ricevuta':

                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $destinazione = $destinazione . "attachments/" . $this->currRicnum . "/";
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl(
                                        'body.html', $destinazione . 'body.html', true
                                )
                        );
                        break;
                    case $this->nameForm . '_bttn_Infocamere':
                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $codicepraticasw = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CODICEPRATICASW FROM PRORIC WHERE RICNUM = " . $this->currRicnum, false);
                        if (!$codicepraticasw['CODICEPRATICASW']) {
                            Out::msgInfo("ATTENZIONE", "File non trovato");
                            break;
                        }
                        $destinazione = $destinazione . "attachments/" . $this->currRicnum . "/" . $codicepraticasw['CODICEPRATICASW'] . "/";
                        //Out::msgInfo("ciao",$destinazione);
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl(
                                        $codicepraticasw['CODICEPRATICASW'] . '.SUAP.XML', $destinazione . $codicepraticasw['CODICEPRATICASW'] . '.SUAP.XML', true
                                )
                        );
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::show($this->nameForm . '_Utilita');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridRichieste);
                        break;
                    case $this->nameForm . '_ConfermaCancPasso':
                        $ricite_rec = $this->praLib->GetRicite($this->rowidAppoggio, 'rowid');
                        $this->deleteRecordRicdag($ricite_rec['ITEKEY'], $ricite_rec['RICNUM']);
                        $delete_Info = "Oggetto: Cancellazione Passo seq " . $ricite_rec['ITESEQ'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'RICITE', $this->rowidAppoggio, $delete_Info)) {
                            Out::msgStop("ATTENZIONE!", "Errore in cancellazione del Passo seq " . $ricite_rec['ITESEQ']);
                            break;
                        }
                        $this->praLib->ordinaPassiProcRich($this->currPranum, $this->currRicnum);
                        $this->passi = $this->caricaPassi($this->currRicnum);
                        if ($this->passi) {
                            $this->CaricaGriglia($this->gridPassi, $this->passi);
                            Out::show($this->gridPassi);
                        }
                        $giorni = $this->CalcolaGiorni($this->currRicnum);
                        Out::valore($this->nameForm . '_PRORIC[RICGIO]', $giorni);
                        break;
                    case $this->nameForm . '_ConfermaVis':
                        $model = 'praPassoRich';
                        $rowid = $_POST[$this->nameForm . '_AppoggioSI'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoRich';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Offline':
                        $rowid = $_POST[$this->nameForm . "_PRORIC"]['ROWID'];

                        //
                        //Controllo se la richiesta on-line è stata già acquisita
                        //
                        $proges_rec = $this->praLib->GetProges($this->currRicnum, "richiesta");
                        if ($proges_rec) {
                            $data = substr($proges_rec['GESDRE'], 6, 2) . "/" . substr($proges_rec['GESDRE'], 4, 2) . "/" . substr($proges_rec['GESDRE'], 0, 4);
                            Out::msgStop("Attenzione!!!!", "Impossibile rendere offline la richiesta n. $this->currRicnum perchè è stata già acquisita in data $data con n. pratica " . $proges_rec['GESNUM']);
                            break;
                        }

                        //
                        //Rileggo il numenro della richiesta
                        //
                        $proric_rec = $this->praLib->GetProric($rowid, "rowid");
                        if (!$proric_rec) {
                            Out::msgStop("Attenzione!!!", "Richiesta on-line non trovata");
                            break;
                        }

                        //
                        //Cambio lo stato e aggiorno
                        //
                        $proric_rec['RICSTA'] = "OF";
                        $update_Info = 'Oggetto : Stato Offline richiesta n. ' . $proric_rec['RICNUM'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PRORIC', $proric_rec, $update_Info)) {
                            break;
                        }

                        $this->Dettaglio($rowid);
                        $richiedente = "<b>" . $proric_rec['RICCOG'] . " " . $proric_rec['RICNOM'] . "</b>";
                        Out::msgInfo("", "La richiesta n. " . $proric_rec['RICNUM'] . " è stata resa <b>Offline</b>,<br>quindi non sarà più gestibile dal richiedente $richiedente<br>.Per renderla di nuovo diponibile cambiare stato");
                        break;
                    case $this->nameForm . '_ConfermaVin':
                        $model = 'praPassoRich';
                        $rowid = $_POST[$this->nameForm . '_AppoggioNO'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoRich';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediProtocollo':
                        $html = proIntegrazioni::VediProtocollo($this->currRicnum, "RICHIESTA");
                        if ($html['Status'] == "-1") {
                            Out::msgStop("Errore", $html['Message']);
                            break;
                        }
                        Out::msgInfo("Dati Protocollo", $html);
                        break;
                    case $this->nameForm . '_PRORIC[RICTSP]_butt':
                        praRic::praRicAnatsp($this->nameForm, "", "2");
                        break;
                    case $this->nameForm . '_PRORIC[RICSPA]_butt':
                        praRic::praRicAnaspa($this->nameForm, '', "2");
                        break;
                    case $this->nameForm . '_PRORIC[RICPRO]_butt':
                        praRic::praRicAnapra($this->nameForm, 'Ricerca Procedimenti', $this->nameForm . '_PRORIC[RICPRO]');
                        break;
                    case $this->nameForm . '_PRORIC[RICRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm);
                        break;
                    case $this->nameForm . '_PRORIC[RICEVE]_butt':
                        praRic::ricAnaeventi($this->nameForm);
                        break;
                    case $this->nameForm . "_XlsInsertCampi":
                        $model = "praDivAggiuntivi";
                        itaLib::embedModelComponent($model, $this->divRis, $this->nameForm);
                        break;
                    case $this->nameForm . "_ScegliCampi":
                        praRic::praRicItedag($this->nameForm);
                        break;
                    case $this->nameForm . "_ConfermaXLS":
                        $strCampi = trim($_POST[$this->nameForm . "_campi"]);
                        $arrCampi = explode(",", $strCampi);

                    case $this->nameForm . "_XlsDefault":
                        $sql = $this->CreaSql();
                        $Result_tab1 = $this->praLib->getGenericTab($sql);
                        $Result_tab2 = $this->elaboraRecordsXls($Result_tab1, $arrCa);
                        $ita_grid02 = new TableView($this->gridRichieste, array(
                            'arrayTable' => $Result_tab2));
                        $ita_grid02->setSortIndex('RICNUM');
                        $ita_grid02->setSortOrder('asc');
                        $ita_grid02->exportXLS('', 'richieste.xls');
                        break;
                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa($this->nameForm, "", "1");
                        break;
                    case $this->nameForm . '_Sportello_butt':
                        praRic:: praRicAnatsp($this->nameForm, "", "1");
                        break;
                    case $this->nameForm . '_CambioEsibente':
                        Out::msgInput(
                                'Dati Nuovo Esibente', array(
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Cognome  '),
                                'id' => $this->nameForm . '_esbCognome',
                                'name' => $this->nameForm . '_esbCognome',
                                'class' => "required",
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Nome  '),
                                'id' => $this->nameForm . '_esbNome',
                                'name' => $this->nameForm . '_esbNome',
                                'class' => "required",
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Cod. Fiscale  '),
                                'id' => $this->nameForm . '_esbFiscale',
                                'name' => $this->nameForm . '_esbFiscale',
                                'class' => "required",
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'P.E.C.  '),
                                'id' => $this->nameForm . '_esbPec',
                                'name' => $this->nameForm . '_esbPec',
                                'class' => "required",
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Via  '),
                                'id' => $this->nameForm . '_esbVia',
                                'name' => $this->nameForm . '_esbVia',
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Comune  '),
                                'id' => $this->nameForm . '_esbComune',
                                'name' => $this->nameForm . '_esbComune',
                                'size' => '30',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'CAP  '),
                                'id' => $this->nameForm . '_esbCap',
                                'name' => $this->nameForm . '_esbCap',
                                'size' => '30',
                                'maxlength' => '5',
                                'br' => true,
                            ),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Provincia  '),
                                'id' => $this->nameForm . '_esbProvincia',
                                'name' => $this->nameForm . '_esbProvincia',
                                'size' => '30',
                                'maxlength' => '2',
                                'br' => true,
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . "_cambioEsibente", 'model' => $this->nameForm, "class" => "ita-button-validate")
                                ), $this->nameForm
                        );

                        break;
                    case $this->nameForm . "_cambioEsibente":

                        /*
                         * Controllo conformità CF
                         */
                        $praLibElaborazioneDati = new praLibElaborazioneDati();
                        if (!$praLibElaborazioneDati->controlli('CodiceFiscalePiva', $this->formData[$this->nameForm . "_esbFiscale"])) {
                            Out::msgStop("Attenzione", "Codice Fiscale/Partita Iva non conformi.");
                            break;
                        }

                        /*
                         * Controllo conformità PEC
                         */
                        if (!$praLibElaborazioneDati->controlli('email', $this->formData[$this->nameForm . "_esbPec"])) {
                            Out::msgStop("Attenzione", "Indirizzo Pec non conforme.");
                            break;
                        }

                        /*
                         * Chiudo la dialog di raccolta dati
                         */
                        Out::closeCurrentDialog();

                        $proric_rec = $this->praLib->GetProric($this->currRicnum);
                        Out::msgQuestion("ATTENZIONE!", "Confermi il cambio dell'esisbente da <b>" . $proric_rec['RICCOG'] . " " . $proric_rec['RICCOG'] . "</b> a <b>" . $_POST[$this->nameForm . '_esbCognome'] . " " . $_POST[$this->nameForm . '_esbNome'] . "</b>?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCambioEsibente', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCambioEsibente', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . "_ConfermaCambioEsibente":

                        /*
                         * Leggo la Richiesta on-linee
                         */
                        $proric_rec = $this->praLib->GetProric($this->currRicnum);
                        if (!$proric_rec) {
                            Out::msgStop("Attenzione", "Richiesta on-line non trovata.");
                            break;
                        }
                        $oldCognome = $proric_rec['RICCOG'];
                        $oldNome = $proric_rec['RICNOM'];
                        $oldFiscale = $proric_rec['RICFIS'];

                        /*
                         * Cesso il vecchio esibente
                         */
                        $Ricsoggetti_tab = $this->praLib->GetRicsoggetti($this->currRicnum, 'ruolo', true, praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']);
                        if ($Ricsoggetti_tab) {
                            foreach ($Ricsoggetti_tab as $key => $Ricsoggetti_rec) {
                                if ($Ricsoggetti_rec['SOGRICDATA_FINE'] == '') {
                                    if (!$this->praLibAcl->cessaSoggetto($Ricsoggetti_rec, $proric_rec['ROWID'])) {
                                        Out::msgStop("ATTENZIONE!!", $this->praLibAcl->getErrMessage());
                                        break;
                                    }
                                }
                            }
                        }

                        $cognome = $this->formData[$this->nameForm . "_esbCognome"];
                        $nome = $this->formData[$this->nameForm . "_esbNome"];
                        $cf = $this->formData[$this->nameForm . "_esbFiscale"];
                        $mail = $this->formData[$this->nameForm . "_esbPec"];
                        $via = $this->formData[$this->nameForm . "_esbVia"];
                        $comune = $this->formData[$this->nameForm . "_esbComune"];
                        $cap = $this->formData[$this->nameForm . "_esbCap"];
                        $provincia = $this->formData[$this->nameForm . "_esbProvincia"];

                        /*
                         * Inserisco nuovo soggetto su RICSOGGETTI
                         */
                        $arraySoggetto = array(
                            'SOGRICNUM' => $proric_rec['RICNUM'],
                            'SOGRICUUID' => $proric_rec['RICUUID'],
                            'SOGRICFIS' => $cf,
                            'SOGRICDENOMINAZIONE' => $cognome . " " . $nome,
                            'SOGRICRUOLO' => praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'],
                            'SOGRICRICDATA_INIZIO' => date("Ymd"),
                            'SOGRICDATA_FINE' => '',
                            'SOGRICNOTE' => ''
                        );

                        if (!$this->praLibAcl->caricaSoggetto($arraySoggetto, $proric_rec['ROWID'])) {
                            Out::msgStop("ATTENZIONE!!", $this->praLibAcl->getErrMessage());
                            break;
                        }

                        /*
                         * Sincronizzo Dati Aggiuntivi provenienti dalla Form
                         */
                        $Ricdag_tab_Esibente = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$this->currRicnum' AND DAGKEY LIKE 'ESIBENTE%'", true);
                        foreach ($Ricdag_tab_Esibente as $key => $ricdag_rec) {
                            switch ($ricdag_rec['DAGKEY']) {
                                case 'ESIBENTE_CODICEFISCALE_CFI':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $cf;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $cf;
                                    break;
                                case 'ESIBENTE_COGNOME':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $cognome;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $cognome;
                                    break;
                                case 'ESIBENTE_NOME':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $nome;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $nome;
                                    break;
                                case 'ESIBENTE_PEC':
                                case 'ESIBENTE_EMAIL':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $mail;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $mail;
                                    break;
                                case 'ESIBENTE_RESIDENZAVIA':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $via;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $via;
                                    break;
                                case 'ESIBENTE_RESIDENZACOMUNE':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $comune;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $comune;
                                    break;
                                case 'ESIBENTE_RESIDENZACAP_CAP':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $cap;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $cap;
                                    break;
                                case 'ESIBENTE_RESIDENZAPROVINCIA_PV':
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = $provincia;
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = $provincia;
                                    break;
                                default :
                                    $Ricdag_tab_Esibente[$key]['RICDAT'] = '';
                                    $Ricdag_tab_Esibente[$key]['DAGVAL'] = '';
                            }
                        }

                        foreach ($Ricdag_tab_Esibente as $key => $ricdag_rec) {
                            $update_Info_ricddag = "Cambio Esibente: Sincronizzo Dato Aggiuntivo " . $ricdag_rec['DAGKEY'] . " richiesta $this->currRicnum da $oldCognome $oldNome($oldFiscale) a $cognome $nome($cf)";
                            if (!$this->updateRecord($this->PRAM_DB, 'RICDAG', $ricdag_rec, $update_Info_ricddag)) {
                                Out::msgStop("Attenzione", "Errore sincronizzazione Dati Aggiuntivi Nuovo Esibente richiesta n. $this->currRicnum: ");
                                break;
                            }
                        }

                        /*
                         * Sincronizzo PRORIC
                         */
                        $proric_rec['RICCOG'] = $cognome;
                        $proric_rec['RICNOM'] = $nome;
                        $proric_rec['RICFIS'] = $cf;
                        $proric_rec['RICEMA'] = $mail;
                        $proric_rec['RICVIA'] = $via;
                        $proric_rec['RICCOM'] = $comune;
                        $proric_rec['RICCAP'] = $cap;
                        $proric_rec['RICPRV'] = $provincia;
                        //
                        $update_Info = "Cambio Esibente: Sincronizzo PRORIC richiesta $this->currRicnum da $oldCognome $oldNome($oldFiscale) a $cognome $nome($cf)";
                        if (!$this->updateRecord($this->PRAM_DB, 'PRORIC', $proric_rec, $update_Info)) {
                            Out::msgStop("Attenzione", "Errore in aggiornamento del nuovo esibente.");
                            break;
                        }
                        Out::closeCurrentDialog();
                        Out::msgInfo("ATTENZIONE", "Esibente cambiato correttamente");
                        $this->Dettaglio($proric_rec['ROWID']);
                        break;
                    case $this->nameForm . "_Utilita":
                        $arrayAzioni = $this->GetArrayAzioni();
                        if ($arrayAzioni) {
                            Out::msgQuestion("Utilità", "", $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                        } else {
                            Out::msgInfo("Utilità", "Non ci sono azioni disponibili.");
                        }
                        break;
                    case $this->nameForm . '_Estrazione':
                        $this->praLibEstrazione->msgInputEstrazione($this->nameForm);
                        break;
                    case $this->nameForm . $this->praLibEstrazione->buttonStampa:
                        $this->praLibEstrazione->stampaEstrazioneRichieste($this->CreaSql(), $_POST[$this->nameForm . $this->praLibEstrazione->fieldPercentuale], $_POST[$this->nameForm . $this->praLibEstrazione->fieldOrdinamento]);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Pratica':
                        $codice = $_POST[$this->nameForm . '_Pratica'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Pratica', $codice);
                        }
                        break;
                    case $this->nameForm . '_Anno':
                        $codice = $_POST[$this->nameForm . '_Pratica'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if ($codice && $anno) {
                            $Proric_rec = $this->praLib->GetProric($anno . $codice);
                            if ($Proric_rec) {
                                $this->Dettaglio($Proric_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_Procedimento':
                        $codice = $_POST[$this->nameForm . '_Procedimento'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anapra_rec = $this->praLib->GetAnapra($codice);
                        Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_desc_proc', $anapra_rec['PRADES__1']);
                    case $this->nameForm . '_PRORIC[RICTSP]':
                        $codice = $_POST[$this->nameForm . '_PRORIC']['RICTSP'];
                        if ($codice) {
                            $this->DecodAnatsp($codice, "2");
                        } else {
                            Out::valore($this->nameForm . '_ANATSP[TSPDES]', "");
                        }
                        break;
                    case $this->nameForm . '_PRORIC[RICSPA]':
                        $codice = $_POST[$this->nameForm . '_PRORIC']['RICSPA'];
                        if ($codice) {
                            $this->DecodAnaspa($codice, "2");
                        } else {
                            Out::valore($this->nameForm . '_ANASPA[SPADES]', "");
                        }
                        break;
                    case $this->nameForm . '_PRORIC[RICRES]':
                        $codice = $_POST[$this->nameForm . '_PRORIC']['RICRES'];
                        if ($codice) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodResponsabile($codice);
                        }
                        break;
                    case $this->nameForm . '_PRORIC[RICEVE]':
                        $codice = $_POST[$this->nameForm . '_PRORIC']['RICEVE'];
                        if ($codice) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnaeventi($codice, "codice", true);
                        }
                        break;
                    case $this->nameForm . '_Sportello':
                        $codice = $_POST[$this->nameForm . '_Sportello'];
                        if ($codice) {
                            $this->DecodAnatsp($codice, "1");
                        } else {
                            Out::valore($this->nameForm . '_Desc_spor', "");
                        }
                        break;
                    case $this->nameForm . '_Aggregato':
                        $codice = $_POST[$this->nameForm . '_Aggregato'];
                        if ($codice) {
                            $this->DecodAnaspa($codice, "1");
                        } else {
                            Out::valore($this->nameForm . '_Desc_aggr', "");
                        }
                        break;
                    case $this->nameForm . '_FascicoloRemoto':
                        $codice = $_POST[$this->nameForm . '_FascicoloRemoto'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_FascicoloRemoto', $codice);
                        }
                        break;
                    case $this->nameForm . '_AnnoRemoto':
                        $codice = $_POST[$this->nameForm . '_FascicoloRemoto'];
                        $anno = $_POST[$this->nameForm . '_AnnoRemoto'];
                        if ($codice && $anno) {
                            $Proric_rec = $this->praLib->GetProric($anno . $codice, "fascicoloRemoto");
                            if ($Proric_rec) {
                                $this->Dettaglio($Proric_rec['ROWID']);
                            }
                        }
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $mess = "";
                        $arrayMsg = array('F8-Annulla' => array('id' => $this->nameForm . '_AnnullaVis', 'model' => $this->nameForm, 'shortCut' => "f8"));
                        $ricite_rec = $this->praLib->GetRicite($_POST['rowid'], 'rowid');
                        if ($ricite_rec['ITEVPA']) {
                            $Ricite_vaiSI = $this->praLib->GetRicite($ricite_rec['ITEVPA'], 'itekey', false, '', $ricite_rec['RICNUM']);
                            Out::valore($this->nameForm . '_AppoggioSI', $Ricite_vaiSI['ROWID']);
                            $mess .= "Il passo di destinazione (risposta SI) ha sequenza: " . $Ricite_vaiSI['ITESEQ'] . " - ";
                            $mess .= $Ricite_vaiSI['ITEDES'] . "<br>";
                            $arrayMsg['F5-Vai al passo SI'] = array('id' => $this->nameForm . '_ConfermaVis', 'model' => $this->nameForm, 'shortCut' => "f5");
                        }
                        if ($ricite_rec['ITEVPN']) {
                            $Ricite_vaiNO = $this->praLib->GetRicite($ricite_rec['ITEVPN'], 'itekey', false, '', $ricite_rec['RICNUM']);
                            Out::valore($this->nameForm . '_AppoggioNO', $Ricite_vaiNO['ROWID']);
                            $mess .= "Il passo di destinazione (risposta NO) ha sequenza: " . $Ricite_vaiNO['ITESEQ'] . " - ";
                            $mess .= $Ricite_vaiNO['ITEDES'] . "<br>";
                            $arrayMsg['F6-Vai al passo NO'] = array('id' => $this->nameForm . '_ConfermaVin', 'model' => $this->nameForm, 'shortCut' => "f6");
                        }

                        Out::msgQuestion("Info Passo.", $mess, $arrayMsg);
                        break;

                    case $this->gridAllegati:
                        $allegato = $this->allegati[$_POST['rowid']];
                        $ext = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
                        if (strtolower($ext) != "p7m") {
                            break;
                        }
                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $filePath = $destinazione . "attachments/" . $this->currRicnum . "/" . $allegato['DOCUPL'];
                        $this->praLib->VisualizzaFirme($filePath, $allegato['DOCUPL']);
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {

                    case $this->gridAllegati:
                        $ricdoc_rec['DOCNAME'] = $_POST['value'];
                        $ricdoc_rec['ROWID'] = $this->allegati[$_POST['rowid']]['ROWID'];
                        if (!$this->updateRecord($this->PRAM_DB, 'RICDOC', $ricdoc_rec, $update_Info)) {
                            Out::msgStop("ERRORE ", "Non è stato possibile effettuare le modifiche alla Richiesta N " . $this->currRicnum);
                            break;
                        }
                        $this->eqAudit->logEqEvent($this, array(// Controllo modifiche 
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'DB' => $this->PRAM_DB,
                            'DSet' => 'RICDOC',
                            'Estremi' => "Modificato DOCNAME per la richiesta N " . $this->currRicnum . " rowid documento " . $ricdoc_rec['ROWID']
                        ));
                        Out::msgBlock($this->nameForm, 800, true, 'Campo Aggiornato');
                        break;

                    case $this->gridDati:
                        switch ($_POST['cellname']) {
                            case "DAGCTR":
                                $dagctr_rec['DAGCTR'] = $_POST['value'];
                                break;
                            case "DAGKEY":
                                $dagctr_rec['DAGKEY'] = $_POST['value'];
                                break;
                            case "RICDAT":
                                $dagctr_rec['RICDAT'] = $_POST['value'];
                                break;
                        }
                        $dagctr_rec['ROWID'] = $this->altriDati[$_POST['rowid']]['ROWID'];
                        if (!$this->updateRecord($this->PRAM_DB, 'RICDAG', $dagctr_rec, $update_Info)) {
                            Out::msgStop("ERRORE ", "Non è stato possibile effettuare le modifiche alla Richiesta N " . $this->currRicnum);
                            break;
                        }
                        $this->eqAudit->logEqEvent($this, array(// Controllo modifiche 
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'DB' => $this->PRAM_DB,
                            'DSet' => 'RICDAG',
                            'Estremi' => "Modificato " . $_POST['cellname'] . " per la richiesta N " . $this->currRicnum . " rowid " . $dagctr_rec['ROWID']
                        ));
                        Out::msgBlock($this->nameForm, 800, true, 'Campo Aggiornato');
                        break;
                    case $this->gridAcl:
                        $acl_rec = $this->acl[$_POST['rowid']];
                        //Out::msgInfo("OPST", print_r($_POST, true));
                        //Out::msgInfo("acl rec", print_r($acl_rec, true));
                        $ricacl_rec = $this->praLib->GetRicacl($acl_rec['ROW_ID']);
                        if (!$ricacl_rec) {
                            Out::msgStop("Attenzione!!!", "Record ACL non trovato.");
                            break;
                        }


                        $fl_aggiorna = true;
                        switch ($_POST['cellname']) {
                            case "RICACLTRASHED":
                                $Valore = $_POST['value'];
                                break;
                            case "RICACLDATA_INIZIO":
                            case "RICACLDATA_FINE":
                                if ($_POST['value']) {
                                    // CONTROLLO IL FORMATO DATA
                                    $Data = explode("/", $_POST['value']);
                                    $Valore = $Data[2] . $Data[1] . $Data[0];
                                    if (count($Data) < 3 || strlen($Valore) != 8) {
                                        Out::msgInfo('Attenzione', "La data deve essere formato: GG/MM/AAAA");
                                        $fl_aggiorna = false;
                                        break;
                                    }
                                } else {
                                    $fl_aggiorna = false;
                                }
                                break;
                        }


                        $ricacl_rec[$_POST['cellname']] = trim($Valore);
                        $update_Info = "Oggetto: Aggiorno campo " . $_POST['cellname'] . " dalla griglia";
                        if ($fl_aggiorna) {
                            if (!$this->updateRecord($this->PRAM_DB, 'RICACL', $ricacl_rec, $update_Info)) {
                                Out::msgStop("ERRORE ", "Non è stato possibile effettuare le modifiche alla Richiesta N " . $this->currRicnum);
                                break;
                            }
                            Out::msgBlock($this->nameForm, 1500, true, "Campo " . $_POST['cellname'] . " Aggiornato");
                        }
                        $this->caricaAcl($this->currRicnum);
                        break;
                }
                break;

            case 'returnAnapra':
                $anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ID_ANAPRA'], 'rowid');
                Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_desc_proc', $anapra_rec['PRADES__1']);
                break;
            case 'returnPraPassoRich':
                $rigaSel = $_POST['selRow'];
                $this->passi = $this->caricaPassi($this->currRicnum);
                $giorni = $this->CalcolaGiorni($this->currRicnum);
                Out::valore($this->nameForm . '_PRORIC[RICGIO]', $giorni);
                Out::codice("jQuery('#$this->gridPassi').jqGrid('setSelection','$rigaSel');");
                break;
            case 'returnAnatsp2':
                $this->DecodAnatsp($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnAnaspa2':
                $this->DecodAnaspa($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodResponsabile($_POST['retKey'], 'rowid');
                break;
            case 'returnAnaeventi':
                $this->DecodAnaeventi($_POST['retKey'], 'rowid', true);
                break;
            case "returnAggiuntivi":
                $itedag_rec = $this->praLib->GetItedag($_POST['retKey'], "rowid");
                $this->praAggiuntiviSel .= $itedag_rec['ITDKEY'] . ",";
                Out::valore($this->nameForm . "_campi", $this->praAggiuntiviSel);
                break;
            case 'returnAnatsp1':
                $this->DecodAnatsp($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnAnaspa1':
                $this->DecodAnaspa($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_datiAgg');
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_currRicnum');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_praAggiuntiviSel');
        App::$utente->removeKey($this->nameForm . '_acl');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $anno = $_POST[$this->nameForm . '_Anno'];
        $daData = $_POST[$this->nameForm . '_Da_data'];
        $aData = $_POST[$this->nameForm . '_A_data'];
        $Fiscale = $_POST[$this->nameForm . '_Fiscale'];
        $Pratica = $_POST[$this->nameForm . '_Pratica'];
        $Procedimento = $_POST[$this->nameForm . '_Procedimento'];
        $Stato = $_POST[$this->nameForm . '_Stato'];
        $Sportello = $_POST[$this->nameForm . '_Sportello'];
        $Aggregato = $_POST[$this->nameForm . '_Aggregato'];
        $daOra = $_POST[$this->nameForm . '_Da_ora'];
        $aOra = $_POST[$this->nameForm . '_A_ora'];
        $Protocollo = $_POST[$this->nameForm . '_Protocollo'];
        $AnnoProt = $_POST[$this->nameForm . '_AnnoProt'];
        $Prot = $_POST[$this->nameForm . '_Protocollate'];
        $Prot_Diff = $_POST[$this->nameForm . '_Prot_Differita'];

        if ($anno) {
            if ($daData == "")
                $daData = $anno . "0101";
            if ($aData == "")
                $aData = $anno . "1231";
        }

        $sql = "SELECT
                ANAPRA.PRADES__1 AS PRADES__1,
                PRORIC.ROWID AS ROWID,
                PRORIC.RICDRE AS RICDRE,
                PRORIC.RICORE AS RICORE,
                PRORIC.RICSPA AS RICSPA,
                PRORIC.RICFIS AS RICFIS,
                PRORIC.RICNUM AS RICNUM,
                PRORIC.RICPRO AS RICPRO,
           	PRORIC.RICNPR AS NUM_PROTOCOLLO,
		PRORIC.RICDPR AS DATA_PROTOCOLLO,
                PRORIC.RICCOG AS RICCOG,
                PRORIC.RICNOM AS RICNOM,
                PRORIC.RICDAT AS RICDAT,
                PRORIC.RICTIM AS RICTIM,
                PRORIC.RICSTA AS RICSTA,
                PRORIC.RICEMA AS RICEMA,
                PRORIC.RICTSP AS RICTSP,
                PRORIC.RICDATARPROT AS RICDATARPROT,
                PRORIC.RICORARPROT AS RICORARPROT,
                PRORIC.RICERRRPROT AS RICERRRPROT
             FROM PRORIC PRORIC
                LEFT OUTER JOIN ANAPRA ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
             WHERE 1 ";

        if ($daData != "" && $aData != "") {
            $sql .= " AND (RICDRE BETWEEN '$daData' AND '$aData')";
        }
        if ($Fiscale) {
            $sql .= " AND RICFIS = '$Fiscale'";
        }
        if ($Pratica) {
            $Pratica = $anno . str_pad($Pratica, 6, 0, STR_PAD_RIGHT);
            $sql .= " AND RICNUM = '$Pratica'";
        }
        if ($Protocollo) {
            if (empty($AnnoProt)) {
                $sql .= " AND SUBSTR(RICNPR,5) = '$Protocollo'";
            } else {
                $Protocollo = $AnnoProt . $Protocollo;
                $sql .= " AND RICNPR = '$Protocollo'";
            }
        }
        if ($Prot && $Prot == 'N') {
            $sql .= " AND RICNPR = ''";
        }
        if ($Prot && $Prot == 'P') {
            $sql .= " AND RICNPR <> ''";
        }
        if ($Prot_Diff && $Prot_Diff != 1) {
            if ($Prot_Diff == 2) {
                $sql .= " AND RICDATARPROT <> ''";
            } else {
                $sql .= " AND RICORARPROT = '' AND RICDATARPROT = ''";
            }
        }
        if ($Procedimento) {
            $sql .= " AND RICPRO = '$Procedimento'";
        }
        if ($Stato) {
            if ($Stato == "PD") {
                $sql .= " AND RICSTA = '01' AND RICDATARPROT <> '' AND RICORARPROT <> ''";
            } else {
                $sql .= " AND RICSTA = '$Stato'";
            }
        }
        if ($Sportello) {
            $sql .= " AND RICTSP = '$Sportello'";
        }
        if ($Aggregato) {
            $sql .= " AND RICSPA = '$Aggregato'";
        }
        if ($daOra != "" && $aOra != "") {
            $sql .= " AND (RICTIM BETWEEN '$daOra' AND '$aOra')";
        }

        if ($_POST['_search'] === 'true') {
            $pratica = $_POST['PRATICA'];
            $ricdre = $_POST['RICDRE'];
            $ricore = $_POST['RICORE'];
            $ricpro = $_POST['RICPRO'];
            $procedimento = addslashes($_POST['PROCEDIMENTO']);
            $ricfis = addslashes($_POST['RICFIS']);
            $riccog = addslashes($_POST['RICCOG']);
            $ricnom = addslashes($_POST['RICNOM']);
            $ricdat = $_POST['RICDAT'];
            $rictim = $_POST['RICTIM'];
            $ricema = addslashes($_POST['RICEMA']);

            if ($procedimento) {
                $sql .= " AND " . $this->PRAM_DB->strUpper("PRADES__1") . " LIKE '%" . strtoupper($procedimento) . "%'";
            }

            if ($pratica) {
                $pratica = $anno . str_pad($pratica, 6, 0, STR_PAD_LEFT);
                $sql .= " AND RICNUM = '$pratica'";
            }

            if ($ricdre) {
                $sql .= " AND " . $this->PRAM_DB->strConcat($this->PRAM_DB->subString('RICDRE', 7, 2), "'/'", $this->PRAM_DB->subString('RICDRE', 5, 2), "'/'", $this->PRAM_DB->subString('RICDRE', 1, 4)) . " LIKE '%$ricdre%'";
            }

            if ($ricore) {
                $sql .= " AND RICORE LIKE '%$ricore%'";
            }

            if ($ricpro) {
                $sql .= " AND RICPRO LIKE '%$ricpro%'";
            }

            if ($ricfis) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICFIS') . " LIKE '%" . strtoupper($ricfis) . "%'";
            }

            if ($riccog) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICCOG') . " LIKE '%" . strtoupper($riccog) . "%'";
            }

            if ($ricnom) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICNOM') . " LIKE '%" . strtoupper($ricnom) . "%'";
            }

            if ($ricdat) {
                $sql .= " AND " . $this->PRAM_DB->strConcat($this->PRAM_DB->subString('RICDAT', 7, 2), "'/'", $this->PRAM_DB->subString('RICDAT', 5, 2), "'/'", $this->PRAM_DB->subString('RICDAT', 1, 4)) . " LIKE '%$ricdat%'";
            }

            if ($rictim) {
                $sql .= " AND RICTIM LIKE '%$rictim%'";
            }

            if ($ricema) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICEMA') . " LIKE '%" . strtoupper($ricema) . "%'";
            }
        }
        // Out::msginfo("sql", $sql);
        return $sql;
    }

    function DecodAnatsp($Codice, $retid, $tipoRic = 'codice') {
        $anatsp_rec = $this->praLib->GetAnatsp($Codice, $tipoRic);
        switch ($retid) {
            case "1":
                Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPCOD']);
                Out::valore($this->nameForm . '_Desc_spor', $anatsp_rec['TSPDES']);
                break;
            case "2":
                Out::valore($this->nameForm . '_PRORIC[RICTSP]', $anatsp_rec['TSPCOD']);
                Out::valore($this->nameForm . '_ANATSP[TSPDES]', $anatsp_rec['TSPDES']);
                break;
        }
        return $anatsp_rec;
    }

    function DecodAnaspa($Codice, $retid, $tipoRic = 'codice') {
        $anaspa_rec = $this->praLib->GetAnaspa($Codice, $tipoRic);
        switch ($retid) {
            case "1":
                Out::valore($this->nameForm . '_Aggregato', $anaspa_rec['SPACOD']);
                Out::valore($this->nameForm . '_Desc_aggr', $anaspa_rec['SPADES']);
                break;
            case "2":
                Out::valore($this->nameForm . '_PRORIC[RICSPA]', $anaspa_rec['SPACOD']);
                Out::valore($this->nameForm . '_ANASPA[SPADES]', $anaspa_rec['SPADES']);
                break;
        }
        return $anaspa_rec;
    }

    public function Dettaglio($Indice) {
        //
        //Decodifica richiesta on-line
        //
        $proric_rec = $this->praLib->GetProric($Indice, 'rowid');
        $this->currRicnum = $proric_rec['RICNUM'];
        $this->currPranum = $proric_rec['RICPRO'];
        $this->Nascondi();
        $open_Info = 'Oggetto: ' . $proric_rec['RICNUM'];
        $this->openRecord($this->PRAM_DB, 'PRORIC', $open_Info);
        Out::valori($proric_rec, $this->nameForm . '_PRORIC');
        Out::valore($this->nameForm . '_Numero_pratica', substr($proric_rec['RICNUM'], 4, 6) . " / " . substr($proric_rec['RICNUM'], 0, 4));

        //
        //Decodifica Protocollo
        //
        Out::valore($this->nameForm . '_NUMPROT', "");
        Out::valore($this->nameForm . '_DATAPROT', "");
        if ($proric_rec['RICNPR'] != 0) {
            Out::valore($this->nameForm . '_NUMPROT', substr($proric_rec['RICNPR'], 4) . " / " . substr($proric_rec['RICNPR'], 0, 4));
            Out::valore($this->nameForm . '_DATAPROT', $proric_rec['RICDPR']);
            Out::show($this->nameForm . '_VediProtocollo');
        }

        //
        //Decodifica Sportelli
        //
        $this->DecodAnatsp($proric_rec['RICTSP'], "2");
        $this->DecodAnaspa($proric_rec['RICSPA'], "2");

        //
        //Decodifica procedimento
        //
        //$this->DecodAnapra($proric_rec['RICPRO'], $this->nameForm . "_PRORIC[RICPRO]");
        $this->DecodAnapra($proric_rec, $this->nameForm . "_PRORIC[RICPRO]");

        $this->DecodAnaeventi($proric_rec['RICEVE'], 'codice', true);

        //
        //Carico i passi
        //
        //$this->praPassi = $this->caricaPassi($proric_rec['RICNUM']);
        $this->caricaPassi($proric_rec['RICNUM']);
        $this->caricaAllegati($proric_rec['RICNUM']);
        $this->CaricoCampiAggiuntivi($proric_rec['RICPRO'], $proric_rec['RICNUM']);
        $this->caricaAcl($proric_rec['RICNUM']);

        //
        //Decodifico il responsabile
        //
        $this->DecodResponsabile($proric_rec['RICRES']);

        /*
         * Richieste Accorpate
         */
        $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $proric_rec['RICNUM']);
        if (count($proric_tab_accorpate)) {
            Out::show($this->nameForm . '_divPraUnica');
            TableView::enableEvents($this->gridRichiesteAccorpate);
            TableView::reload($this->gridRichiesteAccorpate);
        } else {
            TableView::disableEvents($this->gridRichiesteAccorpate);
            Out::hide($this->nameForm . '_divPraUnica');
        }

        //
        //Blocco alcuni campi importatnti della richiesta on-line
        //
        Out::attributo($this->nameForm . '_PRORIC[RICDRE]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PRORIC[RICORE]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PRORIC[RICDAT]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PRORIC[RICTIM]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PRORIC[RICFIS]', 'readonly', '0');
        Out::attributo($this->nameForm . '_PRORIC[RICPRO]', 'readonly', '0');
        Out::hide($this->nameForm . '_PRORIC[RICPRO]_butt');
        Out::attributo($this->nameForm . '_NUMPROT', 'readonly', '0');
        Out::attributo($this->nameForm . '_DATAPROT', 'readonly', '0');

        if ($proric_rec['RICSTA'] != "OF") {
            Out::show($this->nameForm . '_Offline');
        }
        if ($proric_rec['RICSTA'] != "99" AND $proric_rec['RICSTA'] != "OF" AND $proric_rec['RICSTA'] != "98") {
            Out::show($this->nameForm . '_bttn_Ricevuta');
        }
        if ($proric_rec['RICSTA'] == "99" OR $proric_rec['RICSTA'] == "91") {
            Out::show($this->nameForm . '_bttn_Infocamere');
        }
        if ($proric_rec['RICSTA'] != "99" ) {
//            $this->visualizzaScadenza($Indice, false);  
            $this->visualizzaScadenza($proric_rec['RICSTA'], $proric_rec['RICDRE'], $proric_rec['RICTSP'], false);  
        }
        else {
            $this->visualizzaScadenza($proric_rec['RICSTA'], $proric_rec['RICDRE'], $proric_rec['RICTSP'], true);            
        }

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_Bttn_log');
        Out::show($this->nameForm . '_CambioEsibente');
        Out::show($this->nameForm . '_AggiornaDatiAcquisizione');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
    }

    function CalcolaGiorni($pratica) {
        $sql = "SELECT * FROM RICITE WHERE RICNUM = '" . $pratica . "'";
        $ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $giorni = 0;
        foreach ($ricite_tab as $ricite_rec) {
            $giorni += $ricite_rec['ITEGIO'];
        }
        return $giorni;
    }

    function Elenca() {
        if ($_POST['sql']) {
            $sql = $_POST['sql'];
        } else {
            $this->sql = $sql = $this->CreaSql();
        }
        Out::clearFields($this->divRis);
        Out::hide($this->divGes, '');
        Out::hide($this->divRic, '');
        Out::show($this->divRis, '');
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Utilita');
        Out::setFocus('', $this->nameForm . '_AltraRicerca');
        TableView::enableEvents($this->gridRichieste);
        TableView::reload($this->gridRichieste);
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "", "1", "Tutti");
        Out::select($this->nameForm . '_Stato', 1, "", "1", "Tutti");

        Out::select($this->nameForm . '_Protocollate', 1, "T", "0", "Tutte");
        Out::select($this->nameForm . '_Protocollate', 1, "P", "0", "Protocollate");
        Out::select($this->nameForm . '_Protocollate', 1, "N", "0", "Non protocollate");

        Out::select($this->nameForm . '_TipoInCorso', 1, "T", "0", "Tutte");
        Out::select($this->nameForm . '_TipoInCorso', 1, "A", "0", "Solo Attive");
        Out::select($this->nameForm . '_TipoInCorso', 1, "S", "0", "Solo Scadute");
        
        
        Out::select($this->nameForm . '_Prot_Differita', 1, "1", "0", "Tutte");
        Out::select($this->nameForm . '_Prot_Differita', 1, "2", "0", "Con richiesta protocollazione");
        Out::select($this->nameForm . '_Prot_Differita', 1, "3", "0", "Senza richiesta protocollazione");
        foreach (praLibRichiesta::$RICSTA_DESCR as $key => $value) {
            Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, $key, "0", $value);
            Out::select($this->nameForm . '_Stato', 1, $key, "0", $value);
        }
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "01", "0", "Inoltrata");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "02", "0", "Acquisita");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "03", "0", "Chiusa");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "91", "0", "Inviata ad Camera di Commercio");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "98", "0", "Annullata");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "99", "0", "In corso");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "OF", "0", "Offline");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "IM", "0", "Protocollazione in corso");
//        Out::select($this->nameForm . '_PRORIC[RICSTA]', 1, "AT", "0", "Acquisita da terze parti");
//
//        Out::select($this->nameForm . '_Stato', 1, "", "1", "Tutti");
//        Out::select($this->nameForm . '_Stato', 1, "01", "0", "Inoltrata");
//        Out::select($this->nameForm . '_Stato', 1, "02", "0", "Acquisita");
//        Out::select($this->nameForm . '_Stato', 1, "03", "0", "Chiusa");
//        Out::select($this->nameForm . '_Stato', 1, "91", "0", "Inviata ad Camera di Commercio");
//        Out::select($this->nameForm . '_Stato', 1, "98", "0", "Annullata");
//        Out::select($this->nameForm . '_Stato', 1, "99", "0", "In corso");
//        Out::select($this->nameForm . '_Stato', 1, "OF", "0", "Offline");
//        Out::select($this->nameForm . '_Stato', 1, "IM", "0", "Protocollazione in corso");
//        Out::select($this->nameForm . '_Stato', 1, "AT", "0", "Acquisita da terze parti");

        foreach (praLib::$TIPO_SEGNALAZIONE as $k => $v) {
            Out::select($this->nameForm . '_PRORIC[RICSEG]', '1', $k, '0', $v);
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1' || $_POST['page'] == 0) {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
            //$ita_grid01->setPageRows($pageRows);
        }

        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    public function deleteRecordRicdag($codice, $pratica) {
        $sql = "SELECT * FROM RICDAG WHERE DAGNUM = $pratica AND ITEKEY = '" . $codice . "'";
        $ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($ricdag_tab) {
            foreach ($ricdag_tab as $ricdag_rec) {
                $delete_Info = "Oggetto: Cancellazione dato aggiuntivo " . $ricdag_rec['DAGKEY'] . " - Chiave: " . $ricdag_rec['ITEKEY'];
                if (!$this->deleteRecord($this->PRAM_DB, 'RICDAG', $ricdag_rec['ROWID'], $delete_Info)) {
                    return false;
                }
            }
        }
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridRichieste);
        TableView::clearGrid($this->gridRichieste);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridPassi);
        TableView::clearGrid($this->gridPassi);
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_SvuotaRicerca');
        Out::show($this->nameForm . '_Bttn_log');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Pratica');
        Out::valore($this->nameForm . '_Anno', date("Y"));

        Out::hide($this->nameForm . '_TipoInCorso_field');
        
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Aggiorna');
        //Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Offline');
        Out::hide($this->nameForm . '_bttn_Ricevuta');
        Out::hide($this->nameForm . '_bttn_Infocamere');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_VediProtocollo');
        Out::hide($this->nameForm . '_Bttn_log');
        Out::hide($this->nameForm . '_SvuotaRicerca');
        Out::hide($this->nameForm . '_CambioEsibente');
        Out::hide($this->nameForm . '_Utilita');
        Out::hide($this->nameForm . '_AggiornaDatiAcquisizione');
    }

    function DecodAnapra($proric_rec, $retid, $tipoRic = 'codice') {
        $anapra_rec = $this->praLib->GetAnapra($proric_rec['RICPRO'], $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Procedimento":
                Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_desc_proc', $anapra_rec['PRADES__1']);
                break;
            case $this->nameForm . "_PRORIC[RICPRO]" :
                //
                //Decodifico procedimento richiesta
                //
                //COMMENTATO PERCHE NON SI PUò CAMBIARE IL PROCEDIEMNTO NELLA GESTIONE DELLE RICHIESTE ON LINE

                Out::valore($this->nameForm . "_PRORIC[RICPRO]", $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);

                //
                //Decodifico tipologia, settore, attivita
                //
                $anatip_rec = $this->praLib->GetAnatip($proric_rec['RICTIP']);
                $anaset_rec = $this->praLib->GetAnaset($proric_rec['RICSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($proric_rec['RICATT']);
                Out::valore($this->nameForm . "_PRORIC[RICGIO]", $anapra_rec['PRAGIO']);
                Out::valore($this->nameForm . "_Desc_tip", $anatip_rec['TIPDES']);
                Out::valore($this->nameForm . "_Desc_set", $anaset_rec['SETDES']);
                Out::valore($this->nameForm . "_Desc_att", $anaatt_rec['ATTDES']);
                if ($anapra_rec['PRAGIO'] != 0) {
                    $data1 = strtotime($this->rowidAppoggio);
                    $data2 = $anapra_rec['PRAGIO'] * 86400;
                    $somma = $data1 + $data2;
                    $scadenza = date('Ymd', $somma);
                    Out::valore($this->nameForm . "_SCADENZA", $scadenza);
                }
                $this->DecodResponsabile($anapra_rec['PRARES']);
                break;
        }
        return $anapra_rec;
    }

    function DecodResponsabile($codice, $tipoRic = 'codice') {
        //
        //Decodifico responsabile
        //
        $ananom_rec = $this->praLib->GetAnanom($codice, $tipoRic);
        Out::valore($this->nameForm . '_PRORIC[RICRES]', $ananom_rec['NOMRES']);
        Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec["NOMCOG"] . ' ' . $ananom_rec["NOMNOM"]);

        //
        //Decodifico settore, servizio, unita operativa
        //
        $anauniRes_rec = $this->praLib->GetAnauniRes($ananom_rec['NOMRES']);
        if ($anauniRes_rec['UNISET'] == "")
            $anauniRes_rec['UNISET'] = "";
        $anauni_rec = $this->praLib->getAnauni($anauniRes_rec['UNISET']);
        Out::valore($this->nameForm . '_PROGES[GESSET]', $anauni_rec['UNISET']);
        Out::valore($this->nameForm . '_Settore', $anauni_rec['UNIDES']);

        if ($anauniRes_rec['UNISER'] == "")
            $anauniRes_rec['UNISET'] = "";
        $anauniServ_rec = $this->praLib->GetAnauniServ($anauniRes_rec['UNISET'], $anauniRes_rec['UNISER']);
        Out::valore($this->nameForm . '_PROGES[GESSER]', $anauniServ_rec['UNISER']);
        Out::valore($this->nameForm . '_Servizio', $anauniServ_rec['UNIDES']);

        if ($anauniRes_rec['UNIOPE'] == "")
            $anauniRes_rec['UNISET'] = $anauniRes_rec['UNISER'] = "";
        $anauniOpe_rec = $this->praLib->GetAnauniOpe($anauniRes_rec['UNISET'], $anauniRes_rec['UNISER'], $anauniRes_rec['UNIOPE']);
        Out::valore($this->nameForm . '_PROGES[GESOPE]', $anauniOpe_rec['UNIOPE']);
        Out::valore($this->nameForm . '_Unita', $anauniOpe_rec['UNIDES']);
    }

    public function caricaAllegati($pratica) {
        $praLibRiservato = new praLibRiservato;

        $this->allegati = array();
        $sql = "SELECT * FROM RICDOC WHERE DOCNUM = '$pratica'";
        if ($_POST['_search'] === 'true') {
            $nomefile = addslashes($_POST['DOCUPL']);
            $nomefile_orig = addslashes($_POST['DOCNAME']);
            $classificazione_all = $_POST['CLASSIFICAZIONE'];
            $note_2 = addslashes($_POST['NOTE']);

            if ($nomefile) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DOCUPL') . " LIKE '%" . strtoupper($nomefile) . "%'";
                //$sql .= " AND DOCUPL LIKE '%$nomefile%'";
            }
            if ($nomefile_orig) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DOCNAME') . " LIKE '%" . strtoupper($nomefile_orig) . "%'";
                //$sql .= " AND DOCNAME LIKE '%$nomefile_orig%'";
            }
        }
        $sql .= " ORDER BY DOCUPL";
        $allegati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        foreach ($allegati as $allegato) {
            $ext = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
            $arrayMeta = $anacla_rec = $anaddo_rec = array();
            $strDest = $edit = "";
            if (strtolower($ext) == "p7m") {
                $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
            }
            $classificazione = '';

            if ($allegato['DOCMETA']) {
                $arrayMeta = unserialize($allegato['DOCMETA']);
                $anacla_rec = $this->praLib->GetAnacla($arrayMeta['CLASSIFICAZIONE']);
                $classificazione = $anacla_rec['CLADES'];
                $note_1 = $arrayMeta['NOTE'];
                if (is_array($arrayMeta['DESTINAZIONE'])) {
                    foreach ($arrayMeta['DESTINAZIONE'] as $dest) {
                        $anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $strDest .= $anaddo_rec['DDONOM'] . "<br>";
                    }
                }
            }
            if ($classificazione_all) {
                if (strpos($classificazione, $classificazione_all) === false) {
                    continue;
                }
            }
            if ($note_2) {
                if (strpos($note_1, $note_2) === false) {
                    continue;
                }
            }

            $pathAllegatiRichieste = $this->praLib->getPathAllegatiRichieste();
            $filePath = $pathAllegatiRichieste . "attachments/" . $pratica . "/" . $allegato['DOCUPL'];
            //
            $this->allegati[] = array(
                "ROWID" => $allegato['ROWID'],
                "DOCUPL" => $allegato['DOCUPL'],
                "FIRMA" => $edit,
                "DOCNAME" => $allegato['DOCNAME'],
                "SIZE" => $this->praLib->formatFileSize(filesize($filePath)),
                "CLASSIFICAZIONE" => $classificazione,
                "DESTINAZIONI" => $strDest,
                "NOTE" => $arrayMeta['NOTE'],
                'RISERVATO' => $praLibRiservato->getIconRiservato($allegato['DOCRIS'])
            );
        }
        $this->CaricaGriglia($this->gridAllegati, $this->allegati, '2');
        $this->ContaSizeAllegati($this->allegati);
    }

    function ContaSizeAllegati($allegati) {
        $pathAllegatiRichieste = $this->praLib->getPathAllegatiRichieste();

        if ($allegati) {
            $totSize = 0;
            foreach ($allegati as $allegato) {
                $filePath = $pathAllegatiRichieste . "attachments/" . $this->currRicnum . "/" . $allegato['DOCUPL'];
                $totSize = $totSize + filesize($filePath);
            }
            if ($totSize != 0) {
                $Size = $this->praLib->formatFileSize($totSize);
                Out::valore($this->nameForm . "_Totale", $Size);
            }
        } else {
            Out::valore($this->nameForm . "_Totale", ' ');
        }
    }

    public function caricaPassi($pratica) {
        $sql = "SELECT
                    RICITE.ROWID AS ROWID,
                    RICITE.RICNUM AS RICNUM,
                    RICITE.ITESEQ AS ITESEQ,
                    RICITE.ITEGIO AS ITEGIO,
                    RICITE.ITEDES AS ITEDES,
                    RICITE.ITEPUB AS ITEPUB,
                    RICITE.ITEOBL AS ITEOBL,
                    RICITE.ITECTP AS ITECTP,
                    RICITE.ITEQST AS ITEQST,
                    RICITE.ITEVPA AS ITEVPA,
                    RICITE.ITEVPN AS ITEVPN,
                    RICITE.ITEKEY AS ITEKEY,
                    RICITE.ITEIRE AS ITEIRE,
                    RICITE.ITEZIP AS ITEZIP,
                    PRACLT.CLTDES AS CLTDES," .
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM RICITE LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=RICITE.ITERES
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=RICITE.ITECLT
                WHERE RICITE.RICNUM = '$pratica' ORDER BY ITESEQ";
        $this->passi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if ($this->passi) {
            foreach ($this->passi as $key => $passo) {
                $Proric_rec = $this->praLib->GetProric($passo['RICNUM']);
                $color = $this->GetColorPasso($passo, $Proric_rec['RICSEQ']);
                $this->passi[$key]['ITESEQ'] = "<div style=\"background-color:$color;color:white\">" . $passo['ITESEQ'] . "</div>";
                if ($passo['ITEVPA'] != '' || $passo['ITEVPN'] != '') {
                    $this->passi[$key]['VAI'] = '<span class="ita-icon ita-icon-arrow-green-dx-16x16"></span>';
                }
                if ($passo['ITECTP'] != 0) {
                    $Itepas_rec = $this->praLib->GetRicite($passo['ITECTP'], "itekey", false, '', $pratica);
                    $this->passi[$key]['CONTROLLO'] = $Itepas_rec['ITESEQ'];
                }
            }
            Out::show($this->nameForm . '_CancellaPassi');
            Out::show($this->nameForm . '_ExportPassi');
        } else {
            Out::hide($this->nameForm . '_CancellaPassi');
            Out::hide($this->nameForm . '_ExportPassi');
        }
        $this->CaricaGriglia($this->gridPassi, $this->passi, '2');
    }

    public function GetColorPasso($record, $ricseq) {
        $color = "";
        if ($record['ITEOBL'] != 0) {
            $color = "red";
        } else {
            $color = "orange";
        }
        if ($record['ITEQST'] != 0) {
            $color = "blue";
        }
        if ($record['ITEIRE'] != 0 || $record['ITEZIP'] != 0) {
            $color = "navy";
        }
        if (strpos($ricseq, chr(46) . $record['ITESEQ'] . chr(46)) !== false) {
            $color = "green";
        }
        return $color;
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PRATICA'] = substr($Result_rec['RICNUM'], 4, 6) . "/" . substr($Result_rec['RICNUM'], 0, 4);
            $Result_tab[$key]['INIZIO'] = substr($Result_rec['RICDRE'], 6, 2) . "/" . substr($Result_rec['RICDRE'], 4, 2) . "/" . substr($Result_rec['RICDRE'], 0, 4) . "<br>" . $Result_rec['RICORE'];
            $dataInoltro = "";
            if ($Result_rec['RICDAT']) {
                $dataInoltro = substr($Result_rec['RICDAT'], 6, 2) . "/" . substr($Result_rec['RICDAT'], 4, 2) . "/" . substr($Result_rec['RICDAT'], 0, 4);
            }
            $Result_tab[$key]['INOLTRO'] = $dataInoltro . "<br>" . $Result_rec['RICTIM'];

            $dataProtDiff = "";
            if ($Result_rec['RICDATARPROT']) {
                $dataProtDiff = substr($Result_rec['RICDATARPROT'], 6, 2) . "/" . substr($Result_rec['RICDATARPROT'], 4, 2) . "/" . substr($Result_rec['RICDATARPROT'], 0, 4);
            }
            $Result_tab[$key]['DIFFERITA'] = $dataProtDiff . "<br>" . $Result_rec['RICORARPROT'];

            $anapra_rec = $this->praLib->GetAnapra($Result_rec['RICPRO']);
            $Result_tab[$key]['PROCEDIMENTO'] = $anapra_rec['PRADES__1'];
            $img = $this->GetImgStato($Result_rec['RICSTA'], $Result_rec['ROWID']);
            if ($img) {
                $Result_tab[$key]['STATO'] = $img;
            }
            $Ric_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $Result_rec['RICNUM']);
            $Result_tab[$key]['ACCORPATE'] = count($Ric_accorpate) ? count($Ric_accorpate) : '';
            $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
            $Result_tab[$key]['AGGREGATO'] = $anaspa_rec['SPADES'];
        }
        return $Result_tab;
    }

    private function GetImgStato($stato, $rowId = 0) {
        switch ($stato) {
            case praLibRichiesta::RICSTA_INOLTRATA:
                $img = "<span class=\"ita-icon ita-icon-chiusagreen-24x24\">Richiesta inoltrata</span>";
                break;
            case praLibRichiesta::RICSTA_INOLTRATA_CAMERA_COMMERCIO:
                $img = "<span class=\"ita-icon ita-icon-chiusagray-24x24\">Richiesta inoltrata alla camera di commercio</span>";
                break;
            case praLibRichiesta::RICSTA_ANNULLATA:
                $img = "<span class=\"ita-icon ita-icon-check-red-24x24\">Richiesta annullata</span>";
                break;
            case praLibRichiesta::RICSTA_IN_CORSO:
                $img = "<span class=\"ui-icon ui-icon-pencil\"  style=\"color: green; font-size:24px;\" >Richiesta in corso</span>";
                $proric_rec = $this->praLib->GetProric($rowId, "rowid");
                if ($proric_rec && $this->isEnableScadenza()){
                    if ($this->isRichiestaScaduta($proric_rec['RICSTA'], $proric_rec['RICDRE'], $proric_rec['RICTSP'], $proric_rec['RICFORZAINVIO'])){
                        $img = "<span class=\"ui-icon ui-icon-clock\"  style=\"color: red; font-size:24px;\" >Richiesta in corso Scaduta</span>";
                    }
                    else {
                        $img = "<span class=\"ui-icon ui-icon-clock\"  style=\"color: lightgreen; font-size:24px;\" >Richiesta in corso Attiva</span>";
                    }
                }
                break;
            case praLibRichiesta::RICSTA_OFFLINE:
                $img = "<span class=\"ita-icon ita-icon-divieto-24x24\">Richiesta Off-line</span>";
                break;
            case praLibRichiesta::RICSTA_PROTOCOLLAZIONE_IN_CORSO:
                $img = "<span class=\"ita-icon ita-icon-yellow-alert-24x24\">Protocollazione in Corso</span>";
                break;
            default:
                break;
        }
        return $img;
    }

    function DecodAnaeventi($Codice, $tipoRic = 'codice', $updsegnalazione = false) {
        Out::valore($this->nameForm . "_PRORIC[RICEVE]", '');
        Out::valore($this->nameForm . "_Desc_eve", '');
        $anaeventi_rec = $this->praLib->GetAnaeventi($Codice, $tipoRic);
        if ($anaeventi_rec) {
            Out::valore($this->nameForm . "_PRORIC[RICEVE]", $anaeventi_rec['EVTCOD']);
            Out::valore($this->nameForm . "_Desc_eve", $anaeventi_rec['EVTDESCR']);
            if ($updsegnalazione && $anaeventi_rec['EVTSEGCOMUNICA']) {
                Out::valore($this->nameForm . "_PRORIC[RICSEG]", $anaeventi_rec['EVTSEGCOMUNICA']);
            }
        }
    }

    function elaboraRecordsXls($Result_tab, $arrCampi = array()) {
        $Result_tab_new = array();
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab_new[$key] = $Result_rec;
            $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
            $Result_tab_new[$key]['RICSPA'] = $anaspa_rec['SPADES'];

            /*
             * Aggiungo alla fine i campi aggiuntivi scelti dall'operatore
             */
            if ($arrCampi) {
                foreach ($arrCampi as $campo) {
                    if ($campo) {
                        $ricdag_recCampo = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $Result_rec['RICNUM'] . "' AND DAGKEY='$campo' AND RICDAT<>''", false);
                        $Result_tab_new[$key][$campo] = $ricdag_recCampo['RICDAT'];
                    }
                }
            }
        }
        return $Result_tab_new;
    }

    public function CaricoCampiAggiuntivi($proric_rec, $codice) {

        // Out::msgInfo(" NUMERO PROC ITEKEY", $proric_rec);
        $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '$proric_rec' AND DAGNUM = '$codice'";

        if ($_POST['_search'] === 'true') {
            $datiseq = addslashes($_POST['DAGSEQ']);
            $datinome = addslashes($_POST['DAGKEY']);
            $datidesc = addslashes($_POST['DAGDES']);
            $dagset = addslashes($_POST['DAGSET']);
            $dagval = addslashes($_POST['DAGVAL']);
            $dagtipo = addslashes($_POST['DAGTIP']);
            $datiacquisiti = addslashes($_POST['RICDAT']);
            if ($datiseq) {
                $sql .= " AND DAGSEQ LIKE '%$datiseq%'";
            }
            if ($datinome) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGKEY') . " LIKE '%" . strtoupper($datinome) . "%'";
            }
            if ($datidesc) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGDES') . " LIKE '%" . strtoupper($datidesc) . "%'";
                //$sql .= " AND DAGDES LIKE '%$datidesc%'";
            }
            if ($dagset) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGSET') . " LIKE '%" . strtoupper($dagset) . "%'";
                //$sql .= " AND DAGSET LIKE '%$dagset%'";
            }
            if ($dagval) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGVAL') . " LIKE '%" . strtoupper($dagval) . "%'";
            }
            if ($dagtipo) {
                $sql .= " AND DAGTIP LIKE '%$dagtipo%'";
            }
            if ($datiacquisiti) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICDAT') . " LIKE '%" . strtoupper($datiacquisiti) . "%'";
            }
        }
        $sql .= " ORDER BY DAGSEQ";

        $this->altriDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->CaricaGriglia($this->gridDati, $this->altriDati);
    }

    function AzzeraVariabili($pulisciRicerca = true) {
        if ($pulisciRicerca) {
            Out::clearFields($this->nameForm, $this->divRicerca);
        }
    }

    public function GetArrayAzioni() {
        $arrayAzioni = array(
            'Estrazione' => array('id' => $this->nameForm . '_Estrazione', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-spreadsheet-32x32'", 'model' => $this->nameForm),
        );
        return $arrayAzioni;
    }

    private function caricaAcl($ricnum) {
        $sql = "SELECT 
                    RICACL.*,
                    RICSOGGETTI.SOGRICDENOMINAZIONE,
                    RICSOGGETTI.SOGRICFIS
                FROM
                    RICACL
                LEFT OUTER JOIN RICSOGGETTI ON RICSOGGETTI.ROW_ID = RICACL.ROW_ID_RICSOGGETTI
                WHERE
                    SOGRICNUM = '$ricnum'
                ";
        $ricacl_tab = $this->praLib->getGenericTab($sql);
        if ($ricacl_tab) {
            foreach ($ricacl_tab as $key => $ricacl_rec) {
                $da_ta = substr($ricacl_rec['RICACLDATA'], 6, 2) . "/" . substr($ricacl_rec['RICACLDATA'], 4, 2) . "/" . substr($ricacl_rec['RICACLDATA'], 0, 4);
                $ricacl_tab[$key]['DATAEORA'] = $da_ta . " " . $ricacl_rec['RICACLORA'];

                /*
                 * Se c'è Decodifico il Passo
                 */
                if ($ricacl_rec['ROW_ID_PASSO'] != 0) {
                    $ricite_rec = $this->praLib->GetRicite($ricacl_rec['ROW_ID_PASSO'], 'rowid', false);
                    $ricacl_tab[$key]['PASSO'] = $ricite_rec['ITESEQ'] . "-" . $ricite_rec['ITEDES'];
                }

                /*
                 * Decodifica Messaggio Attiva Quando
                 */
                switch ($ricacl_rec['RICACLATTIVA']) {
                    case 1:
                        $msg = "Attivo solo se la richiesta On-Line e' in compilazione (NON INOLTRATA)";
                        break;
                    case 2:
                        $msg = "Attivo solo se la richiesta On-Line e' stata INOLTRATA";
                        break;
                    case 3:
                        $msg = "Attivo sempre sia se la richiesta On-Line e' in compilazione o e' inoltrata";
                        break;
                    default:
                        $msg = "";
                        break;
                }
                $ricacl_tab[$key]['ATTIVAQUANDO'] = $msg;

                /*
                 * Decodifico il tipo ACL dai Metadadti JSON
                 */
                $arrMetadata = json_decode($ricacl_rec['RICACLMETA'], true);
                if ($arrMetadata) {
                    if ($arrMetadata['AUTORIZZAZIONE'][0]['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_RICHIESTA') {
                        $tipo = "VISUALIZZAZIONE_RICHIESTA";
                        if (isset($arrMetadata['AUTORIZZAZIONE'][0]['INTEGRAZIONE_RICHIESTA']) && $arrMetadata['AUTORIZZAZIONE'][0]['INTEGRAZIONE_RICHIESTA'] == 1) {
                            $tipo = "INTEGRAZIONE_RICHIESTA";
                        }
                    } elseif ($arrMetadata['AUTORIZZAZIONE'][0]['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
                        $tipo = "GESTIONE_PASSO";
                    }
                }
                $ricacl_tab[$key]['TIPOACL'] = $tipo;
            }
            $this->acl = $ricacl_tab;
            $this->CaricaGriglia($this->gridAcl, $this->acl, '2');
        }
    }

    private function visualizzaScadenza($ricSta, $ricDre, $ricTsp, $visualizza = true){
        if ($visualizza){
            /**
             * Si calcola la scadenza
             */
//            $dataScad = $this->getDataScadenza($rowId);
            $dataScad = $this->getDataScadenza($ricSta, $ricDre, $ricTsp);
            
            if ($dataScad){
                Out::show($this->nameForm . '_PRORIC[RICFORZAINVIO]_field');
                Out::show($this->nameForm . '_ScadenzaRichiesta_field');

                Out::valore($this->nameForm . '_ScadenzaRichiesta', $dataScad);

                if ($dataScad > date('Ymd')){
                    Out::css($this->nameForm . '_ScadenzaRichiesta', "background-color", "lightgreen");
                }
                else {
                    Out::css($this->nameForm . '_ScadenzaRichiesta', "background-color", "red");
                }
            }
            else {
                Out::hide($this->nameForm . '_PRORIC[RICFORZAINVIO]_field');
                Out::hide($this->nameForm . '_ScadenzaRichiesta_field');
            }

        }
        else {
            Out::hide($this->nameForm . '_PRORIC[RICFORZAINVIO]_field');
            Out::hide($this->nameForm . '_ScadenzaRichiesta_field');
            
        }
        
    }


    private function isEnableScadenza(){
        $attivaScadenza = false;
        
        /*
         * Controllo se attivata voce "SCADRICATTIVA" nella tabella ENV_CONFIG in ITAFRONOFFICE
         */
        $valore = $this->getValoreEnv_Config("SCADENZARICHIESTAONLINE", "SCADRICATTIVA");
        if ($valore && $valore == "Si"){
            $attivaScadenza = true;
        }
        
        return $attivaScadenza;
    }
    
    private function getGiorniScadenza($ricTsp){
        $gg = 0;
        
        /*
         * Si controlla se configurati giorni nello sportello online della richiesta
         */
        $anatsp_rec = $this->praLib->GetAnatsp($ricTsp);
        if ($anatsp_rec){
            $gg = $anatsp_rec['TSPGGSCAD'];
        }
        
        if (!$gg || $gg < 1){
            $gg = $this->getValoreEnv_Config("SCADENZARICHIESTAONLINE", "SCADRICGIORNI");
        }
        
        if (!$gg || $gg == ''){
            $gg = 0;
        }
        
        return $gg;
    }

    private function getDataScadenza($ricSta, $ricDre, $ricTsp){
        $dataScad = '';
        if ($this->isEnableScadenza() && $ricSta == 99){        
            $gg = $this->getGiorniScadenza($ricTsp);
            if ($gg > 0){
                $dataScad = $this->praLib->AddGiorniToData($ricDre, $gg);
            }
        }

        return $dataScad;
    }

    private function isRichiestaScaduta($ricSta, $ricDre, $ricTsp, $ricForzaInvio){
        $scaduta = false;
        $dataScad = $this->getDataScadenza($ricSta, $ricDre, $ricTsp);
        if ($dataScad){
            if ($dataScad < date('Ymd') && $ricForzaInvio == 0){
                $scaduta = true;
            }
        }
        
        return $scaduta;
    }

    private function getValoreEnv_Config($classe, $key){
        $valore = '';
//        $classe = 'SCADENZARICHIESTAONLINE';
//        $key = 'SCADRICATTIVA';
        $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE = '$classe' AND CHIAVE = '" . $key . "'";
        $parFO_rec = ItaDB::DBSQLSelect($this->praLib->getITAFODB(), $sql, false);
        if ($parFO_rec){
            $valore = $parFO_rec['CONFIG'];
        }
        
        return $valore;
    }

    
}

