<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/AppUtility.class.php';

function envConfig() {
    $envConfig = new envConfig();
    $envConfig->parseEvent();
    return;
}

class envConfig extends itaModel {

    public $nameForm = "envConfig";
    public $menLib;        
    
    function __construct() {
        parent::__construct();
        $this->menLib = new menLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::css($this->nameForm . '_wrapper', 'overflow', 'initial');

                // Controllo permessi visualizzazione pulsanti
                Out::hide($this->nameForm . '_divCol3');

                Out::hide($this->nameForm . '_devTestProg_btn');

                $gruppi = $this->menLib->getNomiGruppi($this->menLib->utenteAttuale);
                foreach ($gruppi as $key => $gruppo) {
                    if ($gruppo == 'ITALSOFT' || $gruppo == 'italsoft') {
                        Out::show($this->nameForm . '_devTestProg_btn');

                        Out::show($this->nameForm . '_divCol3');
                        break;
                    }
                    if ($gruppo == 'ADMIN' || $gruppo == 'admin') {
                        Out::show($this->nameForm . '_divCol3');
                    }
                }
                
                // Legge informazioni build, se presenti le aggiunge in visualizzazione nella form
                $buildInfo = $this->getBuildInfo();
                $htmlBuildInfo = '';
                if ($buildInfo !== false) {
                    $htmlBuildInfo = '<br>
                                      <span style="font-style:italic;">Versione build: </span>
                                      <span style="font-weight:bold;">' . 
                                      $buildInfo['buildVersion'] . '</span><span style="font-style:italic;">  del  </span><span style="font-weight:bold;">' .
                                      $buildInfo['buildDate'] . '</span>';
                } 
                
                Out::html($this->nameForm . '_divMessaggio', '
                    <center>
                        <span style="font-style:italic;">Pannello di controllo per l\'utente:</span>
                        <br>
                        <span style="font-weight:bold;font-size:1.2em;">' . App::$utente->getKey('nomeUtente') . '</span>'
                        . $htmlBuildInfo .                                                
                    '</center>');
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_accProfilo_btn':
                        //Out::msgInfo("Attenzione","Applicazione non Disponibile");
                        Out::closeDialog($this->nameForm);
                        $model = 'accProfilo';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_menDeskConfig_btn':
                        //Out::msgInfo("Attenzione","Applicazione non Disponibile");
                        Out::closeDialog($this->nameForm);
                        $model = 'menDeskConfig';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_menCwbConfig_btn':
                        //Out::msgInfo("Attenzione","Applicazione non Disponibile");
                        Out::closeDialog($this->nameForm);
                        $model = 'menCwbConfig';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_menAuthConfig_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'menAuthConfig';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Firma_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST['messagge'] = '<div style="text-align:center;" class="ita-box ui-widget-content ui-corner-all "><div style="vertical-align:middle;display:inline-block;" class="ita-icon ita-icon-sigillo-32x32"></div><div style="top:auto; display:inline-block;font-size:1.5em;color:green;vertical-align:middle;">Carica il file da firmare</div></div><br>';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUpload";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Verifica_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST['messagge'] = '<div style="text-align:center;" class="ita-box ui-widget-content ui-corner-all "><div style="vertical-align:middle;display:inline-block;" class="ita-icon ita-icon-shield-green-32x32"></div><div style="top:auto; display:inline-block;font-size:1.5em;color:green;vertical-align:middle;">Carica il file da verificare</div></div><br>';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadVerify";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_PosteCom_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'ptiRegCom';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST['uteIns'] = App::$utente->getKey('nomeUtente');
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnRegCom";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
//                    case $this->nameForm . '_devToolsMenu_btn':
//                        Out::closeDialog($this->nameForm);
//                        $model = 'devMenu';
//                        $_POST = array();
//                        $_POST['event'] = 'openform';
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
//                        break;
                    case $this->nameForm . '_devMenExplorer_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'menExplorer';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_devTestProg_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'devTestProg';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
//                    case $this->nameForm . '_devAnaParametri_btn':
//                        Out::closeDialog($this->nameForm);
//                        $model = 'devAnaParametri';
//                        $_POST = array();
//                        $_POST['event'] = 'openform';
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
//                        break;
                    case $this->nameForm . '_envConfigParam_btn':
                        Out::closeDialog($this->nameForm);
                        $model = 'envConfigParam';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_devGestReport_btn':
                        $gruppi = $this->menLib->getNomiGruppi($this->menLib->utenteAttuale);
                        $gestione = false;
                        foreach ($gruppi as $key => $gruppo) {
                            if ($gruppo == 'ITALSOFT' || $gruppo == 'italsoft') {
                                $gestione = true;
                            }
                        }
                        Out::closeDialog($this->nameForm);
                        $model = 'devGestReport';
                        $_POST = array();
                        $_POST['gestione'] = $gestione;
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_accCambiaPass_btn':
                        include_once ITA_BASE_PATH . '/apps/Utility/utiSecDiag.class.php';
                        utiSecDiag::GetMsgInputPassword($this->nameForm, 'Cambia Password');
                        break;
                    case $this->nameForm . '_Filebox_btn':
                        itaLib::openForm('utiFilebox');    
                        $model = "utiFilebox";
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model, $model);
                        $formObj->setEvent('openform');                        
                        $formObj->parseEvent();
                        $this->close();
                        break;

                    case $this->nameForm . '_returnPassword':
                        $ditta = App::$utente->getKey('ditta');
                        $utente = App::$utente->getKey('nomeUtente');
                        $password = $_POST[$this->nameForm . '_password'];
                        $ret_verpass = ita_verpass($ditta, $utente, $password);
                        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
                            Out::msgStop("Errore di validazione", "Inserire la Password Corretta!");
                            break;
                        }

                        $model = "accPassword";
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setEvent('openform');
                        $formObj->setModo('modifica');
                        $formObj->parseEvent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
            case "returnUpload":
                itaLib::openForm('rsnAuth', true);
                /* @var $rsnAuth rsnAuth */
                $rsnAuth = itaModel::getInstance('rsnAuth');
                $rsnAuth->setEvent('openform');
                $rsnAuth->setReturnEvent("returnFromSignAuth");
                $rsnAuth->setReturnModel($this->nameForm);
                $rsnAuth->setReturnId('');
                $rsnAuth->setInputFilePath($_POST['uploadedFile']);
                $rsnAuth->setOutputFilePath($_POST['uploadedFile'] . ".p7m");
                $rsnAuth->setInputFileName($_POST['file']);
                $rsnAuth->setOutputFileName('');
                $rsnAuth->setMultiFile(true);
                $rsnAuth->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
                $rsnAuth->parseEvent();
                break;
            case "returnUploadVerify":
                $model = "utiP7m";
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

                $fileDaVerificare = $_POST['file'];
                $nomeFileCaricato = $_POST['uploadedFile'];

                $_POST['event'] = "openform";
                $_POST['file'] = $nomeFileCaricato;
                $_POST['fileOriginale'] = $fileDaVerificare;
                $model();
                break;
            case "returnFromSignAuth":
                if ($_POST['result'] === true) {
                    Out::openDocument(utiDownload::getUrl($_POST['outputFileName'], $_POST['outputFilePath'], true));
                } else {
                    if ($_POST['result'] == "cancel") {
                        break;
                    }
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                if (isset($_POST['inputFilePath'])) {
                    if (file_exists($_POST['inputFilePath'])) {
                        if ($_POST['inputFilePath'] <> $_POST['outputFilePath'])
                            @unlink($_POST['inputFilePath']);
                    }
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function getBuildInfo() {                
        $buildInfo = AppUtility::getBuildInfo();
        if (count($buildInfo) == 0) {
            return false;            
        }
        $buildVersion = ((isset($buildInfo['latest']) && isset($buildInfo['latest']['version'])) ? $buildInfo['latest']['version'] : '');
        $buildDate = ((isset($buildInfo['latest']) && isset($buildInfo['latest']['date'])) ? $buildInfo['latest']['date'] : '');
        if (!$buildVersion) {
            return false;        
        }
        return array(
            'buildVersion' => $buildVersion,
            'buildDate' => $buildDate
        );
    }
    
}

?>
