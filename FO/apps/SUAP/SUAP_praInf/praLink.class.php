<?php

class praLink extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $sql = "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'LINK' ORDER BY ANPSEQ ASC";
        $Anpdoc_tab = ItaDB::DBSQLSelect($dati['pramSource'], $sql, true);

        if (!$Anpdoc_tab) {
            return false;
        }

        $html->appendHtml('<ul style="margin: 0; padding: 0px 10px; list-style: inside disc; line-height: 1.2em; font-size: .9em;">');

        foreach ($Anpdoc_tab as $Anpdoc_rec) {
            $filepath = $dati['repositoryUrl'] . "allegati/{$dati['Codice']}/" . $Anpdoc_rec['ANPFIL'];

            if (!file_exists($filepath)) {
                continue;
            }

            $contenuto = file_get_contents($filepath);
            $href = strip_tags($contenuto);

            $html->appendHtml('<li><a href="' . $href . '" target="_blank">' . $Anpdoc_rec['ANPNOT'] . '</a></li>');
        }

        $html->appendHtml('</ul>');

        return $html->getHtml();
    }

}
