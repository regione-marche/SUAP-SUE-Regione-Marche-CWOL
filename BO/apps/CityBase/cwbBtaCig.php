<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfCigCupHelper.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfBilancioHelper.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfImpAccHelper.php';

function cwbBtaCig() {
    $cwbBtaCig = new cwbBtaCig();
    $cwbBtaCig->parseEvent();
    return;
}

class cwbBtaCig extends cwbBpaGenTab {

    private $componentBorOrganModel;
    private $componentBorOrganAlias;
    private $componentBorOrganDettaglioModel;
    private $componentBorOrganDettaglioAlias;

    function initVars() {
        $this->GRID_NAME = 'gridBtaCig';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 42;
        $this->libDB = new cwbLibDB_BTA();
        $this->cigcupHelper = new cwfCigCupHelper();
        $this->bilancioHelper = new cwfBilancioHelper();
        $this->impaccHelper = new cwfImpAccHelper();

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
            // Se Visibilita' su Tutto il Bilancio da Operativita' Ciclo Attivo/Passivo
        $this->visib_ciclo_attivo = $this->impaccHelper->visibilita_ciclo_attivo_passivo('E');
        $this->visib_ciclo_passivo = $this->impaccHelper->visibilita_ciclo_attivo_passivo('S');
		
        $INTERROGAZIONE = 0;
        if (isSet($_POST['external_SOLO_FILTRO']) && $_POST['external_SOLO_FILTRO'] == 1) {
            $this->searchOpenElenco = true;
            $INTERROGAZIONE = 1;
        } else {
            $this->searchOpenElenco = false;
        }

        $this->libDB_FTA = new cwfLibDB_FTA();
        $this->prefisso_edit = "edit_";
        $this->libDB_BOR = new cwbLibDB_BOR();

        $this->PARAMETRI = array();
        if (null !== (cwbParGen::getFormSessionVar($this->nameForm, 'parametri'))) {
            $this->PARAMETRI = cwbParGen::getFormSessionVar($this->nameForm, 'parametri');
        }
// Leggo i Parametri gestionali solo 1^ Volta
        if (empty($this->PARAMETRI)) {
// Controllo se Abilitato al CIG
            $AUTOR_LEVEL_CIG_CUP = $this->cigcupHelper->abilitazione_cig_cup(cwfCigCupHelper::CIG);
            $RUOLO_CIG = $this->cigcupHelper->ruolo_cig_cup();
            $this->PARAMETRI = array('MODIFICABILITA' => $AUTOR_LEVEL_CIG_CUP
                , 'RUOLO_CIG' => $RUOLO_CIG
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
                $this->initTable();
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
//                Out::valore($this->nameForm . '_CESSATIPRIMADEL', date('d/m/Y'));
//                if ($this->PARAMETRI['INTERROGAZIONE']) {
//                    // Si tratta di una Interrogazione. Vengono Nascosti i Pulsanti
//                    // per Gestire le Operazioni e vengono Mantenuti quelli
//                    // delle Ricerche e Consultazioni.
//                    // Trattandosi di Sola Consultazione evito eventuale messaggio
//                    $this->forza_initAuthenticator();
//                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_CIG[DES_BREVE]':
                        if (strlen($this->formData[$this->nameForm . '_BTA_CIG']['DES_CIG']) === 0) {
                            Out::valore($this->nameForm . '_BTA_CIG[DES_CIG]', $this->formData[$this->nameForm . '_BTA_CIG']['DES_BREVE']);
                        }
                        break;

                    case $this->nameForm . '_BTA_CIG[CODUTE_RUP]':
                        $this->setUtentiFromDB_dettaglio($_POST[$this->nameForm . '_BTA_CIG']['CODUTE_RUP'], 'DETTAGLIO');
                        break;

                    case $this->nameForm . '_CODUTE_RUP':
                        $this->setUtentiFromDB_search($_POST[$this->nameForm . '_CODUTE_RUP'], 'RICERCA');
                        break;

                    case $this->nameForm . '_BTA_CIG[PROGSOGG]':
                        $this->setProgsoggFromDB('BTA_CIG[PROGSOGG]', trim($_POST[$this->nameForm . '_BTA_CIG']['PROGSOGG']), $this->prefisso_edit);
                        break;

                    case $this->nameForm . '_PROGSOGG':
                        $this->setProgsoggFromDB('PROGSOGG', trim($_POST[$this->nameForm . '_PROGSOGG']), $this->prefisso_edit);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTN_COD_CIG':
                        Out::enableField($this->nameForm . '_BTA_CIG[COD_CIG]');
                        break;

                    case $this->nameForm . '_DatiAnac':
                        $cigda = $this->getSelected();
                        if (empty($cigda)) {
                            Out::msgInfo('', "Nessun CIG selezionato");
                        } elseif (count($cigda) > 1) {
                            Out::msgInfo('', "Selezionare solo un CIG per Dati ANAC");
                        } else {
                            $this->bilancioHelper->apriFinestraDatiAnac($cigda[0], 'return_callbackDatiAnac', $this->nameFormOrig, $this->nameForm);
                        }

                        break;
                    case $this->nameForm . '_DatiAnac_Edit':
                        $cigda = trim($_POST[$this->nameForm . '_BTA_CIG']['COD_CIG']);
                        if (empty($cigda)) {
                            Out::msgInfo('CIG mancante', 'Occorre selezionare un CIG');
                        } else {
                            $this->bilancioHelper->apriFinestraDatiAnac($cigda, 'return_callbackDatiAnac_Edit', $this->nameFormOrig, $this->nameForm, true);
                        }
                        break;

                    case $this->nameForm . '_Situazione':
                        $codcigsit = $this->getSelected();
                        if (empty($codcigsit)) {
                            Out::msgInfo('', "Nessun CIG selezionato");
                        } else {
                            $this->situazioneCig($codcigsit);
                        }
                        break;

                    case $this->nameForm . '_Situazione_Edit':
                        $codcigsit = trim($_POST[$this->nameForm . '_BTA_CIG']['COD_CIG']);
                        if (empty($codcigsit)) {
                            Out::msgInfo('CIG mancante', 'Occorre selezionare un CIG');
                        } else {
                            $this->situazioneCig($codcigsit);
                        }
                        break;

                    case $this->nameForm . '_Riepilogo':
                        $codcigriep = $this->getSelected();
                        if (empty($codcigriep)) {
                            Out::msgInfo('', "Nessun CIG selezionato");
                        } else {
                            $this->riepilogoCig($codcigriep);
                        }
                        break;

                    case $this->nameForm . '_Riepilogo_Edit':
                        $codcigriep = trim($_POST[$this->nameForm . '_BTA_CIG']['COD_CIG']);
                        if (empty($codcigriep)) {
                            Out::msgInfo('CIG mancante', 'Occorre selezionare un CIG');
                        } else {
                            $this->riepilogoCig($codcigriep);
                        }

                        break;

                    case $this->nameForm . '_SmartCIG':
                        $this->bilancioHelper->prenotaSmartCIG('return_callbackSmartCig', $this->nameForm); //Da definire ancora
                        break;

                    case $this->nameForm . '_BTA_CIG[CODUTE_RUP]_butt':
                        $row = $_POST[$this->nameForm . '_BTA_CIG'];

                        $externalFilters = array();
                        $externalFilters['ATTIVO'] = true;
                        if (empty($externalFilters['L1ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L1ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L1ORG]'] != '00') {
                                $externalFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L1ORG]']);
                            }
                        }
                        if (empty($externalFilters['L2ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L2ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L2ORG]'] != '00') {
                                $externalFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L2ORG]']);
                            }
                        }
                        if (empty($externalFilters['L3ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L3ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L3ORG]'] != '00') {
                                $externalFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L3ORG]']);
                            }
                        }
                        if (empty($externalFilters['L4ORG'])) {
                            if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L4ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L4ORG]'] != '00') {
                                $externalFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L4ORG]']);
                            }
                        }

                        cwbLib::apriFinestraRicerca('cwbBorUteorg', $this->nameForm, 'returnUtenti', 'DETTAGLIO', true, $externalFilters, $this->nameFormOrig, '', $postData);
                        break;

                    case $this->nameForm . '_CODUTE_RUP_butt':
                        $row = $_POST[$this->nameForm . '_BTA_CIG'];
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

                    case $this->nameForm . '_BTA_CIG[PROGSOGG]_butt':
                        $progsogg = trim($_POST[$this->nameForm . '_BTA_CIG']['PROGSOGG']);
                        $this->apriFinestraBtaSogg($progsogg, 'PROGSOGG', 'return_editProgsogg');
                        break;

                    case $this->nameForm . '_PROGSOGG_butt':
                        $progsogg = trim($_POST[$this->nameForm . '_BTA_CIG']['PROGSOGG']);
                        $this->apriFinestraBtaSogg($progsogg, 'PROGSOGG', 'return_editProgsogg_2');
                        break;

                    default:
                        $nome_pulito = strtoupper(str_replace($this->nameForm . '_', '', $_POST['id']));
                        $len_pref_clicc = strlen($this->prefisso_cliccabili);
                        if (strlen($nome_pulito) > $len_pref_clicc) {
                            $key = str_replace(substr($nome_pulito, 0, $len_pref_clicc), "", $nome_pulito);
                            switch ($key) {
                                case "PROGSOGG": // Fornitore
                                    $this->info_fornitore();
                                    break;
                                default:
                                    break;
                            }
                        }
                        break;
                }
                break;
            case 'returnUtenti':
                $this->setUtenti($this->formData['returnData'], $_POST['id']);
                break;
            case 'return_editProgsogg':
                $this->setProgsogg('BTA_CIG[PROGSOGG]', $this->formData['returnData'], $this->prefisso_edit);
                break;
            case 'return_editProgsogg_2':
                $this->setProgsogg('PROGSOGG', $this->formData['returnData'], $this->prefisso_edit);
                break;
            case 'return_callbackDatiAnac':
            case 'return_callbackSmartCig':
                // Siccome da questa gestione mio puo' aver INSERITO un nuovo Cig,
                // controllo se gia' esiste nella nostra tabella (BTA_CIG) e se
                // e' variato da prima della chiamata ai dati ANAC.
                // Se non e' inserito lo Inserisco direttamente

                $post_da_anac = $_POST; // Salvo dati reinviatimi da Anac-SmartCig
                $codute_rup = strtoupper(cwbParGen::getSessionVar('nomeUtente'));
                $idorgan = 0;
                $progsogg = 0;
                $prec_cod_cig = '';

                $NEW_CIG_IN_BTA_CIG = $this->cigcupHelper->nuovo_cig_bta_da_anac($post_da_anac, $codute_rup, $idorgan, $progsogg, $prec_cod_cig);
                if ($NEW_CIG_IN_BTA_CIG) {
                    // Nuovo CIG inserito in BTA_CIG; visualizzo, aggiorno ecc.
                    $this->elenca();
                }
                break;
            case 'return_callbackDatiAnac_Edit':

                break;
        }

        $this->componentBorOrganModel->parseEvent();
        $this->componentBorOrganDettaglioModel->parseEvent();
    }

    /*
     * Sovrascrivo il controllo dell'Autenticazione in modo da potre accodare 
     * anche i miei controlli al fine di renderli in sola lettura egestire
     * i pulsanti custom.
     */

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

    private function setUtentiFromDB_dettaglio($codute, $target) {
        if (!empty($codute)) {
            $dettaglioFilters['CODUTE'] = strtoupper($this->formData[$this->nameForm . '_BTA_CIG']['CODUTE_RUP']);
            if (empty($dettaglioFilters['L1ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L1ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L1ORG]'] != '00') {
                    $dettaglioFilters['L1ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L1ORG]']);
                }
            }
            if (empty($dettaglioFilters['L2ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L2ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L2ORG]'] != '00') {
                    $dettaglioFilters['L2ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L2ORG]']);
                }
            }
            if (empty($dettaglioFilters['L3ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L3ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L3ORG]'] != '00') {
                    $dettaglioFilters['L3ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L3ORG]']);
                }
            }
            if (empty($dettaglioFilters['L4ORG'])) {
                if (!empty($this->formData[$this->nameForm . '_BTA_CIG[L4ORG]']) && $this->formData[$this->nameForm . '_BTA_CIG[L4ORG]'] != '00') {
                    $dettaglioFilters['L4ORG'] = trim($this->formData[$this->nameForm . '_BTA_CIG[L4ORG]']);
                }
            }
        }

        $uteorg = $this->libDB_BOR->leggiBorUteorg($dettaglioFilters, false);

        if (isSet($uteorg) && $uteorg != null) {
            $filtri = array('CODUTE' => strtoupper($codute));
            $data = $this->libDB->leggiGeneric('BOR_UTENTI', $filtri, false);
        } else {
            Out::msgInfo('RUP errato', "L'utente rup non fa parte del servizio richiedente");
            $data = null;
        }
        $this->setUtenti($data, $target);
    }

    private function setUtenti($data, $target) {
        switch ($target) {
            case 'DETTAGLIO':
                Out::valore($this->nameForm . '_BTA_CIG[CODUTE_RUP]', $data['CODUTE']);
                Out::valore($this->nameForm . '_BOR_UTENTI[NOMEUTE]', $data['NOMEUTE']);
                break;
            case 'RICERCA':
                Out::valore($this->nameForm . '_CODUTE_RUP', $data['CODUTE']);
                Out::valore($this->nameForm . '_NOMEUTE', $data['NOMEUTE']);
                break;
        }
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_edit_BTA_CIG[PROGSOGG]_DESC', '');
        Out::valore($this->nameForm . '_BOR_UTENTI[NOMEUTE]', '');
        Out::valore($this->nameForm . '_PROGSOGG', '');
        Out::valore($this->nameForm . '_edit_PROGSOGG_DESC', '');
    }

    protected function postNuovo() {
        $this->pulisciCampi();

        Out::enableField($this->nameForm . '_BTA_CIG[COD_CIG]');

        $this->componentBorOrganDettaglioModel->setLxORG();

        Out::setFocus("", $this->nameForm . '_BTA_CIG[COD_CIG]');

        Out::valore($this->nameForm . '_BTA_CIG[DATAINIZ]', date('d-m-Y'));

        $idorgan = $this->getIdOrganForNew();
        $this->componentBorOrganDettaglioModel->setIdorgan($idorgan);
        Out::valore($this->nameForm . '_BTA_CIG[IDORGAN]', $idorgan);

        Out::hide($this->nameForm . '_BTN_COD_CIG');

        $user = cwbParGen::getUtente();
        $this->setUtentiFromDB_dettaglio($user, 'DETTAGLIO');
		
        // Controllo se Abilitato solo ai propri CIG (RUP)
        if ($this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_RUP) {
            Out::disableField($this->nameForm . '_BTA_CIG[CODUTE_RUP]');
            Out::disableButton($this->nameForm . '_BTA_CIG[CODUTE_RUP]_butt');
        } else {
            Out::enableField($this->nameForm . '_BTA_CIG[CODUTE_RUP]');
            Out::enableButton($this->nameForm . '_BTA_CIG[CODUTE_RUP]_butt');
        }
        
        if($this->apriDettaglioIndex == 'new' && isSet($_POST['COD_CIG'])){
            Out::valore($this->nameForm . '_BTA_CIG[COD_CIG]', $_POST['COD_CIG']);
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_COD_CIG');
        $this->initCombo();
    }

    protected function postAltraRicerca() {
        $this->pulisciCampi();
        Out::setFocus("", $this->nameForm . '_COD_CIG');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_CIG[COD_CIG]');
    }

    protected function postDettaglio() {
        $this->pulisciCampi();

        Out::disableField($this->nameForm . '_BTA_CIG[COD_CIG]');

        $idorgan = ($this->CURRENT_RECORD['IDORGAN'] != 0 ? $this->CURRENT_RECORD['IDORGAN'] : null);

        $this->componentBorOrganDettaglioModel->setIdorgan($idorgan);

        Out::setFocus("", $this->nameForm . '_BTA_CIG[DES_CIG]');

        if ($this->viewMode) {
            Out::hide($this->nameForm . '_BTN_COD_CIG');
        } else {
            Out::show($this->nameForm . '_BTN_COD_CIG');
        }

        //decodifica campi
        $this->setUtentiFromDB_dettaglio($this->CURRENT_RECORD['CODUTE_RUP'], 'DETTAGLIO');
        $this->setProgsoggFromDB('BTA_CIG[PROGSOGG]', $this->CURRENT_RECORD['PROGSOGG'], $this->prefisso_edit);
        
        // Controllo se Abilitato solo ai propri CIG (RUP)
        if ($this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_RUP) {
            Out::disableField($this->nameForm . '_BTA_CIG[CODUTE_RUP]');
            Out::disableButton($this->nameForm . '_BTA_CIG[CODUTE_RUP]_butt');
        } else {
            Out::enableField($this->nameForm . '_BTA_CIG[CODUTE_RUP]');
            Out::enableButton($this->nameForm . '_BTA_CIG[CODUTE_RUP]_butt');
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        // Controllo se Abilitato solo ai propri CIG (RUP, DIRIGENTE ecc.)
        switch ($this->PARAMETRI['RUOLO_CIG']) {
            case cwfCigCupHelper::RUOLO_GENERICO:
                $limita_cig = $this->cigcupHelper->cig_cup_se_generico(cwfCigCupHelper::CIG);
                if (!empty($limita_cig) && $limita_cig[0] == 'GLOBALE') {
                    //utente globale puo vedere tutto
                } else {
                    if (empty($limita_cig)) {
                        $limita_cig = array(-9999999999); // Filtro va applicato senza far trovare nulla
                    }
                    $filtri['PROG_CIG_in'] = $limita_cig;
                }
                break;
            case cwfCigCupHelper::RUOLO_RUP:
                // Se si tratta di ENTRATE il RUP non va controllato
                $filtri['CODUTE_RUP_UTILIZZO_E_S'] = cwbParGen::getSessionVar('nomeUtente');
                break;
            case cwfCigCupHelper::RUOLO_DIRIGENTE:
                $limita_cig = $this->cigcupHelper->cig_cup_se_dirigente(cwfCigCupHelper::CIG);
                if (empty($limita_cig)) {
                    $limita_cig = array(-9999999999); // Filtro va applicato senza far trovare nulla
                }
                $filtri['PROG_CIG_in'] = $limita_cig;
                break;
            case cwfCigCupHelper::RUOLO_OPER_RAG:
                break;
            default:
                break;
        }
        
        // Puo' Visualizzare Cig/Cup delle Strutture Organizzative a 
        //  cui e' Abilitato (se Utente non Globale)
        // Su Ruolo=Operatore di Ragioneria ha visualizzazione su tutto il Bilancio
        if ($this->PARAMETRI['RUOLO_CIG'] != cwfCigCupHelper::RUOLO_OPER_RAG) {
            $filtro_servizi_per_visibilita = $this->bilancioHelper->filtro_servizi_per_visibilita($this->UTENTE_GLOBALE, $this->visibilita['organ']);
            if (!empty($filtro_servizi_per_visibilita) ) {
                if ($this->visib_ciclo_passivo) {
                    $filtri['LISTA_LXORG_UTILIZZO_E_S'] = $filtro_servizi_per_visibilita; 
                } else {
                    $filtri['LISTA_LXORG'] = $filtro_servizi_per_visibilita; 
                }
            }
        }
        
        if (!isSet($filtri['COD_CIG'])) {
            if (!empty($this->formData[$this->nameForm . '_COD_CIG'])) {
                $filtri['COD_CIG'] = trim($this->formData[$this->nameForm . '_COD_CIG']);
            }
        }
        if (!isSet($filtri['DES_BREVE'])) {
            if (!empty($this->formData[$this->nameForm . '_DES_BREVE'])) {
                $filtri['DES_BREVE'] = trim($this->formData[$this->nameForm . '_DES_BREVE']);
            }
        }
        if (!isSet($filtri['DATAINIZIODAL'])) {
            if (!empty($this->formData[$this->nameForm . '_DATAINIZIODAL'])) {
                $filtri['DATAINIZIODAL'] = trim($this->formData[$this->nameForm . '_DATAINIZIODAL']);
            }
        }
        if (!isSet($filtri['CESSATIPRIMADEL'])) {
            if (!empty($this->formData[$this->nameForm . '_CESSATIPRIMADEL'])) {
                $filtri['CESSATIPRIMADEL'] = trim($this->formData[$this->nameForm . '_CESSATIPRIMADEL']);
            }
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
        if (!isSet($filtri['CODUTE_RUP_UTILIZZO_E_S'])) {
            if (!empty($this->formData[$this->nameForm . '_CODUTE_RUP'])) {
                // Se si tratta di ENTRATE il RUP non va controllato
                $filtri['CODUTE_RUP_UTILIZZO_E_S'] = trim($this->formData[$this->nameForm . '_CODUTE_RUP']);
            }
        }
        if (!isSet($filtri['PROGSOGG'])) {
            if (!empty($this->formData[$this->nameForm . '_PROGSOGG'])) {
                $filtri['PROGSOGG'] = trim($this->formData[$this->nameForm . '_PROGSOGG']);
            }
        }
//        if (empty($filtri['RAGSOC'])) {
//            if (isSet($this->formData[$this->nameForm . 'edit_PROGSOGG_DESC']) && $this->formData[$this->nameForm . 'edit_PROGSOGG_DESC'] != '') {
//                $filtri['RAGSOC'] = trim($this->formData[$this->nameForm . 'edit_PROGSOGG_DESC']);
//            }
//        }

        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaCig($filtri, false, $sqlParams);
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if (!empty($_POST['COD_CIG'])) {
            $this->gridFilters['COD_CIG'] = $this->formData['COD_CIG'];
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
        if (!empty($_POST['CODUTE'])) {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
//        if (!empty($_POST['PROGSOGG'])) {
//            $this->gridFilters['PROGSOGG'] = $this->formData['PROGSOGG'];
//        }
//        if (!empty($_POST['RAGSOC'])) {
//            $this->gridFilters['RAGSOC'] = $this->formData['RAGSOC'];
//        }
// Fornitore: Se Intero valido sul Progressivo alrimenti sempre sulla Descrizione
        $fornitore = trim($_POST['PROGSOGG']);
        if (!empty($fornitore)) {
            if (is_numeric($fornitore) && !is_float($fornitore) && intval($fornitore) > 0) {
// Numero Intero valido: Controllo il Progressivo
                $this->gridFilters['PROGSOGG'] = $fornitore;
            } else {
// Testo: Controllo la Descrizione
                $this->gridFilters['RAGSOC'] = $fornitore;
            }
        }

        if (!empty($_POST['CODUTE_RUP'])) {
            $this->gridFilters['CODUTE_RUP'] = $this->formData['CODUTE_RUP'];
        }
        // Validita': Valorizzate sulla Combo 1=Passive 2=Attive 3=Tutte
        if (!empty($_POST['UTILIZZO_E_S']) && $_POST['UTILIZZO_E_S'] != 3) {
            $this->gridFilters['UTILIZZO_E_S'] = $this->formData['UTILIZZO_E_S'] - 1; // Valore su Tabella: 0=Passive 1=Attive
        }
        
        if (!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS'] - 1;
        }
    }

    private function info_fornitore($da_err = '') {
        $messaggio = "";
        $titolo = "Come effettuare la Ricerca";
        if ($da_err) {
            $messaggio = "E' stato indicato in modo errato il Parametro della Ricerca [" . $da_err . "].<br />";
            $titolo = "Parametro Errato sulla Ricerca";
        }
        $messaggio .= "Si possono utilizzare le seguenti modalità di Ricerca:<br />";
        $des_clifor = "Soggetto"; // cwfDictionary::getDescrizione("CLIFOR",'E')."/".cwfDictionary::getDescrizione("CLIFOR","S");
        $messaggio .= "<br />NUMERO : Ricerca il Codice del " . $des_clifor . ". Deve essere un numero intero (es. 1235).";
        $messaggio .= "<br /><br />DESCRIZIONE : Ricerca in qualsiasi parte della Descrizione del " . $des_clifor . " (es. ROSSI)<br />";
        Out::msgInfo($titolo, $messaggio);
    }

    private function assegnaIconaInfo($colname) {
// Siccome il parent non ha ID allora lo creo con lo stesso contenuto.
        $html = $this->crea_cliccabile($colname);
        $contenitore_grid = $this->nameForm . '_divRisultato'; // Contenitore della Grid
        Out::codice('$("#' . $contenitore_grid . '").find("#gs_' . $colname . '").parent().prepend("<span id=\'gs_xico_' . $contenitore_grid . $colname . '\'></span>");');
        Out::html('gs_xico_' . $contenitore_grid . $colname, $html);
    }

    private function initTable() {
        $this->assegnaIconaInfo('PROGSOGG');
        cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $this->GRID_NAME);
        
            // Select Filtro su Colonna 
        $html = '<select id="'.$this->nameForm.'_gs_UTILIZZO_E_S" name="UTILIZZO_E_S" style="width:100%"></select>';
        Out::gridSetColumnFilterHtml($this->nameForm, $this->GRID_NAME, 'UTILIZZO_E_S', $html);
        Out::select($this->nameForm.'_gs_UTILIZZO_E_S', 1, '3', 1, 'Tutto');
        Out::select($this->nameForm.'_gs_UTILIZZO_E_S', 1, '1', 0, "Interna - Cig Richiesto dall'ente da gestire su fatture PASSIVE");
        Out::select($this->nameForm.'_gs_UTILIZZO_E_S', 1, '2', 0, "Altro Ente - Cig Fornito da altro ente per emissione fatture ATTIVE");
        
        // Se gestiti Atti Italsoft visualizzo Colonna Esito CIG
        $atti_italsoft_gestiti = $this->bilancioHelper->gestito_dati_anac_italsoft();
        if ( $atti_italsoft_gestiti ) {
            Out::gridShowCol($this->nameForm."_".$this->GRID_NAME, "ESITO_CIG");
        } else {
            Out::gridHideCol($this->nameForm."_".$this->GRID_NAME, "ESITO_CIG");
        }
    }

    private function crea_cliccabile($key, $tooltip = 'Visualizza Dati', $icona = 'ui-icon-info') {
        $img = "<span class='ui-icon " . $icona . "'></span>"; // Icona standard UI
        $id = $this->nameForm . "_" . $this->prefisso_cliccabili . $key;
        $html = "<span id='" . $id . "'>" . $img . "</span>";
        $icona_cliccabile = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $img, array(), $tooltip);
        return $icona_cliccabile;
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaCig(array('COD_CIG' => $index), false, $sqlParams);
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
            'L1ORG' => $this->nameForm . '_BTA_CIG[L1ORG]',
            'L2ORG' => $this->nameForm . '_BTA_CIG[L2ORG]',
            'L3ORG' => $this->nameForm . '_BTA_CIG[L3ORG]',
            'L4ORG' => $this->nameForm . '_BTA_CIG[L4ORG]',
            'IDORGAN' => $this->nameForm . '_BTA_CIG[IDORGAN]'
        ));
        $this->componentBorOrganDettaglioModel->initSelector(true, $disable);
        $this->componentBorOrganDettaglioModel->setDescriptionWidth(300);
    }

    private function getIdOrganForNew() {
        $idorgan = 0;
        // Puo' Visualizzare Cig/Cup delle Strutture Organizzative a 
        //  cui e' Abilitato (se Utente non Globale)
        // Su Ruolo=Operatore di Ragioneria ha visualizzazione su tutto il Bilancio
        $utente_globale = false;
        if ($this->UTENTE_GLOBALE || $this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_OPER_RAG) {
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

    private function initCombo() {
        Out::select($this->nameForm . '_BTA_CIG[UTILIZZO_E_S]', 1, "0", 1, "Interna - Cig Richiesto dall'ente da gestire su fatture PASSIVE");
        Out::select($this->nameForm . '_BTA_CIG[UTILIZZO_E_S]', 1, "1", 0, "Altro Ente - Cig Fornito da altro ente per emissione fatture ATTIVE");
    }

    protected function elaboraRecords($Result_tab) {
        if (is_array($Result_tab)) {
            // Se gestiti Atti Italsoft visualizzo Colonna Esito CIG (o altro)
            $atti_italsoft_gestiti = $this->bilancioHelper->gestito_dati_anac_italsoft();
            
            foreach ($Result_tab as $key => $Result_rec) {
                
                switch ($Result_rec['UTILIZZO_E_S']) {
                    case 0:
                        $Result_tab[$key]['UTILIZZO_E_S'] = 'Interna';
                        break;
                    case 1:
                        $Result_tab[$key]['UTILIZZO_E_S'] = 'Altro ente';
                        break;
                }
                $Result_tab[$key]['IMPO_GARA'] = number_format(floatval($Result_rec['IMPO_GARA']), 2, ', ', '.');
                $Result_tab[$key]['IMPO_AGGIUDIC'] = number_format(floatval($Result_rec['IMPO_AGGIUDIC']), 2, ', ', '.');

                // Fornitore
                $Result_tab[$key]['PROGSOGG'] = ""; // Evito uno 0
                if ($Result_rec['PROGSOGG']) {
                    $forni = "<span style='font-weight: bold;'>" . $Result_rec['PROGSOGG'] . "</span> - " . trim($Result_rec['RAGSOC']);
                    $Result_tab[$key]['PROGSOGG'] = '<div style="width: 100%; text-align:left;"> ' . $forni . '</div>';
                }
                
                $esito_cig = '';
                if ( $atti_italsoft_gestiti ) {
                    $ris_esito_cig = $this->bilancioHelper->esitoCIG( $Result_rec['COD_CIG'] ); // $Result_rec['COD_CIG']
                    $esito_cig = $ris_esito_cig['html']; // Colore senza la Descrizione
                }
                $Result_tab[$key]['ESITO_CIG'] = $esito_cig;
            }
        }

        return $Result_tab;
    }

    protected function situazioneCig($codcigris) {
        $model = cwbLib::apriFinestra('cwfSituazioneCig', $this->nameFormOrig, ",", array(), $this->nameForm);
        $model->FILTRI['COD_CIG'] = $codcigris;
        $model->FILTRI['TIPO_CIG_CUP'] = cwfCigCupHelper::CIG;
        $model->parseEvent();
    }

    protected function riepilogoCig($codcigriep) {
        $model = cwbLib::apriFinestra('cwfRiepilogoCig', $this->nameFormOrig, ",", array(), $this->nameForm);
        $model->FILTRI['COD_CIG'] = $codcigriep;
        $model->FILTRI['TIPO_CIG_CUP'] = cwfCigCupHelper::CIG;
        $model->parseEvent();
    }

    private function apriFinestraBtaSogg($progsogg, $campo, $return) {
        $apro_lista = false;
        $postData = array(
            'soggOrigin_CLIFOR' => true // Per la sola Finanziaria
        );
        $externalFilter = array();
        $externalFilter['QUALIVEDO'] = array();
        $externalFilter['QUALIVEDO']['PERMANENTE'] = false;
        $externalFilter['QUALIVEDO']['VALORE'] = 4;
        if (!empty($progsogg)) {
            $externalFilter['PROGSOGG'] = array();
            $externalFilter['PROGSOGG']['PERMANENTE'] = false;
            $externalFilter['PROGSOGG']['VALORE'] = $progsogg;
            $apro_lista = true;
        }

        cwbLib::apriFinestraRicerca('cwbBtaSogg', $this->nameForm, $return, $campo, $apro_lista, $externalFilter, $this->nameFormOrig, '', $postData);
    }

    private function setProgsogg($campo, $data, $prefi = '') {
        Out::valore($this->nameForm . '_' . $campo, $data['PROGSOGG']);
        Out::valore($this->nameForm . '_' . $prefi . $campo . '_DESC', (trim($data['RAGSOC'])));
    }

    private function setProgsoggFromDB($campo, $progsogg, $prefi = '') {
        $data = null;
        if (!empty($progsogg)) {
            $filtri = array(
                'PROGSOGG' => $progsogg
            );
            $data = $this->libDB_FTA->leggiFtaClfor($filtri, false);
        }
        $this->setProgsogg($campo, $data, $prefi);
    }

    private function initForm() {
        $titolo = '';
        if ($this->PARAMETRI['INTERROGAZIONE']) {
            $titolo .= 'Consultazione CIG ';
        } else {
            $titolo .= 'Codice Identificativo Gara ';
        }
        $titolo .= '- Utente: ' . cwbParGen::getNomeUte();
        $titolo .= ' Tip. Operatore: ' . $this->cigcupHelper->descrizione_ruolo_cig_cup($this->PARAMETRI['RUOLO_CIG']);
        if (!$this->PARAMETRI['INTERROGAZIONE']) {
            $titolo .= ' (' . $this->cigcupHelper->descrizione_abilitazione_cig_cup($this->PARAMETRI['MODIFICABILITA']) . ')';
        }
        Out::setGridCaption($this->nameForm . "_" . $this->GRID_NAME, $titolo);
        if ($this->PARAMETRI['INTERROGAZIONE']) {
            Out::gridHideCol($this->nameForm . "_" . $this->GRID_NAME, "UTILIZZO_E_S");
        }

        // Controllo se Abilitato solo ai propri CIG (RUP)
        if ($this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_RUP) {
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

    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna, $SmartCIG = false, $DatiAnac = false, $Situazione = false, $Riepilogo = false, $SmartCIG_Edit = false, $DatiAnac_Edit = false, $Situazione_Edit = false, $Riepilogo_Edit = false) {

        if ($this->PARAMETRI['INTERROGAZIONE'] || $this->viewMode) {
// Si tratta di una Interrogazione. Vengono Nascosti i Pulsanti
// per Gestire le Operazioni e vengono Mantenuti quelli
// delle Ricerche e Consultazioni.
            $nuovo = false;
            $aggiungi = false;
            $aggiorna = false;
            $cancella = false;
        }
        $DatiAnac ? Out::show($this->nameForm . '_DatiAnac') : Out::hide($this->nameForm . '_DatiAnac');
        $Situazione ? Out::show($this->nameForm . '_Situazione') : Out::hide($this->nameForm . '_Situazione');
        $Riepilogo ? Out::show($this->nameForm . '_Riepilogo') : Out::hide($this->nameForm . '_Riepilogo');
        $SmartCIG ? Out::show($this->nameForm . '_SmartCIG') : Out::hide($this->nameForm . '_SmartCIG');

        $DatiAnac_Edit ? Out::show($this->nameForm . '_DatiAnac_Edit') : Out::hide($this->nameForm . '_DatiAnac_Edit');
        $Situazione_Edit ? Out::show($this->nameForm . '_Situazione_Edit') : Out::hide($this->nameForm . '_Situazione_Edit');
        $Riepilogo_Edit ? Out::show($this->nameForm . '_Riepilogo_Edit') : Out::hide($this->nameForm . '_Riepilogo_Edit');
        $SmartCIG_Edit ? Out::show($this->nameForm . '_SmartCIG_Edit') : Out::hide($this->nameForm . '_SmartCIG_Edit');


        parent::setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna);
    }

    protected function setVisRisultato() {
//$divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna, 
//$DatiAnac = false, $Situazione = false, $Riepilogo = false, $SmartCIG, $DatiAnac_Edit = false, $Situazione_Edit = false, $Riepilogo_Edit = false, $SmartCIG_Edit

        $this->setVisControlli(false, true, false, true, false, false, false, false, true, false, true, true, true, true, false, false, false, false);
    }

    protected function setVisDettaglio() {
// In pratica serve solo per Visualizzare "Situazione", "Riepilogo" ecc.
// Sovrascrivo metodo standard ed aggiungo sola visualizzazione pulsanti consultazione.
        if ($this->viewMode) {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, false, false, false, false, false, true, false, false, false, false, false, false, false, false);
            } else {
                $this->setVisControlli(true, false, false, false, false, false, false, false, true, true, false, false, false, false, true, true, true, true);
            }
        } else {
            if (isSet($this->apriDettaglioIndex)) {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true, false, false, false, false, false, false, false, false);
            } else {
                $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, true, true, false, false, false, false, true, true, true, true);
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
                'COD_CIG'=>$index
        );
        $row_mod = $this->libDB->leggiBtaCig($filtri, false);
        if (!empty($row_mod)) {
            $UTILIZZO_E_S = $row_mod['UTILIZZO_E_S']; // 0=Uscite 1=Entrate
            // Controllo se Abilitato solo ai propri CIG (RUP). 
            if ($this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_RUP) {
                if (strtoupper($row_mod['CODUTE_RUP']) != $codute) {
                    // Se si tratta di ENTRATE il RUP non va controllato
                    if ($UTILIZZO_E_S != 1) {
                        // Non e' Entrate per cui applico controllo
                        $puo_modificare = false;
                        $deser .= "<br />Utente Abilitato a gestire solo i propri CIG"; // Segnalazione Errore
                    }
                }
            }

            // Controlli sulla Struttura Organizzativa (se c'e')
            if ($puo_modificare && !empty($row_mod['IDORGAN']) ) {
                // Leggo Autorizzazioni BTA-x dell'Utente per l'Assegnatario del
                // Cig/Cup (magari sta su piu' servizi con differenti Ruoli)
                $auth_BTA_serv = $this->bilancioHelper->autorizzazioni_sul_servizio($row_mod['IDORGAN'], null, 'BTA', null);
                if ( empty($auth_BTA_serv[$this->AUTOR_NUMERO]) ) {
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
                    if (!empty($filtro_servizi_per_visibilita) ) {
                        // Controllo se e' uno dei servizi a cui e' Abilitato
                        $serv_gestibile = false;
                        foreach($filtro_servizi_per_visibilita as $keyass=>$assegnatario) {
                            // Controllo se e' una Struttura Organizzativa Gestibile
                            $gestib = true;
                            for ($nass=1; $nass<=4; $nass++) {
                                if (isSet($assegnatario['L'.$nass.'ORG']) && !empty($assegnatario['L'.$nass.'ORG']) && $assegnatario['L'.$nass.'ORG'] != '00') {
                                    if ($row_mod['L'.$nass.'ORG'] != $assegnatario['L'.$nass.'ORG']) {
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
                            if ($this->UTENTE_GLOBALE || $this->PARAMETRI['RUOLO_CIG'] == cwfCigCupHelper::RUOLO_OPER_RAG) {
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
            Out::msgInfo('Modifica Impossibile',$deser);
        }
    }
}

?>