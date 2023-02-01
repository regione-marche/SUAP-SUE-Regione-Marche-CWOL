<?php

/**
 * Description of itaInserisciArrivo
 *
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 */
class itaInviaProtocollo {

    private $username;
    private $riferimento;
    private $account;

//set()    
    public function setUsername($username) {
        $this->username = $username;
    }
    public function setRiferimento($riferimento) {
        $this->riferimento = $riferimento;
    }
    public function setAccount($account) {
        $this->account = $account;
    }

//get()
    public function getUsername() {
        return $this->username;
    }
    public function getRiferimento() {
        return $this->riferimento;
    }
    public function getAccount() {
        return $this->account;
    }
    
    public function getRichiesta($namespace = false) {
        if ($namespace) {
            $prefix = $namespace . ":";
        }
        $richiesta = array();
        //
        // Username
        //
        $richiesta[$prefix . "username"] = $this->getUsername();

        //
        // Riferimento
        //
        if ($this->getRiferimento()) {
            $richiesta[$prefix . "riferimento"][$prefix . 'anno'] = $this->riferimento['anno'];
            $richiesta[$prefix . "riferimento"][$prefix . 'numero'] = $this->riferimento['numero'];
            app::log('riferimento');
            app::log($this->riferimento);
            if (isset($this->riferimento['registro'])) {
                $richiesta[$prefix . "riferimento"][$prefix . "registro"][$prefix . "codice"] = $this->riferimento['registro']['codice'];
                if (isset($this->riferimento['registro']['descrizione'])) {
                    $richiesta[$prefix . "riferimento"][$prefix . "registro"][$prefix . "descrizione"] = $this->riferimento['registro']['descrizione'];
                }
            }
        }

        //
        // Account
        //
        if ($this->getAccount()) {
            $richiesta[$prefix . "account"] = $this->account;
        }

        app::log('richiesta');
        app::log($richiesta);

        return array('richiestaInviaProtocollo' => $richiesta);
    }
    

}

?>
