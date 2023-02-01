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

class itaDocument {

//    private $objInstance=null;
    private $content;
    private $headerContent;
    private $location;
    private $dictionary;
    private $returnCode;
    private $message;
    private $resourceFolder;
    private $fileListXML;
    private $fileHeaderHTM;
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

    private function loadResources($location="") {
        if (!$location) {
            $location = $this->location;
        }
        if ($location!='') {
            $fileInfo = pathinfo(realpath($location));
            $this->resourceFolder = $fileInfo['filename'] . "_file";
            $this->resources = $this->loadResourceList($fileInfo['dirname'] . '/' . $this->resourceFolder);
        } else {
            $this->setReturnCode("");
            $this->setMessage('Location non impostata.');
            return false;
        }
    }

    public function saveContent($file, $overwrite=false) {
        $exists = file_exists($file);
        if ($exists) {
            if ($overwrite == false) {
                $this->setReturnCode("");
                $this->setMessage('File già esistente.');
                return false;
            }
        }
        $newBaseName = pathinfo($file, PATHINFO_BASENAME);
        $newResourceFolder = pathinfo($file, PATHINFO_FILENAME) . "_file";
        $newResourcePath = pathinfo($file, PATHINFO_DIRNAME) . "/" . $newResourceFolder;
        if (!is_dir($newResourcePath)) {
            mkdir($newResourcePath, 0777, true);
        }
        $fileListXml = "
            <xml xmlns:o=\"urn:schemas-microsoft-com:office:office\">\r\n
                <o:MainFile HRef=\"../" . $newBaseName . "\"/>\r\n";
        foreach ($this->resources as $key => $resource) {
            $fileListXml .= "<o:File HRef=\"" . $resource['HREFNAME'] . "\"/>\r\n";
        }
        $fileListXml .= "<o:File HRef=\"filelist.xml\"/>\r\n";
        $fileListXml .= "<o:File HRef=\"header.htm\"/>\r\n";
        $fileListXml .= '</xml>';
        if (!$this->writeFile($newResourcePath . "/filelist.xml", $fileListXml)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura di filelist.xml.');
            return false;
        }
        if (file_exists($this->fileHeaderHTM)) {
            $fileHeaderHTM = $this->headerContent;//file_get_contents($this->fileHeaderHTM);
//            $fileHeaderHTM = file_get_contents($this->fileHeaderHTM);
            $fileHeaderHTM = str_replace(pathinfo($this->getLocation(), PATHINFO_BASENAME), $newBaseName, $fileHeaderHTM);
            if (!$this->writeFile($newResourcePath . "/header.htm", $fileHeaderHTM)) {
                $this->setReturnCode("");
                $this->setMessage('Errore nella scrittura di header.htm.');
                return false;
            }
        }
        foreach ($this->resources as $key => $resource) {
            @copy($resource['FILEPATH'], $newResourcePath . "/" . $resource['HREFNAME']);
        }

        $newContent = str_replace($this->resourceFolder, $newResourceFolder, $this->content);
        if (!$this->writeFile($file, $newContent)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione.');
            return false;
        }
        return true;
    }

    public function getHead() {
        $head = $this->content;
        $head = "hheeaadd";
        return $head;
    }

    public function getDocumentVars() {
        $data = array();
        preg_match_all('/\#\{\$(.*?)\}\#/is', $this->getContent(), $data, PREG_PATTERN_ORDER);
        unset($data[0]);
        return $data[1];
    }

    public function getPageBreak() {
        return '<span> <br clear="all" style="page-break-before:always"></span>';
    }

    public function getBody($includeTag=false) {
        $tagBody = array("<body", "</body>");
        $risultato = explode($tagBody[0], $this->content, 2);
        $bodyTesto = $tagBody[0] . $risultato[1];
        $risultato = explode($tagBody[1], $bodyTesto, 1);
        $bodyTesto = $risultato[0] . $tagBody[1];
        if ($includeTag == false) {
            $daPos = strpos($bodyTesto, "<body");
            $aPos = strpos($bodyTesto, ">");
            $bodyTesto = substr_replace($bodyTesto, '', $daPos, $aPos - $daPos + 1);
            $bodyTesto = str_replace("</body>", "", $bodyTesto);
            $bodyTesto = str_replace("</html>", "", $bodyTesto);
        }

        $bodyObj = new itaDocument();
        $bodyObj->setContent($bodyTesto);
        $bodyObj->setLocation($this->location);
        $bodyObj->resourceFolder = $this->resourceFolder;
        $bodyObj->setResources($this->resources);
        return $bodyObj;
    }

    private function addResources($objDoc) {
        foreach ($objDoc->getResources() as $key => $resource) {
            $oldResource = $resource['HREFNAME'];
            $newResource = md5(rand() * time()) . "." . pathinfo($oldResource, PATHINFO_EXTENSION);
            $objDoc->setContent(str_replace($objDoc->resourceFolder . "/" . $oldResource, $this->resourceFolder . "/" . $newResource, $objDoc->getContent()));
            $resource['HREFNAME'] = $newResource;
            $this->resources[] = $resource;
        }
    }

    public function appendToBody($toAppend) {
        $tagBody = "</body>";
        if (is_string($toAppend)) {
            $toAppendStr = $toAppend;
        } elseif (is_object($toAppend)) {
            $this->addResources($toAppend);
            $toAppendStr = $toAppend->getContent();
        }
        $risultato = explode($tagBody, $this->content);
        $this->content = $risultato[0] . $toAppendStr . $tagBody . $risultato[1];
        return true;
    }

    public function prependToBody($toPrepend) {
        if (is_string($toPrepend)) {
            $toPrependStr = $toPrepend;
        } elseif (is_object($toPrepend)) {
            $this->addResources($toPrepend);
            $toPrependStr = $toPrepend->getContent();
        }
        $daPos = strpos($this->content, "<body");
        $aPos = strpos($this->content, ">", $daPos);
        $tagBody = substr($this->content, $daPos, $aPos - $daPos + 1);
        $risultato = explode($tagBody, $this->content);
        $this->content = $risultato[0] . $tagBody . $toPrependStr . $risultato[1];
        return true;
    }

    public function mergeDictionary($primoGiro=true) {
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
        $itaSmarty = new itaSmarty();
        foreach ($this->dictionary as $key => $valore) {        
            $itaSmarty->assign($key, $valore);
        }
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.htm';
        if (!$this->writeFile($documentoTmp, $this->content)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione nella directory temporanea.');
            return false;
        }
        $this->content = $itaSmarty->fetch($documentoTmp);
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-headerTmp.htm';
        if (!$this->writeFile($documentoTmp, $this->headerContent)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione nella directory temporanea.');
            return false;
        }
        $this->headerContent = $itaSmarty->fetch($documentoTmp);
        $this->setMessage("");
        $this->setReturnCode("0");
        return true;
    }

    private function loadResourceList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListFile = array();
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            if (strtoupper($obj) == "FILELIST.XML") {
                $this->fileListXML = $filePath . '/' . $obj;
                continue;
            }

            if (strtoupper($obj) == "HEADER.HTM") {
                $this->fileHeaderHTM = $filePath . '/' . $obj;
                $this->headerContent = file_get_contents($this->fileHeaderHTM);
                continue;
            }

            $retListFile[] = array(
                    'PATHNAME' => $filePath,
                    'FILEPATH' => $filePath . '/' . $obj,
                    'FILENAME' => $obj,
                    'HREFNAME' => $obj
            );
        }
        closedir($dh);
        return $retListFile;
    }

    private function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!@fwrite($fpw, $string)) {
            fclose($fpw);
            $this->setMessage("Errore in scrittura file");
            $this->setReturnCode("0");
            return false;
        }
        fclose($fpw);
        return true;
    }

    public function ripulisciTmpFile() {
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.htm';
        @unlink($documentoTmp);
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-headerTmp.htm';
        @unlink($documentoTmp);
    }
}

?>
