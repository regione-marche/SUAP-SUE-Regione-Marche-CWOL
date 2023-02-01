<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateDistinta extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");

        $tipoPasso = 'Distinta allegati';

        $img_base = frontOfficeLib::getIcon('notepad');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO

        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$href\" style = \"border:0px;\" />";
        } else {
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$img_base\" style=\"border:0px;\" />";
        }

        $azione_img_tag = '<i class="icon ion-compose italsoft-icon"></i><span>Crea e Scarica</span>';

        //Controllo rapporto e allegati mancanti
//        if ($dati['Ricite_rec']['ITEFILE'] == 1 && $dati['Ricite_rec']['ITEIDR'] == 1) {
//            $h_ref = $azione_img_tag;
//        } else {
        $url = ItaUrlUtil::GetPageUrl(array('event' => 'seqClick',
                    'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));

        $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"$url\">$azione_img_tag</a>";

        //}
        //
        // Siamo in modalita sola consultazione
        //
        if ($dati['Consulta'] == true) {
            $h_ref = $azione_img_tag;
        }

        // Blocco tutti gli href precedenti a invio richiesta
        // se pratica inviata a infocamenre con web service
        //
        if ($dati['Note_Infocamere']['INFOCAMERE']['DATE'] && $dati['Ricite_rec']['ITEIRE'] == 0) {
            $h_ref = $azione_img_tag;
        }

        //
        // Conteggio Passi Obbligatori completati
        //
        if ($dati['countEsg'] == $dati['countObl']) {
            $descObl = "Tutti i passi obbligatori sono completati: Puoi inviare la richiesta<br>";
        } else {
            if ($dati['countObl'] >= 1) {
                $descObl = "Passi obbligatori completati: " . $dati['countEsg'] . " di " . $dati['countObl'] . "<br>";
            } else {
                $descObl = "Passo obbligatorio completato <br> ";
            }
        }


        $html->appendHtml("<div class=\"divAction\">");
        //
        // Costruisco box info
        //
        
        $html->appendHtml("<div style=\"height:auto; width: 100%;\" class=\"ui-widget-content ui-corner-all boxInfo\">");
        //
        $html->appendHtml("<div class=\"divContenitore\">");
        $html->appendHtml("<div class=\"divTipoPasso legenda\" style=\"display:inline-block;\">Tipo passo: $tipoPasso");
        $html->appendHtml("</div>"); //div tipo passo
        $html->appendHtml("<div class=\"divPassiObl\" style=\"padding-right:10px;display:inline-block;float:right;\">$descObl");
        $html->appendHtml("</div>"); //div passi obl
        $html->appendHtml("</div>"); //div contenitore

        $html->appendHtml("<div>");
        //$html->appendHtml("<div style=\"display:inline-block;font-size:1.5em;\" class=\"descrizioneAzione\">Distinta Allegati inseriti</div>");
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();
        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Distinta Allegati inseriti"));
        // 5 -- FINE DESCRIZIONE BOX

        if ($h_ref != $azione_img_tag) {
            $html->appendHtml("<div class=\"buttonlink\">$h_ref</div>");
        }

        $html->appendHtml("</div>");

        
         /*
         * Messaggio FO tipo passo
         */
        $metadati = unserialize($dati['Praclt_rec']['CLTMETA']);
        $messaggioFO = $metadati['MESSAGGIOFO'];
        if ($messaggioFO) {
            $value = $praLib->elaboraTemplateDefault($messaggioFO, $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
            $html->appendHtml("<div class=\"italsoft-alert italsoft-alert--info\">$value</div><br>");
        }
        
        // 4 -- NOTE DEL PASSO
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNote.class.php';
        $praHtmlNote = new praHtmlNote();
        $html->appendHtml($praHtmlNote->GetNote($dati));
        // 3 -- FINE NOTE DEL PASSO

        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA

        $html->appendHtml("<div style=\"height:auto;width:100%;\" class=\"divAllegati\">");
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
        $praHtmlGridAllegati = new praHtmlGridAllegati();
        //Tabella Riepilogo Allegati
        $html->appendHtml($praHtmlGridAllegati->GetGridDistinta($dati, $extraParms));
        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");

        return $html->getHtml();
    }

}

?>
