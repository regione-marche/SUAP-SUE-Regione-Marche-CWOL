<?php

require_once(ITA_LIB_PATH . '/phpMailer/class.phpmailer.php');

class itaMailer extends PHPMailer {

    public function itaAddAddress($addressMulti, $nameMulti = '') {
        $arr_address = array();
        $arr_address = explode(';', $addressMulti);
        foreach ($arr_address as $key => $addressValue) {
            parent::AddAddress($addressValue);
        }
    }
    public function itaAddCCAddress($addressMulti, $nameMulti = '') {
        $arr_address = array();
        $arr_address = explode(';', $addressMulti);
        foreach ($arr_address as $key => $addressValue) {
            parent::AddCC($addressValue);
        }
    }

    public function itaAddBCCAddress($addressMulti, $nameMulti = '') {
        $arr_address = array();
        $arr_address = explode(';', $addressMulti);
        foreach ($arr_address as $key => $addressValue) {
            parent::AddBCC($addressValue);
        }
    }

    public function GetSentMIMEMessage() {
        //if (method_exists(parent, 'GetSentMIMEMessage')){
            return parent::GetSentMIMEMessage();
        //}else{
//            return 'no';
//        }
            
    }
    
    public function Send($parametri = null) {
        if ($parametri == null) {
            return false;
        } else {
            $this->From = $parametri['FROM'];
            $this->Timeout = 60;
            if (isset($parametri['HOST'])) {
                if ($parametri['HOST']) {
                    $this->IsSMTP();
                    $this->SMTPAuth = false;
                    if (isset($parametri['PASSWORD']) && $parametri['PASSWORD']) {
                        $this->SMTPAuth = true;
                    }
                    $this->Host = $parametri['HOST'];
                    if (isset($parametri['PORT'])) {
                        if ($parametri['PORT'])
                            $this->Port = $parametri['PORT'];
                    }
                    if (isset($parametri['SMTPSECURE'])) {
                        if ($parametri['SMTPSECURE'])
                            $this->SMTPSecure = $parametri['SMTPSECURE'];
                    }
                    $this->Username = $parametri['USERNAME'];
                    $this->Password = $parametri['PASSWORD'];
                }
            }
        }
        return parent::Send();
    }

}

?>
