<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class suapApp {

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
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAudit.class.php');
        require_once(ITA_SUAP_PATH . '/SUAP_italsoft/suapErr.class.php');
    }

    static function addCSSFrontOffice($blocco = null) {
        frontOfficeApp::$cmsHost->addCSS(ITA_SUAP_PUBLIC . '/SUAP_praMod/style.css', $blocco);
        frontOfficeApp::$cmsHost->addCSS(ITA_SUAP_PUBLIC . '/SUAP_praInf/style.css', $blocco);
        frontOfficeApp::$cmsHost->addCSS(ITA_SUAP_PUBLIC . '/SUAP_praMup/style.css', $blocco);
        frontOfficeApp::$cmsHost->addCSS(ITA_SUAP_PUBLIC . '/SUAP_praNews/style.css', $blocco);
        frontOfficeApp::$cmsHost->addCSS(ITA_SUAP_PUBLIC . '/SUAP_praDoc/style.css', $blocco);
    }

    static function addJsFrontOffice($blocco = null) {
        
    }

}

?>