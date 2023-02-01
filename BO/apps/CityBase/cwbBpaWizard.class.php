<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/linkedListDouble.class.php';
include_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDBRequest.class.php';

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';

/**
 *
 * Superclasse gestione wizard, la cache viene gestita solo per il popolamento dei valori quando da una form 
 * si va indietro a quella precedente, non è gestita la cache sull'avanti
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    27.05.2016   
 * @link
 * @see
 * @since
 * 
 */
class cwbBpaWizard extends itaFrontControllerCW {

    const DEFAULT_COMPLETE_MSG = 'Pratica conclusa con successo';
    const DEFAULT_ERROR_MSG = 'Errore completamento pratica';
    const OPEN_MODEL_ERROR = 'Apertura model fallita';
    const ERROR = 'Errore';
    const FINISH = 'Fine Evento';
    const WIZARD_PREV_VALUE = 'wizardPrevValue';
    const APC_TTL = 7200;

    protected $navigationRules; // lista guida che pilota il wizard 
    protected $firstStepName; // prima form del wizard
    protected $lastStepName; // ultima form del wizard
    protected $nextParameters; // Parametri da passare tra la form corrente e quella successiva. Parametri utilizzati nella form (es. popolare dati a video)
    protected $DBName;         // Nome Db da usare nel wizard
    protected $completeMsg;  // Messaggio da stampare al concludi in caso di esito positivo
    protected $completeErrorMsg;  // Messaggio da stampare al concludi in caso di errore
    protected $previous;   //variabile impostata a true nel caso di indietro

    function postItaFrontControllerCostruct() {
        $this->initVars();

        $this->nextParameters = array();

        $this->previous = cwbParGen::getFormSessionVar($this->nameForm, '_previous');
        $this->APC_TTL = cwbParGen::getFormSessionVar($this->nameForm, 'APC_TTL');
        $this->navigationRules = unserialize(cwbParGen::getFormSessionVar($this->nameForm, '_navigationRules'));
        if (!$this->navigationRules) {
            $this->initListaGuida();
        }
    }

    function __destruct() {
        $this->preDestruct();
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_navigationRules', serialize($this->navigationRules));
            cwbParGen::setFormSessionVar($this->nameForm, '_previous', $this->previous);
            cwbParGen::setFormSessionVar($this->nameForm, 'APC_TTL', $this->APC_TTL);
        }
        $this->postDestruct();
    }

    /*
     * inizializzo la lista guida mettendo la prima e l'ultima form che sono fisse, aggiungo anche l'alias che poi mi riserve
     */

    private function initListaGuida() {
        $this->navigationRules = new linkedListDouble();
        $this->navigationRules->push(array('nameForm' => ($this->firstStepName . '_' . time() . '_' . rand()), 'nameFormOrig' => $this->firstStepName));
        $this->navigationRules->push(array('nameForm' => ($this->lastStepName . '_' . time() . '_' . rand()), 'nameFormOrig' => $this->lastStepName));
        $this->navigationRules->rewind(); // la prima volta va fatto il rewind per inizializzare il puntatore ad inizio lista         

        $devLib = new devLib();
        $apcTttl = $devLib->getEnv_config('CITYPEOPLE', 'codice', 'APC_TTL', false); //E' abilitato il debug?       
        if ($apcTttl['CONFIG'] > 0) {
            //se indicato un valore lo usa altrimenti imposta 7200 come costante
            $this->APC_TTL = $apcTttl['CONFIG'];
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                // pulisco il wizard all'apertura per problema all'apertura del wizard se è già aperto (non parte nessun evento di chiusura sul vecchio)

                $this->cleanWizard();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->close();
                        break;
                    case $this->nameForm . '_Avanti':
                        $this->goNext();
                        break;
                    case $this->nameForm . '_Indietro':
                        $this->goPrevious();
                        break;
                    case $this->nameForm . '_Conferma':
                        $this->complete();
                        break;
                    case $this->nameForm . '_Annulla':
                        $this->clean();
                        break;
                    case $this->nameForm . '_Pulisci'://pulisce la schermata e le variabili
                        $this->cleanWizard();
                        break;
                    case $this->nameForm . '_ConfermaAnnulla':
                        $this->cleanWizard();
                        break;
                    case $this->nameForm . '_NonAnnullare':
                        break;
                    case $this->nameForm . '_AnnullaPrevious':
                        break;
                    case $this->nameForm . '_ConfermaPrevious':
                        $this->previous();
                        break;
                    case $this->nameForm . '_ConfermaAvanti':
                        // se parte un warning e viene cliccato conferma, richiamo l'avanti senza validazione
                        $this->goNextOperations();
//                        $this->nextParameters = array();
                        break;
                    case $this->nameForm . '_AnnullaAvanti':
                        // se c'è un warning e si preme annulla non faccio niente 
                        $this->nextParameters = array();
                        break;
                    case $this->nameForm . '_Chiudi':
                        $this->close();
                        break;
//                    default :
//                        // se clicco un evento della pagina contenuta all'interno del wizard,
//                        // devo rilanciare l'evento alla form interna
//                        $currentForm = $this->navigationRules->current();
//                        if (!strpos($_POST['id'], $currentForm) && strpos($_POST['id'], $currentForm) >= 0) {
//
//                            $this->runFormEvent($currentForm);
//                        }
//                        break;
                }
                break;
//            default :
//                // se lancio un evento della pagina contenuta all'interno del wizard,
//                // devo rilanciare l'evento alla form interna
//                $currentForm = $this->navigationRules->current();
//                if (strpos($_POST['id'], $currentForm) !== false && strpos($_POST['id'], $currentForm) >= 0) {
//
//                    $this->runFormEvent($currentForm);
//                }
//                break;
        }
        $this->customParseEvent();
    }

    protected function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        $this->deleteCache();
        cwbParGen::removeSessionVar($this->nameForm);

        // svuoto le sessioni delle pagine inserite da codice all'interno del wrapper
        $this->navigationRules->rewind();
        for ($index = 0; $index < $this->navigationRules->count(); $index++) {
            $currArr = $this->navigationRules->current();
            cwbParGen::removeSessionVar($currArr['nameForm']);
            $this->navigationRules->next();
        }
    }

    /*
     * azioni da eseguire all'evento 'openform' del wizard
     */

    private function openFormAction() {
        $this->visualizzaBottoni();
        $this->includeForm();
        $this->postOpenForm();
    }

    /*
     * gli eventi che non sono del 'container' wizard li rilancio alla classe interna specifica contenuta
     */

//    private function runFormEvent($currentNameForm) {
//        $formObj = itaFrontController::getInstance($currentNameForm);
//        if (!$formObj) {
//            Out::msgStop(self::ERROR, self::OPEN_MODEL_ERROR);
//            return;
//        }
//        $formObj->parseEvent();
//    }

    /*
     * include una form all'interno del div gestione del 'container' wizard
     */

    private function includeForm($oldNameForm = null, $indietro = false) {
        $currArr = $this->navigationRules->current();
        $currentNameForm = $currArr['nameFormOrig']; // prendo la form corrente da aprire
        $currentNameFormAlias = $currArr['nameForm']; // prendo l'alias salvato
        $appliedCache = false;
//        Out::hide($this->nameForm . '_divGestione', 'slide', 500);
        Out::hide($this->nameForm . '_divGestione');
        Out::innerHtml($this->nameForm . '_divGestione', ""); // pulisco div gestione
        // se ci sono dei parametri da passare tra la form precedente e quella successiva li passo all'oggetto
        $cacheValue = $this->getFormDataCache($currentNameForm, $this->navigationRules->currentKey());
        $params = array();
        $wizardPrevValue = array();

        if ($cacheValue) {
            // la cache va applicata all'indietro oppure all'avanti se c'è e se il programmatore torna true sul metodo applyCacheNext (cioè non sono stati modificati dati chiave)
            if ($indietro || $this->applyCacheNext($currentNameForm, $oldNameForm, $cacheValue)) {
                // se c'è la cache comanda sui parametri passati tra una form e l'altra
                // gli risetto 'token' perché serve per reperire i dati dalla cache
                $appliedCache = true;
                $token = $_POST['TOKEN'];
                $_POST = array();
                $_POST['TOKEN'] = $token;
                foreach ($cacheValue as $key => $value) {
                    if ($key === self::WIZARD_PREV_VALUE) {
                        $wizardPrevValue = $value;
                    } else if (strpos($key, $currentNameForm) !== false && strpos($key, $currentNameForm) >= 0) {
                        $params[$key] = $cacheValue[$key];
                        $_POST[$key] = $cacheValue[$key];
                    }
                }
            }
        }

        //      itaLib::openInner($currentNameForm, '', '', $this->nameForm . '_divGestione'); // aggiungo la form al div gestione
        $formObj = cwbLib::innestaForm($currentNameForm, $this->nameForm . '_divGestione', false, $currentNameFormAlias);
        Out::show($this->nameForm . '_divGestione', 'slide', 500);
        if (!$formObj) {
            Out::msgStop(self::ERROR, self::OPEN_MODEL_ERROR);
            return;
        }
        $this->previous = $indietro;
        $formObj->setPrevious($indietro);

        $this->customOperationIncludeForm($formObj);

        $formObj->setAppliedCache($appliedCache);
        $formObj->setWizardParameters($this->nextParameters);
        $formObj->setEvent('openform');

        if ($wizardPrevValue) {
            // se sulla post c'è questo array (key=nome proprietà che gestisce la grid, value=record della grid),
            // lo scorro e faccio la set 
            foreach ($wizardPrevValue as $key => $value) {
                $formObj->$key($value);
            }

            unset($_POST[self::WIZARD_PREV_VALUE]);
        }

        $this->addWizardParams($this->nextParameters, $formObj);
        $this->addWizardParams($params, $formObj);

        $formObj->parseEvent();

        return $formObj;
    }

    /**
     * Aggiunge a video i parametri passati dal passo precedente del wizard
     */
    private function addWizardParams($params, $formObj) {
        if ($params) { // parametri in arrivo dallo step precedente del wizard
            Out::valori($params);
            if (array_key_exists($formObj->getNameForm() . "_" . $formObj->getTABLE_NAME(), $params)) {
                $currentRecord = $params[$formObj->getNameForm() . "_" . $formObj->getTABLE_NAME()];
                foreach ($currentRecord as $key => $value) {
                    $id = $formObj->getNameForm() . "_" . $formObj->getTABLE_NAME() . "[" . $key . "]";
                    Out::valore($id, $value);
                }
            }
        }
    }

    /*
     * Tasto Avanti
     * all'avanti viene eseguita la validazione della form(sulla form ci deve essere un metodo 'validaWizardStep')
     * passata la validazione, viene messa la post in cache per riusarla in caso di pressione del tasto 'indietro'
     * poi viene inclusa la form successiva al posto di quella corrente
     */

    protected function goNext() {
        $this->nextParameters = array();
        if ($this->verifyNext()) {
            $this->goNextOperations();
        }
        $this->nextParameters = array();
    }

    private function goNextOperations() {
        $oldArr = $this->navigationRules->current();
        $oldNameForm = $oldArr['nameFormOrig'];
        $oldNameFormAlias = $oldArr['nameForm'];
        // verifico se ci sono grid da salvare in cache (dalla post non si riesce a prendere il valore della grid)
        $formObj = itaFrontController::getInstance($oldNameForm, $oldNameFormAlias);
        if ($formObj) {
            $grids = $formObj->setValueToSave();

            if ($grids) {
                // se ci sono delle grid da salvare appoggio i record contenuti sulla POST che poi viene messa in cache,
                // cosi all'indietro le posso riprendere e ripassare il valore alla proprietà che gestisce la griglia 
                $_POST[self::WIZARD_PREV_VALUE] = $grids;
            }
        }
        $esito = $this->preNext($formObj, $this->navigationRules->currentKey());
        if ($esito) {
            $this->postValidateNext($formObj, $this->navigationRules->currentKey());

            // prima di passare a quella successiva, appoggio la form corrente in cache
            $this->addFormDataCache($oldNameForm, $this->navigationRules->currentKey(), $_POST);

            $this->navigationRules->next(); // vado avanti
            $currentInstance = $this->includeForm($oldNameForm);
            $this->visualizzaBottoni();
            $this->postNext($currentInstance, $this->navigationRules->currentKey());
        }
    }

    /*
     * Tasto Indietro
     * alla pressione del tasto indietro, pulisco la cache della form corrente e rimuovo la form dalla lista guida,
     * poi includo la form in cui sto andando
     */

    protected function goPrevious() {
        Out::msgQuestion("Torna Indietro", "Andando indietro e modificando dati importanti, perderai le modifiche fatte al passo corrente. Confermare?", array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaPrevious', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaPrevious', 'model' => $this->nameForm)
        ));
    }

    /*
     *  Tasto Annulla
     *  all'annulla si azzera il wizard e si riparte dalla prima form
     */

    protected function clean() {
        Out::msgQuestion("Annullamento ", "Annullando perderai TUTTE le modifiche fatte. Confermare?", array(
            'Annulla' => array('id' => $this->nameForm . '_NonAnnullare', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAnnulla', 'model' => $this->nameForm)
        ));
    }

    /*
     * Tasto Indietro
     * alla pressione del tasto indietro, pulisco la cache della form corrente e rimuovo la form dalla lista guida,
     * poi includo la form in cui sto andando
     */

    protected function previous() {
        $oldArr = $this->navigationRules->current();
        $oldNameForm = $oldArr['nameFormOrig'];
        $oldNameFormAlias = $oldArr['nameForm'];

        $formObj = itaFrontController::getInstance($oldNameForm, $oldNameFormAlias);

        $this->prePrevious($formObj, $this->navigationRules->currentKey());
        $toRemove = true;
        // se faccio indietro a partire dall'ultima form non la devo eliminare (la prima e l'ultima form rimangono sempre fisse nella lista guida)
        if ($this->lastStepName === $oldNameForm) {
            $toRemove = false;
        }

        // verifico se ci sono grid da salvare in cache (dalla post non si riesce a prendere il valore della grid)
        if ($formObj) {
            $grids = $formObj->setValueToSave();

            if ($grids) {
                // se ci sono delle grid da salvare appoggio i record contenuti sulla POST che poi viene messa in cache,
                // cosi all'indietro le posso riprendere e ripassare il valore alla proprietà che gestisce la griglia 
                $_POST[self::WIZARD_PREV_VALUE] = $grids;
            }
        }

        // prima di passare a quella precedente, appoggio la form corrente in cache
        $this->addFormDataCache($oldNameForm, $this->navigationRules->currentKey(), $_POST);

        // pulisco la sessione 
        cwbParGen::removeFormSessionVars($oldNameFormAlias);

        $this->navigationRules->prev(); // vado indietro
        $currentArr = $this->navigationRules->current();
        $currentNameForm = $currentArr['nameFormOrig'];
        // pulisco la cache delle operazioni su db della form corrente (dopo aver fatto prev)
        $this->cleanOperationCache($currentNameForm, $this->navigationRules->currentKey());
        // pulisco la cache dei parametri fissi passati (cancello sia quelli senza key che con key non sapendo quale è stato usato)
        $this->cleanFixedParameterCacheKey($this->navigationRules->currentKey(), $currentNameForm);
        $this->cleanFixedParameterCacheKey($this->navigationRules->currentKey());

        // se si torna indietro rimuovo la form corrente,tranne se è il primo o l'ultimo passo
        //  (se poi si ritorna avanti sullo stesso percorso la riaggiungo)        
        if ($toRemove) {
            $this->navigationRules->offsetUnset($this->navigationRules->key() + 1);
        }
        $currentInstance = $this->includeForm($oldNameForm, true);
        $this->visualizzaBottoni();
        $this->postPrevious($currentInstance, $this->navigationRules->currentKey());
    }

    /*
     * Tasto Concludi
     *  su customConcludi vanno eseguite le operazioni finali del wizard, se vanno a buon fine viene 
     *  ripulita la cache per questo wizard e si torna alla pagina iniziale
     */

    protected function complete() {
        //disabilito il pulsante conferma così nel caso di errore non posso ricliccare
//        Out::attributo($this->nameForm . '_Conferma', 'disabled', '0', 'disabled'); //viene disabilitato in attesa di decisione
        //Out::html($this->nameForm . '_Conferma', "PROVA CAMBIO TEXT");
        Out::hide($this->nameForm . '_Conferma'); //ora deciso di nascondere il pulsante  16-10-2018 Sr #512
        Out::hide($this->nameForm . '_Annulla'); //ora deciso di nascondere il pulsante  16-10-2018 Sr #512
        Out::show($this->nameForm . '_Chiudi'); //ora deciso di aggiungere pulsante Chiudi 16-10-2018 Sr #512
        // progress bar
        $this->processInit('executeComplete', 4, 1, 'false');
        $this->processStart("Salvataggio in corso...", 80, 350, 'false');
    }

    /*
     * Viene utilizzato per il reperimento dei progressivi prima del salvataggio finale
     */

    protected function preExecuteComplete() {
        
    }

    /*
     * al concludi se ci sono operazioni in cache le eseguo tutte e poi passo la palla al customConcludi
     */

    public function executeComplete() {
        $this->processRefresh();
        $this->processMax(4);

        $this->preExecuteComplete();

        cwbDBRequest::getInstance()->startManualTransaction(null, $this->MAIN_DB);  // cwbDBRequest::getInstance()->startManualTransaction($this->DBName);
        try {
            $result = true;
            $operations = $this->getOperationsCache();

            if ($operations) {
                $i = 0;
                // scorro tutti i formData a cui sono associate operazioni                
                foreach ($operations as $formName => $formDataOperation) {
                    // per ogni formdata scorro le sue operazioni                    
                    foreach ($formDataOperation as $operationKey => $operation) {
                        $i++;
                        $first = $i === 1;

                        $this->executeOperation($operation, $first, $db);
                    }
                }
            }

            if ($result) {
                $result = $this->postComplete();
            }
            if ($result == false) {//Aggiunto perchè l'eccezione sul salvataggio della tabella viene intercettato già nei metodi sottostanti
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            } else {
                cwbDBRequest::getInstance()->commitManualTransaction();
            }

            //Se c'è stato errore nel salvataggio mi fermo. Ma poi su ANPR non scrivo niente... tanto ha fatto Rollback... forse
            if ($result == false) {
                $result = false;
            } else {
                //Usare questo metodo effettuare le attività logicamente fuori dalla transazione
                //es. chiamate al WS
                try {
                    $result = $this->postCompleteAfterTransaction();
                } catch (Exception $exc) { //Intercetto l'eccezione, altrimenti ma fa la rollback e spacco tutto perchè la transazione è già chiusa
                    if (empty($this->completeErrorMsg)) {
                        $this->completeErrorMsg = $exc->getMessage(); //non è valorizzato e sovrascrivo il messaggio dell'elaborazione
                    }
                    $result = false;
                }
            }
        } catch (Exception $exc) { //Entra qua in caso di crash dell'appliativo
            if (empty($this->completeErrorMsg)) {
                $this->completeErrorMsg = $exc->getMessage(); //non è valorizzato e sovrascrivo il messaggio dell'elaborazione
            }
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            $result = false;
        }

        $this->processEnd(); // chiudi progress

        if ($result) {
            $this->generateOutput();
            if (!$this->completeMsg) {
                $this->finishTrue();
//                return true; <-- non abilitare altrimenti il wizard non si chiude
            } else {
                $this->finishFalse();
            }

            $this->cleanWizard(1);
        } else {
            Out::msgStop(self::DEFAULT_ERROR_MSG, $this->completeErrorMsg);
        }
    }

    /*
     * lancio le operazioni automatiche
     */

    private function executeOperation($opToExecute, $db) {
        $operation = $opToExecute['operation'];
        $tableName = $opToExecute['table'];
        $data = $opToExecute['value'];
        $recordInfo = $opToExecute['recordInfo'];

        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, true);

        $oldCurrentRecord = $modelService->getByPks($db, $tableName, $data);

        $validationInfo = $modelService->validate($db, $tableName, $data, $operation, $oldCurrentRecord);

        if (empty($validationInfo)) {
            // non ci sono errori di validazione
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($tableName, $data);

            if ($operation == itaModelService::OPERATION_INSERT) {
                $modelService->insertRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
            } else if ($operation == itaModelService::OPERATION_UPDATE) {
                $modelService->updateRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
            } else if ($operation == itaModelService::OPERATION_DELETE) {
                $modelService->deleteRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
            }
        } else {
            cwbDBRequest::getInstance()->rollBackManualTransaction();

            foreach ($validationInfo as $currentInfo) {
                if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                    $msg .= (strtolower($tableName) != strtolower($this->TABLE_NAME) ? "Tabella: $tableName - " : "");
                    $msg .= ($line != 0 ? "Riga: $line - " : "");
                    $msg .= $currentInfo['msg'] . '<br/>';
                }
            }

            Out::valore(self::ERROR, $msg);
        }
    }

    /*
     *  Tasto Annulla
     *  all'annulla si azzera il wizard e si riparte dalla prima form
     */

    public function cleanWizard($conferma = 0) {
        parent::close(false);
        $this->close = false; // sovrascrito il true del parent
        $this->deleteCache();
        cwbParGen::removeSessionVar($this->nameForm);
        // svuoto le sessioni delle pagine inserite da codice all'interno del wrapper
        $this->navigationRules->rewind();
        for ($index = 0; $index < $this->navigationRules->count(); $index++) {
            $currArr = $this->navigationRules->current();
            $oldNameFormAlias = $currArr['nameForm'];

            cwbParGen::removeSessionVar($oldNameFormAlias);
            $this->navigationRules->next();
        }

        $this->initListaGuida();
        $this->openFormAction();
    }

    /*
     * aggiunge una form dopo quella corrente
     */

    public function addStepToNavigationRules($newNameForm) {
        // calcolo l'alias della form che poi viene usato per tutta la durata
        $toAdd = array('nameForm' => ($newNameForm . '_' . time() . '_' . rand()), 'nameFormOrig' => $newNameForm);
        $this->navigationRules->add($this->navigationRules->key() + 1, $toAdd);
    }

    /*
     * svuota la cache per il wizard corrente
     */

    public function deleteCache() {
        $cache = CacheFactory::newCache();
        $key = $_POST['TOKEN'] . $this->nameForm;
        $cache->delete($key);
    }

    // formdata (contenuto della POST)

    /*
     * aggiunge le info in cache
     */
    private function addFormDataCache($formDataName, $formDatakey, $formData) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache && $formDataName && $formDatakey >= 0 && $formData) {
            $data = $cache->get($cacheKey);

            if (!$data) {
                $data = array();
            }

            // aggiungo la $_POST in cache      
            $data['stepData'][$formDataName . $formDatakey] = array(
                'formData' => $formData
            );

            $cache->set($cacheKey, $data, $this->APC_TTL);
        }
    }

    /*
     * cancella le info dalla cache
     */

    private function cleanFormDataCache($formDataName, $formDataKey) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache && $formDataKey >= 0 && $formDataName) {
            $data = $cache->get($cacheKey);

            if ($data) {
                if ($data['stepData'] && $data['stepData'][$formDataName . $formDataKey]) {
                    unset($data['stepData'][$formDataName . $formDataKey]);

                    $cache->set($cacheKey, $data, $this->APC_TTL);
                }
            }
        }
    }

    /*
     * prendi le info dalla cache
     */

    private function getFormDataCache($formDataName, $formDataKey) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data && $data['stepData'] && $data['stepData'][$formDataName . $formDataKey] && $data['stepData'][$formDataName . $formDataKey]['formData']) {
                return $data['stepData'][$formDataName . $formDataKey]['formData'];
            }
        }
    }

    // operations (operazioni automatiche da eseguire al concludi)    

    /*
     * Se $operationKey è vuoto rimuove tutte le operazioni della form $formDataKey, sennò cancella l'operazione $operationKey
     * $formDataName = nome della form che inserisce l'operazione
     * $formDataKey = key della form che inserisce l'operazione (nel caso venisse usata più volte la stessa form)
     * $operationKey = nome univoco dell'operazione che si vuole eseguire
     */

    public function cleanOperationCache($formDataName, $formDataKey, $operationKey = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache && $formDataKey >= 0 && $formDataName) {
            $data = $cache->get($cacheKey);

            if ($data) {
                if ($data['operations'] && $data['operations'][$formDataName . $formDataKey]) {
                    // se viene passata la chiave dell'operazione, cancello quell'operazione
                    if ($operationKey) {
                        unset($data['operations'][$formDataName . $formDataKey][$operationKey]);
                    } else {
                        // se la chiave dell'operazione è vuota allora cancello tutte le operazioni della form
                        unset($data['operations'][$formDataName . $formDataKey]);
                    }

                    $cache->set($cacheKey, $data, $this->APC_TTL);
                }
            }
        }
    }

    /*
     * aggiunge l'operazione in cache
     * $formDataName = nome della form da cui viene aggiunta l'operazione
     * $formDataKey = key  della form da cui viene aggiunta l'operazione
     * $operationKey = chiave univoca dell'operazione
     * $operation = itaModelService::OPERATION_INSERT, itaModelService::OPERATION_UPDATE, itaModelService::OPERATION_DELETE
     * $table = nome tabella 
     * $value = valore (array con i record per insert,update o delete)

     */

    public function addOperationCache($formDataName, $formDataKey, $operationKey, $operation, $table, $value, $recordInfo = null) {

        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache) {
            $data = $cache->get($cacheKey);

            if (!$data) {
                $data = array();
            }

            if ($operation && $table) {
                if (!$data['operations']) {
                    $data['operations'] = array();
                }

                if (!$data['operations'][$formDataName . $formDataKey]) {
                    $data['operations'][$formDataName . $formDataKey] = array();
                }

                // aggiungo l'operazione
                $data['operations'][$formDataName . $formDataKey][$operationKey] = array(
                    'operation' => $operation,
                    'table' => $table,
                    'value' => $value,
                    'recordInfo' => $recordInfo
                );
            }

            $cache->set($cacheKey, $data, $this->APC_TTL);
        }
    }

    /*
     * prendi le operazioni di una singola key oppure tutte le operazioni della form
     * Se $operationKey è vuoto ritorna tutte le operazioni della form, sennò torna la singola operazione $operationKey
     * 
     * $formDataName = nome della form che inserisce l'operazione
     * $formDataKey = key della form che inserisce l'operazione
     * $operationKey = nome univoco dell'operazione che si vuole eseguire
     * 
     * return array singola operazione o lista operazioni
     */

    protected function getOperationCache($formDataName, $formDataKey, $operationKey = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data && $data['operations'] && $data['operations'][$formDataName . $formDataKey]) {
                if ($operationKey) {
                    return $data['operations'][$formDataName . $formDataKey][$operationKey];
                } else {
                    return $data['operations'][$formDataName . $formDataKey];
                }
            }
        }
    }

    /*
     * prendi tutte le operazioni in cache
     */

    protected function getOperationsCache() {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data && $data['operations']) {
                return $data['operations'];
            }
        }
    }

    // FIXED PARAMETER (parametri da usare in più step del wizard oppure da passare dagli step iniziali fino alla fine)
    // questi parametri sono diversi da $nextParameters in quanto non vengono passati allo step successivo ma rimangono in cache
    // per essere usati in genere nello step finale. 


    /*
     * aggiunge un parametro fisso in cache
     */
    public function addFixedParameterCache($key, $value, $formName = null, $formKey = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache && $key) {
            $data = $cache->get($cacheKey);

            if (!$data) {
                $data = array();
            }

            if ($formName) {
                $keyForm = $formName;
                if ($formKey >= 0) {
                    // se viene passata la chiave all'interno della linkedlist, la concateno (caso di form usata più volte, per distinguerle)
                    $keyForm .= $formKey;
                }

                // se c'è formdata il parametro lo metto sotto la form, in questo modo viene gestito
                // in automatico nel passaggio tra una form e l'altra
                $data['fixedParameters'][$keyForm][$key] = $value;
            } else {
                // se non c'è formdata metto il parametro direttamente sotto fixedParameters e non verrà gestito a mano
                // (va gestita manualmente ad esempio la cancellazione dalla cache se faccio indietro da una form)
                $data['fixedParameters'][$key] = $value;
            }

            $cache->set($cacheKey, $data, $this->APC_TTL);
        }
    }

    /*
     * cancella un parametro fisso dalla cache
     */

    public function cleanFixedParameterCache($key, $formDataName, $formDataKey = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data) {
                $keyForm = $formDataName;
                if ($formDataKey >= 0) {
                    // se viene passata la chiave all'interno della linkedlist, la concateno (caso di form usata più volte, per distinguerle)
                    $keyForm .= $formDataKey;
                }

                if ($data['fixedParameters'] && $data['fixedParameters'][$keyForm] && $data['fixedParameters'][$keyForm][$key]) {
                    unset($data['fixedParameters'][$keyForm][$key]);

                    $cache->set($cacheKey, $data, $this->APC_TTL);
                }
            }
        }
    }

    /*
     * cancella un parametro fisso dalla cache
     */

    public function cleanFixedParameterCacheKey($key, $formName = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data) {
                $keyToDelete = $key;
                if ($formName) {
                    $keyToDelete = $formName . $key;
                }

                if ($data['fixedParameters'] && $data['fixedParameters'][$keyToDelete]) {
                    unset($data['fixedParameters'][$keyToDelete]);

                    $cache->set($cacheKey, $data, $this->APC_TTL);
                }
            }
        }
    }

    /*
     * cancella tutti i parametri fissi dalla cache
     */

    public function cleanFixedParametersCache() {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;

        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data) {
                if ($data['fixedParameters']) {
                    unset($data['fixedParameters']);

                    $cache->set($cacheKey, $data, $this->APC_TTL);
                }
            }
        }
    }

    /*
     * prendi tutti i parametri fissi dalla cache
     */

    protected function getFixedParametersCache() {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            if ($data && $data['fixedParameters']) {
                return $data['fixedParameters'];
            }
        }
    }

    /*
     * prendi un parametro fisso dalla cache
     */

    protected function getFixedParameterCacheKey($key, $formName = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            $keyToGet = $key;
            if ($formName) {
                $keyToGet = $formName . $key;
            }

            if ($data && $data['fixedParameters'] && $data['fixedParameters'][$keyToGet]) {
                return $data['fixedParameters'][$keyToGet];
            }
        }
    }

    /*
     * prendi un parametro fisso dalla cache
     */

    protected function getFixedParameterCache($key, $formName = null, $formKey = null) {
        $cache = CacheFactory::newCache();
        $cacheKey = $_POST['TOKEN'] . $this->nameForm;
        if ($cache) {
            $data = $cache->get($cacheKey);

            $keyForm = $formName;
            if ($formKey >= 0) {
                // se viene passata la chiave all'interno della linkedlist, la concateno (caso di form usata più volte, per distinguerle)
                $keyForm .= $formKey;
            }
            if ($keyForm) {
                if ($data && $data['fixedParameters'] && $data['fixedParameters'][$keyForm] && array_key_exists($key, $data['fixedParameters'][$keyForm])) {
                    // cambiato controllo da $data['fixedParameters'][$keyForm][$key] in  array_key_exists($key, $data['fixedParameters'][$keyForm]) nel caso in cui il valore è 0
                    // *EC 20/12/2016
                    return $data['fixedParameters'][$keyForm][$key];
                }
            } else {
                if ($data && $data['fixedParameters'] && array_key_exists($key, $data['fixedParameters'])) {
                    // cambiato controllo da $data['fixedParameters'][$keyForm][$key] in  array_key_exists($key, $data['fixedParameters'][$keyForm]) nel caso in cui il valore è 0
                    // *EC 20/12/2016
                    return $data['fixedParameters'][$key];
                }
            }
        }
    }

    private function visualizzaBottoni() {
        $data = $this->navigationRules->current();
        $nameForm = $data['nameFormOrig'];

        if ($nameForm == $this->firstStepName) {
            $this->showFirstStep();
        } else if ($nameForm == $this->lastStepName) {
            $this->showLastStep();
        } else {
            $this->showIntermediateStep();
        }
    }

    private function verifyNext() {
        $currentObj = $this->navigationRules->current();
        $formObj = itaFrontController::getInstance($currentObj['nameFormOrig'], $currentObj['nameForm']);
        if (!$formObj) {
            Out::msgStop(self::ERROR, self::OPEN_MODEL_ERROR);
            return;
        }

        if ($this->authorized($formObj)) {
            return $this->validate($formObj);
        }

        return false;
    }

    /**
     * controlla le autorizzazioni per aprire il prossimo passo
     * @param type $formObj
     * 
     * @return boolean
     */
    private function authorized($formObj) {
        $currentObj = $this->navigationRules->current();

        // se non è settato il modulo salto il controllo delle autorizzazioni
        if ($formObj->getAUTOR_MODULO()) {
            $params = array('username' => cwbParGen::getSessionVar('nomeUtente'),
                'modulo' => $formObj->getAUTOR_MODULO(), 'num' => $formObj->getAUTOR_NUMERO());

            return $this->checkAutor(cwbAuthenticatorFactory::getAuthenticator($currentObj['nameFormOrig'], $params));
        }

        return true;
    }

    /**
     * Controllo autorizzazioni, L - Sola Lettura, G - Tutto tranne cancellazione, C - Tutto abilitato
     */
    private function checkAutor($authenticator) {
        if ($authenticator->missingAuthentication()) {
            Out::msgStop("Autorizzazioni mancanti", $authenticator->getMissingAuthenticationMessage());
            return false;
        }
        return true;
    }

    /**
     * esegue la validazione della pagina
     * @param type $formObj
     * @return boolean
     */
    private function validate($formObj) {
        $grids = $formObj->setValueToSave();

        $formObj->validaWizardStep($_POST, $msgError, $msgWarn, $grids);

        if ($msgError) {
            // errori bloccanti
            Out::msgStop("Errori di validazione", $msgError);

            return false;
        } else if ($msgWarn) {
            // warning non bloccanti nelle subform
            if (method_exists($formObj, 'customWarning')) {
                // caso di gestione del warning custom (es. apertura dialog)
                $formObj->customWarning($msgWarn);
            } else {
                //Nel caso in cui non c'è un metodo nel figlio richiama questo metodo 
                Out::msgQuestion("Errori non bloccanti", $msgWarn . ", proseguire con l'operazione?", array(
                    'Annulla' => array('id' => $this->nameForm . '_AnnullaAvanti', 'model' => $this->nameForm),
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaAvanti', 'model' => $this->nameForm)
                ));
            }
            return false;
        }

        return true;
    }

    private function showFirstStep() {
        Out::show($this->nameForm . '_Avanti');
        Out::hide($this->nameForm . '_Indietro');
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Annulla');
        Out::show($this->nameForm . '_Pulisci');
        Out::hide($this->nameForm . '_Chiudi');
    }

    private function showLastStep() {
        Out::hide($this->nameForm . '_Avanti');
        Out::show($this->nameForm . '_Annulla');
        Out::html($this->nameForm . '_Annulla_lbl', "Annulla");
        Out::show($this->nameForm . '_Indietro');
        Out::show($this->nameForm . '_Conferma');
        Out::attributo($this->nameForm . '_Conferma', 'disabled', '1');
        Out::hide($this->nameForm . '_Pulisci');
        Out::hide($this->nameForm . '_Chiudi');
    }

    private function showIntermediateStep() {
        Out::show($this->nameForm . '_Avanti');
        Out::show($this->nameForm . '_Annulla');
        Out::html($this->nameForm . '_Annulla_lbl', "Annulla");
        Out::show($this->nameForm . '_Indietro');
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Pulisci');
        Out::hide($this->nameForm . '_Chiudi');
    }

    protected function initVars() {
        
    }

    protected function customParseEvent() {
        
    }

    protected function preNext($currentStepInstance, $currentKey) {
        
    }

    protected function postNext($currentStepInstance, $currentKey) {
        
    }

    protected function postValidateNext($currentStepInstance, $currentKey) {
        
    }

    protected function postOpenForm() {
        
    }

    protected function prePrevious($currentStepInstance, $currentKey) {
        
    }

    protected function postPrevious($currentStepInstance, $currentKey) {
        
    }

    // metodi in transazione
    protected function postComplete($db) {
        return true;
    }

    protected function postCompleteAfterTransaction() {
        return true;
    }

    // metodi fuori transazione (es. generazione stampe o output da mandare a video)
    protected function generateOutput() {
        
    }

    protected function preDestruct() {
        
    }

    protected function customOperationIncludeForm($formObj) {
        return $formObj;
    }

    protected function postDestruct() {
        
    }

    // serve per confrontare la cache della pagina con la post corrente e capire se quella cache va riapplicata oppure no
    protected function applyCacheNext($currentNameForm, $oldNameForm, $cacheValue) {
        return false;
    }

    public function getNavigationRules() {
        return $this->navigationRules;
    }

    public function getFirstFormName() {
        return $this->firstStepName;
    }

    public function setNavigationRules($listaGuida) {
        $this->navigationRules = $listaGuida;
    }

    public function setFirstFormName($firstFormName) {
        $this->firstStepName = $firstFormName;
    }

    public function getLastFormName() {
        return $this->lastStepName;
    }

    public function setLastFormName($lastFormName) {
        $this->lastStepName = $lastFormName;
    }

    public function finishTrue() {
        Out::msgInfo(self::FINISH, "Evento salvato con successo!");
    }

    public function finishFalse() {
        Out::msgInfo(SELF::FINISH, self::DEFAULT_COMPLETE_MSG);
    }

}

?>