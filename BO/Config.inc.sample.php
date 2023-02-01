<?php

/*
 * File di configurazione per itaEngine.
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
define('ITA_LOGIN', 'advanced');

/**
 * Definisce la form utilizzata per il login.
 */
define('ITA_LOGIN_FORM', 'accValidate');

/**
 * Definisce l'applicativo desktop da caricare una volta
 * effettuato il login.
 */
define('ITA_DESKTOP', 'envDesktop');

/**
 * Definisce il caricamento dello script itaRunner per gli applet Java,
 * accetta i valori 'active' o 'none'.
 */
define('ITA_IFRAME', 'active');

/**
 * Definisce il titolo della pagina iniziale di itaEngine 'Start.php'.
 * Di default il titolo è 'Italsoft'.
 */
define('ITA_PAGE_TITLE', 'Cityware.online');

/**
 * Definisce il tema da utilizzare in itaEngine.
 * Il valore di default è definito in 'lib/AppDefinitions/Conf.class.php'.
 */
define('ITA_THEME', 'pal-blue');

/**
 * Definisce il path del file binario di php per l'esecuzione da riga
 * di comando
 */
define('ITA_PHP_BINARY', 'php');