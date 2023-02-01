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
    private $wc;

    function __construct() {
        
    }

    // Definizione di GetPageUrl per wordpress
    public static function GetPageUrl($data) {

        return $url;
    }

    // Prima parte dell'url di inclusione immagini
    public static function UrlInc() {
        
    }

    public static function GetAbsolutePageUrl($data) {
        
    }

    public static function GetRelativePageUrl($data) {
        
    }

}
