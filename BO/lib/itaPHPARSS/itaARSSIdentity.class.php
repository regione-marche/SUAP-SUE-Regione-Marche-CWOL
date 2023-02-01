<?php

/**
 * Description of itaARSSIdentity
 *
 * @author michele
 */
class itaARSSIdentity {
//    public $delegated_domain;
//    public $delegated_password;
//    public $delegated_user;
//    public $ext_auth_blobvalue;
//    public $ext_auth_value;
//    public $ext_authtype;
    public $otpPwd;
    public $typeHSM;
    public $typeOtpAuth;
    public $user;
    public $userPWD;

    public function getDelegated_domain() {
        return $this->delegated_domain;
    }

    public function setDelegated_domain($delegated_domain) {
        $this->delegated_domain = $delegated_domain;
    }

    public function getDelegated_password() {
        return $this->delegated_password;
    }

    public function setDelegated_password($delegated_password) {
        $this->delegated_password = $delegated_password;
    }

    public function getDelegated_user() {
        return $this->delegated_user;
    }

    public function setDelegated_user($delegated_user) {
        $this->delegated_user = $delegated_user;
    }

    public function getExt_auth_blobvalue() {
        return $this->ext_auth_blobvalue;
    }

    public function setExt_auth_blobvalue($ext_auth_blobvalue) {
        $this->ext_auth_blobvalue = $ext_auth_blobvalue;
    }

    public function getExt_auth_value() {
        return $this->ext_auth_value;
    }

    public function setExt_auth_value($ext_auth_value) {
        $this->ext_auth_value = $ext_auth_value;
    }

    public function getExt_authtype() {
        return $this->ext_authtype;
    }

    public function setExt_authtype($ext_authtype) {
        $this->ext_authtype = $ext_authtype;
    }

    public function getOtpPwd() {
        return $this->otpPwd;
    }

    public function setOtpPwd($otpPwd) {
        $this->otpPwd = $otpPwd;
    }

    public function getTypeHSM() {
        return $this->typeHSM;
    }

    public function setTypeHSM($typeHSM) {
        $this->typeHSM = $typeHSM;
    }

    public function getTypeOtpAuth() {
        return $this->typeOtpAuth;
    }

    public function setTypeOtpAuth($typeOtpAuth) {
        $this->typeOtpAuth = $typeOtpAuth;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getUserPWD() {
        return $this->userPWD;
    }

    public function setUserPWD($userPWD) {
        $this->userPWD = $userPWD;
    }    
}

?>
