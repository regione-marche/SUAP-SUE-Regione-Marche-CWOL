<?php

class frontOfficeLib {

    const ICON_EXT_TEMPLATE = "File-Extension-%ext%-%size%x%size%.png";

    function __construct() {
        
    }

    function __destruct() {
        
    }

    public function formattaData($data_in) {
        $data_out = substr($data_in, 6, 2);
        $data_out .= "/";
        $data_out .= substr($data_in, 4, 2);
        $data_out .= "/";
        $data_out .= substr($data_in, 0, 4);
        return $data_out;
    }

    static public function converti($data) {
        $data2 = explode("/", $data);
        return $data2[2] . $data2[1] . $data2[0];
    }

    private function contentDownloadHeaders($fileName, $contentLength = false, $disposition = 'inline', $utf8decode = false) {
        $contentType = $this->getMimeType($fileName);
        @ob_end_clean();

        $ob_level = ob_get_level();
        for ($i = $ob_level; $i > 0; $i--) {
            @ob_end_clean();
        }

        header('X-Robots-Tag: noindex');

        header('Cache-Control: max-age=0');
        header('Cache-Control: no-store');
        header('Cache-Control: must-revalidate');

        header("Content-Type: $contentType" . ($utf8decode ? '; charset=utf-8' : ''));
        header('Content-Disposition: ' . $disposition . '; filename="' . addslashes(basename($fileName)) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Description: "' . addslashes(basename($fileName)) . '"');

        if ($contentLength !== false) {
            header("Content-Length: $contentLength");
        }

        @ob_end_flush();
    }

    public function scaricaBinario($binary, $fileName, $exit = true) {
        if (!is_string($binary)) {
            return false;
        }

        $this->contentDownloadHeaders($fileName, strlen($binary), 'attachment');

        set_time_limit(0);
        echo $binary;

        if ($exit) {
            exit;
        }

        return true;
    }

    private function validaFilepath($filepath) {
        if (strpos($filepath, 'file://') === 0) {
            $filepath = substr($filepath, 7);
        }

        $realpath = realpath($filepath);
        if ($filepath !== $realpath && in_array('..', explode(DIRECTORY_SEPARATOR, $filepath))) {
            return false;
        }

        return $filepath;
    }

    public function scaricaFile($filePath, $fileName, $flExit = true, $utf8decode = false, $headers = true) {
        $filePath = $this->validaFilepath($filePath);

        if (!$filePath) {
            return false;
        }

        if (!file_exists($filePath)) {
            return false;
        }

        if ($headers) {
            $this->contentDownloadHeaders($fileName, filesize($filePath), 'attachment', $utf8decode);
        } else {
            @ob_end_clean();

            $ob_level = ob_get_level();
            for ($i = $ob_level; $i > 0; $i--) {
                @ob_end_clean();
            }

            @ob_end_flush();
        }

        set_time_limit(0);
        $file = @fopen($filePath, "rb");

        if ($file) {
            while (!feof($file)) {
                print(@fread($file, 1024 * 1024));
                ob_flush();
                flush();
            }
            fclose($file);
        }

        if ($flExit) {
            exit;
        }

        return true;
    }

    public function vediAllegato($file, $flExit = true, $utf8decode = false, $headers = true) {
        $file = $this->validaFilepath($file);

        if (!$file) {
            return false;
        }

        if (!file_exists($file)) {
            return false;
        }

        if ($headers) {
            $this->contentDownloadHeaders($file, filesize($file), 'inline', $utf8decode);
        } else {
            @ob_end_clean();

            $ob_level = ob_get_level();
            for ($i = $ob_level; $i > 0; $i--) {
                @ob_end_clean();
            }

            @ob_end_flush();
        }

        readfile($file);

        if ($flExit) {
            exit;
        }

        return true;
    }

    public function getDownloadURI($filepath, $filename, $forceDownload = true, $utf8decode = false, $headers = true) {
        $blog_details = get_blog_details();
        $download_uri = rtrim($blog_details->path, '/') . '/itafrontoffice-download';

        $enc_data = itaCrypt::encrypt(json_encode(array(
                    'filepath' => $filepath,
                    'filename' => $filename
        )));

        $uri_params = "key=$enc_data";
        $uri_params .= '&forceDownload=' . ($forceDownload ? '1' : '0');
        $uri_params .= '&utf8decode=' . ($utf8decode ? '1' : '0');
        $uri_params .= '&headers=' . ($headers ? '1' : '0');

        return "$download_uri?$uri_params";
    }

    public function getMimeType($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'fdf':
                return "Application/vnd.fdf";

            case 'pdf':
                return "Application/pdf";

            case 'txt':
                return "text/plain";

            case 'html':
            case 'htm':
                return "text/html";

            case 'jpg':
            case 'jpeg':
                return "image/jpg";

            case 'gif':
                return "image/gif";

            case 'png':
                return "image/png";

            case 'doc':
            case 'docx':
                return "Application/msword";

            case 'xls':
                return "Application/vnd.ms-excel";

            case 'csv':
                return "text/csv";

            default:
                return "Application/octet-stream";
        }
    }

    public function getEnv_config($codice, $ITAFRONTOFFICE, $tipo = 'codice', $chiave = '', $multi = true) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE='$codice'";
            if ($chiave != '') {
                $sql .= " AND CHIAVE='$chiave'";
            }
        } else {
            $sql = "SELECT * FROM ENV_CONFIG WHERE ROWID=$codice";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($ITAFRONTOFFICE, $sql, $multi);
    }

    /**
     * Ritorna la differenza in giorni tra due date
     * @param type $endDate yyyymmdd
     * @param type $beginDate yyyymmdd
     * @return type
     */
    public function dateDiffDays($dat2, $dat1) {
        $gg1 = substr($dat1, 6, 2);
        $mm1 = substr($dat1, 4, 2);
        $aa1 = substr($dat1, 0, 4);
        $data1 = mktime(0, 0, 0, $mm1, $gg1, $aa1, 0);
        $gg2 = substr($dat2, 6, 2);
        $mm2 = substr($dat2, 4, 2);
        $aa2 = substr($dat2, 0, 4);
        $data2 = mktime(0, 0, 0, $mm2, $gg2, $aa2, 0);
        $gg = abs(($data2 - $data1) / (60 * 60 * 24));
        return $gg;
    }

    /**
     * 
     * @param type $file_name
     * @param type $size
     * @return type
     */
    static public function getExtensionIconUrl($file_name, $size = "32") {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $icon = str_replace('%size%', $size, str_replace("%ext%", ucfirst($ext), self::ICON_EXT_TEMPLATE));
        $iconUrl = ItaUrlUtil::UrlInc() . "/images/$icon";
        return $iconUrl;
    }

    /**
     * Ritorna l'URL dell'icona specificata.
     * 
     * @param string $icon Nome del file.
     * @return string URL dell'icona.
     */
    static public function getIcon($icon) {
        return ItaUrlUtil::UrlInc() . "/images/icons/$icon.png";
    }

    /**
     * Ritorna l'URL dell'icona relativa al tipo di file specificato.
     * 
     * @param string $filename Percorso del file.
     * @return string URL dell'icona.
     */
    static public function getFileIcon($filename) {
        $icon = 'file-empty';
        switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            case 'tar':
            case 'zip':
            case 'rar':
            case '7z':
                $icon = 'file-archive';
                break;

            case 'htm':
            case 'doc':
            case 'docx':
                $icon = 'file-doc';
                break;

            case 'exe':
                $icon = 'file-exe';
                break;

            case 'pdf':
                $icon = 'file-pdf';
                break;

            case 'ppt':
                $icon = 'file-ppt';
                break;

            case 'txt':
                $icon = 'file-txt';
                break;

            case 'xls':
            case 'xlsx':
                $icon = 'file-xls';
                break;

            case 'p7m':
                $icon = 'file-p7m';
                break;

            case 'png':
            case 'jpg':
            case 'jpe':
            case 'jpeg':
            case 'gif':
            case 'bmp':
            case 'tif':
            case 'tiff':
                $icon = 'file-image';
                break;
        }

        return self::getIcon($icon);
    }

    static public function sysLog($str) {
        openlog("itaFrontOfficeLog", LOG_PID, LOG_USER);
        syslog(LOG_INFO, $str);
        closelog();
    }

    static public function friendlyErrorType($type) {
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

    /**
     * Versione statica di $this->formattaData.
     * Converte una data 'yyyymmdd' nel formato 'dd/mm/yyyy'.
     * 
     * @param string $yyyymmdd
     * @return string
     */
    static public function convertiData($yyyymmdd) {
        return sprintf('%s/%s/%s', substr($yyyymmdd, 6, 2), substr($yyyymmdd, 4, 2), substr($yyyymmdd, 0, 4));
    }

    static public function formattedDateDiffDays($date1, $date2) {
        $data1 = strtotime(str_replace('/', '-', $date1));
        $data2 = strtotime(str_replace('/', '-', $date2));
        return abs(($data2 - $data1) / (60 * 60 * 24));
    }

    static public function addDayToDate($date, $returnFormat = "Ymd", $numDays = "1") {
        return date($returnFormat, strtotime("+$numDays day", strtotime($date)));
        //return date($returnFormat, strtotime("+1 day", strtotime($date)));
    }
    
    static public function delDayToDate($date, $returnFormat = "Ymd") {
        return date($returnFormat, strtotime("-1 day", strtotime($date)));
    }

    static public function decodeGatewayData($garble) {
        require_once ITA_LIB_PATH . '/itaPHPCore/itaCrypt.class.php';
        $base64 = strtr($garble, '._-', '+/=');
        $json = itaCrypt::decrypt($base64);
        $data = json_decode($json, true);

        return $data;
    }

    static public function formatFileSize($a_bytes) {
        if ($a_bytes < 1024) {
            return $a_bytes . ' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) . ' KiB';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MiB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GiB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) . ' TiB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) . ' PiB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) . ' EiB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) . ' ZiB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) . ' YiB';
        }
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
    static public function docx2Pdf($inputfile, $outputfile = false, $params = array(), $overwrite = true) {
        $return = array(
            'STATO' => 'OK',
            'MESSAGGIO' => '',
            'OUTPUT' => ''
        );

        if (!file_exists($inputfile)) {
            $return['STATO'] = 'KO';
            $return['MESSAGGIO'] = 'Il file di origine non esiste';
            return $return;
        }

        /*
         * Valorizzazione parametri
         */

        $defaults = array(
            'envpath' => '',
            'quality' => '90',
            'pdfa' => '1',
            'lossless' => '0',
            'verbose' => '0'
        );

        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());
        $frontOfficeLib = new frontOfficeLib;

        foreach ($defaults as $k => $v) {
            $envconf_rec = $frontOfficeLib->getEnv_config('PDFUNOCONV', $ITALWEB_DB, 'codice', strtoupper($k), false);
            $defaults[$k] = $envconf_rec ? $envconf_rec['CONFIG'] : $v;
        }

        $p = array_merge($defaults, $params);

        /*
         * Gestione output file
         */

        if (!$outputfile) {
            $outputfile = ITA_FRONTOFFICE_TEMP . '/' . md5(rand() . microtime()) . '.pdf';
        }

        if (file_exists($outputfile)) {
            if (!$overwrite) {
                $return['STATO'] = 'KO';
                $return['MESSAGGIO'] = 'Il file di destinazione è già presente';
                return $return;
            }

            if (!unlink($outputfile)) {
                $return['STATO'] = 'KO';
                $return['MESSAGGIO'] = 'Il file di destinazione è già presente e non è possibile eliminarlo';
                return $return;
            }
        }

        /*
         * Comando
         */

        $cmd = "{$p['envpath']}unoconv -f pdf -o $outputfile -e Quality={$p['quality']} -e SelectPdfVersion={$p['pdfa']} -e UseLosslessCompression={$p['lossless']}";

        /*
         * Parametri opzionali
         */

        if ($p['verbose'] && $p['verbose'] != '0') {
            $cmd .= " -" . str_repeat('v', $p['verbose']);
        }

        if ($p['pagerange']) {
            $cmd .= " -e PageRange={$p['pagerange']}";
        }

        /*
         * Esecuzione comando
         */

        exec("$cmd $inputfile", $output, $result);

        if (!file_exists($outputfile) /* && $result != 0 */) {
            $return['STATO'] = 'KO';
            $return['MESSAGGIO'] = "Errore nella conversione del file.\n$cmd $inputfile\n" . implode("\n", $output);
            return $return;
        }

        $return['OUTPUT'] = $outputfile;

        return $return;
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

    static public function redirect($uri) {
        @ob_end_clean();
        $ob_level = ob_get_level();
        for ($i = $ob_level; $i > 0; $i--) {
            @ob_end_clean();
        }

        @ob_end_flush();

        header('Location: ' . $uri);
        exit;
    }

    static public function copyDirectory($source, $destination) {
        if (!file_exists($source)) {
            return true;
        }

        if (is_dir($source)) {
            mkdir($destination, 0755, true);

            foreach (
                $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item
            ) {
                if ($item->isDir()) {
                    mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                } else {
                    if (!copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                        return false;
                    }
                }
            }
        } else {
            if (!file_exists(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            if (!copy($source, $destination)) {
                return false;
            }
        }

        return true;
    }

    static public function removeDirectory($source) {
        if (!file_exists($source) || !is_dir($source)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        rmdir($source);
    }

}
