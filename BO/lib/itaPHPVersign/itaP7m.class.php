<?php

require_once ITA_LIB_PATH . '/QXml/QXml.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.ARSS.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.DSS.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.j4sign.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.j4signDSS.class.php';

class itaP7m {

    public $file;
    public $tempPath;
    public $xmlFileResult;
    public $xmlStringResult;
    public $xmlStructResult;
    public $arrContentFiles;
    public $contentSHA;
    public $subLevelObj;
    public $contentFileMarcato;

    public static function getP7mInstance($file, $verify = true) {

        $versignEngine = App::getConf("itaVersign.versign_engine");
        if ($versignEngine == null) {
            $versignEngine = "ARSS";
        }
        switch ($versignEngine) {
            case "ARSS":
                return itaP7mARSS::getInstance($file, $verify);
                break;
            case "DSS":
                return itaP7mDSS::getInstance($file, $verify);
                break;
            case "j4sign-DSS":
                return itaP7mj4signDSS::getInstance($file, $verify);
                break;
            case "j4sign":
                return itaP7mj4sign::getInstance($file, $verify);
                break;
            default:
                return false;
                break;
        }
    }

    function getContentFileMarcato() {
        return $this->contentFileMarcato;
    }

    function setContentFileMarcato() {
        $path = pathinfo($this->getContentFileName(), PATHINFO_DIRNAME);
        $base = pathinfo($this->getContentFileName(), PATHINFO_BASENAME);
        $fileMarcato = $path . "/marcato_" . $base;
        if(!copy($this->getContentFileName(), $fileMarcato)){
            return false;
        }
        $this->contentFileMarcato = $fileMarcato;
    }

    public function getFileName() {
        return $this->file;
    }

    protected static function createTempPath($file) {
        if (!$file) {
            return false;
        }
        $subPath = "p7m-file-" . md5($file . microtime());
        $tempPath = itaLib::getAppsTempPath($subPath);
        itaLib::deleteDirRecursive($tempPath);
        $tempPath = itaLib::createAppsTempPath($subPath);
        return $tempPath;
    }

    protected function parseLdapDn($dn) {
        /*
         * Replico il funzionamento di un eventuale funzione 'canonical_dn'
         * per la pulizia della stringa $dn da chiavi che iniziano per 'OID.'.
         */
        $dn = preg_replace('/OID\.(\d+\.\d+.\d+.\d+=)/', '$1', $dn);
        //
        $parsr = ldap_explode_dn($dn, 0);
        $out = array();
        foreach ($parsr as $key => $value) {
            if (strstr($value, '=') !== false) {
                list($prefix, $data) = explode("=", $value);
                //$data=preg_replace("/\\\\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\\\1')).''", $data);
                if (isset($current_prefix) && $prefix == $current_prefix) {
                    $out[$prefix][] = $data;
                } else {
                    $current_prefix = $prefix;
                    $out[$prefix] = array();
                    $out[$prefix][] = $data;
                }
            }
        }
        return $out;
    }

}

?>