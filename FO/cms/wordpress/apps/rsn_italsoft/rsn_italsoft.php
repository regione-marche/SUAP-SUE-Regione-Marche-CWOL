<?php

/*
  Plugin Name: Remote Sign Italsoft
  Plugin URI: http://wordpress.org/#
  Description: Remote Sign Italsoft - Il plugin per includere il modulo esterno.
  Author: Italsoft SRL
  Version: 1.0.0
  Author URI: http://www.italsoft-mc.it/
 */


define('ITA_RSN_PATH', __DIR__ . '/includes');
define('ITA_RSN_PUBLIC', plugins_url('public', __FILE__));

function rsn_plugins_loaded() {
    if (class_exists('Itafrontoffice_Ajax')) {
        Itafrontoffice_Ajax::register('rsnAuth');
    }
}

add_action('plugins_loaded', 'rsn_plugins_loaded');

function rsnAuth($attrs) {
    if (!defined('ITA_DB_SUFFIX')) {
        define('ITA_DB_SUFFIX', $attrs['ente']);
    }

    require_once ITA_RSN_PATH . '/rsnAuth.php';
    $rsnAuth = new rsnAuth();
    $rsnAuth->setConfig($attrs);
    echo $rsnAuth->parseEvent();
}

function rsn_test_func($attr) {
    define('ITA_DB_SUFFIX', "01");
//    require_once ITA_RSN_PATH . "/rsnLib.class.php";
//    $rsnLib = new rsnLib();
    require_once ITA_RSN_PATH . '/rsnAuth.php';
    $rsnAuth = new rsnAuth();
    echo $rsnAuth->parseEvent();
}

add_shortcode('rsn_test', 'rsn_test_func');
