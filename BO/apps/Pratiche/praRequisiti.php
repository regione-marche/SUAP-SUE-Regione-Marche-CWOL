<?php
/**
 *
 * ANAGRAFICA REQUISISI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 **/

// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praRequisiti() {
    $praRequisiti = new praRequisiti();
    $praRequisiti->parseEvent();
    return;
}

class praRequisiti extends itaModel {
    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $requisito;
    public $nameForm="praRequisiti";
    public $divGes="praRequisiti_divGestione";
    public $divRis="praRequisiti_divRisultato";
    public $divRic="praRequisiti_divRicerca";
    public $gridRequisiti="praRequisiti_gridRequisiti";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->requisito = App::$utente->getKey($this->nameForm . '_requisito');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_requisito', $this->requisito);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                $this->CreaCombo();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRequisiti:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridRequisiti:
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
                $ita_grid01 = new TableView($this->gridRequisiti,
                        array(
                                'sqlDB'=>$this->PRAM_DB,
                                'sqlQuery'=>$sql));
                $ita_grid01->setSortIndex('REQDES');
                $ita_grid01->exportXLS('','requisiti.xls');
                break;
            case 'onClickTablePager':
                $ordinamento=$_POST['sidx'];
                $tableSortOrder=$_POST['sord'];
                $sql=$this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'],array('sqlDB'=>$this->PRAM_DB,'sqlQuery'=>$sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab=$ita_grid01->getDataArray();
                //$Result_tab=$this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json',$Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec=$this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters=array("Sql"=>$this->CreaSql(),"Ente"=>$ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB,'praRequisiti', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm.'_Elenca': // Evento bottone Elenca
                    // Importo l'ordinamento del filtro
                        $sql=$this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridRequisiti,
                                    array(
                                            'sqlDB'=>$this->PRAM_DB,
                                            'sqlQuery'=>$sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('REQDES');
                            $Result_tab=$ita_grid01->getDataArray();
                            //$Result_tab=$this->elaboraRecord($Result_tab);
                            if (!$ita_grid01->getDataPageFromArray('json',$Result_tab)) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            }
                            else {   // Visualizzo la ricerca
                                Out::hide($this->divGes,'');
                                Out::hide($this->divRic,'');
                                Out::show($this->divRis,'');
                                $this->Nascondi();
                                Out::show($this->nameForm.'_AltraRicerca');
                                Out::show($this->nameForm.'_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridRequisiti);

                            }
                        }
                        catch(Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.",$e->getMessage());
                        }
                        break;

                    case $this->nameForm.'_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm.'_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        Out::clearFields($this->nameForm, $this->divRic);
                        Out::clearFields($this->nameForm, $this->divGes);
                        Out::attributo($this->nameForm . '_ANASET[SETCOD]', 'readonly', '1');
                        Out::show($this->nameForm.'_Aggiungi');
                        Out::show($this->nameForm.'_AltraRicerca');
                        Out::setFocus('',$this->nameForm.'_ANASET[SETCOD]');
                        break;
                    case $this->nameForm.'_Aggiungi':
                        $codice=$_POST[$this->nameForm.'_ANAREQ']['REQCOD'];
                        if($codice == '') {
                            $Anareq_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAREQ ORDER BY REQCOD DESC", false, 1, 1);
                            $codice = $Anareq_rec['REQCOD'] + 1;
                        }
                        $_POST[$this->nameForm.'_ANAREQ']['REQCOD']=$codice;
                        $Anareq_ric=$this->praLib->GetAnareq($codice);
                        if (!$Anareq_ric) {
                            $Anareq_ric=$_POST[$this->nameForm.'_ANAREQ'];
                            try {
                                $Anareq_ric = $this->praLib->SetMarcaturaRequisito($Anareq_ric, true);
                                $insert_Info = 'Oggetto: ' . $Anareq_ric['REQCOD'] . $Anareq_ric['REQDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAREQ', $Anareq_ric, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            }catch(Exception $e) {
                                Out::msgStop("Errore in Inserimento",$e->getMessage(),'600','600');
                            }
                        }
                        else {
                            Out::msgInfo("Codice già  presente","Inserire un nuovo codice.");
                            Out::setFocus('',$this->nameForm.'_ANAREQ[REQCOD]');
                        }
                        break;
                    case $this->nameForm.'_Aggiorna':
                        if(!$this->Aggiorna($_POST[$this->nameForm.'_ANAREQ'])) {
                            Out::msgStop("Aggiornamento requisito", "Errore in aggiornamento su ANAREQ");
                            break;
                        }
                        $this->OpenRicerca();
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
                        $Anareq_rec=$_POST[$this->nameForm.'_ANAREQ'] ;
                        try {
                            $delete_Info = 'Oggetto: ' . $Anareq_ric['REQCOD'] . $Anareq_ric['REQDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAREQ', $Anareq_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        }catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA REQUISITI",$e->getMessage());
                        }
                        break;
                    case $this->nameForm.'_ANAREQ[REQFIL]_butt':
                        if ($_POST[$this->nameForm.'_ANAREQ']['REQFIL']!='') {
                            $ditta = App::$utente->getKey('ditta');
                            $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                    $_POST[$this->nameForm.'_ANAREQ']['REQFIL'],
                                    $destinazione.$_POST[$this->nameForm.'_ANAREQ']['REQFIL']
                                    )
                            );
                        }
                        break;
                    case $this->nameForm.'_FileLocaleTesto':
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
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Reqcod':
                        $codice = $_POST[$this->nameForm . '_Reqcod'];
                        if (trim($codice) != "") {
                            $Anareq_rec = $this->praLib->getAnareq($codice);
                            if ($Anareq_rec) {
                                $this->Dettaglio($Anareq_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Reqcod', $codice);
                        }
                        break;
                    case $this->nameForm.'_ANASET[SETCOD]':
                        $codice=$_POST[$this->nameForm.'_ANASET']['SETCOD'];
                        if (trim($codice)!="") {
                            Out::valore($this->nameForm.'_ANASET[SETCOD]',$codice);
                        }
                        break;

                }
                break;
            case 'returnUploadFile':
                $this->AllegaTesto();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_requisito');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql="SELECT * FROM ANAREQ WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm.'_Reqcod']!="") {
            $sql .= " AND REQCOD = '".$_POST[$this->nameForm.'_Reqcod']."'";
        }
        if ($_POST[$this->nameForm.'_Reqdes']!="") {
            $sql .= " AND REQDES LIKE '%".addslashes($_POST[$this->nameForm.'_Reqdes'])."%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::html($this->nameForm . "_Editore", '&nbsp;');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridRequisiti);
        TableView::clearGrid($this->gridRequisiti);
        $this->Nascondi();
        Out::show($this->nameForm.'_Nuovo');
        Out::show($this->nameForm.'_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('',$this->nameForm.'_Reqcod');
    }

    public function Nascondi() {
        Out::hide($this->nameForm.'_Aggiungi');
        Out::hide($this->nameForm.'_Aggiorna');
        Out::hide($this->nameForm.'_Cancella');
        Out::hide($this->nameForm.'_AltraRicerca');
        Out::hide($this->nameForm.'_Nuovo');
        Out::hide($this->nameForm.'_Elenca');
    }

    public function Dettaglio($Indice) {
        $Anareq_rec=$this->praLib->GetAnareq($Indice,'rowid');
        $this->requisito = $Anareq_rec['REQCOD'];
        $open_Info='Oggetto: ' . $Anareq_rec['REQCOD'] . " " . $Anareq_rec['REQDES'];
        $this->openRecord($this->PRAM_DB, 'ANAREQ', $open_Info);
        $this->visualizzaMarcatura($Anareq_rec);
        $this->Nascondi();
        Out::valori($Anareq_rec,$this->nameForm.'_ANAREQ');
        Out::show($this->nameForm.'_Aggiorna');
        Out::show($this->nameForm.'_Cancella');
        Out::show($this->nameForm.'_AltraRicerca');
        Out::hide($this->divRic,'');
        Out::hide($this->divRis,'');
        Out::show($this->divGes,'');
        Out::setFocus('',$this->nameForm.'_ANAREQ[REQDES]');
        Out::attributo($this->nameForm . '_ANAREQ[REQCOD]', 'readonly', '0');
        TableView::disableEvents($this->gridRequisiti);
    }

    public function Aggiorna($Anareq_rec) {
        $Anareq_rec = $this->praLib->SetMarcaturaRequisito($Anareq_rec);
        $update_Info = 'Oggetto: ' . $Anareq_ric['REQCOD'] . $Anareq_ric['REQDES'];
        if (!$this->updateRecord($this->PRAM_DB, 'ANAREQ', $Anareq_rec, $update_Info)) {
            return false;
        }
        return true;
    }

    public function visualizzaMarcatura($Anareq_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anareq_rec['REQUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anareq_rec['REQUPDDATE'])) . ' ' . $Anareq_rec['REQUPDTIME'] . '  </span>');
    }

    public function CreaCombo() {
        Out::select($this->nameForm.'_ANAREQ[REQTIPO]', 1,"","0", "");
        Out::select($this->nameForm.'_ANAREQ[REQTIPO]', 1,"OGGETTIVO","0", "OGGETTIVO");
        Out::select($this->nameForm.'_ANAREQ[REQTIPO]', 1,"SOGGETTIVO","0", "SOGGETTIVO");
        Out::select($this->nameForm.'_ANAREQ[REQTIPO]', 1,"NOTE","0", "NOTE");
        Out::select($this->nameForm.'_ANAREQ[REQAREA]', 1,"","0", "");
        Out::select($this->nameForm.'_ANAREQ[REQAREA]', 1,"Ambiente","0", "AMBIENTE");
        Out::select($this->nameForm.'_ANAREQ[REQAREA]', 1,"Edilizia","0", "EDILIZIA");
        Out::select($this->nameForm.'_ANAREQ[REQAREA]', 1,"Sicurezza","0", "SICUREZZA");
    }

    function AllegaTesto() {
        $origFile = $_POST['uploadedFile'];
        if ($ditta == '') $ditta = App::$utente->getKey('ditta');
        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
        if (!is_dir($destinazione)) {
            Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: '.$destinazione);
            return false;
        }
        $ext = ".".pathinfo($origFile, PATHINFO_EXTENSION);
        $nomeFile = $destinazione."requisito_".$this->requisito.$ext;
        if ($nomeFile!='') {
            if (!@rename($origFile, $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm.'_ANAREQ[REQFIL]',"requisito_".$this->requisito.$ext);
                $Anareq_rec = $this->praLib->GetAnareq($this->requisito);
                $Anareq_rec["REQFIL"] = "requisito_".$this->requisito.$ext;
                if(!$this->Aggiorna($Anareq_rec)) {
                    Out::msgStop("Aggiornamento requisito", "Errore in aggiornamento su ANAREQ");
                    return false;
                }
                Out::msgInfo("Aggiornamento Requisito", "Testo Associato modificato e record aggiornato");
                $this->Dettaglio($Anareq_rec['ROWID']);
            }
        }else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

}
?>