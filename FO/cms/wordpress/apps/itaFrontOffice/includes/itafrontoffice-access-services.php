<?php

class Itafrontoffice_Access_Services {

    private $service;
    private $slug = 'itafrontoffice_accessservices';
    private $tbversion = '1.2';

    const SERVICE_SPID = 'spid';
    const SERVICE_COHESION = 'cohesion';

    public function __construct($service) {
        $this->service = $service;
    }

    public function ssoError($syslog, $display_message) {
        $frontOfficeErr = new frontOfficeErr();
        $frontOfficeErr->parseError(basename(__FILE__), 'IAS-01', $syslog, 'Itafrontoffice_Access_Services', $display_message);
        wp_set_current_user(0);
        wp_logout();
        $frontOfficeErr->getHtmlCms();
        exit;
    }

    public function ssoLogin($codice_fiscale) {
        $users_list = get_cimyFieldValue(false, 'FISCALE', $codice_fiscale);

        if (!$users_list || !count($users_list)) {
            return false;
        }

        $user = new WP_User($users_list[0]['user_id']);
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login);

        if (defined('ITA_FRONTOFFICE_ACCESS_SERVICES_LOGS') && ITA_FRONTOFFICE_ACCESS_SERVICES_LOGS === true) {
            $this->insertUserLog($user->ID);
        }

        return true;
    }

    public function ssoSignup($userData, $role = null) {
        $codice_fiscale = $userData['codice_fiscale'];
        $nome = $userData['nome'];
        $cognome = $userData['cognome'];
        $email_certificata = $userData['email_certificata'];
        $residenza_indirizzo = $userData['residenza_indirizzo'];
        $residenza_localita = $userData['residenza_localita'];
        $residenza_cap = $userData['residenza_cap'];
        $residenza_provincia = $userData['residenza_provincia'];

        frontOfficeLib::sysLog("Avvio registrazione automatizzata nuovo utente. Utente: {$codice_fiscale}, CF: {$codice_fiscale}");

        /*
         * Verifica del codice fiscale.
         */
        $users_fiscale = get_cimyFieldValue(false, 'FISCALE', $codice_fiscale);

        if (count($users_fiscale) > 0) {
            $this->ssoError("Codice Fiscale {$codice_fiscale} già presente, impossibile registrare un nuovo utente", 'Accesso negato - Codice fiscale già utilizzato da altra utenza.');
            return false;
        }

        /*
         * Preparo l'array utente per la registrazione.
         */
        $user_info = array();
        $user_info['ID'] = '';
        $user_info['user_login'] = $codice_fiscale;
        $user_info['user_pass'] = wp_generate_password();
        $user_info['first_name'] = $nome;
        $user_info['last_name'] = $cognome;
        $user_info['user_email'] = $email_certificata;

        if (!is_null($role)) {
            $user_info['role'] = $role;
        }

        $wp_uid = wp_insert_user($user_info);

        if (is_wp_error($wp_uid) || $wp_uid === 0) {
            /*
             * Errore in registrazione.
             */
            $err = utf8_decode($wp_uid->get_error_message());

            /*
             * Patch personalizzazione messaggio.
             */
            $user_err = $err;
            switch ($wp_uid->get_error_code()) {
                case 'existing_user_email':
                    $user_err = "L'indirizzo e-mail indicato in registrazione è utilizzato da più utenti o non corretto. Modificare l'indirizzo e-mail nell'apposita sezione Profilo - Modifica contatti prima di procedere nuovamente con l'inserimento della domanda online.";
                    break;
            }

            $this->ssoError("Errore in registrazione nuovo utente {$codice_fiscale} <{$email_certificata}> | $err", $user_err);
            return false;
        }

        $user = get_user_by('id', $wp_uid);

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);

        $mappa_campi_cimyuef = array(
            'FISCALE' => $codice_fiscale,
            'INDIRIZZO' => $residenza_indirizzo,
            'COMUNE' => $residenza_localita,
            'CAP' => $residenza_cap,
            'PROVINCIA' => $residenza_provincia
        );

        foreach ($mappa_campi_cimyuef as $key => $value) {
            if (!set_cimyFieldValue($wp_uid, $key, $value)) {
                $this->ssoError("Errore inserimento $key '$value' in $codice_fiscale", 'Errore di accesso al servizio.');
            }
        }

        if (!is_null($role)) {
            $blog_ids = get_sites(array('fields' => 'ids'));

            foreach ($blog_ids as $blog_id) {
                $result_add_user = add_user_to_blog($blog_id, $wp_uid, $role);

                if ($result_add_user !== true) {
                    /*
                     * Errore in allineamento utente.
                     */

                    $err = utf8_decode($result_add_user->get_error_message());
                    $this->ssoError("Errore in registrazione nuovo utente {$codice_fiscale} <{$email_certificata}> | $err", $err);
                    return false;
                }
            }
        }

        return true;
    }

    public function getTableName() {
        global $wpdb;
        return $wpdb->base_prefix . 'itafrontoffice_access_logs';
    }

    public function updateTable() {
        global $wpdb;

        $table_name = $this->getTableName();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  user bigint(20) NOT NULL,
  time varchar(12) NOT NULL,
  path varchar(20) NOT NULL,
  service varchar(20) NOT NULL,
  user_agent varchar(512) NOT NULL,
  PRIMARY KEY (id)
) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_site_option($this->slug . '_tbversion', $this->tbversion);
    }

    public function checkValidTableVersion() {
        $current_version = get_site_option($this->slug . '_tbversion');
        return $current_version && version_compare($current_version, $this->tbversion, '>=');
    }

    public function insertUserLog($user_id) {
        if (!$this->checkValidTableVersion()) {
            $this->updateTable();

            if (!$this->checkValidTableVersion()) {
                return false;
            }
        }

        global $wpdb;

        $blog_details = get_blog_details(get_current_blog_id());

        $wpdb->insert($this->getTableName(), array(
            'time' => date('YmdHi'),
            'user' => $user_id,
            'path' => $blog_details->path,
            'service' => $this->service,
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ));

        return true;
    }

}
