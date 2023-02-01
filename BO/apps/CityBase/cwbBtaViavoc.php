<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaViavoc() {
    $cwbBtaViavoc = new cwbBtaViavoc();
    $cwbBtaViavoc->parseEvent();
    return;
}

class cwbBtaViavoc extends cwbBpaGenTab {

    private $rictype; // tipo di ricerca.... Elemento + voce, Solo elemento, Solo Via.
    private $flag;

    function initVars() {
        $this->GRID_NAME = 'gridBtaViavoc';
        $this->GRID_NAME_VOCI = 'gridBtaVoci';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 11;
        $this->setTABLE_VIEW("BTA_VIAVOC_V01");
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODELEMEN_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaEleme', $this->nameForm, 'returnFromBtaEleme', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODELEMEN]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaEleme', $this->nameForm, 'returnFromBtaEleme', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_CODVIA_DA_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaVie', $this->nameForm, 'returnFromBtaVie', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_CODVIA_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaVie', $this->nameForm, 'returnFromBtaVie', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_CODVIA_A_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaVie', $this->nameForm, 'returnFromBtaVie', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODVOCEL]_butt':
                        if (strlen($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN']) === 0) { // se ho il codice elemento inserito, apro le voci associate.
                            cwbLib::apriFinestraRicerca('cwbBtaVoci', $this->nameForm, 'returnFromBtaVoci', $_POST['id'], true);
                        } else {
                            $row = $this->libDB->leggiBtaElemeChiave($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN']); // dal codice, mi leggo tutto il record di BTA_ELEME
                            cwbLib::apriFinestraDettaglio('cwbBtaVoci', $this->nameForm, 'returnFromBtaVoci', $_POST['id'], $row, $externalFilter);
                        }
                        break;
                    case $this->nameForm . '_CODVOCEL_butt':
                        if (strlen($_POST[$this->nameForm . '_CODELEMEN']) === 0) { // se ho il codice elemento inserito, apro le voci associate.
                            cwbLib::apriFinestraRicerca('cwbBtaVoci', $this->nameForm, 'returnFromBtaVoci', $_POST['id'], true);
                        } else {
                            $row = $this->libDB->leggiBtaElemeChiave($_POST[$this->nameForm . '_CODELEMEN']); // dal codice, mi leggo tutto il record di BTA_ELEME
                            cwbLib::apriFinestraDettaglio('cwbBtaVoci', $this->nameForm, 'returnFromBtaVoci', $_POST['id'], $row, $externalFilter);
                        }
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODVIA]_butt':
                        cwbLib::apriFinestraRicerca('cwbBtaVie', $this->nameForm, 'returnFromBtaVie', $_POST['id'], true);
                        break;
//                    case $this->nameForm . '_Conferma':
//                        $this->flag = true; // setto flag a true... quando ripasserà sul valida, controllo subito la flag impostata.
//                        $this->aggiungi();
//                        break;
                }
                break;
            case 'returnFromBtaEleme':
                switch ($this->elementId) {
                    case $this->nameForm . '_CODELEMEN_butt':
                        Out::valore($this->nameForm . '_CODELEMEN', $this->formData['returnData']['CODELEMEN']);
                        Out::valore($this->nameForm . '_DESELEMEN', $this->formData['returnData']['DESELEMEN']);
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODELEMEN]_butt':
                        Out::valore($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', $this->formData['returnData']['CODELEMEN']);
                        Out::valore($this->nameForm . '_DESELEMEN_decod', $this->formData['returnData']['DESELEMEN']);
                        break;
                }
                break;
            case 'returnFromBtaVoci':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_VIAVOC[CODVOCEL]_butt':
                        Out::valore($this->nameForm . '_BTA_VIAVOC[CODVOCEL]', $this->formData['returnData']['CODVOCEL']);
                        Out::valore($this->nameForm . '_DESVOCEL_decod', $this->formData['returnData']['DESVOCEEL']);
                        break;
                    case $this->nameForm . '_CODVOCEL_butt':
                        Out::valore($this->nameForm . '_CODVOCEL', $this->formData['returnData']['CODVOCEL']);
                        Out::valore($this->nameForm . '_DESVOCEL', $this->formData['returnData']['DESVOCEEL']);
                        break;
                }
                break;
            case 'returnFromBtaVie':
                switch ($this->elementId) {
                    case $this->nameForm . '_CODVIA_butt':
                        Out::valore($this->nameForm . '_CODVIA', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA', $this->formData['returnData']['TOPONIMO'] . ' ' . $this->formData['returnData']['DESVIA']);
                        break;
                    case $this->nameForm . '_CODVIA_DA_butt':
                        Out::valore($this->nameForm . '_CODVIA_DA', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_DA', $this->formData['returnData']['TOPONIMO'] . ' ' . $this->formData['returnData']['DESVIA']);
                        break;
                    case $this->nameForm . '_CODVIA_A_butt':
                        Out::valore($this->nameForm . '_CODVIA_A', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_A', $this->formData['returnData']['TOPONIMO'] . ' ' . $this->formData['returnData']['DESVIA']);
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODVIA]_butt':
                        Out::valore($this->nameForm . '_BTA_VIAVOC[CODVIA]', $this->formData['returnData']['CODVIA']);
                        Out::valore($this->nameForm . '_DESVIA_decod', $this->formData['returnData']['TOPONIMO'] . ' ' . $this->formData['returnData']['DESVIA']);
                        $this->caricaVoci($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->formData['returnData']['CODVIA']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BTA_VIAVOC[CODVOCEL]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVOCEL'], $this->nameForm . '_BTA_VIAVOC[CODVOCEL]', $this->nameForm . '_DESVOCEL_decod')) {
                            $this->decodVoci($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVOCEL'], ($this->nameForm . '_DESVOCEL_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESVOCEL_decod', '');
                        }
                        break;
                    case $this->nameForm . '_CODVOCEL':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVOCEL'], $this->nameForm . '_CODVOCEL', $this->nameForm . '_DESVOCEL')) {
                            $this->decodVoci($_POST[$this->nameForm . '_CODELEMEN'], $_POST[$this->nameForm . '_CODVOCEL'], ($this->nameForm . '_DESVOCEL'));
                        } else {
                            Out::valore($this->nameForm . '_DESVOCEL', '');
                        }
                        break;
                    case $this->nameForm . '_CODELEMEN':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODELEMEN'], $this->nameForm . '_CODELEMEN', $this->nameForm . '_DESELEMEN')) {
                            $this->decodElemento($_POST[$this->nameForm . '_CODELEMEN'], ($this->nameForm . '_CODELEMEN'), ($this->nameForm . '_DESELEMEN'));
                        } else {
                            Out::valore($this->nameForm . '_DESELEMEN', '');
                        }
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODELEMEN]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->nameForm . '_BTA_VIAVOC[CODELEMEN]', $this->nameForm . '_DESELEMEN_decod')) {
                            $this->decodElemento($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->nameForm . '_BTA_VIAVOC[CODELEMEN]', ($this->nameForm . '_DESELEMEN_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESELEMEN_decod', '');
                        }
                        break;
                    case $this->nameForm . '_CODVIA_DA':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_DA'], $this->nameForm . '_CODVIA_DA', $this->nameForm . '_DESVIA_DA')) {
                            $this->decodVia($_POST[$this->nameForm . '_CODVIA_DA'], ($this->nameForm . '_CODVIA_DA'), ($this->nameForm . '_DESVIA_DA'));
                            Out::valore($this->nameForm . '_CODVIA_A', 99999);
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_DA', '');
                        }
                        break;
                    case $this->nameForm . '_CODVIA':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA'], $this->nameForm . '_CODVIA', $this->nameForm . '_DESVIA')) {
                            $this->decodVia($_POST[$this->nameForm . '_CODVIA'], ($this->nameForm . '_CODVIA'), ($this->nameForm . '_DESVIA'));
                        } else {
                            Out::valore($this->nameForm . '_DESVIA', '');
                        }
                        break;
                    case $this->nameForm . '_CODVIA_A':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODVIA_A'], $this->nameForm . '_CODVIA_A', $this->nameForm . '_DESVIA_A')) {
                            $this->decodVia($_POST[$this->nameForm . '_CODVIA_A'], ($this->nameForm . '_CODVIA_A'), ($this->nameForm . '_DESVIA_A'));
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_A', '');
                        }
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[CODVIA]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], $this->nameForm . '_BTA_VIAVOC[CODVIA]', $this->nameForm . '_DESVIA_decod')) {
                            $this->decodVia($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], ($this->nameForm . '_BTA_VIAVOC[CODVIA]'), ($this->nameForm . '_DESVIA_decod'));
                            $this->caricaVoci($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA']);
                        } else {
                            Out::valore($this->nameForm . '_DESVIA_decod', '');
                        }
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[PARI_DISP]':
                        $this->abilitaCampiGest();
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[F_SUBNCIV]':
                        if (!$_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_SUBNCIV']) {
                            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '0');
                            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFE0');
                        } else {
                            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '1');
                            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFFF');
                        }
                        break;
                    case $this->nameForm . '_BTA_VIAVOC[F_SUBNCIVF]':
                        if (!$_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_SUBNCIVF']) {
                            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '0');
                            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFE0');
                        } else {
                            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '1');
                            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFFF');
                        }
                        break;
                    case $this->nameForm . '_RIC_TYPE0':
                        $this->rictype = 0;
                        cwbParGen::setFormSessionVar($this->nameForm, 'rictype', $this->rictype); // salvo in sessione il tipo ricerca.
                        //mostro/nascondo campi in base al tipo di ricerca
                        Out::hide($this->nameForm . '_CODVIA_field');
                        Out::hide($this->nameForm . '_DESVIA_field');
                        Out::show($this->nameForm . '_CODVOCEL_field');
                        Out::show($this->nameForm . '_DESVOCEL_field');
                        Out::show($this->nameForm . '_CODELEMEN_field');
                        Out::show($this->nameForm . '_DESELEMEN_field');
                        Out::show($this->nameForm . '_CODVIA_DA_field');
                        Out::show($this->nameForm . '_DESVIA_DA_field');
                        Out::show($this->nameForm . '_CODVIA_A_field');
                        Out::show($this->nameForm . '_DESVIA_A_field');

                        // per sicurezza, pulisco i campi che non sono relativi al tipo di ricerca selezionato
                        Out::valore($this->nameForm . '_CODVIA', ' ');
                        Out::valore($this->nameForm . '_DESVIA', ' ');
                        Out::setFocus("", $this->nameForm . '_CODELEMEN');
                        break;

                    case $this->nameForm . '_RIC_TYPE1':
                        $this->rictype = 1;
                        cwbParGen::setFormSessionVar($this->nameForm, 'rictype', $this->rictype); // salvo in sessione il tipo ricerca.
                        //mostro/nascondo campi in base al tipo di ricerca
                        Out::hide($this->nameForm . '_CODVIA_field');
                        Out::hide($this->nameForm . '_DESVIA_field');
                        Out::hide($this->nameForm . '_CODVOCEL_field');
                        Out::hide($this->nameForm . '_DESVOCEL_field');
                        Out::show($this->nameForm . '_CODELEMEN_field');
                        Out::show($this->nameForm . '_DESELEMEN_field');
                        Out::show($this->nameForm . '_CODVIA_DA_field');
                        Out::show($this->nameForm . '_DESVIA_DA_field');
                        Out::show($this->nameForm . '_CODVIA_A_field');
                        Out::show($this->nameForm . '_DESVIA_A_field');

                        // per sicurezza, pulisco i campi che non sono relativi al tipo di ricerca selezionato
                        Out::valore($this->nameForm . '_CODVIA', ' ');
                        Out::valore($this->nameForm . '_DESVIA', ' ');
                        Out::valore($this->nameForm . '_CODVOCEL', ' ');
                        Out::valore($this->nameForm . '_DESVOCEL', ' ');
                        Out::setFocus("", $this->nameForm . '_CODELEMEN');
                        break;

                    case $this->nameForm . '_RIC_TYPE2':
                        $this->rictype = 2;
                        cwbParGen::setFormSessionVar($this->nameForm, 'rictype', $this->rictype); // salvo in sessione il tipo ricerca.
                        // mostro/nascondo campi in base al tipo di ricerca
                        Out::show($this->nameForm . '_CODVIA_field');
                        Out::show($this->nameForm . '_DESVIA_field');
                        Out::hide($this->nameForm . '_CODVOCEL_field');
                        Out::hide($this->nameForm . '_DESVOCEL_field');
                        Out::hide($this->nameForm . '_CODELEMEN_field');
                        Out::hide($this->nameForm . '_DESELEMEN_field');
                        Out::hide($this->nameForm . '_CODVIA_DA_field');
                        Out::hide($this->nameForm . '_DESVIA_DA_field');
                        Out::hide($this->nameForm . '_CODVIA_A_field');
                        Out::hide($this->nameForm . '_DESVIA_A_field');

                        // per sicurezza, pulisco i campi che non sono relativi al tipo di ricerca selezionato
                        Out::valore($this->nameForm . '_CODVOCEL', ' ');
                        Out::valore($this->nameForm . '_DESVOCEL', ' ');
                        Out::valore($this->nameForm . '_CODELEMEN', ' ');
                        Out::valore($this->nameForm . '_DESELEMEN', ' ');
                        Out::valore($this->nameForm . '_CODVIA_DA', ' ');
                        Out::valore($this->nameForm . '_DESVIA_DA', ' ');
                        Out::valore($this->nameForm . '_CODVIA_A', ' ');
                        Out::valore($this->nameForm . '_DESVIA_A', ' ');
                        Out::setFocus("", $this->nameForm . '_CODVIA');
                        break;
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESELEMEN_decod', ' ');
        Out::valore($this->nameForm . '_DESVOCEL_decod', ' ');
        Out::valore($this->nameForm . '_DESVIA_decod', ' ');
    }

    protected function postNuovo() {
        Out::delAllRow($this->nameForm . '_' . $this->GRID_NAME_VOCI); //pulisco grid
        Out::attributo($this->nameForm . '_BTA_VIAVOC[CODVIA]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_VIAVOC[CODVIA]', 'background-color', '#FFFFFF');
        $progr = cwbLibCalcoli::trovaProgressivo("PROGVIAVOC", "BTA_VIAVOC");
        Out::attributo($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', 'background-color', '#FFFFFF');
        Out::valore($this->nameForm . '_BTA_VIAVOC[PROGVIAVOC]', $progr);

        //All'ingresso, setto come default "Associazione - Intera via"... disabilito i vari campi che in questa situazione non vanno valorizzati            
        Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIV]', 'disabled', '0', 'disabled');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIVF]', 'disabled', '0', 'disabled');
        Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV]', ' ');
        Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', ' ');
        Out::valore($this->nameForm . '_BTA_VIAVOC[PARI_DISP]', 'T');
        Out::valore($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', $this->externalParams['CODELEMEN']);
        Out::valore($this->nameForm . '_DESELEMEN_decod', $this->externalParams['DESELEMEN']);
        Out::valore($this->nameForm . '_BTA_VIAVOC[CODVOCEL]', $this->externalParams['CODVOCEL']);
        $this->decodVoci($_POST[$this->nameForm . '_CODELEMEN'], $_POST[$this->nameForm . '_CODVOCEL'], $this->nameForm . '_DESVOCEL_decod');
    }

    protected function postApriForm() {
        if (!$this->masterRecord) {
            $this->rictype = 1;
            cwbParGen::setFormSessionVar($this->nameForm, 'rictype', $this->rictype); // salvo in sessione il tipo ricerca.
        }
        $this->initComboAssociazione();
        Out::attributo($this->nameForm . "_RIC_TYPE1", "checked", "0", "checked");
        Out::hide($this->nameForm . '_CODVIA_field');
        Out::hide($this->nameForm . '_DESVIA_field');
        Out::hide($this->nameForm . '_CODVOCEL_field');
        Out::hide($this->nameForm . '_DESVOCEL_field');
        Out::setFocus("", $this->nameForm . '_CODELEMEN');
    }

    protected function postElenca() {
        $this->valorizzaMaster();
    }

    private function valorizzaMaster() {
        if ($this->masterRecord) {
            Out::valore($this->nameForm . '_DESELEMEN_master', $this->masterRecord['CODELEMEN'] . '-' .
                    $this->masterRecord['DESELEMEN']);
            Out::valore($this->nameForm . '_DESVOCEEL_master', $this->masterRecord['CODVOCEL'] . '-' .
                    $this->masterRecord['DESVOCEEL']);
        }
    }

    protected function postAltraRicerca() {
        Out::attributo($this->nameForm . "_RIC_TYPE1", "checked", "0", "checked");
        Out::setFocus("", $this->nameForm . '_DESELEMEN');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_VIAVOC[DESELEMEN]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_VIAVOC[CODVIA]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[CODVIA]', 'background-color', '#FFFFE0');
        Out::attributo($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_VIAVOC[CODELEMEN]', 'background-color', '#FFFFE0');
        $this->decodElemento($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $this->nameForm . '_BTA_VIAVOC[CODELEMEN]', $this->nameForm . '_DESELEMEN_decod');
        $this->decodVia($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVIA'], $this->nameForm . '_BTA_VIAVOC[CODVIA]', $this->nameForm . '_DESVIA_decod');
        $this->decodVoci($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODELEMEN'], $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODVOCEL'], ($this->nameForm . '_DESVOCEL_decod'));
        $this->caricaVoci($this->CURRENT_RECORD['CODELEMEN'], $this->CURRENT_RECORD['CODVIA']);
        $this->abilitaCampiGest();
        // Se presenti i sottonumeri, attivo direttamente il campo.
        if (trim($this->CURRENT_RECORD['SUBNCIV'])) {
            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '1');
            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFFF');
        }
        if (trim($this->CURRENT_RECORD['SUBNCIV_F'])) {
            Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '1');
            Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFFF');
        }
        //metto valori giusti su civico iniziale e finale. se richiamo solo il metodo abilitaCampiGest, mi mette valori fissi e non va bene.
        Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV]', trim($this->CURRENT_RECORD['NUMCIV']));
        Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', trim($this->CURRENT_RECORD['NUMCIV_F']));
        Out::valore($this->nameForm . '_BTA_VIAVOC[DESELEMEN]', trim($this->CURRENT_RECORD['DESELEMEN']));
        Out::setFocus('', $this->nameForm . '_BTA_VIAVOC[CODVOCEL]');
    }

    protected function preElenca() {
        if ($this->masterRecord) {
            return; // se tipo ricerca = 3, non devo settare nessun tipo di ricerca (sono stato chiamato da un altro programma che mi ha passato il record) 
        }
        $rictype = cwbParGen::getFormSessionVar($this->nameForm, 'rictype'); // leggo tipo ricerca
        switch ($rictype) {  // in base alla radio selezionata, controllo i parametri di ricerca.
            case 0:
                $this->controllacampi_ric_type0();
                $this->rictype = $this->controllacampi_ric_type0();
                break;
            case 1:
                $this->controllacampi_ric_type1();
                $this->rictype = $this->controllacampi_ric_type1();
                break;
            case 2:
                $this->controllacampi_ric_type2();
                $this->rictype = $this->controllacampi_ric_type2();
                break;
        }
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->masterRecord != null) {
            $filtri['CODELEMEN'] = $this->masterRecord['CODELEMEN'];
            $filtri['CODVOCEL'] = $this->masterRecord['CODVOCEL'];
            Out::show($this->nameForm . '_divMaster');
        } else {
            $filtri['CODELEMEN'] = trim($this->formData[$this->nameForm . '_CODELEMEN']);
            $filtri['CODVOCEL'] = trim($this->formData[$this->nameForm . '_CODVOCEL']);
            $filtri['DESELEMEN'] = trim($this->formData[$this->nameForm . '_DESELEMEN']);
            $filtri['CODVIA_DA'] = trim($this->formData[$this->nameForm . '_CODVIA_DA']);
            $filtri['CODVIA_A'] = trim($this->formData[$this->nameForm . '_CODVIA_A']);
            $filtri['CODVIA'] = trim($this->formData[$this->nameForm . '_CODVIA']);
            Out::hide($this->nameForm . '_divMaster');
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaViavoc($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaViavocChiave($index, $sqlParams);
    }

    private function caricaVoci($codelemen, $codvia) {
        $filtri = array(
            'CODELEMEN' => $codelemen,
            'CODVIA' => $codvia
        );

        $this->SQL = $this->libDB->getSqlLeggiBtaViavocVoci($filtri, false, $sqlParams);

        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME_VOCI, array(
            'sqlDB' => $this->MAIN_DB,
            'sqlQuery' => $this->SQL,
            'sqlParams' => $sqlParams
        ));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME_VOCI]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : self::DEFAULT_ROWS);
        if (!$this->getDataPage($ita_grid01, $this->elaboraGridVoci($ita_grid01))) {
            Out::setFocus('', $this->nameForm . '_BTA_VIAVOC[CODVOCEL]');
        } else {
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_VOCI);
        }
    }

    protected function elaboraGridVoci($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsVoci($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraRecordsVoci($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_tab[$key]['PARI_DISP']) {
                case "P":
                    $Result_tab[$key]['PARIDISPTUTTA'] = 'Pari';
                    break;
                case "D":
                    $Result_tab[$key]['PARIDISPTUTTA'] = 'Dispari';
                    break;
                case "T":
                    $Result_tab[$key]['PARIDISPTUTTA'] = 'Tutta';
                    break;
            }
            $Result_tab[$key]['DALNUMERO'] = $Result_tab[$key]['NUMCIV'] . " " . $Result_tab[$key]['SUBNCIV'];
            $Result_tab[$key]['ALNUMERO'] = $Result_tab[$key]['NUMCIV_F'] . " " . $Result_tab[$key]['SUBNCIV_F'];
            $Result_tab[$key]['ASSEGNATO'] = $Result_tab[$key]['CODVOCEL'] . " " . $Result_tab[$key]['DESVOCEEL'];
        }
        return $Result_tab;
    }

//    protected function valida($rec, $tipoOperazione, &$msg) {
//        if ($this->flag) {
//            return true;
//        }
//        $esito = true;
//        $msg = '';
//
//        // Controllo campi obbligatori in aggiunta/modifica
//        if ($tipoOperazione !== cwbLib::TIPO_OPER_CANCELLA) {
//            if (strlen($rec['CODELEMEN']) === 0) {
//                $esito = false;
//                $msg .= "Codice Elemento obbligatorio\n";
//            }
//            if (strlen($rec['CODVOCEL']) === 0) {
//                $esito = false;
//                $msg .= "Codice Voce obbligatorio\n";
//            }
//            if (strlen($rec['PROGVIAVOC']) === 0) {
//                $esito = false;
//                $msg .= "Codice obbligatorio\n";
//            }
//            if ($rec['PARI_DISP'] <> 'T') {
//                if ($rec['NUMCIV'] > $rec['NUMCIV_F']) {
//                    $esito = false;
//                    $msg .= "Hai indicato un numero civico iniziale" . ' ' . $rec['NUMCIV'] . " maggiore del finale" . ' ' . $rec['NUMCIV_F'] . "/n";
//                }
//                if ($rec['NUMCIV'] = $rec['NUMCIV_F']) {
//                    if ($rec['SUBNCIV'] = 0 && $rec['SUBNCIVF'] = 1) {
//                        $esito = false;
//                        $msg .= "Se indicato il sottonumero finale, va indicato anche quello iniziale./n";
//                    }
//                }
//                if ($rec['SUBNCIV'] = 1 && $rec['SUBNCIVF'] = 0) {
//                    $esito = false;
//                    $msg .= "Se indicato il sottonumero iniziale, va indicato anche quello finale./n";
//                }
//                if (($rec['SUBNCIV'] = 1 && $rec['SUBNCIVF'] = 1) && ($rec['SUBNCIV'] > $rec['SUBNCIVF'])) {
//                    $esito = false;
//                    $msg .= "Hai indicato un sottonumero iniziale" . ' ' . $rec['SUBNCIV'] . " maggiore del finale" . ' ' . $rec['SUBNCIVF'] . "/n";
//                }
//            }
//
//            //controllo sovrapposizione numeri civici   
//            $this->controllo_civici($rec);
//            $controllo = cwbParGen::getFormSessionVar($this->nameForm, 'esito_controllo');
//            if ($controllo) {
//                $esito = false; // setto esito false per non farlo scrivere finchè l'utente non clicca Conferma.
//                Out::msgQuestion("Attenzione", "Stai caricando per questa via/elemento, un intervallo di numeri civici che si sovrappone ad un'altra registrazione.\n" . "Solo se vuoi confermare la registrazione clicca su SI.", array(
//                    'F8-Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                    'F5-Conferma' => array('id' => $this->nameForm . '_Conferma', 'model' => $this->nameForm, 'shortCut' => "f5")
//                ));
//            }
//        }
//        return $esito;
//    }

    protected function controllacampi_ric_type0() { // Viario su Elementi e Voci
        if (strlen($_POST[$this->nameForm . '_CODELEMEN']) === 0 && strlen($_POST[$this->nameForm . '_CODVOCEL']) > 0) { // controllo campo di ricerca Cod.Elemento
            $esito = false;
            Out::msginfo("Attenzione", "Indicare l'ELEMENTO (La ricerca per Elemento è obbligatoria per il tipo di ricerca selezionato);");
        }

        if (strlen($_POST[$this->nameForm . '_CODELEMEN']) > 0 && strlen($_POST[$this->nameForm . '_CODVOCEL']) === 0) { // controllo campo di ricerca Cod.Elemento
            $esito = false;
            Out::msginfo("Attenzione", "Indicare la VOCE (La ricerca per Voce è obbligatoria per il tipo di ricerca selezionato);");
        }

        if (strlen($_POST[$this->nameForm . '_CODVOCEL']) === 0 && strlen($_POST[$this->nameForm . '_CODELEMEN']) === 0) { // controllo campo di ricerca Cod.Voce Elemento
            $esito = false;

            // Se manca codice voce e codice elemento, concateno i due messaggi.
            $messaggio = "Indicare l'ELEMENTO (La ricerca per Elemento è obbligatoria per il tipo di ricerca selezionato);\n"
                    . "Indicare la VOCE (La ricerca per Voce è obbligatoria per il tipo di ricerca selezionato);";
            Out::msginfo("Attenzione", $messaggio);
        }

        if ($_POST[$this->nameForm . '_CODELEMEN'] && $_POST[$this->nameForm . '_CODVOCEL']) {
            $esito = true;
        }

        return $esito;
    }

    protected function controllacampi_ric_type1() { // Elementi e Viario su Voci
        if (strlen($_POST[$this->nameForm . '_CODELEMEN']) === 0) {
            $esito = false;
            Out::msginfo("Attenzione", "Indicare l'ELEMENTO (La ricerca per Elemento è obbligatoria per il tipo di ricerca selezionato!);");
        } else {
            $esito = true;
        }
        return $esito;
    }

    protected function controllacampi_ric_type2() { // Elementi e Voci per Via
        if (strlen($_POST[$this->nameForm . '_CODVIA']) === 0) {
            $esito = false;
            Out::msginfo("Attenzione", "Indicare la VIA (La ricerca per Via è obbligatoria per il tipo di ricerca selezionato!);");
        } else {
            $esito = true;
        }
        return $esito;
    }

    private function decodElemento($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaElemeChiave($cod);
        if ($row) {
            Out::valore($codField, $row['CODELEMEN']);
            Out::valore($desField, $row['DESELEMEN']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    private function decodVia($cod, $codField, $desField) {
        $row = $this->libDB->leggiBtaVieChiave($cod);
        if ($row) {
            Out::valore($codField, $row['CODVIA']);
            Out::valore($desField, $row['TOPONIMO'] . ' ' . $row['DESVIA']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    private function decodVoci($cod, $cod2, $desField) {
        $row = $this->libDB->leggiBtaVociChiave($cod, $cod2);
        if ($row) {
            Out::valore($desField, $row['DESVOCEEL']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    protected function abilitaCampiGest() {
        switch ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PARI_DISP']) {
            //pari   
            case 'P':
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'readonly', '1');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'background-color', '#FFFFFF');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'readonly', '1');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'background-color', '#FFFFFF');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIV]', 'disabled', '1', 'disabled');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIVF]', 'disabled', '1', 'disabled');
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 0);
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 999998);
                break;
            //dispari    
            case 'D':
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'readonly', '1');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'background-color', '#FFFFFF');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'readonly', '1');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'background-color', '#FFFFFF');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIV]', 'disabled', '1', 'disabled');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIVF]', 'disabled', '1', 'disabled');
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 1);
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 999999);
                break;
            //intera via
            case 'T':
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'readonly', '0');
                Out::css($this->nameForm . '_BTA_VIAVOC[SUBNCIV_F]', 'background-color', '#FFFFE0');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIV]', 'disabled', '0', 'disabled');
                Out::attributo($this->nameForm . '_BTA_VIAVOC[F_SUBNCIVF]', 'disabled', '0', 'disabled');
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV]', ' ');
                Out::valore($this->nameForm . '_BTA_VIAVOC[NUMCIV_F]', ' ');
                break;
        }
    }

    private function initComboAssociazione() {
        // Combo Associazione
        Out::select($this->nameForm . '_BTA_VIAVOC[PARI_DISP]', 1, "P", 0, "Pari");
        Out::select($this->nameForm . '_BTA_VIAVOC[PARI_DISP]', 1, "D", 0, "Dispari");
        Out::select($this->nameForm . '_BTA_VIAVOC[PARI_DISP]', 1, "T", 1, "Intera Via");
    }

    protected function elaboraRecords($Result_tab) {

        foreach ($Result_tab as $key => $Result_rec) {
            $row = $this->libDB->leggiBtaVieChiave($Result_tab[$key]['CODVIA']);
            $Result_tab[$key]['DESVIA'] = $row['TOPONIMO'] . ' ' . $row['DESVIA'];
            switch ($Result_tab[$key]['PARI_DISP']) {
                case "P":
                    $Result_tab[$key]['PARI_DISP'] = 'Pari';
                    break;
                case "D":
                    $Result_tab[$key]['PARI_DISP'] = 'Dispari';
                    break;
                case "T":
                    $Result_tab[$key]['PARI_DISP'] = 'Tutta';
                    break;
            }
        }
        return $Result_tab;
    }

}

?>