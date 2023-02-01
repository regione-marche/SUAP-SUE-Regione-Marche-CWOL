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
class cmsHost_cli implements cmsHostInterface {

    public $getdb;

    public function getUserName() {
        return null;
    }

    public function getPassword() {
        
    }

    public function getUserID() {
        return null;
    }

    public function getSiteName() {
        return null;
    }

    public function getSiteHomepageURI() {
        return null;
    }

    public function getAltriDati($dato = "") {
        return null;
    }

    public function getCodFisFromUtente($nomeUtente) {
        return null;
    }
    
    public function setDatiUtente($datiUtente) {
        
    }

    public function getDatiUtente() {
        return array();
    }

    public function getRuoloUtente() {
        return false;
    }

    public function getCurrentPageID() {
        return false;
    }

    public function addJs($path, $blocco = null) {
    }

    public function addCSS($path, $blocco = null) {
    }

    public function getSMTPInfo() {
        return array();
    }

    public function getSiteAdminMailAddress() {
        return null;
    }

    public function autenticato() {
        return false;
    }

    public function getUserInfo($info = '') {
        return null;
    }

    public function getRequestGet($key = null) {
        return null;
    }

    public function getRequestPost($key = null) {
        return null;
    }
    
    public function getRequestCookie($key = null) {
        return null;
    }

    public function getRequest($key = null) {
        return null;
    }

    public function addCSSPrint($path, $blocco = null) {
        
    }

    public function addJsScripts($blocco = null) {
        
    }

    public function getLanguage() {
        
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
