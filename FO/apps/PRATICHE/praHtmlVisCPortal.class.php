<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlDescBox
 *
 * @author Andrea
 */
class praHtmlVisCPortal {

    function DisegnaPagina($dati, $extraParms) {
        $html = new html();
        $praLib = new praLib();
        $whereStato = $whereProc = "";
        $html->appendHtml($this->addJsComunica());

        if ($extraParms['TipoFiltro'] == "") {
            $html->appendHtml("<div class=\"divSelect\">");
            //
            //Select stato
            //
            $html->appendHtml("<label class=\"lblSelect\"><b>Scegli le richieste da visualizzare</b></label>");
            $html->appendHtml("<select name=\"tipo\" id=\"tipo\" onchange=submit('');>");
            $html->appendHtml("<option class=\"optSelect\" value=\"\">Tutte</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "99")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"99\">Richieste in corso</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "98")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"98\">Richieste Annullate</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "01")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"01\">Richieste inoltrate</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "81")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"81\">Richieste Inoltrata ad Agenzia</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "91")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"91\">Richieste Inviate per la comunicazione unica d'impresa</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "02")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"02\">Richieste Acquisite</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "03")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"03\">Richieste Chiuse</option>';");
            $Sel = "";
            if ($extraParms['Tipo'] == "WITHATTACH")
                $Sel = "selected";
            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"WITHATTACH\">Richieste con Allegati</option>';");
            $html->appendHtml("</select>");
            $html->appendHtml("</div>");
        }

        $html->appendHtml("<br>");
        switch ($extraParms['Tipo']) {
            case "02": //Acquisita
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = "LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM";
                $whereStato = " AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH='') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN=''))";
                break;
            case "03": //Chiusa
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = "LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM";
                $whereStato = "AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH<>'') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN<>''))";
                break;
            case "WITHATTACH": //Con Allegati Pubblicati
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = " INNER JOIN PASDOC PASDOC ON PROGES.GESNUM = SUBSTRING(PASDOC.PASKEY, 1, 10) 
                          INNER JOIN PROPAS PROPAS ON PROPAS.PRONUM = PROGES.GESNUM";
                $whereStato = "AND PROPAS.PROPUBALL=1 AND PASDOC.PASPUB=1";
                break;
            default:
                if ($extraParms['Tipo']) {
                    $whereStato = "AND RICSTA = '" . $extraParms['Tipo'] . "'";
                }
                break;
        }

        if ($extraParms['TipoFiltro']) {
            $whereStato = "AND RICSTA = '" . $extraParms['TipoFiltro'] . "'";
        }
        if ($extraParms['procedi']) {
            $html->appendHtml("<div style=\"font-size:1.5em;color:blue;\">Richieste <b>" . $dati['Anapra_rec']['PRADES__1'] . $dati['Anapra_rec']['PRADES__2'] . $dati['Anapra_rec']['PRADES__3'] . "</b> in corso</div>");
            $whereProc = "AND RICPRO = '" . $extraParms['procedi'] . "'";
        }


        $sql = "SELECT
                    $field
                    PRORIC.RICNUM AS RICNUM,
                    PRORIC.RICPRO AS RICPRO,
                    PRORIC.RICTIM AS RICTIM,
                    PRORIC.RICNPR AS RICNPR,
                    PRORIC.RICDPR AS RICDPR,
                    PRORIC.RICDAT AS RICDAT,
                    PRORIC.RICDRE AS RICDRE,
                    PRORIC.RICSTA AS RICSTA,
                    PRORIC.RICFIS AS RICFIS,
                    PRORIC.RICORE AS RICORE,
                    PRORIC.RICRPA AS RICRPA,
                    PRORIC.RICAGE AS RICAGE,
                    PRORIC.RICSTT AS RICSTT,
                    PRORIC.RICATT AS RICATT,
                    PROGES.GESNUM AS GESNUM,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESDCH AS GESDCH
                FROM 
                    PRORIC PRORIC
                LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                $join
                WHERE 
                    PRORIC.RICFIS = '" . $dati['fiscale'] . "' AND PRORIC.RICSTA<>'OF' $whereStato $whereProc
                GROUP BY 
                    PRORIC.RICNUM
                ORDER BY 
                    PRORIC.RICNUM DESC";

        $Proric_tab = ItaDB::DBSQLSelect($extraParms["PRAM_DB"], $sql, true);

        if ($Proric_tab == "") {
            $count = 0;
        } else {
            $count = count($Proric_tab);
        }
        $html->appendHtml("<div style=\"float:right;font-size:1.2em;\">");
        $html->appendHtml("<b>Totale richieste trovate: " . $count . "</b>");
        $html->appendHtml("</div>");
        if ($extraParms['config']['view'] == 0) {
            $html->appendHtml('<table id="tabella_allegati12" class="tabella_allegati tablesorter" border="2" cellpadding="0" cellspacing="0" width="100%">');
            $html->appendHtml("<thead>");
            $html->appendHtml("<tr>");
            $html->appendHtml("<th align=\"center\">Numero<br>Richiesta</th>");
            $html->appendHtml("<th align=\"center\">N./Data<br>Protocollo</th>");
            $html->appendHtml("<th align=\"center\">Procedimento</th>");
            $html->appendHtml("<th align=\"center\">Dati Impresa</th>");
            $html->appendHtml("<th align=\"center\">Inizio<br>del</th>");
            $html->appendHtml("<th align=\"center\">Stato<br>Inoltro</th>");
            $html->appendHtml("<th align=\"center\">Stato<br>Pratica</th>");
            $html->appendHtml("<th align=\"center\">Data<br>Acquisizione</th>");
            $html->appendHtml("<th align=\"center\">Data<br>Chiusura</th>");
            if ($extraParms['modo'] != "cportal") {
                $html->appendHtml("<th align=\"center\">Annulla</th>");
                $html->appendHtml("<th align=\"center\">Integra</th>");
            }
            $html->appendHtml("<th align=\"center\">Allegati<br>Pubblicati</th>");
            $html->appendHtml("</tr>");
            $html->appendHtml("</thead>");
        } else {
            $html->appendHtml("<br><br>");
        }

        $procIntegrazione = $this->GetProcedimentoIntegrazione($extraParms['PRAM_DB']);
        $html->appendHtml('<tbody>');
        foreach ($Proric_tab as $Proric_rec) {
            $data_inoltro = $descStato = $il = $dataAcq = $dataChi = "";
            $Prasta_rec = $arrayDesc = $datiImpresa = array();
            $Anapra_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM ANAPRA ANAPRA
                        WHERE PRANUM='" . $Proric_rec['RICPRO'] . "'", false);
//            $Anapra_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM ANAPRA ANAPRA
//                        LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
//                        WHERE PRANUM='" . $Proric_rec['RICPRO'] . "'", false);
            if ($Proric_rec['RICRPA']) {
                $datiImpresa = $this->GetdatiImpresa($Proric_rec['RICRPA'], $extraParms['PRAM_DB']);
            } else {
                $datiImpresa = $this->GetdatiImpresa($Proric_rec['RICNUM'], $extraParms['PRAM_DB']);
            }
            $data_ric = substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
            if ($Proric_rec['RICDAT'] != "") {
                $data_inoltro = substr($Proric_rec['RICDAT'], 6, 2) . "/" . substr($Proric_rec['RICDAT'], 4, 2) . "/" . substr($Proric_rec['RICDAT'], 0, 4);
            }
            $numero = strval(intval(substr($Proric_rec['RICNUM'], 4, 6))) . "/" . substr($Proric_rec['RICNUM'], 0, 4);
            $ric_sta = $Proric_rec['RICSTA'];

            $funzione = '';
            switch ($ric_sta) {
                case "01" :
                    $t_stato = "Inoltrata";
                    $il = "il";
                    break;
                case "91" :
                    $t_stato = "Inviate per la comunicazione Unica d'impresa";
                    $il = "il";
                    $href = ItaUrlUtil::GetPageUrl(array('event' => 'annullaInfocamere', 'ricnum' => $Proric_rec['RICNUM']));
                    $funzione = "<br><br><button class=\"italsoft-button\" onclick=\"annullaComunica('$href','" . $Proric_rec['RICNUM'] . "');return false;\">Annulla Inoltro a comunica</button>";
                    break;
                case "81" :
                    $t_stato = "Inoltrata ad Agenzia " . $Proric_rec['RICAGE'];
                    $il = "il";
                    break;
                case "98" :
                    $t_stato = "Annullata dal Richiedente";
                    $il = "il";
                    break;
                case "99" :
                    $t_stato = "Non Completata la richiesta";
                    break;
            }

            if ($extraParms['config']['view'] == 0) {
                $color = $rifRichiesta = "";
                if ($Proric_rec['RICRPA']) {
                    $color = "color:blue;";
                    $rifRichiesta = "<span style=\"color:black\">Rif. " . strval(intval(substr($Proric_rec['RICRPA'], 4, 6))) . "/" . substr($Proric_rec['RICRPA'], 0, 4) . "</span>";
                }
                $html->appendHtml("<tr>");
                $href = ItaUrlUtil::GetPageUrl(array('p' => $extraParms['config']['online_page'], 'event' => 'navClick', 'direzione' => 'primoRosso', 'ricnum' => $Proric_rec['RICNUM']));
                $desc_node = "<a href=\"$href\">" . $numero . "</a><br>$rifRichiesta";
                $html->appendHtml("<td  class=\"txttab\"  style=\"font-size:0.8em;\"><b>" . $desc_node . "</b></td>");

                $numProt = $dataProt = "";
                if ($Proric_rec['RICNPR'] != 0) {
                    $numProt = substr($Proric_rec['RICNPR'], 4) . "/" . substr($Proric_rec['RICNPR'], 0, 4);
                    $dataProt = substr($Proric_rec['RICDPR'], 6, 2) . "/" . substr($Proric_rec['RICDPR'], 4, 2) . "/" . substr($Proric_rec['RICDPR'], 0, 4);
                }
                $html->appendHtml("<td  class=\"txttab\"  style=\"font-size:0.8em;\">$numProt<br>$dataProt</td>");

                $Anaset_rec = $praLib->GetAnaset($Proric_rec['RICSTT'], "codice", $extraParms['PRAM_DB']);
                $Anaatt_rec = $praLib->GetAnaatt($Proric_rec['RICATT'], "codice", $extraParms['PRAM_DB']);
                //$html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\"><b>" . $Anapra_rec['PRANUM'] . "</b> - <br>" . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "</td>");
                $descProc = $Anaset_rec['SETDES'] . "<br>" . $Anaatt_rec['ATTDES'] . "<br><b>" . $Anapra_rec['PRANUM'] . "</b> - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">$descProc</td>");
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">" . $datiImpresa['DENOMIMPRESA'] . "<br>" . $datiImpresa['FISCALE'] . "</td>");
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">" . $data_ric . "<br>" . $Proric_rec['RICORE'] . "</td>");
            }
            if ($Proric_rec['RICRPA']) {
                $Proges_rec_int = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICRPA'] . "'", false);
                if ($Proges_rec_int) {
                    $Propas_tab_integra = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROPAS WHERE PRONUM='" . $Proges_rec_int['GESNUM'] . "' AND PRORIN<>''", true);
                    foreach ($Propas_tab_integra as $Propas_rec_integra) {
                        if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                            if ($Propas_rec_integra['PROINI']) {
                                $descStato = "Acquisita dall'ente";
                                $dataAcq = substr($Propas_rec_integra['PROINI'], 6, 2) . "/" . substr($Propas_rec_integra['PROINI'], 4, 2) . "/" . substr($Propas_rec_integra['PROINI'], 0, 4);
                            }
                            if ($Propas_rec_integra['PROFIN']) {
                                $descStato = "Chiusa";
                                $dataChi = substr($Propas_rec_integra['PROFIN'], 6, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 4, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 0, 4);
                            }
                        }
                    }
                }
            } else {
                $Proges_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICNUM'] . "'", false);
                if ($Proges_rec) {
                    $funzione = '';
                    $dataAcq = substr($Proges_rec['GESDRE'], 6, 2) . "/" . substr($Proges_rec['GESDRE'], 4, 2) . "/" . substr($Proges_rec['GESDRE'], 0, 4);
                    if ($Proges_rec['GESDCH']) {
                        $dataChi = substr($Proges_rec['GESDCH'], 6, 2) . "/" . substr($Proges_rec['GESDCH'], 4, 2) . "/" . substr($Proges_rec['GESDCH'], 0, 4);
                    }
                    $Prasta_rec = $praLib->GetPrasta($Proges_rec['GESNUM'], "codice", $extraParms['PRAM_DB']);
                    if ($Prasta_rec['STAPST'] != 0) {
                        if ($Proges_rec['GESCLOSE']) {
                            $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
                            $arrayDesc = explode(" - ", $Prasta_rec['STADEX']);
                            $lastDesc = end($arrayDesc);
                            $descStato = $Prasta_rec['STADES'] . " - $lastDesc";
                        } else {
                            $descStato = $Prasta_rec['STADES'];
                        }
                    } else {
                        $descStato = "Acquisita dall'ente";
                        if ($Proges_rec['GESDCH']) {
                            $descStato = "Chiusa";
                        }
                    }
                }
            }

            if ($extraParms['config']['view'] == 0) {
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">" . $t_stato . "<br>$il $data_inoltro " . $Proric_rec['RICTIM'] . "$funzione </td>");
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">$descStato</td>");
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">$dataAcq</td>");
                $html->appendHtml("<td  class=\"txttab\" style=\"font-size:0.8em;$color\">$dataChi</td>");

                if ($extraParms['modo'] != "cportal") {
                    $html->appendHtml("<td title=\"Invia Mail di Richiesta Annullamento Pratica\"  class=\"txttab\" style=\"font-size:0.8em;\">");
                    if (($Proric_rec['RICSTA'] == '01' || $Proric_rec['RICSTA'] == '91') && $descStato != "Chiusa") {
                        $href = ItaUrlUtil::GetPageUrl(array('event' => 'sceltaMotivo', 'ricnum' => $Proric_rec['RICNUM']));
                        $html->appendHtml("<a href=\"javascript:\" onclick=\"location.replace('$href');\">");
                        $html->appendHtml("<img title=\"Avvio procedura di Annullamento\" src=\"" . ITA_PRATICHE_PUBLIC . "/SUAP_praVis/images/mail-256.jpg\" width=\"30px\" height=\"30px\" style=\"border:0px;\" /></a>");
                    }
                    $html->appendHtml("</td>");

                    //$html->appendHtml("<td  class=\"txttab_pravis\">");
                    $html->appendHtml("<td  class=\"txttab\">");
                    if (($Proric_rec['RICSTA'] == '01' || $Proric_rec['RICSTA'] == '91') && $Proric_rec['RICRPA'] == "" && $procIntegrazione && $descStato != "Chiusa") {
                        $href = ItaUrlUtil::GetPageUrl(array('p' => $extraParms['config']['online_page'], 'event' => 'openBlock', 'procedi' => $procIntegrazione, 'padre' => $Proric_rec['RICNUM'], 'tipo' => 'integrazione'));
                        $html->appendHtml("<a href=\"javascript:\" onclick=\"location.replace('$href');\">");
                        $html->appendHtml("<img title=\"Avvia procedura di integrazione\" src=\"" . ITA_PRATICHE_PUBLIC . "/SUAP_praVis/images/integrazione.gif\" width=\"30px\" height=\"30px\" style=\"border:0px;\" /></a>");
                    }
                    $html->appendHtml("</td>");
                }

                $html->appendHtml('<td align="center" class="txttab">');
                $Propas_tab = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROPAS WHERE PRONUM='" . $Proric_rec['GESNUM'] . "' AND PROPUBALL = 1 ORDER BY PROSEQ", true);
                if ($Propas_tab) {
                    $href = ItaUrlUtil::GetPageUrl(array('p' => $extraParms['config']['attachment_page'], 'event' => 'dettaglio', 'ricnum' => $Proric_rec['RICNUM'], 'gesnum' => $Proric_rec['GESNUM']));
                    $html->appendHtml("<a href=\"javascript:\" onclick=\"location.replace('$href');\">");
                    $html->appendHtml("<img title=\"Vedi Allegati Pubblicati\" src=\"" . ITA_PRATICHE_PUBLIC . "/SUAP_praVis/images/attach.png\" width=\"30px\" height=\"30px\" style=\"border:0px;\" /></a>");
                }
                $html->appendHtml("</td>");
                $html->appendHtml("</tr>");
            } elseif ($extraParms['config']['view'] == 1) {
                $html->appendHtml("<div>");
                $html->appendHtml("<h3> N. Richiesta: " . $numero . "</h3>");
                $html->appendHtml("<h3> N. Procedimento: " . $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "</h3>");
                $html->appendHtml("<h3> Richiesta del: " . $data_ric . " ore " . $Proric_rec['RICORE'] . "</h3>");
                $html->appendHtml("<h3> Denominazione Impresa: " . $datiImpresa['DENOMIMPRESA'] . "</h3>");
                $html->appendHtml("<h3> Codice Fiscale Impresa: " . $datiImpresa['FISCALE'] . "</h3>");
                $html->appendHtml("<h3> Stato: " . $t_stato . "</h3>");
                if ($Proric_rec['RICTIM'])
                    $ore = "ore";
                $html->appendHtml("<h3> Inoltro del: " . $data_inoltro . " $ore " . $Proric_rec['RICTIM'] . "</h3>");
                $html->appendHtml("<h3> Stato Pratica: $descStato</h3>");
                $html->appendHtml("<h3> Data Acquisizione: $dataAcq</h3>");
                $html->appendHtml("<h3> Data Chiusura: $dataChi</h3>");
                $html->appendHtml("<span><b>---------------------------------------------------------------------------------------------</b></span>");
                $html->appendHtml("</div>");
            }
        }

        if ($extraParms['config']['view'] == 0) {
            $html->appendHtml('</tbody>');
            $html->appendHtml("</table>");
            //
            $itaTableSorter = new itaTableSorter();
            $html->appendHtml($itaTableSorter->mostraPager("12"));
        }
        return $html->getHtml();
    }

    private function addJsComunica() {
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= 'L\\\'annullamento dell\\\'inoltro del file zip per la comunicazione unica d\\\'impresa permette di sbloccare la richiesta on-line per:<br>';
        $content .= '- inviare nuovamente il file zip dopo aver corretto eventuali errori o refusi,<br>';
        $content .= '- rispondere (NO) alla domanda di contestualità alla comunicazione unica per inviare direttamente all\\\'ente la richiesta.<br><br>';
        $content .= 'Confermi l\\\'annullamento?</div>';
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
            function annullaComunica(url, richiesta){
                $('<div id =\"praVisCancelComunica\">$content</div>').dialog({
                title:\"Annullamento Inoltro Comunicazione unica d'impresa.\",
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

    public function GetDatiImpresa($codice, $PRAM_DB) {
        $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice' AND DAGTIP<>'' AND DAGTIP = 'DenominazioneImpresa'", false);
        $Denominazione = $Ricdag_rec_den['RICDAT'];
        $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice' AND DAGTIP<>'' AND DAGTIP = 'Codfis_InsProduttivo'", false);
        $Fiscale = $Ricdag_rec_fis['RICDAT'];
        //
        if ($Denominazione == "") {
            $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice'
                                  AND DAGKEY = 'DICHIARANTE_COGNOME_NOME' AND RICDAT<>'' AND DAGSET LIKE '%_01'", false);
        }
        if ($Fiscale == "") {
            $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice'
                                  AND DAGKEY = 'DICHIARANTE_CODICEFISCALE_CFI' AND RICDAT<>'' AND DAGSET LIKE '%_01'", false);
        }
        $Denominazione = $Ricdag_rec_den['RICDAT'];
        $Fiscale = $Ricdag_rec_fis['RICDAT'];

        return array(
            "DENOMIMPRESA" => $Denominazione,
            "FISCALE" => $Fiscale
        );
    }

    public function GetProcedimentoIntegrazione($PRAM_DB) {
        $praLib = new praLib();
        $Filent_rec = $praLib->GetFilent(1, $PRAM_DB);
        if ($Filent_rec['FILDE5']) {
            return $Filent_rec['FILDE5'];
        } else {
            if ($Filent_rec['FILDE4'] == "S" && $Filent_rec['FILDE3']) {
                $pramMaster = ItaDB::DBOpen('PRAM', $Filent_rec['FILDE3']);
                $Filent_rec_master = $praLib->GetFilent(1, $pramMaster);
                if ($Filent_rec_master['FILDE5']) {
                    return $Filent_rec_master['FILDE5'];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

}

?>
