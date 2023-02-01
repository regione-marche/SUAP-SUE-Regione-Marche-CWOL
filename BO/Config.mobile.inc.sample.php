<?php

/*
 * File di configurazione per itaMobile.
 */

/**
 * Definisce il percorso di installazione di itaEngine.
 */
define('ITA_BASE_PATH', '/users/itaEngine');

/**
 * Definisce il percorso della cartella 'config'.
 */
define('ITA_CONFIG_PATH', ITA_BASE_PATH . '/config');

/**
 * Definisce il percorso della cartella 'lib'.
 */
define('ITA_LIB_PATH', ITA_BASE_PATH . '/lib');

/**
 * Definisce il tipo di login, 'basic' o 'advanced'.
 */
define('ITA_LOGIN', 'basic');

/**
 * Definisce la form utilizzata per il login.
 */
define('ITA_LOGIN_FORM', 'accLogin');

/**
 * Definisce l'applicativo desktop da caricare una volta
 * effettuato il login.
 */
define('ITA_DESKTOP', 'envDesktopMobile');
