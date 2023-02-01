<?php

class itaDate {

// 
// ***** Commentata perchè "gregoriantojd" a volte da errore. DA VERIFICARE. ****
// 
//    public static function dateDiffDays($endDate, $beginDate) {
//        $date_parts1 = array();
//        $date_parts2 = array();
//        if (strlen($endDate) == 10) {
//            $date_parts1 = explode('-', $beginDate);
//            $date_parts2 = explode('-', $endDate);
//        } else {
//            $date_parts1[0] = substr($beginDate, 0, 4); //anno
//            $date_parts1[1] = substr($beginDate, 4, 2); //mese
//            $date_parts1[2] = substr($beginDate, 6, 2); //giorno
//            $date_parts2[0] = substr($endDate, 0, 4); //anno
//            $date_parts2[1] = substr($endDate, 4, 2); //mese
//            $date_parts2[2] = substr($endDate, 6, 2); //giorno
//        }
//        $start_date = gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
//        $end_date = gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
//        return $end_date - $start_date;
//    }
// 
//    
    public static function dateDiffDays($dat2, $dat1) {
        $date1 = new DateTime($dat1);
        $date2 = new DateTime($dat2);
        $interval = $date1->diff($date2);
        
        return $interval->format('%a');
        
//        $gg1 = substr($dat1, 6, 2);
//        $mm1 = substr($dat1, 4, 2);
//        $aa1 = substr($dat1, 0, 4);
//        $data1 = mktime(0, 0, 0, $mm1, $gg1, $aa1, 0);
//        $gg2 = substr($dat2, 6, 2);
//        $mm2 = substr($dat2, 4, 2);
//        $aa2 = substr($dat2, 0, 4);
//        $data2 = mktime(0, 0, 0, $mm2, $gg2, $aa2, 0);
//        $gg = abs(($data2 - $data1) / (60 * 60 * 24));
//        return $gg;
    }

    public static function formattedDateDiffDays($date1, $date2) {
        $date1 = str_replace('/', '-', $date1);
        $date2 = str_replace('/', '-', $date2);
        return self::dateDiffDays($date1, $date2);
//        $data1 = strtotime(str_replace('/', '-', $date1));
//        $data2 = strtotime(str_replace('/', '-', $date2));
//        return abs(($data2 - $data1) / (60 * 60 * 24));
    }

    public static function dateDiffYears($endDate, $beginDate) {
        $date1 = new DateTime($endDate);
        $date2 = new DateTime($beginDate);
        $interval = $date1->diff($date2);
        
        return $interval->format('%y');
//        return round(self::dateDiffDays($endDate, $beginDate) / 365, 0);
    }

    public static function addDays($data, $giorni) {
        $date = new DateTime($data);
        $interval = new DateInterval('P'.abs($giorni).'D');
        if($giorni>=0){
            $date->add($interval);
        }
        else{
            $date->sub($interval);
        }
        return $date->format('Ymd');
        
//        $giorno = substr($data, 6, 2);
//        $mese = substr($data, 4, 2);
//        $anno = substr($data, 0, 4);
//        return date("Ymd", mktime(0, 0, 0, $mese, $giorno + $giorni, $anno));
    }

    public static function addYears($data, $anni) {
        $date = new DateTime($data);
        $interval = new DateInterval('P'.abs($anni).'Y');
        if($anni>=0){
            $date->add($interval);
        }
        else{
            $date->sub($interval);
        }
        return $date->format('Ymd');
//        $giorno = substr($data, 6, 2);
//        $mese = substr($data, 4, 2);
//        $anno = substr($data, 0, 4);
//        return date("Ymd", mktime(0, 0, 0, $mese, $giorno, $anno + $anni));
    }

    public static function subtractDays($data, $giorni) {
        $date = new DateTime($data);
        $interval = new DateInterval('P'.abs($giorni).'D');
        if($giorni>=0){
            $date->sub($interval);
        }
        else{
            $date->add($interval);
        }
        return $date->format('Ymd');
//        return date('Ymd', strtotime("-$giorni day", strtotime($data)));
    }

    public static function addMonths($data, $mesi) {
        $date = new DateTime($data);
        $interval = new DateInterval('P'.abs($mesi).'M');
        if($mesi>=0){
            $date->add($interval);
        }
        else{
            $date->sub($interval);
        }
        return $date->format('Ymd');
//        $giorno = substr($data, 6, 2);
//        $mese = substr($data, 4, 2);
//        $anno = substr($data, 0, 4);
//        $timestamp = mktime(0, 0, 0, $mese + $mesi, $giorno, $anno);
//        return date("Ymd", "$timestamp");
    }

    public static function GetItaDateTime() {
        return date('Y-m-d\TH:i:s');
    }

    /**
     * 
     * @param type $dataNascita - formato AAAAMMGG
     * @return int - Età
     */
    public static function calcolaEta($dataNascita) {
        return self::dateDiffYears(date('Ymd'), $dataNascita);
//        if (!$dataNascita) {
//            return false;
//        }
//        $anno = substr($dataNascita, 0, 4);
//        $mese = substr($dataNascita, 4, 2);
//        $giorno = substr($dataNascita, 6, 2);
//        
//        $eta = date("Y") - $anno;
//        if ($mese > date('m')){
//            $eta--;
//        }
//        if ($mese == date('m') && $giorno > date('d')){
//            $eta--;
//        }
//        return $eta;
    }

}
