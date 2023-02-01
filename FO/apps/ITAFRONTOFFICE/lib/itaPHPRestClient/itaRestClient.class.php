<?php

/**
 *
 * Classe per collegamento rest servoice
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPRestClient
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    24.05.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaRestClient {

    private $curlopt_url;
    private $curlopt_ssl_verifyhost = false;
    private $curlopt_ssl_verifypeer = false;
    private $curlopt_followlocation = false;
    private $curlopt_header = false;
    private $curlopt_useragent;
    private $timeout = 4;
    private $result;
    private $headers;
    private $errCode;
    private $errMessage;
    private $httpStatus;
    private $debugLevel = false;
    private $debug;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function getCurlopt_url() {
        return $this->curlopt_url;
    }

    public function setCurlopt_url($curlopt_url) {
        $this->curlopt_url = $curlopt_url;
    }

    public function getCurlopt_ssl_verifyhost() {
        return $this->curlopt_ssl_verifyhost;
    }

    public function getCurlopt_ssl_verifypeer() {
        return $this->curlopt_ssl_verifypeer;
    }

    public function setCurlopt_ssl_verifyhost($curlopt_ssl_verifyhost) {
        $this->curlopt_ssl_verifyhost = $curlopt_ssl_verifyhost;
    }

    public function setCurlopt_ssl_verifypeer($curlopt_ssl_verifypeer) {
        $this->curlopt_ssl_verifypeer = $curlopt_ssl_verifypeer;
    }

    public function getCurlopt_followlocation() {
        return $this->curlopt_followlocation;
    }

    public function getCurlopt_header() {
        return $this->curlopt_header;
    }

    function getCurlopt_useragent() {
        return $this->curlopt_useragent;
    }

    function setCurlopt_useragent($curlopt_useragent) {
        $this->curlopt_useragent = $curlopt_useragent;
    }
    
    public function setCurlopt_followlocation($curlopt_followlocation) {
        $this->curlopt_followlocation = $curlopt_followlocation;
    }

    public function setCurlopt_header($curlopt_header) {
        $this->curlopt_header = $curlopt_header;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getHttpStatus() {
        return $this->httpStatus;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
    }

    public function getResult() {
        return $this->result;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    function getDebugLevel() {
        return $this->debugLevel;
    }

    function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
    }

    function getDebug() {
        return $this->debug;
    }

    function setDebug($debug) {
        $this->debug = $debug;
    }

    public function get($path, $data = array(), $headers = array(), $contentRaw = null, $mimeType = null) {
        return $this->call("GET", $path, $data, $headers, false, $contentRaw, $mimeType);
    }

    public function post($path, $data = array(), $headers = array(), $contentRaw = null, $mimeType = null) {
        return $this->call("POST", $path, $data, $headers, false, $contentRaw, $mimeType);
    }

    public function postMultipart($path, $data = array(), $headers = array()) {
        return $this->call("POST", $path, $data, $headers, true);
    }

    public function put($path, $data = array(), $headers = array(), $contentRaw = null, $mimeType = null) {
        return $this->call("PUT", $path, $data, $headers, false, $contentRaw, $mimeType);
    }

    public function delete($path, $data = array(), $headers = array()) {
        return $this->call("DELETE", $path, $data, $headers);
    }

    public function patch($path, $data = array(), $headers = array()) {
        return $this->call("PATCH", $path, $data, $headers);
    }

    private function call($verb, $path, $data, $headers, $isMultipart = false, $contentRaw = null, $contentType = null) {
        if (!$headers) {
            $headers = array();
        }
        try {
            if ($data) {
                $param = $this->curl_get_postfields($data, $isMultipart);
            }
            if ($isMultipart) {
                $headers = array_merge(
                        array("Content-Type: multipart/form-data; boundary={$param[0]}"), $headers
                );
                $encData = $param[1];
            } else {
                $encData = $param;
            }

            $resource = curl_init();

            // Valorizza content-type, altrimenti in certi casi non riesce a 
            // riconoscerlo automaticamente, e il servizio non riceve i dati raw
            if ($contentType != null) {
                array_push($headers, 'Content-Type: ' . $contentType);
            }

            $verbose = false;
            $out = null;

            if ($this->getDebugLevel() == true) {
                $verbose = true;
                $out = fopen('php://temp', 'w+');
            }

            $urlQuery = '';

            if ($verb === 'GET') {
                $urlQuery = ($encData) ? '?' . $encData : '';
            } elseif ($contentRaw != null && $encData) {
                $urlQuery = '?' . $encData;
            }

            @curl_setopt_array($resource, array(
                        CURLOPT_CONNECTTIMEOUT => $this->timeout,
                        CURLOPT_SSL_VERIFYHOST => $this->curlopt_ssl_verifyhost,
                        CURLOPT_SSL_VERIFYPEER => $this->curlopt_ssl_verifypeer,
                        CURLOPT_URL => $this->curlopt_url . $path . $urlQuery,
                        CURLOPT_CUSTOMREQUEST => $verb,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => $this->curlopt_followlocation,
                        CURLOPT_HEADER => $this->curlopt_header,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_VERBOSE => $verbose,
                        CURLOPT_STDERR => $out)
            );

            if ($this->curlopt_useragent) {
                curl_setopt($resource, CURLOPT_USERAGENT, $this->curlopt_useragent);
            }

            if ($verb !== 'GET') {
                curl_setopt($resource, CURLOPT_POSTFIELDS, $contentRaw != null ? $contentRaw : $encData);
            }

            $this->result = curl_exec($resource);
            if ($this->getDebugLevel() == true) {
                rewind($out);
                $this->setDebug(stream_get_contents($out));
                fclose($out);
            }

            if ($this->curlopt_header === true) {
                $headers = explode("\n", substr($this->result, 0, curl_getinfo($resource, CURLINFO_HEADER_SIZE)));
                $this->result = substr($this->result, curl_getinfo($resource, CURLINFO_HEADER_SIZE));

                $this->headers = array();
                $respone_number = -1;
                foreach ($headers as $header) {
                    if (strpos($header, 'HTTP/1.1') === 0) {
                        $respone_number++;
                        continue;
                    }

                    if (strpos($header, ':') !== false) {
                        list($header_key, $header_value) = array_map('trim', explode(':', $header, 2));
                        $this->headers[$respone_number][$header_key] = $header_value;
                    }
                }

                $this->headers = array_reverse($this->headers);
            }

            $this->httpStatus = curl_getinfo($resource, CURLINFO_HTTP_CODE);
            if ($this->result === false) {
                $this->setErrMessage(curl_error($resource));
                curl_close($resource);
                return false;
            }
            curl_close($resource);
        } catch (Exception $e) {
            $this->setErrMessage($e->getMessage());
            curl_close($resource);
            return false;
        }
        return true;
    }

    private function curl_get_postfields($data = array(), $isMultipart = false) {
        if ($isMultipart) {
            $assoc = $data['FIELDS'];
            $files = $data['FILES'];
            return $this->curl_custom_postfields($assoc, $files);
        }
        return http_build_query($data);
    }

    private function curl_custom_postfields(array $assoc = array(), array $files = array()) {

        // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

        // build normal parameters
        foreach ($assoc as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v),
            ));
        }

        /*
         * build file parameters:
         *  se elemento file � array si distingue tra nome file logico e mome file su disco,
         *  se elemento file � stringa si usa un unico valore (retro compatibilita)
         *  cio permette di pilotare il nome del file da inviare.
         */
        foreach ($files as $k => $v_file) {

            if (is_array($v_file)) {
                $v_filename = $v_file['filename'];
                $v = $v_file['filecontent'];
            } else {
                $v_filename = $v_file;
                $v = $v_file;
            }

            /*
             * Prendo lo stream dei file da variabile $v
             */
            switch (true) {
                case false === $v = realpath(filter_var($v)):
                case!is_file($v):
                case!is_readable($v):
                    throw new Exception('Errore nel file con id ' . $k . ' e path ' . $files[$k]);
            }
            $data = file_get_contents($v);

            /*
             * Pulisco il nome per il campo filename prendendo il basename
             * e togliendo caratteri non validi da variabile $v_filename
             */
            $v_filename = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v_filename));
            $k = str_replace($disallow, "_", $k);
            $v_filename = str_replace($disallow, "_", $v_filename);

            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v_filename}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }

        // generate safe boundary
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        array_walk($body, array($this, 'add_boundary'), $boundary);

        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";
        return array($boundary, implode("\r\n", $body));
    }

    function add_boundary(&$part, $key, $boundary) {
        $part = "--{$boundary}\r\n{$part}";
    }

//        public function formdata_call($data) {
//        $assoc = $data['FIELDS'];
//        $files = $data['FILES'];
//        try {
//            $postParam = $this->curl_custom_postfields($assoc, $files);
//            $resource = curl_init();
//            @curl_setopt_array($resource, array(
//                        CURLOPT_CONNECTTIMEOUT => $this->timeout,
//                        CURLOPT_URL => $this->curlopt_url,
//                        CURLOPT_POST => true,
//                        CURLOPT_RETURNTRANSFER => true,
//                        CURLOPT_POSTFIELDS => $postParam[1],
//                        CURLOPT_HTTPHEADER => array(//"Expect: 100-continue",
//                            "Content-Type: multipart/form-data; boundary={$postParam[0]}")
//            ));
//
//            $this->result = curl_exec($resource);
//            if ($this->result === false) {
//                $this->setErrMessage(curl_error($resource));
//                curl_close($resource);
//                return false;
//            }
//            curl_close($resource);
//        } catch (Exception $e) {
//            $this->setErrMessage($e->getMessage());
//            curl_close($resource);
//            return false;
//        }
//        return true;
//    }
}

?>
