<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    proConservazioneManager
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    16.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFascicoloArch.class.php';
include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class proConservazioneManager {
    /*
     * Esiti Versamento
     */

    const ESITO_POSTITIVO = 'POSITIVO';
    const ESITO_WARNING = 'WARNING';
    const ESITO_NEGATIVO = 'NEGATIVO';
    /*
     * Error Code
     */
    const ERR_CODE_SUCCESS = 0;
    const ERR_CODE_FATAL = -1;
    const ERR_CODE_QUESTION = -2;
    const ERR_CODE_INFO = -3;
    const ERR_CODE_WARNING = -4;
    /*
     * Chiavi Esiti Versamento
     */
    const CHIAVE_ESITO_CONSERVATORE = 'Conservatore';
    const CHIAVE_ESITO_VERSIONE = 'Versione';
    const CHIAVE_ESITO_DATAVERSAMENTO = 'DataVersamento';
    const CHIAVE_ESITO_UTENTEVERSAMENTO = 'UtenteVersamento';
    const CHIAVE_ESITO_ESITO = 'Esito';
    const CHIAVE_ESITO_FILE = 'FileEsito';
    const CHIAVE_ESITO_FILE_RICHIESTA = 'FileRichiesta';
    const CHIAVE_ESITO_CODICEERRORE = 'CodiceErrore';
    const CHIAVE_ESITO_MESSAGGIOERRORE = 'MessaggioErrore';
    const CHIAVE_ESITO_CHIAVEVERSAMENTO = 'ChiaveVersamento';
    const CHIAVE_ESITO_IDVERSAMENTO = 'IdSIP';
    const CHIAVE_ESITO_ALLEGATINONCONSERV = 'AllegatoNonConservabile';
    const CHIAVE_NOTECONSERV = 'NoteConservazione';
    const CHIAVE_ESITO_IDPENDINGREQUEST = 'IDPendingRequest';

    /*
     * Motivi Versamento
     */
    const MOTIVO_VERSAMENTO = 'VERSAMENTO';
    const MOTIVO_MODIFICA = 'MODIFICA';
    const MOTIVO_ANNULLAMENTO = 'ANNULLAMENTO';

    /*
     * Parametri generali
     */

    protected $parametri = array();
    protected $parametriManager = array();

    /*
     * Variabili di controllo esito operazioni
     */
    protected $errCode;
    protected $errMessage;
    protected $retStatus;
    protected $retEsito;

    /*
     * Librerie
     */
    protected $proLib;
    protected $devLib;
    protected $proLibAllegati;
    protected $eqAudit;
    protected $logger;
    protected $accLib;

    /*
     * Variabili Varie
     */
    protected $rowidAnapro;
    protected $anapro_rec;
    protected $dataVersamento = null;
    protected $oraVersamento = null;
    protected $unitaDocumentaria;
    protected $baseDati = array();
    protected $xmlResponso;
    protected $xmlRichiesta;
    protected $datiMinimiEsitoVersamento = array();
    protected $LastNomeFileRichiesta;
    protected $LastNomeFileResponso;
    protected $AllegatiNonConservati;

    /*
     * Variabili RDV
     */
    protected $RDVFile;
    protected $RDVXml;
    protected $RDVArrayXml = array();
    protected $LastNomeFileRDV;

    public function __construct($parametri) {
        $this->proLib = new proLib();
        $this->devLib = new devLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibFascicolo = new proLibFascicolo();
        $this->parametri = $parametri;
        $this->eqAudit = new eqAudit();
        $this->accLib = new accLib();
        $this->InizializzaLogger();
    }

    function getRDVFile() {
        return $this->RDVFile;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getRetStatus() {
        return $this->retStatus;
    }

    public function getRetEsito() {
        return $this->retEsito;
    }

    public function setRowidAnapro($rowidAnapro) {
        $this->rowidAnapro = $rowidAnapro;
    }

    public function setAnapro_rec($anapro_rec) {
        $this->anapro_rec = $anapro_rec;
    }

    public function setUnitaDocumentaria($unitaDocumentaria) {
        $this->unitaDocumentaria = $unitaDocumentaria;
    }

    public function setLogger($logger) {
        $this->logger = $logger;
    }

    public function setDataVersamento($dataVersamento) {
        $this->dataVersamento = $dataVersamento;
    }

    public function setOraVersamento($oraVersamento) {
        $this->oraVersamento = $oraVersamento;
    }

    public function getBaseDati() {
        $this->getBaseDatiUnitaDocumentarie();
        return $this->baseDati;
    }

    public function getAllegatiNonConservati() {
        return $this->AllegatiNonConservati;
    }

    public function setAllegatiNonConservati($AllegatiNonConservati) {
        $this->AllegatiNonConservati = $AllegatiNonConservati;
    }

    protected function InizializzaLogger() {
        $FilePathLog = $this->proLib->GetFilePathLogConservazione();
        $this->logger = new itaPHPLogger('proConservazioneManager', false);
        $this->logger->pushEqAudit($this);
        $this->logger->pushFile($FilePathLog);
    }

    /**
     * Inizzializzazione variabili
     */
    public function InizializzaEsiti() {
        $this->errCode = self::ERR_CODE_SUCCESS;
        $this->errMessage = "";
        $this->retStatus = "";
        $this->retEsito = null;
        $this->AllegatiNonConservati = array();
        $this->baseDati = array();
    }

    protected function conservaAnapro() {
        /*
         * Inizializza esiti
         */
        $this->logger->info('Inizializzo gli Esiti per la Conservazione. Prot: ' . $this->anapro_rec['PRONUM'] . $this->anapro_rec['PROPAR']);
        $this->InizializzaEsiti();

        /*
         * Prerequisiti generali convervarione 
         */
        if (!$this->ControllaPrerequisitiConservazione()) {
            return false;
        }
        return true;
    }

    /**
     * 
     */
    public function ControllaPrerequisitiConservazione() {
        /*
         * Controllo Presenza Rowid Protocollo
         */
        if ($this->rowidAnapro == '' || $this->rowidAnapro == 'null') {
            $this->errMessage = self::ERR_CODE_FATAL;
            $this->errMessage = "Occorre selezionare il documento da conservare.";
            return false;
        }
        /*
         * Lettura record del protocollo
         */
        $Anapro_rec = $this->proLib->GetAnapro($this->rowidAnapro, 'rowid');
        if (!$Anapro_rec) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Documento da versare non accessibile.";
            return false;
        }
        /*
         * Setto il record del protocollo.
         */
        $this->setAnapro_rec($Anapro_rec);
        return true;
    }

    protected function getBaseDatiUnitaDocumentarie() {
        $this->logger->info('Carico il dizionario Base per la Conservazione');
        $baseDati = proConservazioneManagerHelper::getBaseDatiUnitaDocumentarie($this->rowidAnapro, $this->dataVersamento, $this->oraVersamento);
        if (!$baseDati) {
            $this->errCode = proConservazioneManagerHelper::getLastErrorCode();
            $this->errMessage = proConservazioneManagerHelper::getLastErrorMessage();
            return false;
        }

        if (!proConservazioneManagerHelper::getRisorseAnadoc($baseDati)) {
            $this->errCode = proConservazioneManagerHelper::getLastErrorCode();
            $this->errMessage = proConservazioneManagerHelper::getLastErrorMessage();
            return false;
        }
        if (!proConservazioneManagerHelper::getRisorseMail($baseDati)) {
            $this->errCode = proConservazioneManagerHelper::getLastErrorCode();
            $this->errMessage = proConservazioneManagerHelper::getLastErrorMessage();
            return false;
        }
        $this->AllegatiNonConservati = proConservazioneManagerHelper::getAllegatiNonConservabili();
        $this->baseDati = $baseDati;
        return true;
    }

    /**
     * Funzione per decodifica classificazione protocollo.
     * @param type $Orgccf
     * @return string
     */
    public function DecodClassifica($Orgccf) {
        $classificazione = '';
        if (strlen($Orgccf) === 4) {
            $classificazione = intval($Orgccf);
        } else if (strlen($Orgccf) === 8) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4));
        } else if (strlen($Orgccf) === 12) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4)) . '.' . intval(substr($Orgccf, 8, 4));
        }
        return $classificazione;
    }

    private function GetEstensione($NomeFile) {
        // SEMPLIFICARE?
        $Estensione = '';
        $Ctr = 1;
        while (true) {
            $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            if ($ext == '' || $Ctr == 3) {
                break;
            }
            $Estensione = '.' . $ext . $Estensione;
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    public function ProteggiCarattteri($Stringa) {
        return htmlspecialchars($Stringa, ENT_COMPAT);
    }

    /**
     * Storicizzo la tabella PROCONSER
     * 
     * @param type $rowid_anapro
     */
    public function StoricizzaProconser($NoteProConser = '') {
        $sql = "SELECT * FROM PROCONSER WHERE ROWID_ANAPRO = {$this->rowidAnapro} AND FLSTORICO = 0";
        $Proconser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        /* Se storico non è presente: non c'è da storicizzare nulla */
        if (!$Proconser_rec) {
            return true;
        }
        try {
            $Proconser_rec['FLSTORICO'] = 1;
            $Proconser_rec['NOTECONSER'] = $NoteProConser;
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROCONSER', 'ROWID', $Proconser_rec);
            return true;
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in aggiornamento PROCONSER.<br> " . $e->getMessage();
            return false;
        }
    }

    /**
     * Storicizzo la tabella PROUPDATECONSER
     * 
     * @param type $rowid_anapro
     */
    public function StoricizzaProUpdateConser() {
        $sql = "SELECT * FROM PROUPDATECONSER WHERE ROWID_ANAPRO = {$this->rowidAnapro} AND UPDATETIPO = '{$this->unitaDocumentaria}' AND FLESEGUITO = 0";
        $Proupdateconser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        $arrTabdag = $this->datiMinimiEsitoVersamento;
        /* Se storico non è presente: non c'è da storicizzare nulla */
        if (!$Proupdateconser_rec) {
            return true;
        }
        try {
            $Proupdateconser_rec['FLESEGUITO'] = 1;
            $Proupdateconser_rec['CHIAVEVERSAMENTO'] = $arrTabdag[self::CHIAVE_ESITO_CHIAVEVERSAMENTO];
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROUPDATECONSER', 'ROWID', $Proupdateconser_rec);
            return true;
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in aggiornamento PROUPDATECONSER.<br> " . $e->getMessage();
            return false;
        }
    }

    protected function SalvaEsitoConservazione() {

        /*
         *  Preparazione array metadati:
         */
        $arrTabdag = $this->datiMinimiEsitoVersamento;
        /* Aggiungo il nome file esito relativo */
        $arrTabdag[self::CHIAVE_ESITO_FILE] = $this->LastNomeFileResponso;
        $arrTabdag[self::CHIAVE_ESITO_FILE_RICHIESTA] = $this->LastNomeFileRichiesta;
        $arrTabdag[self::CHIAVE_ESITO_UTENTEVERSAMENTO] = App::$utente->getKey('nomeUtente');
        /*
         * Allegati non conservabili:
         */
        foreach ($this->AllegatiNonConservati as $key => $Allegato) {
            $arrTabdag[self::CHIAVE_ESITO_ALLEGATINONCONSERV . '_' . $key] = $Allegato;
        }

        $this->retEsito = $arrTabdag[self::CHIAVE_ESITO_ESITO];
        $this->retStatus = $arrTabdag[self::CHIAVE_ESITO_MESSAGGIOERRORE];
        /* Salvataggio dei metadati */
        if (!$this->SalvaMetadatiConservazione($arrTabdag)) {
            return false;
        }

        return true;
    }

    public function SalvaMetadatiConservazione($arrTabdag = array()) {
        $ProConser_rec = array();
        $ProConser_rec['PRONUM'] = $this->anapro_rec['PRONUM'];
        $ProConser_rec['PROPAR'] = $this->anapro_rec['PROPAR'];
        $ProConser_rec['ROWID_ANAPRO'] = $this->anapro_rec['ROWID'];
        /*
         * Estrazione Fonte Dati e Valori
         */
        $ProConser_rec['PROGVERSAMENTO'] = $this->GetProgressivoConservazione();
        $ProConser_rec['DATAVERSAMENTO'] = date("Ymd", strtotime($arrTabdag[self::CHIAVE_ESITO_DATAVERSAMENTO]));
        $ProConser_rec['ORAVERSAMENTO'] = date("H:i:s");
        $ProConser_rec['MOTIVOVERSAMENTO'] = self::MOTIVO_VERSAMENTO;
        $ProConser_rec['ESITOVERSAMENTO'] = $arrTabdag[self::CHIAVE_ESITO_ESITO]; //Corretto? o da rielaborare?
        $ProConser_rec['DOCVERSAMENTO'] = $arrTabdag[self::CHIAVE_ESITO_FILE_RICHIESTA];
        $ProConser_rec['DOCESITO'] = $arrTabdag[self::CHIAVE_ESITO_FILE];
        //$ProConser_rec['COD_UNITA_DOCUMENTARIA'] = 'STRG'; //!!!!!!??????
        $ProConser_rec['COD_UNITA_DOCUMENTARIA'] = $this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC];
        $ProConser_rec['CONSERVATORE'] = $arrTabdag[self::CHIAVE_ESITO_CONSERVATORE];
        $ProConser_rec['VERSIONE'] = $arrTabdag[self::CHIAVE_ESITO_VERSIONE];
        $ProConser_rec['CODICEERRORE'] = $arrTabdag[self::CHIAVE_ESITO_CODICEERRORE];
        $ProConser_rec['MESSAGGIOERRORE'] = $arrTabdag[self::CHIAVE_ESITO_MESSAGGIOERRORE];
        $ProConser_rec['CHIAVEVERSAMENTO'] = $arrTabdag[self::CHIAVE_ESITO_CHIAVEVERSAMENTO];
        $ProConser_rec['UUIDSIP'] = $arrTabdag[self::CHIAVE_ESITO_IDVERSAMENTO];
        $ProConser_rec['PENDINGUUID'] = $arrTabdag[self::CHIAVE_ESITO_IDPENDINGREQUEST];
        $ProConser_rec['UTENTEVERSAMENTO'] = $arrTabdag[self::CHIAVE_ESITO_UTENTEVERSAMENTO];
        $ProConser_rec['FLSTORICO'] = 0;
        $ProConser_rec['NOTECONSER'] = $arrTabdag[self::CHIAVE_NOTECONSERV];

        // Controllo se è già presente il record?
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROCONSER', 'ROWID', $ProConser_rec);
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in inserimento PROCONSER." . $e->getMessage();
            return false;
        }
        // Salvo la fonte dati:
        $proLibTabDag = new proLibTabDag();
        if (!$proLibTabDag->SalvataggioFonteTabdag("ANAPRO", $this->anapro_rec['ROWID'], proLibConservazione::FONTE_DATI_ESITO_CONSERVAZIONE, $arrTabdag, 0, true)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'Salvataggio dati aggiuntivi Esito fallito: ' . $proLibTabDag->getErrMessage();
            return false;
        }
        return true;
    }

    protected function GetProgressivoConservazione() {
        $sql = "SELECT MAX(PROGVERSAMENTO) AS MAXPROG FROM PROCONSER WHERE PRONUM = " . $this->anapro_rec['PRONUM'] . " AND PROPAR =  '" . $this->anapro_rec['PROPAR'] . "' ";
        $ProconserMax_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        $Progressivo = $ProconserMax_rec['MAXPROG'] + 1;
        return $Progressivo;
    }

    protected function GetAnadocDaConservare() {
        /*
         * Estratto i documenti
         * @TODO Criteri di estrazione.! [per ora non quelli di servizio, ma accettazioni e consegne dovrebbero.
         */
        $where = ' AND DOCSERVIZIO=0 ORDER BY DOCTIPO ASC,ROWID ASC';
        $Anadoc_tab = $this->proLib->GetAnadoc($this->anapro_rec['PRONUM'], 'protocollo', true, $this->anapro_rec['PROPAR'], $where);

        $subPath = "proDocAllegati-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        if (!$tempPath) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'Errore in creazione cartella temporanea.';
            return false;
        }

        /*
         * Allegati e Doc principale:
         */
        $AnadocTab = array();
        $Principale = false;
        foreach ($Anadoc_tab as $key => $Anadoc_rec) {
            // PREPARO ANADOC
            $hashVersato = $this->proLibAllegati->GetHashDocAllegato($Anadoc_rec['ROWID'], 'sha256');
            if (!$hashVersato) {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $this->proLibAllegati->getErrMessage();
                return false;
            }

            $Anadoc_rec['DOCNAME'] = $this->ProteggiCarattteri($Anadoc_rec['DOCNAME']);
            // Filepath:
            $filecontent = $this->proLibAllegati->CopiaDocAllegato($Anadoc_rec['ROWID'], $tempPath . '/' . $Anadoc_rec['DOCFIL']);
            if (!$filecontent) {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $this->proLibAllegati->getErrMessage();
                return false;
            }
            /*
             *  Nuove Chiavi:
             */
            $Anadoc_rec['HASHFILE'] = $hashVersato;
            $Anadoc_rec['FILEPATH'] = $filecontent;
            $Anadoc_rec['ESTENSIONE'] = $this->GetEstensione($Anadoc_rec['DOCNAME']);

            /* Controlli se Principale o Allegato */
            if ($Anadoc_rec['DOCTIPO'] == '') {
                /* Controllo presenza più allegati principali: */
                if ($Principale == true) {
                    /* Principale già presente: il secondo è un Allegato. */
                    $Anadoc_rec['DOCTIPO'] = 'ALLEGATO';
                } else {
                    /* E' il Principale */
                    $Principale = true;
                    $Anadoc_rec['DOCTIPO'] = 'PRINCIPALE';
                }
            }
            /* Aggiungo A AnadocTab */
            $AnadocTab[] = $Anadoc_rec;
        }
        /* Controllo mancanza Doc Principale */
        if ($Principale == false) {
            if ($AnadocTab) {
                $AnadocTab[0]['DOCTIPO'] = 'PRINCIPALE';
            }
        }
        return $AnadocTab;
    }

    public function extractRDVXml() {
//        $time1 = microtime(true);
        if (!$this->RDVFile) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "RDV file non scaricaro";
            return false;
        }
        if (!file_exists($this->RDVFile)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "RDV file non accessibile";
            return false;
        }

        $p7m = itaP7m::getP7mInstance($this->RDVFile, false);
        if (!$p7m) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Verifica File Firmato Fallita";
            return false;
        }
        if (!file_exists($p7m->getContentFileName())) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore nella estrazione file dal p7m.";
            return false;
        }
        $ContentP7m = file_get_contents($p7m->getContentFileName());
        $p7m->cleanData();
//        $time2 = microtime(true);
//        $delta = $time2 - $time1;
        return $ContentP7m;

        /*
         * Sanitize Sosepso:
         *
         * $string = substr($stringP7m, strpos($stringP7m, '<?xml '));
          // skip everything after the XML content
          $matches = array();
          preg_match_all('/<\/.+?>/', $string, $matches, PREG_OFFSET_CAPTURE);
          $lastMatch = end($matches[0]);

          $utf8_string = $this->sanitizeXML(substr($string, 0, strlen($lastMatch[0]) + $lastMatch[1]));
          return $utf8_string;
         *  */
    }

    /**
     * Removes invalid characters from a UTF-8 XML string
     *
     * @access public
     * @param string a XML string potentially containing invalid characters
     * @return string
     */
    function sanitizeXML($string) {
        if (!empty($string)) {
            file_put_contents("C:\works\sanitized_iniziale.xml", $string);
            $regex = '/(
            [\xC0-\xC1] # Invalid UTF-8 Bytes
            | [\xF5-\xFF] # Invalid UTF-8 Bytes
            | \xE0[\x80-\x9F] # Overlong encoding of prior code point
            | \xF0[\x80-\x8F] # Overlong encoding of prior code point
            | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
            | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
            | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
            | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
            | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
            | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
            | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
            | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
        )/x';
            $string = preg_replace($regex, '', $string);
            file_put_contents("C:\works\sanitized_intermedio.xml", $string);
            $result = "";
            $current;
            $length = strlen($string);
            for ($i = 0; $i < $length; $i++) {
                $current = ord($string{$i});
                if (($current == 0x9) ||
                        ($current == 0xA) ||
                        ($current == 0xD) ||
                        (($current >= 0x20) && ($current <= 0xD7FF)) ||
                        (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                        (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                    $result .= chr($current);
                } else {
                    $ret;    // use this to strip invalid character(s)
                    // $ret .= " ";    // use this to replace them with spaces
                }
            }
            $string = $result;
        }
        file_put_contents('C:/Works/sanitized_finale.xml', $string);
        return $string;
    }

}
