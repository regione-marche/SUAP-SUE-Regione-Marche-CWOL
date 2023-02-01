<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';

function cwbBgeAgidScadenze() {
    $cwbBgeAgidScadenze = new cwbBgeAgidScadenze();
    $cwbBgeAgidScadenze->parseEvent();
    return;
}

class cwbBgeAgidScadenze extends cwbBpaGenTab {

    private $proginv;
    private $gridValues;

    function initVars() {
        $this->GRID_NAME = 'gridBgeAgidScadenze';
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->libDB_BTA_SOGG = new cwbLibDB_BTA_SOGG();
        $this->proginv = cwbParGen::getFormSessionVar($this->nameForm, 'proginv');
        $this->gridValues = cwbParGen::getFormSessionVar($this->nameForm, 'gridValues');
        $this->CURRENT_RECORD = cwbParGen::getFormSessionVar($this->nameForm, 'CURRENT_RECORD');
        $this->elencaAutoAudit = true;
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'proginv', $this->proginv);
            cwbParGen::setFormSessionVar($this->nameForm, 'gridValues', $this->gridValues);
            cwbParGen::setFormSessionVar($this->nameForm, 'CURRENT_RECORD', $this->CURRENT_RECORD);
        }
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Scartate':
                        $this->visualizzaScartate();
                        break;
                    case $this->nameForm . '_Cancellate':
                        $this->visualizzaCancellate();
                        break;
                    case $this->nameForm . '_RipristinaScad':
                        $this->ripristinaScadenza();
                        break;
                    case $this->nameForm . '_btnVerificaIUV':
                        $this->ricercaPosizioneDaIUV();
                        break;
                    case $this->nameForm . '_btnStampaBollettino':
                        $this->stampaBollettino();
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        $this->initComboStato();
        $this->initComboTipo();
        $this->initComboProv();
        $this->initComboTipIns();

        // Doppio clic da tab scadenze della Console Gestione Nodo... mostro dettaglio
        if ($this->externalParams) {
            $scadenza = $this->libDB->leggiBgeAgidScadenzeChiave($this->externalParams);
            $this->showHideNote($scadenza['NUMSOSP'], $scadenza['STATO']);
            $this->setVisControlli(true, false, false, false, false, false, false, false, false, false);
            $soggetto = $this->libDB_BTA_SOGG->leggiBtaSoggChiave($scadenza['PROGSOGG']);
            Out::valore($this->nameForm . '_NOMINATIVO', $soggetto['RAGSOC']);
            Out::valori($scadenza, $this->nameForm . '_' . $this->TABLE_NAME);
        }
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    private function ricercaPosizioneDaIUV() {
        $pagoPa = new itaPagoPa(itaPagoPa::EFILL_TYPE);
        $result = $pagoPa->ricercaPosizioneDaIUV($this->CURRENT_RECORD['IUV']);
        Out::msgInfo('Info Posizione', print_r($result, true));
    }

    private function stampaBollettino() {
        $itaPDFUtils = new itaPDFUtils();
        $outputPath = $itaPDFUtils->getRisultato();
        $pagoPa = new itaPagoPa(itaPagoPa::EFILL_TYPE);       
        $result = $pagoPa->generaBollettinoDaIUV($this->CURRENT_RECORD['IUV']);
        if ($result) {
            $random = rand(111, 999999) + rand(111, 999999);
            $result = base64_decode($result);
            $FilePath = itaLib::getUploadPath() . "/" . $random . ".pdf";
            file_put_contents($FilePath, $result);
            $this->innestaDocViewer($FilePath);
        } else {
            Out::msgStop("Errore", "Errore reperimento pdf popolato");
            return;
        }
    }

    private function innestaDocViewer($pathDest) {
        if ($pathDest) {
            $alias = 'cwbDocViewer' . '_' . time();
            $formObj = cwbLib::innestaForm('cwbDocViewer', $this->nameForm . '_divGestione', false, $alias);

            if ($formObj) {
                $formObj->setEvent('openform');
                $formObj->setSingleMode(true);
                $formObj->setFiles(array(0 => array('NOME' => $pathDest)));
                $formObj->parseEvent();
            }
        }
    }

    protected function postDettaglio($index) {
        $this->decodSogg($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGSOGG']);
        Out::valore($this->nameForm . '_STATO_hidden', $this->CURRENT_RECORD['STATO']);
        if (intval($this->CURRENT_RECORD['IUV']) <> ' ') {
            Out::show($this->nameForm . '_btnVerificaIUV');
            Out::show($this->nameForm . '_btnStampaBollettino');
        } else {
            Out::hide($this->nameForm . '_btnVerificaIUV');
            Out::hide($this->nameForm . '_btnStampaBollettino');
        }
        $this->showHideNote($this->CURRENT_RECORD['NUMSOSP'], $this->CURRENT_RECORD['STATO']);
        if ((intval($this->CURRENT_RECORD['STATO']) != 3) && (intval($this->CURRENT_RECORD['STATO']) != 6)) {
            Out::html($this->nameForm . '_spanSospesa', '');
        }
    }

    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        $this->initComboStato();
        $this->initComboTipo();
        $this->initComboProv();
        $this->initComboTipIns();
//        if ($filtri['PROGINV']) {
//            $this->proginv = $filtri['PROGINV'];
//        }

        if ($this->externalParams['ROW_ID']) {
            list($proginv, $annoemi, $numemi, $idbol_sere) = explode('|', $this->externalParams['ROW_ID']);
        } elseif (!$filtri['ANNOEMI'] && !$filtri['NUMEMI'] && !$filtri['IDBOL_SERE']) {
            $annoemi = trim($this->formData[$this->nameForm . '_ANNOEMI']);
            $numemi = trim($this->formData[$this->nameForm . '_NUMEMI']);
            $idbol_sere = trim($this->formData[$this->nameForm . '_IDBOL_SERE']);
        }
        $filtri['PROGKEYTAB'] = trim($this->formData[$this->nameForm . '_PROGKEYTAB']);
        $filtri['ANNORIF'] = trim($this->formData[$this->nameForm . '_ANNORIF']);
        $filtri['STATO'] = trim($this->formData[$this->nameForm . '_STATO']);
        $filtri['IUV'] = trim($this->formData[$this->nameForm . '_IUV']);
        $filtri['CODTIPSCAD'] = trim($this->formData[$this->nameForm . '_CODTIPSCAD']);
        $filtri['SUBTIPSCAD'] = trim($this->formData[$this->nameForm . '_SUBTIPSCAD']);
        $filtri['PROGCITYSC'] = trim($this->formData[$this->nameForm . '_PROGCITYSC']);
        $filtri['NUMDOC'] = trim($this->formData[$this->nameForm . '_NUMDOC']);
        $filtri['PROGSOGG'] = trim($this->formData[$this->nameForm . '_PROGSOGG']);
        $filtri['CODFISCALE'] = trim($this->formData[$this->nameForm . '_CODFISCALE']);
        $filtri['PROGINV'] = $proginv ? $proginv : $filtri['PROGINV'];
        $filtri['ANNOEMI'] = $annoemi ? $annoemi : $filtri['ANNOEMI'];
        $filtri['NUMEMI'] = $numemi ? $numemi : $filtri['NUMEMI'];
        $filtri['IDBOL_SERE'] = $idbol_sere ? $idbol_sere : $filtri['IDBOL_SERE'];
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidScadenze($filtri, true, $sqlParams, true);
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODTIPSCAD'] != '') {
            $this->gridFilters['CODTIPSCAD'] = $this->formData['CODTIPSCAD'];
        }
        if ($_POST['SUBTIPSCAD'] != '') {
            $this->gridFilters['SUBTIPSCAD'] = $this->formData['SUBTIPSCAD'];
        }
        if ($_POST['PROGCITYSC'] != '') {
            $this->gridFilters['PROGCITYSC'] = $this->formData['PROGCITYSC'];
        }
        if ($_POST['NUMDOC'] != '') {
            $this->gridFilters['NUMDOC'] = $this->formData['NUMDOC'];
        }
        if ($_POST['PROGSOGG'] != '') {
            $this->gridFilters['PROGSOGG'] = $this->formData['PROGSOGG'];
        }
        if ($_POST['IUV'] != '') {
            $this->gridFilters['IUV'] = $this->formData['IUV'];
        }
        if ($_POST['CODFISCALE'] != '') {
            $this->gridFilters['CODFISCALE'] = $this->formData['CODFISCALE'];
        }
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->initComboStato();
        $this->initComboTipo();
        $this->initComboProv();
        $this->initComboTipIns();
        $this->SQL = $this->libDB->getSqlLeggiBgeAgidScadenzeChiave($index, $sqlParams);
    }

    private function visualizzaScartate() {
        try {
            // mostra scadenze scartate
            $ita_grid01 = $this->initializeTableScartate();
            if (!$ita_grid01) {
                Out::msgInfo('Attenzione!', "Non ci sono scadenze Sospese!");
                $this->apriForm();
                return;
            }
            Out::show($this->nameForm . '_divRisultato');
            if ($this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01), $this->GRID_NAME)) {
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    private function visualizzaCancellate() {
        try {
            // mostra scadenze cancellate
            $ita_grid01 = $this->initializeTableCancellate();
            if (!$ita_grid01) {
                Out::msgInfo('Attenzione!', "Non ci sono scadenze Cancellate!");
                $this->apriForm();
                return;
            }
            Out::show($this->nameForm . '_divRisultato');
            if ($this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01), $this->GRID_NAME)) {
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    private function ripristinaScadenza() {
        try {
            $scadenza = $this->libDB->leggiBgeAgidScadenzeChiave($this->CURRENT_RECORD['PROGKEYTAB']);
            if (intval($scadenza['STATO']) === 3 || intval($scadenza['STATO']) === 13) {
                $scadenza['STATO'] = 1;
            } elseif (intval($scadenza['STATO']) === 6) {
                $scadenza['STATO'] = 5;
            }
            $this->aggiornaScadenza($scadenza);
        } catch (Exception $exc) {
            Out::msgInfo("Attenzione!", $exc);
            return;
        }
        Out::msgInfo('Ok', 'Scadenza Ripristinata con Successo!');
        $this->visualizzaScartate();
    }

    private function aggiornaScadenza($scadenza) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('bge_agid_scadenze'), true, false);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord('bge_agid_scadenze', $scadenza);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, 'cwbBgeAgidScadenze', $scadenza);
        $modelService->updateRecord(ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), ''), 'bge_agid_scadenze', $modelServiceData->getData(), $recordInfo);
    }

    private function initializeTableScartate() {
        // imposto filtro per STATO = 3 or STATO = 6
        $filters['STATO_or'] = array(1 => 3, 2 => 6);
        $records = $this->libDB->leggiBgeAgidScadenze($filters);
        if ($records) {
            $this->helper->setGridName($this->GRID_NAME);
            return $this->helper->initializeTableArray($records);
        } else {
            return false;
        }
    }

    private function initializeTableCancellate() {
        $records = $this->libDB->leggiBgeAgidStoscade();
        if ($records) {
            $this->helper->setGridName($this->GRID_NAME);
            return $this->helper->initializeTableArray($records);
        } else {
            return false;
        }
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_tab[$key]['TIPOPENDEN']) {
                case 0:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza unica scadenza';
                    break;
                case 1:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza di testata o rata unica';
                    break;
                case 2:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza di dettaglio di rata';
                    break;
            }

            switch ($Result_tab[$key]['STATO']) {
                case 0:
                    $Result_tab[$key]['STATO'] = 'Ripristinata';
                    break;
                case 1:
                    $Result_tab[$key]['STATO'] = 'Creata';
                    break;
                case 2:
                    $Result_tab[$key]['STATO'] = 'Inviata';
                    break;
                case 3:
                    $Result_tab[$key]['STATO'] = 'Sospesa';
                    break;
                case 4:
                    $Result_tab[$key]['STATO'] = 'Pubblicata in attesa dello IUV';
                    break;
                case 5:
                    $Result_tab[$key]['STATO'] = 'Pubblicata con IUV';
                    break;
                case 6:
                    $Result_tab[$key]['STATO'] = 'In cancellazione';
                    break;
                case 7:
                    $Result_tab[$key]['STATO'] = 'Cancellata';
                    break;
                case 8:
                    $Result_tab[$key]['STATO'] = 'In sostituzione';
                    break;
                case 9:
                    $Result_tab[$key]['STATO'] = 'Sostituita';
                    break;
                case 10:
                    $Result_tab[$key]['STATO'] = 'Pagata';
                    break;
                case 11:
                    $Result_tab[$key]['STATO'] = 'Pagata ma non Riconciliata';
                    break;
                case 12:
                    $Result_tab[$key]['STATO'] = 'Riconciliata';
                    break;
                case 13:
                    $Result_tab[$key]['STATO'] = 'Cancellazione FALLITA';
                    break;
            }
        }
        return $Result_tab;
    }

    private function initComboTipo() {
        Out::html($this->nameForm . '_BGE_AGID_SCADENZE[TIPOPENDEN]', ' ');
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[TIPOPENDEN]', 1, 0, 1, "Pendenza unica scadenza");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[TIPOPENDEN]', 1, 1, 0, "Pendenza di testata con più rate");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[TIPOPENDEN]', 1, 2, 0, "Pendenza di dettaglio rata");
        Out::disableField($this->nameForm . '_BGE_AGID_SCADENZE[TIPOPENDEN]');
    }

    private function initComboProv() {
        Out::html($this->nameForm . '_BGE_AGID_SCADENZE[PROVENIENZA]', ' ');
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[PROVENIENZA]', 1, 1, 0, "Back Office");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[PROVENIENZA]', 1, 2, 0, "Front Office");
        Out::disableField($this->nameForm . '_BGE_AGID_SCADENZE[PROVENIENZA]');
    }

    private function initComboTipIns() {
        Out::html($this->nameForm . '_BGE_AGID_SCADENZE[TIP_INS]', ' ');
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[TIP_INS]', 1, 1, 0, "Massivo");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[TIP_INS]', 1, 0, 0, "Puntuale (da ricalcolo)");
        Out::disableField($this->nameForm . '_BGE_AGID_SCADENZE[TIP_INS]');
    }

    private function initComboStato() {
        Out::html($this->nameForm . '_STATO', '');
        Out::select($this->nameForm . '_STATO', 1, 0, 1, " ");
        Out::select($this->nameForm . '_STATO', 1, 1, 0, "Creata");
        Out::select($this->nameForm . '_STATO', 1, 2, 0, "Inviata");
        Out::select($this->nameForm . '_STATO', 1, 3, 0, "Sospesa");
        Out::select($this->nameForm . '_STATO', 1, 4, 0, "Pubblicata in Attesa di IUV");
        Out::select($this->nameForm . '_STATO', 1, 5, 0, "Pubblicata con IUV");
        Out::select($this->nameForm . '_STATO', 1, 6, 0, "In Cancellazione");
        Out::select($this->nameForm . '_STATO', 1, 7, 0, "Cancellata");
        Out::select($this->nameForm . '_STATO', 1, 8, 0, "In Sostituzione");
        Out::select($this->nameForm . '_STATO', 1, 9, 0, "Sostituita");
        Out::select($this->nameForm . '_STATO', 1, 10, 0, "Pagata");
        Out::select($this->nameForm . '_STATO', 1, 11, 0, "Pagata ma non riconciliata");
        Out::select($this->nameForm . '_STATO', 1, 12, 0, "Riconciliata");
        Out::select($this->nameForm . '_STATO', 1, 13, 0, "Cancellazione FALLITA");

        Out::html($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', '');
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 0, 1, " ");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 1, 0, "Creata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 2, 0, "Inviata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 3, 0, "Sospesa");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 4, 0, "Pubblicata in Attesa di IUV");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 5, 0, "Pubblicata con IUV");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 6, 0, "In Cancellazione");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 7, 0, "Cancellata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 8, 0, "In Sostituzione");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 9, 0, "Sostituita");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 10, 0, "Pagata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 11, 0, "Pagata ma non riconciliata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 12, 0, "Riconciliata");
        Out::select($this->nameForm . '_BGE_AGID_SCADENZE[STATO]', 1, 13, 0, "Cancellazione FALLITA");
        Out::disableField($this->nameForm . '_BGE_AGID_SCADENZE[STATO]');
    }

    private function decodSogg($codValue) {
        $row = $this->libDB_BTA_SOGG->leggiBtaSoggChiave($codValue);
        Out::valore($this->nameForm . '_NOMINATIVO', $row['COGNOME'] . ' ' . $row['NOME']);
    }

    private function showHideNote($numsosp, $stato) {
        if (intval($stato) !== 11) {
            Out::hide($this->nameForm . '_divNoteNonRicon');
        } else {
            Out::show($this->nameForm . '_divNoteNonRicon');
        }

        if (intval($stato) === 3) {
            Out::html($this->nameForm . '_spanSospesa', "<font color='red'>Attenzione! La scadenza risulta <b>Sospesa.</b>"
                    . " Verificare le Note di Sospensione. E' possibile ripristinare la scadenza in modo da poterla pubblicare nuovamente!</font>");
        } elseif (intval($stato) === 6) {
            Out::html($this->nameForm . '_spanSospesa', "<font color='red'>Attenzione! La scadenza risulta <b>In Cancellazione.</b>"
                    . " E' possibile ripristinare la scadenza in modo da poterla cancellare nuovamente!</font>");
        }

        if (intval($stato) === 6 || intval($stato) === 3) {
            Out::show($this->nameForm . '_divNoteSosp');
        } else {
            Out::hide($this->nameForm . '_divNoteSosp');
        }
    }

}

?>