<?php

require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';

/**
 * Libreria per la gestione dello scanner
 *
 * @author l.pergolini
 */
class itaScannerClient {

    private $smartAgent;
    private $errorCode;
    private $errorDescription;
    private $provider;
    private $smartagent_protocol;
    private $smartagent_forcePdf;
    private $smartagent_color;
    private $smartagent_quality;
    private $smartagent_forceClose;
    private $smartagent_showUi;

    public function __construct() {
        $this->smartAgent = new SmartAgent();
    }

    /**
     * Effettua la scansiono di un documento 
     * @param array $returnData Dati di ritorno
     *      - returnForm
     *      - returnId
     *      - returnEvent
     * @param int $protocol 0 WIA  1 twain 2 ISIS OPZIONALE
     * @param int $forcePdf Il documento scannerizzato deve essere un pdf OPZIONALE
     * @param int $color 0=Bianco e nero , 1=colori, 2=scala di grigi, default=0 OPZIONALE
     * @param int $quality 1-100=qualità immagine, default=50 OPZIONALE
     * @param int $forceClose effettua la chiusura della finestra in modo automatico OPZIONALE
     * @parma int $shouUi visualizza la ui del driver OPZIONALE
     */
    public function scan($returnData, $protocol = NULL, $forcePdf = NULL, $color = NULL, $quality = NULL, $forceClose = NULL, $showUi = NULL) {
        $this->setError(0, '');
        //unset($this->smartagent_protocol);

        if ($this->smartAgent->isEnabled()) {
            $this->setPersonalSetting($protocol, $forcePdf, $color, $quality, $forceClose, $showUi);
            $params["forcePdf"] = $this->smartagent_forcePdf;
            $params["color"] = $this->smartagent_color;
            $params['quality'] = $this->smartagent_quality;
            $params['forceClose'] = $this->smartagent_forceClose;
            $params['showUi'] = $this->smartagent_showUi;
            switch ($this->smartagent_protocol) {

                case 0; //WIA         
                    $this->smartAgent->wiaScan($params, $returnData);
                    break;
                case 1; //TWAIN
                    $this->smartAgent->twainScan($params, $returnData);
                    break;
                case 2; //ISIS
                    $this->smartAgent->isisScan($params, $returnData);
                    break;
            }
        } else {
            $this->setError(-1, 'Smartagent non configurato');
        }
    }

    private function setPersonalSetting($protocol, $forcePdf, $color, $quality, $forceClose, $showUi) {

        if ($protocol !== null && ($protocol == 0 || $protocol == 1 || $protocol ==2 )) {
            $this->smartagent_protocol = $protocol;
        }
        //precondition boolean 
        if ($forcePdf !== null && ($forcePdf == 1 || $forcePdf == 0)) {
            $this->smartagent_forcePdf = $forcePdf;
        }
        // 0=Bianco e nero , 1=colori, 2=scala di grigi, default=0
        if ($color !== null && ($color == 0 || $color == 1 || $color == 2)) {
            $this->smartagent_color = $color;
        }
        // 1-100=qualità immagine, default=50
        if ($quality !== null && ($quality >= 1 & $quality <= 100)) {
            $this->smartagent_quality = $quality;
        }
        //effettua la chiusura della finestra in modo automatico
        if ($forceClose !== null && ($forceClose == 1 || $forceClose == 0)) {
            $this->smartagent_forceClose = $forceClose;
        }
        //visualizza la ui del driver
        if ($showUi !== null && ($showUi == 1 || $showUi == 0)) {
            $this->smartagent_showUi = $showUi;
        }
    }

    private function setError($errCode, $errDesc) {
        $this->errorCode = $errCode;
        $this->errorDescription = $errDesc;
    }

    public function setParameters($parameters = array()) {
        if(isSet($parameters['provider'])) $this->provider = $parameters['provider'];
        if(isSet($parameters['smartagent_protocol'])) $this->smartagent_protocol = $parameters['smartagent_protocol'];
        if(isSet($parameters['smartagent_forcePdf'])) $this->smartagent_forcePdf = $parameters['smartagent_forcePdf'];
        if(isSet($parameters['smartagent_color'])) $this->smartagent_color = $parameters['smartagent_color'];
        if(isSet($parameters['smartagent_quality'])) $this->smartagent_quality = $parameters['smartagent_quality'];
        if(isSet($parameters['smartagent_forceClose'])) $this->smartagent_forceClose = $parameters['smartagent_forceClose'];
        if(isSet($parameters['smartagent_showUi'])) $this->smartagent_showUi = $parameters['smartagent_showUi'];
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }

    public function setParametersFromJsonString($json) {
        try {
            $parameters = json_decode($json, true);
            if (is_array($parameters)) {
                $this->setParameters($parameters);
            } else {
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getProvider() {
        return $this->provider;
    }

    public function getSmartagent_protocol() {
        return $this->smartagent_protocol;
    }

    public function getSmartagent_forcePdf() {
        return $this->smartagent_forcePdf;
    }

    public function getSmartagent_color() {
        return $this->smartagent_color;
    }

    public function getSmartagent_quality() {
        return $this->smartagent_quality;
    }

    public function getSmartagent_forceClose() {
        return $this->smartagent_forceClose;
    }

    public function setProvider($provider) {
        $this->provider = $provider;
    }

    public function setSmartagent_protocol($smartagent_protocol) {
        $this->smartagent_protocol = $smartagent_protocol;
    }

    public function setSmartagent_forcePdf($smartagent_forcePdf) {
        $this->smartagent_forcePdf = $smartagent_forcePdf;
    }

    public function setSmartagent_color($smartagent_color) {
        $this->smartagent_color = $smartagent_color;
    }

    public function setSmartagent_quality($smartagent_quality) {
        $this->smartagent_quality = $smartagent_quality;
    }

    public function setSmartagent_forceClose($smartagent_forceClose) {
        $this->smartagent_forceClose = $smartagent_forceClose;
    }

}

?>