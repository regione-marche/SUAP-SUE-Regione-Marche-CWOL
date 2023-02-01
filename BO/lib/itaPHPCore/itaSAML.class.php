<?php

require_once ITA_LIB_PATH . '/saml2/src/_autoload.php';
require_once ITA_LIB_PATH . '/saml2/custom/itaContainer.php';
require_once ITA_LIB_PATH . '/saml2/lib/xmlseclibs/xmlseclibs.php';

/**
 * Classe di interfaccia con SAML2
 *
 * @author m.biagioli
 */
class itaSAML {
    
    const SUBJECT_CONFIRMATION_METHOD = "urn:oasis:names:tc:SAML:2.0:cm:bearer";
    const FORMAT_NAMEID_UNSPECIFIED = "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified";
    const AUTHN_CONTEXT_CLASSREF = '';
    const ATTRIBUTE_NAMEFORMAT_UNSPECIFIED = "urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified";
    
    const ERROR_CODE_INTERNAL = 999;
    
    private $errorCode;
    private $errorDescription;
    
    /**
     * Creazione asserzione SAML2 come stringa
     * @param array $data Dati con cui creare asserzione. La struttura è la seguente:
     *      - issuer                    : Chi emette l'asserzione
     *      - conditions
     *          - notBefore             : Asserzione non valida prima di
     *          - notOnOrAfter          : Asserzione non più valida dopo di
     *      - subject
     *          - notBefore             : Asserzione non valida prima di   
     *          - notOnOrAfter          : Asserzione non più valida dopo di
     *          - nameId                : Identificativo del soggetto 
     *      - authn
     *          - instant               : Tempo formulazione asserzione
     *          - notOnOrAfter          : Asserzione non più valida dopo di    
     *      - signature
     *          - privateKey            : Chiave privata per la firma  (PEM) 
     *          - publicKey             : Chiave pubblica per la firma (PEM)   
     *      - options
     *          - removeXmlDeclaration  : se TRUE, rimuove la dichiarazione XML dalla stringa
     * @return Asserzione SAML in caso di esito positivo, altrimenti false
     */
    public function createAssertionString($data) {
        // Azzera errri
        $this->setError(0, '');
        
        // Controllo precondizioni
        if (!$this->createAssertionStringCheckPreconditions($data)) {
            return;
        }
        
        // Crea container
        $container = new itaContainer();
        SAML2\Compat\ContainerSingleton::setContainer($container);
        
        // Inizializza asserzione
        $assertion = new \SAML2\Assertion();
        
        // Issuer
        $assertion->setIssuer($data['issuer']);    
        
        // Conditions
        $assertion->setNotBefore($data['conditions']['notBefore']);
        $assertion->setNotOnOrAfter($data['conditions']['notOnOrAfter']);
        
        // Subject
        $subjectConfirmation = new \SAML2\XML\saml\SubjectConfirmation();
        $subjectConfirmation->Method = self::SUBJECT_CONFIRMATION_METHOD;       
        $subjectConfirmationData = new \SAML2\XML\saml\SubjectConfirmationData();
        $subjectConfirmationData->NotBefore = $data['subject']['notBefore'];
        $subjectConfirmationData->NotOnOrAfter = $data['subject']['notOnOrAfter'];
        $subjectConfirmation->SubjectConfirmationData = $subjectConfirmationData;
        $assertion->setSubjectConfirmation(array($subjectConfirmation));     
        $nameId = new \SAML2\XML\saml\NameID();
        $nameId->Format = self::FORMAT_NAMEID_UNSPECIFIED;
        $nameId->value = $data['subject']['nameId'];        
        $assertion->setNameId($nameId);
        
        // AuthnStatement        
        $assertion->setAuthnInstant($data['authn']['instant']);
        $assertion->setSessionNotOnOrAfter($data['authn']['notOnOrAfter']);
        $assertion->setAuthnContextClassRef(self::AUTHN_CONTEXT_CLASSREF);
        $assertion->setSubjectLocality($data['authn']['subjectLocality']);
        
        // Attributes
        $attributes = array();       
        foreach ($data['attributes'] as $attributeKey => $attributeValue) {
            $attributes[$attributeKey] = array($attributeValue);
        }
        $assertion->setAttributes($attributes);
        $assertion->setAttributeNameFormat(self::ATTRIBUTE_NAMEFORMAT_UNSPECIFIED);      
        
        // Signature          
        $assertion->setSignatureKey($this->getPrivateKeyFromPEM($data['signature']['privateKey']));
        $assertion->setCertificates(array($data['signature']['publicKey']));
        
        // Restituisce asserzione come stringa
        $xml = $assertionElement = $assertion->toXML();
        return $this->processAssertionString($data, $xml->ownerDocument->saveXML());                        
    } 
    
    private function createAssertionStringCheckPreconditions($data) {
        if (!isset($data['issuer'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'issuer' non valorizzato");            
            return false;
        }
        if (!isset($data['conditions'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'conditions' non valorizzato");            
            return false;
        }
        if (!isset($data['conditions']['notBefore'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'conditions>notBefore' non valorizzato");            
            return false;
        }
        if (!isset($data['conditions']['notOnOrAfter'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'conditions>notOnOrAfter' non valorizzato");            
            return false;
        }
        if (!isset($data['subject'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'subject' non valorizzato");            
            return false;
        }
        if (!isset($data['subject']['notBefore'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'subject>notBefore' non valorizzato");            
            return false;
        }
        if (!isset($data['subject']['notOnOrAfter'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'subject>notOnOrAfter' non valorizzato");            
            return false;
        }
        if (!isset($data['subject']['nameId'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'subject>nameId' non valorizzato");            
            return false;
        }
        if (!isset($data['authn'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'authn' non valorizzato");            
            return false;
        }
        if (!isset($data['authn']['instant'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'authn>instant' non valorizzato");            
            return false;
        }
        if (!isset($data['authn']['notOnOrAfter'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'authn>notOnOrAfter' non valorizzato");            
            return false;
        }
        if (!isset($data['attributes'])) {
            $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'attributes' non valorizzato");            
            return false;
        }
        if (isset($data['signature'])) {
            if (!isset($data['signature']['privateKey'])) {
                $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'signature>privateKey' non valorizzato");            
                return false;
            }
            if (!isset($data['signature']['publicKey'])) {
                $this->setError(self::ERROR_CODE_INTERNAL, "Campo 'signature>publicKey' non valorizzato");            
                return false;
            }
        }
        return true;
    }
    
    public function getPrivateKeyFromPEM($pem, $alg = null) {
        if (!$alg) {
            $alg = \RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA1;
        }
        $privateKey = new \RobRichards\XMLSecLibs\XMLSecurityKey($alg, array('type'=>'private'));
        $privateKey->loadKey($pem);
        return $privateKey;
    }
    
    private function processAssertionString($data, $strAssertion) {
        if (isset($data['options']) && isset($data['options']['removeXmlDeclaration'])) {
            $tmpDoc = new DOMDocument();
            $tmpDoc->loadXML($strAssertion);
            $strAssertion = $tmpDoc->saveXML($tmpDoc->documentElement);
        }        
        return $strAssertion;
    }
    
    private function setError($errorCode, $errorDescription) {
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
    }            
    
    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }    
    
}
