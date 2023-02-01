<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaVoci() {
    $cwbBtaVoci = new cwbBtaVoci();
    $cwbBtaVoci->parseEvent();
    return;
}

class cwbBtaVoci extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaVoci';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 10;
        $this->libDB = new cwbLibDB_BTA();
        $this->setTABLE_VIEW("BTA_VOCI_V01");
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODELEMEN':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODELEMEN'], $this->nameForm . '_CODELEMEN');
                        break;
                    case $this->nameForm . '_BTA_VOCI[CODELEMEN]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->nameForm . '_BTA_VOCI[CODELEMEN]');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Associaz':
                        $this->apriAssociaz();
                        break;
                }
                break;
            case 'openform':
                switch ($_POST['id']) {
                    case $this->returnNameForm . '_Voci':
                        Out::valore($this->nameForm . '_CODELEMEN2', $_POST[$this->nameForm . '_CODELEMEN']);
                        Out::valore($this->nameForm . '_DESELEMEN2', $_POST[$this->nameForm . '_DESELEMEN']);
                        $this->flagModulo();
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("CODVOCEL", "BTA_VOCI", " CODELEMEN = " . $this->formData[$this->nameForm . '_CODELEMEN2']);
        Out::valore($this->nameForm . '_BTA_VOCI[CODVOCEL]', $progr);
        Out::hide($this->nameForm . '_Associaz');
        Out::setFocus("", $this->nameForm . '_BTA_VOCI[DESVOCEEL]');
        Out::css($this->nameForm . '_BTA_VOCI[CODVOCEL]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_VOCI[CODELEMEN]', $this->formData[$this->nameForm . '_CODELEMEN2']);
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_Associaz');
        Out::setFocus("", $this->nameForm . '_DESVOCEEL');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Associaz');
        Out::setFocus("", $this->nameForm . '_DESVOCEEL');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_VOCI[DESELEMEN]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODELEMEN_formatted'] != '') {
            $this->gridFilters['CODELEMEN'] = $this->formData['CODELEMEN_formatted'];
        }
        if ($_POST['DESELEMEN'] != '') {
            $this->gridFilters['DESELEMEN'] = $this->formData['DESELEMEN'];
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_VOCI[CODELEMEN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VOCI[CODELEMEN]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_VOCI[DESELEMEN]', trim($this->CURRENT_RECORD['DESELEMEN']));
        Out::show($this->nameForm . '_Associaz');
        Out::setFocus('', $this->nameForm . '_BTA_VOCI[DESELEMEN]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->masterRecord != null) {
            $filtri['CODELEMEN'] = $this->masterRecord['CODELEMEN'];
        } else {
            $filtri['CODELEMEN'] = trim($this->formData[$this->nameForm . '_CODELEMEN2']);
            $filtri['CODVOCEL'] = trim($this->formData[$this->nameForm . '_CODVOCEL']);
            $filtri['DESVOCEEL'] = trim($this->formData[$this->nameForm . '_DESVOCEEL']);
        }
        Out::show($this->nameForm . '_Associaz');
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaVoci($filtri, false, $sqlParams);
    }

    protected function postElenca() {
        $this->valorizzaMaster();
    }

    private function valorizzaMaster() {
        if ($this->masterRecord) {
            Out::valore($this->nameForm . '_CODELEMEN2', $this->masterRecord['CODELEMEN']);
            Out::valore($this->nameForm . '_DESELEMEN2', $this->masterRecord['DESELEMEN']);
            if ($this->masterRecord['ELEMENANA'] == "S") {
                Out::attributo($this->nameForm . "_ELEMENANA", "checked", "0", "checked");
            }
            if ($this->masterRecord['ELEMENTRI'] == "S") {
                Out::attributo($this->nameForm . "_ELEMENTRI", "checked", "0", "checked");
            }
            if ($this->masterRecord['ELEMENDEF'] == "S") {
                Out::attributo($this->nameForm . "_ELEMENDEF", "checked", "0", "checked");
            }
        }
    }

    protected function apriAssociaz() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            if ($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'] != 'null') {
                $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);

                if (!empty($this->CURRENT_RECORD['CODELEMEN'])) {
                    $externalParams = array();
                    $externalParams['CODELEMEN']['PERMANENTE'] = true;
                    $externalParams['CODELEMEN'] = $this->CURRENT_RECORD['CODELEMEN'];
                     $externalParams['DESELEMEN']['PERMANENTE'] = true;
                    $externalParams['DESELEMEN'] = $this->CURRENT_RECORD['DESELEMEN'];
                    $externalParams['CODVOCEL']['PERMANENTE'] = true;
                    $externalParams['CODVOCEL'] = $this->CURRENT_RECORD['CODVOCEL'];
                  
                }
                cwbLib::apriFinestraRicerca('cwbBtaViavoc', $this->nameForm, 'returnFromBtaViavoc', $_POST['id'], true, $externalParams, $this->nameFormOrig);
            } else {
                Out::msgStop('Voci di Viario', 'Selezionare una riga dalla grid');
            }
        }
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($CODELEMEN, $CODVOCEL) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaVociChiave($CODELEMEN, $CODVOCEL, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODVOCEL_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODVOCEL']);
        }
        return $Result_tab;
    }

    private function flagModulo() {
        if ($_POST[$this->nameForm . '_ELEMENANA'] == 'S') {
            Out::valore($this->nameForm . '_ELEMENANA', 1);
        } else {
            Out::valore($this->nameForm . '_ELEMENANA', 0);
        }
        if ($_POST[$this->nameForm . '_ELEMENTRI'] == 'S') {
            Out::valore($this->nameForm . '_ELEMENTRI', 1);
        } else {
            Out::valore($this->nameForm . '_ELEMENTRI', 0);
        }
        if ($_POST[$this->nameForm . '_ELEMENDEF'] == 'S') {
            Out::valore($this->nameForm . '_ELEMENDEF', 1);
        } else {
            Out::valore($this->nameForm . '_ELEMENDEF', 0);
        }
        Out::valore($this->nameForm . '_F_MAPGIS', $_POST[$this->nameForm . '_F_MAPGIS']);
    }
    
     protected function initializeTable($sqlParams, &$sortIndex, &$sortOrder) {
                $sortIndex = array();
                $sortIndex[] = 'CODELEMEN';
                $sortIndex[] = 'CODVOCEL';

        if (empty($sortOrder)) {
            $sortOrder = 'asc';
        }
        return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
    }

}

?>