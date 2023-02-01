<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    13.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';

function envFullCalendarEvent() {
    $envFullCalendarEvent = new envFullCalendarEvent();
    $envFullCalendarEvent->parseEvent();
    return;
}

class envFullCalendarEvent extends itaModel {

    public $ITALWEB_DB;
    public $envLibCalendar;
    public $utiEnte;
    public $eventoRowid;
    public $esegui;
    public $nameForm = "envFullCalendarEvent";
    public $divGes = "envFullCalendarEvent_divGestione";
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
        $this->eventoRowid = $rowid;
    }

    public function setAtCloseExe($esegui) {
        $this->esegui = $esegui;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->eventoRowid && !$this->envLibCalendar->isEventEditable($this->eventoRowid)) {
                    $this->returnToParent();
                    break;
                }

                $this->Open();
                Out::codice('tinyActivate("' . $this->nameForm . '_CAL_EVENTI[DESCRIZIONE]");');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Salva':
                        $eventi_rec = $this->checkEventParams();
                        if (!$eventi_rec) {
                            break;
                        }

                        $prom_rec = $_POST[$this->nameForm . '_CAL_PROMEMORIA'];

                        $result = $this->ctrRipetiCampi($eventi_rec);
                        if ($result === false) {
                            break;
                        }
                        $eventi_rec = $this->ctrRipeti($eventi_rec);

                        ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $eventi_rec);
                        $eventId = ItaDB::DBLastId($this->ITALWEB_DB);

                        if ($_POST[$this->nameForm . '_CHECK_PROM']) {
                            if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                if (!$this->envLibCalendar->addPromemoriaEvent($eventId, $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                    Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                    break;
                                }
                            }
                        }

                        $id = $eventi_rec['ROWID_CALENDARIO'];

                        $personalCalendars = $this->envLibCalendar->getSelectedCalendars();
                        $personalCalendars[$id]['visible'] = true;
                        $this->envLibCalendar->setSelectedCalendars($personalCalendars);

                        Out::closeDialog($this->nameForm);
                        Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $eventi_rec = $this->checkEventParams();
                        if (!$eventi_rec) {
                            break;
                        }

                        $prom_rec = $_POST[$this->nameForm . '_CAL_PROMEMORIA'];

                        $result = $this->ctrRipetiCampi($eventi_rec);
                        if ($result === false) {
                            break;
                        }
                        $eventi_rec = $this->ctrRipeti($eventi_rec);

                        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $eventi_rec);

                        if ($_POST[$this->nameForm . '_CHECK_PROM']) {
                            if ($prom_rec['ROWID']) {
                                if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                    if (!$this->envLibCalendar->updatePromemoria($prom_rec['ROWID'], $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                        break;
                                    }
                                } else {
                                    if (!$this->envLibCalendar->deletePromemoria($prom_rec['ROWID'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                        break;
                                    }
                                }
                            } else {
                                if ($prom_rec['TIPO'] && $prom_rec['TEMPO'] && $prom_rec['UNITA']) {
                                    if (!$this->envLibCalendar->addPromemoriaEvent($eventi_rec['ROWID'], $prom_rec['TIPO'], $prom_rec['TEMPO'], $prom_rec['UNITA'])) {
                                        Out::msgStop('Errore', $this->envLibCalendar->getErrMessage());
                                        break;
                                    }
                                }
                            }
                        } else {
                            $this->envLibCalendar->deletePromemoriaFromEvent($eventi_rec['ROWID']);
                        }

                        Out::closeDialog($this->nameForm);
                        if ($this->esegui == '') {
                            Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                        } else {
                            Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                            Out::broadcastMessage($this->nameForm, 'AGGIORNADAPOPUP');
                        }
                        break;

                    case $this->nameForm . '_Elimina':
                        $eventi_rec = $_POST[$this->nameForm . '_CAL_EVENTI'];
                        if ($this->envLibCalendar->isEventDeletable($eventi_rec['ROWID'])) {
                            Out::msgQuestion("Elimina", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaElimina', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                            );
                        } else {
                            Out::msgStop("Errore", "Non hai i permessi per eliminare questo evento");
                        }
                        break;

                    case $this->nameForm . '_ConfermaElimina':
                        $eventi_rec = $_POST[$this->nameForm . '_CAL_EVENTI'];
                        if ($this->envLibCalendar->isEventDeletable($eventi_rec['ROWID'])) {
                            ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $eventi_rec['ROWID']);
                            $this->envLibCalendar->deletePromemoriaFromEvent($eventi_rec['ROWID']);
                            Out::closeDialog($this->nameForm);
                            if ($this->esegui == '') {
                                Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                            } else {
                                Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                                Out::broadcastMessage($this->nameForm, 'AGGIORNADAPOPUP');
                            }
                        } else {
                            Out::msgStop("Errore", "Non hai i permessi per eliminare questo evento");
                        }
                        break;

                    case $this->nameForm . '_Stampa':
                        $this->stampaEvento($_POST[$this->nameForm . '_CAL_EVENTI']['ROWID']);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    private function checkEventParams() {
        $eventi_rec = $_POST[$this->nameForm . '_CAL_EVENTI'];

        $starttime = !$_POST[$this->nameForm . '_STARTTIME'] ? '000000' : substr($_POST[$this->nameForm . '_STARTTIME'], 0, 2) . substr($_POST[$this->nameForm . '_STARTTIME'], 3, 2) . '00';
        $eventi_rec['START'] .= $starttime;

        if ($eventi_rec['END']) {
            $endtime = !$_POST[$this->nameForm . '_ENDTIME'] ? '000000' : substr($_POST[$this->nameForm . '_ENDTIME'], 0, 2) . substr($_POST[$this->nameForm . '_ENDTIME'], 3, 2) . '00';
            $eventi_rec['END'] .= $endtime;

            if ($eventi_rec['END'] < $eventi_rec['START']) {
                Out::msgStop("Errore", "La data fine deve essere successiva a quella di inizio");
                return false;
            }
        }

        if ($_POST[$this->nameForm . '_ENDTIME'] && !$eventi_rec['END']) {
            Out::msgStop("Errore", "Inserire la data fine oltre l'orario");
            return false;
        }

        if (!$eventi_rec['DESCRIZIONE'])
            $eventi_rec['DESCRIZIONE'] = '';

        return $eventi_rec;
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_esegui');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
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

        if ($this->eventoRowid) {
            $eventi_rec = $this->envLibCalendar->getEvent($this->eventoRowid);
            if ($eventi_rec) {
                Out::show($this->nameForm . '_Aggiorna');
                Out::show($this->nameForm . '_Stampa');

                if ($this->envLibCalendar->isEventDeletable($this->eventoRowid)) {
                    Out::show($this->nameForm . '_Elimina');
                }

                //Out::attributo($this->nameForm . '_CAL_EVENTI[ROWID_CALENDARIO]', 'disabled', '0', 'disabled');
                Out::valore($this->nameForm . '_STARTTIME', substr($eventi_rec['START'], 8, 2) . ':' . substr($eventi_rec['START'], 10, 2));
                $eventi_rec['START'] = substr($eventi_rec['START'], 6, 2) . '/' . substr($eventi_rec['START'], 4, 2) . '/' . substr($eventi_rec['START'], 0, 4);
                if ($eventi_rec['END']) {
                    Out::valore($this->nameForm . '_ENDTIME', substr($eventi_rec['END'], 8, 2) . ':' . substr($eventi_rec['END'], 10, 2));
                    $eventi_rec['END'] = substr($eventi_rec['END'], 6, 2) . '/' . substr($eventi_rec['END'], 4, 2) . '/' . substr($eventi_rec['END'], 0, 4);
                }
                Out::valori($eventi_rec, $this->nameForm . '_CAL_EVENTI');

                foreach ($this->envLibCalendar->getPromemoriaFromEvent($this->eventoRowid) as $prom_rec) {
                    Out::valori($prom_rec, $this->nameForm . '_CAL_PROMEMORIA');
                    Out::valore($this->nameForm . '_CHECK_PROM', '1');
                }
                $mesi = str_pad($eventi_rec['TEMPO'], 2, '0', STR_PAD_LEFT);
                Out::valore($this->nameForm . '_CAL_EVENTI[TEMPO]', $mesi);
            } else {
                Out::closeDialog($this->nameForm);
                Out::msgStop("", "Evento non presente");
            }
        } else {
            $start = strtotime($_POST['calendarParam']['start']);
            $end = strtotime($_POST['calendarParam']['end']);

            if (date('Hi', $start) === '0000' && date('Hi', $end) === '0000') {
                $end = $start;
            }

            if ($start) {
                Out::valore($this->nameForm . '_CAL_EVENTI[START]', date('d/m/Y', $start));
                Out::valore($this->nameForm . '_STARTTIME', date('Hi', $start) === '0000' ? date('H') . ':00' : date('H:i', $start));
            }
            if ($end) {
                Out::valore($this->nameForm . '_CAL_EVENTI[END]', date('d/m/Y', $end));
                Out::valore($this->nameForm . '_ENDTIME', date('Hi', $end) === '0000' ? (date('H') + 1) . ':00' : date('H:i', $end));
            }

            Out::show($this->nameForm . '_Salva');
            //Out::attributo($this->nameForm . '_CAL_EVENTI[ROWID_CALENDARIO]', 'disabled', '1');
        }

        Out::setFocus($this->nameForm, $this->nameForm . '_CAL_EVENTI[TITOLO]');
    }

    private function selectCalendari() {
        $personalCalendars = $this->envLibCalendar->getSelectedCalendars(true);
        $calendarsList = $this->envLibCalendar->getCalendars('_1__');
        $oneSelect = false;
        foreach ($calendarsList as $key => $calendar) {
            if ($personalCalendars[$calendar['ROWID']]) {
                if ($calendar['IDUTENTE'] == App::$utente->getIdUtente() && !$oneSelect) {
                    $sel = '1';
                    $oneSelect = true;
                } else
                    $sel = '0';
                $text = $calendar['TITOLO'];
                $text = '<span style="margin-right: 4px; width: 20px; height: 10px; display: inline-block; background-color: ' . $personalCalendars[$calendar['ROWID']]['color'] . ';"></span> ' . $text;
                Out::select($this->nameForm . '_CAL_EVENTI[ROWID_CALENDARIO]', 1, $calendar['ROWID'], $sel, $text);
            }
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
        Out::select($this->nameForm . '_CAL_EVENTI[TEMPO]', 1, '', 1, '');
        for ($i = 1; $i <= 12; $i++) {
            $i = str_pad($i, 2, '0', STR_PAD_LEFT);
            Out::select($this->nameForm . '_CAL_EVENTI[TEMPO]', 1, $i, 0, $i);
        }
    }

    public function selectClassificazione() {
        $sql = "SELECT * FROM ENV_TIPI";
        $tipi_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);

        Out::select($this->nameForm . '_CAL_EVENTI[CLASSEVENTO]', 1, '', 1, '');

        foreach ($tipi_tab as $tipi_rec) {
            Out::select($this->nameForm . '_CAL_EVENTI[CLASSEVENTO]', 1, $tipi_rec['CODICE'], 0, $tipi_rec['DESCRIZIONE']);
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
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_CaricaTestoBase');
    }

    public function ctrRipeti($eventi_rec) {
        if ($eventi_rec['RIPETI'] == 0) {
            $eventi_rec['TEMPO'] = $eventi_rec['UNITA'] = 0;
            $eventi_rec['TERMINA'] = '';
        } else {
            $eventi_rec['UNITA'] = 2592000;
        }
        return $eventi_rec;
    }

    public function ctrRipetiCampi($eventi_rec) {
        if ($eventi_rec['RIPETI'] != 0) {
            if ($eventi_rec['TEMPO'] == '') {
                Out::msgInfo('ATTENZIONE', 'Indicare il numero dei mesi.');
                return false;
            }
        }
        return true;
    }

    public function stampaEvento($rowidEvento) {
        $sql = "SELECT
                    CAL_EVENTI.TITOLO,
                    CAL_EVENTI.START,
                    CAL_EVENTI.END,
                    CAL_EVENTI.DESCRIZIONE,
                    CAL_CALENDARI.TITOLO AS CALENDARIO
                FROM
                    CAL_EVENTI
                LEFT OUTER JOIN CAL_CALENDARI ON CAL_EVENTI.ROWID_CALENDARIO = CAL_CALENDARI.ROWID
                WHERE CAL_EVENTI.ROWID = '$rowidEvento'";

        $eventi_rec = ItaDB::DBSQLSelect($this->envLibCalendar->ITALWEB_DB, $sql, false);

        $htmlTemplate = '<div style="text-align: center;"><h1>%s</h1><span style="font-size: .8em;">%s<br>%s</span></div><br>%s';

        $startDate = DateTime::createFromFormat('YmdHis', $eventi_rec['START']);
        $endDate = DateTime::createFromFormat('YmdHis', $eventi_rec['END']);
        $textDate = $startDate ? 'Dal ' . $startDate->format('d/m/Y, H:i') . ($endDate ? ' al ' . $endDate->format('d/m/Y, H:i') : '') : '';

        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        $docLib = new docLib;
        $pdfPreview = $docLib->Xhtml2Pdf(sprintf($htmlTemplate, $eventi_rec['TITOLO'], $eventi_rec['CALENDARIO'], $textDate, $eventi_rec['DESCRIZIONE']));

        if ($pdfPreview === false) {
            Out::msgStop('Errore', $this->docLib->getErrMessage());
            return false;
        }

        Out::openDocument(utiDownload::getUrl($eventi_rec['TITOLO'] . '.pdf', $pdfPreview));
        return true;
    }

}
