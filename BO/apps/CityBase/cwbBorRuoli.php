<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBor.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

function cwbBorRuoli() {
    $cwbBorRuoli = new cwbBorRuoli();
    $cwbBorRuoli->parseEvent();
    return;
}

class cwbBorRuoli extends cwbBpaGenTab {

    private $gridUtenti;
    private $utentiTableName;
    private $GRID_NAME_UTENTI;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorRuoli';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    protected function initVars() {
        $this->GRID_NAME = 'gridBorRuoli';
        $this->GRID_NAME_UTENTI = 'gridBorUteorg';
        $this->utentiTableName = 'BOR_UTEORG';

        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 5;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();

        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;

        $this->openDetailFlag = true;

        $this->errorOnEmpty = false;

        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;


        $this->gridUtenti = cwbParGen::getFormSessionVar($this->nameForm, 'gridBorUteorg');
        if ($this->gridUtenti == '') {
            $this->gridUtenti = array();
        }
        $this->addDescribeRelation($this->utentiTableName,
                array(
                    'KRUOLO' => 'KRUOLO'
                ),
                itaModelServiceData::RELATION_TYPE_ONE_TO_MANY
        );
    }

    protected function preNuovo() {
        $this->pulisciCampi();
        Out::hide($this->nameForm . '_divTabUtenti');
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_COD_UFF_decod', '');
        Out::valore($this->nameForm . '_BOR_RUOLI[COD_UFF]', '');
    }

    protected function postNuovo() {
        Out::setFocus("", $this->nameForm . '_BOR_RUOLI[KRUOLO]');
//        Out::hide($this->nameForm . '_BOR_RUOLI[KRUOLO]');
        Out::hide($this->nameForm . '_BOR_RUOLI[KRUOLO]_field');
        Out::hide($this->nameForm . '_Autorizzazioni');

        $this->onChangeTipoOperatore();
    }

    private function onChangeTipoOperatore($value = null) {
        $testo = '';
        switch ($value) {
            case 0:
                $testo = "Ciclo attivo e passivo : l'utente opera con le autorizzazioni e le funzioni proprie.";
                break;
            case 1:
                $testo = "Ciclo passivo : l'utente è autorizzato ad operare sui propri CIG/CUP (Codice utente sul CIG/CUP = utente operatore).";
                break;
            case 2:
                $testo = "Ciclo attivo e passivo : l'utente opera con le autorizzazioni e le funzioni proprie.";
                break;
            case 3:
                $testo = "Bilancio, Ciclo attivo e passivo : L'utente è autorizzato ad operare su tutto l'organigramma con le stesse autorizzazioni dell'area-settore-servizio a cui è associato come default.";
                break;
        }

        Out::html($this->nameForm . '_spanDescrizioneTipoOperatore', $testo);
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_DES_RUOLO');
    }

    protected function postAltraRicerca() {
        $this->pulisciCampi();
        Out::setFocus("", $this->nameForm . '_DES_RUOLO');
        Out::hide($this->nameForm . '_Autorizzazioni');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_RUOLO[DES_RUOLO]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['KRUOLO'] != '') {
            $this->gridFilters['KRUOLO'] = $this->formData['KRUOLO'];
        }
        if ($_POST['DES_RUOLO'] != '') {
            $this->gridFilters['DES_RUOLO'] = $this->formData['DES_RUOLO'];
        }
        if (!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS'] - 1;
        }
        if ($_POST['DIRIGENTE'] != null && $_POST['DIRIGENTE'] > 0) {
            $this->gridFilters['DIRIGENTE'] = $this->formData['DIRIGENTE'] - 1;
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
        $this->azzeraForm();
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BOR_RUOLI[DES_RUOLO]');
        Out::show($this->nameForm . '_BOR_RUOLI[KRUOLO]');
        Out::show($this->nameForm . '_BOR_RUOLI[KRUOLO]_field');
        Out::show($this->nameForm . '_divTabUtenti');
        Out::show($this->nameForm . '_Autorizzazioni');

        $this->onChangeTipoOperatore($this->CURRENT_RECORD['DIRIGENTE']);

        $arrayValue = $this->getDataRelation();
        $this->gridUtenti = $arrayValue[$this->utentiTableName];
        $this->caricaGridUtenti();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $prog = 1;

        $filtri['KRUOLO'] = trim($this->formData[$this->nameForm . '_KRUOLO']);
        $filtri['DES_RUOLO'] = trim($this->formData[$this->nameForm . '_DES_RUOLO']);
        $filtri['PROGENTE'] = $prog;
        $filtri['DIRIGENTE'] = trim($this->formData[$this->nameForm . '_DIRIGENTE']);


        foreach ($filtri as $key => $filtro) {
            if (strlen($filtro) === 0) {
                unset($filtri[$key]);
            }
        }

        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorRuoli($filtri, true, $sqlParams);

        Out::hide($this->nameForm . '_Autorizzazioni');
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBorRuoliChiave($index, $sqlParams);
    }

    protected function preParseEvent() {
        parent::preParseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initCombo();
                break;
        }
    }

    protected function customParseEvent() {
        parent::customParseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Autorizzazioni':
                        $this->onClickAutorizzazioni();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_RUOLI[DIRIGENTE]':
                        $value = trim($_POST[$this->nameForm . '_BOR_RUOLI']['DIRIGENTE']);
                        $this->onChangeTipoOperatore($value);
                        break;
                }
                break;
            case 'onSelectCheckRow':
                if (count($this->selectedValues) === 1) {
                    Out::show($this->nameForm . '_Autorizzazioni');
                } else {
                    Out::hide($this->nameForm . '_Autorizzazioni');
                }
                break;
        }
    }

    private function initCombo() {
        Out::html($this->nameForm . '_BOR_RUOLI[DIRIGENTE]', '');
        Out::select($this->nameForm . '_BOR_RUOLI[DIRIGENTE]', 1, 0, false, 'Generico');
        Out::select($this->nameForm . '_BOR_RUOLI[DIRIGENTE]', 1, 1, false, 'RUP');
        Out::select($this->nameForm . '_BOR_RUOLI[DIRIGENTE]', 1, 2, false, 'Dirigente');
        Out::select($this->nameForm . '_BOR_RUOLI[DIRIGENTE]', 1, 3, false, 'Operatore Ragioneria');

        Out::html($this->nameForm . '_DIRIGENTE', '');
        Out::select($this->nameForm . '_DIRIGENTE', 1, null, false, 'Nessuno selezionato');
        Out::select($this->nameForm . '_DIRIGENTE', 1, 0, false, 'Generico');
        Out::select($this->nameForm . '_DIRIGENTE', 1, 1, false, 'RUP');
        Out::select($this->nameForm . '_DIRIGENTE', 1, 2, false, 'Dirigente');
        Out::select($this->nameForm . '_DIRIGENTE', 1, 3, false, 'Operatore Ragioneria');
    }

    protected function elaboraRecords($Result_tab) {

        foreach ($Result_tab as $key => $Result_rec) {

            switch ($Result_tab[$key]['DIRIGENTE']) {
                case 0:
                    $Result_tab[$key]['DIRIGENTE'] = 'Generico';
                    break;
                case 1:
                    $Result_tab[$key]['DIRIGENTE'] = 'RUP';
                    break;
                case 2:
                    $Result_tab[$key]['DIRIGENTE'] = 'Dirigente';
                    break;
                case 3:
                    $Result_tab[$key]['DIRIGENTE'] = 'Operatore Ragioneria';
                    break;
            }
        }
        return $Result_tab;
    }

    protected function getDataRelation() {
        $valueArray = array();
        $valueArray[$this->utentiTableName] = $this->libDB->leggiBorUteorg(array('KRUOLO' => $this->CURRENT_RECORD['KRUOLO']));

//        $valueArray[$this->utentiTableName] = $this->libDB->leggiGeneric('BOR_UTEORG',
//                array('KRUOLO' => $this->CURRENT_RECORD['KRUOLO']));

        return $valueArray;
    }

    private function caricaGridUtenti() {
        // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $helper = new cwbBpaGenHelper();
        $helper->setGridName($this->GRID_NAME_UTENTI);
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_UTENTI);

        $records = $this->elaboraUtentiRecords($this->gridUtenti);

        $ita_grid01 = $helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
//        Out::enableGrid($this->nameForm . '_' . $this->GRID_NAME_UTENTI);
    }

    private function elaboraUtentiRecords($records) {
        $htmlRecords = array();
        foreach ($records as $key => $value) {

            $htmlRecords[$key]['CODUTE'] = $records[$key]['CODUTE'];

            $htmlRecords[$key]['RUOLOUTE'] = $records[$key]['RUOLOUTE'];

            $htmlRecords[$key]['L1ORG'] = $records[$key]['L1ORG'];
            $htmlRecords[$key]['L2ORG'] = $records[$key]['L2ORG'];
            $htmlRecords[$key]['L3ORG'] = $records[$key]['L3ORG'];
            $htmlRecords[$key]['L4ORG'] = $records[$key]['L4ORG'];
            $htmlRecords[$key]['DESPORG'] = $records[$key]['DESPORG'];
            $htmlRecords[$key]['DATAINIZ'] = $records[$key]['DATAINIZ'];

            $htmlRecords[$key]['DATAFINE'] = $records[$key]['DATAFINE'];

            $htmlRecords[$key]['ID_AUTUFF'] = $records[$key]['ID_AUTUFF'];
//            if (empty($records[$key]['ID_AUTUFF'])) {
//                $descrizione = ' ';
//            } else {
                $descrizione = $this->libDB->leggiGeneric('FTA_AUTUFF', array('ID_AUTUFF' => $records[$key]['ID_AUTUFF']), false);
//            }
            $htmlRecords[$key]['DES_AUTUFF'] = $descrizione['DES_AUTUFF'];
        }

        return $htmlRecords;
    }

    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case $this->utentiTableName:
                $results = $this->gridUtenti;
                break;
            default:
                break;
        }

        return $results;
    }

    private function azzeraForm() {
        $this->gridUtenti = array();
    }

    private function onClickAutorizzazioni() {
        if (count($this->selectedValues) === 1 || ($this->detailView && $_POST[$this->nameForm . '_BOR_RUOLI']['KRUOLO'] > 0)) {
            $id = 0;
            if ($this->detailView) {
                $id = $_POST[$this->nameForm . '_BOR_RUOLI']['KRUOLO'];
            } else {
                $id = key($this->selectedValues);
            }

            $postData = array();
            $postData['tipoRicerca'] = 2;
            $postData['ruolo'] = $id;
            $postData['disableTornaAElenco'] = true;
            $model = cwbLib::apriFinestra('cwbAutorizzazioni', $this->nameFormOrig, '', '', array(), $this->nameForm, null, $postData);
            $model->parseEvent();
        }
    }

}
