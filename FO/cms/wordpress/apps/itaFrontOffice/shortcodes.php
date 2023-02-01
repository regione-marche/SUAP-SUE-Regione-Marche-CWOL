<?php

function itafo_plugins_loaded() {
    if (class_exists('Itafrontoffice_Ajax')) {
        Itafrontoffice_Ajax::register('itafrontoffice_calendar_func');
    }
}

add_action('plugins_loaded', 'itafo_plugins_loaded');

function itafrontoffice_shortcodes_cssjs() {
    frontOfficeApp::$cmsHost->addCSS(ItaUrlUtil::UrlInc() . '/vendor/fullcalendar/3.10.0/fullcalendar.min.css');
    frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/moment/2.24.0/moment.min.js');
    frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/fullcalendar/3.10.0/fullcalendar.min.js');
    frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/fullcalendar/3.10.0/locale/it.js');
}

add_action('wp_head', 'itafrontoffice_shortcodes_cssjs');

function itafrontoffice_calendar_func($attrs) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attrs);

    $attrs = shortcode_atts(
        array(
        'ente' => '',
        'id' => ''
        ), $attrs
    );

    frontOfficeApp::setEnte($attrs['ente']);

    require_once ITA_FRONTOFFICE_PLUGIN . '/models/ifoCalendar.php';

    $ifoCalendar = new ifoCalendar();
    $ifoCalendar->setConfig($attrs);

    try {
        return utf8_encode($ifoCalendar->parseEvent());
    } catch (Exception $e) {
        return $ifoCalendar->ifoErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'ifoCalendar');
    }
}

add_shortcode('itafrontoffice_calendar', 'itafrontoffice_calendar_func');
