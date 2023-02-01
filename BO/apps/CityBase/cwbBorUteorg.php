<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';

function cwbBorUteorg() {
    $cwbBorUteorg = new cwbBorUteorg();
    $cwbBorUteorg->parseEvent();
    return;
}

class cwbBorUteorg extends cwbBpaGenTab {
    private $forceReadOnly;
    private $componentBorOrganModel;
    private $componentBorOrganAlias;
    private $componentBorOrganDettaglioModel;
    private $componentBorOrganDettaglioAlias;
    private $componentBorUtentiDettaglioModel;
    private $componentBorUtentiDettaglioAlias;

    protected function initVars() {
        $this->GRID_NAME = 'gridBorUteorg';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 5;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();

        $this->libDB_FTA = new cwfLibDB_FTA();

        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;

        $this->openDetailFlag = true;
        if(isSet($_POST['forceReadOnly'])){
            $this->forceReadOnly = $_POST['forceReadOnly'];
        }
        else{
            $this->forceReadOnly = cwbParGen::getFormSessionVar($this->nameForm, 'forceReadOnly');
        }
        $this->componentBorOrganAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorOrganAlias');
        if ($this->componentBorOrganAlias != '') {
            $this->componentBorOrganModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganAlias);
        }
        $this->componentBorUtentiDettaglioAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorUtentiDettaglioAlias');
        if ($this->componentBorUtentiDettaglioAlias != '') {
            $this->componentBorUtentiDettaglioModel = itaFrontController::getInstance('cwbComponentBorUtenti', $this->componentBorUtentiDettaglioAlias);
        }
        $this->componentBorOrganDettaglioAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias');
        if ($this->componentBorOrganDettaglioAlias != '') {
            $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        }

        $this->errorOnEmpty = false;
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'forceReadOnly', $this->forceReadOnly);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorOrganAlias', $this->componentBorOrganAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorUtentiDettaglioAlias', $this->componentBorUtentiDettaglioAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias', $this->componentBorOrganDettaglioAlias);
        }
    }
    
    protected function prepareAuthenticatorParams($autorLevel) {
        if($this->forceReadOnly === true){
            return array(
                'username' => cwbParGen::getSessionVar('nomeUtente'),
                'modulo' => $this->AUTOR_MODULO,
                'num' => $this->AUTOR_NUMERO,
                'level' => 'L'
            );
        }
        else{
            return parent::prepareAuthenticatorParams($autorLevel);
        }
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComponents();
                break;
        }
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_UTEORG[KRUOLO]_butt':
                        $ute = $this->formData[$this->nameForm . '_BOR_UTEORG']['CODUTE'];
                        cwbLib::apriFinestraRicerca('cwbBorRuoli', $this->nameForm, 'returnBorRuoli', '', true, null, $this->nameFormOrig);
                        break;
//                    default:
//                        if(preg_match('/'.$this->nameForm.'_SEARCH_([0-9]*)/',$_POST['id'],$matches)){
//                            $rowId = $matches[1]-1;
//                            cwbLib::apriFinestraRicerca('cwbBorLivell', $this->nameForm, 'returnLivell', $rowId, true, null);
//                        }
                    case $this->nameForm . '_BOR_UTEORG[ID_AUTUFF]_butt':
                        cwbLib::apriFinestraRicerca('cwfFtaAutuff', $this->nameForm, 'returnFtaAutuff', '', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_btnBilancioUtente':
                        $this->onClickBilancioUtente();
                        break;
                    case $this->nameForm . '_btnAutorizzazioni':
                        $this->onClickAutorizzazioni();
                        break;
                    case $this->nameForm . '_btnTipoOperativita':
                        $this->onClickTipoOperativita();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_UTEORG[KRUOLO]':
                        $this->setBorRuoliFromDB($_POST[$this->nameForm . '_BOR_UTEORG']['KRUOLO']);
                        break;
                    case $this->nameForm . '_BOR_UTEORG[ID_AUTUFF]':
                        $this->setFtaAutuffFromDB($_POST[$this->nameForm . '_BOR_UTEORG']['ID_AUTUFF']);
                        break;
                }
                break;
            case 'returnBorRuoli':
                $this->setBorRuoli($this->formData['returnData']);
                break;
            case 'returnFtaAutuff':
                $this->setFtaAutuff($this->formData['returnData']);
                break;
        }
    }

    private function initComponents() {
        $this->componentBorOrganAlias = $this->nameForm . '_BorOrganDettaglio_' . time() . rand(0, 1000);
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_divORGAN', '', '', $this->componentBorOrganAlias);
        $this->componentBorOrganModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganAlias);
        $this->componentBorOrganModel->setReturnData(array(
            'L1ORG' => $this->nameForm . '_L1ORG_SEARCH',
            'L2ORG' => $this->nameForm . '_L2ORG_SEARCH',
            'L3ORG' => $this->nameForm . '_L3ORG_SEARCH',
            'L4ORG' => $this->nameForm . '_L4ORG_SEARCH',
            'IDORGAN' => $this->nameForm . '_IDORGANSEARCH'
        ));
        $this->componentBorOrganModel->initSelector(true, $disable);


        $this->componentBorUtentiDettaglioAlias = $this->nameForm . '_BorUtentiDettaglio_' . time() . rand(0, 1000);
        itaLib::openInner('cwbComponentBorUtenti', '', true, $this->nameForm . '_divDettaglioUtente', '', '', $this->componentBorUtentiDettaglioAlias);
        $this->componentBorUtentiDettaglioModel = itaFrontController::getInstance('cwbComponentBorUtenti', $this->componentBorUtentiDettaglioAlias);
        $this->componentBorUtentiDettaglioModel->setReturnData(array(
            'UTELOG' => $this->nameForm . '_BOR_UTEORG[CODUTE]'
        ));
        $this->componentBorUtentiDettaglioModel->setCallbackFunction('nuovoCallbackCodute', $this->nameFormOrig, $this->nameForm);

        $this->componentBorOrganDettaglioAlias = $this->nameForm . '_BorOrganDettaglio_' . time() . rand(0, 1000);
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_divDettaglioORGAN', '', '', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel->setReturnData(array(
            'L1ORG' => $this->nameForm . '_BOR_UTEORG[L1ORG]',
            'L2ORG' => $this->nameForm . '_BOR_UTEORG[L2ORG]',
            'L3ORG' => $this->nameForm . '_BOR_UTEORG[L3ORG]',
            'L4ORG' => $this->nameForm . '_BOR_UTEORG[L4ORG]',
            'IDORGAN' => $this->nameForm . '_BOR_UTEORG[IDORGAN]'
        ));
        $this->componentBorOrganDettaglioModel->initSelector(true, $disable);

        Out::valore($this->nameForm . '_FLAG_ESCLUDI_CESSATI', true);

        Out::css($this->nameForm . '_btnBilancioUtente', 'height', '52px');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODUTE'] != '') {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if ($_POST['NOMEUTE'] != '') {
            $this->gridFilters['NOMEUTE'] = $this->formData['NOMEUTE'];
        }
        if ($_POST['L1ORG'] != '') {
            $this->gridFilters['L1ORG'] = $this->formData['L1ORG'];
        }
        if ($_POST['L2ORG'] != '') {
            $this->gridFilters['L2ORG'] = $this->formData['L2ORG'];
        }
        if ($_POST['L3ORG'] != '') {
            $this->gridFilters['L3ORG'] = $this->formData['L3ORG'];
        }
        if ($_POST['L4ORG'] != '') {
            $this->gridFilters['L4ORG'] = $this->formData['L4ORG'];
        }
        if ($_POST['DESPORG'] != '') {
            $this->gridFilters['DESPORG'] = $this->formData['DESPORG'];
        }
        if ($_POST['KRUOLO'] != '') {
            $this->gridFilters['KRUOLO'] = $this->formData['KRUOLO'];
        }
        if ($_POST['RUOLOUTE'] != '') {
            $this->gridFilters['RUOLOUTE'] = $this->formData['RUOLOUTE'];
        }
        if ($_POST['ID_AUTUFF'] != '') {
            $this->gridFilters['ID_AUTUFF'] = $this->formData['ID_AUTUFF'];
        }
//        if ($_POST['DES_AUTUFF'] != '') {
//            $this->gridFilters['DES_AUTUFF'] = $this->formData['DES_AUTUFF'];
//        }
        if ($_POST['FLAG_DEFAULT'] != 0) {
            $this->gridFilters['FLAG_DEFAULT'] = $this->formData['FLAG_DEFAULT'] - 1;
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODUTE'] = trim($this->formData[$this->nameForm . '_CODUTE']);
        $filtri['NOMEUTE'] = trim($this->formData[$this->nameForm . '_NOMEUTE']);
        if (empty($filtri['L1ORG'])) {
            if (!empty($this->formData[$this->nameForm . '_L1ORG_SEARCH']) && $this->formData[$this->nameForm . '_L1ORG_SEARCH'] != '00') {
                $filtri['L1ORG'] = trim($this->formData[$this->nameForm . '_L1ORG_SEARCH']);
            }
        }
        if (empty($filtri['L2ORG'])) {
            if (!empty($this->formData[$this->nameForm . '_L2ORG_SEARCH']) && $this->formData[$this->nameForm . '_L2ORG_SEARCH'] != '00') {
                $filtri['L2ORG'] = trim($this->formData[$this->nameForm . '_L2ORG_SEARCH']);
            }
        }
        if (empty($filtri['L3ORG'])) {
            if (!empty($this->formData[$this->nameForm . '_L3ORG_SEARCH']) && $this->formData[$this->nameForm . '_L3ORG_SEARCH'] != '00') {
                $filtri['L3ORG'] = trim($this->formData[$this->nameForm . '_L3ORG_SEARCH']);
            }
        }
        if (empty($filtri['L4ORG'])) {
            if (!empty($this->formData[$this->nameForm . '_L4ORG_SEARCH']) && $this->formData[$this->nameForm . '_L4ORG_SEARCH'] != '00') {
                $filtri['L4ORG'] = trim($this->formData[$this->nameForm . '_L4ORG_SEARCH']);
            }
        }
        $filtri['DESPORG'] = trim($this->formData[$this->nameForm . '_DESPORG']);
        $filtri['FLAG_DEFAULT'] = trim($this->formData[$this->nameForm . '_FLAG_DEFAULT'] == 1 ? 1 : null);
        $filtri['FLAG_ESCLUDI_CESSATI'] = trim($this->formData[$this->nameForm . '_FLAG_ESCLUDI_CESSATI'] == 1 ? 1 : null);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlleggiBorUteorg($filtri, false, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {

//        sqlDettaglio($index, &$sqlParams = array())
//        $index = explode('|', $index);
//        $filtri = array(
//            'KEY_CODUTE' => $index[0],
//            'KEY_DATAINIZ' => $index[1]
//        );
//
//        $this->SQL = $this->libDB->getSqlleggiBorUteorg($filtri, false, $sqlParams);

        $this->SQL = $this->libDB->getSqlleggiBorUteorgChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        if (is_array($Result_tab)) {
            foreach ($Result_tab as &$row) {
//                $date = strtotime($row['DATAINIZ']);
//                $row['CUSTOMKEY'] = $row['CODUTE'] . '|' . date('Y-m-d', $date);
                $row['CUSTOMKEY'] = $row['IDUTEORG'];
            }
        }
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
                $testo = "Ciclo attivo e passivo : L'utente è autorizzato ad operare su tutto l'organigramma con le stesse autorizzazioni dell'area-settore-servizio a cui è associato come default.";
                break;
        }
        Out::html($this->nameForm . '_spanDescrizioneTipoOperatore', $testo);
    }

    private function onChangeTipoOperatore2($value = null) {
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
                $testo = "Ciclo attivo e passivo : L'utente è autorizzato ad operare su tutto l'organigramma con le stesse autorizzazioni dell'area-settore-servizio a cui è associato come default.";
                break;
        }
        Out::html($this->nameForm . '_spanDescrizioneTipoOperatore2', $testo);
    }

    public function nuovoCallbackCodute($data) {
        $this->setBorUtentiFromDB($data['UTELOG']);
        $this->setFtaAututeFromDB($data['UTELOG']);
    }

    protected function postDettaglio($index, &$sqlDettaglio = null) {
        Out::valore($this->componentBorUtentiDettaglioAlias . '_CODUTE', $this->CURRENT_RECORD['CODUTE']);
        Out::valore($this->componentBorUtentiDettaglioAlias . '_NOMEUTE', $this->CURRENT_RECORD['NOMEUTE']);

        Out::disableField($this->componentBorUtentiDettaglioAlias . '_CODUTE');
        Out::disableField($this->componentBorUtentiDettaglioAlias . '_CODUTE_butt');
        Out::disableField($this->componentBorUtentiDettaglioAlias . '_NOMEUTE');
        Out::disableField($this->nameForm . '_BOR_UTEORG[DATAINIZ]');

        $this->setBorRuoliFromDB($this->CURRENT_RECORD['KRUOLO'], $this->CURRENT_RECORD['CODUTE']);
        $this->setFtaAutuffFromDB($this->CURRENT_RECORD['ID_AUTUFF']);

        $l1 = ($this->CURRENT_RECORD['L1ORG'] != '00' ? $this->CURRENT_RECORD['L1ORG'] : null);
        $l2 = ($this->CURRENT_RECORD['L2ORG'] != '00' ? $this->CURRENT_RECORD['L2ORG'] : null);
        $l3 = ($this->CURRENT_RECORD['L3ORG'] != '00' ? $this->CURRENT_RECORD['L3ORG'] : null);
        $l4 = ($this->CURRENT_RECORD['L4ORG'] != '00' ? $this->CURRENT_RECORD['L4ORG'] : null);
        $this->componentBorOrganDettaglioModel->setLxORG($l1, $l2, $l3, $l4);
        if($this->viewMode || $this->authenticator->getLevel() == 'L'){
            $this->componentBorOrganDettaglioModel->disableFields();
        }
        else{
            $this->componentBorOrganDettaglioModel->enableFields();
        }

        // campi sola lettura
        $this->setBorUtentiFromDB($this->CURRENT_RECORD['CODUTE']);
        $this->setFtaAututeFromDB($this->CURRENT_RECORD['CODUTE']);

        Out::show($this->nameForm . '_btnBilancioUtente');
        Out::show($this->nameForm . '_btnAutorizzazioni');
        Out::show($this->nameForm . '_btnTipoOperativita');
    }

    protected function postAggiorna($esito = null) {
        Out::hide($this->nameForm . '_btnBilancioUtente');
        Out::hide($this->nameForm . '_btnAutorizzazioni');
        Out::hide($this->nameForm . '_btnTipoOperativita');
    }

    protected function postTornaElenco() {
        Out::hide($this->nameForm . '_btnBilancioUtente');
        Out::hide($this->nameForm . '_btnAutorizzazioni');
        Out::hide($this->nameForm . '_btnTipoOperativita');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_btnBilancioUtente');
        Out::hide($this->nameForm . '_btnAutorizzazioni');
        Out::hide($this->nameForm . '_btnTipoOperativita');
    }

    protected function postConfermaCancella($esito = null) {
        if ($esito) {
            Out::hide($this->nameForm . '_btnBilancioUtente');
            Out::hide($this->nameForm . '_btnAutorizzazioni');
            Out::hide($this->nameForm . '_btnTipoOperativita');
        }
    }

    protected function postNuovo() {
        Out::valore($this->componentBorUtentiDettaglioAlias . '_CODUTE', '');
        Out::valore($this->componentBorUtentiDettaglioAlias . '_NOMEUTE', '');

        Out::enableField($this->componentBorUtentiDettaglioAlias . '_CODUTE');
        Out::enableField($this->componentBorUtentiDettaglioAlias . '_CODUTE_butt');
        Out::enableField($this->componentBorUtentiDettaglioAlias . '_NOMEUTE');
        Out::enableField($this->nameForm . '_BOR_UTEORG[DATAINIZ]');

        Out::valore($this->nameForm . '_BOR_UTEORG[DATAINIZ]', date('d/m/Y'));

//        Out::show($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
//        Out::show($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');
//        Out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
//        Out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');

        $this->componentBorOrganDettaglioModel->setLxORG();
        $this->componentBorOrganDettaglioModel->enableFields();

        out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
        out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');

        Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
        Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
        Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore');
        Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
        Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
        Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
    }

    public function setExternalLxORG($l1org, $l2org, $l3org, $l4org) {
        $this->componentBorOrganDettaglioModel->setLxORG($l1org, $l2org, $l3org, $l4org);
    }

    public function disableLxORG() {
        $this->componentBorOrganDettaglioModel->disableFields();
    }

    public function enableLxORG() {
        $this->componentBorOrganDettaglioModel->enableFields();
    }

    public function openDettaglioExternal($data, $viewMode = false) {
        $this->CURRENT_RECORD = $data;
        $this->viewMode = $viewMode;
        $this->dettaglio(null);
    }

    private function onClickBilancioUtente() {
        $codute = trim($this->formData[$this->nameForm . '_BOR_UTEORG']['CODUTE']);

        if (strlen($codute) > 0) {
            $model = cwbLib::apriFinestraDettaglioRecord('cwfFtaAutute', $this->nameFormOrig, null, null, $codute);
            $model->parseEvent();
        }
    }

    private function onClickAutorizzazioni() {
        $kruolo = trim($this->formData[$this->nameForm . '_BOR_UTEORG']['KRUOLO']);

        if ($kruolo > 0) {
            $postData = array();
            $postData['tipoRicerca'] = 2;
            $postData['ruolo'] = $kruolo;
            $model = cwbLib::apriFinestra('cwbAutorizzazioni', $this->nameFormOrig, null, null, null, null, null, $postData);
            $model->parseEvent();
        } else {
            Out::msgStop('ATTENZIONE', 'Ruolo non selezionato');
        }
    }

    private function onClickTipoOperativita() {
        $id = trim($this->formData[$this->nameForm . '_BOR_UTEORG']['ID_AUTUFF']);

        if ($id > 0) {
            $model = cwbLib::apriFinestraDettaglioRecord('cwfFtaAutuff', $this->nameFormOrig, null, null, $id);
            $model->parseEvent();
        } else {
            Out::msgStop('ATTENZIONE', 'Codice tipo operatività non selezionato');
        }
    }

    private function setFtaAutuffFromDB($idaut) {
        if (empty($idaut)) {
            $datafta = null;
        } else {
            $filtriautuff = array(
                'ID_AUTUFF' => $idaut
            );
            $datafta = $this->libDB_FTA->leggiGeneric('FTA_AUTUFF', $filtriautuff, false);
        }
        $this->setFtaAutuff($datafta);
    }

    private function setFtaAutuff($datafta) {
        Out::valore($this->nameForm . '_BOR_UTEORG[ID_AUTUFF]', $datafta['ID_AUTUFF']);
        Out::valore($this->nameForm . '_FTA_AUTUFF[DES_AUTUFF]', $datafta['DES_AUTUFF']);
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////// FLAG_GESAUT /////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////
    private function setBorUtentiFromDB($codute) {
        if (empty($codute)) {
            $databor = null;
            out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
            out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');
        } else {
            $filtriutenti = array(
                'CODUTE' => $codute
            );
            $databor = $this->libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
        }
        $this->setBorUtenti($databor);
    }

    private function setBorUtenti($databor) {
        if ($databor !== false) {
            out::show($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
            out::show($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');

            if ($databor['FLAG_GESAUT'] == 0) {
                Out::valore($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 'Applicativo');
            } else if ($databor['FLAG_GESAUT'] == 1) {
                Out::valore($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 'Utente');
            } else if ($databor['FLAG_GESAUT'] == 2) {
                Out::valore($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]', 'Ruolo');
            }
        } else {
            out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]');
            out::hide($this->nameForm . '_BOR_UTENTI[FLAG_GESAUT]_field');
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////// TIPO_OPER /////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////
    private function setFtaAututeFromDB($codute) {
        if (empty($codute)) {
            $datafta = null;
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
            Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
            Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
        } else {
            $filtri = array(
                'CODUTE_OP' => $codute
            );
            $datafta = $this->libDB->leggiGeneric('FTA_AUTUTE', $filtri, false);
        }
        $this->setFtaAutute($datafta, $codute);
    }

    private function setFtaAutute($datafta, $codute) {
        if ($datafta !== false) {

            $ruo = $this->formData[$this->nameForm . '_BOR_UTEORG']['KRUOLO'];
//            if ($ruo == 0) {
//                $ruo = $this->CURRENT_RECORD['KRUOLO'];
//            }

            $filtriutenti = array(
                'CODUTE' => $codute
            );
            $bor = $this->libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
            $bge = $this->libDB->leggiGeneric('BGE_APPLI', array(), false);
            if ($bor['FLAG_GESAUT'] == 1 || ($bor['FLAG_GESAUT'] == 0 && $bge['MODO_GESAUT'] == 0)) {
                if ($datafta['TIPO_OPER'] == 0) {
                    Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]', 'Generico');
                } else if ($datafta['TIPO_OPER'] == 1) {
                    Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]', 'RUP');
                } else if ($datafta['TIPO_OPER'] == 2) {
                    Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]', 'Dirigente');
                } else if ($datafta['TIPO_OPER'] == 3) {
                    Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]', 'Operatore Ragioneria');
                }
                $this->onChangeTipoOperatore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');

                Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
                Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
                Out::show($this->nameForm . '_spanDescrizioneTipoOperatore');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
                Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
            } else if ($bor['FLAG_GESAUT'] == 2 && !empty($ruo)) {
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
                Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore');
                Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
                Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
                Out::show($this->nameForm . '_spanDescrizioneTipoOperatore2');
            } else {
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
                Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
                Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
                Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
            }
        } else {
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]_field');
            Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
            Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
        }
    }

    /////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////// DIRIGENTE /////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////
    private function setBorRuoliFromDB($kruolo) {
        if (empty($kruolo)) {
            $data = null;
        } else {
            $filtri = array(
                'KRUOLO' => $kruolo
            );
            $data = $this->libDB->leggiGeneric('BOR_RUOLI', $filtri, false);
        }
        $this->setBorRuoli($data);
    }

    private function setBorRuoli($data) {
        Out::valore($this->nameForm . '_BOR_UTEORG[KRUOLO]', $data['KRUOLO']);
        Out::valore($this->nameForm . '_BOR_RUOLI[DES_RUOLO]', $data['DES_RUOLO']);

        //leggere campo dirigente
        if ($data['DIRIGENTE'] == 0) {
            Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2', 'Generico');
        } else if ($data['DIRIGENTE'] == 1) {
            Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2', 'RUP');
        } else if ($data['DIRIGENTE'] == 2) {
            Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2', 'Dirigente');
        } else if ($data['DIRIGENTE'] == 3) {
            Out::valore($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2', 'Operatore Ragioneria');
        }
        $this->onChangeTipoOperatore2($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');

        $ute = $this->formData[$this->nameForm . '_BOR_UTEORG']['CODUTE'];
        if ($ute == null) {
            $ute = $this->CURRENT_RECORD['CODUTE'];
        }

        $filtriutenti = array(
            'CODUTE' => $ute
        );
        $bor = $this->libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
        if ($bor['FLAG_GESAUT'] == 2 && $data !== null) {
            Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
            Out::show($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
            Out::show($this->nameForm . '_spanDescrizioneTipoOperatore2');
        } else {
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2');
            Out::hide($this->nameForm . '_FTA_AUTUTE[TIPO_OPER]2_field');
            Out::hide($this->nameForm . '_spanDescrizioneTipoOperatore2');
        }
    }
}
?>