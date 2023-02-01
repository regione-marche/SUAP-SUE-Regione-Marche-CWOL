<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praNote.class.php';

function praSubPassoNote() {
    $praSubPassoNote = new praSubPassoNote();
    $praSubPassoNote->parseEvent();
    return;
}

class praSubPassoNote extends praSubPasso {
    public $nameForm = 'praSubPassoNote';
    public $noteManager;
    public $PRAM_DB;

    function __construct() {
        parent::__construct();
    }

    public function postInstance() {
        parent::postInstance();
        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
        $this->PRAM_DB = $this->praLib->getPRAMDB();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Dettaglio($this->keyPasso);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridNote':
                        $this->openFormAddNota();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridNote':
                        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROPAS, $this->keyPasso);
                        $this->caricaNote();
                        break;
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridNote':
                        $this->openFormEditNota();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridNote':
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                            Out::msgStop("Attenzione!", "Solo l'utente " . App::$utente->getKey('nomeUtente') . " è abilitato alla modifica della Nota.");
                            break;
                        }
                        $this->noteManager->cancellaNota($_POST['rowid']);
                        $this->noteManager->salvaNote();
                        $this->caricaNote();
                        break;
                }

                break;


            case 'returnPraDettNote':
                $this->returnFormAddNota();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_noteManager');
    }

    public function returnToParent($propak, $close = true) {
        parent::returnToParent($close);
    }

    public function Nuovo($rowid, $tipo = 'propak') {
        
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->propas_rec = $this->praLib->getPropas($this->keyPasso, 'propak');
        $this->AzzeraVariabili();
        $this->Nascondi();
        $this->noteManager = praNoteManager::getInstance($this->praLib, praNoteManager::NOTE_CLASS_PROPAS, $this->keyPasso);
        $this->CaricaNote();

        //DA CAPIRE $this->praReadOnly

        /*
         * Modalità di apertura in sola lettura
         */

        // @TODO: verificare la valorizzazione della variabile nell'oggetto padre.
        if ($this->praReadOnly == true) {
            $this->HideButton();
        }
    }

    private function AzzeraVariabili() {
        $this->noteManager = null;
    }

    private function Nascondi() {
        
    }

    function HideButton() {
        Out::hide($this->nameForm . '_gridNote' . '_delGridRow');
        Out::hide($this->nameForm . '_gridNote' . '_addGridRow');
    }

    function openFormAddNota() {
        $destinatari = array();
        $propas_tab = $this->praLib->getGenericTab("SELECT DISTINCT(PRORPA) FROM PROPAS WHERE PRONUM ='$this->currGesnum' AND PRORPA<>''", true);
        foreach ($propas_tab as $propas_rec) {
            $destinatari[] = $this->praLib->getGenericTab("SELECT " . $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS DESTINATARIO, NOMRES FROM ANANOM WHERE NOMRES='{$propas_rec['PRORPA']}'", false);
        }
        $model = 'praDettNote';
        itaLib::openForm($model);
        /* @var $formObj itaModel */
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnModelOrig($this->nameFormOrig);
        $formObj->setReturnEvent('returnPraDettNote');
        $formObj->setReturnId('');
        $_POST = array();
        $_POST['destinatari'] = $destinatari;
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

    function openFormEditNota() {
        $dati = $this->noteManager->getNota($_POST['rowid']);
        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
            Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
            return;
        }
        $destinatari = array();
        $propas_tab = $this->praLib->getGenericTab("SELECT DISTINCT(PRORPA) FROM PROPAS WHERE PRONUM ='$this->currGesnum' AND PRORPA<>''", true);
        foreach ($propas_tab as $propas_rec) {
            $destinatari[] = $this->praLib->getGenericTab("SELECT " . $this->PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS DESTINATARIO, NOMRES FROM ANANOM WHERE NOMRES='{$propas_rec['PRORPA']}'", false);
        }
        $model = 'praDettNote';
        itaLib::openForm($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnModelOrig($this->nameFormOrig);
        $formObj->setReturnEvent('returnPraDettNote');
        $formObj->setReturnId('');
        $rowid = $_POST['rowid'];
        $_POST = array();
        if ($this->visualizzazione) {
            $_POST['readonly'] = true;
        }
        $_POST['dati'] = $dati;
        $_POST['rowid'] = $rowid;
        $_POST['destinatari'] = $destinatari;
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

    private function CaricaNote() {
        $datiGrigliaNote = array();
        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $datiGrigliaNote[$key]['NOTE'] = '<div style="margin: 3px 3px 3px 3px;"><div style="font-size: 9px;font-weight:bold; color: grey;">' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] .
                    "</div><div class='ita-Wordwrap'><b>" . $nota['OGGETTO'] . '</b>';
            if ($nota['TESTO']) {
                $testo = $nota['TESTO'];
                $datiGrigliaNote[$key]['NOTE'] .= '<div title="' . $nota['TESTO'] . '" style="font-size: 11px;">' . $testo . '</div>';
            }
            $datiGrigliaNote[$key]['NOTE'] .= '</div></div>';
        }

        $this->caricaGriglia($this->nameForm . '_gridNote', $datiGrigliaNote);
    }

    function returnFormAddNota() {
        $dati = array(
            'OGGETTO' => $_POST['oggetto'],
            'TESTO' => $_POST['testo'],
            'CLASSE' => praNoteManager::NOTE_CLASS_PROPAS,
            'CHIAVE' => $this->keyPasso
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
        $this->CaricaNote();

        /*
         * Aggiorno dal POST il record di PROPAS solo in presenza del parametro GOBID
         */
        Out::valore($this->nameForm . "_PROPAS[PROANN]", $dati['OGGETTO']);
        $propas_rec = $this->formData[$this->nameForm . '_PROPAS'];
        $propas_rec['PROANN'] = $dati['OGGETTO'];
        $update_Info = "Oggetto: aggiorno annotazioni passo da oggetto Nota";
        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore", "Errore aggiornamento passo da oggetto nota");
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '20') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
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

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();
        $oggetto_notifica = "$tipoNotifica UNA NOTA AL PASSO SEQ. " . $this->propas_rec['PROSEQ'] . ", PRATICA NUMERO $this->currGesnum";
        $testo_notifica = $oggetto;
        $dati_extra = array();
        $dati_extra['ACTIONMODEL'] = $this->returnModel;
        $dati_extra['ACTIONPARAM'] = serialize(array('setOpenMode' => array('edit'), 'setOpenRowid' => array($this->propas_rec['ROWID'])));
        $envLib->inserisciNotifica($this->nameform, $oggetto_notifica, $testo_notifica, $uteins, $dati_extra);
        return;
    }
}