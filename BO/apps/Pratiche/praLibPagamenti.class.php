<?php

class praLibPagamenti {

    public $PRAM_DB;
    private $errMessage;
    private $errCode;

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function sincronizzaSomme($numeroPratica, $progressivoOnere, $verbose = false) {
        $sql = "SELECT
                    ROWID,
                    IMPORTO
                FROM
                    PROIMPO
                WHERE
                    IMPONUM = '{$numeroPratica}'
                AND
                    IMPOPROG = '{$progressivoOnere}'";

        $proimpo_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        $sommaDaPagare = floatval($proimpo_rec['IMPORTO']);
        $sommaPagata = 0;

        $sql = "SELECT
                    ROWID,
                    CONCILIAZIONE,
                    SOMMAPAGATA
                FROM
                    PROCONCILIAZIONE
                WHERE
                    IMPONUM = '{$numeroPratica}'
                AND
                    IMPOPROG = '{$progressivoOnere}'";

        $proconciliazione_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        foreach ($proconciliazione_tab as $proconciliazione_rec) {
            if (
                    !$proconciliazione_rec['CONCILIAZIONE'] ||
                    ($proconciliazione_rec['CONCILIAZIONE'] == 'S' && floatval($proconciliazione_rec['SOMMAPAGATA']) < $sommaDaPagare) ||
                    ($proconciliazione_rec['CONCILIAZIONE'] == 'P' && floatval($proconciliazione_rec['SOMMAPAGATA']) >= $sommaDaPagare)
            ) {
                $proconciliazione_rec['CONCILIAZIONE'] = floatval($proconciliazione_rec['SOMMAPAGATA']) >= $sommaDaPagare ? 'S' : 'P';

                try {
                    $n = ItaDB::DBUpdate($this->PRAM_DB, 'PROCONCILIAZIONE', 'ROWID', $proconciliazione_rec);
                } catch (Exception $e) {
                    $this->errCode = -1;
                    $this->errMessage = $e->getMessage();
                    return false;
                }
            }

            $sommaPagata += floatval($proconciliazione_rec['SOMMAPAGATA']);
        }

        $differenzaSomme = $sommaDaPagare - $sommaPagata;

        if ($differenzaSomme < 0 && $verbose) {
            Out::msgInfo("Attenzione", "La somma dei pagamenti supera l'importo dell'onere");
        }

        $proimpo_rec['PAGATO'] = $sommaPagata;
        $proimpo_rec['DIFFERENZA'] = $differenzaSomme;

        try {
            ItaDB::DBUpdate($this->PRAM_DB, 'PROIMPO', 'ROWID', $proimpo_rec);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = $e->getMessage();
            return false;
        }

        return true;
    }

}
