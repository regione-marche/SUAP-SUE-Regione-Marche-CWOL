<?php

/**
 *
 * ANAGRAFICA PROCEDIMENTI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Andimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDiagramma.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praControllers.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praAzioniFO.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function praPro() {
    $praPro = new praPro();
    $praPro->parseEvent();
    return;
}

class praPro extends itaModel {

    public $praLib;
    public $devLib;
    public $PRAM_DB;
    public $utiEnte;
    public $rowidAppoggio;
    public $currPranum;
    public $allegati = array();
    public $passi = array();
    public $passiSel = array();
    public $requisiti = array();
    public $normative = array();
    public $discipline = array();
    public $eventi = array();
    public $obbligatori = array();
    public $azioniFO = array();
    public $nameForm = "praPro";
    public $divGes = "praPro_divGestione";
    public $divRis = "praPro_divRisultato";
    public $divRic = "praPro_divRicerca";
    public $divDup = "praPro_divDuplica";
    public $gridAnapra = "praPro_gridAnapra";
    public $gridPassi = "praPro_gridPassi";
    public $gridAllegati = "praPro_gridAllegati";
    public $gridRequisiti = "praPro_gridRequisiti";
    public $gridNormative = "praPro_gridNormative";
    public $gridDiscipline = "praPro_gridDiscipline";
    public $gridEventi = "praPro_gridEventi";
    public $gridObbligatori = "praPro_gridObbligatori";
    public $gridAzioniFO = "praPro_gridAzioniFO";
    public $gridGruppi = "praPro_gridGruppi";
    public $controller;
    public $insertTo;
    public $page;
    public $autoSearch;
    public $autoDescr;
    public $praProcDatiAggiuntiviFormname;
    public $praProcDiagrammaFormname;
    public $curRowidComposizione;
    private $passiGruppiFormName;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->devLib = new devLib();
            $this->utiEnte = new utiEnte();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
            $this->passi = App::$utente->getKey($this->nameForm . '_passi');
            $this->passiSel = App::$utente->getKey($this->nameForm . '_passiSel');
            $this->requisiti = App::$utente->getKey($this->nameForm . '_requisiti');
            $this->eventi = App::$utente->getKey($this->nameForm . '_eventi');
            $this->normative = App::$utente->getKey($this->nameForm . '_normative');
            $this->discipline = App::$utente->getKey($this->nameForm . '_discipline');
            $this->obbligatori = App::$utente->getKey($this->nameForm . '_obbligatori');
            $this->currPranum = App::$utente->getKey($this->nameForm . '_currPranum');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->insertTo = App::$utente->getKey($this->nameForm . '_insertTo');
            $this->page = App::$utente->getKey($this->nameForm . '_page');
            $this->autoSearch = App::$utente->getKey($this->nameForm . '_autoSearch');
            $this->autoDescr = App::$utente->getKey($this->nameForm . '_autoDescr');
            $this->azioniFO = App::$utente->getKey($this->nameForm . '_azioniFO');
            $this->praProcDatiAggiuntiviFormname = App::$utente->getKey($this->nameForm . '_praProcDatiAggiuntiviFormname');
            $this->praProcDiagrammaFormname = App::$utente->getKey($this->nameForm . '_praProcDiagrammaFormname');
            $this->curRowidComposizione = App::$utente->getKey($this->nameForm . "_curRowidComposizione");
            $this->passiGruppiFormName = App::$utente->getKey($this->nameForm . "_passiGruppiFormName");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_passi', $this->passi);
            App::$utente->setKey($this->nameForm . '_passiSel', $this->passiSel);
            App::$utente->setKey($this->nameForm . '_requisiti', $this->requisiti);
            App::$utente->setKey($this->nameForm . '_eventi', $this->eventi);
            App::$utente->setKey($this->nameForm . '_normative', $this->normative);
            App::$utente->setKey($this->nameForm . '_discipline', $this->discipline);
            App::$utente->setKey($this->nameForm . '_obbligatori', $this->obbligatori);
            App::$utente->setKey($this->nameForm . '_currPranum', $this->currPranum);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_insertTo', $this->insertTo);
            App::$utente->setKey($this->nameForm . '_page', $this->page);
            App::$utente->setKey($this->nameForm . '_autoSearch', $this->autoSearch);
            App::$utente->setKey($this->nameForm . '_autoDescr', $this->autoDescr);
            App::$utente->setKey($this->nameForm . '_azioniFO', $this->azioniFO);
            App::$utente->setKey($this->nameForm . '_praProcDatiAggiuntiviFormname', $this->praProcDatiAggiuntiviFormname);
            App::$utente->setKey($this->nameForm . '_praProcDiagrammaFormname', $this->praProcDiagrammaFormname);
            App::$utente->setKey($this->nameForm . "_curRowidComposizione", $this->curRowidComposizione);
            App::$utente->setKey($this->nameForm . "_passiGruppiFormName", $this->passiGruppiFormName);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                $this->CaricaSubform();

                if ($_POST['autoSearch']) {
                    $this->autoSearch = str_pad($_POST['autoSearch'], 6, "0", STR_PAD_LEFT);
                    $Anapra_rec = $this->praLib->GetAnapra($this->autoSearch);
                    if ($Anapra_rec) {
                        $this->Dettaglio($Anapra_rec['ROWID']);
                    } else {
                        if ($_POST['autoDescr']) {
                            $this->autoDescr = $_POST['autoDescr'];
                        }
                        $this->nuovo($this->autoSearch, $this->autoDescr);
                    }
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAzioniFO:
                        $sel1 = $sel2 = $sel3 = false;
                        $Praazioni_rec = $this->azioniFO[$_POST['rowid']];
                        switch ($Praazioni_rec['ERROREAZIONE']) {
                            case "CONT":
                                $sel1 = true;
                                break;
                            case "ERR":
                                $sel2 = true;
                                break;
                            case "WARN":
                                $sel3 = true;
                                break;
                        }
                        Out::msgInput(
                                "Compila i seguenti campi", array(
                            array(
                                'id' => $this->nameForm . '_IdGridAzione',
                                'name' => $this->nameForm . '_IdGridAzione',
                                'value' => $_POST['rowid'],
                                'type' => 'text',
                                'style' => "display:none;",
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Classe'),
                                'id' => $this->nameForm . '_ClasseAzione',
                                'name' => $this->nameForm . '_ClasseAzione',
                                'value' => $Praazioni_rec['CLASSEAZIONE'],
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Metodo'),
                                'id' => $this->nameForm . '_MetodoAzione',
                                'name' => $this->nameForm . '_MetodoAzione',
                                'value' => $Praazioni_rec['METODOAZIONE'],
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Operazione dopo Errore'),
                                'id' => $this->nameForm . '_ErroreAzione',
                                'name' => $this->nameForm . '_ErroreAzione',
                                'value' => $Praazioni_rec['ERROREAZIONE'],
                                'type' => 'select',
                                'width' => '50',
                                'size' => '1',
                                'options' => array(
                                    array("", ""),
                                    array("CONT", "Continua esecuzione", $sel1),
                                    array("ERR", "Blocca esecuzione", $sel2),
                                    array("WARN", "Continua con invio segnalazione silenziosa", $sel3),
                                )
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiAzione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;
                    case $this->gridAnapra:
                        $Anapra_rec = $this->praLib->GetAnapra($_POST['rowid'], "rowid");
                        $msg = "<span style=\"font-weight:bold;\">Questo procedimento con i suoi passi risultano essere utilizzati come template nei seguenti procedimenti:</span>";
                        if (!$this->praLib->CheckUsagePassoTemplate($Anapra_rec['PRANUM'], $msg, true, $this->nameForm)) {
                            $this->rowidAppoggio = $_POST['rowid'];
                            break;
                        }
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAllegati:
                        $ext = pathinfo($this->allegati[$_POST['rowid']]['FILEPATH'], PATHINFO_EXTENSION);
                        if ($ext == "html") {
                            $contentFile = @file_get_contents($this->allegati[$_POST['rowid']]['FILEPATH']);
                            $this->openEdit("returnSaveEdit", $contentFile);
                        } else {
                            if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                                Out::openDocument(
                                        utiDownload::getUrl(
                                                $this->allegati[$_POST['rowid']]['FILENAME'], $this->allegati[$_POST['rowid']]['FILEPATH']
                                        )
                                );
                            }
                        }
                        break;
                    case $this->gridPassi:
                        $rigaSel = $_POST[$this->gridPassi]['gridParam']['selrow'];
                        $model = 'praPassoProc';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['page'] = $this->page;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->gridEventi:
                        $model = "praIteevt";
                        itaLib::openDialog($model);
                        $_POST['PRANUM'] = $_POST[$this->nameForm . '_ANAPRA']['PRANUM'];
                        /* @var $formObj itaModel */
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setOpenData($this->eventi[$_POST['rowid']]);
                        $formObj->setReturnId($_POST['rowid']);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraIteevt');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->gridObbligatori:
                        $rigaSel = $_POST[$this->gridObbligatori]['gridParam']['selrow'];
                        $model = "praIteObb";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraIteObb');
                        $formObj->setEvent('openform');
                        $formObj->setOpenData($this->obbligatori[$rigaSel]);
                        $formObj->setRigaSel($rigaSel);
                        $formObj->parseEvent();
                        break;

                    case $this->gridGruppi:
                        $this->curRowidComposizione = $_POST['rowid'];
                        $this->OpenDettaglioGruppo();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $model = 'praPassoProc';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $this->currPranum;
                        $_POST['modo'] = "add";
                        $_POST['page'] = $this->page;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->gridRequisiti:
                        praRic::praRicAnareq('praPro');
                        break;
                    case $this->gridNormative:
                        praRic::praRicAnanor('praPro');
                        break;
                    case $this->gridDiscipline:
                        praRic::praRicAnadis('praPro');
                        break;
                    case $this->gridEventi:
                        $model = "praIteevt";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraIteevt');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->gridObbligatori:
                        $rigaSel['ROWID'] = '';
                        $rigaSel['OBBPRA'] = $_POST[$this->nameForm . '_ANAPRA']['PRANUM'];
                        $rigaSel['OBBEVCOD'] = '';
                        $rigaSel['OBBSUBPRA'] = '';
                        $rigaSel['OBBSUBEVCOD'] = '';
                        $rigaSel['OBBEXPRCTR'] = '';
                        //
                        $model = "praIteObb";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnPraIteObb');
                        $formObj->setEvent('openform');
                        $formObj->setOpenData($rigaSel);
                        $formObj->setRigaSel('');
                        $formObj->parseEvent();
                        break;

                    case $this->gridGruppi:
                        $this->RichiediNuovoGruppo();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnapra:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridAzioniFO:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione dell'azione?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaAzione', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaAzione', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->gridPassi:
                        if (!$this->ControlliCanc($_POST['rowid'])) {
                            break;
                        }
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il passo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridAllegati:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridRequisiti:
                        unset($this->requisiti[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridRequisiti, $this->requisiti);
                        break;
                    case $this->gridEventi:
                        unset($this->eventi[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridEventi, $this->elaboraRecordsEventi($this->eventi));
                        break;
                    case $this->gridNormative:
                        unset($this->normative[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridNormative, $this->normative);
                        break;
                    case $this->gridDiscipline:
                        unset($this->discipline[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridDiscipline, $this->discipline);
                        break;
                    case $this->gridObbligatori:
                        unset($this->obbligatori[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridObbligatori, $this->elaboraRecordsObbligatori($this->obbligatori));
                        break;

                    case $this->gridGruppi:
                        $this->curRowidComposizione = $_POST['rowid'];
                        $domanda = "Sei sicuro di voler cancellare il Gruppo selezionato?";

                        if ($this->getNumeroPassi($this->curRowidComposizione) > 0) {
                            $domanda = "Nel gruppo sono presenti dei passi, sei sicuro di voler cancellare il Gruppo selezionato?";
                        }

                        $itediaggruppi_rec = $this->praLib->GetItediaggruppi($this->curRowidComposizione);

                        if ($itediaggruppi_rec) {
                            Out::msgQuestion("Gestione Documenti Composizione.", $domanda, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaGruppo',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Cancella' => array('id' => $this->nameForm . '_ConfermaCancellaGruppo',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                Out::msgQuestion("Export su File  Excel", "Scegli Export Excel da lanciare", array(
                    'F8-Export Tabellare Semplice' => array('id' => $this->nameForm . '_ConfermaTabella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Export Banca Dati Procedimenti' => array('id' => $this->nameForm . '_ConfermaTabellaBDP', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnapra:
                        TableView::clearGrid($this->gridAnapra);
                        $ordinamento = $_POST['sidx'];
                        if ($ordinamento == 'DESCRIZIONE') {
                            $ordinamento = 'PRADES__1';
                        }
                        if ($ordinamento == 'RESPONSABILE') {
                            $ordinamento = 'NOMCOG';
                        }
                        if ($ordinamento == '') {
                            $ordinamento = 'PRANUM';
                        }
                        $sql = $this->CreaSql();
                        $anapraTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        $anapraTab = $this->elaboraTabella($anapraTab);
                        //$ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01 = new TableView($_POST['id'], array('arrayTable' => $anapraTab));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                    case $this->gridPassi:
                        $this->CaricaGriglia($this->gridPassi, $this->passi, '2');
                        break;
                    case $this->gridAllegati:
                        break;
                    case $this->gridGruppi:
                        $this->caricaGrigliaGruppi($this->currPranum);
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['cellname']) {
                    case 'SEQUENZA':
                        if (is_numeric($_POST['value'])) {
                            $this->allegati[$_POST['rowid']]['SEQUENZA'] = (int) $_POST['value'];
                            $this->riordinaAllegati();
                        }

                        $this->CaricaGriglia($this->gridAllegati, $this->allegati);
                        break;
                    case 'FILEINFO':
                        $this->allegati[$_POST['rowid']]['FILEINFO'] = $_POST['value'];
                        break;
                    case 'CLASSE':
                        $this->allegati[$_POST['rowid']]['CLASSE'] = $_POST['value'];
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridGruppi:
                        switch ($_POST['colName']) {
                            case 'ASSEGNAPASSI':

                                $this->curRowidComposizione = $_POST['rowid'];
                                $itediaggruppi_rec = $this->praLib->GetItediaggruppi($this->curRowidComposizione);
                                if ($itediaggruppi_rec)
                                    $descGruppo = $itediaggruppi_rec['DESCRIZIONE'];


                                /* PRovato ad usare utiJqGridCustom  */

                                $dbName = "PRAM";

                                /** @var utiJqGridCustom $model */
                                $model = cwbLib::apriFinestra('utiJqGridCustom', $this->nameForm, null, null, null, $this->getNameFormOrig(), null);

                                $sql = 'SELECT ITEDIAGPASSIGRUPPI.ROW_ID, ITEPAS.ITESEQ, ITEPAS.ITEDES, ITEPAS.ITEGIO FROM ITEPAS '
                                        . 'LEFT JOIN ITEDIAGPASSIGRUPPI ON ITEDIAGPASSIGRUPPI.ITEKEY = ITEPAS.ITEKEY '
                                        . 'WHERE ITEDIAGPASSIGRUPPI.ROW_ID_ITEDIAGGRUPPI = ' . $this->curRowidComposizione;

                                $colModel = array(
                                    array('name' => 'ITESEQ', 'title' => 'Sequenza', 'class' => '{align:\'center\', fixed: true}', 'width' => '80px'),
                                    array('name' => 'ITEDES', 'title' => 'Descrizione')
                                );
                                $metadata = array(
                                    'caption' => 'Passi Associati',
                                    'shrinkToFit' => true,
                                    'width' => 1000,
                                    'readerId' => 'ROW_ID',
                                    'sortname' => 'ROW_ID',
                                    'navGrid' => true,
                                    'navButtonDel' => true,
                                    'navButtonAdd' => true,
                                    'navButtonEdit' => false,
                                    'navButtonExcel' => false,
                                    'navButtonPrint' => false,
                                    'filterToolbar' => false,
                                    'navButtonRefresh' => false,
                                    'resizeToParent' => true,
                                    //            'showInlineButtons'=>'{view: true, edit: true, delete: false}',
                                    //            'showAuditColumns'=>true,
                                    //            'showRecordStatus'=>true,
                                    //            'onSelectRow'=>true,
                                    //            'multiselect'=>true,
                                    //            'multiselectEvents'=>true,
                                    'rowNum' => 999999,
                                    'rowList' => '[]',
                                    'reloadOnResize' => false,
                                    'pgbuttons' => false,
                                    'pginput' => false
                                );

                                $model->setJqGridModel($colModel, $metadata);
                                $model->setJqGridDataDB($sql, $dbName);
                                $titolo = "Elenco Passi associati al Gruppo " . $this->curRowidComposizione;
                                if ($descGruppo)
                                    $titolo = $titolo . " - " . $descGruppo;
                                $model->setTitle($titolo);
                                $model->setReturnEvents('view', 'dbClickRow', 'select', 'multiselect', 'details', 'returnCancellaPasso', 'printPdf', 'printXslx', 'returnAggiungiPassi', 'returnCloseRiportaPassi');
                                $model->render();

                                $this->passiGruppiFormName = $model->getNameForm();

                                break;
                        }
                        break;



                    case $this->gridPassi:
                        switch ($_POST['colName']) {
                            case 'VAI':
                                $mess = '';
                                $arrayMsg = array('F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaVis', 'model' => $this->nameForm, 'shortCut' => 'f8'));
                                $itepas_rec = $this->praLib->GetItepas($_POST['rowid'], 'rowid');

                                if ($itepas_rec['ITEQST'] == 0) {
                                    if ($itepas_rec['ITEVPA']) {
                                        $itepas_vaipasso_rec = $this->praLib->GetItepas($itepas_rec['ITEVPA'], 'itekey');
                                        $mess .= "Il passo di destinazione ha sequenza: {$itepas_vaipasso_rec['ITESEQ']} - ";
                                        $mess .= $itepas_vaipasso_rec['ITEDES'] . '<br>';
                                        $arrayMsg['F5 - Vai al passo'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $itepas_vaipasso_rec['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$itepas_vaipasso_rec['ROWID']}' }",
                                            'shortCut' => 'f5'
                                        );
                                    }
                                }

                                if ($itepas_rec['ITEQST'] == 1) {
                                    if ($itepas_rec['ITEVPA']) {
                                        $Itepas_vaiSI = $this->praLib->GetItepas($itepas_rec['ITEVPA'], 'itekey');
                                        $mess .= "Il passo di destinazione (risposta SI) ha sequenza: {$Itepas_vaiSI['ITESEQ']} - ";
                                        $mess .= $Itepas_vaiSI['ITEDES'] . '<br>';
                                        $arrayMsg['F5 - Vai al passo SI'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $Itepas_vaiSI['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$Itepas_vaiSI['ROWID']}' }",
                                            'shortCut' => 'f5'
                                        );
                                    }

                                    if ($itepas_rec['ITEVPN']) {
                                        $Itepas_vaiNO = $this->praLib->GetItepas($itepas_rec['ITEVPN'], 'itekey');
                                        $mess .= "Il passo di destinazione (risposta NO) ha sequenza: {$Itepas_vaiNO['ITESEQ']} - ";
                                        $mess .= $Itepas_vaiNO['ITEDES'] . '<br>';
                                        $arrayMsg['F6 - Vai al passo NO'] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $Itepas_vaiNO['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$Itepas_vaiNO['ROWID']}' }",
                                            'shortCut' => 'f6'
                                        );
                                    }
                                }

                                if ($itepas_rec['ITEQST'] == 2) {
                                    /*
                                     * Cerco sulla tabella ITEVPADETT
                                     */

                                    $itevpadett_tab = $this->praLib->GetItevpadett($itepas_rec['ITEKEY'], 'itekey');
                                    foreach ($itevpadett_tab as $itevpadett_rec) {
                                        $itepas_vaipasso_rec = $this->praLib->GetItepas($itevpadett_rec['ITEVPA'], 'itekey');
                                        $mess .= "Il passo di destinazione ({$itevpadett_rec['ITEVPADESC']}) ha sequenza: {$itepas_vaipasso_rec['ITESEQ']} - ";
                                        $mess .= $itepas_vaipasso_rec['ITEDES'] . '<br>';
                                        $arrayMsg["Vai alla risposta '{$itevpadett_rec['ITEVPADESC']}'"] = array(
                                            'id' => $this->nameForm . '_ConfermaVaiPasso' . $itepas_vaipasso_rec['ROWID'],
                                            'model' => $this->nameForm,
                                            'metaData' => "extraData: { id: '{$this->nameForm}_ConfermaVaiPasso', rowid: '{$itepas_vaipasso_rec['ROWID']}' }"
                                        );
                                    }
                                }

                                if (count($arrayMsg) > 1) {
                                    Out::msgQuestion('Info Passo', $mess, $arrayMsg);
                                }
                                break;
                            case 'TEMPLATE':
                                $itepas_rec = $this->praLib->GetItepas($_POST['rowid'], "rowid");
                                if ($itepas_rec['TEMPLATEKEY']) {
                                    $itepas_rec_template = $this->praLib->GetItepas($itepas_rec['TEMPLATEKEY'], "itekey");
                                    $rigaSel = $_POST['rowid'];
                                    $model = 'praPassoProc';
                                    $_POST = array();
                                    $_POST['event'] = 'openform';
                                    $_POST['rowid'] = $itepas_rec_template['ROWID'];
                                    $_POST['page'] = $this->page;
                                    $_POST['modo'] = "edit";
                                    $_POST['perms'] = $this->perms;
                                    $_POST['selRow'] = $rigaSel;
                                    $_POST[$model . '_returnModel'] = $this->nameForm;
                                    $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                                    $_POST[$model . '_title'] = 'Gestione Passo.....';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                        }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::hide($this->divDup);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        $sql = $this->CreaSql();
                        $result_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
                        if (!$result_tab) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::show($this->nameForm . '_Duplica');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnapra);
                            TableView::reload($this->gridAnapra);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANAPRA[PRANUM]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divDup);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneClassOnLine");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneRequisiti");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNormativa");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneParametri");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneWorkflow");
                        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneGruppi");
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANAPRA[PRANUM]');
                        break;
                    case $this->nameForm . '_Aggiungi':
                        /*
                         * Se il codice è valorizatto prendo quello, altrimenti lo calcolo incrementando l'ultimo codice
                         */
                        if ($_POST[$this->nameForm . '_ANAPRA']['PRANUM']) {
                            $codice = $_POST[$this->nameForm . '_ANAPRA']['PRANUM'];
                        } else {
                            $anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(PRANUM) AS PRANUM FROM ANAPRA", false);
                            if ($anapra_rec['PRANUM'] == '999999')
                                $anapra_rec['PRANUM'] = '000000';
                            $codice = $anapra_rec['PRANUM'] + 1;
                        }

                        /*
                         * Verifico l'esistenza del procedimento
                         */
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAPRA WHERE PRANUM = '$codice'", false);
                        if ($anapra_rec) {
                            Out::msgInfo("Inserimento procedimento", "Inserimento fallito. Codice $codice già presente");
                            break;
                        }

                        $_POST[$this->nameForm . '_ANAPRA']['PRANUM'] = $codice;
                        Out::valore($this->nameForm . '_ANAPRA[PRAGIO]', '0');
                        $anapra_rec = array();
                        $anapra_rec = $_POST[$this->nameForm . '_ANAPRA'];
                        $anapra_rec['PRADES__1'] = substr($_POST[$this->nameForm . '_PRADES'], 0, 80);
                        $anapra_rec['PRADES__2'] = substr($_POST[$this->nameForm . '_PRADES'], 80);
                        $anapra_rec = $this->praLib->SetMarcaturaProcedimento($anapra_rec, true);

                        try {
                            $insert_Info = 'Oggetto: Inserimento procedimento n. ' . $codice;
                            if (!$this->insertRecord($this->PRAM_DB, 'ANAPRA', $anapra_rec, $insert_Info)) {
                                Out::msgStop("Errore", 'Inserimento Pratica Fallito.');
                                break;
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore", $e->getMessage());
                            break;
                        }

                        $this->Dettaglio($codice, 'codice');
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $anapra_rec = $_POST[$this->nameForm . '_ANAPRA'];
                        $anapra_rec_orig = $this->praLib->GetAnapra($anapra_rec['ROWID'], "rowid");
                        $tipoEnte = $this->praLib->GetTipoEnte();
                        if ($tipoEnte == "M") {
                            if (!$this->Aggiorna($anapra_rec, true)) {
                                Out::msgStop("Aggiornamento procedimento", "Errore nell'aggiornamento su ANAPRA");
                            }
                        } elseif ($tipoEnte == "S") {
                            $filent_rec = $this->GetFilentMS();
                            if (!$anapra_rec['PRASLAVE']) {
                                if ($filent_rec['MASTER']['FILDE6'] == $anapra_rec_orig['PRAUPDEDITOR']) {
                                    Out::msgQuestion("Aggiornamento procedimento", "L'aggiornamento comporta la personalizzazione del procedimento.<br><br>Confermi l'aggiornamento?", array(
                                        'F8-Annulla' => array('id' => $this->nameForm . '_NoPersonalizza', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Aggiorna' => array('id' => $this->nameForm . '_SiPersonalizza', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                } else {
                                    if (!$this->Aggiorna($anapra_rec, true)) {
                                        Out::msgStop("Aggiornamento procedimento", "Errore nell'aggiornamento su ANAPRA");
                                    }
                                }
                                break;
                            } else {
                                if (!$this->Aggiorna($anapra_rec, false)) {
                                    Out::msgStop("Aggiornamento procedimento", "Errore nell'aggiornamento su ANAPRA");
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        $msg = "<span style=\"font-weight:bold;\">Impossibile cancellare il procedimento perche ci sono i seguenti passi che utilizzano dei passi template.</span>";
                        if (!$this->praLib->CheckUsagePassoTemplate($this->currPranum, $msg)) {
                            break;
                        }
                        $sql = "SELECT * FROM PROGES WHERE GESPRO='" . $_POST[$this->nameForm . '_ANAPRA']['PRANUM'] . "'";
//                        $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//                        if (count($proges_tab) > 0) {
//                            Out::msgStop("ATTENZIONE!", "Il Procedimento è assegnato a Fascicoli Elettronici. Non è possibile eliminarlo.");
//                            break;
//                        }
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del Procedimento " . $_POST[$this->nameForm . '_PRADES'] . "?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaTabellaBDP':
                        Out::msgInput(
                                "Compila i seguenti campi", array(
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'UO Dirigenziale'),
                                'id' => $this->nameForm . '_UOdir',
                                'name' => $this->nameForm . '_UOdir',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35',
                                'maxlength' => '50'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'UO Responsabile'),
                                'id' => $this->nameForm . '_UOres',
                                'name' => $this->nameForm . '_UOres',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35',
                                'maxlength' => '50'),
                            array(
                                'label' => array('style' => "width:100px;", 'value' => 'Soggetto Con<br>Potere Esecutivo'),
                                'id' => $this->nameForm . '_Soggetto',
                                'name' => $this->nameForm . '_Soggetto',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '35',
                                'maxlength' => '50')
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCampi',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                                ), $this->nameForm
                        );
                        break;
                    case $this->nameForm . '_ConfermaTabella':
                        $sql = $this->CreaSqlExcel();
                        $ita_grid01 = new TableView($this->gridAnapra, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('PRANUM');
                        $ita_grid01->setSortOrder('asc');
                        $ita_grid01->exportXLS('', 'procedimenti.xls');
                        break;
                    case $this->nameForm . '_ConfermaDatiAzione':
                        $AzioniFO = new praAzioniFO();
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['CLASSEAZIONE'] = $_POST[$this->nameForm . '_ClasseAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['METODOAZIONE'] = $_POST[$this->nameForm . '_MetodoAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['ERROREAZIONE'] = $_POST[$this->nameForm . '_ErroreAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['OPERAZIONE'] = $AzioniFO->GetDescErroreAzione($_POST[$this->nameForm . '_ErroreAzione']);
                        ;
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;
                    case $this->nameForm . '_ConfermaCampi':
                        if ($_POST[$this->nameForm . "_UOdir"] == "" || $_POST[$this->nameForm . "_UOres"] == "" || $_POST[$this->nameForm . "_Soggetto"] == "") {
                            Out::msgStop("Attenzione!!", "Compilare tutti i campi");
                            break;
                        }
                        $arrayBDP = $this->CreaArrayExcelBDP();
                        $result_tab = $this->elaboraRecordsBDP($arrayBDP);
                        $ita_grid01 = new TableView($this->gridAnapra, array(
                            'arrayTable' => $result_tab,
                        ));
                        $ita_grid01->exportXLS("", 'Banca Dati Procedimenti.xls');
                        break;
                    case $this->nameForm . '_NoPersonalizza':
                        if (!$this->Aggiorna($_POST[$this->nameForm . '_ANAPRA'], true)) {
                            Out::msgStop("Aggiornamento procedimento", "Errore nell'aggiornamento su ANAPRA");
                        }
                        break;
                    case $this->nameForm . '_SiPersonalizza':
                        if (!$this->Aggiorna($_POST[$this->nameForm . '_ANAPRA'], true)) {
                            Out::msgStop("Aggiornamento procedimento", "Errore nell'aggiornamento su ANAPRA");
                        }
                        break;
                    case $this->nameForm . '_ConfermaSlave':
                        $Filent_rec = $this->praLib->GetFilent(1);
                        if ($_POST[$this->nameForm . "_ANAPRA"]["PRASLAVE"] && $Filent_rec['FILDE4'] == "S" && $Filent_rec['FILDE3']) {
                            //Out::block($this->gridPassi);
                            Out::show($this->nameForm . '_divConsulta');
                        } else {
                            //Out::unblock($this->gridPassi);
                            Out::hide($this->nameForm . '_divConsulta');
                        }
                        $this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], '1');
                        $this->GestioneInformativa($_POST[$this->nameForm . '_ANAPRA']);
                        break;
                    case $this->nameForm . '_AnnullaSlave':
                        Out::valore($this->nameForm . "_ANAPRA[PRASLAVE]", "0");
                        $this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], '0');
                        $this->GestioneInformativa($_POST[$this->nameForm . '_ANAPRA']);
                        break;
                    case $this->nameForm . '_ConfermaPersonalizzato':
                        $this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], '0');
                        $this->GestioneInformativa($_POST[$this->nameForm . '_ANAPRA']);
                        //Out::unblock($this->gridPassi);
                        Out::hide($this->nameForm . '_divConsulta');
                        break;
                    case $this->nameForm . '_AnnullaPersonalizzato':
                        Out::valore($this->nameForm . "_ANAPRA[PRASLAVE]", "1");
                        $this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], '1');
                        $this->GestioneInformativa($_POST[$this->nameForm . '_ANAPRA']);
                        break;
                    case $this->nameForm . '_ConfermaCancellaAzione':
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['CLASSEAZIONE'] = '';
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['METODOAZIONE'] = '';
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $anapra_rec = $_POST[$this->nameForm . '_ANAPRA'];
//Cancello passi
                        $this->passi = $this->caricaPassi($this->currPranum);
                        if ($this->passi) {
                            $delete_Info = "Oggetto: Cancellazione passi procedimento " . $this->currPranum;
                            foreach ($this->passi as $passo) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEPAS', $passo['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione del passo  seq " . $passo['ITESEQ'] . " - " . $passo['ITEKEY']);
                                    break;
                                }
                            }
                        }
//Cancello parametri BO
                        $parambo_rec = $this->praLib->GetParamBO($this->currPranum);
                        if ($parambo_rec) {
                            $delete_Info = "Oggetto: Cancellazione Parametri BO procedimento " . $this->currPranum;
                            if (!$this->deleteRecord($this->PRAM_DB, 'PARAMBO', $parambo_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Errore", "Errore in cancellazione Parametri BO per anafrafica PRANUM " . $this->currPranum);
                                break;
                            }
                        }

//cancello allegati
                        if ($this->allegati) {
                            foreach ($this->allegati as $allegato) {
                                if (!@unlink($allegato['FILEPATH'])) {
                                    Out::msgStop("ATTENZIONE!", "Errore in cancellazione file.");
                                    break;
                                } else {
                                    $Anpdoc_tab = $this->praLib->GetAnpdoc($this->currPranum, 'codice', true);
                                    if ($Anpdoc_tab) {
                                        $delete_Info = "Oggetto: Cancellazione allegati procedimento " . $this->currPranum;
                                        foreach ($Anpdoc_tab as $key => $Anpdoc_rec) {
                                            if (!$this->deleteRecord($this->PRAM_DB, 'ANPDOC', $Anpdoc_rec['ROWID'], $delete_Info)) {
                                                Out::msgStop("Errore", "Errore in cancellazione allegato " . $Anpdoc_rec['ANPFIL'] . " - " . $Anpdoc_rec['ANPKEY']);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

//Cancello dati aggiuntivi
                        $Itedag_tab = $this->praLib->GetItedag($this->currPranum, 'codice', true);
                        if ($Itedag_tab) {
                            $delete_Info = "Oggetto: Cancellazione dati aggiuntivi procedimento " . $this->currPranum;
                            foreach ($Itedag_tab as $Itedag_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEDAG', $Itedag_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione dato aggiuntivo " . $Itedag_rec['ITDKEY']);
                                    break;
                                }
                            }
                        }

//Cancello Requisiti
                        $Itereq_tab = $this->praLib->GetItereq($this->currPranum, 'codice', true);
                        if ($Itereq_tab) {
                            $delete_Info = "Oggetto: Cancellazione requisiti procedimento " . $this->currPranum;
                            foreach ($Itereq_tab as $Itereq_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEREQ', $Itereq_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione dato aggiuntivo " . $Itereq_rec['REQCOD']);
                                    break;
                                }
                            }
                        }

//Cancello Normative
                        $Itenor_tab = $this->praLib->GetItenor($this->currPranum, 'codice', true);
                        if ($Itenor_tab) {
                            $delete_Info = "Oggetto: Cancellazione normative procedimento " . $this->currPranum;
                            foreach ($Itenor_tab as $Itenor_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITENOR', $Itenor_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione dato aggiuntivo " . $Itenor_rec['NORCOD']);
                                    break;
                                }
                            }
                        }
//Cancello Eventi
                        $Iteevt_tab = $this->praLib->GetIteevt($this->currPranum, 'codice', true);
                        if ($Iteevt_tab) {
                            $delete_Info = "Oggetto: Cancellazione eventi procedimento " . $this->currPranum;
                            foreach ($Iteevt_tab as $Iteevt_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEEVT', $Iteevt_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione evento " . $Iteevt_rec['IEVCOD']);
                                    break;
                                }
                            }
                        }

//Cancello Procedimenti obbligatori
                        $Itepraobb_tab = $this->praLib->GetItePraObb($this->currPranum, 'codice', true);
                        if ($Itepraobb_tab) {
                            $delete_Info = "Oggetto: Cancellazione procedimenti obbligatori del proc " . $this->currPranum;
                            foreach ($Itepraobb_tab as $Itepraobb_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITEPRAOBB', $Itepraobb_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione proc Obbl " . $Itepraobb_rec['OBBSUBPRA']);
                                    break;
                                }
                            }
                        }

                        //Cancello azioni
                        $Praazioni_tab = $this->praLib->GetPraazioni($this->currPranum, 'codice', true);
                        if ($Praazioni_tab) {
                            $delete_Info = "Oggetto: Cancellazione azioni procedimento " . $this->currPranum;
                            foreach ($Praazioni_tab as $Praazioni_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione azione " . $Praazioni_rec['CODICEAZIONE']);
                                    break;
                                }
                            }
                        }

                        //Cancello ITECONTROLLI
                        $Itecontrolli_tab = $this->praLib->GetItecontrolli($this->currPranum, "itecod");
                        if ($Itecontrolli_tab) {
                            $delete_Info = "Oggetto: Cancellazione controlli procedimento " . $this->currPranum;
                            foreach ($Itecontrolli_tab as $Itecontrolli_rec) {
                                if (!$this->deleteRecord($this->PRAM_DB, 'ITECONTROLLI', $Itecontrolli_rec['ROWID'], $delete_Info)) {
                                    Out::msgStop("Errore", "Errore in cancellazione controlli " . $Itecontrolli_rec['ITEKEY']);
                                    break;
                                }
                            }
                        }

                        /*
                         * Cancella ITEDEST
                         */
                        $praLibPasso = new praLibPasso();
                        if (!$praLibPasso->deleteRecordItedest($this->currPranum, "itecod")) {
                            Out::msgStop("Errore", "Errore in cancellazione destinatari del proc " . $this->currPranum);
                            break;
                        }

                        /*
                         * Cancello il procedimento
                         */
                        $delete_Info = 'Oggetto: Cancellazione procedimento ' . $anapra_rec['PRANUM'];
                        if ($this->deleteRecord($this->PRAM_DB, 'ANAPRA', $anapra_rec['ROWID'], $delete_Info)) {
                            Out::msgInfo("Cancellazzione procedimento", "Procedimento $this->currPranum cancellato correttamente");
                            $this->OpenRicerca();
                        }
                        break;



                    case $this->nameForm . '_Duplica':
                        Out::hide($this->divRic);
                        Out::show($this->divDup);
                        Out::hide($this->divRis);
                        Out::hide($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Nuovo');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_DuplicaRec');
                        Out::setFocus('', $this->nameForm . '_daNum');
                        break;
                    case $this->nameForm . '_DuplicaRec':
                        $daNum = $_POST[$this->nameForm . '_daNum'];
                        $aNum = $_POST[$this->nameForm . '_aNum'];
                        if (trim($daNum) != "") {
                            $daNum = str_repeat("0", 6 - strlen(trim($daNum))) . trim($daNum);
                            Out::valore($this->nameForm . '_daNum', $daNum);

//Se Procediemnto sorgente è centralizzato si prende dal MAster
                            $Anapra_da_ctrPraslave = $this->praLib->GetAnapra($daNum);
                            $tipoEnte = $this->praLib->GetTipoEnte();
                            $praLib = $this->praLib;
                            $PRAM_DB = $praLib->getPRAMDB();
                            if ($tipoEnte == "S" && $Anapra_da_ctrPraslave['PRASLAVE'] == 1) {
                                $dbsuffix = $this->praLib->GetEnteMaster();
                                $praLib = new praLib($dbsuffix);
                                $PRAM_DB = $praLib->getPRAMDB($dbsuffix);
                            }
                            $Anapra_da = $praLib->GetAnapra($daNum);
//

                            if (!$Anapra_da) {
                                Out::msgStop("Attenzione!", 'Numero di Procedimento non presente. Inserirne uno esistente.');
                                Out::setFocus('', $this->nameForm . '_daNum');
                                break;
                            }
                        } else {
                            Out::msgStop("ATTENZIONE.", "Impostare il numero di Procedimento da Duplicare");
                            Out::setFocus('', $this->nameForm . '_daNum');
                            break;
                        }
                        if (trim($aNum) != "") {
                            $aNum = str_repeat("0", 6 - strlen(trim($aNum))) . trim($aNum);
                            Out::valore($this->nameForm . '_aNum', $aNum);
                            $Anapra_a = $this->praLib->GetAnapra($aNum);
                            if ($Anapra_a) {
                                Out::msgStop("Attenzione!", 'Numero di Procedimento presente. Inserirne uno non esistente.');
                                Out::setFocus('', $this->nameForm . '_aNum');
                                break;
                            }
                        }
                        if ($aNum == '') {
                            $sql = "SELECT MAX(PRANUM) AS PRANUM FROM ANAPRA";
                            $anapra_max = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            if ($anapra_max['PRANUM'] == '999999')
                                $anapra_max['PRANUM'] = '000000';
                            $aNum = $anapra_max['PRANUM'] + 1;
                            $aNum = str_repeat("0", 6 - strlen(trim($aNum))) . trim($aNum);
                        }
                        $Anapra_da['PRANUM'] = $aNum;
                        unset($Anapra_da['ROWID']);
//$Anapra_da['PRATES'] = ''; // mm 21062012
//if (!$this->DuplicaPassi($daNum, $aNum)) {
                        if (!$this->DuplicaPassi($daNum, $aNum, $PRAM_DB)) {
                            $stop = true;
                            break;
                        }
                        if (!$this->DuplicaAllegati($daNum, $aNum, $PRAM_DB)) {
                            $stop = true;
                            break;
                        }
                        if (!$this->DuplicaEventi($daNum, $aNum)) {
                            $stop = true;
                            break;
                        }
                        if (!$this->DuplicaParametri($daNum, $aNum, $PRAM_DB)) {
                            $stop = true;
                            break;
                        }
                        $insert_Info = 'Oggetto: Duplicazione da: ' . $daNum . " in " . $aNum;
                        if (!$this->insertRecord($this->PRAM_DB, 'ANAPRA', $Anapra_da, $insert_Info)) {
                            Out::msgStop("Errore", 'Inserimento Pratica Fallito.');
                            $stop = true;
                            break;
                        }
                        if ($stop == true)
                            break;
                        Out::msgInfo('Duplica.', 'Duplicazione avvenuta con successo');
                        Out::valore($this->nameForm . '_pranum', $aNum);
                        Out::valore($this->nameForm . '_aPranum', $aNum);
                        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRAGIO, TIPDES, " .
                                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
                                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
                                FROM ANAPRA ANAPRA 
                                LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
                                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                                LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
                                WHERE PRANUM BETWEEN '" . $aNum . "' AND '" . $aNum . "'";
//                        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRAGIO, TIPDES, " .
//                                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
//                                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
//                                FROM ANAPRA ANAPRA LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
//                                LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD 
//                                WHERE PRANUM BETWEEN '" . $aNum . "' AND '" . $aNum . "'";
                        $ita_grid01 = new TableView($this->gridAnapra, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(14);
                        $ita_grid01->setSortIndex('PRANUM');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $ita_grid01->getDataArray();
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::hide($this->divDup);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::show($this->nameForm . '_Duplica');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnapra);
                        }
                        break;
                    case $this->nameForm . '_Scanner':
                        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
                        $this->ApriScanner();
                        break;
                    case $this->nameForm . '_FileLocale':
                        $this->AllegaFile();
                        break;
                    case $this->nameForm . '_CreaTesto':
                        $this->openEdit("returnTextEdit");
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::hide($this->divDup);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::show($this->nameForm . '_Duplica');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridAnapra);
                        break;
                    case $this->nameForm . '_ConfermaCancPasso':
                        $itepas_rec = $this->praLib->GetItepas($this->rowidAppoggio, 'rowid');
                        $retAntecedente = $this->praLib->CheckAntecedenteITEPAS($itepas_rec);
                        if (!$retAntecedente) {
                            Out::msgInfo("Cancellazione!!!", "Il passo selezionato, risulta essere un antecedente.<br>Cancellare prima i passi collegati");
                            break;
                        }

                        $arrayPassi = array($itepas_rec);

                        $praLibPasso = new praLibPasso();
                        $praLibPasso->cancellaPassi($arrayPassi, $_POST[$this->nameForm . '_ANAPRA']['ROWID'], $this->currPranum, $this->passi);

                        if ($praLibPasso->getErrCode()) {
                            Out::msgStop("Errore", $praLibPasso->getErrMessage());
                            break;
                        }

                        /*
                          if (!$this->deleteRecordItedag($itepas_rec['ITEKEY'])) {
                          Out::msgStop("Cancellazione Passo", "Errore cancellazione dati aggiunti passo " . $itepas_rec['ITESEQ']);
                          break;
                          }
                          if (!$this->deleteRecordPraazioni($itepas_rec['ITEKEY'])) {
                          Out::msgStop("Cancellazione Passo", "Errore cancellazione azione passo " . $itepas_rec['ITESEQ']);
                          break;
                          }

                          if (!$this->deleteRecordItedest($itepas_rec['ITEKEY'])) {
                          Out::msgStop("Cancellazione Passo", "Errore cancellazione destinatari passo " . $itepas_rec['ITESEQ']);
                          break;
                          }
                          $delete_Info = "Oggetto: Cancellazione Passo seq " . $itepas_rec['ITESEQ'];
                          if (!$this->deleteRecord($this->PRAM_DB, 'ITEPAS', $this->rowidAppoggio, $delete_Info)) {
                          Out::msgStop("ATTENZIONE!", "Errore in cancellazione del Passo seq " . $itepas_rec['ITESEQ']);
                          break;
                          }
                          $Anapra_rec = array('ROWID' => $_POST[$this->nameForm . '_ANAPRA']['ROWID']);
                          $Anapra_rec = $this->praLib->SetMarcaturaProcedimento($Anapra_rec);
                          $update_Info = 'Oggetto: Aggiornamento marcatura procedimento n. ' . $itepas_rec['ITECOD'];
                          if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $Anapra_rec, $update_Info)) {
                          Out::msgStop("Cancellazione Passo", "Errore in aggiornamento Marcatura Procedimento");
                          }

                          $this->praLib->ordinaPassiProc($this->currPranum);

                         * 
                         */
                        $this->passi = $this->caricaPassi($this->currPranum);
                        if ($this->passi) {
                            $this->CaricaGriglia($this->gridPassi, $this->passi);
                            Out::show($this->gridPassi);
                        }
                        $giorni = $this->CalcolaGiorni($this->currPranum);
                        Out::valore($this->nameForm . '_ANAPRA[PRAGIO]', $giorni);
                        $this->visualizzaMarcatura($Anapra_rec);
                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        if (array_key_exists($this->rowidAppoggio, $this->allegati) == true) {
                            if (file_exists($this->allegati[$this->rowidAppoggio]['FILEPATH'])) {
                                if (!@unlink($this->allegati[$this->rowidAppoggio]['FILEPATH'])) {
                                    Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                                    break;
                                }
                            }
                            if ($this->allegati[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = "Oggetto: Cancellazione allegato " . $this->allegati[$this->rowidAppoggio]['FILENAME'];
                                if (!$this->deleteRecord($this->PRAM_DB, 'ANPDOC', $this->allegati[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Gestione File", "Errore in cancellazione allegato " . $this->allegati[$this->rowidAppoggio]['FILENAME']);
                                    break;
                                }
                            }

                            $Anapra_rec = array('ROWID' => $_POST[$this->nameForm . '_ANAPRA']['ROWID']);
                            $Anapra_rec = $this->praLib->SetMarcaturaProcedimento($Anapra_rec);
                            $update_Info = 'Oggetto: Aggiornamento marcatura procedimento n. ' . $itepas_rec['ITECOD'];
                            if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $Anapra_rec, $update_Info)) {
                                Out::msgStop("Cancellazione Passi", "Errore in aggiornamento Marcatura Procedimento");
                            }
                            $Anapra_rec = $this->praLib->GetAnapra($_POST[$this->nameForm . "_ANAPRA"]['ROWID'], 'rowid');
                            $this->visualizzaMarcatura($Anapra_rec);

                            unset($this->allegati[$this->rowidAppoggio]);
                            $this->riordinaAllegati();

                            $this->CaricaGriglia($this->gridAllegati, $this->allegati);
                        }
                        break;
                    case $this->nameForm . '_StampaDatiAgg':
                        Out::msgInput(
                                'Stampa Campi Aggiuntivi', array(array(
                                'label' => array('value' => 'Solo Campi Mappati'),
                                'id' => $this->nameForm . '_soloMappati',
                                'name' => $this->nameForm . '_soloMappati',
                                'type' => 'checkbox',),
                                ), array(
                            'F5-Generale' => array('id' => $this->nameForm . '_StampaAggGen', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F1-Dettagliata' => array('id' => $this->nameForm . '_StampaAggDett', 'model' => $this->nameForm, 'shortCut' => "f1")
                                ), $this->nameForm . "_divGestione"
                        );
                        break;
                    case $this->nameForm . '_CercaDatiAgg':
                        Out::msgInput(
                                'Cerca Campo Aggiuntivo', array(array(
                                'label' => array('value' => 'Nome Campo'),
                                'id' => $this->nameForm . '_nomeDatoAgg',
                                'size' => '30',
                                'name' => $this->nameForm . '_nomeDatoAgg',),
                                ), array(
                            'F1-Cerca' => array('id' => $this->nameForm . '_cercaDatoAgg', 'model' => $this->nameForm, 'shortCut' => "f1")
                                ), $this->nameForm . "_divGestione"
                        );
                        break;
                    case $this->nameForm . '_cercaDatoAgg':
                        if (!$_POST[$this->nameForm . "_nomeDatoAgg"]) {
                            Out::msgStop("Cerca dati Aggiuntivi", "Inserire un nome per il dato");
                            break;
                        }
                        praRic::praRicDatiAggiuntivi($this->currPranum, $_POST[$this->nameForm . "_nomeDatoAgg"], $this->nameForm);
                        break;
                    case $this->nameForm . '_StampaAggGen':
                        $dati_tab = $this->praLib->getGenericTab($this->CreaSqlStampaDatiAgg($_POST[$this->nameForm . "_soloMappati"]));
                        if (!$dati_tab) {
                            Out::msgInfo("Stampa dati Aggiuntivi", "Dati non trovati");
                            break;
                        }
                        $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                        $Iteevt_tab = $this->praLib->GetIteevt($this->currPranum, "codice", true);
                        $strEventi = "";
                        foreach ($Iteevt_tab as $Iteevt_rec) {
                            $Anaeventi_rec = $this->praLib->GetAnaeventi($Iteevt_rec['IEVCOD']);
                            $Anaset_rec = $this->praLib->GetAnaset($Iteevt_rec['IEVSTT']);
                            $Anaatt_rec = $this->praLib->GetAnaatt($Iteevt_rec['IEVATT'], 'condizionato', false, $Iteevt_rec['IEVSTT']);
                            $strEventi .= $Anaeventi_rec['EVTDESCR'] . " - " . $Anaset_rec["SETDES"] . " - " . $Anaatt_rec["ATTDES"] . "\n";
                        }
                        $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Sql" => $this->CreaSqlStampaDatiAgg($_POST[$this->nameForm . "_soloMappati"]),
                            "Ente" => $parametriEnte_rec['DENOMINAZIONE'],
                            "proc_codice" => $this->currPranum,
                            "proc_descrizione" => $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4'],
                            "proc_classificazione" => $strEventi
                        );

                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praStampaAggGen', $parameters);
                        break;
                    case $this->nameForm . '_StampaAggDett':
                        $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                        $Iteevt_tab = $this->praLib->GetIteevt($this->currPranum, "codice", true);
                        $strEventi = "";
                        foreach ($Iteevt_tab as $Iteevt_rec) {
                            $Anaeventi_rec = $this->praLib->GetAnaeventi($Iteevt_rec['IEVCOD']);
                            $Anaset_rec = $this->praLib->GetAnaset($Iteevt_rec['IEVSTT']);
                            $Anaatt_rec = $this->praLib->GetAnaatt($Iteevt_rec['IEVATT'], 'condizionato', false, $Iteevt_rec['IEVSTT']);
                            $strEventi .= $Anaeventi_rec['EVTDESCR'] . " - " . $Anaset_rec["SETDES"] . " - " . $Anaatt_rec["ATTDES"] . "\n";
                        }
                        $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Sql" => $this->CreaSqlStampaDatiAgg($_POST[$this->nameForm . "_soloMappati"], true),
                            "Ente" => $parametriEnte_rec['DENOMINAZIONE'],
                            "proc_codice" => $this->currPranum,
                            "proc_descrizione" => $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4'],
                            "proc_classificazione" => $strEventi
                        );
                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praStampaAggDett', $parameters);
                        break;
                    case $this->nameForm . '_FileLocaleTesto':
                        Out::msgQuestion("Upload.", "Vuoi caricare un documento interno o uno esterno?", array(
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_goDettaglio':
                        $this->Dettaglio($this->rowidAppoggio);
                        break;
                    case $this->nameForm . '_UploadEsterno':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadEsterno";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
// MM 21062012
//                    case $this->nameForm . '_UploadInterno':
//                        if ($ditta == '')
//                            $ditta = App::$utente->getKey('ditta');
//                        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
//                        if (!is_dir($destinazione)) {
//                            Out::msgStop("Errore.", 'Directory non presente!');
//                            break;
//                        }
//                        $matriceSelezionati = array();
//                        $matriceSelezionati = $this->GetFileList($destinazione);
//                        if ($matriceSelezionati) {
//                            praRic::ricImmProcedimenti($matriceSelezionati, $this->nameForm, 'returnIndicePRATES', 'Testi Disponibili');
//                        } else {
//                            Out::msgInfo('Attenzione.', 'Nessun Testo presente in elenco. Caricare manualmente il Testo.');
//                        }
//                        break;
                    case $this->nameForm . '_pratip_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "RICERCA CATEGORIE", '', "pratip");
                        break;
                    case $this->nameForm . '_ANAPRA[PRATIP]_butt':
                        praRic::praRicAnatip($this->PRAM_DB, $this->nameForm, "RICERCA CATEGORIE", '', "ANAPRA[PRATIP]");
                        break;
                    case $this->nameForm . '_prares_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "prares");
                        break;
                    case $this->nameForm . '_ANAPRA[PRARES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "ANAPRA[PRARES]");
                        break;
                    case $this->nameForm . '_prastt_butt':
                        praRic::praRicAnaset("praPro", "", 'prastt');
                        break;
                    case $this->nameForm . '_ANAPRA[PRASTT]_butt':
                        praRic::praRicAnaset("praPro", "", 'ANAPRA[PRASTT]');
                        break;
                    case $this->nameForm . '_praatt_butt':
                        if ($_POST[$this->nameForm . '_prastt']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_prastt'] . "'";
                            praRic::praRicAnaatt("praPro", $where, 'praatt');
                        } else {
                            Out::msgInfo("Attenzione!!!", "Scegliere prima un settore");
                        }
                        break;
                    case $this->nameForm . '_pratsp_butt':
                        praRic::praRicAnatsp($this->nameForm, '', 'pratsp');
                        break;
                    case $this->nameForm . '_ANAPRA[PRAATT]_butt':
                        $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_ANAPRA']['PRASTT'] . "'";
                        praRic::praRicAnaatt("praPro", $where, 'ANAPRA[PRAATT]');
                        break;
                    case $this->nameForm . '_ANAPRA[PRATSP]_butt':
                        praRic::praRicAnatsp($this->nameForm, '', 'ANAPRA[PRATSP]');
                        break;
                    case $this->nameForm . '_ANAPRA[PRAMOD]_butt':
                        if ($_POST[$this->nameForm . '_ANAPRA']['PRAMOD']) {
                            Out::msgInfo("Attenzione", "Prima di Scegliere un nuovo Controller Annullare quello già dichiarato.");
                        } else {
                            praControllers::ricControllers("praPro", "Elenco Controllers", 'ANAPRA[PRAMOD]');
                        }
                        break;
                    case $this->nameForm . '_evento_butt':
                        praRic::ricAnaeventi($this->nameForm);
                        break;
                    case $this->nameForm . '_CancellaPassi':
                        praRic::praPassiSelezionati($this->passi, $this->nameForm, "can", "Cancellazione Passi");
                        break;
                    case $this->nameForm . '_ConfermaSelCan':
                        $errCanc = '';
                        $errCanc = $this->controlloPassidaCancellare($this->passiSel);
                        if ($errCanc) {
                            $msgCanc = '';
                            foreach ($errCanc as $key => $value) {
                                $msgCanc = $msgCanc . 'il passo ' . $value['PASSO_CANC'] . ' che stai cancellando è relativo della domanda ' . $value['PASSO_DOMA'] . '<br>';
                            }
                            $msgCanc = $msgCanc . 'I riferimenti nelle domande verranno tolti.<br>Vuoi confermare la cancellazione ?';
                            Out::msgQuestion("Attenzione", $msgCanc, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaPassi', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        } else {
                            $this->cancellaPassiSel();
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancellaPassi':
                        $this->cancellaPassiSel();
                        break;
                    case $this->nameForm . '_ExportPassi':
                        $msg = "Scelgli il tipo di esportazione.";
                        Out::msgQuestion("Attenzione", $msg, array(
                            'Annulla' => array('id' => $this->nameForm . "_annullaEsportaPassi", 'model' => $this->nameForm),
                            'Esporta Intero Procedimento' => array('id' => $this->nameForm . "_ExportPassiTotale2", 'model' => $this->nameForm),
                            'Esporta Procedimento Simple' => array('id' => $this->nameForm . "_ExportProcSimple", 'model' => $this->nameForm),
                            'Esporta Passi' => array('id' => $this->nameForm . "_ExportPassiDettaglio", 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_ExportPassiDettaglio':
                        praRic::praPassiSelezionati($this->passi, $this->nameForm, "exp", "Esportazione Passi");
                        break;
                    case $this->nameForm . '_ExportPassiTotale':
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibUpgrade.class.php';
                        $praLibUpgrade = new praLibUpgrade();
                        //$xmlFile = $this->praLib->creaXMLProcedimento($this->currPranum);
                        $xmlFile = $praLibUpgrade->creaXMLProcedimento($this->currPranum);
                        if (!$xmlFile) {
                            Out::msgStop("Errore Creazione Xml", $praLibUpgrade->getErrMessage());
                            break;
                        }
                        if (file_exists($xmlFile)) {
                            $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            "exportProc_" . $this->currPranum . "_" . $Anapra_rec['PRAUPDDATE'] . $Anapra_rec['PRAUPDTIME'] . ".xml", $xmlFile, true
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ExportPassiTotale2':
                        $evtCustom = $this->praLib->checkEventiCustom($this->eventi);
                        if ($evtCustom) {
                            Out::msgInfo("Esportazione Procedimento", "L'evento con codice $evtCustom risulta essere personalizzato.<br>Verificare prima di esportare il procedimento.");
                            break;
                        }
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibUpgrade2.class.php';
                        $praLibUpgrade = new praLibUpgrade();
                        $xmlFile = $praLibUpgrade->creaXMLProcedimento($this->currPranum);
                        if (!$xmlFile) {
                            Out::msgStop("Errore Creazione Xml", $praLibUpgrade->getErrMessage());
                            break;
                        }
                        if (file_exists($xmlFile)) {
                            $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            "exportProc_" . $this->currPranum . "_" . $Anapra_rec['PRAUPDDATE'] . $Anapra_rec['PRAUPDTIME'] . ".xml", $xmlFile, true
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ExportProcSimple':
                        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibUpgrade2.class.php';
                        $praLibUpgrade = new praLibUpgrade();
                        $xmlFile = $praLibUpgrade->creaXMLProcSimple($this->currPranum);
                        if (!$xmlFile) {
                            Out::msgStop("Errore Creazione Xml", $praLibUpgrade->getErrMessage());
                            break;
                        }
                        if (file_exists($xmlFile)) {
                            $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            "exportProcSimple_" . $this->currPranum . "_" . $Anapra_rec['PRAUPDDATE'] . $Anapra_rec['PRAUPDTIME'] . ".xml", $xmlFile, true
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_returnExportPassi':
                        $passiSel = $this->passiSel;
                        if ($passiSel) {
                            $XML_ExportPassiTmp = $this->praLib->creaXML($passiSel, $this->currPranum);
                            $XML_ExportPassi = utf8_encode($XML_ExportPassiTmp);
                            //$nome_file = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . '-exportPassi.xml';
                            if (!is_dir(itaLib::getPrivateUploadPath())) {
                                if (!itaLib::createPrivateUploadPath()) {
                                    Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                    return false;
                                }
                            }
                            $nome_file = itaLib::getAppsTempPath() . '/' . 'exportPassi.xml';
                            if (file_put_contents($nome_file, $XML_ExportPassi)) {
                                Out::openDocument(
                                        utiDownload::getUrl(
                                                'exportPassi_' . $this->currPranum . '.xml', $nome_file, true
                                        )
                                );
                            }
                        }
                        break;
                    case $this->nameForm . '_ConsultaPassi':
                        $Filent_rec = $this->praLib->GetFilent(1);
                        if ($Filent_rec['FILDE3']) {
                            praRic::praRicItepas($this->nameForm, "ITEPAS", " WHERE ITECOD = $this->currPranum", "passiMaster", "", "asc", $Filent_rec['FILDE3']);
                        } else {
                            Out::msgStop("Attenzione", "Ente Master non definito");
                        }
                        break;
                    case $this->nameForm . '_ConsultaRequisiti':
                        $Filent_rec = $this->praLib->GetFilent(1);
                        if ($Filent_rec['FILDE3']) {
                            praRic::praRicRequisiti($this->nameForm, " WHERE ITEPRA = $this->currPranum", $Filent_rec['FILDE3']);
                        } else {
                            Out::msgStop("Attenzione", "Ente Master non definito");
                        }
                        break;
                    case $this->nameForm . '_ConsultaNormativa':
                        $Filent_rec = $this->praLib->GetFilent(1);
                        if ($Filent_rec['FILDE3']) {
                            praRic::praRicNormative($this->nameForm, " WHERE ITEPRA = $this->currPranum", $Filent_rec['FILDE3']);
                        } else {
                            Out::msgStop("Attenzione", "Ente Master non definito");
                        }
                        break;
                    case $this->nameForm . '_ConsultaDiscipline':
                        $Filent_rec = $this->praLib->GetFilent(1);
                        if ($Filent_rec['FILDE3']) {
                            praRic::praRicDiscipline($this->nameForm, " WHERE ITEPRA = $this->currPranum", $Filent_rec['FILDE3']);
                        } else {
                            Out::msgStop("Attenzione", "Ente Master non definito");
                        }
                        break;
                    case $this->nameForm . '_ImportPassi':
                        if (!$this->passi) {
                            $model = 'utiUploadDiag';
                            $_POST = Array();
                            $_POST['event'] = 'openform';
                            $_POST[$model . '_returnModel'] = $this->nameForm;
                            $_POST[$model . '_returnEvent'] = "returnUploadXML";
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        } else {
//    $where=" WHERE ITECOD = '".$this->currPranum."' AND ITEPUB <> ''";
                            $where = " WHERE ITECOD = '" . $this->currPranum . "'";
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '', 'Inserire i nuovi passi dopo la sequenza ....');
                        }
                        break;
                    case $this->nameForm . '_AggiornaPMarche':
                        $codiceProcediMarche = $_POST[$this->nameForm . "_ANAPRA"]['PRANUMEST'];
                        if (!$codiceProcediMarche) {
                            Out::msgStop("Attenzione!!", "Inserire il codice procedimento esterno per procedi marche");
                            break;
                        }
                        Out::msgQuestion("Aggiornamento Procedimento", "Vuoi aggiornare il procedimento seguente con ProcediMarche?", array(
                            'F5-Annulla' => array('id' => $this->nameForm . '_AnnullaPrMarche', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F1-Conferma' => array('id' => $this->nameForm . '_ConfermaPrMarche', 'model' => $this->nameForm, 'shortCut' => "f1")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaPrMarche':
                        include_once(ITA_LIB_PATH . '/itaPHPWSProcedimarche/itaWSProcedimarcheClient.class.php');
                        include_once(ITA_LIB_PATH . '/itaPHPWSProcedimarche/itaMyTipoProEnte.class.php');
                        $WSClient = new itaWSProcedimarcheClient();
                        $this->setClientConfig($WSClient);
                        /*
                         * i parametri per questa chiamata sono
                         * id
                         * password
                         * cf_ente
                         * arrayTipoProEnte
                         * 
                         * id, password e cf_ente vengono settati in automatico da setClientConfig.
                         * Il client è predisposto in ogni caso ad accettare dentro $param anche questi tre parametri oltre ad arrayTipoProEnte
                         */
                        $param = array(
                            'arrayTipoProEnte' => array()
                        );

                        /*
                         * arrayTipoProEnte è un array di oggetti itaMyProTipoEnte
                         * 
                         */

                        $MyTipoProEnte = new itaMyTipoProEnte();
                        $MyTipoProEnte->setDS_AnniConservazione($_POST[$this->name_form . '_DS_AnniConservazione']);
                        $MyTipoProEnte->setDS_CF_Ente($_POST[$this->name_form . '_DS_CF_Ente']);
                        $MyTipoProEnte->setDS_CodiceClassifica($_POST[$this->name_form . '_DS_CodiceClassifica']);
                        $MyTipoProEnte->setDS_CodiceProcedimentoEnte($_POST[$this->name_form . '_DS_CodiceProcedimentoEnte']);
                        $MyTipoProEnte->setDS_CustomerSatisfation($_POST[$this->name_form . '_DS_CustomerSatisfation']);
                        $MyTipoProEnte->setDS_ID_ProcedimentoPaleo($_POST[$this->name_form . '_DS_ID_ProcedimentoPaleo']);
                        $MyTipoProEnte->setDS_ID_SerieArchivistica($_POST[$this->name_form . '_DS_ID_SerieArchivistica']);
                        $MyTipoProEnte->setDS_ID_Sistema($_POST[$this->name_form . '_DS_ID_Sistema']);
                        $MyTipoProEnte->setDS_ID_TipoFascicolo($_POST[$this->name_form . '_DS_ID_TipoFascicolo']);
                        $MyTipoProEnte->setDS_ID_TipoProcedimento($_POST[$this->name_form . '_DS_ID_TipoProcedimento']);
                        $MyTipoProEnte->setDS_LinkModulistica($_POST[$this->name_form . '_DS_LinkModulistica']);
                        $MyTipoProEnte->setDS_LinkServizio($_POST[$this->name_form . '_DS_LinkServizio']);
                        $MyTipoProEnte->setDS_ModalitaPagamenti($_POST[$this->name_form . '_DS_ModalitaPagamenti']);
                        $MyTipoProEnte->setDS_ModalitaRichiestaInfo($_POST[$this->name_form . '_DS_ModalitaRichiestaInfo']);
                        $MyTipoProEnte->setDS_NomeCognomeSostituto($_POST[$this->name_form . '_DS_NomeCognomeSostituto']);
                        $MyTipoProEnte->setDS_NomeProcedimento($_POST[$this->name_form . '_DS_NomeProcedimento']);
                        $MyTipoProEnte->setDS_RespProcCognome($_POST[$this->name_form . '_DS_RespProcCognome']);
                        $MyTipoProEnte->setDS_RespProcNome($_POST[$this->name_form . '_DS_RespProcNome']);
                        $MyTipoProEnte->setDS_RisorseFinanziarie($_POST[$this->name_form . '_DS_RisorseFinanziarie']);
                        $MyTipoProEnte->setDS_UO_CodiceIpaCompetente($_POST[$this->name_form . '_DS_UO_CodiceIpaCompetente']);
                        $MyTipoProEnte->setDS_UO_CodiceIpaIstruttoria($_POST[$this->name_form . '_DS_UO_CodiceIpaIstruttoria']);
                        $MyTipoProEnte->setDS_UO_Competente($_POST[$this->name_form . '_DS_UO_Competente']);
                        $MyTipoProEnte->setDS_UO_CompetenzaIstruttoria($_POST[$this->name_form . '_DS_UO_CompetenzaIstruttoria']);
                        $MyTipoProEnte->setDS_UO_RecapitiIstruttoria($_POST[$this->name_form . '_DS_UO_RecapitiIstruttoria']);

                        $arrayTipoProEnte[] = $MyTipoProEnte;

                        $param = array(
                            'arrayTipoProEnte' => $arrayTipoProEnte
                        );

                        $ret = $WSClient->ws_SaveTipiProcedimentoEnte($param);
                        if (!$ret) {
                            if ($WSClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
                            } elseif ($WSClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $ret = $WSClient->getResult();
                        $risultato = print_r($ret, true);
                        Out::msgInfo("SaveTipiProcedimentoEnte Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->nameForm . '_ConfermaVaiPasso':
                        $model = 'praPassoProc';
                        $rowid = $_POST['rowid'];

                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                        $_POST[$model . '_title'] = 'Gestione Passo';

                        /* @var $praPassoProc praPassoProc */
                        itaLib::openForm($model);
                        $praPassoProc = itaModel::getInstance($model);
                        $praPassoProc->setEvent('openform');
                        $praPassoProc->parseEvent();
                        break;

                    case $this->nameForm . '_ConfermaInserimentoGruppo':
                        //
                        // Controlli
                        //
                        if (!$this->formData[$this->nameForm . "_NuovaDescrizione"]) {
                            $this->RichiediNuovoGruppo();
                            break;
                        }

                        $iteDiagGruppi_rec = array();
                        $iteDiagGruppi_rec['PRANUM'] = $this->currPranum;
                        $iteDiagGruppi_rec['DESCRIZIONE'] = $this->formData[$this->nameForm . "_NuovaDescrizione"];
                        $iteDiagGruppi_rec['STATO'] = $this->formData[$this->nameForm . "_StatoTipo"];

                        $insert_Info = 'Nuovo Gruppo inserito: ' . $iteDiagGruppi_rec['PRANUM'] . " " . $iteDiagGruppi_rec['DESCRIZIONE'];
                        if (!$this->insertRecord($this->PRAM_DB, 'ITEDIAGGRUPPI', $iteDiagGruppi_rec, $insert_Info)) {
                            Out::msgStop("Errore", "Aggiunta Nuovo Gruppo Fallita.");
                            break;
                        }

                        $this->caricaGrigliaGruppi($this->currPranum);
                        break;

                    case $this->nameForm . '_AggiornaGruppo':
                        $this->aggiornaGruppo($this->formData);
                        $this->caricaGrigliaGruppi($this->currPranum);
                        break;

                    case $this->nameForm . '_ConfermaCancellaGruppo':

                        //Cancellare i passi associati
                        $itediagpassigruppi_tab = $this->praLib->GetItediagPassiGruppi($this->curRowidComposizione);
                        if ($itediagpassigruppi_tab) {
                            foreach ($itediagpassigruppi_tab as $passo_rec) {
                                $delete_Info = 'Oggetto: Cancellazione ITEDIAGPASSIGRUPPI con ROW_ID = ' . $passo_rec['ROW_ID'];
                                $this->deleteRecord($this->PRAM_DB, "ITEDIAGPASSIGRUPPI", $passo_rec['ROW_ID'], $delete_Info, "ROW_ID");
                            }
                        }

                        $delete_Info = 'Oggetto: Cancellazione ITEDIAGGRUPPI ' . $this->curRowidComposizione;
                        $this->deleteRecord($this->PRAM_DB, "ITEDIAGGRUPPI", $this->curRowidComposizione, $delete_Info, "ROW_ID");
                        $this->caricaGrigliaGruppi($this->currPranum);
                        break;


                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAPRA[PRATPR]':
                        $this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], $_POST[$this->nameForm . "_ANAPRA"]["PRASLAVE"]);
                        break;
                    case $this->nameForm . '_ANAPRA[PRASLAVE]':
                        if ($_POST[$this->nameForm . "_ANAPRA"]["PRASLAVE"]) {
                            $msg = "<br><br><span style=\"font-size:1.3em;color:red;\">Hai selezionato l'uso del modello in modo centralizzato:</span><br><br><span style=\"font-size:1.1em;\">La struttura del procedimento sarà solo visualizzabile.<br>La compilazione on-line qualora prevista utilizzerà la struttura del corrispondente procedimento nell'ente Master</span><br><br><span style=\"font-size:1.3em;\">Confermi?</span>";
                            $conferma = "ConfermaSlave";
                            $annulla = "AnnullaSlave";
                        } else {
//$this->enableTabs($_POST[$this->nameForm . "_ANAPRA"]["PRATPR"], $_POST[$this->nameForm . "_ANAPRA"]["PRASLAVE"]);
                            $msg = "<br><br><span style=\"font-size:1.3em;color:red;\">Hai selezionato l'uso del procedimento in modo personalizzato:</span><br><br><span style=\"font-size:1.1em;\">All'aggiornamento del procedimento l'autore e la versione saranno cambiati.<br>I successivi aggiornamenti da parte dell'autore originale non saranno più considerati.</span><br><br><span style=\"font-size:1.3em;\">Confermi?</span>";
                            $conferma = "ConfermaPersonalizzato";
                            $annulla = "AnnullaPersonalizzato";
                        }
                        Out::msgQuestion("Attenzione", $msg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . "_$annulla", 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . "_$conferma", 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;

                    case $this->nameForm . '_ANAPRA[PRAINF]':
                        $this->GestioneInformativa($_POST[$this->nameForm . '_ANAPRA']);
                        break;
                }
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_pranum':
                        $codice = $_POST[$this->nameForm . '_pranum'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_pranum', $codice);
                        }
                        break;
                    case $this->nameForm . '_aPranum':
                        $codice = $_POST[$this->nameForm . '_aPranum'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_aPranum', $codice);
                            if (trim($_POST[$this->nameForm . '_pranum']) == $codice) {
                                $Anapra_rec = $this->praLib->GetAnapra($codice);
                                if ($Anapra_rec) {
                                    $this->Dettaglio($Anapra_rec['ROWID']);
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_daNum':
                        $codice = $_POST[$this->nameForm . '_daNum'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if (!$Anapra_rec) {
                                Out::msgStop("Attenzione!", 'Numero di Procedimento non presente. Inserirne uno esistente.');
                                Out::setFocus('', $this->nameForm . '_daNum');
                            }
                            Out::valore($this->nameForm . '_daNum', $codice);
                        }
                        break;
                    case $this->nameForm . '_aNum':
                        $codice = $_POST[$this->nameForm . '_aNum'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if ($Anapra_rec) {
                                Out::msgStop("Attenzione!", 'Numero di Procedimento presente. Inserirne uno non esistente.');
                                Out::setFocus('', $this->nameForm . '_aNum');
                            }
                            Out::valore($this->nameForm . '_aNum', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANAPRA[PRANUM]':
                        $codice = $_POST[$this->nameForm . '_ANAPRA']['PRANUM'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAPRA[PRANUM]', $codice);
                        }
                        break;
                    case $this->nameForm . '_prares':
                        $codice = $_POST[$this->nameForm . '_prares'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec, 'ricerca');
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAPRA[PRARES]':
                        $codice = $_POST[$this->nameForm . '_ANAPRA']['PRARES'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec);
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
                    case $this->nameForm . '_ANAPRA[PRASTT]':
                        $Anaset_rec = $this->praLib->GetAnaset($_POST[$this->nameForm . '_ANAPRA']['PRASTT']);
                        if ($Anaset_rec) {
                            Out::valore($this->nameForm . '_ANAPRA[PRASTT]', $Anaset_rec["SETCOD"]);
                            Out::valore($this->nameForm . '_SettoreAttivita', $Anaset_rec["SETDES"]);
                        }
                        break;

                    case $this->nameForm . '_ANAPRA[PRAATT]':
                        $Anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_ANAPRA']['PRAATT'], 'condizionato', false, $_POST[$this->nameForm . '_ANAPRA']['PRASTT']);
                        if ($Anaatt_rec) {
                            Out::valore($this->nameForm . '_ANAPRA[PRAATT]', $Anaatt_rec["ATTCOD"]);
                            Out::valore($this->nameForm . '_Attivita', $Anaatt_rec["ATTDES"]);
                        }
                        break;
                    case $this->nameForm . '_praatt':
                        $Anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_praatt'], 'condizionato', false, $_POST[$this->nameForm . '_prastt']);
                        if ($Anaatt_rec) {
                            Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                            Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                        }
                        break;
                    case $this->nameForm . '_ANAPRA[PRATSP]':
                        $codice = $_POST[$this->nameForm . '_ANAPRA']['PRATSP'];
                        Out::valore($this->nameForm . '_Sportello', '');
                        if ($codice != '') {
                            $Anatsp_rec = $this->praLib->GetAnatsp($codice);
                            if ($Anatsp_rec) {
                                Out::valore($this->nameForm . '_ANAPRA[PRATSP]', $Anatsp_rec['TSPCOD']);
                                Out::valore($this->nameForm . '_Sportello', $Anatsp_rec['TSPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAPRA[PRATIP]':
                        $codice = $_POST[$this->nameForm . '_ANAPRA']['PRATIP'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anatip_rec = $this->praLib->GetAnatip($codice);
                            if ($Anatip_rec) {
                                Out::valore($this->nameForm . '_ANAPRA[PRATIP]', $Anatip_rec['TIPCOD']);
                                Out::valore($this->nameForm . '_Tipologia', $Anatip_rec['TIPDES']);
                            }
                        }
                        break;
                    case $this->nameForm . '_evento':
                        $codice = $_POST[$this->nameForm . '_evento'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodeEvento($codice);
                        }
                        break;
                }
                break;
            case 'returnItepas':
                switch ($_POST['retid']) {
                    case "passiMaster":
                        $Filent_rec = $this->praLib->GetFilent(1);
                        $praLib_slave = $this->praLib;
                        $praLib_master = new praLib($Filent_rec['FILDE3']);
                        $itepas_rec = $praLib_master->GetItepas($_POST['retKey'], 'rowid');
                        $model = 'praPassoProc';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $itepas_rec['ROWID'];
                        $_POST['enteMaster'] = $Filent_rec['FILDE3'];
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    default:
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                                $this->returnToParent();
                            }
                        }

                        if ($_POST['retKey']) {
                            $itepas_rec = $this->praLib->GetItepas($_POST['retKey'], 'rowid');
                            $this->insertTo = $itepas_rec['ITESEQ'];
                        } else {
                            $this->insertTo = 0;
                        }
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadXML";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'returnPassiSel':
                $this->passiSel = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($selRows as $rowid) {
                    foreach ($this->passi as $keyPasso => $passo) {
                        if ($passo["ROWID"] == $rowid) {
                            $this->passiSel[] = $this->passi[$keyPasso];
                        }
                    }
                }
                switch ($_POST['retid']) {
                    case "can":
                        $title = "Cancellazione Passi!!";
                        $msgCancella = "<br><span style=\"color:red;font-weight: bold;font-size:1.3em;\">I passi selezionati verranno cancellati</span>";
                        $confermaSel = "ConfermaSelCan";
                        $usage = false;
                        $antecedente = false;
                        foreach ($this->passiSel as $passo) {
                            if (!$this->praLib->CheckUsagePassoTemplate($passo['ITEKEY'])) {
                                $usage = true;
                            }
                        }
                        foreach ($this->passiSel as $passo) {
                            $retAntecedente = $this->praLib->CheckAntecedenteITEPAS($passo);
                            if (!$retAntecedente) {
                                $antecedente = true;
                                break;
                            }
                        }
                        break;
                    case "exp":
                        foreach ($this->passiSel as $passo) {
                            $passiCollegati = $this->praLib->AddPassiAntecedenti($passo['ROWID']);
                            if ($passiCollegati) {
                                $this->passiSel = array_merge($this->passiSel, $passiCollegati);
                            }
                        }
                        //$this->passiSel = $this->praLib->,($this->passiSel);
                        $title = "Esportazione Passi!!";
                        $confermaSel = "returnExportPassi";
                        break;
                }
                if ($usage) {
                    break;
                }
                if ($antecedente) {
                    Out::msgInfo("Cancellazione!!!", "Tra i passi selezionati, risulta esserci 1 o più antecedenti.<br>Cancellare prima i passi collegati");
                    break;
                }
                if (count($this->passiSel)) {
                    Out::msgQuestion($title, "Hai selezionato " . count($this->passiSel) . " passi. Vuoi Continuare?.$msgCancella", array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSel', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $this->nameForm . "_$confermaSel", 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
                break;
            case 'returnUploadXML':
                $praLibPasso = new praLibPasso();
                $XMLpassi = $_POST['uploadedFile'];
                if (!$praLibPasso->importaPassiXML($this->currPranum, $XMLpassi, $this->insertTo)) {
                    Out::msgStop("Errore", $praLibPasso->getErrMessage());
                    break;
                }
                $this->caricaPassi($this->currPranum);
                break;
            case 'returnFileFromTwain':
                $this->SalvaScanner();
                break;
            case 'returnPraPassoProc':
                $rigaSel = $_POST['selRow'];
                $this->passi = $this->caricaPassi($this->currPranum);
                $giorni = $this->CalcolaGiorni($this->currPranum);
                Out::valore($this->nameForm . '_ANAPRA[PRAGIO]', $giorni);
                $Anapra_rec = $this->praLib->GetAnapra($this->currPranum, 'codice');
                $this->visualizzaMarcatura($Anapra_rec);
                Out::codice("jQuery('#$this->gridPassi').jqGrid('setSelection','$rigaSel');");
                break;
            case 'returnAnatip':
                if ($_POST['retid'] == 'ANAPRA[PRATIP]') {
                    $Anatip_rec = $this->praLib->GetAnatip($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_ANAPRA[PRATIP]', $Anatip_rec['TIPCOD']);
                    Out::valore($this->nameForm . '_Tipologia', $Anatip_rec['TIPDES']);
                } else if ($_POST['retid'] == 'pratip') {
                    $Anatip_rec = $this->praLib->GetAnatip($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_pratip', $Anatip_rec['TIPCOD']);
                    Out::valore($this->nameForm . '_tipologia', $Anatip_rec['TIPDES']);
                }
                break;
            case 'returnAnaset':
                if ($_POST['retid'] == 'ANAPRA[PRASTT]') {
                    $Anaset_rec = $this->praLib->GetAnaset($_POST["retKey"], 'rowid');
                    if ($Anaset_rec) {
                        Out::valore($this->nameForm . '_ANAPRA[PRASTT]', $Anaset_rec["SETCOD"]);
                        Out::valore($this->nameForm . '_SettoreAttivita', $Anaset_rec["SETDES"]);
                    }
                } else if ($_POST['retid'] == 'prastt') {
                    $Anaset_rec = $this->praLib->GetAnaset($_POST["retKey"], 'rowid');
                    if ($Anaset_rec) {
                        Out::valore($this->nameForm . '_prastt', $Anaset_rec["SETCOD"]);
                        Out::valore($this->nameForm . '_settoreAttivita', $Anaset_rec["SETDES"]);
                    }
                }
                break;
            case 'returnAnaatt':
                if ($_POST['retid'] == 'ANAPRA[PRAATT]') {
                    $Anaatt_rec = $this->praLib->GetAnaatt($_POST["retKey"], 'rowid');
                    if ($Anaatt_rec) {
                        Out::valore($this->nameForm . '_ANAPRA[PRAATT]', $Anaatt_rec["ATTCOD"]);
                        Out::valore($this->nameForm . '_Attivita', $Anaatt_rec["ATTDES"]);
                    }
                } else if ($_POST['retid'] == 'praatt') {
                    $Anaatt_rec = $this->praLib->GetAnaatt($_POST["retKey"], 'rowid');
                    if ($Anaatt_rec) {
                        Out::valore($this->nameForm . '_praatt', $Anaatt_rec["ATTCOD"]);
                        Out::valore($this->nameForm . '_attivita', $Anaatt_rec["ATTDES"]);
                    }
                }
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    if ($_POST['retid'] == 'ANAPRA[PRARES]') {
                        $this->DecodResponsabile($Ananom_rec);
                    } else if ($_POST['retid'] == 'prares') {
                        $this->DecodResponsabile($Ananom_rec, 'ricerca');
                    }
                }
                break;
            case 'returnAnareq':
                $Anareq_rec = $this->praLib->GetAnareq($_POST["retKey"], 'rowid');
                if ($Anareq_rec) {
                    $this->requisiti[] = $Anareq_rec;
                    $this->CaricaGriglia($this->gridRequisiti, $this->requisiti);
                }
                break;
            case 'returnAnanor':
                $Ananor_rec = $this->praLib->GetAnanor($_POST["retKey"], 'rowid');
                if ($Ananor_rec) {
                    $this->normative[] = $Ananor_rec;
                    $this->CaricaGriglia($this->gridNormative, $this->normative);
                }
                break;
            case 'returnAnadis':
                $Anadis_rec = $this->praLib->GetAnadis($_POST["retKey"], 'rowid');
                if ($Anadis_rec) {
                    $this->discipline[] = $Anadis_rec;
                    $this->CaricaGriglia($this->gridDiscipline, $this->discipline);
                }
                break;
            case "returnTextEdit":
                if (!@is_dir(itaLib::getPrivateUploadPath())) {
                    if (!itaLib::createPrivateUploadPath()) {
                        Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                        $this->returnToParent();
                    }
                }
                $origFile = "file.html";
                $origFile_path = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "/$origFile";
                $content = $_POST['returnText'];
                $randName = md5(rand() * time()) . "." . pathinfo($origFile_path, PATHINFO_EXTENSION);
                $file = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "/$randName";
                $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                $FileOpen = fopen($file, "w");
                fwrite($FileOpen, $content);
                fclose($FileOpen);

                $this->allegati[] = array(
                    'ROWID' => 0,
                    'SEQUENZA' => 999999,
                    'FILEPATH' => $destFile,
                    'FILENAME' => $randName,
                    'FILEINFO' => "File Originale: $origFile",
                    'CLASSE' => ""
                );
                $this->riordinaAllegati();
                $this->CaricaGriglia($this->gridAllegati, $this->allegati);
                Out::setFocus('', $this->nameForm . '_wrapper');
                break;
            case "returnSaveEdit":
                $doc = $this->allegati[$_POST['rowidText']];
                $newContent = $_POST['returnText'];
                if (!file_put_contents($doc['FILEPATH'], $newContent)) {
                    Out::msgStop("Attenzione", "Errore in aggiornamento del file " . $doc['FILENAME']);
                } else {
                    Out::msgInfo("Aggiornamento Testo", "Testo " . $doc['CODICE'] . " aggiornato correttamente");
                }
                break;
            case "returnAnatsppratsp":
                $Anatsp_rec = $this->praLib->GetAnatsp($_POST["retKey"], 'rowid');
                if ($Anatsp_rec) {
                    Out::valore($this->nameForm . '_pratsp', $Anatsp_rec['TSPCOD']);
                    Out::valore($this->nameForm . '_sportello', $Anatsp_rec['TSPDES']);
                    Out::setFocus('', $this->nameForm . '_pratsp');
                }
                break;
            case "returnAnatspANAPRA[PRATSP]":
                $Anatsp_rec = $this->praLib->GetAnatsp($_POST["retKey"], 'rowid');
                if ($Anatsp_rec) {
                    Out::valore($this->nameForm . '_ANAPRA[PRATSP]', $Anatsp_rec['TSPCOD']);
                    Out::valore($this->nameForm . '_Sportello', $Anatsp_rec['TSPDES']);
                    Out::setFocus('', $this->nameForm . '_ANAPRA[PRATSP]');
                }
                break;
            case 'returnItereq':
                $Filent_rec = $this->praLib->GetFilent(1);
                $praLib_slave = $this->praLib;
                $praLib_master = new praLib($Filent_rec['FILDE3']);
                $Itereq_rec = $praLib_master->GetItereq($_POST['retKey'], "rowid");
                $Anareq_rec = $praLib_master->GetAnareq($Itereq_rec['REQCOD']);
                if ($Anareq_rec['REQFIL']) {
                    $ditta = $Filent_rec['FILDE3'];
                    $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
                    if (!is_dir($destinazione)) {
                        Out::msgStop("Errore.", 'Directory non presente!');
                        break;
                    }
                    Out::openDocument(
                            utiDownload::getUrl(
                                    $Anareq_rec['REQFIL'], $destinazione . $Anareq_rec['REQFIL']
                            )
                    );
                }
                break;
            case 'returnItenor':
                $Filent_rec = $this->praLib->GetFilent(1);
                $praLib_slave = $this->praLib;
                $praLib_master = new praLib($Filent_rec['FILDE3']);
                $Itenor_rec = $praLib_master->GetItenor($_POST['retKey'], "rowid");
                $Ananor_rec = $praLib_master->GetAnanor($Itenor_rec['NORCOD']);
                if ($Ananor_rec['NORFIL']) {
                    $ditta = $Filent_rec['FILDE3'];
                    $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/normativa/';
                    if (!is_dir($destinazione)) {
                        Out::msgStop("Errore.", 'Directory non presente!');
                        break;
                    }
                    Out::openDocument(
                            utiDownload::getUrl(
                                    $Ananor_rec['NORFIL'], $destinazione . $Ananor_rec['NORFIL']
                            )
                    );
                }
                break;
            case 'returnItedis':
                $Filent_rec = $this->praLib->GetFilent(1);
                $praLib_slave = $this->praLib;
                $praLib_master = new praLib($Filent_rec['FILDE3']);
                $Itedis_rec = $praLib_master->GetItedis($_POST['retKey'], "rowid");
                $Anadis_rec = $praLib_master->GetAnadis($Itenor_rec['NORCOD']);
                if ($Anadis_rec['DISFIL']) {
                    $ditta = $Filent_rec['FILDE3'];
                    $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/discipline/';
                    if (!is_dir($destinazione)) {
                        Out::msgStop("Errore.", 'Directory non presente!');
                        break;
                    }
                    Out::openDocument(
                            utiDownload::getUrl(
                                    $Anadis_rec['DISFIL'], $destinazione . $Anadis_rec['DISFIL']
                            )
                    );
                }
                break;
            case "returnDatiAgg":
                $itedag_rec = $this->praLib->GetItedag($_POST['retKey'], "rowid");
                $itepas_rec = $this->praLib->GetItepas($itedag_rec['ITEKEY'], "itekey");
                $model = 'praPassoProc';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['rowid'] = $itepas_rec['ROWID'];
                $_POST['modo'] = "edit";
                $_POST['ricDatAgg'] = true;
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnMethod'] = 'returnPraPassoProc';
                $_POST[$model . '_title'] = 'Gestione Passo.....';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;

            case "returnPraIteObb":
                $returnObbligatorio = $_POST['obbligatorio'];
                $rigaSel = $_POST['rigaSel'];
                if ($rigaSel != '') {
                    $this->obbligatori[$rigaSel]['OBBEVCOD'] = $returnObbligatorio['OBBEVCOD'];
                    $this->obbligatori[$rigaSel]['OBBSUBPRA'] = $returnObbligatorio['OBBSUBPRA'];
                    $this->obbligatori[$rigaSel]['OBBSUBEVCOD'] = $returnObbligatorio['OBBSUBEVCOD'];
                    $this->obbligatori[$rigaSel]['OBBEXPRCTR'] = $returnObbligatorio['OBBEXPRCTR'];
                } else {
                    if ($returnObbligatorio['OBBEVCOD'] . $returnObbligatorio['OBBSUBPRA'] . $returnObbligatorio['OBBSUBEVCOD'] . $returnObbligatorio['OBBEXPRCTR'] != '') {
                        $this->obbligatori[] = $returnObbligatorio;
                    }
                }
                $this->CaricaGriglia($this->gridObbligatori, $this->elaboraRecordsObbligatori($this->obbligatori));
                break;

            case "returnPraIteevt":
                if (isset($_POST[$this->nameForm . '_ITEEVT'])) {
                    if ($this->returnId || $this->returnId === '0') {
                        $this->eventi[$this->returnId] = $_POST[$this->nameForm . '_ITEEVT'];
                    } else {
                        $this->eventi[] = $_POST[$this->nameForm . '_ITEEVT'];
                    }
                    $this->CaricaGriglia($this->gridEventi, $this->elaboraRecordsEventi($this->eventi));
                }
                break;
//            case "returnPraIteevt":
//                if (isset($_POST[$this->nameForm . '_ITEEVT'])) {
//                    $Anaeventi_rec = $this->praLib->GetAnaeventi($_POST[$this->nameForm . '_ITEEVT']['IEVCOD']);
//                    $anatsp_rec = $this->praLib->GetAnatsp($_POST[$this->nameForm . '_ITEEVT']['IEVTSP']);
//                    $anaset_rec = $this->praLib->GetAnaset($_POST[$this->nameForm . '_ITEEVT']['IEVSTT']);
//                    $anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_ITEEVT']['IEVATT']);
//                    //
//                    $Anaeventi_rec['EVTSEGCOMUNICA'] = praLib::$TIPO_SEGNALAZIONE[$Anaeventi_rec['EVTSEGCOMUNICA']];
//                    $Anaeventi_rec['CLASSIFICAZIONE'] = "<div style=\"height:55px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anatsp_rec['TSPDES'] . "</div><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div></div>";
//                    if ($this->returnId || $this->returnId === '0') {
//                        $this->eventi[$this->returnId] = array_merge($Anaeventi_rec, $_POST[$this->nameForm . '_ITEEVT']);
//                    } else {
//                        $this->eventi[] = array_merge($Anaeventi_rec, $_POST[$this->nameForm . '_ITEEVT']);
//                    }
//                    $this->CaricaGriglia($this->gridEventi, $this->eventi);
//                }
//                break;
            case "returnAnaeventi":
                $this->DecodeEvento($_POST['retKey'], 'rowid');
                break;

            case 'returnAggiungiPassi':

                praRic::praPassiSelezionati($this->passi, $this->nameForm, "exp", "Esportazione Passi", "ITEPAS", "true", "returnPassiXGruppo");

                break;

            case 'returnCancellaPasso':

                $passo_rec = $this->formData['returnData'];

                if ($passo_rec) {
                    $delete_Info = 'Oggetto: Cancellazione ITEDIAGPASSIGRUPPI con ROW_ID = ' . $passo_rec['ROW_ID'];
                    $this->deleteRecord($this->PRAM_DB, "ITEDIAGPASSIGRUPPI", $passo_rec['ROW_ID'], $delete_Info, "ROW_ID");
                }
                $this->refreshGrigliaGruppiPassi();

                break;

            case 'returnCloseRiportaPassi':
                $this->caricaGrigliaGruppi($this->currPranum);

                break;

            case 'returnPassiXGruppo':
                $this->passiSel = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($selRows as $rowid) {
                    foreach ($this->passi as $keyPasso => $passo) {
                        if ($passo["ROWID"] == $rowid) {
                            $this->passiSel[] = $this->passi[$keyPasso];
                        }
                    }
                }

                //Scorre i passi selezionati. se passo non presente nel gruppo si aggiunge
                foreach ($this->passiSel as $passo) {
                    $sql = "SELECT * FROM ITEDIAGPASSIGRUPPI WHERE ROW_ID_ITEDIAGGRUPPI = '$this->curRowidComposizione' "
                            . " AND ITEDIAGPASSIGRUPPI.ITEKEY = '" . $passo['ITEKEY'] . "' ";
                    $iteDiagPassiGruppi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                    if (!$iteDiagPassiGruppi) {
                        // Si aggiunge record di ITEDIAGPASSIGRUPPI
                        $iteDiagPassiGruppi_rec = array();
                        $iteDiagPassiGruppi_rec['ITEKEY'] = $passo['ITEKEY'];
                        $iteDiagPassiGruppi_rec['ROW_ID_ITEDIAGGRUPPI'] = $this->curRowidComposizione;

                        $insert_Info = 'Inserimento nuova associazione Gruppi-Passi con ID Gruppo: ' . $iteDiagPassiGruppi_rec['ROW_ID_ITEDIAGGRUPPI'] . "  Itekey: " . $passo['ITEKEY'];
                        if (!$this->insertRecord($this->PRAM_DB, 'ITEDIAGPASSIGRUPPI', $iteDiagPassiGruppi_rec, $insert_Info)) {
                            Out::msgStop("Errore", "Aggiunta Nuova Associazione Passo-Gruppo Fallita.");
                            break;
                        }

                        //Rinfrescare la griglia del model utiJqGridCustom
                        $this->refreshGrigliaGruppiPassi();
                    }
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_passiSel');
        App::$utente->removeKey($this->nameForm . '_requisiti');
        App::$utente->removeKey($this->nameForm . '_eventi');
        App::$utente->removeKey($this->nameForm . '_normative');
        App::$utente->removeKey($this->nameForm . '_discipline');
        App::$utente->removeKey($this->nameForm . '_obbligatori');
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_insertTo');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_autoSearch');
        App::$utente->removeKey($this->nameForm . '_autoDescr');
        App::$utente->removeKey($this->nameForm . '_azioniFO');
        App::$utente->removeKey($this->nameForm . '_praProcDatiAggiuntiviFormname');
        App::$utente->removeKey($this->nameForm . '_praProcDiagrammaFormname');
        App::$utente->removeKey($this->nameForm . "_curRowidComposizione");
        App::$utente->removeKey($this->nameForm . "_passiGruppiFormName");

        $this->close = true;
        itaLib::deletePrivateUploadPath();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divDup);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Duplica');
        Out::show($this->nameForm);
        itaLib::deletePrivateUploadPath();

        $enteMaster = $this->praLib->GetEnteMaster();
        if (!$enteMaster) {
            Out::hide($this->nameForm . "_ANAPRA[PRASLAVE]_field");
        }

        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::setFocus('', $this->nameForm . '_pranum');
        TableView::clearToolbar($this->gridAnapra);
    }

    function AzzeraVariabili($svuotaElenco = true) {
        Out::clearFields($this->nameForm, $this->divDup);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        if ($svuotaElenco == true) {
            Out::clearFields($this->nameForm, $this->divRic);
            TableView::disableEvents($this->gridAnapra);
            TableView::clearGrid($this->gridAnapra);
        }
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridGruppi);
        TableView::clearGrid($this->gridGruppi);
        itaLib::clearAppsTempPath();
        $this->allegati = array();
        $this->passi = array();
        $this->currPranum = '';
        $this->rowidAppoggio = '';

        $this->eventi = array();
        $this->azioniFO = array();
        TableView::clearGrid($this->gridEventi);
        $this->curRowidComposizione = '';
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Duplica');
        Out::hide($this->nameForm . '_DuplicaRec');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_ElaboraKey');
        Out::hide($this->nameForm . '_CancellaPassi');
        Out::hide($this->nameForm . '_ExportPassi');
        Out::hide($this->nameForm . '_ImportPassi');
        Out::hide($this->nameForm . "_StampaDatiAgg");
        Out::hide($this->nameForm . "_CercaDatiAgg");
        Out::hide($this->nameForm . "_AggiornaPMarche");
    }

    public function CaricaSubform() {
        /*
         * Carico pannello Dati aggiuntivi
         */
        Out::html($this->nameForm . '_paneDatiAggiuntivi', '');

        /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
        $praProcDatiAggiuntivi = itaFormHelper::innerForm('praProcDatiAggiuntivi', $this->nameForm . '_paneDatiAggiuntivi');
        $praProcDatiAggiuntivi->setEvent('openform');
        $praProcDatiAggiuntivi->parseEvent();

        $this->praProcDatiAggiuntiviFormname = $praProcDatiAggiuntivi->getNameForm();

        /*
         * Carico pannello workflow
         */
        Out::html($this->nameForm . '_paneWorkflow', '');

        /* @var $praProcWorkflow praProcWorkflow */
        $praProcWorkflow = itaFormHelper::innerForm('praProcDiagramma', $this->nameForm . '_paneWorkflow');
        $praProcWorkflow->setEvent('openform');
        $praProcWorkflow->parseEvent();

        $this->praProcDiagrammaFormname = $praProcWorkflow->getNameForm();

        /*
         * carico pannello AZIONI FO
         */

        $generator = new itaGenerator();
        $retHtml = $generator->getModelHTML('pradivAzioniFO', false, $this->nameForm, true);
        Out::html($this->nameForm . '_divAzioni', $retHtml);
    }

    public function CreaSqlStampaDatiAgg($soloMappati, $dett = false) {
        $join = $joinMappati = $campi = "";
        $order = "ITEDAG.ITEKEY ASC,
                  ITEDAG.ITDSEQ ASC";

        if ($soloMappati == 1) {
            $joinMappati = "INNER JOIN PRAIDC ON ITEDAG.ITDKEY=PRAIDC.IDCKEY";
        }

        if ($dett == true) {
            $join = "INNER JOIN ITEPAS ON ITEPAS.ITEKEY=ITEDAG.ITEKEY";
            $campi = "ITEPAS.ITESEQ,
                      ITEPAS.ITEDES,
                      ITEPAS.ITEKEY,";
            $order = "ITEPAS.ITESEQ ASC,
                      ITEDAG.ITDSEQ ASC";
        }
        $sql = "SELECT
                  $campi
                  ITEDAG.ITDKEY,
                  ITEDAG.ITDDES,
                  ITEDAG.ITDALIAS
                FROM
                  ITEDAG
                  $join
                  $joinMappati
                WHERE
                  ITEDAG.ITECOD = '$this->currPranum'
                ORDER BY
                  $order
            ";
        App::log($sql);
        return $sql;
    }

    public function CreaSqlExcel() {
        $sql = "SELECT PRANUM AS CODICE," .
                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
                $this->PRAM_DB->strConcat("EVTCOD", "'-'", "EVTDESCR") . " AS EVENTO," .
                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE,
                       PRAGIO AS GIORNI,
                       TSPDES AS SPORTELLO,
                       SETDES AS SETTORE,
                       ATTDES AS ATTIVITA,
                       PRADVA AS VALIADITA_DA,
                       PRAAVA AS VALIDITA_A
             FROM ANAPRA ANAPRA
            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
            LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
            LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
            LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
            LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
            LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
            LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
            WHERE PRANUM=PRANUM";
//        $sql = "SELECT PRANUM AS CODICE,
//                       PRAGIO AS GIORNI,
//                       TIPDES AS TIPOLOGIA,
//                       SETDES AS SETTORE,
//                       TSPDES AS SPORTELLO,
//                       ATTDES AS ATTIVITA,
//                       PRADVA AS VALIADITA_DA,
//                       PRAAVA AS VALIDITA_A," .
//                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
//                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
//            FROM ANAPRA ANAPRA
//            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
//            LEFT OUTER JOIN ANASET ANASET ON ANAPRA.PRASTT=ANASET.SETCOD
//            LEFT OUTER JOIN ANAATT ANAATT ON ANAPRA.PRAATT=ANAATT.ATTCOD
//            LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
//            LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD WHERE PRANUM=PRANUM";
        if ($_POST[$this->nameForm . '_pranum'] != "") {
            $daPranum = $_POST[$this->nameForm . '_pranum'];
            if ($_POST[$this->nameForm . '_aPranum'] != '') {
                $aPranum = $_POST[$this->nameForm . '_aPranum'];
            } else {
                $aPranum = $daPranum;
            }
            $sql .= " AND (PRANUM BETWEEN '$daPranum' AND '$aPranum')";
        }
        if ($_POST[$this->nameForm . '_prades'] != "") {
            $sql .= " AND " . $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2") . " LIKE '%" . addslashes($_POST[$this->nameForm . '_prades']) . "%'";
        }
        if ($_POST[$this->nameForm . '_prares'] != "") {
            $sql .= " AND PRARES='" . $_POST[$this->nameForm . '_prares'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            $sql .= " AND IEVTSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            $sql .= " AND IEVTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            $sql .= " AND IEVSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            $sql .= " AND IEVATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }
        if ($_POST[$this->nameForm . '_daValidita'] && $_POST[$this->nameForm . '_aValidita']) {
            $sql .= " AND (PRADVA >= '" . $_POST[$this->nameForm . '_daValidita'] . "' AND PRAAVA<='" . $_POST[$this->nameForm . '_aValidita'] . "')";
        }

        if ($_POST[$this->nameForm . '_testo'] != "") {
            $sql .= " AND PRANUM IN (SELECT ITECOD FROM ITEPAS WHERE " . $this->PRAM_DB->strLower('ITEWRD') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_testo']) . "%')";
        }

        if ($_POST[$this->nameForm . '_soloValidi'] == 1) {
            $sql .= " AND PRAAVA = '' AND PRAOFFLINE = 0";
        }
        return $sql;
    }

    public function CreaArrayExcelBDP() {
        //$sql = "SELECT * FROM ANAPRA WHERE 1 ";
        $sql = "SELECT
                    ANAPRA.*,
                    ITEEVT.IEVTSP,
                    ITEEVT.IEVTIP,
                    ITEEVT.IEVSTT,
                    ITEEVT.IEVATT
                FROM
                    ANAPRA ANAPRA
                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
                LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
                LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
                WHERE 
                    1 ";
        if ($_POST[$this->nameForm . '_pranum'] != "") {
            $daPranum = $_POST[$this->nameForm . '_pranum'];
            if ($_POST[$this->nameForm . '_aPranum'] != '') {
                $aPranum = $_POST[$this->nameForm . '_aPranum'];
            } else {
                $aPranum = $daPranum;
            }
            $sql .= " AND (PRANUM BETWEEN '$daPranum' AND '$aPranum')";
        }
        if ($_POST[$this->nameForm . '_prades'] != "") {
            $sql .= " AND " . $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2") . " LIKE '%" . addslashes($_POST[$this->nameForm . '_prades']) . "%'";
        }
        if ($_POST[$this->nameForm . '_prares'] != "") {
            $sql .= " AND PRARES='" . $_POST[$this->nameForm . '_prares'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            $sql .= " AND IEVTSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            $sql .= " AND IEVTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            $sql .= " AND IEVSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            $sql .= " AND IEVATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }
        $sql .= " ORDER BY PRANUM";
        return ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
    }

    public function CreaSql() {
        $daValidita = $_POST[$this->nameForm . '_daValidita'];
        $aValidita = $_POST[$this->nameForm . '_aValidita'];
        if ($aValidita == "")
            $aValidita = date("Ymd");
        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRAGIO, TIPDES, TSPDES, SETDES, ATTDES,PRADVA, PRAAVA, PRAUPDEDITOR, PRAUPDDATE, " .
                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE," .
                $this->PRAM_DB->strConcat("EVTCOD", "'-'", "EVTDESCR") . " AS EVENTO,
                    GENMETADATA.CHIAVE AS PROCEDIMARCHE
            FROM ANAPRA ANAPRA 
            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
            LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
            LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
            LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
            LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
            LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
            LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
            LEFT OUTER JOIN GENMETADATA ON " . $this->PRAM_DB->strConcat("ITEEVT.ITEPRA", "'-'", "ITEEVT.IEVCOD") . " = GENMETADATA.CHIAVE AND GENMETADATA.CLASSE = 'ITEEVT'
            WHERE PRANUM=PRANUM";
//        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRAGIO, TIPDES, TSPDES, SETDES, ATTDES,PRADVA, PRAAVA, PRAUPDEDITOR, PRAUPDDATE, " .
//                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
//                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
//            FROM ANAPRA ANAPRA 
//            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
//            LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
//            LEFT OUTER JOIN ANASET ANASET ON ANAPRA.PRASTT=ANASET.SETCOD
//            LEFT OUTER JOIN ANAATT ANAATT ON ANAPRA.PRAATT=ANAATT.ATTCOD
//            LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD 
//            LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
//            WHERE PRANUM=PRANUM";
        if ($_POST[$this->nameForm . '_pranum'] != "") {
            $daPranum = $_POST[$this->nameForm . '_pranum'];
            if ($_POST[$this->nameForm . '_aPranum'] != '') {
                $aPranum = $_POST[$this->nameForm . '_aPranum'];
            } else {
                $aPranum = $daPranum;
            }
            $where = " AND (PRANUM BETWEEN '$daPranum' AND '$aPranum')";
        }
        if ($_POST[$this->nameForm . '_prades'] != "") {
            $where .= " AND " . $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2") . " LIKE '%" . addslashes($_POST[$this->nameForm . '_prades']) . "%'";
        }
        if ($_POST[$this->nameForm . '_tipo_procedimento'] != "") {
            if ($_POST[$this->nameForm . '_tipo_procedimento'] == "GENERICO") {
                $where .= " AND PRATPR= ''";
            } else {
                $where .= " AND PRATPR='" . $_POST[$this->nameForm . '_tipo_procedimento'] . "'";
            }
        }
        if ($_POST[$this->nameForm . '_prares'] != "") {
            $where .= " AND PRARES='" . $_POST[$this->nameForm . '_prares'] . "'";
        }
        if ($_POST[$this->nameForm . '_evento'] != "") {
            $where .= " AND IEVCOD='" . $_POST[$this->nameForm . '_evento'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratsp'] != "") {
            //$where .= " AND PRATSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
            $where .= " AND IEVTSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
        }
        if ($_POST[$this->nameForm . '_pratip'] != "") {
            //$where .= " AND PRATIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
            $where .= " AND IEVTIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
        }
        if ($_POST[$this->nameForm . '_prastt'] != "") {
            //$where .= " AND PRASTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
            $where .= " AND IEVSTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
        }
        if ($_POST[$this->nameForm . '_praatt'] != "") {
            //$where .= " AND PRAATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
            $where .= " AND IEVATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
        }
        //if ($daValidita && $aValidita) {
        if ($daValidita) {
            //$sql .= " AND (PRADVA >= '" . $_POST[$this->nameForm . '_daValidita'] . "' AND PRAAVA<='" . $_POST[$this->nameForm . '_aValidita'] . "')";
            $where .= " AND (PRADVA >= '$daValidita' AND PRAAVA<='$aValidita')";
        }

        if ($_POST[$this->nameForm . '_testo'] != "") {
            $where .= " AND PRANUM IN (SELECT ITECOD FROM ITEPAS WHERE " . $this->PRAM_DB->strLower('ITEWRD') . " LIKE '%" . strtolower($_POST[$this->nameForm . '_testo']) . "%')";
        }

        if ($_POST[$this->nameForm . '_soloValidi'] == 1) {
            $where .= " AND PRAAVA = '' AND PRAOFFLINE = 0";
        }

        if ($_POST[$this->nameForm . '_procediMarche'] == 1) {
            $where .= " AND CHIAVE <> ''";
        }

        if ($_POST[$this->nameForm . '_procediMarche'] == 2) {
            $where .= " AND CHIAVE IS NULL";
        }

        if ($_POST['_search'] == true) {
            if ($_POST['PRANUM']) {
                $where .= " AND PRANUM = '" . addslashes($_POST['PRANUM']) . "'";
            }
            if ($_POST['DESCRIZIONE']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('PRADES__1') . " LIKE '%" . strtoupper(addslashes($_POST['DESCRIZIONE'])) . "%'";
            }
            if ($_POST['TIPDES']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('TIPDES') . " LIKE '%" . strtoupper(addslashes($_POST['TIPDES'])) . "%'";
            }
            if ($_POST['EVENTO']) {
                $where .= " AND " . $this->PRAM_DB->strUpper($this->PRAM_DB->strConcat("EVTCOD", "'-'", "EVTDESCR")) . " LIKE '%" . strtoupper(addslashes($_POST['EVENTO'])) . "%'";
            }
            if ($_POST['PRADVA']) {
                /*
                 * Verifica formato data
                 */
                if (strpos($_POST['PRADVA'], '/') !== false) {
                    $_POST['PRADVA'] = implode('', array_reverse(explode('/', $_POST['PRADVA'])));
                }
                $where .= " AND " . $this->PRAM_DB->strUpper('PRADVA') . " LIKE '%" . strtoupper(addslashes($_POST['PRADVA'])) . "%'";
            }
            if ($_POST['PRAAVA']) {
                /*
                 * Verifica formato data
                 */
                if (strpos($_POST['PRAAVA'], '/') !== false) {
                    $_POST['PRAAVA'] = implode('', array_reverse(explode('/', $_POST['PRAAVA'])));
                }
                $where .= " AND " . $this->PRAM_DB->strUpper('PRAAVA') . " LIKE '%" . strtoupper(addslashes($_POST['PRAAVA'])) . "%'";
            }
            if ($_POST['PRAUPDEDITOR']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('PRAUPDEDITOR') . " LIKE '%" . strtoupper(addslashes($_POST['PRAUPDEDITOR'])) . "%'";
            }
            if ($_POST['PRAUPDDATE']) {
                /*
                 * Verifica formato data
                 */
                if (strpos($_POST['PRAUPDDATE'], '/') !== false) {
                    $_POST['PRAUPDDATE'] = implode('', array_reverse(explode('/', $_POST['PRAUPDDATE'])));
                }
                $where .= " AND " . $this->PRAM_DB->strUpper('PRAUPDDATE') . " LIKE '%" . strtoupper(addslashes($_POST['PRAUPDDATE'])) . "%'";
            }
            if ($_POST['TSPDES']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('TSPDES') . " LIKE '%" . strtoupper(addslashes($_POST['TSPDES'])) . "%'";
            }
            if ($_POST['SETDES']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('SETDES') . " LIKE '%" . strtoupper(addslashes($_POST['SETDES'])) . "%'";
            }
            if ($_POST['ATTDES']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('ATTDES') . " LIKE '%" . strtoupper(addslashes($_POST['ATTDES'])) . "%'";
            }
            if ($_POST['PRAGIO']) {
                $where .= " AND PRAGIO LIKE '%" . addslashes($_POST['PRAGIO']) . "%'";
            }
            if ($_POST['RESPONSABILE']) {
                $where .= " AND " . $this->PRAM_DB->strUpper('NOMCOG') . " LIKE '%" . strtoupper(addslashes($_POST['RESPONSABILE'])) . "%' OR " . $this->PRAM_DB->strUpper('NOMNOM') . " LIKE '%" . strtoupper(addslashes($_POST['RESPONSABILE'])) . "%'";
            }
        }
        App::log($sql . $where);
        return $sql . $where;

//        $sql = "SELECT ANAPRA.ROWID AS ROWID, PRANUM, PRAGIO, TIPDES, SETDES, ATTDES," .
//                $this->PRAM_DB->strConcat("PRADES__1", "' '", "PRADES__2") . " AS DESCRIZIONE," .
//                $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS RESPONSABILE
//            FROM ANAPRA ANAPRA
//            LEFT OUTER JOIN ANANOM ANANOM ON ANAPRA.PRARES=ANANOM.NOMRES
//            LEFT OUTER JOIN ANASET ANASET ON ANAPRA.PRASTT=ANASET.SETCOD
//            LEFT OUTER JOIN ANAATT ANAATT ON ANAPRA.PRAATT=ANAATT.ATTCOD
//            LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD WHERE PRANUM=PRANUM";
//        if ($_POST[$this->nameForm . '_pranum'] != "") {
//            $daPranum = $_POST[$this->nameForm . '_pranum'];
//            if ($_POST[$this->nameForm . '_aPranum'] != '') {
//                $aPranum = $_POST[$this->nameForm . '_aPranum'];
//            } else {
//                $aPranum = $daPranum;
//            }
//            $sql .= " AND (PRANUM BETWEEN '$daPranum' AND '$aPranum')";
//        }
//        if ($_POST[$this->nameForm . '_prades'] != "") {
//            $sql .= " AND " . $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2") . " LIKE '%" . addslashes($_POST[$this->nameForm . '_prades']) . "%'";
//        }
//        if ($_POST[$this->nameForm . '_pratip'] != "") {
//            $sql .= " AND PRATIP='" . $_POST[$this->nameForm . '_pratip'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_prares'] != "") {
//            $sql .= " AND PRARES='" . $_POST[$this->nameForm . '_prares'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_prastt'] != "") {
//            $sql .= " AND PRASTT='" . $_POST[$this->nameForm . '_prastt'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_praatt'] != "") {
//            $sql .= " AND PRAATT='" . $_POST[$this->nameForm . '_praatt'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_pratsp'] != "") {
//            $sql .= " AND PRATSP='" . $_POST[$this->nameForm . '_pratsp'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_testo'] != "") {
//            $sql .= " AND PRANUM IN (SELECT ITECOD FROM ITEPAS WHERE LOWER(ITEWRD) LIKE '%" . strtolower($_POST[$this->nameForm . '_testo']) . "%')";
//        }
//        return $sql;
    }

    public function Dettaglio($Indice, $tipo = 'rowid') {
        $Anapra_rec = $this->praLib->GetAnapra($Indice, $tipo);
        $Parambo_rec = $this->praLib->getAltriParametriBO($Anapra_rec['PRANUM']);
//        $Anaset_rec = $this->praLib->GetAnaset($Anapra_rec['PRASTT']);
//        $Anaatt_rec = $this->praLib->GetAnaatt($Anapra_rec['PRAATT']);
//        $Anatsp_rec = $this->praLib->GetAnatsp($Anapra_rec['PRATSP']);
        $open_Info = 'Oggetto: ' . $Anapra_rec['PRANUM'] . " " . $Anapra_rec['PRADES__1'];
        $this->openRecord($this->PRAM_DB, 'ANAPRA', $open_Info);
        $this->Nascondi();
        $this->AzzeraVariabili(false);
        $this->currPranum = $Anapra_rec['PRANUM'];
        $this->visualizzaMarcatura($Anapra_rec);
        Out::valori($Anapra_rec, $this->nameForm . '_ANAPRA');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . "_StampaDatiAgg");
        Out::show($this->nameForm . "_CercaDatiAgg");
        Out::show($this->nameForm . "_AggiornaPMarche");
        if (!$this->autoSearch) {
            Out::attributo($this->nameForm . '_PRADES', 'readonly', '1');
            Out::show($this->nameForm . '_Cancella');
            Out::show($this->nameForm . '_AltraRicerca');
            Out::show($this->nameForm . '_Torna');
        } else {
            Out::attributo($this->nameForm . '_PRADES', 'readonly');
        }
        Out::hide($this->divRic);
        Out::hide($this->divDup);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $giorni = $this->CalcolaGiorni($Anapra_rec['PRANUM']);
        Out::valore($this->nameForm . '_ANAPRA[PRAGIO]', $giorni);
        Out::valore($this->nameForm . '_PRADES', $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2']);
        $Ananom_rec = $this->praLib->GetAnanom($Anapra_rec['PRARES']);
        if ($Ananom_rec) {
            $this->DecodResponsabile($Ananom_rec);
        }
        Out::valori($Parambo_rec, $this->nameForm . '_PARAMBO');
        $this->caricaPassi($Anapra_rec['PRANUM']);
        $this->CaricaAllegati($Anapra_rec['PRANUM']);
        $this->CaricaRequisiti($Anapra_rec['PRANUM']);
        $this->CaricaEventi($Anapra_rec['PRANUM']);
        $this->CaricaNormative($Anapra_rec['PRANUM']);
        $this->CaricaDiscipline($Anapra_rec['PRANUM']);
        $this->CaricaObbligatori($Anapra_rec['PRANUM']);
        $this->CaricaAzioni($Anapra_rec['PRANUM']);
        $this->caricaGrigliaGruppi($Anapra_rec['PRANUM']);

        Out::attributo($this->nameForm . '_ANAPRA[PRANUM]', 'readonly', '0');
        $this->enabletabs($Anapra_rec['PRATPR'], $Anapra_rec['PRASLAVE']);

        $this->GestioneInformativa($Anapra_rec);

        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::attributo($this->nameForm . '_ANAPRA[PRANUM]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_PRADES');
        TableView::disableEvents($this->gridAnapra);

        /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
        $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);
        $praProcDatiAggiuntivi->openGestione($this->currPranum);

        /* @var $praProcDiagramma praProcDiagramma */
        $praProcDiagramma = itaModel::getInstance('praProcDiagramma', $this->praProcDiagrammaFormname);
        $praProcDiagramma->openGestione($Anapra_rec);
        $praProcDiagramma = null;
        return true;
    }

    public function OpenEdit($returnEvent, $testo = "") {
        $model = 'utiEditDiag';
        $rowidtext = $_POST['rowid'];
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['edit_text'] = $testo;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['rowidText'] = $rowidtext;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    public function CaricoCampiAggiuntivi($codice, $sortIndex = 'IDCDES') {
        $sql = "SELECT * FROM PRAIDC WHERE IDCPAS LIKE '%.$codice.%'";
        $ita_grid01 = new TableView($this->gridDati, array(
            'sqlDB' => $this->PRAM_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10);
        $ita_grid01->setSortIndex($sortIndex);
        $ita_grid01->setSortOrder('asc');
        if (!$ita_grid01->getDataPage('json')) {
            
        }
    }

    function CancellaDatiAggiuntivi($codice) {
        $sql = "SELECT * FROM PRAIDC WHERE IDCPAS LIKE '%.$codice.%'";
        $praidc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        foreach ($praidc_tab as $praidc_rec) {
            $this->CancellaDato($codice, $praidc_rec);
        }
    }

    function GetFilentMS() {
        $filent_rec = array();
        $praLib_slave = $this->praLib;
        $filent_rec['SLAVE'] = $praLib_slave->GetFilent(1);
        $praLib_master = new praLib($filent_rec['SLAVE']['FILDE3']);
        $filent_rec['MASTER'] = $praLib_master->GetFilent(1);
        return $filent_rec;
    }

    function CancellaDato($codice, $praidc_rec) {
        $praidc_rec['IDCPAS'] = str_replace('.' . $codice . '.', '.', $praidc_rec['IDCPAS']);
        if ($praidc_rec['IDCPAS'] == '.')
            $praidc_rec['IDCPAS'] = '';
        $update_Info = "Oggetto: Aggiornamento dato aggiuntivo " . $praidc_rec['IDCKEY'];
        if (!$this->updateRecord($this->PRAM_DB, 'PRAIDC', $praidc_rec, $update_Info)) {
            return false;
        }
    }

    function elaboraRecordsBDP($result_tab) {
        $result_tab_def = array();
        foreach ($result_tab as $key => $result_rec) {
            $Anapra_rec = $this->praLib->GetAnapra($result_rec['PRANUM']);
            $Anatsp_rec = $this->praLib->GetAnatsp($result_rec['IEVTSP']);
            $Anatip_rec = $this->praLib->GetAnatip($result_rec['IEVTIP']);
            $Anaset_rec = $this->praLib->GetAnaset($result_rec['IEVSTT']);
            $Anaatt_rec = $this->praLib->GetAnaatt($result_rec['IEVATT']);
            $Ananom_rec = $this->praLib->GetAnanom($Anapra_rec['PRARES']);
            $result_tab_def[$key]['CODICE PROCEDIMENTO'] = $result_rec['PRANUM'];
            $result_tab_def[$key]['IDENTIFICATIVO REGIONALE'] = "";
            $result_tab_def[$key]['IDENTIFICATIVO NAZIONALE'] = "";
            $result_tab_def[$key]['NOME PROCEDIMENTO'] = ""; //$Anaset_rec['SETDES'];
            $result_tab_def[$key]['DESCRIZIONE PROCEDIMENTO'] = $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'];
            $Itenor_tab = $this->praLib->GetItenor($result_rec['PRANUM'], "codice", true);
            $desc = "";
            if ($Itenor_tab) {
                $i = 0;
                foreach ($Itenor_tab as $Itenor_rec) {
                    $i += 1;
                    $Ananor_rec = $this->praLib->GetAnanor($Itenor_rec['NORCOD']);
//$desc .= "-".$Ananor_rec['NORDES'] . "<br>";
                    $desc .= "<b>Rif. $i:</b> " . $Ananor_rec['NORDES'] . " ";
                }
            }
            $result_tab_def[$key]['RIFERIMENTI NORMATIVI'] = $desc;
            $result_tab_def[$key]['CATEGORIA DESTINATARIO PROCEDIMENTO'] = "Cittadini/Impresa";
            $result_tab_def[$key]['CODICE ATECO'] = "";
            $result_tab_def[$key]['TIPOLOGIA ATTIVITA'] = "";
            $result_tab_def[$key]['REGIMI ABITATIVI'] = $Anatsp_rec['TSPDES'];
            $result_tab_def[$key]['GIUSTIFICAZIONE DEL REGIME'] = ""; //$Anatip_rec['TIPDES'];
            $result_tab_def[$key]['AMMINISTRAZIONE COMPETENTE'] = $Anatsp_rec['TSPCOM'];
            $result_tab_def[$key]['ALTRE AMMINISTRAZIONI'] = "";
            $result_tab_def[$key]['TERMINE PER CONCLUSIONE DEL PROCEDIMENTO'] = "";
            $result_tab_def[$key]['ALTRI TERMINI SIGNIFICATIVI DEL PROCEDIMENTO'] = "";
            $result_tab_def[$key]['SISTEMA DI CONTROLLI ASSOCIATO AL PROCEDIMENTO'] = "";
            $result_tab_def[$key]['UO DIRIGENZIALE'] = $_POST[$this->nameForm . "_UOdir"];
            $result_tab_def[$key]['UO RESPONSABILE'] = $_POST[$this->nameForm . "_UOres"];
            $result_tab_def[$key]['RECAPITI UO'] = $Anatsp_rec['TSPPEC'];
            $result_tab_def[$key]['RESPONSABILE PROCEDIMENTO'] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
            $result_tab_def[$key]['SOGGETTO CON POTERE ESECUTIVO'] = $_POST[$this->nameForm . "_Soggetto"];
            $result_tab_def[$key]['LINK AGLI ATTI E DOCUMENTI'] = $Anatsp_rec['TSPMOD'];
            $result_tab_def[$key]['LINK AL SERVIZIO SE DISPONIBILE'] = $Anatsp_rec['TSPWEB'];
            $result_tab_def[$key]['CODICE CLASSIFICAZIONE TITOLARIO'] = $this->praLib->GetClassificazioneProtocollazione($result_rec['PRANUM']);
            $result_tab_def[$key]['TIPO FASCICOLO'] = "";
            $result_tab_def[$key]['CODICE CATASTALE'] = $Anatsp_rec['TSPCCA'];
            if ($Anapra_rec['PRADVA']) {
                $result_tab_def[$key]['DATA INIZIO VALIDITA'] = substr($Anapra_rec['PRADVA'], 6, 2) . "/" . substr($Anapra_rec['PRADVA'], 4, 2) . "/" . substr($Anapra_rec['PRADVA'], 0, 4);
            } else {
                $result_tab_def[$key]['DATA INIZIO VALIDITA'] = "";
            }
            if ($Anapra_rec['PRAAVA']) {
                $result_tab_def[$key]['DATA FINE VALIDITA'] = substr($Anapra_rec['PRAAVA'], 6, 2) . "/" . substr($Anapra_rec['PRAAVA'], 4, 2) . "/" . substr($Anapra_rec['PRAAVA'], 0, 4);
            } else {
                $result_tab_def[$key]['DATA FINE VALIDITA'] = "";
            }
            $result_tab_def[$key]['TIPOLOGIA'] = $Anatip_rec['TIPDES'];
            $result_tab_def[$key]['SETTORE'] = $Anaset_rec['SETDES'];
            $result_tab_def[$key]['ATTIVITA'] = $Anaatt_rec['ATTDES'];
            $result_tab_def[$key]['AUTORE'] = "Italsoft";
            $result_tab_def[$key]['REVISORE'] = "Italsoft";
        }
        return $result_tab_def;
    }

    function CalcolaGiorni($codice) {
        $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $codice . "'";
        $itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $giorni = 0;
        foreach ($itepas_tab as $itepas_rec) {
            $giorni += $itepas_rec['ITEGIO'];
        }
        return $giorni;
    }

    function Aggiorna($anapra_rec, $Personalizza = false) {

        $this->AggiornaAltriParametri($_POST['praPro_PARAMBO'], $this->currPranum);
        $giorni = $this->CalcolaGiorni($this->currPranum);
        Out::valore($this->nameForm . '_ANAPRA[PRAGIO]', $giorni);
        $anapra_rec['PRADES__1'] = substr($_POST[$this->nameForm . '_PRADES'], 0, 80);
        $anapra_rec['PRADES__2'] = substr($_POST[$this->nameForm . '_PRADES'], 80);
        $codice = $anapra_rec['PRANUM'];
        $anapra_rec['PRANUM'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
        $this->SalvaRequisiti();
        $this->SalvaEventi();
        $this->SalvaNormative();
        $this->SalvaDiscipline();
        $this->SalvaObbligatori();
        if (!$this->SalvaAllegati($anapra_rec['PRANUM'])) {
            return false;
        }

        if ($Personalizza == true) {
            $anapra_rec = $this->praLib->SetMarcaturaProcedimento($anapra_rec);
        }

        $this->SalvaAzioniFO();


        if ($Personalizza) {
            $update_Info = 'Oggetto: Aggiornamento personalizzato procedimento n. ' . $anapra_rec['PRANUM'];
        } else {
            $update_Info = 'Oggetto: Aggiornamento procedimento n. ' . $anapra_rec['PRANUM'];
        }

        if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $anapra_rec, $update_Info)) {
            return false;
        }

        /* @var $praProcDatiAggiuntivi praProcDatiAggiuntivi */
        $praProcDatiAggiuntivi = itaModel::getInstance('praProcDatiAggiuntivi', $this->praProcDatiAggiuntiviFormname);
        if (!$praProcDatiAggiuntivi->aggiornaDati()) {
            return false;
        }

        /* @var $praProcDiagramma praProcDiagramma */
        $praProcDiagramma = itaModel::getInstance('praProcDiagramma', $this->praProcDiagrammaFormname);
        if (!$praProcDiagramma->aggiorna()) {
            return false;
        }
        $praProcDiagramma = null;

        $this->Dettaglio($anapra_rec['PRANUM'], 'codice');

        return true;
    }

    function AggiornaAltriParametri($parambo_rec, $pranum) {
        if (!$parambo_rec || !$pranum) {
            return false;
        }
        $parambo_rec["PRANUM"] = $pranum;
        $paranum_rec = $this->praLib->GetParamBO($pranum);
        if (!$paranum_rec) {
            $update_Info = 'Inserimento non riuscito per Altri parametri [PARAMBO] per anagrafica procedimento ' . $parambo_rec["PRANUM"];
            if (!$this->insertRecord($this->PRAM_DB, 'PARAMBO', $parambo_rec, $update_Info)) {
                return false;
            }
            return true;
        }
        $parambo_rec["ROWID"] = $paranum_rec["ROWID"];
        $update_Info = 'Aggiornamento non riuscito per Altri parametri [PARAMBO] per anagrafica procedimento ' . $parambo_rec["PRANUM"];
        if (!$this->updateRecord($this->PRAM_DB, 'PARAMBO', $parambo_rec, $update_Info)) {
            return false;
        }
        return true;
    }

    function ApriScanner() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '1';
        $modelTwain();
    }

    function SalvaScanner() {
        $randName = $_POST['retFile'];
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
        $this->allegati[] = array(
            'ROWID' => 0,
            'SEQUENZA' => 999999,
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => "File Originale: Da scanner",
            'CLASSE' => ""
        );
        $this->riordinaAllegati();
        $this->CaricaGriglia($this->gridAllegati, $this->allegati);
    }

    function AllegaFile() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;

            if (!@rename($uplFile, $destFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
            } else {
                $this->allegati[] = array(
                    'ROWID' => 0,
                    'SEQUENZA' => 999999,
                    'FILEPATH' => $destFile,
                    'FILENAME' => $randName,
                    'FILEINFO' => "File Originale: " . $origFile,
                    'CLASSE' => ""
                );
                $this->riordinaAllegati();
                $this->CaricaGriglia($this->gridAllegati, $this->allegati);
                Out::setFocus('', $this->nameForm . '_wrapper');
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
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
//TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    function SalvaRequisiti() {
        $Itereq_tab = $this->praLib->GetItereq($this->currPranum, 'codice', true);
        if ($Itereq_tab) {
            foreach ($Itereq_tab as $key => $Itereq_rec) {

                $delete_Info = "Oggetto: Cancellazione requisito " . $Itereq_rec['REQCOD'] . " del procediemnto " . $Itereq_rec['ITEPRA'];
                if (!$this->deleteRecord($this->PRAM_DB, 'ITEREQ', $Itereq_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione File", "Errore in cancellazione requisito " . $Itereq_rec['REQCOD'] . " del procediemnto " . $Itereq_rec['ITEPRA']);
                    break;
                }
            }
        }
        foreach ($this->requisiti as $key => $requisito) {
            $update_Info = 'Oggetto: Inserimento del requisito ' . $requisito['REQCOD'] . " " . $requisito['REQDES'];
            $Itereq_rec['ITEPRA'] = $this->currPranum;
            $Itereq_rec['REQCOD'] = $requisito['REQCOD'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITEREQ', $Itereq_rec, $update_Info)) {
                Out::msgStop("Errore", "Inserimento del requisito " . $requisito['REQCOD'] . " - " . $requisito['REQDES'] . " fallito");
                break;
            }
        }
//$this->requisiti = "";
    }

    function SalvaEventi() {
        $Iteevt_tab = $this->praLib->GetIteevt($this->currPranum, 'codice', true);
        if ($Iteevt_tab) {
            foreach ($Iteevt_tab as $Iteevt_rec) {
                $delete_Info = "Oggetto: Cancellazione evento " . $Iteevt_rec['IEVCOD'] . " del procedimento " . $Iteevt_rec['ITEPRA'];
                if (!$this->deleteRecord($this->PRAM_DB, 'ITEEVT', $Iteevt_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione File", "Errore in cancellazione evento " . $Iteevt_rec['IEVCOD'] . " del procediemnto " . $Iteevt_rec['ITEPRA']);
                    break;
                }
            }
        }
        foreach ($this->eventi as $evento) {
//            foreach ($evento as $k => $v) {
//                if ('EVT' === substr($k, 0, 3) || 'CLASSIFICAZIONE' === $k) {
//                    unset($evento[$k]);
//                }
//            }

            $update_Info = 'Oggetto: Inserimento dell\'evento ' . $evento['IEVCOD'] . " " . $evento['IEVDESCR'];
            $evento['ITEPRA'] = $this->currPranum;
            if (!$this->insertRecord($this->PRAM_DB, 'ITEEVT', $evento, $update_Info)) {
                Out::msgStop("Errore", "Inserimento dell\'evento " . $evento['IEVCOD'] . " - " . $evento['IEVDESCR'] . " fallito");
                break;
            }
        }
    }

    function SalvaNormative() {
        $Itenor_tab = $this->praLib->GetItenor($this->currPranum, 'codice', true);
        if ($Itenor_tab) {
            foreach ($Itenor_tab as $key => $Itenor_rec) {

                $delete_Info = "Oggetto: Cancellazione normativa " . $Itenor_rec['NORCOD'] . " del procediemnto " . $Itenor_rec['ITEPRA'];
                if (!$this->deleteRecord($this->PRAM_DB, 'ITENOR', $Itenor_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione File", "Errore in cancellazione normativa " . $Itenor_rec['NORCOD'] . " del procediemnto " . $Itenor_rec['ITEPRA']);
                    break;
                }
            }
        }
        foreach ($this->normative as $key => $normativa) {
            $update_Info = 'Oggetto: Inserimento della normativa ' . $normativa['NORCOD'] . " " . $normativa['NORDES'];
            $Itenor_rec['ITEPRA'] = $this->currPranum;
            $Itenor_rec['NORCOD'] = $normativa['NORCOD'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITENOR', $Itenor_rec, $update_Info)) {
                Out::msgStop("Errore", "Inserimento della normativa " . $normativa['NORCOD'] . " - " . $normativa['NORDES'] . " fallito");
                break;
            }
        }
    }

    function SalvaAzioniFO() {
        foreach ($this->azioniFO as $Praazioni_rec) {
            unset($Praazioni_rec['DESCRIZIONEAZIONE']);
            unset($Praazioni_rec['OPERAZIONE']);
            $Praazioni_rec['PRANUM'] = $this->currPranum;
            if (isset($Praazioni_rec['ROWID'])) {
                $update_Info = 'Oggetto: Aggiornamento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $update_Info)) {
                    Out::msgStop("Errore", "Aggiornamneto azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            } else {
                $insert_Info = 'Oggetto: Inserimento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $insert_Info)) {
                    Out::msgStop("Errore", "Inserimento azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            }
        }
        return true;
    }

    function SalvaDiscipline() {
        $Itedis_tab = $this->praLib->GetItedis($this->currPranum, 'codice', true);
        if ($Itedis_tab) {
            foreach ($Itedis_tab as $key => $Itedis_rec) {
                $delete_Info = "Oggetto: Cancellazione disciplina " . $Itedis_rec['DISCOD'] . " del procediemnto " . $Itedis_rec['ITEPRA'];
                if (!$this->deleteRecord($this->PRAM_DB, 'ITEDIS', $Itedis_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione File", "Errore in cancellazione disicplina sanzionatoria " . $Itedis_rec['DISCOD'] . " del procediemnto " . $Itedis_rec['ITEPRA']);
                    break;
                }
            }
        }
        foreach ($this->discipline as $key => $disciplina) {
            $update_Info = 'Oggetto: Inserimento della disciplina ' . $disciplina['DISCOD'] . " " . $disciplina['DISDES'];
            $Itedis_rec['ITEPRA'] = $this->currPranum;
            $Itedis_rec['DISCOD'] = $disciplina['DISCOD'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITEDIS', $Itedis_rec, $update_Info)) {
                Out::msgStop("Errore", "Inserimento della disciplina " . $disciplina['DISCOD'] . " - " . $disciplina['DISDES'] . " fallito");
                break;
            }
        }
    }

    function SalvaObbligatori() {
        $obbligatori_tab = $this->praLib->GetItePraObb($this->currPranum, 'codice', true);
        if ($obbligatori_tab) {
            foreach ($obbligatori_tab as $key => $obbligatori_rec) {
                $delete_Info = "Oggetto: Cancellazione obbligatori " . $obbligatori_rec['OBBSUBPRA'] . " del procediemnto " . $obbligatori_rec['OBBPRA'];
                if (!$this->deleteRecord($this->PRAM_DB, 'ITEPRAOBB', $obbligatori_rec['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione File", "Errore in cancellazione pratica obbligatoria " . $obbligatori_rec['OBBSUBPRA'] . " del procediemnto " . $obbligatori_rec['OBBPRA']);
                    break;
                }
            }
        }
        foreach ($this->obbligatori as $key => $obbligatori_rec) {
            $update_Info = 'Oggetto: Inserimento procedura obbligatoria ' . $obbligatori_rec['OBBSUBPRA'] . " al procedimento " . $obbligatori_rec['OBBPRA'];
            $itePraObb['OBBPRA'] = $this->currPranum;
            $itePraObb['OBBEVCOD'] = $obbligatori_rec['OBBEVCOD'];
            $itePraObb['OBBSUBPRA'] = $obbligatori_rec['OBBSUBPRA'];
            $itePraObb['OBBSUBEVCOD'] = $obbligatori_rec['OBBSUBEVCOD'];
            $itePraObb['OBBEXPRCTR'] = $obbligatori_rec['OBBEXPRCTR'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITEPRAOBB', $itePraObb, $update_Info)) {
                Out::msgStop("Errore", "Inserimento procedura obbligata " . $itePraObb['OBBSUBPRA'] . " fallito");
                break;
            }
        }
    }

    function DecodResponsabile($Ananom_rec, $tipo = '') {
        if ($tipo == '') {
            Out::valore($this->nameForm . '_ANAPRA[PRARES]', $Ananom_rec["NOMRES"]);
            Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
            $sql = "SELECT ANAUNI.ROWID AS ROWID, ANAUNI.UNIADD AS UNIADD, ANAUNI.UNIOPE AS UNIOPE, ANAUNI.UNISET AS UNISET,
            ANAUNI.UNISER AS UNISER,SETTORI.UNIDES AS DESSET, SERVIZI.UNIDES AS DESSER,UNITA.UNIDES AS DESOPE,
            NOMCOG & ' ' & NOMNOM AS NOMCOG
            FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIADD=ANANOM.NOMRES
            LEFT OUTER JOIN ANAUNI SETTORI ON ANAUNI.UNISET=SETTORI.UNISET AND SETTORI.UNISER=''
            LEFT OUTER JOIN ANAUNI SERVIZI ON ANAUNI.UNISET=SERVIZI.UNISET AND ANAUNI.UNISER=SERVIZI.UNISER AND SERVIZI.UNIOPE=''
            LEFT OUTER JOIN ANAUNI UNITA   ON ANAUNI.UNISET=UNITA.UNISET AND ANAUNI.UNISER=UNITA.UNISER AND ANAUNI.UNIOPE=UNITA.UNIOPE
            AND UNITA.UNIADD=''
            WHERE ANAUNI.UNISET<>'' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIOPE<>'' AND ANAUNI.UNIADD<>'' AND ANAUNI.UNIAPE=''";
            $sql .= " AND ANAUNI.UNIADD = '" . $Ananom_rec["NOMRES"] . "'";
            $AnauniRes_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $Anauni_rec = $this->praLib->getAnauni($AnauniRes_rec['UNISET']);
            Out::valore($this->nameForm . '_ANAPRA[PRASET]', $Anauni_rec['UNISET']);
            Out::valore($this->nameForm . '_Settore', $Anauni_rec['UNIDES']);
            if ($AnauniRes_rec['UNISER'] == "")
                $AnauniRes_rec['UNISET'] = "";
            $AnauniServ_rec = $this->praLib->GetAnauniServ($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER']);
            Out::valore($this->nameForm . '_ANAPRA[PRASER]', $AnauniServ_rec['UNISER']);
            Out::valore($this->nameForm . '_Servizio', $AnauniServ_rec['UNIDES']);
            if ($AnauniRes_rec['UNISET'] == "")
                $AnauniRes_rec['UNIOPE'] = "";
            $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
            Out::valore($this->nameForm . '_ANAPRA[PRAOPE]', $AnauniOpe_rec['UNIOPE']);
            Out::valore($this->nameForm . '_Unita', $AnauniOpe_rec['UNIDES']);
        } else if ($tipo == 'ricerca') {
            Out::valore($this->nameForm . '_prares', $Ananom_rec["NOMRES"]);
            Out::valore($this->nameForm . '_nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
        }
    }

    function CaricaAllegati($numeroPratica) {
        $Anpdoc_tab = $this->praLib->GetAnpdoc($numeroPratica, 'codice', true);
        $this->allegati = array();
        if ($Anpdoc_tab) {
            $destinazione = $this->praLib->SetDirectoryProcedimenti($numeroPratica, 'allegati');
            if (!$destinazione) {
                Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                return false;
            }
            foreach ($Anpdoc_tab as $key => $Anpdoc_rec) {
                $this->allegati[] = Array(
                    'ROWID' => $Anpdoc_rec['ROWID'],
                    'SEQUENZA' => $Anpdoc_rec['ANPSEQ'],
                    'FILEPATH' => $destinazione . "/" . $Anpdoc_rec['ANPFIL'],
                    'FILENAME' => $Anpdoc_rec['ANPFIL'],
                    'FILEINFO' => $Anpdoc_rec['ANPNOT'],
                    'CLASSE' => $Anpdoc_rec['ANPCLA']
                );
            }
            $this->riordinaAllegati();
        }
        $this->CaricaGriglia($this->gridAllegati, $this->allegati);
        return true;
    }

    function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => 'Info non presente'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    public function caricaPassi($procedimento) {
        $sql = "SELECT
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITESEQ AS ITESEQ,
                    ITEPAS.ITEGIO AS ITEGIO,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEPUB AS ITEPUB,
                    ITEPAS.ITEOBL AS ITEOBL,
                    ITEPAS.ITECTP AS ITECTP,
                    ITEPAS.ITEQST AS ITEQST,
                    ITEPAS.ITEVPA AS ITEVPA,
                    ITEPAS.ITEVPN AS ITEVPN,
                    ITEPAS.ITEKEY AS ITEKEY,
                    ITEPAS.ITEKPRE AS ITEKPRE,
                    ITEPAS.TEMPLATEKEY AS TEMPLATEKEY,
                    PRACLT.CLTDES AS CLTDES," .
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM ITEPAS LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=ITEPAS.ITERES
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
                WHERE ITEPAS.ITECOD = '" . $procedimento . "' ORDER BY ITESEQ";
        $this->passi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if ($this->passi) {
            foreach ($this->passi as $key => $passo) {
                $this->passi[$key]['VAI'] = $this->praLib->decodificaImmagineSaltoPasso($passo['ITEQST'], $passo['ITEVPA'], $passo['ITEVPN'], $this->praLib->GetItevpadett($passo['ITEKEY'], 'itekey'));

                if ($passo['ITECTP'] != 0) {
                    $Itepas_rec = $this->praLib->GetItepas($passo['ITECTP'], "itekey");
                    $this->passi[$key]['CONTROLLO'] = $Itepas_rec['ITESEQ'];
                }
                if ($passo['TEMPLATEKEY']) {
                    $this->passi[$key]['TEMPLATE'] = "<span class=\"ita-icon ita-icon-open-folder-24x24\">Apri Gestione Passo Template</span>";
                }
                $this->passi[$key]['ORDERANT'] = str_pad($passo['ITESEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['PROKPRE'], 4, "0", STR_PAD_LEFT);
                if ($passo['ITEKPRE']) {
                    $itepas_recAnt = $this->praLib->GetItepas($passo['ITEKPRE'], "itekey");
                    $this->passi[$key]['ORDERANT'] = str_pad($itepas_recAnt['ITESEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['ITESEQ'], 4, "0", STR_PAD_LEFT);
                    $this->passi[$key]['SEQANT'] = $this->passi[$key]['ITESEQ'];
                    $this->passi[$key]['ITESEQ'] = '';
                }
            }

            Out::show($this->nameForm . '_CancellaPassi');
            Out::show($this->nameForm . '_ExportPassi');
        } else {
            Out::hide($this->nameForm . '_CancellaPassi');
            Out::hide($this->nameForm . '_ExportPassi');
        }

        $passi = $this->array_sort($this->passi, "ORDERANT");
        $this->passi = $passi;
        $this->CaricaGriglia($this->gridPassi, $this->passi, '2');
        $this->caricaDiagramma($procedimento);
        return $this->passi;
    }

    public function CaricaRequisiti($procedimento) {
        $this->requisiti = array();
        $sql = "SELECT * FROM ITEREQ WHERE ITEPRA = '" . $procedimento . "'";
        $requisiti = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->requisiti = array();
        foreach ($requisiti as $key => $requisito) {
            $Anareq_rec = $this->praLib->GetAnareq($requisito['REQCOD']);
            $this->requisiti[] = $Anareq_rec;
        }
        $this->CaricaGriglia($this->gridRequisiti, $this->requisiti);
        return $this->requisiti;
    }

//    public function CaricaEventi($procedimento) {
//        $this->eventi = array();
//        $sql = "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $procedimento . "'";
//        $eventi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
//        $this->eventi = array();
//        foreach ($eventi as $evento) {
//            $sportello = $settore = $attivita = "";
//            $Anaeventi_rec = $this->praLib->GetAnaeventi($evento['IEVCOD']);
//            $anatsp_rec = $this->praLib->GetAnatsp($evento['IEVTSP']);
//            $anaset_rec = $this->praLib->GetAnaset($evento['IEVSTT']);
//            $anaatt_rec = $this->praLib->GetAnaatt($evento['IEVATT']);
//            //
//            $Anaeventi_rec['EVTSEGCOMUNICA'] = praLib::$TIPO_SEGNALAZIONE[$Anaeventi_rec['EVTSEGCOMUNICA']];
//            if ($evento['IEVTSP'] != 0) {
//                $sportello = $evento['IEVTSP'] . "-" . $anatsp_rec['TSPDES'];
//            }
//            if ($evento['IEVSTT'] != 0) {
//                $settore = $evento['IEVSTT'] . "-" . $anaset_rec['SETDES'];
//            }
//            if ($evento['IEVATT'] != 0) {
//                $attivita = $evento['IEVATT'] . "-" . $anaatt_rec['ATTDES'];
//            }
//            $Anaeventi_rec['CLASSIFICAZIONE'] = "<div style=\"height:55px;overflow:auto;\" class=\"ita-Wordwrap\"><div>$sportello</div><div>$settore</div><div>$attivita</div></div>";
//            $this->eventi[] = array_merge($Anaeventi_rec, $evento);
//        }
//        $this->CaricaGriglia($this->gridEventi, $this->eventi);
//        return $this->eventi;
//    }

    public function CaricaEventi($procedimento) {
        $this->eventi = array();
        $sql = "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $procedimento . "'";
        $this->eventi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->CaricaGriglia($this->gridEventi, $this->elaboraRecordsEventi($this->eventi));
        return $eventi;
    }

    public function elaboraRecordsEventi($iteevt_tab) {
        $eventi = array();
        foreach ($iteevt_tab as $iteevt_rec) {
            $sportello = $settore = $attivita = "";
            $Anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD']);
            $anatsp_rec = $this->praLib->GetAnatsp($iteevt_rec['IEVTSP']);
            $anaset_rec = $this->praLib->GetAnaset($iteevt_rec['IEVSTT']);
            $anaatt_rec = $this->praLib->GetAnaatt($iteevt_rec['IEVATT']);
            //
            $Anaeventi_rec['EVTSEGCOMUNICA'] = praLib::$TIPO_SEGNALAZIONE[$Anaeventi_rec['EVTSEGCOMUNICA']];
            if ($iteevt_rec['IEVTSP'] != 0) {
                $sportello = $iteevt_rec['IEVTSP'] . "-" . $anatsp_rec['TSPDES'];
            }
            if ($iteevt_rec['IEVSTT'] != 0) {
                $settore = $iteevt_rec['IEVSTT'] . "-" . $anaset_rec['SETDES'];
            }
            if ($iteevt_rec['IEVATT'] != 0) {
                $attivita = $iteevt_rec['IEVATT'] . "-" . $anaatt_rec['ATTDES'];
            }
            $Anaeventi_rec['CLASSIFICAZIONE'] = "<div style=\"height:55px;overflow:auto;\" class=\"ita-Wordwrap\"><div>$sportello</div><div>$settore</div><div>$attivita</div></div>";
            $eventi[] = array_merge($Anaeventi_rec, $iteevt_rec);
        }
        return $eventi;
    }

    public function CaricaNormative($procedimento) {
        $this->normative = array();
        $sql = "SELECT * FROM ITENOR WHERE ITEPRA = '" . $procedimento . "'";
        $normative = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->normative = array();
        foreach ($normative as $normativa) {
            $Ananor_rec = $this->praLib->GetAnanor($normativa['NORCOD']);
            $this->normative[] = $Ananor_rec;
        }
        $this->CaricaGriglia($this->gridNormative, $this->normative);
        return $this->normative;
    }

    public function CaricaDiscipline($procedimento) {
        $this->discipline = array();
        $sql = "SELECT * FROM ITEDIS WHERE ITEPRA = '" . $procedimento . "'";
        $disicpline = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $this->discipline = array();
        foreach ($disicpline as $disicplina) {
            $Anadis_rec = $this->praLib->GetAnadis($disicplina['DISCOD']);
            $this->discipline[] = $Anadis_rec;
        }
        $this->CaricaGriglia($this->gridDiscipline, $this->discipline);
        return $this->discipline;
    }

    public function CaricaObbligatori($procedimento) {
        $this->obbligatori = array();
        $this->obbligatori = $this->praLib->GetItePraObb($procedimento, 'codice', true);
        $this->CaricaGriglia($this->gridObbligatori, $this->elaboraRecordsObbligatori($this->obbligatori));
        return;
    }

    public function CaricaAzioni($procedimento) {
        $AzioniFO = new praAzioniFO();
        $this->azioniFO = $AzioniFO->getGridAzioniProcedimento($procedimento);
        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
    }

    public function elaboraRecordsObbligatori($obbligatori_tab) {
        foreach ($obbligatori_tab as $key => $obbligatori_rec) {
            $AnaeventiPadre = $this->praLib->GetAnaeventi($obbligatori_rec['OBBEVCOD']);
            $AnapraObb = $this->praLib->GetAnapra($obbligatori_rec['OBBSUBPRA']);
            $AnaeventiObb = $this->praLib->GetAnaeventi($obbligatori_rec['OBBSUBEVCOD']);
            //
            $obbligatori_tab[$key]['DESOBBEVCOD'] = $AnaeventiPadre['EVTDESCR'];
            $obbligatori_tab[$key]['DESOBBSUBPRA'] = $AnapraObb['PRADES__1'];
            $obbligatori_tab[$key]['DESOBBSUBEVCOD'] = $AnaeventiObb['EVTDESCR'];
            $obbligatori_tab[$key]['CONDIZIONE'] = $this->praLib->DecodificaControllo($obbligatori_rec['OBBEXPRCTR']);
        }
        return $obbligatori_tab;
    }

    function DuplicaEventi($procedimento, $nuovoCodice) {
        $sql = "SELECT * FROM ITEEVT WHERE ITEPRA = '$procedimento'";
        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if ($iteevt_tab) {
            foreach ($iteevt_tab as $iteevt_rec) {
                $new_iteevt = $iteevt_rec;
                $new_iteevt['ITEPRA'] = $nuovoCodice;
                unset($new_iteevt['ROWID']);
                if (!$this->insertRecord($this->PRAM_DB, 'ITEEVT', $new_iteevt, $insert_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    function DuplicaAllegati($procedimento, $nuovoCodice, $PRAM_DB) {
        $sql = "SELECT * FROM ANPDOC WHERE ANPKEY = '$procedimento'";
        $allegati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        //$allegati = ItaDB::DBSQLSelect($PRAM_DB, $sql);
        if ($allegati) {
//Creo nuova cartella procedimento
            $ditta = App::$utente->getKey('ditta');
            $vecchiaDestinazione = Config::getPath('general.itaProc') . "ente$ditta/allegati/$procedimento";
            $nuovaDestinazione = Config::getPath('general.itaProc') . "ente$ditta/allegati/$nuovoCodice";

            if (!is_dir($nuovaDestinazione)) {
                if (!@mkdir($nuovaDestinazione)) {
                    Out::msgStop("Attenzione!!", "Errore creazione nuova cartella procedimento $nuovoCodice");
                    return false;
                }
            }

            $insert_Info = "Oggetto: duplico allegati da proc $procedimento a proc $nuovoCodice";
            foreach ($allegati as $allegato) {
//Aggiungo nuovi allegati al DB cambiando i dati
                $new_allegato = $allegato;
                $new_allegato['ANPKEY'] = $nuovoCodice;
                $new_allegato['ANPFIL'] = md5(rand() * time()) . "." . pathinfo($allegato['ANPFIL'], PATHINFO_EXTENSION);
                $new_allegato['ANPLNK'] = "allegato://" . $new_allegato['ANPFIL'];
                unset($new_allegato['ROWID']);
                if (!$this->insertRecord($this->PRAM_DB, 'ANPDOC', $new_allegato, $insert_Info)) {
                    return false;
                }
//Copio gli allegati nella nuova cartella
                if (!@copy($vecchiaDestinazione . "/" . $allegato['ANPFIL'], $nuovaDestinazione . "/" . $new_allegato['ANPFIL'])) {
                    Out::msgStop("Attenzione!!", "Errore duplicazione allegato " . $vecchiaDestinazione . "/" . $allegato['ANPFIL']);
                    return false;
                }
            }
        }
        return true;
    }

    function DuplicaPassi($procedimento, $nuovoCodice, $PRAM_DB) {
        $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $procedimento . "' ORDER BY ITESEQ";
        //$passi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $passi = ItaDB::DBSQLSelect($PRAM_DB, $sql);
        $salvaItekey = array();
        $indice = 0;
        foreach ($passi as $key => $passo) {
            $salvaItekey[$indice]['OLD'] = $passo['ITEKEY'];
            $passi[$key]['ITECOD'] = $nuovoCodice;
            $passi[$key]['ITEKEY'] = $this->praLib->keyGenerator($nuovoCodice);
            $salvaItekey[$indice]['NEW'] = $passi[$key]['ITEKEY'];
            $indice = $indice + 1;
        }
        $indice = 0;
        foreach ($passi as $key => $passo) {
            if ($passo['ITEVPA']) {
                foreach ($salvaItekey as $oldItekey) {
                    if ($oldItekey['OLD'] == $passo['ITEVPA']) {
                        $passi[$key]['ITEVPA'] = $oldItekey['NEW'];
                        break;
                    }
                }
            }
            if ($passo['ITEVPN']) {
                foreach ($salvaItekey as $oldItekey) {
                    if ($oldItekey['OLD'] == $passo['ITEVPN']) {
                        $passi[$key]['ITEVPN'] = $oldItekey['NEW'];
                        break;
                    }
                }
            }
            if ($passo['ITECTP']) {
                foreach ($salvaItekey as $oldItekey) {
                    if ($oldItekey['OLD'] == $passo['ITECTP']) {
                        $passi[$key]['ITECTP'] = $oldItekey['NEW'];
                        break;
                    }
                }
            }
            if ($passo['ITEKPRE']) {
                foreach ($salvaItekey as $oldItekey) {
                    if ($oldItekey['OLD'] == $passo['ITEKPRE']) {
                        $passi[$key]['ITEKPRE'] = $oldItekey['NEW'];
                        break;
                    }
                }
            }
            unset($passi[$key]['ROWID']);
            $insert_Info = 'Oggetto : Duplico passo seq ' . $passo['ITESEQ'] . " - " . $passo['ITEKEY'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $passi[$key], $insert_Info)) {
                return false;
            }

            /*
             * Duplico i campi aggiuntivi di ogni passo
             */
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $salvaItekey[$indice]['OLD'] . "'";
            $datiAggiuntivi = ItaDB::DBSQLSelect($PRAM_DB, $sql);
            if ($datiAggiuntivi) {
                foreach ($datiAggiuntivi as $keyDag => $datoAggiuntivo) {
                    $datiAggiuntivi[$keyDag]['ITECOD'] = $nuovoCodice;
                    $datiAggiuntivi[$keyDag]['ITEKEY'] = $salvaItekey[$indice]['NEW'];
                    $insert_Info = 'Oggetto : Duplico dato aggiuntivo seq ' . $datoAggiuntivo['ITDSEQ'] . " - " . $passo['ITEKEY'];
                    unset($datiAggiuntivi[$keyDag]['ROWID']);
                    if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $datiAggiuntivi[$keyDag], $insert_Info)) {
                        return false;
                    }
                }
            }

            /*
             * duplico i destinatari
             */
            $dett_tab = $this->praLib->GetItedest($salvaItekey[$indice]['OLD']);
            if ($dett_tab) {
                $insert_Info = 'Oggetto : Duplico destinatari passo seq ' . $passo['ITESEQ'];
                foreach ($dett_tab as $keyDest => $dest) {
                    $dett_tab[$keyDest]['ITECOD'] = $nuovoCodice;
                    $dett_tab[$keyDest]['ITEKEY'] = $salvaItekey[$indice]['NEW'];
                    unset($dett_tab[$keyDest]['ROWID']);
                    if (!$this->insertRecord($this->PRAM_DB, 'ITEDEST', $dett_tab[$keyDest], $insert_Info)) {
                        return false;
                    }
                }
            }
            $indice = $indice + 1;
        }
        return true;
    }

    function DuplicaParametri($procedimento, $nuovoCodice, $PRAM_DB) {
        $sql = "SELECT * FROM PARAMBO WHERE PRANUM = $procedimento";
        $parametri = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
        if (!$parametri) {
            return true; // non sono presenti parametri per l'anagrafica
        }

        unset($parametri['ROWID']);
        $parametri['PRANUM'] = $nuovoCodice;
        $insert_Info = "Errore inserimento Parametri BO per il anagrafica procedimento " . $parametri['PRANUM'];
        if (!$this->insertRecord($this->PRAM_DB, 'PARAMBO', $parametri, $insert_Info)) {
            return false;
        }

        return true;
    }

    public function SalvaAllegati($numeroPratica) {
        $destinazione = $this->praLib->SetDirectoryProcedimenti($numeroPratica, 'allegati');
        if ($destinazione == false) {
            Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
            return false;
        }
        foreach ($this->allegati as $allegato) {
            if ($allegato['ROWID'] == 0) {
                if (!@rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $destinazione . "/" . $allegato['FILENAME'] . " !");
                    return false;
                }
                $Anpdoc_rec = array();
                $Anpdoc_rec['ANPKEY'] = $numeroPratica;
                $Anpdoc_rec['ANPFIL'] = $allegato['FILENAME'];
                $Anpdoc_rec['ANPLNK'] = "allegato://" . $allegato['FILENAME'];
                $Anpdoc_rec['ANPUTC'] = '';
                $Anpdoc_rec['ANPUTE'] = '';
                $Anpdoc_rec['ANPNOT'] = $allegato['FILEINFO'];
                $Anpdoc_rec['ANPCLA'] = $allegato['CLASSE'];
                $Anpdoc_rec['ANPSEQ'] = $allegato['SEQUENZA'];

                $insert_Info = 'Oggetto : Inserisco allegato ' . $Anpdoc_rec['ANPFIL'] . " del procediemnto " . $Anpdoc_rec['ANPKEY'];
                if (!$this->insertRecord($this->PRAM_DB, 'ANPDOC', $Anpdoc_rec, $insert_Info)) {
                    return false;
                }
            } else {
                $Anpdoc_rec = $this->praLib->GetAnpdoc($allegato['ROWID'], 'ROWID', false);
                $Anpdoc_rec['ANPNOT'] = $allegato['FILEINFO'];
                $Anpdoc_rec['ANPCLA'] = $allegato['CLASSE'];
                $Anpdoc_rec['ANPSEQ'] = $allegato['SEQUENZA'];

                $update_Info = "Oggetto: Aggiornamento allegato " . $Anpdoc_rec['ANPFIL'];
                if (!$this->updateRecord($this->PRAM_DB, 'ANPDOC', $Anpdoc_rec, $update_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function enableTabSportello($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneClassOnLine");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneClassOnLine");
        }
    }

    public function enableTabRequisiti($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneRequisiti");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneRequisiti");
        }
    }

    public function enableTabNormativa($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneNormative");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneNormative");
        }
    }

    public function enableTabDiscipline($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneDiscipline");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneDiscipline");
        }
    }

    public function enableTabPassi($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        }
    }

    public function enableTabAllegati($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
        }
    }

    public function enableTabObbligatori($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneObbligatori");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneObbligatori");
        }
    }

    public function enableTabAzioni($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneAzioni");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneAzioni");
        }
    }

    public function enableTabParametri($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneParametri");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneParametri");
        }
    }

    public function enableTabWorkflow($enable = true) {
        if ($enable) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneWorkflow");
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneGruppi");
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneWorkflow");
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneGruppi");
        }
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "", "1", "");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "ALTRO", "0", "Altro");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "APERTURA", "0", "Apertura");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "CESSAZIONE", "0", "Cessazione");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "MODIFICHE", "0", "Modifiche");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "SUBENTRO", "0", "Subentro");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "TRASFORMAZIONE", "0", "Trasformazione");
        Out::select($this->nameForm . '_ANAPRA[PRASEG]', 1, "FIERE", "0", "Fiere");

        $codici = array(
            "" => "Generico",
            "ONLINE" => "Compilazione On-Line",
            "ENDOPROCEDIMENTO" => "Endoprocedimento",
            "ENDOPROCEDIMENTOWRKF" => "Endoprocedimento con workflow",
        );
        foreach ($codici as $key => $value) {
            if ($value == 'Generico') {
                Out::select($this->nameForm . '_ANAPRA[PRATPR]', 1, $key, "0", $value);
                Out::select($this->nameForm . '_tipo_procedimento', 1, "GENERICO", "0", $value);
                continue;
            }
            Out::select($this->nameForm . '_ANAPRA[PRATPR]', 1, $key, "0", $value);
            Out::select($this->nameForm . '_tipo_procedimento', 1, $key, "0", $value);
        }

        Out::select($this->nameForm . '_procediMarche', 1, 1, "1", 'Solo Pubblicati');
        Out::select($this->nameForm . '_procediMarche', 1, 2, "2", 'Non Pubblicati');
    }

    public function enableTabs($tipo, $slave = "0") {
        switch ($tipo . "-" . $slave) {
            case "ONLINE-0":
                $this->enableTabPassi();
                $this->enableTabAllegati();
                $this->enableTabSportello();
                $this->enableTabRequisiti();
                $this->enableTabDiscipline();
                $this->enableTabNormativa();
                $this->enableTabObbligatori();
                $this->enableTabAzioni();
                $this->enableTabParametri();
                $this->enableTabWorkflow(false);
                break;
            case "ONLINE-1":
                $this->enableTabPassi();
                $this->enableTabAllegati();
                $this->enableTabSportello();
                $this->enableTabRequisiti();
                $this->enableTabDiscipline();
                $this->enableTabNormativa();
                $this->enableTabObbligatori(false);
                $this->enableTabAzioni(false);
                $this->enableTabParametri();
                $this->enableTabWorkflow(false);
                break;
//            case "MODELLO-0":
//                $this->enableTabPassi();
//                $this->enableTabAllegati();
//                $this->enableTabSportello(false);
//                $this->enableTabRequisiti(false);
//                $this->enableTabDiscipline(false);
//                $this->enableTabNormativa(false);
//                $this->enableTabModello();
//                $this->enableTabObbligatori();
//                $this->enableTabAzioni();
//                $this->enableTabParametri();
//                break;
//            case "MODELLO-1":
//                $this->enableTabPassi();
//                $this->enableTabAllegati();
//                $this->enableTabSportello(false);
//                $this->enableTabRequisiti(false);
//                $this->enableTabDiscipline(false);
//                $this->enableTabNormativa(false);
//                $this->enableTabModello();
//                $this->enableTabObbligatori();
//                $this->enableTabAzioni();
//                $this->enableTabParametri();
//                break;
            case 'ENDOPROCEDIMENTOWRKF-0':
            case 'ENDOPROCEDIMENTOWRKF-1':
                $this->enableTabPassi();
                $this->enableTabAllegati();
                $this->enableTabSportello();
                $this->enableTabRequisiti(false);
                $this->enableTabNormativa(false);
                $this->enableTabDiscipline(false);
                $this->enableTabObbligatori(false);
                $this->enableTabAzioni(false);
                $this->enableTabParametri();
                $this->enableTabWorkflow();
                break;
            default:
                $this->enableTabPassi();
                $this->enableTabAllegati();
                $this->enableTabSportello();
                $this->enableTabRequisiti(false);
                $this->enableTabNormativa(false);
                $this->enableTabDiscipline(false);
                $this->enableTabObbligatori(false);
                $this->enableTabAzioni(false);
                $this->enableTabParametri();
                $this->enableTabWorkflow(false);
                break;
        }

        if (!$slave) {
            Out::show($this->nameForm . '_CancellaPassi');
            Out::show($this->nameForm . '_ExportPassi');
            Out::show($this->nameForm . '_ImportPassi');
//            Out::show($this->nameForm . '_divBottoniAllega');
//            Out::show($this->nameForm . '_gridAllegati_delGridRow');
        } else {
            Out::hide($this->nameForm . '_CancellaPassi');
            Out::hide($this->nameForm . '_ExportPassi');
            Out::hide($this->nameForm . '_ImportPassi');
//            Out::hide($this->nameForm . '_gridAllegati_delGridRow');
//            Out::hide($this->nameForm . '_divBottoniAllega');
        }
    }

    public function cancellaPassiSel() {
//        $delete_Info = 'Oggetto: Cancellazione passi selezionati procedimento ' . $this->currPranum;


        $praLibPasso = new praLibPasso();
        $praLibPasso->cancellaPassi($this->passiSel, $_POST[$this->nameForm . '_ANAPRA']['ROWID'], $this->currPranum, $this->passi);

        if ($praLibPasso->getErrCode()) {
            Out::msgStop("Errore", $praLibPasso->getErrMessage());
            return false;
        }

        /*
          foreach ($this->passiSel as $key => $cancPasso) {

          /*
         * Cancello dati aggiuntivi
          /
          if (!$this->deleteRecordItedag($cancPasso['ITEKEY'])) {
          Out::msgStop("Errore", "Errore in cancellazione dati agg passo " . $cancPasso['ITEKEY']);
          return false;
          }

          /*
         * Cancello destinatari
          /
          if (!$this->deleteRecordItedest($cancPasso['ITEKEY'])) {
          Out::msgStop("Errore", "Errore in cancellazione destinatari passo " . $cancPasso['ITEKEY']);
          return false;
          }

          /*
         * Cancello ITECONTROLLI
          /
          $Itecontrolli_tab = $this->praLib->GetItecontrolli($cancPasso['ITEKEY']);
          if ($Itecontrolli_tab) {
          $delete_Info = "Oggetto: Cancellazione controlli procedimento " . $this->currPranum;
          foreach ($Itecontrolli_tab as $Itecontrolli_rec) {
          if (!$this->deleteRecord($this->PRAM_DB, 'ITECONTROLLI', $Itecontrolli_rec['ROWID'], $delete_Info)) {
          Out::msgStop("Errore", "Errore in cancellazione controlli " . $Itecontrolli_rec['ITEKEY']);
          break;
          }
          }
          }

          if (!$this->deleteRecord($this->PRAM_DB, 'ITEPAS', $cancPasso['ROWID'], $delete_Info)) {
          return false;
          }
          }

          $Anapra_rec = array('ROWID' => $_POST[$this->nameForm . '_ANAPRA']['ROWID']);
          $Anapra_rec = $this->praLib->SetMarcaturaProcedimento($Anapra_rec);
          $update_Info = 'Oggetto: Aggiornamento marcatura procedimento n. ' . $this->currPranum;
          if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $Anapra_rec, $update_Info)) {
          Out::msgStop("Cancellazione Passi", "Errore in aggiornamento Marcatura Procedimento");
          }


          $errCanc = $this->controlloPassidaCancellare($this->passiSel);

          if ($errCanc) {
          foreach ($errCanc as $value) {
          $itepas_rec = $this->praLib->GetItepas($value['PASSO_ROWID'], 'rowid');
          $itepas_rec['ITEVPA'] = '';
          $itepas_rec['ITEVPN'] = '';
          $update_Info = "Oggetto: Cancellazione passo selezionato con chiave " . $itepas_rec['ITEKEY'];
          if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepas_rec, $update_Info)) {
          return false;
          }
          }
          }


          $this->praLib->ordinaPassiProc($this->currPranum);


         */


        $Anapra_rec = $this->praLib->GetAnapra($this->currPranum);
        $this->visualizzaMarcatura($Anapra_rec);

        $this->caricaPassi($this->currPranum);
    }

    public function controlloPassidaCancellare($passiSel) {
        $errCanc = array();
        foreach ($passiSel as $key => $cancPasso) {
// per ogni passo da cancellare ....
            foreach ($this->passi as $key => $passo) {    // per ogni passo presente in tabella .....
                if ($cancPasso['ITEKEY'] == $passo['ITEVPA'] || $cancPasso['ITEKEY'] == $passo['ITEVPN']) {     // se ITEKEY da cancellare è presente in una domanda ....
                    $daCancellare = 0;
                    foreach ($passiSel as $key => $ctrCancPasso) {    // controllo se l'ITEKEY della domanda è tra i passi da cancellare ....
                        if ($passo['ITEKEY'] == $ctrCancPasso['ITEKEY']) {
                            $daCancellare = 1;
                        }
                    }
                    if ($daCancellare == 0) {       // se non è nell'elenco dei cancellandi ....  segnalazione
                        $errCanc[] = array(
                            'PASSO_ROWID' => $passo['ROWID'],
                            'PASSO_CANC' => $cancPasso['ITESEQ'],
                            'PASSO_DOMA' => $passo['ITESEQ']
                        );
                    }
                }
            }
        }
        return $errCanc;
    }

    public function insertRecordItepas() {
        if (!$this->insertTo == 0) {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $this->currPranum . "' AND ITESEQ > '" . $this->insertTo . "' ORDER BY ITESEQ";
            $itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($itepas_tab) {
                foreach ($itepas_tab as $itepas_rec) {
                    $itepas_rec['ITESEQ'] = $itepas_rec['ITESEQ'] + 500;
                    $update_Info = 'Oggetto: Aggiornamento passo con chiave ' . $itepas_rec['ITEKEY'];
                    if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepas_rec, $update_Info)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function visualizzaMarcatura($Anapra_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anapra_rec['PRAUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anapra_rec['PRAUPDDATE'])) . ' ' . $Anapra_rec['PRAUPDTIME'] . '  </span>');
    }

    public function nuovo($codice = '', $descrizione = '') {
        Out::attributo($this->nameForm . '_ANAPRA[PRANUM]', 'readonly', '1');
        Out::hide($this->divRic);
        Out::hide($this->divDup);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        if ($codice) {
            Out::valore($this->nameForm . "_ANAPRA[PRANUM]", $codice);
        }
        if ($descrizione) {
            Out::valore($this->nameForm . "_PRADES", $descrizione);
            Out::attributo($this->nameForm . '_PRADES', 'readonly');
        }

        Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_panePassi");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAllegati");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneClassOnLine");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneRequisiti");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNormativa");
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneParametri");
        Out::show($this->nameForm . '_Aggiungi');
        if (!$this->autoSearch) {
            Out::show($this->nameForm . '_AltraRicerca');
        }
        Out::setFocus('', $this->nameForm . '_PRADES');
    }

    public function ControlliCanc($rowid) {
        $Itepas_rec = $this->praLib->GetItepas($rowid, "rowid");
        $msg = "<span style=\"font-weight:bold;\">Impossibile cancellare il passo " . $Itepas_rec['ITESEQ'] . " - " . $Itepas_rec['ITEDES'] . " perche è utilizzato nei seguenti procedimenti:</span>";
        if (!$this->praLib->CheckUsagePassoTemplate($Itepas_rec['ITEKEY'], $msg)) {
            return false;
        }

        $sql1 = "SELECT * FROM ITEPAS WHERE ITEVPA = '{$Itepas_rec['ITEKEY']}'";
        $Itepas_tab_si = ItaDB::DBSQLSelect($this->PRAM_DB, $sql1, true);
        if ($Itepas_tab_si) {
            $seqPassiDomanda = '';
            foreach ($Itepas_tab_si as $key => $Itepas_rec_si) {
                $seqPassiDomanda .= " - " . $Itepas_rec_si['ITESEQ'];
            }
            Out::msgStop("Errore.", 'I passi con sequenza ' . $seqPassiDomanda . '. <br> Saltano in questo passo. Impossibile cancellare.<br>Pulire i salti prima di cancellare il passo e quindi ripristinarli.');
            return false;
        }

        $sql2 = "SELECT * FROM ITEPAS WHERE ITEVPN = '{$Itepas_rec['ITEKEY']}'";
        $Itepas_tab_no = ItaDB::DBSQLSelect($this->PRAM_DB, $sql2, true);
        if ($Itepas_tab_no) {
            $seqPassiDomanda = '';
            foreach ($Itepas_tab_no as $key => $Itepas_rec_no) {
                $seqPassiDomanda .= " - " . $Itepas_rec_no['ITESEQ'];
            }
            Out::msgStop("Errore.", 'I passi con sequenza ' . $seqPassiDomanda . '.<br> Saltano in questo passo. Impossibile cancellare.<br>Pulire i salti prima di cancellare il passo e quindi ripristinarli.');
            return false;
        }

        $itevpadett_tab = $this->praLib->GetItevpadett($Itepas_rec['ITEKEY'], 'itevpa');
        if ($itevpadett_tab) {
            $seqPassiDomanda = '';
            foreach ($itevpadett_tab as $itevpadett_rec) {
                $Itepas_rec_salto = $this->praLib->GetItepas($itevpadett_rec['ITEKEY'], 'itekey');
                $seqPassiDomanda .= " - " . $Itepas_rec_salto['ITESEQ'];
            }
            Out::msgStop("Errore.", 'I passi con sequenza ' . $seqPassiDomanda . '.<br> Saltano in questo passo. Impossibile cancellare.<br>Pulire i salti prima di cancellare il passo e quindi ripristinarli.');
            return false;
        }

        return true;
    }

    private function setClientConfig($WSClient) {
        $endPoint = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSENDPOINT', false);
        $wsdl = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSWSDL', false);
        $nameSpace = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSNAMESPACE', false);
        $nameSpaceTem = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSNAMESPACETEM', false);
        $nameSpaceWfc = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSNAMESPACEWCF', false);
        $utente = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSUTENTE', false);
        $password = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'WSPASSWORD', false);
        $cfEnte = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice', 'CFENTE', false);
        //
        $WSClient->setWebservices_uri($endPoint['CONFIG']);
        $WSClient->setWebservices_wsdl($wsdl['CONFIG']);
        $WSClient->setMainNamespace($nameSpace['CONFIG']);
        $WSClient->setNamespaceTem($nameSpaceTem['CONFIG']);
        $WSClient->setNamespaceWcf($nameSpaceWfc['CONFIG']);
        $WSClient->setNamespaces();
        $WSClient->setUsername($utente['CONFIG']);
        $WSClient->setPassword($password['CONFIG']);
        $WSClient->setCF($cfEnte['CONFIG']);
    }

    function DecodeEvento($codice, $tipo = "codice") {
        $anaeventi_rec = $this->praLib->GetAnaeventi($codice, $tipo);
        Out::valore($this->nameForm . '_evento', "");
        Out::valore($this->nameForm . '_descEvento', "");
        if ($anaeventi_rec) {
            Out::valore($this->nameForm . '_evento', $anaeventi_rec['EVTCOD']);
            Out::valore($this->nameForm . '_descEvento', $anaeventi_rec['EVTDESCR']);
        }
    }

    public function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            switch ($order) {
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
                default:
                    asort($sortable_array);
                    break;
            }
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }

    private function caricaDiagramma($procedimento) {
        /* @var $praProcDiagramma praProcDiagramma */
        $praProcDiagramma = itaModel::getInstance('praProcDiagramma', $this->praProcDiagrammaFormname);
        $praProcDiagramma->caricaDiagramma($procedimento);
        $praProcDiagramma = null;
    }

    private function riordinaAllegati() {
        array_multisort(array_column($this->allegati, 'SEQUENZA'), SORT_ASC, $this->allegati);
        $i = 1;
        foreach ($this->allegati as $k => $allegato) {
            $this->allegati[$k]['SEQUENZA'] = 10 * $i++;
        }
    }

    private function GestioneInformativa($Anapra_rec) {
        $Filent_rec = $this->praLib->GetFilent(1);

        if ($Anapra_rec['PRASLAVE'] && $Filent_rec['FILDE4'] == 'S' && $Filent_rec['FILDE3']) {
            Out::show($this->nameForm . '_divConsultaPassi');
            Out::show($this->nameForm . '_ANAPRA[PRAINF]_field');
        } else {
            Out::hide($this->nameForm . '_divConsultaPassi');
            Out::hide($this->nameForm . '_ANAPRA[PRAINF]_field');
        }

        if (!$Anapra_rec['PRAINF'] && $Anapra_rec['PRASLAVE'] && $Filent_rec['FILDE4'] == 'S' && $Filent_rec['FILDE3']) {
            Out::block($this->gridRequisiti);
            Out::block($this->gridNormative);
            Out::block($this->gridDiscipline);

            Out::hide($this->nameForm . '_gridRequisiti_addGridRow');
            Out::hide($this->nameForm . '_gridRequisiti_delGridRow');
            Out::hide($this->nameForm . '_gridNormative_addGridRow');
            Out::hide($this->nameForm . '_gridNormative_delGridRow');
            Out::hide($this->nameForm . '_gridDiscipline_addGridRow');
            Out::hide($this->nameForm . '_gridDiscipline_delGridRow');

            Out::show($this->nameForm . '_divConsultaRequisiti');
            Out::show($this->nameForm . '_divConsultaNormative');
            Out::show($this->nameForm . '_divConsultaDiscipline');
        } else {
            Out::unblock($this->gridRequisiti);
            Out::unblock($this->gridNormative);
            Out::unblock($this->gridDiscipline);

            Out::show($this->nameForm . '_gridRequisiti_addGridRow');
            Out::show($this->nameForm . '_gridRequisiti_delGridRow');
            Out::show($this->nameForm . '_gridNormative_addGridRow');
            Out::show($this->nameForm . '_gridNormative_delGridRow');
            Out::show($this->nameForm . '_gridDiscipline_addGridRow');
            Out::show($this->nameForm . '_gridDiscipline_delGridRow');

            Out::hide($this->nameForm . '_divConsultaRequisiti');
            Out::hide($this->nameForm . '_divConsultaNormative');
            Out::hide($this->nameForm . '_divConsultaDiscipline');
        }
    }

    public function RichiediNuovoGruppo() {
        $valori = $this->GetCampiGruppo();
        Out::msgInput(
                'Inserimento Nuovo Gruppo', $valori, array(
            'Conferma ' => array('id' => $this->nameForm . '_ConfermaInserimentoGruppo', 'model' => $this->nameForm),
            'Annulla ' => array('id' => $this->nameForm . '_AnnullaInserimentoGruppo', 'model' => $this->nameForm)
                ), $this->nameForm
        );
        //Out::hide($this->nameForm . '_AllegatoTipo');
        //Out::hide($this->nameForm . '_AllegatoTipo_lbl');
    }

    public function GetCampiGruppo() {
        $OpzioniTipologiaParere = array();

        foreach (segLibPareri::$PARERE_TIPI_DECODIFICA_SINT as $key => $value) {
            $OpzioniTipologiaParere[] = array($key, $value);
        }

        App::log($OpzioniTipologiaParere);

        $valori[] = array(
            'label' => 'Descrizione<br>',
            'id' => $this->nameForm . '_NuovaDescrizione',
            'name' => $this->nameForm . '_NuovaDescrizione',
            'type' => 'textarea',
            'cols' => '40',
            'rows' => '3',
            'maxlength' => '100',
            'class' => "ita-edit");

        foreach (praLibDiagramma::$STATI_GRUPPO as $stato => $descrizioneStato) {
            $options[] = array($stato, $descrizioneStato);
        }

        $valori[] = array(
            'label' => array(
                'value' => "Stato",
                'style' => 'width:40px;display:block;float:left;padding: 0 5px 0 0;text-align: left;'
            ),
            'id' => $this->nameForm . '_StatoTipo',
            'name' => $this->nameForm . '_StatoTipo',
            'type' => 'select',
            'options' => $options,
            'class' => "ita-edit-onchange ");


        return $valori;
    }

    function caricaGrigliaGruppi($pranum) {
        $griglia = $this->gridGruppi;

        TableView::clearGrid($griglia);
        $sql = "SELECT * FROM ITEDIAGGRUPPI "
                . " WHERE PRANUM = '$pranum' "
                . " ORDER BY ROW_ID";

        $elencoGruppi = itaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        foreach ($elencoGruppi as $key => $itediaggruppi_rec) {
            // ASSEGNAPASSI
            $elencoGruppi[$key]['ASSEGNAPASSI'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title='Associa Passi'></span>";
            $elencoGruppi[$key]['STATO'] = praLibDiagramma::$STATI_GRUPPO[$itediaggruppi_rec['STATO']];
            $elencoGruppi[$key]['NUMPASSI'] = $this->getNumeroPassi($itediaggruppi_rec['ROW_ID']);
        }

        if ($elencoGruppi) {
            $ita_grid01 = new TableView($griglia, array(
                'arrayTable' => $elencoGruppi,
                'rowIndex' => 'idx'));
            $ita_grid01->setPageRows(1000000);
            $ita_grid01->getDataPage('json', true);
            TableView::enableEvents($griglia);
        }

        return;
    }

    public function OpenDettaglioGruppo() {
        $itediaggruppi_rec = $this->praLib->GetItediaggruppi($this->curRowidComposizione);

        $valori = $this->GetCampiGruppo();

        Out::msgInput(
                'Modifica Gruppo', $valori, array(
            'Aggiorna ' => array('id' => $this->nameForm . '_AggiornaGruppo', 'model' => $this->nameForm),
            'Annulla ' => array('id' => $this->nameForm . '_AnnullaInserimentoGruppo', 'model' => $this->nameForm)
                ), $this->nameForm
        );


        Out::valore($this->nameForm . '_NuovaDescrizione', $itediaggruppi_rec['DESCRIZIONE']);
        Out::valore($this->nameForm . '_StatoTipo', $itediaggruppi_rec['STATO']);
    }

    function aggiornaGruppo($formData) {

        $iteDiagGruppi_rec = $this->praLib->GetItediaggruppi($this->curRowidComposizione);

        if (isset($formData[$this->nameForm . "_NuovaDescrizione"])) {
            $iteDiagGruppi_rec['DESCRIZIONE'] = $formData[$this->nameForm . "_NuovaDescrizione"];
        }
        if (isset($formData[$this->nameForm . "_StatoTipo"])) {
            $iteDiagGruppi_rec['STATO'] = $formData[$this->nameForm . "_StatoTipo"];
        }


        try {
            $nrow_1 = $this->updateRecord($this->PRAM_DB, 'ITEDIAGGRUPPI', $iteDiagGruppi_rec, "Modificato gruppo Row_id: $this->curRowidComposizione Descrizione: {$iteDiagGruppi_rec['DESCRIZIONE']}", "ROW_ID");

            if (!$nrow_1) {
                Out::msgStop("Errore", "Errore aggiornamento Gruppo.");
                return;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return;
        }


        return;
    }

    function getNumeroPassi($row_Id_DiagGruppi) {

        $itediagpassigruppi_tab = $this->praLib->GetItediagPassiGruppi($row_Id_DiagGruppi);

        return count($itediagpassigruppi_tab);
    }

    private function refreshGrigliaGruppiPassi() {
        $model = itaModel::getInstance('utiJqGridCustom', $this->passiGruppiFormName);
        $model->refresh();
    }

    private function elaboraTabella($anapraTab) {
        foreach ($anapraTab as $key => $anapra) {
            if ($anapra['PROCEDIMARCHE'] != '') {
                $anapraTab[$key]['PROCEDIMARCHE'] = "<span class=\"ita-icon ita-icon-regioneMarche-24x24\">Pubblicato su procediMarche</span>";
            }
        }
        return $anapraTab;
    }

}
