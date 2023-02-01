<?php

class praLibAllegati {

    private static $praLibAllegati;
    private $praLib;
    private $errMessage;
    private $errCode;

    public static function getInstance($praLib) {
        if (!isset(self::$praLibAllegati)) {
            $obj = new praLibAllegati();
            $obj->setPraLib($praLib);

            self::$praLibAllegati = $obj;
        }

        return self::$praLibAllegati;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
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

    public function GetParametriLimitiUpload($PRAM_DB) {
        $arrayParametri = array();
        $parametriLimitiUpload = $this->praLib->GetAnapar('LIMITIUPLOAD', 'codice', $PRAM_DB, true);

        foreach ($parametriLimitiUpload as $parametro) {
            $arrayParametri[$parametro['PARKEY']] = $parametro['PARVAL'];
        }

        return $arrayParametri;
    }

    public function getStatoRapporti($dati, $itekey_rapporto = '') {
        $arrStato = array();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITEDRR'] == 1) {
                $arrRapporto = array();
                if (strpos($dati['Proric_rec']['RICSEQ'], "." . $ricite_rec['ITESEQ'] . ".") !== false) {
                    $arrRapporto['PassoEseguito'] = true;
                } else {
                    $arrRapporto['PassoEseguito'] = false;
                }
                $arrRapporto['Riferimento'] = $ricite_rec['ITEPROC'];
                $arrRapporto['Allegati'] = $this->praLib->ControllaRapportoConfig($dati, $ricite_rec);
                $arrRapporto['AllegatiMancanti'] = $this->checkAllegatiMancantiRapporto($arrRapporto['Allegati']);
                $arrRapporto['UploadCaricato'] = $this->checkStatoUploadRapporto($dati, $ricite_rec['ITEKEY']);
                $arrRapporto['Metadati'] = unserialize($ricite_rec['ITEMETA']);
                $arrStato[$ricite_rec['ITEKEY']] = $arrRapporto;
            }
        }
        return $arrStato;
    }

    function checkStatoUploadRapporto($dati, $itekeyRapporto) {
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITECTP'] == $itekeyRapporto) {
                if (strpos($dati['Proric_rec']['RICSEQ'], "." . $ricite_rec['ITESEQ'] . ".") !== false) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    function checkAllegatiMancantiRapporto($arrayPdf) {
        $manca = 0;
        krsort($arrayPdf);
        foreach ($arrayPdf as $file) {
            if ($file['FILENAME'] != '' && $file['FILEPATH'] == '') {
                $manca = $manca + 1;
            }
        }
        return $manca;
    }

    /**
     * Estrae dal passo la lista delle estensioni permesse.
     * 
     * @param Array $ricite_rec Record del passo RICITE
     */
    public function estensioniPermesse($ricite_rec) {
        $strExt = $ricite_rec['ITEEXT'];

        $arrayExt = array();
        $arrayExtTmp = array();
        if ($strExt) {
            $strExt = str_replace('||', '|', $strExt);
            $arrayExtTmp = explode('|', $strExt);
            foreach ($arrayExtTmp as $ext) {
                if ($ext) {
                    $arrayExt[] = $ext;
                }
            }
        }

        return $arrayExt;
    }

    /**
     * Controlla che l'estensione di un file sia tra quelle permesse.
     * Ritorna true se permessa, false in caso contrario.
     * 
     * @param String $filename Path del file
     * @param Array $estensioniPermesse Array di estensioni permesse
     */
    public function checkEstensione($filename, $estensioniPermesse) {
        if (!count($estensioniPermesse)) {
            return true;
        }

        $baseExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fullExtension = $baseExtension;

        if ($baseExtension === 'p7m' && $this->GetBaseExtP7MFile($filename)) {
            $fullExtension = $this->GetBaseExtP7MFile($filename) . '.p7m';
        }

        return in_array($baseExtension, $estensioniPermesse) || in_array($fullExtension, $estensioniPermesse);
    }

    /**
     * Ritorna l'estensione base di un file p7m.
     * 
     * @param type $baseFile
     * @return string
     */
    public function GetBaseExtP7MFile($baseFile) {
        $Est_baseFile = strtolower(pathinfo($baseFile, PATHINFO_EXTENSION));

        if ($Est_baseFile == "") {
            return "";
        } else {
            if ($Est_baseFile == "p7m") {
                $baseFile = pathinfo($baseFile, PATHINFO_FILENAME);
                $Est_baseFile = $this->GetBaseExtP7MFile($baseFile);
            }
        }

        return $Est_baseFile;
    }

    /**
     * Ritorna l'estensione completa di un file p7m.
     * 
     * @param type $baseFile
     * @return string
     */
    public function GetExtP7MFile($baseFile) {
        $Est = strtolower(pathinfo($baseFile, PATHINFO_EXTENSION));

        if ($Est == "") {
            return "pdf";
        } else {
            if ($Est == "p7m") {
                $baseFile = pathinfo($baseFile, PATHINFO_FILENAME);
                $Est = $this->GetExtP7MFile($baseFile) . ".$Est";
            }
        }

        return $Est;
    }

    /**
     * Carica un allegato nella cartella della richiesta online.
     * 
     * @param type $dati
     * @param type $filename
     * @param type $filepath
     * @param type $request
     * @param type $fileerror
     * @return boolean|string
     */
    function caricaAllegato(&$dati, $filename, $filepath, $request = array(), $fileerror = 0) {
        if ($filename == "") {
            $this->errCode = -1;
            $this->errMessage = 'Selezionare un file da caricare.';
            return false;
        }

        if ($fileerror != 0) {
            $max_size = ini_get('upload_max_filesize');

            $this->errCode = -1;
            $this->errMessage = "Errore in upload.<br>Controllare se la dimensione del file caricato supera $max_size.";
            return false;
        }

        /*
         * Verifico la dimensione del file rispetto ad eventuali limiti imposti.
         * Verifico anche il limite sul passo.
         */

        $limitiUpload = $this->GetParametriLimitiUpload($dati['PRAM_DB']);
        $limiteUploadSingolo = (int) $limitiUpload['LIMUPL_SINGOLO'];
        $limiteUploadMulti = (int) $limitiUpload['LIMUPL_MULTIUPL'];

        $filesize = filesize($filepath) / 1000 / 1000;

        if ($limiteUploadSingolo && $filesize > $limiteUploadSingolo) {
            $this->errCode = -1;
            $this->errMessage = "La dimensione del file caricato supera il limite massimo consentito di {$limiteUploadSingolo}MB.";
            return false;
        }

        if ($limiteUploadMulti) {
            $dimensioniPasso = $this->getDimensioniAllegatiPasso($dati);
            if (($dimensioniPasso + $filesize) > $limiteUploadMulti) {
                $this->errCode = -1;
                $this->errMessage = "E' stato superato il limite massimo consentito di {$limiteUploadSingolo}MB per il passo corrente.";
                return false;
            }
        }

        if ($dati['Praclt_rec']['CLTOPEFO'] === praLibStandardExit::FUN_FO_PASSO_CARTELLA) {
            $metadata_praclt = unserialize($dati['Praclt_rec']['CLTMETA']);
            if ($metadata_praclt && isset($metadata_praclt['METAOPEFO'])) {
                $limiteUploadCartella = (int) $metadata_praclt['METAOPEFO']['LIMITE_UPLOAD_CARTELLA'];
                $dimensioniPasso = $this->getDimensioniAllegatiPasso($dati);
                if (($dimensioniPasso + $filesize) > $limiteUploadCartella) {
                    $this->errCode = -1;
                    $this->errMessage = "E' stato superato il limite massimo consentito di {$limiteUploadCartella}MB per il passo corrente.";
                    return false;
                }
            }
        }

        if ($dati['Ricite_rec']['ITEQALLE'] == 1) {
            $request['QualificaAllegato']['NOTE'] = utf8_decode($request['QualificaAllegato']['NOTE']);
        }

        $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);

        if ($metadati['TEMPLATENOMEUPLOAD']) {
            if ($dati['Ricite_rec']['ITEQALLE'] == 1) {
                $errorMessage = '';

                if (isset($request['QualificaAllegato']['CLASSIFICAZIONE']) && $request['QualificaAllegato']['CLASSIFICAZIONE'] == "") {
                    $errorMessage .= 'Il campo Classificazione non pu&ograve; essere vuoto.';
                }

                if (isset($request['QualificaAllegato']['DESTINAZIONE'][0]) && $request['QualificaAllegato']['DESTINAZIONE'][0] == "") {
                    $errorMessage .= ($errorMessage !== '' ? '<br />' : '') . 'Il campo Destinazione non pu&ograve; essere vuoto.';
                }

                if ($errorMessage !== '') {
                    $this->errCode = -1;
                    $this->errMessage = $errorMessage;
                    return false;
                }
            }
        }

        $Seq = $dati['seq'];
        $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($Seq)) . $Seq;

        $Est = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($Est == "") {
            $this->errCode = -1;
            $this->errMessage = 'Estensione del file non trovata. Ricontrollare l\'allegato scelto.';
            return false;
        }

        /*
         * Verifico validità estensione
         */

        $arrayEstensioni = $this->estensioniPermesse($dati['Ricite_rec']);
        if (!$this->checkEstensione($filename, $arrayEstensioni)) {
            $this->errCode = -1;
            $this->errMessage = 'L\'estensione del file non corrisponde alle estensioni previste.';
            return false;
        }

        /*
         * Controllo lo sha del file rispetto al file originale
         * se si tratta di una sostituzione
         */

        if ($dati['Ricite_rec']['RICSHA2SOST']) {
            if (hash_file('sha256', $filepath) == $dati['Ricite_rec']['RICSHA2SOST']) {
                $this->errCode = -1;
                $this->errMessage = 'Il file caricato corrisponde con quello che si sta sostituendo.';
                return false;
            }
        }

        /*
         * Elenco i file del passo escludendo i file di tipo info e err
         */

        $results = $this->praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo);
        foreach ($results as $key => $alle) {
            $ext = pathinfo($alle, PATHINFO_EXTENSION);
            if ($ext == 'info' || $ext == 'err') {
                unset($results[$key]);
            }
        }

        /*
         * Blocco secondo upload un caso di un upload singolo
         */

        if ($dati['Ricite_rec']['ITEUPL'] == 1 && $dati['Ricite_rec']['ITEMLT'] == 0) {
            if (count($results) >= 1) {
                $this->errCode = -2;
                $this->errMessage = "E' già stato caricato un allegato per il passo <b>" . $dati['Ricite_rec']['ITEDES'] . ".</b><br>Non è possibile caricarne altri.";
                return false;
            }
        }

        /*
         * in caso di p7m antepongo sempre il prefisso pdf
         */

        if ($Est == strtolower("p7m")) {
            /*
             * Mi trovo l'estensione base del file
             */

            $Est_baseFile = $this->GetBaseExtP7MFile($filename);

            if ($Est_baseFile == "") {
                $this->errCode = -1;
                $this->errMessage = 'Il nome del <b>p7m</b> che si sta cercando di caricare non contiene l\'estensione base.<br>Si prega di controllare o rifirmare il file originale.';
                return false;
            }

            /*
             * Mi trovo e accodo tutte le estensioni p7m
             */

            $Est_tmp = $this->GetExtP7MFile($filename);
            $posPrimoPunto = strpos($Est_tmp, ".");
            $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
            $p7mEst = str_replace($delEst, "", $Est_tmp);
        }

        if (count($results) >= 1) {
            sort($results);
            $lastAllegato = end($results);
            $lenght = 12 + $dati['seqlen'];
            $All = substr($lastAllegato, 0, $lenght);
            $lenght2 = 13 + $dati['seqlen'];
            $Inc = substr($lastAllegato, $lenght2, 2) + 1;
            $Inc = str_repeat("0", 2 - strlen($Inc)) . $Inc;
            if ($Est == strtolower("p7m")) {
                $nomeFileAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "_$Inc.$Est_baseFile.$p7mEst";
            } else {
                $nomeFileAllegato = $All . "_" . $Inc . "." . $Est;
            }
        } else {
            if ($dati['Ricite_rec']['ITEMLT'] == 1) {
                $Inc = "01";
                if ($Est == strtolower("p7m")) {
                    $nomeFileAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "_$Inc.$Est_baseFile.$p7mEst";
                } else {
                    $nomeFileAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "_$Inc." . $Est;
                }
            } else {
                if ($Est == strtolower("p7m")) {
                    $nomeFileAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . ".$Est_baseFile.$p7mEst";
                } else {
                    $nomeFileAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "." . $Est;
                }
            }
        }

        /*
         * Ora ho il nome del file definitivo
         */

        if (!move_uploaded_file($filepath, $dati['CartellaAllegati'] . "/" . $nomeFileAllegato)) {
            if (!rename($filepath, $dati['CartellaAllegati'] . "/" . $nomeFileAllegato)) {
                $this->errCode = -3;
                $this->errMessage = "Errore durante la copia dell'allegato caricato nella destinazione '{$dati['CartellaAllegati']}/$nomeFileAllegato'.";
                return false;
            }
        }

        chmod($dati['CartellaAllegati'] . "/" . $nomeFileAllegato, 0777);

        return $nomeFileAllegato;
    }

    /**
     * Registra un documento caricato nella richiesta online effettuando
     * tutte le operazioni necessarie.
     * 
     * @param type $dati
     * @param type $nomeFileAllegato
     * @param type $filenameOriginale
     * @param type $request
     * @return boolean
     */
    public function registraAllegato(&$dati, $nomeFileAllegato, $filenameOriginale, $request = array(), $writeRICERM = true) {
        if ($writeRICERM) {
            $dati['Ricite_rec']['RICERF'] = 0;
            $dati['Ricite_rec']['RICERM'] = "";
        }

        $allegatoRiservato = $request['RiservatezzaAllegato'] == '1' ? true : false;

        $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
        $Est = strtolower(pathinfo($nomeFileAllegato, PATHINFO_EXTENSION));

        if ($Est == "pdf") {
            $fl_pdfa = false;
            $arrayEstensioni = $this->estensioniPermesse($dati['Ricite_rec']);
            if ($Est == 'pdf' && in_array('pdfa', $arrayEstensioni)) {
                $fl_pdfa = true;
            }

            if ($fl_pdfa) {
                $manager = itaPDFA::getManagerInstance();
                if ($manager) {
                    $manager->verifyPDFSimple($dati['CartellaAllegati'] . "/" . $nomeFileAllegato);
                    if ($manager->getLastExitCode() != 0) {
                        @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

                        $this->errCode = -1;
                        $this->errMessage = $manager->getLastMessage();
                        return false;
                    }
                }
            }

            if (!$this->eseguiVerificaFile($dati, $nomeFileAllegato, false, $writeRICERM)) {
                return false;
            }
        } elseif ($Est == "p7m") {
            $anapar_rec = $this->praLib->GetAnapar("VERIFICA_FIRMA", "parkey", $dati['PRAM_DB'], false);

            if ($anapar_rec['PARVAL'] == "Si") {
                $ente = frontOfficeApp::getEnte();
                $ctr_p7m_content = $this->praLib->ControlloP7m($dati['CartellaAllegati'] . "/" . $nomeFileAllegato, $dati['Proric_rec']['RICNUM'], $dati['Ricite_rec']['ITECTP'], $dati['PRAM_DB'], $ente, $dati);

                if ($ctr_p7m_content === '') {
                    @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

                    $this->errCode = -1;
                    $this->errMessage = 'ATTENZIONE: Firma digitale con errori.';
                    return false;
                }

                if (file_exists($dati['CartellaAllegati'] . "/" . $ctr_p7m_content)) {
                    if (!$this->eseguiVerificaFile($dati, $ctr_p7m_content, true)) {
                        @unlink($dati['CartellaAllegati'] . '/' . $ctr_p7m_content);
                        return false;
                    }

                    if ($metadati['TEMPLATENOMEUPLOAD']) {
                        $nameTmp = $this->praLib->GetNomeTemplateUpload($request['QualificaAllegato'], $metadati, $dati['PRAM_DB'], $filenameOriginale, $dati);
                        $filenameOriginale = $this->praLib->GetP7MFileContentName($nameTmp);
                    } else {
                        $filenameOriginale = $this->praLib->GetP7MFileContentName($filenameOriginale);
                    }
                    $this->praLib->registraRicdoc($dati['Ricite_rec'], $ctr_p7m_content, $filenameOriginale, $dati['PRAM_DB'], $request['QualificaAllegato'], true, $dati['CartellaAllegati'], $allegatoRiservato);
                } else {
                    if ($ctr_p7m_content !== true) {
                        if ($dati['Proric_rec']['RICGGG'] == 1) {
                            $libErr = null;

                            if (class_exists('suapErr')) {
                                $libErr = new suapErr();
                            } elseif (class_exists('sueErr')) {
                                $libErr = new sueErr();
                            }

                            if (!is_null($libErr)) {
                                $libErr->parseError(__FILE__, 'E0026', "Errore NON BLOCCANTE verifica firma Richiesta '{$dati['Proric_rec']['RICNUM']}' Allegato '{$nomeFileAllegato}'", __CLASS__, '', false);
                            }
                        } else {
                            @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

                            $this->errCode = -1;
                            $this->errMessage = 'ATTENZIONE: ' . $ctr_p7m_content;
                            return false;
                        }
                    }
                }
            }
        }

        /*
         * Se c'è il template per il nome personalizzato dell'upload lo prendo
         */
        if ($metadati['TEMPLATENOMEUPLOAD']) {
            $filenameOriginale = $this->praLib->GetNomeTemplateUpload($request['QualificaAllegato'], $metadati, $dati['PRAM_DB'], $filenameOriginale, $dati);
        }

        /*
         * Una volta effettuato l'upload azzero gli errori
         */
        $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);

        /*
         * Se tutto a buon fine Registro RICDOC
         */
        $this->praLib->registraRicdoc($dati['Ricite_rec'], $nomeFileAllegato, $filenameOriginale, $dati['PRAM_DB'], $request['QualificaAllegato'], false, $dati['CartellaAllegati'], $allegatoRiservato);

        return true;
    }

    public function eseguiVerificaFile(&$dati, $nomeFileAllegato, $isP7M = false, $writeRICERM = true) {
        $fileINFO = $this->CreaFileInfo($dati['CartellaAllegati'] . "/" . $nomeFileAllegato, $dati);

        if (!$fileINFO) {
            return false;
        }

        if (!$this->BloccoFilePDF($dati['CartellaAllegati'] . "/" . $nomeFileAllegato, $dati)) {
            @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);
            $this->errCode = -1;
            $this->errMessage = 'Errore blocco file Pdf.';
            return false;
        }

        $FileInfo = pathinfo($nomeFileAllegato, PATHINFO_FILENAME);
        $fileInfo['DATAFILE'] = $dati['CartellaAllegati'] . "/" . $FileInfo . '.info';
        $ctr_campi_file = $this->praLib->ControlloInfo($fileInfo, $dati['Proric_rec']['RICNUM'], $dati['Ricite_rec']['ITECTP'], $dati['PRAM_DB']);

        if ($ctr_campi_file !== true) {
            @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);
            $this->errCode = -1;
            $this->errMessage = 'ATTENZIONE: Il file di upload non coincide con quello di download.';
            return false;
        }

        $msgErr = $this->praLib->ControlliCampi($fileInfo, $dati['Proric_rec']['RICNUM'], $dati['Ricite_rec']['ITECTP'], $dati['PRAM_DB']);

        if ($msgErr != '') {
            @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);
            $this->errCode = -1;
            $this->errMessage = $msgErr;
            return false;
        }

        if (!$isP7M) {
            $ret = $this->praLib->ControlliValiditaCampi($fileInfo, $dati['Proric_rec']['RICNUM'], $dati['Ricite_rec']['ITECTP'], $dati['PRAM_DB']);
            $blocca = false;

            foreach ($ret as $value) {
                if ($value['status'] == "-1") {
                    $blocca = true;
                    break;
                }
            }

            $msgErr = "";

            foreach ($ret as $value) {
                if ($value['status'] == "-1") {
                    $msgErr .= "<span style=\"color:red;\">Errore: " . $value['message'] . "</span>";
                } elseif ($value['status'] == "0") {
                    $msgErr .= "<span style=\"color:blue;\">Avviso: " . $value['message'] . "</span>";
                }
            }

            if ($blocca) {
                /*
                 * Se bloccante ritorno errore.
                 */

                @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

                $this->errCode = -1;
                $this->errMessage = $msgErr;
                return false;
            }

            /*
             * Salvo l'errore ma non il flag.
             */

            if ($writeRICERM) {
                $dati['Ricite_rec']['RICERM'] = $msgErr;
                $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
            }
        }

        //$retCarica = $this->praLib->caricaCampi($fileInfo, $nomeFileAllegato, $dati['Proric_rec'], $dati['Ricite_rec']['ITEKEY'], $dati['Ricite_rec']['ITECTP'], $dati['PRAM_DB']);
        $retCarica = $this->praLib->caricaCampi($fileInfo, $nomeFileAllegato, $dati);

        /*
         * Se ci sono i controlli, verifico la validita del file di upload
         */
        if ($dati['Riccontrolli_tab']) {
            $praVar = new praVars();
            $praVar->setPRAM_DB($dati['PRAM_DB']);
            $praVar->setGAFIERE_DB($dati['GAFIERE_DB']);
            $praVar->setDati($dati);
            $praVar->loadVariabiliRichiesta();
            $msg = $this->praLib->CheckValiditaPasso($dati['Riccontrolli_tab'], $praVar->getVariabiliRichiesta()->getAlldataPlain("", "."));
            if ($msg) {
                $this->errCode = -1;
                $this->errMessage = $msg;

                if (!$this->praLib->CancellaUpload($dati['PRAM_DB'], $dati, $dati['Ricite_rec'], $nomeFileAllegato)) {
                    $this->errMessage .= '<br><br>Errore nel cancellare l\'upload.';
                }

                return false;
            }
        }

        if ($dati['Ricite_rec']['RICERF'] == 0 && $dati['Ricite_rec']['ITEQSTDAG'] == 1) {
            $dati['Ricite_rec']['RICQSTRIS'] = 1;
            $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
        }

        return true;
    }

    public function CreaFileInfo($filePDF, $dati) {
        if (ITA_JVM_PATH == "" || !file_exists(ITA_JVM_PATH)) {
            $this->errCode = -3;
            $this->errMessage = 'JRE/JDK Mancante';
            return false;
        }

        $xmlDump = $dati['CartellaTemporary'] . "/xmlDump.xml";
        $FileXml = fopen($xmlDump, "w");
        if (!file_exists($xmlDump)) {
            $this->errCode = -3;
            $this->errMessage = "File $xmlDump non trovato";
            return false;
        }

        $output = pathinfo($filePDF, PATHINFO_DIRNAME) . '/' . pathinfo($filePDF, PATHINFO_FILENAME) . '.info';
        $xml = $this->praLib->CreaXmlDumpDataFieldsPdf($filePDF, $output);
        fwrite($FileXml, $xml);
        fclose($FileXml);
        exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlDump ", $ret);
        $taskDump = false;
        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskDump = true;
                break;
            }
        }

        if ($taskDump == false) {
            $libErr = null;

            if (class_exists('suapErr')) {
                $libErr = new suapErr();
            } elseif (class_exists('sueErr')) {
                $libErr = new sueErr();
            }

            if (!is_null($libErr)) {
                $libErr->parseError(__FILE__, 'E0024', "Errore creazione file info dell'allegato $filePDF<br>Error ---> " . $ret[0], __CLASS__, "", false);
            }
        }

        if (filesize($output) == 0) {
            @unlink($output);
        }

        $this->praLib->ClearDirectory($dati['CartellaTemporary']);

        return true;
    }

    public function BloccoFilePDF($filePDF, $dati) {
        if (ITA_JVM_PATH == "" || !file_exists(ITA_JVM_PATH)) {
            $this->errCode = -3;
            $this->errMessage = 'JRE/JDK Mancante';
            return false;
        }

        $xmlFlatten = $dati['CartellaTemporary'] . "/xmlFlatten.xml";
        $FileXml = fopen($xmlFlatten, "w");
        if (!file_exists($xmlFlatten)) {
            $this->errCode = -3;
            $this->errMessage = "File $xmlFlatten non trovato";
            return false;
        }

        $input = $filePDF;
        $output = pathinfo($filePDF, PATHINFO_DIRNAME) . '/' . pathinfo($filePDF, PATHINFO_FILENAME) . '_tmp.pdf';
        $xml = $this->praLib->CreaXmlFlattenPdf($input, $output);
        fwrite($FileXml, $xml);
        fclose($FileXml);
        exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlFlatten ", $ret);
        $taskFlatten = false;
        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskFlatten = true;
                break;
            }
        }

        $this->praLib->ClearDirectory($dati['CartellaTemporary']);

        if ($taskFlatten == false) {
            $libErr = null;

            if (class_exists('suapErr')) {
                $libErr = new suapErr();
            } elseif (class_exists('sueErr')) {
                $libErr = new sueErr();
            }

            if (!is_null($libErr)) {
                $libErr->parseError(__FILE__, 'E0025', "Errore nel bloccare il pdf $filePDF.<br>Error--->" . $ret[0], __CLASS__, "", false);
            }

            @unlink($output);
            return pathinfo($filePDF, PATHINFO_DIRNAME) . '/' . pathinfo($filePDF, PATHINFO_FILENAME) . '.pdf';
        } else {
            @unlink($filePDF);
            @rename($output, pathinfo($filePDF, PATHINFO_DIRNAME) . '/' . pathinfo($filePDF, PATHINFO_FILENAME) . '.pdf');
            return pathinfo($filePDF, PATHINFO_DIRNAME) . '/' . pathinfo($filePDF, PATHINFO_FILENAME) . '.pdf';
        }
    }

    /**
     * Ritorna il $ricite_rec del passo speciale CARTELLA della richiesta se presente.
     * @param type $dati
     * @return boolean
     */
    public function getPassoCartella($dati) {
        foreach ($dati['Ricite_tab'] as $ricite_rec) {
            $praclt_rec = $this->praLib->GetPraclt($ricite_rec['ITECLT'], 'codice', $dati['PRAM_DB']);
            if ($praclt_rec['CLTOPEFO'] === praLibStandardExit::FUN_FO_PASSO_CARTELLA) {
                return $ricite_rec;
            }
        }

        return false;
    }

    /**
     * Ritorna il $ricdoc_rec di un allegato per ROWID controllando che sia
     * un allegato caricato nel passo speciale CARTELLA.
     * 
     * @param type $dati
     * @param type $rowid
     * @return boolean
     */
    public function getAllegatoCartella($dati, $rowid) {
        $passoCartella = $this->getPassoCartella($dati);
        if (!$passoCartella) {
            $this->errCode = -1;
            $this->errMessage = 'Passo upload cartella non trovato.';
            return false;
        }

        $itekeyCartella = $passoCartella['ITEKEY'];

        $ricdoc_rec = $this->praLib->GetRicdoc($rowid, 'rowid', $dati['PRAM_DB']);

        if (!$ricdoc_rec) {
            $this->errCode = -1;
            $this->errMessage = 'Documento non trovato.';
            return false;
        }

        if ($ricdoc_rec['ITEKEY'] != $itekeyCartella) {
            $this->errCode = -1;
            $this->errMessage = "Il documento non appartiene al passo cartella '$itekeyCartella'.";
            return false;
        }

        return $ricdoc_rec;
    }

    /**
     * Ritorna il $ricdoc_tab del passo speciale CARTELLA se presente.
     * 
     * @param type $dati
     * @return boolean
     */
    public function getAllegatiCartella($dati) {
        $passoCartella = $this->getPassoCartella($dati);
        if (!$passoCartella) {
            return false;
        }

        $ricdoc_tab = $this->praLib->GetRicdoc($passoCartella['ITEKEY'], 'itekey', $dati['PRAM_DB'], true, $dati['Proric_rec']['RICNUM']);

        if (!$ricdoc_tab) {
            return false;
        }

        return $ricdoc_tab;
    }

    /**
     * Cancella gli allegati presenti nel passo speciale CARTELLA della richiesta.
     * 
     * @param type $dati
     * @return boolean
     */
    public function cancellaAllegatiCartella($dati) {
        $passoCartella = $this->getPassoCartella($dati);
        $allegatiCartella = $this->getAllegatiCartella($dati);

        if ($passoCartella && $allegatiCartella) {
            foreach ($allegatiCartella as $allegatoCartella) {
                if (!$this->praLib->CancellaUpload($dati['PRAM_DB'], $dati, $passoCartella, $allegatoCartella['DOCUPL'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Ritorna l'elenco di filepath di tutti gli allegati di un passo.
     * 
     * @param type $dati
     * @param string $seqPasso
     * @return string
     */
    public function getAllegatiPasso($dati, $seqPasso = false) {
        if (!$seqPasso) {
            $seqPasso = str_repeat('0', $dati['seqlen'] - strlen($dati['seq'])) . $dati['seq'];
        }

        $allegati = array();
        $results = $this->praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C$seqPasso");

        foreach ($results as $result) {
            $allegati[] = $dati['CartellaAllegati'] . "/$result";
        }

        return $allegati;
    }

    /**
     * Ritorna la dimensione totale dei file (in MB) legati ad un passo.
     * 
     * @param type $dati
     * @param string $seqPasso
     * @return type
     */
    public function getDimensioniAllegatiPasso($dati, $seqPasso = false) {
        if (!$seqPasso) {
            $seqPasso = str_repeat('0', $dati['seqlen'] - strlen($dati['seq'])) . $dati['seq'];
        }

        $filesize = 0;

        foreach ($this->getAllegatiPasso($dati, $seqPasso) as $allegato) {
            $filesize += (filesize($allegato) / 1000 / 1000);
        }

        return $filesize;
    }

    public function getStringDestinatari($destinazioni, $PRAM_DB) {
        $arrayDest = unserialize($destinazioni);
        $strDest = "";
        if (is_array($arrayDest)) {
            foreach ($arrayDest as $dest) {
                $Anaddo_rec = $this->praLib->GetAnaddo($dest, "codice", false, $PRAM_DB);
                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
            }
        }
        return $strDest;
    }

}
