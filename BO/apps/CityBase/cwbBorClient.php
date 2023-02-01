<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorClient() {
    $cwbBorClient = new cwbBorClient();
    $cwbBorClient->parseEvent();
    return;
}

class cwbBorClient extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorClient';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 3;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
        $this->elencaAutoFlagDis = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_CLIENT[PROGDITTAD]_butt':
                        cwbLib::apriFinestraRicerca('cwbBorDistr', $this->nameForm, 'returnFromBorDistr', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_Enti':
                        $this->apriEnti();
                        break;
                }
                break;
            case 'returnFromBorDistr':
                switch ($this->elementId) {
                    case $this->nameForm . '_BOR_CLIENT[PROGDITTAD]_butt':
                        Out::valore($this->nameForm . '_BOR_CLIENT[PROGDITTAD]', $this->formData['returnData']['PROGDITTAD']);
                        Out::valore($this->nameForm . '_DITTA_decod', $this->formData['returnData']['DESENTE']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_CLIENT[PROGDITTAD]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGDITTAD'], $this->nameForm . '_BOR_CLIENT[PROGDITTAD]', $this->nameForm . '_DITTA_decod')) {
                            $this->decodDitta($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGDITTAD'], ($this->nameForm . '_BOR_CLIENT[PROGDITTAD]'), ($this->nameForm . '_DITTA_decod'));
                        }else{
                            Out:valore($this->nameForm . '_DITTA_decod','');
                        }
                        break;
                    case $this->nameForm . '_BOR_CLIENT[PROGCLIENT]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGCLIENT'], $this->nameForm . '_BOR_CLIENT[PROGCLIENT]');
                        break;
                    case $this->nameForm . '_BOR_CLIENT[DESENTE]':
                        Out::msgInfo("Attenzione!", "La modifica della descrizione dell'Ente rende necessario il rilascio di nuove password per il funzionamento dell'applicativo." );
                        break;
                    case $this->nameForm . '_BOR_CLIENT[CAP]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CAP'], $this->nameForm . '_BOR_CLIENT[CAP]');
                        break;
                }
                break;
        }
    }

    protected function setVisRisultato() {
        parent::setVisRisultato();
        Out::show($this->nameForm . '_Enti');
    }

    protected function setVisRicerca() {
        parent::setVisRicerca();
        Out::hide($this->nameForm . '_Enti');
    }

    protected function setVisNuovo() {
        parent::setVisNuovo();
        Out::hide($this->nameForm . '_Enti');
    }

    protected function setVisDettaglio() {
        parent::setVisDettaglio();
        Out::show($this->nameForm . '_Enti');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGCLIENT_formatted'] != '') {
            $this->gridFilters['PROGCLIENT'] = $this->formData['PROGCLIENT_formatted'];
        }
        if ($_POST['DESENTE'] != '') {
            $this->gridFilters['DESENTE'] = $this->formData['DESENTE'];
        }
        if ($_POST['INDIRENTE'] != '') {
            $this->gridFilters['INDIRENTE'] = $this->formData['INDIRENTE'];
        }
        if ($_POST['CAP'] != '') {
            $this->gridFilters['CAP'] = $this->formData['CAP'];
        }
        if ($_POST['DESLOCAL'] != '') {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if ($_POST['PROVINCIA'] != '') {
            $this->gridFilters['PROVINCIA'] = $this->formData['PROVINCIA'];
        }
        if ($_POST['CODATTIVE'] != '') {
            $this->gridFilters['CODATTIVE'] = $this->formData['CODATTIVE'];
        }
        if ($_POST['NATGIURID'] != '') {
            $this->gridFilters['NATGIURID'] = $this->formData['NATGIURID'];
        }
        if ($_POST['PROGDITTAD'] != '') {
            $this->gridFilters['PROGDITTAD'] = $this->formData['PROGDITTAD'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
    }

    private function apriEnti() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        $externalFilter = array();
        $externalFilter[$this->PK] = $this->CURRENT_RECORD[$this->PK];
        cwbLib::apriFinestraDettaglio('cwbBorEnti', $this->nameForm, 'returnFromBorEnti', $_POST['id'], $this->CURRENT_RECORD, $externalFilter);
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BOR_CLIENT[PROGCLIENT]', 'readonly', '1');
        Out::css($this->nameForm . '_BOR_CLIENT[PROGCLIENT]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BOR_CLIENT[PROGCLIENT]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_CLIENT[PROGCLIENT]');
    }

    protected function preDettaglio($index) {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BOR_CLIENT[PROGCLIENT]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BOR_CLIENT[INDIRENTE]');
        // toglie gli spazi del char
        Out::css($this->nameForm . '_BOR_CLIENT[PROGCLIENT]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BOR_CLIENT[DESENTE]', trim($this->CURRENT_RECORD['DESENTE']));
        Out::valore($this->nameForm . '_BOR_CLIENT[INDIRENTE]', trim($this->CURRENT_RECORD['INDIRENTE']));
        Out::valore($this->nameForm . '_BOR_CLIENT[DESLOCAL]', trim($this->CURRENT_RECORD['DESLOCAL']));
        Out::valore($this->nameForm . '_BOR_CLIENT[CODFISCALE]', trim($this->CURRENT_RECORD['CODFISCALE']));
        Out::valore($this->nameForm . '_BOR_CLIENT[PARTIVA]', trim($this->CURRENT_RECORD['PARTIVA']));
        Out::valore($this->nameForm . '_BOR_CLIENT[CAP]', trim($this->CURRENT_RECORD['CAP']));
        Out::valore($this->nameForm . '_BOR_CLIENT[TELEFONO]', trim($this->CURRENT_RECORD['TELEFONO']));
        Out::valore($this->nameForm . '_BOR_CLIENT[TELEFONO_1]', trim($this->CURRENT_RECORD['TELEFONO_1']));
        $this->decodDitta($this->CURRENT_RECORD['PROGDITTAD'], ($this->nameForm . '_BOR_CLIENT[PROGDITTAD]'), ($this->nameForm . '_DITTA_decod'));
    }

    protected function postApriForm() {
        $this->initComboEnte();
        Out::setFocus('', $this->nameForm . '_DESENTE');
    }

    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_DESENTE');
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DITTA_decod', '');
    }

    public function postSqlElenca($filtri,&$sqlParams=array()) {
        Out::show($this->nameForm . '_Enti');
        $filtri['PROGCLIENT'] = trim($this->formData[$this->nameForm . '_PROGCLIENT']);
        $filtri['DESENTE'] = trim($this->formData[$this->nameForm . '_DESENTE']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorClient($filtri, true,$sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        Out::show($this->nameForm . '_Enti');
        $this->SQL = $this->libDB->getSqlLeggiBorClientChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGCLIENT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGCLIENT']);
        }
        return $Result_tab;
    }

    private function initComboEnte() {
        // Ente
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 1, 1, "01 -COMUNI E UNIONI DI COMUNI (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 2, 0, "02 -PROVINCE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 3, 0, "03 -COMUNITA' MONTANE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 4, 0, "04 -CITTA' METROPOLITANE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 5, 0, "05 -ALTRI ENTI PUBBLICI (Class<>Mecc) Non gestisce funzioni e servizi su spesa(Solo Tit.+Int.)");
        Out::select($this->nameForm . '_BOR_CLIENT[NAT_ENTE]', 1, 6, 0, "06 -ALTRI ISTITUTI (Class=Mecc) Gestisce funzioni su spesa");
    }

    private function decodDitta($cod, $codField, $desField) {
        $row = $this->libDB->leggiBorDistrChiave($cod);
        if ($row) {
            Out::valore($codField, $row['PROGDITTAD']);
            Out::valore($desField, $row['DESENTE']);
        }else{
            Out::valore($codField, '');
            Out::valore($desField, '');
        }
    }
    
}

