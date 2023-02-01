<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBta.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggettoUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiUtil.class.php';

function cwbBtaSogg() {
    $cwbBtaSogg = new cwbBtaSogg();
    $cwbBtaSogg->parseEvent();
    return;
}

class cwbBtaSogg extends cwbBpaGenTab {

    private $libDB_BTA;
    private $libDB_FTA;
    private $libDB_BGE;
    private $soloAttuali = 1; // CON 1 RICERCO SOLO SU "DATI ATTUALI
    private $dittaindiv;
    private $origin;
    private $componentSoggAlias;
    private $componentSoggModel;
    private $componentBtaNoteAlias;
    private $componentBtaNoteModel;
    private $msgBlock;
    private $GRID_NAME_STORICO;
    private $TABLE_NAME_STORICO;
    private $TABLE_NAME_NOTE;
    private $cwbBtaSoggettoUtils;
    private $rowId;
    private $sortIndex; // 26-09-2019 correzione
    private $sortOrder; // 26-09-2019 correzione

    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaSogg';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->cwbBtaSoggettoUtils = new cwbBtaSoggettoUtils();
        $this->TABLE_NAME_STORICO = 'BTA_SOGGST';
        $this->TABLE_NAME_NOTE = 'BTA_NOTE';

        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 19;

        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;

        $this->errorOnEmpty = false;

        $this->soloAttuali = cwbParGen::getFormSessionVar($this->nameForm, 'soloAttuali');
        $this->GRID_NAME = 'gridBtaSogg';
        $this->GRID_NAME_NOTE = 'gridBtaNote';
        $this->GRID_NAME_STORICO = 'gridBtaSoggStoricoDett';
        $this->libDB = new cwbLibDB_BTA_SOGG();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->libDB_FTA = new cwfLibDB_FTA();
        $this->libDB_BGE = new cwbLibDB_BGE();

        $this->origin = cwbParGen::getFormSessionVar($this->nameForm, 'origin');
        if ($this->origin == '') {
            $this->origin = null;
        }

        $this->componentSoggAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentSoggAlias');
        if ($this->componentSoggAlias != '') {
            $this->componentSoggModel = itaFrontController::getInstance('cwfComponentSogg', $this->componentSoggAlias);
        }

        $this->componentBtaNoteAlias = cwbParGen::getFormSessionVar($this->nameForm, 'componentBtaNoteAlias');
        if ($this->componentBtaNoteAlias != '') {
            $this->componentBtaNoteModel = itaFrontController::getInstance('cwbComponentBtaNote', $this->componentBtaNoteAlias);
        }

        $this->dittaindiv = cwbParGen::getFormSessionVar($this->nameForm, 'dittaindiv');

        $this->msgBlock = cwbParGen::getFormSessionVar($this->nameForm, 'msgBlock');
        $this->rowId = cwbParGen::getFormSessionVar($this->nameForm, 'rowId');
        $this->sortIndex = cwbParGen::getFormSessionVar($this->nameForm, 'sortIndex');  // 26-09-2019 correzione
        $this->sortOrder = cwbParGen::getFormSessionVar($this->nameForm, 'sortOrder');  // 26-09-2019 correzione

        $this->prefisso_cliccabili = "span_custominfo_"; // prefisso per Icone Cliccabili per Informazioni
        $this->prefisso_ALLEG = 'ALLEG_'; // Prefisso per gli ALLEGATI cliccabile nella Grid
    }

    protected function preDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'origin', $this->origin);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentSoggAlias', $this->componentSoggAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'componentBtaNoteAlias', $this->componentBtaNoteAlias);
            cwbParGen::setFormSessionVar($this->nameForm, 'dittaindiv', $this->dittaindiv);
            cwbParGen::setFormSessionVar($this->nameForm, 'soloAttuali', $this->soloAttuali);
            cwbParGen::setFormSessionVar($this->nameForm, 'msgBlock', $this->msgBlock);
            cwbParGen::setFormSessionVar($this->nameForm, 'rowId', $this->rowId);
            cwbParGen::setFormSessionVar($this->nameForm, 'sortIndex', $this->sortIndex);   // 26-09-2019 correzione
            cwbParGen::setFormSessionVar($this->nameForm, 'sortOrder', $this->sortOrder);   // 26-09-2019 correzione
        }
    }

    protected function postClose() {
        if (!empty($this->componentSoggModel)) {
            $this->componentSoggModel->unlockRecords();
        }
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initComboTipo();
                $this->initComboSesso();
                $this->initComboRic();
                $this->initComboDef();
                $this->initComboPresente();
                $this->initComboValidato();
                $this->initComponents();
                $this->setRicClfor(false);
//                $this->initFixedElement();
                $this->initOrigin();
            case 'onClick':
                switch ($_POST['id']) {
//                    case $this->nameForm . '_Aggiungi':
//                        $this->verificaDati(true);
//                        $this->setBreakEvent(true);
//                        break;
                    case $this->nameForm . '_ConfermaAggiungi':
                        $this->aggiungi();
                        $this->setBreakEvent(true);
                        break;
//                    case $this->nameForm . '_Aggiorna':
//                        $this->verificaDati();
//                        $this->setBreakEvent(true);
//                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->verificaStoricizzazione();
                        $this->setBreakEvent(true);
                        break;
                    case $this->nameForm . '_AnnullaAggiornaStoricizza':
                        $this->aggiorna();
                        $this->setBreakEvent(true);
                        break;
//                    case $this->nameForm . '_VerificaStoricizza':
//                        $this->verificaStoricizzazione();
//                        $this->setBreakEvent(true);
//                        break;
                    case $this->nameForm . '_ConfermaAggiornaStoricizza':
                        $this->scegliDataStoricizzazione();
                        $this->setBreakEvent(true);
                        break;
                    case $this->nameForm . '_ConfermaDataStoric':
                        $this->storicizza();
                        $this->setBreakEvent(true);
                        break;
                    case $this->nameForm . '_AnnullaDataStoric':
                        $this->setBreakEvent(true);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        if ($this->sortIndex == $_POST['sidx']) {
                            if ($this->sortOrder == 'desc') {
                                $this->sortOrder = 'asc';
                            } else {
                                $this->sortOrder = 'desc';
                            }
                        } else {
                            $this->sortOrder = $_POST['sord'];  // 26-09-2019 correzione
                        }
                        $this->sortIndex = $_POST['sidx'];
                        break;
                }
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPOPERS':
                        if ($_POST[$this->nameForm . '_TIPOPERS'] == 'G') {
                            Out::hide($this->nameForm . '_SESSO_field');
                        } else {
                            Out::show($this->nameForm . '_SESSO_field');
                        }
                        break;
                    case $this->nameForm . '_RIC_CLFOR':
                        $this->setRicClfor($_POST[$this->nameForm . '_RIC_CLFOR'] == 1);
                        break;
                    case $this->nameForm . '_BTA_SOGG[DITTAINDIV]':
                        if ($_POST[$this->nameForm . '_BTA_SOGG']['DITTAINDIV'] == 1) {
                            $this->changeDittaIndividuale(1);
                        } else {
                            Out::msgQuestion("Attenzione", "Attenzione, se si disattiva la ditta individuale, al salvataggio, verranno eliminati i dati della ditta individuale dalla residenza e dall'albo dei pagamenti.<br><br>Proseguire?", array(
                                'No' => array('id' => $this->nameForm . '_AnnullaDittaIndividuale', 'model' => $this->nameForm),
                                'Si' => array('id' => $this->nameForm . '_ConfermaDittaIndividuale', 'model' => $this->nameForm)
                            ));
                        }

                        break;
                    case $this->nameForm . '_RIC_STOR':
                        $this->comboRicerca();
                        break;
                    case $this->nameForm . '_RIC_STOR':
                        $this->comboRicerca();
                        break;
                    case $this->nameForm . '_QUALIVEDO':
                        $this->postSqlElenca(array(), $sqlParams);
                        // aggiorno grid dopo aver selezionato la combo.
                        $ita_grid01 = new TableView($this->nameForm . '_' . $this->GRID_NAME, array(
                            'sqlDB' => $this->MAIN_DB,
                            'sqlQuery' => $this->SQL,
                            'sqlParams' => $sqlParams));
                        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
                        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['rowNum'];
                        $ita_grid01->setPageRows($pageRows ? $pageRows : cwbBpaGenHelper::DEFAULT_ROWS);

                        //     $this->setSortParameter($ita_grid01);

                        if (!$this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01))) {
                            TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);
                            Out::msgStop("Selezione", "Nessun record trovato.");
                        } else {
                            $this->setVisRisultato();
                            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
                        }
                        break;
                    case $this->nameForm . '_PROGSOGG':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGSOGG'], $this->nameForm . '_PROGSOGG');
                        break;
                    case $this->nameForm . '_GIORNO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_GIORNO'], $this->nameForm . '_GIORNO');
                        break;
                    case $this->nameForm . '_MESE':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_MESE'], $this->nameForm . '_MESE');
                        break;
                    case $this->nameForm . '_ANNO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_ANNO'], $this->nameForm . '_ANNO');
                        break;
                    case $this->nameForm . '_BTA_SOGG[NR_ISC_REA]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NR_ISC_REA'], $this->nameForm . '_BTA_SOGG[NR_ISC_REA]');
                        break;
                    case $this->nameForm . '_BTA_SOGG[PROGSOGG]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGSOGG'], $this->nameForm . '_BTA_SOGG[PROGSOGG]');
                        break;
                    case $this->nameForm . '_BTA_SOGG[TIPOPERS]':
                        $this->tipoPersona($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPOPERS']);
                        break;
                    case $this->nameForm . '_BTA_SOGG[PROV_DEF]':
                        $this->codFiscProvDef($_POST[$this->nameForm . '_BTA_SOGG']['PROV_DEF']); // verifico se il codice fiscale è provvisorio/definitivo e abilito/disabilito di conseguenza i campi.
                        break;
                    case $this->nameForm . '_BTA_SOGG[CODNAZI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], $this->nameForm . '_BTA_SOGG[CODNAZI]', $this->nameForm . '_DESNAZI_decod')) {
                            $this->decodNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_SOGG[CODNAZI]'), null, ($this->nameForm . '_DESNAZI_decod'));
                        } else {
                            Out::valore($this->nameForm . '_DESNAZI_decod', '');
                        }
                        break;
                    case $this->nameForm . '_DESNAZI_decod':
                        $this->decodNazion(null, ($this->nameForm . '_BTA_SOGG[CODNAZI]'), $_POST[$this->nameForm . '_DESNAZI_decod'], ($this->nameForm . '_DESNAZI_decod'));
                        break;
                    case $this->nameForm . '_BTA_SOGG[CODNAZPRO]':
                    case $this->nameForm . '_BTA_SOGG[CODLOCAL]':
                        if ($_POST[$this->nameForm . '_BTA_SOGG']['CODNAZPRO']) {
                            Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', str_pad($_POST[$this->nameForm . '_BTA_SOGG']['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                        }
                        if ($_POST[$this->nameForm . '_BTA_SOGG']['CODLOCAL']) {
                            Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', str_pad($_POST[$this->nameForm . '_BTA_SOGG']['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                        }
                        if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'] && $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL']) {
                            if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_DESLOCAL_decod')) {

                                $result = $this->decodLocal($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', null, ($this->nameForm . '_DESLOCAL_decod'));
                                if ($_POST[$this->nameForm . '_BTA_SOGG']['CODLOCAL']) {
                                    Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], 3, "00", STR_PAD_LEFT));
                                    Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', str_pad($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], 3, "00", STR_PAD_LEFT));
                                    Out::valore($this->nameForm . '_COD_BELFIORE', $result['CODBELFI']);
                                }
                            } else {
                                Out::valore($this->nameForm . '_DESLOCAL_decod', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_DESLOCAL_decod', '');
                        }
                        break;

                    case $this->nameForm . '_DESLOCAL_decod':
                        $result = $this->decodLocal(null, $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', $_POST[$this->nameForm . '_DESLOCAL_decod'], ($this->nameForm . '_DESLOCAL_decod'));
                        Out::valore($this->nameForm . '_COD_BELFIORE', $result['CODBELFI']);
                        break;
                    case $this->nameForm . '_FATT_SDI_SPLIT':
                        if ($_POST[$this->nameForm . '_FATT_SDI_SPLIT'] == 1) {
                            Out::valore($this->nameForm . '_BTA_SOGG[FATT_SDI]', '2');
                        } else {
                            Out::valore($this->nameForm . '_BTA_SOGG[FATT_SDI]', '0');
                        }
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME_STORICO:
                        Out::hide($this->nameForm . '_divGridStoricoVar');
                        Out::show($this->nameForm . '_divGesStorico');
                        $this->dettaglioStorico($this->formData['rowid']);
                        break;
                }
                break;
            case 'onSelectRow':
                $this->rowId = $this->formData['rowid'];
                $this->visualizzaButtonbarRow();
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $row = $this->libDB->leggiBtaSoggChiave($_POST['rowid']); // leggo record selezionato da grid.
                        $this->set_presenti($row);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
//                    case $this->nameForm . '_paneNote':
//                        $this->componentBtaNoteModel->eventShow();
//                        break;
                    case $this->nameForm . '_AnnullaDittaIndividuale':
                        Out::valore($this->nameForm . '_BTA_SOGG[DITTAINDIV]', 1);
                        break;
                    case $this->nameForm . '_ConfermaDittaIndividuale':
                        $this->changeDittaIndividuale(0);
                        break;
                    case $this->nameForm . '_TELEFONO_butt':
                        $this->showPhone();
                        break;
                    case $this->nameForm . '_paneStorico':
                        Out::show($this->nameForm . '_divGridStoricoVar');
                        Out::hide($this->nameForm . '_divGesStorico');
                        break;
                    case $this->nameForm . '_CODAREAMA_butt':
                        cwbLib::apriFinestraRicerca('cwbBorMaster', $this->nameForm, 'returnFromBorMaster', $_POST['id'], true);
                        break;
                    case $this->nameForm . '_BTA_SOGG[CODLOCAL]_butt':
                        $this->decodLocal($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZPRO'], $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', $_POST[$this->nameForm . '_DESLOCAL_decod'], ($this->nameForm . '_DESLOCAL_decod'), true);
                        break;
                    case $this->nameForm . '_BTA_SOGG[CODNAZI]_butt':
                        $this->decodNazion($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODNAZI'], ($this->nameForm . '_BTA_SOGG[CODNAZI]'), $_POST[$this->nameForm . '_DESNAZI_decod'], ($this->nameForm . '_DESNAZI_decod'), true);
                        break;
                    case $this->nameForm . '_TornaGrid':
                        $this->TornaGrid();
                        break;
                    case $this->nameForm . '_btnCalcCodfisc':
                        $dataNascita = $this->getDataNascita();

                        $methodArgs = array();
                        $methodArgs[0] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COGNOME'];
                        $methodArgs[1] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NOME'];
                        $methodArgs[2] = $dataNascita['GIORNO'] . '-' . $dataNascita['MESE'] . '-' . $dataNascita['ANNO'];
                        $methodArgs[3] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SESSO'];
                        $methodArgs[4] = $_POST[$this->nameForm . '_COD_BELFIORE'];

                        $error = '';
                        if (empty($methodArgs[0])) {
                            $error .= 'Cognome<br>';
                        }
                        if (empty($methodArgs[1])) {
                            $error .= 'Nome<br>';
                        }
                        if (empty($methodArgs[2])) {
                            $error .= 'Data di nascita<br>';
                        }
                        if (empty($methodArgs[3])) {
                            $error .= 'Sesso<br>';
                        }
                        if (empty($methodArgs[4])) {
                            $error .= 'Luogo di nascita<br>';
                        }
                        if (!empty($error)) {
                            $error = 'E\' necessario valorizzare i seguenti dati per calcolare il codice fiscale:<br>' . $error;
                            Out::msgStop('Errore', $error);
                        } else {
                            $risultato = $this->calcolaCodFisc($methodArgs);
                            if ($risultato['RESULT']['MESSAGE']) {
                                Out::valore($this->nameForm . '_BTA_SOGG[CODFISCALE]', $risultato['RESULT']['MESSAGE']);
                            } else {
                                Out::valore($this->nameForm . '_BTA_SOGG[CODFISCALE]', '');
                            }
                        }
                        break;
                    case $this->nameForm . '_btnRepDatiCf':
                        $methodArgs = array();
                        $methodArgs[0] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODFISCALE'];
                        $risultato = $this->repDatidaCodFisc($methodArgs);
                        if ($risultato['RESULT']['LIST']['ROW']) {
                            Out::valore($this->nameForm . '_BTA_SOGG[SESSO]', $risultato['RESULT']['LIST']['ROW']['SESSO']);
                            $anno = str_pad($risultato['RESULT']['LIST']['ROW']['ANNO'], 4, '0', STR_PAD_LEFT);
                            $mese = str_pad($risultato['RESULT']['LIST']['ROW']['MESE'], 2, '0', STR_PAD_LEFT);
                            $giorno = str_pad($risultato['RESULT']['LIST']['ROW']['GIORNO'], 2, '0', STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_BTA_SOGG[ANNO]', $anno);
                            Out::valore($this->nameForm . '_BTA_SOGG[MESE]', $mese);
                            Out::valore($this->nameForm . '_BTA_SOGG[GIORNO]', $giorno);
                            Out::valore($this->nameForm . '_dataNascita', $anno . '-' . $mese . '-' . $giorno);
                            Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', $risultato['RESULT']['LIST']['ROW']['CODNAZPRO']);
                            Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', $risultato['RESULT']['LIST']['ROW']['CODLOCAL']);
                            $this->decodLocal($risultato['RESULT']['LIST']['ROW']['CODNAZPRO'], $risultato['RESULT']['LIST']['ROW']['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', null, ($this->nameForm . '_DESLOCAL_decod'));
                        } else {
                            Out::valore($this->nameForm . '_BTA_SOGG[SESSO]', '');
                            Out::valore($this->nameForm . '_BTA_SOGG[GIORNO]', '');
                            Out::valore($this->nameForm . '_BTA_SOGG[MESE]', '');
                            Out::valore($this->nameForm . '_BTA_SOGG[ANNO]', '');
                            Out::valore($this->nameForm . '_dataNascita', '');
                            Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', '');
                            Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', '');
                        }
                        break;

                    case $this->nameForm . '_btnRendiFornitore':  // entra in modifica
                        $this->viewMode = false;
                        if (!empty($this->rowId)) {  // determinato rowId quando ho selezionato la riga 'onSelectRow'
                            $this->dettaglio($this->rowId);
                            break;
                        }
                        break;

                    case $this->nameForm . '_btnUfficiFE':  // entra Uffici FE x SDI
                        $this->viewMode = false;
                        if (!empty($this->rowId)) {  // determinato rowId quando ho selezionato la riga 'onSelectRow'
                            $progsogg = $this->rowId;
                            cwbLib::apriFinestraRicerca('cwbBtaSoggfe', $this->nameForm, 'return_ID_SOGGFE', '', true, array('PROGSOGG' => $progsogg), $this->nameForm, '', array());
                        }
                        break;

                    case $this->nameForm . '_btnDurc':
                        $this->viewMode = false;
                        if (!empty($this->rowId)) {  // determinato rowId quando ho selezionato la riga 'onSelectRow'
                            $progsogg = $this->rowId;
                            $externalFilters = array();
                            $externalFilters['PROGSOGG'] = array();
                            $externalFilters['PROGSOGG']['PERMANENTE'] = true; // non modificabile
                            $externalFilters['PROGSOGG']['VALORE'] = $progsogg;

                            $externalFilters['DATAFINE_SEARCH'] = array();
                            $externalFilters['DATAFINE_SEARCH']['PERMANENTE'] = false; // modificabile
                            $externalFilters['DATAFINE_SEARCH']['VALORE'] = cwbParGen::getDataElaborazione();
                            cwbLib::apriFinestraRicerca('cwbBtaDurc', $this->nameForm, 'return_PROG_DURC', '', true, $externalFilters, $this->nameForm, '', array());
                        }
                        break;

                    case $this->nameForm . '_btnAllegati':
                        $this->viewMode = false;
                        if (!empty($this->rowId)) {  // determinato rowId quando ho selezionato la riga 'onSelectRow'
                            $progsogg = $this->rowId;
                            $chiaveTestata = array(
                                'PROGSOGG' => $progsogg
                            );
                            cwbLibAllegatiUtil::apriFinestraAllegati('BTA_SOGGAL', $chiaveTestata, array(), array(), $this->viewMode);
                        }
                        break;

                    default:
                        // ID Particolari (elemento cliccabile) 
                        // Pulisco dell'eventuale: $this->nameForm
                        $nome_pulito = strtoupper(str_replace($this->nameForm . '_', '', $_POST['id']));
                        $len_pref_clicc = strlen($this->prefisso_cliccabili);
                        if (strlen($nome_pulito) > $len_pref_clicc) {
                            $key = str_replace(substr($nome_pulito, 0, $len_pref_clicc), "", $nome_pulito);
                            switch ($key) {

                                default:
                                    if (strlen($key) > strlen($this->prefisso_ALLEG) && substr($key, 0, strlen($this->prefisso_ALLEG)) == $this->prefisso_ALLEG) {
                                        $progx = explode("_", str_replace(substr($key, 0, strlen($this->prefisso_ALLEG)), "", $key));
                                        $selList = $this->libDB->leggiGeneric('BTA_SOGGAL', array('PROGSOGG' => $progx[0]), true);
                                        $selRow = $selList[0];
                                        if ($selRow['PROGSOGG'] == 0) {
                                            Out::msgStop("Errore", "Soggetto non correttamente selezionato");
                                            return;
                                        }
                                        $viewMode = false;
                                        if (empty($selRow) || count($selList) == 0)
                                            $viewMode = true;
                                        if (!empty($selRow['PROGSOGG'])) {
                                            $chiaveTestata = array(
                                                'PROGSOGG' => $selRow['PROGSOGG']
                                            );
                                            cwbLibAllegatiUtil::apriFinestraAllegati('BTA_SOGGAL', $chiaveTestata, array(), array(), $viewMode);
                                        }
                                    }  //  prefisso_ALLEG
                                    break;
                            } // switch ($key) 
                        }
                        break;
                }
                break;
            case 'returnFromBtaLocal':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESLOCAL_decod_butt':
                    case $this->nameForm . '_DESLOCAL_decod':
                    case $this->nameForm . '_BTA_SOGG[CODNAZPRO]':
                    case $this->nameForm . '_BTA_SOGG[CODLOCAL]':
                    case $this->nameForm . '_BTA_SOGG[CODLOCAL]_butt':
                        Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->formData['returnData']['CODNAZPRO']);
                        Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', $this->formData['returnData']['CODLOCAL']);
                        Out::valore($this->nameForm . '_DESLOCAL_decod', $this->formData['returnData']['DESLOCAL']);
                        Out::valore($this->nameForm . '_COD_BELFIORE', $this->formData['returnData']['CODBELFI']);
                        break;
                }
            case 'returnFromBtaNazion':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SOGG[CODNAZI]_butt':
                    case $this->nameForm . '_BTA_SOGG[CODNAZI]':
                    case $this->nameForm . '_DESNAZI_decod':

                        Out::valore($this->nameForm . '_BTA_SOGG[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
            case 'returnFromBorMaster':
                switch ($this->elementId) {
                    case $this->nameForm . '_BTA_SOGG[CODNAZI]_butt':
                        Out::valore($this->nameForm . '_BTA_CONSOL[CODNAZI]', $this->formData['returnData']['CODNAZI']);
                        Out::valore($this->nameForm . '_DESNAZI_decod', $this->formData['returnData']['DESNAZI']);
                        break;
                }
                break;
//            case 'onSelectRow': // già presente sopra !!!!
//                switch ($_POST['id']) {
//                    case $this->nameForm . '_' . $this->GRID_NAME:
//                        $row = $this->libDB->leggiBtaSoggChiave($_POST['rowid']); // leggo record selezionato da grid.
//                        $this->set_presenti($row);
//                        break;
//                }
//                break;
        }
    }

    protected function preElenca() {
        if (!empty($_POST[$this->nameForm . '_CODFISCALE'])) {
            $_POST['sidx'] = 'CODFISCALE';
            Out::codice('$("#' . $this->nameForm . '_' . $this->GRID_NAME . '").jqGrid().setGridParam({sortname: \'CODFISCALE\', sortorder: \'asc\'})');
        } elseif (!empty($_POST[$this->nameForm . '_PARTIVA'])) {
            $_POST['sidx'] = 'PARTIVA';
            Out::codice('$("#' . $this->nameForm . '_' . $this->GRID_NAME . '").jqGrid().setGridParam({sortname: \'PARTIVA\', sortorder: \'asc\'})');
        } elseif (!empty($_POST[$this->nameForm . '_NOME_RIC'])) {
            $_POST['sidx'] = 'NOME_RIC';
            Out::codice('$("#' . $this->nameForm . '_' . $this->GRID_NAME . '").jqGrid().setGridParam({sortname: \'COGNOME\', sortorder: \'asc\'})');
        }
        Out::show($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::show($this->nameForm . '_btnDurc');
        Out::show($this->nameForm . '_btnAllegati');
    }

    protected function preTornaElenco() {
        parent::preTornaElenco();
        Out::show($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::show($this->nameForm . '_btnDurc');
        Out::show($this->nameForm . '_btnAllegati');
    }

    protected function initializeTable($sqlParams, $sortIndex, $sortOrder) {
        if (!empty($this->sortIndex)) {  // 26-09-2019 correzione 
//            if ($sortIndex == $this->sortIndex){
//                if ($this->sortOrder == 'desc'){
//                    $this->sortOrder = 'asc';
//                } else {
//                    $this->sortOrder = 'desc';
//                }
//            }
            $sortIndex = $this->sortIndex;
            $sortOrder = $this->sortOrder;
        }   // 26-09-2019 correzione
        switch ($sortIndex) {
            case 'COGNOME':
                $sortIndex = array();
//                $sortIndex[] = 'COGNOME';
//                $sortIndex[] = 'NOME';
                $sortIndex[] = 'NOME_RIC';
                break;
        }
        return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
    }

    private function initFixedElement() {
        $codice = 'itaGetLib("libs/elementResize/jquery.resize.js");
                    
                    function ' . $this->nameForm . 'Resize(){
                        $("#' . $this->nameForm . '_tabDettagli").height($("#' . $this->nameForm . '_workSpace").height()-95);
                    }
                    
                    $("#' . $this->nameForm . '_workSpace").resize(' . $this->nameForm . 'Resize);
                    ' . $this->nameForm . 'Resize();';
        Out::codice($codice);
    }

    private function initOrigin() {
        $this->origin = array();
        $this->origin['ANAGRAFE'] = (isSet($_POST['soggOrigin_ANAGRAFE']) && $_POST['soggOrigin_ANAGRAFE'] == true);
        $this->origin['CONTRIB'] = (isSet($_POST['soggOrigin_CONTRIB']) && $_POST['soggOrigin_CONTRIB'] == true);
        $this->origin['CLIFOR'] = (isSet($_POST['soggOrigin_CLIFOR']) && $_POST['soggOrigin_CLIFOR'] == true);
        $this->origin['SOGGISCR'] = (isSet($_POST['soggOrigin_SOGGISCR']) && $_POST['soggOrigin_SOGGISCR'] == true);
        $this->origin['AMMINISTR'] = (isSet($_POST['soggOrigin_AMMINISTR']) && $_POST['soggOrigin_AMMINISTR'] == true);

        $origin = implode('', array_map(function($row) {
                    return ($row === true ? '1' : '0');
                }, $this->origin));
        switch ($origin) {
            case '10000':
                $presentein = 'ANAGRA';
                $qualivedo = 2;
                break;
            case '01000':
                $presentein = 'CONTRIB';
                $qualivedo = 3;
                break;
            case '00100':
                $presentein = 'FORNIT';
                $qualivedo = 4;
                break;
            case '00010':
                $presentein = 'SOGGISCR';
                $qualivedo = 5;
                break;
            case '00001':
                $presentein = 'AMMINISTR';
                $qualivedo = 6;
                break;
            default:
                $presentein = '';
                $qualivedo = 1;
                break;
        }
        Out::gridSetColumnFilterValue($this->nameForm, $this->GRID_NAME, 'PRESENTEIN', $presentein);
        Out::valore($this->nameForm . '_QUALIVEDO', $qualivedo);
    }

    private function initComponents() {
        $this->componentSoggAlias = $this->nameForm . 'componentSogg' . time() . rand(0, 1000);
        $this->componentSoggModel = itaFrontController::getInstance('cwfComponentSogg', $this->componentSoggAlias);
        itaLib::openInner('cwfComponentSogg', '', true, $this->nameForm . '_ForCli', '', '', $this->componentSoggAlias);
        $this->componentSoggModel->initComponents($this->nameFormOrig, $this->nameForm);

        $this->componentBtaNoteAlias = 'componentBtaNote' . time() . rand(0, 1000);
        $this->componentBtaNoteModel = itaFrontController::getInstance('cwbComponentBtaNote', $this->componentBtaNoteAlias);
        itaLib::openInner('cwbComponentBtaNote', '', true, $this->nameForm . '_componentNote', '', '', $this->componentBtaNoteAlias, true);
//        $this->componentBtaNoteModel->initComponent($this->nameFormOrig, $this->nameForm);
        $this->componentBtaNoteModel->initComponent($this->nameForm, $this->nameForm);
//  mostra subito la tabella BTA_NOTE
        $this->componentBtaNoteModel->eventShow();
//  setto il model del padre nel figlio per poter essere richiamato all'OnChange
        $this->componentBtaNoteModel->setReturnNameForm($this->nameForm);
        $this->componentBtaNoteModel->setReturnModel($this->nameFormOrig);   
        
        Out::hide($this->nameForm . '_btnRendiFornitore');  // Inizializza
        Out::hide($this->nameForm . '_btnUfficiFE');  // Inizializza
        Out::hide($this->nameForm . '_btnDurc');  // Inizializza
        Out::hide($this->nameForm . '_btnAllegati');  // Inizializza
    }

    /*
     * Controlla quali bottoni visualizzare della lista per il documento selezionato
     */

    private function visualizzaButtonbarRow() {
        Out::show($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        if ($this->origin['CLIFOR']) {
            $ftaClfor = $this->libDB->leggiGeneric('FTA_CLFOR', array('PROGSOGG' => $this->rowId), false);
            if (!empty($ftaClfor) && count($ftaClfor) > 0) {
                Out::hide($this->nameForm . '_btnRendiFornitore');
                Out::show($this->nameForm . '_btnUfficiFE');
            }
        }
        Out::show($this->nameForm . '_btnDurc');
        Out::show($this->nameForm . '_btnAllegati');
    }

    private function storicizza() {
        $this->formDataToCurrentRecord();
        $oldData = $this->getOldCurrentRecord($this->TABLE_NAME, $this->CURRENT_RECORD);
        $oldData['DATAVARIAZ'] = $_POST[$this->nameForm . '_DATA_STORIC'];
//        unset($oldData['FATT_SDI']);
        $oldData['PROGINT'] = cwbLibCalcoli::trovaProgressivo('PROGINT', "BTA_SOGGST");

        /* @var $service itaModelService */
        $service = itaModelServiceFactory::newModelService($this->nameForm);
        $data = new itaModelServiceData(new cwbModelHelper());
        $data->addMainRecord("BTA_SOGGST", $oldData);

        $service->insertRecord($this->MAIN_DB, "BTA_SOGGST", $data->getData(), null);

        $this->aggiorna();
    }

    private function scegliDataStoricizzazione() {
        $this->generaDataVariazione();
    }

    private function generaDataVariazione() {
        $fields = array(
            array(
                'label' => array(
                    'value' => "Data Storicizzazione"
                ),
                'id' => $this->nameForm . '_DATA_STORIC',
                'name' => $this->nameForm . '_DATA_STORIC',
                'value' => "",
                'type' => 'input',
                'class' => 'ita-date required',
                'size' => 10
            )
        );

        Out::msgInput('Data Storicizzazione', $fields, array(
            'Conferma' => array(
                'id' => $this->nameForm . '_ConfermaDataStoric',
                'model' => $this->nameForm,
                'class' => 'ita-edit'
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_AnnullaDataStoric',
                'model' => $this->nameForm,
                'class' => 'ita-button'
            )), $this->nameForm . "_workSpace"
        );

        Out::valore($this->nameForm . '_DATA_STORIC', date("d/m/Y"));
    }

    private function verificaDati($aggiungi = false) {
        $warnings = array();
        $errors = array();

        $this->formDataToCurrentRecord();
        if ($this->CURRENT_RECORD['TIPOPERS'] == 'F') {
            if (!empty($this->CURRENT_RECORD['CODFISCALE'])) {
                $row = $this->decodLocal($this->CURRENT_RECORD['CODNAZPRO'], $this->CURRENT_RECORD['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', null, ($this->nameForm . '_DESLOCAL_decod'));

                $methodArgs = array();
                $methodArgs[0] = $this->CURRENT_RECORD['COGNOME'];
                $methodArgs[1] = $this->CURRENT_RECORD['NOME'];
                $methodArgs[2] = $this->CURRENT_RECORD['GIORNO'] . '-' . $this->CURRENT_RECORD['MESE'] . '-' . $this->CURRENT_RECORD['ANNO'];
                $methodArgs[3] = $this->CURRENT_RECORD['SESSO'];
                $methodArgs[4] = $row['CODBELFI'];

                $risultato = $this->calcolaCodFisc($methodArgs);
                if (!empty($risultato['RESULT']['MESSAGE']) && $risultato['RESULT']['MESSAGE'] != $this->CURRENT_RECORD['CODFISCALE']) {
                    $warnings[] = 'Codice Fiscale non corretto o incongruente con dati anagrafici.';
                }
            } else {
                $warnings[] = 'Il soggetto è persona fisica ma non è stato immesso il Codice Fiscale.';
            }
        } else {
            if (empty($this->CURRENT_RECORD['CODFISCALE']) && empty($this->CURRENT_RECORD['PARTIVA'])) {
                $warnings[] = 'Il soggetto è persona giuridica ma non è stato immessa la Partita IVA né il Codice Fiscale.';
            }
        }

        if ($this->CURRENT_RECORD['DITTAINDIV'] == 0 && trim($this->CURRENT_RECORD['RAGSOC']) != '' && trim($this->CURRENT_RECORD['COGNOME'] . ' ' . $this->CURRENT_RECORD['NOME']) != trim($this->CURRENT_RECORD['RAGSOC'])) {
            $warnings[] = 'La ragione sociale è diversa dal nominativo (Cognome e Nome o Ragione Sociale) e non si tratta di ditta individuale. Se Rag.Sociale non è valorizzato verrà assegnato automaticamente.';
        }


        $cfLen = strlen(trim($this->CURRENT_RECORD['CODFISCALE']));
        if ($cfLen != 0 && $cfLen != 11 && $cfLen != 16) {
            $warnings[] = 'La lunghezza del codice fiscale è anomala.';
        }

        $pivaLen = strlen(trim($this->CURRENT_RECORD['PARTIVA']));
        if ($pivaLen != 0 && $pivaLen != 11) {
            $warnings[] = 'La lunghezza della partita IVA è anomala.';
        }

        if (!empty($this->CURRENT_RECORD['PARTIVA'])) {
            if ($this->CURRENT_RECORD['CODNAZI'] == 1 || $this->CURRENT_RECORD['CODNAZI'] == 0) {
                if (!cwbLibBta::checkPIVAIta($this->CURRENT_RECORD['PARTIVA'])) {
                    $warnings[] = 'La partita IVA risulta non essere corretta.';
                }
            } else {
                if (!cwbLibBta::checkPIVALength($this->CURRENT_RECORD['PARTIVA'], $this->CURRENT_RECORD['CODNAZI'])) {
                    $warnings[] = 'La lunghezza della partita IVA risulta essere diversa da quella specificata per lo stato.';
                }
            }
        }

        $bgeAppli = array_shift($this->libDB_BGE->leggiBgeAppli(array()));

        if (!empty($this->CURRENT_RECORD['CODFISCALE'])) {
            $filtri = array(
                'CODFISCALE' => trim($this->CURRENT_RECORD['CODFISCALE']),
                'PROGSOGG_diff' => $this->CURRENT_RECORD['PROGSOGG']
            );
            $cfCheck = $this->libDB->leggiBtaSogg($filtri, false, 1);
            if ($cfCheck) {
                if ($bgeAppli['UNIV_CFIS'] == 0) {
                    $warnings[] = 'Il soggetto ha un codice fiscale già usato da altri soggetti.';
                } else {
                    $errors[] = 'Il soggetto ha un codice fiscale già usato da altri soggetti.';
                }
            }
        }
        if (!empty($this->CURRENT_RECORD['PARTIVA'])) {
            $filtri = array(
                'PARTIVA' => trim($this->CURRENT_RECORD['PARTIVA']),
                'PROGSOGG_diff' => $this->CURRENT_RECORD['PROGSOGG']
            );
            $cfCheck = $this->libDB->leggiBtaSogg($filtri, false, 1);
            if ($cfCheck) {
                if ($bgeAppli['UNIV_PIVA'] == 0) {
                    $warnings[] = 'Il soggetto ha una partita IVA già usata da altri soggetti.';
                } else {
                    $errors[] = 'Il soggetto ha una partita IVA già usata da altri soggetti.';
                }
            }
        }

        if (!empty($errors)) {
            Out::msgStop('Errore', 'Attenzione, sono stati riscontrati i seguenti errori bloccanti:<br>' . implode('<br>', $errors));
        } elseif (!$aggiungi) {
            if (empty($warnings)) {
                $this->verificaStoricizzazione();
            } else {
                Out::msgQuestion("Attenzione", "Attenzione, risultano risultano i seguenti errori:<br>" . implode('<br>', $warnings) . "<br><br>Proseguire?", array(
                    'No' => array('id' => $this->nameForm . '_AnnullaAggiorna', 'model' => $this->nameForm),
                    'Si' => array('id' => $this->nameForm . '_VerificaStoricizza', 'model' => $this->nameForm)
                ));
            }
        } else {
            if (empty($warnings)) {
                $this->aggiungi();
            } else {
                Out::msgQuestion("Attenzione", "Attenzione, risultano risultano i seguenti errori:<br>" . implode('<br>', $warnings) . "<br><br>Proseguire?", array(
                    'No' => array('id' => $this->nameForm . '_AnnullaAggiungi', 'model' => $this->nameForm),
                    'Si' => array('id' => $this->nameForm . '_ConfermaAggiungi', 'model' => $this->nameForm)
                ));
            }
        }
    }

    private function verificaStoricizzazione() {
        $this->formDataToCurrentRecord();
        $oldData = $this->getOldCurrentRecord($this->TABLE_NAME, $this->CURRENT_RECORD);
        if ($this->CURRENT_RECORD && $oldData) {

            if (($this->CURRENT_RECORD['TIPOPERS'] == 'F' && ($this->CURRENT_RECORD['COGNOME'] != $oldData['COGNOME'] || $this->CURRENT_RECORD['NOME'] != $oldData['NOME'] || $this->CURRENT_RECORD['SESSO'] != $oldData['SESSO'] || $this->CURRENT_RECORD['CODFISCALE'] != $oldData['CODFISCALE'] || $this->CURRENT_RECORD['PARTIVA'] != $oldData['PARTIVA'])) || ($this->CURRENT_RECORD['TIPOPERS'] == 'G' && ($this->CURRENT_RECORD['COGNOME'] != $oldData['COGNOME'] || $this->CURRENT_RECORD['NOME'] != $oldData['NOME'] || $this->CURRENT_RECORD['CODFISCALE'] != $oldData['CODFISCALE'] || $this->CURRENT_RECORD['PARTIVA'] != $oldData['PARTIVA']))
            ) {
                // DEVO CHIEDERE SE STORICIZZARE
                Out::msgQuestion("Storicizzazione", "Attenzione, risultano variati il nominativo, il sesso, il codice fiscale o la P.IVA. Storicizzare la variazione?", array(
                    'No' => array('id' => $this->nameForm . '_AnnullaAggiornaStoricizza', 'model' => $this->nameForm),
                    'Si' => array('id' => $this->nameForm . '_ConfermaAggiornaStoricizza', 'model' => $this->nameForm)
                ));
            } else {
                $this->aggiorna();
            }
        } else {
            $this->aggiorna();
        }
    }

    private function comboRicerca() {
        if ($this->formData[$this->nameForm . '_RIC_STOR'] == 1) {
            $this->soloAttuali = 1;
            $this->GRID_NAME = 'gridBtaSoggAttuali';
        }
        if ($this->formData[$this->nameForm . '_RIC_STOR'] == 2) {
            $this->soloAttuali = 2;
            $this->GRID_NAME = 'gridBtaSoggStorico';
        }
        if ($this->formData[$this->nameForm . '_RIC_STOR'] == 3) {
            $this->soloAttuali = 3;
            $this->GRID_NAME = 'gridBtaSoggStorico';
        }
    }

    protected function preApriForm() {
        $this->soloAttuali = 1;
        $this->GRID_NAME = 'gridBtaSoggAttuali';
        Out::attributo($this->nameForm . "_FLAG_DIS", "checked", "0", "checked");
        Out::hide($this->nameForm . '_divPresenteBtn');
        Out::setFocus("", $this->nameForm . '_PROGSOGG');
        Out::attributo($this->nameForm . "_FL_DATAFINE", "checked", "0", "checked");
    }

    protected function postAltraRicerca() {
        Out::valore($this->nameForm . '_QUALIVEDO', 1); // Setto la combo "Presente in:" su "TUTTI".
        Out::hide($this->nameForm . '_divPresenteBtn');
        Out::setFocus("", $this->nameForm . '_PROGSOGG');

        $this->componentSoggModel->unlockRecords();
    }

    protected function preNuovo() {
        parent::preNuovo();
        Out::hide($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::hide($this->nameForm . '_btnDurc');
        Out::hide($this->nameForm . '_btnAllegati');
    }

    protected function postNuovo() {
        $this->dittaindiv = 0;

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_STORICO); // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $this->tipoPersona(); // abilito/disabilito campi in base al tipo persona
        $this->codFiscProvDef('P'); // abilito/disabilito campi in base al codice fiscale
        Out::hide($this->nameForm . '_BTA_SOGG[PROGSOGG]_field');
        Out::setFocus("", $this->nameForm . '_BTA_SOGG[COGNOME]');
        Out::tabSelect($this->nameForm . '_tabDettaSogg', $this->nameForm . '_paneId');
        $this->componentBtaNoteModel->openNuovo();
        Out::tabSelect($this->nameForm . '_tabDettagli', $this->nameForm . '_Soggetto');
        Out::valore($this->nameForm . '_dataNascita', '');
        Out::hide($this->nameForm . '_divStor');

        if ($this->origin['CLIFOR']) {
            $this->componentSoggModel->openNuovo();
            Out::tabEnable($this->nameForm . '_tabDettagli', $this->nameForm . '_ForCli');
        } else {
            Out::tabDisable($this->nameForm . '_tabDettagli', $this->nameForm . '_ForCli');
        }

        Out::tabDisable($this->nameForm . '_tabDettagli', $this->nameForm . '_soggfe');
//        Out::enableField($this->nameForm . '_BTA_SOGG[TIPOPERS]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[COGNOME]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[NOME]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[SESSO]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[GIORNO]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[MESE]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[ANNO]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[CODNAZPRO]');
//        Out::enableField($this->nameForm . '_BTA_SOGG[CODLOCAL]');
    }

    private function allineaCampiRicercaDb() {
        // popola i campi usati per la ricerca su db NOME_RIC e RAGSOC_RIC
        $nominativo = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COGNOME'] . " " . $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NOME'];
        $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NOME_RIC'] = cwbLibCalcoli::calcNomeRic($nominativo);
        $ragsoc = $nominativo;
        if (!$_POST[$this->nameForm . '_' . $this->TABLE_NAME]['RAGSOC']) {
            $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['RAGSOC'] = $ragsoc;
        }
        $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['RAGSOC_RIC'] = cwbLibCalcoli::calcNomeRic($ragsoc);

        $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['DATAINIZ'] = cwbParGen::getDataElaborazione();
        if (empty($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPOVALID']))
            $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPOVALID'] = 99;
    }

    protected function preAggiungi() {
        $this->getDataNascita();

        $this->allineaCampiRicercaDb();

        $this->valorizzaProvenienzaDB($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROVEN']); // valorizzo manualmente il campo PROVEN

        $this->gestisciOperazioniRelations();
    }

    protected function preAggiorna() {
        $this->getDataNascita();

        $this->allineaCampiRicercaDb();

        $this->valorizzaProvenienzaDB($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROVEN']); // valorizzo manualmente il campo PROVEN

        $this->gestisciOperazioniRelations();
    }

    protected function preConfermaCancella() {
        $this->gestisciOperazioniRelations();
    }
    
    //  Questo metodo serve al cancella per selezionare le giuste righe di dettaglio documento.
    protected function loadRelationsHook() {
        $this->componentBtaNoteModel->openDettaglio($this->CURRENT_RECORD, false);
        if ($this->origin['CLIFOR'])
            $this->componentSoggModel->openDettaglio($this->CURRENT_RECORD, false);
    }

    private function gestisciOperazioniRelations() {
        if (isSet($this->componentSoggModel)) {
            foreach ($this->componentSoggModel->getRelations() as $relation) {
                $this->addDescribeRelation($relation['table'], $relation['foreignKey'], $relation['type'], $relation['alias']);
            }
        }
        if (isSet($this->componentBtaNoteModel)) {
            $relation = $this->componentBtaNoteModel->getRelation();
            $this->addDescribeRelation($relation['table'], $relation['foreignKey'], $relation['type'], $relation['alias']);
        }
        
        $this->cleanOperationData();

        if ($this->componentSoggModel->getActiveCliFor() === true) {
            foreach ($this->componentSoggModel->getOperations() as $operation) {
                switch ($operation['operation']) {
                    case itaModelService::OPERATION_INSERT:
                        $this->addInsertOperation($operation['table'], $operation['alias']);

                        if ($operation['table'] === 'FTA_NOTE') {
                            $this->componentSoggModel->updatePrognote();
                        }
                        break;
                    case itaModelService::OPERATION_UPDATE:
                        $this->addUpdateOperation($operation['table'], $operation['keys'], $operation['alias']);
                        break;
                    case itaModelService::OPERATION_DELETE:
                        $this->addDeleteOperation($operation['table'], $operation['keys'], $operation['alias']);
                        break;
                }
            }
        }

        $operations = $this->componentBtaNoteModel->getOperations();
        if(is_array($operations)){
            foreach($operations as $operation){
//        foreach ($this->componentBtaNoteModel->getOperations() as $operation) {
                switch ($operation['operation']) {
                    case itaModelService::OPERATION_INSERT:
                        $this->addInsertOperation($operation['table'], $operation['alias']);

                        if (empty($_POST[$this->nameForm . '_BTA_SOGG']['PROGNOTE'])) {
                            Out::valore($this->nameForm . '_BTA_SOGG[PROGNOTE]', 'new');
                            $_POST[$this->nameForm . '_BTA_SOGG']['PROGNOTE'] = 'new';
                            $this->formData[$this->nameForm . '_BTA_SOGG']['PROGNOTE'] = 'new';
                        }
                        break;
                    case itaModelService::OPERATION_UPDATE:
                        $this->addUpdateOperation($operation['table'], $operation['keys'], $operation['alias']);
                        break;
                    case itaModelService::OPERATION_DELETE:
                        $this->addDeleteOperation($operation['table'], $operation['keys'], $operation['alias']);
                        break;
                }
            }
        }
    }
    
    protected function getDataRelation($operation = null) {
        $return = array();

        if ($this->componentSoggModel->getActiveCliFor() === true) {
            foreach ($this->componentSoggModel->getDataFromDB() as $component) {
                if (!empty($component['data'])) {
                    if (!isSet($return[$component['alias']])) {
                        $return[$component['alias']] = array();
                    }
                    $return[$component['alias']] = $component['data'];
                }
            }
        }

        $component = $this->componentBtaNoteModel->getDataFromDB();
        if (!empty($component['data'])) {
            if (!isSet($return[$component['alias']])) {
                $return[$component['alias']] = array();
            }
            $return[$component['alias']] = $component['data'];
        }

        return $return;
    }

    protected function getDataRelationView($tableName, $alias = null) {
        if ($tableName === $this->TABLE_NAME_NOTE) {
            return $this->componentBtaNoteModel->getData();
        } else {
            $return = false;
            if ($this->componentSoggModel->getActiveCliFor() === true) {
                $return = $this->componentSoggModel->getDataRelationView($tableName, $alias);
                if ($return !== false) {
                    return $return;
                }
            }
        }
    }
    
    protected function postDettaglio($index) {
        $this->dittaindiv = $this->CURRENT_RECORD['DITTAINDIV'];

        cwbParGen::setFormSessionVar($this->nameForm, 'CURRENT_RECORD', $this->CURRENT_RECORD); // salvo current_record in sessione.
        Out::show($this->nameForm . '_BTA_SOGG[PROGSOGG]_field');
        Out::hide($this->nameForm . '_QUALIVEDO'); // NASCONDO COMBO "PRESENTE IN:"... DEVO MOSTRARLA SOLO QUANDO VEDO LA GRID.
        Out::hide($this->nameForm . '_Storicizza');
        Out::hide($this->nameForm . '_spanEliminabile');
        if ($this->CURRENT_RECORD['FLAG_DIS']) {
            Out::show($this->nameForm . '_spanEliminabile');
        }
        $this->tipoPersona($this->CURRENT_RECORD['TIPOPERS']); // abilito/disabilito campi in base al tipo persona
        if ($this->CURRENT_RECORD['TIPOPERS'] == 'F') {
            $this->codFiscProvDef($this->CURRENT_RECORD['PROV_DEF']); // abilito/disabilito campi in base al codice fiscale
            $row = $this->decodLocal($this->CURRENT_RECORD['CODNAZPRO'], $this->CURRENT_RECORD['CODLOCAL'], $this->nameForm . '_BTA_SOGG[CODNAZPRO]', $this->nameForm . '_BTA_SOGG[CODLOCAL]', null, ($this->nameForm . '_DESLOCAL_decod'));
            Out::valore($this->nameForm . '_COD_BELFIORE', $row['CODBELFI']);
            $this->decodNazion($this->CURRENT_RECORD['CODNAZI'], ($this->nameForm . '_BTA_SOGG[CODNAZI]'), null, ($this->nameForm . '_DESNAZI_decod'));

            $anno = str_pad($this->CURRENT_RECORD['ANNO'], 4, '0', STR_PAD_LEFT);
            $mese = str_pad($this->CURRENT_RECORD['MESE'], 2, '0', STR_PAD_LEFT);
            $giorno = str_pad($this->CURRENT_RECORD['GIORNO'], 2, '0', STR_PAD_LEFT);
            Out::valore($this->nameForm . '_dataNascita', $anno . '-' . $mese . '-' . $giorno);
        } else {
            Out::valore($this->nameForm . '_BTA_SOGG[GIORNO]', '');
            Out::valore($this->nameForm . '_BTA_SOGG[MESE]', '');
            Out::valore($this->nameForm . '_BTA_SOGG[ANNO]', '');
            Out::valore($this->nameForm . '_dataNascita', '');
            Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', '');
            Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', '');

            Out::valore($this->nameForm . '_DESLOCAL_decod', '');
        }
        Out::tabSelect($this->nameForm . '_tabDettaSogg', $this->nameForm . '_paneId');
        $this->valorizzaProvenienza($this->CURRENT_RECORD['PROVEN']); // valorizzo manualmente il campo PROVEN
        $this->caricaStorico();
        Out::hide($this->nameForm . '_divGesStorico');
        Out::show($this->nameForm . '_divPresenteBtn');
        Out::tabSelect($this->nameForm . '_tabDettaSogg', $this->nameForm . '_paneId');

        Out::show($this->nameForm . '_divStor');

        // TODO: in OMNIS, quando doppio clicco la grid, parte il metodo $visual_selez che credo serva per capire
        // che tipo di dettaglio deve aprire e quindi da chi è richiamata (es. da Menu, da Anagrafe, da Tributi ecc.).
        // Il metodo $visual_selez richiama inoltre il metodo $set_param; questo metodo a sua volta richiama "set_presenti".
        // set_presenti serve per capire se l'elemento scelto dalla grid è presente in Anagrafe (controlla inoltre lo stato in Anagrafe),
        //  Tributi, Finanziaria ecc...
        // All'inizio dell'evento set_presenti, controlla se sono o in modifica o in cancellazione.... se sono in questi due casi,
        // non fa controllo su aree. Controllo dove presente solo se sono in modifica.

        $this->set_presenti($this->CURRENT_RECORD);

        $viewMode = ($this->viewMode || !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_WRITE));

        $this->componentBtaNoteModel->openDettaglio($this->CURRENT_RECORD, $viewMode);

        if ($this->CURRENT_RECORD['FORNIT']) {
            $this->componentSoggModel->openDettaglio($this->CURRENT_RECORD, $viewMode);
            Out::tabEnable($this->nameForm . '_tabDettagli', $this->nameForm . '_ForCli');
        } elseif ($this->origin['CLIFOR']) {
            $this->componentSoggModel->openNuovo();
//            $this->componentSoggModel->changeDittaIndividuale($this->CURRENT_RECORD['DITTAINDIV']);
            $this->componentSoggModel->changeDittaIndividuale($this->CURRENT_RECORD);
            $this->componentSoggModel->setParamFtaResidDaAnagra($this->CURRENT_RECORD);
            $this->componentSoggModel->setProgsogg($this->CURRENT_RECORD);
            Out::tabEnable($this->nameForm . '_tabDettagli', $this->nameForm . '_ForCli');
        } else {
            $this->componentSoggModel->resetData();
            Out::tabDisable($this->nameForm . '_tabDettagli', $this->nameForm . '_ForCli');
        }
        Out::tabSelect($this->nameForm . '_tabDettagli', $this->nameForm . '_Soggetto');

        Out::tabEnable($this->nameForm . '_tabDettagli', $this->nameForm . '_soggfe');

        Out::hide($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::hide($this->nameForm . '_btnDurc');
        Out::hide($this->nameForm . '_btnAllegati');

        if ($this->CURRENT_RECORD['FATT_SDI'] == 0 || $this->CURRENT_RECORD['FATT_SDI'] == 1) {
            Out::valore($this->nameForm . '_FATT_SDI_SPLIT', 0);
        } else {
            Out::valore($this->nameForm . '_FATT_SDI_SPLIT', 1);  // FATT_SDI = 2
        }
    }

    protected function postDettaglioStorico($index) {
        $this->decodLocal($this->CURRENT_RECORD['CODNAZPRO'], $this->CURRENT_RECORD['CODLOCAL'], ($this->nameForm . '_DESLOCAL_decod_st'));
        $this->decodNaz($this->CURRENT_RECORD['CODNAZI'], ($this->nameForm . '_BTA_SOGGST[CODNAZI]'), ($this->nameForm . '_DESNAZI_decod_st'));
        Out::attributo($this->nameForm . '_BTA_SOGGST[TIPOPERS]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_SOGGST[SESSO]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_SOGGST[PROV_DEF]', 'readonly', '0');
        Out::attributo($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BTA_SOGG[PROGSOGG]');

        Out::hide($this->nameForm . '_btnUfficiFE');
    }

    public function postElenca() {
        if (!isSet($this->formData[$this->nameForm . '_RIC_STOR']) || $this->formData[$this->nameForm . '_RIC_STOR'] == 1) {
            Out::hide($this->nameForm . '_divGridStorico');
            Out::show($this->nameForm . '_divGridAttuali');
            Out::gridForceResize($this->nameForm . '_' . $this->GRID_NAME);
        } else {
            Out::show($this->nameForm . '_divGridStorico');
            Out::hide($this->nameForm . '_divGridAttuali');
            Out::gridForceResize($this->nameForm . '_' . $this->GRID_NAME_STORICO);
        }
        if ($this->externalParams['lookupDanCoda']) {
            Out::hide($this->nameForm . '_divPresenteBtn');
            Out::hide($this->nameForm . '_RIC_STOR');
            Out::hide($this->nameForm . '_divGridStorico');
            Out::show($this->nameForm . '_divGridAttuali');
            Out::gridForceResize($this->nameForm . '_' . $this->GRID_NAME);
        } else {
            Out::show($this->nameForm . '_divPresenteBtn');
        }

        if (!empty($this->msgBlock)) {
            Out::msgBlock('', 5000, false, $this->msgBlock);
            unset($this->msgBlock);
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if (!empty($_POST['PROGSOGG'])) {
            $this->gridFilters['PROGSOGG'] = $this->formData['PROGSOGG'];
        }
        if (!empty($_POST['PROGSOGG_ST'])) {
            $this->gridFilters['PROGSOGG'] = $this->formData['PROGSOGG_ST'];
        }
        if (!empty($_POST['COGNOME'])) {
            $nomeRic = cwbLibCalcoli::calcNomeRic($this->formData['COGNOME']);
//            $this->gridFilters['NOME_RIC'] = '%' . str_replace(' ', '', $this->formData['COGNOME']);
            $this->gridFilters['NOME_RIC'] = '';
            $this->gridFilters['RAGSOC_RIC'] = '';
            $this->gridFilters['NOME_RIC_OR_RAGSOC_RIC'] = '%' . $nomeRic;
        }
        if (!empty($_POST['COGNOME_ST'])) {
            $nomeRic = cwbLibCalcoli::calcNomeRic($this->formData['COGNOME_ST']);
//            $this->gridFilters['NOME_RIC'] = '%' . str_replace(' ', '', $this->formData['COGNOME_ST']);
            $this->gridFilters['NOME_RIC'] = '';
            $this->gridFilters['RAGSOC_RIC'] = '';
            $this->gridFilters['NOME_RIC_OR_RAGSOC_RIC'] = '%' . $nomeRic;
        }

        if (!empty($_POST['TIPOPERS'])) {
            $this->gridFilters['TIPOPERS'] = $this->formData['TIPOPERS'];
        }
        if (!empty($_POST['TIPOPERS_ST'])) {
            $this->gridFilters['TIPOPERS'] = $this->formData['TIPOPERS_ST'];
        }

        if (!empty($_POST['DITTAINDIV'])) {
            $this->gridFilters['DITTAINDIV'] = $this->formData['DITTAINDIV'] - 1;
        }
        if (!empty($_POST['DITTAINDIV_ST'])) {
            $this->gridFilters['DITTAINDIV'] = $this->formData['DITTAINDIV_ST'] - 1;
        }

        if (!empty($_POST['SESSO'])) {
            $this->gridFilters['SESSO'] = $this->formData['SESSO'];
        }
        if (!empty($_POST['SESSO_ST'])) {
            $this->gridFilters['SESSO'] = $this->formData['SESSO_ST'];
        }

        if (!empty($_POST['DESLOCAL'])) {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL'];
        }
        if (!empty($_POST['DESLOCAL_ST'])) {
            $this->gridFilters['DESLOCAL'] = $this->formData['DESLOCAL_ST'];
        }

        if (!empty($_POST['CODFISCALE'])) {
            $this->gridFilters['CODFISCALE'] = $this->formData['CODFISCALE'];
        }
        if (!empty($_POST['CODFISCALE_ST'])) {
            $this->gridFilters['CODFISCALE'] = $this->formData['CODFISCALE_ST'];
        }

        if (!empty($_POST['PARTIVA'])) {
            $this->gridFilters['PARTIVA'] = $this->formData['PARTIVA'];
        }
        if (!empty($_POST['PARTIVA_ST'])) {
            $this->gridFilters['PARTIVA'] = $this->formData['PARTIVA_ST'];
        }
        if (!empty($_POST['GIORNO'])) {
            $this->gridFilters['GIORNO'] = $this->formData['GIORNO'];
        }
        if (!empty($_POST['MESE'])) {
            $this->gridFilters['MESE'] = $this->formData['MESE'];
        }
        if (!empty($_POST['ANNO'])) {
            $this->gridFilters['ANNO'] = $this->formData['ANNO'];
        }

        if (!empty($_POST['PRESENTEIN'])) {
            switch ($this->formData['PRESENTEIN']) {
                case 'ANAGRA':
                    $this->gridFilters['ANAGRA'] = true;
                    break;
                case 'CONTRIB':
                    $this->gridFilters['CONTRIB'] = true;
                    break;
                case 'FORNIT':
                    $this->gridFilters['FORNIT'] = true;
                    break;
                case 'SOGGISCR':
                    $this->gridFilters['SOGGISCR'] = true;
                    break;
                case 'AMMINISTR':
                    $this->gridFilters['AMMINISTR'] = true;
                    break;
            }
        }
        if (!empty($_POST['PRESENTEIN_ST'])) {
            switch ($this->formData['PRESENTEIN_ST']) {
                case 'ANAGRA':
                    $this->gridFilters['ANAGRA'] = true;
                    break;
                case 'CONTRIB':
                    $this->gridFilters['CONTRIB'] = true;
                    break;
                case 'FORNIT':
                    $this->gridFilters['FORNIT'] = true;
                    break;
                case 'SOGGISCR':
                    $this->gridFilters['SOGGISCR'] = true;
                    break;
                case 'AMMINISTR':
                    $this->gridFilters['AMMINISTR'] = true;
                    break;
            }
        }
    }

    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
//        Out::valore('gs_PROGSOGG','');
//        Out::valore('gs_COGNOME','');
//        Out::valore('gs_TIPOPERS','');
//        Out::valore('gs_DITTAINDIV','');
//        Out::valore('gs_SESSO','');
//        Out::valore('gs_DESLOCAL','');
//        Out::valore('gs_CODFISCALE','');
//        Out::valore('gs_PARTIVA','');
//        Out::valore('gs_PRESENTEIN','');
//        Out::valore('gs_PROGSOGG_ST','');
//        Out::valore('gs_COGNOME_ST','');
//        Out::valore('gs_TIPOPERS_ST','');
//        Out::valore('gs_DITTAINDIV_ST','');
//        Out::valore('gs_SESSO_ST','');
//        Out::valore('gs_DESLOCAL_ST','');
//        Out::valore('gs_CODFISCALE_ST','');
//        Out::valore('gs_PARTIVA_ST','');
//        Out::valore('gs_PRESENTEIN_ST','');
        Out::hide($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::hide($this->nameForm . '_btnDurc');
        Out::hide($this->nameForm . '_btnAllegati');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        Out::show($this->nameForm . '_QUALIVEDO');        // MOSTRO COMBO "PRESENTE IN:"... DEVO MOSTRARLA SOLO QUANDO VEDO LA GRID.
        switch ($_POST[$this->nameForm . '_QUALIVEDO']) {  // Switch in base al valore della combo presente .
            case 2:
                $filtri['ANAGRA'] = 1;  // mi interessa solamente che sia valorizzato, non importa come. Nella query controllo solo
                // se l'array esiste. Se esisto aggiungo alla condizione "ANAGRA IS NOT NULL".
                break;
            case 3:
                $filtri['CONTRIB'] = 1;
                break;
            case 4:
                $filtri['FORNIT'] = 1;
                break;
            case 5:
                $filtri['SOGGISCR'] = 1;
                break;
            case 6:
                $filtri['AMMINISTR'] = 1;
                break;
        }
        $nomeRic = cwbLibCalcoli::calcNomeRic($this->formData[$this->nameForm . '_NOME_RIC']);
        if (empty($this->formData[$this->nameForm . '_RIC_CLFOR'])) {
//            $filtri['NOME_RIC'] = str_replace(' ', '', $this->formData[$this->nameForm . '_NOME_RIC']);
            $filtri['NOME_RIC'] = '%' . $nomeRic;
        } else {
//            $filtri['RAGSOC_RIC'] = str_replace(' ', '', $this->formData[$this->nameForm . '_NOME_RIC']);
            $filtri['NOME_RIC_OR_RAGSOC_RIC'] = '%' . $nomeRic;
        }
        $filtri['PROGSOGG'] = trim($this->formData[$this->nameForm . '_PROGSOGG']);
        $filtri['TIPOPERS'] = trim($this->formData[$this->nameForm . '_TIPOPERS']);
        $filtri['SESSO'] = trim($this->formData[$this->nameForm . '_SESSO']);
        $filtri['CODFISCALE'] = trim($this->formData[$this->nameForm . '_CODFISCALE']);
        $filtri['PARTIVA'] = trim($this->formData[$this->nameForm . '_PARTIVA']);
        $filtri['GIORNO'] = trim($this->formData[$this->nameForm . '_GIORNO']);
        $filtri['MESE'] = trim($this->formData[$this->nameForm . '_MESE']);
        $filtri['ANNO'] = trim($this->formData[$this->nameForm . '_ANNO']);
        $filtri['FL_DATAFINE'] = trim($this->formData[$this->nameForm . '_FL_DATAFINE']);  //04-2019
        if ($this->externalParams['lookupDanCoda']) {
            $this->soloAttuali = 0;
        }
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaSogg($filtri, false, $this->soloAttuali, $sqlParams);
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_COGNOME_RAGSOC', '');
        Out::valore($this->nameForm . '_DESLOCAL_decod', '');
        Out::valore($this->nameForm . '_DESNAZI_decod', '');
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaSoggChiave($index, $sqlParams);
    }

    protected function dettaglioStorico($index) {
        // Effettua il caricamento del dettaglio
        // Questa operazione viene saltata nel caso delle finestre di gestione del singolo record
        if ($index != null) {
            $this->loadCurrentRecordStorico($index);
        }

        $this->bloccaChiave($this->CURRENT_RECORD);
        if ($this->LOCK['status'] != 0) {
            Out::msgStop('Errore', 'Record bloccato in modifica da un altro operatore');
            return;
        }


        $this->recordInfo(itaModelService::OPERATION_OPENRECORD, $this->CURRENT_RECORD);
        $this->openRecord($this->MAIN_DB, $this->TABLE_NAME_STORICO, $this->RECORD_INFO);
        Out::valori($this->CURRENT_RECORD, $this->nameForm . '_' . $this->TABLE_NAME_STORICO);

        // Refresh array
        // Consente di avere i dati aggiornati sia nella request corrente che in quella successiva
        $_POST[$this->nameForm . '_' . $this->TABLE_NAME_STORICO] = array();
        $_POST[$this->nameForm . '_' . $this->TABLE_NAME_STORICO] = array_merge($_POST[$this->nameForm . '_' . $this->TABLE_NAME_STORICO], $this->CURRENT_RECORD);

        // se sei abilitato alla sola lettura vengono disabilitati tutti i campi figli del div di gestione
//        if ($this->authenticator->isActionAllowed(itaAuthenticator::ACTION_READ)) {
//            Out::codice("$('#" . $this->nameForm . "_divGestione').find('*').attr('disabled', true);");
//        }

        $this->setVisDettaglio();
        TableView::disableEvents($this->nameForm . '_' . $this->GRID_NAME_STORICO);
        $this->controlliAuditDettaglio(itaModelService::OPERATION_UPDATE);
        $this->postDettaglioStorico($index);
    }

    protected function loadCurrentRecordStorico($index) {
        $this->sqlDettaglioStorico($index, $sqlParams);
        $this->CURRENT_RECORD = ItaDB::DBQuery($this->MAIN_DB, $this->SQL, false, $sqlParams);
    }

    public function sqlDettaglioStorico($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaSoggstChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        $cellcontent = ' ';
        // salvo path icone in variabile
        $path_ico_ditta = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_101512-16x16.png'; // icona quando Ditta indiv.
        $path_ico_presanagr = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_103500-16x16.png'; // icona quando presente in Anagrafe
        $path_ico_pretrib = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_103501-16x16.png'; // icona quando presente in Tributi.
        $path_ico_prefornit = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_103502-16x16.png'; // icona quando presente in Fornitori/Clienti.
        $path_ico_presoggiscr = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_103503-16x16.png'; // icona quando presente in Serv.Dom.Ind.
        $path_ico_preamm = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_103601-16x16.png'; // icona quando presente in Atti Amministr..
        $path_ico_vuota = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_110006-16x16.png'; // icona vuota

        $storico = ($this->formData[$this->nameForm . '_RIC_STOR'] != 1);

        if ($this->origin['CLIFOR']) {
            Out::gridShowCol($this->nameForm . '_' . $this->GRID_NAME, 'ALLEG');
        } else {
            Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'ALLEG');
        }
        if (is_array($Result_tab)) {
            foreach ($Result_tab as $key => $Result_rec) {
                if ($Result_rec['TIPOPERS'] == 'F' && !$Result_rec['DITTAINDIV']) {  // 02-04-2019
                    $Result_tab[$key]['COGNOME'] = cwbLibHtml::formatDataGridCod($Result_rec['COGNOME'] . " " . $Result_rec['NOME']);
                } else {  // 02-04-2019
                    $Result_tab[$key]['COGNOME'] = cwbLibHtml::formatDataGridCod($Result_rec['RAGSOC']); // 02-04-2019
                }  // 02-04-2019
                $Result_tab[$key]['RAGSOC'] = cwbLibHtml::formatDataGridCod($Result_rec['RAGSOC']);

                if ($this->origin['CLIFOR']) {
                    $btaSoggal = $this->libDB->leggiGeneric('BTA_SOGGAL', array('PROGSOGG' => $Result_rec['PROGSOGG']), true);
                    if (!empty($btaSoggal)) {
                        $numAlleg = count($btaSoggal);
                        if ($numAlleg > 0) {
                            $titleAlleg = 'Allegati: ' . $numAlleg . ', Click per visualizzare gli allegati';
                            $alleg = $this->crea_cliccabile($this->prefisso_ALLEG . $Result_rec['PROGSOGG'], $titleAlleg, 'ui-icon-mail-attachment');
//                            $Result_tab[$key]['COGNOME'] = '<div>' . $Result_tab[$key]['COGNOME']
//                            . ' ' . $alleg . ' </div>';
//                            $Result_tab[$key]['RAGSOC'] = '<div>' . $Result_tab[$key]['RAGSOC']
//                            . ' ' . $alleg . ' </div>';
                            $Result_tab[$key]['ALLEG'] = '<div>' . $alleg . ' </div>';
                        }
                    }
                }  //  if ($this->origin['CLIFOR']) {
                switch ($Result_rec['TIPOPERS']) {
                    case 'F': $Result_tab[$key]['TIPOPERS'] = 'Fisica';
                        break;
                    case 'G': $Result_tab[$key]['TIPOPERS'] = 'Giuridica';
                        break;
                    default: $Result_tab[$key]['TIPOPERS'] = '';
                        break;
                }

                $Result_tab[$key]['DITTAINDIV'] = (!empty($Result_rec['DITTAINDIV']) ? cwbLibHtml::formatDataGridIcon('', $path_ico_ditta) : '');

                switch ($Result_rec['SESSO']) {
                    case 'M': $Result_tab[$key]['SESSO'] = 'Maschio';
                        break;
                    case 'F': $Result_tab[$key]['SESSO'] = 'Femmina';
                        break;
                    default: $Result_tab[$key]['SESSO'] = '';
                        break;
                }

                $giorno = (!empty($Result_rec['GIORNO']) ? str_pad($Result_rec['GIORNO'], 2, '0', STR_PAD_LEFT) : '--');
                $mese = (!empty($Result_rec['MESE']) ? str_pad($Result_rec['MESE'], 2, '0', STR_PAD_LEFT) : '--');
                $anno = $Result_rec['ANNO'];
                if (!empty($Result_rec['ANNO'])) {
                    $Result_tab[$key]['DATANA'] = $giorno . '/' . $mese . '/' . $anno;
                } else {
                    $Result_tab[$key]['DATANA'] = '';
                }
//  02-04-2019 Modifica: se è un Soggetto Finanziaria se c'è mette la data cessazione
                $soggForn = false;
                if ($this->origin['CLIFOR']) {
                    $ftaClfor = $this->libDB->leggiGeneric('FTA_CLFOR', array('PROGSOGG' => $Result_rec['PROGSOGG']), false);
                    if (!empty($ftaClfor) && count($ftaClfor) > 0 && !empty($ftaClfor['DATAFINE'])) {
                        $dataCess = new DateTime($ftaClfor['DATAFINE']);
                        $title = "Data cessazione Cliente-Fornitore il " . $dataCess->format('d/m/Y');
                        $Result_tab[$key]['DATAMORTE'] = '<div style="text-align:left;" '
                                . 'title="' . $title . '">'
                                . 'F. ' . $dataCess->format('d/m/Y') . '</div>';
                        $soggForn = true;
                    }
                }
                if (!$soggForn) {
                    if (!empty($Result_rec['DATAMORTE'])) {
                        $dataMorte = new DateTime($Result_rec['DATAMORTE']);
                        if ($Result_rec['TIPOPERS'] == 'F' && $Result_rec['DITTAINDIV'] <> 1) {
                            $title = "Deceduto il " . $dataMorte->format('d/m/Y');
                            $Result_tab[$key]['DATAMORTE'] = '<div style="text-align:left;" '
                                    . 'title="' . $title . '">'
                                    . 'D. ' . $dataMorte->format('d/m/Y') . '</div>';
                        } else {
                            $title = "Cessata attività il " . $dataMorte->format('d/m/Y');
                            $Result_tab[$key]['DATAMORTE'] = '<div style="text-align:left;" '
                                    . 'title="' . $title . '">'
                                    . 'C. ' . $dataMorte->format('d/m/Y') . '</div>';
                        }
                    } else {
                        $Result_tab[$key]['DATAMORTE'] = ' ';
                    }
                }
                $cellcontent = ($Result_tab[$key]['ANAGRA'] != NULL ? cwbLibHtml::formatDataGridIcon('', $path_ico_presanagr) : cwbLibHtml::formatDataGridIcon('', $path_ico_vuota));
                $cellcontent_2 = ($Result_tab[$key]['CONTRIB'] != NULL ? cwbLibHtml::formatDataGridIcon('', $path_ico_pretrib) : cwbLibHtml::formatDataGridIcon('', $path_ico_vuota));
                $cellcontent_3 = ($Result_tab[$key]['FORNIT'] != NULL ? cwbLibHtml::formatDataGridIcon('', $path_ico_prefornit) : cwbLibHtml::formatDataGridIcon('', $path_ico_vuota));
                $cellcontent_4 = ($Result_tab[$key]['SOGGISCR'] != NULL ? cwbLibHtml::formatDataGridIcon('', $path_ico_presoggiscr) : cwbLibHtml::formatDataGridIcon('', $path_ico_vuota));
                $cellcontent_5 = ($Result_tab[$key]['AMMINISTR'] != NULL ? cwbLibHtml::formatDataGridIcon('', $path_ico_preamm) : cwbLibHtml::formatDataGridIcon('', $path_ico_vuota));
                $Result_tab[$key]['PRESENTEIN'] = $cellcontent . $cellcontent_2 . $cellcontent_3 . $cellcontent_4 . $cellcontent_5;

                if ($storico) {
                    $Result_tab[$key]['COGNOME'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($Result_tab[$key]['COGNOME_ST'] . " " . $Result_tab[$key]['NOME_ST'], 'red'), "italic") . ' ';

                    switch ($Result_rec['TIPOPERS_ST']) {
                        case 'F': $Result_tab[$key]['TIPOPERS'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor('Fisica', 'red'), "italic") . ' ';
                            break;
                        case 'G': $Result_tab[$key]['TIPOPERS'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor('Giuridica', 'red'), "italic") . ' ';
                            break;
                        default: $Result_tab[$key]['TIPOPERS'] .= '<br> ';
                            break;
                    }

                    switch ($Result_rec['SESSO_ST']) {
                        case 'M': $Result_tab[$key]['SESSO'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor('Maschio', 'red'), "italic") . ' ';
                            break;
                        case 'F': $Result_tab[$key]['SESSO'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor('Femmina', 'red'), "italic") . ' ';
                            break;
                        default: $Result_tab[$key]['SESSO'] .= '<br> ';
                            break;
                    }

                    $giorno = (!empty($Result_rec['GIORNO_ST']) ? str_pad($Result_rec['GIORNO_ST'], 2, '0', STR_PAD_LEFT) : '--');
                    $mese = (!empty($Result_rec['MESE_ST']) ? str_pad($Result_rec['MESE_ST'], 2, '0', STR_PAD_LEFT) : '--');
                    $anno = $Result_rec['ANNO_ST'];
                    if (!empty($anno)) {
                        $Result_tab[$key]['DATANA'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($giorno . '/' . $mese . '/' . $anno, 'red'), "italic") . ' ';
                    }

                    $Result_tab[$key]['DESLOCAL'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($Result_rec['DESLOCAL_ST'], 'red'), "italic") . ' ';
                    $Result_tab[$key]['CODFISCALE'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($Result_rec['CODFISCALE_ST'], 'red'), "italic") . ' ';
                    $Result_tab[$key]['PARTIVA'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($Result_rec['PARTIVA_ST'], 'red'), "italic") . ' ';

                    if (!empty($Result_rec['DATAVARIAZ_ST'])) {
                        $dataVariazione = new DateTime($Result_rec['DATAVARIAZ_ST']);
                        $Result_tab[$key]['DATAMORTE'] .= '<br>' . cwbLibHtml::formatDataGridCod(cwbLibHtml::formatDataGridTextColor($dataVariazione->format('d/m/Y'), 'red'), "italic") . ' ';
                    } else {
                        $Result_tab[$key]['DATAMORTE'] .= '<br> ';
                    }
                }
            }
        }
        return $Result_tab;
    }

    /**
     * Crea Icona INFO (o testo) Cliccabile
     */
    private function crea_cliccabile($key, $tooltip = 'Visualizza Dati', $icona = 'ui-icon-info') {
        $img = "<span class='ui-icon " . $icona . "'></span>"; // Icona standard UI
        $id = $this->nameForm . "_" . $this->prefisso_cliccabili . $key;
        $icona_cliccabile = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $img, array(), $tooltip);
        return $icona_cliccabile;
    }

    private function decodNazion($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaNazion", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODNAZI", $desValue, $desField, "DESNAZI", 'returnFromBtaNazion', $_POST['id'], $searchButton);
    }

    public function postTornaElenco() {
        Out::show($this->nameForm . '_QUALIVEDO'); // MOSTRO COMBO "PRESENTE IN:"... DEVO MOSTRARLA SOLO QUANDO VEDO LA GRID.
        Out::show($this->nameForm . '_divPresenteBtn');

        $this->componentSoggModel->unlockRecords();
    }

    private function TornaGrid() {
        Out::hide($this->nameForm . '_divGesStorico');
        Out::show($this->nameForm . '_divGridStoricoVar');
    }

    private function caricaStorico() {

        $filtri = array();
        $filtri['PROGSOGG'] = $_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGSOGG'];
        $result_tab = $this->libDB->leggiBtaSoggst($filtri);

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($this->nameForm);
        $helper->setGridName($this->GRID_NAME_STORICO);

        $res = $this->elaboraRecordsStorico($result_tab);

        $ita_grid01 = $helper->initializeTableArray($res);

        if (!$ita_grid01->getDataPage('json')) {
            TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_STORICO);
        } else {
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_STORICO);
        }
    }

    private function initComboPresente() {
        // Quali vedo?
        Out::select($this->nameForm . '_QUALIVEDO', 1, "1", 1, "Tutti");
        Out::select($this->nameForm . '_QUALIVEDO', 1, "2", 0, "Anagrafe");
        Out::select($this->nameForm . '_QUALIVEDO', 1, "3", 0, "Tributi");
        Out::select($this->nameForm . '_QUALIVEDO', 1, "4", 0, "Fornitori/Clienti");
        Out::select($this->nameForm . '_QUALIVEDO', 1, "5", 0, "Serv.Dom.Ind.");
        Out::select($this->nameForm . '_QUALIVEDO', 1, "6", 0, "Atti Ammin.");
    }

    private function initComboTipo() {
        // Tipo persona
        Out::select($this->nameForm . '_TIPOPERS', 1, " ", 1, " ");
        Out::select($this->nameForm . '_TIPOPERS', 1, "F", 0, "Fisica");
        Out::select($this->nameForm . '_TIPOPERS', 1, "G", 0, "Giuridica");

        Out::select($this->nameForm . '_BTA_SOGG[TIPOPERS]', 1, "F", 1, "Fisica");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOPERS]', 1, "G", 0, "Giuridica");

        // Tipo persona "dati storico"
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOPERS]', 1, "F", 1, "Fisica");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOPERS]', 1, "G", 0, "Giuridica");
    }

    private function initComboSesso() {
        // Sesso
        Out::select($this->nameForm . '_SESSO', 1, " ", 1, " ");
        Out::select($this->nameForm . '_SESSO', 1, "M", 0, "Maschio");
        Out::select($this->nameForm . '_SESSO', 1, "F", 0, "Femmina");

        Out::select($this->nameForm . '_BTA_SOGG[SESSO]', 1, "M", 1, "Maschio");
        Out::select($this->nameForm . '_BTA_SOGG[SESSO]', 1, "F", 0, "Femmina");

        // Sesso "dati storico"
        Out::select($this->nameForm . '_BTA_SOGGST[SESSO]', 1, "M", 1, "Maschio");
        Out::select($this->nameForm . '_BTA_SOGGST[SESSO]', 1, "F", 0, "Femmina");
    }

    private function initComboDef() {
        // Combo codice fiscale Definitivo o Provvisorio 
        Out::select($this->nameForm . '_BTA_SOGG[PROV_DEF]', 1, "P", 1, "Provvisorio");
        Out::select($this->nameForm . '_BTA_SOGG[PROV_DEF]', 1, "D", 0, "Definitivo");

        // Combo codice fiscale Definitivo o Provvisorio Storico
        Out::select($this->nameForm . '_BTA_SOGGST[PROV_DEF]', 1, "P", 1, "Provvisorio");
        Out::select($this->nameForm . '_BTA_SOGGST[PROV_DEF]', 1, "D", 0, "Definitivo");
    }

    private function initComboRic() {
        // Ricerca su 
        Out::select($this->nameForm . '_RIC_STOR', 1, "1", 1, "Solo dati attuali");
        Out::select($this->nameForm . '_RIC_STOR', 1, "2", 0, "Dati attuali + storico");
        Out::select($this->nameForm . '_RIC_STOR', 1, "3", 0, "Solo storico");
    }

    private function decodLocal($codNazpro, $codLocal, $codNazproField, $codLocalField, $desValue, $desField, $search = false) {
        $result = cwbLib::decodificaLookup("cwbBtaLocal", $this->nameForm, $this->nameFormOrig, array($codNazpro, $codLocal), array($codNazproField, $codLocalField), array("CODNAZPRO", "CODLOCAL"), $desValue, $desField, "DESLOCAL", "returnFromBtaLocal", $_POST['id'], $search);
        return $result;
    }

    private function decodNaz($cod, $codField, $desField) {
        $row = $this->libDB_BTA->leggiBtaNazionChiave($cod, $sqlParams);
        if ($row) {
            Out::valore($codField, $row['CODNAZI']);
            Out::valore($desField, $row['DESNAZI']);
        } else {
            Out::valore($codField, ' ');
            Out::valore($desField, ' ');
        }
    }

    protected function codFiscProvDef($prov_def) {
        switch ($prov_def) {
            case 'P':
                Out::disableField($this->nameForm . '_BTA_SOGG[TIPOVALID]');
                Out::enableField($this->nameForm . '_BTA_SOGG[CODFISCALE]');
                Out::disableField($this->nameForm . '_BTA_SOGG[DATAVALID]');
                break;

            case 'D':
                Out::enableField($this->nameForm . '_BTA_SOGG[TIPOVALID]');
                Out::disableField($this->nameForm . '_BTA_SOGG[CODFISCALE]');
                Out::enableField($this->nameForm . '_BTA_SOGG[DATAVALID]');
                break;
        }
    }

    protected function tipoPersona($tipoPersona = 'F') {
        switch ($tipoPersona) {
            case 'G':
                Out::hide($this->nameForm . '_BTA_SOGG[SESSO]_field');
                Out::hide($this->nameForm . '_BTA_SOGG[DITTAINDIV]_field');
                Out::disableField($this->nameForm . '_BTA_SOGG[NOME]');
                Out::disableField($this->nameForm . '_dataNascita');
                Out::disableField($this->nameForm . '_BTA_SOGG[CODNAZPRO]');
                Out::disableField($this->nameForm . '_BTA_SOGG[CODLOCAL]');
                Out::disableField($this->nameForm . '_BTA_SOGG[PROV_DEF]');
                Out::disableField($this->nameForm . '_BTA_SOGG[TIPOVALID]');
                Out::disableField($this->nameForm . '_BTA_SOGG[DATAVALID]');

                Out::valore($this->nameForm . '_BTA_SOGG[DITTAINDIV]', 0);
                Out::valore($this->nameForm . '_BTA_SOGG[NOME]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[GIORNO]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[MESE]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[ANNO]', '');
                Out::valore($this->nameForm . '_dataNascita', '');
                Out::valore($this->nameForm . '_BTA_SOGG[CODNAZPRO]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[CODLOCAL]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[PROV_DEF]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[TIPOVALID]', '');
                Out::valore($this->nameForm . '_BTA_SOGG[DATAVALID]', '');

                Out::html($this->nameForm . '_BTA_SOGG[COGNOME]_lbl', "Rag. Sociale");
                break;

            case 'F':
                Out::show($this->nameForm . '_BTA_SOGG[SESSO]_field');
                Out::show($this->nameForm . '_BTA_SOGG[DITTAINDIV]_field');
                Out::enableField($this->nameForm . '_BTA_SOGG[NOME]');
                Out::enableField($this->nameForm . '_BTA_SOGG[GIORNO]');
                Out::enableField($this->nameForm . '_BTA_SOGG[MESE]');
                Out::enableField($this->nameForm . '_BTA_SOGG[ANNO]');
                Out::enableField($this->nameForm . '_dataNascita');
                Out::enableField($this->nameForm . '_BTA_SOGG[CODNAZPRO]');
                Out::enableField($this->nameForm . '_BTA_SOGG[CODLOCAL]');
                Out::enableField($this->nameForm . '_BTA_SOGG[PROV_DEF]');
                Out::enableField($this->nameForm . '_BTA_SOGG[TIPOVALID]');
                Out::enableField($this->nameForm . '_BTA_SOGG[DATAVALID]');

                Out::html($this->nameForm . '_BTA_SOGG[COGNOME]_lbl', "Cognome");
                break;
        }
    }

    protected function elaboraRecordsStorico($Result_tab) {
//        if ($this->soloAttuali == 1) { // se sto cercando solo dati attuali, per sicurezza pulisco grid storico.
//            $Result_tab = ' ';
//        }
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['DATAVARIAZ_3'] = $Result_rec['DATAVARIAZ'];
            $Result_tab[$key]['NOMINATIVO_3'] = $Result_rec['COGNOME'] . " " . $Result_rec['NOME'];
            $Result_tab[$key]['TIPO_3'] = $Result_rec['TIPOPERS'];
            $Result_tab[$key]['SESSO_3'] = $Result_rec['SESSO'];
            $Result_tab[$key]['DATANASCITA_3'] = $Result_rec['GIORNO'] . "/"
                    . $Result_rec['MESE'] . "/" . $Result_rec['ANNO'];
            $Result_tab[$key]['CODFISCALE_3'] = $Result_rec['CODFISCALE'];
            $Result_tab[$key]['PARTIVA_3'] = $Result_rec['PARTIVA'];
        }
        return $Result_tab;
    }

    protected function set_presenti($record) {
        $this->mostraBottoniPresenti($record);
        if ($record['DATAMORTE'] > 0) {
            Out::show($this->nameForm . '_spanCancellato'); // se DATAMORTE >0 mostro lo span.
            if ($record['TIPOPERS'] === 'F') {
                if ($record['SESSO'] === 'M') {
                    Out::html($this->nameForm . '_spanCancellato', 'DECEDUTO');
                } else {
                    Out::html($this->nameForm . '_spanCancellato', 'DECEDUTA');
                }
            } else {
                Out::html($this->nameForm . '_spanCancellato', 'CESSATA ATTIV.');
            }
        } else {
            Out::hide($this->nameForm . '_spanCancellato'); // se DATAMORTE <0 nascondo lo span.
        }
        //TODO CONTROLLO IN ANAGRAFE PER PRENDERE LO STATO
        $results = $this->libDB->leggiDanAnagra($record['PROGSOGG']);
        if ($results) {
            // TODO... DEVO SETTARE VARIABILI CHE IN OMNIS SONO CHIAMATE "iv_pres_ana" E "iv_pres_ana_2" A TRUE... 
            // SERVE PER POI CONTROLLARE AUTORIZZAZIONI. PER ADESSO SETTO VARIABILE SENZA CONTROLLARLA POI SU CHECK_AUTOR
            Out::show($this->nameForm . '_Anagrafe');
            $iv_pres_ana = true;
            $iv_pres_ana_2 = true;
            if ($results['MOTIVO_C'] === '' && ($results['FAMIGLIA_T'] === 'AN' | $results['FAMIGLIA_T'] === 'CV' |
                    $results['FAMIGLIA_T'] === 'FE')) {
                // TODO... DEVO SETTARE VARIABILE CHE IN OMNIS è CHIAMATA "iv_pres_ana_attivo" A TRUE... 
                // SERVE PER POI CONTROLLARE AUTORIZZAZIONI. PER ADESSO SETTO VARIABILE SENZA CONTROLLARLA POI SU CHECK_AUTOR
                $iv_pres_ana_attivo = true;
            }
            $results = $this->libDB->leggiDtaFlagan($results['FAMIGLIA_T'], $results['MOTIVO_C']);
            Out::html($this->nameForm . '_Anagrafe_lbl', 'Ana: ' . trim($results['STATOCITB'])); // Cambio label al bottone "Anagrafe" aggiungendo
        } else {
            Out::hide($this->nameForm . '_Anagrafe');
            $iv_pres_ana = false;
        }
    }

    private function initComboValidato() {
        // Validato 
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "99", 1, "--- NON VALIDATO --- ");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "0", 0, "0-Sogg.presente con stesso c.f.");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "1", 0, "1-Sogg.presente con c.f. diverso da quello inviato");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "2", 0, "2-Codice Fiscale validato da Agenzia Entrate");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "3", 0, "3-Sogg.presente con dati anagrafici diversi da quelli inviati");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "4", 0, "4-Sogg.presente ma risultato collegato");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "5", 0, "5-Sogg.presente ma risultato omocodice");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "7", 0, "7-Sogg.presente ma con dati anagrafici piÃ¹ corretti di quelli forniti dal Comune");
        Out::select($this->nameForm . '_BTA_SOGG[TIPOVALID]', 1, "9", 0, "9-Sogg.non presente in archivi Ministero");

        //Validato Storico
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "99", 1, "--- NON VALIDATO --- ");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "0", 0, "0-Sogg.presente con stesso c.f.");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "1", 0, "1-Sogg.presente con c.f. diverso da quello inviato");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "2", 0, "2-Codice Fiscale validato da Agenzia Entrate");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "3", 0, "3-Sogg.presente con dati anagrafici diversi da quelli inviati");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "4", 0, "4-Sogg.presente ma risultato collegato");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "5", 0, "5-Sogg.presente ma risultato omocodice");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "7", 0, "7-Sogg.presente ma con dati anagrafici piÃ¹ corretti di quelli forniti dal Comune");
        Out::select($this->nameForm . '_BTA_SOGGST[TIPOVALID]', 1, "9", 0, "9-Sogg.non presente in archivi Ministero");
    }

    protected function valorizzaProvenienza($proven) {
        Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', '');
        switch ($proven) {  // in base alla radio selezionata, controllo i parametri di ricerca.
            case 'A':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Anagrafe');
                break;
            case 'S':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Stato Civile');
                break;
            case 'T':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Tributi');
                break;
            case 'I':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'I.C.I.');
                break;
            case 'F':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Finanziaria');
                break;
            case 'D':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Servizi Dom.Individuale');
                break;
            case 'Q':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Acquedotto');
                break;
            case 'Z':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Diretto');
                break;
            case 'Y':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Conversione');
                break;
            case 'W':
                Out::valore($this->nameForm . '_BTA_SOGG[PROVEN_DESCR]', 'Web Services Esterno');
                break;
        }
    }

    protected function valorizzaProvenienzaDB($proven) {
        switch ($proven) {  // in base alla radio selezionata, controllo i parametri di ricerca.
            case 'Anagrafe':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'A';
                break;
            case 'Stato Civile':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'S';
                break;
            case 'Tributi':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'T';
                break;
            case 'I.C.I.':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'I';
                break;
            case 'Finanziaria':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'F';
                break;
            case 'Servizi Dom.Individuale':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'D';
                break;
            case 'Acquedotto':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'Q';
                break;
            case 'Diretto':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'Z';
                break;
            case 'Conversione':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'Y';
                break;
            case 'Web Services Esterno':
                $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] = 'W';
                break;
        }
    }

    protected function mostraBottoniPresenti($record) {
        // Nascondo i bottoni in base a dove è presente il soggetto.
        if ($record['ANAGRA']) {
            Out::show($this->nameForm . '_Anagrafe');
        } else {
            Out::hide($this->nameForm . '_Anagrafe');
        }
        if ($record['CONTRIB']) {
            Out::show($this->nameForm . '_Tributi');
        } else {
            Out::hide($this->nameForm . '_Tributi');
        }
        if ($record['FORNIT']) {
            Out::show($this->nameForm . '_Fornitori');
        } else {
            Out::hide($this->nameForm . '_Fornitori');
        }
        if ($record['SOGGISCR']) {
            Out::show($this->nameForm . '_ServDomInd');
        } else {
            Out::hide($this->nameForm . '_ServDomInd');
        }
        if ($record['AMMINISTR']) {
            Out::show($this->nameForm . '_Atti');
        } else {
            Out::hide($this->nameForm . '_Atti');
        }
    }

    protected function calcolaCodFisc($methodArgs) {
        return $this->cwbBtaSoggettoUtils->calcolaCodFisc($methodArgs);
    }

    protected function repDatidaCodFisc($methodArgs) {
        return $this->cwbBtaSoggettoUtils->repDatidaCodFisc($methodArgs);
    }

    public function setModelData($data) {
        parent::setModelData($data);
        if (isSet($this->componentSoggModel)) {
            $this->componentSoggModel->setComponentFormData($data);
        }
    }

    public function getBtaSoggData() {
        $this->getDataNascita();

        return $_POST[$this->nameForm . '_BTA_SOGG'];
    }

    public function changeDittaIndividuale($dittaindiv) {
        $this->dittaindiv = $dittaindiv;
        if (!$dittaindiv && $_POST[$this->nameForm . '_BTA_SOGG']['PROVEN'] == 'Finanziaria' && $this->libDB->documentiCollegatiSoggetto($_POST[$this->nameForm . '_BTA_SOGG']['PROGSOGG'])) {
            Out::msgStop('Errore', 'Esistono Documenti Collegati al Soggetto come Ditta Individuale. Impossibile togliere il flag \'Ditta Individuale\'');
            Out::valore($this->nameForm . '_BTA_SOGG[DITTAINDIV]', 1);
        }

        $this->componentSoggModel->changeDittaIndividuale($_POST[$this->nameForm . '_BTA_SOGG'], $this->viewMode);
    }

    public function getDittaIndiv() {
        return $this->dittaindiv;
    }

    private function setRicClfor($ric_clfor) {
        if ($ric_clfor) {
            Out::html($this->nameForm . '_NOME_RIC_lbl', 'Ragione Sociale');
            Out::gridHideCol($this->nameForm . '_gridBtaSoggAttuali', 'COGNOME');
            Out::gridShowCol($this->nameForm . '_gridBtaSoggAttuali', 'RAGSOC');
        } else {
            Out::html($this->nameForm . '_NOME_RIC_lbl', 'Nominativo');
            Out::gridShowCol($this->nameForm . '_gridBtaSoggAttuali', 'COGNOME');
            Out::gridHideCol($this->nameForm . '_gridBtaSoggAttuali', 'RAGSOC');
        }
        //Out::gridForceResize($this->nameForm . '_gridBtaSoggAttuali');
    }

    protected function postAggiungi() {
        Out::msgInfo('Creazione nuovo record', 'E\' stato inserito correttamente un nuovo soggetto con matricola ' . $this->getLastInsertId());

        Out::hide($this->nameForm . '_btnRendiFornitore');
        Out::hide($this->nameForm . '_btnUfficiFE');
        Out::hide($this->nameForm . '_btnDurc');
        Out::hide($this->nameForm . '_btnAllegati');
    }

    protected function postAggiorna() {
        $this->msgBlock = 'Soggetto aggiornato correttamente';
    }

    protected function postConfermaCancella() {
        $this->msgBlock = 'Soggetto eliminato correttamente';
    }

    private function showPhone() {
        $progsogg = $_POST[$this->nameForm . '_BTA_SOGG']['PROGSOGG'];

        if (empty($progsogg)) {
            $datiRecap = null;
            $datiIndditt = null;
        } else {
            $filtri = array(
                'PROGSOGG' => $progsogg
            );
            $datiRecap = $this->libDB_FTA->leggiFtaResidCurrent($filtri, false);
            $datiIndditt = $this->libDB_FTA->leggiFtaIndditCurrent($filtri, false);
        }

        $html = '';
        if ($datiRecap) {
            $telefono = trim($datiRecap['TELEFONO']);
            $telefono2 = trim($datiRecap['TELEFONO_1']);
            $cellulare = trim($datiRecap['CELLULARE']);
            $fax = trim($datiRecap['FAX']);
            $email = trim($datiRecap['E_MAIL']);
            $pec = trim($datiRecap['E_MAIL_PEC']);
            if (!empty($telefono) || !empty($telefono2) || !empty($cellulare) || !empty($fax) || !empty($email) || !empty($pec)) {
                $html .= '<div class="ita-box ui-widget-content ui-corner-all" style="display: inline-block; width: 440px; vertical-align: top;">';
                $html .= '<div class="ita-header ui-widget-header ui-corner-all" title="Recapiti Soggetto">';
                $html .= '</div>';
                $html .= '<table>';
                if (!empty($telefono)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Telefono:</td><td>' . $telefono . '</td></tr>';
                }
                if (!empty($telefono2)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Telefono:</td><td>' . $telefono2 . '</td></tr>';
                }
                if (!empty($cellulare)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Cellulare:</td><td>' . $cellulare . '</td></tr>';
                }
                if (!empty($fax)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Fax:</td><td>' . $fax . '</td></tr>';
                }
                if (!empty($email)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">eMail:</td><td>' . $email . '</td></tr>';
                }
                if (!empty($pec)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">eMail PEC:</td><td>' . $pec . '</td></tr>';
                }
                $html .= '</table>';
                $html .= '</div>';
            }
        }
        if ($datiIndditt) {
            $telefono = trim($datiIndditt['TELEFONO']);
            $telefono2 = trim($datiIndditt['TELEFONO_1']);
            $cellulare = trim($datiIndditt['CELLULARE']);
            $fax = trim($datiIndditt['FAX']);
            $email = trim($datiIndditt['E_MAIL']);
            $pec = trim($datiIndditt['E_MAIL_PEC']);
            if (!empty($telefono) || !empty($telefono2) || !empty($cellulare) || !empty($fax) || !empty($email) || !empty($pec)) {
                $html .= '<div class="ita-box ui-widget-content ui-corner-all" style="display: inline-block; width: 440px; vertical-align: top;">';
                $html .= '<div class="ita-header ui-widget-header ui-corner-all" title="Recapiti Ditta Individuale">';
                $html .= '</div>';
                $html .= '<table>';
                if (!empty($telefono)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Telefono:</td><td>' . $telefono . '</td></tr>';
                }
                if (!empty($telefono2)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Telefono:</td><td>' . $telefono2 . '</td></tr>';
                }
                if (!empty($cellulare)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Cellulare:</td><td>' . $cellulare . '</td></tr>';
                }
                if (!empty($fax)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">Fax:</td><td>' . $fax . '</td></tr>';
                }
                if (!empty($email)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">eMail:</td><td>' . $email . '</td></tr>';
                }
                if (!empty($pec)) {
                    $html .= '<tr><td style="font-weight:bold; width: 75px">eMail PEC:</td><td>' . $pec . '</td></tr>';
                }
                $html .= '</table>';
                $html .= '</div>';
            }
        }
        if (isSet($datiRecap) && !empty($datiRecap['CODNAZPRO']) && !empty($datiRecap['CODLOCAL'])) {
            $nominativo = trim($_POST[$this->nameForm . '_BTA_SOGG']['COGNOME'] . ' ' . $_POST[$this->nameForm . '_BTA_SOGG']['NOME']);
            $filtri = array(
                'CODNAZPRO' => $datiRecap['CODNAZPRO'],
                'CODLOCAL' => $datiRecap['CODLOCAL']
            );
            $localData = $this->libDB_BTA->leggiBtaLocal($filtri, false);

            if (!empty($nominativo) && $localData) {
                $url = "https://www.paginebianche.it/ricerca?qs=" . urlencode($nominativo) . "&dv=" . urldecode($localData['DESLOCAL'] . ' (' . $localData['PROVINCIA'] . ')');

                $html .= '<div class="ita-box ui-widget-content ui-corner-all" style="width: 887px">';
                $html .= '<div class="ita-header ui-widget-header ui-corner-all" title="Pagine Bianche">';
                $html .= '</div>';
                $html .= '<iframe style="width: 883px; height: 400px" src="' . $url . '"></iframe>';
                $html .= '</div>';
            }
        }
        if ($html != '') {
            Out::msgInfo('Contatti', $html, 600, 950, 'desktopBody', false, true);
        } else {
            Out::msgBlock('', 2000, false, 'Non sono registrati recapiti al momento');
        }
    }

    protected function preRicercaEsterna($index) {
        $filtri = array();
        $filtri['PROGSOGG'] = $index;

        $error = "E' necessario selezionare un soggetto avente i dati relativi a:<br>";

        if ($this->origin['ANAGRAFE']) {
            $filtri['ANAGRA'] = true;
            $error .= "Anagrafe<br>";
        }
        if ($this->origin['CONTRIB']) {
            $filtri['CONTRIB'] = true;
            $error .= "Tributi<br>";
        }
        if ($this->origin['CLIFOR']) {
            $filtri['FORNIT'] = true;
            $error .= "Clienti/Fornitori<br>";
        }
        if ($this->origin['SOGGISCR']) {
            $filtri['SOGGISCR'] = true;
            $error .= "Rette<br>";
        }
        if ($this->origin['AMMINISTR']) {
            $filtri['AMMINISTR'] = true;
            $error .= "Atti amministrativi<br>";
        }

        $error .= "<br>Modificare il soggetto inserendo i dati mancanti per proseguire.";

        $data = $this->libDB->leggiBtaSogg($filtri, false);
        if (!$data) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, $error);
        }
    }

    private function getDataNascita() {
        $anno = substr($_POST[$this->nameForm . '_dataNascita'], 0, 4);
        $mese = substr($_POST[$this->nameForm . '_dataNascita'], 4, 2);
        $giorno = substr($_POST[$this->nameForm . '_dataNascita'], 6, 2);

        Out::valore($this->nameForm . '_BTA_SOGG[ANNO]', $anno);
        $_POST[$this->nameForm . '_BTA_SOGG']['ANNO'] = $anno;
        $this->formData[$this->nameForm . '_BTA_SOGG']['ANNO'] = $anno;

        Out::valore($this->nameForm . '_BTA_SOGG[MESE]', $mese);
        $_POST[$this->nameForm . '_BTA_SOGG']['MESE'] = $mese;
        $this->formData[$this->nameForm . '_BTA_SOGG']['MESE'] = $mese;

        Out::valore($this->nameForm . '_BTA_SOGG[GIORNO]', $giorno);
        $_POST[$this->nameForm . '_BTA_SOGG']['GIORNO'] = $giorno;
        $this->formData[$this->nameForm . '_BTA_SOGG']['GIORNO'] = $giorno;

        return array(
            'ANNO' => $anno,
            'MESE' => $mese,
            'GIORNO' => $giorno
        );
    }

}

?>