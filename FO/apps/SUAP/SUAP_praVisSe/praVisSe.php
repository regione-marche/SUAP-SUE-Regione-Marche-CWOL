<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';

class praVisSe extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $html = new html();

        switch ($this->request['event']) {
            case 'invioMail':
                if (trim($_POST['motivo'])) {
                    $ricnum = $_POST['ricnum'];
                    if ($ricnum == "") {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0046', "Numero pratica non trovato", __CLASS__);
                    }
                    $dati['Proric_rec'] = $Proric_rec = $this->praLib->GetProric($ricnum, 'codice', $this->PRAM_DB);
                    $dati['Ananom_rec'] = $Ananom_rec = $this->praLib->GetAnanom($Proric_rec['RICRES'], 'codice', $this->PRAM_DB);
                    $dati['Anapra_rec'] = $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], 'codice', $this->PRAM_DB);
                    $dati['Anatsp_rec'] = $Anatsp_rec = $this->praLib->GetAnatsp($Proric_rec['RICTSP'], 'codice', $this->PRAM_DB);
                    $dati['Anaspa_rec'] = $Anaspa_rec = $this->praLib->GetAnaspa($Proric_rec['RICSPA'], 'codice', $this->PRAM_DB);
                    $dati['Ricdag_tab_totali'] = $this->praLib->GetRicdag($Proric_rec['RICNUM'], 'codice', $this->PRAM_DB, true);

                    //
                    //leggo i dati per invio mail
                    //
                    $dati['CartellaMail'] = $this->praLib->getCartellaRepositoryPratiche($ricnum . "/mail");
                    $dati['CartellaAllegati'] = $this->praLib->getCartellaAttachmentPratiche($ricnum);
                    $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB, praVisSe::$param);

                    //******************************MAIL RESPONSABILE********************\\

                    $ErrorMailResp = $this->praLib->InvioMailResponsabile($dati, "ANNULLAMENTO-RICHIESTA", $this->PRAM_DB, $arrayDatiMail, "ANNULLAMENTO-RICHIESTA");
                    if ($ErrorMailResp != true) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0047', "Invio richiesta al responsabile per annullamento pratica n. " . $ricnum . " fallito - " . $itaMailer->ErrorInfo, __CLASS__);
                        return false;
                    }

                    //******************************MAIL RICHIEDENTE********************\\

                    $mailRich = $this->praLib->GetMailRichiedente("", $dati['Ricdag_tab_totali']);
                    $ErrorMailRich = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, "ANNULLAMENTO-RICHIESTA");
                    if ($ErrorMailRich != true) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0047', "Invio richiesta al richiedente per annullamento pratica n. " . $ricnum . " fallito - " . $itaMailer->ErrorInfo, __CLASS__);
                        return false;
                    }

                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyAnnullamento'], "bodyAnn.html", "praVisSe");
                    $html->appendHtml($htmlOutput);
                } else {
                    $html->appendHtml("<script type=\"text/javascript\">alert(\"Indicare il motivo dell'annullamento\"); history.go(-1)</script>");
                }
                break;

            case 'sceltaMotivo':
                $html->appendHtml("<label class=\"label\"><b>Indicare il motivo dell'annullamento</b></label><br>");
                $html->appendHtml("<textarea class=\"textArea\" name=\"motivo\" cols=\"50\" rows=\"3\" > </textarea><br><br><br>");
                $html->appendHtml("<div class=\"divButtonScelta\">");
                $html->appendHtml("<input type=\"submit\" value=\"Invia Richiesta\"/>");
                $html->appendHtml("</div>");
                break;

            case 'openBack':
                $domain = ITA_DB_SUFFIX;
                $user = ITA_USER_BACKEND;
                $passowrd = ITA_PASSWORD_BACKEND;
                $ws_url = ITA_WS_BACKEND;
                $engine_url = ITA_ENGINE_BACKEND;
                require_once(ITA_LIB_PATH . '/itaPHPCore/ietoken.php');
                $token = ita_getToken($ws_url . "/Accessi/accWs.php?wsdl", $user, $passowrd, $domain);
                if (strpos($token, "Errore:") === false) {
                    $html->appendHtml('
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        setIeUrl("' . $engine_url . '");
                        setIeToken("' . $token . '");
                        setIeDomain("' . ITA_DB_SUFFIX . '");
                        ieLancia({topbar:"0", homepage:"0", itaiframe:"0", model:"menDirect", menu:"PA_GES", prog:"PA_AGP",praReadonly:\'true\',rowidDettaglio:"' . $_POST['prid'] . '",accessreturn:""});
                        });
                </script>');
                }

            default:
                $this->disegnaFormRicerca($html);
                $sql = $this->creaSQLRicerca();

                $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

                if ($Proric_tab == "") {
                    $count = 0;
                } else {
                    $count = count($Proric_tab);
                }

                $html->appendHtml("<div style=\"text-align: right; ;font-size: 1.2em;\">");
                $html->appendHtml("<b>Totale segnalazioni trovate: " . $count . "</b>");
                $html->appendHtml("</div><br />");
                $html->appendHtml("<div style=\"overflow:auto;\">");

                if ($this->config['view'] == 0) {
                    $tableData = array('header' => array(), 'body' => array());
                    $tableData['header'][] = 'Segnalazione';
                    $tableData['header'][] = array('text' => 'Dati', 'attrs' => array('data-sorter' => 'false'));
                    $tableData['header'][] = 'Stato<br />Inoltro';
                    $tableData['header'][] = 'Stato<br />Pratica';
                    $tableData['header'][] = 'Data<br />Acquisizione';
                    $tableData['header'][] = 'Data<br />Chiusura';
                    $tableData['header'][] = array('text' => 'Integra', 'attrs' => array('data-sorter' => 'false'));
                } else {
                    $html->appendHtml("<br><br>");
                }

                $procIntegrazione = $this->GetProcedimentoIntegrazione();

                foreach ($Proric_tab as $Proric_rec) {
                    $tableRow = array();

                    $data_inoltro = $descStato = $il = $dataAcq = $dataChi = "";
                    $Prasta_rec = $arrayDesc = $datiSegnalazione = $datiSegnalazione1 = $datiSegnalazione2 = array();
                    $Anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAPRA ANAPRA
                        LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
                        WHERE PRANUM='" . $Proric_rec['RICPRO'] . "'", false);

                    if ($Proric_rec['RICRPA']) {
                        $datiSegnalazione1 = $this->GetDatiSegnalazione($Proric_rec['RICRPA']);
                        $datiSegnalazione2 = $this->GetDatiSegnalazione($Proric_rec['RICNUM']);
                        $datiSegnalazione['INDIRIZZO'] = $datiSegnalazione1['INDIRIZZO'];
                        $datiSegnalazione['DESCRIZIONE'] = $datiSegnalazione2['INTEGRAZIONE'];
                    } else {
                        $datiSegnalazione = $this->GetDatiSegnalazione($Proric_rec['RICNUM']);
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

                        $href = ItaUrlUtil::GetPageUrl(array('p' => $this->config['online_page'], 'event' => 'navClick', 'direzione' => 'primoRosso', 'ricnum' => $Proric_rec['RICNUM']));
                        $desc_node = "<a href=\"$href\">" . $numero . "</a><br>$rifRichiesta";

                        $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICNUM']}\">$desc_node</div>";
                        $tableRow[] = "<div style=\"$color\">" . $datiSegnalazione['INDIRIZZO'] . "<br>" . $datiSegnalazione['DESCRIZIONE'] . '</div>';
                        $tableRow[] = "<div style=\"$color\" data-sortValue=\"{$Proric_rec['RICSTA']}\">" . $t_stato . "<br>$il $data_inoltro " . $Proric_rec['RICTIM'] . '</div>';
                    }

                    if ($Proric_rec['RICRPA']) {
                        $codiceRichiesta = $Proric_rec['RICRPA'];
                    } else {
                        $codiceRichiesta = $Proric_rec['RICNUM'];
                    }

                    $descStatoIntegrazione = '';

                    if ($Proric_rec['RICRPA']) {
                        $swPasso = $swPassoAperto = $swPassoChiuso = false;
                        $Proges_rec_int = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICRPA'] . "'", false);
                        if ($Proges_rec_int) {
                            $Propas_tab_integra = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='" . $Proges_rec_int['GESNUM'] . "' AND PRORIN<>''", true);
                            foreach ($Propas_tab_integra as $Propas_rec_integra) {
                                if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                                    $swPasso = true;
                                    if ($Propas_rec_integra['PROINI']) {
                                        $descStatoIntegrazione .= "Acquisita ";
                                        $descStatoIntegrazione .= substr($Propas_rec_integra['PROINI'], 6, 2) . "/" . substr($Propas_rec_integra['PROINI'], 4, 2) . "/" . substr($Propas_rec_integra['PROINI'], 0, 4);
                                        $swPassoAperto = true;
                                    }
                                    if ($Propas_rec_integra['PROFIN']) {
                                        $descStatoIntegrazione .= " <br>Chiusa ";
                                        $descStatoIntegrazione .= substr($Propas_rec_integra['PROFIN'], 6, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 4, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 0, 4);
                                        $swPassoChiuso = true;
                                    }
                                    break;
                                }
                            }
                        }

                        if (!$swPasso) {
                            $descStatoIntegrazione = "Integrazione: Non Acquisita";
                        } else {
                            $descStatoIntegrazione = "Integrazione: " . $descStatoIntegrazione;
                        }

                        $descStatoIntegrazione .= "<br>Richiesta principale: ";
                    }

                    $descStato2 = '';
                    $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA='" . $codiceRichiesta . "'", false);

                    if ($Proges_rec) {
                        $dataAcq = substr($Proges_rec['GESDRE'], 6, 2) . "/" . substr($Proges_rec['GESDRE'], 4, 2) . "/" . substr($Proges_rec['GESDRE'], 0, 4);

                        if ($Proges_rec['GESDCH']) {
                            $dataChi = substr($Proges_rec['GESDCH'], 6, 2) . "/" . substr($Proges_rec['GESDCH'], 4, 2) . "/" . substr($Proges_rec['GESDCH'], 0, 4);
                        }

                        $Prasta_rec = $this->praLib->GetPrasta($Proges_rec['GESNUM'], "codice", $this->PRAM_DB);

                        if ($Prasta_rec['STAPST'] != 0) {
                            //if ($Proges_rec['GESCLOSE']) {
                            $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
                            $arrayDesc = explode(" - ", $Prasta_rec['STADEX']);
                            $lastDesc = end($arrayDesc);
                            $descStato = $Prasta_rec['STADES'] . " <br> $lastDesc";
//                                } else {
//                                    $descStato = $Prasta_rec['STADES'];
//                                }
                        } else {
                            $descStato = "Acquisita $dataAcq";
                            if ($Proges_rec['GESDCH']) {
                                $descStato = "Chiusa $dataChi";
                            }
                        }

//                        $href = ItaUrlUtil::GetPageUrl(array('event' => 'openBack', 'prid' => $Proges_rec['ROWID'], 'tema' => 'cupertino'));
//                        $descStato2 = "<a href=\"javascript:\" onclick=\"location.replace('$href');\">$descStatoIntegrazione$descStato</a>";
                        $descStato2 = $descStatoIntegrazione . $descStato;
                    }

                    if ($this->config['view'] == 0) {
                        $rigaIntegrazione = '';
                        if (($Proric_rec['RICSTA'] == '01') && $Proric_rec['RICRPA'] == "" && $procIntegrazione) {
                            $href = ItaUrlUtil::GetPageUrl(array('p' => $this->config['online_page'], 'event' => 'openBlock', 'procedi' => $procIntegrazione, 'padre' => $Proric_rec['RICNUM'], 'tipo' => 'integrazione'));
                            $rigaIntegrazione = $html->getImage(frontOfficeLib::getIcon('integra'), '24px', 'Avvia procedura di integrazione', $href);
                        }

                        $tableRow[] = "<div style=\"$color\">" . $descStato2 . '</div>';
                        $tableRow[] = "<div class=\"align-center\" style=\"$color\">" . $dataAcq . '</div>';
                        $tableRow[] = "<div class=\"align-center\" style=\"$color\">" . $dataChi . '</div>';
                        $tableRow[] = "<div class=\"align-center\">" . $rigaIntegrazione . '</div>';

                        $tableData['body'][] = $tableRow;
                    } elseif ($this->config['view'] == 1) {
                        $html->appendHtml("<div>");
                        $html->appendHtml("<h3> N. Segnalazione: " . $numero . "</h3>");
                        $html->appendHtml("<h3> N. Procedimento: " . $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . "</h3>");
                        $html->appendHtml("<h3> Richiesta del: " . $data_ric . " ore " . $Proric_rec['RICORE'] . "</h3>");
                        $html->appendHtml("<h3> Stato: " . $t_stato . "</h3>");
                        if ($Proric_rec['RICTIM'])
                            $ore = "ore";
                        $html->appendHtml("<h3> Inoltro del: " . $data_inoltro . " $ore " . $Proric_rec['RICTIM'] . "</h3>");
                        $html->appendHtml("<h3> Stato Segnalazione: $descStato</h3>");
                        $html->appendHtml("<h3> Data Acquisizione: $dataAcq</h3>");
                        $html->appendHtml("<h3> Data Chiusura: $dataChi</h3>");
                        $html->appendHtml("<span><b>---------------------------------------------------------------------------------------------</b></span>");
                        $html->appendHtml("</div>");
                    }
                }

                if ($this->config['view'] == 0) {
                    $html->addTable($tableData, array(
                        'sortable' => true,
                        'paginated' => true
                    ));
                }

                $html->appendHtml("</div>");
                break;
        }

        $html->appendHtml("</form>");
        return output::$html_out = $html->getHtml();
    }

    public function GetDatiSegnalazione($codice) {
        $sql = "
            SELECT
                *
            FROM
                RICDAG
            WHERE
                DAGNUM='$codice' AND DAGTIP<>'' AND
                (
                    DAGKEY = 'IND_SEGNALAZIONE' OR
                    DAGKEY = 'DESC_SEGNALAZIONE' OR
                    DAGKEY = 'DESC_INTEGRAZIONE' 
                )
                ";
        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $Ricdag_rec) {
                if ($Ricdag_rec['DAGKEY'] == "IND_SEGNALAZIONE")
                    $Indirizzo = $Ricdag_rec['RICDAT'];
                if ($Ricdag_rec['DAGKEY'] == "DESC_SEGNALAZIONE")
                    $Descrizione = $Ricdag_rec['RICDAT'];
                if ($Ricdag_rec['DAGKEY'] == "DESC_INTEGRAZIONE")
                    $Integrazione = $Ricdag_rec['RICDAT'];
            }
            return array(
                "INDIRIZZO" => $Indirizzo,
                "DESCRIZIONE" => $Descrizione,
                "INTEGRAZIONE" => $Integrazione
            );
        }
    }

    public function GetProcedimentoIntegrazione() {
        $Filent_rec = $this->praLib->GetFilent(1, $this->PRAM_DB);
        if ($Filent_rec['FILDE5']) {
            return $Filent_rec['FILDE5'];
        } else {
            if ($Filent_rec['FILDE4'] == "S" && $Filent_rec['FILDE3']) {
                $pramMaster = ItaDB::DBOpen('PRAM', $Filent_rec['FILDE3']);
                $Filent_rec_master = $this->praLib->GetFilent(1, $pramMaster);
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

    private function disegnaFormRicerca($html) {
        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'GET', array(
            'class' => 'italsoft-form--fixed'
        ));

        $html->addHidden('event', 'elenca');

        $html->addInput('select', 'Stato', array(
            'name' => 'tipo'
            ), array(
            '' => 'Tutte',
            '99' => 'Segnalazioni in corso',
            '01' => 'Segnalazioni inoltrate',
            '02' => 'Segnalazioni acquisite',
            '03' => 'Segnalazioni chiuse'
        ));

        $html->addBr();

        $sportelloOptions = array('' => 'Tutti');

        $Anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPAATT = 1", true);
        foreach ($Anaspa_tab as $Anaspa_rec) {
            $sportelloOptions[$Anaspa_rec['SPACOD']] = $Anaspa_rec['SPADES'];
        }

        $html->addInput('select', 'Sportello aggregato', array(
            'name' => 'Aggregato'
            ), $sportelloOptions);

        $html->addBr();

        $html->addInput('checkbox', 'Vedi annullate', array(
            'name' => 'Annullate'
        ));

        $html->addBr();

        $html->addSubmit('Elenca');

        $html->closeTag('form');
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

    private function creaSQLRicerca() {
        $whereStato = '';

        switch ($this->request['tipo']) {
            case '02': // Acquisita
                $whereStato = " AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH='') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN=''))";
                break;

            case '03': // Chiusa
                $whereStato = "AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH<>'') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN<>''))";
                break;

            default:
                if ($this->request['tipo']) {
                    $whereStato = "AND RICSTA = '" . $this->request['tipo'] . "'";
                }
                break;
        }

        if ($this->request['Aggregato']) {
            $whereStato .= " AND RICSPA='" . $this->request['Aggregato'] . "'";
        }

        $whereAnnullate = " AND RICSTA <> '98'";
        if ($this->request['Annullate'] == "1") {
            $whereAnnullate = '';
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
                    PRORIC.RICRPA AS RICRPA,
                    PRORIC.RICAGE AS RICAGE
                FROM 
                    PRORIC PRORIC
                LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM
                WHERE 
                    PRORIC.RICSTA<>'OF' $whereStato $whereAnnullate
                GROUP BY 
                    PRORIC.RICNUM
                ORDER BY 
                    PRORIC.RICNUM DESC";

        return $sql;
    }

}
