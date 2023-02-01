<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class itaZip {
    static public $archiv;
    
  static function zipRecursive($absoluteRootPath, $data, $arcpf, $mode='zip', $obj='', $zipRootPath = true) {
    $ds = "/";
    if (is_object($obj) == false) {
        $archiv = new ZipArchive();
        if (!$archiv->open($arcpf, ZipArchive::CREATE)) {
            return false;
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

}

?>