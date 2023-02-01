<?php

/**
 *
 * ANAGRAFICA SPORTELLI ON-LINE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    23.10.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAcl.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praAzioniFO.class.php';

function praSportelli() {
    $praSportelli = new praSportelli();
    $praSportelli->parseEvent();
    return;
}

class praSportelli extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praLibAcl;
    public $utiEnte;
    public $accLib;
    public $proRic;
    public $proLibSerie;
    public $proLib;
    public $emlLib;
    public $nameForm = "praSportelli";
    public $divGes = "praSportelli_divGestione";
    public $divRis = "praSportelli_divRisultato";
    public $divRic = "praSportelli_divRicerca";
    public $gridSportelli = "praSportelli_gridSportelli";
    public $gridInfoFO = "praSportelli_gridInfoFO";
    public $gridGiorni = "praSportelli_gridGiorni";
    public $appoggio;
    public $rowidAppoggio;
    public $infoFO = array();
    public $gridAzioniFO = "praSportelli_gridAzioniFO";
    public $azioniFO = array();
    public $gridTemplateMail = "praSportelli_gridTemplateMail";
    public $templateMail = array();
    public $utiGridMetaDataNameForm;

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->praLib = new praLib();
            $this->praLibAcl = new praLibAcl();
            $this->proRic = new proRic();
            $this->proLibSerie = new proLibSerie();
            $this->proLib = new proLib();
            $this->emlLib = new emlLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->accLib = new accLib();
            $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->appoggio = App::$utente->getKey($this->nameForm . "_appoggio");
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . "_rowidAppoggio");
        $this->infoFO = App::$utente->getKey($this->nameForm . "_infoFO");
        $this->azioniFO = App::$utente->getKey($this->nameForm . '_azioniFO');
        $this->templateMail = App::$utente->getKey($this->nameForm . '_templateMail');
        $this->utiGridMetaDataNameForm = App::$utente->getKey($this->nameForm . '_utiGridMetaDataNameForm');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_appoggio", $this->appoggio);
            App::$utente->setKey($this->nameForm . "_rowidAppoggio", $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . "_infoFO", $this->infoFO);
            App::$utente->setKey($this->nameForm . '_azioniFO', $this->azioniFO);
            App::$utente->setKey($this->nameForm . '_templateMail', $this->templateMail);
            App::$utente->setKey($this->nameForm . '_utiGridMetaDataNameForm', $this->utiGridMetaDataNameForm);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->CaricaTemplateMail();
                $this->PopolaTabellaTemplateMail();
                $this->OpenRicerca();
                $this->utiGridMetaDataNameForm = $this->praLibAcl->caricaGridCondivisioneAccessi($this->nameForm . "_divDatiACL");
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridSportelli:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAzioniFO:
                        $praAzioneFO = new praAzioniFO();
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
                            case "INV":
                                $sel4 = true;
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
                                    array("CONT", $praAzioneFO->GetDescErroreAzione('CONT'), $sel1),
                                    array("ERR", $praAzioneFO->GetDescErroreAzione('ERR'), $sel2),
                                    array("WARN", $praAzioneFO->GetDescErroreAzione('WARN'), $sel3),
                                    array("INV", $praAzioneFO->GetDescErroreAzione('INV'), $sel4)
                                )
                            ),
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatiAzione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;
                    case $this->gridInfoFO:
                        itaLib::openForm('praInfoFO', true);
                        $praInfo = itaModel::getInstance('praInfoFO');
                        $praInfo->setEvent('openform');
                        $praInfo->setReturnEvent("returnInfoFO");
                        $praInfo->setReturnModel($this->nameForm);
                        $praInfo->setMode("edit");
                        $praInfo->setRowidInfo($_POST['rowid']);
                        $praInfo->setInfo($this->infoFO[$_POST['rowid']]);
                        $praInfo->parseEvent();
                        break;
                    case $this->gridGiorni:
                        $rowid = $_POST[$this->nameForm . '_gridGiorni']['gridParam']['selrow'];
                        $Orarifo = $this->praLib->GetOrariFo($rowid, 'rowid');
                        Out::msgInput(
                                "Inserisci Giorno", array(
                            array(
                                'label' => array('style' => "width:70px;", 'value' => 'Giorno'),
                                'id' => $this->nameForm . '_Giorno',
                                'name' => $this->nameForm . '_Giorno',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '10',
                                'class' => 'ita-date',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Descrizione'),
                                'id' => $this->nameForm . '_Descrizione',
                                'name' => $this->nameForm . '_Descrizione',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '40'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Da ora'),
                                'id' => $this->nameForm . '_Daora',
                                'name' => $this->nameForm . '_Daora',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '5',
                                'class' => 'ita-time',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'A ora'),
                                'id' => $this->nameForm . '_Aora',
                                'name' => $this->nameForm . '_Aora',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '5',
                                'class' => 'ita-time',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Nega'),
                                'id' => $this->nameForm . '_Nega',
                                'name' => $this->nameForm . '_Nega',
                                'type' => 'checkbox',
                                'width' => '50',
                                'size' => '5',
                                'maxlength' => '10'
                            ),
                            array(
                                'label' => array('style' => "width:50px;", 'value' => ''),
                                'id' => $this->nameForm . '_Rowid',
                                'name' => $this->nameForm . '_Rowid',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '5',
                                'maxlength' => '10',
                                'class' => 'ita-hidden'
                            )
                                ), array(
                            'Aggiorna' => array('id' => $this->nameForm . '_AggiornaGiorno', 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm)
                                ), $this->nameForm
                        );
                        Out::valore($this->nameForm . '_Daora', $Orarifo[0]['ORINI']);
                        Out::valore($this->nameForm . '_Aora', $Orarifo[0]['ORFIN']);
                        Out::valore($this->nameForm . '_Nega', $Orarifo[0]['ORNEGA']);
                        Out::valore($this->nameForm . '_Giorno', $Orarifo[0]['ORDATA']);
                        Out::valore($this->nameForm . '_Rowid', $Orarifo[0]['ROWID']);
                        Out::valore($this->nameForm . '_Descrizione', $Orarifo[0]['ORDESCRIZIONE']);
                        break;
                    case $this->gridTemplateMail:
                        $rigaMail = $_POST['rowid'];
                        if ($rigaMail) {
                            $_POST = array();
                            $model = 'praTemplateMail';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnMethod'] = 'returnBodyMail';
                            $_POST['RIGAMAIL'] = $rigaMail;
                            $_POST['TIPOMAIL'] = $this->templateMail[$rigaMail]['TIPOMAIL'];
                            $_POST['OGGETTOMAIL'] = $this->templateMail[$rigaMail]['DATA']['SUBJECT'];
                            $_POST['BODYMAIL'] = $this->templateMail[$rigaMail]['DATA']['BODY'];
                            itaLib::openDialog($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridSportelli:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridInfoFO:
                        Out::msgQuestion("Cancellazione", "Attenzione l'operazione è irreversibile. Confermi la Cancellazioned della seguente Info Front Office?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaInfo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;

                    case $this->gridGiorni:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaGiorno', 'model' => $this->nameForm, 'shortCut' => "f5")
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
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridInfoFO:
                        itaLib::openForm('praInfoFO', true);
                        $praInfo = itaModel::getInstance('praInfoFO');
                        $praInfo->setEvent('openform');
                        $praInfo->setReturnEvent("returnInfoFO");
                        $praInfo->setReturnModel($this->nameForm);
                        $praInfo->setMode("new");
                        $praInfo->setRowidInfo("");
                        $praInfo->parseEvent();
                        break;
                    case $this->gridGiorni:
                        Out::msgInput(
                                "Inserisci Giorno", array(
                            array(
                                'label' => array('style' => "width:70px;", 'value' => 'Giorno'),
                                'id' => $this->nameForm . '_Giorno',
                                'name' => $this->nameForm . '_Giorno',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '10',
                                'class' => 'ita-date',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Descrizione'),
                                'id' => $this->nameForm . '_Descrizione',
                                'name' => $this->nameForm . '_Descrizione',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '40'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Da ora'),
                                'id' => $this->nameForm . '_Daora',
                                'name' => $this->nameForm . '_Daora',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '5',
                                'class' => 'ita-time',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'A ora'),
                                'id' => $this->nameForm . '_Aora',
                                'name' => $this->nameForm . '_Aora',
                                'type' => 'text',
                                'width' => '50',
                                'size' => '5',
                                'class' => 'ita-time',
                                'maxlength' => '10'
                            ), array(
                                'label' => array('style' => "width:70px;", 'value' => 'Nega'),
                                'id' => $this->nameForm . '_Nega',
                                'name' => $this->nameForm . '_Nega',
                                'type' => 'checkbox',
                                'width' => '50',
                                'size' => '5',
                                'maxlength' => '10'
                            )
                                ), array(
                            'Conferma' => array('id' => $this->nameForm . '_InserisciGiorno', 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm)
                                ), $this->nameForm
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridSportelli, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('TSPDES');
                $ita_grid01->exportXLS('', 'sportelli.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridSportelli:
                        $ordinamento = $_POST['sidx'];
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $Result_tab = $ita_grid01->getDataArray();
                        $ita_grid01->getDataPageFromArray('json', $Result_tab);
                        break;
                    case $this->gridGiorni:
                        $ordinamento = $_POST['sidx'];
                        $sql = $this->CreaSqlGiorni($_POST[$this->nameForm . '_ANATSP']['TSPCOD']);
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        //$Result_tab = $ita_grid01->getDataArray();
                        $ita_grid01->getDataPage('json', $ita_grid01);
                        break;
                }
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praSportelli', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Torna':
                    case $this->nameForm . '_Elenca':
                        // Importo l'ordinamento del filtro
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridSportelli, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('TSPDES');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {
                                // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridSportelli);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);

                        Out::attributo($this->nameForm . '_ANATSP[TSPCOD]', 'readonly', '1');
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANATSP[TSPCOD]');
                        Out::tabDisable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneAzioniFO");
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        unset($_POST[$this->nameForm . '_ANATSP']['ROWID']);
                        $Anatsp_ric = $this->praLib->GetAnatsp($codice);
                        if (!$Anatsp_ric) {
                            $Anatsp_ric = $_POST[$this->nameForm . '_ANATSP'];
                            if (!$this->controlli($Anatsp_ric)) {
                                break;
                            }
                            $Anatsp_ric['TSPMETAJSON'] = $this->getMetaJson();
                            try {
                                $insert_Info = 'Oggetto: ' . $Anatsp_ric['TSPCOD'] . $Anatsp_ric['TSPDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANATSP', $Anatsp_ric, $insert_Info)) {
                                    
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANATSP[TSPCOD]');
                        }
                        $this->CaricaAzioni($codice);
                        $this->SalvaAzioniFO();
                        Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneAzioniFO");
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];
                        if (!$this->controlli($Anatsp_rec)) {
                            break;
                        }

                        $arrayMetaDati = $this->infoFO;
                        $arrayMetaDati['TEMPLATEMAIL'] = $this->getArrayMail();


                        //$Anatsp_rec['TSPMETA'] = serialize($this->infoFO);
                        $Anatsp_rec['TSPMETA'] = serialize($arrayMetaDati);
                        $Anatsp_rec['TSPMETAJSON'] = $this->praLibAcl->salvaMetadatiGridCondivisioneAccessi($this->utiGridMetaDataNameForm);

                        $update_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . $Anatsp_rec['TSPDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANATSP', $Anatsp_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        $Orarifo = array();

                        for ($i = 1; $i < 10; $i++) {
                            if (($_POST[$this->nameForm . "_DAORA_$i"] && $_POST[$this->nameForm . "_AORA_$i"]) || $_POST[$this->nameForm . "_FNEGA_$i"]) {
                                $Orarifo['ORTSPCOD'] = $Anatsp_rec['TSPCOD'];
                                $Orarifo['ORDATA'] = '';
                                $Orarifo['ORINI'] = $_POST[$this->nameForm . "_DAORA_$i"];
                                $Orarifo['ORFIN'] = $_POST[$this->nameForm . "_AORA_$i"];
                                $Orarifo['ORGIORNONUM'] = $i - 1;
                                $Orarifo['ORGIORNOSTR'] = $this->GiornoSettimana($i - 1);
                                $Orarifo['ORNEGA'] = $_POST[$this->nameForm . "_FNEGA_$i"];
                                if ($i - 1 <= 6) {
                                    $Orarifo['ORTIPO'] = 'GS';
                                }
                                if ($i - 1 == 7) {
                                    $Orarifo['ORTIPO'] = 'PR';
                                }
                                if ($i - 1 == 8) {
                                    $Orarifo['ORTIPO'] = 'FE';
                                }
                                $sql = "SELECT * FROM ORARIFO WHERE ORTSPCOD='" . $Anatsp_rec['TSPCOD'] . "' AND ORGIORNONUM='" . ($i - 1) . "' AND ORTIPO!='DT' AND ORDATEANN=''";
                                $orarifo_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                                if ($orarifo_rec) {
                                    $Orarifo_rec = $orarifo_rec;
                                    if (($Orarifo['ORINI'] != $orarifo_rec['ORINI']) || ($Orarifo['ORFIN'] != $orarifo_rec['ORFIN']) || ($Orarifo['ORNEGA'] != $orarifo_rec['ORNEGA'])) {
                                        $Orarifo_rec['ORUTEEDIT'] = App::$utente->getKey('nomeUtente');
                                        $Orarifo_rec['ORDATEEDIT'] = date('Ymd');
                                        $Orarifo_rec['ORORAEDIT'] = date('H:i:s');
                                        $Orarifo_rec['ORINI'] = $_POST[$this->nameForm . "_DAORA_$i"];
                                        $Orarifo_rec['ORFIN'] = $_POST[$this->nameForm . "_AORA_$i"];
                                        $Orarifo_rec['ORNEGA'] = $_POST[$this->nameForm . "_FNEGA_$i"];
                                    }

                                    $update_Info = $Orarifo['ORTSPCOD'];
                                    $this->updateRecord($this->PRAM_DB, 'ORARIFO', $Orarifo_rec, $update_Info);
                                } else {
                                    $Orarifo['ORUTEADD'] = App::$utente->getKey('nomeUtente');
                                    $Orarifo['ORDATEADD'] = date('Ymd') . ' ' . date('H:i:s');
                                    $insert_Info = $Orarifo['ORTSPCOD'];
                                    $this->insertRecord($this->PRAM_DB, 'ORARIFO', $Orarifo, $insert_Info);
                                }
                            } else {
                                $sql = "SELECT * FROM ORARIFO WHERE ORTSPCOD='" . $Anatsp_rec['TSPCOD'] . "' AND ORGIORNONUM='" . ($i - 1) . "' AND ORTIPO!='DT' AND ORDATEANN=''";
                                $orarifo_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                                $update_Info = $orarifo_rec['ORTSPCOD'];
                                if ($orarifo_rec) {
                                    $Orarifo = $orarifo_rec;
                                    $Orarifo['ORUTEEDIT'] = App::$utente->getKey('nomeUtente');
                                    $Orarifo['ORDATEEDIT'] = date('Ymd');
                                    $Orarifo['ORORAEDIT'] = date('H:i:s');
                                    $Orarifo['ORDATEANN'] = date('Ymd');
                                    $this->updateRecord($this->PRAM_DB, 'ORARIFO', $Orarifo, $update_Info);
                                }
                            }
                        }
                        $this->SalvaAzioniFO();
                        break;

                    case $this->nameForm . '_InserisciGiorno':
                        $Orarifo['ORTSPCOD'] = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        $Orarifo['ORTIPO'] = 'DT';
                        $Orarifo['ORDATA'] = $_POST[$this->nameForm . '_Giorno'];
                        $Orarifo['ORINI'] = $_POST[$this->nameForm . '_Daora'];
                        $Orarifo['ORFIN'] = $_POST[$this->nameForm . '_Aora'];
                        $Orarifo['ORGIORNONUM'] = date('w', strtotime($Orarifo['ORDATA']));
                        $Orarifo['ORGIORNOSTR'] = $this->GiornoSettimana($Orarifo['ORGIORNONUM']);
                        $Orarifo['ORNEGA'] = $_POST[$this->nameForm . '_Nega'];
                        $Orarifo['ORUTEADD'] = App::$utente->getKey('nomeUtente');
                        $Orarifo['ORDATEADD'] = date('Ymd') . ' ' . date('H:i:s');
                        $Orarifo['ORDESCRIZIONE'] = $_POST[$this->nameForm . '_Descrizione'];
                        $insert_Info = $Orarifo['ORTSPCOD'] . ' ' . $Orarifo['ORDESCRIZIONE'];
                        if (($Orarifo['ORNEGA'] || ( $Orarifo['ORINI'] && $Orarifo['ORFIN'])) && $Orarifo['ORDATA']) {
                            $this->insertRecord($this->PRAM_DB, 'ORARIFO', $Orarifo, $insert_Info);
                        } else {
                            Out::msginfo("Attenzione", "E' obbligatorio inserire una data con gli orari (Da - A) o Flag Nega");
                            break;
                        }
                        TableView::reload($this->gridGiorni);
                        break;

                    case $this->nameForm . '_AggiornaGiorno':
                        $Orarifo['ORTSPCOD'] = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        $Orarifo['ORTIPO'] = 'DT';
                        $Orarifo['ORDATA'] = $_POST[$this->nameForm . '_Giorno'];
                        $Orarifo['ORINI'] = $_POST[$this->nameForm . '_Daora'];
                        $Orarifo['ORFIN'] = $_POST[$this->nameForm . '_Aora'];
                        $Orarifo['ORGIORNONUM'] = date('w', strtotime($Orarifo['ORDATA']));
                        $Orarifo['ORGIORNOSTR'] = $this->GiornoSettimana($Orarifo['ORGIORNONUM']);
                        $Orarifo['ORNEGA'] = $_POST[$this->nameForm . '_Nega'];
                        $Orarifo['ROWID'] = $_POST[$this->nameForm . '_Rowid'];
                        $Orarifo['ORDESCRIZIONE'] = $_POST[$this->nameForm . '_Descrizione'];
                        $update_Info = $Orarifo['ORTSPCOD'] . ' ' . $Orarifo['ORDESCRIZIONE'];
                        if (($Orarifo['ORNEGA'] || ( $Orarifo['ORINI'] && $Orarifo['ORFIN'])) && $Orarifo['ORDATA']) {
                            $orarifo_rec = $this->praLib->GetOrariFo($Orarifo['ROWID'], 'rowid');
                            $Orario_rec = $orarifo_rec[0];
                            if (($Orarifo['ORINI'] != $orarifo_rec[0]['ORINI']) || ( $Orarifo['ORFIN'] != $orarifo_rec[0]['ORFIN']) || ($Orarifo['ORNEGA'] != $orarifo_rec[0]['ORNEGA'])) {
                                $Orario_rec['ORUTEEDIT'] = App::$utente->getKey('nomeUtente');
                                $Orario_rec['ORDATEEDIT'] = date('Ymd');
                                $Orario_rec['ORORAEDIT'] = date('H:i:s');
                                $Orario_rec['ORINI'] = $_POST[$this->nameForm . '_Daora'];
                                $Orario_rec['ORFIN'] = $_POST[$this->nameForm . '_Aora'];
                                $Orario_rec['ORNEGA'] = $_POST[$this->nameForm . '_Nega'];
                            }
                            $this->updateRecord($this->PRAM_DB, 'ORARIFO', $Orario_rec, $update_Info);
                        } else {
                            Out::msginfo("Attenzione", "E' obbligatorio inserire una data con gli orari (Da - A) o Flag Nega");
                            break;
                        }
                        TableView::reload($this->gridGiorni);
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancellaAzione':
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['CLASSEAZIONE'] = '';
                        $this->azioniFO[$_POST[$this->gridAzioniFO]['gridParam']['selrow']]['METODOAZIONE'] = '';
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . $Anatsp_rec['TSPDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANATSP', $Anatsp_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA NORMATIVE", $e->getMessage());
                        }


                        //Cancello azioni

                        if ($Anatsp_rec['TSPCOD'] > 0) {
                            $Praazioni_tab = $this->praLib->GetTSPazioni($Anatsp_rec['TSPCOD'], 'codice', true);
                            if ($Praazioni_tab) {
                                $delete_Info = "Oggetto: Cancellazione azioni sportello " . $Anatsp_rec['TSPCOD'];
                                foreach ($Praazioni_tab as $Praazioni_rec) {
                                    if (!$this->deleteRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec['ROWID'], $delete_Info)) {
                                        Out::msgStop("Errore", "Errore in cancellazione azione " . $Praazioni_rec['CODICEAZIONE']);
                                        break;
                                    }
                                }
                            }
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancellaGiorno':
                        $Orarifo_rec = $this->praLib->GetOrariFo($_POST[$this->nameForm . '_gridGiorni']['gridParam']['selrow'], 'rowid');
                        if ($Orarifo_rec) {
                            $Orarifo_rec[0]['ORUTEEDIT'] = App::$utente->getKey('nomeUtente');
                            $Orarifo_rec[0]['ORDATEEDIT'] = date('Ymd');
                            $Orarifo_rec[0]['ORORAEDIT'] = date('H:i:s');
                            $Orarifo_rec[0]['ORDATEANN'] = date('Ymd');
                            $update_info = 'Oggetto: ' . $Orarifo_rec['ORTSPCOD'] . $Orarifo_rec['ORDESCRIZIONE'];
                            $this->updateRecord($this->PRAM_DB, 'ORARIFO', $Orarifo_rec[0], $update_info);
                        }
                        TableView::reload($this->gridGiorni);
                        break;

                    case $this->nameForm . '_ConfermaCancellaInfo':
                        $update_Info = "Oggetto: cancello info FO " . $this->infoFO[$this->rowidAppoggio]['CODICE'] . " - " . $this->infoFO[$this->rowidAppoggio]['DESCRIZIONE'];
                        unset($this->infoFO[$this->rowidAppoggio]);
                        $Anatsp_rec = $_POST[$this->nameForm . '_ANATSP'];
                        $Anatsp_rec['TSPMETA'] = serialize($this->infoFO);
                        if (!$this->updateRecord($this->PRAM_DB, 'ANATSP', $Anatsp_rec, $update_Info)) {
                            Out::msgStop("Errore in Cancellazione info FO " . $this->infoFO[$this->rowidAppoggio]['CODICE'] . " - " . $this->infoFO[$this->rowidAppoggio]['DESCRIZIONE']);
                            break;
                        }

                        $this->CaricaGriglia($this->gridInfoFO, $this->infoFO);
                        break;
                    case $this->nameForm . '_ANATSP[TSPSERIE]_butt':
                        proRic::proRicSerieArc($this->nameForm);
                        break;
                    case $this->nameForm . '_ANATSP[TSPSUPERADMIN]_butt':
                        accRic::accRicGru($this->nameForm);
                        break;
                    case $this->nameForm . '_ANATSP[TSPRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA DIPENDENTI", '', "tspres");
                        break;
                    case $this->nameForm . '_SelezionaAccount':
                        emlRic::emlRicAccount($this->nameForm, '', 'Smtp');
                        break;
                    case $this->nameForm . '_SvuotaAccount':
                        Out::valore($this->nameForm . '_ANATSP[TSPPEC]', '');
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmpt':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_ANATSP[TSPPEC]', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_ConfermaDatiAzione':
                        $AzioniFO = new praAzioniFO();
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['CLASSEAZIONE'] = $_POST[$this->nameForm . '_ClasseAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['METODOAZIONE'] = $_POST[$this->nameForm . '_MetodoAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['ERROREAZIONE'] = $_POST[$this->nameForm . '_ErroreAzione'];
                        $this->azioniFO[$_POST[$this->nameForm . '_IdGridAzione']]['OPERAZIONE'] = $AzioniFO->GetDescErroreAzione($_POST[$this->nameForm . '_ErroreAzione']);
                        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Tspcod':
                        $codice = $_POST[$this->nameForm . '_Tspcod'];
                        if (trim($codice) != "") {
                            $Anatsp_rec = $this->praLib->getAnatsp($codice);
                            if ($Anatsp_rec) {
                                $this->Dettaglio($Anatsp_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Tspcod', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANATSP[TSPRES]':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPRES'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANATSP[TSPSUPERADMIN]':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPSUPERADMIN'];
                        if ($codice) {
                            $Anagru_rec = $this->accLib->GetGruppi($codice);
                            Out::valore($this->nameForm . "_ANATSP[TSPSUPERADMIN]", $Anagru_rec['GRUCOD']);
                            Out::valore($this->nameForm . "_Superadmin", $Anagru_rec['GRUDES']);
                        }
                        break;
                    case $this->nameForm . '_ANATSP[TSPCOD]':
                        $codice = $_POST[$this->nameForm . '_ANATSP']['TSPCOD'];
                        if (trim($codice) != "") {
                            Out::valore($this->nameForm . '_ANATSP[TSPCOD]', $codice);
                        }
                        break;
                    case $this->nameForm . '_ANATSP[TSPSERIE]':
                        if ($_POST[$this->nameForm . '_ANATSP']['TSPSERIE']) {
                            $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_ANATSP']['TSPSERIE'], 'codice');
                            if ($AnaserieArc_rec) {
                                Out::valore($this->nameForm . '_ANATSP[TSPSERIE]', $AnaserieArc_rec['CODICE']);
                                Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                                $this->getMaxProgserie($AnaserieArc_rec);
                            } else {
                                Out::valore($this->nameForm . '_ANATSP[TSPSERIE]', '');
                                Out::valore($this->nameForm . '_ric_siglaserie', '');
                                Out::valore($this->nameForm . "_Numero_serie", '');
                                Out::valore($this->nameForm . "_Anno_serie", '');
                            }
                        }
                        break;
                }
                break;
            case 'returngru':
                $Anagru_rec = $this->accLib->GetGruppi($_POST["retKey"], 'rowid');
                if ($Anagru_rec) {
                    Out::valore($this->nameForm . "_ANATSP[TSPSUPERADMIN]", $Anagru_rec['GRUCOD']);
                    Out::valore($this->nameForm . "_Superadmin", $Anagru_rec['GRUDES']);
                }
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    $this->DecodResponsabile($Ananom_rec);
                }
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ANATSP[TSPSERIE]', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                }
                $this->getMaxProgserie($AnaserieArc_rec);
                break;
            case 'returnAccountSmtp':
                $this->appoggio = $_POST['retKey'];
                $valori[] = array(
                    'label' => array(
                        'value' => "Password:",
                        'style' => 'margin-top:10px;width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                    ),
                    'id' => $this->nameForm . '_passwordAccount',
                    'name' => $this->nameForm . '_passwordAccount',
                    'type' => 'password',
                    'style' => 'margin-top:10px;width:550px;',
                    'value' => ''
                );
                $messaggio = "Inserisci la password dell'account selezionato";
                Out::msgInput(
                        'Account Email', $valori
                        , array(
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordSmpt', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;
            case "returnInfoFO":
                if ($_POST["rowid"] != "") {
                    $this->infoFO[$_POST["rowid"]] = $_POST['info'];
                } else {
                    $this->infoFO[] = $_POST['info'];
                }
                $this->CaricaGriglia($this->gridInfoFO, $this->infoFO);
                break;
            case 'returnBodyMail':
                $this->templateMail[$_POST['RIGAMAIL']]['DATA']['SUBJECT'] = $_POST['OGGETTOMAIL'];
                $this->templateMail[$_POST['RIGAMAIL']]['DATA']['BODY'] = $_POST['BODYMAIL'];
                out::closeCurrentDialog();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_infoFO');
        App::$utente->removeKey($this->nameForm . '_templateMail');
        App::$utente->removeKey($this->nameForm . '_utiGridMetaDataNameForm');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function DecodResponsabile($Ananom_rec) {
        Out::valore($this->nameForm . '_ANATSP[TSPRES]', $Ananom_rec["NOMRES"]);
        Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
    }

    function CreaSql() {
// Imposto il filtro di ricerca
        $sql = "SELECT * FROM ANATSP WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm . '_Tspcod'] != "") {
            $sql .= " AND TSPCOD = '" . $_POST[$this->nameForm . '_Tspcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Tspdes'] != "") {
            $sql .= " AND TSPDES LIKE '%" . addslashes($_POST[$this->nameForm . '_Tspdes']) . "%'";
        }
        return $sql;
    }

    function CreaSqlGiorni($Codice) {
        $sql = "SELECT * FROM ORARIFO WHERE ORTIPO='DT' AND ORTSPCOD='$Codice' AND ORDATEANN=''";
        return $sql;
    }

    function controlli($Anatsp_rec) {
        $Serie_rec = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_ANATSP']['TSPSERIE'], 'codice');
        if (!$Serie_rec) {
            Out::msgStop("Errrore", "Codice serie non trovato!");
            return false;
        }
        $Ananom_rec = $this->praLib->GetAnanom($Anatsp_rec['TSPRES']);
        app::log($Anatsp_rec);
        if (!$Ananom_rec) {
            return false;
        }

        if (!$Ananom_rec['NOMEML']) {
            Out::msgStop("Errrore", "Il responsabile scelto non ha un indirizzo mail!");
            return false;
        }
        return true;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridSportelli);
        TableView::clearGrid($this->gridSportelli);
//        TableView::clearGrid($this->gridAggregati);
        $this->Nascondi();
        $this->caricaSubForms();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Tspcod');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    public function Dettaglio($Indice) {
        $Anatsp_rec = $this->praLib->GetAnatsp($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anatsp_rec['TSPCOD'] . " " . $Anatsp_rec['TSPDES'];
        $this->openRecord($this->PRAM_DB, 'ANATSP', $open_Info);
        $this->Nascondi();
        Out::valori($Anatsp_rec, $this->nameForm . '_ANATSP');
        if ($Anatsp_rec['TSPSUPERADMIN']) {
            $Gruppi_rec = $this->accLib->GetGruppi($Anatsp_rec['TSPSUPERADMIN']);
            Out::valore($this->nameForm . "_Superadmin", $Gruppi_rec['GRUDES']);
        }
        $Ananom_rec = $this->praLib->GetAnanom($Anatsp_rec['TSPRES']);
        if ($Ananom_rec) {
            $this->DecodResponsabile($Ananom_rec);
        }

        if (!$this->emlLib->getMailAccountList()) {
            Out::hide($this->nameForm . "_SelezionaAccount");
            Out::hide($this->nameForm . "_SvuotaAccount");
            Out::attributo($this->nameForm . "_ANATSP[TSPPEC]", 'readonly', '1');
        } else {
            Out::show($this->nameForm . "_SelezionaAccount");
            Out::show($this->nameForm . "_SvuotaAccount");
            Out::attributo($this->nameForm . "_ANATSP[TSPPEC]", 'readonly', '0');
        }

        //$infoFO = unserialize($Anatsp_rec['TSPMETA']);
        $arrayMetadati = unserialize($Anatsp_rec['TSPMETA']);
        $arrayMail = $arrayMetadati['TEMPLATEMAIL'];
        foreach ($this->templateMail as $key => $template) {
            $this->templateMail[$key]['DATA']['SUBJECT'] = $arrayMail['SUBJECT_' . $this->templateMail[$key]['CHIAVE']];
            $this->templateMail[$key]['DATA']['BODY'] = $arrayMail['BODY_' . $this->templateMail[$key]['CHIAVE']];
        }

        unset($arrayMetadati['TEMPLATEMAIL']);
        $infoFO = $arrayMetadati;
        $this->infoFO = $this->praLib->array_sort($infoFO, "CODICE");
        $this->CaricaGriglia($this->gridInfoFO, $this->infoFO);
        Out::tabEnable($this->nameForm . "_tabProcedimentoFO", $this->nameForm . "_paneAzioniFO");
        $this->CaricaAzioni($Anatsp_rec['TSPCOD']);
//        if ($Anatsp_rec['TSPPEC']) {
//            $Mail_account_rec = $this->emlLib->getMailAccount($Anatsp_rec['TSPPEC']);
//            if (!$Mail_account_rec) {
//                
//            }
//        }
        //$Orarifo_tab = $this->praLib->GetOrariFo($Anatsp_rec['TSPCOD'], 'codice');
        $sql = "SELECT * FROM ORARIFO WHERE ORTSPCOD='" . $Anatsp_rec['TSPCOD'] . "' AND ORDATEANN='' AND ORTIPO!='DT'";
        $Orarifo_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        foreach ($Orarifo_tab as $Orarifo_rec) {
            $i = $Orarifo_rec['ORGIORNONUM'] + 1;
            Out::valore($this->nameForm . "_DAORA_$i", $Orarifo_rec['ORINI']);
            Out::valore($this->nameForm . "_AORA_$i", $Orarifo_rec['ORFIN']);
            Out::valore($this->nameForm . "_FNEGA_$i", $Orarifo_rec['ORNEGA']);
        }

        TableView::enableEvents($this->gridGiorni);
        TableView::reload($this->gridGiorni);
        if ($Anatsp_rec['TSPSERIE']) {
            $decod_serie = $this->proLibSerie->GetSerie($Anatsp_rec['TSPSERIE'], 'codice');
            Out::valore($this->nameForm . "_ANATSP[TSPSERIE]", $Anatsp_rec['TSPSERIE']);
            Out::valore($this->nameForm . "_ric_siglaserie", $decod_serie['SIGLA']);
            $this->getMaxProgserie($decod_serie);
        }
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANATSP[TSPDES]');
        Out::attributo($this->nameForm . '_ANATSP[TSPCOD]', 'readonly', '0');

        $this->praLibAcl->popolaGridCondivisioneAccessi($Anatsp_rec['TSPMETAJSON'], $this->utiGridMetaDataNameForm);

        TableView::disableEvents($this->gridSportelli);
    }

    function CaricaGriglia($griglia, $appoggio) {
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_ANATSP[TSPTIP]', 1, '', "0", '');
        Out::select($this->nameForm . '_ANATSP[TSPTIP]', 1, 'Singolo', "0", 'Singolo');
        Out::select($this->nameForm . '_ANATSP[TSPTIP]', 1, 'Aggregato', "0", 'Aggregato');
        //
        Out::select($this->nameForm . '_ANATSP[TSPSECURESMTP]', 1, "", "1", "");
        Out::select($this->nameForm . '_ANATSP[TSPSECURESMTP]', 1, "tls", "0", "tls");
        Out::select($this->nameForm . '_ANATSP[TSPSECURESMTP]', 1, "ssl", "0", "ssl");
    }

    public function getMaxProgserie($serie) {
        if ($serie['TIPOPROGRESSIVO'] == 'ANNUALE') {
            Out::show($this->nameForm . '_Numero_serie_field');
            Out::show($this->nameForm . '_Anno_serie_field');
            $anno = '20' . date('y');
            $serie_anno = $this->proLibSerie->getMaxProgserie($serie['CODICE'], $anno);
            if ($serie_anno) {
                Out::valore($this->nameForm . "_Numero_serie", $serie_anno['PROGRESSIVO']);
                Out::valore($this->nameForm . "_Anno_serie", $anno);
            } else {
                Out::valore($this->nameForm . "_Numero_serie", '');
                Out::valore($this->nameForm . "_Anno_serie", '');
            }
        } elseif ($serie['TIPOPROGRESSIVO'] == 'ASSOLUTO') {
            $serie_assoluta = $this->proLibSerie->GetSerie($serie['CODICE'], 'codice');
            Out::show($this->nameForm . '_Numero_serie_field');
            Out::hide($this->nameForm . '_Anno_serie_field');
            Out::valore($this->nameForm . "_Numero_serie", $serie_assoluta['PROGRESSIVO']);
        } elseif ($serie['TIPOPROGRESSIVO'] != 'ASSOLUTO' && $serie['TIPOPROGRESSIVO'] != 'ANNUALE') {
            Out::hide($this->nameForm . '_Numero_serie_field');
            Out::hide($this->nameForm . '_Anno_serie_field');
        }
    }

    public function GiornoSettimana($GiornoNum) {
        switch ($GiornoNum) {
            case 0:
                $GiornoStr = 'DO';
                break;
            case 1:
                $GiornoStr = 'LU';
                break;
            case 2:
                $GiornoStr = 'MA';
                break;
            case 3:
                $GiornoStr = 'ME';
                break;
            case 4:
                $GiornoStr = 'GI';
                break;
            case 5:
                $GiornoStr = 'VE';
                break;
            case 6:
                $GiornoStr = 'SA';
                break;
            case 7:
                $GiornoStr = 'PR';
                break;
            case 8:
                $GiornoStr = 'FE';
                break;
        }
        return $GiornoStr;
    }

    private function caricaSubForms() {
        /*
         * carico pannello AZIONI FO
         */
        $generator = new itaGenerator();
        $retHtml = $generator->getModelHTML('pradivAzioniFO', false, $this->nameForm, true);
        Out::html($this->nameForm . '_divAzioni', $retHtml);
    }

    public function CaricaAzioni($sportello) {
        $AzioniFO = new praAzioniFO();
        $this->azioniFO = $AzioniFO->getGridAzioniSportello($sportello);
        $this->CaricaGriglia($this->gridAzioniFO, $this->azioniFO);
    }

    function SalvaAzioniFO() {
        foreach ($this->azioniFO as $Praazioni_rec) {
            unset($Praazioni_rec['DESCRIZIONEAZIONE']);
            unset($Praazioni_rec['OPERAZIONE']);
            $Praazioni_rec['PRATSP'] = $this->formData[$this->nameForm . "_ANATSP"]['TSPCOD'];
            if (isset($Praazioni_rec['ROWID'])) {
                $update_Info = 'Oggetto: Aggiornamento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $update_Info)) {
                    Out::msgStop("Errore", "Aggiornamneto azione FO su sportello " . $Praazioni_rec['PRATSP'] . " " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            } else {
                $insert_Info = 'Oggetto: Inserimento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $insert_Info)) {
                    Out::msgStop("Errore", "Inserimento azione FO su sportello " . $Praazioni_rec['PRATSP'] . " " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            }
        }
        return true;
    }

    public function CaricaTemplateMail() {
        $this->templateMail = array();
        $this->templateMail[1] = array('RIGAMAIL' => 1, 'TIPOMAIL' => "Mail al richiedente di conferma inoltro richiesta OnLine", 'CHIAVE' => "RICHIEDENTE");
        $this->templateMail[2] = array('RIGAMAIL' => 2, 'TIPOMAIL' => "Mail ai responsabili procedimento di conferma inoltro richiesta OnLine", 'CHIAVE' => "RESPONSABILE");
        $this->templateMail[3] = array('RIGAMAIL' => 3, 'TIPOMAIL' => "Mail al richiedente di conferma inoltro richiesta di integrazione", 'CHIAVE' => "INT_RICH");
        $this->templateMail[4] = array('RIGAMAIL' => 4, 'TIPOMAIL' => "Mail ai responsabili procedimento di conderma inoltro richiesta di Integrazione", 'CHIAVE' => "INT_RESP");
        $this->templateMail[5] = array('RIGAMAIL' => 5, 'TIPOMAIL' => "Mail all'ente terzo di conferma inoltro parere espresso", 'CHIAVE' => "ARICPARERI");
        $this->templateMail[6] = array('RIGAMAIL' => 6, 'TIPOMAIL' => "Mail ai responsabili procedimento di inoltro parere espresso", 'CHIAVE' => "AENTITERZI");
    }

    public function PopolaTabellaTemplateMail() {
        $ita_grid01 = new TableView(
                $this->gridTemplateMail, array('arrayTable' => $this->templateMail,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('10');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridTemplateMail);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridTemplateMail);
        }
    }

    private function getArrayMail() {
        foreach ($this->templateMail as $key => $template) {
            $arrayMail['SUBJECT_' . $this->templateMail[$key]['CHIAVE']] = $this->templateMail[$key]['DATA']['SUBJECT'];
            $arrayMail['BODY_' . $this->templateMail[$key]['CHIAVE']] = $this->templateMail[$key]['DATA']['BODY'];
        }
        return $arrayMail;
    }

}
