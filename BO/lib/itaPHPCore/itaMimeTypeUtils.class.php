<?php

require_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';
/*
 * classe di utilità per calcolare il mimetype
 */

class itaMimeTypeUtils {

    const APACHE_MIME_TYPES_URL = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
    const GET_CONTENTS_TIMEOUT = 15;
    const MIME_CACHE_TIMEOUT = 86400;

    static $arrExceptions = array(
        'p7m' => 'application/octet-stream'
    );

    public static function getAllMimeTypes() {
        $cache = CacheFactory::newCache();
        $mimes = $cache->get("MimeTypes");
        if (!$mimes) {
            $mimes = array();

            $ctx = stream_context_create(array('http' => array('timeout' => self::GET_CONTENTS_TIMEOUT)));

            $mimeList = @file_get_contents(self::APACHE_MIME_TYPES_URL, false, $ctx);
            if (!$mimeList) {
                $mimeList = @itaLib::curl_get_contents(self::APACHE_MIME_TYPES_URL, self::GET_CONTENTS_TIMEOUT);
            }
            if (!$mimeList) {
                $mimeList = file_get_contents(ITA_LIB_PATH . '/apacheMimeTypes/mime.types');
            }
            if (!$mimeList) {
                return false;
            }
            $mimeList = explode("\n", $mimeList);

            foreach ($mimeList as $row) {
                if (isset($row[0]) && $row[0] !== '#' && preg_match_all('#([^\s]+)#', $row, $out) && isset($out[1]) && ($c = count($out[1])) > 1) {
                    for ($i = 1; $i < $c; $i++) {
                        $mimes[$out[1][$i]] = $out[1][0];
                    }
                }
            }
            $cache->set("MimeTypes", $mimes, self::MIME_CACHE_TIMEOUT);
        }
        return $mimes;
    }

    /**
     * Ritorna il mimetype abbinato ad una data estensione. Se l'estensione dovesse risultare sconosciuta verrà restituito 'application/octet-stream'
     * @return array
     */
    public static function getMimeTypes($ext = '', $ignoreExceptions = false) {
        $mimes = self::getAllMimeTypes();
        
        if(!$ignoreExceptions){
            $mimes = array_merge($mimes, self::$arrExceptions);
        }

        return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
    }

    /**
     * Restituisce il mime type di un file
     * @param string $filename
     * @return string mimetype
     */
    public static function estraiEstensione($filename, $ignoreExceptions= false, $forceUseExtension=false) {
        if (function_exists('mime_content_type') && $forceUseExtension !== true) {
            $mime = mime_content_type($filename);
            if ($mime) {
                return $mime;
            }
        }

        $pathInfo = pathinfo($filename);
        $extension = strtolower($pathInfo['extension']);
        return self::getMimeTypes($extension, $ignoreExceptions);
    }

    /**
     * Restituisce l'estensione del file legata al contentType
     * @param string $contentType
     * @return string estensione
     */
    public static function estraiEstensioneDaContentType($contentType) {
        $mimeTypes = self::getAllMimeTypes();
        $key = array_search($contentType, $mimeTypes);
        return $key !== null ? strtolower($key) : "";
    }

}
