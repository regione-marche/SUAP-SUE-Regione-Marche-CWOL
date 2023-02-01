<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    02.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibRicevute.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';

class proLib {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    const PROSTATO_ANNULLATO = 4;
    const PROSTATO_COMPLETO = 1; // Non ancora usato
    const PROSTATO_INCOMPLETO = 3; // Non ancora usato
    const TIPOPROT_ARRIVO = "A";
    const TIPOPROT_PARTENZA = "P";
    const TIPOPROT_COMUNICAZIONE_FORMALE = "C";
    const TIPOPROT_INDICE = "I";
    const TIPOPROT_FASCICOLO = "F";
    const TIPOPROT_SOTTO_FASCICOLO = "N";
    const TIPOSPED_ANALOGICA = '';
    const TIPOSPED_MAIL = '1';
    const TIPOSPED_PEC = '2';
    const TIPOSPED_DIRETTA = '3';
    const TIPOSPED_CART = '4';

    public $PROT_DB;
    public $COMUNI_DB;
    public $ANEL_DB;
    public $Param;
    private $errCode;
    private $errMessage;
    private $currTit;
    private $currTitDate;
    private $ditta = "";

    function __construct($ditta = "") {
        try {
            if ($ditta) {
                $this->ditta = $ditta;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
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

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                if ($this->ditta) {
                    $this->PROT_DB = ItaDB::DBOpen('PROT', $this->ditta);
                } else {
                    $this->PROT_DB = ItaDB::DBOpen('PROT');
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getAOOAmministrazione() {
        if (!$this->Param) {
            $this->Param = array();
            $anaent_rec = $this->GetAnaent("26");
            $this->Param['AOO'] = $anaent_rec['ENTDE2'];
        }
        return $this->Param['AOO'];
    }

    public function getIPAAmministrazione() {
        if (!$this->Param) {
            $this->Param = array();
            $anaent_rec = $this->GetAnaent("26");
            $this->Param['IPA'] = $anaent_rec['ENTDE1'];
        }
        return $this->Param['IPA'];
    }

    public function setCOMUNIDB($COMUNI_DB) {
        $this->COMUNI_DB = $COMUNI_DB;
    }

    public function getCOMUNIDB() {
        if (!$this->COMUNI_DB) {
            try {
                $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->COMUNI_DB;
    }

    public function setANELDB($ANEL_DB) {
        $this->ANEL_DB = $ANEL_DB;
    }

    public function getANELDB() {
        if (!$this->ANEL_DB) {
            try {
                $this->ANEL_DB = ItaDB::DBOpen('ANEL');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ANEL_DB;
    }

    public function GetUfficioRuoloDestinatari($ufficio, $ruolo, $codice = '') {
        $where = " AND UFFFI1__2='$ruolo'";
        $where .= ($codice) ? " AND UFFKEY1__2='$codice'" : "";
        $Anauff_rec = $this->GetAnauff($ufficio, 'codice');
        $RuoloUfficio = $this->GetUfficiDestinatari($ufficio, '', 'codice', true, $where);
        if (!$RuoloUfficio && $Anauff_rec['CODICE_PADRE']) {
            $RuoloUfficio = $this->GetUfficioRuoloDestinatari($Anauff_rec['CODICE_PADRE'], $ruolo, $codice);
        }
        return $RuoloUfficio;
    }

    /**
     * 
     * @param type $codice  Codice da cercare in funzione del parametro $tipoRic 
     * @param type $tipoRic tipo di ricerca: 
     *                      'codice'    cerca per codice destinatario interno
     *                      'nome'      cerca per numonativo case insensitive
     *                      'rowid'     cerca per id record      
     * @param type $tutti   'si' interni ed esterni , 'no' solo interni
     * @param type $multi   true ritorna un array di records, false torna un record
     * @return type 
     */
    public function GetAnamed($codice, $tipoRic = 'codice', $tutti = 'si', $multi = false, $noannullati = true) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAMED WHERE MEDCOD='" . $codice . "'";
        } else if ($tipoRic == 'nome') {
//$sql = "SELECT * FROM ANAMED WHERE MEDNOM LIKE '%" . addslashes($codice) . "%'";
            $sql = "SELECT * FROM ANAMED WHERE " . $this->getPROTDB()->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($codice)) . "%'";
        } else {
            $sql = "SELECT * FROM ANAMED WHERE ROWID='$codice'";
        }
        if ($tutti != 'si') {
            $sql .= " AND MEDUFF<>''";
        }
        if ($noannullati) {
            $sql .= " AND MEDANN=0";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetUfficiDestinatari($codiceUff, $medcod = '', $tipoRic = 'codice', $multi = true, $where = '') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT ANAMED.ROWID AS ROWID, ANAMED.MEDCOD AS MEDCOD, ANAMED.MEDNOM, UFFDES.UFFKEY, UFFDES.UFFCOD, UFFDES.UFFSCA
                FROM ANAMED LEFT OUTER JOIN UFFDES
                ON ANAMED.MEDCOD = UFFDES.UFFKEY WHERE (UFFDES.UFFCOD='" . $codiceUff . "' $where ) AND ANAMED.MEDANN=0 AND UFFCESVAL = '' ";
        }
        if ($medcod <> '') {
            $multi = false;
            $sql = $sql . " AND MEDCOD='" . $medcod . "'";
        }
        $uffdest_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $uffdest_tab;
    }

    public function GetAnauff($codice, $tipo = 'codice') {
        $multi = false;
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFCOD='" . $codice . "'";
        } else if ($tipo == 'uffser') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFSER='" . trim($codice) . "'";
            $multi = true;
        } else if ($tipo == 'uffsegser') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFSEGSER='" . trim($codice) . "'";
            $multi = true;
        } else if ($tipo == 'uffsegcla') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFSEGCLA='" . $codice . "'";
            $multi = true;
        } else if ($tipo == 'uffSdi') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFFATCODUNICO ='" . $codice . "'";
            $multi = true;
        } else {
            $sql = "SELECT * FROM ANAUFF WHERE ROWID='$codice'";
        }

        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetUffdes($codice, $tipo = 'uffkey', $uffsca = true, $ordina = ' ORDER BY UFFFI1__3 DESC', $soloValidi = false) {
        $multi = true;
        if ($soloValidi) {
            $whereValidi = " AND UFFCESVAL=''";
        }
        if ($tipo == 'uffkey') {
            $sql = "SELECT * FROM UFFDES WHERE UFFKEY='" . $codice . "'" . $whereValidi . $ordina;
        } else if ($tipo == 'uffcod') {
            $sql = "SELECT * FROM UFFDES WHERE UFFCOD='" . $codice . "'" . $whereValidi;
            if ($uffsca) {
                $sql .= " AND UFFSCA='1'";
            }
        } else if ($tipo == 'ruolo') {
            $multi = false;
            $sql = "SELECT * FROM UFFDES WHERE UFFKEY='" . $codice['UFFKEY'] . "' AND UFFCOD='" . $codice['UFFCOD'] . "'" . $whereValidi;
        } else {
            $multi = false;
            $sql = "SELECT * FROM UFFDES WHERE ROWID='$codice'" . $whereValidi;
        }
        $uffdes_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $uffdes_rec;
    }

    public function getUfftit($codice, $tipo = 'uffcod', $versione = '') {
        if ($versione === '' || $versione === null) {
            $versione = $this->GetTitolarioCorrente();
        }
        $multi = true;
        if ($tipo == 'uffcod') {
            $sql = "SELECT * FROM UFFTIT WHERE UFFCOD='$codice' AND VERSIONE_T=$versione";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM UFFTIT WHERE ROWID=$codice";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnatip($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANATIP WHERE TIPCOD='" . $codice . "' AND TIPANN = 0 ";
        } else {
            $sql = "SELECT * FROM ANATIP WHERE ROWID='$codice'";
        }
        $anatip_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anatip_rec;
    }

    public function GetAnaatti($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAATTI WHERE CODATTO=$codice";
        } else {
            $sql = "SELECT * FROM ANAATTI WHERE ROWID=$codice";
        }
        $anaatti_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anaatti_rec;
    }

    public function GetAnaqua($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAQUA WHERE CODICE=$codice";
        } else {
            $sql = "SELECT * FROM ANAQUA WHERE ROWID=$codice";
        }
        $anaqua_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anaqua_rec;
    }

    public function GetAnaesito($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAESITO WHERE CODICE=$codice";
        } else {
            $sql = "SELECT * FROM ANAESITO WHERE ROWID=$codice";
        }
        $record_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $record_rec;
    }

    public function GetTabdag($codice, $tipo = 'codice', $rowidClasse = 0, $chiave = "", $prog = 0, $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse";
        } elseif ($tipo == 'chiave') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse AND TDAGCHIAVE = '$chiave'";
        } elseif ($tipo == 'progressivo') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse AND TDAGCHIAVE = '$chiave' AND TDPROG = $prog";
        } else {
            $sql = "SELECT * FROM TABDAG WHERE ROWID=$codice";
        }
        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnaspese($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASPESE WHERE CODSPE=$codice";
        } else {
            $sql = "SELECT * FROM ANASPESE WHERE ROWID=$codice";
        }
        $anaspese_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anaspese_rec;
    }

    public function GetAnarice($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANARICE WHERE CODICE=$codice";
        } else {
            $sql = "SELECT * FROM ANARICE WHERE ROWID=$codice";
        }
        $anarice_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anarice_rec;
    }

    public function GetProges($codice, $tipo = 'codice') {
        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM PROGES WHERE GESKEY='$codice'";
                break;
            case 'gesnum':
                $sql = "SELECT * FROM PROGES WHERE GESNUM='$codice'";
                break;
            case 'geskey':
                $sql = "SELECT * FROM PROGES WHERE GESKEY='$codice'";
                break;
            default:
                $sql = "SELECT * FROM PROGES WHERE ROWID=$codice";
                break;
        }
        $proges_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $proges_rec;
    }

    public function GetAnadir($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADIR WHERE CODICE=$codice";
        } else {
            $sql = "SELECT * FROM ANADIR WHERE ROWID=$codice";
        }
        $anadir_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anadir_rec;
    }

    public function GetTitolarioCorrente($oggi = null, $checkDataFine = true) {
        $this->AssegnaTitolarioCorrente($oggi, $checkDataFine);
        return $this->currTit;
    }

    public function AssegnaTitolarioCorrente($oggi = null, $checkDataFine = true) {
        if ($oggi === null) {
            $oggi = date('Ymd');
        }
        $sqlCheckDataFine = '';
        if ($checkDataFine) {
            $sqlCheckDataFine = " AND DATAFINE='' ";
        }
        $sql = "SELECT * FROM AACVERS WHERE DATAINIZ<='$oggi' $sqlCheckDataFine AND FLAG_DIS=0 ORDER BY DATAINIZ DESC, VERSIONE_T DESC";
        $aacvers_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);

        if ($aacvers_tab) {
            $titolarioCorrente = $aacvers_tab[0]['VERSIONE_T'];
        } else {
            $titolarioCorrente = 0;
        }

        $this->currTit = $titolarioCorrente;
        $this->currTitDate = $oggi;
    }

    public function GetAnacat($versione, $codice = '', $tipo = 'codice', $soloValidi = true) {
        if ($tipo == 'codice') {
            if ($versione === '') {
                $versione = $this->GetTitolarioCorrente();
            }
            $sql = "SELECT * FROM ANACAT WHERE VERSIONE_T = $versione AND CATCOD='" . $codice . "' ";
            if ($soloValidi) {
                $sql .= " AND CATDAT = '' ";
            }
        } else {
            $sql = "SELECT * FROM ANACAT WHERE ROWID='$codice'";
        }
        $anacat_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anacat_rec;
    }

    public function GetAnacla($versione, $codice = '', $tipo = 'codice', $soloValidi = true) {
        if ($versione === '') {
            $versione = $this->GetTitolarioCorrente();
        }
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANACLA WHERE VERSIONE_T = $versione AND CLACCA='$codice'";
            if ($soloValidi) {
                $sql .= " AND CLADAT = '' ";
            }
        } else if ($tipo == 'clacod') {
            $sql = "SELECT * FROM ANACLA WHERE VERSIONE_T = $versione AND CLACOD='$codice'";
            if ($soloValidi) {
                $sql .= " AND CLADAT = '' ";
            }
        } else {
            $sql = "SELECT * FROM ANACLA WHERE ROWID='$codice'";
        }
        $anacla_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anacla_rec;
    }

    public function GetAnafas($versione, $codice = '', $tipo = 'fasccf', $soloValidi = true) {
        if ($versione === '') {
            $versione = $this->GetTitolarioCorrente();
        }
        if ($tipo == 'fasccf') {
            $sql = "SELECT * FROM ANAFAS WHERE VERSIONE_T = $versione AND FASCCF='$codice'";
            if ($soloValidi) {
                $sql .= " AND FASDAT = '' ";
            }
        } else if ($tipo == 'codice') {
            if ($versione === '') {
                $versione = $this->GetTitolarioCorrente();
            }
            $sql = "SELECT * FROM ANAFAS WHERE VERSIONE_T = $versione AND FASCOD='$codice'";
            if ($soloValidi) {
                $sql .= " AND FASDAT = '' ";
            }
        } else {
            $sql = "SELECT * FROM ANAFAS WHERE ROWID='$codice'";
        }
        $anafas_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anafas_rec;
    }

    public function GetAnaorg($cod, $tipo = 'codice', $ccf = '', $anno = '', $uo = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAORG WHERE ORGCOD='$cod' AND ORGCCF='$ccf'";
//            if ($anno != '') {
            $sql .= " AND ORGANN='$anno'";
//            }
            if ($uo != '') {
                $sql .= " AND ORGUOF='$uo'";
            }
            $sql .= " ORDER BY ORGANN DESC";
        } else if ($tipo == 'orgkey') {
            $sql = "SELECT * FROM ANAORG WHERE ORGKEY='$cod'";
        } else {
            $sql = "SELECT * FROM ANAORG WHERE ROWID='$cod'";
        }
//        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetAnaTipoDoc($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANATIPODOC WHERE CODICE='$codice' ";
        } else {
            $sql = "SELECT * FROM ANATIPODOC WHERE ROWID='$codice'";
        }
        $anacla_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $anacla_rec;
    }

    public function GetAnaTipoNode($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAORDNODE WHERE TIPONODE='$codice' ";
        } else {
            $sql = "SELECT * FROM ANAORDNODE WHERE ROWID='$codice'";
        }
        $anacla_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $anacla_rec;
    }

    public function GetOrgNode($codice, $tipoRicerca = 'codice', $tipoProtocollo = '') {
        if ($tipoRicerca == 'codice') {
            $sql = "SELECT * FROM ORGNODE WHERE PRONUM='$codice' AND PROPAR='$tipoProtocollo'";
        } else if ($tipoRicerca == 'orgkey') {
            $sql = "SELECT * FROM ORGNODE WHERE ORGKEY='$codice'";
        } else if ($tipoRicerca == 'rowid') {
            $sql = "SELECT * FROM ORGNODE WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetOrgConn($codiceProtocollo, $tipoRicerca = 'codice', $tipoProtocollo = '') {
        if ($tipoRicerca == 'codice') {
            $multi = false;
            $sql = "SELECT * FROM ORGCONN WHERE PRONUM='$codiceProtocollo' AND PROPAR='$tipoProtocollo' AND CONNDATAANN = '' ";
        } else if ($tipoRicerca == 'rowid') {
            $multi = false;
            $sql = "SELECT * FROM ORGCONN WHERE ROWID=$codiceProtocollo";
        }
// IPOTETICO, ANCORA NON USATO!
//        else if ($tipoRicerca == 'parent') {
//            $multi = true;
//            $sql = "SELECT * FROM ORGCONN WHERE PRONUMPARENT='$codiceProtocollo' AND PROPARPARENT='$tipoProtocollo' ORDER BY ORGSEQ";
//        }
//        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetNewFascicolo($titolario, $anno, $uniOpe, $descrizione, $progressivo = '') {
        $retLock = ItaDB::DBLock($this->getPROTDB(), "ANAORG", "", "", 20);
        if ($retLock['status'] !== 0) {
            return false;
        }
        $chiaveIniziale = $titolario . "." . $anno . ".";
        if (!$progressivo) {
            $sql = "SELECT MAX(ORGCOD) AS ULTIMO FROM ANAORG WHERE ORGKEY<>'' AND ORGKEY LIKE '$chiaveIniziale%'";
            $Anaorg_max = itaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
            $progressivo = str_pad((int) $Anaorg_max['ULTIMO'] + 1, 6, "0", STR_PAD_LEFT);
        }
        $fascicolo_rec = array();
        $fascicolo_rec['ORGCOD'] = $progressivo;
        $fascicolo_rec['ORGDES'] = $descrizione;
        $fascicolo_rec['ORGAPE'] = date('Ymd');
        $fascicolo_rec['ORGCCF'] = $titolario;
        $fascicolo_rec['ORGUOF'] = $uniOpe;
        $fascicolo_rec['ORGANN'] = $anno;
        $fascicolo_rec['ORGKEY'] = $chiaveIniziale . $progressivo;
        try {
            ItaDB::DBInsert($this->getPROTDB(), 'ANAORG', 'ROWID', $fascicolo_rec);
            $rowid = $this->getPROTDB()->getLastId();
        } catch (Exception $exc) {
            itaDB::DBUnLock($retLock['lockID']);
            Out::msgStop("Errore db", $exc->getMessage());
            return false;
        }
        $esito = $this->getNewFascicoloAnapro($fascicolo_rec);
        if ($esito === false) {
            ItaDB::DBUnLock($retLock['lockID']);
            Out::msgStop("Attenzione!", "Errore nella creazione del Archivio Fascicolo.");
            return false;
        }

        ItaDB::DBUnLock($retLock['lockID']);
        return $rowid;
    }

    public function getNewFascicoloAnapro($fascicolo_rec) {

        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();

        $codice = $protObj->prenotaRepertorioFascicolo('LEGGI', $fascicolo_rec['ORGANN']);
        $anapro_new = array();
        $anapro_new['PRONUM'] = $codice;
        $anapro_new['PROPAR'] = 'F';
        $anapro_new['PRODAR'] = $fascicolo_rec['ORGAPE'];

        $anapro_new['PROCAT'] = substr($fascicolo_rec['ORGCCF'], 0, 4);
        $anapro_new['PROCCA'] = substr($fascicolo_rec['ORGCCF'], 0, 8);
        $anapro_new['PROCCF'] = $fascicolo_rec['ORGCCF'];
        $anapro_new['PROARG'] = $fascicolo_rec['ORGCOD'];
        $anapro_new['PRORDA'] = $fascicolo_rec['ORGAPE'];
        $anapro_new['PROROR'] = date('H:i:s');
        $anapro_new['PROORA'] = date('H:i:s');
        $anapro_new['PROUOF'] = $fascicolo_rec['ORGUOF'];
        $anapro_new['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anapro_new['PROLOG'] = "999" . substr(App::$utente->getKey('nomeUtente'), 0, 7) . date('d/m/y');
        $anapro_new['PROFASKEY'] = $fascicolo_rec['ORGKEY'];

        $profilo = proSoggetto::getProfileFromIdUtente();
        if (!$profilo['COD_SOGGETTO']) {
            Out::msgStop("Attenzione!", "Configurare il Profilo Utente con il Destinatario della Pianta Organica.");
            return false;
        }
        $anamed_rec = $this->GetAnamed($profilo['COD_SOGGETTO']);
        $anapro_new['PROCON'] = $anamed_rec['MEDCOD'];
        $anapro_new['PRONOM'] = $anamed_rec['MEDNOM'];


        include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
        $segnatura = proSegnatura::getStringaSegnatura($anapro_new);
        if (!$segnatura) {
            return false;
        }
        $anapro_new['PROSEG'] = $segnatura;

        try {
            ItaDB::DBInsert($this->getPROTDB(), 'ANAPRO', 'ROWID', $anapro_new);
            $rowid = $this->getPROTDB()->getLastId();
            $anaproNew_rec = $this->GetAnapro($anapro_new['PRONUM'], 'codice', $anapro_new['PROPAR']);
            if (!$anaproNew_rec) {
                return false;
            }
            $risultato = $protObj->saveOggetto($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $fascicolo_rec['ORGDES']);
            if (!$risultato) {
                return false;
            }

            $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
            $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
            $ananom_rec['NOMPAR'] = $anaproNew_rec['PROPAR'];
            ItaDB::DBInsert($this->getPROTDB(), 'ANANOM', 'ROWID', $ananom_rec);

            $iter = proIter::getInstance($this, $anaproNew_rec);
            $iter->sincIterProtocollo();
            return $rowid;
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
    }

    public function GetAnadog($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADOG WHERE DOGCOD='$codice'";
        } else {
            $sql = "SELECT * FROM ANADOG WHERE ROWID='$codice'";
        }
        $anadog_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anadog_rec;
    }

    public function GetOggUtenti($utente, $oggetto = '', $tipo = 'codice') {
        $multi = true;
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM OGGUTENTI WHERE UTELOG='$utente'";
            if ($oggetto != '') {
                $sql .= " AND DOGCOD='$oggetto'";
                $multi = false;
            }
        } else {
            $sql = "SELECT * FROM OGGUTENTI WHERE ROWID=$utente";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnatsp($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANATSP WHERE TSPCOD='$codice'";
        } else {
            $sql = "SELECT * FROM ANATSP WHERE ROWID='$codice'";
        }
        $anatsp_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anatsp_rec;
    }

    public function GetPrealb($numero, $anno = '', $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PREALB WHERE ALBNUM='" . $numero . "' AND ALBANN='" . $anno . "'";
        } else if ($tipo == 'albind') {
            $sql = "SELECT * FROM PREALB WHERE ALBIND='$numero'";
        } else {
            $sql = "SELECT * FROM PREALB WHERE ROWID=$numero";
        }
        $prealb_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $prealb_rec;
    }

    public function GetAnaent($codice, $tipo = 'entkey') {
        if ($tipo == 'entkey') {
            $sql = "SELECT * FROM ANAENT WHERE ENTKEY='$codice'";
        } else {
            $sql = "SELECT * FROM ANAENT WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetAnaogg($codice, $tipoProt = '') {
        $sql = "SELECT * FROM ANAOGG WHERE OGGNUM=$codice";
        if ($tipoProt != '') {
            $sql .= " AND OGGPAR='$tipoProt'";
        }
//        App::log('$sql');
//        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetAnanom($codice, $multi = false, $tipoProt = '') {
        $sql = "SELECT * FROM ANANOM WHERE NOMNUM=$codice";
        if ($tipoProt != '') {
            $sql .= " AND NOMPAR='$tipoProt'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnapro($codice, $tipo = 'codice', $tipoProt = '', $where = '', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAPRO WHERE (PRONUM BETWEEN '$codice' AND '$codice')";
            if ($tipoProt != '') {
                $sql .= " AND (PROPAR BETWEEN '$tipoProt' AND '$tipoProt')";
            }
            if ($where != '') {
                $sql .= " AND " . $where;
            }
        } elseif ($tipo == 'fascicolo') {
            $sql = "SELECT * FROM ANAPRO WHERE PROFASKEY='$codice' AND PROPAR='F'";
        } else {
            $sql = "SELECT * FROM ANAPRO WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetRegistro($codice, $tipo = 'codice', $anno = '', $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM REGISTRO WHERE PROGRESSIVO=$codice AND ANNO='$anno'";
            if ($where != '') {
                $sql .= " AND " . $where;
            }
        } else {
            $sql = "SELECT * FROM REGISTRO WHERE ROWID='$codice'";
        }
        $registro_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $registro_rec;
    }

    public function GetAnades($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $destipo = '', $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADES WHERE DESNUM=$codice";
            if ($tipoProt != '') {
                $sql .= " AND DESPAR='$tipoProt'";
            }
            if ($destipo == 'D') {
                $sql .= " AND (DESTIPO='' OR DESTIPO='D')";
            } else if ($destipo == 'M') {
                $sql .= " AND DESTIPO='M'";
            } else if ($destipo == 'T') {
                $sql .= " AND DESTIPO='T'";
            }
            $sql .= " $where ORDER BY DESNOM";
        } else {
            $sql = "SELECT * FROM ANADES WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetUffpro($codice, $tipo = 'codice', $multi = true, $uffpar = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM UFFPRO WHERE PRONUM=$codice";
            if ($uffpar) {
                $sql .= " AND UFFPAR='$uffpar'";
            }
            $sql .= " ORDER BY UFFCOD";
        } else {
            $sql = "SELECT * FROM UFFPRO WHERE ROWID=$codice";
            $multi = false;
        }
        $uffpro_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $uffpro_tab;
    }

    public function GetAnadoc($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADOC WHERE DOCKEY LIKE '" . $codice . $tipoProt . "%' $where";
        } else if ($tipo == 'protocollo') {
            $sql = "SELECT * FROM ANADOC WHERE DOCNUM = '$codice' AND DOCPAR = '$tipoProt' $where";
        } else if ($tipo == 'docsha2') {
            $sql = "SELECT * FROM ANADOC WHERE DOCSHA2 = '$codice'";
        } else if ($tipo == 'docrowidbase') {
            $sql = "SELECT * FROM ANADOC WHERE DOCROWIDBASE = '$codice' AND DOCROWIDBASE <> '' ";
        } else {
            $sql = "SELECT * FROM ANADOC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnadocSave($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCKEY LIKE '" . $codice . $tipoProt . "%' $where";
        } else if ($tipo == 'protocollo') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCNUM = '$codice' AND DOCPAR = '$tipoProt' $where";
        } else if ($tipo == 'docsha2') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCSHA2 = '$codice'";
        } else if ($tipo == 'docrowidbase') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCROWIDBASE = '$codice' AND DOCROWIDBASE <> '' ";
        } else {
            $sql = "SELECT * FROM ANADOCSAVE WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAnaspe($codice, $tipo = 'codice', $multi = false, $tipoProt = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASPE WHERE PRONUM=$codice";
            if ($tipoProt != '') {
                $sql .= " AND PROPAR='$tipoProt'";
            }
        } else {
            $sql = "SELECT * FROM ANASPE WHERE ROWID='$codice'";
        }
        $anaspe_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $anaspe_tab;
    }

    public function GetAnaris($codice, $tipo = 'codice', $multi = false, $tipoProt = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANARIS WHERE RISNUM=$codice";
            if ($tipoProt != '') {
                $sql .= " AND RISPAR='$tipoProt'";
            }
        } else {
            $sql = "SELECT * FROM ANARIS WHERE ROWID='$codice'";
        }
        $anaris_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $anaris_tab;
    }

    public function GetArcite($codice, $tipo = 'codice', $multi = false, $tipoProt = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ARCITE WHERE ITEPRO=$codice";
            $sql .= " AND ITEPAR='$tipoProt'";
        } else {
            if ($tipo == 'itekey') {
                $sql = "SELECT * FROM ARCITE WHERE ITEKEY='$codice'";
            } else {
                $sql = "SELECT * FROM ARCITE WHERE ROWID='$codice'";
            }
        }
        $arcite_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        return $arcite_tab;
    }

    public function GetArciteSus($codice) {
        $sql = "SELECT * FROM ARCITE WHERE ITEPRE = '" . $codice . "' AND ITEFIN = ''";
        $arcite_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $arcite_rec;
    }

    public function getAnaruoli($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANARUOLI WHERE RUOCOD='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANARUOLI WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function getAnaservizi($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASERVIZI WHERE SERCOD='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANASERVIZI WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function getAnareparc($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAREPARC WHERE CODICE='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANAREPARC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function checkRepertorioProtetto() {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
        $proLibFascicolo = new proLibFascicolo();
        $repertorio_rec = $this->getAnareparc($proLibFascicolo->repertoriofascicoli);
        if (!$repertorio_rec) {
            $repertorio_rec = array();
            $repertorio_rec['CODICE'] = $proLibFascicolo->repertoriofascicoli;
            $repertorio_rec['DESCRIZIONE'] = "REPERTORIO DEI PROCEDIMENTI AMMINISTRATIVI";
            $repertorio_rec['CARATTERE'] = "G";
            $repertorio_rec['PROGRESSIVO'] = 1;
            $repertorio_rec['TIPOPROGRESSIVO'] = "ANNUALE";
            ItaDB::DBInsert($this->getPROTDB(), 'ANAREPARC', 'ROWID', $repertorio_rec);
        }
        return $repertorio_rec;
    }

    public function getAnaseriearc($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASERIEARC WHERE CODICE='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANASERIEARC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function getAnaregistroarc($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAREGISTRIARC WHERE SIGLA='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANAREGISTRIARC WHERE ROW_ID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function getProgserie($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PROGSERIE WHERE CODICE='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM PROGSERIE WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function getProgregistro($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAREGISTRIPROG WHERE ROWID_ANAREGISTRO= '" . $codice . "' ";
        } else {
            $sql = "SELECT * FROM ANAREGISTRIPROG WHERE ROW_ID= '$codice' ";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function CheckDirectory($numeroProtocollo, $tipoProt = 'A', $ditta = '') {
        $destinazione = $this->SetDirectory($numeroProtocollo, $tipoProt, $ditta, false);
        $checkPath = pathinfo($destinazione, PATHINFO_DIRNAME);
        if (!is_dir($checkPath)) {
            $checkPath = pathinfo($checkPath, PATHINFO_DIRNAME);
            if (!is_dir($checkPath)) {
                $checkPath = pathinfo($checkPath, PATHINFO_DIRNAME);
                if (!is_dir($checkPath)) {
                    return false;
                }
            }
        }
        if (!is_writable($checkPath)) {
            return false;
        }
        return true;
    }

    public function SetDirectory($numeroProtocollo, $tipoProt = 'A', $ditta = '', $creaCartella = true) {
        $tipo = "ARRIVO";
        if ($tipoProt == 'F')
            $tipo = "FASCICOLO";
        if ($tipoProt == 'N')
            $tipo = "SOTTOFASCICOLO";
        if ($tipoProt == 'T')
            $tipo = "AZIONE";
        if ($tipoProt == 'P')
            $tipo = "PARTENZA";
        if ($tipoProt == 'C')
            $tipo = "COMUNICAZIONE";
        if ($tipoProt == 'I')
            $tipo = "INDICE";
        if ($tipoProt == 'PRATICA')
            $tipo = "PRATICA";
        if ($tipoProt == 'ABBINA')
            $tipo = "ABBINA";
        if ($tipoProt == 'ABBINAOCR') {
            $ocrPath = Config::getPath("general.itaAbbinaOcr");
            $d_dir = str_replace('$ditta', App::$utente->getKey('ditta'), $ocrPath);
            if (!is_dir($d_dir)) {
                if (!@mkdir($d_dir, 0777, true)) {
                    return false;
                }
            }
            return $d_dir;
        }
        if ($tipoProt == 'PEC')
            $tipo = "PEC";
        if ($tipoProt == 'IRIDE_EXPORT')
            $tipo = "IRIDE_EXPORT";
        if ($tipoProt == 'XML')
            $tipo = "XML";
        if ($tipoProt == 'TEMP')
            $tipo = "TEMPPATH";
        if ($tipoProt == 'W')
            $tipo = "PASSI";
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        switch ($tipo) {
            case 'ARRIVO': case 'PARTENZA': case 'COMUNICAZIONE':case 'FASCICOLO': case 'AZIONE': case 'SOTTOFASCICOLO': case 'INDICE': case 'PASSI':
                $d_nome = substr($numeroProtocollo, 0, 4) . '/' . $tipo . '/' . $tipoProt . substr($numeroProtocollo, 0, 4) . '0' . substr($numeroProtocollo, 4);
                break;
            case 'ABBINA':
                $d_nome = 'ABBINA';
                break;
            case 'TEMPPATH':
                $d_nome = 'TEMPPATH';
                break;
            case 'PEC':
                $d_nome = 'PEC';
                break;
            case 'XML':
                $d_nome = 'XML';
                break;
            case 'IRIDE_EXPORT':
                $d_nome = 'IRIDE_EXPORT';
                break;
        }
        $d_dir = Config::getPath('general.itaPrim') . 'prot' . $ditta . '/';
        if (!is_dir($d_dir . $d_nome)) {
            if ($creaCartella == true) {
                if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

    public function Diff_Date_toGiorni($data1, $data2, $abs = true) {
        $giorni = strtotime($data1) / (86400) - strtotime($data2) / (86400);
        if ($abs) {
            return round(abs($giorni));
        } else {
            return round($giorni);
        }
    }

    public function AddGiorniToData($data, $giorni = 0) {
        $risData = date('Ymd', strtotime("+$giorni day", strtotime($data)));
        return $risData;
    }

    /**
     * GENERATORE DI CHIAVE UNIVOCA PER DOCUMENTO DEL PROTOCOLLO
     * $data -> NON E' PIU' USATO
     */
    public function IteKeyGenerator($numeroProtocollo, $codDestinatario, $data, $tipoProt) {
        if ($numeroProtocollo == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella creazione della chiave univoca per l'Iter!");
            return false;
        }
        usleep(15000); // 50 millisecondi;
        list($unixTime, $centesimi) = explode(".", microtime(true));
        $centesimi = str_pad(substr($centesimi, 0, 2), "0", 2, STR_PAD_LEFT);
        $tempo = str_repeat(" ", 10 - strlen($unixTime)) . $unixTime . $centesimi;
        $iteKey = $numeroProtocollo . $tipoProt . str_pad($codDestinatario, 6, " ", STR_PAD_LEFT) . $tempo;
        return $iteKey;

//        $unixTime = explode('.', microtime(true));
//        list($sec, $msec) = explode(" ", microtime(true));        
//        $tempo = str_repeat(" ", 10 - strlen($unixTime[0])) . $unixTime[0] . str_pad(substr($unixTime[1], 0, 2), 2, " ", STR_PAD_RIGHT);
//        usleep(50);
//        $iteKey = $numeroProtocollo . $tipoProt . str_pad($codDestinatario, 6, " ", STR_PAD_LEFT) . $tempo;
//        return $iteKey;
    }

    public function getGenericTab($sql, $multi = true, $tipoDB = 'PROT') {
        if ($tipoDB == 'PROT') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        } else {
            $tabella_tab = ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
        }
        return $tabella_tab;
    }

    public function wrRecuperoFile($d_nome, $filename, $filepath) {
        $d_dir = Config::getPath('general.itaRecupero');
        if (!is_dir($d_dir . $d_nome)) {
            if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                return false;
            }
        }
        $recupero = @copy($filepath, $d_dir . $d_nome . '/' . $filename);
        return $recupero;
    }

    function controllaSequenzaProtocollo($pronum, $chkDate, $workYear, $tipoProt = 'AP') {
        $risultato = array();
        $errTitolo = '';
        $errMsg = '';
        $retSeq = 0;
        $conta = 0;
        $nonce = 0;

        // Se solo per C
        if ($tipoProt == 'C') {
            $whereControllo = " PROPAR ='C' ";
        } else {
            $whereControllo = " ( PROPAR ='A' OR  PROPAR ='P')";
        }
        // Se attiva prenotazione unica per A/P/C.
        $anaent_48 = $this->GetAnaent('48');
        if ($anaent_48['ENTDE4']) {
            $whereControllo = " ( PROPAR ='A' OR  PROPAR ='P' OR  PROPAR ='C')";
        }


        if (substr($pronum, 5) != "000001") {
            $sql_chk = "
            SELECT * FROM ANAPRO WHERE PRONUM<$pronum AND PRONUM LIKE '$workYear%' AND $whereControllo ORDER BY PRONUM DESC";
            $anaproChk_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql_chk, false, 1, 1);
            if ($anaproChk_rec) {
                if (abs(intval($pronum) - intval($anaproChk_rec['PRONUM'])) > 1) {
                    $retSeq = 1;
                    $proto1 = substr($anaproChk_rec['PRONUM'], 5);
                    $Datar1 = $anaproChk_rec['PRONRA'];

                    $errTitolo = "Controllo Sequenza Inserimento.";
                    $errMsg = "ATTENZIONE NUMERO PROTOCOLLO CON NUMERI PRECEDENTI MANCANTI<BR>" .
                            "N. " . $proto1 . " Reg. " . $Datar1;
                    $risultato['retSeq'] = $retSeq;
                    $risultato['errTitolo'] = $errTitolo;
                    $risultato['errMsg'] = $errMsg;
                    return $risultato;
                }
            }

            while (true) {
                $conta += 1;
                $key = $pronum - $conta;

//$anaproChk_rec = $this->GetAnapro($key, 'codice', '', " (PROPAR='A' OR PROPAR='P')");
                $anaproChk_rec = $this->GetAnapro($key, 'codice', '', $whereControllo);
                if ($anaproChk_rec) {
                    if ($chkDate < $anaproChk_rec['PRONRA']) {
                        $retSeq = 1;
                        $proto1 = substr($anaproChk_rec['PRONUM'], 5);
                        $Datar1 = $anaproChk_rec['PRONRA'];
                    }
                    break;
                } else {
                    $nonce += 1;
                }
                if ($nonce > 10)
                    break;
            }
        }

        $nonce = 0;
        $conta = 0;
        while (true) {
            $conta += 1;
            $key = $pronum + $conta;

            //            $key = $pronum + 1;
            //$anaproChk_rec = $this->GetAnapro($key, 'codice', '', " (PROPAR='A' OR PROPAR='P')");
            $anaproChk_rec = $this->GetAnapro($key, 'codice', '', $whereControllo);
            if ($anaproChk_rec) {
                if ($chkDate > $anaproChk_rec['PRONRA']) {
                    $retSeq = 1;
                    $proto2 = substr($anaproChk_rec['PRONUM'], 5);
                    $Datar2 = $anaproChk_rec['PRONRA'];
                }
                break;
            } else {
                $nonce += 1;
            }
            if ($nonce > 10)
                break;
        }

        /*
         * Con $nonce non ci fa nulla?..
         * Non controlla se progressivo prima ha qualche buco.
         */
        if ($retSeq != 0) {
            $errTitolo = "Controllo Sequenza Inserimento.";
            $errMsg = "ATTENZIONE NUMERO PROTOCOLLO CON DATA DI REGISTRAZIONE FUORI SEQUENZA CON<BR>" .
                    "N. " . $proto1 . " Reg. " . $Datar1 . " - N. " . $proto2 . " Reg. " . $Datar2;
        }
        if (date('Y', strtotime($chkDate)) != $workYear) {
            $retSeq = 2;
            $errTitolo = "Controllo Sequenza Inserimento.";
            $errMsg = "ATTENZIONE INCONGRUENZA TRA DATA DEL GIORNO E DATA DI REGISTRAZIONE.<BR>" .
                    "IMPOSTARE L'ANNO DELLA DATA DEL GIORNO, UGUALE ALL'ANNO DA  REGISTRARE";
        }
        $risultato['retSeq'] = $retSeq;
        $risultato['errTitolo'] = $errTitolo;
        $risultato['errMsg'] = $errMsg;
        return $risultato;
    }

    function prenotaProtocollo($dataRegistrazione, $modo, $workYear, $tipoPrenota = "AP") {
        $risultato = array();
        /*
         * Se parametro Attivo e sto LEGGENDO/AGGIORNANDO una C
         * tipoPrenota => 'APC' ovvero:
         * Prenota su stesso progressivo A/P/C
         */
        $anaent_48 = $this->GetAnaent('48');
        if ($anaent_48['ENTDE4'] && $tipoPrenota == 'C') {
            $tipoPrenota = 'APC';
        }
        /*
         * Prenoto un numero dal contatore giusto
         * e reperisco l'ultimo utilizzato
         * 
         */
        switch ($tipoPrenota) {
            case 'APC':
                $anaent_rec = $this->GetAnaent('1');
                $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE ( PROPAR ='A' OR PROPAR ='P' OR PROPAR ='C')";
                break;
            case 'AP':
                $anaent_rec = $this->GetAnaent('1');
                $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE ( PROPAR ='A' OR PROPAR ='P')";
                break;
            case 'C':
                $anaent_rec = $this->GetAnaent('23');
                $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE PROPAR ='C'";
                break;
        }
        $pronum = $workYear . "000000" + $anaent_rec['ENTDE1'];
        $anaproChkUltimo_rec = $this->getGenericTab($sql, false);

        if ($modo == "LEGGI") {
            /*
             * AZZERA PROGRESSIVO AD ANNO NUOVO
             */
            if ($anaproChkUltimo_rec['PRONUM'] != '' && $anaproChkUltimo_rec['PRONUM'] != $pronum && substr($anaproChkUltimo_rec['PRONUM'], 0, 4) != $workYear) {
                $pronum = $workYear . "000001";
                $anaent_rec['ENTDE1'] = '0';
                $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANAENT', 'ROWID', $anaent_rec);
                if ($nrow < 0) {
                    $risultato['errTitolo'] = "Errore";
                    $risultato['errMsg'] = "Aggiornameto Progressivo Protocollo non Riuscito.<br>Contattare l'assistenza Software.";
                    $risultato['pronum'] = 'Error';
                    return $risultato;
                }
            } else {
// Vecchio Metodo
            }
// Nuovo metodo  Istruzione presa nel posto di Vecchio Metodo
            $pronum = $workYear . "000000" + $anaent_rec['ENTDE1'] + 1;
// CONTROLLO SEQUENZE            
            switch ($tipoPrenota) {
                case 'APC':
                case 'AP':
                    $risultatoCS = $this->controllaSequenzaProtocollo($pronum, $dataRegistrazione, $workYear);
                    if ($risultatoCS['errMsg'] != '') {
                        $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                        $risultato['errMsg'] = $risultatoCS['errMsg'];
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }
                    $retSeq = $risultatoCS['retSeq'];
                    if ($retSeq != 0) {
                        $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                        $risultato['errMsg'] = $risultatoCS['errMsg'];
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }
                    // Controllo parametro APC
                    $where = " ( PROPAR ='A' OR PROPAR ='P')";
                    if ($tipoPrenota == 'APC') {
                        $where = " ( PROPAR ='A' OR PROPAR ='P' OR PROPAR ='C')";
                    }
                    //CONTROLLO CODICE OCCUPATO
                    $anaproChk_rec = $this->GetAnapro($pronum, 'codice', '', $where);
                    if ($anaproChk_rec) {
                        $risultato['errTitolo'] = "Inserimento Nuovo Protocollo";
                        $risultato['errMsg'] = "Il cod.progressivo  :" . substr($pronum, 4) . "/" . substr($pronum, 0, 4)
                                . " Già inserito.<br><br>Inserimento Interrotto!";
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }
                    break;
                case 'C':
                    // Nuovo controllo per le C 
                    $risultatoCS = $this->controllaSequenzaProtocollo($pronum, $dataRegistrazione, $workYear, 'C'); // con nuovo parametro.
                    if ($risultatoCS['errMsg'] != '') {
                        $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                        $risultato['errMsg'] = $risultatoCS['errMsg'];
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }
                    $retSeq = $risultatoCS['retSeq'];
                    if ($retSeq != 0) {
                        $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                        $risultato['errMsg'] = $risultatoCS['errMsg'];
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }

                    $anaproChk_rec = $this->GetAnapro($pronum, 'codice', 'C');
                    if ($anaproChk_rec) {
                        $risultato['errTitolo'] = "Inserimento Nuovo Documento Formale";
                        $risultato['errMsg'] = "Il cod.progressivo  :" . substr($pronum, 4) . "/" . substr($pronum, 0, 4)
                                . " Già inserito.<br><br>Inserimento Interrotto!";
                        $risultato['pronum'] = 'Error';
                        return $risultato;
                    }
                    break;
            }
        } else if ($modo == "AGGIORNA") {
//            $pronum = $anaproChkUltimo_rec['PRONUM']; // R.1
            $pronum = $workYear . "000000" + $anaent_rec['ENTDE1'] + 1; // R. 2
            $anaent_rec['ENTDE1'] = intval(substr($pronum, 4));

            try {
                $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANAENT', 'ROWID', $anaent_rec);
                if ($nrow <= 0) {
                    $risultato['errTitolo'] = "Errore";
                    $risultato['errMsg'] = "Aggiornameto Progressivo Protocollo non Riuscito.<br>Contattare l'assistenza Software.";
                    $risultato['pronum'] = 'Error';
                    return $risultato;
                }
            } catch (Exception $e) {
                $risultato['errTitolo'] = "Errore";
                $risultato['errMsg'] = $e->getMessage();
                $risultato['pronum'] = 'Error';
                return $risultato;
            }
        }
        $risultato['errTitolo'] = '';
        $risultato['errMsg'] = '';
        $risultato['Error'] = '';
        $risultato['pronum'] = $pronum;
        return $risultato;
    }

    function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => 'Da definire'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function prenotaNotifica($dataRegistrazione, $modo, $workYear) {
        $risultato = array();
        $anaent_rec = $this->GetAnaent('30');
        if ($anaent_rec['ENTDE2'] != $workYear) {
            $numRegistro = "1";
        } else {
            $numRegistro = $anaent_rec['ENTDE1'] + 1;
        }
        if ($modo == "LEGGI") {
            $risultatoCS = $this->controllaSequenzaNotifiche($numRegistro, $dataRegistrazione, $workYear);
            if ($risultatoCS['errMsg'] != '') {
                $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                $risultato['errMsg'] = $risultatoCS['errMsg'];
                $risultato['numRegistro'] = 'Error';
                return $risultato;
            }
            $retSeq = $risultatoCS['retSeq'];
            if ($retSeq != 0) {
                $risultato['errTitolo'] = $risultatoCS['errTitolo'];
                $risultato['errMsg'] = $risultatoCS['errMsg'];
                $risultato['numRegistro'] = 'Error';
                return $risultato;
            }
            $registroChk_rec = $this->GetRegistro($numRegistro, 'codice', $workYear);
            if ($registroChk_rec) {
                $risultato['errTitolo'] = "Inserimento Nuovo Protocollo";
                $risultato['errMsg'] = "Il cod.progressivo  :" . $workYear . "/" . $numRegistro . " Già inserito.<br><br>Inserimento Interrotto!";
                $risultato['numRegistro'] = 'Error';
                return $risultato;
            }
        }
        if ($modo == "AGGIORNA") {
            $anaent_rec['ENTDE1'] = $numRegistro;
            $anaent_rec['ENTDE2'] = $workYear;
            try {
                $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANAENT', 'ROWID', $anaent_rec);
                if ($nrow <= 0) {
                    $risultato['errTitolo'] = "Errore";
                    $risultato['errMsg'] = "Aggiornameto Progressivo Notifiche non Riuscito.<br>Contattare l'assistenza Software.";
                    $risultato['numRegistro'] = 'Error';
                    return $risultato;
                }
            } catch (Exception $e) {
                $risultato['errTitolo'] = "Errore";
                $risultato['errMsg'] = $e->getMessage();
                $risultato['numRegistro'] = 'Error';
                return $risultato;
            }
        }
        $risultato['errTitolo'] = '';
        $risultato['errMsg'] = '';
        $risultato['Error'] = '';
        $risultato['numRegistro'] = $numRegistro;
        return $risultato;
    }

    function controllaSequenzaNotifiche($numRegistro, $chkDate, $workYear) {
        $risultato = array();
        $errTitolo = '';
        $errMsg = '';
        $retSeq = 0;
        $conta = 0;
        $nonce = 0;
        if ($numRegistro != "1") {
            while (true) {
                $conta += 1;
                $key = $numRegistro - $conta;
                $registroChk_rec = $this->GetRegistro($key, 'codice', $workYear);
                if ($registroChk_rec) {
                    if ($chkDate < $registroChk_rec['DATA']) {
                        $retSeq = 1;
                        $proto1 = $registroChk_rec['PROGRESSIVO'];
                        $Datar1 = $registroChk_rec['DATA'];
                    }
                    break;
                } else {
                    $nonce += 1;
                }
                if ($nonce > 10)
                    break;
            }
        }
        $key = $numRegistro + 1;
        $registroChk_rec = $this->GetRegistro($key, 'codice', $workYear);
        if ($registroChk_rec) {
            if ($chkDate > $registroChk_rec['DATA']) {
                $retSeq = 1;
                $proto2 = $registroChk_rec['PROGRESSIVO'];
                $Datar2 = $registroChk_rec['DATA'];
            }
        }
        IF ($retSeq != 0) {
            $errTitolo = "Controllo Sequenza Inserimento.";
            $errMsg = "ATTENZIONE NUMERO NOTIFICA CON DATA DI REGISTRAZIONE FUORI SEQUENZA<BR>" .
                    "N. " . $proto1 . " Reg. " . $Datar1 . " - N. " . $proto2 . " Reg. " . $Datar2;
        }
        if (date('Y', strtotime($chkDate)) != $workYear) {
            $retSeq = 2;
            $errTitolo = "Controllo Sequenza Inserimento.";
            $errMsg = "ATTENZIONE INCONGRUENZA TRA DATA DEL GIORNO E DATA DI REGISTRAZIONE.<BR>" .
                    "IMPOSTARE L'ANNO DELLA DATA DEL GIORNO, UGUALE ALL'ANNO DA  REGISTRARE";
        }
        $risultato['retSeq'] = $retSeq;
        $risultato['errTitolo'] = $errTitolo;
        $risultato['errMsg'] = $errMsg;
        return $risultato;
    }

    public function GetAnagra($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAGRA WHERE CODTRI=" . $codice;
        } else {
            $sql = "SELECT * FROM ANAGRA WHERE ROWID='$codice'";
        }
        $anagra_rec = ItaDB::DBSQLSelect($this->getANELDB(), $sql, false);
        return $anagra_rec;
    }

    public function GetLavoro($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM LAVORO WHERE CODTRI=" . $codice;
        } else {
            $sql = "SELECT * FROM LAVORO WHERE ROWID='$codice'";
        }
        $lavoro_rec = ItaDB::DBSQLSelect($this->getANELDB(), $sql, false);
        return $lavoro_rec;
    }

    public function GetAnindi($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANINDI WHERE CODIND='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANINDI WHERE ROWID='$codice'";
        }
        $anindi_rec = ItaDB::DBSQLSelect($this->getANELDB(), $sql, false);
        return $anindi_rec;
    }

    public function getSqlRegistro() {
        return "
            SELECT
                DISTINCT ANAPRO.ROWID AS ROWID,
                ANAPRO.PRONUM AS PRONUM,
                ANAPRO.PROPAR,
                ANAPRO.PROSEG AS PROSEG,
                ANAPRO.PRODAR AS PRODAR,
                ANAPRO.PROORA AS PROORA,
                ANAPRO.PRODAA AS PRODAA,
                ANAPRO.PROPRE,
                ANAPRO.PROUFF,
                ANAPRO.PROCCA,
                ANAPRO.PROCCF,
                ANAPRO.PROCAT,
                ANAPRO.PRODAS,
                ANAPRO.PRONAL,
                ANAPRO.PROIND,
                ANAPRO.PROCAP,
                ANAPRO.PROCIT,
                ANAPRO.PROPRO,
                ANAPRO.PROTSP,
                ANAPRO.PRONOM AS PRONOM,
                ANAPRO.PROMAIL,
                ANAPRO.PROEME,
                ANAPRO.PROUOF,
                ANAPRO.PRONRA,
                ANAPRO.PRONPA,
                ANAPRO.PRONAF,
                ANAPRO.PROIDMAILDEST,
                ANAOGG.OGGOGG,
                UFFPRO.PRONUM AS UFFPRONUM,
                ANAPRO.PRORISERVA,
                ANAPRO.PROTSO,                
                ANAPRO.PROUTE,
                ANAPRO.PROPARPRE,
                ANAPRO.PROINCOGG,
                ANAPRO.PROFASKEY,
                ANAPRO.PROFIS,
                ANAPRO.PROCODTIPODOC,
                ANAPRO.PROSTATOPROT,
                ANAUFF.UFFCOD AS UFFCOD,                                
                ANAUFF.UFFDES AS UFFDES,
                ANAUFF2.UFFCOD AS UFFCOD2,
                ANAUFF2.UFFDES AS UFFDES2,
                ANAMED.MEDFIS AS MEDFIS,
                 (SELECT DESNOM FROM ANADES FORCE INDEX(I_DESPAR) WHERE ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR AND (ANADES.DESPAR = 'C' OR ANADES.DESPAR = 'P') AND ANADES.DESTIPO = 'M') AS DESNOM_FIRMATARIO
            FROM ANAPRO ANAPRO
            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
            LEFT OUTER JOIN ANAUFF ANAUFF ON ANAPRO.PROUFF=ANAUFF.UFFCOD
            LEFT OUTER JOIN ANAUFF ANAUFF2 ON ANAPRO.PROUOF=ANAUFF2.UFFCOD
            LEFT OUTER JOIN UFFPRO UFFPRO ON ANAPRO.PRONUM=UFFPRO.PRONUM AND ANAPRO.PROPAR=UFFPRO.UFFPAR
            LEFT OUTER JOIN ANAMED ON ANAPRO.PROCON = ANAMED.MEDCOD";
// LEFT OUTER JOIN ANADES ANADES_FIRMATARIO ON ANAPRO.PRONUM=ANADES_FIRMATARIO.DESNUM AND ANAPRO.PROPAR=ANADES_FIRMATARIO.DESPAR AND (ANADES_FIRMATARIO.DESPAR = 'C' OR ANADES_FIRMATARIO.DESPAR = 'P') AND ANADES_FIRMATARIO.DESTIPO = 'M'
//COUNT(ANADOC_MAIL_A.ROWID) + COUNT(ANADES_MAIL_P.ROWID) + COUNT(ANAPRO_MAIL_P.ROWID) AS QUANTEMAIL
//            LEFT OUTER JOIN (SELECT * FROM ANADOC WHERE ANADOC.DOCTIPO='' AND ANADOC.DOCSERVIZIO=0) ANADOC_MAIL_A ON ANAPRO.PRONUM=ANADOC_MAIL_A.DOCNUM AND ANAPRO.PROPAR=ANADOC_MAIL_A.DOCPAR 
//            LEFT OUTER JOIN (SELECT * FROM ANADES WHERE ANADES.DESTIPO='D') ANADES_MAIL_P ON ANAPRO.PRONUM=ANADES_MAIL_P.DESNUM AND ANAPRO.PROPAR=ANADES_MAIL_P.DESPAR 
//            LEFT OUTER JOIN (SELECT * FROM ANAPRO WHERE ANAPRO.PROPAR='P' AND ANAPRO.PROIDMAILDEST <> '') ANAPRO_MAIL_P ON ANAPRO.PRONUM=ANAPRO_MAIL_P.PRONUM AND ANAPRO.PROPAR=ANAPRO_MAIL_P.PROPAR  
//            ";
//LEFT OUTER JOIN ANADOC ANADOC ON ANADOC.DOCKEY=CONCAT(ANAPRO.PRONUM, ANAPRO.PROPAR)
    }

    public function getSqlRegistroStampa() {
        return "
            SELECT
                DISTINCT ANAPRO.ROWID AS ROWID,
                ANAPRO.PRONUM AS PRONUM,
                ANAPRO.PROPAR,
                ANAPRO.PROSEG AS PROSEG,                
                PRODAR AS PRODAR,
                PROORA AS PROORA,
                PROPRE,
                PROUFF,
                PROCCA,
                PROCCF,
                PROCAT,
                PRODAS,
                PRONAL,
                ANAPRO.PROIND,
                ANAPRO.PROCAP,
                PROEME,                
                ANAPRO.PROCIT,
                PROUOF,                
                ANAPRO.PROPRO,
                PRONRA,
                PRONPA,
                ANAPRO.PRONOM AS PRONOM,
                PRONAF,
                OGGOGG,
                UFFPRO.PRONUM AS UFFPRONUM,
                PRORISERVA,
                PROTSO,
                PROUTE,
                PROPARPRE,
                PROINCOGG,
                PROFASKEY,
                ANAPRO.PROCODTIPODOC,
                ANAUFF.UFFCOD AS UFFCOD,                
                ANAUFF.UFFDES AS UFFDES,
                ANAUFF2.UFFCOD AS UFFCOD2,
                ANAUFF2.UFFDES AS UFFDES2
                TMPPRO.CAMPO1 AS CAMPO1
            FROM TMPPRO TMPPRO
            LEFT OUTER JOIN ANAPRO ANAPRO ON TMPPRO.CHIAVENUM = ANAPRO.ROWID
            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
            LEFT OUTER JOIN ANAUFF ANAUFF ON ANAPRO.PROUFF=ANAUFF.UFFCOD
            LEFT OUTER JOIN ANAUFF ANAUFF2 ON ANAPRO.PROUOF=ANAUFF2.UFFCOD            
            LEFT OUTER JOIN UFFPRO UFFPRO ON ANAPRO.PRONUM=UFFPRO.PRONUM AND ANAPRO.PROPAR=UFFPRO.UFFPAR";
    }

    public function GetTitproc($codice, $where = 'rowid', $multi = false) {
        if ($where != 'rowid') {
            $sql = "SELECT * FROM TITPROC WHERE $codice";
        } else {
            $sql = "SELECT * FROM TITPROC WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetAbilitaProt($codice, $where = 'rowid', $multi = false) {
        if ($where == 'rowid') {
            $sql = "SELECT * FROM ABILITAPROT WHERE ROWID=$codice";
        } else if ($where == 'tutti') {
            $sql = "SELECT * FROM ABILITAPROT";
            $multi = true;
        } else {
            $sql = "SELECT * FROM ABILITAPROT WHERE CODICE='$codice'";
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

    /**
     * Ritorna l'elenco degli account assegnati in Pop3 per il protocollo
     * @return array
     */
    public function getAccountNamePop3() {
        $anaent_28 = $this->GetAnaent('28');
        return unserialize($anaent_28['ENTVAL']);
    }

    /**
     * Ritorna l'account Smtp per il protocollo
     * @return string
     */
    public function getAccountNameSmtp() {
        $anaent_26 = $this->GetAnaent('26');
        return $anaent_26['ENTDE4'];
    }

    /**
     * Ritornano i parametri degli account di posta Pop3 assegnati al protocollo
     * @return array
     */
    public function getParametriAccountPop3() {
        $account = $this->getAccountNamePop3();
        $parametri = array();
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        foreach ($account as $value) {
            $parametri[] = $emlLib->getMailAccount($value['EMAIL']);
        }
        return $parametri;
    }

    /**
     * Ritornano i parametri dell'account di posta smtp assegnato al protocollo
     * @return array
     */
    public function getParametriAccountSmtp() {
        $account = $this->getAccountNameSmtp();
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $parametri = $emlLib->getMailAccount($account);
        return $parametri;
    }

    public function getParametriPosta($tipoPosta = 'PostaNormale') {
        $anaent_27 = $this->GetAnaent('27');
        if ($tipoPosta == 'PostaNormale') {
            return $parametriPosta = array(
                'MessaggioOriginale' => $anaent_27['ENTDE1'],
                'Allegati' => $anaent_27['ENTDE2'],
                'Corpo' => $anaent_27['ENTDE3']
            );
        } else {
            return $parametriPosta = array(
                'Busta' => $anaent_27['ENTDE4'],
                'MessaggioOriginale' => $anaent_27['ENTDE5'],
                'Allegati' => $anaent_27['ENTDE6']
            );
        }
    }

    public function getPromitagg($codice, $tipo = 'codice', $multi = true, $tipoProt = "") {
        if ($tipo == 'codice') {
            $whereTipo = "";
            if ($tipoProt) {
                $whereTipo = " AND PROPAR='$tipoProt'";
            }
            $sql = "SELECT * FROM PROMITAGG WHERE PRONUM=$codice $whereTipo ORDER BY PRONOM";
        } else {
            $sql = "SELECT * FROM PROMITAGG WHERE ROWID=$codice";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function getPromail($where = '', $multi = false) {
        if (!$where) {
            return false;
        }
        $sql = "SELECT * FROM PROMAIL WHERE $where";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function formatFileSize($a_bytes) {
        if ($a_bytes < 1024) {
            return $a_bytes . ' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) . ' KiB';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MiB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GiB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) . ' TiB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) . ' PiB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) . ' EiB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) . ' ZiB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) . ' YiB';
        }
    }

    function getSegnaturaArray($anapro_rec) {
        $anades_tab = $this->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], "D");
        $anamed_dest = $this->GetAnamed($anapro_rec['PROCON'], 'codice');
        $anaogg_rec = $this->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $anaent_2 = $this->GetAnaent('2');
        $anaent_26 = $this->GetAnaent('26');
        $anaent_28 = $this->GetAnaent('28');
        $dataReg = date('Y-m-d', strtotime($anapro_rec['PRODAR']));
        $confermaRicezione = array();
        if ($anaent_28['ENTDE6'] == '1') {
            $confermaRicezione = array('confermaRicezione' => 'si');
        }
        $tipoIndirizzoTelematico = array("tipo" => "smtp");
        $xmlArray = array();

//
// Intestazione
//
        $xmlArray['Intestazione']['Identificatore']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
        $xmlArray['Intestazione']['Identificatore']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];
        $NumeroRegistrazione = str_pad(substr($anapro_rec['PRONUM'], 4), 7, '0', STR_PAD_LEFT);
        $xmlArray['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'] = $NumeroRegistrazione;
        $xmlArray['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] = $dataReg;
        if ($anapro_rec['PROPAR'] == 'P' || $anapro_rec['PROPAR'] == 'C') {
//
// Origine
//
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anaent_2['ENTDE2'] . ' ' . $anaent_2['ENTDE3'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];

//
// Destinazione
//
            $destinazione = array();
            $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
            if (!$tipoIndirizzoTelematicoDest['tipo']) {
                $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
            }
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
            $destinazione['IndirizzoTelematico']['@textNode'] = "";
            $destinazione['@attributes'] = $confermaRicezione;
            if ($anamed_dest['MEDCODAOO'] != '') {
                $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_dest['MEDDENAOO'];
                $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_dest['MEDCODAOO'];
                $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
            }
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;

            foreach ($anades_tab as $key => $anades_rec) {
                if ($anades_rec['DESCOD'] != '') {
                    $anamed_rec = $this->GetAnamed($anades_rec['DESCOD'], 'codice');
                    $destinazione = array();
                    $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
                    if (!$tipoIndirizzoTelematicoDest['tipo']) {
                        $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
                    }
                    $destinazione['@attributes'] = $confermaRicezione;
                    $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                    $destinazione['IndirizzoTelematico']['@textNode'] = "";
                    if ($anamed_rec['MEDCODAOO'] != '') {
                        $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_rec['MEDDENAOO'];
                        $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_rec['MEDCODAOO'];
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                    }
                    $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
                }
            }

//
// Risposta
//
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
        } else if ($anapro_rec['PROPAR'] == 'A') {
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = htmlentities($anapro_rec['PRONOM'], ENT_COMPAT, 'UTF-8');
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anapro_rec['PROIND'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = '';
//
// Destinazione
// 
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $destinazione['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
        }
//
// Oggetto
//
//        $xmlArray['Intestazione']['Oggetto']['@textNode'] = $anaogg_rec['OGGOGG'];
        $xmlArray['Intestazione']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);

//
// Descrizione
//
        $anadoc_tab = $this->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], ' AND DOCSERVIZIO=0  ORDER BY DOCTIPO');
        if ($anadoc_tab) {
            $descrizione = array();
            $principaleTrovato = false;
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $principaleTrovato = true;
                }
            }
            if ($principaleTrovato === false) {
                $xmlArray = array('stato' => false, 'messaggio' => 'Non è presente nessun Allegato principale. Selezionarne uno per poter continuare.');
                return $xmlArray;
            }
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $descrizione['Documento']['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
//                    $descrizione['Documento']['Oggetto']['@textNode'] = $anaogg_rec['OGGOGG'];
                    $descrizione['Documento']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);
                } else {
                    $documento = array();
                    $documento['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
                    $documento['TipoDocumento']['@textNode'] = '';
                    $descrizione['Allegati']['Documento'][] = $documento;
                }
            }
            $xmlArray['Descrizione'] = $descrizione;
        } else {
            $xmlArray['Descrizione']['TestoDelMessaggio']['@textNode'] = '';
        }
        return $xmlArray;
    }

    function getConfermaRicezioneArray($anapro_rec) {
        $anades_tab = $this->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], "D");
        $anamed_dest = $this->GetAnamed($anapro_rec['PROCON'], 'codice');
        $anaogg_rec = $this->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);

        $anaent_2 = $this->GetAnaent('2');
        $anaent_26 = $this->GetAnaent('26');
        $anaent_28 = $this->GetAnaent('28');

        $exOggetto = $exCorpo = '';
        $proLibMail = new proLibMail();
        $ElementiMail = $proLibMail->GetElementiTemplateMail($anapro_rec, 1, true);
        if ($ElementiMail) {
            $exOggetto = $ElementiMail['OGGETTOMAIL'];
            $exCorpo = $ElementiMail['BODYMAIL'];
        }
        $dataReg = date('Y-m-d', strtotime($anapro_rec['PRODAR']));
        if (!$exOggetto) {
            $exOggetto = 'Notifica ricezione email. Numero di Protocollo: ' . (int) substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
        }
        $exCorpo = htmlspecialchars_decode($exCorpo);
        $xmlArray = array();

        //
        // Identificatore
        //
        $xmlArray['Identificatore']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
        $xmlArray['Identificatore']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];
        $xmlArray['Identificatore']['NumeroRegistrazione']['@textNode'] = substr($anapro_rec['PRONUM'], 4);
        $xmlArray['Identificatore']['DataRegistrazione']['@textNode'] = $dataReg;
        //
        // Messaggio Ricevuto
        //
        if ($anamed_dest['MEDCODAOO'] != '') {
            $CodiceAmministrazioneAOO = $anamed_dest['MEDCODAOO'];
            $DenominazioneAOO = $anamed_dest['MEDDENAOO'];
        }
        $xmlArray['MessaggioRicevuto']['Identificatore']['CodiceAmministrazione']['@textNode'] = '';
        $xmlArray['MessaggioRicevuto']['Identificatore']['CodiceAOO']['@textNode'] = $CodiceAmministrazioneAOO;
        $xmlArray['MessaggioRicevuto']['Identificatore']['NumeroRegistrazione']['@textNode'] = $anapro_rec['PROSEG'];
        $xmlArray['MessaggioRicevuto']['Identificatore']['DataRegistrazione']['@textNode'] = '';
        // Descrizione Messaggio?
        $xmlArray['MessaggioRicevuto']['DescrizioneMessaggio']['@textNode'] = '';
//
// Riferimenti e Descrizione non obbligatori?!
//
        return $xmlArray;
    }

    function registraAnamed($mednom, $medcit, $medind, $medcap, $medpro, $email, $fisc) {
        $medcod = '';
        $anamed_check = $this->getGenericTab("SELECT * FROM ANAMED
                            WHERE " . $this->getPROTDB()->strUpper('MEDNOM') . " ='" . addslashes(strtoupper(trim($mednom))) . "' AND
                                  " . $this->getPROTDB()->strUpper('MEDCIT') . " ='" . addslashes(strtoupper(trim($medcit))) . "' AND
                                  " . $this->getPROTDB()->strUpper('MEDIND') . " ='" . addslashes(strtoupper(trim($medind))) . "'");
        if ($anamed_check) {
            $titolo = "Attenzione!";
            $messaggio = "Il nominativo è già presente nell'archivio. Non è possibile reinserirlo!";
        } else {
            for ($i = 1; $i <= 999999; $i++) {
                $codice = str_repeat("0", 6 - strlen(trim($i))) . trim($i);
                $anamed_rec = ItaDB::DBSQLSelect($this->getPROTDB(), "SELECT ROWID FROM ANAMED WHERE MEDCOD='$codice'", false);
                if (!$anamed_rec) {
                    $medcod = $codice;
                    break;
                }
            }
            if ($medcod == '') {
                $titolo = "Attenzione!";
                $messaggio = "Contattare l'assistenza, raggiunto il limite di inserimento mittenti/destinatari.";
            } else {
                $anamed_rec['MEDCOD'] = $medcod;
                $anamed_rec['MEDNOM'] = $mednom;
                $anamed_rec['MEDCIT'] = $medcit;
                $anamed_rec['MEDIND'] = $medind;
                $anamed_rec['MEDCAP'] = $medcap;
                $anamed_rec['MEDPRO'] = $medpro;
                $anamed_rec['MEDEMA'] = $email;
                $anamed_rec['MEDFIS'] = $fisc;
                ItaDB::DBInsert($this->getPROTDB(), 'ANAMED', 'ROWID', $anamed_rec);
                $titolo = "Registrazione.";
                $messaggio = "Elemento registrato correttamente.";
            }
        }
        return array('MEDCOD' => $medcod, 'titolo' => $titolo, 'messaggio' => $messaggio);
    }

    function registraAnaogg($Oggetto, $Procat, $Clacod) {
        $dogcod = '';
        $anamed_check = $this->getGenericTab("SELECT * FROM ANADOG
                            WHERE " . $this->getPROTDB()->strUpper('DOGDEX') . " ='" . addslashes(strtoupper(trim($Oggetto))) . "' AND
                                  DOGCAT = '$Procat' AND
                                  DOGCLA = '$Clacod' ");
        if ($anamed_check) {
            $titolo = "Attenzione!";
            $messaggio = "L'oggetto è già presente nell'archivio. Non è possibile reinserirlo!";
        } else {
            for ($i = 1; $i <= 9999; $i++) {
                $codice = str_repeat("0", 6 - strlen(trim($i))) . trim($i);
                $anadog_rec = ItaDB::DBSQLSelect($this->getPROTDB(), "SELECT ROWID FROM ANADOG WHERE DOGCOD='$codice'", false);
                if (!$anadog_rec) {
                    $dogcod = $codice;
                    break;
                }
            }
            if ($dogcod == '') {
                $titolo = "Attenzione!";
                $messaggio = "Contattare l'assistenza, raggiunto il limite di inserimento mittenti/destinatari.";
            } else {
                $anadog_rec['DOGCOD'] = $dogcod;
                $anadog_rec['DOGDEX'] = $Oggetto;
                $anadog_rec['DOGCAT'] = $Procat;
                $anadog_rec['DOGCLA'] = $Clacod;
                ItaDB::DBInsert($this->getPROTDB(), 'ANADOG', 'ROWID', $anadog_rec);
                $titolo = "Registrazione.";
                $messaggio = "Elemento registrato correttamente.";
            }
        }
        return array('DOGCOD' => $dogcod, 'titolo' => $titolo, 'messaggio' => $messaggio);
    }

    public function checkRiservatezzaProtocollo($anapro_rec) {
        if ($anapro_rec['PRORISERVA'] == 1 || $anapro_rec['PROTSO'] == 1) {
            return true;
        }
        return false;
    }

    public function elaboraTitolarioUfftit($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            $result_tab[$key]['CODICE'] = $result_rec['CATCOD'];
            $anacat_rec = $this->GetAnacat($result_rec['VERSIONE_T'], $result_rec['CATCOD']);
            $result_tab[$key]['DESCRIZIONE'] = $anacat_rec['CATDES'];
            if ($result_rec['CLACOD'] != '') {
                $result_tab[$key]['CODICE'] .= '.' . $result_rec['CLACOD'];
                $anacla_rec = $this->GetAnacla($result_rec['VERSIONE_T'], $result_rec['CATCOD'] . $result_rec['CLACOD']);
                $result_tab[$key]['DESCRIZIONE'] = $anacla_rec['CLADE1'];
                if ($result_rec['FASCOD'] != '') {
                    $result_tab[$key]['CODICE'] .= '.' . $result_rec['FASCOD'];
                    $anafas_rec = $this->GetAnafas($result_rec['VERSIONE_T'], $result_rec['CATCOD'] . $result_rec['CLACOD'] . $result_rec['FASCOD']);
                    $result_tab[$key]['DESCRIZIONE'] = $anafas_rec['FASDES'];
                }
            }
            $result_tab[$key]['VERSIONE'] = $result_rec['DESCRI'];
        }
        return $result_tab;
    }

    public function checkVisibilitaIncrOggetto($uffcodAbilitato) {
        if ($uffcodAbilitato == '') {
            return true;
        }
        $visibile = false;
        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->GetAnamed($profilo['COD_SOGGETTO']);
        if ($anamed_rec) {
            $uffdes_rec = $this->getGenericTab("SELECT * FROM UFFDES WHERE UFFKEY='" . $anamed_rec['MEDCOD'] . "' AND UFFCOD='$uffcodAbilitato'");
            if ($uffdes_rec) {
                $visibile = true;
            }
        }
        return $visibile;
    }

    public function checkRiscontro($anno, $codice, $tipo) {
        $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
        $where = "PROPARPRE='$tipo'";
        $sql = "SELECT ROWID, PRONUM FROM ANAPRO WHERE $where AND PROPRE BETWEEN $anno$codice AND $anno$codice";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    public function checkStatoManutenzione() {
        $anaent_29 = $this->GetAnaent('29');
        if ($anaent_29['ENTDE3'] != '') {
            return true;
        } else {
            return false;
        }
    }

    public function getUtenteInterno() {
        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($profilo['COD_SOGGETTO'] != '') {
            $anamed_rec = $this->GetAnamed($profilo['COD_SOGGETTO']);
            if (!$anamed_rec) {
                return false;
            }
            return $anamed_rec;
        } else {
            return false;
        }
    }

    function GetMsgInputPassword($form, $titolo, $retid = "", $msg = "") {
        $header = "<span style=\"color:red;font-weight:bold;font-size:1.2em;\">Digitare la password utilizzata per il login</span>";
        $footer = $msg;
        Out::msgInput($titolo, array(
            'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Password'),
            'id' => $form . '_password',
            'name' => $form . '_password',
            'type' => 'password',
            'width' => '70',
            'size' => '40',
            'maxchars' => '30'), array('F5-Conferma' => array('id' => $form . "_returnPassword$retid", 'model' => $form, 'shortCut' => "f5")), $form, "auto", "auto", true, $header, $footer
        );
    }

    function CtrPassword($password) {
        $ditta = App::$utente->getKey('ditta');
        $utente = App::$utente->getKey('nomeUtente');
        $ret = ita_verpass($ditta, $utente, $password);
        if ($ret['status'] != 0 && $ret['status'] != '-99') {
            Out::msgStop("Errore di validazione", $ret['messaggio'], 'auto', 'auto', '');
            return false;
        } else {
            return true;
        }
    }

    function checkMailRicMulti($idMailPadre) {
        $ret = array(
            "ACCETTAZIONE" => false,
            "CONSEGNA" => false,
            "NOTIFICHE" => false
        );
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        $emlLib = new emlLib();
        $mailArchivio_tab = $emlLib->getMailArchivio($idMailPadre, 'idmailpadre');
        foreach ($mailArchivio_tab as $mailArchivio_rec) {

            switch ($mailArchivio_rec['PECTIPO']) {
                case emlMessage::PEC_TIPO_ACCETTAZIONE:
                    $key = 'ACCETTAZIONE';
                    break;

                case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                    $key = "CONSEGNA";
                    break;

                case emlMessage::PEC_TIPO_PRESA_IN_CARICO:
                case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:
                    $key = "NOTIFICHE";
                    break;
            }
            $ret[$key][] = $mailArchivio_rec['IDMAIL'];
        }
        return $ret;
    }

    function checkMailRic($idMailPadre) {
        $ret = array(
            "ACCETTAZIONE" => false,
            "CONSEGNA" => false,
            "NOTIFICHE" => false
        );
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        $emlLib = new emlLib();
        $mailArchivio_tab = $emlLib->getMailArchivio($idMailPadre, 'idmailpadre');
        App::log('$mailArchivio_tab');
        App::log(count($mailArchivio_tab));
        foreach ($mailArchivio_tab as $mailArchivio_rec) {
            switch ($mailArchivio_rec['PECTIPO']) {
                case emlMessage::PEC_TIPO_ACCETTAZIONE:
                    $ret["ACCETTAZIONE"] = $mailArchivio_rec['IDMAIL'];
                    break;

                case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                    $ret["CONSEGNA"] = $mailArchivio_rec['IDMAIL'];
                    break;

                case emlMessage::PEC_TIPO_PRESA_IN_CARICO:
                case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:
                    $ret['NOTIFICHE'] = $idMailPadre;
                    break;
            }
        }
        return $ret;
    }

    function getMail($idMail) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        $emlLib = new emlLib();
        return $emlLib->getMailArchivio($idMail);
    }

    function getLoginDaMedcod($medcod) {
        try {
            $ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        return ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTEANA__1 = '" . $medcod . "'", false);
    }

    public function getAnapra($codice, $tipoRic = 'codice', $where = '') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPRA WHERE PRANUM='" . $codice . "' $where";
        } else {
            $sql = "SELECT * FROM ANAPRA WHERE ROWID=$codice";
        }
        $PRAM_DB = ItaDB::DBOpen('PRAM');
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function caricaDestinatari($codice, $tipoProt, $proArriDest = array(), $estraiTrsmSingole = false) {
        $i = 0;
        $whereAgg = '';
        if (!$estraiTrsmSingole) {
            $whereAgg = " AND DESCUF<>''";
        }
        $anades_tab = $this->GetAnades($codice, 'codice', true, $tipoProt, 'T', $whereAgg);
        foreach ($anades_tab as $recordDest) {
            $proArriDest[$i] = $recordDest;
            if ($recordDest['DESGES'] == 1) {
                $proArriDest[$i]['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
            }
//$arcite_rec = $this->getScadenzaTrasmissione($codice, $recordDest['DESCOD'], $tipoProt);
            $arcite_rec = $this->getScadenzaTrasmissione($codice, $recordDest['DESCOD'], $tipoProt, $recordDest['DESCUF']); /* Nuova modifica - correzione bug. */
            $proArriDest[$i]['TERMINE'] = $arcite_rec['ITETERMINE'];
            $i = $i + 1;
        }
        return $proArriDest;
    }

    public function getScadenzaTrasmissione($pronum, $codDestinatario, $propar, $descuf = '') {
        $sql = "SELECT * FROM ARCITE WHERE ITEPRO=" . $pronum . " AND ITENODO='ASS' AND ITEPAR='$propar' AND ITEDES='$codDestinatario'";
        if ($descuf) {
            $sql .= " AND ITEUFF ='$descuf' ";
        }
        return $this->getGenericTab($sql, false);
    }

    public function caricaAltriDestinatari($codice, $propar, $elabora = true) {
        $proAltriDestinatari = $this->GetAnades($codice, 'codice', true, $propar, 'D', " AND DESCUF=''");
        if ($elabora === true) {
            $basLib = new basLib();
            foreach ($proAltriDestinatari as $key => $value) {
                if ($proAltriDestinatari[$key]['DESCONOSCENZA']) {
                    $proAltriDestinatari[$key]['CONOSCENZA'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                    ;
                } else {
                    $proAltriDestinatari[$key]['CONOSCENZA'] = "";
                }
                if ($value['DESIDMAIL']) {
                    $proAltriDestinatari[$key]['MAILDEST'] = '<span class="ui-icon ui-icon-mail-closed"></span>';

                    if ($propar == 'P') {
                        $proAltriDestinatari[$key]['SBLOCCA'] = '<span class="ui-icon ui-icon-unlocked"></span>';
                    } else {
                        $proAltriDestinatari[$key]['SBLOCCA'] = '';
                    }

                    $retRic = $this->checkMailRic($value['DESIDMAIL']);
                    if ($retRic['ACCETTAZIONE']) {
                        $proAltriDestinatari[$key]['ACCETTAZIONE'] = '<span class="ui-icon ui-icon-check"></span>';
                        $proAltriDestinatari[$key]['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                    }
                    if ($retRic['CONSEGNA']) {
                        $proAltriDestinatari[$key]['CONSEGNA'] = '<span class="ui-icon ui-icon-check"></span>';
                        $proAltriDestinatari[$key]['IDCONSEGNA'] = $retRic['CONSEGNA'];
                    }
                    if ($retRic['NOTIFICHE']) {
                        $proAltriDestinatari[$key]['NOTIFICAPEC'] = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>";
                        $proAltriDestinatari[$key]['IDNOTIFICAPEC'] = $retRic['NOTIFICHE'];
                    }
                }
                // Ruolo
                if ($value['DESRUO_EXT']) {
                    $Ana_Ruoli_rec = $basLib->getRuolo($value['DESRUO_EXT'], 'codice');
                    if ($Ana_Ruoli_rec) {
                        $proAltriDestinatari[$key]['RUOLO'] = $Ana_Ruoli_rec['RUODES'];
                    }
                } else {
                    $proAltriDestinatari[$key]['RUOLO'] = '';
                }
            }
        }
        return $proAltriDestinatari;
    }

    public function caricaAllegatiProtocollo($codice, $propar) {
        $proLibSdi = new proLibSdi();
        $proArriAlle = array();
        $protPath = $this->SetDirectory($codice, $propar);
        $anapro_rec = $this->GetAnapro($codice, 'codice', $propar);
        $anadoc_tab = $this->GetAnadoc($codice, 'codice', true, $propar, ' AND DOCSERVIZIO=0  ORDER BY DOCTIPO');
        if ($anadoc_tab) {
            foreach ($anadoc_tab as $anadoc_rec) {
                $CtrAllegati = '';
                $isFatturaPA = $proLibSdi->isAnadocFileFattura($anapro_rec['ROWID'], $anadoc_rec['DOCNAME']);
                $isMessaggioFatturaPA = $proLibSdi->isAnadocFileMessaggio($anapro_rec['ROWID'], $anadoc_rec['DOCNAME']);
                if ($isFatturaPA) {
                    $CtrAllegati = $proLibSdi->ctrNumeroAllegatiFatturaElettronica($anapro_rec['ROWID']);
                }
                $ini_tag = $fin_tag = $daMail = $vsign = $segna = '';
                if ($anadoc_rec['DOCIDMAIL'] != '') {
                    $daMail = "<span title = \"Allegato da Email.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
                if (!$this->checkMd5Allegato($anadoc_rec, $protPath)) {
                    $ini_tag = '<div class="ita-html"><span 
                        style="display:inline-block;margin-right:6px;" 
                        class="ita-tooltip ui-icon ui-icon-alert"
                        title="MD5 NON corrispondente!"
                        ></span><span style="display:inline-block;color:#BE0000;" >';
                    $fin_tag = '</span></div>';
                }
                $anadoc_rec['NOMEFILE'] = $ini_tag . $anadoc_rec['DOCNAME'] . $fin_tag;
                $anadoc_rec['FILEORIG'] = $anadoc_rec['DOCNAME'];
//                $anadoc_rec['FILEPATH'] = $protPath . "/" . $anadoc_rec['DOCFIL'];
                $anadoc_rec['FILEPATH'] = $anadoc_rec['DOCFIL'];
                $anadoc_rec['FILENAME'] = $anadoc_rec['DOCFIL'];
                $anadoc_rec['FILEINFO'] = $anadoc_rec['DOCNOT'];
                $anadoc_rec['ISFATTURAPA'] = $isFatturaPA;
                $anadoc_rec['ISMESSAGGIOFATTURAPA'] = $isMessaggioFatturaPA;
                $anadoc_rec['ALLEGATIFATTURA'] = $CtrAllegati;
                $anadoc_rec['DAMAIL'] = $daMail;
//                $ext = pathinfo($protPath . "/" . $anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
//                $ext = pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
                $ext = pathinfo($anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
                $preview = $this->GetImgPreview($ext, $anadoc_rec, $anadoc_rec['DOCPAR']);
                $anadoc_rec['PREVIEW'] = $preview;
                $proArriAlle[] = $anadoc_rec;
            }
        }
        return $proArriAlle;
    }

    public function checkSHA2Allegato($anadoc_rec, $protPath) {
        if (strtoupper($anadoc_rec['DOCFIL']) == 'SEGNATURA.XML' || $anadoc_rec['DOCPATHASSOLUTA'] != '') {
            return true;
        }
        if (hash_file('sha256', $protPath . "/" . $anadoc_rec['DOCFIL']) != $anadoc_rec['DOCSHA2']) {
            return false;
        } else {
            return true;
        }
    }

    public function checkMd5Allegato($anadoc_rec, $protPath) {
        if (strtoupper($anadoc_rec['DOCFIL']) == 'SEGNATURA.XML' || $anadoc_rec['DOCPATHASSOLUTA'] != '') {
            return true;
        }
        if ($anadoc_rec['DOCMD5']) {
            if (md5_file($protPath . "/" . $anadoc_rec['DOCFIL']) != $anadoc_rec['DOCMD5']) {
                return false;
            } else {
                return true;
            }
        } else {
            $FilePathDest = $protPath . "/" . $anadoc_rec['DOCFIL'];
            if (!file_exists($FilePathDest)) {
                if ($anadoc_rec['DOCUUID']) {
                    include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
                    $proLibAllegati = new proLibAllegati();
                    $FilePathDest = $proLibAllegati->CopiaTmpAllegatoByUUID($anadoc_rec['DOCUUID']);
                }
            }
            if (hash_file('sha256', $FilePathDest) != $anadoc_rec['DOCSHA2']) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function GetImgPreview($ext, $allegato, $propar) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
        $proLibAllegati = new proLibAllegati();

        $title = "Menu funzioni";
        if (strtolower($ext) == "p7m") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
        } else if ($propar === 'A') {

            $docmeta = unserialize($allegato['DOCMETA']);
            if ($propar === 'A') {
                if ($docmeta['SEGNATURA'] !== true) {
                    $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
                }
            } else {
                return '';
            }
        } else if (strtolower($ext) == "pdf") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        } else {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        }
        if ($allegato['ISFATTURAPA'] || $allegato['ISMESSAGGIOFATTURAPA']) {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-euro-blue-24x24\" title=\"Documento SDI\"></span>";
            if ($allegato['ALLEGATIFATTURA']) {
                $preview .= "<span style=\"display:inline-block; margin-left:-9px;\" class=\"ita-icon ita-icon-clip-16x16\" title=\"Allegati alla Fattura\"></span>";
            }
        }

        $docfirma_check = $proLibAllegati->GetDocfirma($allegato['ROWID'], 'rowidanadoc');
        if ($docfirma_check) {
            if (!$docfirma_check['FIRDATA'] && !$docfirma_check['FIRANN']) {
                $preview .= "<span style=\"display:inline-block;margin-left:4px;\" class=\"ita-icon ita-icon-sigillo-24x24\" title=\"Documento alla Firma\"></span>";
            }
        }
        return $preview;
    }

    public function AnalizzaP7m($file) {
        include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
        $p7m = itaP7m::getP7mInstance($file);
        if (!$p7m) {
            Out::msgStop("Inserimento File Firmato", "Verifica Fallita");
            return false;
        }
        if ($p7m->isFileVerifyPassed()) {
            Out::msgInfo("verifica Firme", "Firma verificata");
            $p7m->cleanData();
            return true;
        } else {
            $signErrors = $p7m->getMessageErrorFileAsString();
            $signErrors .= $p7m->getMessageErrorFileAsString();
            Out::msgStop("Firma non verificata", $p7m->getMessageErrorFileAsString());
            $p7m->cleanData();
            return false;
        }
    }

    public function caricaUffici($codice, $tipo) {
        $proArriUff = $this->getGenericTab("SELECT * FROM UFFPRO LEFT OUTER JOIN ANAUFF ON ANAUFF.UFFCOD = UFFPRO.UFFCOD WHERE UFFPRO.PRONUM='$codice' AND UFFPRO.UFFPAR='$tipo'");
        foreach ($proArriUff as $key => $recordUff) {
            unset($proArriUff[$key]['ROWID']);
            if ($recordUff['UFFFI1']) {
                $proArriUff[$key]['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                $proArriUff[$key]['FLGEST'] = 1;
            } else {
                $proArriUff[$key]['GESTIONE'] = '';
                $proArriUff[$key]['FLGEST'] = 0;
            }
        }
        return $proArriUff;
    }

    public function getStringaUffici($codice, $tipo) {
        $proArriUff = $this->caricaUffici($codice, $tipo);
        $uffici_prim = array();
        $uffici_sec = array();
        foreach ($proArriUff as $ufficio) {
            $orig = '';
            if ($this->checkAssegnaOriginale($ufficio['UFFCOD'], $codice, $tipo)) {
                $orig = '*';
            }
            if ($ufficio['UFFABB']) {
                if ($ufficio['FLGEST']) {
                    $uffici_prim[] = $orig . trim($ufficio['UFFABB']);
                } else {
                    $uffici_sec[] = $orig . trim($ufficio['UFFABB']);
                }
            } else {
                if ($ufficio['FLGEST']) {
                    $uffici_prim[] = $orig . trim($ufficio['UFFCOD']);
                } else {
                    $uffici_sec[] = $orig . trim($ufficio['UFFCOD']);
                }
            }
        }
        $result = array_merge($uffici_prim, $uffici_sec);
        return implode('-', $result);
    }

    public function checkAssegnaOriginale($ufficio, $codice, $tipo) {
        $proArriDest = $this->caricaDestinatari($codice, $tipo);
        foreach ($proArriDest as $value) {
            if ($value['DESORIGINALE'] != '' && $ufficio == $value['DESCUF']) {
                return true;
            }
        }
        return false;
    }

    public function getScannerEndorserParams($anapro_rec) {
        $entusc = '1';
        if ($anapro_rec['PROPAR'] == 'P') {
            $entusc = '2';
        }
        $anaent_2 = $this->GetAnaent('2');
        $anaent_31 = $this->GetAnaent('31');
        $uffici = $this->getStringaUffici($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        switch ($anaent_31['ENTDE6']) {
            case '':
                $testo = $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  " . $uffici;
                break;
            case '1':
                $testo = $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  " . $uffici . "              \b" . $anapro_rec['PRONUM'];
                break;
            case '2':
                if ($anapro_rec['PROPAR'] == 'A') {
                    $testo = "Protocollo in Arrivo  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  " . $uffici;
                } else if ($anapro_rec['PROPAR'] == 'P') {
                    $testo = "Protocollo in Partenza  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  " . $uffici;
                } else {
                    $testo = "Documento Formale  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  " . $uffici;
                }
                break;
            case '3':
                $testo = $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'];
                break;
            case '4':
                if ($anapro_rec['PROPAR'] == 'A') {
                    $testo = "Protocollo in Arrivo  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'];
                } else if ($anapro_rec['PROPAR'] == 'P') {
                    $testo = "Protocollo in Partenza  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'];
                } else {
                    $testo = "Documento Formale  " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'];
                }
                break;
            case '5':
                /* Stringa molto lunga, rimossa solo da qui: tipologia prot, che è anche nella segnatura. */
                if ($anapro_rec['PROPAR'] == 'A') {
                    $testo = " " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  Perv." . date("d/m/Y", strtotime($anapro_rec['PRODAA'])) . "  " . $uffici;
                } else if ($anapro_rec['PROPAR'] == 'P') {
                    $testo = " " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  Perv." . date("d/m/Y", strtotime($anapro_rec['PRODAA'])) . "  " . $uffici;
                } else {
                    $testo = " " . $anaent_2['ENTDE1'] . "  Prot." . $anapro_rec['PROSEG'] . "  Perv." . date("d/m/Y", strtotime($anapro_rec['PRODAA'])) . "  " . $uffici;
                }
                break;
        }
        return array('CAP_PRINTERSTRING' => $testo);
    }

    public function RiordinaSequenzeMail($arrayMail) {
        foreach ($arrayMail as $key => $mail) {
            $new_seq += 10;
            $arrayMail[$key]['TDAGSEQ'] = $new_seq;
        }
        return $arrayMail;
    }

    public function RiordinaSequenzeMailDB($arrayMail) {
        foreach ($arrayMail as $mail) {
            $new_seq += 10;
            $mail['TDAGSEQ'] = $new_seq;
            try {
                $nrow = ItaDB::DBUpdate($this->getPROTDB(), "TABDAG", "ROWID", $mail);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                App::log($exc);
                return false;
            }
        }
        return true;
    }

    public function caricaUof($model, $idUtente = null) {
        Out::codice('$(protSelector("#' . $model->nameForm . '_ANAPRO[PROUOF]' . '")+" option").remove();');
        $profilo = proSoggetto::getProfileFromIdUtente($idUtente);
        $anamed_rec = $this->GetAnamed($profilo['COD_SOGGETTO']);
        $prouof = '';
        if ($anamed_rec) {
            $uffdes_tab = $this->GetUffdes($anamed_rec['MEDCOD'], 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
            $select = "1";
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->GetAnauff($uffdes_rec['UFFCOD']);
                if ($select) {
                    $prouof = $anauff_rec['UFFCOD'];
                    $UffdesREC = $uffdes_rec;
                }
                if ($anauff_rec['UFFANN'] == 0) {
                    Out::select($model->nameForm . '_ANAPRO[PROUOF]', 1, $uffdes_rec['UFFCOD'], $select, substr($anauff_rec['UFFDES'], 0, 30));

                    $select = '';
                }
            }
            Out::html($model->nameForm . '_ANAPRO[PROSECURE]', '');
            $livello = $UffdesREC['UFFPROTECT'];
            for ($i = 1; $i <= $livello; $i++) {
                Out::select($model->nameForm . '_ANAPRO[PROSECURE]', 1, $i, 1, $i);
            }
        }
        return $prouof;
    }

    public function caricaElementiUtenteUfficio($divModel, $divDestinazione, $nameForm) {
        $generator = new itaGenerator();
        $html = $generator->getModelHTML($divModel, false, $nameForm, true);
        Out::html($divDestinazione, $html);
    }

    public function ControllaFormatoOggetto($Oggetto) {
        $Ret = array();
        $Ret['STATO'] = true;
        $Ret['MESSAGGIO'] = '';
// Controllo lunghezza minima di 10 caratteri
// Controllo contenuto con il trim()
        if (strlen(trim($Oggetto)) < 10) {
            $Ret['STATO'] = false;
            $Ret['MESSAGGIO'] = 'La lunghezza minima è di 10 caratteri.';
            return $Ret;
        }
// Controllo ripetizione Parole
        $Parole = explode(' ', $Oggetto);
        foreach ($Parole as $key => $Parola) {
            if (strlen(trim($Parola)) == 0) {
                continue;
            }
            if (strtolower($Parola) == strtolower($Parole[($key - 1)])) {
                $Ret['STATO'] = false;
                $Ret['MESSAGGIO'] = 'La parola <b>' . $Parola . ' </b>viene ripetuta più volte, controlla che non ci siano ripetizioni. ';
                return $Ret;
            }
        }
        return $Ret;
    }

    public function GetDelegheIter($codice, $tipo = 'rowid') {
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM DELEGHEITER WHERE ROWID='$codice'";
        }
        $anatsp_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anatsp_rec;
    }

    public function getSqlUnionDestinatariResponsabile($codice, $checkDeleghe = true, $delefunzione = 0) {
        $sql = $this->getSqlDestinatariResponsabile($codice);
        /*
         * Aggiungo le eventuali deleghe
         */
        if ($checkDeleghe) {
            $proLibDeleghe = new proLibDeleghe();
            $Data = date('Ymd');
            $uffdes_tab = $this->GetUffdes($codice, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
            foreach ($uffdes_tab as $uffdes_rec) {
                $Delega_rec = $proLibDeleghe->CheckUtenteDelegato($codice, $uffdes_rec['UFFCOD'], $Data, $delefunzione);
                if ($Delega_rec) {
                    $sql .= "  UNION ";
                    $sql .= $this->getSqlDestinatariResponsabile($Delega_rec['DELESRCCOD'], $Delega_rec['DELEDSTCOD'], $uffdes_rec['UFFCOD']);
                }
            }
        }
        return $sql;
    }

    public function getSqlDestinatariResponsabile($codice, $codiceDelegato = '', $singoloUfficio = '') {
        $sql = "SELECT
                    ANAMED.*
                FROM 
                    ANAUFF
                LEFT OUTER JOIN UFFDES UFFDES ON ANAUFF.UFFCOD = UFFDES.UFFCOD 
                LEFT OUTER JOIN ANAMED ANAMED ON ANAMED.MEDCOD = UFFDES.UFFKEY 
                WHERE
                    ANAUFF.UFFRES = '$codice' AND
                    ANAUFF.UFFANN=0 AND
                    UFFDES.UFFCESVAL=''      
            ";
        if ($singoloUfficio) {
            $sql .= " AND UFFDES.UFFCOD='$singoloUfficio' ";
        }
        if ($codiceDelegato) {
            $sql .= " AND UFFDES.UFFKEY<>'$codiceDelegato' ";
        } else {
            $sql .= " AND UFFDES.UFFKEY<>'$codice' ";
        }
        $sql .= " GROUP BY ANAMED.MEDCOD";
        return $sql;
    }

    public function getUfficiResponsabile($codice) {
        $sql = "SELECT
                    ANAUFF.*
                FROM 
                    ANAUFF
                LEFT OUTER JOIN UFFDES UFFDES ON ANAUFF.UFFCOD = UFFDES.UFFCOD 
                LEFT OUTER JOIN ANAMED ANAMED ON ANAMED.MEDCOD = UFFDES.UFFKEY 
                WHERE
                    ANAUFF.UFFRES = '$codice' AND
                    ANAUFF.UFFANN=0 AND
                    UFFDES.UFFCESVAL=''      
                    AND UFFDES.UFFKEY<>'$codice' ";

        $sql .= " GROUP BY ANAUFF.UFFCOD";
        $Anauff_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        return $Anauff_tab;
    }

    public function GetCodiceRegistroProtocollo() {
        $anaent_rec = $this->GetAnaent("44");
        return $anaent_rec['ENTDE1'];
    }

    public function GetCodiceRegistroSuapDocumento() {
        $anaent_rec = $this->GetAnaent("59");
        return $anaent_rec['ENTDE2'];
    }

    public function GetCodiceRegistroDocFormali() {
        /* Controllare se stesso progressivo A/P/C, usare getCodiceRegistroProtocollo */
        $CodiceRegistro = '';
        $anaent48_rec = $this->GetAnaent("48");
        if ($anaent48_rec['ENTDE4']) {
            $CodiceRegistro = $this->GetCodiceRegistroProtocollo();
        } else {
            $anaent_rec = $this->GetAnaent("44");
            $CodiceRegistro = $anaent_rec['ENTDE2'];
        }
        return $CodiceRegistro;
    }

    public function GetAnaproSave($Pronum, $Propar) {
        $sql_save = "
            SELECT
                ROWID,
                PROUTE,
                PROUOF
            FROM
                ANAPROSAVE
            WHERE
                PRONUM=$Pronum AND PROPAR='$Propar' ORDER BY ROWID ";
        return $this->getGenericTab($sql_save);
    }

    public function GetUfficiAnamed($codice) {
        return $this->getGenericTab("SELECT ANAUFF.UFFCOD,ANAUFF.UFFDES FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND UFFDES.UFFCESVAL='' AND ANAUFF.UFFANN=0");
    }

    public function ControlloAssociazioneUtenteUfficio($CodUtente, $CodUfficio, $Tipologia = 'destinatario') {
        if ($CodUtente || $CodUfficio) {
            if ($CodUtente && !$CodUfficio) {
                $this->setErrCode(-1);
                $this->setErrMessage("Occorre indicare anche l'ufficio del $Tipologia.");
                return false;
            }
            if (!$CodUtente && $CodUfficio) {
                $this->setErrCode(-1);
                $this->setErrMessage("Occorre indicare anche il $Tipologia.");
                return false;
            }
            $uffdes_tab = $this->GetUfficiAnamed($CodUtente);
            $trovato = false;
            foreach ($uffdes_tab as $ufficio) {
                if ($ufficio['UFFCOD'] == $CodUfficio) {
                    $trovato = true;
                    break;
                }
            }
            if (!$trovato) {
                $this->setErrCode(-1);
                $this->setErrMessage("Il $Tipologia non fa pare dell'ufficio indicato.");
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Indicare il $Tipologia e il suo ufficio per poter procedere.");
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $tipoPerm 
     *          ''    -> per estrarle in ogni caso
     *          send  -> per protocollazione in invio
     *          rec   -> per protocollazione in arrivo
     * @return type
     */
    public function GetElencoDaMail($tipoPerm = '', $ufficio = '') {
        $ElencoMail = array();
        $daMailFiltrate = array();

        $anaent_26 = $this->GetAnaent('26');
        $anaent_37 = $this->GetAnaent('37');
        $anaent_45 = $this->GetAnaent('45');

        if ($anaent_26['ENTDE5']) {
            /* Per ora elenco Mail ha senso se tutte e 2 sono parametrizzate. Senso utilizzare se piu di una?  */
            if ($anaent_26['ENTDE4']) {//&& ($anaent_26['ENTDE4'] != $anaent_37['ENTDE2'])
                $ElencoMail[$anaent_26['ENTDE4']] = $anaent_26['ENTDE4'];
            }
            if ($anaent_37['ENTDE2']) {
                $ElencoMail[$anaent_37['ENTDE2']] = $anaent_37['ENTDE2'];
            }
            /* Elenco Mail */
            $anaent_56 = $this->GetAnaent('56');
            if (!$anaent_56['ENTVAL']) {
                $anaent_28 = $this->GetAnaent('28');
                $AccountRicezione = unserialize($anaent_28['ENTVAL']);
                foreach ($AccountRicezione as $AccountMail) {
                    $ElencoMail[$AccountMail['EMAIL']] = $AccountMail['EMAIL'];
                }
            }
        }
        /*
         *  Mail Autorizzazioni: Solo quelle autorizzate.
         *    (servirà la principale del protocollo?)
         */
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $utente = App::$utente->getKey('nomeUtente');
        $MailAutorizUtente = $emlLib->GetMailAutorizzazioni($utente, 'login', true, true);
        /* Integro a MailAutorizzazioni quelle per ufficio */
        $MailAutorizUfficioUtente = $this->GetMailAutorizzazioniUfficioUtente($utente, $ufficio, $tipoPerm);
        /* Merge Array Autorizzazioni */
        $MailAutoriz = array_merge($MailAutorizUtente, $MailAutorizUfficioUtente);

        /*
         * Estraggo Solo Mail Autorizzate.
         */
        if ($MailAutoriz) {
            //foreach ($ElencoMail as $Account) {
            foreach ($MailAutoriz as $key => $value) {
                /* Controllo tipo permesso */
                $Scarta = false;
                switch ($tipoPerm) {
                    case 'send':
                        if (!$value['PERM_SEND']) {
                            $Scarta = true;
                        }
                        break;
                    case 'rec':
                        if (!$value['PERM_REC']) {
                            $Scarta = true;
                        }
                        break;
                    case '':
                    default:
                        break;
                }
                if (!$Scarta) {
                    $Mail = $value['MAIL'] ? $value['MAIL'] : $value['UFFMAIL'];
                    $daMailFiltrate[] = $Mail;
                }
            }
            // }
        } else {
            /* Altrimenti può usare quelle predefinite */
            $daMailFiltrate = $ElencoMail;
        }
        return $daMailFiltrate;
    }

    public function GetElencoMailProtocollo() {
        $ElencoMail = array();
        $anaent_26 = $this->GetAnaent('26');
        $anaent_37 = $this->GetAnaent('37');

        if ($anaent_26['ENTDE5']) {
            /* Per ora elenco Mail ha senso se tutte e 2 sono parametrizzate. Senso utilizzare se piu di una?  */
            if ($anaent_26['ENTDE4']) {
                $ElencoMail[$anaent_26['ENTDE4']] = $anaent_26['ENTDE4'];
            }
            if ($anaent_37['ENTDE2']) {
                $ElencoMail[$anaent_37['ENTDE2']] = $anaent_37['ENTDE2'];
            }
            /* Elenco Mail */
            $anaent_28 = $this->GetAnaent('28');
            $AccountRicezione = unserialize($anaent_28['ENTVAL']);
            foreach ($AccountRicezione as $AccountMail) {
                $ElencoMail[$AccountMail['EMAIL']] = $AccountMail['EMAIL'];
            }
        }
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $MailAccount_tab = $emlLib->getMailAccountList();
        $MailProtocollo = array();
        foreach ($MailAccount_tab as $key => $MailAccount) {
            foreach ($ElencoMail as $Mail) {
                if ($MailAccount['MAILADDR'] == $Mail) {
                    $MailProtocollo[$MailAccount['ROWID']] = $MailAccount;
                }
            }
        }

        return $MailProtocollo;
    }

    public function ApriRiscontro($Anapro_rec, $TipoRiscontro = 'P') {
        if (!$Anapro_rec) {
            $this->setErrMessage('Protocollo da riscontrare obbligatorio per il riscontro.');
            return false;
        }
        itaLib::openForm('proArri', true);
        /* @var $proArri proArri */
        $proArri = itaModel::getInstance('proArri');
        $_POST['tipoProt'] = $TipoRiscontro;
        $proArri->setEvent('openform');
        $proArri->parseEvent();
        //  $proArri->Nuovo(); Eventuo "Nuovo" viene già chiamato da "parseEvent" ad "openform"
        $proArri->setRiscontro($Anapro_rec);
        return true;
    }

    public function CheckEsistenzaProto($pronum, $tipoProt = 'X') {
        $sql = " SELECT ROWID FROM ANAPRO ";
        $sql .= " WHERE PRONUM = '$pronum' ";
        if ($tipoProt == 'X') {
            $sql .= " AND (PROPAR = 'A' OR PROPAR = 'P') ";
        } else {
            $sql .= " AND (PROPAR = '$tipoProt') "; //NON SERVE PIU -> OR PROPAR = '$tipoProt" . "A' 
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    function GetBaseExtP7MFile($baseFile) {
        $Est_baseFile = strtolower(pathinfo($baseFile, PATHINFO_EXTENSION));
        if ($Est_baseFile == "") {
            $Est_baseFile = "pdf";
        } else {
            if ($Est_baseFile == "p7m") {
                $baseFile = pathinfo($baseFile, PATHINFO_FILENAME);
                $Est_baseFile = $this->GetBaseExtP7MFile($baseFile);
            }
        }
        return $Est_baseFile;
    }

    function GetExtP7MFile($baseFile) {
        $Est = strtolower(pathinfo($baseFile, PATHINFO_EXTENSION));
        if ($Est == "") {
            return "pdf";
        } else {
            if ($Est == "p7m") {
                $baseFile = pathinfo($baseFile, PATHINFO_FILENAME);
//$Est .= "." . $this->GetExtP7MFile($baseFile);
                $Est = $this->GetExtP7MFile($baseFile) . ".$Est";
            }
        }
        return $Est;
    }

    public function GetWsOrganigramma() {
        $arrOrganigramma = array();
        $sql = "SELECT
            ANAUFF.ROWID AS ROWIDUFF,
            ANASERVIZI.ROWID AS ROWIDSER,
            ANAUFF.UFFDES AS UFFDES,
            ANAUFF.UFFCOD,
            ANASERVIZI.SERDES AS SERDES,
            ANAMED.MEDCOD AS MEDCOD,
            ANAMED.MEDNOM AS MEDNOM,
            ANAMED.ROWID AS ROWIDMED,
            ANARUOLI.RUODES AS RUODES,
            UFFDES.UFFSCA AS UFFSCA,
            UFFDES.UFFFI1__1 AS GESTISCI,
            " . $this->getPROTDB()->strConcat("'0-'", 'ANAUFF.ROWID', "'-'", 'ANAMED.ROWID', "'-0'") . " AS ROWID
            FROM ANAUFF
            LEFT OUTER JOIN ANASERVIZI ON ANAUFF.UFFSER = ANASERVIZI.SERCOD
            LEFT OUTER JOIN UFFDES ON UFFDES.UFFCOD = ANAUFF.UFFCOD
            LEFT OUTER JOIN ANAMED ON ANAMED.MEDCOD = UFFDES.UFFKEY
            LEFT OUTER JOIN ANARUOLI ON ANARUOLI.RUOCOD = UFFDES.UFFFI1__2
            WHERE ANAUFF.UFFANN<>1 AND ANAMED.MEDANN<>1 AND UFFDES.UFFCESVAL=''
    ORDER BY SERDES, UFFDES, MEDNOM, RUODES ";
        $Organigramma_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        // CONCAT('0-',ANAUFF.ROWID,'-',ANAMED.ROWID,'-0') AS ROWID
        foreach ($Organigramma_tab as $Organigramma_rec) {
            $dati = array();
            $dati['settore'] = $Organigramma_rec['SERDES'];
            $dati['codiceUfficio'] = $Organigramma_rec['UFFCOD'];
            $dati['descrizioneUfficio'] = $Organigramma_rec['UFFDES'];
            $dati['codiceUtente'] = $Organigramma_rec['MEDCOD'];
            $dati['nominativo'] = $Organigramma_rec['MEDNOM'];
            $dati['ruolo'] = $Organigramma_rec['RUODES'];
            $arrOrganigramma[] = $dati;
        }

        return $arrOrganigramma;
    }

    public function GetProRepDocAnapro($codice, $tipo = 'codice', $tipoProt = '', $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PROREPDOC WHERE PRONUM=$codice AND PROPAR='$tipoProt'";
            if ($where != '') {
                $sql .= " AND " . $where;
            }
        } else {
            $sql = "SELECT * FROM PROREPDOC WHERE ROWID='$codice'";
        }
        $registro_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $registro_rec;
    }

    public function GetProRepDoc($classe, $chiave) {
        $sql = "SELECT * FROM PROREPDOC WHERE CLASSE='$classe' AND CHIAVE='$chiave'";
        $registro_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $registro_rec;
    }

    public function elaboraCSVDestinatari($filePath, $tipoProt) {
        if (!file_exists($filePath)) {
            $this->setErrCode(-1);
            $this->setErrMessage('File non trovato');
            return false;
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile aprire il file');
            return false;
        }

        $arrayDati = array();

        while (($data = fgetcsv($handle, 0, ";", '"')) !== false) {
            if (count($data) < 5) {
                $this->setErrCode(-1);
                $this->setErrMessage('File non valido');
                return false;
            }

            /*
             * Controlli per ogni campo
             */

            /*
             * Destinatario
             */
            if (strlen($data[0]) > 250) {
                $this->setErrCode(-1);
                $this->setErrMessage('Destinatario "' . $data[0] . '" non valido');
                return false;
            }

            /*
             * Indirizzo
             */
            if (strlen($data[1]) > 100) {
                $this->setErrCode(-1);
                $this->setErrMessage('Indirizzo "' . $data[1] . '" non valido');
                return false;
            }

            /*
             * Città
             */
            if (strlen($data[2]) > 40) {
                $this->setErrCode(-1);
                $this->setErrMessage('Città "' . $data[2] . '" non valida');
                return false;
            }

            /*
             * Provincia
             */
            if (strlen($data[3]) > 4) {
                $this->setErrCode(-1);
                $this->setErrMessage('Provincia "' . $data[3] . '" non valida');
                return false;
            }

            /*
             * CAP
             * /^[0-9]*$/ per accettare solo numerici
             */
            if (strlen($data[4]) > 5 || !preg_match('/^[0-9]*$/', $data[4])) {
                $this->setErrCode(-1);
                $this->setErrMessage('CAP "' . $data[4] . '" non valido - Correggere il destinatario: ' . $data[0]);
                return false;
            }

            /*
             * Email
             * Controllo regex base per email
             * http://www.regular-expressions.info/email.html
             */
            if ($data[5] && !preg_match('/^[A-Z0-9\'._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i', $data[5])) {
                $this->setErrCode(-1);
                $this->setErrMessage('Email "' . $data[5] . '" non valida');
                return false;
            }

            $tmpRec = array();
            $tmpRec['DESPAR'] = $tipoProt;
            $tmpRec['PRONOM'] = $tmpRec['DESNOM'] = $data[0];
            $tmpRec['PROIND'] = $tmpRec['DESIND'] = $data[1];
            $tmpRec['PROCIT'] = $tmpRec['DESCIT'] = $data[2];
            $tmpRec['PROPRO'] = $tmpRec['DESPRO'] = $data[3];
            $tmpRec['PROCAP'] = $tmpRec['DESCAP'] = $data[4];
            $tmpRec['DESMAIL'] = $data[5];

            $arrayDati[] = $tmpRec;
        }

        return $arrayDati;
    }

    public function GetUfficioUtentePredef($utente = '') {
        if (!$utente) {
            $utente = App::$utente->getKey('nomeUtente');
        }
        $Ufficio = '';
        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $accLib = new accLib();
        $utenti_rec = $accLib->GetUtenti($utente, 'utelog');
        $anamed_rec = $this->GetAnamed($utenti_rec['UTEANA__1']);
        if ($anamed_rec) {
            $uffdes_tab = $this->GetUffdes($anamed_rec['MEDCOD']);
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $Ufficio = $anauff_rec['UFFCOD'];
                    break;
                }
            }
        }
        return $Ufficio;
    }

    /**
     * 
     * @param type $Anapro_rec
     * Carica l'elenco di EFAS collegate a protocollo EFAA indicato
     */
    public function CaricaElencoEFASCollegati($Anapro_rec) {
        $anaent_45 = $this->GetAnaent('45');
        $sql = "SELECT ANAPRO.*,ANAOGG.OGGOGG "
                . " FROM ANAPRO "
                . " LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR "
                . " WHERE PROPARPRE = '" . $Anapro_rec['PROPAR'] . "' "
                . " AND PROPRE = '" . $Anapro_rec['PRONUM'] . "'"
                . " AND PROCODTIPODOC = '" . $anaent_45['ENTDE5'] . "' AND PROCODTIPODOC <> '' ";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    public function MettiTogliRiservato($Anapro_rec, $Riservato = '') {
        $AnaproRec = array();
        $AnaproRec['ROWID'] = $Anapro_rec['ROWID'];
        if ($Riservato) {
            $AnaproRec['PRORISERVA'] = '1';
            $motivo = 'Protocollo impostato come Riservato';
        } else {
            $AnaproRec['PRORISERVA'] = '';
            $motivo = 'Rimossa la riservatezza del protocollo.';
        }
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave($motivo, $Anapro_rec['ROWID'], 'rowid');
        /* Aggiorno con il riservato: */
        try {
            ItaDB::DBUpdate($this->getPROTDB(), "ANAPRO", "ROWID", $AnaproRec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento ANAPRO " . $exc->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $DatiProtocollo
     *      TIPO_PROT=>'A/P/C'    
     *      COD_OGGETTO=> CODICE OGGETTO PROTOCOLLo
     *      OGGETTO=> OGGETTO PROTOCOLLO
     *      FIRMATARIO=> CODICE FIRMATARIO
     *      FIRMATARIO_UFFICIO=> CODICE UFFICIO FIRMATARIO
     *      MITT_DEST => NOMINATIVO MITTENTE/DESTINATARIO
     *      TITOLARIO => Codice titolario: 00010002
     *      ALLEGATI = ARRAY() DI ALLEGATI.
     *      ANADES = ARRAY() DI ALTRI DESTINATARI.
     */
    public function ProtocollaWizard($DatiProtocollo, $RowidProtocollo = '') {
        // Qui controlli:?
//        return;
        // Carico i Dati:
        $proArriAlle = array();
        $Principale = false;
        foreach ($DatiProtocollo['ALLEGATI'] as $Allegato) {
            $tipoAlle = 'ALLEGATO';
            if ($Principale == false) {
                $tipoAlle = '';
                $Principale = true;
            }
            $proArriAlle[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $Allegato['FILEPATH'],
                'FILENAME' => $Allegato['FILENAME'],
                'FILEINFO' => $Allegato['DOCNAME'],
                'DOCTIPO' => $tipoAlle,
                'DOCNAME' => $Allegato['DOCNAME'],
                'NOMEFILE' => $Allegato['DOCNAME'],
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 0,
            );
        }
        $DatiProtocollo['ALLEGATI'] = $proArriAlle;


        /*
         * Se ho già un protocollo a cui aggiungere allegati:
         */
        if ($RowidProtocollo) {
            $proArri = itaModel::getInstance('proArri', 'proArri');
            if (!$proArri->ProtAllegaDaFascicolo) {
                Out::msgInfo("Attenzione", "Protocollo variato durante la selezione.<br>Non è possibile procedere con la protocollazione.");
                return;
            }
            $proArri->ProtocollaSimple($DatiProtocollo, $RowidProtocollo);
        } else {
            // Elaborazione in proArri: ProtocollaSimple
            itaLib::openForm('proArri', true);
            /* @var $proArri proArri */
            $proArri = itaModel::getInstance('proArri', 'proArri');
            $_POST['tipoProt'] = $DatiProtocollo['TIPO_PROT'];
            $proArri->setEvent('openform');
            $proArri->parseEvent();
            //$proArri->Nuovo();
            $proArri->ProtocollaSimple($DatiProtocollo);
        }
    }

    public function AllegaDaFascicolo($RowidProtocollo = '') {
        // Elaborazione in proArri: ProtocollaSimple
        itaLib::openForm('proGestPratica', true);
        /* @var $proGestPratica proGestPratica */
        $proGestPratica = itaModel::getInstance('proGestPratica');
        $proGestPratica->setEvent('openform');
        $proGestPratica->setRowidAllegaAProtocollo($RowidProtocollo);
        $proGestPratica->parseEvent();
    }

    public function caricaAllegatiAnaproSave($AnaproSave_rec = array()) {
        if (!$AnaproSave_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Variazione del protocollo mancante.");
            return false;
        }
        $codice = $AnaproSave_rec['PRONUM'];
        $propar = $AnaproSave_rec['PROPAR'];

        $proArriAlle = array();
        $protPath = $this->SetDirectory($codice, $propar);
        $anapro_rec = $this->GetAnapro($codice, 'codice', $propar);

        // $anadoc_tab = $this->GetAnadoc($codice, 'codice', true, $propar, ' AND DOCSERVIZIO=0  ORDER BY DOCTIPO');
        $sql = "SELECT * FROM ANADOCSAVE WHERE DOCKEY LIKE '" . $codice . $propar . "%' 
                        AND SAVEDATA='" . $AnaproSave_rec['SAVEDATA'] . "' AND SAVEORA='" . $AnaproSave_rec['SAVEORA'] . "' 
                            ORDER BY SAVEDATA, SAVEORA, DOCTIPO ";
        $anadoc_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);

        if ($anadoc_tab) {
            foreach ($anadoc_tab as $anadoc_rec) {

                $ini_tag = $fin_tag = $daMail = $vsign = $segna = '';
                if ($anadoc_rec['DOCIDMAIL'] != '') {
                    $daMail = "<span title = \"Allegato da Email.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
                if (!$this->checkMd5Allegato($anadoc_rec, $protPath)) {
                    $ini_tag = '<div class="ita-html"><span 
                        style="display:inline-block;margin-right:6px;" 
                        class="ita-tooltip ui-icon ui-icon-alert"
                        title="MD5 NON corrispondente!"
                        ></span><span style="display:inline-block;color:#BE0000;" >';
                    $fin_tag = '</span></div>';
                }
                $anadoc_rec['NOMEFILE'] = $ini_tag . $anadoc_rec['DOCNAME'] . $fin_tag;
                $anadoc_rec['FILEORIG'] = $anadoc_rec['DOCNAME'];
                $anadoc_rec['FILEPATH'] = $protPath . '/' . $anadoc_rec['DOCFIL'];
                $anadoc_rec['FILENAME'] = $anadoc_rec['DOCFIL'];
                $anadoc_rec['FILEINFO'] = $anadoc_rec['DOCNOT'];
                $anadoc_rec['DAMAIL'] = $daMail;
                $anadoc_rec['ANAPROSAVE'] = '1';

                $ext = pathinfo($anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
                $preview = $this->GetImgPreview($ext, $anadoc_rec, $anadoc_rec['DOCPAR']);
                $anadoc_rec['PREVIEW'] = $preview;
                /* Controllo se è ancora presente: */
                $info = "<span class=\"ita-tooltip ita-icon ita-icon-check-green-24x24\" title=\"File attualmente nel protocollo\"></span>";
//                Out::msgInfo('docsha',$anadoc_rec['DOCSHA2']);
                $sql = "SELECT * FROM ANADOC WHERE DOCKEY = '{$anadoc_rec['DOCKEY']}' ";
                $AnadocExist_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
                if (!$AnadocExist_rec) {
                    $info = "<span style=\"margin-left:3px;\" class=\"ita-tooltip ita-icon ita-icon-delete-16x16\" title=\"File cancellato\"></span>";
                }
                $anadoc_rec['INFO'] = '<div class="ita-html">' . $info . '</div>';

                $proArriAlle[] = $anadoc_rec;
            }
        }
        return $proArriAlle;
    }

    public function GetSqlMedRuoli() {
        $sql = "SELECT 
                ANAMED.*,
                MEDRUOLI.ID,
                MEDRUOLI.RUOCOD,
                ANARUOLI.RUODES
                    FROM MEDRUOLI 
                    LEFT OUTER JOIN ANAMED ANAMED ON MEDRUOLI.MEDCOD = ANAMED.MEDCOD 
                    LEFT OUTER JOIN ANARUOLI ANARUOLI ON MEDRUOLI.RUOCOD = ANARUOLI.RUOCOD ";
        return $sql;
    }

    public function GetSqlMedSerie() {
        $sql = "SELECT 
                    MEDSERIE.ROW_ID,
                    MEDSERIE.SERIECODICE,
                    MEDSERIE.DATAINI,
                    MEDSERIE.DATAEND,
                    ANASERIEARC.DESCRIZIONE,
                    ANASERIEARC.SIGLA
                FROM MEDSERIE 
                LEFT OUTER JOIN ANASERIEARC ANASERIEARC ON MEDSERIE.SERIECODICE = ANASERIEARC.CODICE ";
        return $sql;
    }

    /**
     * 
     * @param type $Codice
     * @param type $ElencoRuoli
     * @return type
     */
    public function GetRuoliSoggetto($Codice = '', $ElencoRuoli = array()) {
        $sql = $this->GetSqlMedRuoli();
        $sql .= " WHERE 1 ";
        /*
         * Se indico il singolo soggetto:
         */
        if ($Codice) {
            $sql .= " AND MEDRUOLI.MEDCOD = '$Codice' ";
        }
        /*
         * Se indico un elenco di ruoli
         */
        if ($ElencoRuoli) {
            $sql .= " AND ( ";
            foreach ($ElencoRuoli as $Ruolo) {
                $sql .= " MEDRUOLI.RUOCOD = '" . $Ruolo . "' OR ";
            }
            $sql = substr($sql, 0, -3) . ") ";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    /**
     * 
     * @param type $Codice
     * @param type $ElencoSerie
     * @return type
     */
    public function GetSerieSoggetto($Codice = '', $ElencoSerie = array()) {
        $sql = $this->GetSqlMedSerie();
        $sql .= " WHERE 1 ";
        /*
         * Se indico il singolo soggetto:
         */
        if ($Codice) {
            $sql .= " AND MEDSERIE.MEDCOD = '$Codice' ";
        }
        /*
         * Se indico un elenco di ruoli
         */
        if ($ElencoSerie) {
            $sql .= " AND ( ";
            foreach ($ElencoSerie as $Serie) {
                $sql .= " MEDSERIE.SERIECODICE = '" . $Serie . "' OR ";
            }
            $sql = substr($sql, 0, -3) . ") ";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    public function GetMedRuoli($codice) {
        $sql = "SELECT * FROM MEDRUOLI WHERE ID='$codice'";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetMedSerie($codice) {
        $sql = "SELECT * FROM MEDSERIE WHERE ROW_ID='$codice'";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetLastAnaproSave($Pronum, $Propar) {
        $sql = " SELECT * FROM ANAPROSAVE WHERE PRONUM=$Pronum AND PROPAR='$Propar' ORDER BY ROWID DESC ";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function CheckProtAnnullato($pronum, $propar) {
        $anapro_rec = $this->GetAnapro($pronum, 'codice', $propar);
        if ($anapro_rec['PROSTATOPROT'] == self::PROSTATO_ANNULLATO) {
            return true;
        }
        return false;
    }

    public function GetFilePathLogConservazione() {
        $anaent_53 = $this->GetAnaent('53');
        $FilePathLog = '';
        if ($anaent_53['ENTDE5']) {
            $FilePathLog = $anaent_53['ENTDE5'];
        }
        // Se manca?
        return $FilePathLog;
    }

    public function checkNotificaPecProt($pronum, $propar) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $ITALWEB_DB = $emlLib->getITALWEB();
        $Italweb = $ITALWEB_DB->getDB();
        $sql = "SELECT * "
                . " FROM PROMAIL "
                . " LEFT OUTER JOIN $Italweb.MAIL_ARCHIVIO MAIL_ARCHIVIO ON PROMAIL.IDMAIL = $Italweb.MAIL_ARCHIVIO.IDMAIL "
                . " WHERE PRONUM = $pronum AND PROPAR = '$propar' AND MAIL_ARCHIVIO.SENDREC = 'R' AND PROMAIL.IDMAIL <> '' "
                . " AND ("
                . "      MAIL_ARCHIVIO.PECTIPO = '" . emlMessage::PEC_TIPO_PRESA_IN_CARICO . "' OR "
                . "      MAIL_ARCHIVIO.PECTIPO = '" . emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA . "' OR "
                . "      MAIL_ARCHIVIO.PECTIPO = '" . emlMessage::PEC_TIPO_ERRORE_CONSEGNA . "' OR "
                . "      MAIL_ARCHIVIO.PECTIPO = '" . emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA . "' OR "
                . "      MAIL_ARCHIVIO.PECTIPO = '" . emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS . "' "
                . "     )  ";
        $MailArchivio_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);

        return $MailArchivio_tab;
    }

    /**
     * Innesta form in un div.
     * @param type $model Nome del model da innestare
     * @param type $container Contenitore dove viene innestata la form
     * @param type $onlyOnce
     * @param type $alias
     */
    public static function innestaForm($model, $container, $onlyOnce = false, $alias = '') {
        if (!$alias) {
            $alias = $model . '_' . time() . '_' . rand();
        }

        itaLib::openInner($model, true, false, $container, '', '', $alias);
        $objModel = itaFrontController::getInstance($model, $alias);
        $_POST['nameform'] = $alias;

        return $objModel;
    }

    public function ApriProtocollo($indice) {
        /*
         * Controllo accesso per l'utente:
         */
        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this, 'rowid', $indice);
        if (!$anaproctr_rec) {
            if (!$this->CheckAccessoProtDocAllFirma($indice)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Protocollo non accessibile");
                return false;
            }
        }
        $Anaproctr_rec = $this->GetAnapro($indice, 'rowid');
        $model = 'proArri';
        $_POST = array();
        $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
        $_POST['event'] = 'openform';
        $_POST['proGest_ANAPRO']['ROWID'] = $indice;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();

        return true;
    }

    public function GetParametriAccountMail() {
        $ElencoParmail = array();
        $anaent_28 = $this->GetAnaent('28');
        $Account_Tab = unserialize($anaent_28['ENTVAL']);

        foreach ($Account_Tab as $key => $Account) {
            $Account['ID'] = $key;
            $ElencoParmail[$Account['EMAIL']] = $Account;
        }

        return $ElencoParmail;
    }

    public function GetMailArchivioProtocollo($pronum, $propar) {
        $emlLib = new emlLib();
        $where = " PRONUM = $pronum AND PROPAR = '$propar' AND SENDREC = 'R' ORDER BY ROWID DESC";
        $Promail_rec = $this->getPromail($where);
        return $emlLib->getMailArchivio($Promail_rec['IDMAIL'], 'id');
    }

    public function GetProExtConser($codice, $tipo = 'ext', $multi = false, $Ordine = '') {
        $sql = "SELECT * FROM PROEXTCONSER ";
        switch ($tipo) {
            case'rowid':
                $sql .= " WHERE ROW_ID = $codice ";
                break;
            case 'ext':
                $sql .= " WHERE ESTENSIONE = '$codice' ";
                break;
            default:
                break;
        }
        $sql .= $Ordine;
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function getEestensioniUtilizzabili() {
        $Estensioni = array();
        $ProExtConser_tab = $this->GetProExtConser('', '', true);
        foreach ($ProExtConser_tab as $ProExtConser_rec) {
            $Ext = strtolower($ProExtConser_rec['ESTENSIONE']);
            $Estensioni[$Ext] = $ProExtConser_rec;
        }
        return $Estensioni;
    }

    public function getNotifiche($IdMailPadre) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();

        $sqlChilds = sprintf("SELECT ROWID, IDMAIL, PECTIPO, SUBJECT, FROMADDR, MSGDATE,DATAFILE,ACCOUNT FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '%s'", $IdMailPadre);
        $mailarchivio_childs = ItaDB::DBSQLSelect($emlLib->getITALWEB(), $sqlChilds);

        foreach ($mailarchivio_childs as $key => $mailarchivio_rec) {
            $mailarchivio_childs[$key]['DATASOURCE'] = $emlLib->SetDirectory($mailarchivio_rec['ACCOUNT']) . $mailarchivio_rec['DATAFILE'];
        }

        return $mailarchivio_childs;
    }

    public function getMailArrivo($Anapro_rec) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();

        if ($Anapro_rec['PROIDMAIL']) {
            $Idmail = $Anapro_rec['PROIDMAIL'];
        } else {
            $where = " AND DOCIDMAIL <> '' ";
            $Anadoc_rec = $this->GetAnadoc($Anapro_rec['PRONUM'], 'codice', false, $Anapro_rec['PROPAR'], $where);
            $Idmail = $Anadoc_rec['DOCIDMAIL'];
        }
        $sql = sprintf("SELECT ROWID, IDMAIL, PECTIPO, SUBJECT, FROMADDR, MSGDATE,DATAFILE,ACCOUNT FROM MAIL_ARCHIVIO WHERE IDMAIL = '%s'", $Idmail);
        $MailArchivio_tab = ItaDB::DBSQLSelect($emlLib->getITALWEB(), $sql);
        foreach ($MailArchivio_tab as $key => $MailArchivio_rec) {
            $MailArchivio_tab[$key]['DATASOURCE'] = $emlLib->SetDirectory($MailArchivio_rec['ACCOUNT']) . $MailArchivio_rec['DATAFILE'];
        }

        return $MailArchivio_tab;
    }

    public function lockAnapro($rowid) {
        $retLock = ItaDB::DBLock($this->getPROTDB(), "ANAPRO", $rowid, "", 120);
        if ($retLock['status'] != 0) {
            $this->setErrCode($retLock['status']);
            $this->setErrMessage('Blocco Tabella ANAPRO non Riuscito.');
            return false;
        }
        return $retLock;
    }

    public function unlockAnapro($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            return false;
        }
    }

    public function GetProDocProt($codice, $tipo = 'sorgnum', $sorgtipo = '') {
        if ($tipo == 'sorgnum') {
            $sql = "SELECT * FROM PRODOCPROT WHERE SORGNUM=$codice AND SORGTIP = '$sorgtipo' ";
        } else if ($tipo == 'destnum') {
            $sql = "SELECT * FROM PRODOCPROT WHERE DESTNUM=$codice AND DESTTIP = '$sorgtipo' ";
        } else {
            $sql = "SELECT * FROM PRODOCPROT WHERE ROWID=$codice";
        }
        $anadir_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        return $anadir_rec;
    }

    public function GetIterAperti($itepro, $itepar, $soloInGest = true) {
        $sql = "SELECT * FROM ARCITE
            WHERE 
            ARCITE.ITEPRO = '$itepro' AND 
            ARCITE.ITEPAR = '$itepar' AND 
            ARCITE.ITEFIN = ''  AND 
                  (ARCITE.ITENODO = 'ASS' OR ARCITE.ITENODO = 'TRX' )";
        if ($soloInGest) {
            $sql .= " AND ARCITE.ITEGES = '1'";
        }
        ///$sql.=" ORDER BY ANAPRO.PROPAR ASC ";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    public function GetAnaDocFromDocLink($pronum, $propar, $keyLink = '') {
        $sql = "SELECT * FROM ANADOC WHERE DOCNUM = $pronum AND DOCPAR = '$propar' AND DOCLNK = '$keyLink'";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function setProtIncompletoPerErroreInAllegati($msgError, $anapro_rec) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';

        /*
         * 1 Inseirmento audit errore:
         */
        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'DB' => $this->PROT_DB->getDB(),
            'DSet' => 'ANAPRO',
            'Operazione' => '06',
            'Estremi' => "Errore allegati {$anapro_rec['PRONUM']} " . $msgError,
            'Key' => $anapro_rec['ROWID']
        ));
        /*
         * 2 Inserimento tra le note 
         */
        $dati = array(
            'OGGETTO' => "Errore in salvataggio allegato. ",
            'TESTO' => "Errore riscontrato: " . $msgError,
            'CLASSE' => proNoteManager::NOTE_CLASS_PROTOCOLLO,
            'CHIAVE' => array('PRONUM' => $anapro_rec['PRONUM'], 'PROPAR' => $anapro_rec['PROPAR'])
        );
        $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $anapro_rec['PRONUM'], "PROPAR" => $anapro_rec['PROPAR']));
        $noteManager->aggiungiNota($dati);
        $noteManager->salvaNote();
        /*
         * 3 Rendo protocollo incompleto
         */
        $update_Info = 'Setto Incompleto per errore allegati: ' . $anapro_rec['PRONUM'] . ' ' . $anapro_rec['PROPAR'];
        $anapro_rec['PROSTATOPROT'] = self::PROSTATO_INCOMPLETO;
        try {
            ItaDB::DBUpdate($this->getPROTDB(), "ANAPRO", "ROWID", $anapro_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento ANAPRO " . $exc->getMessage());
            return false;
        }
        return true;
    }

    public function CheckAccessoProtDocAllFirma($indice) {
        // Controllo se è un documento alla firma: accessibile perchè ha accesso al documento.
        $Anaproctr_rec = $this->GetAnapro($indice, 'rowid');
        $ProDocProt = $this->GetProDocProt($Anaproctr_rec['PRONUM'], 'destnum', $Anaproctr_rec['PROPAR']);
        if ($ProDocProt) {
            $anaproctrSorg_rec = proSoggetto::getSecureAnaproFromIdUtente($this, 'codice', $ProDocProt['SORGNUM'], $ProDocProt['SORGTIP']);
            if ($anaproctrSorg_rec) {
                return $Anaproctr_rec;
            }
        }
        return false;
    }

    public function CheckAbilitaAnaSoggettiUnici() {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        // Controllo Anagrafe CityWareOnLine
        $devLib = new devLib();
        $Anagrafe_parm_rec = $devLib->getEnv_config('CONNESSIONI', 'codice', 'ANAGRAFE', false);
        $TipoAnagrafe = $Anagrafe_parm_rec['CONFIG'];
        if ($TipoAnagrafe == 'CityWareOnLine') {
            return true;
        }
        //Controllo Contabilita CityWareOnLine
        $devLib = new devLib();
        $Contab_param_rec = $devLib->getEnv_config('TIPIAPPLICATIVO', 'codice', 'CONTABILITA', false);
        $TipoContabilita = $Contab_param_rec['CONFIG'];
        if ($TipoContabilita == 'CityWareOnLine') {
            return true;
        }
        return false;
    }

    public function GetDatiResidenzaSoggettoUnico($ProSogg = '') {
        include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
        $this->libDB_FTA = new cwfLibDB_FTA();

        $FattResidStor_tab = $this->libDB_FTA->leggiFtaResidStorico(array('PROGSOGG' => $ProSogg));
        end($FattResidStor_tab);
        $lastKey = key($FattResidStor_tab);
        return $FattResidStor_tab[$lastKey];
    }

    public function GetRagioneTrasm($codice, $tipo = 'codice') {
        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM RAGIONITRASM WHERE CODICE =$codice";
                break;

            default:
                $sql = "SELECT * FROM RAGIONITRASM WHERE ROW_ID=$codice";
                break;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    /**
     * 
     * @param type $codice
     * @param type $tipo
     * @param type $multi
     * @param type $soloValidi
     * @param type $dataValidita
     * @param type $tipoPerm:  send | rec
     * @return type
     */
    public function GetUffMail($codice, $tipo = 'codice', $multi = true, $soloValidi = false, $dataValidita = '', $tipoPerm = '') {

        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM UFFMAIL WHERE UFFCOD = '$codice' ";
                break;
            case 'mail':
                $sql = "SELECT * FROM UFFMAIL WHERE MAIL = '$codice' ";
                break;
            default:
                $sql = "SELECT * FROM UFFMAIL WHERE ROW_ID = '$codice' ";
                break;
        }
        //
        if ($soloValidi) {
            if (!$dataValidita) {
                $dataValidita = date('Ymd');
            }
            $sql .= " AND DADATA <='$dataValidita' AND (ADATA >= '$dataValidita' OR ADATA = '') ";
        }
        // Controllo tipo permessi
        if ($tipoPerm) {
            switch ($tipoPerm) {
                case 'send':
                    $sql .= " AND PERM_SEND = 1 ";
                    break;
                case 'rec':
                    $sql .= " AND PERM_REC = 1 ";
                    break;
                default:
                    break;
            }
        }
        //Out::msginfo('test',$sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetMailAutorizzazioniUfficioUtente($utente = '', $ufficio = '', $tipoPerm = '') {
        if (!$utente) {
            $utente = App::$utente->getKey('nomeUtente');
        }
        $MailAutoriz = array();
        if ($ufficio) {
            $UffMail_tab = $this->GetUffMail($ufficio, 'codice', true, true);
            if (!$UffMail_tab) {
                /* Ricerca dal tramite ufficio padre delle mail autorizzate */
                if ($tipoPerm == 'send') {
                    $UffMail_tab = $this->GetMailAutorizzazioniUfficioPadre($ufficio, $tipoPerm);
                }
            }
            /* Aggiunto le mail estratte */
            foreach ($UffMail_tab as $UffMail_rec) {
                $MailAutoriz[] = $UffMail_rec;
            }
        } else {
            // Carico tutte le mail abilitate agli uffici dell'utente.
            include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
            $accLib = new accLib();
            $utenti_rec = $accLib->GetUtenti($utente, 'utelog');
            if ($utenti_rec['UTEANA__1']) {
                $Uffdes_tab = $this->GetUffdes($utenti_rec['UTEANA__1'], 'uffkey', '', ' ORDER BY UFFFI1__3 DESC', true);
                foreach ($Uffdes_tab as $Uffdes_rec) {
                    /*
                     * Per ogni ufficio estraggo le mail autorizzate.
                     * Se non le trova cerco tramite ufficio padre il primo padre con mail autorizzate.
                     */
                    $UffMail_tab = $this->GetUffMail($Uffdes_rec['UFFCOD'], 'codice', true, true, '', $tipoPerm);
                    if (!$UffMail_tab) {
                        /* Ricerca dal tramite ufficio padre delle mail autorizzate */
                        if ($tipoPerm == 'send') {
                            $UffMail_tab = $this->GetMailAutorizzazioniUfficioPadre($ufficio, $tipoPerm);
                        }
                    }
                    foreach ($UffMail_tab as $UffMail_rec) {
                        $MailAutoriz[] = $UffMail_rec;
                    }
                }
            }
        }
        return $MailAutoriz;
    }

    public function GetMailAutorizzazioniUfficioPadre($ufficio, $tipoPerm = '') {
        $UffMail_tab = array();
        $Anauff_rec = $this->GetAnauff($ufficio, 'codice');
        if ($Anauff_rec['CODICE_PADRE']) {
            $UffMail_tab = $this->GetUffMail($Anauff_rec['CODICE_PADRE'], 'codice', true, true, '', $tipoPerm);
            if (!$UffMail_tab) {
                $UffMail_tab = $this->GetMailAutorizzazioniUfficioPadre($Anauff_rec['CODICE_PADRE'], $tipoPerm);
            }
        }
        return $UffMail_tab;
    }

    public function getUoRiferimento($codiceUfficio) {
        $Anauff_rec = $this->GetAnauff($codiceUfficio, 'codice');
        if (!$Anauff_rec) {
            return $codiceUfficio;
        }
        if (!$Anauff_rec['CODICE_PADRE']) {
            return $codiceUfficio;
        }
        //Controllare se è una 'U'
        if ($Anauff_rec['TIPOUFFICIO'] == 'U' || $Anauff_rec['TIPOUFFICIO'] == '') {
            return $codiceUfficio;
        }

        $AnauffPadre_rec = $this->GetAnauff($Anauff_rec['CODICE_PADRE'], 'codice');
        if (!$AnauffPadre_rec) {
            return $codiceUfficio;
        }
        if ($AnauffPadre_rec['TIPOUFFICIO'] == 'U' || $AnauffPadre_rec['TIPOUFFICIO'] == '') {
            return $AnauffPadre_rec['UFFCOD'];
        }
        return $this->getUoRiferimento($AnauffPadre_rec['UFFCOD']);
    }

    public static $GetTipiSpedizione = array(
        self::TIPOSPED_ANALOGICA => 'Analogica',
        self::TIPOSPED_MAIL => 'Mail Normale',
        self::TIPOSPED_PEC => 'PEC',
        self::TIPOSPED_DIRETTA => 'Consegna Diretta',
        self::TIPOSPED_CART => 'CART'
    );

    public function msgQuestionLanciaProtocollo($nameForm) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $retAbilitati = array();
        $ArrBottoni = array();
        if ($profilo['PROT_ABILITATI'] == '') {
            $ArrBottoni = array(
                'F6-Documento Formale' => array('id' => $nameForm . '_LanciaDocFormale', 'model' => $nameForm, 'shortCut' => "f6"),
                'F8-Partenza' => array('id' => $nameForm . '_LanciaDocPartenza', 'model' => $nameForm, 'shortCut' => "f8"),
                'F5-Arrivo' => array('id' => $nameForm . '_LanciaDocArrivo', 'model' => $nameForm, 'shortCut' => "f5"));
            $retAbilitati = array("A", "P", "C");
        } else if ($profilo['PROT_ABILITATI'] == '1') {
            $ArrBottoni = array(
                'F6-Documento Formale' => array('id' => $nameForm . '_LanciaDocFormale', 'model' => $nameForm, 'shortCut' => "f6"),
                'F5-Arrivo' => array('id' => $this->nameForm . '_LanciaDocArrivo', 'model' => $this->nameForm, 'shortCut' => "f5"));
        } else if ($profilo['PROT_ABILITATI'] == '2') {
            $ArrBottoni = array(
                'F6-Documento Formale' => array('id' => $nameForm . '_LanciaDocFormale', 'model' => $nameForm, 'shortCut' => "f6"),
                'F8-Partenza' => array('id' => $nameForm . '_LanciaDocPartenza', 'model' => $nameForm, 'shortCut' => "f8"));
            $retAbilitati = array("P", "C");
        } else if ($profilo['PROT_ABILITATI'] == '3') {
            $retAbilitati = array("C");
        }
        /*
         * Controllo se attivare doc alla firma
         */
        $anaent_55 = $this->GetAnaent('55');
        if ($anaent_55['ENTDE2']) {
            $ArrBottoni['Documento Alla Firma'] = array('id' => $nameForm . '_DocumentoAllaFirma',
                'model' => $nameForm);
            $retAbilitati[] = "DOCFIRMA";
        }
        if (count($retAbilitati > 1)) {
            Out::msgQuestion("Protocollo.", "Seleziona il Tipo di Protocollo:", $ArrBottoni);
        }
        return $retAbilitati;
    }

}
