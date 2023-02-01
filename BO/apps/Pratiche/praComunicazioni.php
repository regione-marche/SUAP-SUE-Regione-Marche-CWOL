<?php

/**
 *
 * Ricerca Comunicazioni nei Passi delle Pratiche
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

function praComunicazioni() {
    $praComunicazioni = new praComunicazioni();
    $praComunicazioni->parseEvent();
    return;
}

class praComunicazioni extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    public $utiEnte;
    public $sql;
    public $nameForm = "praComunicazioni";
    public $divGes = "praComunicazioni_divGestione";
    public $divRis = "praComunicazioni_divRisultato";
    public $divRic = "praComunicazioni_divRicerca";
    public $gridComunicazioni = "praComunicazioni_gridComunicazioni";

    function __construct() {
        parent::__construct();
        // Apro il DB
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
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridComunicazioni:
                        $pracom_rec = $this->praLib->GetPracom($_POST['rowid'], 'rowid');
                        $propas_rec = $this->praLib->GetPropas($pracom_rec['COMPAK'], 'propak');
                        $rigaSel = $_POST[$this->gridComunicazioni]['gridParam']['selrow'];
                        $model = 'praPasso';
                        $rowid = $propas_rec['ROWID'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST['sql'] = $this->sql;
                        $_POST['selRow'] = $rigaSel;
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPasso';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridComunicazioni, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('COMDAT');
                $ita_grid01->exportXLS('', 'comunicazioni.xls');
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
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praComunicazioni', $parameters);
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': // Evento bottone Elenca
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
                $rigaSel = $_POST['selRow'];
                //TableView::setSelection($this->gridComunicazioni,$rigaSel);
                Out::codice("jQuery('#" . $this->gridComunicazioni . "').jqGrid('setSelection','$rigaSel');");
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
        $daDataInvio = $_POST[$this->nameForm . '_DaDataInvio'];
        $aDataInvio = $_POST[$this->nameForm . '_ADataInvio'];
        $daDataScadenza = $_POST[$this->nameForm . '_DaDataSca'];
        $aDataScadenza = $_POST[$this->nameForm . '_ADataSca'];
        $destinatario = $_POST[$this->nameForm . '_Destinatario'];
        $tipo = $_POST[$this->nameForm . '_Tipo'];
        $stato = $_POST[$this->nameForm . '_Stato'];
        $silenzio = $_POST[$this->nameForm . '_Silenzio'];
        $escludiChiuse = $_POST[$this->nameForm . '_EscludiChiuse'];
        $StatoPasso = $_POST[$this->nameForm . '_StatoPasso'];

        $anno = date('Y');
        $da_ta = date('Ymd');
        if ($daDataInvio == "")
            $daDataInvio = $anno . "0101";
        if ($aDataInvio == "")
            $aDataInvio = $anno . "1231";
        $sql = "SELECT
                  PRACOM.ROWID AS ROWID,
                  PRACOM.COMTIP AS COMTIP,
                  PRACOM.COMPAK AS COMPAK,
                  PRACOM.COMNOM AS COMNOM,
                  PRACOM.COMMLD AS COMMLD,
                  PRACOM.COMDAT AS COMDAT,
                  PRACOM.COMDFI AS COMDFI,
                  PROPAS.PRONUM AS PRONUM,
                  PROPAS.PROSEQ AS PROSEQ,
                  PRAMITDEST.DATAINVIO AS DATAINVIO,
                  PRAMITDEST.SCADENZARISCONTRO AS SCADENZARISCONTRO,".
                  $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.SIGLA', "'/'", "PROGESCHIUSE.SERIEPROGRESSIVO", "'/'",'PROGESCHIUSE.SERIEANNO') . " AS SERIEPRATICA
                FROM PRACOM PRACOM
                  LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PROPAK = PRACOM.COMPAK
                  LEFT OUTER JOIN PRAMITDEST PRAMITDEST ON PRAMITDEST.KEYPASSO = PRACOM.COMPAK
                  LEFT OUTER JOIN PROGES PROGESCHIUSE ON PRACOM.COMNUM = PROGESCHIUSE.GESNUM
                  LEFT OUTER JOIN ".$this->PROT_DB->getDB().".ANASERIEARC ON ". $this->PROT_DB->getDB().".ANASERIEARC.CODICE = ".$this->PRAM_DB->getDB().".PROGESCHIUSE.SERIECODICE 
                WHERE 1 ";

        if ($daDataInvio != "" && $aDataInvio != "") {
            $sql .= " AND (DATAINVIO BETWEEN '$daDataInvio' AND '$aDataInvio')";
        }
        if ($daDataScadenza != "" && $aDataScadenza != "") {
            $sql .= " AND (SCADENZARISCONTRO <> '' AND (SCADENZARISCONTRO BETWEEN '$daDataScadenza' AND '$aDataScadenza'))";
        }
        if ($destinatario) {
            $sql .= " AND ".$this->PRAM_DB->strUpper('NOME')." LIKE '%" . strtoupper($destinatario) . "%'";
        }
        if ($tipo) {
            $sql .= " AND COMTIP = '$tipo'";
        }
        if ($silenzio == 1) {
            $sql .= " AND COMFSA = $silenzio";
        }

        if ($stato == "I") {
            $sql .= " AND SCADENZARISCONTRO<>'' AND SCADENZARISCONTRO > $da_ta";
        } else if ($stato == "S") {
            
            $sql .= " AND SCADENZARISCONTRO<>'' AND SCADENZARISCONTRO < $da_ta";
        }
        if ($escludiChiuse == 1) {
            $sql .= " AND PROGESCHIUSE.GESDCH = '' AND PROPAS.PROFIN = ''";
        }
        if ($StatoPasso) {
            $sql .= " AND PROSTATO = '$StatoPasso'";
        }
        $sql .= " GROUP BY COMPAK";
//        $sql .= " AND COMPAK IN (SELECT PROPAK FROM PROPAS)
//             GROUP BY COMPAK";

        app::log($sql);
        return $sql;
    }

    function Elenca() {
        // Importo l'ordinamento del filtro
        if ($_POST['sql']) {
            $sql = $_POST['sql'];
        } else {
            $this->sql = $sql = $this->CreaSql();
        }
        try {   // Effettuo la FIND
            $ita_grid01 = new TableView($this->gridComunicazioni, array(
                'sqlDB' => $this->PRAM_DB,
                'sqlQuery' => $sql));
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(1000);
            //$ita_grid01->setSortIndex('COMDAT');
            $ita_grid01->setSortIndex('SERIEPRATICA');
            $ita_grid01->setSortOrder('desc');
            $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
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
                TableView::enableEvents($this->gridComunicazioni);
            }
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
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
        Out::valore($this->nameForm . "_EscludiChiuse", 1);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
    }

    function CreaCombo() {
        //Out::select($this->nameForm . '_Tipo', 1, "M", "0", "In Arrivo");
        //Out::select($this->nameForm . '_Tipo', 1, "D", "0", "In Partenza");
        Out::select($this->nameForm . '_Tipo', 1, "A", "0", "In Arrivo");
        Out::select($this->nameForm . '_Tipo', 1, "P", "0", "In Partenza");

        Out::select($this->nameForm . '_Stato', 1, "I", "0", "In Scadenza");
        Out::select($this->nameForm . '_Stato', 1, "S", "0", "Scadute");
    }

    public function elaboraRecord($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PRATICA'] = $Result_rec['SERIEPRATICA'];
            $Pramitdest_tab = $this->praLib->GetPraMitDest($Result_rec['COMPAK'], "compak", true);
//            if ($Result_rec['COMTIP'] == "D") {
//                $Pramitdest_tab = $this->praLib->GetPraDestinatari($Result_rec['COMPAK'], "codice", true);
//                $Result_tab[$key]['TIPO'] = "In Partenza";
//            } elseif ($Result_rec['COMTIP'] == "M") {
//                $Pramitdest_tab = $this->praLib->GetPraArrivo($Result_rec['COMPAK'], "codice", true);
//                $Result_tab[$key]['TIPO'] = "In Arrivo";
//            }
            $mittDest = $email = $dateInvio = $dateScadenza = $tipoCom = "";
            foreach ($Pramitdest_tab as $Pramitdest_rec) {
                $mittDest .= $Pramitdest_rec['NOME'] . "<br>";
                $email .= $Pramitdest_rec['MAIL'] . "<br>";
                if ($Pramitdest_rec['DATAINVIO'])
                    $dateInvio .= substr($Pramitdest_rec['DATAINVIO'], 6, 2) . "/" . substr($Pramitdest_rec['DATAINVIO'], 4, 2) . "/" . substr($Pramitdest_rec['DATAINVIO'], 0, 4) . "<br>";
                if ($Pramitdest_rec['SCADENZARISCONTRO'])
                    $dateScadenza .= substr($Pramitdest_rec['SCADENZARISCONTRO'], 6, 2) . "/" . substr($Pramitdest_rec['SCADENZARISCONTRO'], 4, 2) . "/" . substr($Pramitdest_rec['SCADENZARISCONTRO'], 0, 4) . "<br>";
                if ($Pramitdest_rec['TIPOCOM'] == "D") {
                    $Pramitdest_tab = $this->praLib->GetPraDestinatari($Result_rec['COMPAK'], "codice", true);
                    $tipoCom .= "In Partenza<br>";
                } elseif ($Pramitdest_rec['TIPOCOM'] == "M") {
                    $Pramitdest_tab = $this->praLib->GetPraArrivo($Result_rec['COMPAK'], "codice", true);
                    $tipoCom .= "In Arrivo<br>";
                }
            }
            $Result_tab[$key]['MITDEST'] = $mittDest;
            $Result_tab[$key]['MAILDEST'] = $email;
            $Result_tab[$key]['DATEINVIO'] = $dateInvio;
            $Result_tab[$key]['DATESCADENZA'] = $dateScadenza;
            $Result_tab[$key]['TIPO'] = $tipoCom;
        }
        return $Result_tab;
    }

}

?>