<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaIparub() {
    $cwbBtaIparub = new cwbBtaIparub();
    $cwbBtaIparub->parseEvent();
    return;
}

class cwbBtaIparub extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaIparub';
        $this->libDB = new cwbLibDB_BTA();
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 25;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PK':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PK'], $this->nameForm . '_PK');
                        break;
                }
                break;
        }
    }

    protected function postNuovo() {
        $row = cwbParGen::getFormSessionVar($this->nameForm, 'masterRecord'); //leggo record salvato in sessione
        Out::valore($this->nameForm . '_BTA_IPARUB[IPA_CODAMM]', $row['IPA_CODAMM']);
        $progr = cwbLibCalcoli::trovaProgressivo("PK", "BTA_IPARUB");
        Out::valore($this->nameForm . '_BTA_IPARUB[PK]', $progr);
        Out::setFocus("", $this->nameForm . '_BTA_IPARUB[IPA_CODDES]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_IPARUB[PK]');
    }

    protected function postApriForm() {
        $this->initComboUffici();
        $this->initComboTipoMail();
        Out::setFocus("", $this->nameForm . '_IPA_CODAMM');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_IPA_CODAMM');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function pulisciCampi() {
        Out::valore($this->nameForm . '_BTA_IPARUB[IPA_CODAMM]', '');
        Out::valore($this->nameForm . '_BTA_IPARUB[IPA_CODDES]', '');
        Out::valore($this->nameForm . '_BTA_IPARUB[IPA_NFAX]', '');
        Out::valore($this->nameForm . '_BTA_IPARUB[IPA_TPMAIL]', '');
        Out::valore($this->nameForm . '_BTA_IPARUB[NOTE_V]', '');
        Out::valore($this->nameForm . '_BTA_IPARUB[PK]', '');
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BTA_IPARUB[IPA_CODDES]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->masterRecord != null) {
            cwbParGen::setFormSessionVar($this->nameForm, 'masterRecord', $this->masterRecord); // salvo in sessione il record
            $this->initComboUffici();
            $this->initComboTipoMail();
            $filtri['PK'] = $this->masterRecord['IPA_CODAMM'];
        } else {
            $filtri['PK'] = trim($this->formData[$this->nameForm . '_PK']);
            $filtri['IPA_CODAMM'] = trim($this->formData[$this->nameForm . '_IPA_CODAMM']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaIparub($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaIparubChiave($index, $sqlParams);
    }

    protected function valida($rec, $tipoOperazione, &$msg) {
        $esito = true;
        $msg = '';

        // Controllo campi obbligatori in aggiunta/modifica
        if ($tipoOperazione !== cwbLib::TIPO_OPER_CANCELLA) {
            if (strlen($rec['PK']) === 0) {
                $esito = false;
                $msg .= "Codice obbligatorio\n";
            }
        }

        return $esito;
    }

    private function initComboUffici() {
        // Carica lista uffici
        $uffici = $this->libDB->leggiBtaIpades($filtri);

        // Popola combo in funzione dei dati caricati da db        
        Out::select($this->nameForm . '_BTA_IPARUB[IPA_CODDES]', 1, "0", 1, " ");
        foreach ($uffici as $ufficio) {
            Out::select($this->nameForm . '_BTA_IPARUB[IPA_CODDES]', 1, $ufficio['IPA_CODDES'], 0, trim($ufficio['IPA_UFFDES']));
        }
    }

    private function initComboTipoMail() {
        // Tipo Mail
        Out::select($this->nameForm . '_BTA_IPARUB[IPA_TPMAIL]', 1, "1 ", 1, "PEC");
        Out::select($this->nameForm . '_BTA_IPARUB[IPA_TPMAIL]', 1, "2 ", 0, "CECPAC");
        Out::select($this->nameForm . '_BTA_IPARUB[IPA_TPMAIL]', 1, "3 ", 0, "ALTRO");
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PK_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PK']);
        }
        return $Result_tab;
    }

}

?>