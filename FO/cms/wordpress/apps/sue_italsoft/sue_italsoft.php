<?php

/*
  Plugin Name: SUE Italsoft
  Plugin URI: http://wordpress.org/#
  Description: sue Italsoft - Il plugin per includere il modulo esterno.
  Author: Michele Moscioni - Italsoft
  Version: 3.3.0
  Author URI: http://www.italsoft-mc.it/
 */
if (!session_id()) {
    session_start();
}

define('ITA_SUE_PATH', __DIR__ . '/includes');
define('ITA_SUE_PUBLIC', plugins_url('public', __FILE__));

require_once(ITA_SUE_PATH . '/SUE_italsoft/sueApp.class.php');

function sue_plugins_loaded() {
    /*
     * Menu gestione config del plugin
     */
    if (class_exists('Itafrontoffice_Impostazioni')) {
        Itafrontoffice_Impostazioni::register_plugin('SUE', 'sue_italsoft');
    }

    if (class_exists('Itafrontoffice_Ajax')) {
        Itafrontoffice_Ajax::register('sue_vis_func');
        Itafrontoffice_Ajax::register('sue_mup_func');
        Itafrontoffice_Ajax::register('sue_pubb_func');
    }
}

add_action('plugins_loaded', 'sue_plugins_loaded');

//
// Compilazione html head
//
function sue_insercssjs_func() {
    sueApp::addCSSFrontOffice();
}

add_action('wp_head', 'sue_insercssjs_func');

/**
 * Shortcode [sue_mod]  che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_mod_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_pramod_func($attr, true);
}

add_shortcode('sue_mod', 'sue_mod_func');

/**
 * Inserisce lo shortcode [sue_inf] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_inf_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_prainf_func($attr, true);
}

add_shortcode('sue_inf', 'sue_inf_func');

/**
 * Inserisce lo shortcode [sue_mup] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_mup_func($attr) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);

    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array('tema' => 'cupertino', 'ente' => 'XXX', 'procedi' => '', 'setuser' => '', 'procedimenti_page' => ''), $attr);

    // Verifico se l'utente e' autenticato
    if (!is_user_logged_in()) {
        $userObj = false;
        if ($a['setuser']) {
            $userObj = wp_set_current_user($a['setuser']);
        }
        if (!$userObj) {
            // Imposto la variabile di sessione 'ita_percorso_redirect' in maniera tale che una volta effettuato
            //   il login, mi apre la pagina che avevo chiesto
            $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $script = $_SERVER['SCRIPT_NAME'];
            $params = $_SERVER['QUERY_STRING'];
            $_SESSION['ita_percorso_redirect'] = $protocol . '://' . $host . $script . '?' . $params;

            $html = 'Per proseguire, se ti sei gia registrato, è necessario effettuare il login facendo click su "Accedi", altrimenti' .
                ' registrati su "Registrazione e accreditamento".';
            return utf8_encode($html);
        }
    }

    //
    // Inclusione del primo file di configurazione
    //
    require_once("config.inc." . $a['ente'] . ".php");
    
    if (is_user_logged_in() && defined('ITA_PRATICHE_DENIED_ROLES')) {
        $deniedRoles = explode(',', ITA_PRATICHE_DENIED_ROLES);
        $deniedMessage = 'Impossibile proseguire con la compilazione online.';
        if (defined('ITA_PRATICHE_DENIED_ROLES_MESSAGE') && ITA_PRATICHE_DENIED_ROLES_MESSAGE) {
            $deniedMessage = ITA_PRATICHE_DENIED_ROLES_MESSAGE;
        }

        foreach ($deniedRoles as $deniedRole) {
            if (strtolower(frontOfficeApp::$cmsHost->getRuoloUtente()) === trim($deniedRole)) {
                output::addAlert($deniedMessage, 'Attenzione', 'warning');
                return output::$html_out;
            }
        }
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    sueApp::load();
    require_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');
    require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
    require_once(ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
    require_once(ITA_LIB_PATH . '/dompdf-0.6/dompdf_config.inc.php');
    require_once(ITA_SUE_PATH . '/SUE_mup/sueMup.php');
    require_once(ITA_LIB_PATH . '/itaPHPCore/itaZip.class.php');

    //
    // Vado a leggere i dati dell'utente sul database
    //
    $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
    $autenticato = 1;
     if ($datiUtente['ESIBENTE_CODICEFISCALE_CFI'] == "") {
        $autenticato = 0;
    }

    //$autenticato = 0;
    $sueMup = new sueMup();
    $sueMup->setConfig(array(
        'ditta' => ITA_DB_SUFFIX,
        'autenticato' => $autenticato, //1,
        'permessi' => 'rw',
        'pospay' => '',
        'procedi' => $a['procedi'],
        'procedimenti_page' => $a['procedimenti_page']
    ));

    try {
        $result = utf8_encode($sueMup->parseEvent());
        return $result ?: utf8_encode(output::$html_out);
    } catch (Exception $e) {
        $sueErr = new sueErr();
        return $sueErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'sueMup');
    }
}

add_shortcode('sue_mup', 'sue_mup_func');

/**
 * Inserisce lo shortcod [sue_vis] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_vis_func($attr) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_pravis_func($attr, true);
}

add_shortcode('sue_vis', 'sue_vis_func');

/**
 * Inserisce lo shortcode [sue_doc] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_doc_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_pradoc_func($attr, true);
}

add_shortcode('sue_doc', 'sue_doc_func');

/**
 * Inserisce lo shortcode [sue_docCount] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_doccount_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_pradoccount_func($attr, true);
}

add_shortcode('sue_doccount', 'sue_doccount_func');

/**
 * Inserisce lo shortcode [suap_articoli] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_articoli_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_articoli_func($attr, true);
}

add_shortcode('sue_articoli', 'sue_articoli_func');

/**
 * Inserisce lo shortcode [suap_articoli] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_news_func($attr) {
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_pranews_func($attr, true);
}

add_shortcode('sue_news', 'sue_news_func');

/**
 * Inserisce lo shortcode [sue_graph] che e' il "tag" da inserire nella pagina/articolo di wordpress
 */
function sue_graph_func($attr) {
    return suap_pragraph_func($attr, true);
}

add_shortcode('sue_graph', 'sue_graph_func');

function sue_pubb_func($attr) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);
    $a = shortcode_atts(array('ente' => 'XXX'), $attr);
    require_once("config.inc." . $a['ente'] . ".php");

    return suap_prapubb_func($attr, true);
}

add_shortcode('sue_pubb', 'sue_pubb_func');
