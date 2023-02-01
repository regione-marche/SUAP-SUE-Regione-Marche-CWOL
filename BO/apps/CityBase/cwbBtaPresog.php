<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggSearchUtils.class.php';

function cwbBtaPresog() {
    $cwbBtaPresog = new cwbBtaPresog();
    $cwbBtaPresog->parseEvent();
    return;
}

class cwbBtaPresog extends cwbBpaGenTab {

    private $nameFormBtaSoggRicerca = null;

    function initVars() {
        $this->GRID_NAME = 'gridBtaPresog';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
        $this->skipAuth = true;
        $this->TABLE_VIEW = 'BTA_PRESOG_V01';
    }

    protected function postConstruct() {
        $this->nameFormBtaSoggRicerca = cwbParGen::getFormSessionVar($this->nameForm, '_nameFormBtaSoggRicerca');
    }

    protected function postDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_nameFormBtaSoggRicerca', $this->nameFormBtaSoggRicerca);
        }
    }

    protected function elaboraCurrentRecord($operation) {
        // trovaProgressivo qui
        if ($operation === itaModelService::OPERATION_INSERT) {
            $id = cwbLibCalcoli::trovaProgressivo('PROGPSO', 'BTA_PRESOG');
            $this->CURRENT_RECORD['PROGSOGG'] = cwbBtaSoggSearchUtils::leggiChiaveSelezionata($this->nameFormBtaSoggRicerca);
            $this->CURRENT_RECORD['PROGPSO'] = $id;
            if ($this->externalParams) {
                $this->CURRENT_RECORD['PROGTES'] = $this->externalParams;
            }
        }
        if ($operation === itaModelService::OPERATION_UPDATE) {
            $this->CURRENT_RECORD['PROGSOGG'] = cwbBtaSoggSearchUtils::leggiChiaveSelezionata($this->nameFormBtaSoggRicerca);
        }
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_divId');
    }

    protected function postNuovo() {
        $this->innestaSoggetti();
    }

    protected function postDettaglio($index) {
        $this->innestaSoggetti();
        cwbBtaSoggSearchUtils::popolaCampi($this->nameFormBtaSoggRicerca, $this->CURRENT_RECORD['PROGSOGG']);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        Out::hide($this->nameForm . '_divId');
        $filtri['PROGTES'] = $this->externalParams;
        $pretes = $this->libDB->leggiBtaPretesChiave($this->externalParams);
        $html = '<font size="3" color="red">' . $pretes['DESTES'] . '</font>';
        Out::html($this->nameForm . '_span', $html);
        $filtri['NOMINATIVO'] = trim($this->formData[$this->nameForm . '_NOMINATIVO']);
        $filtri['PROGSOGG'] = trim($this->formData[$this->nameForm . '_PROGSOGG']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaPresogV01($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaPresogV01Chiave($index, $sqlParams);
    }

    private function innestaSoggetti() {
        Out::innerHtml($this->nameForm . '_divSoggetto', ""); // pulisco div 
        $formObj = cwbBtaSoggSearchUtils::innestaComponenteSoggetto($this->nameForm . '_divSoggetto', $this->nameFormOrig, $this->nameForm);
        $this->nameFormBtaSoggRicerca = $formObj->getNameForm();
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $soggetto = array();
            $Result_tab[$key]['PROGSOGG_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGSOGG']);
            $Result_tab[$key]['NOMINATIVO'] = $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'];
            $libDB = new cwbLibDB_BTA_SOGG();
            $soggetto = $libDB->leggiBtaSoggChiave($Result_tab[$key]['PROGSOGG']);
            $anno = str_pad($Result_tab[$key]['ANNO'], 4, '0', STR_PAD_LEFT);
            $mese = str_pad($Result_tab[$key]['MESE'], 2, '0', STR_PAD_LEFT);
            $giorno = str_pad($Result_tab[$key]['GIORNO'], 2, '0', STR_PAD_LEFT);
            $Result_tab[$key]['DTNASC'] = $anno . '/' . $mese . '/' . $giorno;
        }
        return $Result_tab;
    }

}

?>