<?php

class praTemplateUploadIntegrazione extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        /*
         * Carico dati aggiuntivi
         */

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSostituzioni.class.php';
        $praLibSostituzioni = new praLibSostituzioni();
        $sostMetadata = unserialize($dati['Ricite_rec']['ITEMETA']);
        $sostMetadata = $sostMetadata[$praLibSostituzioni->metaKey];
        $sostData = $praLibSostituzioni->getDatiDocumento($sostMetadata, $dati['PRAM_DB']);

        //
        //Comincio il Disegno
        //
        
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");

        $url = ItaUrlUtil::GetPageUrl(array());

        $html->addForm($url, 'POST', array(
            'enctype' => 'multipart/form-data'
        ));

        $html->addHidden("event", 'seqClick');
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        if ($dati['Ricite_rec']['ITEQCLA'] == '1' && $sostMetadata['CLASSIFICAZIONE']) {
            $html->addHidden("QualificaAllegato[CLASSIFICAZIONE]", $sostMetadata['CLASSIFICAZIONE']);
        }

        if ($dati['Ricite_rec']['ITEQDEST'] == '1' && $sostMetadata['DESTINAZIONE']) {
            foreach ($sostMetadata['DESTINAZIONE'] as $num => $value) {
                $html->addHidden("QualificaAllegato[DESTINAZIONE][$num]", $value);
            }
        }

        $tipoPasso = 'Upload';

        $img_base = frontOfficeLib::getIcon('upload');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        /*
         * --- Inizio Dati Aggiuntivi
         */

        $divDati = '<div style="background-color: #fafafa; margin: 10px 20px 20px 10px; padding: 10px; border: 1px solid #bbb; font-size: 1em; border-radius: 5px;">';
        $divDati .= '<div style="color: blue; font-weight: bold; margin-bottom: 10px; font-size: 1.1em;">Informazioni sul file da sostituire</div>';

        foreach ($sostData as $label => $value) {
            $divDati .= "<label class=\"descrizioneAzione\" style=\"margin: 6px; width: 230px; display: inline-block; vertical-align: top;\"><b>$label</b></label>";
            $divDati .= "<div style=\"margin: 6px; display: inline-block;\">$value</div><br>";
        }

        $divDati .= '</div>';

        if ($dati['Ricite_rec']['ITEQALLE'] == 1 && $dati['Ricite_rec']['ITEQNOTE'] == 1) {
            $div_upload .= "<label class=\"descrizioneAzione\" style=\"width:230px;display:inline-block;vertical-align:top;\"><b>Note Integrazione</b></label>";
            $div_upload .= "<textarea cols=\"40\" rows=\"5\" name=\"QualificaAllegato[NOTE]\"></textarea><br><br>";
        }

        /*
         * --- Fine Dati Qualifica
         */

        // 3 --  INIZIO BOX INFO

        if (!$dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
            $div_upload .= "<label class=\"descrizioneAzione\" style=\"width:230px;display:inline-block;vertical-align:top;\"><b>Allega Documento</b></label>";
            $div_upload .= "<input size=\"50\" id=\"brsw_ita_upload\" name=\"ita_upload\" type=\"file\" class=\"italsoft-dragndrop-upload\"/>";
            // Bottone Conferma
            $div_upload .= "<br><br /><div style=\"text-align:center;margin:10px;\">";
            $div_upload .= "<button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"italsoft-button\" type=\"submit\">";
            $div_upload .= '<i class="icon ion-arrow-up-a italsoft-icon"></i>';
            $div_upload .= "<div class=\"\" style=\"display:inline-block;\"><b>Invia File</b></div>";
            $div_upload .= "</button>";

            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUploadCartella.class.php';
            $praTemplateUploadCartella = new praTemplateUploadCartella();
            $div_upload .= ' ' . $praTemplateUploadCartella->getHtmlButtonIncorpora($dati);

            $div_upload .= "</div>";
        }

        //
        // Creao vettore di nomi files upload allegati al passo
        //
        
        $results = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($dati['seq'], $dati['seqlen'], "0", STR_PAD_LEFT));
        foreach ($results as $key => $alle) {
            $ext = pathinfo($alle, PATHINFO_EXTENSION);
            if ($ext == 'info' || $ext == 'err') {
                unset($results[$key]);
            }
        }

        sort($results);

        //
        // Verifiche su eseguibilità azione, in caso negativo sovrascrivo
        // $h_ref già stablito con la sola immagine
        //
        //
        // Passo Upload singolo già effettuato
        //
        
        if (count($results) >= 1 && $dati['Ricite_rec']['ITEMLT'] == 0 && $dati['Ricite_rec']['ITEUPL'] != 0 && $dati['Ricite_rec']['RICERF'] == 0) {
            $div_upload = "<div style=\"color:red;font-size:1.2em;text-align:center;\"><b>Upload singolo già effettuato, altri upload non consentiti.</b></div>";
        }

//        if ($dati['Browser'] != Browser::BROWSER_FIREFOX) {
//            $h_ref = $azione_img_tag;
//        }
        //
        // Siamo in modalita sola consultazione
        //
        if ($dati['Consulta'] == true) {
            $div_upload = "";
        }

        // Blocco tutti gli haref precedenti a invio richiesta
        // se pratica inviata a infocamenre con web service
        //
        if ($dati['Note_Infocamere']['INFOCAMERE']['DATE'] && $dati['Ricite_rec']['ITEIRE'] == 0) {
            $div_upload = "";
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

        $html->appendHtml("<div style=\"height:auto;\" class=\"divAction\">");

        //
        // Costruisco box info
        //
        
        $html->appendHtml("<div style=\"height:auto; width: 100%;\" class=\"ui-widget-content ui-corner-all boxInfo\">");
        //
        $html->appendHtml("<div class=\"divContenitore\">");
        $html->appendHtml("<div class=\"divTipoPasso legenda\" style=\"display:inline-block;vertical-align:top;padding-right:10px;\">Tipo passo: $tipoPasso");
        $html->appendHtml("</div>"); //div tipo passo

        $msgErr = "";

        if ($dati['Ricite_rec']['RICERF'] == 1 || $dati['Ricite_rec']['RICERM'] != '') {
            $msgErr = $html->getAlert($dati['Ricite_rec']['RICERM'], 'Errore nei dati', 'error');
        }
        //elseif ($extraParms['ERRUPL'] == 1) {
        //    $msgErr = "ATTENZIONE! L'estensione del file non corrisponde alle estensioni previste.</b><br>PASSO NON ESEGUITO.";
        //}
        $html->appendHtml("<div class=\"divPassiObl\" style=\"padding-right:10px;display:inline-block;float:right;\">$descObl");
        $html->appendHtml("</div>"); //div passi obl
        $html->appendHtml("</div>"); //div contenitore


        $html->appendHtml($msgErr); //div Error
        //
        // Informazioni su upload del passo
        //

        $arrayExt = $praLib->Estensioni($dati['Ricite_rec']['ITEEXT']);

        if ($arrayExt) {
            $html->appendHtml("Upload di file solo con le seguenti estensioni: ");
            foreach ($arrayExt as $ext) {
                if ($ext == 'pdfa') {
                    $ext = "pdf in modalità PDF/A";
                }
                $html->appendHtml("<span style=\"font-size:1.8em;\" class=\"legenda\"><b>" . $ext . "</b></span>" . '&nbsp');
            }
            $html->appendHtml("<br>");
            $html->appendHtml("<br>");
        }

        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'admin') {
            $controlli = unserialize($dati['Ricite_rec']['RICNOT']);
            if ($dati['Ricite_rec']['ITECTP']) {
                $href = ItaUrlUtil::GetPageUrl(array('event' => 'eliminaControlli', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'seq' => $dati['seq']));
                $html->appendHtml("<a href=\"$href\"><input type=\"button\" value=\"Elimina Controlli\" name=\"delCtr\"></a>");
            } else if ($dati['Ricite_rec']['ITECTP'] == "" && $controlli['ITECTP'] != 0) {
                $href = ItaUrlUtil::GetPageUrl(array('event' => 'ripristinaControlli', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'seq' => $dati['seq']));
                $html->appendHtml("<a href=\"$href\"><input type=\"button\" value=\"Ripristina Controlli\" name=\"addCtr\"></a>");
            }
        }

        // -- Tariffa del passo
        if ($dati['Ricite_rec']['TARIFFA'] != 0) {
            $html->appendHtml("<div style=\"font-size: 18px; font-weight: bold; margin: 15px 5px 5px;\">L'importo per questo passo è di Euro " . $dati['Ricite_rec']['TARIFFA'] . "</div><br>");
        }

        $html->appendHtml($divDati);

        $html->appendHtml($div_upload);

        // 4 -- NOTE DEL PASSO

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNote.class.php';
        $praHtmlNote = new praHtmlNote();
        $html->appendHtml($praHtmlNote->GetNote($dati));

        // 4 -- FINE NOTE DEL PASSO

        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());

        // 7 -- FINE LEGENDA

        $html->appendHtml("<div style=\"height:auto;width:100%;\" class=\"divAllegati\">");

        if ($results) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            // Tabella Allegati
            $html->appendHtml($praHtmlGridAllegati->GetGrid($dati, $results, $extraParms));
        }

        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");

        $html->appendHtml("</div>");

        return $html->getHtml();
    }

}
