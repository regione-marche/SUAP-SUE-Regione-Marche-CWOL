<?php

class praOneri extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $file_oneri = $Itepas_tab = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'ONR'", false);
        if ($file_oneri) {
            $file = $dati['repositoryUrl'] . "allegati/" . $dati['Codice'] . "/" . $file_oneri['ANPFIL'];
            if (file_exists($file)) {
                $contenuto = file_get_contents($file);
                $html->appendHtml($contenuto);
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $html->getHtml();
    }

}
