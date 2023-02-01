<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

//include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaLocal() {
    $cwbBtaLocal = new cwbBtaLocal();
    $cwbBtaLocal->parseEvent();
    return;
}

class cwbBtaLocal extends cwbBpaGenTab {

//test
    private $italiano = true;
    private $validAnpr = false;
    private $validAnprLocal = false;

    protected function initVars() {
        $this->GRID_NAME = 'gridBtaLocal';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 4;
        $this->italiano = cwbParGen::getFormSessionVar($this->nameForm, 'italiano');
        $this->validAnpr = cwbParGen::getFormSessionVar($this->nameForm, 'validAnpr');
        $this->libDB = new cwbLibDB_BTA();
//        $this->libCalcoli = new cwbLibCalcoli();
        $this->errorOnEmpty = false;
//        $this->elencaAutoAudit = true;
//        $this->elencaAutoFlagDis = true;
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'italiano', $this->italiano); // salvo in sessione il record
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DESLOCAL'] != '') {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $this->formData['PROVINCIA'];
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESCONSOL_decod_butt':
                        $this->decodConsol($this->formData[$this->nameForm . '_CODCONSOL'], ($this->nameForm . '_CODCONSOL'), $this->formData[$this->nameForm . '_DESCONSOL_decod'], ($this->nameForm . '_DESCONSOL_decod'), true);
                        break;
                    case $this->nameForm . '_DESCONSOL_DECOD_butt':
                        $this->decodConsol($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'], ($this->nameForm . '_BTA_LOCAL[CODCONSOL]'), $_POST[$this->nameForm . '_DESCONSOL_DECOD'], ($this->nameForm . '_DESCONSOL_DECOD'), true, true);
                        break;
                    case $this->nameForm . '_DESNAZI_decod_butt':
                        $this->decodNazion($_POST[$this->nameForm . '_ISTNAZPRO_2'], $this->nameForm . '_ISTNAZPRO_2', $this->nameForm . '_DESNAZI_decod', null, null, null, $_POST[$this->nameForm . '_DESNAZI_decod'], true);
                        break;
                    case $this->nameForm . '_DESTRIBU_decod_butt':
                        $this->decodTribu($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODTRIBUN'], ($this->nameForm . '_BTA_LOCAL[CODTRIBUN]'), $_POST[$this->nameForm . '_DESTRIBU_decod'], ($this->nameForm . '_DESTRIBU_decod'), true);
                        break;
                    case $this->nameForm . '_DESTRIBU_2_decod_butt':
                        $this->decodTribu($this->formData[$this->nameForm . '_CODTRIBUN_2'], ($this->nameForm . '_CODTRIBUN_2'), $this->formData[$this->nameForm . '_DESTRIBU_2_decod'], ($this->nameForm . '_DESTRIBU_2_decod'), true);
                        break;
                    case $this->nameForm . '_paneIndici':
                        $this->TabIndici();
                        break;
                    case $this->nameForm . '_paneVista':
                        $this->TabVista();
                        break;
                    case $this->nameForm . '_ComItal':
                        $this->ComuniItaliani();
                        break;
                    case $this->nameForm . '_paneItaliani':
                        $this->Italiani();
                        break;
                    case $this->nameForm . '_paneEsteri':
                        $this->Esteri();
                        break;
                }
                break;
            case 'returnFromBtaConsol':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESCONSOL_decod_butt':
                    case $this->nameForm . '_DESCONSOL_decod':
                    case $this->nameForm . '_CODCONSOL':
                        Out::valore($this->nameForm . '_CODCONSOL', $this->formData['returnData']['CODCONSOL']);
                        Out::valore($this->nameForm . '_DESCONSOL_decod', $this->formData['returnData']['DESCONSOL']);
                        break;
                    case $this->nameForm . '_DESCONSOL_DECOD_butt':
                    case $this->nameForm . '_BTA_LOCAL[CODCONSOL]':
                    case $this->nameForm . '_DESCONSOL_DECOD':
                        Out::valore($this->nameForm . '_BTA_LOCAL[CODCONSOL]', $this->formData['returnData']['CODCONSOL']);
                        Out::valore($this->nameForm . '_DESCONSOL_DECOD', $this->formData['returnData']['DESCONSOL']);
                        Out::valore($this->nameForm . '_DES_LOCAL_CONSOL', $this->formData['returnData']['DESLOCAL']);
                        break;
                }
                break;

            case 'returnFromBtaTribu':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESTRIBU_decod_butt':
                    case $this->nameForm . '_DESTRIBU_decod':
                    case $this->nameForm . '_BTA_LOCAL[CODTRIBUN]':
                        Out::valore($this->nameForm . '_BTA_LOCAL[CODTRIBUN]', $this->formData['returnData']['CODTRIBUN']);
                        Out::valore($this->nameForm . '_DESTRIBU_decod', $this->formData['returnData']['DESTRIBU']);
                        break;
                    case $this->nameForm . '_DESTRIBU_2_decod_butt':
                    case $this->nameForm . '_DESTRIBU_2_decod':
                    case $this->nameForm . '_CODTRIBUN_2':
                        Out::valore($this->nameForm . '_CODTRIBUN_2', $this->formData['returnData']['CODTRIBUN']);
                        Out::valore($this->nameForm . '_DESTRIBU_2_decod', $this->formData['returnData']['DESTRIBU']);
                        break;
                }
                break;

            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESNAZI_decod_butt':
                    case $this->nameForm . '_ISTNAZPRO_2':
                    case $this->nameForm . '_DESNAZI_decod':
                        $this->DecodLocalIstnazpro($this->formData['returnData']['CODNAZI'], null);
                        Out::valore($this->nameForm . '_ISTNAZPRO_2', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_CODNA_IST', $this->formData['returnData']['CODNA_IST']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODNAZPRO_2': // Assegnazione automatica progressivo località. Replico funzionamento OMNIS                       
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODNAZPRO_2'], $this->nameForm . '_CODNAZPRO_2')) {
                            $progre = $this->TrovaProgre($_POST[$this->nameForm . '_CODNAZPRO_2'], '');
                            if (!$progre['CONTA']) {
                                Out::msgStop('Attenzione', "Non risulta inserita nessuna località per il progressivo Stato" . " " . $_POST[$this->nameForm . '_CODNAZPRO_2'] . "." . "E' consigliabile inserire dapprima il progressivo località 0, come proposto dal programma");
                            }
                            $key = true;
                            while ($key) {
                                $conta = $this->TrovaProgre($_POST[$this->nposameForm . '_CODNAZPRO_2'], $progre['CONTA']);
                                if ($conta['CONTA'] == 0) {
                                    $key = false;
                                    Out::valore($this->nameForm . '_CODNAZPRO_2', str_pad($_POST[$this->nameForm . '_CODNAZPRO_2'], 3, '0', STR_PAD_LEFT)); // formatto valori per visualizzazione
                                    Out::valore($this->nameForm . '_CODLOCAL_2', str_pad($progre['CONTA'], 3, '0', STR_PAD_LEFT));
                                } else {
                                    $progre['CONTA'] = $progre['CONTA'] + 1;
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_CODLOCAL_2':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODLOCAL_2'], $this->nameForm . '_CODLOCAL_2');
                        break;
                    case $this->nameForm . '_PROVINCIA_2':
                        $this->decodNazionSigla($_POST[$this->nameForm . '_PROVINCIA_2'], ($this->nameForm . '_PROVINCIA_2'), ($this->nameForm . '_DESNAZION_DECOD'));
                        break;
                    case $this->nameForm . '_BTA_LOCAL[F_ECCEZIONALE]':
                        $checked = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_ECCEZIONALE'];
                        if ($checked) {
                            Out::valore($this->nameForm . '_ISTNAZPRO_2', 998);
                            Out::valore($this->nameForm . '_CODNA_IST', 0);
                        } else {
                            Out::valore($this->nameForm . '_ISTNAZPRO_2', ' ');
                            Out::valore($this->nameForm . '_CODNA_IST', ' ');
                        }
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODNAZPRO]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], $this->nameForm . '_BTA_LOCAL[CODNAZPRO]')) {
                            Out::valore($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], 3, '0', STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODLOCAL]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], $this->nameForm . '_BTA_LOCAL[CODLOCAL]')) {
                            Out::valore($this->nameForm . '_BTA_LOCAL[CODLOCAL]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], 3, '0', STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_BTA_LOCAL[ISTNAZPRO]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ISTNAZPRO'], $this->nameForm . '_BTA_LOCAL[ISTNAZPRO]')) {
                            Out::valore($this->nameForm . '_BTA_LOCAL[ISTNAZPRO]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ISTNAZPRO'], 3, '0', STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_BTA_LOCAL[ISTLOCAL]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ISTLOCAL'], $this->nameForm . '_BTA_LOCAL[ISTLOCAL]')) {
                            Out::valore($this->nameForm . '_BTA_LOCAL[ISTLOCAL]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ISTLOCAL'], 3, '0', STR_PAD_LEFT));
                        }
                        break;
                    case $this->nameForm . '_CODCONSOL':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODCONSOL'], $this->nameForm . '_CODCONSOL')) {
                            $this->decodConsol($this->formData[$this->nameForm . '_CODCONSOL'], ($this->nameForm . '_CODCONSOL'), $this->formData[$this->nameForm . '_DESCONSOL_decod'], ($this->nameForm . '_DESCONSOL_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESCONSOL_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESCONSOL_decod':
                        $this->decodConsol(null, ($this->nameForm . '_CODCONSOL'), $this->formData[$this->nameForm . '_DESCONSOL_decod'], ($this->nameForm . '_DESCONSOL_decod'));

                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODCONSOL]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'], $this->nameForm . '_BTA_LOCAL[CODCONSOL]')) {
                            $this->decodConsol($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'], ($this->nameForm . '_BTA_LOCAL[CODCONSOL]'), $_POST[$this->nameForm . '_DESCONSOL_DECOD'], ($this->nameForm . '_DESCONSOL_DECOD'), false, true);
                        } else {
                            Out::valore($this->nameForm . '_DESCONSOL_DECOD', '');
                            Out::valore($this->nameForm . '_DES_LOCAL_CONSOL', ' ');
                        }
                        break;
                    case $this->nameForm . '_DESCONSOL_DECOD':
                        $this->decodConsol(null, ($this->nameForm . '_BTA_LOCAL[CODCONSOL]'), $_POST[$this->nameForm . '_DESCONSOL_DECOD'], ($this->nameForm . '_DESCONSOL_DECOD'), false, true);

                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODTRIBUN]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODTRIBUN'], $this->nameForm . '_BTA_LOCAL[CODTRIBUN]')) {
                            $this->decodTribu($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODTRIBUN'], ($this->nameForm . '_BTA_LOCAL[CODTRIBUN]'), $_POST[$this->nameForm . '_DESTRIBU_decod'], ($this->nameForm . '_DESTRIBU_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESTRIBU_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESTRIBU_decod':
                        $this->decodTribu(null, ($this->nameForm . '_BTA_LOCAL[CODTRIBUN]'), $_POST[$this->nameForm . '_DESTRIBU_decod'], ($this->nameForm . '_DESTRIBU_decod'));

                        break;
                    case $this->nameForm . '_CODTRIBUN_2':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODTRIBUN_2'], $this->nameForm . '_CODTRIBUN_2')) {
                            $this->decodTribu($this->formData[$this->nameForm . '_CODTRIBUN_2'], ($this->nameForm . '_CODTRIBUN_2'), $this->formData[$this->nameForm . '_DESTRIBU_2_decod'], ($this->nameForm . '_DESTRIBU_2_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESTRIBU_2_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESTRIBU_2_decod':
                        $this->decodTribu(null, ($this->nameForm . '_CODTRIBUN_2'), $this->formData[$this->nameForm . '_DESTRIBU_2_decod'], ($this->nameForm . '_DESTRIBU_2_decod'));

                        break;
                    case $this->nameForm . '_DESNAZI_decod': // In base alla nazione selezionata, precompilo la form.
// Sto replicando il funzionamento del vecchio (metodo "decod_local").
                        $this->DecodLocalIstnazpro($_POST[$this->nameForm . '_ISTNAZPRO_2'], $_POST[$this->nameForm . '_DESNAZI_decod']);

                        break;
                    case $this->nameForm . '_ISTNAZPRO_2': // In base alla nazione selezionata, precompilo la form.
// Sto replicando il funzionamento del vecchio (metodo "decod_local").
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_ISTNAZPRO_2'], $this->nameForm . '_ISTNAZPRO_2')) {
                            $this->DecodLocalIstnazpro($_POST[$this->nameForm . '_ISTNAZPRO_2'], null);
                        } else {
                            Out::valore($this->nameForm . '_DESNAZI_decod', '');
                        }
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODNAZPROA]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPROA'], $this->nameForm . '_BTA_LOCAL[CODNAZPROA]');
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODLOCALA]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCALA'], $this->nameForm . '_BTA_LOCAL[CODLOCALA]');
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODCNC]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCNC'], $this->nameForm . '_BTA_LOCAL[CODCNC]');
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODAMBITO]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODAMBITO'], $this->nameForm . '_BTA_LOCAL[CODAMBITO]');
                        break;
                    case $this->nameForm . '_BTA_LOCAL[CODRURAL]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODRURAL'], $this->nameForm . '_BTA_LOCAL[CODRURAL]');
                        break;
                    case $this->nameForm . '_CODNAZPROA_2':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODNAZPROA_2'], $this->nameForm . '_CODNAZPROA_2');
                        break;
                    case $this->nameForm . '_CODLOCALA_2':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODLOCALA_2'], $this->nameForm . '_CODLOCALA_2');
                        break;
                    case $this->nameForm . '_CODRURAL_2':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODRURAL_2'], $this->nameForm . '_CODRURAL_2');
                        break;
                    case $this->nameForm . '_CODNAZPRO_da':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODNAZPRO_da'], $this->nameForm . '_CODNAZPRO_da');
                        break;
                    case $this->nameForm . '_CODNAZPRO_a':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODNAZPRO_a'], $this->nameForm . '_CODNAZPRO_a');
                        break;
                    case $this->nameForm . '_CODLOCAL':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODLOCAL'], $this->nameForm . '_CODLOCAL');
                        break;
                    case $this->nameForm . '_NREGIONE':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_NREGIONE'], $this->nameForm . '_NREGIONE');
                        break;
                    case $this->nameForm . '_CODCNC':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODCNC'], $this->nameForm . '_CODCNC');
                        break;
                }
                break;
        }
    }

    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_DESLOCAL');
    }

    protected function postApriForm() {
        $this->initComboPosGeog();
        $this->initComboRegione();
        Out::setFocus('', $this->nameForm . '_DESLOCAL');
        $this->validAnprLocal = $this->libDB->leggiValidLocalAnpr();
        if ($this->validAnprLocal['CONTA'] > 5000) {
            $this->validAnpr = true;
            cwbParGen::setFormSessionVar($this->nameForm, 'validAnpr', $this->validAnpr);
        }

        Out::valore($this->nameForm . '_ricSoloAttive', 0);
        Out::valore($this->nameForm . '_ricSoloValidatiAnpr', 1);
        Out::valore($this->nameForm . '_ricComuniItaliani', 1);
        Out::valore($this->nameForm . '_ricLocEstere', 1);
        Out::valore($this->nameForm . '_ricLuogoEccezionale', 1);
    }

    protected function postNuovo() {
        Out::tabEnable($this->nameForm . '_tabGestione', $this->nameForm . '_paneEsteri');
        Out::tabEnable($this->nameForm . '_tabGestione', $this->nameForm . '_paneItaliani');
        Out::tabSelect($this->nameForm . '_tabGestione', $this->nameForm . '_paneItaliani');

//campi LocalitÃ  Italiana
        Out::attributo($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', 'readonly', '1');
        Out::attributo($this->nameForm . '_BTA_LOCAL[CODLOCAL]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_BTA_LOCAL[CODLOCAL]', 'background-color', '#FFFFFF');

//campi LocalitÃ  Estera
        Out::attributo($this->nameForm . '_CODNAZPRO_2', 'readonly', '1');
        Out::attributo($this->nameForm . '_CODLOCAL_2', 'readonly', '1');
        Out::css($this->nameForm . '_CODNAZPRO_2', 'background-color', '#FFFFFF');
        Out::css($this->nameForm . '_CODLOCAL_2', 'background-color', '#FFFFFF');

        Out::setFocus("", $this->nameForm . '_BTA_LOCAL[CODNAZPRO]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
//attivo entrambe le tab
        Out::tabEnable($this->nameForm . '_tabGestione', $this->nameForm . '_paneEsteri');
        Out::tabEnable($this->nameForm . '_tabGestione', $this->nameForm . '_paneItaliani');

//controllo quale tab devo abilitare o disabilitare e la seleziono
        if ($this->CURRENT_RECORD['F_ITA_EST'] == 0) {

//LocalitÃ  Italiana
            $this->italiano = true;
            Out::tabDisable($this->nameForm . '_tabGestione', $this->nameForm . '_paneEsteri');
            Out::tabSelect($this->nameForm . '_tabGestione', $this->nameForm . '_paneItaliani');
            Out::attributo($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', 'readonly', '0');
            Out::attributo($this->nameForm . '_BTA_LOCAL[CODLOCAL]', 'readonly', '0');
            Out::css($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', 'background-color', '#FFFFE0');
            Out::css($this->nameForm . '_BTA_LOCAL[CODLOCAL]', 'background-color', '#FFFFE0');
            Out::setFocus("", $this->nameForm . '_BTA_LOCAL[ISTNAZPRO]');
        } else {

//Località  Estera
            $this->italiano = false;
            Out::tabDisable($this->nameForm . '_tabGestione', $this->nameForm . '_paneItaliani');
            Out::tabSelect($this->nameForm . '_tabGestione', $this->nameForm . '_paneEsteri');
            Out::attributo($this->nameForm . '_CODNAZPRO_2', 'readonly', '0');
            Out::attributo($this->nameForm . '_CODLOCAL_2', 'readonly', '0');
            Out::css($this->nameForm . '_CODNAZPRO_2', 'background-color', '#FFFFE0');
            Out::css($this->nameForm . '_CODLOCAL_2', 'background-color', '#FFFFE0');

//valorizzazione manuale dei campi Esteri
            Out::valore($this->nameForm . '_ISTNAZPRO_2', $this->CURRENT_RECORD['ISTNAZPRO']);
            Out::valore($this->nameForm . '_CODNAZPRO_2', str_pad($this->CURRENT_RECORD['CODNAZPRO'], 3, '0', STR_PAD_LEFT));
            Out::valore($this->nameForm . '_CODLOCAL_2', str_pad($this->CURRENT_RECORD['CODLOCAL'], 3, '0', STR_PAD_LEFT));
            Out::valore($this->nameForm . '_DESLOCAL_2', trim($this->CURRENT_RECORD['DESLOCAL']));
            Out::valore($this->nameForm . '_CAP_2', $this->CURRENT_RECORD['CAP']);
            Out::valore($this->nameForm . '_PROVINCIA_2', trim($this->CURRENT_RECORD['PROVINCIA']));
            Out::valore($this->nameForm . '_CODBELFI_2', $this->CURRENT_RECORD['CODBELFI']);
            Out::valore($this->nameForm . '_CODNAZPROA_2', $this->CURRENT_RECORD['CODNAZPROA']);
            Out::valore($this->nameForm . '_CODLOCALA_2', $this->CURRENT_RECORD['CODLOCALA']);
            Out::valore($this->nameForm . '_CODRURAL_2', $this->CURRENT_RECORD['CODRURAL']);
            Out::valore($this->nameForm . '_CODTRIBUN_2', $this->CURRENT_RECORD['CODTRIBUN']);
            Out::valore($this->nameForm . '_DATAINIZ_2', $this->CURRENT_RECORD['DATAINIZ']);
            Out::valore($this->nameForm . '_DATAFINE_2', $this->CURRENT_RECORD['DATAFINE']);
            $this->decodNazion($this->CURRENT_RECORD['ISTNAZPRO'], $this->nameForm . '_ISTNAZPRO_2', ($this->nameForm . '_DESNAZI_decod'), '', ($this->nameForm . '_DESNAZION_DECOD'), $this->nameForm . '_CODNA_IST');
            Out::setFocus("", $this->nameForm . '_ISTNAZPRO_2');
        }

// Decodifiche
        $this->decodTribu($this->CURRENT_RECORD['CODTRIBUN'], ($this->nameForm . '_BTA_LOCAL[CODTRIBUN]'), null, ($this->nameForm . '_DESTRIBU_decod'));
        $this->decodTribu($this->CURRENT_RECORD['CODTRIBUN'], ($this->nameForm . '_CODTRIBUN_2'), null, ($this->nameForm . '_DESTRIBU_2_decod'));
        $this->decodConsol($this->CURRENT_RECORD['CODCONSOL'], ($this->nameForm . '_BTA_LOCAL[CODCONSOL]'), null, ($this->nameForm . '_DESCONSOL_DECOD'), false, true);

// Formatta campi
        Out::valore($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', str_pad($this->CURRENT_RECORD['CODNAZPRO'], 3, '0', STR_PAD_LEFT));
        Out::valore($this->nameForm . '_BTA_LOCAL[CODLOCAL]', str_pad($this->CURRENT_RECORD['CODLOCAL'], 3, '0', STR_PAD_LEFT));
        Out::valore($this->nameForm . '_BTA_LOCAL[ISTNAZPRO]', str_pad($this->CURRENT_RECORD['ISTNAZPRO'], 3, '0', STR_PAD_LEFT));
        Out::valore($this->nameForm . '_BTA_LOCAL[ISTLOCAL]', str_pad($this->CURRENT_RECORD['ISTLOCAL'], 3, '0', STR_PAD_LEFT));

// Valorizza campi da impostare manualmente
        Out::valore($this->nameForm . '_PROVINCIA_2', $this->CURRENT_RECORD['PROVINCIA']);
        Out::valore($this->nameForm . '_CAP_2', $this->CURRENT_RECORD['CAP']);

        Out::attributo($this->nameForm . '_BTA_LOCAL[CODNAZPRO]', 'readonly', '0');
    }

    protected function formDataToCurrentRecord() {
        parent::formDataToCurrentRecord();
// Valorizzazione campo-campo per località estera:
        if (!$this->italiano) {
            $this->CURRENT_RECORD = array();
            $this->CURRENT_RECORD['CODNAZPRO'] = $_POST[$this->nameForm . '_CODNAZPRO_2'];
            $this->CURRENT_RECORD['CODLOCAL'] = $_POST[$this->nameForm . '_CODLOCAL_2'];
            $this->CURRENT_RECORD['ISTNAZPRO'] = $_POST[$this->nameForm . '_ISTNAZPRO_2'];
            $this->CURRENT_RECORD['DESLOCAL'] = $_POST[$this->nameForm . '_DESLOCAL_2'];
            $this->CURRENT_RECORD['CAP'] = $_POST[$this->nameForm . '_CAP_2'];
            $this->CURRENT_RECORD['PROVINCIA'] = $_POST[$this->nameForm . '_PROVINCIA_2'];
            $this->CURRENT_RECORD['CODBELFI'] = $_POST[$this->nameForm . '_CODBELFI_2'];
            $this->CURRENT_RECORD['CODNAZPROA'] = $_POST[$this->nameForm . '_CODNAZPROA_2'];
            $this->CURRENT_RECORD['CODLOCALA'] = $_POST[$this->nameForm . '_CODLOCALA_2'];
            $this->CURRENT_RECORD['CODRURAL'] = $_POST[$this->nameForm . '_CODRURAL_2'];
            $this->CURRENT_RECORD['CODTRIBUN'] = $_POST[$this->nameForm . '_CODTRIBUN_2'];
            $this->CURRENT_RECORD['CODCONSOL'] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODCONSOL'];
            $this->CURRENT_RECORD['F_ITA_EST'] = 1;
            if (cwbLibCheckInput::IsNBZ($_POST[$this->nameForm . '_DATAINIZ_2'])) {
                $this->CURRENT_RECORD['DATAINIZ'] = '1900-01-01';
            } else {
                $this->CURRENT_RECORD['DATAINIZ'] = $_POST[$this->nameForm . '_DATAINIZ_2'];
            }

            $this->CURRENT_RECORD['DATAFINE'] = $_POST[$this->nameForm . '_DATAFINE_2'];
        }
//Valorizzo Audit
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $this->CURRENT_RECORD['CODUTE'] = cwbParGen::getSessionVar('nomeUtente');
        $this->CURRENT_RECORD['DATAOPER'] = $currentDate;
        $this->CURRENT_RECORD['TIMEOPER'] = $currentTime;
    }

    protected function postPulisciCampi() {

//pulisco campi Tab Italiani
        Out::valore($this->nameForm . '_DESTRIBU_decod', '');

//pulisco campi Tab Esteri
        Out::valore($this->nameForm . '_ISTNAZPRO_2', '');
        Out::valore($this->nameForm . '_DESNAZI_decod', '');
        Out::valore($this->nameForm . '_DESNAZION_DECOD', '');
        Out::valore($this->nameForm . '_DESTRIBU_2_decod', '');
        Out::valore($this->nameForm . '_DESCONSOL_DECOD', '');
        Out::valore($this->nameForm . '_DES_LOCAL_CONSOL', '');
        Out::valore($this->nameForm . '_CODNA_IST', '');
        Out::valore($this->nameForm . '_CODNAZPRO_2', '');
        Out::valore($this->nameForm . '_CODLOCAL_2', '');
        Out::valore($this->nameForm . '_DESLOCAL_2', '');
        Out::valore($this->nameForm . '_CAP_2', '');
        Out::valore($this->nameForm . '_PROVINCIA_2', '');
        Out::valore($this->nameForm . '_CODBELFI_2', '');
        Out::valore($this->nameForm . '_CODNAZPROA_2', '');
        Out::valore($this->nameForm . '_CODLOCALA_2', '');
        Out::valore($this->nameForm . '_CODRURAL_2', '');
        Out::valore($this->nameForm . '_CODTRIBUN_2', '');
        Out::valore($this->nameForm . '_DATAINIZ_2', '');
        Out::valore($this->nameForm . '_DATAFINE_2', '');
    }

    protected function preNuovo() {
        $this->italiano = true;
    }

    private function initComboPosGeog() {
        Out::select($this->nameForm . '_BTA_LOCAL[NORDSUD]', 1, "S", 1, "S = Italia Settentrionale");
        Out::select($this->nameForm . '_BTA_LOCAL[NORDSUD]', 1, "C", 0, "C = Italia Centrale");
        Out::select($this->nameForm . '_BTA_LOCAL[NORDSUD]', 1, "M", 0, "M = Italia Meridionale");
        Out::select($this->nameForm . '_BTA_LOCAL[NORDSUD]', 1, "I", 0, "I = Italia Insulare");
    }

    private function initComboRegione() {
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 0, 1, " ");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 1, 0, "01 - PIEMONTE");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 2, 0, "02 - VALLE D'AOSTA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 3, 0, "03 - LOMBARDIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 4, 0, "04 - TRENTINO ALTO-ADIGE");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 5, 0, "05 - VENETO");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 6, 0, "06 - FRIULI-VENEZIA GIULIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 7, 0, "07 - LIGURIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 8, 0, "08 - EMILIA-ROMAGNA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 9, 0, "09 - TOSCANA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 10, 0, "10 - UMBRIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 11, 0, "11 - MARCHE");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 12, 0, "12 - LAZIO");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 13, 0, "13 - ABRUZZI");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 14, 0, "14 - MOLISE");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 15, 0, "15 - CAMPANIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 16, 0, "16 - PUGLIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 17, 0, "17 - BASILICATA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 18, 0, "18 - CALABRIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 19, 0, "19 - SICILIA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 20, 0, "20 - SARDEGNA");
        Out::select($this->nameForm . '_BTA_LOCAL[NREGIONE]', 1, 91, 0, "91 - EX TERRITORI ITALIANI");
    }

    public function postSqlElenca($filtri, &$sqlParams) {
        if (count($filtri) > 0) {//significa che ho dei filtri in ingresso quindi non li devo sovrascrivere TODO SR
            $extraFilter = $filtri;
            foreach ($filtri as $key => $value) {
                $filtri[$key] = $filtri[$key];
            }
        } else {
            $filtri['DESLOCAL'] = trim($this->formData[$this->nameForm . '_DESLOCAL']);
            $filtri['CODNAZPRO_da'] = trim($this->formData[$this->nameForm . '_CODNAZPRO_da']);
            $filtri['CODNAZPRO_a'] = trim($this->formData[$this->nameForm . '_CODNAZPRO_a']);
            $filtri['CODLOCAL'] = trim($this->formData[$this->nameForm . '_CODLOCAL']);
            $filtri['PROVINCIA'] = trim($this->formData[$this->nameForm . '_PROVINCIA']);
            $filtri['CODBELFI'] = trim($this->formData[$this->nameForm . '_CODBELFI']);
            $filtri['NREGIONE'] = trim($this->formData[$this->nameForm . '_NREGIONE']);
            $filtri['CODCNC'] = trim($this->formData[$this->nameForm . '_CODCNC']);
            $filtri['CODCATASTO'] = trim($this->formData[$this->nameForm . '_CODCATASTO']);
            $filtri['CODCONSOL'] = trim($this->formData[$this->nameForm . '_CODCONSOL']);
            $filtri['ricSoloAttive'] = trim($this->formData[$this->nameForm . '_ricSoloAttive']);
            $filtri['ricSoloValidatiAnpr'] = trim($this->formData[$this->nameForm . '_ricSoloValidatiAnpr']);
            $filtri['ricComuniItaliani'] = trim($this->formData[$this->nameForm . '_ricComuniItaliani']);
            $filtri['ricLocEstere'] = trim($this->formData[$this->nameForm . '_ricLocEstere']);
            $filtri['ricLuogoEccezionale'] = trim($this->formData[$this->nameForm . '_ricLuogoEccezionale']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaLocal($filtri, false, $sqlParams, $this->validAnpr, $this->nameOrderField, $this->typeOrderField);
    }

    protected function elaboraRecords($Result_tab) {
        $path_ico_verif = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_122501-16x16.png';
        foreach ($Result_tab as $key => $Result_rec) {
//CODISTAT
            if ($Result_rec['F_ITA_EST'] == 0) {
                $Result_tab[$key]['CODISTAT'] = str_pad($Result_tab[$key]['ISTNAZPRO'], 3, "00", STR_PAD_LEFT) .
                        str_pad($Result_tab[$key]['ISTLOCAL'], 3, "00", STR_PAD_LEFT);
            } else {
                $Result_tab[$key]['CODISTAT'] = str_pad($Result_tab[$key]['ISTNAZPRO'], 3, "00", STR_PAD_LEFT);
            }
            $Result_tab[$key]['CODISTAT'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODISTAT']);

//MKE_COD_ISTAT
            $Result_tab[$key]['MKE_COD_ISTAT'] = str_pad($Result_tab[$key]['CODNAZPRO'], 3, "00", STR_PAD_LEFT) .
                    str_pad($Result_tab[$key]['CODLOCAL'], 3, "00", STR_PAD_LEFT);
            $Result_tab[$key]['MKE_COD_ISTAT'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['MKE_COD_ISTAT']);

            if ($Result_rec['ID_COMUANPR'] > 0) {
                $Result_tab[$key]['ANPR'] = cwbLibHtml::formatDataGridIcon('', $path_ico_verif);
                $Result_tab[$key]['ANPR'] = cwbLibHtml::formatDataGridTextColor($Result_tab[$key]['ANPR'], '#001FE2');
            } else {
                $Result_tab[$key]['ANPR'] = ' ';
            }

            if ($Result_rec['F_ITA_EST'] == 1) {
                $tooltip = '';
                $tooltip = 'Stato Estero: ' . $Result_tab[$key]['DESNAZI'];
                $Result_tab[$key]['DESLOCAL'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $tooltip . '">' . $Result_rec['DESLOCAL'] . '</span></div>';
                $Result_tab[$key]['CODISTAT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $tooltip . '">' . $Result_tab[$key]['CODISTAT'] . '</span></div>';
                $Result_tab[$key]['PROVINCIA'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $tooltip . '">' . $Result_rec['PROVINCIA'] . '</span></div>';
            }
        }
        return $Result_tab;
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($CODNAZPRO, $CODLOCAL) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaLocalChiave($CODNAZPRO, $CODLOCAL, $sqlParams);
    }

    public function ComuniItaliani() {
        $url = "http://www.comuni-italiani.it";
        Out::codice("window.open('" . $url . "','_Blank')");
    }

    private function Italiani() {
        $this->italiano = true;
    }

    private function Esteri() {
        $this->italiano = false;
        Out::setFocus("", $this->nameForm . '_DESLOCAL_2');
    }

    private function decodTribu($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTribu", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODTRIBUN", $desValue, $desField, "DESTRIBU", 'returnFromBtaTribu', $_POST['id'], $searchButton);
    }

    private function decodConsol($codValue, $codField, $desValue, $desField, $searchButton = false, $gestione = false) {
        $row = cwbLib::decodificaLookup("cwbBtaConsol", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODCONSOL", $desValue, $desField, "DESCONSOL", 'returnFromBtaConsol', $_POST['id'], $searchButton);
// popola il campo 'situato in'
        if ($gestione) {
            if ($row) {
                Out::valore($this->nameForm . '_DES_LOCAL_CONSOL', $row['DESLOCAL']);
            } else {
                Out::valore($this->nameForm . '_DES_LOCAL_CONSOL', ' ');
            }
        }
    }

    public function TrovaProgre($codnazpro, $codlocal) {
        $this->SQL = $this->libDB->getSqlLeggiBtaLocalProgrStato($codnazpro, $codlocal, $sqlParams);
        $max = ItaDB::DBQuery($this->MAIN_DB, $this->SQL, false, $sqlParams);
        return $max;
    }

    private function decodNazion($codValue, $codField, $desField, $desField2, $desField3, $desField4, $desValue = null, $search = false) {
        $row = cwbLib::decodificaLookup("cwbBtaNazion", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODNAZI", $desValue, $desField, "DESNAZI", 'returnFromBtaNazion', $_POST['id'], $search);

        if ($row) {
            Out::valore($codField, $row['CODNAZI']);
            Out::valore($desField, $row['DESNAZI']);
            Out::valore($desField2, $row['SIGLANAZ']);
            Out::valore($desField3, $row['DESNAZI']);
            Out::valore($desField4, $row['CODNA_IST']);
        } else {
            Out::valore($desField, ' ');
            if ($desField2) {
                Out::valore($desField2, ' ');
            }
            if ($desField3) {
                Out::valore($desField3, ' ');
            }
            Out::valore($this->nameForm . '_CODTRIBUN_2', ' ');
        }

        return $row;
    }

    private function decodNazionSigla($sigla, $siglaField, $desField) {
        $row = $this->libDB->leggiBtaNazion(array("SIGLANAZ" => $sigla), false);
        if ($row) {
            Out::valore($siglaField, $row['SIGLANAZ']);
            Out::valore($desField, $row['DESNAZI']);
        } else {
            Out::valore($siglaField, ' ');
            Out::valore($desField, ' ');
        }
    }

    protected function DecodLocalIstnazpro($istnazpro, $desNazValue = null) {
        Out::valore($this->nameForm . '_CODNA_IST', $istnazpro);
        $row = $this->decodNazion($istnazpro, ($this->nameForm . '_ISTNAZPRO_2'), ($this->nameForm . '_DESNAZI_decod'), ($this->nameForm . '_PROVINCIA_2'), ($this->nameForm . '_DESNAZION_DECOD'), ($this->nameForm . '_CODNAZPRO_2'), $desNazValue);
        if ($_POST[$this->nameForm . '_CODNAZPRO_2'] == 0 && ($istnazpro || $row)) {
            if ($row) {
                $istnazpro = $row['CODNAZI'];
            }

            Out::valore($this->nameForm . '_CODNAZPRO_2', $istnazpro);
            $progre = $this->TrovaProgre($istnazpro, 0);
            if (!$progre['CONTA']) {
                Out::msgStop('Attenzione', "Non risulta inserita nessuna località per il progressivo Stato" . " " . $_POST[$this->nameForm . '_CODNAZPRO_2'] . "." . "E' consigliabile inserire dapprima il progressivo località 0, come proposto dal programma");
            }
            $key = true;
            while ($key) {
                $conta = $this->TrovaProgre($istnazpro, $progre['CONTA']);
                if ($conta['CONTA'] == 0) {
                    $key = false;
                    Out::valore($this->nameForm . '_CODLOCAL_2', str_pad($progre['CONTA'], 3, '0', STR_PAD_LEFT));
                } else {
                    $progre['CONTA'] = $progre['CONTA'] + 1;
                }
            }
        }
        $this->SQL = $this->libDB->getSqlLeggiBtaLocalIstat($istnazpro, 000, $sqlParams);
        $località = ItaDB::DBQuery($this->MAIN_DB, $this->SQL, false, $sqlParams);
        Out::valore($this->nameForm . '_CODBELFI_2', $località['CODBELFI']);
        Out::valore($this->nameForm . '_BTA_LOCAL[TERREST]', $località['TERREST']);
        $this->decodTribu($località['CODTRIBUN'], ($this->nameForm . '_CODTRIBUN_2'), null, ($this->nameForm . '_DESTRIBU_2_decod'));
    }

    protected function postExternalFilter() {
        if (isSet($this->externalParams['CODNAZPRO'])) {
            if (!is_array($this->externalParams['CODNAZPRO'])) {
                $value = $this->externalParams['CODNAZPRO'];
                $permanent = true;
            } else {
                $value = $this->externalParams['CODNAZPRO']['VALORE'];
                if (!isSet($this->externalParams['CODNAZPRO']['PERMANENTE']) || $this->externalParams['CODNAZPRO']['PERMANENTE'] === null || $this->externalParams['CODNAZPRO']['PERMANENTE'] === true) {
                    $permanent = true;
                } else {
                    $permanent = false;
                }
            }

            Out::valore($this->nameForm . '_CODNAZPRO_da', $value);
            Out::valore($this->nameForm . '_CODNAZPRO_a', $value);

            if ($permanent) {
                Out::disableField($this->nameForm . '_CODNAZPRO_da');
                Out::disableField($this->nameForm . '_CODNAZPRO_a');
            }
        }
        Out::valore($this->nameForm . '_ricSoloAttive', $this->externalParams['ricSoloAttivi']['VALORE']);
        Out::valore($this->nameForm . '_ricSoloValidatiAnpr', $this->externalParams['ricSoloValidatiAnpr']['VALORE']);
        Out::valore($this->nameForm . '_ricComuniItaliani', $this->externalParams['ricComuniItaliani']['VALORE']);
        Out::valore($this->nameForm . '_ricLocEstere', $this->externalParams['ricLocEstere']['VALORE']);
        Out::valore($this->nameForm . '_ricLuogoEccezionale', $this->externalParams['ricLuogoEccezionale']['VALORE']);
    }

    protected function initializeTable($sqlParams, &$sortIndex, &$sortOrder) {
        switch ($sortIndex) {
            case 'MKE_COD_ISTAT':
                $sortIndex = array();
                $sortIndex[] = 'CODNAZPRO';
                $sortIndex[] = 'CODLOCAL';
                break;
        }
        if (empty($sortOrder)) {
            $sortOrder = 'asc';
        }
        return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
    }

}
