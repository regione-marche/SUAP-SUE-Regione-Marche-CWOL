<?php

/*
 * classe di utilità per la gestione di files e cartelle
 */

class itaFileUtils {

    /**
     * Copia cartella
     * @param string $src Cartella sorgente
     * @param type $dst Cartella destinazione
     */
    public static function copyDir($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    self::copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * Cancella cartella
     * @param string $dir Cartella da cancellare
     */
    public static function removeDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        self::removeDir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

}
