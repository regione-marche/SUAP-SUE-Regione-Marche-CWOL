<?php

class itaSmartyUtils {

    public function dateDiffDays($params, &$smartyObj) {
        return frontOfficeLib::formattedDateDiffDays($params['d1'], $params['d2']);
    }

    public function date2timestamp($params, &$smartyObj) {
        return strtotime(str_replace('/', '-', $params['date']));
    }

}
