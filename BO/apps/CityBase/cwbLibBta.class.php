<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbLibBta{
    public static function checkPIVAIta($piva){
        if(strlen($piva) != 11){
            return false;
        }
        $x = substr($piva, 0, 1) + substr($piva, 2, 1) + substr($piva, 4, 1) + substr($piva, 6, 1) + substr($piva, 8, 1) + substr($piva, 10, 1);
        $y = ((substr($piva, 1, 1)*2) > 9 ? (substr($piva, 1, 1)*2) - 9 : (substr($piva, 1, 1)*2));
        $y += ((substr($piva, 3, 1)*2) > 9 ? (substr($piva, 3, 1)*2) - 9 : (substr($piva, 3, 1)*2));
        $y += ((substr($piva, 5, 1)*2) > 9 ? (substr($piva, 5, 1)*2) - 9 : (substr($piva, 5, 1)*2));
        $y += ((substr($piva, 7, 1)*2) > 9 ? (substr($piva, 7, 1)*2) - 9 : (substr($piva, 7, 1)*2));
        $y += ((substr($piva, 9, 1)*2) > 9 ? (substr($piva, 9, 1)*2) - 9 : (substr($piva, 9, 1)*2));
        
        return ((($x+$y)%10) == 0);
    }
    
    public static function checkPIVALength($piva,$codnazi=null){
        if(empty($codnazi)){
            return (strlen($piva) == 11);
        }
        
        $libDB = new cwbLibDB_BTA();
        $filtri = array(
            'CODNAZI'=>$codnazi
        );
        $row = $libDB->leggiBtaNazion($filtri,false);
        return (strlen($piva) == $row['LLPIVANAZ']);
    }
}
?>