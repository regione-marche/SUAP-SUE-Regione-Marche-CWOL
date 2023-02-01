<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Andrea Bufarini  <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    12.03.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praImmobili {

    private $immobili = array();
    public $pratica;
    public $praLib;
    public $tabTitle;

    /**
     * 
     * @param type $praLib     Non piu usato !!!!
     * @param type $pratica
     * @return object
     */
    public static function getInstance($praLib='', $pratica = '') {
        try {
            $obj = new praImmobili();
        } catch (Exception $exc) {
            return false;
        }
        if ($pratica) {
            $obj->pratica = $pratica;
            if (!$obj->caricaImmobili()) {
                return false;
            }
        }
        return $obj;
    }

    /**
     * Carica dati Oggetto da DB
     * @return boolean
     */
    function caricaImmobili() {
        $this->immobili = $this->GetImmobiliFromDB();
        return true;
    }

    function setTabTitle($immobili_tab) {
        $this->tabTitle = "Dati Catastali";
        foreach ($immobili_tab as $immobili_rec) {
            if ($immobili_rec['CTRRET'] == 2 || $immobili_rec['CTRRET'] == 3) {
                $this->tabTitle = "<span style=\"display:inline-block;vertical-align:text-top;\" class=\"ita-icon ita-icon-yellow-alert-16x16\"></span> Dati Catastali";
                break;
            }
        }
    }

    function getTabTitle() {
        return $this->tabTitle;
    }

    /**
     * Restituisce l'array degli immobili
     * @return type array
     */
    public function getImmobili() {
        return $this->immobili;
    }

    /**
     * 
     * @param type $rowid
     * @return array
     */
    public function getImmobile($rowid) {
        return $this->immobili[$rowid];
    }

    /**
      Restituisce array formattato per essere
     * visualizzato in una tabella
     * @return array
     */
    public function getGriglia() {
        $praimm_tab = $this->immobili;
        foreach ($praimm_tab as $key => $praimm_rec) {
            if ($praimm_rec['TIPO'] == "F") {
                $praimm_tab[$key]['TIPOUNITA'] = "Fabbricato";
            } elseif ($praimm_rec['TIPO'] == "T") {
                $praimm_tab[$key]['TIPOUNITA'] = "Terreno";
            }

            $color = "";
            switch ($praimm_rec['CTRRET']) {
                case 2:
                case 3:
                    $color = "red";
                    break;
                case 1:
                case 4:
                    $color = "green";
                    break;
            }
            if ($color) {
                $praimm_tab[$key]['ESITO'] = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"" . $praimm_rec['CTRMSG'] . "\"><span class=\"ita-icon ita-icon-check-$color-24x24\"></span></span></div>";
            }
        }
        return $praimm_tab;
    }

    /**
     * 
     * @return array
     */
    private function GetImmobiliFromDB() {
        if ($this->pratica) {
            $praLib = new praLib();
            $immobili_tab = $praLib->GetPraimm($this->pratica, "codice", true);
            $this->setTabTitle($immobili_tab);
            return $immobili_tab;
        } else {
            return array();
        }
    }

    /**
     * Cancella immobile sia da array che da DB
     * @param int $rowid id del rig aimmobile su DB
     * @param object $model oggetto model che esegue traccia la cancellazione
     * @return boolean
     */
    public function CancellaImmobile($rowid, $model) {
        $praLib = new praLib();
        if (array_key_exists($rowid, $this->immobili) == true) {
            if ($this->immobili[$rowid]['ROWID'] != 0) {
                $delete_Info = 'Oggetto: Cancellazione immobile pratica' . $this->immobili[$rowid]['PRONUM'];
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'PRAIMM', $this->immobili[$rowid]['ROWID'], $delete_Info)) {
                    return false;
                }
            }
            unset($this->immobili[$rowid]);
        }
        $this->ordinaSequenzaImmobili();
        return true;
    }

    /**
     * Cancella tutti gli immobili di una pratica
     * @return boolean
     */
    public function CancellaImmobili($model) {
        if ($this->immobili) {
            $praLib = new praLib();
            $delete_Info = "Oggetto: Cancellazione di tutti gli immobili della pratica $this->pratica";
            foreach ($this->immobili as $immobile) {
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'PRAIMM', $immobile['ROWID'], $delete_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Carica una nuova riga oppure aggiorna una riga esistente
     * @param int $rowid
     * @param array $newImmobile<br>
     *              TIPO<br>
     *              SEZIONE<br>
     *              FOGLIO<br>
     *              PARTICELLA<br>
     *              SUBALTERNO<br>
     *              NOTE
     */
    function CaricaImmobile($rowid, $newImmobile) {
        if ($rowid == "") {
            $this->immobili[] = array(
                "ROWID" => 0,
                "PRONUM" => $this->pratica,
                "SEQUENZA" => 999999
            );
            end($this->immobili);
            $i = key($this->immobili);
        } else {
            $i = $rowid;
        }
        $this->immobili[$i]['TIPO'] = $newImmobile['TIPO'];
        if ($newImmobile['SEZIONE']) {
            $this->immobili[$i]['SEZIONE'] = str_pad($newImmobile['SEZIONE'], 3, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['FOGLIO']) {
            $this->immobili[$i]['FOGLIO'] = str_pad($newImmobile['FOGLIO'], 4, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['PARTICELLA']) {
            $this->immobili[$i]['PARTICELLA'] = str_pad($newImmobile['PARTICELLA'], 5, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['SUBALTERNO']) {
            $this->immobili[$i]['SUBALTERNO'] = $newImmobile['SUBALTERNO'];
        }
        $this->immobili[$i]['NOTE'] = $newImmobile['NOTE'];
        $this->immobili[$i]['CODICE'] = $newImmobile['CODICE'];
        $this->OrdinaSequenzaImmobili();
        return true;
    }

    function ValidaImmobile($rowid, $newImmobile, $model) {
        if ($rowid == "") {
            $this->immobili[] = array(
                "ROWID" => 0,
                "PRONUM" => $this->pratica,
                "SEQUENZA" => 999999
            );
            end($this->immobili);
            $i = key($this->immobili);
        } else {
            $i = $rowid;
        }
        //
        $this->immobili[$i]['TIPO'] = $newImmobile['TIPO'];
        if ($newImmobile['SEZIONE']) {
            $this->immobili[$i]['SEZIONE'] = str_pad($newImmobile['SEZIONE'], 3, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['FOGLIO']) {
            $this->immobili[$i]['FOGLIO'] = str_pad($newImmobile['FOGLIO'], 4, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['PARTICELLA']) {
            $this->immobili[$i]['PARTICELLA'] = str_pad($newImmobile['PARTICELLA'], 5, "0", STR_PAD_LEFT);
        }
        if ($newImmobile['SUBALTERNO']) {
            $this->immobili[$i]['SUBALTERNO'] = $newImmobile['SUBALTERNO'];
        }
        $this->immobili[$i]['NOTE'] = $newImmobile['NOTE'];
        $this->immobili[$i]['CODICE'] = $newImmobile['CODICE'];
        $this->immobili[$i]['CTRRET'] = 4;
        $this->immobili[$i]['CTRMSG'] = "I dati catastali sono stati validati dall'operatore di Back Office.";
        $this->OrdinaSequenzaImmobili();
        $msg = $this->RegistraImmobili($model);
        if ($msg != true) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $immobili
     * @return boolean|int
     */
    function ordinaSequenzaImmobili() {
        if (!$this->immobili) {
            return false;
        }
        $new_seq = 0;
        foreach ($this->immobili as $key => $immobile) {
            $new_seq += 10;
            $this->immobili[$key]['SEQUENZA'] = $new_seq;
        }
    }

    /**
     * Inserisce o aggiorna un immobili sul DB
     * 
     * @param object $model
     * @return boolean
     */
    function RegistraImmobili($model) {
        $praLib = new praLib();
        foreach ($this->immobili as $immobile) {
            if ($immobile['ROWID'] == 0) {
                $insert_Info = "Oggetto: Inserisco l'immobile della pratica " . $immobile['PRONUM'];
                if (!$model->insertRecord($praLib->getPRAMDB(), 'PRAIMM', $immobile, $insert_Info)) {
                    return "Errore Inserimento Immobile pratica " . $immobile['PRONUM'];
                }
            } else {
                $update_Info = "Oggetto: Aggiorno l'immobile della pratica " . $immobile['PRONUM'];
                if (!$model->updateRecord($praLib->getPRAMDB(), 'PRAIMM', $immobile, $update_Info)) {
                    return "Errore Aggiornamento Immobile pratica " . $immobile['PRONUM'];
                }
            }
        }
        return true;
    }

    function RinumeraImmobili($model, $anno, $newNumero) {
        if ($this->immobili) {
            $praLib = new praLib() ;
            $update_Info = "Oggetto: Rinumero Immobili da " . $this->immobili[0]['PRONUM'] . " a $anno$newNumero";
            foreach ($this->immobili as $immobile) {
                $immobile['PRONUM'] = $anno . $newNumero;
                if (!$model->updateRecord($praLib->getPRAMDB(), 'PRAIMM', $immobile, $update_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

}
