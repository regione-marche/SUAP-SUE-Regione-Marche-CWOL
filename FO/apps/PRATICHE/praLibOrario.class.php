<?php

/**
 * Description of praLibOrario
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
class praLibOrario {

    /**
     * @param type $PRAM_DB
     * @param type $sportello_richiesta
     * @param string $data_richiesta YYYYMMDD
     * @param string $ora_richiesta HH:MM
     * @return int 1 (valido), 0 (non valido)
     */
    public function verificaAperturaSportello($PRAM_DB, $sportello_richiesta, $data_richiesta = false, $ora_richiesta = false) {
        if (!$data_richiesta) {
            $data_richiesta = date('Ymd');
        }

        if (!$ora_richiesta) {
            $ora_richiesta = date('H:i');
        }

        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', ITA_DB_SUFFIX);

        $anatsp_rec_sql = "SELECT TSPACTORARIO FROM ANATSP WHERE TSPCOD = '$sportello_richiesta'";
        $anatsp_rec = ItaDB::DBSQLSelect($PRAM_DB, $anatsp_rec_sql, false);

        if (!$anatsp_rec) {
            return 1;
        }

        if ($anatsp_rec['TSPACTORARIO'] == 0) {
            return 1;
        }

        $ret_data_particolare = $this->verificaAperturaSportelloORARIFO($PRAM_DB, $sportello_richiesta, $data_richiesta, $ora_richiesta, 'DT');
        if ($ret_data_particolare > -1) {
            return $ret_data_particolare;
        }

        $cal_tabfesta_rec_sql = "SELECT TIPOFESTA FROM CAL_TABFESTA WHERE DATAFESTA = '$data_richiesta'";
        $cal_tabfesta_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $cal_tabfesta_rec_sql, false);

        if ($cal_tabfesta_rec) {
            switch ($cal_tabfesta_rec['TIPOFESTA']) {
                case 'F':
                    $tipo_giorno = 'FE';
                    break;

                case 'P':
                    $tipo_giorno = 'PR';
                    break;
            }

            $ret_data_festivita = $this->verificaAperturaSportelloORARIFO($PRAM_DB, $sportello_richiesta, $data_richiesta, $ora_richiesta, $tipo_giorno);
            if ($ret_data_festivita > -1) {
                return $ret_data_festivita;
            }
        }

        $ret_data_giorno_settimanale = $this->verificaAperturaSportelloORARIFO($PRAM_DB, $sportello_richiesta, $data_richiesta, $ora_richiesta, 'GS');
        if ($ret_data_giorno_settimanale > -1) {
            return $ret_data_giorno_settimanale;
        }

        return 1;
    }

    /**
     * 
     * @param type $sportello_richiesta
     * @param string $data_richiesta YYYYMMDD
     * @param string $ora_richiesta HH:MM
     * @param string $tipo_giorno DT | FE | PR | GS
     * @return int 1 (valido), 0 (non valido), -1 (non trovato)
     */
    private function verificaAperturaSportelloORARIFO($PRAM_DB, $sportello_richiesta, $data_richiesta, $ora_richiesta, $tipo_giorno) {
        $where = array(
            'ORTSPCOD' => $sportello_richiesta,
            'ORTIPO' => $tipo_giorno,
            'ORDATEANN' => ''
        );

        switch ($tipo_giorno) {
            case 'GS':
                $ora_richiesta_array = explode(':', $ora_richiesta);
                $giorno_settimana = date('w', mktime($ora_richiesta_array[0], $ora_richiesta_array[1], '00', substr($data_richiesta, 4, 2), substr($data_richiesta, 6, 2), substr($data_richiesta, 0, 4)));
                $where['ORGIORNONUM'] = $giorno_settimana;
                break;

            case 'FE':
            case 'PR':
                break;

            case 'DT':
                $where['ORDATA'] = $data_richiesta;
                break;
        }

        $orarifo_rec_sql = "SELECT
                                ORNEGA,
                                ORINI,
                                ORFIN
                            FROM
                                ORARIFO
                            WHERE
                                1 = 1";

        foreach ($where as $where_field => $where_value) {
            $orarifo_rec_sql .= " AND $where_field = '$where_value'";
        }

        $orarifo_rec = ItaDB::DBSQLSelect($PRAM_DB, $orarifo_rec_sql, false);

        if (!$orarifo_rec) {
            return -1;
        }

        if ($orarifo_rec['ORNEGA'] == 1) {
            return 0;
        }

        if ($orarifo_rec['ORINI'] && $orarifo_rec['ORINI'] > $ora_richiesta) {
            return 0;
        }

        if ($orarifo_rec['ORFIN'] && $orarifo_rec['ORFIN'] < $ora_richiesta) {
            return 0;
        }

        return 1;
    }

}
