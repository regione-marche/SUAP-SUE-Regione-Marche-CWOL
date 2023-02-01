<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proRegistro() {
    $proRegistro = new proRegistro();
    $proRegistro->parseEvent();
    return;
}

class proRegistro extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proRegistro";
    public $divRis = "proRegistro_divRisultato";
    public $divRic = "proRegistro_divRicerca";
    public $gridRisultato = "proRegistro_gridRisultato";
    public $workDate;
    public $workYear;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($data));
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'onClickTablePager':
                $ordinamento = $_POST['sidx'];
                switch ($ordinamento) {
                    case 'CODICE':
                        $ordinamento = 'PRONUM';
                        break;
                    default:
                        break;
                }
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $ita_grid01->getDataArray();
                $result_tab = $this->elaboraRecords($result_tab);
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . " ORDER BY ANAPRO.PRONUM,ANAPRO.PROPAR ",
                    "Titolo" => "REGISTRO PROTOCOLLI",
                    "Ente" => $Anaent_rec['ENTDE1']
                );
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proRegistro', $parameters);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridRisultato, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridRisultato]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('PRONUM');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $ita_grid01->getDataArray();
                        $this->risultato = $result_tab;
                        $result_tab = $this->elaboraRecords($result_tab);
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_Pagina');
                            Out::show($this->nameForm . '_Pagina_lbl');
                            Out::valore($this->nameForm . '_Pagina', 1);
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Stampa');
                            TableView::enableEvents($this->gridRisultato);
                        }
                        break;
                    case $this->nameForm . '_Stampa':
                        $Anaent_rec = $this->proLib->GetAnaent('2');
                        if ($_POST[$this->nameForm . '_Pagina'] == 0)
                            $_POST[$this->nameForm . '_Pagina'] = 1;
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql() . " ORDER BY ANAPRO.PRONUM,ANAPRO.PROPAR ",
                            "Titolo" => "REGISTRO PROTOCOLLI",
                            "Ente" => $Anaent_rec['ENTDE1']
                        );
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proRegistro', $parameters);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dal_prot':
                        $codice = $_POST[$this->nameForm . '_Dal_prot'];
                        if ($codice != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_Dal_prot', $codice);
                        } else {
                            Out::valore($this->nameForm . '_Dal_prot', "");
                        }
                        break;
                    case $this->nameForm . '_Al_prot':
                        $codice = $_POST[$this->nameForm . '_Al_prot'];
                        if ($codice != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_Al_prot', $codice);
                        } else {
                            Out::valore($this->nameForm . '_Al_prot', "");
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        $this->Nascondi();
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        $this->AzzeraVariabili();
        Out::show($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Pagina');
        Out::hide($this->nameForm . '_Pagina_lbl');
        Out::valore($this->nameForm . '_Anno', $this->workYear);
        Out::show($this->nameForm);
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::disableEvents($this->gridRisultato);
        TableView::clearGrid($this->gridRisultato);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Stampa');
    }

    public function CreaSql() {
        $Anno = $_POST[$this->nameForm . '_Anno'];
        $daData = $_POST[$this->nameForm . '_Dal_periodo'];
        $aData = $_POST[$this->nameForm . '_Al_periodo'];
        $daAtto = $_POST[$this->nameForm . '_Dal_prot'];
        $aAtto = $_POST[$this->nameForm . '_Al_prot'];

        $sql = $this->proLib->getSqlRegistro();

        if ($altri == 0) {
            $sql.=" LEFT OUTER JOIN ANANOM ANANOM ON ANAPRO.PRONUM=ANANOM.NOMNUM AND ANAPRO.PROPAR=ANANOM.NOMPAR";
        } else {
            $sql.=" LEFT OUTER JOIN ANADES ANADES ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";
        }
        $sql.=" WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C') ";

        if ($aData == '' && $daData == '' && $daAtto == '' && $aAtto == '') {
            $Dap = $Anno . "0101";
            $Alp = $Anno . "1231";
            $Dpr = $Anno . "000001";
            $Apr = $Anno . "999999";
            $sql .=" AND (PRODAR BETWEEN '$Dap' AND '$Alp') AND (ANAPRO.PRONUM BETWEEN $Dpr AND $Apr)";
        }

        if ($aData != '') {
            if ($aData == '')
                $aData = $daData;
            $sql .= " AND (PRODAR BETWEEN '$daData' AND '$aData')";
        }

        if ($daAtto != "") {
            if ($aAtto == '')
                $aAtto = $daAtto;
            $Dpr = $Anno * 1000000 + $daAtto;
            $Apr = $Anno * 1000000 + $aAtto;
            $Dap = $Anno . "0101";
            $Alp = $Anno . "1231";
            $sql .= " AND (ANAPRO.PRONUM BETWEEN $Dpr AND $Apr) AND (PRODAR BETWEEN '$Dap' AND '$Alp')";
        }
        App::log($sql);
        return $sql;
    }

    function elaboraRecords($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            $result_tab[$key]['CODICE'] = substr($result_rec['PRONUM'], 4, 6);
            $ini_tag = $fin_tag = '';

//            if (substr($result_rec['PROPAR'], 1, 1) == 'A') {
            if ($result_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
                $fin_tag = "</p>";
            }

            $result_tab[$key]['OGGOGG'] = $ini_tag . $result_tab[$key]['OGGOGG'] . $fin_tag;
            $result_tab[$key]['PRONOM'] = $ini_tag . $result_tab[$key]['PRONOM'] . $fin_tag;
            $result_tab[$key]['PRODAS'] = $ini_tag . $result_tab[$key]['PRODAS'] . $fin_tag;
            $result_tab[$key]['PROPRE'] = $ini_tag . $result_tab[$key]['PROPRE'] . $fin_tag;
            $result_tab[$key]['PROORA'] = $ini_tag . $result_tab[$key]['PROORA'] . $fin_tag;
            $result_tab[$key]['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($result_tab[$key]['PRODAR'])) . $fin_tag;

            $visibile = 0;

            if ($this->proLib->checkRiservatezzaProtocollo($result_rec)) {
                $visibile = 1;
            }
            if ($visibile == 1 || $visibile == 2) {
                $result_tab[$key]['OGGOGG'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div>$star</div>";
                $result_tab[$key]['PRONOM'] = "<div style = \"padding-left:2px;height:90%;margin:1px;background-color:lightgrey;\"><div style=\"float:left;display:inline-block;background-color:lightgrey;color:black;\">RISERVATO</div>$star</div>";
            }
            $result_tab[$key]['CODICE'] = $ini_tag . $result_tab[$key]['CODICE'] . $fin_tag;
            $result_tab[$key]['PROPAR'] = $ini_tag . $result_tab[$key]['PROPAR'] . $fin_tag;
        }
        return $result_tab;
    }

}

?>
