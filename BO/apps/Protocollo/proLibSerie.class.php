<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    01.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proLibSerie {

    public $proLib;
    private $errCode;
    private $errMessage;

    
    
    const PANEL_DATI_PRINCIPALI = "0";
    const PANEL_CATASTALI = "1";
    const PANEL_PASSI = "2";
    const PANEL_ALLEGATI = "3";
    const PANEL_DATI_AGGIUNTIVI = "4";
    const PANEL_COMUNICAZIONE = "5";
    const PANEL_NOTE = "6";
    const PANEL_ACCORPATI = "7";
    const PANEL_ASSEGNAZIONI = "8";
    const PANEL_PAGAMENTI = "9";
    
    const PANEL_FIELD_DESCRIZIONE = "DESCRIZIONE";
    const PANEL_FIELD_FILE_XML = "FILE_XML";
    const PANEL_FIELD_SUB_FORM = "SUB_FORM";
    const PANEL_ID_ELEMENT = "ID_ELEMENT";
    const PANEL_ID_FLAG = "ID_FLAG";
    const PANEL_FIELD_DEF_SEQ = "EF_SEQ";
    const PANEL_FIELD_DEF_STATO = "DEF_STATO";
    const PANEL_PROPR_STATO = "STATO";
    
    public static $PANEL_LIST = array(
        self::PANEL_DATI_PRINCIPALI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Dati Principali",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneDati",
            self::PANEL_FIELD_DEF_SEQ => "00",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_CATASTALI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Dati Catastali",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneCatastali",
            self::PANEL_FIELD_DEF_SEQ => "10",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_PASSI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Passi",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "panePassi",
            self::PANEL_FIELD_DEF_SEQ => "20",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ALLEGATI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Allegati",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneAllegati",
            self::PANEL_FIELD_DEF_SEQ => "30",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_DATI_AGGIUNTIVI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Dati Aggiunti",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneAggiuntivi",
            self::PANEL_FIELD_DEF_SEQ => "40",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_COMUNICAZIONE => array(
            self::PANEL_FIELD_DESCRIZIONE => "Comunicazione",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneComunicazioni",
            self::PANEL_FIELD_DEF_SEQ => "50",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_NOTE => array(
            self::PANEL_FIELD_DESCRIZIONE => "Note",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneNote",
            self::PANEL_FIELD_DEF_SEQ => "60",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ACCORPATI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Procedimenti Accorpati",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneAccorpati",
            self::PANEL_FIELD_DEF_SEQ => "70",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ASSEGNAZIONI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Assegnazioni",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "praTabAssegnazioni",
            self::PANEL_FIELD_DEF_SEQ => "80",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_PAGAMENTI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Gestione Pagamenti",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "praTabPagamenti",
            self::PANEL_FIELD_DEF_SEQ => "90",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
    );
    
    
    
    function __construct() {
        $this->proLib = new proLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function GetProgSerie($codice, $anno = null) {
        if ($anno === null) {
            $sql = "SELECT * FROM PROGSERIE WHERE CODICE=$codice";
            $multi = true;
        } else {
            $sql = "SELECT * FROM PROGSERIE WHERE CODICE=$codice AND ANNO=$anno";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    public function GetSerie($codice, $tipo = 'rowid', $multi = false) {
        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM ANASERIEARC WHERE CODICE = '" . $codice . "'";
                break;
            case 'sigla':
                $sql = "SELECT * FROM ANASERIEARC WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($codice) . "'";
                break;
            case'rowid':
            default :
                $sql = "SELECT * FROM ANASERIEARC WHERE ROWID = $codice ";
                break;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    public function GetConnSerie($Codice, $CodiceTitolario, $Versione_T, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM CONNSERIEARC WHERE CODICESERIE = $Codice AND ORGCCF = '$CodiceTitolario' AND VERSIONE_T = '$Versione_T' ";
        } else {
            $sql = "SELECT * FROM CONNSERIEARC WHERE ROWID = $Codice ";
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function AggiungiSerieATitolario($CodiceSerie, $CodiceTitolario, $Versione_T) {
        if (!$CodiceSerie || !$CodiceTitolario) {
            $this->setErrMessage('Indicare Codice Serie e Titolario.');
            return false;
        }

        $CheckSerie_rec = $this->GetConnSerie($CodiceSerie, $CodiceTitolario, $Versione_T);
        if ($CheckSerie_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Serie già presente per il titolario indicato.');
            return false;
        }
        $ConnSerieArc_rec = array();
        $ConnSerieArc_rec['CODICESERIE'] = $CodiceSerie;
        $ConnSerieArc_rec['ORGCCF'] = $CodiceTitolario;
        $ConnSerieArc_rec['VERSIONE_T'] = $Versione_T;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'CONNSERIEARC', 'ROWID', $ConnSerieArc_rec);
            return true;
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in inserimento connessione serie.' . $exc->getMessage());
            return false;
        }
    }

    public function CancellaSerieTitolario($rowidSerie) {
        $ConnSerieArc_rec = $this->GetConnSerie($rowidSerie, '', '', 'rowid');
        if (!$ConnSerieArc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Serie non presente per il titolario indicato.');
            return false;
        }
        /* @TODO Inserire un controllo di utilizzo serie.. */
        try {
            ItaDB::DBDelete($this->proLib->getPROTDB(), 'CONNSERIEARC', 'ROWID', $ConnSerieArc_rec['ROWID']);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in cancellazione connessione serie.' . $exc->getMessage());
            return false;
        }
        return true;
    }

    public function CaricaGrigliaSerie($gridSerie, $CodiceTitolario, $Versione_T) {
        $sql = "SELECT CONNSERIEARC.*,ANASERIEARC.DESCRIZIONE,ANASERIEARC.SIGLA
                    FROM CONNSERIEARC 
                    LEFT OUTER JOIN ANASERIEARC ON CONNSERIEARC.CODICESERIE=ANASERIEARC.CODICE 
                    WHERE ORGCCF = '$CodiceTitolario' AND VERSIONE_T = '$Versione_T' AND FLGANN = 0 ";
        TableView::clearGrid($gridSerie);
        $ita_grid01 = new TableView($gridSerie, array(
            'sqlDB' => $this->proLib->getPROTDB(),
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->setSortIndex('CODICESERIE');
        $Result_tab = $ita_grid01->getDataArray();
// Qui se serve elaborare la tabella di risultato.
        $ita_grid01->getDataPageFromArray('json', $Result_tab);
        return;
    }

    public function GetElencoConnSerie($codiceTitolario, $Versione_t, $soloValidi = true) {
        $sql = "SELECT ANASERIEARC.*,CONNSERIEARC.ORGCCF
                    FROM CONNSERIEARC 
                    LEFT OUTER JOIN ANASERIEARC ON CONNSERIEARC.CODICESERIE=ANASERIEARC.CODICE 
                    WHERE ORGCCF = '$codiceTitolario' AND VERSIONE_T = '$Versione_t' ";
        if ($soloValidi) {
            $sql .= " AND  FLGANN = 0";
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
    }

    public function CtrSerieObbligatoria($Titolario, $Versione_T) {
        $SerieCon_tab = $this->GetElencoConnSerie($Titolario, $Versione_T);
        if (!$SerieCon_tab) {
            $this->setErrMessage("Nessuna serie disponibile per il titolario indicato.");
            return false;
        }
        //$this->setErrMessage('Per il titolario indicato è richiesta una Serie. Valorizzare una serie prima di procedere.');
        $this->setErrMessage('Per il titolario indicato è possibile utilizzare una Serie. Verifica ed eventualmente aggiungila.');
        return true;
    }

    public function CtrSerieInTitolario($Codice, $CodiceTitolario, $Versione_T) {
        $ConnSerieTitolario = $this->GetConnSerie($Codice, $CodiceTitolario, $Versione_T);
        if (!$ConnSerieTitolario) {
            $this->setErrCode(-1);
            $this->setErrMessage("La Serie indicata non è utilizzabile per il titolario indicato.");
            return false;
        }
        return true;
    }

    public function AggiungiSerieAFascicolo($CodiceSerie, $CodiceTitolario, $Versione_T, $Anaorg_rec = '', $Progressivo = '') {
// Lock tab ANAORG.
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAORG", "", "", 20);
        if ($retLock['status'] !== 0) {
            return false;
        }
        $NewProgressivo = $this->PrenotaProgressivoSerie($CodiceSerie, $CodiceTitolario, $Versione_T, $Anaorg_rec, $Progressivo);
        if (!$NewProgressivo) {
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }
        if (!$this->AggiornaSerieFascicolo($Anaorg_rec, $CodiceSerie, $NewProgressivo)) {
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }
        ItaDB::DBUnLock($retLock['lockID']);
        return true;
    }

    public function AggiornaSerieFascicolo($Anaorg_rec, $CodiceSerie, $NewProgressivo) {
        if (!$this->CtrProgressivoSerieFascicolo($CodiceSerie, $NewProgressivo, $Anaorg_rec['ORGKEY'])) {
            return false;
        }
        /* Aggiornamento ANAORG del progressivo. */
        $AnaorgAgg = array();
        $AnaorgAgg['ROWID'] = $Anaorg_rec['ROWID'];
        $AnaorgAgg['CODSERIE'] = $CodiceSerie;
        $AnaorgAgg['PROGSERIE'] = $NewProgressivo;
// Agg Serie
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAORG', 'ROWID', $AnaorgAgg);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento " . $exc->getMessage());
            return false;
        }
// Rilettura
        $Anaorg_rec = $this->proLib->GetAnaorg($Anaorg_rec['ROWID'], 'rowid');
        $segnatura = $Segnatura = $this->GetSegnaturaFascicolo($Anaorg_rec);
        $Anaorg_rec['ORGSEG'] = $segnatura;
// Segnatura
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAORG', 'ROWID', $Anaorg_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento " . $exc->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Blocca Progressivo Serie
     * 
     * @param type $CodiceSerie
     * @return boolean
     * 
     */
    public function bloccaProgressivoSerie($CodiceSerie) {
        if (!$CodiceSerie) {
            $this->setErrMessage('Indicare Codice Serie e Anno.');
            return false;
        }
        $Serie_rec = $this->GetSerie($CodiceSerie, 'codice');
        if (!$Serie_rec) {
            $this->setErrMessage('Lettura dati seria fallita.*');
            return false;
        }
        $retLock = $this->lockSerie($Serie_rec['ROWID']);
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    /**
     * Sblocca progressivo Pratica
     * 
     * @param type $retLock
     * @return type
     * 
     */
    public function sbloccaProgressivoSerie($retLock) {
        return $this->unlockSerie($retLock);
    }

    /**
     * Legge e inizializza a "1", se necessario, l'ultimo codice libero per il progressivo serie.
     * Se Annuale legge da PROGSERIE altrimenti da ANASERIEARC
     * 
     * @param type $CodiceSerie
     * @param type $Anno
     * @return boolean|int
     * 
     */
    public function leggiProgressivoSerie($CodiceSerie, $Anno) {
        if (!$CodiceSerie || !$Anno) {
            $this->setErrMessage('Indicare Codice Serie e Anno.');
            return false;
        }
        $AnaSerieArc_rec = $this->GetSerie($CodiceSerie, 'codice');
        if (!$AnaSerieArc_rec) {
            $this->setErrMessage('Lettura dati seria fallita.*');
            return false;
        }

        if ($AnaSerieArc_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            $this->setErrMessage('Progressivo non prenotablie. Inserimento Manuale.');
            return false;
        }

        switch ($AnaSerieArc_rec['TIPOPROGRESSIVO']) {
            case 'ANNUALE':
                /*
                 * Controllo se inizializzo anno
                 */
                $Progserie_rec = $this->GetProgSerie($CodiceSerie, $Anno);
                if (!$Progserie_rec) {
                    $Progserie_rec = array();
                    $Progserie_rec['CODICE'] = $CodiceSerie;
                    $Progserie_rec['ANNO'] = $Anno;
                    $Progserie_rec['PROGRESSIVO'] = 1;
                    $Progserie_rec['PROGRESSIVO'];
                    try {
                        ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROGSERIE', 'ROWID', $Progserie_rec);
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Inizializzazione PROGSERIE " . $exc->getMessage());
                        return false;
                    }
                    $Progserie_rec = $this->GetProgSerie($CodiceSerie, $Anno);
                    if (!$Progserie_rec) {
                        $this->setErrMessage('Lettura dati serie annuale fallita.*');
                        return false;
                    }
                }
                $progressivo = $Progserie_rec['PROGRESSIVO'];
                break;
            case 'ASSOLUTO':
                $progressivo = $AnaSerieArc_rec['PROGRESSIVO'];
                if (!$AnaSerieArc_rec['PROGRESSIVO']) {
                    $progressivo = 1;
                }
                break;
        }
        return $progressivo;
    }

    /**
     * Aggiorna il record dei progressivi serie 
     * se annunale aggiorna la Tabella PROGSERIE altrimenti la TABELLA ANASERIEARC
     * 
     * @param type $CodiceSerie
     * @param type $Anno
     * @return boolean
     * 
     */
    function aggiornaProgressivoSerie($CodiceSerie, $Anno, $Progressivo) {
        if (!$CodiceSerie || !$Anno) {
            $this->setErrMessage('Indicare Codice Serie e Anno.');
            return false;
        }
        $AnaSerieArc_rec = $this->GetSerie($CodiceSerie, 'codice');
        if (!$AnaSerieArc_rec) {
            $this->setErrMessage('Lettura dati seria fallita.*');
            return false;
        }

        if ($AnaSerieArc_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            $this->setErrMessage('Progressivo non prenotablie. Inserimento Manuale.');
            return false;
        }
        switch ($AnaSerieArc_rec['TIPOPROGRESSIVO']) {
            case 'ANNUALE':
                $Progserie_rec = $this->GetProgSerie($CodiceSerie, $Anno);
                if (!$Progserie_rec) {
                    $this->setErrMessage('Lettura dati serie annuale fallita.*');
                    return false;
                }
                $Progserie_rec['PROGRESSIVO'] = $Progressivo;
                $Progserie_rec['DATAPROGRESSIVO'] = date("Ymd");
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROGSERIE', 'ROWID', $Progserie_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento PROGSERIE " . $exc->getMessage());
                    return false;
                }
                break;
            case 'ASSOLUTO':
                $AnaSerieArc_rec['PROGRESSIVO'] = $Progressivo;
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANASERIEARC', 'ROWID', $AnaSerieArc_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento ANASERIEARC " . $exc->getMessage());
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * 
     * @param type $CodiceSerie
     * @param type $Anno
     * @return boolean
     * 
     */
    public function prenotaProgressivoSerieUtil($CodiceSerie, $Anno) {
        $retLock = $this->bloccaProgressivoSerie($CodiceSerie, $Anno);
        if (!$retLock) {
            return false;
        }
        $Progressivo = $this->leggiProgressivoSerie($CodiceSerie, $Anno);
        if (!$Progressivo) {
            $this->sbloccaProgressivoSerie($retLock);
            return false;
        }
        $Progressivo_new = $Progressivo + 1;
        if (!$this->aggiornaProgressivoSerie($CodiceSerie, $Anno, $Progressivo_new)) {
            $this->sbloccaProgressivoSerie($retLock);
            return false;
        }
        $this->sbloccaProgressivoSerie($retLock);
        return $Progressivo;
    }

    /**
     * Funzione Temporanea di prenotazione progressivo serire da spezzare un parti di blocco sblocco e aggiornamento separate
     * @param type $CodiceSerie
     * @param type $Anno
     * @return boolean
     */
    public function PrenotaProgressivoSerieUtil_old($CodiceSerie, $Anno) {
        if (!$CodiceSerie || !$Anno) {
            $this->setErrMessage('Indicare Codice Serie e Anno.');
            return false;
        }
        $Serie_rec = $this->GetSerie($CodiceSerie, 'codice');
        if (!$Serie_rec) {
            $this->setErrMessage('Lettura dati seria fallita.*');
            return false;
        }

        if ($Serie_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            $this->setErrMessage('Progressivo non prenotablie. Inserimento Manuale.');
            return false;
        }

        $retLock = $this->lockSerie($Serie_rec['ROWID']);
        if (!$retLock) {
            $this->setErrMessage('Lock progressivo Fallito.*');
            return false;
        }
        /* Rilettura della serie: */
        $AnaSerieArc_rec = $this->GetSerie($CodiceSerie, 'codice');
        if (!$AnaSerieArc_rec) {
            $this->unlockSerie($retLock);
            $this->setErrMessage('Lettura dati seria fallita.*');
            return false;
        }

        switch ($Serie_rec['TIPOPROGRESSIVO']) {
            case 'ANNUALE':
                /*
                 * Controllo oe inizializzo anno
                 */
                $Progserie_rec = $this->GetProgSerie($CodiceSerie, $Anno);
                if (!$Progserie_rec) {
                    $Progserie_rec = array();
                    $Progserie_rec['CODICE'] = $CodiceSerie;
                    $Progserie_rec['ANNO'] = $Anno;
                    $Progserie_rec['PROGRESSIVO'] = 1;
                    $Progserie_rec['PROGRESSIVO'];
                    try {
                        ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROGSERIE', 'ROWID', $Progserie_rec);
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Inizializzazione PROGSERIE " . $exc->getMessage());
                        $this->unlockSerie($retLock);
                        return false;
                    }
                    $Progserie_rec = $this->GetProgSerie($CodiceSerie, $Anno);
                    if (!$Progserie_rec) {
                        $this->unlockSerie($retLock);
                        $this->setErrMessage('Lettura dati serie annuale fallita.*');
                        return false;
                    }
                }
                $progressivo = $Progserie_rec['PROGRESSIVO'];
                $Progserie_rec['PROGRESSIVO'] = $progressivo + 1;
                $Progserie_rec['DATAPROGRESSIVO'] = date("Ymd");
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROGSERIE', 'ROWID', $Progserie_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento PROGSERIE " . $exc->getMessage());
                    $this->unlockSerie($retLock);
                    return false;
                }
                break;

            case 'ASSOLUTO':
                $Progressivo = $AnaSerieArc_rec['PROGRESSIVO'];
                if (!$AnaSerieArc_rec['PROGRESSIVO']) {
                    $Progressivo = 1;
                }
                $AnaSerieArc_rec['PROGRESSIVO'] = $Progressivo + 1;
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANASERIEARC', 'ROWID', $AnaSerieArc_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento ANASERIEARC " . $exc->getMessage());
                    $this->unlockSerie($retLock);
                    return false;
                }
                break;
        }
        if (!$this->unlockSerie($retLock)) {
            return false;
        }
        return $progressivo;
    }

    public function PrenotaProgressivoSerie($CodiceSerie, $CodiceTitolario, $Versione_T, $Anaorg_rec = '', $Progressivo = '') {
        /*
         * Dati Obbligatori
         */
        if (!$CodiceSerie || !$CodiceTitolario) {
            $this->setErrMessage('Indicare Codice Serie e Titolario.');
            return false;
        }
        /*
         * Controllo serie utilizzabile nel titolario.
         */
        if (!$this->CtrSerieInTitolario($CodiceSerie, $CodiceTitolario, $Versione_T)) {
            return false;
        }

        /*
         * Anaorg per prendere l'anno.... obbligatorio?
         */
        $Orgkey = '';
        if ($Anaorg_rec) {
            $Orgkey = $Anaorg_rec['ORGKEY'];
        }
        $Anno = $Anaorg_rec['ORGANN'];

        /*
         * Controllo progressivo utilizzato
         */
        $Serie_rec = $this->GetSerie($CodiceSerie, 'codice');
        if ($Serie_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            if (!$Progressivo) {
                $this->setErrMessage('La serie richiede un progressivo da indicare manualmente, occorre indicarlo prima di procedere.');
                return false;
            }
            /*
             *  Check Esistenza.
             */
            if (!$this->CtrProgressivoSerieFascicolo($CodiceSerie, $Progressivo, $Orgkey)) {
                return false;
            }
        } else {
            /*
             * Blocco delle tabelle per prenotazione progressivo 
             *              Assoluto e Annuale.
             */
            $retLock = $this->bloccaProgressivoSerie($CodiceSerie);
            if (!$retLock) {
                return false;
            }
            /*
             * Leggo ultimo progressivo libero
             */
            $Progressivo = $this->leggiProgressivoSerie($CodiceSerie, $Anno);

            /*
             *  Check Esistenza.
             */
            if (!$this->CtrProgressivoSerieFascicolo($CodiceSerie, $Progressivo, $Orgkey)) {
                return false;
            }

            /*
             * Aggiorno il numeratore del progressivo
             */
            $Progressivo_new = $Progressivo + 1;
            if (!$this->aggiornaProgressivoSerie($CodiceSerie, $Anno, $Progressivo_new)) {
                $this->sbloccaProgressivoSerie($retLock);
                return false;
            }
            /*
             * Sblocco Tabella Progressivo
             */
            $this->sbloccaProgressivoSerie($retLock);
        }
        return $Progressivo;
    }

    public function ControlloDatiObbligatoriSerie($Serie_rec, $titolario, $versione_t, $Orgkey = '') {

        /* 1. Controllo obbligatorietà serie */
//        if ($this->CtrSerieObbligatoria($titolario, $versione_t)) {
//            if (!$Serie_rec['CODICE']) {
//                return false;
//            }
//        }
        /* Altri Controlli sulle serie */
        if ($Serie_rec['CODICE']) {
            if (!$titolario) {
                $this->setErrCode(-1);
                $this->setErrMessage("Occorre indicare il titolario per poter indicare la serie.");
                return false;
            }
            if (!$this->CtrSerieInTitolario($Serie_rec['CODICE'], $titolario, $versione_t)) {
                return false;
            }
            /* Se Manuale Deve indicare il progressivo. */
            $AnaSerie = $this->GetSerie($Serie_rec['CODICE'], 'codice');
            if ($AnaSerie['TIPOPROGRESSIVO'] == 'MANUALE') {
                if (!$Serie_rec['PROGSERIE']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Occorre indicare il Progressivo della serie prima di poter procedere.");
                    return false;
                }
                /* Controllo esistenza progressivo manuale */
                if (!$this->CtrProgressivoSerieFascicolo($Serie_rec['CODICE'], $Serie_rec['PROGSERIE'], $Orgkey)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function lockSerie($rowid) {
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANASERIEARC", $rowid, "", 120);
        if ($retLock['status'] != 0) {
            $this->setErrMessage('Blocco Tabella PROGRESSIVI non Riuscito per ANASERIEARC.');
            return false;
        }
        return $retLock;
    }

    public function unlockSerie($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
// Accodo gli errori per riaverli entrambi.
            $this->setErrMessage($this->getErrMessage() . ' - Sblocco Tabella PROGRESSIVI non Riuscito per ANASERIEARC.');
            return false;
        }
        return true;
    }

    public function CtrProgressivoSerieFascicolo($CodiceSerie, $Progressivo = '', $Orgkey = '') {
        if (!$CodiceSerie || !$Progressivo) {
            $this->setErrMessage("Progressivo o Serie mancanti.");
            return false;
        }
        /* Controllo numerico: DA RIMUOVERE SE DIVENTA ALFANUMERICO. */
        if (!is_numeric($Progressivo)) {
            $this->setErrMessage("Il progressivo deve essere numerico.");
            return false;
        }

        $sql = "SELECT * FROM ANAORG WHERE CODSERIE = '$CodiceSerie' AND PROGSERIE = '$Progressivo' ";
        $Serie_rec = $this->GetSerie($CodiceSerie, 'codice');
        if ($Serie_rec['TIPOPROGRESSIVO'] == 'ANNUALE') {
            $Anno = date('Y');
            $sql .= " AND ORGANN = $Anno ";
        }
// Serve?
        if ($Orgkey) {
            $sql .= " AND ORGKEY <> '$Orgkey' ";
        }
// Manuale è come assoluto?
        $CheckSerie_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($CheckSerie_rec) {
            $this->setErrMessage("Fascicolo con codice serie $CodiceSerie e progressivo $Progressivo già esistente.");
            return false;
        }
        return true;
    }

    public function GetSegnaturaFascicolo($Anaorg_rec) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabiliFascicolo.class.php';

// Qui funzione per ricavare il modello base.
        $anaent_rec = $this->proLib->GetAnaent('50');
        $separatore = $anaent_rec['ENTDE1'];
        $modello = $anaent_rec['ENTVAL'];
        if (!$modello) {
            $modello = '@{$CATEGORIA}@@{$CLASSE}@@{$SOTTOCLASSE}@@{$ANNO}@@{$PROGRESSIVO}@';
            if (!$separatore)
                $separatore = '/';
        }

        if ($Anaorg_rec['CODSERIE']) {
            $Anaseriearc_rec = $this->proLib->getAnaseriearc($Anaorg_rec['CODSERIE']);
            if ($Anaseriearc_rec['SEGTEMPLATE']) {
                $modello = $Anaseriearc_rec['SEGTEMPLATE'];
                $separatore = $Anaseriearc_rec['SEGSEPARATORE'];
                if (!$separatore)
                    $separatore = '/';
            }
        }
        $proLibVarFascicolo = new proLibVariabiliFascicolo();
        $proLibVarFascicolo->setAnaorg_rec($Anaorg_rec);

        $dictionaryValues = $proLibVarFascicolo->ValorizzaVariabiliAll()->getAllData();
        $wsep = '';
        foreach ($dictionaryValues as $key => $valore) {
            $search = '@{$' . $key . '}@';
            if ($valore) {
                if (strpos($modello, $search) === 0) {
                    $wsep = '';
                } else {
                    $wsep = $separatore;
                }
            } else {
                $wsep = '';
            }
            $replacement = $wsep . $valore;
            $modello = str_replace($search, $replacement, $modello);
        }

        if (strpos($modello, '@{$') !== false) {
            return false;
        }
        if (strpos($modello, '}@') !== false) {
            return false;
        }

        return $modello;
    }

    public function getMaxProgserie($codice, $anno = null) {
        if ($anno === null) {
            $sql = "SELECT * FROM PROGSERIE WHERE CODICE = '$codice' AND PROGRESSIVO = (SELECT MAX(PROGRESSIVO) FROM PROGSERIE WHERE CODICE = '$codice')";
            $multi = true;
        } else {
            $sql = "SELECT * FROM PROGSERIE WHERE CODICE = '$codice' AND PROGRESSIVO = (SELECT MAX(PROGRESSIVO) FROM PROGSERIE WHERE CODICE = '$codice' AND ANNO = $anno)";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    public function GetTitolariConnSerie($Codice, $Versione_T = '', $multi = false) {
        $sql = "SELECT * FROM CONNSERIEARC WHERE CODICESERIE = $Codice ";
        if (!$Versione_T) {
            $Versione_T = $this->proLib->GetTitolarioCorrente();
        }
        $sql.=" AND  VERSIONE_T = '$Versione_T' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    public function ControlloProgressivoSerie($CodiceSerie, $CodiceTitolario, $Versione_T, $Anaorg_rec = '', $Progressivo = '') {
        /*
         * Dati Obbligatori
         */
        if (!$CodiceSerie || !$CodiceTitolario) {
            $this->setErrMessage('Indicare Codice Serie e Titolario.');
            return false;
        }
        /*
         * Controllo serie utilizzabile nel titolario.
         */
        if (!$this->CtrSerieInTitolario($CodiceSerie, $CodiceTitolario, $Versione_T)) {
            return false;
        }

        /*
         * Anaorg per prendere l'anno.... obbligatorio?
         */
        $Orgkey = '';
        if ($Anaorg_rec) {
            $Orgkey = $Anaorg_rec['ORGKEY'];
        }

        if (!$Progressivo) {
            $this->setErrMessage('La serie richiede un progressivo da indicare manualmente, occorre indicarlo prima di procedere.');
            return false;
        }
        /*
         *  Check Esistenza.
         */
        if (!$this->CtrProgressivoSerieFascicolo($CodiceSerie, $Progressivo, $Orgkey)) {
            return false;
        }
        
        return true;
    }

    public function decodParametriPanelFascicolo($MetaPanel, $tipo = 'decode', $array = true) {
        // ritorna array contenente i parametri
        $parametro = json_decode($MetaPanel, $array);
        if ($tipo == 'decode') {
            return $parametro;
        }
        // elabora i parametri i parametri prendendo l'anagrafica dalla classe 
        $valueAnagrafica = proLibSerie::$PANEL_LIST;
        foreach ($valueAnagrafica as $key => $valueAnagrafica_rec) {
            foreach ($parametro as $valueRecord) {
                if ($valueAnagrafica_rec['DESCRIZIONE'] == $valueRecord['DESCRIZIONE']) {
                    $valueAnagrafica[$key]['DEF_STATO'] = $valueRecord['DEF_STATO'];
                    continue;
                }
            }
        }
        return $valueAnagrafica;
    }
    
    
    
}
