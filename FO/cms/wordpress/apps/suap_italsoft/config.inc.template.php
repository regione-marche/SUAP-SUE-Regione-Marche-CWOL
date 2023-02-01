<?php
/* Template configurazione per wordpress........ */

/* parametri definizione organizzazione/ente */
define('ITA_DB_SUFFIX','01');

/* parametri definizione repository file da scaricare */
define('ITA_MASTER_REPOSITORY', "file:///users/pc/procedimenti/enteMASTER/");
define('ITA_PROC_REPOSITORY', 'file:///users/pc/procedimenti/ente' . ITA_DB_SUFFIX . '/');
define('ITA_PRATICHE', 'file:///users/immagini/pratiche/pram' . ITA_DB_SUFFIX . '/');

/* parametri repository allegati alla pratica */
define('ITA_PRAT_LOG', '/srv/www/htdocs/cms_720/pratiche/ente' . ITA_DB_SUFFIX . '/log/');
define('ITA_PRAT_REPOSITORY', '/srv/www/htdocs/cms_720/pratiche/ente' . ITA_DB_SUFFIX . '/repository/');
define('ITA_PRAT_ATTACHMENT', '/srv/www/htdocs/cms_720/pratiche/ente' . ITA_DB_SUFFIX . '/attachments/');
define('ITA_PRAT_TEMPORARY', '/srv/www/htdocs/cms_720/pratiche/ente' . ITA_DB_SUFFIX . '/temp/');
define('ITA_URL_TEMPORARY', 'http://192.168.191.1/cms_720/pratiche/ente' . ITA_DB_SUFFIX . '/temp/');
define('ITA_JVM_PATH',   '/opt/jre1.6.0_29/bin/java');
define('ITA_CALLBACK_PATH',   ITA_SUAP_PATH . '/callback/itaCallback.class.php');
