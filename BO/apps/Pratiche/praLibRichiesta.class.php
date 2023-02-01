<?php

/**
 *
 * LIBRERIA PER GESTIONE RICHIESTE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    10.01.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');

class praLibRichiesta {

    /**
     * Libreria di funzioni Generiche e Utility per Richieste
     * 
     */
    const RICSTA_INOLTRATA = "01";
    const RICSTA_ACQUISITA_BO = "02";
    const RICSTA_CHIUSA = "03";
    const RICSTA_INOLTRATA_CAMERA_COMMERCIO = "91";
    const RICSTA_ANNULLATA = "98";
    const RICSTA_IN_CORSO = "99";
    const RICSTA_OFFLINE = "OF";
    const RICSTA_PROTOCOLLAZIONE_IN_CORSO = "IM";
    const RICSTA_ATTESA_PROTOCOLLAZIONE_DIFFERITA = "PD";
    const PROVENIENZA_PEC = "daCaricaMail";
    const PROVENIENZA_ANAGRAFICA = "daAnagrafica";
    const PROVENIENZA_PROTOCOLLO = "daProtocollo";

    public static $RICSTA_DESCR = array(
        self::RICSTA_INOLTRATA => 'Inoltrata',
        self::RICSTA_ACQUISITA_BO => 'Acquisita',
        self::RICSTA_CHIUSA => 'Chiusa',
        self::RICSTA_INOLTRATA_CAMERA_COMMERCIO => 'Inviata ad Camera di Commercio',
        self::RICSTA_ANNULLATA => 'Annullata',
        self::RICSTA_IN_CORSO => 'In corso',
        self::RICSTA_OFFLINE => 'Offline',
        self::RICSTA_PROTOCOLLAZIONE_IN_CORSO => 'Protocollazione in corso',
        self::RICSTA_ATTESA_PROTOCOLLAZIONE_DIFFERITA => 'Attesa protocollazione differita',
    );
    private $praLib;
    private $PRAM_DB;
    private $errMessage;
    private $errCode;

    public static function getInstance($ditta = '') {
        $obj = new praLibRichiesta();
        try {
            $obj->praLib = new praLib();
            $obj->PRAM_DB = $obj->praLib->getPRAMDB();
            $obj->workDate = date('Ymd');
            $obj->workYear = date('Y', strtotime($obj->workDate));
        } catch (Exception $e) {
            return false;
        }
        return $obj;
    }

    function __construct($ditta = '') {
        
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    /**
     * 
     * @param array $datiMail array(
      "ELENCOALLEGATI" => allegati della richiesta,
      "DATA" => data ricezione richiesta,
      "ORA" => ora ricezione richiesta,
      "FILENAME" => path del file eml ,
      "IDMAIL" => id univoco mail ,
      "PROGES" => record fascicolo,
      "ANADES" => record soggetto principale,
      "ITEEVT" => record evento procedimento,
      "EscludiPassiFO" => flag che esclude l'acquisizione dei passi front office
      "RESULTREST" => codice procedura,
      "provenienza" => provenienza del tipo di richiesta
      "idDocumento" => id del documento nel protocollo dell'ente (solo se si carica da protocollo),
      "segnatura" => segnatura del protocollo (solo se si carica da protocollo),
      "dataProtocollo" => data del protocollo (solo se si carica da protocollo),
      )
      @return array array(
      "PROGES_REC" => record fascicolo,
      "ANADES_REC" => record soggetto principale,
      "PRORIC_REC" => record richiesta on-line,
      "ITEEVT_REC" => record evento procedimento,
      "XMLINFO" => path del file XMLINFO.xml,
      "ALLEGATI" => allegati della richiesta,
      "ALLEGATICOMUNICA" => allegati infocamere,
      "esterna" => flag che identifica la richiesta come non da portale,
      "tipoInserimento" => tipo di acquisizione,
      "starweb" => flag che identifica la richiesta come proveniente da starweb,
      "EscludiPassiFO" => flag che esclude l'acquisizione dei passi front office,
      "ProgressivoDaRichiesta" => flag che valorizza il numero del fasciolo uguale al numero della richiesta,
      "DatiAssegnazione" => array con dati per assegnare la pratica,
      "MITTDEST" => identifica un passo in arrivo o in partenza,
      "idDocumento" => id del documento nel protocollo dell'ente (solo se si carica da protocollo),
      "segnatura" => segnatura del protocollo (solo se si carica da protocollo),
      "tipoReg" => tipo registrazione,
      )
     */
    public function getDatiRichiesta($datiMail) {
        $tipoReg = "";
        if ($datiMail['PRORIC_REC']) {
            $tipoInserimento = "PECSUAP";
            $esterna = false;
        } else {
            switch ($datiMail['provenienza']) {
                case self::PROVENIENZA_PEC:
                    $tipoInserimento = "PECGENERICA";
                    break;
                case self::PROVENIENZA_ANAGRAFICA:
                    $tipoInserimento = "ANAGRAFICA";
                    break;
                case self::PROVENIENZA_PROTOCOLLO:
                    $tipoInserimento = $tipoReg = "WSPROTOCOLLO";
                    break;
                default:
                    break;
            }

            $esterna = true;
            $iteevt_rec = $this->praLib->GetIteevt($datiMail['ITEEVT']['ROWID'], "rowid");
        }

        $Filent_Rec = $this->praLib->GetFilent(1);
        $ProgressivoDaRichiesta = false;
        if ($Filent_Rec['FILVAL'] == 1) {
            $ProgressivoDaRichiesta = true;
        }

        $proric_parm = array();
        $proric_parm['PRORIC_REC'] = $datiMail['PRORIC_REC'];
        $proric_parm['ELENCOALLEGATI'] = $datiMail['ELENCOALLEGATI'];
        $proric_parm['ALLEGATICOMUNICA'] = $datiMail['ALLEGATICOMUNICA'];

        /*
         * Valorizzo dati richiedente pratica
         */
        $anades_rec = array();
        $anades_rec['DESNOM'] = $proric_parm['PRORIC_REC']['RICCOG'] . " " . $proric_parm['PRORIC_REC']['RICNOM'];
        $anades_rec['DESFIS'] = $proric_parm['PRORIC_REC']['RICFIS'];
        $anades_rec['DESIND'] = $proric_parm['PRORIC_REC']['RICVIA'];
        $anades_rec['DESCAP'] = $proric_parm['PRORIC_REC']['RICCAP'];
        $anades_rec['DESCIT'] = $proric_parm['PRORIC_REC']['RICCOM'];
        $anades_rec['DESPRO'] = $proric_parm['PRORIC_REC']['RICPRV'];
        $anades_rec['DESEMA'] = $proric_parm['PRORIC_REC']['RICEMA'];
        $anades_rec['DESRUO'] = praRuolo::getSystemSubjectCode("ESIBENTE");

        /*
         * Valorizzo dati testata pratica
         */
        $proges_rec = array();
        $proges_rec['GESDRE'] = date('Ymd');
        if ($datiMail['PROGES']['GESDRE']) {
            $proges_rec['GESDRE'] = $datiMail['PROGES']['GESDRE'];
        }

        if (isset($datiMail['ANADES'])) {
            $anades_rec = $datiMail['ANADES'];
        }
        
        if ($proric_parm['PRORIC_REC']['RICPRO']) {
            $proges_rec['GESPRO'] = str_pad($proric_parm['PRORIC_REC']['RICPRO'], 6, "0", STR_PAD_LEFT);
        } else {
            $proges_rec['GESPRO'] = $datiMail['PROGES']['GESPRO'];
        }
        $proges_rec['GESWFPRO'] = $datiMail['PROGES']['GESWFPRO'];
        $proges_rec['GESCODPROC'] = $datiMail['PROGES']['GESCODPROC'];
        $proges_rec['GESNPR'] = $datiMail['PROGES']['GESNPR'];
        $proges_rec['GESPAR'] = $datiMail['PROGES']['GESPAR'];

        /*
         * Usato getAnapra perchè la funzione DecodAnapra con $retid vuoto, restituisce solo il record Anapra
         */
        //$anapra_rec = $this->DecodAnapra($proges_rec['GESPRO'], "", 'codice', $dataReg);
        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);

        $proges_rec['GESPRO'] = $anapra_rec['PRANUM'];
        $proges_rec['GESGIO'] = $anapra_rec['PRAGIO'];
        if ($proric_parm['PRORIC_REC']['RICDAT']) {
            $proges_rec['GESDRI'] = $proric_parm['PRORIC_REC']['RICDAT'];
            $proges_rec['GESORA'] = $proric_parm['PRORIC_REC']['RICTIM'];
        } else if ($datiMail['DATA']) {
            $proges_rec['GESDRI'] = $datiMail['DATA'];
            $proges_rec['GESORA'] = $datiMail['ORA'];
        } else {
            $proges_rec['GESDRI'] = date("Ymd");
            $proges_rec['GESORA'] = date("H:i:s");
        }

        $proges_rec['GESRES'] = $anapra_rec['PRARES'];
        if ($proric_parm['PRORIC_REC']) {
            $proges_rec['GESRES'] = str_pad($proric_parm['PRORIC_REC']['RICRES'], 6, "0", STR_PAD_LEFT);
        } else if ($datiMail['PROGES']['GESRES']) {
            $proges_rec['GESRES'] = $datiMail['PROGES']['GESRES'];
        }

        /*
         * Assegno l'aggregato in base alla richiesta on-line, se non c'è in base alla visibilità dell'utente loggato
         */
        if ($proric_parm['PRORIC_REC']['RICSPA']) {
            $proges_rec['GESSPA'] = $proric_parm['PRORIC_REC']['RICSPA'];
        } else {
            $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
            $proges_rec['GESSPA'] = $ananom_rec['NOMSPA'];
        }

        /*
         * Se c'è il codice procedura ed è stato scelto il responsabile, lo assegno
         * così come GESDRI, GESORA e GESSPA preso dalla visibilità dell'utente loggato
         */
        if ($proges_rec['GESCODPROC']) {
            $proges_rec['GESDRI'] = date("Ymd");
            $proges_rec['GESORA'] = date("H:i:s");
        }

        $filent_valoResp_rec = $this->praLib->GetFilent(19);
        if ($filent_valoResp_rec["FILVAL"] == 1) {
            $proges_rec['GESRES'] = "";
        }

        $proges_rec['GESPRA'] = $proric_parm['PRORIC_REC']['RICNUM'];


        /*
         * Ribalto il protocollo se c'è
         */
        if ($proric_parm['PRORIC_REC']['RICNPR'] != 0) {
            $proges_rec['GESNPR'] = $proric_parm['PRORIC_REC']['RICNPR'];
            $proges_rec['GESPAR'] = "A";
            $proges_rec['GESMETA'] = $proric_parm['PRORIC_REC']['RICMETA'];
        }

        /*
         * Assegno il codice evento e segnalazione comunica per statistiche
         */
        $proges_rec['GESEVE'] = $proric_parm['PRORIC_REC']['RICEVE'];
        $proges_rec['GESSEG'] = $proric_parm['PRORIC_REC']['RICSEG'];

        /*
         * Identifico la presenza del file xmlifo.xml
         */
        $fileXML = "";
        foreach ($proric_parm['ELENCOALLEGATI'] as $allegato) {
            if (strpos($allegato['FILENAME'], 'XMLINFO.xml') !== false) {
                $fileXML = $allegato['DATAFILE'];
                $tipoReg = "consulta";
                break;
            }
        }

        /*
         * Inizializzo indicatore staweb (infocamere)
         */
        $starweb = false;
        foreach ($proric_parm['ALLEGATICOMUNICA'] as $allegato) {
            if (strpos(strtolower($allegato['FILENAME']), 'suap.pdf.p7m') !== false) {
                $starweb = true;
                $tipoReg = "infocamere";
                break;
            }
        }

        /*
         * Data Model per il caricamento del fascicolo
         * 
         */
        return array(
            "PROGES_REC" => $proges_rec,
            "ANADES_REC" => $anades_rec,
            "PRORIC_REC" => $proric_parm['PRORIC_REC'],
            "ITEEVT_REC" => $iteevt_rec,
            "PRODAG_REC" => $datiMail['PRODAG'],
            "XMLINFO" => $fileXML,
            "ALLEGATI" => $proric_parm['ELENCOALLEGATI'],
            "ALLEGATIACCORPATE" => $this->praLib->GetAllegatiAccorpate($fileXML),
            "ALLEGATICOMUNICA" => $proric_parm['ALLEGATICOMUNICA'],
            "esterna" => $esterna,
            "tipoInserimento" => $tipoInserimento,
            "starweb" => $starweb,
            "EscludiPassiFO" => $datiMail['EscludiPassiFO'],
            "ProgressivoDaRichiesta" => $ProgressivoDaRichiesta,
            "DatiAssegnazione" => $datiMail['Assegnazione'],
            "MITTDEST" => $datiMail['MITTDEST'],
            "idDocumento" => $datiMail['idDocumento'],
            "segnatura" => $datiMail['segnatura'],
            "tipoReg" => $tipoReg,
            "PRAFOLIST_REC" => $datiMail['PRAFOLIST_REC'],
            "CALLBACK_PARAMS" => $datiMail['CALLBACK_PARAMS'],
            "CALLBACK" => $datiMail['CALLBACK'],
        );
    }

    public function getSoggettiRichiesta($ricdag_tab) {
        $arrAggiuntivi = array();
        foreach ($ricdag_tab as $ricdag_rec) {
            if ($ricdag_rec['DAGKEY'] == "DICHIARANTE_COGNOME" && substr($ricdag_rec['DAGSET'], -2) == "01" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['DICHIARANTE']['COGNOME'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "DICHIARANTE_NOME" && substr($ricdag_rec['DAGSET'], -2) == "01" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['DICHIARANTE']['NOME'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI" && substr($ricdag_rec['DAGSET'], -2) == "01" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['DICHIARANTE']['FISCALE'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "DICHIARANTE_QUALIFICA" && substr($ricdag_rec['DAGSET'], -2) == "01" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['DICHIARANTE']['QUALIFICA'] = $ricdag_rec['RICDAT'];
            }
        }

        foreach ($ricdag_tab as $ricdag_rec) {
            if ($ricdag_rec['DAGKEY'] == "IMPRESA_RAGIONESOCIALE" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['IMPRESA']['DENOMINAZIONE'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "IMPRESA_SEDEVIA" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['IMPRESA']['VIA'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "IMPRESA_SEDECIVICO" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['IMPRESA']['CIVICO'] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "IMPRESA_SEDECAP" && $ricdag_rec['RICDAT']) {
                $arrAggiuntivi['IMPRESA']['CAP'] = $ricdag_rec['RICDAT'];
            }
        }

        if ($arrAggiuntivi['IMPRESA']['DENOMINAZIONE'] == "") {
            foreach ($ricdag_tab as $ricdag_rec) {
                if ($ricdag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_RAGIONESOCIALE" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['DENOMINAZIONE'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['VIA'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['CIVICO'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECAP" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['CAP'] = $ricdag_rec['RICDAT'];
                }
            }
        }

        if ($arrAggiuntivi['IMPRESA']['DENOMINAZIONE'] == "") {
            foreach ($ricdag_tab as $ricdag_rec) {
                if ($ricdag_rec['DAGTIP'] == "DenominazioneImpresa" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['DENOMINAZIONE'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGTIP'] == "Indir_InsProduttivo" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['VIA'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGTIP'] == "Civico_InsProduttivo" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['CIVICO'] = $ricdag_rec['RICDAT'];
                }
                if ($ricdag_rec['DAGTIP'] == "Cap_InsProduttivo" && $ricdag_rec['RICDAT']) {
                    $arrAggiuntivi['IMPRESA']['CAP'] = $ricdag_rec['RICDAT'];
                }
            }
        }
        return $arrAggiuntivi;
    }

}
