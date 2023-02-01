<?php
/**
 *
 * ANAGRAFICA QUALIFCIHE/PROFILI PROFESSIONALI
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

include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praQua() {
    $praQua = new praQua();
    $praQua->parseEvent();
    return;
}

class praQua extends itaModel {

    public $praLib;
    public $utiEnte;
    public $PRAM_DB;
    public $ITALWEB_DB;
    public $nameForm="praQua";
    public $divGes="praQua_divGestione";
    public $divRis="praQua_divRisultato";
    public $divRic="praQua_divRicerca";
    public $gridQua="praQua_gridQua";
    public $tipo;


    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->utiEnte = new utiEnte();
            $this->PRAM_DB=$this->praLib->getPRAMDB();
            $this->ITALWEB_DB=$this->utiEnte->getITALWEB_DB();
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->tipo=App::$utente->getKey($this->nameForm.'_tipo');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm.'_tipo',$this->tipo);
        }

    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->tipo=$_POST['tipo'];
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridQua:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm.'_gridQua':
                        $_POST=array();
                        $model='proDettDestinatari';
                        $_POST[$model.'_proDettCampi']='';
                        $_POST[$model.'_returnField']= $this->nameForm.'_CaricaGridPart';
                        $_POST[$model.'_tipoForm']= 'Richiesta';
                        Out::closeDialog($model);
                        $_POST[$model.'_returnModel']=$this->nameForm;
                        $_POST['event']='openform';
                        itaLib::openForm($model);
                        $appRoute=App::getPath('appRoute.'.substr($model,0,3));
                        include_once App::getConf('modelBackEnd.php').'/'.$appRoute.'/'.$model.'.php';
                        $model();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridQua:
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
                $ita_grid01 = new TableView($this->gridQua,
                        array(
                                'sqlDB'=>$this->PRAM_DB,
                                'sqlQuery'=>$sql));
                $ita_grid01->setSortIndex('ARCDES');
                $ita_grid01->exportXLS('','Anaarc.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec=$this->utiEnte->GetParametriEnte();
                $titolo='';
                switch ($this->tipo) {
                    case 'QU':
                        $titolo="ANAGRAFICA QUALIFICHE";
                        break;
                    case 'PP':
                        $titolo="ANAGRAFICA PROFILI PROFESSIONALI";
                        break;
                }
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters=array("Sql"=>$this->CreaSql(),"Ente"=>$ParametriEnte_rec['DENOMINAZIONE'],"Titolo"=>$titolo);
                $itaJR->runSQLReportPDF($this->PRAM_DB,'praQua', $parameters);
                break;
            case 'onClickTablePager':
                $tableSortOrder=$_POST['sord'];
                $sql=$this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'],array('sqlDB'=>$this->PRAM_DB,'sqlQuery'=>$sql));
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
                        $ita_grid01 = new TableView($this->gridQua,
                                array(
                                        'sqlDB'=>$this->PRAM_DB,
                                        'sqlQuery'=>$sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(10);
                        $ita_grid01->setSortIndex('ARCDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        }
                        else {   // Visualizzo il risultato
                            Out::hide($this->divGes,'');
                            Out::hide($this->divRic,'');
                            Out::show($this->divRis,'');
                            $this->Nascondi();
                            Out::show($this->nameForm.'_AltraRicerca');
                            Out::show($this->nameForm.'_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridQua);
                        }
                        break;
                    case $this->nameForm.'_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm.'_Nuovo':
                        Out::attributo($this->nameForm.'_ANAARC[ARCCOD]','readonly','1');
                        Out::hide($this->divRic,'');
                        Out::hide($this->divRis,'');
                        Out::show($this->divGes,'');
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm.'_Aggiungi');
                        Out::show($this->nameForm.'_AltraRicerca');
                        Out::setFocus('',$this->nameForm.'_ANAARC[ARCCOD]');
                        break;

                    case $this->nameForm.'_Aggiungi':
                        $codice=$_POST[$this->nameForm.'_ANAARC']['ARCCOD'];
                        $codice=str_repeat("0",6-strlen(trim($codice))).trim($codice);
                        switch ($this->tipo) {
                            case "QU":
                                $codice="QU".$codice;
                                break;
                            case "PP":
                                $codice="PP".$codice;
                                break;
                        }
                        $_POST[$this->nameForm.'_ANAARC']['ARCCOD']=$codice;
                        try {   // Effettuo la FIND
                            $Anaarc_rec=$this->praLib->GetAnaarc($codice);
                            if (!$Anaarc_rec) {
                                $Anaarc_rec=$_POST[$this->nameForm.'_ANAARC'];
                                $insert_Info = 'Oggetto: ' . $Anaarc_rec['ARCCOD'] . " " . $Anaarc_rec['ARCDES'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAARC', $Anaarc_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            }
                            else {
                                Out::msgInfo("Codice già  presente","Inserire un nuovo codice.");
                                Out::setFocus('',$this->nameForm.'_ANAARC[ARCCOD]');
                            }
                        }
                        catch(Exception $e) {
                            switch ($this->tipo) {
                                case 'QU':
                                    Out::msgStop("Errore di Inserimento su ANAGRAFICA TIPOLOGIA.",$e->getMessage());
                                    break;
                                case 'PP':
                                    Out::msgStop("Errore di Inserimento su ANAGRAFICA PROFILI PROFESSIONALI.",$e->getMessage());
                                    break;
                            }
                        }
                        break;

                    case $this->nameForm.'_Aggiorna':
                        $Anaarc_rec=$_POST[$this->nameForm.'_ANAARC'];
                        $codice=$Anaarc_rec['ARCCOD'];
                        $Anaarc_rec['ARCCOD']=str_repeat("0",6-strlen(trim($codice))).trim($codice);
                        switch ($this->tipo) {
                            case "QU":
                                $Anaarc_rec['ARCCOD']="QU".$Anaarc_rec['ARCCOD'];
                                break;
                            case "PP":
                                $Anaarc_rec['ARCCOD']="PP".$Anaarc_rec['ARCCOD'];
                                break;
                        }
                        $update_Info = 'Oggetto: ' . $Anarec_rec['ARCCOD'] . " " . $Anaarc_rec['ARCDES'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAARC', $Anaarc_rec, $update_Info)) {
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
                        $Anaarc_rec=$_POST[$this->nameForm.'_ANAARC'];
                        try {
                            $delete_Info = 'Oggetto: ' . $Anaarc_rec['ARCCOD'] . " " . $Anaarc_rec['ARCDES'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAARC', $Anaarc_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        }catch (Exception $e) {
                            switch ($this->tipo) {
                                case 'QU':
                                    Out::msgStop("Errore in Cancellazione su ANAGRAFICA QUALIFICHE",$e->getMessage());
                                    break;
                                case 'PP':
                                    Out::msgStop("Errore in Cancellazione su ANAGRAFICA PROFILI PROFESSIONALI",$e->getMessage());
                                    break;
                            }
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Arccod':
                        $codice=$_POST[$this->nameForm.'_Arccod'];
                        if($codice!='') {
                            $codice=str_repeat("0",6-strlen(trim($codice))).trim($codice);
                            switch ($this->tipo) {
                                case "QU":
                                    $codice="QU".$codice;
                                    break;
                                case "PP":
                                    $codice="PP".$codice;
                                    break;
                            }

                            $Anaarc_rec=$this->praLib->GetAnaarc($codice);
                            if ($Anaarc_rec) {
                                $this->Dettaglio($Anaarc_rec['ROWID']);
                            }
                        }
                        break;
                    case $this->nameForm.'_ANAARC[ARCCOD]':
                        $codice=$_POST[$this->nameForm.'_ANAARC']['ARCCOD'];
                        if (trim($codice)!="") {
                            $codice=str_repeat("0",6-strlen(trim($codice))).trim($codice);
                            Out::valore($this->nameForm.'_ANAARC[ARCCOD]',$codice);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm.'_tipo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        switch ($this->tipo) {
            case 'QU':
                Out::setDialogTitle($this->nameForm, "Archivio Qualifiche");
                break;
            case 'PP':
                Out::setDialogTitle($this->nameForm, "Archivio Profili Professionali");
                Out::setAppSubTitle($this->nameForm, "Archivio Profili Professionali");
                break;
        }
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm.'_Nuovo');
        Out::show($this->nameForm.'_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('',$this->nameForm.'_Arccod');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridQua);
        TableView::clearGrid($this->gridQua);
    }


    public function Nascondi() {
        Out::hide($this->nameForm.'_Aggiungi');
        Out::hide($this->nameForm.'_Aggiorna');
        Out::hide($this->nameForm.'_Cancella');
        Out::hide($this->nameForm.'_AltraRicerca');
        Out::hide($this->nameForm.'_Nuovo');
        Out::hide($this->nameForm.'_Elenca');
    }

    public function CreaSql() {

        $sql="SELECT * FROM ANAARC WHERE ARCCOD ";
        if ($_POST[$this->nameForm.'_Arccod']!="") {
            $sql .= " = '".$this->tipo.$_POST[$this->nameForm.'_Arccod']."'";
        }else {
            $sql .= " LIKE '".$this->tipo."%'";
        }
        if ($_POST[$this->nameForm.'_Arcdes']!="") {
            $sql .= " AND ARCDES LIKE '%".addslashes($_POST[$this->nameForm.'_Arcdes'])."%'";
        }

        return $sql;
    }

    public function Dettaglio($_Indice) {
        $Anaarc_rec=$this->praLib->GetAnaarc($_Indice,'rowid');
        $open_Info='Oggetto: ' . $Anaarc_rec['ARCCOD'] . " " . $Anaarc_rec['ARCDES'];
        $this->openRecord($this->PRAM_DB, 'ANAARC', $open_Info);
        $this->Nascondi();
        $Anaarc_rec['ARCCOD']=substr($Anaarc_rec['ARCCOD'],2);
        Out::valori($Anaarc_rec,$this->nameForm.'_ANAARC');
        Out::show($this->nameForm.'_Aggiorna');
        Out::show($this->nameForm.'_Cancella');
        Out::show($this->nameForm.'_AltraRicerca');
        Out::hide($this->divRic,'');
        Out::hide($this->divRis,'');
        Out::show($this->divGes,'');
        Out::attributo($this->nameForm.'_ANAARC[ARCCOD]','readonly','0');
        Out::setFocus('',$this->nameForm.'_ANAARC[ARCDES]');
        TableView::disableEvents($this->gridQua);
    }
}
?>
