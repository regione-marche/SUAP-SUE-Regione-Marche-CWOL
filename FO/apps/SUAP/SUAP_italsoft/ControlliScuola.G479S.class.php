<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praErr
 *
 * @author Andrea
 */
class ControlliScuola {

    public $workDate;

    function __construct() {
        
    }

    function ctrDoppioni($raccolta) {
        $arrayTrovati = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "";
        $ritorno['RetValue'] = true;

        /*
         * Ciclo per controllare che nei valori immessi non ci sia 1
         */
        for ($i = 2; $i <= 30; $i++) {
            if ($raccolta["SCELTA$i"]) {
                if (trim($raccolta["SCELTA$i"]) == "1") {
                    $ritorno['Status'] = "-1";
                    $ritorno['Message'] = "Inserire le preferenze iniziando dal numero 2";
                    $ritorno['RetValue'] = false;
                    return $ritorno;
                }
            }
        }

        /*
         * Ciclo per verificare se ci sono doppioni nelle preferenze
         */
        for ($i = 2; $i <= 30; $i++) {
            if ($raccolta["SCELTA$i"]) {
                if (in_array($raccolta["SCELTA$i"], $arrayTrovati)) {
                    //return false;
                    $ritorno['Status'] = "-1";
                    $ritorno['Message'] = "Non ci possono essere preferenze uguali.<br>Verificare i dati inseriti";
                    $ritorno['RetValue'] = false;
                    return $ritorno;
                } else {
                    $arrayTrovati[] = $raccolta["SCELTA$i"];
                }
            }
        }
        //return true;
        return $ritorno;
    }

    function GetNomeDistinta($dati) {
        return "IstanzaEducativi_" . $dati['Proric_rec']['RICNUM'] . ".pdf";
    }

}

?>
