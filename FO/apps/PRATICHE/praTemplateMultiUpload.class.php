<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateMultiUpload extends praTemplateLayout {

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
        $html->addHidden("model", 'sueGestPassi');
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        $tipoPasso = 'MultiUpload';

        $img_base = frontOfficeLib::getIcon('upload');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

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
        // UPLOAD MULTIPLO
        //
        //if ($dati['Ricite_rec']['RICERF'] == 0) {
        if (!$dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
            $div_upload .= "<label class=\"descrizioneAzione\" style=\"width:230px;display:inline-block;vertical-align:top;\"><b>Allega Documento</b></label>";
            $div_upload .= "<input size=\"50\" id=\"brsw_ita_upload\" name=\"ita_upload\" type=\"file\" class=\"italsoft-dragndrop-upload\"/>";
            // bottone conferma
            $div_upload .= "<br><br /><div style=\"text-align:center;margin:10px;\">";
            if ($dati['Ricite_rec']['TARIFFA'] != 0) {
                $div_upload .= "<div style=\"text-align: left; font-size: 18px; font-weight: bold; margin: 20px -5px;\">L'importo per questo passo è di Euro " . $dati['Ricite_rec']['TARIFFA'] . "</div><br>";
            }
            $div_upload .= "<button name=\"confermaDati\" class=\"italsoft-button\" type=\"submit\">";
            $div_upload .= '<i class="icon ion-arrow-up-a italsoft-icon"></i>';
            $div_upload .= "<div class=\"\" style=\"display:inline-block;\"><b>Invia file</b></div>";
            $div_upload .= "</button>";

            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUploadCartella.class.php';
            $praTemplateUploadCartella = new praTemplateUploadCartella();
            $div_upload .= ' ' . $praTemplateUploadCartella->getHtmlButtonIncorpora($dati);

            $urlAvanti = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'navClick',
                    'seq' => $dati['Ricite_rec']['ITESEQ'],
                    'ricnum' => $dati['Ricite_rec']['RICNUM'],
                    'direzione' => 'avanti'
            ));

            $continueOnClick = <<<SCRIPT
    function checkUploadField(el, e) {
        if ( $(el).parents('form')[0].ita_upload.value ) {
            $('<div>Sei sicuro di voler proseguire senza caricare l\'allegato?<br />In caso contrario, clicca "No" ed utilizza il pulsante "Invia file".</div>').dialog({
                title: 'Attenzione!',
                resizable: false,
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
                            location.replace('$urlAvanti');
                        }
                    }
                ]
            });

            e.preventDefault();
        }
    }
SCRIPT;

            $div_upload .= " <a class=\"italsoft-button\" href=\"$urlAvanti\" onclick=\"checkUploadField(this, event);\">";
            $div_upload .= '<i class="icon ion-chevron-right italsoft-icon"></i>';
            $div_upload .= "<div class=\"\" style=\"display:inline-block;\"><b>Avanti</b></div>";
            $div_upload .= "</a>";

            $div_upload .= $html->getScript($continueOnClick);

            $div_upload .= "</div>";
        }
        //} else {
        //    $h_ref = $azione_img_tag;
        //}
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
        // Siamo in modalita sola consultazione
        //
        if ($dati['Consulta'] == true) {
            if (!$dati['permessiPasso']['Insert']) {
                $div_upload = "";
            }
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

            //
            //Se c'è già un allegato, do solo una dialog con l'errore e svuoto i campi d'errore
            //
            if (count($results) >= 1) {
                $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Errore Caricamento File!!\">
                                         <p style=\"padding:5px;color:red;font-size:1.2em;\"><b>" . $dati['Ricite_rec']['RICERM'] . "</b></p>
                                   </div>");
                $dati['Ricite_rec']['RICERF'] = 0;
                $dati['Ricite_rec']['RICERM'] = "";
                ItaDB::DBUpdate($extraParms['PRAM_DB'], "RICITE", 'ROWID', $dati['Ricite_rec']);
                $msgErr = "";
            }
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
        $html->appendHtml($div_upload);
        //
        // Info Errori Upload
        //

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
        if ($results) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            // TAbella Allegati
            $html->appendHtml($praHtmlGridAllegati->GetGrid($dati, $results, $extraParms));
        }
        $html->appendHtml("</div>");

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</form>");
        //Errore file upload mancante
//        if ($extraParms['ERRUPL'] == 99) {
//            $html->appendHtml("<script type=\"text/javascript\">alert(\"ATTENZIONE!!!\\nSelezionare un file da caricare\");</script>");
//        }
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

    function GetHtmlGridInfo($html, $praLib, $Metadati, $extraParms) {
        $html->appendHtml("<table class=\"tablesorter\" id=\"tabel_result_not_zebra\">");
        if ($Metadati['CLASSIFICAZIONE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr>');
            $html->appendHtml("<th>Classificazione: </th>");
            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $Anacla_rec = $praLib->GetAnacla($Metadati['CLASSIFICAZIONE'], "codice", false, $extraParms['PRAM_DB']);
            $html->appendHtml("<td>" . $Anacla_rec['CLADES'] . "</td>");
            $html->appendHtml('</tbody>');
            $html->appendHtml('</tr>');
        }
        if ($Metadati['DESTINAZIONE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr>');
            $html->appendHtml("<th>Destinazioni: </th>");
            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $destinazioni = "";
            foreach ($Metadati['DESTINAZIONE'] as $dest) {
                $Anaddo_rec = $praLib->GetAnaddo($dest, "codice", false, $extraParms['PRAM_DB']);
                if ($Anaddo_rec) {
                    $destinazioni .= $Anaddo_rec['DDONOM'] . "<br>";
                }
            }
            $html->appendHtml("<td>$destinazioni</td>");
            $html->appendHtml('</tbody>');
            $html->appendHtml('</tr>');
        }
        if ($Metadati['NOTE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr>');
            $html->appendHtml("<th>Note: </th>");
            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $html->appendHtml("<td>" . $Metadati['NOTE'] . "</td>");
            $html->appendHtml('</tbody>');
            $html->appendHtml('</tr>');
        }
        $html->appendHtml('</table>');
    }

}

?>
