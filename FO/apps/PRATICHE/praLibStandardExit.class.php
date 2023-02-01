<?php

class praLibStandardExit {

//    const CUSTOM_ROOT = "customClass";
//
//    /*
//     * Azioni
//     */
//    const AZIONE_PRE_ISTANZIA_RICHIESTA = "PRE_ISTANZIA_RICHIESTA";
//    const AZIONE_POST_ISTANZIA_RICHIESTA = "POST_ISTANZIA_RICHIESTA";
//    const AZIONE_PRE_INOLTRA_RICHIESTA = "PRE_INOLTRA_RICHIESTA";
//    const AZIONE_POST_INOLTRA_RICHIESTA = "POST_INOLTRA_RICHIESTA";
//    const AZIONE_PRE_PROTOCOLLAZIONE_RICHIESTA = "PRE_PROTOCOLLAZIONE_RICHIESTA";
//    const AZIONE_POST_PROTOCOLLAZIONE_RICHIESTA = "POST_PROTOCOLLAZIONE_RICHIESTA";

    const FUN_FO_ANA_SOGGETTO = "FO_ANA_SOGGETTO";
    const FUN_FO_DATI_CATASTALI = "FO_DATI_CATASTALI";
    const FUN_FO_PASSO_CARTELLA = "FO_PASSO_CARTELLA";
    const FUN_FO_PASSO_MPAY = "FO_PASSO_MPAY";
    const FUN_FO_PASSO_ACC_DOMANDA = "FO_PASSO_ACC_DOMANDA";
    const FUN_FO_PASSO_ACC_SCELTA = "FO_PASSO_ACC_SCELTA";
    const FUN_FO_PASSO_PAGOPA = "FO_PASSO_PAGOPA";

    static $FUNZIONI_FRONT_OFFICE = array(
        self::FUN_FO_ANA_SOGGETTO => array(
            'DESCRIZIONE' => "Raccolta dati Angrafica Soggetto",
            'METADATI' => array(
                "PREFISSO_CAMPI" => "Prefisso Nomi campi Anagrafici",
                "CAMPO_RUOLO" => "Nome del campo Ruolo"
            )
        ),
        self::FUN_FO_DATI_CATASTALI => array(
            'DESCRIZIONE' => "Raccolta dati Catastali",
            'METADATI' => array(
                "CONTROLLO_DATI_DA_WS" => "Controllo dati catastali da QWS",
                "DEFAULT_TIPOLOGIA" => "Tipologia dato (Terreno/Fabbricato)",
            )
        ),
        self::FUN_FO_PASSO_CARTELLA => array(
            'DESCRIZIONE' => "Passo upload Cartella",
            'METADATI' => array()
        ),
        self::FUN_FO_PASSO_MPAY => array(
            'DESCRIZIONE' => "Passo Pagamento MPAY",
            'METADATI' => array(
                'ISTANZA_PAR' => "Codice Istanza Parametri Generali",
                'TIPO_UFFICIO' => "Tipo Ufficio",
                'CODICE_UFFICIO' => "Codice Ufficio",
                'TIPOLOGIA_SERVIZIO' => "Tipologia Servizio"
            )
        ),
        self::FUN_FO_PASSO_ACC_DOMANDA => array(
            'DESCRIZIONE' => 'Passo domanda accorpamento',
            'METADATI' => array()
        ),
        self::FUN_FO_PASSO_ACC_SCELTA => array(
            'DESCRIZIONE' => 'Passo scelta pratica principale',
            'METADATI' => array()
        ),
        self::FUN_FO_PASSO_PAGOPA => array(
            'DESCRIZIONE' => "Passo Pagamento MPAY",
            'METADATI' => array(
                'TIPOLOGIA_SERVIZIO' => "Tipologia Servizio"
            )
        )
    );

    /*
     * Azioni Passo
     */

    const AZIONE_PRE_SUBMIT_RACCOLTA = 'PRE_SUBMIT_RACCOLTA';
    const AZIONE_POST_SUBMIT_RACCOLTA = 'POST_SUBMIT_RACCOLTA';
    const AZIONE_PRE_RENDER_RACCOLTA = 'PRE_RENDER_RACCOLTA';
    const AZIONE_POST_RENDER_RACCOLTA = 'POST_RENDER_RACCOLTA';
    const AZIONE_PRE_RENDER_PASSO = "PRE_RENDER_PASSO";
    const AZIONE_POST_RENDER_PASSO = "POST_RENDER_PASSO";


    /*
     * Risultato Azioni
     */
    const AZIONE_RESULT_CONTINUE = 'CONT';
    const AZIONE_RESULT_STOP = 'STOP';
    const AZIONE_RESULT_WARNING = 'WARN';
    const AZIONE_RESULT_ERROR = 'ERR';
    const AZIONE_RESULT_INVALID = 'INV';
    const AZIONE_RESULT_SUCCESS = 'SUCC';

    private $praLib;
    private $frontOfficeLib;
    private $errCode;
    private $errMessage;

    public static function getInstance($praLib) {
        $obj = new praLibStandardExit();
        $obj->setPraLib($praLib);
        return $obj;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function setFrontOfficeLib($frontOfficeLib) {
        $this->frontOfficeLib = $frontOfficeLib;
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

    public function getFunzioneTipoPasso($codiceAzione, $dati, $risorse = array()) {
        /*
         * Clean errore status
         */
        $this->setErrCode(0);
        $this->setErrMessage('');

        if ($dati['Praclt_rec']['CLTOPEFO']) {
            $metaDati = unserialize($dati['Praclt_rec']['CLTMETA']);
            require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/standardExit/praFunzionePassi.class.php');
            $class = praFunzionePassi::getInstance($dati['Praclt_rec']['CLTOPEFO']);
            if (!is_object($class)) {
                return self::AZIONE_RESULT_SUCCESS;
            }
            $retAzione = $class->eseguiAzione($codiceAzione, $dati, $metaDati, $risorse);
            $this->setErrMessage($class->getErrMessage());
            return $retAzione;
        }
    }

}
