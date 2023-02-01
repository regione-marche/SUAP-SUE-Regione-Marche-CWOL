<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    27.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proArriAllegati() {
    $proArriAllegati = new proArriAllegati();
    $proArriAllegati->parseEvent();
    return;
}

class proArriAllegati extends itaModel {

    public $PROT_DB;
    public $nameForm = "proArriAllegati";
    public $gridAllegati = "proArriAllegati_gridAllegati";
    public $proLib;
    public $proLibAllegati;
    public $proArriAlle = array();
    public $IndiceRowid;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->proArriAlle = App::$utente->getKey($this->nameForm . '_proArriAlle');
        $this->IndiceRowid = App::$utente->getKey($this->nameForm . '_IndiceRowid');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proArriAlle', $this->proArriAlle);
            App::$utente->setKey($this->nameForm . '_IndiceRowid', $this->IndiceRowid);
        }
    }

    public function getIndiceRowid() {
        return $this->IndiceRowid;
    }

    public function setIndiceRowid($IndiceRowid) {
        $this->IndiceRowid = $IndiceRowid;
    }

    public function getProArriAlle() {
        return $this->proArriAlle;
    }

    public function setProArriAlle($proArriAlle) {
        $this->proArriAlle = $proArriAlle;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openDettaglio':
                $this->openDettaglio($this->IndiceRowid);
                break;
            case 'openAnaprosave':
                $this->openAnaprosave($this->IndiceRowid);
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    case $this->gridAllegati:


                        if (array_key_exists($_POST['rowid'], $this->proArriAlle) == true) {
                            $doc = $this->proArriAlle[$_POST['rowid']];
                            if (strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION)) !== 'xhtml') {
                                $filepath = $doc['FILEPATH'];
                                $force = false;
                                $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                                if ($ext == 'xml' || $ext == 'eml') {
                                    $force = true;
                                }
                                if (!$this->proLibAllegati->CheckAllegatoDaFirmare($doc['ROWID'])) {
                                    Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                                    break;
                                }
                                /* Se è un allegato provvisorio uso filepath per aprirlo */
                                if (!$doc['ROWID']) {
                                    Out::openDocument(utiDownload::getUrl($doc['NOMEFILE'], $doc['FILEPATH'], $force));
                                    break;
                                }
                                if ($doc['DOCPAR'] == 'I') {
                                    Out::openDocument(utiDownload::getUrl($doc['NOMEFILE'], $doc['FILEPATH'], $force));
                                } else {
                                    if ($doc['ANAPROSAVE']) {
                                        $this->proLibAllegati->OpenDocAllegatoSave($doc['ROWID'], $force);
                                    } else {
                                        $this->proLibAllegati->OpenDocAllegato($doc['ROWID'], $force);
                                    }
                                }
                            }
                        }
                        break;
                }
                break;
            case 'cellSelect':
                switch ($this->elementId) {
                    case $this->gridAllegati:
                        $allegato = $this->proArriAlle[$_POST['rowid']];
                        switch ($_POST['colName']) {
                            case 'DAMAIL':
                                if ($allegato['DOCIDMAIL']) {
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $allegato['DOCIDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'PREVIEW':
                                $ext = strtolower(pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION));
                                if ($ext == 'p7m') {
                                    $DocPath = $this->proLibAllegati->GetDocPath($allegato['ROWID']);
                                    $allegato['FILEPATH'] = $DocPath['DOCPATH'];
                                    $this->proLibAllegati->VisualizzaFirme($allegato['FILEPATH'], $allegato['DOCNAME']);
                                } else {
                                    
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
//                        Out::msginfo('exce', print_r($this->proArriAlle, true));
//                        break;

                        $ArrDati = array();
                        foreach ($this->proArriAlle as $key => $Result_rec) {
                            $ArrDati[$key]['ROWID'] = $Result_rec['ROWID'];
                            $ArrDati[$key]['NOME_FILE'] = $Result_rec['DOCNAME'];
                            $ArrDati[$key]['DESCRIZIONE'] = $Result_rec['DOCNOT'];
                            $ArrDati[$key]['IMPRONTA'] = $Result_rec['DOCMD5'];
                            $ArrDati[$key]['TIPO'] = $Result_rec['DOCTIPO'] ? $Result_rec['DOCTIPO'] : 'PRINCIPALE';
                            $ArrDati[$key]['DATA'] = date("d/m/Y", strtotime($Result_rec['DOCFDT']));
                        }
                        $ita_grid01 = new TableView($this->gridIndice, array(
                            'arrayTable' => $ArrDati,
                        ));
                        $ita_grid01->setSortIndex('ROWID');
                        $ita_grid01->exportCSV('', 'ElencoDocumenti.csv');
                        break;
                }
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_IndiceRowid');
        App::$utente->removeKey($this->nameForm . '_proArriAlle');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openDettaglio($indice) {
        $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $indice);
        if (!$anapro_rec) {
            Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
            return;
        }
        $Proto = $anapro_rec['PROPAR'] . ' ' . substr($anapro_rec['PRONUM'], 0, 4) . '/' . substr($anapro_rec['PRONUM'], 4);
        Out::setAppTitle($this->nameForm, 'Allegati Protocollo: ' . $Proto);
        $this->proArriIndice = $anapro_rec['ROWID'];
        $this->caricaGrigliaAllegati();

        //toggleAllegati
    }

    private function caricaGrigliaAllegati() {
        foreach ($this->proArriAlle as $key => $allegato) {
            if ($allegato['DOCNAME'] == '') {
                $this->proArriAlle[$key]['DOCNAME'] = $allegato['FILENAME'];
            }
            $ext = strtolower(pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION));
            if ($ext != 'p7m') {
                $this->proArriAlle[$key]['PREVIEW'] = '';
            }
        }
        $daVisualizzare = $this->proArriAlle;
        foreach ($daVisualizzare as $key => $value) {
            if ($value['DOCSERVIZIO']) {
                unset($daVisualizzare[$key]);
            }
        }
        TableView::clearGrid($this->gridAllegati);
        $this->CaricaGriglia($this->gridAllegati, $daVisualizzare);
    }

    public function openAnaprosave($indice = '') {
        // Serve vedere se ha accesso al protocollo? Si potrebbe verificare l'ANAPRO attuale..
        if (!$indice) {
            Out::msgStop("Attenzione", "Indice obbligatorio non presente.");
            return false;
        }
        $sql = "SELECT * FROM ANAPROSAVE WHERE ROWID = $indice ";
        $Anaprosave_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if (!$Anaprosave_rec) {
            Out::msgStop("Attenzione", "Variazione del protocollo non trovata.");
            return false;
        }
        // Controllo se ha accesso al protocollo attuale.
        $AnaproAttuale_rec = $this->proLib->GetAnapro($Anaprosave_rec['PRONUM'], 'codice', $Anaprosave_rec['PROPAR']);
        //Out::msgInfo('', print_r($AnaproAttuale_rec, true));

        $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $AnaproAttuale_rec['ROWID']);
        if (!$anapro_rec) {
            Out::msgStop("Accesso al protocollo", "Protocollo non accessibile.");
            return false;
        }
        // Qui caricamento degli allegati.
        $this->proArriAlle = $this->proLib->caricaAllegatiAnaproSave($Anaprosave_rec);
        if (!$this->proArriAlle && $this->proLib->getErrMessage()) {
            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
            return false;
        }

        $dataSave = date('d/m/Y', strtotime($Anaprosave_rec['SAVEDATA']));
        $Descrizione = '<span style="padding:10px; font-size:14px; color:red; "><b>';
        $Descrizione.= 'Situazione allegati del ' . $dataSave . ' alle ore ' . $Anaprosave_rec['SAVEORA'] . '</b></span>';
        Out::addClass($this->nameForm . '_divDescrizione', "ui-corner-all ui-state-highlight");
        Out::html($this->nameForm . '_divDescrizione', $Descrizione);
        $Proto = $anapro_rec['PROPAR'] . ' ' . substr($anapro_rec['PRONUM'], 0, 4) . '/' . substr($anapro_rec['PRONUM'], 4);
        $AppTitle = 'Varizione Allegati Protocollo: ' . $Proto;
        Out::setAppTitle($this->nameForm, $AppTitle);
        $this->proArriIndice = $anapro_rec['ROWID'];



        $this->caricaGrigliaAllegati();

        //toggleAllegati
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
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

}

?>
