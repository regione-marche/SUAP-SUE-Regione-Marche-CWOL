<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateInviaMail extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm("#");

        $inviata = true;
        if ($dati['Proric_rec']['RICNPR'] != 0) {
            if ($dati['Proric_rec']['RICSTA'] == "01") {
                foreach ($dati['Ricmail_tab'] as $key => $ricmail_rec) {
                    if ($ricmail_rec['MAILSTATO'] != "@INVIATA@") {
                        $inviata = false;
                        break;
                    }
                }
            } else {
                $inviata = false;
            }
        } else {
            $inviata = false;
        }

        $tipoPasso = 'Invia Richiesta';

        //
        if ($inviata) {
            $img_base = frontOfficeLib::getIcon('email');
            ;
        } else {
            $img_base = frontOfficeLib::getIcon('email-open');
        }

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
            $urlMail = ItaUrlUtil::GetPageUrl(array('event' => 'invioMail', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $h_ref = "<a class=\"italsoft-button italsoft-button--primary\" onClick =\"$('.ita-wait').css('display','block').dialog({
                    resizable:false,height:'200',width:'300',title:'Inoltro richiesta in corso.....',modal: true}).parent().children().children('.ui-dialog-titlebar-close').hide();
                    window.location.replace('$urlMail');return false;\" href=\"#\">" . $azione_img_tag . "</a>";
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
        $msgProt = "";
        if ($dati['countEsg'] == $dati['countObl']) {
            $descObl = "Tutti i passi obbligatori sono completati<br>";
            if ($dati['Proric_rec']['RICSTA'] != "01") {
                $viewDesc = "E' possibile inviare la richiesta";
            } else {
                $dataInvio = substr($dati['Proric_rec']['RICDAT'], 6, 2) . "/" . substr($dati['Proric_rec']['RICDAT'], 4, 2) . "/" . substr($dati['Proric_rec']['RICDAT'], 0, 4);
                $oraInvio = $dati['Proric_rec']['RICTIM'];
                if ($dati['Proric_rec']['RICNPR'] != 0) {
                    $numProt = substr($dati['Proric_rec']['RICNPR'], 4) . "/" . substr($dati['Proric_rec']['RICNPR'], 0, 4);
                    $dataProt = substr($dati['Proric_rec']['RICDPR'], 6, 2) . "/" . substr($dati['Proric_rec']['RICDPR'], 4, 2) . "/" . substr($dati['Proric_rec']['RICDPR'], 0, 4);
                    $msgProt = "<br>con Protocollo N. $numProt data $dataProt";
                }
                $viewDesc = "Richiesta inoltrata in data $dataInvio alle ore $oraInvio$msgProt";
            }
        } else {
            if ($dati['countObl'] >= 1) {
                $descObl = "Passi obbligatori completati: " . $dati['countEsg'] . " di " . $dati['countObl'] . "<br>";
                $viewDesc = "Impossibile Inviare la Mail. Completare tutti i passi obbligatori";
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
        $html->appendHtml("<div class=\"divTipoPasso legenda\" style=\"display:inline-block;vertical-align:top;\">Tipo passo: $tipoPasso");
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

        $html->appendHtml("<div class=\"divInvioMail legenda\" style=\"padding-left:30px;text-decoration:underline;font-size:1.3em;color:dark-red;\"><b>$viewDesc</b></div>"); //div invio mail


        $html->appendHtml("<div>");
        //$html->appendHtml("<div style=\"display:inline-block;font-size:1.5em;\" class=\"descrizioneAzione\">Conferma Richiesta e Invio Mail</div>");
        // 5 -- DESCRIZIONE BOX
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();
        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, "Conferma Richiesta e Invio Mail"));
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
        $results = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C");
        if ($results) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            //Tabella Riepilogo Allegati
            $html->appendHtml($praHtmlGridAllegati->GetGridRiepilogo($dati, $extraParms));
        }
        $html->appendHtml("</div>");
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
