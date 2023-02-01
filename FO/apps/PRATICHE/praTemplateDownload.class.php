<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateDownload extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {


        $html = new html();
        $praLib = new praLib();
        //
        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");

        $tipoPasso = 'Download';

        $img_base = frontOfficeLib::getIcon('download');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO
        $alternativLink = "";
        if ($dati['Ricite_rec']['ITEWRD'] != "") {
            $url = ItaUrlUtil::GetPageUrl(array('event' => 'seqClick', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        }

        $azione_img_tag = "";
        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$href\" style = \"border:0px;\" />";
        } else {
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$img_base\" style=\"border:0px;\" />";
        }

        $azione_img_tag = '<i class="icon ion-arrow-down-a italsoft-icon"></i><span>Scarica</span>';

        $url = ItaUrlUtil::GetPageUrl(array('event' => 'seqClick',
                    'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"" . $url . "\">$azione_img_tag</a>";

        // Blocco tutti gli haref precedenti a invio richiesta
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
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();
        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Scarica l'Allegato"));
        // 5 -- FINE DESCRIZIONE BOX
        //
        
        if ($h_ref != $azione_img_tag) {
            $html->appendHtml("<div class=\"buttonlink\">$h_ref</div>");
        }

        if ($dati['Consulta'] != true) {
            $html->appendHtml("<div style=\"display:inline-block;float:right;padding:10px;\">$alternativLink</div>");
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
        // 7 -- INIZIO LEGENDA
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        //
        // 3 -- FINE BOX INFO
        $html->appendHtml("</form>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

}

?>
