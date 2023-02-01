<?php

require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDati.class.php');
require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAcl.class.php');

class praLib {

    public $praErr;
    private $errCode;
    private $errMessage;

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    function __construct($libErr = null) {
        if (!$libErr) {
            //$this->praErr = new sueErr();
        } else {
            $this->praErr = $libErr;
        }
    }

    function __destruct() {
        
    }

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

    public function GetPramMaster($PRAM_DB) {
        $enteMaster = ItaDB::DBSQLSelect($PRAM_DB, "SELECT FILDE3 FROM FILENT WHERE FILKEY=1", false);
        if ($enteMaster['FILDE3']) {
            try {
                $PRAM_MASTER = ItaDB::DBOpen('PRAM', $enteMaster['FILDE3']);
            } catch (Exception $exc) {
                $PRAM_MASTER = $PRAM_DB;
            }
            return $PRAM_MASTER;
        } else {
            return $PRAM_DB;
        }
    }

    public function GetAnatip($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATIP WHERE TIPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATIP WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnatipimpo($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATIPIMPO WHERE CODTIPOIMPO = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATIPIMPO WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnapra($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPRA WHERE PRANUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAPRA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetIteevt($codice, $tipoRic = 'rowid', $PRAM_DB = "") {
        $sql = "SELECT * FROM ITEEVT WHERE ROWID = $codice";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnaeventi($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAEVENTI WHERE EVTCOD='" . $Codice . "'";
        } else if ($tipoRic == 'segnalazione') {
            $sql = "SELECT * FROM ANAEVENTI WHERE EVTSEGCOMUNICA='" . $Codice . "' ORDER BY EVTCOD";
        } else {
            $sql = "SELECT * FROM ANAEVENTI WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnatsp($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATSP WHERE TSPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATSP WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnahelp($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAHELP WHERE HELPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAHELP WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnaspa($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANASPA WHERE SPACOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANASPA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetPraclt($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRACLT WHERE CLTCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRACLT WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetPraidc($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAIDC WHERE IDCKEY = '$Codice'";
        } else {
            $sql = "SELECT * FROM PRAIDC WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetProges($Codice, $tipoRic = 'codice', $multi = false, $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $Codice . "'";
        } else if ($tipoRic == 'richiesta') {
            $sql = "SELECT * FROM PROGES WHERE GESPRA='" . $Codice . "'";
        } else if ($tipoRic == 'protocollo') {
            $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
        } else if ($tipoRic == 'antecedente') {
            $sql = "SELECT * FROM PROGES WHERE GESPRE='" . $Codice . "'";
        } else if ($tipoRic == 'codiceProcedimento') {
            $sql = "SELECT * FROM PROGES WHERE GESCODPROC='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PROGES WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function getAnaseriearc($codice, $tipo = 'codice', $PROT_DB = "") {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASERIEARC WHERE CODICE='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANASERIEARC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($PROT_DB, $sql, false);
    }

    public function GetProric($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRORIC WHERE RICNUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRORIC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetRicsta($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICSTA WHERE RICNUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM RICSTA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnacla($Codice, $tipoRic = 'codice', $multi = false, $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANACLA WHERE CLACOD='$Codice' ORDER BY CLADES";
        } elseif ($tipoRic == 'sportello') {
            $sql = "SELECT * FROM ANACLA WHERE CLASPO = $Codice ORDER BY CLADES";
        } elseif ($tipoRic == 'padre') {
            $sql = "SELECT * FROM ANACLA WHERE CLAPDR = '$Codice' ORDER BY CLADES";
        } else {
            $sql = "SELECT * FROM ANACLA WHERE ROWID='$Codice' ORDER BY CLADES";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetAnaddo($codice, $tipoRic = "codice", $multi = false, $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANADDO WHERE DDOCOD ='$codice'";
        } else {
            $sql = "SELECT * FROM ANADDO ORDER BY DDONOM";
        }

        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetPrasta($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRASTA WHERE STANUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRASTA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnastp($Codice, $PRAM_DB = "") {
        $sql = "SELECT * FROM ANASTP WHERE ROWID=$Codice";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetItepas($Codice, $tipoRic = 'itekey', $PRAM_DB = "", $multi = false) {
        if ($tipoRic == 'itekey') {
            $sql = "SELECT * FROM ITEPAS WHERE ITEKEY='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITEPAS WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetRicite($Codice, $tipoRic = 'itekey', $PRAM_DB = "", $multi = false, $ricnum = "") {
        if ($tipoRic == 'itekey') {
            $sql = "SELECT * FROM RICITE WHERE ITEKEY='" . $Codice . "' AND RICNUM='$ricnum'";
        } elseif ($tipoRic == 'ricnum') {
            $sql = "SELECT * FROM RICITE WHERE RICNUM='$Codice'";
        } elseif ($tipoRic == 'itectp') {
            $sql = "SELECT * FROM RICITE WHERE ITECTP='" . $Codice . "' AND RICNUM='$ricnum'";
        } else {
            $sql = "SELECT * FROM RICITE WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetPassoRapporto($Codice, $ricnum, $tipoRic = 'itekey', $PRAM_DB = "") {
        if ($tipoRic == 'itekey') {
            $sql = "SELECT * FROM RICITE WHERE RICNUM='$ricnum' AND ITEKEY='$Codice'";
        } elseif ($tipoRic == 'itectp') {
            $sql = "SELECT * FROM RICITE WHERE RICNUM='$ricnum' AND ITECTP='$Codice'";
        } else {
            $sql = "SELECT * FROM RICITE WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetRicdag($Codice, $tipoRic = 'codice', $PRAM_DB = "", $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICDAG WHERE DAGNUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM RICDAG WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetRicdoc($Codice, $tipoRic = 'codice', $PRAM_DB = "", $multi = false, $richiesta = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICDOC WHERE DOCUPL='" . $Codice . "'";
        } elseif ($tipoRic == 'itekey') {
            $sql = "SELECT * FROM RICDOC WHERE DOCNUM='$richiesta' AND ITEKEY='" . $Codice . "'";
        } elseif ($tipoRic == 'ricnum') {
            $sql = "SELECT * FROM RICDOC WHERE DOCNUM='$richiesta'";
        } else {
            $sql = "SELECT * FROM RICDOC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetPropas($codice, $tipoRic = 'propak', $multi = false, $PRAM_DB = "", $where = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM='$codice' ORDER BY PROSEQ";
        } else if ($tipoRic == 'propak') {
            $sql = "SELECT * FROM PROPAS WHERE PROPAK='$codice'";
        } else {
            $sql = "SELECT * FROM PROPAS WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetAnanom($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANANOM WHERE NOMRES='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANANOM WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetRicmail($Codice, $tipoRic = 'codice', $multi = false, $PRAM_DB = "", $where = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICMAIL WHERE RICNUM='$Codice'";
        } else {
            $sql = "SELECT * FROM RICMAIL WHERE ROWID=$Codice";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetFilent($Codice, $PRAM_DB = "") {
        $sql = "SELECT * FROM FILENT WHERE FILKEY=" . $Codice;
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnaarc($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAARC WHERE ARCCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAARC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnapar($Codice, $tipoRic = 'codice', $PRAM_DB = "", $multi = true) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPAR WHERE PARCLA='" . $Codice . "'";
        } elseif ($tipoRic == 'parkey') {
            $sql = "SELECT * FROM ANAPAR WHERE PARKEY='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAPAR WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetAnaset($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANASET WHERE SETCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANASET WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnaatt($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAATT WHERE ATTCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAATT WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetPasdoc($keyPasso, $Tipo = 'codice', $multi = false, $PRAM_DB = "") {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM PASDOC WHERE PASKEY = '$keyPasso'";
        } elseif ($Tipo == 'ROWID') {
            $sql = "SELECT * FROM PASDOC WHERE ROWID = '$keyPasso'";
        } elseif ($Tipo == 'pratica') {
            $sql = "SELECT * FROM PASDOC WHERE SUBSTRING(PASKEY,1,10) = '$keyPasso' AND PASFIL NOT LIKE '%info'";
        } elseif ($Tipo == 'numero') {
            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '$keyPasso%'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetAnauni($settore, $servizio, $unita, $tipoRic = 'codice', $PRAM_DB = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $settore . "' AND UNISER='" . $servizio . "' AND UNIOPE='" . $unita . "' AND UNIADD='' AND UNIAPE=''";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
    }

    public function GetAnadesdag($codice, $tipo = 'anades_rowid', $PRAM_DB = '', $multi = true, $deskey = '') {
        if ($tipo == 'anades_rowid') {
            $sql = "SELECT * FROM ANADESDAG WHERE ANADES_ROWID = '$codice'";
            if ($deskey) {
                $sql .= " AND DESKEY = '$deskey'";
            }
        } elseif ($tipo == 'deskey') {
            $sql = "SELECT * FROM ANADESDAG WHERE DESKEY = '$codice'";
        } else {
            $sql = "SELECT * FROM ANADESDAG WHERE ROW_ID = '$codice'";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    /**
     * 
     * @param type $codice
     * @param type $tipoRic
     * @param type $tutti 'si' interni ed esterni , 'no' solo interni
     * @param type $multi
     * @return type
     */
    public function GetAnamed($codice, $tipoRic = 'codice', $tutti = 'si', $multi = false, $noannullati = true) {
        $PROTDB = ItaDB::DBOpen('PROT', ITA_DB_SUFFIX);
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAMED WHERE MEDCOD='" . $codice . "'";
        } else if ($tipoRic == 'nome') {
            //$sql = "SELECT * FROM ANAMED WHERE MEDNOM LIKE '%" . addslashes($codice) . "%'";
            $sql = "SELECT * FROM ANAMED WHERE UCASE(MEDNOM) LIKE '%" . addslashes(strtoupper($codice)) . "%'";
        } else {
            $sql = "SELECT * FROM ANAMED WHERE ROWID='$codice'";
        }
        if ($tutti != 'si') {
            $sql .= " AND MEDUFF<>''";
        }
        if ($noannullati) {
            $sql .= " AND MEDANN=0";
        }
        return ItaDB::DBSQLSelect($PROTDB, $sql, $multi);
    }

    public function GetUffdes($codice, $tipo = 'uffkey', $uffsca = true, $ordina = ' ORDER BY UFFFI1__3 DESC', $soloValidi = false) {
        $PROTDB = ItaDB::DBOpen('PROT', ITA_DB_SUFFIX);
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
        $uffdes_rec = ItaDB::DBSQLSelect($PROTDB, $sql, $multi);
        return $uffdes_rec;
    }

    public function GetAnauff($codice, $tipo = 'codice') {
        $PROTDB = ItaDB::DBOpen('PROT', ITA_DB_SUFFIX);
        $multi = false;
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFCOD='" . $codice . "'";
        } else if ($tipo == 'uffser') {
            $sql = "SELECT * FROM ANAUFF WHERE UFFSER='" . trim($codice) . "'";
            $multi = true;
        } else {
            $sql = "SELECT * FROM ANAUFF WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($PROTDB, $sql, $multi);
    }

    public function GetRicsoggetti($arrCodici, $tipoRic = 'codice', $PRAM_DB = "", $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = '" . $arrCodici['ricnum'] . "'";
        } else if ($tipoRic == 'ruolo') {
            $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = '" . $arrCodici['ricnum'] . "' AND SOGRICRUOLO = '" . $arrCodici['ruolo'] . "'";
        } else if ($tipoRic == 'soggetto') {
            $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = '" . $arrCodici['ricnum'] . "' AND SOGRICRUOLO = '" . $arrCodici['ruolo'] . "'  AND SOGRICFIS = '" . $arrCodici['cf'] . "'";
        } else {
            $sql = "SELECT * FROM RICSOGGETTI WHERE ROW_ID = " . $arrCodici['rowid'];
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetRicAcl($codice, $tipoRic = 'idSoggetto', $PRAM_DB = "", $multi = false) {
        if ($tipoRic == 'idSoggetto') {
            $sql = "SELECT * FROM RICACL WHERE ROW_ID_RICSOGGETTI = " . $codice;
        } else {
            $sql = "SELECT * FROM RICACL WHERE ROW_ID = " . $codice;
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    public function GetAclSoggetto($Codice, $tipoRic = 'codice', $PRAM_DB = "") {
        $userFiscale = frontOfficeApp::$cmsHost->getCodFisFromUtente();
        if ($tipoRic == 'codice') {
            $sql = "SELECT 
                        *
                   FROM
                        RICACL
                   LEFT OUTER JOIN RICSOGGETTI ON RICACL.ROW_ID_RICSOGGETTI = RICSOGGETTI.ROW_ID
                   WHERE 
                        SOGRICNUM = '$Codice' AND
                        SOGRICFIS = '$userFiscale' AND
                        RICACL.RICACLTRASHED = 0 AND
                        RICACL.RICACLDATA_INIZIO <= '" . date('Ymd') . "' AND
                        RICACL.RICACLDATA_FINE >= '" . date('Ymd') . "'
                    ORDER BY RICACL.ROW_ID_PASSO";
        }
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
    }

    public function GetParametriEnte($ITALWEB_DB) {
        $ParametriEnte_rec = ItaDB::DBSQLSelect($ITALWEB_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE='" . ITA_DB_SUFFIX . "'", false);
        return $ParametriEnte_rec;
    }

    public function RegProcedimento($Codice, $Fiscale, $Oggi, $Cognom, $Nome, $Via, $Comune, $Cap, $Prov, $Sequenza, $PRAM_DB, $workYear, $Email, $Nazione = '', $DataNascita = '', $Denominazione = '', $tipo = '', $propak = '') {
        $praLibAcl = new praLibAcl();

        /*
         * Modifica per Evento 
         */
        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');

        /* @var $praLibEventi praLibEventi */
        $praLibEventi = new praLibEventi();
        $Evento = '';
        $EventoSegnalazioneComunica = '';
        $settore = $attivita = '';
        if (is_array($Codice)) {
            $Evento = $Codice['SUBPROC'];
            $EventoId = $Codice['SUBPROCID'];
            $settore = $Codice['SETTORE'];
            $attivita = $Codice['ATTIVITA'];
            $Codice = $Codice['PROCEDI'];

            $iteevt_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ITEEVT WHERE ITEPRA = '$Codice'", true);
            if (count($iteevt_tab) == 1) {
                $EventoId = $iteevt_tab[0]['ROWID'];
            }

            $iteevt_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ITEEVT WHERE ROWID = $EventoId", false);
            if (!$iteevt_rec) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0017', "Codice evento $Evento per procedimento $Codice non trovato in anagrafica", __CLASS__);
                return false;
            }
            $EventoSegnalazioneComunica = $praLibEventi->getSegnalazioneComunicaEvento($PRAM_DB, $Evento);
            if ($EventoSegnalazioneComunica == "") {
                $anapra_rec = $this->GetAnapra($Codice, "codice", $PRAM_DB);
                $EventoSegnalazioneComunica = $anapra_rec['PRASEG'];
            }
        } else {
            /*
             * Controllo presenza Eventi?
             */
            if ($praLibEventi->getEventi($PRAM_DB, $Codice) !== false) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0020', "Codice evento per procedimento $Codice non specificato", __CLASS__);
                return false;
            }
            $Evento = '';
            $anapra_rec = $this->GetAnapra($Codice, "codice", $PRAM_DB);
            if ($anapra_rec['PRASEG']) {
                $anaeventi_rec = $this->GetAnaeventi($anapra_rec['PRASEG'], 'segnalazione', $PRAM_DB);
                if ($anaeventi_rec) {
                    $Evento = $anaeventi_rec['EVTCOD'];
                }
            }
            $settore = $anapra_rec['PRASTT'];
            $attivita = $anapra_rec['PRAATT'];
            $EventoSegnalazioneComunica = $anapra_rec['PRASEG'];
        }


        /*
         * Fine modifica
         */

        /*
         * Ricerca e lettura dati esterni Aggiuntivi (cityware)
         * da astrarre in una funzione generica parametrizzabile da anagrafica
         * procedimento
         * 
         */
        $dataInizialiCw = false;
        if (frontOfficeApp::$cmsHost->autenticato() == 1) {
            $arrayCampi = frontOfficeApp::$cmsHost->getDatiUtente();
            if ($arrayCampi['ESIBENTE_CITY_PROGSOGG']) {
                $progsogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                $arrayXml = $this->getDatiInizialiCityWare($progsogg, $Codice, $Ricnum);
                if ($arrayXml == false) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0099', " Pratica n. $Ricnum ---> errore lettura dati iniziali cw", __CLASS__);
                    return false;
                } else {
                    $dataInizialiCw = $arrayXml;
                }
            }
        }

        $PRAM_DB_R = $this->GetPramMaster($PRAM_DB);
        //
        // Trovo l'ultimo progressivo Richiesta
        //
        $Proric_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT MAX(RICNUM) AS RICNUM FROM PRORIC WHERE RICNUM LIKE '" . $workYear . "%'", false);
        if ($Proric_rec['RICNUM'] == null) {
            $Ricnum = $workYear . "000001";
        } else {
            $Ricnum = $Proric_rec['RICNUM'] + 1;
        }
        if (!$Ricnum) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0977', "Numero pratica non trovato ", __CLASS__);
            return false;
        }

        //
        // Leggo da Anapra e decodifico valori
        //
        $Anapra_rec = $this->GetAnapra($Codice, 'codice', $PRAM_DB);
        if (!$Anapra_rec) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0976', "Codice Procedimento non trovato in anagrafica", __CLASS__);
            return false;
        }

        //
        // Leggo da Anatsp l'identificativo SUAP
        //
//        $Anatsp_rec = $this->GetAnatsp($Anapra_rec['PRATSP'], "codice", $PRAM_DB);
//        if (!$Anatsp_rec['TSPIDE']) {
//            output::$html_out = $this->praErr->parseError(__FILE__, 'E0120', "Identificativo Suap mancante nello sportello on-line " . $Anatsp_rec['TSPCOD'] . " ---> " . $Anatsp_rec['TSPDES'], __CLASS__);
//            return false;
//        }

        require_once ITA_LIB_PATH . '/itaPHPCore/itaUUID.class.php';

        $Proric_rec['RICNUM'] = $Ricnum;
        $Proric_rec['RICPRO'] = $Codice;
        $Proric_rec['RICRES'] = $Anapra_rec['PRARES'];
        $Proric_rec['RICSET'] = $Anapra_rec['PRASET'];
        $Proric_rec['RICSER'] = $Anapra_rec['PRASER'];
        $Proric_rec['RICOPE'] = $Anapra_rec['PRAOPE'];
        $Proric_rec['RICFIS'] = $Fiscale;
        $Proric_rec['RICDRE'] = $Oggi;
        $Proric_rec['RICORE'] = date("H:i:s");
        $Proric_rec['RICSOG'] = $Denominazione;
        $Proric_rec['RICCOG'] = utf8_decode($Cognom);
        $Proric_rec['RICNOM'] = utf8_decode($Nome);
        $Proric_rec['RICVIA'] = $Via;
        $Proric_rec['RICCOM'] = $Comune;
        $Proric_rec['RICCAP'] = $Cap;
        $Proric_rec['RICPRV'] = $Prov;
        $Proric_rec['RICANA'] = '';
        $Proric_rec['RICSTA'] = "99";
        $Proric_rec['RICSEQ'] = $Sequenza;
        $Proric_rec['RICEMA'] = $Email;
        //$Proric_rec['RICTSP'] = $Anapra_rec['PRATSP'];
        $Proric_rec['RICTSP'] = $iteevt_rec['IEVTSP'];
        $Proric_rec['RICSPA'] = '';
        $Proric_rec['RICRPA'] = $_POST['padre'];
        $Proric_rec['RICRUN'] = $_POST['accorpa'];
        $Proric_rec['RICNAZ'] = $Nazione;
        $Proric_rec['RICDAT'] = $DataNascita;
        $Proric_rec['RICEVE'] = $Evento;
        $Proric_rec['RICSEG'] = $EventoSegnalazioneComunica;
        $Proric_rec['RICTIP'] = $iteevt_rec['IEVTIP'];
        $Proric_rec['RICSTT'] = $settore;
        $Proric_rec['RICATT'] = $attivita;
        $Proric_rec['RICUUID'] = itaUUID::getV4();
//        $Proric_rec['RICSTT'] = $iteevt_rec['IEVSTT'];
//        $Proric_rec['RICATT'] = $iteevt_rec['IEVATT'];
        $Proric_rec['RICDESCR'] = $iteevt_rec['IEVDESCR'];

        if ($_SESSION['AUTOCERT_italsoft']['CURRENT_TOKEN']) {
            $Proric_rec['RICTOK'] = $_SESSION['AUTOCERT_italsoft']['CURRENT_TOKEN'];
        }

//
//Se integrazione, prendo lo sportello e l'aggregato del padre
//
        if ($Proric_rec['RICRPA']) {
            $proric_rec_padre = $this->GetProric($Proric_rec['RICRPA'], "codice", $PRAM_DB);
            $Proric_rec['RICTSP'] = $proric_rec_padre['RICTSP'];
            $Proric_rec['RICSPA'] = $proric_rec_padre['RICSPA'];
            $Proric_rec['RICSTT'] = $proric_rec_padre['RICSTT'];
            $Proric_rec['RICATT'] = $proric_rec_padre['RICATT'];

            $arrParam = array('ricnum' => $proric_rec_padre['RICNUM'], 'ruolo' => praRuolo::$SISTEM_SUBJECT_ROLES["DICHIARANTE"]['RUOCOD']);
            $arrayDichiaranti = $this->GetRicsoggetti($arrParam, 'ruolo', $PRAM_DB, true);
            if ($arrayDichiaranti) {
                foreach ($arrayDichiaranti as $dichiarante) {
                    $arraySoggetto = array(
                        'SOGRICNUM' => $Proric_rec['RICNUM'],
                        'SOGRICUUID' => $Proric_rec['RICUUID'],
                        'SOGRICFIS' => $dichiarante['SOGRICFIS'],
                        'SOGRICDENOMINAZIONE' => $dichiarante['SOGRICDENOMINAZIONE'],
                        'SOGRICRUOLO' => praRuolo::$SISTEM_SUBJECT_ROLES["DICHIARANTE"]['RUOCOD'],
                        'SOGRICRICDATA_INIZIO' => date("Ymd"),
                        'SOGRICDATA_FINE' => '',
                        'SOGRICNOTE' => $dichiarante['SOGRICNOTE']
                    );

                    if (!$praLibAcl->caricaSoggetto($arraySoggetto, $PRAM_DB, $proric_rec_padre['ROWID'])) {
                        // Il messaggio viene gestito nel metodo caricaSoggetto
                        return false;
                    }
                }
            }
        }

        /*
         * Se ï¿½ un parere, prendo la classificazione dal fasciolo padre
         * e mi salvo la chiave del passo BO
         */
        if ($propak) {
            $propas_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROPAS WHERE PROPAK = '$propak'", false);
            $Proges_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROGES WHERE GESNUM='" . $propas_rec['PRONUM'] . "'", false);
            $Proric_rec['RICTSP'] = $Proges_rec['GESTSP'];
            $Proric_rec['RICSPA'] = $Proges_rec['GESSPA'];
            $Proric_rec['RICSTT'] = $Proges_rec['GESSTT'];
            $Proric_rec['RICATT'] = $Proges_rec['GESATT'];
            $Proric_rec['PROPAK'] = $propak;
        }

        try {
            $nRows = ItaDB::DBInsert($PRAM_DB, "PRORIC", 'ROWID', $Proric_rec);
        } catch (Exception $e) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0003', $e->getMessage() . " Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }

        $idProric = $PRAM_DB->getLastId();

        $nominativo = $Proric_rec['RICSOG'];
        if (!$nominativo) {
            $nominativo = $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM'];
        }


        $arraySoggetto = array(
            'SOGRICNUM' => $Ricnum,
            'SOGRICUUID' => $Proric_rec['RICUUID'],
            'SOGRICFIS' => $Proric_rec['RICFIS'],
            'SOGRICDENOMINAZIONE' => $nominativo,
            'SOGRICRUOLO' => praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'],
            'SOGRICRICDATA_INIZIO' => $Proric_rec['RICDRE'],
            'SOGRICDATA_FINE' => '',
            'SOGRICNOTE' => ''
        );

        if (!$praLibAcl->caricaSoggetto($arraySoggetto, $PRAM_DB, $idProric)) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0099', $praLibAcl->getErrMessage() . " Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }

        /*
         * Crea cartelle di lavoro per la pratica
         */
        if (!$attachFolder = $this->getCartellaAttachmentPratiche($Ricnum)) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0004', "Creazione cartella <b>$attachFolder</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$repositoryFolderTesti = $this->getCartellaRepositoryPratiche($Ricnum . "/testiAssociati")) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0005', "Creazione cartella <b>$repositoryFolderTesti</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$repositoryFolderImg = $this->getCartellaRepositoryPratiche($Ricnum . "/immagini")) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0006', "Creazione cartella <b>$repositoryFolderImg</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$repositoryFolderMail = $this->getCartellaRepositoryPratiche($Ricnum . "/mail")) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0007', "Creazione cartella <b>$repositoryFolderMail</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$tempFolder = $this->getCartellaTemporaryPratiche($Ricnum)) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$logFolder = $this->getCartellaLog()) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$logFolder</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }

//
// Utilizzo il data base master se il procedimento è slave PRASLAVE=1
//
        $repositoryUrl = "";
        $repositoryUrlMail = ITA_MASTER_REPOSITORY;
        $tipoEnte = $this->GetTipoEnte($PRAM_DB);
        if ($Anapra_rec['PRASLAVE'] != 1) {
            $PRAM_DB_R = $PRAM_DB;
            //$repositoryUrlMail = ITA_PROC_REPOSITORY;
            $repositoryUrl = "";
        } elseif ($Anapra_rec['PRASLAVE'] == 1 && $PRAM_DB_R !== $PRAM_DB && ITA_MASTER_REPOSITORY) {
            $repositoryUrl = ITA_MASTER_REPOSITORY;
        }
        $sourceDocument = ITA_DOC_DOCUMENTI;

        //$Filent_rec = $this->GetFilent(1, $PRAM_DB_R);
        $Filent_rec = $this->GetFilent(1, $PRAM_DB);
        if ($Filent_rec['FILCOD'] == 1 || $tipoEnte == "M") {
            $repositoryUrlMail = ITA_PROC_REPOSITORY;
        }

        /*
         * Ribalta Azioni
         */
        $caricamentoAzioniEnte = false;
        $Praazioni_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PRAAZIONI WHERE PRANUM = '" . $Codice . "' OR PRATSP = '" . $iteevt_rec['IEVTSP'] . "'", true);
        foreach ($Praazioni_tab as $Praazioni_rec) {
            if ($Praazioni_rec['CLASSEAZIONE'] && $Praazioni_rec['METODOAZIONE']) {
                $caricamentoAzioniEnte = true;
                break;
            }
        }
        if (!$caricamentoAzioniEnte) {
            $Praazioni_tab = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM PRAAZIONI WHERE PRANUM = '" . $Codice . "' OR PRATSP = '" . $iteevt_rec['IEVTSP'] . "'", true);
        }
        if ($Praazioni_tab) {
            foreach ($Praazioni_tab as $Praazioni_rec) {
                $Ricazioni_rec = array();
                $Ricazioni_rec = $Praazioni_rec;
                unset($Ricazioni_rec["ROWID"]);
                $Ricazioni_rec['RICNUM'] = $Ricnum;
                try {
                    $nRows = ItaDB::DBInsert($PRAM_DB, "RICAZIONI", 'ROWID', $Ricazioni_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0009', $e->getMessage() . " Pratica N. " . $Ricnum . " Ribaltamento Azioni Fallito", __CLASS__);
                    return false;
                }
            }
        }

        //
        //ribalta passi
        //
        $escludiDatiAggiuntivi = array();
        $Itepas_tab = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM ITEPAS WHERE ITECOD = '" . $Codice . "' ORDER BY ITESEQ", true);
        if ($Itepas_tab) {
            $repConnector = new praRep($repositoryUrl);
            $repConnectorMailXml = new praRep($repositoryUrlMail);
            $repConnectorDocument = new praRep($sourceDocument);
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', ITA_DB_SUFFIX);
            $Ricite_rec_rif = array();
            foreach ($Itepas_tab as $Itepas_rec) {
                $Ricite_rec = array();
                $Ricite_rec = $Itepas_rec;

                unset($Ricite_rec["ROWID"]);
                $Ricite_rec['RICNUM'] = $Ricnum;
                $Ricite_rec['RCIRIS'] = "";
                //Se il responsabile del passo ï¿½ diverso da quello del procedimento, prendo quello del procedimento
                if ($Anapra_rec['PRARES'] != $Itepas_rec['ITERES']) {
                    $Ricite_rec['ITERES'] = $Anapra_rec['PRARES'];
                }
                try {
                    $nRows = ItaDB::DBInsert($PRAM_DB, "RICITE", 'ROWID', $Ricite_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0009', $e->getMessage() . " Pratica N. " . $Ricnum . " Passo sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
                    return false;
                }

                //ribalta Controlli
                $Itecontrolli_tab = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '{$Itepas_rec['ITEKEY']}' ORDER BY SEQUENZA", true);
                if ($Itecontrolli_tab) {
                    foreach ($Itecontrolli_tab as $Itecontrolli_rec) {
                        $Riccontrolli_rec = $Itecontrolli_rec;
                        $Riccontrolli_rec['RICNUM'] = $Ricnum;
                        try {
                            $nRows = ItaDB::DBInsert($PRAM_DB, "RICCONTROLLI", 'ROWID', $Riccontrolli_rec);
                        } catch (Exception $e) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0016', $e->getMessage() . ". Errore ribaltamento ITECONTROLLI Pratica N. " . $Ricnum . " Passo sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
                            return false;
                        }
                    }
                }

                //Aggiungo nuovi passi dal passo riferimento
                if ($Ricite_rec['ITERIF'] != 0 && $Ricite_rec['ITEPROC']) {
                    $sql = "SELECT * FROM RICITE WHERE RICNUM = $Ricnum AND ITEKEY = " . $Ricite_rec['ITEKEY'];
                    $Ricite_rec_rif = ItaDB::DBSQLSelect($PRAM_DB_R, $sql, false);
                    if (!$this->AggiuntaPassiDaRiferimento($Anapra_rec, $Ricite_rec_rif, $PRAM_DB_R, $PRAM_DB, $Ricnum, $repositoryFolderTesti, $repositoryFolderImg)) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0070', " Copia nuovi passi Pratica N. $Ricnum non riuscita", __CLASS__);
                        return false;
                    }
                }

                // copio il file
                if ($Ricite_rec['ITEDOW'] != 0 || $Ricite_rec['ITEFILE'] != 0 || $Ricite_rec['ITEDRR'] != 0) {
                    if ($Ricite_rec['ITEWRD'] != "") {
                        if (!file_exists($repositoryFolderTesti . "/" . $Ricite_rec['ITEWRD'])) {
                            $sql = "SELECT * FROM DOC_DOCUMENTI WHERE CONCAT(CODICE,'.',LOWER(TIPO)) = '" . $Ricite_rec['ITEWRD'] . "' AND CLASSIFICAZIONE = 'FO-SUAP'";
                            $docDocumenti = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
                            if ($docDocumenti) {
                                if (!$repConnectorDocument->getFile('documenti/' . $docDocumenti['URI'], $repositoryFolderTesti . "/" . $Ricite_rec['ITEWRD'], false)) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0010', $repConnectorDocument->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                                    return false;
                                }
                                $escludiDatiAggiuntivi[] = $Itepas_rec['ITEKEY'];
                            } else {
                                if (!$repConnector->getFile('testiAssociati/' . $Ricite_rec['ITEWRD'], $repositoryFolderTesti . "/" . $Ricite_rec['ITEWRD'], false)) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0010', $repConnector->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                                    return false;
                                }
                            }
                        }
                        if ($Ricite_rec['ITEFILE'] != 0) {
                            if ($repConnector->checkFile('testiAssociati/prefilled_' . $Ricite_rec['ITEWRD'])) {
                                if (!$repConnector->getFile('testiAssociati/prefilled_' . $Ricite_rec['ITEWRD'], $repositoryFolderTesti . "/prefilled_" . $Ricite_rec['ITEWRD'], true)) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0011', $repConnector->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                                    return false;
                                }
                            }
                        }
                    }
                }
                // copio le immagini di sfondo
                if ($Ricite_rec['ITEIMG'] != "") {
                    if (!file_exists($repositoryFolderImg . "/" . $Ricite_rec['ITEIMG'])) {
                        if (!$repConnector->getFile('immagini/' . $Ricite_rec['ITEIMG'], $repositoryFolderImg . "/" . $Ricite_rec['ITEIMG'], false)) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0012', $repConnector->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                            return false;
                        }
                    }
                }
            }

            // Riordino le sequenze con i passi aggiunti
            if (!$this->ordinaPassiPratica($Ricnum, $PRAM_DB)) {
                return false;
            }

            // copio file mail
            if (!$repConnectorMailXml->getFile('mail.xml', $repositoryFolderMail . "/mail.xml", false)) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0013', $repConnectorMailXml->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                return false;
            }
        } else {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', "Passi procedimento n. $Codice pratica n. $Ricnum non trovati", __CLASS__);
            return false;
        }

        //ribalta dati Aggiuntivi
        //$Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM ITEDAG WHERE ITECOD = '" . $Codice . "' ORDER BY ITEKEY, ITDSEQ", true);
        $sql = "SELECT * FROM ITEDAG WHERE ITECOD = '" . $Codice . "'";
        foreach ($escludiDatiAggiuntivi as $passoDatoAggiuntivo) {
            $sql .= " AND ITEKEY <> '$passoDatoAggiuntivo'";
        }
        $sql .= " ORDER BY ITEKEY, ITDSEQ";
        $Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB_R, $sql, true);
        if ($Itedag_tab) {
            $this->RegistraDatiAggiuntivi($Itedag_tab, $tipo, $Ricnum, $PRAM_DB);
        }

        // ribalto procedimenti obbligatori per la pratica
        $sql = "SELECT * FROM ITEPRAOBB WHERE OBBPRA = '" . $Codice . "'";
        $Itepraobb_tab = ItaDB::DBSQLSelect($PRAM_DB_R, $sql, true);
        if ($Itepraobb_tab) {
            $this->registraProcedimentiObbligatori($Itepraobb_tab, $Ricnum, $PRAM_DB);
        }

        // Valorizzo campo evento
        $ricdag_rec_evento = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $Ricnum . "' AND DAGTIP = 'Evento_Richiesta'", false);
        if ($ricdag_rec_evento) {
            $ricite_rec_evento = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM RICITE WHERE RICNUM = '" . $Ricnum . "' AND ITEKEY = '{$ricdag_rec_evento['ITEKEY']}' AND ITECOD = '{$ricdag_rec_evento['ITECOD']}'", false);
            $proric_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM = '" . $Ricnum . "'", false);
            if ($ricite_rec_evento && $proric_rec) {
                $ricdag_rec_evento['RICDAT'] = $Evento;
                $proric_rec['RICSEQ'] .= ".{$ricite_rec_evento['ITESEQ']}.";

                try {
                    ItaDB::DBUpdate($PRAM_DB, "PRORIC", 'ROWID', $proric_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0018', $e->getMessage() . " Agg. Evento Richiesta Sequenza PRORIC N. " . $Ricnum, __CLASS__);
                    return false;
                }

                try {
                    ItaDB::DBUpdate($PRAM_DB_R, "RICDAG", 'ROWID', $ricdag_rec_evento);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0019', $e->getMessage() . " Agg. Evento Richiesta Valore RICDAG N. " . $Ricnum, __CLASS__);
                    return false;
                }
            }
        }

        //Ribalta Passi Template
        if (!$this->RibaltaPassiTemplate($Ricnum, $Codice, $PRAM_DB, $PRAM_DB_R, $tipo)) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0033', "Errore Ribaltamento Passi Template per la pratica n. $Ricnum", __CLASS__);
            return false;
        }

        //Ribalta dati utente loggato se loggato
        if (frontOfficeApp::$cmsHost->autenticato() == 1) {
            $arrayCampi = frontOfficeApp::$cmsHost->getDatiUtente();
            foreach ($arrayCampi as $campo => $value) {
                $retRibalta = $this->ribaltaDatoUtenteFO($Ricnum, $Codice, $PRAM_DB, $campo, $value);
                if ($retRibalta['Status'] == "-1") {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $retRibalta['Message'] . " per la richiesta n. $Ricnum--->errore ins. dato utente $campo", __CLASS__);
                    return false;
                }
            }

            /*
             * Registro i dati iniziali da cityware
             * 
             */

            if ($arrayCampi['ESIBENTE_CITY_PROGSOGG'] && $dataInizialiCw) {
                $progsogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                $arrayXml = $dataInizialiCw;
                $campo_extra = "ESIBENTE_RESIDENTE";
                $Ricdag_rec = array();
                $Ricdag_rec['DAGNUM'] = $Ricnum;
                $Ricdag_rec['ITECOD'] = $Codice;
                $Ricdag_rec['ITEKEY'] = $Codice;
                $Ricdag_rec['DAGKEY'] = $campo_extra;
                $Ricdag_rec['DAGALIAS'] = $campo_extra;
                $Ricdag_rec['DAGVAL'] = $arrayXml['LIST'][0]['ROW'][0]['RESIDENTE'][0]['@textNode']; // FORZATO PER TEST
                $Ricdag_rec['RICDAT'] = $arrayXml['LIST'][0]['ROW'][0]['RESIDENTE'][0]['@textNode'];
                try {
                    $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum--->errore ins. dati utente", __CLASS__);
                    return false;
                }

                foreach ($arrayXml['LIST'][0]['ROW'][0] as $cw_key => $cw_value) {
                    if ($cw_key == '@textNode') {
                        continue;
                    }
                    if ($cw_key == '@attributes') {
                        continue;
                    }
                    $campo_extra = "CWDATI_$cw_key";
                    $Ricdag_rec = array();
                    $Ricdag_rec['DAGNUM'] = $Ricnum;
                    $Ricdag_rec['ITECOD'] = $Codice;
                    $Ricdag_rec['ITEKEY'] = $Codice;
                    $Ricdag_rec['DAGKEY'] = $campo_extra;
                    $Ricdag_rec['DAGALIAS'] = $campo_extra;
                    $Ricdag_rec['DAGVAL'] = $cw_value[0]['@textNode']; // FORZATO PER TEST
                    $Ricdag_rec['RICDAT'] = $cw_value[0]['@textNode'];
                    try {
                        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum--->errore ins. dati iniziali cw", __CLASS__);
                        return false;
                    }
                }
            }



            /*
             * Vecchia procedura
             */
//            if ($arrayCampi['ESIBENTE_CITY_PROGSOGG']) {
//                $progsogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
//                $arrayXml = $this->getDatiInizialiCityWare($progsogg, $Codice, $Ricnum);
//                if ($arrayXml == false) {
//                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0099', " Pratica n. $Ricnum--->errore lettura dati iniziali cw", __CLASS__);
//
//                    return false;
//                } else {
//                    $campo_extra = "ESIBENTE_RESIDENTE";
//                    $Ricdag_rec = array();
//                    $Ricdag_rec['DAGNUM'] = $Ricnum;
//                    $Ricdag_rec['ITECOD'] = $Codice;
//                    $Ricdag_rec['ITEKEY'] = $Codice;
//                    $Ricdag_rec['DAGKEY'] = $campo_extra;
//                    $Ricdag_rec['DAGALIAS'] = $campo_extra;
//                    $Ricdag_rec['DAGVAL'] = $arrayXml['LIST'][0]['ROW'][0]['RESIDENTE'][0]['@textNode']; // FORZATO PER TEST
//                    $Ricdag_rec['RICDAT'] = $arrayXml['LIST'][0]['ROW'][0]['RESIDENTE'][0]['@textNode'];
//                    try {
//                        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
//                    } catch (Exception $e) {
//                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum--->errore ins. dati utente", __CLASS__);
//                        return false;
//                    }
//
//                    foreach ($arrayXml['LIST'][0]['ROW'][0] as $cw_key => $cw_value) {
//                        if ($cw_key == '@textNode') {
//                            continue;
//                        }
//                        if ($cw_key == '@attributes') {
//                            continue;
//                        }
//                        $campo_extra = "CWDATI_$cw_key";
//                        $Ricdag_rec = array();
//                        $Ricdag_rec['DAGNUM'] = $Ricnum;
//                        $Ricdag_rec['ITECOD'] = $Codice;
//                        $Ricdag_rec['ITEKEY'] = $Codice;
//                        $Ricdag_rec['DAGKEY'] = $campo_extra;
//                        $Ricdag_rec['DAGALIAS'] = $campo_extra;
//                        $Ricdag_rec['DAGVAL'] = $cw_value[0]['@textNode']; // FORZATO PER TEST
//                        $Ricdag_rec['RICDAT'] = $cw_value[0]['@textNode'];
//                        try {
//                            $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
//                        } catch (Exception $e) {
//                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum--->errore ins. dati iniziali cw", __CLASS__);
//                            return false;
//                        }
//                    }
//                }
//            }
        }

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSostituzioni.class.php';
        $praLibSostituzioni = new praLibSostituzioni();
        if (!$praLibSostituzioni->aggiungiPassi($Proric_rec['RICNUM'], $Proric_rec['RICRPA'], $PRAM_DB)) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0022', " Pratica n. $Ricnum - Errore creazione passi integrazione", __CLASS__);
            return false;
        }

        /*
         * Valorizzo tipizzato 'Richiesta_unica' se la richiesta è
         * automaticamente accorpata
         */
        if ($Proric_rec['RICRUN']) {
            if (!$this->accorpaAPraticaUnica($PRAM_DB, $Ricnum, $Proric_rec['RICRUN'])) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0024', "accorpaAPraticaUnica: " . $this->getErrMessage(), __CLASS__);
                return false;
            }
        }

        return $Ricnum;
    }

    function GetTariffaPasso($ricite_rec, $PRAM_DB, $update = true) {
        /*
         * Controllo se il passo è un upload
         * e se è abilitato il pagamento
         */

        if (($ricite_rec['ITEUPL'] == 1 || $ricite_rec['ITEMLT'] == 1 || $ricite_rec['ITEDAT'] == 1) && $ricite_rec['ITEPAY'] == 1) {
            $anapra_rec = $this->GetAnapra($ricite_rec['ITECOD'], 'codice', $PRAM_DB);
            $proric_rec = $this->GetProric($ricite_rec['RICNUM'], 'codice', $PRAM_DB);

            /*
             * Prendo il listino valido in data odierna
             */

            $dataValidita = date('Ymd');

            $sql = "SELECT
                        CODLISVAL
                    FROM
                        ITELISVAL
                    WHERE
                        INILISVAL <= $dataValidita
                    AND
                        (
                            FINLISVAL = ''
                        OR
                            FINLISVAL >= $dataValidita
                        )
                    ORDER BY
                        INILISVAL DESC";

            $itelisval_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

            if (!$itelisval_rec) {
                return false;
            }

            $CODVAL = $itelisval_rec['CODLISVAL'];

            /*
             * Se c'è il dato agg TIPO_SPORTELLO valorizzato, cambio il valore dello sportello on-line di controllo
             */
            $src_codicesportello = $proric_rec['RICTSP'];
            $ricdag_recTipoSportello = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND DAGKEY = 'TIPO_SPORTELLO' AND RICDAT<>''", false);
            if ($ricdag_recTipoSportello) {
                $src_codicesportello = $ricdag_recTipoSportello['RICDAT'];
            }


            $src_settore = $proric_rec['RICSTT'];
            $src_attivita = $proric_rec['RICATT'];
            $src_procedimento = $ricite_rec['ITECOD'];
            $src_evento = $proric_rec['RICEVE'];
            $src_tipopasso = $ricite_rec['ITECLT'];
            $src_aggregato = $proric_rec['RICSPA'];

            $sql = "SELECT
                        *
                    FROM
                        ITELIS
                    WHERE
                        CODVAL = '$CODVAL'
                    AND
                        ATTIVO = 1
                    ORDER BY
                        SEQUENZA ASC";

            $itelis_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

            foreach ($itelis_tab as $itelis_rec) {
                if (
                        ($itelis_rec['CODICESPORTELLO'] == $src_codicesportello || $itelis_rec['CODICESPORTELLO'] == '0') &&
                        ($itelis_rec['SETTORE'] == $src_settore || $itelis_rec['SETTORE'] == '0') &&
                        ($itelis_rec['ATTIVITA'] == $src_attivita || $itelis_rec['ATTIVITA'] == '0') &&
                        (($itelis_rec['PROCEDIMENTO'] == $src_procedimento && $itelis_rec['PROCEDIMENTO'] != '') || $itelis_rec['PROCEDIMENTO'] == '*') &&
                        (($itelis_rec['EVENTO'] == $src_evento && $itelis_rec['EVENTO'] != '') || $itelis_rec['EVENTO'] == '*') &&
                        (($itelis_rec['TIPOPASSO'] == $src_tipopasso && $itelis_rec['TIPOPASSO'] != '') || $itelis_rec['TIPOPASSO'] == '*') &&
                        ($itelis_rec['AGGREGATO'] == $src_aggregato || $itelis_rec['AGGREGATO'] == '0')
//                      && (($itelis_rec['ITEKEY'] == $src_itekey && $itelis_rec['ITEKEY'] != '') || $itelis_rec['ITEKEY'] == '*')
                ) {

                    if ($update === true) {
                        if (!$this->AggiornaTariffa($ricite_rec, $itelis_rec, $PRAM_DB)) {
                            return false;
                        }
                    }
                    return $itelis_rec;
                }
            }
        }

        return false;
    }

    function AggiornaTariffa($ricite_rec, $itelis_rec, $PRAM_DB) {
        $Proric_rec = $this->GetProric($ricite_rec['RICNUM'], "codice", $PRAM_DB);
        if (strpos($Proric_rec['RICSEQ'], chr(46) . $ricite_rec['ITESEQ'] . chr(46)) !== false && $itelis_rec) {
            if ($ricite_rec['TARIFFA'] != $itelis_rec['IMPORTO']) {
                return false;
            }
        }
        $ricite_rec['TARIFFA'] = $itelis_rec['IMPORTO'];
        try {
            $nRows = ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $ricite_rec);
        } catch (Exception $e) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0009', $e->getMessage() . " Agg. Tariffa Pratica N. " . $ricite_rec['RICNUM'] . " Passo sequenza=" . $ricite_rec['ITESEQ'], __CLASS__);
            return false;
        }

        if ($itelis_rec) {
            foreach (array('PRAIMPO_CODICE' => $itelis_rec['CODICETIPOIMPO'], 'PRAIMPO_TARIFFA' => $itelis_rec['IMPORTO'], 'PRAIMPO_IUV' => '') as $dagkey => $dagval) {
                $Ricdag_rec = array();
                $ricdag_recCtr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "' AND DAGKEY = '$dagkey'", false);
                if ($ricdag_recCtr) {
                    $Ricdag_rec = $ricdag_recCtr;
                    if ($dagval) {
                        $Ricdag_rec['RICDAT'] = $dagval;
                        $Ricdag_rec['DAGVAL'] = $dagval;
                    }
                    try {
                        $nRows = ItaDB::DBUpdate($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0023', $e->getMessage() . " Agg. Dati aggiuntivi Tariffa Pratica N. " . $ricite_rec['RICNUM'] . " Passo sequenza=" . $ricite_rec['ITESEQ'], __CLASS__);
                        return false;
                    }
                } else {
                    $ricdag_recSeq = ItaDB::DBSQLSelect($PRAM_DB, "SELECT MAX(DAGSEQ) AS SEQ FROM RICDAG WHERE DAGNUM = '" . $ricite_rec['RICNUM'] . "' AND ITEKEY = '" . $ricite_rec['ITEKEY'] . "'", false);
                    $seq = $ricdag_recSeq['SEQ'] + 10;
                    $Ricdag_rec['DAGNUM'] = $ricite_rec['RICNUM'];
                    $Ricdag_rec['ITECOD'] = $ricite_rec['ITECOD'];
                    $Ricdag_rec['ITEKEY'] = $ricite_rec['ITEKEY'];
                    $Ricdag_rec['DAGSET'] = $ricite_rec['ITEKEY'] . "_01";
                    $Ricdag_rec['DAGSEQ'] = $seq;
                    $Ricdag_rec['DAGKEY'] = $dagkey;
                    $Ricdag_rec['DAGALIAS'] = $dagkey;
                    $Ricdag_rec['DAGVAL'] = $dagval;
                    $Ricdag_rec['RICDAT'] = $dagval;
                    try {
                        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0023', $e->getMessage() . " Inseriti Dati aggiuntivi Tariffa Pratica N. " . $ricite_rec['RICNUM'] . " Passo sequenza=" . $ricite_rec['ITESEQ'], __CLASS__);
                        return false;
                    }
                }
            }
        }
        return $itelis_rec;
    }

    function RegistraDatiAggiuntivi($Itedag_tab, $tipo, $Ricnum, $PRAM_DB) {
        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAcl.class.php');
        switch ($tipo) {
            case "autocert":
                foreach ($Itedag_tab as $Itedag_rec) {
                    $Ricdag_rec = array();
                    $Ricdag_rec['DAGNUM'] = $Ricnum;
                    $Ricdag_rec['ITECOD'] = $Itedag_rec['ITECOD'];
                    $Ricdag_rec['ITEKEY'] = $Itedag_rec['ITEKEY'];
                    $Ricdag_rec['DAGDES'] = $Itedag_rec['ITDDES'];
                    $Ricdag_rec['DAGSEQ'] = $Itedag_rec['ITDSEQ'];
                    $Ricdag_rec['DAGKEY'] = $Itedag_rec['ITDKEY'];
                    $Ricdag_rec['DAGALIAS'] = $Itedag_rec['ITDALIAS'];
                    $Ricdag_rec['DAGVAL'] = $Itedag_rec['ITDVAL'];
                    $Ricdag_rec['DAGTIP'] = $Itedag_rec['ITDTIP'];
                    $Ricdag_rec['DAGCTR'] = $Itedag_rec['ITDCTR'];
                    $Ricdag_rec['DAGNOT'] = $Itedag_rec['ITDNOT'];
                    $Ricdag_rec['DAGLAB'] = $Itedag_rec['ITDLAB'];
                    $Ricdag_rec['DAGTIC'] = $Itedag_rec['ITDTIC'];
                    $Ricdag_rec['DAGROL'] = $Itedag_rec['ITDROL'];
                    $Ricdag_rec['DAGVCA'] = $Itedag_rec['ITDVCA'];
                    $Ricdag_rec['DAGREV'] = $Itedag_rec['ITDREV'];
                    $Ricdag_rec['DAGLEN'] = $Itedag_rec['ITDLEN'];
                    $Ricdag_rec['DAGDIM'] = $Itedag_rec['ITDDIM'];
                    $Ricdag_rec['DAGDIZ'] = $Itedag_rec['ITDDIZ'];
                    $Ricdag_rec['DAGACA'] = $Itedag_rec['ITDACA'];
                    $Ricdag_rec['DAGPOS'] = $Itedag_rec['ITDPOS'];
                    $Ricdag_rec['DAGLABSTYLE'] = $Itedag_rec['ITDLABSTYLE'];
                    $Ricdag_rec['DAGFIELDSTYLE'] = $Itedag_rec['ITDFIELDSTYLE'];
                    $Ricdag_rec['DAGFIELDCLASS'] = $Itedag_rec['ITDFIELDCLASS'];
                    $Ricdag_rec['DAGEXPROUT'] = $Itedag_rec['ITDEXPROUT'];
                    try {
                        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                    } catch (Exception $e) {
                        praMup::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum chiave=" . $Ricdag_rec['ITEKEY'], __CLASS__);
                        return false;
                    }
                }
                break;
            case "sue":
            case "suap":
                $ITENRA_JSON = false;

                foreach ($Itedag_tab as $Itedag_rec) {
                    $Indice_raccolta_from = 1;
                    $sql = "SELECT * FROM RICITE WHERE RICNUM ='$Ricnum' AND ITEKEY='{$Itedag_rec['ITEKEY']}'";
                    $Ricite_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
                    //$Ricite_rec = $this->GetRicite($Itedag_rec['ITEKEY'], "itekey", $PRAM_DB);
                    if ($Ricite_rec['ITENRA'] == '') {
                        $Ricite_rec['ITENRA'] = 1;
                    } else {
                        $itenra_options = json_decode($Ricite_rec['ITENRA'], true);

                        if (!is_null($itenra_options) && is_array($itenra_options)) {
                            $ITENRA_JSON = true;
                            $Ricite_rec['ITENRA'] = '';

                            if ($itenra_options['FROM']) {
                                $Indice_raccolta_from = intval($itenra_options['FROM']);
                            }
                        }
                    }

                    if (is_numeric($Ricite_rec['ITENRA'])) {
                        for ($j = 1; $j <= $Ricite_rec['ITENRA']; $j++) {
                            $i = str_repeat("0", 2 - strlen($j)) . $j;
                            $Ricdag_rec = array();
                            $Ricdag_rec['DAGNUM'] = $Ricnum;
                            $Ricdag_rec['ITECOD'] = $Itedag_rec['ITECOD'];
                            $Ricdag_rec['ITEKEY'] = $Itedag_rec['ITEKEY'];
                            $Ricdag_rec['DAGDES'] = $Itedag_rec['ITDDES'];
                            $Ricdag_rec['DAGSEQ'] = $Itedag_rec['ITDSEQ'];
                            $Ricdag_rec['DAGKEY'] = $Itedag_rec['ITDKEY'];
                            $Ricdag_rec['DAGALIAS'] = $Itedag_rec['ITDALIAS'];
                            $Ricdag_rec['DAGVAL'] = $Itedag_rec['ITDVAL'];
                            $Ricdag_rec['DAGTIP'] = $Itedag_rec['ITDTIP'];
                            $Ricdag_rec['DAGCTR'] = $Itedag_rec['ITDCTR'];
                            $Ricdag_rec['DAGNOT'] = $Itedag_rec['ITDNOT'];
                            $Ricdag_rec['DAGLAB'] = $Itedag_rec['ITDLAB'];
                            $Ricdag_rec['DAGTIC'] = $Itedag_rec['ITDTIC'];
                            $Ricdag_rec['DAGROL'] = $Itedag_rec['ITDROL'];
                            $Ricdag_rec['DAGVCA'] = $Itedag_rec['ITDVCA'];
                            $Ricdag_rec['DAGREV'] = $Itedag_rec['ITDREV'];
                            $Ricdag_rec['DAGLEN'] = $Itedag_rec['ITDLEN'];
                            $Ricdag_rec['DAGDIM'] = $Itedag_rec['ITDDIM'];
                            $Ricdag_rec['DAGDIZ'] = $Itedag_rec['ITDDIZ'];
                            $Ricdag_rec['DAGACA'] = $Itedag_rec['ITDACA'];
                            $Ricdag_rec['DAGPOS'] = $Itedag_rec['ITDPOS'];
                            $Ricdag_rec['DAGMETA'] = $Itedag_rec['ITDMETA'];
                            $Ricdag_rec['DAGLABSTYLE'] = $Itedag_rec['ITDLABSTYLE'];
                            $Ricdag_rec['DAGFIELDSTYLE'] = $Itedag_rec['ITDFIELDSTYLE'];
                            $Ricdag_rec['DAGFIELDCLASS'] = $Itedag_rec['ITDFIELDCLASS'];
                            $Ricdag_rec['DAGEXPROUT'] = $Itedag_rec['ITDEXPROUT'];
                            $Ricdag_rec['DAGSET'] = $Itedag_rec['ITEKEY'] . "_$i";
                            $Ricdag_rec['DAGCLASSE'] = $Itedag_rec['ITDCLASSE'];
                            $Ricdag_rec['DAGMETODO'] = $Itedag_rec['ITDMETODO'];
                            try {
                                $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                            } catch (Exception $e) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
                                return false;
                            }
                        }
                    } else {
                        $Indice = str_repeat("0", 2 - strlen($Indice_raccolta_from)) . $Indice_raccolta_from;
                        $Ricdag_rec = array();
                        $Ricdag_rec['DAGNUM'] = $Ricnum;
                        $Ricdag_rec['ITECOD'] = $Itedag_rec['ITECOD'];
                        $Ricdag_rec['ITEKEY'] = $Itedag_rec['ITEKEY'];
                        $Ricdag_rec['DAGDES'] = $Itedag_rec['ITDDES'];
                        $Ricdag_rec['DAGSEQ'] = $Itedag_rec['ITDSEQ'];
                        $Ricdag_rec['DAGKEY'] = $Itedag_rec['ITDKEY'];
                        $Ricdag_rec['DAGALIAS'] = $Itedag_rec['ITDALIAS'];
                        $Ricdag_rec['DAGVAL'] = $Itedag_rec['ITDVAL'];
                        $Ricdag_rec['DAGTIP'] = $Itedag_rec['ITDTIP'];
                        $Ricdag_rec['DAGCTR'] = $Itedag_rec['ITDCTR'];
                        $Ricdag_rec['DAGNOT'] = $Itedag_rec['ITDNOT'];
                        $Ricdag_rec['DAGLAB'] = $Itedag_rec['ITDLAB'];
                        $Ricdag_rec['DAGTIC'] = $Itedag_rec['ITDTIC'];
                        $Ricdag_rec['DAGROL'] = $Itedag_rec['ITDROL'];
                        $Ricdag_rec['DAGVCA'] = $Itedag_rec['ITDVCA'];
                        $Ricdag_rec['DAGREV'] = $Itedag_rec['ITDREV'];
                        $Ricdag_rec['DAGLEN'] = $Itedag_rec['ITDLEN'];
                        $Ricdag_rec['DAGDIM'] = $Itedag_rec['ITDDIM'];
                        $Ricdag_rec['DAGDIZ'] = $Itedag_rec['ITDDIZ'];
                        $Ricdag_rec['DAGACA'] = $Itedag_rec['ITDACA'];
                        $Ricdag_rec['DAGPOS'] = $Itedag_rec['ITDPOS'];
                        $Ricdag_rec['DAGMETA'] = $Itedag_rec['ITDMETA'];
                        $Ricdag_rec['DAGLABSTYLE'] = $Itedag_rec['ITDLABSTYLE'];
                        $Ricdag_rec['DAGFIELDSTYLE'] = $Itedag_rec['ITDFIELDSTYLE'];
                        $Ricdag_rec['DAGFIELDCLASS'] = $Itedag_rec['ITDFIELDCLASS'];
                        $Ricdag_rec['DAGSET'] = $Itedag_rec['ITEKEY'] . "_$Indice";
                        $Ricdag_rec['DAGCLASSE'] = $Itedag_rec['ITDCLASSE'];
                        $Ricdag_rec['DAGMETODO'] = $Itedag_rec['ITDMETODO'];
                        try {
                            $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                        } catch (Exception $e) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
                            return false;
                        }
                    }
                }

                /*
                 * Rimuovo ITENRA se utilizzato come JSON
                 * (per lasciare la funzionalitï¿½ di campi multipli su praTemplateRaccoltaMultipla)
                 */
                if ($ITENRA_JSON) {
                    $Ricite_rec['ITENRA'] = '';

                    try {
                        ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $Ricite_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
                        return false;
                    }
                }

                break;
//            case 'suap':
//                foreach ($Itedag_tab as $key => $Itedag_rec) {
//                    $Ricdag_rec = array();
//                    $Ricdag_rec['DAGNUM'] = $Ricnum;
//                    $Ricdag_rec['ITECOD'] = $Itedag_rec['ITECOD'];
//                    $Ricdag_rec['ITEKEY'] = $Itedag_rec['ITEKEY'];
//                    $Ricdag_rec['DAGDES'] = $Itedag_rec['ITDDES'];
//                    $Ricdag_rec['DAGSEQ'] = $Itedag_rec['ITDSEQ'];
//                    $Ricdag_rec['DAGKEY'] = $Itedag_rec['ITDKEY'];
//                    $Ricdag_rec['DAGALIAS'] = $Itedag_rec['ITDALIAS'];
//                    $Ricdag_rec['DAGVAL'] = $Itedag_rec['ITDVAL'];
//                    $Ricdag_rec['DAGTIP'] = $Itedag_rec['ITDTIP'];
//                    $Ricdag_rec['DAGCTR'] = $Itedag_rec['ITDCTR'];
//                    $Ricdag_rec['DAGNOT'] = $Itedag_rec['ITDNOT'];
//                    $Ricdag_rec['DAGLAB'] = $Itedag_rec['ITDLAB'];
//                    $Ricdag_rec['DAGTIC'] = $Itedag_rec['ITDTIC'];
//                    $Ricdag_rec['DAGROL'] = $Itedag_rec['ITDROL'];
//                    $Ricdag_rec['DAGVCA'] = $Itedag_rec['ITDVCA'];
//                    $Ricdag_rec['DAGREV'] = $Itedag_rec['ITDREV'];
//                    $Ricdag_rec['DAGLEN'] = $Itedag_rec['ITDLEN'];
//                    $Ricdag_rec['DAGDIM'] = $Itedag_rec['ITDDIM'];
//                    $Ricdag_rec['DAGDIZ'] = $Itedag_rec['ITDDIZ'];
//                    $Ricdag_rec['DAGACA'] = $Itedag_rec['ITDACA'];
//                    $Ricdag_rec['DAGPOS'] = $Itedag_rec['ITDPOS'];
//                    $Ricdag_rec['DAGMETA'] = $Itedag_rec['ITDMETA'];
//                    $Ricdag_rec['DAGLABSTYLE'] = $Itedag_rec['ITDLABSTYLE'];
//                    $Ricdag_rec['DAGFIELDSTYLE'] = $Itedag_rec['ITDFIELDSTYLE'];
//                    $Ricdag_rec['DAGEXPROUT'] = $Itedag_rec['ITDEXPROUT'];
//                    try {
//                        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
//                    } catch (Exception $e) {
//                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $e->getMessage() . " Pratica n. $Ricnum sequenza=" . $Ricite_rec['ITESEQ'], __CLASS__);
//                        return false;
//                    }
//                }
//                break;
        }

        return true;
    }

    function RibaltaPassiTemplate($Ricnum, $Codice, $PRAM_DB, $PRAM_DB_R, $tipo) {
        //Mi scorro i dati aggiuntivi per vedere se ci sono passi template da cui prendere i dati aggiuntivi aggiornati
        $Ricdag_tab_itekey = ItaDB::DBSQLSelect($PRAM_DB, "SELECT DISTINCT ITEKEY FROM RICDAG WHERE DAGNUM='$Ricnum' AND ITECOD = '$Codice' ORDER BY ITEKEY, DAGSEQ", true);
        foreach ($Ricdag_tab_itekey as $Ricdag_rec) {
            $Passo_rec = $this->GetRicite($Ricdag_rec['ITEKEY'], "itekey", $PRAM_DB, false, $Ricnum);
            if ($Passo_rec['TEMPLATEKEY']) {
                //Se c'ï¿½ template key assegno alcuni campi di RICITE dal template
                $Passo_template = $this->GetItepas($Passo_rec['TEMPLATEKEY'], "itekey", $PRAM_DB_R);
                if ($Passo_rec['ITEDOW'] == 1) {
                    $Passo_rec['ITEWRD'] = $Passo_template['ITEWRD'];
                    $Passo_rec['ITEHELP'] = $Passo_template['ITEHELP'];
                } elseif ($Passo_rec['ITEDAT'] == 1 || $Passo_rec['ITERDM'] == 1) {
                    $Passo_rec['ITEHELP'] = $Passo_template['ITEHELP'];
                    $Passo_rec['ITECOL'] = $Passo_template['ITECOL'];
                    $Passo_rec['ITENRA'] = $Passo_template['ITENRA'];
                    $metadati_template = unserialize($Passo_template['ITEMETA']);
                    $testobase_template = $metadati_template['TESTOBASEXHTML'];
                    $metadati = unserialize($Passo_rec['ITEMETA']);
                    $metadati['TESTOBASEXHTML'] = $testobase_template;
                    $Passo_rec['ITEMETA'] = serialize($metadati);
                }
                try {
                    $nRows = ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $Passo_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0009', $e->getMessage() . " Pratica N. " . $Ricnum . " Passo sequenza=" . $Passo_rec['ITESEQ'], __CLASS__);
                    return false;
                }

                //
                $Ricdag_tab_passo = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$Ricnum' AND ITECOD = '" . $Passo_rec['ITECOD'] . "' AND ITEKEY='" . $Passo_rec['ITEKEY'] . "' ORDER BY ITEKEY, DAGSEQ", true);
                $Itedag_tab_passoTemplate = ItaDB::DBSQLSelect($PRAM_DB_R, "SELECT * FROM ITEDAG WHERE ITECOD = '" . $Passo_template['ITECOD'] . "' AND ITEKEY='" . $Passo_template['ITEKEY'] . "' ORDER BY ITEKEY, ITDSEQ", true);
                foreach ($Ricdag_tab_passo as $Ricdag_rec_passo) {
                    try {
                        $nrow = ItaDb::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $Ricdag_rec_passo['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        Out::msgStop("Errore", $e->getMessage());
                        return false;
                    }
                }
                foreach ($Itedag_tab_passoTemplate as $key => $Itedag_rec_passoTemplate) {
                    $Itedag_tab_passoTemplate[$key]['ITECOD'] = $Passo_rec['ITECOD'];
                    $Itedag_tab_passoTemplate[$key]['ITEKEY'] = $Passo_rec['ITEKEY'];
                }
                $this->RegistraDatiAggiuntivi($Itedag_tab_passoTemplate, $tipo, $Ricnum, $PRAM_DB);
            }
        }
        return true;
    }

    public function AggiuntaPassiDaRiferimento($Anapra_rec, $Ricite_rec, $PRAM_DB_R, $PRAM_DB, $Ricnum, $repositoryFolderTesti, $repositoryFolderImg) {

//
// Utilizzo il data base master se il procedimento ï¿½ slave PRASLAVE=1
//
        $repositoryUrl = "";
        if (!$Anapra_rec['PRASLAVE']) {
            $PRAM_DB_R = $PRAM_DB;
            $repositoryUrl = "";
        } elseif ($Anapra_rec['PRASLAVE'] && $PRAM_DB_R != $PRAM_DB && ITA_MASTER_REPOSITORY) {
            $repositoryUrl = ITA_MASTER_REPOSITORY;
        }
        $repConnector = new praRep($repositoryUrl);
        if ($Ricite_rec['ITERIF'] != 0 && $Ricite_rec['ITEPROC']) {
            if ($Ricite_rec['ITEDAP'] && $Ricite_rec['ITEALP']) {
                $Dal_passo_rec = $this->GetItepas($Ricite_rec['ITEDAP'], "itekey", $PRAM_DB_R);
                $Al_passo_rec = $this->GetItepas($Ricite_rec['ITEALP'], "itekey", $PRAM_DB_R);
                $sql = "SELECT * FROM ITEPAS WHERE ITECOD = " . $Ricite_rec['ITEPROC'] . " AND ITESEQ BETWEEN '" . $Dal_passo_rec['ITESEQ'] . "' AND '" . $Al_passo_rec['ITESEQ'] . "' ORDER BY ITESEQ";
                $Itepas_tab_new_passi = ItaDB::DBSQLSelect($PRAM_DB_R, $sql, true);
                if ($Itepas_tab_new_passi) {
                    $sequenza = $Ricite_rec['ITESEQ'];
                    $procedimento = $Ricite_rec['ITEPROC'];
                    $dalPasso = $Ricite_rec['ITEDAP'];
                    $alPasso = $Ricite_rec['ITEALP'];
                    $itecod = $Ricite_rec['ITECOD'];

                    $Domanda_tab = array();
                    $Ricite_tab_domande = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = $Ricnum AND ITEQST = 1", true);
                    foreach ($Ricite_tab_domande as $key1 => $Ricite_rec_domande) {
                        if ($Ricite_rec_domande['ITEVPA'] == $Ricite_rec['ITEKEY']) {
                            $Domanda_tab[] = $Ricite_rec_domande;
                        }
                        if ($Ricite_rec_domande['ITEVPN'] == $Ricite_rec['ITEKEY']) {
                            $Domanda_tab[] = $Ricite_rec_domande;
                        }
                    }
                    $oldItekey = $Ricite_rec['ITEKEY'];

                    try {
                        $nrow = ItaDb::DBDelete($PRAM_DB, 'RICITE', 'ROWID', $Ricite_rec['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        return false;
                    }
                    $i = 0;
                    foreach ($Itepas_tab_new_passi as $key => $Itepas_rec) {
                        $Ricite_rec = array();
                        $Ricite_rec = $Itepas_rec;
                        unset($Ricite_rec["ROWID"]);
                        $Ricite_rec['RICNUM'] = $Ricnum;
                        $Ricite_rec['RCIRIS'] = "";
                        $Ricite_rec['ITEPROC'] = $procedimento;
                        $Ricite_rec['ITEDAP'] = $dalPasso;
                        $Ricite_rec['ITEALP'] = $alPasso;
                        $Ricite_rec['ITECOD'] = $itecod;
                        $Ricite_rec['ITEKEY'] = $this->keyGenerator($itecod);
                        $i = $i + 1;

                        if ($i == 1) {
                            if ($Domanda_tab) {
                                foreach ($Domanda_tab as $keyDom => $Domanda_rec) {
                                    if ($Domanda_rec['ITEVPA'] == $oldItekey) {
//$Domanda_tab[$keyDom]['ITEVPA'] = $Ricite_rec['ITEKEY'];
                                        $Domanda_rec['ITEVPA'] = $Ricite_rec['ITEKEY'];
                                    }
                                    if ($Domanda_rec['ITEVPN'] == $oldItekey) {
//$Domanda_tab[$keyDom]['ITEVPN'] = $Ricite_rec['ITEKEY'];
                                        $Domanda_rec['ITEVPN'] = $Ricite_rec['ITEKEY'];
                                    }

                                    try {
                                        $nrow = ItaDB::DBUpdate($PRAM_DB, "RICITE", "ROWID", $Domanda_rec);
                                        if ($nrow == -1) {
                                            return false;
                                        }
                                    } catch (Exception $exc) {
                                        return false;
                                    }
                                }
                            }
                        }

                        $i = str_repeat("0", 2 - strlen($i)) . $i;
                        $Ricite_rec['ITESEQ'] = $sequenza . ".$i";
                        try {
                            $nRows = ItaDB::DBInsert($PRAM_DB, "RICITE", 'ROWID', $Ricite_rec);
                        } catch (Exception $e) {
                            return false;
                        }

// copio il file
                        if ($Ricite_rec['ITEDOW'] != 0 || $Ricite_rec['ITEFILE'] != 0) {
                            if ($Ricite_rec['ITEWRD'] != "") {
                                if (!file_exists($repositoryFolderTesti . "/" . $Ricite_rec['ITEWRD'])) {
                                    if (!$repConnector->getFile('testiAssociati/' . $Ricite_rec['ITEWRD'], $repositoryFolderTesti . "/" . $Ricite_rec['ITEWRD'], false)) {
                                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0015', $repConnector->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                                        return false;
                                    }
                                }
                            }
                        }
// copio le immagini di sfondo
                        if ($Ricite_rec['ITEIMG'] != "") {
                            if (!file_exists($repositoryFolderImg . "/" . $Ricite_rec['ITEIMG'])) {
                                if (!$repConnector->getFile('immagini/' . $Ricite_rec['ITEIMG'], $repositoryFolderImg . "/" . $Ricite_rec['ITEIMG'], false)) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0014', $repConnector->getErrorMessage() . " Pratica N. " . $Ricnum, __CLASS__);
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function keyGenerator($codice) {
        if ($codice == '') {
            Out::msgStop("Errore", "Errore nella creazione della chiave univoca per l'Iter!");
            return false;
        }
        usleep(50000); // 50 millisecondi;
        list($msec, $sec) = explode(" ", microtime());
        return $codice . $sec . substr($msec, 2, 2);
    }

    function ordinaPassiPratica($pratica, $PRAM_DB) {
        if ($pratica) {
            $new_seq = 0;
            $Ricite_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = $pratica ORDER BY ITESEQ", true);
            if (!$Ricite_tab) {
                return false;
            }
            foreach ($Ricite_tab as $keyPasso => $Ricite_rec) {
                $new_seq += 10;
                $Ricite_rec['ITESEQ'] = $new_seq;

                try {
                    $nrow = ItaDB::DBUpdate($PRAM_DB, "RICITE", "ROWID", $Ricite_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    return false;
                }
            }
            return true;
        }
    }

    public function DecodValori($Anapra_rec, $Appoggio, $PRAM_DB) {
        $Appoggio[1] = $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];

// RESPONSABILE PROCEDIMENTO
        if ($Anapra_rec['PRADES__1'] != "") {
            $Appoggio[13] = $Anapra_rec['PRARES'];
            $Ananom_rec = $this->GetAnanom($Anapra_rec['PRARES'], 'codice', $PRAM_DB);
            $Appoggio[2] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
            $Appoggio[20] = $Ananom_rec['NOMEML'];
            if ($Ananom_rec['NOMPRO'] != "") {
                if ($Anauni_rec['UNIPRO'] == "") {
                    $Anaarc_rec = $this->GetAnaarc("PP" . $Ananom_rec['NOMPRO'], 'codice', $PRAM_DB);
                } else {
                    $Anaarc_rec = $this->GetAnaarc("PP" . $Anauni_rec['UNIPRO'], 'codice', $PRAM_DB);
                }
                if ($Anaarc_rec != "") {
                    $Appoggio[3] = $Anaarc_rec['ARCDES'];
                }
            }
        }

// SETTORE
        if ($Anapra_rec['PRASET'] != "") {
            $Appoggio[14] = $Anapra_rec['PRASET'];
            $Anauni_rec['UNIDES'] = "";
            $Anauni_rec = $this->GetAnauni($Anapra_rec['PRASET'], '', '', 'codice', $PRAM_DB);
            $Appoggio[4] = $Anauni_rec['UNIDES'];
        }
// RESPONSABILE SETTORE
        if ($Anauni_rec['UNIRES'] != "") {
            $Appoggio[15] = $Anauni_rec['UNIRES'];
            $Ananom_rec = $this->GetAnanom($Anauni_rec['UNIRES'], 'codice', $PRAM_DB);
            if ($Ananom_rec != "") {
                $Appoggio[5] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
            }
            if ($Ananom_rec['NOMPRO']) {
                $Anaarc_rec = $this->GetAnaarc("PP" . $Ananom_rec['NOMPRO'], 'codice', $PRAM_DB);
                if ($Anaarc_rec != "") {
                    $Appoggio[6] = $Anaarc_rec['ARCDES'];
                }
            }
        }

// SERVIZIO
        if ($Anapra_rec['PRASER'] != "") {
            $Appoggio[16] = $Anapra_rec['PRASER'];
            $Anauni_rec = "";
            $Anauni_rec = $this->GetAnauni($Anapra_rec['PRASET'], $Anapra_rec['PRASER'], "", 'codice', $PRAM_DB);
            $Appoggio[7] = $Anauni_rec['UNIDES'];
        }

// RESPONSABILE SERVIZIO
        if ($Anauni_rec['UNIRES'] != "") {
            $Appoggio[17] = $Anauni_rec['UNIRES'];
            $Ananom_rec = $this->GetAnanom($Anauni_rec['UNIRES'], 'codice', $PRAM_DB);
            if ($Ananom_rec != "") {
                $Appoggio[8] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
            }

            if ($Ananom_rec['NOMPRO']) {
                $Anaarc_rec = $this->GetAnaarc("PP" . $Ananom_rec['NOMPRO'], 'codice', $PRAM_DB);
                if ($Anaarc_rec != "") {
                    $Appoggio[9] = $Anaarc_rec['ARCDES'];
                }
            }
        }

// UNITA' OPERATIVA
        if ($Anapra_rec['PRAOPE'] != "") {
            $Appoggio[18] = $Anapra_rec['PRAOPE'];
            $Anauni_rec['UNIDES'] = "";
            $Anauni_rec = $this->GetAnauni($Anapra_rec['PRASET'], $Anapra_rec['PRASER'], $Anapra_rec['PRAOPE'], 'codice', $PRAM_DB);
            $Appoggio[10] = $Anauni_rec['UNIDES'];
        }
// OPERATORE
        if ($Anauni_rec['UNIRES'] != "") {
            $Appoggio[19] = $Anauni_rec['UNIRES'];
            $Ananom_rec = $this->GetAnanom($Anauni_rec['UNIRES'], 'codice', $PRAM_DB);
            if ($Ananom_rec != "") {
                $Appoggio[11] = $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'];
            }
            if ($Ananom_rec['NOMPRO']) {
                $Anaarc_rec = $this->GetAnaarc("PP" . $Ananom_rec['NOMPRO'], 'codice', $PRAM_DB);
                if ($Anaarc_rec) {
                    $Appoggio[12] = $Anaarc_rec['ARCDES'];
                }
            }
        }
        return $Appoggio;
    }

    public function getCartellaAttachmentPratiche($codicePratica) {
        if (@is_dir(ITA_PRAT_ATTACHMENT . $codicePratica)) {
            $cartellaPratiche = ITA_PRAT_ATTACHMENT . $codicePratica;
        } else {
            if (!@mkdir(ITA_PRAT_ATTACHMENT . $codicePratica, 0777, true)) {
                return false;
            }
            $cartellaPratiche = ITA_PRAT_ATTACHMENT . $codicePratica;
        }
        return $cartellaPratiche;
    }

    public function getCartellaRepositoryPratiche($codicePratica) {
        if (@is_dir(ITA_PRAT_REPOSITORY . $codicePratica)) {
            $cartellaRepPratiche = ITA_PRAT_REPOSITORY . $codicePratica;
        } else {
            if (!@mkdir(ITA_PRAT_REPOSITORY . $codicePratica, 0777, true)) {
                return false;
            }
            $cartellaRepPratiche = ITA_PRAT_REPOSITORY . $codicePratica;
        }
        return $cartellaRepPratiche;
    }

    public function getCartellaTemporary() {
        if (@is_dir(ITA_PRAT_TEMPORARY)) {
            $cartellaTemp = ITA_PRAT_TEMPORARY;
        } else {
            if (!@mkdir(ITA_PRAT_TEMPORARY, 0777, true)) {
                return false;
            }
            $cartellaTemp = ITA_PRAT_TEMPORARY;
        }
        return $cartellaTemp;
    }

    public function getCartellaTemporaryPratiche($codicePratica) {
        if (@is_dir(ITA_PRAT_TEMPORARY . $codicePratica)) {
            $cartellaRepPratiche = ITA_PRAT_TEMPORARY . $codicePratica;
        } else {
            if (!@mkdir(ITA_PRAT_TEMPORARY . $codicePratica, 0777, true)) {
                return false;
            }
            $cartellaRepPratiche = ITA_PRAT_TEMPORARY . $codicePratica;
        }
        return $cartellaRepPratiche;
    }

    public function getTemporaryURL($codicePratica) {
        $cartellaRepPratiche = ITA_URL_TEMPORARY . $codicePratica;
        return $cartellaRepPratiche;
    }

    public function getCartellaRepositoryProcedimento($codiceProcedimento) {
        if (@is_dir(ITA_PROC_REPOSITORY . $codiceProcedimento)) {
            $cartellaProcedimento = ITA_PROC_REPOSITORY . $codiceProcedimento;
        } else {
            if (!@mkdir(ITA_PROC_REPOSITORY . $codiceProcedimento, 0777, true)) {
                return false;
            }
            $cartellaProcedimento = ITA_PROC_REPOSITORY . $codiceProcedimento;
        }
        return $cartellaProcedimento;
    }

    public function getCartellaLog() {
        if (@is_dir(ITA_PRAT_LOG)) {
            $cartellaLog = ITA_PRAT_LOG;
        } else {
            if (!@mkdir(ITA_PRAT_LOG, 0777, true)) {
                return false;
            }
            $cartellaLog = ITA_PRAT_LOG;
        }
        return $cartellaLog;
    }

    public function getCartellaResources() {
        if (!defined("ITA_PRAT_RESOURCES")) {
            return false;
        }

        if (@is_dir(ITA_PRAT_RESOURCES)) {
            $cartellaResource = ITA_PRAT_RESOURCES;
        } else {
            if (!@mkdir(ITA_PRAT_RESOURCES, 0777, true)) {
                return false;
            }
            $cartellaResource = ITA_PRAT_RESOURCES;
        }
        return $cartellaResource;
    }

    public function CreaXMLann($dati) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<ROOT>\r\n
                        <RICNUM><![CDATA[" . $dati['Proric_rec']['RICNUM'] . "]]></RICNUM>\r\n
                        <RICRPA><![CDATA[" . $dati['Proric_rec']['RICRPA'] . "]]></RICRPA>\r\n                            
                        <DATA><![CDATA[" . date("Ymd") . "]]></DATA>\r\n
                        <MOTIVO><![CDATA[" . $_POST['motivo'] . "]]></MOTIVO>\r\n
                </ROOT>";
        return $xml;
    }

    public function CreaXML($dati, $PRAM_DB) {
        $Ricite_tab_endo = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEPUB = 0 ORDER BY ITESEQ", true);
//        $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY IN (
//            SELECT ITEKEY FROM RICITE WHERE RICNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEPUB = 0 ORDER BY ITESEQ
//            )";

        $Ricdag_tab_endo = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY IN (
            SELECT ITEKEY FROM RICITE WHERE RICNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEPUB = 0 ORDER BY ITESEQ
            )", true);

        $Ricdag_tab_richiesta = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Proric_rec']['RICPRO'] . "'", true);
        $Ricdoc_tab_richiesta = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Proric_rec']['RICPRO'] . "'", true);

//$Ricite_tab = $dati['Ricite_tab'];
        $Ricite_tab = $dati['Navigatore']['Ricite_tab_new'];
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<ROOT>\r\n
                        <PRORIC>\r\n
                            <RICNUM><![CDATA[" . $dati['Proric_rec']['RICNUM'] . "]]></RICNUM>\r\n
                            <RICDRE><![CDATA[" . $dati['Proric_rec']['RICDRE'] . "]]></RICDRE>\r\n
                            <RICPRO><![CDATA[" . $dati['Proric_rec']['RICPRO'] . "]]></RICPRO>\r\n
                            <RICRES><![CDATA[" . $dati['Proric_rec']['RICRES'] . "]]></RICRES>\r\n
                            <RICANA><![CDATA[" . $dati['Proric_rec']['RICANA'] . "]]></RICANA>\r\n
                            <RICSOG><![CDATA[" . $dati['Proric_rec']['RICSOG'] . "]]></RICSOG>\r\n                                
                            <RICCOG><![CDATA[" . $dati['Proric_rec']['RICCOG'] . "]]></RICCOG>\r\n
                            <RICNOM><![CDATA[" . $dati['Proric_rec']['RICNOM'] . "]]></RICNOM>\r\n
                            <RICFIS><![CDATA[" . $dati['Proric_rec']['RICFIS'] . "]]></RICFIS>\r\n
                            <RICVIA><![CDATA[" . $dati['Proric_rec']['RICVIA'] . "]]></RICVIA>\r\n
                            <RICCOM><![CDATA[" . $dati['Proric_rec']['RICCOM'] . "]]></RICCOM>\r\n
                            <RICCAP><![CDATA[" . $dati['Proric_rec']['RICCAP'] . "]]></RICCAP>\r\n
                            <RICPRV><![CDATA[" . $dati['Proric_rec']['RICPRV'] . "]]></RICPRV>\r\n
                            <RICNAZ><![CDATA[" . $dati['Proric_rec']['RICNAZ'] . "]]></RICNAZ>\r\n                                
                            <RICNAS><![CDATA[" . $dati['Proric_rec']['RICNAS'] . "]]></RICNAS>\r\n                                
                            <RICSET><![CDATA[" . $dati['Proric_rec']['RICSET'] . "]]></RICSET>\r\n
                            <RICSER><![CDATA[" . $dati['Proric_rec']['RICSER'] . "]]></RICSER>\r\n
                            <RICOPE><![CDATA[" . $dati['Proric_rec']['RICOPE'] . "]]></RICOPE>\r\n
                            <RICEMA><![CDATA[" . $dati['Proric_rec']['RICEMA'] . "]]></RICEMA>\r\n
                            <RICTSP><![CDATA[" . $dati['Proric_rec']['RICTSP'] . "]]></RICTSP>\r\n
                            <RICSPA><![CDATA[" . $dati['Proric_rec']['RICSPA'] . "]]></RICSPA>\r\n
                            <RICDAT><![CDATA[" . date("Ymd") . "]]></RICDAT>\r\n
                            <RICTIM><![CDATA[" . date("H:i:s") . "]]></RICTIM>\r\n
                            <RICRPA><![CDATA[" . $dati['Proric_rec']['RICRPA'] . "]]></RICRPA>\r\n
                            <RICEVE><![CDATA[" . $dati['Proric_rec']['RICEVE'] . "]]></RICEVE>\r\n
                            <RICSEG><![CDATA[" . $dati['Proric_rec']['RICSEG'] . "]]></RICSEG>\r\n
                            <RICTIP><![CDATA[" . $dati['Proric_rec']['RICTIP'] . "]]></RICTIP>\r\n
                            <RICSTT><![CDATA[" . $dati['Proric_rec']['RICSTT'] . "]]></RICSTT>\r\n
                            <RICATT><![CDATA[" . $dati['Proric_rec']['RICATT'] . "]]></RICATT>\r\n
                            <RICDESCR><![CDATA[" . $dati['Proric_rec']['RICDESCR'] . "]]></RICDESCR>\r\n
                            <PROPAK><![CDATA[" . $dati['Proric_rec']['PROPAK'] . "]]></PROPAK>\r\n
                            <RICRUN><![CDATA[" . $dati['Proric_rec']['RICRUN'] . "]]></RICRUN>\r\n
                            <RICPC><![CDATA[" . $dati['Proric_rec']['RICPC'] . "]]></RICPC>\r\n
                            <RICUUID><![CDATA[" . $dati['Proric_rec']['RICUUID'] . "]]></RICUUID>\r\n
                        </PRORIC>\r\n";

        /* Export Passi */
        $xml .= " <RICITE>\r\n";
//
// Passi SUAP
//
        foreach ($Ricite_tab as $key => $Ricite_rec) {
            $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
            foreach ($Ricite_rec as $Chiave => $Campo) {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }

//
// Passi Endo procedimento
//
        foreach ($Ricite_tab_endo as $key => $Ricite_rec) {
            if ($Ricite_rec['ITEPUB'] == 0) {
                $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
                foreach ($Ricite_rec as $Chiave => $Campo) {
                    if ($Chiave == 'ITEMETA') {
                        $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                    } else {
                        $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                    }
                }
                $xml .= "</RECORD>\r\n";
            }
        }
        $xml .= "</RICITE>\r\n";

        /* export raccolta dati */
        $xml .= " <RICDAG>\r\n";

        foreach ($Ricdag_tab_richiesta as $key => $Ricdag_rec) {
            $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
            foreach ($Ricdag_rec as $Chiave => $Campo) {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }

        foreach ($dati['Navigatore']['Ricdag_tab_new'] as $key => $Ricdag_rec) {
            $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
            foreach ($Ricdag_rec as $Chiave => $Campo) {
                if ($Chiave == "DAGLAB" || $Chiave == "DAGDES") {
                    continue;
                }
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }

        foreach ($Ricdag_tab_endo as $key => $Ricdag_rec) {
            $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
            foreach ($Ricdag_rec as $Chiave => $Campo) {
                if ($Chiave == "DAGLAB" || $Chiave == "DAGDES") {
                    continue;
                }
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }

        $xml .= "</RICDAG>\r\n";

        /* export indice documenti */
        $xml .= " <RICDOC>\r\n";

        foreach ($Ricdoc_tab_richiesta as $key => $Ricdoc_rec) {
            $xml .= "<RECORD>\r\n";
            foreach ($Ricdoc_rec as $Chiave => $Campo) {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }

        foreach ($dati['Navigatore']['Ricdoc_tab_new'] as $key => $Ricdoc_rec) {
            $xml .= "<RECORD>\r\n"; // Key = \"".$key."\">\r\n";
            foreach ($Ricdoc_rec as $Chiave => $Campo) {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</RECORD>\r\n";
        }
        $xml .= "</RICDOC>\r\n";

        /*
         * Richieste Accorpate
         */
        $Richieste_accorpate = $this->GetRichiesteAccorpate($PRAM_DB, $dati['Proric_rec']['RICNUM']);
        $xml .= " <RICHIESTE_ACCORPATE>\r\n";
        foreach ($Richieste_accorpate as $Proric_rec_accorpato) {
            $xml .= "<XMLINFO><![CDATA[XMLINFO_" . $Proric_rec_accorpato['RICNUM'] . ".xml]]></XMLINFO>\r\n";
        }
        $xml .= "</RICHIESTE_ACCORPATE>\r\n";

        $xml .= "</ROOT>";
        return $xml;
    }

    public function getMimeType($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'fdf':
                $CType = "Application/vnd.fdf";
                break;
            case 'pdf':
                $CType = "Application/pdf";
                break;
            case 'txt':
                $CType = "text/plain";
                break;
            case 'html':
            case 'htm':
                $CType = "text/html";
                break;
            case 'jpg':
            case 'jpeg':
                $CType = "image/jpg";
                break;
            case 'gif':
                $CType = "image/gif";
                break;
            case 'png':
                $CType = "image/png";
                break;
            case 'doc':
            case 'docx':
                $CType = "Application/msword";
                break;
            case 'xls':
                $CType = "Application/vnd.ms-excel";
                break;
            case 'p7m':
                $CType = "Application/pkcs7";
                break;
            default:
                $CType = "Application/octet-stream";
                break;
        }
        return $CType;
    }

    function DecodeFileInfo($fileInfo, $fileName) {
        $chiave = substr(pathinfo($fileName, PATHINFO_FILENAME), -2, 2);
        $arrayDag = array();
        $arrayInfo = array();
        $arrayValue = array();
        $arrayField = array();

        $strInfo = file_get_contents($fileInfo['DATAFILE']);
        $arrayInfo = explode('---', $strInfo);
        unset($arrayInfo[0]);
        foreach ($arrayInfo as $field) {
            $arrayField = explode(chr(10), $field);
            $keyValue = $keyName = "";
            foreach ($arrayField as $value) {
                $arrayValue = explode(': ', $value);
                if (trim($arrayValue[0]) == 'FieldName') {
                    $keyName = trim(substr($arrayValue[1], 0, 60));
                    //$keyName = utf8_decode(trim(substr($arrayValue[1], 0, 60)));
                }
                if (trim($arrayValue[0]) == 'FieldValue') {
                    $keyValue = trim($arrayValue[1]);
                }
            }
            if ($keyName) {
                //$arrayDag[htmlentities($keyName)] = $keyValue;
                $arrayDag[$keyName] = $keyValue;
            }
        }
        return $arrayDag;
    }

    public function ControlliCampi($fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
        $msgErr = $DescCampoOblSe = '';
        if (!$chiavePasso) {
            return $msgErr;
        }

        $info = $this->DecodeFileInfo($fileInfo, '');
        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $chiavePratica . "' AND ITEKEY = '" . $chiavePasso . "' AND DAGCTR <> ''");
        if ($ricdag_tab) {
            $ret = array();
            foreach ($ricdag_tab as $keydag => $ricdag_rec) {
                $espressione = '';
                $controlli = unserialize($ricdag_rec['DAGCTR']);
                foreach ($controlli as $key => $controllo) {
                    switch ($controllo['OPERATORE']) {
                        case 'AND':
                            $espressione = $espressione . ' && ';
                            break;
                        case 'OR':
                            $espressione = $espressione . ' || ';
                            break;
                        default:
                            break;
                    }
                    $espressione = $espressione . '$info[\'' . $controllo['CAMPO'] . '\']';
                    $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                    $espressione = $espressione . '\'' . $controllo['VALORE'] . '\'';
                }
                $espressione = $espressione . '';
                if ($ricdag_rec['DAGKEY'] == $controllo['CAMPO']) {
                    eval('$ret[$keydag] = (' . $espressione . ');');
                }
            }
            foreach ($ricdag_tab as $keydag => $ricdag_rec) {
                $campo = ($ricdag_rec['DAGALIAS']) ? $ricdag_rec['DAGALIAS'] : $ricdag_rec['DAGKEY'];
                //$DescCampoOblSe = $this->CtrObbligatorioSe($ricdag_tab, $ricdag_rec, $fileInfo);
                $DescCampoOblSe = $this->CtrObbligatorioSe($ricdag_rec, $fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB);
                if ($ret[$keydag] == true && $info[$campo] == '') {
                    if ($ricdag_rec['DAGDES']) {
                        $msgErr = $msgErr . $ricdag_rec['DAGDES'] . ' mancante' . '<br>';
                    } else {
                        $msgErr = $msgErr . $ricdag_rec['DAGKEY'] . ' mancante' . '<br>';
                    }
                }
                if ($DescCampoOblSe) {
                    $msgErr = $msgErr . $DescCampoOblSe . ' mancante' . '<br>';
                }
            }
        }
        return $msgErr;
    }

    function CtrObbligatorioSe($ricdag_rec, $fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
        //
        // Prendo il nome e il valore del campo di controllo
        //
        $controlli = unserialize($ricdag_rec['DAGCTR']);
        $espressione = $ret = '';

        $campo = ($ricdag_rec['DAGALIAS']) ? $ricdag_rec['DAGALIAS'] : $ricdag_rec['DAGKEY'];
        $info = $this->DecodeFileInfo($fileInfo, '');
        $valueInfoCampo = $info[$campo];

        foreach ($controlli as $key => $controllo) {
            $campoControllo = $controllo['CAMPO'];
            $valueControllo = $controllo['VALORE'];
            $condizioneControllo = $controllo['CONDIZIONE'];


            //Mi trovo il record del campo presente nell'espressione per avere il DAGALAIS
            $ricdag_rec_ctr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$chiavePratica' AND ITEKEY = '$chiavePasso' AND DAGKEY = '$campoControllo'", false);

            //
            //Se il campo da controllare ï¿½ diverso dal campo che sto scorrendo eseguo il controllo 
            //
            if ($campoControllo != $ricdag_rec['DAGKEY']) {
                //
                //Mi riscorro i campi e quando trovo quello uguale a quello che devo controllare mi salvo il valore
                //
                $campoCtr = ($ricdag_rec_ctr['DAGALIAS']) ? $ricdag_rec_ctr['DAGALIAS'] : $ricdag_rec_ctr['DAGKEY'];

                //Valuto l'espressione
                switch ($controllo['OPERATORE']) {
                    case 'AND':
                        $espressione = $espressione . ' && ';
                        break;
                    case 'OR':
                        $espressione = $espressione . ' || ';
                        break;
                    default:
                        break;
                }
                $espressione = $espressione . '$info[\'' . $campoCtr . '\']';
                $espressione = $espressione . ' ' . $condizioneControllo . ' ';
                $espressione = $espressione . '\'' . $valueControllo . '\'';
                $espressione = $espressione . '';
            }
        }

        if (!$espressione) {
            return;
        }

        eval('$ret = (' . $espressione . ');');

        if ($ret == true && $valueInfoCampo == "") {
            return $ricdag_rec['DAGDES'] ?: $ricdag_rec['DAGKEY'];
        }
    }

//    function CtrObbligatorioSe_20160204($ricdag_rec, $fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
//        //
//        // Prendo il nome e il valore del campo di controllo
//        //
//        $controlli = unserialize($ricdag_rec['DAGCTR']);
//        foreach ($controlli as $controllo) {
//            $campoControllo = $controllo['CAMPO'];
//            $valueControllo = $controllo['VALORE'];
//            $condizioneControllo = $controllo['CONDIZIONE'];
//        }
//
//        $ret = "";
//        //Mi trovo il record del campo presente nell'espressione per avere il DAGALAIS
//        $ricdag_rec_ctr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$chiavePratica' AND ITEKEY = '$chiavePasso' AND DAGKEY = '$campoControllo'", false);
//
//        //
//        //Se il campo da controllare ï¿½ diverso dal campo che sto scorrendo eseguo il controllo 
//        //
//        if ($campoControllo != $ricdag_rec['DAGKEY']) {
//            $campo = ($ricdag_rec['DAGALIAS']) ? $ricdag_rec['DAGALIAS'] : $ricdag_rec['DAGKEY'];
//            $info = $this->DecodeFileInfo($fileInfo, '');
//            //
//            //Mi riscorro i campi e quando trovo quello uguale a quello che devo controllare mi salvo il valore
//            //
//            $campoCtr = ($ricdag_rec_ctr['DAGALIAS']) ? $ricdag_rec_ctr['DAGALIAS'] : $ricdag_rec_ctr['DAGKEY'];
//            $valueInfoCampo = $info[$campo];
//
//            //Valuto l'espressione
//            $espressione = $espressione . '$info[\'' . $campoCtr . '\']';
//            $espressione = $espressione . ' ' . $condizioneControllo . ' ';
//            $espressione = $espressione . '\'' . $valueControllo . '\'';
//            $espressione = $espressione . '';
//            eval('$ret = (' . $espressione . ');');
//            if ($ret == true && $valueInfoCampo == "") {
//                if ($ricdag_rec['DAGDES']) {
//                    return $ricdag_rec['DAGDES'];
//                } else {
//                    return $ricdag_rec['DAGKEY'];
//                }
//            }
//        }
//    }

    function ControlliValiditaCampi($fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$chiavePratica' AND ITEKEY = '$chiavePasso' AND DAGKEY <> DAGALIAS");
        $info = $this->DecodeFileInfo($fileInfo, '');
        $PRAM_DB_R = $this->GetPramMaster($PRAM_DB);
        $ret = array();
        foreach ($info as $campo => $value) {
            foreach ($ricdag_tab as $ricdag_rec) {
                if ($campo == $ricdag_rec['DAGALIAS']) {
                    $praidc_rec = $this->GetPraidc($ricdag_rec['DAGKEY'], "codice", $PRAM_DB_R);
                    $class = "";
                    $praidcCtr = null;
                    if ($praidc_rec['IDCFIA__1'] && $praidc_rec['IDCFIA__1'] != "D") {
                        $descCampo = ($ricdag_rec['DAGDES']) ? $ricdag_rec['DAGDES'] : $ricdag_rec['DAGKEY'];
                        require_once ITA_PRATICHE_PATH . "/PRATICHE_italsoft/praidcCtr." . $praidc_rec['IDCCTR'] . ".class.php";
                        $class = "praidcCtr" . $praidc_rec['IDCCTR'];
                        $praidcCtr = new $class();
                        if (!$praidcCtr->Controlla($value, $descCampo, $praidc_rec['IDCFIA__1'])) {
                            $ret[$ricdag_rec['DAGKEY']]["status"] = $praidcCtr->getErrorCode();
                            $ret[$ricdag_rec['DAGKEY']]["message"] = $praidcCtr->getMsgError();
//                            $ret[$praidc_rec['IDCFIA__1']][$ricdag_rec['DAGKEY']]["status"] = $praidcCtr->getErrorCode();
//                            $ret[$praidc_rec['IDCFIA__1']][$ricdag_rec['DAGKEY']]["message"] = $praidcCtr->getMsgError();
                        }
                    }
                }
            }
        }
        return $ret;
    }

//    function CtrObbligatorioSeOk($ricdag_tab1, $ricdag_rec, $fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
//        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$chiavePratica' AND ITEKEY = '$chiavePasso'", true);
//        //
//        // Prendo il nome e il valore del campo di controllo
//        //
//        $controlli = unserialize($ricdag_rec['DAGCTR']);
//        foreach ($controlli as $controllo) {
//            $campoControllo = $controllo['CAMPO'];
//            $valueControllo = $controllo['VALORE'];
//            $condizioneControllo = $controllo['CONDIZIONE'];
//        }
//        //
//        //Se il campo da controllare ï¿½ diverso dal campo che sto scorrendo eseguo il controllo 
//        //
//        if ($campoControllo != $ricdag_rec['DAGKEY']) {
//            $campo = ($ricdag_rec['DAGALIAS']) ? $ricdag_rec['DAGALIAS'] : $ricdag_rec['DAGKEY'];
//            $info = $this->DecodeFileInfo($fileInfo, '');
//            foreach ($ricdag_tab as $ricdag_rec_ctr) {
//                $valueInfoCampo = $valueInfoCampoControllo = $campoCtr = "";
//                if ($campoControllo == $ricdag_rec_ctr['DAGKEY']) {
//                    //
//                    //Mi riscorro i campi e quando trovo quello uguale a quello che devo controllare mi salvo il valore
//                    //
//                    $campoCtr = ($ricdag_rec_ctr['DAGALIAS']) ? $ricdag_rec_ctr['DAGALIAS'] : $ricdag_rec_ctr['DAGKEY'];
//                    $valueInfoCampoControllo = $info[$campoCtr];
//                    $valueInfoCampo = $info[$campo];
//                    break;
//                }
//            }
//
//            if ($condizioneControllo == "==") {
//                if ($valueInfoCampoControllo == $valueControllo && $valueInfoCampo == "") {
//                    //
//                    //Se valori dei campi coincidono e il valore del campo di controllo ï¿½ vuoto seganalo l'erroe
//                    //
//                    if ($ricdag_rec['DAGDES']) {
//                        return $ricdag_rec['DAGDES'];
//                    } else {
//                        return $ricdag_rec['DAGKEY'];
//                    }
//                }
//            } elseif ($condizioneControllo == "!=") {
//                if ($valueInfoCampoControllo != $valueControllo && $valueInfoCampo == "") {
//                    //
//                    //Se valori dei campi coincidono e il valore del campo di controllo ï¿½ vuoto seganalo l'erroe
//                    //
//                    if ($ricdag_rec['DAGDES']) {
//                        return $ricdag_rec['DAGDES'];
//                    } else {
//                        return $ricdag_rec['DAGKEY'];
//                    }
//                }
//            }
//        }
//    }
//
//    function CtrObbligatorioSeOld($ricdag_tab, $ricdag_rec, $fileInfo) {
//        //
//        // Prendo il nome e il valore del campo di controllo
//        //
//        $controlli = unserialize($ricdag_rec['DAGCTR']);
//        foreach ($controlli as $controllo) {
//            $campoControllo = $controllo['CAMPO'];
//            $valueControllo = $controllo['VALORE'];
//        }
//        //
//        //Se il campo da controllare ï¿½ diverso dal campo che sto scorrendo eseguo il controllo 
//        //
//        if ($campoControllo != $ricdag_rec['DAGKEY']) {
//            $campo = ($ricdag_rec['DAGALIAS']) ? $ricdag_rec['DAGALIAS'] : $ricdag_rec['DAGKEY'];
//            $info = $this->DecodeFileInfo($fileInfo, '');
//            foreach ($ricdag_tab as $ricdag_rec_ctr) {
//                $valueInfoCampo = "";
//                if ($campoControllo == $ricdag_rec_ctr['DAGKEY']) {
//                    //
//                    //Mi riscorro i campi e quando trovo quello uguale a quello che devo controllare mi salvo il valore
//                    //
//                    $campoCtr = ($ricdag_rec_ctr['DAGALIAS']) ? $ricdag_rec_ctr['DAGALIAS'] : $ricdag_rec_ctr['DAGKEY'];
//                    $valueInfoCampoControllo = $info[$campoCtr];
//                    $valueInfoCampo = $info[$campo];
//                    break;
//                }
//            }
//
//            if ($valueInfoCampoControllo == $valueControllo && $valueInfoCampo == "") {
//                //
//                //Se valori dei campi coincidono e il valore del campo di controllo ï¿½ vuoto seganalo l'erroe
//                //
//                if ($ricdag_rec['DAGDES']) {
//                    return $ricdag_rec['DAGDES'];
//                } else {
//                    return $ricdag_rec['DAGKEY'];
//                }
//            }
//        }
//    }
    //public function caricaCampi($fileInfo, $nomeFile, $datiPratica, $chiavePasso, $chiavePassoCtr, $PRAM_DB) {
    public function caricaCampi($fileInfo, $nomeFile, $dati) {
        $info = $this->DecodeFileInfo($fileInfo, '');
        foreach ($info as $Key => $valore) {
            $Ricdag_rec = array();
            $Ricdag_ctr_rec = array();
            if ($dati['Ricite_rec']['ITECTP']) {
                $Ricdag_ctr_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], "
                    SELECT * FROM RICDAG
                        WHERE
                    DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Ricite_rec']['ITECTP'] . "' AND DAGALIAS ='" . $Key . "'", false);
                if (!$Ricdag_ctr_rec) {
                    $Ricdag_ctr_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], "
                    SELECT * FROM RICDAG
                        WHERE
                    DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Ricite_rec']['ITECTP'] . "' AND DAGKEY ='" . $Key . "'", false);
                }
            }
            if ($Ricdag_ctr_rec) {
                $Ricdag_rec = $Ricdag_ctr_rec;
                unset($Ricdag_rec['ROWID']);
            } else {
                $Ricdag_rec["DAGKEY"] = $Key;
                $Ricdag_rec["DAGALIAS"] = $Key;
            }
            $Ricdag_rec["DAGSET"] = pathinfo($nomeFile, PATHINFO_FILENAME);
            $Ricdag_rec["DAGNUM"] = $dati['Proric_rec']['RICNUM'];
            $Ricdag_rec["ITECOD"] = $dati['Proric_rec']['RICPRO'];
            $Ricdag_rec["ITEKEY"] = $dati['Ricite_rec']['ITEKEY'];
            $Ricdag_rec["RICDAT"] = $valore;
            $nRows = ItaDB::DBInsert($dati['PRAM_DB'], "RICDAG", 'ROWID', $Ricdag_rec);
        }

        // Caricamento RICSOCSOG, tramite allineamento
        $praLibAcl = new praLibAcl();
        foreach (praLibAcl::$CURRENT_ROLES as $ruolo) {
            if (!$praLibAcl->sincronizzaSoggetto($dati, $ruolo, $this->praErr)) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0099', $praLibAcl->getErrMessage() . " Pratica N. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                return false;
            }
        }


        return true;
    }

    public function ControlloP7m($filep7m, $chiavePratica, $chiavePasso, $PRAM_DB, $ditta, $dati = array()) {
        $Ricite_rec_ctr = array();
        if ($chiavePasso) {
            $sql = "
            SELECT
                *
            FROM
                RICITE
            WHERE
                RICNUM = '" . $chiavePratica . "' AND ITEKEY = '" . $chiavePasso . "'";
            $Ricite_rec_ctr = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
        }

        $anapar_recTipoFirma = $this->GetAnapar("TIPO_VERIFICA_FIRMA", "parkey", $PRAM_DB, false);
        $p7m = itaP7m::getP7mInstance($filep7m, $anapar_recTipoFirma['PARVAL']);
        if ($p7m == false) {
            return "Verifica non riuscita";
        }

        if (!$p7m->isFileVerifyPassed()) {
            $messaggio = $p7m->getMessageErrorFileAsString();
            $p7m->cleanData();
            return "Errore Controllo Firma Digitale: " . $messaggio;
        }

        if ($Ricite_rec_ctr) {
            if ($Ricite_rec_ctr['ITEDRR'] == 1 || $Ricite_rec_ctr['ITEDISCOMUNICA'] == 1) {
                if ($Ricite_rec_ctr['ITEDRR'] == 1) {
                    $fileDRR = pathinfo($filep7m, PATHINFO_DIRNAME) . "/" . $Ricite_rec_ctr['RICNUM'] . '_' . $ditta . '_rapporto.pdf';
                } elseif ($Ricite_rec_ctr['ITEDISCOMUNICA'] == 1) {
                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibInfocamere.class.php';
                    $praLibInfocamere = new praLibInfocamere($this->praLib);
                    $praLibInfocamere->caricaPratica($dati);
                    $fileDRR = $praLibInfocamere->getCartellaZIP() . "/" . $praLibInfocamere->getFileNameDistinta();
                }
                if (file_exists($fileDRR)) {
                    $Anapar_rec_VerInteg = $this->GetAnapar('VERIFICA_INTEGRITA_DRR', 'parkey', $dati["PRAM_DB"], false);
                    if ($Anapar_rec_VerInteg['PARVAL'] == "Si") {
                        $sha1_drr = sha1_file($fileDRR);
                        $sha1_p7m = $p7m->getContentSHA();
                        if ($sha1_drr !== $sha1_p7m) {
                            $p7m->cleanData();
                            return "File firmato incongruente con il file scaricato.";
                        }
                    }
                } else {
                    $p7m->cleanData();
                    return "Controllo del contenuto del file firmato non applicabile scaricare il rapporto completo da firmare.";
                }
            } else {
                $contentFileName = $p7m->getContentFileName();
                $NomePdfFile = pathinfo($contentFileName, PATHINFO_BASENAME);
                @copy($contentFileName, $dati['CartellaAllegati'] . "/" . $NomePdfFile);
                $p7m->cleanData();
                return $NomePdfFile;
            }
        }

        /*
         * Verifica validità espressione controllo firma
         */

        if (isset($dati['Ricite_rec'])) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibFirma.class.php';
            $praLibFirma = new praLibFirma($PRAM_DB);
            $resultControlloFirma = $praLibFirma->eseguiVerificaFirma($p7m, $dati);

            if (!$resultControlloFirma) {
                $p7m->cleanData();
                return $praLibFirma->getErrMessage();
            }
        }

        $p7m->cleanData();
        return true;
    }

    /**
     *
     */
    public function ControlloInfo($fileInfo, $chiavePratica, $chiavePasso, $PRAM_DB) {
        $arrayInfo = array();
        $arrayCampi = array();
        $arrayInfo = $this->DecodeFileInfo($fileInfo, '');
        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $chiavePratica . "' AND ITEKEY = '" . $chiavePasso . "'");
        if ($ricdag_tab) {
            foreach ($ricdag_tab as $keydag => $ricdag_rec) {
//$arrayCampi[htmlentities(trim($ricdag_rec['DAGKEY']))] = '';
                if ($ricdag_rec['DAGALIAS']) {
                    $arrayCampi[trim($ricdag_rec['DAGALIAS'])] = '';
                } else {
                    $arrayCampi[trim($ricdag_rec['DAGKEY'])] = '';
                }
            }
            $diffInfo2Campi = array_diff_key($arrayInfo, $arrayCampi);
            $diffCampi2Info = array_diff_key($arrayCampi, $arrayInfo);
            $diffsCount = count($diffInfo2Campi) + count($diffCampi2Info);
            if ($diffsCount > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function CreaXmlDumpDataFieldsPdf($input, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"dump_data_fields\">\r\n";
        $xml .= "       <input>$input</input>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function CreaXmlEncrypt($input, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"encrypt\">\r\n";
        $xml .= "       <input>$input</input>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "       <strength>STANDARD_ENCRYPTION_128</strength>";
        $xml .= "       <permissions>";
        $xml .= "       	<allow>ALLOW_PRINTING</allow>";
        $xml .= "       </permissions>";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function CreaXmlFlattenPdf($input, $output, $completeXml = true) {
        $xml = "";
        if ($completeXml) {
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
            $xml .= "<root>\r\n";
        }
        $xml .= "    <task name=\"flatten\">\r\n";
        $xml .= "       <input>$input</input>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        if ($completeXml) {
            $xml .= "</root>";
        }
        return $xml;
    }

    public function CreaXmlCatPdf($arInput, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"cat\">\r\n";
        $xml .= "       <inputs>\r\n";
        foreach ($arInput as $key => $input) {
            $xml .= "       <input>" . $input['FILEPATH'] . "</input>\r\n";
        }
        $xml .= "       </inputs>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function CreaXmlOverlayPdf($arInput, $output) {
        /*
         * Usa la funzione Overlay solo se ci sono dei pdf sovrapposti.
         * ATTENZIONE l'overlay non permette formati di orientamento diversi dei pdf
         */
        $flOverlay = false;
        foreach ($arInput as $key => $input) {
            if ($input['COMPFLAG'] == "S") {
                $flOverlay = true;
                break;
            }
        }
        if ($flOverlay == false) {
            return $this->CreaXmlCatPdf($arInput, $output);
        }
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"overlay\">\r\n";
        $xml .= "       <inputs>\r\n";
        $firstInput = true;
        foreach ($arInput as $key => $input) {
            if (!$firstInput) {
                if ($input['COMPFLAG'] == "A" || $input['COMPFLAG'] == '') {
                    $xml .= "       <pageBreak/>";
                }
            } else {
                $firstInput = false;
            }
            $xml .= "       <input>" . $input['FILEPATH'] . "</input>\r\n";
        }
        $xml .= "       </inputs>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function CreaXmlFillPdf($dati, $Ricdag_tab, $input, $output, $completeXml = true) {
        $xml = "";
        if ($completeXml) {
            $xml .= "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
            $xml .= "<root>\r\n";
        }
        $xml .= "    <task name=\"fill_form\">\r\n";
        $xml .= "        <input>$input</input>\r\n";
        $xml .= "        <output>$output</output>\r\n";
        $xml .= "          <fields>\r\n";
        $pathLogo = $this->getCartellaResources();
        if ($pathLogo) {
            if (ITA_PRAT_RESOURCES != "" && file_exists($pathLogo . "pdf_logo.png")) {
                $xml .= "               <field>\r\n";
                $xml .= "                   <name>Logo</name>\r\n";
                $xml .= "                   <value> </value>\r\n";
                $xml .= "                   <properties>\r\n";
                $xml .= "                       <property name=\"BUTTON_IMAGE\"><![CDATA[" . $pathLogo . "pdf_logo.png]]></property>\r\n";
                $xml .= "                   </properties>\r\n";
                $xml .= "                </field>\r\n";
            }
        }

        foreach ($Ricdag_tab as $Ricdag_rec) {
//            $value = "";
            $partiDa = "";
            $Ricdag_rec = $this->ctrRicdagRec($Ricdag_rec, $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", "."));
            $defaultValue = "";
            $praidc_rec = array();
            if ($Ricdag_rec['DAGDIZ'] == "C") {
                $defaultValue = $Ricdag_rec['DAGVAL'];
            } elseif ($Ricdag_rec['DAGDIZ'] == "D") {
                $defaultValue = $dati['Navigatore']["Dizionario_Richiesta_new"]->getData($Ricdag_rec['DAGVAL']);
            } elseif ($Ricdag_rec['DAGDIZ'] == "T") {
                if (strpos($Ricdag_rec['DAGVAL'], "PARTI_DA_5") !== false) {
                    $partiDa = "_05";
                }
                $defaultValue_pre = $this->elaboraTemplateDefault($Ricdag_rec['DAGVAL'], $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData(), $partiDa);
                $defaultValue = str_replace("\\n", chr(13), $defaultValue_pre);
            } elseif ($Ricdag_rec['DAGDIZ'] == "A") {
                $praidc_rec = $this->GetPraidc($Ricdag_rec['DAGVAL'], "codice", $dati['PRAM_DB']);
                $defaultValue_pre = $this->valorizzaTemplate($praidc_rec['IDCDEF'], $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
                $defaultValue = str_replace("\\n", chr(13), $defaultValue_pre);
            }

            /*
             * Modifica per SELECT
             */

            $xmlOptions = '';
            $arrayOptions = array();

            switch ($Ricdag_rec['DAGDIZ']) {
                case 'C':
                    if ($Ricdag_rec['DAGVAL'] && strpos($Ricdag_rec['DAGVAL'], "|") !== false) {
                        $arrayOptions = explode("|", $Ricdag_rec['DAGVAL']);
                    }
                    break;

                case 'D':
                    $DatiDizionario = $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData();
                    list($Key1, $Key2) = explode(".", $Ricdag_rec['DAGVAL']);

                    foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                        if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                            $Index = intval(substr($KeyDict, -2));
                            $arrayOptions[$Index] = $ValDict;
                        }
                    }
                    break;

                case 'T':
                    $DatiDizionario = $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData();
                    $strTemplate = $Ricdag_rec['DAGVAL'];

                    /*
                     * Ricavo le variabili dal template
                     */
                    preg_match_all('/@{.*?\$([A-Z0-9._]*).*?}@/', $strTemplate, $Matches);

                    /*
                     * Ciclo le variabili matchate, per ogni variabili
                     * ciclo il suo dizionario in cerca delle chiavi
                     * formate da "VARIABILE" + "_XY".
                     * Utilizzo il valore XY come indice per $arrayOptions,
                     * ed aggiungo come valore il template (o l'$arrayOptions al medesimo indice se presente)
                     * sostituendo la variabile
                     * con il valore da dizionario
                     */
                    foreach ($Matches[1] as $matchKey => $Match) {
                        list($Key1, $Key2) = explode(".", $Match);
                        foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                            if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                                $Index = intval(substr($KeyDict, -2));
                                $arrayOptions[$Index] = str_replace($Match, $Match . '_' . substr($KeyDict, -2), (isset($arrayOptions[$Index]) ? $arrayOptions[$Index] : $strTemplate));
                            }
                        }
                    }

                    foreach ($arrayOptions as $optKey => $Option) {
                        $valoreTemplate = $this->valorizzaTemplate($Option, $DatiDizionario);

                        if (!trim($valoreTemplate)) {
                            unset($arrayOptions[$optKey]);
                        } else {
                            $arrayOptions[$optKey] = $valoreTemplate;
                        }
                    }

                    ksort($arrayOptions);
                    break;
            }

            if (count($arrayOptions)) {
                $xmlOptions = "<options>\r\n";
                $xmlOptions .= "    <option><![CDATA[ ]]></option>\r\n";
                foreach ($arrayOptions as $optionValue) {
                    //$xmlOptions .= "    <option>$optionValue</option>\r\n";
                    $xmlOptions .= "    <option><![CDATA[$optionValue]]></option>\r\n";
                }
                $xmlOptions .= "</options>\r\n";
            }

            /*
             * Fine Modifica SELECT
             */

            //$value = utf8_encode($defaultValue);
            $value = $defaultValue;

            $xml .= "           <field>\r\n";
            $xml .= "               <name><![CDATA[" . $Ricdag_rec['DAGALIAS'] . "]]></name>\r\n";
            $xml .= "               <value><![CDATA[$value]]></value>\r\n";
            $xml .= $xmlOptions;
            $xml .= "               <properties>\r\n";
            if ($Ricdag_rec['DAGROL'] == 1) {
                $xml .= "                   <property name=\"READ_ONLY\">1</property>\r\n";
            }
            $xml .= "               </properties>\r\n";
            $xml .= "           </field>\r\n";
        }
        $xml .= "          </fields>\r\n";
        $xml .= "    </task>\r\n";
//        $xml .= "    <task name=\"cat\">\r\n";
//        $xml .= "    </task>\r\n";
//        $xml .= "    <task name=\"flatten\">\r\n";
//        $xml .= "    </task>\r\n";
//        $xml .= "    <task name=\"dump_data\">\r\n";
//        $xml .= "    </task>\r\n";
        if ($completeXml) {
            $xml .= "</root>";
        }
        //return utf8_decode($xml);
        return $xml;
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
                'FILENAME' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    public function GetDatiImpresa($dati, $PRAM_DB = "") {
        $datiImpresa = array();
        $datiSportello = $this->GetDatiSportello($dati, $PRAM_DB);

        $sql = "
            SELECT * FROM RICDAG WHERE
                    DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "'
                AND
                DAGTIP IN ('DenominazioneImpresa', 'Codfis_InsProduttivo','Prov_InsProduttivo', 'Comune_InsProduttivo', 'Indir_InsProduttivo', 'Civico_InsProduttivo', 'Cap_InsProduttivo')";

        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        foreach ($Ricdag_tab as $key => $value) {
            switch ($value['DAGTIP']) {
                case 'DenominazioneImpresa':
                    $datiImpresa['denominazione_impresa'] = $value['RICDAT'];
                    break;

                case 'Codfis_InsProduttivo':
                    $datiImpresa['codfis_suap'] = $value['RICDAT'];
                    break;

                case 'Prov_InsProduttivo':
                    $datiImpresa['provincia_suap'] = $value['RICDAT'];
                    break;

                case 'Comune_InsProduttivo':
                    $datiImpresa['comune_suap'] = $value['RICDAT'];
                    break;

                case 'Indir_InsProduttivo':
                    $datiImpresa['indirizzo_suap'] = $value['RICDAT'];
                    break;

                case 'Civico_InsProduttivo':
                    $datiImpresa['num_civico_suap'] = $value['RICDAT'];
                    break;

                case 'Cap_InsProduttivo':
                    $datiImpresa['cap_suap'] = $value['RICDAT'];
                    break;
            }
        }

        /*
         * Campi che possono essere presi dai dati sportello
         */
        if (!$datiImpresa['provincia_suap']) {
            $datiImpresa['provincia_suap'] = $datiSportello['provincia_suap'];
        }

        if (!$datiImpresa['comune_suap']) {
            $datiImpresa['comune_suap'] = $datiSportello['comune_destinatario'];
        }

        $datiImpresa['cod_istat_suap'] = $istat = $datiSportello['cod_istat_suap'];

        if (!$datiImpresa['cap_suap']) {
            $datiImpresa['cap_suap'] = $datiSportello['cap_suap'];
        }

//        if ($datiImpresa['provincia_suap'] == 'PU') {
//            $datiImpresa['provincia_suap'] = 'PS';
//        }


        /*
         * Apro il DB COMUNI e decodificao i dati insediamento produttivo
         */
        $COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
        /*
         * Leggo dati provincia dell'insediamento produttivo da db COMUNI
         */
        $provinciaInsediamentoProduttivo_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM PROVINCE WHERE SIGLA = '{$datiImpresa['provincia_suap']}'", false);
        //$comuneProvinciaInsediamentoProduttivo_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '{$provinciaInsediamentoProduttivo_rec['PROVINCIA']}'", false);
        //$comuneInseriamentoProduttivo_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '{$datiImpresa['comune_suap']}'", false);

        /*
         * Scompongo l'indirizzo dell'insediamento produttivo
         */
        $posSpazioIns = strpos($datiImpresa['indirizzo_suap'], " ");
        $toponimoInsediamentoProduttivo = substr($datiImpresa['indirizzo_suap'], 0, $posSpazioIns);
        $viaInsediamentoproduttivo = trim(substr($datiImpresa['indirizzo_suap'], $posSpazioIns));

        $datiImpresa['insProduttivo_istat_provincia'] = substr($datiSportello['cod_istat_suap'], 0, 3); //substr($comuneProvinciaInsediamentoProduttivo_rec['CISTAT'], 0, 3);
        $datiImpresa['insProduttivo_descrizione_provincia'] = $provinciaInsediamentoProduttivo_rec['PROVINCIA'];
        $datiImpresa['insProduttivo_codice_catastale'] = $datiSportello['codice_catastale_destinatario']; //$comuneInseriamentoProduttivo_rec['NASCIT'];
        $datiImpresa['insProduttivo_toponimo_indirizzo'] = $toponimoInsediamentoProduttivo;
        $datiImpresa['insProduttivo_denominazione_stradale_indirizzo'] = $viaInsediamentoproduttivo;

        /*
         * Decodifica forma-giuridica 
         */
        $datiImpresa['formaGiuridica_impresa'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "FORMA_GIURIDICA");
        $arrFormaGiuridica = $this->GetArrayFormaGiuridica();
        $keyArrFormaGiurdica = array_search($datiImpresa['formaGiuridica_impresa'], $this->array_column($arrFormaGiuridica, 'CODICE'));
        $descFormaGiuridica = $arrFormaGiuridica[$keyArrFormaGiurdica]['DESCRIZIONE'];
        $datiImpresa['formaGiuridica_impresa_descrizione'] = $descFormaGiuridica;
        /*
         * Partita Iva
         */
        $datiImpresa['Piva_InsProduttivo'] = $this->GetSostituzionePiva($dati['Ricdag_tab_totali']);
        /*
         * Codici REA
         */
        $datiImpresa['provincia_rea'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "IC_PV_REA_IMPRESA");
        $datiImpresa['data_iscrizione_rea'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "IC_DISCR_REA_IMPRESA");
        $datiImpresa['codice_iscrizione_rea'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "IC_COD_REA");



        return $datiImpresa;
    }

    public function GetDatiSportello($dati, $PRAM_DB = "") {
        $datiSportello = array();
        if ($dati['Proric_rec']['RICSPA'] != 0) {
            $datiSportello['identificativo_suap'] = $dati['Anaspa_rec']['SPAENT'];
            $datiSportello['codice_catastale_destinatario'] = $dati['Anaspa_rec']['SPACCA'];
            $datiSportello['cciaa_destinataria'] = $dati['Anaspa_rec']['SPAPRV'];
            $datiSportello['provincia_suap'] = $dati['Anaspa_rec']['SPAPRO'];
            $datiSportello['comune_destinatario'] = $dati['Anaspa_rec']['SPACOM'];
            $datiSportello['cod_amm_suap'] = $dati['Anaspa_rec']['SPAAMMIPA'];
            $datiSportello['cod_aoo_suap'] = $dati['Anaspa_rec']['SPAAOO'];
            $datiSportello['cod_istat_suap'] = $dati['Anaspa_rec']['SPAIST'];
            $datiSportello['cap_suap'] = $dati['Anaspa_rec']['SPACAP'];
            $datiSportello['denominazione_suap'] = $dati['Anaspa_rec']['SPADES'];
        } else {
            $datiSportello['identificativo_suap'] = $dati['Anatsp_rec']['TSPENT'];
            $datiSportello['codice_catastale_destinatario'] = $dati['Anatsp_rec']['TSPCCA'];
            $datiSportello['cciaa_destinataria'] = $dati['Anatsp_rec']['TSPPRV'];
            $datiSportello['provincia_suap'] = $dati['Anatsp_rec']['TSPPRO'];
            $datiSportello['comune_destinatario'] = $dati['Anatsp_rec']['TSPCOM'];
            $datiSportello['cod_istat_suap'] = $dati['Anatsp_rec']['TSPIST'];
            $datiSportello['cod_amm_suap'] = $dati['Anatsp_rec']['TSPAMMIPA'];
            $datiSportello['cod_aoo_suap'] = $dati['Anatsp_rec']['TSPAOO'];

            $datiSportello['cap_suap'] = $dati['Anatsp_rec']['TSPCAP'];
            $datiSportello['denominazione_suap'] = $dati['Anatsp_rec']['TSPDEN'];
        }
//        if ($datiSportello['cciaa_destinataria'] == 'PU') {
//            $datiSportello['cciaa_destinataria'] = 'PS';
//        }
//        if ($datiSportello['provincia_suap'] == 'PU') {
//            $datiSportello['provincia_suap'] = 'PS';
//        }
        return $datiSportello;
    }

    public function GetDatiAdempimento($dati, $PRAM_DB = "") {
        //$tipoSegnalazioneDaPasso = $this->GetValueDatoAggiuntivo($dati['Navigatore']['Ricdag_tab_new'], "TIPOLOGIA_SEGNALAZIONE");
        //
        $datiAdempimento = array();

        switch ($dati['Proric_rec']['RICTSP']) {
            case 2:
                $datiAdempimento['tipologia_adempimento'] = "ordinario";
                break;
            case 1:
            default:
                $datiAdempimento['tipologia_adempimento'] = "SCIA";
                break;
        }

        $segnalazione = $dati['Anapra_rec']['PRASEG'];
        if ($dati['CodiceEvento'] && $dati['Anaeventi_rec']['EVTSEGCOMUNICA']) {
            $segnalazione = $dati['Anaeventi_rec']['EVTSEGCOMUNICA'];
        }

        if ($segnalazione == "ALTRO") {
            $datiAdempimento['oggetto_comunicazione'] = "COMUNICAZIONE";
        } else {
            $datiAdempimento['oggetto_comunicazione'] = $segnalazione;
//            if ($tipoSegnalazioneDaPasso) {
//                $datiAdempimento['oggetto_comunicazione'] = $tipoSegnalazioneDaPasso;
//            }
        }
        $datiAdempimento['nome_adempimento'] = $dati['Anapra_rec']['PRADES__1'];
        $datiAdempimento['user_telemaco'] = frontOfficeApp::$cmsHost->getAltriDati('ita_usertelemaco');
        $datiAdempimento['tipologia_segnalazione'] = $segnalazione;
//        if ($tipoSegnalazioneDaPasso) {
//            $datiAdempimento['tipologia_segnalazione'] = $tipoSegnalazioneDaPasso;
//        }


        return $datiAdempimento;
    }

    public function GetAllegatiAdempimento($dati, $PRAM_DB = "") {
        $allegatiAdempimento = array();
        $allegatiAdempimento['nome_file_xml'] = "";
        $allegatiAdempimento['descrizione_pdf'] = "PDF ADEMPIMENTO";
//
// Filtro i files non necessari o non permessi
//
        $arrayFiles = $this->GetFileList($dati['CartellaAllegati']);
        foreach ($arrayFiles as $keyAlle => $alle) {
            $ext = strtolower(pathinfo($alle['FILEPATH'], PATHINFO_EXTENSION));
            if (!is_dir($alle['FILEPATH'])) {
                switch ($ext) {
                    case 'zip':
                        unset($arrayFiles[$keyAlle]);
                        break;
                    default:
                        if (strpos("|pdf|txt|jpg|p7m|m7m|tsd|", "|$ext|") === false) {
                            unset($arrayFiles[$keyAlle]);
                        }
                        break;
                }
            } else {
                unset($arrayFiles[$keyAlle]);
            }
        }

//
// Estrazione File_firmato
//
        $allegatiAdempimento['source_file_firmato'] = "";
        $allegatiAdempimento['sequenza_passo_file_firmato'] = "";
        $allegatiAdempimento['path_file_firmato'] = "";
        $allegatiAdempimento['progressivo_passo_file_firmato'] = "";
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $keyNav => $Ricite_rec) {
            if ($Ricite_rec['ITEIFC'] == 2 && ($Ricite_rec['ITEUPL'] == 1 || $Ricite_rec['ITEMLT'] == 1)) {
                $trovatoFile = false;
                foreach ($arrayFiles as $key => $file) {
                    $ricdoc_rec = array();
                    $nomeFileOrig = "";
                    $nomeAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($Ricite_rec['ITESEQ'], 3, "0", STR_PAD_LEFT);
                    if (strpos($file['FILENAME'], $nomeAllegato) !== false) {
                        $ricdoc_rec = $this->GetRicdoc($file['FILENAME'], "codice", $dati['PRAM_DB']);
                        if ($ricdoc_rec) {
                            $nomeFileOrig = $ricdoc_rec['DOCNAME'];
                        }
                        $trovatoFile = true;
                        $allegatiAdempimento['nome_originale_file_firmato'] = $nomeFileOrig;
                        $allegatiAdempimento['source_file_firmato'] = $file['FILENAME'];
                        $allegatiAdempimento['path_file_firmato'] = $file['FILEPATH'];
                        $allegatiAdempimento['sequenza_passo_file_firmato'] = $Ricite_rec['ITESEQ'];
                        $allegatiAdempimento['progressivo_passo_file_firmato'] = $keyNav + 1;
                    }
                }
                if (!$trovatoFile) {
                    $allegatiAdempimento['nome_originale_file_firmato'] = "";
                    $allegatiAdempimento['source_file_firmato'] = "";
                    $allegatiAdempimento['path_file_firmato'] = "";
                    $allegatiAdempimento['sequenza_passo_file_firmato'] = $Ricite_rec['ITESEQ'];
                    $allegatiAdempimento['progressivo_passo_file_firmato'] = $keyNav + 1;
                }
            }
        }

//
// Estrazione File non firmato
//
        $allegatiAdempimento['source_file_non_firmato'] = "";
        $allegatiAdempimento['path_file_non_firmato'] = "";
        $allegatiAdempimento['sequenza_passo_file_non_firmato'] = "";
        $allegatiAdempimento['progressivo_passo_file_non_firmato'] = "";
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $Ricite_rec) {
            if ($Ricite_rec['ITEDRR']) {
                $trovatoFile = false;
                foreach ($arrayFiles as $key => $file) {
                    if (strtolower(pathinfo($file['FILEPATH'], PATHINFO_EXTENSION)) == 'pdf' && strpos($file['FILENAME'], "rapporto") !== false) {
                        $trovatoFile = true;
                        $allegatiAdempimento['source_file_non_firmato'] = $file['FILENAME'];
                        $allegatiAdempimento['path_file_non_firmato'] = $file['FILEPATH'];
                        $allegatiAdempimento['sequenza_passo_file_non_firmato'] = $Ricite_rec['ITESEQ'];
                        $allegatiAdempimento['progressivo_passo_file_non_firmato'] = $keyNav + 1;
                    }
                    if (!$trovatoFile) {
                        $allegatiAdempimento['source_file_non_firmato'] = "";
                        $allegatiAdempimento['path_file_non_firmato'] = "";
                        $allegatiAdempimento['sequenza_passo_file_non_firmato'] = $Ricite_rec['ITESEQ'];
                        $allegatiAdempimento['progressivo_passo_file_non_firmato'] = $keyNav + 1;
                    }
                }
            }
        }
//
// Estrazione file allegati generici
//
        $idx_alle = 0;
        $arrayAllegati = array();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $keyNav => $Ricite_rec) {
            if ($Ricite_rec['ITEIFC'] == 1 && ($Ricite_rec['ITEUPL'] == 1 || $Ricite_rec['ITEMLT'] == 1)) {
                //if ($Ricite_rec['ITEIFC'] == 1 && $Ricite_rec['ITEOBL'] == 1 && ($Ricite_rec['ITEUPL'] == 1 || $Ricite_rec['ITEMLT'] == 1)) {
                $trovatoFile = false;
                foreach ($arrayFiles as $key => $file) {
                    $ricdoc_rec = array();
                    $nomeFileOrig = "";
                    $nomeAllegato = $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($Ricite_rec['ITESEQ'], 3, "0", STR_PAD_LEFT);
                    if (strpos($file['FILENAME'], $nomeAllegato) !== false) {
                        $ricdoc_rec = $this->GetRicdoc($file['FILENAME'], "codice", $dati['PRAM_DB']);
                        if ($ricdoc_rec) {
                            $nomeFileOrig = $ricdoc_rec['DOCNAME'];
                        }
                        $trovatoFile = true;
                        $idx_alle = $idx_alle + 1;
                        $arrayAllegati[$idx_alle] = $arrayFiles[$key];
                        $arrayAllegati[$idx_alle]['nome_file'] = $file['FILENAME'];
                        $arrayAllegati[$idx_alle]['nome_file_originale'] = $nomeFileOrig;
                        $arrayAllegati[$idx_alle]['codice_e_descrizione'] = $Ricite_rec['ITETAL'];
                        $arrayAllegati[$idx_alle]['sequenza_passo'] = $Ricite_rec['ITESEQ'];
                        $arrayAllegati[$idx_alle]['progressivo_passo'] = $keyNav + 1;
                    }
                }
                if (!$trovatoFile) {
                    //if ($Ricite_rec['ITEOBL'] == 1) {
                    if (($Ricite_rec['ITEOBL'] == 1 && $Ricite_rec['RICOBL'] == 1) || $Ricite_rec['CLTOBL'] == 1) {
                        $idx_alle = $idx_alle + 1;
                        $arrayAllegati[$idx_alle] = $arrayFiles[$key];
                        $arrayAllegati[$idx_alle]['FILENAME'] = "";
                        $arrayAllegati[$idx_alle]['FILEPATH'] = "";
                        $arrayAllegati[$idx_alle]['nome_file'] = "";
                        $arrayAllegati[$idx_alle]['nome_file_originale'] = "";
                        $arrayAllegati[$idx_alle]['codice_e_descrizione'] = $Ricite_rec['ITETAL'];
                        $arrayAllegati[$idx_alle]['sequenza_passo'] = $Ricite_rec['ITESEQ'];
                        $arrayAllegati[$idx_alle]['progressivo_passo'] = $keyNav + 1;
                        $arrayAllegati[$idx_alle]['allegato_mancante'] = 1;
                    }
                }
            }
        }
        $allegatiAdempimento['allegati'] = $arrayAllegati;
        return $allegatiAdempimento;
    }

    public function GetDatiLegRapp($dati, $PRAM_DB) {
        $datiLegale = array();
        $datiLegale['legale_nome'] = strtoupper($this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "NOME_LEGALE"));
        $datiLegale['legale_cognome'] = strtoupper($this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "COGNOME_LEGALE"));
        $datiLegale['legale_fiscale'] = strtoupper($this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "FISCALE_LEGALE"));
        /*
         * Decodifica Carica
         */

        $datiLegale['legale_carica'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "CARICA_LEGALE");
        $arrCariche = $this->GetArrayCariche();
        $keyArrCariche = array_search($datiLegale['legale_carica'], $this->array_column($arrCariche, 'CODICE'));
        $datiLegale['legale_carica_descrizione'] = $arrCariche[$keyArrCariche]['DESCRIZIONE'];
        return $datiLegale;
    }

    /*
     * Per italsoft è p'esibente
     */

    public function GetDatiEsibente($dati, $PRAM_DB) {
        $datiEsibente['esibente_qualifica'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "QUALIFICA");
        $datiEsibente['esibente_cognome'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "ESIBENTE_COGNOME");
        $datiEsibente['esibente_nome'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "ESIBENTE_NOME");
        $datiEsibente['esibente_codfis'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "ESIBENTE_CODICEFISCALE_CFI");
        $datiEsibente['esibente_codfis_starweb'] = frontOfficeApp::$cmsHost->getUserInfo('cftelemaco');
        $datiEsibente['esibente_pec'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "ESIBENTE_PEC");
        $datiEsibente['esibente_telefono'] = $this->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "ESIBENTE_TELEFONO");
        return $datiEsibente;
    }

    public function GetDatiSedeLegale($dati, $PRAM_DB) {

        $datiSedeLegale['sedeLegale_mail'] = $this->GetSostituzioneMail($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_pec'] = $this->GetSostituzionePec($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_comune'] = $this->GetSostituzioneComune($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_via'] = $this->GetSostituzioneIndirizzo($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_civico'] = $this->GetSostituzioneCivico($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_provincia'] = $this->GetSostituzioneProvincia($dati['Ricdag_tab_totali']);
        $datiSedeLegale['sedeLegale_cap'] = $this->GetSostituzioneCap($dati['Ricdag_tab_totali']);


        /*
         * Apro il DB COMUNI e decodificao i dati della sede legale
         */
        $COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
        /*
         * Leggo dati provincia della sede legale dell'impresa da db COMUNI
         */
        $provicicaSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM PROVINCE WHERE SIGLA = '" . trim(addslashes($datiSedeLegale['sedeLegale_provincia'])) . "'", false);
        $comuneProvicicaSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '" . trim(addslashes($provicicaSedeLegale_rec['PROVINCIA'])) . "'", false);
        $comuneSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '" . trim(addslashes($datiSedeLegale['sedeLegale_comune'])) . "'", false);

        /*
         * Scompongo l'indirizzo della sede legale dell'impresa
         */
        $posSpazio = strpos($datiSedeLegale['sedeLegale_via'], " ");
        $toponimoSedeLegale = substr($datiSedeLegale['sedeLegale_via'], 0, $posSpazio);
        $viaSedeLegale = trim(substr($datiSedeLegale['sedeLegale_via'], $posSpazio));

        $datiSedeLegale['sedeLegale_istat_provincia'] = substr($comuneProvicicaSedeLegale_rec['CISTAT'], 0, 3);
        $datiSedeLegale['sedeLegale_descrizione_provincia'] = $provicicaSedeLegale_rec['PROVINCIA'];
        $datiSedeLegale['sedeLegale_codice_catastale'] = $comuneSedeLegale_rec['NASCIT'];
        $datiSedeLegale['sedeLegale_toponimo_indirizzo'] = $toponimoSedeLegale;
        $datiSedeLegale['sedeLegale_denominazione_stradale_indirizzo'] = $viaSedeLegale;
        if ($datiSedeLegale['sedeLegale_cap'] == "") {
            $comuneSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '" . trim(addslashes($datiSedeLegale['sedeLegale_comune'])) . "'", false);
            $datiSedeLegale['sedeLegale_cap'] = $comuneSedeLegale_rec['COAVPO'];
        }

        return $datiSedeLegale;
    }

    public function getDatiInfocamere($dati, $PRAM_DB = "") {
//
// Dati Impresa
//
        $datiInfocamere = array();
        $datiInfocamere['datiImpresa'] = $this->GetDatiImpresa($dati, $PRAM_DB);
        $datiInfocamere['datiSedeLegale'] = $this->GetDatiSedeLegale($dati, $PRAM_DB);
//
// Dati Sportello
//
        $datiInfocamere['datiSportello'] = $this->GetDatiSportello($dati, $PRAM_DB);

//
// Dati Esibente
//
        $datiInfocamere['datiEsibente'] = $this->GetDatiEsibente($dati, $PRAM_DB);

//
// Dati Legale Rappresentante
//
        $datiInfocamere['datiLegRapp'] = $this->GetDatiLegRapp($dati, $PRAM_DB);

//
// Dati Richiesta
//
        $datiInfocamere['datiAdempimento'] = $this->GetDatiAdempimento($dati, $PRAM_DB);
        $datiInfocamere['datiAdempimento']['codice_pratica'] = $dati['Proric_rec']['CODICEPRATICASW'];

//
// Files
//
        $datiInfocamere['files'] = $this->GetAllegatiAdempimento($dati, $PRAM_DB);

//
// Elenco Controlli
//
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibInfocamere.class.php';
        $praLibInfocamere = new praLibInfocamere($this);
        $datiInfocamere['checks'] = $praLibInfocamere->checkDatiInfocamere($datiInfocamere);

        return $datiInfocamere;
    }

    public function checkDatiImpresa($datiImpresa) {
        $ritorno = array();
        if (!$datiImpresa['DenominazioneImpresa']) {
            $ritorno[] = "Manca Denominazione Impresa";
        }
        if (!$datiImpresa['Prov_InsProduttivo']) {
            $ritorno[] = "Manca Provincia Insediamento Produttivo";
        }
        if (!$datiImpresa['Comune_InsProduttivo']) {
            $ritorno[] = "Manca Comune Insediamento Produttivo";
        }
        if (!$datiImpresa['Indir_InsProduttivo']) {
            $ritorno[] = "Manca Indirizzo Insediamento Produttivo";
        }
        if (!$datiImpresa['Civico_InsProduttivo']) {
            $ritorno[] = "Manca N. Civico Insediamento Produttivo";
        }
        if (!$datiImpresa['Cap_InsProduttivo']) {
            $ritorno[] = "Manca Cap Insediamento Produttivo";
        }
        return $ritorno;
    }

    public function InoltroAdAgenzia($agenzia, $PRAM_DB, $proric_rec, $hash) {
        switch ($agenzia) {
            case "CNA":
                $arrayAgenziaCNA = $this->GetParametriCNA($PRAM_DB);
                $Anatsp_rec = $this->GetAnatsp($proric_rec['RICTSP'], "codice", $PRAM_DB);
                $endPoint = $arrayAgenziaCNA['AGENZIA_CNA_WSURL'];
                $debugLevel = 0;
                $timeout = 6000;
                $soapEncoding = "UTF-8";
                $nameSpaces = "http://www.sixtema-ict.it/Agenzia_Imprese";
                $operationName = "segnala_pratica_completa_in_attesa";
                $soapAction = $nameSpaces . "/" . $operationName;
                $username = $arrayAgenziaCNA['AGENZIA_CNA_WSUSER'];
                $password = $arrayAgenziaCNA['AGENZIA_CNA_WSPASSWD'];
                $secretWord = $arrayAgenziaCNA['AGENZIA_CNA_SECRETWORD'];
                //
                // Controllo presenza tutti i parametri necessari
                //
                require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
                $client = new nusoap_client($endPoint, false);
                $client->debugLevel = $debugLevel;
                $client->timeout = $timeout;
                $client->response_timeout = $timeout;
                //$client->setCredentials($username, $password, 'basic');
                $client->soap_defencoding = $soapEncoding;
                $headers = false;
                $rpcParams = null;
                $style = 'rpc';
                $use = 'literal';
                $param = array(
                    "ID_SUAP" => $Anatsp_rec['TSPIDE'],
                    "Numero_Pratica" => substr($proric_rec['RICNUM'], 4),
                    "Anno_Pratica" => substr($proric_rec['RICNUM'], 0, 4),
                    "HASH" => $hash,
                    "EndPoint_Prelievo" => $arrayAgenziaCNA['AGENZIA_CNA_ENDPOINTWS'],
                    "domainCode" => ITA_DB_SUFFIX,
                    "Credenziali" => base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $secretWord, $username . ":" . $password, MCRYPT_MODE_OFB, "12345678"))
                );
                $result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
                if ($client->fault) {
                    $request = $client->request;
                    $response = $client->response;
                    $fault = $client->faultstring;
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0110', "Impossibile inviare richiesta n. " . $proric_rec['RICNUM'] . " all'agenzia $agenzia.<br><b>Fault:</b><br>$fault<br><b>Request:</b><br>$request<br><b>Response</b>:<br>$response", __CLASS__, "", false);
                    return "KO";
                } else {
                    $error = $client->getError();
                    if ($error) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0111', "Impossibile inviare richiesta n. " . $proric_rec['RICNUM'] . " all'agenzia $agenzia -----> Errore: $error", __CLASS__, "", false);
                        return "KO";
                    }
                }

                break;
            case "CONFA":
                break;
            default:
                break;
        }
        return "OK";
    }

    public function GetParametriBloccoMail($PRAM_DB) {
        $Anapar_tab_block = $this->GetAnapar('BLOCK', 'codice', $PRAM_DB);
        if ($Anapar_tab_block) {
            foreach ($Anapar_tab_block as $Anapar_rec) {
                if ($Anapar_rec['PARKEY'] == 'BLOCK_MAIL_RESP')
                    $arrayParamBloccoMail['bloccaMailResp'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'BLOCK_MAIL_RICH')
                    $arrayParamBloccoMail['bloccaMailRich'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'BLOCK_INVIO_INFO')
                    $arrayParamBloccoMail['bloccaInvioInfo'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'BLOCK_MAIL_STARWEB')
                    $arrayParamBloccoMail['bloccaStarweb'] = $Anapar_rec['PARVAL'];
            }
        }
        return $arrayParamBloccoMail;
    }

    public function GetParametriAgenzie($PRAM_DB, $soloAttive = false) {
        $arrayParAgenzie = array();
        $arrayParAgenzie['CNA'] = $this->GetParametriCNA($PRAM_DB);
        if ($soloAttive == true) {
            if (!$arrayParAgenzie['CNA']['AGENZIA_CNA_ATTIVA'] == 'No') {
                unset($arrayParAgenzie['CNA']);
            }
        }
        return $arrayParAgenzie;
    }

    public function GetParametriCNA($PRAM_DB) {
        $arrayParCNA = array();
        $Anapar_tab_cna = $this->GetAnapar('CNA', 'codice', $PRAM_DB);
        if ($Anapar_tab_cna) {
            foreach ($Anapar_tab_cna as $Anapar_rec) {
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_ATTIVA')
                    $arrayParCNA['AGENZIA_CNA_ATTIVA'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_WSURL')
                    $arrayParCNA['AGENZIA_CNA_WSURL'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_WSWSDL')
                    $arrayParCNA['AGENZIA_CNA_WSWSDL'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_WSUSER')
                    $arrayParCNA['AGENZIA_CNA_WSUSER'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_WSPASSWD')
                    $arrayParCNA['AGENZIA_CNA_WSPASSWD'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_ENDPOINTWS')
                    $arrayParCNA['AGENZIA_CNA_ENDPOINTWS'] = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'AGENZIA_CNA_SECRETWORD')
                    $arrayParCNA['AGENZIA_CNA_SECRETWORD'] = $Anapar_rec['PARVAL'];
            }
        }
        return $arrayParCNA;
    }

    public function GetParametriMail($PRAM_DB, $dati) {
        $parametriMail = array();
        $Anapar_tab_mail = $this->GetAnapar('MAIL', 'codice', $PRAM_DB);
        if ($Anapar_tab_mail) {
            foreach ($Anapar_tab_mail as $Anapar_rec_mail) {
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_MAIL_ACCOUNT')
                    $parametriMail['MAIL']['from'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_NAME_ACCOUNT')
                    $parametriMail['MAIL']['name'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_SMTP_HOST')
                    $parametriMail['MAIL']['host'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_PORT')
                    $parametriMail['MAIL']['port'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_SMTP_SECURE')
                    $parametriMail['MAIL']['smtpSecure'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_SMTP_USER')
                    $parametriMail['MAIL']['username'] = $Anapar_rec_mail['PARVAL'];
                if ($Anapar_rec_mail['PARKEY'] == 'ITA_SMTP_PASSWORD')
                    $parametriMail['MAIL']['password'] = $Anapar_rec_mail['PARVAL'];
            }
        }

        /*
         * Se ci sono i parametri mail nello Sportello on-line, uso quelli
         */
        if ($dati['Anatsp_rec']['TSPMAIL'] && $dati['Anatsp_rec']['TSPPASSWORD']) {
            $parametriMail['MAIL']['from'] = $dati['Anatsp_rec']['TSPMAIL'];
            $parametriMail['MAIL']['name'] = $dati['Anatsp_rec']['TSPFROM'];
            $parametriMail['MAIL']['host'] = $dati['Anatsp_rec']['TSPHOST'];
            $parametriMail['MAIL']['port'] = $dati['Anatsp_rec']['TSPPORT'];
            $parametriMail['MAIL']['smtpSecure'] = $dati['Anatsp_rec']['TSPSECURESMTP'];
            $parametriMail['MAIL']['username'] = $dati['Anatsp_rec']['TSPUSER'];
            $parametriMail['MAIL']['password'] = $dati['Anatsp_rec']['TSPPASSWORD'];
        }

        return $parametriMail;
    }

    public function GetParametriAcquisizioneAutomatica() {
        $parametriAcquisizione = array('FLAG' => 'No', 'URI' => '', 'WSDL' => '', 'NAMESPACE' => '', 'UTENTE' => '', 'PASSWORD' => '');

        $ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
        $frontOfficeLib = new frontOfficeLib;
        $envconfig_tab = $frontOfficeLib->getEnv_config("ACQUISIZIONEAUTOMATICA", $ITAFRONTOFFICE_DB);

        foreach ($envconfig_tab as $envconfig_rec) {
            switch ($envconfig_rec['CHIAVE']) {
                case 'ACQAUT':
                    $parametriAcquisizione['FLAG'] = $envconfig_rec['CONFIG'];
                    break;

                case 'ACQAUTENDPOINT':
                    $parametriAcquisizione['URI'] = $envconfig_rec['CONFIG'];
                    break;

                case 'ACQAUTWSDL':
                    $parametriAcquisizione['WSDL'] = $envconfig_rec['CONFIG'];
                    break;

                case 'ACQAUTUSER':
                    $parametriAcquisizione['UTENTE'] = $envconfig_rec['CONFIG'];
                    break;

                case 'ACQAUTPWD':
                    $parametriAcquisizione['PASSWORD'] = $envconfig_rec['CONFIG'];
                    break;
            }
        }

        return $parametriAcquisizione;
    }

    public function GetParametriPravis($PRAM_DB) {
        $parametriPravis = array();
        $Anapar_tab_pravis = $this->GetAnapar('PRAVIS', 'codice', $PRAM_DB);
        if ($Anapar_tab_pravis) {
            foreach ($Anapar_tab_pravis as $Anapar_rec_pravis) {
                if ($Anapar_rec_pravis['PARKEY'] == 'PRAVIS_SELECTSTATI') {
                    $parametriPravis[$Anapar_rec_pravis['PARKEY']] = $Anapar_rec_pravis['PARVAL'];
                }
            }
        }
        return $parametriPravis;
    }

    public function GetMailRichiedente($modo = "", $Ricdag_tab_totali = array()) {
        $mailRich = array();
        if (frontOfficeApp::$cmsHost->getAltriDati('pec') == "") {
            $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
            $mail1 = $datiUtente['ESIBENTE_EMAIL'];
        } else {
            $mail1 = frontOfficeApp::$cmsHost->getAltriDati('pec');
        }
        $mailRich[] = $mail1;

        $regExp = "/^[a-z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i";

        /*
         * Ricerca pec dichiarante
         */
        $dichiarantePec = $this->GetValueDatoAggiuntivo($Ricdag_tab_totali, "DICHIARANTE_PEC");
        if ($dichiarantePec && $dichiarantePec != $mail1) {
            if (preg_match($regExp, $dichiarantePec)) {
                $mailRich[] = $dichiarantePec;
            }
        }

        /*
         * Ricerca mail dichiarante
         */
        $dichiaranteMail = $this->GetValueDatoAggiuntivo($Ricdag_tab_totali, "DICHIARANTE_EMAIL");
        if ($dichiaranteMail && $dichiaranteMail != $mail1) {
            if (preg_match($regExp, $dichiaranteMail)) {
                $mailRich[] = $dichiaranteMail;
            }
        }

        if ($modo == "RICHIESTA-PARERE") {
            $mailRich[] = $this->GetValueDatoAggiuntivo($Ricdag_tab_totali, "Pec_EnteTerzo");
        }
//        if (frontOfficeApp::$cmsHost->getAltriDati('pec') == "") {
//            $mailRich = $param['email'];
//        } else {
//            $mailRich = frontOfficeApp::$cmsHost->getAltriDati('pec');
//        }
        return $mailRich;
    }

    public function InvioMailAnnullamentoRichiedente($dati, $PRAM_DB, $arrayDatiMail, $mailRich, $modo = '') {
        $parametriMail = $this->GetParametriMail($PRAM_DB, $dati);
        $dati['PRAM_DB'] = $PRAM_DB;

        foreach ($mailRich as $indirizzo) {
            $itaMailer = null;
            $itaMailer = new itaMailer();
            $itaMailer->IsHTML();
            switch ($modo) {
                case "ANNULLAMENTO-INTEGRAZIONE":
                case "ANNULLAMENTO-RICHIESTA":
                    $itaMailer->Subject = $arrayDatiMail['oggettoAnnullamento'];
                    $itaMailer->Body = $arrayDatiMail['bodyAnnullamento'];
                    break;
            }
            //$itaMailer->AddAddress($mailRich);
            $itaMailer->AddAddress($indirizzo);
            $itaMailer->AddCustomHeader("X-TipoRicevuta:breve");
            $itaMailer->Send(array(
                'FROM' => $parametriMail['MAIL']['from'],
                'NAME' => $parametriMail['MAIL']['name'],
                'HOST' => $parametriMail['MAIL']['host'],
                'PORT' => $parametriMail['MAIL']['port'],
                'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
                'USERNAME' => $parametriMail['MAIL']['username'],
                'PASSWORD' => $parametriMail['MAIL']['password']
            ));
        }
        return true;
    }

    public function InvioMailAnnullamentoResponsabile($dati, $PRAM_DB, $arrayDatiMail, $modo = '') {
        $parametriMail = $this->GetParametriMail($PRAM_DB, $dati);
        $dati['PRAM_DB'] = $PRAM_DB;

        $Ananom_rec_spa = array();
        $Ananom_rec_tsp = array();
        // Indirizzo mail di destinazione del responsabile del procediemento
        $Ananom_rec = $this->GetAnanom($dati['Anapra_rec']['PRARES'], 'codice', $PRAM_DB);


        if ($dati['Proric_rec']['RICRPA']) {
            $proric_rec_madre = $this->getProric($dati['Proric_rec']['RICRPA'], "codice", $PRAM_DB);
            $dati['Anaspa_rec'] = $this->getAnaspa($proric_rec_madre['RICSPA'], "codice", $PRAM_DB);
            $dati['Anatsp_rec'] = $this->getAnatsp($proric_rec_madre['RICTSP'], "codice", $PRAM_DB);
            $Anapra_rec = $this->getAnapra($proric_rec_madre['RICPRO'], "codice", $PRAM_DB);
            $Ananom_rec = $this->GetAnanom($Anapra_rec['PRARES'], 'codice', $PRAM_DB);
        }

        //
        //Scrivo il file XMLINFO in base al tipo di richiesta
        //
        switch ($modo) {
            case "ANNULLAMENTO-INTEGRAZIONE":
            case "ANNULLAMENTO-RICHIESTA":
                $Subject = $arrayDatiMail['oggettoAnnullamento'];
                $Body = $arrayDatiMail['bodyAnnullamento'];
                //
                //$Nome_file = $dati['CartellaAllegati'] . "/XMLANN.xml";
                //$xml = $this->CreaXMLann($dati);
                break;
        }
        //
        $itaMailer = null;
        $itaMailer = new itaMailer();
        $itaMailer->IsHTML();
        //
        //
        //Leggo il file XMLINFO
        //
        $Nome_file = $this->CreaXMLINFO($modo, $dati, false);
//        $File = fopen($Nome_file, "w+");
//        if (!file_exists($Nome_file)) {
//            return false;
//        } else {
//            fwrite($File, $xml);
//            fclose($File);
//        }
        //
        if ($dati['Anaspa_rec']) {
            $Ananom_rec_spa = $this->GetAnanom($dati['Anaspa_rec']['SPARES'], 'codice', $PRAM_DB);
        }
        if ($dati['Anatsp_rec']) {
            $Ananom_rec_tsp = $this->GetAnanom($dati['Anatsp_rec']['TSPRES'], 'codice', $PRAM_DB);
        }

        $respAddr = array();
        if (trim($Ananom_rec_tsp['NOMEML']) != '') {
            $respAddr[$Ananom_rec_tsp['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec_tsp['NOMEML']]['NOMRES'] = $Ananom_rec_tsp['NOMRES'];
        }
        if (trim($Ananom_rec_spa['NOMEML']) != '') {
            $respAddr[$Ananom_rec_spa['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec_spa['NOMEML']]['NOMRES'] = $Ananom_rec_spa['NOMRES'];
        }
        if (trim($Ananom_rec['NOMEML']) != '') {
            $respAddr[$Ananom_rec['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec['NOMEML']]['NOMRES'] = $Ananom_rec['NOMRES'];
        }


        foreach ($respAddr as $key => $value) {
            if ($value == true) {
                $itaMailer->itaAddAddress($key);
            }
        }

        $itaMailer->IsHTML();
        $itaMailer->Subject = $Subject;
        $itaMailer->Body = $Body;
        $itaMailer->AddAttachment($Nome_file);
        $itaMailer->AddCustomHeader("X-TipoRicevuta:breve");
        $itaMailer->Send(array(
            'FROM' => $parametriMail['MAIL']['from'],
            'NAME' => $parametriMail['MAIL']['name'],
            'HOST' => $parametriMail['MAIL']['host'],
            'PORT' => $parametriMail['MAIL']['port'],
            'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
            'USERNAME' => $parametriMail['MAIL']['username'],
            'PASSWORD' => $parametriMail['MAIL']['password']
        ));
        return true;
    }

    public function InvioMailRichiedente($dati, $PRAM_DB, $arrayDatiMail, $mailRich, $modo = '', $TotaleAllegati = array()) {
        $parametriMail = $this->GetParametriMail($PRAM_DB, $dati);
        $dati['PRAM_DB'] = $PRAM_DB;

        /*
         * Inizializzo Body e Oggetto della mail
         */
        switch ($modo) {
            case "RICHIESTA-INFOCAMERE":
                $Subject = $arrayDatiMail['oggettoRichInfocamere'];
                $Body = $arrayDatiMail['bodyRichInfocamere'];
                break;
            case "RICHIESTA-INTEGRAZIONE":
                $Subject = $arrayDatiMail['oggettoIntRich'];
                $Body = $arrayDatiMail['bodyIntRich'];
                if ($arrayDatiMail['bodyIntRich'] == "" || $arrayDatiMail['bodyIntRich'] == "<span style=\"font-size: small;\"></span>")
                    $Body = $arrayDatiMail['bodyRichiedente'];
                break;
            case "RICHIESTA-ONLINE":
                $Subject = $arrayDatiMail['oggettoRichiedente'];
                $Body = $arrayDatiMail['bodyRichiedente'];
                break;
            case "ANNULLAMENTO-INTEGRAZIONE":
            case "ANNULLAMENTO-RICHIESTA":
                $Subject = $arrayDatiMail['oggettoAnnullamento'];
                $Body = $arrayDatiMail['bodyAnnullamento'];
                break;
            case "RICHIESTA-PARERE":
                $Subject = $arrayDatiMail['oggettoRichParere'];
                $Body = $arrayDatiMail['bodyRichParere'];
                break;
            default:
                $Subject = $arrayDatiMail['oggettoRichiedente'];
                $Body = $arrayDatiMail['bodyRichiedente'];
                break;
        }

        //
        //Inizializzo il record della mail da inviare 
        //
        $Ricmail_tab = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", true, $PRAM_DB, " AND TOCLASS='RICHIEDENTE'");
        if (!$Ricmail_tab) {
            foreach ($mailRich as $indirizzo) {
                $rowidMail = $this->saveRicMailRecord($dati, $indirizzo, "RICHIEDENTE", "");
                if ($rowidMail == false) {
                    return "Errore Inizializzazione record mail richiedente con l'indirizzo $indirizzo";
                }
            }
            $Ricmail_tab = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", true, $PRAM_DB, " AND TOCLASS='RICHIEDENTE'");
        }


        foreach ($Ricmail_tab as $Ricmail_rec) {
            if ($Ricmail_rec['MAILSTATO'] != "@INVIATA@") {
                $itaMailer = null;
                $itaMailer = new itaMailer();
                $itaMailer->IsHTML();
                $itaMailer->Subject = $Subject;
                $itaMailer->Body = $Body;
                $itaMailer->AddAddress($Ricmail_rec['TOADDR']);
                //$itaMailer->AddAddress($mailRich);

                /*
                 * Aggiungo gli allegati alla mail se il parametro lo consente
                 */
                $anapar_recInvoAlleRichiedente = $this->GetAnapar("BLOCK_ALLEGATI_RICHIEDENTE", "parkey", $PRAM_DB, false);
                if ($anapar_recInvoAlleRichiedente['PARVAL'] == "No") {
                    foreach ($TotaleAllegati as $Allegato) {
                        $size = $size + filesize($dati['CartellaAllegati'] . "/" . $Allegato) / 1048576;
                    }
                    $size = round($size, 2);
                    $anapar_recMaxFilesize = $this->GetAnapar("BLOCK_MAX_FILESIZE", "parkey", $PRAM_DB, false);
                    if ($size < $anapar_recMaxFilesize['PARVAL']) {
                        foreach ($TotaleAllegati as $Allegato) {
                            $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
                            $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
                        }
                    } else {
                        foreach ($TotaleAllegati as $Allegato) {
                            if (strpos($Allegato, "rapporto_pdfa") !== false) {
                                $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
                                $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
                            }
                        }
                    }
                }
                $itaMailer->AddCustomHeader("X-TipoRicevuta:breve");
                $itaMailer->Send(array(
                    'FROM' => $parametriMail['MAIL']['from'],
                    'NAME' => $parametriMail['MAIL']['name'],
                    'HOST' => $parametriMail['MAIL']['host'],
                    'PORT' => $parametriMail['MAIL']['port'],
                    'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
                    'USERNAME' => $parametriMail['MAIL']['username'],
                    'PASSWORD' => $parametriMail['MAIL']['password']
                ));
                $this->saveRicMailRecord($dati, $Ricmail_rec['TOADDR'], "RICHIEDENTE", "", $Ricmail_rec['ROWID'], $itaMailer->ErrorInfo);
            }
        }
        return true;
    }

//    public function InvioMailRichiedente($dati, $PRAM_DB, $arrayDatiMail, $mailRich, $modo = '', $TotaleAllegati = array()) {
//        $parametriMail = $this->GetParametriMail($PRAM_DB);
//        $dati['PRAM_DB'] = $PRAM_DB;
//        //
//        //Inizializzo il record della mail da inviare 
//        //
//        $Ricmail_rec = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", false, $PRAM_DB, " AND TOCLASS='RICHIEDENTE'");
//        if (!$Ricmail_rec) {
//            $rowidMail = $this->saveRicMailRecord($dati, $mailRich, "RICHIEDENTE", "");
//            if ($rowidMail == false) {
//                return "Errore Inizializzazione record mail richiedente con l'indirizzo $mailRich";
//            }
//            $Ricmail_rec = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", false, $PRAM_DB, " AND TOCLASS='RICHIEDENTE'");
//        }
//
//        if ($Ricmail_rec['MAILSTATO'] != "@INVIATA@") {
//            $itaMailer = null;
//            $itaMailer = new itaMailer();
//            $itaMailer->IsHTML();
//            switch ($modo) {
//                case "RICHIESTA-INFOCAMERE":
//                    $itaMailer->Subject = $arrayDatiMail['oggettoRichInfocamere'];
//                    $itaMailer->Body = $arrayDatiMail['bodyRichInfocamere'];
//                    break;
//                case "RICHIESTA-INTEGRAZIONE":
//                    $itaMailer->Subject = $arrayDatiMail['oggettoIntRich'];
//                    $itaMailer->Body = $arrayDatiMail['bodyIntRich'];
//                    if ($arrayDatiMail['bodyIntRich'] == "" || $arrayDatiMail['bodyIntRich'] == "<span style=\"font-size: small;\"></span>")
//                        $itaMailer->Body = $arrayDatiMail['bodyRichiedente'];
//                    break;
//                case "RICHIESTA-ONLINE":
//                    $itaMailer->Subject = $arrayDatiMail['oggettoRichiedente'];
//                    $itaMailer->Body = $arrayDatiMail['bodyRichiedente'];
//                    break;
//                case "ANNULLAMENTO-INTEGRAZIONE":
//                case "ANNULLAMENTO-RICHIESTA":
//                    $itaMailer->Subject = $arrayDatiMail['oggettoAnnullamento'];
//                    $itaMailer->Body = $arrayDatiMail['bodyAnnullamento'];
//                    break;
//                case "RICHIESTA-PARERE":
//                    $itaMailer->Subject = $arrayDatiMail['oggettoRichParere'];
//                    $itaMailer->Body = $arrayDatiMail['bodyRichParere'];
//                    break;
//                default:
//                    $itaMailer->Subject = $arrayDatiMail['oggettoRichiedente'];
//                    $itaMailer->Body = $arrayDatiMail['bodyRichiedente'];
//                    break;
//            }
//            $itaMailer->AddAddress($mailRich);
//
//            /*
//             * Aggiungo gli allegati alla mail se il parametro lo consente
//             */
//            $anapar_recInvoAlleRichiedente = $this->GetAnapar("BLOCK_ALLEGATI_RICHIEDENTE", "parkey", $PRAM_DB, false);
//            if ($anapar_recInvoAlleRichiedente['PARVAL'] == "No") {
//                foreach ($TotaleAllegati as $Allegato) {
//                    $size = $size + filesize($dati['CartellaAllegati'] . "/" . $Allegato) / 1048576;
//                }
//                $size = round($size, 2);
//                $anapar_recMaxFilesize = $this->GetAnapar("BLOCK_MAX_FILESIZE", "parkey", $PRAM_DB, false);
//                if ($size < $anapar_recMaxFilesize['PARVAL']) {
//                    foreach ($TotaleAllegati as $Allegato) {
//                        $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
//                        $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
//                    }
//                } else {
//                    foreach ($TotaleAllegati as $Allegato) {
//                        if (strpos($Allegato, "rapporto_pdfa") !== false) {
//                            $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
//                            $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
//                        }
//                    }
//                }
//            }
//
//
//
//            $itaMailer->AddCustomHeader("X-TipoRicevuta:breve");
//            $itaMailer->Send(array(
//                'FROM' => $parametriMail['MAIL']['from'],
//                'NAME' => $parametriMail['MAIL']['name'],
//                'HOST' => $parametriMail['MAIL']['host'],
//                'PORT' => $parametriMail['MAIL']['port'],
//                'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
//                'USERNAME' => $parametriMail['MAIL']['username'],
//                'PASSWORD' => $parametriMail['MAIL']['password']
//            ));
//            $this->saveRicMailRecord($dati, $mailRich, "RICHIEDENTE", "", $rowidMail, $itaMailer->ErrorInfo);
//        }
//        return true;
//    }

    public function InvioMailResponsabile($dati, $TotaleAllegati, $PRAM_DB, $arrayDatiMail, $modo = '') {
        $dati['PRAM_DB'] = $PRAM_DB;
        $parametriMail = $this->GetParametriMail($PRAM_DB, $dati);
        $Ananom_rec_spa = array();
        $Ananom_rec_tsp = array();
        // Indirizzo mail di destinazione del responsabile del procediemento
        $Ananom_rec = $this->GetAnanom($dati['Anapra_rec']['PRARES'], 'codice', $PRAM_DB);
        // Indirizzo mail di destinazione del responsabile del passo invio mail
        //
        //Calcolo dimensione degli allegati
        //
        foreach ($TotaleAllegati as $Allegato) {
            $size = $size + filesize($dati['CartellaAllegati'] . "/" . $Allegato) / 1048576;
        }
        $size = round($size, 2);

        switch ($modo) {
            case "RICHIESTA-INFOCAMERE":
                $Subject = "Richiesta Procedimento Amministrativo " . substr($dati['Proric_rec']['RICNUM'], 5) . "/" . substr($dati['Proric_rec']['RICNUM'], 0, 4);
                $Body = "<span style=\"color:blue;font-size:1.5em;\"><b>Inviata pratica ad infocamere per responsabile</b></span>";
                break;
            case "RICHIESTA-INTEGRAZIONE":
                $Subject = $arrayDatiMail['oggettoIntResp'];
                $Body = $arrayDatiMail['bodyIntResp'];
                if ($arrayDatiMail['bodyIntResp'] == "" || $arrayDatiMail['bodyIntResp'] == "<span style=\"font-size: small;\"></span>")
                    $Body = $arrayDatiMail['bodyResponsabile'];
                break;
            case "RICHIESTA-ONLINE":
                $Subject = $arrayDatiMail['oggettoResponsabile'];
                $Body = $arrayDatiMail['bodyResponsabile'];
                break;
            case "ANNULLAMENTO-INTEGRAZIONE":
            case "ANNULLAMENTO-RICHIESTA":
                //Se annullamento indirzzo mail del responsabile della pratica da annullare
                $Subject = $arrayDatiMail['oggettoAnnullamento'];
                $Body = $arrayDatiMail['bodyAnnullamento'];
                break;
            case "RICHIESTA-PARERE":
                $Subject = $arrayDatiMail['oggettoRespParere'];
                $Body = $arrayDatiMail['bodyRespParere'];
                break;
            default:
                $Subject = $arrayDatiMail['oggettoResponsabile'];
                $Body = $arrayDatiMail['bodyResponsabile'];
                break;
        }



        $anapar_recMaxFilesize = $this->GetAnapar("BLOCK_MAX_FILESIZE", "parkey", $PRAM_DB, false);
        if (isset($arrayDatiMail['strNoProt']) && $arrayDatiMail['strNoProt']) {
            $Body .= "<br><br><span style=\"text-decoration:underline;\"><b>Allegati Non Protocollati:</b></span><br>" . $arrayDatiMail['strNoProt'];
        }
        if (isset($arrayDatiMail['errStringProt']) && $arrayDatiMail['errStringProt']) {
            $Body .= "<br><br><span style=\"text-decoration:underline;\"><b>Errori Protocollazione:</b></span><br>" . $arrayDatiMail['errStringProt'];
        }
        if (isset($arrayDatiMail['strNoMarcati']) && $arrayDatiMail['strNoMarcati']) {
            $Body .= "<br><br><span style=\"text-decoration:underline;\"><b>Allegati non marcati:</b></span><br>" . $arrayDatiMail['strNoMarcati'];
        }
        //if ($size > 7) {
        if ($size > $anapar_recMaxFilesize['PARVAL']) {
            $Subject .= ". No Allegati (Dimensione Massima Superata)($size / {$anapar_recMaxFilesize['PARVAL']})";
            $Body .= "<br><br><h1>Attenzione!! Gli allegati della pratica " . $dati['Proric_rec']['RICNUM'] . " non sono visibili perchï¿½ hanno superato la dimensione massima. Premendo il bottone Carica, saranno comunque importati anche gli allegati </h1>";
        }
//
//Se integrazione, eredito sportello on-line, aggregato e responsabile procedimento dalla pratica madre
//
        if ($dati['Proric_rec']['RICRPA']) {
            $proric_rec_madre = $this->getProric($dati['Proric_rec']['RICRPA'], "codice", $PRAM_DB);
            $dati['Anaspa_rec'] = $this->getAnaspa($proric_rec_madre['RICSPA'], "codice", $PRAM_DB);
            $dati['Anatsp_rec'] = $this->getAnatsp($proric_rec_madre['RICTSP'], "codice", $PRAM_DB);
            $Anapra_rec = $this->getAnapra($proric_rec_madre['RICPRO'], "codice", $PRAM_DB);
            $Ananom_rec = $this->GetAnanom($Anapra_rec['PRARES'], 'codice', $PRAM_DB);
        }

        //
        //Leggo il file XMLINFO
        //
        $Nome_file = $this->CreaXMLINFO($modo, $dati, false);


        if ($dati['Anaspa_rec']) {
            $Ananom_rec_spa = $this->GetAnanom($dati['Anaspa_rec']['SPARES'], 'codice', $PRAM_DB);
        }
        if ($dati['Anatsp_rec']) {
            $Ananom_rec_tsp = $this->GetAnanom($dati['Anatsp_rec']['TSPRES'], 'codice', $PRAM_DB);
        }

        $respAddr = array();
        if (trim($Ananom_rec_tsp['NOMEML']) != '') {
            $respAddr[$Ananom_rec_tsp['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec_tsp['NOMEML']]['NOMRES'] = $Ananom_rec_tsp['NOMRES'];
        }
        if (trim($Ananom_rec_spa['NOMEML']) != '') {
            $respAddr[$Ananom_rec_spa['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec_spa['NOMEML']]['NOMRES'] = $Ananom_rec_spa['NOMRES'];
        }
        if (trim($Ananom_rec['NOMEML']) != '') {
            $respAddr[$Ananom_rec['NOMEML']]['RET'] = true;
            $respAddr[$Ananom_rec['NOMEML']]['NOMRES'] = $Ananom_rec['NOMRES'];
        }
        //
        //Inizializzo i record delle mail da inviare
        //
        $Ricmail_tab = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", true, $PRAM_DB, " AND TOCLASS='RESPONSABILE'");
        if (!$Ricmail_tab) {
            foreach ($respAddr as $key1 => $value) {
                if ($value['RET'] == true) {
                    $arr_address = explode(';', $key1);
                    foreach ($arr_address as $addressValue) {
                        $rowidMail = $this->saveRicMailRecord($dati, $addressValue, "RESPONSABILE", $value['NOMRES']);
                        if ($rowidMail == false) {
                            return "Errore Inizializzazione record mail responsabile con l'indirizzo $addressValue";
                        }
                    }
                }
            }
            $Ricmail_tab = $this->GetRicmail($dati['Proric_rec']['RICNUM'], "codice", true, $PRAM_DB, " AND TOCLASS='RESPONSABILE'");
        }

        //
        //Invio le mail e aggiorno la tracciatura su RICMAIL
        //
        $returnMsg = "";
        foreach ($Ricmail_tab as $Ricmail_rec) {
            if ($Ricmail_rec['MAILSTATO'] != "@INVIATA@") {
                $itaMailer = new itaMailer();
                $itaMailer->IsHTML();
                $itaMailer->Subject = $Subject;
                $itaMailer->Body = $Body;
                $itaMailer->itaAddAddress($Ricmail_rec['TOADDR']);
                if ($size < $anapar_recMaxFilesize['PARVAL']) {
                    foreach ($TotaleAllegati as $Allegato) {
                        $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
                        $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
                    }
                } else {
                    foreach ($dati['Navigatore']['Ricite_tab_new'] as $keyPassi => $ricite_rec) {
                        if ($ricite_rec['ITEDRR'] == 1) {
                            $passoUploadRapporto = $dati['Navigatore']['Ricite_tab_new'][$keyPassi + 1];
                            break;
                        }
                    }
                    if ($passoUploadRapporto) {
                        $ricdoc_rec = $this->GetRicdoc($passoUploadRapporto['ITEKEY'], "itekey", $PRAM_DB, false, $passoUploadRapporto['RICNUM']);
                        if ($ricdoc_rec) {
                            $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $ricdoc_rec['DOCUPL'], $ricdoc_rec['DOCNAME']);
                        }
                    }
//                    foreach ($TotaleAllegati as $Allegato) {
//                        if (strpos($Allegato, "rapporto_pdfa") !== false) {
//                            $docname = $this->getDocName($dati['Ricite_rec']['RICNUM'], $Allegato, $PRAM_DB);
//                            $itaMailer->AddAttachment($dati['CartellaAllegati'] . "/" . $Allegato, $docname);
//                        }
//                    }
                }
                $itaMailer->AddAttachment($Nome_file);

                /*
                 * Allego gli XML delle Richieste Accorpate
                 */
                $Richieste_accorpate = $this->GetRichiesteAccorpate($PRAM_DB, $dati['Proric_rec']['RICNUM']);
                foreach ($Richieste_accorpate as $Proric_accorpata) {
                    $itaMailer->AddAttachment($dati['CartellaAllegati'] . '/XMLINFO_' . $Proric_accorpata['RICNUM'] . '.xml');
                }

                $itaMailer->AddCustomHeader("X-TipoRicevuta:breve");
                if (!$itaMailer->Send(array(
                            'FROM' => $parametriMail['MAIL']['from'],
                            'NAME' => $parametriMail['MAIL']['name'],
                            'HOST' => $parametriMail['MAIL']['host'],
                            'PORT' => $parametriMail['MAIL']['port'],
                            'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
                            'USERNAME' => $parametriMail['MAIL']['username'],
                            'PASSWORD' => $parametriMail['MAIL']['password']
                        ))) {
                    $returnMsg .= $itaMailer->ErrorInfo . "<br>";
                }
//                $itaMailer->Send(array(
//                    'FROM' => $parametriMail['MAIL']['from'],
//                    'NAME' => $parametriMail['MAIL']['name'],
//                    'HOST' => $parametriMail['MAIL']['host'],
//                    'PORT' => $parametriMail['MAIL']['port'],
//                    'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
//                    'USERNAME' => $parametriMail['MAIL']['username'],
//                    'PASSWORD' => $parametriMail['MAIL']['password']
//                ));
                $this->saveRicMailRecord($dati, $Ricmail_rec['TOADDR'], "RESPONSABILE", $Ricmail_rec['NOMRES'], $Ricmail_rec['ROWID'], $itaMailer->ErrorInfo);
            }
        }
        //return true;
        return $returnMsg;
    }

    public function InvioMailCambioEsibente($dati, $modo) {
        $PRAM_DB = $dati['PRAM_DB'];
        $parametriMail = $this->GetParametriMail($PRAM_DB);

        // Rilegge i parametri dei Template della Mail
        $arrayDatiMail = $this->arrayDatiMail($dati, $PRAM_DB);

        $datiUtenteLoggato = frontOfficeApp::$cmsHost->getDatiUtente();

        $mailDestinatario = '';

        $richiesta = substr($dati['Proric_rec']['RICNUM'], 4) . "/" . substr($dati['Proric_rec']['RICNUM'], 0, 4);
        switch ($modo) {
            case "CAMBIOESIB_ESIBENTE":
                $Subject = $arrayDatiMail['oggettoCambioEsibEsibente'];
                if ($Subject == "") {
                    $Subject = "Notifica cambio esibente richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyCambioEsibEsibente'];
                if ($Body == "") {
                    $Body = "Con la presente si comunica che il sottoscritto " . $dati['Proric_rec']['RICCOG'] . " " . $dati['Proric_rec']['RICNOM'] . " (" . $dati['Proric_rec']['RICFIS'] . ") è stato indicato come nuovo Esibente per la richiesta on-line $richiesta.<br> "
                            . "Questa operazione è stata effettuata dal Sig.re/Sig.ra " . $datiUtenteLoggato['cognome'] . " " . $datiUtenteLoggato['nome'];
                }

                $mailDestinatario = $dati['Proric_rec']['RICEMA'];
                break;
            case "CAMBIOESIB_DICH":
                $Subject = $arrayDatiMail['oggettoCambioEsibDich'];
                if ($Subject == "") {
                    $Subject = "Notifica cambio esibente richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyCambioEsibDich'];
                if ($Body == "") {
                    $Body = "si comunica che il cambio esibente per la richiesta on-line $richiesta è stato effettuato con SUCCESSO. <br>"
                            . "Il nuovo esibente è il Sig.re/Sig.ra " . $dati['Proric_rec']['RICCOG'] . " " . $dati['Proric_rec']['RICNOM'];
                }
                // Indirizzo Mail del soggetto loggato

                $mailDestinatario = $datiUtenteLoggato['email'];
                break;
        }

        if (!$mailDestinatario) {
            $returnMsg = "Non è stato trovato indirizzo Mail di spedizione per il tipo $modo.";
            return $returnMsg;
        }


        /*
         * Invio la mail 
         */
        $returnMsg = "";
        $itaMailer = new itaMailer();
        $itaMailer->IsHTML();
        $itaMailer->Subject = $Subject;
        $itaMailer->Body = $Body;
        $itaMailer->itaAddAddress($mailDestinatario);
        if (!$itaMailer->Send(array(
                    'FROM' => $parametriMail['MAIL']['from'],
                    'NAME' => $parametriMail['MAIL']['name'],
                    'HOST' => $parametriMail['MAIL']['host'],
                    'PORT' => $parametriMail['MAIL']['port'],
                    'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
                    'USERNAME' => $parametriMail['MAIL']['username'],
                    'PASSWORD' => $parametriMail['MAIL']['password']
                ))) {
            $returnMsg = $itaMailer->ErrorInfo . "<br>";
        }

        // Salvo la tracciatura su RICMAIL. 
        // Salva il nuovo record senza esito diu spedizione
        $idRicmail = $this->saveRicMailRecord($dati, $mailDestinatario, $modo, '', 0);
        if ($idRicmail) {
            // Aggiorna esito della spedizione fatta
            $this->saveRicMailRecord($dati, $mailDestinatario, $modo, '', $idRicmail, $itaMailer->ErrorInfo);
        } else {
            $returnMsg = "Invio della mail non è stata salvata per il tipo $modo.";
        }

        return $returnMsg;
    }

    public function InvioMailSoggetto($dati, $modo, $nominativo, $mail = '') {
        $PRAM_DB = $dati['PRAM_DB'];
        $parametriMail = $this->GetParametriMail($PRAM_DB);

        // Rilegge i parametri dei Template della Mail
        $arrayDatiMail = $this->arrayDatiMail($dati, $PRAM_DB);

        $datiUtenteLoggato = frontOfficeApp::$cmsHost->getDatiUtente();

        $richiesta = substr($dati['Proric_rec']['RICNUM'], 4) . "/" . substr($dati['Proric_rec']['RICNUM'], 0, 4);
        switch ($modo) {
            case "ACL_ASSEGNATARIO_PASSO":
                $Subject = $arrayDatiMail['oggettoAclAssegnatario_passo'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione passo per la richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclAssegnatario_passo'];
                if ($Body == "") {
                    $Body = "Con la presente si comunica che il sottoscritto " . $nominativo . "  è stato abilitato a svolgere il passo <b> " . $dati['Ricite_rec']['ITEDES'] . "</b> per la richiesta on-line $richiesta.<br> "
                            . "Questa operazione è stata effettuata dal Sig.re/Sig.ra " . $datiUtenteLoggato['cognome'] . " " . $datiUtenteLoggato['nome'];
                }

                $mailDestinatario = $mail;

                break;
            case "ACL_DICH_PASSO":
                $Subject = $arrayDatiMail['oggettoAclDich_passo'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione passo per la richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclDich_passo'];
                if ($Body == "") {
                    $Body = "Si comunica che il passo <b> " . $dati['Ricite_rec']['ITEDES'] . "</b> per la richiesta on-line $richiesta è stato assegnato con SUCCESSO. <br>"
                            . "Il soggetto abilitato è il Sig.re/Sig.ra " . $nominativo;
                }
                // Indirizzo Mail del soggetto loggato
                $mailDestinatario = $datiUtenteLoggato['email'];

                break;
            case "ACL_ASSEGNATARIO_INTEG":
                $Subject = $arrayDatiMail['oggettoAclAssegnatario_integ'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione integrazione per la richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclAssegnatario_integ'];
                if ($Body == "") {
                    $Body = "Con la presente si comunica che il sottoscritto " . $nominativo . "  è stato abilitato a svolgere l'integrazione per la richiesta on-line $richiesta.<br> "
                            . "Questa operazione è stata effettuata dal Sig.re/Sig.ra " . $datiUtenteLoggato['cognome'] . " " . $datiUtenteLoggato['nome'];
                }

                $mailDestinatario = $mail;

                break;
            case "ACL_DICH_INTEG":
                $Subject = $arrayDatiMail['oggettoAclDich_integ'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione integrazione per la richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclDich_integ'];
                if ($Body == "") {
                    $Body = "Si comunica che la possibilità di inoltrare un integrazione per la richiesta on-line $richiesta è stata assegnata con SUCCESSO. <br>"
                            . "Il soggetto abilitato è il Sig.re/Sig.ra " . $nominativo;
                }
                // Indirizzo Mail del soggetto loggato
                $mailDestinatario = $datiUtenteLoggato['email'];

                break;
            case "ACL_ASSEGNATARIO_VISUAL":
                $Subject = $arrayDatiMail['oggettoAclAssegnatario_visual'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione visualizzazione della richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclAssegnatario_visual'];
                if ($Body == "") {
                    $Body = "Con la presente si comunica che il sottoscritto " . $nominativo . "  è stato abilitato a visualizzare la richiesta on-line $richiesta.<br> "
                            . "Questa operazione è stata effettuata dal Sig.re/Sig.ra " . $datiUtenteLoggato['cognome'] . " " . $datiUtenteLoggato['nome'];
                }

                $mailDestinatario = $mail;

                break;
            case "ACL_DICH_VISUAL":
                $Subject = $arrayDatiMail['oggettoAclDich_visual'];
                if ($Subject == "") {
                    $Subject = "Notifica assegnazione visualizzazione della richiesta on-line numero $richiesta.";
                }

                $Body = $arrayDatiMail['bodyAclDich_visual'];
                if ($Body == "") {
                    $Body = "Si comunica che l'abilitazione di visualizzazione per la richiesta on-line $richiesta è stata assegnata con SUCCESSO. <br>"
                            . "Il soggetto abilitato è il Sig.re/Sig.ra " . $nominativo;
                }
                // Indirizzo Mail del soggetto loggato
                $mailDestinatario = $datiUtenteLoggato['email'];

                break;
        }

        if (!$mailDestinatario) {
            $returnMsg = "Non è stato trovato indirizzo Mail di spedizione per il tipo $modo.";
            return $returnMsg;
        }


        /*
         * Invio la mail 
         */
        $returnMsg = "";
        $itaMailer = new itaMailer();
        $itaMailer->IsHTML();
        $itaMailer->Subject = $Subject;
        $itaMailer->Body = $Body;
        $itaMailer->itaAddAddress($mailDestinatario);
        if (!$itaMailer->Send(array(
                    'FROM' => $parametriMail['MAIL']['from'],
                    'NAME' => $parametriMail['MAIL']['name'],
                    'HOST' => $parametriMail['MAIL']['host'],
                    'PORT' => $parametriMail['MAIL']['port'],
                    'SMTPSECURE' => $parametriMail['MAIL']['smtpSecure'],
                    'USERNAME' => $parametriMail['MAIL']['username'],
                    'PASSWORD' => $parametriMail['MAIL']['password']
                ))) {
            $returnMsg = $itaMailer->ErrorInfo . "<br>";
        }

        // Salvo la tracciatura su RICMAIL. 
        // Salva il nuovo record senza esito di spedizione
        $idRicmail = $this->saveRicMailRecord($dati, $mailDestinatario, $modo, '', 0);
        if ($idRicmail) {
            // Aggiorna esito della spedizione fatta
            $this->saveRicMailRecord($dati, $mailDestinatario, $modo, '', $idRicmail, $itaMailer->ErrorInfo);
        } else {
            $returnMsg = "Invio della mail non è stata salvata per il tipo $modo.";
        }

        return $returnMsg;
    }

    public function saveRicmailRecord($dati, $toAddr, $class, $nomres, $rowid = 0, $errorMsg = "") {
        $metadati = array();

        if ($rowid == 0) {
            $RicMail_rec = array(
                'RICNUM' => $dati['Proric_rec']['RICNUM'],
                'ITEKEY' => $dati['Ricite_rec']['ITEKEY'],
                'MAILSTATO' => "@DA_INVIARE@",
                'METADATI' => '',
                'MSGDATE' => '',
                'SENDREC' => 'S',
                'TOADDR' => $toAddr,
                'TOCLASS' => $class,
                'IDMAIL' => '',
                'NOMRES' => $nomres
            );
            $nrow = ItaDB::DBInsert($dati["PRAM_DB"], "RICMAIL", 'ROWID', $RicMail_rec);
            if ($nrow != 1) {
                return false;
            }
            return ItaDB::DBLastId($dati["PRAM_DB"]);
        } else {
            if ($errorMsg) {
                $stato = "@ERRORE@";
                $msg = $errorMsg;
            } else {
                $stato = "@INVIATA@";
                $msg = "Il messaggio ï¿½ stato inviato con successo";
            }
            $metadati['DATIMAIL']['MESSAGGIO'] = $msg;
            $RicMail_rec = $this->GetRicmail($rowid, "rowid", false, $dati["PRAM_DB"]);
            $RicMail_rec['TOADDR'] = $toAddr;
            $RicMail_rec['MSGDATE'] = date("Ymd") . " " . date("H:i:s");
            $RicMail_rec['METADATI'] = serialize($metadati);
            $RicMail_rec['MAILSTATO'] = $stato;
            $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], "RICMAIL", 'ROWID', $RicMail_rec);
            if ($nRows == -1) {
                return false;
            }
        }
    }

    public function InvioRichiestaProtocollo($dati, $TotaleAllegati, $arrayOggettoProt, $modo, $clientParam) {
        $risultato = array();
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibProtocolla.class.php';
        $praLibProtocolla = new praLibProtocolla();
        $elementi = $praLibProtocolla->protocollaArrivo($dati, $TotaleAllegati, $arrayOggettoProt);
        if (!$elementi) {
            $risultato['Status'] = "-1";
            $risultato['Message'] = "Elementi per la protocollazione non trovati. Impossibile Protocollare.";
            return $risultato;
        }

        //
        //Leggo il tipo protocollo
        //
        $anapar_rec = $this->GetAnapar("TIPO_PROTOCOLLO", "parkey", $dati["PRAM_DB"], false);

        //
        //gestione documenti allegati
        //
        $arrayDoc = $praLibProtocolla->estraiAllegatiWS($TotaleAllegati, $dati, $anapar_rec['PARVAL']);
        $arrayDocFiltrati = $praLibProtocolla->GetAllegatiNonProt($arrayDoc, $anapar_rec['PARVAL'], $dati);

        //controllo che ci siano gli allegati
        if ($arrayDocFiltrati) {
            $elementi['dati']['DocumentoPrincipale'] = $arrayDocFiltrati['arrayDoc']['Principale'];
            $elementi['dati']['DocumentiAllegati'] = $arrayDocFiltrati['arrayDoc']['Allegati'];
        }

        if ($anapar_rec['PARVAL'] == "Iride") {
            require (ITA_LIB_PATH . '/itaPHPIride/itaProIrideManager.class.php');
            $itaManager = itaProIrideManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Jiride") {
            require (ITA_LIB_PATH . '/itaPHPJiride/itaProJIrideManager.class.php');
            $itaManager = itaProJirideManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Paleo") {
            require (ITA_LIB_PATH . '/itaPHPPaleo/itaProPaleoManager.class.php');
            $itaManager = itaProPaleoManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Paleo4") {
            require (ITA_LIB_PATH . '/itaPHPPaleo4/itaProPaleoManager.class.php');
            $itaManager = itaProPaleoManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Paleo41") {
            require (ITA_LIB_PATH . '/itaPHPPaleo4/itaProPaleo41Manager.class.php');
            $itaManager = itaProPaleo41Manager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Italsoft") {
            require (ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotManager.class.php');
            $itaManager = itaItalprotManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Halley") {
            require (ITA_LIB_PATH . '/itaPHPELios/itaELiosManager.class.php');
            $itaManager = itaELiosManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Infor") {
            require (ITA_LIB_PATH . '/itaPHPInfor/itaInforManager.class.php');
            $itaManager = itaInforManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Sici") {
            require (ITA_LIB_PATH . '/itaPHPSici/itaSiciManager.class.php');
            $itaManager = itaSiciManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "HyperSic") {
            require (ITA_LIB_PATH . '/itaPHPhyperSIC/itaHyperSICmanager.class.php');
            $itaManager = itaHyperSICmanager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Leonardo") {
            require (ITA_LIB_PATH . '/itaPHPLeonardo/itaLeonardoManager.class.php');
            $itaManager = itaLeonardoManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "Kibernetes") {
            require (ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesManager.class.php');
            $itaManager = itaKibernetesManager::getInstance($clientParam);
        } elseif ($anapar_rec['PARVAL'] == "CiviliaNext") {
            require (ITA_LIB_PATH . '/itaPHPCiviliaNext/itaPHPCiviliaNextManager.class.php');
            $itaManager = itaCiviliaNextManager::getInstance($clientParam);
        } else {
            $risultato['Status'] = "-1";
            $risultato['Message'] = "Impossibile protocollare.<br>Selezionare prima il tipo di protocollo remoto";
            return $risultato;
        }

        $risultato = $itaManager->InserisciProtocollo($elementi, "A");
        if ($risultato['Status'] == "-1") {
            return $risultato;
        }

        //if ($risultato["RetValue"]['DatiProtocollazione']['DocNumber']['value'] && $risultato["RetValue"]['DatiProtocollazione']['proNum']['value']) {
        if ($risultato["RetValue"]['DatiProtocollazione']['DocNumber']['value'] || $risultato["RetValue"]['DatiProtocollazione']['proNum']['value']) {
            $dati['Proric_rec']['RICNPR'] = $risultato["RetValue"]['DatiProtocollazione']['Anno']['value'] . $risultato["RetValue"]['DatiProtocollazione']['proNum']['value'];
            $dati['Proric_rec']['RICDPR'] = date("Ymd", strtotime($risultato["RetValue"]['DatiProtocollazione']["Data"]['value']));
            $dati['Proric_rec']['RICMETA'] = serialize($risultato["RetValue"]);
            try {
                $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], "PRORIC", 'ROWID', $dati['Proric_rec']);
                if ($nRows == -1) {
                    $risultato['Status'] = "-1";
                    $risultato['Message'] = "Errore Aggiornamento su PRORIC dopo protocollazione";
                    return $risultato;
                }
            } catch (Exception $e) {
                $risultato['Status'] = "-1";
                $risultato['Message'] = $e->getMessage();
                return $risultato;
            }

            $strNoProt = "";
            if ($arrayDocFiltrati['strNoProt']) {
                //$risultato['strNoProt'] = $arrayDocFiltrati['strNoProt'];
                $strNoProt .= $arrayDocFiltrati['strNoProt'];
            }

            if ($risultato['strNoProt']) {
                $strNoProt .= $risultato['strNoProt'];
            }

            if ($strNoProt) {
                $risultato['strNoProt'] = $strNoProt;
            }

            /*
             * Marcatura allegati protocollati
             */
            $msgErrMarc = $this->MarcaturaAllegati($arrayDocFiltrati, $dati);
            if ($msgErrMarc) {
                $risultato['strNoMarcati'] = $msgErrMarc;
            }

            return $risultato;
        } else {
            return $risultato;
        }
    }

    public function InvioRichiestaFascicolazione($dati, $arrayOggettoProt, $clientParamFascicolazione, $clientParam, $DocNumber, $idFascicolo = null, $NProt = "", $Anno = "") {
        $risultato = array();
        $risultato['Status'] = "0";
        $risultato['Message'] = "Fascicolazione avvenuta con successo";
        $risultato["RetValue"] = true;
        //
        //require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibProtocolla.class.php';
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibProtocolla.class.php';
        $praLibProtocolla = new praLibProtocolla();
        $elementi = $praLibProtocolla->protocollaArrivo($dati, "", $arrayOggettoProt);
        if (!$elementi) {
            $risultato['Status'] = "-1";
            $risultato['Message'] = "Elementi per la fascicolazione non trovati. Impossibile creare il fascicolo.";
            return $risultato;
        }

        //
        //Leggo il tipo protocollo
        //
        $anapar_rec = $this->GetAnapar("TIPO_PROTOCOLLO", "parkey", $dati["PRAM_DB"], false);

        if ($anapar_rec['PARVAL'] == "Jiride") {
            /*
             * Istanzio il manager per la fascicolazione
             */
            require (ITA_LIB_PATH . '/itaPHPJiride/itaJIrideFascicolazione.class.php');
            $itaManagerFascicola = itaJIrideFascicolazione::getInstance($clientParamFascicolazione);

            /*
             * Istanzio il manager per leggi protocollo
             */
            require_once (ITA_LIB_PATH . '/itaPHPJiride/itaProJIrideManager.class.php');
            $itaManager = itaProJirideManager::getInstance($clientParam);

            /*
             * Faccio un leggi protocollo con l'ID del protocollo precedente, per trovare l'ID del Fascicolo precedente
             */
            if ($elementi['dati']['numeroProtocolloAntecedente']) {
                $risultato = $itaManager->LeggiProtocollo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                $risultato = $itaManagerFascicola->FascicolaDocumento($risultato['RetValue']['Dati']['IdPratica'], $DocNumber);
                return $risultato;
            }

            $elementi["DocNumber"] = $DocNumber;
            if ($idFascicolo === null) {
                /*
                 * Lancio il ws per Creare il fascicolo
                 */
                $risultato = $itaManagerFascicola->CreaFascicolo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                $idFascicolo = $risultato['idFascicolo'];
            }
            /*
             * Fascicolo il documento (Inserisco il protocollo nel fascicolo)
             */
            $risultato = $itaManagerFascicola->FascicolaDocumento($idFascicolo, $elementi['DocNumber']);
            if ($risultato['Status'] == "-1") {
                return $risultato;
            }
        } elseif ($anapar_rec['PARVAL'] == "Italsoft") {
            /*
             * Istanzio il manager per la fascicolazione
             */
            require (ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotFascicoliManager.class.php');
            $itaManagerFascicola = itaItalprotFascicoliManager::getInstance($clientParamFascicolazione);

            /*
             * Faccio un GetFascicoliProtocollo con il num e anno del protocollo precedente, per trovare l'ID del Fascicolo precedente
             */
            if ($elementi['dati']['numeroProtocolloAntecedente']) {
                $risultato = $itaManagerFascicola->GetFascicoliProtocollo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }

                /*
                 * Fascicolo il documento (Inserisco il protocollo nel fascicolo).
                 * Se c'è più di un fascicolo, prendo il primo con lo stesso titolario
                 */
                $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['ElencoFascicoli'][0]['codiceFascicolo'];
                if (count($risultato['ElencoFascicoli']) > 1) {
                    $arrClassificazione = explode(".", $elementi['dati']['Classificazione']);
                    $classEstesa = str_pad($arrClassificazione[0], 4, "0", STR_PAD_LEFT) . str_pad($arrClassificazione[1], 4, "0", STR_PAD_LEFT);
                    foreach ($risultato['ElencoFascicoli'] as $fascicolo) {
                        if ($classEstesa == $fascicolo['titolario']) {
                            $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $fascicolo['codiceFascicolo'];
                            break;
                        }
                    }
                }
            } else {
                $risultato = $itaManagerFascicola->CreaFascicolo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['datiFascicolo']['codiceFascicolo'];
            }
            //$elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['ElencoFascicoli'][0]['codiceFascicolo'];
            $elementi['dati']['Fascicolazione']['Anno'] = $Anno;
            $elementi['dati']['Fascicolazione']['Numero'] = $NProt;
            $risultatoFascicola = $itaManagerFascicola->FascicolaProtocollo($elementi, "A");
            if ($risultatoFascicola['Status'] == "-1") {
                return $risultatoFascicola;
            }
        } elseif ($anapar_rec['PARVAL'] == "Iride") {
            /*
             * Istanzio il manager per la fascicolazione
             */
            require (ITA_LIB_PATH . '/itaPHPIride/itaIrideFascicolazione.class.php');
            $itaManagerFascicola = itaIrideFascicolazione::getInstance($clientParamFascicolazione);

            /*
             * Istanzio il manager per leggi protocollo
             */
            require_once (ITA_LIB_PATH . '/itaPHPIride/itaProIrideManager.class.php');
            $itaManager = itaProIrideManager::getInstance($clientParam);

            /*
             * Faccio un leggi protocollo con l'ID del protocollo precedente, per trovare l'ID del Fascicolo precedente
             */
            if ($elementi['dati']['numeroProtocolloAntecedente']) {
                $risultato = $itaManager->LeggiProtocollo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                /*
                 * Se la pratica principale non è fascicolata, inizializzo a null idFascicolo e poi lo creo nuovo
                 */
                if ($risultato['RetValue']['Dati']['IdPratica'] == "0") {
                    $idFascicolo = null;
                } else {
                    $idFascicolo = $risultato['RetValue']['Dati']['IdPratica'];
                }
                //$risultato = $itaManagerFascicola->FascicolaDocumento($risultato['RetValue']['Dati']['IdPratica'], $DocNumber);
                //return $risultato;
            }

            $elementi["DocNumber"] = $DocNumber;
            if ($idFascicolo === null) {
                /*
                 * Lancio il ws per Creare il fascicolo
                 */
                $risultato = $itaManagerFascicola->CreaFascicolo($elementi);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                $idFascicolo = $risultato['idFascicolo'];
            }
            /*
             * Fascicolo il documento (Inserisco il protocollo nel fascicolo)
             */
            $risultato = $itaManagerFascicola->FascicolaDocumento($idFascicolo, $elementi['DocNumber']);
            if ($risultato['Status'] == "-1") {
                return $risultato;
            }
        } elseif ($anapar_rec['PARVAL'] == "Sici") {
            require_once ITA_LIB_PATH . '/itaPHPSici/itaSiciManager.class.php';
            $itaManager = itaSiciManager::getInstance($clientParamFascicolazione);
            //
            $creaFascicolo = true;
            if ($elementi['dati']['numeroProtocolloAntecedente']) {
                $param = array();
                $param['NumeroProtocollo'] = $elementi['dati']['numeroProtocolloAntecedente'];
                $param['AnnoProtocollo'] = $elementi['dati']['annoProtocolloAntecedente'];
                $risultato = $itaManager->LeggiProtocollo($param);
                if ($risultato['Status'] == "-1") {
                    return $risultato;
                }
                /*
                 * Se la pratica principale non è fascicolata, creo un nuovo fascicolo (Indicazione di Davide Cecchini)
                 */
                if ($risultato['RetValue']['DatiProtocollo']['NumeroFascicolo']) {
                    $creaFascicolo = false;
                    $idFascicolo = $risultato['RetValue']['DatiProtocollo']['NumeroFascicolo'];
                    $annoFascicolo = $risultato['RetValue']['DatiProtocollo']['AnnoFascicolo'];
                }
            }

            /*
             * Se c'è il flag, creo il fascicolo
             */
            if ($creaFascicolo == true) {
                $risultatoCrea = $itaManager->CreaFascicolo($elementi);
                if ($risultatoCrea['Status'] == "-1") {
                    return $risultatoCrea;
                }
                $idFascicolo = $risultatoCrea['idFascicolo'];
                $annoFascicolo = $risultatoCrea['annoFascicolo'];
            }

            /*
             * Fascicolo il protocollo
             */
            $elementi['AnnoProtocollo'] = $Anno;
            $elementi['NumeroProtocollo'] = $NProt;
            $risultatoFascicola = $itaManager->FascicolaDocumento($idFascicolo, $annoFascicolo, $elementi);
            if ($risultatoFascicola['Status'] == "-1") {
                return $risultatoFascicola;
            }
        } else {
            $risultato['Status'] = "-1";
            $risultato['Message'] = "Impossibile creare un fascicolo nel protocollo.<br>Selezionare prima il tipo di protocollo remoto";
            return $risultato;
        }

//        /*
//         * Faccio un leggi protocollo con l'ID del protocollo precedente, per trovare l'ID del Fascicolo precedente
//         */
//        if ($elementi['dati']['numeroProtocolloAntecedente']) {
//            $risultato = $itaManager->LeggiProtocollo($elementi);
//            if ($risultato['Status'] == "-1") {
//                return $risultato;
//            }
//            $risultato = $itaManagerFascicola->FascicolaDocumento($risultato['RetValue']['Dati']['IdPratica'], $DocNumber);
//            return $risultato;
//        }
//
//        $elementi["DocNumber"] = $DocNumber;
//        if ($idFascicolo === null) {
//            /*
//             * Lancio il ws per Creare il fascicolo
//             */
//            $risultato = $itaManagerFascicola->CreaFascicolo($elementi);
//            if ($risultato['Status'] == "-1") {
//                return $risultato;
//            }
//            $idFascicolo = $risultato['idFascicolo'];
//        }
//        /*
//         * Fascicolo il documento (Inserisco il protocollo nel fascicolo)
//         */
//        $risultato = $itaManagerFascicola->FascicolaDocumento($idFascicolo, $elementi['DocNumber']);
//        if ($risultato['Status'] == "-1") {
//            return $risultato;
//        }
        return $risultato;
    }

    /**
     * Restituisce un array con le parti per compilare la mail responsabile e richiedente richiesta
     *
     * @param type $dati
     * @param type $PRAM_DB
     * @return boolean
     */
    public function arrayDatiMail($dati, $PRAM_DB, $param = array()) {
        $Appoggio = $this->DecodValori($dati['Anapra_rec'], $Appoggio, $PRAM_DB);
        $praDizionario = new praDizionario();
        $Dictionary = $praDizionario->getDictionary();
        $fileXml = $dati['CartellaMail'] . "/mail.xml";
        if (!file_exists($fileXml)) {
            return false;
        }
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($fileXml);
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());

        $arrayDatiMail = array();
        $arrayDatiMail['oggettoRichiedente'] = $this->AssegnaVariabili($arrayXml['SUBJECT_RICHIEDENTE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyRichiedente'] = $this->AssegnaVariabili($arrayXml['BODY_RICHIEDENTE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoResponsabile'] = $this->AssegnaVariabili($arrayXml['SUBJECT_RESPONSABILE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyResponsabile'] = $this->AssegnaVariabili($arrayXml['BODY_RESPONSABILE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoAnnullamento'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ANNULLAMENTO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAnnullamento'] = $this->AssegnaVariabili($arrayXml['BODY_ANNULLAMENTO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoRichInfocamere'] = $this->AssegnaVariabili($arrayXml['SUBJECT_RICHINFOCAMERE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyRichInfocamere'] = $this->AssegnaVariabili($arrayXml['BODY_RICHINFOCAMERE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoIntRich'] = $this->AssegnaVariabili($arrayXml['SUBJECT_INT_RICH']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyIntRich'] = $this->AssegnaVariabili($arrayXml['BODY_INT_RICH']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoIntResp'] = $this->AssegnaVariabili($arrayXml['SUBJECT_INT_RESP']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyIntResp'] = $this->AssegnaVariabili($arrayXml['BODY_INT_RESP']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoRichParere'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ARICPARERI']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyRichParere'] = $this->AssegnaVariabili($arrayXml['BODY_ARICPARERI']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoRespParere'] = $this->AssegnaVariabili($arrayXml['SUBJECT_AENTITERZI']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyRespParere'] = $this->AssegnaVariabili($arrayXml['BODY_AENTITERZI']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);

        $arrayDatiMail['oggettoCambioEsibEsibente'] = $this->AssegnaVariabili($arrayXml['SUBJECT_CAMBIOESIB_ESIBENTE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyCambioEsibEsibente'] = $this->AssegnaVariabili($arrayXml['BODY_CAMBIOESIB_ESIBENTE']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoCambioEsibDich'] = $this->AssegnaVariabili($arrayXml['SUBJECT_CAMBIOESIB_DICH']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyCambioEsibDich'] = $this->AssegnaVariabili($arrayXml['BODY_CAMBIOESIB_DICH']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);

        $arrayDatiMail['oggettoAclAssegnatario_passo'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_ASSEGNATARIO_PASSO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclAssegnatario_passo'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_ASSEGNATARIO_PASSO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoAclDich_passo'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_DICH_PASSO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclDich_passo'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_DICH_PASSO']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);

        $arrayDatiMail['oggettoAclAssegnatario_integ'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_ASSEGNATARIO_INTEG']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclAssegnatario_integ'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_ASSEGNATARIO_INTEG']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoAclDich_integ'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_DICH_INTEG']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclDich_integ'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_DICH_INTEG']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);

        $arrayDatiMail['oggettoAclAssegnatario_visual'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_ASSEGNATARIO_VISUAL']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclAssegnatario_visual'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_ASSEGNATARIO_VISUAL']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['oggettoAclDich_visual'] = $this->AssegnaVariabili($arrayXml['SUBJECT_ACL_DICH_VISUAL']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayDatiMail['bodyAclDich_visual'] = $this->AssegnaVariabili($arrayXml['BODY_ACL_DICH_VISUAL']['@textNode'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);


        /*
         * Controllo valori body richiedente personalizzato per sportello
         */
        $metadataSportello = "";
        if ($dati['Anatsp_rec']['TSPMETA']) {

            $metadataSportello = unserialize($dati['Anatsp_rec']['TSPMETA']);
            $MailTemplate = $metadataSportello['TEMPLATEMAIL'];

            /*
             * RICHIEDENTE
             */
            if ($MailTemplate['SUBJECT_RICHIEDENTE']) {
                $arrayDatiMail['oggettoRichiedente'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_RICHIEDENTE'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_RICHIEDENTE']) {
                $arrayDatiMail['bodyRichiedente'] = $this->AssegnaVariabili($MailTemplate['BODY_RICHIEDENTE'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }

            /*
             * RESPONSABILE
             */
            if ($MailTemplate['SUBJECT_RESPONSABILE']) {
                $arrayDatiMail['oggettoResponsabile'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_RESPONSABILE'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_RESPONSABILE']) {
                $arrayDatiMail['bodyResponsabile'] = $this->AssegnaVariabili($MailTemplate['BODY_RESPONSABILE'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }

            /*
             * RICHIEDENTE INTEGRAZIONE
             */
            if ($MailTemplate['SUBJECT_INT_RICH']) {
                $arrayDatiMail['oggettoIntRich'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_INT_RICH'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_INT_RICH']) {
                $arrayDatiMail['bodyIntRich'] = $this->AssegnaVariabili($MailTemplate['BODY_INT_RICH'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }

            /*
             * RESPONSABILE INTEGRAZIONE
             */
            if ($MailTemplate['SUBJECT_INT_RESP']) {
                $arrayDatiMail['oggettoIntResp'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_INT_RESP'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_INT_RESP']) {
                $arrayDatiMail['bodyIntResp'] = $this->AssegnaVariabili($MailTemplate['BODY_INT_RESP'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }

            /*
             * RICHIEDENTE PARERE ESPRESSO
             */
            if ($MailTemplate['SUBJECT_ARICPARERI']) {
                $arrayDatiMail['oggettoRichParere'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_ARICPARERI'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_ARICPARERI']) {
                $arrayDatiMail['bodyRichParere'] = $this->AssegnaVariabili($MailTemplate['BODY_ARICPARERI'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }

            /*
             * RESPONSABILE PARERE ESPRESSO
             */
            if ($MailTemplate['SUBJECT_ARICPARERI']) {
                $arrayDatiMail['oggettoRespParere'] = $this->AssegnaVariabili($MailTemplate['SUBJECT_AENTITERZI'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
            if ($MailTemplate['BODY_ARICPARERI']) {
                $arrayDatiMail['bodyRespParere'] = $this->AssegnaVariabili($MailTemplate['BODY_AENTITERZI'], $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
            }
        }

        /*
         * Controllo valori body richiedente personalizzato per procedimento
         */
        $metadata = "";
        if ($dati['Ricite_rec']['ITEMETA']) {

            $dictionaryValues = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();

            $metadata = unserialize($dati['Ricite_rec']['ITEMETA']);
            $MailTemplate = $metadata['TESTOBASEMAIL'];
            if ($MailTemplate['BODY_RICHIEDENTE']) {
                $arrayDatiMail['bodyRichiedente'] = $this->valorizzaTemplate($MailTemplate['BODY_RICHIEDENTE'], $dictionaryValues);
            }
            if ($MailTemplate['BODY_RESPONSABILE']) {
                $arrayDatiMail['bodyResponsabile'] = $this->valorizzaTemplate($MailTemplate['BODY_RESPONSABILE'], $dictionaryValues);
            }
        }
        return $arrayDatiMail;
    }

    public function arrayOggettoProt($dati, $PRAM_DB, $param = array()) {
        $Appoggio = $this->DecodValori($dati['Anapra_rec'], $Appoggio, $PRAM_DB);
        $praDizionario = new praDizionario();
        $Dictionary = $praDizionario->getDictionary();
        $fileXml = $dati['CartellaMail'] . "/mail.xml";
        if (!file_exists($fileXml)) {
            return false;
        }
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($fileXml);
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());

        /*
         * Leggo il template oggetto protocollo
         */
        $templateOggetto = $arrayXml['SUBJECT_PROTOCOLLO']['@textNode'];
        if ($templateOggetto == '') {
            $templateOggetto = "Protocollazione Richiesta on-line $.NUMPRO$/$.ANNOPRO$ - $.DESCPRO$ - $.DENOMIMPRESA$";
        }

        /*
         * Leggo il template oggetto fascicolo
         */
        $templateOggettoFascicolo = $arrayXml['SUBJECT_FASCICOLO']['@textNode'];
        if ($templateOggettoFascicolo == '') {
            $templateOggettoFascicolo = "Fascicolazione Richiesta on-line $.NUMPRO$/$.ANNOPRO$ - $.DESCPRO$ - $.DENOMIMPRESA$";
        }
        $arrayProt = array();
        $arrayProt['oggettoProtocollo'] = $this->AssegnaVariabili($templateOggetto, $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        $arrayProt['oggettoFascicolo'] = $this->AssegnaVariabili($templateOggettoFascicolo, $Dictionary, $dati, $Appoggio, $PRAM_DB, $param);
        return $arrayProt;
    }

    public function GetHtmlOutput($dati, $bodyMail, $fileBodyBase, $class) {
        //$fileBodyBase = "body.html";
        $file = fopen($dati['CartellaAllegati'] . "/" . $fileBodyBase, "w+");
        if (!file_exists($dati['CartellaAllegati'] . "/" . $fileBodyBase)) {
            return false;
        } else {
            fwrite($file, "<html>");
            fwrite($file, "<body>");
            fwrite($file, "<SCRIPT language=JavaScript>");
            fwrite($file, "focus();");
            fwrite($file, "print();");
            fwrite($file, "close();");
            fwrite($file, "</SCRIPT>");
            fwrite($file, "<pre>");
            fwrite($file, $bodyMail);
            fwrite($file, "</pre>");
            fwrite($file, "</body>");
            fwrite($file, "</html>");
            fclose($file);

            $out = $bodyMail . "<br><br>";
            $out .= "<div align=\"center\">";
            $out .= "<form>";
            $out .= "<input class=\"ita-print-hide italsoft-button\" type=\"button\" onClick=\"window.print();\" value=\"Stampa\"/>";
            $out .= "</form>";
            $out .= "</div>";
            return $out;
        }
    }

    public function AssegnaVariabili($Testo, $Dictionary, $dati, $Appoggio, $PRAM_DB, $param = array()) {
        if (!$param) {
            $param = frontOfficeApp::$cmsHost->getDatiUtente();
        }

        $DatiImpresa = $dati['dati_infocamere']['datiImpresa'];
        $DatiSportello = $dati['dati_infocamere']['datiSportello'];
        $DatiSedeLegale = $dati['dati_infocamere']['datiSedeLegale'];
        //
        // Mi trovo tutti i dati aggiuntivi provenienti dai passi upload per per assegnare i valori anche senza la raccolta dati
        //
        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $dati['Proric_rec']['RICNUM'] . "'", true);
        if ($dati['Proric_rec']['RICRPA']) {
            $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $dati['Proric_rec']['RICRPA'] . "'", true);
        }
        //$Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='" . $dati['Proric_rec']['RICNUM'] . "' AND DAGSET <> ''", true);
        foreach ($Dictionary as $dizionario) {
            $chiave = $dizionario['chiave'];
            while ((strpos($Testo, $chiave) !== false)) {
                $posizione = strpos($Testo, $chiave);
                $lunghezza = strlen($chiave);
                $sostituzione = '';
                switch ($chiave) {
                    case '$.NUMPRO$':
                        $sostituzione = substr($dati['Proric_rec']['RICNUM'], 4);
                        break;
                    case '$.DATAPRO$':
                        $sostituzione = substr($dati['Proric_rec']['RICDRE'], 6, 2) . "/" . substr($dati['Proric_rec']['RICDRE'], 4, 2) . "/" . substr($dati['Proric_rec']['RICDRE'], 0, 4);
                        break;
                    case '$.ANNOPRO$':
                        $sostituzione = substr($dati['Proric_rec']['RICNUM'], 0, 4);
                        break;
                    case '$.CODPRO$':
                        $sostituzione = $dati['Proric_rec']['RICPRO'];
                        break;
                    case '$.DESCPRO$':
                        $sostituzione = $Appoggio[1];
                        break;
                    case '$.RESPPRO$':
                        $sostituzione = $Appoggio[2] . " " . $Appoggio[3];
                        break;
                    case '$.DESCSET$':
                        $sostituzione = $Appoggio[4];
                        break;
                    case '$.RESPSET$':
                        $sostituzione = $Appoggio[5];
                        break;
                    case '$.DESCSER$':
                        $sostituzione = $Appoggio[7];
                        break;
                    case '$.RESPSER$':
                        $sostituzione = $Appoggio[8];
                        break;
                    case '$.UNITA$':
                        $sostituzione = $Appoggio[10];
                        break;
                    case '$.OPERATORE$':
                        $sostituzione = $Appoggio[11];
                        break;
                    case '$.CODRICH$':
                        $sostituzione = $dati['Proric_rec']['RICANA'];
                        break;
                    case '$.NOME$':
                        $sostituzione = $dati['Proric_rec']['RICNOM'];
                        break;
                    case '$.COGNOME$':
                        $sostituzione = $dati['Proric_rec']['RICCOG'];
                        break;
                    case '$.FISCALE$':
                        $sostituzione = $dati['Proric_rec']['RICFIS'];
                        break;
                    case '$.DATAINOLTROPRO$':
                        //Cambiato perche RICDAT viene valoriozzato dopo l'invio della mail
                        //$sostituzione = substr($dati['Proric_rec']['RICDAT'], 6, 2) . "/" . substr($dati['Proric_rec']['RICDAT'], 4, 2) . "/" . substr($dati['Proric_rec']['RICDAT'], 0, 4);
                        $sostituzione = date("d/m/Y");
                        break;
                    case '$.ORAINOLTROPRO$':
                        //Cambiato perche RICTIM viene valoriozzato dopo l'invio della mail
                        //$sostituzione = $dati['Proric_rec']['RICTIM'];
                        $sostituzione = date("H:i:s");
                        break;
                    case '$.INDIRIZZO$':
                        $sostituzione = $param['via'];
                        break;
                    case '$.COMUNE$':
                        $sostituzione = $param['comune'];
                        break;
                    case '$.CAP$':
                        $sostituzione = $param['cap'];
                        break;
                    case '$.PROVINCIA$':
                        $sostituzione = $param['provincia'];
                        break;
                    case '$.MOTIVO$':
                        $sostituzione = $_POST['motivo'];
                        break;
                    case '$.ENTE$':
                        $sostituzione = frontOfficeApp::$cmsHost->getSiteName();
                        break;
                    case '$.SPORTELLO$':
                        $Anatsp_rec = $this->GetAnatsp($dati['Proric_rec']['RICTSP'], 'codice', $PRAM_DB);
                        $sostituzione = $Anatsp_rec['TSPDES'];
                        break;
                    case '$.SPORTELLOCOM$':
                        $Anatsp_rec = $this->GetAnatsp($dati['Proric_rec']['RICTSP'], 'codice', $PRAM_DB);
                        $sostituzione = $Anatsp_rec['TSPCOM'];
                        break;
                    case '$.SPORTELLOAGG$':
                        $Anaspa_rec = $this->GetAnaspa($dati['Proric_rec']['RICSPA'], 'codice', $PRAM_DB);
                        $sostituzione = $Anaspa_rec['SPADES'];
                        break;
                    case '$.NUMRICHIESTAMADRE$':
                        $sostituzione = substr($dati['Proric_rec']['RICRPA'], 4);
                        break;
                    case '$.DESCPROCMADRE$':
                        $proric_rec_padre = $this->GetProric($dati['Proric_rec']['RICRPA'], "codice", $PRAM_DB);
                        $Anapra_rec_padre = $this->GetAnapra($proric_rec_padre['RICPRO'], "codice", $PRAM_DB);
                        $sostituzione = $Anapra_rec_padre['PRADES__1'] . $Anapra_rec_padre['PRADES__2'];
                        break;
                    case '$.ANNORICHIESTAMADRE$':
                        $sostituzione = substr($dati['Proric_rec']['RICRPA'], 0, 4);
                        break;
                    case '$.DENOMIMPRESA$':
                        if ($DatiImpresa['denominazione_impresa']) {
                            $sostituzione = $DatiImpresa['denominazione_impresa'];
                        } else {
                            //$sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DenominazioneImpresa");
                            $sostituzione = $this->GetSostituzioneImpresa($Ricdag_tab);
                        }
                        break;
                    case '$.CODFISIMPRESA$':
                        if ($DatiImpresa['codfis_suap']) {
                            $sostituzione = $DatiImpresa['codfis_suap'];
                        } else {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Codfis_InsProduttivo");
                        }
                        if ($sostituzione == "") {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_CODICEFISCALE_CFI");
                        }
                        break;
                    case '$.INDIRIZZOIMPRESA$':
                        if ($DatiImpresa['indirizzo_suap']) {
                            $sostituzione = $DatiImpresa['indirizzo_suap'];
                        } else {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Indir_InsProduttivo");
                        }
                        if ($sostituzione == "") {
                            //$sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZAVIA");
                            $sostituzione = $this->GetSostituzioneIndirizzo($Ricdag_tab);
                        }
                        break;
                    case '$.CIVICOIMPRESA$':
                        if ($DatiImpresa['num_civico_suap']) {
                            $sostituzione = $DatiImpresa['num_civico_suap'];
                        } else {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Civico_InsProduttivo");
                        }
                        if ($sostituzione == "") {
                            //$sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZACIVICO");
                            $sostituzione = $this->GetSostituzioneCivico($Ricdag_tab);
                        }
                        break;
                    case '$.CAPIMPRESA$':
                        if ($DatiImpresa['cap_suap']) {
                            $sostituzione = $DatiImpresa['cap_suap'];
                        }
                        break;
                    case '$.COMUNEIMPRESA$':
                        if ($DatiImpresa['comune_suap']) {
                            $sostituzione = $DatiImpresa['comune_suap'];
                        }
                        break;
                    case '$.PROVINCIAIMPRESA$':
                        if ($DatiImpresa['provincia_suap']) {
                            $sostituzione = $DatiImpresa['provincia_suap'];
                        }
                        break;
                    case '$.ISTATIMPRESA$':
                        if ($DatiImpresa['cod_istat_suap']) {
                            $sostituzione = $DatiImpresa['cod_istat_suap'];
                        }
                        break;
                    case '$.USERID$':
                        $sostituzione = frontOfficeApp::$cmsHost->getUserName();
                        break;
                    case '$.TELEFONO$':
                        $sostituzione = frontOfficeApp::$cmsHost->getAltriDati("PHONE");
                        if ($sostituzione == "") {
                            $sostituzione = frontOfficeApp::$cmsHost->getAltriDati("TELEPHONE");
                        }
                        break;
                    case '$.IND_SEGNALAZIONE$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Luogo_segnalazione");
                        if (!$sostituzione) {
                            $via = $this->GetValueDatoAggiuntivo($Ricdag_tab, "INTER_VIA");
                            $civico = $this->GetValueDatoAggiuntivo($Ricdag_tab, "INTER_CIVICO");
                            $sostituzione = $via . ", " . $civico;
                        }
                        break;
                    case '$.DESC_SEGNALAZIONE$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Desc_segnalazione");
                        break;
                    case '$.NUMPROT$':
                        $sostituzione = substr($dati['Proric_rec']['RICNPR'], 4);
                        break;
                    case '$.ANNONUMPROT$':
                        $sostituzione = substr($dati['Proric_rec']['RICNPR'], 4) . "/" . substr($dati['Proric_rec']['RICNPR'], 0, 4);
                        break;
                    case '$.DATAPROT$':
                        $sostituzione = substr($dati['Proric_rec']['RICDPR'], 6, 2) . "/" . substr($dati['Proric_rec']['RICDPR'], 4, 2) . "/" . substr($dati['Proric_rec']['RICDPR'], 0, 4);
                        break;
                    case '$.DATAODIERNA$':
                        $sostituzione = date("d/m/Y");
                        break;
                    case '$.DICH_COG_NOM$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_COGNOME_NOME");
                        if ($sostituzione == "") {
                            $cognome = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_COGNOME");
                            $nome = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_NOME");
                            $sostituzione = $cognome . " " . $nome;
                        }
                        break;
                    case '$.OGGETTO_DOMANDA$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "OGGETTO_DOMANDA");
                        break;
                    case '$.ENTETERZO_DENOM$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Denom_EnteTerzo");
                        break;
                    case '$.ENTETERZO_FISCALE$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Fiscale_EnteTerzo");
                        break;
                    case '$.ENTETERZO_PEC$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Pec_EnteTerzo");
                        break;
                    case '$.ENTETERZO_REFERENTE$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "Referente_EnteTerzo");
                        break;
                    case '$.FASCICOLO_NUMERO$':
                        $sostituzione = substr($dati['Propas_rec']['PRONUM'], 4) . "/" . substr($dati['Propas_rec']['PRONUM'], 0, 4);
                        break;
                    case '$.ARTICOLO_TITOLO$':
                        $sostituzione = $dati['Propas_rec']['PROPTIT'];
                        break;
                    case '$.DESC_PARERE$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "descrizioneParere");
                        break;
                    case '$.ISCRITTO_COGNOME$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "ISCRITTO_COGNOME");
                        break;
                    case '$.ISCRITTO_NOME$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "ISCRITTO_NOME");
                        break;
                    case '$.ISCRITTO_SCUOLA$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "PRIMA_SCELTA");
                        break;
                    case '$.PEC_ESIBENTE$':
                        $sostituzione = frontOfficeApp::$cmsHost->getAltriDati('pec');
                        break;
                    case '$.SCUOLA_TRASPORTO$':
                        $sostituzione = $this->getScuolaTrasporto($Ricdag_tab);
                        break;
                    case '$.HOOK_CATEG_LABEL$':
                        $sostituzione = nl2br($this->GetValueDatoAggiuntivo($Ricdag_tab, "HOOK_CATEG_LABEL"));
                        break;
                    case '$.CAP_SEDELEGALE$':
                        if ($DatiSedeLegale['sedeLegale_cap']) {
                            $sostituzione = $DatiSedeLegale['sedeLegale_cap'];
                        }
                        break;
                    case '$.COMUNE_SEDELEGALE$':
                        if ($DatiSedeLegale['sedeLegale_comune']) {
                            $sostituzione = $DatiSedeLegale['sedeLegale_comune'];
                        }
                        break;
                    case '$.ENTE_DESTINATARIO$':
                        if ($DatiSportello['identificativo_suap']) {
                            $sostituzione = $DatiSportello['identificativo_suap'];
                        }
                        break;
                    case '$.COMUNE_DESTINATARIO$':
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, 'COMUNEDESTINATARIO');
                        break;
                }
                $Testo = substr_replace($Testo, $sostituzione, $posizione, $lunghezza);
            }
        }
        return $Testo;
    }

    function GetSostituzioneImpresa($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DenominazioneImpresa");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_RAGIONESOCIALE");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_RAGIONESOCIALE");
                if ($sostituzione == "") {
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_DITTA_RAGIONESOCIALE");
                        if ($sostituzione == "") {
                            $cognome = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_COGNOME");
                            $nome = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_NOME");
                            if ($cognome && $nome) {
                                $sostituzione = $cognome . " " . $nome;
                            } else {
                                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_COGNOME_NOME");
                                if ($sostituzione == "") {
                                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAESEC_RAGIONESOCIALE");
                                    if ($sostituzione == "") {
                                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "ALTRITECNICI_IMPRESA_RAGIONESOCIALE");
                                        if ($sostituzione == "") {
                                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "ALTRISOGESEC_RAGIONESOCIALE");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $sostituzione;
    }

    function GetValueDatoAggiuntivo($Ricdag_tab, $tipo) {
        foreach ($Ricdag_tab as $Ricdag_rec) {
            $value = "";
            if ($Ricdag_rec['DAGTIP'] == $tipo) {
                $value = $Ricdag_rec['RICDAT'];
                break;
            }
        }
        if ($value == "") {
            foreach ($Ricdag_tab as $Ricdag_rec) {
                $value = "";
                if ($Ricdag_rec['DAGKEY'] == $tipo && $Ricdag_rec['RICDAT'] != "") {
                    $value = $Ricdag_rec['RICDAT'];
                    break;
                }
            }
        }
        return $value;
    }

//    function GetValueDatoAggiuntivo($Ricdag_tab, $tipo) {
//        foreach ($Ricdag_tab as $Ricdag_rec) {
//            if ($Ricdag_rec['DAGTIP'] == $tipo) {
//                return $Ricdag_rec['RICDAT'];
//            }
//        }
//    }

    function registraRicdoc($Ricite_rec, $fileAssociato, $fileOriginale, $PRAM_DB, $Metadati = array(), $flServizio = false, $pathAllegati = "", $impostaRiservato = false) {
        $docPrinc = 0;
        $passoRappporto = $this->GetRicite($Ricite_rec['ITECTP'], 'itekey', $PRAM_DB, false, $Ricite_rec['RICNUM']);
        if ($passoRappporto['ITEDRR'] == 1 || $Ricite_rec['ITEIFC'] == 2 || $Ricite_rec['ITEPRI'] == 1) {
            $docPrinc = 1;
        }
        $fileNameAssociato = pathinfo($fileAssociato, PATHINFO_FILENAME);
        $fileNameOriginale = pathinfo($fileOriginale, PATHINFO_FILENAME);
        $ext = pathinfo($fileAssociato, PATHINFO_EXTENSION);
        if (strtolower($ext) == "p7m") {
            $fileNameAssociato = $this->GetRicdocP7MNameAssociato($fileNameAssociato);
            $fileNameOriginale = $this->GetRicdocP7MNameOriginale($fileNameOriginale);
            //Mi trovo l'estensione base del file
            $Est_baseFile = $this->GetBaseExtP7MFile($fileAssociato);
            // Mi trovo e accodo tutte le estensioni p7m
            $Est_tmp = $this->GetExtP7MFile($fileAssociato);
            $posPrimoPunto = strpos($Est_tmp, ".");
            $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
            $p7mExt = str_replace($delEst, "", $Est_tmp);
            //$p7mExt = pathinfo($Est_tmp, PATHINFO_FILENAME);
            //Creo l'estensione finale del file
            $ext = $Est_baseFile . "." . $p7mExt;
        }
        $array_nome = explode("_", $fileNameAssociato);
        $seq = 0;
        $newName = $fileNameOriginale . "_" . $array_nome[1];
        if (isset($array_nome[2])) {
            $newName .= "_" . $array_nome[2];
            $seq = $array_nome[2];
        }
        $newName .= "." . $ext;
        $Ricdoc_rec = array();
        if ($Metadati) { // per SUE
            if ($Metadati['DESTINAZIONE'][0] == "") {
                $Metadati['DESTINAZIONE'] = "";
            }
            $Ricdoc_rec["DOCMETA"] = serialize($Metadati);
        }
        $Ricdoc_rec["DOCNUM"] = $Ricite_rec['RICNUM'];
        $Ricdoc_rec["ITECOD"] = $Ricite_rec['ITECOD'];
        $Ricdoc_rec["ITEKEY"] = $Ricite_rec['ITEKEY'];
        $Ricdoc_rec["DOCUPL"] = $fileAssociato;
        $Ricdoc_rec["DOCSEQ"] = $seq;
        $Ricdoc_rec["DOCNAME"] = utf8_decode($newName);
        $Ricdoc_rec["DOCSHA2"] = hash_file('sha256', $pathAllegati . "/" . $fileAssociato);
        $Ricdoc_rec["DOCPRI"] = $docPrinc;
        $Ricdoc_rec["DOCRIS"] = $impostaRiservato ? 1 : 0;
        if ($flServizio == true)
            $Ricdoc_rec["DOCFLSERVIZIO"] = 1;
        $nRows = ItaDB::DBInsert($PRAM_DB, "RICDOC", 'ROWID', $Ricdoc_rec);
    }

    function cancellaRicdoc($dati, $fileAssociato, $PRAM_DB) {
        foreach ($dati['Ricdoc_tab_tot'] as $Ricdoc_rec) {
            if ($Ricdoc_rec['DOCUPL'] == $fileAssociato) {
                $nRows = ItaDB::DBDelete($PRAM_DB, 'RICDOC', 'ROWID', $Ricdoc_rec['ROWID']);
                return false;
            }
        }
    }

    function getDocName($Pratica, $Docupl, $PRAM_DB) {
        $Ricdoc_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM ='" . $Pratica . "' AND DOCUPL ='" . $Docupl . "'", false);
        if ($Ricdoc_rec) {
            return $Ricdoc_rec['DOCNAME'];
        } else {
            return $docupl;
        }
    }

    function cancellaRichiesta($richiesta, $PRAM_DB) {
//
//Rileggo il numenro della richiesta
//
        $proric_rec = $this->GetProric($richiesta, "codice", $PRAM_DB);
        if ($proric_rec) {
//
//Rileggo tutti i passi da cancallare e li cancello
//
            $Ricite_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $proric_rec['RICNUM'] . "'", true);
            foreach ($Ricite_tab as $key => $Ricite_rec) {
                ItaDB::DBDelete($PRAM_DB, 'RICITE', 'ROWID', $Ricite_rec['ROWID']);
            }
//
//Rileggo i dati aggiuntivi da cancellare e li cancello
//
            $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $proric_rec['RICNUM'] . "'", true);
            foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                ItaDB::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $Ricdag_rec['ROWID']);
            }
//
//Rileggo i dati documenti
//
            $Ricdoc_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM = '" . $proric_rec['RICNUM'] . "'", true);
            foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                ItaDB::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $Ricdag_rec['ROWID']);
            }
//
//Cancello la richiesta
//
            ItaDB::DBDelete($PRAM_DB, 'PRORIC', 'ROWID', $proric_rec['ROWID']);
//
// Cancello le directory del FO interessate
//

            $cartellaAttachment = $this->getCartellaAttachmentPratiche($proric_rec['RICNUM']);
            if (!$this->RemoveDir($cartellaAttachment)) {
                
            }

            $cartellaRepository = $this->getCartellaRepositoryPratiche($proric_rec['RICNUM']);
            if (!$this->RemoveDir($cartellaRepository)) {
                
            }
            $cartellaTemp = $this->getCartellaTemporaryPratiche($proric_rec['RICNUM']);
            if (!$this->RemoveDir($cartellaTemp)) {
                
            }
        }
    }

    function xhtml2Pdf($bodyValue, $dictionaryValues = array(), $outpufile = '') {
        if (!$bodyValue) {
            return false;
        }
        if (!$outputfile) {
            return false;
        }
        $documentPreview = dirname(__FILE__) . "/layoutTemplate.xhtml";
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $contentPreview2 = utf8_encode($itaSmarty->fetch($documentPreview));

//
// Setto le nuove destinazioni
//
        $output_xhtml = pathinfo($outpufile, PATHINFO_DIRNAME) . "/" . pathinfo($outpufile, PATHINFO_BASENAME) . ".xhtml";
        if (!file_put_contents($output_xhtml, $contentPreview2)) {
//Out::msgStop("Errore", "Creazione $output_xhtml Fallita");
            return false;
        }

//
// Preparo il nuovo Comando
//
        $command = ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . '/java/itaH2P/itaH2P.jar ' . $output_xhtml . ' ' . $outpufile;
        passthru($command, $return_var);
        return $outpufile;
    }

    function RemoveDir($dirname) {
// Verifica necessaria
        if (!file_exists($dirname)) {
            return false;
        }
// Cancella un semplice file
        if (is_file($dirname)) {
            return unlink($dirname);
        }
// Loop per le dir
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
// Salta i punti
            if ($entry == '.' || $entry == '..') {
                continue;
            }
// Recursiva
            $this->RemoveDir("$dirname/$entry");
        }
// Chiude tutto
        $dir->close();
        return rmdir($dirname);
    }

    public function Estensioni($ext) {
        $arrayExt = array();
        if ($ext <> '') {
            $ext = str_replace('||', '|', $ext);
            $arrayExt = explode('|', $ext);
        }
        return $arrayExt;
    }

    function searchFile($folder, $srch) {
        $results = array();
        $folder = rtrim($folder, "/") . '/';
        $hd = opendir($folder);
        while (false !== ($file = readdir($hd))) {
            if ($file != '.' && $file != '..') {
                if (preg_match("#$srch#", $file)) {
                    $results[] = $file;
                }
            }
        }
        closedir($hd);
        return $results;
    }

    /**
     * Elabora la stringa $template moltiplicandola n volte se le variabili
     * contenute in essa sono presenti nell'array $dati nel formato 'var_n'.
     * Ignora le chiavi con valore < di $parti_da se quest'ultimo viene definito.
     * 
     * @param string $template
     * @param array $dati
     * @param mixed $parti_da
     * @return string
     */
    function elaboraTemplateDefault($template, $dati, $parti_da = false) {
        $return_array = array();

        /*
         * Estraggo tutte le variabili nel formato $var
         */
        preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_.]*)/', $template, $matches);

        foreach ($matches[1] as $mkey => $match) {
            /*
             * Per ogni match verifico la presenza del dato nell'array $dati
             * (senza limite di livelli)
             */
            $keys = explode('.', $match);

            /*
             * Estraggo l'ultima chiave (la utilizzo in seguito per verificare
             * chiavi nel formato {$var_key}_n)
             */
            $var_key = array_pop($keys);

            $tmp_dati = $dati;

            /*
             * Percorro l'array tramite tutte le $keys tranne l'ultima
             */
            for ($i = 0; $i < count($keys); $i++) {
                if (!isset($tmp_dati[$keys[$i]])) {
                    /*
                     * Se la chiave non esiste, continuo il foreach dei $match
                     */
                    continue 2;
                }

                $tmp_dati = $tmp_dati[$keys[$i]];
            }

            /*
             * Controllo tutte le chiavi all'attuale livello per verificare la
             * presenza di chiavi nel formato key_n
             */
            foreach ($tmp_dati as $key => $value) {
                if (strpos($key, $var_key) === 0 && strlen($key) !== strlen($var_key)) {
                    $idx = substr($key, strlen($var_key));

                    /*
                     * Salto le chiavi che non sono nel formato _n
                     */
                    if (!preg_match('/^_[\d]*$/', $idx)) {
                        continue;
                    }

                    if (!isset($return_array[$idx])) {
                        $return_array[$idx] = $template;
                    }

                    /*
                     * Sostituisco le istanze di $var non seguite da _
                     * con $var_n.
                     * Eseguo il preg_quote per l'escape del carattere $.
                     */
                    $return_array[$idx] = preg_replace('/(' . preg_quote($matches[0][$mkey], '/') . ')(?!_)/', '$1' . $idx, $return_array[$idx]);
                }
            }
        }

        $return_template = count($return_array) ? $return_array : $template;

        /*
         * Rimuovo i passi < di $parti_da
         */
        if (is_array($return_template) && $parti_da !== false) {
            foreach ($return_template as $k => $v) {
                /*
                 * La comparazione è eseguita tra stringhe '_n'...
                 * era inizialmente implementata in questo modo, eventualmente
                 * può essere leggermente modificata
                 */
                if ($k < $parti_da) {
                    unset($return_template[$k]);
                }
            }
        }

        /*
         * Riporto le variabili dal formato '$!var' a '$var'
         * (variabili da non moltiplicare).
         */
        if (is_array($return_template)) {
            foreach ($return_template as $k => &$v) {
                $v = preg_replace('/\$!([a-zA-Z_][a-zA-Z0-9_.]*)/', '$$1', $v);
            }
        } else {
            $return_template = preg_replace('/\$!([a-zA-Z_][a-zA-Z0-9_.]*)/', '$$1', $return_template);
        }

        return $this->valorizzaTemplate($return_template, $dati);
    }

    private function dictionarySimpleToAssociative($dictionaryValues) {
        foreach ($dictionaryValues as $k => $v) {
            if (strpos($k, '.') !== false) {
                $keys = explode('.', $k);
                switch (count($keys)) {
                    case 2:
                        $dictionaryValues[$keys[0]][$keys[1]] = $v;
                        break;

                    case 3:
                        $dictionaryValues[$keys[0]][$keys[1]][$keys[2]] = $v;
                        break;
                }

                unset($dictionaryValues[$k]);
            }
        }

        return $dictionaryValues;
    }

    function valorizzaTemplate($template, $dictionaryValues) {
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaSmarty.class.php");
        $itaSmarty = new itaSmarty();

        $arrDictionary = $this->dictionarySimpleToAssociative($dictionaryValues);

        foreach ($arrDictionary as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $baseFile = md5(rand() * microtime());
        $fileTemplate = $itaSmarty->template_dir . "/" . $baseFile . ".txt";
        file_put_contents($fileTemplate, $template);
        //file_put_contents($fileTemplate, $template);
        //$templateCompilato = utf8_encode($itaSmarty->fetch($fileTemplate));
        $templateCompilato = $itaSmarty->fetch($fileTemplate);
        unlink($fileTemplate);
        return $templateCompilato;
    }

    function elaboraTabelleTemplate($template, $dati, $removeTemplate = false) {
        $dom = new DOMDocument;
//
// Documento template
//
        $template = '<div id="rootTemplate">' . $template . '</div>';
        $ret = $dom->loadHTML($template);
//
// Estraggo tutte le tabelle dal template
//
        $tables = $dom->getElementsByTagName('table');
        foreach ($tables as $table) {
            if ($table->getAttribute('class') !== "ita-table-template") {
                continue;
            }
//
// Estraggo le righe della tabella
//
            $trs = $table->getElementsByTagName('tr');

            foreach ($trs as $tr) {
                if ($tr->getAttribute('class') == 'ita-table-header') {
//                    $printDOM->getElementsByTagName('tbody');
//                    $printDOM->documentElement->appendChild($printDOM->importNode($tr, TRUE));
                    continue;
                }

//
// Preparo i campi multipli in un array
//
                $newGrid = array();
                $tds = $tr->getElementsByTagName('td');
                foreach ($tds as $td) {
// Contenuto della cella
                    $tmpDOM = new DOMDocument();
                    $tmpDOM->appendChild($tmpDOM->importNode($td, TRUE));
                    $nodeValue = utf8_decode($tmpDOM->saveHTML());
                    $tmpDOM = null;
                    $xx = 0;
                    while (true) {
                        $xx += 1;
                        if ($xx == 1000) {
                            break;
                        }

// Parte da sostituire
                        $unit_inner = $this->extract_unit($nodeValue, "@{", "}@");
                        if (!$unit_inner) {
                            break;
                        }
                        $unit = "@{" . $unit_inner . "}@";
                        list($skip, $key0) = explode("$", $unit_inner);
                        list($key1, $key2) = explode(".", $key0);
//
// Trovo tutte le istanze multiple del campo
//

                        foreach ($dati[$key1] as $campo => $valueCampo) {
                            if (strpos($campo, $key2) !== false) {
                                list($skip, $idx) = explode($key2, $campo);

                                /*
                                 * Controllo sull'esistenza di chiavi che potevano essere contenute in altri chiavi. ES:
                                 * INTER_CIV ï¿½ contenuta in INTER_CIV_ESP e veniva fuori una chiave _ESP_01 che poi duplicava la riga sul pdf della raccolta
                                 */
                                $countUnderscore = substr_count($idx, "_");
                                if ($countUnderscore > 1) {
                                    continue;
                                }
                                //
                                $newUnit = '@{$' . $key1 . "." . $key2 . $idx . '}@';
                                if (!$idx) {
                                    //non deve fare nulla quando la riga ï¿½ singola
                                    //per raccolta dati singola
                                    //$newGrid["##"][$unit] = $newUnit;
                                } else {

                                    //per raccolta dati multipla
                                    $newGrid[$idx][$unit] = $newUnit;
                                }
                            }
                        }

                        $nodeValue = str_replace($unit, "", $nodeValue);
                    }
                }
                $trCloned = $tr->cloneNode(TRUE);
                break;
            }

//
// Duplico le righe
//
            if ($removeTemplate) {
                //
                // Rimuovo il tr template non indicizzato
                //
                try {
                    $tr->parentNode->removeChild($tr);
                } catch (Exception $exc) {
                    ob_end_clean();
                    die($exc->getMessage());
                }
            }

            foreach ($newGrid as $key => $newRow) {
                if (!$key) {
                    continue;
                }
                //
                // Prendo la riga base da duplicare
                //
                $tmpDOM = new DOMDocument();
                $tmpDOM->appendChild($tmpDOM->importNode($trCloned, TRUE));
                $stringTR = utf8_decode($tmpDOM->saveHTML());
                $tmpDOM = null;

                foreach ($newRow as $unit => $value) {
                    $stringTR = str_replace($unit, $value, $stringTR);
                }
                $tmpDOM = new DOMDocument();
                $tmpDOM->loadHTML($stringTR);
                $trNode = $tmpDOM->getElementsByTagName('tr')->item(0);
                $tbody = $table->getElementsByTagName('tbody')->item(0);

                $tbody->appendChild($dom->importNode($trNode, TRUE));
            }
        }
        $domTemplate = $dom->getElementsByTagName('div')->item(0);
        $tmpDOM = new DOMDocument();
        $tmpDOM->appendChild($tmpDOM->importNode($domTemplate, TRUE));
        $xmTemplate = $tmpDOM->getElementsByTagName('div')->item(0);
        $returnHtml = utf8_decode($tmpDOM->saveXML($xmTemplate, LIBXML_NOEMPTYTAG));

        /*
         * Fix per duplicazione <br/> causato dal parametro LIBXML_NOEMPTYTAG
         * su funzione DOMDocument->saveXML.
         */
        $returnHtml = str_replace('<br></br>', '<br/>', $returnHtml);
        return $returnHtml;
    }

    function extract_unit($string, $start, $end) {
        $pos = stripos($string, $start);
        $str = substr($string, $pos);
        $str_two = substr($str, strlen($start));
        $second_pos = stripos($str_two, $end);
        $str_three = substr($str_two, 0, $second_pos);
        $unit = trim($str_three); // remove whitespaces
        return $unit;
    }

    function ClearDirectory($folder) {
        $folder = rtrim($folder, "/") . '/';
        $hd = opendir($folder);
        while (false !== ($file = readdir($hd))) {
            if ($file != '.' && $file != '..') {
                unlink($folder . "/" . $file); // delete file
            }
        }
        closedir($hd);
    }

    //@TODO: estrarre codice e descrizione dell'errore per rendere la chiamatra utilizabile come api

    public function filtraPassi($dati, $davedere, $currSeq, $praVar) {
        $Proric_rec = $dati['Proric_rec'];
        $Ricite_tab = $dati['Ricite_tab_da_filtrare'];
        $coppie = array();
        $coppie[] = array('offset' => 0, 'length' => 0);
        $fl_esci = false;
        $salta = false;
        $praVar_domande = unserialize(serialize($praVar));
        foreach ($Ricite_tab as $key => $Ricite_rec) {
            $ret_attivo = false;
            $PassoFatto = false;
            if ($salta === $Ricite_rec['ITEKEY']) {
                $salta = false;
                $coppie[] = array('offset' => $key, 'length' => 0);
            }
            if ($salta === false) {
                if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) !== false) {
                    $PassoFatto = true;
                }
                if ($Ricite_rec['ITEATE']) {
                    $ret_attivo = $this->ctrExpression($Ricite_rec, $praVar_domande->getVariabiliRichiesta()->getAlldataPlain("", "."), 'ITEATE');
                    if ($ret_attivo === true) {
                        if ($PassoFatto !== false) {
                            $praVar_domande->addVariabiliCampiAggiuntiviRichiesta($Ricite_tab[$key]['ITESEQ']);
                            $praVar_domande->addVariabiliTipiAggiuntiviRichiesta($Ricite_tab[$key]['ITESEQ']);
                        }
                    }
                } else {
                    $ret_attivo = true;
                    if ($PassoFatto !== false && $Ricite_rec['CLTOFF'] == 0) {
                        $praVar_domande->addVariabiliCampiAggiuntiviRichiesta($Ricite_tab[$key]['ITESEQ']);
                        $praVar_domande->addVariabiliTipiAggiuntiviRichiesta($Ricite_tab[$key]['ITESEQ']);
                    }
                }
                //if ($Ricite_rec['ITEQSTDAG'] == 1 && $Ricite_rec['RICQSTRIS'] == 0) {
                //if ($Ricite_rec['ITEQSTDAG'] == 1 && !$PassoFatto && $Ricite_rec['CLTOFF'] == 0) {
                //if (($Ricite_rec['ITEQSTDAG'] == 1 && !$PassoFatto && $Ricite_rec['CLTOFF'] == 0) || ($Ricite_rec['ITEIRE'] == 1 && $Ricite_rec['ITEQSTDAG'] == 1)) {
                if (($Ricite_rec['ITEQSTDAG'] == 1 && !$PassoFatto && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) || ($Ricite_rec['ITEIRE'] == 1 && $Ricite_rec['ITEQSTDAG'] == 1 && $ret_attivo == true)) {
                    $coppie[count($coppie) - 1]['length'] += 1;
                    $fl_esci = true;
                    break;
                }

                if ($Ricite_rec['ITEQST'] == 1 && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) {
                    switch ($Ricite_rec['RCIRIS']) {
                        case 'SI' :
                            $coppie[count($coppie) - 1]['length'] += 1;
                            if ($Ricite_rec['ITEVPA']) {
                                $salta = $Ricite_rec['ITEVPA'];
                            }
                            break;
                        case 'NO' :
                            $coppie[count($coppie) - 1]['length'] += 1;
                            if ($Ricite_rec['ITEVPN']) {
                                $salta = $Ricite_rec['ITEVPN'];
                            }
                            break;
                        default:
//                            //$coppie[count($coppie) - 1]['length'] +=1;
//                            if ($Ricite_rec['ITEATE']) {
//                                $coppie[count($coppie) - 1]['length'] += 1;
//                            } else {
//                                $coppie[count($coppie) - 1]['length'] +=1;
//                                $fl_esci = true;
//                            }
                            $coppie[count($coppie) - 1]['length'] += 1;
                            if ($ret_attivo) {
                                $fl_esci = true;
                            }
                            break;
                    }
                    if ($fl_esci == true) {
                        break;
                    }
                } else {
                    if ($Ricite_rec['ITEVPA'] && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) {
                        $coppie[count($coppie) - 1]['length'] += 1;
                        $salta = $Ricite_rec['ITEVPA'];
                    } else {
                        $coppie[count($coppie) - 1]['length'] += 1;
                    }
                }
            }
        }

        //
        $praVar_domande = null;
        //
        $Ricite_tab_new = array();
        foreach ($coppie as $value) {
            $Ricite_tab_new = array_merge($Ricite_tab_new, array_slice($Ricite_tab, $value['offset'], $value['length'], false));
        }

        /*
         * Controllo passi di chiusura intermedi
         */
        $keyPassoFinale = false;
        foreach ($Ricite_tab_new as $keyPasso => $ricite_rec) {
            if ($keyPassoFinale && $keyPassoFinale < $keyPasso) {
                unset($Ricite_tab_new[$keyPasso]);
                continue;
            }

            if ($ricite_rec['ITEZIP'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }

            if ($ricite_rec['ITEIRE'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }

            /*
             * Finalizzazione su passo incorporazione a pratica unica
             */
            if ($ricite_rec['ITERICSUB'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }
        }

        $Ricite_tab_appoggio = array();

//
// Carico dizionario variabili base
//
        foreach ($Ricite_tab_new as $key1 => $Ricite_rec_new) {
            //if ($Ricite_rec_new['CLTOFF'] == 1 || $Ricite_rec_new['ITEFILE'] == 1) {
            if ($Ricite_rec_new['CLTOFF'] == 1) {
                continue;
            }
            if ($Ricite_rec_new['ITEATE']) {
                $ret = $this->ctrExpression($Ricite_rec_new, $praVar->getVariabiliRichiesta()->getAlldataPlain("", "."), 'ITEATE');
                if ($ret === true) {
                    $Ricite_tab_appoggio[] = $Ricite_tab_new[$key1];
                    if (strpos($Proric_rec['RICSEQ'], "." . $Ricite_rec_new['ITESEQ'] . ".") !== false) {
                        $praVar->addVariabiliCampiAggiuntiviRichiesta($Ricite_tab_new[$key1]['ITESEQ']);
                        $praVar->addVariabiliTipiAggiuntiviRichiesta($Ricite_tab_new[$key1]['ITESEQ']);
                    }
                }
            } else {
                $Ricite_tab_appoggio[] = $Ricite_tab_new[$key1];
                if (strpos($Proric_rec['RICSEQ'], "." . $Ricite_rec_new['ITESEQ'] . ".") !== false) {
                    $praVar->addVariabiliCampiAggiuntiviRichiesta($Ricite_tab_new[$key1]['ITESEQ']);
                    $praVar->addVariabiliTipiAggiuntiviRichiesta($Ricite_tab_new[$key1]['ITESEQ']);
                }
            }
        }


        $Ricite_tab_new = $Ricite_tab_appoggio;

        /*
         * Se ci sono passi speciali per accorpate e
         * la richiesta è un'accorpata, nascondo i passi.
         */

        if ($Proric_rec['RICRUN']) {
            $newCurrSeq = false;

            foreach ($Ricite_tab_new as $keyPasso => $ricite_rec) {
                if ($newCurrSeq === true) {
                    $newCurrSeq = $ricite_rec['ITESEQ'];
                }

                if (
                        $ricite_rec['CLTOPEFO'] == praLibStandardExit::FUN_FO_PASSO_ACC_DOMANDA ||
                        $ricite_rec['CLTOPEFO'] == praLibStandardExit::FUN_FO_PASSO_ACC_SCELTA
                ) {
                    if ($currSeq == $ricite_rec['ITESEQ'] || ($newCurrSeq !== true && $newCurrSeq && $newCurrSeq == $ricite_rec['ITESEQ'])) {
                        $newCurrSeq = true;
                    }

                    unset($Ricite_tab_new[$keyPasso]);
                }
            }

            if ($newCurrSeq !== true && $newCurrSeq) {
                $currSeq = $newCurrSeq;
            }
        }

        $Ricite_tab_new = array_values($Ricite_tab_new);

        $tot = count($Ricite_tab_new);
        $spazio = intval($davedere / 2);
        $p = false;

        if (!$currSeq) {
            $currSeq = $Ricite_tab_new[0]['ITESEQ'];
        }

        foreach ($Ricite_tab_new as $Key => $Ricite_rec) {
            if ($Ricite_rec['ITESEQ'] == $currSeq) {
                if ($p == false) {
                    $p = $Key;
                }
            }
        }

        if ($p !== false) {
            $fin = ($p + $spazio >= $tot) ? $tot - 1 : (($p + $spazio < $davedere - 1) ? $davedere - 1 : $p + $spazio);
            $ini = $fin + 1 - $davedere;
            if ($ini < 0)
                $ini = 0;
            $per = $davedere;
            $next = ($p == $tot - 1) ? false : true;
            $prev = ($p == 0) ? false : true;

            return array(
                'Ricite_tab' => array_slice($Ricite_tab_new, $ini, $per, true),
                'Ricite_tab_new' => $Ricite_tab_new,
                'Dizionario_Richiesta_new' => $praVar->getVariabiliRichiesta(),
                'Posizione' => $p,
                'Quanti' => $tot,
                'Inizio' => $ini,
                'Fine' => $fin,
                'Davedere' => $davedere,
                'Successivo' => $next,
                'Precedente' => $prev
            );
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile andare al passo " . $currSeq . " della pratica n. " . $Ricite_rec['RICNUM']);
            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0021', "Impossibile andare al passo " . $currSeq . " della pratica n. " . $Ricite_rec['RICNUM'], __CLASS__);
            return false;
        }
    }

    function ctrRicdagRec($Ricdag_rec, $plainDict) {
        if ($Ricdag_rec['DAGEXPROUT']) {
            $exprOut_tab = unserialize($Ricdag_rec['DAGEXPROUT']);
            foreach ($exprOut_tab as $exprOut_rec) {
                $ret = $this->evalExpression($plainDict, $exprOut_rec['EXPCTR']);
                if ($ret == true) {
                    $Ricdag_rec['DAGDIZ'] = $exprOut_rec['ITDDIZ'];
                    $Ricdag_rec['DAGVAL'] = $exprOut_rec['ITDVAL'];
                    $Ricdag_rec['DAGROL'] = $exprOut_rec['ITDROL'];
                    //
                    // Nuove variabili
                    //
                    $Ricdag_rec['DAGLAB'] = $exprOut_rec['ITDLAB'];
                    $Ricdag_rec['DAGTIC'] = $exprOut_rec['ITDTIC'];
                    $Ricdag_rec['DAGVCA'] = $exprOut_rec['ITDVCA'];
                    $Ricdag_rec['DAGREV'] = $exprOut_rec['ITDREV'];
                    $Ricdag_rec['DAGLEN'] = $exprOut_rec['ITDLEN'];
                    $Ricdag_rec['DAGDIM'] = $exprOut_rec['ITDDIM'];
                    $Ricdag_rec['DAGACA'] = $exprOut_rec['ITDACA'];
                    $Ricdag_rec['DAGPOS'] = $exprOut_rec['ITDPOS'];
                    $Ricdag_rec['DAGLABSTYLE'] = $exprOut_rec['ITDLABSTYLE'];
                    $Ricdag_rec['DAGFIELDSTYLE'] = $exprOut_rec['ITDFIELDSTYLE'];
                    $Ricdag_rec['DAGFIELDCLASS'] = $exprOut_rec['ITDFIELDCLASS'];
                    $Ricdag_rec['DAGMETA'] = serialize($exprOut_rec['ITDMETA']);
                    break;
                }
            }
        }
        return $Ricdag_rec;
    }

    function CheckValiditaPasso($Riccontrolli_tab, $raccolta) {
        $arrayEsiti = array();
        foreach ($Riccontrolli_tab as $Riccontrolli_rec) {
            $ret = $this->evalExpression($raccolta, $Riccontrolli_rec['ESPRESSIONE']);
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['ESITO'] = $ret;
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['MESSAGGIO'] = $Riccontrolli_rec['MESSAGGIO'];
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['AZIONE'] = $Riccontrolli_rec['AZIONE'];
            if ($ret === false && $Riccontrolli_rec['AZIONE'] == 2) {
                break;
            }
        }
        $msg = "";
        foreach ($arrayEsiti as $esito) {
            if ($esito['ESITO'] === false) {
                $msg .= $esito['MESSAGGIO'] . "<br>";
            }
        }
        return $msg;
    }

    /**
     * Analizza una espressione
     * 
     * @param type $ricite_rec
     * @param type $raccolta
     * @param type $campoEspressione
     * @return type
     */
    function ctrExpression($ricite_rec, $raccolta, $campoEspressione = 'ITEATE') {
        return $this->evalExpression($raccolta, $ricite_rec[$campoEspressione]);
    }

    function evalExpression($raccolta, $serExpression, $_internal = false) {
        $espressione = '';
        $controlli = unserialize($serExpression);
        if (!$controlli) {
            return true;
        }

        if (!$_internal) {
            $arrEspressioni = array();
            foreach ($controlli as $controllo) {
                if (isset($controllo['TIPOCAMPO']) && $controllo['TIPOCAMPO'] === 'D') {
                    foreach ($raccolta as $k => $v) {
                        if (strpos($k, $controllo['CAMPO']) === 0 && strlen($k) !== strlen($controllo['CAMPO'])) {
                            $idx = substr($k, strlen($controllo['CAMPO']) + 1);
                            if (is_numeric($idx) && !isset($arrEspressioni[$idx])) {
                                $arrEspressioni[$idx] = $controlli;
                            }
                        }
                    }
                }

                if (isset($controllo['TIPOVALORE']) && $controllo['TIPOVALORE'] === 'D') {
                    foreach ($raccolta as $k => $v) {
                        if (strpos($k, $controllo['VALORE']) === 0 && strlen($k) !== strlen($controllo['VALORE'])) {
                            $idx = substr($k, strlen($controllo['VALORE']) + 1);
                            if (is_numeric($idx) && !isset($arrEspressioni[$idx])) {
                                $arrEspressioni[$idx] = $controlli;
                            }
                        }
                    }
                }
            }

            if (count($arrEspressioni)) {
                $ret = true;

                foreach ($arrEspressioni as $idx => $arrControlli) {
                    foreach ($arrControlli as $k => $arrControllo) {
                        if (isset($arrControllo['TIPOCAMPO']) && $arrControllo['TIPOCAMPO'] === 'D' && isset($raccolta[$arrControllo['CAMPO'] . '_' . $idx])) {
                            $arrControlli[$k]['CAMPO'] = $arrControllo['CAMPO'] . '_' . $idx;
                        }

                        if (isset($arrControllo['TIPOVALORE']) && $arrControllo['TIPOVALORE'] === 'D' && isset($raccolta[$arrControllo['VALORE'] . '_' . $idx])) {
                            $arrControlli[$k]['VALORE'] = $arrControllo['VALORE'] . '_' . $idx;
                        }
                    }

                    $ret = $ret && $this->evalExpression($raccolta, serialize($arrControlli), true);
                }

                return $ret;
            }
        }

        foreach ($controlli as $controllo) {
            switch ($controllo['OPERATORE']) {
                case 'AND':
                    $espressione = $espressione . ' && ';
                    break;
                case 'OR':
                    $espressione = $espressione . ' || ';
                    break;
                default:
                    break;
            }

            /*
             * Nuova tipologia di condizione
             */
            if (isset($controllo['TIPOCAMPO']) && isset($controllo['TIPOVALORE'])) {
                $valore1 = $valore2 = '';

                switch ($controllo['TIPOCAMPO']) {
                    case 'V':
                        $valore1 = "'" . addslashes($controllo['CAMPO']) . "'";
                        break;

                    case 'D':
                        $valore1 = "\$raccolta['" . $controllo['CAMPO'] . "']";
                        break;

                    case 'C':
                        $valore1 = $controllo['CAMPO'];
                        break;

                    case 'T':
                        $valore1 = $this->valorizzaTemplate($controllo['CAMPO'], $raccolta) ? 'true' : 'false';
                        break;
                }

                switch ($controllo['TIPOVALORE']) {
                    case 'V':
                        $valore2 = "'" . addslashes($controllo['VALORE']) . "'";
                        break;

                    case 'D':
                        $valore2 = "\$raccolta['" . $controllo['VALORE'] . "']";
                        break;

                    case 'C':
                        $valore2 = $controllo['VALORE'];
                        break;
                }

                $espressione = $espressione . "$valore1 {$controllo['CONDIZIONE']} $valore2";
                continue;
            }

            if (substr($controllo['CAMPO'], 0, 1) === '#') {
                $controllo['CAMPO'] = substr($controllo['CAMPO'], 1);
                $espressione = $espressione . $controllo['CAMPO'];
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . $controllo['VALORE'];
            } else {
                $espressione = $espressione . '$raccolta[\'' . $controllo['CAMPO'] . '\']';
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . '\'' . $controllo['VALORE'] . '\'';
            }
        }

        $espressione = $espressione . '';
        eval('$ret = (' . $espressione . ');');
        return $ret;
    }

    public function CreaPdfDistinta($dati, $PassoDistinta) {
        $baseFile = $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($PassoDistinta['ITESEQ'], $dati['seqlen'], "0", STR_PAD_LEFT);
        $resultFile = $dati['CartellaAllegati'] . '/' . $baseFile . '.pdf';
        $praVar = new praVars();
        $praVar->setPRAM_DB($dati['PRAM_DB']);
        $praVar->setDati($dati);
        $praVar->loadVariabiliDistinta($PassoDistinta);
        $Dizionario = $praVar->getVariabiliDistinta();
        if (isset($PassoDistinta['ITEMETA'])) {
            $metadati = unserialize($PassoDistinta['ITEMETA']);
            $template = $metadati['TESTOBASEDISTINTA'];
        }
        $dictionaryValues_pre = $Dizionario->getAllData();

        $dictionaryValues = $this->htmlspecialchars_recursive($dictionaryValues_pre);

        $template = $this->elaboraTabelleTemplate($template, $dictionaryValues, true);

        require_once(ITA_LIB_PATH . "/itaPHPCore/itaSmarty.class.php");
        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $template);
        $documentLayout = $itaSmarty->template_dir . "/" . $baseFile . "_documentlayout.xhtml";
        $layoutTemplate = ITA_PRATICHE_PATH . "/PRATICHE_italsoft/layoutTemplate.xhtml";
        if (!file_exists($layoutTemplate)) {
            $this->errCode = -1;
            $this->errMessage = "File layoutTemplate '$layoutTemplate' non trovato.";
            return false;
        }
        @copy($layoutTemplate, $documentLayout);
        $contentPreview = $itaSmarty->fetch($documentLayout);
        unlink($documentLayout);

        $documentPreview = $itaSmarty->template_dir . "/" . $baseFile . "_documentpreview.xhtml";
        file_put_contents($documentPreview, $contentPreview);

        foreach ($dictionaryValues as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }

        $contentPreview2 = utf8_encode($itaSmarty->fetch($documentPreview));
        unlink($documentPreview);
        $documentPreview2 = $itaSmarty->template_dir . "/" . $baseFile . '_documentpreview2.xhtml';
        $pdfPreview = $itaSmarty->template_dir . "/" . $baseFile . '.pdf';
        file_put_contents($documentPreview2, $contentPreview2);
        passthru(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . '/java/itaH2P/itaH2P.jar ' . $documentPreview2 . ' ' . $pdfPreview, $return_var);
        unlink($documentPreview2);
        if ($return_var == 0) {
            if (!@rename($pdfPreview, $resultFile)) {
                $this->errCode = -1;
                $this->errMessage = "Errore in spostamento file distinta da '$pdfPreview' a '$resultFile'.";
                return false;
            }
        }
        return $resultFile;
    }

    /**
     * Crea Mappa dei passi rapport con i files da accorpare e indica quali sono obbligatori o meno
     * 
     * @param type $dati
     * @param type $ricite_rapporto
     * @return array
     */
    public function ControllaRapportoConfig($dati, $ricite_rapporto) {
        //
        // Definsco il tipo da includere nle rapporto completo in funzione del tipo di rapporto:
        // SE ITEZIP = 1 tipo rapporto file comunica
        // SE ITEDRR = 1 tipo rapporto pdf da firmare 
        //
        if ($ricite_rapporto['ITEZIP']) {
            $confronta = 'ITEIFC';
        } else if ($ricite_rapporto['ITEDRR']) {
            $confronta = 'ITEIDR';
        } else if ($ricite_rapporto['ITERICUNI']) {
            $confronta = 'ITEIDR';
        } else {
            $confronta = 'ITEIDR';
        }

        //
        //Se c'è passo distinta che va nel rapporto la aggiorno per sicurezza
        //
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITEDIS'] == 1) {
                $PassoDistinta = $ricite_rec;
                if ($PassoDistinta['ITEIDR'] == 1) {
                    $resultFile = $this->CreaPdfDistinta($dati, $PassoDistinta);
                    if ($resultFile == false) {
                        return false;
                    }
                }
                //break;
            }
        }

        //
        // Creo nuovo array configurazione rapporto già filtrato per tipo rapporto
        //
        $RapportoLayout = array();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec["CLTOFF"] == 0 && $ricite_rec[$confronta] != 0) {
                $chiaveLayout = str_pad($ricite_rec['ITECOMPSEQ'], $dati['seqlen'], "0", STR_PAD_LEFT);
                $chiaveLayout .= str_pad($ricite_rec['ITESEQ'], $dati['seqlen'], "0", STR_PAD_LEFT);
                $RapportoLayout[$chiaveLayout] = $ricite_rec;
            }
        }

        /*
         * Cerco se nel passo rapporto completo è presente un testo associato DOCX.
         * Se presente, genero il PDF e lo aggiungo alla lista dei file del rapporto
         * completo.
         */
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITEDRR'] == 1 && $ricite_rec['ITEWRD'] && strtolower(pathinfo($ricite_rec['ITEWRD'], PATHINFO_EXTENSION)) == 'docx') {
                $filepathDistinta = $this->CreaPdfDistintaDocx($dati, $ricite_rec);
                if (!$filepathDistinta) {
                    return false;
                }

                $chiaveLayout = str_pad($ricite_rec['ITECOMPSEQ'], $dati['seqlen'], '0', STR_PAD_LEFT);
                $chiaveLayout .= str_pad($ricite_rec['ITESEQ'], $dati['seqlen'], '0', STR_PAD_LEFT);
                $RapportoLayout[$chiaveLayout] = $ricite_rec;
            }
        }

        ksort($RapportoLayout);
        $arrayPdfDest = $arraySchemaRapporto = array();
        $arrayPdfSourceTmp = $this->GetFileList($dati['CartellaAllegati']);
        $arrayPdfSource = $this->array_sort($arrayPdfSourceTmp, "FILENAME");
        foreach ($RapportoLayout as $ricite_rec) {
            $seq = str_repeat("0", $dati['seqlen'] - strlen($ricite_rec['ITESEQ'])) . $ricite_rec['ITESEQ'];
            $trovato = 0;

            /*
             * Testo associato su rapporto completo (al momento DOCX)
             */
            if ($ricite_rec['ITEDRR'] == 1 && $ricite_rec['ITEWRD'] && strtolower(pathinfo($ricite_rec['ITEWRD'], PATHINFO_EXTENSION)) == 'docx') {
                $newIndice = count($arrayPdfDest);
                $arrayPdfDest[$newIndice] = array(
                    'FILEPATH' => $filepathDistinta,
                    'FILENAME' => basename($filepathDistinta),
                    'COMPFLAG' => $ricite_rec['ITECOMPFLAG'],
                    'ITEOBL' => $ricite_rec['ITEOBL'],
                    'RICOBL' => $ricite_rec['RICOBL'],
                    'ITEOBE' => $ricite_rec['ITEOBE']
                );

                continue;
            }

            if (strpos($dati['Proric_rec']['RICSEQ'], "." . $ricite_rec['ITESEQ'] . ".") !== false) {
                foreach ($arrayPdfSource as $key => $file) {
                    if (strpos($file['FILENAME'], $dati['Proric_rec']['RICNUM'] . '_C' . $seq) !== false && strpos($file['FILENAME'], frontOfficeApp::getEnte() . "_rapporto") === false) {
                        /*
                         * Rimuovo dalla tab il file AUTOCERTIFICAZIONE_ACCORPA
                         */
                        if ($ricite_rec['ITERICUNI'] == '1') {
                            $Ricdoc_rec = $this->GetRicdoc($file['FILENAME'], 'codice', $dati['PRAM_DB']);
                            $metaData = unserialize($Ricdoc_rec['DOCMETA']);
                            if ($metaData['AUTOCERTIFICAZIONE_ACCORPATA']) {
                                continue;
                            }
                        }

                        if (strtolower(pathinfo($file['FILEPATH'], PATHINFO_EXTENSION)) == 'pdf') {
                            $newIndice = count($arrayPdfDest);
                            $arrayPdfDest[$newIndice] = $arrayPdfSource[$key];
                            $arrayPdfDest[$newIndice]['COMPFLAG'] = $ricite_rec['ITECOMPFLAG'];
                            $arrayPdfDest[$newIndice]['ITEOBL'] = $ricite_rec['ITEOBL'];
                            $arrayPdfDest[$newIndice]['RICOBL'] = $ricite_rec['RICOBL'];
                            $arrayPdfDest[$newIndice]['ITEOBE'] = $ricite_rec['ITEOBE'];
                            $trovato = $trovato + 1;
                        }
                    }
                }
            }
            if ($trovato == 0) {
                $newIndice = count($arrayPdfDest);
                $arrayPdfDest[$newIndice]['rowid'] = 0;
                $arrayPdfDest[$newIndice]['FILEPATH'] = '';
                $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_C' . $seq . '.pdf';
                $arrayPdfDest[$newIndice]['COMPFLAG'] = $ricite_rec['ITECOMPFLAG'];
                $arrayPdfDest[$newIndice]['ITEOBL'] = $ricite_rec['ITEOBL'];
                $arrayPdfDest[$newIndice]['RICOBL'] = $ricite_rec['RICOBL'];
                $arrayPdfDest[$newIndice]['ITEOBE'] = $ricite_rec['ITEOBE'];
            }
        }
        ksort($arrayPdfDest);

        return $arrayPdfDest;
    }

    public function runCallback($dati, $evento) {
        if (defined('ITA_CALLBACK_PATH') && file_exists(ITA_CALLBACK_PATH)) {
            require_once(ITA_CALLBACK_PATH);
            ita_suap_Callback::run($dati, $evento);
        }
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

    public function GetPassiDefaultDipendenze($PRAM_DB, $ricnum, $seq, $itekey) {
        /*
         * Query che vede le dipendenze nei valori di default delle raccolte
         */
        $sql = "SELECT
                    RICITECHILD.RICNUM,
                    RICITECHILD.ITEKEY,
                    RICITECHILD.ITEDES,
                    RICITECHILD.ITEDAT,
                    RICITECHILD.ITEUPL,
                    RICITECHILD.ITEMLT,
                    RICITECHILD.ITEDOW,
                    RICITECHILD.ITESEQ
                FROM
                    RICDAG RICDAGPARENT
                LEFT OUTER JOIN 
                    RICITE RICITECHILD 
                ON  
                    RICITECHILD.RICNUM=RICDAGPARENT.DAGNUM AND
                    RICITECHILD.ITESEQ > $seq AND
                   (RICITECHILD.ITEDOW = 1 OR RICITECHILD.ITEDAT=1)     
                LEFT OUTER JOIN
                    RICDAG RICDAGCHILD 
                ON  
                    RICDAGCHILD.DAGNUM=RICDAGPARENT.DAGNUM AND
                    RICDAGCHILD.ITEKEY=RICITECHILD.ITEKEY AND
                    (
                        RICDAGCHILD.DAGDIZ = 'D' AND RICDAGCHILD.DAGVAL = CONCAT('PRAAGGIUNTIVI.',RICDAGPARENT.DAGKEY) OR
                        RICDAGCHILD.DAGDIZ = 'T' AND RICDAGCHILD.DAGVAL LIKE CONCAT('%@{','$', 'PRAAGGIUNTIVI.',RICDAGPARENT.DAGKEY,'}@%') OR
                        RICDAGCHILD.DAGCTR LIKE CONCAT('%',RICDAGPARENT.DAGKEY,'%') OR
                        RICDAGCHILD.DAGEXPROUT LIKE CONCAT('%',RICDAGPARENT.DAGKEY,'%')
                    )   
                WHERE
                    RICDAGPARENT.DAGNUM='$ricnum' AND RICDAGPARENT.ITEKEY = '$itekey' AND RICDAGCHILD.DAGVAL IS NOT NULL
                GROUP BY 
                    RICITECHILD.ITEKEY
                ORDER BY
                    RICITECHILD.ITESEQ";
        $Passi_dipendenze1 = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        //$Passi_dipendenze = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        /*
         * Query che vede le dipendenze nell'XHTML delle raccolte
         */
        $sql2 = "SELECT 
                    DISTINCT SELEZIONE.RICNUM, SELEZIONE.ITEKEY, SELEZIONE.ITEDES, SELEZIONE.ITEDAT, SELEZIONE.ITESEQ
                FROM (
                    SELECT 
                        RICDAG.DAGKEY,
                        RICITE.ITEKEY,
                        RICITE.ITEDES,
                        RICITE.ITEDAT,
                        RICITE.ITEMETA,
                        RICITE.ITESEQ,
                        RICITE.RICNUM
                    FROM RICDAG
                    LEFT OUTER JOIN PRORIC PRORIC ON RICDAG.DAGNUM=PRORIC.RICNUM
                    LEFT OUTER JOIN RICITE RICITE ON RICDAG.DAGNUM=RICITE.RICNUM AND RICITE.ITEPUB=1 AND RICITE.ITEDAT=1 AND RICITE.ITEMETA<>'' AND RICITE.ITESEQ > $seq 
                    WHERE
                        PRORIC.RICSEQ LIKE CONCAT('%.' , RICITE.ITESEQ , '.%') AND
                        RICDAG.DAGNUM='$ricnum' AND 
                        RICDAG.ITEKEY='$itekey' AND
                        RICDAG.DAGTIC<>'RadioButton' AND RICDAG.DAGTIC<>'Html' AND
                        RICITE.ITEMETA LIKE CONCAT('%',RICDAG.DAGKEY,'%')
                    ) SELEZIONE";
        $Passi_dipendenze2 = ItaDB::DBSQLSelect($PRAM_DB, $sql2, true);

        $Passi_dipendenze = array_merge($Passi_dipendenze1, $Passi_dipendenze2);


        foreach ($Passi_dipendenze as $key => $Passo) {
            $Ricite_rec_upl = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM='$ricnum' AND ITECTP='" . $Passo['ITEKEY'] . "'", false);
            if ($Ricite_rec_upl) {
                $Passi_dipendenze[$key]['Ricite_rec_upl'] = $Ricite_rec_upl;
                $Ricdoc_tab = $this->GetRicdoc($Ricite_rec_upl['ITEKEY'], 'itekey', $PRAM_DB, true, $ricnum);
                if ($Ricdoc_tab) {
                    $Passi_dipendenze[$key]['RICDOC'] = $Ricdoc_tab;
                }
            } else {// aggiunto else per le raccolte dati che hanno lo stesso il RICDOC
                $Ricdoc_tab = $this->GetRicdoc($Passo['ITEKEY'], 'itekey', $PRAM_DB, true, $ricnum);
                if ($Ricdoc_tab) {
                    $Passi_dipendenze[$key]['RICDOC'] = $Ricdoc_tab;
                }
            }
        }
        return $Passi_dipendenze;
    }

    public function AnnullaPasso($PRAM_DB, $seq, $proric_rec) {
        if ($seq == "") {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0069', "Impossibile annullare il passo della richiesta " . $proric_rec['RICNUM'] . ".<br>Sequenza non trovata<br>" . print_r($_POST, true), __CLASS__);
            return false;
        }
        $NewSequenza_pre = str_replace(chr(46) . $seq . chr(46), "", $proric_rec['RICSEQ']);
        $NewSequenza = preg_replace('/\s+/', '', $NewSequenza_pre);
        $proric_rec['RICSEQ'] = $NewSequenza;
        try {
            $nRows = ItaDB::DBUpdate($PRAM_DB, "PRORIC", 'ROWID', $proric_rec);
            if ($nRows == -1) {
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
        return $proric_rec;
    }

    public function CancellaUpload($PRAM_DB, &$dati, $Ricite_rec_upl, $Allegato) {
        $CancAllegato = pathinfo($Allegato, PATHINFO_BASENAME);
        $ext = pathinfo($CancAllegato, PATHINFO_EXTENSION);
        if (strtolower($ext) == 'pdf') {
            $fileINFO = pathinfo($CancAllegato, PATHINFO_FILENAME) . '.info';
            if (file_exists($dati['CartellaAllegati'] . "/" . $fileINFO)) {
                @unlink($dati['CartellaAllegati'] . "/" . $fileINFO);
            }
        }
        if (strtolower($ext) == 'p7m') {
            //Cancello il file sbustato se c'ï¿½ e relativo file INFO e relativi campi aggiuntivi
//            $pdfFileName = pathinfo($CancAllegato, PATHINFO_FILENAME);
            $pdfFileName = $this->GetP7MFileContentName($Allegato);
            if (file_exists($dati['CartellaAllegati'] . "/" . $pdfFileName)) {
                if (@unlink($dati['CartellaAllegati'] . "/" . $pdfFileName)) {
                    $this->cancellaRicdoc($dati, $pdfFileName, $PRAM_DB);
                    $Ricdag_tab_upl = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND 
                                                            ITEKEY = '" . $Ricite_rec_upl['ITEKEY'] . "'", true);
                    foreach ($Ricdag_tab_upl as $Ricdag_rec) {
                        if ($Ricdag_rec['DAGSET'] == pathinfo($pdfFileName, PATHINFO_FILENAME)) {
                            $nRows = ItaDB::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $Ricdag_rec['ROWID']);
                        }
                    }
                    $fileINFO = pathinfo($pdfFileName, PATHINFO_FILENAME) . '.info';
                    if (file_exists($dati['CartellaAllegati'] . "/" . $fileINFO)) {
                        @unlink($dati['CartellaAllegati'] . "/" . $fileINFO);
                    }
                }
            }
        }

        $fileErr = $CancAllegato . '.err';
        if ($Ricite_rec_upl['RICERF'] == 1 && file_exists($dati['CartellaAllegati'] . "/" . $fileErr)) {
            $Ricite_rec_upl['RICERF'] = 0;
            $Ricite_rec_upl['RICERM'] = '';
        }
        if ($Ricite_rec_upl['RICOBL'] == 1) {
            $Ricite_rec_upl['RICOBL'] = 0;
        }
        $Ricite_rec_upl['RICQSTRIS'] = 0;
        $nRows = ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $Ricite_rec_upl);
        @unlink($dati['CartellaAllegati'] . "/" . $fileErr);

        $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($Ricite_rec_upl['ITESEQ'])) . $Ricite_rec_upl['ITESEQ'];
        $results = $this->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo);
        if (@unlink($dati['CartellaAllegati'] . "/" . $CancAllegato)) {
            $this->cancellaRicdoc($dati, $CancAllegato, $PRAM_DB);
            $Ricdag_tab_upl = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND 
                                                            ITEKEY = '" . $Ricite_rec_upl['ITEKEY'] . "'", true);
            foreach ($Ricdag_tab_upl as $Ricdag_rec) {
                if ($Ricdag_rec['DAGSET'] == pathinfo($CancAllegato, PATHINFO_FILENAME)) {
                    $nRows = ItaDB::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $Ricdag_rec['ROWID']);
                }
            }


            //Allineamento Soggetti
            $praLibAcl = new praLibAcl();
            foreach (praLibAcl::$CURRENT_ROLES as $ruolo) {
                if (!$praLibAcl->sincronizzaSoggetto($dati, $ruolo, $this->praErr)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0099', $praLibAcl->getErrMessage() . " Pratica N. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                    return false;
                }
            }


            // MOMENTANEAMENTE SOSPESO PERCHE NON UTILIZZATO DA APPROFONDIRE
            //$Passo_distinta = $this->GetRicite($Ricite_rec_upl['ITEKEY'], "itectp", $PRAM_DB, false, $dati['Proric_rec']['RICNUM']);
            if (count($results) == 1) {
                $ret_annulla = $this->AnnullaPasso($PRAM_DB, $Ricite_rec_upl['ITESEQ'], $dati['Proric_rec']);
                if (!$ret_annulla) {
                    return false;
                } else {
                    $dati['Proric_rec'] = $ret_annulla;
// MOMENTANEAMENTE SOSPESO PERCHE NON UTILIZZATO DA APPROFONDIRE                    
//                    if ($Passo_distinta) {
//                        $ret_annulla_dis = $this->AnnullaPasso($PRAM_DB, $Passo_distinta['ITESEQ'], $dati['Proric_rec']);
//                        if (!$ret_annulla_dis) {
//                            return false;
//                        }
//                        $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($Passo_distinta['ITESEQ'])) . $Passo_distinta['ITESEQ'];
//                        $results = $this->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo);
//                        if ($results) {
//                            @unlink($dati['CartellaAllegati'] . "/" . $results[0]);
//                            $dati['Proric_rec'] = $ret_annulla_dis;
//                        }
//                    }
                }
            } else {
// MOMENTANEAMENTE SOSPESO PERCHE NON UTILIZZATO DA APPROFONDIRE
//                if ($Passo_distinta) {
//                    $resultFile = $this->CreaPdfDistinta($dati, $Passo_distinta);
//                    if ($resultFile == false) {
//                        return false;
//                    }
//                }
            }
        } else {
            return false;
        }

        return true;
    }

    public function AnnullaRaccolta($PRAM_DB, &$dati, $Ricite_rec_default) {
        if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec_default['ITESEQ'] . chr(46)) !== false) {
            $Ricdag_tab_raccolta = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND 
                                                            ITEKEY = '" . $Ricite_rec_default['ITEKEY'] . "'", true);

            foreach ($Ricdag_tab_raccolta as $Ricdag_rec) {
                if ($Ricdag_rec['DAGTIP'] == "Sportello_Aggregato") {
                    $dati['Proric_rec']['RICSPA'] = $Ricdag_rec['RICDAT'];
                }

                if ($Ricdag_rec['DAGKEY'] == "RICHIESTA_UNICA") {
                    $this->scollegaDaPraticaUnica($PRAM_DB, $dati['Proric_rec']['RICNUM']);

                    /*
                     * Lo scollega effettua l'update a PRORIC_REC ma non posso rinfrescare
                     * $dati con prendiDati (potrei perdere altre modifiche in corso),
                     * per cui imposto $dati['Proric_rec']['RICRUN'] = '' manualmente
                     * così che si aggiorni con il prossimo DBUpdate relativo a PRORIC.
                     */
                    $dati['Proric_rec']['RICRUN'] = '';
                }
            }

            $Ricite_rec_default['RICQSTRIS'] = 0;
            unset($Ricite_rec_default['Ricite_rec_upl']);
            unset($Ricite_rec_default['RICDOC']);
            unset($Ricite_rec_default['CLTOFF']);
            try {
                $nRows = ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $Ricite_rec_default);
                if ($nRows == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }

            $Ricdoc_tab = $this->GetRicdoc($Ricite_rec_default['ITEKEY'], 'itekey', $PRAM_DB, true, $dati['Proric_rec']['RICNUM']);
            if ($Ricdoc_tab) {
                foreach ($Ricdoc_tab as $ricdoc_rec) {
                    /**
                     * Non cancello l'autocertificazione accorpata (passo ITECIRUNI)
                     */
                    $metaData = unserialize($ricdoc_rec['DOCMETA']);
                    if ($metaData['AUTOCERTIFICAZIONE_ACCORPATA']) {
                        continue;
                    }

                    if (file_exists($dati['CartellaAllegati'] . "/" . $ricdoc_rec['DOCUPL'])) {
                        if (!@unlink($dati['CartellaAllegati'] . "/" . $ricdoc_rec['DOCUPL'])) {
                            return false;
                        }
                        $this->cancellaRicdoc($dati, $ricdoc_rec['DOCUPL'], $PRAM_DB);
                    }
                }
            }
            $ret_annulla = $this->AnnullaPasso($PRAM_DB, $Ricite_rec_default['ITESEQ'], $dati['Proric_rec']);
            if (!$ret_annulla) {
                return false;
            } else {
                $dati['Proric_rec'] = $ret_annulla;
            }
        }
        return true;
    }

    public function EliminaDipendenze($Ricite_tab_default, $PRAM_DB, $dati) {
        if ($Ricite_tab_default) {
            $RicseqOrigIniziale = $dati['Proric_rec']['RICSEQ'];
            $msgPassi = "";
            foreach ($Ricite_tab_default as $Ricite_rec_default) {
                if ($Ricite_rec_default['ITEDOW'] == 1) {

                    //
                    //Annullo la sequenza dei passi downlaod con dipendenze
                    //
                    $ret_annulla = $this->AnnullaPasso($PRAM_DB, $Ricite_rec_default['ITESEQ'], $dati['Proric_rec']);
                    if (!$ret_annulla) {
                        return "Errore nell'annullare il passo: " . $Ricite_rec_default['ITEDES'];
                    } else {
                        $dati['Proric_rec'] = $ret_annulla;
                    }

                    //
                    //Cancello gli upload con dipendenze
                    //
                    $Ricite_rec_upl = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM='" . $dati['Proric_rec']['RICNUM'] . "'
                                                                 AND ITECTP='" . $Ricite_rec_default['ITEKEY'] . "'", false);
                    $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($Ricite_rec_upl['ITESEQ'])) . $Ricite_rec_upl['ITESEQ'];
                    $results = $this->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo);
                    foreach ($results as $key => $alle) {
                        $ext = pathinfo($alle, PATHINFO_EXTENSION);
                        if ($ext == 'info' || $ext == 'err') {
                            unset($results[$key]);
                        }
                    }
                    foreach ($results as $key => $Allegato) {
                        if (!$this->CancellaUpload($PRAM_DB, $dati, $Ricite_rec_upl, $Allegato)) {
                            return "Errore nel cancellare gli allegati nel passo: " . $Ricite_rec_default['ITEDES'];
                        }
                    }
//                    foreach ($Ricite_rec_default['RICDOC'] as $Allegato) {
//                        if (!$this->CancellaUpload($PRAM_DB, $dati, $Ricite_rec_upl, $Allegato['DOCUPL'])) {
//                            return "Errore nel cancellare gli allegati nel passo: " . $Ricite_rec_default['ITEDES'];
//                        }
//                    }
                } else if ($Ricite_rec_default['ITEDAT'] == 1) {
                    //
                    //Annulla le raccolte date con dipendenze
                    //
                    if (!$this->AnnullaRaccolta($PRAM_DB, $dati, $Ricite_rec_default)) {
                        return "Errore nell'annullare la raccolta dati: " . $Ricite_rec_default['ITEDES'];
                    }
                    $dati['ricdat'] = 0;
                }

                //
                //Info per MSG di avvviso dopo aver annullato i passi
                //
                $tipo = "";
                if ($Ricite_rec_default['ITEDAT'])
                    $tipo = "Raccolta dati";
                if ($Ricite_rec_default['ITEDOW'])
                    $tipo = "Download e relativo Upload";
                foreach ($dati['Navigatore']['Ricite_tab_new'] as $keyPasso => $passo) {
                    //if ($Ricite_rec_default["ITEKEY"] == $passo['ITEKEY'] && strpos($RicseqOrigIniziale, chr(46) . $Ricite_rec_default['ITESEQ'] . chr(46)) !== false) {
                    if ($Ricite_rec_default["ITEKEY"] == $passo['ITEKEY']) {
                        $msgPassi .= "<span style=\"font-weight:bold;\"> - Passo " . ($keyPasso + 1) . ": " . $passo['ITEDES'] . " ($tipo)</span><br>";
                    }
                }
            }

            $effettuaCheckAnnullaRaccolta = true;

            /*
             * Check per parametro disabilitazione conferma annulla raccolta
             */
            $datiAmbiente = $dati['Navigatore']['Dizionario_Richiesta_new']->getData('AMBIENTE');
            if ($datiAmbiente && $datiAmbiente->getData('DISABILITA_CONFERMA_ANNULLA_RACCOLTA') == '1') {
                $effettuaCheckAnnullaRaccolta = false;
            }

            /*
             * Se sono stati annullati passo esce il div di AVVISO
             */
            if ($effettuaCheckAnnullaRaccolta && $msgPassi) {
                $html = new html();
                $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Attenzione\">
                                              <p style=\"padding-left:5px;padding-right:5px;font-size:1.2em;text-decoration:underline;\"><b>I seguenti Passi sono stati annullati poichè alcuni dati fanno riferimento al modello appena cancellato:</b></p>
                                              <p style=\"padding-left:5px;color:red;font-size:1em;\">$msgPassi</p>
                                           </div>");
                output::$html_out .= $html->getHtml();
            }
        }
        return true;
    }

    /**
     * Check se ci sono dei files su passi obbligatori non presenti
     * 
     * @param type $arrayPdf
     * @return \typeCheck
     * @param type $arrayPdf
     * @return type
     */
    function checkAllegatiMancanti($arrayPdf) {
        $manca = 0;
        krsort($arrayPdf);
        foreach ($arrayPdf as $file) {
            if ($file['FILENAME'] != '' && $file['FILEPATH'] == '' && (($file['ITEOBL'] == 1 && $file['ITEOBE'] == "") || ($file['RICOBL'] == 1 && $file['ITEOBE']))) {
                $manca = $manca + 1;
            }
        }
        return $manca;
    }

    function SbloccaCancellaRapporto($dati, $praLibAllegati, $PRAM_DB) {
        //
        //Mi trovo l'itekey del passo rapporto
        //
        $statoRapporti = $praLibAllegati->getStatoRapporti($dati);
        foreach ($statoRapporti as $key => $value) {
            $itekey = $key;
            break;
        }
        //
        //Se il rapporto ï¿½ stato creato annullo il passo
        //
        if ($statoRapporti[$itekey]['PassoEseguito'] == 1) {
            $passoRapporto_rec = $this->GetPassoRapporto($itekey, $dati['Proric_rec']['RICNUM'], "itekey", $PRAM_DB);
            $proric_rec = $this->AnnullaPasso($PRAM_DB, $passoRapporto_rec['ITESEQ'], $dati['Proric_rec']);
            if (!$proric_rec) {
                return false;
            }
            //
            //Se il rapporto ï¿½ stato caricato, cancello l'upload
            //
            if ($statoRapporti[$itekey]['UploadCaricato'] == 1) {
                $passoUplRapporto_rec = $this->GetPassoRapporto($passoRapporto_rec['ITEKEY'], $dati['Proric_rec']['RICNUM'], "itectp", $PRAM_DB);
                $allegato = $dati['Proric_rec']['RICNUM'] . "_C" . $passoUplRapporto_rec['ITESEQ'] . ".pdf.p7m";
                if (file_exists($dati['CartellaAllegati'] . "/" . $allegato)) {
                    $dati['Proric_rec']['RICSEQ'] = $proric_rec['RICSEQ'];
                    if (!$this->CancellaUpload($PRAM_DB, $dati, $passoUplRapporto_rec, $allegato)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function GetNomeTemplateUpload($qualificaAllegati, $metadati, $PRAM_DB, $nameOrig, $dati) {
        $praVar = new praVars();
        $praVar->setPRAM_DB($PRAM_DB);
        $praVar->setDestinazione($qualificaAllegati['DESTINAZIONE'][0]);
        $praVar->setClassificazione($qualificaAllegati['CLASSIFICAZIONE']);
        //$praVar->setNomeOrig($nameOrig);
        $fileName = $this->GetRicdocP7MNameOriginale($nameOrig);
        $praVar->setNomeOrig($fileName); //modificato 09-03-2016 per problema ferri ES:FI80_TECNICI_rel4_ED_AG.pdf.p7m_C030_01.pdf.p7m
        $praVar->setDati($dati);
        $praVar->loadVariabiliTemplateNomeUpload();
        $Dizionario = $praVar->getVariabiliTemplateUpload();
        $ext = $this->GetExtP7MFile($nameOrig); // modificato il 21/04/2016 perche se si allegava un file con piu estensioni p7m si incasinava
//        $ext = pathinfo($nameOrig, PATHINFO_EXTENSION);
//        if (strtolower($ext) == "p7m") {
//            $file = pathinfo($nameOrig, PATHINFO_FILENAME);
//            $ext1 = pathinfo($file, PATHINFO_EXTENSION);
//            $ext = "$ext1.$ext";
//        }
        //return $this->elaboraTemplateDefault($metadati['TEMPLATENOMEUPLOAD'], $Dizionario->getAllData()) . ".$ext";
        $dictionaryValues_pre = $Dizionario->getAllData();
        $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
        //return $this->elaboraTemplateDefault($metadati['TEMPLATENOMEUPLOAD'], $dictionaryValues) . ".$ext";
        return $this->valorizzaTemplate($metadati['TEMPLATENOMEUPLOAD'], $dictionaryValues) . ".$ext";
    }

    function CheckCampiRaccolta($Ricite_rec, $raccolta, $PRAM_DB) {
        //Controllo i campi mancanti partendo dal box template
        $arrayCampi = $raccolta;

        //$templateDagset = $arrayCampi["01"];
        // Mi trovo il dagset template per riprendere tuttti i campi
//        $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND DAGSET ='" . $Ricite_rec['ITEKEY'] . "_01'";
        $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND DAGSET LIKE '" . $Ricite_rec['ITEKEY'] . "___' ORDER BY DAGSET ASC";
        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        foreach ($Ricdag_tab as $key => $Ricdag_rec) {
            $templateDagset[$Ricdag_rec['DAGKEY']] = unserialize($Ricdag_rec['RICDAT']);
        }
        foreach ($arrayCampi as $key => $campiBox) {
            $arrayCampiMancanti[$key] = array_diff_key($templateDagset, $campiBox);
        }
        foreach ($arrayCampiMancanti as $dagset => $campi) {
            if ($campi) {
                foreach ($campi as $dagkey => $value) {
                    //Inserisco i campi mancanti
                    $arrayCampi[$dagset][$dagkey] = "";
                }
            }
        }
        return $arrayCampi;
    }

    function CheckCampiRaccoltaSingola($Ricite_rec, $raccolta, $PRAM_DB) {
        if (!is_array($raccolta)) {
            $raccolta = array();
        }

        /*
         * Prendo i RICDAG presenti sul database
         */

        $sql = "SELECT
                    DAGKEY
                FROM RICDAG
                WHERE
                    ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND
                    DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND
                    DAGSET LIKE '" . $Ricite_rec['ITEKEY'] . "___' AND
                    DAGTIC = 'RadioGroup'
                ORDER BY DAGSET ASC";
        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        /*
         * Prendo tutti i DAGKEY della raccolta
         */
        $dagkey_tab = array();
        foreach ($Ricdag_tab as $Ricdag_rec) {
            $dagkey_tab[] = $Ricdag_rec['DAGKEY'];
        }

        /*
         * Trovo i DAGKEY mancanti e li imposto con valore vuoto
         */
        $arrayCampiMancanti = array_diff($dagkey_tab, array_keys($raccolta));
        foreach ($arrayCampiMancanti as $dagkey) {
            $raccolta[$dagkey] = '';
        }

        return $raccolta;
    }

    function CreaPdfRaccolta($dati, $PRAM_DB) {
        $baseFile = $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($dati['Ricite_rec']['ITESEQ'], $dati['seqlen'], "0", STR_PAD_LEFT);
//        $resultFile = $dati['CartellaAllegati'] . '/' . $baseFile . '.pdf';
//        $resultName = "RaccoltaDati_" . $dati['Proric_rec']['RICNUM'] . ".pdf";
//
//        /*
//         * Se Portale scuola pesaro, cambio il nome del pdf della distinta
//         */
//        $suffix = ITA_DB_SUFFIX;
//        if (file_exists(ITA_SUAP_PATH . "/SUAP_italsoft/ControlliScuola.$suffix.class.php")) {
//            require ITA_SUAP_PATH . "/SUAP_italsoft/ControlliScuola.$suffix.class.php";
//            $obj = new ControlliScuola();
//            $resultName = $obj->GetNomeDistinta($dati);
//        }

        if (isset($dati['Ricite_rec']['ITEMETA'])) {
            $metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
            $template = $metadati['TESTOBASEXHTML'];
        }

        /*
         * Aggiungiamo il sotto dizionario distinta nel caso in cui serva la tabella di riepilogo degli allegati
         */
        $praVar2 = new praVars();
        $praVar2->setPRAM_DB($dati['PRAM_DB']);
        $praVar2->setDati($dati);
        $praVar2->loadVariabiliDistinta("");
        $praVar2->loadVariabiliRichiesteAccorpate();
        $dati['Navigatore']['Dizionario_Richiesta_new']->addField('PRAALLEGATI', 'Variabili Distinta', 6, 'itaDictionary', $praVar2->getVariabiliDistintaBase());
        $dati['Navigatore']['Dizionario_Richiesta_new']->addField('PRAACCORPATE', 'Variabili Richieste Accorpate', 7, 'itaDictionary', $praVar2->getVariabiliRichiesteAccorpate());
        $dictionaryValues_pre = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();

        $dictionaryValues = $this->htmlspecialchars_recursive($dictionaryValues_pre);

        $template = $this->elaboraTabelleTemplate($template, $dictionaryValues, true);
        require_once(ITA_LIB_PATH . "/itaPHPCore/itaSmarty.class.php");
        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', $template);
        $documentLayout = $itaSmarty->template_dir . "/" . $baseFile . "_documentlayout.xhtml";
        $layoutTemplate = ITA_PRATICHE_PATH . "/PRATICHE_italsoft/layoutTemplate.xhtml";
        if (!file_exists($layoutTemplate)) {
            return false;
        }
        @copy($layoutTemplate, $documentLayout);
        if (!file_exists($documentLayout)) {
            return false;
        }

        $contentPreview = $itaSmarty->fetch($documentLayout);
        unlink($documentLayout);

        $documentPreview = $itaSmarty->template_dir . "/" . $baseFile . "_documentpreview.xhtml";
        file_put_contents($documentPreview, $contentPreview);
        if (!file_exists($documentPreview)) {
            return false;
        }

        foreach ($dictionaryValues as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $contentPreview2 = utf8_encode($itaSmarty->fetch($documentPreview));
        unlink($documentPreview);
        $documentPreview2 = $itaSmarty->template_dir . "/" . $baseFile . '_documentpreview2.xhtml';
        $pdfPreview = $itaSmarty->template_dir . "/" . $baseFile . '.pdf';
        file_put_contents($documentPreview2, $contentPreview2);
        //file_put_contents("/users/pc/dos2ux/tmp/log.xml", $contentPreview2);
        passthru(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . '/java/itaH2P/itaH2P.jar ' . $documentPreview2 . ' ' . $pdfPreview, $return_var);
        if (!file_exists($pdfPreview)) {
            return false;
        }

        unlink($documentPreview2);
        if ($return_var == 0) {
            return $pdfPreview;
            /*
              //Salvo il record su RICDOC se non esiste
              $ricdoc_recCtr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM='" . $dati['Ricite_rec']['RICNUM'] . "' AND ITEKEY='" . $dati['Ricite_rec']['ITEKEY'] . "' AND DOCUPL = '$baseFile.pdf'", false);
              if (!$ricdoc_recCtr) {
              $this->registraRicdoc($dati['Ricite_rec'], $baseFile . '.pdf', $resultName, $PRAM_DB);
              }

              //Converto il pdf in pdf/a
              $raccolta_pdfa = pathinfo($pdfPreview, PATHINFO_DIRNAME) . "/" . pathinfo($pdfPreview, PATHINFO_FILENAME) . "_pdfa." . pathinfo($pdfPreview, PATHINFO_EXTENSION);
              $manager = itaPDFA::getManagerInstance(); //new itaPDFA();
              if ($manager) {
              $manager->convertPDF($pdfPreview, $raccolta_pdfa);
              unlink($pdfPreview);
              if ($manager->getLastExitCode() == 0) {
              if (file_exists($resultFile)) {
              unlink($resultFile);
              }
              if (!@rename($raccolta_pdfa, $resultFile)) {
              return false;
              }
              chmod($resultFile, 0777);
              //$this->registraRicdoc($dati['Ricite_rec'], $baseFile . '.pdf', $resultName, $PRAM_DB);
              return $resultFile;
              }
              } else {
              if (!@rename($pdfPreview, $resultFile)) {
              return false;
              }
              return $resultFile;
              }
             * 
             */
        }
        return false;
    }

    function SalvaPdfRaccolta($pdfPreview, $dati, $PRAM_DB) {
        $baseFile = $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($dati['Ricite_rec']['ITESEQ'], $dati['seqlen'], "0", STR_PAD_LEFT);
        $resultFile = $dati['CartellaAllegati'] . '/' . $baseFile . '.pdf';
        $resultName = "RaccoltaDati_" . $dati['Proric_rec']['RICNUM'] . ".pdf";

        /*
         * Se Portale scuola pesaro, cambio il nome del pdf della distinta
         */
        $suffix = ITA_DB_SUFFIX;
        if (file_exists(ITA_SUAP_PATH . "/SUAP_italsoft/ControlliScuola.$suffix.class.php")) {
            require ITA_SUAP_PATH . "/SUAP_italsoft/ControlliScuola.$suffix.class.php";
            $obj = new ControlliScuola();
            $resultName = $obj->GetNomeDistinta($dati);
        }

        //Salvo il record su RICDOC se non esiste
        $ricdoc_recCtr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM='" . $dati['Ricite_rec']['RICNUM'] . "' AND ITEKEY='" . $dati['Ricite_rec']['ITEKEY'] . "' AND DOCUPL = '$baseFile.pdf'", false);
        if (!$ricdoc_recCtr) {
            $this->registraRicdoc($dati['Ricite_rec'], $baseFile . '.pdf', $resultName, $PRAM_DB);
        }

        //Converto il pdf in pdf/a
        $raccolta_pdfa = pathinfo($pdfPreview, PATHINFO_DIRNAME) . "/" . pathinfo($pdfPreview, PATHINFO_FILENAME) . "_pdfa." . pathinfo($pdfPreview, PATHINFO_EXTENSION);
        $manager = itaPDFA::getManagerInstance(); //new itaPDFA();
        if ($manager) {
            $manager->convertPDF($pdfPreview, $raccolta_pdfa);
            unlink($pdfPreview);
            if ($manager->getLastExitCode() == 0) {
                if (file_exists($resultFile)) {
                    unlink($resultFile);
                }
                if (!@rename($raccolta_pdfa, $resultFile)) {
                    return false;
                }
                chmod($resultFile, 0777);
                return $resultFile;
            }
        } else {
            if (!@rename($pdfPreview, $resultFile)) {
                return false;
            }
            return $resultFile;
        }
    }

    function CheckExistCodFisPIva($Ricdag_tab, $Ricdag_rec_codFis, $Ricdag_tab_CFMappati, $Ricite_tab_new, $Ricdag_rec_codFisLegale = array()) {
        $trovato = false;
        foreach ($Ricite_tab_new as $ricite_rec) {
            if ($ricite_rec['ITEKEY'] === $Ricdag_rec_codFis['ITEKEY']) {
                $trovato = true;
                break;
            }
        }

        if ($Ricdag_rec_codFisLegale) {
            foreach ($Ricite_tab_new as $ricite_rec) {
                if ($ricite_rec['ITEKEY'] === $Ricdag_rec_codFisLegale['ITEKEY']) {
                    $trovato = true;
                    break;
                }
            }
        }


        if (count($Ricdag_tab_CFMappati) > 0 && ($Ricdag_rec_codFis || $Ricdag_rec_codFisLegale) && $trovato == true) {
            foreach ($Ricdag_tab as $Ricdag_rec) {
                $trovato = false;
                if (($Ricdag_rec['RICDAT'] && $Ricdag_rec['DAGTIP'] != "Codfis_InsProduttivo" && trim(strtoupper($Ricdag_rec['RICDAT'])) == trim(strtoupper($Ricdag_rec_codFis['RICDAT']))) ||
                        ($Ricdag_rec['RICDAT'] && $Ricdag_rec['DAGTIP'] != "FiscaleLegale" && trim(strtoupper($Ricdag_rec['RICDAT'])) == trim(strtoupper($Ricdag_rec_codFisLegale['RICDAT'])))
                ) {
                    $trovato = true;
                    break;
                }
            }
            return $trovato;
        } else {
            return true;
        }
    }

    public function GetBaseExtP7MFile($baseFile) {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
        $praLibAllegati = praLibAllegati::getInstance($this);
        return $praLibAllegati->GetBaseExtP7MFile($baseFile);
    }

    public function GetExtP7MFile($baseFile) {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
        $praLibAllegati = praLibAllegati::getInstance($this);
        return $praLibAllegati->GetExtP7MFile($baseFile);
    }

    function GetRicdocP7MNameAssociato($fileNameAssociato) {
        $ext2 = pathinfo($fileNameAssociato, PATHINFO_EXTENSION);
        if (strtolower($ext2) == 'p7m') {
            $fileNameAssociato_new = pathinfo($fileNameAssociato, PATHINFO_FILENAME);
            $fileNameAssociato = $this->GetRicdocP7MNameAssociato($fileNameAssociato_new);
        } else {
            $ext = $ext2 . "." . $ext;
            $fileNameAssociato = pathinfo($fileNameAssociato, PATHINFO_FILENAME);
        }
//        if (strtolower($ext2) == 'pdf') {
//            $ext = $ext2 . "." . $ext;
//            $fileNameAssociato = pathinfo($fileNameAssociato, PATHINFO_FILENAME);
//        } else if (strtolower($ext2) == 'p7m') {
//            $fileNameAssociato_new = pathinfo($fileNameAssociato, PATHINFO_FILENAME);
//            $fileNameAssociato = $this->GetRicdocP7MNameAssociato($fileNameAssociato_new);
//        }
        return $fileNameAssociato;
    }

    function GetRicdocP7MNameOriginale($fileNameOriginale) {
        $ext2 = pathinfo($fileNameOriginale, PATHINFO_EXTENSION);
        if (strtolower($ext2) == 'p7m') {
            $fileNameOriginale_new = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
            $fileNameOriginale = $this->GetRicdocP7MNameOriginale($fileNameOriginale_new);
        } else {
            $ext = $ext2 . "." . $ext;
            $fileNameOriginale = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
        }
//        if (strtolower($ext2) == 'pdf') {
//            $ext = $ext2 . "." . $ext;
//            $fileNameOriginale = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
//        } else if (strtolower($ext2) == 'p7m') {
//            $fileNameOriginale_new = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
//            $fileNameOriginale = $this->GetRicdocP7MNameAssociato($fileNameOriginale_new);
//        }
        return $fileNameOriginale;
    }

    public function GetP7MFileContentName($fileNameOriginale) {
        while (true) {
            $ext2 = pathinfo($fileNameOriginale, PATHINFO_EXTENSION);
            if (strtolower($ext2) == 'p7m') {
                $fileNameOriginale_new = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
                $fileNameOriginale = $this->GetP7MFileContentName($fileNameOriginale_new);
            } else {
                $fileNameOriginale = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
                $file = $fileNameOriginale . "." . $ext2;
                break;
            }
//            if (strtolower($ext2) == 'pdf') {
//                $fileNameOriginale = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
//                $file = $fileNameOriginale . "." . $ext2;
//                break;
//            } else if (strtolower($ext2) == 'p7m') {
//                $fileNameOriginale_new = pathinfo($fileNameOriginale, PATHINFO_FILENAME);
//                $fileNameOriginale = $this->GetP7MFileContentName($fileNameOriginale_new);
//            }
        }
        return $file;
    }

    function GetAllegatiInvioMail($dati, $arrayParamBloccoMail, $PRAM_DB) {
        $TotaleAllegati = $this->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM']);
        if ($arrayParamBloccoMail['bloccaInvioInfo'] == "Si") {
            $TotaleAllegati = $this->RemoveFileInfo($TotaleAllegati, $PRAM_DB);
        }
        return $TotaleAllegati;
    }

    function RemoveFileInfo($allegati, $PRAM_DB) {
        if ($allegati) {
            foreach ($allegati as $key => $allegato) {
                if (pathinfo($allegato, PATHINFO_EXTENSION) == 'info') {
                    unset($allegati[$key]);
                }
                $ricdoc_rec = $this->GetRicdoc($allegato, "codice", $PRAM_DB);
                if ($ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                    unset($allegati[$key]);
                }
            }
            return $allegati;
        }
    }

    function GetNormalArrayParam($param) {
        $clientParam = array();
        foreach ($param as $arrayParametro) {
            $clientParam[$arrayParametro['CHIAVE']] = $arrayParametro['CONFIG'];
        }
        return $clientParam;
    }

    function GetRicdocFromNavigatore($dati, $PRAMDB, $param = array()) {
        $TotaleAllegati = array();
        if ($param['TIPOALLEGATI'] == "NA") { // Prendo solo gli Allegati Non Accorpati(NA) e il rapporto
            foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
                if ($ricite_rec['ITEIDR'] == 0) {
                    $ricdoc_tab = $this->GetRicdoc($ricite_rec['ITEKEY'], "itekey", $PRAMDB, true, $ricite_rec['RICNUM']);
                    if ($ricdoc_tab) {
                        foreach ($ricdoc_tab as $ricdoc_rec) {
                            $TotaleAllegati[] = $ricdoc_rec;
                        }
                    }
                }
            }

            foreach ($dati['Ricdoc_tab_tot'] as $ricdoc_rec) {
                if ($ricdoc_rec['ITEKEY'] == $ricdoc_rec['ITECOD']) {
                    $TotaleAllegati[] = $ricdoc_rec;
                }
            }
        } elseif ($param['TIPOALLEGATI'] == "RC") { // Prendo solo il rapporto completo (RC)
            foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $ricite_rec) {
                if ($ricite_rec['ITEDRR'] == 1) {
                    $passoUploadRapporto = $dati['Navigatore']['Ricite_tab_new'][$key + 1];
                    break;
                }
            }
            if ($passoUploadRapporto) {
                $ricdoc_rec = $this->GetRicdoc($passoUploadRapporto['ITEKEY'], "itekey", $PRAMDB, false, $passoUploadRapporto['RICNUM']);
                $TotaleAllegati[] = $ricdoc_rec;
            } else {
                foreach ($dati['Ricdoc_tab_tot'] as $ricdoc_rec) {
                    if ($ricdoc_rec['DOCPRI'] == 1) {
                        $TotaleAllegati[] = $ricdoc_rec;
                        break;
                    }
                }
            }
        } else { // Prendo tutti gli Allegati
            foreach ($dati['Ricdoc_tab_tot'] as $ricdoc_rec) {
                $TotaleAllegati[] = $ricdoc_rec;
            }
        }
        return $TotaleAllegati;
    }

    /**
     * cerca i campi aggiuntivii che iniziano con il prefisso DEFAULT_ a parità
     * di nome campo e ne prende il valore
     *  
     * @param type $Ricdag_tab
     * @param type $PRAM_DB
     * @return type
     */
    function CheckDatiAggiuntiviDefault($Ricdag_tab, $PRAM_DB) {
        foreach ($Ricdag_tab as $keyDag => $Ricdag_rec) {
            $Ricdag_recDafault = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $Ricdag_rec['DAGNUM'] . "' AND DAGKEY = 'DEFAULT_" . $Ricdag_rec['DAGKEY'] . "'", false);
            if ($Ricdag_recDafault && trim($Ricdag_tab[$keyDag]['DAGVAL']) == '') {
                $Ricdag_tab[$keyDag]['DAGVAL'] = $Ricdag_recDafault['RICDAT'];
            }
        }
        return $Ricdag_tab;
    }

    function AnnullaSceltaIntegrazione($dati) {
//        $ricdag_tab_RicInteg = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Ricite_rec']['ITEVPA'] . "'", true);
//        foreach ($ricdag_tab_RicInteg as $ricdag_rec_RicInteg) {
//            $ricdag_rec_RicInteg['RICDAT'] = "";
//            try {
//                $nRows = ItaDB::DBUpdate($dati["PRAM_DB"], "RICDAG", 'ROWID', $ricdag_rec_RicInteg);
//                if ($nRows == -1) {
//                    return false;
//                }
//            } catch (Exception $exc) {
//                return false;
//            }
//        }
//        $ricite_rec_SceltaInt = $this->GetRicite($dati['Ricite_rec']['ITEVPA'], "itekey", $dati["PRAM_DB"], false, $dati['Proric_rec']['RICNUM']);
//        $proric_rec = $this->AnnullaPasso($dati["PRAM_DB"], $ricite_rec_SceltaInt['ITESEQ'], $dati['Proric_rec']);
//        $proric_rec['RICRPA'] = "";
//        try {
//            $nRows = ItaDB::DBUpdate($dati["PRAM_DB"], "PRORIC", 'ROWID', $proric_rec);
//            if ($nRows == -1) {
//                return false;
//            }
//        } catch (Exception $exc) {
//            return false;
//        }
        /*
         * Mi trovo tutti i campi aggiunti della richiesta che iniziano per RICHIESTA_PADRE (RICHIESTA_PADRE, RICHIESTA_PADRE_FORMATTED ecc)
         */
        $trovato = false;
        $ricdag_tab_RicInteg = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND DAGKEY LIKE 'RICHIESTA_PADRE%'", true);
        if ($ricdag_tab_RicInteg) {
            $ricite_rec_RicInteg = $this->GetRicite($ricdag_tab_RicInteg[0]['ITEKEY'], "itekey", $dati["PRAM_DB"], false, $dati['Proric_rec']['RICNUM']);
            if ($ricite_rec_RicInteg) {
                foreach ($dati['Navigatore']['Ricite_tab_new'] as $Ricite_rec) {
                    if ($Ricite_rec['ITEKEY'] === $ricite_rec_RicInteg['ITEKEY']) {
                        $trovato = true;
                        break;
                    }
                }

                if (!$trovato) {
                    /*
                     * Se lo trovo, annullo il passo
                     */
                    $proric_rec = $this->AnnullaPasso($dati["PRAM_DB"], $ricite_rec_RicInteg['ITESEQ'], $dati['Proric_rec']);

                    /*
                     * Se lo trovo, sgancio l'integrazione
                     */
                    if ($proric_rec['RICRPA']) {
                        $proric_rec['RICRPA'] = "";
                        try {
                            $nRows = ItaDB::DBUpdate($dati["PRAM_DB"], "PRORIC", 'ROWID', $proric_rec);
                            if ($nRows == -1) {
                                return false;
                            }
                        } catch (Exception $exc) {
                            return false;
                        }
                    }

                    /*
                     * Se trovo i dati aggiunti, svuoto il campo RICDAT
                     */
                    foreach ($ricdag_tab_RicInteg as $ricdag_rec_RicInteg) {
                        $ricdag_rec_RicInteg['RICDAT'] = "";
                        try {
                            $nRows = ItaDB::DBUpdate($dati["PRAM_DB"], "RICDAG", 'ROWID', $ricdag_rec_RicInteg);
                            if ($nRows == -1) {
                                return false;
                            }
                        } catch (Exception $exc) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    function CheckUploadFile($ricnum, $nomeFile, $pathAllegati, $PRAM_DB) {
        $hashFile = hash_file('sha256', $pathAllegati . "/" . $nomeFile);
        $ricdoc_tab = $this->GetRicdoc("", "ricnum", $PRAM_DB, true, $ricnum);
        foreach ($ricdoc_tab as $ricdoc_rec) {
            if ($ricdoc_rec['DOCSHA2'] && $ricdoc_rec['DOCSHA2'] == $hashFile) {
                return $this->GetRicite($ricdoc_rec['ITEKEY'], "itekey", $PRAM_DB, false, $ricnum);
            }
        }
        return true;
    }

    function CreaXMLINFO($modo, $dati, $scrivi = true) {
        //
        //Scrivo il file XMLINFO in base al tipo di richiesta
        //
        switch ($modo) {
            case "ANNULLAMENTO-INTEGRAZIONE":
            case "ANNULLAMENTO-RICHIESTA":
                $Nome_file = $dati['CartellaAllegati'] . "/XMLANN.xml";
                $xml = $this->CreaXMLann($dati);
                break;
            default :
                $Nome_file = $dati['CartellaAllegati'] . "/XMLINFO.xml";
                $xml = $this->CreaXML($dati, $dati['PRAM_DB']);
                break;
        }

        if ($scrivi) {
            $File = fopen($Nome_file, "w+");
            if (!file_exists($Nome_file)) {
                return false;
            } else {
                fwrite($File, $xml);
                fclose($File);
            }
            return true;
        } else {
            return $Nome_file;
        }
    }

    public function scriviXmlAccorpate($dati, $modo, $pathPadre) {
        $praLibDati = praLibDati::getInstance($this);
        $proric_tab_accorpate = $this->GetRichiesteAccorpate($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);
        foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
            $dati_accorpata = $praLibDati->prendiDati($proric_rec_accorpate['RICNUM']);
            if (!$dati_accorpata) {
                $this->errCode = -1;
                $this->errMessage = $praLibDati->getErrMessage();
                return false;
            }
            if (!$this->CreaXMLINFO($modo, $dati_accorpata)) {
                $this->errCode = -1;
                $this->errMessage = "errore creazione file XMLINFO della pratica accorpata " . $proric_rec_accorpate['RICNUM'] . " in " . $dati['CartellaAllegati'];
                return false;
            }

            $xmlInfoAccorp = $this->CreaXMLINFO($modo, $dati_accorpata, false);

            /*
             * Copio il file della figla nella cartella allegati del padre
             */
            $xmlInfoAccorpDest = $dati['CartellaAllegati'] . '/XMLINFO_' . $proric_rec_accorpate['RICNUM'] . '.xml';
            copy($xmlInfoAccorp, $xmlInfoAccorpDest);
            if (!file_exists($xmlInfoAccorpDest)) {
                $this->errCode = -1;
                $this->errMessage = "Impossibile copiare il file XMLINFO della praticha accorpata {$proric_rec_accorpate['RICNUM']} in \'{$dati['CartellaAllegati']}\'.";
                return false;
            }

            /*
             * Copio il file della figla nella cartella allegati del procedimento d'origine
             */
            $xmlInfoAccorpDestPadre = $pathPadre . '/XMLINFO_' . $proric_rec_accorpate['RICNUM'] . '.xml';
            copy($xmlInfoAccorp, $xmlInfoAccorpDestPadre);
            if (!file_exists($xmlInfoAccorpDestPadre)) {
                $this->errCode = -1;
                $this->errMessage = "Impossibile copiare il file XMLINFO della praticha accorpata {$proric_rec_accorpate['RICNUM']} in \'$pathPadre\'.";
                return false;
            }

            /*
             * Blocco la pratica accorpata
             */
            if (!$this->BloccaRichiesta($dati_accorpata)) {
                return false;
            }

            if (!$this->scriviXmlAccorpate($dati_accorpata, $modo, $pathPadre)) {
                return false;
            }
        }
        return true;
    }

    public function getDatiInizialiCityWare($progSogg, $procedi, $ricnum) {
//        print_r("<pre>");
//        print_r("is cityware:" . $progSogg);
//        print_r("</pre>");
        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $progSogg; //34002;  // Progsogg
        $methodArgs[1] = $procedi;  //600014;  // Id Pratica Italsoft (tipo pratica) 
        $methodArgs[2] = $ricnum;  // Id Istanza pratica
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_dati_iniziali', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
//                print_r("<pre>");
//                print_r("Errore Lettura risultato xml dati iniziali.");
//                print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//                print_r("<pre>");
//                print_r("Dati Iniziali:");
//                print_r($arrayXml);
//                print_r("</pre>");
            }
        } else {
//            print_r("<pre>");
//            print_r("Errore Lettura dati iniziali.");
//            print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function getPercorsiFermCityWare($progSogg, $procedi, $chiavePasso) {
        //print_r("<pre>");
        // print_r("Percorsi" . $progSogg);
        //print_r("passo" . $chiavePasso);
        //print_r("</pre>");

        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $progSogg; //34002;  // Progsogg
        $methodArgs[1] = $procedi;  //600014;  // Id Pratica Italsoft (tipo pratica) 	
        $methodArgs[2] = $chiavePasso;  //600014;  // Id Pratica Italsoft (tipo pratica) 	
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_percorsi_fermate', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
                //print_r("<pre>");
                //print_r("Errore Lettura risultato xml Percorsi.");
                //print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                //print_r("<pre>");
                //print_r("Dati Percorsi $progSogg:");
                //print_r($arrayXml);
                //print_r("</pre>");
            }
        } else {
            //print_r("<pre>");
            //print_r("Errore Lettura dati Percorsi $progSogg.");
            //print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function getTipoPastoCityWare() {
        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_tipo_pasto', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
                //print_r("<pre>");
                //print_r("Errore Lettura risultato xml Tipo pasto.");
                //print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                //print_r("<pre>");
                //print_r("Dati Tipo pasto $progSogg:");
                //print_r($arrayXml);
                //print_r("</pre>");
            }
        } else {
            //print_r("<pre>");
            //print_r("Errore Lettura dati tipo pasto $progSogg.");
            //print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function getNucleoFamiliareCityWare($progSogg, $procedi) {
//        print_r("<pre>");
//        print_r("Nucleo Familiare" . $progSogg);
//        print_r("</pre>");

        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $progSogg; //34002;  // Progsogg
        $methodArgs[1] = $procedi;  //600014;  // Id Pratica Italsoft (tipo pratica) 	
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_trova_nucleo', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
//                print_r("<pre>");
//                print_r("Errore Lettura risultato xml nucleo Familiare.");
//                print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//                print_r("<pre>");
//                print_r("Dati Nucleo familiare $progSogg:");
//                print_r($arrayXml);
//                print_r("</pre>");
            }
        } else {
//            print_r("<pre>");
//            print_r("Errore Lettura dati Nucleo Familiare $progSogg.");
//            print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function getIstitutiScolasticiCityware($progSogg, $procedi) {
//        print_r("<pre>");
//        print_r("Istituti Scolastici" . $progSogg);
//        print_r("</pre>");
        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $progSogg; //34002;  // Progsogg
        $methodArgs[1] = $procedi; //34002;  // Progsogg
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_istituti_scolastici', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
//                print_r("<pre>");
//                print_r("Errore Lettura risultato xml Istituti Scolastici.");
//                print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//                print_r("<pre>");
//                print_r("Dati Istituti Scolastici $progSogg:");
//                print_r($arrayXml);
                print_r("</pre>");
            }
        } else {
//            print_r("<pre>");
//            print_r("Errore Lettura dati Istituti Scolastici $progSogg.");
//            print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function getServiziCityware($progSogg, $procedi) {
//        print_r("<pre>");
//        print_r("Servizi $progSogg --> $procedi");
//        print_r("</pre>");
        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $progSogg; //34002;  // Progsogg
        $methodArgs[1] = $procedi; //34002;  // Progsogg
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_SWE_PORTAL', 'CN_servizi', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
//                print_r("<pre>");
//                print_r("Errore Lettura risultato xml Servizi.");
//                print_r("</pre>");
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//                print_r("<pre>");
//                print_r("Dati Servizi $progSogg:");
//                print_r($arrayXml);
//                print_r("</pre>");
            }
        } else {
//            print_r("<pre>");
//            print_r("Errore Servizi");
//            print_r("</pre>");
            return false;
        }
        return $arrayXml;
    }

    public function setRicezionePraticaCityware($xmlStream, $pratica) {
        require_once(ITA_LIB_PATH . "/itaPHPOmnis/itaOmnisClient.class.php");
        $methodArgs = array();
        $methodArgs[0] = $xmlStream;
        $methodArgs[1] = $pratica;
        $omnisClient = new itaOmnisClient();
        if ($omnisClient->setParametersFromJsonString(ITA_CITYWARE_ACCESS_PARAMS)) {
            $result = $omnisClient->callExecute('OBJ_CP_S_ISCRIZIONIFEE', 'RicezionePratica', $methodArgs);
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);
            if (!$retXml) {
                return false;
            } else {
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            }
        } else {
            return false;
        }
        return $arrayXml;
    }

    function GetXMLRichiestaDati($dati) {
        $cdata_a = "<![CDATA[";
        $cdata_c = "]]>";
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml = $xml . "<RICHIESTADATI id=\"" . $dati['Proric_rec']['RICNUM'] . "\">\r\n";
        $xml = $xml . "<PRORIC>\r\n";
        $xml = $xml . "<RECORD>\r\n";
        foreach ($dati['Proric_rec'] as $Chiave => $Campo) {
            $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
        }
        $xml = $xml . "</RECORD>\r\n";
        $xml = $xml . "</PRORIC>\r\n";
        //
        $Ricite_tab = $dati['Navigatore']['Ricite_tab_new'];
        $Ricdag_tab = $dati['Navigatore']['Ricdag_tab_new'];
        $Ricdoc_tab = $dati['Navigatore']['Ricdoc_tab_new'];
        $Ricdag_tab_richiesta = ItaDB::DBSQLSelect($dati['PRAM_DB'], "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $dati['Proric_rec']['RICPRO'] . "'", true);
        $Ricite_tab_endo = ItaDB::DBSQLSelect($dati['PRAM_DB'], "SELECT * FROM RICITE WHERE RICNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND ITEPUB = 0 ORDER BY ITESEQ", true);
        //
        $campi = array();
        $campi[] = "RICNUM";
        $campi[] = "ITEKEY";
        $campi[] = "ITEDES";
        $campi[] = "ITESEQ";

        $chiavi = array();
        //
        if ($Ricite_tab) {
            $xml_ricite = "<RICITE>\r\n";
            foreach ($Ricite_tab as $Ricite_rec) {
                if ($Ricite_rec['ITEDOW']) {
                    continue;
                }
                $chiavi[$Ricite_rec['ITEKEY']] = true;
                $xml_ricite = $xml_ricite . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    $xml_ricite = $xml_ricite . "<$Campo>$cdata_a" . $Ricite_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                }
                $xml_ricite = $xml_ricite . "</RECORD>\r\n";
            }

            /*
             * Passi endo procedimenti non esportati
             * 
             */

            /* 			
              foreach ($Ricite_tab_endo as $Ricite_rec) {
              if ($Ricite_rec['ITEPUB'] == 0) {
              $chiavi[] = "'" . $Ricite_rec['ITEKEY'] . "'";
              $xml_ricite = $xml_ricite . "<RECORD>\r\n";
              foreach ($campi as $Chiave => $Campo) {
              $xml_ricite = $xml_ricite . "<$Campo>$cdata_a" . $Ricite_rec[$Campo] . "$cdata_c</$Campo>\r\n";
              }
              $xml_ricite = $xml_ricite . "</RECORD>\r\n";
              }
              }
             */
            $xml_ricite = $xml_ricite . "</RICITE>\r\n";

            $xml .= $xml_ricite;
            //}

            $campi = array();
            $campi[] = "DAGNUM";
            $campi[] = "ITEKEY";
            $campi[] = "DAGKEY";
            $campi[] = "DAGSET";
            $campi[] = "RICDAT";

            if ($Ricdag_tab) {
                $xml = $xml . "<RICDAG>\r\n";

                //
                foreach ($Ricdag_tab_richiesta as $Ricdag_rec) {
                    $xml = $xml . "<RECORD>\r\n";
                    foreach ($campi as $Chiave => $Campo) {
                        $xml = $xml . "<$Campo>$cdata_a" . $Ricdag_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                    }
                    $xml = $xml . "</RECORD>\r\n";
                }



                foreach ($Ricdag_tab as $Ricdag_rec) {
                    if (!array_key_exists($Ricdag_rec['ITEKEY'], $chiavi)) {
                        continue;
                    }
                    $xml = $xml . "<RECORD>\r\n";
                    foreach ($campi as $Chiave => $Campo) {
                        $xml = $xml . "<$Campo>$cdata_a" . $Ricdag_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                    }
                    $xml = $xml . "</RECORD>\r\n";
                }
                $xml = $xml . "</RICDAG>\r\n";
            }

            //}
            //
            // Indice Allegati
            //

            $campi = array();
            $campi[] = "ROWID";
            $campi[] = "DOCNUM";
            $campi[] = "ITEKEY";
            $campi[] = "DOCUPL";
            $campi[] = "DOCNAME";
            //
            if ($Ricdoc_tab) {
                $xml = $xml . "<RICDOC>\r\n";
                foreach ($Ricdoc_tab as $Ricdoc_rec) {
                    $base64 = "";
                    if (strtolower(pathinfo($Ricdoc_rec['DOCUPL'], PATHINFO_EXTENSION)) == 'info') {
                        continue;
                    }
                    $xml = $xml . "<RECORD>\r\n";
                    foreach ($campi as $Chiave => $Campo) {
                        $xml = $xml . "<$Campo>$cdata_a" . $Ricdoc_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                    }
                    $fh = fopen($dati['CartellaAllegati'] . "/" . $Ricdoc_rec['DOCUPL'], 'rb');
                    if ($fh) {
                        $binary = fread($fh, filesize($dati['CartellaAllegati'] . "/" . $Ricdoc_rec['DOCUPL']));
                        fclose($fh);
                        $base64 = base64_encode($binary);
                    }
                    $xml = $xml . "<STREAM>$cdata_a$base64$cdata_c</STREAM>\r\n";
                    $xml = $xml . "</RECORD>\r\n";
                }
                $xml = $xml . "</RICDOC>\r\n";
            }
        }
        $xml = $xml . "</RICHIESTADATI>\r\n";
        return $xml;
    }

    public function GetProcedimentoIntegrazione($PRAM_DB) {
        $Filent_rec = $this->GetFilent(1, $PRAM_DB);
        if ($Filent_rec['FILDE5']) {
            return $Filent_rec['FILDE5'];
        } else {
            if ($Filent_rec['FILDE4'] == "S" && $Filent_rec['FILDE3']) {
                $pramMaster = ItaDB::DBOpen('PRAM', $Filent_rec['FILDE3']);
                $Filent_rec_master = $this->GetFilent(1, $pramMaster);
                if ($Filent_rec_master['FILDE5']) {
                    return $Filent_rec_master['FILDE5'];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function GetRichiesteAccorpate($PRAM_DB, $richiesta) {
        $sql = "SELECT
                    PRORIC.*,
                    " . $PRAM_DB->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES
                FROM
                    PRORIC
                LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO = ANAPRA.PRANUM
                WHERE
                    PRORIC.RICRUN = '$richiesta'
                AND
                    PRORIC.RICSTA <> 'OF'
                ORDER BY PRORIC.RICNUM DESC";

        return ItaDB::DBSQLSelect($PRAM_DB, $sql);
    }

    public function GetAutocertificazioniAccorpate($PRAM_DB, $richiesta) {
        $sql = "SELECT ITEKEY FROM RICITE WHERE RICNUM = '$richiesta' AND ITERICUNI = '1'";
        $Ricite_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        if (!$Ricite_rec) {
            return false;
        }

        $Ricdoc_tab = $this->GetRicdoc($Ricite_rec['ITEKEY'], 'itekey', $PRAM_DB, true, $richiesta);

        foreach ($Ricdoc_tab as $k => $Ricdoc_rec) {
            $metaData = unserialize($Ricdoc_rec['DOCMETA']);
            if (!$metaData['AUTOCERTIFICAZIONE_ACCORPATA']) {
                unset($Ricdoc_tab[$k]);
            }
        }

        return $Ricdoc_tab;
    }

    public function accorpaAPraticaUnica($PRAM_DB, $RICNUM_accorpata, $RICNUM_padre) {
        $Proric_rec = $this->GetProric(addslashes($RICNUM_accorpata), 'codice', $PRAM_DB);
        $Proric_padre_rec = $this->GetProric(addslashes($RICNUM_padre), 'codice', $PRAM_DB);

        if (!$Proric_rec) {
            $this->errCode = -1;
            $this->errMessage = "Pratica accorpata '$RICNUM_accorpata' non trovata.";
            return false;
        }

        if (!$Proric_padre_rec) {
            $this->errCode = -6;
            $this->errMessage = "Pratica padre '$RICNUM_padre' non trovata.";
            return false;
        }

        if (
                $Proric_padre_rec['RICFIS'] != $Proric_rec['RICFIS'] ||
                $Proric_rec['RICNUM'] == $Proric_padre_rec['RICNUM']
        ) {
            $this->errCode = -7;
            $this->errMessage = "Impossibile accorpare la richiesta.";
            return false;
        }

        if (!$Proric_rec['RICRUN']) {
            $Proric_rec['RICRUN'] = $RICNUM_padre;
        }

        $Richiesta_unica_formatted = substr($Proric_rec['RICRUN'], 4, 6) . '/' . substr($Proric_rec['RICRUN'], 0, 4);

        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . addslashes($RICNUM_accorpata) . "' AND DAGTIP = 'Richiesta_unica'");
        foreach ($ricdag_tab as $ricdag_rec) {
            $ricdag_rec['RICDAT'] = $Richiesta_unica_formatted;

            try {
                ItaDB::DBUpdate($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);
            } catch (Exception $e) {
                $this->errCode = -2;
                $this->errMessage = "Errore aggiornamento dato aggiuntivo {$ricdag_rec['DAGKEY']} su '$RICNUM_accorpata' per accorpamento: " . $e->getMessage();
                return false;
            }
        }

        if (count($ricdag_tab) === 0) {
            /*
             * Inserisco il tipizzato come dato aggiuntivo di richiesta
             */

            $ricdag_rec = array();
            $ricdag_rec['DAGNUM'] = $RICNUM_accorpata;
            $ricdag_rec['ITECOD'] = $Proric_rec['RICPRO'];
            $ricdag_rec['ITEKEY'] = $Proric_rec['RICPRO'];
            $ricdag_rec['DAGKEY'] = 'RICHIESTA_UNICA';
            $ricdag_rec['DAGTIP'] = 'Richiesta_unica';
            $ricdag_rec['DAGTIC'] = 'Hidden';
            $ricdag_rec['RICDAT'] = $Richiesta_unica_formatted;

            try {
                ItaDB::DBInsert($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);
            } catch (Exception $e) {
                $this->errCode = -3;
                $this->errMessage = "Errore inserimento dato aggiuntivo RICHIESTA_UNICA su '$RICNUM_accorpata' per accorpamento: " . $e->getMessage();
                return false;
            }
        }

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibStandardExit.class.php';

        /*
         * Cerco i passi di accorpamento e li inserisco come effettuati.
         */

        $sql = "SELECT RICITE.*, PRACLT.CLTOPEFO
                    FROM RICITE
                    LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD = RICITE.ITECLT
                    WHERE
                        RICNUM = '" . addslashes($RICNUM_accorpata) . "' AND (
                            PRACLT.CLTOPEFO = '" . praLibStandardExit::FUN_FO_PASSO_ACC_DOMANDA . "' OR
                            PRACLT.CLTOPEFO = '" . praLibStandardExit::FUN_FO_PASSO_ACC_SCELTA . "'
                        )";

        $ricite_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql);
        $aggiornaSequenza = '';

        foreach ($ricite_tab as $ricite_rec) {
            if ($ricite_rec['CLTOPEFO'] == praLibStandardExit::FUN_FO_PASSO_ACC_DOMANDA) {
                unset($ricite_rec['CLTOPEFO']);
                $ricite_rec['RCIRIS'] = 'SI';

                try {
                    ItaDB::DBUpdate($PRAM_DB, 'RICITE', 'ROWID', $ricite_rec);
                } catch (Exception $e) {
                    $this->errCode = -4;
                    $this->errMessage = "Errore aggiornamento passo domanda accorpamento su '$RICNUM_accorpata': " . $e->getMessage();
                    return false;
                }
            }

            if (strpos($Proric_rec['RICSEQ'], ".{$ricite_rec['ITESEQ']}.") !== false) {
                continue;
            }

            $aggiornaSequenza .= ".{$ricite_rec['ITESEQ']}.";
        }

        if ($aggiornaSequenza !== '') {
            $Proric_rec['RICSEQ'] .= $aggiornaSequenza;
        }

        try {
            ItaDB::DBUpdate($PRAM_DB, 'PRORIC', 'ROWID', $Proric_rec);
        } catch (Exception $e) {
            $this->errCode = -5;
            $this->errMessage = "Errore aggiornamento PRORIC '$RICNUM_accorpata' per accorpamento: " . $e->getMessage();
            return false;
        }

        return true;
    }

    public function scollegaDaPraticaUnica($PRAM_DB, $RICNUM, $annullaPassiPadre = true) {
        $proric_rec = $this->GetProric($RICNUM, 'codice', $PRAM_DB);

        if (!$proric_rec) {
            return false;
        }

        if ($proric_rec['RICRUN'] != '') {
            $ricnumPadre = $proric_rec['RICRUN'];

            $proric_rec['RICRUN'] = '';

            try {
                ItaDB::DBUpdate($PRAM_DB, 'PRORIC', 'ROWID', $proric_rec);
            } catch (Exception $e) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0090', $e->getMessage(), __CLASS__);
                return false;
            }

            if ($annullaPassiPadre) {
                /*
                 * Annullo il passo di accorpamento richieste del padre
                 */

                $proric_padre_rec = $this->GetProric($ricnumPadre, 'codice', $PRAM_DB);
                $ricite_padre_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '$ricnumPadre' AND ITERICUNI = 1");

                $datiPadre = array('Proric_rec' => $proric_padre_rec);

                foreach ($ricite_padre_tab as $ricite_padre_rec) {
                    $datiPadre['CartellaAllegati'] = $this->getCartellaAttachmentPratiche($ricite_padre_rec['RICNUM']);
                    $datiPadre['Ricdoc_tab_tot'] = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDOC WHERE ITECOD = '" . $proric_padre_rec['RICPRO'] . "' AND DOCNUM = '" . $ricite_padre_rec['RICNUM'] . "'", true);

                    if (!$this->AnnullaRaccolta($PRAM_DB, $datiPadre, $ricite_padre_rec)) {
                        return false;
                    }
                }
            }
        }

        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT ROWID, RICDAT, ITEKEY FROM RICDAG WHERE DAGNUM = '$RICNUM' AND ( DAGKEY = 'RICHIESTA_UNICA' OR DAGKEY = 'RICHIESTA_UNICA_FORMATTED' )");
        $itekey_da_scollegare = array();

        foreach ($ricdag_tab as $ricdag_rec) {
            if ($ricdag_rec['RICDAT'] != '') {
                $ricdag_rec['RICDAT'] = '';

                try {
                    ItaDB::DBUpdate($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0091', $e->getMessage(), __CLASS__);
                    return false;
                }

                $itekey_da_scollegare[] = $ricdag_rec['ITEKEY'];
            }
        }

        $itekey_da_scollegare = array_unique($itekey_da_scollegare);

        foreach ($itekey_da_scollegare as $itekey) {
            $itekey_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT ITESEQ FROM RICITE WHERE RICNUM = '$RICNUM' AND ITEKEY = '$itekey'", false);

            if ($itekey_rec) {
                $proric_rec = $this->AnnullaPasso($PRAM_DB, $itekey_rec['ITESEQ'], $proric_rec);
                if ($proric_rec === false) {
                    return false;
                }
            }
        }

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibStandardExit.class.php';

        $sql = "SELECT RICITE.*
                FROM RICITE
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD = RICITE.ITECLT
                WHERE
                    RICNUM = '$RICNUM' AND (
                        PRACLT.CLTOPEFO = '" . praLibStandardExit::FUN_FO_PASSO_ACC_DOMANDA . "' OR
                        PRACLT.CLTOPEFO = '" . praLibStandardExit::FUN_FO_PASSO_ACC_SCELTA . "'
                    )";

        $ricite_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql);

        foreach ($ricite_tab as $ricite_rec) {
            if ($ricite_rec['ITEQST'] == '1') {
                $ricite_rec['RCIRIS'] = '';

                try {
                    ItaDB::DBUpdate($PRAM_DB, 'RICITE', 'ROWID', $ricite_rec);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0091', $e->getMessage(), __CLASS__);
                    return false;
                }
            }

            $proric_rec = $this->AnnullaPasso($PRAM_DB, $ricite_rec['ITESEQ'], $proric_rec);
            if ($proric_rec === false) {
                return false;
            }
        }

        return true;
    }

    public function creaRapportoCompleto($dati, $ditta, $PRAM_DB, $pdfa = true) {
        $arrayPdf = $this->ControllaRapportoConfig($dati, $dati['Ricite_rec']);
        if ($arrayPdf === false) {
            return false;
        }

        $fileRapporto = $dati['Proric_rec']['RICNUM'] . '_' . $ditta . '_rapporto.pdf';
        $fileDRR = $dati['Proric_rec']['RICNUM'] . '_' . $ditta . '_rapporto_' . $dati['Ricite_rec']['ITEKEY'] . '.pdf';

        /*
         * Scarto i passi non obbligatori non caricati
         */
        foreach ($arrayPdf as $key => $file) {
            if ($file['FILENAME'] != '' && $file['FILEPATH'] == '' && ($file['ITEOBL'] == 0 || $file['RICOBL'] == 0)) {
                unset($arrayPdf[$key]);
            }
        }

        /*
         * Controllo la presenza dei files per i passi da comporre
         */
        $tuttiPresenti = true;
        foreach ($arrayPdf as $key => $file) {
            if ($file['FILENAME'] != '' && $file['FILEPATH'] == '') {
                $tuttiPresenti = false;
            }
        }

        /*
         * Controllo se il codice fiscale ins produttivo compare in almeno un dato aggiuntivo della richiesta
         */
        if (!$this->CheckExistCodFisPIva($dati['Ricdag_tab_totali'], $dati['Ricdag_rec_codFis'], $dati['Ricdag_tab_CFMappati'], $dati['Navigatore']['Ricite_tab_new'], $dati['Ricdag_rec_codFisLegale'])) {
            $this->errCode = -2;
            $this->errMessage = 'Nei modelli non sembra esserci nessun codice fiscale uguale al codice fiscale insediamento produttivo.<br>Controllare i dati inseriti.';
            return false;
        }

        $Metadati = unserialize($dati['Ricite_rec']['ITEMETA']);
        $Metadati['SCHEMARAPPORTO'] = array();
        foreach ($arrayPdf as $key => $File) {
            $Metadati['SCHEMARAPPORTO'][] = $File['FILENAME'];
        }
        $dati['Ricite_rec']['ITEMETA'] = serialize($Metadati);
        $nRows = ItaDB::DBUpdate($PRAM_DB, "RICITE", 'ROWID', $dati['Ricite_rec']);

        if (!$arrayPdf || $tuttiPresenti !== true) {
            return false;
        }

        if (file_exists($dati['CartellaAllegati'] . '/' . $fileRapporto)) {
            unlink($dati['CartellaAllegati'] . '/' . $fileRapporto);
        }

        if (file_exists($dati['CartellaAllegati'] . '/' . $fileDRR)) {
            unlink($dati['CartellaAllegati'] . '/' . $fileDRR);
        }

        if (ITA_JVM_PATH == "" || !file_exists(ITA_JVM_PATH)) {
            $this->errCode = -1;
            $this->errMessage = 'JRE/JDK Mancante';
            return false;
        }

        $xmlCat = $dati['CartellaTemporary'] . "/xmlCatRapportoCompleto.xml";
        $FileXml = fopen($xmlCat, "w");

        if (!file_exists($xmlCat)) {
            $this->errCode = -1;
            $this->errMessage = "File '$xmlCat' non trovato";
            return false;
        }

        $arInput = $arrayPdf;
        $output = $dati['CartellaAllegati'] . "/" . $fileRapporto;
        $xml = $this->CreaXmlOverlayPdf($arInput, $output);
        fwrite($FileXml, $xml);
        fclose($FileXml);
        exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlCat ", $ret);
        $taskCat = false;
        foreach ($ret as $key => $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskCat = true;
                break;
            }
        }

        $this->ClearDirectory($dati['CartellaTemporary']);

        if (!$taskCat) {
            $this->errCode = -1;
            $this->errMessage = "Generazione Pdf Rapporto Fallita Richiesta N. " . $dati['Proric_rec']['RICNUM'] . "<br>" . print_r($ret, true);
            return false;
        }

        if ($pdfa) {
            $rapporto_pdfa = $dati['CartellaAllegati'] . "/" . pathinfo($fileRapporto, PATHINFO_FILENAME) . "_pdfa." . pathinfo($fileRapporto, PATHINFO_EXTENSION);
            $manager = itaPDFA::getManagerInstance();
            if ($manager) {
                $manager->convertPDF($dati['CartellaAllegati'] . "/" . $fileRapporto, $rapporto_pdfa);
                if ($manager->getLastExitCode() == 0) {
                    copy($rapporto_pdfa, $dati['CartellaAllegati'] . "/" . $fileRapporto);
                } else {
                    $errOut = $manager->getLastOutput();
                    $errMsg = $manager->getLastMessage();

                    $this->errCode = -3;
                    $this->errMessage = "Errore nel convertire il rapporto in pdf/a pratica n. " . $dati['Proric_rec']['RICNUM'] . ":<br>Error--->$errMsg<br>Output--->$errOut" . print_r($errOut, true);
                }
            }
        }

        if (file_exists($dati['CartellaAllegati'] . '/' . $fileRapporto)) {
            @copy($dati['CartellaAllegati'] . '/' . $fileRapporto, $dati['CartellaAllegati'] . '/' . $fileDRR);
        }

        return $dati['CartellaAllegati'] . '/' . $fileRapporto;
    }

    /**
     * Verifica se un passo ï¿½ stato eseguito
     * 
     * @param string $Proric_rec Record Proric della pratica
     * @param string $Ricite_rec Record Ricite del passo da verificare
     * @return booelan true se il passo ï¿½ stato eseguito,
     *         false in caso contrario
     */
    public function checkEsecuzionePasso($Proric_rec, $Ricite_rec) {
        return strpos($Proric_rec['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) === false ? false : true;
    }

    public function GetProcedimentiObbligatori($PRAM_DB, $Raccolta, $RICNUM, $PRANUM, $EVTCOD, $validate = true) {
        $sql = "SELECT
                    RICPRAOBB.*,
                    RICPRAOBB.OBBSUBPRA AS RICPRO,
                    RICPRAOBB.OBBSUBEVCOD AS RICEVE,
                    ITEEVT.*,
                    ITEEVT.ROWID AS ROWID_ITEEVT,
                    ANAATT.ATTDES,
                    ANASET.SETDES
                FROM
                    RICPRAOBB
                LEFT OUTER JOIN ITEEVT ON RICPRAOBB.OBBSUBPRA = ITEEVT.ITEPRA AND RICPRAOBB.OBBSUBEVCOD = ITEEVT.IEVCOD
                LEFT OUTER JOIN ANAPRA ON ITEEVT.ITEPRA = ANAPRA.PRANUM
                LEFT OUTER JOIN ANAATT ON ITEEVT.IEVATT = ANAATT.ATTCOD
                LEFT OUTER JOIN ANASET ON ITEEVT.IEVSTT = ANASET.SETCOD
                WHERE
                    OBBNUM = '$RICNUM'
                AND
                    OBBPRA = '$PRANUM'
                AND
                    OBBEVCOD = '$EVTCOD'
                AND
					ANAPRA.PRATPR = 'ONLINE' AND
                    ( ITEEVT.IEVDVA IS NULL OR ITEEVT.IEVDVA = '' OR ITEEVT.IEVDVA <= " . date('Ymd') . " ) AND
                    ( ITEEVT.IEVAVA IS NULL OR ITEEVT.IEVAVA = '' OR ITEEVT.IEVAVA >= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRADVA IS NULL OR ANAPRA.PRADVA = '' OR ANAPRA.PRADVA <= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRAAVA IS NULL OR ANAPRA.PRAAVA = '' OR ANAPRA.PRAAVA >= " . date('Ymd') . " ) AND
                    ITEEVT.IEVSTT != 0 AND ITEEVT.IEVTIP != '' AND
                    ANAPRA.PRAOFFLINE = 0
				GROUP BY
					RICPRAOBB.OBBSUBPRA, RICPRAOBB.OBBSUBEVCOD";

        $ricpraobb_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql);

        if ($validate === true) {
            foreach ($ricpraobb_tab as $k => $ricpraobb_rec) {
                /*
                 * Verifico validità espressione
                 */
                if ($ricpraobb_rec['OBBEXPRCTR'] != '' && !$this->evalExpression($Raccolta, $ricpraobb_rec['OBBEXPRCTR'])) {
                    unset($ricpraobb_tab[$k]);
                }
            }
        }

        return $ricpraobb_tab;
    }

    function ControllaRaccolteConfig($dati, $RiciteDistinta_rec, $PRAM_DB) {
        $arrayPassiDaFare = array();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            $sql = "SELECT	
                    RICITERACCOLTE.ITEKEY AS ITEKEY_RACCOLTA,
                    RICITERACCOLTE.ITESEQ AS ITESEQ_RACCOLTA,
                    RICITE.ITEKEY,
                    RICITE.ITEMETA,
                    RICITE.ITESEQ,
                    RICDAG.DAGKEY,
                    PRORIC.RICSEQ
                FROM
                    RICITE
                LEFT OUTER JOIN PRORIC PRORIC ON RICITE.RICNUM=PRORIC.RICNUM
                LEFT OUTER JOIN RICITE RICITERACCOLTE ON RICITERACCOLTE.RICNUM = PRORIC.RICNUM AND RICITERACCOLTE.ITEKEY<>RICITE.ITEKEY AND RICITE.RICNUM = '" . $RiciteDistinta_rec['RICNUM'] . "' 
                LEFT OUTER JOIN RICDAG RICDAG ON RICDAG.ITEKEY = RICITERACCOLTE.ITEKEY AND RICDAG.DAGNUM = '" . $RiciteDistinta_rec['RICNUM'] . "'
                WHERE 
                    RICITERACCOLTE.ITEPUB =1 AND RICITERACCOLTE.ITEDAT=1 AND RICITE.RICNUM = '" . $RiciteDistinta_rec['RICNUM'] . "' AND RICITE.ITEKEY = '" . $RiciteDistinta_rec['ITEKEY'] . "'  AND
                    RICITE.ITEMETA LIKE CONCAT('%',RICDAG.DAGKEY,'%')
                    AND RICITERACCOLTE.ITEKEY = '" . $ricite_rec['ITEKEY'] . "'
                ";
            $datiAggiutivi = ItaDB::DBSQLSelect($PRAM_DB, $sql);
            if ($datiAggiutivi) {
                foreach ($datiAggiutivi as $datoAggiutivo) {
                    $arrayPassiDaFare[$datoAggiutivo['ITEKEY_RACCOLTA']]['ITESEQ'] = $datoAggiutivo['ITESEQ_RACCOLTA'];
                    $arrayPassiDaFare[$datoAggiutivo['ITEKEY_RACCOLTA']]['RICOBL'] = $ricite_rec['RICOBL'];
                    $arrayPassiDaFare[$datoAggiutivo['ITEKEY_RACCOLTA']]['ITEOBE'] = $ricite_rec['ITEOBE'];
                }
            }
        }
//        $sql = "SELECT	
//                    RICITERACCOLTE.ITEKEY AS ITEKEY_RACCOLTA,
//                    RICITERACCOLTE.ITESEQ AS ITESEQ_RACCOLTA,
//                    RICITE.ITEKEY,
//                    RICITE.ITEMETA,
//                    RICITE.ITESEQ,
//                    RICDAG.DAGKEY,
//                    PRORIC.RICSEQ
//                FROM
//                    RICITE
//                LEFT OUTER JOIN PRORIC PRORIC ON RICITE.RICNUM=PRORIC.RICNUM
//                LEFT OUTER JOIN RICITE RICITERACCOLTE ON RICITERACCOLTE.RICNUM = PRORIC.RICNUM AND RICITERACCOLTE.ITEKEY<>RICITE.ITEKEY AND RICITE.RICNUM = '" . $Ricite_rec['RICNUM'] . "'
//                LEFT OUTER JOIN RICDAG RICDAG ON RICDAG.ITEKEY = RICITERACCOLTE.ITEKEY AND RICDAG.DAGNUM = '" . $Ricite_rec['RICNUM'] . "'
//                WHERE 
//                    RICITERACCOLTE.ITEPUB =1 AND RICITERACCOLTE.ITEDAT=1 AND RICITE.RICNUM = '" . $Ricite_rec['RICNUM'] . "' AND RICITE.ITEKEY = '" . $Ricite_rec['ITEKEY'] . "'  AND
//                    
//                    RICITE.ITEMETA LIKE CONCAT('%',RICDAG.DAGKEY,'%')";
//        $datiAggiutivi = ItaDB::DBSQLSelect($PRAM_DB, $sql);
//        foreach ($datiAggiutivi as $datoAggiutivo) {
//            $arrayPassiDaFare[$datoAggiutivo['ITEKEY_RACCOLTA']] = $datoAggiutivo['ITESEQ_RACCOLTA'];
//        }
        return $arrayPassiDaFare;
    }

    function checkPassiMancanti($arrayPassiRaccolte, $Proric_rec) {
        $manca = 0;
        krsort($arrayPassiRaccolte);
        foreach ($arrayPassiRaccolte as $value) {
            //if (strpos($Proric_rec['RICSEQ'], chr(46) . $ricite_rec['ITESEQ'] . chr(46)) === false) {
            //if (strpos($Proric_rec['RICSEQ'], chr(46) . $value['ITESEQ'] . chr(46)) === false && (($value['ITEOBL'] == 1 && $value['ITEOBE'] == "") || ($value['RICOBL'] == 1 && $value['ITEOBE']))) {
            if (strpos($Proric_rec['RICSEQ'], chr(46) . $value['ITESEQ'] . chr(46)) === false && ($value['ITEOBL'] == 1 || $value['RICOBL'] == 1)) {
                $manca = $manca + 1;
            }
        }
        return $manca;
    }

    function registraProcedimentiObbligatori($Itepraobb_tab, $Ricnum, $PRAM_DB) {
        foreach ($Itepraobb_tab as $Itepraobb_rec) {
            $ricPraObb = array();
            $ricPraObb = $Itepraobb_rec;
            $ricPraObb['OBBNUM'] = $Ricnum;
            unset($ricPraObb["ROWID"]);
            try {
                $nRows = ItaDB::DBInsert($PRAM_DB, "RICPRAOBB", 'ROWID', $ricPraObb);
            } catch (Exception $e) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0030', $e->getMessage() . " Pratica n. $Ricnum pratica obbligatoria=" . $Itepraobb_rec['OBBPRA'], __CLASS__);
                return false;
            }
        }
    }

    function GetMittenteProtocollo($Proric_rec, $PRAM_DB) {
        $Anades_rec_mittente = array();

        /*
         * Se ï¿½ un'integrazione, setto come Num Richiesta la richiesta Padre, cosï¿½ prendo i dati aggiuntivi del padre
         */
        $ricnum = $Proric_rec['RICNUM'];
        if ($Proric_rec['RICRPA']) {
            $ricnum = $Proric_rec['RICRPA'];
        }

        /*
         * Mittente standard preso da PRORIC
         */
        $Anades_rec_mittente['DESNOM'] = $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM'];
        $Anades_rec_mittente['DESNOME'] = $Proric_rec['RICNOM'];
        $Anades_rec_mittente['DESCOGNOME'] = $Proric_rec['RICCOG'];
        $Anades_rec_mittente['DESFIS'] = $Proric_rec['RICFIS']; // Qui prima usava $fiscale  corretto
        $Anades_rec_mittente['DESIND'] = $Proric_rec['RICVIA'];
        $Anades_rec_mittente['DESCAP'] = $Proric_rec['RICCAP'];
        $Anades_rec_mittente['DESCIT'] = $Proric_rec['RICCOM'];
        $Anades_rec_mittente['DESPRO'] = $Proric_rec['RICPRV'];
        $Anades_rec_mittente['DESEMA'] = $Proric_rec['RICEMA'];
        $Anades_rec_mittente['DESRUO'] = "0001";

        /*
         * Mi trovo il prefisso del soggetto dalle 3 preferenze nei parametri
         */
        $anapar_rec_mitt1 = $this->GetAnapar("FIRST_CHOICE_MITTPROT", "parkey", $PRAM_DB, false);
        if ($anapar_rec_mitt1['PARVAL']) {
            $prefisso1 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt1['PARVAL']);
        }
        $anapar_rec_mitt2 = $this->GetAnapar("SECOND_CHOICE_MITTPROT", "parkey", $PRAM_DB, false);
        if ($anapar_rec_mitt2['PARVAL']) {
            $prefisso2 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt2['PARVAL']);
        }
        $anapar_rec_mitt3 = $this->GetAnapar("THIRD_CHOICE_MITTPROT", "parkey", $PRAM_DB, false);
        if ($anapar_rec_mitt3['PARVAL']) {
            $prefisso3 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt3['PARVAL']);
        }

        /*
         * Inizializzo l'array dei dati del Mittente
         */
        $datiMittente = $datiMittente1 = $datiMittente2 = $datiMittente3 = array();

        /*
         * Se il prefisso ï¿½ valorizzato, mi trovo i Dati Aggiuntivi ad esso collegati
         */
        if ($prefisso1) {
            $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$ricnum' AND DAGKEY LIKE '$prefisso1" . "\_%' AND RICDAT <> ''", true);
            if ($ricdag_tab) {
                $datiMittente1 = $this->GetDatiMittente($ricdag_tab, $prefisso1);
            }
        }
        if ($prefisso2) {
            $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$ricnum' AND DAGKEY LIKE '$prefisso2" . "\_%' AND RICDAT <> ''", true);
            if ($ricdag_tab) {
                $datiMittente2 = $this->GetDatiMittente($ricdag_tab, $prefisso2);
            }
        }
        if ($prefisso3) {
            $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$ricnum' AND DAGKEY LIKE '$prefisso3" . "\_%' AND RICDAT <> ''", true);
            if ($ricdag_tab) {
                $datiMittente3 = $this->GetDatiMittente($ricdag_tab, $prefisso3);
            }
        }

        /*
         * Ora scelgo i datiMittente in base alla preferenze e al fatto se sono valorizzati i dati aggiuntivi per ciascun mittente
         */
        if ($datiMittente1) {
            $datiMittente = $datiMittente1;
        } else {
            if ($datiMittente2) {
                $datiMittente = $datiMittente2;
            } else {
                if ($datiMittente3) {
                    $datiMittente = $datiMittente3;
                }
            }
        }

        /*
         * Se i dati del mittente sono valorizzati, valorizzo $Anades_rec_mittente
         */
        if ($datiMittente) {
            /*
             * Valorizzo di default $desnom perchï¿½ ï¿½ un dato obbligatorio nel caso non mi tornino nessuno dei valori sottoelencati
             */
            $ricdag_tabTOTALI = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$ricnum' AND RICDAT <> ''", true);
            $desnom = $this->GetSostituzioneImpresa($ricdag_tabTOTALI);
            if ($datiMittente["ragSoc"]) {
                $desnom = $datiMittente["ragSoc"];
            } else {
                if ($datiMittente["cogNom"]) {
                    $desnom = $datiMittente["cogNom"];
                } else {
                    if ($datiMittente["descognome"]) {
                        $desnom = $datiMittente["descognome"] . " " . $datiMittente["desnome"];
                    }
                }
            }
            if ($datiMittente["resVia"]) {
                $indirizzo = $datiMittente["resVia"] . " " . $datiMittente["resCiv"];
            } else {
                $indirizzo = $datiMittente["sedeVia"] . " " . $datiMittente["sedeCiv"];
            }
            $Anades_rec_mittente['DESNOM'] = $desnom;
            $Anades_rec_mittente['DESNOME'] = $datiMittente["desnome"];
            $Anades_rec_mittente['DESCOGNOME'] = $datiMittente["descognome"] ? $datiMittente["descognome"] : $desnom;
            $Anades_rec_mittente['DESFIS'] = $datiMittente["fiscale"] ? $datiMittente["fiscale"] : $datiMittente["piva"];
            $Anades_rec_mittente['DESIND'] = $indirizzo;
            $Anades_rec_mittente['DESCAP'] = $datiMittente["resCap"] ? $datiMittente["resCap"] : $datiMittente["sedeCap"];
            $Anades_rec_mittente['DESCIT'] = $datiMittente["resCom"] ? $datiMittente["resCom"] : $datiMittente["sedeCom"];
            $Anades_rec_mittente['DESPRO'] = $datiMittente["resPro"] ? $datiMittente["resPro"] : $datiMittente["sedePro"];
            $Anades_rec_mittente['DESEMA'] = $datiMittente["pec"] ? $datiMittente["pec"] : $datiMittente["email"];
            $Anades_rec_mittente['DESRUO'] = $datiMittente["ruocod"];
        }

//        print_r("<pre>");
//        print_r($Anades_rec_mittente);
//        print_r("</pre>");
//        exit();
        return $Anades_rec_mittente;
    }

    function GetDatiMittente($ricdag_tab, $prefisso) {
        foreach ($ricdag_tab as $ricdag_rec) {
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_COGNOME_NOME") {
                $datiMittente["cogNom"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RAGIONESOCIALE") {
                $datiMittente["ragSoc"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_NOMEDITTA") {
                $datiMittente["ragSoc"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_COGNOME") {
                $datiMittente["descognome"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_NOME") {
                $datiMittente["desnome"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_CODICEFISCALE_CFI") {
                $datiMittente["fiscale"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_PARTITAIVA_PIVA") {
                $datiMittente["piva"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RESIDENZAVIA") {
                $datiMittente["resVia"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RESIDENZACIVICO") {
                $datiMittente["resCiv"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RESIDENZACAP_CAP") {
                $datiMittente["resCap"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RESIDENZACOMUNE") {
                $datiMittente["resCom"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_RESIDENZAPROVINCIA_PV") {
                $datiMittente["resPro"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_SEDEVIA") {
                $datiMittente["sedeVia"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_SEDECIVICO") {
                $datiMittente["sedeCiv"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_SEDECAP") {
                $datiMittente["sedeCap"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_SEDECOMUNE") {
                $datiMittente["sedeCom"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_SEDEPROVINCIA_PV") {
                $datiMittente["sedePro"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_EMAIL") {
                $datiMittente["email"] = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == $prefisso . "_PEC") {
                $datiMittente["pec"] = $ricdag_rec['RICDAT'];
            }
            $datiMittente["ruocod"] = praRuolo::$SISTEM_SUBJECT_ROLES[$prefisso]['RUOCOD'];
        }
        return $datiMittente;
    }

    function GetSostituzioneIndirizzo($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGVIA");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDELEGVIA");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDEVIA");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDEVIA");
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZAVIA");
                        if ($sostituzione == "") {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAESEC_SEDEVIA");
                        }
                    }
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzioneCivico($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGCIVICO");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDELEGCIVICO");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDECIVICO");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDECIVICO");
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZACIVICO");
                        if ($sostituzione == "") {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAESEC_SEDECIVICO");
                        }
                    }
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzioneComune($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDECOMUNE_LABEL");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGCOMUNE");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDELEGCOMUNE");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDECOMUNE");
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDECOMUNE");
                        if ($sostituzione == "") {
                            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZACOMUNE");
                        }
                    }
                }
            }
        }

        return $sostituzione;
    }

    function GetSostituzioneProvincia($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGPROVINCIA_PV");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDELEGPROVINCIA_PV");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDEPROVINCIA_PV");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDEPROVINCIA_PV");
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZAPROVINCIA_PV");
                    }
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzioneCap($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGCAP");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDELEGCAP");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDECAP");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_SEDECAP");
                    if ($sostituzione == "") {
                        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_RESIDENZACAP_CAP");
                    }
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzioneMail($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGEMAIL");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_EMAIL");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_EMAIL");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_EMAIL");
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzionePec($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_PEC");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_PEC");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_PEC");
            }
        }
        return $sostituzione;
    }

    function GetSostituzioneTelefono($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_SEDELEGTELEFONO");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_TELEFONO");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_TELEFONO");
                if ($sostituzione == "") {
                    $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_TELEFONO");
                }
            }
        }
        return $sostituzione;
    }

    function GetSostituzionePiva($Ricdag_tab) {
        $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESA_PARTITAIVA_PIVA");
        if ($sostituzione == "") {
            $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA");
            if ($sostituzione == "") {
                $sostituzione = $this->GetValueDatoAggiuntivo($Ricdag_tab, "DICHIARANTE_PARTITAIVA_PIVA");
            }
        }
        return $sostituzione;
    }

    function GetImgDocNews($ext) {
        switch ($ext) {
            case "pdf":
                $img = "pdf.jpg";
                break;
            case "doc":
            case "docx":
                $img = "word.jpg";
                break;
            case "xls":
            case "xlsx":
                $img = "excel.jpg";
                break;
            case "txt":
                $img = "txt.gif";
                break;
            case "P7M":
            case "p7m":
                $img = "firmato.jpg";
                break;
            default:
                $img = "file-256.jpg";
                break;
        }
        return $img;
    }

    function getEventId($pranum, $evento_procedimento, $eventoid_procedimento, $PRAM_DB) {
        if ($eventoid_procedimento) {
            return $eventoid_procedimento;
        } else {
            //$praLibEventi = new praLibEventi();
            //$iteevt_rec = $praLibEventi->getEvento($PRAM_DB, $pranum, $evento_procedimento);
            $iteevt_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ITEEVT WHERE ITEPRA='" . addslashes($pranum) . "' AND IEVCOD='" . addslashes($evento_procedimento) . "'", false);
            return $iteevt_rec['ROWID'];
        }
    }

    function getScuolaTrasporto($Ricdag_tab) {
        foreach ($Ricdag_tab as $key => $Ricdag_rec) {
            if ($Ricdag_rec['DAGKEY'] == "COMUNALE" && $Ricdag_rec['RICDAT']) {
                $codice = $Ricdag_rec['RICDAT'];
            }
            if ($Ricdag_rec['DAGKEY'] == "STATALE" && $Ricdag_rec['RICDAT']) {
                $codice = $Ricdag_rec['RICDAT'];
            }
            if ($Ricdag_rec['DAGKEY'] == "PRIMARIA" && $Ricdag_rec['RICDAT']) {
                $codice = $Ricdag_rec['RICDAT'];
            }
            if ($Ricdag_rec['DAGKEY'] == "TEMPO_PIENO" && $Ricdag_rec['RICDAT']) {
                $codice = $Ricdag_rec['RICDAT'];
            }
            if ($Ricdag_rec['DAGKEY'] == "SECONDARIA" && $Ricdag_rec['RICDAT']) {
                $codice = $Ricdag_rec['RICDAT'];
            }
        }
        foreach ($Ricdag_tab as $key => $Ricdag_rec) {
            $meta = unserialize($Ricdag_rec['DAGMETA']);
            if (isset($meta['ATTRIBUTICAMPO'])) {
                if ($meta['ATTRIBUTICAMPO']['RETURNVALUE'] == $codice) {
                    return $Ricdag_rec['DAGLAB'];
                }
            }
        }
    }

    function getWhereSportelli($sportelli) {
        $arrSportelli = explode(",", $sportelli);
        $last = end($arrSportelli);
        $whereSportelli = " AND (";
        foreach ($arrSportelli as $sportello) {
            $whereSportelli .= " IEVTSP = $sportello ";
            if ($sportello != $last) {
                $whereSportelli .= " OR ";
            }
        }
        $whereSportelli .= ")";
        return $whereSportelli;
    }

    function getP7mPathInfo($file) {
        $ext_1 = pathinfo($file, PATHINFO_EXTENSION);
        $retFile = pathinfo($file, PATHINFO_FILENAME);
        $ext_2 = '';
        if (strtoupper($ext_1) == 'P7M') {
            $ext_2 = pathinfo($retFile, PATHINFO_EXTENSION);
            $retFile = pathinfo($retFile, PATHINFO_FILENAME);
        }
        $ext_2 = $ext_2 ? $ext_2 . "." : $ext_2;

        $retExt = $ext_2 . $ext_1;
        return array('filename' => $retFile, 'extension' => $retExt);
    }

    function creaXmlInfocamere($dati, $filePath) {
        /*
         * Nome distinta
         */
        $distinta = pathinfo($filePath, PATHINFO_BASENAME) . ".001.MDA.PDF.P7M";

        /*
         * Mi trovo il passo di upload rapporto
         */
        foreach ($dati['Ricite_tab'] as $key => $ricite_rec) {
            if ($ricite_rec['ITEDRR'] == 1) {
                $ricite_recUplRapporto = $dati['Ricite_tab'][$key + 1];
            }
        }
        $ricdoc_rec = $this->GetRicdoc($ricite_recUplRapporto['ITEKEY'], "itekey", $dati['PRAM_DB'], false, $ricite_recUplRapporto['RICNUM']);
        if (!$ricdoc_rec) {
            return false;
        }
        $size = filesize($dati['CartellaAllegati'] . "/" . $ricdoc_rec['DOCUPL']);


        $di = $dati['dati_infocamere'];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<ps:riepilogo-pratica-suap xmlns:ps=\"http://www.impresainungiorno.gov.it/schema/suap/pratica\">";
        $xml .= "<info-schema versione=\"1.0.0\" data=\"2013-10-03+02:00\"/>\r\n";
        $xml .= "<intestazione>\r\n";
        $xml .= "<ufficio-destinatario codice-amministrazione=\"C_{$di['datiSportello']['codice_catastale_destinatario']}\" codice-aoo=\"C_{$di['datiSportello']['codice_catastale_destinatario']}\" identificativo-suap=\"{$di['datiSportello']['identificativo_suap']}\">{$di['datiSportello']['denominazione_suap']}</ufficio-destinatario>\r\n";
        /*
         * Dati impresa
         */
        $xml .= "<impresa>\r\n";
        $xml .= "<forma-giuridica codice=\"{$di['datiImpresa']['formaGiuridica_impresa']}\">{$di['datiImpresa']['formaGiuridica_impresa_descrizione']}</forma-giuridica>\r\n";
        $xml .= "<ragione-sociale>{$di['datiImpresa']['denominazione_impresa']}</ragione-sociale>\r\n";
        $xml .= "<codice-fiscale>{$di['datiImpresa']['codfis_suap']}</codice-fiscale>\r\n";
        $xml .= "<partita-iva>{$di['datiLegRapp']['legale_piva']}</partita-iva>\r\n";
        /*
         * Dati REA
         */
        if (!$di['datiImpresa']['provincia_rea'] && $di['datiImpresa']['data_iscrizione_rea'] && $di['datiImpresa']['codice_iscrizione_rea']) {
            $data_iscrizione_rea = $di['datiImpresa']['data_iscrizione_rea'];
            $data_iscrizione_rea = date("Y-m-d", strtotime($data_iscrizione_rea));
            $xml .= "<codice-REA provincia=\"{$di['datiImpresa']['provincia_rea']}\" data-iscrizione=\"$data_iscrizione_rea\">{$di['datiImpresa']['codice_iscrizione_rea']}</codice-REA>\r\n";
        }
        /*
         * Dati Indirizzo Impresa
         */
        $xml .= "<indirizzo>\r\n";
        $xml .= "   <stato codice=\"I\">ITALIA</stato>\r\n";
        $xml .= "   <provincia sigla=\"{$di['datiSedeLegale']['sedeLegale_provincia']}\" codice-istat=\"{$di['datiSedeLegale']['sedeLegale_provincia']}\">{$di['datiSedeLegale']['sedeLegale_descrizione_provincia']}</provincia>\r\n";
        $xml .= "   <comune codice-catastale=\"{$di['datiSedeLegale']['sedeLegale_codice_catastale']}\">{$di['datiSedeLegale']['sedeLegale_comune']}</comune>\r\n";
        $xml .= "   <cap>{$di['datiSedeLegale']['sedeLegale_cap']}</cap>\r\n";
        $xml .= "   <toponimo>{$di['datiSedeLegale']['sedeLegale_toponimo_indirizzo']}</toponimo>\r\n";
        $xml .= "   <denominazione-stradale>{$di['datiSedeLegale']['sedeLegale_denominazione_stradale_indirizzo']}</denominazione-stradale>\r\n";
        $xml .= "   <numero-civico>{$di['datiSedeLegale']['sedeLegale_civico']}</numero-civico>\r\n";
        $xml .= "</indirizzo>\r\n";
        /*
         * Fine Dati indirizzo
         */

        /*
         * Rappresentante legale
         */
        $xml .= "<legale-rappresentante>\r\n";
        $xml .= "   <cognome>{$di['datiLegRapp']['legale_cognome']}</cognome>\r\n";
        $xml .= "   <nome>{$di['datiLegRapp']['legale_nome']}</nome>\r\n";
        $xml .= "   <codice-fiscale>{$di['datiLegRapp']['legale_fiscale']}</codice-fiscale>\r\n";
        $xml .= "   <carica codice=\"{$di['datiLegRapp']['legale_carica']}\">{$di['datiLegRapp']['legale_carica_descrizione']}</carica>\r\n";
        $xml .= "</legale-rappresentante>\r\n";
        /*
         * Fine rappresentante Legale
         */
        $xml .= "</impresa>\r\n";
        /*
         * Fine Impresa
         */

        $xml .= "<oggetto-comunicazione tipo-procedimento=\"SCIA\" tipo-intervento=\"" . strtolower($di['datiAdempimento']['tipologia_segnalazione']) . "\">{$di['datiAdempimento']['nome_adempimento']}</oggetto-comunicazione>\r\n";
        //@TODO: normalizzare
        $xml .= "<codice-pratica>" . pathinfo($filePath, PATHINFO_BASENAME) . "</codice-pratica>";
        $xml .= "<dichiarante qualifica=\"{$di['datiLegRapp']['legale_qualifica']}\">\r\n";
        $xml .= "<cognome>{$di['datiLegRapp']['legale_cognome']}</cognome>\r\n";
        $xml .= "<nome>{$di['datiLegRapp']['legale_nome']}</nome>\r\n";
        $xml .= "<codice-fiscale>{$di['datiLegRapp']['legale_fiscale']}</codice-fiscale>\r\n";
        $xml .= "<pec>{$di['datiLegRapp']['legale_pec']}</pec>\r\n";
        $xml .= "<telefono>{$di['datiLegRapp']['legale_telefono']}</telefono>\r\n";
        $xml .= "</dichiarante>\r\n";
        $xml .= "<domicilio-elettronico>{$di['datiLegRapp']['legale_pec']}</domicilio-elettronico>\r\n";

        /*
         * Impianto Produttivo
         */

        $xml .= "<impianto-produttivo>\r\n";
        $xml .= "   <indirizzo>\r\n";
        $xml .= "       <stato codice=\"I\">ITALIA</stato>\r\n";
        $xml .= "       <provincia sigla=\"{$di['datiImpresa']['provincia_suap']}\" codice-istat=\"{$di['datiImpresa']['insProduttivo_istat_provincia']}\">{$comuni_recPRIns['PROVINCIA']}</provincia>\r\n";
        $xml .= "       <comune codice-catastale=\"{$di['datiImpresa']['insProduttivo_codice_catastale']}\">{$di['datiImpresa']['comune_suap']}</comune>\r\n";
        $xml .= "       <cap>{$di['datiImpresa']['cap_suap']}</cap>\r\n";
        $xml .= "       <toponimo>{$di['datiImpresa']['insProduttivo_toponimo_indirizzo']}</toponimo>\r\n";
        $xml .= "       <denominazione-stradale>{$di['datiImpresa']['insProduttivo_denominazione_stradale_indirizzo']}</denominazione-stradale>\r\n";
        $xml .= "       <numero-civico>{$di['datiImpresa']['num_civico_suap']}</numero-civico>\r\n";
        $xml .= "   </indirizzo>\r\n";
        $xml .= "</impianto-produttivo>\r\n";
        $xml .= "</intestazione>\r\n";
        $xml .= "<struttura>\r\n";
        $xml .= "<modulo nome=\"PDF ADEMPIMENTO\">\r\n";
        //$xml .= "<modulo nome=\"{$dati['Anaset_rec']['SETDES']}\">\r\n";
        $xml .= "<distinta-modello-attivita nome-file=\"$distinta\">\r\n";
        $xml .= "<descrizione>{$di['datiAdempimento']['nome_adempimento']}</descrizione>\r\n";
        $xml .= "<nome-file-originale>{$ricdoc_rec['DOCNAME']}</nome-file-originale>\r\n";
        $xml .= "<mime>application/pkcs7</mime>\r\n";
        $xml .= "<mime-base>application/pdf</mime-base>\r\n";
        $xml .= "<dimensione>$size</dimensione>\r\n";
        $xml .= "</distinta-modello-attivita>\r\n";

        foreach ($di['files']['allegati'] as $key => $alle) {
            $ricdoc_recAll = $this->GetRicdoc($alle['FILENAME'], "codice", $dati['PRAM_DB'], false, $ricite_recUplRapporto['RICNUM']);
            if (!$ricdoc_recAll) {
                return false;
            }
            $inc = str_pad($key + 1, 3, 0, STR_PAD_LEFT);
            $nomeFile = pathinfo($filePath, PATHINFO_BASENAME) . ".$inc.PDF.P7M";
            $xml .= "<documento-allegato nome-file=\"$nomeFile\">\r\n";
            $xml .= "<descrizione>{$alle['codice_e_descrizione']}</descrizione>\r\n";
            $xml .= "<nome-file-originale>{$ricdoc_recAll['DOCNAME']}</nome-file-originale>\r\n";
            $xml .= "<mime>application/pkcs7</mime>\r\n";
            $xml .= "<mime-base>application/pdf</mime-base>\r\n";
            $xml .= "<dimensione>" . filesize($alle['FILEPATH']) . "</dimensione>\r\n";
            $xml .= "</documento-allegato>\r\n";
        }


        $xml .= "</modulo>\r\n";
        $xml .= "</struttura>\r\n";
        $xml .= "</ps:riepilogo-pratica-suap>";
        return $xml;
    }

    function GetArrayFormaGiuridica() {
        $arrayOptions = array(
            array(
                "CODICE" => "XX",
                "DESCRIZIONE" => "NON PRECISATA",
            ),
            array(
                "CODICE" => "SZ",
                "DESCRIZIONE" => "SOCIETA' NON PREVISTA DALLA LEGISLAZIONE ITALIANA",
            ),
            array(
                "CODICE" => "SV",
                "DESCRIZIONE" => "SOCIETA' TRA AVVOCATI",
            ),
            array(
                "CODICE" => "SU",
                "DESCRIZIONE" => "SOCIETA' A RESPONSABILITA' LIMITATA CON UNICO SOCIO",
            ),
            array(
                "CODICE" => "ST",
                "DESCRIZIONE" => "SOGGETTO ESTERO",
            ),
            array(
                "CODICE" => "SS",
                "DESCRIZIONE" => "SOCIETA' COSTITUITA IN BASE A LEGGI DI ALTRO STATO",
            ),
            array(
                "CODICE" => "SR",
                "DESCRIZIONE" => "SOCIETA' A RESPONSABILITA' LIMITATA",
            ),
            array(
                "CODICE" => "SP",
                "DESCRIZIONE" => "SOCIETA' PER AZIONI",
            ),
            array(
                "CODICE" => "SO",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE PER AZIONI",
            ),
            array(
                "CODICE" => "SN",
                "DESCRIZIONE" => "SOCIETA' IN NOME COLLETTIVO",
            ),
            array(
                "CODICE" => "SM",
                "DESCRIZIONE" => "SOCIETA' DI MUTUO SOCCORSO",
            ),
            array(
                "CODICE" => "SL",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE A RESPONSABILITA' LIMITATA",
            ),
            array(
                "CODICE" => "SI",
                "DESCRIZIONE" => "SOCIETA' IRREGOLARE",
            ),
            array(
                "CODICE" => "SG",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA EUROPEA",
            ),
            array(
                "CODICE" => "SF",
                "DESCRIZIONE" => "SOCIETA' DI FATTO",
            ),
            array(
                "CODICE" => "SE",
                "DESCRIZIONE" => "SOCIETA' SEMPLICE",
            ),
            array(
                "CODICE" => "SD",
                "DESCRIZIONE" => "SOCIETA' EUROPEA",
            ),
            array(
                "CODICE" => "SC",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA",
            ),
            array(
                "CODICE" => "SA",
                "DESCRIZIONE" => "SOCIETA' ANONIMA",
            ),
            array(
                "CODICE" => "RS",
                "DESCRIZIONE" => "SOCIETA' A RESPONSABILITA' LIMITATA SEMPLIFICATA",
            ),
            array(
                "CODICE" => "RR",
                "DESCRIZIONE" => "SOCIETA' A RESPONSABILITA' LIMITATA A CAPITALE RIDOTTO",
            ),
            array(
                "CODICE" => "PS",
                "DESCRIZIONE" => "PICCOLA SOCIETA' COOPERATIVA A RESPONSABILITA' LIMITATA",
            ),
            array(
                "CODICE" => "PC",
                "DESCRIZIONE" => "PICCOLA SOCIETA' COOPERATIVA",
            ),
            array(
                "CODICE" => "PA",
                "DESCRIZIONE" => "ASSOCIAZIONE IN PARTECIPAZIONE",
            ),
            array(
                "CODICE" => "OS",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE COOPERATIVA A RESPONSABILITA' LIMITATA",
            ),
            array(
                "CODICE" => "OO",
                "DESCRIZIONE" => "COOPERATIVA SOCIALE",
            ),
            array(
                "CODICE" => "OC",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA CONSORTILE",
            ),
            array(
                "CODICE" => "MA",
                "DESCRIZIONE" => "MUTUA ASSICURAZIONE",
            ),
            array(
                "CODICE" => "LL",
                "DESCRIZIONE" => "AZIENDA SPECIALE DI CUI AL DLGS 267/2000",
            ),
            array(
                "CODICE" => "IR",
                "DESCRIZIONE" => "ISTITUTO RELIGIOSO",
            ),
            array(
                "CODICE" => "IF",
                "DESCRIZIONE" => "IMPRESA FAMILIARE",
            ),
            array(
                "CODICE" => "ID",
                "DESCRIZIONE" => "ISTITUTO DI CREDITO DI DIRITTO PUBBLICO",
            ),
            array(
                "CODICE" => "ID",
                "DESCRIZIONE" => "ISTITUTO DI CREDITO",
            ),
            array(
                "CODICE" => "GE",
                "DESCRIZIONE" => "GRUPPO EUROPEO DI INTERESSE ECONOMICO",
            ),
            array(
                "CODICE" => "FO",
                "DESCRIZIONE" => "FONDAZIONE",
            ),
            array(
                "CODICE" => "FI",
                "DESCRIZIONE" => "FONDAZIONE IMPRESA",
            ),
            array(
                "CODICE" => "ES",
                "DESCRIZIONE" => "ENTE DI CUI ALLA L.R. 21-12-93",
            ),
            array(
                "CODICE" => "ER",
                "DESCRIZIONE" => "ENTE ECCLESIASTICO CIVILMENTE RICONOSCIUTO",
            ),
            array(
                "CODICE" => "EP",
                "DESCRIZIONE" => "ENTE PUBBLICO ECONOMICO",
            ),
            array(
                "CODICE" => "EN",
                "DESCRIZIONE" => "ENTE",
            ),
            array(
                "CODICE" => "EM",
                "DESCRIZIONE" => "ENTE MORALE",
            ),
            array(
                "CODICE" => "EL",
                "DESCRIZIONE" => "ENTE SOCIALE",
            ),
            array(
                "CODICE" => "EI",
                "DESCRIZIONE" => "ENTE IMPRESA",
            ),
            array(
                "CODICE" => "EE",
                "DESCRIZIONE" => "ENTE ECCLESIASTICO",
            ),
            array(
                "CODICE" => "ED",
                "DESCRIZIONE" => "ENTE DIRITTO PUBBLICO",
            ),
            array(
                "CODICE" => "EC",
                "DESCRIZIONE" => "ENTE PUBBLICO COMMERCIALE",
            ),
            array(
                "CODICE" => "DI",
                "DESCRIZIONE" => "IMPRESA INDIVIDUALE",
            ),
            array(
                "CODICE" => "CZ",
                "DESCRIZIONE" => "CONSORZIO DI CUI AL DLGS 267/2000",
            ),
            array(
                "CODICE" => "CS",
                "DESCRIZIONE" => "CONSORZIO SENZA ATTIVITA' ESTERNA",
            ),
            array(
                "CODICE" => "CR",
                "DESCRIZIONE" => "CONSORZIO INTERCOMUNALE",
            ),
            array(
                "CODICE" => "CO",
                "DESCRIZIONE" => "CONSORZIO",
            ),
            array(
                "CODICE" => "CN",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE",
            ),
            array(
                "CODICE" => "CM",
                "DESCRIZIONE" => "CONSORZIO MUNICIPALE",
            ),
            array(
                "CODICE" => "CL",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA A RESPONSABILITA LIMITATA",
            ),
            array(
                "CODICE" => "CI",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA A RESPONSABILITA ILLIMITATA",
            ),
            array(
                "CODICE" => "CF",
                "DESCRIZIONE" => "CONSORZIO FIDI",
            ),
            array(
                "CODICE" => "CE",
                "DESCRIZIONE" => "COMUNIONE EREDITARIA",
            ),
            array(
                "CODICE" => "CC",
                "DESCRIZIONE" => "CONSORZIO CON ATTIVITA' ESTERNA",
            ),
            array(
                "CODICE" => "AZ",
                "DESCRIZIONE" => "AZIENDA SPECIALE",
            ),
            array(
                "CODICE" => "AU",
                "DESCRIZIONE" => "SOCIETA'  PER AZIONI CON SOCIO UNICO",
            ),
            array(
                "CODICE" => "AT",
                "DESCRIZIONE" => "AZIENDA AUTONOMA STATALE",
            ),
            array(
                "CODICE" => "AS",
                "DESCRIZIONE" => "SOCIETA' IN ACCOMANDITA SEMPLICE",
            ),
            array(
                "CODICE" => "AR",
                "DESCRIZIONE" => "AZIENDA REGIONALE",
            ),
            array(
                "CODICE" => "AP",
                "DESCRIZIONE" => "AZIENDA PROVINCIALE",
            ),
            array(
                "CODICE" => "AN",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE IN NOME COLLETTIVO",
            ),
            array(
                "CODICE" => "AM",
                "DESCRIZIONE" => "AZIENDA MUNICIPALE",
            ),
            array(
                "CODICE" => "AL",
                "DESCRIZIONE" => "AZIENDA SPECIALE DI ENTE LOCALE",
            ),
            array(
                "CODICE" => "AI",
                "DESCRIZIONE" => "ASSOCIAZIONE IMPRESA",
            ),
            array(
                "CODICE" => "AF",
                "DESCRIZIONE" => "ALTRE FORME",
            ),
            array(
                "CODICE" => "AE",
                "DESCRIZIONE" => "SOCIETA' CONSORTILE IN ACCOMANDITA SEMPLICE",
            ),
            array(
                "CODICE" => "AC",
                "DESCRIZIONE" => "ASSOCIAZIONE",
            ),
            array(
                "CODICE" => "AA",
                "DESCRIZIONE" => "SOCIETA' IN ACCOMANDITA PER AZIONI",
            ),
        );

        array_multisort($this->array_column($arrayOptions, 'DESCRIZIONE'), SORT_ASC, $arrayOptions);

        return $arrayOptions;
    }

    function GetArrayCariche() {
        $arrayOptions = array(
            array(
                "CODICE" => "ACP",
                "DESCRIZIONE" => "AMMINISTRATORE CON POSTILLA",
            ),
            array(
                "CODICE" => "ACR",
                "DESCRIZIONE" => "AMMINISTRATORE CON REQUISITI",
            ),
            array(
                "CODICE" => "ADP",
                "DESCRIZIONE" => "AMMINISTRATORE DELEGATO E PREPOSTO",
            ),
            array(
                "CODICE" => "AF",
                "DESCRIZIONE" => "AFFITTUARIO O CONDUTTORE",
            ),
            array(
                "CODICE" => "AFF",
                "DESCRIZIONE" => "AFFITTUARIO",
            ),
            array(
                "CODICE" => "AMD",
                "DESCRIZIONE" => "AMMINISTRATORE DELEGATO",
            ),
            array(
                "CODICE" => "AMG",
                "DESCRIZIONE" => "AMMINISTRATORE GIUDIZIARIO",
            ),
            array(
                "CODICE" => "AMM",
                "DESCRIZIONE" => "AMMINISTRATORE",
            ),
            array(
                "CODICE" => "AMP",
                "DESCRIZIONE" => "AMMINISTRATORE PROVVISORIO",
            ),
            array(
                "CODICE" => "AMS",
                "DESCRIZIONE" => "AMMINISTRATORE STRAORDINARIO",
            ),
            array(
                "CODICE" => "APR",
                "DESCRIZIONE" => "AMMINISTRATORE E PREPOSTO",
            ),
            array(
                "CODICE" => "ART",
                "DESCRIZIONE" => "AMMINISTRATORE E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "ASO",
                "DESCRIZIONE" => "SOCIO DELL'IMPRESA ARTIGIANA",
            ),
            array(
                "CODICE" => "ATI",
                "DESCRIZIONE" => "TITOLARE DELL'IMPRESA ARTIGIANA",
            ),
            array(
                "CODICE" => "AUN",
                "DESCRIZIONE" => "AMMINISTRATORE UNICO",
            ),
            array(
                "CODICE" => "AUP",
                "DESCRIZIONE" => "AMMINISTRATORE UNICO E PREPOSTO",
            ),
            array(
                "CODICE" => "CA",
                "DESCRIZIONE" => "CONDIRETTORE AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "CAD",
                "DESCRIZIONE" => "CAPO DELEGAZIONE",
            ),
            array(
                "CODICE" => "CAG",
                "DESCRIZIONE" => "CAPO AGENZIA",
            ),
            array(
                "CODICE" => "CC",
                "DESCRIZIONE" => "CONDIRETTORE CENTRALE",
            ),
            array(
                "CODICE" => "CCG",
                "DESCRIZIONE" => "COMITATO DI CONTROLLO GESTIONE",
            ),
            array(
                "CODICE" => "CD",
                "DESCRIZIONE" => "CONDIRETTORE",
            ),
            array(
                "CODICE" => "CDG",
                "DESCRIZIONE" => "CONSIGLIERE E DIRETTORE GENERALE",
            ),
            array(
                "CODICE" => "CDP",
                "DESCRIZIONE" => "CONSIGLIERE DELEGATO E PREPOSTO",
            ),
            array(
                "CODICE" => "CDS",
                "DESCRIZIONE" => "CONSIGLIERE DI SORVEGLIANZA",
            ),
            array(
                "CODICE" => "CDT",
                "DESCRIZIONE" => "CONSIGLIERE DELEGATO E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "CE",
                "DESCRIZIONE" => "CONDIRETTORE COMMERCIALE",
            ),
            array(
                "CODICE" => "CEP",
                "DESCRIZIONE" => "CONSIGLIERE E PREPOSTO",
            ),
            array(
                "CODICE" => "CES",
                "DESCRIZIONE" => "COLLABORATORE ESTERNO",
            ),
            array(
                "CODICE" => "CF",
                "DESCRIZIONE" => "CONDIRETTORE DI FILIALE",
            ),
            array(
                "CODICE" => "CG",
                "DESCRIZIONE" => "CONDIRETTORE GENERALE",
            ),
            array(
                "CODICE" => "CGE",
                "DESCRIZIONE" => "CONSIGLIERE DI GESTIONE",
            ),
            array(
                "CODICE" => "CGS",
                "DESCRIZIONE" => "CONSIGLIO DI GESTIONE",
            ),
            array(
                "CODICE" => "CI",
                "DESCRIZIONE" => "CONDIRETTORE INTERINALE",
            ),
            array(
                "CODICE" => "CLD",
                "DESCRIZIONE" => "COLLAUDATORE",
            ),
            array(
                "CODICE" => "CLR",
                "DESCRIZIONE" => "CONSIGLIERE E LEGALE RAPPRESENTANTE",
            ),
            array(
                "CODICE" => "CLT",
                "DESCRIZIONE" => "COLTIVATORE DIRETTO",
            ),
            array(
                "CODICE" => "CM",
                "DESCRIZIONE" => "COMMISSARIO MINISTERIALE",
            ),
            array(
                "CODICE" => "CMS",
                "DESCRIZIONE" => "COMMISSARIO STRAORDINARIO",
            ),
            array(
                "CODICE" => "CNG",
                "DESCRIZIONE" => "CONIUGE",
            ),
            array(
                "CODICE" => "COA",
                "DESCRIZIONE" => "COAMMINISTRATORE",
            ),
            array(
                "CODICE" => "COD",
                "DESCRIZIONE" => "CONSIGLIERE DELEGATO",
            ),
            array(
                "CODICE" => "COE",
                "DESCRIZIONE" => "COEREDE",
            ),
            array(
                "CODICE" => "COF",
                "DESCRIZIONE" => "COLLABORATORE FAMILIARE",
            ),
            array(
                "CODICE" => "COG",
                "DESCRIZIONE" => "COMMISSARIO GIUDIZIARIO",
            ),
            array(
                "CODICE" => "COL",
                "DESCRIZIONE" => "COMMISSARIO LIQUIDATORE",
            ),
            array(
                "CODICE" => "COM",
                "DESCRIZIONE" => "SOCIO",
            ),
            array(
                "CODICE" => "CON",
                "DESCRIZIONE" => "CONSIGLIERE",
            ),
            array(
                "CODICE" => "COO",
                "DESCRIZIONE" => "CONSIGLIERE ONORARIO",
            ),
            array(
                "CODICE" => "COP",
                "DESCRIZIONE" => "COMMISSARIO PREFETTIZIO",
            ),
            array(
                "CODICE" => "COS",
                "DESCRIZIONE" => "CONSIGLIERE SEGRETARIO",
            ),
            array(
                "CODICE" => "COT",
                "DESCRIZIONE" => "CONDUTTORE",
            ),
            array(
                "CODICE" => "COV",
                "DESCRIZIONE" => "COMMISSARIO GOVERNATIVO",
            ),
            array(
                "CODICE" => "CPC",
                "DESCRIZIONE" => "CAPOCANTIERE",
            ),
            array(
                "CODICE" => "CPR",
                "DESCRIZIONE" => "SOCIO COMPROPRIETARIO",
            ),
            array(
                "CODICE" => "CRT",
                "DESCRIZIONE" => "CURATORE",
            ),
            array(
                "CODICE" => "CS",
                "DESCRIZIONE" => "CONDIRETTORE DI STABILIMENTO",
            ),
            array(
                "CODICE" => "CSA",
                "DESCRIZIONE" => "CAPO SERVIZIO AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "CSG",
                "DESCRIZIONE" => "CUSTODE SEQUESTRO GIUDIZIARIO",
            ),
            array(
                "CODICE" => "CSS",
                "DESCRIZIONE" => "CONSIGLIERE DI SORVEGLIANZA SUPPLENTE",
            ),
            array(
                "CODICE" => "CST",
                "DESCRIZIONE" => "COMMISSARIO STRAORDINARIO",
            ),
            array(
                "CODICE" => "CT",
                "DESCRIZIONE" => "CONDIRETTORE TECNICO",
            ),
            array(
                "CODICE" => "CTE",
                "DESCRIZIONE" => "CONSIGLIERE E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "CU",
                "DESCRIZIONE" => "CAPO UFFICIO",
            ),
            array(
                "CODICE" => "CUE",
                "DESCRIZIONE" => "CURATORE DELLO EMANCIPATO",
            ),
            array(
                "CODICE" => "CUF",
                "DESCRIZIONE" => "CURATORE FALLIMENTARE",
            ),
            array(
                "CODICE" => "CUM",
                "DESCRIZIONE" => "CURATORE SPECIALE DI MINORE",
            ),
            array(
                "CODICE" => "CUV",
                "DESCRIZIONE" => "CAPUFFICIO VENDITE",
            ),
            array(
                "CODICE" => "CVE",
                "DESCRIZIONE" => "CONSIGLIO DI SORVEGLIANZA",
            ),
            array(
                "CODICE" => "CZ",
                "DESCRIZIONE" => "CONDIRETTORE DI ESERCIZIO",
            ),
            array(
                "CODICE" => "C01",
                "DESCRIZIONE" => "NOMINA AD AMMINISTRATORE DELEGATO",
            ),
            array(
                "CODICE" => "C02",
                "DESCRIZIONE" => "NOMINA AD AMMINISTRATORE GIUDIZIARIO",
            ),
            array(
                "CODICE" => "C03",
                "DESCRIZIONE" => "NOMINA AD AMMINISTRATORE",
            ),
            array(
                "CODICE" => "C07",
                "DESCRIZIONE" => "NOMINA AD AMMINISTRATORE UNICO",
            ),
            array(
                "CODICE" => "C08",
                "DESCRIZIONE" => "NOMINA A CONSIGLIERE DELEGATO",
            ),
            array(
                "CODICE" => "C09",
                "DESCRIZIONE" => "NOMINA A COMMISSARIO GIUDIZIARIO",
            ),
            array(
                "CODICE" => "C11",
                "DESCRIZIONE" => "NOMINA A COMMISSARIO LIQUIDATORE",
            ),
            array(
                "CODICE" => "C12",
                "DESCRIZIONE" => "NOMINA A CONSIGLIERE",
            ),
            array(
                "CODICE" => "C13",
                "DESCRIZIONE" => "NOMINA A LEGALE RAPPRESENTANTE DI SOCIETA'",
            ),
            array(
                "CODICE" => "C14",
                "DESCRIZIONE" => "NOMINA A LIQUIDATORE",
            ),
            array(
                "CODICE" => "C15",
                "DESCRIZIONE" => "NOMINA A LIQUIDATORE GIUDIZIARIO",
            ),
            array(
                "CODICE" => "C16",
                "DESCRIZIONE" => "NOMINA A LIQUIDATORE DI UNITA' LOCALE",
            ),
            array(
                "CODICE" => "C17",
                "DESCRIZIONE" => "NOMINA A MEMBRO COMITATO DIRETTIVO",
            ),
            array(
                "CODICE" => "C18",
                "DESCRIZIONE" => "NOMINA A MEMBRO COMITATO ESECUTIVO",
            ),
            array(
                "CODICE" => "C19",
                "DESCRIZIONE" => "NOMINA A PRESIDENTE CONSIGLIO AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "C20",
                "DESCRIZIONE" => "NOMINA A PRESIDENTE COMITATO DIRETTIVO",
            ),
            array(
                "CODICE" => "C21",
                "DESCRIZIONE" => "NOMINA A PRESIDENTE COMITATO ESECUTIVO",
            ),
            array(
                "CODICE" => "C22",
                "DESCRIZIONE" => "NOMINA A PRESIDENTE DEL COLLEGIO SINDACALE",
            ),
            array(
                "CODICE" => "C23",
                "DESCRIZIONE" => "NOMINA A PREPOSTO",
            ),
            array(
                "CODICE" => "C26",
                "DESCRIZIONE" => "NOMINA A RAPPRESENTANTE LEGALE DELLE SEDI SECONDARIE",
            ),
            array(
                "CODICE" => "C28",
                "DESCRIZIONE" => "NOMINA A SINDACO EFFETTIVO",
            ),
            array(
                "CODICE" => "C29",
                "DESCRIZIONE" => "NOMINA A SINDACO SUPPLENTE",
            ),
            array(
                "CODICE" => "C32",
                "DESCRIZIONE" => "NOMINA A VICE AMMINISTRATORE",
            ),
            array(
                "CODICE" => "C34",
                "DESCRIZIONE" => "NOMINA A VICE PRESIDENTE CONSIGLIO AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "DA",
                "DESCRIZIONE" => "DIRETTORE AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "DAM",
                "DESCRIZIONE" => "DIRIGENTE AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "DC",
                "DESCRIZIONE" => "DIRETTORE CENTRALE",
            ),
            array(
                "CODICE" => "DCO",
                "DESCRIZIONE" => "DIRIGENTE COMMERCIALE",
            ),
            array(
                "CODICE" => "DCP",
                "DESCRIZIONE" => "DIRIGENTE CON POTERE",
            ),
            array(
                "CODICE" => "DE",
                "DESCRIZIONE" => "DIRETTORE COMMERCIALE",
            ),
            array(
                "CODICE" => "DES",
                "DESCRIZIONE" => "DELEGATO ALLA SOMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "DF",
                "DESCRIZIONE" => "DIRETTORE DI FILIALE",
            ),
            array(
                "CODICE" => "DFI",
                "DESCRIZIONE" => "DIRETTORE FINANZE",
            ),
            array(
                "CODICE" => "DG",
                "DESCRIZIONE" => "DIRIGENTE GENERALE",
            ),
            array(
                "CODICE" => "DI",
                "DESCRIZIONE" => "DIRIGENTE INTERINALE",
            ),
            array(
                "CODICE" => "DIA",
                "DESCRIZIONE" => "DIRETTORE  ARTISTICO",
            ),
            array(
                "CODICE" => "DIM",
                "DESCRIZIONE" => "DIMISSIONARIO",
            ),
            array(
                "CODICE" => "DIP",
                "DESCRIZIONE" => "DIPENDENTE",
            ),
            array(
                "CODICE" => "DIR",
                "DESCRIZIONE" => "DIRIGENTE",
            ),
            array(
                "CODICE" => "DLF",
                "DESCRIZIONE" => "DELEGATO ALLA FIRMA",
            ),
            array(
                "CODICE" => "DL2",
                "DESCRIZIONE" => "DELEGATO DI CUI ART. 2 LEGGE 25/8/91 N.287",
            ),
            array(
                "CODICE" => "DMK",
                "DESCRIZIONE" => "DIRETTORE MARKETING",
            ),
            array(
                "CODICE" => "DNC",
                "DESCRIZIONE" => "DIP. DI IMPRESA AUTORIZ. AL SERV. NOLEG. CON CONDUCENTE",
            ),
            array(
                "CODICE" => "DP",
                "DESCRIZIONE" => "DIRETTORE PERSONALE",
            ),
            array(
                "CODICE" => "DR",
                "DESCRIZIONE" => "DIRETTORE",
            ),
            array(
                "CODICE" => "DRE",
                "DESCRIZIONE" => "DELEGATO ISCRIZIONE REC",
            ),
            array(
                "CODICE" => "DRG",
                "DESCRIZIONE" => "DIRETTORE REGIONALE",
            ),
            array(
                "CODICE" => "DRR",
                "DESCRIZIONE" => "DIRETTORE RESPONSABILE",
            ),
            array(
                "CODICE" => "DS",
                "DESCRIZIONE" => "DIRETTORE DI STABILIMENTO",
            ),
            array(
                "CODICE" => "DSA",
                "DESCRIZIONE" => "DELEGATO DI CUI ALL'ART. 2 DELLA LEGGE 287 DEL 25.8.1991",
            ),
            array(
                "CODICE" => "DT",
                "DESCRIZIONE" => "DIRETTORE TECNICO",
            ),
            array(
                "CODICE" => "DTD",
                "DESCRIZIONE" => "DELEGATO AL RITIRO CAPITALE VERSATO",
            ),
            array(
                "CODICE" => "DZ",
                "DESCRIZIONE" => "DIRETTORE DI ESERCIZIO",
            ),
            array(
                "CODICE" => "ELE",
                "DESCRIZIONE" => "ELETTORE",
            ),
            array(
                "CODICE" => "EXS",
                "DESCRIZIONE" => "EX SOCIO DI SOCIETA' DI PERSONE",
            ),
            array(
                "CODICE" => "FAT",
                "DESCRIZIONE" => "FATTORE DI CAMPAGNA",
            ),
            array(
                "CODICE" => "FC",
                "DESCRIZIONE" => "FAMILIARE COMPONENTE",
            ),
            array(
                "CODICE" => "FU",
                "DESCRIZIONE" => "FUNZIONARIO",
            ),
            array(
                "CODICE" => "GE",
                "DESCRIZIONE" => "GESTORE DELL' ESERCIZIO",
            ),
            array(
                "CODICE" => "GER",
                "DESCRIZIONE" => "GERENTE",
            ),
            array(
                "CODICE" => "GID",
                "DESCRIZIONE" => "GIUDICE DELEGATO",
            ),
            array(
                "CODICE" => "GOV",
                "DESCRIZIONE" => "GOVERNATORE",
            ),
            array(
                "CODICE" => "GSG",
                "DESCRIZIONE" => "GESTORE SEQUESTRO GIUDIZIARIO",
            ),
            array(
                "CODICE" => "IG",
                "DESCRIZIONE" => "ISPETTORE GENERALE",
            ),
            array(
                "CODICE" => "IMN",
                "DESCRIZIONE" => "IMPRESA MANDANTE",
            ),
            array(
                "CODICE" => "IMR",
                "DESCRIZIONE" => "IMPRESA MANDATARIA",
            ),
            array(
                "CODICE" => "IN",
                "DESCRIZIONE" => "INSTITORE",
            ),
            array(
                "CODICE" => "IS",
                "DESCRIZIONE" => "ISPETTORE",
            ),
            array(
                "CODICE" => "LER",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE",
            ),
            array(
                "CODICE" => "LGR",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE DI SOCIETA",
            ),
            array(
                "CODICE" => "LGT",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE INTESTATARIO DEL TESSERINO",
            ),
            array(
                "CODICE" => "LI",
                "DESCRIZIONE" => "LIQUIDATORE",
            ),
            array(
                "CODICE" => "LIG",
                "DESCRIZIONE" => "LIQUIDATORE GIUDIZIARIO",
            ),
            array(
                "CODICE" => "LRF",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE / FIRMATARIO",
            ),
            array(
                "CODICE" => "LRT",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "LR2",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE ART.2 L. 25/8/91 N.287",
            ),
            array(
                "CODICE" => "LSA",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE DI CUI ALL'ART. 2 DELLA LEGGE 287 DEL 25.8.1991",
            ),
            array(
                "CODICE" => "LUL",
                "DESCRIZIONE" => "LIQUIDATORE DI UNITA' LOCALE",
            ),
            array(
                "CODICE" => "MA",
                "DESCRIZIONE" => "MANDATARIO",
            ),
            array(
                "CODICE" => "MCA",
                "DESCRIZIONE" => "MEMBRO COMMISS. AMMINISTRATIVA",
            ),
            array(
                "CODICE" => "MCD",
                "DESCRIZIONE" => "MEMBRO COMITATO DIRETTIVO",
            ),
            array(
                "CODICE" => "MCE",
                "DESCRIZIONE" => "MEMBRO COMITATO ESECUTIVO",
            ),
            array(
                "CODICE" => "MCG",
                "DESCRIZIONE" => "MEMBRO COMITATO DI GESTIONE",
            ),
            array(
                "CODICE" => "MCS",
                "DESCRIZIONE" => "MEMBRO COMITATO DI SORVEGLIANZA",
            ),
            array(
                "CODICE" => "MCT",
                "DESCRIZIONE" => "MEMBRO DEL COMITATO STRATEGICO",
            ),
            array(
                "CODICE" => "MDC",
                "DESCRIZIONE" => "MEMBRO DI COMUNIONE EREDITARIA",
            ),
            array(
                "CODICE" => "MED",
                "DESCRIZIONE" => "MEMBRO EFFETTIVO CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "MGD",
                "DESCRIZIONE" => "MEMBRO CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "MGE",
                "DESCRIZIONE" => "MEMBRO GIUNTA ESECUTIVA",
            ),
            array(
                "CODICE" => "MGS",
                "DESCRIZIONE" => "MEMBRO DEL COMITATO DI CONTROLLO SULLA GESTIONE",
            ),
            array(
                "CODICE" => "MI",
                "DESCRIZIONE" => "MINORE RAPPRESENTATO DAL TUTORE",
            ),
            array(
                "CODICE" => "MP",
                "DESCRIZIONE" => "TIT*TMI*TPS*",
            ),
            array(
                "CODICE" => "MPP",
                "DESCRIZIONE" => "MADRE ESERCENTE LA PATRIA POTESTA'",
            ),
            array(
                "CODICE" => "MS",
                "DESCRIZIONE" => "MANDATO SPECIALE",
            ),
            array(
                "CODICE" => "MSD",
                "DESCRIZIONE" => "MEMBRO SUPPLENTE CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "NE",
                "DESCRIZIONE" => "NON ELETTORE",
            ),
            array(
                "CODICE" => "OAS",
                "DESCRIZIONE" => "ACCOMANDATARIO DI SAPA",
            ),
            array(
                "CODICE" => "OCA",
                "DESCRIZIONE" => "CONSIGLIO D'AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "OCO",
                "DESCRIZIONE" => "CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "ODI",
                "DESCRIZIONE" => "COMITATO DIRETTIVO",
            ),
            array(
                "CODICE" => "OES",
                "DESCRIZIONE" => "COMITATO ESECUTIVO",
            ),
            array(
                "CODICE" => "OPA",
                "DESCRIZIONE" => "PIU' AMMINISTRATORI",
            ),
            array(
                "CODICE" => "OPC",
                "DESCRIZIONE" => "PERSONA OPERANTE PER CONTO DELLA SOCIETA'",
            ),
            array(
                "CODICE" => "OPN",
                "DESCRIZIONE" => "PRESIDENTE DI CONSORZIO",
            ),
            array(
                "CODICE" => "PA",
                "DESCRIZIONE" => "PROCURATORE AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "PAD",
                "DESCRIZIONE" => "PRESIDENTE E AMMINISTRATORE DELEGATO",
            ),
            array(
                "CODICE" => "PAF",
                "DESCRIZIONE" => "PERSONA AUTORIZZATA ALLA FIRMA",
            ),
            array(
                "CODICE" => "PB",
                "DESCRIZIONE" => "PROCURATORE DI BORSA",
            ),
            array(
                "CODICE" => "PC",
                "DESCRIZIONE" => "PROCURATORE",
            ),
            array(
                "CODICE" => "PCA",
                "DESCRIZIONE" => "PRESIDENTE CONSIGLIO AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "PCD",
                "DESCRIZIONE" => "PRESIDENTE COMITATO DIRETTIVO",
            ),
            array(
                "CODICE" => "PCE",
                "DESCRIZIONE" => "PRESIDENTE COMITATO ESECUTIVO",
            ),
            array(
                "CODICE" => "PCG",
                "DESCRIZIONE" => "PRESIDENTE DEL COMITATO DI GESTIONE",
            ),
            array(
                "CODICE" => "PCM",
                "DESCRIZIONE" => "PRESIDENTE COMMISS. AMMINISTRATIVA",
            ),
            array(
                "CODICE" => "PCO",
                "DESCRIZIONE" => "PRESIDENTE CONSORZIO",
            ),
            array(
                "CODICE" => "PCP",
                "DESCRIZIONE" => "PROCURATORE CON POSTILLA",
            ),
            array(
                "CODICE" => "PCS",
                "DESCRIZIONE" => "PRESIDENTE DEL COLLEGIO SINDACALE",
            ),
            array(
                "CODICE" => "PCT",
                "DESCRIZIONE" => "PRESIDENTE DEL COMITATO DI CONTROLLO SULLA GESTIONE",
            ),
            array(
                "CODICE" => "PCV",
                "DESCRIZIONE" => "PRESIDENTE DEL CONSIGLIO DI SORVEGLIANZA",
            ),
            array(
                "CODICE" => "PDC",
                "DESCRIZIONE" => "PRESIDENTE E CONSIGLIERE DELEGATO",
            ),
            array(
                "CODICE" => "PDI",
                "DESCRIZIONE" => "RAPPRESENTANTE PREPOSTO ALLA DIPENDENZA IN ITALIA",
            ),
            array(
                "CODICE" => "PDS",
                "DESCRIZIONE" => "PROCURATORE DI SOCIETA' CON SOMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "PE",
                "DESCRIZIONE" => "PROCURATORE COMMERCIALE",
            ),
            array(
                "CODICE" => "PED",
                "DESCRIZIONE" => "PRESIDENTE EFFETTIVO CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "PEO",
                "DESCRIZIONE" => "PRESIDENTE E PREPOSTO",
            ),
            array(
                "CODICE" => "PEP",
                "DESCRIZIONE" => "PERSONA PREPOSTA UN DIPEND.",
            ),
            array(
                "CODICE" => "PES",
                "DESCRIZIONE" => "PREPOSTO ESERCIZIO",
            ),
            array(
                "CODICE" => "PF",
                "DESCRIZIONE" => "PROCURATORE DI FILIALE",
            ),
            array(
                "CODICE" => "PG",
                "DESCRIZIONE" => "PROCURATORE GENERALE",
            ),
            array(
                "CODICE" => "PGC",
                "DESCRIZIONE" => "PREPOSTO AL COMMERCIO INGROSSO SETTORE ALIMENTARE",
            ),
            array(
                "CODICE" => "PGD",
                "DESCRIZIONE" => "PRESIDENTE CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "PGE",
                "DESCRIZIONE" => "PRESIDENTE GIUNTA ESECUTIVA",
            ),
            array(
                "CODICE" => "PGF",
                "DESCRIZIONE" => "PREPOSTO ALLA GESTIONE TECNICA (D.M. N.221/2003)",
            ),
            array(
                "CODICE" => "PGS",
                "DESCRIZIONE" => "PRESIDENTE DEL CONSIGLIO DI GESTIONE",
            ),
            array(
                "CODICE" => "PGT",
                "DESCRIZIONE" => "PREPOSTO ALLA GESTIONE TECNICA AI SENSI DEL D.M. 274/97",
            ),
            array(
                "CODICE" => "PL",
                "DESCRIZIONE" => "PROCURATORE CENTRALE",
            ),
            array(
                "CODICE" => "PM",
                "DESCRIZIONE" => "PADRE O MADRE ESERCENTE LA PATRIA POTESTA'",
            ),
            array(
                "CODICE" => "PN",
                "DESCRIZIONE" => "PROCURATORE AD NEGOTIA",
            ),
            array(
                "CODICE" => "PP",
                "DESCRIZIONE" => "PROCURATORE SPECIALE",
            ),
            array(
                "CODICE" => "PPP",
                "DESCRIZIONE" => "PADRE ESERCENTE LA PATRIA POTESTA'",
            ),
            array(
                "CODICE" => "PPR",
                "DESCRIZIONE" => "PRESIDE PROTEMPORE",
            ),
            array(
                "CODICE" => "PR",
                "DESCRIZIONE" => "PROTUTORE",
            ),
            array(
                "CODICE" => "PRA",
                "DESCRIZIONE" => "PRESIDENTE AGGIUNTO",
            ),
            array(
                "CODICE" => "PRC",
                "DESCRIZIONE" => "PRESIDENTE DEI REVISORI DEI CONTI",
            ),
            array(
                "CODICE" => "PRE",
                "DESCRIZIONE" => "PRESIDENTE",
            ),
            array(
                "CODICE" => "PRO",
                "DESCRIZIONE" => "PRESIDENTE ONORARIO",
            ),
            array(
                "CODICE" => "PRP",
                "DESCRIZIONE" => "PROPRIETARIO",
            ),
            array(
                "CODICE" => "PRQ",
                "DESCRIZIONE" => "PROPRIETARIO AUTORIZZATO A RISCUOTERE E QUIETANZARE",
            ),
            array(
                "CODICE" => "PRS",
                "DESCRIZIONE" => "PREPOSTO",
            ),
            array(
                "CODICE" => "PRT",
                "DESCRIZIONE" => "PROCURATORE RESPONSABILE TECNICO SETTORE SPEDIZIONE",
            ),
            array(
                "CODICE" => "PS",
                "DESCRIZIONE" => "PROCURATORE SUPERIORE",
            ),
            array(
                "CODICE" => "PSD",
                "DESCRIZIONE" => "PRESIDENTE SUPPLENTE CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "PSE",
                "DESCRIZIONE" => "RAPPRESENTANTE PREPOSTO A SEDE SECONDARIA IN ITALIA",
            ),
            array(
                "CODICE" => "PSS",
                "DESCRIZIONE" => "PREPOSTO DELLA SEDE SECONDARIA",
            ),
            array(
                "CODICE" => "PT",
                "DESCRIZIONE" => "PROCURATORE TECNICO",
            ),
            array(
                "CODICE" => "PTE",
                "DESCRIZIONE" => "PRESIDENTE E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "PTR",
                "DESCRIZIONE" => "PREPOSTO ALLA GESTIONE TECNICA AI SENSI DELL'ART. 7",
            ),
            array(
                "CODICE" => "RA",
                "DESCRIZIONE" => "RESPONSABILE AGLI ACQUISTI",
            ),
            array(
                "CODICE" => "RAF",
                "DESCRIZIONE" => "RAPPRESENTATE LEGALE DI CUI ALL'ART. 93 DEL R.D. 18/6/1931 N. 773",
            ),
            array(
                "CODICE" => "RAP",
                "DESCRIZIONE" => "RAPPRESENTANTE LEGALE DI CUI ALL'ART. 93 DEL R.D. 18/6/1931 N. 773",
            ),
            array(
                "CODICE" => "RAS",
                "DESCRIZIONE" => "RAPPRESENTANTE STABILE",
            ),
            array(
                "CODICE" => "RAZ",
                "DESCRIZIONE" => "RAPPRESENTANTE DEGLI AZIONISTI",
            ),
            array(
                "CODICE" => "RC",
                "DESCRIZIONE" => "REVISORE DEI CONTI",
            ),
            array(
                "CODICE" => "RCD",
                "DESCRIZIONE" => "RECEDUTO",
            ),
            array(
                "CODICE" => "RCF",
                "DESCRIZIONE" => "RAPPRESENTANTE COMUNE PATRIMONI/FINANZIAMENTI",
            ),
            array(
                "CODICE" => "RCO",
                "DESCRIZIONE" => "RAPPRESENTANTE COMUNE OBBLIGAZIONISTI",
            ),
            array(
                "CODICE" => "RCS",
                "DESCRIZIONE" => "REVISORE DEI CONTI SUPPLENTE",
            ),
            array(
                "CODICE" => "RDF",
                "DESCRIZIONE" => "RESPONSABILE DI FILIALE",
            ),
            array(
                "CODICE" => "RE",
                "DESCRIZIONE" => "RESPONSABILE",
            ),
            array(
                "CODICE" => "RES",
                "DESCRIZIONE" => "RAPPRESENTANTE SOCIETA' ESTERA",
            ),
            array(
                "CODICE" => "RFM",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE FIGLIO MINORE",
            ),
            array(
                "CODICE" => "RG",
                "DESCRIZIONE" => "RAPPRESENTANTE ALLE GRIDA",
            ),
            array(
                "CODICE" => "RIN",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE DI INCAPACE",
            ),
            array(
                "CODICE" => "RIT",
                "DESCRIZIONE" => "RAPPRESENTANTE IN ITALIA",
            ),
            array(
                "CODICE" => "RPS",
                "DESCRIZIONE" => "RAPPRESENTANTE LEGGE P.S.",
            ),
            array(
                "CODICE" => "RSS",
                "DESCRIZIONE" => "RAPPRESENTANTE LEGALE DELLE SEDI SECONDARIE",
            ),
            array(
                "CODICE" => "RSU",
                "DESCRIZIONE" => "REVISORE UNICO",
            ),
            array(
                "CODICE" => "RTC",
                "DESCRIZIONE" => "RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "RV",
                "DESCRIZIONE" => "RESPONSABILE ALLE VENDITE",
            ),
            array(
                "CODICE" => "SA",
                "DESCRIZIONE" => "SOCIO ACCOMANDATARIO D'OPERA",
            ),
            array(
                "CODICE" => "SAB",
                "DESCRIZIONE" => "SOCIO ABILITATO",
            ),
            array(
                "CODICE" => "SAO",
                "DESCRIZIONE" => "SOCIO ACCOMANDATARIO D'OPERA",
            ),
            array(
                "CODICE" => "SAP",
                "DESCRIZIONE" => "SOCIO ACCOMANDATARIO E PREPOSTO",
            ),
            array(
                "CODICE" => "SCA",
                "DESCRIZIONE" => "SEGRETARIO DEL CONSIGLIO DI AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "SCR",
                "DESCRIZIONE" => "SOCIO RAPPRESENTANTE",
            ),
            array(
                "CODICE" => "SD",
                "DESCRIZIONE" => "SOCIETA' EUROPEA",
            ),
            array(
                "CODICE" => "SDR",
                "DESCRIZIONE" => "SOCIETA'DI REVISIONE",
            ),
            array(
                "CODICE" => "SEP",
                "DESCRIZIONE" => "SOCIO E PREPOSTO",
            ),
            array(
                "CODICE" => "SFC",
                "DESCRIZIONE" => "SOCIO CON FIRMA CONGIUNTA",
            ),
            array(
                "CODICE" => "SFI",
                "DESCRIZIONE" => "SOCIO FINANZIATORE",
            ),
            array(
                "CODICE" => "SG",
                "DESCRIZIONE" => "SOCIETA' COOPERATIVA EUROPEA",
            ),
            array(
                "CODICE" => "SGE",
                "DESCRIZIONE" => "SEGRETARIO GENERALE",
            ),
            array(
                "CODICE" => "SIE",
                "DESCRIZIONE" => "SINDACO EFFETTIVO",
            ),
            array(
                "CODICE" => "SIP",
                "DESCRIZIONE" => "SINDACO PROTEMPORE",
            ),
            array(
                "CODICE" => "SIS",
                "DESCRIZIONE" => "SINDACO SUPPLENTE",
            ),
            array(
                "CODICE" => "SLA",
                "DESCRIZIONE" => "SOCIO LAVORANTE",
            ),
            array(
                "CODICE" => "SLR",
                "DESCRIZIONE" => "SOCIO E LEGALE RAPPRESENTANTE",
            ),
            array(
                "CODICE" => "SNA",
                "DESCRIZIONE" => "SOCIO NON ABILITATO",
            ),
            array(
                "CODICE" => "SNC",
                "DESCRIZIONE" => "SOST. DIP. DI IMPRESA AUTORIZ. AL SERV. NOLEG. CON CONDUCENTE",
            ),
            array(
                "CODICE" => "SNP",
                "DESCRIZIONE" => "SOCIO CHE NON PARTECIPA ALLE LAVORAZIONI",
            ),
            array(
                "CODICE" => "SNQ",
                "DESCRIZIONE" => "SOCIO NON QUALIFICATO",
            ),
            array(
                "CODICE" => "SOA",
                "DESCRIZIONE" => "SOCIO AMMINISTRATORE",
            ),
            array(
                "CODICE" => "SOC",
                "DESCRIZIONE" => "SOCIO ACCOMANDANTE",
            ),
            array(
                "CODICE" => "SOF",
                "DESCRIZIONE" => "SOCIO DI SOCIETA' DI FATTO",
            ),
            array(
                "CODICE" => "SOL",
                "DESCRIZIONE" => "SOCIO ACCOMANDATARIO E RAPPRESENTANTE LEGALE",
            ),
            array(
                "CODICE" => "SON",
                "DESCRIZIONE" => "SOCIO DI SOCIETA' IN NOME COLLETTIVO",
            ),
            array(
                "CODICE" => "SOP",
                "DESCRIZIONE" => "SOCIO DI OPERA",
            ),
            array(
                "CODICE" => "SOR",
                "DESCRIZIONE" => "SOCIO ACCOMANDATARIO",
            ),
            array(
                "CODICE" => "SOS",
                "DESCRIZIONE" => "SOSTITUTO DEL TITOLARE",
            ),
            array(
                "CODICE" => "SOT",
                "DESCRIZIONE" => "SOCIO CONTITOLARE",
            ),
            array(
                "CODICE" => "SOU",
                "DESCRIZIONE" => "SOCIO UNICO",
            ),
            array(
                "CODICE" => "SPR",
                "DESCRIZIONE" => "SOCIO DI SOCIETA' DI PERSONE RAPPRES.",
            ),
            array(
                "CODICE" => "SQ",
                "DESCRIZIONE" => "SEQUESTRATARIO",
            ),
            array(
                "CODICE" => "SQU",
                "DESCRIZIONE" => "SOCIO QUALIFICATO",
            ),
            array(
                "CODICE" => "STE",
                "DESCRIZIONE" => "SOCIO E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "SVR",
                "DESCRIZIONE" => "SOVRINTENDENTE",
            ),
            array(
                "CODICE" => "TES",
                "DESCRIZIONE" => "TESORIERE",
            ),
            array(
                "CODICE" => "TI",
                "DESCRIZIONE" => "TITOLARE",
            ),
            array(
                "CODICE" => "TIT",
                "DESCRIZIONE" => "TITOLARE FIRMATARIO",
            ),
            array(
                "CODICE" => "TI2",
                "DESCRIZIONE" => "TITOLARE DI CUI ART. 2 LEGGE 25/8/91 N.287",
            ),
            array(
                "CODICE" => "TMI",
                "DESCRIZIONE" => "TITOLARE MARCHIO IDENTIFICATIVO",
            ),
            array(
                "CODICE" => "TPS",
                "DESCRIZIONE" => "TITOLARE DI LICENZA P.S.",
            ),
            array(
                "CODICE" => "TTE",
                "DESCRIZIONE" => "TITOLARE E RESPONSABILE TECNICO",
            ),
            array(
                "CODICE" => "TU",
                "DESCRIZIONE" => "TUTORE",
            ),
            array(
                "CODICE" => "UM1",
                "DESCRIZIONE" => "RAPPRESENTANTE LEGALE DI CUI ALL'ART. 2 LEGGE REG. N. 37 DEL 30/8/1988",
            ),
            array(
                "CODICE" => "UM2",
                "DESCRIZIONE" => "PREPOSTO DI CUI ALL'ART. 2 LEGGE RE. N. 37 DEL 30/8/1988",
            ),
            array(
                "CODICE" => "US",
                "DESCRIZIONE" => "USUFRUTTUARIO",
            ),
            array(
                "CODICE" => "VAD",
                "DESCRIZIONE" => "VICE AMMINISTRATORE DELEGATO",
            ),
            array(
                "CODICE" => "VCA",
                "DESCRIZIONE" => "VICE AMMINISTRATORE",
            ),
            array(
                "CODICE" => "VCD",
                "DESCRIZIONE" => "VICE CONSIGLIERE DELEGATO",
            ),
            array(
                "CODICE" => "VCG",
                "DESCRIZIONE" => "VICE COMMISSARIO GOVERNATIVO",
            ),
            array(
                "CODICE" => "VCO",
                "DESCRIZIONE" => "VICE COMMISSARIO STRAORDINARIO",
            ),
            array(
                "CODICE" => "VDA",
                "DESCRIZIONE" => "VICE DIRETTORE AMMINISTRATIVO",
            ),
            array(
                "CODICE" => "VDC",
                "DESCRIZIONE" => "VICE DIRETTORE CENTRALE",
            ),
            array(
                "CODICE" => "VDE",
                "DESCRIZIONE" => "VICE DIRETTORE COMMERCIALE",
            ),
            array(
                "CODICE" => "VDF",
                "DESCRIZIONE" => "VICE DIRETTORE DI FILIALE",
            ),
            array(
                "CODICE" => "VDG",
                "DESCRIZIONE" => "VICE DIRETTORE GENERALE",
            ),
            array(
                "CODICE" => "VDI",
                "DESCRIZIONE" => "VICE DIRETTORE INTERINALE",
            ),
            array(
                "CODICE" => "VDS",
                "DESCRIZIONE" => "VICE DIRETTORE DI STABILIMENTO",
            ),
            array(
                "CODICE" => "VDT",
                "DESCRIZIONE" => "VICE DIRETTORE TECNICO",
            ),
            array(
                "CODICE" => "VDZ",
                "DESCRIZIONE" => "VICE DIRETTORE DI ESERCIZIO",
            ),
            array(
                "CODICE" => "VED",
                "DESCRIZIONE" => "VICEPRESIDENTE EFFETTIVO CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "VGD",
                "DESCRIZIONE" => "VICEPRESIDENTE CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "VGE",
                "DESCRIZIONE" => "VICE GERENTE",
            ),
            array(
                "CODICE" => "VGO",
                "DESCRIZIONE" => "VICE GOVERNATORE",
            ),
            array(
                "CODICE" => "VIC",
                "DESCRIZIONE" => "VICE PRESIDENTE",
            ),
            array(
                "CODICE" => "VID",
                "DESCRIZIONE" => "VICE DIRETTORE",
            ),
            array(
                "CODICE" => "VIV",
                "DESCRIZIONE" => "VICE PRESIDENTE VICARIO",
            ),
            array(
                "CODICE" => "VPA",
                "DESCRIZIONE" => "VICE PRESIDENTE CONSIGLIO AMMINISTRAZIONE",
            ),
            array(
                "CODICE" => "VPC",
                "DESCRIZIONE" => "VICE PRESIDENTE E CONSIGLIERE DELEGATO",
            ),
            array(
                "CODICE" => "VPE",
                "DESCRIZIONE" => "VICE PRESID. GIUNTA ESECUTIVA",
            ),
            array(
                "CODICE" => "VPP",
                "DESCRIZIONE" => "VICE PRESIDENTE E PREPOSTO",
            ),
            array(
                "CODICE" => "VSD",
                "DESCRIZIONE" => "VICEPRESIDENTE SUPPLENTE CONSIGLIO DIRETTIVO",
            ),
            array(
                "CODICE" => "VSG",
                "DESCRIZIONE" => "VICE SEGRETARIO GENERALE",
            ),
            array(
                "CODICE" => "992",
                "DESCRIZIONE" => "POTERI DI AMMINISTRAZIONE E RAPPRESENTANZA DEI SOCI",
            ),
            array(
                "CODICE" => "996",
                "DESCRIZIONE" => "RIPARTIZIONE DEGLI UTILI E DELLE PERDITE TRA I SOCI",
            ),
            array(
                "CODICE" => "997",
                "DESCRIZIONE" => "LIMITAZIONE DI RESPONSABILITA' DEI SOCI",
            ),
            array(
                "CODICE" => "998",
                "DESCRIZIONE" => "POTERI DA STATUTO O DA PATTI SOCIALI",
            ),
            array(
                "CODICE" => "999",
                "DESCRIZIONE" => "POTERI DA STATUTO",
            ),
        );

        array_multisort($this->array_column($arrayOptions, 'DESCRIZIONE'), SORT_ASC, $arrayOptions);

        return $arrayOptions;
    }

    function GetArrayQualifiche() {
        $arrayOptions = array(
            array(
                "CODICE" => "ALTRO PREVISTO DALLA VIGENTE NORMATIVA",
                "DESCRIZIONE" => "ALTRO PREVISTO DALLA VIGENTE NORMATIVA",
            ),
            array(
                "CODICE" => "AMMINISTRATORE",
                "DESCRIZIONE" => "AMMINISTRATORE",
            ),
            array(
                "CODICE" => "ASSOCIAZIONE DI CATEGORIA",
                "DESCRIZIONE" => "ASSOCIAZIONE DI CATEGORIA",
            ),
            array(
                "CODICE" => "CENTRO ELABORAZIONE DATI",
                "DESCRIZIONE" => "CENTRO ELABORAZIONE DATI",
            ),
            array(
                "CODICE" => "COMMISSARIO GIUDIZIARIO",
                "DESCRIZIONE" => "COMMISSARIO GIUDIZIARIO",
            ),
            array(
                "CODICE" => "CONSULENTE",
                "DESCRIZIONE" => "CONSULENTE",
            ),
            array(
                "CODICE" => "CURATORE FALLIMENTARE",
                "DESCRIZIONE" => "CURATORE FALLIMENTARE",
            ),
            array(
                "CODICE" => "DELEGATO",
                "DESCRIZIONE" => "DELEGATO",
            ),
            array(
                "CODICE" => "LEGALE RAPPRESENTANTE",
                "DESCRIZIONE" => "LEGALE RAPPRESENTANTE",
            ),
            array(
                "CODICE" => "LIQUIDATORE",
                "DESCRIZIONE" => "LIQUIDATORE",
            ),
            array(
                "CODICE" => "NOTAIO",
                "DESCRIZIONE" => "NOTAIO",
            ),
            array(
                "CODICE" => "PROFESSIONISTA INCARICATO",
                "DESCRIZIONE" => "PROFESSIONISTA INCARICATO",
            ),
            array(
                "CODICE" => "SOCIO",
                "DESCRIZIONE" => "SOCIO",
            ),
            array(
                "CODICE" => "STUDIO ASSOCIATO",
                "DESCRIZIONE" => "STUDIO ASSOCIATO",
            ),
            array(
                "CODICE" => "TITOLARE",
                "DESCRIZIONE" => "TITOLARE",
            ),
        );

        array_multisort($this->array_column($arrayOptions, 'DESCRIZIONE'), SORT_ASC, $arrayOptions);

        return $arrayOptions;
    }

    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    public function getStatoPratica($Proric_rec, $PRAM_DB) {
        if ($Proric_rec['RICRPA']) {
            $Proges_rec_int = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA = '{$Proric_rec['RICRPA']}'", false);

            if ($Proges_rec_int) {
                $Propas_tab_integra = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '{$Proges_rec_int['GESNUM']}' AND PRORIN <> ''", true);

                foreach ($Propas_tab_integra as $Propas_rec_integra) {
                    if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                        $Pracom_rec_integra = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '{$Propas_rec_integra['PROPAK']}' AND COMTIP = 'A'", false);

                        if ($Pracom_rec_integra && $Pracom_rec_integra['COMPRT']) {
                            $statoPraticaProtocollo = substr($Pracom_rec_integra['COMPRT'], 4) . '/' . substr($Pracom_rec_integra['COMPRT'], 0, 4);
                        }

                        if ($Propas_rec_integra['PROINI']) {
                            $dataAcquisizione = frontOfficeLib::convertiData($Propas_rec_integra['PROINI']);
                            $statoPratica = 'Acquisita dall\'ente il ' . $dataAcquisizione;
                        }

                        if ($Propas_rec_integra['PROFIN']) {
                            $dataChiusura = frontOfficeLib::convertiData($Propas_rec_integra['PROFIN']);
                            $statoPratica = 'Chiusa il ' . $dataChiusura;
                        }
                    }
                }
            }
        } else {
            $Proges_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA = '{$Proric_rec['RICNUM']}'", false);

            if ($Proges_rec) {
                if ($Proges_rec['GESNPR']) {
                    $statoPraticaProtocollo = substr($Proges_rec['GESNPR'], 4) . '/' . substr($Proges_rec['GESNPR'], 0, 4);
                }

                $dataAcquisizione = frontOfficeLib::convertiData($Proges_rec['GESDRE']);

                if ($Proges_rec['GESDCH']) {
                    $dataChiusura = frontOfficeLib::convertiData($Proges_rec['GESDCH']);
                }

                $Prasta_rec = $this->GetPrasta($Proges_rec['GESNUM'], 'codice', $PRAM_DB);

                if ($Prasta_rec['STAPST'] != 0) {
                    if ($Proges_rec['GESCLOSE']) {
                        $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
                        $arrayDesc = explode(' - ', $Prasta_rec['STADEX']);
                        $lastDesc = end($arrayDesc);

                        $statoPratica = $Prasta_rec['STADES'] . ' - ' . $lastDesc;
                    } else {
                        $statoPratica = $Prasta_rec['STADES'];
                    }
                } else {
                    $statoPratica = "Acquisita dall'ente il $dataAcquisizione";

                    if ($Proges_rec['GESDCH']) {
                        $statoPratica = "Chiusa il $dataChiusura";
                    }
                }
            }
        }

        return $statoPratica;
    }

    public function checkBloccoIntegrazione($Proric_rec, $PRAM_DB) {
        $procIntegrazione = $this->GetProcedimentoIntegrazione($PRAM_DB);
        $anaparBlkIntegra_rec = $this->GetAnapar('BLOCK_INTEGRAZIONI', 'parkey', $PRAM_DB, false);

        $statoPratica = $this->getStatoPratica($Proric_rec, $PRAM_DB);

        $bloccaIntegrazioni = true;

        if (strpos($statoPratica, 'Chiusa') !== false) {
            if ($anaparBlkIntegra_rec['PARVAL'] == 'No') {
                $bloccaIntegrazioni = false;
            }
        } else {
            $bloccaIntegrazioni = false;
        }

        return in_array($Proric_rec['RICSTA'], array('01', '91')) &&
                $Proric_rec['RICRPA'] == '' &&
                $procIntegrazione &&
                $bloccaIntegrazioni === false;
    }

    function ribaltaDatoUtenteFO($Ricnum, $Itecod, $PRAM_DB, $campo, $value) {
        $retRibalta = array();
        $retRibalta['Status'] = "0";
        $retRibalta['Message'] = "Ribaltamento dati utente FO completato";
        //
        $ricdag_recCtr = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$Ricnum' AND DAGKEY = '$campo'", false);
        if (!$ricdag_recCtr) {
            $Ricdag_rec = array();
            $Ricdag_rec['DAGNUM'] = $Ricnum;
            $Ricdag_rec['ITECOD'] = $Itecod;
            $Ricdag_rec['ITEKEY'] = $Itecod;
            $Ricdag_rec['DAGKEY'] = $campo;
            $Ricdag_rec['DAGALIAS'] = $campo;
            $Ricdag_rec['DAGVAL'] = $value;
            $Ricdag_rec['RICDAT'] = $value;
            try {
                $nRows = ItaDB::DBInsert($PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
            } catch (Exception $e) {
                $retRibalta['Status'] = "-1";
                $retRibalta['Message'] = $e->getMessage();
                return $retRibalta;
            }
        }
        return $retRibalta;
    }

    public function GetTipoEnte($PRAM_DB) {
        $Filent_rec = $this->GetFilent(1, $PRAM_DB);
        if ($Filent_rec) {
            if ($Filent_rec['FILDE4'] == '') {
                return "M";
            }
            return $Filent_rec['FILDE4'];
        } else {
            return 'M';
        }
    }

    function MarcaturaAllegati($allegati, $dati, $tipo = "R") {
        $msgErr = "";
        switch ($tipo) {
            case "R":// Richiesta on-line

                /*
                 * Se c'è, marco l'allegato principale
                 */
                if (isset($allegati['arrayDoc']['Principale'])) {
                    $ricdoc_rec = $this->GetRicdoc($allegati['arrayDoc']['Principale']['ROWID'], "rowid", $dati['PRAM_DB']);
                    if ($ricdoc_rec) {
                        $ricdoc_rec['DOCPRT'] = 1;
                        //
                        try {
                            $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], "RICDOC", 'ROWID', $ricdoc_rec);
                        } catch (Exception $e) {
                            $msgErr .= "Errore marcatura allegato principale " . $allegati['arrayDoc']['Principale']['nomeFile'] . " della richiesta " . $dati['Proric_rec']['RICNUM'] . ".<br>";
                        }
                    } else {
                        $msgErr .= "Record allegato principale " . $allegati['arrayDoc']['Principale']['nomeFile'] . " della richiesta " . $dati['Proric_rec']['RICNUM'] . " non trovato.<br>";
                    }
                }

                /*
                 * Marco gli altri allegati
                 */
                foreach ($allegati['arrayDoc']['Allegati'] as $allegato) {
                    $ricdoc_rec = $this->GetRicdoc($allegato['ROWID'], "rowid", $dati['PRAM_DB']);
                    if ($ricdoc_rec) {
                        $ricdoc_rec['DOCPRT'] = 1;
                        //
                        try {
                            $nRows = ItaDB::DBUpdate($dati['PRAM_DB'], "RICDOC", 'ROWID', $ricdoc_rec);
                        } catch (Exception $e) {
                            $msgErr .= "Errore marcatura allegato " . $allegato['nomeFile'] . " della richiesta " . $dati['Proric_rec']['RICNUM'] . ".<br>";
                        }
                    } else {
                        $msgErr .= "Record allegato " . $allegato['nomeFile'] . " della richiesta " . $dati['Proric_rec']['RICNUM'] . " non trovato.<br>";
                    }
                }
                break;
            default:
                break;
        }
        return $msgErr;
    }

    function checkTariffa($dati) {
        $msg = "";
        foreach ($dati['Ricite_tab'] as $ricite_rec) {
            $itelis_rec = $this->GetTariffaPasso($ricite_rec, $dati['PRAM_DB'], false);
            if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $ricite_rec['ITESEQ'] . chr(46)) !== false && $itelis_rec) {
                if ($ricite_rec['TARIFFA'] != $itelis_rec['IMPORTO']) {
                    $msg .= "L'importo  " . $itelis_rec['DESCRIZIONE'] . " è cambiato da Euro " . number_format($ricite_rec['TARIFFA'], 2, ',', '.') . " a Euro " . number_format($itelis_rec['IMPORTO'], 2, ',', '.') . "<br>";
                }
            }
        }
        return $msg;
    }

    public function ElaboraProgesSerie($gesnum, $sigla = '', $anno = '', $numero = '', $PRAM_DB = '', $PROT_DB = '') {
        if (!$sigla || !$numero || !$anno) {
            $proges_rec = $this->GetProges($gesnum, "codice", false, $PRAM_DB);    // mi ricavo serie e anno da gesnum
            $numero = $proges_rec['SERIEPROGRESSIVO'];
            $anno = $proges_rec['SERIEANNO'];
            $sigla = $proges_rec['SERIECODICE'];
        }
        $decod_sigla = $this->getAnaseriearc($sigla, "codice", $PROT_DB);  // decodifica codice con sigla della serie
        return $decod_sigla['SIGLA'] . "/" . $numero . "/" . $anno;
    }

    function checkIconAnnInt($Proric_rec, $PRAM_DB) {
        $hide = false;
        if ($Proric_rec['RICRUN']) {
            $hide = true;
            $proric_rec_padre = $this->GetProric($Proric_rec['RICRUN'], "codice", $PRAM_DB);
            if ($proric_rec_padre['RICSTA'] == "99") {
                $hide = false;
            }
        }
        return $hide;
    }

    public function checkExistCatasto() {
        try {
            $CATA_DB = ItaDB::DBOpen('CATA', frontOfficeApp::getEnte());
            $record = ItaDB::DBSQLSelect($CATA_DB, "SHOW TABLES FROM " . $CATA_DB->getDB() . " LIKE 'LEGAME'");
        } catch (Exception $exc) {
            return false;
        }

        if ($CATA_DB == "" || !$record) {
            return false;
        }
        return $CATA_DB;
    }

    public function BloccaRichiesta($dati, $differita = false) {
        /*
         * Blocco la Richiesta
         */

        if ($dati["Proric_rec"]['RICSTA'] == '99') {
            $dati["Proric_rec"]['RICSTA'] = '01';
            $dati["Proric_rec"]['RICDAT'] = date("Ymd");
            $dati["Proric_rec"]['RICTIM'] = date("H:i:s");

            if ($differita == true) {
                $dati["Proric_rec"]['RICDATARPROT'] = date("Ymd");
                $dati["Proric_rec"]['RICORARPROT'] = date("H:i:s");
            }

            if (strpos($dati["Proric_rec"]['RICSEQ'], "." . $dati['seq'] . ".") === false) {
                $dati["Proric_rec"]['RICSEQ'] = $dati["Proric_rec"]['RICSEQ'] . "." . $dati['seq'] . ".";
            }

            try {
                ItaDB::DBUpdate($dati['PRAM_DB'], "PRORIC", 'ROWID', $dati["Proric_rec"]);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage($e->getMessage() . " Errore aggiornamento su PRORIC della pratica n. " . $dati["Proric_rec"]['RICNUM']);
                return false;
            }
        }

        return true;
    }

    public function acquisizioneAutomaticaRichiesta($codiceRichiesta, $parametriAcquisizione) {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praWsFrontOffice.class.php';

        $praWsFrontOffice = new praWsFrontOffice;

        $praWsFrontOffice->setWebservices_uri($parametriAcquisizione['URI']);
        $praWsFrontOffice->setWebservices_wsdl($parametriAcquisizione['WSDL']);
        $praWsFrontOffice->setNamespace($parametriAcquisizione['NAMESPACE']);
        $praWsFrontOffice->setTimeout(1200);

        $resultGetItaEngineContextToken = $praWsFrontOffice->ws_GetItaEngineContextToken(array(
            'userName' => $parametriAcquisizione['UTENTE'],
            'userPassword' => $parametriAcquisizione['PASSWORD'],
            'domainCode' => frontOfficeApp::getEnte()
        ));

        if (!$resultGetItaEngineContextToken) {
            if ($praWsFrontOffice->getFault()) {
                $this->errCode = -1;
                $this->errMessage = "Fault chiamata 'ws_GetItaEngineContextToken': " . $praWsFrontOffice->getFault();
            } elseif ($praWsFrontOffice->getError()) {
                $this->errCode = -1;
                $this->errMessage = "Errore chiamata 'ws_GetItaEngineContextToken': " . $praWsFrontOffice->getError();
            }

            return false;
        }

        $contextToken = $praWsFrontOffice->getResult();

        if (!$contextToken) {
            $this->errCode = -1;
            $this->errMessage = "Errore chiamata 'ws_GetItaEngineContextToken': risposta non valida.";
            return false;
        }

        $numeroRichiesta = substr($codiceRichiesta, 4);
        $annoRichiesta = substr($codiceRichiesta, 0, 4);

        $wsAcquisisciRichiesta = $praWsFrontOffice->ws_acquisisciRichiesta(array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => frontOfficeApp::getEnte(),
            'numeroRichiesta' => $numeroRichiesta,
            'annoRichiesta' => $annoRichiesta
        ));

        if (!$wsAcquisisciRichiesta) {
            if ($praWsFrontOffice->getFault()) {
                $this->errCode = -1;
                $this->errMessage = "Fault chiamata 'ws_acquisisciRichiesta': " . $praWsFrontOffice->getFault();
            } elseif ($praWsFrontOffice->getError()) {
                $this->errCode = -1;
                $this->errMessage = "Errore chiamata 'ws_acquisisciRichiesta': " . $praWsFrontOffice->getError();
            }

            return false;
        }

        $resultAcquisisciRichiesta = $praWsFrontOffice->getResult();

        if (!$resultAcquisisciRichiesta) {
            $this->errMessage = "Errore chiamata 'ws_acquisisciRichiesta': risposta non valida.";
            return false;
        }

        $praWsFrontOffice->ws_DestroyItaEngineContextToken(array(
            'token' => $contextToken,
            'domainCode' => frontOfficeApp::getEnte()
        ));

        $xmlResponse = base64_decode($resultAcquisisciRichiesta);

        if (!$xmlResponse) {
            $this->errCode = -1;
            $this->errMessage = "Errore in decodifica risposta 'ws_acquisisciRichiesta'";
            return false;
        }

        return true;
    }

    public function elaboraOggettoRichiesta($dati) {
        $oggettoTemplate = $dati['Anapra_rec']['PRAOGGTML'];
        if (!$oggettoTemplate) {
            return true;
        }

        $praVar = new praVars();
        $praVar->setPRAM_DB($dati['PRAM_DB']);
        $praVar->setGAFIERE_DB($dati['GAFIERE_DB']);
        $praVar->setDati($dati);
        $praVar->loadVariabiliRichiesta();

        $dati['Proric_rec']['RICOGG'] = $this->valorizzaTemplate($oggettoTemplate, $praVar->getVariabiliRichiesta()->getAllData());

        try {
            ItaDB::DBUpdate($dati['PRAM_DB'], 'PRORIC', 'ROWID', $dati['Proric_rec']);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }

        return true;
    }

    public function CreaPdfDistintaDocx($dati, $PassoDistinta) {
        require_once ITA_LIB_PATH . '/itaPHPCore/itaSmarty.class.php';
        require_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

        $praVar = new praVars();
        $praVar->setPRAM_DB($dati['PRAM_DB']);
        $praVar->setGAFIERE_DB($dati['GAFIERE_DB']);
        $praVar->setDati($dati);
        $praVar->loadVariabiliRichiesta();
        $praVar->loadVariabiliDistinta();

        /*
         * Carico il dizionario Parent per pratiche uniche
         */
        if ($dati['Proric_rec']['RICRUN']) {
            $objTest = praLibDati::getInstance($this);
            $datiParent = $objTest->prendiDati($dati['Proric_rec']['RICRUN'], '', '');
            $praVar->loadVariabiliParent($datiParent);
        }

        /*
         * Carico il dizionario Parent per pratiche collegate
         */
        if ($dati['Proric_rec']['RICRPA']) {
            $objTest = praLibDati::getInstance($this);
            $datiParent = $objTest->prendiDati($dati['Proric_rec']['RICRPA'], '', '');
            $praVar->loadVariabiliParent($datiParent);
        }

        $DocumentDOCX = itaDocumentFactory::getDocument('DOCX');
        $DictionaryData = $praVar->getVariabiliRichiesta()->getAllData();

        $DocumentDOCX->setDictionary($DictionaryData);

        if (!$DocumentDOCX->loadContent($dati['CartellaRepository'] . "/testiAssociati/" . $PassoDistinta['ITEWRD'])) {
            $this->errCode = -1;
            $this->errMessage = 'Caricamento DOCX fallito: ' . $DocumentDOCX->getMessage();
            return false;
        }

        if (!$DocumentDOCX->mergeDictionary()) {
            $this->errCode = -1;
            $this->errMessage = 'Compilazione DOCX fallita: ' . $DocumentDOCX->getMessage();
            return false;
        }

        $filledDOCX = $dati['CartellaRepository'] . "/testiAssociati/filled_" . $PassoDistinta['ITEWRD'];

        $DocumentDOCX->saveContent($filledDOCX, true);

        $fileDistinta = $dati['CartellaAllegati'] . '/' . $dati['Proric_rec']['RICNUM'] . '_C' . str_pad($PassoDistinta['ITESEQ'], $dati['seqlen'], "0", STR_PAD_LEFT) . '.pdf';

        $resultConvert = frontOfficeLib::docx2Pdf($filledDOCX, $fileDistinta);
        if ($resultConvert['STATO'] == 'KO') {
            $this->errCode = -1;
            $this->errMessage = 'Conversione DOCX fallita: ' . $resultConvert['MESSAGGIO'];
            return false;
        }

        return $fileDistinta;
    }

    public function htmlspecialchars_recursive($array) {
        $array_htmlspecialchars = array();
        foreach ($array as $campo => $value) {
            if (is_array($value)) {
                $array_htmlspecialchars[$campo] = $this->htmlspecialchars_recursive($value);
            } else {
                $array_htmlspecialchars[$campo] = htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
            }
        }
        return str_replace("\\n", chr(13), $array_htmlspecialchars);
    }

    public function getReadOnly($dati, $dagrol) {
        $trovato = false;
        $readonly = false;
        foreach ($dati['passiDisponibili'] as $passo) {
            if ($passo['ROWID'] == $dati['Ricite_rec']['ROWID']) {
                $trovato = true;
                break;
            }
        }
        
        $ricdat = $dati['ricdat'];
        if (isset($dati['ReadOnly'])){
            $ricdat = $dati['ReadOnly'];
        }
        
        if ($dagrol == 1 || $ricdat == 1 || ($dati['Consulta'] && !$trovato)) {
            $readonly = true;
        }
        return $readonly;
    }

    public function isEnableScadenza(){
        $attivaScadenza = false;
        /*
         * Controllo se attivata voce "SCADRICATTIVA" nella tabella ENV_CONFIG in ITAFRONOFFICE
         */
        $frontOfficeLib = new frontOfficeLib();
        $ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
        
        $envconfig_tab = $frontOfficeLib->getEnv_config("SCADENZARICHIESTAONLINE", $ITAFRONTOFFICE_DB);
        if ($envconfig_tab) {
            foreach ($envconfig_tab as $envconfig_rec) {

                if ($envconfig_rec['CHIAVE'] == "SCADRICATTIVA") {
                    if ($envconfig_rec['CONFIG'] == 'Si') {
                        $attivaScadenza = true;
                    }
                    break;
                }
            }
        }
        
        return $attivaScadenza;
    }
    
    public function getGiorniScadenza($ricTsp){
        $gg = 0;
        
        /*
         * Si controlla se configurati giorni nello sportello online della richiesta
         */
        $PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        $anatsp_rec = $this->GetAnatsp($ricTsp, "codice", $PRAM_DB);
        if ($anatsp_rec){
            $gg = $anatsp_rec['TSPGGSCAD'];
        }
        
        if (!$gg || $gg < 1){

            $frontOfficeLib = new frontOfficeLib();
            $ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());

            $envconfig_tab = $frontOfficeLib->getEnv_config("SCADENZARICHIESTAONLINE", $ITAFRONTOFFICE_DB);
            if ($envconfig_tab) {
                foreach ($envconfig_tab as $envconfig_rec) {

                    if ($envconfig_rec['CHIAVE'] == "SCADRICGIORNI") {
                        if ($envconfig_rec['CONFIG']){
                            $gg = $envconfig_rec['CONFIG'];
                        }
                        break;
                    }
                }
            }
            
        }
        
        if (!$gg || $gg == ''){
            $gg = 0;
        }
        
        return $gg;
    }

    public function getDataScadenza($Proric_rec){
        $dataScad = '';
        if ($this->isEnableScadenza() && $Proric_rec['RICSTA'] == 99){        
            $gg = $this->getGiorniScadenza($Proric_rec['RICTSP']);
            if ($gg > 0){
                $dataScad = frontOfficeLib::addDayToDate($Proric_rec['RICDRE'], "Ymd", $gg);
//                $dataScad = frontOfficeLib::convertiData($dataScad_tmp);
            }
        }
        return $dataScad;
    }

    public function isRichiestaScaduta($Proric_rec){
        $scaduta = false;
        $dataScad = $this->getDataScadenza($Proric_rec);
        if ($dataScad){
            if ($dataScad < date('Ymd') && $Proric_rec['RICFORZAINVIO'] == 0){
                $scaduta = true;
            }
        }
        
        return $scaduta;
    }
    
}
