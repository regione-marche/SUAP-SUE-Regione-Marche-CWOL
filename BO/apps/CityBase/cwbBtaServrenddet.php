<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FES.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtRendicontazioneContabileHelper.php';

function cwbBtaServrenddet() {
    $cwbBtaServrenddet = new cwbBtaServrenddet();
    $cwbBtaServrenddet->parseEvent();
    return;
}

class cwbBtaServrenddet extends cwbBpaGenTab {

    private $finanziariaCwol;

    function initVars() {
        $this->GRID_NAME = 'gridBtaServrenddet';
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_FES = new cwfLibDB_FES();
        $this->finanziariaCwol = cwbParGen::getFormSessionVar($this->nameForm, '_finanziariaCwol');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_finanziariaCwol', $this->finanziariaCwol);
        }
    }

    private function apertura() {
        // se c'è la finziaria nostra il lookup va su fes_imp sennò su bta_rend_acc
        $this->finanziariaCwol = cwtRendicontazioneContabileHelper::esisteFinanziariaCwol();
        if ($this->finanziariaCwol) {
            Out::show($this->nameForm . '_lookupFesImp');
            Out::hide($this->nameForm . '_lookupBtaRendAcc');
        } else {
            Out::hide($this->nameForm . '_lookupFesImp');
            Out::show($this->nameForm . '_lookupBtaRendAcc');
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                if ($this->masterRecord) {
                    $this->apertura();
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DES_IMP_decod_butt':
                        $this->decodFesImp($_POST[$this->nameForm . '_DES_IMP_decod'], ($this->nameForm . '_DES_IMP_decod'), true);
                        break;
                    case $this->nameForm . '_DES_IMP_butt':
                        $this->decodFesImp($_POST[$this->nameForm . '_DES_IMP_decod'], ($this->nameForm . '_DES_IMP'), true);
                        break;
                    case $this->nameForm . '_DES_IMP_RENDACC_decod_butt':
                        $this->decodBtaRendAcc($_POST[$this->nameForm . '_DES_IMP_RENDACC_decod'], ($this->nameForm . '_DES_IMP_RENDACC_decod'), true);
                        break;
                    case $this->nameForm . '_DES_IMP_RENDACC_butt':
                        $this->decodBtaRendAcc($_POST[$this->nameForm . '_DES_IMP_RENDACC_decod'], ($this->nameForm . '_DES_IMP_RENDACC'), true);
                        break;
                    case $this->nameForm . '_BTA_SERVRENDDET[CODTIPSCAD]_butt':
                        cwbLib::apriFinestraRicerca('cwbBweTippen', $this->nameForm, 'returnFromBweTippen', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_BTA_SERVRENDDET[IDBOL_SERE]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaEmi', $this->nameForm, 'returnFromBtaEmi', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_BTA_SERVRENDDET[CODSERVIZIOEMI]_butt':
                        cwbLib::apriFinestraRicerca('cwbBorIdbol', $this->nameForm, 'returnFromBorIdbol', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                }
                break;
            case 'returnFromBweTippen':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SERVRENDDET[CODTIPSCAD]_butt':
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[CODTIPSCAD]', $this->formData['returnData']['CODTIPSCAD']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[SUBTIPSCAD]', $this->formData['returnData']['SUBTIPSCAD']);
                        break;
                }
                break;
            case 'returnFromBtaEmi':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SERVRENDDET[IDBOL_SERE]_butt':
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[ANNOEMI]', $this->formData['returnData']['ANNOEMI']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[NUMEMI]', $this->formData['returnData']['NUMEMI']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[IDBOL_SERE]', $this->formData['returnData']['IDBOL_SERE']);
                        break;
                }
                break;
            case 'returnFromBorIdbol':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SERVRENDDET[CODSERVIZIOEMI]_butt':
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[CODSERVIZIOEMI]', $this->formData['returnData']['IDBOL_SERE']);
                        break;
                }
                break;
            case 'returnFromFesImp':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_IMP_decod_butt':
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[ACCERTAMENTO]', $this->formData['returnData']['ANNORIF'] . '/' . $this->formData['returnData']['NUMEROIMP']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET_ACCERTAMENTO_FESIMP', $this->formData['returnData']['ANNORIF'] . '/' . $this->formData['returnData']['NUMEROIMP']);
                        Out::valore($this->nameForm . '_DES_IMP_decod', $this->formData['returnData']['DES_IMP']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[PROGIMPACC]', $this->formData['returnData']['PROGIMPACC']);
                        break;
                }
                break;
            case 'returnFromBtaRendAcc':
                switch ($this->elementId) {
                    case $this->nameForm . '_DES_IMP_RENDACC_decod_butt':
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET_ACCERTAMENTO_RENDACC', $this->formData['returnData']['ANNORIF'] . '/' . $this->formData['returnData']['NUMEROIMP']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[ACCERTAMENTO]', $this->formData['returnData']['ANNORIF'] . '/' . $this->formData['returnData']['NUMEROIMP']);
                        Out::valore($this->nameForm . '_DES_IMP_RENDACC_decod', $this->formData['returnData']['DES_IMP']);
                        Out::valore($this->nameForm . '_BTA_SERVRENDDET[PROGIMPACC]', $this->formData['returnData']['PROGIMPACC']);
                        break;
                }
                break;
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index, &$sqlDettaglio = null) {
        $progimpacc = $this->CURRENT_RECORD['PROGIMPACC'];
        if ($this->finanziariaCwol) {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_FESIMP';
            $descrizioneField = '_DES_IMP_decod';
        } else {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_RENDACC';
            $descrizioneField = '_DES_IMP_RENDACC_decod';
        }

        if (intval($progimpacc) === 0) {
            Out::valore($this->nameForm . $codiceField, "*************");
            Out::valore($this->nameForm . $descrizioneField, "Accertamento NON Valorizzato");
        } else {
            $decod_fesImp = $this->libDB_FES->leggiFesImpChiave($progimpacc);
            Out::valore($this->nameForm . $codiceField, $decod_fesImp['ANNORIF'] . '/' . $decod_fesImp['NUMEROIMP']);
            Out::valore($this->nameForm . $descrizioneField, $decod_fesImp['DES_IMP']);
        }
    }

    protected function postNuovo() {
        if ($this->finanziariaCwol) {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_FESIMP';
            $descrizioneField = '_DES_IMP_decod';
        } else {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_RENDACC';
            $descrizioneField = '_DES_IMP_RENDACC_decod';
        }

        Out::valore($this->nameForm . $codiceField, "*************");
        Out::valore($this->nameForm . $descrizioneField, "Accertamento NON Valorizzato");
        $progkeytab = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BTA_SERVRENDDET');
        Out::valore($this->nameForm . '_BTA_SERVRENDDET[PROGKEYTAB]', $progkeytab);
        Out::valore($this->nameForm . '_BTA_SERVRENDDET[IDSERVREND]', $this->externalParams['IDSERVREND']);

        $where = ' IDSERVREND=' . $this->externalParams['IDSERVREND'];
        $progint = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BTA_SERVRENDDET', $where);
        Out::valore($this->nameForm . '_BTA_SERVRENDDET[PROGINT]', $progint);
        Out::show($this->nameForm . '_Torna');
    }

    protected function postAggiorna() {
        $this->reload();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $this->disFields();
        $filtri = array();
        $this->initComboTipoVoce();
        $filtri['IDSERVREND'] = $this->externalParams['IDSERVREND'];
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaServrenddet($filtri, true, $sqlParams);
    }

    protected function disFields() {
        if ($this->finanziariaCwol) {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_FESIMP';
            $descrizioneField = '_DES_IMP_decod';
        } else {
            $codiceField = '_BTA_SERVRENDDET_ACCERTAMENTO_RENDACC';
            $descrizioneField = '_DES_IMP_RENDACC_decod';
        }
        Out::attributo($this->nameForm . '_BTA_SERVRENDDET[CODSERVIZIOEMI]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_SERVRENDDET[CODSERVIZIOEMI]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_SERVRENDDET[CODTIPSCAD]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_SERVRENDDET[CODTIPSCAD]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_SERVRENDDET[IDBOL_SERE]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_SERVRENDDET[IDBOL_SERE]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . $codiceField, 'readonly', '0');
        Out::css($this->nameForm . $codiceField, 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . $descrizioneField, 'readonly', '0');
        Out::css($this->nameForm . $descrizioneField, 'background-color', '#FFFFE0');
    }

    protected function postConfermaCancella() {
        $this->reload();
    }

    protected function postTornaElenco() {
        $this->elenca(true);
    }

    protected function reload() {
        $this->tornaAElenco();
        $this->elenca(true);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaServrenddetChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            $Result_tab[$key]['IDSERVREND_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDSERVREND']);
            if (intval($Result_tab[$key]['PROGIMPACC']) === 0) {
                $des_imp = 'Accertamento NON Valorizzato';
            } else {
                $decod_fesImp = $this->libDB_FES->leggiFesImpChiave($Result_tab[$key]['PROGIMPACC']);
                $des_imp = $decod_fesImp['DES_IMP'];
            }
            $Result_tab[$key]['DES_IMP'] = $des_imp;

            switch ($Result_rec['TIPOVOCE']) {
                case 1:
                    $Result_tab[$key]['TIPOVOCE'] = 'Imponibile';
                    break;
                case 2:
                    $Result_tab[$key]['TIPOVOCE'] = 'Addizionali';
                    break;
                case 3:
                    $Result_tab[$key]['TIPOVOCE'] = 'Maggiorazione TARES';
                    break;
                case 4:
                    $Result_tab[$key]['TIPOVOCE'] = 'Spese di bollo';
                    break;
                case 5:
                    $Result_tab[$key]['TIPOVOCE'] = 'IVA';
                    break;
                //    case 6:
                //       $Result_tab[$key]['TIPOVOCE'] = 'Agevolazione (su base imponibile)';
                //       break;
                case 7:
                    $Result_tab[$key]['TIPOVOCE'] = 'Sanzione';
                    break;
                case 8:
                    $Result_tab[$key]['TIPOVOCE'] = 'Sanzione AMM.';
                    break;
                case 9:
                    $Result_tab[$key]['TIPOVOCE'] = 'Interessi';
                    break;
                case 10:
                    $Result_tab[$key]['TIPOVOCE'] = 'Interessi di mora (aggiuntivi)';
                    break;
                case 11:
                    $Result_tab[$key]['TIPOVOCE'] = 'Arrotondamento';
                    break;
                case 12:
                    $Result_tab[$key]['TIPOVOCE'] = 'Arrotondamento per pagamento parziale';
                    break;
                case 101:
                    $Result_tab[$key]['TIPOVOCE'] = 'Spese notifica';
                    break;
                case 102:
                    $Result_tab[$key]['TIPOVOCE'] = 'Recupero spese notifica';
                    break;
                case 103:
                    $Result_tab[$key]['TIPOVOCE'] = 'Spese di procedura';
                    break;
                case 104:
                    $Result_tab[$key]['TIPOVOCE'] = 'Compensi (altre spese)';
                    break;
            }
        }
        return $Result_tab;
    }

    protected function initComboTipoVoce() {
        Out::html($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', ' ');
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 1, 1, "Imponibile");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 2, 0, "Addizionali");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 3, 0, "Maggiorazione TARES");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 4, 0, "Spese di bollo");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 5, 0, "IVA");
        //Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 6, 0, "Agevolazione (su base imponibile)");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 7, 0, "Sanzione");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 8, 0, "Sanzione AMM.");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 9, 0, "Interessi");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 10, 0, "Interessi di mora (aggiuntivi)");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 11, 0, "Arrotondamento");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 12, 0, "Arrotondamento per pagamento parziale");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 101, 0, "Spese notifica");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 102, 0, "Recupero spese notifica");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 103, 0, "Spese di procedura");
        Out::select($this->nameForm . '_BTA_SERVRENDDET[TIPOVOCE]', 1, 104, 0, "Compensi (altre spese)");
    }

    private function decodFesImp($valore, $campoForm, $searchButton = false) {
        $row = cwbLib::decodificaLookup("cwfFesImp", $this->nameForm, $this->nameFormOrig, null, null, null, $valore, $campoForm, "DES_IMP", 'returnFromFesImp', $_POST['id'], $searchButton, true);
    }

    private function decodBtaRendAcc($valore, $campoForm, $searchButton = false) {
        $row = cwbLib::decodificaLookup("cwbBtaRendAcc", $this->nameForm, $this->nameFormOrig, null, null, null, $valore, $campoForm, "DES_IMP", 'returnFromBtaRendAcc', $_POST['id'], $searchButton, true);
    }

}

?>