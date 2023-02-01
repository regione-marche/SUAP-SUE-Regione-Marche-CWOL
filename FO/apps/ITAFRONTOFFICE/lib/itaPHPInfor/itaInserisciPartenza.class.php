<?php

/**
 * Description of itaInserisciPartenza
 *
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 */
class itaInserisciPartenza {

    private $username;
    private $registro;
    private $sezione;
    private $corrispondente;
    private $mittenteInterno;
    private $soggetti;
    private $altriSoggetti;
    private $oggetto;
    private $smistamenti;
    private $classificazione;
    private $dataInvio;
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

    public function setMittenteInterno($mittenteInterno) {
        $this->mittenteInterno = $mittenteInterno;
    }

    public function setSoggetti($soggetti) {
        $this->soggetti = $soggetti;
    }

    public function setAltriSoggetti($altriSoggetti) {
        $this->altriSoggetti = $altriSoggetti;
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

    public function setDataInvio($dataInvio) {
        $this->dataInvio = $dataInvio;
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

    public function getMittenteInterno() {
        return $this->mittenteInterno;
    }

    public function getSoggetti() {
        return $this->soggetti;
    }

    public function getAltriSoggetti() {
        return $this->altriSoggetti;
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

        /*
         *  Username
         */
        $richiesta[$prefix . "username"] = $this->getUsername();


        $richiesta[$prefix . "protocollaPartenza"] = array();

        /**
         *  Registro
         */
        if (isset($this->registro)) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "registro"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "registro"][$prefix . "codice"] = $this->registro['codice']; //obb
            if (isset($this->registro['descrizione'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "registro"][$prefix . "descrizione"] = $this->registro['descrizione']; //opz
            }
        }

        /**
         *  Sezione
         */
        if (isset($this->sezione)) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "sezione"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "sezione"][$prefix . "codice"] = $this->sezione['codice'];
            if (isset($this->sezione['descrizione'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "sezione"][$prefix . "descrizione"] = $this->sezione['descrizione'];
            }
        }

        /**
         *  Corrispondente
         */
        if (isset($this->corrispondente)) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "corrispondente"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "corrispondente"][$prefix . "codice"] = $this->corrispondente['codice'];
            if (isset($this->corrispondente['descrizione'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "corrispondente"][$prefix . "descrizione"] = $this->sezione['descrizione'];
            }
        }

        /**
         *  mittenteInterno
         */
        if (isset($this->mittenteInterno)) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "mittenteInetrno"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "mittenteInterno"][$prefix . "codice"] = $this->mittenteInterno['codice'];
            if (isset($this->mittenteInterno['descrizione'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "mittenteInterno"][$prefix . "descrizione"] = $this->mittenteInterno['descrizione'];
            }
        }

        /*
         *  Soggetti
         */
        $soggetti = $this->getSoggetti();



        $richiesta[$prefix . "protocollaPartenza"][$prefix . "soggetti"] = array();
        $richiesta[$prefix . "protocollaPartenza"][$prefix . "soggetti"][$prefix . "soggetto"] = array(
            $prefix . "denominazione" => $soggetti[0]['denominazione'],
            $prefix . "indirizzo" => $soggetti[0]['indirizzo']
        );

        if (count($soggetti) > 1) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "soggetti"][$prefix . "altriSoggetti"] = array();
            $objSoggetti = array();
            for ($index = 1; $index < count($soggetti); $index++) {

                $objDenominazione = new soapval($prefix . "denominazione", $prefix . "denominazione", $soggetti[$index]['denominazione'], false, false);
                $objIndirizzo = new soapval($prefix . "indirizzo", $prefix . "indirizzo", $soggetti[$index]['indirizzo'], false, false);
                $objSoggetto = new soapval($prefix . "soggetto", $prefix . "soggetto", array($objDenominazione, $objIndirizzo), false, false);

//                $richiesta[$prefix . "protocollaPartenza"][$prefix . "soggetti"][$prefix . "altriSoggetti"][][$prefix . "soggetto"] = array(
//                    $prefix . "denominazione" => $soggetti[$index]['denominazione'],
//                    $prefix . "indirizzo" => $soggetti[$index]['indirizzo']);

                $objSoggetti[] = $objSoggetto;
            }
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "soggetti"][$prefix . "altriSoggetti"] = $objSoggetti;
        }

        /*
         *  Oggetto
         */
        $richiesta[$prefix . "protocollaPartenza"][$prefix . "oggetto"] = $this->getOggetto();

        /*
         *  Smistamenti
         */
        $smistamenti = $this->getSmistamenti();
        $smistamento = $smistamenti[0];
        $richiesta[$prefix . "protocollaPartenza"][$prefix . "smistamenti"] = array();
        //foreach ($smistamenti as $smistamento) {
        $richiesta[$prefix . "protocollaPartenza"][$prefix . "smistamenti"] = array(
            $prefix . "smistamento" => array(
                $prefix . "corrispondente" => array(
                    $prefix . "codice" => $smistamento['codice']
                )
            )
        );
        //}

        /*
         *  Classificazione
         */
        if ($this->classificazione) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "classificazione"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "classificazione"]['titolario'] = $this->classificazione['titolario'];
            if (isset($this->classificazione['descrizione'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "classificazione"]['descrizione'] = $this->classificazione['descrizione'];
            }
            if (isset($this->classificazione['fascicolo'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "classificazione"]['fascicolo'] = array(
                    $prefix . 'anno' => $this->classificazione['fascicolo']['anno'],
                    $prefix . 'numero' => $this->classificazione['fascicolo']['numero'],
                );
                if (isset($this->classificazione['fascicolo']['descrizione'])) {
                    $richiesta[$prefix . "protocollaPartenza"][$prefix . "classificazione"][$prefix . 'fascicolo'][$prefix . 'descrizione'] = $this->classificazione['fascicolo']['descrizione'];
                }
            }
        }

        /*
         *  DataInvio
         */
        if ($this->dataInvio) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "dataInvio"] = $this->dataInvio;
        }

        /*
         *  Estremi
         */
        if ($this->estremi) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "estremi"] = array();
            if (isset($this->estremi['data'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "estremi"][$prefix . 'data'] = $this->estremi['data'];
            }
            if (isset($this->estremi['numero'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "estremi"][$prefix . 'numero'] = $this->estremi['numero'];
            }
            if (isset($this->estremi['importo'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "estremi"][$prefix . 'importo'] = $this->estremi['importo'];
            }
            if (isset($this->estremi['allegati'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "estremi"]['allegati'] = $this->estremi['allegati'];
            }
        }

        /*
         *  Anteatto
         */
        if ($this->anteatto) {
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "anteatto"] = array();
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "anteatto"][$prefix . 'anno'] = $this->anteatto['anno'];
            $richiesta[$prefix . "protocollaPartenza"][$prefix . "anteatto"][$prefix . 'numero'] = $this->anteatto['numero'];
            if (isset($this->anteatto['registro'])) {
                $richiesta[$prefix . "protocollaPartenza"][$prefix . "anteatto"]['registro']['codice'] = $this->anteatto['registro']['codice'];
                if (isset($this->anteatto['registro']['descrizione'])) {
                    $richiesta[$prefix . "protocollaPartenza"][$prefix . "anteatto"][$prefix . 'registro'][$prefix . 'descrizione'] = $this->anteatto['registro']['descrizione'];
                }
            }
        }

        /*
         *  AltriDati
         */
        if ($this->altriDati) {
            //do something
        }

        /*
         *  Documento
         */
        if ($this->documento) {
            $richiesta[$prefix . "documento"] = array();
            $richiesta[$prefix . "documento"][$prefix . "titolo"] = $this->documento['titolo'];
            if (isset($this->documento['volume'])) {
                $richiesta[$prefix . "documento"][$prefix . 'volume'] = array();
                $richiesta[$prefix . "documento"][$prefix . 'volume'][$prefix . 'codice'] = $this->documento['volume']['codice'];
                if (isset($this->documento['volume']['descrizione'])) {
                    $richiesta[$prefix . "documento"][$prefix . 'volume'][$prefix . 'descrizione'] = $this->documento['volume']['descrizione'];
                }
            }
            if (isset($this->documento['formato'])) {
                $richiesta[$prefix . "documento"][$prefix . 'formato'] = array();
                $richiesta[$prefix . "documento"][$prefix . 'formato'][$prefix . 'codice'] = $this->documento['formato']['codice'];
                if (isset($this->documento['formato']['descrizione'])) {
                    $richiesta[$prefix . "documento"][$prefix . 'formato'][$prefix . 'descrizione'] = $this->documento['formato']['descrizione'];
                }
            }
            $richiesta[$prefix . "documento"][$prefix . "nomeFile"] = $this->documento['nomeFile'];
            if (isset($this->documento['file'])) {
                $richiesta[$prefix . "documento"][$prefix . "file"] = $this->documento['file'];
            }
            if (isset($this->documento['progressivo'])) {
                $richiesta[$prefix . "documento"][$prefix . "progressivo"] = $this->documento['progressivo'];
            }
        }

        /*
         *  ConfermaSegnatura
         */
        if ($this->confermaSegnatura) {
            $richiesta[$prefix . "confermaSegnatura"] = $this->confermaSegnatura;
        }
//        app::log('richiesta');
//        app::log($richiesta);

        return array('richiestaProtocollaPartenza' => $richiesta);
    }

}

?>
