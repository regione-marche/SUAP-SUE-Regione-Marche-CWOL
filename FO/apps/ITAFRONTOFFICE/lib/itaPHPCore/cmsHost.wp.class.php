<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cmsHost
 *
 * @author michele
 */
class cmsHost_wp implements cmsHostInterface {

    public $getdb;

    public function getUserName() {
        if (!function_exists('wp_get_current_user')) {
            global $current_user;
            get_currentuserinfo();
        } else {
            $current_user = wp_get_current_user();
        }

        return $current_user->user_login;
    }

    public function getPassword() {
        
    }

    public function getUserID() {
        if (!function_exists('wp_get_current_user')) {
            global $current_user;
            get_currentuserinfo();
        } else {
            $current_user = wp_get_current_user();
        }

        return $current_user->ID;
    }

    public function getSiteName() {
        return get_bloginfo('name');
    }

    public function getSiteHomepageURI() {
        return get_site_url();
    }

    public function getAltriDati($dato = "") {
        /*
         * Set wpdb
         */
        global $wpdb;
        $wpdb->select(DB_NAME);

        $valore = get_cimyFieldValue($this->getUserID(), $dato);
        if ($valore) {
            return $valore;
        }
    }

    public function getCodFisFromUtente($nomeUtente = false) {
        if (!$nomeUtente) {
            $nomeUtente = $this->getUserName();
        }

        /*
         * Set wpdb
         */
        global $wpdb;
        $wpdb->select(DB_NAME);
        $user = get_userdatabylogin($nomeUtente);
        return get_cimyFieldValue($user->ID, "FISCALE");
    }

    public function setDatiUtente($datiUtente) {
        
    }

    public function getDatiUtente() {
        if (!function_exists('wp_get_current_user')) {
            global $current_user;
            get_currentuserinfo();
        } else {
            $current_user = wp_get_current_user();
        }

        $returnArray = array(
            "ESIBENTE_CMSUSER" => $current_user->data->user_login,
            /*
             * Modifica a "first_name" e "last_name" per compatibilità
             * con Wordpress 4.4.2
             */
//            "ESIBENTE_NOME" => $current_user->data->first_name,
//            "ESIBENTE_COGNOME" => $current_user->data->last_name,
            "ESIBENTE_NOME" => get_user_meta($current_user->ID, 'first_name', true),
            "ESIBENTE_COGNOME" => get_user_meta($current_user->ID, 'last_name', true),
            "ESIBENTE_PEC" => $current_user->data->user_email,
            "ESIBENTE_EMAIL" => $current_user->data->user_email,
            "ESIBENTE_CODICEFISCALE_CFI" => $this->getAltriDati("FISCALE"),
            "ESIBENTE_RESIDENZAVIA" => $this->getAltriDati("INDIRIZZO"),
            "ESIBENTE_RESIDENZACIVICO" => $this->getAltriDati("CIVICO"),
            "ESIBENTE_RESIDENZACOMUNE" => $this->getAltriDati("COMUNE"),
            "ESIBENTE_RESIDENZACAP_CAP" => $this->getAltriDati("CAP"),
            "ESIBENTE_RESIDENZAPROVINCIA_PV" => $this->getAltriDati("PROVINCIA"),
            "ESIBENTE_TELEFONO" => $this->getAltriDati("PHONE"),
            "ESIBENTE_PROVISCRIZIONE" => $this->getAltriDati("SEDEORDINE"),
            "ESIBENTE_NUMISCRIZIONE" => $this->getAltriDati("NUMEROISCRIZIONE"),
            "ESIBENTE_ORDINEISCRIZIONE" => $this->getAltriDati("ORDINEISCRIZIONE"),
            "ESIBENTE_CITY_PROGSOGG" => $this->getAltriDati("CITY_PROGSOGG"),
            "ESIBENTE_ITA_CFTELEMACO" => $this->getAltriDati("ITA_CFTELEMACO"),
            "ESIBENTE_ITA_USERTELEMACO" => $this->getAltriDati("ITA_USERTELEMACO"),
        );

        return array_merge($returnArray, array(
            'username' => $returnArray['ESIBENTE_CMSUSER'],
            'fiscale' => $returnArray['ESIBENTE_CODICEFISCALE_CFI'],
            'email' => $returnArray['ESIBENTE_EMAIL'],
            'cognome' => $returnArray['ESIBENTE_COGNOME'],
            'nome' => $returnArray['ESIBENTE_NOME'],
            'via' => $returnArray['ESIBENTE_RESIDENZAVIA'],
            'comune' => $returnArray['ESIBENTE_RESIDENZACOMUNE'],
            'cap' => $returnArray['ESIBENTE_RESIDENZACAP_CAP'],
            'provincia' => $returnArray['ESIBENTE_RESIDENZAPROVINCIA_PV'],
            'ruolo' => '',
            'denominazione' => '',
            'nazione' => '',
            'datanascita' => ''
        ));
    }

    public function getRuoloUtente() {
        if (!function_exists('wp_get_current_user')) {
            global $current_user;
            get_currentuserinfo();
        } else {
            $current_user = wp_get_current_user();
        }

        $role = false;

        if (is_user_logged_in()) {
            if (isset($current_user->roles) && count($current_user->roles)) {
                $role = $current_user->roles[0];
            } else {
                $userRole = ($current_user->data->wp_capabilities);
                $role = key($userRole);
                unset($userRole);
            }
        }

        return $role;
    }

    public function getCurrentPageID() {
        return get_the_ID() ?: $_POST['page_id'] ?: url_to_postid(wp_get_referer());
    }

    public function addJs($path, $blocco = null) {
        ?>
        <script type="text/javascript" src="<?php echo $path; ?>"></script>
        <?php
    }

    public function addCSS($path, $blocco = null) {
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>"/>        
        <?php
    }

    public function addCSSPrint($path, $blocco = null) {
        ?>
        <link rel="stylesheet" media="print" type="text/css" href="<?php echo $path; ?>"/>        
        <?php
    }

    public function getSMTPInfo() {
        // Don't configure for SMTP if no host is provided.
        /*  $SMPTInfo = array();
          if (empty(get_option('host'))) {
          return false;
          } */
        $optionSMTPInfo = get_option('c2c_configure_smtp');
        $SMPTInfo['from_err'] = $optionSMTPInfo['from_email'];
        $SMPTInfo['name_err'] = $optionSMTPInfo['from_name'];
        $SMPTInfo['SMTP_host'] = $optionSMTPInfo['host'];
        $SMPTInfo['SMTP_port'] = $optionSMTPInfo['port'];
        $SMPTInfo['SMTP_username'] = $optionSMTPInfo['smtp_user'];
        $SMPTInfo['SMTP_password'] = $optionSMTPInfo['smtp_pass'];
        if ($optionSMTPInfo['smtp_secure'] != '') {
            $SMPTInfo['SMTP_secure'] = $optionSMTPInfo['smtp_secure'];
        }
        return $SMPTInfo;
    }

    public function getSiteAdminMailAddress() {
//        global $wpdb;
//        mysql_select_db(DB_NAME);
//        $query_mailError = 'SELECT meta_value FROM '. $wpdb->base_prefix . 'sitemeta WHERE meta_key="admin_email"';
//        $ris_query_mailError = mysql_query($query_mailError) or die(mysql_error());
//        while ($mailError = mysql_fetch_assoc($ris_query_mailError)) {
//            $mail_destinatario = $mailError['meta_value'];
//        }
//        return $mail_destinatario;
        global $wpdb, $wp_version;

        $db_info = array(
            'table' => $wpdb->base_prefix . 'sitemeta',
            'field_key' => 'meta_key',
            'field_value' => 'meta_value'
        );

        if (version_compare($wp_version, '4.0.0') >= 0) {
            $db_info = array(
                'table' => $wpdb->base_prefix . 'options',
                'field_key' => 'option_name',
                'field_value' => 'option_value'
            );
        }

        $query_mailError = 'SELECT ' . $db_info['field_value'] . ' FROM ' . $db_info['table'] . ' WHERE ' . $db_info['field_key'] . ' = "admin_email"';
        $mail_destinatario = $wpdb->get_var($query_mailError, 0);
        return $mail_destinatario;
    }

    public function autenticato() {
        return is_user_logged_in();
    }

    public function getUserInfo($info = '') {
        /*
         * Set wpdb
         */
        global $wpdb;
        $wpdb->select(DB_NAME);

        switch ($info) {
            case 'fiscale':
                return get_cimyFieldValue($this->getUserID(), 'FISCALE');
                break;

            case 'ruolo':
                switch ($this->getRuoloUtente()) {
                    case 'administrator':
                    case 'ced':
                        return 'amministra';
                        break;

                    default:
                        return 'altro';
                        break;
                }
                break;

            case 'telemaco':
                return get_cimyFieldValue($this->getUserID(), 'ITA_USERTELEMACO');

            case 'cftelemaco':
                return get_cimyFieldValue($this->getUserID(), 'ITA_CFTELEMACO');

            default:
                break;
        }
        return '';
    }

    public function setUserInfo($info, $value) {
        switch ($info) {
            case 'telemaco':
                return set_cimyFieldValue($this->getUserID(), 'ITA_USERTELEMACO', $value);
            case 'cftelemaco':
                return set_cimyFieldValue($this->getUserID(), 'ITA_CFTELEMACO', $value);
        }
        return false;
    }

    public function getRequestGet($key = null) {
        if ($key === null) {
            return stripslashes_deep($_GET);
        } else {
            return stripslashes_deep($_GET[$key]);
        }
    }

    public function getRequestPost($key = null) {
        if ($key === null) {
            return stripslashes_deep($_POST);
        } else {
            return stripslashes_deep($_POST[$key]);
        }
    }

    public function getRequestCookie($key = null) {
        if ($key === null) {
            return stripslashes_deep($_COOKIE);
        } else {
            return stripslashes_deep($_COOKIE[$key]);
        }
    }

    public function getRequest($key = null) {
        if ($key === null) {
            return stripslashes_deep($_REQUEST);
        } else {
            return stripslashes_deep($_REQUEST[$key]);
        }
    }

    public function getLanguage() {
        return get_locale();
    }

    public function loadTranslation($domain, $dir) {
        load_plugin_textdomain($domain, false, $dir);
    }

    public function translate($string, $domain) {
        return utf8_decode(__(utf8_encode($string), $domain));
    }

    public function translatePlural($string, $stringPlural, $n, $domain) {
        return utf8_decode(_n(utf8_encode($string), utf8_encode($stringPlural), $n, $domain));
    }

    public function translateContext($string, $context, $domain) {
        return utf8_decode(_x(utf8_encode($string), utf8_encode($context), $domain));
    }

    public function addJsScripts($blocco = null) {
        
    }

    public function getUtenteFromCodFis($codiceFiscale) {
        $users = get_cimyFieldValue(false, 'FISCALE', $codiceFiscale);
        return count($users) ? $users[0]['user_login'] : false;
    }

    public function getDatiDaUtente($username) {
        $user = get_user_by('login', $username);
        if (!$user) {
            return false;
        }

        return array(
            'username' => $username,
            'fiscale' => get_cimyFieldValue($user->ID, 'FISCALE'),
            'email' => $user->data->user_email,
            'cognome' => get_user_meta($user->ID, 'last_name', true),
            'nome' => get_user_meta($user->ID, 'first_name', true),
            'via' => get_cimyFieldValue($user->ID, 'INDIRIZZO'),
            'comune' => get_cimyFieldValue($user->ID, 'COMUNE'),
            'cap' => get_cimyFieldValue($user->ID, 'CAP'),
            'provincia' => get_cimyFieldValue($user->ID, 'PROVINCIA'),
            'usertelemaco' => get_cimyFieldValue($user->ID, 'ITA_USERTELEMACO'),
            'sedeordine' => get_cimyFieldValue($user->ID, 'SEDEORDINE'),
            'ordineiscrizione' => get_cimyFieldValue($user->ID, 'ORDINEISCRIZIONE'),
            'numeroiscrizione' => get_cimyFieldValue($user->ID, 'NUMEROISCRIZIONE'),
            'telephone' => get_cimyFieldValue($user->ID, 'TELEPHONE'),
            'phone' => get_cimyFieldValue($user->ID, 'PHONE'),
            'cfstarweb' => get_cimyFieldValue($user->ID, 'ITA_CFTELEMACO'),
            'ruolo' => '',
            'denominazione' => '',
            'nazione' => '',
            'datanascita' => ''
        );
    }

}
