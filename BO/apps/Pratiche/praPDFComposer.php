<?php

/**
 *
 *
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once (ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php');

function praPDFComposer() {
    $praPDFComposer = new praPDFComposer();
    $praPDFComposer->parseEvent();
    return;
}

class praPDFComposer extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $nameForm = "praPDFComposer";
    public $returnModel;
    public $returnEvent;
    public $returnId;
    public $PDFList;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->returnModel = App::$utente->getKey('praPDFComposer_returnModel');
        $this->returnEvent = App::$utente->getKey('praPDFComposer_returnEvent');
        $this->returnId = App::$utente->getKey('praPDFComposer_returnId');
        $this->PDFList = App::$utente->getKey('praPDFComposer_PDFList');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey('praPDFComposer_returnModel', $this->returnModel);
            App::$utente->setKey('praPDFComposer_returnEvent', $this->returnEvent);
            App::$utente->setKey('praPDFComposer_returnId', $this->returnId);
            App::$utente->setKey('praPDFComposer_PDFList', $this->PDFList);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->PDFList = array();
                if ($_POST['PDFList']) {
                    $this->PDFList = $_POST['PDFList'];
                }
                foreach ($this->PDFList as $key => $pdfItem) {
                    Out::html($this->nameForm . '_listSorgente', '<li id="PDFItem_' . $key . '" class="ita-bullet"><div style="display:inline-block;" class="ita-icon ita-icon-pdf-24x24"></div><div " style="height:100%;width:80%;margin-left:4px;vertical-align:middle;display:inline-block;"><span class="ita-Wordwrap">' . $pdfItem['FILEORIG'] . '</span></div></li>', 'append');
                }
                Out::html($this->nameForm . "_divMessaggio", "Trascina i pdf nel riquadro di Destra.<br>Esegui Salva o Anteprima per ottenere il tuo PDF.");

                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_anteprima': // Evento bottone Elenca> 
                        $output = $this->componiPdf();
                        if ($output) {
                            Out::openDocument(utiDownload::getUrl("anteprima.pdf", $output));
                        } else {
                            
                        }
                        break;
                    case $this->nameForm . '_componi': // Evento bottone Elenca>
                        $nomeComposer = pathinfo($_POST[$this->nameForm . "_nomeComposizione"], PATHINFO_FILENAME);
                        $extNomeComposer = strtolower(pathinfo($_POST[$this->nameForm . "_nomeComposizione"], PATHINFO_EXTENSION));
                        if (!$extNomeComposer) {
                            $extNomeComposer = 'pdf';
                        }
                        if ($extNomeComposer != 'pdf') {
                            Out::msgStop("Errore", "Tipo File: $extNomeComposer, non accettabile");
                            break;
                        }

                        $bad = '/[\/:*?"<>\\\|]/';
                        $nomeCorretto = preg_replace($bad, "", $nomeComposer);
                        if ($nomeComposer !== $nomeCorretto) {
                            Out::msgStop("Componi PDF", "Nome PDF: " . $nomeComposer . " non accettabile.");
                            break;
                        }
                        $output = $this->componiPdf();
                        if (!$output) {
                            break;
                        }

                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Archiviazione Composizione.", "Creazione ambiente di lavoro temporaneo fallita.");
                                break;
                            }
                        }
                        $uploadPath = itaLib::getPrivateUploadPath() . "/" . pathinfo($output, PATHINFO_BASENAME);
                        if (!@copy($output, $uploadPath)) {
                            Out::msgStop("Archiviazione Composizione.", "Salvataggio PDF fallito.");
                            break;
                        }
                        $model = $this->returnModel;
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['retid'] = $this->returnID;
                        $_POST["retFileComposer"] = $uploadPath;
                        $_POST["retNomeComposer"] = $nomeComposer . "." . $extNomeComposer;
                        $model();
                        $this->returnToParent();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey('praDipe_returnModel');
        App::$utente->removeKey('praDipe_returnEvent');
        App::$utente->removeKey('praDipe_returnId');
        itaLib::clearAppsTempPath('praPDFComposer');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function CreaXmlCatPdf($arInput, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"cat\">\r\n";
        $xml .= "       <inputs>\r\n";
        foreach ($arInput as $key => $input) {
            $xml .= "       <input>" . $input['FILEPATH'] . "</input>\r\n";
        }
        $xml .= "       </inputs>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function componiPdf() {
        $arInput = array();
        foreach ($_POST[$this->nameForm . "_listComponi"] as $key => $id) {
            $arInput[] = $this->PDFList[$id];
        }
        App::log("lista");
        App::log($_POST[$this->nameForm . "_listComponi"]);
        App::log($this->PDFList);
        $xmlPATH = itaLib::createAppsTempPath('praPDFComposer');
        $xmlFile = $xmlPATH . "/" . md5(rand() * time()) . ".xml";
        $xmlRes = fopen($xmlFile, "w");
        if (!file_exists($xmlFile)) {
            Out::msgStop("Componi PDF", "Errore in composizione PDF");
            return false;
        } else {
            $output = $xmlPATH . "/" . md5(rand() * time()) . ".pdf";
            //App::log($arInput);
            $xml = $this->CreaXmlCatPdf($arInput, $output);
            fwrite($xmlRes, $xml);
            fclose($xmlRes);
            $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFile;
            //App::log('$command');
            //App::log($command);
            exec($command, $ret);
//            App::log($ret);
//            App::log("Output");
//            App::log($output);
            //return false;
//            return $output;

            $taskXml = false;
            foreach ($ret as $value) {
                $arrayExec = explode("|", $value);
                if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                    $taskXml = true;
                    break;
                }
            }
            if ($taskXml == false) {
                return false;
            } else {
                return $output;
            }
        }
    }

}

?>