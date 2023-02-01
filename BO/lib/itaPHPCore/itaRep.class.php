<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaRepclass
 *
 * @author utente
 */
class itaRep {

    public static function Date($strDate) {
        if ($strDate){
            return date("d/m/Y", strtotime($strDate));
        }else{
            return "";
        }
    }

}
?>
