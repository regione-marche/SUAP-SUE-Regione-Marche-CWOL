<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWS.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBwsAnprCerti() {
    $cwbBwsAnprCerti = new cwbBwsAnprCerti();
    $cwbBwsAnprCerti->parseEvent();
    return;
}

class cwbBwsAnprCerti extends cwbBpaGenTab {

    private $FileDaUpload;
    private $currentDate;
    protected $nomeUtente;
    protected $nomeUtenteOrig;

    protected function initVars() {
        $this->GRID_NAME = 'gridBwsCerti';
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 8;
        $this->searchOpenElenco = true;
        $this->libDB_BWS = new cwbLibDB_BWS();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->elencaAutoAudit = false;
        $this->nomeUtenteOrig = cwbParGen::getSessionVar('nomeUtente');
    }

    protected function preDestruct() {
        parent::preDestruct();
        cwbParGen::setSessionVar('nomeUtente', $this->nomeUtenteOrig);
    }

    public function close() {
        parent::close();
        cwbParGen::setSessionVar('nomeUtente', $this->nomeUtenteOrig);
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_UTENTE_butt':
                    case $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]_butt':
                        $this->decodUtente($this->formData[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE'], $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]', true);
                        break;
                }
                switch ($_POST['id']) {
                    case $this->nameForm . '_VerificaTerminale':
                        Out::msgInfo('NOME TERMINALE', strtoupper(gethostbyaddr($_SERVER['REMOTE_ADDR'])));
                        break;
                }

                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]':
                        if ($_POST[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE']) {
                            $rowCodute = $$this->decodUtente($this->formData[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE'], $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]');
                            if (!cwbLibCheckInput::IsNBZ($rowCodute['CODUTE'])) {
                                Out::valore($this->nameForm . '_UTENTE', $rowCodute['CODUTE']);
                            }
                        }
                        break;
                }
                break;
            case 'returnFromBorUtenti':
                switch ($this->elementId) {
                    case $this->nameForm . '_UTENTE_butt':
                    case $this->nameForm . '_UTENTE':
                        Out::valore($this->nameForm . '_UTENTE', $this->formData['returnData']['CODUTE']);
                    case $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]_butt':
                    case $this->nameForm . '_BWS_ANPR_CERTI[CODUTE]':
                        Out::valore($this->nameForm . '_BWS_ANPR_CERTI[CODUTE]', $this->formData['returnData']['CODUTE']);
                        break;
                }
        }
    }

    protected function postApriForm() {
        $this->combo_fAnprAmbiente($this->nameForm . '_BWS_ANPR_CERTI[F_ANPR_AMBIENTE]');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_BWS_ANPR_CERTI[TERMINALE]');
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("IDANPR_CERTI", "BWS_ANPR_CERTI");
        Out::valore($this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]', $progr);
        Out::attributo($this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]', 'readonly', '0');
        Out::css($this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]', 'background-color', '#FFFFE0');
        $this->currentDate = date('Y-m-d');
        Out::valore($this->nameForm . '_BWS_ANPR_CERTI[DATAINIZ]', $this->currentDate);
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]');
//        cwbParGen::setSessionVar('nomeUtente', $this->nomeUtenteOrig);
    }

    protected function preAggiungi() {
        parent::preAggiungi();
        if (!cwbLibCheckInput::IsNBZ($_POST[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE'])) {
            cwbParGen::setSessionVar('nomeUtente', $_POST[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE']);
        } else {
            cwbParGen::setSessionVar('nomeUtente', '');
        }
    }

    protected function preAggiorna() {
        if (itaModelService::OPERATION_UPDATE) {
            if (!cwbLibCheckInput::IsNBZ($_POST[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE'])) {
                cwbParGen::setSessionVar('nomeUtente', $_POST[$this->nameForm . '_BWS_ANPR_CERTI']['CODUTE']);
            } else {
                  cwbParGen::setSessionVar('nomeUtente', '');
            }
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TERMINALE'] != '') {
            $this->gridFilters['TERMINALE'] = $this->formData['TERMINALE'];
        }
        if ($_POST['UTENTE'] != '') {
            $this->gridFilters['UTENTE'] = $this->formData['UTENTE'];
        }
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBwsAnprCertiChiave($index, $sqlParams);
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]', 'readonly', '0');
        Out::css($this->nameForm . '_BWS_ANPR_CERTI[IDANPR_CERTI]', 'background-color', '#FFFFE0');
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['TERMINALE'] = trim($this->formData[$this->nameForm . '_TERMINALE']);
        $filtri['CODUTE'] = trim($this->formData[$this->nameForm . '_UTENTE']);
        $this->SQL = $this->libDB_BWS->getSqlLeggiBwsAnprCerti($filtri, true, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_rec['F_ANPR_AMBIENTE']) {
                case 0:
                    $Result_tab[$key]['DECOD_AMBIENTE'] = 'Comune non subentrato';
                    break;
                case 1:
                    $Result_tab[$key]['DECOD_AMBIENTE'] = 'Test in corso (ambiente di test)';
                    break;
                case 2:
                    $Result_tab[$key]['DECOD_AMBIENTE'] = 'Test in corso (ambiente di pre-subentro)';
                    break;
                case 3:
                    $Result_tab[$key]['DECOD_AMBIENTE'] = 'Comune subentrato';
                    break;
                default:
                    break;
            }
        }
        return $Result_tab;
    }

    private function combo_fAnprAmbiente($nameForm) {
        Out::valore($nameForm . '_hidden', "0");
        Out::select($nameForm, 1, "0", 0, "Comune non subentrato");
        Out::select($nameForm, 1, "1", 0, "Test in corso (ambiente di test)");
        Out::select($nameForm, 1, "2", 0, "Test in corso (ambiente di pre-subentro)");
        Out::select($nameForm, 1, "3", 0, "Comune subentrato");
    }

    private function decodUtente($codValue, $codField, $searchButton = false) {
        return cwbLib::decodificaLookup("cwbBorUtenti", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODUTE", $desValue, $desField, "", 'returnFromBorUtenti', $_POST['id'], $searchButton);
    }

}

?>