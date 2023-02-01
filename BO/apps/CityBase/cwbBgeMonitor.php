<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE_MONITOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeMonitorHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function cwbBgeMonitor() {
    $cwbBgeMonitor = new cwbBgeMonitor();
    $cwbBgeMonitor->parseEvent();
    return;
}

// questa pagina corrisponde alla tabella bge_log che non sta su CITYWARE_DB 
// ma nel db CW_MONITOR_CLIENTI (default 10.0.0.10 db monitor tabella bge_log)
class cwbBgeMonitor extends cwbBpaGenTab {

    const CONNECTION_NAME = 'CW_MONITOR_CLIENTI';
    const GRID_FATTURAZIONE = 'gridBgeMonitorFatturazione';
    const GRID_SCHEDULAZIONE = 'gridBgeMonitorSchedulazione';
    const GRID_COLLEGAMENTI = 'gridBgeMonitorCollegamenti';
    const GRID_UPDATER = 'gridBgeMonitorUpdater';
    const GRID_PAGOPA = 'gridBgeMonitorPagoPa';
    const GRID_SIOPE_PLUS = 'gridBgeMonitorSiopePlus';
    const NAMEFORM_DETTAGLIO = 'cwbBgeMonitorTabDettaglio';
    const TAB_DETTAGLIO = 'tabDettaglio';
    const TABPANE_FATTURAZIONE = 'tabFatturazione';
    const TABPANE_SCHEDULAZIONE = 'tabSchedulazione';
    const TABPANE_PAGOPA = 'tabPagoPa';
    const TABPANE_COLLEGAMENTI = 'tabCollegamenti';
    const TABPANE_UPDATER = 'tabUpdater';
    const TABPANE_SIOPE_PLUS = 'tabSiopePlus';
    const ORE_INATTIVITA = '-24 hours';
    const TABELLA_COLLEGAMENTI = 'BGE_COLLEGAMENTI';
    const TABELLA_ENTI = 'BGE_ENTI';
    const ABBREVIATE = 60;
    const DEFAULT_DELAY = 300000;

    private $listEntiInGrid;
    private $fatturazionePresente;
    private $schedulazionePresente;
    private $pagopaPresente;
    private $updaterPresente;
    private $siopePlusPresente;
    private $monitorHelper;
    private $nameFormAliasDettaglio;
    private $origine = array(
        1 => 'Cityware',
        2 => 'Cw2',
        3 => 'Cityportal',
        4 => 'Cityware.Online'
    );
    private $mappingErrori = array(// rosso è ripetuto per fare da mapping con gli errori (errori 1,2...9 sono rossi)
        1 => 'red', 2 => 'red', 3 => 'red', 4 => 'red', 5 => 'red', 6 => 'red', 7 => 'red', 8 => 'red', 9 => 'red',
        50 => 'yellow', 51 => 'yellow', 52 => 'yellow', 53 => 'yellow',
        90 => 'DarkOrange',
        91 => 'DarkMagenta',
        92 => 'lawngreen',
        93 => 'lawngreen'
    );
    private $mappingAmbiti = array(
        self::TABPANE_FATTURAZIONE => 1,
        self::TABPANE_SCHEDULAZIONE => 2,
        self::TABPANE_PAGOPA => 3,
        self::TABPANE_UPDATER => 4,
        self::TABPANE_SIOPE_PLUS => 5
    );

    protected function initVars() {
        $this->GRID_NAME = 'gridBgeMonitor';
        $this->skipAuth = true;
        $this->noCrud = true;
        $this->TABLE_NAME = 'BGE_LOG';
        $this->libDB = new cwbLibDB_BGE_MONITOR();
        $this->searchOpenElenco = false;
        $this->listEntiInGrid = cwbParGen::getFormSessionVar($this->nameForm, '_listEntiInGrid');
        $this->fatturazionePresente = cwbParGen::getFormSessionVar($this->nameForm, '_fatturazionePresente');
        $this->schedulazionePresente = cwbParGen::getFormSessionVar($this->nameForm, '_schedulazionePresente');
        $this->pagopaPresente = cwbParGen::getFormSessionVar($this->nameForm, '_pagopaPresente');
        $this->updaterPresente = cwbParGen::getFormSessionVar($this->nameForm, '_updaterPresente');
        $this->siopePlusPresente = cwbParGen::getFormSessionVar($this->nameForm, '_siopePlusPresente');
        $this->nameFormAliasDettaglio = cwbParGen::getFormSessionVar($this->nameForm, '_nameFormAliasDettaglio');

        $this->monitorHelper = new cwbBgeMonitorHelper();
        Out::hide($this->nameForm . '_Importa');
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_listEntiInGrid', $this->listEntiInGrid);
            cwbParGen::setFormSessionVar($this->nameForm, '_fatturazionePresente', $this->fatturazionePresente);
            cwbParGen::setFormSessionVar($this->nameForm, '_schedulazionePresente', $this->schedulazionePresente);
            cwbParGen::setFormSessionVar($this->nameForm, '_pagopaPresente', $this->pagopaPresente);
            cwbParGen::setFormSessionVar($this->nameForm, '_updaterPresente', $this->updaterPresente);
            cwbParGen::setFormSessionVar($this->nameForm, '_siopePlusPresente', $this->siopePlusPresente);
            cwbParGen::setFormSessionVar($this->nameForm, '_nameFormAliasDettaglio', $this->nameFormAliasDettaglio);
        }
    }

    protected function postConnettiDB() {
        $this->connettiDBMonitor();
    }

    protected function preApriForm() {
        // select livello errore su tab ricerca
        // i numeri di ritorno corrispondono all'array $mappingErrori
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::TUTTI, 0, "Tutti");
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::SOLO_ATTIVI, 1, "Solo Attivi");
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::VERDE, 0, "Verde - Nessun Errore", "color:" . $this->mappingErrori[cwbBgeMonitorHelper::VERDE]);
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::GIALLO, 0, "Giallo - Warn", "font-weight: bold;color:" . $this->mappingErrori[cwbBgeMonitorHelper::GIALLO]);
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::ARANCIONE, 0, "Arancio - Errore", "color:" . $this->mappingErrori[cwbBgeMonitorHelper::ARANCIONE]);
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::ROSSO, 0, "Rosso - Errore Grave", "color:" . $this->mappingErrori[cwbBgeMonitorHelper::ROSSO]);
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::VIOLA, 0, "Viola - Bloccato", "color:" . $this->mappingErrori[cwbBgeMonitorHelper::VIOLA]);
        Out::select($this->nameForm . '_LIVELLO_ERRORE', 1, cwbBgeMonitorHelper::ESCLUDI_VERDE, 0, "Escludi Verde");
        //     $_POST[$this->nameForm . '_LIVELLO_ERRORE'] = cwbBgeMonitorHelper::SOLO_ATTIVI;
        // select servizi su tab ricerca
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::TUTTI, 1, "Tutti");
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::AMBITO_FATTURAZIONE, 0, "Solo Fatturazione");
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::AMBITO_SCHEDULAZIONE, 0, "Solo Schedulazione");
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::AMBITO_PAGOPA, 0, "Solo Pago Pa");
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::AMBITO_UPDATER, 0, "Solo Updater");
        Out::select($this->nameForm . '_SERVIZI', 1, cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS, 0, "Solo Siope+");
    }

    // sovrascrivo CITYWARE_DB con il db del monitor
    private function connettiDBMonitor() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(self::CONNECTION_NAME, '');     // monitor 10.0.0.10/monitor/bge_log
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    protected function postConstruct() {
        $this->PK = $this->getModelService()->newTableDef($this->TABLE_NAME, $this->MAIN_DB)->getPks(true);
    }

    // sovrascritti per togliere il tasto nuovo
    protected function setVisRisultato() {
        parent::setVisRisultato();
        Out::show($this->nameForm . '_Allinea');
        Out::show($this->nameForm . '_Verifica');
        Out::hide($this->nameForm . '_Pulisci');
    }

    protected function setVisDettaglio() {
        
    }

    protected function setVisNuovo() {
        parent::setVisNuovo();
        Out::show($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_Allinea');
        Out::hide($this->nameForm . '_Verifica');
        Out::valore($this->nameForm . '_BGE_ENTI[ENTE]', '');
        Out::valore($this->nameForm . '_BGE_ENTI[DESENTE]', '');
        Out::hide($this->nameForm . '_Pulisci');
    }

    protected function setVisRicerca() {
        $this->setVisControlli(false, false, true, false, false, false, true, false, false, false);
        Out::hide($this->nameForm . '_Allinea');
        Out::hide($this->nameForm . '_Verifica');
        Out::show($this->nameForm . '_Pulisci');
    }

    protected function dettaglio($index) {
        // non fa niente
    }

    protected function aggiungi($validate = true) {
        try {
            $data = $_POST[$this->nameForm . '_' . self::TABELLA_ENTI];
            $data['ATTIVO'] = 0;

            $modelService = cwbModelServiceFactory::newModelService('cwbBgeEntiMonitor', true);

            $validationInfo = $modelService->validate($this->MAIN_DB, self::TABELLA_ENTI, $data, itaModelService::OPERATION_INSERT, null);

            if (empty($validationInfo)) {
                // non ci sono errori di validazione
                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                $modelServiceData->addMainRecord(self::TABELLA_ENTI, $data);

                $modelService->insertRecord($this->MAIN_DB, self::TABELLA_ENTI, $modelServiceData->getData(), '');
                $this->nuovo();
            } else {
                foreach ($validationInfo as $currentInfo) {
                    if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                        $msg .= "Tabella: " . $this->TABLE_NAME;
                        $msg .= ($line != 0 ? "Riga: $line - " : "");
                        $msg .= $currentInfo['msg'] . '<br/>';
                    }
                }

                Out::msgStop("Errore ", $msg);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Inserimento.", "Errore inserimento " . $e->getMessage());
        }
    }

    protected function elenca($reload) {
        try {
            $this->stopTimer();
            if ($reload) {
                TableView::enableEvents($this->nameForm . '_' . $this->helper->getGridName());
                TableView::reload($this->nameForm . '_' . $this->helper->getGridName());
                return;
            }

            $sortIndex = null;
            $sortOrder = null;

            $ita_grid01 = $this->helper->initializeTableArray($this->getRecords());

            if ($_POST["sidx"] && $_POST["sidx"] !== "ROWID") {
                // ordinamento
                if ($_POST["sidx"] == 'DESENTE' || $_POST["sidx"] == 'SCARTATI') {
                    $ita_grid01->setSortIndex($_POST["sidx"]);
                } else {
                    // caso di ordinamento su colori
                    $ita_grid01->setSortIndex($_POST["sidx"] . '_PRIORITA'); // per ordinare i colori c'è un campo che si chiama nomeColonna_PRIORITA che contiene un numero progressivo                   
                }
                $ita_grid01->setSortOrder($_POST["sord"]);
            } else {
                // default ordina per desente
                $ita_grid01->setSortIndex("DESENTE");
                $ita_grid01->setSortOrder("asc");
            }

            if (!$this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01))) {
                Out::msgStop("Selezione", "Nessun record trovato.");
            } else {
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }
            $this->startTimer();
            $this->updateTimerLastUpdate();
        } catch (ItaException $e) {
            $this->startTimer();
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->startTimer();
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    private function getRecords() {
        $toReturn = array();

        $filters = array();
        $filtroLivelloErrore = $_POST[$this->nameForm . '_LIVELLO_ERRORE'];

        if ($_POST[$this->nameForm . '_DESENTE']) {
            $filters['DESENTE'] = $_POST[$this->nameForm . '_DESENTE'];
        }
        if ($filtroLivelloErrore == cwbBgeMonitorHelper::SOLO_ATTIVI) {
            $filters['ATTIVO'] = 1;
        }

        $enti = $this->libDB->leggiBgeEnti($filters);

        $this->listEntiInGrid = array();

        $serviziDaVedere = $_POST[$this->nameForm . '_SERVIZI'];

        //$debugMsg = '';
        foreach ($enti as $value) {
            $record = array();
            $time = microtime(true);
            
            $this->listEntiInGrid[] = $value['ENTE']; // mi salvo la lista degli enti in grafica

            $record['ENTE'] = $value['ENTE'];
            $record['DESENTE'] = $value['DESENTE'];

            // fatturazione
            $datiFatturazione = $this->libDB->dettaglioFatturazione($value['ENTE']);
            $prioritaFat = null;
            $record['FATTURAZIONELETTRONICA'] = $this->getEsitoHtml($datiFatturazione, $prioritaFat, true);
            $record['FATTURAZIONELETTRONICA_PRIORITA'] = $prioritaFat;

            // schedulazione
            $datiSchedulazione = $this->libDB->dettaglioSchedulazione($value['ENTE']);
            $prioritaSc = null;
            $record['SCHEDULAZIONI'] = $this->getEsitoHtml($datiSchedulazione, $prioritaSc, true);
            $record['SCHEDULAZIONI_PRIORITA'] = $prioritaSc;

            // pagopa
            $datiPagoPa = $this->libDB->dettaglioPagoPa($value['ENTE']);
            $prioritaPpa = null;
            $record['PAGOPA'] = $this->getEsitoHtml($datiPagoPa, $prioritaPpa, true);
            $record['PAGOPA_PRIORITA'] = $prioritaPpa;

            // updater
            $datiUpdater = $this->libDB->dettaglioUpdater($value['ENTE']);
            $prioritaUpdater = null;
            $record['UPDATER'] = $this->getEsitoHtml($datiUpdater, $prioritaUpdater, false);
            $record['UPDATER_PRIORITA'] = $prioritaUpdater;

            // siope plus
            $datiSiopePlus = $this->libDB->dettaglioSiopePlus($value['ENTE']);
            $prioritaSiopePlus = null;
            $record['SIOPE_PLUS'] = $this->getEsitoHtml($datiSiopePlus, $prioritaSiopePlus, false);
            $record['SIOPE_PLUS_PRIORITA'] = $prioritaSiopePlus;

            if ($_POST[$this->nameForm . '_LIVELLO_ERRORE'] && $_POST[$this->nameForm . '_LIVELLO_ERRORE'] != cwbBgeMonitorHelper::TUTTI) {// 0 = tutti, non eseguo il filtro
                // se c'è il filtro in ricerca su livello errore, vedo se il colore del filtro è presente in almeno una colonna
                // ed in base a quello decido se paginarlo o no
                $codiceErrore = $_POST[$this->nameForm . '_LIVELLO_ERRORE'];

                if ($codiceErrore == '99') { // 99 = escludi verde
                    $codiceErrore = '92';
                    if ((preg_match('/img/', $record['FATTURAZIONELETTRONICA']) || preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['FATTURAZIONELETTRONICA'])) && (preg_match('/img/', $record['SCHEDULAZIONI']) || preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['SCHEDULAZIONI'])) && (preg_match('/img/', $record['PAGOPA']) || preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['PAGOPA'])) && (preg_match('/img/', $record['UPDATER']) || preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['UPDATER'])) && (preg_match('/img/', $record['SIOPE_PLUS']) || preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['SIOPE_PLUS']))) {
                        continue; // passo al prossimo elemento del for se il filtro di ricerca è 'escludi verde' e tutte le colonne attive contengono verde
                    }
                } else {
                    if (!preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['FATTURAZIONELETTRONICA']) && !preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['SCHEDULAZIONI']) && !preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['PAGOPA']) && !preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['UPDATER']) && !preg_match('/' . $this->mappingErrori[$codiceErrore] . '/', $record['SIOPE_PLUS'])) {
                        continue; // passo al prossimo elemento del for se il colore del filtro di ricerca non è presente in questo ente
                    }
                }
            }

            if ($filtroLivelloErrore != cwbBgeMonitorHelper::TUTTI) {
                if ($filtroLivelloErrore == cwbBgeMonitorHelper::SOLO_ATTIVI) {
                    if (($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_FATTURAZIONE && $prioritaFat == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SCHEDULAZIONE && $prioritaSc == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_PAGOPA && $prioritaPpa == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_UPDATER && $prioritaUpdater == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS && $prioritaSiopePlus == cwbBgeMonitorHelper::NON_ATTIVO)) {
                        //se c'è il filtro per servizio escludo gli altri

                        continue;
                    }
                } else if ($filtroLivelloErrore == cwbBgeMonitorHelper::ESCLUDI_VERDE) {
                    if (($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_FATTURAZIONE && $prioritaFat == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SCHEDULAZIONE && $prioritaSc == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_PAGOPA && $prioritaPpa == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_UPDATER && $prioritaUpdater == cwbBgeMonitorHelper::NON_ATTIVO) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS && $prioritaSiopePlus == cwbBgeMonitorHelper::NON_ATTIVO)) {
                        //se c'è il filtro per servizio escludo gli altri

                        continue;
                    }
                } else {
                    if (($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_FATTURAZIONE && $prioritaFat != $filtroLivelloErrore) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SCHEDULAZIONE && $prioritaSc != $filtroLivelloErrore) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_PAGOPA && $prioritaPpa != $filtroLivelloErrore) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_UPDATER && $prioritaUpdater != $filtroLivelloErrore) || ($serviziDaVedere == cwbBgeMonitorHelper::AMBITO_SIOPE_PLUS && $prioritaSiopePlus != $filtroLivelloErrore)) {
                        //se c'è il filtro per servizio escludo gli altri

                        continue;
                    }
                }
            }

            //$debugMsg .= "Ente: ".$value['DESENTE']." - ExTime: ".round(microtime(true)-$time,2)."s\r\n";
            $toReturn[] = $record;
        }
		
        //Out::msgInfo('', $debugMsg);

        return $toReturn;
    }

    // se c'è almeno un ko nel record in ingresso, torna quadrato rosso se invece sono tutti ok torna quadrato verde.
    // se il record è vuoto torna l'icona dell'ingranaggio
    private function getEsitoHtml($res, &$priorita, $controllaData = false) {
        if (!$res || empty($res)) {
            // non c'è niente quindi non è attivo l'ambito
            $path_ico = ITA_BASE_PATH . '/apps/CityBase/resources/gear.png';

            $html = cwbLibHtml::formatDataGridIcon('', $path_ico);
            $priorita = cwbBgeMonitorHelper::NON_ATTIVO; // setto per riferimento il campo priorità che viene usato per l'ordinamento dei colori
        } else {
            // scorro i record e segno i codici dei colori trovati    
            $listaPriorita = array();
            foreach ($res as $key => $value) {
                $priorita = null;
                if ($controllaData) {
                    // controllo che la data del record rientri nelle ultime 24 ore sennò è colore viola
                    // (caso di schedulazione che non gira da 24 ore)
                    $dataOra = $value['DATA'] . ' ' . $value['ORA'];

                    if (strtotime($dataOra) <= strtotime(self::ORE_INATTIVITA)) {
                        $priorita = cwbBgeMonitorHelper::VIOLA; // setto per riferimento il campo prioerità che viene usato per l'ordinamento dei colori
                    }
                }

                if (!$priorita) {
                    // se color non è stato valorizzato dal controllo data guardo il campo su db 
                    //il controllo sul tipo di colore viene fatto dal metodo wakeup ad esclusione del colore viola che va controllato a posteriori 
                    $priorita = $value['COLORE_ESITO'];
                }

                $listaPriorita[] = $priorita;
            }

            $prioritaFinale = 99999; // numero alto a caso (piu la priorità è bassa piu è grave l'errore)
            foreach ($listaPriorita as $prior) {
                // cerco l'errore più grave tra i vari record, da far vedere sul riepilogo 
                if ($prioritaFinale > $prior) {
                    $prioritaFinale = $prior;
                }
            }

            $html = '<div style="background-color:' . $this->mappingErrori[$prior] . ';width:15px;height:15px;border:1px solid #000;margin: auto;" />';

            // metto la presa a carico
            if ($res[0]['PRESA_CARICO']) {
                $html .= '<div>' . $res[0]['UTE_PRESA_CARICO'] . '</div>';
            }
        }
        return $html;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case "openform":
                $this->startTimer();

                break;
            case "subGridRowExpanded":
                $this->expandRow();

                break;
            case "editGridRow":
            case "dbClickRow":
                switch ($_POST['id']) {
                    case (preg_match('/_' . self::GRID_COLLEGAMENTI . '/', $_POST['id']) ? true : false) :
                        $this->modificaCollegamento();
                        break;
                }

                break;
            case "delGridRow":
                switch ($_POST['id']) {
                    case (preg_match('/_' . self::GRID_COLLEGAMENTI . '/', $_POST['id']) ? true : false) :
                        $this->cancellaCollegamento();
                        break;
                }

                break;
            case "cellSelect":
                switch ($_POST['id']) {
                    case (preg_match('/_' . self::GRID_FATTURAZIONE . '/', $_POST['id']) ? true : false) :
                    case (preg_match('/_' . self::GRID_SCHEDULAZIONE . '/', $_POST['id']) ? true : false) :
                    case (preg_match('/_' . self::GRID_UPDATER . '/', $_POST['id']) ? true : false) :
                    case (preg_match('/_' . self::GRID_SIOPE_PLUS . '/', $_POST['id']) ? true : false) :
                        if (preg_match('#^XMLICON#', $_POST['colName']) === 1) {
                            $this->downloadBinario($_POST['rowid']);
                        } else if (preg_match('#^ZIPICON#', $_POST['colName']) === 1) {
                            $this->downloadBinario($_POST['rowid'], false);
                        }
                        break;
                }

                break;
            case 'onClick':
                // sul name form del dettaglio c'è l'alias che non conosco
                if (preg_match('/_SalvaCollegamenti/', $_POST['id']) === 1) {
                    $this->salvaCollegamenti();
                } else if (preg_match('/_AnnullaCollegamenti/', $_POST['id']) === 1) {
                    $this->pulisciInserimentoCollegamento();
                } else if (preg_match('/_PrendiInCaricoSCH/', $_POST['id']) === 1) {
                    $this->prendiCarico(self::TABPANE_SCHEDULAZIONE);
                } else if (preg_match('/_PrendiInCaricoPpa/', $_POST['id']) === 1) {
                    $this->prendiCarico(self::TABPANE_PAGOPA);
                } else if (preg_match('/_PrendiInCaricoFatt/', $_POST['id']) === 1) {
                    $this->prendiCarico(self::TABPANE_FATTURAZIONE);
                } else if (preg_match('/_PrendiInCaricoSiopePlus/', $_POST['id']) === 1) {
                    $this->prendiCarico(self::TABPANE_SIOPE_PLUS);
                } else if ($_POST['id'] == ($this->nameForm . '_Verifica')) {
                    $this->filesToProcess();
                } else if ($_POST['id'] == ($this->nameForm . '_Allinea')) {
                    $this->wakeup();
                } else if ($_POST['id'] == ($this->nameForm . '_Importa')) {
                    $this->importaCsv();
                } else if ($_POST['id'] == ($this->nameForm . '_Pulisci')) {
                    $this->pulisciDatiVecchi();
                } else if (preg_match('/_DisattivaSCH/', $_POST['id']) === 1) {
                    $this->disattivaAmbito(self::TABPANE_SCHEDULAZIONE);
                } else if (preg_match('/_DisattivaPpa/', $_POST['id']) === 1) {
                    $this->disattivaAmbito(self::TABPANE_PAGOPA);
                } else if (preg_match('/_DisattivaFatt/', $_POST['id']) === 1) {
                    $this->disattivaAmbito(self::TABPANE_FATTURAZIONE);
                } else if (preg_match('/_DisattivaSiopePlus/', $_POST['id']) === 1) {
                    $this->disattivaAmbito(self::TABPANE_SIOPE_PLUS);
                } else if (preg_match('/_StopTrackingSiopePlus/', $_POST['id']) === 1) {
                    $this->stopTrackingSiope();
                }

                break;
            case 'ontimer':
                if ($_POST['nameform'] == $this->nameForm) {
                    if ($this->controllaModifiche()) {
                        // refresh delle grid solo se ci sono modifiche 
                        $this->elenca(true);
                    }
                }
                break;
            case 'onClickTablePager':
                if (preg_match('/' . self::GRID_COLLEGAMENTI . '/', $_POST['id']) === 1) {
                    $this->caricaCollegamento();
                } else if (preg_match('/' . self::GRID_FATTURAZIONE . '/', $_POST['id']) === 1) {
                    $nameformAlias = str_replace("_" . self::GRID_FATTURAZIONE, "", $_POST['id']);
                    $this->gridFatturazione($_POST[$nameformAlias . '_ENTE_HIDDEN'], $nameformAlias);
                } else if (preg_match('/' . self::GRID_PAGOPA . '/', $_POST['id']) === 1) {
                    $nameformAlias = str_replace("_" . self::GRID_PAGOPA, "", $_POST['id']);
                    $this->gridPagoPa($_POST[$nameformAlias . '_ENTE_HIDDEN'], $nameformAlias);
                } else if (preg_match('/' . self::GRID_SCHEDULAZIONE . '/', $_POST['id']) === 1) {
                    $nameformAlias = str_replace("_" . self::GRID_SCHEDULAZIONE, "", $_POST['id']);
                    $this->gridSchedulazione($_POST[$nameformAlias . '_ENTE_HIDDEN'], $nameformAlias);
                } else if (preg_match('/' . self::GRID_SIOPE_PLUS . '/', $_POST['id']) === 1) {
                    $nameformAlias = str_replace("_" . self::GRID_SIOPE_PLUS, "", $_POST['id']);
                    $this->gridSiopePlus($_POST[$nameformAlias . '_ENTE_HIDDEN'], $nameformAlias);
                }
                break;
        }
    }

    private function expandRow() {
        // inietto la form con le grid di dettaglio sul div del rowexpansion
        $ente = $_POST['rowid'];
        $nameform = $this->nameForm . '_' . $this->GRID_NAME;

        // scorro gli enti e collasso le righe che non sono l'ente selezionato (rowexpand di una riga alla volta)
        foreach ($this->listEntiInGrid as $value) {
            if ($value != $ente) {
                Out::codice('$("#' . $nameform . '").collapseSubGridRow(' . $value . ')');
            }
        }

        $alias = self::NAMEFORM_DETTAGLIO . time();
        $this->nameFormAliasDettaglio = $alias;
        $generator = new itaGenerator();
        $html = $generator->getModelHTML(self::NAMEFORM_DETTAGLIO, false, '', false, $alias);

        Out::html($_POST['subgridDivId'], $html);
        Out::valore($alias . "_ENTE_HIDDEN", $ente); // salvo un campo hidden sulla form dettaglio sennò mi perdo il riferimento all'ente

        $this->pulisciInserimentoCollegamento($alias);

        $this->gridFatturazione($ente, $alias);
        $this->gridSchedulazione($ente, $alias);
        $this->gridPagoPa($ente, $alias);
        $this->gridUpdater($ente, $alias);
        $this->gridSiopePlus($ente, $alias);
        $this->caricaCollegamento($ente, $alias);

        if ($this->fatturazionePresente) {
            // abilito il primo tab se c'è la fatturazione
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_FATTURAZIONE);
        } else if ($this->schedulazionePresente) {
            // abilito il secondo tab se il primo è disabilitato e il secondo no
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_SCHEDULAZIONE);
        } else if ($this->pagopaPresente) {
            // abilito il terzo tab se il primo e secondo sono disabilitati e il terzo no
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_PAGOPA);
        } else if ($this->updaterPresente) {
            // abilito il terzo tab se il primo e secondo sono disabilitati e il terzo no
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_UPDATER);
        } else if ($this->siopePlusPresente) {
            // abilito il terzo tab se il primo e secondo sono disabilitati e il terzo no
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_SIOPE_PLUS);
        } else {
            Out::tabSelect($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_COLLEGAMENTI);
        }
    }

    private function gridFatturazione($ente, $alias) {
        $this->fatturazionePresente = true;
        $datiFatturazione = $this->libDB->dettaglioFatturazione($ente);

        if (!$datiFatturazione) {
            // se è vuoto disabilito il panel del dettaglio
            Out::tabDisable($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_FATTURAZIONE);
            $this->fatturazionePresente = false;
            return;
        }

        Out::valore($alias . "_UTENTE_CARICO_FAT", $datiFatturazione[0]['UTE_PRESA_CARICO']); // carico la presa carico

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($alias);
        $helper->setGridName(self::GRID_FATTURAZIONE);

        $this->parseRecord($datiFatturazione, 'F');

        $ita_grid01 = $helper->initializeTableArray($datiFatturazione);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
        } else {
            TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
        }
    }

    private function gridSchedulazione($ente, $alias) {
        $this->schedulazionePresente = true;
        $datiSchedulazione = $this->libDB->dettaglioSchedulazione($ente);

        if (!$datiSchedulazione) {
            // se è vuoto disabilito il panel del dettaglio
            Out::tabDisable($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_SCHEDULAZIONE);
            $this->schedulazionePresente = false;
            return;
        }
        Out::valore($alias . "_UTENTE_CARICO_SCH", $datiSchedulazione[0]['UTE_PRESA_CARICO']); // carico la presa carico

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($alias);
        $helper->setGridName(self::GRID_SCHEDULAZIONE);

        $this->parseRecord($datiSchedulazione, 'S');

        $ita_grid01 = $helper->initializeTableArray($datiSchedulazione);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
        } else {
            TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
        }
    }

    private function gridPagoPa($ente, $alias) {
        $this->pagopaPresente = true;
        $datiPagoPa = $this->libDB->dettaglioPagoPa($ente);

        if (!$datiPagoPa) {
            // se è vuoto disabilito il panel del dettaglio
            Out::tabDisable($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_PAGOPA);
            $this->pagopaPresente = false;
            return;
        }
        Out::valore($alias . "_UTENTE_CARICO_PPA", $datiPagoPa[0]['UTE_PRESA_CARICO']); // carico la presa carico

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($alias);
        $helper->setGridName(self::GRID_PAGOPA);

        $this->parseRecord($datiPagoPa, 'P');

        if ($_POST["sidx"] && $_POST["sidx"] !== "ROW_ID") {
            $sortIndex = $_POST["sidx"];
            if (preg_match('/' . self::SUFFISSO_CAMPI_FORMATTATI . '/', $sortIndex)) {
                // se il nome del campo finisce per _formatted lo rimuovo per prendere il nome vero
                $sortIndex = strtok($sortIndex, self::SUFFISSO_CAMPI_FORMATTATI);
            }
            if ($sortIndex === false) {
                throw new Exception("Errore Ordinamento");
            }
            $sortOrder = $_POST["sord"];

            if ($sortIndex == 'ESITOP') {
                // se mi chiedono di ordinare per esito, esitop contiene l'html, il codice 
                // che identifica il giusto colore si trova su ESITO_PRIORITAP
                $sortIndex = 'ESITO_PRIORITAP';
            }
        }

        $ita_grid01 = $helper->initializeTableArray($datiPagoPa, $sortIndex, $sortOrder);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
        } else {
            TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
        }
    }

    private function gridUpdater($ente, $alias) {
        $this->updaterPresente = true;
        $datiUpdater = $this->libDB->dettaglioUpdater($ente);

        if (!$datiUpdater) {
            // se è vuoto disabilito il panel del dettaglio
            Out::tabDisable($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_UPDATER);
            $this->updaterPresente = false;
            return;
        }

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($alias);
        $helper->setGridName(self::GRID_UPDATER);

        $this->parseRecord($datiUpdater, 'U', false);

        if ($_POST["sidx"] && $_POST["sidx"] !== "ROW_ID") {
            $sortIndex = $_POST["sidx"];
            if (preg_match('/' . self::SUFFISSO_CAMPI_FORMATTATI . '/', $sortIndex)) {
                // se il nome del campo finisce per _formatted lo rimuovo per prendere il nome vero
                $sortIndex = strtok($sortIndex, self::SUFFISSO_CAMPI_FORMATTATI);
            }
            if ($sortIndex === false) {
                throw new Exception("Errore Ordinamento");
            }
            $sortOrder = $_POST["sord"];

            if ($sortIndex == 'ESITOU') {
                // se mi chiedono di ordinare per esito, esitop contiene l'html, il codice 
                // che identifica il giusto colore si trova su ESITO_PRIORITAU
                $sortIndex = 'ESITO_PRIORITAU';
            }
        }

        $ita_grid01 = $helper->initializeTableArray($datiUpdater, $sortIndex, $sortOrder);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
        } else {
            TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
        }
    }

    private function gridSiopePlus($ente, $alias) {
        $this->siopePlusPresente = true;
        $datiSiope = $this->libDB->dettaglioSiopePlus($ente);

        if (!$datiSiope) {
            // se è vuoto disabilito il panel del dettaglio
            Out::tabDisable($alias . '_' . self::TAB_DETTAGLIO, $alias . '_' . self::TABPANE_SIOPE_PLUS);
            $this->siopePlusPresente = false;
            return;
        }

        Out::valore($alias . "_UTENTE_CARICO_SIOPE_PLUS", $datiSiope[0]['UTE_PRESA_CARICO']); // carico la presa carico

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($alias);
        $helper->setGridName(self::GRID_SIOPE_PLUS);

        $this->parseRecord($datiSiope, 'S', false);

        $ita_grid01 = $helper->initializeTableArray($datiSiope);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
        } else {
            TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
        }
    }

    private function parseRecord(&$dati, $tag, $controllaData = true) {
        // scorro tutti i record ed aggingo una f/s/p alla fine di ogni chiave (perché sulla stessa form non ci possono essere
        // grid con colonne che si chiamano uguali quindi le devo diversificare)
        foreach ($dati as $key => $result_rec) {
            // cambio l'origine a numero con quella a string
            $result_rec['ORIGINE'] = $this->origine[$result_rec['ORIGINE']];
            $priorita = null;
            $result_rec['ESITO'] = $this->getEsitoHtml(array($result_rec), $priorita);
            $result_rec['ESITO_PRIORITA'] = $priorita;

            if ($result_rec['XML']) {
                $icona = '<span class="ita-icon ita-icon-File-Ext-xml-32x32" ></span>';
                $result_rec['XMLICON'] = $icona;
            }

            if ($result_rec['ZIP']) {
                $icona = '<span class="ita-icon ita-icon-File-Ext-zip-32x32" ></span>';
                $result_rec['ZIPICON'] = $icona;
            }

            if ($result_rec['CUSTOM_DATA']) {
                $json = stream_get_contents($result_rec['CUSTOM_DATA']);
                $customData = json_decode($json, true);

                $result_rec = array_merge($result_rec, $customData);

                if ($customData['ESITO']) {
                    $result_rec['ESITO'] = $this->getEsitoHtml(array($result_rec), $priorita);
                    $result_rec['ESITO_PRIORITA'] = $priorita;
                }
            }

            $result_rec['DATA'] = $result_rec['DATA'] . ' ' . $result_rec['ORA'];
            if ($controllaData && strtotime($result_rec['DATA']) <= strtotime(self::ORE_INATTIVITA)) {
                // se é bloccato, coloro la data di viola, lasciando il quadratino dell'esito con il colore originale
                $viola = $this->mappingErrori[cwbBgeMonitorHelper::VIOLA]; // torno viola
                $result_rec['DATA'] = "<div style='color:white; background-color: " . $viola . ";'>" . $result_rec['DATA'] . "</div>";
            }

            $result_rec['STATO'] = "<div style='white-space: normal;'>" . $result_rec['STATO'] . "</div>";

            $dati[$key] = $this->parseRecordSuffisso($result_rec, $tag);
        }
    }

    private function downloadBinario($idBgeLog, $isXml = true) {
        $res = $this->libDB->leggiBgeLogChiave($idBgeLog);
        $filename = time();
        $corpo = null;

        if ($isXml) {
            $corpo = $res['XML'];
            $filename .= '.xml';
        } else {
            $corpo = $res['ZIP'];

            // spacchetto lo zip e se c'è solo 1 pdf scarico direttamente quello sennò scarico lo zip. 
            $filenameToDelete = itaLib::getUploadPath() . "/temp" . $filename . '.zip';
            file_put_contents($filenameToDelete, $corpo);
            rewind($corpo);
            $zip = new ZipArchive;
            $res = $zip->open($filenameToDelete);
            if ($res === true) {
                $extractDir = itaLib::getUploadPath() . '/extract' . $filename;
                $zip->extractTo($extractDir);
                $zip->close();

                $files = array_diff(scandir($extractDir), array('.', '..'));
                if (count($files) === 1) {
                    $firstRecName = reset($files);

                    $corpo = file_get_contents($extractDir . '/' . $firstRecName);
                }

                $filename .= '.pdf';
            } else {
                $filename .= '.zip';
            }

            unlink($filenameToDelete);
        }
        if ($corpo) {
            cwbLib::downloadDocument($filename, $corpo, false);
        } else {
            Out::msgStop("Errore", "Errore reperimento binario");
        }
    }

    /**
     * Controlla se ci sono dei documenti da elaborare
     */
    private function filesToProcess() {
        $res = $this->monitorHelper->filesToProcess();
        if ($res === false) {
            Out::msgStop("Errore Verifica Ftp", "Si è verificato il seguente errore: " . $this->monitorHelper->getLastErrorDescription());
            return;
        }
        Out::msgInfo("Verifica Ftp", "Documenti da elaborare: " . $res);
    }

    /**
     * Viene chiamato da Mule per avvertire che è arrivato qualcosa sull'ftp
     */
    private function wakeup() {
        $res = $this->monitorHelper->wakeup();
        if ($res === false) {
            Out::msgStop("Errore Elaborazione Dati da Ftp", "Si è verificato il seguente errore: " . $this->monitorHelper->getLastErrorDescription());
            return;
        }
        Out::msgInfo("Esito", print_r($res));

        $this->elenca(true);
    }

    private function pulisciInserimentoCollegamento($alias = null) {
        if (!$alias) { // se non viene passato alias lo calcolo
            foreach ($_POST as $key => $value) {
                if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                    preg_match('/' . self::NAMEFORM_DETTAGLIO . '[0-9]+_ENTE_HIDDEN/', $key, $nameWithAlias);
                    $alias = str_replace('_ENTE_HIDDEN', '', $nameWithAlias[0]);

                    break;
                }
            }
        }

        if ($alias) {
            Out::valore($alias . '_TIPO_COLLEGAMENTO', '');
            Out::valore($alias . '_AMBITO_COLLEGAMENTO', '');
            Out::valore($alias . '_TESTO_COLLEGAMENTO', '');
            Out::valore($alias . '_ID_COLLEGAMENTO', '');
        }
    }

    private function insertUpdateCollegamento($data) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::TABELLA_COLLEGAMENTI), true, false);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord(self::TABELLA_COLLEGAMENTI, $data);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, $this->nameForm, $data);
        if ($data['ID']) {
            $modelService->updateRecord($this->MAIN_DB, self::TABELLA_COLLEGAMENTI, $modelServiceData->getData(), $recordInfo);
        } else {
            $modelService->insertRecord($this->MAIN_DB, self::TABELLA_COLLEGAMENTI, $modelServiceData->getData(), $recordInfo);
        }
    }

    private function cancellaCollegamento() {
        $id = $_POST['rowid'];

        $value['ID'] = $id;

        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::TABELLA_COLLEGAMENTI));
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord(self::TABELLA_COLLEGAMENTI, $value);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $this->nameForm, $value);
        $modelService->deleteRecord($this->MAIN_DB, self::TABELLA_COLLEGAMENTI, $modelServiceData->getData(), $recordInfo);
        $this->caricaCollegamento();
    }

    private function modificaCollegamento($data) {
        $id = $_POST['rowid'];

        $toUpdate = $this->libDB->leggiCollegamentoChiave($id);

        foreach ($_POST as $key => $value) {
            if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                preg_match('/' . self::NAMEFORM_DETTAGLIO . '[0-9]+_ENTE_HIDDEN/', $key, $nameWithAlias);
                $alias = str_replace('_ENTE_HIDDEN', '', $nameWithAlias[0]);

                Out::valore($alias . '_ID_COLLEGAMENTO', $toUpdate['ID']);
                Out::valore($alias . '_TIPO_COLLEGAMENTO', $toUpdate['TIPO']);
                Out::valore($alias . '_AMBITO_COLLEGAMENTO', $toUpdate['AMBITO']);
                Out::valore($alias . '_TESTO_COLLEGAMENTO', stream_get_contents($toUpdate['TESTO']));

                return;
            }
        }
    }

    private function caricaCollegamento($ente, $alias) {
        if (!$ente) {
            foreach ($_POST as $key => $value) {
                if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                    preg_match('/' . self::NAMEFORM_DETTAGLIO . '[0-9]+_ENTE_HIDDEN/', $key, $nameWithAlias);

                    $ente = $value;
                    $alias = str_replace('_ENTE_HIDDEN', '', $nameWithAlias[0]);

                    break;
                }
            }
        }

        $collegamenti = $this->libDB->leggiCollegamento($ente);
        if ($collegamenti) {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($alias);
            $helper->setGridName(self::GRID_COLLEGAMENTI);

            $this->parseCollegamenti($collegamenti);

            $ita_grid01 = $helper->initializeTableArray($collegamenti);

            if (!$ita_grid01->getDataPage('json')) {
                TableView::clearGrid($helper->getNameForm() . '_' . $helper->getGridName());
            } else {
                TableView::enableEvents($helper->getNameForm() . '_' . $helper->getGridName());
            }
        } else {
            TableView::clearGrid($alias . '_' . self::GRID_COLLEGAMENTI);
        }
    }

    private function salvaCollegamenti() {
        $ente = null;
        foreach ($_POST as $key => $value) {
            if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                preg_match('/' . self::NAMEFORM_DETTAGLIO . '[0-9]+_ENTE_HIDDEN/', $key, $nameWithAlias);

                $ente = $value;
                $alias = str_replace('_ENTE_HIDDEN', '', $nameWithAlias[0]);
            } else {
                if ($value) {
                    if (preg_match('/_TIPO_COLLEGAMENTO/', $key) === 1) {
                        $data['TIPO'] = $value;
                    } else if (preg_match('/_AMBITO_COLLEGAMENTO/', $key) === 1) {
                        $data['AMBITO'] = $value;
                    } else if (preg_match('/_TESTO_COLLEGAMENTO/', $key) === 1) {

                        $stream = fopen('php://memory', 'r+');
                        fwrite($stream, $value);
                        rewind($stream);

                        $data['TESTO'] = $stream;
                    } else if (preg_match('/_ID_COLLEGAMENTO/', $key) === 1) {
                        $data['ID'] = $value;
                    }
                }
            }
        }

        if (!$data) {
            Out::msgStop("Errore", "Inserire dei dati");
            return;
        }

        if (!$ente) {
            Out::msgStop("Errore", "Errore reperimento ente");
            return;
        }

        $data['ENTE'] = $ente;
        $this->insertUpdateCollegamento($data);

        $this->pulisciInserimentoCollegamento(); // ripulisco le caselle

        $this->caricaCollegamento();
    }

    private function parseCollegamenti(&$collegamenti) {
        foreach ($collegamenti as $key => $Result_rec) {
            // abbrevio tutto a 60 caratteri
            $res = $this->parseRecordSuffisso($Result_rec, 'C');
            $testo = stream_get_contents($res['TESTOC']);
            $pos = strrpos($testo, "<canvas");
            if (!$pos || $pos > self::ABBREVIATE) {
                $pos = self::ABBREVIATE;
            }
            $res['TESTOC'] = substr($testo, 0, $pos) . '...';
            if (strlen($res['AMBITOC']) > self::ABBREVIATE) {
                $res['AMBITOC'] = substr($res['AMBITOC'], 0, self::ABBREVIATE) . '...';
            }
            if (strlen($res['TIPOC']) > self::ABBREVIATE) {
                $res['TIPOC'] = substr($res['TIPOC'], 0, self::ABBREVIATE) . '...';
            }

            $collegamenti[$key] = $res;
        }
    }

    private function parseRecordSuffisso($Result_rec, $tag) {
        $parsed_record = array();
        foreach ($Result_rec as $keyRec => $value) {
            $parsed_record[$keyRec . $tag] = $value;
            unset($Result_rec[$keyRec]);
        }
        return $parsed_record;
    }

    private function prendiCarico($tipo) {
        $ute = null;
        $ente = null;
        foreach ($_POST as $key => $value) {
            // sul name form del dettaglio c'è l'alias che non conosco
            if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                $ente = $value;
            } else if (self::TABPANE_FATTURAZIONE === $tipo && preg_match('/_UTENTE_CARICO_FAT/', $key) === 1) {
                $ute = $value;
            } else if (self::TABPANE_SCHEDULAZIONE === $tipo && preg_match('/_UTENTE_CARICO_SCH/', $key) === 1) {
                $ute = $value;
            } else if (self::TABPANE_PAGOPA === $tipo && preg_match('/_UTENTE_CARICO_PPA/', $key) === 1) {
                $ute = $value;
            } else if (self::TABPANE_SIOPE_PLUS === $tipo && preg_match('/_UTENTE_CARICO_SIOPE_PLUS/', $key) === 1) {
                $ute = $value;
            }
        }

        if ($ute && $ente) {
            if (self::TABPANE_FATTURAZIONE === $tipo) {
                $datiFatturazione = $this->libDB->dettaglioFatturazione($ente);
                $this->updatePresaCarico($datiFatturazione, $ute);
            } else if (self::TABPANE_SCHEDULAZIONE === $tipo) {
                $datiPagoPa = $this->libDB->gridPagoPa($ente);
                $this->updatePresaCarico($datiPagoPa, $ute);
            } else if (self::TABPANE_PAGOPA === $tipo) {
                $datiSchedulazione = $this->libDB->gridSchedulazione($ente);
                $this->updatePresaCarico($datiSchedulazione, $ute);
            } else if (self::TABPANE_SIOPE_PLUS === $tipo) {
                $datiSiopePlus = $this->libDB->gridSiopePlus($ente);
                $this->updatePresaCarico($datiSiopePlus, $ute);
            }
        } else {
            Out::msgStop("Errore", "Errore Presa a Carico");
        }
    }

    // disattiva un ambito di un ente (cancella tutti i record di bge_log per quell'ente e ambito)
    private function disattivaAmbito($tipo) {
        $ente = null;
        foreach ($_POST as $key => $value) {
            // sul name form del dettaglio c'è l'alias che non conosco
            if (preg_match('/_ENTE_HIDDEN/', $key) === 1) {
                $ente = $value;
                break;
            }
        }

        if ($tipo && $ente) {
            $this->libDB->cancellaBgeLog($ente, $this->mappingAmbiti[$tipo]);
            Out::msgInfo("Disattivazione", "Disattivazione Eseguita! Ricaricare la pagina.");
        } else {
            Out::msgStop("Errore", "Errore Disattivazione");
        }
    }

    private function updatePresaCarico($data, $ute) {
        foreach ($data as $key => $value) {
            $value['PRESA_CARICO'] = 1;
            $value['UTE_PRESA_CARICO'] = $ute;
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($this->TABLE_NAME));
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($this->TABLE_NAME, $value);
            $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $this->nameForm, $value);
            $modelService->updateRecord($this->MAIN_DB, $this->TABLE_NAME, $modelServiceData->getData(), $recordInfo);
        }
    }

    private function pulisciDatiVecchi() {
        $this->libDB->cancellaVecchiBgeLog();
    }

    private function importaCsv() {
        $csv = file("c://temp/csvToImport.csv");
        if (!$csv) {
            Out::msgStop("Csv non trovato", "Posizionare il csv su : C://temp/csvToImport.csv");
        } else {
            $array = array_map('str_getcsv', $csv);

            foreach ($array as $key => $value) {
                if ($key > 0) {
                    $data = explode(";", $value[0]);
                    $this->monitorHelper->insertEnte($data[0], $data[1]);
                }
            }
        }
    }

    private function startTimer() {
        $devLib = new devLib();
        $delay = $devLib->getEnv_config('MONITOR_EVENTI', 'codice', 'DELAY', false);
        $delay = (empty($delay['CONFIG']) ? self::DEFAULT_DELAY : $delay['CONFIG']) / 1000;
        Out::addTimer($this->nameForm . '_divTimer_controller', $delay, null, false, false);
    }

    private function stopTimer() {
        Out::removeTimer($this->nameForm . '_divTimer_controller');
    }

    private function updateTimerLastUpdate() {
        Out::innerHtml($this->nameForm . '_divTimer_lastUpdate', date('H:i:s - d/m/Y'));
    }

    // controllo se ci sono modifiche (se la data e ora dell'ultima modifica è maggiore di adesso - DELAY)
    private function controllaModifiche() {
        $ultimaModifica = $this->libDB->leggiDataUltimaModificaLog();

        $ultimaDataOra = DateTime::createFromFormat('Y-m-d', $ultimaModifica['DATA']);
        $oraExploded = explode(":", $ultimaModifica['ORA']);
        $ultimaDataOra->setTime($oraExploded[0], $oraExploded[1], $oraExploded[2]);

        $date_now = new DateTime();
        $devLib = new devLib();
        $delay = $devLib->getEnv_config('MONITOR_EVENTI', 'codice', 'DELAY', false);
        $delay = $delay['CONFIG'] / 1000;
        $date_now->modify('-' . $delay . ' second');

        if ($ultimaDataOra > $date_now) {
            // se la data dell'ultimo record è maggiore della data corrente - il delay del timer 
            // allora devo fare refresh
            return true;
        }

        return false;
    }

    private function stopTrackingSiope() {
        $idSelect = $_POST[$this->nameFormAliasDettaglio . '_' . self::GRID_SIOPE_PLUS]['gridParam']['selrow'];
        if (!$idSelect || !is_numeric($idSelect)) {
            Out::msgStop("Errore", "Selezionare un record");
            return;
        }

        $this->libDB->updateSiopePlusVisualizzazione($_POST[$this->nameFormAliasDettaglio . '_ENTE_HIDDEN'], $idSelect);

        $this->gridSiopePlus($_POST[$this->nameFormAliasDettaglio . '_ENTE_HIDDEN'], $this->nameFormAliasDettaglio);
    }

}

?>