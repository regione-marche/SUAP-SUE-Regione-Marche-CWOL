<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';

function praFoDecodeGest() {
    $praFoDecodeGest = new praFoDecodeGest();
    $praFoDecodeGest->parseEvent();
    return;
}

class praFoDecodeGest extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $nameForm = "praFoDecodeGest";
    public $chiave;
    public $sorgente;
    public $modifica;
    public $dialog;
    public $gridRichieste = "praFoDecodeGest_gridRichieste";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->chiave = App::$utente->getKey($this->nameForm . '_chiave');
            $this->sorgente = App::$utente->getKey($this->nameForm . '_sorgente');
            $this->modifica = App::$utente->getKey($this->nameForm . '_modifica');
            $this->dialog = App::$utente->getKey($this->nameForm . '_dialog');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->returnToParent();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_chiave', $this->chiave);
            App::$utente->setKey($this->nameForm . '_sorgente', $this->sorgente);
            App::$utente->setKey($this->nameForm . '_modifica', $this->modifica);
            App::$utente->setKey($this->nameForm . '_dialog', $this->dialog);
        }
    }

    public function setTipoFo($sorgente) {
        $this->sorgente = $sorgente;
    }

    public function setChiaveFo($chiave) {
        $this->chiave = $chiave;
    }

    public function setModifica($modifica = false) {
        $this->modifica = $modifica;
    }

    public function setDialog($dialog = false) {
        $this->dialog = $dialog;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->openGestione();
                break;
            //Evento che si verifica quando rinfresca una griglia
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRichieste:
                        TableView::clearGrid($this->gridRichieste);
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $Result_tab = $ita_grid01->getDataArray();
                        if (!$ita_grid01->getDataPageFromArray('json', $Result_tab) && $_POST['_search'] !== 'true') {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            //Ritorna alla videata della ricerca
                            $this->openRicerca();
                        }
                        break;
                }
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $Result_tab1 = $this->praLib->getGenericTab($sql);
                $Result_tab2 = $Result_tab1;
                //$Result_tab2 = $this->elaboraRecord($Result_tab1);
                $ita_grid02 = new TableView($this->gridRichieste, array(
                    'arrayTable' => $Result_tab2));
                $ita_grid02->setSortIndex('FOTIPO');
                $ita_grid02->exportXLS('', 'associazione_procedimenti.xls');
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praCtrProc', $parameters);
                break;


            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRichieste:

                        //Out::msgInfo("Valore POST", print_r($_POST, true));

                        $this->setModifica(true);

                        $this->Dettaglio($_POST['rowid']);

                        $this->openGestione();

                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {

                    case $this->gridRichieste:
                        //Out::msgInfo("Valore POST", print_r($_POST, true));
                        //Assegnato, altrimenti non cancella il record
                        Out::valore($this->nameForm . '_PRAFODECODE[ROW_ID]', $_POST['rowid']);

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $prafodecode_rec = $_POST[$this->nameForm . '_PRAFODECODE'];

                        $prafodecode_rec = $this->decodifica($prafodecode_rec);

                        // Messo, perchè se la combo è diabilitata non passa il valore FOTIPO.
                        // Questo capita nel caricamento dai Fascicoli Elettronici
                        if (isset($this->sorgente)) {
                            $prafodecode_rec['FOTIPO'] = $this->sorgente;
                        }


                        $insert_info = 'Oggetto: ' . $prafodecode_rec['FOTIPO'] . ' Codice' . $prafodecode_rec['FOSRCKEY'];


                        if (!$this->insertRecord($this->PRAM_DB, 'PRAFODECODE', $prafodecode_rec, $insert_info)) {
                            $this->unlock($lock);
                            break;
                        }

                        if ($this->dialog) {
                            $this->returnToParent();
                        } else {
                            $this->setChiaveFo('');
                            $this->openGestione();
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':

                        $prafodecode_rec = $_POST[$this->nameForm . '_PRAFODECODE'];

                        $prafodecode_rec = $this->decodifica($prafodecode_rec);

                        $update_info = 'Oggetto: ' . $prafodecode_rec['FOTIPO'] . ' Codice' . $prafodecode_rec['FOSRCKEY'];

                        if (!$this->updateRecord($this->PRAM_DB, 'PRAFODECODE', $prafodecode_rec, $update_info, 'ROW_ID')) {
                            break;
                        }

                        if ($this->dialog) {
                            $this->returnToParent();
                        } else {
                            $this->setChiaveFo('');
                            $this->openGestione();
                        }

                        break;

                    case $this->nameForm . '_Nuovo': // Evento bottone Nuovo

                        $this->mostraForm('divGestione');
                        Out::clearFields($this->nameForm . '_divGestione');

                        $this->mostraButtonBar(array('Aggiungi', 'AltraRicerca'));

                        Out::enableField($this->nameForm . '_PRAFODECODE[FOTIPO]');
                        Out::enableField($this->nameForm . '_PRAFODECODE[FOSRCKEY]');

                        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTEVCOD]');
                        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTSTT]');
                        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTATT]');
                        Out::disableField($this->nameForm . '_FODESTPRO');

                        Out::hide($this->nameForm . '_PRAFODECODE[ATTIVO]');

                        Out::valore($this->nameForm . '_PRAFODECODE[ROW_ID]', 0);

                        Out::setFocus($this->nameForm, $this->nameForm . '_PRAFODECODE[FOTIPO]');

                        break;

                    case $this->nameForm . '_Cancella':

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );


                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $prafodecode_rec = $_POST[$this->nameForm . '_PRAFODECODE'];

                        $delete_Info = 'Oggetto: ' . $prafodecode_rec['FOTIPO'] . " " . $prafodecode_rec['FOSRCKEY'];
                        if ($this->deleteRecord($this->PRAM_DB, 'PRAFODECODE', $prafodecode_rec['ROW_ID'], $delete_Info, "ROW_ID")) {
                            $this->setChiaveFo('');
                            $this->openGestione();
                        }

                        break;


                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $this->Elenca();
                        break;


                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        $this->AzzeraVariabili();
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_PRAFODECODE[CODICETIPOIMPO]_butt':
                        praRic::ricAnatipimpo($this->nameForm);
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTTSP]_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTSTT]_butt':
                        praRic::praRicAnaset($this->nameForm, '', $_POST[$this->nameForm . '_PRAFODECODE']['FODESTATT']);
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTATT]_butt':

                        $settore = $_POST[$this->nameForm . '_PRAFODECODE']['FODESTSTT'];

                        if ($settore) {
                            $where = "AND ATTSET = $settore";
                            praRic::praRicAnaatt($this->nameForm, $where);
                        } else {
                            Out::msgInfo("Attenzione", "Scegliere prima un settore");
                        }
                        break;

                    //case $this->nameForm . '_PRAFODECODE[FODESTPRO]_butt':
                    case $this->nameForm . '_cercaProcedimento_butt':
                        //Se presente Codice Sportello, far vedere solo i procedimenti collegati
                        //allo sportello inserito
                        $condizione = "";
                        $sportello = $_POST[$this->nameForm . '_PRAFODECODE']['FODESTTSP'];
                        if (isset($sportello) && $sportello != '') {
                            $condizione = "ITEEVT.IEVTSP = " . $sportello;
                        }
                        //$condizione = "ITEEVT.IEVTSP = 1";
                        praRic::praRicAnapra($this->nameForm, 'Procedimenti', '', $condizione, '', true);
                        break;

                    case $this->nameForm . '_cancellaProcedimento_butt':

                        $this->decodifica(array(
                            //'FODESTTSP' => "",
                            'FODESTSTT' => "",
                            'FODESTATT' => "",
                            'FODESTPRO'=> "",
                            'FODESTEVCOD' => ""
                        ));

//                        
//                        if ($this->riepistru_rec['IDATTIVORIG'] > 0){
//                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
//                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaAttOrig', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                    )
//                            );
//                        }
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTEVCOD]_butt':
                        praRic::ricAnaeventi($this->nameForm);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PRAFODECODE[CODICETIPOIMPO]':
                        $this->decodifica(array(
                            'CODICETIPOIMPO' => $_POST[$this->nameForm . '_PRAFODECODE']['CODICETIPOIMPO']
                        ));
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTTSP]':
                        $this->decodifica(array(
                            'FODESTTSP' => $_POST[$this->nameForm . '_PRAFODECODE']['FODESTTSP']
                        ));
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTSTT]':
                        $this->decodifica(array(
                            'FODESTSTT' => $_POST[$this->nameForm . '_PRAFODECODE']['FODESTSTT']
                        ));

                        if ($_POST[$this->nameForm . '_PRAFODECODE']['FODESTATT']) {
                            $anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_PRAFODECODE']['FODESTATT']);

                            if ($_POST[$this->nameForm . '_PRAFODECODE']['FODESTSTT'] != $anaatt_rec['ATTSET']) {
                                $this->decodifica(array(
                                    'FODESTATT' => ""
                                ));
                            }
                        }
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTATT]':
                        $settore = $_POST[$this->nameForm . '_PRAFODECODE']['FODESTSTT'];

                        if ($settore) {
                            $this->decodifica(array(
                                'FODESTSTT' => $settore,
                                'FODESTATT' => $_POST[$this->nameForm . '_PRAFODECODE']['FODESTATT']
                            ));
                        } else {
                            $this->decodifica(array(
                                'FODESTATT' => ""
                            ));
                        }
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTPRO]':
                        $codice = $_POST[$this->nameForm . '_PRAFODECODE']['FODESTPRO'];

                        if ($codice != '*') {
                            $codice = str_pad($codice, '6', '0', STR_PAD_LEFT);
                        }

//                        $codSettore = '0';
//                        $codAttivita = '0';
//                        $anapra_rec = $this->praLib->GetAnapra($codice);
//                        if ($anapra_rec){
//                            $codSettore = $anapra_rec['PRASTT'];
//                            $codAttivita = $anapra_rec['PRAATT'];
//                        }
//
//                        if (isset($iteevt_rec['IEVCOD'])) {
//                            $this->decodifica(array(
//                                'FODESTEVCOD' => $iteevt_rec['IEVCOD']
//                            ));
//                        } else {
//                            $this->decodifica(array(
//                                'FODESTEVCOD' => 0
//                            ));
//                        }


                        $this->decodifica(array(
                            'FODESTPRO' => $codice
                        ));

//                        $this->decodifica(array(
//                            'FODESTPRO' => $codice,
//                            'FODESTSTT' => $codSettore,
//                            'FODESTATT' => $codAttivita
//
//                        ));
                        break;

                    case $this->nameForm . '_PRAFODECODE[FODESTEVCOD]':
                        $codice = $_POST[$this->nameForm . '_PRAFODECODE']['FODESTEVCOD'];

                        if ($codice != '*') {
                            $codice = str_pad($codice, '6', '0', STR_PAD_LEFT);
                        }

                        $this->decodifica(array(
                            'FODESTEVCOD' => $codice
                        ));
                        break;
                }
                break;

            case 'returnAnatsp':
                $this->decodifica(array(
                    'FODESTTSP' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnaset':
                $this->decodifica(array(
                    'FODESTSTT' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnaatt':
                $this->decodifica(array(
                    'FODESTATT' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnapra':
                //Out::msgInfo("Record ANAPRA", print_r($_POST['rowData'],true));
                $this->decodifica(array(
                    'FODESTPRO' => $_POST['rowData']['ID_ANAPRA']
                        ), 'rowid');

                $iteevt_rec = $this->praLib->GetIteevt($_POST['rowData']['ID_ITEEVT'], "rowId");
                if (isset($iteevt_rec['IEVSTT'])) {
                    $this->decodifica(array(
                        'FODESTSTT' => $iteevt_rec['IEVSTT']
                    ));
                } else {
                    $this->decodifica(array(
                        'FODESTSTT' => 0
                    ));
                }
                if (isset($iteevt_rec['IEVATT'])) {
                    $this->decodifica(array(
                        'FODESTATT' => $iteevt_rec['IEVATT']
                    ));
                } else {
                    $this->decodifica(array(
                        'FODESTATT' => 0
                    ));
                }

                if (isset($iteevt_rec['IEVCOD'])) {
                    $this->decodifica(array(
                        'FODESTEVCOD' => $iteevt_rec['IEVCOD']
                    ));
                } else {
                    $this->decodifica(array(
                        'FODESTEVCOD' => 0
                    ));
                }


                if (isset($iteevt_rec['IEVTSP'])) {
                    $this->decodifica(array(
                        'FODESTTSP' => $iteevt_rec['IEVTSP']
                    ));
                } else {
                    $this->decodifica(array(
                        'FODESTTSP' => 0
                    ));
                }



                break;

            case 'returnAnaeventi':
                $this->decodifica(array(
                    'FODESTEVCOD' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'retRicAnatipimpo':
                $this->decodifica(array(
                    'CODICETIPOIMPO' => $_POST['retKey']
                        ), 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_chiave');
        App::$utente->removeKey($this->nameForm . '_sorgente');
        App::$utente->removeKey($this->nameForm . '_modifica');
        App::$utente->removeKey($this->nameForm . '_dialog');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            $_POST = array();
            $_POST['model'] = $this->returnModel;
            $_POST['event'] = $this->returnEvent;
            $_POST['id'] = $this->returnId;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
            $returnModel = itaModel::getInstance($this->returnModel);
            $returnModel->parseEvent();
        }

        if ($close) {
            $this->close();
        }
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_SvuotaRicerca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');

        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    private function inizializza() {
        Out::valore($this->nameForm . '_PRAFODECODE[FOTIPO]', $this->sorgente);
        Out::disableField($this->nameForm . '_PRAFODECODE[FOTIPO]');
        Out::valore($this->nameForm . '_PRAFODECODE[FOSRCKEY]', $this->chiave);
        Out::disableField($this->nameForm . '_PRAFODECODE[FOSRCKEY]');

        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTPRO]');
        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTEVCOD]');
        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTSTT]');
        Out::disableField($this->nameForm . '_PRAFODECODE[FODESTATT]');
        Out::disableField($this->nameForm . '_FODESTPRO');

        Out::hide($this->nameForm . '_PRAFODECODE[ATTIVO]');


        //Se presente valore in $chiave, cercare presenza del record PRAFODECODE
        if (isset($this->chiave)) {

            $where = " AND PRAFODECODE.FOTIPO = '" . $this->sorgente . "'";

            $prafodecode_rec = $this->praLib->GetPrafodecode($this->chiave, 'codSrc', false, $where);
            if ($prafodecode_rec) {

                Out::valore($this->nameForm . '_PRAFODECODE[ROW_ID]', $prafodecode_rec[ROW_ID]);
                Out::valore($this->nameForm . '_PRAFODECODE[FODESTTSP]', $prafodecode_rec[FODESTTSP]);
                Out::valore($this->nameForm . '_PRAFODECODE[FODESTPRO]', $prafodecode_rec[FODESTPRO]);
                Out::valore($this->nameForm . '_PRAFODECODE[FODESTSTT]', $prafodecode_rec[FODESTSTT]);
                Out::valore($this->nameForm . '_PRAFODECODE[FODESTATT]', $prafodecode_rec[FODESTATT]);
                Out::valore($this->nameForm . '_PRAFODECODE[FODESTEVCOD]', $prafodecode_rec[FODESTEVCOD]);

                $anapra_rec = $this->praLib->GetAnapra($prafodecode_rec[FODESTPRO]);
                if ($anapra_rec) {
                    $descAnapra = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] .
                            $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];

                    Out::valore($this->nameForm . '_FODESTPRO', $descAnapra);
                }

                $anatsp_rec = $this->praLib->GetAnatsp($prafodecode_rec[FODESTTSP]);
                if ($anatsp_rec) {
                    Out::valore($this->nameForm . '_FODESTTSP', $anatsp_rec['TSPDES']);
                }

                $anaset_rec = $this->praLib->GetAnaset($prafodecode_rec[FODESTSTT]);
                if ($anaset_rec) {
                    Out::valore($this->nameForm . '_FODESTSTT', $anaset_rec['SETDES']);
                }

                $anaatt_rec = $this->praLib->GetAnaatt($prafodecode_rec[FODESTATT]);
                if ($anaatt_rec) {
                    Out::valore($this->nameForm . '_FODESTATT', $anaatt_rec['ATTDES']);
                }

                $anaeventi_rec = $this->praLib->GetAnaeventi($prafodecode_rec[FODESTEVCOD]);
                if ($anaeventi_rec) {
                    Out::valore($this->nameForm . '_FODESTEVCOD', $anaeventi_rec['EVTDESCR']);
                }
            }
        }
    }

    public function openGestione() {
        if (!$this->chiave) {
            $this->openRicerca();
        } else {
            $this->mostraForm('divGestione');
            Out::clearFields($this->nameForm . '_divGestione');

            if ($this->modifica) {
                if ($this->dialog) {
                    $this->mostraButtonBar(array('Aggiorna'));
                } else {
                    $this->mostraButtonBar(array('Aggiorna', 'Cancella', 'AltraRicerca'));
                }
            } else {
                if ($this->dialog) {
                    $this->mostraButtonBar(array('Aggiungi'));
                } else {
                    $this->mostraButtonBar(array('Aggiungi', 'AltraRicerca'));
                }
            }

            $this->inizializza();

            Out::setFocus($this->nameForm, $this->nameForm . '_PRAFODECODE[FOTIPO]');
        }
    }

    function openRicerca() {
        $this->mostraForm('divRicerca');
        Out::clearFields($this->nameForm . '_divGestione');

        $this->mostraButtonBar(array('Nuovo', 'Elenca', 'SvuotaRicerca'));

        $this->inizializza();

        Out::setFocus($this->nameForm, $this->nameForm . '_PRAFODECODE[FOTIPO]');
    }

    public function decodifica($prafodecode_rec, $key = 'codice') {
        $decodFields = array(
            'CODICETIPOIMPO',
            'FODESTTSP',
            'FODESTSTT',
            'FODESTATT',
            'FODESTPRO',
            'FODESTEVCOD'
        );

        foreach ($decodFields as $field) {
            if (isset($prafodecode_rec[$field])) {
                $nodecode = '';
                $nodevalue = '';
                if ($prafodecode_rec[$field]) {
                    switch ($field) {
                        case 'CODICETIPOIMPO':
                            $anatipimpo_rec = $this->praLib->GetAnatipimpo($prafodecode_rec['CODICETIPOIMPO'], $key);
                            if ($anatipimpo_rec) {
                                $nodecode = $anatipimpo_rec['CODTIPOIMPO'];
                                $nodevalue = $anatipimpo_rec['DESCTIPOIMPO'];
                            }
                            break;

                        case 'FODESTTSP':
                            $anatsp_rec = $this->praLib->GetAnatsp($prafodecode_rec['FODESTTSP'], $key);
                            if ($anatsp_rec) {
                                $nodecode = $anatsp_rec['TSPCOD'];
                                $nodevalue = $anatsp_rec['TSPDES'];
                            } else {
                                $nodecode = '';
                                $nodevalue = 'Tutti';
                            }
                            break;

                        case 'FODESTSTT':
                            $anaset_rec = $this->praLib->GetAnaset($prafodecode_rec['FODESTSTT'], $key);
                            if ($anaset_rec) {
                                $nodecode = $anaset_rec['SETCOD'];
                                $nodevalue = $anaset_rec['SETDES'];
                            }
                            break;

                        case 'FODESTATT':
                            $anaatt_rec = $this->praLib->GetAnaatt($prafodecode_rec['FODESTATT'], $key);
                            if ($anaatt_rec) {
                                $nodecode = $anaatt_rec['ATTCOD'];
                                $nodevalue = $anaatt_rec['ATTDES'];
                            }
                            break;

                        case 'FODESTPRO':
                            $anapra_rec = $this->praLib->GetAnapra($prafodecode_rec['FODESTPRO'], $key);
                            if ($anapra_rec) {
                                $nodecode = $anapra_rec['PRANUM'];
                                $nodevalue = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
                            }
                            break;

                        case 'FODESTEVCOD':
                            $anaeventi_rec = $this->praLib->GetAnaeventi($prafodecode_rec['FODESTEVCOD'], $key);
                            if ($anaeventi_rec) {
                                $nodecode = $anaeventi_rec['EVTCOD'];
                                $nodevalue = $anaeventi_rec['EVTDESCR'];
                            }
                            break;
                    }
                }

                Out::valore($this->nameForm . "_PRAFODECODE[$field]", $nodecode);
                Out::valore($this->nameForm . "_$field", $nodevalue);

                $prafodecode_rec[$field] = $nodecode;
            }
        }

        return $prafodecode_rec;
    }

    private function CreaCombo() {

        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES_DESCRIPTIONS as $key => $value) {
            //foreach ($tab as $rec) {
            Out::select($this->nameForm . '_tipoPortale', 1, $key, "0", $value);
        }

        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES_DESCRIPTIONS as $key => $value) {
            Out::select($this->nameForm . '_PRAFODECODE[FOTIPO]', 1, $key, "0", $value);
        }
    }

    function Elenca() {
        //Out::msgInfo("Valore POST", print_r($_POST, true));
        Out::clearFields($this->nameForm . '_divRisultato');
        $this->mostraForm('divRisultato');

        $this->mostraButtonBar(array('Nuovo', 'AltraRicerca'));

        TableView::enableEvents($this->gridRichieste);
        // Questo metodo richiama 'onClickTablePager'. che gestisce
        // la visualizzazione dei dati della griglia
        TableView::reload($this->gridRichieste);

//        TableView::enableEvents($this->nameForm . '_gridRichieste');
//        TableView::reload($this->nameForm . '_gridRichieste');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $tipo = $_POST[$this->nameForm . '_tipoPortale'];
        $chiave = $_POST[$this->nameForm . '_chiave'];

        $sql = "SELECT
                PRAFODECODE.ROW_ID AS ROW_ID,
                PRAFODECODE.FOTIPO AS FOTIPO,
                PRAFODECODE.FOSRCKEY AS FOSRCKEY,
                PRAFODECODE.FODESTPRO AS FODESTPRO,
                PRAFODECODE.FODESTEVCOD AS FODESTEVCOD,
                PRAFODECODE.FODESTTSP AS FODESTTSP,
                PRAFODECODE.FODESTSTT AS FODESTSTT,
		PRAFODECODE.FODESTATT AS FODESTATT,
                ANAPRA.PRADES__1 AS PRADES__1,
                ANAEVENTI.EVTDESCR AS EVTDESCR,
                ANASET.SETDES AS SETDES,
                ANAATT.ATTDES AS ATTDES,
                ANATSP.TSPDES AS TSPDES
             FROM PRAFODECODE PRAFODECODE
                LEFT OUTER JOIN ANAPRA ANAPRA ON PRAFODECODE.FODESTPRO=ANAPRA.PRANUM
                LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON PRAFODECODE.FODESTEVCOD=ANAEVENTI.EVTCOD
                LEFT OUTER JOIN ANASET ANASET ON PRAFODECODE.FODESTSTT=ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ANAATT ON PRAFODECODE.FODESTATT=ANAATT.ATTCOD
                LEFT OUTER JOIN ANATSP ANATSP ON PRAFODECODE.FODESTTSP=ANATSP.TSPCOD

             WHERE 1 ";


        if ($tipo) {
            $sql .= " AND FOTIPO = '$tipo'";
        }

        if ($chiave) {
            $sql .= " AND FOSRCKEY LIKE '%" . $chiave . "%'";
        }

        //Out::msgInfo("Query SQL", $sql);

        return $sql;
    }

    function AzzeraVariabili($pulisciRicerca = true) {
        if ($pulisciRicerca) {
            Out::clearFields($this->nameForm, $this->divRicerca);
        }
    }

    public function Dettaglio($Indice) {
        $idPraFoList = $Indice;
        $prafodecode_rec = $this->praLib->GetPrafodecode($Indice);
        //$this->idPraFoList = $Indice;
        //$prafodecode_rec = $this->praLib->GetPrafolist($Indice);
        Out::valori($prafodecode_rec, $this->nameForm . '_PRAFODECODE');

        $this->setChiaveFo($prafodecode_rec['FOSRCKEY']);
        $this->setTipoFo($prafodecode_rec['FOTIPO']);
    }

}
