<?php

/*
  Per aggiungere una pagina di impostazioni del plugin, all'interno di esso inserire
  le seguenti righe (es. per "albo_italsoft"):

  add_action( 'admin_menu', 'albo_italsoft_menu' );
  function albo_italsoft_menu() { Itafrontoffice_Impostazioni::config( 'Albo', 'albo_italsoft' ); }
 */

class Itafrontoffice_Impostazioni {

    private $slug = 'itafrontoffice_impostazioni';
    private $shortcode_slug = 'itafrontoffice';
    private $menu_icon = 'dashicons-admin-multisite';
    private $menu_network_icon = 'dashicons-admin-multisite';
    private $italsoft_roles = array(
        'user' => 'Utente',
        'residenti' => 'Residente',
        'professionista' => 'Professionista',
        'forze_dellordine' => 'Forze dell\'Ordine',
        'ente_esterno' => 'Ente esterno',
        'ced' => 'CED',
        'spid' => 'SPID',
        'cohesion' => 'Cohesion',
        'federa' => 'Federa',
        'pl' => 'Polizia Locale',
        'esercente' => 'Esercente Strutture Ricettive'
    );

    /**
     * Lista delle opzioni disponibile nella pagina Ente.
     */
    private $ente_options = array(
        'nome' => array('label' => 'Nome ente'),
        'nome_sub' => array('label' => 'Nome ente (aggiuntivo)'),
        'codice_fiscale' => array('label' => 'Codice fiscale'),
        'indirizzo' => array('label' => 'Indirizzo sede'),
        'comune' => array('label' => 'Comune'),
        'cap' => array('label' => 'CAP'),
        'telefono' => array('label' => 'Telefono'),
        'fax' => array('label' => 'Fax'),
        'email' => array('label' => 'Email'),
        'pec' => array('label' => 'PEC'),
        'orario' => array('label' => 'Orario'),
        'responsabile' => array('label' => 'Responsabile'),
        'addetto' => array('label' => 'Addetto')
    );

    /**
     * Plugin registrati esternamenti.
     */
    static private $plugins = array();

    public function __construct() {
        $this->menu_icon = frontOfficeLib::getIcon('institution');
        $this->menu_network_icon = frontOfficeLib::getIcon('dashboard');

        /*
         * Hooks di inizializzazione
         */
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('network_admin_menu', array($this, 'add_menu_page_network'));

        add_action('wp_ajax_' . $this->slug, array($this, 'ajax_handler'));
        add_action('admin_head', array($this, 'custom_head'));
        add_action('admin_enqueue_scripts', array($this, 'custom_scripts'));

        add_action('wp_before_admin_bar_render', array($this, 'admin_bar_menu'));

        /*
         * Allineamento dell'utente al login.
         */

        add_action('wp_login', array($this, 'login_sync_user'));

        $this->register_shortcodes();
    }

    public function login_sync_user($username) {
        $blogs = $this->_wp_get_sites();

        foreach ($blogs as $blog) {
            $this->_allinea_utente_per_sito($username, $blog['blog_id']);
        }
    }

    public function custom_head() {
        echo <<<STYLE
<style>
    @keyframes flash-yellow { 1% { background-color: yellow; } }
    .itafrontoffice-ajax-response { display: inline-block; margin: 0 8px !important; vertical-align: text-top; }
    .itafrontoffice-ajax-response > img { vertical-align: sub; }
    .itafrontoffice-ajax-response > span { animation: flash-yellow 2s ease-out; animation-iteration-count: 1; }
    #adminmenu .toplevel_page_itafrontoffice_impostazioni .wp-menu-image > img { width: 16px; height: 16px; padding-top: 10px; }
    .page-ruolocampi .widefat td, .page-ruolocampi .widefat th { border-left: 1px solid #e4e4e4; }
    .itafrontoffice-logs-pre { margin: -2px -15px; font-family: monospace; font-size: 1.1em; }
    .itafrontoffice-logs-pre > span { display: block; padding: 8px 10px; }
    .itafrontoffice-logs-pre > span:nth-child(even) { background-color: #eee; }
</style>
STYLE;
    }

    public function custom_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function ajax_handler() {
        $request = $_POST;

        if (
            $request['page'] &&
            method_exists($this, $request['page'] . '_options_handler')
        ) {
            call_user_func(array($this, $request['page'] . '_options_handler'), $request);
        }

        echo 'Chiamata non gestita!';
        die;
    }

    public function ajax_button($text, $action, $page, $params = array()) {
        $data = array();
        $data['action'] = $this->slug;
        $data['ajax'] = $action;
        $data['page'] = $page;

        $btn_id = 'button-' . $action;

        $response_p = '<p class="itafrontoffice-ajax-response"></p>';
        $response_j = "$('#$btn_id').next('p')";
        if (isset($params['response-id'])) {
            $response_p = '';
            $response_j = "$('#{$params['response-id']}')";
        }

        $formdata = '';
        if (isset($params['form'])) {
            foreach ($params['form'] as $id) {
                $formdata .= "data['$id'] = document.getElementById('$id').type === 'checkbox' ? (document.getElementById('$id').checked ? 1 : 0) : document.getElementById('$id').value;\n";
            }
        }

        $json = json_encode($data);

        $loader = admin_url('images/loading.gif');

        echo <<<SCRIPT
<button id="$btn_id" class="button" type="button" style="vertical-align: initial;">$text</button>$response_p
<script type="text/javascript">
	jQuery(document).ready(function($) {
        $('#$btn_id').on('click', function() {
            var btn = this,
                data = $json;

            $formdata
            btn.disabled = true;
            $response_j.html('<img src="$loader" />');

            $.post(ajaxurl, data, function(res) {
                btn.disabled = false;
                $response_j.html('<span>' + res + '</span>');
            });
        });
	});
</script>
SCRIPT;
    }

    public function add_menu_page() {
        /*
         * Aggiungo il punto di menu di primo livello, con default il
         * primo sotto menu 'ente'.
         */

        add_menu_page('Ente', 'Impostazioni FO', 'administrator', $this->slug, array($this, 'page_ente'), $this->menu_icon);
        add_submenu_page($this->slug, 'Ente', 'Ente', 'administrator', $this->slug, array($this, 'page_ente'));
        add_submenu_page($this->slug, 'Reindirizzamenti', 'Reindirizzamenti', 'administrator', $this->slug . '_redirect', array($this, 'page_redirect'));

        foreach (self::$plugins as $plugin => $title) {
            add_submenu_page(
                $this->slug, "Configurazione shortcode del plugin $title", "Shortcode $title", 'administrator', "{$this->slug}_$plugin", array($this, 'page_shortcode')
            );
        }

        if (!is_multisite()) {
            $this->add_menu_page_network();
        }
    }

    public function add_menu_page_network() {
        if (is_multisite()) {
            add_menu_page('Impostazioni generali', 'Pannello FO', 'administrator', $this->slug, array($this, 'page_generali'), $this->menu_network_icon);
        }

        add_submenu_page($this->slug, 'Impostazioni generali', 'Generali', 'administrator', $this->slug . (is_multisite() ? '' : '_generali'), array($this, 'page_generali'));

        if (!is_plugin_active('itaregistra/itaregistra.php')) {
            add_submenu_page($this->slug, 'Gestione campi per ruolo', 'Campi per ruolo', 'administrator', $this->slug . '_ruolocampi', array($this, 'page_ruolocampi'));
        }

        foreach (self::$plugins as $plugin => $title) {
            add_submenu_page(
                $this->slug, "Configurazione enti del plugin $title", "Config $title", 'administrator', "{$this->slug}_$plugin", array($this, 'page_plugin')
            );
        }
    }

    public function admin_bar_menu() {
        global $wp_admin_bar;

        if (!is_user_logged_in() || !is_multisite())
            return;

        if (count($wp_admin_bar->user->blogs) < 1 && !is_super_admin())
            return;

        $wp_admin_bar->add_node(array(
            'parent' => 'network-admin',
            'id' => 'network-admin-itafo',
            'title' => 'Pannello FO',
            'href' => network_admin_url('admin.php?page=itafrontoffice_impostazioni'),
        ));

        $blogs = $this->_wp_get_sites();

        foreach ($blogs as $blog) {
            $wp_admin_bar->add_node(array(
                'parent' => 'blog-' . $blog['blog_id'],
                'id' => 'blog-' . $blog['blog_id'] . '-itafo',
                'title' => 'Impostazioni FO',
                'href' => get_admin_url($blog['blog_id'], 'admin.php?page=itafrontoffice_impostazioni'),
            ));
        }
    }

    /**
     * Convalidatore di dati per la pagina 'generali'.
     * @param array $options
     * @return array
     */
    public function generali_options_handler($options) {
        if (isset($options['ajax'])) {
            $response = '';

            switch ($options['ajax']) {
                case 'crea_ruoli':
                    $adds = $dels = 0;

                    foreach ($_POST['roles'] as $role_slug => $value) {
                        if ($value == 1) {
                            if (wp_roles()->is_role($role_slug)) {
                                continue;
                            }

                            if (!isset($this->italsoft_roles[$role_slug])) {
                                continue;
                            }

                            if (add_role($role_slug, $this->italsoft_roles[$role_slug], array('read' => true))) {
                                $adds++;
                            }
                        } else {
                            if (!wp_roles()->is_role($role_slug)) {
                                continue;
                            }

                            remove_role($role_slug);
                            $dels++;
                        }
                    }

                    $response = sprintf('Ruoli aggiornati correttamente (%d aggiunti, %d rimossi).', $adds, $dels);
                    break;

                case 'allinea_ruoli':
                    global $wpdb;

                    $blogs = $this->_wp_get_sites();
                    $roles = get_blog_option(1, $wpdb->base_prefix . 'user_roles') ?: array();

                    foreach ($blogs as $blog) {
                        if ($blog['blog_id'] == 1) {
                            continue;
                        }

                        $blog_roles = get_blog_option($blog['blog_id'], sprintf('%s%d_user_roles', $wpdb->base_prefix, $blog['blog_id'])) ?: array();
                        $roles = array_merge($blog_roles, $roles);
                    }

                    foreach ($blogs as $blog) {
                        if ($blog['blog_id'] == 1) {
                            update_blog_option(1, $wpdb->base_prefix . 'user_roles', $roles);
                            continue;
                        }

                        update_blog_option($blog['blog_id'], sprintf('%s%d_user_roles', $wpdb->base_prefix, $blog['blog_id']), $roles);
                    }

                    $response = sprintf('Allineati i ruoli in %d siti.', count($blogs));
                    break;

                case 'blocca_signup':
                    $arrayBloccati = array();
                    foreach ($_POST['blocca_signup'] as $role_slug => $value) {
                        if ($value != 1) {
                            continue;
                        }

                        if (!wp_roles()->is_role($role_slug)) {
                            continue;
                        }

                        $arrayBloccati[] = $role_slug;
                    }

                    update_site_option($this->slug . '_blocco_signup', $arrayBloccati);

                    $response = sprintf('Bloccati %d ruoli in registrazione.', count($arrayBloccati));
                    break;

                case 'allinea_utenti_sito':
                    global $wpdb;

                    $users_id = $wpdb->get_col("SELECT $wpdb->users.ID FROM $wpdb->users");
                    $blog_id = (int) $_POST['allinea-sito-select'];
                    $inserted_count = 0;

                    if (!$blog_id) {
                        $response = sprintf('Seleziona un sito.');
                        break;
                    }

                    foreach ($users_id as $user_id) {
                        if ($this->_allinea_utente_per_sito($user_id, $blog_id, $response, $errors)) {
                            $inserted_count++;
                        }
                    }

                    $response .= sprintf('Allineati %d utenti (%d totali) nel sito %s.', $inserted_count, count($users_id), get_blog_details($blog_id)->blogname);
                    break;

                case 'allinea_utenti':
                    global $wpdb;

                    $users_id = $wpdb->get_col("SELECT $wpdb->users.ID FROM $wpdb->users");
                    $blogs = $this->_wp_get_sites();
                    $inserted_count = 0;

                    foreach ($blogs as $blog) {
                        $blog_id = (int) $blog['blog_id'];

//                        if ($blog_id === 1) {
//                            continue;
//                        }

                        foreach ($users_id as $user_id) {
                            if ($this->_allinea_utente_per_sito($user_id, $blog_id, $response, $errors)) {
                                $inserted_count++;
                            }
                        }
                    }

                    $response .= sprintf('Allineati %d utenti (%d totali) in %d siti.', $inserted_count, count($users_id), count($blogs));
                    break;

                case 'cambia_tema':
                    $theme_directory_slug = $_POST['tema-select'];

                    if (!$theme_directory_slug) {
                        $response = sprintf('Seleziona un tema.');
                        break;
                    }

                    $blogs = $this->_wp_get_sites();
                    $switchcount = 0;

                    foreach ($blogs as $blog) {
                        $blog_id = (int) $blog['blog_id'];

                        if (is_main_site($blog_id)) {
                            continue;
                        }

                        switch_to_blog($blog_id);
                        switch_theme($theme_directory_slug);
                        restore_current_blog();

                        $switchcount++;
                    }

                    $response .= sprintf('Tema cambiato in %d siti.', $switchcount);
                    break;

                case 'cimy_data_import':
                    global $wpdb;
                    $table_name_fields = $wpdb->prefix . 'cimy_uef_fields';
                    $table_name_wp_fields = $wpdb->prefix . 'cimy_uef_wp_fields';

                    if (!count($wpdb->get_results("SELECT ID FROM $table_name_fields"))) {
                        $sql = file_get_contents(WP_CONTENT_DIR . '/plugins/itaFrontOffice/includes/resources/wp_cimy_uef_fields.sql');
                        $sql = str_replace('wp_cimy_uef_fields', $table_name_fields, $sql);
                        $wpdb->query($sql);

                        $response = "Tabella '$table_name_fields' aggiornata.";
                    }

                    if (!count($wpdb->get_results("SELECT ID FROM $table_name_wp_fields"))) {
                        $sql = file_get_contents(WP_CONTENT_DIR . '/plugins/itaFrontOffice/includes/resources/wp_cimy_uef_wp_fields.sql');
                        $sql = str_replace('wp_cimy_uef_wp_fields', $table_name_wp_fields, $sql);
                        $wpdb->query($sql);

                        $response .= " Tabella '$table_name_wp_fields' aggiornata.";
                    }
                    break;

                case 'post_update_script':
                    include __DIR__ . '/itafrontoffice-post-update.php';
                    $response .= "Script eseguito.";
                    break;
            }

            if ('' !== $response) {
                echo $response;
                die;
            }
        }

        if (!empty($options['config'])) {
            file_put_contents(ITA_FRONTOFFICE_PLUGIN . '/config.inc.php', stripslashes($options['config']));
            $options['config'] = '';
        }

        return $options;
    }

    private function _allinea_utente_per_sito($user_identifier, $blog_id, &$response = '', &$errors = '') {
//        if (is_user_member_of_blog($user_id, $blog_id)) {
//            return false;
//        }

        if (!is_array($errors)) {
            $errors = array();
        }

        $user_name = is_numeric($user_identifier) ? null : $user_identifier;
        $user_id = is_numeric($user_identifier) ? $user_identifier : null;

        $user_temp_data = new WP_User($user_id, $user_name, 1);
        $user_roles = $user_temp_data->roles;

        $user_id = $user_temp_data->ID;

        if (!count($user_roles)) {
            $blogs = $this->_wp_get_sites();

            foreach ($blogs as $blog) {
                if ($blog['blog_id'] == 1) {
                    continue;
                }

                $user_sub_temp_data = new WP_User($user_id, null, $blog['blog_id']);

                if (count($user_sub_temp_data->roles)) {
                    $user_roles = $user_sub_temp_data->roles;
                    break;
                }
            }

            if (!count($user_roles)) {
                if (!in_array($user_id, $errors)) {
                    $errors[] = $user_id;
                    $response .= sprintf('L\'utente <b>%s</b> (%d) non ha impostato nessun ruolo nel sito principale!<br />', $user_temp_data->user_login, $user_id);
                }

                return false;
            }
        }

        $user_result = add_user_to_blog($blog_id, $user_id, $user_roles[0]);

        if ($user_result !== true) {
            $response .= sprintf("<b>Errore inserimento utente '%s' (%d): %s.</b><br />", $user_temp_data->user_login, $user_id, $user_result->get_error_message());
            return false;
        }

        return true;
    }

    public function ruolocampi_options_handler($options) {
        $data = array();

        foreach ($options as $ruolocampo => $value) {
            list($ruolo, $campo) = explode('.', $ruolocampo);

            if (!isset($data[$ruolo])) {
                $data[$ruolo] = array();
            }

            $data[$ruolo][$campo] = $value;
        }

        global $wpdb;
        $table_name = $wpdb->base_prefix . 'ruolo_campo';

        foreach ($data as $ruolo => $campi) {
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET campi = %s WHERE ruolo = %s", serialize($campi), $ruolo));
        }

        return true;
    }

    public function redirect_options_handler($options) {
        foreach ($options as $role => $opts) {
            foreach (array('login', 'logout') as $t) {
                if ($opts[$t . '_type'] !== 'custom') {
                    unset($options[$role][$t . '_url']);
                }

                if ($opts[$t . '_type'] !== 'copy') {
                    unset($options[$role][$t . '_copy']);
                }
            }
        }

        return $options;
    }

    public function shortcode_options_handler($options, $plugin) {
        if (isset($options['action_change'])) {
            if (empty($options['shortcode_ente']) || !preg_match('/^[a-z0-9]+$/i', $options['shortcode_ente'])) {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Inserire un valore valido.', 'error');
                return false;
            }

            $updated = array(0, 0);

            $results = get_posts(
                array(
                    'numberposts' => -1,
                    'post_status' => null,
                    'post_type' => 'any'
                )
            );

            if ($results) {
                $plugin_shortcode = current(explode('_', $plugin));

                foreach ($results as $post) {
                    $done = false;

                    if (preg_match_all('/' . get_shortcode_regex() . '/', $post->post_content, $matches)) {
                        foreach ($matches[0] as $shortcode) {
                            if (strpos($shortcode, $plugin_shortcode) === 1 && preg_match('/ente=([a-z0-9]+)/i', $shortcode, $ente_match)) {
                                if ($ente_match[1] === $options['shortcode_ente']) {
                                    continue;
                                }

                                $shortcode_updated = str_replace('ente=' . $ente_match[1], 'ente=' . $options['shortcode_ente'], $shortcode);
                                $post->post_content = str_replace($shortcode, $shortcode_updated, $post->post_content);

                                $post_updated = array();
                                $post_updated['ID'] = $post->ID;
                                $post_updated['post_content'] = $post->post_content;

                                wp_update_post($post_updated);

                                $done = true;
                                $updated[1] ++;
                            }
                        }
                    }

                    if ($done) {
                        $updated[0] ++;
                    }
                }
            }

            if ($updated[0] === 0) {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Nessuno shortcode da modificare.', 'updated');
            } else {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', sprintf('Sono stati modificati %d shortcode in %d elementi.', $updated[1], $updated[0]), 'updated');
            }
        }

        return false;
    }

    public function plugin_options_handler($options, $plugin) {
        if (
            isset($options['submit']) &&
            isset($options['plugin_config_ente']) &&
            !empty($options['plugin_config_ente']) &&
            isset($options['plugin_config']) &&
            !empty($options['plugin_config'])
        ) {
            /*
             * Modifica file di configurazione ente.
             */

            $file_configurazione = $this->_get_config_path($plugin, $options['plugin_config_ente']);

            if ($file_configurazione) {
                file_put_contents($file_configurazione, stripslashes($options['plugin_config']));
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'File di configurazione aggiornato.', 'updated');
            } else {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Codice ente non valido!', 'error');
            }
        }

        if (isset($options['action_delete']) && isset($options['plugin_delete']) && !empty($options['plugin_delete'])) {
            /*
             * Cancellazione file di configurazione ente.
             */

            $file_configurazione = $this->_get_config_path($plugin, $options['plugin_delete']);

            if ($file_configurazione) {
                unlink($file_configurazione);
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Ente eliminato.', 'error');
            } else {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Codice ente non valido!', 'error');
            }
        }

        return array();
    }

    public function page_ente() {
        $this->render_page('ente');
    }

    public function page_redirect() {
        $this->render_page('redirect');
    }

    public function page_generali() {
        $this->render_page('generali', true);
    }

    public function page_ruolocampi() {
        $this->render_page('ruolocampi', true);
    }

    public function page_plugin() {
        $this->render_page('plugin');
    }

    public function page_shortcode() {
        $this->render_page('shortcode');
    }

    public function render_page($page, $multisite = false) {
        $options_slug = "{$this->slug}_{$page}_options";
        $options_handler_params = array();

        if (in_array($page, array('plugin', 'shortcode'))) {
            $plugin = substr($_GET['page'], strlen($this->slug . '_'));
            $options_handler_params[] = $plugin;
        }

        $options = is_multisite() && $multisite ? get_site_option($options_slug) : get_option($options_slug);
        if (!is_array($options)) {
            $options = array();
        }

        $original_options = $options;
        $options = $this->array_stripslashes($options);

        if (isset($_POST) && count($_POST)) {
            $options = (array) $_POST[$options_slug];

            if (method_exists($this, $page . '_options_handler')) {
                array_unshift($options_handler_params, $options);
                $options = call_user_func_array(array($this, $page . '_options_handler'), $options_handler_params);
            } else {
                $options = array_merge($original_options, $options);
            }

            $response = $options;

            if (is_array($options) && count($options)) {
                $options = $this->array_stripslashes($options);

                if (is_multisite() && $multisite) {
                    $response = update_site_option($options_slug, $options);
                } else {
                    $response = update_option($options_slug, $options);
                }
            }

            if ($response) {
                add_settings_error($this->slug . '_messages', $this->slug . '_message', 'Impostazioni salvate.', 'updated');
            }
        }

        settings_errors($this->slug . '_messages');
        include __DIR__ . "/itafrontoffice-impostazioni/page-$page.php";
    }

    protected function array_stripslashes($a) {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                $a[$k] = is_string($v) ? stripslashes($v) : $this->array_stripslashes($v);
            }
        }

        return $a;
    }

    /*
     * Funzioni per gli shortcode.
     */

    public function register_shortcodes() {
        add_shortcode('ente_logo', array($this, 'shortcode_logo'));
        add_shortcode('ente_denominazione', array($this, 'shortcode_denominazione'));
        add_shortcode('ente_contatti', array($this, 'shortcode_contatti'));
        add_shortcode('home_url', array($this, 'shortcode_home_url'));

        $options_slug = "{$this->slug}_ente_options";

        foreach ($this->ente_options as $key => $option) {
            add_shortcode("{$this->shortcode_slug}_ente_$key", function() use ($options_slug, $key) {
                $options = get_option($options_slug);
                $value = $options[$key];

                if ($value) {
                    switch ($key) {
                        case 'email':
                        case 'pec':
                            $value = "<a href=\"mailto:$value\" style=\"word-break: break-all; display: inline-block;\">$value</a>";
                            break;
                    }
                }

                return $value;
            });
        }
    }

    public function shortcode_logo() {
        return get_option('ente_logo');
    }

    public function shortcode_denominazione() {
        return get_option('ente_denominazione');
    }

    public function shortcode_contatti() {
        return get_option('ente_contatti');
    }

    public function shortcode_home_url() {
        return site_url();
    }

    /*
     * Funzioni per la generazione della pagina di configurazione
     * di un plugin, da registrare tramite la funzione 'config'.
     */

    static public function register_plugin($title, $plugin) {
        self::$plugins[$plugin] = $title;
    }

    /*
     * Funzioni per utilizzo interno.
     */

    private function _get_enti_plugin($plugin) {
        return array_filter(array_map(array($this, '_array_map_enti'), glob(WP_PLUGIN_DIR . '/' . $plugin . '/config.inc.*.php')));
    }

    private function _array_map_enti($value) {
        preg_match('/config\.inc\.([^.]*?)\.php/', $value, $match);
        return $match[1] ? $match[1] : false;
    }

    private function _get_config_path($plugin, $ente = false) {
        if ($ente === false) {
            return WP_PLUGIN_DIR . '/' . $plugin . '/config.inc.01.sample.php';
        }

        if (!preg_match('/^\w{1,6}$/', $ente)) {
            return false;
        }

        return WP_PLUGIN_DIR . '/' . $plugin . '/config.inc.' . $ente . '.php';
    }

    private function _get_single_upload_file($array, $name) {
        return array(
            'name' => $array['name'][$name],
            'type' => $array['type'][$name],
            'tmp_name' => $array['tmp_name'][$name],
            'error' => $array['error'][$name],
            'size' => $array['size'][$name]
        );
    }

    /**
     * 
     * @todo Sostituire con get_sites() ?
     */
    private function _wp_get_sites($args = array()) {
        if (function_exists('get_sites')) {
            $blog_ids = get_sites(array('fields' => 'ids'));
            return array_map(function($id) {
                return array('blog_id' => $id);
            }, $blog_ids);
        }

        global $wpdb;

        $defaults = array(
            'include_id' => '', // includes only these sites in the results, comma-delimited
            'exclude_id' => '', // excludes these sites from the results, comma-delimted
            'blogname_like' => '', // domain or path is like this value
            'ip_like' => '', // Match IP address
            'reg_date_since' => '', // sites registered since (accepts pretty much any valid date like tomorrow, today, 5/12/2009, etc.)
            'reg_date_before' => '', // sites registered before
            'include_user_id' => '', // only sites owned by these users, comma-delimited
            'exclude_user_id' => '', // don't include sites owned by these users, comma-delimited
            'include_spam' => false, // Include sites marked as "spam"
            'include_deleted' => false, // Include deleted sites
            'include_archived' => false, // Include archived sites
            'include_mature' => false, // Included blogs marked as mature
            'public_only' => true, // Include only blogs marked as public
            'sort_column' => 'registered', // or registered, last_updated, blogname, site_id.
            'order' => 'asc', // or desc
            'limit_results' => '', // return this many results
            'start' => '', // return results starting with this item
        );

        $r = wp_parse_args($args, $defaults);
        extract($r, EXTR_SKIP);
        $query = "SELECT * FROM {$wpdb->blogs} as b ";

        if ($sort_column == 'site_id') {
            $query .= ' ORDER BY b.`blog_id` ';
        } elseif ($sort_column == 'lastupdated') {
            $query .= ' ORDER BY b.`last_updated` ';
        } elseif ($sort_column == 'blogname') {
            $query .= ' ORDER BY b.`domain` ';
        } else {
            $sort_column = 'registered';
            $query .= " ORDER BY b.`registered` ";
        }

        $order = ( 'desc' == $order ) ? "DESC" : "ASC";
        $query .= $order;

        $limit = '';
        if (!empty($limit_results)) {
            if (!empty($start)) {
                $limit = $start . " , ";
            }
            $query .= "LIMIT " . $limit . $limit_results;
        }

        $sql = $wpdb->prepare($query);

        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }

}

new Itafrontoffice_Impostazioni();
