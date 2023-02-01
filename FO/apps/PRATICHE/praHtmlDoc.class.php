<?php

class praHtmlDoc {

    protected $html;
    protected $praLib;
    protected $errCode;
    protected $errMessage;

    public function __construct() {
        $this->html = new html();
        $this->praLib = new praLib();
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function DisegnaPagina($dati, $extraParams = array()) {
        $whereRichiesta = "";
        if ($dati['ricnum']) {
            $whereRichiesta = " AND PRORIC.RICNUM = '" . addslashes($dati['ricnum']) . "'";
        }
        $sql = "SELECT 
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
                    PRORIC.RICSTT AS RICSTT,
                    PRORIC.RICATT AS RICATT,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESDCH AS GESDCH,
                    PROGES.GESNUM AS GESNUM,
                    PRORIC.RICRPA AS RICRPA,
                    PRORIC.RICAGE AS RICAGE
                FROM 
                    PRORIC PRORIC
                INNER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                
                WHERE 
                    PRORIC.RICFIS = '" . $dati['fiscale'] . "' AND
                    PRORIC.RICSTA<>'OF' 
                    $whereRichiesta
                GROUP BY 
                    PRORIC.RICNUM
                ORDER BY 
                    PRORIC.RICNUM DESC";
        $Proric_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], $sql, true);
        if ($Proric_tab == "") {
            $count = 0;
        } else {
            $count = count($Proric_tab);
        }

        $tableData = array(
            'header' => array(
                array('text' => 'Visualizza<br />richiesta', 'attrs' => array('width' => '5%')),
                array('text' => 'Data', 'attrs' => array('width' => '5%')),
                array('text' => 'Procedimento', 'attrs' => array('width' => '25%', 'data-sorter' => 'false')),
                array('text' => 'Dati impresa', 'attrs' => array('width' => '15%', 'data-sorter' => 'false')),
                array('text' => 'Adempimento', 'attrs' => array('width' => '15%', 'data-sorter' => 'false')),
                array('text' => 'Annotazioni', 'attrs' => array('width' => '10%', 'data-sorter' => 'false')),
                array('text' => 'Visualizza<br />allegati', 'attrs' => array('width' => '30%', 'data-sorter' => 'false')),
                array('text' => 'Integra', 'attrs' => array('width' => '5%', 'data-sorter' => 'false'))
            ),
            'body' => array(),
            'style' => array(
                'body' => array('text-align: center;', '', '', '', '', '', 'word-break: break-all;', 'text-align: center;', 'text-align: center;')
            )
        );

        $procIntegrazione = $this->praLib->GetProcedimentoIntegrazione($extraParams['PRAM_DB']);
        $Iteevt_rec_int = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM ITEEVT WHERE ITEPRA = '$procIntegrazione'", false);

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';
        $praHtmlVis = new praHtmlVis;

        foreach ($Proric_tab as $Proric_rec) {
            $Propas_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM PROPAS WHERE PRONUM='" . $Proric_rec['GESNUM'] . "' AND PROPUBALL = 1 ORDER BY PROSEQ", true);

            foreach ($Propas_tab as $Propas_rec) {
                $tableRow = array();

                $Pasdoc_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM PASDOC WHERE PASKEY='" . $Propas_rec['PROPAK'] . "' AND PASPUB = 1", true);
                /*
                 * Attivata voce in elenco anche senza allegati
                 */

                $data_inoltro = $descStato = $il = $dataAcq = $dataChi = "";
                $Anapra_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM ANAPRA ANAPRA
                        WHERE PRANUM='" . $Proric_rec['RICPRO'] . "'", false);

                if ($Proric_rec['RICRPA']) {
                    $datiImpresa = $praHtmlVis->GetdatiImpresa($Proric_rec['RICRPA'], $extraParams['PRAM_DB']);
                } else {
                    $datiImpresa = $praHtmlVis->GetdatiImpresa($Proric_rec['RICNUM'], $extraParams['PRAM_DB']);
                }

                if ($Proric_rec['RICDAT'] != "") {
                    $data_inoltro = substr($Proric_rec['RICDAT'], 6, 2) . "/" . substr($Proric_rec['RICDAT'], 4, 2) . "/" . substr($Proric_rec['RICDAT'], 0, 4);
                }
                $numero = strval(intval(substr($Proric_rec['RICNUM'], 4, 6))) . "/" . substr($Proric_rec['RICNUM'], 0, 4);

                $color = $rifRichiesta = "";
                if ($Proric_rec['RICRPA']) {
                    $color = "color: blue;";
                    $rifRichiesta = "<span style=\"color: black;\">Rif. " . strval(intval(substr($Proric_rec['RICRPA'], 4, 6))) . "/" . substr($Proric_rec['RICRPA'], 0, 4) . "</span>";
                }

                $imgNew = "";

                $Ricsta_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM RICSTA WHERE RICNUM='" . $Proric_rec['RICNUM'] . "' AND PROPAK='" . $Propas_rec['PROPAK'] . "'", false);
                if (!$Ricsta_rec) {
                    $imgNew = '<div style="margin-bottom: 5px;">' . $this->html->getImage(frontOfficeLib::getIcon('new'), '32px') . '</div>';
                }

                $collegamentoRichiesta = ItaUrlUtil::GetPageUrl(array(
                            'p' => $extraParams['online_page'],
                            'event' => 'navClick',
                            'direzione' => 'primoRosso',
                            'ricnum' => $Proric_rec['RICNUM']
                ));

                $collegamentoAllegati = ItaUrlUtil::GetPageUrl(array(
                            'parent' => $dati['parent'],
                            'event' => 'dettaglio',
                            'ricnum' => $Proric_rec['RICNUM'],
                            "propak" => $Propas_rec['PROPAK']
                ));

                $desc_node = "<a href=\"$collegamentoRichiesta\">$imgNew$numero</a><br>$rifRichiesta";
                $tableRow[] = "<b>$desc_node</b>";

                $tableRow[] = $Propas_rec['PROINI'] ? frontOfficeLib::convertiData($Propas_rec['PROINI']) : '';

                $Anaset_rec = $this->praLib->GetAnaset($Proric_rec['RICSTT'], "codice", $extraParams['PRAM_DB']);
                $Anaatt_rec = $this->praLib->GetAnaatt($Proric_rec['RICATT'], "codice", $extraParams['PRAM_DB']);
                $descProc = $Anaset_rec['SETDES'] ? $Anaset_rec['SETDES'] . '<br />' : '';
                $descProc .= $Anaatt_rec['ATTDES'] ? $Anaatt_rec['ATTDES'] . '<br />' : '';
                $descProc .= "<b>" . $Anapra_rec['PRANUM'] . "</b> - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];

                $tableRow[] = "<div style=\"$color\"><a href=\"$collegamentoRichiesta\">$descProc</a></div>";

                $codiceDatiImpresa = $Proric_rec['RICRPA'] ?: $Proric_rec['RICNUM'];
                $datiImpresa = $praHtmlVis->GetDatiImpresa($codiceDatiImpresa, $extraParams['PRAM_DB']);
                $tableRow[] = $datiImpresa['DENOMIMPRESA'] . '<br>' . $datiImpresa['FISCALE'];

                $tableRow[] = "<div style=\"$color max-height: 200px; overflow: auto;\">{$Propas_rec['PRODPA']}</div>";
                $tableRow[] = "<div style=\"$color\">{$Propas_rec['PROANN']}</div>";

                $textAllegati = '<a href="' . $collegamentoAllegati . '">';

                foreach ($Pasdoc_tab as $Pasdoc_rec) {
                    $Est = strtolower(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
                    $path = ITA_PRATICHE . substr($Pasdoc_rec['PASKEY'], 0, 4) . "/PASSO/" . $Pasdoc_rec['PASKEY'];
                    $name = $Pasdoc_rec['PASNAME'];
                    if (strtolower($Est) == 'xhtml' || strtolower($Est) == 'docx') {
                        if (file_exists($path . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                            $name = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                        } else if (file_exists($path . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                            $name = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf";
                        }
                    }

                    $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin: 3px 0;">';
                    $textAllegati .= '  <div style="position: absolute; left: 0;">';
                    $textAllegati .= $this->html->getImage(frontOfficeLib::getFileIcon($name), '24px');
                    $textAllegati .= '  </div>';
                    $textAllegati .= $name;
                    $textAllegati .= '</div>';
                }

                $textAllegati .= '</a>';

                $tableRow[] = $textAllegati;

                $textIntegrazione = '&mdash;';

//                if ($Proric_rec['RICRPA'] == "" && $procIntegrazione) {
                if ($this->praLib->checkBloccoIntegrazione($Proric_rec, $extraParams['PRAM_DB'])) {
                    $href = ItaUrlUtil::GetPageUrl(array(
                                'p' => $extraParams['online_page'],
                                'event' => 'openBlock',
                                'procedi' => $procIntegrazione,
                                'padre' => $Proric_rec['RICNUM'],
                                'tipo' => 'integrazione',
                                'subproc' => $Iteevt_rec_int['IEVCOD'],
                                'subprocid' => $Iteevt_rec_int['ROWID']
                    ));

                    $textIntegrazione = $this->html->getImage(frontOfficeLib::getIcon('integra'), '24px', 'Avvia procedura di integrazione', $href);
                }

                $tableRow[] = $textIntegrazione;

                $tableData['body'][] = $tableRow;
            }
        }

        output::addTable($tableData, array('sortable' => true, 'paginated' => true));

        output::appendHtml("<div style=\"text-align:center;\">");
        output::appendHtml("<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"tornaElenco\" class=\"italsoft-button\" type=\"button\" onClick=\"javascript:history.back()\">
                                    <i class=\"icon ion-arrow-return-left italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Torna Elenco</b></div>
                                </button>
                          </div>");
        output::appendHtml("</div>");
        return true;
    }

    public function Dettaglio($dati, $extraParams = array()) {
        $Propas_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM PROPAS WHERE PROPAK='" . addslashes($dati['propak']) . "'", false);
        $Proric_rec = $this->praLib->GetProric(addslashes($dati['ricnum']), "codice", $extraParams['PRAM_DB']);
        $Ricsta_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM RICSTA WHERE RICNUM='" . $Proric_rec['RICNUM'] . "' AND PROPAK='" . $Propas_rec['PROPAK'] . "'", false);
        $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], "codice", $extraParams['PRAM_DB']);

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';
        $praHtmlVis = new praHtmlVis;

        if ($Proric_rec['RICRPA']) {
            $datiImpresa = $praHtmlVis->GetDatiImpresa($Proric_rec['RICRPA'], $extraParams['PRAM_DB']);
        } else {
            $datiImpresa = $praHtmlVis->GetDatiImpresa($Proric_rec['RICNUM'], $extraParams['PRAM_DB']);
        }
        //
        if (!$Ricsta_rec) {
            $Ricsta_rec = array();
            $Ricsta_rec['RICNUM'] = $Proric_rec['RICNUM'];
            $Ricsta_rec['PROPAK'] = $Propas_rec['PROPAK'];
            $Ricsta_rec['READED'] = 1;
            $Ricsta_rec['READDATA'] = date("Ymd");
            $Ricsta_rec['READORA'] = date("H:i:s");
            try {
                $nRows = ItaDB::DBInsert($extraParams['PRAM_DB'], "RICSTA", 'ROWID', $Ricsta_rec);
            } catch (Exception $e) {
                //output::$html_out = $this->praErr->parseError(__FILE__, 'E0091', $e->getMessage() . " Pratica N. " . $Ricsta_rec['GESNUM'], __CLASS__);
                $this->setErrCode(-1);
                $this->setErrMessage($e->getMessage() . " Pratica N. " . $Ricsta_rec['GESNUM']);
                return false;
            }
        }
        //
        output::appendHtml("<div id=\"InfoUO\" class=\"ui-widget ui-widget-content ui-corner-all\" style=\"width: 49%; display: inline-block; margin-right: 1%;\">");

        //output::appendHtml('<table cellpadding="10" cellspacing="10">');
        output::appendHtml('<table id="tabella_allegati14" class="" cellpadding="0" cellspacing="0" style="width: 100%;">');
        output::appendHtml("<thead><tr>");
        output::appendHtml("<th colspan=\"2\" style=\"font-size: 1.3em;\">Dati Richiesta</th>");
        output::appendHtml("</tr></thead>");
        output::appendHtml("<tbody>");
        output::appendHtml("<tr>");
        output::appendHtml("<td style=\"padding:5px;text-decoration:underline;font-weight:bold;font-size:1.1em;\">Numero: </td>");
        output::appendHtml("<td style=\"padding:5px;font-size:1.1em;\">" . substr($Proric_rec['RICNUM'], 4, 6) . "/" . substr($Proric_rec['RICNUM'], 0, 4) . "</td>");
        output::appendHtml("</tr>");
        output::appendHtml("<tr>");
        output::appendHtml("<td style=\"padding:5px;text-decoration:underline;font-weight:bold;font-size:1.1em;\">Procedimento: </td>");
        output::appendHtml("<td style=\"padding:5px;font-size:1.1em;\">" . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "</td>");
        output::appendHtml("</tr>");
        output::appendHtml("<tr>");
        output::appendHtml("<td style=\"padding:5px;vertical-align:top;text-decoration:underline;font-weight:bold;font-size:1.1em;\">Data/Ora Attivazione: </td>");
        output::appendHtml("<td style=\"padding:5px;font-size:1.1em;\">" . substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4) . "<br>" . $Proric_rec['RICORE'] . "</td>");
        output::appendHtml("</tr>");
        output::appendHtml("<tr>");
        output::appendHtml("<td style=\"padding:5px;vertical-align:top;text-decoration:underline;font-weight:bold;font-size:1.1em;\">Data/Ora Inoltro: </td>");
        output::appendHtml("<td style=\"padding:5px;font-size:1.1em;\">" . substr($Proric_rec['RICDAT'], 6, 2) . "/" . substr($Proric_rec['RICDAT'], 4, 2) . "/" . substr($Proric_rec['RICDAT'], 0, 4) . "<br>" . $Proric_rec['RICTIM'] . "</td>");
        output::appendHtml("</tr>");
        output::appendHtml("<tr>");
        output::appendHtml("<td style=\"padding:5px;vertical-align:top;text-decoration:underline;font-weight:bold;font-size:1.1em;\">Dati Impresa: </td>");
        output::appendHtml("<td style=\"padding:5px;font-size:1.1em;\">" . $datiImpresa['DENOMIMPRESA'] . "<br>" . $datiImpresa['FISCALE'] . "</td>");
//        output::appendHtml("</tr>");
        output::appendHtml("</tbody>");
        output::appendHtml("</table>");

        output::appendHtml("</div>");
        //
        output::appendHtml("<div id=\"InfoUO\" class=\"ui-widget ui-widget-content ui-corner-all\" style=\"vertical-align: top; width: 49%; display: inline-block;\">");

        output::appendHtml("<div id=\"headerUo\" class=\"ui-corner-all ui-widget-header\" style=\"text-align:center;font-size:1.1em;padding:5px;\">Passo</div>");
        output::appendHtml("<div id=\"infoUO\" style=\"padding:5px;\">{$Propas_rec['PRODPA']}</div>");

        output::appendHtml("<div id=\"headerUo\" class=\"ui-corner-all ui-widget-header\" style=\"text-align:center;font-size:1.1em;padding:5px;\">Annotazioni</div>");
        output::appendHtml("<div id=\"infoUO\" style=\"padding:5px;\">{$Propas_rec['PROANN']}</div>");
        //
        $Pasdoc_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM PASDOC WHERE PASKEY='" . $Propas_rec['PROPAK'] . "' AND PASPUB = 1", true);
        if ($Pasdoc_tab) {
            output::appendHtml("<div id=\"headerUo\" class=\"ui-corner-all ui-widget-header\" style=\"text-align:center;font-size:1.1em;padding:5px;\">Allegati</div>");

            output::appendHtml('<table id="tabella_allegati12" class="tabella_allegati tablesorter" border="2" cellpadding="0" cellspacing="0" width="100%">');
            output::appendHtml('<tbody>');
            output::appendHtml("<td style=\"padding:5px;\">");
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $Est = strtolower(pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
                $path = ITA_PRATICHE . substr($Pasdoc_rec['PASKEY'], 0, 4) . "/PASSO/" . $Pasdoc_rec['PASKEY'];
                $name = $Pasdoc_rec['PASNAME'];
                if (strtolower($Est) == "xhtml" || strtolower($Est) == "docx") {
                    if (file_exists($path . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                        $name = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                    } else if (file_exists($path . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                        $name = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf";
                    }
                }
                $iconUrl = frontOfficeLib::getFileIcon($name);
                $hRef = ItaUrlUtil::GetPageUrl(array('event' => 'gestioneAllegato', 'operation' => 'view', 'fileId' => $Pasdoc_rec['ROWID']));
                output::appendHtml("<a href=\"" . $hRef . "\" target=\"_blank\"><img src=\"$iconUrl\" width=\"30\" height=\"30\" style=\"padding:5px;border:0px;vertical-align:middle;margin-right:5px;\" />" . $name . "</a><br>");
            }
            output::appendHtml('</td>');
            output::appendHtml('</tbody>');
            output::appendHtml("</table>");
        }

        output::appendHtml("</div>");

        output::addBr(2);

        /*
         * Bottone Torna
         */
        output::appendHtml("<div style=\"text-align:center;\">");
        output::appendHtml("<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"torna\" class=\"italsoft-button\" type=\"button\" onClick=\"javascript:history.back()\">
                                    <i class=\"icon ion-arrow-return-left italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Torna</b></div>
                                </button>
                          </div>");

        /*
         * Bottone Integra
         */
        $procIntegrazione = $this->praLib->GetProcedimentoIntegrazione($extraParams['PRAM_DB']);
        $Iteevt_rec_int = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM ITEEVT WHERE ITEPRA = '$procIntegrazione'", false);

        if ($this->praLib->checkBloccoIntegrazione($Proric_rec, $extraParams['PRAM_DB'])) {
            $href_integra = ItaUrlUtil::GetPageUrl(array('p' => $extraParams['online_page'], 'event' => 'openBlock', 'procedi' => $procIntegrazione, 'padre' => $Proric_rec['RICNUM'], 'tipo' => 'integrazione', 'subproc' => $Iteevt_rec_int['IEVCOD'], 'subprocid' => $Iteevt_rec_int['ROWID']));
            output::appendHtml("<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"torna\" class=\"italsoft-button\" type=\"button\" onClick=\"location.replace('$href_integra')\">
                                    <i class=\"icon ion-edit italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Integra</b></div>
                                 </button>
                                </div>");
        }

        /*
         * Bottone Visualizza Richiesta
         */
        $href_richiesta = ItaUrlUtil::GetPageUrl(array('p' => $extraParams['online_page'], 'event' => 'navClick', 'direzione' => 'primoRosso', 'ricnum' => $Proric_rec['RICNUM']));
        output::appendHtml("<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"torna\" class=\"italsoft-button\" type=\"button\" onClick=\"location.replace('$href_richiesta')\">
                                    <i class=\"icon ion-arrow-right-c italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Visualizza Pratica</b></div>
                                </button>
                          </div>");
        output::appendHtml("</div>");
        return true;
    }

    public function GestioneAllegato($dati, $extraParams = array()) {
        switch ($dati['operation']) {
            case 'view':
                if ($dati['fileId'] == 0) {
                    exit;
                }
                $sql = "SELECT * FROM PASDOC WHERE ROWID=" . addslashes($dati['fileId']);
                $Pasdoc_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], $sql, false);
                if (!$Pasdoc_rec) {
                    exit;
                }
                $id = $Pasdoc_rec['PASKEY'];
                $file = $Pasdoc_rec['PASFIL'];
                $fileOrig = $Pasdoc_rec['PASNAME'];

                if ($Pasdoc_rec['PASCLA'] == "TESTOBASE") {
                    $pramPath = ITA_PRATICHE . substr($id, 0, 4) . "/PASSO/" . $id;
                    if (file_exists($pramPath . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf")) {
                        $fileOrig = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf";
                        $file = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf";
                    }
                    if (file_exists($pramPath . "/" . pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                        $fileOrig = pathinfo($Pasdoc_rec['PASNAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                        $file = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m";
                    }
                }

                if (!$tempFolder = $this->praLib->getCartellaTemporaryPratiche("TMP_PASSO_PRAT-" . $id)) {
                    //output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . substr($Pasdoc_rec['PASKEY'], 0, 10), __CLASS__);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . substr($Pasdoc_rec['PASKEY'], 0, 10));
                    return false;
                }
                if (!file_exists($tempFolder . "/" . $file)) {
                    $repConnector = new praRep(ITA_PRATICHE);
                    if (!$repConnector->getFile(substr($id, 0, 4) . "/PASSO/" . $id . "/" . $file, $tempFolder . "/" . $file, false)) {
                        //output::$html_out = $this->praErr->parseError(__FILE__, 'E0010', $repConnector->getErrorMessage(), __CLASS__);
                        $this->setErrCode(-1);
                        $this->setErrMessage($repConnector->getErrorMessage());
                        return false;
                    }
                }
                $file = $tempFolder . "/" . $file;

                $frontOfficeLib = new frontOfficeLib;
                $frontOfficeLib->scaricaFile($file, $fileOrig);

                $this->removeTempDir($tempFolder);
                exit;
        }
    }

}
