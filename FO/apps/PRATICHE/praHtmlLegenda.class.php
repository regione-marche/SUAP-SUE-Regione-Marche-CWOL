<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlLegenda
 *
 * @author Andrea
 */
class praHtmlLegenda {

    function GetLegenda() {
        $html = new html();
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divLegenda\">");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Legenda Passo</b></div>");
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divColoreRosso\"></div>");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Obbligatorio</b></div>");
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divColoreVerde\"></div>");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Eseguito</b></div>");
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divColoreBlue\"></div>");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Domanda</b></div>");
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divColoreArancione\"></div>");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Facoltativo</b></div>");
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divColoreNavy\"></div>");
        $html->appendHtml("<div style=\"float:left;margin-right: 15px;\" class=\"divTesto\"><b>Invio Mail</b></div>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

}

class praHtmlLegendaLight {

    function GetLegenda() {
        $html = new html();
        $praHtmlNavOriz25 = new praHtmlNavOriz25();

        $html->appendHtml('<div style="margin: 1em 0 1.6em; padding: 0 .6em; font-size: .8em;"><span style="vertical-align: middle; font-weight: bold;">Legenda passi</span> ');
        foreach ($praHtmlNavOriz25->colorePasso as $label => $color) {
            if ($label === 'Invio Richiesta') {
                continue;
            }

            $html->appendHtml('<span class="italsoft-button italsoft-button--circled" style="font-size: .6em; margin: 0 .6em 0 1em; vertical-align: middle; background-color: ' . $color . ';"></span><span style="vertical-align: middle; font-size: .8em;">' . $label . '</span>');
        }
        $html->appendHtml('</div>');
        return $html->getHtml();

//        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all divLegendaLight\">");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Legenda Passo</b></div>");
//        $html->appendHtml("<div style=\"width:10px;height:10px;\" class=\"ui-widget ui-widget-content ui-corner-all divColoreRosso\"></div>");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Obbligatorio</b></div>");
//        $html->appendHtml("<div style=\"width:10px;height:10px;\" class=\"ui-widget ui-widget-content ui-corner-all divColoreVerde\"></div>");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Eseguito</b></div>");
//        $html->appendHtml("<div style=\"width:10px;height:10px;\" class=\"ui-widget ui-widget-content ui-corner-all divColoreBlue\"></div>");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Domanda</b></div>");
//        $html->appendHtml("<div style=\"width:10px;height:10px;\" class=\"ui-widget ui-widget-content ui-corner-all divColoreArancione\"></div>");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Facoltativo</b></div>");
//        $html->appendHtml("<div style=\"width:10px;height:10px;\" class=\"ui-widget ui-widget-content ui-corner-all divColoreNavy\"></div>");
//        $html->appendHtml("<div style=\"font-size:0.7em;margin-top:-4px;float:left;margin-right: 15px;\" class=\"divTesto\"><b>Invio Mail</b></div>");
//        $html->appendHtml("</div>");
//        return $html->getHtml();
    }

}

?>
