<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';

function cwbBgeAgidRisco() {
    $cwbBgeAgidRisco = new cwbBgeAgidRisco();
    $cwbBgeAgidRisco->parseEvent();
    return;
}

class cwbBgeAgidRisco extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBgeAgidRisco';
        $this->skipAuth = telrue;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case "cellSelect":
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        switch ($_POST['colName']) {
                            case 'SCADENZA':
                                list($progkeytab, $idscadenza, $iuv) = explode('|', $_POST['rowid']);
                                cwblib::apriFinestraDettaglio('cwbBgeAgidScadenze', $this->nameForm, '', $_POST['id'], '', $idscadenza);
                                break;

                            case 'RT':
                                list($progkeytab, $idscadenza, $iuv) = explode('|', $_POST['rowid']);
                                $this->visualizzaRT($iuv);
                                break;
                        }
                }
                break;
        }
    }

    protected function sqlDettaglio($index, &$sqlParams) {
        $this->initComboProvpagam();
        Out::disableField($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]');
        list($progkeytab, $idscadenza, $iuv) = explode('|', $index);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidRiscoChiave($progkeytab, $sqlParams);
    }

    protected function postApriForm() {
        $this->initComboProvpagam();
        TableView::disableTableEvents($this->nameForm . '_' . $this->GRID_NAME);
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri = array();
        $filtri['PROGRIC'] = $this->externalParams['PROGRIC'];
        $filtri['PROVPAGAM'] = trim($this->formData[$this->nameForm . '_PROVPAGAM']);
        $filtri['IUV'] = trim($this->formData[$this->nameForm . '_IUV']);
        $filtri['DATAPAG_da'] = trim($this->formData[$this->nameForm . '_DATAPAG_da']);
        $filtri['DATAPAG_a'] = trim($this->formData[$this->nameForm . '_DATAPAG_a']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidRiscoStatoScadenza($filtri, true, $sqlParams);
    }

    public function postElenca() {
        if ($this->externalParams) {
            $this->setVisControlli(false, true, false, false, false, false, false, false, false, false);
        }
    }

    protected function elaboraRecords($Result_tab) {
        $path_sca = ITA_BASE_PATH . '/apps/CityBase/resources/deadline-24x24.png';
        $path_ricevuta = ITA_BASE_PATH . '/apps/CityBase/resources/mail-24x24.png';

        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            $Result_tab[$key]['IMPPAGATO'] = cwbLibOmnis::toOmnisDecimal($Result_tab[$key]['IMPPAGATO'], 2);
            $Result_tab[$key]['SCADENZA'] = cwbLibHtml::formatDataGridIcon('', $path_sca);
            $Result_tab[$key]['RT'] = cwbLibHtml::formatDataGridIcon('', $path_ricevuta);

            switch ($Result_rec['STATO']) {
                case 10:
                    $Result_tab[$key]['STATO'] = 'Pagata';
                    break;
                case 11:
                    $Result_tab[$key]['STATO'] = '<font color="red">Pagata ma non Riconciliata</font>';
                    break;
                case 12:
                    $Result_tab[$key]['STATO'] = '<font color="green">Riconciliata</font>';
                    break;
            }
            switch ($Result_rec['PROVPAGAM']) {
                case 1:
                    $Result_tab[$key]['PROVPAGAM'] = 'Cityportal';
                    break;
                case 2:
                    $Result_tab[$key]['PROVPAGAM'] = 'Nodo';
                    break;
                case 3:
                    $Result_tab[$key]['PROVPAGAM'] = 'Extranodo';
                    break;
                case 4:
                    $Result_tab[$key]['PROVPAGAM'] = 'Sconosciuto';
                    break;
            }
        }
        return $Result_tab;
    }

    private function visualizzaRT($iuv) {
        $pagoPa = new itaPagoPa(itaPagoPa::EFILL_TYPE);
        $result = $pagoPa->recuperaRicevutaPagamento($iuv);

        if ($result['Esito'] == 'Ok') {
            $nomeFile = time() . ".xml";
            $filename = itaLib::getUploadPath() . '/' . time() . ".xml";
            file_put_contents($filename, $result['Ricevuta']);
            $corpo = file_get_contents($filename);
            cwbLib::downloadDocument($nomeFile, $corpo,true);
            unlink($filename);
        } elseif ($result['Esito'] == 'Ko'){
            Out::msgInfo('Attenzione', 'Impossibile recuperare la Ricevuta di Pagamento!');
        }
    }

    private function initComboProvpagam() {
        Out::html($this->nameForm . '_PROVPAGAM', ' ');
        Out::select($this->nameForm . '_PROVPAGAM', 1, 0, 1, "");
        Out::select($this->nameForm . '_PROVPAGAM', 1, 1, 0, "Cityportal");
        Out::select($this->nameForm . '_PROVPAGAM', 1, 2, 0, "Nodo");
        Out::select($this->nameForm . '_PROVPAGAM', 1, 3, 0, "Extranodo");
        Out::select($this->nameForm . '_PROVPAGAM', 1, 4, 0, "Sconosciuto");

        Out::html($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', ' ');
        Out::select($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', 1, 0, 1, "");
        Out::select($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', 1, 1, 0, "Cityportal");
        Out::select($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', 1, 2, 0, "Nodo");
        Out::select($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', 1, 3, 0, "Extranodo");
        Out::select($this->nameForm . '_BGE_AGID_RISCO[PROVPAGAM]', 1, 4, 0, "Sconosciuto");
    }

}

?>