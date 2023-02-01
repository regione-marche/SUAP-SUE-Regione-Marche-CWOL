<?php

/**
 * Description of itaInserisciArrivo
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaAllegaDocumento {

    private $username;
    private $riferimento;
    private $titolo;
    private $volume;
    private $formato;
    private $nomeFile;
    private $file;
    private $progressivo;

//set()    
    public function setUsername($username) {
        $this->username = $username;
    }

    public function setRiferimento($riferimento) {
        $this->riferimento = $riferimento;
    }

    public function setTitolo($titolo) {
        $this->titolo = $titolo;
    }

    public function setVolume($volume) {
        $this->volume = $volume;
    }

    public function setFormato($formato) {
        $this->formato = $formato;
    }

    public function setNomeFile($nomeFile) {
        $this->nomeFile = $nomeFile;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setProgressivo($progressivo) {
        $this->progressivo = $progressivo;
    }

//get()
    public function getUsername() {
        return $this->username;
    }

    public function getRiferimento() {
        return $this->riferimento;
    }

    public function getTitolo() {
        return $this->titolo;
    }

    public function getVolume() {
        return $this->volume;
    }

    public function getFormato() {
        return $this->formato;
    }

    public function getNomeFile() {
        return $this->nomeFile;
    }

    public function getProgressivo() {
        return $this->progressivo;
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
            if (isset($this->riferimento['registro'])) {
                $richiesta[$prefix . "riferimento"][$prefix . "registro"][$prefix . "codice"] = $this->riferimento['registro']['codice'];
                if (isset($this->riferimento['registro']['descrizione'])) {
                    $richiesta[$prefix . "riferimento"][$prefix . "registro"][$prefix . "descrizione"] = $this->riferimento['registro']['descrizione'];
                }
            }
        }

        $richiesta[$prefix . "documento"] = array();
        $richiesta[$prefix . "documento"][$prefix . 'titolo'] = $this->titolo;

        //
        // Volume
        //
        if ($this->getVolume()) {
            $richiesta[$prefix . "documento"][$prefix . "volume"][$prefix . "codice"] = $this->volume['codice'];
            if (isset($this->volume['descrizione'])) {
                $richiesta[$prefix . "documento"][$prefix . "volume"][$prefix . "descrizione"] = $this->volume['descrizione'];
            }
        }

        //
        // Formato
        //
        if ($this->getFormato()) {
            $richiesta[$prefix . "documento"][$prefix . "formato"][$prefix . "codice"] = $this->formato['codice'];
            if (isset($this->formato['descrizione'])) {
                $richiesta[$prefix . "documento"][$prefix . "formato"][$prefix . "descrizione"] = $this->formato['descrizione'];
            }
        }

        //
        // NomeFile
        //
        $richiesta[$prefix . "documento"][$prefix . "nomeFile"] = $this->nomeFile;

        //
        // File
        //
        $richiesta[$prefix . "documento"][$prefix . "file"] = $this->file;

//        app::log('richiesta');
//        app::log($richiesta);

        return array('richiestaAllegaDocumento' => $richiesta);
    }

}

?>
