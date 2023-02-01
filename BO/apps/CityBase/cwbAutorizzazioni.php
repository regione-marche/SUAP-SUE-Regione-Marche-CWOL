<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';

define ('AREE_PER_GRID', serialize(array('B', 'A', 'D', 'F', 'S', 'T')));

function cwbAutorizzazioni() {
    $cwbAutorizzazioni = new cwbAutorizzazioni();
    $cwbAutorizzazioni->parseEvent();
    return;
}

class cwbAutorizzazioni extends cwbBpaGenTab {
    
    // valori tipoRicerca : 
    // 1 - utente
    // 2 - ruolo
    private $disableTornaAElenco;
    private $tipoRicerca;
    private $utente;
    private $ruolo;
    private $aree;
    private $moduli;
    private $moduliGrid;
    private $moduliGridBackup;
    
    private $primaArea;
    private $primoModulo;
    private $areaCorrente;
    private $moduloCorrente;
    
    private $gridCaricate;
    
    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbAutorizzazioni';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->GRID_NAME = 'gridAutorizzazioni';
        
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 2;
        
        $this->noCrud = true;
        
        $this->libDB = new cwbLibDB_BOR();
        $this->libDB_GENERIC = new cwbLibDB_GENERIC();
        
        $this->disableTornaAElenco = cwbParGen::getFormSessionVar($this->nameForm, 'disableTornaAElenco');
        $this->tipoRicerca = cwbParGen::getFormSessionVar($this->nameForm, 'tipoRicerca');
        $this->utente = cwbParGen::getFormSessionVar($this->nameForm, 'utente');
        $this->ruolo = cwbParGen::getFormSessionVar($this->nameForm, 'ruolo');
        $this->aree = cwbParGen::getFormSessionVar($this->nameForm, 'aree');
        $this->moduli = cwbParGen::getFormSessionVar($this->nameForm, 'moduli');
        $this->moduliGrid = cwbParGen::getFormSessionVar($this->nameForm, 'moduliGrid');
        $this->moduliGridBackup = cwbParGen::getFormSessionVar($this->nameForm, 'moduliGridBackup');
        $this->primaArea = cwbParGen::getFormSessionVar($this->nameForm, 'primaArea');
        $this->primoModulo = cwbParGen::getFormSessionVar($this->nameForm, 'primoModulo');
        $this->areaCorrente = cwbParGen::getFormSessionVar($this->nameForm, 'areaCorrente');
        $this->moduloCorrente = cwbParGen::getFormSessionVar($this->nameForm, 'moduloCorrente');
        $this->gridCaricate = cwbParGen::getFormSessionVar($this->nameForm, 'gridCaricate');
        
        if(!($this->disableTornaAElenco) && array_key_exists('disableTornaAElenco', $_POST)) {
            $this->disableTornaAElenco = $_POST['disableTornaAElenco'];
        }
        
        if(!($this->tipoRicerca)) {
            $this->tipoRicerca = $_POST['tipoRicerca'];
        }
        switch($this->tipoRicerca) {
            case 2:
                $this->TABLE_NAME = 'BOR_AUTRUO';
                break;
            default:
                $this->TABLE_NAME = 'BOR_AUTUTE';
                break;
        }
    }
    
    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'disableTornaAElenco', $this->disableTornaAElenco);
            cwbParGen::setFormSessionVar($this->nameForm, 'tipoRicerca', $this->tipoRicerca);
            cwbParGen::setFormSessionVar($this->nameForm, 'utente', $this->utente);
            cwbParGen::setFormSessionVar($this->nameForm, 'ruolo', $this->ruolo);
            cwbParGen::setFormSessionVar($this->nameForm, 'aree', $this->aree);
            cwbParGen::setFormSessionVar($this->nameForm, 'moduli', $this->moduli);
            cwbParGen::setFormSessionVar($this->nameForm, 'moduliGrid', $this->moduliGrid);
            cwbParGen::setFormSessionVar($this->nameForm, 'moduliGridBackup', $this->moduliGridBackup);
            cwbParGen::setFormSessionVar($this->nameForm, 'primaArea', $this->primaArea);
            cwbParGen::setFormSessionVar($this->nameForm, 'primoModulo', $this->primoModulo);
            cwbParGen::setFormSessionVar($this->nameForm, 'areaCorrente', $this->areaCorrente);
            cwbParGen::setFormSessionVar($this->nameForm, 'moduloCorrente', $this->moduloCorrente);
            cwbParGen::setFormSessionVar($this->nameForm, 'gridCaricate', $this->gridCaricate);
        }
    }
    
    // Personalizzazione per recordLock
    protected function bloccaChiave($currentRecord) {
        if ($this->viewMode) {
            return;
        }

        try {
            //Calcolo chiave primaria del record a partire da BDI_INDICI
            $modelService = $this->getModelService();
            $pkString = $currentRecord['CUSTOMKEY'];
            if(!$pkString) {
                switch($this->tipoRicerca) {
                    case 1:
                        $pkString = $this->utente;
                        break;
                    case 2:
                        $pkString = $this->ruolo;
                        break;
                }
            }
            
            $this->LOCK = $modelService->lockRecord($this->MAIN_DB, $this->TABLE_NAME, $pkString, "", 0);
            
            if($this->LOCK['status'] !== -1){
                cwbParGen::setFormSessionVar($this->nameFormOrig, 'lock' . $this->TABLE_NAME, $this->LOCK);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore blocco record", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore blocco record", $e->getMessage(), '600', '600');
        }
    }
    
    private function loadForm() {
        $this->tipoRicerca = $_POST['tipoRicerca'];
        $this->utente = $_POST['utente'];
        $this->ruolo = $_POST['ruolo'];
        
        if(!$this->tipoRicerca) {
            $this->tipoRicerca = 1;
        }
        
        if($this->utente || $this->ruolo) {
            if($this->utente) {
                $this->dettaglio($this->utente);
            }
            if($this->ruolo) {
                $this->dettaglio($this->ruolo);
            }
        } else {
            Out::show($this->nameForm . '_Nuovo');
            Out::hide($this->nameForm . '_btnApplica');
            Out::hide($this->nameForm . '_btnOk');
            Out::hide($this->nameForm . '_btnChiudi');
            Out::hide($this->nameForm . '_btnRimuoviAutorizzazioni');
            Out::hide($this->nameForm . '_btnTutteL');
            Out::hide($this->nameForm . '_btnTutteG');
            Out::hide($this->nameForm . '_btnTutteC');
            
            $this->elenca(true);
        }
        
        switch($this->tipoRicerca) {
            case 1:
                Out::html($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli_lbl', 'Situazione autorizzazione per utenti');
                Out::html('jqgh_' . $this->nameForm . '_gridAutorizzazioni' . '_DECODIFICA', 'Utente');
                if($this->utente) {
                    Out::show($this->nameForm . '_divRicUtente');
                    $this->setUtenteSearch($this->utente);
                    if($this->disableTornaAElenco) {
                        Out::hide($this->nameForm . '_Torna');
                    } else {
                        Out::show($this->nameForm . '_Torna');
                    }
                }
                break;
            case 2:
                Out::html($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli_lbl', 'Situazione autorizzazione per ruoli');
                Out::html('jqgh_' . $this->nameForm . '_gridAutorizzazioni' . '_DECODIFICA', 'Ruolo');
                Out::gridSetColumnFilterAttribute($this->nameForm, $this->GRID_NAME, 'DECODIFICA', 'onkeypress', 0, 'return (event.charCode >= 48 && event.charCode <= 57)');
                if($this->ruolo) {
                    Out::show($this->nameForm . '_divRicRuolo');
                    $this->setRuoloSearch($this->ruolo);
                    if($this->disableTornaAElenco) {
                        Out::hide($this->nameForm . '_Torna');
                    } else {
                        Out::show($this->nameForm . '_Torna');
                    }
                }
                break;
        }
        
    }
    
    protected function setGridFilters() {
        $this->gridFilters = array();
        if(!empty($_POST['DECODIFICA'])) {
            switch($this->tipoRicerca) {
                case 1:
                    $this->gridFilters['CODUTE'] = $this->formData['DECODIFICA'];
                    break;
                case 2:
                    $this->gridFilters['KRUOLO'] = $this->formData['DECODIFICA'];
                    break;
            }
        }
    }
    
    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        if(strlen($filtri['DECODIFICA']) > 0){
            switch($this->tipoRicerca) {
                case 1:
                    $filtri['CODUTE'] = trim($this->formData[$this->nameForm . '_DECODIFICA']);
                    break;
                case 2:
                    $filtri['KRUOLO'] = trim($this->formData[$this->nameForm . '_DECODIFICA']);
                    break;
            }
        }
        
        $this->compilaFiltri($filtri);
        switch($this->tipoRicerca) {
            case 1:
                $this->SQL = $this->libDB->getSqlleggiDistinctCoduteBorAutute($filtri, false, $sqlParams);
                break;
            case 2:
                $this->SQL = $this->libDB->getSqlleggiDistinctKruoloBorAutruo($filtri, false, $sqlParams);
                break;
        }
    }
    
    protected function sqlDettaglio($index, &$sqlParams = array()) {
        switch($this->tipoRicerca) {
            case 1:
                $this->SQL = $this->libDB->getSqlleggiDistinctCoduteBorAutute(array('CODUTE_KEY'=>$index), false, $sqlParams);
                break;
            case 2:
                $this->SQL = $this->libDB->getSqlleggiDistinctKruoloBorAutruo(array('KRUOLO'=>$index), false, $sqlParams);
                break;
        }
    }
    
    protected function elaboraRecords($Result_tab) {
        $aree = unserialize(AREE_PER_GRID);
        
        if(count($Result_tab) > 0) {
            foreach ($Result_tab as $key => $Result_rec) {
                switch($this->tipoRicerca) {
                    case 1:
                        $Result_tab[$key]['DECODIFICA'] = $Result_rec['CUSTOMKEY'];
                        
                        foreach ($aree as $k => $area) {
                            $count = $this->libDB->countBorAutute($Result_rec['CUSTOMKEY'], $area);
                            $icon = ($count > 0 ? 'ui-icon-check' : 'ui-icon-closethick');
                            $Result_tab[$key][$area] = "<span class=\"ui-icon $icon\"></span>";
                        }
                        break;
                    case 2:
                        $Result_tab[$key]['DECODIFICA'] = $Result_rec['CUSTOMKEY'] . ' - ' . $Result_rec['DES_RUOLO'];
                        
                        foreach ($aree as $k => $area) {
                            $count = $this->libDB->countBorAutoruo($Result_rec['CUSTOMKEY'], $area);
                            $icon = ($count > 0 ? 'ui-icon-check' : 'ui-icon-closethick');
                            $Result_tab[$key][$area] = "<span class=\"ui-icon $icon\"></span>";
                        }
                        break;
                }
            }
        }
        
        return $Result_tab;
    }
    
    protected function postNuovo() {
        switch($this->tipoRicerca) {
            case 1:
                $this->setUtenteSearch(null);
                Out::show($this->nameForm . '_divRicUtente');
                Out::enableField($this->nameForm . '_CODUTE');
                break;
            case 2:
                $this->setRuoloSearch(null);
                Out::show($this->nameForm . '_divRicRuolo');
                Out::enableField($this->nameForm . '_KRUOLO');
                break;
        }
        
        Out::hide($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_btnApplica');
        Out::hide($this->nameForm . '_btnOk');
        Out::show($this->nameForm . '_btnChiudi');
        Out::hide($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli');
    }
    
    protected function postDettaglio($index, &$sqlDettaglio = null) {
        switch($this->tipoRicerca) {
            case 1:
                $this->setUtenteSearch($index);
                Out::disableField($this->nameForm . '_CODUTE');
                break;
            case 2:
                $this->setRuoloSearch($index);
                Out::disableField($this->nameForm . '_KRUOLO');
                break;
        }
        
        Out::hide($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_btnChiudi');
        Out::hide($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli');
    }
    
    protected function tornaAElenco() {
        parent::tornaAElenco();
        Out::show($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_btnRimuoviAutorizzazioni');
        Out::hide($this->nameForm . '_btnTutteL');
        Out::hide($this->nameForm . '_btnTutteG');
        Out::hide($this->nameForm . '_btnTutteC');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_btnApplica');
        Out::hide($this->nameForm . '_btnOk');
        Out::hide($this->nameForm . '_btnChiudi');
        Out::show($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli');
        
        $this->elenca(true);
    }
    
    protected function postAggiungi($esito = null) {
        Out::show($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli');
    }
    
    protected function postAggiorna($esito = null) {
        Out::show($this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli');
    }
    
    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform': 
                $this->loadForm();
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_btnChiudi':
                        $this->close();
                        break;
                    case $this->nameForm . '_btnDuplica':
                        $this->duplicaAutorizzazioni();
                        break;
                    case $this->nameForm . '_btnApplica':
                        $this->salvaModifiche();
                        break;
                    case $this->nameForm . '_btnOk':
                        $this->salvaModifiche();
                        $this->tornaAElenco();
                        break;
                    case $this->nameForm . '_CODUTE_butt':
                        cwbLib::apriFinestraRicerca('cwbBorUtenti', $this->nameForm, 'returnBorUtenti', 'CODUTE', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_KRUOLO_butt':
                        cwbLib::apriFinestraRicerca('cwbBorRuoli', $this->nameForm, 'returnBorRuoli', 'KRUOLO', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_btnRimuoviAutorizzazioni':
                        $this->cambiaTutteAutorizzazioni('');
                        break;
                    case $this->nameForm . '_btnTutteL':
                        $this->cambiaTutteAutorizzazioni('L');
                        break;
                    case $this->nameForm . '_btnTutteG':
                        $this->cambiaTutteAutorizzazioni('G');
                        break;
                    case $this->nameForm . '_btnTutteC':
                        $this->cambiaTutteAutorizzazioni('C');
                        break;
                    case $this->nameForm . '_btnStampaAutorizzazioniUtentiRuoli':
                        $this->stampaAutorizzazioniUtentiRuoli();
                        break;
                }
                if (preg_match('/' . $this->nameForm . '_divTabPaneArea_([A-Z]{1})/', $_POST['id'], $matches)) {
                    $area = $matches[1];
                    $this->areaCorrente = $area;
                    $this->onClickArea($area);
                }
                if (preg_match('/' . $this->nameForm . '_divTabPane_([A-Z]{1})_([A-Z]{3})/', $_POST['id'], $matches)) {
                    $area = $matches[1];
                    $modulo = $matches[2];
                    $this->moduloCorrente = $modulo;
                    $this->onClickModulo($area, $modulo);
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODUTE':
                        $this->setUtenteSearch(trim($_POST[$this->nameForm . '_CODUTE']));
                        break;
                    case $this->nameForm . '_KRUOLO':
                        $this->setRuoloSearch(trim($_POST[$this->nameForm . '_KRUOLO']));
                        break;
                }
                break;
            case 'returnBorUtenti':
                $this->setUtenteSearch($this->formData['returnData']['CODUTE']);
                break;
            case 'returnBorRuoli':
                $this->setRuoloSearch($this->formData['returnData']['KRUOLO']);
                break;
            case 'afterSaveCell':
                if (preg_match('/' . $this->nameForm . '_AUT_([A-Z]{3})_([0-9]{3})/', $_POST['id'], $matches)) {
                    $codmodulo = $matches[1];
                    $prog = $matches[2];
                    $value = $_POST['value'];
                    $this->onChangeAut($codmodulo, $prog, $value);
                }
                break;
        }
    }
    
    private function setUtenteSearch($codute) {
        $codute = trim($codute);
        if(strlen($codute) > 0) {
            $utente = $this->libDB->leggiBorUtentiChiave($codute);

            if($utente) {
                Out::show($this->nameForm . '_divRicUtente');
                Out::valore($this->nameForm . '_CODUTE', $utente['CODUTE']);
                Out::valore($this->nameForm . '_NOMEUTE', $utente['NOMEUTE']);
                $this->reinizializzaVariabili();
                $this->utente = $utente['CODUTE'];
                $this->renderAutorizzazioni();
            } else {
                Out::valore($this->nameForm . '_CODUTE', null);
                Out::valore($this->nameForm . '_NOMEUTE', null);
                $this->reinizializzaVariabili();
                $this->hideAutorizzazioni();
                Out::msgStop('ATTENZIONE', 'Nessun utente trovato con questo codice utente.');
            }
        } else {
            $this->reinizializzaVariabili();
            Out::valore($this->nameForm . '_CODUTE', null);
            Out::valore($this->nameForm . '_NOMEUTE', null);
            $this->hideAutorizzazioni();
        }
        
        Out::hide($this->nameForm . '_btnApplica');
        Out::hide($this->nameForm . '_btnOk');
    }
    
    private function setRuoloSearch($kruolo) {
        $kruolo = trim($kruolo);
        if(strlen($kruolo) > 0) {
            $ruolo = $this->libDB->leggiBorRuoliChiave($kruolo);

            if($ruolo) {
                Out::show($this->nameForm . '_divRicRuolo');
                Out::valore($this->nameForm . '_KRUOLO', $ruolo['KRUOLO']);
                Out::valore($this->nameForm . '_DES_RUOLO', $ruolo['DES_RUOLO']);
                $this->reinizializzaVariabili();
                $this->ruolo = $ruolo['KRUOLO'];
                $this->renderAutorizzazioni();
            } else {
                Out::valore($this->nameForm . '_KRUOLO', null);
                Out::valore($this->nameForm . '_DES_RUOLO', null);
                $this->reinizializzaVariabili();
                $this->hideAutorizzazioni();
                Out::msgStop('ATTENZIONE', 'Nessun ruolo trovato con questo codice ruolo.');
            }
        } else {
            $this->reinizializzaVariabili();
            Out::valore($this->nameForm . '_KRUOLO', null);
            Out::valore($this->nameForm . '_DES_RUOLO', null);
            $this->hideAutorizzazioni();
        }
        
        Out::hide($this->nameForm . '_btnApplica');
        Out::hide($this->nameForm . '_btnOk');
    }
    
    private function reinizializzaVariabili() {
        $this->utente = null;
        $this->ruolo = null;
        $this->moduliGrid = null;
        $this->moduliGridBackup = null;
        $this->gridCaricate = array();
    }
    
    private function hideAutorizzazioni() {
        Out::hide($this->nameForm . '_divAutorizzazioni');
        
        Out::hide($this->nameForm . '_btnRimuoviAutorizzazioni');
        Out::hide($this->nameForm . '_btnTutteL');
        Out::hide($this->nameForm . '_btnTutteG');
        Out::hide($this->nameForm . '_btnTutteC');
    }
    
    private function renderAutorizzazioni() {
        Out::show($this->nameForm . '_divAutorizzazioni');
        
        Out::hide($this->nameForm . '_Nuovo');
        
        Out::show($this->nameForm . '_btnRimuoviAutorizzazioni');
        Out::show($this->nameForm . '_btnTutteL');
        Out::show($this->nameForm . '_btnTutteG');
        Out::show($this->nameForm . '_btnTutteC');
        
        $this->renderAreeModuli();
    }
    
    private function renderAreeModuli() {
        if(!$this->aree) {
            $this->caricaAree();
            $this->generaTabAree();
        } else {
            // Se la grid è stata già caricata in precedenza allora simula il click della prima tab area
            Out::tabSelect($this->nameForm . '_divTabAree', $this->nameForm . '_divTabPaneArea_' . $this->primaArea);
            $this->onClickArea($this->primaArea);
        }
    }
    
    private function caricaAree() {
        $this->aree = $this->libDB->leggiBorMaster();
        if($this->aree) {
            // Sposto l'area Base in prima posizione
            $areaBase = null;
            $posAreaBase = 0;
            foreach ($this->aree as $key => $area) {
                if($area['CODAREAMA'] == 'B') {
                    $posAreaBase = $key;
                    $areaBase = $area;
                    break;
                }
            }
            if($posAreaBase > 0) {
                $this->aree[$posAreaBase] = $this->aree[0];
                $this->aree[0] = $areaBase;
            }
            
            // Rimuove le aree senza moduli e autorizzazioni definite
            foreach ($this->aree as $key => $area) {
                $count = $this->libDB->countBorDesautJoinModuliPerArea($area['CODAREAMA']);
                if($count == 0) {
                    unset($this->aree[$key]);
                }
            }
        } else {
            Out::msgStop('ATTENZIONE', 'Impossibile caricare le aree di Cityware.');
        }
    }
    
    private function generaTabAree() {
        $aliasTabPadre = $this->nameForm . '_divTabAree';
        $aliasTabFirstPane = null;
        
        foreach ($this->aree as $key => $area) {
            $aliasTabPane = $this->nameForm . '_divTabPaneArea_' . $area['CODAREAMA'];
            if(!$aliasTabFirstPane) {
                $aliasTabFirstPane = $aliasTabPane;
                $this->primaArea = $area['CODAREAMA'];
            }
            $aliasTab = $this->nameForm . '_divTabArea_' . $area['CODAREAMA'];
            $html_tab = '<div id="' . $aliasTabPane . '" class="ita-tabpane {eventActivate:true}" title="' . $area['DESAREA'] . '">';
            $html_tab .= '<div id="' . $aliasTab . '" class="ita-tab {eventActivate:true}" >';   
            $html_tab .= '</div>';
            $html_tab .= '</div>';

            Out::tabAdd($aliasTabPadre, '', $html_tab);
        }

        Out::tabSelect($aliasTabPadre, $aliasTabFirstPane);
    }
    
    private function onClickArea($area) {
        $this->generaTabModuliDaArea($area);
    }
    
    private function generaTabModuliDaArea($area) {
        if(!isSet($this->moduli[$area])) {
            $filtri = array();
            $filtri['CODAREAMA'] = $area;
            $this->moduli[$area] = $this->libDB->leggiBorModuli($filtri);
        
            $aliasTabFirstPane = null;
            
            $aliasTabPadre = $this->nameForm . '_divTabArea_'. $area;
            
            foreach ($this->moduli[$area] as $key => $modulo) {
                $area = $modulo['CODMODULO'][0];

                $alias = $this->nameForm . '_divTabPane_' . $area . '_' . $modulo['CODMODULO'];
                $aliasDiv = $this->nameForm . '_div_' . $area . '_' . $modulo['CODMODULO'];
                $html_tab = '<div style="height:55vh" id="' . $alias . '" class="ita-tabpane {eventActivate:true}" title="' . $modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO'] . '">';
                $html_tab .= '<div id="' . $aliasDiv . '" style="height:inherit" >';
                $html_tab .= '</div>';
                $html_tab .= '</div>';

                Out::tabAdd($aliasTabPadre, '', $html_tab);

                if(!$aliasTabFirstPane) {
                    $aliasTabFirstPane = $alias;
                    if($area === $this->primaArea) {
                        $this->primoModulo = $modulo['CODMODULO'];
                    }
                }
            }
        
            Out::tabSelect($aliasTabPadre, $aliasTabFirstPane);
        } else {
            Out::tabSelect($this->nameForm . '_divTabArea_'. $area, $this->nameForm . '_divTabPane_' . $area . '_' . $this->moduli[$area][0]['CODMODULO']);
            $this->onClickModulo($area, $this->moduli[$area][0]['CODMODULO'], false);
        }
    }
    
    private function onClickModulo($area, $modulo, $refresh = false, $createGrid = true) {
        $this->generaGridPerModulo($area, $modulo, $refresh, $createGrid);
    }
    
    private function generaGridPerModulo($area, $codmodulo, $refresh = false, $createGrid = true) {
        if($refresh || !$this->moduliGrid[$codmodulo] || !$this->gridCaricate[$codmodulo]) {
            $aliasContainer = $this->nameForm . '_div_' . $area . '_' . $codmodulo;
            
            Out::innerHtml($aliasContainer, "");

            $model = cwbLib::innestaForm('utiJqGridCustom', $aliasContainer);
            
            if(!$this->moduliGrid[$codmodulo]) {
                $this->moduliGrid[$codmodulo] = $this->libDB->leggiBorDesautChiave($codmodulo, true);
            }
            
            $this->generaRowsPerGrid($codmodulo, $refresh);
            
            if(!$refresh || !$this->moduliGridBackup[$codmodulo]) {
                $this->moduliGridBackup[$codmodulo] = $this->moduliGrid[$codmodulo];
            }
            
            $data = $this->moduliGrid[$codmodulo];
            
            if($createGrid) {
                //MODELLO DELLA TABELLA
                $colModel = array(
                    array('name'=>'PROGAUT', 'title'=>'PROG', 'class'=>'{align:\'left\'}', 'width'=>'60'),
                    array('name'=>'DESCRI', 'title'=>'DESCRIZIONE', 'class'=>'{align:\'left\'}', 'width'=>'500'),
                    array('name'=>'AUT_HTML', 'title'=>'AUT', 'class'=>'{align:\'center\'}', 'width'=>'50'),
                    array('name'=>'DESCR_AUT', 'title'=>'DESCRIZIONE', 'class'=>'{align:\'left\'}', 'width'=>'350'),
                    array('name'=>'DES_ESTESA', 'title'=>'DESCRIZIONE ESTESA', 'class'=>'{align:\'left\'}', 'width'=>'800')
                );

                //METADATI DELLA TABELLA
                $metadata = array(
                    'caption'=>$codmodulo,
                    'shrinkToFit'=>false,
                    'width'=>1000,
                    'sortname'=>'PROGAUT',
                    'resizeToParent'=>true,
                    'rowNum'=>999,
                    'reloadOnResize'=>false
                );

                $model->setJqGridModel($colModel, $metadata);
                $model->setJqGridDataArray($data);

                $model->render();

                $this->gridCaricate[$codmodulo] = true;
            }
        }
    }
    
    private function generaRowsPerGrid($codmodulo, $refresh = false) {
        $filtri = array();
        $filtri['CODUTE'] = $this->utente;
        $filtri['CODMODULO'] = $codmodulo;
        
        if(!$refresh || (!$this->moduliGrid[$codmodulo][0]['AUT'] && $this->moduliGrid[$codmodulo][0]['AUT'] !== '')) {
            $filtri = array();
            $filtri['CODMODULO'] = $codmodulo;
            switch ($this->tipoRicerca) {
                case 1:
                    $filtri['CODUTE'] = $this->utente;
                    $record_autorizzazioni = $this->libDB->leggiBorAutute($filtri, false);
                    break;
                case 2:
                    $filtri['KRUOLO'] = $this->ruolo;
                    $record_autorizzazioni = $this->libDB->leggiBorAutruo($filtri, false);
                    break;
            }
        }

        foreach ($this->moduliGrid[$codmodulo] as $key => $record) {
            $prog = str_pad($record['PROGAUT'], 3, '0', STR_PAD_LEFT);
            $fieldName = 'AUTUTE_' . $prog;
            
            if(!array_key_exists('AUT', $record)) {
                $aut = $record_autorizzazioni[$fieldName];
                $this->moduliGrid[$codmodulo][$key]['AUT'] = $aut;
            } else {
                $aut = $record['AUT'];
            }
                    
            // AUT SELECT
            $options = array();
            // Valore default
            $option = array();
            $option['value'] = '';
            $option['selected'] = false;
            array_push($options, $option);
            
            foreach ($record as $field => $value) {
                if(!(strpos($field, 'AUTUTE') === FALSE) && trim($value)) {
                    $option = array();
                    $option['value'] = $value;
                    if($aut == $value) {
                        $option['selected'] = true;
                    }
                    array_push($options, $option);
                }
            }
            $component = array(
                'id' => 'AUT_' . $codmodulo,
                'rowKey' => $prog,
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'onChangeEvent' => true,
                'options' => $options,
                'properties' => array('style' => 'width:35px')
            );
            $this->moduliGrid[$codmodulo][$key]['AUT_HTML'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            $descr_aut = $this->getDescrizioneAutorizzazione($aut);
            $this->moduliGrid[$codmodulo][$key]['DESCR_AUT'] = $descr_aut;
        }
    }
    
    private function getDescrizioneAutorizzazione($aut) {
        $descrizione = '';
        switch($aut) {
            case '':
                $descrizione = 'Non autorizzato';
                break;
            case 'L':
                $descrizione = 'Solo Lettura';
                break;
            case 'G':
                $descrizione = 'Gestione';
                break;
            case 'C':
                $descrizione = 'Gestione e Cancellazione';
                break;
            case 'M':
                $descrizione = 'Gestione Massiva';
                break;
            case 'P':
                $descrizione = 'No Gestione altro soggetto';
                break;
            default:
                $descrizione = $aut;
                break;
        }
        if($codmodulo == 'FES' && $aut == 'P') {
            $descrizione = 'Gestione e Cancellazione solo proprie';
        }
        
        return $descrizione;
    }
    
    private function onChangeAut($codmodulo, $prog, $value) {
        $keyModificata = null;
        foreach ($this->moduliGrid[$codmodulo] as $key => $modulo) {
            if($modulo['PROGAUT'] == $prog) {
                $keyModificata = $key;
                break;
            }
        }
        
        if($keyModificata !== null) {
            $this->moduliGrid[$codmodulo][$keyModificata]['AUT'] = $value;
            
            // Aggiorna la descrizione dell'autorizzazione
            $descrizione = $this->getDescrizioneAutorizzazione($value);
            $this->moduliGrid[$codmodulo][$keyModificata]['DESCR_AUT'] = $descrizione;
            
            $this->generaGridPerModulo($codmodulo[0], $codmodulo, true);
        }
        
        Out::show($this->nameForm . '_btnApplica');
        Out::show($this->nameForm . '_btnOk');
    }
    
    private function cambiaTutteAutorizzazioni($aut = '') {
        $auts = array();
        if($aut) {
            switch($aut) {
                case 'L':
                    $auts = array('L');
                    break;
                case 'G':
                    $auts = array('L', 'G');
                    break;
                case 'C':
                    $auts = array('L', 'G', 'C');
                    break;
            }
        }

        $moduli = $this->moduli[$this->areaCorrente];
        foreach ($moduli as $keyModulo => $modulo) {
            $moduloCorrente = $modulo['CODMODULO'];
            $this->onClickModulo($this->areaCorrente, $moduloCorrente, true, false);

            foreach ($this->moduliGrid[$moduloCorrente] as $keyModuloGrid => $moduloGrid) {
                if(!empty($moduloGrid)) {
                    // Se l'autorizzazione che si vuole assegnare non è '' 
                    // bisogna vedere se quel campo supporta l'autorizzazione selezionata
                    if($aut) {
                        
                        foreach ($moduloGrid as $field => $value) {
                            if(!(strpos($field, 'AUTUTE') === FALSE) && trim($value)) {
                                if(in_array($value, $auts)) {
                                    $this->moduliGrid[$moduloCorrente][$keyModuloGrid]['AUT'] = $value;
                                }
                            }
                        }
                    } else {
                        $this->moduliGrid[$moduloCorrente][$keyModuloGrid]['AUT'] = $aut;
                    }                     
                }
            }
        }
        
        Out::show($this->nameForm . '_btnApplica');
        Out::show($this->nameForm . '_btnOk');
        
        $this->gridCaricate = array();
        Out::tabSelect($this->nameForm . '_divTabArea_'. $this->areaCorrente, $this->nameForm . '_divTabPane_' . $this->areaCorrente . '_' . $this->moduloCorrente);
        $this->onClickModulo($this->areaCorrente, $this->moduloCorrente, true);
    }
    
    private function salvaModifiche() {
        $errore = false;
        $countInsertUpdate = 0;
        $MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        $messaggio = 'Aggiornamento autorizzazioni';
        switch($this->tipoRicerca) {
            case 1:
                $tabella = 'BOR_AUTUTE';
                break;
            case 2:
                $tabella = 'BOR_AUTRUO';
                break;
        }
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tabella), false, false);
        
        // Salva solo i record modificati
        foreach ($this->moduliGrid as $keyModulo => $modulo) {
            foreach ($modulo as $keyRec => $record) {
                $newValue = $record['AUT'];
                $oldValue = $this->moduliGridBackup[$keyModulo][$keyRec]['AUT'];
                if($newValue <> $oldValue) {
                    // Aggiorna su DB il record con il nuovo valore
                    $prog = str_pad($record['PROGAUT'], 3, '0', STR_PAD_LEFT);
                    $fieldName = 'AUTUTE_' . $prog;
                    
                    $filtri = array();
                    switch($this->tipoRicerca) {
                        case 1:
                            $filtri['CODUTE'] = $this->utente;
                            break;
                        case 2:
                            $filtri['KRUOLO'] = $this->ruolo;
                            break;
                    }
                    $filtri['CODMODULO'] = $keyModulo;
                    
                    try {
                        $record = $this->libDB_GENERIC->leggiGeneric($tabella, $filtri, false);
                        if($record) {
                            $record[$fieldName] = $newValue;
                            $modelService->updateRecord($MAIN_DB, $tabella, $record, $messaggio);
                            $countInsertUpdate++;
                        } else {
                            switch($this->tipoRicerca) {
                                case 1:
                                    $record['CODUTE'] = $this->utente;
                                    break;
                                case 2:
                                    $record['KRUOLO'] = $this->ruolo;
                                    break;
                            }
                            $record[$fieldName] = $newValue;
                            $record['CODMODULO'] = $keyModulo;
                            $modelService->insertRecord($MAIN_DB, $tabella, $record, $messaggio);
                            $countInsertUpdate++;
                        }
                        
                    } catch(ItaException $e) {
                        $errore = true;
                        Out::msgStop('ATTENZIONE', 'Errore nel salvataggio! Messaggio di errore : ' . $e->getNativeErroreDesc());
                    }
                }
            }
        }
        
        if(!$errore) {
            Out::hide($this->nameForm . '_btnApplica');
            Out::hide($this->nameForm . '_btnOk');
            
            if($countInsertUpdate > 0) {
                $cwbAuthHelper = new cwbAuthHelper();

                switch($this->tipoRicerca) {
                    case 1:
                        Out::disableField($this->nameForm . '_CODUTE');

                        // Reset delle autorizzazioni in cache
                        $cwbAuthHelper->clearAuthCache($this->utente);
                        break;
                    case 2:
                        Out::disableField($this->nameForm . '_KRUOLO');

                        // Reset delle autorizzazioni in cache
                        $cwbAuthHelper->clearAuthCache();
                        break;
                }
                
                $this->moduliGridBackup = $this->moduliGrid;
                Out::msgInfo('INFO', 'Salvataggio effettuato correttamente!');
            } else {
                Out::msgInfo('INFO', 'Nessuna modifica effettuata.');
            }
        }
    }
    
    private function duplicaAutorizzazioni() {
        $model = cwbLib::apriFinestra('cwbDuplicaAutorizzazioni', $this->nameFormOrig, '', '', array(), $this->nameForm);
        $model->setTipoRicerca($this->tipoRicerca);

        switch ($this->tipoRicerca) {
            case 1:
                if($this->detailView) {
                    $model->setUtente($this->utente);
                } else {
                    $utente = $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'];;
                    $model->setUtente($utente);
                }
                break;
            case 2:
                if($this->detailView) {
                    $model->setRuolo($this->ruolo);
                } else {
                    $ruolo = $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'];;
                    $model->setRuolo($ruolo);
                }
                break;
        }

        $model->initializeForm();
    }
    
    private function stampaAutorizzazioniUtentiRuoli() {
        $recordSelezionati = array();
        
        foreach ($this->selectedValues as $key => $value) {
            array_push($recordSelezionati, $key);
        }
        
        if(count($recordSelezionati) > 0) {
            
            
            $model = cwbLib::apriApp('cwbStampaAutorizzazioni', $this->nameFormOrig, '', '', array('SELEZIONATI' => $recordSelezionati), $this->nameForm);
            $model->setTipo($this->tipoRicerca);
            $model->setListaUtentiRuoli($recordSelezionati);
            $model->loadForm();
            
        } else {
            Out::msgStop('ATTENZIONE!', 'Selezionare almeno un record.');
        }
        
    }
}

?>