<?php

/*
  Plugin Name: ITAFRONTOFFICE Italsoft
  Description: Framework base per le applicazioni itaFrontOffice.
  Author: Italsoft
  Version: 4.0.7
  Author URI: http://www.italsoft.eu/
 */

session_start();

define('ITA_FRONTOFFICE_PLUGIN', __DIR__);
define('ITA_FRONTOFFICE_INCLUDES', ITA_FRONTOFFICE_PLUGIN . '/includes');
//
// Carico la configurazione del plug-in
//
if (!file_exists(ITA_FRONTOFFICE_PLUGIN . '/config.inc.php')) {
    die('Configurazione itaFrontOffice non trovata');
}

require_once ITA_FRONTOFFICE_PLUGIN . '/config.inc.php';

require_once ITA_LIB_PATH . '/itaPHPCore/frontOfficeApp.class.php'; // Carico la classe base del FrameWork

frontOfficeApp::load('wp');

/*
 * Filter input
 */
add_filter('the_content', 'itafrontoffice_filter_input_content', 1);

function itafrontoffice_filter_input_content($content) {
    $html = new html();

    if (count(frontOfficeApp::getFilteredInputs())) {
        $html->addAlert("Sono stati rimossi dati potenzialmente pericolosi nei seguenti valori in ingresso:<br><ul><li><b>" . implode('</b></li><li><b>', frontOfficeApp::getFilteredInputs()) . "</b></li></ul><br>&nbsp;<br>Verifica attentamente i termini usati nei campi di inserimento.", 'Attenzione', 'error');
    }

    return $html->getHtml() . $content;
}

/*
 * Caricamento librerie del mu-plugin.
 */

require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-access-services.php';

global $wp_version;

if (version_compare($wp_version, '4.0.0') >= 0) {
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-disable-comments.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-custom-meta-widget.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-multisite-select.php';

//    if (defined('ITA_FRONTOFFICE_SETTINGS') && ITA_FRONTOFFICE_SETTINGS === true) {
        require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-impostazioni.php';
//    }

    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-update-checker.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-functions.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-ajax.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-post-limited-access.php';
    require_once ITA_FRONTOFFICE_INCLUDES . '/itafrontoffice-download.php';
}

require_once ITA_FRONTOFFICE_PLUGIN . '/shortcodes.php';

//
// Compilazione html head
//
function itaFrontOffice_insercssjs_func() {
    frontOfficeApp::addCSSFrontOffice();
    frontOfficeApp::addJsFrontOffice();

    if (!is_user_logged_in()) {
//        wp_dequeue_script('tidio-chat');
    }
}

function itaFrontOffice_insercssjsadmin_func() {
    frontOfficeApp::addCSSFrontOfficeAdmin();
    frontOfficeApp::addJsFrontOfficeAdmin();
}

add_action('wp_head', 'itaFrontOffice_insercssjs_func');
add_action('admin_head', 'itaFrontOffice_insercssjsadmin_func');

//   GESTIONE DEL BUFFERING PER CONSENTIRE IL DOWNLOAD DI FILE IN STREAMING
function stampa_buffer($buffer) {
    return $buffer;
}

function buffer_start() {
    ob_start("stampa_buffer");
}

function buffer_end() {
    if (ob_get_level()) {
        ob_end_flush();
    }
}

add_action('init', 'buffer_start');
add_action('shutdown', 'buffer_end');

function ita_custom_headers() {
    header('P3P: CP="No_policy"');
}

add_action('send_headers', 'ita_custom_headers', 1);

/*
 * Procedura speciale di autologin
 */

function ita_auto_login_err($code, $logerr = '', $usrerr = '') {
    switch ($code) {
        
    }

    $frontOfficeErr = new frontOfficeErr();
    $frontOfficeErr->parseError(basename(__FILE__), $code, $logerr, 'ita_auto_login()', $usrerr);
    wp_set_current_user(0);
    wp_logout();
    echo $frontOfficeErr->getHtmlCms();
    exit;
}

function ita_auto_login() {
    if (isset($_GET['cptoken'])) {
        frontOfficeLib::sysLog("Avvio auto_login con token {$_GET['cptoken']}.");

        /*
         * Logout preventivo da precedenti accessi
         */

        wp_set_current_user(0);
        wp_logout();

        /*
         * Controllo le configurazioni di base di cimy per itaFrontOffice
         */

        $allFields = get_cimyFields();
        $ok_fiscale = false;
        $ok_progsogg = false;

        foreach ($allFields as $cimyField) {
            if ($cimyField['NAME'] == 'FISCALE') {
                $ok_fiscale = true;
            }

            if ($cimyField['NAME'] == 'CITY_PROGSOGG') {
                $ok_progsogg = true;
            }

            if ($ok_fiscale === true && $ok_progsogg === true) {
                break;
            }
        }

        if ($ok_progsogg === false || $ok_fiscale === false) {
            ita_auto_login_err(1, 'Campi Cimy FISCALE - CITY_PROGSOGG non configurati', 'Errore di accesso al servizio.');
        }

        require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $rest = new itaRestClient;

        $rest->setTimeout(100);

        if (!defined('ITA_CITYPORTAL_REST_URL') || !ITA_CITYPORTAL_REST_URL) {
            ita_auto_login_err(15, 'Costante ITA_CITYPORTAL_REST_URL non definita o valorizzata', 'Errore di accesso al servizio.');
        }

        if (!$rest->post(ITA_CITYPORTAL_REST_URL, array('METHOD' => 'verifyToken', 'TOKEN' => $_GET['cptoken'])) || $rest->getHttpStatus() != 200) {
            ita_auto_login_err(2, 'Richiesta ad ' . ITA_CITYPORTAL_REST_URL . ' fallita con status ' . $rest->getHttpStatus() . ' Messaggio: ' . $rest->getErrMessage(), 'Errore di accesso al servizio.');
        }

        $cp_data = json_decode(base64_decode($rest->getResult()), true);

        if (!is_array($cp_data)) {
            ita_auto_login_err(13, 'Risultato richiesta ad ' . ITA_CITYPORTAL_REST_URL . ' non conforme. Response: ' . $rest->getResult(), 'Errore di accesso al servizio.');
        }

//        $cp_data = array(
//            'utente' => 'random1450439452319',
//            'mail' => 'random1450439452319@apra.it',
//            'codiceFiscale' => 'CRDAED54S34E130C',
//            'timeStamp' => '',
//            'progsogg' => '39'
//        );

        $cp_utente = $cp_data['utente'];
        $cp_mail = $cp_data['mail'];
        $cp_codiceFiscale = $cp_data['codiceFiscale'];
        $cp_timeStamp = $cp_data['timeStamp'];
        $cp_progSogg = $cp_data['progsogg'];

        if (!$cp_utente || !$cp_codiceFiscale || !$cp_progSogg) {
            ita_auto_login_err(14, 'Utente, codice fiscale o progressivo soggetto ricevuti da ' . ITA_CITYPORTAL_REST_URL . ' mancanti.', 'Errore di accesso al servizio.');
        }

        /*
         * Controllo se utente esiste
         */

        /* @var $user WP_User */
        $user = get_user_by('login', $cp_utente);

        if ($user) {
            /*
             * Utente gi� presente
             */

            frontOfficeLib::sysLog("Avvio login automatizzato. Utente: $cp_utente, CF: $cp_codiceFiscale, Progsogg: $cp_progSogg, Mail: $cp_mail.");

            /*
             * Qui controllo se ha il codice fiscale atteso 
             */

            $wp_codiceFiscale = get_cimyFieldValue($user->ID, 'FISCALE');

            if ($wp_codiceFiscale && $wp_codiceFiscale != $cp_codiceFiscale) {
                /*
                 * Codice Fiscale errato, errore
                 */
                ita_auto_login_err(3, "Codice Fiscale utente $cp_utente non coincidente ( wp $wp_codiceFiscale, backend $cp_codiceFiscale )", 'Errore di accesso al servizio.');
            }

            /*
             * Controllo progressivo
             */

            $wp_progSogg = get_cimyFieldValue($user->ID, 'CITY_PROGSOGG');

            if ($wp_progSogg && $wp_progSogg != $cp_progSogg) {
                /*
                 * Progressivo errato, errore
                 */
                ita_auto_login_err(4, "Progressivo utente $cp_utente non coincidente ( wp $wp_progSogg, backend $cp_progSogg )", 'Errore di accesso al servizio.');
            }

            /*
             * Update della mail
             */

            update_user_meta($user->ID, 'user_email', $cp_mail);

            if (get_user_meta($user->ID, 'user_email', true) != $cp_mail) {
                ita_auto_login_err(12, "Aggiornamento email utente $cp_utente <$cp_mail> non riuscito", 'Errore di accesso al servizio.');
            }

            /*
             * Autologin
             */

            $wp_uid = $user->ID;
            wp_set_current_user($wp_uid);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);

            /*
             * Aggiorno i campi di cimy vuoti dopo il login
             * (set_cimyFieldValue necessita di un utente loggato)
             */

            if (!$wp_codiceFiscale) {
                /*
                 * Codice Fiscale assente o vuoto, aggiorno manualmente
                 */
                if (!set_cimyFieldValue($user->ID, 'FISCALE', $cp_codiceFiscale)) {
                    ita_auto_login_err(10, "Errore inserimento FISCALE $cp_codiceFiscale in $cp_utente", 'Errore di accesso al servizio.');
                }
            }

            if (!$wp_progSogg) {
                /*
                 * Progressivo assente o vuoto, aggiorno manualmente
                 */
                if (!set_cimyFieldValue($user->ID, 'CITY_PROGSOGG', $cp_progSogg)) {
                    ita_auto_login_err(11, "Errore inserimento CITY_PROGSOGG $cp_progSogg in $cp_utente", 'Errore di accesso al servizio.');
                }
            }
        } else {
            /*
             * Nuovo utente
             */

            frontOfficeLib::sysLog("Avvio registrazione automatizzata nuovo utente. Utente: $cp_utente, CF: $cp_codiceFiscale, Progsogg: $cp_progSogg, Mail: $cp_mail.");

            /*
             * Controllo indirizzo email
             */

            if (!$cp_mail) {
                /*
                 * Indirizzo email non presente, errore
                 */
                $user_err = "L'indirizzo e-mail per accedere al servizio � obbligatorio. Inserire l'indirizzo e-mail nell'apposita sezione 'Profilo - Modifica contatti' prima di procedere nuovamente con l'inserimento della domanda online.";
                ita_auto_login_err(16, "Indirizzo email mancante, impossibile registrare un nuovo utente", $user_err);
            }

            /*
             * Controllo Codice Fiscale
             */

            $users_fiscale = get_cimyFieldValue(false, 'FISCALE', $cp_codiceFiscale);

            if (count($users_fiscale) > 0) {
                /*
                 * Codice Fiscale gi� presente, errore
                 */
                ita_auto_login_err(5, "Codice Fiscale $cp_codiceFiscale gi� presente, impossibile registrare un nuovo utente", 'Accesso negato - Codice fiscale gi� utilizzato da altra utenza.');
            }

            /*
             * Controllo Progressivo
             */

            $users_progsogg = get_cimyFieldValue(false, 'CITY_PROGSOGG', $cp_progSogg);

            if (count($users_progsogg) > 0) {
                /*
                 * Progressivo gi� presente, errore
                 */
                ita_auto_login_err(6, "Progessivo $cp_progSogg gi� presente, impossibile registrare un nuovo utente", 'Errore di accesso al servizio.');
            }

            /*
             * Auto registrazione
             */

            $user_info = array();
            $user_info['ID'] = '';
            $user_info['user_login'] = $cp_utente;
            $user_info['user_pass'] = wp_generate_password();
            $user_info['user_email'] = $cp_mail;

            $wp_uid = wp_insert_user($user_info);

            if (is_wp_error($wp_uid) || $wp_uid === 0) {
                /*
                 * Errore in registrazione
                 */

                $err = utf8_decode($wp_uid->get_error_message());

                /*
                 * Patch personalizzazione messaggio
                 */
                $user_err = $err;

                switch ($wp_uid->get_error_code()) {
                    case 'existing_user_email':
                        $user_err = "L'indirizzo e-mail indicato in registrazione � utilizzato da pi� utenti o non corretto. Modificare l'indirizzo e-mail nell'apposita sezione Profilo - Modifica contatti prima di procedere nuovamente con l'inserimento della domanda online.";
                        break;
                }

                ita_auto_login_err(7, "Errore in registrazione nuovo utente $cp_utente <$cp_mail> | $err", $user_err);
                /*
                 * Fine patch
                 */
            }

            $user = get_user_by('id', $wp_uid);

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);

            if (!set_cimyFieldValue($wp_uid, 'FISCALE', $cp_codiceFiscale)) {
                ita_auto_login_err(8, "Errore inserimento FISCALE $cp_codiceFiscale in $cp_utente", 'Errore di accesso al servizio.');
            }

            if (!set_cimyFieldValue($wp_uid, 'CITY_PROGSOGG', $cp_progSogg)) {
                ita_auto_login_err(9, "Errore inserimento CITY_PROGSOGG $cp_progSogg in $cp_utente", 'Errore di accesso al servizio.');
            }

            do_action('wp_login', $user->user_login);
        }
    }
}

add_action('init', 'ita_auto_login');

// IPOTESI PER BLOCCARE CODICI FISCALI DOPPI
// !!! NON USARE E' SOLO UN ESEMPIO TENUTO PER PROMEMORIA !!!!!!
// 
//function ita_registra_check($errors, $sanitized_user_login, $user_email) {
//	////// INIZIO CONTROLLO CODICE FISCALE
//	$users_fiscale_list = cimy_ita_getUserFromFieldValue('FISCALE', $_POST['cimy_uef_FISCALE']);
//	print_r("<pre>");
//	print_r($users_fiscale_list);
//	print_r("</pre>");
//	if (count($users_fiscale_list) > 0) {
//		$errors->add("CODICE_FISCALE", '<strong>Codice Fiscale gi� usato</strong>');
//		return $errors;
//    }
//    ////// FINE CONTROLLO CODICE FISCALE	
//}
//add_filter('wpmu_validate_user_signup', 'ita_registra_check');

/*
 * Inizio controllo Codice Fiscale
 */

/**
 * Controllo aggiornamento profilo
 * @param type $arr
 * @param type $sanitized_user_login
 * @param type $user_email
 * @return type
 */
function ita_signup_check_fields($arr) {
    $codice_fiscale = $_POST['cimy_uef_FISCALE'];

    if ($codice_fiscale) {
        global $wpdb;
        $table_name = $wpdb->base_prefix . "signups";

        $users_fiscale = get_cimyFieldValue(false, 'FISCALE', $codice_fiscale);
        $signup_fiscale = $wpdb->get_col($wpdb->prepare("SELECT * FROM $table_name WHERE meta LIKE %s", "%$codice_fiscale%"), 10);

        if (count($users_fiscale) > 0) {
            $arr['errors']->add('cimy_uef_1', __('Questo codice fiscale &egrave; gi&agrave; utilizzato!'));
            return $arr;
        }

        // Controllo tra i signup
        if (count($signup_fiscale)) {
            foreach ($signup_fiscale as $signup) {
                $signup_meta = unserialize($signup);
                if ($signup_meta) {
                    foreach ($signup_meta as $key => $value) {
                        if ('cimy_uef_FISCALE' === $key && $codice_fiscale === $value) {
                            $arr['errors']->add('cimy_uef_1', __('Questo codice fiscale &egrave; stato gi&agrave; utilizzato. Verifica se nella tua posta &egrave; arrivata una email di attivazione. Se non fai nulla, il codice diverr&agrave; nuovamente disponibile in un paio di giorni.'));
                            return $arr;
                        }
                    }
                }
            }
        }
    }

    return $arr;
}

add_filter('wpmu_validate_user_signup', 'ita_signup_check_fields', 10, 3);

/**
 * Controllo registrazione nuovo utente
 * @param type $errors Errors object to add any custom errors to
 * @param type $update true if updating an existing user, false if saving a new user
 * @param type $user User object for user being edited
 * @return type
 */
function ita_update_check_fields($errors, $update, $user) {
    if ($update !== true)
        return;

    $codice_fiscale = $_POST['cimy_uef_FISCALE'];

    if (get_current_user_id() === $user->ID) {
        // Aggiornamento personale, non posso cambiare il CF
        if ($_POST['cimy_uef_FISCALE_1_prev_value'] !== $codice_fiscale) {
            $errors->add('errore_codice_fiscale', __('Il codice fiscale non pu&ograve; essere modificato!'));
        }
    } else if ($codice_fiscale) {
        // Aggiornamento altro utente, controllo se ha il ruolo corretto e se il CF � univoco
        global $wpdb;
        $table_name = $wpdb->base_prefix . "ruolo_campo";
        $ruolo_campo_rec = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ruolo = %s", $user->role));
        $campi = unserialize($ruolo_campo_rec->campi);
        foreach ($campi as $field => $enabled) {
            if ($field === 'FISCALE' && $enabled === '1') {
                $users_fiscale = get_cimyFieldValue(false, 'FISCALE', $codice_fiscale);
                if (count($users_fiscale) > 0) {
                    if (count($users_fiscale) === 1 && intval($users_fiscale[0]['user_id']) === intval($user->ID)) {
                        return;
                    }

                    $errors->add('cimy_uef_1', __('Questo codice fiscale &egrave; gi&agrave; utilizzato!'));
                }
            }
        }
    }
}

add_filter('user_profile_update_errors', 'ita_update_check_fields', 10, 3);

/*
 * Sincronizzazione parametri SMTP del plugin
 * configure-smtp
 */

function ita_sync_smtp_params() {
    if (!defined('ITA_FRONTOFFICE_DISALLOW_SMTP_SYNC') || ITA_FRONTOFFICE_DISALLOW_SMTP_SYNC == false) {
        $c2c_key = 'c2c_configure_smtp';

        global $wpdb;

        $current_options = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = '{$c2c_key}'");
        $main_options = $wpdb->get_var("SELECT option_value FROM {$wpdb->base_prefix}options WHERE option_name = '{$c2c_key}'");

        if ($current_options !== $main_options) {
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}options SET option_value = %s WHERE option_name = %s", $main_options, $c2c_key));
//        update_option($c2c_key, unserialize($main_options), 'yes');
        }
    }
}

add_action('lostpassword_form', 'ita_sync_smtp_params');

if (!function_exists('cimy_ita_filtra_campi')) {

    /**
     * Funzione-estensione per cimy-user-extra-fields
     * @param type $ruolo
     * @param type $extra_fields
     * @return type
     */
    function cimy_ita_filtra_campi($ruolo, $extra_fields) {
        if ($ruolo) {
            global $wpdb;
            $table_name = $wpdb->base_prefix . "ruolo_campo";
            $sql = "SELECT * FROM $table_name WHERE ruolo = '" . $ruolo . "'";
            $Ruolo_campo_rec = $wpdb->get_results($sql, 'ARRAY_A');
            $campi = unserialize($Ruolo_campo_rec[0]['campi']);
            $extra_new = array();
            foreach ($extra_fields as $extra) {
                if ($campi[$extra['NAME']] && $campi[$extra['NAME']] == "1") {
                    $extra_new[] = $extra;
                }
            }
            $extra_fields = $extra_new;
        }
        return $extra_fields;
    }

}
