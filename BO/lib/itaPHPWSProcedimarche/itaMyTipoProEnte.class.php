<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDestinatari
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaMyTipoProEnte {

    private $DS_AnniConservazione;
    private $DS_CF_Ente;
    private $DS_CodiceClassifica;
    private $DS_CodiceProcedimentoEnte;
    private $DS_CustomerSatisfation;
    private $DS_ID_ProcedimentoPaleo;
    private $DS_ID_SerieArchivistica;
    private $DS_ID_Sistema;
    private $DS_ID_TipoFascicolo;
    private $DS_ID_TipoProcedimento;
    private $DS_LinkModulistica;
    private $DS_LinkServizio;
    private $DS_ModalitaPagamenti;
    private $DS_ModalitaRichiestaInfo;
    private $DS_NomeCognomeSostituto;
    private $DS_NomeProcedimento;
    private $DS_RespProcCognome;
    private $DS_RespProcNome;
    private $DS_RisorseFinanziarie;
    private $DS_UO_CodiceIpaCompetente;
    private $DS_UO_CodiceIpaIstruttoria;
    private $DS_UO_Competente;
    private $DS_UO_CompetenzaIstruttoria;
    private $DS_UO_RecapitiIstruttoria;

    public function getDS_AnniConservazione() {
        return $this->DS_AnniConservazione;
    }

    public function getDS_CF_Ente() {
        return $this->DS_CF_Ente;
    }

    public function getDS_CodiceClassifica() {
        return $this->DS_CodiceClassifica;
    }

    public function getDS_CodiceProcedimentoEnte() {
        return $this->DS_CodiceProcedimentoEnte;
    }

    public function getDS_CustomerSatisfation() {
        return $this->DS_CustomerSatisfation;
    }

    public function getDS_ID_ProcedimentoPaleo() {
        return $this->DS_ID_ProcedimentoPaleo;
    }

    public function getDS_ID_SerieArchivistica() {
        return $this->DS_ID_SerieArchivistica;
    }

    public function getDS_ID_Sistema() {
        return $this->DS_ID_Sistema;
    }

    public function getDS_ID_TipoFascicolo() {
        return $this->DS_ID_TipoFascicolo;
    }

    public function getDS_ID_TipoProcedimento() {
        return $this->DS_ID_TipoProcedimento;
    }

    public function getDS_LinkModulistica() {
        return $this->DS_LinkModulistica;
    }

    public function getDS_LinkServizio() {
        return $this->DS_LinkServizio;
    }

    public function getDS_ModalitaPagamenti() {
        return $this->DS_ModalitaPagamenti;
    }

    public function getDS_ModalitaRichiestaInfo() {
        return $this->DS_ModalitaRichiestaInfo;
    }

    public function getDS_NomeCognomeSostituto() {
        return $this->DS_NomeCognomeSostituto;
    }

    public function getDS_NomeProcedimento() {
        return $this->DS_NomeProcedimento;
    }

    public function getDS_RespProcCognome() {
        return $this->DS_RespProcCognome;
    }

    public function getDS_RespProcNome() {
        return $this->DS_RespProcNome;
    }

    public function getDS_RisorseFinanziarie() {
        return $this->DS_RisorseFinanziarie;
    }

    public function getDS_UO_CodiceIpaCompetente() {
        return $this->DS_UO_CodiceIpaCompetente;
    }

    public function getDS_UO_CodiceIpaIstruttoria() {
        return $this->DS_UO_CodiceIpaIstruttoria;
    }

    public function getDS_UO_Competente() {
        return $this->DS_UO_Competente;
    }

    public function getDS_UO_CompetenzaIstruttoria() {
        return $this->DS_UO_CompetenzaIstruttoria;
    }

    public function getDS_UO_RecapitiIstruttoria() {
        return $this->DS_UO_RecapitiIstruttoria;
    }

    public function setDS_AnniConservazione($DS_AnniConservazione) {
        $this->DS_AnniConservazione = $DS_AnniConservazione;
    }

    public function setDS_CF_Ente($DS_CF_Ente) {
        $this->DS_CF_Ente = $DS_CF_Ente;
    }

    public function setDS_CodiceClassifica($DS_CodiceClassifica) {
        $this->DS_CodiceClassifica = $DS_CodiceClassifica;
    }

    public function setDS_CodiceProcedimentoEnte($DS_CodiceProcedimentoEnte) {
        $this->DS_CodiceProcedimentoEnte = $DS_CodiceProcedimentoEnte;
    }

    public function setDS_CustomerSatisfation($DS_CustomerSatisfation) {
        $this->DS_CustomerSatisfation = $DS_CustomerSatisfation;
    }

    public function setDS_ID_ProcedimentoPaleo($DS_ID_ProcedimentoPaleo) {
        $this->DS_ID_ProcedimentoPaleo = $DS_ID_ProcedimentoPaleo;
    }

    public function setDS_ID_SerieArchivistica($DS_ID_SerieArchivistica) {
        $this->DS_ID_SerieArchivistica = $DS_ID_SerieArchivistica;
    }

    public function setDS_ID_Sistema($DS_ID_Sistema) {
        $this->DS_ID_Sistema = $DS_ID_Sistema;
    }

    public function setDS_ID_TipoFascicolo($DS_ID_TipoFascicolo) {
        $this->DS_ID_TipoFascicolo = $DS_ID_TipoFascicolo;
    }

    public function setDS_ID_TipoProcedimento($DS_ID_TipoProcedimento) {
        $this->DS_ID_TipoProcedimento = $DS_ID_TipoProcedimento;
    }

    public function setDS_LinkModulistica($DS_LinkModulistica) {
        $this->DS_LinkModulistica = $DS_LinkModulistica;
    }

    public function setDS_LinkServizio($DS_LinkServizio) {
        $this->DS_LinkServizio = $DS_LinkServizio;
    }

    public function setDS_ModalitaPagamenti($DS_ModalitaPagamenti) {
        $this->DS_ModalitaPagamenti = $DS_ModalitaPagamenti;
    }

    public function setDS_ModalitaRichiestaInfo($DS_ModalitaRichiestaInfo) {
        $this->DS_ModalitaRichiestaInfo = $DS_ModalitaRichiestaInfo;
    }

    public function setDS_NomeCognomeSostituto($DS_NomeCognomeSostituto) {
        $this->DS_NomeCognomeSostituto = $DS_NomeCognomeSostituto;
    }

    public function setDS_NomeProcedimento($DS_NomeProcedimento) {
        $this->DS_NomeProcedimento = $DS_NomeProcedimento;
    }

    public function setDS_RespProcCognome($DS_RespProcCognome) {
        $this->DS_RespProcCognome = $DS_RespProcCognome;
    }

    public function setDS_RespProcNome($DS_RespProcNome) {
        $this->DS_RespProcNome = $DS_RespProcNome;
    }

    public function setDS_RisorseFinanziarie($DS_RisorseFinanziarie) {
        $this->DS_RisorseFinanziarie = $DS_RisorseFinanziarie;
    }

    public function setDS_UO_CodiceIpaCompetente($DS_UO_CodiceIpaCompetente) {
        $this->DS_UO_CodiceIpaCompetente = $DS_UO_CodiceIpaCompetente;
    }

    public function setDS_UO_CodiceIpaIstruttoria($DS_UO_CodiceIpaIstruttoria) {
        $this->DS_UO_CodiceIpaIstruttoria = $DS_UO_CodiceIpaIstruttoria;
    }

    public function setDS_UO_Competente($DS_UO_Competente) {
        $this->DS_UO_Competente = $DS_UO_Competente;
    }

    public function setDS_UO_CompetenzaIstruttoria($DS_UO_CompetenzaIstruttoria) {
        $this->DS_UO_CompetenzaIstruttoria = $DS_UO_CompetenzaIstruttoria;
    }

    public function setDS_UO_RecapitiIstruttoria($DS_UO_RecapitiIstruttoria) {
        $this->DS_UO_RecapitiIstruttoria = $DS_UO_RecapitiIstruttoria;
    }

    public function getSoapValRequest() {
        $soapvalArr = array();
        if ($this->DS_AnniConservazione) {
            $soapvalArr[] = new soapval('wcf:DS_AnniConservazione', 'wcf:DS_AnniConservazione', $this->DS_AnniConservazione, false, false);
        }
        if ($this->DS_CF_Ente) {
            $soapvalArr[] = new soapval('wcf:DS_CF_Ente', 'wcf:DS_CF_Ente', $this->DS_CF_Ente, false, false);
        }
        if ($this->DS_CodiceClassifica) {
            $soapvalArr[] = new soapval('wcf:DS_CodiceClassifica', 'wcf:DS_CodiceClassifica', $this->DS_CodiceClassifica, false, false);
        }
        if ($this->DS_CodiceProcedimentoEnte) {
            $soapvalArr[] = new soapval('wcf:DS_CodiceProcedimentoEnte', 'wcf:DS_CodiceProcedimentoEnte', $this->DS_CodiceProcedimentoEnte, false, false);
        }
        if ($this->DS_CustomerSatisfation) {
            $soapvalArr[] = new soapval('wcf:DS_CustomerSatisfation', 'wcf:DS_CustomerSatisfation', $this->DS_CustomerSatisfation, false, false);
        }
        if ($this->DS_ID_ProcedimentoPaleo) {
            $soapvalArr[] = new soapval('wcf:DS_ID_ProcedimentoPaleo', 'wcf:DS_ID_ProcedimentoPaleo', $this->DS_ID_ProcedimentoPaleo, false, false);
        }
        if ($this->DS_ID_SerieArchivistica) {
            $soapvalArr[] = new soapval('wcf:DS_ID_SerieArchivistica', 'wcf:DS_ID_SerieArchivistica', $this->DS_ID_SerieArchivistica, false, false);
        }
        if ($this->DS_ID_Sistema) {
            $soapvalArr[] = new soapval('wcf:DS_ID_Sistema', 'wcf:DS_ID_Sistema', $this->DS_ID_Sistema, false, false);
        }
        if ($this->DS_ID_TipoFascicolo) {
            $soapvalArr[] = new soapval('wcf:DS_ID_TipoFascicolo', 'wcf:DS_ID_TipoFascicolo', $this->DS_ID_TipoFascicolo, false, false);
        }
        if ($this->DS_ID_TipoProcedimento) {
            $soapvalArr[] = new soapval('wcf:DS_ID_TipoProcedimento', 'wcf:DS_ID_TipoProcedimento', $this->DS_ID_TipoProcedimento, false, false);
        }
        if ($this->DS_LinkModulistica) {
            $soapvalArr[] = new soapval('wcf:DS_LinkModulistica', 'wcf:DS_LinkModulistica', $this->DS_LinkModulistica, false, false);
        }
        if ($this->DS_LinkServizio) {
            $soapvalArr[] = new soapval('wcf:DS_LinkServizio', 'wcf:DS_LinkServizio', $this->DS_LinkServizio, false, false);
        }
        if ($this->DS_ModalitaPagamenti) {
            $soapvalArr[] = new soapval('wcf:DS_ModalitaPagamenti', 'wcf:DS_ModalitaPagamenti', $this->DS_ModalitaPagamenti, false, false);
        }
        if ($this->DS_ModalitaRichiestaInfo) {
            $soapvalArr[] = new soapval('wcf:DS_ModalitaRichiestaInfo', 'wcf:DS_ModalitaRichiestaInfo', $this->DS_ModalitaRichiestaInfo, false, false);
        }
        if ($this->DS_NomeCognomeSostituto) {
            $soapvalArr[] = new soapval('wcf:DS_NomeCognomeSostituto', 'wcf:DS_NomeCognomeSostituto', $this->DS_NomeCognomeSostituto, false, false);
        }
        if ($this->DS_NomeProcedimento) {
            $soapvalArr[] = new soapval('wcf:DS_NomeProcedimento', 'wcf:DS_NomeProcedimento', $this->DS_NomeProcedimento, false, false);
        }
        if ($this->DS_RespProcCognome) {
            $soapvalArr[] = new soapval('wcf:DS_RespProcCognome', 'wcf:DS_RespProcCognome', $this->DS_RespProcCognome, false, false);
        }
        if ($this->DS_RespProcNome) {
            $soapvalArr[] = new soapval('wcf:DS_RespProcNome', 'wcf:DS_RespProcNome', $this->DS_RespProcNome, false, false);
        }
        if ($this->DS_RisorseFinanziarie) {
            $soapvalArr[] = new soapval('wcf:DS_RisorseFinanziarie', 'wcf:DS_RisorseFinanziarie', $this->DS_RisorseFinanziarie, false, false);
        }
        if ($this->DS_UO_CodiceIpaCompetente) {
            $soapvalArr[] = new soapval('wcf:DS_UO_CodiceIpaCompetente', 'wcf:DS_UO_CodiceIpaCompetente', $this->DS_UO_CodiceIpaCompetente, false, false);
        }
        if ($this->DS_UO_CodiceIpaIstruttoria) {
            $soapvalArr[] = new soapval('wcf:DS_UO_CodiceIpaIstruttoria', 'wcf:DS_UO_CodiceIpaIstruttoria', $this->DS_UO_CodiceIpaIstruttoria, false, false);
        }
        if ($this->DS_UO_Competente) {
            $soapvalArr[] = new soapval('wcf:DS_UO_Competente', 'wcf:DS_UO_Competente', $this->DS_UO_Competente, false, false);
        }
        if ($this->DS_UO_CompetenzaIstruttoria) {
            $soapvalArr[] = new soapval('wcf:DS_UO_CompetenzaIstruttoria', 'wcf:DS_UO_CompetenzaIstruttoria', $this->DS_UO_CompetenzaIstruttoria, false, false);
        }
        if ($this->DS_UO_RecapitiIstruttoria) {
            $soapvalArr[] = new soapval('wcf:DS_UO_RecapitiIstruttoria', 'wcf:DS_UO_RecapitiIstruttoria', $this->DS_UO_RecapitiIstruttoria, false, false);
        }
        if (count($soapvalArr) == 1) {
            $MyTipoProEnteSoapval = new soapval('wcf:MyTipoProEnte', 'wcf:MyTipoProEnte', $soapvalArr[0], false, false);
        } else {
            $MyTipoProEnteSoapval = new soapval('wcf:MyTipoProEnte', 'wcf:MyTipoProEnte', $soapvalArr, false, false);
        }
        return $MyTipoProEnteSoapval;
    }

}

?>