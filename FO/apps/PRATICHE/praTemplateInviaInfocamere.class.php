<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateInviaInfocamere extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#", false, array('id' => 'form1', 'name' => 'form1'));
        //
        $Proric_rec = $dati['Proric_rec'];
        if (!$Proric_rec['CODICEPRATICASW']) {
            $html->addHidden("event", 'invioInfocamere');
        } else {
            $html->addHidden("event", 'invioInfocamereNEW');
        }

        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        $tipoPasso = 'Invio Infocamere';

        $img_base = ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/images/Comunica.png";
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO

        $azione_img_tag = "";
        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$href\" style = \"border:0px;\" />";
        } else {
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$img_base\" style=\"border:0px;\" />";
        }

        $azione_img_tag = '<i class="icon ion-paper-airplane italsoft-icon"></i><span>Conferma ed Invia</span>';


        //preparo hRef
        if ($dati['countObl'] == $dati['countEsg']) {
            $arrayNote = unserialize($dati['Ricite_rec']['RICNOT']);
            //$urlZip = ItaUrlUtil::GetPageUrl(array('model' => 'praGestPassi', 'event' => 'invioInfocamere', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            if ($dati['dati_infocamere']['checks']['Errors'] == 0) {
                //if (!$arrayNote && $dati['dati_infocamere']['datiImpresa']) {
                if (!isset($arrayNote['INFOCAMERE']) && $dati['dati_infocamere']['datiImpresa']) {
                    if (ITA_JVM_PATH != "" && file_exists(ITA_JVM_PATH) && (file_exists(ITA_LIB_PATH . "/java/itaStarWebWS.properties") || file_exists(ITA_LIB_PATH . "/java/itaComunicaWS.properties"))) {
                        $title = "Invio file ad Infocamere in corso";

                        /*
                         * record parametro blocca invio
                         */
                        $anaparInvioSW_rec = $praLib->GetAnapar("BLOCK_INVIO_STARWEB", "parkey", $dati['PRAM_DB'], false);
                        if ($anaparInvioSW_rec['PARVAL'] == "Si") {
                            $title = "Scarico file zip Infocamere in corso";
                        }
                        $h_ref = " <a href=\"#\" onclick=\""
                                . "$('.ita-wait').css('display','block').dialog({resizable:false,height:'200',width:'450',position:'center',title:'$title.....',modal: true}).parent().children().children('.ui-dialog-titlebar-close').hide();"
                                . "form1.submit();"
                                . "return false;\">$azione_img_tag</a>";
                    } else {
                        $Proric_rec = $dati['Proric_rec'];
                        if (!$Proric_rec['CODICEPRATICASW']) {
                            $urlZip = ItaUrlUtil::GetPageUrl(array('event' => 'invioInfocamere', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                        } else {
                            $urlZip = ItaUrlUtil::GetPageUrl(array('event' => 'invioInfocamereNEW', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                        }
                        $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"" . $urlZip . "\">$azione_img_tag</a>";
                    }
                }
            } else {
                $h_ref = $azione_img_tag;
            }
        } else {
//            $h_ref = "<a>$azione_img_tag</a>";
            $h_ref = $azione_img_tag;
        }


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
            $descObl = "Tutti i passi obbligatori sono completati<br>";
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

        $praLibAllegati = praLibAllegati::getInstance($praLib);
        $allegatiCartella = $praLibAllegati->getAllegatiCartella($dati);
        if ($allegatiCartella) {
            $seqCartella = false;
            $passoCartella = $praLibAllegati->getPassoCartella($dati);
            foreach ($dati['Navigatore']['Ricite_tab_new'] as $k => $passoVisibile) {
                if ($passoVisibile['ITEKEY'] == $passoCartella['ITEKEY']) {
                    $seqCartella = ($k + 1) * 10;
                }
            }

            $elencoAllegati = '';
            foreach ($allegatiCartella as $allegatoCartella) {
                $elencoAllegati .= "<br>- <i>{$allegatoCartella['DOCNAME']}</i>";
            }

            $html->addAlert("Gli allegati caricati al passo <b>$seqCartella - {$passoCartella['ITEDES']}</b> che non sono stati utilizzati saranno cancellati una volta inoltrata la richiesta.<br>$elencoAllegati", 'Attenzione', 'warning');
        }

        $html->appendHtml("<div>");
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();

        if ($dati['Proric_rec']['CODICEPRATICASW'] == "") {
            // Creo Combo Tipologia Segnalazione solo se non c'è il codice pratica su Proric (VECCHIO INVIO)
            if ($dati["Proric_rec"]['RICSTA'] == '91') {
                $disabled = "disabled=\"disabled\"";
            }
            if ($dati['dati_infocamere']["datiAdempimento"]["tipologia_segnalazione"]) {
                $htmlSelect = "<br><br><label class=\"ita-label-sx ita-label\">Tipologia Segnalazione</label>";
                $htmlSelect .= "<select $disabled name=\"TIPOLOGIA_SEGNALAZIONE\" id=\"TIPOLOGIA_SEGNALAZIONE\">";
                $htmlSelect .= "<option selected class=\"optSelect\" value=\"" . $dati['dati_infocamere']["datiAdempimento"]["tipologia_segnalazione"] . "\">" . $dati['dati_infocamere']["datiAdempimento"]["tipologia_segnalazione"] . "</option>";
                $htmlSelect .= "</select>";
            } else {
                $htmlSelect = "<br><br><label class=\"ita-label-sx ita-label\">Tipologia Segnalazione</label>";
                $htmlSelect .= "<select $disabled name=\"TIPOLOGIA_SEGNALAZIONE\" id=\"TIPOLOGIA_SEGNALAZIONE\">";
                $htmlSelect .= "<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>";
                $arrayOptions = array(
                    "ALTRO" => "Altro",
                    "APERTURA" => "Apertura",
                    "CESSAZIONE" => "Cessazione",
                    "MODIFICHE" => "Modifiche",
                    "SUBENTRO" => "Subentro",
                    "TRASFORMAZIONE" => "Trasformazione",
                );
                foreach ($arrayOptions as $value => $option) {
                    $Sel = "";
                    if ($dati['dati_infocamere']["datiAdempimento"]["tipologia_segnalazione"] == $value || $dati['Note_Infocamere']['INFOCAMERE']['TIPOLOGIA_SEGNALAZIONE'] == $value)
                        $Sel = "selected";
                    $htmlSelect .= "<option $Sel class=\"optSelect\" value=\"$value\">$option</option>";
                }
                $htmlSelect .= "</select>";
            }
        }

        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Conferma Richiesta e Invio $htmlSelect"));
        // 5 -- FINE DESCRIZIONE BOX

        if ($dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
            $html->appendHtml("<br><div style=\"text-align:center;display:inline-block;\">");
            $frontOfficeLib = new frontOfficeLib;
            $downloadUri = $frontOfficeLib->getDownloadURI($dati['CartellaAllegati'] . "/" . $dati['Proric_rec']['CODICEPRATICASW'] . '.zip', $dati['Proric_rec']['CODICEPRATICASW'] . '.SUAP.zip');
            $html->appendHtml("<a class=\"ita-href-submit\" href=\"$downloadUri\" style=\"text-decoration:underline;color:blue;\" target=\"_blank\">Clicca qui per scaricare il file</a><br>");
            $html->appendHtml("<span class=\"infocamere\">");
            $html->appendHtml($arrayNote['NOTE']);
            $html->appendHtml("</span>");
            $html->appendHtml("</div>");
        }

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
        //DATI INSEDIAMENTO PRODUTTIVO
        $DatiImpresa = $dati['dati_infocamere']['datiImpresa'];
        if (!$DatiImpresa) {
            $html->appendHtml("<span class=\"datoImpresa\"><b>DATI IMPRESA INDEFINITI.<br>IMPOSSIBILE INVIARE IL FILE</b></span><br>");
        } else {
            $html->appendHtml("<div title=\"dati Impresa\" class=\"datiImpresa\">Riepilogo dati Insediamento produttivo</div>");
            $html->appendHtml("<span class=\"datoImpresa\">" . $DatiImpresa['denominazione_impresa'] . "</span><br>");
            $html->appendHtml("<span class=\"datoImpresa\">" . $DatiImpresa['indirizzo_suap'] . "</span>");
            $html->appendHtml("<span class=\"datoImpresa\">N. " . $DatiImpresa['num_civico_suap'] . "</span><br>");
            $html->appendHtml("<span class=\"datoImpresa\">" . $DatiImpresa['cap_suap'] . "</span>");
            $html->appendHtml("<span class=\"datoImpresa\">" . $DatiImpresa['comune_suap'] . "</span>");
            $html->appendHtml("<span class=\"datoImpresa\">" . $DatiImpresa['provincia_suap'] . "</span>");
        }
        //
        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA
        $html->appendHtml("<div style=\"height:auto;width:100%;\" class=\"divAllegati\">");
        if ($dati['dati_infocamere']['checks']['Errors'] > 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibInfocamere.class.php';
            $praLibInfocamere = new praLibInfocamere($praLib);
            $praLibInfocamere->caricaPratica($dati);
            $arrHtmlErr = $praLibInfocamere->getChecksErrMsg();
            //
            if ($arrHtmlErr['datiImpresa']) {
                $html->appendHtml($arrHtmlErr['datiImpresa']);
            }
            if ($arrHtmlErr['datiSportello']) {
                $html->appendHtml($arrHtmlErr['datiSportello']);
            }
            if ($arrHtmlErr['datiAdempimenti']) {
                $html->appendHtml($arrHtmlErr['datiAdempimenti']);
            }
            if ($arrHtmlErr['datiEsibente']) {
                $html->appendHtml($arrHtmlErr['datiEsibente']);
            }
            if ($arrHtmlErr['datiLegRapp']) {
                $html->appendHtml($arrHtmlErr['datiLegRapp']);
            }
            if ($arrHtmlErr['files']) {
                $html->appendHtml($arrHtmlErr['files']);
            }
            if ($arrHtmlErr['source_file_firmato']) {
                $html->appendHtml($arrHtmlErr['source_file_firmato']);
            }
        } else {
            $results = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C");
            if ($results) {
                require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
                $praHtmlGridAllegati = new praHtmlGridAllegati();
                //Tabella Riepilogo Allegati
                $html->appendHtml($praHtmlGridAllegati->GetGridRiepilogo($dati, $extraParms));
            }
        }
        $html->appendHtml("</div>");
        //
        $html->appendHtml("<div class=\"ui-widget ui-widget-content ui-corner-all ita-wait\" style=\"display:none\">");
        $html->appendHtml("<img class=\"imgWait\" src=\"" . ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/images/wait1.gif\" />");
        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");

        return $html->getHtml();
    }

}

?>
