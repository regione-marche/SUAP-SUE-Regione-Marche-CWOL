<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    08.06.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
class emlOutgoingMessage {

    private $FromName;
    private $Subject;
    private $From;
    private $Body;
    private $Email;
    private $CCAddresses;
    private $BCCAddresses;
    private $Attachments;
    private $CustomHeaders;

    function __construct() {
        
    }

    public function getFromName() {
        return $this->FromName;
    }

    public function setFromName($FromName) {
        $this->FromName = $FromName;
    }

    public function getSubject() {
        return $this->Subject;
    }

    public function setSubject($Subject) {
        $this->Subject = $Subject;
    }

    public function getFrom() {
        return $this->From;
    }

    public function setFrom($From) {
        $this->From = $From;
    }

    public function getBody() {
        return $this->Body;
    }

    public function setBody($Body) {
        $this->Body = $Body;
    }

    public function getEmail() {
        return $this->Email;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
    }

    function getCCAddresses() {
        return $this->CCAddresses;
    }

    function getBCCAddresses() {
        return $this->BCCAddresses;
    }

    function setCCAddresses($CCAddresses) {
        $this->CCAddresses = $CCAddresses;
    }

    function setBCCAddresses($BCCAddresses) {
        $this->BCCAddresses = $BCCAddresses;
    }

    public function getAttachments() {
        return $this->Attachments;
    }

    /**
     * 
     * @param array $Attachments
     * array numerale di array associativo
     * [FILEORIG] Nome originale del file da allegare (attenzioe al charset ed ai nomi con caratteri speciali)
     * [FILEPATH] Posizione fisica da dove il gestore della casella in uscita prenderà il binario per assemblare la mail
     * 
     */
    public function setAttachments($Attachments) {
        $this->Attachments = $Attachments;
    }

    public function getCustomHeaders() {
        return $this->CustomHeaders;
    }

    public function setCustomHeaders($CustomHeaders) {
        $this->CustomHeaders = $CustomHeaders;
    }

    public function setCustomHeader($key, $value) {
        if (!$this->customHeaders) {
            $this->customHeaders = array();
        }
        if ($value) {
            $this->customHeaders[$key] = $value;
        } else {
            unset($this->customHeaders[$key]);
        }
    }

    public function addAttachment($FilePath, $FileOrig) {
        $this->Attachments[] = array('FILEPATH' => $FilePath, 'FILEORIG' => $FileOrig);
    }

    public function addBinaryAttachment($corpo, $FileOrig) {
        if (!@is_dir(itaLib::getAppsTempPath())) {
            if (!itaLib::createAppsTempPath()) {
                return false;
            }
        }
        $pathTmp = itaLib::getAppsTempPath() . '/' . rand(100, 9999) . $FileOrig;
       
        if (!file_put_contents($pathTmp, $corpo)) {
            return false;
        }

        $this->Attachments[] = array('FILEPATH' => $pathTmp, 'FILEORIG' => $FileOrig);

        return true;
    }

    public function getMessageArray() {
        return array(
            'FromName' => $this->getFromName(),
            'Subject' => $this->Subject,
            'From' => $this->From,
            'Body' => $this->Body,
            'CustomHeaders' => $this->From,
            'Email' => $this->Email,
            'Addresses' => $this->Email,
            'CCAddresses' => $this->CCAddresses,
            'BCCAddresses' => $this->BCCAddresses,
            'Attachments' => $this->Attachments
        );
    }

    //
    // Custom header helpers

    //

    /**
     * Imposta il tipo di ricevuta di consegna PEC com breve
     * Non ritornato gli allegati originali ma l'HASH del file binario.
     * 
     */
    public function setPECRicvutaBreve() {
        $this->setCustomHeader(emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA, emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE);
    }

}

?>
