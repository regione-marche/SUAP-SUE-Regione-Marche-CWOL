<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaServrend() {
    $cwbBtaServrend = new cwbBtaServrend();
    $cwbBtaServrend->parseEvent();
    return;
}

class cwbBtaServrend extends cwbBpaGenTab {

    private $daRendCont;

    const TABELLA_SERVRENDPPA = 'BTA_SERVRENDPPA';
    const TABELLA_SERVRENDDET = 'BTA_SERVRENDDET';

    function initVars() {
        $this->GRID_NAME = 'gridBtaServrend';
        $this->skipAuth = true;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_BGE = new cwbLibDB_BGE();
        $this->libDB_BWE = new cwbLibDB_BWE();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
        $this->addDescribeRelation(self::TABELLA_SERVRENDPPA, array('PROGKEYTAB' => 'IDSERVREND'), itaModelServiceData::RELATION_TYPE_ONE_TO_ONE);
        $this->addDescribeRelation(self::TABELLA_SERVRENDDET, array('PROGKEYTAB' => 'IDSERVREND'), itaModelServiceData::RELATION_TYPE_ONE_TO_MANY);
        if ($_POST['daRendCont'] !== null) {// parametro da menù
            $this->daRendCont = $_POST['daRendCont'];
        } else {
            $this->daRendCont = cwbParGen::getFormSessionVar($this->nameForm, '_daRendCont');
        }
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
        cwbParGen::setFormSessionVar($this->nameForm, '_daRendCont', $this->daRendCont);
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_SERVREND[CODTIPSCAD]_butt':
                        cwbLib::apriFinestraRicerca('cwbBweTippen', $this->nameForm, 'returnFromBweTippen', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_BTA_SERVREND[IDBOL_SERE]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaEmi', $this->nameForm, 'returnFromBtaEmi', $_POST['id'], true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_Configura':
                        $this->configura();
                        break;
                    case 'close-portlet':
                        // ritorno quando chiamata da Gestione Nodo
                        if ($this->returnModel) {
                            cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, null, $this->nameForm);
                        }
                        break;
                }
                break;
            case 'returnFromBweTippen':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SERVREND[CODTIPSCAD]_butt':
                        Out::valore($this->nameForm . '_BTA_SERVREND[CODTIPSCAD]', $this->formData['returnData']['CODTIPSCAD']);
                        Out::valore($this->nameForm . '_BTA_SERVREND[SUBTIPSCAD]', $this->formData['returnData']['SUBTIPSCAD']);
                        Out::valore($this->nameForm . '_DESCRIZ', $this->formData['returnData']['DESCRIZ']);
                        break;
                }
                break;
            case 'returnFromBtaEmi':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SERVREND[IDBOL_SERE]_butt':
                        Out::valore($this->nameForm . '_BTA_SERVREND[ANNOEMI]', $this->formData['returnData']['ANNOEMI']);
                        Out::valore($this->nameForm . '_BTA_SERVREND[NUMEMI]', $this->formData['returnData']['NUMEMI']);
                        Out::valore($this->nameForm . '_BTA_SERVREND[IDBOL_SERE]', $this->formData['returnData']['IDBOL_SERE']);
                        Out::valore($this->nameForm . '_DES_GE60', $this->formData['returnData']['DES_GE60']);
                        break;
                }
                break;


            case "cellSelect":
                list($progkeytab, $annoemi, $numemi, $idbol_sere) = explode('|', $_POST['rowid']);
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridBtaServrend':
                        switch ($_POST['colName']) {
                            case 'DETTAGLIO':
                                $externalParams['IDSERVREND'] = $progkeytab;
                                cwbLib::apriFinestraDettaglio('cwbBtaServrenddet', $this->nameForm, 'returnFromBtaServrenddet', $_POST['id'], $externalParams, $externalParams);
                                break;
                            case 'SCADENZE':
                                $externalParams['ANNOEMI'] = $annoemi;
                                $externalParams['NUMEMI'] = $numemi;
                                $externalParams['IDBOL_SERE'] = $idbol_sere;
                                cwbLib::apriFinestraDettaglio('cwbBgeAgidScadenze', $this->nameForm, 'returnFromBgeAgidScadenze', $_POST['id'], $externalParams, $externalParams);
                                break;
                        }
                        break;
                }
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postNuovo() {
        $this->pulisciCampi();
        $progkeytab = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BTA_SERVREND');
        Out::valore($this->nameForm . '_BTA_SERVREND[PROGKEYTAB]', $progkeytab);
    }

    protected function postApriForm() {
        $this->initComboInterm();
        $this->disFields();
    }

    protected function postAggiungi() {
        $this->pulisciCampi();
    }

    protected function disFields() {
        Out::attributo($this->nameForm . '_BTA_SERVREND[CODTIPSCAD]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_SERVREND[CODTIPSCAD]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_SERVREND[IDBOL_SERE]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_SERVREND[IDBOL_SERE]', 'background-color', '#FFFFE0');
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_CODSERVIZIO', '');
        Out::valore($this->nameForm . '_TIPORIFCRED', '');
        Out::valore($this->nameForm . '_DESCRIZ', '');
        Out::valore($this->nameForm . '_DES_GE60', '');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function preAggiungi() {
        $this->addInsertOperation(self::TABELLA_SERVRENDPPA);
    }

    protected function getDataRelationView($tableName, $alias = null) {
        $key = $alias ? $alias : $tableName;
        $results = array();

        switch ($key) {
            case 'BTA_SERVRENDPPA':
                $this->formDataToCurrentRecord();
                $results = array();
                $results[] = $this->valorizzaBtaServrendppa($this->CURRENT_RECORD['PROGKEYTAB']);
                break;
        }

        return $results;
    }

    //effettua il caricamento dal database di tutte le relazioni
    protected function getDataRelation() {
        $valueArray = array();
        $valueArray[self::TABELLA_SERVRENDPPA] = $this->libDB->leggiBtaServrendppa(array('IDSERVREND' => $this->CURRENT_RECORD['PROGKEYTAB']));
        $valueArray[self::TABELLA_SERVRENDDET] = $this->libDB->leggiBtaServrenddet(array('IDSERVREND' => $this->CURRENT_RECORD['PROGKEYTAB']));
        return $valueArray;
    }

    private function valorizzaBtaServrendppa($progkeytab) {
        $servrendppa['IDSERVREND'] = $progkeytab;
        $servrendppa['INTERMEDIARIO'] = $_POST[$this->nameForm . '_INTERMEDIARIO'];
        $servrendppa['CODSERVIZIO'] = $_POST[$this->nameForm . '_CODSERVIZIO'];
        $servrendppa['TIPORIFCRED'] = $_POST[$this->nameForm . '_TIPORIFCRED'];

        return $servrendppa;
    }

    protected function preApriForm() {
        if ($this->daRendCont) {
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'STATO');
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'CODSERVIZIO');
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'SCADENZE');
            Out::hide($this->nameForm . '_divPPA');
            // TODO SOSTITUIRE CON Out::gridForceResize
            $this->gridForceResize($this->nameForm . '_' . $this->GRID_NAME);
        }
    }

    // TODO SOSTITUIRE CON Out::gridForceResize
    private function gridForceResize($tableId) {
        Out::codice("$('#$tableId').trigger('resize');");
    }

    protected function preAggiorna() {
        $this->formDataToCurrentRecord();
        $this->addUpdateOperation(self::TABELLA_SERVRENDPPA, array('IDSERVREND' => $this->CURRENT_RECORD['PROGKEYTAB']));
    }

    protected function postDettaglio($index) {
        list($progkeytab, $annoemi, $numemi, $idbol_sere) = explode('|', $index);
        $filtri['IDSERVREND'] = $progkeytab;
        $servrendppa = $this->libDB->leggiBtaServrendppa($filtri, false);
        $emissione = $this->decodEmissione($this->CURRENT_RECORD['IDBOL_SERE'], $this->CURRENT_RECORD['ANNOEMI'], $this->CURRENT_RECORD['NUMEMI']);
        $tipoPendenza = $this->decodTipoPendenza($this->CURRENT_RECORD['CODTIPSCAD'], $this->CURRENT_RECORD['SUBTIPSCAD']);
        Out::valore($this->nameForm . '_DESCRIZ', $tipoPendenza['DESCRIZ']);
        Out::valore($this->nameForm . '_DES_GE60', $emissione['DES_GE60']);

        Out::valore($this->nameForm . '_CODSERVIZIO', $servrendppa['CODSERVIZIO']);
        Out::valore($this->nameForm . '_INTERMEDIARIO', $servrendppa['INTERMEDIARIO']);
        Out::valore($this->nameForm . '_TIPORIFCRED', $servrendppa['TIPORIFCRED']);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['PROGKEYTAB'] = trim($this->formData[$this->nameForm . '_PROGKEYTAB']);
        $filtri['CODTIPSCAD'] = trim($this->formData[$this->nameForm . '_CODTIPSCAD']);
        $filtri['SUBTIPSCAD'] = trim($this->formData[$this->nameForm . '_SUBTIPSCAD']);
        $filtri['ANNOEMI'] = trim($this->formData[$this->nameForm . '_ANNOEMI']);
        $this->compilaFiltri($filtri);
        if ($this->daRendCont) {
            // se arrivo da rendicontazione contabile, cambia la query
            $this->SQL = $this->libDB->getSqlLeggiEmissioneRendicontazioneContabile($filtri, false, $sqlParams);
        } else {
            $this->SQL = $this->libDB->getSqlLeggiBtaServrend($filtri, false, $sqlParams);
        }
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        list($progkeytab, $annoemi, $numemi, $idbol_sere) = explode('|', $index);
        if ($this->daRendCont) {
            $this->SQL = $this->libDB->getSqlLeggiBtaServrendRendicontazioneChiave($progkeytab, $sqlParams);
        } else {
            $this->SQL = $this->libDB->getSqlLeggiBtaServrendChiave($progkeytab, $sqlParams);
        }
    }

    protected function elaboraRecords($Result_tab) {
        $path_dett = ITA_BASE_PATH . '/apps/CityBase/resources/details-24x24.png';
        $path_scad = ITA_BASE_PATH . '/apps/CityBase/resources/deadline-24x24.png';

        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['DETTAGLIO'] = cwbLibHtml::formatDataGridIcon('', $path_dett);
            $Result_tab[$key]['SCADENZE'] = cwbLibHtml::formatDataGridIcon('', $path_scad);

            $filtri['ANNOEMI'] = $Result_rec['ANNOEMI'];
            $filtri['NUMEMI'] = $Result_rec['NUMEMI'];
            $filtri['IDBOL_SERE'] = $Result_rec['IDBOL_SERE'];
            $situazEmissioni = $this->libDB_BGE->leggiBgeAgidScadenzePerEmissione($filtri, false);
            $dataInvio = $this->libDB_BGE->leggiBgeAgidInviiDatainvio($filtri, false);

            if ($situazEmissioni) {
                $statoEmissione = "Totale Scadenze:" . $situazEmissioni['TOTALE'] . "<br>";

                if ($situazEmissioni['PUBBLICATO'] > 0) {
                    $statoEmissione .= "<font color='green'>Pubblicate:" . $situazEmissioni['PUBBLICATO'] . "</font>";
                    $dataInvio ? $statoEmissione .= ", <u>inviate il " . date('d-m-Y', strtotime($dataInvio['DATAINVIO'])) . "</u>" : "";
                } else {
                    $statoEmissione .= "Inviate:" . $situazEmissioni['INVIATO'];
                    $dataInvio ? $statoEmissione .= ", <u>il " . date('d-m-Y', strtotime($dataInvio['DATAINVIO'])) . "</u>" : "";
                }

                if ($situazEmissioni['SOSPESO'] > 0) {
                    $statoEmissione .= " - " . "<font color='red'>Sospese:" . $situazEmissioni['SOSPESO'] . "</font>";
                }

                if ($situazEmissioni['RICONCILIATO'] > 0) {
                    $statoEmissione .= " - " . "<font color='green'>Riconciliate:" . $situazEmissioni['RICONCILIATO'] . "</font>";
                }

                if ($situazEmissioni['RENDICONTATO'] > 0) {
                    $statoEmissione .= " - " . "<font color='red'>Rendicontato:" . $situazEmissioni['RENDICONTATO'] . "</font>";
                }

                if ($situazEmissioni['CANCELLATO'] > 0) {
                    $statoEmissione .= " - " . "Cancellate:" . $situazEmissioni['CANCELLATO'];
                }

                $Result_tab[$key]['STATO'] = $statoEmissione;
            } else {
                $scadenzeDaPubbl = $this->libDB_BWE->leggiBwePendenScadenze($filtri);
                if ($scadenzeDaPubbl) {
                    foreach ($scadenzeDaPubbl as $data) {
                        $progcitysc = $data['PROGCITYSC'];
                        if (isset($result[$progcitysc])) {
                            $result[$progcitysc][] = $data;
                        } else {
                            $result[$progcitysc] = array($data);
                        }
                    }
                    $Result_tab[$key]['STATO'] = '<u>Sono presenti ' . count($result) . ' Scadenze da Pubblicare</u>';
                } else {
                    $Result_tab[$key]['STATO'] = '<u>Emissione non pronta per essere pubblicata.<br>'
                            . 'Verificare la tabella BWE_PENDEN e il flag pubblicabile!</u>';
                }
            }
            $interm = $this->decodInterm($Result_rec['INTERMEDIARIO']);
            $Result_tab[$key]['INTERMEDIARIO'] = $interm['DESCRIZIONE'];
        }
        return $Result_tab;
    }

    private function decodEmissione($idbol_sere, $annoemi, $numemi) {
        $filtri['IDBOL_SERE'] = $idbol_sere;
        $filtri['ANNOEMI'] = $annoemi;
        $filtri['NUMEMI'] = $numemi;
        return $this->libDB->leggiBtaEmi($filtri, false);
    }

    private function decodTipoPendenza($codtipscad, $subtipscad) {
        $filtri['CODTIPSCAD'] = $codtipscad;
        $filtri['SUBTIPSCAD'] = $subtipscad;
        return $this->libDB_BWE->leggiBweTippen($filtri, false);
    }

    private function decodInterm($intermediario) {
        $filtri['INTERMEDIARIO'] = $intermediario;
        return $this->libDB_BGE->leggiBgeAgidInterm($filtri, false);
    }

    private function initComboInterm() {
        // Azzera combo
        Out::html($this->nameForm . '_INTERMEDIARIO', '');

        // Carica lista intermediari
        $intermediari = $this->libDB_BGE->leggiBgeAgidInterm(array());

        // Popola combo in funzione dei dati caricati da db
        foreach ($intermediari as $intermediario) {
            Out::select($this->nameForm . '_INTERMEDIARIO', 1, $intermediario['INTERMEDIARIO'], 0, $intermediario['DESCRIZIONE']);
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['ANNOEMI'] != '') {
            $this->gridFilters['ANNOEMI'] = $this->formData['ANNOEMI'];
        }
        if ($_POST['NUMEMI'] != '') {
            $this->gridFilters['NUMEMI'] = $this->formData['NUMEMI'];
        }
        if ($_POST['IDBOL_SERE'] != '') {
            $this->gridFilters['IDBOL_SERE'] = $this->formData['IDBOL_SERE'];
        }
        if ($_POST['DES_GE60'] != '') {
            $this->gridFilters['DES_GE60'] = $this->formData['DES_GE60'];
        }
    }

    private function configura() {
        $objModel = cwbLib::apriFinestra('cwbBtaConfiguraEmissione', $this->nameForm, '', $_POST['id'], null);
        $objModel->parseEvent();
    }

    function getDaRendCont() {
        return $this->daRendCont;
    }

    function setDaRendCont($daRendCont) {
        $this->daRendCont = $daRendCont;
    }

}

?>