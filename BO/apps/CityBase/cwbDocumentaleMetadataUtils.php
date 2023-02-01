<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

class cwbDocumentaleMetadataUtils {

    /**
     * Torna uno spazio
     */
    static function blank(){
        return " ";
    }
    
    /**
     * Torna il nome utente
     */
    static function nomeUtente(){
        return cwbParGen::getUtente();
    }
    
    static function descrizioneEnte(){
        return cwbParGen::getDesente();
    }
}
