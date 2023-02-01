<?php

/*
  Plugin Name: SUAP Italsoft
  Plugin URI: http://wordpress.org/#
  Description: SUAP Italsoft - Il plugin per includere il modulo esterno.
  Author: Italsoft SRL
  Version: 3.3.0
  Author URI: http://www.italsoft-mc.it/
 */
if (!session_id()) {
    session_start();
}

define('ITA_SUAP_PATH', __DIR__ . '/includes');
define('ITA_PRATICHE_PATH', ITA_SUAP_PATH);
define('ITA_SUAP_PUBLIC', plugins_url('public', __FILE__));
define('ITA_PRATICHE_PUBLIC', ITA_SUAP_PUBLIC);

require_once(ITA_SUAP_PATH . '/SUAP_italsoft/suapApp.class.php');

function suap_plugins_loaded() {
    /*
     * Menu gestione config del plugin
     */
    if (class_exists('Itafrontoffice_Impostazioni')) {
        Itafrontoffice_Impostazioni::register_plugin('SUAP', 'suap_italsoft');
    }

    if (class_exists('Itafrontoffice_Ajax')) {
        Itafrontoffice_Ajax::register('suap_pravis_func');
        Itafrontoffice_Ajax::register('suap_pranews_func');
        Itafrontoffice_Ajax::register('suap_pramup_func');
        Itafrontoffice_Ajax::register('suap_prapubb_func');
    }
}

add_action('plugins_loaded', 'suap_plugins_loaded');

//
// Compilazione html head
//
function suap_insercssjs_func() {
    suapApp::addCSSFrontOffice();
}

add_action('wp_head', 'suap_insercssjs_func');

//
// Shortcode [suap_pramod]  che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pramod_func($attr, $external = false) {
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array(
        'tema' => 'cupertino',
        'sportello' => 1,
        'view' => 0,
        'tclass' => 1,
        'online' => 1,
        'info_page' => 0,
        'online_page' => 0,
        'ente' => 'XXX',
        'informativa' => 0,
        'open_accordion' => 0,
        'search_form' => 1,
        'proc_count' => 1
        ), $attr);

    //
    // Carico le configurazioni specifiche per ente
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // Aggiungo le script per lo shortcode specifico
    //
    // frontOfficeApp::$cmsHost->addJs('/SUAP_praMod/accordion.js?a=2', $blocco);
    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();

    require_once ITA_SUAP_PATH . '/SUAP_praMod/praMod.php';

    $praMod = new praMod();

    $config = array(
        'tema' => $a['tema'],
        'sportello' => $a['sportello'],
        'view' => $a['view'],
        'tclass' => $a['tclass'],
        'online' => $a['online'],
        'info_page' => $a['info_page'],
        'online_page' => $a['online_page'],
        'informativa' => $a['informativa'],
        'open_accordion' => $a['open_accordion'],
        'search_form' => $a['search_form'],
        'proc_count' => $a['proc_count']
    );

    $praMod->setConfig($config);

    try {
        return utf8_encode($praMod->parseEvent());
    } catch (Exception $e) {
        $suapErr = new suapErr();
        return $suapErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praMod');
    }
}

add_shortcode('suap_pramod', 'suap_pramod_func');

//
// Inserisce lo shortcode [suap_prainf] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_prainf_func($attr, $external = false) {
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array(
        'online_page' => 0,
        'view' => 0,
        'tema' => 'cupertino',
        'ente' => 'XXX',
        'elenco' => 1,
        'inquadramento' => '1',
        'normativa' => '1',
        'requisiti' => '1',
        'adempimenti' => '1',
        'termini' => '1',
        'oneri' => '1',
        'responsabile' => '1',
        'idtemplate' => "",
        'open_accordion' => 0
        ), $attr);

    //
    // Carico le configurazioni specifiche per ente
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // Aggiungo le script per lo shortcode specifico
    //
//    frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_italsoft/praInf.js');
    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praInf/praInf.php';

    $config = array(
        'view' => $a['view'],
        'tema' => $a['tema'],
        'online_page' => $a['online_page'],
        'Elenco' => $a['elenco'],
        'Inquadramento' => $a['inquadramento'],
        'Normativa' => $a['normativa'],
        'Requisiti' => $a['requisiti'],
        'Adempimenti' => $a['adempimenti'],
        'Termini' => $a['termini'],
        'Oneri' => $a['oneri'],
        'Responsabile' => $a['responsabile'],
        'idTemplate' => $a['idtemplate'],
        'open_accordion' => $a['open_accordion']
    );

    if ($config['idTemplate']) {
        require_once(ITA_SUAP_PATH . '/SUAP_praInf/' . $config['idTemplate'] . '.php');
        $praInf = new $config['idTemplate']();
    } else {
        $praInf = new praInf();
    }

    $praInf->setConfig($config);

    try {
        return utf8_encode($praInf->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praInf');
    }
}

add_shortcode('suap_prainf', 'suap_prainf_func');

//
// Inserisce lo shortcode [suap_pramup] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pramup_func($attr) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(
        array(
        'tema' => 'cupertino',
        'ente' => 'XXX',
        'procedi' => '',
        'setuser' => '',
        'return_page' => '',
        'procedimenti_page' => ''
        ), $attr
    );

//    if (function_exists('access_services_sso_background_login')) {
//        access_services_sso_background_login();
//    }

    /*
     *  Verifico se l'utente è autenticato
     */
    if (!is_user_logged_in()) {
        $userObj = false;
        if ($a['setuser']) {
            $userObj = wp_set_current_user($a['setuser']);
        }

        if (!$userObj) {
            /*
             * Imposto la variabile di sessione 'ita_percorso_redirect'
             * in maniera tale che una volta effettuato il login, mi apre
             * la pagina che avevo chiesto.
             */
            $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $script = $_SERVER['SCRIPT_NAME'];
            $params = $_SERVER['QUERY_STRING'];
            $_SESSION['ita_percorso_redirect'] = $protocol . '://' . $host . $script . '?' . $params;

            $html = 'Per proseguire, se ti sei gia registrato, è necessario effettuare il login facendo click su "Accedi", altrimenti registrati su "Registrazione e accreditamento".';
            output::addAlert($html, 'Attenzione', 'warning');
            return utf8_encode(output::$html_out);
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
                if (trim($deniedRole) == 'spid') {
                    $deniedMessage = 'Per proseguire con la compilazione del procedimento online dopo aver effettuato l\'accesso con SPID';
                    $deniedMessage .= ' &egrave; necessario contattare l\'Help Desk ai recapiti indicati nella relativa pagina.';
                    $deniedMessage .= '<br>Si prega di indicare il <b>codice fiscale</b> dell\'utente SPID con cui si &egrave; effetutato l\'accesso';
                    $deniedMessage .= ' ed una <b>casella PEC (posta elettronica certificata)</b> valida.';
                }

                output::addAlert($deniedMessage, 'Attenzione', 'warning');
                return output::$html_out;
            }
        }
    }

    //
    // Aggiungo le script per lo shortcode specifico
    //
//    frontOfficeApp::$cmsHost->addJs('/SUAP_praMup/itaupload.js?a=2', $blocco);

    /*
     * Fisso il valore dell'ente di ingresso;
     */
    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();
    require_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');
    require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
    require_once(ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
    require_once(ITA_LIB_PATH . '/dompdf-0.6/dompdf_config.inc.php');
    require_once(ITA_SUAP_PATH . '/SUAP_praMup/praMup.php');
//    require_once(ITA_LIB_PATH . '/itaPHPCore/itaZip.class.php');
    require_once(ITA_LIB_PATH . '/zip/itaZip.class.php');

    //
    // Vado a leggere i dati dell'utente sul database
    //
    $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();

    $autenticato = 1;
    if ($datiUtente['ESIBENTE_CODICEFISCALE_CFI'] == "") {
        $autenticato = 0;
    }

    $praMup = new praMup();
    $praMup->setConfig(array(
        'ditta' => ITA_DB_SUFFIX,
        'autenticato' => $autenticato, //1,
        'permessi' => 'rw',
        'pospay' => '',
        'procedi' => $a['procedi'],
        'procedimenti_page' => $a['procedimenti_page'],
        'return_page' => $a['return_page']
    ));

    try {
        $return = utf8_encode($praMup->parseEvent());
        return $return ?: utf8_encode(output::$html_out);
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praMup');
    }
}

add_shortcode('suap_pramup', 'suap_pramup_func');

function redir_login() {
    //if ($_SESSION['ita_percorso_redirect'] != '') {
//    wp_redirect($_SESSION['ita_percorso_redirect']);
    //    $_SESSION['ita_percorso_redirect'] = '';
    //} else {
    //        wp_redirect(get_option('siteurl'));
    //    }
}

add_action('auth_redirect', 'redir_login');

//
// Inserisce lo shortcode [suap_pravis] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pravis_func($attr, $external = false) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);

    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array(
        'tema' => 'cupertino',
        'view' => 0,
        'online_page' => 0,
        'ente' => 'XXX',
        "attachment_page" => 0,
        'search_form' => 1,
        'procedi' => '',
        'template' => 'praHtmlVis',
        'integrazione' => 1
        ), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();

    $currUsername = strtolower(frontOfficeApp::$cmsHost->getUserName());

    if (in_array($currUsername, array('admin', 'pitaprotec'))) {
        require_once ITA_SUAP_PATH . '/SUAP_praVis/praVis_admin.php';
        $praVis = new praVis_admin();
    } else if ($currUsername != '') {
        require_once ITA_SUAP_PATH . '/SUAP_praVis/praVis.php';
        $praVis = new praVis();
    } else {
        output::addAlert('Per la consultazione richieste &egrave; necessario effettuare il login.', 'Attenzione', 'warning');
        return output::$html_out;
    }

    require_once ITA_LIB_PATH . '/QXml/QXml.class.php';

    $config = array(
        'tema' => $a['tema'],
        'view' => $a['view'],
        'online_page' => $a['online_page'],
        'attachment_page' => $a['attachment_page'],
        'search_form' => $a['search_form'],
        'procedi' => $a['procedi'],
        'template' => $a['template'],
        'integrazione' => $a['integrazione']
    );

    $praVis->setConfig($config);

    try {
        return utf8_encode($praVis->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praVis');
    }
}

add_shortcode('suap_pravis', 'suap_pravis_func');

//
// Inserisce lo shortcode [suap_pradoc] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pradoc_func($attr, $external = false) {
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array('tema' => 'cupertino', 'view' => 0, 'ente' => 'XXX', 'online_page' => '', 'template' => 'praHtmlDoc',), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();

    if (strtolower(frontOfficeApp::$cmsHost->getUserName()) != '') {
        require_once ITA_SUAP_PATH . '/SUAP_praDoc/praDoc.php';
        $praDoc = new praDoc();
    } else {
        echo("<br><span style=\"font-size:1.3em;font-weight:bold;\">Attenzione!!! Per la consultazione dei documenti e' necessario effettuare il login.</span>");
        return;
    }

    require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');

    $config = array(
        'tema' => $a['tema'],
        'view' => $a['view'],
        'online_page' => $a['online_page'],
        'template' => $a['template'],
    );

    $praDoc->setConfig($config);

    try {
        return utf8_encode($praDoc->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praDoc');
    }
}

add_shortcode('suap_pradoc', 'suap_pradoc_func');

//
// Inserisce lo shortcode [suap_pradoc] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pradoccount_func($attr, $external = false) {
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array('tema' => 'cupertino', 'view' => 0, 'ente' => 'XXX', 'doc_page' => 0), $attr);

    if (!frontOfficeApp::$cmsHost->getUserName()) {
        return;
    }

    //
    // Inclusione del primo file di configurazione
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();
    if (strtolower(frontOfficeApp::$cmsHost->getUserName()) != '') {
        require_once ITA_SUAP_PATH . '/SUAP_praDocCount/praDocCount.php';
        $praDoc = new praDocCount();

        global $current_user;
        get_currentuserinfo();
        $codFisc = get_cimyFieldValue(wp_get_current_user()->ID, 'FISCALE');

        $config = array(
            'tema' => $a['tema'],
            'view' => $a['view'],
            'doc_page' => $a['doc_page'],
        );

        $praDoc->setConfig($config);

        try {
            return utf8_encode($praDoc->parseEvent());
        } catch (Exception $e) {
            $praErr = new suapErr();
            return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praDocCount');
        }
    }
}

add_shortcode('suap_pradoccount', 'suap_pradoccount_func');

//
// Inserisce lo shortcode [suap_pravis] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pravisse_func($attr) {
    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array('tema' => 'cupertino', 'view' => 0, 'online_page' => 0, 'ente' => 'XXX'), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    require_once("config.inc." . $a['ente'] . ".php");

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();

    if (strtolower(frontOfficeApp::$cmsHost->getUserName()) != '') {
        require_once ITA_SUAP_PATH . '/SUAP_praVisSe/praVisSe.php';
        $praVis = new praVisSe();
    } else {
        echo("<br><span style=\"font-size:1.3em;font-weight:bold;\">Attenzione!!! Per la consultazione richieste e' necessario effettuare il login.</span>");
        return;
    }

    require_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
    global $current_user;
    get_currentuserinfo();
    $codFisc = get_cimyFieldValue(wp_get_current_user()->ID, 'FISCALE');

    $config = array(
        'tema' => $a['tema'],
        'view' => $a['view'],
        'online_page' => $a['online_page'],
    );

    $praVis->setConfig($config);

    try {
        return utf8_encode($praVis->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praVis');
    }
}

add_shortcode('suap_pravisse', 'suap_pravisse_func');

/* * ************************************************************   SUAP ARTICOLI    **************************************************** */
//
// Array che viene utilizzato in modo globale per passare gli articoli
//
$arr_ext_art = array();

//
// Inserisce lo shortcode [suap_articoli] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_articoli_func($attr, $external = false) {
    global $arr_ext_art;
    // Vado a leggere i dati dal database con una chiamata esterna che mi restituisce l'array
    //    
    // Imposto i valori di default
    //    
    //$a = shortcode_atts(array('tema' => 'cupertino', 'ente' => 'XXX'), $attr);
    $a = shortcode_atts(array(
        'ente' => 'XXX',
        'details' => 'YYY',
        "categoria" => ""
        ), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praNews/praNews.php';

    $praNews = new praNews();

    $config = array(
        'categoria' => $a["categoria"]
    );

    $praNews->setConfig($config);

    $arr_ext_art = $praNews->getNewsFromBO();

    global $post;

    $custom_wp_posts = array();
    foreach ($arr_ext_art as $praNewsPost) {
        $post = new stdClass();
        $post->ID = $praNewsPost['ID'];
        $post->post_author = 1;
        $post->post_date = substr($praNewsPost['DATA'], 0, 4) . '-' . substr($praNewsPost['DATA'], 4, 2) . '-' . substr($praNewsPost['DATA'], 6, 2) . ' 08:00:00';
        $post->post_date_gmt = substr($praNewsPost['DATA'], 0, 4) . '-' . substr($praNewsPost['DATA'], 4, 2) . '-' . substr($praNewsPost['DATA'], 6, 2) . ' 08:00:00';
        $post->post_title = $praNewsPost['TITOLO'];
        $post->post_content = $praNewsPost['CONTENUTO'];
        $post->post_password = $praNewsPost['PASSWORD'];
        $post->italsoft_uri = get_permalink($a['details']) . "&event=dettaglioArticolo&ID=" . $praNewsPost['ID'];
        $post->italsoft_classificazione = $praNewsPost['CLASSIFICAZIONE'];
        $post->italsoft_categoria = $praNewsPost['CATEGORIA'];
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_name = 'fake-post-' . $praNewsPost['ID'];
        $post->post_type = 'post';
        $post->filter = 'raw';

        $custom_wp_posts[] = new WP_Post($post);
    }

    $wp_posts = array();

    if ($a["categoria"] == "") {
        $myposts = get_posts(array(
            'numberposts' => 10
        ));

        foreach ($myposts as $post) {
            /*
             * Ciclo i post di WP inserendoli in $wp_posts, nel mezzo inserisco
             * i post custom con data più recente rispetto al post corrente.
             */
            foreach ($custom_wp_posts as $k => $custom_wp_post) {
                if (strtotime($custom_wp_post->post_date) >= strtotime($post->post_date)) {
                    $wp_posts[] = $custom_wp_post;
                    unset($custom_wp_posts[$k]);
                }
            }

            $wp_posts[] = $post;
        }
    }

    /*
     * Unisco i rimanenti post
     */
    $wp_posts = array_merge($wp_posts, $custom_wp_posts);

    $output = '';

    foreach ($wp_posts as $post) {
        setup_postdata($post);

        ob_start();
        get_template_part('template-parts/content', 'card');
        $output .= ob_get_contents();
        ob_end_clean();
    }

    return $output;
}

add_shortcode('suap_articoli', 'suap_articoli_func');

// Inserisce lo shortcode [suap_articoli] che e' il "tag" da inserire nella pagina/articolo di wordpress
function suap_pranews_func($attr, $external = false) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attr);

    //    
    // Imposto i valori di default
    //    
    $a = shortcode_atts(array('ente' => 'XXX', "parere_page" => 0, "procedi" => ""), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    if (!$external) {
        require_once("config.inc." . $a['ente'] . ".php");
    }

    frontOfficeApp::setEnte($a['ente']);

    //
    // LANCIO CLASSI ITALSOFT
    //
    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praNews/praNews.php';
    $praNews = new praNews();
    $config = array(
        "parere_page" => $a["parere_page"],
        "procedi" => $a["procedi"],
    );

    $praNews->setConfig($config);

    $articoloID = frontOfficeApp::$cmsHost->getRequest('ID');
    $requestEvent = frontOfficeApp::$cmsHost->getRequest('event');
    $password = $praNews->getNewsPasswd($articoloID);   // Acquisire questo valore in qualche modo  ///  |||||||||||||||||||||||||||||||||
    $passbox = $_POST['post_password'];

    if (isset($_SESSION['PRANEWS' . $articoloID]) && $_SESSION['PRANEWS' . $articoloID] === md5($password)) {
        try {
            return utf8_encode($praNews->parseEvent());
        } catch (Exception $e) {
            $praErr = new suapErr();
            return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praNews');
        }
    }

    if ($password && !$passbox && $requestEvent != "gestioneAllegato") {
        echo preg_replace('/action="[^"]+"/', '', get_the_password_form());
    } elseif ($password && $passbox || $requestEvent == "gestioneAllegato") {
        if ($password == $passbox || $requestEvent == "gestioneAllegato") {
            eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Accesso all\'articolo protetto ID ' . $articoloID, 'Key' => ''));

            try {
                $_SESSION['PRANEWS' . $articoloID] = md5($password);
                return utf8_encode($praNews->parseEvent());
            } catch (Exception $e) {
                $praErr = new suapErr();
                return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praNews');
            }
        } else {
            $html = new html;
            echo $html->getAlert('La password inserita non &egrave; corretta.', '', 'error') . preg_replace('/action="[^"]+"/', '', get_the_password_form());
        }
    } elseif (!$password || $requestEvent == "gestioneAllegato") {
        try {
            return utf8_encode($praNews->parseEvent());
        } catch (Exception $e) {
            $praErr = new suapErr();
            return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praNews');
        }
    }
}

add_shortcode('suap_pranews', 'suap_pranews_func');

/* * ************************************************************   SUAP GRAFICI    **************************************************** */


$inclusione_script_graf = true;

//
// Inserisce lo shortcode [suap_pragraph] che e' il "tag" da inserire nella pagina/articolo di wordpress
//
function suap_pragraph_func($attr, $external = false) {
    global $inclusione_script_graf;

    //
    // Imposto i valori di default
    //
    $a = shortcode_atts(array(
        'idgrafico' => '',
        'idtabella' => '',
        'tipo_graph' => 'proc_tot',
        'sportello' => '',
        'lista_enti' => '01',
        'anno' => date('Y'),
        'titolo' => '',
        'sottotitolo' => ''
        ), $attr);

    //
    // Inclusione del primo file di configurazione
    //
    require_once("config.inc.template.php");

    if ($a['idgrafico'] == '' || $a['idtabella'] == '' || $a['idgrafico'] == $a['idtabella']) {
        return utf8_encode('Impostare bene tutti i parametri necessari, in particolare "idgrafico" e "idtabella".');
    }

    if ($inclusione_script_graf) {
        /*
         * Aggiungo le script per lo shortcode specifico
         */
        if (file_exists(ITA_BASE_PATH . '/public/vendor/highcharts/2.2.5/highcharts.js')) {
            frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/highcharts/2.2.5/highcharts.js');
        } else {
            frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_italsoft/js/jqHighCharts.2.2.5/highcharts.js', $blocco);
        }
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/grafico.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcTot.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcTot.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcMese.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcSett.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcSport.js', $blocco);
        frontOfficeApp::$cmsHost->addJs(ITA_SUAP_PUBLIC . '/SUAP_praGraf/graProcSegn.js', $blocco);
        $inclusione_script_graf = false;
    }

    /* LANCIO CLASSI ITALSOFT */
    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praGraf/praGraf.php';
    $praGraf = new praGraf();
    $config = array(
        'idgrafico' => $a['idgrafico'],
        'idtabella' => $a['idtabella'],
        'tipo_graph' => $a['tipo_graph'],
        'sportello' => $a['sportello'],
        'lista_enti' => $a['lista_enti'],
        'anno' => $a['anno'],
        'titolo' => $a['titolo'],
        'sottotitolo' => $a['sottotitolo']
    );
    $praGraf->setConfig($config);

    try {
        return utf8_encode($praGraf->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praGraph');
    }
}

add_shortcode('suap_pragraph', 'suap_pragraph_func');

/*
 * Shortcode per open data.
 */

function suap_opendata_func($attrs) {
    $params = shortcode_atts(
        array(
        'ente' => '',
        'scia' => 0,
        'ordinario' => 0
        ), $attrs
    );

    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praOpenData/praOpenData.php';
    $praOpenData = new praOpenData();
    $praOpenData->setConfig($params);

    try {
        return utf8_encode($praOpenData->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praOpenData');
    }
}

add_shortcode('suap_opendata', 'suap_opendata_func');

/*
 * Shortcode per MPAY
 */

function suap_mpay_func($attrs) {
    $params = shortcode_atts(
        array(
        'ente' => ''
        ), $attrs
    );

    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praMPAY/praMPAY.php';
    $praMPAY = new praMPAY();
    $praMPAY->setConfig($params);

    try {
        return utf8_encode($praMPAY->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praMPAY');
    }
}

add_shortcode('suap_mpay', 'suap_mpay_func');

/*
 * Shortcode per pubblicazione richieste online
 */

function suap_prapubb_func($attrs, $external = false) {
    Itafrontoffice_Ajax::active(__FUNCTION__, $attrs);

    $params = shortcode_atts(
        array(
        'ente' => '',
        'sportello' => '',
        'serie' => ''
        ), $attrs
    );

    if (!$external) {
        require_once "config.inc.{$params['ente']}.php";
    }

    frontOfficeApp::setEnte($params['ente']);


    suapApp::load();
    require_once ITA_SUAP_PATH . '/SUAP_praPubb/praPubb.php';
    $praPubb = new praPubb();
    $praPubb->setConfig($params);

    try {
        return utf8_encode($praPubb->parseEvent());
    } catch (Exception $e) {
        $praErr = new suapErr();
        return $praErr->parseError(__FILE__, 'E9999', $e->getMessage() . "\n" . $e->getTraceAsString(), 'praPubb');
    }
}

add_shortcode('suap_prapubb', 'suap_prapubb_func');
