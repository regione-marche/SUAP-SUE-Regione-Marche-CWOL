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
        global $wpdb;
        $wpdb->select(DB_NAME);

        $url = "";
        $found = false;

        // Se è indicato un id di un post, lancia permalink con il parametro
        foreach ($data as $key => $value) {
            if ($key == 'p') {
                $url = get_permalink($value);
                $found = true;
            }
        }

        // ..altrimenti lancia permalink senza parametro..
        if ($found == false) {
            $url = get_permalink();

            if (!$url) {
                $url = get_permalink(frontOfficeApp::$cmsHost->getCurrentPageID());
            }
        }

        // ..infine completa il path con le variabili!
        foreach ($data as $key => $value) {
            if ($key == 'p') {
                continue;
            }
            $sep = '&';
            if (strpos($url, '?') === false) {
                $sep = "?";
            }

            $url .= $sep . $key . "=" . urlencode($value);
        }

        if (preg_match('/&lang=[a-z]{2,3}/i', $url, $matches)) {
            $url = str_replace($matches[0], '', $url) . $matches[0];
        }

        return $url;
    }

    // Prima parte dell'url di inclusione immagini
    public static function UrlInc() {
        global $wpdb;
        $wpdb->select(DB_NAME);
        // site_url e WPINC sono specifici di wordpress
        return plugins_url('public', ITA_FRONTOFFICE_INCLUDES);
    }

    public static function GetAbsolutePageUrl($data) {
        $urlParts = parse_url(self::GetPageUrl($data));
        return $urlParts['path'] . ($urlParts['query'] ? '?' . $urlParts['query'] : '');
    }

    public static function GetRelativePageUrl($data) {
        $pageUrl = self::GetAbsolutePageUrl($data);
        $urlParts = parse_url($pageUrl);

        if (strpos($_SERVER['REQUEST_URI'], $urlParts['path']) === 0) {
            return str_replace($urlParts['path'], '', $pageUrl);
        }

        return $pageUrl;
    }

}
