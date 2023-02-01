<?php

/**
 *
 * GESTIONE Note
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    17.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praNote.class.php';

function praDettNote() {
    $praDettNote = new praDettNote();
    $praDettNote->parseEvent();
    return;
}

class praDettNote extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praDettNote";
    public $praLib;
    public $gridDestinatari = "praDettNote_gridDestinatari";
    public $gridNote = "praDettNote_gridNote";
    public $destinatari;
    public $originale;
    public $noteManager;
    public $locale;
    public $oggettoNotifica;
    public $readonly;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
            $this->originale = App::$utente->getKey($this->nameForm . '_originale');
            $this->locale = App::$utente->getKey($this->nameForm . '_locale');
            $this->oggettoNotifica = App::$utente->getKey($this->nameForm . '_oggettoNotifica');
            $this->readonly = App::$utente->getKey($this->nameForm . '_readonly');
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
                if ($_POST['elenca']) {
                    $this->noteManager = praNoteManager::getInstance($this->praLib, $_POST['class'], $_POST['chiave']);
                    $this->caricaNote();
                    $this->locale = true;
                } else {
                    $this->locale = false;
                    $this->dettaglio($_POST['rowid'], $_POST['dati'], $_POST['destinatari']);
                    if ($_POST['dati']['ROWID'] != 0) {
                        $noteClas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM NOTECLAS WHERE ROWIDNOTE=" . $_POST['dati']['ROWID'], false);
                        switch ($noteClas_rec['CLASSE']) {
                            case "PROGES":
                                $this->originale['TITOLO'] = "Nota Della Pratica";
                                break;
                            case "PROPAS":
                                $this->originale['TITOLO'] = "Nota Del Passo";
                                break;
                            case "PASDOC":
                                $this->originale['TITOLO'] = "Nota Dell'Allegato";
                                break;
                        }
                    }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Registra':
                        $codiciDest = split(",", $_POST[$this->gridDestinatari]['gridParam']['selarrrow']);
                        $codDestinatari = array();
                        foreach ($codiciDest as $idananom) {
                            if ($idananom != '') {
                                $utente = $this->praLib->getLoginDaNomres($this->destinatari[$idananom]['NOMRES']);
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
                            $returnObj = itaModel::getInstance($this->getReturnModelOrig(), $this->getReturnModel());
                            $returnObj->setEvent($this->returnEvent);
                            $returnObj->parseEvent();
                            $this->returnToParent();
                        } else {
                            Out::msgBlock("", 2000, true, "Nota: " . $this->aggiorna());
                            $this->noteManager->caricaNote();
                            $this->caricaNote();
                        }
                        break;
                    case $this->nameForm . '_Stampa':
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array(
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                            "Titolo" => $this->originale['TITOLO'],
                            "Info" => $this->originale['INFO'],
                            "Oggetto" => $this->originale['OGGETTO'],
                            "Testo" => $this->originale['TESTO']
                        );
                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praDettNote', $parameters);
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
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Dipendenti");
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
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praEleNote', $parameters);

                break;
            case 'returnUnires':
                $this->destinatari[] = $this->praLib->getGenericTab("SELECT " . $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS DESTINATARIO, NOMRES FROM ANANOM WHERE ROWID={$_POST['retKey']}", false);
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
        $this->originale['INFO'] = $info;
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
            $this->inserisciNotifica($_POST['oggetto'], $destinatario, $tipoNotifica, $anapro_rec['PROFASKEY']);
        }
        return $tipoNotifica;
    }

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica, $profaskey) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();
        $oggetto_notifica = "$tipoNotifica UNA NOTA: " . $this->oggettoNotifica;
        $testo_notifica = $oggetto;
        $dati_extra = array();
        $envLib->inserisciNotifica($this->nameform, $oggetto_notifica, $testo_notifica, $uteins, $dati_extra);
        return;
    }

}

?>