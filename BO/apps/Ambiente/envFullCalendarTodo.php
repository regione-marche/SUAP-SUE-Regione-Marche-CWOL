<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    04.11.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';

function envFullCalendarTodo() {
    $envFullCalendarTodo = new envFullCalendarTodo();
    $envFullCalendarTodo->parseEvent();
    return;
}

class envFullCalendarTodo extends itaModel {

    public $ITALWEB_DB;
    public $envLibCalendar;
    public $utiEnte;
    public $attivitaRowid;
    public $esegui;
    public $nameForm = "envFullCalendarTodo";
    public $divGes = "envFullCalendarTodo_divGestione";
    public $todoPrio = array('1' => 'Bassa', '2' => 'Normale', '3' => 'Alta');
    public $tipoProm = array('notifica' => 'Notifica', 'email' => 'Email', 'popup' => 'Pop-Up');
    public $unitProm = array('60' => 'Minuti', '3600' => 'Ore', '86400' => 'Giorni', '604800' => 'Settimane', '2592000' => 'Mesi');

    function __construct() {
        parent::__construct();
        try {
            $this->envLibCalendar = new envLibCalendar();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->esegui = App::$utente->getKey($this->nameForm . '_esegui');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_esegui', $this->esegui);
        }
    }

    public function setModelParam($params) {
        $params = unserialize($params);
        foreach ($params as $func => $args) {
            call_user_func_array(array($this->nameForm, $func), $args);
        }
    }

    public function setRowid($rowid) {
        $this->attivitaRowid = $rowid;
    }

    public function setAtCloseExe($esegui) {
        $this->esegui = $esegui;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->attivitaRowid && !$this->envLibCalendar->isTodoEditable($this->attivitaRowid)) {
                    $this->returnToParent();
                    break;
                }

                $this->Open();
                Out::codice('tinyActivate("' . $this->nameForm . '_CAL_ATTIVITA[DESCRIZIONE]");');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Salva':
                        $todo_rec = $this->checkTodoParams();
                        $prom_rec = $_POST[$this->nameForm . '_CAL_PROMEMORIA'];

                        $result = $this->ctrRipetiCampi($todo_rec);
                        if ($result === false) {
                            break;
                        }
                        $todo_rec = $this->ctrRipeti($todo_rec);

                        ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo_rec);
                        $todoId = ItaDB::DBLastId($this->ITALWEB_DB);

                        if ($_POST[$this->nameForm . '_CHECK_PROM']) {
                            if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                if (!$this->envLibCalendar->addPromemoriaTodo($todoId, $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                    Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                }
                            }
                        }

                        $id = $todo_rec['ROWID_CALENDARIO'];

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $todo_rec = $this->checkTodoParams();
                        $prom_rec = $_POST[$this->nameForm . '_CAL_PROMEMORIA'];

                        if (!$todo_rec)
                            break;

                        $result = $this->ctrRipetiCampi($todo_rec);
                        if ($result === false) {
                            break;
                        }
                        $todo_rec = $this->ctrRipeti($todo_rec);

                        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo_rec);

                        if ($_POST[$this->nameForm . '_CHECK_PROM']) {
                            if ($prom_rec['ROWID']) {
                                if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                    if (!$this->envLibCalendar->updatePromemoria($prom_rec['ROWID'], $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                    }
                                } else {
                                    if (!$this->envLibCalendar->deletePromemoria($prom_rec['ROWID'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                    }
                                }
                            } else {
                                if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                    if (!$this->envLibCalendar->addPromemoriaTodo($todo_rec['ROWID'], $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                    }
                                }
                            }
                        } else {
                            $this->envLibCalendar->deletePromemoriaFromTodo($todo_rec['ROWID']);
                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Elimina':
                        $todo_rec = $_POST[$this->nameForm . '_CAL_ATTIVITA'];
                        if ($this->envLibCalendar->isTodoDeletable($todo_rec['ROWID'])) {
                            Out::msgQuestion("Elimina", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaElimina', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Errore", "Non hai i permessi per eliminare questa attività");
                        }
                        break;

                    case $this->nameForm . '_ConfermaElimina':
                        $todo_rec = $_POST[$this->nameForm . '_CAL_ATTIVITA'];
                        if ($this->envLibCalendar->isTodoDeletable($todo_rec['ROWID'])) {
                            ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo_rec['ROWID']);
                            $this->envLibCalendar->deletePromemoriaFromTodo($todo_rec['ROWID']);
                            $this->returnToParent();
                        } else {
                            Out::msgStop("Errore", "Non hai i permessi per eliminare questa attività");
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    private function checkTodoParams() {
        $todo_rec = $_POST[$this->nameForm . '_CAL_ATTIVITA'];

        $starttime = !$_POST[$this->nameForm . '_STARTTIME'] ? '000000' : substr($_POST[$this->nameForm . '_STARTTIME'], 0, 2) . substr($_POST[$this->nameForm . '_STARTTIME'], 3, 2) . '00';
        $todo_rec['START'] .= $starttime;

        if (!$todo_rec['DESCRIZIONE'])
            $todo_rec['DESCRIZIONE'] = '';

        return $todo_rec;
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_esegui');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
//        $returnObj = itaModel::getInstance($this->returnModel);
//        $returnObj->setEvent($this->returnEvent);
//        $returnObj->parseEvent();
        if ($this->esegui == '') {
            Out::broadcastMessage($this->nameForm, 'AGGIORNATODO');
        } else {
            Out::broadcastMessage($this->nameForm, 'AGGIORNADAPOPUP');
        }
        if ($close) {
            $this->close();
        }
    }

    public function Open() {
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->divGes);

        $this->selectCalendari();
        $this->setMesi();
        $this->selectClassificazione();

        if ($this->attivitaRowid) {
            $todo_rec = $this->envLibCalendar->getTodo($this->attivitaRowid);
            if ($todo_rec) {
                Out::show($this->nameForm . '_Aggiorna');

                if ($this->envLibCalendar->isTodoDeletable($this->attivitaRowid)) {
                    Out::show($this->nameForm . '_Elimina');
                }

                //Out::attributo($this->nameForm . '_CAL_ATTIVITA[ROWID_CALENDARIO]', 'disabled', '0', 'disabled');
                Out::valore($this->nameForm . '_STARTTIME', substr($todo_rec['START'], 8, 2) . ':' . substr($todo_rec['START'], 10, 2));
                $todo_rec['START'] = substr($todo_rec['START'], 6, 2) . '/' . substr($todo_rec['START'], 4, 2) . '/' . substr($todo_rec['START'], 0, 4);
                Out::valori($todo_rec, $this->nameForm . '_CAL_ATTIVITA');

                foreach ($this->envLibCalendar->getPromemoriaFromTodo($this->attivitaRowid) as $prom_rec) {
                    Out::valori($prom_rec, $this->nameForm . '_CAL_PROMEMORIA');
                    Out::valore($this->nameForm . '_CHECK_PROM', '1');
                }
                $mesi = str_pad($todo_rec['TEMPO'], 2, '0', STR_PAD_LEFT);
                Out::valore($this->nameForm . '_CAL_ATTIVITA[TEMPO]', $mesi);
            } else {
                Out::closeDialog($this->nameForm);
                Out::msgStop("", "Attività non presente");
            }
        } else {
//            $start = strtotime($_POST['calendarParam']['start']);
//            $end = strtotime($_POST['calendarParam']['end']);
//
//            if ($start) {
//                Out::valore($this->nameForm . '_CAL_ATTIVITA[START]', date('d/m/Y', $start));
//                Out::valore($this->nameForm . '_STARTTIME', date('H:i', $start));
//            }

            $mm = date('i') > 30 ? '30' : '00';
            Out::valore($this->nameForm . '_CAL_ATTIVITA[START]', date('d/m/Y'));
            Out::valore($this->nameForm . '_STARTTIME', date('H') . $mm);
            Out::show($this->nameForm . '_Salva');
            //Out::attributo($this->nameForm . '_CAL_ATTIVITA[ROWID_CALENDARIO]', 'disabled', '1');
        }

        Out::setFocus($this->nameForm, $this->nameForm . '_CAL_ATTIVITA[TITOLO]');
    }

    private function selectCalendari() {
        $personalCalendars = $this->envLibCalendar->getSelectedCalendars(true);
        $calendarsList = $this->envLibCalendar->getCalendars('_1__');
        $oneSelect = false;
        foreach ($calendarsList as $key => $calendar) {
            if (isset($personalCalendars[$calendar['ROWID']])) {
                if ($calendar['IDUTENTE'] == App::$utente->getIdUtente() && !$oneSelect) {
                    $sel = '1';
                    $oneSelect = true;
                } else
                    $sel = '0';
                $text = $calendar['TITOLO'];
                $text = '<span style="margin-right: 4px; width: 20px; height: 10px; display: inline-block; background-color: ' . $personalCalendars[$calendar['ROWID']]['color'] . ';"></span> ' . $text;
                Out::select($this->nameForm . '_CAL_ATTIVITA[ROWID_CALENDARIO]', 1, $calendar['ROWID'], $sel, $text);
            }
        }
        foreach ($this->todoPrio as $key => $value) {
            $sel = $key == '2' ? '1' : '0';
            Out::select($this->nameForm . '_CAL_ATTIVITA[PRIORITA]', 1, $key, $sel, $value);
        }
        foreach ($this->tipoProm as $key => $value) {
            $sel = $key == 'notifica' ? '1' : '0';
            Out::select($this->nameForm . '_CAL_PROMEMORIA[TIPO]', 1, $key, $sel, $value);
        }
        Out::valore($this->nameForm . '_CAL_PROMEMORIA[TEMPO]', '30');
        foreach ($this->unitProm as $key => $value) {
            $sel = $key == 'minuti' ? '1' : '0';
            Out::select($this->nameForm . '_CAL_PROMEMORIA[UNITA]', 1, $key, $sel, $value);
        }
    }

    public function setMesi() {
        Out::select($this->nameForm . '_CAL_ATTIVITA[TEMPO]', 1, '', 1, '');
        for ($i = 1; $i <= 12; $i++) {
            $i = str_pad($i, 2, '0', STR_PAD_LEFT);
            Out::select($this->nameForm . '_CAL_ATTIVITA[TEMPO]', 1, $i, 0, $i);
        }
    }

    public function selectClassificazione() {
        $sql = "SELECT * FROM ENV_TIPI";
        $tipi_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);

        Out::select($this->nameForm . '_CAL_ATTIVITA[CLASSEVENTO]', 1, '', 1, '');

        foreach ($tipi_tab as $tipi_rec) {
            Out::select($this->nameForm . '_CAL_ATTIVITA[CLASSEVENTO]', 1, $tipi_rec['CODICE'], 0, $tipi_rec['DESCRIZIONE']);
        }
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    public function Nascondi() {
        Out::hide($this->divGes);
        Out::hide($this->nameForm . '_Salva');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Elimina');
    }

    public function ctrRipeti($todo_rec) {
        if ($todo_rec['RIPETI'] == 0) {
            $todo_rec['TEMPO'] = $todo_rec['UNITA'] = 0;
            $todo_rec['TERMINA'] = '';
        } else {
            $todo_rec['UNITA'] = 2592000;
        }
        return $todo_rec;
    }

    public function ctrRipetiCampi($todo_rec) {
        if ($todo_rec['RIPETI'] != 0) {
            if ($todo_rec['TEMPO'] == '') {
                Out::msgInfo('ATTENZIONE', 'Indicare il numero dei mesi.');
                return false;
            }
        }
        return true;
    }

}

?>