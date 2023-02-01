<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdTipdoc() {
    $cwbBgdTipdoc = new cwbBgdTipdoc();
    $cwbBgdTipdoc->parseEvent();
    return;
}

class cwbBgdTipdoc extends cwbBpaGenTab {

    const GRID_NAME_METADATI = 'gridBgdMetdoc';
    const GRID_NAME_METADATI_ASP = 'gridBgdMetdocAspetti';

    function initVars() {
        $this->GRID_NAME = 'gridBgdTipdoc';
        $this->GRID_NAME_ASPETTI = 'gridBgdAsptdc';
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BGD();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case "subGridRowExpanded":
                $this->expandRowAspetti();

                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BGD_TIPDOC[CODNAZI]', 'readonly', '1');
        Out::css($this->nameForm . '_BGD_TIPDOC[CODNAZI]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BGD_TIPDOC[CODNAZI]');
    }

    protected function postApriForm() {
        $this->initComboUnitDocu();
        $this->initComboInsDocu();
        Out::attributo($this->nameForm . "_FLAG_ASPECT", "checked", "0", "checked");
        Out::attributo($this->nameForm . "_FLAG_DIS", "checked", "0", "checked");
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Torna');
        Out::setFocus("", $this->nameForm . '_DESNAZI');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGD_TIPDOC[CODNAZI]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        $this->caricaAspetti();
        $this->caricaMetadati();
        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_Torna');
        Out::setFocus('', $this->nameForm . '_BGD_TIPDOC[IDTIPDOC]');
    }

    private function expandRowAspetti() {
        $idaspect = $_POST['rowid'];
        $this->caricaTabellaMetDoc($idaspect, self::GRID_NAME_METADATI_ASP . '_' . $idaspect);
    }

    private function caricaTabellaMetDoc($idtipdoc, $gridName) {
        Out::delAllRow($this->nameForm . '_' . $gridName); //pulisco grid nel caso non ci fossero Aspetti.

        $filtri['IDTIPDOC'] = trim($idtipdoc);
        $result_tab = $this->libDB->leggiBgdMetdoc($filtri);
        if ($result_tab) {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($this->nameForm);
            $helper->setGridName($gridName);

            $ita_grid01 = $helper->initializeTableArray($result_tab);
            $ita_grid01->getDataPage('json');
            TableView::enableEvents($this->nameForm . '_' . $gridName);
        }
    }

    private function caricaMetadati() {
        $this->caricaTabellaMetDoc($this->CURRENT_RECORD['IDTIPDOC'], self::GRID_NAME_METADATI);
    }

    private function caricaAspetti() {
        $filtri['IDTIPDOC'] = trim($this->CURRENT_RECORD['IDTIPDOC']);
        $result_tab = $this->libDB->leggiBgdAsptdc($filtri);
        if (!$result_tab) {
            Out::delAllRow($this->nameForm . '_' . $this->GRID_NAME_ASPETTI); //pulisco grid nel caso non ci fossero Aspetti.
        } else {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($this->nameForm);
            $helper->setGridName($this->GRID_NAME_ASPETTI);

            $ita_grid01 = $helper->initializeTableArray($result_tab);
            $ita_grid01->getDataPage('json');
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_ASPETTI);
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDTIPDOC_formatted'] != '') {
            $this->gridFilters['IDTIPDOC'] = $this->formData['IDTIPDOC_formatted'];
        }
        if ($_POST['DESCRIZIONE'] != '') {
            $this->gridFilters['DESCRIZIONE'] = $this->formData['DESCRIZIONE'];
        }
        if ($_POST['ALIAS'] != '') {
            $this->gridFilters['ALIAS'] = $this->formData['ALIAS'];
        }
        if ($_POST['AREA_ORIG'] != '') {
            $this->gridFilters['AREA_ORIG'] = $this->formData['AREA_ORIG'];
        }
        if ($_POST['MODULO_ORIG'] != '') {
            $this->gridFilters['MODULO_ORIG'] = $this->formData['MODULO_ORIG'];
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDTIPDOC'] = trim($this->formData[$this->nameForm . '_IDTIPDOC']);
        $filtri['DESCRIZIONE'] = trim($this->formData[$this->nameForm . '_DESCRIZIONE']);
        $filtri['ALIAS'] = trim($this->formData[$this->nameForm . '_ALIAS']);
        $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        $filtri['FLAG_ASPECT'] = trim($this->formData[$this->nameForm . '_FLAG_ASPECT']);
        $filtri['AREA_ORIG'] = trim($this->formData[$this->nameForm . '_AREA_ORIG']);
        $filtri['MODULO_ORIG'] = trim($this->formData[$this->nameForm . '_MODULO_ORIG']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgdTipdoc($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgdTipdocChiave($index, $sqlParams);
    }

    private function initComboUnitDocu() {
        // Elemento unità documentaria
        Out::select($this->nameForm . '_BGD_TIPDOC[ELEMEN_UD]', 1, "0", 1, "0=Escluso");
        Out::select($this->nameForm . '_BGD_TIPDOC[ELEMEN_UD]', 1, "1", 0, "1=Documento principale");
        Out::select($this->nameForm . '_BGD_TIPDOC[ELEMEN_UD]', 1, "2", 0, "2=Allegato");
        Out::select($this->nameForm . '_BGD_TIPDOC[ELEMEN_UD]', 1, "3", 0, "3=Annesso");
        Out::select($this->nameForm . '_BGD_TIPDOC[ELEMEN_UD]', 1, "4", 0, "4=Annotazione");
    }

    private function initComboInsDocu() {
        // Inserimento manuale altri allegati
        Out::select($this->nameForm . '_BGD_TIPDOC[FLAG_TPINS]', 1, "0", 1, "Inserimento manuale non previsto");
        Out::select($this->nameForm . '_BGD_TIPDOC[FLAG_TPINS]', 1, "1", 0, "Note mediante Tx.control");
        Out::select($this->nameForm . '_BGD_TIPDOC[FLAG_TPINS]', 1, "2", 0, "Importazione documento esterno");
    }

    protected function elaboraGridAspetti($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsAspetti($Result_tab_tmp);
        return $Result_tab;
    }

    protected function elaboraRecordsAspetti($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDASPECT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDASPECT']);
        }
        return $Result_tab;
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDTIPDOC_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDTIPDOC']);
        }
        return $Result_tab;
    }

}

?>