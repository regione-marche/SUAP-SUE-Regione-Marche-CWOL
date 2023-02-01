<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDocumentDOCX
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
require_once(ITA_LIB_PATH . '/zip/itaZip.class.php');

class itaDocumentDOCX {

    private $content;
    private $style;
    private $headers = array();
    private $footers = array();
    private $location;
    private $dictionary;
    private $fileListXML;
    private $fileHeaderHTM;
    private $namespace;
    private $tempPath;
    private $sectPr;
    private $returnCode;
    private $message;
    private $uniqueId;
    private $embedded = array();
    private $contentTypes = array();
    private $xmlns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /*
     * Getter & Setter
     */

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
        $this->dictionary = $this->normalizzaVariabiliRaccoltaMultipla($dictionary);
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getStyle() {
        return $this->style;
    }

    public function setStyle($style) {
        $this->style = $style;
    }

    public function close() {
        
    }

    private function getTempPath($directory, $clear = false) {
        $location = rtrim(ITA_FRONTOFFICE_TEMP, '/\\') . DIRECTORY_SEPARATOR . $directory;

        if (!file_exists($location)) {
            if (!mkdir($location, 0755, true)) {
                $this->setReturnCode(-1);
                $this->setMessage('Errore durante la creazione della cartella di lavoro temporanea.');
                return false;
            }
        } else {
            if (!is_dir($location)) {
                $this->setReturnCode(-1);
                $this->setMessage('La cartella di lavoro temporanea non è valida.');
                return false;
            }
        }

        return $location;
    }

    public function loadContent($file) {
        if (!file_exists($file)) {
            $this->setReturnCode(-1);
            $this->setMessage('Documento non trovato.');
            return false;
        }
        $this->basename = basename($file);
        $this->uniqueId = hash('md5', pathinfo($file, PATHINFO_FILENAME) . round(microtime(true) * 1000));

        $directory = 'docx-' . pathinfo($file, PATHINFO_FILENAME) . '-unpacked-' . $this->uniqueId;

        $location = $this->getTempPath($directory, true);

        if (!itaZip::Unzip($file, $location)) {
            $this->setReturnCode(-1);
            $this->setMessage('Documento non valido.');
            return false;
        }

        $this->setLocation($location);

        $this->readXMLContentTypes();

        $content = $this->readXMLBody($this->getLocation() . '/word/document.xml');
        if (!$content) {
            return false;
        }

        foreach (glob($this->getLocation() . '/word/header*.xml') as $header) {
            $this->headers[$header] = $this->readXMLBody($header);
            if (!$this->headers[$header]) {
                return false;
            }
        }

        foreach (glob($this->getLocation() . '/word/footer*.xml') as $footer) {
            $this->footers[$footer] = $this->readXMLBody($footer);
            if (!$this->footers[$footer]) {
                return false;
            }
        }

        $this->setContent($content);

        $style = $this->readXMLStyle();
        if (!$style) {
            return false;
        }

        $this->setStyle($style);

        return true;
    }

    private function readXMLEmbedded($filename) {
        $filepath = $this->location . "/word/_rels/$filename.rels";

        if (!file_exists($filepath)) {
            file_put_contents($filepath, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        }

        $xmlRels = simplexml_load_file($filepath);

        if ($xmlRels) {
            // Includo tutti gli id di riferimento presenti
            foreach ($xmlRels->Relationship as $relationship) {
                $this->embedded[$filename][(string) $relationship['Id']] = true;
            }
        }

        return $xmlRels;
    }

    private function readXMLContentTypes() {
        $xmlTypes = simplexml_load_file($this->location . '/[Content_Types].xml');

        if ($xmlTypes) {
            // Leggo tutti i Content-Type
            foreach ($xmlTypes->Default as $default) {
                $this->contentTypes[(string) $default['Extension']] = (string) $default['ContentType'];
            }
        }
    }

    private function readXMLBody($xmlfile) {
        $reader = new XMLReader();
        $filename = basename($xmlfile);
        $isMainDocument = $filename === 'document.xml';

        if (!$reader->open($xmlfile)) {
            $this->setReturnCode(-1);
            $this->setMessage("Impossibile aprire il documento '$filename'.");
            return false;
        }

        $xmlRels = $this->readXMLEmbedded($filename);

        $str = '';

        while ($reader->read()) {
            if ($isMainDocument && $reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'body') {
                // Cerco la sezione body, e ne estratto anche il namespace
                $this->namespace = substr(str_replace($reader->localName, '', $reader->name), 0, -1);
                $str = $reader->readInnerXml();
                continue;
            }

            if ($isMainDocument && $reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'sectPr' && $reader->depth === 2) {
                // Cerco la sezione (tag sectPr) che determina header + footer (tag sectPr di sezione)
                $this->sectPr = $reader->readOuterXml();
                continue;
            }

            if ($xmlRels && $reader->nodeType == XMLReader::ELEMENT && $reader->getAttribute('r:embed')) {
                foreach ($xmlRels->Relationship as $relationship) {
                    if ($relationship['Id'] == $reader->getAttribute('r:embed')) {
                        $relationship->addAttribute('Content', base64_encode(file_get_contents($this->location . '/word/' . $relationship['Target'])));
                        $this->embedded[$filename][(string) $relationship['Id']] = $relationship;
                    }
                }
                continue;
            }
        }

        $reader->close();

        if ($isMainDocument && !$str && !$this->namespace) {
            $this->setReturnCode(-1);
            $this->setMessage("Impossibile leggere il documento '$filename'.");
            return false;
        }

        if ($isMainDocument) {
            // La rimuovo dalla stringa lasciando solo il contenuto
            $str = str_replace($this->sectPr, '', $str);

            // Clear delle variabili
            if (!($str = $this->cleanXML($str))) {
                return false;
            }
        } else {
            $str = file_get_contents($xmlfile);

            // Clear delle variabili
            if (!($str = $this->cleanXML($str, true))) {
                return false;
            }
        }

        return $str;
    }

    /**
     * Pulisce gli XML (unendo le variabili) ed inserisce gli stili univoci
     */
    private function cleanXML($xml, $have_root = false) {
        if ($have_root) {
            /* @var $dom DOMDocument */
            $dom = DOMDocument::loadXML($xml);
        } else {
            // Provo includendo un elemento root
            $dom = DOMDocument::loadXML('<root>' . $xml . '</root>');
        }

        if (!$dom) {
            $this->setReturnCode(-1);
            $this->setMessage('Impossibile leggere l\'xml. (cleanXML)');
            return false;
        }

        /*
         * I nodi al primo livello dell'XML da pulire
         */
        $elements = $dom->childNodes->item(0)->childNodes;

        /*
         * Patterns da utilizzare per la pulizia
         */
        $match_patterns = array(
            // @{$___}@, $___.$, @{if___}@, @{else}@, @{/if}@
            'preg'  => array('/@{\$(.*?)}@/ms', '/\$(.*?)\.\$/ms', '/@{if(.*?)}@/ms', '/@{else}@/ms', '/@{\/if}@/ms', '/@{foreach(.*?)}@/ms', '/@{\/foreach}@/ms'),
            'open'  => array('@{$'            , '$'              , '@{if'           , '@{else'      , '@{/if'       , '@{foreach'           , '@{/foreach'       ),
            'close' => array('}@'             , '.$'             , '}@'             , '}@'          , '}@'          , '}@'                  , '}@'               )
//            'preg' => array('/@{\$(.*?)}@/', '/\$(.*?)\.\$/'),
//            'open' => array('@{$', '$'),
//            'close' => array('}@', '.$')
        );

        /*
         * Scorro i nodi selezionati e prendo solo quelli che contegono almeno un pattern di sostituzione
         */

        /* @var $element DOMNode */
        foreach ($elements as $element) {
            $found = false;
            /*
             * La preg match utilizza i modificatori "m" ed "s":
             * - m: multilinea, cattura testo anche su più linee
             * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
             *      cattura anche i caratteri newline (omessi senza il modificatore)
             */
            foreach ($match_patterns['preg'] as $preg_match) {
                if (preg_match($preg_match, $element->nodeValue)) {
                    $found = true;
                }
            }

            if ($found) {
                $matched_string = '';
                $matched_run = false;
                $searching_closure = false;
                $matched_pattern_index = false;
                $searching_pattern_index = 0;
                $to_remove = array();
                $to_remove_confirmed = array();

                /*
                 * Estraggo i tag r (run DOCX), scorro il loro contenuto e verifico aperture e chiusure delle variabili da sostituire
                 * in modo da accorpare le variabili spezzate su più run in un'unica run. 
                 */
                $runs = $this->getElementsByTagNameFromNode($element, 'r');

                /* @var $run DOMNode */
                foreach ($runs as $run) {
                    /*
                     * Resetto la variabile $same_run per ricerche su varie run
                     */
                    $same_run = false;
                    
                    $text = $run->nodeValue;

                    foreach (str_split($text) as $charpos => $char) {
                        if (!$searching_closure) {
                            /*
                             * Cerco apertura tag
                             */

                            /*
                             * Se è già stato trovato parte di un pattern di apertura...
                             */
                            if ($matched_pattern_index !== false) {

                                $found_next_char = false;

                                /*
                                 * Per ogni pattern di apertura
                                 */
                                foreach ($match_patterns['open'] as $mi => $open_tag) {

                                    /*
                                     * ... controllo che il carattere successivo sia quello corrispondente
                                     */
                                    if ($char == $open_tag[$searching_pattern_index]) {
                                        /*
                                         * Se corrisponde proseguo nella ricerca del pattern
                                         */
                                        $found_next_char = true;
                                        $searching_pattern_index++;

                                        /*
                                         * Se il pattern è stato trovato interamente...
                                         */
                                        if ($searching_pattern_index >= strlen($open_tag)) {
                                            /*
                                             * Inizio la ricerca della chiusura del pattern
                                             */
                                            $matched_pattern_index = $mi;
                                            $searching_pattern_index = 0;
                                            $searching_closure = true;
                                        }

                                        /*
                                         * Esco dal foreach
                                         */
                                        break;
                                    }
                                }

                                if (!$found_next_char) {
                                    /*
                                     * Se il carattere successivo non corrisponde a nessun pattern,
                                     * resetto le variabili per ricominciare la ricerca dal primo
                                     * carattere dei pattern
                                     */
                                    $matched_pattern_index = false;
                                    $searching_pattern_index = 0;
                                    $to_remove = array();
                                }
                            }

                            /*
                             * Se sto ancora cercando il primo char di un pattern di apertura
                             */
                            if ($matched_pattern_index === false) {
                                /*
                                 * Per ogni pattern di apertura
                                 */
                                foreach ($match_patterns['open'] as $mi => $open_tag) {
                                    /*
                                     * Se il primo carattere di apertura corrisponde con il carattere parsato...
                                     */
                                    if ($char == $open_tag[$searching_pattern_index]) {
                                        /*
                                         * ... valorizzo le variabili per continuare la ricerca del pattern
                                         */
                                        $matched_pattern_index = true;
                                        $searching_pattern_index++;
                                        $matched_run = $run;
                                        $same_run = true;

                                        /*
                                         * Se il pattern è di solo un carattere, passo alla ricerca della chiusura
                                         */
                                        if ($searching_pattern_index >= strlen($open_tag)) {
                                            $searching_pattern_index = 0;
                                            $searching_closure = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            // Cerco chiusura tag
                            if ($char == $match_patterns['close'][$matched_pattern_index][$searching_pattern_index]) {
                                $searching_pattern_index++;
                                if ($searching_pattern_index >= strlen($match_patterns['close'][$matched_pattern_index])) {

                                    if (!$same_run) {
                                        $matched_string .= substr($text, 0, $charpos + 1);

                                        /*
                                         * Salvo nel nodo t (text) della run su cui riaccorpare la variabile ricostruita
                                         */
                                        /* @var $node DOMNode */
                                        foreach ($matched_run->childNodes as $node) {
                                            if ($node->localName == 't') {
                                                $cdata = $dom->createTextNode($matched_string);
                                                $node->nodeValue = '';
                                                $node->appendChild($cdata);
                                            }
                                        }

                                        /*
                                         * Salvo, nel nodo t (text) della run da dove ho preso la parte riaccorpata, il residuo di testo
                                         */
                                        foreach ($run->childNodes as $node) {
                                            if ($node->localName == 't') {
                                                /*
                                                 *  Previene alcuni problemi dato dagli spazi
                                                 */
                                                $node->setAttribute('xml:space', 'preserve');
                                                $node->nodeValue = substr($text, $charpos + 1);

                                                /*
                                                 * Carlo, fix aggiunto in data 6.10.15
                                                 * Aggiorna anche il valore di $text su cui si sta lavorando al momento 
                                                 */
                                                $text = $run->nodeValue;
                                            }
                                        }

                                        /*
                                         * Salvo le run intermedie (con delle parti di variabile) che ho accorpato
                                         * per la successiva rimozione
                                         */
                                        foreach ($to_remove as $node) {
                                            array_push($to_remove_confirmed, $node);
                                        }
                                    }

                                    $searching_closure = false;
                                    $matched_pattern_index = false;
                                    $searching_pattern_index = 0;
                                    $matched_run = false;
                                    $matched_string = '';
                                    $to_remove = array();
                                }
                            } else {
                                $searching_pattern_index = 0;
                            }
                        }
                    }

                    if ($matched_pattern_index !== false) {
                        $matched_string .= $text;

                        if (!$same_run) {
                            array_push($to_remove, $run);
                        }
                    }
                }

                /*
                 * Rimuovo le run intermedie (con delle parti di variabile) che ho accorpato
                 */
                foreach ($to_remove_confirmed as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        /* Ricerca e modifica degli stili */
        $styles = array();

        // Non è possibile fare il merge perché sono oggetti e non array
        /*
         * Tabelle
         */
        foreach ($dom->getElementsByTagName('tblStyle') as $t) {
            array_push($styles, $t);
        }
        /*
         * Paragrafo
         */
        foreach ($dom->getElementsByTagName('pStyle') as $t) {
            array_push($styles, $t);
        }
        /*
         * Run
         */
        foreach ($dom->getElementsByTagName('rStyle') as $t) {
            array_push($styles, $t);
        }

        foreach ($styles as $style) {
            $style->setAttribute($this->namespace . ':val', $style->getAttribute($this->namespace . ':val') . '_' . $this->uniqueId);
        }

        if ($have_root) {
            return $dom->saveXML();
        } else {
            // Ritorno escludendo l'elemento root
            return $this->extractTag($dom, 'root');
//            return $this->regexTagMatch($dom->saveXML(), 'root');
        }
    }

    private function readXMLStyle() {
        $reader = new XMLReader();

        if (!$reader->open($this->location . '/word/styles.xml')) {
            $this->setReturnCode(-1);
            $this->setMessage('Impossibile leggere il file di stile.');
            return false;
        }

        $str = '';

        $regsub1 = '${1}${2}_' . $this->uniqueId . '${3}';
        $regsub2 = '${1}${2} ' . $this->uniqueId . '${3}';

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'style') {
                // Sostituisco gli id ed i riferimenti con valori univoci
                /*
                 * La preg match utilizza i modificatori "m" ed "s":
                 * - m: multilinea, cattura testo anche su più linee
                 * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
                 *      cattura anche i caratteri newline (omessi senza il modificatore)
                 */
                $str .= preg_replace(array(
                    '/(' . $this->namespace . ':styleId=")(.*?)(")/ms',
                    '/(' . $this->namespace . ':name ' . $this->namespace . ':val=")(.*?)(")/ms',
                    '/(' . $this->namespace . ':basedOn ' . $this->namespace . ':val=")(.*?)(")/ms',
                    '/(' . $this->namespace . ':link ' . $this->namespace . ':val=")(.*?)(")/ms'
                    ), array($regsub1, $regsub2, $regsub1, $regsub1), $reader->readOuterXml());
            }
        }

        $reader->close();

        /* Rimuovo l'indicatore di stile primario (qFormat) e rimuovo il valore
         * default che applica automaticamente uno stile */
        /*
         * La preg match utilizza i modificatori "m" ed "s":
         * - m: multilinea, cattura testo anche su più linee
         * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
         *      cattura anche i caratteri newline (omessi senza il modificatore)
         */
        return preg_replace(array(
            '/<' . $this->namespace . ':qFormat\/>/ms',
            '/' . $this->namespace . ':default=".*?"/ms'
            ), '', $str);
    }

    private function writeXMLBody() {
        $txt = file_get_contents($this->getLocation() . '/word/document.xml');

        // Riscrivo l'xml
        $xml = substr($txt, 0, strpos($txt, $this->namespace . ':body>') + strlen($this->namespace . ':body>'));
        $xml .= $this->content;
        $xml .= $this->sectPr;
        $xml .= '</' . $this->namespace . ':body>';
        $xml .= '</' . $this->namespace . ':document>';

        file_put_contents($this->getLocation() . '/word/document.xml', $xml);

        foreach ($this->headers as $k => $header) {
            file_put_contents($k, $header);
        }

        foreach ($this->footers as $k => $footer) {
            file_put_contents($k, $footer);
        }

        $styles = file_get_contents($this->getLocation() . '/word/styles.xml');
        // Appendo gli stili personalizzati
        file_put_contents($this->getLocation() . '/word/styles.xml', str_replace('</' . $this->namespace . ':styles>', $this->style . '</' . $this->namespace . ':styles>', $styles));

        // Elaboro le relationships
        foreach ($this->embedded as $filename => $embedded) {
            $xmlRels = file_get_contents($this->location . "/word/_rels/$filename.rels");

            if ($xmlRels) {
                foreach ($embedded as $id => $xmlelement) {
                    if (strpos($xmlRels, $id) === false) {
                        if (file_exists($this->location . '/word/' . (string) $xmlelement['Target'])) {
                            $xmlelement['Target'] = $this->getNextFilename($this->location . '/word/' . (string) $xmlelement['Target']);
                        }

                        $relationshipXml = '<Relationship Id="' . $id . '" Type="' . (string) $xmlelement['Type'] . '" Target="' . (string) $xmlelement['Target'] . '"/>';
                        $xmlRels = str_replace('</Relationships>', $relationshipXml . '</Relationships>', $xmlRels);
                        @mkdir(dirname($this->location . '/word/' . (string) $xmlelement['Target']), 0777, true);
                        file_put_contents($this->location . '/word/' . (string) $xmlelement['Target'], base64_decode($xmlelement['Content']));
                    } else if ($xmlelement['Content'] && $xmlelement['Target']) {
                        file_put_contents($this->location . '/word/' . (string) $xmlelement['Target'], base64_decode($xmlelement['Content']));
                    }
                }

                file_put_contents($this->location . "/word/_rels/$filename.rels", $xmlRels);
            }
        }

        // Elaboro i Content-Types
        $xmlTypes = file_get_contents($this->location . '/[Content_Types].xml');
        if ($xmlTypes) {
            foreach ($this->contentTypes as $extension => $contentType) {
                if (strpos($xmlTypes, "Extension=\"$extension\"") === false) {
                    $defaultXml = "<Default Extension=\"$extension\" ContentType=\"$contentType\"/>";
                    $xmlTypes = str_replace('</Types>', $defaultXml . '</Types>', $xmlTypes);
                }
            }

            file_put_contents($this->location . '/[Content_Types].xml', $xmlTypes);
        }

        return true;
    }

    private function getNextFilename($filepath) {
        $pathinfo = pathinfo($filepath);

        if (preg_match('/(.*?)(\d+)$/', $pathinfo['filename'], $matches)) {
            $f = $matches[1];
            $n = (int) $matches[2];
        } else {
            $f = $pathinfo['filename'];
            $n = 1;
        }

        while (file_exists($pathinfo['dirname'] . '/' . $f . $n . '.' . $pathinfo['extension'])) {
            $n++;
        }

        return $f . $n . '.' . $pathinfo['extension'];
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

        if (!$this->writeXMLBody()) {
            return false;
        }

        itaZip::zipRecursive('', $this->getLocation(), $file, 'zip', false, false);
        return true;
    }

    public function getPageBreak() {
        return array(
            'content' => "<{$this->namespace}:p xmlns:{$this->namespace}=\"" . $this->xmlns . "\"><{$this->namespace}:r><{$this->namespace}:br {$this->namespace}:type=\"page\"/></{$this->namespace}:r></{$this->namespace}:p>",
            'style' => '',
            'embedded' => array(),
            'contentTypes' => array()
        );
    }

    public function getBody() {
        return array(
            'content' => $this->content,
            'style' => $this->style,
            'embedded' => $this->embedded,
            'contentTypes' => $this->contentTypes
        );
    }

    private function addEmbeddedImage($src, $relsname = 'document.xml') {
        $fn = basename($src);
        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $nid = 'rId' . (count($this->embedded[$relsname]) + 1);

        if (!isset($this->contentTypes[$ext])) {
            $this->contentTypes[$ext] = itaMimeTypeUtils::getMimeTypes($ext);
        }

        $this->embedded[$relsname][$nid] = array(
            'Type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image',
            'Target' => "media/$fn",
            'Content' => base64_encode(file_get_contents($src))
        );

        return $nid;
    }

    private function addEmbeddedBase64($base64, $filename, $relsname = 'document.xml') {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $nid = 'rId' . (count($this->embedded[$relsname]) + 1);

        if (!isset($this->contentTypes[$ext])) {
            $this->contentTypes[$ext] = itaMimeTypeUtils::getMimeTypes($ext);
        }

        $this->embedded[$relsname][$nid] = array(
            'Type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image',
            'Target' => "media/$filename",
            'Content' => $base64
        );

        return $nid;
    }

    private function getEmbeddedImageCode($rId, $x = false, $y = false, $relsname = 'document.xml') {
        if (!$x && !$y) {
            $is = getimagesizefromstring(base64_decode($this->embedded[$relsname][$rId]['Content']));
            $x = $is[0] * 5000;
            $y = $is[1] * 5000;
        }

        $fn = basename($this->embedded[$relsname][$rId]['Target']);
        $id = substr($rId, 3);

        $xml = <<<XML
<w:drawing>
    <wp:inline distT="0" distB="0" distL="0" distR="0">
        <wp:extent cx="$x" cy="$y"/>
        <wp:docPr id="$id" name="$fn" descr="$fn"/>
        <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
                <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
                    <pic:nvPicPr><pic:cNvPr id="$id" name="$fn" descr="$fn"/><pic:cNvPicPr/></pic:nvPicPr>
                    <pic:blipFill><a:blip r:embed="$rId"/><a:srcRect/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>
                    <pic:spPr>
                        <a:xfrm><a:off x="0" y="0"/><a:ext cx="$x" cy="$y"/></a:xfrm>
                        <a:prstGeom prst="rect"><a:avLst/></a:prstGeom>
                    </pic:spPr>
                </pic:pic>
            </a:graphicData>
        </a:graphic>
    </wp:inline>
</w:drawing>
XML;

        return $xml;
    }

    private function incorporateEmbedded(&$content, $embedded, $relsname = 'document.xml') {
        $nextid = count($this->embedded[$relsname]) + 1;

        foreach ($embedded as $partialEmbedded) {
            foreach ($partialEmbedded as $id => $xmlelement) {
                if ($xmlelement === true)
                    continue;

                $xmlelement['Id'] = 'rId' . ($nextid++);
                $this->embedded[$relsname][(string) $xmlelement['Id']] = $xmlelement;

                $content = str_replace($id, $xmlelement['Id'], $content);
            }
        }
    }

    public function appendToBody($toAppend) {
        $this->incorporateEmbedded($toAppend['content'], $toAppend['embedded']);
        $this->setContent($this->getContent() . $toAppend['content']);
        $this->style .= $toAppend['style'];
        $this->contentTypes = array_merge($this->contentTypes, $toAppend['contentTypes']);
        return true;
    }

    public function prependToBody($toPrepend) {
        $this->incorporateEmbedded($toPrepend['content'], $toPrepend['embedded']);
        $this->setContent($toPrepend['content'] . $this->getContent());
        $this->style .= $toPrepend['style'];
        $this->contentTypes = array_merge($this->contentTypes, $toPrepend['contentTypes']);
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

        if (!is_array($this->dictionary)) {
            $this->setMessage("Dizionario non presente");
            $this->setReturnCode("2");
            return false;
        }

        $this->content = $this->elaboraTabelle($this->content);

        if (!$this->content) {
            return false;
        }

        $this->content = $this->sostituisciVariabili($this->content);

        if (!$this->content) {
            return false;
        }

        foreach ($this->headers as $k => $header) {
            if (!$this->headers[$k] = $this->sostituisciVariabili($header, basename($k))) {
                return false;
            }
        }

        foreach ($this->footers as $k => $footer) {
            if (!$this->footers[$k] = $this->sostituisciVariabili($footer, basename($k))) {
                return false;
            }
        }

        return true;
    }

    public function fillFormData() {
        $this->formMissingVars = array();

        preg_match_all('/<w:ffData>(.*?)<\/w:ffData>/ms', $this->content, $matches);

        foreach ($matches[1] as $k => $match) {
            preg_match('/<w:name w:val="(.*?)"\/>/', $match, $name_match);
            preg_match('/<w:default w:val="(.*?)"\/>/', $match, $default_match);

            $name = $name_match[1];
            $default = $default_match[1];
            $type = strpos($match, 'checkBox') === false ? 'text' : 'checkbox';

            $realname = $type === 'checkbox' ? $name : $default;

            if (!isset($this->dictionary[strtoupper($realname)])) {
                $this->formMissingVars[] = $name;
                continue;
            }

            $replace_with = $this->dictionary[strtoupper($realname)];
            $replace_with = preg_replace('/\s+/', ' ', $replace_with);
            if (strpos($replace_with, ' ') !== false) {
                $replace_with = str_replace(' ', '}@ @{', $replace_with);
            }

            if (!$replace_with) {
                continue;
            }

            switch ($type) {
                case 'text':
                    $this->content = str_replace($default . '</w:t>', '@{' . $replace_with . '}@</w:t>', $this->content);
                    break;

                case'checkbox':
                    $xml_block = $matches[0][$k];
                    $xml_to_replace = str_replace($default_match[0], '<w:default w:val="@{' . $replace_with . '}@"/>', $xml_block);
                    $this->content = str_replace($xml_block, $xml_to_replace, $this->content);
                    break;
            }
        }

        return true;
    }

    public function getFormMissingVars() {
        return $this->formMissingVars ?: array();
    }

    public function elaboraTabelle($xml, $relsname = 'document.xml') {
        // Includo un elemento root o l'xml non sarebbe valido
        /* @var $dom DOMDocument */
        $dom = DOMDocument::loadXML('<root>' . $xml . '</root>');

        if (!$dom) {
            $this->setReturnCode(-1);
            $this->setMessage('Impossibile leggere l\'xml. (Elabora Tabelle)');
            return false;
        }

        /* @var $table DOMElement */
        foreach ($dom->getElementsByTagName('tbl') as $table) {
            $rows = $this->getElementsByTagNameFromNode($table, 'tr');

            /* @var $row DOMElement */
            foreach ($rows as $row) {

                // Prendo la variabili all'interno della riga
                /*
                 * La preg match utilizza i modificatori "m" ed "s":
                 * - m: multilinea, cattura testo anche su più linee
                 * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
                 *      cattura anche i caratteri newline (omessi senza il modificatore)
                 */
                preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_.]*)/', $row->textContent, $matches);

                $variables = array();
                $indici_presenti = array();

                // Verifico che almeno una delle variabili all'interno sia parte del dizionario
                foreach ($matches[1] as $match) {
                    $keys = explode('.', $match);
                    $check = $this->dictionary;

                    // Ricreo in $check la referenza del match ($dict[$keys[0]][$keys[1]]...)
                    foreach ($keys as $key) {
                        $check = isset($check[$key]) ? $check[$key] : false;
                    }

                    // Se la chiave a cui punta è un array...
                    if (is_array($check)) {
                        // ... mi salvo la chiave e il numero di record
                        array_push($variables, $match);
                        $indici_presenti = array_unique(array_merge($indici_presenti, array_keys($check)));
                    }
                }

                sort($indici_presenti);
                $variables = array_unique($variables);

                if (count($indici_presenti)) {
                    foreach ($indici_presenti as $i) {
                        /* @var $new_row DOMElement */
                        $new_row = $row->parentNode->insertBefore($row->cloneNode(true), $row);

                        /* @var $tc DOMElement */
                        foreach ($new_row->getElementsByTagName('tc') as $tc) {
                            foreach ($tc->childNodes as $tc_child) {
                                if ($tc_child->nodeType !== XML_ELEMENT_NODE) {
                                    continue;
                                }

                                /*
                                 * Aggiungo lo schema
                                 */
                                $tc_child->setAttribute('xmlns:w', $this->xmlns);
                            }

                            $tc_xml = $this->regexTagMatch($dom->saveXML($tc), 'w:tc');

                            /*
                             * Sostituisco i nomi delle variabili con "nome_variabile[$i]", dove i è il numero del record
                             */
                            /*
                             * La preg match utilizza i modificatori "m" ed "s":
                             * - m: multilinea, cattura testo anche su più linee
                             * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
                             *      cattura anche i caratteri newline (omessi senza il modificatore)
                             */
                            $tc_xml = preg_replace('/(' . implode('|', array_map('preg_quote', $variables, array_fill(0, count($variables), '/'))) . ')(?!_)/', '$1[' . $i . ']', $tc_xml);

                            $tc_xml = $this->sostituisciVariabili($tc_xml, $relsname);

                            /*
                             * Aggiungo lo schema
                             */
                            /* @var $dom_tmp DOMDocument */
                            $dom_tmp = DOMDocument::loadXML('<root xmlns:w="' . $this->xmlns . '">' . $tc_xml . '</root>');

                            if (!$dom_tmp) {
                                $this->setReturnCode(-1);
                                $this->setMessage('Impossibile leggere l\'xml. (Sostituzione Elabora Tabelle)');
                                return false;
                            }

                            while ($tc->hasChildNodes()) {
                                $tc->removeChild($tc->firstChild);
                            }

                            foreach ($dom_tmp->firstChild->childNodes as $tc_child) {
                                $tc->appendChild($dom->importNode($tc_child, true));
                            }

                            $dom_tmp = null;
                        }

                        /*
                         * Cancello la nuova riga se non presenta del contenuto
                         */
                        if (!$new_row->textContent) {
                            $new_row->parentNode->removeChild($new_row);
                        }

//                        $texts = $this->getElementsByTagNameFromNode($new_row, 't');
//                        /* @var $text DOMElement */
//                        foreach ($texts as $text) {
//                            $text->nodeValue = preg_replace('/@{\$(' . implode('|', $variables) . ')}@/', '@{$$1[' . $i . ']}@', $text->nodeValue);
//                            $text->nodeValue = $this->sostituisciVariabili($text->nodeValue);
//                        }
                    }

                    $row->parentNode->removeChild($row);
                }

                /*
                 * Se ci sono variabili ma non hanno record rimuovo la riga
                 */
                if (count($variables) > 0 && count($indici_presenti) === 0) {
                    $row->parentNode->removeChild($row);
                }
            }
        }

        // Ritorno escludendo l'elemento root
        return $this->extractTag($dom, 'root');
//        return $this->regexTagMatch($dom->saveXML(), 'root');
    }

    /**
     * Ritorna un array con i soli oggetti itaDocumentDOCX da un dizionario
     * @param string $key
     * @return array
     */
    private function getDOCXFromDictionary($dictionary = false, $key = '') {
        if ($dictionary === false) {
            $dictionary = $this->dictionary;
        }

        $is_numeric_array = true;
        foreach ($dictionary as $k => $v) {
            if (!is_int($k)) {
                $is_numeric_array = false;
                break;
            }
        }

        $docxs = array();

        foreach ($dictionary as $k => $v) {
            $nextKey = ($key !== '' ? $key . '.' : '') . $k;
            if (is_object($v) && $v instanceof itaDocumentDOCX) {
                $idx = $is_numeric_array ? $key . "[$k]" : $nextKey;
                $docxs[$idx] = $v->getBody();
            } else if (is_array($v)) {
                $docxs = array_merge($docxs, $this->getDOCXFromDictionary($dictionary[$k], $nextKey));
            }
        }

        return $docxs;
    }

    /**
     * Sostituisce le variabili che corrispondono a DOCX esterni
     * @param string $xml
     * @return string
     */
    private function sostituisciVariabiliDOCX($xml, $relsname) {
        $docxs = $this->getDOCXFromDictionary();
        $has_root = false;

        /*
         * La preg match utilizza i modificatori "m" ed "s":
         * - m: multilinea, cattura testo anche su più linee
         * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
         *      cattura anche i caratteri newline (omessi senza il modificatore)
         */
        if (preg_match("/<\\?xml.*?>/ms", $xml)) {
            $has_root = true;
        }

        // Includo un elemento root o l'xml non sarebbe valido
        /* @var $dom DOMDocument */
        if ($has_root) {
            $dom = DOMDocument::loadXML($xml);
        } else {
            $dom = DOMDocument::loadXML('<root>' . $xml . '</root>');
        }

        if (!$dom) {
            $this->setReturnCode(-1);
            $this->setMessage('Impossibile leggere l\'xml. (Sostituisci Variabili DOCX)');
            return false;
        }

        $replace_array = array();

        /* @var $node DOMElement */
        foreach ($dom->firstChild->childNodes as $node) {
            /*
             * La preg match utilizza i modificatori "m" ed "s":
             * - m: multilinea, cattura testo anche su più linee
             * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
             *      cattura anche i caratteri newline (omessi senza il modificatore)
             */
            preg_match_all('/@{\$(.*?)}@/ms', $node->nodeValue, $matches);

            foreach ($matches[1] as $match) {
                if (isset($docxs[$match])) {
                    $partial = $docxs[$match];
                    $this->incorporateEmbedded($partial['content'], $partial['embedded'], $relsname);
                    $fragment = $dom->createDocumentFragment();
                    $fragment->appendXML($partial['content']);
                    $replace = $node;

                    /*
                     * Cerco i nodi che sono contenuti all'interno di una tabella
                     */
                    if ($node->localName === 'tbl') {
                        /* @var $tc DOMNode */
                        foreach ($node->getElementsByTagName('tc') as $tc) {
                            if (strpos($tc->nodeValue, $match) !== false) {
                                /* @var $tag DOMNode */
                                foreach ($tc->childNodes as $tag) {
                                    if (strpos($tag->nodeValue, $match) !== false) {
                                        $replace = $tag;
                                    }
                                }
                            }
                        }
                    }

                    $replace_array[] = array($replace, $fragment);
                    $this->appendToBody(array('content' => '', 'style' => $partial['style'], 'contentTypes' => $partial['contentTypes']));
                }
            }
        }

        foreach ($replace_array as $arr) {
            if (!$arr[0]->parentNode) {
                continue;
            }

            $arr[0]->parentNode->replaceChild($arr[1], $arr[0]);
        }

        if ($has_root) {
            return $dom->saveXML();
        } else {
            // Ritorno escludendo l'elemento root
            return $this->extractTag($dom, 'root');
//            return $this->regexTagMatch($dom->saveXML(), 'root');
        }
    }

    private function sostituisciVariabiliImmagini($xml, $relsname) {
        $has_root = false;

        if (preg_match("/<\\?xml.*?>/ms", $xml)) {
            $has_root = true;
        }

        /* @var $dom DOMDocument */
        if ($has_root) {
            $dom = DOMDocument::loadXML($xml);
        } else {
            $dom = DOMDocument::loadXML('<root>' . $xml . '</root>');
        }

        if (!$dom) {
            $this->setReturnCode(-1);
            $this->setMessage('Impossibile leggere l\'xml. (Sostituisci Variabili DOCX)');
            return false;
        }

        /* @var $drawing DOMElement */
        foreach ($dom->getElementsByTagName('drawing') as $drawing) {
            /* @var $docPr DOMElement */
            foreach ($drawing->getElementsByTagName('docPr') as $docPr) {
                $descr = $docPr->attributes->getNamedItem('descr')->value;

                if (!preg_match('/@{\$(.*?)}@/', $descr, $matches)) {
                    continue 2;
                }

                $blip = $drawing->getElementsByTagName('blip')->item(0);
                if (!$blip->attributes->getNamedItem('embed')) {
                    continue 2;
                }

                $rId = $blip->attributes->getNamedItem('embed')->value;
                $keys = explode('.', $matches[1]);
                $varData = $this->dictionary;

                foreach ($keys as $key) {
                    if (isset($varData[$key])) {
                        $varData = $varData[$key];
                    } else {
                        continue 3;
                    }
                }

                if (strpos($varData, 'data:') !== 0) {
                    continue 2;
                }

                preg_match('/data:image\/(\w+);base64,([a-zA-Z0-9+\/=]+)/', $varData, $matches);

                $this->contentTypes[$matches[1]] = 'image/' . $matches[1];
                $this->embedded[$relsname][$rId]['Content'] = $matches[2];
            }
        }

        return $xml;
    }

    private function smartyUTF8EncodeVar($var) {
        /*
         * Codifica una variabile per Smarty.
         * Prende in considerazione solo "string" e "array",
         * eventualmente estendere anche ad altro.
         * La codifica viene fatta forzatamente su ogni stringa.
         * Non crea problemi, ma è possibile aggiungere un controllo
         * tramite la funzione "mb_detect_encoding".
         * Dai test, "mb_detect_encoding" ritorna "ASCII" sulle stringhe che
         * non danno problemi, "UTF-8" sulle stringhe accentate che creano problemi.
         * Quest'ultime dovrebbero però essere "ISO-8859-1".
         */

        if (is_string($var)) {
//            if (strpos($var, 'data:image/') === 0) {
//                preg_match('/data:image\/(\w+);base64,([a-zA-Z0-9+\/=]+)/', $var, $matches);
//
//                $rid = $this->addEmbeddedBase64($matches[2], md5($var) . '.' . $matches[1]);
//                $var = $this->getEmbeddedImageCode($rid);
//                return $var;
//            }

            $var = utf8_encode($var);
            /*
             * Modifica 21.03.2016
             * Eseguo l'escape dei caratteri speciali XML (problemi con &)
             */
            //$var = htmlentities($var, ENT_XML1, 'UTF-8');
            $var = htmlspecialchars($var, ENT_NOQUOTES, 'UTF-8');

            /*
             * Modifica DA VERIFICARE per l'inserimento dei caratteri
             * newline con variabili contenenti più righe.
             * La modifica non funziona se la variabile è corretta dalla funzione
             * cleanXML aggiungendo un nodo CDATA.
             */
//            $var = str_replace(array("\n\r", "\n", "\r"), '<' . $this->namespace . ':br/>', $var);
        } else if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = $this->smartyUTF8EncodeVar($v);
            }
        }

        return $var;
    }

    private function sostituisciVariabili($str, $relsname = 'document.xml') {
        $str = $this->sostituisciVariabiliDOCX($str, $relsname);

        $this->sostituisciVariabiliImmagini($str, $relsname);

        $itaSmarty = new itaSmarty();

        foreach ($this->dictionary as $key => $valore) {

            /*
             * Ignoro gli oggetti di primo livello
             */
            if (is_object($valore)) {
                continue;
            }

            if (is_array($valore)) {
                foreach ($valore as $sk => $sv) {
                    /*
                     * Pulisco gli oggetti di secondo livello
                     * (solo in $valore che andrà assegnato)
                     */
                    if (is_object($sv)) {
                        unset($valore[$sk]);
                    }
                }
            }

            /*
             * Codifica UTF-8 per Smarty tramite smartyUTF8EncodeVar,
             * dettagli all'interno della funzione
             * Carlo, 08.01.16
             */
            $itaSmarty->assign($key, $this->smartyUTF8EncodeVar($valore));
        }

        $documentoTmp = $this->getTempPath("docx-{$this->basename}-mergedictionary-{$this->uniqueId}") . DIRECTORY_SEPARATOR . 'tmp.xml';

        $str = $this->legacyConvertVar($str);

        if (!$this->writeFile($documentoTmp, $str)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione nella directory temporanea.');
            return false;
        }

        $str = $itaSmarty->fetch($documentoTmp);

        return $str;
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

    private function legacyConvertVar($str) {
        $patterns = array();
        $replacements = array();
        foreach ($this->dictionary as $key => $value) {
            /*
             * La preg match utilizza i modificatori "m" ed "s":
             * - m: multilinea, cattura testo anche su più linee
             * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
             *      cattura anche i caratteri newline (omessi senza il modificatore)
             */
            $patterns[] = preg_quote('/$' . $key . '.$/ms');
            $replacements[] = '@{\$' . $key . '}@';
        }
        return preg_replace($patterns, $replacements, $str);
    }

    private function getElementsByTagNameFromNode($node, $tag, &$elements = array()) {
        if ($node instanceof DOMElement) {
            /* @var $node DOMElement */
            return $node->getElementsByTagName($tag);
        }

        /* @var $node DOMNode */
        /* @var $child DOMNode */
        foreach ($node->childNodes as $child) {
            if ($child->localName == $tag) {
                array_push($elements, $child);
            } else if ($child->hasChildNodes()) {
                $this->getElementsByTagNameFromNode($child, $tag, $elements);
            }
        }

        return $elements;
    }

    /**
     * Utilità per creare regexp per regexTagMatch
     * 
     * @param type $tag
     * @param type $includeTags
     * @return type
     */
    private function regexHTML($tag, $includeTags = false) {
        /*
         * La preg match utilizza i modificatori "m" ed "s":
         * - m: multilinea, cattura testo anche su più linee
         * - s: tramite il carattere speciale "." (che cattura tutti i caratteri)
         *      cattura anche i caratteri newline (omessi senza il modificatore)
         */
        if ($includeTags) {
            return "/(<{$tag}[^>]*>.*<\/{$tag}>)/ms";
        } else {
            return "/<{$tag}[^>]*>(.*)<\/{$tag}>/ms";
        }
    }

    /**
     * Estrae i tag con il nome specificato
     * 
     * @param type $string Stringa da elaborare
     * @param type $tag Nome del tag da estrarre
     * @param type $includeTags indica se estrarre anche i tag o solo il contenuto
     * @param type $multi estrare solo il primo tag o tutti
     * @return type stringa con il risultato se multi=false, array dei risultati se multi=true
     */
    private function regexTagMatch($string, $tag, $includeTags = false, $multi = false) {
        /*
         * Con le seguenti operazioni si restringe la stringa $string al minimo
         * per effettuare poi il preg_match, riducendo la quantità di backtracking
         * necessario ed evitando eventuali errori di catastrophic backtracking.
         * Per come è strutturata la regex, basterebbe trimmare la stringa dai
         * caratteri non necessari solamente a destra.
         */
//        $open_pos = strpos($s, "<$tag");
//        $close = "</$tag>";
//        $string = substr($string, $open_pos, strrpos($string, $close) - $open_pos + strlen($close));

        if ($multi) {
            preg_match_all($this->regexHTML($tag, $includeTags), $string, $matches);
        } else {
            preg_match($this->regexHTML($tag, $includeTags), $string, $matches);
        }

        return $matches[1];
    }

    private function extractTag($dom, $tag, $includeTag = false) {
        /* @var $dom DOMDocument */

        /* @var $element DOMNode */
        $element = $dom->getElementsByTagName($tag)->item(0);

        if ($includeTag) {
            return $dom->saveXML($element);
        }

        $b = '';

        foreach ($element->childNodes as $child) {
            $b .= $dom->saveXML($child);
        }

        return $b;
    }

    private function normalizzaVariabiliRaccoltaMultipla($array) {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                if (substr($key, -3, 1) === '_' && is_numeric(substr($key, -2))) {
                    if (!is_array($array[substr($key, 0, -3)])) {
                        $array[substr($key, 0, -3)] = array();
                    }

                    /*
                     * Sfaso di 1 la chiave (- 1)
                     */
                    $array[substr($key, 0, -3)][intval(substr($key, -2)) - 1] = $value;
                }
            } elseif (is_array($value)) {
                $array[$key] = $this->normalizzaVariabiliRaccoltaMultipla($value);
            }
        }

        return $array;
    }

}
