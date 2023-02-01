<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function cwbBgeWfparams() {
    $cwbBgeWfparams = new cwbBgeWfparams();
    $cwbBgeWfparams->parseEvent();
    return;
}

class cwbBgeWfparams extends cwbBpaGenTab {

    public $praLib;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBgeWfparams';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    protected function initVars() {
        $this->GRID_NAME = 'gridBgeWfparams';

        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 12;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGE();

        $this->praLib = new praLib();

        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;

        $this->openDetailFlag = true;

        $this->errorOnEmpty = false;

        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESPRO]', '');
        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESWFPRO]', '');
        Out::valore($this->nameForm . '_BGE_WFPARAMS[AREA]', '');
    }

    protected function postNuovo() {
        Out::setFocus("", $this->nameForm . '_BGE_WFPARAMS[CONTESTO_APP]');
    }

    protected function postApriForm() {
        $this->initContestoApp();
        Out::setFocus("", $this->nameForm . '_CONTESTO_APP');
    }

    protected function postAltraRicerca() {
        $this->pulisciCampi();
        Out::setFocus("", $this->nameForm . '_CONTESTO_APP');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGE_WFPARAMS[CONTESTO_APP]');
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CONTESTO_APP'] != '') {
            $this->gridFilters['CONTESTO_APP'] = $this->formData['CONTESTO_APP'];
        }
        if ($_POST['GESPRO'] != '') {
            $this->gridFilters['GESPRO'] = $this->formData['GESPRO'];
        }
        if ($_POST['GESWFPRO'] != '') {
            $this->gridFilters['GESWFPRO'] = $this->formData['GESWFPRO'];
        }
        if ($_POST['GESCODPROC'] != '') {
            $this->gridFilters['GESCODPROC'] = $this->formData['GESCODPROC'];
        }
        if ($_POST['ITEEVT'] != '') {
            $this->gridFilters['ITEEVT'] = $this->formData['ITEEVT'];
        }
        if ($_POST['AREA'] != '') {
            $this->gridFilters['AREA'] = $this->formData['AREA'];
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BGE_WFPARAMS[CONTESTO_APP]');

        $this->setAreaFromDB($this->CURRENT_RECORD['AREA'], 'DETTAGLIO');

        $codice1 = $this->CURRENT_RECORD['GESPRO'];
        $codice1 = str_repeat("0", 6 - strlen(trim($codice1))) . trim($codice1);
        $anapra_rec1 = $this->praLib->GetAnapra($codice1);
        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESPRO]', $anapra_rec1['PRANUM']);
        Out::valore($this->nameForm . '_DES_GESPRO', $anapra_rec1['PRADES__1']);

        $codice2 = $this->CURRENT_RECORD['GESWFPRO'];
        $codice2 = str_repeat("0", 6 - strlen(trim($codice2))) . trim($codice2);
        $anapra_rec2 = $this->praLib->GetAnapra($codice2);
        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESWFPRO]', $anapra_rec2['PRANUM']);
        Out::valore($this->nameForm . '_DES_GESWFPRO', $anapra_rec2['PRADES__1']);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CONTESTO_APP'] = trim($this->formData[$this->nameForm . '_CONTESTO_APP']);
        $filtri['AREA'] = trim($this->formData[$this->nameForm . '_AREA']);

        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeWfparams($filtri, false, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgeWfparamsChiave($index, $sqlParams);
    }

    protected function customParseEvent() {
        parent::customParseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGE_WFPARAMS[GESPRO]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_BGE_WFPARAMS[GESPRO]');
                        break;
                    case $this->nameForm . '_BGE_WFPARAMS[GESWFPRO]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_BGE_WFPARAMS[GESWFPRO]');
                        break;

                    case $this->nameForm . '_AREA_butt':
                        $externalFilters = array();
                        cwbLib::apriFinestraRicerca('cwbBorMaster', $this->nameForm, 'returnMaster', 'RICERCA', true, $externalFilters, $this->nameFormOrig, '', $postData);
                        break;

                    case $this->nameForm . '_BGE_WFPARAMS[AREA]_butt':
                        $externalFilters = array();
                        cwbLib::apriFinestraRicerca('cwbBorMaster', $this->nameForm, 'returnMaster', 'DETTAGLIO', true, $externalFilters, $this->nameFormOrig, '', $postData);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGE_WFPARAMS[GESPRO]':
                        $codice = $_POST[$this->nameForm . '_BGE_WFPARAMS']['GESPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anapra_rec = $this->praLib->GetAnapra($codice);
                        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESPRO]', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DES_GESPRO', $anapra_rec['PRADES__1']);
                        break;

                    case $this->nameForm . '_BGE_WFPARAMS[GESWFPRO]':
                        $codice = $_POST[$this->nameForm . '_BGE_WFPARAMS']['GESWFPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anapra_rec = $this->praLib->GetAnapra($codice);
                        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESWFPRO]', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DES_GESWFPRO', $anapra_rec['PRADES__1']);
                        break;

                    case $this->nameForm . '_AREA':
                        $this->setAreaFromDB(strtoupper($_POST[$this->nameForm . '_AREA']), 'RICERCA');
                        break;

                    case $this->nameForm . '_BGE_WFPARAMS[AREA]':
                        $this->setAreaFromDB(strtoupper($_POST[$this->nameForm . '_BGE_WFPARAMS']['AREA']), 'DETTAGLIO');
                        break;
                }
                break;
            case 'returnAnapra':
                $anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ID_ANAPRA'], 'rowid');
                switch ($_POST['retid']) {
                    case $this->nameForm . '_BGE_WFPARAMS[GESPRO]':
                        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESPRO]', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DES_GESPRO', $anapra_rec['PRADES__1']);
                        break;
                    case $this->nameForm . '_BGE_WFPARAMS[GESWFPRO]':
                        Out::valore($this->nameForm . '_BGE_WFPARAMS[GESWFPRO]', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_DES_GESWFPRO', $anapra_rec['PRADES__1']);
                        break;
                }
                break;

            case 'returnMaster':
                $this->setArea($this->formData['returnData'], $_POST['id']);
                break;
        }
    }

    private function setAreaFromDB($cod, $target) {
        $data = null;
        if (!empty($cod)) {
            $dettaglioFilters = array('CODAREAMA' => $cod);
            $data = $this->libDB->leggiGeneric('BOR_MASTER', $dettaglioFilters, false);
        }
        $this->setArea($data, $target);
    }

    private function setArea($data, $target) {
        switch ($target) {
            case 'RICERCA':
                Out::valore($this->nameForm . '_AREA', $data['CODAREAMA']);
                Out::valore($this->nameForm . '_DAREA', $data['DESAREA']);
                break;
            case 'DETTAGLIO':
                Out::valore($this->nameForm . '_BGE_WFPARAMS[AREA]', $data['CODAREAMA']);
                Out::valore($this->nameForm . '_DES_AREA', $data['DESAREA']);
                break;
        }
    }
    
    private function initContestoApp() {
        // Ricerca
        Out::select($this->nameForm . '_CONTESTO_APP', 1, "FAT_CICLO_PAS", 1, "FAT_CICLO_PAS (Fatture ciclo passivo)");
        Out::select($this->nameForm . '_CONTESTO_APP', 1, "DOMANDA_SERV_SOC", 0, "DOMANDA_SERV_SOC (Domanda servizi sociali)");
        
        // Gestione
        Out::select($this->nameForm . '_BGE_WFPARAMS[CONTESTO_APP]', 1, "FAT_CICLO_PAS", 1, "FAT_CICLO_PAS (Fatture ciclo passivo)");
        Out::select($this->nameForm . '_BGE_WFPARAMS[CONTESTO_APP]', 1, "DOMANDA_SERV_SOC", 0, "DOMANDA_SERV_SOC (Domanda servizi sociali)");
        
        // Grid
        $options = array();
        $options[''] = '---TUTTI---';
        $options['FAT_CICLO_PAS'] = 'FAT_CICLO_PAS';
        $options['DOMANDA_SERV_SOC'] = 'DOMANDA_SERV_SOC';        

        // Imposta filtri griglia
        Out::gridSetColumnFilterSelect($this->nameForm, $this->GRID_NAME, 'CONTESTO_APP', $options);
    }
    
}
