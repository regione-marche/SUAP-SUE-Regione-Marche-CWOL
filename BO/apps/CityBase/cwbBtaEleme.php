<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaEleme() {
    $cwbBtaEleme = new cwbBtaEleme();
    $cwbBtaEleme->parseEvent();
    return;
}

class cwbBtaEleme extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaEleme';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 10;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'editGridRow':
            case 'editRowInline':
                if ($this->formData['rowid'] <= 20) {
                    Out::msgBlock('', 3000, false, 'Attenzione! Non è consentita la modifica per gli Elementi con codice minore di 20');
                    $this->setBreakEvent(true);
                    break;
                }
                break;
            case 'dbClickRow':
                if (!cwbLibCheckInput::IsNBZ($this->returnNameForm)) {
                    
                } else {
                    if ($this->formData['rowid'] <= 20) {
                        Out::msgBlock('', 3000, false, 'Attenzione! Non è consentita la modifica per gli Elementi con codice minore di 20');
                        $this->setBreakEvent(true);
                        break;
                    }
                }
                break;

            case 'delGridRow':
                if ($this->formData['rowid'] <= 20) {
                    Out::msgBlock('', 3000, false, 'Attenzione! Non è consentita la cancellazione per gli Elementi con codice minore di 20');
                    $this->setBreakEvent(true);
                    break;
                }
                break;
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODELEMEN':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODELEMEN'], $this->nameForm . '_CODELEMEN');
                        break;
                    case $this->nameForm . '_BTA_ELEME[CODELEMEN]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->nameForm . '_BTA_ELEME[CODELEMEN]');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Voci':
                        $this->apriVoci();
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_ELEME[CODELEMEN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_ELEME[CODELEMEN]', 'background-color', '#FFFFFF');
        $progr = cwbLibCalcoli::trovaProgressivo("CODELEMEN", "BTA_ELEME");
        Out::valore($this->nameForm . '_BTA_ELEME[CODELEMEN]', $progr);
        Out::hide($this->nameForm . '_Voci');
        Out::setFocus("", $this->nameForm . '_BTA_ELEME[DESELEME]');
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_Voci');
        Out::setFocus("", $this->nameForm . '_DESELEMEN');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Voci');
        Out::setFocus("", $this->nameForm . '_DESELEMEN');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_ELEME[DESELEMEN]');
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
        Out::attributo($this->nameForm . '_BTA_ELEME[CODELEMEN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_ELEME[CODELEMEN]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BTA_ELEME[DESELEMEN]', trim($this->CURRENT_RECORD['DESELEMEN']));
        Out::show($this->nameForm . '_Voci');
        Out::setFocus('', $this->nameForm . '_BTA_ELEME[DESELEMEN]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODELEMEN'] = trim($this->formData[$this->nameForm . '_CODELEMEN']);
        $filtri['DESELEMEN'] = trim($this->formData[$this->nameForm . '_DESELEMEN']);
        $this->compilaFiltri($filtri);
        Out::show($this->nameForm . '_Voci');
        $this->SQL = $this->libDB->getSqlLeggiBtaEleme($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaElemeChiave($index, $sqlParams);
    }

    protected function apriVoci() {
        if ($this->getDetailView()) {
            $this->formDataToCurrentRecord();
        } else {
            $this->loadCurrentRecord($_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow']);
        }

        $externalFilter = array();
        if (!empty($this->CURRENT_RECORD['CODELEMEN'])) {
            $externalFilter['CODELEMEN'] = array();
            $externalFilter['CODELEMEN']['PERMANENTE'] = true;
            $externalFilter['CODELEMEN']['VALORE'] = $this->CURRENT_RECORD['CODELEMEN'];
            $externalFilter['DESELEMEN'] = array();
            $externalFilter['DESELEMEN']['PERMANENTE'] = true;
            $externalFilter['DESELEMEN']['VALORE'] = $this->CURRENT_RECORD['DESELEMEN'];
            $externalFilter['ELEMENANA']['PERMANENTE'] = true;
            $externalFilter['ELEMENANA']['VALORE'] = $this->CURRENT_RECORD['ELEMENANA'];
            $externalFilter['ELEMENTRI']['PERMANENTE'] = true;
            $externalFilter['ELEMENTRI']['VALORE'] = $this->CURRENT_RECORD['ELEMENTRI'];
            $externalFilter['ELEMENDEF']['PERMANENTE'] = true;
            $externalFilter['ELEMENDEF']['VALORE'] = $this->CURRENT_RECORD['ELEMENDEF'];
            $externalFilter['F_MAPGIS']['PERMANENTE'] = true;
            $externalFilter['F_MAPGIS']['VALORE'] = $this->CURRENT_RECORD['F_MAPGIS'];
        }
        cwbLib::apriFinestraRicerca('cwbBtaVoci', $this->nameForm, 'returnFromBtaVoci', $_POST['id'], true, $externalFilter, $this->nameFormOrig);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODELEMEN_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODELEMEN']);

            //Valorizzo check Anagrafe
            if ($Result_tab[$key]['ELEMENANA'] == "S") {
                $Result_tab[$key]['ELEMENANA'] = 1;
            } else {
                $Result_tab[$key]['ELEMENANA'] = 0;
            }

            //Valorizzo check Tributi
            if ($Result_tab[$key]['ELEMENTRI'] == "S") {
                $Result_tab[$key]['ELEMENTRI'] = 1;
            } else {
                $Result_tab[$key]['ELEMENTRI'] = 0;
            }

            //Valorizzo check Predefinito
            if ($Result_tab[$key]['ELEMENDEF'] == "S") {
                $Result_tab[$key]['ELEMENDEF'] = 1;
            } else {
                $Result_tab[$key]['ELEMENDEF'] = 0;
            }
        }
        return $Result_tab;
    }

}

?>