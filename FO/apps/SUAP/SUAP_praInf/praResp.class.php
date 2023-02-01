<?php

class praResp extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $file_res = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'RES'", false);
        if ($file_res) {
            $file = ITA_PROC_REPOSITORY . "allegati/" . $dati['Codice'] . "/" . $file_res['ANPFIL'];
            if (file_exists($file)) {
                $contenuto = file_get_contents($file);
                $html->appendHtml($contenuto);
            }
        } else {
            $Ananom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANANOM WHERE NOMRES = '" . $dati['Anapra_rec']['PRARES'] . "'", false);
            if (!$Ananom_rec) {
                $Ananom_rec_anatsp = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANANOM WHERE NOMRES = '" . $dati['Anatsp_rec']['TSPRES'] . "'", false);
                if (!$Ananom_rec_anatsp) {
//                    $html->appendHtml("<div>");
//                    $html->appendHtml("<h3>Responsabile non disponibile.</h3></br>");
//                    $html->appendHtml("</div>");
                    return false;
                } else {
                    $Resp = $Ananom_rec_anatsp;
                }
            } else {
                $Resp = $Ananom_rec;
            }
            if ($Resp) {
                $html->appendHtml("<div>");
                $html->appendHtml("<div class = \"infoLabel\">Responsabile</div>");
                $html->appendHtml("<span class = \"infoText\">" . $Resp['NOMCOG'] . " " . $Resp['NOMNOM'] . "</span><br />");

                if ($Resp['NOMANN']) {
                    $html->appendHtml("<div class = \"infoLabel\">Orario al Pubblico</div>");
                    $html->appendHtml("<span class = \"infoText\">" . $Resp['NOMANN'] . "</span><br />");
                }

                $html->appendHtml("<div class = \"infoLabel\">Mail</div>");
                $html->appendHtml("<a href=\"mailto:" . $Resp['NOMEML'] . "\" title=\"Invia e-mail\">");
                $html->appendHtml("<span class = \"infoText\">" . $Resp['NOMEML'] . "</span></a>");
                $html->appendHtml("</div>");
            }
        }

        return $html->getHtml();
    }

}
