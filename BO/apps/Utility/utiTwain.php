<?php

/**
 *
 * Gestione interfaccia scanner twain
 *
 *  * PHP Version 5
 *
 * @category   utiTwain
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>*
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    21.05.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPCore/itaTwain.class.php');

function utiTwain() {
    $utiTwain = new utiTwain();
    $utiTwain->parseEvent();
    return;
}

class utiTwain extends itaModel {

    private $twainCAPS;
    private $endorserParams;
    private $forzaDevice;
    public $nameForm = "utiTwain";
    private $flagPDFA;
    private $closeOnReturn;
    private $titolo;
    private $currAllegato;

    function __construct() {
        parent::__construct();
        $this->flagPDFA = App::$utente->getKey($this->nameForm . '_flagPDFA');
        $this->closeOnReturn = App::$utente->getKey($this->nameForm . '_closeOnReturn');
        $this->twainCAPS = App::$utente->getKey($this->nameForm . '_twainCAPS');
        $this->endorserParams = App::$utente->getKey($this->nameForm . '_endorserParams');
        $this->forzaDevice = App::$utente->getKey($this->nameForm . '_forzaDevice');
        $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_flagPDFA', $this->flagPDFA);
            App::$utente->setKey($this->nameForm . '_closeOnReturn', $this->closeOnReturn);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_twainCAPS', $this->twainCAPS);
            App::$utente->setKey($this->nameForm . '_endorserParams', $this->endorserParams);
            App::$utente->setKey($this->nameForm . '_forzaDevice', $this->forzaDevice);
        }
    }

    public function getEndorserParams() {
        return $this->endorserParams;
    }

    public function setEndorserParams($endorserParams) {
        $this->endorserParams = $endorserParams;
    }

    public function getForzaDevice() {
        return $this->forzaDevice;
    }

    public function setForzaDevice($forzaDevice) {
        $this->forzaDevice = $forzaDevice;
    }

    public function getFlagPDFA() {
        return $this->flagPDFA;
    }

    public function setFlagPDFA($flagPDFA) {
        $this->flagPDFA = $flagPDFA;
    }

    public function getCloseOnReturn() {
        return $this->closeOnReturn;
    }

    public function setCloseOnReturn($closeOnReturn) {
        $this->closeOnReturn = $closeOnReturn;
    }

    public function getTitolo() {
        return $this->titolo;
    }

    public function setTitolo($titolo) {
        $this->titolo = $titolo;
    }

    public function openScanner($titolo, $returnModel, $returnEvent = 'returnFileFromTwain', $returnId = '') {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                return false;
            }
        }
        itaLib::openForm($this->nameForm, true);
        $this->setReturnModel($returnModel);
        $this->setReturnEvent($returnEvent);
        if (!$returnId) {
            $this->setReturnId($returnModel . '_Scanner');
        } else {
            $this->setReturnId($returnId);
        }
        $this->setCloseOnReturn($closeOnReturn);
        $this->setTitolo($titolo);

        if ($this->titolo != '') {
            Out::setDialogOption($this->nameForm, 'title', "'$this->titolo'");
        }
        /*
         * Lettura Parametri
         */
        $Params = $this->GetParams();
        $pversion = $Params['PRODUCTVERSION'];
        if ($pversion) {
            $pversion_metadata = 'pversion:\'' . addslashes($pversion) . '\',';
        }
        $pkey = $Params['PRODUCTKEY'];
        if ($pkey) {
            $pkey_metadata = 'pkey:\'' . addslashes($pkey) . '\',';
        }

        Out::html($this->nameForm . "_divTwain", '
                    <div id="utiTwain_controlContainer" class="ita-twain-dwt-container {' . $pversion_metadata . $pkey_metadata . 'lastsource:\'' . addslashes($this->getCustomConfig('ULTIMODISPOSITIVO')) . '\',width:437,height:511}"
                    ></div>');

        Out::setFocus('', $this->nameForm . '_SourceName');
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->endorserParams = $_POST[$this->nameForm . '_endorserParams'];
                $this->forzaDevice = $_POST[$this->nameForm . '_forzaDevice'];
                $this->titolo = $_POST[$this->nameForm . '_titolo'];
                if ($this->titolo != '') {
                    Out::setDialogOption($this->nameForm, 'title', "'$this->titolo'");
                }
                /*
                 * Lettura Parametri
                 */
                $Params = $this->GetParams();
                $pversion = $Params['PRODUCTVERSION'];
                if ($pversion) {
                    $pversion_metadata = 'pversion:\'' . addslashes($pversion) . '\',';
                }
                $pkey = $Params['PRODUCTKEY'];
                if ($pkey) {
                    $pkey_metadata = 'pkey:\'' . addslashes($pkey) . '\',';
                }
                
                Out::html($this->nameForm . "_divTwain", '
                    <div
                        id="utiTwain_controlContainer"
                        class="ita-twain-dwt-container {' . $pversion_metadata . $pkey_metadata . 'lastsource:\'' . addslashes($this->getCustomConfig('ULTIMODISPOSITIVO')) . '\',width:437,height:511}"
                    ></div>');

                Out::hide($this->nameForm . '_divTwainCap');
                $this->flagPDFA = $_POST[$this->nameForm . "_flagPDFA"];
                $this->closeOnReturn = $_POST[$this->nameForm . "_closeOnReturn"];
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                //TODO@ refactor returnEvent
                $this->returnEvent = $_POST[$this->nameForm . "_returnMethod"];
                //TODO@ refactor returnId
                $this->returnId = $_POST[$this->nameForm . "_returnField"];
                Out::setFocus('', $this->nameForm . '_SourceName');
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SourceName':
                        //
                        // LEGGERE IMPOSTAZIONI PREFERITE DALL'UTENTE
                        //
                        $this->setTwainCaps($_POST[$this->nameForm . '_twainDevice'][$_POST[$this->nameForm . '_SourceName']], $this->endorserParams, $_POST[$this->nameForm . '_Stampa']);
                        if ($this->twainCAPS) {
                            Out::show($this->nameForm . '_Stampa_field');
                        } else {
                            Out::hide($this->nameForm . '_Stampa_field');
                        }
                        Out::hide($this->nameForm . "_divTwainCap");
                        Out::html($this->nameForm . "_divTwainCap", '');
                        if ($this->twainCAPS) {
                            Out::html($this->nameForm . "_divTwainCap", $this->twainCaps2Html($this->twainCAPS));
                            Out::show($this->nameForm . "_divTwainCap");
                        }


                        $this->caricaConfigurazioni();

                        break;
                    case $this->nameForm . '_Stampa':
                        if ($_POST[$this->nameForm . '_Stampa'] == 1) {
                            Out::html($this->nameForm . "_divTwainCap", $this->twainCaps2Html($this->twainCAPS));
                        } else {
                            $arrCaps = array();
                            $arrCaps[] = array('capability' => '0x1027', 'datatype' => '5', 'containertype' => '0', 'datavalue' => '0'); // CAP_PRINTERENABLED
                            $arrCaps[] = array(
                                'capability' => '0x102a',
                                'valuetype' => '',
                                'datatype' => '5',
                                'containertype' => '1',
                                'datavalue' => ''
                            ); // CAP_PRINTERSTRING
                            Out::html($this->nameForm . "_divTwainCap", $this->twainCaps2Html($arrCaps));
                        }
                        Out::show($this->nameForm . "_divTwainCap");
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SalvaImpostazioni':
                        $this->setConfig();
                        Out::msgInfo("Profili acquisizione", "<br><br>Il profilo preferito per il dispositivo :<br>" . $_POST[$this->nameForm . '_twainDevice'][$_POST[$this->nameForm . '_SourceName']] . "<br>salvato correttamente.");
                        break;
                    case $this->nameForm . '_btnSalva':
                        $this->setCustomConfig("ULTIMODISPOSITIVO", $_POST[$this->nameForm . '_twainDevice'][$_POST[$this->nameForm . '_SourceName']]);
                        $strHTTPServer = $_SERVER['SERVER_ADDR'];
                        $strActionPage = 'upload_twain.php';
                        $randName = md5(rand() * time());
                        $ita_UploadName = $randName;
                        $callBackId = $this->nameForm . '_btnSalva';
                        Out::codice("btnUpload_onclick('" . $strHTTPServer . "','" . $strActionPage . "','" . $ita_UploadName . "','" . $callBackId . "')");
                        break;
                    case $this->nameForm . '_AnnullaPDFA':
                        if ($this->currAllegato['uplFile']) {
                            unlink($this->currAllegato['uplFile']);
                            Out::msgInfo('Allega PDF', "Allegato Rifiutato:" . $this->currAllegato['uplFile'], 'auto', 'auto', 'desktopBody', true);
                        }
                        break;
                    case $this->nameForm . '_ConfermaPDFA':
                        if (!@rename($this->currAllegato['uplFile'], $this->currAllegato['destFile'])) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file!", 'auto', 'auto', '', true);
                        } else {
                            $_POST = array();
                            $_POST['event'] = $this->returnEvent;
                            $_POST['model'] = $this->returnModel;
                            $_POST['id'] = $this->returnId;
                            $_POST['retFile'] = $this->currAllegato['origFile'];
                            $phpURL = App::getConf('modelBackEnd.php');
                            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                            $returnModel = $this->returnModel;
                            $returnModel();
                            if ($this->closeOnReturn == 1) {
                                $this->returnToParent();
                            }
                            Out::msgInfo('Allega PDF', "Allegato PDF Accettato nonostante la non Conformità a PDF/A:" . $this->currAllegato['origFile'], 'auto', 'auto', 'desktopBody', true);
                        }
                        break;
                    case $this->nameForm . '_ConvertiPDFA':
                        $retConvert = $this->convertiPDFA($this->currAllegato['uplFile'], $this->currAllegato['destFile'], true);
                        if ($retConvert['status'] == 0) {
                            $_POST = array();
                            $_POST['event'] = $this->returnEvent;
                            $_POST['model'] = $this->returnModel;
                            $_POST['id'] = $this->returnId;
                            $_POST['retFile'] = $this->currAllegato['origFile'];
                            $phpURL = App::getConf('modelBackEnd.php');
                            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                            $returnModel = $this->returnModel;
                            $returnModel();
                            if ($this->closeOnReturn == 1) {
                                $this->returnToParent();
                            }
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A: " . $this->currAllegato['origFile'], 'auto', 'auto', 'desktopBody', true);
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message'], 'auto', 'auto', '', true);
                        }
                        break;
                    case $this->nameForm . '_btnChiudi':
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onTwainUpload':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnSalva':
                        $origFile = $_POST['fileTwain'];
                        $uplFile = itaLib::getUploadPath() . "/" . $origFile;
                        $destFile = itaLib::getPrivateUploadPath() . "/" . $origFile;
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita", 'auto', 'auto', '', true);
                                $this->returnToParent();
                            }
                        }

                        if (strtolower(pathinfo($uplFile, PATHINFO_EXTENSION)) == 'pdf') {
                            $retVerify = $this->verificaPDFA($uplFile);
                        } else {
                            $retVerify['status'] = 0;
                        }
                        if ($retVerify['status'] != 0) {
                            if ($retVerify['status'] == -5) {
                                $flag = $this->flagPDFA;
                                if (!$flag) {
                                    $flag = "00A";
                                }
                                $verifyPDFA = substr($flag, 0, 1);
                                $convertPDFA = substr($flag, 1, 1);
                                $PDFLevel = substr($flag, 2, 1);
                                if (!$convertPDFA) {
                                    $this->currAllegato = array(
                                        'uplFile' => $uplFile,
                                        'destFile' => $destFile,
                                        'origFile' => $origFile
                                    );

                                    Out::msgQuestion("Allegato non conforme PDF/A ", $retVerify['message'], array(
                                        'F8-Rifiuta Allegato' => array('id' => $this->nameForm . '_AnnullaPDFA', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Accetta Allegato' => array('id' => $this->nameForm . '_ConfermaPDFA', 'model' => $this->nameForm, 'shortCut' => "f5"),
                                        'F1-Converti Allegato' => array('id' => $this->nameForm . '_ConvertiPDFA', 'model' => $this->nameForm, 'shortCut' => "f1")
                                            ), 'auto', 'auto', 'false', true
                                    );
                                } else {
                                    $retConvert = $this->convertiPDFA($uplFile, $destFile, true);
                                    if ($retConvert['status'] == 0) {

                                        $_POST = array();
                                        $_POST['event'] = $this->returnEvent;
                                        $_POST['model'] = $this->returnModel;
                                        $_POST['id'] = $this->returnId;
                                        $_POST['retFile'] = $origFile;
                                        $phpURL = App::getConf('modelBackEnd.php');
                                        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                                        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                                        $returnModel = $this->returnModel;
                                        $returnModel();
                                        if ($this->closeOnReturn == 1) {
                                            $this->returnToParent();
                                        }
                                        Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A verifica il PDF." . $this->currAllegato['origFile'], 'auto', 'auto', 'desktopBody', true);
                                        Out::openDocument(utiDownload::getUrl($origFile, $destFile));
                                    } else {
                                        Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message'], 'auto', 'auto', '', true);
                                        break;
                                    }
                                }
                            } else {
                                Out::msgStop("Verifica PDF/A Impossibile", $retVerify['message'], 'auto', 'auto', '', true);
                                unlink($uplFile);
                                break;
                            }
                        } else {
                            if (@rename($uplFile, $destFile)) {
                                $_POST = array();
                                $_POST['event'] = $this->returnEvent;
                                $_POST['model'] = $this->returnModel;
                                $_POST['id'] = $this->returnId;
                                $_POST['retFile'] = $origFile;
                                $phpURL = App::getConf('modelBackEnd.php');
                                $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
                                include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
                                $returnModel = $this->returnModel;
                                $returnModel();
                                if ($this->closeOnReturn == 1) {
                                    $this->returnToParent();
                                }
                            } else {
                                Out::msgStop("Acquisizione file", "Caricamento file da scanner fallita.
                                    <br><br>
                                        $uplFile
                                    <br>
                                        $destFile", 'auto', 'auto', '', true);
                            }
                        }
                        break;
                }
                break;
            case 'onBlur':
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_flagPDFA');
        App::$utente->removeKey($this->nameForm . '_closeOnReturn');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_twainCAPS');
        App::$utente->removeKey($this->nameForm . '_endorserParams');
        App::$utente->removeKey($this->nameForm . '_forzaDevice');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function verificaPDFA($fileName) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
            return $ret;
        }
        $flag = $this->flagPDFA;
        if (!$flag) {
            $flag = "00A";
        }
        $verifyPDFA = substr($flag, 0, 1);
        $convertPDFA = substr($flag, 1, 1);
        $PDFLevel = substr($flag, 2, 1);
        if ($verifyPDFA == "1") {
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
            $ret = itaPDFAUtil::verifyPDFSimple($fileName, 2, $PDFLevel);
        } else {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
        }
        return $ret;
    }

    public function convertiPDFA($fileName, $outputFile, $deleteFileName = false) {
        if ($fileName == $outputFile) {
            $ret['status'] = -99;
            $ret['message'] = "Nome file da convertire uguale al nome file convertito. Non ammesso.";
            return $ret;
        }
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = -99;
            $ret['message'] = "file non adatto alla conversione";
            return $ret;
        }

        $flag = $this->flagPDFA;
        if (!$flag) {
            $flag = "00A";
        }
        $verifyPDFA = substr($flag, 0, 1);
        $convertPDFA = substr($flag, 1, 1);
        $PDFLevel = substr($flag, 2, 1);
        $ret = itaPDFAUtil::convertPDF($fileName, $outputFile, 2, $PDFLevel);
        if ($ret['status'] == 0) {
            if ($deleteFileName === true) {
                unlink($fileName);
            }
        }
        return $ret;
    }

    function twainCaps2Html($arrCaps) {
        $html = '<TABLE id="' . $this->nameForm . '_gridtawincap" class="ita-twaincap-grid">';
        foreach ($arrCaps as $id => $twainCap) {
            $capability = $twainCap['capability'];
            $valuetype = $twainCap['valuetype'];
            $datatype = $twainCap['datatype'];
            $containertype = $twainCap['containertype'];
            $datavalue = $twainCap['datavalue'];
            $html .= '  <TR id="' . $this->nameForm . '_gridtawincap_' . $id . '" class="ita-twaincap-row">';
            $html .= '      <TD class="ita-twaincap-capability">' . $capability . '</TD>';
            $html .= '      <TD class="ita-twaincap-valuetype">' . $valuetype . '</TD>';
            $html .= '      <TD class="ita-twaincap-datatype">' . $datatype . '</TD>';
            $html .= '      <TD class="ita-twaincap-containertype">' . $containertype . '</TD>';
            $html .= '      <TD class="ita-twaincap-datavalue">' . $datavalue . '</TD>';
            $html .= '  </TR>';
        }
        $html .= "</TABLE>";
        return $html;
    }

    function setTwainCaps($sourceDeviceName, $endorserParams, $enabledStampa = false) {
        App::log("Abilito: " . $sourceDeviceName);
        $device = new itaTwain($sourceDeviceName);
        if ($this->forzaDevice) {
            $driverType = $this->forzaDevice;
        } else {
            $driverType = $device->getDriverType();
        }
        if ($driverType == "twaingeneric") {
            $this->twainCAPS = array();
            return false;
        }

        if (!$enabledStampa) {
            $device->setCapPrinter(itaTwain::TWPR_ENDORSERBOTTOMBEFORE);
            $device->setCapPrinterEnabled(itaTwain::VALUE_FALSE);
            $device->setCapPrinterString('');

            $device->setCapPrinter(itaTwain::TWPR_ENDORSERBOTTOMAFTER);
            $device->setCapPrinterEnabled(itaTwain::VALUE_FALSE);
            $device->setCapPrinterString('');

            $device->setCapPrinter(itaTwain::TWPR_ENDORSERTOPBEFORE);
            $device->setCapPrinterEnabled(itaTwain::VALUE_FALSE);
            $device->setCapPrinterString('');

            $device->setCapPrinter(itaTwain::TWPR_ENDORSERTOPAFTER);
            $device->setCapPrinterEnabled(itaTwain::VALUE_FALSE);
            $device->setCapPrinterString('');
            $this->twainCAPS = $device->getCapsArray();
            return true;
        }
        switch ($driverType) {
            case "microrei":
                $device->setCapPrinter(itaTwain::TWPR_ENDORSERBOTTOMBEFORE);
                $device->setCapPrinterEnabled(itaTwain::VALUE_FALSE);
                $device->setCapPrinterString('');

                $device->setCapPrinter(itaTwain::TWPR_ENDORSERTOPBEFORE);
                $device->setCapPrinterEnabled(itaTwain::VALUE_TRUE);
                $device->setCapPrinterString($endorserParams['CAP_PRINTERSTRING']);

                $device->setCap(
                        array(
                            'capability' => itaTwain::CAP_MICROREI_PRINTERPROTID,
                            'valuetype' => itaTwain::TWTY_UINT16,
                            'datatype' => itaTwain::TWON_ONEVALUE,
                            'containertype' => itaTwain::VALUE_FALSE,
                            'datavalue' => '0'
                        )
                );

                $device->setCap(
                        array(
                            'capability' => itaTwain::CAP_MICROREI_PRINTERPOSITION,
                            'valuetype' => itaTwain::TWTY_UINT16,
                            'datatype' => itaTwain::TWON_ONEVALUE,
                            'containertype' => itaTwain::VALUE_FALSE,
                            'datavalue' => '3'
                        )
                );

                $device->setCap(
                        array(
                            'capability' => itaTwain::CAP_MICROREI_PRINTERDENSITY,
                            'valuetype' => itaTwain::TWTY_UINT16,
                            'datatype' => itaTwain::TWON_ONEVALUE,
                            'containertype' => itaTwain::VALUE_FALSE,
                            'datavalue' => '11'
                        )
                );
                break;
            default:
                $device->setCapPrinter(itaTwain::TWPR_ENDORSERTOPAFTER);
                $device->setCapPrinterEnabled(itaTwain::VALUE_TRUE);
                $device->setCapPrinterMode(itaTwain::TWPM_SINGLESTRING);
                $device->setCapPrinterString($endorserParams['CAP_PRINTERSTRING']);
                break;
        }
        $this->twainCAPS = $device->getCapsArray();
        return true;
    }

    private function caricaConfigurazioni() {
//        App::log('caricaConfigurazioni');
//        App::log($_POST);
        $parametri = $this->getCustomConfig('SCANNER/' . $_POST[$this->nameForm . '_twainDevice'][$_POST[$this->nameForm . '_SourceName']] . '/PARAMETRI');
        foreach ($parametri as $key => $valore) {
            if ($key == $this->nameForm . '_pixelType') {
                switch ($valore) {
                    case '':
                        Out::attributo($this->nameForm . "_PixelBW", "checked", "0", "checked");
                        break;
                    case '2':
                        Out::attributo($this->nameForm . "_PixelColor", "checked", "0", "checked");
                        break;
                    case '1':
                    default :
                        Out::attributo($this->nameForm . "_PixelGray", "checked", "0", "checked");
                        break;
                }
            } else if ($key == $this->nameForm . '_ImageType') {
                switch ($valore) {
                    case '1':
                        Out::attributo($this->nameForm . "_ImageJpg", "checked", "0", "checked");
                        Out::attributo($this->nameForm . "_MultiImage", "disabled", "0");
                        break;
                    case '2':
                        Out::attributo($this->nameForm . "_ImageTiff", "checked", "0", "checked");
                        Out::attributo($this->nameForm . "_MultiImage", "disabled", "1");
                        break;
                    case '3':
                    default :
                        Out::attributo($this->nameForm . "_ImagePdf", "checked", "0", "checked");
                        Out::attributo($this->nameForm . "_MultiImage", "disabled", "1");
                        break;
                }
            } else {
                Out::valore($key, $valore);
            }
        }
    }

    private function setConfig() {
        $parametri = array(
            $this->nameForm . '_ShowSource' => $_POST[$this->nameForm . '_ShowSource'],
            $this->nameForm . '_pixelType' => $_POST[$this->nameForm . '_pixelType'],
            $this->nameForm . '_Resolution' => $_POST[$this->nameForm . '_Resolution'],
            $this->nameForm . '_Feeder' => $_POST[$this->nameForm . '_Feeder'],
            $this->nameForm . '_FronteRetro' => $_POST[$this->nameForm . '_FronteRetro'],
            $this->nameForm . '_ImageType' => $_POST[$this->nameForm . '_ImageType'],
            $this->nameForm . '_MultiImage' => $_POST[$this->nameForm . '_MultiImage'],
            $this->nameForm . '_Stampa' => $_POST[$this->nameForm . '_Stampa']
        );
        $this->setCustomConfig("SCANNER/{$_POST[$this->nameForm . '_twainDevice'][$_POST[$this->nameForm . '_SourceName']]}/PARAMETRI", $parametri);
    }

    private function GetParams() {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

        /*
         * Elenco parametri
         */
        $ArrParams = array();

        $devLib = new devLib();
        $global_params = $devLib->getEnv_config_global_ini('WEBTWAINSCAN');
        if ($global_params) {
            foreach ($global_params as $key => $param) {
                $ArrParams[$key] = $param['CONFIG'];
            }
        } else {
            /* Product Version */
            $params = $devLib->getEnv_config('WEBTWAINSCAN', 'codice', 'PRODUCTVERSION', false);
            $ArrParams['PRODUCTVERSION'] = $params['CONFIG'];
            /* ProductKey */
            $params = $devLib->getEnv_config('WEBTWAINSCAN', 'codice', 'PRODUCTKEY', false);
            $ArrParams['PRODUCTKEY'] = $params['CONFIG'];
        }

        return $ArrParams;
    }

}

?>
