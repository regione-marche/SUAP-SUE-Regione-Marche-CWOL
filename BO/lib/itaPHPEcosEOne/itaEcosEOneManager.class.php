<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of macCosmariPesaManager
 *
 * @author michele
 */
class itaEcosEOneManager {

    private $errCode;
    private $errMessage;

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getPesaBackEnd($params) {
        $dal = substr($params['DAL'], 0, 4) . "-" . substr($params['DAL'], 4, 2) . "-" . substr($params['DAL'], 6, 2);
        $al = substr($params['AL'], 0, 4) . "-" . substr($params['AL'], 4, 2) . "-" . substr($params['AL'], 6, 2);
        $odbc = odbc_connect('Ecos e-one', "dba", "sql");
        if ($odbc) {
            $sql = "SELECT 
                            bsmte.mte_num_p,
                            bsmco.mco_prog,
                            bsmte.mte_dat_r,
                            bsmte.mez_targa_1,
                            bsmte.mez_targa_2,
                            bsmte.doc_cod,
                            bsmco.rif_cod,
                            bsmco.mco_qta_p,
                            bsmco.mco_note_fattura,
                            bsmco.mco_num_fi,
                            bsmco.mco_tipomov,
                            bsmco.mco_direz,
                            bsmco.imp_cod_tr,
                            bsmco.imp_cod_de,
                            bsmco.imp_uni_de,
                            bsmco.imp_uni_pr,
                            bsmco.rif_cod,
                            bsimp.imp_cod,
                            bsimp.imp_ragsoc,
                            bsimp_dest.imp_ragsoc as ragsog_dest,
                            bsimp_trasp.imp_ragsoc as ragsoc_trasp,
                            bsrif.cer2_cod,
                            bsaut.aut_des,
                            bsaut.aut_cod
                                                     
                          
                    FROM bsmte
                            LEFT OUTER JOIN bsmco bsmco ON bsmte.mte_num_p=bsmco.mte_num_p AND bsmte.mte_cod_p=bsmco.mte_cod_p AND bsmte.mte_ann_p=bsmco.mte_ann_p
                            LEFT OUTER JOIN bsimp bsimp ON bsimp.imp_cod=bsmco.imp_cod_pr AND bsimp.imp_uni=bsmco.imp_uni_pr
                            LEFT OUTER JOIN bsimp bsimp_dest ON bsimp_dest.imp_cod=bsmco.imp_cod_de AND bsimp_dest.imp_uni=bsmco.imp_uni_de
                            LEFT OUTER JOIN bsimp bsimp_trasp ON bsimp_trasp.imp_cod=bsmco.imp_cod_tr AND bsimp_trasp.imp_uni=bsmco.imp_uni_pr
                            LEFT OUTER JOIN bsrif bsrif ON bsrif.rif_cod=bsmco.rif_cod
                            LEFT OUTER JOIN bsaut ON bsaut.aut_cod=bsmte.mte_aut_cod AND bsaut.imp_cod=bsmco.imp_cod_tr
                                                     
							       
                     WHERE mte_dat_r>='$dal' AND mte_dat_r<='$al'";

            $result = odbc_exec($odbc, $sql);
            while ($riga = odbc_fetch_array($result)) {
                $risultati[] = $riga;
            }
            if (count($risultati)) {
                $this->errCode = 0;
                $this->errMessage = '';
                return $risultati;
            } else {
                $this->errCode = -1;
                $this->errMessage = 'Nessun dato';
                return false;
            }
        } else {
            $this->errCode = -1;
            $this->errMessage = 'Errore Connessione';
            return false;
        }
    }

}
