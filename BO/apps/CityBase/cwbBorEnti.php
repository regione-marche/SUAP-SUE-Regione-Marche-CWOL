<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBorEnti() {
    $cwbBorEnti = new cwbBorEnti();
    $cwbBorEnti->parseEvent();
    return;
}

class cwbBorEnti extends cwbBpaGenTab {

    protected $libDB_BTA;

    function initVars() {
        $this->GRID_NAME = 'gridBorEnti';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 3;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
//        $this->hasSequence = true;
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESENTE_decod_butt':
                        $this->decodClient($this->formData[$this->nameForm . '_PROGCLIENT'], ($this->nameForm . '_PROGCLIENT'), $this->formData[$this->nameForm . '_DESENTE_decod'], ($this->nameForm . '_DESENTE_decod'), true);
                        break;
                }
                break;
            case 'returnFromBorClient':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESENTE_decod_butt':
                    case $this->nameForm . '_PROGCLIENT':
                    case $this->nameForm . '_DESENTE_decod':

                        Out::valore($this->nameForm . '_PROGCLIENT', $this->formData['returnData']['PROGCLIENT']);
                        Out::valore($this->nameForm . '_DESENTE_decod', $this->formData['returnData']['DESENTE']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGCLIENT':
                        $this->decodClient($this->formData[$this->nameForm . '_PROGCLIENT'], ($this->nameForm . '_PROGCLIENT'), null, ($this->nameForm . '_DESENTE_decod'));
                        break;
                    case $this->nameForm . '_DESENTE_decod':
                        $this->decodClient(null, ($this->nameForm . '_PROGCLIENT'), $this->formData[$this->nameForm . '_DESENTE_decod'], ($this->nameForm . '_DESENTE_decod'));
                        break;
                    case $this->nameForm . '_BOR_ENTI[IDBOL_ENTE]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDBOL_ENTE'], $this->nameForm . '_BOR_ENTI[IDBOL_ENTE]');
                        break;
                    case $this->nameForm . '_BOR_ENTI[CODENTE]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODENTE'], $this->nameForm . '_DESLOCAL_decod', $this->nameForm . '_ISTLOCAL')) {
                            $this->decodLocal($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODENTE'], ($this->nameForm . '_DESLOCAL_decod'), ($this->nameForm . '_ISTLOCAL'));
                        } else {
                            Out::valore($this->nameForm . '_DESLOCAL_decod', '');
                        }
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGENTE_formatted'] != '') {
            $this->gridFilters['PROGENTE'] = $this->formData['PROGENTE_formatted'];
        }
        if ($_POST['DESENTE'] != '') {
            $this->gridFilters['DESENTE'] = $this->formData['DESENTE'];
        }
        if ($_POST['CODENTE'] != '') {
            $this->gridFilters['CODENTE'] = $this->formData['CODENTE'];
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
        if ($_POST['DES_BREVE'] != '') {
            $this->gridFilters['DES_BREVE'] = $this->formData['DES_BREVE'];
        }
        if ($_POST['CODATECO7'] != '') {
            $this->gridFilters['CODATECO7'] = $this->formData['CODATECO7'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
        if ($_POST['IDBOL_ENTE'] != '') {
            $this->gridFilters['IDBOL_ENTE'] = $this->formData['IDBOL_ENTE'];
        }
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("PROGENTE", "BOR_ENTI");
        Out::valore($this->nameForm . '_BOR_ENTI[PROGENTE]', $progr);
        Out::setFocus("", $this->nameForm . '_BOR_ENTI[DESENTE]');
    }

    protected function caricaDatiAggiuntivi($tipoOperazione) {
        //One-To-One
        $tableData = array("operation" => $tipoOperazione,
            "data" => $this->popolaCampiEnteDc()
        );

        $this->modelData->addRelationOneToOne("BOR_ENTEDC", $tableData, "", array("PROGENTE" => "PROGENTE"));
    }

    private function popolaCampiEnteDc() {
        return array(
            'ENTE_DOCER' => $_POST[$this->nameForm . '_ENTE_DOCER'],
            'UTENTE_DOCER' => $_POST[$this->nameForm . '_UTENTE_DOCER'],
            'PWDUTE_DOCER' => $_POST[$this->nameForm . '_PWDUTE_DOCER'],
            'PROGENTE' => $this->CURRENT_RECORD['PROGENTE'],
            'TIMEOPER' => $this->CURRENT_RECORD['TIMEOPER'],
            'DATAOPER' => $this->CURRENT_RECORD['DATAOPER'],
            'CODUTE' => $this->CURRENT_RECORD['CODUTE']
        );
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_ENTI[DESENTE]');
    }

    protected function postConfermaCancella() {
        // cancella su tabella 1:1 entedc
        $this->deleteRecord($this->MAIN_DB, 'BOR_ENTEDC', $this->CURRENT_RECORD[$this->PK], $this->RECORD_INFO, $this->PK);
    }

    protected function postAltraRicerca() {
        Out::setFocus('', $this->nameForm . '_DESENTE');
    }

    protected function postApriForm() {
        $this->initComboEnte();
        Out::setFocus('', $this->nameForm . '_DESENTE');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    private function decodClient($codValue, $codField, $desValue, $desField, $search = false) {
        cwbLib::decodificaLookup("cwbBorClient", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "PROGCLIENT", $desValue, $desField, "DESENTE", "returnFromBorClient", $_POST['id'], $search);
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BOR_ENTI[DESENTE]');
        $this->decodLocal($this->CURRENT_RECORD['CODENTE'], ($this->nameForm . '_DESLOCAL_decod'), ($this->nameForm . '_ISTLOCAL'));
        Out::valore($this->nameForm . '_BOR_ENTI[DESENTE]', trim($this->CURRENT_RECORD['DESENTE']));
        Out::valore($this->nameForm . '_BOR_ENTI[DES_BREVE]', trim($this->CURRENT_RECORD['DES_BREVE']));
        Out::valore($this->nameForm . '_BOR_ENTI[INDIRENTE]', trim($this->CURRENT_RECORD['INDIRENTE']));
        Out::valore($this->nameForm . '_BOR_ENTI[DESLOCAL]', trim($this->CURRENT_RECORD['DESLOCAL']));
        Out::valore($this->nameForm . '_BOR_ENTI[CAP]', trim($this->CURRENT_RECORD['CAP']));
        Out::valore($this->nameForm . '_BOR_ENTI[CODFISCALE]', trim($this->CURRENT_RECORD['CODFISCALE']));
        Out::valore($this->nameForm . '_BOR_ENTI[PARTIVA]', trim($this->CURRENT_RECORD['PARTIVA']));
        Out::valore($this->nameForm . '_BOR_ENTI[TELEFONO]', trim($this->CURRENT_RECORD['TELEFONO']));
        Out::valore($this->nameForm . '_BOR_ENTI[TELEFONO_1]', trim($this->CURRENT_RECORD['TELEFONO_1']));
        Out::valore($this->nameForm . '_ENTE_DOCER', trim($this->CURRENT_RECORD['ENTE_DOCER']));
        Out::valore($this->nameForm . '_UTENTE_DOCER', trim($this->CURRENT_RECORD['UTENTE_DOCER']));
        Out::valore($this->nameForm . '_PWDUTE_DOCER', trim($this->CURRENT_RECORD['PWDUTE_DOCER']));
    }

    private function decodLocal($cod, $desField, $codIstatField) {

        // Ricava CODNAZPRO e CODLOCAL dal codice Ente
        $codnazpro = intval(substr($cod, 0, 3));
        $codlocal = intval(substr($cod, 3, 3));

        // Effettua ricerca per chiave su BTA_LOCAL e popola i campi di decodifica
        $row = $this->libDB_BTA->leggiBtaLocalChiave($codnazpro, $codlocal);
        if ($row) {
            Out::valore($desField, $row['DESLOCAL']);
            Out::valore($codIstatField, str_pad($row['ISTNAZPRO'], 3, '0', STR_PAD_LEFT) . str_pad($row['ISTLOCAL'], 3, '0', STR_PAD_LEFT));
        } else {
            Out::valore($desField, '');
            Out::valore($codIstatField, '');
        }
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESLOCAL_decod', '');
        Out::valore($this->nameForm . '_ISTLOCAL', '');
        Out::valore($this->nameForm . '_ENTE_DOCER', '');
        Out::valore($this->nameForm . '_UTENTE_DOCER', '');
        Out::valore($this->nameForm . '_PWDUTE_DOCER', '');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['PROGENTE'] = trim($this->formData[$this->nameForm . '_PROGENTE']);
        $filtri['DESENTE'] = trim($this->formData[$this->nameForm . '_DESENTE']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorEnti($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorEntiChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGENTE_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGENTE']);
        }
        return $Result_tab;
    }

    private function initComboEnte() {
        // Ente
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 1, 1, "01 -COMUNI E UNIONI DI COMUNI (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 2, 0, "02 -PROVINCE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 3, 0, "03 -COMUNITA' MONTANE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 4, 0, "04 -CITTA' METROPOLITANE (Class=Mecc)");
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 5, 0, "05 -ALTRI ENTI PUBBLICI (Class<>Mecc) Non gestisce funzioni e servizi su spesa(Solo Tit.+Int.)");
        Out::select($this->nameForm . '_BOR_ENTI[NAT_ENTE]', 1, 6, 0, "06 -ALTRI ISTITUTI (Class=Mecc) Gestisce funzioni su spesa");
    }

}

