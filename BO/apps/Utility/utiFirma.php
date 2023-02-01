<?php

/**
 *
 * Utility firma (Cades/Xades/Manuale)
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Utility
 * @author     Nicola Corvo <n.corvo@palinformatica.it>
 * @author     Massimo Biagioli (rev) <m.biagioli@palinformatica.it> 
 * @copyright  Pal Informatica srl
 * @license
 * @version    22.10.2018
 * @link
 * @see
 * @since
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/RemoteSign/rsnSigner.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiFirmaValidators/utiFirmaIdFlussoValidator.class.php';

function utiFirma() {
    $utiFirma = new utiFirma();
    $utiFirma->parseEvent();
    return;
}

class utiFirma extends itaModel {
    public $nameForm = "utiFirma";
    public $gridAllegati = "utiFirma_gridAllegati";
    public $topMsg;
    public $inputFilePath;
    public $outputFilePath;
    public $outputFileName;
    public $inputFileName;
    public $multiFile = false;
    public $allegati = array();
    public $returnMultiFile = false;
    public $confermato;
    public $PathFileFirmaLocale;
    public $accLib;
    public $aperturaPreferenziale;
    public $fileLimit = 0;
    public $checkForFileName = false;
    public $objValidator;
    public $forceSignMethod;    
    private $listaFileFirmati;

    function __construct() {
        parent::__construct();
        $this->returnMultiFile = App::$utente->getKey($this->nameForm . "_returnMultiFile");
        $this->multiFile = App::$utente->getKey($this->nameForm . "_multiFile");
        $this->confermato = App::$utente->getKey($this->nameForm . "_confermato");
        $this->allegati = App::$utente->getKey($this->nameForm . "_allegati");
        $this->inputFilePath = App::$utente->getKey($this->nameForm . '_inputFilePath');
        $this->outputFilePath = App::$utente->getKey($this->nameForm . '_outputFilePath');
        $this->outputFileName = App::$utente->getKey($this->nameForm . '_outputFileName');
        $this->inputFileName = App::$utente->getKey($this->nameForm . '_inputFileName');
        $this->topMsg = App::$utente->getKey($this->nameForm . '_topMsg');
        $this->PathFileFirmaLocale = App::$utente->getKey($this->nameForm . '_PathFileFirmaLocale');
        $this->aperturaPreferenziale = App::$utente->getKey($this->nameForm . '_aperturaPreferenziale');
        $this->fileLimit = App::$utente->getKey($this->nameForm . '_fileLimit');
        $this->checkForFileName = App::$utente->getKey($this->nameForm . '_checkForFileName');
        $this->objValidator = App::$utente->getKey($this->nameForm . '_objValidator');
        $this->listaFileFirmati = App::$utente->getKey($this->nameForm . '_listaFileFirmati');
        $this->forceSignMethod = App::$utente->getKey($this->nameForm . '_forceSignMethod');
        $this->options = App::$utente->getKey($this->nameForm . '_options');        
        $this->accLib = new accLib();                
    }

    function __destruct() {        
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_returnMultiFile", $this->returnMultiFile);
            App::$utente->setKey($this->nameForm . "_multiFile", $this->multiFile);
            App::$utente->setKey($this->nameForm . "_confermato", $this->confermato);
            App::$utente->setKey($this->nameForm . "_allegati", $this->allegati);
            App::$utente->setKey($this->nameForm . '_inputFilePath', $this->inputFilePath);
            App::$utente->setKey($this->nameForm . '_outputFilePath', $this->outputFilePath);
            App::$utente->setKey($this->nameForm . '_outputFileName', $this->outputFileName);
            App::$utente->setKey($this->nameForm . '_inputFileName', $this->inputFileName);
            App::$utente->setKey($this->nameForm . '_topMsg', $this->topMsg);
            App::$utente->setKey($this->nameForm . '_PathFileFirmaLocale', $this->PathFileFirmaLocale);
            App::$utente->setKey($this->nameForm . '_aperturaPreferenziale', $this->aperturaPreferenziale);
            App::$utente->setKey($this->nameForm . '_fileLimit', $this->fileLimit);
            App::$utente->setKey($this->nameForm . '_checkForFileName', $this->checkForFileName);
            App::$utente->setKey($this->nameForm . '_objValidator', $this->objValidator);
            App::$utente->setKey($this->nameForm . '_listaFileFirmati', $this->listaFileFirmati);
            App::$utente->setKey($this->nameForm . '_forceSignMethod', $this->forceSignMethod);
            App::$utente->setKey($this->nameForm . '_options', $this->options);
        }
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                                                                
                switch ($this->aperturaPreferenziale) {
                    case '1':
                        $this->OpenFormFirmaRemota();
                        break;
                    case '2':
                        Out::hide($this->nameForm . '_FirmaRemota');
                        break;
                    case '3':
                    default:
                        break;
                }
                
                //Preparo la cartella temporanea di lavoro
                $PathFile = itaLib::createAppsTempPath('FIRMALOCALE');
                if (!$PathFile) {
                    Out::msgStop("Attenzione", "Errore nella creazione della cartella temporanea.");
                    break;
                }
                $this->PathFileFirmaLocale = $PathFile;
                
                if (!$this->allegati) {
                    $this->allegati = array(
                        array(
                            'FILEORIG' => $this->inputFileName,
                            'INPUTFILEPATH' => $this->inputFilePath,
                            'OUTPUTFILEPATH' => $this->outputFilePath,
                            'OUTPUTFILENAME' => $this->outputFileName,
                            'SIGNRESULT' => "",
                            'SIGNMESSAGE' => ""
                        )
                    );
                }

                $this->confermato = false;
                
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                if (!$this->multiFile) {
                    out::hide($this->gridAllegati . "_addGridRow");
                    out::hide($this->gridAllegati . "_delGridRow");
                }
                                
                $rsnSigner = new rsnSigner();
                Out::html($this->nameForm . '_FirmaRemota', "<img width=24px style=\"display:inline-block; margin:2px;\" src=\"" . $rsnSigner->getSignerLogo() . "\"></img><span style=\"display:inline-block;\">Vai alla <br>Firma Remota</span>");
                $html = "<div>";
                $html .= "<div style=\"display:inline-block;\"><div style=\"vertical-align:middle;display:inline-block;\" class=\"ita-icon ita-icon-sigillo-64x64\"></div>";
                $MessaggioLocale = "<div style=\"font-size:1.3em;color:red;\">Scarica i File da firmare e ricaricali Firmati:</div><br>";
                $html .= "<div style=\"display:inline-block;vertical-align:middle;padding-left:5px;\">$MessaggioLocale</div>";
                $html .= "</div>";
                Out::html($this->nameForm . "_topMsg", $html);
                Out::setFocus('', $this->nameForm . '_utente');                                       
                break;
            
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CtxAllegaFile':
                        $rowid = $this->formData[$this->gridAllegati]['gridParam']['selarrrow'];
                        $Allegato = $this->allegati[$rowid];
                        $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                        $this->verificaFileFirmato($uplFile, $rowid);
                        break;
                    
                    case $this->nameForm . '_CtxScaricaFile':
                        $allegato = $this->allegati[$_POST[$this->gridAllegati]['gridParam']['selarrrow']];
                        if (!$allegato) {
                            break;
                        }
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_OPEN_RECORD, 'Estremi' => "{$allegato['FILEORIG']}: Scaricato file"));

                        Out::openDocument(
                                utiDownload::getUrl(
                                        $allegato['FILEORIG'], $allegato['INPUTFILEPATH'], true
                                )
                        );
                        break;

                    case $this->nameForm . '_CtxCancFileFirmato':
                        Out::msgQuestion("Cancella Allegato.", "Vuoi cancellare il file firmato?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancFileFirmato',
                                'model' => $this->nameForm, 'shortCut' => "f8"),
                            'Cancella' => array('id' => $this->nameForm . '_ConfermaCancFileFirmato',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancFileFirmato':
                        $rowid = $this->formData[$this->gridAllegati]['gridParam']['selarrrow'];
                        if (!@unlink($this->allegati[$rowid]['FILEFIRMATO'])) {
                            Out::msgStop("Attenzione", "Errore in cancellazione file firmato caricato.");
                            break;
                        }
                        $this->allegati[$rowid]['FILEFIRMATO'] = "";
                        $this->allegati[$rowid]['SIGNRESULT'] = "";
                        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_DEL_RECORD, 'Estremi' => "{$this->allegati[$rowid]['FILEORIG']}: Cancellato file firmato"));
                        Out::msgInfo("Cancellazione", "Cancellazione file firmato avvenuta correttamente.");
                        break;

                    case $this->nameForm . '_FirmaRemota':
                        $this->OpenFormFirmaRemota();
                        break;
                    
                    case $this->nameForm . '_Conferma':
                        // Controllare che siano tutti firmati
                        $TuttiFirmati = true;
                        foreach ($this->allegati as $allegato) {
                            if (!$allegato['FILEFIRMATO'] || $allegato['SIGNRESULT'] != 'OK') {
                                $TuttiFirmati = false;
                            }
                        }
                        if ($TuttiFirmati == false) {
                            Out::msgStop("Attenzione", "Tutti i file devono essere firmati e validi per poter procedere.");
                            break;
                        }
                        //Rename per tutti gli Allegati
                        $fl_err = false;
                        foreach ($this->allegati as $key => $Allegato) {
                            $uplFile = $Allegato['FILEFIRMATO'];
                            $destFile = $Allegato['OUTPUTFILEPATH'];
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Salvataggio File", "Errore in salvataggio del file.");
                                $fl_err = true;
                                break;
                            }
                        }
                        if ($fl_err) {
                            break;
                        }
                        $this->returnToParent(true);
                        break;
                    
                    case 'close-portlet':
                        $this->returnToParent("cancel");
                        break;
                    
                    case $this->nameForm . '_ScaricaTutti':
                        $this->scaricaTutti();
                        break;
                    
                    case $this->nameForm . '_CaricaTutti':
                        $this->caricaTutti();
                        break;
                    
                    case $this->nameForm . '_FIRMATO_upld':
                        $this->caricaFileFirmato();
                        break;
                }
                break;
            
            case 'delGridRow':
                $allegato = $this->allegati[$_POST['rowid']];
                if (!$allegato) {
                    break;
                }
                if ($allegato['SIGNRESULT'] == "OK") {
                    Out::msgInfo("Attenzione", "If file risulta firmato, scaricare.");
                    break;
                }

                if (file_exists($allegato['INPUTFILEPATH'])) {
                    if (!unlink(@$allegato['INPUTFILEPATH'])) {
                        Out::msgStop("Attenzione", "Errore in cancellazione file da firmare");
                        break;
                    }
                }
                unset($this->allegati[$_POST['rowid']]);
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_DEL_RECORD, 'Estremi' => "{$allegato['FILEORIG']}: Cancellato file firmato"));
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;
                
            case 'addGridRow':
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
                //$nomep7m = $allegato['FILEORIG'] . ".p7m";
                $nomep7m = $allegato['FILENAMEFIRMATO'];
                $path = $allegato['FILEFIRMATO']; //File caricato.
                switch ($_POST['colName']) {
                    case 'PREVIEW':
                        $this->setFunzioneAllegati($this->nameForm, $allegato);
                        break;
                    case "ResultIcon":
                        if ($allegato["SIGNRESULT"] == "OK") {
                            $model = "utiP7m";
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $_POST['event'] = "openform";
                            $_POST['file'] = $path;
                            $_POST['fileOriginale'] = $nomep7m;
                            $model();
                        }
                        break;
                }
                break;
                
            case 'returnUploadFirmato':
                $rowid = $this->formData[$this->gridAllegati]['gridParam']['selarrrow'];
                $Allegato = $this->allegati[$rowid];
                //
                //Prevedere di usare lo stesso nome in output?
                //
                $uplFile = $_POST['uploadedFile'];
                $NomeFile = pathinfo($_POST['uploadedFile'], PATHINFO_BASENAME);
                $destFile = $this->PathFileFirmaLocale . '/' . $NomeFile;
                $ext = strtolower(pathinfo($_POST['uploadedFile'], PATHINFO_EXTENSION));
                if (strtolower($ext) != "p7m") {
                    Out::msgStop("Attenzione", "Occorre caricare un file firmato digitalmente p7m.");
                    break;
                }
                // Controllo sei i 2 file coincidono
                $Ret = $this->VerificaFirma($Allegato['INPUTFILEPATH'], $uplFile);
                if ($Ret['STATO'] !== true) {
                    Out::msgStop("Attenzione", $Ret['MESSAGGIO']);
                    break;
                }
                // Se controlli vanno a buon fine, sposto il p7m e setto i risultati.
                if (!@rename($uplFile, $destFile)) {
                    Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                    break;
                }
                $this->allegati[$rowid]['FILEFIRMATO'] = $destFile;
                $this->allegati[$rowid]['SIGNRESULT'] = "OK";
                Out::hide($this->nameForm . '_FirmaRemota');
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;

            case 'returnUpload':
                if (strtolower(pathinfo($_POST['file'], PATHINFO_EXTENSION)) == "p7m") {
                    Out::msgInfo("Attenzione!!!", "Il file risulta già essere firmato digitalmente");
                    break;
                }
                $this->allegati[] = array(
                    'FILEORIG' => $_POST['file'],
                    'INPUTFILEPATH' => $_POST['uploadedFile'],
                    'OUTPUTFILEPATH' => $_POST['uploadedFile'] . ".p7m",
                    'SIGNRESULT' => ""
                );
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;
        }
    }

    public function returnToParent($result) {
        /* @var $returnModelObj itaModel */
        if (!isSet($this->returnModelOrig)) {
            $returnModelObj = itaModel::getInstance($this->returnModel);
        } else {
            $returnModelObj = itaModel::getInstance($this->returnModelOrig, $this->returnModel);
        }

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
        if ($this->returnMultiFile) {
            $_POST['returnAllegati'] = $this->allegati;
        }
        $_POST['fileNameFirmato'] = $this->allegati[0]['FILENAMEFIRMATO'];

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
        App::$utente->removeKey($this->nameForm . '_outputFileName');
        App::$utente->removeKey($this->nameForm . '_inputFileName');
        App::$utente->removeKey($this->nameForm . '_topMsg');
        App::$utente->removeKey($this->nameForm . '_PathFileFirmaLocale');
        App::$utente->removeKey($this->nameForm . '_aperturaPreferenziale');
        App::$utente->removeKey($this->nameForm . '_checkForFileName');
        App::$utente->removeKey($this->nameForm . '_objValidator');
        App::$utente->removeKey($this->nameForm . '_fileLimit');
        App::$utente->removeKey($this->nameForm . '_listaFileFirmati');
        App::$utente->removeKey($this->nameForm . '_forceSignMethod');
        App::$utente->removeKey($this->nameForm . '_options');
        Out::closeDialog($this->nameForm);
    }

    private function elaboraArrayAllegati() {
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

            $this->allegati[$key]["FileIcon"] = "<span style = \"margin:2px; display:inline-block;\" class=\"$icon\"></span>";
            $this->allegati[$key]["FileSize"] = $fileSize;
            switch ($this->allegati[$key]["SIGNRESULT"]) {
                case "":
                    $saveTooltip = "";
                    $resultIcon = "ita-icon-shield-blue-24x24";
                    $resultTooltip = "Ancora da firmare.";
                    break;
                case "OK":
                    $saveTooltip = "Scarica e salva il documento firmato.";
                    $resultIcon = "ita-icon-shield-green-24x24";
                    $resultTooltip = "Documento firmato allegato con successo.";
                    if (!file_exists($allegato['FILEFIRMATO'])) {
                        $resultIcon = "ita-icon-yellow-alert-24x24";
                        $resultTooltip = "Documento firmato non accessibile. Anomalia.";
                        $this->allegati[$key]["SIGNRESULT"] = "KO";
                    } else {
                        $this->allegati[$key]["FileIcon"] .= '<span title ="File Firmato" style="display:inline-block; position:relative; margin-left:-15px; top:-18px; " class="ita-tooltip ita-icon ita-icon-sigillo-16x16"></span>';
                    }
                    break;
                default:
                    $saveTooltip = "";
                    $resultIcon = "ita-icon-yellow-alert-24x24";
                    $resultTooltip = "Errore nella firma." . $this->allegati[$key]["SIGNMESSAGE"];
                    break;
            }
            $this->allegati[$key]["ResultIcon"] = "<div class=\"ita-html\"><span style = \"margin:2px;\" title=\"$resultTooltip\" class=\"ita-tooltip ita-icon $resultIcon\"></span></div>";
            $title = "Menu funzioni";
            $Preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
            $this->allegati[$key]["PREVIEW"] = $Preview;
        }
        return $this->allegati;
    }

    private function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = '1000', $selectAll = false) {
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

    public function OpenFormFirmaRemota() {
        itaLib::openForm('rsnAuth', true);
        /* @var $rsnAuth rsnAuth */
        $rsnAuth = itaModel::getInstance('rsnAuth', 'rsnAuth');
        $rsnAuth->setEvent('openform');
        $rsnAuth->setReturnEvent($this->returnEvent);
        $rsnAuth->setReturnModel($this->returnModelOrig ?: $this->returnModel);
        $rsnAuth->setReturnId('');
        $rsnAuth->setInputFilePath($this->getInputFilePath());
        $rsnAuth->setOutputFilePath($this->getOutputFilePath());
        $rsnAuth->setReturnMultiFile($this->getReturnMultiFile());
        $rsnAuth->setinputFileName($this->getInputFileName());
        $rsnAuth->setMultiFile($this->getMultiFile());
        $rsnAuth->setForceSignMethod($this->getForceSignMethod());
        $rsnAuth->setAllegati($this->getAllegati()); //Forse Inutile
        $rsnAuth->setTopMsg($this->getTopMsg());
        $rsnAuth->parseEvent();
        $this->close(true);
        Out::closeDialog($this->nameForm);
    }

    public function setFunzioneAllegati($nameForm, $allegato) {
        $ext = strtolower(pathinfo($allegato['INPUTFILEPATH'], PATHINFO_EXTENSION));
                
        $arrayBottoni = array(
            'Scarica File da Firmare' => array('id' => $nameForm . '_CtxScaricaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $nameForm),
            //'Allega File Firmato' => array('id' => $nameForm . '_returnUploadFirmato', "style" => "width:250px;height:40px;", 'metaData' => "upload:true,iconLeft:'ita-icon-sigillo-32x32'", 'model' => $nameForm)
            'Allega File Firmato' => array('id' => $nameForm . '_CtxAllegaFile', "style" => "width:250px;height:40px;", 'metaData' => "upload:true,iconLeft:'ita-icon-sigillo-32x32'", 'model' => $nameForm)
        );
                        
        if ($allegato['FILEFIRMATO']) {
            $arrayBottoni = array(
                'Cancella File Firmato' => array('id' => $nameForm . '_CtxCancFileFirmato', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $nameForm)
            );
        }
        if ($arrayBottoni) {
            Out::msgQuestion("Gestione Allegato", "", $arrayBottoni, 'auto', 'auto', 'true', false, true, true);
        } else {
            Out::msgInfo("Funzione Allegato.", "Non ci sono funzioni disponibili.");
        }
        Out::activateUploader($nameForm . '_CtxAllegaFile_uploader');
        return;
    }

    public function VisualizzaFirme($file, $fileORiginale) {
        $model = "utiP7m";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $_POST['event'] = "openform";
        $_POST['file'] = $file;
        $_POST['fileOriginale'] = $fileORiginale;
        $model();
    }

    public function VerificaFirma($fileDRR, $FileP7m) {
        if (strtolower(pathinfo($FileP7m, PATHINFO_EXTENSION)) == 'xml') {
            return $this->VerificaXades($fileDRR, $FileP7m);
        } else {
            return $this->VerificaCades($fileDRR, $FileP7m);
        }
    }
    
    public function VerificaXades($fileDRR, $FileP7m) {
        $ArrRet = array();
        $p7m = itaP7m::getP7mInstance($FileP7m);
        if ($p7m == false) {
            $ArrRet['STATO'] = false;
            $ArrRet['MESSAGGIO'] = "Verifica non riuscita";
            return $ArrRet;
        }
        
        // Controlla se c'è il firmatario e se il certificato non è scaduto.
        // Firmatario            
        $infoSummary = $p7m->getInfoSummary();
        $signedBy = $infoSummary[0]['signer'];

        // Check data certificato
        $notAfter = $infoSummary[0]['NotAfter'];

        if (strlen($signedBy) > 0 && strlen($notAfter) > 0) {                                            
            $signDate = strtotime($notAfter);
            $dateNow = strtotime("now");

            if ($signDate && $signDate > $dateNow) {

                // Utilizza il validator se valorizzato
                if ($this->getObjValidator() != null) {
                    $objValidator = unserialize($this->getObjValidator());
                    try {
                        $ArrRet = $objValidator->validate($fileDRR, $FileP7m);
                    } catch (Exception $e) {
                        $ArrRet['STATO'] = false;
                        $ArrRet['MESSAGGIO'] = "Errore nella validazione dei due file!";
                    }

                    $p7m->cleanData();
                    return $ArrRet;
                } else {
                    $ArrRet['STATO'] = true;
                    return $ArrRet;
                }
            } else {
                $messaggio = $p7m->getMessageErrorFileAsString();
                $p7m->cleanData();
                $ArrRet['STATO'] = false;
                $ArrRet['MESSAGGIO'] = $messaggio;
                return $ArrRet;
            }
        } else {
            $messaggio = $p7m->getMessageErrorFileAsString();
            if(strlen($messaggio) == 0) {
                $messaggio = 'Il file non è firmato';
            }
            $p7m->cleanData();
            $ArrRet['STATO'] = false;
            $ArrRet['MESSAGGIO'] = $messaggio;
            return $ArrRet;
        }        
    }
    
    private function VerificaCades($fileDRR, $FileP7m) {
        $ArrRet = array();
        $p7m = itaP7m::getP7mInstance($FileP7m);
        if ($p7m == false) {
            $ArrRet['STATO'] = false;
            $ArrRet['MESSAGGIO'] = "Verifica non riuscita";
            return $ArrRet;
        }
        if (!$p7m->isFileVerifyPassed()) {
            $messaggio = $p7m->getMessageErrorFileAsString();
            $p7m->cleanData();
            $ArrRet['STATO'] = false;
            $ArrRet['MESSAGGIO'] = $messaggio;
            return $ArrRet;
        }
        if (file_exists($fileDRR)) {
            if (strtolower(pathinfo($fileDRR, PATHINFO_EXTENSION)) == 'p7m') {
                $p7m_drr = itaP7m::getP7mInstance($fileDRR);
                if ($p7m_drr == false) {
                    $ArrRet['STATO'] = false;
                    $ArrRet['MESSAGGIO'] = "Verifica sorgente non riuscita";
                    return $ArrRet;
                }
                $sha1_drr = $p7m_drr->getContentSHA();
            } else {
                $sha1_drr = sha1_file($fileDRR);
            }
            $sha1_p7m = $p7m->getContentSHA();
            if ($sha1_drr !== $sha1_p7m) {
                $p7m->cleanData();
                $ArrRet['STATO'] = false;
                $ArrRet['MESSAGGIO'] = "File firmato incongruente con il file scaricato. Pertanto il file non verrà allegato.";
                return $ArrRet;
            }
        } else {
            $p7m->cleanData();
            $ArrRet['STATO'] = false;
            $ArrRet['MESSAGGIO'] = "Controllo del contenuto del file firmato non applicabile, scaricare il rapporto completo da firmare.";
            return $ArrRet;
        }
        $ArrRet['STATO'] = true;
        $ArrRet['MESSAGGIO'] = '';
        return $ArrRet;
    }
    
    private function scaricaTutti() {
        foreach ($this->getAllegati() as $allegato) {
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_OPEN_RECORD, 'Estremi' => "{$allegato['FILEORIG']}: Scaricato file"));

            Out::openDocument(
                    utiDownload::getUrl(
                            $allegato['FILEORIG'], $allegato['INPUTFILEPATH'], true
                    )
            );
        }        
    }
    
    private function caricaTutti() {        
        $this->listaFileFirmati = '';
        Out::valore($this->nameForm . '_FIRMATO', $this->listaFileFirmati);
        
        Out::msgInput('Carica file firmati',
            array(
                array(
                    'label' => array(
                        'value'=>'File firmati:',
                        'style'=>'width: 120px; text-align: left;'
                    ),
                    'id' => $this->nameForm . '_FIRMATO',
                    'name' => $this->nameForm . '_FIRMATO',
                    'type' => 'type',
                    'class' => 'ita-edit-upload',
                    'value' => $this->listaFileFirmati,
                    'size' => '30'
                )
            ),
            array(
                'OK' => array(
                    'id' => $this->nameForm . '_okFirmati',
                    'model' => $this->nameForm
                )
            ),
            $this->nameForm
        );
        Out::activateUploader($this->nameForm . '_FIRMATO_upld_uploader');
        Out::codice("$('#" . $this->nameForm . "_FIRMATO" . "').prop('disabled', true);");
    }
    
    private function caricaFileFirmato(){
        $origFile = $_POST['file'];
        $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
        
        if(strlen($this->listaFileFirmati) > 0) {
            $this->listaFileFirmati = $this->listaFileFirmati . ' , ';
        }
        $this->listaFileFirmati = $this->listaFileFirmati . $origFile;
        
        Out::valore($this->nameForm . '_FIRMATO', $this->listaFileFirmati);
        
        $this->verificaFileFirmato($uplFile);
    }
    
    private function verificaFileFirmato($uplFile, $rowid = '') {
        $fileSizeIsOk = true;
        
        if($this->getFileLimit() > 0) {
            $fileSizeIsOk = $this->verifyFileSize($uplFile);
        }
        
        if($fileSizeIsOk) {
            // Verifica file firmato
            $NomeFile = pathinfo($uplFile, PATHINFO_BASENAME);
            $FileNameFirmato = $_POST['file'];

            $destFile = $this->PathFileFirmaLocale . '/' . $NomeFile;

            if(!$rowid) {
                $rowid = $this->getRowIdAllegatoFromNomeFile($FileNameFirmato);
            }

            if($rowid !== null) {

                $inputfilepath = $this->allegati[$rowid]['INPUTFILEPATH'];
                
                $tipoFirmaValida = false;
                // Controllo forzatura tipo firma
                if($this->getForceSignMethod()) {
                    $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
                    if ($this->getForceSignMethod() === rsnSigner::TYPE_SIGN_XADES) {
                        if($ext === 'xml') {
                            $tipoFirmaValida = true;
                        }
                    }
                    
                    if (($this->getForceSignMethod() === rsnSigner::TYPE_SIGN_PADES) || 
                        ($this->getForceSignMethod() === rsnSigner::TYPE_SIGN_CADES)) {
                        if($ext === 'p7m') {
                            $tipoFirmaValida = true;
                        }
                    }
                }
                
                if(!$this->getForceSignMethod() || $tipoFirmaValida) {
                    $firmaVerificata = false;

                    $Ret = $this->VerificaFirma($inputfilepath, $uplFile);
                    if ($Ret['STATO'] !== true) {
                        Out::msgStop("Attenzione", $Ret['MESSAGGIO'] . '. Nome file : ' . $this->extractFileName(basename($uplFile)));
                        unlink($uplFile);
                    } else {
                        $firmaVerificata = true;
                    }

                    if($firmaVerificata) {
                        if (!@rename($uplFile, $destFile)) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file! Nome file : " . $this->extractFileName(basename($uplFile)));
                        }

                        $this->allegati[$rowid]['FILEFIRMATO'] = $destFile;
                        $this->allegati[$rowid]['SIGNRESULT'] = "OK";
                        $this->allegati[$rowid]['FILENAMEFIRMATO'] = $FileNameFirmato;
                        Out::hide($this->nameForm . '_FirmaRemota');
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_INS_RECORD, 'Estremi' => "{$this->allegati[$rowid]['FILEORIG']}: Allegato file firmato"));
                        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                    }
                } else {
                    $this->removeFileFromList(basename($FileNameFirmato));
                    Out::msgStop("Upload File:", "File firmato in " . ($ext === 'xml' ? 'XADES' : 'PADES/CADES') . 
                            ' ma è richiesto ' . $this->getForceSignMethod() . '.');
                }
            } else {
                $this->removeFileFromList(basename($FileNameFirmato));
                Out::msgStop("Upload File:", "File corrispondente non firmato non trovato! Nome file caricato : " . $this->extractFileName(basename($uplFile)));
            }
        } else {
            $this->removeFileFromList(basename($FileNameFirmato));
            Out::msgStop("Upload File:", "Il peso del file firmato è maggiore di " . $this->fileLimit . "kb! Nome file : " . $this->extractFileName(basename($uplFile)));
        }
    }
    
    private function extractFileName($filename) {       
        $pos = strpos($filename, '-');
        if($pos !== false && $pos > 0) {
            $filename = substr($filename, $pos+1);
            $filename = $this->extractFileName($filename);
        }
        
        return $filename;
    }
    
    private function getRowIdAllegatoFromNomeFile($filename) {        
        foreach($this->getAllegati() as $key => $allegato) {
            
            // Se il file firmato ha estensione .p7m effettua il controllo rispetto al file originale
            $filename = strtolower($filename);
            if (strpos($filename, '.p7m') && (substr($filename, 0, strpos($filename, '.p7m')) == strtolower($allegato['FILEORIG']))) {
                return $key;
            }
            
            if($filename == strtolower($allegato['FILEORIG'])) {
                return $key;
            }
        }
        
        return null;        
    }
    
    private function removeFileFromList($filename) {
        if(!empty($this->listaFileFirmati)) {
            $filename = trim($filename);
            $lista = split(',', $this->listaFileFirmati);
            foreach ($lista as $key => $value) {
                $value = trim($value);
                if($value == $filename) {
                    unset($lista[$key]);
                }
            }
            
            $this->listaFileFirmati = implode(",", $lista);
            
            Out::valore($this->nameForm . '_FIRMATO', $this->listaFileFirmati);
        }
    }
    
    private function verifyFileSize($uplFile) {        
        $fileSizeKB = filesize($uplFile);
        $fileSizeKB = $fileSizeKB / 1024;
        $fileSizeKB = floatval($fileSizeKB);
        $fileSizeLimitKB = floatval($this->getFileLimit());
        
        if($fileSizeKB > $fileSizeLimitKB) {
            return false;
        } else {
            return true;
        }        
    }
        
    /*
     * GETTER/SETTER
     */
    
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

    public function getOutputFileName() {
        return $this->outputFileName;
    }

    public function setOutputFileName($outputFileName) {
        $this->outputFileName = $outputFileName;
    }

    public function getInputFileName() {
        return $this->inputFileName;
    }

    public function setInputFileName($inputFileName) {
        $this->inputFileName = $inputFileName;
    }

    public function setTopMsg($topMsg) {
        $this->topMsg = $topMsg;
    }

    public function getAperturaPreferenziale() {
        return $this->aperturaPreferenziale;
    }

    public function setAperturaPreferenziale($aperturaPreferenziale) {
        $this->aperturaPreferenziale = $aperturaPreferenziale;
    }
    
    public function getFileLimit() {
        return $this->fileLimit;
    }

    public function setFileLimit($fileLimit) {
        $this->fileLimit = $fileLimit;
    }
    
    public function getObjValidator() {
        return $this->objValidator;
    }

    public function setObjValidator($objValidator) {
        $this->objValidator = $objValidator;
    }
    
    public function getCheckForFileName() {
        return $this->checkForFileName;
    }

    public function setCheckForFileName($checkForFileName) {
        $this->checkForFileName = $checkForFileName;
    }
    
    public function getForceSignMethod() {
        return $this->forceSignMethod;
    }

    public function setForceSignMethod($forceSignMethod) {
        $this->forceSignMethod = $forceSignMethod;
    }    

}
