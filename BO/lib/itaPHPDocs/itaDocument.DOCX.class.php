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
    private $numbering = array('abs' => array(), 'num' => array());
    private $contentTypesOverrides = array();
    private $usedStyles = array('table' => array(), 'paragraph' => array(), 'character' => array(), 'numbering' => array());
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
        if ($clear) {
            if (!itaLib::clearAppsTempPathRecursive($directory)) {
                $this->setReturnCode(-1);
                $this->setMessage('Errore durante la pulizia della cartella di lavoro temporanea.');
                return false;
            }
        }

        $location = itaLib::createAppsTempPath($directory);
        if (!$location) {
            $this->setReturnCode(-1);
            $this->setMessage('Errore durante la creazione della cartella di lavoro temporanea.');
            return false;
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
        if ($style === false) {
            return false;
        }

        $this->setStyle($style);

        return true;
    }

    private function readXMLEmbedded($filename) {
        $filepath = $this->location . "/word/_rels/$filename.rels";

        if (!file_exists($filepath)) {
            return false;
        }

        $this->embedded[$filename] = array();

        $xmlRels = simplexml_load_file($filepath);

        if ($xmlRels) {
            // Includo tutti gli id di riferimento presenti
            foreach ($xmlRels->Relationship as $relationship) {
                $this->embedded[$filename][(string) $relationship['Id']] = true;
            }
        }

        return $xmlRels;
    }

    private function readXMLNumbering() {
        $filepath = $this->location . '/word/numbering.xml';

        if (!file_exists($filepath)) {
            return array();
        }

        $reader = new XMLReader();

        if (!$reader->open($filepath)) {
            $this->setReturnCode(-1);
            $this->setMessage("Impossibile aprire il documento '" . basename($filepath) . "'.");
            return false;
        }

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'abstractNum') {
                $this->numbering['abs'][$reader->getAttribute('w:abstractNumId')] = $reader->readOuterXml();
                continue;
            }

            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'numStyleLink') {
                $this->usedStyles['numbering'][] = $reader->getAttribute('w:val');
            }

            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == 'num') {
                $currentNumXML = $reader->readOuterXml();

                $matches = array();
                preg_match('/w:val="(\d+)"/', $currentNumXML, $matches);

                $num = $reader->getAttribute('w:numId');

                $this->numbering['num'][$num] = array(
                    'abs' => $matches[1],
                    'XML' => $currentNumXML
                );
                continue;
            }
        }

        return true;
    }
    
    private function readXMLContentTypes() {
        $xmlTypes = simplexml_load_file($this->location . '/[Content_Types].xml');

        if ($xmlTypes) {
            // Leggo tutti i Content-Type
            foreach ($xmlTypes->Default as $default) {
                $this->contentTypes[(string) $default['Extension']] = (string) $default['ContentType'];
            }

            foreach ($xmlTypes->Override as $override) {
                $this->contentTypesOverrides[(string) $override['PartName']] = (string) $override['ContentType'];
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

        if ($isMainDocument) {
            $xmlNumbering = $this->readXMLNumbering();
            if ($xmlNumbering === false) {
                return false;
            }
        }

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
            
            /*
             * Inserito per gestione immagini
             */
            if ($xmlRels && $reader->nodeType == XMLReader::ELEMENT && $reader->getAttribute('r:embed')) {
                foreach ($xmlRels->Relationship as $relationship) {
                    if ($relationship['Id'] == $reader->getAttribute('r:embed')) {
                        $relationship->addAttribute('Content', base64_encode(file_get_contents($this->location . '/word/' . $relationship['Target'])));
                        $this->embedded[$filename][(string) $relationship['Id']] = $relationship;
                    }
                }
                continue;
            }

            /*
             * Inserito per gestione hyperlink
             */
            if ($xmlRels && $reader->nodeType == XMLReader::ELEMENT && $reader->getAttribute('r:id')) {
                foreach ($xmlRels->Relationship as $relationship) {
                    if ($relationship['Id'] == $reader->getAttribute('r:id')) {
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

        $content = str_replace('-&gt;', '->', $content);

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
            'preg'  => array('/@{\$(.*?)}@/ms', '/\$(.*?)\.\$/ms', '/@{if(.*?)}@/ms', '/@{else}@/ms', '/@{\/if}@/ms', '/@{foreach(.*?)}@/ms', '/@{\/foreach}@/ms', '/@{while(.*?)}@/ms', '/@{\/while}@/ms', '/@{eval var=(.*?)}@/ms'),
            'open'  => array('@{$'            , '$'              , '@{if'           , '@{else'      , '@{/if'       , '@{foreach'           , '@{/foreach'       , '@{while'           , '@{/while'       , '@{eval var='          ),
            'close' => array('}@'             , '.$'             , '}@'             , '}@'          , '}@'          , '}@'                  , '}@'               , '}@'                , '}@'             , '}@'                    )
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
                    $original_text = $text;

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
                                    if (
                                        $char == $open_tag[$searching_pattern_index] &&
                                        substr($matched_string . $original_text, ($charpos + strlen($matched_string)) - $searching_pattern_index, $searching_pattern_index) == substr($open_tag, 0, $searching_pattern_index)
                                    ) {
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
            $this->usedStyles['table'][] = $t->getAttribute($this->namespace . ':val');
            array_push($styles, $t);
        }

        /*
         * Paragrafo
         */
        foreach ($dom->getElementsByTagName('pStyle') as $t) {
            $this->usedStyles['paragraph'][] = $t->getAttribute($this->namespace . ':val');
            array_push($styles, $t);
        }
        
        /*
         * Run
         */
        foreach ($dom->getElementsByTagName('rStyle') as $t) {
            $this->usedStyles['character'][] = $t->getAttribute($this->namespace . ':val');
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
                /*
                 * Controllo se lo stile è effettivamente
                 * utilizzato nel documento o è un default. In caso contrario
                 * non lo includo.
                 */

                $styleType = $reader->getAttribute($this->namespace . ':type');
                $styleId = $reader->getAttribute($this->namespace . ':styleId');

                if (
                    !($reader->getAttribute($this->namespace . ':default')) &&
                    isset($this->usedStyles[$styleType]) &&
                    !in_array($styleId, $this->usedStyles[$styleType])
                ) {
                    continue;
                }

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

        return $str;
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
        if (strpos($styles, '<' . $this->namespace . ':style ') !== false) {
            $stylesOpening = substr($styles, 0, strpos($styles, '<' . $this->namespace . ':style '));
        } else {
            $stylesOpening = substr($styles, 0, strpos($styles, '</' . $this->namespace . ':styles>'));
        }
        // Sostituisco con gli stili elaborati
        file_put_contents($this->getLocation() . '/word/styles.xml', $stylesOpening . $this->style . '</' . $this->namespace . ':styles>');

        // Elaboro le relationships
        foreach ($this->embedded as $filename => $embedded) {
            $xmlRels = file_get_contents($this->location . "/word/_rels/$filename.rels");

            if (!$xmlRels) {
                continue;
            }

            foreach ($embedded as $id => $xmlelement) {
                /* @var $xmlelement SimpleXMLElement */

                /*
                 * Distinguo per tipo di relationship
                 */

                if (strpos($xmlelement['Type'], 'relationships/image') !== false) {
                    if (!isset($xmlelement['Content'])) {
                        continue;
                    }

                    $xmlelement['Target'] = (string) $xmlelement['Target'];

                    if (strpos($xmlRels, "\"$id\"") === false) {
                        if (file_exists($this->location . '/word/' . $xmlelement['Target'])) {
                            $xmlelement['Target'] = $this->getNextFilename($this->location . '/word/', $xmlelement['Target']);
                        }
                    }

                    @mkdir(dirname($this->location . '/word/' . $xmlelement['Target']), 0777, true);
                    file_put_contents($this->location . '/word/' . $xmlelement['Target'], base64_decode($xmlelement['Content']));
                    unset($xmlelement['Content']);
                }

                /*
                 * Salvo la relationship se non già presente
                 */

                if (strpos($xmlRels, "\"$id\"") === false) {
                    unset($xmlelement['Id']);

                    $relationshipXml = '<Relationship Id="' . $id . '"';
                    foreach ($xmlelement->attributes() as $k => $v) {
                        $relationshipXml .= " $k=\"$v\"";
                    }

                    $xmlRels = str_replace('</Relationships>', $relationshipXml . '/></Relationships>', $xmlRels);
                }
            }

            if ($filename === 'document.xml' && count($this->numbering['num']) && strpos($xmlRels, 'relationships/numbering') === false) {
                $nextId = $this->getNextEmbedKey($embedded);
                $relationshipXml = '<Relationship Id="rId' . $nextId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>';
                $xmlRels = str_replace('</Relationships>', $relationshipXml . '</Relationships>', $xmlRels);
            }

            file_put_contents($this->location . "/word/_rels/$filename.rels", $xmlRels);
        }
       
        /*
         * Elaboro le numerazioni
         */

        $numbering = file_get_contents($this->location . '/word/numbering.xml');
        if (!$numbering) {
            $numbering = utf8_encode('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\r\n" . '<w:numbering xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"></w:numbering>');
        }

        foreach ($this->numbering['num'] as $numId => $numberingDef) {
            if (!isset($this->numbering['abs'][$numberingDef['abs']])) {
                $this->setReturnCode("");
                $this->setMessage('Errore riferimento numerazione ' . $numberingDef['abs']);
                return false;
            }

            if (strpos($numbering, "numId=\"$numId\"") === false) {
                $numbering = str_replace('</w:numbering>', $numberingDef['XML'] . '</w:numbering>', $numbering);
            }

            if (strpos($numbering, "abstractNumId=\"{$numberingDef['abs']}\"") === false) {
                $XML = $this->numbering['abs'][$numberingDef['abs']];
                $numbering = str_replace('</w:abstractNum><w:num', '</w:abstractNum>' . $XML . '<w:num', $numbering);
            }
        }

        file_put_contents($this->location . '/word/numbering.xml', $numbering);

        // Elaboro i Content-Types
        $xmlTypes = file_get_contents($this->location . '/[Content_Types].xml');
        if ($xmlTypes) {
            foreach ($this->contentTypes as $extension => $contentType) {
                if (strpos($xmlTypes, "Extension=\"$extension\"") === false) {
                    $defaultXml = "<Default Extension=\"$extension\" ContentType=\"$contentType\"/>";
                    $xmlTypes = str_replace('</Types>', $defaultXml . '</Types>', $xmlTypes);
                }
            }

            foreach ($this->contentTypesOverrides as $partName => $contentType) {
                if (
                    strpos($xmlTypes, "PartName=\"$partName\"") === false &&
                    file_exists($this->location . $partName)
                ) {
                    $overrideXml = "<Override PartName=\"$partName\" ContentType=\"$contentType\"/>";
                    $xmlTypes = str_replace('</Types>', $overrideXml . '</Types>', $xmlTypes);
                }
            }

            file_put_contents($this->location . '/[Content_Types].xml', $xmlTypes);
        }

        return true;
    }
   
    private function getNextFilename($basepath, $filename) {
        $filepath = $basepath . $filename;
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

        return str_replace($pathinfo['filename'], $f . $n, $filename);
    }

    public function saveContent($file, $overwrite = false) {
        $exists = file_exists($file);
        if ($exists) {
            if ($overwrite == false) {
                $this->setReturnCode("");
                $this->setMessage('File già esistente.');
                return false;
            }

            if (!unlink($file)) {
                $this->setReturnCode("");
                $this->setMessage('Errore in sovrascrittura file.');
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
            'contentTypes' => array(),
            'numbering' => array()
        );
    }

    public function getBody() {
        return array(
            'content' => $this->content,
            'style' => $this->style,
            'embedded' => $this->embedded,
            'contentTypes' => $this->contentTypes,
            'contentTypesOverrides' => $this->contentTypesOverrides,
            'numbering' => $this->numbering
        );
    }

    private function getNextEmbedKey($embedArray) {
        $nextKey = 1;
        foreach (array_keys($embedArray) as $embedKey) {
            if (substr($embedKey, 3) >= $nextKey) {
                $nextKey = substr($embedKey, 3) + 1;
            }
        }

        return $nextKey;
    }

    private function incorporateEmbedded($content, $embedded, $relsname = 'document.xml') {
        $nextid = $this->getNextEmbedKey($this->embedded[$relsname]);

        foreach ($embedded['document.xml'] as $id => $xmlelement) {
            if ($xmlelement === true)
                continue;

            if (
                strpos($xmlelement['Type'], 'relationships/header') !== false ||
                strpos($xmlelement['Type'], 'relationships/footer') !== false
            ) {
                continue;
            }

            $xmlelement['Id'] = 'rId' . ($nextid++);
            $this->embedded[$relsname][(string) $xmlelement['Id']] = $xmlelement;

            $content = str_replace("\"$id\"", '"' . $xmlelement['Id'] . '"', $content);
        }

        return $content;
    }
    
    private function incorporateNumbering($content, $numbering) {
        $nextAbsId = 1;

        if (max(array_keys($numbering['abs'])) >= $nextAbsId) {
            $nextAbsId = max(array_keys($numbering['abs'])) + 1;
        }


        if (max(array_keys($this->numbering['abs'])) >= $nextAbsId) {
            $nextAbsId = max(array_keys($this->numbering['abs'])) + 1;
        }

        $arrayMapAbs = array();

        foreach ($numbering['abs'] as $replaceAbsId => $xml) {
            $arrayMapAbs[$replaceAbsId] = $nextAbsId;
            $this->numbering['abs'][$nextAbsId] = str_replace("{$this->namespace}:abstractNumId=\"$replaceAbsId\"", "{$this->namespace}:abstractNumId=\"$nextAbsId\"", $xml);
            $nextAbsId++;
        }

        $nextNumId = 1;

        if (max(array_keys($numbering['num'])) >= $nextNumId) {
            $nextNumId = max(array_keys($numbering['num'])) + 1;
        }


        if (max(array_keys($this->numbering['num'])) >= $nextNumId) {
            $nextNumId = max(array_keys($this->numbering['num'])) + 1;
        }

        foreach ($numbering['num'] as $replaceNumId => $values) {
            /*
             * Verifico la presenza nel testo
             */

            if (!preg_match("/{$this->namespace}:numId {$this->namespace}:val=\"$replaceNumId\"/", $content)) {
                continue;
            }

            $content = str_replace("{$this->namespace}:numId {$this->namespace}:val=\"$replaceNumId\"", "{$this->namespace}:numId {$this->namespace}:val=\"$nextNumId\"", $content);

            $oldAbsId = $values['abs'];
            $newAbsId = $arrayMapAbs[$values['abs']];

            $this->numbering['num'][$nextNumId] = array();
            $this->numbering['num'][$nextNumId]['abs'] = $newAbsId;
            $this->numbering['num'][$nextNumId]['XML'] = str_replace(
                array(
                "{$this->namespace}:numId=\"$replaceNumId\"",
                "{$this->namespace}:abstractNumId {$this->namespace}:val=\"$oldAbsId\""
                ), array(
                "{$this->namespace}:numId=\"$nextNumId\"",
                "{$this->namespace}:abstractNumId {$this->namespace}:val=\"$newAbsId\""
                ), $values['XML']);

            $nextNumId++;
        }

        return $content;
    }
    
    public function appendToBody($toAppend) {
        $toAppend['content'] = $this->embedToDocument($toAppend);
        $this->setContent($this->getContent() . $toAppend['content']);
        return true;
    }

    public function prependToBody($toPrepend) {
        $toPrepend['content'] = $this->embedToDocument($toPrepend);
        $this->setContent($toPrepend['content'] . $this->getContent());
        return true;
    }

    public function embedToDocument($toEmbed, $relsname = 'document.xml') {
        $toEmbed['content'] = $this->incorporateEmbedded($toEmbed['content'], $toEmbed['embedded'], $relsname);
        $toEmbed['content'] = $this->incorporateNumbering($toEmbed['content'], $toEmbed['numbering']);

        /* Rimuovo l'indicatore di stile primario (qFormat) e rimuovo il valore
         * default che applica automaticamente uno stile */
        $this->style .= preg_replace(array(
            '/<' . $this->namespace . ':qFormat\/>/ms',
            '/' . $this->namespace . ':default=".*?"/ms'
            ), '', $toEmbed['style']);
        
        $this->contentTypes = array_merge($this->contentTypes, $toEmbed['contentTypes']);
        if ($toEmbed['contentTypesOverrides']) {
            $this->contentTypesOverrides = array_merge($this->contentTypesOverrides, $toEmbed['contentTypesOverrides']);
        }
        return $toEmbed['content'];
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
                $n_records = 0;

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

                        if ($n_records < count($check)) {
                            $n_records = count($check);
                        }
                    }
                }
                
                $variables = array_unique($variables);

                if ($n_records) {
                    for ($i = 0; $i < $n_records; $i++) {
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
                            $tc_xml = preg_replace('/(' . implode('|', array_map('preg_quote', $variables, array_fill(0, count($variables), '/'))) . ')(?![a-z_])/i', '$1[' . $i . ']', $tc_xml);

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
                if (count($variables) > 0 && $n_records === 0) {
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

                    $partial['content'] = $this->embedToDocument($partial, $relsname);
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
    
        /**
     * Rimuove i paragrafi che contengono soltando istruzioni di controllo
     */
    private function clearParagraphs($xml) {
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
            $this->setMessage('Impossibile leggere l\'xml. (clearParagraphs)');
            return false;
        }

        $toReplace = array();
        $controlBlocks = array('if', 'foreach', 'while');
        $matchInfo = array();
        $searchClosure = false;

        /*
         * I nodi al primo livello dell'XML da pulire
         */

        $elements = $dom->childNodes->item(0)->childNodes;

        /*
         * Scorro i nodi selezionati e prendo solo quelli che contegono almeno un pattern di sostituzione
         */

        /* @var $element DOMNode */
        foreach ($elements as $element) {
            $currentNodeValue = trim($element->nodeValue);

            if (preg_match('/^@{(' . implode('|', $controlBlocks) . ')((?!}@).)+}@$/m', $currentNodeValue, $matches)) {
                $matchInfo['element'] = $element;
                $matchInfo['tag'] = $matches[1];

                foreach ($elements as $searchElement) {
                    $searchNodeValue = trim($searchElement->nodeValue);

                    if ($element === $searchElement) {
                        $searchClosure = true;
                        continue;
                    }

                    /*
                     * Case speciale per l'else
                     */
                    if ($searchClosure && $matchInfo['tag'] == 'if' && preg_match('/@{else}@/m', $searchNodeValue) && !$searchElement->getAttribute('rand')) {
                        if ($searchNodeValue !== "@{else}@") {
                            $searchClosure = false;
                        } else {
                            $searchElement->setAttribute('rand', rand()); // Imposto un attributo random per identificare in modo univoco il nodo
                            $matchInfo['else'] = $searchElement;
                        }
                    }

                    if ($searchClosure && preg_match('/@{\/' . $matchInfo['tag'] . '}@/m', $searchNodeValue) && !$searchElement->getAttribute('rand')) {
                        if ($searchNodeValue == "@{/{$matchInfo['tag']}}@") {
                            $searchElement->setAttribute('rand', rand()); // Imposto un attributo random per identificare in modo univoco il nodo

                            $toReplace[$dom->saveXML($matchInfo['element'])] = trim($matchInfo['element']->nodeValue);
                            $toReplace[$dom->saveXML($searchElement)] = $searchNodeValue;
                            if (isset($matchInfo['else'])) {
                                $toReplace[$dom->saveXML($matchInfo['else'])] = trim($matchInfo['else']->nodeValue);
                            }
                        }

                        $searchClosure = false;
                    }
                }

                $matchInfo = array();
            }

            /*
             * Costrutti speciali
             */
            if (
                preg_match('/^@{\$PAGE_BREAK}@$/m', $currentNodeValue, $matches) ||
                preg_match('/^@{eval ((?!}@).)+}@$/m', $currentNodeValue, $matches)
            ) {
                $toReplace[$dom->saveXML($element)] = $currentNodeValue;
            }
        }

        if ($has_root) {
            $returnStr = $dom->saveXML();
        } else {
            $returnStr = $this->extractTag($dom, 'root');
        }

        foreach ($toReplace as $k => $v) {
            $returnStr = str_replace($k, $v, $returnStr);
        }

        return $returnStr;
    }
    
    private function sostituisciVariabili($str, $relsname = 'document.xml') {
        $str = $this->sostituisciVariabiliDOCX($str, $relsname);

        $this->sostituisciVariabiliImmagini($str, $relsname);

        $itaSmarty = new itaSmarty();

        foreach ($this->dictionary as $key => $valore) {
            /*
             * Codifica UTF-8 per Smarty tramite smartyUTF8EncodeVar,
             * dettagli all'interno della funzione
             * Carlo, 08.01.16
             */
            $itaSmarty->assign($key, $this->smartyUTF8EncodeVar($valore));
        }

        $docxPageBreak = $this->getPageBreak();
        $itaSmarty->assign('PAGE_BREAK', $docxPageBreak['content']);

        $documentoTmp = $this->getTempPath('docxMergeDictionary') . DIRECTORY_SEPARATOR . "{$this->basename}_{$this->uniqueId}.xml";

        $str = $this->clearParagraphs($str);
        $str = $this->legacyConvertVar($str);
        $str = str_replace('-&gt;', '->', $str);

        if (!$this->writeFile($documentoTmp, $str)) {
            $this->setReturnCode("");
            $this->setMessage('Errore nella scrittura del risultato dell\'elaborazione nella directory temporanea.');
            return false;
        }

        $str = $itaSmarty->fetch($documentoTmp);

        unlink($documentoTmp);

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
