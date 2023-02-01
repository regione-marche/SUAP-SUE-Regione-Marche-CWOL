<?php

class operationResult {
    const SUCCESS = 0;

    private $returnCode = 0;
    private $message;
    private $resourceDescriptors = array();
    private $version = "1.2.1";

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getResourceDescriptors() {
        return $this->resourceDescriptors;
    }

    public function setResourceDescriptors($resourceDescriptors) {
        $this->resourceDescriptors = $resourceDescriptors;
    }

    public function addResourceDescriptor($descriptor) {
        $this->resourceDescriptors[] = $descriptor;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    public function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

}

?>
