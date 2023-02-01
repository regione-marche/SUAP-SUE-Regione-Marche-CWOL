<?php

require_once ITA_LIB_PATH . '/QXml/QXml.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.ARSS.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.DSS.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.j4sign.class.php';
require_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.j4signDSS.class.php';

class itaP7m {

    public static function getP7mInstance($file, $tipoVerifica = "j4sign", $verify = true) {
        switch ($tipoVerifica) {
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

    protected static function createTempPath($file) {
        if (!$file) {
            return false;
        }
        $subPath = "p7m-file-" . md5($file . microtime());
        $tempPath = pathinfo($file, PATHINFO_DIRNAME) . "/" . $subPath;
        if (@mkdir($tempPath, 0777, true)) {
            return $tempPath;
        } else {
            return false;
        }
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

        if (strpos($dir, " ") !== false) {
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