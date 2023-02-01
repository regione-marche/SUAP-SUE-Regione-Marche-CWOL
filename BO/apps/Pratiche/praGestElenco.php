<?php

/**
 *
 * ANAGRAFICA FASCICOLI ELETTRONICI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2015 Italsoft sRL
 * @license
 * @version    18.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElenco.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEstrazione.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibChiusuraMassiva.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibExportXls.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';

function praGestElenco() {
    $praGestElenco = new praGestElenco();
    $praGestElenco->parseEvent();
    return;
}

class praGestElenco extends itaModel {

    public $PRAM_DB;
    public $utiEnte;
    public $accLib;
    public $nameForm = "praGestElenco";
    public $divRic = "praGestElenco_divRicerca";
    public $divRis = "praGestElenco_divRisultato";
    public $gridGest = "praGestElenco_gridGest";
    public $praLib;
    public $praLibAllegati;
    public $proLib;
    public $proLibSerie;
    public $returnModel;
    public $returnId;
    public $page;
    public $eqAudit;
    public $praReadOnly;
    public $flagAssegnazioni;
    public $profilo;
    public $openMode;
    public $openRowid;
    public $praLibEstrazione;
    public $searchMode = false;
    public $fascicoliSel = array();
    public $chiusuraFascicoli;
    public $rowidFiera;
    private $fixedSeries;
    private $praLibElenco;
    private $praLibChiusuraMassiva;
    private $praLibExportXls;

    function __construct() {
        parent::__construct();
    }

    function postInstance() {
        parent::postInstance();

        $this->ditta = App::$utente->getKey($this->nameForm . '_ditta');


        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);

        $this->divRic = $this->nameForm . "_divRicerca";
        $this->divRis = $this->nameForm . "_divRisultato";
        $this->gridGest = $this->nameForm . "_gridGest";

        try {
            $this->utiEnte = new utiEnte();
            $this->praLib = new praLib($this->ditta);
            $this->proLib = new proLib();
            $this->praLibAllegati = new praLibAllegati();
            $this->accLib = new accLib();
            $this->eqAudit = new eqAudit();
            $this->praLibElenco = new praLibElenco();
            $this->praLibEstrazione = new praLibEstrazione();
            $this->praLibChiusuraMassiva = new praLibChiusuraMassiva();
            $this->praLibExportXls = new praLibExportXls();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
            if ($Filent_Rec_TabAss['FILVAL'] == 1) {
                $this->flagAssegnazioni = true;
            }
            $this->profilo = proSoggetto::getProfileFromIdUtente();
            $this->fixedSeries = $this->profilo['SERIE_SOGGETTO'];
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->proLibSerie = new proLibSerie();
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnId = App::$utente->getKey($this->nameForm . '_returnId');
        $this->page = App::$utente->getKey($this->nameForm . '_page');
        $this->praReadOnly = App::$utente->getKey($this->nameForm . '_praReadOnly');
        $this->searchMode = App::$utente->getKey($this->nameForm . '_searchMode');
        $this->fascicoliSel = App::$utente->getKey($this->nameForm . '_fascicoliSel');
        $this->chiusuraFascicoli = App::$utente->getKey($this->nameForm . '_chiusuraFascicoli');
        $this->rowidFiera = App::$utente->getKey($this->nameForm . '_rowidFiera');

        /*
         * Riassegnate per poter supportare model con Alias (Dialog di ricerca)
         */
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnId', $this->returnId);
            App::$utente->setKey($this->nameForm . '_praReadOnly', $this->praReadOnly);
            App::$utente->setKey($this->nameForm . '_searchMode', $this->searchMode);
            App::$utente->setKey($this->nameForm . '_fascicoliSel', $this->fascicoliSel);
            App::$utente->setKey($this->nameForm . '_chiusuraFascicoli', $this->chiusuraFascicoli);
            App::$utente->setKey($this->nameForm . '_ditta', $this->ditta);
            App::$utente->setKey($this->nameForm . '_rowidFiera', $this->rowidFiera);
        }
    }

    public function getOpenMode() {
        return $this->openMode;
    }

    public function setOpenMode($openMode) {
        $this->openMode = $openMode;
    }

    public function getOpenRowid() {
        return $this->openRowid;
    }

    public function setOpenRowid($openRowid) {
        $this->openRowid = $openRowid;
    }

    public function setModelParam($params) {
        $params = unserialize($params);
        foreach ($params as $func => $args) {
            call_user_func_array(array($this->nameForm, $func), $args);
        }
    }

    function getDitta() {
        return $this->ditta;
    }

    function setDitta($ditta) {
        $this->ditta = $ditta;
    }

    public function getFixedSeries() {
        return $this->fixedSeries;
    }

    public function setFixedSeries($fixedSeries) {
        $this->fixedSeries = $fixedSeries;
    }

    public function setRowidFiera($rowidFiera) {
        $this->rowidFiera = $rowidFiera;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->getDitta()) {
                    $this->praLib = new praLib($this->getDitta());
                    $this->PRAM_DB = $this->praLib->getPRAMDB();
                }

                $this->chiusuraFascicoli = $_POST['chiusuraFascicoli'];
                $this->inizializzaForm();


                if ($this->getOpenMode() == 'edit' && $this->getOpenRowid()) {
                    $this->Dettaglio($this->getOpenRowid());
                    break;
                }

                if ($_POST['rowidDettaglio']) {
                    $this->praReadOnly = $_POST['praReadonly'];
                    $this->Dettaglio($_POST['rowidDettaglio']);
                } else {
                    $this->OpenRicerca();
                }

                //TableView::setColumnProperty($this->gridGest, "GESNUM", "width", "140");

                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        if ($this->searchMode == true) {
                            $this->returnId = $_POST['rowid'];
                            $this->returnToParent();
                        } elseif ($this->chiusuraFascicoli == 1) {
                            Out::msgInfo("Chiusura Fascicoli", "Dettaglio non visibile in questa modalità.");
                            break;
                        } else {
                            $this->daPortlet = $_POST['daPortlet'];
                            $this->Dettaglio($_POST['rowid'], $_POST['openForm']);
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        Out::msgQuestion("Estrazione Excel", "Scegli il tipo di Estrazione", array(
                            'Inserisci Campi Aggiuntivi' => array('id' => $this->nameForm . '_XlsInsertCampi', 'model' => $this->nameForm),
                            'Default' => array('id' => $this->nameForm . '_XlsDefault', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        //Scelgo Ordinamento
                        $ordinamento = $_POST['sidx'];
                        $sord = $_POST['sord'];
                        if ($ordinamento != 'IMPRESA' && $ordinamento != 'FISCALE') {
                            $arrayOrd = $this->praLib->GetOrdinamentoGridGest($ordinamento, $sord);
                            $ordinamento = $arrayOrd['sidx'];
                            $sord = $arrayOrd['sord'];
                            $sql = $this->CreaSql();
                            TableView::disableEvents($this->gridGest);
                            $ita_grid01 = new TableView($this->gridGest, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows($_POST['rows']);
                            $ita_grid01->setSortIndex($ordinamento);
                            $ita_grid01->setSortOrder($sord);
                            $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                            if ($ita_grid01->getDataPageFromArray('json', $Result_tab)) {
                                TableView::enableEvents($this->gridGest);
                            }

                            foreach ($ita_grid01->getDataArray() as $proges_rec) {
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESNUM", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "ANTECEDENTE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESPRA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "GESDRE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "RICEZ", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "DESNOM", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "IMPRESA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "DESCPROC", "", "{'padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "NOTE", "", "{'padding-top':'2px','padding-right':'2px'}", "", false);
                                TableView::setCellValue($this->gridGest, $proges_rec['ROWID'], "SPORTELLO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                            }
                        }
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridGest:
                        switch ($_POST['colName']) {
                            case 'ANTECEDENTE':
                                $proges_rec = $this->praLib->GetProges($_POST['rowid'], 'rowid');
                                praRic::praRicAntecedente($this->nameForm, 'returnAntecedente', $this->PRAM_DB, $proges_rec);
                                break;
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    /*
                     * Elenca
                     * 
                     */
                    case $this->nameForm . '_Elenca':
                        TableView::clearGrid($this->gridGest);
                        TableView::enableEvents($this->gridGest);
                        TableView::reload($this->gridGest);
                        Out::hide($this->divRic, '');
                        Out::show($this->divRis, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        if (!$this->searchMode) {
                            Out::show($this->gridGest . '_exportTableToExcel');
                        } else {
                            Out::hide($this->gridGest . '_exportTableToExcel');
                        }
                        Out::show($this->nameForm . '_Utilita');
                        break;

                    /*
                     * Nuovi fascicoli
                     */
                    case $this->nameForm . '_NuovaPratica':
                        $this->praLibElenco->caricaDatiPraticaNew('praGestDatiEssenziali', $this->nameForm, 'returnCaricaMailGenerica');
                        break;
                    case $this->nameForm . '_NuovaPraticaDaProt':
                        $this->praLibElenco->caricaDatiPraticaNew('praGestDatiEssenziali', $this->nameForm, 'returnCaricaMailGenerica', true);
                        break;
                    case $this->nameForm . '_NuovaPraticaDaProcedura':
                        $this->praLibElenco->caricaDatiPraticaNew('praGestDatiEssenziali', $this->nameForm, 'returnCaricaMailGenerica');
                        break;
                    case $this->nameForm . '_CaricaDaMail':
                        $this->praLibElenco->caricaDatiPraticaNew('praGestMail', $this->nameForm, 'returnElencoMail');
                        break;
                    case $this->nameForm . '_DaRemoto':
                        $this->praLibElenco->caricaDatiPraticaNew('praGestAcquisizioneBackendItalsoft', $this->nameForm, 'returnPraGestAcquisizioneBackendItalsoft');
                        break;
                    case $this->nameForm . '_Infocamere':
                        if (!itaLib::createPrivateUploadPath()) {
                            Out::msgStop("Gestione Arrivo da CAMERA DI COMMERCIO", "Creazione ambiente di lavoro temporaneo fallita");
                            return false;
                        }
                        if ($_POST["daPortlet"] == "true") {
                            $this->OpenRicerca();
                        }

                        $model = "praRichiesteInfocamere";
                        itaLib::openForm($model);
                        $proric_rec = $_POST['datiMail']['PRORIC_REC'];
                        $rowidChiamante = $_POST['rowidChiamante'];
                        $_POST = array();
                        $_POST['rowidChiamante'] = $rowidChiamante;
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent("openform");
                        $objModel->setProric_rec($proric_rec);
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_Controlla':
                        $this->praLibElenco->openControllaRichiesteFO($this->nameForm, $this->perms);
                        break;

                    /*
                     * Utilita
                     * 
                     */
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
                        $this->praLibEstrazione->stampaEstrazione($this->CreaSql(), $_POST[$this->nameForm . $this->praLibEstrazione->fieldPercentuale], $_POST[$this->nameForm . $this->praLibEstrazione->fieldOrdinamento]);
                        break;

                    case $this->nameForm . '_Stampa':
                        devRic::devElencoReport($this->nameForm, $_POST, " WHERE CODICE<>'' AND CATEGORIA='FASCICOLIELENCO'", 'Elenco');
                        break;

                    case $this->nameForm . '_ChiudiFascicoli':
                        $this->fascicoliSel = array();
                        $Result_tab_tmp = $this->praLibChiusuraMassiva->getArrayFascicoliTmp($this->CreaSql());
                        if (count($Result_tab_tmp) > 200) {
                            Out::msgInfo("Chiusura Fascicoli", "<b>Sono stati selezionati più di 200 fascioli.<br>Il Numero è tropop grande e potrebbe causare problemi in fasi di elaborazione.<br>Si prega di ripetere la selezione.</b>");
                            break;
                        }
                        $Result_tab = $this->elaboraRecords($Result_tab_tmp, true);
                        praRic::praRicProgesByArray($this->nameForm, $Result_tab);
                        break;

                    /*
                     * Eventi Dialog chiusura Fascicoli
                     * 
                     */
                    case $this->nameForm . "_statoChiusura_butt":
                        $msg = "<b>Confermando verrà chiusa la pratica n. $this->currGesnum.<br>Se sei sicuro di procedere, scegli unno stato.</b>";
                        praRic::praRicAnastp($this->nameForm, "WHERE STPFLAG LIKE 'Chiusa%'", "CHIUDI", $msg);
                        break;
                    case $this->nameForm . "_ConfermaChiusuraFascicoloMassiva":
                        $retChiudi = $this->praLibChiusuraMassiva->ChiudiFascicoli($this->fascicoliSel, $_POST[$this->nameForm . "_dataChiusura"], $_POST[$this->nameForm . "_statoChiusura"]);
                        Out::closeCurrentDialog();
                        if (!$retChiudi) {
                            Out::msgStop("Chiusura Fascicoli", $this->praLibChiusuraMassiva->getErrMessage());
                        } else {
                            Out::msgInfo("Chiusura Fascicoli", "Chiusi con successo " . count($this->fascicoliSel) . " fascicoli.");
                        }
                        TableView::reload($this->gridGest);
                        break;

                    /*
                     * Bottoni di servizio
                     * 
                     */
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca(true);
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        $this->AzzeraVariabili();
                        $this->CreaCombo();
                        break;
                    /*
                     * Funzioni Export excel
                     * 
                     */
                    case $this->nameForm . "_XlsInsertCampi":
                        $this->praLibExportXls->exportXlsAdvanced($this->nameForm, $this->nameFormOrig, $this->xlsxMode, $this->xlsxPageDescription, $this->xlsxDefaultModel);
                        break;
                    case $this->nameForm . "_XlsDefault":
                        $this->praLibExportXls->exportXLSDefault($this->CreaSql(), $this->gridGest);
                        break;

                    /*
                     * Gestione eventi Form
                     * 
                     */
                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca procedimento", $this->nameForm . '_Procedimento');
                        break;
                    case $this->nameForm . '_ric_siglaserie_butt':
                        $fixedSeries = implode(",", $this->fixedSeries);
                        if ($this->fixedSeries) {
                            $where = " WHERE CODICE IN ($fixedSeries)";
                        }
                        proRic::proRicSerieArc(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where);
                        break;
                    case $this->nameForm . '_Passo_butt':
                        praRic::praRicPraclt($this->nameForm);
                        break;
                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), '', "1");
                        break;
                    case $this->nameForm . '_Sportello_butt':
                        praRic:: praRicAnatsp(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", "2");
                        break;
                    case $this->nameForm . '_Sett_butt':
                        praRic:: praRicAnaset(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . '_Responsabile_butt':
                        praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Responsabile", '', $this->nameForm . '_Responsabile');
                        break;
                    case $this->nameForm . '_Settore_butt':
                        praRic::praRicAnauni(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA SETTORE", "returnSettore");
                        break;
                    case $this->nameForm . '_Servizio_butt':
                        praRic::praRicAnaSer($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA SERVIZIO", "AND UNISET = '" . $_POST[$this->nameForm . '_Settore'] . "'", "returnServizio");
                        break;
                    case $this->nameForm . '_NomeCampo_butt':
                        praRic::praRicPraidc(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), 'returnPraidcRic');
                        break;
                    case $this->nameForm . "_DescRuolo_butt":
                        praRic::praRicRuoli(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . "_Nominativo_butt":
                        praRic::praRicAnades(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        break;
                    case $this->nameForm . '_Tipologia_butt':
                        praRic::praRicAnatip($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Attivita");
                        break;
                    case $this->nameForm . '_Atti_butt':
                        if ($_POST[$this->nameForm . '_Sett']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_Sett'] . "'";
                        }
                        praRic::praRicAnaatt(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where);
                        break;
                    case $this->nameForm . '_CodUtenteAss_butt':
                        $msgDetail = "Scegliere il soggetto di cui si vuol sapere le pratiche assegnate.";
                        praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Soggetti", " WHERE NOMABILITAASS = 1 ", $this->nameForm . "_ricercaAss", false, null, $msgDetail, true);
                        break;
                    case $this->nameForm . "_CodTipoPasso_butt":
                        praRic::praRicPraclt(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "RICERCA Tipo Passo", "returnPraclt");
                        break;
                    case $this->nameForm . "_Evento_butt":
                        praRic::ricAnaeventi(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", "RIC");
                        break;
                    case $this->nameForm . '_TipologiaProg_butt':
                        praRic::praRicAnadoctipreg(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "", " WHERE FL_ATTIVO = 1");
                        break;
                    case $this->nameForm . '_StatoPasso_butt':
                        praRic::praRicAnastp(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), '', "STATOPASSO");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Mancanza':
                        if ($_POST[$this->nameForm . "_Mancanza"] == 1) {
                            Out::hide($this->nameForm . "_Via_field");
                            Out::hide($this->nameForm . "_Nominativo_field");
                            Out::hide($this->nameForm . "_codFis_field");
                        } else {
                            Out::show($this->nameForm . "_Via_field");
                            Out::show($this->nameForm . "_Nominativo_field");
                            Out::show($this->nameForm . "_codFis_field");
                        }
                        break;
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
                    case $this->nameForm . '_Gespre0':
                        if ($_POST[$this->nameForm . '_Gespre0']) {
                            $AnaserieArc_tab = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_Gespre0'], 'sigla', true);
                            if (!$AnaserieArc_tab) {
                                Out::msgStop("Attenzione", "Sigla Inesistente.");
                                Out::valore($this->nameForm . '_Gespre0', '');
                                Out::valore($this->nameForm . '_Gespre3', '');
                                break;
                            }
                            $result = count($AnaserieArc_tab);
                            if ($result > 1) {
                                $where = "WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($AnaserieArc_tab[0]['SIGLA']) . "'";
                                proRic::proRicSerieArc($this->nameForm, $where, 'returnSerieArcAnt');
                                break;
                            }
                            Out::valore($this->nameForm . '_Gespre3', $AnaserieArc_tab[0]['CODICE']);
                            Out::valore($this->nameForm . '_Gespre0', $AnaserieArc_tab[0]['SIGLA']);
                            break;
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dal_num':
                        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
                        if ($Dal_num) {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Dal_num', $Dal_num);
                        }
                        break;
                    case $this->nameForm . '_Al_num':
                        $Al_num = $_POST[$this->nameForm . '_Al_num'];
                        if ($Al_num) {
                            $Al_num = str_pad($Al_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Al_num', $Al_num);
                        }
                        break;
                    case $this->nameForm . '_Anno':
                        $Dal_num = $_POST[$this->nameForm . '_Dal_num'];
                        $al_num = $_POST[$this->nameForm . '_Al_num'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if ($anno != '' && $Dal_num == $al_num && $Dal_num != '') {
                            $proges_tab = $this->praLib->GetProges($anno . $Dal_num, 'codice', true);
                            if (count($proges_tab) == 1) {
                                if ($this->searchMode == true) {
                                    $this->returnId = $proges_tab[0]['ROWID'];
                                    $this->returnToParent();
                                } else {
                                    Out::valore($this->nameForm . '_Dal_num', '');
                                    Out::valore($this->nameForm . '_Al_num', '');
                                    $this->Dettaglio($proges_tab[0]['ROWID']);
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Da_richiesta':
                        $Da_richiesta = $_POST[$this->nameForm . '_Da_richiesta'];
                        if ($Da_richiesta) {
                            $Da_richiesta = str_pad($Da_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Da_richiesta', $Da_richiesta);
                        }
                        break;
                    case $this->nameForm . '_A_richiesta':
                        $Alla_richiesta = $_POST[$this->nameForm . '_A_richiesta'];
                        if ($Alla_richiesta) {
                            $Alla_richiesta = str_pad($Alla_richiesta, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_A_richiesta', $Alla_richiesta);
                        }
                        break;
                    case $this->nameForm . '_NumProt':
                        $NumProt = $_POST[$this->nameForm . '_NumProt'];
                        if ($NumProt) {
                            $NumProt = str_pad($NumProt, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_NumProt', $NumProt);
                        }
                        break;
                    case $this->nameForm . '_Procedimento':
                        $proc = $_POST[$this->nameForm . '_Procedimento'];
                        if ($proc) {
                            $proc = str_pad($proc, 6, "0", STR_PAD_LEFT);
                            $tipoEnte = $this->praLib->GetTipoEnte();
                            $praLib = $this->praLib;
                            $anapra_rec = $praLib->GetAnapra($proc);
                            Out::valore($this->nameForm . '_Procedimento', $proc);
                            Out::valore($this->nameForm . '_Desc_proc', $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2']);
                        }
                        break;
                    case $this->nameForm . '_Tipologia':
                        $codice = $_POST[$this->nameForm . '_Tipologia'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            $this->DecodAnatip($codice);
                        }
                        break;
                    case $this->nameForm . '_Evento':
                        $codice = $_POST[$this->nameForm . '_Evento'];
                        if ($codice) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnaeventi($codice, "codice", "RIC", true);
                        }
                        break;
                    case $this->nameForm . '_Sett':
                        $codice = $_POST[$this->nameForm . '_Sett'];
                        if ($codice) {
                            $this->DecodAnaset($codice);
                        }
                        break;
                    case $this->nameForm . '_Atti':
                        $codice = $_POST[$this->nameForm . '_Atti'];
                        if ($codice) {
                            $this->DecodAnaatt($codice);
                        }
                        break;
                    case $this->nameForm . '_Responsabile':
                        $codice = $_POST[$this->nameForm . '_Responsabile'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            $this->DecodAnanom($codice, $this->nameForm . "_Responsabile");
                        }
                        break;

                    case $this->nameForm . '_Settore':
                        $codice = $_POST[$this->nameForm . '_Settore'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_set = $this->praLib->getAnauni($codice);
                            Out::valore($this->nameForm . '_Settore', $codice);
                            Out::valore($this->nameForm . '_Desc_settore', $Anauni_set['UNIDES']);
                            Out::valore($this->nameForm . '_Servizio', '');
                            Out::valore($this->nameForm . '_Desc_servizio', '');
                        } else {
                            Out::valore($this->nameForm . '_Settore', '');
                            Out::valore($this->nameForm . '_Desc_settore', '');
                            Out::valore($this->nameForm . '_Servizio', '');
                            Out::valore($this->nameForm . '_Desc_servizio', '');
                        }
                        break;
                    case $this->nameForm . '_Servizio':
                        $codice = $_POST[$this->nameForm . '_Servizio'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anauni_ser = $this->praLib->GetAnauniServ($_POST[$this->nameForm . '_Settore'], $codice);
                            if ($Anauni_ser) {
                                Out::valore($this->nameForm . '_Servizio', $codice);
                                Out::valore($this->nameForm . '_Desc_servizio', $Anauni_ser['UNIDES']);
                            } else {
                                Out::valore($this->nameForm . '_Servizio', '');
                                Out::valore($this->nameForm . '_Desc_servizio', '');
                                Out::setFocus($this->nameForm . '_Servizio');
                                Out::msgInfo('ATTENZIONE', 'Codice Servizio non corretto per il Settore ' . $_POST[$this->nameForm . '_Settore']);
                            }
                        } else {
                            Out::valore($this->nameForm . '_Servizio', '');
                            Out::valore($this->nameForm . '_Desc_servizio', '');
                        }
                        break;

                    case $this->nameForm . '_CodUtenteAss':
                        $codice = $_POST[$this->nameForm . '_CodUtenteAss'];
                        if ($codice) {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            $this->DecodAnanom($codice, $this->nameForm . "_ricercaAss");
                        }
                        break;
                    case $this->nameForm . '_CodTipoPasso':
                        $codice = $_POST[$this->nameForm . '_CodTipoPasso'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->DecodPraclt($codice);
                        break;
                    case $this->nameForm . '_Sezione':
                        $sezione = $_POST[$this->nameForm . '_Sezione'];
                        if ($sezione) {
                            $sezione = str_pad($sezione, 3, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Sezione', $sezione);
                        }
                        break;
                    case $this->nameForm . '_Foglio':
                        $foglio = $_POST[$this->nameForm . '_Foglio'];
                        if ($foglio) {
                            $foglio = str_pad($foglio, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Foglio', $foglio);
                        }
                        break;
                    case $this->nameForm . '_Sub':
                        $sub = $_POST[$this->nameForm . '_Sub'];
                        if ($sub) {
                            $sub = str_pad($sub, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Sub', $sub);
                        }
                        break;
                    case $this->nameForm . '_Particella':
                        $particella = $_POST[$this->nameForm . '_Particella'];
                        if ($particella) {
                            $particella = str_pad($particella, 5, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Particella', $particella);
                        }
                        break;
                    case $this->nameForm . '_AnnoProt':
                        $NumProt = $_POST[$this->nameForm . '_NumProt'];
                        $anno = $_POST[$this->nameForm . '_AnnoProt'];
                        if ($anno && $NumProt) {
                            $proges_rec = $this->praLib->GetProges($anno . $NumProt, 'protocollo');
                            if ($proges_rec) {
                                $this->Dettaglio($proges_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_Anno_rich':
                        $Da_richiesta = $_POST[$this->nameForm . '_Da_richiesta'];
                        $Alla_richiesta = $_POST[$this->nameForm . '_A_richiesta'];
                        $anno = $_POST[$this->nameForm . '_Anno_rich'];
                        if ($anno != '' && $Da_richiesta == $Alla_richiesta && $Da_richiesta != '') {
                            $proges_tab = $this->praLib->GetProges($anno . $Da_richiesta, 'richiesta', true);
                            if (count($proges_tab) == 1) {
                                if ($this->searchMode == true) {
                                    $this->returnId = $proges_tab[0]['ROWID'];
                                    $this->returnToParent();
                                } else {
                                    Out::valore($this->nameForm . '_Da_richiesta', '');
                                    Out::valore($this->nameForm . '_A_richiesta', '');
                                    //Per pulizia campo da data reg dopo on-blur Anno richiesta
                                    Out::codice("jQuery('#" . $this->nameForm . "_Da_data" . "').blur();");
                                    $this->Dettaglio($proges_tab[0]['ROWID']);
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Aggregato':
                        $codice = $_POST[$this->nameForm . '_Aggregato'];
                        if ($codice) {
                            $this->DecodAnaspa1($codice);
                        }
                        break;
                    case $this->nameForm . '_Sportello':
                        $codice = $_POST[$this->nameForm . '_Sportello'];
                        if ($codice) {
                            $this->DecodAnatsp2($codice);
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
                    case $this->nameForm . "_statoChiusura":
                        if ($_POST[$this->nameForm . '_statoChiusura']) {
                            $anastp_rec = $this->praLib->GetAnastp($_POST[$this->nameForm . "_statoChiusura"], "rowid", " AND STPFLAG LIKE 'Chiusa%'");
                            if ($anastp_rec) {
                                Out::valore($this->nameForm . '_descStato', $anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_statoChiusura', "");
                                Out::valore($this->nameForm . '_descStato', "");
                                Out::setFocus('', $this->nameForm . '_statoChiusura');
                            }
                        }
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {

                    case $this->nameForm . '_NomeCampo':

                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Praidc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAIDC WHERE " . $this->PRAM_DB->strUpper('IDCKEY') . " LIKE '%"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Praidc_tab as $Praidc_rec) {

                            itaSuggest::addSuggest($Praidc_rec['IDCKEY']);
                        }

                        itaSuggest::sendSuggest();
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
                break;
            case 'returnSerieArcAnt':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_Gespre3', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_Gespre0', $AnaserieArc_rec['SIGLA']);
                }
                break;
            case 'returnUploadMail':
                $directMailFile = $_POST['uploadedFile']; //itaLib::createPrivateUploadPath() . "/" . $randName;
                if (strtolower(pathinfo($directMailFile, PATHINFO_EXTENSION)) != "eml") {
                    Out::msgInfo("Importazione File", "Il file da caricare deve essere un eml.");
                    break;
                }
                $model = 'proElencoMail';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['returnModel'] = 'praGestElenco';
                $_POST['returnEvent'] = 'returnElencoMail';
                $_POST['modoFiltro'] = "DIRECT";
                $_POST['directMailFile'] = $directMailFile;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'returnElencoMail':
                $this->inizializzaForm();
                $this->OpenRicerca();
                $this->CaricaDaPec();
                break;
            case 'returnAnapra':
                $this->DecodAnapra($_POST['rowData']['ID_ANAPRA'], $_POST['retid'], 'rowid', $this->dataRegAppoggio, $_POST['rowData']['ID_ITEEVT']);
                break;
            case 'returnAnaeventi':
                $this->DecodAnaeventi($_POST['retKey'], 'rowid', $_POST['retid'], true);
                break;
            case 'returnAnatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                break;
            case 'returnAnaspa1':
                $this->DecodAnaspa1($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnPraclt':
                $this->DecodPraclt($_POST['retKey'], 'rowid');
                break;
            case 'returnUnires':
                $this->DecodAnanom($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnUniset':
                $this->DecodSettore($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnUniser':
                $this->DecodServizio($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnAnatsp2':
                $this->DecodAnatsp2($_POST['retKey'], 'rowid');
                break;
            case 'returnElencoReportElenco':
                $tabella_rec = $_POST['rowData'];
                $_POST = $_POST['retid'];
                $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array(
                    "Sql" => $this->CreaSql(),
                    "Ente" => $parametriEnte_rec['DENOMINAZIONE']
                );
                $itaJR->runSQLReportPDF($this->PRAM_DB, $tabella_rec['CODICE'], $parameters);
                break;

            case 'returnAntecedente';
                $proges_rec = $this->praLib->GetProges($_POST['retKey'], 'rowid');
                if ($proges_rec) {
                    $this->Dettaglio($proges_rec['ROWID']);
                }
                break;
            case 'returnCtrRichieste':
                $this->CaricaDaPec();
                break;
            case 'returnCtrRichiesteFO':
                $datiAcquisizione = $_POST['datiAcquisizione'];
                if (!$this->Dettaglio($datiAcquisizione['GESNUM'], "codice")) {
                    $this->OpenRicerca();
                    return false;
                }
                break;
            case 'returnAnastp':
                if ($_POST['retid'] == "STATOPASSO") {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                } else {
                    $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                    Out::valore($this->nameForm . '_statoChiusura', $anastp_rec['ROWID']);
                    Out::valore($this->nameForm . '_descStato', $anastp_rec['STPDES']);
                }
                break;
            case 'returnAnaruo':
                $this->DecodAnaruo($_POST['retKey'], 'rowid');
                break;
            case 'returnAnades':
                $this->DecodAnades($_POST['retKey'], 'rowid');
                break;
            case 'returnAnatip':
                $this->DecodAnatip($_POST['retKey'], 'rowid');
                break;
            case "returnAnaset":
                $this->DecodAnaset($_POST['retKey'], 'rowid');
                break;
            case "returnAnaatt":
                $this->DecodAnaatt($_POST['retKey'], 'rowid');
                break;
            case 'returnCaricaMailGenerica':
                if ($_POST['carica'] === true) {
                    $_POST['datiMail'] = $_POST['datiMail']['Dati'];
                    $this->CaricaDaPec();
                }
                break;
            case 'returnPraidcRic':
                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], "rowid");
                Out::valore($this->nameForm . "_NomeCampo", $praidc_rec['IDCKEY']);
                break;
            case 'returnAnadoctipreg':
                $Anadoctipreg_rec = $this->praLib->GetAnadoctipreg($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_TipologiaProg', $Anadoctipreg_rec['CODDOCREG']);
                break;
            case 'returnProgesByArray':
                if (!$_POST['rowData']) {
                    Out::msgInfo("Chiusura Fascicoli", "Selezionare almeno un fasciolo da chiudere.");
                    break;
                }
                foreach ($_POST['rowData'] as $proges_rec) {
                    $this->fascicoliSel[] = $proges_rec['ROWID'];
                }
                $this->praLibChiusuraMassiva->getMsgInputChiudiFascicolo($this->nameForm, $this->fascicoliSel, $this->nameForm . '_ConfermaChiusuraFascicoloMassiva');
                break;
            case 'returnPraPasso':
                $Proges_rec = $this->praLib->GetProges($_POST['gesnum']);
                $this->Dettaglio($Proges_rec['ROWID']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_profilo');
        App::$utente->removeKey($this->nameForm . '_returnId');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_praReadOnly');
        App::$utente->removeKey($this->nameForm . '_searchMode');
        App::$utente->removeKey($this->nameForm . '_fascicoliSel');
        App::$utente->removeKey($this->nameForm . '_chiusuraFascicoli');
        App::$utente->removeKey($this->nameForm . '_ditta');
        App::$utente->removeKey($this->nameForm . '_rowidFiera');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        if ($this->returnModel != '') {

            $_POST = array();
            $_POST['rowid'] = $this->returnId;
            $_POST['id'] = "proStepIter2_gridStepProced";
            $model = $this->returnModel;
            if (!$this->searchMode) {
                itaLib::openForm($model);
            }
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent($this->returnEvent ?: 'dbClickRow');
            $objModel->parseEvent();
            Out::closeDialog($this->nameForm);
        } else {
            Out::show('menuapp');
        }
    }

    function OpenRicerca() {
        /*
         * Azzero le variabili conservate in Session
         */
        $Filent_Rec_22 = $this->praLib->GetFilent(22);

        Out::show($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        $this->AzzeraVariabili(false);
        $this->Nascondi();
        Out::show($this->nameForm . '_NuovaPratica');
        if ($Filent_Rec_22['FILDE1'] == 1) {
            Out::hide($this->nameForm . '_CaricaDaMail');
        } else {
            Out::show($this->nameForm . '_CaricaDaMail');
        }

        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . "_SvuotaRicerca");
        if (!$this->searchMode) {
            Out::show($this->nameForm . '_Controlla');
            Out::show($this->nameForm);
            Out::show($this->nameForm . '_divButtonNew');
            Out::hide($this->nameForm . '_MancanzaDesc');
            $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
            if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Iride' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'Jiride' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'Paleo4' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'Italsoft-ws' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'Paleo41' || $PARMENTE_rec['TIPOPROTOCOLLO'] == 'CiviliaNext') {
                Out::show($this->nameForm . '_NuovaPraticaDaProt');
            }

            /*
             * Leggo il parametro urlRest e se c'è visualizzo il bottone da Codice Procedura
             */
            $praGobid = new praGobidManager();
            $urlRest = $praGobid->leggiParametro('RESTURL');
            if ($urlRest) {
                Out::show($this->nameForm . "_NuovaPraticaDaProcedura");
            }

            /*
             * Leggo i parametri per backend remoto e visualizzo il bottone Da Remoto
             */
            include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
            $envLib = new envLib();
            $istanzeBackendRemoto = $envLib->getIstanze('BACKENDFASCICOLIITALSOFT');
            if (count($istanzeBackendRemoto)) {
                Out::show($this->nameForm . '_DaRemoto');
            }
        }
        if ($this->chiusuraFascicoli == 1) {
            Out::hide($this->nameForm . "_divButtonNew");
        }

        Out::setFocus('', $this->nameForm . '_Dal_num');
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        Out::html($this->nameForm . "_divInfo", "Sportelli On-line Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['SPORTELLO_DESC'] . "</span> Aggregati Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['AGGREGATO_DESC'] . "</span>");
    }

    private function CreaCombo() {
        Out::html($this->nameForm . "_Stato_proc", "");
        Out::html($this->nameForm . "_Stato_allegato", "");
        Out::html($this->nameForm . "_Stato_passo", "");
        Out::html($this->nameForm . "_Tipo", "");
        Out::html($this->nameForm . "_Tipo_seg", "");

        Out::select($this->nameForm . '_Stato_proc', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_proc', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_proc', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_Stato_passo', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato_passo', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato_passo', 1, "C", "0", "Chiusi");

        Out::select($this->nameForm . '_Stato_allegato', 1, "", "1", "");
        Out::select($this->nameForm . '_Stato_allegato', 1, "C", "", "Da controllare");
        Out::select($this->nameForm . '_Stato_allegato', 1, "V", "0", "Validi");
        Out::select($this->nameForm . '_Stato_allegato', 1, "N", "0", "Non validi");
        Out::select($this->nameForm . '_Stato_allegato', 1, "S", "0", "Sostituiti");
        Out::select($this->nameForm . '_Stato_allegato', 1, "NP", "0", "Non Presentato");

        Out::select($this->nameForm . '_Tipo', 1, "", "1", "");
        Out::select($this->nameForm . '_Tipo', 1, "F", "0", "Fabbricato");
        Out::select($this->nameForm . '_Tipo', 1, "T", "0", "Terreno");

        foreach (praLib::$TIPO_SEGNALAZIONE as $j => $z) {
            Out::select($this->nameForm . '_Tipo_seg', '1', $j, '0', $z);
        }
    }

    function AzzeraVariabili($pulisciRicerca = true) {
        if ($pulisciRicerca) {
            Out::clearFields($this->nameForm, $this->divRic);
        }
        TableView::disableEvents($this->gridGest);
        TableView::clearGrid($this->gridGest);
        Out::block($this->nameForm . "_divIntestatario");
        Out::block($this->nameForm . "_divUnitaLocale");
        $this->fascicoliSel = array();
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_NuovaPratica');
        Out::hide($this->nameForm . '_NuovaPraticaDaProt');
        Out::hide($this->nameForm . '_CaricaDaMail');
        Out::hide($this->nameForm . '_Controlla');
        Out::hide($this->nameForm . '_divButtonNew');
        Out::hide($this->nameForm . "_SvuotaRicerca");
        Out::hide($this->nameForm . "_NuovaPraticaDaProcedura");
        Out::hide($this->nameForm . '_Utilita');
        Out::hide($this->nameForm . '_DaRemoto');
    }

    function CreaSql() {
        if (isset($this->formData['fieldParam'])) {
            foreach ($this->formData['fieldParam'] as $campo => $value) {
                if ($value['filterMode'] == "empty") {
                    switch ($campo) {
                        case $this->nameForm . '_Sett':
                        case $this->nameForm . '_Atti':
                            $this->formData[$campo] = "0";
                            break;
                        default:
                            $this->formData[$campo] = "";
                            break;
                    }
                }
            }
        }

        $Stato_proc = $this->formData[$this->nameForm . '_Stato_proc'];
        $codiceserie = $this->formData[$this->nameForm . '_ric_codiceserie'];
        $fixedSeries = implode(",", $this->fixedSeries);
        $Dal_numserie = $this->formData[$this->nameForm . '_Dal_numserie'];
        $al_numserie = $this->formData[$this->nameForm . '_Al_numserie'];
        $annoserie = $this->formData[$this->nameForm . '_Annoserie'];
        $Dal_num = $this->formData[$this->nameForm . '_Dal_num'];
        $al_num = $this->formData[$this->nameForm . '_Al_num'];
        $anno = $this->formData[$this->nameForm . '_Anno'];
        $Da_rich = $this->formData[$this->nameForm . '_Da_richiesta'];
        $A_rich = $this->formData[$this->nameForm . '_A_richiesta'];
        $annoRich = $this->formData[$this->nameForm . '_Anno_rich'];
        $Da_data = $this->formData[$this->nameForm . '_Da_data'];
        $a_data = $this->formData[$this->nameForm . '_A_data'];
        $Da_data_sc = $this->formData[$this->nameForm . '_Da_data_sc'];
        $a_data_sc = $this->formData[$this->nameForm . '_A_data_sc'];
        $Da_data_ch = $this->formData[$this->nameForm . '_Da_datach'];
        $a_data_ch = $this->formData[$this->nameForm . '_A_datach'];
        $Intestatario = $this->formData[$this->nameForm . '_Intestatario'];
        $procedimento = $this->formData[$this->nameForm . '_Procedimento'];
        $Stato_passo = $this->formData[$this->nameForm . '_Stato_passo'];
        $Stato_allegato = $this->formData[$this->nameForm . '_Stato_allegato'];
        $passo = $this->formData[$this->nameForm . '_Passo'];
        $Responsabile = $this->formData[$this->nameForm . '_Responsabile'];
        $SettorePA = $this->formData[$this->nameForm . '_Settore'];      // settore pianta organica
        $ServizioPA = $this->formData[$this->nameForm . '_Servizio'];    // servizio pianta organica
        $Campo = $this->formData[$this->nameForm . '_Campo'];
        $NoteFascicolo = $this->formData[$this->nameForm . '_NoteFascicolo'];
        $aggregato = $this->formData[$this->nameForm . '_Aggregato'];
        $sportello = $this->formData[$this->nameForm . '_Sportello'];
        $tipologia = $this->formData[$this->nameForm . '_Tipologia'];
        $settore = $this->formData[$this->nameForm . '_Sett'];
        $attivita = $this->formData[$this->nameForm . '_Atti'];
        $articolo = $this->formData[$this->nameForm . '_Articolo'];
        $tipoArticolo = $this->formData[$this->nameForm . '_TipoArt'];
        $Tipo = $this->formData[$this->nameForm . '_Tipo'];
        $Sezione = $this->formData[$this->nameForm . '_Sezione'];
        $Foglio = $this->formData[$this->nameForm . '_Foglio'];
        $Sub = $this->formData[$this->nameForm . '_Sub'];
        $Note = $this->formData[$this->nameForm . '_Note'];
        $Codice = $this->formData[$this->nameForm . '_Codice'];
        $Particella = $this->formData[$this->nameForm . '_Particella'];
        $nomeCampo = $this->formData[$this->nameForm . '_NomeCampo'];
        $Ruolo = $this->formData[$this->nameForm . '_Ruolo'];
        $inMancanza = $this->formData[$this->nameForm . '_Mancanza'];
        $Nominativo = $this->formData[$this->nameForm . '_Nominativo'];
        $codFis = $this->formData[$this->nameForm . '_codFis'];
        $NumProt = $this->formData[$this->nameForm . '_NProt'];
        $AnnoProt = $this->formData[$this->nameForm . '_AnnoProt'];
        $NumDoc = $this->formData[$this->nameForm . '_Ndoc'];
        $DataDoc = $this->formData[$this->nameForm . '_DataDoc'];
        $CodAmm = $this->formData[$this->nameForm . '_CodiceAmm'];
        $CodAoo = $this->formData[$this->nameForm . '_CodiceAoo'];
        $Oggetto = $this->formData[$this->nameForm . '_Oggetto'];
        $TipologiaPasso = $this->formData[$this->nameForm . '_CodTipoPasso'];
        $maggGiorni = $this->formData[$this->nameForm . '_maggGiorni'];
        $Assegnatario = $this->formData[$this->nameForm . '_CodUtenteAss'];
        $tipologiaProg = $this->formData[$this->nameForm . '_TipologiaProg'];
        $progPasso = $this->formData[$this->nameForm . '_progPasso'];
        $annoProg = $this->formData[$this->nameForm . '_AnnoProg'];
        $validoDa = $this->formData[$this->nameForm . '_validoDa'];
        $validoAl = $this->formData[$this->nameForm . '_validoAl'];
        $via = $this->formData[$this->nameForm . '_Via'];
        $civico = $this->formData[$this->nameForm . '_Civico'];
        $evento = $this->formData[$this->nameForm . '_Evento'];
        $tipoSeg = $this->formData[$this->nameForm . '_Tipo_seg'];
        $CodiceProcedura = $this->formData[$this->nameForm . '_CodiceProcedura'];
        $StatoPasso = $this->formData[$this->nameForm . '_StatoPasso'];
        $DescrizionePasso = addslashes($this->formData[$this->nameForm . '_DescrizionePasso']);
        $AnnotazioniPasso = addslashes($this->formData[$this->nameForm . '_AnnotazioniPasso']);
        $Da_dataRic = $this->formData[$this->nameForm . '_Da_dataRic'];
        $a_dataRic = $this->formData[$this->nameForm . '_A_dataRic'];
        $nonAssegnati = $this->formData[$this->nameForm . '_NonAssegnati'];

        $D_gio = date('Ymd');
        if ($anno == '')
            if ($procedimento != '')
                $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);
        if ($passo != '')
            $passo = str_pad($passo, 6, 0, STR_PAD_RIGHT);
        if ($Dal_num == '')
            $Dal_num = "0";
        if ($al_num == '')
            $al_num = "999999";
        if ($Dal_num != '')
            $Dal_num = $anno . str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
        if ($al_num != '')
            $al_num = $anno . str_pad($al_num, 6, "0", STR_PAD_LEFT);
        if ($Da_rich != '') {
            $Da_rich = $annoRich . str_pad($Da_rich, 6, "0", STR_PAD_LEFT);
        } else {
            $Da_rich = "0";
        }
        if ($A_rich != '') {
            $A_rich = $annoRich . str_pad($A_rich, 6, "0", STR_PAD_LEFT);
        } else {
            $A_rich = "999999";
        }
        if ($Dal_numserie && $al_numserie == '') {
            $al_numserie = '99999999999';
        }
        if ($al_numserie && $Dal_numserie == '') {
            $Dal_numserie = "1";
        }


//Ricerca Immobili
        if ($Tipo)
            $join1 = " PRAIMM.TIPO =  '$Tipo'";
        if ($Sezione) {
            $join2 = ($join1) ? " AND" : "";
            $join2 .= " PRAIMM.SEZIONE =  '$Sezione'";
        }
        if ($Foglio) {
            $join3 = ($join2 || $join1) ? " AND" : "";
            $join3 .= " PRAIMM.FOGLIO =  '$Foglio'";
        }
        if ($Particella) {
            $join4 = ($join3 || $join2 || $join1) ? " AND" : "";
            $join4 .= " PRAIMM.PARTICELLA =  '$Particella'";
        }
        if ($Sub) {
            $join5 = ($join3 || $join2 || $join1 || $join4) ? " AND" : "";
            $join5 .= " PRAIMM.SUBALTERNO =  '$Sub'";
        }
        if ($Note) {
            $join6 = ($join3 || $join2 || $join1 || $join4 || $join5) ? " AND" : "";
            $join6 .= $this->PRAM_DB->strLower('PRAIMM.NOTE') . " LIKE  '%" . strtolower($Note) . "%'";
        }
        if ($Codice) {
            $join7 = ($join3 || $join2 || $join1 || $join4 || $join5 || $join6) ? " AND" : "";
            $join7 .= " PRAIMM.CODICE =  '$Codice'";
        }
        if ($join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7) {
            $joinImmobili = "INNER JOIN PRAIMM ON PROGES.GESNUM = PRAIMM.PRONUM AND " . $join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7;
        }

        if ($Campo || $nomeCampo) {
            $joinCampo = "INNER JOIN PRODAG PRODAG1 ON PROGES.GESNUM = PRODAG1.DAGNUM";
            if ($Campo) {
                $joinCampo .= " AND " . $this->PRAM_DB->strLower('PRODAG1.DAGVAL') . " LIKE '%" . addslashes(strtolower($Campo)) . "%'";
            }
            if ($nomeCampo) {
                $joinCampo .= " AND " . $this->PRAM_DB->strLower('PRODAG1.DAGKEY') . " LIKE '%" . addslashes(strtolower($nomeCampo)) . "%'";
            }
        }
        if ($Ruolo) {
            if ($via) {
                $whereVia = " AND " . $this->PRAM_DB->strLower('ANADES.DESIND') . " LIKE '%" . strtolower(addslashes($via)) . "%'";
            }
            if ($civico) {
                $whereCivico = " AND ANADES.DESCIV = '$civico'";
            }
            $joinRuolo = "ANADES.DESRUO = '$Ruolo' $whereVia $whereCivico";
        } else {
            if ($via) {
                $joinVia = "INNER JOIN ANADES ANADESVIA ON ANADESVIA.DESNUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('ANADESVIA.DESIND') . " LIKE '%" . strtolower(addslashes($via)) . "%'";
            }
            if ($civico) {
                $joinCivico = "INNER JOIN ANADES ANADESCIVICO ON ANADESCIVICO.DESNUM=PROGES.GESNUM AND ANADESCIVICO.DESCIV = '$civico'";
            }
        }
        if ($NumProt || $NumDoc || $DataDoc) {
            $joinProtocollo = "LEFT OUTER JOIN PRACOM PRACOM1 ON PRACOM1.COMNUM = PROGES.GESNUM";
        }
        if ($CodAmm || $CodAoo) {
            $joinCodAmmAoo = "LEFT OUTER JOIN PRACOM PRACOMCODAMM ON PRACOMCODAMM.COMNUM = PROGES.GESNUM";
        }
        if ($Nominativo) {
            $joinNom = ($joinRuolo) ? " AND " : "";
            $joinNom .= $this->PRAM_DB->strLower('ANADES.DESNOM') . " LIKE  '%" . addslashes(strtolower($Nominativo)) . "%'";
        }
        if ($codFis) {
            $joinFis = ($joinNom || $joinRuolo) ? " AND " : "";
            $joinFis .= $this->PRAM_DB->strLower('ANADES.DESFIS') . " LIKE  '%" . strtolower($codFis) . "%'";
        }
        if ($Intestatario != '') {
//$joinInt = ($joinNom || $joinRuolo || $joinFis) ? " AND" : "";
            $joinInt = " INNER JOIN ANADES ANADES1 ON ANADES1.DESNUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('ANADES.DESNOM') . " LIKE  '%" . strtolower(addslashes($Intestatario)) . "%' AND (ANADES.DESRUO = '0001' OR ANADES.DESRUO = '')";
        }

        if ($joinRuolo . $joinNom . $joinFis) {
            $joinSoggetti = "INNER JOIN ANADES ANADES1 ON PROGES.GESNUM = ANADES1.DESNUM AND " . $joinRuolo . $joinNom . $joinFis;
        }
        if ($inMancanza) {
            $countSogg = "(SELECT COUNT(*) FROM ANADES WHERE DESNUM=GESNUM AND DESRUO = '$Ruolo') AS COUNT_SOGG,";
            $whereCountSogg = " AND U.COUNT_SOGG = 0";
            $joinSoggetti = "";
        }


        if ($TipologiaPasso != '') {
            $praclt_rec = $this->praLib->GetPraclt($TipologiaPasso);
            $joinTipoPasso = " INNER JOIN PROPAS PROPAS1 ON PROPAS1.PRONUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('PROPAS1.PRODTP') . " LIKE  '%" . addslashes(strtolower($praclt_rec['CLTDES'])) . "%'";
        }

        if ($StatoPasso != '') {
            $joinStatoPasso = " INNER JOIN PROPAS PROPAS2 ON PROPAS2.PRONUM=PROGES.GESNUM AND PROPAS2.PROSTATO = '$StatoPasso'";
        }

        if ($SettorePA != '') {
            $joinSettorePA = " INNER JOIN PROPAS PROPAS3 ON PROPAS3.PRONUM=PROGES.GESNUM AND PROPAS3.PROSET = '$SettorePA'";
            if ($ServizioPA != '') {
                $joinSettorePA .= "  AND PROPAS3.PROSER = '$ServizioPA'";
            }
        }

        if ($DescrizionePasso != '') {
            $joinDescrizionePasso = " INNER JOIN PROPAS PROPAS4 ON PROPAS4.PRONUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('PROPAS4.PRODPA') . " LIKE '%" . strtolower($DescrizionePasso) . "%'";
        }
        if ($AnnotazioniPasso != '') {
            $joinAnnotazioniPasso = " INNER JOIN PROPAS PROPAS5 ON PROPAS5.PRONUM=PROGES.GESNUM AND PROPAS5.PROANN LIKE '%" . $AnnotazioniPasso . "%'";
        }
        if ($Assegnatario != '') {
            $select_assegnatario = ",(SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=P.GESNUM AND PROOPE<>'')) AS ULTRES";
            $where_assegnatario = " AND U.ULTRES = '$Assegnatario'";
        }

        $joinProgressivo = '';
        $joinProgressivoBase = '';
        $whereJoinProgressivo = array();

        if ($progPasso || $validoDa || $validoAl || $tipologiaProg || $annoProg) {
            $joinProgressivoBase = " INNER JOIN PROPAS PROPASPROG ON  PROPASPROG.PRONUM=PROGES.GESNUM AND ";
        }

        if ($tipologiaProg) {
            $whereJoinProgressivo[] = "PROPASPROG.PRODOCTIPREG = '$tipologiaProg'";
        }
        if ($progPasso) {
            $whereJoinProgressivo[] = "PROPASPROG.PRODOCPROG = '$progPasso'";
        }
        if ($annoProg) {
            $whereJoinProgressivo[] = "PROPASPROG.PRODOCANNO = '$annoProg'";
        }

        if ($validoDa) {
            if ($validoAl == "") {
                $validoAl = "99999999";
                //$whereJoinProgressivo[] = " PROPASPROG.PRODOCINIVAL >= '$validoDa'";
            }
        }
        if ($validoAl) {
            if ($validoDa == "") {
                $validoDa = "00000000";
                //$whereJoinProgressivo[] = " PROPASPROG.PRODOCFINVAL <= '$validoAl' ";
            }
        }

        if ($validoAl && $validoDa) {
            $whereJoinProgressivo[] = "(PROPASPROG.PRODOCINIVAL <= '$validoDa' AND PROPASPROG.PRODOCFINVAL >= '$validoAl') AND PROPASPROG.PRODOCPROG <> ''";
        }

        if ($joinProgressivoBase && count($whereJoinProgressivo)) {
            $joinProgressivo = $joinProgressivoBase . implode(' AND ', $whereJoinProgressivo);
        }

        $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
        $oggi = date('Ymd');
        $sql = "SELECT 
                    *
                FROM(
                    SELECT
                        *
                        $select_assegnatario
                    FROM(
                        SELECT
                            DISTINCT PROGES.ROWID AS ROWID,
                            PROGES.GESNUM AS GESNUM," .
                $this->PRAM_DB->strConcat("SERIEANNO", "LPAD(SERIEPROGRESSIVO, 6, '0')") . " AS ORDER_GESNUM,
                            PROGES.SERIEANNO AS SERIEANNO,
                            PROGES.SERIEPROGRESSIVO AS SERIEPROGRESSIVO,
                            PROGES.SERIECODICE AS SERIECODICE,
                            PROGES.GESKEY AS GESKEY,
                            PROGES.GESDRE AS GESDRE,
                            PROGES.GESDRE AS ORDER_GESDRE,
                            PROGES.GESDRI AS GESDRI," .
                $this->PRAM_DB->dateDiff(
                        $this->PRAM_DB->coalesce(
                                $this->PRAM_DB->nullIf("GESDCH", "''"), "'$oggi'"
                        ), 'GESDRI'
                ) . " AS NUMEROGIORNI,
                            PROGES.GESORA AS GESORA,
                            PROGES.GESDCH AS GESDCH,
                            PROGES.GESCODPROC AS GESCODPROC,
                            PROGES.GESPRA AS GESPRA,
                            PROGES.GESPRA AS ORDER_GESPRA,
                            PROGES.GESTSP AS GESTSP,
                            PROGES.GESSPA AS GESSPA," .
                $this->PRAM_DB->strConcat('GESTSP', "'/'", 'GESSPA') . " AS TSP_SPA,
                            (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=GESNUM AND PROOPE<>'') AS N_ASSEGNAZIONI,
                            (SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=GESNUM AND PROOPE<>'')) AS ULT_RESP,
                            (SELECT PRORPA FROM PROPAS WHERE PRONUM=GESNUM AND PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROOPE='' GROUP BY PRORPA) AS FL_PRORPA,
                            $countSogg
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
                        FROM PROGES PROGES
                            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM
                            $joinImmobili
                            $joinCampo
                            $joinInt
                            $joinSoggetti
                            $joinProtocollo
                            $joinTipoPasso
                            $joinStatoPasso
                            $joinDescrizionePasso
                            $joinAnnotazioniPasso
                            $joinSettorePA
                            $joinProgressivo
                            $joinValidoDa
                            $joinVia
                            $joinCivico
                            $joinCodAmmAoo
                        WHERE 
                            (GESNUM BETWEEN '$Dal_num' AND '$al_num') ";


        if ($Da_data && $a_data) {
            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
        }
        if ($Da_data_sc && $a_data_sc) {
            $sql .= " AND (GESDSC BETWEEN '$Da_data_sc' AND '$a_data_sc')";
        }
        if ($Da_data_ch && $a_data_ch) {
            $sql .= " AND (GESDCH BETWEEN '$Da_data_ch' AND '$a_data_ch')";
        }
        if ($Da_dataRic && $a_dataRic) {
            $sql .= " AND (GESDRI BETWEEN '$Da_dataRic' AND '$a_dataRic')";
        }
        if ($Dal_numserie && $al_numserie) {
            $sql .= " AND (SERIEPROGRESSIVO BETWEEN '$Dal_numserie' AND '$al_numserie')";
        }
        /*
         * 
         * Novita serie
         * 
         */
        if ($codiceserie) {
            $sqlSerie = " AND SERIECODICE = $codiceserie";
        } else if ($fixedSeries) {
            $sqlSerie = " AND SERIECODICE IN ($fixedSeries)";
        }
        $sql .= $sqlSerie;

        if ($annoserie) {
            $sql .= " AND SERIEANNO = $annoserie";
        }
        if ($CodiceProcedura) {
            $sql .= " AND GESCODPROC LIKE '%$CodiceProcedura%'";
        }
        if ($CodicePro)
            if ($Da_rich && $A_rich) {
                $sql .= " AND (GESPRA BETWEEN '$Da_rich' AND '$A_rich')";
            }
        if ($Stato_proc == 'C') {
            $sql .= " AND GESDCH <> ''";
        } else if ($Stato_proc == 'A') {
            $sql .= " AND GESDCH = ''";
        }
        if ($Stato_passo == 'A') {
            $sql .= " AND PROPAS.PROINI <> '' AND PROPAS.PROFIN = ''";
        } else if ($Stato_passo == 'C') {
            $sql .= "PROPAS.PROFIN <> ''";
        }
        if ($Responsabile != '') {
            $sql .= " AND (PROGES.GESRES = '$Responsabile')";
        }

        if ($aggregato) {
            $sql .= " AND GESSPA = " . $aggregato;
        }
        if ($sportello) {
            $sql .= " AND GESTSP = " . $sportello;
        }
        if ($tipologia) {
            $sql .= " AND GESTIP = " . $tipologia;
        }
        if ($settore != "") {
            $sql .= " AND GESSTT = " . $settore;
        }
        if ($attivita != "") {
            $sql .= " AND GESATT = " . $attivita;
        }
        if ($NoteFascicolo) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('GESNOT') . " LIKE '%" . strtoupper(addslashes($NoteFascicolo)) . "%'";
        }
        if ($Oggetto) {
            $sql .= " AND " . $this->PRAM_DB->strLower('GESOGG') . " LIKE '%" . addslashes(strtolower($Oggetto)) . "%'";
        }
        if ($procedimento) {
            $sql .= " AND GESPRO = '$procedimento'";
        }
        if ($evento) {
            $sql .= " AND GESEVE = '$evento'";
        }
        if ($tipoSeg) {
            $sql .= " AND GESSEG = '$tipoSeg'";
        }

        if ($Stato_allegato != '') {
            $sql .= " AND GESNUM IN (SELECT " . $this->PRAM_DB->subString('PASKEY', 1, 10) . " FROM PASDOC WHERE PASDOC.PASSTA = '$Stato_allegato')";
        }
        if ($articolo != '') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPAS.PROPCONT LIKE '%" . addslashes($articolo) . "%')";
        }
        if ($tipoArticolo == 'T') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPAS.PROPART = 1)";
        }
        if ($tipoArticolo == 'I') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPDADATA<>'' AND PROPADDATA<>'' AND PROPDADATA <= $D_gio AND PROPADDATA >= $D_gio)";
        }
        if ($tipoArticolo == 'S') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPADDATA <> '' AND PROPADDATA < $D_gio)";
        }

        if ($nonAssegnati == 1) {
            $sql .= " AND GESNUM NOT IN (SELECT PRONUM FROM PROPAS WHERE PROPAS.PROOPE <> '')";
        }
        if ($NumProt) {
            if ($AnnoProt == "") {
                $sql .= " AND (SUBSTR(COMPRT,5) = '$NumProt' OR SUBSTR(PROGES.GESNPR,5) = '$NumProt' OR
                               SUBSTR(COMPRT,5) = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "' OR
                               SUBSTR(COMPRT,5) = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
            } else {
                $sql .= " AND (PRACOM1.COMPRT = '$AnnoProt$NumProt' OR PROGES.GESNPR = '$AnnoProt$NumProt' OR
                               PRACOM1.COMPRT='$AnnoProt" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "' OR 
                               PROGES.GESNPR='$AnnoProt" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
            }
        }

        if ($CodAmm) {
            $sql .= " AND (PROGES.GESAMMPR = '$CodAmm' OR PRACOMCODAMM.COMAMMPR = '$CodAmm')";
        }
        if ($CodAoo) {
            $sql .= " AND (PROGES.GESAOOPR = '$CodAoo' OR PRACOMCODAMM.COMAOOPR = '$CodAoo')";
        }

        if ($NumDoc) {
            $sql .= " AND PRACOM1.COMIDDOC = '$NumDoc' ";
        }
        if ($DataDoc) {
            $sql .= " AND PRACOM1.COMDATADOC = '$DataDoc'";
        }

        $sql .= " GROUP BY GESNUM"; // Per non far vedere pratiche doppie a colpa della join con ANADES
        $sql .= ") P ";
        if ($maggGiorni) {
            $sql .= " WHERE P.NUMEROGIORNI > $maggGiorni";
        }

        $sql .= ") U WHERE 1=1 ";
        if ($where_assegnatario) {
            $sql .= " $where_assegnatario";
        }
        if ($whereCountSogg) {
            $sql .= " $whereCountSogg";
        }
//
        $where_finale = array();
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        $sql_visibilita_arr = array();

        /*
         * 
         * Vecchio
         * 
         */
//        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//            $sql_visibilita_arr[] = " (U.GESTSP = " . $retVisibilta['SPORTELLO'] . " OR U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
//        }

        /*
         * 
         * Nuovo
         * 
         * 
         */
        if (count($retVisibilta['SPORTELLI']) != 0) {
            $arrSportelliAggregati = array();
            foreach ($retVisibilta['SPORTELLI'] as $key => $filtroSportello) {
                if (strpos($filtroSportello, '/') !== false) {
                    $arrSportelliAggregati[] = "'$filtroSportello'";
                    unset($retVisibilta['SPORTELLI'][$key]);
                }
            }
            $sqlArray = array();
            if (count($retVisibilta['SPORTELLI'])) {
                $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
                $sqlFiltroSportelli = "U.GESTSP IN ($strSportelli)";
                $sqlArray[] = $sqlFiltroSportelli;
            }
            if (count($arrSportelliAggregati)) {
                $strSportelliAggregati = implode(",", $arrSportelliAggregati);
                $sqlFiltroAggregati = "U.TSP_SPA IN ($strSportelliAggregati) ";
                $sqlArray[] = $sqlFiltroAggregati;
            }
            $sqlFiltro_tsp_spa = implode(" OR ", $sqlArray);
            $sql_visibilita_arr[] = " ($sqlFiltro_tsp_spa OR U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
        }

        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
            $sql_visibilita_arr[] = " (U.GESSPA = " . $retVisibilta['AGGREGATO'] . " OR U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}')";
        }
        if (count($sql_visibilita_arr) == 0) {
            $sql_visibilita_arr[] = " 1 = 1 ";
        }
        if (count($sql_visibilita_arr)) {
            $where_finale[] = "(" . implode(" AND ", $sql_visibilita_arr) . ")";
        }






        $codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
        if ($this->flagAssegnazioni && $Utenti_rec['UTEANA__3'] && $codRespAss != $Utenti_rec['UTEANA__3']) {
            $where_finale[] = "
                        ((U.GESRES = '{$Utenti_rec['UTEANA__3']}' OR
                        U.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}' OR
                        U.ULT_RESP = '{$Utenti_rec['UTEANA__3']}')
                        )";
        }

        if (count($where_finale)) {
            $sql .= " AND (" . implode(' OR ', $where_finale) . ")";
        }
        return $sql;
    }

    private function DecodAnaeventi($Codice, $tipoRic = 'codice', $retid = "", $updsegnalazione = false) {
        $anaeventi_rec = $this->praLib->GetAnaeventi($Codice, $tipoRic);
        switch ($retid) {
            case "RIC":
                Out::valore($this->nameForm . "_Evento", '');
                Out::valore($this->nameForm . "_Desc_evento", '');
                if ($anaeventi_rec) {
                    Out::valore($this->nameForm . "_Evento", $anaeventi_rec['EVTCOD']);
                    Out::valore($this->nameForm . "_Desc_evento", $anaeventi_rec['EVTDESCR']);
                }
                break;
        }
    }

    private function DecodAnapra($Codice, $retid, $tipoRic = 'codice', $dataRegistrazione = '', $idEvento = "") {
        $anapra_rec = $this->praLib->GetAnapra($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Procedimento":
                Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc', $anapra_rec['PRADES__1']);
                break;
            case "" :
                break;
        }
        return $anapra_rec;
    }

    private function DecodPraclt($Codice, $tipoRic = 'codice') {
        $praclt_rec = $this->praLib->GetPraclt($Codice, $tipoRic);
        Out::valore($this->nameForm . '_CodTipoPasso', $praclt_rec['CLTCOD']);
        Out::valore($this->nameForm . '_TipoPasso', $praclt_rec['CLTDES']);
        return $praclt_rec;
    }

    private function DecodAnaruo($Codice, $tipoRic = 'codice') {
        $anaruo_rec = $this->praLib->GetAnaruo($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Ruolo", $anaruo_rec['RUOCOD']);
        Out::valore($this->nameForm . "_DescRuolo", $anaruo_rec['RUODES']);
        return $anaruo_rec;
    }

    private function DecodAnatip($Codice, $tipoRic = 'codice') {
        $anatip_rec = $this->praLib->GetAnatip($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Tipologia", $anatip_rec['TIPCOD']);
        Out::valore($this->nameForm . "_Desc_tipo", $anatip_rec['TIPDES']);
        return $anatip_rec;
    }

    private function DecodAnaset($Codice, $tipoRic = 'codice') {
        $anaset_rec = $this->praLib->GetAnaset($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Sett", $anaset_rec['SETCOD']);
        Out::valore($this->nameForm . "_Desc_sett", $anaset_rec['SETDES']);
        return $anaset_rec;
    }

    private function DecodAnaatt($Codice, $tipoRic = 'codice') {
        $anaatt_rec = $this->praLib->GetAnaatt($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Atti", $anaatt_rec['ATTCOD']);
        Out::valore($this->nameForm . "_Desc_atti", $anaatt_rec['ATTDES']);
        return $anaatt_rec;
    }

    private function DecodAnades($Codice, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Nominativo", $anades_rec['DESNOM']);
    }

    private function DecodAnaspa1($Codice, $tipoRic = 'codice') {
        $anaspa_rec = $this->praLib->GetAnaspa($Codice, $tipoRic);
        Out::valore($this->nameForm . '_Aggregato', $anaspa_rec['SPACOD']);
        Out::valore($this->nameForm . '_Desc_aggr', $anaspa_rec['SPADES']);
        return $anaspa_rec;
    }

    private function DecodAnatsp2($Codice, $tipoRic = 'codice') {
        $anatsp_rec = $this->praLib->GetAnatsp($Codice, $tipoRic);
        Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_Desc_spor', $anatsp_rec['TSPDES']);
        return $anatsp_rec;
    }

    private function DecodAnanom($Codice, $retid, $tipoRic = 'codice') {
        $ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_Responsabile":
                Out::valore($this->nameForm . '_Responsabile', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_Desc_resp', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case $this->nameForm . "_ricercaAss":
                Out::valore($this->nameForm . '_CodUtenteAss', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_UtenteAss', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case "" :
                break;
        }
        return $ananom_rec;
    }

    private function DecodSettore($retKey, $retid, $tipoRic = 'codice') {
        $Anauni_set = $this->praLib->getAnauni($retKey, $tipoRic);
        if ($Anauni_set) {
            Out::valore($this->nameForm . '_Settore', $Anauni_set['UNISET']);
            Out::valore($this->nameForm . '_Desc_settore', $Anauni_set['UNIDES']);
            Out::valore($this->nameForm . '_Servizio', '');
            Out::valore($this->nameForm . '_Desc_servizio', '');
        }
    }

    private function DecodServizio($retKey, $retid, $tipoRic = 'codice') {
        $Anauni_ser = $this->praLib->getAnauni($retKey, $tipoRic);
        if ($Anauni_ser) {
            Out::valore($this->nameForm . '_Servizio', $Anauni_ser['UNISER']);
            Out::valore($this->nameForm . '_Desc_servizio', $Anauni_ser['UNIDES']);
        }
    }

    function GetStatoAllegati($passta) {
        switch ($passta) {
            case "":
                $stato = "";
                break;
            case "C":
                $stato = "Da controllare";
                break;
            case "V":
                $stato = "Valido";
                break;
            case "N":
                $stato = "Non Valido";
                break;
            case "S":
                $stato = "Sostituito";
                break;
        }
        return $stato;
    }

    function elaboraRecords($Result_tab, $escludiChiusi = false) {
        $presenteGesKey = false;
        foreach ($Result_tab as $key => $Result_rec) {
            if ($escludiChiusi == true) {
                if ($Result_rec['GESDCH']) {
                    unset($Result_tab[$key]);
                    continue;
                }
            }
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
                $gesnpr = "<div style=\"color:DodgerBlue;\"><b>" . intval(substr($Result_rec['GESNPR'], 4)) . "/" . substr($Result_rec['GESNPR'], 0, 4) . "</b></div>";
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

            //$Result_tab[$key]['NOTE'] = $this->getHtmlNote($Result_rec['GESNOT'], $Result_rec['GESNUM']); //"<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\">" . $Result_rec['GESNOT'] . "</div>";
            $Result_tab[$key]['NOTE'] = $this->getHtmlNoteProgress($Result_rec['GESNOT'], $Result_rec['GESNUM']);

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

            $opacity = "";
            if (!$Result_rec['GESDCH']) {
                $opacity1 = (($Result_tab[$key]["NUMEROGIORNI"] <= 60) ? $Result_tab[$key]["NUMEROGIORNI"] * (100 / 60) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
            $Result_tab[$key]["GIORNI"] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $Result_tab[$key]["NUMEROGIORNI"] . '</span></div>';


            if ($Result_rec['PRIORITA_RICH'] == '99') {
                $Result_tab[$key]['PRIORITA_RICH'] = '';
            }

            $Result_tab[$key]['STATO'] = $this->praLib->GetImgStatoPratica($Result_rec);

            $proges_rec_ant = $this->praLib->GetProges($Result_rec['GESNUM'], "antecedente");
            if ($Result_rec['GESPRE'] || $proges_rec_ant) {
                $Result_tab[$key]['ANTECEDENTE'] = '<span class="ui-icon ui-icon-folder-open"></span>';
            }

            $Result_tab[$key]['STATOALL'] = $this->ControllaAllegatiStat($Result_rec['GESNUM']);

            /*
             * valorizzo impresa e codice fiscale sulla tabella
             */
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab[$key]['IMPRESA'] = "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";
        }

        if ($presenteGesKey) {
            //Out::msgInfo("GridGest", print_r($this->gridGest, true));
            //TableView::setColumnProperty($this->gridGest, "GESNUM", "width", "140");
        }

        return $Result_tab;
    }

    function getHtmlNoteProgress($note, $gesnum) {
        $sql = $this->praLib->CreaSqlCaricaPassi($gesnum);
        $Propas_tab = $this->praLib->getGenericTab($sql);
        $passi_BO = 0;
        $arrPassi = array();
        foreach ($Propas_tab as $Propas_rec) {
            if ($Propas_rec['PROKPRE']) {
                continue;
            }
            if ($Propas_rec['PROPUB'] == 0 && $Propas_rec['PRODPA'] != "Collegamento al Commercio") {
                $passi_BO++;
                if ($Propas_rec['PROFIN']) {
                    $arrPassi[$passi_BO]['SEQ'] = $Propas_rec['PROSEQ'];
                    $arrPassi[$passi_BO]['STATO'] = "C";
                } else if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'] == "") {
                    $arrPassi[$passi_BO]['SEQ'] = $Propas_rec['PROSEQ'];
                    $arrPassi[$passi_BO]['STATO'] = "A";
                } elseif ($Propas_rec['PROINI'] == "" && $Propas_rec['PROFIN'] == "") {
                    $arrPassi[$passi_BO]['SEQ'] = $Propas_rec['PROSEQ'];
                    $arrPassi[$passi_BO]['STATO'] = "I";
                }
            }
        }

        $arrayPassiDef = array();
        $i = 0;
        foreach ($arrPassi as $key => $passo) {
            if (!$arrayPassiDef) {
                $arrayPassiDef[$i][] = $passo;
            } else {
                if ($arrayPassiDef[$i][0]['STATO'] == $passo['STATO']) {
                    $arrayPassiDef[$i][] = $passo;
                } else {
                    $i++;
                    $arrayPassiDef[$i][] = $passo;
                }
            }
        }

        $html = "<div style=\"height:55px;overflow:auto;margin-bottom:3px;\" class=\"ita-Wordwrap\">$note</div>";
        if ($passi_BO != 0) {
            $title = htmlspecialchars("PASSI TOTALI: $passi_BO", ENT_COMPAT);
            $html .= "<div class=\"ita-html\">";
            $html .= "<div title=\"$title\" style=\"border:1px solid darkgrey;height:15px;overflow:auto;margin-bottom:3px;\" class=\"ita-tooltip\">";
            foreach ($arrayPassiDef as $passi) {
                if ($passi[0]['STATO'] == "A")
                    $color = "red";
                if ($passi[0]['STATO'] == "C")
                    $color = "lightgreen";
                if ($passi[0]['STATO'] == "I")
                    $color = "lightgrey";
                //$percentuale_passi = number_format((count($passi) / count($arrayPassiDef)) * 100, 2);
                $percentuale_passi = number_format((count($passi) / $passi_BO) * 100, 2);
                //
                if ($percentuale_passi > 0) {
                    $html .= "<div style=\"width:$percentuale_passi%; display: inline-block; background-color: $color; text-align: center; color: black; line-height: 15px;\">" . count($passi) . "</div>";
                }
            }
            $html .= "</div></div>";
        }
        return $html;
    }

    private function ControllaAllegatiStat($Gesnum) {
        $pasdoc_tab = array();
        $Propas_sql = $this->praLib->CreaSqlCaricaPassi($Gesnum, true);
        $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $Propas_sql);
        foreach ($Propas_tab as $Propas_rec) {
            $Allegati_passo = $this->praLib->GetPasdoc($Propas_rec['PROPAK'], "codice", true);
            if ($Allegati_passo) {
                $pasdoc_tab = array_merge($pasdoc_tab, $Allegati_passo);
            }
        }
        if ($pasdoc_tab) {
            $non_valido = false;
            $validi = array();
            foreach ($pasdoc_tab as $pasdoc_rec) {
                if ($pasdoc_rec['PASSTA'] == "N") {
                    $non_valido = true;
                } elseif ($pasdoc_rec['PASSTA'] == "V" || $pasdoc_rec['PASSTA'] == "S") {
                    $validi[] = $pasdoc_rec;
                }
            }
            if ($non_valido === true) {
                $Stato = "<span class=\"ita-icon ita-icon-check-red-24x24\">Ci sono allegati non validi</span>";
            } else {
                $Stato = "<span class=\"ita-icon ita-icon-check-grey-24x24\">Ci sono allegati da controllare</span>";
            }
            if ($validi == $pasdoc_tab) {
                $Stato = "<span class=\"ita-icon ita-icon-check-green-24x24\">Tutti gli allegati sono stati validati</span>";
            }
        }

        return $Stato;
    }

    public function Dettaglio($Indice, $tipo = 'rowid') {
        $proges_rec = $this->praLib->GetProges($Indice, $tipo);
        if (!$proges_rec) {
            $this->OpenRicerca();
            Out::msgStop("Attenzione", "Record PROGES con rowid: $Indice non più disponibile.");
            return false;
        }

        if ($proges_rec['GESWFPRO']) {
            $model = 'praGest2';
        } else {
            $model = 'praGest';
        }

        $_POST = array();
        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
        itaLib::openForm($model);
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent('openform');
        if ($this->rowidFiera) {
            $objModel->setRowidFiera($this->rowidFiera);
        }

        $objModel->parseEvent();
        return true;
    }

    function CaricaDaPec() {
        if ($_POST["daPortlet"] == "true") {
            $this->inizializzaForm();
        }

        /*
         * Mi prendo l'array dati della richiesta
         */
        $praLibRichiesta = praLibRichiesta::getInstance();
        $arrDati = $praLibRichiesta->getDatiRichiesta($_POST['datiMail']);

        /*
         * Assegno i dati della Mail se provengo dal CaricaDaMail
         */
        $arrDati['FILENAME'] = $_POST['datiMail']['FILENAME'];
        $arrDati['IDMAIL'] = $_POST['datiMail']['IDMAIL'];


        /*
         * Chiamo una funzione generica di aggiunta fascicolo
         */
        $praLibPratica = praLibPratica::getInstance();
        $retAcq = $praLibPratica->acquisizioneRichiesta($arrDati, $this);
        if ($retAcq['Status'] == "-1") {
            $this->OpenRicerca();
            Out::msgStop("Inserimento da PEC", "Inserimento fallito: " . $retAcq['Message']);
            return false;
        }

        /*
         * Mantenere
         */
        if ($retAcq['PROPAK']) {
            $Propas_rec = $this->praLib->GetPropas($retAcq['PROPAK'], "propak");
            if ($Propas_rec == false) {
                Out::msgStop("Errore inserimento passo integrazione", $this->praLib->getErrMessage());
                return false;
            }
            $model = 'praPasso';
            $_POST = array();
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            $_POST['perms'] = $this->perms;
            $_POST[$model . '_returnModel'] = $this->nameForm;
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent("openform");
            $objModel->parseEvent();
        } else {
            $proges_rec_new = $this->praLib->GetProges($retAcq['GESNUM']);
            if (!$this->Dettaglio($proges_rec_new['ROWID'])) {
                $this->OpenRicerca();
                return false;
            }
        }

        Out::msgInfo("Acquisizione:", $retAcq['ExtendedMessageHtml']);

        return true;
    }

    public function GetArrayAzioni() {
        if (!$this->searchMode) {
            $arrayAzioni = array(
                'Stampa' => array('id' => $this->nameForm . '_Stampa', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-print-32x32'", 'model' => $this->nameForm),
                'Estrazione' => array('id' => $this->nameForm . '_Estrazione', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-spreadsheet-32x32'", 'model' => $this->nameForm),
                'Chiudi Fascicoli' => array('id' => $this->nameForm . '_ChiudiFascicoli', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-chiusagray-32x32'", 'model' => $this->nameForm),
            );
        }
        if ($this->chiusuraFascicoli) {
            $arrayAzioni = array(
                'Stampa' => array('id' => $this->nameForm . '_Stampa', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-print-32x32'", 'model' => $this->nameForm),
                'Estrazione' => array('id' => $this->nameForm . '_Estrazione', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-spreadsheet-32x32'", 'model' => $this->nameForm),
                'Chiudi Fascicoli' => array('id' => $this->nameForm . '_ChiudiFascicoli', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-chiusagray-32x32'", 'model' => $this->nameForm),
            );
        } else {
            $arrayAzioni = array(
                'Stampa' => array('id' => $this->nameForm . '_Stampa', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-print-32x32'", 'model' => $this->nameForm),
                'Estrazione' => array('id' => $this->nameForm . '_Estrazione', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-spreadsheet-32x32'", 'model' => $this->nameForm),
            );
        }
        return $arrayAzioni;
    }

    private function inizializzaForm() {
        Out::hide($this->nameForm . "_NonAssegnati_field");
        $this->CreaCombo();
    }

    public function printXlsxFromModel($model) {
        $this->praLibExportXls->StampaXls($model, $this->CreaSql());
    }

}
