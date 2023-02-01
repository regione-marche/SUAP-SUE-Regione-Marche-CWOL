<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praTariffeGest() {
    $praTariffeGest = new praTariffeGest();
    $praTariffeGest->parseEvent();
    return;
}

class praTariffeGest extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $nameForm = "praTariffeGest";
    public $rowid;
    public $numeroListino;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->numeroListino = App::$utente->getKey($this->nameForm . '_numeroListino');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->returnToParent();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_numeroListino', $this->numeroListino);
        }
    }

    public function setRowid($rowid) {
        $this->rowid = $rowid;
    }

    public function setNumeroListino($numeroListino) {
        $this->numeroListino = $numeroListino;
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
                        $itelis_rec = $_POST[$this->nameForm . '_ITELIS'];
                        $itelis_rec['CODVAL'] = $this->numeroListino;

                        $itelis_rec = $this->decodifica($itelis_rec);

                        $insert_info = 'Oggetto: ' . $itelis_rec['DESCRIZIONE'];

                        if (!$this->insertRecord($this->PRAM_DB, 'ITELIS', $itelis_rec, $insert_info)) {
                            $this->unlock($lock);
                            break;
                        }

                        $this->riordina();

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $itelis_rec = $_POST[$this->nameForm . '_ITELIS'];
                        $itelis_rec['CODVAL'] = $this->numeroListino;

                        $itelis_rec = $this->decodifica($itelis_rec);

                        $update_info = 'Oggetto: ' . $itelis_rec['ROWID'] . " " . $itelis_rec['DESCRIZIONE'];

                        if (!$this->updateRecord($this->PRAM_DB, 'ITELIS', $itelis_rec, $update_info)) {
                            break;
                        }

                        $this->riordina();

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
                        $itelis_rec = $_POST[$this->nameForm . '_ITELIS'];

                        $delete_info = 'Oggetto: ' . $itelis_rec['ROWID'] . " " . $itelis_rec['DESCRIZIONE'];

                        if (!$this->deleteRecord($this->PRAM_DB, 'ITELIS', $itelis_rec['ROWID'], $delete_info)) {
                            break;
                        }

                        $this->riordina();

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ITELIS[CODICETIPOIMPO]_butt':
                        praRic::ricAnatipimpo($this->nameForm);
                        break;

                    case $this->nameForm . '_ITELIS[CODICESPORTELLO]_butt':
                        praRic::praRicAnatsp($this->nameForm);
                        break;

                    case $this->nameForm . '_ITELIS[SETTORE]_butt':
                        praRic::praRicAnaset($this->nameForm, '', $_POST[$this->nameForm . '_ITELIS']['ATTIVITA']);
                        break;

                    case $this->nameForm . '_ITELIS[ATTIVITA]_butt':
                        $settore = $_POST[$this->nameForm . '_ITELIS']['SETTORE'];

                        if ($settore) {
                            $where = "AND ATTSET = $settore";
                            praRic::praRicAnaatt($this->nameForm, $where);
                        } else {
                            Out::msgInfo("Attenzione", "Scegliere prima un settore");
                        }
                        break;
                    case $this->nameForm . '_ITELIS[PROCEDIMENTO]_butt':
                        praRic::praRicAnapra($this->nameForm, 'Procedimenti', '', '', '', true);
                        break;
                    case $this->nameForm . '_ITELIS[EVENTO]_butt':
                        praRic::ricAnaeventi($this->nameForm);
                        break;
                    case $this->nameForm . '_ITELIS[TIPOPASSO]_butt':
                        praRic::praRicPraclt($this->nameForm, "Tipi Passo");
                        break;
                    case $this->nameForm . '_ITELIS[AGGREGATO]_butt':
                        praRic::praRicAnaspa($this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITELIS[CODICETIPOIMPO]':
                        $this->decodifica(array(
                            'CODICETIPOIMPO' => $_POST[$this->nameForm . '_ITELIS']['CODICETIPOIMPO']
                        ));
                        break;

                    case $this->nameForm . '_ITELIS[CODICESPORTELLO]':
                        $this->decodifica(array(
                            'CODICESPORTELLO' => $_POST[$this->nameForm . '_ITELIS']['CODICESPORTELLO']
                        ));
                        break;

                    case $this->nameForm . '_ITELIS[SETTORE]':
                        $this->decodifica(array(
                            'SETTORE' => $_POST[$this->nameForm . '_ITELIS']['SETTORE']
                        ));

                        if ($_POST[$this->nameForm . '_ITELIS']['ATTIVITA']) {
                            $anaatt_rec = $this->praLib->GetAnaatt($_POST[$this->nameForm . '_ITELIS']['ATTIVITA']);

                            if ($_POST[$this->nameForm . '_ITELIS']['SETTORE'] != $anaatt_rec['ATTSET']) {
                                $this->decodifica(array(
                                    'ATTIVITA' => ""
                                ));
                            }
                        }
                        break;

                    case $this->nameForm . '_ITELIS[ATTIVITA]':
                        $settore = $_POST[$this->nameForm . '_ITELIS']['SETTORE'];

                        if ($settore) {
                            $this->decodifica(array(
                                'SETTORE' => $settore,
                                'ATTIVITA' => $_POST[$this->nameForm . '_ITELIS']['ATTIVITA']
                            ));
                        } else {
                            $this->decodifica(array(
                                'ATTIVITA' => ""
                            ));
                        }
                        break;

                    case $this->nameForm . '_ITELIS[PROCEDIMENTO]':
                        $codice = $_POST[$this->nameForm . '_ITELIS']['PROCEDIMENTO'];

                        if ($codice != '*') {
                            $codice = str_pad($codice, '6', '0', STR_PAD_LEFT);
                        }

                        $this->decodifica(array(
                            'PROCEDIMENTO' => $codice
                        ));
                        break;

                    case $this->nameForm . '_ITELIS[EVENTO]':
                        $codice = $_POST[$this->nameForm . '_ITELIS']['EVENTO'];

                        if ($codice != '*') {
                            $codice = str_pad($codice, '6', '0', STR_PAD_LEFT);
                        }

                        $this->decodifica(array(
                            'EVENTO' => $codice
                        ));
                        break;

                    case $this->nameForm . '_ITELIS[TIPOPASSO]':
                        $codice = $_POST[$this->nameForm . '_ITELIS']['TIPOPASSO'];

                        if ($codice != '*') {
                            $codice = str_pad($codice, '6', '0', STR_PAD_LEFT);
                        }

                        $this->decodifica(array(
                            'TIPOPASSO' => $codice
                        ));
                        break;
                    case $this->nameForm . '_ITELIS[AGGREGATO]':
                        $codice = $_POST[$this->nameForm . '_ITELIS']['AGGREGATO'];
                        $this->decodifica(array(
                            'AGGREGATO' => $codice
                        ));
                        break;
                }
                break;

            case 'returnAnatsp':
                $this->decodifica(array(
                    'CODICESPORTELLO' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnPraclt':
                $this->decodifica(array(
                    'TIPOPASSO' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnaset':
                $this->decodifica(array(
                    'SETTORE' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnaatt':
                $this->decodifica(array(
                    'ATTIVITA' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'returnAnapra':
                $this->decodifica(array(
                    'PROCEDIMENTO' => $_POST['rowData']['ROWID']
                        ), 'rowid');
                break;

            case 'returnAnaeventi':
                $this->decodifica(array(
                    'EVENTO' => $_POST['retKey']
                        ), 'rowid');
                break;

            case 'retRicAnatipimpo':
                $this->decodifica(array(
                    'CODICETIPOIMPO' => $_POST['retKey']
                        ), 'rowid');
                break;
            case 'returnAnaspa':
                $this->decodifica(array(
                    'AGGREGATO' => $_POST['retKey']
                        ), 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_numeroListino');
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

        Out::setFocus($this->nameForm, $this->nameForm . '_ITELIS[SEQUENZA]');

        Out::hide($this->nameForm . '_ITELIS[ITEKEY]_field');
        Out::hide($this->nameForm . '_ITEKEY_field');

        if (!$rowid) {
            /*
             * Nuovo
             */

            $this->mostraButtonBar(array('Aggiungi'));

            $sql = "SELECT MAX(SEQUENZA) AS SEQ FROM ITELIS WHERE CODVAL = {$this->numeroListino}";
            $seq = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $seq = (ceil(intval($seq['SEQ']) / 10) + 1) * 10;

            Out::valore($this->nameForm . '_ITELIS[SEQUENZA]', $seq);
            Out::valore($this->nameForm . '_ITELIS[ATTIVO]', '1');
        } else {
            /*
             * Dettaglio
             */

            $this->mostraButtonBar(array('Aggiorna', 'Cancella'));

            $itelis_rec = $this->praLib->GetItelis($rowid, 'rowid');

            Out::valori($itelis_rec, $this->nameForm . '_ITELIS');

            $this->decodifica($itelis_rec);
        }
    }

    public function decodifica($itelis_rec, $key = 'codice') {
        $decodFields = array(
            'CODICETIPOIMPO',
            'CODICESPORTELLO',
            'SETTORE',
            'ATTIVITA',
            'PROCEDIMENTO',
            'EVENTO',
            'TIPOPASSO',
            'AGGREGATO'
//            'ITEKEY'
        );

        foreach ($decodFields as $field) {
            if (isset($itelis_rec[$field])) {
                $nodecode = '';
                $nodevalue = '';

                if ($itelis_rec[$field] == '*') {
                    $nodecode = '*';
                    $nodevalue = 'Tutti';
                } elseif (($field == 'CODICESPORTELLO' || $field == 'SETTORE' || $field == 'ATTIVITA' || $field == 'AGGREGATO') && (trim($itelis_rec[$field]) == '' || $itelis_rec[$field] == '0')) {
                    $nodecode = '';
                    $nodevalue = 'Tutti';
                } else {
                    if ($itelis_rec[$field]) {
                        switch ($field) {
                            case 'CODICETIPOIMPO':
                                $anatipimpo_rec = $this->praLib->GetAnatipimpo($itelis_rec['CODICETIPOIMPO'], $key);
                                if ($anatipimpo_rec) {
                                    $nodecode = $anatipimpo_rec['CODTIPOIMPO'];
                                    $nodevalue = $anatipimpo_rec['DESCTIPOIMPO'];
                                }
                                break;

                            case 'CODICESPORTELLO':
                                $anatsp_rec = $this->praLib->GetAnatsp($itelis_rec['CODICESPORTELLO'], $key);
                                if ($anatsp_rec) {
                                    $nodecode = $anatsp_rec['TSPCOD'];
                                    $nodevalue = $anatsp_rec['TSPDES'];
                                } else {
                                    $nodecode = '';
                                    $nodevalue = 'Tutti';
                                }
                                break;

                            case 'SETTORE':
                                $anaset_rec = $this->praLib->GetAnaset($itelis_rec['SETTORE'], $key);
                                if ($anaset_rec) {
                                    $nodecode = $anaset_rec['SETCOD'];
                                    $nodevalue = $anaset_rec['SETDES'];
                                } else {
                                    $nodecode = '';
                                    $nodevalue = 'Tutti';
                                }
                                break;

                            case 'ATTIVITA':
                                $anaatt_rec = $this->praLib->GetAnaatt($itelis_rec['ATTIVITA'], $key);
                                if ($anaatt_rec) {
                                    if (isset($itelis_rec['SETTORE']) && $itelis_rec['SETTORE'] != $anaatt_rec['ATTSET']) {
                                        $nodecode = '';
                                        $nodevalue = 'Tutti';
                                        break;
                                    }

                                    $nodecode = $anaatt_rec['ATTCOD'];
                                    $nodevalue = $anaatt_rec['ATTDES'];
                                } else {
                                    $nodecode = '';
                                    $nodevalue = 'Tutti';
                                }
                                break;

                            case 'PROCEDIMENTO':
                                $anapra_rec = $this->praLib->GetAnapra($itelis_rec['PROCEDIMENTO'], $key);
                                if ($anapra_rec) {
                                    $nodecode = $anapra_rec['PRANUM'];
                                    $nodevalue = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
                                }
                                break;

                            case 'EVENTO':
                                $anaeventi_rec = $this->praLib->GetAnaeventi($itelis_rec['EVENTO'], $key);
                                if ($anaeventi_rec) {
                                    $nodecode = $anaeventi_rec['EVTCOD'];
                                    $nodevalue = $anaeventi_rec['EVTDESCR'];
                                }
                                break;

                            case 'TIPOPASSO':
                                $praclt_rec = $this->praLib->GetPraclt($itelis_rec['TIPOPASSO'], $key);
                                if ($praclt_rec) {
                                    $nodecode = $praclt_rec['CLTCOD'];
                                    $nodevalue = $praclt_rec['CLTDES'];
                                }
                                break;
                            case 'AGGREGATO':
                                $anaspa_rec = $this->praLib->GetAnaspa($itelis_rec['AGGREGATO'], $key);
                                if ($anaspa_rec) {
                                    $nodecode = $anaspa_rec['SPACOD'];
                                    $nodevalue = $anaspa_rec['SPADES'];
                                }
                                break;
                        }
                    }
                }

                Out::valore($this->nameForm . "_ITELIS[$field]", $nodecode);
                Out::valore($this->nameForm . "_$field", $nodevalue);

                $itelis_rec[$field] = $nodecode;
            }
        }

        return $itelis_rec;
    }

    public function riordina() {
        $sql = "SELECT ROWID, SEQUENZA FROM ITELIS WHERE CODVAL = '{$this->numeroListino}' ORDER BY SEQUENZA ASC";
        $itelis_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $progressivo = 1;

        foreach ($itelis_tab as $itelis_rec) {
            $prog_old = $itelis_rec['SEQUENZA'];
            $prog_new = ($progressivo++) * 10;

            if ($prog_old == $prog_new) {
                continue;
            }

            $itelis_rec['SEQUENZA'] = $prog_new;
            $update_info = 'Oggetto: update progressivo SEQUENZA su ROWID ' . $itelis_rec['ROWID'];

            if (!$this->updateRecord($this->PRAM_DB, 'ITELIS', $itelis_rec, $update_info)) {
                return false;
            }
        }
    }

}
