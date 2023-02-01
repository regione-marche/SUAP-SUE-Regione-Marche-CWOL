<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaEmi() {
    $cwbBtaEmi = new cwbBtaEmi();
    $cwbBtaEmi->parseEvent();
    return;
}

class cwbBtaEmi extends cwbBpaGenTab {

    const tipEmi1 = "Riscossione diretta gestita dall'ente senza denuncia";
    const tipEmi2 = "Riscossione diretta gestita dall'ente con denuncia";
    const tipEmi3 = 'Riscossione diretta tramite banca';
    const tipEmi4 = 'Riscossione diretta tramite C.N.C.(ruolo)';
    const tipEmi5 = 'Rateizzazione';
    const tipEmi6 = 'Riscossione diretta accertamenti';
    const tipEmi7 = 'Ingiunzione di pagamento';
    const tipEmi8 = 'Sollecito/Costituzione in mora';
    const tipEmi9 = 'Provvedimento';
    const tipEmi10 = 'Flussi e usi non contabili';
    const tipEmi11 = 'Concessioni';
    const tipEmi12 = 'Ordini di lavoro';

    function initVars() {
        $this->GRID_NAME = 'gridBtaEmi';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 1;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
    }

    protected function postApriForm() {
        $this->initTable();
    }

    protected function postDettaglio($index) {
        
    }

    private function initTable() {
        $html = '<select id="' . $this->nameForm . '_gs_TIPOEMI" name="TIPOEMI" style="width:100%"></select>';
        Out::gridSetColumnFilterHtml($this->nameForm, $this->GRID_NAME, 'TIPOEMI', $html);
        Out::select($this->nameForm . '_gs_TIPOEMI', 1, '', 0, '---TUTTI---');
        if (intval($this->externalParams['TIPOEMI']) === 2) {
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 2, 0, self::tipEmi2);
        } elseif (intval($this->externalParams['TIPOEMI']) === 8) {
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 8, 0, self::tipEmi8);
        } elseif ($this->externalParams['TIPOEMI_or']) {
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 2, 0, self::tipEmi2);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 6, 0, self::tipEmi6);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 9, 0, self::tipEmi9);
        } else {
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 1, 0, self::tipEmi1);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 2, 0, self::tipEmi2);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 3, 0, self::tipEmi3);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 4, 0, self::tipEmi4);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 5, 0, self::tipEmi5);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 6, 0, self::tipEmi6);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 7, 0, self::tipEmi7);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 8, 0, self::tipEmi8);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 9, 0, self::tipEmi9);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 10, 0, self::tipEmi10);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 11, 0, self::tipEmi11);
            Out::select($this->nameForm . '_gs_TIPOEMI', 1, 12, 0, self::tipEmi12);
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDBOL_SERE_formatted'] != '') {
            $this->gridFilters['IDBOL_SERE'] = $this->formData['IDBOL_SERE_formatted'];
        }
        if ($_POST['ANNOEMI_formatted'] != '') {
            $this->gridFilters['ANNOEMI'] = $this->formData['ANNOEMI_formatted'];
        }
        if ($_POST['NUMEMI_formatted'] != '') {
            $this->gridFilters['NUMEMI'] = $this->formData['NUMEMI_formatted'];
        }
        if ($_POST['DES_GE60'] != '') {
            $this->gridFilters['DES_GE60'] = $this->formData['DES_GE60'];
        }
        if ($_POST['TIPOEMI'] != '') {
            $this->gridFilters['TIPOEMI'] = $this->formData['TIPOEMI'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->externalParams['TIPOEMI']) {
            $filtri['TIPOEMI'] = $this->externalParams['TIPOEMI'];
        } elseif ($this->externalParams['TIPOEMI_or']) {
            $filtri['TIPOEMI_or'] = $this->externalParams['TIPOEMI_or'];
        }
        $filtri['IDBOL_SERE'] = trim($this->formData[$this->nameForm . '_IDBOL_SERE']);
        $filtri['ANNOEMI'] = trim($this->formData[$this->nameForm . '_ANNOEMI']);
        $filtri['NUMEMI'] = trim($this->formData[$this->nameForm . '_NUMEMI']);
        $this->compilaFiltri($filtri);

        $this->SQL = $this->libDB->getSqlLeggiBtaEmi($filtri, false, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($IDBOL_SERE, $ANNOEMI, $NUMEMI) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBtaEmiChiave($IDBOL_SERE, $ANNOEMI, $NUMEMI, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        if(is_array($Result_tab)){
            foreach ($Result_tab as $key => $Result_rec) {
                $Result_tab[$key]['IDBOL_SERE_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDBOL_SERE']);
                $Result_tab[$key]['ANNOEMI_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['ANNOEMI']);
                $Result_tab[$key]['NUMEMI_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['NUMEMI']);
                switch (intval($Result_rec['TIPOEMI'])) {
                    case 1:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi1;
                        break;
                    case 2:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi2;
                        break;
                    case 3:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi3;
                        break;
                    case 4:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi4;
                        break;
                    case 5:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi5;
                        break;
                    case 6:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi6;
                        break;
                    case 7:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi7;
                        break;
                    case 8:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi8;
                        break;
                    case 9:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi9;
                        break;
                    case 10:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi10;
                        break;
                    case 11:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi11;
                        break;
                    case 12:
                        $Result_tab[$key]['TIPOEMI'] = self::tipEmi12;
                        break;
                }
            }
        }
        return $Result_tab;
    }

}

?>