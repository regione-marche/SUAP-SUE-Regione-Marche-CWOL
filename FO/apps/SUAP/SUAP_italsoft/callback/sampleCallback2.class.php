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

class ita_suap_Callback {
    public static function run($dati, $evento) {
        require_once ITA_SUAP_PATH . '/praExport.class.php';
        $praExport = new praExport();
        $result=$praExport->createZip($dati['Proric_rec']['RICNUM'], $dati['CartellaTemporary']);
        if(!$result) {
            return false;
        }else {
            //print_r($result);
            return true;
        }
    }
}

?>
