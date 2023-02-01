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
class cmsHost_si implements cmsHostInterface {

    public function getUserName() {
        return ColorBox::$user->info->username;
    }

    public function getPassword() {
        return ColorBox::$user->info->password;
    }

    public function getSiteName() {
        $wc = AV_WebController::getInstance();
        return $wc->progetto->sito->nome;
    }

    public function getSiteHomepageURI() {

    }

    public function getAltriDati($dato = "") {
        $altro = new CB_Config('users_campi');
        $altri = unserialize($altro->valore);
        $prof = new AV_Cittadino(ColorBox::$user->info->profilo);

        foreach ($altri as $k => $v) {
            if ($dato != "" && $dato == $k) {
                if (in_array(ColorBox::$user->info->ruolo, $v[2])) {
                    $valore = $prof->getCampo($k);
                    break;
                }
            } else {
                if (in_array(ColorBox::$user->info->ruolo, $v[2])) {
                    $valore[$k] = $prof->getCampo($k);
                }
            }
        }

        return $valore;
    }

    public function getCodFisFromUtente($nomeUtente) {
        if ($nomeUtente == "") {
            return false;
        }
        $credenziali = new CB_Credenziali($nomeUtente);
        $prof = new AV_Cittadino($credenziali->profilo);
        return $prof->codiceFiscale;
    }
    
    public function setDatiUtente($datiUtente) {
        
    }

    public function getDatiUtente() {
        $credenziali = new CB_Credenziali(ColorBox::$user->info->username);
        $prof = new AV_Cittadino($credenziali->profilo);
        $returnArray = array(
            "ESIBENTE_CMSUSER" => ColorBox::$user->info->username,
            "ESIBENTE_NOME" => $prof->nome,
            "ESIBENTE_COGNOME" => $prof->cognome,
            "ESIBENTE_PEC" => ColorBox::$user->info->email,
            "ESIBENTE_EMAIL" => ColorBox::$user->info->email,
            "ESIBENTE_CODICEFISCALE_CFI" => $prof->codiceFiscale,
            "ESIBENTE_RESIDENZAVIA" => $prof->indirizzoResidenza,
            "ESIBENTE_RESIDENZACOMUNE" => $prof->comuneResidenza,
            "ESIBENTE_RESIDENZACAP" => $prof->cap,
            "ESIBENTE_RESIDENZAPROVINCIA_PV" => $prof->provincia,
            "ESIBENTE_TELEFONO" => $prof->cellulare,
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
        return ColorBox::$user->info->ruolo;
    }

    public function getCurrentPageID() {
        return;
    }
    
    public function addJs($path, $blocco = null) {
        if ($blocco->inModifica) {
            return;
        }
        $blocco->wc->progetto->pagina->addScriptEsterno('tools' . $path);                
    }

    public function addCSS($path,$blocco = null){
        if ($blocco->inModifica) {
            return;
        }
    $blocco->wc->progetto->pagina->associaCSS('tools' . $path);
    }
    
    public function getSMTPInfo() {
        // Don't configure for SMTP if no host is provided.
        $SMPTInfo = array();
        /*
         * Esempio di valorizzazione non scommentare
         *     
        $SMPTInfo['from_err'] = 'michele.moscioni@italsoft.eu';
        $SMPTInfo['name_err'] = 'Michele';
        $SMPTInfo['SMTP_host'] = 'mail.italsoft.eu';
        $SMPTInfo['SMTP_port'] = '25';
        $SMPTInfo['SMTP_username'] = 'michele.moscioni@italsoft.eu';
        $SMPTInfo['SMTP_password'] = '12345678';        
        $SMPTInfo['SMTP_secure'] = 'ssl';
        */
        return $SMPTInfo;
    }

    public function getSiteAdminMailAddress() {
        return '';
        
    }
    
     public function autenticato() {
        return ColorBox::$user->autenticato;
    }
    
    public function getUserInfo($info = '') {
        $sett_ruolo = 2;   /// LEGGERE DA PARAMETRO
        
        switch ($info) {
            case 'fiscale':
                $cittadino = new AV_Cittadino(ColorBox::$user->info->profilo);
                return $cittadino->codiceFiscale;
                break;
            case 'ruolo':
                $ruolo = $this->getRuoloUtente();
                if ($ruolo == $sett_ruolo) {
                    return 'amministra';
                } else {
                    return 'altro';
                }
                break;
            default:
                break;
        }
        return '';
    } 
    
    public function getRequestGet($key = null) {
        if ($key == null) {
            return $_GET;
        } else {
            return $_GET[$key];
        }
    }

    public function getRequestPost($key = null) {
        if ($key === null) {
            return $_POST;
        } else {
            return $_POST[$key];
        }
    }
    
    public function getRequestCookie($key = null) {
        if ($key === null) {
            return $_COOKIE;
        } else {
            return $_COOKIE[$key];
        }
    }

    public function getRequest($key = null) {
        if ($key !== null) {
            return $_REQUEST;
        } else {
            return $_REQUEST[$key];
        }
    }

    public function addCSSPrint($path, $blocco = null) {
        
    }

    public function addJsScripts($blocco = null) {
        
    }

    public function getLanguage() {
        
    }

    public function getUserID() {
        
    }

    public function loadTranslation($domain, $dir) {
        
    }

    public function setUserInfo($info, $value) {
        
    }

    public function translate($string, $domain) {
        
    }

    public function translateContext($string, $context, $domain) {
        
    }

    public function translatePlural($string, $stringPlural, $n, $domain) {
        
    }

    public function getUtenteFromCodFis($codiceFiscale) {
        
    }

    public function getDatiDaUtente($username) {
        
    }

}

?>
