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
class itaInserisciArrivo {

    private $username;
    private $registro; //Optional
    private $sezione;  //Optional 
    private $corrispondente; //Optional
    private $soggetti;
    private $smistamenti;
    private $oggetto;
    private $classificazione;
    private $dataRicezione;
    private $estremi;
    private $anteatto;
    private $altriDati;
    private $documento;
    private $confermaSegnatura;

//set()    
    public function setUsername($username) {
        $this->username = $username;
    }

    public function setRegistro($registro) {
        $this->registro = $registro;
    }

    public function setSezione($sezione) {
        $this->sezione = $sezione;
    }

    public function setCorrispondente($corrispondente) {
        $this->corrispondente = $corrispondente;
    }

    public function setSoggetti($soggetti) {
        $this->soggetti = $soggetti;
    }

    public function setSmistamenti($smistamenti) {
        $this->smistamenti = $smistamenti;
    }

    public function setOggetto($oggetto) {
        $this->oggetto = $oggetto;
    }

    public function setClassificazione($classificazione) {
        $this->classificazione = $classificazione;
    }

    public function setDataRicezione($dataRicezione) {
        $this->dataRicezione = $dataRicezione;
    }

    public function setEstremi($estremi) {
        $this->estremi = $estremi;
    }

    public function setAnteatto($anteatto) {
        $this->anteatto = $anteatto;
    }

    public function setAltriDati($altriDati) {
        $this->altriDati = $altriDati;
    }

    public function setDocumento($documento) {
        $this->documento = $documento;
    }

    public function setConfermaSegnatura($confermaSegnatura) {
        if ($confermaSegnatura == true) {
            $this->confermaSegnatura = true;
        } else {
            $this->confermaSegnatura = false;
        }
    }

//get()

    public function getUsername() {
        return $this->username;
    }

    public function getRegistro() {
        return $this->registro;
    }

    public function getSezione() {
        return $this->sezione;
    }

    public function getCorrispondente() {
        return $this->corrispondente;
    }

    public function getSoggetti() {
        return $this->soggetti;
    }

    public function getSmistamenti() {
        return $this->smistamenti;
    }

    public function getOggetto() {
        return $this->oggetto;
    }

    public function getClassificazione() {
        return $this->classificazione;
    }

    public function getDataRicezione() {
        return $this->dataRicezione;
    }

    public function getEstremi() {
        return $this->estremi;
    }

    public function getAnteatto() {
        return $this->anteatto;
    }

    public function getAltriDati() {
        return $this->altriDati;
    }

    public function getDocumento() {
        return $this->documento;
    }

    public function getConfermaSegnatura() {
        return $this->confermaSegnatura;
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
        // Protocolla Arrivo
        //
        $richiesta[$prefix . "protocollaArrivo"] = array();

        //
        // Registro
        //
        if ($this->getRegistro()) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "registro"] = array(
                $prefix . "codice" => $this->getRegistro()
            );
        }
        //
        // Sezione
        //
        if ($this->getSezione()) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "sezione"] = array(
                $prefix . "codice" => $this->getSezione()
            );
        }
        //
        // Corrispondente
        //
        if ($this->getCorrispondente()) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "corrispondente"] = array(
                $prefix . "codice" => $this->getCorrispondente()
            );
        }

        //
        // Soggetti
        //
        $soggetti = $this->getSoggetti();
        $richiesta[$prefix . "protocollaArrivo"][$prefix . "soggetti"] = array();
        $richiesta[$prefix . "protocollaArrivo"][$prefix . "soggetti"][$prefix . "soggetto"] = array(
            $prefix . "denominazione" => $soggetti['denominazione']
        );
        if (isset($soggetti['indirizzo'])) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "soggetti"][$prefix . "soggetto"][$prefix . "indirizzo"] = $soggetti['indirizzo'];
        }

        //
        // Smistamenti
        //
        $smistamenti = $this->getSmistamenti();
        $smistamento = $smistamenti[0];
        $richiesta[$prefix . "protocollaArrivo"][$prefix . "smistamenti"] = array();
        //foreach ($smistamenti as $smistamento) {
        $richiesta[$prefix . "protocollaArrivo"][$prefix . "smistamenti"] = array(
            $prefix . "smistamento" => array(
                $prefix . "corrispondente" => array(
                    $prefix . "codice" => $smistamento['codice']
                )
            )
        );

        //}
        //
        // Oggetto
        //
        $richiesta[$prefix . "protocollaArrivo"][$prefix . "oggetto"] = $this->getOggetto();

        //
        // Classificazione
        //
//        app::log('classificazione');
//        app::log($this->classificazione);
        if ($this->classificazione) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "classificazione"] = array();
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "classificazione"][$prefix . 'titolario'] = $this->classificazione['titolario'];
            if (isset($this->classificazione['fascicolo'])) {
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "classificazione"][$prefix . 'fascicolo'] = array(
                    $prefix . 'anno' => $this->classificazione['fascicolo']['anno'],
                    $prefix . 'numero' => $this->classificazione['fascicolo']['numero']
                        //$prefix . 'descrizione' => $this->classificazione['fascicolo']['descrizione']                        
                );
            }
        }

        //
        // Data Ricezione
        //
        if ($this->dataRicezione) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "dataRicezione"] = $this->dataRicezione;
        }


        //
        // Estremi
        //
        if ($this->estremi) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "estremi"] = array();
            if (isset($this->estremi['data']))
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "data"] = $this->estremi['data'];
            if (isset($this->estremi['numero']))
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "numero"] = $this->estremi['numero'];
            if (isset($this->estremi['importo']))
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "importo"] = $this->estremi['importo'];
            if (isset($this->estremi['allegati']))
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "allegati"] = $this->estremi['allegati'];
        }

        //
        // Anteatto
        //
        if ($this->anteatto) {
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"] = array();
            if (isset($this->anteatto['registro'])) {
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"][$prefix . "registro"] = array();
                $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"][$prefix . "registro"]['codice'] = $this->anteatto['registro']['codice'];
                if (isset($this->anteatto['registro']['descrizione']))
                    $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"][$prefix . "registro"]['descrizione'] = $this->anteatto['registro']['descrizione'];
            }
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"][$prefix . "anno"] = $this->anteatto['anno'];
            $richiesta[$prefix . "protocollaArrivo"][$prefix . "anteatto"][$prefix . "numero"] = $this->anteatto['numero'];
        }

        //
        // Documento
        //
        if ($this->documento) {
            $richiesta[$prefix . "documento"] = array();
            $richiesta[$prefix . "documento"][$prefix . "titolo"] = $this->documento['titolo'];
            $richiesta[$prefix . "documento"][$prefix . "nomeFile"] = $this->documento['nomeFile'];
            $richiesta[$prefix . "documento"][$prefix . "file"] = $this->documento['file'];
        }

        //
        // Conferma Segnatura
        //
        $richiesta[$prefix . "confermaSegnatura"] = (bool) $this->confermaSegnatura;

//        app::log('richiesta');
//        app::log($richiesta);

        return array('richiestaProtocollaArrivo' => $richiesta);
    }

}

?>
