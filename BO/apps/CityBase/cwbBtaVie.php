<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaVie() {
    $cwbBtaVie = new cwbBtaVie();
    $cwbBtaVie->parseEvent();
    return;
}

class cwbBtaVie extends cwbBpaGenTab {

    protected $libDB_BOR;
    private $flag;

    function initVars() {
        $this->setDetailView(false);
        $this->GRID_NAME = 'gridBtaVie';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 7;
        $this->setTABLE_VIEW("BTA_VIE_V02");
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DESVIA_formatted'] != '') {
            $this->gridFilters['DESVIA'] = $this->formData['DESVIA_formatted'];
        }
        if ($_POST['DATAINIZ'] != '') {
            $this->gridFilters['DATAINIZ'] = $this->formData['DATAINIZ'];
        }
        if ($_POST['DATAFINE'] != '') {
            $this->gridFilters['DATAFINE'] = $this->formData['DATAFINE'];
        }
        if ($_POST['UBICAZIONE'] != '') {
            $this->gridFilters['UBICAZIONE'] = $this->formData['UBICAZIONE'];
        }
        if ($_POST['CODVIA_formatted'] != '') {
            $this->gridFilters['CODVIA'] = $this->formData['CODVIA_formatted'];
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Civici':
                        $this->apriCivici();
                        break;
                    case $this->nameForm . '_Mappa':
                        $this->mappa();
                        break;
                    case $this->nameForm . '_DESVIA_O_decod_butt':
                        $this->decodViaOriginale($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA_O'], ($this->nameForm . '_BTA_VIE[CODVIA_O]'), $_POST[$this->nameForm . '_DESVIA_O_decod'], ($this->nameForm . '_DESVIA_O_decod'), true);
                        break;
                    case $this->nameForm . '_TOPONIMO_butt':
                        $this->controllaToponimo($_POST[$this->nameForm . '_TOPONIMO'], $this->nameForm . '_TOPONIMO', true);
                        break;
                    case $this->nameForm . '_BTA_VIE[TOPONIMO]_butt':
                        $this->controllaToponimo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TOPONIMO'], $this->nameForm . '_BTA_VIE[TOPONIMO]', true);
                        break;
                    case $this->nameForm . '_Conferma':
                        $this->flag = true; // setto flag a true... quando ripasserà sul valida, controllo subito la flag impostata.
                        $this->aggiungi();
                        break;
                }
                break;
            case 'returnFromBtaVie':
                $_POST['nameform'] = null; // svuoto IL NAMEfORM ORIGINALE sennò continua a settare l'alias
                switch ($this->elementId) {
                    case $this->nameForm . '_DESVIA_O_decod_butt':
                    case $this->nameForm . '_DESVIA_O_decod':
                    case $this->nameForm . '_BTA_VIE[CODVIA_O]':
                        Out::valore($this->nameForm . '_BTA_VIE[CODVIA_O]', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_O_decod', $this->formData['returnData']['DESVIA']);

                        break;
                }
                break;
            case 'returnFromBtaTopono':
                switch ($this->elementId) {
                    case $this->nameForm . '_TOPONIMO_butt':
                    case $this->nameForm . '_TOPONIMO':
                        Out::valore($this->nameForm . '_TOPONIMO', trim($this->formData['returnData']['TOPONIMO']));
                        break;
                    case $this->nameForm . '_BTA_VIE[TOPONIMO]_butt':
                    case $this->nameForm . '_BTA_VIE[TOPONIMO]':
                        Out::valore($this->nameForm . '_BTA_VIE[TOPONIMO]', trim($this->formData['returnData']['TOPONIMO']));
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_COD_NUMER':
                        $valore = $_POST[$this->nameForm . '_COD_BELF'] . str_pad($_POST[$this->nameForm . '_COD_NUMER'], 7, "00", STR_PAD_LEFT);
                        Out::valore($this->nameForm . '_COD_NUMER', str_pad($_POST[$this->nameForm . '_COD_NUMER'], 7, "00", STR_PAD_LEFT));
                        Out::valore($this->nameForm . '_VIE_ENTRY', $valore);
                        break;
                    case $this->nameForm . '_CODVIA_da':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_da'], $this->nameForm . '_CODVIA_da');
                        if (!$_POST[$this->nameForm . '_CODVIA_a']) {
                            Out::valore($this->nameForm . '_CODVIA_a', '99999');
                        }
                        break;
                    case $this->nameForm . '_CODVIA_a':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_a'], $this->nameForm . '_CODVIA_a');
                        break;
                    case $this->nameForm . '_BTA_VIE[DESVIA]':
                        $ordiva = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['DESVIA'];
                        Out::valore($this->nameForm . '_BTA_VIE[ORDVIA]', $ordiva);
                        break;
                    case $this->nameForm . '_BTA_VIE[TOPONIMO]':
                        $this->controllaToponimo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TOPONIMO'], $this->nameForm . '_BTA_VIE[TOPONIMO]');
                        break;
                    case $this->nameForm . '_TOPONIMO':
                        $this->controllaToponimo($_POST[$this->nameForm . '_TOPONIMO'], $this->nameForm . '_TOPONIMO');
                        break;
                    case $this->nameForm . '_BTA_VIE[CODVIA]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], $this->nameForm . '_BTA_VIE[CODVIA]');
                        break;
                    case $this->nameForm . '_DESVIA_O_decod':
                        $this->decodViaOriginale(null, ($this->nameForm . '_BTA_VIE[CODVIA_O]'), $_POST[$this->nameForm . '_DESVIA_O_decod'], ($this->nameForm . '_DESVIA_O_decod'));

                        break;
                    case $this->nameForm . '_BTA_VIE[CODVIA_O]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA_O'], $this->nameForm . '_BTA_VIE[CODVIA_O]', $this->nameForm . '_DESVIA_O_decod')) {
                            $this->decodViaOriginale($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA_O'], ($this->nameForm . '_BTA_VIE[CODVIA_O]'), null, ($this->nameForm . '_DESVIA_O_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_O_decod', '');
                        }

                        break;
                }
                break;
        }
    }

    protected function postNuovo() {
        $this->pulisciCampi();
        Out::attributo($this->nameForm . '_BTA_VIE[CODVIA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_VIE[CODVIA]', 'background-color', '#FFFFFF');
        $progr = cwbLibCalcoli::trovaProgressivo("CODVIA", "BTA_VIE_V02");
        $ente = cwbParGen::getBorEnti();
        $codnazpro_ente = intval(substr($ente[0]['CODENTE'], 0, 3));
        $codlocal_ente = intval(substr($ente[0]['CODENTE'], 3, 3)); 
        $codbelfi = $this->reperisci_belf($codnazpro_ente,$codlocal_ente);
        Out::valore($this->nameForm . '_COD_BELF', $codbelfi);
        Out::valore($this->nameForm . '_BTA_VIE[DATAINIZ]', date("d/m/Y"));
        Out::valore($this->nameForm . '_BTA_VIE[CODVIA]', $progr);
        Out::hide($this->nameForm . '_Mappa');
        Out::setFocus("", $this->nameForm . '_BTA_VIE[TOPONIMO]');
    }
    
    protected function postApriForm() {
        $this->initComboEnte();
        Out::hide($this->nameForm . '_Mappa');
        Out::hide($this->nameForm . '_Associaz');
        Out::hide($this->nameForm . '_Relazioni');
        Out::hide($this->nameForm . '_Entita');
        Out::hide($this->nameForm . '_Civici');
        Out::setFocus("", $this->nameForm . '_DESVIA');
    }

    public function postAltraRicerca() {
        Out::hide($this->nameForm . '_Associaz');
        Out::hide($this->nameForm . '_Relazioni');
        Out::hide($this->nameForm . '_Entita');
        Out::hide($this->nameForm . '_Civici');
        Out::hide($this->nameForm . '_Mappa');
        Out::setFocus("", $this->nameForm . '_DESVIA');
    }

    public function postTornaElenco() {
        Out::show($this->nameForm . '_Associaz');
        Out::show($this->nameForm . '_Relazioni');
        Out::show($this->nameForm . '_Entita');
        Out::show($this->nameForm . '_Civici');
        Out::show($this->nameForm . '_Mappa');
    }

    protected function postAggiungi() {
        // todo: va richiamato il metodo $reperisci_belf da convertire da Cityware.
        Out::setFocus("", $this->nameForm . '_BTA_VIE[CODVIA]');
    }

    private function apriCivici() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        $externalFilter = array();
        $externalFilter[$this->PK] = $this->CURRENT_RECORD[$this->PK];

        cwbLib::apriFinestraDettaglio('cwbBtaNcivi', $this->nameForm, 'returnFromBtaNcivi', $_POST['id'], $this->CURRENT_RECORD, $externalFilter);
    }

    private function mappa() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }
        $ente = $this->libDB_BOR->leggiBorEntiChiave(trim($this->formData[$this->nameForm . '_PROGENTE']));
        $parametri = $this->CURRENT_RECORD['TOPONIMO'] . "+" . $this->CURRENT_RECORD['DESVIA'] . "+" . $this->CURRENT_RECORD['NUMCIV'] . "+" . trim($ente['DESENTE']) . "+italia";
        $url = "http://maps.google.com/maps?q=" . $parametri;
        Out::codice("window.open('" . $url . "','_Blank')");
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_COD_BELF', '');
        Out::valore($this->nameForm . '_COD_NUMER', '');
        Out::valore($this->nameForm . '_VIE_ENTRY', '');
        Out::valore($this->nameForm . '_DESVIA_O_decod', '');
    }

    protected function postDettaglio($index) {
        $this->decodViaOriginale($this->CURRENT_RECORD['CODVIA_O'], ($this->nameForm . '_BTA_VIE[CODVIA_O]'), null, ($this->nameForm . '_DESVIA_O_decod'));
        Out::attributo($this->nameForm . '_BTA_VIE[CODVIA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIE[CODVIA]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_VIE[DESVIA]');
        Out::valore($this->nameForm . '_BTA_VIE[TOPONIMO]', trim($this->CURRENT_RECORD['TOPONIMO']));
        Out::valore($this->nameForm . '_BTA_VIE[DESVIA]', trim($this->CURRENT_RECORD['DESVIA']));
        Out::valore($this->nameForm . '_BTA_VIE[ORDVIA]', trim($this->CURRENT_RECORD['ORDVIA']));
        Out::valore($this->nameForm . '_BTA_VIE[DESVIAUFF]', trim($this->CURRENT_RECORD['DESVIAUFF']));
        Out::valore($this->nameForm . '_BTA_VIE[UBICAZIONE]', trim($this->CURRENT_RECORD['UBICAZIONE']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_Associaz');
        Out::show($this->nameForm . '_Relazioni');
        Out::show($this->nameForm . '_Entita');
        Out::show($this->nameForm . '_Civici');
        $filtri['CODVIA_da'] = trim($this->formData[$this->nameForm . '_CODVIA_da']);
        $filtri['CODVIA_a'] = trim($this->formData[$this->nameForm . '_CODVIA_a']);
        $filtri['DESVIA'] = trim($this->formData[$this->nameForm . '_DESVIA']);
        $filtri['TOPONIMO'] = trim($this->formData[$this->nameForm . '_TOPONIMO']);
        $filtri['PROGENTE'] = trim($this->formData[$this->nameForm . '_PROGENTE']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        if($filtri['FLAG_DIS'] === '' || $filtri['FLAG_DIS'] === null){
            $filtri['FLAG_DIS'] = 0;
        }
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaVie($filtri, true, $sqlParams);
    }

    protected function postElenca() {
        Out::show($this->nameForm . '_Mappa');
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaVieChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODVIA_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODVIA']);
            $enti = $this->libDB_BOR->leggiBorEnti(array());
            $elementi = count($enti);
            if ($elementi > 1) {
                $Result_tab[$key]['DESVIA_formatted'] = trim($Result_tab[$key]['DES_BREVE']) .
                        '-' . $Result_tab[$key]['TOPONIMO'] . ' ' . $Result_tab[$key]['DESVIA'];
            } else {
                $Result_tab[$key]['DESVIA_formatted'] = $Result_tab[$key]['TOPONIMO'] . ' ' . $Result_tab[$key]['DESVIA'];
            }
        }
        return $Result_tab;
    }

    private function reperisci_belf($codnazpro_ente,$codlocal_ente) {
        $row = $this->libDB->leggiBtaLocalChiave($codnazpro_ente, $codnazpro_ente);
        return $row['CODBELFI'];
    }

    private function initComboEnte() {

        // Azzera combo
        Out::html($this->nameForm . '_PROGENTE', '');

        // Carica lista aree
        $enti = $this->libDB_BOR->leggiBorEnti(array());
        $elementi = count($enti); // conto numero di enti presenti
        if ($elementi == 1) {      // Se ho solamente un ente, nascondo la combo
            Out::hide($this->nameForm . '_PROGENTE_field');
            Out::hide($this->nameForm . '_BTA_VIE[PROGENTE]_field');
            return;
        }

        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_PROGENTE', 1, '', 0, "--- TUTTI ---");
//        Out::select($this->nameForm . '_BTA_VIE[PROGENTE]', 1, '', 0, "--- TUTTI ---");
        foreach ($enti as $ente) {
            Out::select($this->nameForm . '_PROGENTE', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE']) . "-"
                    . trim($ente['DES_BREVE']));
            Out::select($this->nameForm . '_BTA_VIE[PROGENTE]', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE']) . "-"
                    . trim($ente['DES_BREVE']));
        }
    }

    private function controllaToponimo($toponimo, $campoForm, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaTopono", $this->nameForm, $this->nameFormOrig, null, null, null, $toponimo, $campoForm, "TOPONIMO", 'returnFromBtaTopono', $_POST['id'], $searchButton);
    }

    private function decodViaOriginale($codValue, $codField, $desValue, $desField, $searchButton = false) {
        $row = cwbLib::decodificaLookup("cwbBtaVie", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODVIA", $desValue, $desField, "DESVIA", 'returnFromBtaVie', $_POST['id'], $searchButton);
        if ($row) {
            Out::valore($desField, $row['TOPONIMO'] . ' ' . $row['DESVIA']);
        }
    }

}

?>