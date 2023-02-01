<?php

/**
 *
 * GESTIONE TRASMISSIONI RIFIUTATE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    26.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once (ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php');

function proElencoAssegnaTrasmissioni() {
    $proElencoAssegnaTrasmissioni = new proElencoAssegnaTrasmissioni();
    $proElencoAssegnaTrasmissioni->parseEvent();
    return;
}

class proElencoAssegnaTrasmissioni extends itaModel {

    public $nameForm = "proElencoAssegnaTrasmissioni";
    public $PROT_DB;
    public $ITW_DB;
    public $workDate;
    public $proLib;
    public $accLib;
    public $gridRisultato = "proElencoAssegnaTrasmissioni_gridRisultato";
    public $tabella;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->accLib = new accLib();
        // Apro il DB
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITW_DB = $this->accLib->getITW();
        $this->workDate = date('Ymd');
        $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tabella', $this->tabella);
        }
    }

    public function parseEvent() {
//        App::log($_POST);
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm('proElencoAssegnaTrasmissioni', '', true, $container = $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");
                $this->elencaRisultato(true);
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridRisultato:
                        $rowId = $_POST['rowid'];
                        $model = 'proElencoTrasmissioni';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowidTrasmissione'] = $rowId;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridRisultato:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proElencoAssegnaTrasmissioni', $parameters);
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRisultato:
                        if ($this->tabella != false) {
                            $ita_grid01 = new TableView($this->gridRisultato, array('arrayTable' => $this->tabella, 'rowIndex' => 'idx'));
                            $ita_grid01->setPageNum($_POST['page']);
                            $ita_grid01->setPageRows(20);
                            $ita_grid01->clearGrid($this->gridRisultato);
                            $ita_grid01->getDataPage('json');
                        }
                        break;
                }
                break;
            case 'onChange': // Evento onChange
                switch ($_POST['id']) {
                    case $this->nameForm . '_Rifiutati':
                    case $this->nameForm . '_Scaduti':
                        $this->caricaRisultatiGriglia();
                        $this->caricaGriglia($this->gridRisultato, $this->tabella);
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_tabella');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function elencaRisultato($tutti = false) {
        $this->caricaRisultatiGriglia($tutti);
        Out::valore($this->nameForm . '_Rifiutati', 1);
        Out::valore($this->nameForm . '_Scaduti', 1);
        $this->caricaGriglia($this->gridRisultato, $this->tabella, '1', 20);
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
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

    function caricaRisultatiGriglia($tutti) {
        $sql = "SELECT * FROM ARCITE WHERE ITEPRE='' ";

        if ($_POST[$this->nameForm . '_Rifiutati'] == 1 && $_POST[$this->nameForm . '_Scaduti'] == 1 || $tutti) {
            $sql.=" AND (ITESTATO=1 OR ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate')";
        } else if ($_POST[$this->nameForm . '_Rifiutati'] == 1 || $_POST[$this->nameForm . '_Scaduti'] == 1 || $tutti) {
            if ($_POST[$this->nameForm . '_Rifiutati'] == 1) {
                $sql.=" AND ITESTATO=1";
            }
            if ($_POST[$this->nameForm . '_Scaduti'] == 1) {
                $sql.=" AND ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate'";
            }
        } else {
            $sql.=" AND (ITESTATO=1 OR ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate')";
        }

//        if ($_POST[$this->nameForm . '_visualizza'] == '') {
//            $sql.=" AND (ITESTATO=1 OR ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate')";
//        } else if ($_POST[$this->nameForm . '_visualizza'] == 'R') {
//            $sql.=" AND ITESTATO=1";
//        } else {
//            $sql.=" AND ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate'";
//        }
//        if ($_POST[$this->nameForm . '_dalPeriodo'] <> '') {
//            $sql.=" AND ITEDAT>='" . $_POST[$this->nameForm . '_dalPeriodo'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_alPeriodo'] <> '') {
//            $sql.=" AND ITEDAT<='" . $_POST[$this->nameForm . '_alPeriodo'] . "'";
//        }
//        if ($_POST[$this->nameForm . '_tipoTrasm'] <> '') {
//            $sql.=" AND ITEPAR='" . $_POST[$this->nameForm . '_tipoTrasm'] . "'";
//        }

        $sql .= " GROUP BY ITEPRO,ITEPAR ORDER BY ITEEVIDENZA DESC, ITEDAT DESC, ITEPRO DESC";
//        App::log($sql);
        $arcite_tab = $this->proLib->getGenericTab($sql);
        $this->tabella = array();
        foreach ($arcite_tab as $arcite_rec) {
            $record = array();
            $codice = $arcite_rec['ITEPRO'];
            $record['ROWID'] = $arcite_rec['ROWID'];
            $record['ITEPAR'] = $arcite_rec['ITEPAR'];
            $record['ANNO'] = substr($codice, 0, 4);
            $record['NUMERO'] = intval(substr($codice, 4));
            $record['ITEDAT'] = $arcite_rec['ITEDAT'];
            $anaogg_rec = $this->proLib->GetAnaogg($codice, $arcite_rec['ITEPAR']);
            $record['OGGETTO'] = $anaogg_rec['OGGOGG'];
            $anapro_rec = $this->proLib->GetAnapro($codice, 'codice', $arcite_rec['ITEPAR']);
            $record['PROVENIENZA'] = $anapro_rec['PRONOM'];
            $record['ITEMOTIVO'] = $arcite_rec['ITEMOTIVO'];
            $record['ITETERMINE'] = $arcite_rec['ITETERMINE'];
            $ini_tag = "<p style = 'font-weight:lighter;'>";
            $fin_tag = "</p>";
            if ($arcite_rec['ITEEVIDENZA'] == 1) {
                $ini_tag = "<p style = 'font-weight:900;color:#BE0000;'>";
                $fin_tag = "</p>";
            }
            $record['ITEPAR'] = $ini_tag . $record['ITEPAR'] . $fin_tag;
            $record['ANNO'] = $ini_tag . $record['ANNO'] . $fin_tag;
            $record['NUMERO'] = $ini_tag . $record['NUMERO'] . $fin_tag;
            $record['ITEDAT'] = $ini_tag . date('d/m/Y', strtotime($record['ITEDAT'])) . $fin_tag;
            $record['OGGETTO'] = $ini_tag . $record['OGGETTO'] . $fin_tag;
            $record['PROVENIENZA'] = $ini_tag . $record['PROVENIENZA'] . $fin_tag;
            $record['ITEMOTIVO'] = $ini_tag . $record['ITEMOTIVO'] . $fin_tag;
            if ($record['ITETERMINE']) {
                $record['ITETERMINE'] = substr($record['ITETERMINE'], 6) . '/' . substr($record['ITETERMINE'], 4, 2) . '/' . substr($record['ITETERMINE'], 0, 4);
            }
            $record['ITETERMINE'] = $ini_tag . $record['ITETERMINE'] . $fin_tag;
            $this->tabella[] = $record;
        }
    }

    function creaSql() {
        $sql = "SELECT *,
                ".$this->PROT_DB->subString('ITEPRO',1,4)." AS ANNO,
                ".$this->PROT_DB->subString('ITEPRO',5,6)." AS NUMERO,
                ANAOGG.OGGOGG AS OGGETTO,
                ANAPRO.PRONOM AS PROVENIENZA
                FROM ARCITE 
                JOIN ANAOGG ON ARCITE.ITEPRO=ANAOGG.OGGNUM
                AND ARCITE.ITEPAR=ANAOGG.OGGPAR
                LEFT OUTER JOIN ANAPRO ON ARCITE.ITEPRO=ANAPRO.PRONUM
                AND ARCITE.ITEPAR=ANAPRO.PROPAR
                WHERE ITEPRE='' ";
        if ($_POST[$this->nameForm . '_Rifiutati'] == 1 && $_POST[$this->nameForm . '_Scaduti'] == 1 || $tutti) {
            $sql.=" AND (ITESTATO=1 OR ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate')";
        } else if ($_POST[$this->nameForm . '_Rifiutati'] == 1 || $_POST[$this->nameForm . '_Scaduti'] == 1 || $tutti) {
            if ($_POST[$this->nameForm . '_Rifiutati'] == 1) {
                $sql.=" AND ITESTATO=1";
            }
            if ($_POST[$this->nameForm . '_Scaduti'] == 1) {
                $sql.=" AND ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate'";
            }
        } else {
            $sql.=" AND (ITESTATO=1 OR ITESTATO=0 AND ITETERMINE<>'' AND ITETERMINE<'$this->workDate')";
        }

        $sql .= " GROUP BY ITEPRO,ITEPAR ORDER BY ITEEVIDENZA DESC, ITEDAT DESC, ITEPRO DESC";
//        App::log($sql);
        return $sql;
    }
    
    

}

?>