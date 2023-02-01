<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBgdAdassm() {
    $cwbBgdAdassm = new cwbBgdAdassm();
    $cwbBgdAdassm->parseEvent();
    return;
}

class cwbBgdAdassm extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBgdAdassm';
        $this->libDB = new cwbLibDB_BGD();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_IDADASSM':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_IDADASSM'], $this->nameForm . '_IDADASSM');
                        break;
                    case $this->nameForm . '_CODAREAMA':
                        $this->initComboRicercaModuli($_POST[$this->nameForm . '_CODAREAMA']);
                        break;
                    case $this->nameForm . '_BGD_ADASSM[IDTIPDOC]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDTIPDOC'], $this->nameForm . '_BGD_ADASSM[IDTIPDOC]')) {
                            $this->decodDocu($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDTIPDOC'], ($this->nameForm . '_BGD_ADASSM[IDTIPDOC]'), ($this->nameForm . '_TIPDOC_decod'));
                        } else {
                            Out::valore($this->nameForm . '_TIPDOC_decod');
                        }
                    case $this->nameForm . '_BGD_ADASSM[IDADMCNF]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDADMCNF'], $this->nameForm . '_BGD_ADASSM[IDADMCNF]')) {
                            $this->decodConf($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDADMCNF'], ($this->nameForm . '_BGD_ADASSM[IDADMCNF]'), ($this->nameForm . '_IDADMCNF_decod'));
                        } else {
                            Out::valore($this->nameForm . '_IDADMCNF_decod');
                        }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGD_ADASSM[IDTIPDOC]_butt':
                        cwbLib::apriFinestraRicerca('cwbBgdTipdoc', $this->nameForm, 'returnFromBgdTipdoc', $_POST['id'], true, array(
                            'escludeAspetti' => true,
                            'escludeNonEsportabili' => true
                        ));
                        break;
                    case $this->nameForm . '_BGD_ADASSM[IDADMCNF]_butt':
                        cwbLib::apriFinestraRicerca('cwbBgdAdmcnf', $this->nameForm, 'returnFromBgdAdmcnf', $_POST['id'], true);
                        break;
                }
                break;
            case 'returnFromBgdTipdoc':
                switch ($this->elementId) {
                    case $this->nameForm . '_BGD_ADASSM[IDTIPDOC]_butt':
                        Out::valore($this->nameForm . '_BGD_ADASSM[IDTIPDOC]', $this->formData['returnData']['IDTIPDOC']);
                        Out::valore($this->nameForm . '_TIPDOC_decod', $this->formData['returnData']['DESCRIZIONE']);
                        break;
                }
            case 'returnFromBgdAdmcnf':
                switch ($this->elementId) {
                    case $this->nameForm . '_BGD_ADASSM[IDADMCNF]_butt':
                        Out::valore($this->nameForm . '_BGD_ADASSM[IDADMCNF]', $this->formData['returnData']['IDADMCNF']);

                        //cerco descrizione F_GESTDOC da concatenare poi al modulo.
                        switch ($this->formData['returnData']['F_GESTDOC']) {
                            case 0:
                                $decodifica = '0 - Nessuna gestione documentale';
                                break;
                            case 3:
                                $decodifica = '3 - DOCER';
                                break;
                            case 4:
                                $decodifica = '4 - ALFRESCO';
                                break;
                        }
                        Out::valore($this->nameForm . '_IDADMCNF_decod', $this->formData['returnData']['CODMODULO'] . ' (' . $decodifica . ')');
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

        Out::valore($this->nameForm . '_TIPDOC_decod', '');
        Out::valore($this->nameForm . '_IDADMCNF_decod', '');
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_BGD_ADASSM[IDADASSM]_field');
        Out::setFocus("", $this->nameForm . '_BGD_ADASSM[TIPO_DOC]');
    }

    protected function postApriForm() {
        $this->initComboRicerca();
        $this->initComboTuttiModuli();
        $this->initComboTipGes();
        Out::setFocus("", $this->nameForm . '_CODAREAMA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_CODAREAMA');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_ADASSM[IDADASSM]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TIPO_DOC'] != '') {
            $this->gridFilters['TIPO_DOC'] = $this->formData['TIPO_DOC'];
        }
        if ($_POST['PHP_CLASS'] != '') {
            $this->gridFilters['PHP_CLASS'] = $this->formData['PHP_CLASS'];
        }
        if ($_POST['DBNAME'] != '') {
            $this->gridFilters['DBNAME'] = $this->formData['DBNAME'];
        }
        if ($_POST['DBSUFFIX'] != '') {
            $this->gridFilters['DBSUFFIX'] = $this->formData['DBSUFFIX'];
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_BGD_ADASSM[IDADASSM]_field');
        $this->decodDocu($this->CURRENT_RECORD['IDTIPDOC'], ($this->nameForm . '_BGD_ADASSM[IDTIPDOC]'), ($this->nameForm . '_TIPDOC_decod'));
        $this->decodConf($this->CURRENT_RECORD['IDADMCNF'], ($this->nameForm . '_BGD_ADASSM[IDADMCNF]'), ($this->nameForm . '_IDADMCNF_decod'));
        Out::attributo($this->nameForm . '_BGD_ADASSM[IDADASSM]', 'readonly', '0');
        Out::css($this->nameForm . '_BGD_ADASSM[IDADASSM]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BGD_ADASSM[CODAREAMA]');
    }

    public function postSqlElenca($filtri,&$sqlParams = array()){  
        $filtri['CODAREAMA'] = trim($this->formData[$this->nameForm . '_CODAREAMA']);
        $filtri['CODMODULO'] = trim($this->formData[$this->nameForm . '_CODMODULO']);
        $filtri['TIPO_DOC'] = trim($this->formData[$this->nameForm . '_TIPO_DOC']);
        $filtri['F_TIPOGEST'] = trim($this->formData[$this->nameForm . '_F_TIPOGEST']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdAdassm($filtri, true,$sqlParams);
    }

    public function sqlDettaglio($index) {
        $this->SQL = $this->libDB->getSqlLeggiBgdAdassmChiave($index);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_tab[$key]['F_TIPOGEST']) {
                case 0:
                    $Result_tab[$key]['F_TIPOGEST'] = '';
                    break;
                case 1:
                    $Result_tab[$key]['F_TIPOGEST'] = '1 - Automatica (da gestionale)';
                    break;
                case 2:
                    $Result_tab[$key]['F_TIPOGEST'] = '2 - Manuale (dati presenti su gestionale)';
                    break;
                case 3:
                    $Result_tab[$key]['F_TIPOGEST'] = '3 - Esterna (dati caricati manualmente)';
                    break;
            }
            //cerco descrizione tipologia documento.
            $tipodoc = $this->libDB->leggiBgdTipdocChiave($Result_tab[$key]['IDTIPDOC']);
            $Result_tab[$key]['IDTIPDOC'] = $tipodoc['DESCRIZIONE'];
            $Result_tab[$key]['IDADASSM_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDADASSM']);
        }
        return $Result_tab;
    }

    private function initComboRicerca() {
        $this->initComboRicercaAree();
        $this->initComboRicercaModuli('');
    }

    private function initComboTipGes() {
        Out::select($this->nameForm . '_BGD_ADASSM[F_TIPOGEST]', 1, "0", 1, "");
        Out::select($this->nameForm . '_BGD_ADASSM[F_TIPOGEST]', 1, "1", 0, "1 - Automatica (da gestionale)");
        Out::select($this->nameForm . '_BGD_ADASSM[F_TIPOGEST]', 1, "2", 0, "2 - Manuale (dati presenti su gestionale)");
        Out::select($this->nameForm . '_BGD_ADASSM[F_TIPOGEST]', 1, "3", 0, "3 - Esterna (dati caricati manualmente)");

        Out::select($this->nameForm . '_F_TIPOGEST', 1, "0", 1, "");
        Out::select($this->nameForm . '_F_TIPOGEST', 1, "1", 0, "1 - Automatica (da gestionale)");
        Out::select($this->nameForm . '_F_TIPOGEST', 1, "2", 0, "2 - Manuale (dati presenti su gestionale)");
        Out::select($this->nameForm . '_F_TIPOGEST', 1, "3", 0, "3 - Esterna (dati caricati manualmente)");
    }

    private function initComboTuttiModuli() {
        // Estrae e popola la combo con tutti i moduli
        $moduli = $this->libDB_BOR->leggiBorModuli(array());

        // Popola combo in funzione dei dati caricati da db        
        foreach ($moduli as $modulo) {
            Out::select($this->nameForm . '_BGD_ADASSM[CODMODULO]', 1, $modulo['CODMODULO'], 0, trim($modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO']));
        }
    }

    private function initComboRicercaAree() {

        // Azzera combo
        Out::html($this->nameForm . '_CODAREAMA', '');
        Out::html($this->nameForm . '_BGD_ADASSM[CODAREAMA]', '');

        // Carica lista aree
        $aree = $this->libDB_BOR->leggiBorMaster(array());

        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_CODAREAMA', 1, '', 1, "--- TUTTE ---");
        foreach ($aree as $area) {
            Out::select($this->nameForm . '_CODAREAMA', 1, $area['CODAREAMA'], 0, trim($area['CODAREAMA'] . ' - ' . $area['DESAREA']));
            Out::select($this->nameForm . '_BGD_ADASSM[CODAREAMA]', 1, $area['CODAREAMA'], 0, trim($area['CODAREAMA'] . ' - ' . $area['DESAREA']));
        }
    }

    private function initComboRicercaModuli($area) {

        // Azzera combo
        Out::html($this->nameForm . '_CODMODULO', '');
        Out::html($this->nameForm . '_BGD_ADASSM[CODMODULO]', '');

        // Aggiungi voce 'TUTTI'
        Out::select($this->nameForm . '_CODMODULO', 1, '', 1, "--- TUTTI ---");

        // Se area corrente non valorizzata, esce
        if ($area == '') {
            return;
        }

        // Carica lista moduli
        $filtri = array();
        $filtri['CODAREAMA'] = $area;
        $moduli = $this->libDB_BOR->leggiBorModuli($filtri);

        // Popola combo in funzione dei dati caricati da db        
        foreach ($moduli as $modulo) {
            Out::select($this->nameForm . '_CODMODULO', 1, $modulo['CODMODULO'], 0, trim($modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO']));
        }
    }

    private function decodDocu($cod, $codField, $desField) {
        $row = $this->libDB->leggiBgdTipdocChiave($cod);
        if ($row) {
            Out::valore($codField, $row['IDTIPDOC']);
            Out::valore($desField, $row['DESCRIZIONE']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    private function decodConf($cod, $codField, $desField) {
        $row = $this->libDB->leggiBgdAdmcnfChiave($cod);
        if ($row) {
            Out::valore($codField, $row['IDADMCNF']);
            switch ($row['F_GESTDOC']) {
                case 0:
                    $decodifica = '0 - Nessuna gestione documentale';
                    break;
                case 3:
                    $decodifica = '3 - DOCER';
                    break;
                case 4:
                    $decodifica = '4 - ALFRESCO';
                    break;
            }
            Out::valore($desField, $row['CODMODULO'] . ' (' . $decodifica . ')');
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

}

?>