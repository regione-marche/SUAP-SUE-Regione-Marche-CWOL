<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

function cwbBorRespo() {
    $cwbBorRespo = new cwbBorRespo();
    $cwbBorRespo->parseEvent();
    return;
}

class cwbBorRespo extends cwbBpaGenTab {

    private $componentBorUtentiModel;
    private $componentBorUtentiAlias;

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorRespo';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        if (itaHooks::isActive('citywareHook.php')) {
            $this->GRID_NAME = 'gridBorRespo';
            $this->AUTOR_MODULO = 'BOR';
            $this->AUTOR_NUMERO = 4;
            $this->libDB = new cwbLibDB_BOR();
            $this->searchOpenElenco = true;
            $this->elencaAutoAudit = true;
            $this->elencaAutoFlagDis = true;

            $this->componentBorUtentiAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBorUtentiAlias');
            if ($this->componentBorUtentiAlias == '') {
                $this->componentBorUtentiAlias = $this->nameForm . '_BorUtenti_' . time() . rand(0, 1000);
                itaLib::openInner('cwbComponentBorUtenti', '', true, $this->nameForm . '_BorUtentiContainer', '', '', $this->componentBorUtentiAlias);

                $this->componentBorUtentiModel = itaFrontController::getInstance('cwbComponentBorUtenti', $this->componentBorUtentiAlias);
                $this->componentBorUtentiModel->setReturnData(array('UTELOG' => $this->nameForm . '_BOR_RESPO[CODUTE_RESP]'));
            } else {
                $this->componentBorUtentiModel = itaFrontController::getInstance('cwbComponentBorUtenti', $this->componentBorUtentiAlias);
            }
        } else {
            $this->GRID_NAME = 'gridBorRespo';
            $this->TABLE_NAME = 'CEP_RESPO';
            $this->skipAuth = true;
            $this->searchOpenElenco = true;
            $this->libDB = new cwbLibDB_GENERIC('CEP');
            $this->elencaAutoAudit = true;
            $this->elencaAutoFlagDis = true;
            $this->errorOnEmpty = false;
        }
    }

    protected function preDestruct() {
        if ($this->close != true) {
            if (itaHooks::isActive('citywareHook.php')) {
                cwbParGen::setFormSessionVar($this->nameForm, 'componentBorUtentiAlias', $this->componentBorUtentiAlias);
            }
        }
    }

    protected function customParseEvent() {
        if (itaHooks::isActive('citywareHook.php')) {
            $this->componentBorUtentiModel->parseEvent();
        }
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGRESPO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGRESPO'], $this->nameForm . '_PROGRESPO');
                        break;
                }
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Gruppo':
                        cwbLib::apriFinestraDettaglio('cwbBorGrpret', $this->nameForm, 'returnFromBorGrpret', $_POST['id'], true);
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if (!empty($_POST['IDRESPO_formatted'])) {
            $this->gridFilters['IDRESPO'] = $this->formData['IDRESPO_formatted'];
        }
        if (!empty($_POST['PROGRESPO'])) {
            $this->gridFilters['PROGRESPO'] = $this->formData['PROGRESPO'];
        }
        if (!empty($_POST['NOMERES'])) {
            $this->gridFilters['NOMERES'] = $this->formData['NOMERES'];
        }
        if (!empty($_POST['CODUTE'])) {
            $this->gridFilters['CODUTE'] = $this->formData['CODUTE'];
        }
        if (!empty($_POST['FLAG_DIS'])) {
            $this->gridFilters['FLAG_DIS'] = $this->formData['FLAG_DIS'] - 1;
        }
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_Gruppo');
        $progr = cwbLibCalcoli::trovaProgressivo("PROGRESPO", "BOR_RESPO");
        Out::valore($this->nameForm . '_BOR_RESPO[PROGRESPO]', $progr);
        Out::valore($this->nameForm . '_BOR_RESPO[PROGENTE]', 1); // TODO: per adesso lo setto fisso, ma poi bisognerà valorizzarlo in base all'ente selezionato.
        Out::setFocus("", $this->nameForm . '_BOR_RESPO[NOMERES]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_RESPO[NOMERES]');
    }

    protected function postDettaglio($index) {
        //$this->decodUtenti($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODUTE_RESP'], ($this->nameForm . '_BOR_RESPO[CODUTE_RESP]'), ($this->nameForm . '_NOMEUTE_decod'));
        Out::show($this->nameForm . '_Gruppo');
        Out::setFocus('', $this->nameForm . '_BOR_RESPO[NOMERES]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BOR_RESPO[NOMERES]', trim($this->CURRENT_RECORD['NOMERES']));
        Out::valore($this->nameForm . '_BOR_RESPO[PROGRESPO]', trim($this->CURRENT_RECORD['IDRESPO']));

        if (itaHooks::isActive('citywareHook.php')) {
            $this->componentBorUtentiModel->updateUtente($this->CURRENT_RECORD['CODUTE_RESP']);
        }
    }

    protected function postApriForm() {
        Out::hide($this->nameForm . '_Gruppo');
        Out::hide($this->nameForm . '_BOR_RESPO[IDRESPO]');
        Out::hide($this->nameForm . '_BOR_RESPO[IDRESPO]_lbl');
        Out::hide($this->nameForm . '_BOR_RESPO[PROGRESPO]');
        Out::hide($this->nameForm . '_BOR_RESPO[PROGRESPO]_lbl');
        Out::hide($this->nameForm . '_BOR_RESPO[PROGENTE]');
        Out::hide($this->nameForm . '_BOR_RESPO[PROGENTE]_lbl');
        Out::setFocus("", $this->nameForm . '_NOMERES');
    }

    protected function postAltraRicerca() {
        Out::hide($this->nameForm . '_Gruppo');
        Out::setFocus("", $this->nameForm . '_NOMERES');
    }

    public function postElenca() {
        Out::show($this->nameForm . '_Gruppo');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if (empty($filtri['PROGRESPO'])) {
            $filtri['PROGRESPO'] = trim($this->formData[$this->nameForm . '_PROGRESPO']);
        }
        if (empty($filtri['NOMERES'])) {
            $filtri['NOMERES'] = trim($this->formData[$this->nameForm . '_NOMERES']);
        }
        if (empty($filtri['FLAG_DIS'])) {
            $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorRespo($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorRespoChiave($index, $sqlParams);
    }

    public function postPulisciCampi() {
        Out::valore($this->nameForm . '_NOMEUTE_decod', '');
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDRESPO_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDRESPO']);
        }
        return $Result_tab;
    }

    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
//        Out::valore('gs_IDRESPO_formatted','');
//        Out::valore('gs_PROGRESPO','');
//        Out::valore('gs_NOMERES','');
//        Out::valore('gs_CODUTE','');
//        Out::valore('gs_FLAG_DIS','');
    }

}
