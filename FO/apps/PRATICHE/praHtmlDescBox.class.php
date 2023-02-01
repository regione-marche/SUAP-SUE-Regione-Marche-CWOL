<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlDescBox
 *
 * @author Andrea
 */
class praHtmlDescBox {

    function GetDescBox($dati, $descBox, $addMsg = "") {
        $html = new html();
        $praLib = new praLib();

        if ($dati['Ricite_rec']['ITEHTML']) {
            $testo = $dati['Ricite_rec']['ITEHTML'];
        } else {
            $metaDati = unserialize($dati['Praclt_rec']['CLTMETA']);
            if ($metaDati['MSGBOXFO']) {
                $testo = $metaDati['MSGBOXFO'];
            }
        }

        if ($testo) {
            $dictionaryValues_pre = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();
            $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
            $template = $praLib->elaboraTabelleTemplate($testo, $dictionaryValues, true);
            $descBox = $praLib->valorizzaTemplate($template, $dictionaryValues);
        }


        $html->appendHtml("<div id=\"DescBox\" style=\"display: inline-block; line-height: 20px; font-size: 1.5em;\" class=\"descrizioneAzione\">$descBox</div>");

        if ($addMsg)
            $html->appendHtml($addMsg);

        return $html->getHtml();
    }

}

?>
