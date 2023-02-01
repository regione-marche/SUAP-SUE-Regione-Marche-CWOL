<?php
class utiEmailDate {
    public static function eDate2Date($edate) {
        $_mesi = array(
                "01"=>"Jan",
                "02"=>"Feb",
                "03"=>"Mar",
                "04"=>"Apr",
                "05"=>"May",
                "06"=>"Jun",
                "07"=>"Jul",
                "08"=>"Aug",
                "09"=>"Sep",
                "10"=>"Oct",
                "11"=>"Nov",
                "12"=>"Dec"
        );
        $result=array();
        if (strpos($edate, ",")!=0) {
            $ar1=explode(",", $edate);
            $resto=$ar1[1];
            $result['dayweek']=$ar1[0];
        }else {
            $resto=$edate;
        }
        $ar1=explode(" ",$resto);
        $ar1=self::remove_item_by_value($ar1, '', false);
        $result['day']=$ar1[0];
        $result['month']=array_search($ar1[1], $_mesi,true);
        $result['desmonth']=$ar1[1];
        $result['year']=$ar1[2];
        $result['date']=$result['year'].$result['month'].str_pad($result['day'],2,'0',STR_PAD_LEFT);
        $result['time']=$ar1[3];
        return $result;
    }
    public static function remove_item_by_value($array, $val = '', $preserve_keys = true) {
        if (empty($array) || !is_array($array)) return false;
        if (!in_array($val, $array)) return $array;

        foreach($array as $key => $value) {
            if ($value == $val) unset($array[$key]);
        }

        return ($preserve_keys === true) ? $array : array_values($array);
    }
}
?>
