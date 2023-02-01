<?php

class praAlle extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        /*
         * Mi trovo gli altri allegati (ALL) da 001 a n sullo Slave
         */
        $arr_all_slave = array();
        for ($ki = 1; $ki <= 99; $ki++) {
            $h = str_pad($ki, 3, "0", STR_PAD_LEFT);
            $anpdoc_slave_all_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $dati['Codice'] . "' AND ANPCLA = 'ALL_$h'", false);
            if ($anpdoc_slave_all_rec) {
                $arr_all_slave[$ki]['FILENAME'] = $anpdoc_slave_all_rec['ANPFIL'];
                $arr_all_slave[$ki]['NOTE'] = $anpdoc_slave_all_rec['ANPNOT'];
            }
        }

        /*
         * Butto fuori il contenuto degli ALL dello Slave
         */
        if ($arr_all_slave) {
            if ($this->config && $this->config['idTemplate'] == 'praInfCittadino') {
                $html->appendHtml('<ul style="margin: 0; padding: 0px 10px; list-style: inside disc; line-height: 1.2em;">');

                foreach ($arr_all_slave as $anpdoc_rec) {
                    $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'vediAllegato',
                            'procedi' => $dati['Codice'],
                            'allegato' => frontOfficeApp::encrypt($anpdoc_rec['FILENAME']),
                            'type' => 'all'
                    ));

                    $textAllegati = '<li style="font-size: .9em;">';
                    $textAllegati .= '<a href="' . $allegatoHref . '">';
                    $textAllegati .= $anpdoc_rec['NOTE'];
                    $textAllegati .= '</a>';
                    $textAllegati .= '</li>';

                    $html->appendHtml($textAllegati);
                }

                $html->appendHtml('</ul>');

                return $html->getHtml();
            }

            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            $html->appendHtml($praHtmlGridAllegati->GetGridAltriAllegati($dati, $arr_all_slave));
        }

        return $html->getHtml();
    }

}
