<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateDistintaComunica extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#", false, array('id' => 'form1', 'name' => 'form1'));
        $html->addHidden('p', frontOfficeApp::$cmsHost->getCurrentPageID());
        $html->addHidden("event", 'seqClick');
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        $tipoPasso = 'Download Distinta Infocamere';

        $img_base = frontOfficeLib::getIcon('distinta-download');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO

        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$href\" style = \"border:0px;\" />";
        } else {
            $azione_img_tag = "<img class=\"imgLink ImmagineAzione\" src=\"$img_base\" style=\"border:0px;\" />";
        }

        $azione_img_tag = '<i class="icon ion-arrow-down-a italsoft-icon"></i><span>Crea e Scarica</span>';

        /*
         * Controllo rapporto e allegati mancanti
         */
        $activeHref = true;
//        $url = ItaUrlUtil::GetPageUrl(array('event' => 'seqClick',
//                    'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $Anapar_rec_VerInteg = $praLib->GetAnapar('VERIFICA_INTEGRITA_DRR', 'parkey', $extraParms["PRAM_DB"], false);
        if ($Anapar_rec_VerInteg['PARVAL'] == "No") {
            $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"#\" onclick=\"document.getElementById('form1').submit();\">$azione_img_tag</a>";
        } else {
            $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" href=\"#\" onclick=\"scaricaDistinta('','" . $dati['Proric_rec']['RICNUM'] . "');return false;\">$azione_img_tag</a>";
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
        
        $html->appendHtml("<div style=\"height:auto;width: 100%;\" class=\"ui-widget-content ui-corner-all boxInfo\">");
        //
        $html->appendHtml("<div class=\"divContenitore\">");
        $html->appendHtml("<div class=\"divTipoPasso legenda\" style=\"display:inline-block;\">Tipo passo: $tipoPasso");
        $html->appendHtml("</div>"); //div tipo passo
        $html->appendHtml("<div class=\"divPassiObl\" style=\"padding-right:10px;display:inline-block;float:right;\">$descObl");
        $html->appendHtml("</div>"); //div passi obl
        $html->appendHtml("</div>"); //div contenitore

        $passoInvioComunica = array();
        foreach ($dati['Ricite_tab'] as $ricite_rec) {
            if ($ricite_rec['ITEZIP'] == 1) {
                $passoInvioComunica = $ricite_rec;
                break;
            }
        }

        $buttonAnnulla = "";
        $arrayNote = unserialize($passoInvioComunica['RICNOT']);
        if (!isset($arrayNote['INFOCAMERE']) && $dati['dati_infocamere']['datiImpresa']) {
            if ($dati['Proric_rec']['CODICEPRATICASW']) {
                $Url = ItaUrlUtil::GetPageUrl(array('event' => 'annullaDistintaComunica', 'seq' => $dati['seq'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                $buttonAnnulla = "<div style=\"margin:10px;display:inline-block;\">
                                    <button style=\"cursor:pointer;\" name=\"annullaDistinta\" class=\"italsoft-button\" type=\"button\" onclick=\"location.href='$Url';\">
                                         <i class=\"icon ion-close italsoft-icon\"></i>
                                         <div class=\"\" style=\"display:inline-block;\"><b>Annulla Distinta</b></div>
                                    </button>
                             </div>";
            }
        }

        $html->appendHtml("<div>");

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
                $messaggioAlert = '<b>Se non riesci a scaricare la Distinta Infocamere</b> <a href="#" onclick="document.getElementById("form1").submit();">clicca qui</a>.</b>';
                $divMsgConferma .= $html->getAlert($messaggioAlert);
            }

            if ($divMsgConferma) {
                $divMsgConferma = '<br /><br />' . $divMsgConferma;
            }
        }


        $readonlyCf = $readonlyUser = "";
        $htmlStarwebFields = "<div>";

        /*
         * Campo di input per l'utente telemaco
         */
        $userTelemaco = frontOfficeApp::$cmsHost->getUserInfo('telemaco');
        if ($userTelemaco) {
            $readonlyUser = "readonly";
        }
        $htmlStarwebFields .= "<div class=\"ita-field\">";
        $htmlStarwebFields .= "<label class=\"ita-label\" style=\"font-size:1.3em;padding-left:10px;padding-right:5px;display:inline-block;align:right;width:250px;\"><b>Utente Telemaco*</b></label>";
        $htmlStarwebFields .= "<input type=\"text\" style=\"\" $readonlyUser maxlength=\"7\" size=\"20\" value=\"$userTelemaco\" name=\"ITA_USERTELEMACO\" id=\"ITA_USERTELEMACO\"/>";
        $htmlStarwebFields .= "</div><br>";

        /*
         * Campo di input per il codice fiscale legato all'utente telemaco
         */
        $cfTelemaco = frontOfficeApp::$cmsHost->getUserInfo('cftelemaco');
//        if ($cfTelemaco) {
//            $readonlyCf = "readonly";
//        }
        if ($cfTelemaco == "") {
            $cfEsibente = $dati['dati_infocamere']['datiEsibente']['esibente_codfis'];
            if (strlen($cfEsibente) == 16) {
                $cfTelemaco = $cfEsibente;
            }
        }
        $htmlStarwebFields .= "<div class=\"ita-field\">";
        $htmlStarwebFields .= "<label class=\"ita-label\" style=\"font-size:1.3em;padding-left:10px;padding-right:5px;display:inline-block;align:right;width:250px;\"><b>Codice Fiscale per Starweb*</b></label>";
        $htmlStarwebFields .= "<input type=\"text\" style=\"\" $readonlyCf maxlength=\"16\" size=\"20\" value=\"$cfTelemaco\" name=\"ITA_CFTELEMACO\" id=\"ITA_CFTELEMACO\"/>";
        $htmlStarwebFields .= "</div>";
        $htmlStarwebFields .= "</div>";
        $htmlStarwebFields .= "<br><br>";


        //$html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Crea e Scarica la Distinta per Infocamere", $divMsgConferma));
        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Crea e Scarica la Distinta per Infocamere", $divMsgConferma . $htmlStarwebFields));


        $html->appendHtml("<div style=\"text-align:center;\">$buttonAnnulla</div>");

        if ($h_ref != $azione_img_tag) {
            $html->appendHtml("<div class=\"buttonlink\">$h_ref</div>");
        }

        // 5 -- FINE DESCRIZIONE BOX

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

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("<div style=\"height:auto;width:100%;\" class=\"divAllegati\">");
//        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
//        $praHtmlGridAllegati = new praHtmlGridAllegati();
        //Tabella Riepilogo Allegati
//        $html->appendHtml($praHtmlGridAllegati->GetGridRapporto($dati, $extraParms));
//        $html->appendHtml($htmlGrid);
        $html->appendHtml($this->addJsConfermaScarico());
        $html->appendHtml("</div>");
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
            function scaricaDistinta(url, richiesta){
                $('<div id =\"praConfermaScaricoDistinta\">$content</div>').dialog({
                title:\"Scarica PDF Distinta Infocamere.\",
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
                        text: 'Si',
                        class: 'italsoft-button',
                        click:  function() {
                            $(this).dialog('destroy');
                            document.getElementById('form1').submit();
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
