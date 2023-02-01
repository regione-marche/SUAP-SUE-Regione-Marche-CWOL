<?php

/**
 *
 * Classe per collegamento Contabilità APRA
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPARSS
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.01.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

class itaLibCity {

    public $CITYWARE_DB;
    public $envLib;
    private $errCode;
    private $errMessage;

    function __construct($params) {
        $this->envLib = new envLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getCITYWARE() {
        if (!$this->CITYWARE_DB) {
            try {
                $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->CITYWARE_DB;
    }

    public function creaSqlContabilita($anno, $modoAccesso) {
        $utente = $this->getUtenteCityWare();
        if ($modoAccesso == '') {
            $modo = $this->getModoAccessoContabilita($utente);
        } else {
            $modo = $modoAccesso;
        }
        $cond = $this->getFiltroAccessoContabilita($utente, $modo);
        $sql = "SELECT fba_bilad.progkeyvb, fba_bilad.anno_ese, fba_bilad.e_s, fba_bilad.codmeccan, fba_bilad.codvocebil, fba_bilad.progkeymp, fba_bilad.progkeypf, fba_bilad.cod_meccan, fba_bilad.codvocebi, fba_bilad.des_bilav, fba_bilad.cod_contot, fba_bilad.cod_finavi, fba_bilad.sett_iva, fba_bilad.ivaaliq, fba_bilad.cod_stab11, fba_bilad.cod_stab21, fba_bilad.cod_stab31, fba_bilad.cod_stab12, fba_bilad.cod_stab22, fba_bilad.cod_stab32, fba_bilad.cod_stab13, fba_bilad.cod_stab23, fba_bilad.cod_stab33, fba_bilad.progr_bpl, fba_bilad.proge_bpl, fba_bilad.prges1_bpl, fba_bilad.cod_piaco, fba_bilad.cod_centro, fba_bilad.progente, fba_bilad.codobie, fba_bilad.idorgan_as, fba_bilad.idorgan_rs, fba_bilad.funzdelre, fba_bilad.finanzcom, fba_bilad.flag_nfraz, fba_bilad.flag_nofsc, fba_bilad.impo_resip, fba_bilad.impo_coap, fba_bilad.impo_fplup, fba_bilad.impo_preac, fba_bilad.impo_fpluv, fba_bilad.impo_encac, fba_bilad.impo_prea1, fba_bilad.impo_fplu1, fba_bilad.impo_enca1, fba_bilad.impo_prea2, fba_bilad.impo_fplu2, fba_bilad.impo_enca2, fba_bilad.impo_imac, fba_bilad.impo_iapr, fba_bilad.impo_pari, fba_bilad.impo_iaac, fba_bilad.impo_iaa1, fba_bilad.impo_iaa2, fba_bilad.impo_reap, fba_bilad.impo_iare, fba_bilad.impo_prre, fba_bilad.impo_caap, fba_bilad.impo_cain, fba_bilad.impo_aini, fba_bilad.impo_aini1, fba_bilad.impo_aini2, fba_bilad.impo_staep, fba_bilad.impo_pagp, fba_bilad.impo_dis1, fba_bilad.impo_dis2, fba_bilad.impo_dis3, fba_bilad.impo_dis4, fba_bilad.impo_dis5, fba_bilad.notebil, fba_bilad.codute, fba_bilad.dataoper, fba_bilad.timeoper, fba_bilad.flag_dis, 0 AS codvocebil_parte1, 0 AS codvocebil_parte2, 0 AS codvocebil_parte3, fba_bila.anno_ini, fba_bila.anno_fine, fba_tabapf.cod_liv1, fba_tabapf.cod_liv2, fba_tabapf.cod_liv3, fba_tabapf.cod_liv4, fba_tabapf.cod_liv5, COALESCE(fba_tabamp.codmission::integer, 0) AS codmission, COALESCE(fba_tabamp.codprogra::integer, 0) AS codprogra, o_as.l1org AS l1org_as, o_as.l2org AS l2org_as, o_as.l3org AS l3org_as, o_as.l4org AS l4org_as, o_as.desporg AS ruoloute_as, o_rs.l1org AS l1org_rs, o_rs.l2org AS l2org_rs, o_rs.l3org AS l3org_rs, o_rs.l4org AS l4org_rs, o_rs.desporg AS ruoloute_rs,
                        fba_varb_v01.impv_covp, fba_varb_v01.impv_covn, fba_varb_v01.impv_fpvp, fba_varb_v01.impv_fpvn, fbi_voci.cod_siope
                        FROM cityware.fba_bilad
                        LEFT JOIN cityware.fba_bila ON fba_bilad.progkeyvb = fba_bila.progkeyvb
                        LEFT JOIN cityware.fba_tabamp ON fba_bilad.progkeymp = fba_tabamp.progkeymp AND fba_bilad.anno_ese = fba_tabamp.anno_ese
                        LEFT JOIN cityware.fba_tabapf ON fba_bilad.progkeypf = fba_tabapf.progkeypf AND fba_bilad.anno_ese = fba_tabapf.anno_ese
                        LEFT JOIN cityware.bor_organ o_as ON fba_bilad.idorgan_as = o_as.idorgan
                        LEFT JOIN cityware.bor_organ o_rs ON fba_bilad.idorgan_rs = o_rs.idorgan
                        LEFT JOIN cityware.fba_varb_v01 ON fba_bilad.anno_ese = fba_varb_v01.anno_ese AND fba_bilad.progkeyvb = fba_varb_v01.progkeyvb
                        LEFT JOIN cityware.fbi_voci ON fba_bilad.anno_ese = fbi_voci.anno_ese AND fba_bilad.e_s = fbi_voci.e_s AND fba_bilad.cod_meccan = fbi_voci.cod_meccan AND fba_bilad.codvocebi = fbi_voci.codvocebi";
        $sql.= " WHERE fba_bilad.flag_dis = 0 AND fba_bilad.anno_ese = $anno AND $cond";
        return $sql;
    }

    public function creaSqlImpegni($anno, $progKey) {
        $sql = "SELECT DISTINCT fes_imp.progimpacc, fes_imp.anno_ese, fes_imp.annorif, fes_imp.n_impeg, fes_imp.numeroimp, fes_imp.e_s, fes_imp.cod_meccan, fes_imp.codvocebi, fes_imp.voce_econb, fes_imp.cod_siope, fes_imp.des_imp, fes_imp.imimp_orig, fes_imp.imimp_prep, fes_imp.imimp_impo, fes_imp.imimp_disl, fes_imp.imimp_disp, fes_imp.class_imp, fes_imp.datascade, fes_imp.flag_impc, fes_imp.anno_chiu, fes_imp.flag_impa, fes_imp.flag_geord, fes_imp.flag_regcg, fes_imp.cod_contot, fes_imp.cod_finavi, fes_imp.prog_cup, fes_imp.progsogg, fes_imp.cod_centro, fes_imp.cod_ripart, fes_imp.cod_piaco, fes_imp.cod_piaca, fes_imp.tipodelib, fes_imp.numdelib, fes_imp.datadelib, fes_imp.tipodeliba, fes_imp.numdeliba, fes_imp.datadeliba, fes_imp.tipodelibr, fes_imp.numdelibr, fes_imp.datadelibr, fes_imp.tipodelibv, fes_imp.numdelibv, fes_imp.datadelibv, fes_imp.l1org, fes_imp.l2org, fes_imp.l3org, fes_imp.l4org, fes_imp.cod_stab11, fes_imp.cod_stab21, fes_imp.cod_stab31, fes_imp.cod_stab12, fes_imp.cod_stab22, fes_imp.cod_stab32, fes_imp.cod_stab13, fes_imp.cod_stab23, fes_imp.cod_stab33, fes_imp.cod_stab14, fes_imp.cod_stab24, fes_imp.cod_stab34, fes_imp.datascadl, fes_imp.prognote, fes_imp.prog_regpn, fes_imp.ivaaliq, fes_imp.coduteins, fes_imp.datainser, fes_imp.timeinser, fes_imp.codute, fes_imp.dataoper, fes_imp.timeoper, fes_imp.flag_dis, fes_imp.progente, fes_imp.codobie, fes_imp.c_commessa, fes_imp.prog_cig, fes_imp.codmeccan, fes_imp.codvocebil, fes_imp.flag_dinv, fes_imp.flag_tipen, fes_imp.cod_bolli, fes_imp.progkeyvb, fes_imp.proven_imp, fta_claimp.flag_pd, bta_sogg.cognome, bta_sogg.nome, fba_tabamp.codmission, fba_tabamp.codprogra, fba_tabapf.cod_liv1, fba_tabapf.cod_liv2, fba_tabapf.cod_liv3, fba_tabapf.cod_liv4, fba_tabapf.cod_liv5, fba_bilad.anno_ese AS anno_bila
                FROM cityware.fes_imp
                LEFT JOIN cityware.fba_bilad ON fes_imp.progkeyvb = fba_bilad.progkeyvb AND fes_imp.progkeyvb <> 0 AND fba_bilad.progkeyvb <> 0
                LEFT JOIN cityware.fta_claimp ON fes_imp.class_imp = fta_claimp.class_imp
                LEFT JOIN cityware.bta_sogg ON fes_imp.progsogg = bta_sogg.progsogg
                LEFT JOIN cityware.fba_tabamp ON fba_bilad.progkeymp = fba_tabamp.progkeymp AND fba_bilad.anno_ese = fba_tabamp.anno_ese
                LEFT JOIN cityware.fba_tabapf ON fba_bilad.progkeypf = fba_tabapf.progkeypf AND fba_bilad.anno_ese = fba_tabapf.anno_ese";
        $sql.= " WHERE fba_bilad.anno_ese = $anno AND fba_bilad.progkeyvb = $progKey";
        $sql.= " ORDER BY fes_imp.annorif DESC, fes_imp.n_impeg DESC";

        return $sql;
    }
    
    public function creaSqlPagatoPerImpegno($anno, $progkey){
        $sql = "SELECT * FROM cityware.fes_impval WHERE anno_ese= $anno AND progimpacc = $progkey";
        return $sql;
    }

    public function getUtenteCityWare() {
        $idUtente = App::$utente->getKey('idUtente');
        $utenteCityware_rec = $this->envLib->GetEnvUtemeta('UTE_CITYWARE', $idUtente);
        if ($utenteCityware_rec) {
            return $utenteCityware_rec['Utente'];
        } else {
            return '';
        }
    }

    public function getModoAccessoContabilita($utente) {
        $sql = "SELECT * FROM FTA_AUTUTE WHERE FLAG_DIS = 0 AND CODUTE_OP = '$utente'";
        $modoAccesso = ItaDB::DBSQLSelect($this->getCITYWARE(), $sql, false);
        if (!$modoAccesso) {
            return 99;
        } else {
            return $modoAccesso['MODO_NAVBI'];
        }
    }

    public function getFiltroAccessoContabilita($utente, $modo) {
        switch ($modo) {
            case 99:
                $cond = '1=0';  // NON AUTORIZZATO
                break;
            case 0:
                $cond = '1=1';  // AUTORIZZATO - NESSUN LIMITE
                break;
            case 1:
                $cond = $this->soloVociUtente($utente);
                break;
            case 2:
                $cond = $this->daOrganigramma($utente);
                break;
            case 3:
                $cond = '1=0';  // NON AUTORIZZATO
                break;
            default :
                $cond = '1=0';  // NON AUTORIZZATO
        }
        return $cond;
    }

    public function soloVociUtente($utente) {
        $sql = "SELECT * FROM BOR_UTEORG WHERE FLAG_DIS = 0 AND DATAFINE IS NULL AND CODUTE = '$utente'";
        $vociUtente = ItaDB::DBSQLSelect($this->getCITYWARE(), $sql, false);
        if (!$vociUtente) {
            $cond = "1=0";  // NON AUTORIZZATO
        } else {
            $cond = " ((o_as.l1org='" . $vociUtente['L1ORG'] . "' AND o_as.l2org='" . $vociUtente['L2ORG'] . "' AND o_as.l3org='" . $vociUtente['L3ORG'] . "')";
            $cond.= " OR (o_rs.l1org='" . $vociUtente['L1ORG'] . "' AND o_rs.l2org='" . $vociUtente['L2ORG'] . "' AND o_rs.l3org='" . $vociUtente['L3ORG'] . "'))";
        }
        return $cond;
    }

    public function daOrganigramma($utente) {
        $sql = "SELECT * FROM FTA_FUNZUT WHERE FLAG_DIS=0  AND CODUTEOPE = '$utente' ORDER BY PROGRIFU";
        $organigramma = ItaDB::DBSQLSelect($this->getCITYWARE(), $sql, true);
        if (!$organigramma) {
            $cond = "1=0";  // NON AUTORIZZATO
        } else {
            if ($organigramma[0]['PROGRIFU'] == 0) {
                $cond = "1=1";  // AUTORIZZATO - NESSUN LIMITE
            } else {
                $cond = "";
                foreach ($organigramma as $orga) {
                    if ($cond) {
                        $cond.= " OR ";
                    }
                    $cond.= " (o_as.l1org='" . $orga['L1ORG_PA'] . "' AND o_as.l2org='" . $orga['L2ORG_PA'] . "' AND o_as.l3org='" . $orga['L3ORG_PA'] . "')";
                    $cond.= " OR (o_rs.l1org='" . $orga['L1ORG_PA'] . "' AND o_rs.l2org='" . $orga['L2ORG_PA'] . "' AND o_rs.l3org='" . $orga['L3ORG_PA'] . "')";
                }
                $cond = " ($cond)";
            }
        }
        return $cond;
    }

    function importoEuro($numero, $simboloeuro = "", $decimali = 2) {
        $numero = floatval($numero);
        $str = number_format($numero, $decimali, '.', '');
        return $str;
    }

}

?>
