<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateRaccolta extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTemplate.class.php';

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'POST');
        if ($dati['ricdat'] != 0) {
            $html->addHidden("event", 'annullaRaccolta');
        } else {
            $html->addHidden("event", 'submitRaccolta');
        }
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        $tipoPasso = 'Raccolta Dati';

        /*
         * Verifica ITENRA
         */
        $chkJson = json_decode($dati['Ricite_rec']['ITENRA']);
        if ($chkJson != false) {
            $dati['Ricite_rec']['ITENRA'] = '';
        }

        $img_base = frontOfficeLib::getIcon('notepad');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        // 3 --  INIZIO BOX INFO
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
        //
        //BR di separazione
        //
        $html->appendHtml("<br>");

        // Comincio campi Racccolta
        if ($dati['Ricdag_tab']) {

            /*
             * DISEGNO LA FORM RACCOLTA DATI
             */
            $buttonSvuota = "<button class=\"ita-svuota-dati italsoft-button italsoft-button--secondary\" type=\"button\" onclick=\"if ( confirm('Sei sicuro di voler svuotare tutti i campi?') ) itaFrontOffice.clear(this.form);\">
                                <i class=\"icon ion-backspace italsoft-icon\"></i>
                                <div class=\"\" style=\"display:inline-block;\"><b>Svuota Dati</b></div>
                             </button>";
            if (!$dati['permessiPasso']['Delete']) {
                $buttonSvuota = "";
            }


            $buttonConferma = "<br />
                               <div style=\"text-align:center;margin:10px;\">
                                    <button name=\"confermaDati\" class=\"ita-form-submit italsoft-button\" type=\"submit\">
                                       <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                       <div class=\"\" style=\"display:inline-block;\"><b>Conferma Dati</b></div>
                                    </button>
                                     $buttonSvuota
                                </div>";

//            $buttonConferma = "<br /><div style=\"text-align:center;margin:10px;\">
//                                 <button name=\"confermaDati\" class=\"ita-form-submit italsoft-button\" type=\"submit\">
//                                    <i class=\"icon ion-checkmark italsoft-icon\"></i>
//                                    <div class=\"\" style=\"display:inline-block;\"><b>Conferma Dati</b></div>
//                                 </button>
//                                 <button class=\"ita-svuota-dati italsoft-button italsoft-button--secondary\" type=\"button\" onclick=\"if ( confirm('Sei sicuro di voler svuotare tutti i campi?') ) itaFrontOffice.clear(this.form);\">
//                                    <i class=\"icon ion-backspace italsoft-icon\"></i>
//                                    <div class=\"\" style=\"display:inline-block;\"><b>Svuota Dati</b></div>
//                                 </button>
//                               </div>";



            $buttonAnnulla = "<br /><div style=\"text-align:center;margin:10px;display:inline-block;\">
                                 <button name=\"modificaDati\" class=\"italsoft-button\" type=\"submit\">
                                    <i class=\"icon ion-edit italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Modifica Dati</b></div>
                                 </button>
                               </div>";

            $urlAvanti = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'navClick',
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'ricnum' => $dati['Ricite_rec']['RICNUM'],
                        'direzione' => 'avanti'
            ));

            $buttonAvanti = " <a class=\"italsoft-button italsoft-button-avanti\" href=\"$urlAvanti\">";
            $buttonAvanti .= '<i class="icon ion-arrow-right-b italsoft-icon"></i>';
            $buttonAvanti .= "<div class=\"\" style=\"display:inline-block;\"><b>Avanti</b></div>";
            $buttonAvanti .= "</a><br>";

            // Assegno il bottone scarica solo se è presente un template
            $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
            if (isset($metadati["TESTOBASEXHTML"])) {
                $Url = ItaUrlUtil::GetPageUrl(array('event' => 'downloadRaccolta', 'seq' => $dati['seq'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                $buttonDownloadPdf = "<br /><div style=\"margin:10px;display:inline-block;\">
                                            <button style=\"cursor:pointer;\" name=\"scaricaPdf\" class=\"italsoft-button\" type=\"button\" onclick=\"location.href='$Url';\">
                                                 <i class=\"icon ion-arrow-swap italsoft-icon\"></i>
                                                 <div class=\"\" style=\"display:inline-block;\"><b>Scarica PDF</b></div>
                                            </button>
                                          </div>";

                if (strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'admin') {
                    $hrefGenera = ItaUrlUtil::GetPageUrl(array('event' => 'rigeneraDistinta', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'seq' => $dati['seq']));
                    $buttonRigeneraPdf = "<br /><div style=\"margin:10px;display:inline-block;\">
                                            <button style=\"cursor:pointer;\" name=\"rigenera\" classitalsoft-button\" type=\"button\" onclick=\"location.href='$hrefGenera';\">
                                                 <i class=\"icon ion-document italsoft-icon\"></i>
                                                 <div class=\"\" style=\"display:inline-block;\"><b>Rigenera PDF</b></div>
                                            </button>
                                          </div>";
                }
            }

            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praDisegnoRaccolta.class.php';
            $praDisegnoRaccolta = new praDisegnoRaccolta();
            $html->appendHtml($praDisegnoRaccolta->getDisegnoRaccoltaRichiesta($dati));

            if ($dati['Consulta'] == true) {
                if (!$dati['permessiPasso']['Insert']) {
                    $buttonConferma = "";
                }
                if (!$dati['permessiPasso']['Edit']) {
                    $buttonAnnulla = "";
                }

                //$buttonConferma = $buttonAnnulla = "";
            }

            if (!$dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
                $html->appendHtml("<br>");
                //$html->appendHtml("<div class=\"divButton\">");
                if ($dati['ricdat'] == 0) {
                    $html->appendHtml($buttonConferma);
                } else {
                    $html->appendHtml("<div style=\"text-align:center;\">" . $buttonAnnulla . $buttonAvanti . $buttonDownloadPdf . $buttonRigeneraPdf . "</div>");
                }
                //$html->appendHtml("</div>");
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

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

    public function disegnaRicerca($html, $id, $tableData, $tableDataParams, $title = 'Elenco') {
        $html->appendHtml("
<div style=\"display: inline-block; vertical-align: middle; margin-left: 5px; font-size: 1.6em; cursor: pointer;\">
    <span title=\"$title\" class=\"icon ion-search italsoft-icon italsoft-button-ricerca\" data-ricerca=\"$id\"></span>
</div>
");

        $tableDataParams['stickyHeaders'] = true;
        $html->appendHtml("<div id=\"$id\" style=\"display: none;\">");
        $html->addTable($tableData, $tableDataParams);
        $html->appendHtml("</div>");
    }

}
