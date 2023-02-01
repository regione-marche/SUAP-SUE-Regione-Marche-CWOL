<?php

/**
 * Description of itaInserisciArrivo
 *
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 */
class itaServiziRicerca {

    private $username;
    private $sigla;
    private $variabili;

//set()    
    public function setUsername($username) {
        $this->username = $username;
    }

    public function setSigla($sigla) {
        $this->sigla = $sigla;
    }

    function setVariabili($variabili) {
        $this->variabili = $variabili;
    }

//get()
    public function getUsername() {
        return $this->username;
    }

    public function getSigla() {
        return $this->sigla;
    }

    function getVariabili() {
        return $this->variabili;
    }

    public function getRichiesta($namespace = false) {
        if ($namespace) {
            $prefix = $namespace . ":";
        }
        $richiesta = array();

        $richiesta[$prefix . "RichiestaTendineEtichette"][$prefix . 'etichette'][$prefix . 'siglaCampoModulo'] = $this->sigla;



        if (count($this->variabili) == 1) {
            foreach ($this->variabili as $variabile => $value) {
                $richiesta[$prefix . "RichiestaTendineEtichette"][$prefix . 'etichette'][$prefix . 'variabili'][$prefix . 'variabile'] = $variabile;
                $richiesta[$prefix . "RichiestaTendineEtichette"][$prefix . 'etichette'][$prefix . 'variabili'][$prefix . 'valore'] = $value;
            }
        } else {
            $i = 0;
            $arrVariabili = array();
            foreach ($this->variabili as $variabile => $value) {
                $arrVariabili[$i][$prefix . 'variabile'] = $variabile;
                $arrVariabili[$i][$prefix . 'valore'] = $value;
                $i++;
            }
            $richiesta[$prefix . "RichiestaTendineEtichette"][$prefix . 'etichette'][$prefix . 'variabili'] = $arrVariabili;
        }

        return array(
            "operatore" => $this->getUsername(),
            'richiesta' => $richiesta
        );
    }

}

?>
