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
        print_r("Sample Callback: " . $evento);
        return true;
    }
}

?>
