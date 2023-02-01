<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaLibclass
 *
 * @author utente
 */
class itaLib {
    const PHP = 'PHP';

    public static function closeForm($model) {
        Out::closeDialog($model);
        $modelMap = array();
        $modelMap = App::$utente->getKey('modelMap');
        unset($modelMap[$model]);
        App::$utente->setKey('modelMap', $modelMap);
    }

    /**
     * 
     * @param type $model
     * @param type $modal
     * @param type $onlyOnce
     * @param type $container
     * @param type $opener
     */
    public static function openDialog($model, $modal = '', $onlyOnce = true, $container = 'desktopBody', $opener = "", $host_model = '', $alias_model = '') {
        self::openForm($model, $modal, $onlyOnce, $container, $opener, 'dialog', $host_model, $alias_model);
    }

    /**
     * 
     * @param type $model
     * @param type $modal
     * @param type $onlyOnce
     * @param type $container
     * @param type $opener
     */
    public static function openApp($model, $modal = '', $onlyOnce = true, $container = 'desktopBody', $opener = "", $host_model = '', $alias_model = '') {
        self::openForm($model, $modal, $onlyOnce, $container, $opener, 'app', $host_model, $alias_model);
    }

    /**
     * 
     * @param type $model
     * @param type $modal
     * @param type $onlyOnce
     * @param type $container
     * @param type $opener
     */
    public static function openInner($model, $modal = '', $onlyOnce = true, $container = 'desktopBody', $opener = "", $host_model = '', $alias_model = "", $removeLayout = false) {
        self::openForm($model, $modal, $onlyOnce, $container, $opener, 'inner', $host_model, $alias_model, $removeLayout);
    }

    /**
     * 
     * @param type $model   Modell da leggere su reposytory
     * @param type $modal   flag modale (solo per dialog)
     * @param type $onlyOnce    flag aopertura univoca (sovrascrive model se gia caricato nel browser)
     * @param type $container   contenitore di destinazione
     * @param type $opener      model che apre la form (non usato) facoltativo
     * @param type $openMode    moddo di apertura (auto, dialo, app, inner)
     * @param type $host_model  sorgente php che gestisce il model
     * @param type $alias_model alisa che deve essere dato all'elemento nel browser
     */
    public static function openForm($model, $modal = '', $onlyOnce = true, $container = 'desktopBody', $opener = "", $openMode = 'auto', $host_model = '', $alias_model = '', $removeLayout = false) {
        $alias_model = ($alias_model) ? $alias_model : $model;

        if ($onlyOnce == true) {
//            Out::closeDialog($model);
            Out::closeDialog($alias_model);
        }
        if ($modal) {
            // non usato    
        }
        $generator = new itaGenerator();
        $htmlModel = $generator->getModelHTML($model, false, $host_model, false, $alias_model);
        if ($removeLayout) {
            $htmlModel = $generator->removeLayoutHTML($htmlModel);
        }
        if ($htmlModel != '') {
            $wrapper_id = ($host_model) ? $host_model . "_" . $alias_model . '_wrapper' : $alias_model . '_wrapper';
            Out::addContainer($container, $wrapper_id);
            Out::addClass($wrapper_id, 'ita-dialog-wrapper');
            Out::hide($wrapper_id);
            switch ($openMode) {
                case 'auto':
                    Out::html($wrapper_id, $htmlModel);
                    break;
                case 'dialog';
                    Out::dialogHtml($wrapper_id, $htmlModel);
                    break;
                case 'app';
                    Out::appHtml($wrapper_id, $htmlModel);
                    break;
                case 'inner';
                    Out::innerHtml($wrapper_id, $htmlModel);
                    break;
            }
            Out::show($wrapper_id);
            $modelMap = array();
            $modelMap = App::$utente->getKey('modelMap');
            $modelMap[$alias_model]['parent'] = $opener;
            App::$utente->setKey('modelMap', $modelMap);
        }
    }

    public static function embedModelComponent($model, $container = 'desktopBody', $host_model = '') {
        $generator = new itaGenerator();
        $retHtml = $generator->getModelHTML($model, false, $host_model);
        if ($retHtml != '') {
            $htmlModel = $retHtml;
            $wrapper_id = ($host_model) ? $host_model . "_" . $model . '_wrapper' : $model . '_wrapper';
            Out::addContainer($container, $wrapper_id);
            Out::addClass($wrapper_id, 'ita-dialog-wrapper');
            Out::hide($wrapper_id);
            Out::html($wrapper_id, $htmlModel);
            Out::show($wrapper_id);
        }
    }

    //Solo per array monodimensionali
    public static function utf8_decode_array($array) {
        $array_utf8_decode = array();
        foreach ($array as $campo => $value) {
            if ($campo == "ITEMETA" || $campo == "ITDMETA") {
                $array_utf8_decode[$campo] = base64_decode($value);
            } else {
                $array_utf8_decode[$campo] = utf8_decode($value);
            }
        }
        return $array_utf8_decode;
    }

    public static function utf8_encode_recursive($array) {
        $array_utf8_encode = array();
        foreach ($array as $campo => $value) {
            if (is_array($value)) {
                $array_utf8_encode[$campo] = self::utf8_encode_recursive($value);
            } else {
                $array_utf8_encode[$campo] = utf8_encode($value);
            }
        }
        return $array_utf8_encode;
    }

    public static function utf8_decode_recursive($array) {
        $array_utf8_decode = array();
        foreach ($array as $campo => $value) {
            if (is_array($value)) {
                $array_utf8_decode[$campo] = self::utf8_decode_recursive($value);
            } else {
                $array_utf8_decode[$campo] = utf8_decode($value);
            }
        }
        return $array_utf8_decode;
    }

    public static function callCgi($host, $model, $modal = '') {
        if ($host == '') {
            $host = 'http://' . $_SERVER['HTTP_HOST'];
        }
        $path_cgi = '/italsoftcgi/eloq.cgi/';
        $fp = new Snoopy;
        $url = $host . $path_cgi . $model;
        $fp->submit($url, $_POST);
        return $fp->results;
    }

    public static function dateDiffDays($endDate, $beginDate) {
        $date_parts1 = explode('-', $beginDate);
        $date_parts2 = explode('-', $endDate);
        $start_date = gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
        $end_date = gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
        return $end_date - $start_date;
    }

    public static function dateDiffYears($endDate, $beginDate) {
        return round(self::dateDiffDays($endDate, $beginDate) / 365, 0);
    }

    public static function getParmEnte($codiceEnte = '') {
        if ($codiceEnte == '') {
            $codiceEnte = App::$utente->getKey('ditta');
        }

        try {
            $ITW_DB = ItaDB::DBOpen('ITALWEBDB', false);
        } catch (Exception $e) {
            Out::msgStop("Apertura Data Base", $e->getMessage());
            return false;
        }
        try {
            $PARMENTE_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE='" . $codiceEnte . "'", false);
        } catch (Exception $e) {
            Out::msgStop("DBSQLSelect", $e->getMessage());
            return false;
        }
        return $PARMENTE_rec;
    }

    public static function createPrivateUploadPath($token = '') {
        $privateUploadPath = self::getPrivateUploadPath($token);
        if (!@is_dir($privateUploadPath)) {
            if (@mkdir($privateUploadPath, 0777)) {
                return $privateUploadPath;
            } else {
                return false;
            }
        } else {
            if (!self::clearPrivateUploadPath($token)) {
                return false;
            }
            return $privateUploadPath;
        }
    }

    public static function deletePrivateUploadPath($token = '') {
        
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if (!$token) {
            return false;
        }
        
        $privateUploadPath = self::getPrivateUploadPath($token);
        if (!@is_dir($privateUploadPath)) {
            return true;
        } else {
            if (!self::clearPrivateUploadPath($token)) {
                return false;
            }
            if (!@rmdir($privateUploadPath)) {
                return false;
            }
            return true;
        }
    }

    public static function clearPrivateUploadPath($token = '') {
        //@TODO Metti controllo sul token
        // PER EVITARE CANCELLAZIONI SENZA TOKEN MOLTO PERICOLOSE MM 06.07.2015
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if (!$token) {
            return false;
        }


        $privateUploadPath = self::getPrivateUploadPath($token);
        if (!@is_dir($privateUploadPath)) {
            return true;
        }

        if (!$dh = @opendir($privateUploadPath))
            return false;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            if (is_dir($privateUploadPath . '/' . $obj)) {
                if (self::deleteDirRecursive($privateUploadPath . '/' . $obj) == false) {
                    closedir($dh);
                    return false;
                }
            } else {
                if (!@unlink($privateUploadPath . '/' . $obj)) {
                    closedir($dh);
                    return false;
                }
            }
        }
        closedir($dh);
        return true;
    }

    public static function listPrivateUploadPath($token = '') {
        $privateUploadPath = self::getPrivateUploadPath($token);
        if (!@is_dir($privateUploadPath)) {
            return false;
        }

        if (!$dh = @opendir($privateUploadPath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen['LIST'][$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $privateUploadPath . "/" . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => "Da definire"
            );
        }
        closedir($dh);
        $retListGen["PATH"] = $privateUploadPath;
        return $retListGen;
    }

    public static function getPrivateUploadPath($token = '') {
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if (!$token) {
            return false;
        }
        
        return self::getUploadPath(true) . "/" . $token;
    }

    public static function getUploadPath($create = false) {
        $uploadPath = App::getPath('temporary.uploadPath');
        if ($create == false) {
            return $uploadPath;
        }
        if (!$uploadPath) {
            return false;
        }
        if (!@is_dir($uploadPath)) {
            if (!@mkdir($uploadPath, 0777, true)) {
                return false;
            }
        }
        return $uploadPath;
    }

    /**
     * Restituisce il percorso della path temporanea per le applicazioni
     * @param type $subpath
     * @param type $token
     * @return string
     */
    public static function getAppsTempPath($subpath = false, $token = '') {
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if ($subpath) {
            return Config::getPath('temporary.appsPath') . "/" . $token . "/" . $subpath;
        } else {
            return Config::getPath('temporary.appsPath') . "/" . $token;
        }
    }

    /**
     * Restituisce la path temporanea per le applicazioni 
     * e se la path o l'eventuale subpath definita non esistono le crea.
     * @param type $subpath
     * @param type $token
     * @return boolean
     */
    public static function createAppsTempPath($subpath = false, $token = '') {
        $appsTempPath = self::getAppsTempPath($subpath, $token);
        if (!@is_dir($appsTempPath)) {
            if (@mkdir($appsTempPath, 0777, true)) {
                return $appsTempPath;
            } else {
                return false;
            }
        } else {
            if (!self::clearAppsTempPath($subpath)) {
                return false;
            }
            return $appsTempPath;
        }
    }

    /**
     * Cancella l'intera path temporanea in modo ricorsivo,
     * se definita la subpath cancella solo la subpath e le eventuali sottocartelle.
     * @param type $subpath
     * @param type $token
     * @return boolean
     */
    public static function deleteAppsTempPath($subpath = false, $token = '') {

        // PER EVITARE CANCELLAZIONI SENZA TOKEN MOLTO PERICOLOSE MM 06.07.2015
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if (!$token) {
            return false;
        }
        $appsTempPath = self::getAppsTempPath($subpath, $token);
        if (!$appsTempPath) {
            return false;
        }
        if (!@is_dir($appsTempPath)) {
            return true;
        } else {
            if (!self::deleteDirRecursive($appsTempPath, false)) {
                return false;
            }
            return true;
        }
    }

    /**
     * Svuolta la path temporanea di tutto il contenuto, senza però cancellare
     * la cartella. Se definita la subpath ne svuota il contenuto senza cancellarla.
     * @param type $subpath
     * @param type $token
     * @return boolean
     */
    public static function clearAppsTempPath($subpath = false, $token = '') {
        $appsTempPath = self::getAppsTempPath($subpath, $token);
        if (!@is_dir($appsTempPath)) {
            return true;
        }
        if (!$dh = @opendir($appsTempPath))
            return false;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            if (!@unlink($appsTempPath . '/' . $obj)) {
                closedir($dh);
                return false;
            }
        }
        closedir($dh);
        return true;
    }

    /**
     * Svuolta la path temporanea di tutto il contenuto in modo ricorsivo, senza però cancellare
     * la cartella. Se definita la subpath ne svuota il contenuto senza cancellarla.
     * @param type $subpath
     * @param type $token
     * @return boolean
     */
    public static function clearAppsTempPathRecursive($subpath = false, $token = '') {

        // PER EVITARE CANCELLAZIONI SENZA TOKEN MOLTO PERICOLOSE MM 06.07.2015
        if (!$token) {
            $token = App::$utente->getKey('TOKEN');
        }
        if (!$token) {
            return false;
        }

        $appsTempPath = self::getAppsTempPath($subpath, $token);
        if (!@is_dir($appsTempPath)) {
            return true;
        }
        //@TODO mettere controllo sul token
        return self::deleteDirRecursive($appsTempPath, true);
    }

    public static function deleteDirRecursive($dir, $deleteContentOnly = false) {
        if ($dir == ".") {
            return true;
        }
        if ($dir == "..") {
            return true;
        }
        if ($dir == "/") {
            return false;
        }
        if ($dir == "") {
            return false;
        }
        if (strpos($dir, "*") !== false) {
            return false;
        }
        if (strpos($dir, "#") !== false) {
            return false;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        if (self::deleteDirRecursive($dir . "/" . $object) == false) {
                            return false;
                        }
                    } else {
                        if (!@unlink($dir . "/" . $object)) {
                            return false;
                        }
                    }
                }
            }
            reset($objects);
            if ($deleteContentOnly !== true) {
                if (!@rmdir($dir)) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function listAppsTempPath($subpath = false, $token = '') {
        $appsTempPath = self::getAppsTempPath($subpath, $token);
        if (!@is_dir($appsTempPath)) {
            return false;
        }
        if (!$dh = @opendir($appsTempPath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen['LIST'][$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $privateUploadPath . "/" . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => "Da definire"
            );
        }
        closedir($dh);
        $retListGen["PATH"] = $appsTempPath;
        return $retListGen;
    }

    public static function getLogPath($subPath, $create = false) {
        $logPath = Config::getConf('log.log_folder');
        if (!$logPath){
            $logPath = ITA_BASE_PATH . '/var/log';
        }
        $logPath = ($subPath) ? $logPath . DIRECTORY_SEPARATOR . $subPath : $logPath;
        if ($create == false) {
            return $logPath;
        }
        if (!$logPath) {
            return false;
        }
        
        if (!@is_dir($logPath)) {
            if (!@mkdir($logPath, 0777, true)) {
                return false;
            }
        }
        return $logPath;
    }
    
    
    public static function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!@fwrite($fpw, $string)) {
            fclose($fpw);
            return false;
        }
        fclose($fpw);
        return true;
    }

    /**
     * 
     * @return string Ritorna una stringa per il base name di nomi file e path random e univoci per la propria sessione di lavoro
     */
    public static function getRandBaseName($prefix = '') {
        usleep(5);
        return md5(uniqid($prefix . microtime(true) . '-'));
    }

    public static function getFileTail($filePath, $num_to_get = 1) {
        $fp = fopen($filePath, 'r');
        if (!$fp) {
            return false;
        }
        $position = filesize($filePath);
        fseek($fp, $position - 1);
        $chunklen = 4096;
        while ($position >= 0) {
            $position = $position - $chunklen;
            if ($position < 0) {
                $chunklen = abs($position);
                $position = 0;
            }
            fseek($fp, $position);
            $data = fread($fp, $chunklen) . $data;
            if (substr_count($data, "\n") >= $num_to_get + 1) {
                preg_match("!(.*?\n){" . ($num_to_get - 1) . "}$!", $data, $match);
                return $match[0];
            }
        }
        fclose($fp);
        return $data;
    }

    public static function pathinfoFilename($path, $options = null) {
//        $info = pathinfo($path);
//        list($filename, $skip) = explode("." . $info['extension'], $info['basename']);

        $filename = pathinfo($path, PATHINFO_FILENAME);
        return $filename;
    }

    public static function writeIniFile($file, $array) {
        $r1 = array('"', "\r", "\n");
        $r2 = array(' ', '', '');

        $h = fopen($file, 'w');
        $n = '';

        foreach ($array as $node => $subarr) {
            if (is_array($subarr)) {
                $n = $n == '' ? '' : $n . "\r\n";
                fwrite($h, $n . "[" . str_replace($r1, $r2, $node) . "]");
                $n = "\r\n";

                foreach ($subarr as $k => $v) {
                    fwrite($h, $n . "$k = \"" . str_replace($r1, $r2, $v) . "\"");
                }
            }
        }

        fclose($h);

        return true;
    }

    /*
     * Alternativa a funzione php hex2bin se non esistente
     * 
     * http://php.net/manual/es/function.hex2bin.php
     * 
     */

    public static function hex2BinDecode($str) {
        $sbin = "";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i += 2) {
            $sbin .= pack("H*", substr($str, $i, 2));
        }

        return $sbin;
    }

    public static function friendlyErrorType($type) {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return "";
    }

    public static function stringXmlToArray($xmlString) {
        $xmlObj = new DOMDocument();
        $xmlObj->preserveWhiteSpace = false;
        $xmlObj->loadXML($xmlString);
        return self::xmlToArray($xmlObj);
    }

    public static function xmlToArray($root) {
        $result = null;

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE || $child->nodeType == XML_CDATA_SECTION_NODE) {                  
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = self::xmlToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::xmlToArray($child);
                }
            }
        }

        return $result;
    }

    private static function decodeUnicodePreserveCharactersCallback($match) {
        return self::decodeUnicodeCharactersCallback($match, true);
    }

    private static function decodeUnicodeCharactersCallback($match, $preserveCharacters = false) {
        switch ($match[0]) {
            // Referenza char map: http://www.alanwood.net/demos/ansi.html
            case '%u20AC': return 'Euro';
            case '%u2026': return '...';
            case '%u2018': return '\'';
            case '%u2019': return '\'';
            case '%u201C': return '"';
            case '%u201D': return '"';
            case '%u2013': return '-';
            case '%u2014': return '-';
            default: return $preserveCharacters ? $match[0] : '';
        }
    }

    /**
     * Decodifica entità unicode nella loro versione semplificata.
     * 
     * @param string $text Testo contenente i caratteri da decodificare.
     * @param boolean $preserveCharacters Comportamento per i caratteri non gestiti. Se false vengono
     * rimossi, se true non vengono alterati.
     * @return string Stringa con i caratteri decodificati.
     */
    public static function decodeUnicodeCharacters($text, $preserveCharacters = false) {
        if ($preserveCharacters) {
            return preg_replace_callback('/%u[0-9a-f]{4}/i', array(self, 'decodeUnicodePreserveCharactersCallback'), $text);
        } else {
            return preg_replace_callback('/%u[0-9a-f]{4}/i', array(self, 'decodeUnicodeCharactersCallback'), $text);
        }
    }

    /**
     * Permette di scaricare file tramite CURL (comodo al posto del file_get_contents se non è attiva l'estensione per https)
     * @param string $url
     * @return string
     */
    public static function curl_get_contents($url, $timeout = 60) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    public static function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        self::rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    public static function rmkdir($dir) {
        if (file_exists($dir) && is_dir($dir)) {
            return;
        }
        if (!file_exists($dir)) {
            $prevDir = dirname($dir);
            if (!file_exists(($prevDir)) || !is_dir($prevDir)) {
                self::rmkdir($prevDir);
            }
            mkdir($dir);
        }
    }

    public static function getPhpBin() {
        if (defined('PHP_BINARY'))
            return PHP_BINARY;
        return ITA_PHP_BINARY;
    }

    public static function generatePassword($length = 10) {
        $charset = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?');
        $charset_len = count($charset) - 1;

        $password = '';

        shuffle($charset);

        for ($i = 0; $i < $length; $i++) {
            $n = function_exists('random_int') ? random_int(0, $charset_len) : mt_rand(0, $charset_len);
            $password .= $charset[$n];
        }

        return $password;
    }

    public static function execAsync($command, $arguments = array(), $executor=self::PHP) {
        if($executor == self::PHP){
            $exec = ITA_PHP_BINARY;
        }
        else{
            $exec = $executor ?: '';
        }
        $exec .= ' ' . $command;

        if (is_array($arguments)) {
            $exec .= ' ' . implode(' ', $arguments);
        } else {
            $exec .= ' ' . $arguments;
        }

        $exec = trim($exec . ' &');

        $d = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w")  // stdout is a pipe that the child will write to
        );
        $result = proc_open($exec, $d, $p);
    }
    
    public static function isWindows() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public static function killProcess($pid){
        if(self::isWindows()){
            //Ogni tanto Apache perde la variabile d'ambiente PATH, la riassegno manualmente leggendola con getenv
            exec('SET PATH=\''.getenv('PATH').'\' && TASKKILL /F /PID '.$pid);
        }
        else{
            exec('kill -9 '.$pid);
        }
    }
}