<?php

/**
 *
 * GESTIONE Note
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    06.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';

function proDettNote() {
    $proDettNote = new proDettNote();
    $proDettNote->parseEvent();
    return;
}

class proDettNote extends itaModel {

    public $PROT_DB;
    public $nameForm = "proDettNote";
    public $proLib;
    public $gridDestinatari = "proDettNote_gridDestinatari";
    public $gridNote = "proDettNote_gridNote";
    public $destinatari;
    public $originale;
    public $noteManager;
    public $locale;
    public $oggettoNotifica;
    public $readonly;
    public $extraDati;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
            $this->originale = App::$utente->getKey($this->nameForm . '_originale');
            $this->locale = App::$utente->getKey($this->nameForm . '_locale');
            $this->oggettoNotifica = App::$utente->getKey($this->nameForm . '_oggettoNotifica');
            $this->readonly = App::$utente->getKey($this->nameForm . '_readonly');
            $this->extraDati = App::$utente->getKey($this->nameForm . '_extraDati');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_originale', $this->originale);
            App::$utente->setKey($this->nameForm . '_locale', $this->locale);
            App::$utente->setKey($this->nameForm . '_oggettoNotifica', $this->oggettoNotifica);
            App::$utente->setKey($this->nameForm . '_readonly', $this->readonly);
            App::$utente->setKey($this->nameForm . '_extraDati', $this->extraDati);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        $this->readonly = $_POST['readonly'];
        if ($this->readonly) {
            Out::hide($this->gridNote . "_addGridRow");
            Out::hide($this->gridNote . "_editGridRow");
            Out::hide($this->nameForm . "_Registra");
            Out::hide($this->nameForm . "_divDestGrid");
            Out::attributo($this->nameForm . '_oggetto', 'readonly', '0', 'readonly');
            Out::attributo($this->nameForm . '_testo', 'readonly', '0', 'readonly');
            Out::attributo($this->nameForm . '_testo', 'rows', '0', '14');
        }
        switch ($_POST['event']) {
            case 'openform':
                $this->destinatari = $_POST['destinatari'];
                $this->oggettoNotifica = $_POST['oggettoNotifica'];
                $this->extraDati = $_POST['extraDati'];
                if ($_POST['elenca']) {
                    $this->noteManager = proNoteManager::getInstance($this->proLib, $_POST['class'], $_POST['chiave']);
                    $this->caricaNote();
                    $this->locale = true;
                } else {
                    $this->locale = false;
                    $this->dettaglio($_POST['rowid'], $_POST['dati'], $_POST['destinatari']);
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Registra':
                        $codiciMed = split(",", $_POST[$this->gridDestinatari]['gridParam']['selarrrow']);
                        $codDestinatari = array();
                        foreach ($codiciMed as $idmedcod) {
                            if ($idmedcod != '') {
                                $utente = $this->proLib->getLoginDaMedcod($this->destinatari[$idmedcod]['MEDCOD']);
                                if ($utente) {
                                    $codDestinatari[] = $utente['UTELOG'];
                                }
                            }
                        }
                        $_POST = array();
                        $_POST['oggetto'] = $this->formData["{$this->nameForm}_oggetto"];
                        $_POST['testo'] = $this->formData["{$this->nameForm}_testo"];
                        $_POST['destinatari'] = $codDestinatari;
                        $_POST['NOTE_ROWID'] = $this->formData["{$this->nameForm}_NOTE_ROWID"];
                        $_POST['NON_AGGIORNA'] = false;
                        if ($this->originale['OGGETTO'] == $_POST['oggetto'] && $this->originale['TESTO'] == $_POST['testo']) {
                            $_POST['NON_AGGIORNA'] = true;
                        }
                        if ($this->locale == false) {
                            $returnObj = itaModel::getInstance($this->returnModel);
                            $returnObj->setEvent($this->returnEvent);
                            $returnObj->parseEvent();
                            $this->returnToParent();
                        } else {
                            Out::msgBlock("", 2000, true, "Nota: " . $this->aggiorna());
                            $this->noteManager->caricaNote();
                            $this->caricaNote();
                            $this->messaggioBroadcast();
                        }

                        break;
                    case $this->nameForm . '_Stampa':
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                            "Titolo" => "Nota del Protocollo",
                            "Info" => $this->originale['INFO'],
                            "Oggetto" => $this->originale['OGGETTO'],
                            "Testo" => $this->originale['TESTO']
                        );
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proDettNote', $parameters);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        $rowid = $_POST['rowid'];
                        $dati = $this->noteManager->getNota($rowid);
                        $destinatari = $this->destinatari;
                        $this->dettaglio($rowid, $dati, $destinatari);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        $this->nuovo($this->destinatari);
                        break;
                    case $this->gridDestinatari:
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnAnamed');
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridNote:
                        break;
                    case $this->gridDestinatari:
                        unset($this->destinatari[$_POST['rowid']]);
                        $this->caricaGriglia($this->gridDestinatari, $this->destinatari);
                        break;
                }
                break;
            case 'printTableToHTML':
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array(
                    "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                    "Titolo" => "Note " . $this->oggettoNotifica,
                    "Sql" => $this->noteManager->getSqlNote()
                );
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proEleNote', $parameters);

                break;
            case 'returnAnamed':
                $this->destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE ROWID={$_POST['retKey']}", false);
                $this->caricaGriglia($this->gridDestinatari, $this->destinatari);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_originale');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_oggettoNotifica');
        App::$utente->removeKey($this->nameForm . '_readonly');
        App::$utente->removeKey($this->nameForm . '_extraDati');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                        $griglia, array('arrayTable' => $appoggio,
                    'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaNote() {
        Out::hide($this->nameForm . '_divDettaglio');
        Out::show($this->nameForm . '_divElenco');
        $datiGrigliaNote = array();
        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $datiGrigliaNote[$key]['NOTE'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 7px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';
            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
                if (strlen($testo) > 45) {
                    $testo = substr($testo, 0, 45);
                }
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 9px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    private function dettaglio($rowid, $dati, $destinatari) {
        Out::show($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divElenco');
        $this->originale = $dati;
        $this->caricaGriglia($this->gridDestinatari, $destinatari);
        $info = "<div>Utente: " . App::$utente->getKey('nomeUtente') . "</div>";
        if ($dati) {
            Out::valore($this->nameForm . '_oggetto', $dati['OGGETTO']);
            Out::valore($this->nameForm . '_testo', $dati['TESTO']);
            Out::valore($this->nameForm . '_NOTE_ROWID', $rowid);
            if ($dati['UTELOGMOD']) {
                $info = "<div>Utente proprietario: " . $dati['UTELOG'] . " - del " . date("d/m/Y", strtotime($dati['DATAINS'])) . " ore " . $dati['ORAINS'] . "</div>" .
                        "<div>Ultima modifica effettuata da: " . $dati['UTELOGMOD'] . " - del " . date("d/m/Y", strtotime($dati['DATAMOD'])) . " ore " . $dati['ORAMOD'] . '</div>';
            } else {
                $info = "<div>Utente: " . $dati['UTELOG'] . " - del " . date("d/m/Y", strtotime($dati['DATAINS'])) . " ore " . $dati['ORAINS'] . '</div>';
            }
        } else {
            Out::hide($this->nameForm . '_Stampa');
        }
        //$this->originale['INFO'] = $info;
        Out::html($this->nameForm . "_divInfo", $info);
        Out::setFocus('', $this->nameForm . '_oggetto');
    }

    private function nuovo($destinatari) {
        Out::show($this->nameForm . '_divDettaglio');
        Out::hide($this->nameForm . '_divElenco');
        $this->originale = array();
        $this->caricaGriglia($this->gridDestinatari, $destinatari);
        $info = "<div>Utente: " . App::$utente->getKey('nomeUtente') . "</div>";
        Out::hide($this->nameForm . '_Stampa');
        Out::html($this->nameForm . "_divInfo", $info);
        Out::setFocus('', $this->nameForm . '_oggetto');
    }

    private function aggiorna() {
        $dati = array(
            'OGGETTO' => $_POST['oggetto'],
            'TESTO' => $_POST['testo'],
            'CLASSE' => $this->noteManager->getClasse(),
            'CHIAVE' => $this->noteManager->getChiave()
        );
        $tipoNotifica = "CARICATA";
        if ($_POST['NON_AGGIORNA'] !== true) {

            if ($_POST['NOTE_ROWID'] === '') {
                $tipoNotifica = "INSERITA";
                $this->noteManager->aggiungiNota($dati);
            } else {
                $tipoNotifica = "MODIFICATA";
                $this->noteManager->aggiornaNota($_POST['NOTE_ROWID'], $dati);
            }
            $this->noteManager->salvaNote();
        }
        foreach ($_POST['destinatari'] as $destinatario) {
            $this->inserisciNotifica($_POST['oggetto'], $destinatario, $tipoNotifica);
        }
        return $tipoNotifica;
    }

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica, $profaskey) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $env_notifiche = array();
        $env_notifiche['OGGETTO'] = "$tipoNotifica UNA NOTA: " . $this->oggettoNotifica;
        $env_notifiche['TESTO'] = $oggetto;
        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
        $env_notifiche['MODELINS'] = $this->nameForm;
        $env_notifiche['DATAINS'] = date("Ymd");
        $env_notifiche['ORAINS'] = date("H:i:s");
        $env_notifiche['UTEDEST'] = $uteins;
        $insert_Info = 'Oggetto notifica: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
        $this->insertRecord($ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $insert_Info);
    }

    private function messaggioBroadcast() {
        $classe = $this->noteManager->getClasse();
        $chiave = $this->noteManager->getChiave();
        Out::broadcastMessage($this->nameForm, 'UPDATE_NOTE_' . $classe, array('PRONUM' => $chiave['PRONUM'], 'PROPAR' => $chiave['PROPAR'], 'EXTRADATA' => $this->extraDati['extraData']));
    }

}

?>