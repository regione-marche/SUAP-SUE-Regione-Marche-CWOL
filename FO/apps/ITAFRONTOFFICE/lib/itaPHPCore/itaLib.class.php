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

}

?>
