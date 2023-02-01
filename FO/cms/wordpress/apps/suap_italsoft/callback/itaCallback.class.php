<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Callback Class
 *
 * @author Andrea Bufarini
 */
class itaCallback {
    public static function run($dati, $evento) {
        switch ($evento) {
            case 'regProcedimento':
                praSuap::log("CALLBACK:regProcedimento",$dati);
                break;
            case 'annullaPratica':
                praSuap::log("CALLBACK:regProcedimento",$dati);
                break;
            case 'invioMail':
                praSuap::log("CALLBACK:regProcedimento",$dati); 
                break;
        }
    }
}

?>
