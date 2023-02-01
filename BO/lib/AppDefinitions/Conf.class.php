<?php

if (!class_exists('Conf')) {

    class Conf {

        const JQUERY_VERSION = '1.9.1';
        const JQUERY_UI_VERSION = '1.10.3';
        const JQUERY_UI_STYLE = 'pal-blue';
        const JQUERY_JQGRID_VERSION = '4.4.4';
        const JQUERY_PLUPLOAD_VERSION = '1.5.7';
        const JQUERY_TINYMCE_VERSION = '4.1.5';
        const JQUERY_FULLCALENDAR_VERSION = '2.6.0';
        const JQUERY_COLORPICKER_VERSION = '1.0.9';
        const ITA_ENGINE_VERSION = '5.0';

        static $jquery_plugin = array(
            'jquery.mb.browser' => 'min',
            'jquery.blockUI' => '265',
            'jquery.fieldSelection' => '010',
            'jquery.layout' => '130307',
            'jquery.layout.resizePaneAccordions' => '21',
            'jquery.jkey' => '1200',
            'jquery.maskedinput' => '130',
            'jquery.metadata' => '200',
            'jquery.pixastic' => '013',
            'jquery.timers' => '120',
            'jquery.url' => '200', // eliminare
            'jquery.validate' => '181',
            'date' => '010',
            'moment-with-locales' => '283.min',
            'jquery.imgareaselect.0910/scripts/jquery.imgareaselect' => 'min',
            'jquery.fgmenu' => '301', // -> eliminare e usare jquery ui
            'jquery.cookie' => '2010' // -> eliminare
        );
        static $jquery_plugin_css = array();
        static $js_scripts = array(
            'Smartagent' => '10'
        );

        const ITA_STYLE = 'ita-Style50.css';

    }

}