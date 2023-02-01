<?php

class praTer extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();
        $Itepas_tab = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT * FROM ITEPAS WHERE ITECOD = '" . $dati['Codice'] . "' AND ITEPUB=0 AND ITEGIO<>0 ORDER BY ITESEQ", true);
        $giorni = ItaDB::DBSQLSelect($dati['pramSource'], "SELECT SUM(ITEGIO) AS TOTALE FROM ITEPAS WHERE ITECOD = '" . $dati['Codice'] . "' AND ITEPUB=0 AND ITEGIO<>0 ORDER BY ITESEQ", false);

        if ($Itepas_tab) {
            $tableData = array(
                'header' => array('Passi', 'Giorni'),
                'body' => array()
            );

            foreach ($Itepas_tab as $Itepas_rec) {
                $tableData['body'][] = array(
                    $Itepas_rec['ITEDES'], '<div class="align-center">' . $Itepas_rec['ITEGIO'] . '</div>'
                );
            }

            $tableData['body'][] = array(
                '<b>TOTALE GIORNI PER COMPLETARE LA RICHIESTA</b>', '<div class="align-center"><b>' . $giorni['TOTALE'] . '</b></div>'
            );

            $html->addTable($tableData);
        } else {
            return false;
        }

        return $html->getHtml();
    }

}
