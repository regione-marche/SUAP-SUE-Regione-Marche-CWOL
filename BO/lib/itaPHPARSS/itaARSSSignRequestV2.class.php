<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itareqProtocolloArrivo
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */


class itaARSSSignRequestV2 {
    public $binaryinput;
    public $certID;
    public $dstName;
    public $identity;
    public $session_id;    
//    public $notify_id;
//    public $notifymail;
//    public $profile;
//    public $requiredmark;
//    public $signingTime;
//    public $srcName;
//    public $stream;
    public $transport;
    
    
    public function getBinaryinput() {
        return $this->binaryinput;
    }

    public function setBinaryinput($binaryinput) {
        $this->binaryinput = $binaryinput;
    }

    public function loadBinaryinput($fileName) {
        $this->binaryinput = base64_encode(file_get_contents($fileName));
    }
    
    public function getCertID() {
        return $this->certID;
    }

    public function setCertID($certID) {
        $this->certID = $certID;
    }

    public function getDstName() {
        return $this->dstName;
    }

    public function setDstName($dstName) {
        $this->dstName = $dstName;
    }

    public function getIdentity() {
        return $this->identity;
    }

    public function setIdentity($identity) {
        $this->identity = $identity;
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

    public function getProfile() {
        return $this->profile;
    }

    public function setProfile($profile) {
        $this->profile = $profile;
    }

    public function getRequiredmark() {
        return $this->requiredmark;
    }

    public function setRequiredmark($requiredmark) {
        $this->requiredmark = $requiredmark;
    }

    public function getSession_id() {
        return $this->session_id;
    }

    public function setSession_id($session_id) {
        $this->session_id = $session_id;
    }

    public function getSigningTime() {
        return $this->signingTime;
    }

    public function setSigningTime($signingTime) {
        $this->signingTime = $signingTime;
    }

    public function getSrcName() {
        return $this->srcName;
    }

    public function setSrcName($srcName) {
        $this->srcName = $srcName;
    }

    public function getStream() {
        return $this->stream;
    }

    public function setStream($stream) {
        $this->stream = $stream;
    }

    public function getTransport() {
        return $this->transport;
    }

    public function setTransport($transport) {
        $this->transport = $transport;
    }

    
}
