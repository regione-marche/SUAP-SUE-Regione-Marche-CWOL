<?php

/**
 *
 * VISUALIZZA Notifiche
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    05.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

function envViewerNotice() {
    $envViewerNotice = new envViewerNotice();
    $envViewerNotice->parseEvent();
    return;
}

class envViewerNotice extends itaModel {

    public $nameForm = "envViewerNotice";
    public $envLib;
    public $ITALWEB_DB;

    function __construct() {
        parent::__construct();
        $this->envLib = new envLib();
        $this->ITALWEB_DB = $this->envLib->getITALWEB_DB();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (isset($_POST['rowid'])) {
                    Out::valore($this->nameForm . '_ENV_NOTIFICHE[ROWID]', $_POST['rowid']);
                    $env_notifiche = $this->envLib->getGenericTab("SELECT * FROM ENV_NOTIFICHE WHERE ROWID = {$_POST['rowid']}", false);
                    $utente = App::$utente->getKey('nomeUtente');
                    if ($env_notifiche['UTEDEST'] == $utente) {
                        $data = date("Ymd");
                        $ora = date("H:i:s");
                        if ($env_notifiche['DATAVIEW'] == '') {
                            $env_notifiche['DATAVIEW'] = $data;
                            $env_notifiche['ORAVIEW'] = $ora;
                            $update_Info = 'Oggetto notifica viewed: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
                            $this->updateRecord($this->ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $update_Info);
                        }
                        if ($env_notifiche['DATADELIV'] == '') {
                            $env_notifiche['DATADELIV'] = $data;
                            $env_notifiche['ORADELIV'] = $ora;
                            $update_Info = 'Oggetto notifica delivered: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
                            $this->updateRecord($this->ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $update_Info);
                        }
                    }
                    $dataVis = '';
                    if ($env_notifiche['DATAVIEW']) {
                        $dataVis = date("d/m/Y", strtotime($env_notifiche['DATAVIEW']));
                    }
                    $message1 = "
                        <div>{$env_notifiche['OGGETTO']}</div>";
                    $message2 = '
                        <div>
                            <table border="0">
                                <tr>
                                    <td style="width:150px"><b> Inserito da: </b></td><td style="width:485px"><b> ' . $env_notifiche['UTEINS'] . ' </b></td>
                                </tr>
                                <tr>
                                    <td style="width:150px"> Data di inserimento: </td><td style="width:485px"> ' . date("d/m/Y", strtotime($env_notifiche['DATAINS'])) . ' </td>
                                </tr>
                                <tr>
                                    <td style="width:150px"> Ora di inserimento: </td><td style="width:485px"> ' . $env_notifiche['ORAINS'] . ' </td>
                                </tr>
                                <tr>
                                    <td style="width:150px"> Data di visualizzazione: </td><td style="width:485px"> ' . $dataVis . ' </td>
                                </tr>
                                <tr>
                                    <td style="width:150px"> Ora di visualizzazione: </td><td style="width:485px"> ' . $env_notifiche['ORAVIEW'] . ' </td>
                                </tr>
                                ';

                    if ($env_notifiche['MAILTOSEND'] == 1) {
                        $mailtosend='<b>Si</b>';
                        $message2 .= '
                            <tr>
                                <td style="width:150px"><b> Mail da inviare: </b></td><td style="width:485px"> ' . $mailtosend . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> N tentativi invio: </td><td style="width:485px"> ' . $env_notifiche['MAILSENDATTEMPT'] . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> Errori invio mail: </td><td style="width:485px"> ' . $env_notifiche['MAILSENDERR'] . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> Ultimo tentativo: </td><td style="width:485px"> ' . $env_notifiche['MAILSENDMSG'] . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> Avviso inviato a: </td><td style="width:485px"> ' . $env_notifiche['MAILDEST'] . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> Data invio Mail: </td><td style="width:485px"> ' . date("d/m/Y", strtotime($env_notifiche['MAILDATE'])) . ' </td>
                            </tr>
                            <tr>
                                <td style="width:150px"> Ora invio Mail: </td><td style="width:485px"> ' . $env_notifiche['MAILTIME'] . ' </td>
                            </tr>';
                    }
                    $message2 .= '</table></div>';

                    if (( ( $env_notifiche['ACTIONMENU'] && $env_notifiche['ACTIONPROG'] ) || ( $env_notifiche['ACTIONMODEL'] ) ) && $env_notifiche['ACTIONPARAM']) {
                        $button = '<button class="ita-tooltip ita-button-validate ita-element-animate ui-corner-all ui-state-default" name="envViewerNotice_launchApp" id="envViewerNotice_launchApp" type="button" style="float: right; margin: -14px 2px 0 0;" title="' . htmlspecialchars('Vai al dettaglio:<br>' . $env_notifiche['OGGETTO']) . '"><span class="ita-icon ita-icon-ingranaggio-24x24"></span></button>';
                    }

                    Out::html($this->nameForm . "_divInfo1", $message1 . $button);
                    Out::valore($this->nameForm . "_Testo", $env_notifiche['TESTO']);
                    Out::html($this->nameForm . "_divInfo2", $message2);
                } else {
                    Out::msgStop("Attenzione!", "Riferimenti alla notifica sbagliati.");
                    parent::close();
                    Out::closeDialog($this->nameForm);
                    break;
                }
                Out::setFocus('', $this->nameForm . '_oggetto');
                $this->envLib->setNoticeCounter(App::$utente->getKey('nomeUtente'));
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_launchApp':
                        $env_id = $_POST[$this->nameForm . '_ENV_NOTIFICHE']['ROWID'];
                        $this->launchNoticeApp($env_id);
                        $this->close();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    private function launchNoticeApp($id) {
        if ($id) {
            $env_notifiche = $this->envLib->getGenericTab("SELECT * FROM ENV_NOTIFICHE WHERE ROWID = $id", false);
            $model = $env_notifiche['ACTIONMODEL'];
            itaLib::openForm($model);
            //itaLib::openDialog($model);
            $formObj = itaModel::getInstance($model);
            if (!$formObj) {
                Out::msgStop("Errore", "apertura dettaglio fallita");
                return;
            }
            $formObj->setReturnModel($this->nameForm);
            $formObj->setReturnEvent('returnCalendarEvent');
            $formObj->setReturnId('');
            $formObj->setModelParam($env_notifiche['ACTIONPARAM']);
            $formObj->setEvent('openform');
            $formObj->parseEvent();
        }
    }

}

?>