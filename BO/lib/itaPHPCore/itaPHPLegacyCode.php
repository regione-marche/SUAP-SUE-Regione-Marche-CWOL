<?php

if (!function_exists('array_column')) {

    function array_column($array, $column_key) {
        $result = array();

        foreach ($array as $v) {
            $result[] = $v[$column_key];
        }

        return $result;
    }

}

if (!function_exists('cal_days_in_month')) {

    function cal_days_in_month($calendar, $month, $year) {
        $dt = DateTime::createFromFormat('d-m-Y', "1-$month-$year");
        if (!$dt || $month > 12) {
            return false;
        }

        return $dt->format('t');
    }

}

if (!function_exists('cal_days_in_month')) {
    function cal_days_in_month($calendar, $month, $year) {
        $dt = new DateTime();
        $dt->setDate($year, $month, 1);

        if (!$dt || $month > 12) {
            return false;
        }

        return (int) $dt->format('t');
    }
}
