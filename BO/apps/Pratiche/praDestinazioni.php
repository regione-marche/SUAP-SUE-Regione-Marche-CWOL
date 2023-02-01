<?php

/**
 *
 * ANAGRAFICA DESTINAZIONI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    24.06.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praDestinazioni() {
    $praDestinazioni = new praDestinazioni();
    $praDestinazioni->parseEvent();
    return;
}

class praDestinazioni extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $proLib;
    public $utiEnte;
    public $nameForm = "praDestinazioni";
    public $divGes = "praDestinazioni_divGestione";
    public $divRis = "praDestinazioni_divRisultato";
    public $divRic = "praDestinazioni_divRicerca";
    public $gridDestinazioni = "praDestinazioni_gridDestinazioni";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                if (!$this->SincronizzaDestinazioni()) {
                    Out::msgStop("ATTENZIONE!", "Errore sincronizzazione destinatari SUE");
                    break;
                }
                //$this->CaricaDestinazioni();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'viewRowInline':
            case 'editRowInline':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDestinazioni:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
//            case 'addGridRow':
//                $PROT_DB = $this->proLib->getPROTDB();
//                proRic::proRicAnamed($this->nameForm, "WHERE MEDUFF " . $PROT_DB->isBlank());
//                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDestinazioni:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDestinazioni, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('DDONOM');
                $ita_grid01->exportXLS('', 'destinazioni.xls');
                break;
            case 'onClickTablePager':
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->CreaSql()));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praDestinazioni', $parameters);
                break;
            case "onClick":
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridDestinazioni, array(
                                'sqlDB' => $this->PRAM_DB,
                                'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('DDONOM');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '');
                                Out::hide($this->divRic, '');
                                Out::show($this->divRis, '');
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridDestinazioni);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_ANADDO[DDOCDE]');
                        Out::valore($this->nameForm . '_ANADDO[ROWID]', 0);

                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANADDO']['DDOCOD'];
                        $Anaddo_ric = $this->praLib->GetAnaddo($codice);
                        if (!$Anaddo_ric) {
                            $Anaddo_rec = $_POST[$this->nameForm . '_ANADDO'];
                            $insert_Info = 'Oggetto: Inserisco Destinazione ' . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM'];
                            if (!$this->insertRecord($this->PRAM_DB, 'ANADDO', $Anaddo_rec, $insert_Info)) {
                                Out::msgStop("Attenzione!!!", "Errore inserimento destinazione " . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM']);
                                break;
                            }
                            $this->OpenRicerca();
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANADDO[DDOCOD]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Anaddo_rec = $_POST[$this->nameForm . '_ANADDO'];
                        $update_Info = 'Oggetto: Aggiorno Destinazione ' . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM'];
                        if (!$this->updateRecord($this->PRAM_DB, 'ANADDO', $Anaddo_rec, $update_Info)) {
                            Out::msgStop("Attenzione!!!", "Errore aggiornamento destinazione " . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM']);
                            break;
                        }
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anaddo_rec = $_POST[$this->nameForm . '_ANADDO'];
                        $delete_Info = 'Oggetto: Cancello Destinazione' . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'ANADDO', $Anaddo_rec['ROWID'], $delete_Info)) {
                            Out::msgStop("Errore in Cancellazione Destinazione " . $Anaddo_rec['DDOCOD'] . " - " . $Anaddo_rec['DDONOM']);
                        }
                        $this->OpenRicerca();
                        break;
//                    case $this->nameForm . "_ConfermaCancella":
//                        $arrayRowid = explode(",", $_POST[$this->gridDestinazioni]['gridParam']['selarrrow']);
//                        foreach ($arrayRowid as $rowid) {
//                            $err = false;
//                            $anaddo_rec = $this->praLib->GetAnaddo($rowid, "rowid");
//                            $delete_Info = "Oggetto: Cancellazione destinatario SUE " . $anaddo_rec['MEDCOD'] . " - " . $anaddo_rec['MEDNOM'];
//                            if (!$this->deleteRecord($this->PRAM_DB, 'ANADDO', $anaddo_rec['ROWID'], $delete_Info)) {
//                                $err = true;
//                                break;
//                            }
//                        }
//                        if ($err) {
//                            Out::msgStop("Attenzione", "Errore in cancellazione destinatario SUE " . $anaddo_rec['MEDCOD'] . " - " . $anaddo_rec['MEDNOM']);
//                            break;
//                        }
//                        $this->CaricaDestinazioni();
//                        break;
//                    case $this->nameForm . "_ConfermaDestinazione":
//                        $anaddo_rec = $_POST[$this->nameForm . "_ANADDO"];
//                        if ($anaddo_rec['ROWID']) {
//                            $update_Info = "Oggetto: Aggiorno destinatario " . $anaddo_rec['DDOCOD'] . " - " . $anaddo_rec['DDONOM'];
//                            if (!$this->updateRecord($this->PRAM_DB, 'ANADDO', $anaddo_rec, $update_Info)) {
//                                Out::msgStop("ATTENZIONE!", "Errore Inserimento destinatario SUE " . $anaddo_rec['DDOCOD'] . " - " . $anaddo_rec['DDONOM']);
//                                break;
//                            }
//                        } else {
//                            $insert_Info = "Oggetto: Inserisco destinatario " . $anaddo_rec['DDOCOD'] . " - " . $anaddo_rec['DDONOM'];
//                            if (!$this->insertRecord($this->PRAM_DB, 'ANADDO', $anaddo_rec, $insert_Info)) {
//                                Out::msgStop("ATTENZIONE!", "Errore Inserimento destinatario SUE " . $anaddo_rec['DDOCOD'] . " - " . $anaddo_rec['DDONOM']);
//                                break;
//                            }
//                        }
//                        $this->CaricaDestinazioni();
//                        break;
                    case $this->nameForm . '_ANADDO[DDOCOD]_butt':
                        $PROT_DB = $this->proLib->getPROTDB();
                        proRic::proRicAnamed($this->nameForm, "WHERE MEDUFF " . $PROT_DB->isBlank());
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case "returnanamed":
                $anamed_rec = $this->proLib->GetAnamed($_POST["retKey"], "rowid");
                $anaddo_rec_ctr = $this->praLib->GetAnaddo($anamed_rec['MEDCOD']);
                if ($anaddo_rec_ctr) {
                    Out::msgInfo("Inserimento destinatario", "Il destinatario " . $anamed_rec['MEDNOM'] . " risulta essere presente nell'elenco delle destinazioni");
                    break;
                }
                $anaddo_rec = array();
                $anaddo_rec['DDOCOD'] = $anamed_rec['MEDCOD'];
                $anaddo_rec['DDONOM'] = $anamed_rec['MEDNOM'];
                $anaddo_rec['DDOIND'] = $anamed_rec['MEDIND'];
                $anaddo_rec['DDOCAP'] = $anamed_rec['MEDCAP'];
                $anaddo_rec['DDOCIT'] = $anamed_rec['MEDCIT'];
                $anaddo_rec['DDOPRO'] = $anamed_rec['MEDPRO'];
                $anaddo_rec['DDOEMA'] = $anamed_rec['MEDEMA'];
                $anaddo_rec['DDOFIS'] = $anamed_rec['MEDFIS'];
                $anaddo_rec['DDOTEL'] = $anamed_rec['MEDTEL'];
                $anaddo_rec['DDONOTE'] = $anamed_rec['MEDNOTE'];
                Out::valori($anaddo_rec, $this->nameForm . '_ANADDO');
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql = "SELECT * FROM ANADDO WHERE 1 ";
        if ($_POST[$this->nameForm . '_Ddocod'] != "") {
            $sql .= " AND DDOCOD = '" . $_POST[$this->nameForm . '_Ddocod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Ddonom'] != "") {
            $sql .= " AND ".$this->PRAM_DB->strLower('DDONOM')." LIKE '%" . addslashes(strtolower($_POST[$this->nameForm . '_Ddonom'])) . "%'";
        }

        return $sql;
    }

    function SincronizzaDestinazioni() {
        $anaddo_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->CreaSql(), true);
        if ($anaddo_tab) {
            foreach ($anaddo_tab as $anaddo_rec) {
                $anamed_rec = $this->proLib->GetAnamed($anaddo_rec['DDOCOD']);
                $anaddo_rec_upd = array();
                $anaddo_rec_upd['ROWID'] = $anaddo_rec['ROWID'];
                $anaddo_rec_upd['DDONOM'] = $anamed_rec['MEDNOM'];
                $anaddo_rec_upd['DDOIND'] = $anamed_rec['MEDIND'];
                $anaddo_rec_upd['DDOCAP'] = $anamed_rec['MEDCAP'];
                $anaddo_rec_upd['DDOCIT'] = $anamed_rec['MEDCIT'];
                $anaddo_rec_upd['DDOPRO'] = $anamed_rec['MEDPRO'];
                $anaddo_rec_upd['DDOEMA'] = $anamed_rec['MEDEMA'];
                $anaddo_rec_upd['DDOFIS'] = $anamed_rec['MEDFIS'];
                $anaddo_rec_upd['DDOTEL'] = $anamed_rec['MEDTEL'];
                $anaddo_rec_upd['DDONOTE'] = $anamed_rec['MEDNOTE'];
                $update_Info = "Oggetto: Inserisco destinatario SUE " . $anamed_rec['MEDCOD'] . " - " . $anamed_rec['MEDNOM'];
                if (!$this->updateRecord($this->PRAM_DB, 'ANADDO', $anaddo_rec_upd, $update_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

//    function CaricaDestinazioni() {
//        $sql = $this->CreaSql();
//        try {   // Effettuo la FIND
//            $ita_grid01 = new TableView($this->gridDestinazioni, array(
//                'sqlDB' => $this->PRAM_DB,
//                'sqlQuery' => $sql));
//            $ita_grid01->setPageNum(1);
//            $ita_grid01->setPageRows(1000);
//            $ita_grid01->setSortIndex('DDONOM');
//            if (!$ita_grid01->getDataPage('json')) {
//                TableView::clearGrid($this->gridDestinazioni);
//                TableView::disableEvents($this->gridDestinazioni);
//            } else {
//                Out::show($this->divRis);
//                TableView::enableEvents($this->gridDestinazioni);
//            }
//        } catch (Exception $e) {
//            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
//        }
//    }

    public function Dettaglio($Indice) {
        $Anaddo_rec = $this->praLib->GetAnaddo($Indice, 'rowid');
        $open_Info = 'Oggetto: ' . $Anaddo_rec['DDOCOD'] . " " . $Anaddo_rec['DDONOM'];
        $this->openRecord($this->PRAM_DB, 'ANADDO', $open_Info);
        $this->Nascondi();
        Out::valori($Anaddo_rec, $this->nameForm . '_ANADDO');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::show($this->divGes, '');
        Out::setFocus('', $this->nameForm . '_ANADDO[DDOCOD]');
        Out::attributo($this->nameForm . '_ANADDO[DDOCOD]', "readonly", '0');

        TableView::disableEvents($this->gridDestinazioni);

//        Out::valore($this->nameForm . '_ANADDO[ROWID]', "");
//        if (!$Anaddo_rec) {
//            $Anaddo_rec = $this->praLib->GetAnaddo($_POST['rowid'], 'rowid');
//        }
//        Out::msgInput(
//                "Dettaglio Destinazione", array(
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Codice Soggetto'),
//                'id' => $this->nameForm . '_ANADDO[DDOCOD]',
//                'name' => $this->nameForm . '_ANADDO[DDOCOD]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '8',
//                'maxlength' => '6'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Codice Destinazione'),
//                'id' => $this->nameForm . '_ANADDO[DDOCDE]',
//                'name' => $this->nameForm . '_ANADDO[DDOCDE]',
//                'type' => 'text',
//                'class' => 'required',
//                'size' => '8',
//                'maxlength' => '6'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Nominativo'),
//                'id' => $this->nameForm . '_ANADDO[DDONOM]',
//                'name' => $this->nameForm . '_ANADDO[DDONOM]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '40',
//                'maxlength' => '100'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Indirizzo'),
//                'id' => $this->nameForm . '_ANADDO[DDOIND]',
//                'name' => $this->nameForm . '_ANADDO[DDOIND]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '40',
//                'maxlength' => '100'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Cap'),
//                'id' => $this->nameForm . '_ANADDO[DDOCAP]',
//                'name' => $this->nameForm . '_ANADDO[DDOCAP]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '6',
//                'maxlength' => '5'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Comune'),
//                'id' => $this->nameForm . '_ANADDO[DDOCIT]',
//                'name' => $this->nameForm . '_ANADDO[DDOCIT]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '40',
//                'maxlength' => '40'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Provincia'),
//                'id' => $this->nameForm . '_ANADDO[DDOPRO]',
//                'name' => $this->nameForm . '_ANADDO[DDOPRO]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '3',
//                'maxlength' => '2'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'E-mail'),
//                'id' => $this->nameForm . '_ANADDO[DDOEMA]',
//                'name' => $this->nameForm . '_ANADDO[DDOEMA]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '40',
//                'maxlength' => '100'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Fiscale'),
//                'id' => $this->nameForm . '_ANADDO[DDOFIS]',
//                'name' => $this->nameForm . '_ANADDO[DDOFIS]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '22',
//                'maxlength' => '16'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Telefono'),
//                'id' => $this->nameForm . '_ANADDO[DDOTEL]',
//                'name' => $this->nameForm . '_ANADDO[DDOTEL]',
//                'type' => 'text',
//                'class' => 'ita-readonly',
//                'size' => '30',
//                'maxlength' => '16'),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Note'),
//                'id' => $this->nameForm . '_ANADDO[DDONOTE]',
//                'name' => $this->nameForm . '_ANADDO[DDONOTE]',
//                'type' => 'textarea',
//                'class' => 'ita-readonly',
//                'cols' => '50',
//                'rows' => '5',
//                '@textNode@' => $Anaddo_rec['DDONOTE']),
//            array(
//                'label' => array('style' => "width:80px;", 'value' => 'Telefono'),
//                'id' => $this->nameForm . '_ANADDO[ROWID]',
//                'name' => $this->nameForm . '_ANADDO[ROWID]',
//                'type' => 'text',
//                'class' => 'ita-hidden')
//                ), array(
//            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDestinazione', "class" => "ita-button-validate", 'model' => $this->nameForm, "shortCut" => "f5")
//                ), $this->nameForm
//        );
//
//        Out::valori($Anaddo_rec, $this->nameForm . '_ANADDO');
//        TableView::disableEvents($this->gridDestinazioni);
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDestinazioni);
        TableView::clearGrid($this->gridDestinazioni);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Ddocod');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
    }

}

?>