<?php

/**
 * Parametri generali di Cityware
 *
 * @author Massimo Biagioli
 */
class cwbParGen {

    const CITYWARE_PREFIX = 'CITYWARE';

    // --- PARAMETRI CITYWARE ---------------

    public static function setAnnoContabile($annoContabile) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_anno_contabile', $annoContabile);
    }

    public static function getAnnoContabile() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_anno_contabile');
    }
    
    public static function setRuolo($kruolo){
        self::setSessionVar(self::CITYWARE_PREFIX . '_ruolo', $kruolo);
    }
    
    public static function getRuolo(){
        return self::getSessionVar(self::CITYWARE_PREFIX . '_ruolo');
    }

    public static function setBorClient($cliente) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_cliente', $cliente);
    }

    public static function getBorClient() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_cliente');
    }

    public static function setBorEnti($enti) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_enti', $enti);
    }

    public static function getBorEnti() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_enti');
    }

    public static function getBorEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_ente');
//        $enti = self::getBorEnti();
//        return $enti[0];
    }

    public static function setBorEnte($btaLocal) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_ente', $btaLocal);
    }

    public static function getProgEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_progEnte');
    }

    public static function setProgEnte($progEnte) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_progEnte', $progEnte);
    }

    public static function getCodente() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_progCodente');
    }

    public static function setCodente($codente) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_progCodente', $codente);
    }

    public static function getDesente() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_progDesente');
    }

    public static function setDesente($desente) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_progDesente', $desente);
    }

    public static function getUtente() {
        return cwbParGen::getSessionVar('nomeUtente');
    }

    public static function getIdUtente() {
        return cwbParGen::getSessionVar('idUtente');
    }

    public static function setCodlocalEnte($codLocal) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_codlocalEnte', $codLocal);
    }

    public static function getCodlocalEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_codlocalEnte');
    }

    public static function setCodnazproEnte($codnazpro) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_codnazproEnte', $codnazpro);
    }

    public static function getCodnazproEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_codnazproEnte');
    }

    public static function getCodCatastEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_codCatasto');
    }

    public function setCodCatastEnte($codCatasto) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_codCatasto', $codCatasto);
    }

    public static function setCodAoo($codaoo) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_codaoo', $codaoo);
    }

    public static function getCodAoo() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_codaoo');
    }

    public static function setDesAoo($desaoo) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_desaoo', $desaoo);
    }

    public static function getDesAoo() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_desaoo');
    }

    public static function setModelloOrganizzativo($modorg) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_modorg', $modorg);
    }

    public static function getModelloOrganizzativo() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_modorg');
    }
    
    public static function getBtaLocal() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_local');
    }

    public static function setBtaLocal($local) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_local', $local);
    }
    
    // --- WRAPPER SESSIONE ---------------

    /**
     * Imposta una variabile in sessione
     * @param string $key Chiave
     * @param string $value Valore
     */
    public static function setSessionVar($key, $value) {
        App::$utente->setKey($key, $value);
    }

    /**
     * Legge una variabile dalla sessione
     * @param string $key Chiave
     * @return string Valore
     */
    public static function getSessionVar($key) {
        return App::$utente->getKey($key);
    }

    /**
     * Rimuove un valore dalla sessione
     * @param string $key Chiave
     */
    public static function removeSessionVar($key) {
        App::$utente->removeKey($key);
    }

    /**
     * Imposta una variabile in sessione per la form specificata
     * @param string $form Nome form
     * @param string $key Chiave
     * @param string $value Valore
     */
    public static function setFormSessionVar($form, $key, $value) {
        $formValue = App::$utente->getKey($form);
        if ($formValue == null) {
            $formValue = array();
        }
        $formValue[$key] = $value;
        App::$utente->setKey($form, $formValue);
    }

    /**
     * Legge una variabile dalla sessione per la form specificata
     * @param string $form Nome form
     * @param string $key Chiave
     * @return string Valore se chiave trovata, altrimenti ''
     */
    public static function getFormSessionVar($form, $key) {
        $formValue = App::$utente->getKey($form);
        return ($formValue == null ? '' : (isset($formValue[$key]) ? $formValue[$key] : null));
    }

    /**
     * Rimuove un valore dalla sessione per la form specificata
     * @param string $form Nome form
     * @param string $key Chiave
     */
    public static function removeFormSessionVar($form, $key) {
        $formValue = App::$utente->getKey($form);
        if ($formValue != null) {
            unset($formValue[$key]);
            App::$utente->setKey($form, $formValue);
        }
    }

    /**
     * Rimuove tutti i valori dalla sessione per la form specificata
     * @param string $form Nome form
     */
    public static function removeFormSessionVars($form) {
        App::$utente->removeKey($form);
    }

    /**
     * 
     * @param string $model
     */
    public static function addOpenDetailFlag($alias, $model) {
        $openDetails = App::$utente->getKey('openDetails');
        if (!isSet($openDetails)) {
            $openDetails = array();
        }
        $openDetails[$model . '|' . $alias] = array(
            'model' => $model,
            'alias' => $alias
        );
        App::$utente->setKey('openDetails', $openDetails);
    }

    public static function removeOpenDetailFlag($alias, $model) {
        $openDetails = App::$utente->getKey('openDetails');
        if (!isSet($openDetails)) {
            $openDetails = array();
        }
        if (isSet($openDetails[$model . '|' . $alias])) {
            unset($openDetails[$model . '|' . $alias]);
            App::$utente->setKey('openDetails', $openDetails);
        }
    }

    public static function isOpenDetailFlag($alias, $model) {
        $openDetails = App::$utente->getKey('openDetails');
        return(isSet($openDetails[$model . '|' . $alias]));
    }

    public static function getOpenDetailFlags() {
        $openDetails = App::$utente->getKey('openDetails');
        return (is_array($openDetails) ? $openDetails : array());
    }

    public static function setDataElaborazione($data) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_dataSessione', $data);
    }

    public static function getDataElaborazione() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_dataSessione');
    }

    /*
     * Questa variabile restituisce il campo di BOR_UTENTI.NOMEUTE
     * //aggiunto il 10-01-2019 da SilviaRivi
     */

    public static function getNomeUte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_nomeUte');
    }

    public static function setNomeUte($nomeUte) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_nomeUte', $nomeUte);
    }

    public static function getPIVAEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_pIVAEnte');
    }

    public static function setPIVAEnte($pIVAEnte) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_pIVAEnte', $pIVAEnte);
    }

    public static function getCFEnte() {
        return self::getSessionVar(self::CITYWARE_PREFIX . '_cFEnte');
    }

    public static function setCFEnte($cFEnte) {
        self::setSessionVar(self::CITYWARE_PREFIX . '_cFEnte', $cFEnte);
    }

}
