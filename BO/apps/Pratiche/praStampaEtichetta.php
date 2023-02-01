<?php

/**
 *
 * SELEZIONA AREA DI STAMPA
 *
 * PHP Version 5
 *
 * @category
 * @package    Seleziona area di stampa
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    25/03/2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
function praStampaEtichetta() {
    $praStampaEtichetta = new praStampaEtichetta();
    $praStampaEtichetta->parseEvent();
    return;
}

class praStampaEtichetta extends itaModel {

    public $nameForm = "praStampaEtichetta";
    public $gridStampa;

    function __construct() {
        parent::__construct();
        $this->gridStampa = App::$utente->getKey($this->nameForm . '_gridStampa');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gridStampa', $this->gridStampa);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::valore($this->nameForm . '_PROGES[ROWID]', $_POST['chiave']);
//                $spazio='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $messaggio = '<span style="font-weight:bold;color:red;font-size:1.6em;"> Seleziona l\'area di stampa dell\'etichetta</span><br>';
                Out::html($this->nameForm . '_boxMsg', $messaggio);
                Out::hide($this->nameForm . '_divGriglia2x6');
                Out::hide($this->nameForm . '_divGriglia3x8');
                Out::hide($this->nameForm . '_divGriglia3x10');
                $elencoRecord = array();
                switch ($_POST['tipo']) {
                    case '2':
                        $this->gridStampa = $this->nameForm . '_grid2x6';
                        Out::show($this->nameForm . '_divGriglia2x6');
                        $record = array('COLONNA1' => '<br><br><br><br><br>', 'COLONNA2' => '<br><br><br><br><br>');
                        $elencoRecord = array($record, $record, $record, $record, $record, $record);
                        break;
                    case '3':
                        $this->gridStampa = $this->nameForm . '_grid3x8';
                        Out::show($this->nameForm . '_divGriglia3x8');
                        $record = array('COLONNA1' => '<br><br><br><br>', 'COLONNA2' => '<br><br><br><br>', 'COLONNA3' => '<br><br><br><br>');
                        $elencoRecord = array($record, $record, $record, $record, $record, $record, $record, $record);
                        break;
                    case '4':
                        $this->gridStampa = $this->nameForm . '_grid3x10';
                        Out::show($this->nameForm . '_divGriglia3x10');
                        $record = array('COLONNA1' => '<br><br><br>', 'COLONNA2' => '<br><br><br>', 'COLONNA3' => '<br><br><br>');
                        $elencoRecord = array($record, $record, $record, $record, $record, $record, $record, $record, $record, $record);
                        break;
                }
                $this->CaricaGrigliaGenerica($elencoRecord);
                break;
            case 'cellSelect':
                $riga = $_POST['rowid'];
                $colonna = $_POST['iCol'];
                $sql = "SELECT * FROM PROGES WHERE ROWID=" . $_POST[$this->nameForm . '_PROGES']['ROWID'];
                include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
                $praLib = new praLib();
                $PRAM_DB = $praLib->getPRAMDB();
                $proges_rec = $praLib->GetProges($_POST[$this->nameForm . '_PROGES']['ROWID'], 'rowid');
                $deserializzato = unserialize($proges_rec['GESMETA']);
                $data = substr($proges_rec['GESNPR'], 0, 4);
                if ($deserializzato['DatiProtocollazione']['Data']['value']) {
                    $data = date('d/m/Y', strtotime($deserializzato['DatiProtocollazione']['Data']['value']));
                }
                $segnatura = $deserializzato['DatiProtocollazione']['Segnatura']['value'];
                include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
                $utiEnte = new utiEnte();
                $parametriEnte_rec = $utiEnte->GetParametriEnte();
                $report = 'praStampa3x10';
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $sql,
                    "Ente" => $parametriEnte_rec['DENOMINAZIONE'],
                    "DataProt" => $data,
                    "Segnatura" => $segnatura,
                    "Riga" => $riga,
                    "Colonna" => $colonna);
                $itaJR->runSQLReportPDF($PRAM_DB, $report, $parameters);
//                $this->returnToParent();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
//        Out::setFocus('', $focus);
    }

    public function returnToParent($focus = '') {
        $this->close($focus);
        App::$utente->removeKey($this->nameForm . '_gridStampa');
        $this->close = true;
    }

    function carica2x6() {
        
    }

    function CaricaGrigliaGenerica($elementi) {
        $ita_grid01 = new TableView(
                $this->gridStampa, array('arrayTable' => $elementi,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(100000);
        TableView::enableEvents($this->gridStampa);
        TableView::clearGrid($this->gridStampa);
        $ita_grid01->getDataPage('json');
        return;
    }

}

?>
