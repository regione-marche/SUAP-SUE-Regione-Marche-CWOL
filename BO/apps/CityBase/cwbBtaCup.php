<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfCigCupHelper.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfBilancioHelper.php';

function cwbBtaCup() {
    $cwbBtaCup = new cwbBtaCup();
    $cwbBtaCup->parseEvent();
    return;
}

class cwbBtaCup extends cwbBpaGenTab {

    private $componentBorOrganModel;
    private $componentBorOrganAlias;
    private $componentBorOrganDettaglioModel;
    private $componentBorOrganDettaglioAlias;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaCup';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->GRID_NAME = 'gridBtaCup';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 43;
        $this->libDB = new cwbLibDB_BTA();
        $this->cigcupHelper = new cwfCigCupHelper();
        $this->bilancioHelper = new cwfBilancioHelper();


        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;

        $this->checkAnnoContabile = true;

        $this->closeOnYearChange = true;

        $this->componentBorOrganAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorOrganAlias');
        if ($this->componentBorOrganAlias != '') {
            $this->componentBorOrganModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganAlias);
        }

        $this->componentBorOrganDettaglioAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias');
        if ($this->componentBorOrganDettaglioAlias != '') {
            $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        }

        // Assegno Autorizzazioni per l'Utente sul Bilancio
        $this->UTENTE_GLOBALE = $this->bilancioHelper->utente_globale();
        $this->visibilita = $this->bilancioHelper->assAuthBilancio();

        $INTERROGAZIONE = 0;
        if (isSet($_POST['external_SOLO_FILTRO']) && $_POST['external_SOLO_FILTRO'] == 1) {
            $this->searchOpenElenco = true;
            $INTERROGAZIONE = 1;
        } else {
            $this->searchOpenElenco = false;
        }

        $this->libDB_FTA = new cwfLibDB_FTA();
        $this->libDB_BOR = new cwbLibDB_BOR();

        $this->PARAMETRI = array();
        if (null !== (cwbParGen::getFormSessionVar($this->nameForm, 'parametri'))) {
            $this->PARAMETRI = cwbParGen::getFormSessionVar($this->nameForm, 'parametri');
        }
// Leggo i Parametri gestionali solo 1^ Volta
        if (empty($this->PARAMETRI)) {
// Controllo se Abilitato al CUP
            $AUTOR_LEVEL_CIG_CUP = $this->cigcupHelper->abilitazione_cig_cup(cwfCigCupHelper::CUP);
            $RUOLO_CUP = $this->cigcupHelper->ruolo_cig_cup();
            $this->PARAMETRI = array('MODIFICABILITA' => $AUTOR_LEVEL_CIG_CUP
                , 'RUOLO_CUP' => $RUOLO_CUP
                , 'INTERROGAZIONE' => $INTERROGAZIONE
            );
        }
    }

    protected function preDestruct() {
        if (!$this->close) {
            cwbParGen::setFormSessionVar($this->nameForm, 'parametri', $this->PARAMETRI);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorOrganAlias', $this->componentBorOrganAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBorOrganDettaglioAlias', $this->componentBorOrganDettaglioAlias);
        }
    }

    protected function preApriForm() {
        Out::valore($this->nameForm . '_CESSATIPRIMADEL', date('d/m/Y'));
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComponents();
                $this->initForm();
                if ($this->PARAMETRI['INTERROGAZIONE']) {
                    // Si tratta di una Interrogazione. Vengono Nascosti i Pulsanti
                    // per Gestire le Operazioni e vengono Mantenuti quelli
                    // delle Ricerche e Consultazioni.
                    // Trattandosi di Sola Consultazione evito eventuale messaggio
                    $this->forza_initAuthenticator();
                }
                break;
            // Vari ingressi per Modifica
            case 'editRowInline':
            case 'dbClickRow':
                if ($this->PARAMETRI['INTERROGAZIONE']) {
                    // Resetto Sempre Evento per non far fare quello di Default
                    $this->setBreakEvent(true);
                }
                break;
        }
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
//                $this->initComponents();
//                Out::valore($this->nameForm . '_CESSATIPRIMADEL', date('Y-m-d'));
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_CUP[DES_BREVE]':
                        if (strlen($this->formData[$this->nameForm . '_BTA_CUP']['DES_CUP']) === 0) {
                            Out::valore($this->nameForm . '_BTA_CUP[DES_CUP]', $this->formData[$this->nameForm . '_BTA_CUP']['DES_BREVE']);
                        }
                        break;
                    case $this->nameForm . '_BTA_CUP[CODUTE_RUP]':
                        $this->setUtentiFromDB_dettaglio($_POST[$this->nameForm . '_BTA_CUP']['CODUTE_RUP'], 'DETTAGLIO');
                        break;

                    case $this->nameForm . '_CODUTE_RUP':
                        $this->setUtentiFromDB_search($_POST[$this->nameForm . '_CODUTE_RUP'], 'RICERCA');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTN_COD_CUP':
                        $this->enableCodsCup();
                        break;
                    case $this->nameForm . '_Situazione':
                        $codcupsit = $this->getSelected();
                        if (empty($codcupsit)) {
                            Out::msgInfo('', "Nessun CUP selezionato");
                        } else {
                            $this->situazioneCup($codcupsit);
                        }
                        break;

                    case $this->nameForm . '_Situazione_Edit':
                        $codcupsit = trim($_POST[$this->nameForm . '_BTA_CUP']['COD_CUP']);
                        if (empty($codcupsit)) {
                            Out::msgInfo('CUP mancante', 'Occorre selezionare un CUP');
                        } else {
                            $this->situazioneCup($codcupsit);
                        }
                        break;

                    case $this->nameForm . '_Riepilogo':
                        $codcupriep = $this->getSelected();
                        if (empty($codcupriep)) {
                            Out::msgInfo('', "Nessun CUP selezionato");
                        } else {
                            $this->riepilogoCup($codcupriep);
                        }
                        break;

                    case $this->nameForm . '_Riepilogo_Edit':
                        $codcupriep = trim($_POST[$this->nameForm . '_BTA_CUP']['COD_CUP']);
                        if (empty($codcigriep)) {
                            Out::msgInfo('CUP mancante', 'Occorre selezionare un CUP');
                        } else {
                            $this->riepilogoCup($codcupriep);
                        }
                        break;

                    case $this->nameForm . '_BTA_CUP[CODUTE_RUP]_butt':
                        $row = $_POST[$this->nameForm . '_BTA_CUP'];
                        $externalFilters = array();
                        $externalFilters['ATTIVO'] = true;
                        if (empty($externalFilters['L1ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L1ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L1ORG]'] != '00') {
                                $externalFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L1ORG]']);
                            }
                        }
                        if (empty($externalFilters['L2ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L2ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L2ORG]'] != '00') {
                                $externalFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L2ORG]']);
                            }
                        }
                        if (empty($externalFilters['L3ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L3ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L3ORG]'] != '00') {
                                $externalFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L3ORG]']);
                            }
                        }
                        if (empty($externalFilters['L4ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L4ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L4ORG]'] != '00') {
                                $externalFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L4ORG]']);
                            }
                        }
                        cwbLib::apriFinestraRicerca('cwbBorUteorg', $this->nameForm, 'returnUtenti', 'DETTAGLIO', true, $externalFilters, $this->nameFormOrig, '', $postData);
                        break;

                    case $this->nameForm . '_CODUTE_RUP_butt':
                        $row = $_POST[$this->nameForm . '_BTA_CUP'];
                        $externalFilters = array();
                        $externalFilters['ATTIVO'] = true;
                        if (empty($externalFilters['L1ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_L1ORG_SEARCH']) && $this->formData[$this->nameForm . '_L1ORG_SEARCH'] != '00') {
                                $externalFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_L1ORG_SEARCH']);
                            }
                        }
                        if (empty($externalFilters['L2ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_L2ORG_SEARCH']) && $this->formData[$this->nameForm . '_L2ORG_SEARCH'] != '00') {
                                $externalFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_L2ORG_SEARCH']);
                            }
                        }
                        if (empty($externalFilters['L3ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_L3ORG_SEARCH']) && $this->formData[$this->nameForm . '_L3ORG_SEARCH'] != '00') {
                                $externalFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_L3ORG_SEARCH']);
                            }
                        }
                        if (empty($externalFilters['L4ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_L4ORG_SEARCH']) && $this->formData[$this->nameForm . '_L4ORG_SEARCH'] != '00') {
                                $externalFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_L4ORG_SEARCH']);
                            }
                        }
                        cwbLib::apriFinestraRicerca('cwbBorUteorg', $this->nameForm, 'returnUtenti', 'RICERCA', true, $externalFilters, $this->nameFormOrig, '', $postData);
                        break;
                }
                break;
            case 'returnUtenti':
                $this->setUtenti($this->formData['returnData'], $_POST['id']);
                break;
        }

        $this->componentBorOrganModel->parseEvent();
        $this->componentBorOrganDettaglioModel->parseEvent();
    }

    protected function initAuthenticator() {
        parent::initAuthenticator();
        if ($this->authenticator->getLevel() == 'L') {
            $this->forza_initAuthenticator();
        }
    }

    private function forza_initAuthenticator($value = 'L') {
        $this->authenticator->setLevel($value); // Forzo il parametro letto in precedenza al nuovo
        $this->viewMode = true;
    }

    private function setUtentiFromDB_dettaglio($codute, $target) {
        if (!empty($codute)) {
            $dettaglioFilters['CODUTE'] = strtoupper($this->formData[$this->nameForm . '_BTA_CUP']['CODUTE_RUP']);
            if (empty($dettaglioFilters['L1ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L1ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L1ORG]'] != '00') {
                    $dettaglioFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L1ORG]']);
                }
            }
            if (empty($dettaglioFilters['L2ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L2ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L2ORG]'] != '00') {
                    $dettaglioFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L2ORG]']);
                }
            }
            if (empty($dettaglioFilters['L3ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L3ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L3ORG]'] != '00') {
                    $dettaglioFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L3ORG]']);
                }
            }
            if (empty($dettaglioFilters['L4ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CUP[L4ORG]']) && $this->formData[$this->nameForm . '_BTA_CUP[L4ORG]'] != '00') {
                    $dettaglioFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_BTA_CUP[L4ORG]']);
                }
            }
        }

        $uteorg = $this->libDB_BOR->leggiBorUteorg($filtri, false);

        if (isSet($uteorg) && $uteorg != null) {
            $filtri = array('CODUTE' => strtoupper($codute));
            $data = $this->libDB->leggiGeneric('BOR_UTENTI', $filtri, false);
        } else {
            Out::msgInfo('RUP errato', "L'utente rup non fa parte del servizio richiedente");
            $data = null;
        }
        $this->setUtenti($data, $target);
    }

    private function setUtentiFromDB_search($codute, $target) {
        if (!empty($codute)) {
            $searchFilters['CODUTE'] = strtoupper($this->formData[$this->nameForm . '_CODUTE_RUP']);
            if (empty($searchFilters['L1ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_L1ORG_SEARCH']) && $this->formData[$this->nameForm . '_L1ORG_SEARCH'] != '00') {
                    $searchFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_L1ORG_SEARCH']);
                }
            }
            if (empty($searchFilters['L2ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_L2ORG_SEARCH']) && $this->formData[$this->nameForm . '_L2ORG_SEARCH'] != '00') {
                    $searchFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_L2ORG_SEARCH']);
                }
            }
            if (empty($searchFilters['L3ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_L3ORG_SEARCH']) && $this->formData[$this->nameForm . '_L3ORG_SEARCH'] != '00') {
                    $searchFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_L3ORG_SEARCH']);
                }
            }
            if (empty($searchFilters['L4ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_L4ORG_SEARCH']) && $this->formData[$this->nameForm . '_L4ORG_SEARCH'] != '00') {
                    $searchFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_L4ORG_SEARCH']);
                }
            }
        }
        $uteorg = $this->libDB_BOR->leggiBorUteorg($searchFilters, false);

        if (isSet($uteorg) && $uteorg != null) {
            $sfiltri = array('CODUTE' => strtoupper($codute));
            $data = $this->libDB->leggiGeneric('BOR_UTENTI', $sfiltri, false);
        } else {
            Out::msgInfo('RUP errato', "L'utente rup non fa parte del servizio richiedente");
            $data = null;
        }
        $this->setUtenti($data, $target);
    }

    private function setUtenti($data, $target) {
        switch ($target) {
            case 'DETTAGLIO':
                Out::valore($this->nameForm . '_BTA_CUP[CODUTE_RUP]', $data['CODUTE']);
                Out::valore($this->nameForm . '_BOR_UTENTI[NOMEUTE]', $data['NOMEUTE']);
                break;
            case 'RICERCA':
                Out::valore($this->nameForm . '_CODUTE_RUP', $data['CODUTE']);
                Out::valore($this->nameForm . '_NOMEUTE', $data['NOMEUTE']);
                break;
        }
    }

    protected function postNuovo() {
        $this->enableCodsCup();

        Out::valore($this->nameForm . '_COD_CUP_1', '');
        Out::valore($this->nameForm . '_COD_CUP_2', '');
        Out::valore($this->nameForm . '_COD_CUP_3', '');
        Out::valore($this->nameForm . '_COD_CUP_4', '');
        Out::valore($this->nameForm . '_COD_CUP_5', '');
        Out::valore($this->nameForm . '_COD_CUP_6', '');
        Out::valore($this->nameForm . '_COD_CUP_7', '');
        Out::valore($this->nameForm . '_COD_CUP_8', '');

        $this->componentBorOrganDettaglioModel->setLxORG();

        Out::setFocus("", $this->nameForm . '_BTA_CUP[COD_CUP]');

        Out::valore($this->nameForm . '_BTA_CUP[DATAINIZ]', date('Y-m-d'));

        $idorgan = $this->getIdOrganForNew();
        $this->componentBorOrganDettaglioModel->setIdorgan($idorgan);
        Out::valore($this->nameForm . '_BTA_CUP[IDORGAN]', $idorgan);

        Out::hide($this->nameForm . '_BTN_COD_CUP');

        $user = cwbParGen::getUtente();
        $this->setUtentiFromDB_dettaglio($user, 'DETTAGLIO');

        // Controllo se Abilitato solo ai propri CUP (RUP)
        if ($this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_RUP) {
            Out::disableField($this->nameForm . '_BTA_CUP[CODUTE_RUP]');
            Out::disableButton($this->nameForm . '_BTA_CUP[CODUTE_RUP]_butt');
        } else {
            Out::enableField($this->nameForm . '_BTA_CUP[CODUTE_RUP]');
            Out::enableButton($this->nameForm . '_BTA_CUP[CODUTE_RUP]_butt');
        }

        if ($this->apriDettaglioIndex == 'new' && isSet($_POST['COD_CUP'])) {
            Out::valore($this->nameForm . '_BTA_CUP[COD_CUP]', $_POST['COD_CUP']);
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_COD_CUP');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_COD_CUP');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_CUP[COD_CUP]');
    }

    protected function postDettaglio() {
        $this->disableCodsCup();

        $this->valorizzaCodCupText();

        $idorgan = ($this->CURRENT_RECORD['IDORGAN'] != '00' ? $this->CURRENT_RECORD['IDORGAN'] : null);

        $this->componentBorOrganDettaglioModel->setIdorgan($idorgan);

        Out::setFocus("", $this->nameForm . '_BTA_CUP[DES_CUP]');

        if ($this->viewMode) {
            Out::hide($this->nameForm . '_BTN_COD_CUP');
        } else {
            Out::show($this->nameForm . '_BTN_COD_CUP');
        }

        // Controllo se Abilitato solo ai propri CUP (RUP)
        if ($this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_RUP) {
            Out::disableField($this->nameForm . '_BTA_CUP[CODUTE_RUP]');
            Out::disableButton($this->nameForm . '_BTA_CUP[CODUTE_RUP]_butt');
        } else {
            Out::enableField($this->nameForm . '_BTA_CUP[CODUTE_RUP]');
            Out::enableButton($this->nameForm . '_BTA_CUP[CODUTE_RUP]_butt');
        }
    }

    protected function preAggiorna() {
        $this->valorizzaCodCup();
    }

    protected function preAggiungi() {
        $this->valorizzaCodCup();
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        // Controllo se Abilitato solo ai propri CUP (RUP, DIRIGENTE ecc.)
        switch ($this->PARAMETRI['RUOLO_CUP']) {
            case cwfCigCupHelper::RUOLO_GENERICO:
                $limita_cup = $this->cigcupHelper->cig_cup_se_generico(cwfCigCupHelper::CUP);
                if (!empty($limita_cup) && $limita_cup[0] == 'GLOBALE') {
//utente globale puo vedere tutto
                } else {
                    if (empty($limita_cup)) {
                        $limita_cup = array(-9999999999); // Filtro va applicato senza far trovare nulla
                    }
                    $filtri['PROG_CUP_in'] = $limita_cup;
                }
                break;
            case cwfCigCupHelper::RUOLO_RUP:
                $filtri['CODUTE_RUP'] = cwbParGen::getSessionVar('nomeUtente');
                break;
            case cwfCigCupHelper::RUOLO_DIRIGENTE:
                $limita_cup = $this->cigcupHelper->cig_cup_se_dirigente(cwfCigCupHelper::CUP);
                if (empty($limita_cup)) {
                    $limita_cup = array(-9999999999); // Filtro va applicato senza far trovare nulla
                }
                $filtri['PROG_CUP_in'] = $limita_cup;
                break;
            case cwfCigCupHelper::RUOLO_OPER_RAG:
                break;
            default:
                break;
        }

        // Puo' Visualizzare Cig/Cup delle Strutture Organizzative a 
        //  cui e' Abilitato (se Utente non Globale)
        // Su Ruolo=Operatore di Ragioneria ha visualizzazione su tutto il Bilancio
        if ($this->PARAMETRI['RUOLO_CUP'] != cwfCigCupHelper::RUOLO_OPER_RAG) {
            $filtro_servizi_per_visibilita = $this->bilancioHelper->filtro_servizi_per_visibilita($this->UTENTE_GLOBALE, $this->visibilita['organ']);
            if (!empty($filtro_servizi_per_visibilita)) {
                $filtri['LISTA_LXORG'] = $filtro_servizi_per_visibilita;
            }
        }

        if (!isSet($filtri['COD_CUP'])) {
            $filtri['COD_CUP'] = trim($this->formData[$this->nameForm . '_COD_CUP']);
        }
        if (!isSet($filtri['DES_BREVE'])) {
            $filtri['DES_BREVE'] = trim($this->formData[$this->nameForm . '_DES_BREVE']);
        }
        if (!isSet($filtri['DATAINIZIODAL'])) {
            $filtri['DATAINIZIODAL'] = trim($this->formData[$this->nameForm . '_DATAINIZIODAL']);
        }
        if (!isSet($filtri['CESSATIPRIMADEL'])) {
            $filtri['CESSATIPRIMADEL'] = trim($this->formData[$this->nameForm . '_CESSATIPRIMADEL']);
        }

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
        if (!isSet($filtri['CODUTE_RUP'])) {
            $filtri['CODUTE_RUP'] = trim($this->formData[$this->nameForm . '_CODUTE_RUP']);
        }

        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaCup($filtri, false, $sqlParams);
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if (!empty($_POST['COD_CUP'])) {
            $this->gridFilters['COD_CUP'] = $this->formData['COD_CUP'];
        }
        if (!empty($_POST['DES_BREVE'])) {
            $this->gridFilters['DES_BREVE'] = $this->formData['DES_BREVE'];
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
        if (!empty($_POST['DESPORG'])) {
            $this->gridFilters['DESPORG'] = $this->formData['DESPORG'];
        }
        if (!empty($_POST['CODUTE_RUP'])) {
            $this->gridFilters['CODUTE_RUP'] = $this->formData['CODUTE_RUP'];
        }
        if (!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS'] - 1;
        }
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaCup(array('COD_CUP' => $index), false, $sqlParams);
    }

    private function initComponents() {
// RICERCA
        $this->componentBorOrganAlias = $this->nameForm . '_componentBorOrganAlias_' . time() . rand(0, 1000);
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
        $this->componentBorOrganModel->setDescriptionWidth(300);
// EDIT
        $this->componentBorOrganDettaglioAlias = $this->nameForm . '_componentBorOrganDettaglioAlias_' . time() . rand(0, 1000);
        itaLib::openInner('cwbComponentBorOrgan', '', true, $this->nameForm . '_divDettaglioORGAN', '', '', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel = itaFrontController::getInstance('cwbComponentBorOrgan', $this->componentBorOrganDettaglioAlias);
        $this->componentBorOrganDettaglioModel->setReturnData(array(
            'L1ORG' => $this->nameForm . '_BTA_CUP[L1ORG]',
            'L2ORG' => $this->nameForm . '_BTA_CUP[L2ORG]',
            'L3ORG' => $this->nameForm . '_BTA_CUP[L3ORG]',
            'L4ORG' => $this->nameForm . '_BTA_CUP[L4ORG]',
            'IDORGAN' => $this->nameForm . '_BTA_CUP[IDORGAN]'
        ));
        $this->componentBorOrganDettaglioModel->initSelector(true, $disable);
        $this->componentBorOrganDettaglioModel->setDescriptionWidth(300);
    }

    private function valorizzaCodCupText() {
        $cod_cup = $this->CURRENT_RECORD['COD_CUP'];

        Out::valore($this->nameForm . '_COD_CUP_1', $cod_cup{0});
        Out::valore($this->nameForm . '_COD_CUP_2', $cod_cup{1});
        Out::valore($this->nameForm . '_COD_CUP_3', $cod_cup{2});
        Out::valore($this->nameForm . '_COD_CUP_4', $cod_cup{3});
        Out::valore($this->nameForm . '_COD_CUP_5', substr($cod_cup, 4, 2));
        Out::valore($this->nameForm . '_COD_CUP_6', substr($cod_cup, 6, 5));
        Out::valore($this->nameForm . '_COD_CUP_7', substr($cod_cup, 11, 3));
        Out::valore($this->nameForm . '_COD_CUP_8', $cod_cup{14});
    }

    private function valorizzaCodCup() {

        $cod_cup1 = $_POST[$this->nameForm . '_COD_CUP_1'];
        $cod_cup2 = $_POST[$this->nameForm . '_COD_CUP_2'];
        $cod_cup3 = $_POST[$this->nameForm . '_COD_CUP_3'];
        $cod_cup4 = $_POST[$this->nameForm . '_COD_CUP_4'];
        $cod_cup5 = $_POST[$this->nameForm . '_COD_CUP_5'];
        $cod_cup6 = $_POST[$this->nameForm . '_COD_CUP_6'];
        $cod_cup7 = $_POST[$this->nameForm . '_COD_CUP_7'];
        $cod_cup8 = $_POST[$this->nameForm . '_COD_CUP_8'];

        $cod_cup = $cod_cup1 . $cod_cup2 . $cod_cup3 . $cod_cup4 . $cod_cup5 . $cod_cup6 . $cod_cup7 . $cod_cup8;

        Out::valore($this->nameForm . '_BTA_CUP[COD_CUP]', $cod_cup);

        $_POST[$this->nameForm . '_BTA_CUP']['COD_CUP'] = $cod_cup;
        $this->formData[$this->nameForm . '_BTA_CUP']['COD_CUP'] = $cod_cup;
    }

    private function getIdOrganForNew() {
        $idorgan = 0;
        // Puo' Visualizzare Cig/Cup delle Strutture Organizzative a 
        //  cui e' Abilitato (se Utente non Globale)
        // Su Ruolo=Operatore di Ragioneria ha visualizzazione su tutto il Bilancio
        $utente_globale = false;
        if ($this->UTENTE_GLOBALE || $this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_OPER_RAG) {
            $utente_globale = true;
        }
        $organ_default = $this->bilancioHelper->struttura_organizzativa_da_proporre($utente_globale, $this->visibilita['organ']);
        if (!empty($organ_default)) {
            $idorgan = $organ_default['IDORGAN'];
        }
// Vecchia Modalita' senza Gestione dei Ruoli
//        $user = cwbParGen::getUtente();
//        $this->libDBBor = new cwbLibDB_BOR();
//
//        $results = $this->libDBBor->getBorOrganFromUser($user, true);
//
//        if (count($results) > 0) {
//            $idorgan = $results[0]['IDORGAN'];
//        }

        return $idorgan;
    }

    private function enableCodsCup() {
        Out::enableField($this->nameForm . '_COD_CUP_1');
        Out::enableField($this->nameForm . '_COD_CUP_2');
        Out::enableField($this->nameForm . '_COD_CUP_3');
        Out::enableField($this->nameForm . '_COD_CUP_4');
        Out::enableField($this->nameForm . '_COD_CUP_5');
        Out::enableField($this->nameForm . '_COD_CUP_6');
        Out::enableField($this->nameForm . '_COD_CUP_7');
        Out::enableField($this->nameForm . '_COD_CUP_8');
    }

    private function disableCodsCup() {
        Out::disableField($this->nameForm . '_COD_CUP_1');
        Out::disableField($this->nameForm . '_COD_CUP_2');
        Out::disableField($this->nameForm . '_COD_CUP_3');
        Out::disableField($this->nameForm . '_COD_CUP_4');
        Out::disableField($this->nameForm . '_COD_CUP_5');
        Out::disableField($this->nameForm . '_COD_CUP_6');
        Out::disableField($this->nameForm . '_COD_CUP_7');
        Out::disableField($this->nameForm . '_COD_CUP_8');
    }

    protected function situazioneCup($codcupris) {
        $model = cwbLib::apriFinestra('cwfSituazioneCig', $this->nameFormOrig, ",", array(), $this->nameForm);
        $model->FILTRI['COD_CUP'] = $codcupris;
        $model->FILTRI['TIPO_CIG_CUP'] = cwfCigCupHelper::CUP;
        $model->parseEvent();
    }

    protected function riepilogoCup($codcupriep) {
        $model = cwbLib::apriFinestra('cwfRiepilogoCig', $this->nameFormOrig, ",", array(), $this->nameForm);
        $model->FILTRI['COD_CUP'] = $codcupriep;
        $model->FILTRI['TIPO_CIG_CUP'] = cwfCigCupHelper::CUP;
        $model->parseEvent();
    }

    private function initForm() {
        $titolo = '';
        if ($this->PARAMETRI['INTERROGAZIONE']) {
            $titolo .= 'Consultazione CUP ';
        } else {
            $titolo .= 'Codice Unico Progetto ';
        }
        $titolo .= '- Utente: ' . cwbParGen::getNomeUte();
        $titolo .= ' Tip. Operatore: ' . $this->cigcupHelper->descrizione_ruolo_cig_cup($this->PARAMETRI['RUOLO_CUP']);
        if (!$this->PARAMETRI['INTERROGAZIONE']) {
            $titolo .= ' (' . $this->cigcupHelper->descrizione_abilitazione_cig_cup($this->PARAMETRI['MODIFICABILITA']) . ')';
        }
        Out::setGridCaption($this->nameForm . "_" . $this->GRID_NAME, $titolo);

        // Controllo se Abilitato solo ai propri CUP (RUP)
        if ($this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_RUP) {
            // Blocco RUP gia' nella Ricerca (Fisso Utente corrente)
            Out::valore($this->nameForm . '_CODUTE_RUP', cwbParGen::getSessionVar('nomeUtente'));
            Out::disableField($this->nameForm . '_CODUTE_RUP');
            Out::disableButton($this->nameForm . '_CODUTE_RUP_butt');
            Out::valore($this->nameForm . '_NOMEUTE', cwbParGen::getNomeUte());
        }
    }

    /*
     * Sovrascrivo gestione Button per quelli Personalizzati
     */

    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna, $Situazione = false, $Riepilogo = false, $Situazione_Edit = false, $Riepilogo_Edit = false) {

        if ($this->PARAMETRI['INTERROGAZIONE'] || $this->viewMode) {
// Si tratta di una Interrogazione. Vengono Nascosti i Pulsanti
// per Gestire le Operazioni e vengono Mantenuti quelli
// delle Ricerche e Consultazioni.
            $nuovo = false;
            $aggiungi = false;
            $aggiorna = false;
            $cancella = false;
        }
        $Situazione ? Out::show($this->nameForm . '_Situazione') : Out::hide($this->nameForm . '_Situazione');
        $Riepilogo ? Out::show($this->nameForm . '_Riepilogo') : Out::hide($this->nameForm . '_Riepilogo');
        $Situazione_Edit ? Out::show($this->nameForm . '_Situazione_Edit') : Out::hide($this->nameForm . '_Situazione_Edit');
        $Riepilogo_Edit ? Out::show($this->nameForm . '_Riepilogo_Edit') : Out::hide($this->nameForm . '_Riepilogo_Edit');

        parent::setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna);
    }

    protected function setVisRisultato() {
        $this->setVisControlli(false, true, false, true, false, false, false, false, true, false, true, true, false, false);
    }

    protected function setVisDettaglio() {
// In pratica serve solo per Visualizzare "Situazione", "Riepilogo" ecc.
// Sovrascrivo metodo standard ed aggiungo sola visualizzazione pulsanti consultazione.
        if ($this->viewMode) {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, false, false, false, false, false, true, false, false, false, false);
            } else {
                $this->setVisControlli(true, false, false, false, false, false, false, false, true, true, false, false, true, true);
            }
        } else {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true, false, false, false, false);
            } else {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, true, true, false, false, true, true);
            }
        }
    }

    /*
     * Ridefinizione della Modifica per far si che
     *  si possa controllare l'effettiva modificabilita' del Record.
     * Magari puo' essere visualizzato ma non modificato.
     */

    protected function dettaglio($index) {
        $puo_modificare = true;
        $deser = '';
        $codute = strtoupper(cwbParGen::getSessionVar('nomeUtente'));
        // Leggo l'Operazione Selezionata
        $filtri = array(
            'COD_CUP' => $index
        );
        $row_mod = $this->libDB->leggiBtaCup($filtri, false);
        if (!empty($row_mod)) {
            // Controllo se Abilitato solo ai propri CUP (RUP)
            if ($this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_RUP) {
                if (strtoupper($row_mod['CODUTE_RUP']) != $codute) {
                    $puo_modificare = false;
                    $deser .= "<br />Utente Abilitato a gestire solo i propri CIG"; // Segnalazione Errore
                }
            }

            // Controlli sulla Struttura Organizzativa (se c'e')
            if ($puo_modificare && !empty($row_mod['IDORGAN'])) {
                // Leggo Autorizzazioni BTA-x dell'Utente per l'Assegnatario del
                // CUP/Cup (magari sta su piu' servizi con differenti Ruoli)
                $auth_BTA_serv = $this->bilancioHelper->autorizzazioni_sul_servizio($row_mod['IDORGAN'], null, 'BTA', null);
                if (empty($auth_BTA_serv[$this->AUTOR_NUMERO])) {
                    $puo_modificare = false;
                    $deser .= "<br />Non si e' abilitati a questa gestione,<br />per la Struttura Organizzativa dell'Assegnatario";
                } else {
                    if ($auth_BTA_serv[$this->AUTOR_NUMERO] == "L") {
                        $this->viewMode = true; // Ma in Sola Lettura
                    }
                }
                if ($puo_modificare) {
                    // Controllo se Struttura Organizzativa a cui si e' Abilitato
                    $filtro_servizi_per_visibilita = $this->bilancioHelper->filtro_servizi_per_visibilita($this->UTENTE_GLOBALE, $this->visibilita['organ']);
                    // Se Visibilita' a tutti i Servizi non controllo nulla (Es. GLOBALE)
                    if (!empty($filtro_servizi_per_visibilita)) {
                        // Controllo se e' uno dei servizi a cui e' Abilitato
                        $serv_gestibile = false;
                        foreach ($filtro_servizi_per_visibilita as $keyass => $assegnatario) {
                            // Controllo se e' una Struttura Organizzativa Gestibile
                            $gestib = true;
                            for ($nass = 1; $nass <= 4; $nass++) {
                                if (isSet($assegnatario['L' . $nass . 'ORG']) && !empty($assegnatario['L' . $nass . 'ORG']) && $assegnatario['L' . $nass . 'ORG'] != '00') {
                                    if ($row_mod['L' . $nass . 'ORG'] != $assegnatario['L' . $nass . 'ORG']) {
                                        $gestib = false;
                                        break;
                                    }
                                }
                            }
                            if ($gestib) {
                                $serv_gestibile = true;
                                break;
                            }
                        }
                        if (!$serv_gestibile) {
                            // Non Gestibile; vedo se puo' visualizzarlo (Es. Operatore Ragioneria)
                            if ($this->UTENTE_GLOBALE || $this->PARAMETRI['RUOLO_CUP'] == cwfCigCupHelper::RUOLO_OPER_RAG) {
                                $this->viewMode = true; // Ma in Sola Lettura
                            } else {
                                $puo_modificare = false;
                                $deser .= "<br />Non si e' abilitati a questa gestione,<br />per la Struttura Organizzativa dell'Assegnatario";
                            }
                        }
                    }
                }
            }
        }
        if ($puo_modificare) {
            parent::dettaglio($index);
        } else {
            Out::msgInfo('Modifica Impossibile', $deser);
        }
    }

}

?>