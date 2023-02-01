<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class itaPHPPagoPA {

    private $current_url;
    private $timeout;
    private $debug_level;

    public function getCurrent_url() {
        return $this->current_url;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function getDebug_level() {
        return $this->debug_level;
    }

    public function setCurrent_url($current_url) {
        $this->current_url = $current_url;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setDebug_level($debug_level) {
        $this->debug_level = $debug_level;
    }

    /**
     * funzione per prendere token di ItaEngine
     * 
     * @param type $UserName
     * @param type $Password
     * @param type $DomainCode
     * 
     * return $token String
     */
    public function GetItaEngineContextToken($UserName, $Password, $DomainCode) {
        $parametri_call = array(
            'UserName' => $UserName,
            'Password' => $Password,
            'DomainCode' => $DomainCode
        );
        $headers = array();
        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout($this->timeout);
        $itaRestClient->setDebugLevel($this->debug_level);

        if (!$itaRestClient->get($this->current_url, $parametri_call, $headers)) {
            return $itaRestClient->getErrMessage();
        }
        return $itaRestClient->getResult();
    }

    /**
     * funzione per distruggere il token di ItaEngine
     * 
     * @param type $token
     * 
     * return true|false
     */
    public function DestroyItaEngineContextToken($token) {
        $parametri_call = array(
            'token' => $token
        );
        return $this->eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la pubblicazione di una posizione
     * 
     * @param type $token - token di accesso
     * @param type $modo - CHIAVEPENDENZA|XML
     * @param type $parametri
     * se modo = CHIAVEPENDENZA: CodTipScad, SubTipScad, ProgCitySc = '', AnnoRif = ''
     * se modo = XML: CodTipScad, SubTipScad, XML
     */
    public function pubblicaPosizione($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'XML':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'Pendenza' => base64_encode($parametri['Pendenza'])
                );
                break;

            default:
                return false;
                break;
        }
        return $this->eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per inserire un pagamento dentro la posizione
     * 
     * @param type $modo - CHIAVEPENDENZA|IUV
     * @param type $parametri
     */
    public function eseguiPagamento($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'ProgCitySca' => $parametri['ProgCitySca'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'IUV':
                $parametri_call = array(
                    'CodiceIdentificativo' => $parametri['CodiceIdentificativo'],
                    'urlReturn' => $parametri['urlReturn']
                );

                break;

            default:
                return false;
                break;
        }
        file_put_contents("/tmp/param_pre_call.txt", print_r($parametri_call, true));
        return $this->eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la generazione del bollettino
     * 
     * @param type $parametri
     */
    public function generaBollettino($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'ProgCitySca' => $parametri['ProgCitySca'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'IUV':
                $parametri_call = array(
                    'CodiceIdentificativo' => $parametri['CodiceIdentificativo']
                );
                break;

            default:
                return false;
                break;
        }
        return $this->eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la ricerca di una posizione
     * 
     * @param type $modo - CHIAVEPENDENZA|IUV
     * @param type $parametri
     */
    public function ricercaPosizioneIUV($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'ProgCitySca' => $parametri['ProgCitySca'],
                    'AnnoRif' => $parametri['AnnoRif'],
                    'NumRata' => $parametri['NumRata']
                );
                break;
            case 'IUV':
                $parametri_call = array(
                    'CodiceIdentificativo' => $parametri['CodiceIdentificativo']
                );
                break;

            default:
                return false;
                break;
        }
        return $this->eseguiChiamataRest($token, $parametri_call);
    }

    private function eseguiChiamataRest($token, $parametri) {
        file_put_contents("/tmp/log_chiamata_rest", "url: " . $this->current_url . " - PARAMETRI:\n" . print_r($parametri, true), FILE_APPEND);
        if ($token) {
            $headers = array(
                'X-ITA-TOKEN: ' . $token
            );
        }
        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout($this->timeout);
        $itaRestClient->setDebugLevel($this->debug_level);
        if (!$itaRestClient->get($this->current_url, $parametri, $headers)) {
            file_put_contents("/tmp/err_message", $itaRestClient->getErrMessage());
            file_put_contents("/tmp/err_parametri", print_r($parametri, true));
            file_put_contents("/tmp/err_headers", print_r($headers, true));
            return $itaRestClient->getErrMessage();
        }
        file_put_contents("/tmp/chiamata.txt", "URL: " . $this->current_url . "\nPARAMETRI: " . print_r($parametri, true));
        file_put_contents("/tmp/debug_rest.txt", $itaRestClient->getDebug());
        return $itaRestClient->getResult();
    }

}
