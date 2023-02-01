<?php

class praOpenData extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    private $mesi = array(
        '01' => 'Gennaio',
        '02' => 'Febbraio',
        '03' => 'Marzo',
        '04' => 'Aprile',
        '05' => 'Maggio',
        '06' => 'Giugno',
        '07' => 'Luglio',
        '08' => 'Agosto',
        '09' => 'Settembre',
        '10' => 'Ottobre',
        '11' => 'Novembre',
        '12' => 'Dicembre'
    );

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
        } catch (Exception $e) {
            
        }
    }

    private function getArrayMensile($anno) {
        $mensile = array();
        $ultimo = date('Y') == $anno ? date('m') : '12';
        foreach ($this->mesi as $mese => $desc) {
            if ((int) $mese > (int) $ultimo) {
                break;
            }

            $mensile[$desc] = 0;
        }

        return $mensile;
    }

    public function parseEvent() {

        $anno = frontOfficeApp::$cmsHost->getRequest('anno') ?: date('Y');
        $enti = explode('-', $this->config['ente']);

        $openData = array(
            'Anno' => (int) $anno,
            'Sportello' => array(),
            'Settore' => array()
        );

        $sql_pra_where = "WHERE (RICSTA = '01' OR RICSTA = '91') AND SUBSTRING(RICDRE, 1, 6) = '" . addslashes($anno) . "%s'";

        $sql_pra_tsp = "SELECT COUNT(*) as COUNT, RICTSP FROM PRORIC $sql_pra_where GROUP BY RICTSP";
        $sql_pra_set = "SELECT COUNT(*) as COUNT, RICSTT FROM PRORIC $sql_pra_where GROUP BY RICSTT";

        foreach ($enti as $ente) {
            $PRAM_DB = ItaDB::DBOpen('PRAM', $ente);

            foreach ($this->mesi as $meseCod => $meseDes) {
                $proric_sportello = ItaDB::DBSQLSelect($PRAM_DB, sprintf($sql_pra_tsp, $meseCod));
                foreach ($proric_sportello as $proric_rec) {
                    $anatsp_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT TSPDES, TSPCOM FROM ANATSP WHERE TSPCOD = '{$proric_rec['RICTSP']}'", false);

                    if (!$anatsp_rec['TSPDES']) {
                        continue;
                    }

                    $anatsp_rec['TSPCOM'] = utf8_encode($anatsp_rec['TSPCOM']) ?: $ente;
                    $anatsp_rec['TSPDES'] = utf8_encode($anatsp_rec['TSPDES']);

                    $openData['Sportello'][$anatsp_rec['TSPDES']]['Totale'] += $proric_rec['COUNT'];
                    $openData['Sportello'][$anatsp_rec['TSPDES']]['Comuni'][$anatsp_rec['TSPCOM']] += $proric_rec['COUNT'];

                    if (!isset($openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'])) {
                        $openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'] = $this->getArrayMensile($anno);
                    }

                    $openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'][$meseDes] += $proric_rec['COUNT'];
                }

                $proric_settore = ItaDB::DBSQLSelect($PRAM_DB, sprintf($sql_pra_set, $meseCod));
                foreach ($proric_settore as $proric_rec) {
                    $anaset_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT SETDES FROM ANASET WHERE SETCOD = '{$proric_rec['RICSTT']}'", false);

                    if (!$anaset_rec['SETDES']) {
                        continue;
                    }

                    $anaset_rec['SETDES'] = utf8_encode($anaset_rec['SETDES']);

                    $openData['Settore'][$anaset_rec['SETDES']]['Totale'] += $proric_rec['COUNT'];

                    if (!isset($openData['Settore'][$anaset_rec['SETDES']]['Mensile'])) {
                        $openData['Settore'][$anaset_rec['SETDES']]['Mensile'] = $this->getArrayMensile($anno);
                    }

                    $openData['Settore'][$anaset_rec['SETDES']]['Mensile'][$meseDes] += $proric_rec['COUNT'];
                }
            }
        }

        @ob_end_clean();

        $ob_level = ob_get_level();
        for ($i = $ob_level; $i > 0; $i--) {
            @ob_end_clean();
        }

        header('Content-type: application/json');

        @ob_end_flush();

        echo json_encode($openData);
        exit;
    }

}
