<?php

/**
 *
 * DETTAGLIO PASSO DA RICHIESTA ON LINE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    27.08.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praTipiAllegato.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRiservato.class.php';

function praPassoRich() {
    $praPassoRich = new praPassoRich();
    $praPassoRich->parseEvent();
    return;
}

class praPassoRich extends itaModel {

    public $praLib;
    public $proLib;
    public $praTipi;
    public $docLib;
    public $PRAM_DB;
    public $nameForm = "praPassoRich";
    public $divGes = "praPassoRich_divGestione";
    public $divCtrCampi = "praPassoRich_divCtrCampi";
    public $DivUpload = "praPassoRich_DivUpload";
    public $divMail = "praPassoRich_DivMail";
    public $gridAllegati = "praPassoRich_gridAllegati";
    public $gridDati = "praPassoRich_gridDati";
    public $allegati = array();
    public $currRicnum;
    public $currItekey;
    public $currPranum;
    public $returnModel;
    public $returnMethod;
    public $rowidAppoggio;
    public $datiAppoggio;
    public $page;
    public $ext;
    public $selRow;
    public $currXHTML;
    public $currXHTMLDis;
    public $currDescBox;
    public $altriDati = array();
    public $eqAudit;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->praTipi = new praTipiAllegato();
            $this->docLib = new docLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->allegati = App::$utente->getKey($this->nameForm . '_allegati');
            $this->currRicnum = App::$utente->getKey($this->nameForm . '_currRicnum');
            $this->currItekey = App::$utente->getKey($this->nameForm . '_currItekey');
            $this->currPranum = App::$utente->getKey($this->nameForm . '_currPranum');
            $this->currXHTML = App::$utente->getKey($this->nameForm . '_currXHTML');
            $this->currXHTMLDis = App::$utente->getKey($this->nameForm . '_currXHTMLDis');
            $this->currDescBox = App::$utente->getKey($this->nameForm . '_currDescBox');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->datiAppoggio = App::$utente->getKey($this->nameForm . '_datiAppoggio');
            $this->altriDati = App::$utente->getKey($this->nameForm . '_altriDati');
            $this->page = App::$utente->getKey($this->nameForm . '_page');
            $this->ext = App::$utente->getKey($this->nameForm . '_ext');
            $this->selRow = App::$utente->getKey($this->nameForm . '_selRow');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->eqAudit = new eqAudit();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_allegati', $this->allegati);
            App::$utente->setKey($this->nameForm . '_currRicnum', $this->currRicnum);
            App::$utente->setKey($this->nameForm . '_currItekey', $this->currItekey);
            App::$utente->setKey($this->nameForm . '_currPranum', $this->currPranum);
            App::$utente->setKey($this->nameForm . '_currXHTML', $this->currXHTML);
            App::$utente->setKey($this->nameForm . '_currXHTMLDis', $this->currXHTMLDis);
            App::$utente->setKey($this->nameForm . '_currDescBox', $this->currDescBox);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_datiAppoggio', $this->datiAppoggio);
            App::$utente->setKey($this->nameForm . '_altriDati', $this->altriDati);
            App::$utente->setKey($this->nameForm . '_page', $this->page);
            App::$utente->setKey($this->nameForm . '_ext', $this->ext);
            App::$utente->setKey($this->nameForm . '_selRow', $this->selRow);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->allegati = array();
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->page = $_POST["page"];
                $this->selRow = $_POST["selRow"];
                Out::setDialogOption($this->nameForm, 'title', "'" . $_POST[$this->nameForm . "_title"] . "'");
                $this->CreaCombo();
                switch ($_POST['modo']) {
                    case "edit" :
                        if ($_POST['rowid']) {
                            $this->dettaglio($_POST['rowid'], 'rowid');
                        }
                        break;
                    case "add" :
                        if ($_POST['procedimento']) {
                            $this->altriDati = array();
                            $this->currPranum = $_POST['procedimento'];
                            $this->currRicnum = $_POST['pratica'];
                            $this->apriInserimento();
                        }
                        break;
                }
                //Out::hide($this->nameForm . '_gridAllegati');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        if ($this->Controlli() !== false) {
                            try {
                                $Ricite_rec = $_POST[$this->nameForm . '_RICITE'];
                                $Ricite_rec['ITECOD'] = $this->currPranum;
                                $Ricite_rec['RICNUM'] = $this->currRicnum;
                                if ($Ricite_rec['ITESEQ'] == 0 || $Ricite_rec['ITESEQ'] == '') {
                                    $Ricite_rec['ITESEQ'] = 99999;
                                }
                                if ($this->ControllaSequenza($Ricite_rec['ITESEQ'])) {
                                    Out::msgInfo("Inserimento Passo", "Attenzione!! l'attuale sequenza scelta " . $Ricite_rec['ITESEQ'] . " risulta essere occupata");
                                    break;
                                }
                                if ($Ricite_rec['ITEPUB'] == 1 || ($Ricite_rec['ITEPUB'] == 0 && $Ricite_rec['ITECOM'] == 0)) {
                                    $Ricite_rec['ITECDE'] = "";
                                }
                                if ($Ricite_rec['ITERIF'] == 0) {
                                    $Ricite_rec['ITEPROC'] = $Ricite_rec['ITEDAP'] = $Ricite_rec['ITEALP'] = "";
                                }
                                $Ricite_rec['ITEKEY'] = $this->praLib->keyGenerator($Ricite_rec['ITECOD']);
                                $insert_Info = 'Oggetto: Inserimento passo seq ' . $Ricite_rec['ITESEQ'] . ' - ' . $Ricite_rec['ITEKEY'];
                                if ($this->insertRecord($this->PRAM_DB, 'RICITE', $Ricite_rec, $insert_Info)) {
                                    //$this->praLib->ordinaPassiProcRich($this->currPranum, $this->currRicnum);
                                    $this->returnToParent();
                                }
                            } catch (Exception $e) {
                                Out::msgStop("Errore di Inserimento su Tipi di Passi.", $e->getMessage());
                            }
                        }
                        break;
                    case $this->nameForm . '_ApriAttivaExpr':
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_RICITE']['ITEKEY'];
                        $iteeat = $_POST[$this->nameForm . '_RICITE']['ITEATE'];
                        $Ricite_rec = $this->praLib->GetRicite($_POST[$this->nameForm . '_RICITE']['ROWID'], 'rowid');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Ricite_rec['ITECOD'],
                            'ITEATE' => $iteeat,
                            //'ITEATE' => $Ricite_rec['ITEATE'],
                            'RICNUM' => $Ricite_rec['RICNUM'],
                            'TABELLA' => "RICDAG",
                            'MODEL' => $this->nameForm
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITEATE";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Ricite_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnAttivaExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_ApriObblExpr':
                        $model = 'praPassoProcExpr';
                        $itekey = $_POST[$this->nameForm . '_RICITE']['ITEKEY'];
                        $iteobe = $_POST[$this->nameForm . '_RICITE']['ITEOBE'];
                        $Ricite_rec = $this->praLib->GetRicite($_POST[$this->nameForm . '_RICITE']['ROWID'], 'rowid');
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione per rendere obbligatorio il passo:',
                            'ITEKEY' => $itekey,
                            'ITECOD' => $Ricite_rec['ITECOD'],
                            'ITEATE' => $iteobe,
                            'RICNUM' => $Ricite_rec['RICNUM'],
                            'TABELLA' => "RICDAG",
                            'MODEL' => $this->nameForm
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITEOBE";
                        $_POST['ITEKEY'] = $itekey;
                        $_POST['ITECOD'] = $Ricite_rec['ITECOD'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnObblExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_pdfTestoBase':
                    case $this->nameForm . '_editTestoBase':
                        $rowid = $_POST[$this->nameForm . '_RICITE']['ROWID'];
                        $Ricite_rec = $this->praLib->GetRicite($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Ricite_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Ricite_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Ricite_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currXHTML;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiag';
                        $_POST['returnField'] = $this->nameForm . '_pdfTestoBase';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_pdfTestoBaseDis':
                        $rowid = $_POST[$this->nameForm . '_RICITE']['ROWID'];
                        $Ricite_rec = $this->praLib->GetRicite($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Ricite_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Ricite_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Ricite_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');
                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currXHTMLDis;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiagDist';
                        $_POST['returnField'] = $this->nameForm . '_pdfTestoBaseDis';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_OpenDescBox':
                        $rowid = $_POST[$this->nameForm . '_RICITE']['ROWID'];
                        $Ricite_rec = $this->praLib->GetRicite($rowid, 'rowid');
                        $praLibVar = new praLibVariabili();
                        if ($Ricite_rec['ITEPUB'] == 1) {
                            $praLibVar->setFrontOfficeFlag(true);
                        }
                        $praLibVar->setCodiceProcedimento($Ricite_rec['ITECOD']);
                        $praLibVar->setChiavePasso($Ricite_rec['ITEKEY']);
                        $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');
                        $model = 'utiEditDiag';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['edit_text'] = $this->currDescBox;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnEditDiagDescBox';
                        $_POST['returnField'] = $this->nameForm . '_OpenDescBox';
                        $_POST['dictionaryLegend'] = $dictionaryLegend;
                        $_POST['readonly'] = false;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Duplica':
                        $where = " WHERE RICNUM = '" . $this->currRicnum . "'";
                        praRic::praRicItepas($this->nameForm, 'RICITE', $where, '4');
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Ricite_rec = $_POST[$this->nameForm . '_RICITE'];
                        /*
                         * Modifica per supporto altri metadati
                         * Carlo 09.06.15
                         */
                        $Ricite_rec_db = $this->praLib->GetRicite($Ricite_rec['ROWID'], 'rowid');
                        $metadata = unserialize($Ricite_rec_db['ITEMETA']);
                        if (!$metadata) {
                            $metadata = array();
                        }

                        if ($Ricite_rec['ITEMLT'] == 1 || $Ricite_rec['ITEUPL'] == 1) {
                            if ($_POST[$this->nameForm . '_accorpaPDFUpload'] == 1) {
                                $Ricite_rec['ITEIDR'] = 1;
                            } else if ($_POST[$this->nameForm . '_accorpaPDFUpload'] == 0) {
                                $Ricite_rec['ITEIDR'] = 0;
                            }
                            if ($_POST[$this->nameForm . '_RICITE']['ITEFILE'] == 1) {
                                $Ricite_rec['ITEFILE'] = 1;
                            } else if ($_POST[$this->nameForm . '_RICITE']['ITEFILE'] == 0) {
                                $Ricite_rec['ITEFILE'] = 0;
                            }
                        }

                        if ($Ricite_rec['ITEDAT'] == 1 || $Ricite_rec['ITERDM'] == 1) {
                            if ($_POST[$this->nameForm . '_accorpaPDFTestoBase'] == 1) {
                                $Ricite_rec['ITEIDR'] = 1;
                            } else if ($_POST[$this->nameForm . '_accorpaPDFTestoBase'] == 0) {
                                $Ricite_rec['ITEIDR'] = 0;
                            }
                        }

                        if ($Ricite_rec['ITEDIS'] == 1) {
                            if ($_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] == 1) {
                                $Ricite_rec['ITEIDR'] = 1;
                            } else if ($_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] == 0) {
                                $Ricite_rec['ITEIDR'] = 0;
                            }
                            if ($_POST[$this->nameForm . '_uploadAutomatico'] == 1) {
                                $Ricite_rec['ITEFILE'] = 1;
                            } elseif ($_POST[$this->nameForm . '_uploadAutomatico'] == 0) {
                                $Ricite_rec['ITEFILE'] = 0;
                            }
                        }


//                        if ($Ricite_rec['ITEDAT'] == 0 || !$this->currXHTML) {
//                            $_POST[$this->nameForm . '_accorpaPDFTestoBase'] = 0;
//                            Out::msgInfo(" ", " qui");
//                        }
//                        if ($Ricite_rec['ITEDIS'] == 0 || !$this->currXHTMLDis) {
//                            $_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] = 0;
//                        }
//                        if ($Ricite_rec['ITEDIS'] == 1) {
//                            if ($_POST[$this->nameForm . '_accorpaPDFTestoBaseDis'] == 1) {
//                                $Ricite_rec['ITEIDR'] = 1;
//                            }
//                            if ($_POST[$this->nameForm . '_uploadAutomatico'] == 1) {
//                                $Ricite_rec['ITEFILE'] = 1;
//                            }
//                        }
//                        if ($Ricite_rec['ITEUPL'] == 1 || $Ricite_rec['ITEMLT'] == 1) {
//                            if ($_POST[$this->nameForm . '_RICITE']['ITEFILE'] == 1) {
//                                $Ricite_rec['ITEFILE'] = 1;
//                            }
//                        }
                        //
                        // Generazione e aggiustamento campi automatici
                        //
                        if ($Ricite_rec['ITEKEY'] == '') {
                            $Ricite_rec['ITEKEY'] = $this->praLib->keyGenerator($Ricite_rec['ITECOD']);
                        }
                        if ($Ricite_rec['ITEUPL'] == 0 && $Ricite_rec['ITEMLT'] == 0 && $Ricite_rec['ITEDAT'] == 0 && $Ricite_rec['ITERDM'] == 0) {
                            //$Ricite_rec['ITEIDR'] = 0;
                            $Ricite_rec['ITEIFC'] = 0;
                            $Ricite_rec['ITEEXT'] = '';
                        }
                        if ($Ricite_rec['ITEIDR'] == 1) {
                            $Ricite_rec['ITEIFC'] = 0;
                        }
                        if ($Ricite_rec['ITEIFC'] == 0 || $Ricite_rec['ITEIFC'] == 2) {
                            $Ricite_rec['ITETAL'] = "";
                        }
                        if ($Ricite_rec['ITEPUB'] == 1 || ($Ricite_rec['ITEPUB'] == 0 && $Ricite_rec['ITECOM'] == 0)) {
                            $Ricite_rec['ITECDE'] = "";
                        }
                        if ($Ricite_rec['ITERIF'] == 0) {
                            $Ricite_rec['ITEPROC'] = $Ricite_rec['ITEDAP'] = $Ricite_rec['ITEALP'] = "";
                        }

                        if ($Ricite_rec['ITEDAT'] == 0) {
                            $Ricite_rec['ITECOL'] = 0;
                        }

                        if ($this->currXHTML) {
                            $metadata['TESTOBASEXHTML'] = $this->currXHTML;
                        }
                        if ($this->currXHTMLDis) {
                            $metadata['TESTOBASEDISTINTA'] = $this->currXHTMLDis;
                        }
                        if ($this->currDescBox) {
                            $Ricite_rec['ITEHTML'] = $this->currDescBox;
                        }
                        if ($_POST[$this->nameForm . '_NomeFileUpload']) {
                            $metadata['TEMPLATENOMEUPLOAD'] = $_POST[$this->nameForm . '_NomeFileUpload'];
                        }
                        if ($_POST[$this->nameForm . '_Classificazioni']) {
                            $metadata['CODICECLASSIFICAZIONE'] = $_POST[$this->nameForm . '_Classificazioni'];
                        }
                        if ($Ricite_rec['ITEQALLE'] == 0) {
                            $Ricite_rec['ITEQCLA'] = 0;
                            $Ricite_rec['ITEQDEST'] = 0;
                            $Ricite_rec['ITEQNOTE'] = 0;
                            $Ricite_rec['ITEQNOTE'] = 0;
                            unset($metadata['TEMPLATENOMEUPLOAD']);
                            Out::valore($this->nameForm . '_NomeFileUpload', "");
                            unset($metadata['CODICECLASSIFICAZIONE']);
                            Out::valore($this->nameForm . '_Classificazioni', "");
                        }

                        $Ricite_rec['ITEMETA'] = serialize($metadata);

                        if ($this->Controlli() !== false) {
                            $rowid = $_POST[$this->nameForm . '_RICITE']['ROWID'];
                            $procedimento = $Ricite_rec['ITECOD'];
                            $update_Info = 'Oggetto: Aggironamento passo seq ' . $Ricite_rec['ITESEQ'] . " - " . $Ricite_rec['ITEKEY'];
                            if ($this->updateRecord($this->PRAM_DB, 'RICITE', $Ricite_rec, $update_Info)) {
//                                $retInsetDAG = $this->InserisciDatiAggiuntivi($Ricite_rec['ITEKEY']);  // con i filtri settati mi cancellava i campi aggiuntivi 
//                                if ($retInsetDAG['status'] == false) {
//                                    Out::msgStop("Errore", $retInsetDAG['message']);
//                                    break;
//                                }
                                //$this->praLib->ordinaPassiProcRich($procedimento, $this->currRicnum);
                                $this->returnToParent();
                            }
                        }
                        break;
                    case $this->nameForm . '_CancellaCampi':
                        Out::msgQuestion("Attenzione.", "Vuoi cancellare i campi aggiuntivi?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCan', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Cancella' => array('id' => $this->nameForm . '_CancellaCampiConf', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_EsportaDati':
                        $ExportDati = "";
                        if ($this->datiFiltrati == "")
                            $this->datiFiltrati = $this->altriDati;
                        foreach ($this->datiFiltrati as $key => $dato) {
                            $ExportDati .= $dato['DAGKEY'] . ";";
                        }
                        $ExportDati .= "\r\n";
                        $ExportDati = substr($ExportDati, 0, strlen($ExportDati) - 1);
                        foreach ($this->datiFiltrati as $key => $dato) {
                            $ExportDati .= $dato['DAGVAL'] . ";";
                        }
                        //$nome_file = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . '-ExportDati.csv';
                        if (!is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                break;
                            }
                        }
                        $nome_file = itaLib::getAppsTempPath() . '/' . 'ExportDati.csv';
                        if (file_put_contents($nome_file, $ExportDati)) {
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            'exportDati_' . $this->currGesnum . '.csv', $nome_file
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaCampoAgg':
                        if (array_key_exists($this->rowidAppoggio, $this->altriDati) == true) {
                            $delete_Info = 'Oggetto: Cancellazione campo aggiuntivo ' . $this->altriDati[$this->rowidAppoggio]['DAGKEY'] . " - " . $this->altriDati[$this->rowidAppoggio]['ITEKEY'];
                            if (!$this->deleteRecord($this->PRAM_DB, 'RICDAG', $this->altriDati[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                Out::msgStop("Cancellazione dati Aggiuntivi", "Errore nella cancellazione del dato: " . $this->altriDati[$this->rowidAppoggio]['DAGKEY']);
                                break;
                            }
                            unset($this->altriDati[$this->rowidAppoggio]);
                        }
                        $this->CaricaGriglia($this->gridDati, $this->altriDati);
                        break;
                    case $this->nameForm . '_CancellaCampiConf':
                       // Out::msgInfo($this->currRicnum, print_r($_POST[$this->nameForm.'_RICITE']['ITEKEY'],true));
                        $this->CancellaDatiAggiuntivi($_POST[$this->nameForm.'_RICITE']['ITEKEY']);
                        $this->altriDati = array();
                        $this->CaricaGriglia($this->gridDati, $this->altriDati);
                        Out::hide($this->nameForm . '_CancellaCampi');
                        Out::hide($this->nameForm . '_EsportaDati');
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::valore($this->nameForm . '_Destinazione', '');
                        Out::valore($this->nameForm . '_DescrizioneVai', '');
                        Out::valore($this->nameForm . '_RICITE[ITEVPA]', '');
                        break;
                    case $this->nameForm . '_SvuotaNo':
                        Out::valore($this->nameForm . '_DestinazioneNo', '');
                        Out::valore($this->nameForm . '_DescrizioneVaiNo', '');
                        Out::valore($this->nameForm . '_RICITE[ITEVPN]', '');
                        break;
                    case $this->nameForm . '_CtrSvuota':
                        Out::valore($this->nameForm . '_CtrPasso', '');
                        Out::valore($this->nameForm . '_CtrDesPasso', '');
                        Out::valore($this->nameForm . '_RICITE[ITECTP]', '');
                        break;
                    case $this->nameForm . '_Svuota2':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEWRD'] != '') {
                            if ($this->altriDati) {
                                Out::msgInfo('Avviso', 'Testo non modificabile in presenza di campi aggiuntivi.<br>Procedere prima con la cancellazione dei dati aggiuntivi.');
                            } else {
                                Out::valore($this->nameForm . '_RICITE[ITEWRD]', '');
                            }
                        }
                        break;
                    case $this->nameForm . '_Svuota3':
                        Out::valore($this->nameForm . '_RICITE[ITEIMG]', '');
                        break;
                    case $this->nameForm . '_SvuotaExt':
                        Out::valore($this->nameForm . '_RICITE[ITEEXT]', '');
                        break;
                    case $this->nameForm . '_SvuotaTipi':
                        Out::valore($this->nameForm . '_RICITE[ITETAL]', '');
                        break;
                    case $this->nameForm . '_RifSvuota':
                        Out::valore($this->nameForm . '_RICITE[ITEPROC]', '');
                        Out::valore($this->nameForm . '_RICITE[ITEDAP]', '');
                        Out::valore($this->nameForm . '_RICITE[ITEALP]', '');
                        Out::valore($this->nameForm . '_DesProcedimento', '');
                        Out::valore($this->nameForm . '_DalPasso', '');
                        Out::valore($this->nameForm . '_AlPasso', '');
                        Out::valore($this->nameForm . '_DesDalPasso', '');
                        Out::valore($this->nameForm . '_DesAlPasso', '');
                        break;
                    case $this->nameForm . '_RICITE[ITECLT]_butt':
                        praRic::praRicPraclt($this->nameForm, "RICERCA Tipo Passo");
                        break;
                    case $this->nameForm . '_RICITE[ITERES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "RICERCA Dipendenti");
                        break;
                    case $this->nameForm . '_RICITE[ITEWRD]_butt':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEWRD'] != '') {
                            $ditta = App::$utente->getKey('ditta');
                            $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                            eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
                            $destinazione = $destinazione . "repository/" . $this->currRicnum . "/testiAssociati/";
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $_POST[$this->nameForm . '_RICITE']['ITEWRD'], $destinazione . $_POST[$this->nameForm . '_RICITE']['ITEWRD']
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEIMG]_butt':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEIMG'] != '') {
                            $ditta = App::$utente->getKey('ditta');
                            $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                            eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
                            $destinazione = $destinazione . "repository/" . $this->currRicnum . "/immagini/";
                            if (!is_dir($destinazione)) {
                                Out::msgStop("Errore.", 'Directory non presente!');
                                break;
                            }
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $_POST[$this->nameForm . '_RICITE']['ITEIMG'], $destinazione . $_POST[$this->nameForm . '_RICITE']['ITEIMG']
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEURL]_butt':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEURL']) {
                            Out::openDocument($_POST[$this->nameForm . '_RICITE']['ITEURL']);
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITECDE]_butt':
                        proRic::proRicAnamed($this->nameForm, $where, 'proAnamed');
                        break;
                    case $this->nameForm . '_RICITE[ITEPROC]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti");
                        break;
                    case $this->nameForm . '_RICITE[ITETAL]_butt':
                        $Tipi = $this->praTipi->getTipi();
                        $this->praTipi->CaricaTipi($Tipi, $this->nameForm);
                        break;
                    case $this->nameForm . '_AlPasso_butt':
                        $retid = "6";
                    case $this->nameForm . '_DalPasso_butt':
                        if ($retid != "6")
                            $retid = '5';
                        if ($_POST[$this->nameForm . "_RICITE"]['ITEPROC']) {
                            $where = "WHERE ITEPUB = 1 AND ITECOD = " . $_POST[$this->nameForm . "_RICITE"]['ITEPROC'];
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, $retid, '', 'asc');
                        } else {
                            Out::msgInfo("Ricerca passi", "Scegliere il procediemnto di riferimento");
                        }
                        break;
                    case $this->nameForm . '_FileLocaleTesto':
                        Out::msgQuestion("Upload.", "Vuoi caricare un documento interno o uno esterno?", array(
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadDocEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadDocInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_FileLocale':
                        Out::msgQuestion("Upload.", "Vuoi caricare un file interno o uno esterno?", array(
                            'F8-Esterno' => array('id' => $this->nameForm . '_UploadFileEsterno', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Interno' => array('id' => $this->nameForm . '_UploadFileInterno', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ImportaCampi':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
                        $origine = $destinazione . "repository/" . $this->currRicnum . "/testiAssociati/";
                        //$origine = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                        if ($this->praLib->CreaFileInfo($origine . $_POST[$this->nameForm . '_RICITE']['ITEWRD'])) {
                            $outputFile = pathinfo($_POST[$this->nameForm . '_RICITE']['ITEWRD'], PATHINFO_FILENAME);
                            $outputPath = itaLib::getAppsTempPath();
                            $fileInfo['DATAFILE'] = $outputPath . '/' . $outputFile . '.info';
                            $arrayInfo = $this->praLib->DecodeFileInfo($fileInfo);
                            $errLength = false;
                            foreach ($arrayInfo as $Key => $valore) {
                                if ($Key != "") {
                                    //$Itedag_rec["ITDKEY"] = $Key;
                                    $Ricdag_rec["DAGNUM"] = $this->currRicnum;
                                    $Ricdag_rec["ITECOD"] = $this->currPranum;
                                    $Ricdag_rec["DAGKEY"] = trim(substr($Key, 0, 60));
                                    $Ricdag_rec["DAGALIAS"] = trim(substr($Key, 0, 60));
                                    $Ricdag_rec["ITEKEY"] = $_POST[$this->nameForm . '_RICITE']['ITEKEY'];
                                    $this->altriDati[] = $Ricdag_rec;
                                    if (strlen($Key) > 60) {
                                        $errLength = true;
                                    }
                                }
                            }
                            $this->InserisciDatiAggiuntivi($_POST[$this->nameForm.'_RICITE']['ITEKEY']); // tania
                            $this->CaricaGriglia($this->gridDati, $this->altriDati);
                            if ($errLength === true) {
                                Out::msgInfo('AVVISO', "Alcuni campi hanno una lunghezza maggiore di 60 caratteri.<br>Il controllo dei suddetti campi potrebbe dare problemi.");
                            }
                            Out::show($this->nameForm . '_CancellaCampi');
                            Out::show($this->nameForm . '_EsportaDati');
                        } else {
                            Out::msgInfo('Errore', 'Importazione campi non eseguita.');
                        }
                        break;
                    case $this->nameForm . '_UploadDocEsterno':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadDocEsterno";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_UploadDocInterno':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        $matriceSelezionati = array();
                        $matriceSelezionati = $this->GetFileList($destinazione);
                        if ($matriceSelezionati) {
                            praRic::ricImmProcedimenti($matriceSelezionati, $this->nameForm, 'returnIndiceITEWRD', 'Testi Disponibili');
                        } else {
                            Out::msgInfo('Attenzione.', 'Nessun Testo presente in elenco. Caricare manualmente il Testo.');
                        }
                        break;
                    case $this->nameForm . '_UploadFileEsterno':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadFileEsterno";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_UploadFileInterno':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }
                        $matriceSelezionati = array();
                        $matriceSelezionati = $this->GetFileList($destinazione);
                        if ($matriceSelezionati) {
                            praRic::ricImmProcedimenti($matriceSelezionati, $this->nameForm, 'returnIndiceITEIMG', 'Immagini Disponibili');
                        } else {
                            Out::msgInfo('Attenzione.', 'Nessuna Immagine presente in elenco. Caricare manualmente una Immagine.');
                        }

                        break;
                    case $this->nameForm . '_Destinazione_butt':
                        $retid = '1';
                    case $this->nameForm . '_DestinazioneNo_butt':
                        if ($retid != "1")
                            $retid = '2';
                    case $this->nameForm . '_CtrPasso_butt':
                        $where = '';
                        if ($retid != "1" && $retid != "2") {
                            $retid = '3';
                        }
                        if ($_POST[$this->nameForm . '_RICITE']['ROWID'] == '') {
                            $itecod = $_POST[$this->nameForm . '_RICITE']['ITECOD'];
                            $where = " WHERE RICNUM = $this->currRicnum AND ITECOD = '" . $itecod . "' AND ITEPUB = 1";
                        } else {
                            $Ricite_rec = $this->praLib->GetRicite($_POST[$this->nameForm . '_RICITE']['ROWID'], 'rowid');
                            $itecod = $Ricite_rec['ITECOD'];
                            $where = " WHERE RICNUM = $this->currRicnum AND ITECOD = '" . $itecod . "' AND ITEKEY <> '" . $Ricite_rec['ITEKEY'] . "' AND ITEPUB = 1";
                        }
                        praRic::praRicItepas($this->nameForm, 'RICITE', $where, $retid);
                        break;
                    case $this->nameForm . '_Sovrascrivi':
                        if (!@rename($this->datiAppoggio['origFile'], $this->datiAppoggio['destinazione'] . $this->datiAppoggio['nomeFile'])) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            break;
                        } else {
                            Out::valore($this->nameForm . '_RICITE[ITEWRD]', $this->datiAppoggio['nomeFile']);
                        }
                        break;
                    case $this->nameForm . '_CercaDocumento':
                        docRic::docRicDocumenti($this->nameForm, " WHERE CLASSIFICAZIONE = 'PRATICHE'");
                        break;
                    case $this->nameForm . '_VediDocumento':
                        $codice = $_POST[$this->nameForm . '_RICITE']['ITETBA'];
                        $documenti_rec = $this->docLib->getDocumenti($codice);
                        switch ($documenti_rec['TIPO']) {
                            case 'XHTML':
                                $rowid = $_POST[$this->nameForm . '_RICITE']['ROWID'];
                                $Ricite_rec = $this->praLib->GetRicite($rowid, 'rowid');
                                $praLibVar = new praLibVariabili();
                                if ($Ricite_rec['ITEPUB']) {
                                    $praLibVar->setFrontOfficeFlag(true);
                                }
                                $praLibVar->setCodiceProcedimento($Ricite_rec['ITECOD']);
                                $praLibVar->setChiavePasso($Ricite_rec['ITEKEY']);
                                $dictionaryLegend = $praLibVar->getLegendaProcedimento('adjacency', 'smarty');

                                $model = 'utiEditDiag';
                                $_POST = array();
                                $_POST['event'] = 'openform';
                                $_POST['edit_text'] = $documenti_rec['CONTENT'];
                                $_POST['returnModel'] = $this->nameForm;
                                $_POST['returnEvent'] = '';
                                $_POST['returnField'] = '';
                                $_POST['dictionaryLegend'] = $dictionaryLegend;

                                $_POST['readonly'] = true;
                                itaLib::openForm($model);
                                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                $model();
                                break;
                            case "MSWORD HTML":
                            case "RTF":
                            case "ODT":
                            case "XML":
                            case "TXT":
                                $nomeDoc = $documenti_rec['CODICE'] . '.' . $documenti_rec['TIPO'];
                                $nomeFile = $documenti_rec['CONTENT'] . '.' . $documenti_rec['TIPO'];
                                $docPath = Config::getPath('general.fileEnte') . "ente" . App::$utente->getKey('ditta') . "/documenti/";
                                //                                $docPath = Config::getPath('general.itaDocumenti');
                                $file = $docPath . $nomeFile;
                                if (file_exists($file)) {
                                    Out::openDocument(utiDownload::getUrl($nomeDoc, $file));
                                }
                                break;
                        }
                        break;
                    case $this->nameForm . '_TogliDocumento':
                        Out::valore($this->nameForm . "_RICITE[ITETBA]", '');
                        Out::valore($this->nameForm . "_DocumentoOgg", '');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->caricaAllegati($this->currItekey, $this->currRicnum);
                        break;
                    case $this->gridDati:
                        $this->CaricoCampiAggiuntivi($this->currItekey, $this->currRicnum);
                        break;
                } break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {

                    case $this->gridAllegati:
                        $ditta = App::$utente->getKey('ditta');
                        $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                        eval($comando);
                        $destinazione = $destinazione . "attachments/" . $this->currRicnum;
                        if (!is_dir($destinazione)) {
                            Out::msgStop("Errore.", 'Directory non presente!');
                            break;
                        }

                        if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                            Out::openDocument(utiDownload::getUrl(
                                            $this->allegati[$_POST['rowid']]['DOCNAME'], $destinazione . "/" . $this->allegati[$_POST['rowid']]['DOCUPL']
                                    )
                            );
                        }

                        break;
                } break;


            case 'afterSaveCell':
                switch ($_POST['id']) {

                    case $this->gridAllegati:
                        $ricdoc_rec['DOCNAME'] = $_POST['value'];
                        $ricdoc_rec['ROWID'] = $this->allegati[$_POST['rowid']]['ROWID'];
                        if (!$this->updateRecord($this->PRAM_DB, 'RICDOC', $ricdoc_rec, $update_Info)) {
                            Out::msgStop("ERRORE ", "Non è stato possibile effettuare le modifiche alla Richiesta N " . $this->currRicnum);
                            break;
                        }
                        $this->eqAudit->logEqEvent($this, array(// Controllo modifiche 
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'DB' => $this->PRAM_DB,
                            'DSet' => 'RICDOC',
                            'Estremi' => "Modificato DOCNAME per la richiesta N " . $this->currRicnum . " rowid documento " . $ricdoc_rec['ROWID']
                        ));
                        Out::msgBlock($this->nameForm, 800, true, 'Campo Aggiornato');

                        break;
                    case $this->gridDati:
                        switch ($_POST['cellname']) {
                            case "DAGCTR":
                                $dagctr_rec['DAGCTR'] = $_POST['value'];
                                break;
                            case "DAGKEY":
                                $dagctr_rec['DAGKEY'] = $_POST['value'];
                                break;
                            case "RICDAT":
                                $dagctr_rec['RICDAT'] = $_POST['value'];
                                break;
                            case "DAGROL":
                                $dagctr_rec['DAGROL'] = $_POST['value'];
                                break;
                        }
                        $dagctr_rec['ROWID'] = $this->altriDati[$_POST['rowid']]['ROWID'];

                        if (!$this->updateRecord($this->PRAM_DB, 'RICDAG', $dagctr_rec, $update_Info)) {
                            Out::msgStop("ERRORE ", "Non è stato possibile effettuare le modifiche alla Richiesta N " . $this->currRicnum);
                            break;
                        }
                        $this->eqAudit->logEqEvent($this, array(// Controllo modifiche 
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'DB' => $this->PRAM_DB,
                            'DSet' => 'RICDAG',
                            'Estremi' => "Modificato " . $_POST['cellname'] . " per la richiesta N " . $this->currRicnum . " rowid " . $dagctr_rec['ROWID']
                        ));
                        Out::msgBlock($this->nameForm, 800, true, 'Campo Aggiornato');
                        break;
                }
                break;


            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RICITE[ITEDES]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Descrizioni_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT ITEDES FROM RICITE WHERE ITEPUB = 1 AND " . $this->PRAM_DB->strLower('ITEDES') . " LIKE '%" . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Descrizioni_tab as $Descrizioni_rec) {
                            itaSuggest::addSuggest($Descrizioni_rec['ITEDES']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_RICITE[ITENOT]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Note_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT ITENOT FROM RICITE WHERE ITEPUB = 1 AND " . $this->PRAM_DB->strLower('ITENOT') . " LIKE '%" . addslashes(strtolower(itaSuggest::getQuery())) . "%'", true);
                        foreach ($Note_tab as $Note_rec) {
                            itaSuggest::addSuggest($Note_rec['ITENOT']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case "returnPraclt":
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                if ($Praclt_rec) {
                    Out::valore($this->nameForm . '_RICITE[ITECLT]', $Praclt_rec['CLTCOD']);
                    Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                }
                break;
            case "returnEditDiag":
                $this->currXHTML = $_POST['returnText'];
                break;
            case "returnEditDiagDist":
                $this->currXHTMLDis = $_POST['returnText'];
                break;
            case "returnEditDiagDescBox":
                $this->currDescBox = $_POST['returnText'];
                break;
            case 'returnUnires':
                $Ananom_rec = $this->praLib->GetAnanom($_POST["retKey"], 'rowid');
                if ($Ananom_rec) {
                    $this->DecodResponsabile($Ananom_rec);
                }
                break;
            case 'returnAttivaExpr' :
                Out::valore($this->nameForm . '_AttivaEspressione', $this->praLib->DecodificaControllo($_POST['dati']['ITEATE']));
                Out::valore($this->nameForm . '_RICITE[ITEATE]', $_POST['dati']['ITEATE']);
                break;
            case 'returnObblExpr' :
                Out::valore($this->nameForm . '_ObblEspressione', $this->praLib->DecodificaControllo($_POST['dati']['ITEATE']));
                Out::valore($this->nameForm . '_RICITE[ITEOBE]', $_POST['dati']['ITEATE']);
                break;
            case 'returnIndiceITEWRD':
                if ($ditta == '')
                    $ditta = App::$utente->getKey('ditta');
                $pathfile = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';

                $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
                $destinazione = $destinazione . "repository/" . $this->currRicnum . "/testiAssociati/";

                $matriceSelezionati = $this->GetFileList($pathfile);

                //$ext = pathinfo($destinazione . $matriceSelezionati[$_POST['retKey']]['FILENAME'], PATHINFO_EXTENSION);
                $ext = pathinfo($pathfile . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME'], PATHINFO_EXTENSION);

                $errore = false;
                foreach ($this->ext as $key => $est) {
                    if ($ext != $est['EXT']) {
                        $errore = true;
                        break;
                    }
                }
                if ($errore != false) {
                    Out::msgStop('ERRORE!!', "L'estensione del file scelto non è tra quelle gestite.<br>Gestire l'estensione o sciegliere un altro file");
                    break;
                }
                @copy($pathfile . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME'], $destinazione . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME']);
                Out::valore($this->nameForm . '_RICITE[ITEWRD]', $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME']);
                break;
            case 'returnIndiceITEIMG':
                if ($ditta == '')
                    $ditta = App::$utente->getKey('ditta');
                $pathfile = Config::getPath('general.itaProc') . 'ente' . $ditta . '/immagini/';
                $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
                eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
                $destinazione = $destinazione . "repository/" . $this->currRicnum . "/immagini/";
                $matriceSelezionati = $this->GetFileList($pathfile);
                @copy($pathfile . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME'], $destinazione . $matriceSelezionati[$_POST['rowData']['rowid']]['FILENAME']);
                Out::valore($this->nameForm . '_RICITE[ITEIMG]', $matriceSelezionati[$_POST['retKey']]['FILENAME']);
                break;
            case 'returnUploadDocEsterno':
                $this->AllegaTesto();
                break;
            case 'returnUploadFileEsterno':
                $this->AllegaFile();
                break;
            case 'returnItepas':
                switch ($_POST['retid']) {
                    case '1':
                        $this->DecodVaialpasso($_POST["retKey"], 'rowid');
                        break;
                    case '2':
                        $this->DecodVaialpassoNo($_POST["retKey"], 'rowid');
                        break;
                    case '3':
                        $this->DecodCtrPasso($_POST["retKey"], 'rowid');
                        break;
                    case '4':
                        $this->DecodDuplicaPasso($_POST["retKey"], 'rowid');
                        break;
                    case '5':
                        $this->DecodDalPasso($_POST["retKey"], 'rowid');
                        break;
                    case '6':
                        $this->DecodAlPasso($_POST["retKey"], 'rowid');
                        break;
                    default:
                        break;
                }
                break;
            case 'returnanamed':
                $this->DecodAnamedCom($_POST['retKey'], 'rowid');
                break;
            case "returnAnapra";
                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ROWID'], 'rowid');
                Out::valore($this->nameForm . '_RICITE[ITEPROC]', $Anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                break;
            case "returnDocumenti";
                $this->decodDocumenti($_POST['retKey'], 'rowid');
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RICITE[ITEPUB]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEPUB'] == 1) {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::hide($this->nameForm . '_RICITE[ITECOM]');
                            Out::hide($this->nameForm . '_RICITE[ITECOM]_lbl');
                            Out::unBlock($this->nameForm . '_divSuap');
                        } else {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::show($this->nameForm . '_RICITE[ITECOM]');
                            Out::show($this->nameForm . '_RICITE[ITECOM]_lbl');
                            Out::block($this->nameForm . '_divSuap');
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITECOM]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITECOM'] == 1) {
                            Out::hide($this->nameForm . '_divSuap');
                            Out::show($this->nameForm . '_divGenerali');
                            Out::hide($this->nameForm . '_RICITE[ITEPUB]');
                            Out::hide($this->nameForm . '_RICITE[ITEPUB]_lbl');
                        } else {
                            Out::show($this->nameForm . '_divSuap');
                            Out::hide($this->nameForm . '_divGenerali');
                            Out::show($this->nameForm . '_RICITE[ITEPUB]');
                            Out::show($this->nameForm . '_RICITE[ITEPUB]_lbl');
                            Out::block($this->nameForm . '_divSuap');
                        }
                        break;

                    case $this->nameForm . '_RICITE[ITEOBL]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEOBL'] == 1) {
                            Out::show($this->nameForm . '_ObblEspressione_field');
                            Out::show($this->nameForm . '_ApriObblExpr');
                        } else {
                            Out::hide($this->nameForm . '_ObblEspressione_field');
                            Out::hide($this->nameForm . '_ApriObblExpr');
                        }
                        break;

                    case $this->nameForm . '_RICITE[ITEQST]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEQST'] == 0) {
                            if ($_POST[$this->nameForm . '_RICITE']['ITEVPN']) {
                                Out::valore($this->nameForm . "_RICITE[ITEQST]", 1);
                                Out::msgStop("Errore", "Vai al Passo (Risposta NO) selezionato.<br>Prima premere il Bottone Svuota ");
                                break;
                            } else {
                                Out::hide($this->nameForm . "_DestinazioneNo");
                                Out::hide($this->nameForm . "_DestinazioneNo_butt");
                                Out::hide($this->nameForm . "_DestinazioneNo_lbl");
                                Out::hide($this->nameForm . "_DescrizioneVaiNo");
                                Out::hide($this->nameForm . "_SvuotaNo");
                                Out::html($this->nameForm . "_Destinazione_lbl", "Salta al Passo");
                            }
                        } elseif ($_POST[$this->nameForm . '_RICITE']['ITEQST'] == 1) {
                            Out::show($this->nameForm . "_DestinazioneNo");
                            Out::show($this->nameForm . "_DestinazioneNo_butt");
                            Out::show($this->nameForm . "_DestinazioneNo_lbl");
                            Out::show($this->nameForm . "_DescrizioneVaiNo");
                            Out::show($this->nameForm . "_SvuotaNo");
                            Out::html($this->nameForm . "_Destinazione_lbl", "Vai al Passo (Risposta SI)");
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEDIS]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEDIS'] == 1) {
                            Out::show($this->nameForm . "_DivDistinta");
                        } else {
                            Out::hide($this->nameForm . "_DivDistinta");
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEUPL]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEUPL'] == 1) {
                            Out::show($this->divCtrCampi);
                            Out::show($this->DivUpload);
                        } else {
                            Out::hide($this->divCtrCampi);
                            Out::hide($this->DivUpload);
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEMLT]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEMLT'] == 1) {
                            Out::show($this->divCtrCampi);
                            Out::show($this->DivUpload);
                        } else {
                            Out::hide($this->divCtrCampi);
                            Out::hide($this->DivUpload);
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEIDR]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEIDR'] == 0) {
                            Out::show($this->nameForm . "_RICITE[ITEIFC]");
                            Out::show($this->nameForm . "_RICITE[ITEIFC]_lbl");
                        } else {
                            Out::hide($this->nameForm . "_RICITE[ITEIFC]");
                            Out::hide($this->nameForm . "_RICITE[ITEIFC]_lbl");
                            Out::hide($this->nameForm . "_RICITE[ITETAL]");
                            Out::hide($this->nameForm . "_RICITE[ITETAL]_butt");
                            Out::hide($this->nameForm . "_RICITE[ITETAL]_lbl");
                            Out::hide($this->nameForm . "_SvuotaTipi");
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEIFC]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEIFC'] == 0 || $_POST[$this->nameForm . '_RICITE']['ITEIFC'] == 2) {
                            Out::hide($this->nameForm . "_RICITE[ITETAL]");
                            Out::hide($this->nameForm . "_RICITE[ITETAL]_butt");
                            Out::hide($this->nameForm . "_RICITE[ITETAL]_lbl");
                            Out::hide($this->nameForm . "_SvuotaTipi");
                        } else {
                            Out::show($this->nameForm . "_RICITE[ITETAL]");
                            Out::show($this->nameForm . "_RICITE[ITETAL]_butt");
                            Out::show($this->nameForm . "_RICITE[ITETAL]_lbl");
                            Out::show($this->nameForm . "_SvuotaTipi");
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEIRE]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEIRE'] == 1) {
                            Out::show($this->divMail);
                        } else {
                            Out::hide($this->divMail);
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITERIF]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITERIF'] == 0) {
                            Out::hide($this->nameForm . '_divRiferimento');
                        } else {
                            Out::show($this->nameForm . '_divRiferimento');
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEMLT]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEMLT'] == 1) {
                            Out::show($this->divCtrCampi);
                        } else {
                            Out::hide($this->divCtrCampi);
                        }
                        break;
                }
                break;


            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_estensione':
                        if ($_POST[$this->nameForm . '_estensione'] != '') {
                            $posi = strpos($_POST[$this->nameForm . '_RICITE']['ITEEXT'], '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|');
                            if ($posi !== false) {
                                Out::valore($this->nameForm . '_RICITE[ITEEXT]', str_replace('|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|', '', $_POST[$this->nameForm . '_RICITE']['ITEEXT']));
                            } else {
                                Out::valore($this->nameForm . '_RICITE[ITEEXT]', $_POST[$this->nameForm . '_RICITE']['ITEEXT'] . '|' . strtolower($_POST[$this->nameForm . '_estensione']) . '|');
                            }
                            Out::valore($this->nameForm . '_estensione', '');
                            Out::setFocus('', $this->nameForm . '_estensione');
                        }
                        break;

                    case $this->nameForm . '_RICITE[ITECLT]':
                        $codice = $_POST[$this->nameForm . '_RICITE']['ITECLT'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            Out::valore($this->nameForm . '_RICITE[ITECLT]', $Praclt_rec['CLTCOD']);
                            Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITERES]':
                        $codice = $_POST[$this->nameForm . '_RICITE']['ITERES'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Ananom_rec = $this->praLib->GetAnanom($codice);
                            if ($Ananom_rec) {
                                $this->DecodResponsabile($Ananom_rec);
                            } else {
                                Out::valore($this->nameForm . '_RICITE[ITESET]', "");
                                Out::valore($this->nameForm . '_RICITE[ITESER]', "");
                                Out::valore($this->nameForm . '_RICITE[ITEOPE]', "");
                                Out::valore($this->nameForm . '_SETTORE', "");
                                Out::valore($this->nameForm . '_SERVIZIO', "");
                                Out::valore($this->nameForm . '_UNITA', "");
                                Out::valore($this->nameForm . '_Nome', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITECDE]':
                        $codice = $_POST[$this->nameForm . '_RICITE']['ITECDE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $Anamed_rec = $this->proLib->GetAnamed($codice);
                            $this->DecodAnamedCom($Anamed_rec['ROWID'], 'rowid');
                        } else {
                            Out::valore($this->nameForm . '_DESTINATARIO', "");
                        }
                        break;
                    case $this->nameForm . '_RICITE[ITEPROC]':
                        if ($_POST[$this->nameForm . '_RICITE']['ITEPROC']) {
                            $codice = $_POST[$this->nameForm . '_RICITE']['ITEPROC'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if ($Anapra_rec) {
                                Out::valore($this->nameForm . '_RICITE[ITEPROC]', $Anapra_rec['PRANUM']);
                                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                            } else {
                                Out::valore($this->nameForm . '_DesProcedimento', "");
                            }
                        }
                        break;
                }
                break;
            // case 'dbClickRow':
//                $ditta = App::$utente->getKey('ditta');
//                $filePdf=Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/'. $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
//                $dati = array();
//                $dati = $this->altriDati;  //[$_POST['rowid']];
//                $idxDati = $_POST['rowid'];
//                $model = 'praPassoProcAgg';
//                $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
//                $_POST = array();
//                $_POST['event'] = 'openform';
//                $_POST[$model . '_returnEvent'] = 'returnPraPassoProcAgg';
//                $_POST[$model . '_returnModel'] = $this->nameForm;
//                $_POST['chiamante'] = $rowid;
//                $_POST['perms'] = $this->perms;
//                $_POST['filePdf']= $filePdf;
//                $_POST['dati'] = $dati;
//                $_POST['idxDati'] = $idxDati;
//                itaLib::openForm($model);
//                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                $model();
            // break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridDati:
//                        $ditta = App::$utente->getKey('ditta');
//                        $filePdf=Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/'. $_POST[$this->nameForm . '_ITEPAS']['ITEWRD'];
//                        $dati = array();
//                        $dati = $this->altriDati;  //[$_POST['rowid']];
//                        $idxDati = $_POST['rowid'];
//                        $model = 'praPassoProcAgg';
//                        $rowid = $_POST[$this->nameForm . '_ITEPAS']['ROWID'];
//                        $_POST = array();
//                        $_POST['event'] = 'openform';
//                        $_POST[$model . '_returnEvent'] = 'returnPraPassoProcAgg';
//                        $_POST[$model . '_returnModel'] = $this->nameForm;
//                        $_POST['chiamante'] = $rowid;
//                        $_POST['perms'] = $this->perms;
//                        $_POST['filePdf']= $filePdf;
//                        $_POST['dati'] = $dati;
//                        $_POST['idxDati'] = $idxDati;
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
//                        break;
                }
                break;
            case 'delGridRow':
                Out::msgQuestion("ATTENZIONE!", "L'operazione è irreversibile. <br>Desidere cancellare il campo aggiuntivo?", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCampoAgg', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCampoAgg', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                $this->rowidAppoggio = $_POST['rowid'];
                break;
            case 'returnTipiAllegati':
                Out::valore($this->nameForm . '_RICITE[ITETAL]', $_POST['rowData']['valore']);
                if (strpos($_POST['rowData']['valore'], "99") !== false) {
                    Out::attributo($this->nameForm . '_RICITE[ITETAL]', 'readonly', '1');
                } else {
                    Out::attributo($this->nameForm . '_RICITE[ITETAL]', 'readonly', '0');
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_currRicnum');
        App::$utente->removeKey($this->nameForm . '_currPranum');
        App::$utente->removeKey($this->nameForm . '_currXHTML');
        App::$utente->removeKey($this->nameForm . '_currXHTMLDis');
        App::$utente->removeKey($this->nameForm . '_currDescBox');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_datiAppoggio');
        App::$utente->removeKey($this->nameForm . '_altriDati');
        App::$utente->removeKey($this->nameForm . '_close');
        App::$utente->removeKey($this->nameForm . '_page');
        App::$utente->removeKey($this->nameForm . '_ext');
        App::$utente->removeKey($this->nameForm . '_selRow');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnMethod;
        $_POST['model'] = $this->returnModel;
        $_POST['page'] = $this->page;
        $_POST['selRow'] = $this->selRow;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    public function Dettaglio($rowid, $tipo = 'codice', $Ricite_rec = array()) {
        $duplica = false;
        if (!$Ricite_rec) {
            $Ricite_rec = $this->praLib->GetRicite($rowid, $tipo);
            Out::attributo($this->nameForm . '_RICITE[ITESEQ]', "readonly", '0');
        } else {
            $duplica = true;
            Out::attributo($this->nameForm . '_RICITE[ITESEQ]', "readonly", '1');
        }


        if ($Ricite_rec['ITEMETA']) {
            $metadata = unserialize($Ricite_rec['ITEMETA']);
        }
        if (isset($metadata['TESTOBASEXHTML'])) {
            $this->currXHTML = $metadata['TESTOBASEXHTML'];
        }
        if (isset($metadata['TESTOBASEDISTINTA'])) {
            $this->currXHTMLDis = $metadata['TESTOBASEDISTINTA'];
        }
        if ($Ricite_rec['ITEHTML']) {
            $this->currDescBox = $Ricite_rec['ITEHTML'];
        }
        if (isset($metadata['TEMPLATENOMEUPLOAD'])) {
            Out::valore($this->nameForm . '_NomeFileUpload', $metadata['TEMPLATENOMEUPLOAD']);
        }
        if (isset($metadata['CODICECLASSIFICAZIONE'])) {
            Out::valore($this->nameForm . '_Classificazioni', $metadata['CODICECLASSIFICAZIONE']);
        }



        $this->currRicnum = $Ricite_rec['RICNUM'];
        $this->currItekey = $Ricite_rec['ITEKEY'];
        $Ananom_rec = $this->praLib->GetAnanom($Ricite_rec['ITERES']);
        $open_Info = 'Oggetto: ' . $Ricite_rec['ITECOD'] . ' - ' . $Ricite_rec['ITESEQ'];
        $this->openRecord($this->PRAM_DB, 'RICITE', $open_Info);
        Out::valori($Ricite_rec, $this->nameForm . '_RICITE');
        Out::valore($this->nameForm . '_accorpaPDFUpload', $Ricite_rec['ITEIDR']);
        Out::valore($this->nameForm . '_accorpaPDFTestoBase', $Ricite_rec['ITEIDR']);
        Out::valore($this->nameForm . '_accorpaPDFTestoBaseDis', $Ricite_rec['ITEIDR']);
        Out::valore($this->nameForm . '_uploadAutomatico', $Ricite_rec['ITEFILE']);


        if ($Ricite_rec['ITEOBL'] == 1) {
            Out::show($this->nameForm . '_ObblEspressione_field');
            Out::show($this->nameForm . '_ApriObblExpr');
        } else {
            Out::hide($this->nameForm . '_ObblEspressione_field');
            Out::hide($this->nameForm . '_ApriObblExpr');
        }


        $this->DecodResponsabile($Ananom_rec);
        $this->DecodAnamedCom($Ricite_rec['ITECDE']);
        $this->decodDocumenti($Ricite_rec['ITETBA']);
        $Praclt_rec = $this->praLib->GetPraclt($Ricite_rec['ITECLT']);
        Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
        Out::valore($this->nameForm . '_Pratica', substr($Ricite_rec['RICNUM'], 4, 6) . " / " . substr($Ricite_rec['RICNUM'], 0, 4));
        if ($duplica == false) {
            if ($Ricite_rec['ITEVPA'] != '')
                $this->DecodVaialpasso($Ricite_rec['ITEVPA'], 'itekey', $Ricite_rec['RICNUM']);
            if ($Ricite_rec['ITEVPN'] != '')
                $this->DecodVaialpassoNo($Ricite_rec['ITEVPN'], 'itekey', $Ricite_rec['RICNUM']);
            if ($Ricite_rec['ITECTP'] != '')
                $this->DecodCtrPasso($Ricite_rec['ITECTP'], 'itekey', $Ricite_rec['RICNUM']);
            $this->CaricoCampiAggiuntivi($Ricite_rec['ITEKEY'], $Ricite_rec['RICNUM']);
            $this->CaricaGriglia($this->gridDati, $this->altriDati);
        }
        if (!$Ricite_rec['ITEWRD'] == '' && pathinfo($Ricite_rec['ITEWRD'], PATHINFO_EXTENSION) == 'pdf') {
            Out::show($this->nameForm . '_ImportaCampi');
            if (!$this->altriDati) {
                Out::hide($this->nameForm . '_CancellaCampi');
                Out::hide($this->nameForm . '_EsportaDati');
            } else {
                Out::show($this->nameForm . '_CancellaCampi');
                Out::show($this->nameForm . '_EsportaDati');
            }
        } else {
            Out::hide($this->nameForm . '_ImportaCampi');
            Out::hide($this->nameForm . '_CancellaCampi');
            Out::hide($this->nameForm . '_EsportaDati');
        }
        if ($Ricite_rec['ITEUPL'] == 1 || $Ricite_rec['ITEMLT'] == 1) {
            Out::show($this->divCtrCampi);
            Out::show($this->DivUpload);
        } else {
            Out::hide($this->divCtrCampi);
            Out::hide($this->DivUpload);
        }

        if ($Ricite_rec['ITEDRR'] == 1) {
            Out::show($this->nameForm . '_rapportoConfig');
        } else {
            Out::hide($this->nameForm . '_rapportoConfig');
        }


        if ($Ricite_rec['ITEIRE'] == 1) {
            Out::show($this->divMail);
        } else {
            Out::hide($this->divMail);
        }
        if ($Ricite_rec['ITEIDR'] == 0) {
            Out::show($this->nameForm . "_RICITE[ITEIFC]");
            Out::show($this->nameForm . "_RICITE[ITEIFC]_lbl");
            if ($Ricite_rec['ITEIFC'] != 1) {
                Out::hide($this->nameForm . "_RICITE[ITETAL]");
                Out::hide($this->nameForm . "_RICITE[ITETAL]_butt");
                Out::hide($this->nameForm . "_RICITE[ITETAL]_lbl");
                Out::hide($this->nameForm . "_SvuotaTipi");
            } else {
                Out::show($this->nameForm . "_RICITE[ITETAL]");
                Out::show($this->nameForm . "_RICITE[ITETAL]_butt");
                Out::show($this->nameForm . "_RICITE[ITETAL]_lbl");
                Out::show($this->nameForm . "_SvuotaTipi");
            }
        } else {
            Out::hide($this->nameForm . "_RICITE[ITEIFC]");
            Out::hide($this->nameForm . "_RICITE[ITEIFC]_lbl");
            Out::hide($this->nameForm . "_RICITE[ITETAL]");
            Out::hide($this->nameForm . "_RICITE[ITETAL]_butt");
            Out::hide($this->nameForm . "_RICITE[ITETAL]_lbl");
            Out::hide($this->nameForm . "_SvuotaTipi");
        }

        if ($Ricite_rec['ITERIF'] == 0) {
            Out::hide($this->nameForm . '_divRiferimento');
        } else {
            Out::show($this->nameForm . '_divRiferimento');
            if ($Ricite_rec['ITEPROC']) {
                $Anapra_rif_rec = $this->praLib->GetAnapra($Ricite_rec['ITEPROC']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rif_rec['PRADES__1']);
                if ($Ricite_rec['ITEDAP'] != '')
                    $this->DecodDalPasso($Ricite_rec['ITEDAP'], 'itekey', $Ricite_rec['RICNUM']);
                if ($Ricite_rec['ITEALP'] != '')
                    $this->DecodAlPasso($Ricite_rec['ITEALP'], 'itekey', $Ricite_rec['RICNUM']);
            }
        }

        if ($Ricite_rec['ITEPUB'] == 1) {
            Out::show($this->nameForm . '_divSuap');
            Out::hide($this->nameForm . '_divGenerali');
            Out::hide($this->nameForm . '_RICITE[ITECOM]');
            Out::hide($this->nameForm . '_RICITE[ITECOM]_lbl');
        } else if ($Ricite_rec['ITECOM'] == 1) {
            Out::hide($this->nameForm . '_divSuap');
            Out::show($this->nameForm . '_divGenerali');
            Out::hide($this->nameForm . '_RICITE[ITEPUB]');
            Out::hide($this->nameForm . '_RICITE[ITEPUB]_lbl');
        } else if ($Ricite_rec['ITECOM'] == 0 && $Ricite_rec['ITEPUB'] == 0) {
            Out::show($this->nameForm . '_divSuap');
            Out::hide($this->nameForm . '_divGenerali');
            Out::block($this->nameForm . '_divSuap');
        }

        if ($Ricite_rec['ITEQST'] == 0) {
            Out::hide($this->nameForm . "_DestinazioneNo");
            Out::hide($this->nameForm . "_DestinazioneNo_butt");
            Out::hide($this->nameForm . "_DestinazioneNo_lbl");
            Out::hide($this->nameForm . "_DescrizioneVaiNo");
            Out::hide($this->nameForm . "_SvuotaNo");
            Out::hide($this->nameForm . "_RICITE[RCIRIS]_field");
            Out::html($this->nameForm . "_Destinazione_lbl", "Salta al Passo");
        } elseif ($Ricite_rec['ITEQST'] == 1) {
            Out::show($this->nameForm . "_DestinazioneNo");
            Out::show($this->nameForm . "_DestinazioneNo_butt");
            Out::show($this->nameForm . "_DestinazioneNo_lbl");
            Out::show($this->nameForm . "_DescrizioneVaiNo");
            Out::show($this->nameForm . "_SvuotaNo");
            Out::show($this->nameForm . "_RICITE[RCIRIS]_field");
            Out::html($this->nameForm . "_Destinazione_lbl", "Vai al Passo (Risposta SI)");
        }


        if ($Ricite_rec['ITEDAT'] == 1) {
            Out::show($this->nameForm . "_DivRaccolta");
        } else {
            Out::hide($this->nameForm . "_DivRaccolta");
        }
        if ($Ricite_rec['ITEDIS'] == 1) {
            Out::show($this->nameForm . "_DivDistinta");
        } else {
            Out::hide($this->nameForm . "_DivDistinta");
        }

        if ($Ricite_rec['ITEQCLA'] == 1) {
            Out::show($this->nameForm . "_Classificazioni");
        } else {
            Out::hide($this->nameForm . "_Classificazioni");
        }

        if ($Ricite_rec['ITERDM'] == 1) {
            Out::show($this->nameForm . "_RICITE[ITENRA]_field");
        } else {
            Out::hide($this->nameForm . "_RICITE[ITENRA]_field");
        }
        if ($Ricite_rec['ITECTB'] == 1) {
            Out::show($this->nameForm . "_editTestoBase");
        } else {
            Out::hide($this->nameForm . "_editTestoBase");
        }

        if ($Ricite_rec['ITEQALLE'] == 1) {
            Out::show($this->nameForm . "_RICITE[ITEQCLA]_field");
            Out::show($this->nameForm . "_RICITE[ITEQDEST]_field");
            Out::show($this->nameForm . "_RICITE[ITEQNOTE]_field");
            Out::show($this->nameForm . "_NomeFileUpload_field");
        } else {
            Out::hide($this->nameForm . "_RICITE[ITEQCLA]_field");
            Out::hide($this->nameForm . "_RICITE[ITEQDEST]_field");
            Out::hide($this->nameForm . "_RICITE[ITEQNOTE]_field");
            Out::hide($this->nameForm . "_NomeFileUpload_field");
            Out::hide($this->nameForm . "_Classificazioni");
        }

        if ($Ricite_rec['ITEDOW'] == 1 || $Ricite_rec['ITEDAT'] == 1 || $Ricite_rec['ITERDM'] == 1) {
            Out::show($this->nameForm . "_divPassoTemplate");
        } else {
            Out::hide($this->nameForm . "_divPassoTemplate");
        }

        if (strpos($Ricite_rec['ITETAL'], "99") !== false) {
            Out::attributo($this->nameForm . '_RICITE[ITETAL]', 'readonly', '1');
        } else {
            Out::attributo($this->nameForm . '_RICITE[ITETAL]', 'readonly', '0');
        }

        $metadati = unserialize($Ricite_rec['ITEMETA']);
        if ($metadati['TESTOBASEXHTML']) {
            Out::addClass($this->nameForm . "_pdfTestoBase", "ui-state-highlight");
        }
        if ($metadati['TESTOBASEDISTINTA']) {
            Out::addClass($this->nameForm . "_pdfTestoBaseDis", "ui-state-highlight");
        }
        if ($Ricite_rec['ITEHTML']) {
            Out::addClass($this->nameForm . "_OpenDescBox", "ui-state-highlight");
        }
        Out::valore($this->nameForm . "_AttivaEspressione", $this->praLib->DecodificaControllo($Ricite_rec['ITEATE']));
        Out::valore($this->nameForm . "_ObblEspressione", $this->praLib->DecodificaControllo($Ricite_rec['ITEOBE']));


        if ($duplica == false) {
            Out::hide($this->nameForm . '_Aggiungi');
            Out::hide($this->nameForm . '_Duplica');
            if ($Ricite_rec['ITEPRIV'] == 1) {
                $aggiorna = $this->checkSpegniAggiorna($Ricite_rec['ITECOD']);
                if ($aggiorna == true) {
                    Out::show($this->nameForm . '_Aggiorna');
                } else {
                    Out::hide($this->nameForm . '_Aggiorna');
                }
            }
        } else {
            Out::show($this->nameForm . '_Aggiungi');
            Out::show($this->nameForm . '_Duplica');
            Out::hide($this->nameForm . '_Aggiorna');
            Out::valore($this->nameForm . '_RICITE[ITESEQ]', "");
            Out::valore($this->nameForm . '_RICITE[ITEWRD]', "");
            Out::valore($this->nameForm . '_RICITE[ITEIMG]', "");
            Out::valore($this->nameForm . '_RICITE[ITEVPA]', "");
            Out::valore($this->nameForm . '_RICITE[ITEVPN]', "");
            Out::valore($this->nameForm . '_Destinazione', "");
            Out::valore($this->nameForm . '_DestinazioneVai', "");
            Out::valore($this->nameForm . '_DestinazioneNo', "");
            Out::valore($this->nameForm . '_DestinazioneVaiNo', "");
            Out::valore($this->nameForm . '_CtrPasso', "");
            Out::valore($this->nameForm . '_CtrDesPasso', "");
        }
        Out::setFocus('', $this->nameForm . '_RICITE[ITEPUB]');
        $this->caricaAllegati($Ricite_rec['ITEKEY'], $Ricite_rec['RICNUM']);
        //$this->CaricoCampiAggiuntivi($Ricite_rec['ITEKEY'], $Ricite_rec['RICNUM']);
    }

    public function apriInserimento() {
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Duplica');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->divCtrCampi);
        Out::valore($this->nameForm . "_RICITE[ITEPUB]", 1);
        Out::hide($this->nameForm . '_RICITE[ITECOM]');
        Out::hide($this->nameForm . '_RICITE[ITECOM]_lbl');
        Out::hide($this->nameForm . '_RICITE[ITETAL]');
        Out::hide($this->nameForm . '_RICITE[ITETAL]_lbl');
        Out::hide($this->nameForm . '_RICITE[ITETAL]_butt');
        Out::hide($this->nameForm . '_ObblEspressione_field');
        Out::hide($this->nameForm . '_ApriObblExpr');
        Out::hide($this->nameForm . '_DivRaccolta');
        Out::hide($this->nameForm . '_SvuotaTipi');
        //  Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");

        Out::show($this->nameForm . '_divSuap');
        Out::hide($this->nameForm . '_divGenerali');
        Out::hide($this->nameForm . '_DivUpload');
        Out::hide($this->nameForm . '_DivDistinta');
        Out::hide($this->nameForm . '_rapportoConfig');
        Out::hide($this->nameForm . '_DivMail');
        Out::hide($this->nameForm . '_divRiferimento');
        Out::hide($this->nameForm . "_DestinazioneNo");
        Out::hide($this->nameForm . "_DestinazioneNo_butt");
        Out::hide($this->nameForm . "_DestinazioneNo_lbl");
        Out::hide($this->nameForm . "_DescrizioneVaiNo");
        Out::hide($this->nameForm . "_SvuotaNo");
        Out::html($this->nameForm . "_Destinazione_lbl", "Salta al Passo");
        Out::valore($this->nameForm . "_RICITE[ITECOD]", $this->currPranum);
        Out::valore($this->nameForm . "_Pratica", substr($this->currRicnum, 4, 6) . " / " . substr($this->currRicnum, 0, 4));
        Out::setFocus('', $this->nameForm . '_RICITE[ITESEQ]');
    }

    public function ControllaSequenza($seqAttuale) {
        $trovato = false;
        $Ricite_tab = $this->praLib->GetRicite($this->currPranum, 'codice', true, 'ORDER BY ITESEQ', $this->currRicnum);
        foreach ($Ricite_tab as $key => $RiciteRec) {
            if ($seqAttuale == $RiciteRec['ITESEQ']) {
                $trovato = true;
                break;
            }
        }
        return $trovato;
    }

    public function checkSpegniAggiorna($procedimento) {
        $aggiorna = false;
        $proric_rec = $this->praLib->GetProric($this->currRicnum);
        //$Anapra_re = $this->praLib->GetAnapra($procedimento);
        //$Anatsp_rec = $this->praLib->GetAnatsp($Anapra_re['PRATSP']);
        $Anatsp_rec = $this->praLib->GetAnatsp($proric_rec['RICTSP']);
        $Utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('nomeUtente'));
        if ($Anatsp_rec['TSPSUPERADMIN'] != 0) {
            $gruppoSuperAdmin = str_pad($Anatsp_rec['TSPSUPERADMIN'], 10, '0', STR_PAD_LEFT);
        }
        if ($Utenti_rec['UTEGRU'] != 0) {
            if ($Utenti_rec['UTEGRU'] == 1 || $Utenti_rec['UTEGRU'] == $Anatsp_rec['TSPSUPERADMIN']) {
                $aggiorna = true;
            }
        }
        for ($i = 1; $i <= 30; $i++) {
            if ($Utenti_rec["UTEGEX__$i"] != 0) {
                $gruppo = str_pad($Utenti_rec["UTEGEX__$i"], 10, '0', STR_PAD_LEFT);
                if ($gruppo == 0000000001 || $gruppo == $gruppoSuperAdmin) {
                    $aggiorna = true;
                    break;
                }
            }
        }
        return $aggiorna;
    }

    public function Controlli() {
        $proges_rec = $this->praLib->GetProges($this->currRicnum, "richiesta");
        if ($proges_rec) {
            $data = substr($proges_rec['GESDRE'], 6, 2) . "/" . substr($proges_rec['GESDRE'], 4, 2) . "/" . substr($proges_rec['GESDRE'], 0, 4);
            Out::msgStop("Attenzione!!!", "Impossibile aggiornare il passo.<br>La richiesta on-line $this->currRicnum è stata già acquisita in data $data con n. pratica " . $proges_rec['GESNUM']);
            return false;
        }

        $sql = "SELECT * FROM RICITE WHERE ITECOD = '" . $_POST[$this->nameForm . '_RICITE']['ITECOD'] . "'AND RICNUM = $this->currRicnum AND ITEPUB<>0 AND (ITEUPL<>0 OR ITEMLT<>0) AND ITEIFC=1";
        $Ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if (count($Ricite_tab) > 7) {
            Out::msgStop("Errore.", "E' stato superato il numero massimo di passi upload per CAMERA DI COMEMRCIO.<br>Il numero di passi upload consentiti è 7");
            return false;
        }

        if ($_POST[$this->nameForm . '_RICITE']['ITEIRE']) {
            $Anapra_rec = $this->praLib->GetAnapra($_POST[$this->nameForm . '_RICITE']['ITECOD'], 'codice');
            if ($Anapra_rec['PRARES'] == "") {
                Out::msgInfo("Attenzione!", 'Il Passo è di tipo invio richiesta!!<br>Il Responsabile del procedimento non è stato inserito.<br>La procedura utilizzerà il responsabile dello sportello aggregato e dello sportello On-line.');
                //return false;
            }
        } else {
            if ($_POST[$this->nameForm . '_RICITE']['ITEPUB']) {
                if ($_POST[$this->nameForm . '_RICITE']['ITERES'] == "" && $_POST[$this->nameForm . '_Nome'] == "") {
                    Out::msgStop("Errore!", 'Il Passo è di tipo suap o invio richiesta!!<br>Scegliere il responsabile');
                    return false;
                }
            }
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEIFC'] == 1 && strlen($_POST[$this->nameForm . '_RICITE']['ITETAL'] == "")) {
            Out::msgStop("Errore!", 'Si scelto di inserire nel file COMUNICA il file come allegato generico.<br>Scegliere un tipo allegato');
            return false;
        }

        if ($_POST[$this->nameForm . '_RICITE']['ITERIF'] == 1) {
            if ($_POST[$this->nameForm . '_RICITE']['ITEPROC'] == "") {
                Out::msgStop("Errore!", 'Il passo è di tipo RIFERIMENTO.<br>Sceglire il procedimento di riferimento');
                return false;
            } else {
                if ($_POST[$this->nameForm . '_DalPasso'] == "" || $_POST[$this->nameForm . '_AlPasso'] == "") {
                    Out::msgStop("Errore!", 'Il passo è di tipo RIFERIMENTO.<br>Scegliere i passi di arrivo e partenza per il procedimento ' . $_POST[$this->nameForm . '_RICITE']['ITEPROC']);
                    return false;
                }
            }
        }

        if (strlen($_POST[$this->nameForm . '_RICITE']['ITETAL']) == 3) {
            Out::msgStop("Errore!", 'Inserire una descrizione per il tipo allegato 99');
            return false;
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEOBL'] != 1 && $_POST[$this->nameForm . '_RICITE']['ITEIFC'] == 2) {
            Out::msgStop("Errore!", 'Il tipo PDF Pratica deve essere un passo obbligatorio.<br>Ceccare Operazione Obbligatoria');
            return false;
        }
        if ($_POST[$this->nameForm . '_uploadAutomatico'] == 1 && $_POST[$this->nameForm . '_RICITE']['ITEWRD'] == "") {
            Out::msgStop("Errore!", 'Il passo è di tipo upload automatico.<br>Scegliere un testo associato');
            return false;
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITECOM']) {
            if ($_POST[$this->nameForm . '_RICITE']['ITECDE'] && $_POST[$this->nameForm . '_RICITE']['ITEINT'] == 1) {
                Out::msgStop("Errore.", 'Puoi scegliere un solo destinatario');
                return false;
            } else {
                if ($_POST[$this->nameForm . '_RICITE']['ITECDE'] == "" && $_POST[$this->nameForm . '_RICITE']['ITEINT'] == 0) {
                    Out::msgStop("Errore.", 'Il Passo è di tipo comunicazione!!<br>Scegliere il destinatario della comunicazione');
                    return false;
                }
            }
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEDOW'] == 1 && $_POST[$this->nameForm . '_RICITE']['ITEWRD'] == "" && $_POST[$this->nameForm . '_RICITE']['ITEURL'] == "") {
            Out::msgStop("Errore.", 'Il seguente passo è un passo download!!<br>Sceglire un testo associato o un url associato');
            return false;
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEDOW'] == 1 && $_POST[$this->nameForm . '_RICITE']['ITEWRD'] != "" && $_POST[$this->nameForm . '_RICITE']['ITEURL'] != "") {
            Out::msgStop("Errore.", 'Il passo può gestire solo il download da un url o di un testo!!<br>Scegliere una delle 2 opzioni.');
            return false;
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEQST'] == 1 && ($_POST[$this->nameForm . '_RICITE']['ITEVPA'] == "" && $_POST[$this->nameForm . '_RICITE']['ITEVPN'] == "")) {
            Out::msgStop("Errore.", 'Flag domanda presente!!<br>Scegliere i passi di destinazione');
            return false;
        }
        if ($_POST[$this->nameForm . '_RICITE']['ITEQST'] == 1 && $_POST[$this->nameForm . '_RICITE']['ITESEQ'] == "") {
            Out::msgStop("Errore.", 'Flag domanda presente!!<br>Decidere la sequenza del passo');
            return false;
        }
        if ($_POST[$this->nameForm . '_Destinazione'] != "") {
            if ($_POST[$this->nameForm . '_Destinazione'] < $_POST[$this->nameForm . '_RICITE']['ITESEQ']) {
                Out::msgStop("Errore.", 'Non è possibile saltare in un passo precedente.');
                return false;
            }
        }
        if ($_POST[$this->nameForm . '_DestinazioneNo'] != "") {
            if ($_POST[$this->nameForm . '_DestinazioneNo'] < $_POST[$this->nameForm . '_RICITE']['ITESEQ']) {
                Out::msgStop("Errore.", 'Non è possibile saltare in un passo precedente.');
                return false;
            }
        }

        $sql = "SELECT * FROM RICITE WHERE ITEKEY = '" . $_POST[$this->nameForm . '_RICITE']['ITEKEY'] . "'";
        $Ricite_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Ricite_rec['ITESEQ'] != "" && ($Ricite_rec['ITESEQ'] != $_POST[$this->nameForm . '_RICITE']['ITESEQ'])) {
            $sql1 = "SELECT * FROM RICITE WHERE ITEVPA = '" . $_POST[$this->nameForm . '_RICITE']['ITEKEY'] . "'";
            $sql2 = "SELECT * FROM RICITE WHERE ITEVPN = '" . $_POST[$this->nameForm . '_RICITE']['ITEKEY'] . "'";
            $Ricite_tab_si = ItaDB::DBSQLSelect($this->PRAM_DB, $sql1, true);
            $Ricite_tab_no = ItaDB::DBSQLSelect($this->PRAM_DB, $sql2, true);
            if ($Ricite_tab_si) {
                $minoreSi = false;
                foreach ($Ricite_tab_si as $key => $recordSi) {
                    if ($_POST[$this->nameForm . '_RICITE']['ITESEQ'] < $recordSi['ITESEQ']) {
                        $sequenza = $sequenza . " - " . $recordSi['ITESEQ'];
                        $minoreSi = true;
                    }
                }
                if ($minoreSi == true) {
                    Out::msgStop("Errore.", 'I passi con sequenza ' . $sequenza . ' saltano in questo passo.<br>Impossibile spostare il passo prima delle sequenze qui indicate.');
                    return false;
                }
            }
            if ($Ricite_tab_no) {
                $minoreNo = false;
                foreach ($Ricite_tab_no as $key => $recordNo) {
                    if ($_POST[$this->nameForm . '_RICITE']['ITESEQ'] < $recordNo['ITESEQ']) {
                        $seq = $seq . " - " . $recordNo['ITESEQ'];
                        $minoreNo = true;
                    }
                }
                if ($minoreNo == true) {
                    Out::msgStop("Errore.", 'I passi con sequenza ' . $seq . ' saltano in questo passo.<br>Impossibile spostare il passo prima delle sequenze qui indicate.');
                    return false;
                }
            }
        }
    }

    public function CreaCombo() {
        $sql = "SELECT * FROM ANAPAG";
        $anapag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        Out::select($this->nameForm . '_RICITE[ITEPAY]', 1, "", "1", "");
        foreach ($anapag_tab as $anapag_rec) {
            Out::select($this->nameForm . '_RICITE[ITEPAY]', 1, $anapag_rec['PAGCOD'], "0", $anapag_rec['PAGDES']);
        }
        Out::select($this->nameForm . '_RICITE[ITEARE]', 1, '', "0", '');
        Out::select($this->nameForm . '_RICITE[ITEARE]', 1, 'AMBIENTE', "0", 'AMBIENTE');
        Out::select($this->nameForm . '_RICITE[ITEARE]', 1, 'EDILIZIA', "0", 'EDILIZIA');
        Out::select($this->nameForm . '_RICITE[ITEARE]', 1, 'SICUREZZA', "0", 'SICUREZZA');
        Out::select($this->nameForm . '_RICITE[ITERUO]', 1, '', "0", 'TUTTI');
        Out::select($this->nameForm . '_RICITE[ITERUO]', 1, '0001', "0", 'ESIBENTE');
        Out::select($this->nameForm . '_RICITE[ITERUO]', 1, '0002', "0", 'PROCURATORE');
        Out::select($this->nameForm . '_RICITE[ITERUO]', 1, '0003', "0", 'AGENZIE');

        Out::select($this->nameForm . '_RICITE[ITEIFC]', 1, '0', "1", 'No');
        Out::select($this->nameForm . '_RICITE[ITEIFC]', 1, '1', "0", 'Si Allegato Generico');
        Out::select($this->nameForm . '_RICITE[ITEIFC]', 1, '2', "0", 'Si PDF Pratica');

        Out::select($this->nameForm . '_RICITE[RCIRIS]', 1, '', "1", '');
        Out::select($this->nameForm . '_RICITE[RCIRIS]', 1, 'SI', "0", 'Si');
        Out::select($this->nameForm . '_RICITE[RCIRIS]', 1, 'NO', "0", 'No');
        //Out::select($this->nameForm . '_RICITE[ITEIFC]', 1, '3', "0", 'Si PDF Distinta');
    }

    function DecodResponsabile($Ananom_rec) {
        Out::valore($this->nameForm . '_RICITE[ITERES]', $Ananom_rec["NOMRES"]);
        Out::valore($this->nameForm . '_Nome', $Ananom_rec["NOMCOG"] . ' ' . $Ananom_rec["NOMNOM"]);
        $AnauniRes_rec = $this->praLib->GetAnauniRes($Ananom_rec['NOMRES']);
        $Anauni_rec = $this->praLib->getAnauni($AnauniRes_rec['UNISET']);
        Out::valore($this->nameForm . '_RICITE[ITESET]', $Anauni_rec['UNISET']);
        Out::valore($this->nameForm . '_SETTORE', $Anauni_rec['UNIDES']);
        if ($AnauniRes_rec['UNISER'] == "")
            $AnauniRes_rec['UNISET'] = "";
        $AnauniServ_rec = $this->praLib->GetAnauniServ($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER']);
        Out::valore($this->nameForm . '_RICITE[ITESER]', $AnauniServ_rec['UNISER']);
        Out::valore($this->nameForm . '_SERVIZIO', $AnauniServ_rec['UNIDES']);
        if ($AnauniRes_rec['UNISET'] == "")
            $AnauniRes_rec['UNIOPE'] = "";
        $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
        Out::valore($this->nameForm . '_RICITE[ITEOPE]', $AnauniOpe_rec['UNIOPE']);
        Out::valore($this->nameForm . '_UNITA', $AnauniOpe_rec['UNIDES']);
    }

    function DecodVaialpasso($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        Out::valore($this->nameForm . '_Destinazione', $Ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DescrizioneVai', $Ricite_rec['ITEDES']);
        Out::valore($this->nameForm . '_RICITE[ITEVPA]', $Ricite_rec['ITEKEY']);
    }

    function DecodVaialpassoNo($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        Out::valore($this->nameForm . '_DestinazioneNo', $Ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DescrizioneVaiNo', $Ricite_rec['ITEDES']);
        Out::valore($this->nameForm . '_RICITE[ITEVPN]', $Ricite_rec['ITEKEY']);
    }

    function DecodCtrPasso($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        Out::valore($this->nameForm . '_CtrPasso', $Ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_CtrDesPasso', $Ricite_rec['ITEDES']);
        Out::valore($this->nameForm . '_RICITE[ITECTP]', $Ricite_rec['ITEKEY']);
    }

    function DecodDalPasso($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        Out::valore($this->nameForm . '_DalPasso', $Ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DesDalPasso', $Ricite_rec['ITEDES']);
        Out::valore($this->nameForm . '_RICITE[ITEDAP]', $Ricite_rec['ITEKEY']);
    }

    function DecodAlPasso($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        Out::valore($this->nameForm . '_AlPasso', $Ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_DesAlPasso', $Ricite_rec['ITEDES']);
        Out::valore($this->nameForm . '_RICITE[ITEALP]', $Ricite_rec['ITEKEY']);
    }

    function DecodDuplicaPasso($codice, $tipo = 'itekey', $pratica = '') {
        $Ricite_rec = $this->praLib->GetRicite($codice, $tipo, false, '', $pratica);
        $this->Dettaglio($codice, $tipo, $Ricite_rec);
    }

    function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => 'Info non presente'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function AllegaTesto() {
        $origFile = $_POST['uploadedFile'];
        $nomeFile = $_POST['file'];
        $ext = pathinfo($nomeFile, PATHINFO_EXTENSION);
        foreach ($this->ext as $key => $est) {
            if ($ext != $est['EXT']) {
                Out::msgStop('ERRORE!!', "L'estensione del file scelto non è tra quelle gestite.<br>Gestire l'estensione o sciegliere un altro file");
                return false;
            }
        }
        if ($nomeFile != '') {
            if ($ditta == '')
                $ditta = App::$utente->getKey('ditta');
            $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
            eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
            $destinazione = $destinazione . "repository/" . $this->currRicnum . "/testiAssociati/";

            if (!is_dir($destinazione)) {
                Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: ' . $destinazione);
                return false;
            }
            $nomeFile = str_replace(' ', '_', $nomeFile);
            if (strlen($nomeFile) > 100) {
                Out::msgStop("Attenzione!", "Rinominare il File, il nome non deve essere più lungo di 100 caratteri.");
                return false;
            }
            if (file_exists($destinazione . $nomeFile)) {
                Out::msgQuestion("Attenzione.", "Il nome del File coincide con uno già esistente. Sovrascrivere il File da Caricare?", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSov', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Sovrascrivi' => array('id' => $this->nameForm . '_Sovrascrivi', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                $this->datiAppoggio = array('origFile' => $origFile, 'destinazione' => $destinazione, 'nomeFile' => $nomeFile);
                //                Out::msgStop("Attenzione!", "Il nome del File coincide con uno già esistente. Rinominare il File da Caricare!");
                return false;
            }
            if (!@rename($origFile, $destinazione . $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm . '_RICITE[ITEWRD]', $nomeFile);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

    function AllegaFile() {
        $origFile = $_POST['uploadedFile'];
        $nomeFile = $_POST['file'];
        $ext = strtolower(pathinfo($nomeFile, PATHINFO_EXTENSION));
        $arayImg = array('jpeg', 'bmp', 'gif', 'jpg');
        if (in_array($ext, $arayImg) !== true) {
            Out::msgStop('ERRORE!!', "L'estensione del file scelto non è un'immagine.<br>Sciegliere un altro file");
            return false;
        }

        if ($nomeFile != '') {
            if ($ditta == '')
                $ditta = App::$utente->getKey('ditta');
            $comando = '$destinazione="' . Config::getPath('general.itaCms') . '";';
            eval($comando);
//                            $destinazione = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
            $destinazione = $destinazione . "repository/" . $this->currRicnum . "/immagini/";

            if (!is_dir($destinazione)) {
                Out::msgStop("Errore in caricamento file testo.", 'Destinazione non esistente: ' . $destinazione);
                return false;
            }
            $nomeFile = str_replace(' ', '_', $nomeFile);
            if (strlen($nomeFile) > 24) {
                Out::msgStop("Attenzione!", "Rinominare il File, il nome non deve essere più lungo di 20 caratteri.");
                return false;
            }
            if (file_exists($destinazione . $nomeFile)) {
                Out::msgStop("Attenzione!", "Il nome del File coincide con uno già esistente. Rinominare il File da Caricare!");
                return false;
            }
            if (!@rename($origFile, $destinazione . $nomeFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                Out::valore($this->nameForm . '_RICITE[ITEIMG]', $nomeFile);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return false;
        }
        return true;
    }

    function DecodAnamedCom($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $Anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        Out::valore($this->nameForm . '_RICITE[ITECDE]', $Anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_DESTINATARIO', $Anamed_rec['MEDNOM']);
        return $Anamed_rec;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1') {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($_appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    public function CaricoCampiAggiuntivi($codice, $pratica) {
        $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '$codice' AND DAGNUM = '$pratica'";
        if ($_POST['_search'] === 'true') { // setta all'occorrenza filtro per la ricerca 
            $seqcampo = addslashes($_POST['DAGSEQ']);
            $nomecampo = addslashes($_POST['DAGKEY']);
            $descrizionecampo = addslashes($_POST['DAGDES']);
            $dagtipo = addslashes($_POST['DAGTIP']);
            $datiacquisiti = addslashes($_POST['RICDAT']);
            if ($seqcampo) {
                $sql .= " AND DAGSEQ LIKE '%$seqcampo%'";
            }
            if ($nomecampo) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGKEY') . " LIKE '%" . strtoupper($nomecampo) . "%'";
            }
            if ($descrizionecampo) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DAGDES') . " LIKE '%" . strtoupper($descrizionecampo) . "%'";
            }
            if ($dagtipo) {
                $sql .= " AND DAGTIP LIKE '%$dagtipo%'";
            }
            if ($datiacquisiti) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('RICDAT') . " LIKE '%" . strtoupper($datiacquisiti) . "%'";
            }
        }
        $sql .= " ORDER BY DAGSEQ";
        $this->altriDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        foreach ($this->altriDati as $key => $value) {
            if(!$value['DAGCTR']){
                continue;
            }
            $decode = $this->praLib->DecodificaControllo($value['DAGCTR']);
            if($decode){
                 $img = "<span class=\"ita-icon ita-icon-bullet-red-16x16\">Richiesta inoltrata</span>";
            $this->altriDati[$key]['DECODEDAGCTR'] = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"$decode\">" .$img. "</span></div>";
            }
        }
        $this->CaricaGriglia($this->gridDati, $this->altriDati);
    }

    function InserisciDatiAggiuntivi($codice) {
        //Inizializzo l array di ritorno
        $retInsertDatiAgg = array(
            'status' => true,
            'message' => '');

        /*
          //mi trovo i dati aggiuntivi nel DB
          $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $this->currRicnum . "' AND ITEKEY ='$codice' ORDER BY DAGSEQ";
          $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

          //Controllo se il count tra i 2 array è ugauale
          $countDB = count($Ricdag_tab);
          $countArray = count($this->altriDati);
          if ($countDB != $countArray) {
          $retInsertDatiAgg['status'] = false;
          $retInsertDatiAgg['message'] = "Il numero dei campi aggiuntivi nel DB ($countDB) non corrisponde al numero campi aggiuntivi in array ($countArray).<br><b>RICARICARE IL PASSO</b>";
          return $retInsertDatiAgg;
          }

          //Controllo se i 2 array sono uguali
          if ($Ricdag_tab !== $this->altriDati) {
          $retInsertDatiAgg['status'] = false;
          $retInsertDatiAgg['message'] = "I campi aggiuntivi nel DB sono diversi ai campi aggiuntivi in array.<br><b>RICARICARE IL PASSO</b>";
          return $retInsertDatiAgg;
          }
         */
        if (!$this->CancellaDatiAggiuntivi($codice)) {
            $retInsertDatiAgg['status'] = false;
            $retInsertDatiAgg['message'] = "Errore in cancellazione dei dati aggiuntivi";
            return $retInsertDatiAgg;
        }
        $seq = 0;
        foreach ($this->altriDati as $dati) {
            $seq += 10;
            $Ricdag_rec = array();
            $Ricdag_rec = $dati;
            $Ricdag_rec['ROWID'] = 0;
            $Ricdag_rec['DAGSEQ'] = $seq;
            //$Ricdag_rec = array();
            $Ricdag_rec['ITEKEY'] = $codice;
            $Ricdag_rec['DAGKEY'] = $dati['DAGKEY'];
            $Ricdag_rec['DAGALIAS'] = $dati['DAGALIAS'];
            $Ricdag_rec['DAGSEQ'] = $seq;
            $Ricdag_rec['DAGNUM'] = $this->currRicnum;
            $Ricdag_rec['ITECOD'] = $_POST[$this->nameForm . '_RICITE']['ITECOD'];
            $Ricdag_rec['DAGVAL'] = $dati['DAGVAL'];
            $Ricdag_rec['DAGTIP'] = $dati['DAGTIP'];
            $Ricdag_rec['DAGCTR'] = $dati['DAGCTR'];
            $Ricdag_rec['DAGNOT'] = $dati['DAGNOT'];
            $Ricdag_rec['DAGDES'] = $dati['DAGDES'];

            $Ricdag_rec['DAGLAB'] = $dati['DAGLAB'];
            $Ricdag_rec['DAGTIC'] = $dati['DAGTIC'];
            $Ricdag_rec['DAGROL'] = $dati['DAGROL'];
            $Ricdag_rec['DAGVCA'] = $dati['DAGVCA'];
            $Ricdag_rec['DAGREV'] = $dati['DAGREV'];
            $Ricdag_rec['DAGLEN'] = $dati['DAGLEN'];
            $Ricdag_rec['DAGDIM'] = $dati['DAGDIM'];
            $Ricdag_rec['DAGDIZ'] = $dati['DAGDIZ'];
            $Ricdag_rec['DAGACA'] = $dati['DAGACA'];

            $insert_Info = 'Oggetto : Inserimento dato aggiuntivo' . $Ricdag_rec['ITDKEY'] . " del passo" . $Ricdag_rec['ITEKEY'];
            if (!$this->insertRecord($this->PRAM_DB, 'RICDAG', $Ricdag_rec, $insert_Info)) {
                $retInsertDatiAgg['status'] = false;
                $retInsertDatiAgg['message'] = "Errore in inserimento dei dati aggiuntivi";
                return $retInsertDatiAgg;
            }
        }
        return $retInsertDatiAgg;
    }

    function CancellaDatiAggiuntivi($codice) {
        if ($this->currRicnum) {
            $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $this->currRicnum . "' AND ITEKEY ='$codice'";
            $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            foreach ($Ricdag_tab as $Ricdag_rec) {
                $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $Ricdag_rec['ITDKEY'] . " pratica: " . $this->currRicnum . " passo: " . $Ricdag_rec['ITEKEY'];
                if (!$this->deleteRecord($this->PRAM_DB, 'RICDAG', $Ricdag_rec['ROWID'], $delete_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    function decodDocumenti($codice, $tipo = 'codice') {
        $documenti_rec = $this->docLib->getDocumenti($codice, $tipo);
        Out::valore($this->nameForm . "_RICITE[ITETBA]", $documenti_rec['CODICE']);
        Out::valore($this->nameForm . "_DocumentoOgg", $documenti_rec['OGGETTO']);
    }

    public function caricaAllegati($codice, $pratica) {
        $praLibRiservato = new praLibRiservato;

        $this->allegati = array();

        $sql = "SELECT * FROM RICDOC WHERE DOCNUM = '$pratica' AND ITEKEY = '$codice'";

        if ($_POST['_search'] === 'true') {
            $nomefile = addslashes($_POST['DOCUPL']);
            $nomefile_orig = addslashes($_POST['DOCNAME']);
            $classificazione_all = $_POST['CLASSIFICAZIONE'];
            $note_2 = addslashes($_POST['NOTE']);

            if ($nomefile) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DOCUPL') . " LIKE '%" . strtoupper($nomefile) . "%'";
            }
            if ($nomefile_orig) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('DOCNAME') . " LIKE '%" . strtoupper($nomefile_orig) . "%'";
            }
        }

        $sql .= " ORDER BY DOCUPL";

        $allegati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        foreach ($allegati as $allegato) {
            $ext = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
            $arrayMeta = $anacla_rec = $anaddo_rec = array();
            $strDest = $edit = "";
            if (strtolower($ext) == "p7m") {
                $edit = "<span class=\"ita-icon ita-icon-shield-blue-16x16\">Verifica Firma</span>";
            }
            $classificazione = '';

            if ($allegato['DOCMETA']) {
                $arrayMeta = unserialize($allegato['DOCMETA']);
                $anacla_rec = $this->praLib->GetAnacla($arrayMeta['CLASSIFICAZIONE']);
                $classificazione = $anacla_rec['CLADES'];
                $note_1 = $arrayMeta['NOTE'];
                if (is_array($arrayMeta['DESTINAZIONE'])) {
                    foreach ($arrayMeta['DESTINAZIONE'] as $dest) {
                        $anaddo_rec = $this->praLib->GetAnaddo($dest);
                        $strDest .= $anaddo_rec['DDONOM'] . "<br>";
                    }
                }
            }

            if ($classificazione_all) {
                if (strpos($classificazione, $classificazione_all) === false) {
                    continue;
                }
            }
            if ($note_2) {
                if (strpos($note_1, $note_2) === false) {
                    continue;
                }
            }

            //
            $pathAllegatiRichieste = $this->praLib->getPathAllegatiRichieste();
            $filePath = $pathAllegatiRichieste . "attachments/" . $pratica . "/" . $allegato['DOCUPL'];
            //
            $this->allegati[] = array(
                "ROWID" => $allegato['ROWID'],
                "DOCUPL" => $allegato['DOCUPL'],
                "FIRMA" => $edit,
                "DOCNAME" => $allegato['DOCNAME'],
                "SIZE" => $this->praLib->formatFileSize(filesize($filePath)),
                "CLASSIFICAZIONE" => $classificazione,
                "DESTINAZIONI" => $strDest,
                "NOTE" => $arrayMeta['NOTE'],
                'RISERVATO' => $praLibRiservato->getIconRiservato($allegato['DOCRIS'])
            );
        }
        $this->CaricaGriglia($this->gridAllegati, $this->allegati);
    }

}

?>