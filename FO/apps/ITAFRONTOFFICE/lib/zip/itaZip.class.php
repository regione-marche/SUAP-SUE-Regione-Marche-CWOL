<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class itaZip {

    static public $archiv;

    static public function createArchive($zipPath) {
        $archiv = new ZipArchive();
        if (!$archiv->open($zipPath, ZipArchive::CREATE)) {
            return false;
        } else {
            self::$archiv=$archiv;
            return true;
        }
    }

    static public function addFromString($name,$data) {
        self::$archiv->addFromString($name, $data);
    }
    
    static public function closeArchive(){
        self::$archiv->close();
    }

    /**
     * 
     * @param type $absoluteRootPath
     * @param type $data
     * @param type $arcpf
     * @param type $mode
     * @param type $obj
     * @param type $zipRootPath
     * @return type
     */
    static function zipRecursive($absoluteRootPath, $data, $arcpf, $mode = 'zip', $obj = '', $zipRootPath = true) {
        $ds = "/";
        if (is_object($obj) == false) {
            $archiv = new ZipArchive();
            if (!$archiv->open($arcpf, ZipArchive::CREATE)) {
                echo "errore creazione:" . $arcpf . "<br>";
            }
        } else {
            $archiv = & $obj;
        }

        if (!$zipRootPath) {
            $absoluteRootPath = $data;
        }
        if ($mode == 'zip') {
            if (is_dir($data) == true) {
                if ($zipRootPath) {
                    $archiv->addEmptyDir(str_replace($absoluteRootPath . $ds, '', $data));
                }
                $files = scandir($data);
                $bad = array('.', '..');
                $files = array_diff($files, $bad);
                foreach ($files as $ftmp) {
                    if (is_dir($data . $ds . $ftmp) == true) {
                        $archiv = itaZip::zipRecursive($absoluteRootPath, $data . $ds . $ftmp, $arcpf, 'zip', $archiv);
                    } elseif (is_file($data . $ds . $ftmp) == true) {
                        $archiv->addFile($data . $ds . $ftmp, str_replace($absoluteRootPath . $ds, '', $data . '/' . $ftmp));
                    }
                }
            } elseif (is_file($data) == true) {
                $archiv->addFile($data, str_replace($absoluteRootPath . $ds, '', $data));
            }
        }
        if (is_object($obj) == false) {
            $stato = $archiv->status;
            $files = $archiv->numFiles;
            $archiv->close();
            return $stato;
        } else {
            return $archiv;
        }
    }

    static function Unzip($file, $extractPath) {
        $zip = new ZipArchive;
        $ret = $zip->open($file);
        //if ($zip->open($file) === TRUE) {
        if ($ret === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    static function listZip($file) {
        $listZip = array();
        $za = new ZipArchive();
        if ($za->open($file) !== true) {
            return false;
        }
        $listZip['numFiles'] = $za->numFiles;
        $listZip['status'] = $za->status;
        $listZip['statusSys'] = $za->statusSys;
        $listZip['filename'] = $za->filename;
        $listZip['comment'] = $za->comment;
        for ($i = 0; $i < $za->numFiles; $i++) {
            $listZip['statIndex'][$i] = $za->statIndex($i);
        }
        $za->close();
        return $listZip;
    }

}

?>