<?php

class praVisSe_admin {

    public $PRAM_DB;
    public $cbs_credenziali;
    public $av_cittadini;
    public $config;
    public $praLib;
    public $praErr;
    static public $param;
    static public $html_out;

    public function getClassInstance($class_name, &$object) {
        if (!class_exists($class_name, false)) {
            return false;
        }
        $object = new $class_name();
        return true;
    }

    public function setParam($param) {
        self::$param = $param;
    }

    public function getParam() {
        return self::$param;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }

    public function parseEvent() {
        if (!$_POST) {
            $_POST = $_GET;
        }
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', praVis_admin::$param['ditta']);

        if (!$this->getClassInstance('suapErr', $this->praErr)) {
            return "Errore Fatale";
        }
        if (!$this->getClassInstance('praLib', $this->praLib)) {
            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0056', "Errore inizializzazione praLib", __CLASS__);
        }

        if (!$this->getClassInstance('html', $html)) {
            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0056', "Errore inizializzazione html", __CLASS__);
        }

        $html->addForm(ItaUrlUtil::GetPageUrl(array()));
        if ($_POST['elenca']) {
            $_POST['event'] = 'openTable';
        }

        switch ($_POST['event']) {
            default:
                $html->appendHtml("<div class=\"divSelect\">");
                $html->appendHtml("<label class=\"labelForm\"><b>Utente</b></label>");
                $html->appendHtml("<input type=\"text\" name=\"utente\" id=\"utente\"</input>");
                $html->appendHtml("<br>");
                $html->appendHtml("<label class=\"labelForm\"><b>N. Segnalazione</b></label>");
                $html->appendHtml("<input type=\"text\" name=\"pratica\" id=\"pratica\" maxlength=\"6\" size=\"7\"</input>");
                $html->appendHtml("<br>");
                $html->appendHtml("<label class=\"labelForm\"><b>Anno</b></label>");
                $html->appendHtml("<input type=\"text\" name=\"anno\" id=\"anno\" maxlength=\"4\" size=\"4\" </input>");
                $html->appendHtml("<br>");

                $html->appendHtml("<div class=\"divSelect\">");
                $html->appendHtml("<label class=\"labelForm\"><b>Stato</b></label>");
                $html->appendHtml("<select name=\"tipo\" id=\"tipo\">");
                $html->appendHtml("<option class=\"optSelect\" value=\"\">Tutte</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"99\">Richiese in corso</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"98\">Richiese Annullate</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"01\">Richiese inoltrate</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"91\">Richiese Inviate alla camera di commercio</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"02\">Richiese Acquisite</option>';");
                $html->appendHtml("<option $Sel class=\"optSelect\" value=\"03\">Richiese Chiuse</option>';");
                $html->appendHtml("</select>");
                $html->appendHtml("</div>");

                //
                //Select sportello
                //
                $html->appendHtml("<div class=\"divSelect\">");
                $html->appendHtml("<label class=\"labelForm\"><b>Sportello</b></label>");
                $html->appendHtml("<select name=\"sportello\" id=\"sportello\">");
                $Anatsp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANATSP ORDER BY TSPCOD", true);
                $html->appendHtml("<option class=\"optSelect\" value=\"\">Tutti</option>';");
                foreach ($Anatsp_tab as $anatsp_rec) {
                    $Sel = "";
                    if ($_POST['sportello'] == $anatsp_rec['TSPCOD'])
                        $Sel = "selected";
                    $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $anatsp_rec['TSPCOD'] . "\">" . $anatsp_rec['TSPDES'] . "</option>';");
                }
                $html->appendHtml("</select>");
                $html->appendHtml("</div>");


                $html->appendHtml("<br><br>");
                $html->appendHtml("<div class=\"divButton\"><input type=\"submit\" value=\"Elenca\" name=\"elenca\"></div>");

                $html->appendHtml("</div>");
                $html->appendHtml("<br>");

                break;
            case "openTable":
                $anno = $_POST['anno'];
                $pratica = str_repeat("0", 6 - strlen($_POST['pratica'])) . $_POST['pratica'];

                //$sql = "SELECT * FROM PRORIC WHERE  1 "; //RICSTA = 99 ";
                if ($_POST['anno'] && $_POST['pratica']) {
                    //$sql .= " AND RICNUM = $anno$pratica ";
                    $whereRicnum = " AND RICNUM = $anno$pratica ";
                } else if ($_POST['anno'] && $_POST['pratica'] == "") {
                    //$sql .= " AND RICNUM LIKE '$anno%' ";
                    $whereAnno = " AND RICNUM LIKE '$anno%' ";
                } else if ($_POST['anno'] == "" && $_POST['pratica']) {
                    //$sql .= " AND RICNUM LIKE '%$pratica' ";
                    $wherePratica = " AND RICNUM LIKE '%$pratica' ";
                }
                if ($_POST['sportello']) {
                    //$sql .= " AND RICTSP = " . $_POST['sportello'];
                    $whereSportello = " AND RICTSP = " . $_POST['sportello'];
                }

                if ($_POST['utente']) {
                    $codFis = frontofficeApp::$cmsHost->getCodFisFromUtente($_POST['utente']);
                    if (!$codFis) {
                        $html->appendHtml("<script type=\"text/javascript\">alert(\"Utente non valido\"); history.go(-1)</script>");
                        break;
                    }
                    //$sql .= " AND RICFIS = '$codFis' ";
                    $whereUtente = " AND RICFIS = '$codFis' ";
                }
//                if ($_POST['tipo']) {
//                    $sql .= " AND RICSTA = '" . $_POST['tipo'] . "'";
//                }

                switch ($_POST['tipo']) {
                    case "02": //Acquisita
                        $whereStato = " AND (PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH='') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN='')";
                        break;
                    case "03": //Chiusa
                        $whereStato = "AND (PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH<>'') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN<>'')";
                        break;
                    default:
                        if ($_POST['tipo']) {
                            $whereStato = " AND RICSTA = '" . $_POST['tipo'] . "'";
                        }
                        break;
                }
                $sql = "SELECT 
                            PRORIC.RICNUM AS RICNUM,
                            PRORIC.RICPRO AS RICPRO,
                            PRORIC.RICTIM AS RICTIM,
                            PRORIC.RICDAT AS RICDAT,
                            PRORIC.RICDRE AS RICDRE,
                            PRORIC.RICSTA AS RICSTA,
                            PRORIC.RICFIS AS RICFIS,
                            PRORIC.RICORE AS RICORE,
                            PROGES.GESDRE AS GESDRE,
                            PROGES.GESDCH AS GESDCH,
                            PROPAS.PROINI AS PROINI,
                            PROPAS.PROFIN AS PROFIN,
                            PROPAS.PRORIN AS PRORIN,
                            PRORIC.RICRPA AS RICRPA
                        FROM 
                            PRORIC PRORIC
                        LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                        LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM
                        WHERE 
                            PRORIC.RICSTA<>'OF' 
                            $whereRicnum
                            $whereAnno
                            $wherePratica
                            $whereSportello
                            $whereUtente
                            $whereStato   
                        GROUP BY 
                            PRORIC.RICNUM
                        ORDER BY 
                            RICNUM DESC";

                $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                if (!$Proric_tab) {
                    $count = 0;
                } else {
                    $count = count($Proric_tab);
                }
                $html->appendHtml("<br>");
                $html->appendHtml("<div style=\"float:right;font-size:1.2em;\">");
                $html->appendHtml("<b>Totale richieste trovate: " . $count . "</b>");
                $html->appendHtml("</div>");
                if ($this->config['view'] == 0) {
                    $html->appendHtml("<table class=\"tabella\" cellspacing=\"5\" cellpadding=\"5\" border=\"2\">");
                    $html->appendHtml("<tr class=\"tith\">");
                    $html->appendHtml("<td align=\"center\">N.<br> Richiesta</td>");
                    $html->appendHtml("<td align=\"center\">Procedimento</td>");
                    $html->appendHtml("<td align=\"center\">Dati Impresa</td>");
                    $html->appendHtml("<td align=\"center\">Inizio<br>del</td>");
                    $html->appendHtml("<td align=\"center\">Stato<br>Richiesta</td>");
                    $html->appendHtml("<td align=\"center\">Stato</td>");
                    $html->appendHtml("<td align=\"center\">Data<br>Acquisizione</td>");
                    $html->appendHtml("<td align=\"center\">Data<br>Chiusura</td>");
                    $html->appendHtml("<td align=\"center\">Codice Fiscale</td>");
                    $html->appendHtml("</tr>");
                }
                foreach ($Proric_tab as $Proric_rec) {
                    $data_inoltro = $descStato = $il = $dataAcq = $dataChi = "";
                    $Prasta_rec = $arrayDesc = $datiImpresa = array();
                    $Anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAPRA ANAPRA
                        LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
                        WHERE PRANUM='" . $Proric_rec['RICPRO'] . "'", false);
                    if ($Proric_rec['RICRPA']) {
                        $datiImpresa = $this->GetdatiImpresa($Proric_rec['RICRPA']);
                    } else {
                        $datiImpresa = $this->GetdatiImpresa($Proric_rec['RICNUM']);
                    }

                    $data_ric = substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
                    if ($Proric_rec['RICDAT'] != "") {
                        $data_inoltro = substr($Proric_rec['RICDAT'], 6, 2) . "/" . substr($Proric_rec['RICDAT'], 4, 2) . "/" . substr($Proric_rec['RICDAT'], 0, 4);
                    }
                    $numero = strval(intval(substr($Proric_rec['RICNUM'], 4, 6))) . "/" . substr($Proric_rec['RICNUM'], 0, 4);
                    $ric_sta = $Proric_rec['RICSTA'];

                    switch ($ric_sta) {
                        case "01" :
                            $t_stato = "Inoltrata";
                            $il = "il";
                            break;
                        case "91" :
                            $t_stato = "Inviate alla camera di commercio";
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

                    if ($this->config['view'] == 0) {
                        $color = $rifRichiesta = "";
                        if ($Proric_rec['RICRPA']) {
                            $color = "color:blue;";
                            $rifRichiesta = "<span style=\"color:black\">Rif. " . strval(intval(substr($Proric_rec['RICRPA'], 4, 6))) . "/" . substr($Proric_rec['RICRPA'], 0, 4) . "</span>";
                        }

                        $html->appendHtml("<tr>");
                        $href = ItaUrlUtil::GetPageUrl(array('p' => $this->config['online_page'], 'event' => 'navClick', 'direzione' => 'primoRosso', 'ricnum' => $Proric_rec['RICNUM']));
                        $desc_node = "<a href=\"$href\">" . $numero . "</a><br>$rifRichiesta";
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;\">" . $desc_node . "</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\"><b>" . $Anapra_rec['PRANUM'] . "</b> - <br>" . $Anapra_rec['PRADES__1'] . "</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">" . $datiImpresa['DENOMIMPRESA'] . "<br>" . $datiImpresa['FISCALE'] . "</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">" . $data_ric . "<br>" . $Proric_rec['RICORE'] . "</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">" . $t_stato . "<br>$il $data_inoltro " . $Proric_rec['RICTIM'] . " </td>");

                        if ($Proric_rec['RICRPA']) {
                            $Proges_rec_int = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICRPA'] . "'", false);
                            if ($Proges_rec_int) {
                                $Propas_tab_integra = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='" . $Proges_rec_int['GESNUM'] . "' AND PRORIN<>''", true);
                                foreach ($Propas_tab_integra as $Propas_rec_integra) {
                                    if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                                        if ($Propas_rec_integra['PROINI']) {
                                            $descStato = "Acquisita";
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
                            $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICNUM'] . "'", false);
                            if ($Proges_rec) {
                                $dataAcq = substr($Proges_rec['GESDRE'], 6, 2) . "/" . substr($Proges_rec['GESDRE'], 4, 2) . "/" . substr($Proges_rec['GESDRE'], 0, 4);
                                if ($Proges_rec['GESDCH']) {
                                    $dataChi = substr($Proges_rec['GESDCH'], 6, 2) . "/" . substr($Proges_rec['GESDCH'], 4, 2) . "/" . substr($Proges_rec['GESDCH'], 0, 4);
                                }
                                $Prasta_rec = $this->praLib->GetPrasta($Proges_rec['GESNUM'], "codice", $this->PRAM_DB);
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
                                    $descStato = "Acquisita";
                                    if ($Proges_rec['GESDCH']) {
                                        $descStato = "Chiusa";
                                    }
                                }
                            }
                        }


                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">$descStato</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">$dataAcq</td>");
                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;$color\">$dataChi</td>");

                        $html->appendHtml("<td align=\"center\" class=\"txttab\" style=\"font-size:0.8em;\">" . $Proric_rec['RICFIS'] . "</td>");
                        $html->appendHtml("</tr>");
                    } elseif ($this->config['view'] == 1) {
                        $html->appendHtml("<div>");
                        $html->appendHtml("<h3> Numero " . $numero . "</h3></br>");
                        $html->appendHtml("<h1><b>" . $Anapra_rec['PRANUM'] . "</b> - " . $Anapra_rec['PRADES__1'] . "</h1></br>");
                        $html->appendHtml("<h3> Richiesta del  : " . $data_ric . "<br>" . $Proric_rec['RICORE'] . "</h3>");
                        $html->appendHtml("<h3> Denominazione Impresa  : $Denominazione</h3>");
                        $html->appendHtml("<h3> Codice Fiscale Impresa  : $Fiscale</h3>");
                        $html->appendHtml("<h3> Stato " . $t_stato . "</h3></br>");
                        $html->appendHtml("<h3> Codice Fiscale : " . $Proric_rec['RICFIS'] . "</h3>");
                        $html->appendHtml("</div>");
                    }
                }

                if ($this->config['view'] == 0) {
                    $html->appendHtml("</table>");
                }
                break;
        }
        $html->appendHtml("</form>");
        return output::$html_out = $html->getHtml();
    }

    public function GetDatiImpresa($codice) {
        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice'
                                  AND DAGTIP<>'' AND (DAGTIP = 'DenominazioneImpresa' OR DAGTIP = 'Codfis_InsProduttivo')", true);
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $Ricdag_rec) {
                if ($Ricdag_rec['DAGTIP'] == "DenominazioneImpresa")
                    $Denominazione = $Ricdag_rec['RICDAT'];
                if ($Ricdag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                    $Fiscale = $Ricdag_rec['RICDAT'];
            }
            return array(
                "DENOMIMPRESA" => $Denominazione,
                "FISCALE" => $Fiscale
            );
        }
    }

}

?>