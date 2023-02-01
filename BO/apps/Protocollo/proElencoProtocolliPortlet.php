<?php

/**
 *
 * Elenco Protocolli su Portlet
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    26.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php';

function proElencoProtocolliPortlet() {
    $proElencoProtocolliPortlet = new proElencoProtocolliPortlet();
    $proElencoProtocolliPortlet->parseEvent();
    return;
}

class proElencoProtocolliPortlet extends itaModel {

    public $PROT_DB;
    public $ITW_DB;
    public $ITALWEB_DB;
    public $nameForm = "proElencoProtocolliPortlet";
    public $divRis = "proElencoProtocolliPortlet_divRisultato";
    public $gridProtocolli = "proElencoProtocolliPortlet_gridProtocolli";
    public $proLib;
    public $segLib;
    public $proLibMail;
    public $accLib;
    public $itaDate;
    public $utente;
    public $tabella;
    public $proLibAllegati;
    public $proLibFascicolo;
    public $codiceDest;
    public $DataSuperioreFiltro;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->accLib = new accLib();
            $this->itaDate = new itaDate();
            $this->proLibMail = new proLibMail();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITW_DB = $this->accLib->getITW();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->utente = App::$utente->getKey($this->nameForm . '_utente');
            $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->segLib = new segLib();
            $this->codiceDest = App::$utente->getKey($this->nameForm . '_codiceDest');
            $this->DataSuperioreFiltro = App::$utente->getKey($this->nameForm . '_DataSuperioreFiltro');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_utente', $this->utente);
            App::$utente->setKey($this->nameForm . '_codiceDest', $this->codiceDest);
            App::$utente->setKey($this->nameForm . '_tabella', $this->tabella);
            App::$utente->setKey($this->nameForm . '_DataSuperioreFiltro', $this->DataSuperioreFiltro);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm('proElencoProtocolliPortlet', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");

            case 'openportletapp':
                Out::attributo($this->nameForm . "_FlagTutti", "checked", "0", "checked");
                Out::attributo($this->nameForm . "_FlagModelli", "checked", "1", "checked");
//                Out::hide($this->nameForm . '_FlagNotifica_field');
                $profilo = proSoggetto::getProfileFromIdUtente();
                if ($profilo['PROT_ABILITATI'] == '2' || $profilo['PROT_ABILITATI'] == '3') {
                    Out::hide($this->nameForm . '_FlagNonAssegnati_field');
                }
                $this->utente = App::$utente->getKey('nomeUtente');
                $this->codiceDest = proSoggetto::getCodiceSoggettoFromIdUtente();
                if (!$this->codiceDest) {
                    Out::html($this->divElenco, "<br><br><h1>CODICE DESTINATARIO NON CONFIGUARTO CONTATTATRE L'AMMINISTRATRE DEL SISTEMA</h1><br><br>");
                    break;
                }
                // Check Ufficio Valido:
                $Ctrruoli = proSoggetto::getRuoliFromCodiceSoggetto($this->codiceDest);
                if (!$Ctrruoli) {
                    Out::html($this->divElenco, "<br><br><h1>CODICE DESTINATARIO NON CONFIGUARTO CONTATTATRE L'AMMINISTRATRE DEL SISTEMA</h1><br><br>");
                    break;
                }

                $this->CreaCombo();
                Out::codice("itaGo('ItaForm',$('#" . $this->nameForm . "_selectUffici'),{asyncCall:false,bloccaui:true,event:'onChange'});"); //,context:'$this->id',model:'$this->model'});");                
                // Controllo Documenti Predisposti alla Firma:
                $anaent_55 = $this->proLib->GetAnaent('55');
                if (!$anaent_55['ENTDE2']) {
                    Out::hide($this->nameForm . '_divDocPredisposti');
                }


                break;
            case 'editGridRow':
            case 'dbClickRow':
                if ($_POST['rowid'] != '0') {
                    $indice = $_POST['rowid'];
                    $Anaproctr_rec = $this->proLib->GetAnapro($indice, 'rowid');
                    if ($Anaproctr_rec['PROTEMPLATE'] == 1) {
                        Out::msgQuestion("Protocollo.", "Seleziona l'operazione da eseguire per il <b>modello</b>:", array(
                            'Apri il protocollo' => array('id' => $this->nameForm . '_ApriProtocollo', 'model' => $this->nameForm),
                            'Usa per nuovo protocollo' => array('id' => $this->nameForm . '_CreaNuovoDaModello', 'model' => $this->nameForm)
                                )
                        );
                    } else {
                        $this->ApriProtocollo($indice);
                    }
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridProtocolli:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        $utente = App::$utente->getKey('nomeUtente');
                        $sql = $this->creaSql();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proElencoProtocolliPortlet', $parameters);
                        break;
                }
                break;
            case 'addGridRow':
                $profilo = proSoggetto::getProfileFromIdUtente();
                $ArrBottoni = array();
                if ($profilo['PROT_ABILITATI'] == '') {
                    $ArrBottoni = array(
                        'F6-Documento Formale' => array('id' => $this->nameForm . '_Formali', 'model' => $this->nameForm, 'shortCut' => "f6"),
                        'F8-Partenza' => array('id' => $this->nameForm . '_Partenza', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Arrivo' => array('id' => $this->nameForm . '_Arrivo', 'model' => $this->nameForm, 'shortCut' => "f5"));
                } else if ($profilo['PROT_ABILITATI'] == '1') {
                    $ArrBottoni = array(
                        'F6-Documento Formale' => array('id' => $this->nameForm . '_Formali', 'model' => $this->nameForm, 'shortCut' => "f6"),
                        'F5-Arrivo' => array('id' => $this->nameForm . '_Arrivo', 'model' => $this->nameForm, 'shortCut' => "f5"));
                } else if ($profilo['PROT_ABILITATI'] == '2') {
                    $ArrBottoni = array(
                        'F6-Documento Formale' => array('id' => $this->nameForm . '_Formali', 'model' => $this->nameForm, 'shortCut' => "f6"),
                        'F8-Partenza' => array('id' => $this->nameForm . '_Partenza', 'model' => $this->nameForm, 'shortCut' => "f8"));
                } else if ($profilo['PROT_ABILITATI'] == '3') {
                    $this->nuovoProtocollo('C');
                    break;
                }
                /*
                 * Controllo se attivare doc alla firma
                 */
                $anaent_55 = $this->proLib->GetAnaent('55');
                if ($anaent_55['ENTDE2']) {
                    $ArrBottoni['Documento Alla Firma'] = array('id' => $this->nameForm . '_DocumentoAllaFirma',
                        'model' => $this->nameForm);
                }
                Out::msgQuestion("Protocollo.", "Seleziona il Tipo di Protocollo:", $ArrBottoni);
                break;
            case 'onClickTablePager':
                $this->caricaDatiGriglia();
                $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella, '2');
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Arrivo':
                        $this->nuovoProtocollo('A');
                        break;
                    case $this->nameForm . '_Partenza':
                        $this->nuovoProtocollo('P');
                        break;
                    case $this->nameForm . '_Formali':
                        $this->nuovoProtocollo('C');
                        break;
                    case $this->nameForm . '_DocumentoAllaFirma':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAtto();
                        break;
                    case $this->nameForm . '_btnCerca':
                        $model = 'proGest';
                        itaLib::openForm($model, true);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent('openform');
                        $objModel->setConsultazione(false);
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_Filtra':
                        $this->caricaDatiGriglia();
                        $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella);
                        if ($this->DataSuperioreFiltro) {
                            Out::msgInfo("Attenzione", "É possibile tornare indietro fino ad un massimo di 2 anni dalla data odierna.<br>Utilizzare la ricerca del protocollo per una ricerca completa.");
                        }
                        break;

                    case $this->nameForm . '_ApriProtocollo':
                        $indice = $_POST[$this->gridProtocolli]['gridParam']['selarrrow'];
                        $this->ApriProtocollo($indice);
                        break;
                    case $this->nameForm . '_CreaNuovoDaModello':
                        $indice = $_POST[$this->gridProtocolli]['gridParam']['selarrrow'];
                        $this->CreaNuovoModello($indice);
                        break;
                }
                break;
            case 'onChange': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_selectUffici':
                        switch ($_POST[$this->nameForm . '_selectUffici']) {
                            case "@":
                                Out::html($this->nameForm . '_divMessaggio', "<span style=\"color:orange\">I protocolli che hai inserito.</span>");
                                break;
                            case "*":
                                Out::html($this->nameForm . '_divMessaggio', "<span style=\"color:green\">I protocolli assegnati ai tuoi uffici di appartenenza.</span>");
                                break;
                            default :
                                $anauff_rec = $this->proLib->GetAnauff($_POST[$this->nameForm . '_selectUffici']);
                                Out::html($this->nameForm . '_divMessaggio', "<span>I protocolli assegnati all'ufficio: " . $anauff_rec['UFFDES'] . '</span>');
                                break;
                        }
                        $this->caricaDatiGriglia();
                        $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella);
                        break;
                    case $this->nameForm . '_FlagTutti':
                    case $this->nameForm . '_FlagModelli':
                    case $this->nameForm . '_FlagNotifica':
                    case $this->nameForm . '_FlagNonAssegnati':
                    case $this->nameForm . '_FlagNonFascicolati':
                    case $this->nameForm . '_FlagIncompleti':
                    case $this->nameForm . '_StatoDocPredispostoProt':
                        $this->caricaDatiGriglia();
                        $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella);
                        break;

                    case $this->nameForm . '_LimiteVis':
                        Out::valore($this->nameForm . '_DaData', '');
                        if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
                            $this->MostraNascondiCampi();
                            break;
                        }
                        $this->caricaDatiGriglia();
                        $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella);
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_utente');
        App::$utente->removeKey($this->nameForm . '_codiceDest');
        App::$utente->removeKey($this->nameForm . '_tabella');
        App::$utente->removeKey($this->nameForm . '_DataSuperioreFiltro');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        App::$utente->removeKey($this->nameForm . '_utente');
        App::$utente->removeKey($this->nameForm . '_tabella');
        App::$utente->removeKey($this->nameForm . '_codiceDest');
        $this->close = true;
        if ($close)
            $this->close();
        Out::show($this->modelChiamante);
    }

    private function CaricaGrigliaGenerica($griglia, $appoggio, $tipo = '1', $pageRows = 20, $caption = '') {

        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        $Ordine = $_POST['sidx'];
        if ($Ordine == 'CODICE' || $Ordine == 'ANNO') {
            $Ordine = 'PRONUM';
        }
        $Sord = $_POST['sord'];
        if (!$Sord) {
            $Sord = 'desc';
        }
        if (!$Ordine || $Ordine == 'PRODAR') {
            $Ordine = 'DATAPROT ' . $Sord . ', ROWID ';
        }
        $Ordine.= ' ' . $Sord . ', ROWID ';
        $ita_grid01->setSortIndex($Ordine);
        $ita_grid01->setSortOrder($Sord);

        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaDatiGriglia() {
        $this->MostraNascondiCampi();
        $limiteProt = 101;
        if ($_POST[$this->nameForm . '_LimiteVis']) {
            $limiteProt = ''; // Pensare ad un limite forzato?
        }

        $sql = $this->creaSql();
        $anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        // Check Non Fascicolati:
        $incr = 0;
        $this->tabella = array();
        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_38 = $this->proLib->GetAnaent('38');

        foreach ($anapro_tab as $key => $anapro_rec) {
            if ($_POST[$this->nameForm . '_TipoVedi'] == 'NF') {
                if ($this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                    continue;
                }
            }
            $tagFatEle = '';
            $incr++;
            if ($limiteProt) {
                if ($incr >= $limiteProt) {
                    break;
                }
            }

            $anapro_rec['ANNO'] = substr($anapro_rec['PRONUM'], 0, 4);
            $anapro_rec['CODICE'] = intval(substr($anapro_rec['PRONUM'], 4));
            $prodar = $anapro_rec['PRODAR'];
            if ($anapro_rec['PROPAR'] == 'C' || ($anapro_rec['PROPAR'] == 'I' && !$anapro_rec['PRONOM'])) {
                $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
                if ($anades_mitt) {
                    $anapro_rec['PRONOM'] = $anades_mitt['DESNOM'];
                }
            }
            if ($anapro_rec['PROPAR'] == 'I') {
                $Indice_rec = $this->segLib->GetIndice($anapro_rec['PRONUM'], 'anapro', false, $anapro_rec['PROPAR']);
                $dizionarioIdelib = $this->segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                $anapro_rec['CODICE'] = intval($dizionarioIdelib['PROGRESSIVO']);
            }

            $allegati = $this->proLibAllegati->checkPresenzaAllegati($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $ini_tag = "";
            $fin_tag = "";
            $NonAssegnato = "";
            if (!$anapro_rec['TOTASSEGNATI'] && $anapro_rec['PROPAR'] == 'A') {
                $ini_tag = "<p style = 'background-color:orange;'>";
                $fin_tag = "</p>";
                $NonAssegnato = '<span class="ui-state-error ui-corner-all " title="Non Assegnato" style="border: 0;" >';
                $NonAssegnato.= '<div class="ita-html"  style="display:inline-block;" ><span class="ui-icon ui-icon-person ita-tooltip "></span></div></span>';
            }
            if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100") {
                $ini_tag = "<p style = 'background-color:yellow;'>";
                $fin_tag = "</p>";
            }
            if ($allegati) {
                $anapro_rec['PRONAF'] = '<span style="display:inline-block" class="ui-icon ui-icon-document">Con Allegati </span>';
            } else {
                if ($anaent_32['ENTDE4'] == 1) {
                    $ini_tag = "<p style = 'background-color:yellow;'>";
                    $fin_tag = "</p>";
                    $anapro_rec['PRONAF'] = '<span style="display:inline-block" class="ui-icon ui-icon-alert">Senza Allegati </span>';
                }
            }
            if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
                $fin_tag = "</p>";
            }
            if ($anapro_rec['PRORISERVA']) {
                $ini_tag = "<p style = 'color:white;background-color:gray;'>";
                $fin_tag = "</p>";
                $anapro_rec['PRONOM'] = "RISERVATO";
                $anapro_rec['OGGOGG'] = "RISERVATO";
            }
            if ($anapro_rec['PROCODTIPODOC']) {
                if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE2'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4']) {
                    $tagFatEle = "<span style=\"display:inline-block;vertical-align:top;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
                }
            }

            // Controllo speizione PEC!
            if (trim(strtoupper($anapro_rec['PROTSP'])) == 'PEC') {
                $ini_tagOggetto = '<div class="ita-html" style="width:18px; display:inline-block;vertical-align:top;"><span class="ita-tooltip ui-icon ui-icon-mail-closed" title="Da PEC"></span></div><div style="display:inline-block;vertical-align:top;">';
                $fin_tagOggetto = '</div>';
            } else {
                $ini_tagOggetto = '<div style="width:18px; display:inline-block;vertical-align:top;"></div><div style="display:inline-block;vertical-align:top;">';
                $fin_tagOggetto = '</div>';
            }
            //Valorizzazione di default
            //$anapro_rec['PRONAF'] ui-icon-notice
            /* Controllo se il protocollo è fascicolato */
            $nonFas = '';
//            if (!$anapro_rec['PROFASKEY']) {
            if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                $nonFas = '<div class="ita-html" style="display:inline-block;" ><span style="display:inline-block" title="Non Fascicolato" class="ita-tooltip ui-icon ui-icon-notice">Non Fascicolato </span></div>';
            }

            /*
             * Decodifica di tipo Protocollo.
             */
            $iconaDocumento = 'ita-icon-register-document-16x16';
            $statoDocProt = '';
            switch ($anapro_rec['PROPAR']) {
                case 'P':
                    $tooltipitepar = 'PROTOCOLLO IN PARTENZA';
                    break;
                case 'C':
                    $tooltipitepar = 'DOCUMENTO FORMALE';
                    break;
                case 'A':
                    $tooltipitepar = 'PROTOCOLLO IN ARRIVO';
                    break;
                case 'I':
                    $iconaDocumento = 'ita-icon-register-document-green-16x16';
                    $tooltipitepar = 'INDICE DOCUMENTALE';
                    // SORGNUM
                    if ($anapro_rec['DESTNUM']) {
                        $Prot = intval(substr($anapro_rec['DESTNUM'], 4));
                        $Anno = substr($anapro_rec['DESTNUM'], 0, 4);
                        $Tip = $anapro_rec['DESTTIP'];
                        $statoDocProt = "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"Protocollato con N. $Prot/$Anno $Tip \" class=\"ita-tooltip ita-icon ita-icon-check-green-16x16\"></span>";
                    } else {
                        $statoDocProt = "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"Non protocollato\" class=\"ita-tooltip ita-icon ita-icon-check-orange-16x16\"></span>";
                    }

                    break;
                case 'W':
                    $iconaDocumento = 'ita-icon-footsteps-16x16';
                    $tooltipitepar = 'PASSO';
                    break;
            }
            $ProParInfo = "<div class=\"ita-html\"><div style=\"display:inline-block; width:8px;\">" . $anapro_rec['PROPAR'] . "</div><span style=\"display:inline-block;vertical-align:bottom;\" title=\"$tooltipitepar\" class=\"ita-tooltip ita-icon $iconaDocumento\"></span> $statoDocProt </div>";

            /*
             * Evidenza:
             */
            $arcite_tab = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
            if ($arcite_tab[0]['ITEEVIDENZA'] == 1) {
                $ini_tag = "<span style = 'display:inline-block; color:#BE0000;'>" . $ini_tag;
                $fin_tag = $fin_tag . "</span>";
            }

            $anapro_rec['PROPAR'] = $ini_tag . $ProParInfo . $fin_tag;
            $anapro_rec['ANNO'] = $ini_tag . $anapro_rec['ANNO'] . $fin_tag;
            $anapro_rec['CODICE'] = $ini_tag . $anapro_rec['CODICE'] . $fin_tag;
            $anapro_rec['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($prodar)) . $fin_tag;
            $anapro_rec['PRONOM'] = $ini_tag . $anapro_rec['PRONOM'] . $fin_tag;
            $anapro_rec['OGGOGG'] = $ini_tagOggetto . $ini_tag . $anapro_rec['OGGOGG'] . $fin_tag . $fin_tagOggetto;
            $anapro_rec['PROLRIS'] = $ini_tag . $anapro_rec['PROLRIS'] . $fin_tag;
            $anapro_rec['PRONAF'] = $tagFatEle . $nonFas . $anapro_rec['PRONAF'] . $NonAssegnato . '<p></p>';
            $anapro_rec['DATAPROT'] = $prodar;

            $risultato = $this->getStatoNotifiche($anapro_tab[$key]);
//            if ($risultato['ANOMALIA'] === false && $_POST[$this->nameForm . '_TipoVedi'] == 'E') {
//                continue;
//            }
            $anapro_rec['NOTIFICA'] = $risultato['STATONOTIFICA'];
//                    ." I: " . $anapro_rec['INVIATE'] . " DI: " . $anapro_rec['DAINVIARE'] . " C: " . $anapro_rec['CONSEGNATE'] . " A: " . $anapro_rec['ACCETTATE'];
            $this->tabella[] = $anapro_rec;
        }
    }

    private function nuovoProtocollo($tipo) {
        $model = 'proArri';
        $_POST['event'] = 'openform';
        $_POST['tipoProt'] = $tipo;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        Out::setFocus('', 'proArri_Propre1');
        $profilo = proSoggetto::getProfileFromIdUtente();
        if (($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) && $this->tipoProt == 'A') {
            $oggutenti_check = $profilo['OGG_UTENTE'];
            if ($oggutenti_check) {
                Out::setFocus('', "proArri_ANAPRO[PROCON]");
            }
        }
    }

    private function creaSql() {
        $this->DataSuperioreFiltro = false;
        $ufficio = $_POST[$this->nameForm . '_selectUffici'];
        $limiteVis = $_POST[$this->nameForm . '_LimiteVis'];
        $daData = $_POST[$this->nameForm . '_DaData'];

        $limiteProt = " LIMIT 0, 100";
        $limiteGiorni = 60;
        if ($limiteVis) {
            if ($limiteVis == 'DATE') {
                if ($daData) {
                    $limiteProt = "";
                    $ngiorni = $diff_data = $this->itaDate->dateDiffDays(date('Ymd'), $daData);
                    if ($ngiorni >= 730) {
                        $limiteGiorni = 730;
                        $this->DataSuperioreFiltro = true;
                    } else {
                        $limiteGiorni = $ngiorni;
                    }
                }
            } else {
                $limiteProt = "";
                $limiteGiorni = $limiteVis;
            }
        }

        $sql = $this->proLib->getSqlRegistro();
        //
        // Prime assegnazioni su Arcite
        //
        $WhereIndice = '';
        $sql.=" LEFT OUTER JOIN ARCITE ARCITE FORCE INDEX(I_ITEPRO)ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR";
        if ($this->segLib->checkExistDB('SEGR')) {
            $segrDB = $this->segLib->getSEGRDB()->getDB();
            $sql.= " LEFT OUTER JOIN $segrDB.INDICE INDICE ON $segrDB.INDICE.INDPRO=ANAPRO.PRONUM AND $segrDB.INDICE.INDPAR=ANAPRO.PROPAR AND $segrDB.INDICE.INDTIPODOC = '" . segLibDocumenti::TIPODOC_DOCUMENTO . "'  ";
            $WhereIndice = " OR ($segrDB.INDICE.INDTIPODOC IS NOT NULL AND $segrDB.INDICE.INDPREPAR <> '' ) ";
        }
        $where = '';
        if ($_POST['_search'] == true) {
            if ($_POST['PROPAR']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAPRO.PROPAR') . " = '" . addslashes(strtoupper($_POST['PROPAR'])) . "'";
            }
            if ($_POST['ANNO']) {
                $anno = $_POST['ANNO'] + 1;
                $where .= " AND ANAPRO.PRONUM >= " . $_POST['ANNO'] . "000000 AND ANAPRO.PRONUM <= " . $anno . "000000";
            }
            if ($_POST['ANNO'] && $_POST['CODICE']) {
                $where .= " AND ANAPRO.PRONUM =" . $_POST['ANNO'] . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT);
            } else if ($_POST['CODICE']) {
                $where .= " AND (ANAPRO.PRONUM =" . date('Y') . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT) . "";
                if ($this->segLib->checkExistDB('SEGR')) {
                    $where .= "  OR INDICE.IDELIB LIKE '%" . $_POST['CODICE'] . "%' ";
                }
                $where.= ") ";
            }
            if ($_POST['PRODAR']) {
                if (strlen($_POST['PRODAR']) == 8) {
                    $data = substr($_POST['PRODAR'], 4) . substr($_POST['PRODAR'], 2, 2) . substr($_POST['PRODAR'], 0, 2);
                } else if (strlen($_POST['PRODAR']) == 10) {
                    $data = substr($_POST['PRODAR'], 6) . substr($_POST['PRODAR'], 3, 2) . substr($_POST['PRODAR'], 0, 2);
                }
                if ($data) {
                    $where .= " AND ANAPRO.PRODAR= '" . $data . "'";
                }
            }
            if ($_POST['PRONOM']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAPRO.PRONOM') . " LIKE '%" . addslashes(strtoupper($_POST['PRONOM'])) . "%'";
            }
            if ($_POST['OGGOGG']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAOGG.OGGOGG') . " LIKE '%" . addslashes(strtoupper($_POST['OGGOGG'])) . "%'";
            }
            if ($_POST['PROLRIS']) {
                $where .= " AND ANAPRO.PROLRIS LIKE '" . $_POST['PROLRIS'] . "'";
            }
        }

        $codiceSoggetto = proSoggetto::getCodiceSoggettoFromIdUtente();
        if ($_POST[$this->nameForm . '_TipoVedi'] == 'M') {
            $template = ' AND ANAPRO.PROTEMPLATE = 1 ';
        }

        $sql .= " WHERE";
        $where_profilo = " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);

        switch ($ufficio) {
            case "@":
                $sql .= " (ARCITE.ITEDES='$codiceSoggetto' AND ARCITE.ITENODO='INS')";
                break;
            case "*":
                $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
                $sql .= ' (';
                foreach ($uffdes_tab as $uffdes_rec) {
                    //$sql .= " (UFFPRO.UFFCOD = '{$uffdes_rec['UFFCOD']}') OR";
                    $sql .= " (UFFPRO.UFFCOD = '{$uffdes_rec['UFFCOD']}' OR ANAPRO.PROUOF='{$uffdes_rec['UFFCOD']}') OR";
                }
                $sql .= " 1=0)";
                break;
            default:
                //$sql .= " (UFFPRO.UFFCOD = '$ufficio')";
                $sql .= " (UFFPRO.UFFCOD = '$ufficio' OR ANAPRO.PROUOF='$ufficio')";
                break;
        }

        $dataLimite = date('Ymd', strtotime('-' . $limiteGiorni . ' day', strtotime(date("Ymd"))));
        $sql .= " AND PRODAR>='$dataLimite'";

        $sql .= " AND (PROPAR='A' OR PROPAR='P' OR PROPAR='C' $WhereIndice )"; //OR PROPAR='AA' OR PROPAR='PA' OR PROPAR='CA'
        $sql .= " $where $template $where_profilo ORDER BY PRODAR DESC, PROORA DESC, PRONUM DESC " . $limiteProt;



//        $sql = "SELECT DISTINCT ANAPRO.ROWID AS ROWID, ANAPRO.PRONUM AS PRONUM, ANAPRO.PROLRIS AS PROLRIS, PROPAR, PRODAR AS PRODAR, PROPRE, PROCCA, PROCCF,
//            PROCAT,PRODAS, PROIND, PROCAP, PROCIT, PROPRO, PRONRA, PRONPA, PRONOM AS PRONOM, PRONAF, PRORISERVA, OGGOGG,SUBSTRING(PRONUM,1,4)AS ANNO,
//            SUBSTRING(PRONUM,5,6)AS CODICE, PROTSO, PROIDMAILDEST, PROMAIL
//            FROM ANAPRO ANAPRO
//            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
//            LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
//            WHERE (ARCITE.ITEDES='$codiceSoggetto' AND ARCITE.ITENODO='INS') AND (PROPAR='A' OR PROPAR='P' OR PROPAR='C' OR PROPAR='AA' OR PROPAR='PA' OR PROPAR='CA') $where $template ORDER BY PRODAR DESC, PROORA DESC, PRONUM DESC LIMIT 0,100";

        $sqlDaInviare = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE ANADES.DESNUM=A.PRONUM AND ANADES.DESPAR=A.PROPAR AND ANADES.DESTIPO='D' AND DESMAIL<>''
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                WHERE ANAPRO.PRONUM=A.PRONUM AND ANAPRO.PROPAR=A.PROPAR AND ANAPRO.PROMAIL<>''
            )";

        $sqlInviate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE ANADES.DESNUM=A.PRONUM AND ANADES.DESPAR=A.PROPAR AND ANADES.DESTIPO='D' AND DESMAIL<>'' AND ANADES.DESIDMAIL<>''
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                WHERE ANAPRO.PRONUM=A.PRONUM AND ANAPRO.PROPAR=A.PROPAR AND ANAPRO.PROMAIL<>'' AND ANAPRO.PROIDMAILDEST<>''
            )";

        $sqlAccettate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANADES.DESIDMAIL 
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='D' AND 
                        ANADES.DESMAIL<>'' AND 
                        ANADES.DESIDMAIL<>'' AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_ACCETTAZIONE . "'
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANAPRO.PROIDMAILDEST                     
                WHERE   ANAPRO.PRONUM=A.PRONUM AND 
                        ANAPRO.PROPAR=A.PROPAR AND 
                        ANAPRO.PROMAIL<>'' AND 
                        ANAPRO.PROIDMAILDEST<>''AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_ACCETTAZIONE . "'
            )";

        $sqlConsegnate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANADES.DESIDMAIL 
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='D' AND 
                        ANADES.DESMAIL<>'' AND 
                        ANADES.DESIDMAIL<>'' AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA . "'
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANAPRO.PROIDMAILDEST                     
                WHERE   ANAPRO.PRONUM=A.PRONUM AND 
                        ANAPRO.PROPAR=A.PROPAR AND 
                        ANAPRO.PROMAIL<>'' AND 
                        ANAPRO.PROIDMAILDEST<>''AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA . "'
            )";
        $sqlTotAssegnati = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='T'
            ) ";


        $sqlRet = "
            SELECT * 
                FROM (
                    SELECT
                            A.*,
                            $sqlDaInviare AS DAINVIARE,
                            $sqlInviate   AS INVIATE,
                            $sqlAccettate   AS ACCETTATE,
                            $sqlConsegnate   AS CONSEGNATE,
                            $sqlTotAssegnati   AS TOTASSEGNATI,
                            ANATSP.TSPTIPO AS TIPOSPED,    
                            PRODOCPROT.SORGNUM AS SORGNUM,    
                            PRODOCPROT.SORGTIP AS SORGTIP,    
                            PRODOCPROT.DESTNUM AS DESTNUM,    
                            PRODOCPROT.DESTTIP AS DESTTIP    
                        FROM
                    ({$sql}) A
                        LEFT OUTER JOIN ANATSP ANATSP ON A.PROTSP=ANATSP.TSPCOD
                        LEFT OUTER JOIN PRODOCPROT ON A.PRONUM=PRODOCPROT.SORGNUM AND A.PROPAR=PRODOCPROT.SORGTIP
                ) B
            
            WHERE 1=1 ";
        switch ($_POST[$this->nameForm . '_TipoVedi']) {
            case 'E':
                $sqlRet.=" AND (
                            (INVIATE - CONSEGNATE <> 0) OR
                            (INVIATE - ACCETTATE <> 0) 
                            OR ( DAINVIARE >= 1 AND INVIATE <> DAINVIARE )
                            OR ( INVIATE = 0 AND TIPOSPED = '2' AND PROPAR <> 'I' )
                        )";
                break;
            case 'N':
                $sqlRet.=" AND (
                           (B.PROPAR ='A') AND TOTASSEGNATI = 0
                        )";
                break;
            case 'NF':
                $sqlRet.=" AND B.PROFASKEY = '' ";
                break;
            case 'IN':
                $sqlRet.=" AND B.PROSTATOPROT = " . proLib::PROSTATO_INCOMPLETO . " ";
                break;

            default:
                break;
        }
        //Condizione Predisposti:
        switch ($_POST[$this->nameForm . '_StatoDocPredispostoProt']) {
            case '1':
                $sqlRet.=" AND (DESTNUM IS NULL AND PROPAR = 'I' ) ";
                break;
            case '2':
                $sqlRet.=" AND (DESTNUM IS NOT NULL AND SORGTIP = 'I' ) ";
                break;
        }


        return $sqlRet;
    }

    private function checkNotificaDestinatari($anapro_rec) {
        $risultato = array('DAINVIARE' => 0, 'INVIATE' => 0, 'ACCETTATE' => 0, 'CONSEGNATE' => 0);
        $destinatari = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], 'D');
        if ($anapro_rec['PROMAIL']) {
            $destinatari[] = array('DESMAIL' => $anapro_rec['PROMAIL'], 'DESIDMAIL' => $anapro_rec['PROIDMAILDEST']);
        }
        foreach ($destinatari as $destinatario) {
            if ($destinatario['DESMAIL']) {
                $risultato['DAINVIARE'] ++;
                if ($destinatario['DESIDMAIL']) {
                    $risultato['INVIATE'] ++;
                    $retRic = $this->proLib->checkMailRic($destinatario['DESIDMAIL']);
                    if ($retRic['ACCETTAZIONE']) {
                        $risultato['ACCETTATE'] ++;
                    }
                    if ($retRic['CONSEGNA']) {
                        $risultato['CONSEGNATE'] ++;
                    }
                }
            }
        }
        return $risultato;
    }

    private function getStatoNotifiche($anapro_rec) {
        $statoNotifica = '';
        $AnomaliePEC = '';

        if ($anapro_rec['PRONUM'] && $anapro_rec['PROPAR']) {
            $NotificheMail = $this->proLibMail->GetElencoNotifichePecProt($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            if ($NotificheMail['INDICE_ANOMALIE']) {
                $AnomaliePEC = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>";
            }
        }


        if ($anapro_rec['PROPAR'] === 'C') {
            return array('ANOMALIA' => false, 'STATONOTIFICA' => $statoNotifica);
        }
        $notificaTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-mail-closed"></span></div>';
        $notificaParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-mail-closed"></span></div>';
        $accTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-check"></span></div>';
        $accParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-check"></span></div>';
        $consTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-check"></span>';
        $consParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-check"></span></div>';
//        $risultatoNotifiche = $this->checkNotificaDestinatari($anapro_rec);
//        App::log($anapro_rec['PRONUM']." I: " . $risultatoNotifiche['INVIATE'] . " DI: " . $risultatoNotifiche['DAINVIARE'] . " C: " . $risultatoNotifiche['CONSEGNATE'] . " A: " . $risultatoNotifiche['ACCETTATE']);
        if ($anapro_rec['INVIATE'] == 0) {
            return array('ANOMALIA' => false, 'STATONOTIFICA' => '');
        }
        if ($anapro_rec['DAINVIARE'] - $anapro_rec['INVIATE'] == 0) {
            if ($anapro_rec['ACCETTATE'] == 0) {
                App::log($anapro_rec['PRONUM']);
                App::log(array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti));
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti);
            }
            if ($anapro_rec['CONSEGNATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accParziale);
            }
            if ($anapro_rec['INVIATE'] - $anapro_rec['ACCETTATE'] != 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accParziale . $consParziale);
            }
            if ($anapro_rec['INVIATE'] - $anapro_rec['CONSEGNATE'] != 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accTutti . $consParziale);
            }
            return array('ANOMALIA' => false, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accTutti . $consTutti);
        } else {
            if ($anapro_rec['ACCETTATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale);
            }
            if ($anapro_rec['CONSEGNATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale . $accParziale);
            }
            return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale . $accParziale . $consParziale);
        }
        return array('ANOMALIA' => false, 'STATONOTIFICA' => $AnomaliePEC);
    }

    private function CreaCombo() {
        $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
        Out::select($this->nameForm . '_selectUffici', 1, "@", "1", '<p style="color:orange;">Inseriti</p>');
        Out::select($this->nameForm . '_selectUffici', 1, "*", "0", '<p style="color:green;">I tuoi uffici.</p>');
        foreach ($uffdes_tab as $uffdes_rec) {
            $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
            Out::select($this->nameForm . '_selectUffici', 1, $uffdes_rec['UFFCOD'], '0', substr($anauff_rec['UFFDES'], 0, 30));
        }
        // Combo giorni

        Out::select($this->nameForm . '_LimiteVis', 1, "", "1", 'Ultimi 100 prot.');
        Out::select($this->nameForm . '_LimiteVis', 1, "30", "0", 'Ultimi 30 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "60", "0", 'Ultimi 60 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "90", "0", 'Ultimi 90 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "120", "0", 'Ultimi 120 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "150", "0", 'Ultimi 150 giorni');
        Out::select($this->nameForm . '_LimiteVis', 1, "DATE", "0", 'Da Data Specifica');
        // Prot
        Out::select($this->nameForm . '_StatoDocPredispostoProt', 1, "", "0", 'Tutti');
        Out::select($this->nameForm . '_StatoDocPredispostoProt', 1, "1", "0", 'In Attesa di Protocollazione');
        Out::select($this->nameForm . '_StatoDocPredispostoProt', 1, "2", "0", 'Protocollato');
    }

    private function MostraNascondiCampi() {
        Out::hide($this->nameForm . '_DaData_field');
        Out::hide($this->nameForm . '_Filtra');
        if ($_POST[$this->nameForm . '_LimiteVis'] == 'DATE') {
            Out::show($this->nameForm . '_DaData_field');
            Out::show($this->nameForm . '_Filtra');
        }
    }

    private function ApriProtocollo($indice) {
        $Anaproctr_rec = $this->proLib->GetAnapro($indice, 'rowid');

        switch ($Anaproctr_rec['PROPAR']) {
            case 'I':
                $Indice_rec = $this->segLib->GetIndice($Anaproctr_rec['PRONUM'], 'anapro', false, $Anaproctr_rec['PROPAR']);
                $segLibDocumenti = new segLibDocumenti();
                if (!$segLibDocumenti->ApriAtto($this->nameForm, $Indice_rec)) {
                    Out::msgStop("Attenzione", $segLibDocumenti->getErrMessage());
                }
                break;

            default:

                $model = 'proArri';
                $_POST = array();
                $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
                $_POST['event'] = 'openform';
                $_POST['proGest_ANAPRO']['ROWID'] = $indice;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
        }
    }

    private function CreaNuovoModello($indice) {
        $Anaproctr_rec = $this->proLib->GetAnapro($indice, 'rowid');
        $_POST = array();
        itaLib::openForm('proArri', true);
        /* @var $proArri proArri */
        $proArri = itaModel::getInstance('proArri');
        $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
        $proArri->setEvent('openform');
        $proArri->parseEvent();
        $proArri->Nuovo();
        $proArri->duplicaDoc($Anaproctr_rec['ROWID']);
    }

}

?>