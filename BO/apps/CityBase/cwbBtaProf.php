<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaProf() {
    $cwbBtaProf = new cwbBtaProf();
    $cwbBtaProf->parseEvent();
    return;
}

class cwbBtaProf extends cwbBpaGenTab {

    function initVars() {
        $this->setDetailView(false);
        $this->GRID_NAME = 'gridBtaProf';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 12;
        $this->libDB = new cwbLibDB_BTA();
        $this->errorOnEmpty = false;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESPROF_decod_butt':
                        $this->decodProfOriginale($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF_S'], ($this->nameForm . '_BTA_PROF[CODPROF_S]'), $_POST[$this->nameForm . '_DESPROF_decod'], ($this->nameForm . '_DESPROF_decod'), true);
                        break;
                }
                break;
            case 'returnFromBtaProf':
                $_POST['nameform'] = $this->nameFormOrig; // svuoto IL NAMEFORM ORIGINALE altrimenti continua a settare l'alias
                switch ($this->elementId) {
                    case $this->nameFormOrig . '_DESPROF_decod_butt':
                    case $this->nameFormOrig . '_DESPROF_decod':
                        if ($this->formData['returnData']['CODPROF'] === $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF']) {
                            Out::msgInfo('Attenzione', 'Il codice professione raggruppato deve essere diverso dal codice principale!');
                        } else {
                            Out::valore($this->nameFormOrig . '_BTA_PROF[CODPROF_S]', $this->formData['returnData']['CODPROF']);
                            Out::valore($this->nameFormOrig . '_DESPROF_decod', $this->formData['returnData']['DESPROF']);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODPROF':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODPROF'], $this->nameForm . '_CODPROF');
                        break;
                    case $this->nameForm . '_CODPROF_IS':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODPROF_IS'], $this->nameForm . '_CODPROF_IS');
                        break;
                    case $this->nameForm . '_BTA_PROF[CODPROF_IS]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF_IS'], $this->nameForm . '_BTA_PROF[CODPROF_IS]');
                        break;
                    case $this->nameForm . '_BTA_PROF[ANPR_POSPROF]':
                        $anpr_posprof = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ANPR_POSPROF'];
                        if ($anpr_posprof <> 0) {
                            $flag = 1;
                        }
                        //$this->ctrlCombo($flag);
                        $posprof = $this->valorizzaPosprof($anpr_posprof);
                        Out::valore($this->nameForm . '_POSPROF', $posprof);
                        Out::valore($this->nameForm . '_BTA_PROF[POSPROF]', $posprof);
                        break;
                    case $this->nameForm . '_BTA_PROF[SETTATTIV]':
                        if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SETTATTIV'] <> 0) {
                            $flag = 2;
                        }
                        $this->ctrlCombo($flag);
                        break;
                    case $this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]':
                        $anpr_condnoprof = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ANPR_CONDNOPROF'];
                        if ($anpr_condnoprof <> 0) {
                            $flag = 3;
                        }
                        $this->ctrlCombo($flag);
                        $condnoprof = $this->valorizzaCondnoprof($anpr_condnoprof);
                        Out::valore($this->nameForm . '_CONDNOPROF', $condnoprof);
                        Out::valore($this->nameForm . '_BTA_PROF[CONDNOPROF]', $condnoprof);
                        break;
                    case $this->nameForm . '_BTA_PROF[CODPROF_S]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF_S'], $this->nameForm . '_BTA_PROF[CODPROF_S]', $this->nameForm . '_DESPROF_decod')) {
                            $this->decodProfOriginale($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF_S'], ($this->nameForm . '_BTA_PROF[CODPROF_S]'), null, ($this->nameForm . '_DESPROF_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESPROF_decod', '');
                        }
                        break;

                    case $this->nameForm . '_DESPROF_decod':
                        $this->decodProfOriginale($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF_S'], ($this->nameForm . '_BTA_PROF[CODPROF_S]'), $_POST[$this->nameForm . '_DESPROF_decod'], ($this->nameForm . '_DESPROF_decod'));
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODPROF_formatted'] != '') {
            $this->gridFilters['CODPROF'] = $this->formData['CODPROF_formatted'];
        }
        if ($_POST['CODPROF_IS'] != '') {
            $this->gridFilters['CODPROF_IS'] = $this->formData['CODPROF_IS'];
        }
        if ($_POST['DESPROF_IS'] != '') {
            $this->gridFilters['DESPROF_IS'] = $this->formData['DESPROF_IS'];
        }
        if ($_POST['DESPROF'] != '') {
            $this->gridFilters['DESPROF'] = $this->formData['DESPROF'];
        }
    }

    protected function postApriForm() {
        $this->initPosProf();
        $this->initSettAtt();
        $this->initCondNonProf();
        $this->initAlboGiudici();
        $this->initAlboScruta();
        $this->initPresSeg();
        Out::disableField($this->nameForm . '_POSPROF');
        Out::disableField($this->nameForm . '_CONDNOPROF');
        Out::setFocus("", $this->nameForm . '_DESPROF');
    }

    protected function postNuovo() {
        $progr = cwbLibCalcoli::trovaProgressivo("CODPROF", "BTA_PROF");
        Out::valore($this->nameForm . '_BTA_PROF[CODPROF]', $progr);
        Out::attributo($this->nameForm . '_BTA_PROF[CODPROF]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_PROF[CODPROF]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_PROF[CODPROF]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_PROF[CODPROF]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        //Out::attributo($this->nameForm . '_BTA_PROF[CODPROF]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_PROF[DESPROF]');
        //Out::css($this->nameForm . '_BTA_PROF[CODPROF]', 'background-color', '#FFFFE0');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_PROF[DESPROF]', trim($this->CURRENT_RECORD['DESPROF']));
        Out::valore($this->nameForm . '_BTA_PROF[DESPROF_IS]', trim($this->CURRENT_RECORD['DESPROF_IS']));
        Out::valore($this->nameForm . '_POSPROF', trim($this->CURRENT_RECORD['POSPROF']));
        Out::valore($this->nameForm . '_CONDNOPROF', trim($this->CURRENT_RECORD['CONDNOPROF']));
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESPROF_decod', '');
        Out::valore($this->nameForm . '_PROFGIUPOP_SI', '');
        Out::valore($this->nameForm . '_PROFGIUPOP_NO', '');
        Out::valore($this->nameForm . '_PROFALBOS_SI', '');
        Out::valore($this->nameForm . '_PROFALBOS_NO', '');
        Out::valore($this->nameForm . '_PROFPRES_SI', '');
        Out::valore($this->nameForm . '_PROFPRES_NO', '');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['CODPROF'] = trim($this->formData[$this->nameForm . '_CODPROF']);
        $filtri['DESPROF'] = trim($this->formData[$this->nameForm . '_DESPROF']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaProf($filtri, true, $sqlParams);
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESPROF');
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaProfChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODPROF_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODPROF']);

            //imposto manualmente la Posizione Professione nella grid
            switch ($Result_tab[$key]['POSPROF']) {
                case 0:
                    $Result_tab[$key]['POSPROF_formatted'] = ' ';
                    break;
                case 1:
                    $Result_tab[$key]['POSPROF_formatted'] = '1-IMPRENDITORE LIBERO PROFESSIONISTA';
                    break;
                case 2:
                    $Result_tab[$key]['POSPROF_formatted'] = '2-DIRIGENTE IMPIEGATO';
                    break;
                case 3:
                    $Result_tab[$key]['POSPROF_formatted'] = '3-LAVORATORE IN PROPRIO';
                    break;
                case 4:
                    $Result_tab[$key]['POSPROF_formatted'] = '4-OPERAIO E ASSIMILATI';
                    break;
                case 5:
                    $Result_tab[$key]['POSPROF_formatted'] = '5-COADIUVANTE';
                    break;
            }

            //imposto manualmente il Settore nella grid
            switch ($Result_tab[$key]['SETTATTIV']) {
                case 0:
                    $Result_tab[$key]['SETTATTIV_formatted'] = ' ';
                    break;
                case 1:
                    $Result_tab[$key]['SETTATTIV_formatted'] = '1-AGRICOLTURA';
                    break;
                case 2:
                    $Result_tab[$key]['SETTATTIV_formatted'] = '2-INDUSTRIA';
                    break;
                case 3:
                    $Result_tab[$key]['SETTATTIV_formatted'] = '3-COMMERCIO, PUBBLICI ESERCIZI E ALBERGHI';
                    break;
                case 4:
                    $Result_tab[$key]['SETTATTIV_formatted'] = '4-PUBBLICA AMM.NE E SERVIZI PUBBLICI O PRIVATI';
                    break;
            }

            //imposto manualmente la Condizione Non Professionale nella grid
            switch ($Result_tab[$key]['CONDNOPROF']) {
                case 0:
                    $Result_tab[$key]['CONDNOPROF_formatted'] = ' ';
                    break;
                case 96:
                    $Result_tab[$key]['CONDNOPROF_formatted'] = '96-CASALINGA';
                    break;
                case 97:
                    $Result_tab[$key]['CONDNOPROF_formatted'] = '97-STUDENTE';
                    break;
                case 98:
                    $Result_tab[$key]['CONDNOPROF_formatted'] = '98-IN ATTESA PRIMA OCCUPAZIONE';
                    break;
                case 99:
                    $Result_tab[$key]['CONDNOPROF_formatted'] = '99-ALTRE CONDIZIONI NON PROF.';
                    break;
            }
        }
        return $Result_tab;
    }

    private function initPosProf() {
        // Posizione Professionale
        Out::select($this->nameForm . '_POSPROF', 1, "0", 1, " ");
        Out::select($this->nameForm . '_POSPROF', 1, "1", 0, "1-IMPRENDITORE LIBERO PROFESSIONISTA");
        Out::select($this->nameForm . '_POSPROF', 1, "2", 0, "2-DIRIGENTE IMPIEGATO");
        Out::select($this->nameForm . '_POSPROF', 1, "3", 0, "3-LAVORATORE IN PROPRIO");
        Out::select($this->nameForm . '_POSPROF', 1, "4", 0, "4-OPERAIO E ASSIMILATI");
        Out::select($this->nameForm . '_POSPROF', 1, "5", 0, "5-COADIUVANTE");

        //Posizione Professionale ANPR
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "0", 1, "Non applicabile(es: cond.non prof.)");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "1", 0, "Dirigente");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "2", 0, "Quadro/impiegato");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "3", 0, "Operaio o assimilato");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "4", 0, "Imprenditore, libero professionista");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "5", 0, "Lavoratore in proprio");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "6", 0, "Coadiuvante familiare/socio coop");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "7", 0, "Collaborazione coord-continuativa/Prestazione opera occasionale");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "9", 0, "Non conosciuta/non fornita");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "A", 0, "Dirigente o impiegato(Solo per conversione AIRE/ANPR)");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_POSPROF]', 1, "B", 0, "Lavoratorre dipendente(Solo per conversione AIRE/ANPR)");
    }

    private function initSettAtt() {
        // Settore AttivitÃ 
        Out::select($this->nameForm . '_BTA_PROF[SETTATTIV]', 1, "0", 1, " ");
        Out::select($this->nameForm . '_BTA_PROF[SETTATTIV]', 1, "1", 0, "1-AGRICOLTURA");
        Out::select($this->nameForm . '_BTA_PROF[SETTATTIV]', 1, "2", 0, "2-INDUSTRIA");
        Out::select($this->nameForm . '_BTA_PROF[SETTATTIV]', 1, "3", 0, "3-COMMERCIO, PUBBLICI ESERCIZI E ALBERGHI");
        Out::select($this->nameForm . '_BTA_PROF[SETTATTIV]', 1, "4", 0, "4-PUBBLICA AMM.NE E SERVIZI PUBBLICI O PRIVATI");
    }

    private function initCondNonProf() {
        // Condizione non Professionale
        Out::select($this->nameForm . '_CONDNOPROF', 1, "0", 1, " ");
        Out::select($this->nameForm . '_CONDNOPROF', 1, "96", 0, "96-CASALINGA");
        Out::select($this->nameForm . '_CONDNOPROF', 1, "97", 0, "97-STUDENTE");
        Out::select($this->nameForm . '_CONDNOPROF', 1, "98", 0, "98-IN ATTESA PRIMA OCCUPAZIONE");
        Out::select($this->nameForm . '_CONDNOPROF', 1, "99", 0, "99-ALTRE CONDIZIONI NON PROF.");

        // Condizione non Professionale ANPR
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "0", 1, "Non applicabile(es:età pre-scolare)");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "1", 0, "Casalinga");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "2", 0, "Studente");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "3", 0, "Disoccupato/in cerca di prima occupazione");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "4", 0, "Pensionato/ritirato dal lavoro");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "5", 0, "Altra condizione non professionale");
        Out::select($this->nameForm . '_BTA_PROF[ANPR_CONDNOPROF]', 1, "6", 0, "Non conosciuta/non fornita");
    }

    private function initAlboGiudici() {
        // Combo Albo Giudici Popolari
        Out::select($this->nameForm . '_BTA_PROF[PROFGIUPOP]', 1, "S", 1, "SI");
        Out::select($this->nameForm . '_BTA_PROF[PROFGIUPOP]', 1, "N", 0, "NO");
    }

    private function initAlboScruta() {
        // Combo Albo Scrutatori
        Out::select($this->nameForm . '_BTA_PROF[PROFALBOS]', 1, "S", 1, "SI");
        Out::select($this->nameForm . '_BTA_PROF[PROFALBOS]', 1, "N", 0, "NO");
    }

    private function initPresSeg() {
        // Combo Presidente Seggio
        Out::select($this->nameForm . '_BTA_PROF[PROFPRES]', 1, "S", 1, "SI");
        Out::select($this->nameForm . '_BTA_PROF[PROFPRES]', 1, "N", 0, "NO");
    }

    private function ctrlCombo($valore) {
        switch ($valore) {
            case 1: // Posizione professione
            case 2: // Settore attività
                if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ANPR_CONDNOPROF'] > 0) {
                    Out::msgInfo('Attenzione', "Incongruenza nelle impostazioni dell'attività");
                }
                break;
            case 3: // Cond. non professionale
                if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ANPR_POSPROF'] > 0 ||
                        $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SETTATTIV'] > 0) {
                    Out::msgInfo('Attenzione', "Incongruenza nelle impostazioni dell'attività");
                }
                break;
        }
    }

    protected function valorizzaPosprof($anpr_pospof) {
        switch ($anpr_pospof) {
            case '0':
            case '9':
                $posprof = '0';
                break;
            case '1':
            case '2':
            case 'A':
                $posprof = '2';
                break;
            case '3':
            case 'B':
                $posprof = '4';
                break;
            case '4':
                $posprof = '1';
                break;
            case '5':
            case '7':
                $posprof = '3';
                break;
            case '6':
                $posprof = '5';
                break;
        }
        return $posprof;
    }

    protected function valorizzaCondnoprof($anpr_condnoprof) {
        switch ($anpr_condnoprof) {
            case '0':
            case '6':
                $condnoprof = '0';
                break;
            case '1':
                $condnoprof = '96';
                break;
            case '2':
                $condnoprof = '97';
                break;
            case '3':
            case '4':
                $condnoprof = '98';
                break;
            case '5':
                $condnoprof = '99';
                break;
        }
        return $condnoprof;
    }

    private function decodProfOriginale($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaProf", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODPROF", $desValue, $desField, "DESPROF", 'returnFromBtaProf', $_POST['id'], $searchButton, true);
        if ($row['CODPROF'] === $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODPROF']) {
            Out::msgInfo('Attenzione', 'Il codice professione raggruppato deve essere diverso dal codice principale!');
            Out::valore($codField, '');
            Out::valore($desField, '');
        }
    }

}

