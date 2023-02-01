<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorGrpred() {
    $cwbBorGrpred = new cwbBorGrpred();
    $cwbBorGrpred->parseEvent();
    return;
}

class cwbBorGrpred extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBorGrpred';
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 4;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BOR();
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_GRPRED[PROGRESPO]_butt':
                        cwbLib::apriFinestraRicerca('cwbBorRespo', $this->nameForm, 'returnFromBorRespo', $_POST['id'], true);
                        break;
                }
                break;
            case 'returnFromBorRespo':
                switch ($this->elementId) {
                    case $this->nameForm . '_BOR_GRPRED[PROGRESPO]_butt':
                        Out::valore($this->nameForm . '_BOR_GRPRED[PROGRESPO]', $this->formData['returnData']['PROGRESPO']);
                        Out::valore($this->nameForm . '_NOMERES', $this->formData['returnData']['NOMERES']);
                        Out::valore($this->nameForm . '_BOR_GRPRED[IDRESPO]', $this->formData['returnData']['PROGRESPO']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_GRPRED[PROGRESPO]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'], $this->nameForm . '_BOR_GRPRED[PROGRESPO]')) {
                            $this->decodRespo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'], ($this->nameForm . '_BOR_GRPRED[PROGRESPO]'), ($this->nameForm . '_NOMERES'));
                            Out::valore($this->nameForm . '_BOR_GRPRED[IDRESPO]', $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO']);
                        }else{
                            Out::valore($this->nameForm . '_NOMERES','');
                        }
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGGRPRE_formatted'] != '') {
            $this->gridFilters['PROGGRPRE'] = $this->formData['PROGGRPRE_formatted'];
        }
        if ($_POST['DES_GRPRE'] != '') {
            $this->gridFilters['DES_GRPRE'] = $this->formData['DES_GRPRE'];
        }
    }

    protected function preElenca() {
        Out::hide($this->nameForm . '_BOR_GRPRED[PROGENTE]');
    }

    protected function postNuovo() {
        $row = cwbParGen::getFormSessionVar($this->nameForm, 'masterRecord');
        if ($row != null) {
            Out::valore($this->nameForm . '_BOR_GRPRED[PROGGRPRE]', $row['PROGGRPRE']);
            $this->decodGruppo($row['PROGGRPRE'], ($this->nameForm . '_BOR_GRPRED[PROGGRPRE]'), ($this->nameForm . '_DES_GRPRE_decod'));
        }
        Out::valore($this->nameForm . '_BOR_GRPRED[PROGRESPO]', ''); // PULISCO campi per aggiunta
        Out::valore($this->nameForm . '_NOMERES', '');
        Out::setFocus("", $this->nameForm . '_BOR_GRPRED[PROGRESPO]');
    }

    protected function formDataToCurrentRecord() {
        $this->CURRENT_RECORD = $_POST[$this->nameForm . '_' . $this->TABLE_NAME];
        // forzo l'aggiunta di questi due campi nell'array perchè altrimenti non li vedevo mai.
        $this->CURRENT_RECORD['PROGENTE'] = 1; // TODO: per adesso lo setto fisso, ma poi bisognerà valorizzarlo in base all'ente selezionato.
        $this->CURRENT_RECORD['IDRESPO'] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'];
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_GRPRED[PROGRESPO]');
    }

    protected function postDettaglio($index) {
        $this->decodRespo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGRESPO'], ($this->nameForm . '_BOR_GRPRED[PROGRESPO]'), ($this->nameForm . '_NOMERES'));
        $this->decodGruppo($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGGRPRE'], ($this->nameForm . '_BOR_GRPRED[PROGGRPRE]'), ($this->nameForm . '_DES_GRPRE_decod'));
        Out::setFocus('', $this->nameForm . '_BOR_GRPRED[DES_GRPRE]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_GRPRED[DES_GRPRE]', trim($this->CURRENT_RECORD['DES_GRPRE']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $this->valorizzaMaster();
        cwbParGen::setFormSessionVar($this->nameForm, 'masterRecord', $this->masterRecord);
        Out::show($this->nameForm . '_Gruppo');
        $this->SQL = $this->libDB->getSqlLeggiBorGrpredChiave($this->masterRecord['PROGGRPRE'],$this->masterRecord['PROGENTE'],true,$sqlParams);
    }

    private function valorizzaMaster() {
        if ($this->masterRecord) {
            Out::valore($this->nameForm . '_PROG', $this->masterRecord['PROGGRPRE']);
            Out::valore($this->nameForm . '_DESCR', $this->masterRecord['DES_GRPRE']);
        }
    }

    public function sqlDettaglio($index, &$sqlParams) {
        list($proggrpre, $progente, $progrespo) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBorGrpredChiave($proggrpre, $progente, $progrespo, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        
    }

    private function decodGruppo($cod, $codField, $desField) {
        //todo: gli passo fisso 1 ma quando mi ritornerà l'id dell'ente selezionato, va corretto.
        $row = $this->libDB->leggiBorGrpretChiave($cod, 1);
        if ($row) {
            Out::valore($codField, $row['PROGGRPRE']);
            Out::valore($desField, $row['DES_GRPRE']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    private function decodRespo($cod, $codField, $desField) {
        $row = $this->libDB->leggiBorRespoChiave($cod);
        if ($row) {
            Out::valore($codField, $row['PROGRESPO']);
            Out::valore($desField, $row['NOMERES']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

}

