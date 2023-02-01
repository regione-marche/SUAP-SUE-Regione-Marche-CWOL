<?php

/**
 * Description of praLibEventi
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
class praLibEventi {

    public function getOggettoProric($PRAM_DB, $proric_rec) {
        if (isset($proric_rec['RICDESCR']) && $proric_rec['RICDESCR']) {
            return $proric_rec['RICDESCR'];
        }

        /* @var $praLib praLib */
        $praLib = new praLib();

        $anapra_rec = $praLib->GetAnapra($proric_rec['RICPRO'], 'codice', $PRAM_DB);
        $EVTDESCR = $this->getDescrizioneEvento($PRAM_DB, $proric_rec['RICEVE']);

        if ($proric_rec['RICEVE'] == "000006") {// Se evento ALTRO, lo nascondo
            return $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
        } else {
            return $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'] . '<br>Evento: ' . $EVTDESCR;
        }
    }

    public function getOggetto($PRAM_DB, $anapra_rec, $iteevt_rec = false) {
        if (!is_array($anapra_rec) && $anapra_rec) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
            /* @var $praLib praLib */
            $praLib = new praLib();
            $anapra_rec = $praLib->GetAnapra($anapra_rec, 'codice', $PRAM_DB);
        }

        if (!is_array($iteevt_rec) && $iteevt_rec) {
            $iteevt_rec = $this->getEvento($PRAM_DB, $anapra_rec['PRANUM'], $iteevt_rec);
        }

        if (!isset($iteevt_rec['EVTDESCR'])) {
            $iteevt_rec['EVTDESCR'] = $this->getDescrizioneEvento($PRAM_DB, $iteevt_rec['IEVCOD']);
        }

        /*
         * Se presente nuovo flag non mostare descrizione eventro
         * salta all'ultimo retune e mostra solo la descrione del procedimento
         */
        if (is_array($iteevt_rec)) {
            if ($iteevt_rec['IEVDESCR']) {
                return $iteevt_rec['IEVDESCR'];
            } else if ($iteevt_rec['EVTDESCR']) {
                if ($iteevt_rec['IEVCOD'] == "000006") {// Se evento ALTRO, lo nascondo
                    return $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
                } else {
                    return $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'] . '<br>Evento: ' . $iteevt_rec['EVTDESCR'];
                }
            }
        }

        return $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
    }

    public function getDescrizioneEvento($PRAM_DB, $evtcod) {
        $sql = "SELECT
                    ANAEVENTI.EVTDESCR
                FROM
                    ANAEVENTI
                WHERE
                    ANAEVENTI.EVTCOD = '$evtcod'";

        $anaeventi_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        return $anaeventi_rec ? $anaeventi_rec['EVTDESCR'] : '';
    }

    public function getSegnalazioneComunicaEvento($PRAM_DB, $evtcod) {
        $sql = "SELECT
                    ANAEVENTI.EVTSEGCOMUNICA
                FROM
                    ANAEVENTI
                WHERE
                    ANAEVENTI.EVTCOD = '$evtcod'";

        $anaeventi_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        return $anaeventi_rec ? $anaeventi_rec['EVTSEGCOMUNICA'] : '';
    }

    public function getEvento($PRAM_DB, $pranum, $ievcod) {
        $sql = "SELECT
                    ITEEVT.*, ANAEVENTI.*
                FROM
                    ITEEVT
                LEFT OUTER JOIN
                    ANAEVENTI
                ON
                    ITEEVT.IEVCOD = ANAEVENTI.EVTCOD
                WHERE
                    ITEEVT.ITEPRA = '$pranum'
                AND
                    ITEEVT.IEVCOD = '$ievcod'";

        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function getIteevt($PRAM_DB, $codice, $tipoRic = 'rowid') {
        $sql = "SELECT * FROM ITEEVT WHERE ROWID = '$codice'";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    /**
     * 
     * @param type $PRAM_DB
     * @param string $pranum
     * @return array|boolean
     */
    public function getEventi($PRAM_DB, $pranum, $data = false) {
        if ($data === false) {
            $data = date('Ymd');
        }

        /*
         * Verifico la presenza delle nuove tabelle
         * per gli Eventi
         */
        $database_tables = $PRAM_DB->listTables();
        if (!in_array('ANAEVENTI', $database_tables) || !in_array('ITEEVT', $database_tables)) {
            return false;
        }

        $sql = "SELECT
                    ITEEVT.*, ANAEVENTI.*
                FROM
                    ITEEVT
                LEFT OUTER JOIN
                    ANAEVENTI
                ON
                    ITEEVT.IEVCOD = ANAEVENTI.EVTCOD
                WHERE
                    ITEEVT.ITEPRA = '$pranum'";

        $sql_filter = " AND
                            ( ITEEVT.IEVDVA = '' OR ITEEVT.IEVDVA <= $data )
                        AND
                            ( ITEEVT.IEVAVA = '' OR ITEEVT.IEVAVA >= $data )";

        $iteevt_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql . $sql_filter, true);

        /*
         * Se sono presenti Eventi validi, ritorno la tab risultante,
         * in caso contrario verifico che ci siano degli Eventi (anche non validi)
         * e, se presenti, ritorno un array vuoto, oppure false
         */
        if ($iteevt_tab) {
            return $iteevt_tab;
        } else {
            /*
             * Se non si volesse bloccare il tutto quando gli Eventi non sono validi,
             * basta ritornare direttamente false
             * return false
             */
            return ItaDB::DBSQLSelect($PRAM_DB, $sql, true) ? array() : false;
        }
    }

}
