<?php

/**
 *
 * Classe per collegamento Task services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaTask
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    21.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaAuthenticate {

    private $email;
    private $password;

//set()    
    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

//get()
    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getRichiesta($namespace = false) {
        if ($namespace) {
            $prefix = $namespace . ":";
        }
        $richiesta = array();
        $richiesta[$prefix . "email"] = (string) $this->getEmail();
        $richiesta[$prefix . "password"] = (string) $this->getPassword();

        app::log('richiesta');
        app::log($richiesta);
        Out::msginfo("", print_r($richiesta, true));
        return array('authenticate' => $richiesta);
    }

}

?>
