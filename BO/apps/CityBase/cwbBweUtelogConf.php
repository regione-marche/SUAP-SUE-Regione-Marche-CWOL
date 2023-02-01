<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenRow.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';

function cwbBweUtelogConf() {
    $cwbBweUtelogConf = new cwbBweUtelogConf();
    $cwbBweUtelogConf->parseEvent();
    return;
}

class cwbBweUtelogConf extends cwbBpaGenRow {

    protected function initVars() {
        $this->libDB = new cwbLibDB_BWE();
        $this->skipAuth =true;
    }

    protected function sqlDettaglio($index) {
        $this->SQL = $this->libDB->getSqlLeggiBweUtelogConf();
    }

}

?>