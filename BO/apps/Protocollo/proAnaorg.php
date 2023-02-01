<?php

/**
 *
 * GESTIONE ITER TRASMISSIONI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    02.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function proAnaorg() {
    $proAnaorg = new proAnaorg();
    $proAnaorg->parseEvent();
    return;
}

class proAnaorg extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proAnaorg";
    public $divGes = "proAnaorg_divGestione";
    public $divRis = "proAnaorg_divRisultato";
    public $divRic = "proAnaorg_divRicerca";
    public $gridAnaorg = "proAnaorg_gridAnaorg";
    public $gridAllegati = "proAnaorg_gridAllegati";
    public $returnField = '';
    public $returnModel = '';
    public $allegati = '';
    public $appoggio = '';

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
        $this->appoggio = App::$utente->getKey($this->nameForm . '_appoggio');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_appoggio', $this->appoggio);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                if ($_POST[$this->nameForm . '_returnField'] == '') {
                    $this->OpenRicerca();
                    TableView::disableEvents($this->gridAnaorg);
                } else {
                    $this->SetNuovo();
                    $this->returnField = $_POST['proAnaorg_returnField'];
                    $this->returnModel = $_POST['proAnaorg_returnModel'];
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaorg:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridAllegati:
//                        App::log($this->allegati);
//                        App::log($_POST['rowid']);
                        if (array_key_exists($_POST['rowid'], $this->allegati) == true && $this->allegati[$_POST['rowid']]['DOCFIL'] != '') {
                            $destinazione = $this->proLib->SetDirectory($this->allegati[$_POST['rowid']]['PRONUM'], $this->allegati[$_POST['rowid']]['PROPAR']);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $this->allegati[$_POST['rowid']]['DOCNAME'], $destinazione . '/' . $this->allegati[$_POST['rowid']]['DOCFIL']
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaorg:
                        if ($this->ControllaCancella($_POST['rowid'])) {
                            $this->Dettaglio($_POST['rowid']);
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Fascicolo in uso. Non è possibile procedere con la cancellazione.");
                        }
                        break;
                    case $this->gridAllegati:
                        $documento = $this->allegati[$_POST[$this->gridAllegati]['gridParam']['selrow']];
                        if ($documento['PROPAR'] == 'F') {
                            $this->appoggio = $_POST;
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaDoc', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaDoc', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->appoggio = $_POST;
                        Out::msgQuestion("Inserimento di un nuovo Documento.", "Vuoi caricare un File o una dicitura di un Documento?", array(
                            'F6-Dicitura' => array('id' => $this->nameForm . '_CaricaDicitura',
                                'model' => $this->nameForm, 'shortCut' => "f6"),
                            'F5-File' => array('id' => $this->nameForm . '_ConfermaFile',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaorg, array('sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('ORGDES');
                $ita_grid01->exportXLS('', 'Anaorg.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($_POST['sidx'] == 'ORGDES') {
                    $ordinamento = 'ORGDES';
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaorg, array('sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnaorg', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        try {   // Effettuo la FIND
                            $sql = $this->CreaSql();
                            $ita_grid01 = new TableView($this->gridAnaorg, array(
                                'sqlDB' => $this->PROT_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridAnaorg]['gridParam']['rowNum']);
                            $ita_grid01->setSortIndex('ORGCCF');
                            $ita_grid01->setSortOrder('asc');

//                            $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
//                            if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '', 0);
                                Out::hide($this->divRic, '', 0);
                                Out::show($this->divRis, '', 0);
                                $this->Nascondi();
                                if ($_POST[$this->nameForm . "_Stato"] == "A" && $_POST[$this->nameForm . "_DaData"] && $_POST[$this->nameForm . "_AData"]) {
                                    Out::show($this->nameForm . '_Chiudi');
                                }
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridAnaorg);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                            App::log($e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Catcod_butt':
                        $where = " WHERE CATDAT" . $this->PROT_DB->isBlank();
                        proRic::proRicCat($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_CATCOD_butt':
                        $where = " WHERE CATDAT" . $this->PROT_DB->isBlank();
                        proRic::proRicCat($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        $where = " WHERE CLADAT" . $this->PROT_DB->isBlank();
                        if ($_POST[$this->nameForm . '_Catcod'] != "") {
                            $codice = $_POST[$this->nameForm . '_Catcod'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $where = $where . " AND CLACAT = '$codice'";
                        }
                        proRic::proRicCla($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_CLACOD_butt':
                        $where = " WHERE CLADAT" . $this->PROT_DB->isBlank();
                        if ($_POST[$this->nameForm . '_CATCOD'] != "") {
                            $codice = $_POST[$this->nameForm . '_CATCOD'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $where = $where . " AND CLACAT = '$codice'";
                        }
                        proRic::proRicCla($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        $where = " WHERE FASDAT" . $this->PROT_DB->isBlank();
                        if ($_POST[$this->nameForm . '_Catcod'] != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $where = $where . " AND FASCCA LIKE '" . $codice1 . "____'";
                        }
                        if ($_POST[$this->nameForm . '_Clacod'] != "") {
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $where = $where . " AND FASCCA LIKE '____" . $codice2 . "'";
                        }
                        proRic::proRicFas($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_FASCOD_butt':
                        $where = " WHERE FASDAT" . $this->PROT_DB->isBlank();
                        if ($_POST[$this->nameForm . '_CATCOD'] != "") {
                            $codice1 = $_POST[$this->nameForm . '_CATCOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $where = $where . " AND FASCCA LIKE '" . $codice1 . "____'";
                        }
                        if ($_POST[$this->nameForm . '_CLACOD'] != "") {
                            $codice2 = $_POST[$this->nameForm . '_CLACOD'];
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $where = $where . " AND FASCCA LIKE '____" . $codice2 . "'";
                        }
                        proRic::proRicFas($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->SetNuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        // Controlli
                        if (trim($_POST[$this->nameForm . '_CATCOD']) != '') {
                            if (trim($_POST[$this->nameForm . '_CLACOD']) == '') {
                                $_POST[$this->nameForm . '_FASCOD'] = '';
                            }
                        } else {
                            $_POST[$this->nameForm . '_CLACOD'] = '';
                            $_POST[$this->nameForm . '_FASCOD'] = '';
                        }
                        $titolario = $_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] . $_POST[$this->nameForm . '_FASCOD'];
                        $codice = str_pad($_POST[$this->nameForm . '_ANAORG']['ORGCOD'], 6, "0", STR_PAD_LEFT);
                        $anno = $_POST[$this->nameForm . '_ANAORG']['ORGANN'];
                        if (!$anno) {
                            $anno = date("Y");
                        }
                        $uniOpe = $_POST[$this->nameForm . '_ANAORG']['ORGAOO'];
                        $descrizione = $_POST[$this->nameForm . '_ANAORG']['ORGDES'];
                        //$sql = "SELECT ORGCCF FROM ANAORG WHERE ORGCCF='$titolario' AND ORGCOD='$codice'";
                        //$Anaorg_tab = $this->proLib->getGenericTab($sql);
                        //if (!$Anaorg_tab) {
                        $Anaorg_rec = $_POST[$this->nameForm . '_ANAORG'];
                        $new_fascicolo_rowid = $this->proLib->GetNewFascicolo($titolario, $anno, $uniOpe, $descrizione, '');
                        if (!$new_fascicolo_rowid) {
                            Out::msgStop("Creazione fascicolo", "Creazione Fascicolo Fallita");
                            break;
                        }
                        //$this->OpenRicerca();
//                        App::log('$new_fascicolo_rowid');
//                        App::log($new_fascicolo_rowid);
                        $this->Dettaglio($new_fascicolo_rowid);
//                        } else {
//                            Out::msgInfo("Codice già  presente", "Combinazione Categoria, Classe, Sottoclasse e Fascia già  presente. Modificare i valori!");
//                            Out::setFocus('', $this->nameForm . '_CATCOD');
//                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anaorg_rec = $_POST[$this->nameForm . '_ANAORG'];
                        $update_Info = 'Oggetto: ' . $Anaorg_rec['ORGCOD'] . " " . $Anaorg_rec['ORGDES'];
                        if ($this->updateRecord($this->PROT_DB, 'ANAORG', $Anaorg_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        if ($this->ControllaCancella($_POST[$this->nameForm . 'ANAORG']['ROWID'])) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Fascicolo in uso. Non è possibile procedere con la cancellazione.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anaorg_rec = $_POST[$this->nameForm . '_ANAORG'];
                        try {
                            $codice = $_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] . $_POST[$this->nameForm . '_FASCOD']
                                    . $_POST[$this->nameForm . '_ANAORG']['ORGCOD'];
                            $sql = "SELECT PROCHI FROM ANAPRO WHERE PROCHI='$codice'";
                            $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            //     Controllo se è usato in altre anagrafiche e nelle procedure
                            if ($Anapro_tab != null) {
                                Out::msgStop("Errore! Impossibile cancellare il Fascicolo perchè assegnato a Procedure.", $e->getMessage());
                            } else {
                                $delete_Info = 'Oggetto: ' . $_POST[$this->nameForm . '_ANAORG']['ORGCOD'] . " "
                                        . $_POST[$this->nameForm . '_ANAORG']['ORGDES'];
                                if ($this->deleteRecord($this->PROT_DB, 'ANAORG', $_POST[$this->nameForm . '_ANAORG']['ROWID'], $delete_Info)) {
                                    $this->OpenRicerca();
                                }
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Chiudi':
                        $this->proLib->GetMsgInputPassword($this->nameForm, "Chiusura Fascicoli");
                        break;
                    case $this->nameForm . "_returnPassword":
                        if (!$this->proLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnaorg, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $result_tab = $ita_grid01->getDataArray();
                        if ($result_tab) {
                            foreach ($result_tab as $result_rec) {
                                $Anaorg_rec = $this->proLib->GetAnaorg($result_rec['ROWID'], "rowid");
                                $err = false;
                                $Anaorg_rec['ORGDAT'] = date("Ymd");
                                $update_Info = "Oggetto: Chiudo il fascicolo " . $Anaorg_rec['ORGCOD'] . "-" . $Anaorg_rec['ORGDES'];
                                if (!$this->updateRecord($this->PROT_DB, 'ANAORG', $Anaorg_rec, $update_Info)) {
                                    Out::msgStop("ATTENZIONE!", "Errore nella chiusura del fascicolo " . $Anaorg_rec['ORGCOD'] . "-" . $Anaorg_rec['ORGDES']);
                                    $err = true;
                                    break;
                                }
                            }
                            if (!$err) {
                                Out::msgInfo("Chiusura Fascicoli", "Chiusi Correttamente " . count($result_tab) . " Fascicoli");
                                $this->OpenRicerca();
                            }
                        }
                        break;
                    case $this->nameForm . '_Prenota':
                        // Controlli
                        if (trim($_POST[$this->nameForm . '_CATCOD']) != '') {
                            if (trim($_POST[$this->nameForm . '_CLACOD']) == '') {
                                $_POST[$this->nameForm . '_FASCOD'] = '';
                            }
                        } else {
                            $_POST[$this->nameForm . '_CLACOD'] = '';
                            $_POST[$this->nameForm . '_FASCOD'] = '';
                        }
                        $codice = $_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] . $_POST[$this->nameForm . '_FASCOD'];
                        $sql = "SELECT ORGCOD FROM ANAORG WHERE ORGCCF = '$codice' AND ORGCOD LIKE '" . date(y) . "%' ORDER BY ORGCOD DESC";
                        $Anaorg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                        if ($Anaorg_tab == null) {
                            $codice = date(y) . "0001";
                        } else {
                            $codice = $Anaorg_tab[0]['ORGCOD'] + 1;
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        }
                        Out::valore($this->nameForm . '_ANAORG[ORGCOD]', $codice);
                        break;
                    case $this->nameForm . '_ConfermaFile':
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->nameForm . '_CaricaGridDaAllegare';
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Allegati al Fascicolo';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->nameForm . '_CaricaDicitura':
                        $valori[] = array(
                            'label' => array(
                                'value' => "Inserire la Dicitura del Documento.",
                                'style' => 'width:350px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_dicituraDoc',
                            'name' => $this->nameForm . '_dicituraDoc',
                            'type' => 'text',
                            'style' => 'margin:2px;width:350px;',
                            'value' => ''
                        );
                        Out::msgInput(
                                'Fascicolazione.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaInputDicitura',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->nameForm . '_ConfermaInputDicitura':
                        $sql = "SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='" . $this->appoggio[$this->nameForm . '_ANAORG']['ORGKEY'] . "'";
                        $anapro_rec = $this->proLib->getGenericTab($sql, false);
                        $iteKey = $this->proLib->IteKeyGenerator($anapro_rec['PRONUM'], '', '', $anapro_rec['PROPAR']);
                        $anadoc_rec = array();
                        $anadoc_rec['DOCKEY'] = $iteKey;
                        $anadoc_rec['DOCNUM'] = $anapro_rec['PRONUM'];
                        $anadoc_rec['DOCPAR'] = $anapro_rec['PROPAR'];
                        $anadoc_rec['DOCFIL'] = '';
                        $anadoc_rec['DOCLNK'] = '';
                        $anadoc_rec['DOCUTC'] = '';
                        $anadoc_rec['DOCUTE'] = 'NOTE DOCUMENTO';
                        $anadoc_rec['DOCNOT'] = $_POST[$this->nameForm . '_dicituraDoc'];
                        $anadoc_rec['DOCNAME'] = '';
                        $anadoc_rec['DOCTIPO'] = 'ALLEGATO';
                        $anadoc_rec['DOCMD5'] = '';
                        $anadoc_rec['DOCSHA2'] = '';
                        $insert_Info = 'Oggetto : ' . $anadoc_rec['DOCKEY'] . " " . $anadoc_rec['DOCUTE'];
                        $this->insertRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $insert_Info);
                        $this->caricaAllegatiFascicolo($this->appoggio[$this->nameForm . '_ANAORG']['ORGKEY']);
                        break;
                    case $this->nameForm . '_ConfermaCancellaDoc':
                        $documento = $this->allegati[$_POST[$this->gridAllegati]['gridParam']['selrow']];
                        if ($documento['PROPAR'] == 'F') {
                            if ($documento['DOCFIL'] != '') {
                                $destinazione = $this->proLib->SetDirectory($documento['PRONUM'], $documento['PROPAR']);
                                @unlink($destinazione . '/' . $documento['DOCFIL']);
                            }
                            $delete_Info = 'Oggetto: ' . $documento['DOCKEY'] . " " . $documento['DOCNOT'];
                            if ($this->deleteRecord($this->PROT_DB, 'ANADOC', $documento['ROWID'], $delete_Info)) {
                                $this->caricaAllegatiFascicolo($this->appoggio[$this->nameForm . '_ANAORG']['ORGKEY']);
                            }
                        }
                        break;
                    case $this->nameForm . '_Iter':
                        $sql = "SELECT ARCITE.* 
                            FROM ARCITE 
                            LEFT OUTER JOIN ANAPRO 
                            ON ARCITE.ITEPRO = ANAPRO.PRONUM AND ARCITE.ITEPAR = ANAPRO.PROPAR 
                            WHERE PROPAR='F' AND PROFASKEY='{$_POST[$this->nameForm . '_ANAORG']['ORGKEY']}'";
                        $arcite_rec = $this->proLib->getGenericTab($sql, false);
                        if ($arcite_rec) {
                            $model = 'proGestIter';
                            itaLib::openForm($model);
                            $formObj = itaModel::getInstance($model);
                            $formObj->setReturnModel($this->nameForm);
//                            $formObj->setReturnEvent('returnProDettNote');
                            $formObj->setReturnId('');
                            $_POST = array();
                            $_POST['tipoOpen'] = 'visualizzazione';
                            $_POST['rowidIter'] = $arcite_rec['ROWID'];
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                        }
                        break;
                    case $this->nameForm . '_GestPratica':
                        $model = 'proGestPratica';
                        itaLib::openForm($model);
                        $proGestPratica = itaModel::getInstance($model);
                        $proGestPratica->CreaCombo();
//                        $proGestPratica->setReturnEvent("returnProGestPratica");
//                        $proGestPratica->setReturnModel($this->nameForm);
//                        $proGestPratica->setGeskey($_POST[$this->nameForm . "_ANAORG"]['ORGKEY']);
                        $proGestPratica->Dettaglio($_POST[$this->nameForm . "_ANAORG"]['ORGKEY'], false, "", "geskey");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stato':
                        switch ($_POST[$this->nameForm . '_Stato']) {
                            case "A":
                                Out::show($this->nameForm . '_DaData_field');
                                Out::show($this->nameForm . '_AData_field');
                                break;
                            case "C":
                                Out::show($this->nameForm . '_DaData_field');
                                Out::show($this->nameForm . '_AData_field');
                                break;
                            default:
                                Out::hide($this->nameForm . '_DaData_field');
                                Out::hide($this->nameForm . '_AData_field');
                                break;
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Catcod':
                        $codice = $_POST[$this->nameForm . '_Catcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT CATDES FROM ANACAT WHERE CATDAT" . $this->PROT_DB->isBlank() . " AND CATCOD='$codice'";
                            $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_Catcod', $codice);
                            Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        } else {
                            Out::valore($this->nameForm . '_Catdes', "");
                        }
                        break;
                    case $this->nameForm . '_CATCOD':
                        $codice = $_POST[$this->nameForm . '_CATCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT CATDES FROM ANACAT WHERE CATDAT" . $this->PROT_DB->isBlank() . " AND CATCOD='$codice'";
                            $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_CATCOD', $codice);
                            Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                        } else {
                            Out::valore($this->nameForm . '_CATDES', "");
                        }
                        break;
                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT CLADE1 FROM ANACLA WHERE CLADAT" . $this->PROT_DB->isBlank() . " AND CLACCA='$codice1$codice2'";
                            $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_Clacod', $codice2);
                            Out::valore($this->nameForm . '_Clades', $Anacla_tab[0]['CLADE1']);
                        } else {
                            Out::valore($this->nameForm . '_Clades', "");
                        }
                        break;
                    case $this->nameForm . '_CLACOD':
                        $codice = $_POST[$this->nameForm . '_CLACOD'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_CATCOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT CLADE1 FROM ANACLA WHERE CLADAT" . $this->PROT_DB->isBlank() . " AND CLACCA='$codice1$codice2'";
                            $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_CLACOD', $codice2);
                            Out::valore($this->nameForm . '_CLADES', $Anacla_tab[0]['CLADE1']);
                        } else {
                            Out::valore($this->nameForm . '_CLACOD', "");
                            Out::valore($this->nameForm . '_CLADES', "");
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT FASDE1 FROM ANAFAS WHERE FASDAT" . $this->PROT_DB->isBlank() . " AND FASCCF='$codice1$codice2$codice3'";
                            $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_Fascod', $codice3);
                            Out::valore($this->nameForm . '_Fasdes', $Anafas_tab[0]['FASDE1']);
                        } else {
                            Out::valore($this->nameForm . '_Fasdes', "");
                        }
                        break;
                    case $this->nameForm . '_FASCOD':
                        $codice = $_POST[$this->nameForm . '_FASCOD'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_CATCOD'];
                            $codice2 = $_POST[$this->nameForm . '_CLACOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT FASDE1 FROM ANAFAS WHERE FASDAT" . $this->PROT_DB->isBlank() . " AND FASCCF='$codice1$codice2$codice3'";
                            $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_FASCOD', $codice3);
                            Out::valore($this->nameForm . '_FASDES', $Anafas_tab[0]['FASDE1']);
                        } else {
                            Out::valore($this->nameForm . '_FASDES', "");
                        }
                        break;
                    case $this->nameForm . '_Orgcod':
                        $codice = $_POST[$this->nameForm . '_Orgcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_Orgcod', $codice);
                            $Anaorg_tab = $this->GetAnaorg($codice, $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod']);
                            if (count($Anaorg_tab) == 1) {
                                $this->Dettaglio($Anaorg_tab[0]['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAORG[ORGCOD]':
                        $codice = $_POST[$this->nameForm . '_ANAORG']['ORGCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAORG[ORGCOD]', $codice);
                        }
                        break;
                }
                break;
            case 'returncat':
                $sql = "SELECT CATCOD, CATDES FROM ANACAT WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anacat_tab) != 0) {
                        Out::valore($this->nameForm . '_Catcod', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_CATCOD', $Anacat_tab[0]['CATCOD']);
                        Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                    App::log($e->getMessage());
                }
                break;
            case 'returncla':
                $sql = "SELECT * FROM ANACLA WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anacla_tab) != 0) {
                        $Anacat_tab = $this->GetAnacat($Anacla_tab[0]['CLACAT']);
                        Out::valore($this->nameForm . '_Catcod', $Anacla_tab[0]['CLACAT']);
                        Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_Clacod', $Anacla_tab[0]['CLACOD']);
                        Out::valore($this->nameForm . '_Clades', $Anacla_tab[0]['CLADE1']);
                        Out::valore($this->nameForm . '_CATCOD', $Anacla_tab[0]['CLACAT']);
                        Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_CLACOD', $Anacla_tab[0]['CLACOD']);
                        Out::valore($this->nameForm . '_CLADES', $Anacla_tab[0]['CLADE1']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                    App::log($e->getMessage());
                }
                break;
            case 'returnfas':
                $sql = "SELECT * FROM ANAFAS WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anafas_tab) != 0) {
                        $Anacat_tab = $this->GetAnacat(substr(trim($Anafas_tab[0]['FASCCA']), 0, 4));
                        $Anacla_tab = $this->GetAnacla(substr(trim($Anafas_tab[0]['FASCCA']), 0, 8));
                        Out::valore($this->nameForm . '_Catcod', substr(trim($Anafas_tab[0]['FASCCA']), 0, 4));
                        Out::valore($this->nameForm . '_Catdes', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_Clacod', substr(trim($Anafas_tab[0]['FASCCA']), 4, 4));
                        Out::valore($this->nameForm . '_Clades', $Anacla_tab[0]['CLADE1']);
                        Out::valore($this->nameForm . '_Fascod', $Anafas_tab[0]['FASCOD']);
                        Out::valore($this->nameForm . '_Fasdes', $Anafas_tab[0]['FASDE1']);
                        Out::valore($this->nameForm . '_CATCOD', substr(trim($Anafas_tab[0]['FASCCA']), 0, 4));
                        Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
                        Out::valore($this->nameForm . '_CLACOD', substr(trim($Anafas_tab[0]['FASCCA']), 4, 4));
                        Out::valore($this->nameForm . '_CLADES', $Anacla_tab[0]['CLADE1']);
                        Out::valore($this->nameForm . '_FASCOD', $Anafas_tab[0]['FASCOD']);
                        Out::valore($this->nameForm . '_FASDES', $Anafas_tab[0]['FASDE1']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                    App::log($e->getMessage());
                }
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CaricaGridDaAllegare':
                        $sql = "SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='" . $this->appoggio[$this->nameForm . '_ANAORG']['ORGKEY'] . "'";
                        $anapro_rec = $this->proLib->getGenericTab($sql, false);
//                        App::log($this->appoggio);
//                        App::log($anapro_rec);
//                        App::log($_POST);
                        $destinazione = $this->proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                        if (!$destinazione) {
                            Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                            return false;
                        }
                        foreach ($_POST['retList'] as $allegato) {
                            if (!@rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                                Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                                return false;
                            }
                            $iteKey = $this->proLib->IteKeyGenerator($anapro_rec['PRONUM'], '', '', $anapro_rec['PROPAR']);
                            $anadoc_rec = array();
                            $anadoc_rec['DOCKEY'] = $iteKey;
                            $anadoc_rec['DOCNUM'] = $anapro_rec['PRONUM'];
                            $anadoc_rec['DOCPAR'] = $anapro_rec['PROPAR'];
                            $anadoc_rec['DOCFIL'] = $allegato['FILENAME'];
                            $anadoc_rec['DOCLNK'] = "allegato://" . $allegato['FILENAME'];
                            $anadoc_rec['DOCUTC'] = '';
                            $anadoc_rec['DOCUTE'] = 'ALLEGATO AL FASCICOLO';
                            $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
                            $anadoc_rec['DOCNAME'] = $allegato['FILEORIG'];
                            $anadoc_rec['DOCTIPO'] = 'ALLEGATO';
                            $anadoc_rec['DOCMD5'] = md5_file($destinazione . "/" . $allegato['FILENAME']);
                            $anadoc_rec['DOCSHA2'] = hash_file('sha256', $destinazione . "/" . $allegato['FILENAME']);
                            $insert_Info = 'Oggetto : ' . $anadoc_rec['DOCKEY'] . " " . $anadoc_rec['DOCFIL'];
                            $this->insertRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $insert_Info);
                        }
                        itaLib::deletePrivateUploadPath();
                        $this->caricaAllegatiFascicolo($this->appoggio[$this->nameForm . '_ANAORG']['ORGKEY']);
                        break;
                }
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
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_appoggio');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function OpenRicerca() {
        Out::show($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        Out::hide($this->divGes, '', 200);
        TableView::disableEvents($this->gridAnaorg);
        TableView::clearGrid($this->gridAnaorg);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::valore($this->nameForm . '_Stato', "A");
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Catcod');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Prenota');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_divAllegati');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Iter');
        Out::hide($this->nameForm . '_GestPratica');

        Out::hide($this->nameForm . '_DaData_field');
        Out::hide($this->nameForm . '_AData_field');
        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_CATCOD_field');
            Out::hide($this->nameForm . '_CATDES_field');
            Out::hide($this->nameForm . '_CLACOD_field');
            Out::hide($this->nameForm . '_CLADES_field');
            Out::hide($this->nameForm . '_FASCOD_field');
            Out::hide($this->nameForm . '_FASDES_field');

            Out::hide($this->nameForm . '_Catcod_field');
            Out::hide($this->nameForm . '_Catdes_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Clades_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_Fasdes_field');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_CLACOD_field');
            Out::hide($this->nameForm . '_CLADES_field');
            Out::hide($this->nameForm . '_FASCOD_field');
            Out::hide($this->nameForm . '_FASDES_field');

            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Clades_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_Fasdes_field');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_FASCOD_field');
            Out::hide($this->nameForm . '_FASDES_field');

            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_Fasdes_field');
        }
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $where = "WHERE ORGCOD=ORGCOD";
        if ($_POST[$this->nameForm . '_Orgcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Orgcod'];
            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCOD='$codice'";
        }
        if ($_POST[$this->nameForm . '_Orgdes'] != "") {
            $valore = addslashes(trim($_POST[$this->nameForm . '_Orgdes']));
            $where .= " AND " . $this->PROT_DB->strUpper('ORGDES') . " LIKE '%" . strtoupper($valore) . "%'";
        }
        if ($_POST[$this->nameForm . '_Catcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Catcod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '" . $codice . "%'";
        }
        if ($_POST[$this->nameForm . '_Clacod'] != "") {
            $codice = $_POST[$this->nameForm . '_Clacod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '____" . $codice . "%'";
        }
        if ($_POST[$this->nameForm . '_Fascod'] != "") {
            $codice = $_POST[$this->nameForm . '_Fascod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '________" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_Anno'] != "") {
            $where .= " AND ORGANN='" . $_POST[$this->nameForm . '_Anno'] . "'";
        }
        if ($_POST[$this->nameForm . '_Validita'] != "") {
            $where .= " AND ORGDAT<='" . $_POST[$this->nameForm . '_Validita'] . "'";
        }
//        else {
//            $where .= " AND ORGDAT=''";
//        }
        $Da_data = $_POST[$this->nameForm . '_DaData'];
        $a_data = $_POST[$this->nameForm . '_AData'];
        switch ($_POST[$this->nameForm . '_Stato']) {
            case "A":
                $where .= " AND (ORGAPE<>0 AND ORGDAT=0)";
                if ($Da_data && $a_data) {
                    $where .= " AND (ORGAPE BETWEEN $Da_data AND $a_data)";
                }
                break;
            case "C":
                $where .= " AND (ORGAPE<>0 AND ORGDAT<>0)";
                if ($Da_data && $a_data) {
                    $where .= " AND (ORGDAT BETWEEN $Da_data AND $a_data)";
                }
                break;
        }
        $sql = "
            SELECT
            ANAORG.ROWID AS ROWID,
            ANAORG.ORGCOD AS ORGCOD,
            ANAORG.ORGANN AS ORGANN,
            ANAORG.ORGDES AS ORGDES,
            ANAORG.ORGDAT AS ORGDAT,
            ANAORG.ORGUOF AS ORGUOF,
            ANAFAS.FASCOD AS FASCOD,
            ANACLA.CLACOD AS CLACOD,
            ANACAT.CATCOD AS CATCOD
        FROM
            ANAORG ANAORG
        LEFT OUTER JOIN ANAFAS ANAFAS 
        ON ANAORG.ORGCCF = ANAFAS.FASCCF
        LEFT OUTER JOIN ANACLA ANACLA 
        ON " . $this->PROT_DB->subString('ANAORG.ORGCCF', 1, 8) . " = ANACLA.CLACCA
        LEFT OUTER JOIN ANACAT ANACAT 
        ON " . $this->PROT_DB->subString('ANAORG.ORGCCF', 1, 4) . " = ANACAT.CATCOD  $where";

        return $sql;
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_Stato', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato', 1, "C", "0", "Chiusi");
    }

    public function Dettaglio($_Indice) {
        $Anaorg_rec = ItaDB::DBSQLSelect($this->PROT_DB, "SELECT * FROM ANAORG WHERE ROWID='$_Indice'", false);
        $open_Info = 'Oggetto: ' . $Anaorg_rec['ORGCOD'] . " " . $Anaorg_rec['ORGDES'];
        $this->openRecord($this->PROT_DB, 'ANAORG', $open_Info);
        $this->Nascondi();
        if (strlen($Anaorg_rec['ORGDAT']) < 8) {
            $Anaorg_rec['ORGDAT'] = '';
        }
        Out::valori($Anaorg_rec, $this->nameForm . '_ANAORG');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::valore($this->nameForm . '_CATCOD', substr(trim($Anaorg_rec['ORGCCF']), 0, 4));
        Out::valore($this->nameForm . '_CLACOD', substr(trim($Anaorg_rec['ORGCCF']), 4, 4));
        Out::valore($this->nameForm . '_FASCOD', substr(trim($Anaorg_rec['ORGCCF']), 8, 4));

        Out::hide($this->nameForm . '_CATCOD_butt');
        Out::hide($this->nameForm . '_CLACOD_butt');
        Out::hide($this->nameForm . '_FASCOD_butt');

        Out::attributo($this->nameForm . '_CATCOD', 'readonly', "0", "readonly");
        Out::attributo($this->nameForm . '_CLACOD', 'readonly', "0", "readonly");
        Out::attributo($this->nameForm . '_FASCOD', 'readonly', "0", "readonly");
        Out::attributo($this->nameForm . '_ANAORG[ORGANN]', 'readonly', "0", "readonly");
        Out::attributo($this->nameForm . '_ANAORG[ORGCOD]', 'readonly', "0", "readonly");

        Out::show($this->nameForm . '_Iter');
        Out::show($this->nameForm . '_GestPratica');

        $sql = "SELECT CATDES FROM ANACAT WHERE CATDAT" . $this->PROT_DB->isBlank() . " AND CATCOD='" . substr(trim($Anaorg_rec['ORGCCF']), 0, 4) . "'";
        $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        if (count($Anacat_tab) != 0) {
            Out::valore($this->nameForm . '_CATDES', $Anacat_tab[0]['CATDES']);
        }
        $sql = "SELECT CLADE1 FROM ANACLA WHERE CLADAT" . $this->PROT_DB->isBlank() . " AND CLACCA='" . substr(trim($Anaorg_rec['ORGCCF']), 0, 8) . "'";
        $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        if (count($Anacla_tab) != 0) {
            Out::valore($this->nameForm . '_CLADES', $Anacla_tab[0]['CLADE1']);
        }
        $sql = "SELECT FASDE1 FROM ANAFAS WHERE FASDAT" . $this->PROT_DB->isBlank() . " AND FASCCF='" . substr(trim($Anaorg_rec['ORGCCF']), 0, 12) . "'";
        $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        if (count($Anafas_tab) != 0) {
            Out::valore($this->nameForm . '_FASDES', $Anafas_tab[0]['FASDE1']);
        }
        TableView::disableEvents($this->gridAnaorg);

        Out::show($this->nameForm . '_divAllegati');
        $this->caricaAllegatiFascicolo($Anaorg_rec['ORGKEY']);
    }

    function GetAnacat($_Codice) {
        $sql = "SELECT CATCOD, CATDES FROM ANACAT WHERE CATDAT" . $this->PROT_DB->isBlank() . " AND CATCOD='" . $_Codice . "'";
        $Anacat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anacat_tab;
    }

    function GetAnacla($_Codice) {
        $sql = "SELECT CLACOD, CLADE1 FROM ANACLA WHERE CLADAT" . $this->PROT_DB->isBlank() . " AND CLACCA='" . $_Codice . "'";
        $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anacla_tab;
    }

    function GetAnaorg($_Cod1, $_Cod2) {
        $sql = "SELECT ROWID FROM ANAORG WHERE ORGCOD='$_Cod1' AND ORGCCF='$_Cod2'";
        $Anaorg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anaorg_tab;
    }

    function SetNuovo() {
        $this->AzzeraVariabili();
        Out::attributo($this->nameForm . '_CATCOD', 'readonly', '1');
        Out::attributo($this->nameForm . '_CLACOD', 'readonly', '1');
        Out::attributo($this->nameForm . '_FASCOD', 'readonly', '1');
        Out::attributo($this->nameForm . '_ANAORG[ORGANN]', 'readonly', '1');
        Out::attributo($this->nameForm . '_ANAORG[ORGCOD]', 'readonly', '1');
        Out::show($this->nameForm . '_CATCOD_butt');
        Out::show($this->nameForm . '_CLACOD_butt');
        Out::show($this->nameForm . '_FASCOD_butt');
        $anaent_rec = $this->proLib->GetAnaent('26');
        Out::valore($this->nameForm . '_ANAORG[ORGAOO]', $anaent_rec['ENTDE2']);
        Out::hide($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 200);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Aggiungi');
//        Out::show($this->nameForm.'_Progressivo');
        Out::setFocus('', $this->nameForm . '_CATCOD');
    }

    private function caricaAllegatiFascicolo($orgkey) {
        $this->allegati = array();
        if ($orgkey == '') {
            TableView::clearGrid($this->gridAllegati);
            return;
        }

        $sql = "SELECT
                    ANADOC.ROWID AS ROWID,
                    ANADOC.DOCNAME AS DOCNAME,
                    ANADOC.DOCNOT AS DOCNOT,
                    ANADOC.DOCKEY AS DOCKEY,
                    ANADOC.DOCUTE AS DOCUTE,
                    ANADOC.DOCFIL AS DOCFIL,
                    ANADOC.DOCRAGGRUPPAMENTO AS DOCRAGGRUPPAMENTO
                FROM (
                    SELECT
                        ANAPRO.PRONUM,
                        ANAPRO.PROPAR,
                        ANADOC.ROWID AS ROWID,
                        ANADOC.DOCNAME AS DOCNAME,
                        ANADOC.DOCNOT AS DOCNOT,
                        ANADOC.DOCKEY AS DOCKEY,
                        ANADOC.DOCUTE AS DOCUTE,
                        ANADOC.DOCFIL AS DOCFIL,
                        ANADOC.DOCRAGGRUPPAMENTO AS DOCRAGGRUPPAMENTO
                   FROM
                        ANAPRO
                   LEFT OUTER JOIN ANADOC ANADOC ON ANADOC.DOCKEY LIKE " . $this->PROT_DB->strConcat('ANAPRO.PRONUM', 'ANAPRO.PROPAR', "'%'") . "
                   WHERE
                        ANAPRO.PROFASKEY='$orgkey' 
                ) ANADOC  WHERE ".$this->PROT_DB->strUpper('ANADOC.DOCFIL')."<>'SEGNATURA.XML' ORDER BY ANADOC.DOCRAGGRUPPAMENTO ASC";
        
        $docfascicolo_tab = $this->proLib->getGenericTab($sql);
        foreach ($docfascicolo_tab as $docfascicolo_rec) {
            if (substr($docfascicolo_rec['DOCKEY'], 10, 1) == 'F') {
                $docfascicolo_rec['PROTOCOLLO'] = $docfascicolo_rec['DOCUTE'];
            } else {
                $docfascicolo_rec['PROTOCOLLO'] = (int) substr($docfascicolo_rec['DOCKEY'], 4, 6) . " / " . substr($docfascicolo_rec['DOCKEY'], 0, 4) . " - " . substr($docfascicolo_rec['DOCKEY'], 10, 1);
            }
            $docfascicolo_rec['PRONUM'] = substr($docfascicolo_rec['DOCKEY'], 0, 10);
            $docfascicolo_rec['PROPAR'] = substr($docfascicolo_rec['DOCKEY'], 10, 1);
            $this->allegati[$docfascicolo_rec['ROWID']] = $docfascicolo_rec;
        }
        $this->caricaGriglia($this->gridAllegati, $this->allegati);
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
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

    function ControllaCancella($rowidAnaorg) {
        $anaorg_rec = $this->proLib->GetAnaorg($rowidAnaorg, 'rowid');
        //CONTROLLO SU ANAPRO
        $sql = "SELECT ROWID FROM ANAPRO WHERE PROFASKEY = '" . $anaorg_rec['ORGKEY'] . "'";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false, 0, 1);
        if ($rec)
            return false;

        return true;
    }

}

?>
