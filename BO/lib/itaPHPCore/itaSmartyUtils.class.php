<?php

require_once ITA_LIB_PATH . '/itaPHPCore/itaDate.class.php';

class itaSmartyUtils {

    public function dateDiffDays($params, &$smartyObj) {
        return itaDate::formattedDateDiffDays($params['d1'], $params['d2']);
    }
    
    public function date2timestamp($params, &$smartyObj) {
        return strtotime(str_replace('/', '-', $params['date']));
    }

}
