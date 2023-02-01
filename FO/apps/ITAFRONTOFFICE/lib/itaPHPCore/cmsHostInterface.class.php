<?php

interface cmsHostInterface {

    public function getUserID();

    public function getUsername();

    public function getPassword();

    public function getSiteName();

    public function getSiteHomepageURI();

    public function getAltriDati($dato = '');

    public function getCodFisFromUtente($nomeUtente = false);

    public function getUtenteFromCodFis($codiceFiscale);

    public function getDatiDaUtente($username);

    public function setDatiUtente($datiUtente);

    public function getDatiUtente();

    public function getRuoloUtente();

    public function getCurrentPageID();

    public function addJsScripts($blocco = null);

    public function addJs($path, $blocco = null);

    public function addCSS($path, $blocco = null);

    public function addCSSPrint($path, $blocco = null);

    public function getSMTPInfo();

    public function getSiteAdminMailAddress();

    public function autenticato();

    public function getUserInfo($info = '');

    public function setUserInfo($info, $value);

    public function getRequestGet($key = null);

    public function getRequestPost($key = null);

    public function getRequestCookie($key = null);

    public function getRequest($key = null);

    public function getLanguage();

    public function loadTranslation($domain, $dir);

    public function translate($string, $domain);

    public function translatePlural($string, $stringPlural, $n, $domain);

    public function translateContext($string, $context, $domain);
}
