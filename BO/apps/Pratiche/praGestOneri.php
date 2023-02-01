<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPagamenti.class.php';

function praGestOneri() {
    $praGestOneri = new praGestOneri();
    $praGestOneri->parseEvent();
    return;
}

class praGestOneri extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praLibPagamenti;
    public $nameForm = "praGestOneri";
    public $gridPagamenti = "praGestOneri_gridPagamenti";
    public $rowid;
    public $progressivoOnere;
    public $numeroPratica;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->praLibPagamenti = new praLibPagamenti();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->progressivoOnere = App::$utente->getKey($this->nameForm . '_progressivoOnere');
            $this->numeroPratica = App::$utente->getKey($this->nameForm . '_numeroPratica');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->returnToParent();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_progressivoOnere', $this->progressivoOnere);
            App::$utente->setKey($this->nameForm . '_numeroPratica', $this->numeroPratica);
        }
    }

    public function setRowid($rowid) {
        $this->rowid = $rowid;
    }

    public function setNumeroPratica($numeroPratica) {
        $this->numeroPratica = $numeroPratica;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openGestione($this->rowid);

                if ($this->delete) {
                    if (!$this->controllaCancella()) {
                        $this->returnToParent();
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
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridPagamenti:
                        $model = "praGestConciliazioni";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->numeroPratica);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setReturnId($_POST[$this->nameForm . '_PROIMPO']['ROWID']);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridPagamenti:
                        $proimpo_rec = $this->praLib->GetProimpo($_POST[$this->nameForm . '_PROIMPO']['ROWID'], 'rowid');

                        $model = "praGestConciliazioni";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->setNumeroPratica($this->numeroPratica);
                        $formObj->setProgressivoOnere($proimpo_rec['IMPOPROG']);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setReturnId($_POST[$this->nameForm . '_PROIMPO']['ROWID']);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridPagamenti:
                        $model = "praGestConciliazioni";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        $formObj->delete = true;
                        $formObj->setRowid($_POST['rowid']);
                        $formObj->setNumeroPratica($this->numeroPratica);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('retGestConciliazione');
                        $formObj->setReturnId($_POST[$this->nameForm . '_PROIMPO']['ROWID']);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridPagamenti:
                        TableView::clearGrid($this->gridPagamenti);

                        $sql = "SELECT
                                    PROCONCILIAZIONE.*,
                                    ANAQUIET.QUIETANZATIPO
                                FROM
                                    PROCONCILIAZIONE
                                LEFT OUTER JOIN
                                    ANAQUIET
                                ON
                                    ANAQUIET.CODQUIET = PROCONCILIAZIONE.QUIETANZA
                                WHERE
                                    IMPONUM = '{$this->numeroPratica}'
                                AND
                                    IMPOPROG = '{$this->progressivoOnere}'";

                        $gridScheda = new TableView($this->gridPagamenti, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPage('json');
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        if (!($lock = $this->lock() )) {
                            break;
                        }

                        $proimpo_rec = $_POST[$this->nameForm . '_PROIMPO'];
                        $proimpo_rec['IMPONUM'] = $this->numeroPratica;
                        $proimpo_rec['IMPOPROG'] = $this->getProgressivo();
                        $proimpo_rec['DIFFERENZA'] = $proimpo_rec['IMPORTO'];
                        $insert_info = 'Oggetto: ' . $proimpo_rec['IMPONUM'] . '/' . $proimpo_rec['IMPOPROG'];

                        if (!$this->insertRecord($this->PRAM_DB, 'PROIMPO', $proimpo_rec, $insert_info)) {
                            $this->unlock($lock);
                            break;
                        }

                        $this->unlock($lock);

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $proimpo_rec = $_POST[$this->nameForm . '_PROIMPO'];
                        $update_info = 'Oggetto: ' . $proimpo_rec['ROWID'] . " " . $proimpo_rec['IMPONUM'] . '/' . $proimpo_rec['IMPOPROG'];

                        if (!$this->updateRecord($this->PRAM_DB, 'PROIMPO', $proimpo_rec, $update_info)) {
                            break;
                        }

                        if (!$this->praLibPagamenti->sincronizzaSomme($this->numeroPratica, $proimpo_rec['IMPOPROG'], true)) {
                            Out::msgStop("Errore", $this->praLibPagamenti->getErrMessage());
                            break;
                        }

                        $this->returnToParent();
                        break;

//                    case $this->nameForm . '_Pagamento':
//                        $proimpo_rec = $this->praLib->GetProimpo($_POST[$this->nameForm . '_PROIMPO']['ROWID'], 'rowid');
//
//                        $model = "praGestConciliazioni";
//                        itaLib::openDialog($model);
//                        $formObj = itaModel::getInstance($model);
//                        if (!$formObj) {
//                            Out::msgStop("Errore", "Apertura model fallita");
//                            break;
//                        }
//                        $formObj->setNumeroPratica($this->numeroPratica);
//                        $formObj->setProgressivoOnere($proimpo_rec['IMPOPROG']);
//                        $formObj->setReturnModel($this->nameForm);
//                        $formObj->setReturnEvent('retGestConciliazione');
//                        $formObj->setReturnId($_POST[$this->nameForm . '_PROIMPO']['ROWID']);
//                        $formObj->setEvent('openform');
//                        $formObj->parseEvent();
//                        break;

                    case $this->nameForm . '_Cancella':
                        $proimpo_rec = $_POST[$this->nameForm . '_PROIMPO'];
                        if (!$this->controllaCancella()) {
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
                        $proimpo_rec = $_POST[$this->nameForm . '_PROIMPO'];
                        $delete_info = 'Oggetto: ' . $proimpo_rec['ROWID'] . " " . $proimpo_rec['IMPONUM'] . '/' . $proimpo_rec['IMPOPROG'];

                        if (!$this->controllaCancella()) {
                            break;
                        }

                        if (!$this->deleteRecord($this->PRAM_DB, 'PROIMPO', $proimpo_rec['ROWID'], $delete_info)) {
                            break;
                        }

//                        if (!$this->riordinaDatabase()) {
//                            Out::msgStop("Errore", "Errore durante la numerazione dei progressivi su PROIMPO");
//                            break;
//                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_PROIMPO[IMPOCOD]_butt':
                        praRic::ricAnatipimpo($this->nameForm);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROIMPO[IMPOCOD]':
                        $IMPOCOD = intval($_POST[$this->nameForm . '_PROIMPO']['IMPOCOD']);
                        $anatipimpo_rec = $this->praLib->GetAnatipimpo($IMPOCOD);
                        Out::valore($this->nameForm . '_PROIMPO[IMPOCOD]', $IMPOCOD);
                        Out::valore($this->nameForm . '_ANATIPIMPO[DESCTIPOIMPO]', $anatipimpo_rec['DESCTIPOIMPO']);
                        break;
                }
                break;

            case 'retRicAnatipimpo':
                $anatipimpo_rec = $this->praLib->GetAnatipimpo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_PROIMPO[IMPOCOD]', $anatipimpo_rec['CODTIPOIMPO']);
                Out::valori($anatipimpo_rec, $this->nameForm . '_ANATIPIMPO');
                break;

            case 'retGestConciliazione':
                $this->openGestione($_POST['id']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_progressivoOnere');
        App::$utente->removeKey($this->nameForm . '_numeroPratica');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel) {
            $_POST = array();
            $_POST['model'] = $this->returnModel;
            $_POST['event'] = $this->returnEvent;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
            $returnModel = itaModel::getInstance($this->returnModel);
            $returnModel->parseEvent();
        }

        if ($close) {
            $this->close();
        }
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Pagamento');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');

        Out::setFocus($this->nameForm, $this->nameForm . '_PROIMPO[IMPOCOD]');

        if (!$rowid) {
            /*
             * Nuovo
             */

            Out::valore($this->nameForm . '_PROIMPO[DATAREG]', date('Ymd'));
            $this->mostraButtonBar(array('Aggiungi'));
            Out::hide($this->nameForm . '_divPagamenti');
        } else {
            /*
             * Dettaglio
             */

            $this->mostraButtonBar(array('Aggiorna', 'Cancella'));

            $proimpo_rec = $this->praLib->GetProimpo($rowid, 'rowid');
            $anatipimpo_rec = $this->praLib->GetAnatipimpo($proimpo_rec['IMPOCOD']);

            Out::valori($proimpo_rec, $this->nameForm . '_PROIMPO');
            Out::valori($anatipimpo_rec, $this->nameForm . '_ANATIPIMPO');

            $this->progressivoOnere = $proimpo_rec['IMPOPROG'];
            TableView::enableEvents($this->gridPagamenti);
            TableView::reload($this->gridPagamenti);
        }
    }

    public function getProgressivo() {
        $sql = "SELECT MAX(IMPOPROG) AS PROG FROM PROIMPO WHERE IMPONUM = '{$this->numeroPratica}' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec['PROG'] ? intval($rec['PROG']) + 1 : 1;
    }

    public function riordinaDatabase() {
//        $sql = "SELECT ROWID, IMPOPROG FROM PROIMPO WHERE IMPONUM = '{$this->numeroPratica}' ORDER BY IMPOPROG ASC";
//        $proimpo_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//        $progressivo = 1;
//
//        foreach ($proimpo_tab as $proimpo_rec) {
//            $prog_old = $proimpo_rec['IMPOPROG'];
//            $prog_new = $progressivo++;
//
//            if ($prog_old == $prog_new) {
//                continue;
//            }
//
//            $sql = "SELECT ROWID FROM PROCONCILIAZIONE WHERE IMPONUM = '{$this->numeroPratica}' AND IMPOPROG = '$prog_old'";
//            $proconciliazione_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//
//            foreach ($proconciliazione_tab as $proconciliazione_rec) {
//                $proconciliazione_rec['IMPOPROG'] = $prog_new;
//                $update_info = 'Oggetto: update progressivo IMPOPROG su ROWID ' . $proconciliazione_rec['ROWID'];
//
//                if (!$this->updateRecord($this->PRAM_DB, 'PROCONCILIAZIONE', $proconciliazione_rec, $update_info)) {
//                    return false;
//                }
//            }
//
//            $proimpo_rec['IMPOPROG'] = $prog_new;
//            $update_info = 'Oggetto: update progressivo IMPOPROG su ROWID ' . $proimpo_rec['ROWID'];
//
//            if (!$this->updateRecord($this->PRAM_DB, 'PROIMPO', $proimpo_rec, $update_info)) {
//                return false;
//            }
//        }

        return true;
    }

    public function controllaCancella() {
        $relation_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROCONCILIAZIONE WHERE IMPONUM = '{$this->numeroPratica}' AND IMPOPROG = '{$this->progressivoOnere}'", false);

        if ($relation_rec) {
            Out::msgStop("Errore", "Sono presenti dei pagamenti, impossibile cancellare il record");
            return false;
        }

        return true;
    }

    public function lock() {
        $retLock = ItaDB::DBLock($this->PRAM_DB, "PROIMPO", "", "", 20);

        if ($retLock['status'] !== 0) {
            Out::msgStop("Errore blocco tabella PROIMPO");
            return false;
        }

        return $retLock;
    }

    public function unlock($lock) {
        ItaDB::DBUnLock($lock['lockID']);
    }

}
