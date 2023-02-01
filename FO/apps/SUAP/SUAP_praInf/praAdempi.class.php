<?php

class praAdempi extends praSchedaTemplate {

    public function getHtml($dati) {
        $img_str = frontOfficeLib::getFileIcon('.pdf');
        $img_upl = frontOfficeLib::getIcon('notepad');

        $html = new html();

        $html->appendHtml("<div class=\"infoMod\">");
        $html->appendHtml("<h3>Le modalita' per produrre i documenti richiesti dal procedimento on-line, si basano su questa metodologia:</h3></br>");
        $html->appendHtml("</div>");

        $html->appendHtml("<div style=\"font-size: .9em;\">");
        $html->appendHtml("<table class=\"tabella\">");
        $html->appendHtml("<tr>");
        $html->appendHtml("<td class=\"txttab\" style=\"width: 100px; text-align: center;\">");
        $html->appendHtml("<img src=\"" . $img_str . "\" width=\"24px\" height=\"24px\" style=\"border:0px;\"></img>");
        $html->appendHtml("</td>");
        $html->appendHtml("<td class=\"txttab\">Documento compilabile on-line</td>");
        $html->appendHtml("</tr>");

        $html->appendHtml("<tr>");
        $html->appendHtml("<td class=\"txttab\" style=\"width: 100px; text-align: center;\">");
        $html->appendHtml("<img src=\"" . $img_upl . "\" width=\"24px\" height=\"24px\" style=\"border:0px;\"></img>");
        $html->appendHtml("</td>");
        $html->appendHtml("<td class=\"txttab\">Documento acquisito via web</td>");
        $html->appendHtml("</tr>");

        $html->appendHtml("</table>");
        $html->appendHtml("</div>");
        $html->appendHtml("<br>");

        $html->appendHtml("<div  class=\"infoMod\">");
        $html->appendHtml("<h3>Adempimenti e documenti da produrre per la richiesta on-line (dichiarazioni sostitutive di certificazioni - attestazioni - asseverazioni - eventuali dichiarazioni di conformit&agrave; da parte dell'Agenzia delle imprese)</h3></br>");
        $html->appendHtml("</div>");

        $Itepas_tab = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT * FROM ITEPAS WHERE ITECOD = '" . $dati['Codice'] . "' AND ITEPUB<>0 ORDER BY ITESEQ", true);
        if ($Itepas_tab) {
            $html->appendHtml("<table class=\"tabella\">");
            foreach ($Itepas_tab as $Itepas_rec) {
                $Img = $img_upl;
                if ($Itepas_rec['ITESTR']) {
                    $Img = $img_str;
                }

                $html->appendHtml("<tr>");
                $html->appendHtml("<td class=\"txttab\">" . $Itepas_rec['ITEDES'] . "</td>");
                $html->appendHtml("<td class=\"txttab\" style=\"width: 100px; text-align: center;\">");
                $html->appendHtml("<img src=\"" . $Img . "\" width=\"24px\" height=\"24px\" style=\"border:0px;\"></img>");
                $html->appendHtml("</td>");
                $html->appendHtml("</tr>");
            }
            $html->appendHtml("</table>");
        } else {
            return false;
        }

        return $html->getHtml();
    }

}
