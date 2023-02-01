<?php

/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    21.11.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaLeggiProtocollo {

    private $username;
    private $registro;
    private $anno;
    private $numero;
    private $allegati;

//set()    
    public function setUsername($username) {
        $this->username = $username;
    }
    public function setRegistro($registro) {
        $this->registro = $registro;
    }
    public function setAnno($anno) {
        $this->anno = $anno;
    }
    public function setNumero($numero) {
        $this->numero = $numero;
    }
    public function setAllegati($allegati) {
        $this->allegati = $allegati;
    }

//get()
    public function getUsername() {
        return $this->username;
    }
    public function getRegistro() {
        return $this->registro;
    }
    public function getAnno() {
        return $this->anno;
    }
    public function getNumero() {
        return $this->numero;
    }
    public function getAllegati() {
        return $this->allegati;
    }

    public function getRichiesta($namespace = false){
        if($namespace){
            $prefix = $namespace. ":";
        }
        $richiesta = array();
        $richiesta[$prefix . "username"] = (string)$this->getUsername();
        $richiesta[$prefix . "riferimento"] = array(
            $prefix . "anno" => (string)$this->getAnno(),
            $prefix . "numero" => (string)$this->getNumero()
        );
        //$richiesta[$prefix . "allegati"] = (bool)$this->getAllegati();
        if ($this->getAllegati()){
            $richiesta[$prefix . "allegati"] = true;
        } else {
            $richiesta[$prefix . "allegati"] = false;
        }
        app::log('richiesta');
        app::log($richiesta);
        
        return array('richiestaLeggiProtocollo' => $richiesta);
        
    }
}

?>
