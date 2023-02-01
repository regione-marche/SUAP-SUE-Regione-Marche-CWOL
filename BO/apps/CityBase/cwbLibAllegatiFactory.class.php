<?php

include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibAllegatiFesDocall.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibAllegatiFesLiqAll.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiBtaSoggal.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiBtaDurcal.class.php';

class cwbLibAllegatiFactory {

    private static $contestiAmmessi = array(
        'FES_DOCALL' => 'cwfLibAllegatiFesDocall',
        'FES_LIQALL' => 'cwfLibAllegatiFesLiqAll',
        'BTA_SOGGAL' => 'cwbLibAllegatiBtaSoggal',
        'BTA_DURCAL' => 'cwbLibAllegatiBtaDurcal'
    );

    /**
     * Ritorna l'istanza della classe passata come contesto 
     */
    public static function getLibAllegato($contesto = null) {
        if (isset(self::$contestiAmmessi[$contesto]) == null) {
            throw new Exception('Il contesto: ' . $contesto . ' non è ammesso');
        }
        return new self::$contestiAmmessi[$contesto]($contesto);
    }

}
