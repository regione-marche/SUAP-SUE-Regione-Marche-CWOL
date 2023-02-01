<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TUE.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TDE.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DAN.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_SUT.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TAC.class.php';

function cwbBgeRelazUbic() {
    $cwbBgeRelazUbic = new cwbBgeRelazUbic();
    $cwbBgeRelazUbic->parseEvent();
    return;
}

class cwbBgeRelazUbic extends cwbBpaGenTab {

    function initVars() {
        $this->noCrud = true;
        $this->GRID_NAME_UE = 'gridTueIdent';
        $this->GRID_NAME_AN = 'gridDanAnagra';
        $this->GRID_NAME_TRIBU = 'gridTdeDen';
        $this->GRID_NAME_ICI = 'gridTicQua';
        $this->GRID_NAME_SERVI = 'gridSutIser';
        $this->GRID_NAME_ACQUE = 'gridTacUtenze';
        $this->libDB = new cwbLibDB_BGE();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 14;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Visualizza':
                        $this->Visualizza();
                        break;
                    case $this->nameForm . '_DESVIA_decod_butt':
                        $this->decodVia(null, ($this->nameForm . '_CODVIA'), $_POST[$this->nameForm . '_DESVIA_decod'], ($this->nameForm . '_DESVIA_decod'),true);
                        break;
                }
                break;
            case 'returnFromBorOrgan':
                switch ($this->elementId) {
                    case $this->nameForm . '_BOR_AOO[IDORGAN]_butt':
                        Out::valore($this->nameForm . '_BOR_AOO[IDORGAN]', $this->formData['returnData']['IDORGAN']);
                        Out::valore($this->nameForm . '_DESPORG_decod', $this->formData['returnData']['DESPORG']);
                        break;
                }
                break;
            case 'returnFromBtaVie':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESVIA_decod_butt':
                    case $this->nameForm . '_DESVIA_decod':
                    case $this->nameForm . '_CODVIA':

                        Out::valore($this->nameForm . '_CODVIA', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_decod', $this->formData['returnData']['TOPONIMO'] . ' ' . $this->formData['returnData']['DESVIA']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODVIA':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA'], $this->nameForm . '_CODVIA', $this->nameForm . '_DESVIA_decod')) {
                            $this->decodVia($_POST[$this->nameForm . '_CODVIA'], ($this->nameForm . '_CODVIA'), $_POST[$this->nameForm . '_DESVIA_decod'], ($this->nameForm . '_DESVIA_decod'));
                        } else {
                            Out:valore($this->nameForm . '_DESVIA_decod', '');
                        }
                    case $this->nameForm . '_DESVIA_decod':
                        $this->decodVia(null, ($this->nameForm . '_CODVIA'), $_POST[$this->nameForm . '_DESVIA_decod'], ($this->nameForm . '_DESVIA_decod'));

                    case $this->nameForm . '_BOR_AOO[IDUTRUOR_P]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDUTRUOR_P'], $this->nameForm . '_BOR_AOO[IDUTRUOR_P]');
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        $this->initComboTipImmobile();
        Out::show($this->nameForm . '_divRisultato');
        Out::setFocus("", $this->nameForm . '_CODVIA');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_CODVIA');
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_BOR_AOO[IDAOO]_field');
        Out::setFocus("", $this->nameForm . '_BOR_AOO[CODAOOIPA]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_AOO[DESAOO]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BOR_AOO[IDAOO]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BOR_AOO[DESAOO]');
        Out::show($this->nameForm . '_BOR_AOO[IDAOO]_field');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_AOO[DESAOO]', trim($this->CURRENT_RECORD['DESAOO']));
    }

    public function postSqlElenca($filtri) {
//        $filtri['IDAOO'] = trim($this->formData[$this->nameForm . '_IDAOO']);        
//        $filtri['DESAOO'] = trim($this->formData[$this->nameForm . '_DESAOO']);
//       $filtri['CODAOOIPA'] = trim($this->formData[$this->nameForm . '_CODAOOIPA']);
//        $filtri['PROGENTE'] = trim($this->formData[$this->nameForm . '_PROGENTE']);
//        $this->compilaFiltri($filtri);
        $this->caricaUE();
    }

    private function Visualizza() {
        $this->caricaUE();
        $this->caricaAna();
        $this->caricaTributi();
        $this->caricaICI();
        $this->caricaServizi();
        $this->caricaAcque();
    }

    private function caricaUE() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];

        $filtri = array(
            'CODVIA' => $codvia
        );

        $libTue = new cwtLibDB_TUE();
        $sqlParams = array();
        $sql = $libTue->getSqlLeggiTueIdentV02($filtri, false, $sqlParams);

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_UE, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_UE]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);

        if (!$this->getDataPage($ita_grid01, $this->elaboraGridUe($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_UE);
        }
    }

    private function caricaAna() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];

        $filtri = array(
            'CODVIA' => $codvia,
            'MOTIVO_C' => ' '
        );

        $libDan = new cwdLibDB_DAN();
        $sqlParams = array();
        $sql = $libDan->getSqlLeggiDanAnagra($filtri, true, $sqlParams);
        $sql.= ' ORDER BY NUMCIV, FAMIGLIA, FAMIGLIA_T';

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_AN, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_AN]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridAn($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_AN);
        }
        Out::show($this->nameForm . '_divRicerca');
    }

    private function caricaTributi() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];
        $filtri = array(
            'CODVIA' => $codvia,
            'FLAG_DIS' => 0
        );

        $sqlParams = array();
        $libTde = new cwtLibDB_TDE();
        $sql = $libTde->getSqlLeggiTdeDenV03($filtri, false, $sqlParams);

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_TRIBU, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_TRIBU]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridTribu($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_TRIBU);
        }
        Out::show($this->nameForm . '_divRicerca');
    }

    private function caricaICI() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];

        $sql = "SELECT * FROM TIC_QUA_V09 WHERE FLAG_DIS=0 AND (CODVIA_QUA=$codvia OR CODVIA_UE=$codvia)";
        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_ICI, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_ICI]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridICI($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_ICI);
        }
        Out::show($this->nameForm . '_divRicerca');
    }

    private function caricaServizi() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];
        $filtri = array(
            'CODVIA' => $codvia,
            'FLAG_DIS' => 0
        );

        $libsut = new cwtLibDB_SUT();
        $sqlParams = array();
        $sql = $libsut->getSqlLeggiSutIserV06($filtri, false, $sqlParams);

        $sql.= ' ORDER BY NUMCIV ';

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_SERVI, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_SERVI]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridServi($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_SERVI);
        }
        Out::show($this->nameForm . '_divRicerca');
    }

    private function caricaAcque() {
        $codvia = $_POST[$this->nameForm . '_CODVIA'];

        $filtri = array(
            'CODVIA' => $codvia,
            'FLAG_DIS' => 0
        );

        $libtac = new cwtLibDB_TAC();
        $sqlParams = array();
        $sql = $libtac->getSqlLeggiTacUtenzeV01($filtri, false, $sqlParams);

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_ACQUE, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlParams' => $sqlParams,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_ACQUE]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        $this->setSortParameter($ita_grid01);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridAcque($ita_grid01))) {
            Out::setFocus("", $this->nameForm . '_CODVIA');
        } else {
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_ACQUE);
        }
        Out::show($this->nameForm . '_divRicerca');
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeRelazUbicChiave($index, $sqlParams);
    }

    private function decodVia($codValue, $codField, $desValue, $desField, $searchBut = false) {
        $row = cwbLib::decodificaLookup("cwbBtaVie", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODVIA", $desValue, $desField, 'DESVIA', "returnFromBtaVie", $_POST['id'], $searchBut);

        if ($row) {
            Out::valore($desField, $row['TOPONIMO'] . ' ' . $row['DESVIA']);
        }
    }
   
    protected function elaboraGridUe($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsUe($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsUe($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CATEGORIA'] = $Result_tab[$key]['CATEG_ALFA'] . $Result_tab[$key]['CATEG_NUM'];
        }
        return $Result_tab;
    }

    protected function elaboraGridAn($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsAn($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsAn($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['FAMIGLIA'] = $Result_tab[$key]['FAMIGLIA_T'] . ' ' . $Result_tab[$key]['FAMIGLIA'];
            $Result_tab[$key]['NOMINATIVO'] = "<b>" . $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'] . "</b>";
            $Result_tab[$key]['CV_UBIC'] = "<font color='red'>" . $Result_tab[$key]['NUMCIV'] . "</font>";
            $Result_tab[$key]['DATANASCITA'] = $Result_tab[$key]['GIORNO'] . '/' . $Result_tab[$key]['MESE'] . '/' .
                    $Result_tab[$key]['ANNO'];
        }
        return $Result_tab;
    }

    protected function elaboraGridTribu($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsTribu($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsTribu($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGSOGG_tribu'] = $Result_tab[$key]['PROGSOGG'];
            $Result_tab[$key]['NUMCIV_tribu'] = $Result_tab[$key]['NUMCIV'];
            $Result_tab[$key]['NOMINA'] = "<b>" . $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'] . "</b>";
        }
        return $Result_tab;
    }

    protected function elaboraGridICI($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsICI($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsICI($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['NOMINATIV'] = "<b>" . $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'] . "</b>";
            $Result_tab[$key]['NUMCIVICO'] = "<font color='red'>" . $Result_tab[$key]['NUMCIV'] . "</font>";
        }
        return $Result_tab;
    }

    protected function elaboraGridServi($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsServi($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsServi($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['NOMINATIVOSDI'] = "<b>" . $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'] . "</b>";
            $Result_tab[$key]['RICHIEDENTE'] = $Result_tab[$key]['COGNOME2'] . ' ' . $Result_tab[$key]['NOME2'];
            $Result_tab[$key]['NUMCIVSDI'] = "<font color='red'>" . $Result_tab[$key]['NUMCIV'] . "</font>";
            $Result_tab[$key]['DATAINIZSDI'] = $Result_tab[$key]['DATAINIZ'];
            $Result_tab[$key]['DATAFINESDI'] = $Result_tab[$key]['DATAFINE'];
            $Result_tab[$key]['DTNASC'] = $Result_tab[$key]['GIORNO'] . '/' . $Result_tab[$key]['MESE'] . '/' .
                    $Result_tab[$key]['ANNO'];
        }
        return $Result_tab;
    }

    protected function elaboraGridAcque($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsAcque($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsAcque($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['NOMINATIVOACQUE'] = "<b>" . $Result_tab[$key]['COGNOME'] . ' ' . $Result_tab[$key]['NOME'] . "</b>";
            $Result_tab[$key]['UTENZA'] = $Result_tab[$key]['PROGSOGG'] . $Result_tab[$key]['PROGUTEN'];
            $Result_tab[$key]['NUMCIVACQUE'] = "<font color='red'>" . $Result_tab[$key]['NUMCIV'] . "</font>";
        }
        return $Result_tab;
    }

    private function initComboTipImmobile() {
        // Combo Tipo Immobile
        Out::select($this->nameForm . '_TIPOIMMOBILE', 1, "0", 1, "__TUTTI__");
        Out::select($this->nameForm . '_TIPOIMMOBILE', 1, "1", 0, "1-Terreni agricoli");
        Out::select($this->nameForm . '_TIPOIMMOBILE', 1, "2", 0, "2-Aree fabbricabili");
        Out::select($this->nameForm . '_TIPOIMMOBILE', 1, "3", 0, "3-Fabbricati");
        Out::select($this->nameForm . '_TIPOIMMOBILE', 1, "4", 0, "4-Fabbricati gr.D");
    }

}

