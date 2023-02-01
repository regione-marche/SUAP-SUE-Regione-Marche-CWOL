<?php

class praTemplateInviaAgenzia extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {


        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");
        $html->addHidden("event", 'inoltroAgenzia');
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);
        
        $tipoPasso = 'Invio ad Agenzia';

        $img_base = ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/images/CNA.jpg";
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO
        //
        // Conteggio Passi Obbligatori completati
        //
        if ($dati['countEsg'] == $dati['countObl'] || $dati['countOblNonFatti'] == 0) {
            $descObl = "Tutti i passi obbligatori sono completati<br>";
            $viewDesc = "E' possibile inviare la richiesta";
        } else {
            if ($dati['countObl'] >= 1) {
                $descObl = "Passi obbligatori completati: " . $dati['countEsg'] . " di " . $dati['countObl'] . "<br>";
                $viewDesc = "Impossibile Inviare la Richiesta. Completare tutti i passi obbligatori";
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

        
        $html->appendHtml("<div class=\"divInvioMail legenda\" style=\"padding-left:30px;text-decoration:underline;font-size:1.3em;color:dark-red;\"><b>$viewDesc</b></div>"); //div invio mail

        $buttonConferma = "<br /><div style=\"text-align:center;margin:10px;\">
                                   <button style=\"cursor:pointer;\" name=\"confermaAgenzia\" class=\"italsoft-button\" type=\"submit\">
                                       <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                       <div class=\"\" style=\"display:inline-block;\"><b>Conferma Inoltro Agenzia</b></div>
                                   </button>
                               </div>";
        $html->appendHtml("<label class=\"ita-label\">Scecli l'agenzia alla quale inoltare la richiesta</label>");
        $html->appendHtml("<select name=\"agenzia\" id=\"agenzia\">");
        $arrParAgenzie = $praLib->GetParametriAgenzie($extraParms["PRAM_DB"], true);
        if (count($arrParAgenzie) > 1) {
            $html->appendHtml("<option selected class=\"optSelect\" value=\"\">Seleziona una scelta</option>';");
        }
        foreach ($arrParAgenzie as $key => $value) {
            $sel = "";
            if ($dati['Proric_rec']['RICAGE'] == $key) {
                $sel = "selected";
            }
            switch ($key) {
                case "CNA":
                    $html->appendHtml("<option $sel class=\"optSelect\" value=\"CNA\">CNA</option>';");
                    break;
                case "CONFA":
                    $html->appendHtml("<option $sel class=\"optSelect\" value=\"CONFA\">Confartigianato</option>';");
                    break;
            }
        }

        $html->appendHtml("</select><br>");
        $html->appendHtml("<SCRIPT language=JavaScript>");
        $html->appendHtml("$(function(){");
        $html->appendHtml("
                    var agenzia = $('#agenzia').val();
                    if(agenzia != ''){
                        $('.ImmagineAzione').attr('src','" . $img_base = ITA_PRATICHE_PUBLIC . "/SUAP_praMup/images/'+agenzia+'.jpg');
                    }else{    
                       $('.ImmagineAzione').attr('src','" . $img_base = ITA_PRATICHE_PUBLIC . "/SUAP_praMup/images/AGENZIANONDEFINITA.jpg');
                    }
                    $('#agenzia').change(function() {
                    var agenzia_c = $('#agenzia').val();
                    if(agenzia_c != ''){
                        $('.ImmagineAzione').attr('src','" . $img_base = ITA_PRATICHE_PUBLIC . "/SUAP_praMup/images/'+agenzia_c+'.jpg');
                    }else{    
                       $('.ImmagineAzione').attr('src','" . $img_base = ITA_PRATICHE_PUBLIC . "/SUAP_praMup/images/AGENZIANONDEFINITA.jpg');
                    }
                    
                    });


                ");
        $html->appendHtml("});");
        $html->appendHtml("</SCRIPT>");
        if ($dati['countOblNonFatti'] == 0) {
            $html->appendHtml($buttonConferma);
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
        $results = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C");
        if ($results) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            //Tabella Riepilogo Allegati
            $html->appendHtml($praHtmlGridAllegati->GetGridInviiAgenzia($dati, $extraParms));
        }
        $html->appendHtml("</div>");
        
        $html->appendHtml($this->disegnaFooter($dati, $extraParms));
        
        $html->appendHtml("</form>");
        $html->appendHtml("</div>");

        return $html->getHtml();
    }

}

?>
