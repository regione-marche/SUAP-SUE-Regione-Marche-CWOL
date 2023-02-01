<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praCustomClassBase.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praMigrationPagoPA extends praCustomClass {

    public function sistemaPassoSuccessivo($pranum) {
        $praLib = new praLib();

        /*
         * Sistemazione Salti
         */
        $sqlPassi = "SELECT * FROM ITEPAS WHERE ITECOD = '$pranum' AND ITEPAS.ITEVPA <> '' ORDER BY ITECOD, ITESEQ ";
        $itepas_tab = ItaDB::DBSQLSelect($praLib->PRAM_DB, $sqlPassi, true);
        if ($itepas_tab) {
            foreach ($itepas_tab as $itepas_rec) {
                $sqlControllo = "SELECT * FROM ITEPAS WHERE ITECOD = '$pranum' AND ITEPAS.ITEKEY = '" . $itepas_rec['ITEVPA'] . "' ";
                $itepasControllo_rec = ItaDB::DBSQLSelect($praLib->PRAM_DB, $sqlControllo, false);
                if (!$itepasControllo_rec) {
                    $seqSucc = $itepas_rec['ITESEQ'] + 20;
                    $sqlSucc = "SELECT * FROM ITEPAS WHERE ITEPAS.ITECOD = '$pranum' AND ITEPAS.ITESEQ = " . $seqSucc;
                    $itepasSucc_rec = ItaDB::DBSQLSelect($praLib->PRAM_DB, $sqlSucc, false);
                    if ($itepasSucc_rec) {
                        $itepas_rec['ITEVPA'] = $itepasSucc_rec['ITEKEY'];
                        $nrow = ItaDB::DBUpdate($praLib->PRAM_DB, "ITEPAS", "ROWID", $itepas_rec);
                        if ($nrow == -1) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

}
