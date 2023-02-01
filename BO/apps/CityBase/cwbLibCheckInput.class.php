<?php

/**
 *
 * Utility CheckInput Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibCheckInput {

    /**
     * Controlla input numerico
     * @param string $valore Valore del campo da controllare
     * @param string $nomeCampo Nome campo
     * @param mixed $nomeCampiDecod Array che contiene i campi di decodifica da svuotare o il campo di decodifica come stringa     
     * @return true se numerico, altrimenti false
     */
    public static function checkNumeric($valore, $nomeCampo, $nomeCampiDecod = null) {
        $esito = false;
        if (($valore != null) && (trim($valore) !== "")) {
            if (!is_numeric($valore)) {
                Out::valore($nomeCampo, 0);
                if ($nomeCampiDecod != null) {
                    if (is_string($nomeCampiDecod)) {
                        Out::valore($nomeCampiDecod, '');
                    } else {
                        foreach ($nomeCampiDecod as $nomeCampoDecod) {
                            Out::valore($nomeCampoDecod, '');
                        }
                    }
                }
            } else {
                $esito = true;
            }
        }
        return $esito;
    }

    public static function checkGG($valore, $nomeCampo, $nomeCampiDecod = null) {
        $esito = false;
        if (($valore != null) && (trim($valore) !== "")) {
            $valore = str_pad($valore, 2, "0", STR_PAD_LEFT);
            if (preg_match("/(0[1-9]|[1-2][0-9]|3[0-1])/", $valore) == 0) {
                //if (!is_numeric($valore)) {
                Out::valore($nomeCampo, 0);
                if ($nomeCampiDecod != null) {
                    if (is_string($nomeCampiDecod)) {
                        Out::valore($nomeCampiDecod, '');
                    } else {
                        foreach ($nomeCampiDecod as $nomeCampoDecod) {
                            Out::valore($nomeCampoDecod, '');
                        }
                    }
                }
            } else {
                $esito = true;
            }
        }
        return $esito;
    }

    public static function checkMM($valore, $nomeCampo, $nomeCampiDecod = null) {
        $esito = false;
        if (($valore != null) && (trim($valore) !== "")) {
            $valore = str_pad($valore, 2, "0", STR_PAD_LEFT);
            if (preg_match("/(0[1-9]|1[0-2])/", $valore) == 0) {
                Out::valore($nomeCampo, 0);
                if ($nomeCampiDecod != null) {
                    if (is_string($nomeCampiDecod)) {
                        Out::valore($nomeCampiDecod, '');
                    } else {
                        foreach ($nomeCampiDecod as $nomeCampoDecod) {
                            Out::valore($nomeCampoDecod, '');
                        }
                    }
                }
            } else {
                $esito = true;
            }
        }
        return $esito;
    }

    public static function checkAAAA($valore, $nomeCampo, $nomeCampiDecod = null) {
        $esito = false;
        if (($valore != null) && (trim($valore) !== "")) {
            if (preg_match("/[0-9]{4}/", $valore) == 0) {
                Out::valore($nomeCampo, 0);
                if ($nomeCampiDecod != null) {
                    if (is_string($nomeCampiDecod)) {
                        Out::valore($nomeCampiDecod, '');
                    } else {
                        foreach ($nomeCampiDecod as $nomeCampoDecod) {
                            Out::valore($nomeCampoDecod, '');
                        }
                    }
                }
            } else {
                $esito = true;
            }
        }
        return $esito;
    }

    public static function IsNBZ($campo = '', $tipo = NULL) {
        $virgola = false;
        if (is_null($tipo)) {
            if (is_numeric($campo)) {
                $tipo = 0;
                if (is_float($campo)) {
                    $virgola = true;
                }
            } else {
                $tipo = 1;
            }
        }

        if (is_null($campo)) {
            return true;
        }

        //$campo = '"'.$campo.'"';
        $result = trim($campo);
        if (empty($result)) {    //0, null, '', ..
            return true;
        }

        if ($virgola == false) {
            If (($tipo == 0) && ($campo == 0)) {
                return true;
            }

//            If ((trim($campo)=='')||($campo==0)){
//                return true;
//            }
        }

        settype($decimal, 'int');
        $decimal = strtok($campo, ',');
        if (empty($decimal)) {
            return true;
        }

        return false;
    }

    public static function IsNBE($campo = '') {
        $virgola = false;
        $tipo = 1;

        if (is_null($campo)) {
            return true;
        }
        
        $result = trim($campo.' ');
        if ($result=='') {    //0, null, '', ..
            return true;
        }

        return false;
    }

}

?>