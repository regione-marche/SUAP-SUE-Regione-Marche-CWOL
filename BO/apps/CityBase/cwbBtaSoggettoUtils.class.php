<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Utils per il soggetto 
 *
 * @author l.pergolini
 */
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggettoUtilsInterface.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

class cwbBtaSoggettoUtils implements cwbBtaSoggettoUtilsInterface {

    private $clientUtils;

    const OMNIS_TYPE = 'omnis';

    public function __construct($type = null) {
        $this->clientUtils = null;
        //defult utilizzo di omnis 
        if ($type == null || self::OMNIS_TYPE === $type) {
            include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
            $this->clientUtils = new itaOmnisClient();
        }
    }

    public function calcolaCodFisc($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BGE_PHP_CODE', 'getCalc_cfis', $methodArgs, 'CITYWARE', false);
    }

    public function repDatidaCodFisc($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BGE_PHP_CODE', 'getRep_dati_da_cfisc', $methodArgs, 'CITYWARE', false);
    }
    
    public function calcolaCodFiscPHP(){
        //TODO
    }
    
    public function repDatidaCodFiscPHP($cf){
        if(strlen($cf) != 16){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Lunghezza del codice fiscale passato errata');
        }
        
        $cf = strtoupper($cf);
        
        if(!preg_match('/^[A-Z]{6}([0-9]{2})([A-Z])([0-9]{2})([A-Z][0-9]{3})[A-Z]$/', $cf, $matches)){
            preg_match('/^[A-Z]{6}([A-Z0-9]{2})([A-Z])([A-Z0-9]{2})([A-Z][A-Z0-9]{3})[A-Z]$/', $cf, $matches);
            
            $search = array('L','M','N','P','Q','R','S','T','U','V');
            $replace = array(0,  1,  2,  3,  4,  5,  6,  7,  8,  9);
            
            $matches[1] = str_replace($search, $replace, $matches[1]);
            $matches[3] = str_replace($search, $replace, $matches[3]);
            $matches[4] = substr($matches[4], 0, 1) . str_replace($search, $replace, substr($matches[4], 1, 3));
        }
        
        $return = array();
        $return['ANNO'] = $matches[1];
        switch(strtoupper($matches[2])){
            case 'A': $return['MESE'] = '01'; break;
            case 'B': $return['MESE'] = '02'; break;
            case 'C': $return['MESE'] = '03'; break;
            case 'D': $return['MESE'] = '04'; break;
            case 'E': $return['MESE'] = '05'; break;
            case 'H': $return['MESE'] = '06'; break;
            case 'L': $return['MESE'] = '07'; break;
            case 'M': $return['MESE'] = '08'; break;
            case 'P': $return['MESE'] = '09'; break;
            case 'R': $return['MESE'] = '10'; break;
            case 'S': $return['MESE'] = '11'; break;
            case 'T': $return['MESE'] = '12'; break;
            default:  $return['MESE'] = null;
        }
        if($matches[3] >= 40) {
            $return['GIORNO'] = str_pad(($matches[3]-40), 2, '0', STR_PAD_LEFT);
            $return['SESSO'] = 'F';
        } else {
            $return['GIORNO'] = $matches[3];
            $return['SESSO'] = 'M';
        }
        
        $dateNow = new DateTime();
        $dateCF = DateTime::createFromFormat('Ymd', ('20'.$return['ANNO'].$return['MESE'].$return['GIORNO']));
        
        if($dateNow >= $dateCF) {
            $return['ANNO'] = '20'.$return['ANNO'];
        } else {
            $return['ANNO'] = '19'.$return['ANNO'];
        }
        
        $libDB = new cwbLibDB_GENERIC();
        
        $local = $libDB->leggiGeneric('BTA_LOCAL', array('CODBELFI'=>$matches[4]), false);
        $return['COMUNE'] = array();
        $return['COMUNE']['COD_CAT'] = $matches[3];
        $return['COMUNE']['COD_ISTAT'] = (!empty($local['CODNAZPRO']) ? str_pad($local['ISTNAZPRO'], 3, '0', STR_PAD_LEFT).str_pad($local['ISTLOCAL'], 3, '0', STR_PAD_LEFT) : null);
        $return['COMUNE']['DESCRIZIONE'] = (!empty($local['CODNAZPRO']) ? $local['DESLOCAL'] : null);
        
        return $return;
    }

    public function getErrorCode() {
        return $this->clientUtils->getErrcode();
    }

    public function getErrorMessage() {
        return $this->clientUtils->getErrMessage();
    }

    public function resetStatusError() {
        $this->clientUtils->setErrcode(0);
        $this->clientUtils->setErrMessage('');
    }

    public function ctl_cfis($methodArgs = array()) {
        $this->resetStatusError();
        return $this->clientUtils->callExecute('OBJ_BGE_PHP_CODE', 'ctl_cfis', $methodArgs, 'CITYWARE', false);
    }

}
