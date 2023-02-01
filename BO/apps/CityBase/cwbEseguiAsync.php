
<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbEventiBat.class.php';

function cwbEseguiAsync() {
    $cwbEseguiAsync = new cwbEseguiAsync();
    $cwbEseguiAsync->parseEvent();
    return;
}

class cwbEseguiAsync extends cwbBpaGenTab {

    private $idElaborazione;
    private $nameFormElab;
    private $utenteRichiesta;
    private $chiaveGenerica;
    private $delay;
    private $cliName;
    private $cliArguments;
    private $testataSelect;
    private $vediUltimo;

    const DELAY = 10;
    const GRID_NAME_TESTATA = 'gridLogTestata';
    const GRID_NAME_DETTAGLIO = 'gridLogDettaglio';

    protected function initVars() {
        $this->skipAuth = true;
        $this->noCrud = true;
        $this->GRID_NAME = 'gridLogDettaglio';
        $this->searchOpenElenco = true;
        $this->idElaborazione = cwbParGen::getFormSessionVar($this->nameForm, '_idElaborazione');
        $this->nameFormElab = cwbParGen::getFormSessionVar($this->nameForm, '_nameFormElab');
        $this->utenteRichiesta = cwbParGen::getFormSessionVar($this->nameForm, '_utenteRichiesta');
        $this->chiaveGenerica = cwbParGen::getFormSessionVar($this->nameForm, '_chiaveGenerica');
        $this->testataSelect = cwbParGen::getFormSessionVar($this->nameForm, '_testataSelect');
        $this->vediUltimo = cwbParGen::getFormSessionVar($this->nameForm, '_vediUltimo');
    }

    protected function postDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_idElaborazione', $this->idElaborazione);
            cwbParGen::setFormSessionVar($this->nameForm, '_nameFormElab', $this->nameFormElab);
            cwbParGen::setFormSessionVar($this->nameForm, '_utenteRichiesta', $this->utenteRichiesta);
            cwbParGen::setFormSessionVar($this->nameForm, '_chiaveGenerica', $this->chiaveGenerica);
            cwbParGen::setFormSessionVar($this->nameForm, '_testataSelect', $this->testataSelect);
            cwbParGen::setFormSessionVar($this->nameForm, '_vediUltimo', $this->vediUltimo);
        }
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'ontimer':
                if ($_POST['nameform'] == $this->nameForm) {
                    $this->elenca(false);
                    $this->checkSelect();
                }
                break;
            case 'onSelectRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_NAME_TESTATA:
                        // alla selezione della grid principale, carico la grid di dettaglio
                        $this->caricaDettaglio($_POST[$this->nameForm . '_' . self::GRID_NAME_TESTATA]['gridParam']['selrow']);
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        if (!$this->idElaborazione && !$this->nameFormElab) {
            Out::msgStop("Errore", "Id elaborazione o nameFormElab non impostato");
            return;
        }

        $this->testataSelect = null;

        if ($this->cliName) {
            // se viene passato il nome del cli, lo lancio in automatico. Se è vuoto significa che il cli
            // è già attivo e voglio vedere lo stato di avanzamento
            $devLib = new devLib();
            $utente = $devLib->getEnv_config('PARAMS_CLI_ASYNC', 'codice', 'UTENTE_CLI_ASYNC', false);
            $xml = 'Avviata Procedura';
            cwbEventiBat::logga(cwbEventiBat::EVENTO_INIZIO, "ALLINEA_RENDICONTAZIONI", $utente, $xml, $this->idElaborazione);

            // lancio in cli su un altro thread
            $cmd = ITA_BASE_PATH . '/cli/' . $this->cliName . '.php ';

            itaLib::execAsync($cmd, $this->cliArguments);
        }

        if ($this->idElaborazione) {
            Out::hide($this->nameForm . '_divTestata');
            cwbEventiBat::activateTimerByIdElab($this->nameForm . '_workSpace', $this->idElaborazione, $this->getDelay());
        } else {
            $records = cwbEventiBat::getEventsTestata($this->nameFormElab, $this->utenteRichiesta, $this->chiaveGenerica, cwbParGen::getSessionVar('ditta'), $this->vediUltimo);
            if (count($records) === 1) {
                Out::hide($this->nameForm . '_divTestata');
                cwbEventiBat::activateTimerByIdElab($this->nameForm . '_workSpace', $this->idElaborazione, $this->getDelay());
            } else {
                Out::show($this->nameForm . '_divTestata');
                cwbEventiBat::activateTimer($this->nameForm . '_workSpace', $this->nameFormElab, cwbParGen::getSessionVar('ditta'), $this->utenteRichiesta, $this->getDelay());
            }
        }
    }

    private function checkSelect() {
        if ($this->testataSelect) {
            TableView::setSelection($this->nameForm . '_' . self::GRID_NAME_TESTATA, $this->testataSelect);
        } else {
            if (!$this->idElaborazione) {
                TableView::clearGrid($this->nameForm . '_' . self::GRID_NAME_DETTAGLIO);
            }
        }
    }

    protected function initializeTable($sqlParams, $sortIndex = '', $sortOrder = '') {
        Out::html($this->nameForm . '_spanOra', "Ultimo aggiornamento: " . date("d-m-Y H:i:s"));
        if ($this->idElaborazione) {
            $this->switchGridName();
            $records = cwbEventiBat::getEvents($this->idElaborazione);
            $msg = cwbEventiBat::getEventRiepilogoByIdElab($this->idElaborazione);
            if ($msg) {
                $msg = "Riepilogo: " . $msg;
            }
            Out::html($this->nameForm . '_spanRiepilogo', "<b>" . $msg . "</b>");
        } else {
            $records = cwbEventiBat::getEventsTestata($this->nameFormElab, $this->utenteRichiesta, $this->chiaveGenerica, cwbParGen::getSessionVar('ditta'), $this->vediUltimo);
            if (count($records) === 1) {
                // se c'è solo un elaborazione non mostro la testata
                $this->idElaborazione = $records[0]['IDELAB'];
                $this->switchGridName();
                $records = cwbEventiBat::getEvents($this->idElaborazione);
                $msg = cwbEventiBat::getEventRiepilogoByIdElab($this->idElaborazione);
                if ($msg) {
                    $msg = "Riepilogo: " . $msg;
                }
                Out::html($this->nameForm . '_spanRiepilogo', "<b>" . $msg . "</b>");
            } else {
                $this->switchGridName(self::GRID_NAME_TESTATA);
            }
        }

        $this->helper->setDefaultRows(15);
        return $this->helper->initializeTableArray($records);
    }

    protected function nessunRecordMessage() {
        
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $value) {
            $Result_tab[$key]['TIPOEVENTO_format'] = cwbEventiBat::$eventoEnum[$value['TIPOEVENTO']];
            if ($this->GRID_NAME === self::GRID_NAME_TESTATA) {
                foreach ($value as $keyRec => $valueRec) {
                    // se sto caricando la grid di testata, aggiungo _testata alla fine
                    $Result_tab[$key][$keyRec . '_TESTATA'] = $valueRec;
                }
            }
        }

        return $Result_tab;
    }

    private function switchGridName($gridName = self::GRID_NAME_DETTAGLIO) {
        $this->helper->setGridName($gridName);
        $this->GRID_NAME = $gridName;

        $this->TABLE_VIEW = "BTA_EVENTI_BAT";
        $this->TABLE_NAME = "BTA_EVENTI_BAT";
    }

    private function caricaDettaglio($idEvento) {
        $this->testataSelect = $idEvento;
        $record = cwbEventiBat::getEventByPk($idEvento);
        $this->idElaborazione = $record['IDELAB'];
        $this->switchGridName();
        $this->elenca(false);
        $this->idElaborazione = null;
    }

    function getIdElaborazione() {
        return $this->idElaborazione;
    }

    function getDelay() {
        return $this->delay ? $this->delay : self::DELAY;
    }

    function setIdElaborazione($idElaborazione) {
        $this->idElaborazione = $idElaborazione;
    }

    function setDelay($delay) {
        $this->delay = $delay;
    }

    function getCliName() {
        return $this->cliName;
    }

    function getCliArguments() {
        return $this->cliArguments;
    }

    function setCliName($cliName) {
        $this->cliName = $cliName;
    }

    function setCliArguments($cliArguments) {
        $this->cliArguments = $cliArguments;
    }

    function getNameFormElab() {
        return $this->nameFormElab;
    }

    function getUtenteRichiesta() {
        return $this->utenteRichiesta;
    }

    function setNameFormElab($nameForm) {
        $this->nameFormElab = $nameForm;
    }

    function setUtenteRichiesta($utenteRichiesta) {
        $this->utenteRichiesta = $utenteRichiesta;
    }

    function getChiaveGenerica() {
        return $this->chiaveGenerica;
    }

    function setChiaveGenerica($chiaveGenerica) {
        $this->chiaveGenerica = $chiaveGenerica;
    }

    function getVediUltimo() {
        return $this->vediUltimo;
    }

    function setVediUltimo($vediUltimo) {
        $this->vediUltimo = $vediUltimo;
    }

}

?>