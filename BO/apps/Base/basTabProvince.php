<?php
/**
 *
 * Archivio PROVINCE
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    28.08.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once './apps/Base/basLib.class.php';
include_once './apps/Utility/utiEnte.class.php';

function basTabProvince() {
    $basTabProvince = new basTabProvince();
    $basTabProvince->parseEvent();
    return;
}

class basTabProvince extends itaModel {

    public $COMUNI_DB;
    public $basLib;
    public $nameForm = "basTabProvince";
    public $divGes = "basTabProvince_divGestione";
    public $divRis = "basTabProvince_divRisultato";
    public $divRic = "basTabProvince_divRicerca";
    public $gridComune = "basTabProvince_gridComune";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->COMUNI_DB = $this->basLib->getCOMUNIDB();
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
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridComune:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridComune:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?",
                                array(
                                'F8-Annulla'=>array('id'=>$this->nameForm.'_AnnullaCancella','model'=>$this->nameForm,'shortCut'=>"f8"),
                                'F5-Conferma'=>array('id'=>$this->nameForm.'_ConfermaCancella','model'=>$this->nameForm,'shortCut'=>"f5")
                                )
                        );

                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql=$this->CreaSql();
                $ita_grid01 = new TableView($this->gridComune,
                        array(
                                'sqlDB'=>$this->COMUNI_DB,
                                'sqlQuery'=>$sql));
                $ita_grid01->setSortIndex('PROVINCIA');
                $ita_grid01->exportXLS('','comuni.xls');
                break;
            case 'printTableToHTML':
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY PROVINCIA', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->COMUNI_DB, 'basProvince', $parameters);
                break;
            case 'onClickTablePager':
                $sql=$this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'],array('sqlDB'=>$this->COMUNI_DB,'sqlQuery'=>$sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Elenca':
                        $sql=$this->CreaSql();
                        $ita_grid01 = new TableView($this->gridComune,
                                array(
                                        'sqlDB'=>$this->COMUNI_DB,
                                        'sqlQuery'=>$sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridComune]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('PROVINCIA');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm.'_AltraRicerca');
                            Out::show($this->nameForm.'_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridComune);
                        }
                        break;
                    case $this->nameForm.'_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm.'_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm.'_Aggiungi');
                        Out::show($this->nameForm.'_AltraRicerca');
                        Out::setFocus('',$this->nameForm.'_PROVINCE[PROVINCIA]');
                        break;
                    case $this->nameForm.'_Aggiungi':
                        $codice=$_POST[$this->nameForm.'_PROVINCE']['PROVINCIA'];
                        $comune_rec=$this->basLib->getProvince($_POST[$this->nameForm.'_PROVINCE']['PROVINCIA']);
                        if (!$comune_rec) {
                            $comune_rec=$_POST[$this->nameForm.'_PROVINCE'];
                            $insert_Info = 'Oggetto: ' . $comune_rec['PROVINCIA'];
                            if ($this->insertRecord($this->COMUNI_DB, 'PROVINCE', $comune_rec, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        }
                        else {
                            Out::msgInfo("Provincia giра presente","Inserire una nuova Provincia.");
                            Out::setFocus('',$this->nameForm.'_PROVINCE[PROVINCIA]');
                        }
                        break;
                    case $this->nameForm.'_Aggiorna':
                        $comune_rec=$_POST[$this->nameForm.'_PROVINCE'];
                        $update_Info = 'Oggetto: ' . $comune_rec['PROVINCIA'];
                        if ($this->updateRecord($this->COMUNI_DB, 'PROVINCE', $comune_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm.'_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?",
                                array(
                                'F8-Annulla'=>array('id'=>$this->nameForm.'_AnnullaCancella','model'=>$this->nameForm,'shortCut'=>"f8"),
                                'F5-Conferma'=>array('id'=>$this->nameForm.'_ConfermaCancella','model'=>$this->nameForm,'shortCut'=>"f5")
                                )
                        );
                        break;
                    case $this->nameForm.'_ConfermaCancella':
                        $comune_rec=$_POST[$this->nameForm.'_PROVINCE'];
                        $delete_Info = 'Oggetto: ' . $comune_rec['PROVINCIA'];
                        if ($this->deleteRecord($this->COMUNI_DB, 'PROVINCE', $comune_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridComune);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm.'_Nuovo');
        Out::show($this->nameForm.'_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('',$this->nameForm.'_Codice');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_PROVINCE[ROWID]', '');
        TableView::disableEvents($this->gridComune);
        TableView::clearGrid($this->gridComune);
    }


    function Nascondi() {
        Out::hide($this->nameForm.'_Aggiungi');
        Out::hide($this->nameForm.'_Aggiorna');
        Out::hide($this->nameForm.'_Cancella');
        Out::hide($this->nameForm.'_AltraRicerca');
        Out::hide($this->nameForm.'_Nuovo');
        Out::hide($this->nameForm.'_Elenca');
        Out::hide($this->nameForm.'_Torna');
    }

    function CreaSql() {
        $sql="SELECT * FROM PROVINCE";
        if ($_POST[$this->nameForm.'_Codice']!="") {
            $sql .= " WHERE ".$this->COMUNI_DB->strUpper('PROVINCIA')." LIKE '%".strtoupper($_POST[$this->nameForm.'_Codice'])."%'";
        }
        App::log($sql);
        return $sql;
    }

    function Dettaglio($indice) {
        $comune_rec=$this->basLib->getProvince($indice,'rowid');
        $open_Info='Oggetto: ' . $comune_rec['PROVINCIA'] . " " . $comune_rec['PROVINCIA'];
        $this->openRecord($this->COMUNI_DB, 'PROVINCE', $open_Info);
        $this->Nascondi();
        Out::valori($comune_rec,$this->nameForm.'_PROVINCE');
        Out::show($this->nameForm.'_Aggiorna');
        Out::show($this->nameForm.'_Cancella');
        Out::show($this->nameForm.'_AltraRicerca');
        Out::show($this->nameForm.'_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::setFocus('',$this->nameForm.'_PROVINCE[PROVINCIA]');
        TableView::disableEvents($this->gridComune);
    }
}
?>