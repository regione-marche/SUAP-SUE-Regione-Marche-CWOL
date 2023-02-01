<?php

require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';

/**
 * Libreria per firma grafometrica Namirial
 *
 * @author m.biagioli
 */
class itaFirmaGrafometricaNamirial {
    
    private $smartAgent;
    private $errorCode;
    private $errorDescription;
    private $params;
    
    public function __construct() {
        $this->smartAgent = new SmartAgent();
    }
    
    /**
     * Effettua firma grafometrica di un documento
     * @param string $pathDocToSign Path del documento da firmare     
     * @param string device Dispositivo
     * @param string certificate Alias certificato
     * @param array $returnData Dati di ritorno
     *      - returnForm
     *      - returnId
     *      - returnEvent
     */
    public function sign($pathDocToSign, $device, $certificate, $returnData) {        
        $this->setError(0, '');
        
        if ($device) {
            $this->params['device'] = $device;            
        }
        $this->params['certificate'] = $certificate;
        
        if ($this->smartAgent->isEnabled()) {            
            $filecontent = file_get_contents($pathDocToSign);
            $source = base64_encode($filecontent);                        
            $this->smartAgent->namirialSignature($source, $this->params, $returnData);
        } else {
            $this->setError(-1, 'Smartagent non configurato');
        }
    }
    
    /**
     * Verifica firma documento
     * @param string $pathDocToVerify Percorso del documento da verificare
     * @param array $returnData Dati di ritorno
     *      - returnForm
     *      - returnId
     *      - returnEvent
     */
    public function verify($pathDocToVerify, $returnData) {        
        $this->setError(0, '');
        if ($this->smartAgent->isEnabled()) {            
            $filecontent = file_get_contents($pathDocToVerify);
            $source = base64_encode($filecontent);
            $this->smartAgent->namirialVerifySignature($source, $returnData);
        } else {
            $this->setError(-1, 'Smartagent non configurato');            
        }
    }
    
    private function setError($errCode, $errDesc) {
        $this->errorCode = $errCode;
        $this->errorDescription = $errDesc;
    }
    
    /** 
     *  Parametri specifici per la libreria grafometrica:
     *      - certificate
     *      - device
     *      - biometricData
     *      - noPdfSignInfo
     *      - makePdfOriginal
     *      - saveinSameFolder
     *      - forceOverwrite
     */
    public function setParameters($params = array()) {
        $this->params = $params;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }
    
}

?>