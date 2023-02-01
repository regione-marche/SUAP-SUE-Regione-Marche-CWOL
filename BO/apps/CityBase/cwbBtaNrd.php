<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';

function cwbBtaNrd() {
    $cwbBtaNrd = new cwbBtaNrd();
    $cwbBtaNrd->parseEvent();
    return;
}

class cwbBtaNrd extends cwbBpaGenTab {

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaNrd';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->GRID_NAME = 'gridBtaNrd';
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_FTA = new cwfLibDB_FTA();
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 26;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;

        $this->fakeMultiselect = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AREA_COMP':
                        if ($_POST[$this->nameForm . '_AREA_COMP'] == ' ') {
                            Out::hide($this->nameForm . '_GENERICI_field');
                        } else {
                            Out::show($this->nameForm . '_GENERICI_field');
                            Out::attributo($this->nameForm . "_GENERICI", "checked", "0", "checked");
                        }
                        break;
                    case $this->nameForm . '_BTA_NRD[SETT_IVA]':
                        if (cwbLibCheckInput::checkNumeric($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['SETT_IVA'], $this->nameForm . '_BTA_NRD[SETT_IVA]', $this->nameForm . '_DES_SETIVA')) {
                            $this->decodSetiva($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['SETT_IVA'], $this->nameForm . '_BTA_NRD[SETT_IVA]', $this->formData[$this->nameForm . '_DES_SETIVA'], ($this->nameForm . '_DES_SETIVA'), FALSE);
                        } else {
                            Out::valore($this->nameForm . '_DES_SETIVA', '');
                        }
                        break;
                    case $this->nameForm . '_DES_SETIVA':
                        $this->decodSetiva(null, ($this->nameForm . '_BTA_NRD[SETT_IVA]'), $this->formData[$this->nameForm . '_DES_SETIVA'], ($this->nameForm . '_DES_SETIVA'));
                        break;
                    case $this->nameForm . '_BTA_NRD[COD_REGIVA]':
                        if (cwbLibCheckInput::checkNumeric($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['COD_REGIVA'], $this->nameForm . '_BTA_NRD[COD_REGIVA]', $this->nameForm . '_DES_REGIVA')) {
                            $this->decodReivag($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['COD_REGIVA'], $this->nameForm . '_BTA_NRD[COD_REGIVA]', $this->formData[$this->nameForm . '_DES_REGIVA'], ($this->nameForm . '_DES_REGIVA'), FALSE);
                        } else {
                            Out::valore($this->nameForm . '_DES_REGIVA', '');
                        }
                        break;
                    case $this->nameForm . '_DES_REGIVA':
                        $this->decodReivag(null, ($this->nameForm . '_BTA_NRD[COD_REGIVA]'), $this->formData[$this->nameForm . '_DES_REGIVA'], ($this->nameForm . '_DES_REGIVA'));
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DES_SETIVA_butt':
                        $this->decodSetiva($this->formData[$this->nameForm . '_BTA_NRD[SETT_IVA]'], ($this->nameForm . '_BTA_NRD[SETT_IVA]'), $this->formData[$this->nameForm . '_DES_SETIVA'], ($this->nameForm . '_DES_SETIVA'), true);
                        break;
                    case $this->nameForm . '_DES_REGIVA_butt':
                        $this->decodReivag($this->formData[$this->nameForm . '_BTA_NRD[COD_REGIVA]'], ($this->nameForm . '_BTA_NRD[COD_REGIVA]'), $this->formData[$this->nameForm . '_DES_REGIVA'], ($this->nameForm . '_DES_REGIVA'), true);
                        break;
                    /////////////////////////// Numeratore Per Anno - Lista ///////////////////////////
                    case $this->nameForm . '_NrdAn':
                        $num = $this->getSelected();
                        if (empty($num)) {
                            Out::msgInfo('', "Nessun Numeratore selezionato");
                        } else {
                            $filtrinr = array('COD_NR_D' => $num[0]);
                            $datanrdan = $this->libDB->leggiGeneric('BTA_NRD_AN', $filtrinr, false);
                            if ($datanrdan !== false) {
                                $model = cwbLib::apriFinestra('cwbBtaNrdAn', $this->nameFormOrig, '', '', $filtrinr, $this->nameForm, null);
                                $model->parseEvent();
                            } else {
                                Out::msgInfo('', "Il numeratore non è presente nella tabella 'Numeratori Documenti per Anno'.");
                            }
                        }
                        break;
                    /////////////////////////// Numeratore Per Anno - Dettaglio ///////////////////////////
                    case $this->nameForm . '_NrdAn_Edit':
                        $num = trim($_POST[$this->nameForm . '_BTA_NRD']['COD_NR_D']);
                        if (empty($num)) {
                            Out::msgInfo('Numeratore mancante', 'Nessun Numeratore selezionato');
                        } else {
                            $filtrinr = array('COD_NR_D' => $num);
                            $datanrdan = $this->libDB->leggiGeneric('BTA_NRD_AN', $filtrinr, false);
                            if ($datanrdan !== false) {
                                $model = cwbLib::apriFinestra('cwbBtaNrdAn', $this->nameFormOrig, '', '', $filtrinr, $this->nameForm, null);
                                $model->parseEvent();
                            } else {
                                Out::msgInfo('', "Il numeratore non è presente nella tabella 'Numeratori Documenti per Anno'.");
                            }
                        }
                        break;
                }
                break;
            case 'returnFromFtaSetiva':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_SETIVA_butt':
                    case $this->nameForm . '_DES_SETIVA':
                        Out::valore($this->nameForm . '_BTA_NRD[SETT_IVA]', $this->formData['returnData']['SETT_IVA']);
                        Out::valore($this->nameForm . '_DES_SETIVA', $this->formData['returnData']['DES_SETIVA']);
                        break;
                }
                break;
            case 'returnFromFtaReivag':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_REGIVA':
                    case $this->nameForm . '_DES_REGIVA_butt':
                        Out::valore($this->nameForm . '_BTA_NRD[COD_REGIVA]', $this->formData['returnData']['COD_REGIVA']);
                        Out::valore($this->nameForm . '_DES_REGIVA', $this->formData['returnData']['DES_REGIVA']);
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        parent::postPulisciCampi();

        Out::valore($this->nameForm . '_DES_SETIVA', '');
        Out::valore($this->nameForm . '_DES_REGIVA', '');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_NRD[COD_REGIVA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_NRD[COD_REGIVA]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_NRD[COD_REGIVA]');
    }

    protected function postApriForm() {
        $this->initComboAreaComp();
        $this->initComboTipologia();
        Out::attributo($this->nameForm . "_GENERICI", "checked", "0", "checked");
        Out::hide($this->nameForm . '_GENERICI_field');
        Out::setFocus("", $this->nameForm . '_DES_NR_D');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_NR_D');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_NRD[COD_REGIVA]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if (trim($_POST['COD_NR_D']) != '') {
            $this->gridFilters['COD_NR_D_like'] = $this->formData['COD_NR_D'];
        }
        if (trim($_POST['DES_NR_D']) != '') {
            $this->gridFilters['DES_NR_D'] = $this->formData['DES_NR_D'];
        }
        if (trim($_POST['COD_REGIVA']) != '') {
            $this->gridFilters['COD_REGIVA'] = $this->formData['COD_REGIVA'];
        }
        if (!empty($_POST['AREA_COMP'])) {
            $this->gridFilters['AREA_COMP'] = trim($_POST['AREA_COMP']);
//            if(is_numeric($_POST['AREA_COMP'])){
//                $this->gridFilters['AREA_COMP_grid'] = array(trim($_POST['AREA_COMP']));
//            }
//            else{
//                $this->gridFilters['AREA_COMP_grid'] = $this->likeAreaComp($_POST['AREA_COMP']);
//            }
        }
        if (!empty($_POST['F_TP_NR_D'])) {
            $this->gridFilters['F_TP_NR_D'] = $_POST['F_TP_NR_D'] - 1;
//            if(is_numeric($_POST['F_TP_NR_D'])){
//                $this->gridFilters['F_TP_NR_D_grid'] = array(trim($_POST['F_TP_NR_D']));
//            }
//            else{
//                $this->gridFilters['F_TP_NR_D_grid'] = $this->likeFTpNrD($_POST['F_TP_NR_D']);
//            }
        }
    }

    private function likeAreaComp($area_comp) {
        $srcArray = array(
            0 => 'Generica',
            1 => 'Serv.Economici',
            2 => 'Tributi',
            3 => 'ICI',
            4 => 'Serv.Dom.Individ.',
            5 => 'Serv.Demografici',
            6 => 'Delibere/Determine',
            8 => 'Acquedotto',
            9 => 'Servizi Cimiteriali',
            10 => 'Recupero Crediti'
        );

        return array_keys(cwbLib::searchInArrayAsLike($srcArray, '%' . $area_comp . '%'));
    }

    private function likeFTpNrD($f_tp_nr_d) {
        $srcArray = array(
            0 => '0 - Numerazione libera',
            1 => '1 - Numerazione per IVA/Fiscale',
            2 => '2 - Numerazione non fiscale'
        );

        return array_keys(cwbLib::searchInArrayAsLike($srcArray, '%' . $f_tp_nr_d . '%'));
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        $this->decodReivag($this->CURRENT_RECORD['COD_REGIVA'], ($this->nameForm . '_BTA_NRD[COD_REGIVA]'), ($this->nameForm . '_DES_REGIVA'));
        $this->decodSetiva($this->CURRENT_RECORD['SETT_IVA'], ($this->nameForm . '_BTA_NRD[SETT_IVA]'), ($this->nameForm . '_DES_SETIVA'));
        Out::attributo($this->nameForm . '_BTA_NRD[COD_REGIVA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_NRD[COD_REGIVA]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_NRD[DES_NR_D]', trim($this->CURRENT_RECORD['DES_NR_D']));
        Out::setFocus('', $this->nameForm . '_BTA_NRD[DES_NR_D]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['COD_REGIVA'] = trim($this->formData[$this->nameForm . '_COD_REGIVA']);
        $filtri['DES_NR_D'] = trim($this->formData[$this->nameForm . '_DES_NR_D']);
        $filtri['COD_REGIVA'] = trim($this->formData[$this->nameForm . '_COD_REGIVA']);
        $filtri['AREA_COMP'] = trim($this->formData[$this->nameForm . '_AREA_COMP']);
        $filtri['GENERICI'] = trim($this->formData[$this->nameForm . '_GENERICI']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaNrd($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaNrdChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_tab[$key]['AREA_COMP']) {
                case 0:
                    $Result_tab[$key]['AREA_COMP'] = 'Generica';
                    break;
                case 1:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Economici';
                    break;
                case 2:
                    $Result_tab[$key]['AREA_COMP'] = 'Tributi';
                    break;
                case 3:
                    $Result_tab[$key]['AREA_COMP'] = 'ICI';
                    break;
                case 4:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Dom.Individ.';
                    break;
                case 5:
                    $Result_tab[$key]['AREA_COMP'] = 'Serv.Demografici';
                    break;
                case 6:
                    $Result_tab[$key]['AREA_COMP'] = 'Delibere/Determine';
                    break;
                case 8:
                    $Result_tab[$key]['AREA_COMP'] = 'Acquedotto';
                    break;
                case 9:
                    $Result_tab[$key]['AREA_COMP'] = 'Servizi Cimiteriali';
                    break;
                case 10:
                    $Result_tab[$key]['AREA_COMP'] = 'Recupero Crediti';
                    break;
            }
            switch ($Result_tab[$key]['F_TP_NR_D']) {
                case 0:
                    $Result_tab[$key]['F_TP_NR_D'] = '0 - Numerazione libera';
                    break;
                case 1:
                    $Result_tab[$key]['F_TP_NR_D'] = '1 - Numerazione per IVA/Fiscale';
                    break;
                case 2:
                    $Result_tab[$key]['F_TP_NR_D'] = '2 - Numerazione non fiscale';
                    break;
            }
            $Result_tab[$key]['COD_REGIVA_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['COD_REGIVA']);
        }
        return $Result_tab;
    }

    private function initComboAreaComp() {
        $this->initComboAreaCompRic();
        $this->initComboAreaCompGes();
    }

    private function initComboAreaCompRic() {
        // Combo Area competenza in divRicerca
        Out::select($this->nameForm . '_AREA_COMP', 1, " ", 1, "---TUTTI---");
        Out::select($this->nameForm . '_AREA_COMP', 1, "1", 0, "Serv.Economici");
        Out::select($this->nameForm . '_AREA_COMP', 1, "2", 0, "Tributi");
        Out::select($this->nameForm . '_AREA_COMP', 1, "3", 0, "ICI");
        Out::select($this->nameForm . '_AREA_COMP', 1, "4", 0, "Serv.Dom.Individ.");
        Out::select($this->nameForm . '_AREA_COMP', 1, "5", 0, "Serv.Demografici");
        Out::select($this->nameForm . '_AREA_COMP', 1, "6", 0, "Delibere/Determine");
        Out::select($this->nameForm . '_AREA_COMP', 1, "8", 0, "Acquedotto");
        Out::select($this->nameForm . '_AREA_COMP', 1, "9", 0, "Servizi Cimiteriali");
        Out::select($this->nameForm . '_AREA_COMP', 1, "10", 0, "Recupero Crediti");
    }

    private function initComboAreaCompGes() {
        // Combo Area competenza in divGestione
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "0", 0, "Generica");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "1", 0, "Serv.Economici");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "2", 0, "Tributi");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "3", 0, "ICI");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "4", 0, "Serv.Dom.Individ.");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "5", 0, "Serv.Demografici");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "6", 0, "Delibere/Determine");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "8", 0, "Acquedotto");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "9", 0, "Servizi Cimiteriali");
        Out::select($this->nameForm . '_BTA_NRD[AREA_COMP]', 1, "10", 0, "Recupero Crediti");
    }

    private function initComboTipologia() {
        // Combo Tipologia
        Out::select($this->nameForm . '_BTA_NRD[F_TP_NR_D]', 1, "0", 1, "0 - Numerazione libera");
        Out::select($this->nameForm . '_BTA_NRD[F_TP_NR_D]', 1, "1", 0, "1 - Numerazione per IVA/Fiscale");
        Out::select($this->nameForm . '_BTA_NRD[F_TP_NR_D]', 1, "2", 0, "2 - Numerazione non fiscale");
    }

    private function decodSetiva($codValue, $codField, $desValue, $desField = null, $searchButton = false) {
        cwbLib::decodificaLookup("cwfFtaSetiva", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "SETT_IVA", $desValue, $desField, "DES_SETIVA", 'returnFromFtaSetiva', $_POST['id'], $searchButton);
    }

    private function decodReivag($codValue, $codField, $desValue, $desField = null, $searchButton = false) {
        cwbLib::decodificaLookup("cwfFtaReivag", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "COD_REGIVA", $desValue, $desField, "DES_REGIVA", 'returnFromFtaReivag', $_POST['id'], $searchButton);
    }

    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $altraRicerca, $cancella, $torna, $NrdAn = false, $NrdAn_Edit = false) {
        $NrdAn ? Out::show($this->nameForm . '_NrdAn') : Out::hide($this->nameForm . '_NrdAn');
        $NrdAn_Edit ? Out::show($this->nameForm . '_NrdAn_Edit') : Out::hide($this->nameForm . '_NrdAn_Edit');
        parent::setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $altraRicerca, $cancella, $torna);
    }

    protected function setVisRisultato() {
        $this->setVisControlli(false, true, false, true, false, false, false, false, true, false, true, false);
    }

    protected function setVisDettaglio() {
        if (isSet($this->apriDettaglioIndex)) {
            $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, false, true, false, false);
        } else {
            $this->setVisControlli(true, false, false, false, $this->abilitaDuplica(), true, false, true, true, true, false, true);
        }
    }

}

?>
