<?php

/**
 *
 * GESTIONE TARIFFE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    17.03.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praTariffe() {
    $praTariffe = new praTariffe();
    $praTariffe->parseEvent();
    return;
}

class praTariffe extends itaModel {

    public $praLib;
    public $utiEnte;
    public $PRAM_DB;
    public $nameForm = "praTariffe";
    public $divRis = "praTariffe_divRisultato";
    public $divRic = "praTariffe_divRicerca";
    public $gridTariffe = "praTariffe_gridTariffe";
    public $gridListini = "praTariffe_gridListini";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->utiEnte = new utiEnte();
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
                TableView::hideCol($this->gridTariffe, 'ITEKEY');
                TableView::hideCol($this->gridTariffe, 'DESCITEKEY');

                $this->openRisultato();
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridTariffe:
                        switch ($_POST['colName']) {
                            case 'ATTIVO':
                                $itelis_rec = $this->praLib->GetItelis($_POST['rowid'], 'rowid');
                                $itelis_rec['ATTIVO'] = ($itelis_rec['ATTIVO'] == 0 ? 1 : 0);

                                $update_info = 'Oggetto: ' . $itelis_rec['ROWID'] . " " . $itelis_rec['DESCRIZIONE'];

                                if (!$this->updateRecord($this->PRAM_DB, 'ITELIS', $itelis_rec, $update_info)) {
                                    break;
                                }

                                TableView::reload($this->gridTariffe);
                                break;
                        }
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridListini:
                        $this->openGestione();
                        break;

                    case $this->gridTariffe:
                        $model = "praTariffeGest";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setNumeroListino($_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridListini:
                        $itelisval_rec = $this->openGestione($_POST['rowid']);

                        if (!$this->verificaTariffe($itelisval_rec['CODLISVAL'])) {
                            Out::msgStop("Errore", "Sono presenti delle tariffe, impossibile cancellare");
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                        ));
                        break;

                    case $this->gridTariffe:
                        $model = "praTariffeGest";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->delete = true;
                        $formObj->setNumeroListino($_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']);
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridListini:
                        $ita_grid01 = new TableView(
                                $this->gridListini, array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->getSqlListini())
                        );
                        $ita_grid01->setSortIndex('INILISVAL');
                        $ita_grid01->exportXLS('', 'praTariffe.xls');
                        break;

                    case $this->gridTariffe:
                        $ita_grid01 = new TableView(
                                $this->gridTariffe, array('sqlDB' => $this->PRAM_DB, 'sqlQuery' => $this->getSql($_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']))
                        );
                        $ita_grid01->setSortIndex('SEQUENZA');
                        $ita_grid01->exportXLS('', 'praTariffe.xls');
                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridTariffe:
                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];
                        $sql = $this->getSql($itelisval_rec['CODLISVAL']) . " ORDER BY SEQUENZA ASC";
                        $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Sql" => $sql,
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                            'Descrizione' => $itelisval_rec['DESCLISVAL'],
                            'Da' => $itelisval_rec['INILISVAL'],
                            'Ad' => $itelisval_rec['FINLISVAL'] ? $itelisval_rec['FINLISVAL'] : ''
                        );
                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praTariffe', $parameters);
//                        $btnStyle = 'width: 100%; padding: 4px 0;';
//                        Out::msgQuestion("Stampa", "Selezionare l'ordinamento della stampa", array(
//                            'per Sequenza' => array('id' => $this->nameForm . '_StampaSequenza', 'model' => $this->nameForm, 'style' => $btnStyle),
//                            'per Settore' => array('id' => $this->nameForm . '_StampaSettore', 'model' => $this->nameForm, 'style' => $btnStyle),
//                            'per Attività' => array('id' => $this->nameForm . '_StampaAttivita', 'model' => $this->nameForm, 'style' => $btnStyle),
//                            'per Sportello' => array('id' => $this->nameForm . '_StampaSportello', 'model' => $this->nameForm, 'style' => $btnStyle),
//                            'per Procedimento' => array('id' => $this->nameForm . '_StampaProcedimento', 'model' => $this->nameForm, 'style' => $btnStyle),
//                            'per Evento' => array('id' => $this->nameForm . '_StampaEvento', 'model' => $this->nameForm, 'style' => $btnStyle)
//                                ), 'auto', 'auto', 'true', false, true, true);
                        break;
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridListini:
                        $this->openGestione($_POST['rowid']);
                        break;

                    case $this->gridTariffe:
                        $model = "praTariffeGest";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setNumeroListino($_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']);
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridListini:
                        TableView::clearGrid($_POST['id']);

                        $sql = $this->getSqlListini();

                        $gridScheda = new TableView($_POST['id'], array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPage('json');
                        break;

                    case $this->gridTariffe:
                        TableView::clearGrid($_POST['id']);

                        $sql = $this->getSql($_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']);

                        if ($_POST['_search'] == true) {
                            if ($_POST['DESCRIZIONE'] !== '') {
                                $sql .= " AND DESCRIZIONE LIKE '%" . addslashes($_POST['DESCRIZIONE']) . "%'";
                            }

                            if ($_POST['IMPORTO'] !== '') {
                                $sql .= " AND IMPORTO LIKE '%" . addslashes(floatval($_POST['IMPORTO'])) . "%'";
                            }

                            if ($_POST['TIPOIMPO'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ANATIPIMPO.DESCTIPOIMPO") . " LIKE '%" . strtoupper(addslashes($_POST['TIPOIMPO'])) . "%'";
                            }

                            if ($_POST['CODICESPORTELLO'] !== '') {
                                $sql .= " AND CODICESPORTELLO LIKE '%" . addslashes($_POST['CODICESPORTELLO']) . "%'";
                            }

                            if ($_POST['SPORTELLO'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ANATSP.TSPDES") . " LIKE '%" . strtoupper(addslashes($_POST['SPORTELLO'])) . "%'";
                            }

                            if ($_POST['SETTORE'] !== '') {
                                $sql .= " AND SETTORE LIKE '%" . addslashes($_POST['SETTORE']) . "%'";
                            }

                            if ($_POST['DESCSETTORE'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ANASET.SETDES") . " LIKE '%" . strtoupper(addslashes($_POST['DESCSETTORE'])) . "%'";
                            }

                            if ($_POST['ATTIVITA'] !== '') {
                                $sql .= " AND ATTIVITA LIKE '%" . addslashes($_POST['ATTIVITA']) . "%'";
                            }

                            if ($_POST['DESCATTIVITA'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ANAATT.ATTDES") . " LIKE '%" . strtoupper(addslashes($_POST['DESCATTIVITA'])) . "%'";
                            }

                            if ($_POST['PROCEDIMENTO'] !== '') {
                                $sql .= " AND PROCEDIMENTO LIKE '%" . addslashes($_POST['PROCEDIMENTO']) . "%'";
                            }

                            if ($_POST['DESCPROCEDIMENTO'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper($this->PRAM_DB->strConcat('ANAPRA.PRADES__1', 'ANAPRA.PRADES__2', 'ANAPRA.PRADES__3', 'ANAPRA.PRADES__4')) . " LIKE '%" . strtoupper(addslashes($_POST['DESCPROCEDIMENTO'])) . "%'";
                            }

                            if ($_POST['EVENTO'] !== '') {
                                $sql .= " AND EVENTO LIKE '%" . addslashes($_POST['EVENTO']) . "%'";
                            }

                            if ($_POST['DESCEVENTO'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ANAEVENTI.EVTDESCR") . " LIKE '%" . strtoupper(addslashes($_POST['DESCEVENTO'])) . "%'";
                            }

                            if ($_POST['TIPOPASSO'] !== '') {
                                $sql .= " AND TIPOPASSO LIKE '%" . addslashes($_POST['TIPOPASSO']) . "%'";
                            }

                            if ($_POST['DESCTIPOPASSO'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("PRACLT.CLTDES") . " LIKE '%" . strtoupper(addslashes($_POST['DESCTIPOPASSO'])) . "%'";
                            }

                            if ($_POST['ITEKEY'] !== '') {
                                $sql .= " AND ITEKEY LIKE '%" . addslashes($_POST['ITEKEY']) . "%'";
                            }

                            if ($_POST['DESCITEKEY'] !== '') {
                                $sql .= " AND " . $this->PRAM_DB->strUpper("ITEPAS.ITEDES") . " LIKE '%" . strtoupper(addslashes($_POST['DESCITEKEY'])) . "%'";
                            }
                        }

                        $gridScheda = new TableView($_POST['id'], array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPageFromArray('json', $this->elaboraGridTarrife($gridScheda->getDataArray()));
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_NuovoListino':
                        $this->openGestione();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];
                        $itelisval_rec['CODLISVAL'] = $this->getProgressivo();

                        if (!$this->verificaValidita($itelisval_rec)) {
                            Out::msgStop("Errore", "Periodo non valido");
                            break;
                        }

                        $insert_info = 'Oggetto: ' . $itelisval_rec['DESCLISVAL'];

                        if (!$this->insertRecord($this->PRAM_DB, 'ITELISVAL', $itelisval_rec, $insert_info)) {
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];

                        if (!$this->verificaValidita($itelisval_rec)) {
                            Out::msgStop("Errore", "Periodo non valido");
                            break;
                        }

                        $update_info = 'Oggetto: ' . $itelisval_rec['ROWID'] . " " . $itelisval_rec['DESCLISVAL'];

                        if (!$this->updateRecord($this->PRAM_DB, 'ITELISVAL', $itelisval_rec, $update_info)) {
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Cancella':
                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];

                        if (!$this->verificaTariffe($itelisval_rec['CODLISVAL'])) {
                            Out::msgStop("Errore", "Sono presenti delle tariffe, impossibile cancellare");
                            break;
                        }

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                        ));
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];

                        if (!$this->verificaTariffe($itelisval_rec['CODLISVAL'])) {
                            Out::msgStop("Errore", "Sono presenti delle tariffe, impossibile cancellare");
                            break;
                        }

                        $delete_info = 'Oggetto: ' . $itelisval_rec['ROWID'] . " " . $itelisval_rec['DESCLISVAL'];

                        if (!$this->deleteRecord($this->PRAM_DB, 'ITELISVAL', $itelisval_rec['ROWID'], $delete_info)) {
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_ElencaListini':
                        $this->openRisultato();
                        break;

//                    case $this->nameForm . '_RiapriListino':
//                        /*
//                         * Controllo aperture successive
//                         */
//
//                        $sql = "SELECT
//                                    ROWID
//                                FROM
//                                    ITELISVAL
//                                WHERE
//                                    INILISVAL > {$_POST[$this->nameForm . '_ITELISVAL']['FINLISVAL']}";
//
//                        if (ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false)) {
//                            Out::msgStop("Errore", "Ci sono aperture successive a questo listino, impossibile riaprire.");
//                            return false;
//                        }
//
//                        $itelisval_rec = array(
//                            'ROWID' => $_POST[$this->nameForm . '_ITELISVAL']['ROWID'],
//                            'FINLISVAL' => ''
//                        );
//
//                        $update_info = 'Oggetto: Riapertura ' . $itelisval_rec['ROWID'];
//
//                        if (!$this->updateRecord($this->PRAM_DB, 'ITELISVAL', $itelisval_rec, $update_info)) {
//                            break;
//                        }
//
//                        $this->openGestione($itelisval_rec['ROWID']);
//                        break;

                    case $this->nameForm . '_ImportaListino':
                        $this->ricListino($this->nameForm, $_POST[$this->nameForm . '_ITELISVAL']['CODLISVAL']);
                        break;

//                    case $this->nameForm . '_StampaSequenza':
//                        if (!isset($order)) {
//                            $order = 'SEQUENZA';
//                        }
//                    case $this->nameForm . '_StampaSettore':
//                        if (!isset($order)) {
//                            $order = 'SETTORE';
//                        }
//                    case $this->nameForm . '_StampaAttivita':
//                        if (!isset($order)) {
//                            $order = 'ATTIVITA';
//                        }
//                    case $this->nameForm . '_StampaSportello':
//                        if (!isset($order)) {
//                            $order = 'SPORTELLO';
//                        }
//                    case $this->nameForm . '_StampaProcedimento':
//                        if (!isset($order)) {
//                            $order = 'PROCEDIMENTO';
//                        }
//                    case $this->nameForm . '_StampaEvento':
//                        if (!isset($order)) {
//                            $order = 'EVENTO';
//                        }
//
//                        $itelisval_rec = $_POST[$this->nameForm . '_ITELISVAL'];
//                        $sql = $this->getSql($itelisval_rec['CODLISVAL']) . " ORDER BY $order ASC";
//                        $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
//                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
//                        $itaJR = new itaJasperReport();
//                        $parameters = array("Sql" => $sql, "Ente" => $ParametriEnte_rec['DENOMINAZIONE'], 'Descrizione' => $itelisval_rec['DESCLISVAL']);
//                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praTariffe', $parameters);
//                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnListino':
                $sql = "SELECT
                            ITELISVAL.CODLISVAL
                        FROM
                            ITELISVAL
                        WHERE
                            ITELISVAL.ROWID = '{$_POST['retKey']}'";

                $itelisval_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                $sql = "SELECT
                            *
                        FROM
                            ITELIS
                        WHERE
                            CODVAL = {$itelisval_rec['CODLISVAL']}
                        ORDER BY
                            SEQUENZA ASC";

                $itelis_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

                $new_codval = $_POST['retid'];

                foreach ($itelis_tab as $itelis_rec) {
                    unset($itelis_rec['ROWID']);
                    $itelis_rec['CODVAL'] = $new_codval;

                    try {
                        ItaDB::DBInsert($this->PRAM_DB, 'ITELIS', 'ROWID', $itelis_rec);
                    } catch (Exception $e) {
                        Out::msgStop("Errore", $e->getMessage());
                    }
                }

                TableView::reload($this->gridTariffe);
                break;

            case 'retGestConciliazione':
                TableView::reload($this->gridTariffe);
                break;
        }
    }

    function elaboraGridTarrife($Result_tab) {
        $cssColor = 'LemonChiffon';
        $attivo = '<div style="margin: 0 auto; border-radius: 50px; background-color: green; width: 8px; height: 8px;">&nbsp;</div>';
        $disattivo = '<div style="margin: 0 auto; border-radius: 50px; background-color: red; width: 8px; height: 8px;">&nbsp;</div>';

        if (!$Result_tab) {
            Out::show($this->nameForm . '_ImportaListino');
        } else {
            Out::hide($this->nameForm . '_ImportaListino');
        }

        foreach ($Result_tab as &$Result_rec) {
            $Result_rec['ATTIVO'] = ($Result_rec['ATTIVO'] == 0 ? $disattivo : $attivo);

            if ($Result_rec['CODICESPORTELLO'] == '0') {
                $Result_rec['SPORTELLO'] = 'Tutti';
            }
            $Result_rec['CODICESPORTELLO'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['CODICESPORTELLO'] . '</div>';

            if ($Result_rec['SETTORE'] == '0') {
                $Result_rec['DESCSETTORE'] = 'Tutti';
            }
            $Result_rec['SETTORE'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['SETTORE'] . '</div>';

            if ($Result_rec['ATTIVITA'] == '0') {
                $Result_rec['DESCATTIVITA'] = 'Tutti';
            }
            $Result_rec['ATTIVITA'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['ATTIVITA'] . '</div>';


            if ($Result_rec['PROCEDIMENTO'] == '*') {
                $Result_rec['DESCPROCEDIMENTO'] = 'Tutti';
            } elseif (!$Result_rec['PROCEDIMENTO']) {
                $Result_rec['PROCEDIMENTO'] = '&nbsp;';
            }
            $Result_rec['PROCEDIMENTO'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['PROCEDIMENTO'] . '</div>';


            if ($Result_rec['EVENTO'] == '*') {
                $Result_rec['DESCEVENTO'] = 'Tutti';
            } elseif (!$Result_rec['EVENTO']) {
                $Result_rec['EVENTO'] = '&nbsp;';
            }
            $Result_rec['EVENTO'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['EVENTO'] . '</div>';


            if ($Result_rec['TIPOPASSO'] == '*') {
                $Result_rec['DESCTIPOPASSO'] = 'Tutti';
            } elseif (!$Result_rec['TIPOPASSO']) {
                $Result_rec['TIPOPASSO'] = '&nbsp;';
            }
            $Result_rec['TIPOPASSO'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['TIPOPASSO'] . '</div>';


            if ($Result_rec['ITEKEY'] == '*') {
                $Result_rec['DESCITEKEY'] = 'Tutti';
            } elseif (!$Result_rec['ITEKEY']) {
                $Result_rec['ITEKEY'] = '&nbsp;';
            }

            if ($Result_rec['AGGREGATO'] == '0') {
                $Result_rec['AGGREGATO'] = 'Tutti';
            } else {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['AGGREGATO']);
                $Result_rec['AGGREGATO'] = $anaspa_rec['SPADES'];
            }
            $Result_rec['ITEKEY'] = '<div style="background-color: ' . $cssColor . ';">' . $Result_rec['ITEKEY'] . '</div>';
        }

        return $Result_tab;
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_NuovoListino');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_ChiudiListino');
        Out::hide($this->nameForm . '_RiapriListino');
        Out::hide($this->nameForm . '_ImportaListino');
        Out::hide($this->nameForm . '_ElencaListini');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openRicerca() {
        $this->mostraForm('divRicerca');
        $this->mostraButtonBar(array());

        TableView::disableEvents($this->gridListini);
        TableView::clearGrid($this->gridListini);

        TableView::disableEvents($this->gridTariffe);
        TableView::clearGrid($this->gridTariffe);
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('NuovoListino'));

        TableView::disableEvents($this->gridTariffe);
        TableView::clearGrid($this->gridTariffe);

        TableView::enableEvents($this->gridListini);
        TableView::reload($this->gridListini);
    }

    public function openGestione($rowid = false) {
        $this->mostraForm('divGestione');

        Out::clearFields($this->nameForm);

        TableView::disableEvents($this->gridListini);
        TableView::clearGrid($this->gridListini);

        if (!$rowid) {
            $this->mostraButtonBar(array('Aggiungi', 'ElencaListini'));

            Out::setFocus($this->nameForm, $this->nameForm . '_ITELISVAL[DESCLISVAL]');

            Out::valore($this->nameForm . '_ITELISVAL[INILISVAL]', date('Ymd'));

            Out::hide($this->nameForm . '_divTariffe');

            TableView::disableEvents($this->gridTariffe);
            TableView::clearGrid($this->gridTariffe);
        } else {
            $this->mostraButtonBar(array('Aggiorna', 'Cancella', 'ElencaListini'));

            Out::setFocus($this->nameForm, $this->nameForm . '_ITELISVAL[DESCLISVAL]');

            $itelisval_rec = $this->praLib->GetItelisval($rowid, 'rowid');
            Out::valori($itelisval_rec, $this->nameForm . '_ITELISVAL');

            Out::show($this->nameForm . '_divTariffe');

            TableView::enableEvents($this->gridTariffe);
            TableView::reload($this->gridTariffe);

            return $itelisval_rec;
        }
    }

    public function getSqlListini() {
        $sql = "SELECT
                    ITELISVAL.*,
                    (
                        SELECT
                            COUNT(*)
                        FROM
                            ITELIS
                        WHERE
                            ITELIS.CODVAL = ITELISVAL.CODLISVAL
                    ) AS NUMTARIFFE
                FROM
                    ITELISVAL";

        return $sql;
    }

    public function getSql($codval) {
        $sql = "SELECT
                    ITELIS.ROWID,
                    ITELIS.CODVAL,
                    ITELIS.SEQUENZA,
                    ITELIS.CODICETIPOIMPO,
                    ITELIS.CODICESPORTELLO,
                    ITELIS.TIPOPASSO,
                    ITELIS.SETTORE,
                    ITELIS.ATTIVITA,
                    ITELIS.PROCEDIMENTO,
                    ITELIS.ITEKEY,
                    ITELIS.EVENTO,
                    ITELIS.IMPORTO,
                    ITELIS.DESCRIZIONE,
                    ITELIS.ATTIVO,
                    ITELIS.AGGREGATO,
                    ANATIPIMPO.DESCTIPOIMPO AS TIPOIMPO,
                    ANATSP.TSPDES AS SPORTELLO,
                    PRACLT.CLTDES AS DESCTIPOPASSO,
                    ANASET.SETDES AS DESCSETTORE,
                    ANAATT.ATTDES AS DESCATTIVITA,
                    " . $this->PRAM_DB->strConcat('ANAPRA.PRADES__1', 'ANAPRA.PRADES__2', 'ANAPRA.PRADES__3', 'ANAPRA.PRADES__4') . " AS DESCPROCEDIMENTO,
                    ITEPAS.ITEDES AS DESCITEKEY,
                    ANAEVENTI.EVTDESCR AS DESCEVENTO
                FROM
                    ITELIS
                LEFT OUTER JOIN ANATIPIMPO ON ANATIPIMPO.CODTIPOIMPO = ITELIS.CODICETIPOIMPO
                LEFT OUTER JOIN ANATSP ON ANATSP.TSPCOD = ITELIS.CODICESPORTELLO
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD = ITELIS.TIPOPASSO
                LEFT OUTER JOIN ANASET ON ANASET.SETCOD = ITELIS.SETTORE
                LEFT OUTER JOIN ANAATT ON ANAATT.ATTCOD = ITELIS.ATTIVITA
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM = ITELIS.PROCEDIMENTO
                LEFT OUTER JOIN ITEPAS ON ITEPAS.ITEKEY = ITELIS.ITEKEY AND ITEPAS.ITEKEY != ''
                LEFT OUTER JOIN ANAEVENTI ON ANAEVENTI.EVTCOD = ITELIS.EVENTO
                WHERE
                    ITELIS.CODVAL = $codval";

        return $sql;
    }

    public function getProgressivo() {
        $sql = "SELECT MAX(CODLISVAL) AS PROG FROM ITELISVAL LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec['PROG'] ? intval($rec['PROG']) + 1 : 1;
    }

    public function verificaTariffe($codlisval) {
        $sql = "SELECT
                    ROWID
                FROM
                    ITELIS
                WHERE
                    CODVAL = {$codlisval}";

        return ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false) ? false : true;
    }

    public function verificaValidita($itelisval_rec) {
        if (!$itelisval_rec['FINLISVAL']) {
            $sql = "SELECT
                        ROWID
                    FROM
                        ITELISVAL
                    WHERE
                        CODLISVAL <> {$itelisval_rec['CODLISVAL']}
                    AND
                        (
                            FINLISVAL = ''
                        OR
                            FINLISVAL >= {$itelisval_rec['INILISVAL']}
                        )";
            if (ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false)) {
                return false;
            }
        } else {
            if ($itelisval_rec['INILISVAL'] > $itelisval_rec['FINLISVAL']) {
                return false;
            }

            $sql = "SELECT
                        ROWID
                    FROM
                        ITELISVAL
                    WHERE
                        CODLISVAL <> {$itelisval_rec['CODLISVAL']}
                    AND
                        (
                            (
                                FINLISVAL = ''
                            AND
                                INILISVAL <= {$itelisval_rec['FINLISVAL']}
                            )
                        OR
                            (
                                (
                                    INILISVAL >= {$itelisval_rec['INILISVAL']}
                                AND
                                    INILISVAL <= {$itelisval_rec['FINLISVAL']}
                                )
                            OR
                                (
                                    FINLISVAL >= {$itelisval_rec['INILISVAL']}
                                AND
                                    FINLISVAL <= {$itelisval_rec['FINLISVAL']}
                                )
                            )
                        OR
                            (
                                INILISVAL <= {$itelisval_rec['INILISVAL']}
                            AND
                                FINLISVAL >= {$itelisval_rec['FINLISVAL']}
                            )
                        )";

            if (ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false)) {
                return false;
            }
        }

        return true;
    }

    private function ricListino($returnModel, $retId = '') {
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Valido dal",
            "al"
        );

        $colonneModel = array(
            array("name" => 'CODLISVAL', "width" => 50),
            array("name" => 'DESCLISVAL', "width" => 200),
            array("name" => 'INILISVAL', "width" => 100, 'formatter' => 'eqdate'),
            array("name" => 'FINLISVAL', "width" => 100, 'formatter' => 'eqdate')
        );

        $sql = "SELECT
                    ITELISVAL.*
                FROM
                    ITELISVAL
                WHERE
                    (
                        SELECT
                            COUNT( ITELIS.ROWID )
                        FROM
                            ITELIS
                        WHERE
                            ITELIS.CODVAL = ITELISVAL.CODLISVAL
                    ) > 0";

        $gridOptions = array(
            "Caption" => "Elenco Listini",
            "width" => '470',
            "height" => '400',
            "sortname" => "INILISVAL",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnListino';
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}
