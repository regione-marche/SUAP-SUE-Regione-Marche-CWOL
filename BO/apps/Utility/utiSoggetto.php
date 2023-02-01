<?php

/* * 
 *
 * SINCRONIZZAZIONE SOGGETTI
 *
 * PHP Version 5
 *
 * @category
 * @author     Antimo Panetta <antimo.panetta@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    08.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function utiSoggetto() {
    $utiSoggetto = new utiSoggetto();
    $utiSoggetto->parseEvent();
    return;
}

class utiSoggetto extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = "utiSoggetto";
    public $divRic = "utiSoggetto_divRicerca";
    public $divGes = "utiSoggetto_divGestione";
    public $divRis = "utiSoggetto_divRisultato";
    public $divModificaSoggetto = "utiSoggetto_divModificaSoggetto";
    public $divBott = "utiSoggetto_divBott";
    public $gridRuolo = "utiSoggetto_gridRuolo";
    public $gridElencoSoggetti = "utiSoggetto_gridElencoSoggetti";
    public $gridSoggetti = "utiSoggetto_gridSoggetti";
    public $errMessage;
    public $gridFilters;
    public $datiRicerca;
    public $devLib;

    function __construct() {
        parent::__construct();
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->devLib = new devLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
    }

    public function setDatiRicerca($dati) {
        $this->datiRicerca = $dati;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->OpenGestione();
                break;
            case 'openricerca':
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridElencoSoggetti:
                        if ($_POST['rowid']) {
                            Out::clearFields($this->nameForm, $this->divModificaSoggetto);
                            $soggetto = $this->getSoggetto($_POST['rowid'], 'rowid', false);
                            Out::valori($soggetto, $this->nameForm . '_ANA_SOGGETTI');
                            $pecID = $soggetto['PIVA'];
                            if ($soggetto['CF'] != '') {
                                $pecID = $soggetto['CF'];
                            }
                            $pecRec = $this->getPEC($pecID);
                            if ($pecRec) {
                                Out::valore($this->nameForm . '_pec', $pecRec['PEC']);
                                Out::valore($this->nameForm . '_validaFinoAl', $pecRec['VALIDAFINOAL']);
                            }
                            Out::show($this->nameForm . '_divModificaSoggetto');
                        }
                        break;
                    case $this->gridSoggetti:
                        if ($_POST['rowid']) {
                            $soggetto = $this->getSoggetto($_POST['rowid'], 'rowid', false);
                            $pecID = $soggetto['PIVA'];
                            if ($soggetto['CF'] != '') {
                                $pecID = $soggetto['CF'];
                            }
                            $pecRec = $this->getPEC($pecID);
                            $soggetto['PEC'] = $pecRec['PEC'];
                            if ($this->returnModel) {
                                $formObj = itaModel::getInstance($this->returnModel);
                                if (!$formObj) {
                                    Out::msgStop("Errore", "apertura fallita");
                                    $this->close();
                                    break;
                                }
                                $formObj->setEvent($this->returnEvent);
                                $formObj->setReturnSoggetto($soggetto);
                                $formObj->parseEvent();
                            }
                            $this->close();
                        }
                        break;
                }
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridElencoSoggetti:
                        $this->popolaGridElencoSoggetti();
                        Out::hide($this->nameForm . '_divModificaSoggetto');
                        break;
                    case $this->gridSoggetti:
                        $this->elencaSoggetti();
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_File_butt':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadFile";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ImportaCamCom':
                        if ($_POST[$this->nameForm . '_File'] != '') {
                            $esito = $this->importaCamCom($_POST[$this->nameForm . '_File']);
                            if ($esito === true) {
                                Out::valore($this->nameForm . '_File', '');
                                Out::msgInfo('Avviso', 'Importazione Terminata.');
                            } else {
                                Out::msgStop("ATTENZIONE", $this->getErrMessage());
                            }
                        } else {
                            Out::msgStop("Avviso", "Selezionare il file da importare.");
                            break;
                        }
                        break;
                    case $this->nameForm . "_Aggiorna":
                        if ($_POST[$this->nameForm . '_pec'] == '') {
                            Out::msgStop('ATTENZIONE', 'l\'indirizzo PEC non può essere cancellato. Indicare la data di validità.');
                            break;
                        }
                        $soggetto = $_POST[$this->nameForm . '_ANA_SOGGETTI'];
                        $pec = $_POST[$this->nameForm . '_pec'];
                        $validaFinoAl = $_POST[$this->nameForm . '_validaFinoAl'];
                        if ($_POST[$this->nameForm . '_ANA_SOGGETTI']['ROWID']) {
                            $pecID = $soggetto['PIVA'];
                            if ($soggetto['CF'] != '') {
                                $pecID = $soggetto['CF'];
                            }
                            $update_Info = 'Aggiornamento soggetto: ' . $pecID;
                            if (!$this->updateRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $soggetto, $update_Info)) {
                                Out::msgStop('ATTENZIONE', 'Errore in aggiornamento su ANA_SOGGETTI.');
                                break;
                            } else {
                                $pecRec = $this->getPEC($pecID);
                                if ($pecRec) {
                                    $pecRec['PEC'] = $pec;
                                    $pecRec['VALIDAFINOAL'] = $validaFinoAl;
                                    if (!$this->updateRecord($this->ITALWEB_DB, 'ANA_PEC', $pecRec, $update_Info)) {
                                        Out::msgStop('ATTENZIONE', 'Errore in aggiornamento su ANA_PEC.');
                                        break;
                                    }
                                } else {
                                    if ($pec != '') {
                                        $pecSoggetto['CFPI'] = $pecID;
                                        $pecSoggetto['PEC'] = $pec;
                                        $parametri = array();
                                        $esito = $this->sincronizzaPEC($pecSoggetto, $parametri);
                                        if ($esito !== true) {
                                            Out::msgStop("Errore", $this->getErrMessage());
                                            break;
                                        }
                                    }
                                }
                                $this->popolaGridElencoSoggetti();
                                Out::hide($this->nameForm . '_divModificaSoggetto');
                            }
                        }
                        break;
                    case $this->nameForm . "_Elenca":
                        $this->elencaSoggetti();
                        Out::hide($this->divRic, '');
                        Out::show($this->divRis, '');
                        Out::hide($this->nameForm . '_Elenca');
                        Out::show($this->nameForm . '_AltraRicerca');
                        break;
                    case $this->nameForm . "_AltraRicerca":
                        $this->OpenRicerca();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'returnUploadFile':
                $fileCamCom = $_POST['uploadedFile'];
                $path_parts = pathinfo($fileCamCom);
                if (strtoupper($path_parts['extension']) == 'CSV') {
                    Out::valore($this->nameForm . '_File', $fileCamCom);
                    Out::show($this->divBott, '');
                } else {
                    Out::valore($this->nameForm . '_File', '');
                    Out::msgInfo('ATTENZIONE', 'Solo file con estensione CSV.');
                    Out::hide($this->divBott, '');
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_gridFilters');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function OpenGestione() {
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::valore($this->nameForm . '_File', '');
        Out::show($this->nameForm . '_File_lbl');
        Out::show($this->nameForm . '_File');
        Out::show($this->nameForm . '_File_butt');
        Out::html($this->nameForm . '_Testo', '<center><br>ATTENZIONE<br>
                                                            Questa procedura è soggetta a forte personalizzazione in base al significato delle colonne del file.<br>
                                                            Il file della Camera di Commercio deve essere di tipo CSV.<br>
                                                            I campi vanno separati con il carattere \';\' .<br>
                                                            Prima di ogni utilizzo contattare l\'assistenza software.
                                                            <br>&nbsp;</center>');
        Out::hide($this->divBott, '');
        Out::hide($this->nameForm . '_divModificaSoggetto');
        $this->popolaGridElencoSoggetti();
        $this->creaCombo();
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm);
    }

    public function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        Out::show($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        if ($this->datiRicerca) {
            Out::valore($this->nameForm . '_Cognome', $this->datiRicerca['COGNOME']);
            Out::valore($this->nameForm . '_Nome', $this->datiRicerca['NOME']);
            Out::valore($this->nameForm . '_Cfpi', $this->datiRicerca['CFPI']);
            Out::valore($this->nameForm . '_Pec', $this->datiRicerca['PEC']);
        }
        Out::setFocus('', $this->nameForm . '_Cognome');
        Out::show($this->nameForm);
    }

    public function sincronizzaSoggetto($soggetto, $parametri = array()) {
        $ctrValore = $soggetto['CF'];
        if ($ctrValore == '' && $soggetto['PIVA'] != '') {
            $ctrValore = $soggetto['PIVA'];
        }
        if ($ctrValore == '') {
            $this->setErrMessage('Codice Fiscale/Partita Iva non valorizzati.');
            return false;
        }
        $ctrSoggetto = $this->getSoggetto($ctrValore, 'cfpi', false);
        if (!$ctrSoggetto) {
            $soggetto['DATAINSERIMENTO'] = date('Ymd');
            $soggetto['ORAINSERIMENTO'] = date('H:i:s');
            $soggetto['UTENTEINSERIMENTO'] = App::$utente->getKey('nomeUtente');
            $insert_Info = 'Inserimento soggetto: ' . $ctrValore;
            if (!$this->insertRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $soggetto, $insert_Info)) {
                $this->setErrMessage('Errore di inserimento su ANA_SOGGETTI ' . $exc->getMessage());
                return false;
            }
            return true;
        }
        $sincronizza = $this->livelloSincronizzazione($soggetto['FONTEDATI'], $ctrSoggetto['FONTEDATI']);
        if (!$sincronizza) {
            return true;
        }

        $rowid = $ctrSoggetto['ROWID'];
        //
        unset($ctrSoggetto['DATAINSERIMENTO']);
        unset($ctrSoggetto['ORAINSERIMENTO']);
        unset($ctrSoggetto['UTENTEINSERIMENTO']);
        $ctrSoggetto = $soggetto;
        $ctrSoggetto['DATAAGGIORNAMENTO'] = date('Ymd');
        $ctrSoggetto['ORAAGGIORNAMENTO'] = date('H:i:s');
        $ctrSoggetto['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
        $ctrSoggetto['FONTEDATI'] = $soggetto['FONTEDATI'];
        //
        $ctrSoggetto['ROWID'] = $rowid;
        $update_Info = 'Aggiornamento soggetto: ' . $ctrValore;
        if (!$this->updateRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $ctrSoggetto, $update_Info)) {
            $this->setErrMessage('Errore di aggiornamento su ANA_SOGGETTI ' . $exc->getMessage());
            return false;
        }
        return true;
    }

    public function getSoggetto($codice, $tipo = 'cfpi', $multi = true) {
        switch ($tipo) {
            case 'rowid':
                $sql = "SELECT * FROM ANA_SOGGETTI WHERE ROWID = $codice";
                break;
            case 'cfpi':
                $codice = trim($codice);
                $sql = "SELECT * FROM ANA_SOGGETTI WHERE TRIM(CF) = '" . $codice . "' OR TRIM(PIVA) = '" . $codice . "'";
                break;
        }
        try {
            $soggetto = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, $multi);
        } catch (Exception $e) {
            $soggetto = array();
        }
        return $soggetto;
    }

    public function sincronizzaPEC($pecSoggetto, $parametri) {
        $pec = $this->getPEC($pecSoggetto['CFPI']);
        //se la pec non esiste in archivio viene inserita
        if (!$pec) {
            if ($pecSoggetto['PEC'] == '') {
                return true;
//                $this->setErrMessage("PEC non definita");
//                return false;
            }
            $pecSoggetto['DATAINSERIMENTO'] = date('Ymd');
            $pecSoggetto['ORAINSERIMENTO'] = date('H:i:s');
            $pecSoggetto['UTENTEINSERIMENTO'] = App::$utente->getKey('nomeUtente');
            $insert_Info = 'Inserimento PEC soggetto: ' . $pecSoggetto['CFPI'];
            if (!$this->insertRecord($this->ITALWEB_DB, 'ANA_PEC', $pecSoggetto, $insert_Info)) {
                $this->setErrMessage('Errore di inserimento su ANA_PEC ' . $exc->getMessage());
                return false;
            }
            return true;
        }
        //se si sta inserendo una pe
        $sincronizza = $this->livelloSincronizzazione($pecSoggetto['FONTEDATI'], $pec['FONTEDATI']);
        if (!$sincronizza) {
            if ($pecSoggetto['PEC'] == $pec['PEC']) {
                return true; //se non si hanno i permessi per modificare il record, ma è lo stesso indirizzo return true
            }
            //se non si hanno i permessi per aggiornare il record e l'indirizzo è diverso da quello presente viene restituito il messaggio
            $this->setErrMessage("Non si dispone dei permessi necessari per modificare l'indirizzo pec " . $pec['PEC'] . ".<br/>"
                    . "L'indirizzo presente è stato inserito dall'archivio " . $pec['FONTEDATI']);
            return false;
        }

        $pec['DATAAGGIORNAMENTO'] = date('Ymd');
        $pec['ORAAGGIORNAMENTO'] = date('H:i:s');
        $pec['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
        $pec['FONTEDATI'] = $pecSoggetto['FONTEDATI'];
        if ($pecSoggetto['PEC'] != '') {
            $pec['PEC'] = $pecSoggetto['PEC'];
            $update_Info = 'Aggiornamento PEC soggetto: ' . $pecSoggetto['CFPI'];
        } else {
            $pec['VALIDAFINOAL'] = date('Ymd');
            $update_Info = 'Fine validà PEC soggetto: ' . $pecSoggetto['CFPI'];
        }
        if (!$this->updateRecord($this->ITALWEB_DB, 'ANA_PEC', $pec, $update_Info)) {
            $this->setErrMessage('Errore di aggiornamento su ANA_PEC ' . $exc->getMessage());
            return false;
        }
        return true;
    }

    public function getPEC($cfpi, $soloPEC = false) {
        $returnPec = array();
        $sql = "SELECT * FROM ANA_PEC WHERE TRIM(CFPI) = '" . trim($cfpi) . "'";
        try {
            $pecSoggetto = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        } catch (Exception $e) {
            $pecSoggetto = array();
        }
        foreach ($pecSoggetto as $pec) {
            if ($pec['VALIDAFINOAL'] == '') {
                $returnPec = $pec;
                break;
            }
        }
        if ($soloPEC) {
            return $returnPec['PEC']; //posso scegliere di far tornare solo l'indirizzo PEC e non tutto il record
        }
        return $returnPec;
    }

    public function importaCamCom($file) {
        $fileCamCom = fopen($file, 'r');
        if (!$fileCamCom) {
            $this->setErrMessage('Importazione INTERROTTA.');
            return false;
        }
        while (true) {
            $rigaFile = fgets($fileCamCom);
            if ($rigaFile === false) {
                break;
            }
            $soggetto = $riga = array();
            list ($riga['PRG'], $riga['PRV'], $riga['N-REG-IMP'], $riga['N-REA'], $riga['UL-SEDE'], $riga['N-ALBO-AA'], $riga['SEZ-REG-IMP'],
                    $riga['NG'], $riga['DT-ISCR-RI'], $riga['DT-ISCR-RD'], $riga['DT-ISCR-AA'], $riga['DT-APER-UL'], $riga['DT-CESSAZ'],
                    $riga['DT-INI-AT'], $riga['DT-CES-AT'], $riga['DT-FALLIM'], $riga['DT-LIQUID'], $riga['DENOMINAZIONE'], $riga['INDIRIZZO'], $riga['STRAD'],
                    $riga['CAP'], $riga['COMUNE'], $riga['FRAZIONE'], $riga['ALTRE-INDICAZIONI'], $riga['AA-ADD'], $riga['IND'], $riga['DIP'],
                    $riga['C-FISCALE'], $riga['PARTITA-IVA'], $riga['TELEFONO'], $riga['CAPITALE'], $riga['ATTIVITA'], $riga['CODICI-ATTIVITA'],
                    $riga['VALUTA-CAPITALE'], $riga['IND_PEC']) = EXPLODE(";", $rigaFile);

            /*
             * CONTROLLI PRIMA DELL'ASSEGNAZIONE
             */
            if ($riga['DENOMINAZIONE'] == 'DENOMINAZIONE' && $riga['INDIRIZZO'] == 'INDIRIZZO') {
                continue;
            }

            $cf = '';
            $pi = trim($riga['PARTITA-IVA']);
            if ($pi != '') {
                $pi = str_pad($pi, 11, "0", STR_PAD_LEFT);
            }
            if (trim($pi) == '') {           // AGENTE E RAPPRESENTANTE DI COMMERCIO
                $cf = trim($riga['C-FISCALE']);
            }
            if ($cf == '' && $pi == '') {
                continue;
            }

            $comune = explode('-', $riga['COMUNE']);    // COMUNE - PROVINCIA

            $denominazione1 = $riga['DENOMINAZIONE'];
            $denominazione2 = '';
            if (strlen($riga['DENOMINAZIONE']) > 60) {
                $denominazione1 = substr($riga['DENOMINAZIONE'], 0, 60);
                $denominazione2 = substr($riga['DENOMINAZIONE'], 60);
            }
            /*
             * FINE CONTROLLI
             */
            $soggetto['COGNOME'] = $denominazione1;
            $soggetto['NOME'] = $denominazione2;
            $soggetto['NATGIU'] = 1;
            $soggetto['CF'] = $cf;
            $soggetto['PIVA'] = $pi;
            $soggetto['CIVICO'] = '';
            $soggetto['DESCRIZIONEVIA'] = $riga['INDIRIZZO'];
            $soggetto['DATANASCITA'] = '';
            $soggetto['CITTARESI'] = trim($comune[0]);
            $soggetto['PROVRESI'] = trim($comune[1]);
            $soggetto['DATAAGGIORNAMENTO'] = date('Ymd');
            $soggetto['ORAAGGIORNAMENTO'] = date('H:i');
            $soggetto['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
            $soggetto['FONTEDATI'] = 'CAMCOM';
            $parametri = array();

            $esito = $this->sincronizzaSoggetto($soggetto, $parametri);
            if ($esito !== true) {
                Out::msgStop("Errore", $this->getErrMessage());
                return false;
            }

            if (trim($riga['IND_PEC']) != '') {
                $pecSoggetto['CFPI'] = $pi;
                if ($cf != '') {
                    $pecSoggetto['CFPI'] = $cf;
                }
                $pecSoggetto['PEC'] = trim($riga['IND_PEC']);
                $pecSoggetto['FONTEDATI'] = 'CAMCOM';
                $parametri = array();
                $esito = $this->sincronizzaPEC($pecSoggetto, $parametri);
                if ($esito !== true) {
                    Out::msgStop("Errore", $this->getErrMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function creaSqlSoggetti() {

        $sql = "SELECT 
                       ANA_SOGGETTI.ROWID,
		       ANA_SOGGETTI.COGNOME,
                       ANA_SOGGETTI.NOME,
                       ANA_SOGGETTI.CITTARESI,
                       ANA_SOGGETTI.PROVRESI,
                       ANA_SOGGETTI.DESCRIZIONEVIA,
                       ANA_SOGGETTI.CIVICO,
                       IF (ANA_SOGGETTI.CF <> '', ANA_SOGGETTI.CF, ANA_SOGGETTI.PIVA) AS CFPI,
                       ANA_PEC.PEC,
                       ANA_PEC.VALIDAFINOAL
                  
                    FROM ANA_SOGGETTI
                    LEFT OUTER JOIN ANA_PEC ANA_PEC ON ANA_PEC.CFPI = ANA_SOGGETTI.CF
                    WHERE ANA_SOGGETTI.CF <> ''";

        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                $sql .= " AND UPPER($key) LIKE UPPER('%$value%') ";
            }
        }

        $sql .= "UNION
                
                SELECT
		       ANA_SOGGETTI.ROWID, 
                       ANA_SOGGETTI.COGNOME,
                       ANA_SOGGETTI.NOME,
                       ANA_SOGGETTI.CITTARESI,
                       ANA_SOGGETTI.PROVRESI,
                       ANA_SOGGETTI.DESCRIZIONEVIA,
                       ANA_SOGGETTI.CIVICO,
                       IF (ANA_SOGGETTI.CF <> '', ANA_SOGGETTI.CF, ANA_SOGGETTI.PIVA) AS CFPI,
                       ANA_PEC.PEC,
                       ANA_PEC.VALIDAFINOAL
                  
                    FROM ANA_SOGGETTI
                    LEFT OUTER JOIN ANA_PEC ANA_PEC ON ANA_PEC.CFPI = ANA_SOGGETTI.PIVA
                    WHERE ANA_SOGGETTI.PIVA <> ''";

        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                $sql .= " AND UPPER($key) LIKE UPPER('%$value%') ";
            }
        }
        return $sql;
    }

    public function popolaGridElencoSoggetti() {
        TableView::disableEvents($this->gridElencoSoggetti);
        $this->setGridFilters();
        $sql = $this->creaSqlSoggetti();
        $ita_grid01 = new TableView($this->gridElencoSoggetti, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum($_POST['page'] ?: $_POST['page'] ?: $_POST[$this->gridElencoSoggetti]['gridParam']['page'] ?: 1);
        $ita_grid01->setPageRows($_POST['rows'] ?: $_POST['rows'] ?: $_POST[$this->gridElencoSoggetti]['gridParam']['rowNum'] ?: 16);
        $ita_grid01->setSortIndex($_POST['sidx'] ?: $_POST['sidx'] ?: 'COGNOME, NOME');
        $ita_grid01->setSortOrder($_POST['sord'] ?: $_POST['sord'] ?: '');
        $ita_grid01->getDataPage('json');
        TableView::enableEvents($this->gridElencoSoggetti);
    }

    public function creaCombo() {
        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "0", "0", "Persona Fisica");
        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "1", "0", "Persona Giuridica");
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CFPI'] != '') {
            $this->gridFilters ['CFPI'] = $_POST['CFPI'];
        }
        if ($_POST['COGNOME'] != '') {
            $this->gridFilters ['COGNOME'] = $_POST['COGNOME'];
        }
        if ($_POST['NOME'] != '') {
            $this->gridFilters ['NOME'] = $_POST['NOME'];
        }
        if ($_POST['DESCRIZIONEVIA'] != '') {
            $this->gridFilters ['DESCRIZIONEVIA'] = $_POST['DESCRIZIONEVIA'];
        }
        if ($_POST['CITTARESI'] != '') {
            $this->gridFilters ['CITTARESI'] = $_POST['CITTARESI'];
        }
        if ($_POST['PEC'] != '') {
            $this->gridFilters ['PEC'] = $_POST['PEC'];
        }
    }

    public function elencaSoggetti() {
        $this->gridFilters = array();
        if ($_POST[$this->nameForm . '_Cognome'] != '') {
            $this->gridFilters ['COGNOME'] = $_POST[$this->nameForm . '_Cognome'];
        }
        if ($_POST[$this->nameForm . '_Nome'] != '') {
            $this->gridFilters ['NOME'] = $_POST[$this->nameForm . '_Nome'];
        }
        if ($_POST[$this->nameForm . '_Cfpi'] != '') {
            $this->gridFilters ['CFPI'] = $_POST[$this->nameForm . '_Cfpi'];
        }
        if ($_POST[$this->nameForm . '_Pec'] != '') {
            $this->gridFilters ['PEC'] = $_POST[$this->nameForm . '_Pec'];
        }
        TableView::disableEvents($this->gridSoggetti);
        $sql = $this->creaSqlSoggetti();
        $ita_grid01 = new TableView($this->gridSoggetti, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum($_POST['page'] ?: $_POST['page'] ?: $_POST[$this->gridSoggetti]['gridParam']['page'] ?: 1);
        $ita_grid01->setPageRows($_POST['rows'] ?: $_POST['rows'] ?: $_POST[$this->gridSoggetti]['gridParam']['rowNum'] ?: 16);
        $ita_grid01->setSortIndex($_POST['sidx'] ?: $_POST['sidx'] ?: 'COGNOME, NOME');
        $ita_grid01->setSortOrder($_POST['sord'] ?: $_POST['sord'] ?: '');
        $ita_grid01->getDataPage('json');
        TableView::enableEvents($this->gridSoggetti);
    }

    public function livelloSincronizzazione($fonteDatiChiamante, $fonteDatiANA_SOGGETTI = '') {
        if (!$fonteDatiANA_SOGGETTI) {
            return true; //se non è definita la fonte dati di ANA_SOGGETTI la modifica è permessa
        }
        if ($fonteDatiChiamante == $fonteDatiANA_SOGGETTI) {
            return true; //se è la stessa fonte dati di ANA_SOGGETTI la modifica è permessa
        }
        $parametro = $this->devLib->getEnv_config('UTILITY', 'codice', 'SINCRONIZZASOGGETTO', false);
        if (!$parametro) {
            return true; //se non è definito il parametro di precedenza la modifica è permessa
        }
        $parametroSincro = $parametro['CONFIG'];
        if (!$parametroSincro) {
            return true; //se il parametro di precedenza è vuoto la modifica è permessa
        }
        $arr_livelli = explode(',', trim($parametroSincro));
        $livelloChiamante = $livelloANA_SOGGETTI = 9999;
        foreach ($arr_livelli as $k => $livello) {
            if ($fonteDatiChiamante == $livello) {
                $livelloChiamante = $k;
            }
            if ($fonteDatiANA_SOGGETTI == $livello) {
                $livelloANA_SOGGETTI = $k;
            }
        }
        if ($livelloChiamante <= $livelloANA_SOGGETTI) {
            return true; //se il programma chiamante ha un livello precedente o uguale a quello del soggetto in ANA_SOGGETTI la modifica è permessa
        }
        return false; //in tutti gli altri casi la modifica non è permessa
    }

}
