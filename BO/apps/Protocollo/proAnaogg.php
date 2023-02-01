<?php

/* * 
 *
 * GESTIONE ANAGRAFICA OGGETTI PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    09.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proAnaogg() {
    $proAnaogg = new proAnaogg();
    $proAnaogg->parseEvent();
    return;
}

class proAnaogg extends itaModel {

    public $PROT_DB;
    public $nameForm = "proAnaogg";
    public $divAppoggio = "proAnaogg_divAppoggio";
    public $divGes = "proAnaogg_divGestione";
    public $divRis = "proAnaogg_divRisultato";
    public $divRic = "proAnaogg_divRicerca";
    public $gridAnaogg = "proAnaogg_gridAnaogg";
    public $gridUffici = "proAnaogg_gridUffici";
    public $returnField = '';
    public $returnModel = '';
    public $proLib;
    public $uffici; //TODO@ NON PIU UTILIZZATO, da rimuovere
    public $DatiAggiuntaOggetto = array();
    public $ArrUffici = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->proLib = new proLib();
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->uffici = App::$utente->getKey($this->nameForm . '_uffici');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->DatiAggiuntaOggetto = App::$utente->getKey($this->nameForm . '_DatiAggiuntaOggetto');
        $this->ArrUffici = App::$utente->getKey($this->nameForm . '_ArrUffici');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_uffici', $this->uffici);
            App::$utente->setKey($this->nameForm . '_DatiAggiuntaOggetto', $this->DatiAggiuntaOggetto);
            App::$utente->setKey($this->nameForm . '_ArrUffici', $this->ArrUffici);
        }
    }

    public function parseEvent() {
        //TODO@ $this->uffici da rimuovere e togliere vecchia gestione.
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->DatiAggiuntaOggetto = array();
                if ($_POST[$this->nameForm . '_returnField'] == '') {
                    $this->OpenRicerca();
                    TableView::disableEvents($this->gridAnaogg);
                } else {
                    $this->SetNuovo();
                    $this->returnField = $_POST['proAnaogg_returnField'];
                    $this->returnModel = $_POST['proAnaogg_returnModel'];
                }
                break;
            case 'AggiuntaNuovoOggetto':
                $this->SetNuovo();
                Out::addClass($this->nameForm . '_ANADOG[DOGCAT]', "required");
                Out::addClass($this->nameForm . '_ANADOG[DOGCLA]', "required");
                Out::valore($this->nameForm . '_ANADOG[DOGDEX]', $this->DatiAggiuntaOggetto['OGGETTO']);
                Out::valore($this->nameForm . '_ANADOG[DOGCAT]', $this->DatiAggiuntaOggetto['PROCAT']);
                Out::valore($this->nameForm . '_ANADOG[DOGCLA]', $this->DatiAggiuntaOggetto['CLACOD']);
                Out::hide($this->nameForm . '_AltraRicerca');
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridUffici:
                        Out::msgQuestion("Uffici e Destinatari", "Selezionare un Ufficio o un Destinatario.", array(
                            'Destinatario' => array('id' => $this->nameForm . '_SelDestinatario', 'model' => $this->nameForm),
                            'Ufficio' => array('id' => $this->nameForm . '_SelUfficio', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaogg:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridUffici:
                        switch ($_POST['colName']) {
                            case 'VISIONE_CHECK':
                                $rowid = $_POST['rowid'];
                                $riga = $this->ArrUffici[$_POST['rowid']];
                                if ($this->ArrUffici[$rowid]['VISIONE'] == 0) {
                                    $this->ArrUffici[$rowid]['VISIONE'] = 1;
                                    Out::setCellValue($this->gridUffici, $rowid, 'VISIONE_CHECK', '<span class="ui-icon ui-icon-check" style="display: inline-block;"></span>');
                                } else {
                                    $this->ArrUffici[$rowid]['VISIONE'] = 0;
                                    Out::setCellValue($this->gridUffici, $rowid, 'VISIONE_CHECK', '&nbsp;');
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaogg:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'class' => 'ita-button-delete', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                    case $this->gridUffici:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaUfficio', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaUfficio', 'class' => 'ita-button-delete', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnaogg, array('sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DOGDEX');
                $ita_grid01->exportXLS('', 'Anaogg.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnaogg:
                        $ordinamento = $_POST['sidx'];
                        if ($_POST['sidx'] == 'CODICI') {
                            $ordinamento = 'DOGCAT';
                        }
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnaogg, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                    case $this->gridUffici:
                        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
                        break;
                }
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnadog', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_TornaElenco':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        Out::hide($this->nameForm . '_TornaElenco');
                        break;
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        try {   // Effettuo la FIND
                            $sql = $this->CreaSql();
                            $ita_grid01 = new TableView($this->gridAnaogg, array(
                                'sqlDB' => $this->PROT_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridAnaogg]['gridParam']['rowNum']);
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '', 0);
                                Out::hide($this->divRic, '', 0);
                                Out::show($this->divRis, '', 0);
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridAnaogg);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_Medcod_butt':
                        proRic::proRicAnamed($this->nameForm);
                        break;
                    case $this->nameForm . '_ANADOG[DOGMED]_butt':
                        proRic::proRicAnamed($this->nameForm);
                        break;
                    case $this->nameForm . '_Uffcod_butt':
                        proRic::proRicAnauff($this->nameForm);
                        break;
                    case $this->nameForm . '_ANADOG[DOGUFF]_butt':
                        $this->uffici = $_POST[$this->nameForm . '_ANADOG']['DOGUFF'];
                        proRic::proRicAnauff($this->nameForm, '', 'returnanauffNew');
                        break;
                    case $this->nameForm . '_Catcod_butt':
//                        $where = " WHERE CATDAT=''";
//                        proRic::proRicCat($this->nameForm, $where);
//                        break;

                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, '', 'returnTitolarioric');
                        break;

                    case $this->nameForm . '_Clacod_butt':
                        $where = array();
                        if ($_POST[$this->nameForm . '_Catcod'] != "") {
                            $codice = str_pad($_POST[$this->nameForm . '_Catcod'], 4, '0', STR_PAD_LEFT);
                            $where['ANACAT'] = " AND CATCOD = '$codice'";
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioric');
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        $where = array();
                        if ($_POST[$this->nameForm . '_Catcod'] != "") {
                            $codice = str_pad($_POST[$this->nameForm . '_Catcod'], 4, '0', STR_PAD_LEFT);
                            $where['ANACAT'] = " AND CATCOD = '$codice'";
                        }
                        if ($_POST[$this->nameForm . '_Clacod'] != "") {
                            $codice = str_pad($_POST[$this->nameForm . '_Clacod'], 4, '0', STR_PAD_LEFT);
                            $where['ANACLA'] = "AND CLACOD = '$codice'";
                        }
                        //proRic::proRicFas($this->nameForm, $where);
                        //break;
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioric');
                        break;
                    case $this->nameForm . '_ANADOG[DOGCAT]_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_ANADOG[DOGCLA]_butt':
                        if ($_POST[$this->nameForm . '_ANADOG']['DOGCAT']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_ANADOG']['DOGCAT'] . "'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_ANADOG[DOGFAS]_butt':
                        if ($_POST[$this->nameForm . '_ANADOG']['DOGCAT']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_ANADOG']['DOGCAT'] . "'";
                            if ($_POST[$this->nameForm . '_ANADOG']['DOGCLA']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_ANADOG']['DOGCAT']
                                        . $_POST[$this->nameForm . '_ANADOG']['DOGCLA'] . "'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->SetNuovo();
                        break;
                    case $this->nameForm . '_Progressivo':
                        $this->GetProgressivo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        // Controlli
                        if (trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']) == '') {
                            $_POST[$this->nameForm . '_ANADOG']['DOGCLA'] = '';
                        }
                        if (trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']) == '') {
                            $_POST[$this->nameForm . '_ANADOG']['DOGFAS'] = '';
                        }
                        $cod_med = $_POST[$this->nameForm . '_ANADOG']['DOGMED'];
                        if (is_numeric($cod_med)) {
                            $cod_med = str_repeat("0", 6 - strlen(trim($cod_med))) . trim($cod_med);
                        } else {
                            $cod_med = trim($cod_med);
                        }
                        if ($cod_med == "000000") {
                            $cod_med = "      ";
                        }
                        $cod_uff = str_repeat(" ", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGUFF']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGUFF']);
                        $cod_cat = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']);
                        if ($cod_cat == "0000") {
                            $cod_cat = "    ";
                        }
                        $cod_cla = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']);
                        if ($cod_cla == "0000") {
                            $cod_cla = "    ";
                        }
                        $cod_fas = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGFAS']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGFAS']);
                        if ($cod_fas == "0000") {
                            $cod_fas = "    ";
                        }
                        $cod_org = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGORG']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGORG']);
                        if ($cod_org == "000000") {
                            $cod_org = "      ";
                        }
                        // TODO@ lavora con $_POST..... si potrebbe sistemare
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = str_repeat(" ", 30);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_med, 0, 6);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_uff, 6, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_cat, 10, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_cla, 14, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_fas, 18, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_org, 22, 6);
                        //    $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = $cod_med . $cod_uff . $cod_cat . $cod_cla . $cod_fas . $cod_org;
                        $codice1 = $_POST[$this->nameForm . '_ANADOG']['DOGCOD'];
                        $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                        $_POST[$this->nameForm . '_ANADOG']['DOGCOD'] = $codice1;
                        $_POST[$this->nameForm . '_ANADOG']['DOGDEX'] = substr($_POST[$this->nameForm . '_ANADOG']['DOGDEX'], 0, 1000);
                        $sql = "SELECT DOGDE1 FROM ANADOG WHERE DOGCOD='$codice1'";
                        try {   // Effettuo la FIND
                            $Anaogg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            if (count($Anaogg_tab) == 0) {
                                $Anaogg_rec = $_POST[$this->nameForm . '_ANADOG'];
                                $Anaogg_rec['DOGUFF'] = $this->ScriviUfficiDestSelezionati();
                                $insert_Info = 'Oggetto: ' . $Anaogg_rec['DOGCOD'] . " " . $_POST[$this->nameForm . '_ANADOG']['DOGDEX'];
                                if ($this->insertRecord($this->PROT_DB, 'ANADOG', $Anaogg_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } else {
                                Out::msgInfo("Codice già  presente", "Codice già  presente. Modificare i valori!");
                                Out::setFocus('', $this->nameForm . '_ANADOG[DOGMED]');
                                break;
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su ANAGRAFICA SOTTOCLASSI.", $e->getMessage());
                            break;
                        }
                        if ($this->DatiAggiuntaOggetto['NUOVOOGGETTO'] == true) {
                            $Dati = array('NUOVOOGGETTO' => array(
                                    'OGGETTO' => $Anaogg_rec['DOGDEX'],
                                    'PROCAT' => $Anaogg_rec['DOGCAT'],
                                    'CLACOD' => $Anaogg_rec['DOGCLA']
                            ));
                            $returnObj = itaModel::getInstance($this->returnModel);
                            $returnObj->setFormData($Dati);
                            $returnObj->setEvent($this->returnEvent);
                            $returnObj->parseEvent();
                            $this->returnToParent();
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        if (trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']) == '') {
                            $_POST[$this->nameForm . '_ANADOG']['DOGCLA'] = '';
                        }
                        if (trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']) == '') {
                            $_POST[$this->nameForm . '_ANADOG']['DOGFAS'] = '';
                        }
                        $cod_med = $_POST[$this->nameForm . '_ANADOG']['DOGMED'];
                        if (is_numeric($cod_med)) {
                            $cod_med = str_repeat("0", 6 - strlen(trim($cod_med))) . trim($cod_med);
                        } else {
                            $cod_med = trim($cod_med);
                        }
//                      $cod_med = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_MEDCOD']))) . trim($_POST[$this->nameForm . '_MEDCOD']);
                        if ($cod_med == "000000") {
                            $cod_med = "      ";
                        }
                        $cod_uff = str_repeat(" ", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGUFF']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGUFF']);
                        $cod_cat = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGCAT']);
                        if ($cod_cat == "0000") {
                            $cod_cat = "    ";
                        }
                        $cod_cla = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGCLA']);
                        if ($cod_cla == "0000") {
                            $cod_cla = "    ";
                        }
                        $cod_fas = str_repeat("0", 4 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGFAS']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGFAS']);
                        if ($cod_fas == "0000") {
                            $cod_fas = "    ";
                        }
                        $cod_org = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . '_ANADOG']['DOGORG']))) . trim($_POST[$this->nameForm . '_ANADOG']['DOGORG']);
                        if ($cod_org == "000000") {
                            $cod_org = "      ";
                        }
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = str_repeat(" ", 30);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_med, 0, 6);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_uff, 6, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_cat, 10, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_cla, 14, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_fas, 18, 4);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDE1'] = substr_replace($_POST[$this->nameForm . '_ANADOG']['DOGDE1'], $cod_org, 22, 6);
                        $_POST[$this->nameForm . '_ANADOG']['DOGDEX'] = substr($_POST[$this->nameForm . '_ANADOG']['DOGDEX'], 0, 1000);
                        $Anaogg_rec = $_POST[$this->nameForm . '_ANADOG'];
                        $Anaogg_rec['DOGUFF'] = $this->ScriviUfficiDestSelezionati();
                        $update_Info = 'Oggetto: ' . $Anaogg_rec['DOGCOD'] . " " . $_POST[$this->nameForm . '_ANADOG']['DOGDEX'];
                        if ($this->updateRecord($this->PROT_DB, 'ANADOG', $Anaogg_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'class' => 'ita-button-delete', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anaogg_rec = $_POST[$this->nameForm . '_ANADOG'];
                        $delete_Info = 'Oggetto: ' . $Anaogg_rec['DOGCOD'] . " " . $_POST[$this->nameForm . '_DOGDEX'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANADOG', $_POST[$this->nameForm . '_ANADOG']['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancellaUfficio':
                        $rowid = $_POST[$this->gridUffici]['gridParam']['selarrrow'];
                        App::log($rowid);
                        App::log($this->ArrUffici);
                        unset($this->ArrUffici[$rowid]);
                        App::log($this->ArrUffici);
                        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
                        break;

                    case $this->nameForm . '_SelDestinatario':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedDestinatario');
                        break;

                    case $this->nameForm . '_SelUfficio':
                        //   proRic::proRicAnauff($this->nameForm, '', 'returnUfficio');
                        itaLib::openForm('proSeleTrasmUffici');
                        /* @var $proSeleTrasmUffici proSeleTrasmUffici */
                        $proSeleTrasmUffici = itaModel::getInstance('proSeleTrasmUffici');
                        $proSeleTrasmUffici->setEvent('openform');
                        $proSeleTrasmUffici->setReturnModel($this->nameForm);
                        $proSeleTrasmUffici->setReturnId('');
                        $proSeleTrasmUffici->parseEvent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Medcod':
                        $codice = $_POST[$this->nameForm . '_Medcod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = trim($codice);
                            }
                            //$codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT MEDNOM FROM ANAMED WHERE MEDCOD='$codice' AND MEDANN=0";
                            $Anamed_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_Medcod', $codice);
                            Out::valore($this->nameForm . '_Mednom', $Anamed_tab[0]['MEDNOM']);
                        } else {
                            Out::valore($this->nameForm . '_Mednom', "");
                        }
                        break;
                    case $this->nameForm . '_ANADOG[DOGMED]':
                        $codice = $_POST[$this->nameForm . '_ANADOG']['DOGMED'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = trim($codice);
                            }
                            if ($codice == "000000") {
                                $codice = "      ";
                            }
//                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT MEDNOM FROM ANAMED WHERE MEDCOD='$codice' AND MEDANN=0";
                            $Anamed_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_ANADOG[DOGMED]', $codice);
                            Out::valore($this->nameForm . '_MEDNOM', $Anamed_tab[0]['MEDNOM']);
                        } else {
                            Out::valore($this->nameForm . '_MEDNOM', "");
                        }
                        break;
                    case $this->nameForm . '_Uffcod':
                        $codice = $_POST[$this->nameForm . '_Uffcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $sql = "SELECT UFFDES FROM ANAUFF WHERE UFFCOD='$codice'";
                            $Anauff_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            Out::valore($this->nameForm . '_Uffcod', $codice);
                            Out::valore($this->nameForm . '_Uffdes', $Anauff_tab[0]['UFFDES']);
                        } else {
                            Out::valore($this->nameForm . '_Uffdes', "");
                        }
                        break;

                    case $this->nameForm . '_Catcod':
                        $codice = $_POST[$this->nameForm . '_Catcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $Anacat_rec = $this->proLib->GetAnacat('', $codice);
                            Out::valore($this->nameForm . '_Catcod', $codice);
                            Out::valore($this->nameForm . '_Catdes', $Anacat_rec['CATDES']);
                        } else {
                            Out::valore($this->nameForm . '_Catdes', "");
                        }
                        break;

                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $Anacla_rec = $this->proLib->GetAnacla('', $codice1 . $codice2);
                            Out::valore($this->nameForm . '_Clacod', $codice2);
                            Out::valore($this->nameForm . '_Clades', $Anacla_rec['CLADE1'] . $Anacla_rec['CLADE2']);
                        } else {
                            Out::valore($this->nameForm . '_Clades', "");
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
                            $Anafas_rec = $this->proLib->GetAnafas('', $codice1 . $codice2 . $codice3);
                            Out::valore($this->nameForm . '_Fascod', $codice3);
                            Out::valore($this->nameForm . '_Fasdes', $Anafas_rec['FASDES']);
                        } else {
                            Out::valore($this->nameForm . '_Fasdes', "");
                        }
                        break;

                    case $this->nameForm . '_Dogcod':
                        $codice = $_POST[$this->nameForm . '_Dogcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_Dogcod', $codice);
                            $Anaogg_rec = $this->proLib->GetAnadog($codice);
                            if ($Anaogg_rec) {
                                $this->Dettaglio($Anaogg_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm . '_ANADOG[DOGCOD]':
                        $codice = $_POST[$this->nameForm . '_ANADOG']['DOGCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANADOG[DOGCOD]', $codice);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANADOG[DOGCAT]':
                        $codice = $_POST[$this->nameForm . '_ANADOG']['DOGCAT'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacatNew($codice);
                        break;
                    case $this->nameForm . '_ANADOG[DOGCLA]':
                        $codice = $_POST[$this->nameForm . '_ANADOG']['DOGCLA'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANADOG']['DOGCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnaclaNew($codice1 . $codice2);
                        } else {
                            $codice = $_POST[$this->nameForm . '_ANADOG']['DOGCAT'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacatNew($codice);
                        }
                        break;
                    case $this->nameForm . '_ANADOG[DOGFAS]':
                        $codice = $_POST[$this->nameForm . '_ANADOG']['DOGFAS'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANADOG']['DOGCAT'];
                            $codice2 = $_POST[$this->nameForm . '_ANADOG']['DOGCLA'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafasNew($codice1 . $codice2 . $codice3, 'fasccf');
                        } else {
                            $codice = $_POST[$this->nameForm . '_ANADOG']['DOGCLA'];
                            $codice1 = $_POST[$this->nameForm . '_ANADOG']['DOGCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnaclaNew($codice1 . $codice2);
                        }
                        break;
                }
                break;
            case 'returnanamed':
                $sql = "SELECT MEDCOD, MEDNOM FROM ANAMED WHERE ROWID='" . $_POST['retKey'] . "' AND MEDANN=0";
                try {   // Effettuo la FIND
                    $Anamed_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anamed_tab) != 0) {
                        Out::valore($this->nameForm . '_Medcod', $Anamed_tab[0]['MEDCOD']);
                        Out::valore($this->nameForm . '_Mednom', $Anamed_tab[0]['MEDNOM']);
                        Out::valore($this->nameForm . '_ANADOG[DOGMED]', $Anamed_tab[0]['MEDCOD']);
                        Out::valore($this->nameForm . '_MEDNOM', $Anamed_tab[0]['MEDNOM']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                }
                break;
            case 'returnanauff':
                $sql = "SELECT UFFCOD, UFFDES FROM ANAUFF WHERE ROWID='" . $_POST['retKey'] . "'";
                try {   // Effettuo la FIND
                    $Anauff_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                    if (count($Anauff_tab) != 0) {
                        Out::valore($this->nameForm . '_Uffcod', $Anauff_tab[0]['UFFCOD']);
                        Out::valore($this->nameForm . '_Uffdes', $Anauff_tab[0]['UFFDES']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                }
                break;
            case 'returnanauffNew':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                if ($this->uffici != '') {
                    $this->uffici = $this->uffici . "|";
                }
                $this->uffici = $this->uffici . $anauff_rec['UFFCOD'];
                Out::valore($this->nameForm . '_ANADOG[DOGUFF]', $this->uffici);
                break;
            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolario($rowid, $tipoArc);
                break;
            case 'returnTitolarioric':
                Out::valore($this->nameForm . '_Catcod', $_POST['rowData']['CATCOD']);
                Out::valore($this->nameForm . '_Clacod', $_POST['rowData']['CLACOD']);
                Out::valore($this->nameForm . '_Fascod', $_POST['rowData']['FASCOD']);
                Out::valore($this->nameForm . '_Catdes', $_POST['rowData']['CATDES']);
                Out::valore($this->nameForm . '_Clades', $_POST['rowData']['CLADES']);
                Out::valore($this->nameForm . '_Fasdes', $_POST['rowData']['FASDES']);

//                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
//                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
//                $this->decodTitolario($rowid, $tipoArc);
                break;
            case 'returncat':
                $this->DecodAnacat('', $_POST['retKey'], 'rowid');
                break;
            case 'returncla':
                $this->DecodAnacla('', $_POST['retKey'], 'rowid');
                break;
            case 'returnfas':
                $this->DecodAnafas('', $_POST['retKey'], 'rowid');
                break;

            case 'returnUfficio':
                $this->AggiungiUfficio($_POST['retKey'], 'rowid');
                break;
            case 'returnanamedDestinatario':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid');
                //Controllo se ha solo un ufficio...
                $sql = "SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='" . $anamed_rec['MEDCOD'] . "' AND ANAUFF.UFFANN=0";
                $uffdes_tab = $this->proLib->getGenericTab($sql);
                if (count($uffdes_tab) == 1) {
                    $uffdes_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD'], 'codice');
                    $this->AggiungiDestinatario($uffdes_rec, $anamed_rec);
                } else {
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', $anamed_rec['MEDCOD'], 'Ufficio');
                }
                break;

            case 'returnUfficiPerDestinatarioUfficio':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retid'], 'codice');
                $uffdes_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                $this->AggiungiDestinatario($uffdes_rec, $anamed_rec);
                break;

            case 'returnFromSeleTrasmUfficio':
                $TipoSelezione = $_POST['tipoSelezione'];
                $retUffici = $_POST['retUffici'];
                foreach ($retUffici as $anauff_rec) {
                    if ($anauff_rec) {
                        $this->AggiungiUfficio($anauff_rec['ROWID'], 'rowid', $TipoSelezione);
                    }
                }

                break;
        }
    }

    public function close() {
// TODO@ sembra non essere utilizzata, da confermare...
//        if ($this->returnModel != '') {
//            $rowId = $_POST['rowid'];
//            $_POST = array();
//            $_POST['event'] = 'returntoform';
//            $_POST['model'] = $this->returnModel;
//            $_POST['retField'] = $this->returnField;
//            $_POST['retKey'] = $rowId;
//            $phpURL = App::getConf('modelBackEnd.php');
//            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
//            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
//            $model = $this->returnModel;
//            $model();
//        }
        App::$utente->removeKey($this->nameForm . '_returnField');
        App::$utente->removeKey($this->nameForm . '_uffici');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_DatiAggiuntaOggetto');
        App::$utente->removeKey($this->nameForm . '_ArrUffici');
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
        TableView::disableEvents($this->gridAnaogg);
        TableView::clearGrid($this->gridAnaogg);
        TableView::clearGrid($this->gridUffici);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Medcod');
    }

    function AzzeraVariabili() {
        $this->uffici = '';
        $this->ArrUffici = array();
        Out::clearFields($this->nameForm, $this->divAppoggio);
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
    }

    public function setDatiAggiuntaOggetto($Dati) {
        $this->DatiAggiuntaOggetto = $Dati;
    }

    public function getDatiAggiuntaOggetto() {
        return $this->DatiAggiuntaOggetto;
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $where = "WHERE DOGCOD=DOGCOD";
        if ($_POST[$this->nameForm . '_Dogcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Dogcod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND DOGCOD='$codice'";
        }
        if ($_POST[$this->nameForm . '_Dogdes'] != "") {
            $valore = addslashes(trim($_POST[$this->nameForm . '_Dogdes']));
            $where = $where . " AND " . $this->PROT_DB->strUpper('DOGDEX') . " LIKE '%" . addslashes(strtoupper($valore)) . "%'";
        }
        if ($_POST[$this->nameForm . '_Medcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Medcod'];
            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND " . $this->PROT_DB->strUpper('DOGMED') . " = '" . addslashes(strtoupper($codice)) . "'";
        }
        if ($_POST[$this->nameForm . '_Uffcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Uffcod'];
            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND DOGUFF = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_Catcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Catcod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND DOGCAT = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_Clacod'] != "") {
            $codice = $_POST[$this->nameForm . '_Clacod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND DOGCLA = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_Fascod'] != "") {
            $codice = $_POST[$this->nameForm . '_Fascod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where = $where . " AND DOGFAS = '" . $codice . "'";
        }
        $sql = "SELECT * FROM ANADOG $where";
        return $sql;
    }

    public function Dettaglio($codice, $tipo = 'rowid') {
        $Anadog_rec = $this->proLib->GetAnadog($codice, $tipo);
        if ($Anadog_rec) {
            $this->DecodAnamed($Anadog_rec['DOGMED'], 'codice', 'si');
            $this->DecodAnauff($Anadog_rec['DOGUFF']);
            $this->DecodAnacat('', $Anadog_rec['DOGCAT']);
            if ($Anadog_rec['DOGCLA']) {
                $this->DecodAnacla('', $Anadog_rec['DOGCAT'] . $Anadog_rec['DOGCLA']);
            }
            if ($Anadog_rec['DOGFAS']) {
                $this->DecodAnafas('', $Anadog_rec['DOGCAT'] . $Anadog_rec['DOGCLA'] . $Anadog_rec['DOGFAS']);
            }
        }
        $open_Info = 'Oggetto: ' . $Anadog_rec['DOGCOD'] . " " . $Anadog_rec['DOGDEX'];
        $this->openRecord($this->PROT_DB, 'ANADOG', $open_Info);
        $this->Nascondi();
        Out::valori($Anadog_rec, $this->nameForm . '_ANADOG');

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_TornaElenco');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
//        Out::attributo($this->nameForm . '_ANADOG[DOGCOD]', 'readonly', '0');
        Out::addClass($this->nameForm . '_ANADOG[DOGCOD]', "ita-readonly");
        Out::setFocus('', $this->nameForm . '_ANADOG[DOGDEX]');
        TableView::disableEvents($this->gridAnaogg);

        $this->ArrUffici = array();
        $this->CaricaUffici($Anadog_rec);
        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
    }

    private function CaricaUffici($Anadog_rec) {
        $Uffici = explode('|', $Anadog_rec['DOGUFF']);
        $key = 0;
        foreach ($Uffici as $Ufficio) {
            if ($Ufficio) {
                $ArrUfficio = array();
                $key++;
                $ElementiDest = explode('@', $Ufficio);
                $ArrUfficio['CODUFF'] = $ElementiDest[0];
                $Anauff_rec = $this->proLib->GetAnauff($ElementiDest[0], 'codice');
                $ArrUfficio['UFFICIO'] = $Anauff_rec['UFFDES'];
                if ($ElementiDest[1]) {
                    $Anamed_rec = $this->proLib->GetAnamed($ElementiDest[1], 'codice');
                    $ArrUfficio['CODDEST'] = $ElementiDest[1];
                    $ArrUfficio['DESTINATARIO'] = $ElementiDest[1] . ' - ' . $Anamed_rec['MEDNOM'];
                }
                $ArrUfficio['VISIONE'] = 0;
                if ($ElementiDest[2]) {
                    $ArrUfficio['VISIONE'] = $ElementiDest[2];
                }
                if ($ArrUfficio['VISIONE'] == 1) {
                    $ArrUfficio['VISIONE_CHECK'] = '<span class="ui-icon ui-icon-check" style="display: inline-block;"></span>';
                } else {
                    $ArrUfficio['VISIONE_CHECK'] = "&nbsp;";
                }
                $ArrUfficio['TIPOTRASM'] = $ElementiDest[3];
                if ($ArrUfficio['TIPOTRASM'] && $ArrUfficio['TIPOTRASM'] == 'Ufficio') {
                    $ArrUfficio['DESTINATARIO'] = 'TRASMISSIONE AD INTERO UFFICIO';
                }


                $this->ArrUffici[$key] = $ArrUfficio;
            }
        }
    }

    public function CaricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows('10000');
        if ($_POST['sidx']) {
            $ita_grid01->setSortIndex($_POST['sidx']);
        } else {
            $ita_grid01->setSortIndex('CODUFF');
        }
        if ($_POST['sord']) {
            $ita_grid01->setSortOrder($_POST['sord']);
        } else {
            $ita_grid01->setSortOrder('asc');
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
    }

    function SetNuovo() {
        $this->AzzeraVariabili();
        TableView::clearGrid($this->gridUffici);
//        Out::attributo($this->nameForm . '_ANADOG[DOGCOD]', 'readonly', '1');
        Out::delClass($this->nameForm . '_ANADOG[DOGCOD]', "ita-readonly");
        Out::hide($this->divRic, '', 200);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 200);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Progressivo');
        Out::setFocus('', $this->nameForm . '_ANADOG[DOGCOD]');
    }

    function DecodAnauff($Codice, $Tipo = 'codice') {
        $Anauff_rec = $this->proLib->GetAnauff($Codice, $Tipo);
        Out::valore($this->nameForm . "_ANADOG[DOGUFF]", $Anauff_rec['UFFCOD']);
        Out::valore($this->nameForm . "_UFFDES", $Anauff_rec['UFFDES']);
        return $Anauff_rec;
    }

    function DecodAnamed($Codice, $_tipoRic = 'codice', $_tutti = 'si') {
        $Anamed_rec = $this->proLib->GetAnamed($Codice, $_tipoRic, $_tutti);
        Out::valore($this->nameForm . '_ANADOG[DOGMED]', $Anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_MEDNOM', $Anamed_rec['MEDNOM']);
        return $Anamed_rec;
    }

    function DecodAnacat($data, $codice, $tipo = 'codice') {
        $Anacat_rec = $this->proLib->GetAnacat($data, $codice, $tipo);
        Out::valore($this->nameForm . '_Catcod', $Anacat_rec['CATCOD']);
        Out::valore($this->nameForm . '_Catdes', $Anacat_rec['CATDES']);
        Out::valore($this->nameForm . '_ANADOG[DOGCAT]', $Anacat_rec['CATCOD']);
        Out::valore($this->nameForm . '_TitolarioDecod', $Anacat_rec['CATDES']);
        return $Anacat_rec;
    }

    function DecodAnacla($data, $codice, $tipo = 'codice') {

        $Anacla_rec = $this->proLib->GetAnacla($data, $codice, $tipo);
        if ($Anacla_rec) {
            $this->DecodAnacat($Anacla_rec['CLADAT'], $Anacla_rec['CLACAT']);
        }
        Out::valore($this->nameForm . '_Clacod', $Anacla_rec['CLACOD']);
        Out::valore($this->nameForm . '_Clades', $Anacla_rec['CLADE1']);
        Out::valore($this->nameForm . '_ANADOG[DOGCLA]', $Anacla_rec['CLACOD']);
        Out::valore($this->nameForm . '_TitolarioDecod', $Anacla_rec['CLADE1']);
        return $Anacla_rec;
    }

    function DecodAnafas($data, $codice, $tipo = 'codice') {
        $Anafas_rec = $this->proLib->GetAnafas($data, $codice, $tipo);
        if ($Anafas_rec) {
            $this->DecodAnacla($Anafas_rec['FASDAT'], $Anafas_rec['FASCCA']);
        }
        Out::valore($this->nameForm . '_Fascod', $Anafas_rec['FASCOD']);
        Out::valore($this->nameForm . '_Fasdes', $Anafas_rec['FASDE1']);
        Out::valore($this->nameForm . '_ANADOG[DOGFAS]', $Anafas_rec['FASCOD']);
        Out::valore($this->nameForm . '_TitolarioDecod', $Anafas_rec['FASDES']);
        return $Anafas_rec;
    }

    private function DecodAnacatNew($codice, $tipo = 'codice') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_ANADOG[DOGCAT]', '');
            Out::valore($this->nameForm . '_ANADOG[DOGCLA]', '');
            Out::valore($this->nameForm . '_ANADOG[DOGFAS]', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anacat_rec;
    }

    private function DecodAnaclaNew($codice, $tipo = 'codice') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_ANADOG[DOGCLA]', '');
            Out::valore($this->nameForm . '_ANADOG[DOGFAS]', '');
        }
        return $anacla_rec;
    }

    private function DecodAnafasNew($codice, $tipo = 'codice') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_ANADOG[DOGFAS]', '');
        }
        return $anafas_rec;
    }

    private function decodTitolario($rowid, $tipoArc) {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_ANADOG[DOGCAT]', $cat);
        Out::valore($this->nameForm . '_ANADOG[DOGCLA]', $cla);
        Out::valore($this->nameForm . '_ANADOG[DOGFAS]', $fas);
        Out::valore($this->nameForm . '_TitolarioDecod', $des);
    }

    private function GetProgressivo() {
        for ($i = 1; $i <= 9999; $i++) {
            $codice = str_repeat("0", 4 - strlen(trim($i))) . trim($i);
            $anadog_rec = $this->proLib->GetAnadog($codice);
            if (!$anadog_rec) {
                Out::valore($this->nameForm . '_ANADOG[DOGCOD]', $codice);
                Out::setFocus('', $this->nameForm . '_ANADOG[DOGDEX]');
                break;
            }
        }
    }

    private function AggiungiUfficio($Codice, $Tipo, $tipoTrasm = '') {
        $Anauff_rec = $this->proLib->GetAnauff($Codice, $Tipo);
        foreach ($this->ArrUffici as $UfficioDest) {
            if ($UfficioDest['CODUFF'] == $Anauff_rec['UFFCOD']) {
                if ($UfficioDest['CODDEST'] == '') {
                    Out::msgInfo("Attenzione", "Ufficio: <b>" . $Anauff_rec['UFFDES'] . "</b> già presente.");
                    return;
                }
                if ($UfficioDest['CODDEST'] != '') {
                    Out::msgInfo("Attenzione", "É già presente un Destinatario per l'ufficio: <b>" . $Anauff_rec['UFFDES'] . "</b>. Se si vuole caricare l'intero ufficio, occorre prima rimuovere i singoli destinatari dell'ufficio.");
                    return;
                }
            }
        }
        $Ufficio['CODUFF'] = $Anauff_rec['UFFCOD'];
        $Ufficio['UFFICIO'] = $Anauff_rec['UFFDES'];
        $Ufficio['CODDEST'] = '';
        $Ufficio['TIPOTRASM'] = $tipoTrasm;
        if ($tipoTrasm == 'Persona') {
            $Ufficio['DESTINATARIO'] = '';
        } else {
            $Ufficio['DESTINATARIO'] = 'TRASMISSIONE AD INTERO UFFICIO';
        }

        if (!$this->ArrUffici) {
            $this->ArrUffici[1] = $Ufficio;
        } else {
            $this->ArrUffici[] = $Ufficio;
        }
        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
    }

    private function AggiungiDestinatario($Anauff_rec, $Anamed_rec) {
        foreach ($this->ArrUffici as $UfficioDest) {
            if ($UfficioDest['CODUFF'] == $Anauff_rec['UFFCOD']) {
                if ($UfficioDest['CODDEST'] == $Anamed_rec['MEDCOD']) {
                    Out::msgInfo("Attenzione", "Destinatario: <b>" . $Anamed_rec['MEDNOM'] . "</b> già presente per l'ufficio: <b>" . $Anauff_rec['UFFDES'] . "</b>.");
                    return;
                }
                if ($UfficioDest['CODDEST'] == '') {
                    Out::msgInfo("Attenzione", "É già stato caricato l'intero ufficio: <b>" . $Anauff_rec['UFFDES'] . "</b>.<br> Se si vuole caricare singoli destinatari, occorre prima rimuovere l'assegnazione dell'intero ufficio.");
                    return;
                }
            }
        }
        $Destinatario['CODUFF'] = $Anauff_rec['UFFCOD'];
        $Destinatario['UFFICIO'] = $Anauff_rec['UFFDES'];
        $Destinatario ['CODDEST'] = $Anamed_rec['MEDCOD'];
        $Destinatario['DESTINATARIO'] = $Anamed_rec['MEDCOD'] . ' - ' . $Anamed_rec['MEDNOM'];
        if (!$this->ArrUffici) {
            $this->ArrUffici[1] = $Destinatario;
        } else {
            $this->ArrUffici[] = $Destinatario;
        }
        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
    }

    private function ScriviUfficiDestSelezionati() {
        $doguff = '';
        App::log($this->ArrUffici);
        foreach ($this->ArrUffici as $UfficioDest) {
            $doguff .= $UfficioDest['CODUFF'];
            //if ($UfficioDest['CODDEST']) {
            $doguff .= "@" . $UfficioDest['CODDEST'];
            //}
            $doguff .= "@" . $UfficioDest['VISIONE'];
            if ($UfficioDest['TIPOTRASM']) {
                $doguff .= "@" . $UfficioDest['TIPOTRASM'];
            }
            $doguff .= "|";
        }
        $doguff = substr($doguff, 0, -1);
        return $doguff;
    }

}

?>
