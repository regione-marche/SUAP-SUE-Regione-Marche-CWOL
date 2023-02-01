<?php

include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/CityFee/cwsLibVariabili.class.php';

/**
 * Classe di utilità per gestione parametri email
 */
class cwbBgeMailParamsLib {
    
    /**
     * Istanzia libreria specifica per gestione variabili
     * @param string $area Area applicativa Cityware
     * @return \cwfLibVariabili
     */
    public function instanceLibVariabili($area) {                
        switch (strtoupper($area)) {
            case 'F':
                return new cwfLibVariabili();                
            case 'S':
                return new cwsLibVariabili();                
            default:                
                return false;                
        }        
    }    
    
    public function leggiRowAccount($rowId) {
        $ITALWEB = ItaDB::DBOpen('ITALWEB');
        $sql = "SELECT MAIL_ACCOUNT.* FROM MAIL_ACCOUNT WHERE ROWID=$rowId";
        return ItaDB::DBSQLSelect($ITALWEB, $sql, false);
    }
    
    /**
     * Restituisce oggetto mailbox per spedizione mail
     * @param array mailParams Parametri email
     * @return array Esito
     *      0 = Nessun errore
     *      1 = Modalità di reperimento email non valida
     */
    public function getMailBox($mailParams, $modRepMail = null) {
        $toReturn = array();
        $toReturn['ESITO'] = 0;
        $toReturn['MESSAGGIO'] = '';
        
        // Controlla modalità reperimento email in funzione delle priorità        
        if ($modRepMail === null) {
            $modRepMail = $this->getModRepMail($mailParams); 
        }
        if ($modRepMail === false) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Nessuna modalità di reperimento mail impostata risulta valida";
        }
        
        switch ($modRepMail) {
            case 1:     // Utente
                $emlMailBox = emlMailBox::getUserAccountInstance();
                if (!$emlMailBox) {                    
                    $errMsg = "Utente non configurato per invio mail";                    
                }     
                break;
            case 2:     // Account
                $emlMailBox = emlMailBox::getInstance($mailParams['ROW_ACCOUNT']['MAILADDR']);    
                if (!$emlMailBox) {                    
                    $errMsg = "Account di posta elettronica non configurato";                    
                }     
                break;
            case 3:     // Organigramma
                $emlMailBox = false;    // NON GESTITO
                $errMsg = "Utente su organigramma non configurato per invio mail";                
        }
        
        if (!$emlMailBox) {
            $toReturn['ESITO'] = 2;
            $toReturn['MESSAGGIO'] = $errMsg;
            return $toReturn;
        }         
        
        $toReturn['DATA'] = $emlMailBox;
        
        return $toReturn;
    }
    
    private function getModRepMail($mailParams) {
        $index = 1;
        if (!$this->checkModRepMail($mailParams, $index)) {
            $index = 2;
            if (!$this->checkModRepMail($mailParams, $index)) {
                $index = 3;
                if (!$this->checkModRepMail($mailParams, $index)) {
                    return false;
                }            
            }            
        }
        return $mailParams["MOD_REPMAIL$index"];
    }
    
    private function checkModRepMail($mailParams, $index) {
        $modRepMail = $mailParams["MOD_REPMAIL$index"];
        return ($modRepMail != 0);
    }
    
}
