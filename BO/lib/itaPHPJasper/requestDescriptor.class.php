<?php

class requestDescriptor {

    private $operationName;
    private $locale;
    private $arguments = array();
    private $resourceDescriptor;
    private $attachments;

    public function setOperationName($operationName) {
        $this->operationName = $operationName;
    }

    public function getOperationName() {
        return $this->operationName;
    }

    public function setAttachments($attachments) {
        $this->attachments = $attachments;
    }

    public function getAttachments() {
        return $this->attachments;
    }
    
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function setArguments($arguments) {
        $this->arguments = $arguments;
    }

    public function addArgument($argumentName, $value=null) {
        $this->arguments[$argumentName] = $value;
    }

    public function setResourceDescriptor($resourceDescriptor) {
        $this->resourceDescriptor = $resourceDescriptor;
    }

    public function getResourceDescriptor($param) {
        return $this->resourceDescriptor;
    }

    public function toXML($exportChildren=false) {
        $xml = "<request ";
        $xml .= 'operationName = "' . $this->getOperationName() . '" ';
        $xml .= 'locale = "' . $this->getLocale() . '" ';
        $xml .= ">";
        foreach ($this->arguments as $argument => $value) {
            $xml .= "<argument name=\"$argument\">" . $value . "</argument>";
        }
        $xml .= $this->resourceDescriptor->toXml($exportChildren);
        $xml .="</request>";
        return $xml;
    }

}

?>
