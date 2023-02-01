<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateSelVieSassuolo2015 extends praTemplateLayout {

    function GetPagina($dati, $praLib, $extraParms = array()) {
        $fiereSel = ItaDB::DBSQLSelect($dati['PRAM_DB'], "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND DAGKEY='DENOM_FIERA'", false);
        //
        $html = new html();
        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");
        $html->addForm(ItaUrlUtil::GetPageUrl(array()));
        if ($dati['ricdat'] != 0) {
            $html->addHidden("event", 'annullaRaccolta');
        } else {
            $html->addHidden("event", 'submitRaccoltaMultipla');
        }
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("seq", $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden("ricnum", $dati['Ricite_rec']['RICNUM']);
        
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

            //
            // DISEGNO LA FORM RACCOLTA DATI
            //
            

            $buttonConferma = "<br /><div style=\"text-align:center;margin:10px;\">
                                 <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-form-submit italsoft-button\" type=\"submit\">
                                    <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Conferma Dati</b></div>
                                 </button>
                               </div>";
            $buttonAnnulla = "<br /><div style=\"cursor:pointer;\" style=\"margin:10px;display:inline-block;\">
                                 <button name=\"annullaDati\" class=\"italsoft-button\" type=\"submit\">
                                    <i class=\"icon ion-edit italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Modifica Dati</b></div>
                                 </button>
                               </div>";

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

            // Se utente admin spenfo i bottoni annulla e conferma dati
//            if (strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'admin') {
//                $buttonConferma = $buttonAnnulla = "";
//            } else {
//                // se la pratica è inoltrata al comune o infocamere spengo i bottoni
//                if ($dati['Proric_rec']['RICSTA'] == '91' || $dati['Proric_rec']['RICSTA'] == '01' || $dati['Proric_rec']['RICSTA'] == '98') {
//                    $buttonConferma = $buttonAnnulla = "";
//                }
//            }
            if ($dati['Consulta'] == true) {
                $buttonConferma = $buttonAnnulla = "";
            }

            //
            //Metto l'html prima del div template se la sua posizione è Inizio
            foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
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
                    $html->appendHtml("<div class=\"ita-html-container\">" . utf8_decode($defaultValue) . "</div>");
                    $html->appendHtml($br);
                    break; // Perche se ci sono raccolte multiple trova piu di un campo
                }
            }


            if ($dati['Ricite_rec']['ITERDM']) {
                $html->appendHtml("<div id=\"boxRaccolte\">");
            }

            if ($dati['Ricite_rec']['ITENRA'] == "") {
                $templateClass = "ita-div-box-template";
            }

            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php';
            $praLibGfm = new praLibGfm();

            $arrayFiereSel = unserialize($fiereSel['RICDAT']);
            $arraySelezionate = $praLibGfm->GetArrayFiereSelezionate($arrayFiereSel);

            //
            //Cancello dati aggiuntivi presenti dell fiere non selezionate
            //
            foreach ($arrayFiereSel as $rowidFiera => $value) {
                if ($value == 0) {
                    $fiere_rec = $praLibGfm->GetFiere($rowidFiera, $extraParms['GAFIERE_DB'], "rowid");
                    $anafiere_rec = $praLibGfm->GetAnafiere($fiere_rec['FIERA'], $extraParms['GAFIERE_DB']);
                    //
                    $sql = "SELECT * FROM RICDAG
                        WHERE
                        DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                        ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                        DAGSET IN 
                            (SELECT DAGSET FROM RICDAG
                             WHERE
                             DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                             ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                             DAGKEY = 'ROWIDFIERA' AND
                             RICDAT = '$rowidFiera')
                        ORDER BY DAGSEQ";
                    $Ricdag_tab = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sql, true);
                    if ($Ricdag_tab) {
                        foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                            //il primo record non deve essere mai cancellato
                            if (substr($Ricdag_rec['DAGSET'], -2) == "01") {
                                continue;
                            }
                            try {
                                $nrow = ItaDb::DBDelete($extraParms['PRAM_DB'], 'RICDAG', 'ROWID', $Ricdag_rec['ROWID']);
                                if ($nrow == 0) {
                                    $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Sincronizzazione Vie Selezionate\">
                                                           <p><span style=\"font-size:1.1em;color:red;text-decoration:underline;\">Errore nella sincronizzazione delle vie per la fiera " . $anafiere_rec['FIERA'] . "</p>
                                                       </div>");
                                    return $html->getHtml();
                                }
                            } catch (Exception $e) {
                                $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Sincronizzazione Vie Selezionate\">
                                                     <p><span style=\"font-size:1.1em;color:red;text-decoration:underline;\">Errore nella sincronizzazione delle vie per la fiera " . $anafiere_rec['FIERA'] . "<br>" . $e->getMessage() . "</p>
                                                   </div>");
                                return $html->getHtml();
                            }
                        }
                    }
                }
            }

            $i = 1;
            foreach ($arrayFiereSel as $chiaveSel => $value) {
                $sql = "SELECT * FROM RICDAG
                        WHERE
                        DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                        ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                        DAGSET IN 
                            (SELECT DAGSET FROM RICDAG
                             WHERE
                             DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                             ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                             DAGKEY = 'ROWIDFIERA' AND
                             RICDAT = '$chiaveSel')
                        ORDER BY DAGSEQ";
                $Ricdag_tab = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sql, true);
                if ($Ricdag_tab) {
                    if ($value == 1) {
                        foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                            $Ricdag_rec['DAGSET'] = $Ricdag_rec['ITEKEY'] . "_" . str_pad($i, 2, "0", STR_PAD_LEFT);
                            $sqlChk = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $Ricdag_rec['DAGNUM'] . "' AND ITEKEY = '" . $Ricdag_rec['ITEKEY'] . "' AND DAGKEY = '" . $Ricdag_rec['DAGKEY'] . "' AND DAGSET = '" . $Ricdag_rec['DAGSET'] . "' AND ROWID <> " . $Ricdag_rec['ROWID'];
                            $check_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlChk, false);
                            if ($check_rec) {
                                $nrow = ItaDB::DBDelete($extraParms['PRAM_DB'], 'RICDAG', 'ROWID', $check_rec['ROWID']);
                            }
                            try {
                                $nRows = ItaDB::DBUpdate($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec);
                            } catch (Exception $e) {
                                print_r("Errore Aggiornamento dati aggiuntivi fiera rowid $chiaveSel ----> " . $e->getMessage());
                                return false;
                            }
                        }
                        $i++;
                    }
                }
            }


            //Aggiungo dati aggiuntivi nuove fiere selezionate
            $sqlVia1 = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'VIA1' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_01'";
            $Ricdag_recVia1 = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlVia1, false);
            $sqlVia2 = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'VIA2' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_01'";
            $Ricdag_recVia2 = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlVia2, false);
            $sqlVia3 = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'VIA3' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_01'";
            $Ricdag_recVia3 = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlVia3, false);
            $sqlRowid = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'ROWIDFIERA' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_01'";
            $Ricdag_recRowid = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlRowid, false);
            $sqlFieraDesc = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'DESCFIERA' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_01'";
            $Ricdag_recFieraDesc = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlFieraDesc, false);

            //
            //Se ci sono, Svuoto i valori dei dati aggiuntivi per le fiere non piu selezionate
            //
            foreach ($arrayFiereSel as $rowidFiera => $value) {
                if ($value == 0) {
                    if (!$this->SvuotoRicdatFieraSenzaDomanda($rowidFiera, $dati['Ricite_rec'], $extraParms['PRAM_DB'])) {
                        return $html->getHtml();
                    }
                }
            }

            foreach ($arraySelezionate as $rowidFiera => $dagvalue) {
                $fiere_rec = $praLibGfm->GetFiere($rowidFiera, $extraParms['GAFIERE_DB'], "rowid");
                $anafiere_rec = $praLibGfm->GetAnafiere($fiere_rec['FIERA'], $extraParms['GAFIERE_DB']);

                $sqlInizio = "SELECT * FROM RICDAG WHERE DAGSET = '" . $dati['Ricite_rec']['ITEKEY'] . "_01' AND DAGKEY = 'ROWIDFIERA' AND RICDAT='' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                $Ricdag_recInizio = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlInizio, false);
                if ($Ricdag_recInizio) {
                    $contatoreSync = 1;
                    $chiave_arr = str_pad($contatoreSync, 2, "0", STR_PAD_LEFT);
                    $Ricdag_recInizio['RICDAT'] = $rowidFiera;
                    $nRows = ItaDB::DBUpdate($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_recInizio);

                    $sqlInizio2 = "SELECT * FROM RICDAG WHERE DAGSET = '" . $dati['Ricite_rec']['ITEKEY'] . "_01' AND DAGKEY = 'DESCFIERA' AND RICDAT='' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                    $Ricdag_recInizio2 = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlInizio2, false);
                    $Ricdag_recInizio2['RICDAT'] = $anafiere_rec['FIERA'];
                    $nRows = ItaDB::DBUpdate($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_recInizio2);
                } else {
                    $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'ROWIDFIERA' AND RICDAT='$rowidFiera' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                    $Ricdag_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sql, false);
                    if (!$Ricdag_rec) {
                        $maxDagsetSync = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT MAX(DAGSET) AS DAGSET FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'", false);
                        $contatoreSync = substr($maxDagsetSync['DAGSET'], -2) + 1;
                        $chiave_arr = str_pad($contatoreSync, 2, "0", STR_PAD_LEFT);

                        // VIA 1
                        $Ricdag_rec_new1 = $Ricdag_recVia1;
                        $Ricdag_rec_new1['ROWID'] = 0;
                        $Ricdag_rec_new1['RICDAT'] = "";
                        $Ricdag_rec_new1['DAGSET'] = $dati['Ricite_rec']['ITEKEY'] . "_$chiave_arr";
                        $nRows1 = ItaDB::DBInsert($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec_new1);

                        //VIA 2
                        $Ricdag_rec_new2 = $Ricdag_recVia2;
                        $Ricdag_rec_new2['ROWID'] = 0;
                        $Ricdag_rec_new2['RICDAT'] = "";
                        $Ricdag_rec_new2['DAGSET'] = $dati['Ricite_rec']['ITEKEY'] . "_$chiave_arr";
                        $nRows2 = ItaDB::DBInsert($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec_new2);

                        //VIA 3
                        $Ricdag_rec_new3 = $Ricdag_recVia3;
                        $Ricdag_rec_new3['ROWID'] = 0;
                        $Ricdag_rec_new3['RICDAT'] = "";
                        $Ricdag_rec_new3['DAGSET'] = $dati['Ricite_rec']['ITEKEY'] . "_$chiave_arr";
                        $nRows3 = ItaDB::DBInsert($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec_new3);

                        //ROWID FIERA
                        $Ricdag_rec_new4 = $Ricdag_recRowid;
                        $Ricdag_rec_new4['ROWID'] = 0;
                        $Ricdag_rec_new4['RICDAT'] = $rowidFiera;
                        $Ricdag_rec_new4['DAGSET'] = $dati['Ricite_rec']['ITEKEY'] . "_$chiave_arr";
                        $nRows = ItaDB::DBInsert($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec_new4);

                        //DESC FIERA
                        $Ricdag_rec_new5 = $Ricdag_recFieraDesc;
                        $Ricdag_rec_new5['ROWID'] = 0;
                        $Ricdag_rec_new5['RICDAT'] = $anafiere_rec['FIERA'];
                        $Ricdag_rec_new5['DAGSET'] = $dati['Ricite_rec']['ITEKEY'] . "_$chiave_arr";
                        $nRows = ItaDB::DBInsert($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec_new5);
                    }
//                    else {
//                        $Ricdag_recRowidFiera['RICDAT'] = $rowidFiera;
//                        $nRows = ItaDB::DBUpdate($extraParms['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_recRowidFiera);
//                    }
                }
            }

            //
            //Disegno la pagina
            //
            $k = 0;
            $anaditta_rec = $praLibGfm->GetAnaditta($dati['Proric_rec']['RICFIS'], $extraParms['GAFIERE_DB'], "fiscale");
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());
            foreach ($arraySelezionate as $rowidFiera => $value) {
                $sql = "SELECT * FROM RICDAG WHERE DAGKEY = 'DOMANDA_POSTEGGIO' AND RICDAT='Si' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                            DAGSET IN (SELECT DAGSET FROM RICDAG 
                            WHERE
                            DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                            DAGKEY='FIERA_ROWID' AND
                            RICDAT = '$rowidFiera')";
                $Ricdag_recDomPost = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sql, false);
                $vecchiaVia = "";
                $fiere_rec = $praLibGfm->GetFiere($rowidFiera, $extraParms['GAFIERE_DB'], "rowid");
                $anafiere_rec = $praLibGfm->GetAnafiere($fiere_rec['FIERA'], $extraParms['GAFIERE_DB']);
                if ($anaditta_rec && $Ricdag_recDomPost) {
                    $sql = "SELECT * FROM FIERECOM WHERE CODICE=" . $anaditta_rec['CODICE'] . " AND FIERA ='" . $fiere_rec['FIERA'] . "' AND  SUBSTRING(DATA, 1, 4) = '" . (date('Y') - 1) . "' ";
                    $fierecom_rec = ItaDB::DBSQLSelect($extraParms['GAFIERE_DB'], $sql, false);

                    if ($fierecom_rec) {
                        //$fierecom_rec['CODICEVIA'] = "000117";
                        $sqlVia = "SELECT * FROM ANA_COMUNE WHERE ANACOD = '" . $fierecom_rec['CODICEVIA'] . "'";
                        $vie_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlVia, false);
                        $vecchiaVia = $vie_rec["ANADES"];
                    }
                }

                $k++;
                //$j = str_repeat("0", 2 - strlen($k)) . $k;
                $sql = "SELECT * FROM RICDAG
                        WHERE
                        DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                        ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                        DAGSET IN 
                            (SELECT DAGSET FROM RICDAG
                             WHERE
                             DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND
                             ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND
                             DAGKEY = 'ROWIDFIERA' AND
                             RICDAT = '$rowidFiera')
                        ORDER BY DAGSEQ";
                $dati['Ricdag_tab'] = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sql, true);
                $chiave_arr = substr($dati['Ricdag_tab'][0]['DAGSET'], -2);
                //
                $i = $chiave_arr;
                $html->appendHtml("<div id=\"boxRaccolta_$i\" name=\"boxRaccolta_$i\" style=\"margin-bottom:5px;\" class=\"$templateClass ita-box-raccolta ui-widget-content ui-corner-all\">");
                if ($dati['Ricite_rec']['ITERDM']) {
                    $html->appendHtml("<div style=\"padding-left:5px;\" id=\"headerRaccolta_$i\" class=\"ui-widget-header ui-corner-all ita-header-raccolta\">" . $anafiere_rec['FIERA'] . "</div>");
                }
                if ($dati['Ricite_rec']['ITECOL'] != 0) {
                    $col = $dati['Ricite_rec']['ITECOL'];
                }
                if ($col) {
                    $html->appendHtml("<table class=\"tableAutocert\">");
                }
                $contaCol = 0;
                foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
                    $styleLblBO = $Ricdag_rec['DAGLABSTYLE'];
                    $styleFldBO = $Ricdag_rec['DAGFIELDSTYLE'];
                    $classPosLabel = $disabled = $checked = "";

                    $br = $campoObl = $class = "";
                    // Metto un br quando ce il flag a capo tranne se è il primo campo
                    if ($Ricdag_rec['DAGACA'] == 1 && $col == 0) {
                        $br = "<br>";
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
                    if ($Ricdag_rec['DAGTIP'] == 'Cap_InsProduttivo') {
                        continue;
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Comune_InsProduttivo') {
                        continue;
                    }
                    if ($Ricdag_rec['DAGTIP'] == 'Prov_InsProduttivo') {
                        continue;
                    }

                    $htmlCampo = new html();
                    if ($Ricdag_rec['DAGTIC'] != "Html") {
                        $htmlCampo->appendHtml("<div class=\"ita-field\">");
                    } else {
                        $htmlCampo->appendHtml("<div class=\"ita-html-container\">");
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
                    }

                    switch ($Ricdag_rec['DAGTIC']) {
                        case 'Select':
                            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='VIE' ORDER BY ANADES LIMIT 50";
                            $indirizzi_tab = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
                            $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                            $htmlCampo->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\"><b>$etichetta $campoObl</b></label>");
                            $htmlCampo->appendHtml("<select style=\"$styleFldBO;vertical-align:middle;display:inline-block;\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\" $disabled>");
                            foreach ($indirizzi_tab as $key => $indirizzi_rec) {
                                $Sel = "";
                                $option = $indirizzi_rec['ANADES']; //$indirizzi_rec['ANACOD'];
                                $nodevalue = $indirizzi_rec['ANADES'];

                                if ($Ricdag_rec['RICDAT']) {
                                    if ($Ricdag_rec['RICDAT'] == $option) {
                                        $Sel = "selected";
                                    }
                                } else {
                                    if ($vecchiaVia == $option) {
                                        $Sel = "selected";
                                    }
                                }
                                $htmlCampo->appendHtml("<option $Sel class=\"optSelect\" value=\"$option\">$nodevalue</option>';");
                            }
                            $htmlCampo->appendHtml("</select>");

                            $azione_img_tag = "<img class=\"imgLink\" src=\"" . ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/images/maps.png" . "\" style=\"border:0px;\" />";
                            $h_ref = "<a href=\"#\">$azione_img_tag</a>";
                            //$h_ref = "<a href=\"javascript:openMaps('SASSUOLO', 'MO')\">$azione_img_tag</a>";
                            $htmlCampo->appendHtml("<div id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i" . "_maps" . "\" class=\"ita-open-maps buttonlink ui-corner-all ui-widget-header\" style=\"vertical-align:middle;display:inline-block;width:16px;height:16px;padding:0px;margin:0px\">$h_ref</div>");
                            break;
                        case 'Text':
                            switch ($Ricdag_rec['DAGKEY']) {
                                case "ROWIDFIERA":
                                    $value = $rowidFiera;
                                    break;
                                case "DESCFIERA":
                                    $value = $anafiere_rec['FIERA'];
                                    break;
                            }

                            $html->appendHtml("<input type=\"text\" style=\"$styleFldBO\" value=\"$value\" name=\"raccolta[$i][" . $Ricdag_rec['DAGKEY'] . "]\" id=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]_$i\"\" />");
                            break;
                        default:
                            break;
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
                $html->appendHtml("</div>");
            }
            $html->appendHtml("</div>");


            //Metto l'html dopo del div template se la sua posizione è Fine
            foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
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
                    $html->appendHtml("<div class=\"ita-html-container\">" . utf8_decode($defaultValue) . "</div>");
                    $html->appendHtml($br);
                    break;
                }
            }


            // Se utente admin spenfo i bottoni annulla e conferma dati
//            if (strtolower(frontOfficeApp::$cmsHost->getUserName()) == 'admin') {
//                $buttonConferma = $buttonAnnulla = "";
//            } else {
//                // se la pratica è inoltrata al comune o infocamere spengo i bottoni
//                if ($dati['Proric_rec']['RICSTA'] == '91' || $dati['Proric_rec']['RICSTA'] == '01' || $dati['Proric_rec']['RICSTA'] == '98') {
//                    $buttonConferma = $buttonAnnulla = "";
//                }
//            }
            if ($dati['Consulta'] == true) {
                $buttonConferma = $buttonAnnulla = "";
            }

            if (!$dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
                $html->appendHtml("<br>");
                if ($dati['ricdat'] == 0) {
                    $html->appendHtml($buttonConferma);
                } else {
                    $html->appendHtml("<div style=\"text-align:center;\">" . $buttonAnnulla . $buttonDownloadPdf . "</div>");
                }
            }
        }


        // 4 -- NOTE DEL PASSO
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNote.class.php';
        $praHtmlNote = new praHtmlNote();
        $html->appendHtml($praHtmlNote->GetNote($dati));
        // 3 -- FINE NOTE DEL PASSO

        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA
        
        $html->appendHtml($this->disegnaFooter($dati, $extraParms));
        
        $html->appendHtml("</form>");
        $html->appendHtml("</div>");

        $html->appendHtml("<script type=\"text/javascript\" src=\"" . ITA_PRATICHE_PUBLIC . "/PRATICHE_italsoft/js/duplicateBox.js?a=1\"></script>");
        $html->appendHtml("<SCRIPT>");
        $html->appendHtml("$(function () {openMaps('SASSUOLO', 'MO');});");
        $html->appendHtml("</SCRIPT>");

        return $html->getHtml();
    }

    function SvuotoRicdatFieraSenzaDomanda($rowidFiera, $ricite_rec, $PRAM_DB) {
        $sql = "SELECT * FROM RICDAG
                        WHERE
                        DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND
                        ITEKEY = '" . $ricite_rec['ITEKEY'] . "' AND
                        DAGSET IN 
                            (SELECT DAGSET FROM RICDAG
                             WHERE
                             DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND
                             ITEKEY = '" . $ricite_rec['ITEKEY'] . "' AND
                             DAGKEY = 'FIERA_ROWID' AND
                             RICDAT = '$rowidFiera')
                        ORDER BY DAGSEQ";
        try {
            $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        } catch (Exception $e) {
            print_r("Errore Selezione dati aggiuntivi fiera rowid $rowidFiera ----> " . $e->getMessage());
            return false;
        }
        //
        foreach ($Ricdag_tab as $ricdag_rec) {
            $ricdag_rec['RICDAT'] = "";
            try {
                $nRows = ItaDB::DBUpdate($PRAM_DB, "RICDAG", 'ROWID', $ricdag_rec);
            } catch (Exception $e) {
                print_r("Errore Aggiornamento dati aggiuntivi fiera rowid $rowidFiera ----> " . $e->getMessage());
                return false;
            }
        }
        return true;
    }

}

?>
