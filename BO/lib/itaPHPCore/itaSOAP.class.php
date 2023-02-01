<?php
require_once ITA_LIB_PATH . '/itaPHPCore/itaSAML.class.php';

/**
 * Classe di utilità SOAP
 *
 * @author m.biagioli
 */
class itaSOAP {
    
    const WS_ID_PREFIX = 'TS-';     // Timestamp prefix
    const WS_SIG_PREFIX = 'SIG-';   // Signature 
    const WS_KI_PREFIX = 'KI-';     // KeyInfo
    
    /**
     * Crea stringa WSSecurity da inserire nel SOAP Header
     * @param array $options Opzioni
     *      - mustUnderstand    (0/1)
     *      - timestamp         (0/1)
     *      - mode              (username/saml)
     *      - username
     *          - username      
     *          - password      
     *      - saml
     *          - assertion     (stringa che contiene asserzione saml completa)
     *      - binarySecurityToken
     *          - publicCert    (Certificato pubblico)
     *          - extract       (1=Rimuove ---- BEGIN CERTIFICATE ---- ed ---- END CERTIFICATE ----)
     * @return string
     */
    public static function createWSSecurity($options = array()) {
        $options['wsID'] = uniqid(self::WS_ID_PREFIX);
        
        //$options['created'] = date('Y-m-d\TH:i:s') . ".123Z";
        //$options['expires'] = date('Y-m-d\TH:i:s', time() + 600). ".123Z";
        
        $options['created'] = gmdate('Y-m-d\TH:i:s\Z', time());
        $options['expires'] = gmdate('Y-m-d\TH:i:s\Z', time() + 300);        
        
        if (isset($options['binarySecurityToken'])) {
            if ($options['binarySecurityToken']['extract'] === 1) {
                $pubcert = explode("\n", $options['binarySecurityToken']['publicCert']);
                array_shift($pubcert);
                while (!trim(array_pop($pubcert))) {
                }
                array_walk($pubcert, 'trim');
                $pubcert = implode('', $pubcert);
                $options['binarySecurityToken']['publicCert'] = $pubcert;                            
            }
        }
        
        $wsse = self::createWSSecurityHeader($options);
        $wsse .= self::createWSSecurityTimestamp($options);
        if (!isset($options['mode'])) {
            $wsse .= self::createWSSecurityUsername($options);
        } else {
            if ($options['mode'] === 'saml') {
                $wsse .= self::createWSSecuritySaml($options);
            } else {
                $wsse .= self::createWSSecurityUsername($options);
            }
        }
        $wsse .= self::createWSSecurityBinarySecurityToken($options);
        $wsse .= self::createWSSecurityFooter();        
        return $wsse;
    }        
    
    /**
     * Effettua la firma di un messaggio SOAP
     * @param string $soapMessage Intero messaggio SOAP (Evvelope)
     * @param string $wsse Stringa wsse 
     * @param array $signCert Certificato di firma
     * @param array $references Nodi reference su cui generare il digest value
     * @param alg Algoritmo per estrarre la chiave privata dal PEM
     */
    public static function signSoapMessage($soapMessage, $wsse, $signCert, $references = array(), $alg = null) {          
        $root = new DOMDocument();
        $root->loadXML($soapMessage);
        $saml = new itaSAML();                        
        $pkey = $saml->getPrivateKeyFromPEM($signCert['pkey'], $alg);        
        \SAML2\Utils::insertSignature($pkey, array($signCert['cert']), $root->documentElement, null, true, $signatureString, $references);        
        $wsse = str_replace("</wsse:Security>", $signatureString . "</wsse:Security>", $wsse);
        return $wsse;
    }
    
    private static function createWSSecurityHeader($options) {
        $str = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"';
        if (isset($options['mustUnderstand'])) {
            $str .= ' SOAP-ENV:mustUnderstand="' . $options['mustUnderstand'] . '"';
        }
        $str .= '>';
        return $str;        
    }
    
    private static function createWSSecurityTimestamp($options) {
        if (!isset($options['timestamp']) || $options['timestamp'] === 0) {
            return '';
        }
        $str = '<wsu:Timestamp wsu:Id="' . $options['wsID'] . '">';
        $str .= '   <wsu:Created>' . $options['created'] . '</wsu:Created>';
        $str .= '   <wsu:Expires>' . $options['expires'] . '</wsu:Expires>';
        $str .= '</wsu:Timestamp>';
        return $str;
    }
    
    private static function createWSSecurityUsername($options) {
        if (!isset($options['username'])) {
            return '';
        }
        $nonce = mt_rand();
        $str =  '<wsse:UsernameToken>';
        $str .= '   <wsse:Username>' . $options['username']['username'] . '</wsse:Username>';
        $str .= '   <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $options['username']['password'] . '</wsse:Password>';
        $str .= '   <wsse:Nonce>' . base64_encode(pack('H*', $nonce)) . '</wsse:Nonce>';
        $str .= '   <wsu:Created>' . $options['created'] . '</wsu:Created>';
        $str .= '</wsse:UsernameToken>';                
        return $str;
    }
    
    private static function createWSSecuritySaml($options) {
        if (!isset($options['saml'])) {
            return '';
        }
        if (!isset($options['saml']['assertion'])) {
            return '';
        }
        return $options['saml']['assertion'];
    }
    
    private static function createWSSecurityBinarySecurityToken($options) {
        if (!isset($options['binarySecurityToken'])) {
            return '';
        }        
        $str =  '<wsse:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="X509-d3bc171e-596d-45fb-920b-7a3145d3075d">';        
        if ($options['binarySecurityToken']['extract'] === 1) {
            $options['binarySecurityToken']['publicCert'] = str_replace('-----BEGIN CERTIFICATE-----', '', $options['binarySecurityToken']['publicCert']);
            $options['binarySecurityToken']['publicCert'] = str_replace('-----END CERTIFICATE-----', '', $options['binarySecurityToken']['publicCert']);
            $str .= $options['binarySecurityToken']['publicCert'];
        }
        $str .= '</wsse:BinarySecurityToken>';
        return $str;
    }    
    
    private static function createWSSecurityFooter() {
        return '</wsse:Security>';
    }
    
}
