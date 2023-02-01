<?php

if (!class_exists('Conf')) {

    class Conf {

        const JQUERY_VERSION = '2.1.1';
        const JQUERY_MOBILE_VERSION = '1.4.5';
        const JQUERY_MOBILE_STYLE = 'redmond';
        const ITA_MOBILE_VERSION = '1.0';
        const JQUERY_PLUPLOAD_VERSION = '1.5.7';

        static $jquery_plugin = array(
            'jquery.metadata' => '200',
            'jquery.validate' => '181',
            'jquery.jkey' => '1200',
            'jquery-ui.1.11.3' => 'min', // draggable, autocomplete
            'jquery.ui' => 'datepicker',
            'jquery.mobile' => 'datepicker',
            'jquery.maskedinput' => '140',
            'date' => '010'
        );
        static $jquery_plugin_css = array(
            'jquery.mobile' => 'datepicker',
            'jquery.mobile.datepicker' => 'theme',
            'jquery-ui' => 'min'
        );

        const ITA_STYLE = 'ita-Mobile10.css';

    }

}