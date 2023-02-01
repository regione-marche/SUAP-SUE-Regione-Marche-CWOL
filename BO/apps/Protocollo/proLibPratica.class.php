<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    30.10.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
include_once (ITA_LIB_PATH . '/itaPHPPaleo/itaOperatorePaleo.class.php');

class proLibPratica {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $PROT_DB;
    public $PRAM_DB;
    public $ITW_DB;
    public $ITALWEB_DB;
    public $accLib;
    public $praLib;
    public $proLib;
    public $devLib;

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PROT_DB = ItaDB::DBOpen('PROT', $ditta);
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                $this->ITW_DB = ItaDB::DBOpen('ITW', $ditta);
            } else {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                $this->ITW_DB = ItaDB::DBOpen('ITW');
            }
            $this->praLib = new praLib();
            $this->proLib = new proLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function setPRAMDB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function setITWDB($ITW_DB) {
        $this->ITW_DB = $ITW_DB;
    }

    public function setITALWEBDB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getPRAMDB() {
        return $this->PRAM_DB;
    }

    public function getPROTDB() {
        return $this->PROT_DB;
    }

    public function getITWDB() {
        return $this->ITW_DB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEBDB', "");
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    /**
     * Restituisce unrecord pratica
     * @param type $Codice
     * @param type $tipoRic
     * @param Boolean $multi Non usato
     * @return type
     */
    public function GetProges($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $Codice . "'";
        } else if ($tipoRic == 'geskey') {
            $sql = "SELECT * FROM PROGES WHERE GESKEY='" . $Codice . "'";
        } else if ($tipoRic == 'richiesta') {
            $sql = "SELECT * FROM PROGES WHERE GESPRA='" . $Codice . "'";
        } else if ($tipoRic == 'protocollo') {
            $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PROGES WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetMetadatiProges($codice, $tipoRic = 'codice', $multi = false) {
        $proges_rec = $this->GetProges($codice, $tipoRic, $multi);
        $metavalue = unserialize($proges_rec['GESMETA']);
        return $metavalue;
    }

    public function GetPrasta($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRASTA WHERE STANUM='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM PRASTA WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetProdag($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRODAG WHERE DAGSET='" . $Codice . "'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $Codice . "'";
        } else if ($tipoRic == 'dagpak') {
            $sql = "SELECT * FROM PRODAG WHERE DAGPAK = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRODAG WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetPropas($codice, $tipoRic = 'propak', $multi = false, $tipo = '') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM='$codice' ORDER BY PROSEQ";
        } else if ($tipoRic == 'propak') {
            $sql = "SELECT * FROM PROPAS WHERE PROPAK='$codice'";
        } else if ($tipoRic == 'paspro') {
            $sql = "SELECT * FROM PROPAS WHERE PASPRO=$codice AND PASPAR='$tipo'";
        } else {
            $sql = "SELECT * FROM PROPAS WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetPakdoc($codice, $tipoRic = 'propak', $multi = true, $tipo = '') {
        if ($tipoRic == 'pronum') {
            $sql = "SELECT * FROM PAKDOC WHERE PRONUM='{$codice['PRONUM']}' AND PROPAR='{$codice['PROPAR']}'";
        } else if ($tipoRic == 'propak') {
            if ($tipo) {
                $where = " AND PROPAR = '$tipo'";
            }
            $sql = "SELECT * FROM PAKDOC WHERE PROPAK='$codice' $where";
        } else if ($tipoRic == 'chiave') {
            $sql = "SELECT * FROM PAKDOC WHERE PRONUM='{$codice['PRONUM']}' AND PROPAR='{$codice['PROPAR']}' AND PROPAK='{$codice['PROPAK']}'";
        } else {
            $sql = "SELECT * FROM PAKDOC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function SetDirectoryPratiche($anno, $keyProc, $tipo = "PASSO", $crea = true, $ditta = '') {
        $repertorio = substr($keyProc, 0, 10);
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        switch ($tipo) {
            case "PASSO":
                $d_nome = $anno . '/' . $tipo . '/' . $repertorio . '/' . substr($keyProc, 0, 20) . '/' . $keyProc;
                break;
            case "ANAPRA":
                $d_nome = $tipo . '/' . $keyProc;
                break;
            case "PROGES":
                $d_nome = $anno . '/' . $tipo . '/' . $repertorio . '/' . $keyProc;
                break;
            default :
                return false;
        }
        $d_dir = Config::getPath('general.itaPrim') . 'prot' . $ditta . '/';
        if (!is_dir($d_dir . $d_nome)) {
            if ($crea == true) {
                if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

    public function PropakGenerator($numeroProcedimento) {
        if ($numeroProcedimento == '') {
            Out::msgStop("Errore", "Errore nella creazione della chiave univoca per l'Iter! PropakGenerator");
            return false;
        }
        usleep(50000); // 50 millisecondi;
        list($msec, $sec) = explode(" ", microtime());
        return $numeroProcedimento . $sec . substr($msec, 2, 2);
    }

    function ordinaPassi($procedimento) {
        if ($procedimento) {
            $new_seq = 0;
            $Propas_tab = $this->getPropasTab($procedimento);
            if (!$Propas_tab) {
                return false;
            }
            foreach ($Propas_tab as $Propas_rec) {
                $new_seq +=10;
                $Propas_rec['PROSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->getPROTDB(), "PROPAS", "ROWID", $Propas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
            return true;
        }
    }

    function getPropasTab($procedimento, $sortField = "PROSEQ") {
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $procedimento . "' ORDER BY " . $sortField;
        try {
            return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        } catch (Exception $e) {
            Out::msgStop('Errore DB', $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param type $procedimento
     * @param type $forzaChiusura
     * @return array|boolean|string
     */
    function sincronizzaStato($procedimento, $forzaChiusura = false) {
//
// Estraggo il record stato del pratica da aggiornare.
//
        $Prasta_rec = $this->GetPrasta($procedimento, 'codice');
        if (!$Prasta_rec) {
            $fl_trovato = false;
            $Prasta_rec = array();
        } else {
            $fl_trovato = true;
        }


//
// Estraggo il recod pratica
//
        $Proges_rec = $this->GetProges($procedimento, 'codice');

        if (!$Proges_rec) {
            return $Prasta_rec;
        }

        $Prasta_rec['STANUM'] = $Proges_rec['GESNUM'];
        $Prasta_rec['STANRC'] = $Proges_rec['GESNRC'];
        $Prasta_rec['STAPRO'] = $Proges_rec['GESPRO'];

        $Prasta_rec['STAPAS'] = '';
        $Prasta_rec['STADIN'] = '';
        $Prasta_rec['STADFI'] = '';
        $Prasta_rec['STADES'] = '';
        $Prasta_rec['STADEX'] = '';
        $Prasta_rec['STAPST'] = 0;
        $arrayStato = array(
            'tipo' => '',
            'chiudi' => false,
            'descrizione' => '',
            'PROPAK' => ''
        );
        if ($Proges_rec['GESCLOSE'] == "@forzato@" && $Proges_rec['GESDCH'] != "") {
            $Prasta_rec['STADES'] = 'Fascicolo Chiuso';
            return $this->registraPrasta($Prasta_rec, $fl_trovato);
        }


//
// Estraggo i passi collegati alla pratica
//
        // FILTRA SOLO AZIONI DI TIPO T
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '$procedimento' AND (PROINI<>'' OR PROFIN <>'' ) ORDER BY PROINI, PROSEQ";
        $Propas_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        if ($Propas_tab) {

//
// Scorro i passi che si non aperti o chiusi
//
            foreach ($Propas_tab as $Propas_rec) {
                $Prasta_rec['STAPAS'] = $Propas_rec['PROCLT'];
                $Prasta_rec['STADIN'] = $Propas_rec['PROINI'];
                $Prasta_rec['STADFI'] = $Propas_rec['PROFIN'];
                $Prasta_rec['STAPAK'] = $Propas_rec['PROPAK'];
                $Prasta_rec['STADEX'] = $Propas_rec['PRODTP'] . " - " . $Propas_rec['PRODPA'] . " - " . $Propas_rec['PROANN'];
                $Prasta_rec['STAPST'] = $Propas_rec['PROPST'];
//                $Anatsp_rec = array();
                if ($Propas_rec['PROSTATO'] == 0) {
                    $Anastp_rec['STPFLAG'] = "In corso";
                    $Anastp_rec['STPDES'] = "Fascicolo Aperto";
                } else {
                    $Anastp_rec = $this->praLib->GetAnastp($Propas_rec['PROSTATO']);
                }
                $Prasta_rec['STACOD'] = $Propas_rec['PROSTATO'];
                $Prasta_rec['STAFLAG'] = $Anastp_rec['STPFLAG'];
                if ($Anastp_rec['STPFLAG'] == "Chiusa Negativamente") {
                    $arrayStato['tipo'] = 'chiudiNeg';
                    $arrayStato['chiudi'] = true;
                    $arrayStato['descrizione'] = array($Anastp_rec['STPDES']);
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                    break;
                } else if ($Anastp_rec['STPFLAG'] == "Chiusa Positivamente") {
                    $arrayStato['tipo'] = 'chiudiPos';
                    $arrayStato['chiudi'] = true;
                    $arrayStato['descrizione'] = array($Anastp_rec['STPDES']);
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                    break;
                } else if ($Anastp_rec['STPFLAG'] == "Annullata") {
                    $arrayStato['tipo'] = 'annulla';
                    $arrayStato['chiudi'] = true;
                    $arrayStato['descrizione'] = array($Anastp_rec['STPDES']);
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                    break;
                } else if ($Anastp_rec['STPFLAG'] == "Sospesa") {
                    $arrayStato['tipo'] = 'Sospesa';
                    $arrayStato['chiudi'] = false;
                    $arrayStato['descrizione'] = array($Anastp_rec['STPDES']);
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                    break;
                } elseif ($Anastp_rec['STPFLAG'] == "In corso") {
                    $arrayStato['tipo'] = 'inCorso';
                    $arrayStato['chiudi'] = false;
                    if ($Propas_rec['PROFIN'] == '') {
                        $arrayStato['descrizione'][] = $Anastp_rec['STPDES'];
                    }
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                }
            }
        }
//if (!$forzaChiusura) {
        if ($arrayStato['chiudi'] == true) {
            $Proges_rec['GESDCH'] = date("Ymd");
            $Proges_rec['GESCLOSE'] = $arrayStato['PROPAK'];
        } else {
            $Proges_rec['GESDCH'] = "";
            $Proges_rec['GESCLOSE'] = "";
        }
//}
        try {
            $nrow = ItaDB::DBUpdate($this->getPROTDB(), 'PROGES', 'ROWID', $Proges_rec);
            if ($nrow == -1) {
                Out::msgStop("Errore", 'Aggiornamento Stato Fascicolo Fallito.');
                return false;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }

        if ($Proges_rec['GESDCH'] != '') {
            $Prasta_rec['STADES'] = 'Fascicolo Chiuso';
            if ($Proges_rec['GESCLOSE'] != "") {
                if ($arrayStato['descrizione']) {
                    $Prasta_rec['STADES'] = implode(" , ", array_unique($arrayStato['descrizione']));
                } else {
                    $Prasta_rec['STADES'] = 'Fascicolo Chiuso';
                }
            }
        } else {
            if ($Prasta_rec['STADIN'] == '' && $Prasta_rec['STADFI'] == '') {
                $Prasta_rec['STADES'] = 'Procedimento Acquisito';
            } else {
                if ($arrayStato['descrizione']) {
                    $Prasta_rec['STADES'] = implode(" , ", array_unique($arrayStato['descrizione']));
                } else {
                    $Prasta_rec['STADES'] = 'Fascicolo Aperto';
                }
            }
        }

        return $this->registraPrasta($Prasta_rec, $fl_trovato);
    }

    private function registraPrasta($Prasta_rec, $fl_trovato) {
        if ($fl_trovato) {
            try {
                $nrow = ItaDB::DBUpdate($this->getPROTDB(), 'PRASTA', 'ROWID', $Prasta_rec);
                if ($nrow == -1) {
                    Out::msgStop("Errore", 'Aggiornamento Stato Fascicolo Fallito.');
                    return false;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return false;
            }
        } else {
            try {
                $nrow = ItaDB::DBInsert($this->getPROTDB(), 'PRASTA', 'ROWID', $Prasta_rec);
                if ($nrow != 1) {
                    Out::msgStop("Errore", 'Inserimento Stato Fascicolo Fallito.');
                    return false;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return false;
            }
        }
        return $Prasta_rec;
    }

    function bloccaProgressivoPratica($repertorio) {
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAREPARC", $repertorio, "", 20);
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    function leggiProgressivoPratica($workYear, $repertoriofascicoli) {

        $Anareparc_rec = $this->proLib->getAnareparc($repertoriofascicoli);
        if (!$Anareparc_rec) {
            $ProgressivoRepertorio = 0;
        } else {
            $Proges_rec = ItaDB::DBSQLSelect($this->getPROTDB(), "SELECT MAX(GESNUM) AS ULTIMO FROM PROGES", false);
            if (substr($Proges_rec['ULTIMO'], 10, 4) != $workYear) {
                $ProgressivoRepertorio = 0;
            } else {
                $ProgressivoRepertorio = intval($Anareparc_rec['PROGRESSIVO']);
            }
        }
        return str_pad($ProgressivoRepertorio + 1, 6, "0", STR_PAD_LEFT);
    }

    function aggiornaProgressivoPratica($repertorio, $ProgressivoRepertorio) {

        $Anareparc_rec = $this->proLib->checkRepertorioProtetto();

//        $Anareparc_rec = $this->proLib->getAnareparc($repertorio);
//        if (!$Anareparc_rec) {
//            $Anareparc_rec['PROGRESSIVO'] = $ProgressivoRepertorio;
//            $nins = ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAREPARC', 'ROWID', $Anareparc_rec);
//            if ($nins == 0) {
//                return false;
//            }
//        } else {
        $Anareparc_rec['PROGRESSIVO'] = $ProgressivoRepertorio;
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAREPARC', 'ROWID', $Anareparc_rec);
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
//        }
        return true;
    }

    function sbloccaProgressivoPratica($retLock) {
        return ItaDB::DBUnLock($retLock['lockID']);
    }

    /*
     * Prenotazione Istantanea pratica non usato
     */
    function prenotaPratica($workYear) {
        $retLock = $this->bloccaProgressivoPratica();
        $progressivo = $this->leggiProgressivoPratica($workYear);
        $this->aggiornaProgressivoPratica(intval($progressivo));
        $this->sbloccaProgressivoPratica($retLock);
        return $progressivo;
    }

    /**
     * Ribalta passi e dati collegati da anagrafica procedimento ANAPRA nell'inserimento diretto
     *
     * @param type $procedimento
     * @param type $datiFascicolazione
     * @return type
     */
    public function ribaltaPassi($proLibFascicolo, $model, $procedimento, $datiFascicolazione = array()) {
        $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $procedimento . "'";
        $proges_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        if (!$proges_rec) {
            return false;
        }
        //
        //Se c'è l'ente master prendi i passi da lui
        //
        $tipoEnte = $this->GetTipoEnte();
        if ($tipoEnte != "M") {
            $enteMaster = $this->GetEnteMaster();

            $PRAM_DB = ItaDB::DBOpen('PRAM', $enteMaster);
        } else {
            $PRAM_DB = $this->getPRAMDB();
        }
        $PRAM_DB = $this->getPRAMDB();
        $sql = "SELECT
                ITEPAS.ROWID AS ROWID,
                ITEPAS.ITEPUB AS ITEPUB,
                ITEPAS.ITESEQ AS ITESEQ,
                ITEPAS.ITERES AS ITERES,
                ITEPAS.ITEOPE AS ITEOPE,
                ITEPAS.ITESET AS ITESET,
                ITEPAS.ITESER AS ITESER,
                ITEPAS.ITEGIO AS ITEGIO,
                ITEPAS.ITECLT AS ITECLT,
                ITEPAS.ITEDES AS ITEDES,
                ITEPAS.ITEPUB AS ITEPUB,
                ITEPAS.ITEKEY AS ITEKEY,
                ITEPAS.ITEVPA AS ITEVPA,
                ITEPAS.ITEVPN AS ITEVPN,
                ITEPAS.ITEQST AS ITEQST,
                ITEPAS.ITEDAT AS ITEDAT,
                ITEPAS.ITEIRE AS ITEIRE,
                ITEPAS.ITECOM AS ITECOM,
                ITEPAS.ITEZIP AS ITEZIP,
                ITEPAS.ITEDRR AS ITEDRR,
                ITEPAS.ITEDIS AS ITEDIS,
                ITEPAS.ITEDOW AS ITEDOW,
                ITEPAS.ITEUPL AS ITEUPL,
                ITEPAS.ITEMLT AS ITEMLT,
                PRACLT.CLTDES AS CLTDES
            FROM ITEPAS
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
            WHERE ITECOD='" . $proges_rec['GESPRO'] . "' AND ITEPUB = 0 ORDER BY ITESEQ";

        $Itepas_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $new_seq = 0;

        foreach ($Itepas_tab as $key => $Itepas_rec) {
            $Praclt_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $Itepas_rec['ITECLT'] . "'", false);
            $new_seq += 10;
            $propas_rec = array();
            $propas_rec['PRONUM'] = $proges_rec['GESNUM'];
            $propas_rec['PROPRO'] = $proges_rec['GESPRO'];
            $propas_rec['PROSEQ'] = $seq = $new_seq;
            $propas_rec['PROPUB'] = $Itepas_rec['ITEPUB'];
            $propas_rec['PRORPA'] = $datiFascicolazione['RES'];
            $propas_rec['PROUFFRES'] = $datiFascicolazione['UFF'];
            $propas_rec['PASPROUTE'] = App::$utente->getKey('nomeUtente');
            ;
            $propas_rec['PASPROUFF'] = $datiFascicolazione['GESPROUFF'];
            $propas_rec['PROUOP'] = "";
            $propas_rec['PROSET'] = "";
            $propas_rec['PROSER'] = "";
            $propas_rec['PROGIO'] = $Itepas_rec['ITEGIO'];
            $propas_rec['PROCLT'] = $Itepas_rec['ITECLT'];
            $propas_rec['PRODTP'] = $Praclt_rec['CLTDES'];
            $propas_rec['PRODPA'] = $Itepas_rec['ITEDES'];
            $propas_rec['PROQST'] = $Itepas_rec['ITEQST'];
            $propas_rec['PROCOM'] = $Itepas_rec['ITECOM'];
            $propas_rec['PRODAT'] = $Itepas_rec['ITEDAT'];
            $propas_rec['PROIRE'] = $Itepas_rec['ITEIRE'];
            $propas_rec['PROZIP'] = $Itepas_rec['ITEZIP'];
            $propas_rec['PRODRR'] = $Itepas_rec['ITEDRR'];
            $propas_rec['PRODIS'] = $Itepas_rec['ITEDIS'];
            $propas_rec['PRODAT'] = $Itepas_rec['ITEDAT'];
            $propas_rec['PRODOW'] = $Itepas_rec['ITEDOW'];
            $propas_rec['PROUPL'] = $Itepas_rec['ITEUPL'];
            $propas_rec['PROMLT'] = $Itepas_rec['ITEMLT'];
            $propas_rec['PROQST'] = $Itepas_rec['ITEQST'];

            $propas_rec['PROPAK'] = $this->PropakGenerator($propas_rec['PRONUM']);
            $propas_rec['PROVPA'] = '';
            if ($Itepas_rec['ITEVPA'] != '') {
                $propas_rec['PROVPA'] = $propas_rec['PRONUM'] . substr($Itepas_rec['ITEVPA'], 6);
            }
            if ($Itepas_rec['ITEVPN'] != '') {
                $propas_rec['PROVPN'] = $propas_rec['PRONUM'] . substr($Itepas_rec['ITEVPN'], 6);
            }
            if ($Itepas_rec['ITECOM'] != 0) {
                if ($Itepas_rec['ITEINT'] == 1)
                    $propas_rec['PROINT'] = $Itepas_rec['ITEINT'];
                if ($Itepas_rec['ITECDE'] != "")
                    $propas_rec['PROCDE'] = $Itepas_rec['ITECDE'];
                $propas_rec['PROTBA'] = $Itepas_rec['ITETBA'];
            }

            $seqPasso = str_repeat("0", 3 - strlen($propas_rec['PROSEQ'])) . $propas_rec['PROSEQ'];
            foreach ($datiFascicolazione['ALLEGATI'] as $key => $allegato) {
                if (strpos($allegato['FILENAME'], "C" . $seqPasso) !== false) {
                    $alle[] = $allegato['FILENAME'];
                    $propas_rec['PROALL'] = serialize($alle);
                }
            }

            if ($Itepas_rec['ITEPUB'] == 1) {
                $propas_rec['PROITK'] = $this->currItekey;
                $propas_rec['PROINI'] = $proges_rec['GESDRE'];
                $propas_rec['PROFIN'] = $proges_rec['GESDRE'];
                $propas_rec['PROUTEADD'] = "@ADMIN@";
                $propas_rec['PROUTEEDIT'] = "@ADMIN@";
                $propas_rec['PRODATEADD'] = $propas_rec['PRODATEEDIT'] = date("Ymd");
                $propas_rec['PROORAADD'] = $propas_rec['PROORAEDIT'] = date("H:i:s");
                $propas_rec['PROVISIBILITA'] = "Protetto";
            } else {
                $propas_rec['PROVISIBILITA'] = "Aperto";
            }

            $rowid_anapro_Azione = $proLibFascicolo->creaAzione($proges_rec['GESKEY'], $propas_rec['PRODPA'], $datiFascicolazione);
            if (!$rowid_anapro_Azione) {
                return false;
            }
            $anapro_Azione_rec = $this->proLib->GetAnapro($rowid_anapro_Azione, 'rowid');
            $propas_rec['PASPRO'] = $anapro_Azione_rec['PRONUM'];
            $propas_rec['PASPAR'] = $anapro_Azione_rec['PROPAR'];

            try {
                $nrow = ItaDB::DBInsert($this->getPROTDB(), 'PROPAS', 'ROWID', $propas_rec);
                if ($nrow != 1) {
                    Out::msgStop("Errore", 'Inserimento Testata Pratica Fallito.');
                    return false;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return false;
            }

            $anapro_F_rec = $this->proLib->GetAnapro($proges_rec['GESKEY'], 'fascicolo');
            if (!$anapro_F_rec) {
                return false;
            }
            if (!$proLibFascicolo->insertDocumentoFascicolo($model, $proges_rec['GESKEY'], $anapro_Azione_rec['PRONUM'], $anapro_Azione_rec['PROPAR'], $anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR'])) {
                Out::msgStop("Aggiunta istanza Fallita", $proLibFascicolo->getErrMessage());
                return false;
            }

            if (!$this->ribaltaDatiAggEsterna($Itepas_rec['ITEKEY'], $propas_rec['PROPAK'])) {
                return false;
            }
        }
        return true;
    }

    public function GetTipoEnte() {
        return "S";
        /*
         * SLAVE FORZATO 
         * 
          $Filent_rec = $this->GetFilent(1);
          if ($Filent_rec) {
          if ($Filent_rec['FILDE4'] == '') {
          return "M";
          }
          return $Filent_rec['FILDE4'];
          } else {
          return 'M';
          }
         * 
         */
    }

    public function GetEnteMaster() {
        return false;
        /*
          $Filent_rec = $this->GetFilent(1);
          if ($Filent_rec) {
          return $Filent_rec['FILDE3'];
          } else {
          return false;
          }
         * 
         */
    }

    function ribaltaAllegatiPasso($allegati, $keyPasso, $seq, $procedimento) {
        //
        //EREDITATA DA LIBRERIE PRA(SUAP/SUE) REIMPLEMETARE QUANDO E SE SERVIRA'
        //
        return true;
    }

    function ribaltaDatiAggEsterna($keyPasso, $keyPassoPropas) {
        $Propas_rec = $this->GetPropas($keyPassoPropas);
        $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $keyPasso . "'";
        $Itedag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        if ($Itedag_tab) {
            foreach ($Itedag_tab as $Itedag_rec) {
                $Prodag_rec["DAGNUM"] = $Propas_rec['PRONUM'];
                $Prodag_rec["DAGCOD"] = $Propas_rec['PROPRO'];
                $Prodag_rec["DAGSEQ"] = 0;
                $Prodag_rec["DAGSFL"] = $Itedag_rec['ITDSEQ'];
                $Prodag_rec["DAGDES"] = $Itedag_rec['ITDDES'];
                $Prodag_rec["DAGKEY"] = $Itedag_rec["ITDKEY"];
                $Prodag_rec["DAGVAL"] = $Itedag_rec["ITDVAL"];
                $Prodag_rec["DAGPAK"] = $keyPassoPropas;
                $Prodag_rec["DAGSET"] = $keyPassoPropas . "_01";
                try {
                    $nrow = ItaDB::DBInsert($this->getPROTDB(), "PRODAG", "ROWID", $Prodag_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function GetProdst($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRODST WHERE DSTSET = '" . $Codice . "'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRODST WHERE ".$this->getPROTDB()->subString('DSTSET',1,10)." = '$Codice'";
        } else if ($tipoRic == 'desc') {
            $sql = "SELECT * FROM PRODST WHERE DSTDES = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRODST WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            switch ($order) {
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
                default:
                    asort($sortable_array);
                    break;
            }
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }

    function CheckUltimo($ArrayElement, $threeArray) {
        $ultimo = false;
        $parent = $ArrayElement['parent'];
        $conta = 0;
        foreach ($threeArray as $elemento) {
            if ($elemento['parent'] == $parent) {
                $conta = $conta + 1;
            }
        }
        if ($conta == 1) {
            $ultimo = true;
        } else {
            $ultimo = false;
        }
        return $ultimo;
    }

    function cleanArrayTree($altriDati) {
        foreach ($altriDati as $key => $dato) {
            if ($dato['level'] == 0 && $dato['parent'] == false && $dato['isLeaf'] == 'false') {
                unset($altriDati[$key]);
            }
        }
        return $altriDati;
    }

    function CheckPadre($Element, $threeArray, $campoPadreParent) {
        $parent = $Element['parent'];
        foreach ($threeArray as $key1 => $elemento) {
            if ($elemento[$campoPadreParent] == $parent && $elemento['isLeaf'] !== true) {
                $keyPadre = $key1;
                break;
            }
        }
        return $keyPadre;
    }

    function ordinaPrioritaArrayDag($pratica) {
        if (!$pratica) {
            return false;
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' ORDER BY DAGPRI";
        $prodag_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $new_priorita = 0;
        foreach ($prodag_tab as $dato) {
            if ($dato['DAGPRI'] == 0) {
                continue;
            }
            $new_priorita +=10;
            $dato['DAGPRI'] = $new_priorita;
            try {
                $nrow = ItaDB::DBUpdate($this->getPROTDB(), "PRODAG", "ROWID", $dato);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                Out::msgStop("Errore", $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    public function ImportXmlFilePropas($XMLpassi, $partiDa, $pratica) {
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($XMLpassi);
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (array_key_exists('ITEPAS', $arrayXml)) {
            if ($this->insertRecordPropas($partiDa, $pratica)) {
                $arrayItepas = $arrayXml['ITEPAS'];
                //  Registro su PROPAS
                if ($arrayItepas[0]) {      // sono presenti più passi
                    //$insert_Info = "Oggetto: Importazione Passo pratica" . $pratica;
                    foreach ($arrayItepas as $itepasRec) {
                        $praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $itepasRec['ITECLT']['@textNode'] . "'", false);
                        $propas_rec = array();
                        $partiDa = $partiDa + 10;
                        $propas_rec['PRONUM'] = $pratica;
                        $propas_rec['PROPRO'] = $itepasRec['ITECOD']['@textNode'];
                        $propas_rec['PROSEQ'] = $partiDa;
                        $propas_rec['PRORPA'] = $itepasRec['ITERES']['@textNode'];
                        $propas_rec['PROUOP'] = $itepasRec['ITEOPE']['@textNode'];
                        $propas_rec['PROSET'] = $itepasRec['ITESET']['@textNode'];
                        $propas_rec['PROSER'] = $itepasRec['ITESER']['@textNode'];
                        $propas_rec['PROGIO'] = $itepasRec['ITEGIO']['@textNode'];
                        $propas_rec['PROCLT'] = $itepasRec['ITECLT']['@textNode'];
                        $propas_rec['PROCOM'] = $itepasRec['ITECOM']['@textNode'];
                        $propas_rec['PRODTP'] = $praclt_rec['CLTDES'];
                        $propas_rec['PRODPA'] = $itepasRec['ITEDES']['@textNode'];
                        $propas_rec['PROQST'] = $itepasRec['ITEQST']['@textNode'];
                        $propas_rec['PRODAT'] = $itepasRec['ITEDAT']['@textNode'];
                        $propas_rec['PROIRE'] = $itepasRec['ITEIRE']['@textNode'];
                        $propas_rec['PROPUB'] = $itepasRec['ITEPUB']['@textNode'];
                        $propas_rec['PROZIP'] = $arrayItepas['ITEZIP']['@textNode'];
                        $propas_rec['PRODRR'] = $arrayItepas['ITEDRR']['@textNode'];
                        $propas_rec['PRODIS'] = $arrayItepas['ITEDIS']['@textNode'];
                        $propas_rec['PRODOW'] = $arrayItepas['ITEDOW']['@textNode'];
                        $propas_rec['PROUPL'] = $arrayItepas['ITEUPL']['@textNode'];
                        $propas_rec['PROMLT'] = $arrayItepas['ITEMLT']['@textNode'];
                        $propas_rec['PROPAK'] = $this->PropakGenerator($pratica);
                        $propas_rec['PROVPA'] = '';
                        $propas_rec['PROVPN'] = '';
                        $propas_rec['PROCTR'] = '';
                        if ($itepasRec['ITECTR']['@textNode'] != '') {
                            $propas_rec['PROCTR'] = $itepasRec['ITECTR']['@textNode'];
                        }
                        if ($itepasRec['ITECOM']['@textNode'] != 0) {
                            if ($itepasRec['ITEINT']['@textNode'] == 1)
                                $propas_rec['PROINT'] = $itepasRec['ITEINT']['@textNode'];
                            if ($itepasRec['ITECDE']['@textNode'] != "")
                                $propas_rec['PROCDE'] = $itepasRec['ITECDE']['@textNode'];
                        }
                        $propas_rec_utf8_decode = itaLib::utf8_decode_array($propas_rec);
                        try {
                            $nrow = ItaDB::DBInsert($this->getPROTDB(), "PROPAS", 'ROWID', $propas_rec_utf8_decode);
                            if ($nrow != 1) {
                                return false;
                            }
                        } catch (Exception $exc) {
                            Out::msgStop("Errore", $exc->getMessage());
                            return false;
                        }
                    }
                } else {        // è presente un solo passo
                    $praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $arrayItepas['ITECLT']['@textNode'] . "'", false);
                    $propas_rec = array();
                    $partiDa = $partiDa + 10;
                    $propas_rec['PRONUM'] = $pratica;
                    $propas_rec['PROPRO'] = $arrayItepas['ITECOD']['@textNode'];
                    $propas_rec['PROSEQ'] = $partiDa;
                    $propas_rec['PRORPA'] = $arrayItepas['ITERES']['@textNode'];
                    $propas_rec['PROUOP'] = $arrayItepas['ITEOPE']['@textNode'];
                    $propas_rec['PROSET'] = $arrayItepas['ITESET']['@textNode'];
                    $propas_rec['PROSER'] = $arrayItepas['ITESER']['@textNode'];
                    $propas_rec['PROGIO'] = $arrayItepas['ITEGIO']['@textNode'];
                    $propas_rec['PROCLT'] = $arrayItepas['ITECLT']['@textNode'];
                    $propas_rec['PROCOM'] = $arrayItepas['ITECOM']['@textNode'];
                    $propas_rec['PRODTP'] = $praclt_rec['CLTDES'];
                    $propas_rec['PRODPA'] = $arrayItepas['ITEDES']['@textNode'];
                    $propas_rec['PROQST'] = $arrayItepas['ITEQST']['@textNode'];
                    $propas_rec['PRODAT'] = $arrayItepas['ITEDAT']['@textNode'];
                    $propas_rec['PROIRE'] = $arrayItepas['ITEIRE']['@textNode'];
                    $propas_rec['PROZIP'] = $arrayItepas['ITEZIP']['@textNode'];
                    $propas_rec['PRODRR'] = $arrayItepas['ITEDRR']['@textNode'];
                    $propas_rec['PRODIS'] = $arrayItepas['ITEDIS']['@textNode'];
                    $propas_rec['PRODOW'] = $arrayItepas['ITEDOW']['@textNode'];
                    $propas_rec['PROUPL'] = $arrayItepas['ITEUPL']['@textNode'];
                    $propas_rec['PROMLT'] = $arrayItepas['ITEMLT']['@textNode'];
                    $propas_rec['PROPAK'] = $this->PropakGenerator($pratica);
                    $propas_rec['PROVPA'] = '';
                    $propas_rec['PROVPN'] = '';
                    $propas_rec['PROCTR'] = '';
                    if ($arrayItepas['ITEVPA']['@textNode'] != '') {
                        $propas_rec['PROVPA'] = $propas_rec['PRONUM'] . substr($arrayItepas['ITEVPA']['@textNode'], 6);
                    }
                    if ($arrayItepas['ITEVPN']['@textNode'] != '') {
                        $propas_rec['PROVPN'] = $propas_rec['PRONUM'] . substr($arrayItepas['ITEVPN']['@textNode'], 6);
                    }
                    if ($arrayItepas['ITECTR']['@textNode'] != '') {
                        $propas_rec['PROCTR'] = $arrayItepas['ITECTR']['@textNode'];
                    }
                    if ($arrayItepas['ITECOM']['@textNode'] != 0) {
                        if ($arrayItepas['ITEINT']['@textNode'] == 1)
                            $propas_rec['PROINT'] = $arrayItepas['ITEINT']['@textNode'];
                        if ($arrayItepas['ITECDE']['@textNode'] != "")
                            $propas_rec['PROCDE'] = $arrayItepas['ITECDE']['@textNode'];
                    }
                    $propas_rec_utf8_decode = itaLib::utf8_decode_array($propas_rec);
                    try {
                        $nrow = ItaDB::DBInsert($this->getPROTDB(), "PROPAS", 'ROWID', $propas_rec_utf8_decode);
                        if ($nrow != 1) {
                            return false;
                        }
                    } catch (Exception $exc) {
                        Out::msgStop("Errore", $exc->getMessage());
                        return false;
                    }
                }
            } else {
                Out::msgStop("Errore", "Procedura di importazione passi interrotta per errore nello spostamento sequenza.");
                return false;
            }
        } else {
            Out::msgStop("Errore", "File di importazione passi non è conforme.");
            return false;
        }
        return true;
    }

    public function insertRecordPropas($partiDa, $pratica) {
        if (!$partiDa == 0) {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $pratica . "' AND PROSEQ > '" . $partiDa . "' ORDER BY PROSEQ";
            $propas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
            if ($propas_tab) {
                foreach ($propas_tab as $propas_rec) {
                    $propas_rec['PROSEQ'] = $propas_rec['PROSEQ'] + 500;
                    try {
                        $nrow = ItaDB::DBUpdate($this->getPROTDB(), "PROPAS", "ROWID", $propas_rec);
                        if ($nrow == -1) {
                            return false;
                        }
                    } catch (Exception $exc) {
                        Out::msgStop("Errore", $exc->getMessage());
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function CaricaAllegatoDaZip($allegato, $arrayAlle) {
        $trovato = false;
        if ($allegato['isLeaf'] == "false") {// E UNA CARTELLA
            $dirname = pathinfo($allegato['FILEPATH'], PATHINFO_BASENAME);
            $arrayDirFile = $this->loadSelectDirFile($allegato['FILEPATH']);
            foreach ($arrayAlle as $alle) {
                foreach ($arrayDirFile as $file) {
                    if ($alle['NAME'] == $file['NAME']) {
                        $trovato = true;
                        break;
                    }
                }
            }
            if ($trovato == true) {
                Out::msgInfo("Inserimento Allegato", "Files della cartella $dirname già inseriti");
                return false;
            }
            foreach ($arrayDirFile as $file) {
                $seq = count($arrayAlle) + 1;
                $file['SEQ'] = $seq;
                $arrayAlle[$seq] = $file;
            }
            return array(
                "Allegati" => $arrayAlle,
                "daCartella" => true
            );
        } else { // E UN FILE
            foreach ($arrayAlle as $alle) {
                if ($alle['NAME'] == $allegato['NAME']) {
                    $trovato = true;
                    break;
                }
            }
            if ($trovato == true) {
                Out::msgInfo("Inserimento Allegato", "l'allegato " . $allegato['NAME'] . " è gia presente");
                return false;
            }
            $key = count($arrayAlle) + 1;
            $allegato['SEQ'] = $key;
            $allegato['level'] = 1;
            $allegato['parent'] = "seq_GEN";
            return array(
                "Allegato" => $allegato,
                "daFile" => true
            );
        }
    }

    function loadSelectDirFile($zipDir) {
        $ds = "/";
        $bad = array('.', '..');
        $files = array_diff(scandir($zipDir), $bad);
        $arrayDirFile = array();
        foreach ($files as $ftmp) {
            $key = count($arrayDirFile) + 1;
            if (is_dir($zipDir . $ds . $ftmp) == true) {
                $arrayDirFile = $this->loadSelectDirFile($zipDir . $ds . $ftmp);
            } elseif (is_file($zipDir . $ds . $ftmp) == true) {
                $arrayFile = array();
                $arrayFile['SEQ'] = $key;
                $arrayFile['NAME'] = '<span style = "color:orange;">' . $ftmp . '</span>';
                $arrayFile['INFO'] = 'GENERALE';
                $arrayFile['NOTE'] = "File originale: " . $ftmp;
                //Valorizzo Array
                $arrayFile['FILENAME'] = md5(rand() * time()) . "." . pathinfo($zipDir . $ds . $ftmp, PATHINFO_EXTENSION);
                $arrayFile['FILEINFO'] = $ftmp;
                $arrayFile['FILEPATH'] = $zipDir . $ds . $ftmp;
                $arrayFile['FILEORIG'] = $ftmp;
                $arrayFile['ROWID'] = 0;
                $arrayFile['level'] = 1;
                $arrayFile['parent'] = "seq_GEN";
                $arrayFile['expanded'] = 'false';
                $arrayFile['loaded'] = 'false';
                $arrayFile['isLeaf'] = 'true';
                $arrayDirFile[$key] = $arrayFile;
            }
        }
        return $arrayDirFile;
    }

    public function checkStatoFascicolo($geskey) {
        $proges_rec = $this->GetProges($geskey, 'geskey');
        if ($proges_rec['GESDCH']) {
            return false;
        } else {
            return true;
        }
    }

    public function GetIncDagset($datiAgg) {
        foreach ($datiAgg as $campo) {
            if (strlen($campo['DAGSET']) == 35) {
                $array1[] = $campo['DAGSET'];
            }
        }
        $array1 = $this->array_sort($array1, "DAGSET");
        $incDagset = substr(end($array1), -2) + 1;
        $incDagset = str_repeat("0", 2 - strlen($incDagset)) . $incDagset;
        return $incDagset;
    }

}

?>