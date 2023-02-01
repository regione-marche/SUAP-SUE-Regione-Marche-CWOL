<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of elmDate
 *
 * @author michele
 */
class emlDate {

    public static function eDate2Date($eDateIn) {
        /*
         * Elimino il  nme del giorno della della settimana perchè non congruente
         * strtotime sbaglia la data
         */
        if (strpos($eDateIn, ',') === false) {
            $edate = $eDateIn;
        } else {
            $edate = trim(substr($eDateIn, strpos($eDateIn,',')+1));
        }
        
        $result['day'] = date('d', strtotime($edate));
        $result['month'] = date('m', strtotime($edate));
        $result['desmonth'] = date('M', strtotime($edate));
        $result['year'] = date('Y', strtotime($edate));
        $result['date'] = date('Ymd', strtotime($edate));
        $result['time'] = date('H:i:s', strtotime($edate));
        return $result;
    }

}
