<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorUtenti() {
    $cwbBorUtenti = new cwbBorUtenti();
    $cwbBorUtenti->parseEvent();
    return;
}

class cwbBorUtenti extends cwbBpaGenTab {
    private $livelliTableName;
    private $gridLivelliName;
    private $gridLivelliContent;
    
    public function __construct($nameFormOrig=null, $nameForm=null) {
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'cwbBorUtenti';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->GRID_NAME = 'gridBorUtenti';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 1;
        
        $this->searchOpenElenco = true;
        //Indica quali schermate aprire dopo aver creato, modificato o cancellato un elemento
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->libDB = new cwbLibDB_BOR();
        $this->setTABLE_VIEW("BOR_UTENTI_V01");
        
        //GESTIONE DELLE RELAZIONI
        $this->livelliTableName = 'BOR_UTELIV';
        $this->gridLivelliName = 'gridBorUteliv';
        $this->gridLivelliContent = cwbParGen::getFormSessionVar($this->nameForm, 'gridLivelliContent');
        if($this->gridLivelliContent == ''){
            $this->gridLivelliContent = array();
        }
        $this->addDescribeRelation($this->livelliTableName, array('CODUTE' => 'CODUTENTE'), itaModelServiceData::RELATION_TYPE_ONE_TO_MANY);
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'gridLivelliContent', $this->gridLivelliContent);
        }
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_UTENTI[PROGRESPO]_butt':
                        cwbLib::apriFinestraRicerca('cwbBorRespo', $this->nameForm, 'returnFromBorRespo', $_POST['id'], true);
                        break;
                    default:
                        if(preg_match('/'.$this->nameForm.'_SEARCH_([0-9]*)/',$_POST['id'],$matches)){
                            $rowId = $matches[1]-1;
                            cwbLib::apriFinestraRicerca('cwbBorLivell', $this->nameForm, 'returnLivell', $rowId, true, null);
                        }
                }
                break;
            case 'returnFromBorRespo':
                switch ($this->elementId) {
                    case $this->nameForm . '_BOR_UTENTI[PROGRESPO]_butt':
                        Out::valore($this->nameForm . '_BOR_UTENTI[PROGRESPO]', $this->formData['returnData']['PROGRESPO']);
                        Out::valore($this->nameForm . '_NOMERES', $this->formData['returnData']['NOMERES']);
                        Out::valore($this->nameForm . '_BOR_UTENTI[IDRESPO]', $this->formData['returnData']['PROGRESPO']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_UTENTI[PROGRESPO]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'], $this->nameForm . '_BOR_UTENTI[PROGRESPO]')) {
                            $this->decodRespo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'], ($this->nameForm . '_BOR_UTENTI[PROGRESPO]'), ($this->nameForm . '_NOMERES'));
                            Out::valore($this->nameForm . '_BOR_UTENTI[IDRESPO]', $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO']);
                        } else {
                            Out::valore($this->nameForm . '_NOMERES', '');
                        }
                        break;
                    default:
                        if (preg_match('/' . $this->nameForm . '_(FLAG_VALID)_([0-9]*)/', $_POST['id'], $matches)) {
                            $subject = $matches[1];
                            $rowId = $matches[2] - 1;
                            $value = $this->formData[$_POST['id']];
                            $this->modificaCampoLivello($rowId, $subject, $value);
                        }
                        break;
                }
                break;
            case 'onBlur':
                if (preg_match('/' . $this->nameForm . '_(DATAINIZ|DATAFINE)_([0-9]*)/', $_POST['id'], $matches)) {
                    $subject = $matches[1];
                    $rowId = $matches[2] - 1;
                    $value = $this->formData[$_POST['id']];
                    $this->modificaCampoLivello($rowId, $subject, $value);
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->gridLivelliName:
                        $this->aggiungiLivello();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->gridLivelliName:
                        $rowId = $_POST['rowid'] - 1;
                        $this->cancellaLivello($rowId);
                        break;
                }
                break;
            case 'returnLivell':
                $rowId = $this->elementId;
                $this->setLivello($rowId,$this->formData);
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODUTE'] != '') {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if ($_POST['NOMEUTE'] != '') {
            $this->gridFilters['NOMEUTE'] = $this->formData['NOMEUTE'];
        }
    }

    protected function preNuovo() {
        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_LIVELLI);
        
        $this->gridLivelliContent = array();
        $this->caricaGridLivelli();
    }

    protected function postNuovo() {
        Out::setFocus("", $this->nameForm . '_BOR_UTENTI[NOMEUTE]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_UTENTI[NOMEUTE]');
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BOR_UTENTI[NOMEUTE]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_UTENTI[NOMEUTE]', trim($this->CURRENT_RECORD['NOMEUTE']));

        $arrayValue = $this->getDataRelation();
        $this->gridLivelliContent = $arrayValue[$this->livelliTableName];
        $this->caricaGridLivelli();
    }
    
    protected function getDataRelation($operation=null) {
        $valueArray = array();
        if($operation !== itaModelService::OPERATION_DELETE){
            $valueArray[$this->livelliTableName] = $this->libDB->leggiBorUteliv(array('CODUTENTE' => $this->CURRENT_RECORD['CODUTE']));
        }
        return $valueArray;
    }
    
    private function caricaGridLivelli(){
        // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $helper = new cwbBpaGenHelper();
        $helper->setGridName($this->gridLivelliName);
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_' . $this->gridLivelliName);

        $this->ordinaLivelli();
        $records = $this->elaboraLivelliRecord($this->gridLivelliContent);

        $ita_grid01 = $helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $this->gridLivelliName);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->gridLivelliName);
    }
    
    public function ricaricaGridLivelli($data){
        if(!is_array($this->CURRENT_RECORD)){
            $this->CURRENT_RECORD = array();
        }
        if(isSet($this->operationsData[$this->nameForm][$this->livelliTableName])){
            unset($this->operationsData[$this->nameForm][$this->livelliTableName]);
        }
        $this->CURRENT_RECORD['CODUTE'] = $data['CODUTE'];
        $arrayValue = $this->getDataRelation();
        $this->gridLivelliContent = $arrayValue[$this->livelliTableName];
        $this->caricaGridLivelli();
    }
    
    private function elaboraLivelliRecord($records) {
        $htmlRecords = array();

        $disable = ($this->authenticator->getLevel() !== 'C' && !$this->authenticator->getLevel() !== 'G');

        foreach ($records as $key => $value) {
            $htmlRecords[$key] = $value;

            $component = array(
                'id' => 'SEARCH',
                'type' => 'ita-button',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'onClickEvent' => false,
                'icon' => 'ui-icon-search',
                'properties' => array(
                    'style' => 'width: 20px; height: 20px;'
                )
            );
            if($disable){
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['SEARCH'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            $component = array(
                'id' => 'DES_LIVELL',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'onChangeEvent' => false,
                'additionalClass' => 'required',
                'properties' => array(
                    'value' => $records[$key]['DES_LIVELL'],
                    'style' => 'width: 90%;'
                )
            );
            $htmlRecords[$key]['DES_LIVELL'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            // data inizio
            $component = array(
                'id' => 'DATAINIZ',
                'type' => 'ita-datepicker',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                //'onChangeEvent' => true,
                'additionalClass' => 'ita-edit-onblur required',
                'properties' => array(
                    'value' => $records[$key]['DATAINIZ'],
                    'style' => 'width: 70px'
                )
            );
            if($disable){
                $component['type'] = 'ita-edit';
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['DATAINIZ'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            // data fine
            $component = array(
                'id' => 'DATAFINE',
                'type' => 'ita-datepicker',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                //'onChangeEvent' => true,
                'additionalClass' => 'ita-edit-onblur',
                'properties' => array('value' => $records[$key]['DATAFINE'],
                    'style' => 'width: 70px'
                )
            );
            if($disable){
                $component['type'] = 'ita-edit';
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['DATAFINE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            
            // data fine
            $component = array(
                'id' => 'FLAG_VALID',
                'type' => 'ita-checkbox',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                //'onChangeEvent' => true,
                'additionalClass' => 'ita-edit-onblur',
                'properties' => array()
            );
            if($records[$key]['FLAG_VALID'] == 1){
                $component['properties']['checked'] = 'true';
            }
            if($disable){
                $component['properties']['disabled'] = 'true';
            }
            $htmlRecords[$key]['FLAG_VALID'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
        }

        return $htmlRecords;
    }
    
    private function ordinaLivelli(){
        $livelliOrder = array();
        foreach($this->gridLivelliContent as $key=>$resp){
            $livelliOrder[$key] = preg_replace("/[^0-9]/","",$resp['DATAINIZ']);
        }
        array_multisort($livelliOrder, SORT_ASC, $this->gridLivelliContent);
    }
    
    private function aggiungiLivello(){
        $this->addInsertOperation($this->livelliTableName);
        $livello = $this->getModelService()->define($this->MAIN_DB, $this->livelliTableName);
        $livello['CODUTENTE'] = $this->CURRENT_RECORD['CODUTE'];
        $livello['DES_LIVELL'] = null;
        $livello['DATAINIZ'] = date('Ymd');
        $livello['FLAG_VALID'] = true;
        $this->gridLivelliContent[] = $livello;

        $this->caricaGridLivelli();
    }
    
    private function cancellaLivello($rowId){
        $this->addDeleteOperation($this->livelliTableName, array('IDUTELIV' => $this->gridLivelliContent[$rowId]['IDUTELIV']));

        unset($this->gridLivelliContent[$rowId]);

        $this->caricaGridLivelli();
    }
    
    private function setLivello($rowId,$data){
        $this->addUpdateOperation($this->livelliTableName, array('IDUTELIV' => $this->gridLivelliContent[$rowId]['IDUTELIV']));
        
        Out::valore($this->nameForm . '_DES_LIVELL_' . ($rowId+1), $data['returnData']['DES_LIVELL']);
        $this->gridLivelliContent[$rowId]['DES_LIVELL'] = $data['returnData']['DES_LIVELL'];
        $this->gridLivelliContent[$rowId]['IDLIVELL'] = $data['returnData']['IDLIVELL'];
    }
    
    private function modificaCampoLivello($rowId, $nomeCampo, $valore) {
        if(!isSet($this->gridLivelliContent[$rowId][$nomeCampo]) || $this->gridLivelliContent[$rowId][$nomeCampo] != $valore){
            $this->addUpdateOperation($this->livelliTableName, array('IDUTELIV' => $this->gridLivelliContent[$rowId]['IDUTELIV']));

            $this->gridLivelliContent[$rowId][$nomeCampo] = $valore;

            $this->caricaGridLivelli();
        }
    }
    
    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case $this->livelliTableName:
                $results = $this->gridLivelliContent;
                break;
            default:
                break;
        }

        return $results;
    }

    public function postApriForm() {
        Out::setFocus('', $this->nameForm . '_CODUTE');
    }

    public function postSqlElenca($filtri, &$sqlParams) {
        $filtri['CODUTE'] = trim($this->formData[$this->nameForm . '_CODUTE']);
        $filtri['NOMEUTE'] = trim($this->formData[$this->nameForm . '_NOMEUTE']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorUtenti($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorUtentiChiave(trim($index), $sqlParams);
    }

    /**
     * Imposta controlli su campi di audit
     * @param string $tipoOperazione Tipo operazione
     */
    protected function controlliAuditDettaglio($tipoOperazione) {
        // Mostra/nasconde audit in funzione del tipo di operazione
        $tipoOperazione === itaModelService::OPERATION_INSERT ? Out::hide($this->nameForm . '_divAudit') : Out::show($this->nameForm . '_divAudit');

//        // Ultima modifica        
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[DATAOPER]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[TIMEOPER]', 'readonly', '0');
    }

}

