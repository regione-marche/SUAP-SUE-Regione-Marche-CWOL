<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of emlMessage
 *
 * @author michele
 */
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDate.class.php';
include_once ITA_LIB_PATH . '/itaPHPMail/itaMime.class.php';
include_once ITA_LIB_PATH . '/QXml/QXml.class.php';

class emlMessage {

    const PEC_TIPO_ACCETTAZIONE = "accettazione";
    const PEC_TIPO_NON_ACCETTAZIONE = "non-accettazione";
    const PEC_TIPO_PRESA_IN_CARICO = "presa-in-carico";
    const PEC_TIPO_AVVENUTA_CONSEGNA = "avvenuta-consegna";
    const PEC_TIPO_POSTA_CERTIFICATA = "posta-certificata";
    const PEC_TIPO_ERRORE_CONSEGNA = "errore-consegna";
    const PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA = "preavviso-errore-consegna";
    const PEC_TIPO_RILEVAZIONE_VIRUS = "rilevazione-virus";

    private $emlFile;
    private $tempPath;
    private $messageId;
    private $arrayStruct;
    private $fromDbMailbox;
    private $lastExitCode;
    private $lastMessage;

    /**
     * 
     * @param type $id
     * @param type $arrayStruct
     * @param type $tempPath
     */
    public function __construct($messageId = '', $arrayStruct = '', $tempPath = '') {
        $this->messageId = $messageId;
        $this->arrayStruct = $arrayStruct;
        $this->tempPath = $tempPath;
    }

    /**
     * Setta il nome del file eml sorgente che rappresenta il contenuto del messaggio
     * @param string $emlFile Nome del file eml sorgente
     */
    public function setEmlFile($emlFile) {
        $this->emlFile = $emlFile;
    }

    /**
     * Restituisce il nome del file eml sorgente che rappresenta il contenuto del messaggio
     * @return String
     */
    public function getEmlFile() {
        return $this->emlFile;
    }

    /**
     * Setta la path di lavoro temporanea per il salvataggio degli allegati
     * @param String $tempPath Path nel file system
     */
    public function setTempPath($tempPath) {
        $this->tempPath = $tempPath;
    }

    /**
     * restituisce la path di lavoro temporanea per il salvataggio degli allegati
     * @return String Path nel file system
     */
    public function getTempPath() {
        return $this->tempPath;
    }

    private function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    /**
     * Restituisce Id del messaggio Mail conforme alle specifiche RFC 2822
     * @return type
     */
    public function getMessageId() {
        return $this->messageId();
    }

    public function setStruct($arrayStruct) {
        $this->arrayStruct = $arrayStruct;
    }

    /**
     * Restituisce la struttura del messaggio in forma di array associativo multilivello
     * @return array
     */
    public function getStruct() {
        return $this->arrayStruct;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function setLastExitCode($lastExitCode) {
        $this->lastExitCode = $lastExitCode;
    }

    public function setLastMessage($lastMessage) {
        $this->lastMessage = $lastMessage;
    }

    /**
     * Restituisce la parte di struttura relativa agli allegati
     * @return Array
     */
    public function getAttachments() {
        $attachments = array();
        App::log($this->arrayStruct);
        //break;
        if (isset($this->arrayStruct['FileDisposition'])) {
            if ($this->arrayStruct['FileDisposition'] == 'attachment') {
                $attachments[] = array(
                    "Type" => $this->arrayStruct['Type'],
                    "Description" => $this->arrayStruct['Description'],
                    "DataFile" => $this->arrayStruct['DataFile'],
                    "FileName" => $this->arrayStruct['FileName'],
                    "FileDisposition" => $this->arrayStruct['FileDisposition']
                );
                if (isset($this->arrayStruct['Attachments'])) {
                    $attachments = array_merge($attachments, $this->arrayStruct['Attachments']);
                }
                return $attachments;
            }
        }
//        if (isset($this->arrayStruct['Attachments'][0]['Attachments'])) {
//            $attachments = array_merge($this->arrayStruct['Attachments'][0]['Attachments'], $this->arrayStruct['Attachments']);
//            return $attachments;
//        }
        return $this->arrayStruct['Attachments'];
    }

    /**
     * Restituisce la path fisica del file contenente il corpo della mail
     * @return string
     */
    public function getEmlBodyDataFile() {
        if (isset($this->arrayStruct['FileDisposition'])) {
            if ($this->arrayStruct['FileDisposition'] == 'attachment') {
                return "";
            }
        }
        return $this->arrayStruct['DataFile'];
    }

    /**
     * Restituisce il corpo della mail in formato stringa
     * @return string
     */
    public function getEmlBody() {
        if (isset($this->arrayStruct['FileDisposition'])) {
            if ($this->arrayStruct['FileDisposition'] == 'attachment') {
                return "";
            }
        }
        if (file_exists($this->arrayStruct['DataFile'])) {
            return file_get_contents($this->arrayStruct['DataFile']);
        } else {
            return "";
        }
    }

    public function getMessaggioOriginaleObj() {
        if ($this->arrayStruct['ita_PEC_info'] != 'N/A') {
            if ($this->arrayStruct['ita_PEC_info']['messaggio_originale'] != 'N/A') {
                return $this->arrayStruct['ita_PEC_info']['messaggio_originale']['EmlObject'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getCertificazione($element = '') {
        switch ($element) {
            case 'tipo':
                return $this->arrayStruct['ita_PEC_info']['dati_certificazione']['tipo'];
            case 'msgid':
                return substr($this->arrayStruct['ita_PEC_info']['dati_certificazione']['msgid'], 1, -1);
            default:
                return false;
        }
    }

    /**
     * 
     * @return type
     */
    public function isPEC() {
        return ($this->arrayStruct['ita_PEC_info'] == "N/A") ? false : true;
    }

    /**
     * Cancella i file e le cartelle di lavoro temporanee ($this->tempPath) per il messaggio
     * @return bool true = success
     */
    public function cleanData() {
        return itaLib::deleteDirRecursive($this->tempPath);
    }

    /**
     * Elabora il messaggio senza verifica e controllo posta firmata o certificata
     * @param boolean $saveAttach
     * @param string $attachPath se vuoto la path sarà gestita automaticamente (consigliato)
     * @return boolean
     */
    public function parseEmlFile($saveAttach = true, $attachPath = '') {
        $tempPath = '';
        if ($saveAttach === true) {
            if ($attachPath != '') {
                $tempPath = $attachPath;
            } else {
                $tempPath = $this->createTempAttachPath($this->emlFile);
            }
            if (!$this->verifyAttachPath($tempPath)) {
                $this->lastExitCode = -1;
                $this->lastMessage = "Creazione Cartella appoggio allegati fallita";
                return false;
            }
        }
        $this->setTempPath($tempPath);
        $retDecode = itaMime::parseMail($this->getEmlFile(), 1, $tempPath, 0);
        if ($retDecode === false) {
            $this->lastExitCode = itaMime::$lastExitCode;
            $this->lastMessage = itaMime::$lastMessage;
            return false;
        }
        $this->setMessageId($retDecode['Message-Id']);
        $nm = 0;
        foreach ($retDecode['Attachments'] as $key => $attachment) {
            if (!isset($attachment['FileName'])) {
                if ($attachment['Type'] == "message") {
                    $nm += 1;
                    $retDecode['Attachments'][$key]['FileName'] = "message-" . $nm . ".eml";
                    $retDecode['Attachments'][$key]['FileDisposition'] = "attachment";
                }
            }
        }
        $this->setStruct($retDecode);
        return true;
    }

    public function NormalizzaCaratteri($stringa) {
        $stringa_tmp = "";
        for ($i = 0; $i < strlen($stringa); $i++) {
            $carattere = substr($stringa, $i, 1);
            if (ord($carattere) > 31) {
                $stringa_tmp = $stringa_tmp . $carattere;
            }
        }

        return $stringa_tmp;
    }

    /**
     * Elabora il messaggio con dati di verifica e controllo posta firmata o certificata
     * @param boolean $saveAttach
     * @param String $attachPath
     * @return boolean
     */
    public function parseEmlFileDeep($saveAttach = true, $attachPath = '') {
        if (!$this->parseEmlFile($saveAttach, $attachPath)) {
            return false;
        }
        //
        // Extra info su certificato
        //
        $retDecode = $this->getStruct();

        if (is_array($retDecode['Signature'])) {
            switch ($retDecode['Signature']['SubType']) {
                case "x-pkcs7-signature":
                case "pkcs7-signature":
                    $retDecode['ita_Signature_info'] = array();
                    $ret_verify = openssl_pkcs7_verify($this->emlFile, PKCS7_BINARY);
                    while ($msg = openssl_error_string()) {
                        //TODO: Gestione Errorri
//                        App::log($msg);
                    }
                    $retDecode['ita_Signature_info']['Verified'] = $ret_verify;
                    break;
                default:
                    $retDecode['ita_Signature_verify'] = "N/A";
                    break;
            }
        }

        $retDecode['ita_PEC_info'] = "N/A";
        foreach ($retDecode['Attachments'] as $key => $attachment) {
            if ($attachment['FileName'] == 'daticert.xml') {
                $xmlObj = new QXML;
                $xmlObj->setXmlFromFile($attachment['DataFile']);
                $arrayXml = $xmlObj->getArray();
                if (is_array($arrayXml['postacert'])) {
                    $retDecode['ita_PEC_info'] = array();
                    $retDecode['ita_PEC_info']['dati_certificazione'] = array();
                    $retDecode['ita_PEC_info']['dati_certificazione']['FileName'] = "daticert.xml";
                    $retDecode['ita_PEC_info']['dati_certificazione']['tipo'] = $arrayXml['postacert']['@attributes']['tipo'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['errore'] = $arrayXml['postacert']['@attributes']['errore'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['errore-esteso'] = base64_encode($arrayXml['postacert']['dati']['errore-esteso']['@textNode']);
                    $retDecode['ita_PEC_info']['dati_certificazione']['mittente'] = $arrayXml['postacert']['intestazione']['mittente']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['oggetto'] = $arrayXml['postacert']['intestazione']['oggetto']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['gestore-emittente'] = $arrayXml['postacert']['dati']['gestore-emittente']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['zona'] = $arrayXml['postacert']['dati']['data']['@attributes']['zona'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['data'] = $arrayXml['postacert']['dati']['data']['giorno']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['ora'] = $arrayXml['postacert']['dati']['data']['ora']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['identificativo'] = $arrayXml['postacert']['dati']['identificativo']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['errore-esteso'] = $arrayXml['postacert']['dati']['errore-esteso']['@textNode'];
                    $retDecode['ita_PEC_info']['dati_certificazione']['msgid'] = $arrayXml['postacert']['dati']['msgid']['@textNode'];
                }
                break;
            }
        }

        if ($retDecode['ita_PEC_info'] != "N/A") {
            $retDecode['ita_PEC_info']['messaggio_originale'] = 'N/A';
            foreach ($retDecode['Attachments'] as $key => $attachment) {
                if ($attachment['FileName'] == 'postacert.eml') {
                    $retDecode['ita_PEC_info']['messaggio_originale'] = array();
                    $retDecode['ita_PEC_info']['messaggio_originale']['FileName'] = 'postacert.eml';
                    $attachPathOriginale = $this->tempPath . "/" . md5($retDecode['ita_PEC_info']['dati_certificazione']['msgid']);
                    if (!is_dir($attachPathOriginale)) {
                        mkdir($attachPathOriginale, 0777, true);
                        //TODO: CONTROLLO ERRORE
                    }
                    //$originalMessage = $this->parseEmlFileDeep($attachment['DataFile'], $attachPathOriginale);

                    $originalMessage = new emlMessage();
                    $originalMessage->setEmlFile($attachment['DataFile']);
                    $retParse = $originalMessage->parseEmlFileDeep(true, $attachPathOriginale);
                    if ($retParse === false) {
                        return false;
                    }
                    $retDecode['ita_PEC_info']['messaggio_originale']['EmlObject'] = $originalMessage;
                    $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'] = $originalMessage->getStruct();
                }
            }
            // 
            // Non ho mai trovato postacert.eml
            //
            if ($retDecode['ita_PEC_info']['messaggio_originale'] == 'N/A') {
                foreach ($retDecode['Attachments'] as $key => $attachment) {
                    if (pathinfo($attachment['FileName'], PATHINFO_EXTENSION) == 'eml') {
                        $retDecode['ita_PEC_info']['messaggio_originale'] = array();
                        $retDecode['ita_PEC_info']['messaggio_originale']['FileName'] = $attachment['FileName'];
                        $attachPathOriginale = $this->tempPath . "/" . md5($retDecode['ita_PEC_info']['dati_certificazione']['msgid']);
                        if (!is_dir($attachPathOriginale)) {
                            mkdir($attachPathOriginale, 0777, true);
                            //TODO: CONTROLLO ERRORE
                        }
                        //$originalMessage = $this->parseEmlFileDeep($attachment['DataFile'], $attachPathOriginale);

                        $originalMessage = new emlMessage();
                        $originalMessage->setEmlFile($attachment['DataFile']);
                        $retParse = $originalMessage->parseEmlFileDeep(true, $attachPathOriginale);
                        if ($retParse === false) {
                            return false;
                        }
                        $retDecode['ita_PEC_info']['messaggio_originale']['EmlObject'] = $originalMessage;
                        $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'] = $originalMessage->getStruct();
                    }
                }
            }

            // 
            // Non ho mai trovato un filedi tipo eml **** PROTOTIPO ****
            //
//            if ($retDecode['ita_PEC_info']['messaggio_originale'] == 'N/A') {
//                foreach ($retDecode['Attachments'] as $key => $attachment) {
//                    if ($attachment['FileName'] != 'postacert.eml') {
//                        $retDecode['ita_PEC_info']['messaggio_originale'] = array();
//                        $retDecode['ita_PEC_info']['messaggio_originale']['FileName'] = 'postacert.eml';
//                        $attachPathOriginale = $this->tempPath . "/" . md5($retDecode['ita_PEC_info']['dati_certificazione']['msgid']);
//                        if (!is_dir($attachPathOriginale)) {
//                            mkdir($attachPathOriginale, 0777, true);
//                            //TODO: CONTROLLO ERRORE
//                        }
//                        //$originalMessage = $this->parseEmlFileDeep($attachment['DataFile'], $attachPathOriginale);
//
//                        $originalMessage = new emlMessage();
//                        $originalMessage->setEmlFile($attachment['DataFile']);
//                        $originalMessage->parseEmlFile(true, $attachPathOriginale);
//                        $retDecode['ita_PEC_info']['messaggio_originale']['EmlObject'] = $originalMessage;
//                        $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'] = $originalMessage->getStruct();
//                    }
//                }
//            }
        }

        $this->setMessageId($retDecode['Message-Id']);
        $this->setStruct($retDecode);
        //App::log('$this->getStruct()');        
        //App::log($this->getStruct());
        return true;
    }

    private function createTempAttachPath($file) {
        $retDecode = itaMime::parseMail($file, 1, "", 0);
        $subPath = "mail-message-" . md5($retDecode['Message-Id'] . microtime());
        $tempPath = itaLib::getAppsTempPath($subPath);
        itaLib::deleteDirRecursive($tempPath);
        $tempPath = itaLib::createAppsTempPath($subPath);
        return $tempPath;
    }

    private function verifyAttachPath($path) {
        if (!is_dir($path)) {
            return false;
        }
        $dh = @opendir($path);
        if ($dh === false) {
            return false;
        }
        closedir($dh);
        return true;
    }

}
