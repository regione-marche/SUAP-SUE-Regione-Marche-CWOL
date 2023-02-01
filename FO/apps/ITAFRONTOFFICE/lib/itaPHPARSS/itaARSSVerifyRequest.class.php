<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 *
 * 
 */
class itaARSSVerifyRequest {

    public $transport;
    public $binaryinput;
    public $srcName;
    public $dstName;
    public $type;
    public $Verdate;
    public $notifymail;
    public $notify_id;

    public function getBinaryinput() {
        return $this->binaryinput;
    }

    public function setBinaryinput($binaryinput) {
        $this->binaryinput = $binaryinput;
    }

    public function loadBinaryinput($fileName) {
        $this->binaryinput = base64_encode(file_get_contents($fileName));
    }

    public function getDstName() {
        return $this->dstName;
    }

    public function setDstName($dstName) {
        $this->dstName = $dstName;
    }

    public function getNotify_id() {
        return $this->notify_id;
    }

    public function setNotify_id($notify_id) {
        $this->notify_id = $notify_id;
    }

    public function getNotifymail() {
        return $this->notifymail;
    }

    public function setNotifymail($notifymail) {
        $this->notifymail = $notifymail;
    }

    public function getTransport() {
        return $this->transport;
    }

    public function setTransport($transport) {
        $this->transport = $transport;
    }
    public function getSrcName() {
        return $this->srcName;
    }

    public function setSrcName($srcName) {
        $this->srcName = $srcName;
    }

    public function getType() {
        return $sthis->type;
    }

    public function setType($Type) {
        $this->type = $Type;
    }

    public function getVerdate() {
        return $this->Verdate;
    }

    public function setVerdate($Verdate) {
        $this->Verdate = $Verdate;
    }


}

?>
