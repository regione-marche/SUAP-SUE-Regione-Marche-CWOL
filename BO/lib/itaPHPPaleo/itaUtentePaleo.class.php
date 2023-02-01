<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaUtentePaleo
 *
 * @author michele
 */
class itaUtentePaleo {

    private $Userid;
    private $CodAmm;

    function __construct($Userid="", $CodAmm="") {
        if ($Userid) {
            $this->Userid = $Userid;
        }
        if ($CodAmm) {
            $this->CodAmm = $CodAmm;
        }
    }

    public function setUserId($Userid) {
        $this->Userid = $Userid;
    }

    public function setCodAmm($CodAmm) {
        $this->CodAmm = $CodAmm;
    }

    public function getUserId() {
        return $this->Userid;
    }

    public function getCodAmm() {
        return $this->CodAmm;
    }

}

?>
