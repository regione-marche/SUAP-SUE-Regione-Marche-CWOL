<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    09.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/CalendarView.class.php';

function envFullCalendarSettings() {
    $envFullCalendarSettings = new envFullCalendarSettings();
    $envFullCalendarSettings->parseEvent();
    return;
}

class envFullCalendarSettings extends itaModel {

    public $ITALWEB_DB;
    public $envLibCalendar;
    public $utiEnte;
    public $calendarioRowid;
    public $listaCalendari;
    public $nameForm = "envFullCalendarSettings";
    public $divRis = "envFullCalendarSettings_divRisultato";
    public $divGes = "envFullCalendarSettings_divGestione";
    public $gridCalendar = "envFullCalendarSettings_gridCalendar";

    function __construct() {
        parent::__construct();
        try {
            $this->envLibCalendar = new envLibCalendar();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->listaCalendari = App::$utente->getKey($this->nameForm . '_listaCalendari');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_listaCalendari', $this->listaCalendari);
        }
    }

    public function setRowid($rowid) {
        $this->calendarioRowid = $rowid;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Risultato();
                $this->creaSelects();
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridCalendar:
                        if (is_numeric($_POST['rowid'])) {
                            $this->Gestione($_POST['rowid']);
                        } else {
                            Out::msgInfo("", "Non puoi modificare le impostazioni di un calendario esterno");
                        }
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridCalendar:
                        $this->Gestione();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridCalendar:
                        $ita_grid01 = new TableView(
                                $this->gridCalendar, array('arrayTable' => $this->arrayCalendar(), 'rowIndex' => 'idx')
                        );
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->getDataPage('json', true);
                        TableView::enableEvents($this->gridCalendar);
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridCalendar:
                        switch ($_POST['colName']) {
                            case 'CHECKBOX':
                                $id = $_POST['rowid'];
                                $personalCalendars = $this->envLibCalendar->getSelectedCalendars();

                                if ($personalCalendars[$id]['visible'] || $personalCalendars['GOOGLE'][$id]['visible']) {
                                    ( is_numeric($id) ? $personalCalendars[$id]['visible'] = false : $personalCalendars['GOOGLE'][$id]['visible'] = false );
                                    TableView::setCellValue($this->gridCalendar, $id, "CHECKBOX", ' ');
                                } else {
                                    ( is_numeric($id) ? $personalCalendars[$id]['visible'] = true : $personalCalendars['GOOGLE'][$id]['visible'] = true );
                                    TableView::setCellValue($this->gridCalendar, $id, "CHECKBOX", '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>');
                                }

                                if (!$this->envLibCalendar->setSelectedCalendars($personalCalendars)) {
                                    Out::msgStop("Errore", "Aggiornamento profili calendario-utente fallita");
                                    break;
                                }

                                $allDeselected = true;
                                foreach ($personalCalendars as $id => $calendar) {
                                    if ($id == 'GOOGLE') {
                                        foreach ($calendar as $gCal) {
                                            if ($gCal['visible'] == true && $gCal['open'] == true) {
                                                $allDeselected = false;
                                                break 2;
                                            }
                                        }
                                    } else {
                                        if ($calendar['visible'] == true) {
                                            $allDeselected = false;
                                            break;
                                        }
                                    }
                                }

                                if ($allDeselected) {
                                    Out::msgInfo("Attenzione", "Hai deselezionato tutti i calendari, il calendario risulterà vuoto");
                                }

                                Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                                Out::broadcastMessage($this->nameForm, 'AGGIORNATODO');
                                break;
                            case 'VEDITUTTO':
                                $id = $_POST['rowid'];
                                foreach ($this->listaCalendari as $calendario) {
                                    if ($calendario['ROWID'] == $id && $calendario['VEDITUTTO'] != '') {
                                        $model = "envFullCalendarViewer";
                                        itaLib::openDialog($model);
                                        $formObj = itaModel::getInstance($model);
                                        if (!$formObj) {
                                            Out::msgStop("Errore", "apertura dettaglio fallita");
                                            break;
                                        }
                                        $formObj->setReturnModel($this->nameForm);
                                        $formObj->setReturnEvent('returnCalendarViewer');
                                        $formObj->setReturnId('');
                                        $formObj->setRowid($id);
                                        $formObj->setEvent('openform');
                                        $formObj->parseEvent();
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_GRUPPI_PRIV':
                        Out::valore($this->nameForm . '_GRUPPO_R', '0');
                        Out::valore($this->nameForm . '_GRUPPO_W', '0');
                        Out::valore($this->nameForm . '_GRUPPO_X', '0');
                        break;

                    case $this->nameForm . '_ALTRI_PRIV':
                        Out::valore($this->nameForm . '_ALTRI_R', '0');
                        Out::valore($this->nameForm . '_ALTRI_W', '0');
                        Out::valore($this->nameForm . '_ALTRI_X', '0');
                        break;

                    case $this->nameForm . '_Salva':
                        $calendario_rec = $_POST[$this->nameForm . '_CAL_CALENDARI'];
                        $calendario_rec['GRUPPI'] = $_POST[$this->nameForm . '_GRUPPO_R'] . $_POST[$this->nameForm . '_GRUPPO_W'] . $_POST[$this->nameForm . '_GRUPPO_X'] . '0';
                        $calendario_rec['ALTRI'] = $_POST[$this->nameForm . '_ALTRI_R'] . $_POST[$this->nameForm . '_ALTRI_W'] . $_POST[$this->nameForm . '_ALTRI_X'] . '0';
//                        if ($_POST[$this->nameForm . '_GOOGLE']) {
//                            $calendario_rec['METADATI'] = serialize(array('url' => $_POST[$this->nameForm . '_GOOGLE']));
//                            $calendario_rec['TIPO'] = 'GOOGLE';
//                        } else {
//                        }
                        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_CALENDARI', 'ROWID', $calendario_rec);
                        Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                        //Out::closeDialog($this->nameForm);
                        $this->Risultato();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->Gestione();
                        break;

                    case $this->nameForm . '_Torna':
                        $this->Risultato();
                        break;

                    case $this->nameForm . '_ModificaGruppo':
                        Out::msgQuestion("Modifica", "Abilitare la modifica del gruppo di appartenenza del calendario?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaModifica', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaModifica', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;

                    case $this->nameForm . '_ConfermaModifica':
                        Out::hide($this->nameForm . '_ModificaGruppo');
                        Out::attributo($this->nameForm . '_CAL_CALENDARI[GRUPPO]', 'disabled', '1');
                        break;

                    case $this->nameForm . '_Elimina':
                        $calendario_rec = $this->envLibCalendar->getCalendar($_POST[$this->nameForm . '_CAL_CALENDARI']['ROWID']);

                        if ($calendario_rec['TIPO'] == 'APPLICATIVI') {
                            break;
                        }

                        if ($this->envLibCalendar->isCalendarEmpty($calendario_rec['ROWID'])) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaElimina', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaElimina', 'model' => $this->nameForm, 'shortCut' => "f5")
                            ));
                        } else {
                            Out::msgInfo("Attenzione", "Il calendario contiene degli eventi/attività e non può essere eliminato.");
                        }
                        break;

                    case $this->nameForm . '_ConfermaElimina':
                        $calendario_rec = $_POST[$this->nameForm . '_CAL_CALENDARI'];
                        if ($this->envLibCalendar->isCalendarEmpty($calendario_rec['ROWID'])) {
                            $this->envLibCalendar->deleteCalendar($calendario_rec['ROWID']);
                            Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                            $this->Risultato();
                        } else {
                            Out::msgInfo("Attenzione", "Il calendario contiene degli eventi/attività e non può essere eliminato.");
                        }
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $calendario_rec = $_POST[$this->nameForm . '_CAL_CALENDARI'];
                        $calendario_rec['PROPRIETARIO'] = '1110';
                        $calendario_rec['GRUPPI'] = $_POST[$this->nameForm . '_GRUPPO_R'] .
                                $_POST[$this->nameForm . '_GRUPPO_W'] .
                                $_POST[$this->nameForm . '_GRUPPO_X'] . '0';
                        $calendario_rec['ALTRI'] = $_POST[$this->nameForm . '_ALTRI_R'] .
                                $_POST[$this->nameForm . '_ALTRI_W'] .
                                $_POST[$this->nameForm . '_ALTRI_X'] . '0';
                        $calendario_rec['UTENTE'] = App::$utente->getIdUtente();
//                        if ($_POST[$this->nameForm . '_GOOGLE']) {
//                            $calendario_rec['METADATI'] = serialize(array('url' => $_POST[$this->nameForm . '_GOOGLE']));
//                            $calendario_rec['TIPO'] = 'GOOGLE';
//                            $calendario_rec['PROPRIETARIO'] = '1000';
//                            $calendario_rec['GRUPPI'] = $_POST[$this->nameForm . '_GRUPPO_R'] . '000';
//                            $calendario_rec['ALTRI'] = $_POST[$this->nameForm . '_ALTRI_R'] . '000';
//                        }
                        ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_CALENDARI', 'ROWID', $calendario_rec);
                        Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                        $this->Risultato();
                        break;
                }
                break;

            case 'returnColorpicker':
                $id = $_POST[$this->gridCalendar]['gridParam']['selrow'];
                $personalCalendars = $this->envLibCalendar->getSelectedCalendars();
                if ($personalCalendars['GOOGLE'][$id]) {
                    $personalCalendars['GOOGLE'][$id]['color'] = $_POST['colorPicked'];
                } else {
                    if (!$personalCalendars[$id])
                        $personalCalendars[$id] = array();
                    $personalCalendars[$id]['color'] = $_POST['colorPicked'];
                }
                $this->envLibCalendar->setSelectedCalendars($personalCalendars);
                Out::broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_listaCalendari');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function creaSelects() {
        Out::select($this->nameForm . '_CAL_CALENDARI[GRUPPO]', 1, '0', 0, ' ');
        foreach ($this->envLibCalendar->getUserGroups() as $group) {
            Out::select($this->nameForm . '_CAL_CALENDARI[GRUPPO]', 1, $group['GRUPPO'], 0, $group['DESCRI']);
        }
    }

    public function Risultato() {
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->divRis);
        Out::show($this->nameForm . '_Nuovo');

        $ita_grid01 = new TableView(
                $this->gridCalendar, array('arrayTable' => $this->arrayCalendar(), 'rowIndex' => 'idx')
        );
        $ita_grid01->setSortIndex('UTENTE');
        $ita_grid01->setSortOrder('asc');
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(999);
        $ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridCalendar);
    }

    private function arrayCalendar() {
        $calendarsList = $this->envLibCalendar->getCalendars('1___');
        $personalCalendars = $this->envLibCalendar->getSelectedCalendars();
        // $newPersonalCalendar = array();
        foreach ($calendarsList as &$calendar) {
            $gruppi = '';
            if (substr($calendar['GRUPPI'], 0, 1) == '1') {
                if ($gruppi)
                    $gruppi .= ' L';
                else
                    $gruppi = 'L';
            }
            if (substr($calendar['GRUPPI'], 1, 1) == '1') {
                if ($gruppi)
                    $gruppi .= ' M';
                else
                    $gruppi = 'M';
            }
            if (substr($calendar['GRUPPI'], 2, 1) == '1') {
                if ($gruppi)
                    $gruppi .= ' C';
                else
                    $gruppi = 'C';
            }
            $altri = '';
            if (substr($calendar['ALTRI'], 0, 1) == '1') {
                if ($altri)
                    $altri .= ' L';
                else
                    $altri = 'L';
            }
            if (substr($calendar['ALTRI'], 1, 1) == '1') {
                if ($altri)
                    $altri .= ' M';
                else
                    $altri = 'M';
            }
            if (substr($calendar['ALTRI'], 2, 1) == '1') {
                if ($altri)
                    $altri .= ' C';
                else
                    $altri = 'C';
            }

            if ($personalCalendars[$calendar['ROWID']]['visible']) {
                $calendar['CHECKBOX'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
            } else {
                $calendar['CHECKBOX'] = ' ';
            }

            $calendar['GRUPPI'] = $gruppi;
            $calendar['ALTRI'] = $altri;

            if (!$personalCalendars[$calendar['ROWID']]['color']) {
                $personalCalendars[$calendar['ROWID']]['color'] = '#3b91ad';
            }

            $color = $personalCalendars[$calendar['ROWID']]['color'];

            $calendar['COLOR'] = '<div class="ita-html" style="width: 90%; height: 70%; margin: auto; cursor: pointer;"><div class="ita-colorpicker {type: \'divColor\'}" style="width: 100%; height: 100%; background-color: ' . $color . '"></div></div>';

            if ($calendar['TIPO'] == 'APPLICATIVI') {
                $calendar['TITOLO'] .= ' <span class="ui-icon ui-icon-gear" style="vertical-align: bottom; display: inline-block;"></span>';
            }

            if ($calendar['TIPO'] == '' && $calendar['IDUTENTE'] == App::$utente->getIdUtente()) {
                $calendar['VEDITUTTO'] = ' <span class="ui-icon ui-icon-newwin" style="vertical-align: bottom; display: inline-block;"></span>';
            } else {
                $calendar['VEDITUTTO'] = '';
            }
            $this->listaCalendari = $calendarsList;

            // $newPersonalCalendar[$calendar['ROWID']] = $personalCalendars[$calendar['ROWID']];
        }
        $this->envLibCalendar->setSelectedCalendars($personalCalendars);

        if (isset($personalCalendars['GOOGLE']) && is_array($personalCalendars['GOOGLE'])) {
            foreach ($personalCalendars['GOOGLE'] as $gCalendar) {
                if ($gCalendar['open']) {
                    array_push($calendarsList, array(
                        'ROWID' => $gCalendar['id'],
                        'TITOLO' => $gCalendar['name'],
                        'UTENTE' => 'Google',
                        'CHECKBOX' => ( $gCalendar['visible'] ? '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>' : ' ' ),
                        'COLOR' => '<div class="ita-html" style="width: 90%; height: 70%; margin: auto; cursor: pointer;"><div class="ita-colorpicker {type: \'divColor\'}" style="width: 100%; height: 100%; background-color: ' . $gCalendar['color'] . '"></div></div>'
                    ));
                }
            }
        }

        return $calendarsList;
    }

    public function Gestione($id) {

        if ($id) {
            $calendario_rec = $this->envLibCalendar->getCalendar($id);

            if ($calendario_rec['UTENTE'] !== App::$utente->getIdUtente()) { // || $calendario_rec['TIPO'] == 'APPLICATIVI') {
                Out::msgInfo("", "Puoi modificare solo i tuoi calendari");
                return;
            }

            $meta = unserialize($calendario_rec['METADATI']);
            $this->AzzeraVariabili();
            $this->Nascondi();
            Out::show($this->divGes);
            Out::show($this->nameForm . '_Salva');
            Out::show($this->nameForm . '_Elimina');
            Out::show($this->nameForm . '_Torna');

            if ($calendario_rec['TIPO'] == 'GOOGLE') {
                Out::hide($this->nameForm . '_GRUPPO_W_field');
                Out::hide($this->nameForm . '_GRUPPO_X_field');
                Out::hide($this->nameForm . '_ALTRI_W_field');
                Out::hide($this->nameForm . '_ALTRI_X_field');
                //Out::show($this->nameForm . '_GOOGLE_field');
                //Out::attributo($this->nameForm . '_GOOGLE', 'readonly', '0');
                //Out::addClass($this->nameForm . '_GOOGLE', 'ita-readonly');
            } else if ($calendario_rec['TIPO'] == 'APPLICATIVI') {
                Out::hide($this->nameForm . '_Elimina');
            } else {
                Out::show($this->nameForm . '_GRUPPO_W_field');
                Out::show($this->nameForm . '_GRUPPO_X_field');
                Out::show($this->nameForm . '_ALTRI_W_field');
                Out::show($this->nameForm . '_ALTRI_X_field');
                //Out::hide($this->nameForm . '_GOOGLE_field');
            }

            Out::show($this->nameForm . '_ModificaGruppo');
            Out::attributo($this->nameForm . '_CAL_CALENDARI[GRUPPO]', 'disabled', '0', 'disabled');

            Out::valore($this->nameForm . '_PROPRIETARIO', $this->envLibCalendar->getNomeUtente($calendario_rec['UTENTE']));

            Out::valori($calendario_rec, $this->nameForm . '_CAL_CALENDARI');
            Out::valore($this->nameForm . '_GRUPPO_R', substr($calendario_rec['GRUPPI'], 0, 1));
            Out::valore($this->nameForm . '_GRUPPO_W', substr($calendario_rec['GRUPPI'], 1, 1));
            Out::valore($this->nameForm . '_GRUPPO_X', substr($calendario_rec['GRUPPI'], 2, 1));
            Out::valore($this->nameForm . '_ALTRI_R', substr($calendario_rec['ALTRI'], 0, 1));
            Out::valore($this->nameForm . '_ALTRI_W', substr($calendario_rec['ALTRI'], 1, 1));
            Out::valore($this->nameForm . '_ALTRI_X', substr($calendario_rec['ALTRI'], 2, 1));
            //Out::valore($this->nameForm . '_GOOGLE', $meta['url']);
        } else {

            $this->AzzeraVariabili();
            $this->Nascondi();
            Out::show($this->divGes);
            Out::show($this->nameForm . '_Aggiungi');
            Out::show($this->nameForm . '_Torna');

            Out::show($this->nameForm . '_GRUPPO_W_field');
            Out::show($this->nameForm . '_GRUPPO_X_field');
            Out::show($this->nameForm . '_ALTRI_W_field');
            Out::show($this->nameForm . '_ALTRI_X_field');

            Out::valore($this->nameForm . '_PROPRIETARIO', $this->envLibCalendar->getNomeUtente());

            Out::hide($this->nameForm . '_ModificaGruppo');
            Out::attributo($this->nameForm . '_CAL_CALENDARI[GRUPPO]', 'disabled', '1');
            //Out::show($this->nameForm . '_GOOGLE_field');
            //Out::attributo($this->nameForm . '_GOOGLE', 'readonly', '1');
            //Out::delClass($this->nameForm . '_GOOGLE', 'ita-readonly');
        }

        Out::setFocus($this->nameForm, $this->nameForm . '_CAL_CALENDARI[TITOLO]');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    public function Nascondi() {
        Out::hide($this->divGes);
        Out::hide($this->divRis);
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Salva');
        Out::hide($this->nameForm . '_Elimina');
        Out::hide($this->nameForm . '_Torna');
    }

}

?>