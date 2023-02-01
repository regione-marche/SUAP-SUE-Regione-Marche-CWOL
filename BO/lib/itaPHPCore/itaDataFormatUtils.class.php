<?php

/**
 * Utilità per formattazione
 *
 * @author m.biagioli
 */
class itaDataFormatUtils {
    public static function safeNumberFormat($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',') {
        return number_format(str_replace(',', '.', $number), $decimals, $dec_point, $thousands_sep);
    }
    
    public static function humanReadableFileSize($filesize){
        $spacePrefix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $i = 0;
        
        while ($filesize > 1024) {
            $filesize /= 1024;
            $i++;
        }
        return round($filesize, 2) . ' ' . $spacePrefix[$i];
    }
}
