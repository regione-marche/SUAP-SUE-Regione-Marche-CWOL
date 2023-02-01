<?php
/**
 *
 * Relazioni Procedimenti - Titolario
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    28.05.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function proTitProc() {
    $proTitProc = new proTitProc();
    $proTitProc->parseEvent();
    return;
}

class proTitProc extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $praLib;
    public $nameForm = "proTitProc";
    public $divDettaglio = "proTitProc_divDettaglio";
    public $gridRel = "proTitProc_gridRel";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->praLib = new praLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
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
                $this->caricaTitProc();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRel:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridRel:
                        $this->caricaTitProc();
                        break;
                }
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridRel:
                        $this->deleteRecord($this->PROT_DB, 'TITPROC', $_POST['rowid'], '', 'ROWID', false);
                        $this->caricaTitProc();
                        break;
                }
                break;
            case 'exportTableToExcel':
                break;
            case 'printTableToHTML':
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRel:
                        $ita_grid01 = new TableView($_POST['id'],array('sqlDB'=>$this->PROT_DB,'sqlQuery'=>"SELECT * FROM TITPROC"));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'afterSaveCell':
                if ($_POST['value']!='undefined') {
                    switch ($_POST['id']) {
                        case $this->gridRel:
                            break;
                    }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm.'_Salva':
                        $titproc_rec=$_POST[$this->nameForm.'_TITPROC'];
                        $insert_Info = 'Oggetto: '.$titproc_rec['PRANUM']." "
                                .$titproc_rec['CATCOD'].$titproc_rec['CLACOD'].$titproc_rec['FASCOD'];
                        if ($titproc_rec['ROWID']=='') {
                            $this->insertRecord($this->PROT_DB, 'TITPROC', $titproc_rec, $insert_Info);
                        }else {
                            $this->updateRecord($this->PROT_DB, 'TITPROC', $titproc_rec, $insert_Info);
                        }
                        $this->caricaTitProc();
                        break;
                    case $this->nameForm . '_TITPROC[PRANUM]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_PROGES[GESPRO]');
                        break;
                    case $this->nameForm . '_TITPROC[CATCOD]_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_TITPROC[CLACOD]_butt':
                        if ($_POST[$this->nameForm.'_TITPROC']['CATCOD']) {
                            $where=array('ANACAT'=>" AND CATCOD='".$_POST[$this->nameForm.'_TITPROC']['CATCOD']."'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_TITPROC[FASCOD]_butt':
                        if ($_POST[$this->nameForm.'_TITPROC']['CATCOD']) {
                            $where['ANACAT']=" AND CATCOD='".$_POST[$this->nameForm.'_TITPROC']['CATCOD']."'";
                            if ($_POST[$this->nameForm.'_TITPROC']['CLACOD']) {
                                $where['ANACLA']=" AND CLACCA='".$_POST[$this->nameForm.'_TITPROC']['CATCOD']
                                        .$_POST[$this->nameForm.'_TITPROC']['CLACOD']."'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm.'_TITPROC[PRANUM]':
                        $codice = $_POST[$this->nameForm . '_TITPROC']['PRANUM'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $anapra_rec = $this->praLib->GetAnapra($codice);
                        Out::valore($this->nameForm . '_TITPROC[PRANUM]', $anapra_rec['PRANUM']);
                        Out::valore($this->nameForm . '_ProcDecod', $anapra_rec['PRADES__1']);
                        break;
                    case $this->nameForm.'_TITPROC[CATCOD]':
                        $codice = $_POST[$this->nameForm.'_TITPROC']['CATCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacat($codice);
                        break;
                    case $this->nameForm.'_TITPROC[CLACOD]':
                        $codice = $_POST[$this->nameForm.'_TITPROC']['CLACOD'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm.'_TITPROC']['CATCOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        }else {
                            $codice = $_POST[$this->nameForm.'_TITPROC']['CATCOD'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacat($codice);
                        }
                        break;
                    case $this->nameForm.'_TITPROC[FASCOD]':
                        $codice = $_POST[$this->nameForm.'_TITPROC']['FASCOD'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm.'_TITPROC']['CATCOD'];
                            $codice2 = $_POST[$this->nameForm.'_TITPROC']['CLACOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafas($codice1 . $codice2 . $codice3, 'fasccf');
                        }else {
                            $codice = $_POST[$this->nameForm.'_TITPROC']['CLACOD'];
                            $codice1 = $_POST[$this->nameForm.'_TITPROC']['CATCOD'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        }
                        break;
                }
                break;
            case 'returnAnapra':
                $this->DecodAnapra($_POST['retKey'], 'rowid');
                break;
            case 'returnTitolario':
                $tipoArc=substr($_POST['rowData']['CHIAVE'],0,6);
                $rowid=substr($_POST['rowData']['CHIAVE'],7,6);
                $this->decodTitolario($rowid, $tipoArc);
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }

    function caricaTitProc() {
        Out::show($this->nameForm);
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valore($this->nameForm . '_TITPROC[ROWID]', '');
        $this->caricaRel();
        Out::setFocus('',$this->nameForm.'_TITPROC[PRANUM]');
    }

    function Dettaglio($indice) {
        $titproc_rec=$this->proLib->GetTitproc($indice,'rowid');
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valori($titproc_rec,$this->nameForm.'_TITPROC');
        $this->DecodAnapra($titproc_rec['PRANUM']);
        $this->DecodAnacat($titproc_rec['CATCOD']);
        $this->DecodAnacla($titproc_rec['CATCOD'].$titproc_rec['CLACOD']);
        $this->DecodAnafas($titproc_rec['CATCOD'].$titproc_rec['CLACOD'].$titproc_rec['FASCOD'],'fasccf');
        Out::setFocus('',$this->nameForm.'_TITPROC[PRANUM]');
    }

    function caricaRel() {
        TableView::clearGrid($this->gridRel);
        $ita_grid01 = new TableView($this->gridRel,
                array(
                        'sqlDB'=>$this->PROT_DB,
                        'sqlQuery'=>"SELECT * FROM TITPROC"));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->setSortIndex('PRANUM');
        $ita_grid01->setSortOrder('asc');
        $ita_grid01->getDataPage('json');
    }

    function decodTitolario($rowid, $tipoArc) {
        $cat=$cla=$fas=$des='';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat=$anacat_rec['CATCOD'];
                $des=$anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat=$anacla_rec['CLACAT'];
                $cla=$anacla_rec['CLACOD'];
                $des=$anacla_rec['CLADE1'].$anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat=substr($anafas_rec['FASCCA'],0,4);
                $cla=substr($anafas_rec['FASCCA'],4);
                $fas=$anafas_rec['FASCOD'];
                $des=$anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_TITPROC[CATCOD]', $cat);
        Out::valore($this->nameForm . '_TITPROC[CLACOD]', $cla);
        Out::valore($this->nameForm . '_TITPROC[FASCOD]', $fas);
        Out::valore($this->nameForm . '_TitolarioDecod', $des);
    }

    function DecodAnacat($codice, $tipo='codice') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        }else {
            Out::valore($this->nameForm . '_TITPROC[CATCOD]', '');
            Out::valore($this->nameForm . '_TITPROC[CLACOD]', '');
            Out::valore($this->nameForm . '_TITPROC[FASCOD]', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anacat_rec;
    }

    function DecodAnacla($codice, $tipo='codice') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        }else {
            Out::valore($this->nameForm . '_TITPROC[CLACOD]', '');
            Out::valore($this->nameForm . '_TITPROC[FASCOD]', '');
        }
        return $anacla_rec;
    }

    function DecodAnafas($codice, $tipo='fasccf') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        }else {
            Out::valore($this->nameForm . '_TITPROC[FASCOD]', '');
        }
        return $anafas_rec;
    }

    function DecodAnapra($Codice, $tipoRic = 'codice') {
        $anapra_rec = $this->praLib->GetAnapra($Codice, $tipoRic);
        Out::valore($this->nameForm . '_TITPROC[PRANUM]', $anapra_rec['PRANUM']);
        Out::valore($this->nameForm . '_ProcDecod', $anapra_rec['PRADES__1']);
    }
}
?>