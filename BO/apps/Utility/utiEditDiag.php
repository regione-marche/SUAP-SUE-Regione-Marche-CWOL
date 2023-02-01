<?php

register_shutdown_function("inline_custom_fatal_handler");
set_error_handler('inline_custom_error_handler', E_ERROR);

function inline_custom_error_handler($no, $str, $file, $line) {
    file_put_contents('C:/errors.txt', sprintf('%s in %s:%d' . "\n", $str, $file, $line), FILE_APPEND);
}

function inline_custom_fatal_handler() {
    if (($error = error_get_last()) !== NULL) {
        inline_custom_error_handler('', $error["message"], $error["file"], $error["line"]);
    }
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once(ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php');

function utiEditDiag() {
    $utiEditDiag = new utiEditDiag();
    $utiEditDiag->parseEvent();
    return;
}

class utiEditDiag extends itaModel {

    public $ITALWEB;
    public $docLib;
    public $nameForm = "utiEditDiag";
    public $returnModel;
    public $returnEvent;
    public $returnField;
    public $editText;
    public $paramText;
    public $dictionaryLegend;
    public $dictionaryValues;
    public $rowidText;
    private $matches_patterns = '<!--\s*(ita.*?):(.*?)\s*-->';

    function __construct() {
        parent::__construct();
        try {
            $this->docLib = new docLib();
            $this->ITALWEB = $this->docLib->getITALWEB();
            $this->returnModel = App::$utente->getKey('utiEditDiag_returnModel');
            $this->returnEvent = App::$utente->getKey('utiEditDiag_returnEvent');
            $this->returnField = App::$utente->getKey('utiEditDiag_returnField');
            $this->dictionaryLegend = App::$utente->getKey('utiEditDiag_dictionaryLegend');
            $this->dictionaryValues = App::$utente->getKey('utiEditDiag_dictionaryValues');
            $this->rowidText = App::$utente->getKey('utiEditDiag_rowidText');
            $this->editText = App::$utente->getKey('utiEditDiag_editText');
            $this->paramText = App::$utente->getKey('utiEditDiag_paramText');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey('utiEditDiag_returnModel', $this->returnModel);
            App::$utente->setKey('utiEditDiag_returnEvent', $this->returnEvent);
            App::$utente->setKey('utiEditDiag_returnField', $this->returnField);
            App::$utente->setKey('utiEditDiag_dictionaryLegend', $this->dictionaryLegend);
            App::$utente->setKey('utiEditDiag_dictionaryValues', $this->dictionaryValues);
            App::$utente->setKey('utiEditDiag_rowidText', $this->rowidText);
            App::$utente->setKey('utiEditDiag_editText', $this->editText);
            App::$utente->setKey('utiEditDiag_paramText', $this->paramText);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->returnField = $_POST['returnField'];
                $this->dictionaryLegend = $_POST['dictionaryLegend'];
                $this->dictionaryValues = $_POST['dictionaryValues'];
                $this->rowidText = $_POST['rowidText'];
                if ($_POST['readonly'] == true) {
                    Out::hide($this->nameForm . '_Salva');
                }
                $this->editText = $_POST['edit_text'];
                preg_match_all($this->matches_patterns, $this->editText, $matches);
                $this->paramText = array();
                foreach ($matches[0] as $key => $value) {
                    $this->paramText[$matches[1][$key]] = $matches[2][$key];
                }
                $this->editText = preg_replace('(<!--\s*ita.*?\s*-->)', '', $this->editText);
                Out::valore($this->nameForm . '_editor', $this->editText);
                $this->showParameters();
                Out::codice('tinyActivate("' . $this->nameForm . '_editor",true);');
                break;
            case "onClick":
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnDelLayout':
                        unset($this->paramText['itaModelloXhtml']);
                        $this->showParameters();
                        break;
                    case $this->nameForm . '_btnConfig':
                        docRic::docRicLayout($this->nameForm);
                        break;
                    case $this->nameForm . '_Salva':
                        $returnText = $this->compileParamText() . $_POST[$this->nameForm . '_editor'];
                        $model = $this->returnModel;
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['returnField'] = $this->returnField;
                        $_POST['returnText'] = $returnText;
                        $_POST['rowidText'] = $this->rowidText;
                        $model();
                        Out::closeDialog($this->nameForm);
                        break;
                    case $this->nameForm . '_Preview':
                        $returnText = $this->compileParamText() . $_POST[$this->nameForm . '_editor'];
//
// Creo il PDF
//
                        $pdfPreview = $this->docLib->Xhtml2Pdf($returnText, $this->dictionaryValues);
                        if ($pdfPreview === false) {
                            Out::msgStop("Errore", $this->docLib->getErrMessage());
                            break;
                        }
                        Out::openDocument(utiDownload::getUrl(App::$utente->getKey('TOKEN') . "-preview.pdf", $pdfPreview));


//                        $documenti_rec = $this->docLib->getDocumenti('A4_PRATICHE');
//                        if ($documenti_rec) {
//                            $unserContent = unserialize($documenti_rec['CONTENT']);
//                            $headerValue = $unserContent['XHTML_HEADER'];
//                            $footerValue = $unserContent['XHTML_FOOTER'];
//                        } else {
//                            $headerValue = null;
//                            $footerValue = null;
//                        }
//                        $returnText = $_POST[$this->nameForm . '_editor'];
//
//                        $itaSmarty = new itaSmarty();
//
//                        //Sostituzione variabili
//                        foreach ($this->dictionaryValues as $key => $valore) {
//                            $itaSmarty->assign($key, $valore);
//                        }
//
//                        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-tmp_editor.xhtml';
//                        if (!@file_put_contents($documentoTmp, addslashes($returnText))) {
//                            return false;
//                        } else {
//                            $documentoCompilato = $itaSmarty->fetch($documentoTmp);
//                        }
//                        //
//                        
//                        //$itaSmarty->assign('documentbody', $returnText);
//                        $itaSmarty->assign('documentbody', $documentoCompilato);
//                        $itaSmarty->assign('documentheader', $headerValue);
//                        $itaSmarty->assign('documentfooter', $footerValue);
//
//                        $documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';
//                        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layout0000.xhtml";
//                        if (!@copy($layoutTemplate, $documentLayout)) {
//                            Out::msgStop("Errore", "Copia template layout Fallita");
//                            break;
//                        }
//                        $contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
//                        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
//                        $pdfPreview = itaLib::getAppsTempPath() . '/documentpreview.pdf';
//
//                        if (!file_put_contents($documentPreview, $contentPreview)) {
//                            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
//                            break;
//                        }
//                        $command =  App::getConf("Java.JVMPath")." -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $documentPreview . ' ' . $pdfPreview;
//                        passthru($command, $return_var);
//                        Out::openDocument(utiDownload::getUrl(
//                                App::$utente->getKey('TOKEN') . "-preview.pdf", $pdfPreview
//                                )
//                        );
//                        Out::msgInfo("Attenzione", "Copiato: <br>" . $documentoTmp . " <br> da template:<br>" . $layoutTemplate);
                        break;
                }
                break;
            case 'returnXlayout':
                if ($_POST['retKey']) {
                    $Doc_documenti_rec = $this->docLib->getDocumenti($_POST['retKey'], 'rowid');
                    if ($Doc_documenti_rec) {
                        $this->paramText['itaModelloXhtml'] = $Doc_documenti_rec['CODICE'];
                        $this->showParameters();
                    }
                }
                break;
            case 'embedVars':
                $model = 'docVarsBrowser';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['dictionaryLegend'] = $this->dictionaryLegend;
                $_POST['editorId'] = $this->nameForm . '_editor';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'compileVars':
                $returnText = $_POST[$this->nameForm . '_editor'];
//                $itaSmarty = new itaSmarty();
//                foreach ($this->dictionaryValues as $key => $valore) {
//                    $itaSmarty->assign($key, $valore);
//                }
//                $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-tmp_editor.xhtml';
//                if (!@file_put_contents($documentoTmp, $returnText)) {
//                    return false;
//                } else {
//                    $documentoCompilato = $itaSmarty->fetch($documentoTmp);
//                }
                /*
                 * Utilizzo funzione centralizzata per sostiuzione XHTML
                 */
                $masterDoc = itaDocumentFactory::getDocument("XHTML");
                $masterDoc->setDictionary($this->dictionaryValues);

                /*
                 * Aggiunti tag html perchè parseItaElements si aspetta i tag html
                 */
                $masterDoc->setContent("<html>$returnText</html>");
                if (!$masterDoc->mergeDictionary()) {
                    Out::msgStop("Creazione PDF da documento base Fallita: " . $masterDoc->getMessage());
                    break;
                }
                $documentoCompilato = $masterDoc->getContent();
                Out::codice("tinySetContent('" . $this->nameForm . "_editor', '" . str_replace("\r", '\r', str_replace("\n", '\n', addslashes($documentoCompilato))) . "');");
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey('utiEditDiag_returnModel');
        App::$utente->removeKey('utiEditDiag_returnEvent');
        App::$utente->removeKey('utiEditDiag_returnField');
        App::$utente->removeKey('utiEditDiag_dictionaryLegend');
        App::$utente->removeKey('utiEditDiag_dictionaryValues');
        App::$utente->removeKey('utiEditDiag_rowidText');
        App::$utente->removeKey('utiEditDiag_editText');
        App::$utente->removeKey('utiEditDiag_paramText');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function compileParamText() {
        foreach ($this->paramText as $key => $value) {
            $paramText .= "<!-- $key:$value -->";
        }
        return $paramText;
    }

    private function showParameters() {
        $codiceLayout = '';
        $fonteLayout = '';
        if ($this->paramText['itaTestoBase']) {
            $documenti_rec = $this->docLib->getDocumenti($this->paramText['itaTestoBase']);
            $unserMetadata = unserialize($documenti_rec['METADATI']);
            if ($unserMetadata['MODELLOXHTML'] == 'PERSONALIZZATO') {
                $descr_layout = "PERSONALIZZATO NEL TESTO BASE";
                $codiceLayout = '';
            } else if (!$unserMetadata['MODELLOXHTML']) {
                $descr_layout = "NON DEFINITO";
                $codiceLayout = '';
            } else {
                $codiceLayout = $unserMetadata['MODELLOXHTML'];
                $documenti_xlayout_rec = $this->docLib->getDocumenti($codiceLayout);
                $descr_layout = $documenti_xlayout_rec['OGGETTO'];
            }
            $fonteLayout = 'TESTOBASE';
        }

        if ($this->paramText['itaModelloXhtml']) {
            $codiceLayout = $this->paramText['itaModelloXhtml'];
            $documenti_xlayout_rec = $this->docLib->getDocumenti($codiceLayout);
            $descr_layout = $documenti_xlayout_rec['OGGETTO'];

//            $descr_layout = $this->paramText['itaModelloXhtml'];
            $fonteLayout = 'TESTOALLEGATO';
        }

//$content = "Testo base di origine: {$this->paramText['itaTestoBase']} <br/>";
        $content = "<span style=\"font-size:14px;\">Modello pagina </span>";
        $content .= ($fonteLayout == "TESTOBASE") ? " <span style=\"font-size:14px;\">(dal testo base):</span>" : " <span style=\"color:orange;font-size:14px;\">(specifico del documento):</span>";
        $content .= "</br><span style=\"font-size:14px;color:darkgreen;\">$descr_layout</span>";
        if ($fonteLayout == 'TESTOBASE') {
            Out::hide($this->nameForm . "_btnDelLayout");
        } else {
            Out::show($this->nameForm . "_btnDelLayout");
        }
        Out::html($this->nameForm . '_divMessaggio', $content);
    }

    public function setFormTitle($title) {
        Out::setDialogTitle($this->nameForm, $title);
    }

}

?>
