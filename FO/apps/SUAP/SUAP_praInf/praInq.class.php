<?php

class praInq extends praSchedaTemplate {

    public function getHtml($dati) {
        $repositoryUrl = $dati['repositoryUrl'];

        $html = new html();

        /*
         * Mi trovo il record del file INF del Master
         */
        $file_inf = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'INF'", false);

        /*
         * Mi trovo il record del file INF dello Slave
         * e se c'è lo sostituisco a quello del Master
         */
        $file_inf_slave_000 = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'INF_000'", false);

        if ($file_inf_slave_000) {
            $file_inf = $file_inf_slave_000;
            $repositoryUrl = ITA_PROC_REPOSITORY;
        }

        /*
         * Butto Fuori il contenuto del file INF principale
         */
        if ($file_inf) {
            $file = $repositoryUrl . "allegati/" . $dati['Codice'] . "/" . $file_inf['ANPFIL'];
            if (file_exists($file)) {
                $contenuto = file_get_contents($file);
                $html->appendHtml($contenuto);
            } else {
                return false;
            }
        } else {
            return false;
        }

        /*
         * Mi trovo gli altri records dei file INF dello Slave da 001 a n
         */
        $arr_inf_slave = array();
        for ($j = 1; $j <= 99; $j++) {
            $i = str_pad($j, 3, "0", STR_PAD_LEFT);
            $anpdoc_slave_j = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'INF_$i'", false);
            if ($anpdoc_slave_j) {
                $arr_inf_slave[] = ITA_PROC_REPOSITORY . "allegati/" . $dati['Codice'] . "/" . $anpdoc_slave_j['ANPFIL'];
            }
        }

        /*
         * Butto fuori il contenuto degli altri file INF dello Slave
         */
        if ($arr_inf_slave) {
            foreach ($arr_inf_slave as $file_inf_slave) {
                $html->appendHtml(file_get_contents($file_inf_slave));
            }
        }

        return $html->getHtml();
    }

}
