<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTree.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBor.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FBA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfRicbilHelper.php';

function cwbBorOrganRicbi() {
    $cwbBorOrganRicbi = new cwbBorOrganRicbi();
    $cwbBorOrganRicbi->parseEvent();
    return;
}

class cwbBorOrganRicbi extends cwbBpaGenTree {

    private $progEnte;
    private $idModOrg;
    private $searchComponentAlias;
    private $searchComponentModel;
    private $authBilad;
    private $ANNO_ELE;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorOrganRicbi';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    protected function initVars() {
        $this->GRID_NAME = 'gridBorOrganRicbi';
        $this->TABLE_NAME = 'BOR_ORGAN';
//        $this->AUTOR_MODULO = 'BOR';
//        $this->AUTOR_NUMERO = 4;
        $this->AUTOR_MODULO = 'FBI';
        $this->AUTOR_NUMERO = 27;
        //Indica quali schermate aprire dopo aver creato, modificato o cancellato un elemento
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        //Indica di memorizzare in sessione se è aperto o meno un dettaglio
        $this->openDetailFlag = true;

        $this->searchOpenElenco = true;

        $this->libDB = new cwbLibDB_BOR();
        $this->libDB_FBA = new cwfLibDB_FBA();

        $this->ricbilHelper = new cwfRicbilHelper();
        $this->prefisso_elenco = 'ELENCOCLICK_'; // Prefisso per gli Elenchi cliccabili nella Grid

        $this->filtriFissi = array('PROGENTE', 'ATTIVO', 'ID_MODORG', 'FLAG_RICBI');
        $this->treeFields = array('IDORGAN', 'DESPORG', 'DATAINIZ', 'DATAFINE', 'ALIAS', 'DESPORG_B', 'MODORG', 'AUTH', 'FLAG_RICBI', 'STAMPA');

        $this->progEnte = cwbParGen::getProgEnte();
        $this->idModOrg = cwbParGen::getModelloOrganizzativo();

        $this->propagateToChild = array(
            'FLAG_RICBI' => 'always'
        );
        $this->excludeFieldsUnderline = array('DATAINIZ', 'DATAFINE', 'ALIAS', 'MODORG', 'FLAG_RICBI');

        $this->searchComponentAlias = cwbParGen::getFormSessionVar($this->nameForm, 'searchComponentAlias');
        if ($this->searchComponentAlias != '') {
            $this->searchComponentModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->searchComponentAlias);
        }
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'searchComponentAlias', $this->searchComponentAlias);
//            cwbParGen::setFormSessionVar($this->nameForm, 'history', $this->history);
        }
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComponents();
                $this->initCombo();
                $this->popolaSearchAOOSelect();
                $this->setModorgSelectGrid();
                $this->popolaSearchModOrgSelect($this->nameForm . '_search_IDMODORG');
                break;
        }
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_ApriNodi':
                        
                        break;

                    case $this->nameForm . '_ChiudiNodi':
                        break;

                    default:
                        // ID Particolari (elemento cliccabile) Controllo Icona Isu Filter di jqGrid 
                        // Pulisco dell'eventuale: $this->nameForm
                        $nome_pulito = strtoupper(str_replace($this->nameForm . '_', '', $_POST['id']));
                        if (strlen($nome_pulito) > strlen($this->prefisso_elenco) && substr($nome_pulito, 0, strlen($this->prefisso_elenco)) == $this->prefisso_elenco) {
                            // Chiave composta da PROG_ELE
                            $PROG_ELE = str_replace(substr($nome_pulito, 0, strlen($this->prefisso_elenco)), "", $nome_pulito);
                            // Apro il Visualizzatore di Elenco Stampato
                            $this->ricbilHelper->visualizza_elenco_richieste($PROG_ELE);
                        }
                        break;
                }
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
                }
        }
    }

    private function initComponents() {
        $this->searchComponentAlias = $this->nameForm . '_SOSelectorComponent';
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_search_LxORGdiv', '', '', $this->searchComponentAlias);
        $this->searchComponentModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->searchComponentAlias);
        $this->searchComponentModel->initSelector(false);
    }

    protected function caricaNodiPrimoLivello($filtri = null) {
        return $this->addAuthCheck($this->libDB->leggiBorOrganRicbiNodiPrimoLivello($this->progEnte, $filtri['ID_MODORG'], $filtri['FLAG_RICBI']));
    }

    protected function caricaFigli($idPadre) {
        return $this->addAuthCheck($this->libDB->leggiBorOrganRicbiFigli($idPadre));
    }

    protected function getLivello($id) {
        return $this->libDB->getLivelloBorOrganRicbi($id);
    }

    protected function caricaAlbero($filtri) {
        return $this->addAuthCheck($this->libDB->leggiBorOrganRicbi($filtri));
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
            $padre = $this->addAuthCheck($this->libDB->leggiBorOrganRicbiPadre($nodo));
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
        $this->SQL = $this->libDB->getSqlLeggiBorOrganRicbiChiave($index, $sqlParams);
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
                break;
            case 'FLAG_RICBI':
                switch ($row['FLAG_RICBI']) {
                    case 0:
                        $ricbi = 'Attivo';
                        break;
                    case 1:
                        $ricbi = 'Bloccato';
                        break;
                    default:
                        $ricbi = 'Attivo';
                        break;
                }
                return $ricbi;
                break;
            case 'STAMPA':
                $ris = $this->controlloElencoStampato($row);
                switch ($ris['stampato']) {
                    case 0:
                        $ricele = '<span title="Elenco non stampato" style="color: red; font-size: 18px;" class="ui-icon ui-icon-circle-close"></span>';
                        break;
                    case 1:
                        $colore = 'green';
                        $tooltip = '';
                        if ($ris['PROG_ELE'] == 0) {
                            $tooltip .= ' in livelli superiori';
                        }
                        if ($row['FLAG_RICBI'] == 0) {
                            $colore = 'yellow';
                            $tooltip .= ' ma ATTIVO anzichè bloccato';
                        }

                        $ricele = '<span title="Elenco stampato' . $tooltip . '" style="color: ' . $colore . '; font-size: 18px;" class="ui-icon ui-icon-circle-check"></span>';
                        if (!empty($ris['PROG_ELE'])) {
                            $PROG_ELE = $ris['PROG_ELE'];
                            if (empty($ris['firmato'])) {
                                $ricele .= $this->crea_cliccabile($this->prefisso_elenco . $PROG_ELE, 'Consulta Elenco Stampato e non Firmato (Numero ' . $PROG_ELE . ')', 'ui-icon-print');
                            } else {
                                $ricele .= $this->crea_cliccabile($this->prefisso_elenco . $PROG_ELE, 'Consulta Elenco Stampato e Firmato (Numero ' . $PROG_ELE . ')', 'ui-icon-shield');
                            }
                        }
                        break;
                    case 2:
                        if ($ris['tuttistampati'] == true) {
                            $ricele = '<span title="Elenco stampato in livelli inferiori" style="color: green; font-size: 18px;" class="ui-icon ui-icon-circle-triangle-e"></span>';
                        } else {
                            $ricele = '<span title="Almeno un elenco stampato in livelli inferiori" style="color: orange; font-size: 18px;" class="ui-icon ui-icon-circle-triangle-e"></span>';
                        }
                        break;
                }
                return $ricele;
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
        if (isSet($_POST['FLAG_RICBI']) && $_POST['FLAG_RICBI'] != '') {
            $this->gridFilters['FLAG_RICBI'] = $this->formData['FLAG_RICBI'] - 1;
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
        $figli = $this->libDB->leggiBorOrganRicbiFigli($row['IDORGAN']);
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
        $l1 = (trim($this->CURRENT_RECORD['L1ORG']) != '00' ? trim($this->CURRENT_RECORD['L1ORG']) : null);
        $l2 = (trim($this->CURRENT_RECORD['L2ORG']) != '00' ? trim($this->CURRENT_RECORD['L2ORG']) : null);
        $l3 = (trim($this->CURRENT_RECORD['L3ORG']) != '00' ? trim($this->CURRENT_RECORD['L3ORG']) : null);
        $l4 = (trim($this->CURRENT_RECORD['L4ORG']) != '00' ? trim($this->CURRENT_RECORD['L4ORG']) : null);

        $this->setLxORGMod($l1, $l2, $l3, $l4);

        $this->setAliasAutocomplete($this->nameForm . '_BOR_ORGAN[ALIAS]');

        Out::valore($this->nameForm . '_IDBORAOO', $this->CURRENT_RECORD['IDBORAOO']);
        Out::valore($this->nameForm . '_ID_MODORG', $this->CURRENT_RECORD['ID_MODORG']);

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
        $result = $this->libDB->leggiBorOrganRicbiAlias($filtri);
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
            $padre = $this->libDB->leggiBorOrganRicbi($filtri, false);

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
        if (isSet($this->formData[$this->nameForm . '_ID_MODORG'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['ID_MODORG'] = $this->formData[$this->nameForm . '_ID_MODORG'];
        }
        if (isSet($this->formData[$this->nameForm . '_IDBORAOO'])) {
            $_POST[$this->nameForm . '_BOR_ORGAN']['IDBORAOO'] = $this->formData[$this->nameForm . '_IDBORAOO'];
        }
    }

    protected function postAggiungi() {
        cwbLibBor::getLxORGData(true, $this->progEnte);
    }

    protected function setVisDettaglio() {
//        $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true);
        $this->setVisControlli(true, false, false, false, false, true, false, true, false, true);
    }

    protected function setVisRicerca() {
        $this->setVisControlli(false, false, true, false, false, false, true, false, false, false);
    }

    protected function setVisRisultato() {
        $this->setVisControlli(false, true, false, false, false, false, false, false, true, false);
    }

//    protected function setVisNuovo() {
//        $divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna
//    }

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

    protected function preAltraRicerca() {
        $this->setAliasAutocomplete($this->nameForm . '_search_ALIAS');
    }

    protected function postConfermaCancella() {
        cwbLibBor::getLxORGData(true, $this->progEnte);
    }

    protected function postResetParametriRicerca() {
        $this->searchComponentModel->setLxORG();
    }

    protected function clearFiltriGrid() {
        parent::clearFiltriGrid();

        Out::valore($this->nameForm . '_gs_MODORG', $_POST[$this->nameForm . '_search_IDMODORG']);
    }

    private function initCombo() {
        Out::select($this->nameForm . '_BOR_ORGAN[FLAG_RICBI]', 1, "0", 1, "0 - Attivo");
        Out::select($this->nameForm . '_BOR_ORGAN[FLAG_RICBI]', 1, "1", 0, "1 - Bloccato");
    }

    protected function controlloElencoStampato($organ) {

        $stampato = false;
        $PROG_ELE = 0;
        $firmato = 0;

        $this->ANNO_ELE = cwbParGen::getAnnoContabile();        // Controllo se Almeno 1 Elenco Stampato per quel Servizio (o Superiori)
        $filtri = array('ANNO_ELE' => $this->ANNO_ELE, 'FLAG_DIS' => 0); // Elenco Valido di un certo Anno
        $filtri_servizio = array('L1ORG' => $organ['L1ORG'], 'L2ORG' => $organ['L2ORG'], 'L3ORG' => $organ['L3ORG'], 'L4ORG' => $organ['L4ORG']);
        // Se ad esempio 04.03.02.00 aggiungere 04.00.00.00 e 04.03.00.00
        $filtri['LXORG_O_SUPERIORE'] = $filtri_servizio;
//        $filtri['LXORG_LISTA'] = $filtri_servizio;

        $ricele = $this->libDB_FBA->leggiFbaRicele($filtri);

        //Se $ricele = vuoto => NON STAMPATO . Se almeno 1 record => STAMPATO

        if ($ricele == null) {
            // controllo esistenza stampe livelli inferiori
            $filtri = array('ANNO_ELE' => $this->ANNO_ELE, 'FLAG_DIS' => 0); // Elenco Valido di un certo Anno
            $filtri['L1ORG'] = $organ['L1ORG'];
            if (!empty($organ['L2ORG']) && $organ['L2ORG'] != '00') {
                $filtri['L2ORG'] = $organ['L2ORG'];
                if (!empty($organ['L3ORG']) && $organ['L3ORG'] != '00') {
                    $filtri['L3ORG'] = $organ['L3ORG'];
                    if (!empty($organ['L4ORG']) && $organ['L4ORG'] != '00') {
                        $filtri['L4ORG'] = $organ['L4ORG'];
                    }
                }
            }
            $ricele = $this->libDB_FBA->leggiFbaRicele($filtri);
            if (!empty($ricele)) {
                $stampato = 2;
                $nfigli = $this->libDB->leggiBorOrganRicbiFigli($organ['IDORGAN']);
                $tuttistampati = true;

                foreach ($nfigli as $figlio) {
                    $trov = false;
                    foreach ($ricele as $st) {
                        if ($st['IDORGAN'] == $figlio['IDORGAN']) {
                            $trov = true;
                            break;
                        }
                    }
                    if ($trov == false) {
                        $tuttistampati = false;
                    }
                }
            } else {
                $stampato = 0;
            }
        } else {
            $dati_elenco = $this->ricbilHelper->elenco_richieste_bilancio_da_servizio($this->ANNO_ELE, $organ);
            if (!empty($dati_elenco['PROG_ELE'])) {
                $PROG_ELE = $dati_elenco['PROG_ELE'];
                $firmato = $dati_elenco['FIRMATO'];
            }
            $stampato = 1;
        }
        return array('stampato' => $stampato, 'PROG_ELE' => $PROG_ELE, 'firmato' => $firmato, 'tuttistampati' => $tuttistampati);
    }

    private function crea_cliccabile($key, $tooltip = 'Visualizza Dati', $icona = 'ui-icon-info') {
        $img = "<span class='ui-icon " . $icona . "'></span>"; // Icona standard UI
        $id = $this->nameForm . "_" . $key;
        $html = "<span id='" . $id . "'>" . $img . "</span>";
        $icona_cliccabile = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $img, array(), $tooltip);
        return $icona_cliccabile;
    }

    protected function postElenca() {
        // Attiva elementi cliccabili
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME);
    }

//    private function caricaFigliRicorsivoRicbi($dati, $chiavi) {
//        $figli = $this->caricaFigli($dati[$chiavi[0]]);
//        if (is_array($figli)) {
//            foreach ($figli as $figlio) {
//                $figli = array_merge($figli, $this->caricaFigliRicorsivoRicbi($figlio, $chiavi));
//            }
//        } else {
//            $figli = array();
//        }
//        return $figli;
//    }

//   //Effettua il caricametno dei modelli collegati a quello principale
//    private function caricaCascataRicbi() {
//        if (!isSet($this->modelData)) {
//            $this->modelData = new itaModelServiceData(new cwbModelHelper());
//        }
//        $this->modelData->addChildrenRecords($this->TABLE_NAME, $this->caricaCascata());
//
//        $data = $this->modelData->getData();
//        $oldCurrentRecord = $this->getOldCurrentRecord($data['CURRENT_RECORD']['tableName'], $data['CURRENT_RECORD']['tableData']);
//
//        $check = false;
//        $figli = array();
//
//
//        if (is_array($this->propagateToChild)) {
//            foreach (array_keys($this->propagateToChild) as $field) {
//                if ($oldCurrentRecord[$field] != $data['CURRENT_RECORD']['tableData'][$field]) {
//                    $check = true;
//                    break;
//                }
//            }
//        }
//
//        if ($check) {
//            $chiavi = $this->getModelService()->getPKs($this->MAIN_DB, $this->TABLE_NAME);
//            $figli = $this->caricaFigliRicorsivoRicbi($data['CURRENT_RECORD']['tableData'], $chiavi);
//
//            if (is_array($figli)) {
//                foreach ($figli as &$figlio) {
//                    $modify = false;
//                    foreach ($this->propagateToChild as $field => $mode) {
//                        if ($mode == 'always') {
//                            if ($figlio[$field] != $data['CURRENT_RECORD']['tableData'][$field]) {
//                                $modify = true;
//                                $figlio[$field] = $data['CURRENT_RECORD']['tableData'][$field];
//                            }
//                        } else {
//                            $action = $this->$mode($data['CURRENT_RECORD']['tableData'], $figlio);
//                            if ($action) {
//                                $modify = true;
//                            }
//                        }
//                    }
//                    if ($modify === false) {
//                        unset($figlio);
//                    }
//                }
//            }
//        }
//        return $figli;
//    }

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