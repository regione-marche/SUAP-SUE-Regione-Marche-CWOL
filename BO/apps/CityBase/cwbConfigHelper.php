<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

class cwbConfigHelper {

    public static function getBgeNumciv() {
        $bgeNumciv_rec = App::$utente->getKey('BGE_NUMCIV');
        if (!$bgeNumciv_rec) {
            self::initBgeNumciv();
        }

        return App::$utente->getKey('BGE_NUMCIV');
    }

    public static function setBgeNumciv($bgeNumciv) {
        App::$utente->setKey('BGE_NUMCIV', $bgeNumciv);
    }

    public static function initBgeNumciv() {
        try {
            $libDB = new cwbLibDB_GENERIC();
            $bgeNumciv = $libDB->leggiGeneric('BGE_NUMCIV', array(), false);
        } catch (Exception $e) {
            out::msgInfo('CitywareOneline', 'Impossibile determinare la formattazione dei civici, verrà usato il default');
        }
        self::setBgeNumciv($bgeNumciv);
    }

}

?>