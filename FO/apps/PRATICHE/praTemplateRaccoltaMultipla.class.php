<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateRaccoltaMultipla extends praTemplateLayout {

    function GetPagina($dati, $praLib, $extraParms = array()) {
        $html = new html();
        $htmlRicerca = new html();

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSostituzioni.class.php';
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTemplate.class.php';

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'POST');
        if ($dati['ricdat'] != 0) {
            $html->addHidden("event", 'annullaRaccolta');
        } else {
            $html->addHidden("event", 'submitRaccoltaMultipla');
        }
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);

        /*
         * Verifica ITENRA
         */
        $chkJson = json_decode($dati['Ricite_rec']['ITENRA']);
        if ($chkJson != false) {
            $dati['Ricite_rec']['ITENRA'] = '';
        }

        $tipoPasso = 'Raccolta Dati Multipla';

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
        $html->appendHtml("<div style=\"height:auto; width: 100%;\" class=\"ui-widget-content ui-corner-all boxInfo\">");
        $html->appendHtml("<div style=\"width:99%;text-align:center;display:inline-block;\" id=\"divInfo\">");
        $html->appendHtml("<div style=\"float:left;display:inline-block;\" class=\"legenda\">Tipo passo: $tipoPasso</div>");
        $html->appendHtml("<div style=\"float:right;display:inline-block;\">$descObl</div>");
        $html->appendHtml("</div>");

        $html->appendHtml("<div style=\"font-size:1.2em;\" class=\"descrizioneAzione\">" . $dati['Ricite_rec']['ITEDES'] . "</div>");
        // Comincio campi Racccolta
        if ($dati['Ricdag_tab']) {

            /*
             * DISEGNO LA FORM RACCOLTA DATI
             */
            $addBox = "<button id=\"addBox\" name=\"addBox\" class=\"ita-box-add-button italsoft-button\" type=\"button\" style=\"margin-top: .8em; font-size: .9em;\">
                            <i class=\"buttonAddRem icon ion-plus italsoft-icon\"></i>
                            <span>Aggiungi riga</span>
                        </button>";
            if ($dati['Ricite_rec']['ITENRA'] || $dati['ricdat'] == 1) {
                $addBox = "";
            }

            $buttonConferma = "<br /><div style=\"text-align:center;margin:10px;\">
                                 <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-form-submit italsoft-button\" type=\"submit\">
                                    <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Conferma Dati</b></div>
                                 </button>
                               </div>";
            $buttonAnnulla = "<br /><div style=\"cursor: pointer; margin: 10px; display: inline-block;\">
                                 <button name=\"annullaDati\" class=\"italsoft-button\" type=\"submit\">
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
            if ($metadati) {
                $Url = ItaUrlUtil::GetPageUrl(array('event' => 'downloadRaccolta', 'seq' => $dati['seq'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                $buttonDownloadPdf = "<br /><div style=\"margin:10px;display:inline-block;\">
                                      <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"italsoft-button\" type=\"button\" onclick=\"location.href='$Url';\">
                                           <i class=\"icon ion-arrow-swap italsoft-icon\"></i>
                                           <div class=\"\" style=\"display:inline-block;\"><b>Scarica PDF</b></div>
                                      </button>
                                  </div>";
            }


//            if ($dati['Consulta'] == true) {
//                $buttonConferma = $buttonAnnulla = "";
//            }
            //
            //Metto l'html prima del div template se la sua posizione è Inizio
            foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
                $Ricdag_rec = $praLib->ctrRicdagRec($Ricdag_rec, $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", "."));
                $br = "";
                if ($Ricdag_rec['DAGACA'] == 1) {
                    $br = "<br>";
                }
                $meta = unserialize($Ricdag_rec["DAGMETA"]);
                if ($Ricdag_rec['DAGDIZ'] == "H" && $meta['HTMLPOS'] == "Inizio") {
                    $dictionaryValues_pre = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();
                    $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
                    $template = $praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
                    $defaultValue = $praLib->valorizzaTemplate($template, $dictionaryValues);
                    $html->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}\">" . $defaultValue . "</div>");
                    $html->appendHtml($br);
                    break; // Perche se ci sono raccolte multiple trova piu di un campo
                }
            }


            if ($dati['Ricite_rec']['ITERDM']) {
                $html->appendHtml("<div id=\"boxRaccolte\">");
            }

            //$contaSet = 0;
            if ($dati['Ricite_rec']['ITENRA'] == "") {
                $templateClass = "ita-div-box-template";
            }
            $jj = 0;
            foreach ($dati['Dagset_tab'] as $dagvalue) {
                $jj++;
                $dagset = substr($dagvalue['DAGSET'], -2);
                $contaSet = intval($dagset);
                $i = str_repeat("0", 2 - strlen($dagset)) . $dagset;
                //$contaSet +=1;
                $html->appendHtml("<div id=\"boxRaccolta_$i\" name=\"boxRaccolta_$i\" style=\"margin-bottom:5px;\" class=\"$templateClass ita-box-raccolta ui-widget-content ui-corner-all\">");
                if ($dati['Ricite_rec']['ITERDM']) {
                    $html->appendHtml("<div style=\"padding-left:5px;\" id=\"headerRaccolta_$i\" class=\"ui-widget-header ui-corner-all ita-header-raccolta\">" . $contaSet . "</div>");
                }
                if ($dati['Ricite_rec']['ITECOL'] != 0) {
                    $col = $dati['Ricite_rec']['ITECOL'];
                }
                if ($col) {
                    $html->appendHtml("<table class=\"tableAutocert\">");
                }
                $contaCol = 0;
                //$width = "160px";
                $DagsetFilter = $dagvalue['DAGSET'];
                foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
                    $Ricdag_rec = $praLib->ctrRicdagRec($Ricdag_rec, $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", "."));
                    $styleLblBO = $Ricdag_rec['DAGLABSTYLE'];
                    $styleFldBO = $Ricdag_rec['DAGFIELDSTYLE'];
                    $classFldBO = $Ricdag_rec['DAGFIELDCLASS'];
                    $classPosLabel = $disabled = $checked = "";
                    if ($Ricdag_rec['DAGSET'] !== $DagsetFilter) {
                        continue;
                    }
                    $br = $campoObl = $class = "";
                    // Metto un br quando ce il flag a capo tranne se è il primo campo
                    if ($Ricdag_rec['DAGACA'] == 1 && $col == 0) {
                        $br = "<br id=\"{$Ricdag_rec['DAGKEY']}_{$i}_acapo\">";
                    }
                    // metto un * quando i lcampo è obbligatorio
                    if (strpos($Ricdag_rec['DAGCTR'], $Ricdag_rec['DAGKEY'])) {
                        $campoObl = "*";
                    }
                    if ($col) {
                        if ($contaCol == 0) {
                            $html->appendHtml("<tr>");
                        }
                        $contaCol += 1;
                        //if ($Ricdag_rec['DAGACAPO']) {
                        if ($Ricdag_rec['DAGACA']) {
                            $colspan = "colspan=\"" . $col - $contaCol . "\"";
                            $contaCol = $col;
                        }

                        $html->appendHtml("<td $colspan>");
                    }
                    $value = $readonly = "";
                    $defaultValue = "";
                    if ($Ricdag_rec['DAGDIZ'] == "C") {
                        $defaultValue = $Ricdag_rec['DAGVAL'];
                    } elseif ($Ricdag_rec['DAGDIZ'] == "D") {
                        $defaultValue = $dati['Navigatore']["Dizionario_Richiesta_new"]->getData($Ricdag_rec['DAGVAL']);
                    } elseif ($Ricdag_rec['DAGDIZ'] == "T") {
                        $defaultValue_pre = $praLib->elaboraTemplateDefault($Ricdag_rec['DAGVAL'], $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
                        $defaultValue = str_replace("\\n", chr(13), $defaultValue_pre);
                    } elseif ($Ricdag_rec['DAGDIZ'] == "H") {
                        $dictionaryValues_pre = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();
                        $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
                        $template = $praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
                        $defaultValue = $praLib->valorizzaTemplate($template, $dictionaryValues);
                    }
                    if ($Ricdag_rec['DAGTIP'] == "Sportello_Aggregato") {
                        $Ricdag_rec['DAGTIC'] = "Sportello_Aggregato";
                    }
                    if ($Ricdag_rec['DAGTIP'] == "Indir_InsProduttivo") {
                        $Ricdag_rec['DAGTIC'] = "Indir_InsProduttivo";
                    }
                    if ($Ricdag_rec['DAGTIP'] == "Foglio_catasto") {
                        $Ricdag_rec['DAGTIC'] = "Foglio_catasto";
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Cap_InsProduttivo') {
                        continue;
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Comune_InsProduttivo') {
                        continue;
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Prov_InsProduttivo') {
                        continue;
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Ruolo') {
                        $Ricdag_rec['DAGTIC'] = "Ruolo";
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Comune') {
                        $Ricdag_rec['DAGTIC'] = "Comune";
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Codfis_Anades') {
                        $Ricdag_rec['DAGTIC'] = "Codfis_Anades";
                    }
                    $htmlCampo = new html();

                    if ($Ricdag_rec['DAGTIC'] != "Html") {
                        $htmlCampo->appendHtml("<div class=\"ita-field\">");
                    } else {
                        $htmlCampo->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}_$i\">");
                    }

                    switch ($Ricdag_rec['DAGPOS']) {
                        case "":
                        case "Sinistra":
                            $classPosLabel = "ita-label-sx";
                            break;
                        case "Destra":
                            $classPosLabel = "ita-label-dx";
                            break;
                        case "Sopra":
                            $classPosLabel = "ita-label-top";
                            break;
                        case "Sotto":
                            $classPosLabel = "ita-label-bot";
                            break;
                        case "Nascosta":
                            $classPosLabel = "ita-label-hidden";
                            break;
                    }

                    /*
                     * Override del campo
                     */
                    $fl_disegno_std = false;

                    /* @var $objTemplate praLibCustomClass */
                    $objTemplate = praLibCustomClass::getInstance($praLib);

                    if ($objTemplate) {
                        $datiPasso = array(
                            'Ricdag_rec' => $Ricdag_rec,
                            'styleLblBO' => $styleLblBO,
                            'styleFldBO' => $styleFldBO,
                            'classFldBO' => $classFldBO,
                            'defaultValue' => $defaultValue,
                            'classPosLabel' => $classPosLabel,
                            'br' => $br,
                            'campoObl' => $campoObl
                        );

                        $retTemplateCampo = $objTemplate->eseguiDisegnoCampo($dati, $datiPasso);

                        if ($retTemplateCampo === false) {
                            $fl_disegno_std = true;
                        }

                        $htmlCampo->appendHtml($retTemplateCampo);
                    } else {
                        $fl_disegno_std = true;
                    }

                    if ($fl_disegno_std) {
                        switch ($Ricdag_rec['DAGTIC']) {
                            case 'Data':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-datepicker ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly maxlength=\"10\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                break;

                            case 'Time':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-time ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly maxlength=\"5\" size=\"7\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\"/>");
                                break;

                            case 'Importo':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $readonly = $praLib->getReadOnly($dati, $Ricdag_rec['DAGROL']);
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];

                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"text\" class=\"italsoft-input--currency ita-edit $classFldBO\" style=\"text-align: right; $styleFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" /> &euro;");
                                break;

                            case 'Hidden':
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"hidden\" class=\"$classFldBO\" style=\"$styleFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                break;

                            case 'Password':
                                $type = "password";
                            case 'Text':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                break;
                            case 'Indir_InsProduttivx':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                //$htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;width:$width;display:inline-block;align:right;\"><b>$etichetta $campoObl</b></label>");
                                //$htmlCampo->appendHtml("<input class=\"ita-edit $class\" type=\"$type\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                break;
                            case 'TextArea':
                                $meta = unserialize($Ricdag_rec["DAGMETA"]);
                                $cols = $meta['ATTRIBUTICAMPO']['COLS'];
                                $rows = $meta['ATTRIBUTICAMPO']['ROWS'];
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<textarea data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"$classPosLabel ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly cols=\"$cols\" rows=\"$rows\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\">$value</textarea>");
                                break;
                            case 'Select':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $disabled = "disabled=\"disabled\"";
                                }
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<select data-default=\"" . htmlspecialchars($defaultValue) . "\" style=\"$styleFldBO\" class=\"$classFldBO\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" $disabled>");
                                $htmlCampo->appendHtml("<option class=\"optSelect\" value=\"\"></option>");
                                $arrayOptions = explode("|", $Ricdag_rec['DAGVAL']);

                                switch ($Ricdag_rec['DAGDIZ']) {
                                    case 'D':
                                        $arrayOptions = array();
                                        $DatiDizionario = $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData();
                                        list($Key1, $Key2) = explode(".", $Ricdag_rec['DAGVAL']);

                                        foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                                            if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                                                $Index = intval(substr($KeyDict, -2));
                                                $arrayOptions[$Index] = $ValDict;
                                            }
                                        }
                                        break;

                                    case 'T':
                                        $DatiDizionario = $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData();
                                        $strTemplate = $Ricdag_rec['DAGVAL'];
                                        $arrayOptions = array();

                                        /*
                                         * Ricavo le variabili dal template
                                         */
                                        preg_match_all('/@{.*?\$([A-Z0-9._]*).*?}@/', $strTemplate, $Matches);

                                        /*
                                         * Ciclo le variabili matchate, per ogni variabili
                                         * ciclo il suo dizionario in cerca delle chiavi
                                         * formate da "VARIABILE" + "_XY".
                                         * Utilizzo il valore XY come indice per $arrayOptions,
                                         * ed aggiungo come valore il template (o l'$arrayOptions al medesimo indice se presente)
                                         * sostituendo la variabile
                                         * con il valore da dizionario
                                         */
                                        foreach ($Matches[1] as $matchKey => $Match) {
                                            list($Key1, $Key2) = explode(".", $Match);
                                            foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                                                if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                                                    $Index = intval(substr($KeyDict, -2));
                                                    $arrayOptions[$Index] = str_replace($Match, $Match . '_' . substr($KeyDict, -2), (isset($arrayOptions[$Index]) ? $arrayOptions[$Index] : $strTemplate));
                                                }
                                            }
                                        }

                                        foreach ($arrayOptions as $optKey => $Option) {
                                            $valoreTemplate = $praLib->valorizzaTemplate($Option, $DatiDizionario);

                                            if (!trim($valoreTemplate)) {
                                                unset($arrayOptions[$optKey]);
                                            } else {
                                                $arrayOptions[$optKey] = $valoreTemplate;
                                            }
                                        }

                                        ksort($arrayOptions);
                                        break;
                                }

                                foreach ($arrayOptions as $key => $option) {
                                    $default = false;
                                    if (substr($option, 0, 1) == "#") {
                                        $option = str_replace("#", "", $option);
                                        if ($Ricdag_rec['RICDAT'] == "") {
                                            $default = true;
                                        }
                                    }

                                    $Sel = "";
                                    $nodevalue = $option;
                                    if (strpos($option, ":")) {
                                        list($option, $nodevalue) = explode(":", $option);
                                    }
                                    if ($Ricdag_rec['RICDAT'] == $option || $default)
                                        $Sel = "selected";
//                                if ($Ricdag_rec['RICDAT'] == $option)
//                                    $Sel = "selected";
                                    $htmlCampo->appendHtml("<option $Sel class=\"optSelect\" value=\"$option\">$nodevalue</option>';");
                                }
                                $htmlCampo->appendHtml("</select>");
                                break;
                            case 'Html';
                                $meta = unserialize($Ricdag_rec["DAGMETA"]);
                                if ($meta['HTMLPOS'] == "Default") {
                                    $htmlCampo->appendHtml($defaultValue);
                                }
                                break;
                            case 'RadioButton';
                                $br = '';
                                break;
                            case 'RadioGroup';
                                foreach ($dati['Ricdag_tab'] as $key => $RicdagRadioButton_rec) {
                                    $RicdagRadioButton_rec = $praLib->ctrRicdagRec($RicdagRadioButton_rec, $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", "."));

                                    if ($RicdagRadioButton_rec['DAGTIC'] !== 'RadioButton') {
                                        continue;
                                    }
                                    if (substr($RicdagRadioButton_rec['DAGSET'], -2) != $i) {
                                        continue;
                                    }
                                    if (is_array($RicdagRadioButton_rec['DAGMETA'])) {
                                        $metaRadioButton = $RicdagRadioButton_rec['DAGMETA'];
                                    } else {
                                        $metaRadioButton = unserialize($RicdagRadioButton_rec['DAGMETA']);
                                    }
                                    if ($metaRadioButton['ATTRIBUTICAMPO']['NAME'] !== $Ricdag_rec['DAGKEY']) {
                                        continue;
                                    }

                                    /*
                                     * Setting del value di ritorno e dell attributo checked
                                     */
                                    $defaultValueRadioGroup = $Ricdag_rec['DAGVAL'];
                                    $returnValueRadioButton = $metaRadioButton['ATTRIBUTICAMPO']['RETURNVALUE'];

                                    $brRadioButton = "";
                                    if ($RicdagRadioButton_rec['DAGACA'] == 1) {
                                        $brRadioButton = "<br>";
                                    }

                                    $disabled = '';
                                    //if ($RicdagRadioButton_rec['DAGROL'] == 1 || $dati['ricdat'] == 1) {
                                    if ($praLib->getReadOnly($dati, $RicdagRadioButton_rec['DAGROL'])) {
                                        $disabled = "disabled";
                                    }
                                    $checked = "";
                                    if ($Ricdag_rec['RICDAT'] === $returnValueRadioButton) {
                                        $checked = "checked";
                                    } else {
                                        if ($Ricdag_rec['RICDAT'] === '' && $defaultValueRadioGroup !== '' && $returnValueRadioButton == $defaultValueRadioGroup) {
                                            $checked = "checked";
                                        }
                                    }
                                    $etichetta = $RicdagRadioButton_rec['DAGLAB'] ? $RicdagRadioButton_rec['DAGLAB'] : $RicdagRadioButton_rec['DAGDES'];
                                    if ($classPosLabel != "ita-label-bot" && $classPosLabel != "ita-label-dx") {
                                        $htmlCampo->appendHtml("<label class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                                        $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"radio\" $checked $disabled value=\"$returnValueRadioButton\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$key" . "_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                    } else {
                                        if ($classPosLabel == 'ita-label-dx') {
                                            $classPosLabel = '';
                                            $styleLblBO = 'display: inline-block;' . $styleLblBO;
                                        }
                                        $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"radio\" $checked $disabled value=\"$returnValueRadioButton\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$key" . "_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                        $htmlCampo->appendHtml("<label class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                                    }
                                    $htmlCampo->appendHtml($brRadioButton);
                                }
                                break;

                            case 'CheckBox';
                                $returnValues = $meta['ATTRIBUTICAMPO']['RETURNVALUES'];
                                $arrayReturVal = explode("/", $returnValues);
                                $valCheched = $arrayReturVal[0];
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $disabled = "disabled";
                                }
                                if ($Ricdag_rec['RICDAT'] == "On" || $Ricdag_rec['RICDAT'] == "1" || $Ricdag_rec['RICDAT'] == "Si") {
                                    $checked = "checked=\"yes\"";
                                } else {
                                    if ($Ricdag_rec['RICDAT'] == "" && $defaultValue && $defaultValue == $valCheched) {
                                        $checked = "checked=\"yes\"";
                                    }
                                }
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $labelFor = "raccolta[{$Ricdag_rec['DAGKEY']}]_$key";
                                if ($classPosLabel != "ita-label-bot" && $classPosLabel != "ita-label-dx") {
                                    $htmlCampo->appendHtml("<label for=\"$labelFor\" class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                                    $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"checkbox\" $checked $disabled id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                } else {
                                    if ($classPosLabel == 'ita-label-dx') {
                                        $classPosLabel = '';
                                        $styleLblBO = 'display: inline-block;' . $styleLblBO;
                                    }
                                    $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"checkbox\" $checked $disabled id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");
                                    $htmlCampo->appendHtml("<label for=\"$labelFor\" class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                                }
                                break;

                            case 'Ruolo':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = 'readonly="readonly"';
                                }

                                $value = "";
                                if ($Ricdag_rec['RICDAT'] != "" || $dati['ricdat'] == 1) {
                                    $value = $Ricdag_rec['RICDAT'];
                                }

                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");

                                $sql = "SELECT RUOCOD, RUODES FROM ANARUO WHERE 1 = 1";
                                $metadata = unserialize($Ricdag_rec['DAGMETA']);
                                if ($metadata['PARAMSTIPODATO']['CODICE']) {
                                    $FiltriQuery = array();
                                    $CodiciRuolo = explode(',', $metadata['PARAMSTIPODATO']['CODICE']);
                                    foreach ($CodiciRuolo as $CodiceRuolo) {
                                        list($RangeStart, $RangeEnd) = explode('-', $CodiceRuolo, 2);
                                        if ($RangeEnd) {
                                            $FiltriQuery[] = "RUOCOD >= $RangeStart AND RUOCOD <= $RangeEnd";
                                        } else {
                                            $FiltriQuery[] = "RUOCOD = $CodiceRuolo";
                                        }
                                    }

                                    $sql .= " AND (" . implode(' OR ', $FiltriQuery) . ")";
                                }

                                $anaruo_tab = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql . " ORDER BY RUODES", true);
                                if ($anaruo_tab) {
                                    $readonly = 'readonly="readonly"';
                                }

                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"hidden\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");

                                $valueDescRuolo = '';
                                if ($anaruo_tab) {
                                    foreach ($anaruo_tab as $anaruo_rec) {
                                        if ($anaruo_rec['RUOCOD'] == $value) {
                                            $valueDescRuolo = $anaruo_rec['RUODES'];
                                        }
                                    }
                                }

                                $htmlCampo->appendHtml("<input type=\"text\" value=\"$valueDescRuolo\" $readonly size=\"50\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO]\" />");

                                if ($anaruo_tab && $dati['ricdat'] != 1) {
                                    $htmlCampo->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">
                                        <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#raccolta\\\\[" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO\\\\]_' + this.dataset.raccolta).val('');\" data-raccolta=\"$i\"></span>
                                    </div>");

                                    $elencoTableData = array(
                                        'header' => array('Descrizione'),
                                        'body' => array()
                                    );

                                    foreach ($anaruo_tab as $anaruo_rec) {
                                        $elencoTableData['body'][] = array(
                                            array('text' => $anaruo_rec['RUOCOD'], 'attrs' => array('class' => 'ita-hidden-cell', 'data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . ']')),
                                            array('text' => $anaruo_rec['RUODES'], 'attrs' => array('class' => 'ita-hidden-cell', 'data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . '_DESCRIZIONEDELRUOLO]')),
                                            array('text' => $anaruo_rec['RUODES'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . '_DESCRIZIONE]'))
                                        );
                                    }

                                    $this->disegnaRicerca($htmlCampo, 'ricElencoRuoli' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $elencoTableData, array(
                                        'sortable' => true,
                                        'filters' => true,
                                        'paginated' => true
                                            ), 'Elenco Ruoli');
                                }
                                break;

                            case 'Codfis_Anades':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = 'readonly="readonly"';
                                }

                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");

                                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSoggetti.class.php';
                                $praLibSoggetti = new praLibSoggetti();
                                $anades_tab = $praLibSoggetti->getSoggettiFromAnades($dati['PRAM_DB'], $dati['Proric_rec']['RICFIS']);

                                /*
                                 * Controllo se il passo ha funzione di anagrafica soggetto
                                 * per trovare i passi su cui ribaltare i dati
                                 */

                                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php';

                                $campi_alias = array();
                                $campi_prefisso = 'DEFAULT';
                                if ($dati['Praclt_rec']['CLTOPEFO'] == 'FO_ANA_SOGGETTO') {
                                    $metadata_praclt = unserialize($dati['Praclt_rec']['CLTMETA']);
                                    if ($metadata_praclt && isset($metadata_praclt['METAOPEFO'])) {
                                        $campi_prefisso = $metadata_praclt['METAOPEFO']['PREFISSO_CAMPI'];

                                        foreach ($metadata_praclt['METAOPEFO'] as $metakey => $metavalue) {
                                            if (strpos($metakey, 'ALIAS_') === 0 && $metavalue) {
                                                $campi_alias[substr($metakey, 6)] = $metavalue;
                                            }
                                        }
                                    }
                                }

                                $metadata = unserialize($Ricdag_rec['DAGMETA']);
                                if ($metadata['PARAMSTIPODATO']['PREFISSO_CAMPI']) {
                                    $campi_prefisso = $metadata['PARAMSTIPODATO']['PREFISSO_CAMPI'];
                                }
                                if ($metadata['PARAMSTIPODATO']) {
                                    foreach ($metadata['PARAMSTIPODATO'] as $metakey => $metavalue) {
                                        if (strpos($metakey, 'ALIAS_') === 0 && $metavalue) {
                                            $campi_alias[substr($metakey, 6)] = $metavalue;
                                        }
                                    }
                                }

                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");

                                if ($anades_tab && $dati['ricdat'] != 1) {
                                    $datiTabellaLookup = array(
                                        'header' => array(
                                            'Cod. Fiscale',
                                            'Cognome',
                                            'Nome',
                                            'Data di<br />nascita',
                                            'Comune di<br / >nascita',
                                            'Provincia di<br / >nascita'
                                        ),
                                        'body' => array()
                                    );

                                    $codiciFiscaliUtenti = array();
                                    foreach ($anades_tab as $anades_rec) {
                                        if (in_array($anades_rec['DESFIS'], $codiciFiscaliUtenti)) {
                                            continue;
                                        }

                                        $codiciFiscaliUtenti[] = $anades_rec['DESFIS'];

                                        if ($anades_rec['DESNASDAT']) {
                                            $anades_rec['DESNASDAT'] = substr($anades_rec['DESNASDAT'], 6, 2) . '/' . substr($anades_rec['DESNASDAT'], 4, 2) . '/' . substr($anades_rec['DESNASDAT'], 0, 4);
                                        }

                                        /*
                                         * Campi visualizzati
                                         */

                                        $tabellaLookupRecord = array(
                                            $anades_rec['DESFIS'],
                                            $anades_rec['DESCOGNOME'],
                                            $anades_rec['DESNOME'],
                                            $anades_rec['DESNASDAT'],
                                            $anades_rec['DESNASCIT'],
                                            $anades_rec['DESNASPROV']
                                        );

                                        /*
                                         * Campi nascosti per mappatura soggetto
                                         */

                                        foreach (praRuolo::$SUBJECT_BASE_FIELDS as $nome_campo => $nome_colonna) {
                                            if (isset($campi_alias[$nome_campo]) && $campi_alias[$nome_campo]) {
                                                $nome_campo = $campi_alias[$nome_campo];
                                            }

                                            $nome_campo_completo = "{$campi_prefisso}_{$nome_campo}";
                                            $tabellaLookupRecord[] = array('text' => $anades_rec[$nome_colonna], 'attrs' => array('data-ita-edit-ref' => "raccolta[$nome_campo_completo]", 'class' => 'ita-hidden-cell'));
                                        }

                                        /*
                                         * Campi EXTRA
                                         */

                                        $anadesdag_tab = $praLib->GetAnadesdag($anades_rec['ROWID'], 'anades_rowid', $dati['PRAM_DB']);
                                        foreach ($anadesdag_tab as $anadesdag_rec) {
                                            $nome_campo_completo = "{$campi_prefisso}_{$anadesdag_rec['DESKEY']}";
                                            $tabellaLookupRecord[] = array('text' => $anadesdag_rec['DESVAL'], 'attrs' => array('data-ita-edit-ref' => "raccolta[$nome_campo_completo]", 'class' => 'ita-hidden-cell'));
                                        }

                                        $datiTabellaLookup['body'][] = $tabellaLookupRecord;
                                    }

                                    $this->disegnaRicerca($htmlCampo, 'ricCodfisAnades' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $datiTabellaLookup, array(
                                        'sortable' => true,
                                        'filters' => true,
                                        'paginated' => true
                                            ), 'Elenco soggetti');
                                }
                                break;

                            case 'Comune':
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"text\" style=\"$styleFldBO\" class=\"$classFldBO\" readonly=\"readonly\" maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");

                                $htmlCampo->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">
                                    <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#raccolta\\\\[" . $Ricdag_rec['DAGKEY'] . "\\\\]_' + this.dataset.raccolta).val('');\" data-raccolta=\"$i\"></span>
                                </div>");

                                $elencoTableData = array(
                                    'header' => array(
                                        'Comune',
                                        'Provincia',
                                        'CAP',
                                        'ISTAT'
                                    ),
                                    'body' => array()
                                );

                                $attrs = array(
                                    'id' => 'ricComuni'
                                );

                                $sql = "SELECT COMUNE, PROVIN, COAVPO, CISTAT FROM COMUNI WHERE 1 = 1";

                                $metadata = unserialize($Ricdag_rec['DAGMETA']);

                                if ($metadata['PARAMSTIPODATO']['REGIONE']) {
                                    $sql .= " AND REGIONE = '" . addslashes($metadata['PARAMSTIPODATO']['REGIONE']) . "'";
                                    $attrs['data-filter-regione'] = $metadata['PARAMSTIPODATO']['REGIONE'];
                                }

                                if ($metadata['PARAMSTIPODATO']['PROVINCIA']) {
                                    $sql .= " AND PROVIN = '" . addslashes($metadata['PARAMSTIPODATO']['PROVINCIA']) . "'";
                                    $attrs['data-filter-provincia'] = $metadata['PARAMSTIPODATO']['PROVINCIA'];
                                }

                                if ($metadata['PARAMSTIPODATO']['ESCLUDI']) {
                                    $listaISTAT = array_map('trim', explode(',', $metadata['PARAMSTIPODATO']['ESCLUDI']));
                                    $sql .= " AND CISTAT NOT IN ('" . implode("', '", $listaISTAT) . "')";
                                    $attrs['data-filter-escludi'] = $metadata['PARAMSTIPODATO']['ESCLUDI'];
                                }

                                $dagkey_pv = $metadata['PARAMSTIPODATO']['CAMPO_PV'] ?: $Ricdag_rec['DAGKEY'] . '_PV';
                                $dagkey_cap = $metadata['PARAMSTIPODATO']['CAMPO_CAP'] ?: $Ricdag_rec['DAGKEY'] . '_CAP';
                                $dagkey_istat = $metadata['PARAMSTIPODATO']['CAMPO_ISTAT'] ?: $Ricdag_rec['DAGKEY'] . '_ISTAT';

                                $comuni_tab = ItaDB::DBSQLSelect(ItaDB::DBOpen('COMUNI', ''), $sql);
                                if (count($comuni_tab) < 500) {
                                    foreach ($comuni_tab as $comuni_rec) {
                                        $elencoTableData['body'][] = array(
                                            array('text' => $comuni_rec['COMUNE'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . ']')),
                                            array('text' => $comuni_rec['PROVIN'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $dagkey_pv . ']')),
                                            array('text' => $comuni_rec['COAVPO'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $dagkey_cap . ']')),
                                            array('text' => $comuni_rec['CISTAT'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $dagkey_istat . ']'))
                                        );
                                    }

                                    $this->disegnaRicerca($htmlCampo, 'ricComuni' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $elencoTableData, array(
                                        'sortable' => true,
                                        'filters' => true
                                            ), 'Elenco Comuni');
                                    break;
                                }

                                $this->disegnaRicerca($htmlCampo, 'ricComuni' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $elencoTableData, array(
                                    'sortable' => true,
                                    'filters' => true,
                                    'paginated' => true,
                                    'ajax' => true,
                                    'attrs' => $attrs,
                                    'selectable' => array(
                                        'raccolta[' . $Ricdag_rec['DAGKEY'] . ']',
                                        'raccolta[' . $dagkey_pv . ']',
                                        'raccolta[' . $dagkey_cap . ']',
                                        'raccolta[' . $dagkey_istat . ']'
                                    )
                                        ), 'Elenco Comuni');
                                break;

                            case 'Indir_InsProduttivo':
                                $meta = unserialize($Ricdag_rec["DAGMETA"]);
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }

                                $value = "";
                                if ($Ricdag_rec['RICDAT'] != "" || $dati['ricdat'] == 1) {
                                    $value = $Ricdag_rec['RICDAT'];
                                }
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());
                                //
                                switch ($meta['PARAMSTIPODATO']['ANACAT']) {
                                    case "VIEEVT":
                                        $sql = "SELECT ANADES FROM ANA_COMUNE WHERE ANACAT='" . $meta['PARAMSTIPODATO']['ANACAT'] . "' ORDER BY ANADES";
                                        break;
                                    case "VIEALL":
                                        $sql = "SELECT * FROM (SELECT ANADES, ANACAT FROM ANA_COMUNE WHERE ANACAT='VIEEVT' ORDER BY ANADES) AS EVT
                                                    UNION 
                                                SELECT * FROM (SELECT ANADES, ANACAT FROM ANA_COMUNE WHERE ANACAT='VIEFO' ORDER BY ANADES) AS FO
                                                ";
                                        break;
                                    default:
                                        $sql = "SELECT ANADES FROM ANA_COMUNE WHERE ANACAT='VIEFO' ORDER BY ANADES";
                                        break;
                                }

                                $indirizzi_tab = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
                                if ($indirizzi_tab) {
                                    $readonly = "readonly=\"readonly\"";
                                }

                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" />");

                                if ($indirizzi_tab && $dati['ricdat'] != 1) {
                                    $htmlCampo->appendHtml("<div style=\"display: inline-block; vertical-align: middle; margin-left: -33px; padding: 5px 8px; background: #ffffdf;\">
                                        <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#raccolta\\\\[" . $Ricdag_rec['DAGKEY'] . "\\\\]_' + this.dataset.raccolta).val('');\" data-raccolta=\"$i\"></span>
                                    </div>");

                                    $elencoTableData = array(
                                        'header' => array('Descrizione'),
                                        'body' => array()
                                    );

                                    foreach ($indirizzi_tab as $indirizzi_rec) {
                                        if ($indirizzi_rec['ANACAT'] == 'VIEEVT') {
                                            $elencoTableData['body'][] = array(array('text' => $indirizzi_rec['ANADES'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . ']', 'style' => 'font-weight: bold;')));
                                        } else {
                                            $elencoTableData['body'][] = array(array('text' => $indirizzi_rec['ANADES'], 'attrs' => array('data-ita-edit-ref' => 'raccolta[' . $Ricdag_rec['DAGKEY'] . ']')));
                                        }
                                    }

                                    $this->disegnaRicerca($htmlCampo, 'ricElenvoVie' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $elencoTableData, array(
                                        'sortable' => true,
                                        'filters' => true,
                                        'paginated' => true
                                            ), 'Elenco Vie');
                                }
                                break;

                            case 'Foglio_catasto':
                                if ($praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                                    $readonly = "readonly=\"readonly\"";
                                }
                                $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                                /*
                                 * Disegno il campo Foglio
                                 */
                                $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                                $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                                $htmlCampo->appendHtml("<input data-default=\"" . htmlspecialchars($defaultValue) . "\" type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" />");

                                /*
                                 * Controllo esistenza DB CATA
                                 */
                                $CATA_DB = $praLib->checkExistCatasto();
                                if (!$CATA_DB) {
                                    break;
                                }

                                /*
                                 * Se il DB è presente disegno la lentina di ricerca
                                 */
                                $legame_tab = ItaDB::DBSQLSelect($CATA_DB, "SELECT TIPOIMMOBILE, FOGLIO, NUMERO, SUB FROM LEGAME LIMIT 100", true);
                                if ($legame_tab && $dati['ricdat'] != 1) {
                                    $elencoTableData = array(
                                        'header' => array(
                                            'Tipo',
                                            'Foglio',
                                            'Numero',
                                            'Sub',
                                        ),
                                        'body' => array()
                                    );

                                    $this->disegnaRicerca($htmlCampo, 'ricCatasto' . $Ricdag_rec['DAGSEQ'], $i, $htmlRicerca, $elencoTableData, array(
                                        'sortable' => true,
                                        'filters' => true,
                                        'paginated' => true,
                                        'ajax' => true,
                                        'attrs' => array(
                                            'id' => 'ricCatasto'
                                        ),
                                        'selectable' => array(
                                            'raccolta[IMM_TIPO]',
                                            'raccolta[IMM_FOGLIO]',
                                            'raccolta[IMM_PARTICELLA]',
                                            'raccolta[IMM_SUBALTERNO]'
                                        )
                                            ), 'Elenco Catasto');
                                }
                                break;
                            default:
                                break;
                        }
                    }

                    if (trim($Ricdag_rec['DAGNOT'])) {
                        $htmlCampo->appendHtml('<span class="italsoft-tooltip--click" data-title="' . htmlentities(nl2br($Ricdag_rec['DAGNOT']), ENT_COMPAT | ENT_HTML5, 'ISO-8859-1') . '">');
                        $htmlCampo->appendHtml($html->getImage(frontOfficeLib::getIcon('info'), '25px'));
                        $htmlCampo->appendHtml('</span>');
                    }

                    $htmlCampo->appendHtml("</div>");
                    $html->appendHtml($htmlCampo->getHtml());
                    $html->appendHtml($br);
                    if ($col) {
                        $html->appendHtml("</td>");
                        if ($col == $contaCol) {
                            $html->appendHtml("</tr>");
                            $contaCol = 0;
                        }
                    }
                }
                $html->appendHtml("<br>");
                if ($col) {
                    if (($col - $contaCol) && $contaCol != 0) {
                        $html->appendHtml(str_repeat("<td></td>", $col - $contaCol));
                        $html->appendHtml("<tr>");
                    }
                    $html->appendHtml("</table>");
                }

                /*
                 * Fix per Indice Raccolta con offset
                 */
//                if ($i > 1) {
                if ($dati['ricdat'] == 0) {
                    $html->appendHtml("<div style=\"margin-top: 1em; text-align: right; font-size: .7em;\" class=\"italsoft-raccolta-actions\">");

                    if ($dati['permessiPasso']['Delete']) {
                        $html->appendHtml("<button title=\"Svuota Raccolta\" class=\"italsoft-button italsoft-button--secondary italsoft-button--circled\" type=\"button\" onclick=\"if ( confirm('Sei sicuro di voler svuotare tutti i campi?') ) itaFrontOffice.clear(this.parentElement.parentElement);\">
                                        <i class=\"icon ion-close italsoft-icon\"></i>
                                    </button>");
                    }

                    if ($jj > 1) {
                        if ($dati['Ricite_rec']['ITENRA'] == "") {
                            $html->appendHtml("<button id=\"delButtonBox_$i\" title=\"Elimina Raccolta\" name=\"delButtonBox[$i]\" class=\"ita-del-box italsoft-button italsoft-button--circled\" type=\"button\">
                                                <i class=\"buttonAddRem icon ion-minus italsoft-icon\"></i>
                                            </button>");
                        }
                    }

                    $html->appendHtml("</div>");
                }

                $html->appendHtml("</div>");
                if ($jj == count($dati['Dagset_tab'])) {
                    if ($dati['Consulta'] == true) {
                        if (!$dati['permessiPasso']['Insert']) {
                            $addBox = "";
                        }
                    }
                    $html->appendHtml($addBox);
                }
            }
            $html->appendHtml("</div>");


            //Metto l'html dopo del div template se la sua posizione è Fine
            foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
                $Ricdag_rec = $praLib->ctrRicdagRec($Ricdag_rec, $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", "."));
                $br = "";
                if ($Ricdag_rec['DAGACA'] == 1) {
                    $br = "<br>";
                }
                $meta = unserialize($Ricdag_rec["DAGMETA"]);
                if ($Ricdag_rec['DAGDIZ'] == "H" && $meta['HTMLPOS'] == "Fine") {
                    $dictionaryValues_pre = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();
                    $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
                    $template = $praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
                    $defaultValue = $praLib->valorizzaTemplate($template, $dictionaryValues);
                    $html->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}\">" . $defaultValue . "</div>");
                    $html->appendHtml($br);
                    break;
                }
            }


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
                if ($dati['ricdat'] == 0) {
                    $html->appendHtml($buttonConferma);
                } else {
                    $html->appendHtml("<div style=\"text-align:center;\">" . $buttonAnnulla . $buttonAvanti . $buttonDownloadPdf . "</div>");
                }
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

        $html->appendHtml($htmlRicerca->getHtml());

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



        $html->appendHtml("<script type=\"text/javascript\" src=\"" . ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/js/duplicateBox.js?a=1\"></script>");

        return $html->getHtml();
    }

    private function setFieldValue($dati, $Ricdag_rec, $defaultValue) {
        $value = "";
        if ($Ricdag_rec['RICDAT'] != "" || $dati['ricdat'] == 1) {
            $value = $Ricdag_rec['RICDAT'];
        } else {
            if ($defaultValue !== '') {
                $value = $defaultValue;
            }
        }
        return htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
    }

    public function disegnaRicerca($html, $id, $i, $htmlRicerca, $tableData, $tableDataParams, $title = 'Elenco') {
        $html->appendHtml("
<div style=\"display: inline-block; vertical-align: middle; margin-left: 5px; font-size: 1.6em; cursor: pointer;\">
    <span title=\"$title\" class=\"icon ion-search italsoft-icon italsoft-button-ricerca\" data-ricerca=\"$id\" data-raccolta=\"$i\"></span>
</div>
");

        if (strpos($htmlRicerca->getHtml(), $id) === false) {
            $tableDataParams['stickyHeaders'] = true;
            $htmlRicerca->appendHtml("<div id=\"$id\" style=\"display: none;\">");
            $htmlRicerca->addTable($tableData, $tableDataParams);
            $htmlRicerca->appendHtml("</div>");
        }
    }

}
