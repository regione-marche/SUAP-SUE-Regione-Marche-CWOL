<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentMapping/itaDocumentMappingFactory.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaDocEditor.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';
include_once ITA_LIB_PATH . '/itaOnlyOffice/itaOnlyOfficePluginFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';

class docLib {

    /**
     * Libreria di funzioni Generiche e Utility per Documenti
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $ITALWEB;
    private $errMessage;
    private $errCode;
    private $protectedCats = array(
        "SEGRETERIA"
    );

    //
    const CLASSIFICAZIONE_SERVECONOMICI = "SERV_ECON";
    const CLASSIFICAZIONE_ANAGRAFE = "ANAGRAFE";
    const CLASSIFICAZIONE_ELETTORALE = "ELETTORALE";
    const CLASSIFICAZIONE_PRATICHE = "PRATICHE";
    const CLASSIFICAZIONE_FIERE = "FIERE";
    const CLASSIFICAZIONE_ZTL = "ZTL";
    const CLASSIFICAZIONE_COMMERCIO = "COMMERCIO";
    const CLASSIFICAZIONE_TRIBUTI = "TRIBUTI";
    const CLASSIFICAZIONE_CDS = "CDS";
    const CLASSIFICAZIONE_CDR = "CDR";
    const CLASSIFICAZIONE_ALBO = "ALBO";
    const CLASSIFICAZIONE_SEGRETERIA = "SEGRETERIA";
    const CLASSIFICAZIONE_BDAP = "BDAP";
    const CLASSIFICAZIONE_AMMTRASPARENTE = "AMMT";
    const CLASSIFICAZIONE_INCIDENTI = "INCIDENTI";
    const CLASSIFICAZIONE_GAP = "GAP";
    const CLASSIFICAZIONE_PROTOCOLLO = "PROTOCOLLO";
    const CLASSIFICAZIONE_TRIBUTICW = "TRIBUTI-CW";
    const CLASSIFICAZIONE_FOSUAP = "FO-SUAP";
    const CLASSIFICAZIONE_CROCEROSSA = "CROCEROSSA";
    const CLASSIFICAZIONE_SOCIALI = "SOCIALI";
    const CLASSIFICAZIONE_ICI = "ICI";
    //
    const PERM_READONLY = 0755;
    const MAX_CONVERSIONS = 50;
    
    public static function getElencoClassificazioni() {
        return array(
            self::CLASSIFICAZIONE_SERVECONOMICI => "Servizi Economici",
            self::CLASSIFICAZIONE_ANAGRAFE => "Anagrafe",
            self::CLASSIFICAZIONE_ELETTORALE => "Elettorale",
            self::CLASSIFICAZIONE_PRATICHE => "Pratiche",
            self::CLASSIFICAZIONE_FIERE => "Fiere e Mercati",
            self::CLASSIFICAZIONE_ZTL => "Ztl",
            self::CLASSIFICAZIONE_COMMERCIO => "Commercio",
            self::CLASSIFICAZIONE_TRIBUTI => "Tributi",
            self::CLASSIFICAZIONE_CDS => "Codice della Strada",
            self::CLASSIFICAZIONE_CDR => "Codice dei Regolamenti",
            self::CLASSIFICAZIONE_ALBO => "Albo Pretorio",
            self::CLASSIFICAZIONE_SEGRETERIA => "Segreteria",
            self::CLASSIFICAZIONE_BDAP => "Bdap",
            self::CLASSIFICAZIONE_AMMTRASPARENTE => "Amministrazione Trasparente",
            self::CLASSIFICAZIONE_INCIDENTI => "Incidenti",
            self::CLASSIFICAZIONE_GAP => "Gestione Pratiche Ricorsi",
            self::CLASSIFICAZIONE_PROTOCOLLO => "Protocollo",
            self::CLASSIFICAZIONE_TRIBUTICW => "Tributi CityWare",
            self::CLASSIFICAZIONE_FOSUAP => "Modelli Personalizzati Suap",
            self::CLASSIFICAZIONE_CROCEROSSA => "Croce Rossa",
            self::CLASSIFICAZIONE_SOCIALI => "Servizi Sociali",
            self::CLASSIFICAZIONE_ICI => "Imposta Comunale sugli Immobili"
        );
    }

    function __construct() {
        
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getProtectedCats() {
        return $this->protectedCats;
    }

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getITALWEB() {
        if (!$this->ITALWEB) {
            try {
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB;
    }

    public function getProtectedCatsWhere() {
        $protected_where = '';
        if ($this->protectedCats) {
            $protected_where = " AND ( 1=1";
            foreach ($this->protectedCats as $cat) {
                $protected_where .= " AND CLASSIFICAZIONE<>'$cat'";
            }
            $protected_where .= ") ";
        }
        return $protected_where;
    }

    public function getFilePath($documenti_rec) {
        $docPath = Config::getPath('general.fileEnte') . "ente" . App::$utente->getKey('ditta') . "/documenti/";
        $nomeFile = $documenti_rec['URI'];
        return $docPath . $nomeFile;
    }

    public function getClassificazioni() {
        $protectedWhere = $this->getProtectedCatsWhere();
        $sqlCla = "SELECT CLASSIFICAZIONE FROM DOC_DOCUMENTI WHERE 1=1 $protectedWhere GROUP BY CLASSIFICAZIONE";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sqlCla, true);
    }

    public function getDocumenti($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CODICE='$codice'";
        } else {
            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getDocumentiByClassFunz($classificazione = '', $funzione = '') {
        $sql = "SELECT * FROM DOC_DOCUMENTI";
        $where = "WHERE";

        if (!empty($classificazione)) {
            $sql .= " $where CLASSIFICAZIONE = '" . addslashes($classificazione) . "'";
            $where = "AND";
        }
        if (!empty($funzione)) {
            $sql .= " $where FUNZIONE = '" . addslashes($funzione) . "'";
            $where = "AND";
        }

        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql);
    }

    public function getDocIntegrativi($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID_PADRE <>'$codice'";
        } elseif ($tipo == 'figlio') {
            $sql = "SELECT * FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID_PADRE='$codice'";  // ritorna il record figlio
        } else {
            $sql = "SELECT * FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID='$codice'";
        }
        if ($where) {
            $sql .= ' AND ' . $where . ' ORDER BY SEQUENZA';
        } else {
            $sql .= ' ORDER BY SEQUENZA';
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getClassificazioneDoc($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT CODICEDOC FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID ='$codice'";
        } elseif ($tipo == 'figli') {
            $sql = "SELECT CODICEDOC FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID_PADRE ='$codice'";
        } else {
            $sql = "SELECT * FROM DOC_ANAG_CLASSIFICAZIONE WHERE ROW_ID ='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function deleteDocumenti($codice, $tipo = 'rowid') {
        $documenti_rec = $this->getDocumenti($codice, $tipo);
        $eqAudit = new eqAudit();

        try {
            if (!ItaDB::DBDelete($this->getITALWEB(), 'DOC_DOCUMENTI', 'ROWID', $documenti_rec['ROWID'])) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in Cancellazione su DOC_DOCUMENTI');

                $eqAudit->logEqEvent('', array(
                    'DB' => 'ITALWEB',
                    'DSet' => 'DOC_DOCUMENTI',
                    'Operazione' => eqAudit::OP_DEL_RECORD_FAILED,
                    'Estremi' => 'Cancellazione DOC_DOCUMENTI: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'],
                    'Key' => 'ROWID'
                ));

                return false;
            }

            $eqAudit->logEqEvent('', array(
                'DB' => 'ITALWEB',
                'DSet' => 'DOC_DOCUMENTI',
                'Operazione' => eqAudit::OP_DEL_RECORD,
                'Estremi' => 'Cancellazione DOC_DOCUMENTI: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'],
                'Key' => 'ROWID'
            ));

            if ($documenti_rec['URI'] && file_exists($this->setDirectory() . $documenti_rec['URI'])) {
                if (!$this->CancellaTesto($this->setDirectory() . $documenti_rec['URI'], '')) {
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }

        return true;
    }

    public function deleteStoricoDocumenti($codice) {
        //permessa cancellazione solo per rowid
        $sql = "SELECT * FROM DOC_STORICO WHERE ROWID = $codice";
        $documenti_storico = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
        $eqAudit = new eqAudit();
        try {
            if (!ItaDB::DBDelete($this->getITALWEB(), 'DOC_STORICO', 'ROWID', $documenti_storico['ROWID'])) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in Cancellazione su DOC_STORICO');

                $eqAudit->logEqEvent('', array(
                    'DB' => 'ITALWEB',
                    'DSet' => 'DOC_STORICO',
                    'Operazione' => eqAudit::OP_DEL_RECORD_FAILED,
                    'Estremi' => 'Cancellazione DOC_STORICO: ' . $documenti_storico['CODICE'] . " " . $documenti_storico['OGGETTO'],
                    'Key' => 'ROWID'
                ));

                return false;
            }

            $eqAudit->logEqEvent('', array(
                'DB' => 'ITALWEB',
                'DSet' => 'DOC_DOCUMENTI',
                'Operazione' => eqAudit::OP_DEL_RECORD,
                'Estremi' => 'Cancellazione DOC_STORICO: ' . $documenti_storico['CODICE'] . " " . $documenti_storico['OGGETTO'],
                'Key' => 'ROWID'
            ));

            if ($documenti_storico['URI'] && file_exists($this->setDirectory() . $documenti_storico['NUMREV'] . "_" . $documenti_storico['URI'])) {
                if (!$this->CancellaTesto($this->setDirectory() . $documenti_storico['NUMREV'] . "_" . $documenti_storico['URI'], '')) {
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 
     */
    public function importAllegatoFromDocumento($codice, $tipo = 'codice') {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita");
                return false;
            }
        }
        $pathDocumenti = $this->setDirectory('base', false);
        if (!$pathDocumenti) {
            $this->setErrCode(-1);
            $this->setErrMessage("Accesso cartella documento impossibile.");
            return false;
        }
        $Doc_documenti_rec = $this->getDocumenti($codice, $tipo);
        if (!$Doc_documenti_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Documento: $codice non trovato.");
            return false;
        }
        $suffix = pathinfo($Doc_documenti_rec['URI'], PATHINFO_EXTENSION);
        $randName = md5(rand() * time());
        $destino = itaLib::getPrivateUploadPath() . "/" . $randName . "." . $suffix;
        $sorgente_htm = $pathDocumenti . pathInfo($Doc_documenti_rec['URI'], PATHINFO_BASENAME);
        $destino_htm = itaLib::getPrivateUploadPath() . "/" . $randName . "." . pathInfo($Doc_documenti_rec['URI'], PATHINFO_EXTENSION);
        switch ($suffix) {
            case 'xhtml':
                $contenuto = $Doc_documenti_rec['CONTENT'];
                $contenuto = "<!-- itaTestoBase:" . $Doc_documenti_rec['CODICE'] . " -->" . $contenuto;
                if (!@file_put_contents($destino, $contenuto)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Salvataggio documento di appoggio XHTML fallito.");
                    return false;
                }
                break;
            case 'htm':
                if (!$this->CopiaTesto($sorgente_htm, $destino_htm, "word-html")) {
                    return false;
                }
                break;
            case 'docx':
                if (!$this->CopiaTesto($sorgente_htm, $destino_htm, "docx")) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return $destino;
    }

    /**
     * 
     * @param type $docCodice
     * @param type $bodyValue
     * @return boolean
     */
    public function getXhtmlDocBodyString($docCodice, $bodyValue = '') {
        $documenti_rec = $this->getDocumenti($docCodice);
        if (!$documenti_rec) {
            return false;
        }
        if ($bodyValue) {
            $documenti_rec['CONTENT'] = $bodyValue;
        } else {
            $bodyValue = $documenti_rec['CONTENT'];
        }
    }

    /**
     * Restituisce il contenuto di un documento formato xhtml
     * impaginato come da modello
     * 
     * @param type $docCodice
     * @param type $bodyValue
     * @param type $formatForDuplex
     * @return boolean
     */
    public function getXhtmlDocString($docCodice, $bodyValue = '', $formatForDuplex = false, $extraParam = array()) {
        //
        // recupero il testo base di provenienza
        //
        if (!$docCodice) {
            return false;
        }
        $documenti_rec = $this->getDocumenti($docCodice);
        if (!$documenti_rec) {
            return false;
        }
        if ($bodyValue) {
            $documenti_rec['CONTENT'] = $bodyValue;
        } else {
            $bodyValue = $documenti_rec['CONTENT'];
        }

        if ($formatForDuplex == true) {
            $bodyValue .= '<p style="page-break-after: left;"><!--pagebreak-left--></p>';
        }

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


        /// qui smarty header footer content


        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $bodyValue);
        $itaSmarty->assign('documentheader', $headerContent);
        $itaSmarty->assign('documentfooter', $footerContent);
        $itaSmarty->assign('headerHeight', $marginHeader);
        $itaSmarty->assign('footerHeight', $marginFooter);
        $itaSmarty->assign('marginTop', $marginTop);
        $itaSmarty->assign('marginBottom', $marginBottom);
        $itaSmarty->assign('marginLeft', $marginLeft);
        $itaSmarty->assign('marginRight', $marginRight);
        $itaSmarty->assign('pageFormat', $format);
        $itaSmarty->assign('pageOrientation', $orientation);
        if ($extraParam) {
            foreach ($extraParam as $campo => $valore) {
                $itaSmarty->assign($campo, $valore);
            }
        }

        $documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';
        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layoutTemplate.xhtml";
        if (!copy($layoutTemplate, $documentLayout)) {
            Out::msgStop("Errore", "Copia template layout Fallita");
            return false;
        }
        $docXhtmlString = $itaSmarty->fetch($documentLayout);
        @unlink($documentLayout);
        return $docXhtmlString;
    }

    /**
     * Analizza e compila un documento xhtml predisposto con css modello
     * 
     * @param type $xhtmlDocString in codifica UTF-8
     * @param type $dictionaryValues in codifica UTF-8
     * @return boolean
     */
    function compileXhtmlTemplate($xhtmlDocString, $dictionaryValues) {
        $dom = new DOMDocument;

        /*
         * Trasformo in DOM Object la stringa Xhtml
         */
        $template = $xhtmlDocString;
        $ret = $dom->loadXML($template);
        if ($ret === false) {
            return false;
        }

        /*
         * Analizzo le tabelle del template per trattare quelle con classe ita-template
         */
        $tables = $dom->getElementsByTagName('table');
        foreach ($tables as $table) {
            if (!$table->getAttribute('class') == "ita-table-template") {
                continue;
            }

            /*
             * Estraggo le righe della tabelle individuata
             */
            $trs = $table->getElementsByTagName('tr');

            foreach ($trs as $tr) {
                if ($tr->getAttribute('class') == 'ita-table-header') {
                    continue;
                }

                /*
                 * Preparo i campi multipli in un array
                 * cliclendo una cella(td) alla volta
                 */
                $newGrid = array();
                $tds = $tr->getElementsByTagName('td');
                foreach ($tds as $td) {
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

                        /*
                         * Per ogni cella individuto la variabile da sostituire
                         */
                        $unit_inner = $this->extract_unit($nodeValue, "@{", "}@");
                        if (!$unit_inner) {
                            break;
                        }
                        $unit = "@{" . $unit_inner . "}@";
                        list($skip, $key0) = explode("$", $unit_inner);
                        list($key1, $key2) = explode(".", $key0);

                        /*
                         * Creo una griglia con le nuove valriabili da sostituire ma con un suffisso per indica riga dati
                         */
                        foreach ($dictionaryValues[$key1] as $campo => $valueCampo) {
                            if (strpos($campo, $key2) !== false) {
                                list($skip, $idx) = explode($key2, $campo);
                                $newUnit = '@{$' . $key1 . "." . $key2 . $idx . '}@';
                                $newGrid[$idx][$unit] = $newUnit;
                            }
                        }
                        /*
                         * Elminio dalla variabile di appoggio la variabile elaborata
                         */
                        $nodeValue = str_replace($unit, "", $nodeValue);
                    }
                }
                $trCloned = $tr->cloneNode(TRUE);
                break;
            }

            /*
             * Procedura di moltiplicazione della riga template
             */
            if ($removeTemplate) {
                /*
                 * Rimuovo il tr template non indicizzato
                 */
                try {
                    $tr->parentNode->removeChild($tr);
                } catch (Exception $exc) {
                    ob_end_clean();
                    die($exc->getMessage());
                }
            }
            /*
             * Ciclo la griglia con le nuove variabili da sostituire
             */
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
                /*
                 * Aggiunto utf_8_encode per compatibilità con xhtml in ingresso
                 */
                $tmpDOM->loadHTML(utf8_encode($stringTR));
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

        $xhtmDocStringPhase2 .= $tmpDOM->saveXML($xmTemplate); //**
        //$xhtmDocStringPhase2 .= utf8_decode($tmpDOM->saveXML($xmTemplate)); // *MM*
//        $xhtmDocStringPhase2 = utf8_decode($dom->saveXML());
//        $xhtmDocStringPhase2 = utf8_decode($tmpDOM->saveXML());
        $baseFile = md5(rand() * microtime());
        $fileTemplate = itaLib::getAppsTempPath() . "/" . $baseFile . ".txt";
        if (!file_put_contents($fileTemplate, $xhtmDocStringPhase2)) {
            //TODO@ sistema return errori
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return false;
        }
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            if (is_array($valore)) {
                foreach ($valore as $key1 => $value) {
                    //$valore[$key1] = htmlspecialchars($value);
                    $valore[$key1] = $this->normalizzaVariabili($value);
                }
            } else {
                //$valore = htmlspecialchars($valore);
                $valore = $this->normalizzaVariabili($valore);
            }
            $itaSmarty->assign($key, $valore);
        }
        //$xhtmCompiledDoc = utf8_encode($itaSmarty->fetch($fileTemplate));
        $xhtmCompiledDoc = $itaSmarty->fetch($fileTemplate);
        unlink($fileTemplate);
        return $xhtmCompiledDoc;
    }

    function extract_unit($string, $start, $end) {
        $pos = stripos($string, $start);
        $str = substr($string, $pos);
        $str_two = substr($str, strlen($start));
        $second_pos = stripos($str_two, $end);
        $str_three = substr($str_two, 0, $second_pos);
        $unit = trim($str_three); // remove whitespaces
        return $unit;
    }

    /**
     * Trasforma un file xhtml itaEngine in pdf
     * 
     * @param type $bodyValue
     * @param type $dictionaryValues - in codifica ISO
     * @param type $outpufile
     * @return boolean|string
     */
    function Xhtml2Pdf($bodyValue, $dictionaryValues = array(), $outpufile = '', $pdfA = true, $extraParam = array()) {
        if (!$bodyValue) {
            return false;
        }
        //
        // recupero il testo base di provenienza
        //
        $tmpArr = explode("<!-- itaTestoBase:", $bodyValue);
        $tmpStr = $tmpArr[1];
        $tmpArr = explode(" -->", $tmpStr);
        $testoBase = $tmpArr[0];

        $tmpArr = explode("<!-- itaModelloXhtml:", $bodyValue);
        $tmpStr = $tmpArr[1];
        $tmpArr = explode(" -->", $tmpStr);
        $modelloXhtml = $tmpArr[0];
        $documenti_rec = $this->getDocumenti($testoBase);
        $documenti_rec['CONTENT'] = $bodyValue;
        $unserMetadata = unserialize($documenti_rec['METADATI']);
        if ($unserMetadata['MODELLOXHTML'] == 'PERSONALIZZATO' && !$modelloXhtml) {
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
            if ($modelloXhtml) {
                $codiceLayout = $modelloXhtml;
            } else {
                $codiceLayout = $unserMetadata['MODELLOXHTML'];
            }
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


        /// qui smarty header footer content


        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $bodyValue);
        $itaSmarty->assign('documentheader', $headerContent);
        $itaSmarty->assign('documentfooter', $footerContent);
        $itaSmarty->assign('headerHeight', $marginHeader);
        $itaSmarty->assign('footerHeight', $marginFooter);
        $itaSmarty->assign('marginTop', $marginTop);
        $itaSmarty->assign('marginBottom', $marginBottom);
        $itaSmarty->assign('marginLeft', $marginLeft);
        $itaSmarty->assign('marginRight', $marginRight);
        $itaSmarty->assign('pageFormat', $format);
        $itaSmarty->assign('pageOrientation', $orientation);
        //eventuali parametri extra
        foreach ($extraParam as $campo => $valore) {
            $itaSmarty->assign($campo, $valore);
        }

        $tempPath = itaLib::getAppsTempPath();
        if (!is_dir($tempPath)) {
            $tempPath = itaLib::createAppsTempPath();
            if (!is_dir($tempPath)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallito");
                return false;
            }
        }
        $documentLayout = $tempPath . '/documentlayout.xhtml';
        //$documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';

        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layoutTemplate.xhtml";
        if (!@copy($layoutTemplate, $documentLayout)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia template layout Fallita");
            return false;
        }

        //$contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
        $contentPreview = $itaSmarty->fetch($documentLayout);
        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
        if (!file_put_contents($documentPreview, $contentPreview)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione $documentPreview Fallita");
            return false;
        }
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
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
        $pdfPreview = itaLib::getAppsTempPath() . '/pdfPreview.pdf';
        @unlink($pdfPreview);
        $randName = md5(rand() . microtime());
        $pdfOutput = itaLib::getAppsTempPath() . '/' . $randName . '-pdf-output.pdf';
        @unlink($pdfOutput);

        if (!file_put_contents($documentPreview2, $this->brConvertTag($contentPreview2))) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione $documentPreview Fallita");
            return false;
        }

        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $documentPreview2 . ' ' . $pdfPreview;
        exec($command, $output, $ret);

        if ($ret == 1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione $pdfPreview Fallita:  <br/>" . print_r($output[0], true));
            return false;
        }

        if (!$outpufile) {
            $outpufile = $pdfOutput;
        }

        if ($pdfA) {
            $ret_pdfa = $this->convertiPDFA($pdfPreview, $outpufile);
            if ($ret_pdfa['status'] != 0) {
                //Out::msgStop("Errore", "Spostamento $pdfPreview -> $outpufile Fallita<br>**** " . $ret_pdfa['status']);
                //return false;
            }
            @unlink($pdfPreview);
        } else {
            @rename($pdfPreview, $outpufile);
        }

        //Unlink dei file
        if (!@unlink($documentPreview)) {
//            Out::msgStop("Attenzione", "Errore nella cancellazione del file temporaneo: " . $documentPreview);
        }
        if (!@unlink($documentPreview2)) {
//            Out::msgStop("Attenzione", "Errore nella cancellazione del file temporaneo: " . $documentPreview2);
        }
        if (!@unlink($documentLayout)) {
//            Out::msgStop("Attenzione", "Errore nella cancellazione del file temporaneo: " . $documentLayout);
        }
        return $outpufile;
    }

    private function getUnoconvEnvParam($key, $default = false) {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $envconf_rec = $devLib->getEnv_config('PDFUNOCONV', 'codice', strtoupper($key), false);
        return $envconf_rec ? $envconf_rec['CONFIG'] : $default;
    }

    /**
     * Trasforma un file DOCX in PDF
     * 
     * @param String $inputfile Path del file in input
     * @param String $outputfile Path del file in output
     * @param Array $params Parametri aggiuntivi chiave => valore.<br>
     * Chiavi supportate: envpath, quality (1-100), pdfa (0-1), lossless (0-1),
     * pagerange (X-X singola, X-Y intervallo, X,Y singole), verbose (0-9).<br>
     * Documentazione parametri: https://wiki.openoffice.org/wiki/API/Tutorials/PDF_export
     * @param Boolean $overwrite Sovrascrive o meno il file di destinazione
     * @return Boolean
     */
    public function docx2Pdf($inputfile, $outputfile = false, $params = array(), $overwrite = true) {
        if (!file_exists($inputfile)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il file di origine non esiste');
            return false;
        }

        /*
         * Valorizzazione parametri
         */

        $defaults = array(
            'envpath' => '',
            'quality' => '90',
            'pdfa' => '1',
            'lossless' => '0',
            'verbose' => '0',
            'listener' => '0',
            'port' => 0
        );

        foreach ($defaults as $k => $v) {
            $defaults[$k] = $this->getUnoconvEnvParam($k, $v);
        }

        $p = array_merge($defaults, $params);

        /*
         * Gestione output file
         */

        if (!$outputfile) {
//            $outputfile = pathinfo($inputfile, PATHINFO_DIRNAME) . '/' . pathinfo($inputfile, PATHINFO_FILENAME) . '.pdf';
            $outputfile = itaLib::getAppsTempPath() . '/' . md5(rand() . microtime()) . '.pdf';
        }

        if (file_exists($outputfile)) {
            if (!$overwrite) {
                $this->setErrCode(-1);
                $this->setErrMessage('Il file di destinazione è già presente');
                return false;
            }

            if (!unlink($outputfile)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Il file di destinazione è già presente e non è possibile eliminarlo');
                return false;
            }
        }
        
        if(!is_dir(ITA_BASE_PATH . '/var/unoconv')){
            mkdir(ITA_BASE_PATH . '/var/unoconv', 0777, true);
        }
            
        $logPath = ITA_BASE_PATH . '/var/unoconv/unoconv.log';
        $logger = new itaPHPLogger('docx2pdf', false);
        $logger->pushFile($logPath);
        
        $ret_lck = ItaDB::DBLock($this->getITALWEB(), "DOCUMENTI_CONVERT",1, "", 3600, 60);
        $cnt = 0;
        if(file_exists(ITA_BASE_PATH . '/var/unoconv/cnt')){
            $cnt = file_get_contents(ITA_BASE_PATH . '/var/unoconv/cnt');
            if($cnt > self::MAX_CONVERSIONS){
                $logger->debug("Restart unoconv - Max conversions - User: " . App::$utente->getkey("nomeUtente"));
                $this->restartSoffice();
                $cnt = 0;
            }
        }
        file_put_contents(ITA_BASE_PATH . '/var/unoconv/cnt', $cnt+1);
        
        /*
         * Comando
         */
        $unoconvPath = $this->getUnoconvPath();
        $cmd = "$unoconvPath -f pdf -o $outputfile -e Quality={$p['quality']} -e SelectPdfVersion={$p['pdfa']} -e UseLosslessCompression={$p['lossless']}";

        /*
         * Parametri opzionali
         */
        if($p['listener'] == 1){
            $cmd .= " -n";
        }
        if(!empty($p['port'])){
            $cmd .= " -p " . $p['port'];
        }
        if ($p['verbose'] && $p['verbose'] != '0') {
            $cmd .= " -" . str_repeat('v', $p['verbose']);
        }

        if ($p['pagerange']) {
            $cmd .= " -e PageRange={$p['pagerange']}";
        }
        
        /*
         * Esecuzione comando
         */
        $uniq = uniqid();
        $uniq.= ' - in: '.$inputfile;
        $uniq.= ' - out: '.$outputfile;
        $uniq.= ' - cnt: '.$cnt;
        $logger->debug("Conversion - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq);
        
        $this->fixPermissions();
        
        exec("$cmd $inputfile", $output, $result);
        if(file_exists($outputfile) && $result != 0){
            $logger->error("Conversion with error - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq);
        }
        if($p['listener'] == 1 && !file_exists($outputfile)){
            if($result == 113){
                $this->startSoffice();
                $logger->warning("Start unoconv - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq);
                sleep(10);
            }
            for($i=0; $i<10; $i++){
                exec("$cmd $inputfile", $output, $result);
                switch($result){
                    case 113:
                        sleep(3);
                        break;
                    case 5:
                    case 6:
                        $this->restartSoffice();
                        $logger->warning("Kill/Restart unoconv - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq." - Result: ".$result);
                        break;
                    default:
                        break 2;
                }
            }
            if($result != 0){
                $logger->error("Error unoconv - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq." - Result: ".$result);
            }
        }
        if (!file_exists($outputfile) /* && $result != 0 */) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore '.$result.' nella conversione del file' . '<br><br>' . implode('<br>', $output));
            $logger->error("Error unoconv - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq." - Description: ".'Errore '.$result.' nella conversione del file' . '<br><br>' . implode('<br>', $output));
        
            ItaDB::DBUnLock($ret_lck['lockID'], $this->getITALWEB());
            return false;
        }
        $logger->debug("End conversion - User: " . App::$utente->getkey("nomeUtente") ." Id: ". $uniq);
        
        ItaDB::DBUnLock($ret_lck['lockID'], $this->getITALWEB());
        return $outputfile;
    }
    
    private function getUnoconvPath(){
        $envpath = $this->getUnoconvEnvParam('envpath', false);
        if($envpath){
            preg_match('/^((?:(?:HOME|UNO_PATH)=(?:(?:".*?")|(?:[^\s]*))(?:\s*(?:HOME|UNO_PATH)=(?:(?:".*?")|(?:[^\s]*)))?)|(?:(?:.*?)python(?:.exe)?))/i', $envpath, $matches);
            $pythonPath = $matches[1] ?: '';
            $unoconvPath = ITA_LIB_PATH . '/bin/unoconv';
            
            return $pythonPath . ' ' . $unoconvPath;
        }
        return false;
    }
    
    private function startSoffice(){
        $unoconvPath = $this->getUnoconvPath();
        if(!empty($unoconvPath)){
            $params = array(
                '-l',
                '-r "' . ITA_BASE_PATH . '/var/unoconv"'
            );
            $port = $this->getUnoconvEnvParam('port', false);
            if(!empty($port)){
                $params[] = '-p '.$port;
            }
            itaLib::execAsync($unoconvPath, $params, '');
        }
    }
    
    public function restartSoffice(){
        $path = ITA_BASE_PATH . '/var/unoconv/soffice.pid';
        
        if(file_exists($path)){
            $pid = file_get_contents($path);
            itaLib::killProcess($pid);
            file_put_contents(ITA_BASE_PATH . '/var/unoconv/cnt', 0);
            sleep(10);
        }
        $this->startSoffice();
        sleep(10);
    }
    
    private function fixPermissions(){
        if(!itaLib::isWindows()){
            $unoconvPath = $unoconvPath = ITA_LIB_PATH . '/bin/unoconv';
            if(!file_exists($unoconvPath)){
                return false;
            }
            
            $perms = substr(decoct(fileperms($unoconvPath) & 0777), -3);
            if($perms != 0755){
                chmod($unoconvPath, 0755);
            }
        }
    }

//    private function normalizzaVariabili($v) {
//         $type = "text/plain";
//        if (strpos(trim($v), 'Content-type: ') === 0) {
//            list($header, $v) = explode("\n", $v);
//            list($skip, $type) = explode(": ", $header);
//        }
//        switch ($type) {
//            case "text/html":
//                return $v;
//                break;
//            case "text/plain":
//            default:
//                return htmlspecialchars($v);
//                break;
//        }
//    }

    private function normalizzaVariabili($v) {
        $type = "text/plain";
        if (strpos(trim($v), 'Content-type: ') === 0) {
            list($header, $xx) = explode("\n", $v);
            list($skip, $type) = explode(": ", $header);
        }

        switch ($type) {
            case "text/html":
                list($skip, $v) = explode("Content-type: text/html\n", $v);
                $src_arr = array("&amp;", "&", "&amp;#");
                $rep_arr = array("&", "&amp;", "&#");
                $v = str_replace($src_arr, $rep_arr, $v);
                return $v;
                break;
            case "text/plain":
            default:
                return htmlspecialchars($v, ENT_COMPAT, 'ISO-8859-1');
                break;
        }
    }

    public function convertiPDFA($fileName, $outputFile, $deleteFileName = false) {
        //if ($fileName == $outpuFile) {
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
        $praLib = new praLib();
        $flag = $praLib->getFlagPDFA();
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

    function compilaDocumentoBase($codice, $valori, $outputfile, $autoopen = false, $forceDownload = false) {
        $documenti_rec = $this->getDocumenti($codice);
        if (!$documenti_rec) {
            return false;
        }
        if (gettype($valori) == "object") {
            if (get_class($valori) == 'itaDictionary') {
                $dictionaryValues = $valori->getAllData();
            } else {
                return false;
            }
        } else {
            if (gettype($valori) == "array") {
                $dictionaryValues = $valori;
            } else {
                return false;
            }
        }

        switch ($documenti_rec['TIPO']) {
            case "JREPORT":
//
// Normalizzare array per parametri jreport
//
                if (gettype($valori) == "object") {
                    if (get_class($valori) == 'itaDictionary') {
                        $dictionaryValues = $valori->getAllDataPlain();
                    } else {
                        return false;
                    }
                }
                $dictionaryValues['CODICE'] = $codice;
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $report = $itaJR->getSQLReportPDF($this->getITALWEB(), $documenti_rec['URI'], $dictionaryValues);
                if (!$report) {
                    Out::msgStop("Attenzione!!!", "Errore creazione report PDF");
                    return false;
                }
                $ptr = fopen($outputfile, 'wb');
                fwrite($ptr, $report);
                fclose($ptr);

                if ($autoopen) {
                    Out::openDocument(utiDownload::getUrl(
                                    App::$utente->getKey('TOKEN') . "-preview.pdf", $outputfile
                            )
                    );
                } else {
                    return $outputfile;
                }
                break;
            case "XHTML":
                $outXHTML = $this->compilaXHTMLFromDocumentiRec($documenti_rec, $dictionaryValues);
                $outPDF = $this->getPDFFromXHTML($outXHTML, $outputfile, $autoopen);
                return $outPDF;
                break;
            case "DOCX":
                $outDOCX = $this->compileDOCX($this->getFilePath($documenti_rec), $dictionaryValues, $outputfile);
                //$outPDF = $this->getPDFFromXHTML($outXHTML, $outputfile, $autoopen);
                return $outDOCX;
                break;
            default:
                $template = $this->getFilePath($documenti_rec);
                $fileName = $codice . "-" . time() . "." . pathinfo($template, PATHINFO_EXTENSION);
                $itaSmarty = new itaSmarty();
                $itaSmarty->force_compile = true;
                foreach ($valori as $key => $value) {
                    $itaSmarty->assign($key, $value);
                }
                try {
                    $output = $itaSmarty->fetch($template, false);
                } catch (Exception $exc) {
                    out::msgStop("", $exc->getMessage());
                }
                $filePath = itaLib::createAppsTempPath('compilaDocumentoBase') . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
                $handler = fopen($filePath, 'w');
                if (fwrite($handler, $output)) {
                    fclose($handler);
                    Out::openDocument(utiDownload::getUrl(
                                    $fileName, $filePath, $forceDownload
                            )
                    );
                    return true;
                } else {
                    fclose($handler);
                    return false;
                }
//                $url = "http://" . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . App::$utente->getKey('privUrl') . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
//                $handler = fopen($filePath, 'w');
//                if (fwrite($handler, $output)) {
//                    Out::openDocument($url);
//                    return true;
//                } else {
//                    return false;
//                }
                break;
        }
    }

    function compilaXHTMLFromDocumentiRec($documenti_rec, $dictionaryValues = array(), $extraParam = array()) {
        if (!$documenti_rec) {
            return false;
        }
        //
        // Preparo le variabili di layout dal documenti_rec
        //
        $bodyValue = $documenti_rec['CONTENT'];
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
            $Doc_documenti_rec = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, False);
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

        //
        // Compilo il layout
        //
        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $bodyValue);
        $itaSmarty->assign('documentheader', $headerContent);
        $itaSmarty->assign('documentfooter', $footerContent);
        $itaSmarty->assign('headerHeight', $marginHeader);
        $itaSmarty->assign('footerHeight', $marginFooter);
        $itaSmarty->assign('marginTop', $marginTop);
        $itaSmarty->assign('marginBottom', $marginBottom);
        $itaSmarty->assign('marginLeft', $marginLeft);
        $itaSmarty->assign('marginRight', $marginRight);
        $itaSmarty->assign('pageFormat', $format);
        $itaSmarty->assign('pageOrientation', $orientation);
        //eventuali parametri extra
        foreach ($extraParam as $campo => $valore) {
            $itaSmarty->assign($campo, $valore);
        }

        $documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';
        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layoutTemplate.xhtml";
        if (!copy($layoutTemplate, $documentLayout)) {
            Out::msgStop("Errore", "Copia template layout Fallita");
            return;
        }
        $contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
        unlink($documentLayout);

        //
        // Compilo xhtml con i dati applicativi
        //
        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
        if (!file_put_contents($documentPreview, $contentPreview)) {
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return;
        }
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            if (is_array($valore)) {
                foreach ($valore as $key1 => $value) {
                    $valore[$key1] = $this->normalizzaVariabili($value);
                }
            } else {
                $valore = $this->normalizzaVariabili($valore);
            }
            $itaSmarty->assign($key, $valore);
        }
        $contentPreview2 = utf8_encode($itaSmarty->fetch($documentPreview));
        unlink($documentPreview);
        return $contentPreview2;
    }

    function getPDFFromXHTML($xhtml, $outputfile, $autoOpen = false) {
        $XHTMLFile = itaLib::getAppsTempPath() . '/documentpreview2.xhtml';
        $pdfPreview = itaLib::getAppsTempPath() . '/documentpreview.pdf';
        if (!file_put_contents($XHTMLFile, $xhtml)) {
            Out::msgStop("Errore", "Creazione $XHTMLFile Fallita");
            return false;
        }

        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $XHTMLFile . ' ' . $pdfPreview;
        passthru($command, $return_var);
// Cortocircuitato perche' non serve (Michele Moscioni)
// Aggiunto su connections.ini su [PRAM]: 
//   realname = svil_PRAM
//   
//        if ($outputfile) {
//            $ret_pdfa = $this->convertiPDFA($pdfPreview, $outputfile);
//            if ($ret_pdfa['status'] != 0) {
//                Out::msgStop("Errore", "Spostamento $pdfPreview -> $outputfile Fallita<br>**** " . $ret_pdfa['status'] . " - " . $ret_pdfa['message']);
//                return false;
//            }
//        } else {
        $outputfile = $pdfPreview;
//        }
        if ($autoOpen) {
            Out::openDocument(utiDownload::getUrl(
                            App::$utente->getKey('TOKEN') . "-preview.pdf", $outputfile
                    )
            );
        } else {
            return $outputfile;
        }
    }

    /**
     * helper per analisi firma
     * 
     * @param type $file
     * @return boolean
     */
    public function AnalizzaP7m($file) {
        include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
        $p7m = itaP7m::getP7mInstance($file);
        if (!$p7m) {
            Out::msgStop("Inserimento File Firmato", "Verifica Fallita");
            return false;
        }
        if ($p7m->isFileVerifyPassed()) {
            Out::msgInfo("verifica Firme", "Firma verificata");
            $p7m->cleanData();
            return true;
        } else {
            $signErrors = $p7m->getMessageErrorFileAsString();
            $signErrors .= $p7m->getMessageErrorFileAsString();
            Out::msgStop("Firma non verificata", $p7m->getMessageErrorFileAsString());
            $p7m->cleanData();
            return false;
        }
    }

    /**
     * helper per verifica pdfa
     * 
     * @param type $fileName
     * @return string
     */
    public function verificaPDFA($fileName) {
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
            return $ret;
        }
        $ret = itaPDFAUtil::verifyPDFSimple($fileName, 2, $PDFLevel);
        return $ret;
    }

    /**
     * path standard per documenti
     * 
     * @param type $tipo
     * @param type $crea
     * @return boolean|string
     */
    public function setDirectory($tipo = 'base', $crea = true) {
        $docPath = Config::getPath('general.fileEnte') . "ente" . App::$utente->getKey('ditta') . "/documenti/";
        if (!is_dir($docPath)) {
            if (!@mkdir($docPath, 0777, true)) {
                return false;
            }
        }
        return $docPath;
    }

    /**
     * helper per lancio visualizza firme
     * 
     * @param type $file
     * @param type $fileORiginale
     */
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

    public function CopiaTesto($sorgente, $destino, $tipoTesto, $perms = 0777) {
        $ext = strtolower(pathinfo($sorgente, PATHINFO_EXTENSION));
        if (!file_exists($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il file sorgente non esiste. ($sorgente)");
            return false;
        }

        if (!@copy($sorgente, $destino)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il Testo non è stato creato correttamente.');
            return false;
        }

        if (!@chmod($destino, $perms)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il Testo non è stato creato correttamente. Errore in assegnazione permessi di scrittura.');
            return false;
        }

        if ($ext == "htm") {
            $sorgente_file = pathinfo($sorgente, PATHINFO_DIRNAME) . "/" . pathinfo($sorgente, PATHINFO_FILENAME) . "_file";
            $destino_file = pathinfo($destino, PATHINFO_DIRNAME) . "/" . pathinfo($destino, PATHINFO_FILENAME) . "_file";
            if (!$this->CopiaCartella($sorgente_file, $destino_file, $perms)) {
                return false;
            }
            $datiTesto = file_get_contents($destino);
            if ($datiTesto) {
                $cerca = pathInfo($sorgente_file, PATHINFO_BASENAME);
                $cambia = pathInfo($destino_file, PATHINFO_BASENAME);
                $datiTesto = str_replace($cerca, $cambia, $datiTesto);
                if (!file_put_contents($destino, $datiTesto)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Il Testo non è stato creato correttamente.');
                    return false;
                }
            }
        }

        return $destino . $ext;
    }

    public function SpostaTesto($sorgente, $destino, $tipoTesto, $perms = 0777) {
        $ext_file = strtolower(pathinfo($sorgente, PATHINFO_EXTENSION));

        if ($ext_file == 'htm') {
            $sorgente = pathinfo($sorgente, PATHINFO_DIRNAME) . "/" . pathinfo($sorgente, PATHINFO_FILENAME);
            $destino = pathinfo($destino, PATHINFO_DIRNAME) . "/" . pathinfo($destino, PATHINFO_FILENAME);
            $tipoTesto = "word-html";
        }

        if ($tipoTesto == "word-html") {
            $ext = ".htm";
        }

        if (!file_exists($sorgente . $ext)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il file sorgente non esiste. ($sorgente$ext)");
            return false;
        }

        if (!@rename($sorgente . $ext, $destino . $ext)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il Testo non è stato spostato correttamente. (1)');
            return false;
        }

        if (!@chmod($destino . $ext, $perms)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il Testo non è stato spostato correttamente. Errore in assegnazione permessi di scrittura. (2)');
//            return false;
        }

        if ($tipoTesto == "word-html") {
            $sorgente_file = $sorgente . "_file";
            $destino_file = $destino . "_file";
            if (!$this->SpostaCartella($sorgente_file, $destino_file, $perms)) {
                return false;
            }
            $datiTesto = file_get_contents($destino . $ext);
            if ($datiTesto) {
                $cerca = pathInfo($sorgente_file, PATHINFO_BASENAME);
                $cambia = pathInfo($destino_file, PATHINFO_BASENAME);
                $datiTesto = str_replace($cerca, $cambia, $datiTesto);
                if (!file_put_contents($destino . $ext, $datiTesto)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Il Testo non è stato spostato correttamente. (3)');
                    return false;
                }
            }
        }

        return $destino . $ext;
    }

    private function CopiaCartella($sorgente, $destino, $perms = 0777) {
        if (!file_exists($sorgente)) {
            return true;
        }
        if (!is_dir($destino)) {
            @mkdir($destino);
            if (!@chmod($destino, $perms)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Il Testo non è stato creato correttamente. Errore in assegnazione permessi di scrittura. (4)');
                return false;
            }
        }
        $dh = opendir($sorgente);
        while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_dir("$sorgente/$file")) {
                    $this->CopiaCartella("$sorgente/$file", "$destino/$file", $perms);
                } else {
                    @copy("$sorgente/$file", "$destino/$file");
                    if (!@chmod("$destino/$file", $perms)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Il Testo non è stato creato correttamente. Errore in assegnazione permessi di scrittura. (5)');
                        closedir($dh);
                        return false;
                    }
                }
            }
        }
        closedir($dh);
        return true;
    }

    private function SpostaCartella($sorgente, $destino, $perms = 0777) {
        if (!file_exists($sorgente)) {
            return true;
        }
        if (!is_dir($destino)) {
            @mkdir($destino);
            if (!@chmod($destino, $perms)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Il Testo non è stato creato correttamente. Errore in assegnazione permessi di scrittura. (6)');
                return false;
            }
        }
        $dh = opendir($sorgente);
        while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_dir("$sorgente/$file")) {
                    $this->SpostaCartella("$sorgente/$file", "$destino/$file", $perms);
                } else {
                    @rename("$sorgente/$file", "$destino/$file");
                    if (!@chmod("$destino/$file", $perms)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Il Testo non è stato creato correttamente. Errore in assegnazione permessi di scrittura. (7)');
                        closedir($dh);
                        return false;
                    }
                }
            }
        }
        closedir($dh);
        @rmdir($sorgente);
        return true;
    }

    public function ProprietarioTesto($sorgente) {
        if (!file_exists($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il file sorgente non esiste');
            return false;
        }

        clearstatcache();

        /*
         * 
         */
//        return fileowner($sorgente);
        return true;
    }

    public function ImprontaTesto($sorgente, $algo = 'sha256') {
        if (!file_exists($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il file sorgente non esiste');
            return false;
        }
        clearstatcache();
        return hash_file($algo, $sorgente);
    }

    /**
     * 
     * @param type $sorgente
     * @param type $tipoTesto
     * @param type $permessi
     * @param string $dirAppoggio Cartella di appoggio (es. '/a/b/'). A fine<br>
     * operazione la cartella verrà eliminata automaticamente.
     * @return boolean
     */
    public function CambiaPermessiTesto($sorgente, $tipoTesto, $permessi = docLib::PERM_READONLY, $dirAppoggio = false) {
        $ext = pathinfo($sorgente, PATHINFO_EXTENSION);

        $tmp = pathinfo($sorgente, PATHINFO_DIRNAME) . '/' . pathinfo($sorgente, PATHINFO_FILENAME) . '_' . App::$utente->getKey('TOKEN') . "." . $ext;

//        if (!$dirAppoggio) {
//            $tmp = itaLib::createAppsTempPath('APPOGGIO_DOC') . DIRECTORY_SEPARATOR . pathinfo($sorgente, PATHINFO_BASENAME);
//        } else {
//            if (!is_dir($dirAppoggio)) {
//                if (!@mkdir($dirAppoggio, 0777, true)) {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage('Impossibile creare la cartella di appoggio');
//                    return false;
//                }
//            }
//            
//            @chmod($dirAppoggio, 0777);
//
//            $tmp = $dirAppoggio . pathinfo($sorgente, PATHINFO_BASENAME);
//        }

        if (!file_exists($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il file sorgente non esiste');
            return false;
        }

        if (!$this->CopiaTesto($sorgente, $tmp, $tipoTesto)) {
            return false;
        }

        if (!$this->CancellaTesto($sorgente, $tipoTesto)) {
            /*
             * Ricopio il testo nella posizione originale se la cancellazioe non
             * va a buon fine (es. viene eliminato l'.htm ma non la sua cartella)
             */
            if (!$this->CopiaTesto($tmp, $sorgente, $tipoTesto)) {
                return false;
            }

            return false;
        }

//        if (!$this->SpostaTesto($sorgente, $tmp, $tipoTesto)) {
//            return false;
//        }

        if (!$this->CopiaTesto($tmp, $sorgente, $tipoTesto)) {
            return false;
        }

        if (!$this->CancellaTesto($tmp, $tipoTesto)) {
            return false;
        }

        if (!chmod($sorgente, $permessi)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il proprietario del Testo non è stato cambiato correttamente. Errore in assegnazione permessi (1)');
            return false;
        }

        if ($ext == 'htm') {
            $cartella = pathinfo($sorgente, PATHINFO_DIRNAME) . "/" . pathinfo($sorgente, PATHINFO_FILENAME) . "_file";
            if (!$this->CambiaPermessiCartella($cartella, $permessi)) {
                return false;
            }
        }

//        if (!$dirAppoggio) {
//            itaLib::deleteAppsTempPath('APPOGGIO_DOC');
//        } else {
//            rmdir($dirAppoggio);
//        }

        return $this->ProprietarioTesto($sorgente, $tipoTesto);
    }

    private function CambiaPermessiCartella($sorgente, $permessi = docLib::PERM_READONLY) {
        if (!file_exists($sorgente)) {
            return true;
        }
//        if (!chown($sorgente, get_current_user())) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("I permessi di $sorgente non sono stati cambiati correttamenti. Errore in assegnazione permessi");
//            return false;
//        }

        if (!chmod($sorgente, $permessi)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il proprietario di $sorgente non è stato cambiato correttamente. Errore in assegnazione permessi ");
            return false;
        }

        $dh = opendir($sorgente);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($sorgente . '/' . $file)) {
                    if (!$this->CambiaPermessiCartella($sorgente . '/' . $file)) {
                        return false;
                    }
                } else {
//                    if (!chown($sorgente . '/' . $file, get_current_user())) {
//                        $this->setErrCode(-1);
//                        $this->setErrMessage('Il proprietario non è stato cambiato correttamente. Errore in assegnazione permessi (2)');
//                        return false;
//                    }

                    if (!chmod($sorgente . '/' . $file, $permessi)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('I permessi del Testo non sono stati cambiati correttamenti. Errore in assegnazione permessi (2)');
                        return false;
                    }
                }
            }
        }
        closedir($dh);
        return true;
    }

    public function CancellaTesto($sorgente, $tipoTesto) {
        $ext = strtolower(pathinfo($sorgente, PATHINFO_EXTENSION));
        if (!file_exists($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il file sorgente non esiste. ($sorgente)");
            return false;
        }

        if (!unlink($sorgente)) {
            $this->setErrCode('E1');
            $this->setErrMessage('Il Testo non è stato cancellato correttamente. Errore in eliminazione file (1)');
            return false;
        }
        if ($ext == 'htm') {
            $cartella = pathinfo($sorgente, PATHINFO_DIRNAME) . '/' . pathinfo($sorgente, PATHINFO_FILENAME) . '_file';
            if (!$this->CancellaCartella($cartella)) {
                return false;
            }
        }

        return true;
    }

    private function CancellaCartella($sorgente) {
        if (!file_exists($sorgente)) {
            return true;
        }

        $dh = opendir($sorgente);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($sorgente . '/' . $file)) {
                    if (!$this->CancellaCartella($sorgente . '/' . $file)) {
                        return false;
                    }
                } else {
                    if (!unlink($sorgente . '/' . $file)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Il Testo non è stato cancellato correttamente. Errore in eliminazione file (2)');
                        return false;
                    }
                }
            }
        }
        closedir($dh);
        if (!rmdir($sorgente)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il Testo non è stato cancellato correttamente. Errore in eliminazione cartella (1)');
            return false;
        }
        return true;
    }

    /**
     * Apre un documento di tipo WORD-htm con applet java
     * @param type $percorso
     * @param type $file
     */
    public function CaricaApplet($percorso, $file) {
//        if (App::$utente->getKey('idUtente') == 10027) {
        $this->ApriDocumento($percorso, $file);
        return true;
//        }

        $ip = App::getPath('general.itaIp');
        $appletPath = addslashes('\\\\' . $ip . '\\' . $percorso . '\\') . $file;
        Out::codice("$('#appletIFrame').contents().find('#itaRunner')[0].openWordFromJs('" . $appletPath . "');");
    }

    public function brConvertTag($str) {
        $patterns = array();
        $replacements = array();
        $patterns[] = preg_quote('/<br>/');
        $replacements[] = '<br />';
        return preg_replace($patterns, $replacements, $str);
    }

    public function ApriDocumento($percorso, $file) {
        $ip = App::getPath('general.itaIp');
        $file_path = addslashes('\\\\' . $ip . '\\' . $percorso . '\\') . $file;
        include_once ITA_LIB_PATH . '/itaPHPCore/itaShellExec.class.php';

        $ext_tmp = pathinfo($file_path, PATHINFO_EXTENSION);
        $CmdOpenDoc = $this->GetComandoAperturaDoc($ext_tmp);
        itaShellExec::shellExecAlt($CmdOpenDoc['CMD'], $CmdOpenDoc['ARG'] . ' "' . $file_path . '"', 'itaEngine', 'envCheckAgent', '', 'returnSmartAgent');
    }

    public function GetComandoAperturaDoc($ext) {
        $CmdOpenDoc = array();
        $ext = strtoupper(str_replace('.', '', $ext));
        $CmdOpenDoc = $this->GetComandoFromExt($ext);
        if (!$CmdOpenDoc['CMD']) {
            $CmdOpenDoc = $this->GetComandoFromExt('*');
        }
        if (!$CmdOpenDoc['CMD']) {
            $CmdOpenDoc['CMD'] = 'start';
            $CmdOpenDoc['ARG'] = 'winword.exe';
        }
        return $CmdOpenDoc;
    }

    public function GetComandoFromExt($ext) {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $CmdOpenDoc = array();
        $OpenExt_rec = $devLib->getEnv_config('OPENDOCEXT', 'codice', $ext, false);
        if ($OpenExt_rec) {
            $OpenCmd_rec = $devLib->getEnv_config('OPENDOCCMD', 'codice', $OpenExt_rec['CONFIG'], false);
            $ConfigCmd = unserialize($OpenCmd_rec['CONFIG']);
            $Argomenti = explode(' ', trim($ConfigCmd['COMANDO']));
            $CmdOpenDoc['CMD'] = $Argomenti[0];
            $CmdOpenDoc['ARG'] = $Argomenti[1];
        }
        return $CmdOpenDoc;
    }

    public function GetStringDecode($dictionaryValues, $string) {
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $tmpPath = itaLib::getAppsTempPath();
        $documentoTmp = $tmpPath . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.tpl';
        if (!$this->writeFile($documentoTmp, $string)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore scrittura su: $documentoTmp<br>Creazione modello oggetto da parametri fallita.");
            return false;
        }
        $contenuto = $itaSmarty->fetch($documentoTmp);
        @unlink($documentoTmp);
        return $contenuto;
    }

    public function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!fwrite($fpw, $string)) {
            fclose($fpw);
            return false;
        }
        fclose($fpw);
        return true;
    }

    public function getDocMapAnag($id) {
        return ItaDB::DBSQLSelect($this->getITALWEB(), "SELECT * FROM DOC_MAP_ANAG WHERE ROW_ID = '$id'", false);
    }

    public function getDocMapVoci($id, $tipo = 'rowid') {
        switch ($tipo) {
            default:
            case 'rowid':
                return ItaDB::DBSQLSelect($this->getITALWEB(), "SELECT * FROM DOC_MAP_VOCI WHERE ROW_ID = '$id'", false);

            case 'mappatura':
                return ItaDB::DBSQLSelect($this->getITALWEB(), "SELECT * FROM DOC_MAP_VOCI WHERE ANAG_ID = '$id'", true);
        }
    }

    public function ExtraUnicode2Html($content) {
        $content = preg_replace('/%u([0-9a-f]{4})/i', '&#x$1;', $content);
        return $content;
    }

    public function utf82NumericalHTML($content) {
        if ($content == '')
            return '';
        $tmp = json_encode($content); //codifica json per convertire i caratteri utf8 in codice numerico
        $tmp = substr($tmp, 1, -1); //skip apici iniziali e finali
        $content = preg_replace('/\\\\u([0-9a-f]{4})/i', '&#x$1;', $tmp); //conversione da formato numerico ascii a formato html
        return $content;
    }

    public function ExtraHtml2Unicode($content) {
        $content = preg_replace('/&#x([0-9a-f]{4});/i', '%u$1', $content);
        return $content;
    }

    public function getDocumentoMappato($codiceTestoBase, $destFile, $mappatura = false) {
        include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

        $documenti_rec = $this->getDocumenti($codiceTestoBase);

        $percorsoFile = $this->setDirectory() . $documenti_rec['URI'];

        $codiceMappatura = $mappatura ?: $documenti_rec['MAPPATURA'];

        if ($codiceMappatura) {
            $mapDictionary = array();

            $mapanag_rec = $this->getDocMapAnag($codiceMappatura);
            $mappatura_tab = $this->getDocMapVoci($codiceMappatura, 'mappatura');
            foreach ($mappatura_tab as $mappatura_rec) {
                $mapDictionary[strtoupper($mappatura_rec['VARIABILE_EXT'])] = $mappatura_rec['VARIABILE_INT'];
            }

            /*
             * Classe modifica sintassi variabili
             */
            $itaDocumentMapping = itaDocumentMappingFactory::getMapping($mapanag_rec['TIPO_SINTASSI']);

            switch ($documenti_rec['TIPO']) {
                case "DOCX":
                    /* @var $preDocumentDocx itaDocumentDOCX */
                    $preDocumentDocx = itaDocumentFactory::getDocument('docx');

                    $preDocumentDocx->setDictionary($mapDictionary);

                    if (!$preDocumentDocx->loadContent($percorsoFile)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($preDocumentDocx->getMessage());
                        return false;
                    }

                    $content = $itaDocumentMapping->convert($preDocumentDocx->getContent(), $mapDictionary);
                    $preDocumentDocx->setContent($content);

                    if (!$preDocumentDocx->fillFormData() || !$preDocumentDocx->saveContent($destFile, true)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($preDocumentDocx->getMessage());
                        return false;
                    }
            }
        }

        return true;
    }

    /**
     * Elabora un DOCX con un dizionario.
     * 
     * @param string $inputFile Percorso del file DOCX
     * @param array $dictionaryValues Dizionario data.
     * @param string $outputFile Percorso del file di output.
     * @param string $codiceTestoBase Codice dell'eventuale testo base di riferimento.
     * Serve per prendere il riferimento alla relativa mappatura variabili.
     * @param boolean $stopOnMissingVars Se true, ferma la compilazione qualora vengano
     * incontrate variabili non mappate.
     * @return boolean|string Percorso del file compilato, o false in caso di errore.
     */
    public function compileDOCX($inputFile, $dictionaryValues, $outputFile = false, $codiceTestoBase = false, $stopOnMissingVars = false) {
        include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

        if ($outputFile === false) {
            $tempFilename = md5(microtime() . $inputFile) . '.' . pathinfo($inputFile, PATHINFO_EXTENSION);
            $outputFile = itaLib::getAppsTempPath() . '/' . $tempFilename;
        }

        if ($codiceTestoBase) {
            $documenti_rec = $this->getDocumenti($codiceTestoBase);
            if ($documenti_rec['MAPPATURA']) {
                $mapDictionary = array();

                $mapanag_rec = $this->getDocMapAnag($documenti_rec['MAPPATURA']);
                $mappatura_tab = $this->getDocMapVoci($documenti_rec['MAPPATURA'], 'mappatura');
                foreach ($mappatura_tab as $mappatura_rec) {
                    $mapDictionary[strtoupper($mappatura_rec['VARIABILE_EXT'])] = $mappatura_rec['VARIABILE_INT'];
                }

                /*
                 * Classe modifica sintassi variabili
                 */
                $itaDocumentMapping = itaDocumentMappingFactory::getMapping($mapanag_rec['TIPO_SINTASSI']);

                /* @var $preDocumentDocx itaDocumentDOCX */
                $preDocumentDocx = itaDocumentFactory::getDocument('docx');

                $preDocumentDocx->setDictionary($mapDictionary);

                if (!$preDocumentDocx->loadContent($inputFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($preDocumentDocx->getMessage());
                    return false;
                }

                $content = $itaDocumentMapping->convert($preDocumentDocx->getContent(), $mapDictionary);
                if ($stopOnMissingVars && count($itaDocumentMapping->getMissingVars())) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Sono state trovate le seguenti variabili non mappate:<br>"' . implode('"<br>"', $itaDocumentMapping->getMissingVars()) . '"');
                    return false;
                }

                $preDocumentDocx->setContent($content);

                if (!$preDocumentDocx->fillFormData() || !$preDocumentDocx->saveContent($outputFile, true)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($preDocumentDocx->getMessage());
                    return false;
                }

                if ($stopOnMissingVars && count($preDocumentDocx->getFormMissingVars())) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Sono state trovate le seguenti variabili non mappate:<br>"' . implode('"<br>"', $preDocumentDocx->getFormMissingVars()) . '"');
                    return false;
                }

                $inputFile = $outputFile;
            }
        }

        /* @var $documentDocx itaDocumentDOCX */
        $documentDocx = itaDocumentFactory::getDocument('docx');
        $documentDocx->setDictionary($dictionaryValues);
        if (!$documentDocx->loadContent($inputFile) || !$documentDocx->mergeDictionary() || !$documentDocx->saveContent($outputFile, true)) {
            $this->setErrCode(-1);
            $this->setErrMessage($documentDocx->getMessage());
            return false;
        }

        return $outputFile;
    }

    public function openOODocument($resourceRowid, $filePath, $fileName, $classificazione) {
        /*
         *  TODO: PARTE DA ASTRARRE:  
         *  1) CON SISTEMA DI APERTURA segLibAllegati
         *  2) SUCCESSIVAMENTE CON GESTIONE DOCUMENTALE TRASVERSALE
         * 
         */

        $docEditorParams = array(
            'resourceRowid' => $resourceRowid,
            'fileName' => $fileName,
            'filePath' => $filePath
        );
        $itaDocEditor = itaDocEditor::newDocEditor($docEditorParams);

        $pluginParams = array(
            'domain' => App::$utente->getKey('ditta'),
            'token' => App::$utente->getKey('TOKEN'),
            'resourceid' => $resourceRowid,
            'classificazione' => $classificazione
        );
        $itaDocEditor->addPlugin(itaOnlyOfficePluginFactory::PLUGIN_TYPE_DICTIONARY, $pluginParams);

        $itaDocEditor->openEditor();
    }

    public function getParamTipoOperatore() {
        $docParametri = itaModel::getInstance('docParametri');
        return $docParametri->getParametro('SEG_TIPO_OPERATORE');
    }

    public function checkCodice($codice) {
        $ret = array();
        $valueTipoOperatore = $this->getParamTipoOperatore();
        $prefix = substr($codice, 0, 3);
        $arrApp = Config::getPathSection('appRoute');
        if ($valueTipoOperatore == "MASTER") {
            if (isset($arrApp[$prefix])) {
                $ret["Status"] = true;
                $ret["Message"] = "Codice corretto";
            } else {
                $ret["Status"] = false;
                $ret["Message"] = "In modalità Master, le prime 3 lettere del codice, devono coincidere con un prefisso applicativo";
            }
        } else {
            if (isset($arrApp[$prefix])) {
                $ret["Status"] = false;
                $ret["Message"] = "In modalità Cliente, le prime 3 lettere del codice non devono coincidere con un prefisso applicativo";
            } else {
                $ret["Status"] = true;
                $ret["Message"] = "Codice corretto";
            }
        }
        return $ret;
    }

    public function aggiungi($documenti, $varHtml, $varFile, $aggiornaFile, $model, $skipCheckCodice = false, $allegato = "") {
        $toReturn = array(
            'COD_ERR' => 0,
            'MSG_LBL' => '',
            'MSG_ERR' => ''
        );

        //@FIXME: MANCANO PARAMETRI DI CHIAMATA
        $documenti_rec = $this->getDocumenti($documenti['CODICE']);
        if (!$documenti_rec) {
            if (!$skipCheckCodice) {
                $retCheck = $this->checkCodice($documenti['CODICE']);
                if ($retCheck['Status'] == false) {
                    $toReturn['COD_ERR'] = 1;
                    $toReturn['MSG_LBL'] = "Inserimento Testo Base";
                    $toReturn['MSG_ERR'] = $retCheck['Message'];
                    return $toReturn;
                }
            }
            $documenti_rec = $documenti;
            $documenti_rec['DATAREV'] = date('Ymd');
            $documenti_rec['NUMREV'] = 1;
            //TODO@ fai set directory
            $docPath = $this->setDirectory();
            if (!is_dir($docPath)) {
                if (!@mkdir($docPath, 0777, true)) {
                    $toReturn['COD_ERR'] = 1;
                    $toReturn['MSG_LBL'] = "Creazione cartella testi";
                    $toReturn['MSG_ERR'] = "Creazione cartella $docPath fallita";
                    return $toReturn;
                }
            }
            $nomeFile = md5($documenti['CODICE']);
            $documenti_rec['URI'] = $nomeFile . '.' . $model->arrSuffix[$documenti_rec['TIPO']];
            switch ($documenti_rec['TIPO']) {
                case 'XHTML':
                    $content = $varHtml;
                    $content = $this->ExtraUnicode2Html($content);
                    $documenti_rec['CONTENT'] = $content;
                    $documenti_rec['METADATI'] = $model->setMetadati();
                    $Nome_file = $docPath . $documenti_rec['URI'];
                    $File = fopen($Nome_file, "w+");
                    fwrite($File, $documenti_rec['CONTENT']);
                    fclose($File);
                    break;
                case "JREPORT":
                    $documenti_rec['URI'] = $varFile;
                    $documenti_rec['CONTENT'] = $varFile;
                    break;
                case "MSWORDXML":
                case "RTF":
                case "ODT":
                case "XML":
                case "TXT":
                case "PDF":
                    if ($aggiornaFile == true) {
                        $documenti_rec['CONTENT'] = $nomeFile;
                        @copy($varFile, $docPath . $documenti_rec['URI']);
                    }
                    break;
                case "MSWORDHTML":
                    $doc_ext = isset($doc_ext) ? $doc_ext : 'htm';
                case "DOCX":
                    $doc_ext = isset($doc_ext) ? $doc_ext : 'docx';
                    $documenti_rec['CONTENT'] = $nomeFile;
                    //if (!$this->CopiaTesto(ITA_BASE_PATH . '/apps/Documenti/resources/doc.' . $doc_ext, $docPath . $nomeFile . '.' . $doc_ext, 'word-html')) {
                    $sorgente = ITA_BASE_PATH . '/apps/Documenti/resources/doc.' . $doc_ext;
                    if ($allegato) {
                        $sorgente = $docPath . $allegato;
                    }
                    if (!$this->CopiaTesto($sorgente, $docPath . $nomeFile . '.' . $doc_ext, 'word-html')) {
                        $toReturn['COD_ERR'] = 1;
                        $toReturn['MSG_LBL'] = "Errore";
                        $toReturn['MSG_ERR'] = $this->getErrMessage();
                        return $toReturn;
                    }
                    break;
            }

            $insert_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
            if ($model->insertRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec, $insert_Info)) {
                $documenti_rec = $this->getDocumenti($documenti_rec['CODICE']);

                switch ($documenti_rec['TIPO']) {
                    case "MSWORDHTML":
                    case "DOCX":
                        /* @var $docParametri docParametri */
                        $docParametri = itaModel::getInstance('docParametri');

                        if (
                                !$docParametri->getParametro('SEG_MODDIR_DOCX') ||
                                ($this->FixedFields['SCARICA_DOCX'] && $documenti_rec['TIPO'] == 'DOCX')
                        ) {

                            break;
                        }
                        $docPath = Config::getPath('general.fileEnte_share') . '\\ente' . App::$utente->getKey('ditta') . '\\documenti';
                        $this->CaricaApplet($docPath, $documenti_rec['URI']);
                        break;
                }

                //aggiungo il record nello storico
                $storico_Info = 'Storico: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'] . " revisione " . $documenti_rec['NUMREV'];
                $documenti_storico = $documenti_rec;
                unset($documenti_storico['ROWID']);
                $model->insertRecord($this->ITALWEB, "DOC_STORICO", $documenti_storico, $storico_Info);
                //salvo la copia del documento documento inserendo il numero di revisione prima dell'uri
                switch ($documenti_storico['TIPO']) {
                    case 'XHTML':
                        $Nome_file = $docPath . $documenti_storico['NUMREV'] . "_" . $documenti_rec['URI'];
                        $File = fopen($Nome_file, "w+");
                        @fwrite($File, $documenti_storico['CONTENT']);
                        @fclose($File);
                        break;
                    case "JREPORT":
                        $documenti_storico['URI'] = $varFile;
                        $documenti_storico['CONTENT'] = $varFile;
                        break;
                    case "MSWORDXML":
                    case "RTF":
                    case "ODT":
                    case "XML":
                    case "TXT":
                    case "PDF":
                        $destFile = $docPath . $documenti_storico['NUMREV'] . "_" . $documenti_storico['URI'];
                        @copy($docPath . $documenti_rec['URI'], $destFile);
                        break;
                    case "MSWORDHTML":
                    case "DOCX":
                        //in questo caso la copia del documento viene effettuata quando si clicca su "Apri/Visualizza"
                        break;
                }

                $toReturn['DATA'] = $documenti_rec;
            }
        } else {
            $toReturn['COD_ERR'] = 2;
            $toReturn['MSG_LBL'] = "Codice già  presente";
            $toReturn['MSG_ERR'] = "Inserire un nuovo codice.";
        }

        return $toReturn;
    }

    public function aggiorna($documenti, $varHtml, $varFile, $aggiornaFile, $model) {
        $toReturn = array(
            'COD_ERR' => 0,
            'MSG_LBL' => '',
            'MSG_ERR' => ''
        );

        $documenti_rec = $documenti;
        $documenti_old = $this->getDocumenti($documenti_rec['CODICE']);
        $documenti_rec['DATAREV'] = date('Ymd');
        $documenti_rec['NUMREV'] += 1;
        $docPath = $this->setDirectory();
        if (!$documenti_old['URI']) {
            $nomeMD5 = md5($documenti['CODICE']);
            $documenti_old['URI'] = $documenti_rec['URI'] = $nomeMD5 . '.' . $model->arrSuffix[$documenti_rec['TIPO']];
        }
        switch ($documenti_rec['TIPO']) {
            case 'XHTML':
                $content = $varHtml;
                $content = $this->ExtraUnicode2Html($content);
                $documenti_rec['CONTENT'] = $content;
                $documenti_rec['METADATI'] = $model->setMetadati();
                $Nome_file = $docPath . $documenti_old['URI'];
                $File = fopen($Nome_file, "w+");
                fwrite($File, $documenti_rec['CONTENT']);
                fclose($File);
                break;
            case "JREPORT":
                $documenti_rec['URI'] = $varFile;
                $documenti_rec['CONTENT'] = $varFile;
                break;
            case "MSWORDXML":
            case "RTF":
            case "ODT":
            case "XML":
            case "TXT":
            case "PDF":
                if ($aggiornaFile == true) {
                    @unlink($docPath . $documenti_old['URI']);
                    $documenti_rec['URI'] = $documenti_old['CONTENT'] . '.' . $model->arrSuffix[$documenti_rec['TIPO']];
                    $documenti_rec['CONTENT'] = $documenti_old['CONTENT'] . '.' . $model->arrSuffix[$documenti_rec['TIPO']];
                    $destFile = $docPath . pathinfo($varFile, PATHINFO_BASENAME);
                    @copy($varFile, $destFile);
                }
                $documenti_rec['URI'] = pathinfo($varFile, PATHINFO_BASENAME);
                $documenti_rec['CONTENT'] = pathinfo($varFile, PATHINFO_FILENAME);
                break;
            case "MSWORDHTML":
            case "DOCX":
                break;
        }
        $update_Info = 'Aggiornamento documento: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
        if ($model->updateRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec, $update_Info)) {
            //aggiungo il record nello storico rileggendo prima il record corrente
            $documenti_rec = $this->getDocumenti($documenti_rec['ROWID'], 'rowid');
            $storico_Info = 'Storico: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'] . " revisione " . $documenti_rec['NUMREV'];
            $documenti_storico = $documenti_rec;
            unset($documenti_storico['ROWID']);
            $model->insertRecord($this->ITALWEB, "DOC_STORICO", $documenti_storico, $storico_Info);
            //salvo la copia del documento documento inserendo il numero di revisione prima dell'uri
            switch ($documenti_storico['TIPO']) {
                case 'XHTML':
                    $documenti_storico['CONTENT'] = $varHtml;
                    $documenti_storico['METADATI'] = $model->setMetadati();
                    $Nome_file = $docPath . $documenti_storico['NUMREV'] . "_" . $documenti_old['URI'];
                    $File = fopen($Nome_file, "w+");
                    @fwrite($File, $documenti_storico['CONTENT']);
                    @fclose($File);
                    break;
                case "JREPORT":
                    $documenti_storico['URI'] = $varFile;
                    $documenti_storico['CONTENT'] = $varFile;
                    break;
                case "MSWORDXML":
                case "RTF":
                case "ODT":
                case "XML":
                case "TXT":
                case "PDF":
                    $documenti_storico['URI'] = $documenti_old['CONTENT'] . '.' . $model->arrSuffix[$documenti_storico['TIPO']];
                    $destFile = $docPath . $documenti_storico['NUMREV'] . "_" . pathinfo($varFile, PATHINFO_BASENAME);
                    @copy($varFile, $destFile);
                    break;
                case "MSWORDHTML":
                case "DOCX":
                    $origFile = $docPath . $documenti_storico['CONTENT'] . '.' . $model->arrSuffix[$documenti_storico['TIPO']];
                    $destFile = $docPath . $documenti_storico['NUMREV'] . "_" . $documenti_storico['CONTENT'] . '.' . $model->arrSuffix[$documenti_storico['TIPO']];
                    if (!$this->CopiaTesto($origFile, $destFile, 'word-html')) {
                        $toReturn['COD_ERR'] = 1;
                        $toReturn['MSG_LBL'] = "Errore";
                        $toReturn['MSG_ERR'] = $this->getErrMessage();
                        return $toReturn;
                    }
                    break;
            }

            $toReturn['DATA'] = $documenti_rec;
        }

        return $toReturn;
    }

}
