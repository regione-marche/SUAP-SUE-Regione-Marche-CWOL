<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaAuthenticator.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthFilters.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

/**
 * Authenticator di default di Cityware
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class cwbBaseAuthenticator extends itaAuthenticator {
    protected $params;
    protected $level;
    protected $levelDettaglio;
    protected $lib;
    protected $error;

    public function __construct($params) {
        $this->params = $params;
        if(isSet($this->params['lib'])){
            $this->setLib($this->params['lib']);
        }
        $this->authenticate();
    }

    public function isActionAllowed($actionType) {
        switch ($actionType) {
            case itaAuthenticator::ACTION_READ:
                return $this->level === 'L' || $this->level === 'G' || $this->level === 'C';
            case itaAuthenticator::ACTION_WRITE:
                return $this->level === 'G' || $this->level === 'C';
            case itaAuthenticator::ACTION_DELETE:
                return $this->level === 'C';
        }
        return $this->customActionAllowed($actionType);
    }

    public function isActionAllowedDettaglio($actionType) {
        if(!isSet($this->levelDettaglio)){
            $this->authenticateDettaglio();
        }
        
        switch ($actionType) {
            case itaAuthenticator::ACTION_READ:
                return $this->levelDettaglio === 'L' || $this->levelDettaglio === 'G' || $this->levelDettaglio === 'C';
            case itaAuthenticator::ACTION_WRITE:
                return $this->levelDettaglio === 'G' || $this->levelDettaglio === 'C';
            case itaAuthenticator::ACTION_DELETE:
                return $this->levelDettaglio === 'C';
        }
        return $this->customActionAllowedDettaglio($actionType);
    }

    /**
     * Da implementare nelle sottoclassi: serve per specificare un tipo di azione custom
     * @param int $actionType Tipo di azione custom
     */
    protected function customActionAllowed($actionType) {
        
    }
    
    protected function customActionAllowedDettaglio($actionType) {
        
    }

    protected function authenticate() {
        try{
            if (isSet($this->params['level'])) {
                $this->level = $this->params['level'];
            } else {            
//            $this->level = $this->getLib()->getAuthUtente($this->params['username'], $this->params['modulo'], $this->params['num'], false);
                $this->level = $this->getLib()->getPageLevel();

                if(empty($this->level)){
                    $this->error = $this->generateMissingAuthenticationMessage();
                }
                else{
                    $this->error = null;
                }
            }
		}
        catch(ItaException $e){
            $this->error = $e->getNativeErroreDesc();
            $this->level = false;
        }
        catch(Exception $e){
            $this->error = $e->getMessage();
            $this->level = false;
        }
    }
    
    public function authenticateDettaglio($rowDettaglio=null, $filters=null){
        try{
            if(empty($rowDettaglio) || empty($filters)){
                $this->levelDettaglio = $this->level;
            }
            else{
                $lib = $this->getLib();
                $lib->setFieldsDettaglio($filters);
                $this->levelDettaglio = $lib->getDettaglioLevel($rowDettaglio);
            
                if(empty($this->levelDettaglio)){
                    $this->error = $this->generateMissingAuthenticationMessage();
                }
                else{
                    $this->error = null;
                }
            }
        }
        catch(ItaException $e){
            $this->error = $e->getNativeErroreDesc();
            $this->levelDettaglio = false;
        }
        catch(Exception $e){
            $this->error = $e->getMessage();
            $this->levelDettaglio = false;
        }
    }

    public function missingAuthentication() {
        return empty($this->level);
    }

    protected function generateMissingAuthenticationMessage() {
        $fieldsPage = $this->lib->getFieldsPage();
        if(!empty($fieldsPage)){
            if($this->lib->getContext() !== cwbAuthFilters::SOURCE_BASE){
                $libDB = new cwbLibDB_GENERIC();
                $ftaAutute = $libDB->leggiGeneric('FTA_AUTUTE', array('CODUTE_OP'=>$this->params['username']), false);
                if($ftaAutute === false){
                    return "Utente ".$this->params['username']." non è stato definito nella tabella definizioni bilancio per utente";
                }
            }
            $msg = $this->recursiveGenerateMissingAuthenticationMessage($fieldsPage);
            return "Utente " . $this->params['username'] . " non autorizzato a ".implode(', ', $msg);
        }
        else{
            return "Utente " . $this->params['username'] . " non autorizzato a " . $this->params['modulo'] . "/" . $this->params['num'];
        }
    }
    
    protected function recursiveGenerateMissingAuthenticationMessage($array){
        $return = array();
        if(isSet($array[cwbAuthFilters::MODULO])){
            $return[] = $array[cwbAuthFilters::MODULO] . '/' . $array[cwbAuthFilters::NUMERO];
        }
                
        if(is_array($array)){
            foreach($array as $v){
                $return = array_merge($return, $this->recursiveGenerateMissingAuthenticationMessage($v));
            }
        }
        
        return $return;
    }

    public function getMissingAuthenticationMessage() {
        return $this->error;
    }

//    public function getLib() {
//        if (!$this->lib) {
//            $this->lib = new cwbAuthHelper();
//        }
//        return $this->lib;
//    }
    
    public function getLib(){
        if(!isSet($this->lib)){
            $this->lib = new cwbAuthFilters();
            $this->lib->setUser($this->params['username']);
        }
        if(!$this->lib->getFieldsPage() && !empty($this->params['modulo'])){
            $this->lib->setFieldsPage(array(
                cwbAuthFilters::MODULO=>$this->params['modulo'],
                cwbAuthFilters::NUMERO=>$this->params['num']
            ));
        }
        return $this->lib;
    }
    
    public function setLib($libFilters){
        $this->lib = $libFilters;
    }

    public function getParams() {
        return $this->params;
    }

    public function getLevel() {
        return (empty($this->level) ? null : $this->level);
    }
    
    public function getLevelDettaglio(){
        if(isSet($this->levelDettaglio)){
            return (empty($this->levelDettaglio) ? null : $this->levelDettaglio);
        }
        return $this->getLevel();
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function setLevel($level) {
        $this->level = $level;
    }
    
}
