<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtRendicontazioneContabileHelper.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbEventiBat.class.php';
include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

function cwbBtaRendConf() {
    $cwbBtaRendConf = new cwbBtaRendConf();
    $cwbBtaRendConf->parseEvent();
    return;
}

class cwbBtaRendConf extends cwbBpaGenTab {

    function initVars() {
        $this->skipAuth = true;
        $this->GRID_NAME = 'gridBtaRendConf';
        $this->libDB = new cwbLibDB_BTA();
        $this->searchOpenElenco = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AllineaRendicontazioneAsync':
                        $this->allineaRendicontazioneAsync();
                        break;
                    case $this->nameForm . '_AllineaRendicontazioneSync':
                        $this->allineaRendicontazioneSync();
                        break;
                    case $this->nameForm . '_SvuotaRendicontazione':
                        $this->svuotaRendicontazione();
                        break;
                    case $this->nameForm . '_test':
                        $this->test();
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
        Out::hide($this->nameForm . "_divId");
    }

    protected function postApriForm() {
        if (cwbParGen::getSessionVar('nomeUtente') == 'italsoft') {
            Out::show($this->nameForm . '_test');
        } else {
            Out::hide($this->nameForm . '_test');
        }
        $this->initComboIntervallo();
        $this->initComboEventoCarico();
        $this->initComboEventoRiscossione();
        Out::setFocus("", $this->nameForm . '_DATAINIZREND');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DATAINIZREND');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_REND_CONF[DATAINIZREND]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        $this->initComboIntervallo();
    }

    protected function postDettaglio($index) {
        Out::show($this->nameForm . "_divId");
        Out::setFocus('', $this->nameForm . '_BTA_REND_CONF[DATAINIZREND]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['DATAINIZREND'] = trim($this->formData[$this->nameForm . '_DATAINIZREND']);
        $filtri['INTERVALLO'] = trim($this->formData[$this->nameForm . '_INTERVALLO']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaRendConf($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaRendConfChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['ID_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['ID']);

            switch ($Result_tab[$key]['INTERVALLO']) {
                case 1:
                    $Result_tab[$key]['INTERVALLO'] = 'Giornaliero';
                    break;
                case 2:
                    $Result_tab[$key]['INTERVALLO'] = 'Settimanale';
                    break;
                case 3:
                    $Result_tab[$key]['INTERVALLO'] = 'Mensile';
                    break;
                case 4:
                    $Result_tab[$key]['INTERVALLO'] = 'Trimestrale';
                    break;
                case 5:
                    $Result_tab[$key]['INTERVALLO'] = 'Annuale';
                    break;
            }

            switch ($Result_tab[$key]['EVENTO_CARICO']) {
                case 1:
                    $Result_tab[$key]['EVENTO_CARICO_format'] = 'Per Data Stampa';
                    break;
                case 2:
                    $Result_tab[$key]['EVENTO_CARICO_format'] = 'Per Data Notifica';
                    break;
            }

            switch ($Result_tab[$key]['EVENTO_RISCOSSIONE']) {
                case 1:
                    $Result_tab[$key]['EVENTO_RISCOSSIONE_format'] = 'Per Data Sistema';
                    break;
                case 2:
                    $Result_tab[$key]['EVENTO_RISCOSSIONE_format'] = 'Per Data Contabile';
                    break;
            }
        }
        return $Result_tab;
    }

    private function initComboIntervallo() {
        // Azzera combo
        Out::html($this->nameForm . '_INTERVALLO', '');
        Out::html($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', '');

        Out::select($this->nameForm . '_INTERVALLO', 1, "0", 1, "Tutti");
        Out::select($this->nameForm . '_INTERVALLO', 1, "1", 0, "Giornaliero");
        Out::select($this->nameForm . '_INTERVALLO', 1, "2", 0, "Settimanale");
        Out::select($this->nameForm . '_INTERVALLO', 1, "3", 0, "Mensile");
        Out::select($this->nameForm . '_INTERVALLO', 1, "4", 0, "Trimestrale");
        Out::select($this->nameForm . '_INTERVALLO', 1, "5", 0, "Annuale");

        Out::select($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', 1, "1", 0, "Giornaliero");
        Out::select($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', 1, "2", 0, "Settimanale");
        Out::select($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', 1, "3", 1, "Mensile");
        Out::select($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', 1, "4", 0, "Trimestrale");
        Out::select($this->nameForm . '_BTA_REND_CONF[INTERVALLO]', 1, "5", 0, "Annuale");
    }

    private function initComboEventoCarico() {
        // Azzera combo
        Out::html($this->nameForm . '_BTA_REND_CONF[EVENTO_CARICO]', '');

        Out::select($this->nameForm . '_BTA_REND_CONF[EVENTO_CARICO]', 1, "1", 1, "Per Data Stampa");
        Out::select($this->nameForm . '_BTA_REND_CONF[EVENTO_CARICO]', 1, "2", 0, "Per Data Notifica");
    }

    private function initComboEventoRiscossione() {
        // Azzera combo
        Out::html($this->nameForm . '_BTA_REND_CONF[EVENTO_RISCOSSIONE]', '');

        Out::select($this->nameForm . '_BTA_REND_CONF[EVENTO_RISCOSSIONE]', 1, "1", 1, "Per Data Sistema");
        Out::select($this->nameForm . '_BTA_REND_CONF[EVENTO_RISCOSSIONE]', 1, "2", 0, "Per Data Contabile");
    }

    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna) {
        parent::setVisControlli($divGestione, $divRisultato, $divRicerca, $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna);
        $divRisultato ? Out::show($this->nameForm . '_SvuotaRendicontazione') : Out::hide($this->nameForm . '_SvuotaRendicontazione');
        $divRisultato ? Out::show($this->nameForm . '_AllineaRendicontazione') : Out::hide($this->nameForm . '_AllineaRendicontazione');
    }

    private function allineaRendicontazioneAsync() {
        $model = cwbLib::apriFinestra('cwbEseguiAsync', $this->nameForm, "", "");
        $idElaborazione = cwbEventiBat::nextIdElaborazione();
        $devLib = new devLib();
        $utente = $devLib->getEnv_config('PARAMS_CLI_ASYNC', 'codice', 'UTENTE_CLI_ASYNC', false);
        $password = $devLib->getEnv_config('PARAMS_CLI_ASYNC', 'codice', 'PASSWORD_CLI_ASYNC', false);
        $arguments = array(
            $utente['CONFIG'],
            $password['CONFIG'],
            cwbParGen::getSessionVar('ditta'),
            $idElaborazione
        );
        $model->setCliArguments($arguments);
        $model->setCliName("allineaRendicontazioni");
        $model->setIdElaborazione($idElaborazione);
        $model->parseEvent();
    }

    private function allineaRendicontazioneSync() {
        cwtRendicontazioneContabileHelper::checkDatiConvalida();
        cwtRendicontazioneContabileHelper::checkDatiRangeFissi();
//        cwtRendicontazioneContabileHelper::checkDatiAnnuale();
        cwtRendicontazioneContabileHelper::checkDatiConvalidaXls();
//        cwtRendicontazioneContabileHelper::checkDatiAnnualeXls();
//        cwtRendicontazioneContabileHelper::checkDatiRangeFissiXls();
    }

    private function svuotaRendicontazione() {
        cwtRendicontazioneContabileHelper::cancellaBtaRendss(null, null, null);
        Out::msgInfo("Operazione Eseguita", "Tabella bta_rendss svuotata");
    }

    private function test() {
        $model = cwbLib::apriFinestra('cwtTestRendicontazione', $this->nameForm, "", "");
        $model->parseEvent();
    }

    protected function postAggiorna($esito = null) {
        // svuoto la cache con il campo id_rendsel/id_rendcont
        $cache = CacheFactory::newCache();
        $cache->delete(cwtRendicontazioneContabileHelper::CACHE_KEY);
    }

}

?>