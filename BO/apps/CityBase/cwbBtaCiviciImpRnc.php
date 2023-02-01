<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';

function cwbBtaCiviciImpRnc() {
    $cwbBtaCiviciImpRnc = new cwbBtaCiviciImpRnc();
    $cwbBtaCiviciImpRnc->parseEvent();
    return;
}

class cwbBtaCiviciImpRnc extends itaFrontControllerCW {
    public $divGes = "_divGestione";

    function postItaFrontControllerCostruct() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName());
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                Out::show($this->nameForm.$this->divGes);
                Out::setFocus('', $this->nameForm . '_DESTIPCIV_1');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESTIPCIV_1_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaTipciv', $this->nameForm, 'returnFromBtaTipciv', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_DESTIPCIV_2_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaTipciv', $this->nameForm, 'returnFromBtaTipciv', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_DESTIPCIV_3_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaTipciv', $this->nameForm, 'returnFromBtaTipciv', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_DESTIPCIV_3_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaTipciv', $this->nameForm, 'returnFromBtaTipciv', $_POST['id'], true);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CESSARE':
                        $value = $_POST[$this->nameForm . '_CESSARE']; // vedo se chekkata o no.
                        if ($value == 1) {
                            $data = date("d/m/Y");
                            Out::valore($this->nameForm . '_DATAFINE', $data);
                        } else {
                            Out::valore($this->nameForm . '_DATAFINE', ' ');
                        }
                        break;
                }
                break;
            case 'returnFromBtaTipciv':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESTIPCIV_1_butt':
                        Out::valore($this->nameForm . '_DESTIPCIV_1', $this->formData['returnData']['DESTIPCIV']);
                        break;
                    case $this->nameForm . '_DESTIPCIV_2_butt':
                        Out::valore($this->nameForm . '_DESTIPCIV_2', $this->formData['returnData']['DESTIPCIV']);
                        break;
                    case $this->nameForm . '_DESTIPCIV_3_butt':
                        Out::valore($this->nameForm . '_DESTIPCIV_3', $this->formData['returnData']['DESTIPCIV']);
                        break;
                }
        }
    }

    public function close() {
        parent::close();
    }

}

?>