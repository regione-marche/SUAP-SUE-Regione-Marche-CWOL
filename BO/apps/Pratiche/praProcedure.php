<?php
/**
 *
 * ANAGRAFICA PROCEDURE DI CONTROLLO
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

function praProcedure() {
    $praProcedure = new praProcedure();
    $praProcedure->parseEvent();
    return;
}

class praProcedure extends itaModel {
    public $PRAM_DB;
    public $praLib;
    public $utiEnte;
    public $nameForm="praProcedure";
    public $divGes="praProcedure_divGestione";
    public $divRis="praProcedure_divRisultato";
    public $divRic="praProcedure_divRicerca";
    public $divGridParametri="praProcedure_divGridParametri";
    public $gridProcedure="praProcedure_gridProcedure";
    public $gridParametri="praProcedure_gridParametri";
    public $parametri = array();
    public $tipo;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->parametri = App::$utente->getKey($this->nameForm . '_parametri');
        $this->tipo = App::$utente->getKey($this->nameForm . '_tipo');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_parametri', $this->parametri);
            App::$utente->setKey($this->nameForm . '_tipo', $this->tipo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridProcedure:
                        if($_POST['return']) {
                            $this->CreaCombo();
                        }
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridParametri:
                        $model = 'praParamProc';
                        $rowid = $_POST['rowid'];
                        $rowidChiamante = $_POST[$this->nameForm.'_ANAPCO']['ROWID'];
                        if($rowidChiamante == "") $this->tipo = $_POST[$this->nameForm.'_ANAPCO']['PCOTIP'];
                        App::log('tipo= '.$this->tipo);
                        $_POST = array();
                        $_POST['event'] = 'modifica';
                        $_POST[$model . '_rowidParam'] = $rowid;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnParametri';
                        $_POST[$model . '_returnId'] = $this->gridProcedure;
                        $_POST[$model . '_rowidChiamante'] = $rowidChiamante;
                        $_POST[$model . '_parametri'] = $this->parametri;
                        $_POST[$model . '_tipo'] = $this->tipo;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'addGridRow':
                $model = 'praParamProc';
                $rowidChiamante = $_POST[$this->nameForm.'_ANAPCO']['ROWID'];
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnEvent'] = 'returnParametri';
                $_POST[$model . '_returnId'] = $this->gridProcedure;
                $_POST[$model . '_rowidChiamante'] = $rowidChiamante;
                $_POST[$model . '_parametri'] = $this->parametri;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridProcedure:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?",
                                array(
                                'F8-Annulla'=>array('id'=>$this->nameForm.'_AnnullaCancella','model'=>$this->nameForm,'shortCut'=>"f8"),
                                'F5-Conferma'=>array('id'=>$this->nameForm.'_ConfermaCancella','model'=>$this->nameForm,'shortCut'=>"f5")
                                )
                        );
                        break;
                    case $this->gridParametri:
                        if($this->parametri[$_POST['rowid']]['PREDEFINITO']) {
                            out::msgInfo('ATTENZIONE IMPOSSIBILE CANCELLARE!', 'Il parametro '.$this->parametri[$_POST['rowid']]['PARAMETRO'].'
                                è un parametro predefinito');
                            break;
                        }
                        unset ($this->parametri[$_POST['rowid']]);
                        $this->Dettaglio($_POST[$this->nameForm.'_ANAPCO']['ROWID'], 'rowid', 'unset');
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql=$this->CreaSql();
                $ita_grid01 = new TableView($this->gridProcedure,
                        array(
                                'sqlDB'=>$this->PRAM_DB,
                                'sqlQuery'=>$sql));
                $ita_grid01->setSortIndex('PCODES');
                $ita_grid01->exportXLS('','procedure.xls');
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
                $ita_grid01->getDataPageFromArray('json',$Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec=$this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters=array("Sql"=>$this->CreaSql(),"Ente"=>$ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB,'praProcedure', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm.'_Elenca': // Evento bottone Elenca
                    // Importo l'ordinamento del filtro
                        $sql=$this->CreaSql();
                        try {   // Effettuo la FIND
                            $ita_grid01 = new TableView($this->gridProcedure,
                                    array(
                                            'sqlDB'=>$this->PRAM_DB,
                                            'sqlQuery'=>$sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows(1000);
                            $ita_grid01->setSortIndex('PCODES');
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
                                TableView::enableEvents($this->gridProcedure);
                            }
                        }
                        catch(Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.",$e->getMessage());
                        }
                        break;
                    case $this->nameForm.'_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridProcedure);
                        break;
                    case $this->nameForm.'_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm.'_Nuovo':
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::hide($this->divGridParametri);
                        Out::show($this->divGes);
                        $this->Nascondi();
                        $this->AzzeraVariabili();
                        Out::attributo($this->nameForm . '_ANAPCO[PCOCOD]', 'readonly', '1');
                        Out::attributo($this->nameForm . '_ANAPCO[PCOTIP]','disabled','1','');
                        Out::show($this->nameForm.'_Aggiungi');
                        Out::show($this->nameForm.'_AltraRicerca');
                        Out::setFocus('',$this->nameForm.'_ANAPCO[PCOCOD]');
                        break;
                    case $this->nameForm.'_Aggiungi':
                        $codice=$_POST[$this->nameForm.'_ANAPCO']['PCOCOD'];
                        $_POST[$this->nameForm.'_ANAPCO']['PCOCOD']=$codice;
                        $Anapco_ric=$this->praLib->GetAnapco($codice);
                        if (!$Anapco_ric) {
                            $Anapco_ric=$_POST[$this->nameForm.'_ANAPCO'];
                            $Anapco_ric['PCOPAR'] = serialize($this->parametri);
                            try {
                                $insert_Info = 'Oggetto: ' . $Anapco_ric['PCOCOD'] . $Anapco_ric['PCODES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAPCO', $Anapco_ric, $insert_Info)) {
                                    $this->parametri = array();
                                    $this->Dettaglio($Anapco_ric['PCOCOD'], 'codice');
                                }
                            }catch(Exception $e) {
                                Out::msgStop("Errore in Inserimento",$e->getMessage(),'600','600');
                            }
                        }
                        else {
                            Out::msgInfo("Codice già  presente","Inserire un nuovo codice.");
                            Out::setFocus('',$this->nameForm.'_ANAPCO[PCOCOD]');
                        }
                        break;
                    case $this->nameForm.'_Aggiorna':
                        $Anapco_rec=$_POST[$this->nameForm.'_ANAPCO'];
                        $Anapco_rec['PCOPAR'] = serialize($this->parametri);
                        $update_Info = 'Oggetto: ' . $Anapco_rec['PCOCOD'] . $Anapco_rec['PCODES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAPCO', $Anapco_rec, $update_Info)) {
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
                        $Anapco_rec=$_POST[$this->nameForm.'_ANAPCO'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anapco_rec['PCOCOD'] . $Anapco_rec['PCODES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAPCO', $Anapco_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        }catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA PROCEDURE",$e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Pcocod':
                        $codice = $_POST[$this->nameForm . '_Pcocod'];
                        if (trim($codice) != "") {
                            $Anapco_rec = $this->praLib->getAnapco($codice);
                            if ($Anapco_rec) {
                                $this->Dettaglio($Anapco_rec['ROWID']);
                            }
                            Out::valore($this->nameForm . '_Pcocod', $codice);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm.'_ANAPCO[PCOTIP]':
                        switch ($_POST[$this->nameForm.'_ANAPCO']['PCOTIP']) {
                            case 'Http':
                                $this->parametri = $this->CaricaParametriHttp();
                                Out::hide($this->gridParametri.'_addGridRow','');
                                Out::hide($this->gridParametri.'_delGridRow','');
                                break;
                            case 'WS':
                                $this->parametri = $this->CaricaParametriWS();
                                Out::hide($this->gridParametri.'_addGridRow','');
                                Out::hide($this->gridParametri.'_delGridRow','');
                                break;
                            case 'Embed':
                                $this->parametri = $this->CaricaParametriEmbed();
                                Out::show($this->gridParametri.'_addGridRow','');
                                Out::show($this->gridParametri.'_delGridRow','');
                                break;
                        }
                        $this->CaricaGriglia($this->gridParametri, $this->parametri);
                        Out::show($this->divGridParametri,'');
                }
                break;
            case 'returnParametri':
                $this->parametri = $_POST['parametri'];
                $this->CaricaGriglia($this->gridParametri, $this->parametri);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_parametri');
        App::$utente->removeKey($this->nameForm . '_tipo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $sql="SELECT * FROM ANAPCO WHERE ROWID = ROWID";

        if ($_POST[$this->nameForm.'_Pcocod']!="") {
            $sql .= " AND PCOCOD LIKE '%".addslashes($_POST[$this->nameForm.'_Pcocod'])."%'";
        }
        if ($_POST[$this->nameForm.'_Pcodes']!="") {
            $sql .= " AND PCODES LIKE '%".addslashes($_POST[$this->nameForm.'_Pcodes'])."%'";
        }
        return $sql;
    }

    function OpenRicerca() {
        $this->parametri=array();
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        $this->Nascondi();
        $this->AzzeraVariabili();
        Out::show($this->nameForm.'_Nuovo');
        Out::show($this->nameForm.'_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('',$this->nameForm.'_Pcocod');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::clearFields($this->nameForm, $this->nameForm.'_divAppoggio');
        TableView::disableEvents($this->gridProcedure);
        TableView::clearGrid($this->gridProcedure);
        TableView::clearGrid($this->gridParametri);
    }

    public function Nascondi() {
        Out::hide($this->nameForm.'_Aggiungi');
        Out::hide($this->nameForm.'_Aggiorna');
        Out::hide($this->nameForm.'_Cancella');
        Out::hide($this->nameForm.'_AltraRicerca');
        Out::hide($this->nameForm.'_Nuovo');
        Out::hide($this->nameForm.'_Elenca');
        Out::hide($this->nameForm.'_Torna');
    }

    public function Dettaglio($Indice, $tipoRic='rowid', $unset) {
        $Anapco_rec=$this->praLib->GetAnapco($Indice,$tipoRic);
        $this->tipo = $Anapco_rec['PCOTIP'];
        $open_Info='Oggetto: ' . $Anapco_rec['PCOCOD'] . " " . $Anapco_rec['PCODES'];
        $this->openRecord($this->PRAM_DB, 'ANAPCO', $open_Info);
        $this->Nascondi();
        Out::valori($Anapco_rec,$this->nameForm.'_ANAPCO');
        if($unset == "") {
            $this->parametri = unserialize($Anapco_rec['PCOPAR']);
        }
        $this->CaricaParametri();
        Out::show($this->nameForm.'_Aggiorna');
        Out::show($this->nameForm.'_Cancella');
        Out::show($this->nameForm.'_AltraRicerca');
        Out::show($this->nameForm.'_Torna');
        Out::hide($this->divRic,'');
        Out::hide($this->divRis,'');
        Out::show($this->divGes,'');
        Out::show($this->divGridParametri,'');
        if($this->tipo == 'Embed') {
            Out::show($this->gridParametri.'_addGridRow','');
            Out::show($this->gridParametri.'_delGridRow','');
        }else {
            Out::hide($this->gridParametri.'_addGridRow','');
            Out::hide($this->gridParametri.'_delGridRow','');
        }
        Out::setFocus('',$this->nameForm.'_ANAPCO[PCODES]');
        Out::attributo($this->nameForm . '_ANAPCO[PCOCOD]', 'readonly', '0');
        Out::attributo($this->nameForm . '_ANAPCO[PCOTIP]','disabled','0','');
        TableView::disableEvents($this->gridProcedure);
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_ANAPCO[PCOTIP]', 1, "Http", "1", "Http");
        Out::select($this->nameForm . '_ANAPCO[PCOTIP]', 1, "WS", "0", "Web Service");
        Out::select($this->nameForm . '_ANAPCO[PCOTIP]', 1, "Embed", "0", "Embed");
    }

    public function CaricaParametri() {
        if($_POST['parametri']) {
            $this->parametri = $_POST['parametri'];
        }
        $this->CaricaGriglia($this->gridParametri, $this->parametri);
    }

    function CaricaGriglia($griglia, $appoggio, $tipo='1', $pageRows='20') {
        $ita_grid01 = new TableView(
                $griglia,
                array('arrayTable' => $appoggio,
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

    function CaricaParametriHttp() {
        $arrayParam['PARAMETRO'] = 'action';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamHttp[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'method';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamHttp[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'post';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamHttp[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'cedi controllo';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamHttp[] = $arrayParam;
        return $arrayParamHttp;
    }

    function CaricaParametriWS() {
        $arrayParam['PARAMETRO'] = 'server';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamWS[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'service';
        $arrayParam['VALORE'] = '';
        $arrayParamWS[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'action';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamWS[] = $arrayParam;
        $arrayParam['PARAMETRO'] = 'parametri input';
        $arrayParam['VALORE'] = '';
        $arrayParam['PREDEFINITO'] = 1;
        $arrayParamWS[] = $arrayParam;
        return $arrayParamWS;
    }

    function CaricaParametriEmbed() {
        return array();
    }

}
?>