<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggSearchUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggSearchUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TCA.class.php';

function cwbBtaSoggSearch() {
    $cwbBtaSoggSearch = new cwbBtaSoggSearch();
    $cwbBtaSoggSearch->parseEvent();
    return;
}

class cwbBtaSoggSearch extends itaFrontControllerCW {

    private $nameFormContainer;
    private $aliasNameFormContainer;
    private $modalitaComponente; // 1 ricerca su bta_sogg, 2 su tca_sogg
    private $escludiOmnis;
    private $chiaveOnsuggest;

    const BLOCK_CHANGE_KEY = "C.Fisc.";
    const NAMEFORM_SOGGETTI = "cwbBtaSogg";

    protected function postItaFrontControllerCostruct() {
        $this->nameFormContainer = cwbParGen::getFormSessionVar($this->nameForm, '_nameFormContainer');
        $this->aliasNameFormContainer = cwbParGen::getFormSessionVar($this->nameForm, '_aliasNameFormContainer');
        $this->modalitaComponente = cwbParGen::getFormSessionVar($this->nameForm, '_modalitaComponente');
        $this->escludiOmnis = cwbParGen::getFormSessionVar($this->nameForm, '_escludiOmnis');
        $this->chiaveOnsuggest = cwbParGen::getFormSessionVar($this->nameForm, '_chiaveOnsuggest');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_nameFormContainer', $this->nameFormContainer);
            cwbParGen::setFormSessionVar($this->nameForm, '_aliasNameFormContainer', $this->aliasNameFormContainer);
            cwbParGen::setFormSessionVar($this->nameForm, '_modalitaComponente', $this->modalitaComponente);
            cwbParGen::setFormSessionVar($this->nameForm, '_escludiOmnis', $this->escludiOmnis);
            cwbParGen::setFormSessionVar($this->nameForm, '_chiaveOnsuggest', $this->chiaveOnsuggest);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->apriForm();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->close = true;
                        cwbParGen::removeFormSessionVars($this->nameForm);
                        break;
                    case $this->nameForm . '_VisualizzaDettaglio':
                        $this->visualizzaDettaglio();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SOGGSEARCH_MATRICOLA':
                        $this->changeData($_POST[$this->nameForm . '_SOGGSEARCH_MATRICOLA'], null, null);
                        break;
                    case $this->nameForm . '_SOGGSEARCH_NOMINATIVO':
                        $this->changeData($_POST[$this->nameForm . '_SOGGSEARCH_MATRICOLA'], null, $_POST[$this->nameForm . '_SOGGSEARCH_NOMINATIVO']);
                        break;
                    case $this->nameForm . '_SOGGSEARCH_CFPIVA':
                        $this->changeData($_POST[$this->nameForm . '_SOGGSEARCH_MATRICOLA'], $_POST[$this->nameForm . '_SOGGSEARCH_CFPIVA'], null);
                        break;
                }

                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SOGGSEARCH_NOMINATIVO':
                        $this->suggestNominativo();
                        break;
                    case $this->nameForm . '_SOGGSEARCH_CFPIVA':
                        $this->suggestCfPiva();
                        break;
                    case $this->nameForm . '_SOGGSEARCH_MATRICOLA':
                        $this->suggestMatricola();
                        break;
                }
                break;
            case 'onSuggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SOGGSEARCH_NOMINATIVO':
                    case $this->nameForm . '_SOGGSEARCH_CFPIVA':
                    case $this->nameForm . '_SOGGSEARCH_MATRICOLA':
                        $this->chiaveOnsuggest = $_POST['suggestCampi'][$this->nameForm . '_CHIAVE_RICERCA'];

                        if (!$_POST['suggestCampi'][$this->nameForm . '_CHIAVE_RICERCA']) {
                            $this->cleanValori();
                            return;
                        }

                        $this->popolaCampiSoggetto();
                        $this->lanciaEventoComponentePadre();

                        break;
                }
                break;
        }
    }

    private function apriForm() {
        if ($this->modalitaComponente === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            Out::show($this->nameForm . '_SOGGSEARCH_MATRICOLA_field');
        } else if ($this->modalitaComponente === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            Out::hide($this->nameForm . '_SOGGSEARCH_MATRICOLA_field');
        }
    }

    private function suggestNominativo() {
        $nominativo = itaSuggest::getQuery();
        if ($this->modalitaComponente === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $this->suggestBtaSogg($nominativo, null, null);
        } else if ($this->modalitaComponente === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            $this->suggestTcaSogg($nominativo, null, null);
        }
    }

    private function suggestCfPiva() {
        $cfpiva = itaSuggest::getQuery();
        if ($this->modalitaComponente === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $this->suggestBtaSogg(null, $cfpiva, null);
        } else if ($this->modalitaComponente === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            $this->suggestTcaSogg(null, $cfpiva, null);
        }
    }

    private function suggestMatricola() {
        $progsogg = itaSuggest::getQuery();
        if ($this->modalitaComponente === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $this->suggestBtaSogg(null, null, $progsogg);
        } else if ($this->modalitaComponente === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            $this->suggestTcaSogg(null, null, $progsogg);
        }
    }

    private function suggestBtaSogg($nominativo, $cfpiva, $progsogg) {
        $libDB = new cwbLibDB_BTA_SOGG();

        if ($nominativo) {
            $nominativo = str_replace(" ", "", $nominativo);
        }

        $filtri = array(
            'NOME_RIC' => cwbLibCalcoli::calcNomeRic($nominativo),
            'CODFISCALEORPARTIVA' => $cfpiva,
            'PROGSOGG' => $progsogg
        );

        $soggetti = $libDB->leggiBtaSogg($filtri);

        if (!$soggetti) {
            $this->cleanValori();
            itaSuggest::addSuggest("Nessun Soggetto Trovato", null);
        } else {
            foreach ($soggetti as $soggetto) {
                $param = array($this->nameForm . '_CHIAVE_RICERCA' => $soggetto['PROGSOGG']);

                if ($soggetto['TIPOPERS'] === 'F') {
                    if ($soggetto['SESSO'] === 'M') {
                        $x = 'o';
                    } else {
                        $x = 'a';
                    }
                    $dtnascita = $soggetto['GIORNO'] . "/" . $soggetto['MESE'] . "/" . $soggetto['ANNO'];
                    $descrizione = "C.Fisc. " . $soggetto['CODFISCALE'] . ' - ' . $soggetto['COGNOME'] . ' ' . $soggetto['NOME'] . ' - ' . 'Nat' . $x . ' il ' . $dtnascita;
                } else {
                    // TODO che altri dati mettere ?
                    $descrizione = "P.IVA " . $soggetto['PARTIVA'] . ' - ' . $soggetto['COGNOME'] . ' ' . $soggetto['NOME'];
                }

                itaSuggest::addSuggest($descrizione, $param);
            }
        }

        itaSuggest::sendSuggest();
    }

    private function suggestTcaSogg($nominativo, $cf) {
        $libDB = new cwtLibDB_TCA();

        $filtri = array(
            'NOMERAGCAT' => $nominativo,
            'CODFISCALE' => $cf,
        );

        $soggetti = $libDB->leggiTcaSogg($filtri);

        if (!$soggetti) {
            $this->cleanValori();
        } else {
            foreach ($soggetti as $soggetto) {
                // TODO ASPETTARE AGGIUNTA SEQUENCE SU DB
                $chiave = $soggetto['CODADMIN'] . '|' . $soggetto['SEZIONE'] . '|' . $soggetto['IDSOGGETTO'] . '|' . $soggetto['TIPOSOGG'];
                $param = array($this->nameForm . '_CHIAVE_RICERCA' => $chiave);

                if ($soggetto['SESSO'] === 'F') {
                    $x = 'a';
                } else {
                    $x = 'o';
                }
                $dtnascita = substr($soggetto['DTNASC'], 0, 10);
                $descrizione = "C.Fisc. " . $soggetto['CODFISCALE'] . ' - ' . $soggetto['NOMERAGCAT'] . ' - ' . 'Nat' . $x . ' il ' . $dtnascita;

                itaSuggest::addSuggest($descrizione, $param);
            }
        }

        itaSuggest::sendSuggest();
    }

    private function popolaCampiSoggetto() {
        $chiave = $_POST['suggestCampi'][$this->nameForm . '_CHIAVE_RICERCA'];

        cwbBtaSoggSearchUtils::popolaCampi($this->nameForm, $chiave, $this->modalitaComponente, $this->escludiOmnis);
    }

    private function cleanValori($escludeNominativo = false, $exludeCf = false) {
        cwbBtaSoggSearchUtils::pulisciValori($this->nameForm, $escludeNominativo, $exludeCf);
    }

    // se è stato settato il nameform del padre, alla selezione di un soggetto rilancia l'evento anche sul padre
    private function lanciaEventoComponentePadre() {
        if ($this->nameFormContainer) {
            $objModel = itaFrontController::getInstance($this->nameFormContainer, $this->aliasNameFormContainer);
            $objModel->setEvent($this->nameForm . "_onSelectBtaSoggSearch");
            $objModel->setFormData($_POST['suggestCampi'][$this->nameForm . '_CHIAVE_RICERCA']);
            $objModel->parseEvent();
        }
    }

    private function changeData($matricola = null, $cf = null, $nominativo = null) {
        if (strpos($matricola, self::BLOCK_CHANGE_KEY) !== false || strpos($cf, self::BLOCK_CHANGE_KEY) !== false || strpos($nominativo, self::BLOCK_CHANGE_KEY) !== false) {
            // se scrivo qualcosa, e poi faccio tab dopo che è uscita la suggest, il tab oltre a 
            // far partire l'evento onchange fa partire anche la selezione del primo record del suggest.
            // In questo caso l'evento onchange che parte per secondo, nella casella trova il valore elaborato 
            // invece che quello inserito. Quindi in questo caso blocco l'evento onchange tanto i dati sono
            // gia stati caricati dall'evento onsuggest
            return;
        }

        $input = array(
            'NOME_RIC' => cwbLibCalcoli::calcNomeRic($nominativo),
            'CFPIVA' => $cf,
            'MATRICOLA' => ($nominativo || $cf) ? null : $matricola
        );
        $soggTrovato = cwbBtaSoggSearchUtils::forzaSelezione($this->nameForm, $input, $this->getModalitaComponente(), $matricola);

        if ($soggTrovato) {
            $chiaveOnsuggest = $this->chiaveOnsuggest;
            $this->chiaveOnsuggest = null;

            if ($chiaveOnsuggest && $chiaveOnsuggest == $soggTrovato['PROGSOGG']) {
                // se seleziono un record dal suggest, dopo parte anche l'evento onchange e quindi 
                // se ho già un evento suggest su questa chiave lo devo bloccare
                return;
            }

            cwbBtaSoggSearchUtils::popolaCampi($this->nameForm, $soggTrovato['PROGSOGG']);
            $this->lanciaEventoComponentePadre();
        }
    }

    private function visualizzaDettaglio() {
        $progsogg = cwbBtaSoggSearchUtils::leggiChiaveSelezionata($this->nameForm);
        if (!$progsogg) {
            Out::msgStop("Attenzione", "Soggetto non selezionato");
            return;
        }

        $objModel = cwbLib::apriFinestraDettaglioRecord(self::NAMEFORM_SOGGETTI, $this->nameForm, "", "", $progsogg);
        $objModel->parseEvent();
    }

    function getNameFormContainer() {
        return $this->nameFormContainer;
    }

    function setNameFormContainer($nameFormContainer) {
        $this->nameFormContainer = $nameFormContainer;
    }

    function getAliasNameFormContainer() {
        return $this->aliasNameFormContainer;
    }

    function setAliasNameFormContainer($aliasNameFormContainer) {
        $this->aliasNameFormContainer = $aliasNameFormContainer;
    }

    function getModalitaComponente() {
        if (!$this->modalitaComponente) {
            // default
            $this->modalitaComponente = cwbBtaSoggSearchUtils::BTA_SOGG_MODE;
        }
        return $this->modalitaComponente;
    }

    function setModalitaComponente($modalitaComponente) {
        $this->modalitaComponente = $modalitaComponente;
    }

    function getEscludiOmnis() {
        return $this->escludiOmnis;
    }

    function setEscludiOmnis($escludiOmnis) {
        $this->escludiOmnis = $escludiOmnis;
    }

}
