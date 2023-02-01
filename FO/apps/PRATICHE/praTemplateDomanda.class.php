<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateDomanda extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {


        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");

        $tipoPasso = 'Domanda';

        $img_base = frontOfficeLib::getIcon('question');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO

        $url_si = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'seqClick',
                    'seq' => $dati['Ricite_rec']['ITESEQ'],
                    'ricnum' => $dati['Proric_rec']['RICNUM'],
                    'risposta' => 'SI'
        ));

        $url_no = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'seqClick',
                    'seq' => $dati['Ricite_rec']['ITESEQ'],
                    'ricnum' => $dati['Proric_rec']['RICNUM'],
                    'risposta' => 'NO'
        ));

        $style_si = $style_no = array('circled', 'inline');

        if ($dati['Note_Infocamere']['INFOCAMERE']['DATE'] || $dati['Consulta'] == true) {
            $url_si = $url_no = '#';
        }

        if ($dati['Ricite_rec']['RCIRIS'] == "SI") {
            $style_no[] = 'secondary';
            $risposta = 'SI';
        }

        if ($dati['Ricite_rec']['RCIRIS'] == "NO") {
            $style_si[] = 'secondary';
            $risposta = 'NO';
        }

        if ($dati['Ricite_rec']['RCIRIS'] == '') {
            $style_si[] = 'secondary';
            $style_no[] = 'secondary';
            $risposta = 'Nessuna';
        }

        $divRisposta = '<br /><div>';
        //$divRisposta .= "<div style=\"display:inline-block;font-size:1.5em;\" class=\"descrizioneAzione\">Rispondi alla Domanda</div>";
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();
        //$divRisposta .= $praHtmlDescBox->GetDescBox($dati, "Rispondi alla Domanda");
        $divRisposta .= $praHtmlDescBox->GetDescBox($dati, $dati['Ricite_rec']['ITEDES']);
        // 5 -- FINE DESCRIZIONE BOX
//        $divRisposta .= "<div style=\"$borderSi;margin:5px;display: inline-block;height:70px;width: 70px;\" class=\"buttonlink ui-corner-all ui-widget-header\">$href_si</div>";
//        $divRisposta .= "<div style=\"$borderNo;margin:5px;display: inline-block;height:70px;width: 70px;\" class=\"buttonlink ui-corner-all ui-widget-header\">$href_no</div>";

        $divRisposta .= '<div style="display: inline-block; font-size: 1.5em; margin: .3em 0 .3em .5em;">';
        $divRisposta .= $html->getButton('Sì', $url_si, $style_si);
        $divRisposta .= '<span style="display: inline-block; width: .4em;"></span>';
        $divRisposta .= $html->getButton('No', $url_no, $style_no);
        $divRisposta .= '</div>';

        $divRisposta .= '<br /><br />';

        $divRisposta .= "<div class=\"descrizioneAzione\"><small>Risposta Data: $risposta</small></div>";
        $divRisposta .= "</div>";


//        if ($dati['Browser'] != Browser::BROWSER_FIREFOX) {
//            $h_ref = $azione_img_tag;
//        }
        //
        // Siamo in modalita sola consultazione
        //
        if ($dati['Consulta'] == true) {
            $h_ref = $azione_img_tag;
        }

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

        $html->appendHtml($divRisposta);


        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

}

?>
