<?php

/**
 *
 * Ricerca Articoli nei Passi delle Pratiche
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praArticoli() {
    $praArticoli = new praArticoli();
    $praArticoli->parseEvent();
    return;
}

class praArticoli extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    public $utiEnte;
    public $sql;
    public $nameForm = "praArticoli";
    public $divGes = "praArticoli_divGestione";
    public $divRis = "praArticoli_divRisultato";
    public $divRic = "praArticoli_divRicerca";
    public $gridPassi = "praArticoli_gridPassi";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->sql = App::$utente->getKey($this->nameForm . '_sql');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_sql', $this->sql);
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': 
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridPassi:
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST['sql'] = $this->sql;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                        $_POST[$model . '_title'] = 'Gestione Passo.....';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridPassi, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('SERIEPRATICA');
                $ita_grid01->exportXLS('', 'articoli.xls');
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'PRATICA') {
                    $ordinamento = 'SERIEPRATICA';
                }
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $ita_grid01->getDataArray();
                $Result_tab = $this->elaboraRecord($Result_tab);
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praArticoli', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
                        // Importo l'ordinamento del filtro
                        $this->Elenca();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_StatoPasso_butt':
                        praRic::praRicAnastp($this->nameForm, '', "STATOPASSO");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_StatoPasso':
                        if ($_POST[$this->nameForm . '_StatoPasso']) {
                            $codice = $_POST[$this->nameForm . '_StatoPasso'];
                            $anastp_rec = $this->praLib->GetAnastp($codice);
                            if ($anastp_rec) {
                                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                            } else {
                                Out::valore($this->nameForm . '_Stato1', "");
                            }
                        } else {
                            Out::valore($this->nameForm . '_Stato1', "");
                        }
                        break;
                }
                break;
            case 'returnPraPasso':
                $this->Elenca();
                break;
            case 'returnAnastp':
                $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_StatoPasso', $anastp_rec['ROWID']);
                Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_sql');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $daDataPubb = $_POST[$this->nameForm . '_DaDataPub'];
        $aDataPubb = $_POST[$this->nameForm . '_ADataPub'];
        $daDataSca = $_POST[$this->nameForm . '_DaDataSca'];
        $aDataSca = $_POST[$this->nameForm . '_ADataSca'];
        $contenuto = $_POST[$this->nameForm . '_Articolo'];
        $titolo = $_POST[$this->nameForm . '_Titolo'];
        $tipo = $_POST[$this->nameForm . '_Tipo'];
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];

//        $anno = date('Y');
//        if($daDataPubb == "")
//            $daDataPubb = $anno."0101";
//        if($daDataSca == "")
//            $daDataSca = $anno."0101";
//        if($aDataPubb == "")
//            $aDataPubb = $anno."1231";
//        if($aDataSca == "")
//            $aDataSca = $anno."1231";

        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRONUM AS PRONUM,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROPTIT AS PROPTIT,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROPDADATA AS PROPDADATA,
                    PROPAS.PROPADDATA AS PROPADDATA,
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROPPASS AS PROPPASS,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROINI AS PROINI," .
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,".
                $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'",'PROGES.SERIEANNO') . " AS SERIEPRATICA
              FROM PROPAS
                LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
                LEFT OUTER JOIN PROGES ON PROGES.GESNUM=PROPAS.PRONUM
                LEFT OUTER JOIN ".$this->PROT_DB->getDB().".ANASERIEARC ON ". $this->PROT_DB->getDB().".ANASERIEARC.CODICE = ".$this->PRAM_DB->getDB().".PROGES.SERIECODICE 
              WHERE PROPAS.PROPART = 1 ";

        if ($daDataPubb != "" && $aDataPubb != "") {
            $sql .= " AND (PROPDADATA BETWEEN '$daDataPubb' AND '$aDataPubb')";
        }
        if ($daDataSca != "" && $aDataSca != "") {
            $sql .= " AND (PROPADDATA BETWEEN '$daDataSca' AND '$aDataSca')";
        }
        if ($contenuto) {
            $sql .= " AND PROPCONT LIKE '%$contenuto%'";
        }
        if ($titolo) {
            $sql .= " AND PROPTIT LIKE '%$titolo%'";
        }
        if ($tipo == 'Pr') {
            $sql .= " AND PROPPASS <> ''";
        } else if ($tipo == 'Pu') {
            $sql .= " AND PROPPASS = ''";
        }
        if ($StatoPasso) {
            $sql .= " AND PROSTATO = '$StatoPasso'";
        }
        app::log($sql);
        return $sql;
    }

    function Elenca() {
        if ($_POST['sql']) {
            $sql = $_POST['sql'];
        } else {
            $this->sql = $sql = $this->CreaSql();
        }
        try {   // Effettuo la FIND
            $ita_grid01 = new TableView($this->gridPassi, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            $ita_grid01->setSortIndex('SERIEPRATICA');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $ita_grid01->getDataArray();
            $Result_tab = $this->elaboraRecord($Result_tab);
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab)) {
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
                TableView::enableEvents($this->gridPassi);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_Tipo', 1, "Pu", "0", "Publici");
        Out::select($this->nameForm . '_Tipo', 1, "Pr", "0", "Protetti");
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '');
        Out::show($this->divRic, '');
        Out::hide($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridPassi);
        TableView::clearGrid($this->gridArticoli);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_DaDataPub');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PRATICA'] =$Result_rec['SERIEPRATICA'];
            if ($Result_rec['PROPPASS']) {
                app::log("quii");
                $Result_tab[$key]['PROTETTO'] = '<span class="ita-icon ita-icon-key-24x24">Protetto</span>';
            }
        }
        return $Result_tab;
    }

}

?>