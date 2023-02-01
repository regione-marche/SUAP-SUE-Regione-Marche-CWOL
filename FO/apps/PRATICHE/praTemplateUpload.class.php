<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateUpload extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {


        $html = new html();
        $praLib = new praLib();
        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $url = ItaUrlUtil::GetPageUrl(array());
//        $url = ItaUrlUtil::GetPageUrl(array(
//                    'model' => 'sueGestPassi',
//                    'event' => 'seqClick',
//                    'seq' => $dati['Ricite_rec']['ITESEQ'],
//                    'ricnum' => $dati['Ricite_rec']['RICNUM'])
//        );

        $html->addForm($url, 'POST', array(
            'enctype' => 'multipart/form-data'
        ));

        $html->addHidden("event", 'seqClick');
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        $tipoPasso = "Upload";
        if ($this->CheckUploadRapportoCompleto($dati)) {
            $tipoPasso = "Upload del Rapporto Completo";
        }

        $img_base = frontOfficeLib::getIcon('upload');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        if ($dati['Ricite_rec']['ITEQALLE'] == 1) {
            $readonly = '';
            $etichetta = 'Descrizione:';

            /*
             * Select  Classificazioni
             */
            if ($dati['Ricite_rec']['ITEQCLA'] == 1) {
                /*
                 * Trovo l'array delle classificazioni in base alle configurazzioni del BO
                 */
                $arrayOptions = $dati['Anacla_tab'];
                $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
                if (isset($metadati['CODICECLASSIFICAZIONE'])) {
                    $Anacla_figli_tab = $praLib->GetAnacla($metadati['CODICECLASSIFICAZIONE'], "padre", true, $extraParms['PRAM_DB']);
                    if ($Anacla_figli_tab) {
                        $arrayOptions = $Anacla_figli_tab;
                    } else {
                        $arrayOptions = $praLib->GetAnacla($metadati['CODICECLASSIFICAZIONE'], "codice", true, $extraParms['PRAM_DB']);
                    }
                }

                $div_upload .= "<label class=\"descrizioneAzione\" style=\"width:230px;display:inline-block\"><b>Classificazione</b></label>";
                $div_upload .= "<select style=\"max-width: 500px;\" name=\"QualificaAllegato[CLASSIFICAZIONE]\" id=\"QualificaAllegato[CLASSIFICAZIONE]\">";
                if (count($arrayOptions) > 1) {
                    $div_upload .= "<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>";
                }
                foreach ($arrayOptions as $option) {
                    $Sel = "";
                    if ($dati['Ricdag_rec']['RICDAT'] == $option['SPACOD']) {
                        //$Sel = "selected";
                    }
                    $div_upload .= "<option $Sel class=\"optSelect\" value=\"" . $option['CLACOD'] . "\">" . $option['CLADES'] . "</option>";
                }
                $div_upload .= "</select><br><br>";
            }

            /*
             * Select Destinazioni
             */
            if ($dati['Ricite_rec']['ITEQDEST'] == 1) {
                $div_upload .= "<div>";
                $div_upload .= "<div style=\"display:inline-block;vertical-align:top;\">";
                $div_upload .= "<label class=\"descrizioneAzione ita-label-dest\" style=\"width:230px;display:inline-block;\"><b>Destinazione</b></label>";
                $div_upload .= "</div>";
                $div_upload .= "<div style=\"display:inline-block;\" class=\"ita-conteiner-dest\">";
                $div_upload .= "<div id=\"divDest_0\" class=\"ita-div-upload-dest ita-div-upload-dest-template\">";
                $div_upload .= "<select style=\"max-width: 500px;\" id=\"QualificaAllegato_DESTINAZIONE_0\" class=\"ita-select-upload-dest\" name=\"QualificaAllegato[DESTINAZIONE][0]\">";
                $div_upload .= "<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>";
                foreach ($dati['Anaddo_tab'] as $Anaddo_rec) {
                    $Sel = "";
                    if ($dati['Ricdag_rec']['RICDAT'] == $Anaddo_rec['SPACOD']) {
                        //$Sel = "selected";
                    }
                    $div_upload .= "<option $Sel class=\"optSelect\" value=\"" . $Anaddo_rec['DDOCOD'] . "\">" . $Anaddo_rec['DDONOM'] . "</option>";
                }
                $div_upload .= "</select>";
                $div_upload .= "<button id=\"addDest_0\" name=\"addDest[0]\" style=\"font-size: .8em; margin-left: 5px; margin-bottom: 1.2em;\" class=\"ita-upload-dest-add-button italsoft-button italsoft-button--secondary\" type=\"button\">
                           <div class=\"buttonAddRem icon ion-plus italsoft-icon\"></div>
                           </button>";

                $div_upload .= "</div>";
                $div_upload .= "</div>";
                $div_upload .= "</div>";
            }

            /*
             * Note
             */
            if ($dati['Ricite_rec']['ITEQNOTE'] == 1) {
                $div_upload .= "<label class=\"descrizioneAzione\" style=\"width:230px;display:inline-block;vertical-align:top;\"><b>" . $etichetta . "</b></label>";
                $div_upload .= "<textarea $readonly cols=\"40\" rows=\"5\" name=\"QualificaAllegato[NOTE]\"></textarea><br><br>";
            }
        }

        /*
         * Selezione riservatezza allegato
         */
        if ($dati['Ricite_rec']['ITEFLRISERVATO']) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibRiservato.class.php';

            switch ($dati['Ricite_rec']['ITEFLRISERVATO']) {
                case praLibRiservato::SI_RISERVATO:
                    $div_upload .= "<div>";
                    $div_upload .= "<label class=\"descrizioneAzione\" style=\"width: 230px; display: inline-block;\"><b>Riservatezza</b></label>";
                    $div_upload .= "<select style=\"vertical-align: middle;\" id=\"RiservatezzaAllegato\" name=\"RiservatezzaAllegato\">";
                    $div_upload .= "<option value=\"1\">Riservato</option>;";
                    $div_upload .= "</select>";
                    $div_upload .= "</div><br>";
                    break;

                case praLibRiservato::CHIEDI_RISERVATO:
                    $div_upload .= "<div>";
                    $div_upload .= "<label class=\"descrizioneAzione\" style=\"width: 230px; display: inline-block;\"><b>Riservatezza</b></label>";
                    $div_upload .= "<select style=\"vertical-align: middle;\" id=\"RiservatezzaAllegato\" name=\"RiservatezzaAllegato\">";
                    $div_upload .= "<option value=\"0\">Non riservato</option>;";
                    $div_upload .= "<option value=\"1\">Riservato</option>;";
                    $div_upload .= "</select>";
                    $div_upload .= "</div><br>";
                    break;

                case praLibRiservato::RISERVATO_DA_ESPRESSIONE:
                    $risultatoExpr = $praLib->ctrExpression($dati['Ricite_rec'], $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain('', '.'), 'ITEEXPRRISERVATO');
                    if ($risultatoExpr) {
                        $div_upload .= "<div>";
                        $div_upload .= "<label class=\"descrizioneAzione\" style=\"width: 230px; display: inline-block;\"><b>Riservatezza</b></label>";
                        $div_upload .= "<select style=\"vertical-align: middle;\" id=\"RiservatezzaAllegato\" name=\"RiservatezzaAllegato\">";
                        $div_upload .= "<option value=\"1\">Riservato</option>;";
                        $div_upload .= "</select>";
                        $div_upload .= "</div><br>";
                    }
                    break;
            }
        }

        // 3 --  INIZIO BOX INFO
        //
//        if ($dati['Ricite_rec']['RICERF'] == 0) {
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
//        }
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
            if (!$dati['permessiPasso']['Insert']) {
                $div_upload = "";
            }

//            $div_upload = "";
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
            $tariffa = number_format($dati['Ricite_rec']['TARIFFA'], 2, ',', '.');
            $html->appendHtml("<div style=\"font-size: 18px; font-weight: bold; margin-bottom: 10px;\">L'importo per questo passo è di Euro $tariffa</div><br />");
            $msgTariffa = $praLib->checkTariffa($dati);
            if ($msgTariffa) {
                output::addAlert($msgTariffa, 'Attenzione', 'warning');
            }
        }


        /*
         * Messaggio FO tipo passo
         */
        $metadati = unserialize($dati['Praclt_rec']['CLTMETA']);
        $messaggioFO = $metadati['MESSAGGIOFO'];
        if ($messaggioFO) {
            $value = $praLib->elaboraTemplateDefault($messaggioFO, $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
            $html->appendHtml("<div class=\"italsoft-alert italsoft-alert--info\">$value</div><br>");
        }

        $html->appendHtml($div_upload);
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
        if ($results) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            // Tabella Allegati
            $html->appendHtml($praHtmlGridAllegati->GetGrid($dati, $results, $extraParms));
        }
        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        $html->appendHtml("<script type=\"text/javascript\" src=\"" . ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/js/destinazione.js?a=1\"></script>");
        //Errore file upload mancante
//        if ($extraParms['ERRUPL'] == 99) {
//            $html->appendHtml("<script type=\"text/javascript\">alert(\"ATTENZIONE!!!\\nSelezionare un file da caricare\");</script>");
//        }
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

    function CheckUploadRapportoCompleto($dati) {
        foreach ($dati['Navigatore']['Ricite_tab'] as $key => $Ricite_rec) {
            if ($dati['seq'] == $Ricite_rec['ITESEQ']) {
                if ($key > 0) {
                    $key = $key - 1;
                }
                $Ricite_rec = $dati['Navigatore']['Ricite_tab'][$key];
                break;
            }
        }
        if ($Ricite_rec['ITEDRR'] == 1) {
            return true;
        } else {
            return false;
        }
    }

}

?>
