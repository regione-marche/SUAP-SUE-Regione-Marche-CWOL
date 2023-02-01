<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class sueApp {

    static public $revision = "01.11.001";

    static function load() {
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praDizionario.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRep.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praVars.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibCustomClass.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibStandardExit.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praCustomClass.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDatiAggiuntivi.class.php');
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateLayout.class.php');
        require_once(ITA_SUE_PATH . '/SUE_italsoft/sueErr.class.php');
    }

    static function addCSSFrontOffice($blocco = null) {
        
    }

    static function addJsFrontOffice($blocco = null) {
        
    }

}

?>