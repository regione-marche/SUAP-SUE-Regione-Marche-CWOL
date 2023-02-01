<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateRapporto extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");

        $tipoPasso = 'Download Rapporto Completo';

        $img_base = frontOfficeLib::getIcon('rapporto-completo');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO

        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$href\" style = \"border:0px;\" />";
        } else {
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$img_base\" style=\"border:0px;\" />";
        }

        $azione_img_tag = '<i class="icon ion-compose italsoft-icon"></i><span>Crea e Scarica</span>';

        /*
         * Controllo rapporto e allegati mancanti
         */
        $activeHref = true;
        $url = ItaUrlUtil::GetPageUrl(array('event' => 'seqClick',
                'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $Anapar_rec_VerInteg = $praLib->GetAnapar('VERIFICA_INTEGRITA_DRR', 'parkey', $extraParms["PRAM_DB"], false);
        if ($Anapar_rec_VerInteg['PARVAL'] == "No") {
            $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"" . $url . "\">$azione_img_tag</a>";
        } else {
            $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"#\" onclick=\"scaricaDRR('$url','" . $dati['Proric_rec']['RICNUM'] . "'); return false;\">$azione_img_tag</a>";
        }

        /*
         * Istanzio la classe per costruire la tabella degli allegati o dei passi mancanti
         */
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
        $praHtmlGridAllegati = new praHtmlGridAllegati();

        /*
         * Se anche passo raccolta dati con XHTML, verifico se ci sono delle raccolte che non sono state fatte
         */
        if (($dati['Ricite_rec']['ITEDAT'] == 1 || $dati['Ricite_rec']['ITERDM'] == 1) && $dati['Ricite_rec']['ITEMETA']) {
            $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
            if ($metadati === false) {
                //Errore unserialize
                return false;
            }
            $arrayPassiRaccolte = $praLib->ControllaRaccolteConfig($dati, $dati['Ricite_rec'], $extraParms['PRAM_DB']);
            if ($arrayPassiRaccolte) {
                $manca = $praLib->checkPassiMancanti($arrayPassiRaccolte, $dati['Proric_rec']);
                if ($manca != 0) {
                    $h_ref = $azione_img_tag;
                    $activeHref = false;
                }
            } else {
                $h_ref = $azione_img_tag;
                $activeHref = false;
            }
            $htmlGrid = $praHtmlGridAllegati->GetGridPassi($arrayPassiRaccolte, $dati['Proric_rec'], $extraParms);

            /*
             * Html Bottone Rigenera distinta solo se admin
             */
            if (strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'admin' || strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'pitaprotec') {
                $hrefGenera = ItaUrlUtil::GetPageUrl(array('event' => 'rigeneraDistinta', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'seq' => $dati['seq']));
                $buttonRigeneraPdf = "<br /><div style=\"margin:10px;display:inline-block;\">
                                            <button style=\"cursor:pointer;\" name=\"rigenera\" class=\"italsoft-button\" type=\"button\" onclick=\"location.href='$hrefGenera';\">
                                                 <i class=\"icon ion-document italsoft-icon\"></i>
                                                 <div class=\"\" style=\"display:inline-block;\"><b>Rigenera PDF</b></div>
                                            </button>
                                          </div>";
            }
        } elseif ($dati['Ricite_rec']['ITEWRD'] && strtolower(pathinfo($dati['Ricite_rec']['ITEWRD'], PATHINFO_EXTENSION)) == 'docx') {
            /*
             * Controllo i passi da cui dipendono il rapporto completo (flag PDR)
             */
            foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
                if ($ricite_rec['CLTOFF'] == 0 && ($ricite_rec['RICOBL'] != 0 || $ricite_rec['CLTOBL'] != 0) && $ricite_rec['ITEPDR'] != 0) {
                    if (strpos($dati['Proric_rec']['RICSEQ'], '.' . $ricite_rec['ITESEQ'] . '.') === false) {
                        $h_ref = $azione_img_tag;
                        $activeHref = false;
                        break;
                    }
                }
            }
        } else {
            /*
             * Controllo che tutti i passi che accorpano siano stati fatti
             */
            $arrayPdf = $praLib->ControllaRapportoConfig($dati, $dati['Ricite_rec'], $extraParms['PRAM_DB']);
            if ($arrayPdf) {
                $manca = $praLib->checkAllegatiMancanti($arrayPdf);
                if ($manca != 0) {
                    $h_ref = $azione_img_tag;
                    $activeHref = false;
                }
            } else {
                $h_ref = $azione_img_tag;
                $activeHref = false;
            }
            $htmlGrid = $praHtmlGridAllegati->GetGridRapporto($dati, $extraParms);
        }


        //
        // Siamo in modalita sola consultazione
        //
        if ($dati['Consulta'] == true) {
            $h_ref = $azione_img_tag;
            $activeHref = false;
        }

        // Blocco tutti gli haref precedenti a invio richiesta
        // se pratica inviata a infocamenre con web service
        //
        if ($dati['Note_Infocamere']['INFOCAMERE']['DATE'] && $dati['Ricite_rec']['ITEIRE'] == 0) {
            $h_ref = $azione_img_tag;
            $activeHref = false;
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
        //$html->appendHtml("<div style=\"display:inline-block;font-size:1.5em;\" class=\"descrizioneAzione\">Crea e Scarica il Rapporto Completo della Richiesta</div>");
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();

        $divMsgConferma = "";
        if ($dati['Proric_rec']['RICSTA'] == "99") {
            if ($Anapar_rec_VerInteg['PARVAL'] == "Si") {
                $messaggioAlert = '<b>Quando sarà richiesto dal browser scegliere di salvare il file e non di aprirlo.<br>Salvando il file dal programma di visualizzazione dei file PDF si rischia che questo sia modificato<br>e non possa più essere caricato dopo la firma.</b>';
                $divMsgConferma .= $html->getAlert($messaggioAlert, 'Attenzione', 'warning');
            }
            if ($activeHref == true) {
                $messaggioAlert = '<b>Se non riesci a scaricare il Rapporto Completo</b> <a href="' . $url . '">clicca qui</a>.</b>';
                $divMsgConferma .= $html->getAlert($messaggioAlert);
            }

            if ($divMsgConferma) {
                $divMsgConferma = '<br /><br />' . $divMsgConferma;
            }
        }

        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Crea e Scarica il Rapporto Completo della Richiesta", $divMsgConferma));
        $html->appendHtml("<br>" . $buttonRigeneraPdf);
        //$html->appendHtml($divMsgConferma);
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
//        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
//        $praHtmlGridAllegati = new praHtmlGridAllegati();
        //Tabella Riepilogo Allegati
//        $html->appendHtml($praHtmlGridAllegati->GetGridRapporto($dati, $extraParms));
        $html->appendHtml($htmlGrid);
        $html->appendHtml($this->addJsConfermaScarico());
        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

    private function addJsConfermaScarico() {
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= '<b>Attenzione:<br>Quando sarà richiesto dal browser scegliere di salvare il file e non di aprirlo.<br>Salvando il file dal programma di visualizzazione dei file PDF si rischia che questo sia modificato<br>e non possa più essere caricato dopo la firma.</b><br><br>';
        $content .= '<b>Confermi lo scarico?</b></div>';
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
            function scaricaDRR(url, richiesta){
                $('<div id =\"praConfermaScaricoDRR\">$content</div>').dialog({
                title:\"Scarica PDF Rapporto Completo.\",
                bgiframe: true,
                resizable: false,
                height: 'auto',
                width: 'auto',
                modal: true,
                close: function(event, ui) {
                    $(this).dialog('destroy');
                },
                buttons: [
                    {
                        text: 'No',
                        class: 'italsoft-button italsoft-button--secondary',
                        click:  function() {
                            $(this).dialog('destroy');
                        }
                    },
                    {
                        text: 'Sì',
                        class: 'italsoft-button',
                        click:  function() {
                            $(this).dialog('destroy');
                            location.replace(url);
                        }
                    }
                ]
            });
            };";
        $script .= '</script>';
        return $script;
    }

}

?>
