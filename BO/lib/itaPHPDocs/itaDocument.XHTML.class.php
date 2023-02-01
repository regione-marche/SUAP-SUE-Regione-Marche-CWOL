<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDocument
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');

class itaDocumentXHTML {

    private $content;
    private $location;
    private $dictionary;
    private $returnCode;
    private $message;
    private $resourceFolder;
    private $resources = array();

    /*
     * Getter & Setter
     */

//    function __construct() {
//        if ($this->objInstance==null){
//            $this->objInstance=rand(1, 999999);
//        }
//    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
    }

    public function getMessage() {
        return $this->message;
    }

    private function setMessage($message) {
        $this->message = $message;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    private function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getDictionary() {
        return $this->dictionary;
    }

    public function setDictionary($dictionary) {
        $this->dictionary = $dictionary;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    private function setResources($resources) {
        $this->resources = $resources;
    }

    private function getResources() {
        return $this->resources;
    }

    public function loadContent($file) {
        if (file_exists($file)) {
            $this->setContent(file_get_contents($file));
            $this->setLocation($file);
            $this->loadResources();
            return true;
        } else {
            $this->setReturnCode("");
            $this->setMessage('File documento non torvato.');
            return false;
        }
    }

    private function loadResources($location = "") {
        $this->resourceFolder = "";
        $this->resources = "";
    }

    public function saveContent($file, $overwrite = false) {
        $exists = file_exists($file);
        if ($exists) {
            if ($overwrite == false) {
                $this->setReturnCode("");
                $this->setMessage('File già esistente.');
                return false;
            }
        }
        if (!$this->writeFile($file, $this->content)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione.');
            return false;
        }
        return true;
    }

    public function getPageBreak() {
        return '<p style="page-break-after: always;"><!--pagebreak--></p>';
    }

    public function getAnnotations() {
        $pos1 = strpos($this->content, "<!-- itaTestoBase:");
        if ($pos1 !== false) {
            $pos2 = strpos(substr($this->content, $pos1), " -->");
            if ($pos2 !== false) {
                return substr($this->content, $pos1, $pos2 + 4);
            }
        }
        return '';
    }

    public function getBody($includeTag = false) {
        return str_replace($this->getAnnotations(), '', $this->content);
    }

    public function appendToBody($toAppend) {
        $this->setContent($this->getContent() . $toAppend);
        return true;
    }

    public function prependToBody($toPrepend) {
        $this->setContent($this->getAnnotations() . $toPrepend . $this->getBody());
        return true;
    }

    public function mergeDictionary() {
        $this->setMessage("");
        $this->setReturnCode("0");
        if (!$this->content) {
            $this->setMessage("Contenuto template non presente");
            $this->setReturnCode("1");
            return false;
        }
        if (!$this->dictionary) {
            $this->setMessage("Dizionario non presente");
            $this->setReturnCode("2");
            return false;
        }
        if (!$this->parseItaElements()) {
            App::log('errore parse');
        }
        $itaSmarty = new itaSmarty();
//        foreach ($this->dictionary as $key => $valore) {
//            $itaSmarty->assign($key, $valore);
//        }

        foreach ($this->dictionary as $key => $valore) {
            if (is_array($valore)) {
                foreach ($valore as $key1 => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key2 => $val) {
                            $value[$key2] = $this->normalizzaVariabili($val);
                        }
                    } else {
                        $valore[$key1] = $this->normalizzaVariabili($value);
                    }
                }
            } else {
                $valore = $this->normalizzaVariabili($valore);
            }
            $itaSmarty->assign($key, $valore);
        }

        $documentoTmp = itaLib::createAppsTempPath('mergeDictionary') . '/documentoTmp.xhtml';
        if (!$this->writeFile($documentoTmp, $this->getBody())) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione nella directory temporanea.');
            itaLib::deleteAppsTempPath('mergeDictionary');
            return false;
        }
        $this->setContent($this->getAnnotations() . $itaSmarty->fetch($documentoTmp));
        itaLib::deleteAppsTempPath('mergeDictionary');
        return true;
    }

    private function normalizzaVariabili($v) {
        $type = "text/plain";
        if (strpos(trim($v), 'Content-type: ') === 0) {
            list($header, $xx) = explode("\n", $v);
            list($skip, $type) = explode(": ", $header);
        }

        switch ($type) {
            case "text/html":
                list($skip, $v) = explode("Content-type: text/html\n", $v);
                return $v;
                break;
            case "text/plain":
            default:
                return htmlspecialchars($v, ENT_COMPAT, 'ISO-8859-1');
                break;
        }
    }

    private function parseItaElements() {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument;
//
// Documento template
//
        //$template = $this->getBody();
        $ret = $dom->loadXML($this->getBody());
        if ($ret === false) {
            App::log(libxml_get_errors());
            return false;
        }
//
// Estraggo tutte le tabelle dal template
//
        $tables = $dom->getElementsByTagName('table');
        foreach ($tables as $table) {
            if (!$table->getAttribute('class') == "ita-table-template") {
                continue;
            }
//
// Estraggo le righe della tabella
//
            $trs = $table->getElementsByTagName('tr');
            foreach ($trs as $tr) {
                if ($tr->getAttribute('class') == 'ita-table-header') {
                    continue;
                }

//
// Preparo i campi multipli in un array
//
                $newGrid = array();
                $tds = $tr->getElementsByTagName('td');
                foreach ($tds as $td) {
// Contenuto della cella
                    $tmpDOM = new DOMDocument();
                    $tmpDOM->appendChild($tmpDOM->importNode($td, TRUE));
                    $nodeValue = utf8_decode($tmpDOM->saveHTML());
                    $tmpDOM = null;
                    $xx = 0;
                    while (true) {
                        $xx += 1;
                        if ($xx == 1000) {
                            break;
                        }

// Parte da sostituire
                        $unit_inner = $this->extract_unit($nodeValue, "@{", "}@");
                        if (!$unit_inner) {
                            break;
                        }
                        $unit = "@{" . $unit_inner . "}@";
                        list($skip, $key0) = explode("$", $unit_inner);
                        list($key1, $key2) = explode(".", $key0);
//
// Trovo tutte le istanze multiple del campo
//
                        foreach ($this->dictionary[$key1] as $campo => $valueCampo) {
                            if (strpos($campo, $key2) !== false) {
                                list($skip, $idx) = explode($key2, $campo);
                                $newUnit = '@{$' . $key1 . "." . $key2 . $idx . '}@';
                                $newGrid[$idx][$unit] = $newUnit;
                            }
                        }
                        $nodeValue = str_replace($unit, "", $nodeValue);
                    }
                }
                $trCloned = $tr->cloneNode(TRUE);
                break;
            }

//
// Duplico le righe
//
            if ($removeTemplate) {
                //
                // Rimuovo il tr template non indicizzato
                //
                try {
                    $tr->parentNode->removeChild($tr);
                } catch (Exception $exc) {
                    ob_end_clean();
                    die($exc->getMessage());
                }
            }
            foreach ($newGrid as $key => $newRow) {
                if (!$key) {
                    continue;
                }
                //
                // Prendo la riga base da duplicare
                //
                $tmpDOM = new DOMDocument();
                $tmpDOM->appendChild($tmpDOM->importNode($trCloned, TRUE));
                $stringTR = utf8_decode($tmpDOM->saveHTML());
                $tmpDOM = null;

                foreach ($newRow as $unit => $value) {
                    $stringTR = str_replace($unit, $value, $stringTR);
                }
                $tmpDOM = new DOMDocument();
                $tmpDOM->loadHTML($stringTR);
                $trNode = $tmpDOM->getElementsByTagName('tr')->item(0);
                $tbody = $table->getElementsByTagName('tbody')->item(0);

                $tbody->appendChild($dom->importNode($trNode, TRUE));
            }
        }

        $domTemplate = $dom->getElementsByTagName('html')->item(0);
        $tmpDOM = new DOMDocument();
        $tmpDOM->appendChild($tmpDOM->importNode($domTemplate, TRUE));
        $xmTemplate = $tmpDOM->getElementsByTagName('html')->item(0);
        $xhtmDocStringPhase2 = '<?xml version="1.0" encoding="UTF-8"?>';
        $xhtmDocStringPhase2 .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $xhtmDocStringPhase2 .= utf8_decode($tmpDOM->saveXML($xmTemplate));

        $this->setContent($this->getAnnotations() . $xhtmDocStringPhase2);
        return true;
    }

    private function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (@fwrite($fpw, $string) === false) {
            fclose($fpw);
            $this->setMessage("Errore in scrittura file");
            $this->setReturnCode("0");
            return false;
        }
        fclose($fpw);
        return true;
    }

    public function ripulisciTmpFile() {
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.xhtml';
        @unlink($documentoTmp);
    }

    public function xhtml2Pdf($outpufile = '') {
        if (!$this->getContent()) {
            return false;
        }
        //
        // recupero il testo base di provenienza
        //

        $tmpArr = explode("<!-- itaTestoBase:", $this->getAnnotations());
        $tmpStr = $tmpArr[1];
        $tmpArr = explode(" -->", $tmpStr);
        $testoBase = $tmpArr[0];
        $documenti_rec = $this->getDocumenti($testoBase);
        $documenti_rec['CONTENT'] = $this->getContent();
        $unserMetadata = unserialize($documenti_rec['METADATI']);
        if ($unserMetadata['MODELLOXHTML'] == 'PERSONALIZZATO') {
            $headerContent = $unserMetadata['HEADERCONTENT'];
            $footerContent = $unserMetadata['FOOTERCONTENT'];
            $orientation = $unserMetadata['ORIENTATION'];
            $format = $unserMetadata['FORMAT'];
            $marginTop = $unserMetadata['MARGIN-TOP'] + $unserMetadata['MARGIN-HEADER'];
            $marginHeader = $unserMetadata['MARGIN-HEADER'];
            $marginLeft = $unserMetadata['MARGIN-LEFT'];
            $marginRight = $unserMetadata['MARGIN-RIGHT'];
            $marginBottom = $unserMetadata['MARGIN-BOTTOM'] + $unserMetadata['MARGIN-FOOTER'];
            $marginFooter = $unserMetadata['MARGIN-FOOTER'];
            if ($orientation == "O") {
                $orientation = "landscape";
            } else if ($orientation == "V") {
                $orientation = "portrait";
            }
        } else {
            $codiceLayout = $unserMetadata['MODELLOXHTML'];
            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CODICE = '$codiceLayout' AND TIPO = 'XLAYOUT'";
            $Doc_documenti_rec = ItaDB::DBSQLSelect($this->ITALWEB, $sql, False);
            $unserContent = unserialize($Doc_documenti_rec['CONTENT']);
            $metadatiLayout = unserialize($Doc_documenti_rec['METADATI']);
            if ($metadatiLayout) {
                $headerContent = $unserContent['XHTML_HEADER'];
                $footerContent = $unserContent['XHTML_FOOTER'];
                $orientation = $metadatiLayout['ORIENTATION'];
                $format = $metadatiLayout['FORMAT'];
                $marginTop = $metadatiLayout['MARGIN-TOP'] + $metadatiLayout['MARGIN-HEADER'];
                $marginHeader = $metadatiLayout['MARGIN-HEADER'];
                $marginLeft = $metadatiLayout['MARGIN-LEFT'];
                $marginRight = $metadatiLayout['MARGIN-RIGHT'];
                $marginBottom = $metadatiLayout['MARGIN-BOTTOM'] + $metadatiLayout['MARGIN-FOOTER'];
                $marginFooter = $metadatiLayout['MARGIN-FOOTER'];
                if ($orientation == "O") {
                    $orientation = "landscape";
                } else if ($orientation == "V") {
                    $orientation = "portrait";
                }
            }
        }
    }

    public function getXHTLM() {

        /// qui smarty header footer content


        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $this->getContent());
        $itaSmarty->assign('documentheader', $this->headerContent);
        $itaSmarty->assign('documentfooter', $this->footerContent);
        $itaSmarty->assign('headerHeight', $this->marginHeader);
        $itaSmarty->assign('footerHeight', $this->marginFooter);
        $itaSmarty->assign('marginTop', $this->marginTop);
        $itaSmarty->assign('marginBottom', $this->marginBottom);
        $itaSmarty->assign('marginLeft', $this->marginLeft);
        $itaSmarty->assign('marginRight', $this->marginRight);
        $itaSmarty->assign('pageFormat', $this->format);
        $itaSmarty->assign('pageOrientation', $this->orientation);

        $layoutTemplate = dirname(__FILE__) . "/layoutTemplate.xhtml";
        //$contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
        $contentPreview = $itaSmarty->fetch($layoutTemplate);


        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
        if (!file_put_contents($documentPreview, $contentPreview)) {
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return false;
        }
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            if (is_array($valore)) {
                foreach ($valore as $key1 => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key2 => $val) {
                            $value[$key2] = htmlspecialchars($val);
                        }
                    } else {
                        $valore[$key1] = htmlspecialchars($value);
                    }
                }
            } else {
                htmlspecialchars($valore);
            }
            $itaSmarty->assign($key, $valore);
        }
//        foreach ($dictionaryValues as $key => $valore) {
//            if (is_array($valore)) {
//                foreach ($valore as $key1 => $value) {
//                    $valore[$key1] = htmlspecialchars($value);
//                }
//            } else {
//                $this->normalizzaVariabili($valore);
//            }
//            $itaSmarty->assign($key, $valore);
//        }
        $contentPreview2 = utf8_encode($itaSmarty->fetch($documentPreview));
        $documentPreview2 = itaLib::getAppsTempPath() . '/documentpreview2.xhtml';
        $pdfPreview = itaLib::getAppsTempPath() . '/documentpreview.pdf';
        if (!file_put_contents($documentPreview2, $contentPreview2)) {
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return false;
        }

        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $documentPreview2 . ' ' . $pdfPreview;
        passthru($command, $return_var);
    }

    private function extract_unit($string, $start, $end) {
        $pos = stripos($string, $start);
        $str = substr($string, $pos);
        $str_two = substr($str, strlen($start));
        $second_pos = stripos($str_two, $end);
        $str_three = substr($str_two, 0, $second_pos);
        $unit = trim($str_three); // remove whitespaces
        return $unit;
    }

}

?>
