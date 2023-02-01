<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTree.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaNumeratori.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBor.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';
//CEP
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/Cep/cepHelper.class.php';

function cwbBorOrgan() {
    $cwbBorOrgan = new cwbBorOrgan();
    $cwbBorOrgan->parseEvent();
    return;
}

class cwbBorOrgan extends cwbBpaGenTree {

    private $libDB_BTA;
    private $progEnte;
    private $idModOrg;
    private $anno;
    private $history;
    private $gridResponsabili;
    private $gridUtenti;
    private $resposabiliTableName;
    private $utentiTableName;
    private $GRID_NAME_RESPONSABILI;
    private $GRID_NAME_UTENTI;
    private $searchComponentAlias;
    private $searchComponentModel;
    private $authBilad;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorOrgan';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    protected function initVars() {
        if (itaHooks::isActive('citywareHook.php')) {
            $this->GRID_NAME = 'gridBorOrgan';
            $this->GRID_NAME_RESPONSABILI = 'gridBorStores';
            $this->GRID_NAME_UTENTI = 'gridBorUteorg';
            $this->resposabiliTableName = 'BOR_STORES';
            $this->utentiTableName = 'BOR_UTEORG';

            $this->AUTOR_MODULO = 'BOR';
            $this->AUTOR_NUMERO = 4;
            //Indica quali schermate aprire dopo aver creato, modificato o cancellato un elemento
            $this->actionAfterNew = self::GOTO_LIST;
            $this->actionAfterModify = self::GOTO_LIST;
            $this->actionAfterDelete = self::GOTO_LIST;
            //Indica di memorizzare in sessione se è aperto o meno un dettaglio
            $this->openDetailFlag = true;

            $this->searchOpenElenco = true;

            $this->libDB = new cwbLibDB_BOR();
            $this->libDB_BTA = new cwbLibDB_BTA;

            $this->filtriFissi = array('PROGENTE', 'ATTIVO', 'ID_MODORG');
            $this->treeFields = array('IDORGAN', 'DESPORG', 'DATAINIZ', 'DATAFINE', 'ALIAS', 'DESPORG_B', 'MODORG', 'AUTH');

            $this->anno = cwbParGen::getAnnoContabile();
            $this->progEnte = cwbParGen::getProgEnte();
            $this->idModOrg = cwbParGen::getModelloOrganizzativo();
            $this->history = cwbParGen::getFormSessionVar($this->nameForm, 'history');

            $this->gridResponsabili = cwbParGen::getFormSessionVar($this->nameForm, 'gridResponsabili');
            if ($this->gridResponsabili == '') {
                $this->gridResponsabili = array();
            }
            $this->gridUtenti = cwbParGen::getFormSessionVar($this->nameForm, 'gridUtenti');
            if ($this->gridUtenti == '') {
                $this->gridUtenti = array();
            }

            $this->addDescribeRelation($this->resposabiliTableName,
                    array('IDORGAN' => 'IDORGAN'),
                    itaModelServiceData::RELATION_TYPE_ONE_TO_MANY
            );
            $this->addDescribeRelation($this->utentiTableName,
                    array(
                        'L1ORG' => 'L1ORG',
                        'L2ORG' => 'L2ORG',
                        'L3ORG' => 'L3ORG',
                        'L4ORG' => 'L4ORG',
                        'IDORGAN' => 'IDORGAN'
                    ),
                    itaModelServiceData::RELATION_TYPE_ONE_TO_MANY
            );

            $this->propagateToChild = array(
                'DATAINIZ' => 'childUpdateDataIniz',
                'DATAFINE' => 'childUpdateDataFine',
                'ID_MODORG' => 'always',
                'IDBORAOO' => 'always'
            );
            $this->excludeFieldsUnderline = array('DATAINIZ', 'DATAFINE', 'ALIAS', 'MODORG');

            $this->searchComponentAlias = cwbParGen::getFormSessionVar($this->nameForm, 'searchComponentAlias');
            if ($this->searchComponentAlias != '') {
                $this->searchComponentModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->searchComponentAlias);
            }
        } else {
            $this->GRID_NAME = 'gridBorOrgan';
            $this->TABLE_NAME = 'CEP_ORGAN';
            $this->skipAuth = true;
            $this->searchOpenElenco = true;
            $this->libDB = new cwbLibDB_GENERIC('CEP');
            $this->elencaAutoAudit = true;
            $this->elencaAutoFlagDis = true;
            $this->errorOnEmpty = false;
            $this->closeOnYearChange = true;

            $this->filtriFissi = array('PROGENTE', 'ATTIVO', 'ID_MODORG');
            $this->treeFields = array('IDORGAN', 'DESPORG', 'DATAINIZ', 'DATAFINE', 'ALIAS', 'DESPORG_B', 'MODORG', 'AUTH');

//            $this->anno = cwbParGen::getAnnoContabile();
//            $this->progEnte = cwbParGen::getProgEnte();
            $this->idModOrg = cwbParGen::getModelloOrganizzativo();
            $this->history = cwbParGen::getFormSessionVar($this->nameForm, 'history');

            $this->propagateToChild = array(
                'DATAINIZ' => 'childUpdateDataIniz',
                'DATAFINE' => 'childUpdateDataFine',
                'ID_MODORG' => 'always',
                'IDBORAOO' => 'always'
            );
            $this->excludeFieldsUnderline = array('DATAINIZ', 'DATAFINE', 'ALIAS', 'MODORG');

            $this->searchComponentAlias = cwbParGen::getFormSessionVar($this->nameForm, 'searchComponentAlias');
            if ($this->searchComponentAlias != '') {
                $this->searchComponentModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->searchComponentAlias);
            }
        }
    }

    protected function preDestruct() {
        if ($this->close != true) {
            if (itaHooks::isActive('citywareHook.php')) {
                cwbParGen::setFormSessionVar($this->nameForm, 'gridResponsabili', $this->gridResponsabili);
                cwbParGen::setFormSessionVar($this->nameForm, 'gridUtenti', $this->gridUtenti);
            }
            cwbParGen::setFormSessionVar($this->nameForm, 'searchComponentAlias', $this->searchComponentAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'history', $this->history);
        }
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComponents();
                $this->initGrids();
                if (itaHooks::isActive('citywareHook.php')) {
                    $this->popolaSearchAOOSelect();
                    $this->setModorgSelectGrid();
                    $this->popolaSearchModOrgSelect($this->nameForm . '_search_IDMODORG');
                }
                break;
        }
    }

    protected function customParseEvent() {
        if (itaHooks::isActive('citywareHook.php')) {
            switch ($_POST['event']) {
                case 'onClick':
                    switch ($_POST['id']) {
                        case $this->nameForm . '_BOR_ORGAN[COD_NR_DI]_butt':
                            cwbLib::apriFinestraRicerca('cwbBtaNrd', $this->nameForm, 'returnNumDI', 'NR_DI', true, null, $this->nameFormOrig);
                            break;
                        case $this->nameForm . '_BOR_ORGAN[COD_NR_DL]_butt':
                            cwbLib::apriFinestraRicerca('cwbBtaNrd', $this->nameForm, 'returnNumDL', 'NR_DL', true, null, $this->nameFormOrig);
                            break;
                        case(preg_match('/' . $this->nameForm . '_SEARCH_([0-9]*)/', $_POST['id'], $matches) ? $_POST['id'] : null):
                            $rowId = $matches[1] - 1;
                            cwbLib::apriFinestraRicerca('cwbBorRespo', $this->nameForm, 'returnRespo', $rowId, true, null, $this->nameFormOrig);
                            break;
                        case $this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_showHistory':
                            $this->toggleHistory(true);
                            $this->caricaGridUtenti();
                            break;
                        case $this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_hideHistory':
                            $this->toggleHistory(false);
                            $this->caricaGridUtenti();
                            break;
//                    case(preg_match('/'.$this->nameForm.'_'.$this->GRID_NAME_UTENTI.'_(CODUTE|KRUOLO)_([0-9]*)_butt/',$_POST['id'],$matches) ? $_POST['id'] : null):
//                        switch($matches[1]){
//                            case 'CODUTE':
//                                $page = 'cwbBorUtenti';
//                                break;
//                            case 'KRUOLO':
//                                $page = 'cwbBorRuoli';
//                                break;
//                        }
//                        cwbLib::apriFinestraRicerca($page, $this->nameForm, 'returnUteorgField', $matches[1].'_'.$matches[2], true, null, $this->nameFormOrig);
//                        break;
                    }
                    break;
                case 'onChange':
                    switch ($_POST['id']) {
                        case $this->nameForm . '_search_IDMODORG':
                            Out::valore($this->nameForm . '_gs_MODORG', $_POST[$this->nameForm . '_search_IDMODORG']);
                            break;
                        case $this->nameForm . '_new_L1ORG_select':
                            $l1 = (isSet($this->formData[$this->nameForm . '_new_L1ORG_select']) && trim($this->formData[$this->nameForm . '_new_L1ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L1ORG_select']) : null);
                            $this->setLxORGNew($l1);
                            break;
                        case $this->nameForm . '_new_L2ORG_select':
                            $l1 = (isSet($this->formData[$this->nameForm . '_new_L1ORG_select']) && trim($this->formData[$this->nameForm . '_new_L1ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L1ORG_select']) : null);
                            $l2 = (isSet($this->formData[$this->nameForm . '_new_L2ORG_select']) && trim($this->formData[$this->nameForm . '_new_L2ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L2ORG_select']) : null);
                            $this->setLxORGNew($l1, $l2);
                            break;
                        case $this->nameForm . '_new_L3ORG_select':
                            $l1 = (isSet($this->formData[$this->nameForm . '_new_L1ORG_select']) && trim($this->formData[$this->nameForm . '_new_L1ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L1ORG_select']) : null);
                            $l2 = (isSet($this->formData[$this->nameForm . '_new_L2ORG_select']) && trim($this->formData[$this->nameForm . '_new_L2ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L2ORG_select']) : null);
                            $l3 = (isSet($this->formData[$this->nameForm . '_new_L3ORG_select']) && trim($this->formData[$this->nameForm . '_new_L3ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L3ORG_select']) : null);
                            $this->setLxORGNew($l1, $l2, $l3);
                            break;
                        case $this->nameForm . '_new_L4ORG_select':
                            $l1 = (isSet($this->formData[$this->nameForm . '_new_L1ORG_select']) && trim($this->formData[$this->nameForm . '_new_L1ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L1ORG_select']) : null);
                            $l2 = (isSet($this->formData[$this->nameForm . '_new_L2ORG_select']) && trim($this->formData[$this->nameForm . '_new_L2ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L2ORG_select']) : null);
                            $l3 = (isSet($this->formData[$this->nameForm . '_new_L3ORG_select']) && trim($this->formData[$this->nameForm . '_new_L3ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L3ORG_select']) : null);
                            $l4 = (isSet($this->formData[$this->nameForm . '_new_L4ORG_select']) && trim($this->formData[$this->nameForm . '_new_L4ORG_select']) != '' ? trim($this->formData[$this->nameForm . '_new_L4ORG_select']) : null);
                            $this->setLxORGNew($l1, $l2, $l3, $l4);
                            break;
                        case $this->nameForm . '_BOR_ORGAN[COD_NR_DI]':
                            $diValue = trim($_POST[$this->nameForm . '_BOR_ORGAN']['COD_NR_DI']);
                            $this->setDIValue($diFlag != '', $diValue);
                            break;
                        case $this->nameForm . '_BOR_ORGAN[COD_NR_DL]':
                            $dlValue = trim($_POST[$this->nameForm . '_BOR_ORGAN']['COD_NR_DL']);
                            $this->setDLValue($dlValue != '', $dlValue);
                            break;
                        case $this->nameForm . '_BOR_ORGAN[DETIMPEGN]':
                            $this->setDIValue($this->formData[$this->nameForm . '_BOR_ORGAN']['DETIMPEGN'] != 0);
                            break;
                        case $this->nameForm . '_BOR_ORGAN[DETLIQU]':
                            $this->setDLValue($this->formData[$this->nameForm . '_BOR_ORGAN']['DETLIQU'] != 0);
                            break;
                        case(preg_match('/' . $this->nameForm . '_(DATAINIZ|DATAFINE)_([0-9]*)/', $_POST['id'], $matches) ? $_POST['id'] : null):
                            $subject = $matches[1];
                            $rowId = $matches[2] - 1;
                            $value = $this->formData[$_POST['id']];
                            $this->modificaCampoGridResponsabile($rowId, $subject, $value);
                            break;
//                    case(preg_match('/'.$this->nameForm.'_'.$this->GRID_NAME_UTENTI.'_(CODUTE|KRUOLO|DATAINIZ|DATAFINE)_([0-9]*)/', $_POST['id'], $matches) ? $_POST['id'] : null):
//                        $this->modificaCampoGridUtente($matches[2], $matches[1], $_POST[$_POST['id']]);
//                        break;
                    }
                    break;
                case 'addGridRow':
                    switch ($_POST['id']) {
                        case $this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI:
                            $this->aggiungiResponsabile();
                            break;
                        case $this->nameForm . '_' . $this->GRID_NAME_UTENTI:
                            $this->aggiungiUtente();
                            break;
                    }
                    break;
                case 'viewRowInline':
                    $viewMode = true;
                case 'editRowInline':
                case 'dbClickRow':
                    switch ($_POST['id']) {
                        case $this->nameForm . '_' . $this->GRID_NAME_UTENTI:
                            $this->modificaUtente($_POST['rowid'], $viewMode ?: false);
                            break;
                    }
                    break;
                case 'delGridRow':
                    switch ($_POST['id']) {
                        case $this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI:
                            $rowId = $_POST['rowid'] - 1;
                            $this->cancellaResponsabile($rowId);
                            break;
                        case $this->nameForm . '_' . $this->GRID_NAME_UTENTI:
                            $this->cancellaUtente($_POST['rowid']);
                            break;
                    }
                    break;
                case 'returnRespo':
                    $rowId = $this->elementId;
                    $this->setResponsabile($rowId, $this->formData);
                    break;
                case 'returnNumDI':
                    $this->setDIValue(true, $this->formData['returnData']['COD_NR_D']);
                    break;
                case 'returnNumDL':
                    $this->setDLValue(true, $this->formData['returnData']['COD_NR_D']);
                    break;
//            case 'returnUteorgField':
//                $index = explode('_', $_POST['id']);
//                $this->modificaCampoGridUtente($index[1], $index[0], $this->formData['returnData'][$index[0]]);
//                break;
                case 'returnUtente':
                    $this->returnUtente($_POST['id'], $this->formData['returnData']);
                    break;
            }
//        $this->searchComponentModel->parseEvent();
        }
    }

    private function initComponents() {
        $this->searchComponentAlias = $this->nameForm . '_SOSelectorComponent';
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_search_LxORGdiv', '', '', $this->searchComponentAlias);

        $this->searchComponentModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->searchComponentAlias);
        $this->searchComponentModel->initSelector(false);
    }

    protected function caricaNodiPrimoLivello($filtri = null) {
        return $this->addAuthCheck($this->libDB->leggiBorOrganNodiPrimoLivello($this->progEnte, $filtri['ID_MODORG']));
    }

    protected function caricaFigli($idPadre) {
        return $this->addAuthCheck($this->libDB->leggiBorOrganFigli($idPadre));
    }

    protected function getLivello($id) {
        return $this->libDB->getLivelloBorOrgan($id);
    }

    protected function caricaAlbero($filtri) {
        return $this->addAuthCheck($this->libDB->leggiBorOrgan($filtri));
    }

    private function addAuthCheck($data) {
        if (is_array($data) && !empty($data)) {
            if (!isSet($this->authBilad)) {
                $authHelper = new cwfAuthHelper();
                $this->authBilad = $authHelper->checkAuthBilad();
            }

            if (is_array($data[0])) {
                foreach ($data as &$row) {
                    $row['AUTH'] = '';

                    if (isSet($this->authBilad['GLOBAL']) && $this->authBilad['GLOBAL'] == true) {
                        $row['AUTH'] = '<span class="ui-icon ui-icon-check"></span>';
                    } elseif (!empty($this->authBilad['ORGAN'])) {
                        foreach ($this->authBilad['ORGAN'] as $organ) {
                            if ($organ['L1ORG'] == $row['L1ORG'] &&
                                    ($organ['L2ORG'] == '00' || $organ['L2ORG'] == $row['L2ORG']) &&
                                    ($organ['L3ORG'] == '00' || $organ['L3ORG'] == $row['L3ORG']) &&
                                    ($organ['L4ORG'] == '00' || $organ['L4ORG'] == $row['L4ORG'])) {
                                $row['AUTH'] = '<span class="ui-icon ui-icon-check"></span>';
                                break;
                            }
                        }
                    }
                }
            } else {
                $data['AUTH'] == '';

                if (isSet($this->authBilad['GLOBAL']) && $this->authBilad['GLOBAL'] == true) {
                    $data['AUTH'] = '<span class="ui-icon ui-icon-check"></span>';
                } elseif (!empty($this->authBilad['ORGAN'])) {
                    foreach ($this->authBilad['ORGAN'] as $organ) {
                        if ($organ['L1ORG'] == $data['L1ORG'] &&
                                ($organ['L2ORG'] == '00' || $organ['L2ORG'] == $data['L2ORG']) &&
                                ($organ['L3ORG'] == '00' || $organ['L3ORG'] == $data['L3ORG']) &&
                                ($organ['L4ORG'] == '00' || $organ['L4ORG'] == $data['L4ORG'])) {
                            $data['AUTH'] = '<span class="ui-icon ui-icon-check"></span>';
                            break;
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function caricaGerarchiaNodo($nodo, &$albero) {
        $livello = intval(trim($nodo['NODO']));
        if ($livello == 1) {
            return;
        }

        // controlla se nell'albero e' caricato il padre del nodo selezionato
        $padre = $this->cercaPadre($nodo, $albero, $livello);
        if (!$padre) {
            $padre = $this->addAuthCheck($this->libDB->leggiBorOrganPadre($nodo));
            $albero = array_merge($albero, array($padre));
        }
        $this->caricaGerarchiaNodo($padre, $albero);
    }

    private function cercaPadre($nodo, $albero, $livello) {
        foreach ($albero as $row) {
            switch ($livello) {
                case 2:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] == '00');
                    break;
                case 3:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] == $nodo['L2ORG'] && $row['L3ORG'] == '00');
                    break;
                case 4:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] == $nodo['L2ORG'] && $row['L3ORG'] == $nodo['L3ORG'] && $row['L4ORG'] == '00');
                    break;
            }
            if ($trovato) {
                return $row;
            }
        }
        return false;
    }

    protected function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorOrganChiave($index, $sqlParams);
    }

    protected function formatValue($key, $value, $row) {
        switch ($key) {
            case 'DESPORG':
                $dataInizio = strtotime($row['DATAINIZ']);
                $dataFine = strtotime($row['DATAFINE']);

                $desc = array();
                for ($i = 1; $i <= $row['NODO']; $i++) {
                    $desc[] = $row['L' . $i . 'ORG'];
                }
                $desc = implode(".", $desc);
                $desc .= ' - ' . $row['DESPORG'];
                if ($dataInizio > time() || ($dataFine && $dataFine < time())) {
                    $desc = '<span style="color: red">' . $desc . '</span>';
                }
                return $desc;
                break;
            case 'DATAINIZ':
                $dataInizio = strtotime($row['DATAINIZ']);
                return date('d/m/Y', $dataInizio);
                break;
            case 'DATAFINE':
                $dataFine = strtotime($row['DATAFINE']);
                if ($dataFine) {
                    return date('d/m/Y', $dataFine);
                } else {
                    return '';
                }
            default:
                return $value;
        }
    }

    //Filtri pagina di ricerca
    protected function initFiltriRicerca() {
        $filtri = $this->gridFilters;
        if (empty($filtri['IDBORAOO']) && !empty($this->formData[$this->nameForm . '_search_IDBORAOO'])) {
            $filtri['IDBORAOO'] = trim($this->formData[$this->nameForm . '_search_IDBORAOO']);
        }
        if (empty($filtri['DESPORG']) && !empty($this->formData[$this->nameForm . '_search_DESPORG'])) {
            $filtri['DESPORG'] = trim($this->formData[$this->nameForm . '_search_DESPORG']);
        }
        if (empty($filtri['ID_MODORG']) && !empty($this->formData[$this->nameForm . '_search_IDMODORG'])) {
            $filtri['ID_MODORG'] = trim($this->formData[$this->nameForm . '_search_IDMODORG']);
        }
        if (empty($filtri['ALIAS']) && !empty($this->formData[$this->nameForm . '_search_ALIAS'])) {
            $filtri['ALIAS'] = trim($this->formData[$this->nameForm . '_search_ALIAS']);
        }
        if (empty($filtri['L1ORG']) && !empty($this->formData[$this->searchComponentAlias . '_selector_s_L1ORG'])) {
            $filtri['L1ORG'] = trim($this->formData[$this->searchComponentAlias . '_selector_s_L1ORG']);
        }
        if (empty($filtri['L2ORG']) && !empty($this->formData[$this->searchComponentAlias . '_selector_s_L2ORG'])) {
            $filtri['L2ORG'] = trim($this->formData[$this->searchComponentAlias . '_selector_s_L2ORG']);
        }
        if (empty($filtri['L3ORG']) && !empty($this->formData[$this->searchComponentAlias . '_selector_s_L3ORG'])) {
            $filtri['L3ORG'] = trim($this->formData[$this->searchComponentAlias . '_selector_s_L3ORG']);
        }
        if (empty($filtri['L4ORG']) && !empty($this->formData[$this->searchComponentAlias . '_selector_s_L4ORG'])) {
            $filtri['L4ORG'] = trim($this->formData[$this->searchComponentAlias . '_selector_s_L4ORG']);
        }
        $this->compilaFiltri($filtri);
        return $filtri;
    }

    protected function postExternalFilter() {
        $externalParams = $this->getExternalParamsNormalyzed();

        if (isSet($externalParams['IDBORAOO'])) {
            $this->formData[$this->nameForm . '_search_IDBORAOO'] = $externalParams['IDBORAOO']['VALORE'];
            Out::valore($this->nameForm . '_search_IDBORAOO', $externalParams['IDBORAOO']['VALORE']);
        }

        if (isSet($externalParams['ID_MODORG'])) {
            $this->formData[$this->nameForm . '_search_IDMODORG'] = $externalParams['ID_MODORG']['VALORE'];
            Out::valore($this->nameForm . '_search_IDMODORG', $externalParams['ID_MODORG']['VALORE']);
        }

        if (isSet($externalParams['DESPORG'])) {
            $this->formData[$this->nameForm . '_search_DESPORG'] = $externalParams['DESPORG']['VALORE'];
            Out::valore($this->nameForm . '_search_DESPORG', $externalParams['DESPORG']['VALORE']);
        }

        if (isSet($externalParams['ALIAS'])) {
            $this->formData[$this->nameForm . '_search_ALIAS'] = $externalParams['ALIAS']['VALORE'];
            Out::valore($this->nameForm . '_search_ALIAS', $externalParams['ALIAS']['VALORE']);
        }

        $l1 = $l2 = $l3 = $l4 = null;
        if (isSet($externalParams['L1ORG'])) {
            $l1 = $externalParams['L1ORG']['VALORE'];
        }
        if (isSet($externalParams['L2ORG'])) {
            $l2 = $externalParams['L2ORG']['VALORE'];
        }
        if (isSet($externalParams['L3ORG'])) {
            $l3 = $externalParams['L3ORG']['VALORE'];
        }
        if (isSet($externalParams['L4ORG'])) {
            $l4 = $externalParams['L4ORG']['VALORE'];
        }
        $this->searchComponentModel->setLxORG($l1, $l2, $l3, $l4);

        if (isSet($externalParams['AUTH'])) {
            $this->formData['AUTH'] = $_POST['AUTH'] = $externalParams['AUTH']['VALORE'];
            Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'AUTH', $externalParams['AUTH']['VALORE']);
        }
    }

    protected function preApriForm() {
        if (!empty($this->returnModel) && !isSet($this->externalParams['AUTH'])) {
            $this->formData['AUTH'] = $_POST['AUTH'] = 1;
            Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'AUTH', 1);
        }
    }

    //Filtri della table
    protected function setGridFilters() {
        $this->gridFilters = array();
        if (isSet($_POST['DESPORG']) && $_POST['DESPORG'] != '') {
            $this->gridFilters['DESPORG'] = $this->formData['DESPORG'];
        }
        if (isSet($_POST['DESPORG_B']) && $_POST['DESPORG_B'] != '') {
            $this->gridFilters['DESPORG_B'] = $this->formData['DESPORG_B'];
        }
        if (isSet($_POST['ALIAS']) && $_POST['ALIAS'] != '') {
            $this->gridFilters['ALIAS'] = $this->formData['ALIAS'];
        }
        if (!empty($_POST['AUTH'])) {
            $this->gridFilters['AUTH'] = $this->setFiltersAuth();
        }
        $this->gridFilters['ID_MODORG'] = $_POST['MODORG'];
        Out::valore($this->nameForm . '_search_IDMODORG', $_POST['MODORG']);
    }

    private function setFiltersAuth() {
        if (!isSet($this->authBilad)) {
            $authHelper = new cwfAuthHelper();
            $this->authBilad = $authHelper->checkAuthBilad();
        }

        if (isSet($this->authBilad['GLOBAL'])) {
            return $this->authBilad['GLOBAL'];
        } else {
            return array_values($this->authBilad['ORGAN']);
        }
    }

    protected function postElaboraNodiCaricati($data) {
        usort($data, 'compareBorOrganNode');
        return $data;
    }

    protected function livelloDaNodo($nodo) {
        return intval(trim($nodo['NODO'])) - 1;
    }

    protected function parentDaAlbero($nodo, $albero) {
        $livello = $this->livelloDaNodo($nodo) + 1;
        $padre = $this->cercaPadre($nodo, $albero, $livello);
        return $padre['IDORGAN'];
    }

    protected function getFoglia($row) {
        $figli = $this->libDB->leggiBorOrganFigli($row['IDORGAN']);
        return empty($figli);
    }

    protected function expandedDaCaricamento($nodo, $albero) {
        // Controlla se presenti figli nell'albero caricato per il nodo corrente
        $livello = intval(trim($nodo['NODO']));
        foreach ($albero as $row) {
            switch ($livello) {
                case 1:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] <> '00');
                    break;
                case 2:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] == $nodo['L2ORG'] && $row['L3ORG'] <> '00');
                    break;
                case 3:
                    $trovato = ($row['L1ORG'] == $nodo['L1ORG'] && $row['L2ORG'] == $nodo['L2ORG'] && $row['L3ORG'] == $nodo['L3ORG'] && $row['L4ORG'] <> '00');
                    break;
            }
            if ($trovato) {
                return 'true';
            }
        }
        return 'false';
    }

    protected function preElenca() {
        $this->setAliasAutocomplete('gs_ALIAS');
    }

    protected function preNuovo() {
        if (itaHooks::isActive('citywareHook.php')) {
            Out::hide($this->nameForm . '_gestione_LivelliModifyDiv');
            Out::show($this->nameForm . '_gestione_LivelliNewDiv');

            $this->azzeraForm();

            $this->popolaModOrgSelect();
            $this->popolaAOOSelect();
            $this->setLxORGNew();
            $this->setDIValue(false);
            $this->setDLValue(false);
        } else {
            Out::hide($this->nameForm . '_gestione_LivelliModifyDiv');
            Out::hide($this->nameForm . '_gestione_LivelliNewDiv');
            Out::hide($this->nameForm . '_ID_MODORG');
            Out::hide($this->nameForm . '_IDBORAOO');
        }
    }

    protected function postNuovo() {
        if (isSet($this->formData[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']) &&
                $this->formData[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'] != 'null') {
            $row = intval($this->formData[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        } else {
            $row = null;
        }

        $this->popolaEnte();
        $this->setAliasAutocomplete($this->nameForm . '_BOR_ORGAN[ALIAS]');

        if (isSet($row)) {
            $filtri = array(
                'IDORGAN' => $row
            );
            $rowData = $this->libDB->leggiBorOrgan($filtri, false);
            $l1 = (trim($rowData['L1ORG']) != '00' ? trim($rowData['L1ORG']) : null);
            $l2 = (trim($rowData['L2ORG']) != '00' ? trim($rowData['L2ORG']) : null);
            $l3 = (trim($rowData['L3ORG']) != '00' ? trim($rowData['L3ORG']) : null);
            $l4 = (trim($rowData['L4ORG']) != '00' ? trim($rowData['L4ORG']) : null);

            $this->setLxORGNew($l1, $l2, $l3, $l4);

            Out::valore($this->nameForm . '_BOR_ORGAN[DATAINIZ]', $rowData['DATAINIZ']);
            if (isSet($rowData['DATAFINE'])) {
                Out::valore($this->nameForm . '_BOR_ORGAN[DATAFINE]', $rowData['DATAFINE']);
            }
        } else {
            Out::valore($this->nameForm . '_BOR_ORGAN[DATAINIZ]', date('Ymd'));
        }

        $this->caricaGridResponsabili();
        $this->caricaGridUtenti();
    }

    protected function preDettaglio($row) {
        if (!isSet($row)) {
            return;
        }

        Out::show($this->nameForm . '_gestione_LivelliModifyDiv');
        Out::hide($this->nameForm . '_gestione_LivelliNewDiv');

        $this->azzeraForm();
        $this->popolaEnte();
        $this->popolaModOrgSelect();
        $this->popolaAOOSelect();
    }

    protected function postDettaglio($row) {
        $this->toggleHistory(false);

        $l1 = (trim($this->CURRENT_RECORD['L1ORG']) != '00' ? trim($this->CURRENT_RECORD['L1ORG']) : null);
        $l2 = (trim($this->CURRENT_RECORD['L2ORG']) != '00' ? trim($this->CURRENT_RECORD['L2ORG']) : null);
        $l3 = (trim($this->CURRENT_RECORD['L3ORG']) != '00' ? trim($this->CURRENT_RECORD['L3ORG']) : null);
        $l4 = (trim($this->CURRENT_RECORD['L4ORG']) != '00' ? trim($this->CURRENT_RECORD['L4ORG']) : null);

        $this->setLxORGMod($l1, $l2, $l3, $l4);

        $this->setAliasAutocomplete($this->nameForm . '_BOR_ORGAN[ALIAS]');

        $diFlag = (isSet($this->CURRENT_RECORD['DETIMPEGN']) && $this->CURRENT_RECORD['DETIMPEGN'] != 0 && $this->CURRENT_RECORD['DETIMPEGN'] != '');
        $diNum = $this->CURRENT_RECORD['COD_NR_DI'];
        $this->setDIValue($diFlag, $diNum);

        $dlFlag = (isSet($this->CURRENT_RECORD['DETLIQU']) && $this->CURRENT_RECORD['DETLIQU'] != 0 && $this->CURRENT_RECORD['DETLIQU'] != '');
        $dlNum = $this->CURRENT_RECORD['COD_NR_DL'];
        $this->setDLValue($dlFlag, $dlNum);


        //effettua il caricamento delle relazioni dal database 
        $arrayValue = $this->getDataRelation();
        $this->gridResponsabili = $arrayValue[$this->resposabiliTableName];
        $this->gridUtenti = $arrayValue[$this->utentiTableName];

        $this->caricaGridResponsabili();
        $this->caricaGridUtenti();

        Out::valore($this->nameForm . '_IDBORAOO', $this->CURRENT_RECORD['IDBORAOO']);
        Out::valore($this->nameForm . '_ID_MODORG', $this->CURRENT_RECORD['ID_MODORG']);

//        $disable = (($this->authenticator->getLevel() !== 'C' && !$this->authenticator->getLevel() !== 'G') || $this->viewMode);
//        if($disable){
//            Out::addClass($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI . '_addGridRow', 'ui-state-disabled');
//            Out::addClass($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI . '_delGridRow', 'ui-state-disabled');
//        }
//        else{
//            Out::delClass($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI . '_addGridRow', 'ui-state-disabled');
//            Out::delClass($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI . '_delGridRow', 'ui-state-disabled');
//        }
        Out::tabSelect($this->nameForm . '_divTab', $this->nameForm . '_divDettagli');
    }

    private function azzeraForm() {
        Out::valore($this->nameForm . '_modify_L1ORG_cod', '');
        Out::valore($this->nameForm . '_modify_L1ORG_desc', '');
        Out::valore($this->nameForm . '_modify_L2ORG_cod', '');
        Out::valore($this->nameForm . '_modify_L2ORG_desc', '');
        Out::valore($this->nameForm . '_modify_L3ORG_cod', '');
        Out::valore($this->nameForm . '_modify_L3ORG_desc', '');
        Out::valore($this->nameForm . '_modify_L4ORG_cod', '');
        Out::valore($this->nameForm . '_modify_L4ORG_desc', '');
        Out::valore($this->nameForm . '_new_L1ORG_select', '');
        Out::valore($this->nameForm . '_new_L1ORG_new_cod', '');
        Out::valore($this->nameForm . '_new_L1ORG_new_desc', '');
        Out::valore($this->nameForm . '_new_L2ORG_select', '');
        Out::valore($this->nameForm . '_new_L2ORG_new_cod', '');
        Out::valore($this->nameForm . '_new_L2ORG_new_desc', '');
        Out::valore($this->nameForm . '_new_L3ORG_select', '');
        Out::valore($this->nameForm . '_new_L3ORG_new_cod', '');
        Out::valore($this->nameForm . '_new_L3ORG_new_desc', '');
        Out::valore($this->nameForm . '_new_L4ORG_select', '');
        Out::valore($this->nameForm . '_new_L4ORG_new_cod', '');
        Out::valore($this->nameForm . '_new_L4ORG_new_desc', '');
        Out::valore($this->nameForm . '_DES_NR_DI', '');
        Out::valore($this->nameForm . '_NUM_NR_DI', '');
        Out::valore($this->nameForm . '_DES_NR_DL', '');
        Out::valore($this->nameForm . '_NUM_NR_DL', '');

        $this->gridResponsabili = array();
        $this->gridUtenti = array();
        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);
    }

    private function popolaEnte() {
        Out::valore($this->nameForm . '_PROGENTE_decod', cwbParGen::getDesente());
        Out::valore($this->nameForm . '_BOR_ORGAN[PROGENTE]', cwbParGen::getProgEnte());
    }

    private function popolaAOOSelect() {
        $filtri = array(
            'PROGENTE' => $this->progEnte
        );

        $aoo = $this->libDB->leggiBorAoo($filtri);

        Out::html($this->nameForm . '_IDBORAOO', '');
        foreach ($aoo as $row) {
            Out::select($this->nameForm . '_IDBORAOO', 1, $row['IDAOO'], false, $row['DESAOO']);
        }
        Out::valore($this->nameForm . '_BOR_ORGAN[IDBORAOO]', $row['IDAOO']);
    }

    private function popolaModOrgSelect() {
        $filtri = array(
            'PROGENTE' => $this->progEnte
        );
        $modorg = $this->libDB->leggiBorModorg($filtri);

        Out::html($this->nameForm . '_ID_MODORG', '');
        foreach ($modorg as $row) {
            Out::select($this->nameForm . '_ID_MODORG', 1, $row['IDMODORG'], false, $row['DESCRIZ']);
        }
        Out::valore($this->nameForm . '_BOR_ORGAN[ID_MODORG]', $row['IDMODORG']);
    }

    private function setAliasAutocomplete($textField) {
        $filtri = array(
            'PROGENTE' => $this->progEnte
        );
        $result = $this->libDB->leggiBorOrganAlias($filtri);
        $aliases = array();
        foreach ($result as $row) {
            if (trim($row['ALIAS']) != '') {
                $aliases[] = utf8_encode($row['ALIAS']);
            }
        }

        cwbLibHtml::autocompleteField($textField, $aliases);
    }

    private function setLxORGNew($l1 = null, $l2 = null, $l3 = null, $l4 = null) {
        if (!isSet($l1) || $l1 == '' || $l1 == 'new') {
            $l1 = 'new';
            $l2 = $l3 = $l4 = null;
        } elseif (!isSet($l2) || $l2 == '' || $l2 == 'new') {
            $l2 = 'new';
            $l3 = $l4 = null;
        } elseif (!isSet($l3) || $l3 == '' || $l3 == 'new') {
            $l3 = 'new';
            $l4 = null;
        } elseif (!isSet($l4) || $l4 == '' || $l4 == 'new') {
            $l4 = 'new';
        }

        if (isSet($l1) && $l1 != '' && $l1 != 'new') {
            $filtri = array(
                'PROGENTE' => $this->progEnte,
                'NODO' => 1,
                'L1ORG' => $l1
            );
            $padre = $this->libDB->leggiBorOrgan($filtri, false);

            Out::disableField($this->nameForm . '_ID_MODORG');
            Out::valore($this->nameForm . '_ID_MODORG', $padre['ID_MODORG']);
            Out::valore($this->nameForm . '_BOR_ORGAN[ID_MODORG]', $padre['ID_MODORG']);
            Out::disableField($this->nameForm . '_IDBORAOO');
            Out::valore($this->nameForm . '_IDBORAOO', $padre['IDBORAOO']);
            Out::valore($this->nameForm . '_BOR_ORGAN[IDBORAOO]', $padre['IDBORAOO']);
        } else {
            Out::enableField($this->nameForm . '_ID_MODORG');
            Out::valore($this->nameForm . '_ID_MODORG', $this->idModOrg);
            Out::valore($this->nameForm . '_BOR_ORGAN[ID_MODORG]', $this->idModOrg);
            Out::enableField($this->nameForm . '_IDBORAOO');
            Out::valore($this->nameForm . '_IDBORAOO', '');
            Out::valore($this->nameForm . '_BOR_ORGAN[IDBORAOO]', '');
        }

        $strutture = cwbLibBor::getLxORGData(false, $this->progEnte);

        if (!isSet($l1) || $l1 == 'new') {
            $codiciL1 = array_keys($strutture);
            array_unshift($codiciL1, 'new');

            Out::html($this->nameForm . '_new_L1ORG_select', '');
            foreach ($codiciL1 as $codice) {
                if ($codice === 0)
                    continue;
                switch ($codice) {
                    case 'new':
                        $desc = '--- Nuovo ---';
                        break;
                    default:
                        $desc = $codice . ' - ' . $strutture[$codice][0];
                        break;
                }
                Out::select($this->nameForm . '_new_L1ORG_select', 1, $codice, $codice == $l1, $desc);
            }

            Out::hide($this->nameForm . '_new_L1ORG_newDiv');
            Out::hide($this->nameForm . '_new_L2ORG');
            Out::hide($this->nameForm . '_new_L2ORG_newDiv');
            Out::hide($this->nameForm . '_new_L3ORG');
            Out::hide($this->nameForm . '_new_L3ORG_newDiv');
            Out::hide($this->nameForm . '_new_L4ORG');
            Out::hide($this->nameForm . '_new_L4ORG_newDiv');

            Out::valore($this->nameForm . '_new_L1ORG_select', '');
            Out::valore($this->nameForm . '_new_L1ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L1ORG_new_desc', '');
            Out::valore($this->nameForm . '_new_L2ORG_select', '');
            Out::valore($this->nameForm . '_new_L2ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L2ORG_new_desc', '');
            Out::valore($this->nameForm . '_new_L3ORG_select', '');
            Out::valore($this->nameForm . '_new_L3ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L3ORG_new_desc', '');
            Out::valore($this->nameForm . '_new_L4ORG_select', '');
            Out::valore($this->nameForm . '_new_L4ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L4ORG_new_desc', '');
        }

        if (isSet($l1)) {
            Out::valore($this->nameForm . '_new_L1ORG_select', $l1);

            if ($l1 == 'new') {
                Out::show($this->nameForm . '_new_L1ORG_newDiv');
            } else {
                Out::hide($this->nameForm . '_new_L1ORG_newDiv');

                if ($l1 != 'new' && $l1 != '') {
                    Out::show($this->nameForm . '_new_L2ORG');

                    $codiciL2 = array_keys($strutture[$l1]);
                    array_unshift($codiciL2, 'new');

                    Out::html($this->nameForm . '_new_L2ORG_select', '');
                    foreach ($codiciL2 as $codice) {
                        if ($codice === 0)
                            continue;
                        switch ($codice) {
                            case 'new':
                                $desc = '--- Nuovo ---';
                                break;
                            default:
                                $desc = $codice . ' - ' . $strutture[$l1][$codice][0];
                                break;
                        }
                        Out::select($this->nameForm . '_new_L2ORG_select', 1, $codice, $codice == $l2, $desc);
                    }
                }
            }
        } else {
            Out::hide($this->nameForm . '_new_L1ORG_newDiv');

            Out::valore($this->nameForm . '_new_L1ORG_select', '');
            Out::valore($this->nameForm . '_new_L1ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L1ORG_new_desc', '');
        }

        if (isSet($l2)) {
            if ($l2 == 'new') {
                Out::show($this->nameForm . '_new_L2ORG_newDiv');
            } else {
                Out::hide($this->nameForm . '_new_L2ORG_newDiv');

                if ($l2 != 'new' && $l2 != '') {
                    Out::show($this->nameForm . '_new_L3ORG');

                    $codiciL3 = array_keys($strutture[$l1][$l2]);
                    array_unshift($codiciL3, 'new');

                    Out::html($this->nameForm . '_new_L3ORG_select', '');
                    foreach ($codiciL3 as $codice) {
                        if ($codice === 0)
                            continue;
                        switch ($codice) {
                            case 'new':
                                $desc = '--- Nuovo ---';
                                break;
                            default:
                                $desc = $codice . ' - ' . $strutture[$l1][$l2][$codice][0];
                                break;
                        }
                        Out::select($this->nameForm . '_new_L3ORG_select', 1, $codice, $codice == $l3, $desc);
                    }
                }
            }
        } else {
            if (!isSet($l1) || $l1 == '' || $l1 == 'new') {
                Out::hide($this->nameForm . '_new_L2ORG');
            }
            Out::hide($this->nameForm . '_new_L2ORG_newDiv');

            Out::valore($this->nameForm . '_new_L2ORG_select', '');
            Out::valore($this->nameForm . '_new_L2ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L2ORG_new_desc', '');
        }

        if (isSet($l3)) {
            if ($l3 == 'new') {
                Out::show($this->nameForm . '_new_L3ORG_newDiv');
            } else {
                Out::hide($this->nameForm . '_new_L3ORG_newDiv');

                if ($l3 != 'new' && $l3 != '') {
                    Out::show($this->nameForm . '_new_L4ORG');

                    $codiciL4 = array_keys($strutture[$l1][$l2][$l3]);
                    array_unshift($codiciL4, 'new');

                    Out::html($this->nameForm . '_new_L4ORG_select', '');
                    foreach ($codiciL4 as $codice) {
                        if ($codice === 0)
                            continue;
                        switch ($codice) {
                            case 'new':
                                $desc = '--- Nuovo ---';
                                break;
                            default:
                                $desc = $codice . ' - ' . $strutture[$l1][$l2][$l3][$codice][0];
                                break;
                        }
                        Out::select($this->nameForm . '_new_L4ORG_select', 1, $codice, $codice == $l4, $desc);
                    }
                }
            }
        } else {
            if (!isSet($l2) || $l2 == '' || $l2 == 'new') {
                Out::hide($this->nameForm . '_new_L3ORG');
            }
            Out::hide($this->nameForm . '_new_L3ORG_newDiv');

            Out::valore($this->nameForm . '_new_L3ORG_select', '');
            Out::valore($this->nameForm . '_new_L3ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L3ORG_new_desc', '');
        }

        if (isSet($l4)) {
            if ($l4 == 'new') {
                Out::show($this->nameForm . '_new_L4ORG_newDiv');
            } else {
                Out::hide($this->nameForm . '_new_L4ORG_newDiv');
            }
        } else {
            if (!isSet($l3) || $l3 == '' || $l3 == 'new') {
                Out::hide($this->nameForm . '_new_L4ORG');
            }
            Out::hide($this->nameForm . '_new_L4ORG_newDiv');

            Out::valore($this->nameForm . '_new_L4ORG_select', '');
            Out::valore($this->nameForm . '_new_L4ORG_new_cod', '');
            Out::valore($this->nameForm . '_new_L4ORG_new_desc', '');
        }
    }

    private function setLxORGMod($l1 = null, $l2 = null, $l3 = null, $l4 = null) {
        if (!isSet($l1)) {
            $l2 = $l3 = $l4 = null;
        } elseif (!isSet($l2)) {
            $l3 = $l4 = null;
        } elseif (!isSet($l3)) {
            $l4 = null;
        }

        if (isSet($l2)) {
            Out::disableField($this->nameForm . '_ID_MODORG');
            Out::disableField($this->nameForm . '_IDBORAOO');
        } else {
            Out::enableField($this->nameForm . '_ID_MODORG');
            Out::enableField($this->nameForm . '_IDBORAOO');
        }

        $strutture = cwbLibBor::getLxORGData(false, $this->progEnte);

        if (isSet($l1)) {
            Out::valore($this->nameForm . '_modify_L1ORG_cod', $l1);
            Out::valore($this->nameForm . '_modify_L1ORG_desc', $strutture[$l1][0]);
            if (!isSet($l2)) {
                Out::enableField($this->nameForm . '_modify_L1ORG_desc');
            } else {
                Out::disableField($this->nameForm . '_modify_L1ORG_desc');
            }
        }

        if (isSet($l2)) {
            Out::show($this->nameForm . '_modify_L2ORG');

            Out::valore($this->nameForm . '_modify_L2ORG_cod', $l2);
            Out::valore($this->nameForm . '_modify_L2ORG_desc', $strutture[$l1][$l2][0]);
            if (!isSet($l3)) {
                Out::enableField($this->nameForm . '_modify_L2ORG_desc');
            } else {
                Out::disableField($this->nameForm . '_modify_L2ORG_desc');
            }
        } else {
            Out::hide($this->nameForm . '_modify_L2ORG');
        }

        if (isSet($l3)) {
            Out::show($this->nameForm . '_modify_L3ORG');

            Out::valore($this->nameForm . '_modify_L3ORG_cod', $l3);
            Out::valore($this->nameForm . '_modify_L3ORG_desc', $strutture[$l1][$l2][$l3][0]);
            if (!isSet($l4)) {
                Out::enableField($this->nameForm . '_modify_L3ORG_desc');
            } else {
                Out::disableField($this->nameForm . '_modify_L3ORG_desc');
            }
        } else {
            Out::hide($this->nameForm . '_modify_L3ORG');
        }

        if (isSet($l4)) {
            Out::show($this->nameForm . '_modify_L4ORG');

            Out::valore($this->nameForm . '_modify_L4ORG_cod', $l4);
            Out::valore($this->nameForm . '_modify_L4ORG_desc', $strutture[$l1][$l2][$l3][$l4][0]);
            Out::enableField($this->nameForm . '_modify_L4ORG_desc');
        } else {
            Out::hide($this->nameForm . '_modify_L4ORG');
        }
    }

    private function setDIValue($flag = false, $numeratore = null) {
        Out::valore($this->nameForm . '_BOR_ORGAN[DETIMPEGN]', $flag);
        if (!$flag) {
            Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DI]', '');
            Out::valore($this->nameForm . '_DES_NR_DI', '');
            Out::valore($this->nameForm . '_NUM_NR_DI', '');
            Out::hide($this->nameForm . '_DETIMPEGNdiv');
        } else {
            Out::show($this->nameForm . '_DETIMPEGNdiv');

            if (isSet($numeratore)) {
                $filtri = array(
                    'COD_NR_D' => $numeratore
                );
                $numeratoreData = $this->libDB_BTA->leggiBtaNrd($filtri, false);
                $calcoloNumeratori = new cwbBtaNumeratori();
                $nrDI = $calcoloNumeratori->calcolaNumeratore($this->anno, $numeratore, $numeratoreData['SETT_IVA'], false, false, true);

                Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DI]', $numeratore);
                Out::valore($this->nameForm . '_DES_NR_DI', $numeratoreData['DES_NR_D']);
                Out::valore($this->nameForm . '_NUM_NR_DI', $nrDI['NUMULTDOC']);
            } else {
                Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DI]', '');
                Out::valore($this->nameForm . '_DES_NR_DI', '');
                Out::valore($this->nameForm . '_NUM_NR_DI', '');
            }
        }
    }

    private function setDLValue($flag = false, $numeratore = null) {
        Out::valore($this->nameForm . '_BOR_ORGAN[DETLIQU]', $flag);
        if (!$flag) {
            Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DL]', '');
            Out::valore($this->nameForm . '_DES_NR_DL', '');
            Out::valore($this->nameForm . '_NUM_NR_DL', '');
            Out::hide($this->nameForm . '_DETLIQUNdiv');
        } else {
            Out::show($this->nameForm . '_DETLIQUNdiv');

            if (isSet($numeratore)) {
                $filtri = array(
                    'COD_NR_D' => $numeratore
                );
                $numeratoreData = $this->libDB_BTA->leggiBtaNrd($filtri, false);
                $calcoloNumeratori = new cwbBtaNumeratori();
                $nrDI = $calcoloNumeratori->calcolaNumeratore($this->anno, $numeratore, $numeratoreData['SETT_IVA'], false, false, true);

                Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DL]', $numeratore);
                Out::valore($this->nameForm . '_DES_NR_DL', $numeratoreData['DES_NR_D']);
                Out::valore($this->nameForm . '_NUM_NR_DL', $nrDI['NUMULTDOC']);
            } else {
                Out::valore($this->nameForm . '_BOR_ORGAN[COD_NR_DL]', '');
                Out::valore($this->nameForm . '_DES_NR_DL', '');
                Out::valore($this->nameForm . '_NUM_NR_DL', '');
            }
        }
    }

    private function aggiornaResponsabile($nomeRes = '', $idrespo = 0) {
        Out::valore($this->nameForm . '_BOR_ORGAN[NOMERES]', $nomeRes);
        Out::valore($this->nameForm . '_BOR_ORGAN[IDRESPO]', $idrespo);
    }

    //effettua il caricamento dal database di tutte le relazioni
    protected function getDataRelation() {
        $valueArray = array();
        $valueArray[$this->resposabiliTableName] = $this->libDB->leggiStoricoResponsabiliOrganigramma(array(
            'IDORGAN' => $this->CURRENT_RECORD['IDORGAN']
        ));
        $valueArray[$this->utentiTableName] = $this->libDB->leggiGeneric('BOR_UTEORG', array(
            'L1ORG' => $this->CURRENT_RECORD['L1ORG'],
            'L2ORG' => $this->CURRENT_RECORD['L2ORG'],
            'L3ORG' => $this->CURRENT_RECORD['L3ORG'],
            'L4ORG' => $this->CURRENT_RECORD['L4ORG']
        ));
        return $valueArray;
    }

    private function caricaGridResponsabili() {
        // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $helper = new cwbBpaGenHelper();
        $helper->setGridName($this->GRID_NAME_RESPONSABILI);
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);

        $this->ordinaResponsabili();
        $records = $this->elaboraResponsabiliRecords($this->gridResponsabili);

        $ita_grid01 = $helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);

        if (($this->authenticator->getLevel() !== 'C' && $this->authenticator->getLevel() !== 'G') || $this->viewMode) {
            Out::disableGrid($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);
        } else {
            Out::enableGrid($this->nameForm . '_' . $this->GRID_NAME_RESPONSABILI);
        }

        $ultimoResponsabile = end($this->gridResponsabili);
        $this->aggiornaResponsabile($ultimoResponsabile['NOMERES'], $ultimoResponsabile['IDRESPO']);
    }

    private function elaboraResponsabiliRecords($records) {
        $htmlRecords = array();

        $auth = !(($this->authenticator->getLevel() !== 'C' && $this->authenticator->getLevel() !== 'G') || $this->viewMode);

        foreach ($records as $key => $value) {
            $htmlRecords[$key] = $value;

            if ($auth) {
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
                $htmlRecords[$key]['SEARCH'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            } else {
                $htmlRecords[$key]['SEARCH'] = '';
            }

            $component = array(
                'id' => 'NOMERES',
                'type' => 'ita-readonly',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                'onChangeEvent' => false,
                'additionalClass' => 'required',
                'properties' => array(
                    'value' => $records[$key]['NOMERES'],
                    'style' => 'width: 300px;'
                )
            );
            $htmlRecords[$key]['NOMERES'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            // data inizio
            $component = array(
                'id' => 'DATAINIZ',
                'type' => 'ita-datepicker',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                //'onChangeEvent' => true,
                'additionalClass' => 'ita-edit-onchange required',
                'properties' => array(
                    'value' => $records[$key]['DATAINIZ'],
                    'style' => 'width: 70px'
                )
            );
            if (!$auth) {
                $component['properties']['disabled'] = 'disabled';
            }
            $htmlRecords[$key]['DATAINIZ'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            // data fine
            $component = array(
                'id' => 'DATAFINE',
                'type' => 'ita-datepicker',
                'model' => $this->nameForm,
                'rowKey' => $key + 1,
                //'onChangeEvent' => true,
                'additionalClass' => 'ita-edit-onchange',
                'properties' => array('value' => $records[$key]['DATAFINE'],
                    'style' => 'width: 70px'
                )
            );
            if (!$auth) {
                $component['properties']['disabled'] = 'disabled';
            }
            $htmlRecords[$key]['DATAFINE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
        }

        return $htmlRecords;
    }

    private function caricaGridUtenti() {
        // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $helper = new cwbBpaGenHelper();
        $helper->setGridName($this->GRID_NAME_UTENTI);
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_UTENTI);

//        $this->ordinaUtenti();
        $records = $this->elaboraUtentiRecords($this->gridUtenti);
        usort($records, function($a, $b) {
            if ($a['CODUTE'] != $b['CODUTE']) {
                $dataInizA = new DateTime($a['DATAINIZ_ORIG']);
                $dataInizB = new DateTime($b['DATAINIZ_ORIG']);
                return ($dataInizA > $dataInizB ? 1 : -1);
            }
            return strcmp(strtoupper($b['CODUTE']), strtoupper($a['CODUTE']));
        });

        $ita_grid01 = $helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME_UTENTI);

        if (($this->authenticator->getLevel() !== 'C' && $this->authenticator->getLevel() !== 'G') || $this->viewMode) {
            Out::disableGrid($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
        } else {
            Out::enableGrid($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
        }
    }

    private function elaboraUtentiRecords($records) {
        if (is_array($records)) {
            foreach ($records as $key => &$row) {
                if ($this->history !== true && !empty($row['DATAFINE'])) {
                    unset($records[$key]);
                    continue;
                }

                if (empty($row['CODUTE'])) {
                    $utente = ' ';
                } else {
                    $utente = $this->libDB->leggiGeneric('BOR_UTENTI', array('CODUTE' => $row['CODUTE']), false);
                    $utente = $utente['NOMEUTE'];
                }
                if (empty($row['KRUOLO'])) {
                    $ruolo = ' ';
                    $desruolo = ' ';
                } else {
                    $r = $this->libDB->leggiGeneric('BOR_RUOLI', array('KRUOLO' => $row['KRUOLO']), false);
//                    $ruolo = $r['KRUOLO'];
                    $desruolo = $r['DES_RUOLO'];
                }

                $row['DATAINIZ_ORIG'] = $row['DATAINIZ'];
                $row['DATAFINE_ORIG'] = $row['DATAFINE'];

                $dataIniz = new DateTime($row['DATAINIZ']);

                if (!empty($row['DATAFINE'])) {
                    $dataFine = new DateTime($row['DATAFINE']);
                    $dataFine = $dataFine->format('d/m/Y');
                } else {
                    $dataFine = '';
                }
//                $row['CUSTOMKEY'] = $row['CODUTE'].'|'.$dataIniz->format('Y-m-d');
                $row['CUSTOMKEY'] = $key;

                $row['NOMEUTE'] .= $utente;

//                $row['KRUOLO'] = $ruolo;
                $row['DES_RUOLO'] = $desruolo;

                $row['DATAINIZ'] = $dataIniz->format('d/m/Y');
                $row['DATAFINE'] = $dataFine;
            }
        }

        return $records;
    }

    private function aggiungiResponsabile() {
        $this->addInsertOperation($this->resposabiliTableName);
        $responsabile = $this->getModelService()->define($this->MAIN_DB, $this->resposabiliTableName);
        $responsabile['DATAINIZ'] = date('Ymd');
        $this->gridResponsabili[] = $responsabile;

        $this->caricaGridResponsabili();
    }

    private function cancellaResponsabile($rowId) {
        $this->addDeleteOperation($this->resposabiliTableName, array('IDSTORES' => $this->gridResponsabili[$rowId]['IDSTORES']));

        unset($this->gridResponsabili[$rowId]);

        $this->caricaGridResponsabili();
    }

    private function modificaCampoGridResponsabile($key, $nomeCampo, $valore) {
        if (!isSet($this->gridResponsabili[$key][$nomeCampo]) || $this->gridResponsabili[$key][$nomeCampo] != $valore) {
            $this->addUpdateOperation($this->resposabiliTableName, array('IDSTORES' => $this->gridResponsabili[$key]['IDSTORES']));

            $this->gridResponsabili[$key][$nomeCampo] = $valore;

            $this->caricaGridResponsabili();
        }
    }

    private function aggiungiUtente() {
        $l1org = $_POST[$this->nameForm . '_BOR_ORGAN']['L1ORG'];
        $l2org = $_POST[$this->nameForm . '_BOR_ORGAN']['L2ORG'];
        $l3org = $_POST[$this->nameForm . '_BOR_ORGAN']['L3ORG'];
        $l4org = $_POST[$this->nameForm . '_BOR_ORGAN']['L4ORG'];

        $model = cwbLib::apriFinestraDettaglioRecord('cwbBorUteorg', $this->nameForm, 'returnUtente', 'new', 'new', true, $this->nameFormOrig);
        $model->setSkipValidateMemory(false);
        $model->setEvent('openform');
        $model->parseEvent();
        $model->setExternalLxORG($l1org, $l2org, $l3org, $l4org);
        $model->disableLxORG();
    }

    private function returnUtente($index, $data) {
        if ($index == 'new') {
            $this->addInsertOperation($this->utentiTableName);
            $this->gridUtenti[] = $data;
        } elseif ($data != 'DELETE') {
            $this->returnModificaUtente($index, $data);
        } else {
            $this->cancellaUtente($index);
        }
        $this->caricaGridUtenti();
    }

    private function modificaUtente($index, $viewMode = false) {
        if (!$viewMode) {
            $viewMode = $this->viewMode;
        }
        $model = cwbLib::apriFinestraDettaglioRecord('cwbBorUteorg', $this->nameForm, 'returnUtente', $index, 'new', true, $this->nameFormOrig);
        $model->setSkipValidateMemory(false);
        $model->setEvent('openform');
        $model->parseEvent();
        $model->openDettaglioExternal($this->gridUtenti[$index], $viewMode);
        $model->disableLxORG();
    }

    private function returnModificaUtente($index, $data) {
        $this->addUpdateOperation($this->utentiTableName, array('IDUTEORG' => $this->gridUtenti[$index]['IDUTEORG']));
            
        $this->gridUtenti[$index] = $data;
    }

    private function cancellaUtente($index) { //TODO
        $this->addDeleteOperation($this->utentiTableName, array('IDUTEORG' => $this->gridUtenti[$index]['IDUTEORG']));

        unset($this->gridUtenti[$index]);

        $this->caricaGridUtenti();
    }

//    private function modificaCampoGridUtente($rowId, $nomeCampo, $valore){ //TODO
//        if(!isSet($this->gridUtenti[$rowId][$nomeCampo]) || $this->gridUtenti[$rowId][$nomeCampo] != $valore){
//            $this->addUpdateOperation($this->utentiTableName, array('IDUTEORG' => $this->gridUtenti[$rowId]['IDUTEORG']));
//
//            switch($nomeCampo){
//                case 'CODUTE':
//                    $filtri = array(
//                        'CODUTE_upper'=>trim($valore)
//                    );
//                    $utente = $this->libDB->leggiGeneric('BOR_UTENTI', $filtri, false);
//                    $this->gridUtenti[$rowId][$nomeCampo] = $utente['CODUTE'];
//                    break;
//                default:
//                    $this->gridUtenti[$rowId][$nomeCampo] = $valore;
//                    break;
//            }
//
//            $this->caricaGridUtenti();
//        }
//    }

    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case $this->resposabiliTableName:
                $results = $this->gridResponsabili;
                break;
            case $this->utentiTableName:
                $results = $this->gridUtenti;
                break;
            default:
                break;
        }

        return $results;
    }

    protected function setResponsabile($key, $data) {
        $this->addUpdateOperation($this->resposabiliTableName, array('IDSTORES' => $this->gridResponsabili[$key]['IDSTORES']));

        Out::valore($this->nameForm . '_NOMERES_' . ($key + 1), $data['returnData']['NOMERES']);
        $this->gridResponsabili[$key]['NOMERES'] = $data['returnData']['NOMERES'];
        $this->gridResponsabili[$key]['PROGRESPO'] = $data['returnData']['PROGRESPO'];
        $this->gridResponsabili[$key]['IDRESPO'] = $data['returnData']['IDRESPO'];

        $ultimoResponsabile = end($this->gridResponsabili);
        $this->aggiornaResponsabile($ultimoResponsabile['NOMERES'], $ultimoResponsabile['IDRESPO']);
    }

    protected function postApriForm() {
        if (!itaHooks::isActive('citywareHook.php')) {
            if (cepHelper::openCwbConfig()) {
                $this->close();
            }
        }
    }

    protected function preAggiorna() {
        $nodo = $this->formData[$this->nameForm . '_BOR_ORGAN']['NODO'];
        $_POST[$this->nameForm . '_BOR_ORGAN']['DESPORG'] = $this->formData[$this->nameForm . '_modify_L' . $nodo . 'ORG_desc'];
        if (isSet($this->formData[$this->nameForm . '_ID_MODORG'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['ID_MODORG'] = $this->formData[$this->nameForm . '_ID_MODORG'];
        }
        if (isSet($this->formData[$this->nameForm . '_IDBORAOO'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['IDBORAOO'] = $this->formData[$this->nameForm . '_IDBORAOO'];
        }
    }

    protected function postAggiorna() {
        cwbLibBor::getLxORGData(true, $this->progEnte);
    }

    protected function preAggiungi() {
        if (!empty($this->formData[$this->nameForm . '_new_L1ORG_new_cod'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['NODO'] = 1;
            $_POST[$this->nameForm . '_BOR_ORGAN']['DESPORG'] = trim($this->formData[$this->nameForm . '_new_L1ORG_new_desc']);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L1ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L1ORG_new_cod']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L2ORG'] = '00';
            $_POST[$this->nameForm . '_BOR_ORGAN']['L3ORG'] = '00';
            $_POST[$this->nameForm . '_BOR_ORGAN']['L4ORG'] = '00';
        } elseif (!empty($this->formData[$this->nameForm . '_new_L2ORG_new_cod'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['NODO'] = 2;
            $_POST[$this->nameForm . '_BOR_ORGAN']['DESPORG'] = trim($this->formData[$this->nameForm . '_new_L2ORG_new_desc']);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L1ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L1ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L2ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L2ORG_new_cod']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L3ORG'] = '00';
            $_POST[$this->nameForm . '_BOR_ORGAN']['L4ORG'] = '00';
        } elseif (!empty($this->formData[$this->nameForm . '_new_L3ORG_new_cod'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['NODO'] = 3;
            $_POST[$this->nameForm . '_BOR_ORGAN']['DESPORG'] = trim($this->formData[$this->nameForm . '_new_L3ORG_new_desc']);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L1ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L1ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L2ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L2ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L3ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L3ORG_new_cod']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L4ORG'] = '00';
        } elseif (!empty($this->formData[$this->nameForm . '_new_L4ORG_new_cod'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['NODO'] = 4;
            $_POST[$this->nameForm . '_BOR_ORGAN']['DESPORG'] = trim($this->formData[$this->nameForm . '_new_L4ORG_new_desc']);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L1ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L1ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L2ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L2ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L3ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L3ORG_select']), 2, '0', STR_PAD_LEFT);
            $_POST[$this->nameForm . '_BOR_ORGAN']['L4ORG'] = str_pad(trim($this->formData[$this->nameForm . '_new_L4ORG_new_cod']), 2, '0', STR_PAD_LEFT);
        }
        if (itaHooks::isActive('citywareHook.php')) {
            if (isSet($this->formData[$this->nameForm . '_ID_MODORG'])) {
                $_POST[$this->nameForm . '_BOR_ORGAN']['ID_MODORG'] = $this->formData[$this->nameForm . '_ID_MODORG'];
            }
            if (isSet($this->formData[$this->nameForm . '_IDBORAOO'])) {
                $_POST[$this->nameForm . '_BOR_ORGAN']['IDBORAOO'] = $this->formData[$this->nameForm . '_IDBORAOO'];
            }
        }
    }

    protected function postAggiungi() {
        cwbLibBor::getLxORGData(true, $this->progEnte);
    }

    protected function setVisDettaglio() {
        $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true);
    }

    protected function setVisNuovo() {
//        $divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna
        $this->setVisControlli(true, false, false, false, true, false, false, false, false, true);
    }

    protected function childUpdateDataIniz($parent, &$child) {
        $parent['DATAINIZ'] = substr(preg_replace("/[^0-9]/", "", $parent['DATAINIZ']), 0, 8);
        $child['DATAINIZ'] = substr(preg_replace("/[^0-9]/", "", $child['DATAINIZ']), 0, 8);

        if ($parent['DATAINIZ'] > $child['DATAINIZ']) {
            $child['DATAINIZ'] = $parent['DATAINIZ'];
            return true;
        }
        return false;
    }

    protected function childUpdateDataFine($parent, &$child) {
        if (isSet($parent['DATAFINE']) && trim($parent['DATAFINE']) != '') {
            $parent['DATAFINE'] = substr(preg_replace("/[^0-9]/", "", $parent['DATAFINE']), 0, 8);
        } else {
            $parent['DATAFINE'] = null;
        }
        if (isSet($child['DATAFINE']) && trim($child['DATAFINE']) != '') {
            $child['DATAFINE'] = substr(preg_replace("/[^0-9]/", "", $child['DATAFINE']), 0, 8);
        } else {
            $child['DATAFINE'] = null;
        }
        if (isSet($parent['DATAFINE']) && (!isSet($child['DATAFINE']) || $child['DATAFINE'] > $parent['DATAFINE'])) {
            $child['DATAFINE'] = $parent['DATAFINE'];
            return true;
        }
        return false;
    }

    private function popolaSearchAOOSelect() {
        $filtri = array(
            'PROGENTE' => $this->progEnte
        );

        $aoo = $this->libDB->leggiBorAoo($filtri);

        Out::html($this->nameForm . '_search_IDBORAOO', '');
        Out::select($this->nameForm . '_search_IDBORAOO', 1, '', false, '--- TUTTE ---');
        foreach ($aoo as $row) {
            Out::select($this->nameForm . '_search_IDBORAOO', 1, $row['IDAOO'], false, $row['DESAOO']);
        }
    }

    private function setModorgSelectGrid() {
//        $this->popolaSearchModOrgSelect('gs_MODORG');
//        $html = '<select name="MODORG" id="'.$this->nameForm.'_gs_MODORG" style="width: 100%;" '.cwbLibHtml::getOnChangeEventJqGridFilter($this->nameForm, $this->GRID_NAME).'></select>';
        $html = '<select name="MODORG" id="' . $this->nameForm . '_gs_MODORG" style="width: 100%;"></select>';
        Out::gridSetColumnFilterHtml($this->nameForm, $this->GRID_NAME, 'MODORG', $html);
        $this->popolaSearchModOrgSelect($this->nameForm . '_gs_MODORG');
    }

    private function popolaSearchModOrgSelect($target) {
        $filtri = array(
            'PROGENTE' => $this->progEnte
        );
        $modorg = $this->libDB->leggiBorModorg($filtri);
        $modelloOrganizzativo = cwbParGen::getModelloOrganizzativo();

        Out::html($target, '');
        Out::select($target, 1, '', false, '--- TUTTI ---');
        foreach ($modorg as $row) {
            Out::select($target, 1, $row['IDMODORG'], $modelloOrganizzativo == $row['IDMODORG'], $row['DESCRIZ']);
        }

        if ($target == $this->nameForm . '_gs_MODORG') {
            $_POST['MODORG'] = $modelloOrganizzativo;
        }
    }

    private function initGrids() {
        $html = 'Data Fine ';
        $html .= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_showHistory', '<span class="ui-icon ui-icon-history"></span>', array(), 'Visualizza cessati');
        $html .= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_hideHistory', '<span class="ui-icon ui-icon-cancel"></span>', array(), 'Nascondi cessati');
        Out::gridSetColumnHeader($this->nameForm, $this->GRID_NAME_UTENTI, 'DATAFINE', $html);
    }

    private function toggleHistory($showHistory) {
        $this->history = $showHistory;
        if ($this->history === true) {
            Out::show($this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_hideHistory');
            Out::hide($this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_showHistory');
        } else {
            Out::hide($this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_hideHistory');
            Out::show($this->nameForm . '_' . $this->GRID_NAME_UTENTI . '_showHistory');
        }
    }

    protected function preAltraRicerca() {
        $this->setAliasAutocomplete($this->nameForm . '_search_ALIAS');
    }

    protected function postConfermaCancella() {
        cwbLibBor::getLxORGData(true, $this->progEnte);
    }

    protected function postResetParametriRicerca() {
        $this->searchComponentModel->setLxORG();
    }

    private function ordinaResponsabili() {
        $storesOrder = array();
        foreach ($this->gridResponsabili as $key => $resp) {
            $storesOrder[$key] = preg_replace("/[^0-9]/", "", $resp['DATAINIZ']);
        }
        array_multisort($storesOrder, SORT_ASC, $this->gridResponsabili);
    }

//    private function ordinaUtenti(){
//        $utentiOrder = array();
//        foreach($this->gridUtenti as $key=>$utente){
//            $utentiOrder[$key] = $utente['CODUTE'];
//        }
//        array_multisort($utentiOrder, SORT_ASC, $this->gridUtenti);
//    }

    protected function clearFiltriGrid() {
        parent::clearFiltriGrid();

        Out::valore($this->nameForm . '_gs_MODORG', $_POST[$this->nameForm . '_search_IDMODORG']);
    }

}

/**
 * Confronto di due record pianta organica
 * @param array $recA Record A
 * @param array $recB Record B
 * @return int esito confronto (-1 se A<B, 0 se uguali, 1 se B>A)
 */
function compareBorOrganNode($recA, $recB) {
    $A = $recA['L1ORG'] . $recA['L2ORG'] . $recA['L3ORG'] . $recA['L4ORG'];
    $B = $recB['L1ORG'] . $recB['L2ORG'] . $recB['L3ORG'] . $recB['L4ORG'];

    return strcmp($A, $B);
}

?>