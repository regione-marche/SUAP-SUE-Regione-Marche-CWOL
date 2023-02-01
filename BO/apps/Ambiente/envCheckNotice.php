<?php

/**
 *
 * Controllo Notifiche
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    05.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibNotifiche.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function envCheckNotice() {
    $envCheckNotice = new envCheckNotice();
    $envCheckNotice->parseEvent();
    return;
}

class envCheckNotice extends itaModel {

    public $nameForm = "envCheckNotice";
    public $elencoNotifiche;
    public $envLib;
    public $accLib;
    public $envLibCalendar;
    public $ITALWEB_DB;
    public $MaxDaVisualizzare = 0;

    function __construct() {
        parent::__construct();
        $this->envLib = new envLib();
        $this->accLib = new accLib();
        $this->envLibCalendar = new envLibCalendar();
        $this->ITALWEB_DB = $this->envLib->getITALWEB_DB();
        $this->elencoNotifiche = App::$utente->getKey($this->nameForm . '_elencoNotifiche');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_elencoNotifiche', $this->elencoNotifiche);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'ontimer':
                $this->envLibCalendar->checkPromemoriaCalendarioNotifiche();
                $this->caricaNotifiche();
                $this->envLib->setNoticeCounter(App::$utente->getKey('nomeUtente'));
                $this->envLib->checkUnsentMail(App::$utente->getKey('nomeUtente'));
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    default:
                        if (substr($_POST['id'], 0, 21) == $this->nameForm . '_close_') {
                            $rowid = substr($_POST['id'], 21);
                            $this->setDelivered($rowid);
                            Out::removeElement($this->nameForm . '_noticeContainer_' . $rowid);
                            $this->caricaNotifiche(0, false, false);
                        } else if (substr($_POST['id'], 0, 29) == $this->nameForm . '_noticeOggetto_') {
                            $rowid = substr($_POST['id'], 29);
                            Out::removeElement($this->nameForm . '_noticeContainer_' . $rowid);
                            App::log($rowid);
                            $model = 'envViewerNotice';
                            itaLib::openForm($model);
                            $envViewerNotice = itaModel::getInstance($model);
                            $envViewerNotice->setReturnModel('');
                            $envViewerNotice->setReturnEvent('');
                            $envViewerNotice->setReturnId('');
                            $_POST = array();
                            $_POST['rowid'] = $rowid;
                            $envViewerNotice->setEvent('openform');
                            $envViewerNotice->parseEvent();
                        } else if (substr($_POST['id'], 0, 36) == $this->nameForm . '_noticeVisualizzaMore_') {
                            $num = substr($_POST['id'], 36);
                            $this->caricaNotifiche($num + 1, true, true);
                        }
                        break;
                }
                break;
        }
    }

    private function caricaNotifiche($numVisualizzatoOrig = 0, $forzaCarica = false, $effect = true) {
        $html = '';
        $env_notifiche_tab = $this->checkDaAggiornare($forzaCarica);
        if ($env_notifiche_tab == 'non-ricaricare') {
            return false;
        }
        //Controllo max utente:
        $idUtente = App::$utente->getKey('idUtente');
        $paramNotifiche_rec = $this->accLib->GetEnv_Utemeta($idUtente, 'codice', 'ParmNotifiche');
        if ($paramNotifiche_rec) {
            $meta = unserialize($paramNotifiche_rec['METAVALUE']);
            $notifiche = $meta['Notifiche'];
            if ($notifiche['MaxNumNotifiche']) {
                if ($notifiche['MaxNumNotifiche'] > envLibNotifiche::NOTIFCHE_VIEW_MAX_DEFAULT) {
                    $this->MaxDaVisualizzare = envLibNotifiche::NOTIFCHE_VIEW_MAX_DEFAULT;
                } else if ($notifiche['MaxNumNotifiche'] < 0) {
                    $this->MaxDaVisualizzare = 0;
                } else {
                    $this->MaxDaVisualizzare = $notifiche['MaxNumNotifiche'];
                }
            }
        }
        if ($env_notifiche_tab && $this->MaxDaVisualizzare) {
            if (count($env_notifiche_tab) > $this->MaxDaVisualizzare) {
                $numVis = $numVisualizzatoOrig;
                if ($forzaCarica === true) {
                    if ($numVis >= count($env_notifiche_tab) - 1) {
                        $numVisualizzatoOrig = $numVis = 0;
                    } else {
                        $numVis++;
                    }
                } else {
                    $numVisualizzatoOrig = $numVisualizzatoOrig - 1;
                }

                $DaVisualizzare = $this->MaxDaVisualizzare - 1;
                for ($i = 0; $i < $DaVisualizzare - 1; $i++) {
                    $html .= $this->getContainer($env_notifiche_tab[$i]);
                }
                $differenza = count($env_notifiche_tab) - ($this->MaxDaVisualizzare - 1);

                $html .= $this->getContainer($env_notifiche_tab[$numVis]);
                $html .= '<div id="' . $this->nameForm . '_noticeContainerMore_' . $numVisualizzatoOrig . '" class="ita-noticeLabel ui-corner-all">';
                $html .= '<span>Ci sono altri ' . $differenza . ' avvisi da visualizzare.</span><br>';
                //               $html .= '<br><br><br> <a href="#" id="' . $this->nameForm . '_noticeVisualizzaMore_' . $numVisualizzatoOrig . '" class="ita-hyperlink" style="font-weight:bold"> VISUALIZZA ALTRE NOTIFICHE </a><br><br><br></div>';
            } else {
                foreach ($env_notifiche_tab as $env_notifiche_rec) {
                    $html .= $this->getContainer($env_notifiche_rec);
                }
            }
            if ($effect)
                Out::codice("$('#" . $this->nameForm . "').effect('slide', { direction: 'down', mode: 'hide' }, 1000);");
            Out::html($this->nameForm . "_noticePad", $html);
            if ($effect)
                Out::codice("$('#" . $this->nameForm . "').effect('slide', { direction: 'down', mode: 'show' }, 1000);");
        } else {
            Out::codice("$('#" . $this->nameForm . "').effect('slide', { direction: 'down', mode: 'hide' }, 1000);");
        }
    }

    private function checkDaAggiornare($forzaCarica) {
        $risultato = 'non-ricaricare';
        $utente = App::$utente->getKey('nomeUtente');
        $sql = "SELECT * FROM ENV_NOTIFICHE WHERE UTEDEST='$utente' AND DATAVIEW='' AND DATADELIV=''";
        $env_notifiche_tab = $this->envLib->getGenericTab($sql, true);
        if ($env_notifiche_tab != $this->elencoNotifiche || $forzaCarica === true) {
            $risultato = $env_notifiche_tab;
        }
        $this->elencoNotifiche = $env_notifiche_tab;
        return $risultato;
    }

    private function setDelivered($rowid) {
        $env_notifiche = $this->envLib->getGenericTab("SELECT ROWID, OGGETTO, UTEDEST FROM ENV_NOTIFICHE WHERE ROWID = $rowid", false);
        $env_notifiche['DATADELIV'] = date("Ymd");
        $env_notifiche['ORADELIV'] = date("H:i:s");
        $update_Info = 'Oggetto notifica delivered: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
        $this->updateRecord($this->ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $update_Info);
    }

    private function getContainer($env_notifiche_rec) {
        $html = '<div id="' . $this->nameForm . '_noticeContainer_' . $env_notifiche_rec['ROWID'] . '" class="ita-noticeContent ui-corner-all">';
        $html .= '<div style="float:left;" class="ita-icon ita-icon-chiusagreen-24x24"></div><div style="float: left;color: pink;">&nbsp;&nbsp;&nbsp; Avviso</div>';
        $html .= '<div style="float:right;">
                            <button style="width:18px;height:18px;" 
                            class="ita-button ita-element-animate ui-corner-all ui-state-default ui-button-icon-primary ui-icon ui-icon-closethick"
                            id="' . $this->nameForm . '_close_' . $env_notifiche_rec['ROWID'] . '"></button></div>';
        $html .= '<br><br><a href="#" id="' . $this->nameForm . '_noticeOggetto_' . $env_notifiche_rec['ROWID'] . '" class="ita-hyperlink" style="font-weight:bold">' . $env_notifiche_rec['OGGETTO'] . '</a><br>';
        $html .= "<span >{$env_notifiche_rec['TESTO']}</span>";
        $html .= "</div>";
        return $html;
    }
}

?>
