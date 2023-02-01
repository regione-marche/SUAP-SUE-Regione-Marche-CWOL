<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCheckInput.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/authenticators/cwbAuthenticatorFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';
//include_once ITA_BASE_PATH . '/apps/Pratiche/praWorkflowHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthFilters.php';
/**
 *
 * Superclasse gestione model Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbBpaGenModel extends itaFrontControllerCW {
    const GOTO_SEARCH = 1;
    const GOTO_LIST = 2;
    const GOTO_NEW = 3;
    const GOTO_DETAIL = 4;
    const GOTO_NONE = 5;
    
    const XLSX_RAW = 0;
    const XLSX_AUTO = 1;
    const XLSX_DEFINED = 2;
    const XLSX_MODEL = 3;
    const XLSX_CHOOSE = 4;

    protected $current_index;               // chiave dell'indice del dettaglio attualmente aperto
    protected $CURRENT_RECORD;              // Array che contiene il record corrente
    protected $RECORD_INFO;                 // Array che contiene le informazioni sul record per audit
    protected $PK;                          // Array chiavi primaria
    protected $ALIAS;                       // Alias finestra (per istanze multiple)
    protected $LOCK;                        // Array che identifica un lock logico
    protected $libDB;                       // Libreria Database    
    protected $gridFilters;                 // Array che contiene i filtri sulla jqGrid
    protected $detailView;                  // Indica se sono in dettaglio (il current record va calcolato in maniera diversa in base a se mi trovo in dettaglio(da formdata) oppure in elenca (da riga selezionata))
    protected $filtriFissi;                 // Array di filtri fissi    
    protected $hasSequence;                 // true se la tabella ha la sequence          
    protected $modelData;                   // Dati da passare al ModelService per validazione/salvataggio
    protected $noCrud;                      // indica se la pagina deve gestire o meno le operazioni di crud
    protected $isWizard;                    // indica se la form viene usata all'interno di un wizard
    protected $SQL;                         // Stringa SQL
    protected $masterRecord;                // Record principale (per master-detail)
    protected $flagSearch;                  // Indica se la finestra è stata chiamata in ricerca
    protected $externalParams;              // Array che contiene i parametri esterni    
    protected $searchOpenElenco;            // Indica se in fase di search la finestra deve presentare subito l'elenco
    protected $authenticator;               // Oggetto Authenticator   
    protected $printStrategy;               // Print Strategy (Omnis Report/Jasper)
    protected $operationsData;              // Operazioni (U/D, la insert non va specificata, se è senza pk inserisco diretto) effettuate sulle tabelle relazionate (array('nameForm' => array('relationTableName' => array('operation' => 'I/U/D','guuid' => 'xxx', 'pks'=> array('key'=>'value'))))
    protected $operationMapping;            // contiene i riferimenti con il data su $operationsData
    protected $skipAuth;                    // se true salta il controllo sulle autorizzazioni
    protected $omnisReportName;             // Nome report Omnis
    protected $openDetailFlag;              // Bool, indica se deve segnalare o meno l'apertura di una pagina di dettaglio.
    protected $closeOnYearChange;           // Bool, indica se la pagina deve chiudersi al cambio dell'anno contabile.
    protected $actionAfterNew;              // Prende una costante GOTO_*, indica cosa fare dopo aver creato un nuovo elemento.
    protected $actionAfterModify;           // Prende una costante GOTO_*, indica cosa fare dopo aver modificato un elemento.
    protected $actionAfterDelete;           // Prende una costante GOTO_*, indica cosa fare dopo aver eliminato un elemento.
//    protected $showOperationMessage;
    protected $apriDettaglioIndex;
    protected $returnData;
    protected $elencaAutoAudit;
    protected $elencaAutoFlagDis;
    protected $selectedValues;
    protected $fakeMultiselect;             // Se attivo e la multiselect è attiva non permette la selezione multipla ma viene usato solo per mostrare la checkbox della riga selezionata.
    protected $breakEvent;                  // Se impostato a true, interrompe l'esecuzione di parseEvent
    protected $xlsxRenderType;              // Indica come renderizzare di default l'xlsx della tabella, prende i valori delle costanti XLSX_*
    protected $xlsxPageDescription;         // Descrizione della pagina da mostrare nel configuratore xlsx
    protected $xlsxDefaultModel;            // ID sulla tabella BGE_EXCELT che indica il modello di default da usare per la pagina.
    protected $modelParametriRicerca;
    protected $skipValidateDB;              // Indica se saltare la chiamata al validatore prima di modifiche (operazioni su DB)
    protected $skipValidateMemory;          // Indica se saltare la chiamata al validatore prima di modifiche (operazioni in memoria)
    protected $checkAnnoContabile;          // Indica se va fatto il check delle autorizzazioni sull'anno contabile
    protected $workflowHelper;              // Helper per l'integrazione con il workflow
    protected $authFiltersHelper;           // Helper per le autorizzazioni avanzato
    protected $authFiltersSource;           // Origine su cui controllare le autorizzazioni (cwbAuthFilters::SOURCE_*
    protected $openPageAuthCheck;           // array dei filtri da usare con cwbAuthFilters da applicare all'apertura della pagina
    protected $elencaAuthCheck;             // array dei filtri da usare con cwbAuthFilters da applicare all'elenca
    protected $dettaglioAuthCheck;          // array dei filtri da usare con cwbAuthFilters da applicare all'apertura del dettaglio
    
    function postItaFrontControllerCostruct() {
        if (!$this->libDB) {
            // se vuota inizializzo la lib corretta per questo nameform
            try {
                $libName = cwbModelHelper::libNameByModelName($this->nameForm);

                $importLib = ITA_BASE_PATH . '/' . cwbModelHelper::moduleByModelName($this->nameForm) . '/' . $libName . '.class.php';

                if (file_exists($importLib)) {
                    include_once $importLib;
                    $this->libDB = new $libName();
                }
                else{
                    include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
                    $this->libDB = new cwbLibDB_GENERIC();
                }
            } catch (Exception $exc) {
                
            }
        }

        // Reperisce valori dalla sessione
        $this->current_index = cwbParGen::getFormSessionVar($this->nameForm, '_current_index');
        $this->flagSearch = cwbParGen::getFormSessionVar($this->nameForm, '_flagSearch');
        $this->detailView = cwbParGen::getFormSessionVar($this->nameForm, '_detailView');
        $this->apriDettaglioIndex = cwbParGen::getFormSessionVar($this->nameForm, '_apriDettaglioIndex');
        if ($this->apriDettaglioIndex == '') {
            unset($this->apriDettaglioIndex);
        }
        $this->returnData = cwbParGen::getFormSessionVar($this->nameForm, '_returnData');
        $this->externalParams = cwbParGen::getFormSessionVar($this->nameForm, '_externalParams');
        if ($this->externalParams == '') {
            $this->externalParams = array();
        }
        $this->stopEvent = cwbParGen::getFormSessionVar($this->nameForm, '_stopEvent');
        $this->operationsData = cwbParGen::getFormSessionVar($this->nameForm, '_operationsData');
        if ($this->operationsData == '') {
            $this->operationsData = array();
        }
        if ($this->operationsData[$this->nameForm] === null) {
            $this->operationsData[$this->nameForm] = array();
        }
        $this->selectedValues = cwbParGen::getFormSessionVar($this->nameForm, '_selectedValues');
        if ($this->selectedValues == '') {
            $this->selectedValues = array();
        }
        $this->skipValidateDB = cwbParGen::getFormSessionVar($this->nameForm, '_skipValidateDB');
        $this->skipValidateMemory = cwbParGen::getFormSessionVar($this->nameForm, '_skipValidateMemory');

        $this->preConstruct();

        // Connessione al database
        $this->connettiDB();

        // Ricava nome tabella e chiavi primarie
        if (!$this->noCrud) {
//            if (!isSet($this->TABLE_NAME)) {
//                $this->TABLE_NAME = cwbModelHelper::tableNameByModelName($this->nameFormOrig);
//            }
            if (!isSet($this->PK)) {
                $this->PK = $this->getModelService()->newTableDef($this->TABLE_NAME, $this->MAIN_DB)->getPks(true);
            }
            // se non ho setttato la table view la imposto come tableName
            if (!$this->getTABLE_VIEW()) {
                $this->TABLE_VIEW = $this->TABLE_NAME;
            }
        }
        
        $this->initWorkflowHelper();

        $this->initPrintStrategy();

        $this->initOmnisReportName();

        $this->initAuthFiltersHelper();
        
        // Istanzia authenticator        
        $this->initAuthenticator();

        // Effettua controllo sulle autorizzazioni
        $this->checkAutor();
        
        $this->postConstruct();
    }
    
    protected function initBehaviours($module) {
        switch($module){
            case 'cwf':
                $this->checkAnnoContabile = true;
                $this->authFiltersSource = cwbAuthFilters::SOURCE_BILANCIO;
                break;
        }
    }
    
    protected function initAuthFiltersHelper(){
        if(!isSet($this->authFiltersHelper)){
            $this->authFiltersHelper = new cwbAuthFilters();
        }
        if($this->authFiltersHelper->getContext() == null && isSet($this->authFiltersSource)){
            $this->authFiltersHelper->setContext($this->authFiltersSource);
        }
        if($this->authFiltersHelper->getFieldsPage() == null && isSet($this->openPageAuthCheck)){
            $this->authFiltersHelper->setFieldsPage($this->openPageAuthCheck);
        }
        if($this->authFiltersHelper->getFieldsElenca() == null && isSet($this->elencaAuthCheck)){
            $this->authFiltersHelper->setFieldsElenca($this->elencaAuthCheck);
        }
        if($this->authFiltersHelper->getFieldsDettaglio() == null && isSet($this->dettaglioAuthCheck)){
            $this->authFiltersHelper->setFieldsDettaglio($this->dettaglioAuthCheck);
        }
        if($this->authFiltersHelper->getFiltersTable() == null && isSet($this->TABLE_VIEW)){
            $this->authFiltersHelper->setFiltersTable($this->TABLE_VIEW);
        }
        elseif($this->authFiltersHelper->getFiltersTable() == null && isSet($this->TABLE_NAME)){
            $this->authFiltersHelper->setFiltersTable($this->TABLE_NAME);
        }
    }
    
    protected function checkAnnoContabile(){
        if($this->close === true || $this->checkAnnoContabile === false){
            return;
        }
        if($this->checkAnnoContabile === true){
            $authHelper = new cwfAuthHelper();
            if($authHelper->checkAuthAnnoContabile() === false){
                Out::msgStop("Esercizio contabile non valido", "Il bilancio dell'anno ".cwbParGen::getAnnoContabile()." non è stato ancora approvato, usare un altro esercizio contabile");
                $this->close();
            }
        }
    }

    protected function initAuthenticator() {
        if ($this->skipAuth != true) {
            $autorLevel = cwbParGen::getSessionVar($this->nameForm);
            $autorLevel = isSet($autorLevel['_autorLevel']) ? $autorLevel['_autorLevel'] : null;
            $this->authenticator = cwbAuthenticatorFactory::getAuthenticator($this->nameFormOrig, $this->prepareAuthenticatorParams($autorLevel));
        }
    }

    protected function prepareAuthenticatorParams($autorLevel) {
        return array(
            'username' => cwbParGen::getSessionVar('nomeUtente'),
            'modulo' => $this->AUTOR_MODULO,
            'num' => $this->AUTOR_NUMERO,
            'level' => $autorLevel,
            'lib' => $this->authFiltersHelper
        );
    }
    
    protected function initWorkflowHelper(){
        if(!isSet($this->workflowHelper)){
//            $this->workflowHelper = new praWorkflowHelper();
        }
    }
    
    protected function initPrintStrategy() {
        $this->printStrategy = cwbBpaGenHelper::PRINT_STRATEGY_OMNIS;
    }

    protected function initOmnisReportName() {
        $this->omnisReportName = 'R_' . $this->TABLE_NAME;
    }

    function __destruct() {
        $this->preDestruct();
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_current_index', $this->current_index);
            cwbParGen::setFormSessionVar($this->nameForm, '_flagSearch', $this->flagSearch);
            cwbParGen::setFormSessionVar($this->nameForm, '_detailView', $this->detailView);
            if(isSet($this->apriDettaglioIndex)) cwbParGen::setFormSessionVar($this->nameForm, '_apriDettaglioIndex', $this->apriDettaglioIndex);
            cwbParGen::setFormSessionVar($this->nameForm, '_returnData', $this->returnData);
            cwbParGen::setFormSessionVar($this->nameForm, '_externalParams', $this->externalParams);
            if ($this->skipAuth != true) {
                cwbParGen::setFormSessionVar($this->nameForm, '_autorLevel', $this->authenticator->getLevel());
            }
            cwbParGen::setFormSessionVar($this->nameForm, '_stopEvent', $this->stopEvent);
            cwbParGen::setFormSessionVar($this->nameForm, '_operationsData', $this->operationsData);
            cwbParGen::setFormSessionVar($this->nameForm, '_selectedValues', $this->selectedValues);
            cwbParGen::setFormSessionVar($this->nameForm, '_skipValidateDB', $this->skipValidateDB);
            cwbParGen::setFormSessionVar($this->nameForm, '_skipValidateMemory', $this->skipValidateMemory);
        }
        $this->postDestruct();
    }

    public function parseEvent() {
        $this->preParseEvent();

        // Se nel metodo preParseEvent è stata impostata la variabile breakEvent, termina l'esecuzione dell'evento
        if ($this->getBreakEvent()) {
            return;
        }

        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if(!$this->close){
                    $this->checkAnnoContabile();
                    $this->initGridPager();
                    if (!$this->flagSearch) {
                        $this->caricaParametriRicerca();
                    }
                    $this->aggiungiFlagCloseOnYearChange();
                    if ($this->masterRecord != null) {
                        $this->elenca(true);
                        $this->setVisRisultato();
                    } else {
                        $this->initExternalFilterData();
                        $this->apriForm();
                        $this->initExternalFilterHtml();
                    }
                }
                break;
            case 'viewRowInline':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->viewMode = true;
                        $this->dettaglio($this->formData['rowid']);
                        break;
                }
                break;
            case 'dbClickRow':
                if ($this->flagSearch) {
                    $this->ricercaEsterna($this->formData['rowid']);
                    break;
                }
            case 'editRowInline':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->viewMode = false;
                        $this->dettaglio($this->formData['rowid']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->nuovo();
                        break;
                }
                break;
            case 'delRowInline':
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->cancella();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->nuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->aggiorna();
                        break;
                    case $this->nameForm . '_Cancella':
                        $this->cancella();
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $this->confermaCancella();
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->elenca(true);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->altraRicerca();
                        break;
                    case $this->nameForm . '_Torna':
                        $this->tornaAElenco();
                        break;
                    case $this->nameForm . '_CaricaParametriRicerca':
                        $this->caricaParametriRicerca();
                        break;
                    case $this->nameForm . '_SalvaParametriRicerca':
                        $this->salvaParametriRicerca();
                        break;
                    case $this->nameForm . '_ResetParametriRicerca':
                        $this->resetParametriRicerca();
                        break;
                    // aggiunta possibilità di confermare i warning 
                    case $this->nameForm . '_ConfermaWarning':
                        if ($_POST["OPERATION"] == itaModelService::OPERATION_INSERT) {
                            $this->aggiungi(false);
                        } else if ($_POST["OPERATION"] == itaModelService::OPERATION_UPDATE) {
                            $this->aggiorna(false);
                        } else if ($_POST["OPERATION"] == itaModelService::OPERATION_DELETE) {
                            $this->confermaCancella(false);
                        }

                        break;
                    case $this->nameForm . '_' . $this->GRID_NAME . '_CLEAN_SELECT':
                        $this->resetSelected();
                        break;
                    case $this->nameForm . '_showWorkflow':
                        $this->showWorkflow();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->elenca(false);
                        break;
                }
                break;
            case 'onSelectCheckRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->updateSelectedRow($_POST['rowid']);
                        break;
                }
                break;
            case 'onSelectCheckAll':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->updateSelectAll($_POST['rowids']);
                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->stampaElenco();
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->exportXLS();
                        break;
                }
                break;
        }
        $this->customParseEvent();
    }

//    private function initExternalFilter() {
//        $elements = $this->helper->getSearchElement();
//
//        foreach ($this->externalParams as $key => $value) {
//            // se  è un array 
//            if (is_array($value)) {
//                if (!empty($value["HTMLELEMENT"])) {
//                    $key = $value["HTMLELEMENT"];
//                }
//                if (isSet($value["PERMANENTE"]) && ($value["PERMANENTE"] === NULL || $value["PERMANENTE"] === TRUE)) {
//                    $this->settingProportiesExternalfilter($elements, $key, $value["VALORE"], true);
//                } else {
//                    $this->settingProportiesExternalfilter($elements, $key, $value["VALORE"], false);
//                }
//            } else {
//                // di default il filtro viene creato permanete 
//                $this->settingProportiesExternalfilter($elements, $key, $value, true);
//            }
//        }
//
//        $this->postExternalFilter();
//    }

    private function initExternalFilterData() {
        if (is_array($this->externalParams)) {
            foreach ($this->externalParams as $key => $value) {
                if (is_array($value)) {
                    $htmlElement = (!empty($value['HTMLELEMENT']) ? $value['HTMLELEMENT'] : $key);
                    $valore = (isSet($value['VALORE']) ? $value['VALORE'] : null);
                } else {
                    $htmlElement = $key;
                    $valore = $value;
                }

                if (isSet($valore)) {
                    $this->setExternalFilterData($valore, $htmlElement);
                }
            }

            $this->postExternalFilter();
        }
    }

    private function initExternalFilterHtml() {
        if (is_array($this->externalParams)) {
            foreach ($this->externalParams as $key => $value) {
                if (is_array($value)) {
                    $htmlElement = (!empty($value['HTMLELEMENT']) ? $value['HTMLELEMENT'] : $key);
                    $permanente = (isSet($value['PERMANENTE']) && ($value['PERMANENTE'] === NULL || $value['PERMANENTE'] === TRUE));
                    $valore = (isSet($value['VALORE']) ? $value['VALORE'] : null);
                } else {
                    $htmlElement = $key;
                    $permanente = true;
                    $valore = $value;
                }

                if (isSet($valore)) {
                    $this->setExternalFilterHtml($valore, $htmlElement, $permanente);
                }
            }
        }
    }

    private function setExternalFilterData($valore, $htmlElement) {
        if(!preg_match('/^GROUP\(([A-Za-z][A-Za-z0-9]*)\)/', $htmlElement)){
            $this->formData[$this->nameForm . '_' . $htmlElement] = $valore;
            $_POST[$this->nameForm . '_' . $htmlElement] = $valore;
            Out::valore($this->nameForm . '_' . $htmlElement, $valore);
            
            if($this->elencaAutoAudit === true && $htmlElement == 'CODUTE'){
                Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'CODUTE', $valore);
            }
            if($this->elencaAutoFlagDis === true && $htmlElement == 'FLAG_DIS'){
                if($valore === 0){
                    $flag_dis = 'A';
                }
                elseif($valore === 1){
                    $flag_dis = 'D';
                }
                else{
                    $flag_dis = '';
                }
                Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'FLAG_DIS', $flag_dis);
            }
        }
    }

    private function setExternalFilterHtml($valore, $htmlElement, $permanente) {
        if(!preg_match('/^GROUP\(([A-Za-z][A-Za-z0-9]*)\)/', $htmlElement)){
            Out::valore($this->nameForm . '_' . $htmlElement, $valore);
            if ($permanente === true) {
                Out::disableContainerFields($this->nameForm . "_" . $htmlElement . "_field");
                Out::disableField($this->nameForm . "_" . $htmlElement);
            }
        }
    }

//    private function settingProportiesExternalfilter($elements, $key, $value, $disable) {
//        Out::valore($this->nameForm . "_" . $key, $value);
//        $this->formData[$this->nameForm . "_" . $key] = $value;
//        if ($elements[$key] !== null) {
//            switch ($elements[$key]["tipo_nome"]) {
//                case "ita-edit-lookup":
//                    if ($disable) {
//                        Out::disableContainerFields($this->nameForm . "_" . $key . "_field");
//                    }
//                    $this->decodificaFiltriPermanenti($key, $value);
//                    break;
//                default:
//                    if ($disable) {
//                        Out::disableField($this->nameForm . "_" . $key);
//                    }
//                    break;
//            }
//        }
//    }

    /**
     * Da implementare sulle clasi sottostanti. 
     * Serve per gestire le combo quando vengono passati degli externalPameter con filtro permanente.
     * In questo caso deve essere gestito su ogni pagina con il campo Hidden
     */
    protected function postExternalFilter() {
        
    }

    /*
     * Decodifica i lookup. Da implementare sulle clasi sottostanti 
     * quando vengono passati esternamente dei filtri permanenti
     */

    protected function decodificaFiltriPermanenti($nomeCampo, $valore) {
        
    }

    public function close() {
        $this->preClose();
        
        $this->sbloccaChiave();
        $this->rimuoviFlagCloseOnYearChange();
        $this->rimuoviFlagDettaglioAperto();
        $this->cleanOperationData();
        $this->cleanOperationMapping();
        
        parent::close();
        
        $this->postClose();
    }

    /**
     * Effettua connessione al database di Cityware
     */
    protected function connettiDB() {
        try {
            $this->preConnettiDB();
            $this->MAIN_DB = $this->libDB->getCitywareDB();
            $this->postConnettiDB();
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    /**
     * Apertura finestra
     */
    protected function apriForm() {
        $this->preApriForm();
        $this->manageAutoFields();
        if ($this->searchOpenElenco) {
            $this->elenca(true);
            $this->setVisRisultato();
        } elseif (isSet($this->apriDettaglioIndex)) {
            if ($this->apriDettaglioIndex == 'new') {
                $this->nuovo();
                $this->setVisNuovo();
            } else {
                $this->dettaglio($this->apriDettaglioIndex);
                $this->setVisDettaglio();
            }
        } else {
            $this->setVisRicerca();
        }
        $this->postApriForm();
    }

    /**
     * Lock tabella per chiave
     * @param any $currentRecord Record completo da bloccare
     */
    protected function bloccaChiave($currentRecord) {
        if ($this->viewMode) {
            return;
        }

        try {
            //Calcolo chiave primaria del record a partire da BDI_INDICI
            $modelService = $this->getModelService();
            $pkString = $modelService->calcPkString($this->MAIN_DB, $this->TABLE_NAME, $currentRecord);
            
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

    /**
     * Sblocca record precedentemente bloccato
     */
    protected function sbloccaChiave() {
        if ($this->viewMode) {
            return;
        }

        try {
            $this->LOCK = cwbParGen::getFormSessionVar($this->nameFormOrig, 'lock' . $this->TABLE_NAME);
            if ($this->LOCK) {
                $result = $this->getModelService()->unlockRecord($this->LOCK['lockID'], $this->MAIN_DB);
                if($result['status'] != 0){
                    throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore sblocco record');
                }
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore sblocco record", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore sblocco record", $e->getMessage(), '600', '600');
        }
    }
    
    protected function aggiungiFlagCloseOnYearChange(){
        if ($this->closeOnYearChange === true) {
            cwbParGen::addOpenDetailFlag($this->nameForm, $this->nameFormOrig);
        }
    }

    protected function rimuoviFlagCloseOnYearChange(){
        if ($this->closeOnYearChange === true) {
            cwbParGen::removeOpenDetailFlag($this->nameForm, $this->nameFormOrig);
        }
    }
    /**
     * Segna in sessione che il dettaglio di un dato model è stato aperto
     */
    protected function aggiungiFlagDettaglioAperto() {
        if ($this->closeOnYearChange !== true && $this->openDetailFlag === true ) {
            cwbParGen::addOpenDetailFlag($this->nameForm, $this->nameFormOrig);
        }
    }

    /**
     * Rimuove dalla sessione la flag che indica che il model è stato aperto su un dettaglio
     */
    protected function rimuoviFlagDettaglioAperto() {
        if ($this->closeOnYearChange !== true && $this->openDetailFlag === true) {
            cwbParGen::removeOpenDetailFlag($this->nameForm, $this->nameFormOrig);
        }
    }

    /**
     * Prepara la finestra per inserimento di un nuovo record
     */
    protected function nuovo() {
        $this->viewMode = false;
        Out::restoreContainerFields($this->nameForm . "_divGestione");

        $this->preNuovo();
        TableView::disableEvents($this->nameForm . '_' . $this->GRID_NAME);
        $this->setVisNuovo();
        $this->pulisciCampi();
        $this->controlliAuditDettaglio(itaModelService::OPERATION_INSERT);
        $this->postNuovo();
        $this->resetSelected();
        $this->aggiungiFlagDettaglioAperto();
    }

    private function caricaRecordPrincipale($data = null) {
        $this->modelData = new itaModelServiceData(new cwbModelHelper());
        $this->modelData->addMainRecord($this->TABLE_NAME, ($data == null ? $this->CURRENT_RECORD : $data));
    }

    /**
     * Inserimenti di un nuovo record sul database
     */
    protected function aggiungi($validate = true) {
        try {
            $this->preAggiungi();
            if (isSet($this->apriDettaglioIndex) && $this->returnData) {
                if($validate == false || $this->skipValidateMemory === true){
                    cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $_POST[$this->nameForm . '_' . $this->TABLE_NAME], $this->nameForm, $this->returnNameForm);
                }
                else{
                    $this->formDataToCurrentRecord(itaModelService::OPERATION_INSERT);
                    $this->caricaRecordPrincipale();
                    if($this->valida(itaModelService::OPERATION_INSERT, $msgValida, true)){
                        cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $_POST[$this->nameForm . '_' . $this->TABLE_NAME], $this->nameForm, $this->returnNameForm);
                    }
                    else{
                        if(!empty($msgValida)){
                            $this->showValidationMessage($msgValida, itaModelService::OPERATION_INSERT);
                        }
                        return false;
                    }
                }
                return true;
            } else {
                $this->formDataToCurrentRecord(itaModelService::OPERATION_INSERT);
                $this->caricaRecordPrincipale();
                if ($this->manageDataRelation(itaModelService::OPERATION_INSERT) && ($validate == false || $this->skipValidateDB === true || $this->valida(itaModelService::OPERATION_INSERT, $msgValida))) {
                    $this->eseguiOperazione(itaModelService::OPERATION_INSERT, true);
                    $this->cleanOperationData();
                } else {
                    if(!empty($msgValida)){
                        $this->showValidationMessage($msgValida, itaModelService::OPERATION_INSERT);
                    }

                    // cancello solo le operazioni 1 a 1 perché vengono inserite al click su aggiungi/aggiorna e se 
                    // c'è errore di validazione ricliccando aggiungi/aggiorna la aggiunge doppia. le many to one invece vengono aggiunte
                    // tramite evento su grid e quindi devono rimanere (non si duplicano cliccando aggiungi)
                    if ($this->operationsData[$this->nameForm] && $this->operationMapping) {
                        foreach ($this->operationMapping as $key => $mapping) {
                            if ($mapping['tipoRelazione'] == itaModelServiceData::RELATION_TYPE_ONE_TO_ONE) {
                                unset($this->operationsData[$this->nameForm][$key]);
                            }
                        }
                    }
                    return false;
                }
            }
            return true;
        } catch (ItaException $e) {
            Out::msgStop("Errore di Inserimento.", $e->getNativeErroreDesc());
            return false;
        } catch (Exception $e) {
            Out::msgStop("Errore di Inserimento.", $e->getMessage());
            return false;
        }
    }

    /**
     * Aggiornamento del record selezionato sul database
     */
    protected function aggiorna($validate = true) {
        try {
            $this->preAggiorna();
            if (isSet($this->apriDettaglioIndex) && $this->returnData) {
                if($validate == false || $this->skipValidateMemory === true){
                    cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $_POST[$this->nameForm . '_' . $this->TABLE_NAME], $this->nameForm, $this->returnNameForm);
                }
                else{
                    $this->formDataToCurrentRecord(itaModelService::OPERATION_UPDATE);
                    $this->caricaRecordPrincipale();
                    $this->caricaFigliCascata();
                    $this->manageDataRelation(itaModelService::OPERATION_UPDATE);
                    if($this->valida(itaModelService::OPERATION_UPDATE, $msgValida, true)){
                        cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $_POST[$this->nameForm . '_' . $this->TABLE_NAME], $this->nameForm, $this->returnNameForm);
                    }
                    else{
                        if(!empty($msgValida)){
                            $this->showValidationMessage($msgValida, itaModelService::OPERATION_UPDATE);
                        }
                        return false;
                    }
                }
                return true;
            } else {
                $this->formDataToCurrentRecord(itaModelService::OPERATION_UPDATE);
                $this->caricaRecordPrincipale();
                $this->caricaFigliCascata();
                $this->manageDataRelation(itaModelService::OPERATION_UPDATE);
                if (($validate == false || $this->skipValidateDB === true || $this->valida(itaModelService::OPERATION_UPDATE, $msgValida))
                ) {
                    if($this->eseguiOperazione(itaModelService::OPERATION_UPDATE, true)){
                        $this->cleanOperationData();
                    }
                    else{
                        return false;
                    }
                } else {
                    if(!empty($msgValida)){
                        $this->showValidationMessage($msgValida, itaModelService::OPERATION_UPDATE);
                    }
                    return false;
                }
            }
            if(isSet($this->apriDettaglioIndex)){
                $this->close();
            }
            return true;
        } catch (ItaException $e) {
            Out::msgStop("Errore aggiornamento record", $e->getCompleteErrorMessage(), '600', '600');
            return false;
        } catch (Exception $e) {
            Out::msgStop("Errore aggiornamento record", $e->getMessage(), '600', '600');
            return false;
        }
    }

    protected function caricaFigliCascata() {
        if (!isSet($this->modelData)) {
            $this->modelData = new itaModelServiceData(new cwbModelHelper());
        }
        $this->modelData->addChildrenRecords($this->TABLE_NAME, $this->caricaCascata());
    }

    protected function caricaCascata() {
        return array();
    }

    protected function showValidationMessage($msgValida, $operation) {
        if ($msgValida[itaModelValidator::LEVEL_ERROR]) {
//            Out::msgStop("Errore di validazione", $msgValida);
            Out::msgStop("Errore di validazione", $msgValida[itaModelValidator::LEVEL_ERROR]);
        } else {
            Out::msgQuestion("Attenzione alle segnalazioni", "Controllare le segnalazioni: " . $msgValida[itaModelValidator::LEVEL_WARNING] . " Proseguire con l'operazione ?", array('Annulla' => array('id' => $this->nameForm . '_AnnullaWarning', 'model' => $this->nameForm),
                'Conferma' => array('id' => $this->nameForm . '_ConfermaWarning', 'model' => $this->nameForm,
                    "metaData" => "extraData:{OPERATION:$operation}")
            ));
        }
    }

    //function privata richiamata da warning per effettuare il salvataggio 
    private function eseguiOperazione($operaration) {
        if ($operaration == itaModelService::OPERATION_UPDATE) {
            $esito = $this->salva($operaration);
            if($esito){
                $this->sbloccaChiave();
                $this->postAggiorna($esito);

                switch ($this->actionAfterModify) {
                    case self::GOTO_SEARCH:
                        $this->rimuoviFlagDettaglioAperto();
                        $this->altraRicerca();
                        break;
                    case self::GOTO_NEW:
                        $this->nuovo();
                        break;
                    case self::GOTO_DETAIL:
                        $this->dettaglio($this->CURRENT_RECORD[$this->PK]);
                        break;
                    case self::GOTO_NONE:
                        break;
                    case self::GOTO_LIST:
                    default:
                        $this->rimuoviFlagDettaglioAperto();
                        $this->elenca(true);
                        break;
                }
                Out::msgBlock('desktop', 3000, false, 'Record modificato correttamente');
                return true;
            }
            else{
                $this->postAggiorna($esito);
                return false;
            }
        } else if ($operaration == itaModelService::OPERATION_INSERT) {
            $esito = $this->salva($operaration);
            if($esito){
                $this->pulisciCampi();
                $this->postAggiungi($esito);
                switch ($this->actionAfterNew) {
                    case self::GOTO_SEARCH:
                        $this->rimuoviFlagDettaglioAperto();
                        $this->altraRicerca();
                        break;
                    case self::GOTO_LIST:
                        $this->rimuoviFlagDettaglioAperto();
                        $this->elenca(true);
                        break;
                    case self::GOTO_DETAIL:
                        $this->dettaglio($this->CURRENT_RECORD[$this->PK]);
                        break;
                    case self::GOTO_NONE:
                        break;
                    case self::GOTO_NEW:
                    default:
                        $this->nuovo();
                        break;
                }
                Out::msgBlock('desktop', 3000, false, 'Record inserito correttamente');
                return true;
            }
            else{
                $this->postAggiungi($esito);
                return false;
            }
        } else if ($operaration == itaModelService::OPERATION_DELETE) {
            $this->recordInfo(itaModelService::OPERATION_DELETE, $this->modelData->getData());
            $esito = $this->deleteRecord($this->MAIN_DB, $this->TABLE_NAME, $this->modelData->getData(), $this->RECORD_INFO);
            if($esito){
                $this->sbloccaChiave();
                $this->postConfermaCancella($esito);
                $this->rimuoviFlagDettaglioAperto();
                switch ($this->actionAfterDelete) {
                    case self::GOTO_SEARCH:
                        $this->altraRicerca();
                        break;
                    case self::GOTO_NEW:
                        $this->nuovo();
                        break;
                    case self::GOTO_NONE:
                        break;
                    case self::GOTO_DETAIL:
                    case self::GOTO_LIST:
                    default:
                        $this->elenca(true);
                        break;
                }
                Out::msgBlock('desktop', 3000, false, 'Record cancellato correttamente');
                return true;
            }
            else{
                $this->postConfermaCancella($esito);
                return false;
            }
        }
    }

    /**
     * Effettua il salvataggio del record
     * Effettuare override per gestire l'eventuale salvataggio di altri modelli in transazione
     * @param char $tipoOperazione A=Aggiungi  M=Modifica
     */
    protected function salva($tipoOperazione) {
        $this->recordInfo($tipoOperazione, $this->modelData->getData());
        switch ($tipoOperazione) {
            case itaModelService::OPERATION_INSERT:
                return $this->insertRecord($this->MAIN_DB, $this->TABLE_NAME, $this->modelData->getData(), $this->RECORD_INFO);
                break;
            case itaModelService::OPERATION_UPDATE:
                return $this->updateRecord($this->MAIN_DB, $this->TABLE_NAME, $this->modelData->getData(), $this->RECORD_INFO);
                break;
            default:
        }
    }

    /**
     * Richiesta cancellazione del record selezionato
     */
    protected function cancella() {
        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
        ));
    }

    /**
     * Conferma cancellazione su database
     */
    protected function confermaCancella($validate = true, $index=null) {
        try {
            $this->preConfermaCancella();
            if (isSet($this->apriDettaglioIndex) && $this->returnData) {
                if($validate == false || $this->skipValidateMemory === true){
                    cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, 'DELETE', $this->nameForm, $this->returnNameForm);
                }
                else{
                    $this->caricaRecordPrincipale();
                    
                    if($this->authFiltersHelper->getFieldsDettaglio() !== null && isSet($this->CURRENT_RECORD)){
                        $this->authenticator->authenticateDettaglio($this->CURRENT_RECORD, $this->authFiltersHelper->getFieldsDettaglio());
                        if($this->skipAuth != true && !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_DELETE)){
                            Out::msgStop('Errore', 'L\'utente '.cwbParGen::getUtente().' non è autorizzato a cancellare il record');
                            return;
                        }
                    }
                    
                    if($this->valida(itaModelService::OPERATION_DELETE, $msgValida, true)){
                        cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, 'DELETE', $this->nameForm, $this->returnNameForm);
                    }
                    else{
                        if(!empty($msgValida)){
                            $this->showValidationMessage($msgValida, itaModelService::OPERATION_UPDATE);
                        }
                        return false;
                    }
                }
                return true;
            } else {
                if(!empty($index)){
                    //Se mi viene passato l'indice uso quanto mi viene passato
                    $this->loadCurrentRecord($index);
                }
                elseif($this->getDetailView()){
                    // se sono in dettaglio prendo il current record dalla post
                    $this->formDataToCurrentRecord(itaModelService::OPERATION_DELETE);
                }else{
                    // se sono in griglia prendo il current record dalla riga selezionata
                    $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
                }
                
                if($this->authFiltersHelper->getFieldsDettaglio() !== null && isSet($this->CURRENT_RECORD)){
                    $this->authenticator->authenticateDettaglio($this->CURRENT_RECORD, $this->authFiltersHelper->getFieldsDettaglio());
                    if($this->skipAuth != true && !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_DELETE)){
                        Out::msgStop('Errore', 'L\'utente '.cwbParGen::getUtente().' non è autorizzato a cancellare il record');
                        return;
                    }
                }

                $this->bloccaChiave($this->CURRENT_RECORD);
                
                if ($this->LOCK['status'] != 0) {
                    if (array_key_exists("message", $this->LOCK)) {
                        $message = $this->LOCK["message"];
                    } else {
                        $lockerManager = itaLockerFactory::getLockerManager();
                        $lockInfo = $lockerManager->lockedBy($this->MAIN_DB, $this->TABLE_NAME, $index);
                        $message = 'Record bloccato in modifica dall\'operatore '.$lockInfo['utente'];
                    }
                    Out::msgStop('Errore', $message);
                    return;
                }
                
                $this->caricaRecordPrincipale();
                
                $this->loadRelationsHook();

                // svuoto le operazioni fatte e carico n delete operation quanti sono i record su db
                $this->cleanOperationData();
                $relationToDelete = $this->getDataRelation(itaModelService::OPERATION_DELETE);

                if (is_array($relationToDelete)) {
                    foreach ($relationToDelete as $alias => $data) {
                        if ($this->operationMapping[$alias]["tipoRelazione"] == itaModelServiceData::RELATION_TYPE_NONE) {
                            continue;
                        }
                        if ($this->operationMapping[$alias]["tableName"]) {
                            $tablename = $this->operationMapping[$alias]["tableName"];
                        } else {
                            $tablename = $alias;
                        }

                        $pks = $this->getModelService()->getPks($this->MAIN_DB, $tablename);

                        foreach ($data as $record) {
                            $pksValue = array();

                            foreach ($pks as $pk) {
                                if (empty($record[$pk])) {
                                    continue 3;
                                }
                                $pksValue[$pk] = $record[$pk];
                            }

                            if (!empty($pksValue)) {
                                $this->addDeleteOperation($tablename, $pksValue, $alias);
                            }
                        }
                    }
                }

                if ($this->manageDataRelation(itaModelService::OPERATION_DELETE) && ($validate == false || $this->skipValidateDB === true || $this->valida(itaModelService::OPERATION_DELETE, $msgValida))) {
                    $this->eseguiOperazione(itaModelService::OPERATION_DELETE, true);
                    $this->cleanOperationData();    
                } else {
                    if(!empty($msgValida)){
                        $this->showValidationMessage($msgValida, itaModelService::OPERATION_DELETE);
                    }
                    return false;
                }

                if(isSet($this->apriDettaglioIndex)){
                    $this->close();
                }
            }
            return true;
        } catch (ItaException $e) {
            Out::msgStop("Errore", $e->getNativeErroreDesc());
            return false;
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }

    }

    protected function cleanOperationData() {
        if ($this->operationsData[$this->nameForm]) {
            unset($this->operationsData[$this->nameForm]);
        }
    }
    
    protected function cleanOperationMapping(){
        if ($this->operationMapping[$this->nameForm]) {
            unset($this->operationMapping[$this->nameForm]);
        }
    }

    /**
     * Resetta i parametri di ricerca     
     */
    protected function resetParametriRicerca() {
        $generator = new itaGenerator();
        $elements = $generator->getElementsFromForm($this->nameFormOrig, array('workSpace', 'divRicerca'), array('checkbox','file','lookupinput','password','radio','select','text','textarea'), array(), true);

        foreach ($elements as $value) {
            $nome = $this->nameForm . '_' . $value;
            Out::valore($nome, "");
        }

        $this->postResetParametriRicerca();
    }

    /**
     * Salva i parametri di ricerca    
     */
    protected function salvaParametriRicerca() {
        $this->helper->salvaParametriRicerca($this->modelParametriRicerca);
        $this->postSalvaParametriRicerca();
    }

    /**
     * Ricarica i parametri di ricerca    
     */
    protected function caricaParametriRicerca() {
        $parametri = $this->helper->caricaParametriRicerca($this->modelParametriRicerca);
        $this->postCaricaParametriRicerca($parametri);
    }

    /**
     * Imposta il record corrente dai dati presenti sulla form
     * (Per casi particolari, dove c'è la necessità di impostare manualmente dei valori, 
     *  effettuare override del metodo)
     */
    protected function formDataToCurrentRecord($operation = null) {
        $this->CURRENT_RECORD = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];

        $this->elaboraCurrentRecord($operation);
    }

    /**
     * Visualizzazione dettaglio del record selezionato
     * @param string $index Chiave univoca del record da visualizzare
     */
    protected function dettaglio($index) {
        try {
            $this->setCurrentIndex($index);
            // Effettua il caricamento del dettaglio
            // Questa operazione viene saltata nel caso delle finestre di gestione del singolo record
            if ($index != null) {
                $this->loadCurrentRecord($index);
            }
            
            if($this->authFiltersHelper->getFieldsDettaglio() !== null && isSet($this->CURRENT_RECORD)){
                $this->authenticator->authenticateDettaglio($this->CURRENT_RECORD, $this->authFiltersHelper->getFieldsDettaglio());
                if($this->skipAuth != true && !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_READ)){
                    Out::msgStop('Errore', 'L\'utente '.cwbParGen::getUtente().' non è autorizzato a visualizzare il record');
                    return;
                }
                if($this->skipAuth != true && $this->viewMode != true &&
                        $this->authenticator->isActionAllowed(itaAuthenticator::ACTION_WRITE) &&
                        !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_WRITE)){
                    Out::msgStop('Errore', 'L\'utente '.cwbParGen::getUtente().' non è autorizzato a modificare il record');
                    return;
                }
            }
            
            $this->bloccaChiave($this->CURRENT_RECORD);
            if (!$this->viewMode &&
                $this->skipAuth != true &&
                ($this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_DELETE) ||
                 $this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_WRITE))) {
                Out::restoreContainerFields($this->nameForm . "_divGestione");
                //Out::disableContainerFields($this->nameForm . "_divAudit");

                if ($this->LOCK['status'] != 0) {
                    if (array_key_exists("message", $this->LOCK)) {
                        $message = $this->LOCK["message"];
                    } else {
                        $lockerManager = itaLockerFactory::getLockerManager();
                        $lockInfo = $lockerManager->lockedBy($this->MAIN_DB, $this->TABLE_NAME, $index);
                        $message = 'Record bloccato in modifica dall\'operatore '.$lockInfo['utente'];
                    }
                    Out::msgStop('Errore', $message);
                    return;
                }
            }
            $this->aggiungiFlagDettaglioAperto();
            $this->preDettaglio($index);


            $this->recordInfo(itaModelService::OPERATION_OPENRECORD, $this->CURRENT_RECORD);
            $this->openRecord($this->MAIN_DB, $this->TABLE_NAME, $this->RECORD_INFO);

            // Refresh array
            // Consente di avere i dati aggiornati sia nella request corrente che in quella successiva
            $_POST[$this->nameForm . '_' . $this->TABLE_NAME] = array();
            $_POST[$this->nameForm . '_' . $this->TABLE_NAME] = array_merge($_POST[$this->nameForm . '_' . $this->TABLE_NAME], $this->CURRENT_RECORD);

            $this->setVisDettaglio();
            TableView::disableEvents($this->nameForm . '_' . $this->GRID_NAME);
            $this->controlliAuditDettaglio(itaModelService::OPERATION_UPDATE);

            $this->postDettaglio($index);
            Out::valori($this->CURRENT_RECORD, $this->nameForm . '_' . $this->TABLE_NAME);
            // In visualizzazione, disabilita tutti i controlli
            if ($this->viewMode ||
                ($this->skipAuth != true &&
                !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_DELETE) &&
                !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_WRITE))) {
                Out::disableContainerFields($this->nameForm . "_divGestione");
            }
            $this->resetSelected();
        } catch (ItaException $e) {
            Out::msgStop('Errore', $e->getNativeErroreDesc());
            $this->setVisRisultato();
        } catch (Exception $e) {
            Out::msgStop('Errore', $e->getMessage());
            $this->setVisRisultato();
        }
    }

    /**
     * Restituisce dati al chiamante (quando la finestra è chiamata in ricerca da un'altra)
     * @param string $index Indice
     */
    protected function ricercaEsterna($index) {
        try {
            $this->preRicercaEsterna($index);
            $this->loadCurrentRecord($index);
            cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $this->CURRENT_RECORD, $this->nameForm, $this->returnNameForm);
            $this->close(); // chiamo la close a mano perche il metodo closeDialog dentro ricercaEsterna non chiama l'evento close
        } catch (ItaException $e) {
            Out::msgStop('Errore', $e->getNativeErroreDesc());
            //$this->close();
        } catch (Exception $e) {
            Out::msgStop('Errore', $e->getMessage());
            //$this->close();
        }
    }

    /**
     * Carica record corrente
     * @param string $index Chiave della tabella
     */
    protected function loadCurrentRecord($index) {
        try {
            $sqlParams = array();
            $this->sqlDettaglio($index, $sqlParams);
            $this->CURRENT_RECORD = ItaDB::DBQuery($this->MAIN_DB, $this->SQL, false, $sqlParams);
        } catch (ItaException $e) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore caricamento record corrente: " . $e->getCompleteErrorMessage());
        } catch (Exception $e) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore caricamento record corrente: " . $e->getMessage());
        }
    }

    /**
     * Imposta la finestra in modalità ricerca
     */
    protected function altraRicerca() {
        $this->sbloccaChiave();
        $this->rimuoviFlagDettaglioAperto();
        $this->preAltraRicerca();
        $this->cleanOperationData();
        $this->setVisRicerca();
        $this->clearFiltriGrid();
        $this->postAltraRicerca();
        $this->resetSelected();
    }

    /**
     * Ritorna a elenco (corrisponde all'azione 'Annulla' in Cityware)
     */
    protected function tornaAElenco() {
        $this->sbloccaChiave();
        $this->rimuoviFlagDettaglioAperto();

        if (isSet($this->apriDettaglioIndex)) {
            $this->close();
        } else {
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            $this->preTornaElenco();
            $this->cleanOperationData();
            $this->setVisRisultato();
            $this->postTornaElenco();
        }
    }

    /**
     * Stampa report (utilizzando la strategy definita nella classe)
     */
    protected function stampaElenco() {
        $this->preStampaElenco();
        $this->helper->stampa($this->printStrategy, $this->nameFormOrig, $this->initParametriStampaElenco());
        $this->postStampaElenco();
    }

    /**
     * Esportazione dati su foglio elettronico
     */
    protected function exportXLS() {
        $this->preExportXLS();
        
        $xlsxWriter = new itaXlsxWriter($this->MAIN_DB);
        switch($this->xlsxRenderType){
            case self::XLSX_DEFINED:
                $this->generateXlsx($xlsxWriter);
                $xlsxWriter->createCustom();
                break;
            case self::XLSX_MODEL:
                $sqlParams = array();
                $this->sqlElencaXlsx($sqlParams);
                
                $xlsxWriter->setDataFromSQL($this->SQL, $sqlParams);
                $xlsxWriter->setRenderFieldsFromModel($this->xlsxDefaultModel);
                $xlsxWriter->createCustom();
                break;
            case self::XLSX_CHOOSE:
                $fields = $this->getXlsxFields();
                $model = cwbLib::apriFinestra('utiXlsxCustomizer', $this->nameForm, '', '', array(), $this->nameFormOrig);
                $model->initPage($fields, cwbLib::getCitywareConnectionName(), '', $this->nameFormOrig, $this->xlsxPageDescription, $this->xlsxDefaultModel);
                $model->parseEvent();
                return;
            case self::XLSX_AUTO:
                $sqlParams = array();        
                $this->sqlElencaXlsx($sqlParams);
                $data = ItaDB::DBQuery($this->MAIN_DB, $this->SQL, true, $sqlParams);

                $data = $this->elaboraRecordsXlsx($data);

                $header = array();
                $appRoute = App::getPath('formRoute.' . substr($this->nameFormOrig, 0, 3));
                $htmlModel = simplexml_load_file(ITA_BASE_PATH.'/'.$appRoute.'/'.$this->nameFormOrig.'.xml');
                $cols = $htmlModel->xpath('//engineElement[@el_nome=\''.$this->GRID_NAME.'\']/engineElement[@tipo_tag=\'th\']');
                foreach($cols as $col){
                    $colAttributes = $col->attributes();
                    $colName = preg_replace('/(^th)/', '', (string)$colAttributes->el_nome);
                    if(isSet($col->label)){
                        $label = preg_replace("/(\r?\n){2,}/", " ", Html2Text\Html2Text::convert((string)$col->label));
                    }
                    $header[$colName] = $label;
                }

                foreach($data as &$row){
                    foreach($row as $key=>$field){
                        if(!in_array($key, array_keys($header))){
                            unset($row[$key]);
                        }
                        else{
                            $row[$key] = preg_replace("/(\r?\n){2,}/", " ", Html2Text\Html2Text::convert($field));
                        }
                    }
                }

                foreach($header as $key=>$value){
                    $header[$key] = array('name'=>$value);
                }

                $xlsxWriter->setDataFromArray($data);
                $xlsxWriter->setRenderFieldsMetadata($header);
                $xlsxWriter->createCustom();
                break;
            case self::XLSX_RAW:
            default:
                $sqlParams = array();
                $this->sqlElencaXlsx($sqlParams);
                
                $xlsxWriter->setDataFromSQL($this->SQL, $sqlParams);
                $xlsxWriter->createRaw();
                break;
        }
        $this->postExportXLS(); 
        
        $filename = $this->nameForm . time() . rand(0,1000) . '.xlsx';
        $tempPath = itaLib::getAppsTempPath() . "/" . $filename;
        $xlsxWriter->writeToFile($tempPath);
        Out::openDocument(utiDownload::getOTR($filename, $tempPath, true));
    }
    
    /**
     * Funzione che genera l'xlsx a partire dal model restituito.
     * @param <int> $model chiave sulla tabella BGE_EXCELT
     */
    public function printXlsxFromModel($model){
        $xlsxWriter = new itaXlsxWriter($this->MAIN_DB);
        
        $sqlParams = array();
        $this->sqlElencaXlsx($sqlParams);
                
        $xlsxWriter->setDataFromSQL($this->SQL, $sqlParams);
        $xlsxWriter->setRenderFieldsMetadata($model);
        $xlsxWriter->createCustom();
        
        $filename = $this->nameForm . time() . rand(0,1000) . '.xlsx';
        $tempPath = itaLib::getAppsTempPath() . "/" . $filename;
        $xlsxWriter->writeToFile($tempPath);
        Out::openDocument(utiDownload::getOTR($filename, $tempPath, true));
    }
    
    /**
     * Ritorna l'array dei campi disponibili. L'array può essere un array semplice o un array di array:
     * @return <array> Ogni elemento può essere:
     *                  <string> Contiene il nome del campo della query
     *                  oppure
     *                  <array> La chiave è il nome del campo, l'array può contenere i seguenti valori:
     *                          'name'=>Descrizione testuale del campo
     *                          'width'=>Larghezza definita come in excel della colonna
     *                          'sheet'=>Foglio in cui viene inserita la colonna (parte da 0)
     *                          'format'=>Formato del campo, una costante del tipo itaXlsxWriter::FORMAT_*
     *                          'headerStyle'=>Stile dell'header, array contenente i campi con chiave itaXlsxWriter::STYLE_*
     *                          'fieldStyle'=>Stile del campo, array contenente i campi con chiave itaXlsxWriter::STYLE_*
     *                          'calculated'=>Formula di calcolo del campo (mutuamente esclusiva con callback)
     *                          'callback'=>Funzione di callback per il calcolo del campo
     */
    protected function getXlsxFields(){
        $sqlParams = array();        
        $this->sqlElencaXlsx($sqlParams);
        return array_keys(ItaDB::DBQuery($this->MAIN_DB, $this->SQL, false, $sqlParams));
    }
    
    /**
     * Funzione da estendere per definire la metodologia custom di creazione dell'xlsx.
     * All'interno della funzione è necessario: -assegnare i dati ($xlsxWriter->setDataFromSQL o $xlsxWriter->setDataFromArray)
     *                                          -(opzionale) settare i metadati ($xlsx->setRenderFieldsMetadata)
     * @param type $xlsxWriter
     */
    protected function generateXlsx(&$xlsxWriter){
        
    }

    /**
     * 
     * @return typeInizializza parametri per stampa elenco
     */
    protected function initParametriStampaElenco() {
        $parameters = array();
        $sqlParams = array();
        $this->sqlElenca($sqlParams);
        $resolvedQuery = $this->resolveNamedParameterQuery($this->SQL, $sqlParams);
        switch ($this->printStrategy) {
            case cwbBpaGenHelper::PRINT_STRATEGY_OMNIS:
                $parameters[0] = $this->omnisReportName;
                $parameters[1] = $this->TABLE_NAME;
                $parameters[2] = cwbBpaGenHelper::PRINT_FORMAT_OMNIS_PDF;
                $parameters[3] = $resolvedQuery;
                $parameters[4] = cwbParGen::getNomeUte(); //è il campo BOR_UTENTI.NOMEUTE relativo all'utente che ha effettuato la login
                break;
            case cwbBpaGenHelper::PRINT_STRATEGY_JASPER:
                $parameters = array("Sql" => $this->SQL);
                break;
        }
        return $parameters;
    }

    /*
     * Metodo utilizzato solo nelle stampe.
     * Serve per risolvere un'istruzione SQL con dei parametri.
     * (Sia Omnis che Jasper utilizzano attualmente un'istruzione sql come fonte dati)
     */

    private function resolveNamedParameterQuery($sql, $sqlParams) {
        $sqlResolved = $sql;
        foreach ($sqlParams as $p) {
            $toFind = ':' . $p['name'] . ' ';
            $toReplace = str_replace("'", "''", $p['value']);
            if ($p['type'] == PDO::PARAM_STR) {
                $toReplace = "'$toReplace'";
            }
            $toReplace .= ' ';

            $sqlResolved = str_replace($toFind, $toReplace, $sqlResolved);
        }
        return $sqlResolved;
    }

    /**
     * Imposta visualizzazione dei componenti presenti sulla pagina
     * @param boolean $divGestione Flag Div Gestione visibile
     * @param boolean $divRisultato Flag Div Risultato visibile
     * @param boolean $divRicerca Flag Div Ricerca visibile
     * @param boolean $nuovo Flag pulsante nuovo visibile
     * @param boolean $aggiungi Flag pulsante aggiungi visibile
     * @param boolean $aggiorna Flag pulsante aggiorna visibile
     * @param boolean $elenca Flag pulsante elenca visibile
     * @param boolean $cancella Flag pulsante cancella visibile
     * @param boolean $altraRicerca Flag pulsante altraRicerca visibile
     * @param boolean $torna Flag pulsante torna visibile
     */
    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna) {
        $this->setDetailView($divGestione);
        if(!$divGestione || $nuovo){
            $this->setCurrentIndex(null);
        }
        $divGestione ? Out::show($this->nameForm . '_divGestione') : Out::hide($this->nameForm . '_divGestione');
        $divRisultato ? Out::show($this->nameForm . '_divRisultato') : Out::hide($this->nameForm . '_divRisultato');
        $divRicerca ? Out::show($this->nameForm . '_divRicerca') : Out::hide($this->nameForm . '_divRicerca');

        $nuovo ? Out::show($this->nameForm . '_Nuovo') : Out::hide($this->nameForm . '_Nuovo');
        $aggiungi ? Out::show($this->nameForm . '_Aggiungi') : Out::hide($this->nameForm . '_Aggiungi');
        $aggiorna ? Out::show($this->nameForm . '_Aggiorna') : Out::hide($this->nameForm . '_Aggiorna');
        $elenca ? Out::show($this->nameForm . '_Elenca') : Out::hide($this->nameForm . '_Elenca');
        $cancella ? Out::show($this->nameForm . '_Cancella') : Out::hide($this->nameForm . '_Cancella');
        $altraRicerca ? Out::show($this->nameForm . '_AltraRicerca') : Out::hide($this->nameForm . '_AltraRicerca');
        $torna ? Out::show($this->nameForm . '_Torna') : Out::hide($this->nameForm . '_Torna');
        $divRicerca ? Out::show($this->nameForm . '_divParametriRicerca') : Out::hide($this->nameForm . '_divParametriRicerca');

        // controllo autorizzazioni
        $this->elaboraAutor();
        
        if($this->getExternalRefKey('WORKFLOW') != null){
            Out::show($this->nameForm . '_showWorkflow');
        }
        else{
            Out::hide($this->nameForm . '_showWorkflow');
        }
    }

    /**
     * Imposta visualizzazione su Risultato
     */
    protected function setVisRisultato() {
        $this->setVisControlli(false, true, false, true, false, false, false, false, true, false);
    }

    /**
     * Imposta visualizzazione su aggiunta nuovo record
     */
    protected function setVisNuovo() {
        if (isSet($this->apriDettaglioIndex)) {
            $this->setVisControlli(true, false, false, false, true, false, false, false, false, true);
        } else {
            $this->setVisControlli(true, false, false, false, true, false, false, false, true, false);
        }
    }

    /**
     * Imposta visualizzazione su Dettaglio
     */
    protected function setVisDettaglio() {
        if ($this->viewMode) {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, false, false, false, false, false, true);
            } else {
                $this->setVisControlli(true, false, false, false, false, false, false, false, true, true);
            }
        } else {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true);
            } else {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, true, true);
            }
        }
    }

    /**
     * Imposta visualizzazione su Ricerca
     */
    protected function setVisRicerca() {
        $this->setVisControlli(false, false, true, true, false, false, true, false, false, false);
    }

    /**
     * Validazione record prima di effettuare operazioni su database
     */
    protected function valida($tipoOperazione, &$msg, $inMemory=false) {
        $msg = '';

        $validationInfo = array();

        $modifiedData = $this->modelData->getData();
        foreach ($modifiedData as $current) {
            $this->validaRecord($current['tableName'], $current['tableData'], $validationInfo, $msg, '', $tipoOperazione, $current['keyMapping'], $modifiedData, $inMemory);
        }

        return count($validationInfo) === 0;
    }

    private function validaRecord($tableName, $data, &$validationInfo, &$msg, $line = 0, $tipoOperazione, $keyMapping = array(), $modifiedData = null, $inMemory=false) {
        if (isSet($data['data'])) {
            $toValidate = $data['data'];
        } else {
            $toValidate = $data;
        }

        if (is_array($toValidate[0])) {
            $riga = 1;
            foreach ($toValidate as $record) {
                $tipoOperazione = (isSet($record['operation']) ? $record['operation'] : $tipoOperazione);
                $this->validaRecord($tableName, $record, $validationInfo, $msg, $riga++, $tipoOperazione, $keyMapping, $modifiedData, $inMemory);
            }
        } else {
            $oldCurrentRecord = array();
            if (itaModelService::OPERATION_UPDATE === $tipoOperazione && !$this->noCrud) {
                $oldCurrentRecord = $this->getOldCurrentRecord($tableName, $toValidate);
            }

            if (!is_array($validationInfo)) {
                $validationInfo = array();
            }
            if(itaModelHelper::tableNameByModelName($this->getModelService()->getModelName()) == $tableName){
                $modelService = $this->getModelService();
            }
            else{
                $modelService = itaModelServiceFactory::newModelService(itaModelHelper::modelNameByTableName($tableName, $this->nameFormOrig));
            }
            $validationInfo = array_merge($validationInfo, $modelService->validate($this->MAIN_DB, $tableName, $toValidate, $tipoOperazione, $oldCurrentRecord, $keyMapping, $modifiedData, $inMemory));
            if (count($validationInfo) > 0) {
                $msg = $this->helper->getValidationMessage($validationInfo, null, $this->TABLE_NAME);
            }
        }
    }

    protected function getOldCurrentRecord($tableName, $toValidate) {
        $oldCurrentRecord = $this->getModelService()->getByPks($this->MAIN_DB, $tableName, $toValidate);

        return $oldCurrentRecord;
    }

    /**
     * Imposta controlli su campi di audit
     * @param string $tipoOperazione Tipo operazione
     */
    protected function controlliAuditDettaglio($tipoOperazione) {
        // Mostra/nasconde audit in funzione del tipo di operazione
        $tipoOperazione === itaModelService::OPERATION_INSERT ? Out::hide($this->nameForm . '_divAudit') : Out::show($this->nameForm . '_divAudit');

//        // Inserimento
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[CODUTEINS]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[DATAINSER]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[TIMEINSER]', 'readonly', '0');
//
//        // Ultima modifica        
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[CODUTE]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[DATAOPER]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[TIMEOPER]', 'readonly', '0');
    }

    /**
     * Pulisce campi dettaglio
     */
    protected function pulisciCampi() {
        $generator = new itaGenerator();
        $elements = $generator->getElementsFromForm($this->nameFormOrig, array('workSpace', 'divGestione'), array('checkbox','file','lookupinput','password','radio','select','text','textarea'), array(), true);

        foreach ($elements as $value) {
            $nome = $this->nameForm . '_' . $value;
            Out::valore($nome, "");
        }
        
//        $rec = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];
//        if (is_array($rec)) {
//            foreach ($rec as $key => $value) {
//                $nome = $this->nameForm . '_' . $this->TABLE_NAME . "[$key]";
//                Out::valore($nome, '');
//            }
//        }

        $this->postPulisciCampi();
    }

    /**
     * Effettua integrazione dei filtri su tab ricerca con quelli impostati nella grid
     * @param array $filtri Filtri impostati nella finestra di ricerca
     */
    protected function compilaFiltri(&$filtri) {
        $this->helper->compilaFiltri($this->gridFilters, $filtri);

        //Gestione filtri esterni permanenti
        if(is_array($this->externalParams)){
            foreach ($this->externalParams as $field => $value) {
                if (!is_array($value) || !array_key_exists('VALORE', $value)) {
                    $filtri[$field] = $value;
                } elseif ($value['PERMANENTE'] === true || $value['PERMANENTE'] === null) {
                    $filtri[$field] = $value['VALORE'];
                }
            }
        }
    }

    /**
     * Controllo autorizzazioni, L - Sola Lettura, G - Tutto tranne cancellazione, C - Tutto abilitato
     */
    protected function checkAutor() {
        $auth = $this->authFiltersHelper->getFieldsPage();
        if ($this->skipAuth == true || $this->authenticator->getLevel() !== null || empty($auth)) {
            return;
        }

        if ($this->authenticator->missingAuthentication()) {
            $authHelper = new cwfAuthHelper();
            $auth = $authHelper->getAuthUtente();
            if($auth['TYPE'] === cwfAuthHelper::GESAUT_RUOLO && cwbParGen::getRuolo() === null){
                cwbLib::apriFinestra('menCwbConfig', '', '', '', array(), '', 'menCwbConfig')->parseEvent();
                Out::msgStop("Ruolo non selezionato","L'utente corrente usa le autorizzazioni da ruolo e nessun ruolo è stato selezionato.<br>Selezionare un ruolo.");
            }
            else{
                Out::msgStop("Autorizzazioni mancanti", $this->authenticator->getMissingAuthenticationMessage());
            }
            $this->close();
        }
    }

    /**
     * Imposta controlli in funzione dell'autorizzazione
     */
    protected function elaboraAutor() {
        if ($this->skipAuth != true) {
            if ($this->skipAuth != true && $this->authenticator->isActionAllowed(itaAuthenticator::ACTION_READ) && !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_WRITE)) {
                // disabilito il tasto nuovo e cancella sia sulla griglia che sul dettaglio
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'EDITROW');
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'DELETEROW');
                Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_addGridRow");
                Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_editGridRow");
                Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_delGridRow");
                Out::hide($this->nameForm . '_Nuovo');
            }
            elseif ($this->skipAuth != true && $this->authenticator->isActionAllowed(itaAuthenticator::ACTION_READ) && !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_DELETE)) {
                // abilita il tasto nuovo e disabilita il tasto cancella sia sulla griglia che sul dettaglio
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'DELETEROW');
                Out::hide($this->nameForm . "_" . $this->GRID_NAME . "_delGridRow");
            }
            if($this->skipAuth != true && $this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_READ) && !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_WRITE)) {
                Out::hide($this->nameForm . '_Cancella');
                Out::hide($this->nameForm . '_Aggiungi');
                Out::hide($this->nameForm . '_Aggiorna');
            }
            elseif ($this->skipAuth != true && $this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_READ) && !$this->authenticator->isActionAllowedDettaglio(itaAuthenticator::ACTION_DELETE)) {
                Out::hide($this->nameForm . '_Cancella');
            }
            

        }
        $this->customElaboraAutor();
    }

    /**
     * Effettua i controlli specifici in funzione delle autorizzazioni custom
     */
    protected function customElaboraAutor() {
        
    }

    protected function recordInfo($operation, $data) {
        $this->RECORD_INFO = itaModelHelper::impostaRecordInfo($operation, $this->nameForm, $data);
    }

    protected function abilitaDuplica() {
        return $this->hasSequence;
    }

    protected function initModelService() {
        $this->setModelService(cwbModelServiceFactory::newModelService(itaModelHelper::modelNameByTableName($this->TABLE_NAME, $this->nameFormOrig)));
    }

    protected function clearFiltriGrid() {
        TableView::disableEvents($this->nameForm . '_' . $this->GRID_NAME);
//        TableView::clearToolbar($this->nameForm . '_' . $this->GRID_NAME);
        // se ho effettuato un ordinamento del campo specifico di una datatable rimane sporco il valore nella post 
        TableView::clearSortState($this->nameForm . '_' . $this->GRID_NAME);
        
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
        $this->manageAutoFields();
        Out::gridSetPage($this->nameForm, $this->GRID_NAME, 1);
    }

    /*
     * Helper da utilizzare nella sottoclase.aggiunge un operazione di delete su una tabella relazionata (solo per many-to-one/one-to-many)
     * il controllo delle chiavi valorizzate vieni fatto sul metodo private della superclasse 
     * quindi richiamare sempre questo metodo nella sottoclasse anche nel caso sto facendo una delete di un record non salvato sul db 
     * @param String $tableName Nome tabella
     * @param array $pks chiavi/chiave della tabella 
     * @param String $alias Alias da usare nel caso ci siano più relazioni sulla stessa tabella
     */

    public function addDeleteOperation($tableName, $pks, $alias = null) {
        $key = $alias ? $alias : $tableName;

        if ($this->initOperationArray(itaModelService::OPERATION_DELETE, $tableName, $pks, $alias)) {
            $this->operationsData[$this->nameForm][$key][itaModelService::OPERATION_DELETE][] = array('tableName' => $tableName, 'pksValue' => $pks);
        } else {
            foreach ($this->operationsData[$this->nameForm][$key] as $operation => $value) {
                if ($operation === itaModelService::OPERATION_INSERT) {
                    array_shift($this->operationsData[$this->nameForm][$key][$operation]);
                    break;
                }
            }
        }
    }

    /*
     * Helper da utilizzare nella sottoclase. Uggiunge un operazione di update su una tabella relazionata (solo per many-to-one/one-to-many)
     * il controllo delle chiavi valorizzate vieni fatto sul metodo private della superclasse 
     * quindi richiamare sempre questo metodo nella sottoclasse anche nel caso sto facendo una modifica di un record non salvato sul db 
     * @param String $tableName Nome tabella
     * @param array $pks chiavi/chiave della tabella (key=> xxx,value=>xxx)
     * @param String $alias Alias da usare nel caso ci siano più relazioni sulla stessa tabella
     * 
     */

    public function addUpdateOperation($tableName, $pks, $alias = null) {
        $key = $alias ? $alias : $tableName;

        if ($this->initOperationArray(itaModelService::OPERATION_UPDATE, $tableName, $pks, $alias)) {
            $this->operationsData[$this->nameForm][$key][itaModelService::OPERATION_UPDATE][] = array('tableName' => $tableName, 'pksValue' => $pks);
        }
    }

    /*
     * Helper da utilizzare nella sottoclase Aggiunge un operazione di inserert su una tabella relazionata (solo per many-to-one/one-to-many)
     * @param String $tableName Nome tabella
     * @param String $alias Alias da usare nel caso ci siano più relazioni sulla stessa tabella
     */

    public function addInsertOperation($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;

        if ($this->initOperationArray(itaModelService::OPERATION_INSERT, $tableName, null, $alias)) {
            $this->operationsData[$this->nameForm][$key][itaModelService::OPERATION_INSERT][] = array('tableName' => $tableName);
        }
    }

    private function initOperationArray($operation, $tableName, $pks, $alias = null) {
        $key = $alias ? $alias : $tableName;
        switch($operation){
            case itaModelService::OPERATION_INSERT:
                if (!$this->operationsData[$this->nameForm][$key]) {
                    $this->operationsData[$this->nameForm][$key] = array();
                }

                if (!$this->operationsData[$this->nameForm][$key][$operation]) {
                    $this->operationsData[$this->nameForm][$key][$operation] = array();
                }
                return true;
            case itaModelService::OPERATION_UPDATE:
                if(!isSet($this->CURRENT_RECORD) && isSet($this->current_index)){
                    $this->loadCurrentRecord($this->getCurrentIndex());
                }
                $currentRecords = $this->getDataRelation($operation);
                $currentRecords = $currentRecords[$key];
                
                foreach($currentRecords as $record){
                    foreach($pks as $k=>$v){
                        if($v != $record[$k]){
                            $record = null;
                            break;
                        }
                    }
                    if($record !== null){
                        break;
                    }
                }
                
                if($record !== null){
                    if (!$this->operationsData[$this->nameForm][$key]) {
                        $this->operationsData[$this->nameForm][$key] = array();
                    }

                    if (!$this->operationsData[$this->nameForm][$key][$operation]) {
                        $this->operationsData[$this->nameForm][$key][$operation] = array();
                    }
                    
                    foreach($this->operationsData[$this->nameForm][$key][$operation] as $v){
                        if($v['tableName'] == $tableName && $v['pksValue'] == $pks){
                            return false;
                        }
                    }
                    return true;
                }
                return false;
            case itaModelService::OPERATION_DELETE:
                if(!isSet($this->CURRENT_RECORD) && isSet($this->current_index)){
                    $this->loadCurrentRecord($this->getCurrentIndex());
                }
                $currentRecords = $this->getDataRelation($operation);
                $currentRecords = $currentRecords[$key];
                
                foreach($currentRecords as $record){
                    foreach($pks as $k=>$v){
                        if($v != $record[$k]){
                            $record = null;
                            break;
                        }
                    }
                    if($record !== null){
                        break;
                    }
                }
                
                if($record !== null){
                    if (!$this->operationsData[$this->nameForm][$key]) {
                        $this->operationsData[$this->nameForm][$key] = array();
                    }

                    if (!$this->operationsData[$this->nameForm][$key][$operation]) {
                        $this->operationsData[$this->nameForm][$key][$operation] = array();
                    }
                    return true;
                }
                return false;
        }
//        
//        if ($operation === itaModelService::OPERATION_INSERT || ($pks && count(array_filter($pks, function($var) {
//                            return !is_null($var);
//                        })) === count($pks))) {
//
//            $key = $alias ? $alias : $tableName;
//            if (!$this->operationsData[$this->nameForm][$key]) {
//                $this->operationsData[$this->nameForm][$key] = array();
//            }
//
//            if (!$this->operationsData[$this->nameForm][$key][$operation]) {
//                $this->operationsData[$this->nameForm][$key][$operation] = array();
//            }
//            return true;
//        } else {
//            return false;
//        }
    }

    /*
     * Aggiunge la descrizione di relazione 
     * @param String $tableName Nome tabella
     * @param array() $keyMapping array associativo key=chiave tabella principale => value=nome foreignKey 
     * @param int $tipoRelazione tipo relazione (usare itaModelServiceData es: itaModelServiceData::RELATION_TYPE_ONE_TO_MANY)
     * @param String $alias Alias da usare nel caso ci siano più relazioni sulla stessa tabella (deve combaciare con l'alias usato sui metodi addUpdateOperation etc..)
     */

    public function addDescribeRelation($tableName, $keyMapping, $tipoRelazione, $alias = null) {
        $key = $alias ? $alias : $tableName;

        $this->operationMapping[$key] = array(
            'tableName' => $tableName,
            'keyMapping' => $keyMapping,
            'tipoRelazione' => $tipoRelazione,
        );
    }

    /**
     * Utilizzare per caricare dati aggiuntivi da passare al service
     * @param int $tipoOperazione Tipo operazione del record principale(Insert/Update/delete)
     */
    protected function manageDataRelation($tipoOperazione) {
        if ($this->operationsData) {

            foreach ($this->operationsData[$this->nameForm] as $alias => $operations) {
                $tableData = array();
                if (!key_exists($alias, $this->operationMapping)) {
                    throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Operation mapping non definite");
                }

                $tableName = $this->operationMapping[$alias]['tableName'];
                $recordsData = $this->getDataRelationView($tableName, $alias);

                $countInsert = 0;
                foreach ($operations as $operationKey => $operationData) {
                    foreach ($operationData as $operation) {
                        if (key_exists('pksValue', $operation)) {
                            if ($operationKey === itaModelService::OPERATION_DELETE) {
                                // delete
                                $tableData[] = array("operation" => $operationKey,
                                    "data" => $operation['pksValue']);
                            } else {
                                // update                                
                                if ($recordsData) {
                                    foreach ($recordsData as $keyRec => $record) {
                                        $found = true;
                                        foreach ($operation['pksValue'] as $key => $keyValue) {
                                            if ($record[$key] != $keyValue) {
                                                $found = false;
                                                break;
                                            }
                                        }

                                        if ($found) {
                                            $tableData[] = array(
                                                "operation" => $operationKey,
                                                "data" => $record
                                            );
                                            unset($recordsData[$keyRec]);
                                            break;
                                        }
                                    }
                                }
                            }
                        } else {
                            // insert
                            $countInsert++;
                        }
                    }
                }

                $toInsert = array();
                $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);

                foreach ($recordsData as $value) {
                    // controllo l'oldRecord per capire se deve essere inserito
                    $oldCurrentRecord = $modelService->getByPks($this->MAIN_DB, $tableName, $value);
                        
                    if (!$oldCurrentRecord) {
                        $mainRecord = $this->modelData->getData();
                        $mainRecord = $mainRecord['CURRENT_RECORD']['tableData'];
                        foreach($this->operationMapping[$alias]['keyMapping'] as $from=>$to){
                            $value[$to] = $mainRecord[$from];
                        }
                        $toInsert[] = $value;
                    }
                }

                // $toInsert conterrà tutti i record che non sono presenti sul db.
                // Se la $countInsert è diversa da count($toInsert) c'è un errore.
                if ($countInsert != count($toInsert)) {
                    Out::msgStop("Errore", "Operazioni su tabella relazionata incongruenti");
                    return false;
                }

                foreach ($toInsert as $record) {
                    $tableData[] = array("operation" => itaModelService::OPERATION_INSERT,
                        "data" => $record);
                }

                if ($tableData) {
                    if ($this->operationMapping[$alias]['tipoRelazione'] == itaModelServiceData::RELATION_TYPE_ONE_TO_ONE) {
                        $this->modelData->addRelationOneToOne($tableName, $tableData, null, $this->operationMapping[$alias]['keyMapping']);
                    } elseif ($this->operationMapping[$alias]['tipoRelazione'] == itaModelServiceData::RELATION_TYPE_ONE_TO_MANY) {
                        $this->modelData->addRelationOneToMany($tableName, $tableData, null, $this->operationMapping[$alias]['keyMapping']);
                    }
//                    else {
//                        $this->modelData->addRelationManyToOne($tableName, $tableData, null, $this->operationMapping[$alias]['keyMapping']);
//                    }
                }
            }
        }

        return true;
    }

    /*
     * ritorna l'array con i record della tabella relazionata presi dalla griglia in memoria
     * @param String $tableName TableName della relazione
     * @param String $alias Alias della relazione 
     * 
     * @return array i record della relazione
     */

    protected function getDataRelationView($tableName, $alias = null) {
        return null;
    }

    /*
     * ritorna l'array con i record della tabella relazionata presi dal database
     * metodo da overridere sui figli
     * @return array i record della relazione
     */

    protected function getDataRelation($operation = null) {
        return null;
    }

//    protected function showOperationMessage() {
//        if (isSet($this->showOperationMessage) && $this->showOperationMessage === false) {
//            return;
//        }
//
//        $msg = cwbParGen::getFormSessionVar($this->nameForm, 'operationMsg');
//        if (!empty($msg)) {
//            Out::msgBlock('', 3000, false, $msg);
//            cwbParGen::removeFormSessionVar($this->nameForm, 'operationMsg');
//        }
//    }

    /**
     * Elaborazione automatica dei campi di audit
     * @param <array> $result_tab
     */
    protected function renderElencaAudit(&$result_tab) {
        if (is_array($result_tab)) {
            foreach ($result_tab as &$row) {
                $timeOper = (!empty($row['TIMEOPER']) ? trim($row['TIMEOPER']) : '');
                $dataOper = (!empty($row['DATAOPER']) ? new DateTime($row['DATAOPER']) : '');
                $datatimeoper = $timeOper . (!empty($timeOper) && !empty($dataOper) ? ' - ' : '') . (!empty($dataOper) ? $dataOper->format('d/m/Y') : '');

                $row['CODUTE'] = '<div style="font-style: italic;">' . $row['CODUTE'] . '</div>';
                $row['DATATIMEOPER'] = '<div style="font-style: italic;">' . $datatimeoper . '</div>';
            }
        }
    }

    /**
     * Elaborazione automatica del campo FLAG_DIS
     * @param <array> $result_tab
     */
    protected function renderElencaFlagDis(&$result_tab) {
        
    }

    /**
     * Gestione automatica dei filtri sui campi di audit
     */
    protected function filtraElencaAudit() {
        $codute = trim($_POST['CODUTE']);
        if (!empty($codute)) {
            $this->gridFilters['CODUTE'] = $codute;
        }
    }

    /**
     * Gestione automatica dei filtri sul campo FLAG_DIS
     */
    protected function filtraElencaFlagDis() {
        switch ($_POST['FLAG_DIS']) {
            case 'D':
                $this->gridFilters['FLAG_DIS'] = 1;
                break;
            case 'A':
                $this->gridFilters['FLAG_DIS'] = 0;
                break;
        }
    }

    /**
     * Gestione automatica dell'order by sui campi di audit
     * @param type $sortIndex
     */
    protected function orderElencaAudit(&$sortIndex) {
        if ($sortIndex == 'DATATIMEOPER') {
            $sortIndex = array();
            $sortIndex[] = 'DATAOPER';
            $sortIndex[] = 'TIMEOPER';
        }
    }

    /**
     * Gestione automatica dell'order by sul campo FLAG_DIS
     * @param type $sortIndex
     */
    protected function orderElencaFlagDis(&$sortIndex) {
        
    }

    /**
     * Azzera la selezione multipla
     */
    protected function resetSelected() {
        TableView::setDeselectAll($this->nameForm . "_" . $this->GRID_NAME);
        
        $this->selectedValues = array();
    }

    /**
     * Attiva/Disattiva la selezione su una riga a seconda di quanto impostato sulla frontend
     * @param <string> $rowid
     */
    protected function updateSelectedRow($rowid) {
        if ($_POST['jqg_' . $this->nameForm . '_' . $this->GRID_NAME . '_' . $rowid] == 1) {
            if($this->fakeMultiselect === true){
                $this->selectedValues = array(
                    $rowid => true
                );
            }
            else{
                $this->selectedValues[$rowid] = true;
            }
        } else {
            unset($this->selectedValues[$rowid]);
        }
    }

    /**
     * Permette di selezionare/deselezionare una riga da codice
     * @param <string> $rowid
     * @param <boolean> $select
     */
    protected function toggleSelectedRow($rowid, $select, $propagate=true) {
        if ($select) {
            $this->selectedValues[$rowid] = true;
            TableView::setSelection($this->nameForm . "_" . $this->GRID_NAME, $rowid, 'id', $propagate);
        } else {
            unset($this->selectedValues[$rowid]);
            TableView::disableSelection($this->nameForm . "_" . $this->GRID_NAME, $rowid, 'id');
        }
    }
    
    protected function initGridPager(){
        $codice = '$("#gbox_'.$this->nameForm.'_'.$this->GRID_NAME.' .ui-pg-selbox").closest("td").before("<td dir=\'ltr\'>Righe per pagina:</td>");';
        Out::codice($codice);
        
        $codice = '$("#gbox_'.$this->nameForm.'_'.$this->GRID_NAME.' .ui-pg-selbox option[value=\'Tutte\']").val(999999999999);';
        Out::codice($codice);
        
        if($this->fakeMultiselect === true){
            $codice = "var myGrid = $('#{$this->nameForm}_{$this->GRID_NAME}').setGridParam({
                beforeSelectRow: function(id, e){
                    var propagate = jQuery(this).getGridParam('selarrrow');
                    if(propagate.length > 0 && !propagate.includes(id)){
                        jQuery(this).resetSelection();
                    }
                    return true;
                }
            });";
            Out::codice($codice);
            Out::html('jqgh_' . $this->nameForm . '_' . $this->GRID_NAME . '_cb', '');
        }
        
//        $html = cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_' . $this->GRID_NAME . '_SELECT_ALL', '<span class="ui-icon ui-icon-check-on"></span>', array(), 'Seleziona tutto su tutte le pagine');
        $html = cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_' . $this->GRID_NAME . '_CLEAN_SELECT', '<span class="ui-icon ui-icon-erase"></span>', array(), 'Azzera selezione su tutte le pagine');
        Out::gridSetColumnFilterHtml($this->nameForm, $this->GRID_NAME, 'cb', $html);
//        Out::gridSetColumnWidth($this->nameForm, $this->GRID_NAME, 'cb', 40);
        Out::attributo('cb_' . $this->nameForm . '_' . $this->GRID_NAME, 'title', 0, 'Seleziona/Deseleziona tutta la pagina');
    }


    /**
     * Seleziona/Deseleziona tutte le righe passate a seconda di quanto impostato sulla frontend
     * @param <array> $rowids
     */
    protected function updateSelectAll($rowids) {
        foreach ($rowids as $rowid) {
            $this->updateSelectedRow($rowid);
        }
    }

    protected function renderSelect() {
        foreach (array_keys($this->selectedValues) as $rowid) {
            TableView::setSelection($this->nameForm . "_" . $this->GRID_NAME, $rowid, 'id');
        }
    }
    
    protected function showWorkflow(){
        $workflowData = $this->getExternalRefKey('WORKFLOW');
        $contesto = $this->workflowHelper->getContestoWorkflow($workflowData['PASSO']['PRONUM']);
        $this->workflowHelper->visualizzaDiagramma($contesto);
    }

    // --- METODI ASTRATTI -----------------------------------------------------

    protected function preConstruct() {
        
    }

    protected function postConstruct() {
        
    }

    protected function preDestruct() {
        
    }

    protected function postDestruct() {
        
    }

    protected function preClose() {
        
    }

    protected function postClose() {
        
    }

    protected function elenca() {
        
    }

    protected function setGridFilters() {
        
    }

    /**
     * Utile nel caso si debbano fare operazioni precedenti a quelle di default.
     */
    protected function preParseEvent() {
        
    }

    protected function customParseEvent() {
        
    }

    protected function preConnettiDB() {
        
    }

    protected function postConnettiDB() {
        
    }

    protected function preApriForm() {
        
    }

    protected function postApriForm() {
        
    }
    
    protected function manageAutoFields(){
        if($this->elencaAutoFlagDis){
            Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'FLAG_DIS', 'A');
            $_POST['FLAG_DIS'] = 'A';
        }
    }

    protected function preRicercaEsterna() {
        
    }

    protected function preNuovo() {
        
    }

    protected function postNuovo() {
        
    }

    protected function preAggiungi() {
        
    }

    protected function postAggiungi($esito = null) {
        
    }

    protected function preAggiorna() {
        
    }

    protected function postAggiorna($esito = null) {
        
    }

    protected function preTornaElenco() {
        
    }

    protected function postTornaElenco() {
        
    }

    protected function preConfermaCancella() {
        
    }

    protected function postConfermaCancella($esito = null) {
        
    }

    protected function postPulisciCampi() {
        
    }

    protected function postResetParametriRicerca() {
        
    }

    protected function postCaricaParametriRicerca($parametri) {
        
    }

    protected function postSalvaParametriRicerca() {
        
    }

    protected function sqlElenca(&$sqlParams = array()) {
        if ($this->elencaAutoAudit) {
            $this->filtraElencaAudit();
        }
        if ($this->elencaAutoFlagDis) {
            $this->filtraElencaFlagDis();
        }

        $this->postSqlElenca(array(), $sqlParams);
        
        if($this->authFiltersHelper->getFieldsElenca() !== null){
            $this->authFiltersHelper->buildFiltersElenca($this->SQL, $sqlParams);
        }
    }
    
    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        
    }
    
    public function publicSqlElenca(&$sqlParams){
        $this->postSqlElenca(array(), $sqlParams);
        return $this->SQL;
    }
	
    protected function sqlElencaXlsx(&$sqlParams = array()) {
        if ($this->elencaAutoAudit) {
            $this->filtraElencaAudit();
        }
        if ($this->elencaAutoFlagDis) {
            $this->filtraElencaFlagDis();
        }

        $this->postSqlElencaXlsx(array(), $sqlParams);
    }
    
    protected function postSqlElencaXlsx($filtri, &$sqlParams = array()) {
        $this->postSqlElenca($filtri, $sqlParams);
    }
	
    protected function sqlDettaglio($index, &$sqlParams) {
        
    }

    protected function preElenca() {
        
    }

    protected function postElenca() {
        
    }

    protected function preDettaglio($index, &$sqlDettaglio = null) {
        
    }

    // Effettuare tutte le decodifiche sui campi
    protected function postDettaglio($index, &$sqlDettaglio = null) {
        
    }

    protected function preAltraRicerca() {
        
    }

    protected function elaboraCurrentRecord($operation) {
        
    }

    protected function postAltraRicerca() {
        
    }

    protected function preStampaElenco() {
        
    }

    protected function postStampaElenco() {
        
    }

    protected function preExportXLS() {
        
    }

    protected function postExportXLS() {
        
    }

    protected function elaboraRecords($Result_tab) {
        return $Result_tab;
    }
    
    protected function elaboraRecordsXlsx($Result_tab){
        return $this->elaboraRecords($Result_tab);
    }

    //VIENE CHIAMATO PRIMA DELLA CANCELLAZIONE CON $this->CURRENT_RECORD valorizzato;
    protected function loadRelationsHook(){
        
    }
    // --- GETTER/SETTER -------------------------------------------------------

    public function setCurrentIndex($current_index){
        $this->current_index = $current_index;
    }
    
    public function getCurrentIndex(){
        return $this->current_index;
    }

    public function getDetailView() {
        return $this->detailView;
    }

    public function setDetailView($detailView) {
        $this->detailView = $detailView;
    }

    public function getFiltriFissi() {
        return $this->filtriFissi;
    }

    public function setFiltriFissi($filtriFissi) {
        $this->filtriFissi = $filtriFissi;
    }

    public function getMasterRecord() {
        return $this->masterRecord;
    }

    public function setMasterRecord($masterRecord) {
        $this->masterRecord = $masterRecord;
    }

    public function getExternalParams() {
        return $this->externalParams;
    }

    public function getExternalParamsNormalyzed() {
        $return = array();
        
        if(is_array($this->externalParams)){
            foreach ($this->externalParams as $param => $data) {
                $row = array();
                if (is_array($data)) {
                    $row['PERMANENTE'] = (isSet($data['PERMANENTE']) && ($data['PERMANENTE'] === NULL || $data['PERMANENTE'] === TRUE));
                    $row['VALORE'] = (isSet($data['VALORE']) ? $data['VALORE'] : null);
                    $row['HTMLELEMENT'] = (!empty($data['HTMLELEMENT']) ? $data['HTMLELEMENT'] : $key);
                } else {
                    $row['PERMANENTE'] = true;
                    $row['VALORE'] = $data;
                    $row['HTMLELEMENT'] = $key;
                }
                if (isset($row['VALORE'])) {
                    $return[$param] = $row;
                }
            }
        }
        
        return $return;
    }

    public function setExternalParams($externalParams) {
        $this->externalParams = $externalParams;
    }

    public function getFlagSearch() {
        return $this->flagSearch;
    }

    public function setFlagSearch($flagSearch) {
        $this->flagSearch = $flagSearch;
    }

    public function getSearchOpenElenco() {
        return $this->searchOpenElenco;
    }

    public function setSearchOpenElenco($searchOpenElenco) {
        $this->searchOpenElenco = $searchOpenElenco;
    }

    public function setApriDettaglio($index) {
        $this->apriDettaglioIndex = $index;
    }

    public function setReturnDataFlag($index) {
        $this->returnData = $index;
    }

    public function getBreakEvent() {
        return $this->breakEvent;
    }

    public function setBreakEvent($breakEvent) {
        $this->breakEvent = $breakEvent;
    }

    public function getSelected() {
        return array_keys($this->selectedValues);
    }
    
    public function setSkipValidateDB($skipValidateDB){
        $this->skipValidateDB = $skipValidateDB;
    }
    
    public function getSkipValidateDB(){
        return $this->skipValidateDB;
    }
    
    public function setSkipValidateMemory($skipValidateMemory){
        $this->skipValidateMemory = $skipValidateMemory;
    }
    
    public function getSkipValidateMemory(){
        return $this->skipValidateMemory;
    }
}

