<?php

/* Definisce la cartella delle librerie base italsoft */
define('ITA_BASE_PATH', ITA_FRONTOFFICE_PLUGIN);
define('ITA_CONFIG_PATH', ITA_BASE_PATH . '/config');
define('ITA_LIB_PATH', ITA_BASE_PATH . '/lib');

define('ITA_FRONTOFFICE_LOG', "/var/log/");
define('ITA_FRONTOFFICE_TIMEZONE', 'Europe/Rome');
define('ITA_FRONTOFFICE_TEMP', "/users/tmp/itaFrontOffice/");
define('ITA_FRONTOFFICE_JVM_PATH', "/opt/jre1.6.0_29/bin/java");
define('ITA_FRONTOFFICE_JVM8_PATH', "/opt/jdk1.8.0_111/bin/java");
define('ITA_FRONTOFFICE_OPENSSL_PATH', "/etc/ssl/openssl.cnf");
define('ITA_FRONTOFFICE_DISALLOW_SMTP_SYNC', false);
define('ITA_FRONTOFFICE_ACCESS_SERVICES_LOGS', false);
//define('ITA_FRONTOFFICE_CRYPT_SECRET', '');
define('ITA_FRONTOFFICE_SECURITY_FILTER_INPUT', true);

define('ITA_CITYPORTAL_REST_URL', '');
define('ITA_CITYWARE_ACCESS_PARAMS', array(
    "protocol" => 'http',
    "webServerUrl" => "192.168.15.5:80",
    "appServerUrl" => 'srvdbep:1001',
    "omnisCGI" => '/cgi-bin/nph-omniscgi',
    "defaultLibrary" => 'CITYWARE',
    "remoteTask" => 'RT_HTTP_REQUEST',
    "remoteTaskInt" => 'RT_HTTP_REQUEST_INT',
    "parHexFormat" => 'TRUE'
));

/*
 * Parametri gestione errori PHP
 */
define('ITA_ERROR_HANDLER', 0);
define('ITA_ERROR_HANDLER_OUTPUT', 0);
define('ITA_ERROR_HANDLER_FILE', '');
