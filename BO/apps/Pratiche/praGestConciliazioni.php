<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPagamenti.class.php';

function praGestConciliazioni() {
    $praGestConciliazioni = new praGestConciliazioni();
    $praGestConciliazioni->parseEvent();
    return;
}

class praGestConciliazioni extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praLibPagamenti;
    public $nameForm = "praGestConciliazioni";
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

    public function setProgressivoOnere($progressivoOnere) {
        $this->progressivoOnere = $progressivoOnere;
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

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $proconciliazione_rec = $_POST[$this->nameForm . '_PROCONCILIAZIONE'];

                        if ($proconciliazione_rec['DATAQUIETANZA'] > date('Ymd')) {
                            Out::msgStop("Errore", "Data Quietanza non valida");
                            break;
                        }

                        if (!($lock = $this->lock() )) {
                            break;
                        }

                        $proconciliazione_rec['IMPONUM'] = $this->numeroPratica;

                        $insert_info = 'Oggetto: ' . $proconciliazione_rec['IMPONUM'] . '/' . $proconciliazione_rec['IMPOPROG'];

                        if (!$this->insertRecord($this->PRAM_DB, 'PROCONCILIAZIONE', $proconciliazione_rec, $insert_info)) {
                            $this->unlock($lock);
                            break;
                        }

                        if (!$this->praLibPagamenti->sincronizzaSomme($this->numeroPratica, $this->progressivoOnere, true)) {
                            Out::msgStop("Errore", $this->praLibPagamenti->getErrMessage());
                            $this->unlock($lock);
                            break;
                        }

                        $this->unlock($lock);

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $proconciliazione_rec = $_POST[$this->nameForm . '_PROCONCILIAZIONE'];

                        if ($proconciliazione_rec['DATAQUIETANZA'] > date('Ymd')) {
                            Out::msgStop("Errore", "Data Quietanza non valida");
                            break;
                        }

                        $update_info = 'Oggetto: ' . $proconciliazione_rec['ROWID'] . " " . $proconciliazione_rec['IMPONUM'] . '/' . $proconciliazione_rec['IMPOPROG'];

                        if (!$this->updateRecord($this->PRAM_DB, 'PROCONCILIAZIONE', $proconciliazione_rec, $update_info)) {
                            break;
                        }

                        if (!$this->praLibPagamenti->sincronizzaSomme($this->numeroPratica, $this->progressivoOnere, true)) {
                            Out::msgStop("Errore", $this->praLibPagamenti->getErrMessage());
                            break;
                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Cancella':
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
                        $proconciliazione_rec = $_POST[$this->nameForm . '_PROCONCILIAZIONE'];

                        $delete_info = 'Oggetto: ' . $proconciliazione_rec['ROWID'] . " " . $proconciliazione_rec['IMPONUM'] . '/' . $proconciliazione_rec['IMPOPROG'];

                        if (!$this->deleteRecord($this->PRAM_DB, 'PROCONCILIAZIONE', $proconciliazione_rec['ROWID'], $delete_info)) {
                            break;
                        }

                        if (!$this->praLibPagamenti->sincronizzaSomme($this->numeroPratica, $this->progressivoOnere)) {
                            Out::msgStop("Errore", $this->praLibPagamenti->getErrMessage());
                            break;
                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_PROCONCILIAZIONE[QUIETANZA]_butt':
                        praRic::ricAnaquiet($this->nameForm);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROCONCILIAZIONE[QUIETANZA]':
                        $key = intval($_POST[$this->nameForm . '_PROCONCILIAZIONE']['QUIETANZA']);
                        $anaquiet_rec = $this->praLib->GetAnaquiet($key);
                        Out::valore($this->nameForm . '_PROCONCILIAZIONE[QUIETANZA]', $key);
                        Out::valore($this->nameForm . '_ANAQUIET[QUIETANZATIPO]', $anaquiet_rec['QUIETANZATIPO']);
                        break;
                }
                break;

            case 'retRicAnaquiet':
                $anaquiet_rec = $this->praLib->GetAnaquiet($_POST['retKey'], 'rowid');
                Out::valori($anaquiet_rec, $this->nameForm . '_ANAQUIET');
                Out::valore($this->nameForm . '_PROCONCILIAZIONE[QUIETANZA]', $anaquiet_rec['CODQUIET']);
                Out::setFocus('', $this->nameForm . '_PROCONCILIAZIONE[NUMEROQUIETANZA]');
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
            $_POST['id'] = $this->returnId;
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
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');

        Out::setFocus($this->nameForm, $this->nameForm . '_PROCONCILIAZIONE[QUIETANZA]');

        if (!$rowid) {
            /*
             * Nuovo
             */

            $this->mostraButtonBar(array('Aggiungi'));

            Out::valore($this->nameForm . '_PROCONCILIAZIONE[IMPOPROG]', $this->progressivoOnere);
            Out::valore($this->nameForm . '_PROCONCILIAZIONE[DATAINSERIMENTO]', date('Ymd'));
        } else {
            /*
             * Dettaglio
             */

            $this->mostraButtonBar(array('Aggiorna', 'Cancella'));

            $proconciliazione_rec = $this->praLib->GetProconciliazione($rowid, 'rowid');
            $anaquiet_rec = $this->praLib->GetAnaquiet($proconciliazione_rec['QUIETANZA']);

            Out::valori($proconciliazione_rec, $this->nameForm . '_PROCONCILIAZIONE');
            Out::valori($anaquiet_rec, $this->nameForm . '_ANAQUIET');

            /*
             * Salvo il progressivo onere
             */
            $this->progressivoOnere = $proconciliazione_rec['IMPOPROG'];
        }

        $proimpo_rec = $this->praLib->GetProimpo($this->numeroPratica, 'codice', "IMPOPROG = '{$this->progressivoOnere}'");
        $anatipimpo_rec = $this->praLib->GetAnatipimpo($proimpo_rec['IMPOCOD']);

        $dataReg = substr($proimpo_rec['DATAREG'], 6, 2) . '/' . substr($proimpo_rec['DATAREG'], 4, 2) . '/' . substr($proimpo_rec['DATAREG'], 0, 4);
        $dataSca = substr($proimpo_rec['DATAREG'], 6, 2) . '/' . substr($proimpo_rec['DATAREG'], 4, 2) . '/' . substr($proimpo_rec['DATAREG'], 0, 4);
        $importo = number_format($proimpo_rec['IMPORTO'], 2);

        $infoArray = array(
            'Progressivo' => $this->progressivoOnere,
            'Descrizione' => $anatipimpo_rec['DESCTIPOIMPO'],
            'Registrazione' => substr($proimpo_rec['DATAREG'], 6, 2) . '/' . substr($proimpo_rec['DATAREG'], 4, 2) . '/' . substr($proimpo_rec['DATAREG'], 0, 4),
            'Importo' => number_format($proimpo_rec['IMPORTO'], 2) . 'E',
            'Scadenza' => substr($proimpo_rec['DATAREG'], 6, 2) . '/' . substr($proimpo_rec['DATAREG'], 4, 2) . '/' . substr($proimpo_rec['DATAREG'], 0, 4)
        );

        $infoHtml = "<b><u>Dettagli Onere</u></b>";

        foreach ($infoArray as $k => $v) {
            $infoHtml .= "<br><b>$k</b>: $v";
        }

        Out::html($this->nameForm . '_infoBox', $infoHtml);
    }

    public function lock() {
        $retLock = ItaDB::DBLock($this->PRAM_DB, "PROCONCILIAZIONE", "", "", 20);

        if ($retLock['status'] !== 0) {
            Out::msgStop("Errore blocco tabella PROCONCILIAZIONE");
            return false;
        }

        return $retLock;
    }

    public function unlock($lock) {
        ItaDB::DBUnLock($lock['lockID']);
    }

}
