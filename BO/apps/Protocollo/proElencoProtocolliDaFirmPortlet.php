<?php

/* * 
 *
 * PORTLET PROTOCOLLI CON ALLEGATI DA FIRMARE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    02.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function proElencoProtocolliDaFirmPortlet() {
    $proElencoProtocolliDaFirmPortlet = new proElencoProtocolliDaFirmPortlet();
    $proElencoProtocolliDaFirmPortlet->parseEvent();
    return;
}

class proElencoProtocolliDaFirmPortlet extends itaModel {

    public $PROT_DB;
    public $ITW_DB;
    public $nameForm = "proElencoProtocolliDaFirmPortlet";
    public $divRis = "proElencoProtocolliDaFirmPortlet_divRisultato";
    public $gridProtocolli = "proElencoProtocolliDaFirmPortlet_gridProtocolli";
    public $proLib;
    public $accLib;
    public $utente;
    public $tabella;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->accLib = new accLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITW_DB = $this->accLib->getITW();
            $this->utente = App::$utente->getKey($this->nameForm . '_utente');
            $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_utente', $this->utente);
            App::$utente->setKey($this->nameForm . '_tabella', $this->tabella);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm('proElencoProtocolliDaFirmPortlet', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");
                $this->utente = App::$utente->getKey('nomeUtente');
                $this->caricaDatiGriglia();
                $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella);

                break;
            case 'editGridRow':
            case 'dbClickRow':
                if ($_POST['rowid'] != '0') {
                    $indice = $_POST['rowid'];
                    $Anaproctr_rec = $this->proLib->GetAnapro($indice, 'rowid');
                    $model = 'proArri';
                    $_POST = array();
                    $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
                    $_POST['event'] = 'openform';
                    $_POST['proGest_ANAPRO']['ROWID'] = $indice;
                    itaLib::openForm($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridProtocolli:
//                        $utiEnte = new utiEnte();
//                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
//                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
//                        $itaJR = new itaJasperReport();
//                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
//                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proElencoProtocolliDaFirmPortlet', $parameters);
                        break;
                }
                break;
            case 'onClickTablePager':
                $this->caricaDatiGriglia();
                $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella, '2');
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        App::$utente->removeKey($this->nameForm . '_utente');
        App::$utente->removeKey($this->nameForm . '_tabella');
        $this->close = true;
        if ($close)
            $this->close();
        Out::show($this->modelChiamante);
    }

    private function CaricaGrigliaGenerica($griglia, $appoggio, $tipo = '1', $pageRows = 20, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
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

    private function caricaDatiGriglia() {
        $sql = $this->creaSql();
        $anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        $this->tabella = array();
        foreach ($anapro_tab as $anapro_rec) {
            $anapro_rec['ANNO'] = substr($anapro_rec['PRONUM'], 0, 4);
            $anapro_rec['CODICE'] = intval(substr($anapro_rec['PRONUM'], 4));
            $prodar = $anapro_rec['PRODAR'];
            if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100") {
                $ini_tag = "<p style = 'background-color:yellow;'>";
                $fin_tag = "</p>";
                $anapro_rec['PROPAR'] = $ini_tag . $anapro_rec['PROPAR'] . $fin_tag;
                $anapro_rec['ANNO'] = $ini_tag . $anapro_rec['ANNO'] . $fin_tag;
                $anapro_rec['CODICE'] = $ini_tag . $anapro_rec['CODICE'] . $fin_tag;
                $anapro_rec['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($prodar)) . $fin_tag;
                $anapro_rec['PRONOM'] = $ini_tag . $anapro_rec['PRONOM'] . $fin_tag;
                $anapro_rec['OGGOGG'] = $ini_tag . $anapro_rec['OGGOGG'] . $fin_tag;
                $anapro_rec['PROLRIS'] = $ini_tag . $anapro_rec['PROLRIS'] . $fin_tag;
            }
            if ($anapro_rec['PRORISERVA']) {
                $ini_tag = "<p style = 'color:white;background-color:gray;'>";
                $fin_tag = "</p>";
                $anapro_rec['PROPAR'] = $ini_tag . $anapro_rec['PROPAR'] . $fin_tag;
                $anapro_rec['ANNO'] = $ini_tag . $anapro_rec['ANNO'] . $fin_tag;
                $anapro_rec['CODICE'] = $ini_tag . $anapro_rec['CODICE'] . $fin_tag;
                $anapro_rec['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($prodar)) . $fin_tag;
                $anapro_rec['PROLRIS'] = $ini_tag . $anapro_rec['PROLRIS'] . $fin_tag;
                $anapro_rec['PRONOM'] = $ini_tag . "RISERVATO" . $fin_tag;
                $anapro_rec['OGGOGG'] = $ini_tag . "RISERVATO" . $fin_tag;
            }

            $this->tabella[] = $anapro_rec;
        }
    }

    private function creaSql() {
        $sql = $this->proLib->getSqlRegistro();
        //
        // Collego tabelle secondarie
        //
        //
        // Mittenti
        //
        $sql.=" LEFT OUTER JOIN ANANOM ANANOM ON ANAPRO.PRONUM=ANANOM.NOMNUM AND ANAPRO.PROPAR=ANANOM.NOMPAR";
        //
        // Destinatari
        //
        $sql.=" LEFT OUTER JOIN ANADES ANADES ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";
        //
        // Prime assegnazioni su Arcite
        //
        $sql.=" LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR";

        $sql .= " LEFT OUTER JOIN ANADOC ANADOC ON ANAPRO.PRONUM = ".$this->PROT_DB->subString('ANADOC.DOCKEY',1,10)." AND ANAPRO.PROPAR = ".$this->PROT_DB->subString('ANADOC.DOCKEY',11,1);

        $sql .= " WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C') AND DOCDAFIRM=1 ";

        if ($_POST['_search'] == true) {
            if ($_POST['PROPAR']) {
                $sql .= " AND ".$this->PROT_DB->strUpper('PROPAR')." = '" . addslashes(strtoupper($_POST['PROPAR'])) . "'";
            }
            if ($_POST['ANNO']) {
                $anno = $_POST['ANNO'] + 1;
                $sql .= " AND ANAPRO.PRONUM >= " . $_POST['ANNO'] . "000000 AND ANAPRO.PRONUM <= " . $anno . "000000";
            }
            if ($_POST['ANNO'] && $_POST['CODICE']) {
                $sql .= " AND ANAPRO.PRONUM =" . $_POST['ANNO'] . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT);
            } else if ($_POST['CODICE']) {
                $sql .= " AND ANAPRO.PRONUM =" . date('Y') . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT);
            }
            if ($_POST['PRODAR']) {
                if (strlen($_POST['PRODAR']) == 8) {
                    $data = substr($_POST['PRODAR'], 4) . substr($_POST['PRODAR'], 2, 2) . substr($_POST['PRODAR'], 0, 2);
                } else if (strlen($_POST['PRODAR']) == 10) {
                    $data = substr($_POST['PRODAR'], 6) . substr($_POST['PRODAR'], 3, 2) . substr($_POST['PRODAR'], 0, 2);
                }
                if ($data) {
                    $sql .= " AND PRODAR= '" . $data . "'";
                }
            }
            if ($_POST['PRONOM']) {
                $sql .= " AND ".$this->PROT_DB->strUpper('PRONOM')." LIKE '%" . addslashes(strtoupper($_POST['PRONOM'])) . "%'";
            }
            if ($_POST['OGGOGG']) {
                $sql .= " AND ".$this->PROT_DB->strUpper('OGGOGG')." LIKE '%" . addslashes(strtoupper($_POST['OGGOGG'])) . "%'";
            }
            if ($_POST['PROLRIS']) {
                $sql .= " AND PROLRIS LIKE '" . $_POST['PROLRIS'] . "'";
            }
        }


        $sql .= " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);
//        App::log($sql);
        return $sql;
    }

}

?>