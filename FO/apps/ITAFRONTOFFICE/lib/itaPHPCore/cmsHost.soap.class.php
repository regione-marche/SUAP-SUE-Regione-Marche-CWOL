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
class cmsHost_soap implements cmsHostInterface {

    private $ITW_DB;
    private $datiUtente;

    private function getITWDB() {
        if ($this->ITW_DB) {
            return $this->ITW_DB;
        }

        $this->ITW_DB = ItaDB::DBOpen('ITW', frontOfficeApp::getEnte());
        return $this->ITW_DB;
    }

    public function getUserName() {
        return wsSportello::$userName;
    }

    public function getPassword() {
        
    }

    public function getUserID() {
        return wsSportello::$userCode;
    }

    public function getSiteName() {
        return "soap";
    }

    public function getSiteHomepageURI() {
        return '';
    }

    public function getAltriDati($dato = '') {
        return '';
    }

    public function getCodFisFromUtente($nomeUtente = false) {
        return 'FISCALE_WIP';
    }
    
    public function setDatiUtente($datiUtente) {
        $this->datiUtente = $datiUtente;
    }

    public function getDatiUtente() {
        if ( $this->datiUtente ) {
            return $this->datiUtente;
        }

        $utenti_rec = ItaDB::DBSQLSelect($this->getITWDB(), "SELECT * FROM UTENTI WHERE UTECOD = '" . wsSportello::$userCode . "'", false);
        $richut_rec = ItaDB::DBSQLSelect($this->getITWDB(), "SELECT * FROM RICHUT WHERE RICCOD = '" . wsSportello::$userCode . "'", false);

        $returnArray = array(
            "ESIBENTE_CMSUSER" => wsSportello::$userName,
            "ESIBENTE_NOME" => $richut_rec['RICNOM'],
            "ESIBENTE_COGNOME" => $richut_rec['RICCOG'],
            "ESIBENTE_PEC" => '',
            "ESIBENTE_EMAIL" => $richut_rec['RICMAI'],
            "ESIBENTE_CODICEFISCALE_CFI" => $utenti_rec['UTEFIS'],
            "ESIBENTE_RESIDENZAVIA" => $richut_rec['RICVIA'],
            "ESIBENTE_RESIDENZACIVICO" => '',
            "ESIBENTE_RESIDENZACOMUNE" => $richut_rec['RICCOM'],
            "ESIBENTE_RESIDENZACAP_CAP" => $richut_rec['RICCAP'],
            "ESIBENTE_RESIDENZAPROVINCIA_PV" => $richut_rec['RICPRO'],
            "ESIBENTE_TELEFONO" => '',
            "ESIBENTE_PROVISCRIZIONE" => '',
            "ESIBENTE_NUMISCRIZIONE" => '',
            "ESIBENTE_CITY_PROGSOGG" => '',
            "ESIBENTE_ITA_CFTELEMACO" => ''
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

        return $role;
    }

    public function getCurrentPageID() {
        
    }

    public function addJs($path, $blocco = null) {
        
    }

    public function addCSS($path, $blocco = null) {
        
    }

    public function addCSSPrint($path, $blocco = null) {
        
    }

    public function getSMTPInfo() {
//        $optionSMTPInfo = get_option('c2c_configure_smtp');
//        $SMPTInfo['from_err'] = $optionSMTPInfo['from_email'];
//        $SMPTInfo['name_err'] = $optionSMTPInfo['from_name'];
//        $SMPTInfo['SMTP_host'] = $optionSMTPInfo['host'];
//        $SMPTInfo['SMTP_port'] = $optionSMTPInfo['port'];
//        $SMPTInfo['SMTP_username'] = $optionSMTPInfo['smtp_user'];
//        $SMPTInfo['SMTP_password'] = $optionSMTPInfo['smtp_pass'];
//        if ($optionSMTPInfo['smtp_secure'] != '') {
//            $SMPTInfo['SMTP_secure'] = $optionSMTPInfo['smtp_secure'];
//        }
        return $SMPTInfo;
    }

    public function getSiteAdminMailAddress() {
//
//        global $wpdb, $wp_version;
//
//        $db_info = array(
//            'table' => $wpdb->base_prefix . 'sitemeta',
//            'field_key' => 'meta_key',
//            'field_value' => 'meta_value'
//        );
//
//        if (version_compare($wp_version, '4.0.0') >= 0) {
//            $db_info = array(
//                'table' => $wpdb->base_prefix . 'options',
//                'field_key' => 'option_name',
//                'field_value' => 'option_value'
//            );
//        }
//
//        $query_mailError = 'SELECT ' . $db_info['field_value'] . ' FROM ' . $db_info['table'] . ' WHERE ' . $db_info['field_key'] . ' = "admin_email"';
//        $mail_destinatario = $wpdb->get_var($query_mailError, 0);
//        return $mail_destinatario;
    }

    public function autenticato() {
        return true;
    }

    public function getUserInfo($info = '') {
//        /*
//         * Set wpdb
//         */
//        global $wpdb;
//        $wpdb->select(DB_NAME);
//
//        switch ($info) {
//            case 'fiscale':
//                return get_cimyFieldValue($this->getUserID(), 'FISCALE');
//                break;
//
//            case 'ruolo':
//                switch ($this->getRuoloUtente()) {
//                    case 'administrator':
//                    case 'ced':
//                        return 'amministra';
//                        break;
//
//                    default:
//                        return 'altro';
//                        break;
//                }
//                break;
//
//            case 'telemaco':
//                return get_cimyFieldValue($this->getUserID(), 'ITA_USERTELEMACO');
//
//            case 'cftelemaco':
//                return get_cimyFieldValue($this->getUserID(), 'ITA_CFTELEMACO');
//
//            default:
//                break;
//        }
        return '';
    }

    public function setUserInfo($info, $value) {
//        switch ($info) {
//            case 'telemaco':
//                return set_cimyFieldValue($this->getUserID(), 'ITA_USERTELEMACO', $value);
//            case 'cftelemaco':
//                return set_cimyFieldValue($this->getUserID(), 'ITA_CFTELEMACO', $value);
//        }
//        return false;
    }

    public function getRequestGet($key = null) {
//        if ($key === null) {
//            return stripslashes_deep($_GET);
//        } else {
//            return stripslashes_deep($_GET[$key]);
//        }
    }

    public function getRequestPost($key = null) {
//        if ($key === null) {
//            return stripslashes_deep($_POST);
//        } else {
//            return stripslashes_deep($_POST[$key]);
//        }
    }

    public function getRequestCookie($key = null) {
//        if ($key === null) {
//            return stripslashes_deep($_COOKIE);
//        } else {
//            return stripslashes_deep($_COOKIE[$key]);
//        }
    }

    public function getRequest($key = null) {
//        if ($key === null) {
//            return stripslashes_deep($_REQUEST);
//        } else {
//            return stripslashes_deep($_REQUEST[$key]);
//        }
    }

    public function getLanguage() {
        //return get_locale();
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
        
    }

    public function getDatiDaUtente($username) {
        
    }

}
