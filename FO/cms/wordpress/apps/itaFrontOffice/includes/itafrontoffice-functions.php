<?php

/**
 * Classe di raccolta per funzioni generiche di Wordpress.
 */
class Itafrontoffice_Functions {

    private $noindex = array(
        'suap_pramod',
        'suap_pravis',
        'suap_pravisse',
        'suap_pradoc',
        'suap_pradoccount',
        'sue_mod',
        'sue_vis',
        'sue_visse',
        'sue_doc',
        'sue_doccount'
    );

    public function __construct() {
        /**
         * Disabilita la barra di amministrazione di Wordpress.
         */
        add_action('after_setup_theme', array($this, 'remove_admin_bar'));

        /**
         * Ex-plugin 'Insert'.
         * Permettere di inglobare un post tramite shortcode.
         */
        add_shortcode('inserisci_post', array($this, 'shortcode_inserisci_post'));

        /**
         * Modifica la dicitura delle stringhe.
         */
        add_action('before_signup_header', array($this, 'signup_header_custom_gettext'));
        add_action('tml_render_form', array($this, 'signup_header_custom_gettext'));

        /**
         * Abilita gli shortcode all'interno dei widget.
         */
        add_filter('widget_text', 'do_shortcode');

        /**
         * Redirige gli utenti dalla dashboard al profilo.
         */
        add_action('admin_init', array($this, 'redirect_non_admins'));

        /*
         * Hook per funzioni post-update.
         */
        add_action('upgrader_process_complete', array($this, 'post_update'), 10, 2);

        /*
         * Shortcode Recapiti
         */
        add_shortcode('itafrontoffice_recapiti', array($this, 'shortcode_recapiti'));

        /*
         * Shortcode Entrypoint
         */
        add_shortcode('itafrontoffice_entrypoint', array($this, 'shortcode_entrypoint'));

        /*
         * Funzioni per sovrascrivere il titolo del sito ed il motto con
         * quelli impostati in Impostazioni FO.
         */
        add_filter('option_blogname', array($this, 'overwrite_blogname'), 10, 2);
        add_filter('option_blogdescription', array($this, 'overwrite_blogdescription'), 10, 2);

        add_action('profile_update', array($this, 'on_profile_update'), 10, 2);

        add_action('wp_head', array($this, 'meta_robots'));

        add_action('before_signup_header', array($this, 'filter_registration_roles'));

        add_filter('tml_get_form_field_label', array($this, 'tml_form_labels'));

        /*
         * Reindirizzamenti da TML6
         */

        add_filter('login_redirect', array($this, 'login_redirect'), 10, 3);
        add_filter('logout_redirect', array($this, 'logout_redirect'), 10, 3);
    }

    public function remove_admin_bar() {
        if (!current_user_can('edit_posts')) {
            show_admin_bar(false);
        }
    }

    public function on_profile_update() {
        $eqDesc = sprintf('Aggiornamento profilo utente');
        eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));
    }

    public function shortcode_inserisci_post($atts) {
        extract(shortcode_atts(array('blog_id' => '1', 'post_id' => '1'), $atts));
        $blog_post = get_blog_post($blog_id, $post_id);

        /*
         * Applico i filtri come per la funzione WP the_content.
         * https://developer.wordpress.org/reference/functions/the_content/
         */
        $content = apply_filters('the_content', $blog_post->post_content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    public function signup_header_custom_gettext() {
        add_filter('gettext', array($this, 'custom_gettext_strings'), 20, 3);
    }

    public function filter_registration_roles() {
        global $wp_roles;

        $all_roles = $wp_roles->roles;
        $request_role = $_GET['ruolo'] ?: $_POST['ruolo_temp'];
        if (!$request_role) {
            return;
        }

        foreach ($all_roles as $slug => $info) {
            if ($slug == $request_role && isset($info['capabilities']['edit_posts']) && $info['capabilities']['edit_posts']) {
                wp_redirect(network_home_url());
                die;
            }
        }

        $ruoli_bloccati = get_site_option('itafrontoffice_impostazioni_blocco_signup', array());

        if (in_array($request_role, $ruoli_bloccati)) {
            wp_redirect(network_home_url());
            die;
        }
    }

    public function tml_form_labels($label) {
        return $this->custom_gettext_strings($label, $label, '');
    }

    public function custom_gettext_strings($translated_text, $text, $domain) {
        $pec_address = in_array($_GET['ruolo'] ?: $_REQUEST['ruolo_temp'], array('user', 'pl')) ? false : true;

        $check_pec_address = get_site_option('itafrontoffice_impostazioni_generali_options', array());
        if (isset($check_pec_address['remove_pec']) && $check_pec_address['remove_pec'] == 1) {
            $pec_address = false;
        }

        switch ($text) {
            case 'Email&nbsp;Address:':
                return $pec_address ? 'Indirizzo PEC (Posta Elettronica Certificata):' : $translated_text;

            case 'We send your registration email to this address. (Double-check your email address before continuing.)':
                return $pec_address ? 'Invieremo l\'email di registrazione a questo indirizzo. (Controllare attentamente che l\'indirizzo email sia una PEC prima di proseguire.)' : $translated_text;

            case 'Please enter a valid email address.':
                return $pec_address ? 'Inserisci un indirizzo PEC valido.' : $translated_text;

            case 'Get your own %s account in seconds':
                return 'Registrazione nuovo account';

            case '(Must be at least 4 characters, letters and numbers only.)':
                return '(Deve essere di almeno 4 caratteri, solo lettere minuscole e numeri, senza spazi.)';

            case '(required)':
                return '(richiesto)';

            case 'Email':
                return $pec_address ? 'Indirizzo PEC' : $translated_text;

            case 'Nome utente o indirizzo email':
                return $pec_address ? 'Nome utente o indirizzo PEC' : $translated_text;
        }

        return $translated_text;
    }

    public function redirect_non_admins() {
        if (!defined('DOING_AJAX') && !current_user_can('edit_posts')) {
            wp_redirect(get_edit_profile_url());
        }
    }

    public function post_update($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin == 'itaFrontOffice/itaFrontOffice.php') {
                    require_once __DIR__ . '/itafrontoffice-post-update.php';
                }
            }
        }
    }

    public function shortcode_recapiti() {
        $text = '';

        if (($value = do_shortcode('[itafrontoffice_ente_nome]'))) {
            $text .= "<p><b>$value</b></p>";
        }

        $text .= '<p>';

        if (
            ($indirizzo = do_shortcode('[itafrontoffice_ente_indirizzo]')) &&
            ($cap = do_shortcode('[itafrontoffice_ente_cap]')) &&
            ($comune = do_shortcode('[itafrontoffice_ente_comune]'))
        ) {
            $text .= "$indirizzo<br />$cap $comune<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_telefono]'))) {
            $text .= "Tel: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_fax]'))) {
            $text .= "Fax: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_email]'))) {
            $text .= "Email: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_pec]'))) {
            $text .= "PEC: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_orario]'))) {
            $text .= "Orario: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_responsabile]'))) {
            $text .= "Responsabile: $value<br />";
        }

        if (($value = do_shortcode('[itafrontoffice_ente_addetto]'))) {
            $text .= "Addetto: $value";
        }

        $text .= '</p>';

        return $text;
    }

    public function shortcode_entrypoint($attrs) {
        extract(shortcode_atts(array('icon' => false, 'href' => '#', 'title' => false, 'description' => false,), $attrs));

        if (!$icon && !$title) {
            return false;
        }

        $html .= '<div style="text-align: center; width: 90%; margin: 0 auto;">';
        $html .= sprintf('<i class="icon %s" style="font-size: 4em;"></i>', $icon);

        if ($description) {
            $html .= "<p>$description</p>";
        }

        $html .= sprintf('<p><a class="italsoft-button italsoft-button--outline" href="%s">%s</a></p>', $href, $title);
        $html .= '</div>';

        return $html;
    }

    public function overwrite_blogname($blogname) {
        $itafrontoffice_ente_options = get_option('itafrontoffice_impostazioni_ente_options');
        return trim($itafrontoffice_ente_options['nome']) ?: $blogname;
    }

    public function overwrite_blogdescription($blogdescription) {
        $itafrontoffice_ente_options = get_option('itafrontoffice_impostazioni_ente_options');
        return trim($itafrontoffice_ente_options['nome_sub']) ?: $blogdescription;
    }

    public function meta_robots() {
        if (is_page()) {
            global $post;
            if (preg_match('/(' . implode('|', $this->noindex) . ')/', $post->post_content)) {
                echo '<meta name="robots" content="noindex,nofollow" />';
            }
        }
    }

    /*
     * DA TML6
     */

    public function login_redirect($redirect_to, $request, $user) {
        if (defined('Theme_My_Login::VERSION') && version_compare(Theme_My_Login::VERSION, '7.0.0', '<')) {
            $args = array(
                'post_type' => 'page',
                'meta_query' => array(
                    array(
                        'key' => '_tml_action',
                        'value' => 'profile',
                        'compare' => '=',
                    ),
                ),
            );

            $query = new WP_Query($args);

            if ($query->post) {
                return get_permalink($query->post);
            } else {
                return get_edit_profile_url();
            }
        }

        // Return the redirect URL for the user
        return $this->get_redirect_for_user($user, 'login', $redirect_to);
    }

    public function logout_redirect($redirect_to, $request, $user) {
        if (defined('Theme_My_Login::VERSION') && version_compare(Theme_My_Login::VERSION, '7.0.0', '<')) {
            return $redirect_to;
        }

        // Get the redirect URL for the user
        $redirect_to = $this->get_redirect_for_user($user, 'logout', $redirect_to);

        // Make sure we're not trying to redirect to an admin URL
        if (false !== strpos($redirect_to, 'wp-admin'))
            $redirect_to = add_query_arg('loggedout', 'true', wp_login_url());

        // Return the redirect URL for the user
        return $redirect_to;
    }

    public function get_redirect_for_user($user, $type = 'login', $default = '') {
        // Make sure we have a default
        if (empty($default))
            $default = admin_url('profile.php');

        // Bail if $user is not a WP_User
        if (!$user instanceof WP_User)
            return $default;

        // Make sure $type is valid
        if (!( 'login' == $type || 'logout' == $type ))
            $type = 'login';

        // Make sure the user has a role
        if (is_multisite() && empty($user->roles)) {
            $user->roles = array('subscriber');
        }

        // Get the user's role
        $user_role = reset($user->roles);

        // Get the redirection settings for the user's role
        $settings = get_option('itafrontoffice_impostazioni_redirect_options');
        $redirection = $settings[$user_role];

        $i = 0;
        while ($redirection["{$type}_type"] === 'copy' && $i++ < 5) {
            $redirection = $settings[$redirection["{$type}_copy"]];
        }

        // Determine which redirection type is being used
        switch ($redirection["{$type}_type"]) {
            case 'referer' :
                // Get the referer
                if (!$referer = wp_get_original_referer())
                    $referer = wp_get_referer();

                // Send 'em back to the referer
                $redirect_to = $referer;
                break;

            case 'custom' :
                // Send 'em to the specified URL
                $redirect_to = $redirection["{$type}_url"];

                // Allow a few user specific variables
                $redirect_to = str_replace(
                    array(
                    '%user_id%',
                    '%user_nicename%'
                    ), array(
                    $user->ID,
                    $user->user_nicename
                    ), $redirect_to
                );
                break;
        }

        // Make sure $redirect_to isn't empty
        if (empty($redirect_to))
            $redirect_to = $default;

        return $redirect_to;
    }

}

new Itafrontoffice_Functions();
