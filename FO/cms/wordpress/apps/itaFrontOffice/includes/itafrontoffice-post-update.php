<?php

/*
 * Script caricato esclusivamente dopo l'esecuzione di un aggiornamento
 * del plugin.
 */

if (!file_exists(WP_CONTENT_DIR . '/maintenance.php')) {
    file_put_contents(WP_CONTENT_DIR . '/maintenance.php', '<?php require_once "plugins/itaFrontOffice/includes/resources/maintenance.php";');
}

if (!file_exists(WP_CONTENT_DIR . '/languages/plugins/theme-my-login-it_IT.po')) {
    copy(__DIR__ . '/resources/theme-my-login-it_IT.po', WP_CONTENT_DIR . '/languages/plugins/theme-my-login-it_IT.po');
}

if (!file_exists(WP_CONTENT_DIR . '/languages/plugins/theme-my-login-it_IT.mo')) {
    copy(__DIR__ . '/resources/theme-my-login-it_IT.mo', WP_CONTENT_DIR . '/languages/plugins/theme-my-login-it_IT.mo');
}

if (!file_exists(WP_CONTENT_DIR . '/languages/plugins/theme-my-login-profiles-it_IT.po')) {
    copy(__DIR__ . '/resources/theme-my-login-it_IT.po', WP_CONTENT_DIR . '/languages/plugins/theme-my-login-profiles-it_IT.po');
}

if (!file_exists(WP_CONTENT_DIR . '/languages/plugins/theme-my-login-profiles-it_IT.mo')) {
    copy(__DIR__ . '/resources/theme-my-login-it_IT.mo', WP_CONTENT_DIR . '/languages/plugins/theme-my-login-profiles-it_IT.mo');
}

/*
 * Automatizza la replica delle regole di TML6 per il supporto alla nuova gestione interna.
 */

$blog_ids = get_sites(array('fields' => 'ids'));

foreach ($blog_ids as $blog_id) {
    if (!get_blog_option($blog_id, 'itafrontoffice_impostazioni_redirect_options', false) && get_blog_option($blog_id, 'theme_my_login_redirection', false)) {
        update_blog_option($blog_id, 'itafrontoffice_impostazioni_redirect_options', get_blog_option($blog_id, 'theme_my_login_redirection'));
    }
}

/*
 * Sovrascrive la cartella lib/java/itaJ4SignDSS/conf/ROOTS
 * con quella presente nel .sample.
 */

if (file_exists(ITA_LIB_PATH . '/java/itaJ4SignDSS/conf') && file_exists(ITA_LIB_PATH . '/java/itaJ4SignDSS/conf.sample/ROOTS')) {
    frontOfficeLib::copyDirectory(ITA_LIB_PATH . '/java/itaJ4SignDSS/conf.sample/ROOTS', ITA_LIB_PATH . '/java/itaJ4SignDSS/conf/ROOTS');
}