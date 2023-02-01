<?php

/**
 * Modello delle informazioni del firmatario
 * @author l.pergolini
 */

class ItaPHPSignatureSigner {

    private $issuer;            //Emittente del cetificato 
    private $subject;           //firmatario 
    private $fiscalCode;        //codice Fiscale
    private $validStartDate;    //validita start dmy hh:mmss
    private $validEndDate;      //validita end dmy hh:mmss
    private $certificate;       //id certificato
    private $hashAlgOid;        //hash
    private $signingTime;       

    public function __construct($issuer = NULL, $subject = NULL, $fiscalCode = NULL, $validStartDate = NULL, $validEndDate = NULL, $certificate = NULL, $hashAlgOid = NULL, $signingTime = NULL) {
        $this->setIssuer($issuer);
        $this->setSubject($subject);
        $this->setFiscalCode($fiscalCode);
        $this->setValidEndDate($validEndDate);
        $this->setValidStartDate($validStartDate);
        $this->setCertificate($certificate);
        $this->setHashAlgOid($hashAlgOid);
        $this->setSigningTime($signingTime);
    }

    public function getIssuer() {
        return $this->issuer;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function getFiscalCode() {
        return $this->fiscalCode;
    }

    public function getValidStartDate() {
        return $this->validStartDate;
    }

    public function getValidEndDate() {
        return $this->validEndDate;
    }

    public function getCertificate() {
        return $this->certificate;
    }

    public function getHashAlgOid() {
        return $this->hashAlgOid;
    }

    public function getSigningTime() {
        return $this->signingTime;
    }

    public function setIssuer($issuer) {
        $this->issuer = $issuer;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function setFiscalCode($fiscalCode) {
        $this->fiscalCode = $fiscalCode;
    }

    public function setValidStartDate($validStartDate) {
        $this->validStartDate = $validStartDate;
    }

    public function setValidEndDate($validEndDate) {
        $this->validEndDate = $validEndDate;
    }

    public function setCertificate($certificate) {
        $this->certificate = $certificate;
    }

    public function setHashAlgOid($hashAlgOid) {
        $this->hashAlgOid = $hashAlgOid;
    }

    public function setSigningTime($signingTime) {
        $this->signingTime = $signingTime;
    }

}
