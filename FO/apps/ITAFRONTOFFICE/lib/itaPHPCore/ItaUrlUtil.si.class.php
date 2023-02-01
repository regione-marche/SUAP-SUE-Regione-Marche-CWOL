<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ItaUrlUtil
 *
 * @author Michele Accattoli
 */
class ItaUrlUtil implements ItaUrlUtilInterface {

    // Utilizzato in costruttori per cms diverso da wordpress
    //private $wc;

    public static function GetPageUrl($data) {
            $wc = AV_WebController::getInstance();        
            //return htmlspecialchars($wc->modUrl($data));
            return $wc->modUrl($data);
    }

    // Prima parte dell'url di inclusione immagini
    public static function UrlInc() {
        return "tools";
    }

    public static function GetAbsolutePageUrl($data) {
        
    }

    public static function GetRelativePageUrl($data) {
        
    }
}
