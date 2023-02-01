<?php

/**
 *
 * Firma Remota
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    RemoteSign
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    25.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/RemoteSign/rsnSigner.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once(ITA_LIB_PATH . '/itaPHPARSS/itaARSSParam.class.php');

function rsnAuth() {
    $rsnAuth = new rsnAuth();
    $rsnAuth->parseEvent();
    return;
}

class rsnAuth extends itaModel {

    public $nameForm = "rsnAuth";
    public $gridAllegati = "rsnAuth_gridAllegati";
    public $topMsg;
    public $inputFilePath;
    public $outputFilePath;
    public $inputFileName;
    public $outputFileName;
    public $fileNameFirmato;
    public $multiFile = false;
    public $allegati = array();
    public $returnMultiFile = false;
    public $forceSignMethod;
    public $accLib;
    public $signMethod;
    public $chiediCredenziali;

    function __construct() {
        parent::__construct();
        $this->returnMultiFile = App::$utente->getKey($this->nameForm . "_returnMultiFile");
        $this->multiFile = App::$utente->getKey($this->nameForm . "_multiFile");
        $this->allegati = App::$utente->getKey($this->nameForm . "_allegati");
        $this->inputFilePath = App::$utente->getKey($this->nameForm . '_inputFilePath');
        $this->outputFilePath = App::$utente->getKey($this->nameForm . '_outputFilePath');
        $this->inputFileName = App::$utente->getKey($this->nameForm . '_inputFileName');
        $this->outputFileName = App::$utente->getKey($this->nameForm . '_outputFileName');
        $this->fileNameFirmato = App::$utente->getKey($this->nameForm . '_fileNameFirmato');
        $this->signMethod = App::$utente->getKey($this->nameForm . '_signMethod');
        $this->forceSignMethod = App::$utente->getKey($this->nameForm . '_forceSignMethod');
        $this->topMsg = App::$utente->getKey($this->nameForm . '_topMsg');
        $this->chiediCredenziali = App::$utente->getKey($this->nameForm . '_chiediCredenziali');
        $this->accLib = new accLib();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_returnMultiFile", $this->returnMultiFile);
            App::$utente->setKey($this->nameForm . "_multiFile", $this->multiFile);
            App::$utente->setKey($this->nameForm . "_allegati", $this->allegati);
            App::$utente->setKey($this->nameForm . '_inputFilePath', $this->inputFilePath);
            App::$utente->setKey($this->nameForm . '_outputFilePath', $this->outputFilePath);
            App::$utente->setKey($this->nameForm . '_inputFileName', $this->inputFileName);
            App::$utente->setKey($this->nameForm . '_outputFileName', $this->outputFileName);
            App::$utente->setKey($this->nameForm . '_fileNameFirmato', $this->fileNameFirmato);
            App::$utente->setKey($this->nameForm . '_signMethod', $this->signMethod);
            App::$utente->setKey($this->nameForm . '_forceSignMethod', $this->forceSignMethod);
            App::$utente->setKey($this->nameForm . '_topMsg', $this->topMsg);
            App::$utente->setKey($this->nameForm . '_chiediCredenziali', $this->chiediCredenziali);
        }
    }

    public function getReturnMultiFile() {
        return $this->returnMultiFile;
    }

    public function setReturnMultiFile($returnMultiFile) {
        $this->returnMultiFile = $returnMultiFile;
    }

    public function getAllegati() {
        return $this->allegati;
    }

    public function setAllegati($allegati) {
        $this->allegati = $allegati;
    }

    public function getMultiFile() {
        return $this->multiFile;
    }

    public function setMultiFile($multiFile) {
        $this->multiFile = $multiFile;
    }

    public function getTopMsg() {
        return $this->topMsg;
    }

    public function getInputFilePath() {
        return $this->inputFilePath;
    }

    public function setInputFilePath($inputFilePath) {
        $this->inputFilePath = $inputFilePath;
    }

    public function getOutputFilePath() {
        return $this->outputFilePath;
    }

    public function setOutputFilePath($outputFilePath) {
        $this->outputFilePath = $outputFilePath;
    }

    public function getInputFileName() {
        return $this->inputFileName;
    }

    public function setInputFileName($inputFileName) {
        $this->inputFileName = $inputFileName;
    }

    public function getOutputFileName() {
        return $this->outputFileName;
    }

    public function setOutputFileName($outputFileName) {
        $this->outputFileName = $outputFileName;
    }

    public function getFileNameFirmato() {
        return $this->fileNameFirmato;
    }

    public function setFileNameFirmato($fileNameFirmato) {
        $this->fileNameFirmato = $fileNameFirmato;
    }

    public function setTopMsg($topMsg) {
        $this->topMsg = $topMsg;
    }

    function getForceSignMethod() {
        return $this->forceSignMethod;
    }

    function setForceSignMethod($forceSignMethod) {
        $this->forceSignMethod = $forceSignMethod;
    }

    public function getChiediCredenziali() {
        return $this->chiediCredenziali;
    }

    public function setChiediCredenziali($chiediCredenziali) {
        $this->chiediCredenziali = $chiediCredenziali;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                /*
                 * Carico la lista degli allegati in caso di firma singolo
                 * da variabili di classe settate precedentemente
                 * 
                 */
                if (!$this->allegati) {
                    $this->allegati = array(
                        array(
                            'FILEORIG' => $this->inputFileName,
                            'INPUTFILEPATH' => $this->inputFilePath,
                            'FILENAMEFIRMATO' => $this->fileNameFirmato,
                            'SIGNRESULT' => "",
                            'SIGNMESSAGE' => ""
                        )
                    );
                }


                /*
                 * Inizializza la form
                 * 
                 */
                Out::valore($this->nameForm . '_InputFileName', $this->inputFileName);
                Out::hide($this->nameForm . '_InputFileName_field');

                /*
                 * Funzioni di controllo firma in funzione dei domii configurato e
                 * in funzione dei tupu file da firmare
                 * 
                 */
                $this->creaCombo();
                $this->creaComboSignMethod($this->calculateSignMetods($this->allegati));


                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                if (!$this->multiFile) {
                    out::hide($this->gridAllegati . "_addGridRow");
                    out::hide($this->gridAllegati . "_delGridRow");
                }

                $rsnSigner = new rsnSigner();
                $html = "<div>";
                $html .= "<div style=\"display:inline-block;\"><img width=40px style=\"margin:2px;\" src=\"" . $rsnSigner->getSignerLogo() . "\"></img></div>";
                $html .= "<div style=\"display:inline-block;vertical-align:middle;padding-left:5px;\">$this->topMsg</div>";
                $html .= "</div>";
                Out::html($this->nameForm . "_topMsg", $html);
                $firmaRec_rec = $this->accLib->GetFirmaRemota(App::$utente->getKey('idUtente'));
                $arssParam = new itaARSSParam();
                if (!$arssParam->getPaperTokenBackend()) {
                    Out::removeElement($this->nameForm . '_paperTokenCoords_field');
                    Out::removeElement($this->nameForm . '_GetPaperTokenCoords');
                }
                Out::valore($this->nameForm . '_otpauth', rsnSigner::$TYPES_OPT_AUTH[0]);
                if ($arssParam->getDefaultOtpAuth()) {
                    Out::valore($this->nameForm . '_otpauth', $arssParam->getDefaultOtpAuth());
                }
                if ($firmaRec_rec) {
                    if ($firmaRec_rec['OtpAuth']) {
                        Out::valore($this->nameForm . '_otpauth', $firmaRec_rec['OtpAuth']);
                    }
                    if ($firmaRec_rec['Utente']) {
                        $this->displayCoords($firmaRec_rec['Utente']);
                        Out::valore($this->nameForm . '_utente', $firmaRec_rec['Utente']);
                        if ($firmaRec_rec['Password']) {
                            Out::valore($this->nameForm . '_password', $firmaRec_rec['Password']);
                            Out::setFocus('', $this->nameForm . '_otp');
                        } else {
                            Out::setFocus('', $this->nameForm . '_password');
                        }
                    } else {
                        Out::setFocus('', $this->nameForm . '_utente');
                    }
                } else {
                    Out::setFocus('', $this->nameForm . '_utente');
                }
                // Bottone Chiedi Credenziali
                out::hide($this->nameForm . '_ConfermaCredenziali');
                if ($this->chiediCredenziali) {
                    out::show($this->nameForm . '_ConfermaCredenziali');
                    out::hide($this->nameForm . '_Conferma');
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_utente':
                        $this->displayCoords($_POST[$this->nameForm . '_utente'], $_POST[$this->nameForm . '_utente']);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_GetPaperTokenCoords':
                        $this->displayCoords($_POST[$this->nameForm . '_utente'], $_POST[$this->nameForm . '_password']);
                        break;

                    case $this->nameForm . '_Conferma':
                        if (count($this->allegati) == 0) {
                            Out::msgInfo("Attenzione", "Nessun documento da firmare");
                            break;
                        }

                        /*
                         * Istanza signer con i dati di identità
                         * 
                         */
                        $Signer = new rsnSigner();
                        $Signer->setTypeOtpAuth($_POST[$this->nameForm . '_otpauth']);
                        $Signer->setOtpPwd($_POST[$this->nameForm . '_otp']);
                        $Signer->setUser($_POST[$this->nameForm . '_utente']);
                        $Signer->setPassword($_POST[$this->nameForm . '_password']);

                        /*
                         * Metodo di firma
                         * 
                         */
                        $this->signMethod = $_POST[$this->nameForm . '_signMethod'];
                        if ($this->signMethod == rsnSigner::TYPE_SIGN_PADES) {
                            Out::msgInfo("Attenzione", "Metodo di firma in fase di implementazione");
                            break;
                        }

                        if (count($this->allegati) == 1 && $this->returnMultiFile == false) {
                            $this->signSingle($Signer);
                        } else {
                            $this->signMulti($Signer);
                        }
                        break;
                    case $this->nameForm . '_ConfermaCredenziali':
                        if (count($this->allegati) == 0) {
                            Out::msgInfo("Attenzione", "Nessun documento da firmare");
                            break;
                        }

                        /*
                         * Istanza signer con i dati di identità
                         * 
                         */
//                        $Signer = new rsnSigner();
//                        $Signer->setTypeOtpAuth($_POST[$this->nameForm . '_otpauth']);
//                        $Signer->setOtpPwd($_POST[$this->nameForm . '_otp']);
//                        $Signer->setUser($_POST[$this->nameForm . '_utente']);
//                        $Signer->setPassword($_POST[$this->nameForm . '_password']);
//                        $SessionID = $Signer->openSession();
//                        if (!$SessionID) {
//                            Out::msgStop("Attenzione", $Signer->getReturnCode() . "-" . $Signer->getMessage());
//                            $this->returnToParent(false);
//                        }
                        $resultData = array();
//                        $resultData['SESSIONID'] = $SessionID;
                        $resultData['SIGNMETHOD'] = $_POST[$this->nameForm . '_signMethod'];
                        $resultData['TYPEOTPAUTH'] = $_POST[$this->nameForm . '_otpauth'];
                        $resultData['OTPPWD'] = $_POST[$this->nameForm . '_otp'];
                        $resultData['USER'] = $_POST[$this->nameForm . '_utente'];
                        $resultData['PASSWORD'] = $_POST[$this->nameForm . '_password'];
                        $this->returnToParent($resultData);
                        break;

                    case $this->nameForm . '_InputFileName_butt':
                        Out::openDocument(utiDownload::getUrl($this->inputFileName, $this->inputFilePath));
                        break;

                    case 'close-portlet':
                        $this->returnToParent("cancel");
                        break;
                }
                break;

            case 'dbClickRow':
                $allegato = $this->allegati[$_POST['rowid']];
                if (!$allegato) {
                    break;
                }
                Out::openDocument(
                        utiDownload::getUrl(
                                $allegato['FILEORIG'], $allegato['INPUTFILEPATH']
                        )
                );
                break;

            case 'delGridRow':
                $allegato = $this->allegati[$_POST['rowid']];
                if (!$allegato) {
                    break;
                }
                if ($allegato['SIGNRESULT'] == "OK") {
                    Out::msgInfo("Attenzione", "Il file risulta firmato, scaricare.");
                    break;
                }

                if (file_exists($allegato['INPUTFILEPATH'])) {
                    if (!@unlink($allegato['INPUTFILEPATH'])) {
                        Out::msgStop("Attenzione", "Errore in cancellazione file da firmare");
                        break;
                    }
                }
                unset($this->allegati[$_POST['rowid']]);
                $this->creaComboSignMethod($this->calculateSignMetods($this->allegati));
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;
            case 'addGridRow':
                // TODO : Nuovo Metodo di Apertura form    
                $model = 'utiUploadDiag';
                $_POST = Array();
                $_POST['event'] = 'openform';
                $_POST['messagge'] = '<div style="text-align:center;" class="ita-box ui-widget-content ui-corner-all "><div style="vertical-align:middle;display:inline-block;" class="ita-icon ita-icon-sigillo-32x32"></div><div style="top:auto; display:inline-block;font-size:1.5em;color:green;vertical-align:middle;">Aggiungi il file da firmare</div></div><br>';
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnEvent'] = "returnUpload";
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'cellSelect':
                $allegato = $this->allegati[$_POST['rowid']];
                if (!$allegato) {
                    break;
                }
                $path = $allegato['OUTPUTFILEPATH'];
                $fileOriginale = $allegato['FILEFIRMATO'];
                switch ($_POST['colName']) {
                    case "SaveIcon":
                        if ($allegato['SaveIcon'] != '') {
                            Out::openDocument(utiDownload::getUrl($this->allegati[$_POST['rowid']]['OUTPUTFILENAME'], $this->allegati[$_POST['rowid']]['OUTPUTFILEPATH'], true));
                            break;
                        }
                    case "ResultIcon":
                        if ($allegato["SIGNRESULT"] == "OK") {
                            $model = "utiP7m";
                            itaLib::openForm($model);
                            $modelObj = itaModel::getInstance($model);
                            $modelObj->setEvent("openform");
                            $_POST['file'] = $path;
                            $_POST['fileOriginale'] = $fileOriginale;
                            $modelObj->parseEvent();
                        }
                        break;
                }
                break;

            case 'returnUpload':
                $allegato = array(
                    'FILEORIG' => $_POST['file'],
                    'INPUTFILEPATH' => $_POST['uploadedFile'],
                    'SIGNRESULT' => ""
                );

                $allegato['OUTPUTFILEPATH'] = $_POST['uploadedFile'];

                $this->allegati[] = $allegato;
                $this->creaComboSignMethod($this->calculateSignMetods($this->allegati));
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;
        }
    }

    public function returnToParent($result) {
        /* @var $returnModelObj itaModel */
        $returnModelObj = itaModel::getInstance($this->returnModel);
        if ($returnModelObj == false) {
            return;
        }
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['result'] = $result;
        $_POST['outputFilePath'] = $this->outputFilePath;
        $_POST['inputFilePath'] = $this->inputFilePath;
        $_POST['inputFileName'] = $this->inputFileName;
        $_POST['outputFileName'] = $this->outputFileName;
        $_POST['fileNameFirmato'] = $this->fileNameFirmato;
        if ($this->returnMultiFile) {
            $_POST['returnAllegati'] = $this->allegati;
        }
        $this->allegati = array();
        $this->close();
        $returnModelObj->setEvent($this->returnEvent);
        $returnModelObj->parseEvent();
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . "_returnMultiFile");
        App::$utente->removeKey($this->nameForm . "_multiFile");
        App::$utente->removeKey($this->nameForm . "_confermato");
        App::$utente->removeKey($this->nameForm . "_allegati");
        App::$utente->removeKey($this->nameForm . '_inputFilePath');
        App::$utente->removeKey($this->nameForm . '_outputFilePath');
        App::$utente->removeKey($this->nameForm . '_inputFileName');
        App::$utente->removeKey($this->nameForm . '_outputFileName');
        App::$utente->removeKey($this->nameForm . '_fileNameFirmato');
        App::$utente->removeKey($this->nameForm . '_signMethod');
        App::$utente->removeKey($this->nameForm . '_forceSignMethod');
        App::$utente->removeKey($this->nameForm . '_topMsg');
        App::$utente->removeKey($this->nameForm . '_chiediCredenziali');
        Out::closeDialog($this->nameForm);
    }

    /**
     * Aggiunge le proprietà grafiche alla lista degli allegati
     * 
     * @return array
     */
    function elaboraArrayAllegati() {
        foreach ($this->allegati as $key => $allegato) {
            $fileOrig = $allegato['FILEORIG'];
            $icon = utiIcons::getExtensionIconClass($allegato['INPUTFILEPATH'], 32);
            if ($allegato['SIGNRESULT'] == "OK") {
                if (file_exists($allegato['OUTPUTFILEPATH'])) {
                    $icon = utiIcons::getExtensionIconClass($allegato['INPUTFILEPATH'], 32);
                }
            }
            $fileSize = $this->formatFileSize(filesize($allegato['INPUTFILEPATH']));
            $this->allegati[$key]['FILEORIG'] = $fileOrig;
            $this->allegati[$key]["FileIcon"] = "<span style = \"margin:2px;\" class=\"$icon\"></span>";
            $this->allegati[$key]["FileSize"] = $fileSize;
            switch ($this->allegati[$key]["SIGNRESULT"]) {
                case "":
                    $saveIcon = "";
                    $saveTooltip = "";
                    $resultIcon = "ita-icon-shield-blue-24x24";
                    $resultTooltip = "Ancora da firmare.";
                    break;
                case "OK":
                    $saveIcon = "ita-icon-save-24x24";
                    $saveTooltip = "Scarica e salva il documento firmato.";
                    $resultIcon = "ita-icon-shield-green-24x24";
                    $resultTooltip = "Documento firmato con successo.";
                    if (!file_exists($allegato['OUTPUTFILEPATH'])) {
                        $resultIcon = "ita-icon-yellow-alert-24x24";
                        $resultTooltip = "Documento firmato non accessibile. Anomalia.";
                        $this->allegati[$key]["SIGNRESULT"] = "KO";
                    }
                    break;
                default:
                    $saveIcon = "";
                    $saveTooltip = "";
                    $resultIcon = "ita-icon-yellow-alert-24x24";
                    $resultTooltip = "Errore nella firma." . $this->allegati[$key]["SIGNMESSAGE"];
                    break;
            }
            $this->allegati[$key]["ResultIcon"] = "<div class=\"ita-html\"><span style = \"margin:2px;\" title=\"$resultTooltip\" class=\"ita-tooltip ita-icon $resultIcon\"></span></div>";
            if ($saveIcon) {
                $this->allegati[$key]["SaveIcon"] = "<div class=\"ita-html\"><span style = \"margin:2px;\" title=\"$saveTooltip\" class=\"ita-tooltip ita-icon $saveIcon\"></span></div>";
            } else {
                $this->allegati[$key]["SaveIcon"] = "";
            }
        }
        return $this->allegati;
    }

    /**
     * Invia dati griglia al browser 
     * 
     * @param type $_griglia
     * @param type $_appoggio
     * @param type $_tipo
     * @param type $pageRows
     * @param type $selectAll
     * @return type
     */
    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = '1000', $selectAll = false) {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        if ($selectAll === true) {
            TableView::setSelectAll($_griglia);
        }
        return;
    }

    // TODO: usare funzione centralizzata
    /**
     * 
     * @param integer $a_bytes numero di byte da elaborare
     * @return integer
     */
    function formatFileSize($a_bytes) {
        if ($a_bytes < 1024) {
            return $a_bytes . ' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) . ' KiB';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MiB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GiB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) . ' TiB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) . ' PiB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) . ' EiB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) . ' ZiB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) . ' YiB';
        }
    }

    /**
     * Caria e mostra le coordinate paper token ( riferimento funzione speciale per asmez)
     * 
     * @param type $utente
     * @param type $password
     * @return boolean
     */
    private function displayCoords($utente, $password = '') {
        $Signer = new rsnSigner();
        $Signer->setOtpPwd($_POST[$this->nameForm . '_otp']);
        $Signer->setTypeOtpAuth($_POST[$this->nameForm . '_otpauth']);
        $Signer->setUser($utente);
        $Signer->setPassword($password);
        $retWs = $Signer->getPaperTokenCoords();
        if ($retWs) {
            $extendedCoords = implode(" - ", str_split(trim($retWs), 2));
            Out::valore($this->nameForm . '_paperTokenCoords', $extendedCoords);
            return true;
        } else {
            Out::valore($this->nameForm . '_paperTokenCoords', '');
            return false;
        }
    }

    /**
     * Combo Dominio firma
     */
    private function creaCombo() {
        foreach (rsnSigner::$TYPES_OPT_AUTH as $typeOptAuth) {
            Out::select($this->nameForm . '_otpauth', 1, $typeOptAuth, 0, $typeOptAuth);
        }
    }

    /**
     * Combo Metodo di firma
     * 
     * @param type $listSignMethods
     */
    private function creaComboSignMethod($listSignMethods) {
        Out::html($this->nameForm . '_signMethod', '');
        $selected = 1;
        foreach ($listSignMethods as $value) {
            Out::select($this->nameForm . '_signMethod', 1, key($value), $selected, $value[key($value)]);
            $selected = 0;
        }
    }

    /**
     * Calcolo metodo di firma consigliato per le caratteristiche dell'elenco di files da firmare caricato
     * 
     * @param type $allegati
     * @return boolean
     */
    private function calculateSignMetods($allegati) {
        $listSignMethods = array();
        if ($this->forceSignMethod) {
            $listSignMethods[] = array($this->forceSignMethod => rsnSigner::$TYPES_SIGN[$this->forceSignMethod]);
            return $listSignMethods;
        }
        $extList = array();
        foreach ($allegati as $allegato) {
            $ext = strtolower(pathinfo($allegato['FILEORIG'], PATHINFO_EXTENSION));
            $extList[$ext] ++;
        }
        if (count($extList) > 1) {
            $listSignMethods[] = array(rsnSigner::TYPE_SIGN_CADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_CADES]);
            return $listSignMethods;
        } elseif (count($extList) == 1) {
            reset($extList);
            switch (key($extList)) {
                case 'p7m':
                    $listSignMethods[] = array(rsnSigner::TYPE_SIGN_CADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_CADES]);
                    break;
                case 'pdf':
                    $listSignMethods[] = array(rsnSigner::TYPE_SIGN_CADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_CADES]);
                    //$listSignMethods[] = array(rsnSigner::TYPE_SIGN_PADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_PADES]);
                    break;
                case 'xml':
                    $listSignMethods[] = array(rsnSigner::TYPE_SIGN_XADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_XADES]);
                    $listSignMethods[] = array(rsnSigner::TYPE_SIGN_CADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_CADES]);
                    break;
                default:
                    $listSignMethods[] = array(rsnSigner::TYPE_SIGN_CADES => rsnSigner::$TYPES_SIGN[rsnSigner::TYPE_SIGN_CADES]);
                    break;
            }
            return $listSignMethods;
        }
        return false;
    }

    /**
     * Firma in modo singolo ( senza prenotazione sessione)
     * 
     */
    private function signSingle($Signer) {
        $this->setSignedFilesExtension();

        /**
         * Carico i Parametri per la firma singola
         * 
         */
        $Signer->setInputFilePath($this->allegati[0]['INPUTFILEPATH']);
        $Signer->setOutputFilePath($this->allegati[0]['OUTPUTFILEPATH']);

        /**
         * Lancio la corretta procedura di firma
         * 
         */
        switch ($this->signMethod) {
            case rsnSigner::TYPE_SIGN_CADES:
                if (strtolower((pathinfo($Signer->getInputFilePath(), PATHINFO_EXTENSION))) == 'p7m') {
                    $ret = $Signer->addPkcs7sign();
                } else {
                    $ret = $Signer->signPkcs7();
                }
                break;
            case rsnSigner::TYPE_SIGN_PADES:
                break;
            case rsnSigner::TYPE_SIGN_XADES:
                $ret = $Signer->signXades();
                break;
        }

        /**
         * Parse del risultato
         */
        if ($ret == true) {
            $this->outputFileName = $this->allegati[0]['OUTPUTFILENAME'];
            $this->outputFilePath = $this->allegati[0]['OUTPUTFILEPATH'];
            if (!$this->fileNameFirmato) {
                $this->fileNameFirmato = $this->allegati[0]['FILENAMEFIRMATO'];
            }
            $this->returnToParent(true);
        } else {
            $this->returnToParent(false);
            Out::msgInfo("Firma remota... Fallita!", $Signer->getReturnCode() . "-" . $Signer->getMessage());
        }
    }

    /**
     * FIrma in modo multiplo
     * 
     */
    private function signMulti($Signer) {

        $this->setSignedFilesExtension();

        /**
         * Carico i parametri del multiSign
         * 
         */
        $multiSignFilePaths = array();
        foreach ($this->allegati as $key => $allegato) {
            if ($allegato['SIGNRESULT'] != "OK") {
                $multiSignFilePaths[$key] = array(
                    'inputFilePath' => $allegato['INPUTFILEPATH'],
                    'outputFilePath' => $allegato['OUTPUTFILEPATH'],
                    'fileNameFirmato' => $allegato['FILENAMEFIRMATO']
                );
            }
        }
        $Signer->setMultiSignFilePaths($multiSignFilePaths);


        /**
         * Lancio la corretta procedura di firma
         * 
         */
        switch ($this->signMethod) {
            case rsnSigner::TYPE_SIGN_CADES:
                $ret = $Signer->multiSignPkcs7();
                BREAK;
            case rsnSigner::TYPE_SIGN_PADES:
                break;
            case rsnSigner::TYPE_SIGN_XADES:
                $ret = $Signer->multiSignXades();
                break;
        }

        /**
         * Parse del risultato
         */
        if ($ret == true) {
            foreach ($Signer->getMultiSignFilePaths() as $key => $value) {
                $this->allegati[$key]['SIGNRESULT'] = $value['signResult'];
                $this->allegati[$key]['SIGNMESSAGE'] = $value['signMessage'];
            }
            if (!$this->returnMultiFile) {
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                Out::hide($this->nameForm . '_signMethod_field');
                Out::hide($this->nameForm . '_divCredenziali');
                Out::hide($this->gridAllegati . '_addGridRow');
                Out::hide($this->gridAllegati . '_delGridRow');
                $topMsg = "Documenti Firmati.... scarica i files con l'apposita icona";
                $html = "<div>";
                $html .= "<div style=\"display:inline-block;\"><img width=40px style=\"margin:2px;\" src=\"" . $Signer->getSignerLogo() . "\"></img></div>";
                $html .= "<div style=\"display:inline-block;vertical-align:middle;padding-left:5px;\"><div style=\"font-size:1.3em;color:darkgreen;\">$topMsg</div><br><br></div>";
                $html .= "</div>";
                Out::html($this->nameForm . "_topMsg", $html);
                Out::msgInfo("Firma Remota", "Firme avvenute con successo puoi scaricare i files firmati");
            } else {
                $this->returnToParent(true);
            }
        } else {
            Out::msgInfo("Firma remota... Fallita!", $Signer->getReturnCode() . "-" . $Signer->getMessage());
        }
    }

    /**
     * Normalizzo le estsensioni dei nomi files di ritorno in funzione del tipo di firma da applicare
     * 
     */
    private function setSignedFilesExtension() {
        foreach ($this->allegati as $key => $allegato) {
            $this->allegati[$key]['FILEFIRMATO'] = $allegato['FILEORIG'];
            $this->allegati[$key]['OUTPUTFILENAME'] = $allegato['FILEORIG'];
            $this->allegati[$key]['OUTPUTFILEPATH'] = $allegato['INPUTFILEPATH'];
            $this->allegati[$key]['FILENAMEFIRMATO'] = $allegato['OUTPUTFILENAME'];
            switch ($this->signMethod) {
                case rsnSigner::TYPE_SIGN_CADES:
                    if (strtolower((pathinfo($allegato['INPUTFILEPATH'], PATHINFO_EXTENSION))) !== 'p7m') {
                        $this->allegati[$key]['FILEFIRMATO'] = $allegato['FILEORIG'] . ".p7m";
                        $this->allegati[$key]['OUTPUTFILENAME'] = $allegato['FILEORIG'] . ".p7m";
                        $this->allegati[$key]['OUTPUTFILEPATH'] = $allegato['INPUTFILEPATH'] . ".p7m";
                        $this->allegati[$key]['FILENAMEFIRMATO'] = $this->allegati[$key]['OUTPUTFILENAME'];
                    }
                    break;
                case rsnSigner::TYPE_SIGN_PADES:
                    break;
                case rsnSigner::TYPE_SIGN_XADES:
                    break;
            }
        }
    }

}
