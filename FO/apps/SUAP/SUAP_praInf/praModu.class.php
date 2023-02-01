<?php

class praModu extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $Anpdoc_tab = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT * FROM ANPDOC WHERE ANPKEY='" . $dati['Anapra_rec']['PRANUM'] . "'", true);
        if ($Anpdoc_tab) {
            foreach ($Anpdoc_tab as $Anpdoc_rec) {
                if ($Anpdoc_rec['ANPCLA'] == "DOC") {
                    $T_docu = $Anpdoc_rec['ANPFIL'];
                    break;
                }
            }
        }

        if ($T_docu) {
            $html->appendHtml("<div>");
            $html->appendHtml("<table class=\"tabella\" cellspacing=\"5\" cellpadding=\"5\" border=\"2\">");
//            $html->appendHtml("<tr class=\"tith\">");
//            $html->appendHtml("<td align=\"center\">Modello Informativo del Procedimento</td>");
//            $html->appendHtml("<td align=\"center\">Allegato</td>");
//            $html->appendHtml("</tr>");

            $html->appendHtml("<tr>");
            $html->appendHtml("<td class=\"txttab\">Modello Informativo</td>");
            $html->appendHtml("<td style=\"width: 100px; text-align: center;\" title=\"Vedi Testo\" class=\"txttab\">");

            $allegato = frontOfficeApp::encrypt($T_docu);
            $href = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'vediAllegato',
                    'procedi' => $dati['Anapra_rec']['PRANUM'],
                    'allegato' => $allegato,
                    "type" => "doc",
                    "sportello" => $dati['Iteevt_rec']['IEVTSP']
            ));

            $html->addImage(frontOfficeLib::getFileIcon($T_docu), '24px', 'Vedi testo', $href, '_blank');

            $html->appendHtml("</td>");
            $html->appendHtml("</tr>");
            //
            $html->appendHtml("</table>");
            $html->appendHtml("</div>");
        } else {
            return false;
        }

        return $html->getHtml();
    }

}
