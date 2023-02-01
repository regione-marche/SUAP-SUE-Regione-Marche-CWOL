<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlNote
 *
 * @author Andrea
 */
class praHtmlNote {

    function GetNote($dati) {
        $style = "";
        if ($dati['Ricite_rec']['ITENOTSTYLE']) {
            $style = $dati['Ricite_rec']['ITENOTSTYLE'];
        }
        
        if ( !$dati['Ricite_rec']['ITENOT'] ) {
            return "";
        }
        
        $html = new html();
        $html->appendHtml("<div style=\"margin-top: 1em;\" class=\"italsoft-alert italsoft-alert--info\">");
        $html->appendHtml($dati['Ricite_rec']['ITENOT']);
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

}

?>
