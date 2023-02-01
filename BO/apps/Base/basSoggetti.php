<?php

include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basRic.class.php';

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';

function basSoggetti() {
    $basSoggetti = new basSoggetti();
    $basSoggetti->parseEvent();
    return;
}

class basSoggetti extends itaModel {

    public $ITALWEB_DB;
    public $COMUNI_DB;
    public $ITW_DB;
    public $basLib;
    public $nameForm = "basSoggetti";
    public $divDest = "basSoggetti_divDest";
    public $divGes = "basSoggetti_divGestione";
    public $divRis = "basSoggetti_divRisultato";
    public $divRic = "basSoggetti_divRicerca";
    public $gridAnaSoggetti = "basSoggetti_gridAnaSoggetti";
    public $gridRecapiti = "basSoggetti_gridRecapiti";
    public $gridRuoli = "basSoggetti_gridRuoli";
    public $returnField = '';
    public $returnModel = '';

    public function getDati() {
        return $this->dati;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    function __construct() {
        parent::__construct();
        try {
            $this->basLib = new basLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->creaCombo();
                $this->OpenRicerca();
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridRecapiti:
                        $this->DettaglioRecapito();
                        break;
                    case $this->gridRuoli:
                        $this->DettaglioRuolo();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaSoggetti:
                        break;

                    case $this->gridRecapiti:
                        $this->DettaglioRecapito($_POST['rowid']);
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il recapito selezionato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancRecapito', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancRecapito', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->gridRuoli:
                        $this->DettaglioRuolo($_POST['rowid']);
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler collegamento con soggetto selezionato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancRecapito', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancRuolo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'viewRowInline':
            case 'editRowInline':
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAnaSoggetti:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridRecapiti:
                        $this->DettaglioRecapito($_POST['rowid']);
                        break;
                    case $this->gridRuoli:
                        $this->DettaglioRuolo($_POST['rowid']);
                        break;
                }
                break;

            case 'exportTableToExcel':
                $fields = $this->getXlsxFields();
                $model = cwbLib::apriFinestra('utiXlsxCustomizer', $this->nameForm, '', '', array(), $this->nameFormOrig);
                //$model->initPage($fields, 'ITALWEB', '', $this->nameFormOrig . $this->xlsxMode, $this->xlsxPageDescription, $this->xlsxDefaultModel);
                $model->initPage($fields, 'ITALWEB', '', $this->nameForm, 'AnagraficaSoggetti');
                $model->openLoadDialog();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnaSoggetti:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->clearGrid($this->gridAnaSoggetti);
                        $ita_grid01->getDataPage('json');
                        break;

                    case $this->gridRecapiti:
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridRuoli:
                        switch ($_POST['colName']) {
                            case 'ICON':
                                $contenuto = $this->GetInfoSogg($_POST['rowid']);
                                Out::msgInfo('Informazioni', print_r($contenuto, true));
                                break;
                        }
                        break;
                }
                break;
            case 'printTableToHTML':
                break;

            case 'afterSaveCell':
                break;

            case 'cellSelect':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $Tabella_rec = $_POST[$this->nameForm . '_ANA_SOGGETTI'];

                        /*
                         * Mi Salvo l'utente inserimento
                         */

                        $Tabella_rec['UTENTEINSERIMENTO'] = App::$utente->getKey('nomeUtente');
                        $Tabella_rec['DATAINSERIMENTO'] = date('Ymd');
                        $Tabella_rec['ORAINSERIMENTO'] = date('H:i:s');
                        try {
                            $insert_Info = 'Inserimenti nuova anagrafica soggetto ' . $Tabella_rec['COGNOME'] . " " . $Tabella_rec['NOME'];
                            if ($this->insertRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $Tabella_rec, $insert_Info)) {
                                $this->Dettaglio($this->ITALWEB_DB->getLastId());
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Creazione Movimento.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Elenca':
                        try {
                            $sql = $this->CreaSql();
                            $ita_grid01 = new TableView($this->gridAnaSoggetti, array('sqlDB' => $this->ITALWEB_DB, 'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridAnaSoggetti]['gridParam']['rowNum']);
                            $ita_grid01->setSortIndex('COGNOME,NOME');
                            $ita_grid01->setSortOrder('asc');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                $arrayTab_tmp = $ita_grid01->getDataArray();
                                if (count($arrayTab_tmp) == 1) { // se è solo 1 record visualizzo il dettaglio
                                    $this->Dettaglio($arrayTab_tmp[0]['ROWID']);
                                    break;
                                }
                                Out::hide($this->divGes, '', 0);
                                Out::hide($this->divRic, '', 0);
                                Out::show($this->divRis, '', 0);
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridAnaSoggetti);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                            App::log($e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        Out::clearFields($this->nameForm, $this->divRis);
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->SetNuovo();
                        break;
                    case $this->nameForm . '_ConfermaAggiungiRecapito':
                        $this->AggiungiRecapito($_POST[$this->nameForm]['ALTRIRECAPITI']);
                        break;
                    case $this->nameForm . '_ConfermaAggiungiRuolo':
                        $this->AggiungiMansione($_POST[$this->nameForm]['ALTRISOGGETTI']);
                        break;
                    case $this->nameForm . '_CalcolaCf':
                        include_once ITA_BASE_PATH . '/apps/Utility/utiCodiceFiscale.class.php';
                        $CodiceFiscale = new utiCodiceFiscale();
                        $Sex = $_POST[$this->nameForm . 'SelSesso'];
                        $CodFis = $CodiceFiscale->Calcola($_POST[$this->nameForm . '_ANA_SOGGETTI']['COGNOME'], $_POST[$this->nameForm . '_ANA_SOGGETTI']['NOME'], $_POST[$this->nameForm . '_ANA_SOGGETTI']['DATANASCITA'], $Sex, $_POST[$this->nameForm . '_ANA_SOGGETTI']['CITTANASCITA'], '');
                        Out::valore($this->nameForm . '_ANA_SOGGETTI[CF]', $CodFis);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $Tabella_rec = $_POST[$this->nameForm . '_ANA_SOGGETTI'];
                        $Tabella_rec['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
                        $Tabella_rec['DATAAGGIORNAMENTO'] = date('Ymd');
                        $Tabella_rec['ORAAGGIORNAMENTO'] = date('H:i:s');
                        $update_Info = 'Aggiornamenta ANAGRAFICA SOGGETTO : ' . $Tabella_rec['COGNOME'] . " " . $Tabella_rec['NOME'] . " ROWID " . $Tabella_rec['ROWID'];
                        if ($this->updateRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $Tabella_rec, $update_Info)) {
                            $this->Dettaglio($Tabella_rec['ROWID']);
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del soggetto selezionato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $Tabella_rec = $_POST[$this->nameForm . '_ANA_SOGGETTI'];
                        try {
                            $delete_Info = 'Cancellazione Anagrafica Soggetto: ' . $Tabella_rec['COGNOME'] . " " . $Tabella_rec['NOME'] . " rowid " . $Tabella_rec['ROWID'];
                            if ($this->deleteRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $Tabella_rec['ROWID'], $delete_Info)) {
                                $this->CancellaAnagraficheSoggetto($Tabella_rec['ROWID']);
                                $this->OpenRicerca();
                                Out::msgBlock($this->nameForm, 1000, true, 'Cancellazione Anagrafica Eseguita');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANA_SOGGETTI ", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancRecapito':
                        $Recapito_rec = $_POST[$this->nameForm]['ALTRIRECAPITI'];
                        try {
                            $delete_Info = 'Cancellazione Recapito per anagrafica soggetto rowid : ' . $Recapito_rec['ROW_ID_SOGGETTO'];
                            if ($this->deleteRecord($this->ITALWEB_DB, 'ANA_RECAPITISOGGETTI', $Recapito_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                                $this->CaricaRecapiti($Recapito_rec['ROW_ID_SOGGETTO']);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANA_RECAPITISOGGETTI ", $e->getMessage());
                            return false;
                        }
                        Out::closeCurrentDialog();
                        break;

                    case $this->nameForm . '_ConfermaCancRuolo':
                        $Ruolo_rec = $_POST[$this->nameForm]['ALTRISOGGETTI'];
                        try {
                            $delete_Info = 'Cancellazione Collegamento su anagrafica soggetto rowid : ' . $Ruolo_rec['ROW_ID_DATORE'];
                            if ($this->deleteRecord($this->ITALWEB_DB, 'ANA_RUOLISOGGETTI', $Ruolo_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                                $this->CaricaMansioni($Ruolo_rec['ROW_ID_DATORE']);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANA_RUOLISOGGETTI ", $e->getMessage());
                            return false;
                        }
                        Out::closeCurrentDialog();
                        break;
                    case $this->nameForm . '_Torna':
                        $this->TornaElenco();
                        break;
                    case $this->nameForm . '_RuoloAltroSoggRicerca_butt':
                        basRic::basRicRuoli($this->nameForm, '', 'returnAnaruoRic');
                        break;
                    case $this->nameForm . '[ALTRISOGGETTI][DENOMINAZIONE]_butt':
                        basRic::basRicAnaSoggetti($this->nameForm);
                        break;
                    case $this->nameForm . '[ALTRISOGGETTI][RUODES]_butt':
                        basRic::basRicRuoli($this->nameForm, '', 'returnAnaruoSoggetto');
                        break;
                    case $this->nameForm . '_ANA_SOGGETTI[CITTANASCITA]_butt':
                        basRic::basRicComuni($this->nameForm, '', 'returnComuneNascita');
                        break;
                    case $this->nameForm . '_ANA_SOGGETTI[CITTARESI]_butt':
                        basRic::basRicComuni($this->nameForm, '', 'returnComuneResidenza');
                        break;
                    case $this->nameForm . '_ANA_SOGGETTI[CF]_butt':
                        $option = array(
                            array(
                                '', ''
                            ), array(
                                'M', 'M'
                            ), array(
                                'F', 'F'
                            )
                        );

                        $valori[] = array(
                            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Seleziona Sesso'),
                            'id' => $this->nameForm . 'SelSesso',
                            'name' => $this->nameForm . 'SelSesso',
                            'type' => 'select',
                            'options' => $option,
                            'maxlength' => '1');

                        Out::msgInput(
                                "Calcola CF", $valori, array(
                            'Conferma' => array('id' => $this->nameForm . '_CalcolaCf', 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                                ), $this->nameForm
                        );
                        break;
                    case 'close-portlet':
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Medcod':
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RUOLOCOD':
                        break;
                }

                break;

            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANA_SOGGETTI[CITTANASCITA]':
                        itaSuggest::setNotFoundMessage('');
                        $sql = "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '%" . addslashes((itaSuggest::getQuery())) . "%'";
                        $Tabella_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
                        foreach ($Tabella_tab as $Tabella_rec) {
                            itaSuggest::addSuggest($Tabella_rec['COMUNE'], array(
                                $this->nameForm . "_ANA_SOGGETTI[PROVNASCITA]" => $Tabella_rec['PROVIN']));
                        }
                        itaSuggest::sendSuggest();

                        break;
                    case $this->nameForm . '_ANA_SOGGETTI[CITTARESI]':
                        itaSuggest::setNotFoundMessage('');
                        $sql = "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '%" . addslashes((itaSuggest::getQuery())) . "%'";
                        $Tabella_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
                        foreach ($Tabella_tab as $Tabella_rec) {
                            itaSuggest::addSuggest($Tabella_rec['COMUNE'], array(
                                $this->nameForm . "_ANA_SOGGETTI[PROVRESI]" => $Tabella_rec['PROVIN'],
                                $this->nameForm . "_ANA_SOGGETTI[CAPRESI]" => $Tabella_rec['COAVPO']));
                        }
                        itaSuggest::sendSuggest();

                        break;
                }
                break;
            case 'returnAnaruoRic':
                $Ana_Ruolo_rec = $this->basLib->getRuolo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_RuoloAltroSoggRicerca', $Ana_Ruolo_rec['RUODES']);
                Out::valore($this->nameForm . '_RuoloRowid', $Ana_Ruolo_rec['ROWID']);
                break;
            case 'returnAnaruoSoggetto':
                $Ana_Ruolo_rec = $this->basLib->getRuolo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '[ALTRISOGGETTI][RUODES]', $Ana_Ruolo_rec['RUODES']);
                Out::valore($this->nameForm . '[ALTRISOGGETTI][ROW_ID_ANARUOLI]', $Ana_Ruolo_rec['ROWID']);
                break;
            case 'returnAnaSoggetti':
                $sql = "SELECT * FROM ANA_SOGGETTI WHERE ROWID = " . $_POST['retKey'];
                $soggetto_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM ANA_SOGGETTI WHERE ROWID = " . $_POST['retKey'], false);
                Out::valore($this->nameForm . '[ALTRISOGGETTI][DENOMINAZIONE]', $soggetto_rec['COGNOME'] . ' ' . $soggetto_rec['NOME']);
                Out::valore($this->nameForm . '[ALTRISOGGETTI][ROW_ID_PRESTATORE]', $soggetto_rec['ROWID']);
                break;
            case 'returnComuneNascita':
                $comune_rec = $this->basLib->getComuni($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ANA_SOGGETTI[CITTANASCITA]', $comune_rec['COMUNE']);
                Out::valore($this->nameForm . '_ANA_SOGGETTI[PROVNASCITA]', $comune_rec['PROVIN']);
                Out::setFocus('', $this->nameForm . '_ANA_SOGGETTI[CITTARESI]');
                break;
            case 'returnComuneResidenza':
                $comune_rec = $this->basLib->getComuni($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ANA_SOGGETTI[CITTARESI]', $comune_rec['COMUNE']);
                Out::valore($this->nameForm . '_ANA_SOGGETTI[PROVRESI]', $comune_rec['PROVIN']);
                Out::valore($this->nameForm . '_ANA_SOGGETTI[CAPRESI]', $comune_rec['COAVPO']);
                Out::setFocus('', $this->nameForm . '_ANA_SOGGETTI[DESCRIZIONEVIA]');
                break;
        }
    }

    public function close() {
        if ($this->returnModel != '') {
            $rowId = $_POST['rowid'];
            $_POST = array();
            $_POST['event'] = 'returntoform';
            $_POST['model'] = $this->returnModel;
            $_POST['retField'] = $this->returnField;
            $_POST['retKey'] = $rowId;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
            $model = $this->returnModel;
            $model();
        }
        App::$utente->removeKey($this->nameForm . '_returnField');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    private function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        TableView::disableEvents($this->gridAnaSoggetti);
        TableView::clearGrid($this->gridAnaSoggetti);
        Out::setFocus('', $this->nameForm . '_Nominativo_ric');
    }

    private function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANA_SOGGETTI[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::clearGrid($this->gridRecapiti);
        TableView::clearGrid($this->gridRuoli);
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_StatoInserimento');
        Out::hide($this->nameForm . '_StatoModifiche');
    }

    private function creaCombo() {
        Out::html($this->nameForm . '_FormaGiuridica_ric', '');
        Out::html($this->nameForm . '_ANA_SOGGETTI[NATGIU]', '');
        Out::html($this->nameForm . '_FonteDati_ric', '');

        Out::select($this->nameForm . '_FormaGiuridica_ric', 1, "", "1", "Tutte");
        Out::select($this->nameForm . '_FormaGiuridica_ric', 1, "0", "0", "Fisica");
        Out::select($this->nameForm . '_FormaGiuridica_ric', 1, "1", "0", "Giuridica");
        Out::select($this->nameForm . '_FormaGiuridica_ric', 1, "2", "0", "Ente");

        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "", "0", "Seleziona...");
        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "0", "0", "Fisica");
        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "1", "0", "Giuridica");
        Out::select($this->nameForm . '_ANA_SOGGETTI[NATGIU]', 1, "2", "0", "Ente");

        /*
         * Crea Combo FONTEDATI GRID
         */

        TableView::tableSetFilterHtml($this->gridAnaSoggetti, "FONTEDATI", '');
        $anatsp_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT DISTINCT(FONTEDATI)AS FONTEDATI FROM ANA_SOGGETTI", true);
        if ($anatsp_tab) {
            Out::select($this->nameForm . '_FonteDati_ric', 1, "", "1", "Tutte");
        }
        TableView::tableSetFilterSelect($this->gridAnaSoggetti, "FONTEDATI", 1, "", "0", "");
        foreach ($anatsp_tab as $anatsp_rec) {
            TableView::tableSetFilterSelect($this->gridAnaSoggetti, "FONTEDATI", 1, $anatsp_rec['FONTEDATI'], '0', $anatsp_rec['FONTEDATI']);
            Out::select($this->nameForm . '_FonteDati_ric', 1, $anatsp_rec['FONTEDATI'], "0", $anatsp_rec['FONTEDATI']);
        }
    }

    private function CreaSql() {
        $where = $group = $joinRecapiti = $joinMansioni = '';

        if ($_POST[$this->nameForm . '_Recapito_ric']) {
            $joinRecapiti = " INNER JOIN ANA_RECAPITISOGGETTI ON ANA_SOGGETTI.ROWID = ANA_RECAPITISOGGETTI.ROW_ID_SOGGETTO ";
            $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_RECAPITISOGGETTI.RECAPITO') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Recapito_ric'])) . "%'";
        }

        if ($_POST['DENOMINAZIONE']) {
            $where .= " AND (" . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.COGNOME', "' '", 'ANA_SOGGETTI.NOME')) . " LIKE '%" . strtoupper(addslashes($_POST['DENOMINAZIONE'])) . "%'";
            $where .= " OR " . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.NOME', "' '", 'ANA_SOGGETTI.COGNOME')) . " LIKE '%" . strtoupper(addslashes($_POST['DENOMINAZIONE'])) . "%')";
        }
        if ($_POST[$this->nameForm . '_Nominativo_ric']) {
            $where .= " AND (" . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.COGNOME', "' '", 'ANA_SOGGETTI.NOME')) . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Nominativo_ric'])) . "%'";
            $where .= " OR " . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.NOME', "' '", 'ANA_SOGGETTI.COGNOME')) . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_Nominativo_ric'])) . "%')";
        }

        /*
         * Ricerco per
         * Soggetto collegato
         */
        if ($_POST[$this->nameForm . '_AltroNomeRicerca'] || $_POST[$this->nameForm . '_AltroCFRicerca'] || $_POST[$this->nameForm . '_AltroPIRicerca'] || $_POST[$this->nameForm . '_AltroRecapitoRicerca']) {
            $joinMansioni = " INNER JOIN ANA_RUOLISOGGETTI ON ANA_SOGGETTI.ROWID = ANA_RUOLISOGGETTI.ROW_ID_DATORE ";
            if ($_POST[$this->nameForm . '_AltroNomeRicerca']) {
                $where .= " AND ROW_ID_PRESTATORE IN (SELECT ROWID FROM ANA_SOGGETTI WHERE ";
                $where .= " " . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.COGNOME', "' '", 'ANA_SOGGETTI.NOME')) . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_AltroNomeRicerca'])) . "%'";
                $where .= " OR " . $this->ITALWEB_DB->strUpper($this->ITALWEB_DB->strConcat('ANA_SOGGETTI.NOME', "' '", 'ANA_SOGGETTI.COGNOME')) . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_AltroNomeRicerca'])) . "%')";
            }
            if ($_POST[$this->nameForm . '_AltroCFRicerca']) {
                $where .= " AND ROW_ID_PRESTATORE IN (SELECT ROWID FROM ANA_SOGGETTI WHERE ";
                $where .= " " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.CF') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_AltroCFRicerca'])) . "%')";
            }
            if ($_POST[$this->nameForm . '_AltroPIRicerca']) {
                $where .= " AND ROW_ID_PRESTATORE IN (SELECT ROWID FROM ANA_SOGGETTI WHERE ";
                $where .= " " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.PIVA') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_AltroPIRicerca'])) . "%')";
            }

            if ($_POST[$this->nameForm . '_AltroRecapitoRicerca']) {
                $joinRecapiti = " INNER JOIN ANA_RECAPITISOGGETTI ON ANA_RUOLISOGGETTI.ROW_ID_PRESTATORE = ANA_RECAPITISOGGETTI.ROW_ID_SOGGETTO ";
                $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_RECAPITISOGGETTI.RECAPITO') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_AltroRecapitoRicerca'])) . "%'";
            }

            if ($_POST[$this->nameForm . '_RuoloRowid']) {
                $where .= " AND ROW_ID_ANARUOLI = " . $_POST[$this->nameForm . '_RuoloRowid'];
            }

            $group = " GROUP BY ROWID";
        }


        if ($_POST['CF']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.CF') . " LIKE '%" . strtoupper(addslashes($_POST['CF'])) . "%'";
        }
        if ($_POST[$this->nameForm . '_CF_ric']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.CF') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_CF_ric'])) . "%'";
        }
        if ($_POST['PIVA']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.PIVA') . " LIKE '%" . strtoupper(addslashes($_POST['PIVA'])) . "%'";
        }
        if ($_POST[$this->nameForm . '_PI_ric']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('ANA_SOGGETTI.PIVA') . " LIKE '%" . strtoupper(addslashes($_POST[$this->nameForm . '_PI_ric'])) . "%'";
        }
        if ($_POST['FONTEDATI']) {
            $where .= " AND ANA_SOGGETTI.FONTEDATI = '" . $_POST['FONTEDATI'] . "'";
        }
        if ($_POST[$this->nameForm . '_FonteDati_ric']) {
            $where .= " AND ANA_SOGGETTI.FONTEDATI = '" . $_POST[$this->nameForm . '_FonteDati_ric'] . "'";
        }
        if ($_POST[$this->nameForm . '_FormaGiuridica_ric'] != '') {
            $where .= " AND ANA_SOGGETTI.NATGIU = '" . $_POST[$this->nameForm . '_FormaGiuridica_ric'] . "'";
        }

        if ($_POST[$this->nameForm . '_DADataFormalita']) {
            if ($_POST[$this->nameForm . '_ADataFormalita']) {
                $where .= " AND ANA_SOGGETTI.DATAULTFORM BETWEEN '" . $_POST[$this->nameForm . '_DADataFormalita'] . "' AND '" . $_POST[$this->nameForm . '_ADataFormalita'] . "'";
            } else {
                $where .= " AND ANA_SOGGETTI.DATAULTFORM = '" . $_POST[$this->nameForm . '_DADataFormalita'] . "'";
            }
        }

        $sql = "SELECT ANA_SOGGETTI.*," . $this->ITALWEB_DB->strConcat('COGNOME', "' '", 'NOME') . "AS DENOMINAZIONE "
                . "FROM ANA_SOGGETTI " . $joinMansioni . $joinRecapiti
                . "WHERE 1=1" . $where . $group;
//       Out::msgInfo('SQL', $sql);
//       Out::msgInfo('SQL', print_r($_POST,true));
        return $sql;
    }

    public function Dettaglio($_Indice) {
        Out::clearFields($this->divGes);
        $Tabella_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM ANA_SOGGETTI WHERE ROWID='$_Indice'", false);
        //  Out::msgInfo('pppp',print_r($Tabella_rec,true));
        $open_Info = 'Apertura Anagrafica soggetto: ' . $Tabella_rec['COGNOME'] . " " . $Tabella_rec['NOME'];
        $this->openRecord($this->ITALWEB_DB, 'ANA_SOGGETTI', $open_Info);
        $this->Nascondi();
        $this->creaCombo();
        Out::valori($Tabella_rec, $this->nameForm . '_ANA_SOGGETTI');
        Out::hide($this->divRis, '', 0);
        Out::hide($this->divRic, '', 0);
        Out::show($this->divGes, '', 0);
        Out::attributo($this->nameForm . '_ANA_SOGGETTI[CODANAG]', 'disabled', '0', 'disabled');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Torna');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::tabSelect($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneDatiAnagrafici");
        Out::tabEnable($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneRecapiti");
        Out::tabEnable($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneRuoli");
        Out::setFocus('', $this->nameForm . '_ANA_SOGGETTI[COGNOME]');
        $this->DecodStato($Tabella_rec);

        /*
         * carico le sotto tabelle
         */
        Out::show($this->gridRecapiti);
        Out::show($this->gridRuoli);
        $this->CaricaRecapiti($Tabella_rec['ROWID']);
        $this->CaricaMansioni($Tabella_rec['ROWID']);
        return true;
    }

    public function DettaglioRecapito($_Indice = '') {
        $Recapito_rec = array();
        if ($_Indice) {
            $sql = "SELECT * FROM ANA_RECAPITISOGGETTI WHERE ROW_ID=" . $_Indice;
            $Recapito_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        } else {
            $Recapito_rec['ROW_ID_SOGGETTO'] = $this->formData[$this->nameForm . '_ANA_SOGGETTI']['ROWID'];
        }
        /*
         * getsisto il TIPO ANAGRAFICA 
         *   da anagrafica tipologie su ANA_COMUNE
         *   creo la select
         */
        $Tipologie_tab = $this->basLib->getComana('RIF');

        $option = array();
        $option[] = array('', "Seleziona..");
        foreach ($Tipologie_tab as $Tipologie_rec) {
            $attivo = false;
            if ($Tipologie_rec['ROWID'] == $Recapito_rec['ROW_ID_ANARECAPITO']) {
                $attivo = true;
            }
            $option[] = array($Tipologie_rec['ANADES'], $Tipologie_rec['ANADES'], $attivo);
        }

        /*
         * gestione flag check PREDEFINITO
         */
        if ($Recapito_rec['PREDEFINITO'] == 0) {
            $check = '';
        } else {
            $check = 'checked';
        }

        //  Out::msgInfo('recapito', print_r($Recapito_rec, true));
        $valori = array();
        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRIRECAPITI][ROW_ID]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][ROW_ID]',
            'value' => $Recapito_rec['ROW_ID'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Tipologia'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][ANADES]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][ANADES]',
            'type' => 'select',
            'options' => $option,
            'maxlength' => '1');

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Recapito'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][RECAPITO]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][RECAPITO]',
            'value' => $Recapito_rec['RECAPITO'],
            'type' => 'text',
            'width' => '20',
            'size' => '30',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Predefinito'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][PREDEFINITO]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][PREDEFINITO]',
            $check => $check,
            'type' => 'checkbox',
            'class' => 'ita-check-box',
        );

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Valido da'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][DATAVALINI]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][DATAVALINI]',
            'value' => $Recapito_rec['DATAVALINI'],
            'type' => 'text',
            'class' => 'ita-datepicker',
            'width' => '20',
            'size' => '10',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Valido a'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][DATAVALFIN]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][DATAVALFIN]',
            'value' => $Recapito_rec['DATAVALFIN'],
            'type' => 'text',
            'class' => 'ita-datepicker',
            'width' => '20',
            'size' => '10',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Note'),
            'id' => $this->nameForm . '[ALTRIRECAPITI][NOTE]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][NOTE]',
            'value' => $Recapito_rec['NOTE'],
            'type' => 'text',
            'width' => '20',
            'size' => '30',);
        /* $valori[] = array(
          'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Fonte'),
          'id' => $this->nameForm . '[ALTRIRECAPITI][FONTEDATI]',
          'name' => $this->nameForm . '[ALTRIRECAPITI][FONTEDATI]',
          'value' => $Recapito_rec['FONTEDATI'],
          'type' => 'text',
          'width' => '20',
          'readOnly' => 'readOnly',
          'size' => '20',); */

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRIRECAPITI][ROW_ID_SOGGETTO]',
            'name' => $this->nameForm . '[ALTRIRECAPITI][ROW_ID_SOGGETTO]',
            'value' => $Recapito_rec['ROW_ID_SOGGETTO'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);

        Out::msgInput(
                "Recapito Soggetto", $valori, array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiungiRecapito', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                ), $this->nameForm
        );

        return true;
    }

    private function CaricaMansioni($Rowid) {

        $sqlMansioni = "SELECT ANA_RUOLISOGGETTI.*,ANA_RUOLI.RUODES,ANA_RUOLI.RUOCOD FROM ANA_RUOLISOGGETTI"
                . " LEFT JOIN ANA_RUOLI ON ANA_RUOLISOGGETTI.ROW_ID_ANARUOLI = ANA_RUOLI.ROWID"
                . " WHERE ANA_RUOLISOGGETTI.ROW_ID_DATORE = " . $Rowid;
        $Mansioni_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlMansioni, true);

        foreach ($Mansioni_tab as $key => $Mansioni_rec) {
            $sql = "SELECT " . $this->ITALWEB_DB->strConcat('COGNOME', "' '", 'NOME') . "AS SOGGETTO,ROWID FROM ANA_SOGGETTI WHERE ROWID = '" . $Mansioni_rec['ROW_ID_PRESTATORE'] . "'";
            $Soggetto_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
            $Mansioni_tab[$key]['SOGGETTO'] = $Soggetto_rec['SOGGETTO'];
            $Mansioni_tab[$key]['ICON'] = "<span title = \"Consulta Anagrafica\" class=\"ita-icon ita-icon-cerca-16x16\" style = \"float:left;display:inline-block;\"></span>";
        }

        $this->CaricaGriglia($this->gridRuoli, $Mansioni_tab);
        return true;
    }

    private function CaricaRecapiti($Rowid) {

        $sqlRecapiti = "SELECT ANA_RECAPITISOGGETTI.*,ANA_COMUNE.ANADES FROM ANA_RECAPITISOGGETTI"
                . " LEFT JOIN ANA_COMUNE ON ANA_RECAPITISOGGETTI.ROW_ID_ANARECAPITO = ANA_COMUNE.ROWID"
                . " WHERE ANA_RECAPITISOGGETTI.ROW_ID_SOGGETTO = '" . $Rowid . "'";

        $Recapiti_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlRecapiti, true);
        $this->CaricaGriglia($this->gridRecapiti, $Recapiti_tab);
        return true;
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio)
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function SetNuovo() {
        Out::tabSelect($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneDatiAnagrafici");
        $this->AzzeraVariabili();
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        $this->creaCombo();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Aggiungi');
        Out::tabDisable($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneRecapiti");
        Out::tabDisable($this->nameForm . "_tabSoggetto", $this->nameForm . "_paneRuoli");
        Out::setFocus('', $this->nameForm . '_ANA_SOGGETTI[COGNOME]');
    }

    private function TornaElenco() {
        Out::hide($this->divGes);
        Out::hide($this->divRic);
        Out::show($this->divRis);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Nuovo');
        TableView::enableEvents($this->gridAnaSoggetti);
    }

    private function AggiungiRecapito($Recapito_rec) {
        if (!$Recapito_rec['ROW_ID_SOGGETTO']) {
            Out::msgStop('ERRORE', '<b>Individuazione soggetto non riuscita</b><br>Nessun recapito aggiunto!');
            return false;
        }
        //  return;
        $Tipologia_rec = $this->basLib->getComana('RIF', 'descrizione', $Recapito_rec['ANADES']);

        $Recapito_rec['ROW_ID_ANARECAPITO'] = $Tipologia_rec['ROWID'];
        unset($Recapito_rec['ANADES']);
        if ($Recapito_rec['ROW_ID']) {
            /*
             * AGGIORNO SE è UN RECORD GIà ESISTENTE 
             * ALTRIMENTI INSERISCO
             */
            $Recapito_rec['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
            $Recapito_rec['DATAAGGIORNAMENTO'] = date('Ymd');
            $Recapito_rec['ORAAGGIORNAMENTO'] = date('H:i:s');

            $update_Info = 'Aggiornato recapito anagrafica soggetto rowid ' . $Recapito_rec['ROW_ID_SOGGETTO'];

            $this->updateRecord($this->ITALWEB_DB, 'ANA_RECAPITISOGGETTI', $Recapito_rec, $update_Info, 'ROW_ID');
        } else {
            $Recapito_rec['UTENTEINSERIMENTO'] = App::$utente->getKey('nomeUtente');
            $Recapito_rec['DATAINSERIMENTO'] = date('Ymd');
            $Recapito_rec['ORAINSERIMENTO'] = date('H:i:s');

            $insert_Info = "Inserito recapito anagrafica soggetto rowid " . $Recapito_rec['ROW_ID_SOGGETTO'];
            if (!$this->insertRecord($this->ITALWEB_DB, 'ANA_RECAPITISOGGETTI', $Recapito_rec, $insert_Info, 'ROW_ID')) {
                Out::msgStop('ERRORE', 'ERRORE in inserimento NUOVO RECAPITO');
                return false;
            }
        }
        $this->CaricaRecapiti($Recapito_rec['ROW_ID_SOGGETTO']);
        return true;
    }

    private function AggiungiMansione($Mansione_rec) {
        if (!$Mansione_rec['ROW_ID_DATORE']) {
            Out::msgStop('ERRORE', '<b>Individuazione soggetto di riferimento non riuscita</b><br>Nessun soggetto collegato!');
            return false;
        }

        unset($Mansione_rec['DENOMINAZIONE']);
        unset($Mansione_rec['RUODES']);
        if ($Mansione_rec['ROW_ID']) {
            /*
             * AGGIORNO SE è UN RECORD GIà ESISTENTE 
             * ALTRIMENTI INSERISCO
             */
            $Mansione_rec['UTENTEAGGIORNAMENTO'] = App::$utente->getKey('nomeUtente');
            $Mansione_rec['DATAAGGIORNAMENTO'] = date('Ymd');
            $Mansione_rec['ORAAGGIORNAMENTO'] = date('H:i:s');

            $update_Info = 'Aggiornato colleggato in anagrafica soggetto rowid ' . $Mansione_rec['ROW_ID_DATORE'];

            $this->updateRecord($this->ITALWEB_DB, 'ANA_RUOLISOGGETTI', $Mansione_rec, $update_Info, 'ROW_ID');
        } else {

            $Mansione_rec['UTENTEINSERIMENTO'] = App::$utente->getKey('nomeUtente');
            $Mansione_rec['DATAINSERIMENTO'] = date('Ymd');
            $Mansione_rec['ORAINSERIMENTO'] = date('H:i:s');

            $insert_Info = "Inserito colleggato in anagrafica soggetto rowid " . $Mansione_rec['ROW_ID_DATORE'];
            if (!$this->insertRecord($this->ITALWEB_DB, 'ANA_RUOLISOGGETTI', $Mansione_rec, $insert_Info, 'ROW_ID')) {
                Out::msgStop('ERRORE', 'ERRORE in collegamento SOGGETTO');
                return false;
            }
        }
        $this->CaricaMansioni($Mansione_rec['ROW_ID_DATORE']);
        return true;
    }

    public function DecodStato($Tabella_rec) {
        if ($Tabella_rec['UTENTEINSERIMENTO']) {
            $descInsert = "Creato dall'utente " . $Tabella_rec['UTENTEINSERIMENTO'] . " in data " . substr($Tabella_rec['DATAINSERIMENTO'], 6, 8) . '/' . substr($Tabella_rec['DATAINSERIMENTO'], 4, -2) . '/' . substr($Tabella_rec['DATAINSERIMENTO'], 0, 4) . " " . $Tabella_rec['ORAINSERIMENTO'];
            Out::show($this->nameForm . '_StatoInserimento');
            Out::valore($this->nameForm . '_StatoInserimento', $descInsert);
        }
        if ($Tabella_rec['UTENTEAGGIORNAMENTO']) {
            $descEdit = "Ultima modifica " . $Tabella_rec['UTENTEAGGIORNAMENTO'] . " in data " . substr($Tabella_rec['DATAAGGIORNAMENTO'], 6, 8) . '/' . substr($Tabella_rec['DATAAGGIORNAMENTO'], 4, -2) . '/' . substr($Tabella_rec['DATAAGGIORNAMENTO'], 0, 4) . " " . $Tabella_rec['ORAAGGIORNAMENTO'];
            Out::show($this->nameForm . '_StatoModifiche');
            Out::valore($this->nameForm . '_StatoModifiche', $descEdit);
        }
        return true;
    }

    public function DettaglioRuolo($_Indice = '') {
        $RowidSogg = $this->formData[$this->nameForm . '_ANA_SOGGETTI']['ROWID'];
        $Ruolo_rec = array();
        if ($_Indice) {
            $sqlMansioni = "SELECT ANA_SOGGETTI.COGNOME,"
                    . "ANA_SOGGETTI.NOME,"
                    . "ANA_SOGGETTI.ROWID,"
                    . "ANA_RUOLISOGGETTI.*,"
                    . "ANA_RUOLI.RUODES,"
                    . "ANA_RUOLI.RUOCOD "
                    . "FROM ANA_RUOLISOGGETTI"
                    . " LEFT JOIN ANA_SOGGETTI ON ANA_RUOLISOGGETTI.ROW_ID_PRESTATORE = ANA_SOGGETTI.ROWID"
                    . " LEFT JOIN ANA_RUOLI ON ANA_RUOLISOGGETTI.ROW_ID_ANARUOLI = ANA_RUOLI.ROWID"
                    . " WHERE ANA_RUOLISOGGETTI.ROW_ID_DATORE = $RowidSogg AND ANA_RUOLISOGGETTI.ROW_ID = $_Indice";
            $Ruolo_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlMansioni, false);
        } else {
            $Ruolo_rec['ROW_ID_DATORE'] = $RowidSogg;
        }

        if ($Ruolo_rec['PREDEFINITO'] == 0) {
            $check = '';
        } else {
            $check = 'checked';
        }
        $valori = array();
        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID]',
            'value' => $Ruolo_rec['ROW_ID'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Nominativo'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][DENOMINAZIONE]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][DENOMINAZIONE]',
            'value' => $Ruolo_rec['COGNOME'] . ' ' . $Ruolo_rec['NOME'],
            'type' => 'text',
            'class' => 'ita-edit-lookup',
            'readonly' => 'readonly',
            'width' => '20',
            'size' => '60',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Mansione'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][RUODES]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][RUODES]',
            'value' => $Ruolo_rec['RUODES'],
            'type' => 'text',
            'class' => 'ita-edit-lookup',
            'readonly' => 'readonly',
            'width' => '20',
            'size' => '40',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Predefinito'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][PREDEFINITO]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][PREDEFINITO]',
            $check => $check,
            'type' => 'checkbox',
            'class' => 'ita-check-box',
        );
        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Valido da'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][DATAVALINI]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][DATAVALINI]',
            'value' => $Ruolo_rec['DATAVALINI'],
            'type' => 'text',
            'class' => 'ita-datepicker',
            'width' => '20',
            'size' => '10',);
        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Valido a'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][DATAVALFIN]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][DATAVALFIN]',
            'value' => $Ruolo_rec['DATAVALFIN'],
            'type' => 'text',
            'class' => 'ita-datepicker',
            'width' => '20',
            'size' => '10',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => 'Note'),
            'id' => $this->nameForm . '[ALTRISOGGETTI][NOTE]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][NOTE]',
            'value' => $Ruolo_rec['NOTE'],
            'type' => 'text',
            'width' => '20',
            'size' => '30',);

        $valori[] = array(
            'label' => array('style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_DATORE]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_DATORE]',
            'value' => $Ruolo_rec['ROW_ID_DATORE'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);

        $valori[] = array(
            'label' => array('style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_PRESTATORE]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_PRESTATORE]',
            'value' => $Ruolo_rec['ROW_ID_PRESTATORE'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);
        $valori[] = array(
            'label' => array('style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;', 'value' => ''),
            'id' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_ANARUOLI]',
            'name' => $this->nameForm . '[ALTRISOGGETTI][ROW_ID_ANARUOLI]',
            'value' => $Ruolo_rec['ROW_ID_ANARUOLI'],
            'type' => 'hidden',
            'width' => '20',
            'readonly' => 'readonly',
            'size' => '15',);
        Out::msgInput(
                "Mansione Soggetto", $valori, array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiungiRuolo', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
                ), $this->nameForm
        );

        return true;
    }

    public function CancellaAnagraficheSoggetto($Rowid) {
        if (!$Rowid) {
            Out::msgInfo('ERRORE', 'ERRORE IN CANCELLAZIONE TABELLE COLLEGATE PER ANAGRAFICA SOGGETTO');
            return false;
        }

        /*
         *  CANCELLAZIONI RECAPITI
         */
        $sqlRec = "SELECT * FROM ANA_RECAPITISOGGETTI WHERE ROW_ID_SOGGETTO = " . $Rowid;
        $RecapitTMP_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlRec, true);
        foreach ($RecapitTMP_tab as $RecapitTMP_rec) {
            try {
                $delete_Info = 'Cancellazione Recapito per anagrafica soggetto rowid : ' . $RecapitTMP_rec['ROW_ID_SOGGETTO'];
                $this->deleteRecord($this->ITALWEB_DB, 'ANA_RECAPITISOGGETTI', $RecapitTMP_rec['ROW_ID'], $delete_Info, 'ROW_ID');
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione su ANA_RECAPITISOGGETTI ", $e->getMessage());
                break;
            }
        }


        /*
         * TODO
         * CANCELLAZIONE RUOLI 
         * 
         */

        $sqlMAn = "SELECT * FROM ANA_RUOLISOGGETTI WHERE ROW_ID_PRESTATORE = $Rowid OR ROW_ID_DATORE = $Rowid";
        $RuoliTMP_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlMAn, true);
        foreach ($RuoliTMP_tab as $RuoliTMP_rec) {
            try {
                $delete_Info = 'Cancellazione Collegamento su anagrafica soggetto rowid : ' . $RuoliTMP_rec['ROW_ID_DATORE'];
                $this->deleteRecord($this->ITALWEB_DB, 'ANA_RUOLISOGGETTI', $RuoliTMP_rec['ROW_ID'], $delete_Info, 'ROW_ID');
            } catch (Exception $e) {
                Out::msgStop("Errore in Cancellazione su ANA_RUOLISOGGETTI ", $e->getMessage());
                break;
            }
        }

        return true;
    }

    protected function getXlsxFields() {
        $fields['COGNOME'] = array(
            'name' => 'Cognome',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['NOME'] = array(
            'name' => 'Nome',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['CF'] = array(
            'name' => 'CF',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['PIVA'] = array(
            'name' => 'PIVA',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DATANASCITA'] = array(
            'name' => 'DataNascita',
            'format' => itaXlsxWriter::FORMAT_DATE
        );

        $fields['CITTANASCITA'] = array(
            'name' => 'CittaNascita',
            'format' => itaXlsxWriter::FORMAT_STRING
        );

        $fields['PROVNASCITA'] = array(
            'name' => 'ProvNascita',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['CITTARESI'] = array(
            'name' => 'CittaResidenza',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['PROVRESI'] = array(
            'name' => 'ProvinciaResidenza',
            'format' => itaXlsxWriter::FORMAT_STRING
        );

        $fields['CAPRESI'] = array(
            'name' => 'CapResidenza',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DESCRIZIONEVIA'] = array(
            'name' => 'Via',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['CIVICO'] = array(
            'name' => 'Civico',
            'format' => itaXlsxWriter::FORMAT_STRING
        );

        $fields['NATGIU'] = array(
            'name' => 'NaturaGiuridica',
            'format' => itaXlsxWriter::FORMAT_STRING
        );

        $fields['FONTEDATI'] = array(
            'name' => 'FonteDati',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DATAULTFORM'] = array(
            'name' => 'DataUltimaFormalita',
            'format' => itaXlsxWriter::FORMAT_DATE
        );

        return $fields;
    }

    public function printXlsxFromModel($model) {
        $xlsxWriter = new itaXlsxWriter($this->ITALWEB_DB);
        $ArrayCampi = array();
        $i = 0;
        // per prendermi i filtri settati
        $tmp = $this->formData;
        $_POST = array_merge($_POST, $tmp);
        //
        foreach ($model as $campo) {
            if ($campo['calculated']) {
                $Result = $xlsxWriter->calculatedToArray($campo['calculated']);
                foreach ($Result as $Risultato) {
                    if ($Risultato['EXTRAFIELD']) {
                        $ArrayCampi[$i] = $Risultato['EXTRAFIELD'];
                        $i++;
                    }
                }
            }
        }
        $sql = $this->CreaSql();
        $Result_tabTmp = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        // $Result_tab2 = $this->elaboraRecordsXls($Result_tabTmp, $ArrayCampi);
        $xlsxWriter->setDataFromArray($Result_tabTmp, $sheet);
        $xlsxWriter->setRenderFieldsMetadata($model);
        $xlsxWriter->createCustom();
        // Out::msgInfo('xlswrite', print_r($xlsxWriter,true));
        $filename = $this->nameForm . time() . rand(0, 1000) . '.xlsx';
        $tempPath = itaLib::getAppsTempPath() . "/" . $filename;
        $xlsxWriter->writeToFile($tempPath);
        Out::openDocument(utiDownload::getUrl($filename, $tempPath, true));
    }

    public function GetInfoSogg($Rowid) {
        if (!$Rowid) {
            return false;
        }
        $sql = "SELECT ANA_RUOLISOGGETTI.ROW_ID_PRESTATORE,
                ANA_SOGGETTI.*
                FROM ANA_RUOLISOGGETTI 
                INNER JOIN ANA_SOGGETTI ON ANA_SOGGETTI.ROWID = ANA_RUOLISOGGETTI.ROW_ID_PRESTATORE 
                WHERE ANA_RUOLISOGGETTI.ROW_ID = " . $Rowid;
        $SoggettoTmp_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$SoggettoTmp_rec) {
            return false;
        }
        $sql = "SELECT
        ANA_RECAPITISOGGETTI.RECAPITO,
        ANA_RECAPITISOGGETTI.ROW_ID_ANARECAPITO,
        ANA_RECAPITISOGGETTI.NOTE AS NOTERECAPITO,
        ANA_RECAPITISOGGETTI.PREDEFINITO,
        ANA_RECAPITISOGGETTI.DATAVALFIN AS SCADENZARECAPITO,
        ANA_COMUNE.ANADES 
        FROM ANA_RECAPITISOGGETTI 
        LEFT JOIN ANA_COMUNE ON ANA_COMUNE.ROWID = ANA_RECAPITISOGGETTI.ROW_ID_ANARECAPITO 
        WHERE ANA_RECAPITISOGGETTI.ROW_ID_SOGGETTO = " . $SoggettoTmp_rec['ROWID'] . " AND ANA_COMUNE.ANACAT = 'RIF' AND (DATAVALFIN >= ".date ("Ymd")." OR DATAVALFIN = '')";
        $RecapitiTmp_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        
        $testo = "<b>" . $SoggettoTmp_rec['COGNOME'] . " " . $SoggettoTmp_rec['NOME'] . "</b><br>" .
                "Codie Fiscale : " . $SoggettoTmp_rec['CF'] . "<br>Partita Iva : " . $SoggettoTmp_rec['PIVA'] .
                "<br>Annotazioni : " . $SoggettoTmp_rec['NOTE'];

        foreach ($RecapitiTmp_tab as $key => $RecapitiTmp_rec) {
            if ($key == 0) {
                $testo .= "<br><b>Eventuali Recapiti<br></b>";
            }
            $testo .= "<b>" . strtolower($RecapitiTmp_rec['ANADES']) . "</b> : " . $RecapitiTmp_rec['RECAPITO'];
            if($RecapitiTmp_rec['PREDEFINITO'] == 1){
                $testo .= "<b> Predefinito </b>";
            }
            if($RecapitiTmp_rec['NOTERECAPITO']){
             $testo.= ' // '.$RecapitiTmp_rec['NOTERECAPITO'];

            }
            $testo.= "<br>";
        }

        return $testo;
    }

}
