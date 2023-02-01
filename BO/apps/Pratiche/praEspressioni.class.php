<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Andrea Bufarini  <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license 
 * @version    29.12.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praEspressioni {

    private $espressioni = array();
    public $itekey;

    /**
     * 
     * @param type $praLib non piu usato
     * @param type $itekey
     * @return object
     */
    public static function getInstance($praLib, $itekey = '') {
        try {
            $obj = new praEspressioni();
        } catch (Exception $exc) {
            return false;
        }

        if ($itekey) {
            $obj->itekey = $itekey;
            if (!$obj->caricaEspressioni()) {
                return false;
            }
        }
        return $obj;
    }

//
//    /**
//     * Carica dati Oggetto da DB
//     * @return boolean
//     */
    function caricaEspressioni() {
        $this->espressioni = $this->GetEspressioniFromDB();
        return true;
    }

//    /**
//     * 
//     * @return array
//     */
    function GetEspressioni() {
        return $this->espressioni;
    }

//
//    /**
//      Restituisce array formattato per essere
//     * visualizzato in una tabella
//     * @return array
//     */
    public function getGriglia() {
        $praLib = new praLib();
        $itecontrolli_tab = $this->espressioni;
        foreach ($itecontrolli_tab as $key => $itecontrolli_rec) {
            switch ($itecontrolli_rec["AZIONE"]) {
                case 1:
                    $itecontrolli_tab[$key]['AZIONE'] = "Continua";
                    break;
                case 2:
                    $itecontrolli_tab[$key]['AZIONE'] = "Blocca";
                    break;
            }
            $decode = $praLib->DecodificaControllo($itecontrolli_rec['ESPRESSIONE']);
            $color = "";
            if ($itecontrolli_rec['ROWID'] == 0)
                $color = "orange";
            $itecontrolli_tab[$key]["ESPRESSIONE"] = "<div class=\"ita-html\"><span style=\"color:$color;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$decode\">" . $itecontrolli_rec['ESPRESSIONE'] . "</span></div>";
        }
        return $itecontrolli_tab;
    }

//    /**
//     * 
//     * @return array
//     */
    private function GetEspressioniFromDB() {
        $praLib = new praLib();
        if ($this->itekey) {
            return $praLib->GetItecontrolli($this->itekey);
        } else {
            return array();
        }
    }

    function SetGridValue($rowid, $column, $value) {
        $this->espressioni[$rowid][$column] = utf8_decode($value);
    }

    /**
     * Cancella espressione sia da array che da DB
     * @param int $rowid id del riga espressione su DB
     * @param object $model oggetto model che esegue traccia la cancellazione
     * @return boolean
     */
    public function CancellaEspressione($rowid, $model) {
        $praLib = new praLib();
        if (array_key_exists($rowid, $this->espressioni) == true) {
            if ($this->espressioni[$rowid]['ROWID'] != 0) {
                $delete_Info = 'Oggetto: Cancellazione espressione passo' . $this->espressioni[$rowid]['ITEKEY'];
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'ITECONTROLLI', $this->espressioni[$rowid]['ROWID'], $delete_Info)) {
                    return false;
                }
            }
            unset($this->espressioni[$rowid]);
        }
        return true;
    }

    /**
     * Carica una nuova riga oppure aggiorna una riga esistente
     */
//    function CaricaEspressione($arrExpr, $azione) {
//        $strExpr = serialize($arrExpr);
    function CaricaEspressione($strExpr, $rowid) {
        $seq = 10;
        if ($rowid != "") {
            $this->espressioni[$rowid] = array(
                "ITEKEY" => $this->itekey,
                "SEQUENZA" => $this->espressioni[$rowid]['SEQUENZA'],
                "ESPRESSIONE" => $strExpr,
                "AZIONE" => $this->espressioni[$rowid]['AZIONE'],
                "MESSAGGIO" => $this->espressioni[$rowid]['MESSAGGIO'],
                "DESCRIZIONE" => $this->espressioni[$rowid]['DESCRIZIONE'],
                "ROWID" => $this->espressioni[$rowid]['ROWID'],
            );
        } else {
            $lastExp = end($this->espressioni);
            $seq = $lastExp['SEQUENZA'] + 10;
            $this->espressioni[] = array(
                "ITEKEY" => $this->itekey,
                "SEQUENZA" => $seq,
                "ESPRESSIONE" => $strExpr,
                "AZIONE" => 1,
                "ROWID" => 0,
            );
        }
        return true;
//        $seq = 10;
//        if ($this->espressioni) {
//            $lastExp = end($this->espressioni);
//            $seq = $lastExp['SEQUENZA'] + 10;
//        }
//        $this->espressioni[] = array(
//            "ITEKEY" => $this->itekey,
//            "SEQUENZA" => $seq,
//            "ESPRESSIONE" => $strExpr,
//            "AZIONE" => 1,
//            "ROWID" => 0,
//        );
//        return true;
    }

    /**
     * Inserisce o aggiorna un'espressione sul DB
     * 
     * @param object $model
     * @return boolean
     */
    function RegistraEspressioni($model) {
        $praLib = new praLib();
        if ($this->espressioni) {
            foreach ($this->espressioni as $espressione) {
                if ($espressione['ROWID'] == 0) {
                    $insert_Info = "Oggetto: Inserisco Espressione " . $espressione['ESPRESSIONE'] . " del passo" . $espressione['ITEKEY'];
                    if (!$model->insertRecord($praLib->getPRAMDB(), 'ITECONTROLLI', $espressione, $insert_Info)) {
                        return "Errore Inserimento Espressione " . $espressione['ESPRESSIONE'];
                    }
                } else {
                    $update_Info = "Oggetto: Aggiorno Espressione " . $espressione['ESPRESSIONE'] . " del passo" . $espressione['ITEKEY'];
                    if (!$model->updateRecord($praLib->getPRAMDB(), 'ITECONTROLLI', $espressione, $update_Info)) {
                        return "Errore Aggiornamento Espressione " . $espressione['ESPRESSIONE'];
                    }
                }
            }
        }
        return true;
    }

}

?>
