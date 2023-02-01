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
 * @version    23.12.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Documenti/docLib.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once (ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php');
include_once (ITA_BASE_PATH . '/apps/Mail/emlLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Base/basLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_LIB_PATH . '/itaPHPPaleo/itaOperatorePaleo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

class praLib {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $PRAM_DB;
    public $ITW_DB;
    public $ITALWEB_DB;
    public $ITALWEB;
    public $ITAFO_DB;
    public $accLib;
    public $devLib;
    public $basLib;
    public $proLib;
    private $errMessage;
    private $errCode;
    static public $TIPO_SEGNALAZIONE = array(
        '' => 'Nessuna',
        'ALTRO' => 'Altro',
        'APERTURA' => 'Apertura',
        'CESSAZIONE' => 'Cessazione',
        'MODIFICHE' => 'Modifiche',
        'SUBENTRO' => 'Subentro',
        'TRASFORMAZIONE' => 'Trasformazione',
        'FIERE' => 'Fiera',
    );

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                $this->ITW_DB = ItaDB::DBOpen('ITW', $ditta);
                $this->ITAFO_DB = ItaDB::DBOpen('ITAFRONTOFFICE', $ditta);
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB', $ditta);
                $this->proLib = new proLib($ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                $this->ITW_DB = ItaDB::DBOpen('ITW');
                $this->ITAFO_DB = ItaDB::DBOpen('ITAFRONTOFFICE');
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
                $this->proLib = new proLib();
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
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

    public function setPRAMDB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function setITAFODB($ITAFO_DB) {
        $this->ITAFO_DB = $ITAFO_DB;
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

    public function getITAFODB() {
        return $this->ITAFO_DB;
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

    public function GetOrariFo($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ORARIFO WHERE ORTSPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ORARIFO WHERE ROWID='$Codice'";
        }
        $Orarifo_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        return $Orarifo_rec;
    }

    public function GetAnaarc($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAARC WHERE ARCCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAARC WHERE ROWID='$Codice'";
        }
        $Anaarc_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $Anaarc_rec;
    }

    public function GetUtente($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM UTENTI WHERE UTELOG='" . $Codice . "'";
        } else if ($tipoRic == 'codiceUtente') {
            $sql = "SELECT * FROM UTENTI WHERE UTECOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM UTENTI WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITWDB(), $sql, false);
    }

    public function GetAnaset($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANASET WHERE SETCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANASET WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnahelp($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAHELP WHERE HELPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAHELP WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnaruo($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANARUO WHERE RUOCOD='$Codice'";
        } else {
            $sql = "SELECT * FROM ANARUO WHERE ROWID=$Codice";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnaddo($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANADDO WHERE DDOCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANADDO WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetItecontrolli($Codice, $tipoRic = 'codice', $multi = true) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY='$Codice' ORDER BY SEQUENZA";
        } elseif ($tipoRic == 'itecod') {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY LIKE '$Codice%' ORDER BY SEQUENZA";
        } else {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItedest($Codice, $tipoRic = 'codice', $multi = true) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITEDEST WHERE ITEKEY='$Codice'";
        } elseif ($tipoRic == 'itecod') {
            $sql = "SELECT * FROM ITEDEST WHERE ITECOD = '$Codice'";
        } else {
            $sql = "SELECT * FROM ITEDEST WHERE ROW_ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnacla($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANACLA WHERE CLACOD='" . $Codice . "'";
        } elseif ($tipoRic == 'sportello') {
            $sql = "SELECT * FROM ANACLA WHERE CLASPO='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANACLA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetRicdoc($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICDOC WHERE DOCNUM = '" . $Codice . "'";
        } else if ($tipoRic == 'allegato') {
            $sql = "SELECT * FROM RICDOC WHERE DOCUPL = '" . addslashes($Codice) . "'";
        } else {
            $sql = "SELECT * FROM RICDOC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPraDestinatari($Codice, $tipoRic = 'codice', $multi = false, $sidx = '', $sord = '') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO = '" . $Codice . "' AND TIPOCOM ='D'";
        } else if ($tipoRic == 'idmail') {
            $sql = "SELECT * FROM PRAMITDEST WHERE IDMAIL = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRAMITDEST WHERE ROWID='$Codice'";
        }
        if ($sidx) {
            $sql = $sql . " ORDER BY " . $sidx . " " . strtoupper($sord);
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPraArrivo($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO = '" . $Codice . "' AND TIPOCOM ='M'";
        } else {
            $sql = "SELECT * FROM PRAMITDEST WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPraimm($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAIMM WHERE PRONUM = '$Codice' ORDER BY SEQUENZA";
        } else {
            $sql = "SELECT * FROM PRAIMM WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetRicite($codice, $tipo = 'codice', $multi = false, $where = '', $pratica = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM RICITE WHERE RICNUM = $pratica AND ITECOD='$codice'";
        } else if ($tipo == 'itekey') {
            $sql = "SELECT * FROM RICITE WHERE RICNUM = '$pratica' AND ITEKEY='$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM RICITE WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnastp($Codice, $tipoRic = 'rowid', $where = '') {
        if ($tipoRic == 'rowid') {
            $sql = "SELECT * FROM ANASTP WHERE ROWID='$Codice' $where";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetRicacl($Codice, $tipoRic = 'rowid', $where = '') {
        if ($tipoRic == 'rowid') {
            $sql = "SELECT * FROM RICACL WHERE ROW_ID='$Codice' $where";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetSynclog($enteSync, $tabella) {
        $sql = "SELECT * FROM SYNCLOG WHERE ENTESYNC='$enteSync' AND TABELLASYNC = '$tabella' ORDER BY DATASYNC DESC";
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetParametriEnte($Codice, $tipoRic = 'ente') {
        if ($tipoRic == 'ente') {
            $sql = "SELECT * FROM PARAMETRIENTE WHERE CODICE='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, false);
    }

    /**
     * Restituisce il responsabile dello sportello aggregato
     * @param <type> $Codice
     * @param <type> $tipoRic
     * @return <type>
     */
    public function GetAnaspa($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANASPA WHERE SPACOD = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANASPA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    /**
     * Restituisce il responsabile dello sportello
     * @param <type> $Codice
     * @param <type> $tipoRic Tipo Ricerca: 'codice' per TSPCOD, altrimenti per ROWID
     * @return <type>
     */
    public function GetAnatsp($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATSP WHERE TSPCOD = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATSP WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnapco($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPCO WHERE PCOCOD = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAPCO WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnareq($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAREQ WHERE REQCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAREQ WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetItereq($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITEREQ WHERE ITEPRA='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITEREQ WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetIteevt($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $Codice . "'";
        } elseif ($tipoRic == 'evento') {
            $sql = "SELECT * FROM ITEEVT WHERE IEVCOD = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITEEVT WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPraazioni($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRAAZIONI WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetTSPazioni($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRAAZIONI WHERE PRATSP = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRAAZIONI WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItenor($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITENOR WHERE ITEPRA='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITENOR WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItedis($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITEDIS WHERE ITEPRA='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITEDIS WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItePraObb($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ITEPRAOBB WHERE OBBPRA='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ITEPRAOBB WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnanor($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANANOR WHERE NORCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANANOR WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnadis($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANADIS WHERE DISCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANADIS WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetProdag($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRODAG WHERE DAGSET='" . $Codice . "'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='" . $Codice . "'";
        } else if ($tipoRic == 'dagpak') {
            $sql = "SELECT * FROM PRODAG WHERE DAGPAK='" . $Codice . "' ORDER BY DAGSFL";
        } else {
            $sql = "SELECT * FROM PRODAG WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }
    
    /**
     * Restituisce un array di DAGNUM a partire dai filtri impostati
     * @param string $dagcod Tipologia di procedimento
     * @param array $filtri array dei filtri in forma chiave valore (es array('UUID'=>$valUUID, 'ID_SDI'=>$valSDI)
     * @param boolean $multi se true restitusice risultati multipli, sennò solo il primo
     * @return array
     */
    public function GetDagnumFilters($dagcod, $filtri, $multi=false){
        $sql = "SELECT DISTINCT DAGNUM FROM PRODAG WHERE DAGCOD = '".addslashes($dagcod)."'";
        $where = "AND";
        
        foreach($filtri as $k=>$v){
            $sql .= " $where DAGNUM IN (SELECT DAGNUM FROM PRODAG WHERE DAGKEY = '".addslashes($k)."' AND DAGVAL = '".addslashes($v)."')";
            $where = "AND";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }
    
    /**
     * Restituisce un array di DAGPAK a partire dai filtri impostati
     * @param string $dagcod Tipologia di procedimento
     * @param array $filtri array dei filtri in forma chiave valore (es array('UUID'=>$valUUID, 'ID_SDI'=>$valSDI)
     * @param boolean $multi se true restitusice risultati multipli, sennò solo il primo
     * @return array
     */
    public function GetDagpakFilters($dagcod, $filtri, $multi=false){
        $sql = "SELECT DISTINCT DAGPAK FROM PRODAG WHERE DAGCOD = '".addslashes($dagcod)."'";
        $where = "AND";
        
        foreach($filtri as $k=>$v){
            $sql .= " $where DAGPAK IN (SELECT DAGPAK FROM PRODAG WHERE DAGKEY = '".addslashes($k)."' AND DAGVAL = '".addslashes($v)."')";
            $where = "AND";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnaatt($Codice, $tipoRic = 'codice', $multi = false, $attset = '') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAATT WHERE ATTCOD='" . $Codice . "'";
        } else if ($tipoRic == 'condizionato') {
            $sql = "SELECT * FROM ANAATT WHERE ATTCOD='" . $Codice . "' AND ATTSET='$attset'";
        } else {
            $sql = "SELECT * FROM ANAATT WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    /**
     * Restituisce il responsabile del procedimento
     * @param <type> $Codice
     * @param <type> $tipoRic
     * @return <type>
     */
    public function GetAnanom($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANANOM WHERE NOMRES='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANANOM WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetPraidc($Codice, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM PRAIDC WHERE IDCKEY='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRAIDC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnauni($Codice, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
//$sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $Codice . "'";
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $Codice . "' AND UNISER = '' AND UNIOPE = ''";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$Codice'";
        }

        $Anauni_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $Anauni_rec;
    }

    public function GetAnauniOpe($codice, $servizio, $unita, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $codice . "' AND UNISER='" . $servizio . "' AND UNIOPE='" . $unita . "'";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$codice'";
        }
        $AnauniOpe_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $AnauniOpe_rec;
    }

    public function GetAnauniAdde($codice, $servizio, $unita, $addetto, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $codice . "' AND UNISER='" . $servizio . "' AND UNIOPE='" . $unita . "' AND UNIADD='$addetto'";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$codice'";
        }
        $AnauniOpe_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $AnauniOpe_rec;
    }

    public function GetPraclt($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRACLT WHERE CLTCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRACLT WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnauniServ($codice, $servizio, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANAUNI WHERE UNISET='" . $codice . "' AND UNISER='" . $servizio . "' AND UNIOPE=''";
//            $sql.=" AND UNISET!='' AND UNISER='' AND UNIOPE='' AND UNIADD='' AND UNIAPE=''";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$codice'";
        }

        $AnauniServ_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $AnauniServ_rec;
    }

    public function GetAnauniRes($codice, $Tipo = 'codice') {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANAUNI WHERE UNIRES='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANAUNI WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
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
        } else if ($tipoRic == 'richiesta') {
            $sql = "SELECT * FROM PROGES WHERE GESPRA='" . $Codice . "'";
        } else if ($tipoRic == 'protocollo') {
            $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
        } else if ($tipoRic == 'antecedente') {
            $sql = "SELECT * FROM PROGES WHERE GESPRE='" . $Codice . "'";
        } else if ($tipoRic == 'codiceProcedimento') {
            $sql = "SELECT * FROM PROGES WHERE GESCODPROC='" . $Codice . "'";
        } else if ($tipoRic == 'geskey') {
            $sql = "SELECT * FROM PROGES WHERE GESKEY ='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PROGES WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPrafolist($Codice, $tipoRic = 'rowid', $multi = false) {
        if ($tipoRic == 'rowid') {
            $sql = "SELECT * FROM PRAFOLIST WHERE ROW_ID='" . $Codice . "'";
        } else if ($tipoRic == 'key') {
            $sql = "SELECT * FROM PRAFOLIST WHERE FOTIPO='" . $Codice['FOTIPO'] . "' AND FOPRAKEY = '" . $Codice['FOPRAKEY'] . "'";
        } else if ($tipoRic == 'gesnum') {
            $sql = "SELECT * FROM PRAFOLIST WHERE FOGESNUM='" . $Codice['GESNUM'] . "' AND FOPROPAK = '" . $Codice['PROPAK'] . "'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPrafofiles($Codice, $tipoRic = 'key', $multi = false) {
        if ($tipoRic == 'rowid') {
            $sql = "SELECT * FROM PRAFOFILES WHERE ROW_ID='" . $Codice . "'";
        } else if ($tipoRic == 'key') {
            $sql = "SELECT * FROM PRAFOFILES WHERE FOTIPO='" . $Codice['FOTIPO'] . "' AND FOPRAKEY = '" . $Codice['FOPRAKEY'] . "'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProgesSerie($anno, $numero = '', $serie = '', $multi = false) {
        $sql = "SELECT * FROM PROGES WHERE SERIEANNO = '$anno'";
        if ($numero) {
            $sql .= " AND SERIEPROGRESSIVO = '$numero'";
        }
        if ($serie) {
            $sql .= " AND SERIECODICE = '$serie'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetGesnum($anno, $numero, $serie) {
        if (!$anno || !$numero || !$serie) {
            return false;
        }
        $sql = "SELECT GESNUM FROM PROGES WHERE SERIEANNO = '$anno' AND SERIEPROGRESSIVO = '$numero' AND SERIECODICE = '$serie'";
        $gesnum_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        return $gesnum_rec['GESNUM'];
    }

    public function ElaboraProgesSerie($gesnum, $sigla = '', $anno = '', $numero = '') {
        if (!$sigla || !$numero || !$anno) {
            $proges_rec = $this->GetProges($gesnum);    // mi ricavo serie e anno da gesnum
            $numero = $proges_rec['SERIEPROGRESSIVO'];
            $anno = $proges_rec['SERIEANNO'];
            $sigla = $proges_rec['SERIECODICE'];
        }
        //$proLib = new proLib();
        $decod_sigla = $this->proLib->getAnaseriearc($sigla);  // decodifica codice con sigla della serie
        return $decod_sigla['SIGLA'] . "/" . $numero . "/" . $anno;
    }

    public function GetMetadatiProges($codice, $tipoRic = 'codice', $multi = false) {
        $proges_rec = $this->GetProges($codice, $tipoRic, $multi);
        $metavalue = unserialize($proges_rec['GESMETA']);
        return $metavalue;
    }

    public function GetProric($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRORIC WHERE RICNUM='" . $Codice . "'";
        } else if ($tipoRic == 'fascicoloRemoto') {
            $sql = "SELECT * FROM PRORIC WHERE GESNUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRORIC WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetRicsoggetti($Codice, $tipoRic = 'codice', $multi = false, $ruolo = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM='" . $Codice . "'";
        } elseif ($tipoRic == 'ruolo') {
            $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM='" . $Codice . "' AND SOGRICRUOLO ='$ruolo'";
        } else {
            $sql = "SELECT * FROM RICSOGGETTI WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnades($Codice, $tipoRic = 'codice', $multi = false, $ruolo = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANADES WHERE DESNUM='" . $Codice . "'";
        } elseif ($tipoRic == 'codiceSogg') {
            $sql = "SELECT * FROM ANADES WHERE DESCOD='" . $Codice . "'";
        } elseif ($tipoRic == 'ruolo') {
            $sql = "SELECT * FROM ANADES WHERE DESNUM='" . $Codice . "' AND DESRUO ='$ruolo'";
        } else {
            $sql = "SELECT * FROM ANADES WHERE ROWID='$Codice'";
        }
        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnadesdag($codice, $tipo = 'anades_rowid', $multi = true, $deskey = '') {
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
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetAnavar($codice, $tipoRic = 'VARCOD') {
        $multi = false;
        if ($tipoRic == 'VARCOD') {
            $sql = "SELECT * FROM ANAVAR WHERE VARCOD='" . $codice . "'";
        } else if ($tipoRic == 'VARTIP' || $tipoRic == 'VARCLA') {
            $sql = "SELECT * FROM ANAVAR WHERE $tipoRic='" . $codice . "'";
            $multi = true;
        } else if ($tipoRic == 'VARDES') {
            $sql = "SELECT * FROM ANAVAR WHERE VARDES LIKE '%" . $codice . "%'";
            $multi = true;
        } else {
            $sql = "SELECT * FROM ANAVAR WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProgessub($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PROGESSUB WHERE PRONUM = '$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM PROGESSUB WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItediagPassiGruppi($codice, $tipo = 'gruppo', $multi = true) {
        if ($tipo == 'gruppo') {
            $sql = "SELECT * FROM ITEDIAGPASSIGRUPPI WHERE ROW_ID_ITEDIAGGRUPPI = '$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITEDIAGPASSIGRUPPI WHERE ROW_ID = '$codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProdiagGruppi($codice, $tipo = 'codice', $multi = true) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRODIAGGRUPPI WHERE GESNUM = '$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM PRODIAGGRUPPI WHERE ROW_ID = '$codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProdiagPassiGruppi($codice, $tipo = 'gruppo', $multi = true) {
        if ($tipo == 'gruppo') {
            $sql = "SELECT * FROM PRODIAGPASSIGRUPPI WHERE ROW_ID_PRODIAGGRUPPI = '$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM PRODIAGPASSIGRUPPI WHERE ROW_ID = '$codice'";
        } else if ($tipo == 'propak') {
            $sql = "SELECT * FROM PRODIAGPASSIGRUPPI WHERE PROPAK = '$codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetMarcaturaModifiche() {
        $Filent_rec = $this->GetFilent(1);
        $autore = $Filent_rec['FILDE6'];
        if (!$autore) {
            $autore = 'indefinito';
        }
        return array(
            "EDITOR" => $autore,
            "DATE" => date('Ymd'),
            "TIME" => strftime("%H:%M:%S")
        );
    }

    public function GetEnteMaster() {
        $Filent_rec = $this->GetFilent(1);
        if ($Filent_rec) {
            return $Filent_rec['FILDE3'];
        } else {
            return false;
        }
    }

    public function GetTipoEnte() {
        $Filent_rec = $this->GetFilent(1);
        if ($Filent_rec) {
            if ($Filent_rec['FILDE4'] == '') {
                return "M";
            }
            return $Filent_rec['FILDE4'];
        } else {
            return 'M';
        }
    }

    public function GetAnaeventi($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAEVENTI WHERE EVTCOD = '" . $Codice . "'";
        } else if ($tipoRic == 'segnalazione') {
            $sql = "SELECT * FROM ANAEVENTI WHERE EVTSEGCOMUNICA = '" . $Codice . "' ORDER BY EVTCOD";
        } else {
            $sql = "SELECT * FROM ANAEVENTI WHERE ROWID = '$Codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function AggiornaMarcaturaProcedimento($codiceProcedimento, $tipoCodice = 'codice') {//, $fl_ins = false) {
        $Anapra_rec_read = $this->GetAnapra($codiceProcedimento, $tipoCodice);
        $Anapra_rec = array('ROWID' => $Anapra_rec_read['ROWID']);
        $Anapra_new = $this->SetMarcaturaProcedimento($Anapra_rec);
        try {
            ItaDB::DBUpdate($this->getPRAMDB(), "ANAPRA", "ROWID", $Anapra_new);
            return true;
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }
    }

    public function SetMarcaturaProcedimento($Anapra_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anapra_rec['PRAINSEDITOR'] = $marcatura['EDITOR'];
            $Anapra_rec['PRAINSDATE'] = $marcatura['DATE'];
            $Anapra_rec['PRAINSTIME'] = $marcatura['TIME'];
        }
        $Anapra_rec['PRAUPDEDITOR'] = $marcatura['EDITOR'];
        $Anapra_rec['PRAUPDDATE'] = $marcatura['DATE'];
        $Anapra_rec['PRAUPDTIME'] = $marcatura['TIME'];
        return $Anapra_rec;
    }

    public function SetMarcaturaSettore($Anaset_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anaset_rec['SETINSEDITOR'] = $marcatura['EDITOR'];
            $Anaset_rec['SETINSDATE'] = $marcatura['DATE'];
            $Anaset_rec['SETINSTIME'] = $marcatura['TIME'];
        }
        $Anaset_rec['SETUPDEDITOR'] = $marcatura['EDITOR'];
        $Anaset_rec['SETUPDDATE'] = $marcatura['DATE'];
        $Anaset_rec['SETUPDTIME'] = $marcatura['TIME'];
        return $Anaset_rec;
    }

    public function SetMarcaturaRuolo($Anaruo_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anaruo_rec['RUOINSEDITOR'] = $marcatura['EDITOR'];
            $Anaruo_rec['RUOINSDATE'] = $marcatura['DATE'];
            $Anaruo_rec['RUOINSTIME'] = $marcatura['TIME'];
        }
        $Anaruo_rec['RUOUPDEDITOR'] = $marcatura['EDITOR'];
        $Anaruo_rec['RUOUPDDATE'] = $marcatura['DATE'];
        $Anaruo_rec['RUOUPDTIME'] = $marcatura['TIME'];
        return $Anaruo_rec;
    }

    public function SetMarcaturaAttivita($Anaatt_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anaatt_rec['ATTINSEDITOR'] = $marcatura['EDITOR'];
            $Anaatt_rec['ATTINSDATE'] = $marcatura['DATE'];
            $Anaatt_rec['ATTINSTIME'] = $marcatura['TIME'];
        }
        $Anaatt_rec['ATTUPDEDITOR'] = $marcatura['EDITOR'];
        $Anaatt_rec['ATTUPDDATE'] = $marcatura['DATE'];
        $Anaatt_rec['ATTUPDTIME'] = $marcatura['TIME'];
        return $Anaatt_rec;
    }

    public function SetMarcaturaTipologia($Anatip_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anatip_rec['TIPINSEDITOR'] = $marcatura['EDITOR'];
            $Anatip_rec['TIPINSDATE'] = $marcatura['DATE'];
            $Anatip_rec['TIPINSTIME'] = $marcatura['TIME'];
        }
        $Anatip_rec['TIPUPDEDITOR'] = $marcatura['EDITOR'];
        $Anatip_rec['TIPUPDDATE'] = $marcatura['DATE'];
        $Anatip_rec['TIPUPDTIME'] = $marcatura['TIME'];
        return $Anatip_rec;
    }

    public function SetMarcaturaTipoPasso($Praclt_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Praclt_rec['CLTINSEDITOR'] = $marcatura['EDITOR'];
            $Praclt_rec['CLTINSDATE'] = $marcatura['DATE'];
            $Praclt_rec['CLTINSTIME'] = $marcatura['TIME'];
        }
        $Praclt_rec['CLTUPDEDITOR'] = $marcatura['EDITOR'];
        $Praclt_rec['CLTUPDDATE'] = $marcatura['DATE'];
        $Praclt_rec['CLTUPDTIME'] = $marcatura['TIME'];
        return $Praclt_rec;
    }

    public function SetMarcaturaEventoProcedimento($Anaeventi_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anaeventi_rec['EVTINSEDITOR'] = $marcatura['EDITOR'];
            $Anaeventi_rec['EVTINSDATE'] = $marcatura['DATE'];
            $Anaeventi_rec['EVTINSTIME'] = $marcatura['TIME'];
        }
        $Anaeventi_rec['EVTUPDEDITOR'] = $marcatura['EDITOR'];
        $Anaeventi_rec['EVTUPDDATE'] = $marcatura['DATE'];
        $Anaeventi_rec['EVTUPDTIME'] = $marcatura['TIME'];
        return $Anaeventi_rec;
    }

    public function SetMarcaturaDisciplina($Anadis_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anadis_rec['DISINSEDITOR'] = $marcatura['EDITOR'];
            $Anadis_rec['DISINSDATE'] = $marcatura['DATE'];
            $Anadis_rec['DISINSTIME'] = $marcatura['TIME'];
        }
        $Anadis_rec['DISUPDEDITOR'] = $marcatura['EDITOR'];
        $Anadis_rec['DISUPDDATE'] = $marcatura['DATE'];
        $Anadis_rec['DISUPDTIME'] = $marcatura['TIME'];
        return $Anadis_rec;
    }

    public function SetMarcaturaNormativa($Ananor_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Ananor_rec['NORINSEDITOR'] = $marcatura['EDITOR'];
            $Ananor_rec['NORINSDATE'] = $marcatura['DATE'];
            $Ananor_rec['NORINSTIME'] = $marcatura['TIME'];
        }
        $Ananor_rec['NORUPDEDITOR'] = $marcatura['EDITOR'];
        $Ananor_rec['NORUPDDATE'] = $marcatura['DATE'];
        $Ananor_rec['NORUPDTIME'] = $marcatura['TIME'];
        return $Ananor_rec;
    }

    public function SetMarcaturaRequisito($Anareq_rec, $fl_ins = false) {
        $marcatura = $this->GetMarcaturaModifiche();
        if ($fl_ins) {
            $Anareq_rec['REQINSEDITOR'] = $marcatura['EDITOR'];
            $Anareq_rec['REQINSDATE'] = $marcatura['DATE'];
            $Anareq_rec['REQINSTIME'] = $marcatura['TIME'];
        }
        $Anareq_rec['REQUPDEDITOR'] = $marcatura['EDITOR'];
        $Anareq_rec['REQUPDDATE'] = $marcatura['DATE'];
        $Anareq_rec['REQUPDTIME'] = $marcatura['TIME'];
        return $Anareq_rec;
    }

    public function GetAnapra($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPRA WHERE PRANUM='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAPRA WHERE ROWID=$Codice";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetPropas($codice, $tipoRic = 'propak', $multi = false, $tipoProt = "") {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM='$codice' ORDER BY PROSEQ";
        } else if ($tipoRic == 'propak') {
            $sql = "SELECT * FROM PROPAS WHERE PROPAK='$codice'";
        } else if ($tipoRic == 'prokpre') {
            $sql = "SELECT * FROM PROPAS WHERE PROKPRE='$codice'  ORDER BY PROSEQ";
        } else if ($tipoRic == 'prorin') {
            $sql = "SELECT * FROM PROPAS WHERE PRORIN='$codice'";
        } else if ($tipoRic == 'paspro') {
            $sql = "SELECT * FROM PROPAS WHERE PASPRO='$codice' AND PASPAR= '$tipoProt'";
        } else if ($tipoRic == 'provpa') {
            $sql = "SELECT * FROM PROPAS WHERE PROVPA='$codice'";
        } else if ($tipoRic == 'provpn') {
            $sql = "SELECT * FROM PROPAS WHERE PROVPN='$codice'";
        } else {
            $sql = "SELECT * FROM PROPAS WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPropasFatti($codice, $tipo = 'prospa', $multi = false) {
        if ($tipo == 'prospa') {
            $sql = "SELECT * FROM PROPASFATTI WHERE PROSPA='$codice' ORDER BY ROW_ID DESC";
        } else if ($tipo == 'propak') {
            $sql = "SELECT * FROM PROPASFATTI WHERE PROPAK='$codice'";
        } else if ($tipo == 'pronum') {
            $sql = "SELECT * FROM PROPASFATTI WHERE PRONUM='$codice' ";
        } else {
            $sql = "SELECT * FROM PROPASFATTI WHERE ROW_ID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPropassi($codice, $where, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM='" . $codice . "' " . $where . " ORDER BY PROSEQ DESC";
        } else if ($tipoRic == 'proclt') {
            $sql = "SELECT DISTINCT PROCLT FROM PROPAS WHERE PRONUM='$codice '";
        } else {
            $sql = "SELECT * FROM PROPAS WHERE " . $where . "ORDER BY PROSEQ";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProdst($Codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PRODST WHERE DSTSET = '" . $Codice . "'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRODST WHERE " . $this->getPRAMDB()->subString('DSTSET', 1, 10) . " = '$Codice'";
        } else if ($tipoRic == 'desc') {
            $sql = "SELECT * FROM PRODST WHERE DSTDES = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PRODST WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPasdoc($codice, $Tipo = 'codice', $multi = false) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM PASDOC WHERE PASKEY = '$codice'";
        } elseif ($Tipo == 'ROWID') {
            $sql = "SELECT * FROM PASDOC WHERE ROWID = '$codice'";
        } elseif ($Tipo == 'pratica') {
            $sql = "SELECT * FROM PASDOC WHERE " . $this->getPRAMDB()->subString('PASKEY', 1, 10) . " = '$codice' AND PASFIL NOT LIKE '%info'";
        } elseif ($Tipo == 'numero') {
            $sql = "SELECT * FROM PASDOC WHERE " . $this->getPRAMDB()->subString('PASKEY', 1, 10) . " = '$codice'";
        } elseif ($Tipo == 'pasfil') {
            $sql = "SELECT * FROM PASDOC WHERE PASFIL = '$codice'";
        } elseif ($Tipo == 'passha2') {
            $sql = "SELECT * FROM PASDOC WHERE PASSHA2 = '$codice'";
        } elseif ($Tipo == 'passha2sost') {
            $sql = "SELECT * FROM PASDOC WHERE PASSHA2SOST = '$codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPracom($codice, $tipoRic = 'idmail', $multi = false) {
        if ($tipoRic == 'compak') {
            $sql = "SELECT * FROM PRACOM WHERE COMPAK='$codice'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRACOM WHERE COMNUM = '$codice'";
        } else if ($tipoRic == 'riferimento') {
            $sql = "SELECT * FROM PRACOM WHERE COMRIF = '$codice'";
        } else if ($tipoRic == 'idmail') {
            $sql = "SELECT * FROM PRACOM WHERE COMIDMAIL = '$codice'";
        } else {
            $sql = "SELECT * FROM PRACOM WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPraMitDest($codice, $tipoRic = 'idmail', $multi = false) {
        if ($tipoRic == 'compak') {
            $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO='$codice'";
        } else if ($tipoRic == 'numero') {
            $sql = "SELECT * FROM PRAMITDEST WHERE " . $this->getPRAMDB()->subString('KEYPASSO', 1, 10) . " = '$codice'";
        } else if ($tipoRic == 'idmail') {
            $sql = "SELECT * FROM PRAMITDEST WHERE IDMAIL = '$codice'";
        } else {
            $sql = "SELECT * FROM PRAMITDEST WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetMetadatiPracom($codice, $tipoRic = 'compak', $multi = false) {

        $pracom_rec = $this->GetPracom($codice, $tipoRic, $multi);
        $metavalue = unserialize($pracom_rec['COMMETA']);

        return $metavalue;
    }

    public function GetAnatip($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATIP WHERE TIPCOD='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATIP WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnatipimpo($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANATIPIMPO WHERE CODTIPOIMPO = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANATIPIMPO WHERE ROWID = '$Codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnaquiet($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAQUIET WHERE CODQUIET = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAQUIET WHERE ROWID = '$Codice'";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnadoctipreg($Codice, $tipoRic = 'codice') {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANADOCTIPREG WHERE CODDOCREG = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANADOCTIPREG WHERE ROWID = '$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetProimpo($Codice, $tipoRic = 'codice', $where = '', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROIMPO WHERE IMPONUM = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PROIMPO WHERE ROWID = '$Codice'";
        }

        if ($where) {
            $sql .= " AND $where";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetProconciliazione($Codice, $tipoRic = 'codice', $where = '', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM PROCONCILIAZIONE WHERE IMPONUM = '" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM PROCONCILIAZIONE WHERE ROWID = '$Codice'";
        }

        if ($where) {
            $sql .= " AND $where";
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetFilent($Codice) {
        if ($Codice) {
            $sql = "SELECT * FROM FILENT WHERE FILKEY=" . $Codice;
        } else {
            $sql = "SELECT * FROM FILENT";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    public function GetAnapar($Codice, $tipoRic = 'codice', $multi = true) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAPAR WHERE PARCLA='" . $Codice . "'";
        } elseif ($tipoRic == 'parkey') {
            $sql = "SELECT * FROM ANAPAR WHERE PARKEY='" . $Codice . "'";
        } else {
            $sql = "SELECT * FROM ANAPAR WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

//
// 
//
    public function checkVisibilitaSportello($values, $visibilita = false) {
        if (!$values) {
            return false;
        }
        if (!$visibilita) {
            $visibilita = $this->GetVisibiltaSportello();
        }

        //if ($visibilita['SPORTELLO'] == 0 && $visibilita['AGGREGATO'] == 0) {
        if (count($visibilita['SPORTELLI']) == 0 && $visibilita['AGGREGATO'] == 0) {
            return true;
        }
        $ok_sportello = false;
        $ok_aggregato = false;

        /*
         * 
         * VECCHIO
         * 
         */
        if ($values['SPORTELLO'] == $visibilita['SPORTELLO'] || $visibilita['SPORTELLO'] == 0) {
            $ok_sportello = true;
        }

        /*
         * 
         * NUOVO
         * 
         */
        $arrSportelliSearch = array();
        foreach ($visibilita['SPORTELLI'] as $sportello) {
            $arrSportello = array();
            if (strpos($sportello, '/') !== false) {
                $arrSportello = explode("/", $sportello);
                $arrSportelliSearch[] = $arrSportello[0];
            } else {
                $arrSportelliSearch[] = $sportello;
            }
        }
        //if (array_search($values['SPORTELLO'], $visibilita['SPORTELLI']) || count($visibilita['SPORTELLI']) == 0) {
        if (array_search($values['SPORTELLO'], $arrSportelliSearch) || count($visibilita['SPORTELLI']) == 0) {
            $ok_sportello = true;
        }


        if ($values['AGGREGATO'] == $visibilita['AGGREGATO'] || $visibilita['AGGREGATO'] == 0) {
            $ok_aggregato = true;
        }

        if ($ok_sportello && $ok_aggregato) {
            return true;
        }
        return false;
    }

    public function GetVisibiltaSportello($idUtente = false) {
        $retVisibilta = array();
        if ($idUtente === false) {
            $idUtente = App::$utente->getKey('idUtente');
        }

        $Utenti_rec = ItaDB::DBSQLSelect($this->getITWDB(), "SELECT * FROM UTENTI WHERE UTECOD=$idUtente", false);
        if (!$Utenti_rec) {
            return false;
        }
        $codDipeOrganica = $Utenti_rec['UTEANA__3'];
        $Ananom_rec = $this->GetAnanom($codDipeOrganica);
        if (!$Ananom_rec) {
            return false;
        }
        $retVisibilta['SPORTELLO'] = $Ananom_rec['NOMTSP'];

        $retVisibilta['SPORTELLI'] = array();
        if ($Ananom_rec['NOMTSP']) {
            $retVisibilta['SPORTELLI'][] = $Ananom_rec['NOMTSP'];
        }
        if ($Ananom_rec['NOMTSPEXT']) {
            $retVisibilta['SPORTELLI'] = array_merge($retVisibilta['SPORTELLI'], explode('|', $Ananom_rec['NOMTSPEXT']));
        }
        $retVisibilta['AGGREGATO'] = $Ananom_rec['NOMSPA'];
        if ($Ananom_rec['NOMTSP'] == 0) {
            $retVisibilta['SPORTELLO_DESC'] = "Tutti gli sportelli";
        } else {
            $strSportelli = "";
            foreach ($retVisibilta['SPORTELLI'] as $sportello) {
                $Anatsp_rec = $this->GetAnatsp($sportello);
                $strSportelli .= $Anatsp_rec['TSPDES'] . "|";
            }
            $strSportelli = substr($strSportelli, 0, -1);
//            $Anatsp_rec = $this->GetAnatsp($Ananom_rec['NOMTSP']);
//            $retVisibilta['SPORTELLO_DESC'] = $Anatsp_rec['TSPDES'];
            $retVisibilta['SPORTELLO_DESC'] = $strSportelli;
        }
        if ($Ananom_rec['NOMSPA'] == 0) {
            $retVisibilta['AGGREGATO_DESC'] = "Tutti gli aggregati";
        } else {
            $Anaspa_rec = $this->GetAnaspa($Ananom_rec['NOMSPA']);
            $retVisibilta['AGGREGATO_DESC'] = $Anaspa_rec['SPADES'];
        }
        return $retVisibilta;
    }

    public function PropakGenerator($numeroProcedimento, $index = null) {
        if ($numeroProcedimento == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella creazione della chiave univoca per l'Iter! Numero Procedimento mancante.");
            return false;
        }
        if ($index === null) {
            usleep(50000); // 50 millisecondi;
            list($msec, $sec) = explode(" ", microtime());
            return $numeroProcedimento . $sec . substr($msec, 2, 2);
        } else {
            return $numeroProcedimento . str_pad($index, 12, '0', STR_PAD_LEFT);
        }
    }

    function getPropasTab($procedimento, $sortField = "PROSEQ") {
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $procedimento . "' ORDER BY " . $sortField;
        try {
            return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        } catch (Exception $e) {
            Out::msgStop('Errore DB', $e->getMessage());
            return false;
        }
    }

    public function GetCtrRtn($codice, $tipoRic = 'codice', $multi = false) {
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM CTRRTN WHERE CTRKEY='$codice' ORDER BY CTRKEY";
        } else {
            $sql = "SELECT * FROM CTRRTN WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    function ordinaPassi($procedimento) {
        if ($procedimento) {
            $new_seq = 0;
            $Propas_tab = $this->getPropasTab($procedimento);
            if ($Propas_tab) {
                foreach ($Propas_tab as $Propas_rec) {
                    $new_seq += 10;
                    $Propas_rec['PROSEQ'] = $new_seq;
                    try {
                        $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $Propas_rec);
                        if ($nrow == -1) {
                            return false;
                        }
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($exc->getMessage());
                        return false;
                    }
                }
            }
            return true;
        }
    }

    function GetMsgInputCredenziali($form, $titolo, $retid = "", $msg = "") {
        $header = "<span style=\"color:red;font-weight:bold;font-size:1.2em;\">Digitare le credenziali utilizzate per il login</span>";
        $footer = $msg;
        Out::msgInput($titolo, array(
            array(
                'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Utente'),
                'id' => $form . '_utente',
                'name' => $form . '_utente',
                'value' => "",
                'width' => '70',
                'size' => '40',
                'maxchars' => '30'),
            array(
                'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Password'),
                'id' => $form . '_password',
                'name' => $form . '_password',
                'type' => 'password',
                'value' => "",
                'width' => '70',
                'size' => '40',
                'maxchars' => '30')
                ), array('F5-Conferma' => array('id' => $form . "_returnPassword$retid", 'model' => $form, 'shortCut' => "f5")), $form, "auto", "auto", true, $header, $footer
        );
    }

    function GetMsgInputPassword($form, $titolo, $retid = "", $msg = "") {
        $header = "<span style=\"color:red;font-weight:bold;font-size:1.2em;\">Digitare la password utilizzata per il login</span>";
        $footer = $msg;
        Out::msgInput($titolo, array(
            'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Password'),
            'id' => $form . '_password',
            'name' => $form . '_password',
            'type' => 'password',
            'value' => "",
            'width' => '70',
            'size' => '40',
            'maxchars' => '30'), array('F5-Conferma' => array('id' => $form . "_returnPassword$retid", 'model' => $form, 'shortCut' => "f5")), $form, "auto", "auto", true, $header, $footer
        );
    }

    function GetWhereVisibilitaSportello() {
        $retVisibilta = $this->GetVisibiltaSportello();
        $whereVisibilita = '';
//        if (count($retVisibilta['SPORTELLI']) != 0) {
//            $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
//            $whereVisibilita .= " AND GESTSP IN ($strSportelli)";
//        }
        if (count($retVisibilta['SPORTELLI']) != 0) {
            $arrSportelliAggregati = array();
            foreach ($retVisibilta['SPORTELLI'] as $key => $filtroSportello) {
                if (strpos($filtroSportello, '/') !== false) {
                    $arrSportelliAggregati[] = "'$filtroSportello'";
                    unset($retVisibilta['SPORTELLI'][$key]);
                }
            }
            $sqlArray = array();
            if (count($retVisibilta['SPORTELLI'])) {
                $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
                $sqlFiltroSportelli = "GESTSP IN ($strSportelli)";
                $sqlArray[] = $sqlFiltroSportelli;
            }
            if (count($arrSportelliAggregati)) {
                $strSportelliAggregati = implode(",", $arrSportelliAggregati);
                $concatSportelli = $this->getPRAMDB()->strConcat("GESTSP", "'/'", "GESSPA");
                $sqlFiltroAggregati = " $concatSportelli IN ($strSportelliAggregati) ";
                $sqlArray[] = $sqlFiltroAggregati;
            }
            $sqlFiltro_tsp_spa = implode(" OR ", $sqlArray);
            $whereVisibilita .= " AND ($sqlFiltro_tsp_spa )";
        }
        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
            $whereVisibilita .= " AND GESSPA = " . $retVisibilta['AGGREGATO'];
        }
        return $whereVisibilita;
    }

    function GetWhereVisibilitaSportelloFO() {
        $retVisibilta = $this->GetVisibiltaSportello();
        $whereVisibilita = '';
//        if (count($retVisibilta['SPORTELLI']) != 0) {
//            $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
//            $whereVisibilita .= " AND RICTSP IN ($strSportelli)";
//        }
        if (count($retVisibilta['SPORTELLI']) != 0) {
            $arrSportelliAggregati = array();
            foreach ($retVisibilta['SPORTELLI'] as $key => $filtroSportello) {
                if (strpos($filtroSportello, '/') !== false) {
                    $arrSportelliAggregati[] = "'$filtroSportello'";
                    unset($retVisibilta['SPORTELLI'][$key]);
                }
            }
            $sqlArray = array();
            if (count($retVisibilta['SPORTELLI'])) {
                $strSportelli = implode(",", $retVisibilta['SPORTELLI']);
                $sqlFiltroSportelli = "RICTSP IN ($strSportelli)";
                $sqlArray[] = $sqlFiltroSportelli;
            }
            if (count($arrSportelliAggregati)) {
                $strSportelliAggregati = implode(",", $arrSportelliAggregati);
                $concatSportelli = $this->getPRAMDB()->strConcat("RICTSP", "'/'", "RICSPA");
                $sqlFiltroAggregati = " $concatSportelli IN ($strSportelliAggregati) ";
                $sqlArray[] = $sqlFiltroAggregati;
            }
            $sqlFiltro_tsp_spa = implode(" OR ", $sqlArray);
            $whereVisibilita .= " AND ($sqlFiltro_tsp_spa )";
        }
        if ($retVisibilta ['AGGREGATO'] != 0) {
            $whereVisibilita .= " AND RICSPA = " . $retVisibilta['AGGREGATO'];
        }
        return $whereVisibilita;
    }

    function ordinaPrioritaArrayDag($pratica) {
        if (!$pratica) {
            return false;
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' ORDER BY DAGPRI";
        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $new_priorita = 0;
        foreach ($prodag_tab as $dato) {
            if ($dato['DAGPRI'] == 0) {
                continue;
            }
            $new_priorita += 10;
            $dato['DAGPRI'] = $new_priorita;
            try {
                $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PRODAG", "ROWID", $dato);
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

    function ordinaSeqDatiAggiuntivi($pratica, $keyPasso) {
        if ($pratica) {
            $new_seq = 0;
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGPAK = '$keyPasso' ORDER BY DAGSEQ";
            $datiPasso = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
            if (!$datiPasso) {
                return false;
            }
            foreach ($datiPasso as $keyPasso => $dato) {
                $new_seq += 10;
                $dato['DAGSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PRODAG", "ROWID", $dato);
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

    public function GetStringDecode($dictionaryValues, $string) {
        $itaSmarty = new itaSmarty();
        foreach ($dictionaryValues as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }

        $tmpPath = itaLib::getAppsTempPath();
        if (!is_dir($tmpPath)) {
            $tmpPath = itaLib::createAppsTempPath();
            if (!$tmpPath) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita");
                return false;
            }
        }
        $documentoTmp = $tmpPath . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.tpl';
        if (!$this->writeFile($documentoTmp, $string)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore scrittura su: $documentoTmp<br>Creazione modello oggetto da parametri fallita.");
            return false;
        }
        $contenuto = $itaSmarty->fetch($documentoTmp);
        @unlink($documentoTmp);
        return $contenuto;
    }

    public function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!@fwrite($fpw, $string)) {
            fclose($fpw);
            return false;
        }
        fclose($fpw);
        return true;
    }

    public function ImportXmlFilePropas($XMLpassi, $partiDa, $pratica) {
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($XMLpassi);
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!array_key_exists('ITEPAS', $arrayXml)) {
            Out::msgStop("Errore", "File di importazione passi non è conforme.");
            return false;
        }

        if (!$this->insertRecordPropas($partiDa, $pratica)) {
            Out::msgStop("Errore", "Procedura di importazione passi interrotta per errore nello spostamento sequenza.");
            return false;
        }
        $filent_valoResp_rec = $this->GetFilent(19);
        $arrayItepas = $arrayXml['ITEPAS'];
        $arrayItevpaDett = $arrayXml['ITEVPADETT'];


        $arrayItedag = $arrayXml['ITEDAG'];
        if (!$arrayItedag[0]) {
            $arrayItedag = array($arrayItedag);
        }

        foreach ($arrayItedag as $arrayKey => $itedagRec) {
            foreach ($itedagRec as $itedagKey => $itedagValue) {
                $arrayItedag[$arrayKey][$itedagKey] = $itedagValue[itaXML::textNode];
            }
            $arrayItedag[$arrayKey] = itaLib::utf8_decode_array($arrayItedag[$arrayKey]);
        }

        /*
         * Destinatari
         */
        $arrayItedest = $arrayXml['ITEDEST'];
        if (!$arrayItedest[0]) {
            $arrayItedest = array($arrayItedest);
        }
        foreach ($arrayItedest as $arrayKey => $itedestRec) {
            foreach ($itedestRec as $itedestKey => $itedestValue) {
                $arrayItedest[$arrayKey][$itedestKey] = $itedestValue['@textNode'];
            }
            $arrayItedest[$arrayKey] = itaLib::utf8_decode_array($arrayItedest[$arrayKey]);
        }


        //  Registro su PROPAS
        if (!$arrayItepas[0]) {
            $arrayItepas = array($arrayItepas);
        }
        $arrayCollegamenti = array();
        $i = 0;
        foreach ($arrayItepas as $itepasRec) {
            $praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $itepasRec['ITECLT'][itaXML::textNode] . "'", false);
            $propas_rec = array();
            $partiDa = $partiDa + 10;
            $propas_rec['PRONUM'] = $pratica;
            $propas_rec['PROPRO'] = $itepasRec['ITECOD'][itaXML::textNode];
            $propas_rec['PRORES'] = $itepasRec['ITERES'][itaXML::textNode];
            $propas_rec['PROSEQ'] = $partiDa;
            $propas_rec['PRORPA'] = $itepasRec['ITERES'][itaXML::textNode];
            if ($filent_valoResp_rec["FILVAL"] == 1) {
                $propas_rec['PRORPA'] = "";
            }
            $propas_rec['PROTBA'] = $itepasRec['ITETBA'][itaXML::textNode];
            $propas_rec['PROITK'] = $itepasRec['ITEKEY'][itaXML::textNode];
            $propas_rec['PROUOP'] = $itepasRec['ITEOPE'][itaXML::textNode];
            $propas_rec['PROSET'] = $itepasRec['ITESET'][itaXML::textNode];
            $propas_rec['PROSER'] = $itepasRec['ITESER'][itaXML::textNode];
            $propas_rec['PROGIO'] = $itepasRec['ITEGIO'][itaXML::textNode];
            $propas_rec['PROCLT'] = $itepasRec['ITECLT'][itaXML::textNode];
            $propas_rec['PROCOM'] = $itepasRec['ITECOM'][itaXML::textNode];
            $propas_rec['PRODTP'] = $praclt_rec['CLTDES'];
            $propas_rec['PRODPA'] = $itepasRec['ITEDES'][itaXML::textNode];
            $propas_rec['PROQST'] = $itepasRec['ITEQST'][itaXML::textNode];
            $propas_rec['PRODAT'] = $itepasRec['ITEDAT'][itaXML::textNode];
            $propas_rec['PROIRE'] = $itepasRec['ITEIRE'][itaXML::textNode];
            $propas_rec['PROPUB'] = $itepasRec['ITEPUB'][itaXML::textNode];
            $propas_rec['PROZIP'] = $itepasRec['ITEZIP'][itaXML::textNode];
            $propas_rec['PRODRR'] = $itepasRec['ITEDRR'][itaXML::textNode];
            $propas_rec['PROIDR'] = $itepasRec['ITEIDR'][itaXML::textNode];
            $propas_rec['PRODIS'] = $itepasRec['ITEDIS'][itaXML::textNode];
            $propas_rec['PRODOW'] = $itepasRec['ITEDOW'][itaXML::textNode];
            $propas_rec['PROUPL'] = $itepasRec['ITEUPL'][itaXML::textNode];
            $propas_rec['PROMLT'] = $itepasRec['ITEMLT'][itaXML::textNode];
            $propas_rec['PROOBL'] = $itepasRec['ITEOBL'][itaXML::textNode];
            $propas_rec['PROPRI'] = $itepasRec['ITEPRI'][itaXML::textNode];
            $propas_rec['PROPAK'] = $this->PropakGenerator($pratica);
            $propas_rec['PROVPA'] = '';
            $propas_rec['PROVPN'] = '';
            $propas_rec['PROCTP'] = '';
            $propas_rec['PROCTR'] = '';
            $propas_rec['PROUTEADD'] = $propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
            $propas_rec['PRODATEADD'] = $propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
            if ($itepasRec['ITECTR'][itaXML::textNode] != '') {
                $propas_rec['PROCTR'] = $itepasRec['ITECTR'][itaXML::textNode];
            }
            if ($itepasRec['ITECOM'][itaXML::textNode] != 0) {
                if ($itepasRec['ITEINT'][itaXML::textNode] == 1) {
                    $propas_rec['PROINT'] = $itepasRec['ITEINT'][itaXML::textNode];
                }
                if ($itepasRec['ITECDE'][itaXML::textNode] != "") {
                    $propas_rec['PROCDE'] = $itepasRec['ITECDE'][itaXML::textNode];
                }
                if ($itepasRec['ITECOMDEST'][itaXML::textNode] != 0) {
                    $propas_rec['PROCOMDEST'] = $itepasRec['ITECOMDEST'][itaXML::textNode];
                }
            }
            $propas_rec['ROWID_DOC_CLASSIFICAZIONE'] = $itepasRec['ROWID_DOC_CLASSIFICAZIONE'][itaXML::textNode];
            $propas_rec_utf8_decode = itaLib::utf8_decode_array($propas_rec);
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PROPAS", 'ROWID', $propas_rec_utf8_decode);
                if ($nrow != 1) {
                    return false;
                }
                $lastId = $this->PRAM_DB->getLastId();
            } catch (Exception $exc) {
                Out::msgStop("Errore", $exc->getMessage());
                return false;
            }

            /*
             * Mi creo un array con le chiavi vecchie e nuove per poi riprostinare gli agganci corretti
             */
            $arrayCollegamenti[$i]['OLD_ITEKEY'] = $itepasRec['ITEKEY'][itaXML::textNode];
            $arrayCollegamenti[$i]['NEW_PROPAK'] = $propas_rec['PROPAK'];
            $arrayCollegamenti[$i]['OLD_ITEKPRE'] = $itepasRec['ITEKPRE'][itaXML::textNode];
            $arrayCollegamenti[$i]['OLD_ITEVPA'] = $itepasRec['ITEVPA'][itaXML::textNode];
            $arrayCollegamenti[$i]['OLD_ITEVPN'] = $itepasRec['ITEVPN'][itaXML::textNode];
            $arrayCollegamenti[$i]['OLD_ITECTP'] = $itepasRec['ITECTP'][itaXML::textNode];
            $arrayCollegamenti[$i]['ROWID'] = $lastId;
            $i++;

            /*
             * Registro PRODAG
             */
            foreach ($arrayItedag as $itedagRec) {
                if ($itedagRec['ITEKEY'] === $propas_rec['PROITK']) {
                    if (!$this->ribaltaDatoAggEsterna($propas_rec_utf8_decode, $itedagRec)) {
                        Out::msgStop("Errore", $this->getErrMessage());
                        return false;
                    }
                }
            }

            /*
             * Registro PROMITDEST
             */
            foreach ($arrayItedest as $itedestRec) {
                if ($itedestRec['ITEKEY'] === $propas_rec['PROITK']) {
                    if (!$this->ribaltaDestinatario($propas_rec_utf8_decode, $itedestRec)) {
                        Out::msgStop("Errore", $this->getErrMessage());
                        return false;
                    }
                }
            }
        }

        /*
         * Dopo L'insert dei passi, mi scorro l'array che ho creato per sistemare i figli ed i salti,
         * cioè sistemare PROKPRE
         */
        foreach ($arrayCollegamenti as $value) {
            if ($value['OLD_ITEKPRE'] == "") {
                $itekeyPadre = $value['OLD_ITEKEY'];
                $propakPadre = $value['NEW_PROPAK'];
                foreach ($arrayCollegamenti as $value2) {
                    if ($itekeyPadre == $value2['OLD_ITEKPRE']) {
                        $propas_recFiglio = $this->GetPropas($value2['ROWID'], "rowid");
                        $propas_recFiglio['PROKPRE'] = $propakPadre;
                        try {
                            $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $propas_recFiglio);
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

            /*
             * Sistemo i salti importati
             */
            foreach ($arrayCollegamenti as $value) {
                $propas_recBase = $this->GetPropas($value['ROWID'], "rowid");
                $flUpdate = false;
                /* Passo SI */
                if ($value['OLD_ITEVPA'] != "") {
                    foreach ($arrayCollegamenti as $value2) {
                        if ($value['OLD_ITEVPA'] == $value2['OLD_ITEKEY']) {
                            $propas_recBase['PROVPA'] = $value2['NEW_PROPAK'];
                            $flUpdate = true;
                            break;
                        }
                    }
                }
                /* Passo No */
                if ($value['OLD_ITEVPN'] != "") {
                    foreach ($arrayCollegamenti as $value2) {
                        if ($value['OLD_ITEVPN'] == $value2['OLD_ITEKEY']) {
                            $propas_recBase['PROVPN'] = $value2['NEW_PROPAK'];
                            $flUpdate = true;
                            break;
                        }
                    }
                }
                /* Passo Controllo */
                if ($value['OLD_ITECTP'] != "") {
                    foreach ($arrayCollegamenti as $value2) {
                        if ($value['OLD_ITECTP'] == $value2['OLD_ITEKEY']) {
                            $propas_recBase['PROCTP'] = $value2['NEW_PROPAK'];
                            $flUpdate = true;
                            break;
                        }
                    }
                }

                if ($flUpdate) {
                    try {
                        $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $propas_recBase);
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
        //  Registro su PROPAS
        if (!$arrayItevpaDett[0] && $arrayItevpaDett) {
            $arrayItevpaDett = array($arrayItevpaDett);
        }
        foreach ($arrayItevpaDett as $itevpadettRec) {
            $currPropak = '';
            $currProvpa = '';
            foreach ($arrayCollegamenti as $value) {
                if ($value['OLD_ITEKEY'] == $itevpadettRec['ITEKEY'][itaXML::textNode]) {
                    $currPropak = $value['NEW_PROPAK'];
                }
                if ($value['OLD_ITEKEY'] == $itevpadettRec['ITEVPA'][itaXML::textNode]) {
                    $currProvpa = $value['NEW_PROPAK'];
                }
                if ($currPropak && $currProvpa) {
                    break;
                }
            }
            $provpadett_rec = array();
            $provpadett_rec['PRONUM'] = $pratica;
            $provpadett_rec['PROPRO'] = $itevpadettRec['ITECOD'][itaXML::textNode];
            $provpadett_rec['PROPAK'] = $currPropak;
            $provpadett_rec['PROVPA'] = $currProvpa;
            $provpadett_rec['PROSEQEXPR'] = $itevpadettRec['ITESEQEXPR'][itaXML::textNode];
            $provpadett_rec['PROEXPRVPA'] = $itevpadettRec['ITEEXPRVPA'][itaXML::textNode];
            $provpadett_rec['PROVPADESC'] = $itevpadettRec['ITEVPADESC'][itaXML::textNode];
            $provpadett_rec_utf8_decode = itaLib::utf8_decode_array($provpadett_rec);
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PROVPADETT", 'ROW_ID', $provpadett_rec_utf8_decode);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $exc) {
                Out::msgStop("Errore", $exc->getMessage());
                return false;
            }
        }



        return true;
    }

    public function insertRecordPropas($partiDa, $pratica) {
        if (!$partiDa == 0) {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $pratica . "' AND PROSEQ > '" . $partiDa . "' ORDER BY PROSEQ";
            $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            if ($propas_tab) {
                foreach ($propas_tab as $propas_rec) {
                    $propas_rec['PROSEQ'] = $propas_rec['PROSEQ'] + 500;
                    try {
                        $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $propas_rec);
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

    function ordinaPassiProc($procedimento) {
        if ($procedimento) {
            $new_seq = 0;
            $Itepas_tab = $this->GetItepas($procedimento, 'codice', true, 'ORDER BY ITESEQ');
            if (!$Itepas_tab) {
                return false;
            }
            foreach ($Itepas_tab as $Itepas_rec) {
                $new_seq += 10;
                $Itepas_rec['ITESEQ'] = $new_seq;

                try {
                    $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "ITEPAS", "ROWID", $Itepas_rec);
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

    function ordinaPassiPratica($pratica) {
        if ($pratica) {
            $new_seq = 0;
            $Propas_tab = $this->GetPropas($pratica, 'codice', true);
            if (!$Propas_tab) {
                return false;
            }
            foreach ($Propas_tab as $Propas_rec) {
                $new_seq += 10;
                $Propas_rec['PROSEQ'] = $new_seq;

                try {
                    $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $Propas_rec);
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

    function ordinaPassiProcRich($procedimento, $pratica) {
        if ($pratica) {
            $new_seq = 0;
            $Ricite_tab = $this->GetRicite($procedimento, 'codice', true, 'ORDER BY ITESEQ', $pratica);
            if (!$Ricite_tab) {
                return false;
            }
            foreach ($Ricite_tab as $Ricite_rec) {
                $new_seq += 10;
                $Ricite_rec['ITESEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "RICITE", "ROWID", $Ricite_rec);
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

    function GetClassificazioneProtocollazioneItalsoft($procedimento) {
        if ($procedimento) {
            include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
            //$proLib = new proLib();
            $titproc_rec = $this->proLib->GetTitproc("PRANUM = '$procedimento'", "CODICE");
            $titolario = $titproc_rec['CATCOD'];
            if ($titproc_rec['CLACOD']) {
                $titolario .= '.' . $titproc_rec['CLACOD'];
            }
            if ($titproc_rec['FASCOD']) {
                $titolario .= '.' . $titproc_rec['FASCOD'];
            }
            return $titolario;
        }
    }

    function GetFascicoloProtocollazione($procedimento, $gesnum = "") {
//
//Se c'è il n. fascicoloe elettronico, gli viene data la precedenza per la ricerca del fascicolo.
//Il procedimento in anagrafica, potrebbe essere spento o su sportello prova
//
        if ($gesnum) {
            $proges_rec = $this->GetProges($gesnum);
            if ($proges_rec['GESATT'] != 0) {   //attività
                $Anaatt_rec = $this->GetAnaatt($proges_rec['GESATT']);
            }
            if ($proges_rec['GESSTT'] != 0) { //settore commerciale
                $Anaset_rec = $this->GetAnaset($proges_rec['GESSTT']);
            }
            if ($proges_rec['GESTSP'] != 0) { //sportello on line
                $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            }

            if ($Anaatt_rec['ATTFASCICOLO']) { //attività
                return $Anaatt_rec['ATTFASCICOLO'];
            }
            if ($Anaset_rec['SETFASCICOLO']) {  //settore
                return $Anaset_rec['SETFASCICOLO'];
            }
            if ($Anatsp_rec['TSPFASCICOLO']) { //sportello
                return $Anatsp_rec['TSPFASCICOLO'];
            }
        }
        if ($procedimento) {
            $Anapra_rec = $this->GetAnapra($procedimento); //procedimento
            if ($Anapra_rec['PRAFASCICOLO'] != "") { //procedimento amministrativo
                return $Anapra_rec['PRAFASCICOLO'];
            }
        }
    }

    function GetClassificazioneProtocollazione($procedimento, $gesnum = "") {
//
//Se c'è il n. fascicolo gli viene data la precedenza per la ricerca della classificazione.
//Il procedimento in anagrafica, potrebbe essere spento o su sportello prova
//
        if ($gesnum) {
            $proges_rec = $this->GetProges($gesnum);
            if ($proges_rec['GESATT'] != 0) {   //attività
                $Anaatt_rec = $this->GetAnaatt($proges_rec['GESATT']);
            }
            if ($proges_rec['GESSTT'] != 0) { //settore commerciale
                $Anaset_rec = $this->GetAnaset($proges_rec['GESSTT']);
            }
            if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
                $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            }
            if ($proges_rec['GESTSP'] != 0) { //sportello on line
                $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            }

            if ($Anaatt_rec['ATTCLA']) { //attività
                return $Anaatt_rec['ATTCLA'];
            }
            if ($Anaset_rec['SETCLA']) {  //settore
                return $Anaset_rec['SETCLA'];
            }
            $retVisibilta = $this->GetVisibiltaSportello();
            if ($retVisibilta['AGGREGATO'] != 0) {
                if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                    if ($Anaspa_rec['SPACLA'] && $Anaspa_rec['SPATIPOPROT']) { //aggregato
                        return $Anaspa_rec['SPACLA'];
                    }
                }
            }
            if ($Anatsp_rec['TSPCLA']) { //sportello
                return $Anatsp_rec['TSPCLA'];
            }
        }
        if ($procedimento) {
            $Anapra_rec = $this->GetAnapra($procedimento); //procedimento
            if ($Anapra_rec['PRACLA'] != "") { //procedimento amministrativo
                return $Anapra_rec['PRACLA'];
            }
        }
        /*
         * Tolta la parte che andava a prendere Sportello,Settore, Attività da ANAPRA
         */
    }

    function GetUfficioCaricoProtocollazione($proges_rec) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori ad PROGES e non più da ANAPRA
         */
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            $retVisibilta = $this->GetVisibiltaSportello();
            if ($retVisibilta['AGGREGATO'] == 0) {
                if ($Anaspa_rec['SPAUOP']) {
                    return $Anaspa_rec['SPAUOP'];
                }
            } else {
                if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                    if ($Anaspa_rec['SPAUOP'] && $Anaspa_rec['SPATIPOPROT']) {
                        return $Anaspa_rec['SPAUOP'];
                    }
                }
            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec['TSPUOP']) { //sportello
                return $Anatsp_rec['TSPUOP'];
            }
        }
        return '';
    }

    function GetTipoDocumentoProtocollazione($proges_rec) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori ad PROGES e non più da ANAPRA
         */
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            $retVisibilta = $this->GetVisibiltaSportello();
            if ($retVisibilta['AGGREGATO'] != 0) {
                if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                    if ($Anaspa_rec['SPATDO'] && $Anaspa_rec['SPATIPOPROT']) {
                        return $Anaspa_rec['SPATDO'];
                    }
                }
            }
//            if ($Anaspa_rec['SPATDO']) {
//                return $Anaspa_rec['SPATDO'];
//            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec) {
                if ($Anatsp_rec['TSPTDO']) { //sportello
                    return $Anatsp_rec['TSPTDO'];
                }
            }
        }
        return '';
    }

    function GetTipoDocumentoProtocollazioneEndoPar($proges_rec) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori ad PROGES e non più da ANAPRA
         */
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            if ($Anaspa_rec) {
                $retVisibilta = $this->GetVisibiltaSportello();
                if ($retVisibilta['AGGREGATO'] != 0) {
                    if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                        if ($Anaspa_rec['SPATDOENDOPAR'] && $Anaspa_rec['SPATIPOPROT']) {
                            return $Anaspa_rec['SPATDOENDOPAR'];
                        } else if ($Anaspa_rec['SPATDO'] && $Anaspa_rec['SPATIPOPROT']) { //sportello
                            return $Anaspa_rec['SPATDO'];
                        }
                    }
                }
//                if ($Anaspa_rec['SPATDOENDOPAR']) {
//                    return $Anaspa_rec['SPATDOENDOPAR'];
//                } else if ($Anaspa_rec['SPATDO']) { //sportello
//                    return $Anaspa_rec['SPATDO'];
//                }
            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec) {
                if ($Anatsp_rec['TSPTDOENDOPAR']) {
                    return $Anatsp_rec['TSPTDOENDOPAR'];
                } else if ($Anatsp_rec['TSPTDO']) { //sportello
                    return $Anatsp_rec['TSPTDO'];
                }
            }
        }
        return '';
    }

    function GetTipoDocumentoProtocollazioneEndoArr($proges_rec) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori ad PROGES e non più da ANAPRA
         */
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            if ($Anaspa_rec) {
                $retVisibilta = $this->GetVisibiltaSportello();
                if ($retVisibilta['AGGREGATO'] != 0) {
                    if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                        if ($Anaspa_rec['SPATDOENDOPAR'] && $Anaspa_rec['SPATIPOPROT']) {
                            return $Anaspa_rec['SPATDOENDOARR'];
                        } else if ($Anaspa_rec['SPATDO'] && $Anaspa_rec['SPATIPOPROT']) { //sportello
                            return $Anaspa_rec['SPATDO'];
                        }
                    }
                }
//                if ($Anaspa_rec['SPATDOENDOARR']) {
//                    return $Anaspa_rec['SPATDOENDOARR'];
//                } else if ($Anaspa_rec['SPATDO']) { //sportello
//                    return $Anaspa_rec['SPATDO'];
//                }
            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec) {
                if ($Anatsp_rec['TSPTDOENDOARR']) {
                    return $Anatsp_rec['TSPTDOENDOARR'];
                } else if ($Anatsp_rec['TSPTDO']) { //sportello
                    return $Anatsp_rec['TSPTDO'];
                }
            }
        }
        return '';
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

    function ordinaDatiAggiuntivi($itekey) {
        if ($itekey) {
            $new_seq = 0;
            $Itedag_tab = $this->GetItedag($itekey, 'itekey', true, 'ORDER BY ITDSEQ');
            if (!$Itedag_tab) {
                return false;
            }
            foreach ($Itedag_tab as $Itedag_rec) {
                $new_seq += 10;
                $Itedag_rec['ITDSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "ITEDAG", "ROWID", $Itedag_rec);
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

    function ribaltaAllegatiGobid($procedimento, $allegato) {//, $esterna = "") {
        $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
        if (strtolower($ext) == 'p7m') {
            $ext = "pdf.p7m";
        }
        $randName = md5(rand() * time()) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
        $randName = pathinfo($randName, PATHINFO_FILENAME) . ".$ext";
        $destinazione = $this->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, 'PROGES');
        if (!$destinazione) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella cartella di destinazione.");
            return false;
        }
        if (!@copy($allegato['DATAFILE'], $destinazione . "/" . $randName)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
            return false;
        }
        $Pasdoc_rec['PASKEY'] = $procedimento;
        $Pasdoc_rec['PASNOT'] = "File Originale: " . utf8_decode($allegato['FILENAME']);
        $Pasdoc_rec['PASFIL'] = $randName;
        $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
        $Pasdoc_rec['PASUTC'] = "";
        $Pasdoc_rec['PASUTE'] = "";
        $Pasdoc_rec['PASCLA'] = "GENERALE";
        $Pasdoc_rec['PASNAME'] = utf8_decode(trim($allegato['FILENAME']));
        $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
        $Pasdoc_rec['PASDATADOC'] = date("Ymd");
        $Pasdoc_rec['PASORADOC'] = date("H:i:s");
        $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $randName);

        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento allegato al passo.");
                return false;
            }
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore inserimento allegato al passo. " . $exc->getMessage());
            return false;
        }
        return true;
    }

    function ribaltaAllegatiEsterna($procedimento, $allegati) {//, $esterna = "") {
        foreach ($allegati as $allegato) {
            $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
            if (strtolower($ext) == 'p7m') {
                $ext = "pdf.p7m";
            }
            $randName = md5(rand() * time()) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
            $randName = pathinfo($randName, PATHINFO_FILENAME) . ".$ext";
            $destinazione = $this->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, 'PROGES');
            if (!$destinazione) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella cartella di destinazione.");
                return false;
            }
            if (!@copy($allegato['DATAFILE'], $destinazione . "/" . $randName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                return false;
            }
            $Pasdoc_rec['PASKEY'] = $procedimento;
            $Pasdoc_rec['PASNOT'] = "File Originale: " . utf8_decode($allegato['FILENAME']);
            $Pasdoc_rec['PASFIL'] = $randName;
            $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
            $Pasdoc_rec['PASUTC'] = "";
            $Pasdoc_rec['PASUTE'] = "";
            $Pasdoc_rec['PASCLA'] = "GENERALE";
            $Pasdoc_rec['PASNAME'] = utf8_decode(trim($allegato['FILENAME']));
            $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $Pasdoc_rec['PASDATADOC'] = date("Ymd");
            $Pasdoc_rec['PASORADOC'] = date("H:i:s");
            $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $randName);

            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore inserimento allegato al passo.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento allegato al passo. " . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    function ribaltaAllegatiInfocamere($keyPasso, $allegati) {
        foreach ($allegati as $allegato) {
            $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
            if (strtolower($ext) == 'p7m') {
                $ext = "pdf.p7m";
            }
            $randName = md5(rand() * time()) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
            $randName = pathinfo($randName, PATHINFO_FILENAME) . ".$ext";
            $destinazione = $this->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
            if (!$destinazione) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella cartella di destinazione.");
                return false;
            }
            if (!@copy($allegato['FILEPATH'], $destinazione . "/" . $randName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                return false;
            }
            $Pasdoc_rec['PASKEY'] = $keyPasso;
//$Pasdoc_rec['PASNOT'] = $allegato['FILEINFO'];
            $Pasdoc_rec['PASNOT'] = $allegato['NOTE'];
            $Pasdoc_rec['PASFIL'] = $randName;
            $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
            $Pasdoc_rec['PASUTC'] = "";
            $Pasdoc_rec['PASUTE'] = "";
            $Pasdoc_rec['PASCLA'] = "INFOCAMERE " . $allegato['SEQUENZA'];
//$Pasdoc_rec['PASNAME'] = pathinfo($allegato['FILEINFO'], PATHINFO_BASENAME);
            $Pasdoc_rec['PASNAME'] = $allegato['FILENAME'];
            $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $Pasdoc_rec['PASDATADOC'] = date("Ymd");
            $Pasdoc_rec['PASORADOC'] = date("H:i:s");
            $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $randName);
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in registrazione allegato passo.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in registrazione allegato passo." . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    function RegistraXmlInfo($allegati, $procedimento) {
        foreach ($allegati as $allegatoPasso) {
            $ext = pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
            if ($ext == strtolower("xml") && strpos($allegatoPasso['FILENAME'], "XMLINFO") !== false) {
                $praPath = $this->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, 'PROGES');
                $randName = md5(rand() * time()) . "." . pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
                if (!$praPath) {
                    Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
                    return false;
                } else {
                    if (!@is_dir($praPath)) {
                        if (!@mkdir($praPath, 0777)) {
                            Out::msgStop("Archiviazione File", "Errore creazione cartella di destinazione.");
                            return false;
                        }
                    }
                }
                if (!@copy($allegatoPasso['DATAFILE'], $praPath . "/" . $randName)) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegatoPasso['FILENAME'] . " !");
                    return false;
                }
                $Pasdoc_rec['PASKEY'] = $procedimento;
                $Pasdoc_rec['PASNOT'] = "File Originale: " . $allegatoPasso['FILENAME'];
                $Pasdoc_rec['PASFIL'] = $randName;
                $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
                $Pasdoc_rec['PASUTC'] = "";
                $Pasdoc_rec['PASUTE'] = "";
                $Pasdoc_rec['PASCLA'] = "GENERALE";
                $Pasdoc_rec['PASNAME'] = pathinfo($allegatoPasso['FILENAME'], PATHINFO_BASENAME);
                $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $Pasdoc_rec['PASDATADOC'] = date("Ymd");
                $Pasdoc_rec['PASORADOC'] = date("H:i:s");
                $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $praPath . "/" . $randName);

                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
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

    function RegistraEml($fileEml, $procedimento) {
        $praPath = $this->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, 'PROGES');
//$randName = md5(rand() * time()) . ".eml"; // . "." . pathinfo($fileEml, PATHINFO_EXTENSION);
//Per Importazione Mail FO per pratica gia caricata perche se caricata da mail locale non si aprova dal bottone vedi mail
        /*
         * Quando viene dal carica da mail $fileEml = Z:\pc\mail\data\ente01/certificata@pec.italsoft-mc.it/5a47c81c8ec84ec615f62b7c9fb4e172.eml
         */
        $randName = pathinfo($fileEml, PATHINFO_BASENAME);

        /*
         * Quando viene dal Controlla FO $fileEml = /disk2/tmp/itaEngine/0052180017899603974-G479/6666cd76f96956469e7be39d750cc7d9/4
         * quindi se l'estensione è vuota, mi costruisco il randname e l'estensione la inseriamo noi
         */
        if (pathinfo($randName, PATHINFO_EXTENSION) == "") {
            $randName = md5(rand() * time()) . ".eml";
        }
        $passha = hash_file('sha256', $fileEml);
        $pasdoc_rec_ctr = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY = '$procedimento' AND (PASFIL = '$randName' || PASSHA2 = '$passha')", false);
        if ($pasdoc_rec_ctr) {
            Out::msgStop("Importazione Mail FO", "Il file EML della pratica n. $procedimento risulta già inserito.");
            return false;
        }
        if (!$praPath) {
            Out::msgStop("Archiviazione File", "Errore nella cartella di destinazione.");
            return false;
        } else {
            if (!@is_dir($praPath)) {
                if (!@mkdir($praPath, 0777)) {
                    Out::msgStop("Archiviazione File", "Errore creazione cartella di destinazione.");
                    return false;
                }
            }
        }
        if (!@copy($fileEml, $praPath . "/" . $randName)) {
            Out::msgStop("Importazione Mail FO", "Errore in copia del file $fileEml in $praPath/$randName");
            return false;
        }
        $Pasdoc_rec['PASKEY'] = $procedimento;
        $Pasdoc_rec['PASNOT'] = "File EML importato";
        $Pasdoc_rec['PASFIL'] = $randName;
        $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
        $Pasdoc_rec['PASUTC'] = "";
        $Pasdoc_rec['PASUTE'] = "";
        $Pasdoc_rec['PASCLA'] = "GENERALE";
        $Pasdoc_rec['PASNAME'] = $procedimento . ".eml";
        $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
        $Pasdoc_rec['PASDATADOC'] = date("Ymd");
        $Pasdoc_rec['PASORADOC'] = date("H:i:s");
        $Pasdoc_rec['PASSHA2'] = $passha;

        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
            if ($nrow != 1) {
                Out::msgStop("Archiviazione File", "Errore in salvataggio del file EML !");
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }


        return true;
    }

    function ribaltaAllegatiPassoRicdoc($Ricdoc_tab_input, $allegati, $keyPasso_sorg, $keyPasso, $seq, $procedimento, $classificaAllegati = '', $descPasso_sorg = "", $docShaSost = "", $tipoDirectoryPratiche = 'PASSO') {
        if (!$Ricdoc_tab_input[0]) {
            //Un solo allegato
            $Ricdoc_tab[0] = $Ricdoc_tab_input;
        } else {
            //Piu di un allegato
            $Ricdoc_tab = $Ricdoc_tab_input;
        }

        if ($Ricdoc_tab[0]) {
            foreach ($Ricdoc_tab as $Ricdoc_rec) {
                if ($Ricdoc_rec['ITEKEY'][itaXML::textNode] === $keyPasso_sorg) {
                    $trovato = false;
                    foreach ($allegati as $allegatoPasso) {
                        if (trim($allegatoPasso['FILENAME']) == trim(utf8_decode($Ricdoc_rec['DOCNAME'][itaXML::textNode]))) {
                            $trovato = true;
                            break;
                        }
                    }
                    if ($trovato) {
                        $ext = pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
                        if (strtolower($ext) == 'p7m') {
                            //Mi trovo l'estensione base del file
                            $Est_baseFile = $this->GetBaseExtP7MFile($allegatoPasso['FILENAME']);
                            // Mi trovo e accodo tutte le estensioni p7m
                            $Est_tmp = $this->GetExtP7MFile($allegatoPasso['FILENAME']);
                            $posPrimoPunto = strpos($Est_tmp, ".");
                            $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
                            $p7mExt = str_replace($delEst, "", $Est_tmp);
                            //Creo l'estensione finale del file
                            $ext = $Est_baseFile . "." . $p7mExt;
                        }
                        $randName = md5(rand() * time()) . "." . pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
                        $randName = pathinfo($randName, PATHINFO_FILENAME) . ".$ext";

                        if (strlen($keyPasso) > 10) {
                            $destinazione = $this->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
                        } else {
                            $destinazione = $this->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, 'PROGES');
                        }

                        if (!$destinazione) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Archiviazione File allegati: Cartella di destinazione non definita.");
                            return false;
                        } else {
                            if (!is_dir($destinazione)) {
                                if (!@mkdir($destinazione, 0777)) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage("Archiviazione File: Errore nella creazione cartella di destinazione. $destinazione.");
                                    return false;
                                }
                            }
                        }

                        if (!copy($allegatoPasso['DATAFILE'], $destinazione . "/" . $randName)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Archiviazione File: Errore in salvataggio del file " . $allegatoPasso['FILENAME'] . " !");
                            return false;
                        }


                        /*
                         * Se l'allegato FO è marcato con protocollo, marco anche il record di PASDOC
                         */
                        $class = $rowid = "";
                        if ($Ricdoc_rec['DOCPRT'][itaXML::textNode] == 1) {
                            $propas_rec = $this->GetPropas($keyPasso);
                            if ($propas_rec['PRORIN']) {
                                $pracom_rec = $this->GetPracomA($keyPasso);
                                $class = "PRACOM";
                                $rowid = $pracom_rec['ROWID'];
                            } else {
                                $proges_rec = $this->GetProges(substr($keyPasso, 0, 10));
                                $class = "PROGES";
                                $rowid = $proges_rec['ROWID'];
                            }
                        }

                        $Pasdoc_rec['PASKEY'] = $keyPasso;
                        $Pasdoc_rec['PASNOT'] = utf8_decode($descPasso_sorg);
                        $Pasdoc_rec['PASFIL'] = $randName;
                        $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
                        $Pasdoc_rec['PASPRI'] = $Ricdoc_rec['DOCPRI'][itaXML::textNode];
                        $Pasdoc_rec['PASUTC'] = "";
                        $Pasdoc_rec['PASUTE'] = "";
                        if (!$classificaAllegati) {
                            $Pasdoc_rec['PASCLA'] = "PRATICA N. " . substr($procedimento, 4) . "/" . substr($procedimento, 0, 4);
                        } else {
                            $Pasdoc_rec['PASCLA'] = $classificaAllegati;
                        }
                        $Pasdoc_rec['PASNAME'] = trim($allegatoPasso['FILENAME']);
                        $Metadati = unserialize($Ricdoc_rec['DOCMETA'][itaXML::textNode]);
                        $Pasdoc_rec['PASCLAS'] = $Metadati['CLASSIFICAZIONE'];
                        if ($Metadati['DESTINAZIONE']) {
                            $Pasdoc_rec['PASDEST'] = serialize($Metadati['DESTINAZIONE']);
                        }
                        if ($Metadati['AUTOCERTIFICAZIONE_ACCORPATA']) {
                            $Pasdoc_rec['PASCLAS'] = "AUTOCERTIFICAZIONE_ACCORPATA";
                        }
                        $Pasdoc_rec['PASNOTE'] = $Metadati['NOTE'];
                        if (!$descPasso_sorg && $Metadati['PASNOT']) {
                            $Pasdoc_rec['PASNOT'] = $Metadati['PASNOT'];
                        }
                        $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                        $Pasdoc_rec['PASDATADOC'] = date("Ymd");
                        $Pasdoc_rec['PASORADOC'] = date("H:i:s");
                        $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $randName);
                        $Pasdoc_rec['PASSHA2SOST'] = $docShaSost;
                        $Pasdoc_rec['PASPRTCLASS'] = $class;
                        $Pasdoc_rec['PASPRTROWID'] = $rowid;
                        $Pasdoc_rec['PASRIS'] = $Ricdoc_rec['DOCRIS'][itaXML::textNode];
                        if (!$this->setStatoSostFile(substr($keyPasso, 0, 10), $Pasdoc_rec['PASSHA2SOST'])) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Archiviazione File: Errore in settaggio stato allegato sostituito " . $Pasdoc_rec['PASNAME']);
                            return false;
                        }
                        try {
                            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                            if ($nrow != 1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Archiviazione File: Errore in inserimento PASDOC.");
                                return false;
                            }
                        } catch (Exception $exc) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Archiviazione File: Errore in inserimento PASDOC. " . $exc->getMessage());
                            return false;
                        }



                        $pasdocrowid = ItaDB::DBLastId($this->getPRAMDB());


                        /*
                         * Pulizia repository temporaneo PRAFOFIULES e collegamento a record PASDOC di destinazione
                         */
                        if (isset($allegatoPasso['PRAFOFILES_ROW_ID'])) {
                            // Leggo record di PRAFOFILES
                            $sql = "SELECT * FROM  PRAFOFILES"
                                    . " WHERE PRAFOFILES.ROW_ID = " . $allegatoPasso['PRAFOFILES_ROW_ID'];

                            $praFoFiles_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
                            if (!$praFoFiles_rec) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Accesso a PRAFOFILES Fallito.");
                                return false;
                            }
                            $praFoFiles_rec['PASDOCROWID'] = $pasdocrowid;
                            try {
                                $nrow = ItaDB::DBUpdate($this->getPRAMDB(), 'PRAFOFILES', 'ROW_ID', $praFoFiles_rec);
                                if ($nrow == -1) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage('Aggiornamento PRAFOFILES id allegato collegato Fallito.');
                                    return false;
                                }
                            } catch (Exception $e) {
                                $this->setErrCode(-1);
                                $this->setErrMessage('Aggiornamento PRAFOFILES id allegato collegato Fallito.' . $e->getMessage());
                                return false;
                            }
                            unlink($allegatoPasso['DATAFILE']);
                        }
                    }
                }
            }
        }
        return true;
    }

    function ribaltaAllegatiPasso($allegati, $keyPasso, $seq, $procedimento) {
        foreach ($allegati as $allegatoPasso) {
            $seq = str_repeat("0", 3 - strlen($seq)) . $seq;
            if (strpos($allegatoPasso['FILENAME'], "C" . $seq) !== false) {
                $ext = pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
                if (strtolower($ext) == 'p7m') {
                    $ext = "pdf.p7m";
                }
                $randName = md5(rand() * time()) . "." . pathinfo($allegatoPasso['FILENAME'], PATHINFO_EXTENSION);
                $randName = pathinfo($randName, PATHINFO_FILENAME) . ".$ext";
                $destinazione = $this->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso);
                if (!$destinazione) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaAllegatiPasso: Errore nella cartella di destinazione.");
                    return false;
                } else {
                    if (!@is_dir($destinazione)) {
                        if (!@mkdir($destinazione, 0777)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("ribaltaAllegatiPasso: Errore creazione cartella di destinazione.");
                            return false;
                        }
                    }
                }

                /*
                 * TODO: VERIFICARE O QUI O A MONTE SE MANCA IL DATAFILE ( PRAFOLIST GIA' ELABORATO)
                 */

                if (!@copy($allegatoPasso['DATAFILE'], $destinazione . "/" . $randName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaAllegatiPasso: Errore in salvataggio del file " . $allegatoPasso['FILENAME'] . " !");
                    return false;
                }
                $Pasdoc_rec['PASKEY'] = $keyPasso;
                $Pasdoc_rec['PASNOT'] = "File Originale: " . $allegatoPasso['FILENAME'];
                $Pasdoc_rec['PASFIL'] = $randName;
                $Pasdoc_rec['PASLNK'] = "allegato://" . $Pasdoc_rec['PASFIL'];
                $Pasdoc_rec['PASUTC'] = "";
                $Pasdoc_rec['PASUTE'] = "";
                $Pasdoc_rec['PASCLA'] = "PRATICA N. " . substr($procedimento, 4) . "/" . substr($procedimento, 0, 4);
                $Pasdoc_rec['PASNAME'] = $allegatoPasso['FILENAME'];
                $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                $Pasdoc_rec['PASDATADOC'] = date("Ymd");
                $Pasdoc_rec['PASORADOC'] = date("H:i:s");
                $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $randName);

                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaAllegatiPasso: " . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    function DecodeFileInfo($fileInfo) {//, $fileName) {
//        $chiave = substr(pathinfo($fileName, PATHINFO_FILENAME), -2, 2);
        $arrayDag = array();
        $arrayInfo = array();
        $arrayValue = array();
        $arrayField = array();
        $strInfo = file_get_contents($fileInfo['DATAFILE']);
        if ($strInfo) {
            $arrayInfo = explode('---', $strInfo);
            unset($arrayInfo[0]);
            foreach ($arrayInfo as $field) {
                $arrayField = explode(chr(10), $field);
                $keyValue = "";
                foreach ($arrayField as $value) {
                    $arrayValue = explode(':', $value);
                    if (trim($arrayValue[0]) == 'FieldName') {
                        $keyName = trim($arrayValue[1]);
                    }
                    if (trim($arrayValue[0]) == 'FieldValue') {
                        $keyValue = trim($arrayValue[1]);
                    }
                    if (trim($arrayValue[0]) == 'TabOrder') {
                        $tabOrder = trim($arrayValue[1]);
                    }
                }
                if ($keyName) {
                    $arrayDag[$keyName]['value'] = $keyValue;
                    $arrayDag[$keyName]['taborder'] = $tabOrder;
                }
            }
        }
        uasort($arrayDag, array('praLib', 'sortInfoByTabOrder'));
        return $arrayDag;
    }

    function sortInfoByTabOrder($a, $b) {
        if ($a['taborder'] == $b['taborder']) {
            return 0;
        }
        return ($a['taborder'] < $b['taborder']) ? -1 : 1;
    }

    function CancellaPassi($Propas_tab, $pratica) {
        if ($Propas_tab) {
            foreach ($Propas_tab as $key => $Propas_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                    return false;
                }
            }
            $Prodag_tab = $this->GetProdag($pratica, 'numero', true);
            if ($Prodag_tab) {
                foreach ($Prodag_tab as $key => $Prodag_rec) {
                    try {
                        $nrow = ItaDb::DBDelete($this->PRAM_DB, 'PRODAG', 'ROWID', $Prodag_rec['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        Out::msgStop("Errore", $e->getMessage());
                        return false;
                    }
                }
            }
            return true;
        }
    }

    function ribaltaDatiAggiuntivi($keyPasso) {
        $Propas_rec = $this->GetPropas($keyPasso);
        $sql = "SELECT * FROM PASDAG WHERE PASCOD = '" . $Propas_rec['PROCLT'] . "'";
        $Pasdag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        if ($Pasdag_tab) {
            foreach ($Pasdag_tab as $Pasdag_rec) {
                $Prodag_rec["DAGNUM"] = $Propas_rec['PRONUM'];
                $Prodag_rec["DAGCOD"] = $Propas_rec['PROPRO'];
                $Prodag_rec["DAGSEQ"] = 0;
                $Prodag_rec["DAGDES"] = "";
                $Prodag_rec["DAGSFL"] = $Pasdag_rec['PASSEQ'];
                $Prodag_rec["DAGKEY"] = $Pasdag_rec["PASIDC"];
                $Prodag_rec["DAGVAL"] = "";
                $Prodag_rec["DAGDEF"] = "";
                $Prodag_rec["DAGPAK"] = $keyPasso;
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRODAG", "ROWID", $Prodag_rec);
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

    function ribaltaDatiAggEsterna($keyPasso, $keyPassoPropas) {
        $Propas_rec = $this->GetPropas($keyPassoPropas);
        $sql = "SELECT * FROM ITEDAG WHERE ITECOD = '" . $Propas_rec['PROPRO'] . "' AND ITEKEY = '" . $keyPasso . "'";
        $Itedag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);

        if ($Itedag_tab) {
            foreach ($Itedag_tab as $Itedag_rec) {
                if (!$this->ribaltaDatoAggEsterna($Propas_rec, $Itedag_rec)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function ribaltaDatoAggEsterna($Propas_rec, $Itedag_rec) {
        $Prodag_rec["DAGNUM"] = $Propas_rec['PRONUM'];
        $Prodag_rec["DAGCOD"] = $Propas_rec['PROPRO'];
        $Prodag_rec["DAGSEQ"] = 0;
        $Prodag_rec["DAGSFL"] = $Itedag_rec['ITDSEQ'];
        $Prodag_rec["DAGDES"] = $Itedag_rec['ITDDES'];
        $Prodag_rec["DAGKEY"] = $Itedag_rec["ITDKEY"];
        $Prodag_rec["DAGALIAS"] = $Itedag_rec["ITDALIAS"];
        $Prodag_rec["DAGVAL"] = '';
        $Prodag_rec["DAGDEF"] = $Itedag_rec["ITDVAL"];
        $Prodag_rec["DAGPAK"] = $Propas_rec['PROPAK'];
        $Prodag_rec["DAGSET"] = $Propas_rec['PROPAK'] . "_01";
        $Prodag_rec["DAGTIP"] = $Itedag_rec["ITDTIP"];
        $Prodag_rec["DAGCTR"] = $Itedag_rec["ITDCTR"];
        $Prodag_rec["DAGNOT"] = $Itedag_rec["ITDNOT"];
        $Prodag_rec["DAGLAB"] = $Itedag_rec["ITDLAB"];
        $Prodag_rec["DAGTIC"] = $Itedag_rec["ITDTIC"];
        $Prodag_rec["DAGROL"] = $Itedag_rec["ITDROL"];
        $Prodag_rec["DAGREV"] = $Itedag_rec["ITDREV"];
        $Prodag_rec["DAGLEN"] = $Itedag_rec["ITDLEN"];
        $Prodag_rec["DAGDIM"] = $Itedag_rec["ITDDIM"];
        $Prodag_rec["DAGDIZ"] = $Itedag_rec["ITDDIZ"];
        $Prodag_rec["DAGACA"] = $Itedag_rec["ITDACA"];
        $Prodag_rec["DAGPOS"] = $Itedag_rec["ITDPOS"];
        $Prodag_rec["DAGMETA"] = $Itedag_rec["ITDMETA"];
        $Prodag_rec["DAGLABSTYLE"] = $Itedag_rec["ITDLABSTYLE"];
        $Prodag_rec["DAGFIELDSTYLE"] = $Itedag_rec["ITDFIELDSTYLE"];
        $Prodag_rec["DAGFIELDCLASS"] = $Itedag_rec["ITDFIELDCLASS"];
        $Prodag_rec["DAGEXPROUT"] = $Itedag_rec["ITDEXPROUT"];
        $Prodag_rec["DAGCLASSE"] = $Itedag_rec["ITDCLASSE"];
        $Prodag_rec["DAGMETODO"] = $Itedag_rec["ITDMETODO"];
        $Prodag_rec["DAGVCA"] = $Itedag_rec["ITDVCA"];
        $Prodag_rec["DAGFIELDERRORACT"] = $Itedag_rec["ITDFIELDERRORACT"];

        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRODAG", "ROWID", $Prodag_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage("ribaltaDatoAggEsterna: inserimento dato aggiuntivo fallito");
                return false;
            }
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("ribaltaDatoAggEsterna: " . $exc->getMessage());
            return false;
        }

        return true;
    }

    /**
     *
     * Carica dati aggiuntivi da Front-office
     *
     * @param type $keyPasso
     * @return type
     */
    function ribaltaDatiAggiuntiviRicdag($keyPasso, $Ricdag_tab, $keyFrontOffice, $pratica = "", $descrizioneSet = '') {
        $Propas_rec = $this->GetPropas($keyPasso);
        if ($pratica) {
            $proges_rec = $this->GetProges($pratica);
            $Npratica = $proges_rec['GESNUM'];
            $Procedimento = $proges_rec['GESPRO'];
        } else {
            $Propas_rec = $this->GetPropas($keyPasso);
            $Npratica = $Propas_rec['PRONUM'];
            $Procedimento = $Propas_rec['PROPRO'];
        }
        if ($Ricdag_tab) {
            if (!$Ricdag_tab[0]) {
                $arr = array();
                $arr[] = $Ricdag_tab;
            } else {
                $arr = $Ricdag_tab;
            }

//
//          Creo i data set
//
            $arrDataSet = array();
            foreach ($arr as $Ricdag_rec) {
                $controllo = $Ricdag_rec['ITEKEY'][itaXML::textNode];
                if ($controllo === $keyFrontOffice) {
                    if (!array_key_exists($Ricdag_rec["DAGSET"][itaXML::textNode], $arrDataSet)) {
                        $arrDataSet[$Ricdag_rec["DAGSET"][itaXML::textNode]] = "";
                    }
                }
            }

            $sql = "SELECT DISTINCT DAGSET FROM PRODAG WHERE DAGPAK = '" . $keyPasso . "' ORDER BY DAGSET DESC";
            $dataSet_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false, 1, 1);
            $inc = substr($dataSet_rec['DAGSET'], -2);
            foreach ($arrDataSet as $key => $dset) {
                $inc += 1;
                $incDagset = str_repeat("0", 2 - strlen($inc)) . $inc;
                $Prodst_rec["DSTSET"] = $keyPasso . '_' . $incDagset;
                if ($descrizioneSet) {
                    $Prodst_rec["DSTDES"] = $descrizioneSet . "_" . $incDagset;
                } else {
                    $Prodst_rec["DSTDES"] = $key;
                }
                $arrDataSet[$key] = $keyPasso . '_' . $incDagset;
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRODST", "ROWID", $Prodst_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("ribaltaDatiAggiuntiviRicdag: Errore inserimento DATASET");
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaDatiAggiuntiviRicdag: " . $exc->getMessage());
                    return false;
                }
            }
            foreach ($arr as $Ricdag_rec) {
                $controllo = $Ricdag_rec['ITEKEY'][itaXML::textNode];
                if ($controllo === $keyFrontOffice) {
//$Prodag_rec["DAGNUM"] = $Propas_rec['PRONUM'];
//$Prodag_rec["DAGCOD"] = $Propas_rec['PROPRO'];
                    $Prodag_rec["DAGNUM"] = $Npratica;
                    $Prodag_rec["DAGCOD"] = $Procedimento;
                    $Prodag_rec["DAGSEQ"] = $Ricdag_rec['DAGSEQ'][itaXML::textNode];
                    $Prodag_rec["DAGDES"] = $Ricdag_rec['DAGDES'][itaXML::textNode];
                    $Prodag_rec["DAGSFL"] = 0;
                    $Prodag_rec["DAGKEY"] = $Ricdag_rec["DAGKEY"][itaXML::textNode];
                    $Prodag_rec["DAGALIAS"] = $Ricdag_rec["DAGALIAS"][itaXML::textNode];
                    $Prodag_rec["DAGVAL"] = utf8_decode($Ricdag_rec["RICDAT"][itaXML::textNode]);
                    $Prodag_rec["DAGDEF"] = $Ricdag_rec["DAGVAL"][itaXML::textNode];
                    $Prodag_rec["DAGPAK"] = $keyPasso;
                    $Prodag_rec["DAGSET"] = $arrDataSet[$Ricdag_rec["DAGSET"][itaXML::textNode]]; //$keyPasso . '_' . $incDagset;
                    $Prodag_rec["DAGTIP"] = $Ricdag_rec["DAGTIP"][itaXML::textNode];
                    $Prodag_rec["DAGCTR"] = $Ricdag_rec["DAGCTR"][itaXML::textNode];
                    $Prodag_rec["DAGNOT"] = $Ricdag_rec["DAGNOT"][itaXML::textNode];
                    $Prodag_rec["DAGLAB"] = $Ricdag_rec["DAGLAB"][itaXML::textNode];
                    $Prodag_rec["DAGTIC"] = $Ricdag_rec["DAGTIC"][itaXML::textNode];
                    $Prodag_rec["DAGROL"] = $Ricdag_rec["DAGROL"][itaXML::textNode];
                    $Prodag_rec["DAGVCA"] = $Ricdag_rec["DAGVCA"][itaXML::textNode];
                    $Prodag_rec["DAGFIELDERRORACT"] = $Ricdag_rec["DAGFIELDERRORACT"][itaXML::textNode];
                    $Prodag_rec["DAGREV"] = $Ricdag_rec["DAGREV"][itaXML::textNode];
                    $Prodag_rec["DAGLEN"] = $Ricdag_rec["DAGLEN"][itaXML::textNode];
                    $Prodag_rec["DAGDIM"] = $Ricdag_rec["DAGDIM"][itaXML::textNode];
                    $Prodag_rec["DAGDIZ"] = $Ricdag_rec["DAGDIZ"][itaXML::textNode];
                    $Prodag_rec["DAGACA"] = $Ricdag_rec["DAGACA"][itaXML::textNode];
                    if ($Prodag_rec["DAGKEY"] == "") {
                        $Prodag_rec["DAGKEY"] = $Prodag_rec["DAGALIAS"];
                    }
                    try {
                        $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRODAG", "ROWID", $Prodag_rec);
                        if ($nrow != 1) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("ribaltaDatiAggiuntiviRicdag: Errore inserimento Dato Aggiuntivo");
                            return false;
                        }
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("ribaltaDatiAggiuntiviRicdag: Errore inserimento Dato Aggiuntivo. " . $exc->getMessage());
                        Out::msgStop("Errore", $exc->getMessage());
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function SetDirectoryAggiornamenti($crea = true) {
        $ditta = App::$utente->getKey('ditta');

        $d_dir = Config::getPath('general.itaProc') . 'ente' . $ditta . '/aggiornamenti';
        if (!is_dir($d_dir)) {
            if ($crea == true) {
                if (!@mkdir($d_dir, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir;
    }

    public function SetDirectoryAggiornati($crea = true) {
        $ditta = App::$utente->getKey('ditta');
        $d_dir = Config::getPath('general.itaProc') . 'ente' . $ditta . '/aggiornati';
        if (!is_dir($d_dir)) {
            if ($crea == true) {
                if (!@mkdir($d_dir, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir;
    }

    public function SetDirectorySyncLog($crea = true) {
        $ditta = App::$utente->getKey('ditta');
        $d_dir = Config::getPath('general.itaProc') . 'ente' . $ditta . '/log';
        if (!is_dir($d_dir)) {
            if ($crea == true) {
                if (!@mkdir($d_dir, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir;
    }

    public function SetDirectoryProcedimenti($keyProc, $tipo = "allegati", $crea = true) {
//      if ($ditta == '')
        $ditta = App::$utente->getKey('ditta');
        switch ($tipo) {
            case "allegati":
                $d_nome = $tipo . '/' . $keyProc;
                break;
        }
        $d_dir = Config::getPath('general.itaProc') . 'ente' . $ditta . '/';
        if (!is_dir($d_dir . $d_nome)) {
            if ($crea == true) {
                if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

    public function SetDirectoryPratiche($anno, $keyProc, $tipo = "PASSO", $crea = true, $ditta = '') {
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        switch ($tipo) {
            case "PASSO":
                $d_nome = $anno . '/' . $tipo . '/' . $keyProc;
                break;
            case "ANAPRA":
                $d_nome = $tipo . '/' . $keyProc;
                break;
            case "PROGES":
                $d_nome = $anno . '/' . $tipo . '/' . $keyProc;
                break;
            case "PEC":
                $d_nome = $tipo . '/';
                break;
            case "CART":
            case "cart-ws":
                $d_nome = $anno . '/CART/' . $keyProc;
                break;
            case "STARWS":
            case "star-ws":
                $d_nome = $anno . '/STARWS/' . $keyProc;
                break;
            case "italsoft-local":
                $ditta = App::$utente->getKey('ditta');
                return $this->getPathAllegatiRichieste() . "attachments/" . $keyProc;
                break;
        }
        $d_dir = Config::getPath('general.itaPaim') . 'pram' . $ditta . '/';
        if (!is_dir($d_dir . $d_nome)) {
            if ($crea == true) {
                if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

    public function GetItepasDaMaster($Pram_master, $codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD='$codice'";
        } else if ($tipo == 'itekey') {
            $sql = "SELECT * FROM ITEPAS WHERE ITEKEY='$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITEPAS WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($Pram_master, $sql, $multi);
    }

    public function GetItepas($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD='$codice'";
        } else if ($tipo == 'itekey') {
            $sql = "SELECT * FROM ITEPAS WHERE ITEKEY='$codice'";
        } else if ($tipo == 'templatekey') {
            $sql = "SELECT * FROM ITEPAS WHERE TEMPLATEKEY LIKE '$codice%'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITEPAS WHERE ROWID='$codice'";
        } else if ($tipo == 'itekpre') {
            $sql = "SELECT * FROM ITEPAS WHERE ITEKPRE='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItelis($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ITELIS WHERE ITEKEY='$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITELIS WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItelisval($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ITELISVAL WHERE CODLISVAL = '$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITELISVAL WHERE ROWID = '$codice'";
        }

        if ($where) {
            $sql .= ' ' . $where;
        }

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetItedag($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'itekey') {
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY='$codice'";
        } else if ($tipo == 'codice') {
            $sql = "SELECT * FROM ITEDAG WHERE ITECOD='$codice'";
        } else {
            $sql = "SELECT * FROM ITEDAG WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPrafodecode($codice, $tipo = 'rowid', $multi = false, $where = '') {
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM PRAFODECODE WHERE ROW_ID='$codice'";
        } else if ($tipo == 'codSrc') {
            $sql = "SELECT * FROM PRAFODECODE WHERE FOSRCKEY='$codice'";
        }

        if ($where)
            $sql .= ' ' . $where;

        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetPrasta($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRASTA WHERE STANUM='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM PRASTA WHERE ROWID='$codice'";
        }
        $PRAMDB = $this->getPRAMDB();
        return ItaDB::DBSQLSelect($PRAMDB, $sql, $multi);
    }

    public function GetItevpadett($codice, $tipo = 'rowid', $multi = true) {
        return $this->GetItevpadettDaMaster($this->getPRAMDB(), $codice, $tipo, $multi);
    }

    public function GetItevpadettDaMaster($Pram_master, $codice, $tipo = 'rowid', $multi = true) {
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM ITEVPADETT WHERE ROW_ID = '$codice'";
        } elseif ($tipo == 'itecod') {
            $sql = "SELECT * FROM ITEVPADETT WHERE ITECOD = '$codice'";
        } elseif ($tipo == 'itekey') {
            $sql = "SELECT * FROM ITEVPADETT WHERE ITEKEY = '$codice'";
        } elseif ($tipo == 'itevpa') {
            $sql = "SELECT * FROM ITEVPADETT WHERE ITEVPA = '$codice'";
        }

        return ItaDB::DBSQLSelect($Pram_master, $sql, $multi);
    }

    public function GetProvpadett($codice, $tipo = 'propak', $multi = true) {
        return $this->GetProvpadettDaMaster($this->getPRAMDB(), $codice, $tipo, $multi);
    }

    public function GetProvpadettDaMaster($Pram_master, $codice, $tipo = 'propak', $multi = true) {
        if ($tipo == 'rowid') {
            $sql = "SELECT * FROM PROVPADETT WHERE ROW_ID = '$codice'";
        } elseif ($tipo == 'propro') {
            $sql = "SELECT * FROM PROVPADETT WHERE PROPRO = '$codice'";
        } elseif ($tipo == 'propak') {
            $sql = "SELECT * FROM PROVPADETT WHERE PROPAK = '$codice' ORDER BY PROSEQEXPR";
        } elseif ($tipo == 'provpa') {
            $sql = "SELECT * FROM PROVPADETT WHERE PROVPA = '$codice' ORDER BY PROPRO";
        }

        return ItaDB::DBSQLSelect($Pram_master, $sql, $multi);
    }

    public function GetParamBO($codice, $tipo = 'codice', $multi = false, $where = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PARAMBO WHERE PRANUM='$codice'";
        } else {
            $sql = "SELECT * FROM PARAMBO WHERE ROWID='$codice'";
        }
        if ($where)
            $sql .= ' ' . $where;
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    /**
     * 
     * @param type $Proges_rec
     * @param type $allegati
     * @param type $Itepas_rec
     * @param type $Anapra_rec
     * @param type $currSeq
     * @param type $extraParam array di parametri di controllo
     *                         Valori possibili:
     *                         [VALORESP] = 1/0 se 1 NON valorizza il resposnsabile
     *                         [PASSOBO]  = 1/0 se 1 attivato caricamento automatico da BO
     *                         [PROFILO]  array dati generici profilo utente
     *                         [GENERA_PROPAK] = true/false
     *                                           true = genera PROPAK con $this->PropakGenerator
     *                                           false = genera PROPAK da ITEKEY
     * @return boolean
     */
    public function ribaltaPasso($Proges_rec, $allegati, $Itepas_rec, $Anapra_rec, $currSeq, $extraParam = array()) {
        $Praclt_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $Itepas_rec['ITECLT'] . "'", false);
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        if ($Proges_rec['GESWFPRO']) {
            $Propas_rec['PROPRO'] = $Proges_rec['GESWFPRO'];
        } else {
            $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        }

        $Propas_rec['PRORES'] = $Proges_rec['GESRES'];
        $Propas_rec['PROSEQ'] = $currSeq;
        $Propas_rec['PROPUB'] = $Itepas_rec['ITEPUB'];
//
//Se il responsabile del passo è diverso da quello del procedimento, prendo quello del procedimento
//
        $Propas_rec['PRORPA'] = $Itepas_rec['ITERES'];
        if ($Anapra_rec['PRARES'] != $Itepas_rec['ITERES']) {
            $Propas_rec['PRORPA'] = $Anapra_rec['PRARES'];
        }
        if ($extraParam['VALORESP'] == 1) {
            $Propas_rec['PRORPA'] = "";
        }
        if ($Itepas_rec['ITEAPRIAUTO'] == 1) {
            $Propas_rec['PROINI'] = date('Ymd');
        }
        switch ($Itepas_rec['ITEASSAUTO']) {
            case 'UTENTE':
                $Propas_rec['PRORPA'] = $extraParam['PROFILO']['COD_ANANOM'];
                break;
            case 'RESP_PASSO':
                $Propas_rec['PRORPA'] = $Itepas_rec['ITERES'];
                break;
            case 'RESP_PROCEDIMENTO':
                $Propas_rec['PRORPA'] = $Proges_rec['GESRES']; //$Anapra_rec['PRARES'];
                break;
        }
        $Propas_rec['PROUOP'] = $Itepas_rec['ITEOPE'];
        $Propas_rec['PROSET'] = $Itepas_rec['ITESET'];
        $Propas_rec['PROSER'] = $Itepas_rec['ITESER'];
        $Propas_rec['PROGIO'] = $Itepas_rec['ITEGIO'];
        $Propas_rec['PROCLT'] = $Itepas_rec['ITECLT'];
        $Propas_rec['PRODTP'] = $Praclt_rec['CLTDES'];
        $Propas_rec['PRODPA'] = $Itepas_rec['ITEDES'];
        $Propas_rec['PROQST'] = $Itepas_rec['ITEQST'];
        $Propas_rec['PROCOM'] = $Itepas_rec['ITECOM'];
        $Propas_rec['PRODAT'] = $Itepas_rec['ITEDAT'];
        $Propas_rec['PROIRE'] = $Itepas_rec['ITEIRE'];
        $Propas_rec['PROZIP'] = $Itepas_rec['ITEZIP'];
        $Propas_rec['PRODRR'] = $Itepas_rec['ITEDRR'];
        $Propas_rec['PROIDR'] = $Itepas_rec['ITEIDR'];
        $Propas_rec['PROPDR'] = $Itepas_rec['ITEPDR'];
        $Propas_rec['PRODIS'] = $Itepas_rec['ITEDIS'];
        $Propas_rec['PRODAT'] = $Itepas_rec['ITEDAT'];
        $Propas_rec['PRODOW'] = $Itepas_rec['ITEDOW'];
        $Propas_rec['PROUPL'] = $Itepas_rec['ITEUPL'];
        $Propas_rec['PROMLT'] = $Itepas_rec['ITEMLT'];
        $Propas_rec['PROOBL'] = $Itepas_rec['ITEOBL'];
        $Propas_rec['PROMETA'] = $Itepas_rec['ITEMETA'];
        $Propas_rec['PRORICUNI'] = $Itepas_rec['ITERICUNI'];
        $Propas_rec['PROSTATO'] = $Itepas_rec['ITEDEFSTATO'];
        $Propas_rec['PRORDM'] = $Itepas_rec['ITERDM'];
        $Propas_rec['PROPRI'] = $Itepas_rec['ITEPRI'];


        if ($extraParam['GENERA_PROPAK'] == true) {
            $Propas_rec['PROPAK'] = $this->PropakGenerator($Propas_rec['PRONUM']);
        } else {
            $Propas_rec['PROPAK'] = $Propas_rec['PRONUM'] . substr($Itepas_rec['ITEKEY'], 6);
        }

        if ($Itepas_rec['ITEKPRE']) {
            $Propas_rec['PROKPRE'] = $Propas_rec['PRONUM'] . substr($Itepas_rec['ITEKPRE'], 6);
        }
        $Propas_rec['PROVPA'] = '';
        if ($Itepas_rec['ITEVPA'] != '') {
            $Propas_rec['PROVPA'] = $Propas_rec['PRONUM'] . substr($Itepas_rec['ITEVPA'], 6);
        }
        if ($Itepas_rec['ITEVPN'] != '') {
            $Propas_rec['PROVPN'] = $Propas_rec['PRONUM'] . substr($Itepas_rec['ITEVPN'], 6);
        }
        if ($Itepas_rec['ITECOM'] != 0) {
            if ($Itepas_rec['ITEINT'] == 1)
                $Propas_rec['PROINT'] = $Itepas_rec['ITEINT'];
            if ($Itepas_rec['ITECDE'] != "")
                $Propas_rec['PROCDE'] = $Itepas_rec['ITECDE'];
            if ($Itepas_rec['ITECOMDEST'] != 0)
                $Propas_rec['PROCOMDEST'] = $Itepas_rec['ITECOMDEST'];
            $Propas_rec['PROTBA'] = $Itepas_rec['ITETBA'];
        }
        $seqPasso = str_repeat("0", 3 - strlen($Propas_rec['PROSEQ'])) . $Propas_rec['PROSEQ'];
        foreach ($allegati as $key => $allegato) {
            if (strpos($allegato['FILENAME'], "C" . $seqPasso) !== false) {
                $alle[] = $allegato['FILENAME'];
                $Propas_rec['PROALL'] = serialize($alle);
            }
        }
        if ($Itepas_rec['ITEPUB'] == 1) {
            $Propas_rec['PROITK'] = $Itepas_rec['ITEKEY'];
            $Propas_rec['PROINI'] = $Proges_rec['GESDRE'];
            $Propas_rec['PROFIN'] = $Proges_rec['GESDRE'];
            $Propas_rec['PROUTEADD'] = "@ADMIN@";
            $Propas_rec['PROUTEEDIT'] = "@ADMIN@";
            $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date('H:i:s');
            $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date('H:i:s');
            $Propas_rec['PROVISIBILITA'] = "Protetto";
        } else {
            $Propas_rec['PROITK'] = $Itepas_rec['ITEKEY'];
            $Propas_rec['PROUTEADD'] = App::$utente->getKey('nomeUtente');
            $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
            $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date('H:i:s');
            $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date('H:i:s');
            $Propas_rec['PROVISIBILITA'] = "Aperto";
        }
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage('Inserimento Testata Pratica Fallito.');
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
        $insertedRowid = $this->getPRAMDB()->getLastId();
        return $insertedRowid;
    }

    /**
     * Elabora la variabile metadati diagramma del procedimento di riferimento dellla pratica
     * e la restituisce pronta per il salvataggio
     * 
     * @param array $Proges_rec
     *      'GESNUM' => string      codice pratica
     *      'GESWFPRO' => string    procedimento workflow do riferimento
     * @return boolean/string       false se ci sono errori, la stringa dei metadati di diagramma se non ci sono errori
     */
    public function setMetaDatiDiagramma($Proges_rec) {

        /*
         * Non si ha un endo procedimento workflow di riferimento
         * 
         */
        if (!$Proges_rec['GESWFPRO']) {
            return '';
        }

        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Ribalta passi: Dati Pratica Mancanti.');
            return false;
        }

        $Anapra_wf_rec = $this->GetAnapra($Proges_rec['GESWFPRO']);
        if (!$Anapra_wf_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Preparazione metadati diagramma: Impossibile trovare il codice endo-procedimento nell'anagrafica.");
            return false;
        }

        if (!$Anapra_wf_rec['PRADIAG']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Preparazione metadati diagramma: Metadati non presenti.");
            return false;
        }

        $propas_tab = $this->GetPropas($Proges_rec['GESNUM'], "codice", true);
        if (!$propas_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Preparazione metadati diagramma: Passi workflow non presenti, metadati non elaborabili");
            return false;
        }

        $gesdiag = $Anapra_wf_rec['PRADIAG'];
        foreach ($propas_tab as $propas_rec) {
            $gesdiag = str_replace($propas_rec['PROITK'], $propas_rec['PROPAK'], $gesdiag);
        }

        return $gesdiag;
    }

    public function ribaltaDiagPassiGruppi($Proges_rec, $PRAM_DB) {
        /*
         * Estrazione Gruppi di Controllo diagramma
         */
        $Itediagruppi_wf_tab = false;
        $sql_gruppi_wf = "SELECT * FROM ITEDIAGGRUPPI WHERE PRANUM='{$Proges_rec['GESWFPRO']}'";
        $Itediagruppi_wf_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql_gruppi_wf, true);

        if (!$Itediagruppi_wf_tab) {
            return true;
        }

        foreach ($Itediagruppi_wf_tab as $Itediagruppi_wf_rec) {
            $prodiagGruppi_rec = array(
                'DESCRIZIONE' => $Itediagruppi_wf_rec['DESCRIZIONE'],
                'GESNUM' => $Proges_rec['GESNUM'],
                'STATO' => $Itediagruppi_wf_rec['STATO']
            );

            try {
                Itadb::DBInsert($this->getPRAMDB(), 'PRODIAGGRUPPI', 'ROW_ID', $prodiagGruppi_rec);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Ribalta gruppi: Errore in inserimento<br>" . $exc->getMessage());
                return false;
            }
            $prodiagGruppi_row_id = $this->PRAM_DB->getLastId();



            /*
             * Estrazione passi abbinati al singolo gruppo e ribaltamento di Controllo diagramma
             */
            $Itediapassigruppi_wf_tab = false;
            $sql_passigruppi_wf = "SELECT * FROM ITEDIAGPASSIGRUPPI WHERE ROW_ID_ITEDIAGGRUPPI = " . $Itediagruppi_wf_rec['ROW_ID'];
            $Itediapassigruppi_wf_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql_passigruppi_wf, true);
            foreach ($Itediapassigruppi_wf_tab as $Itediapassigruppi_wf_rec) {

                $sql_currpasso = "SELECT * FROM PROPAS WHERE PRONUM = '{$Proges_rec['GESNUM']}' AND PROITK = '{$Itediapassigruppi_wf_rec['ITEKEY']}'";
                $Propas_curr_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql_currpasso, false);
                if (!$Propas_curr_rec) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Ribalta passi abbinati ai gruppi: Passo di riferimento nel procedimento non disponibile.");
                    return false;
                }
                $prodiagPassiGruppi_rec = array(
                    'PROPAK' => $Propas_curr_rec['PROPAK'],
                    'ROW_ID_PRODIAGGRUPPI' => $prodiagGruppi_row_id
                );
                try {
                    Itadb::DBInsert($this->getPRAMDB(), 'PRODIAGPASSIGRUPPI', 'ROW_ID', $prodiagPassiGruppi_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Ribalta passi abbinati ai gruppi: Errore in inserimento<br>" . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Ribalta passi e dati collegati da anagrafica endo-procedimento-workflow
     *
     * @param type $procedimento
     * @param type $allegati
     * @return type
     */
    public function ribaltaPassiWorkflow($procedimento, $allegati, $daMaster = true) {

        $Proges_rec = $this->GetProges($procedimento);
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Ribalta passi: errore lettura pratica.');
            return false;
        }

        if (!$Proges_rec['GESWFPRO']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ribalta passi: Codice endo-procedimento non definito.");
            return false;
        }

        $Anapra_wf_rec = $this->GetAnapra($Proges_rec['GESWFPRO']);
        if (!$Anapra_wf_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ribalta passi: Impossibile trovare il codice endo-procedimento nell'anagrafica.");
            return false;
        }

        /*
         *  Se c'è l'ente master prendi i passi da db MASTER
         * 
         */
        if ($daMaster) {
            $tipoEnte = $this->GetTipoEnte();
            if ($tipoEnte == "M") {
                $ditta = App::$utente->getKey('ditta');
                $PRAM_DB = $this->getPRAMDB();
            } else {
                if ($Anapra_wf_rec['PRASLAVE'] == 1) {
                    $ditta = $this->GetEnteMaster();
                    $PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                } else {
                    $ditta = App::$utente->getKey('ditta');
                    $PRAM_DB = $this->getPRAMDB();
                }
            }
        } else {
            $ditta = App::$utente->getKey('ditta');
            $PRAM_DB = $this->getPRAMDB();
        }

        /*
         * Estrazione Passi endo-procedimento-wf
         */
        $Itepas_wf_tab = false;
        $sql_wf = "SELECT
                ITEPAS.*,
                PRACLT.CLTDES AS CLTDES
            FROM ITEPAS
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
            WHERE ITECOD='" . $Proges_rec['GESWFPRO'] . "' ORDER BY ITESEQ";
        $Itepas_wf_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql_wf, true);
        /*
         * Parametri aggiuntivi di controllo inserimento
         */
        $extraParam = array();

        /*
         * Ultima sequenza utilizzata nel caso di aggiunta passi
         */
        $new_seq = 0;

        /*
         * Inserimento Passi procedimento 
         */
        foreach ($Itepas_wf_tab as $Itepas_rec) {
            $seq = $new_seq += 10;
            $rowidPropas = $this->ribaltaPasso($Proges_rec, $allegati, $Itepas_rec, $Anapra_wf_rec, $new_seq, $extraParam);
            $Propas_rec = $this->GetPropas($rowidPropas, "rowid");
            if ($allegati) {
                if (!$this->ribaltaAllegatiPasso($allegati, $Propas_rec['PROPAK'], $seq, $procedimento)) {
                    return false;
                }
            }
            if (!$this->ribaltaDatiAggEsterna($Itepas_rec['ITEKEY'], $Propas_rec['PROPAK'])) {
                return false;
            }
        }

        /*
         * Rileggo i passi inseriti
         * 
         */
        $propas_tab = $this->GetPropas($procedimento, "codice", true);

        /*
         * Ribalto la tabella di salto passo multiplo
         */
        foreach ($propas_tab as $propas_rec) {
            if (!$this->ribaltaCondizioniSaltaPasso($propas_rec)) {
                return false;
            }
        }

        if (!$this->ribaltaDiagPassiGruppi($Proges_rec, $PRAM_DB)) {
            return false;
        }

        /*
         * Ribaltamento dati aggiuntivi di procedimento (senza passo)
         */

        $codiceModello = $Proges_rec['GESWFPRO'];
        $sql = "SELECT * FROM ITEDAG WHERE ITECOD = '{$codiceModello}' AND ITEKEY = '{$codiceModello}'";
        $itedag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql);
        foreach ($itedag_tab as $itedag_rec) {
            if (!$this->ribaltaDatoAggEsterna(array(
                        'PRONUM' => $procedimento,
                        'PROPRO' => $codiceModello,
                        'PROPAK' => $procedimento
                            ), $itedag_rec)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Ribalta passi e dati collegati da anagrafica procedimento ANAPRA nell'inserimento diretto
     *
     * @param type $procedimento
     * @param type $allegati
     * @param type $senzaSuap
     * @return type
     */
    public function ribaltaPassi($procedimento, $allegati, $senzaSuap = false, $daMaster = true) {

        $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $procedimento . "'";
        $Proges_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Ribalta passi: errore lettura pratica.');
            return false;
        }

        /*
         * Caricamento Alternativo dei passi workflow
         * 
         */
        if ($Proges_rec['GESWFPRO']) {
            return $this->ribaltaPassiWorkflow($procedimento, $allegati, $daMaster);
        }


        $Anapra_rec = $this->GetAnapra($Proges_rec['GESPRO']);
        if (!$Anapra_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ribalta passi: Impossibile trovare il codice procedimento nell'anagrafica.");
            return false;
        }


        /*
         *  Se c'è l'ente master prendi i passi da db MASTER
         * 
         */
        if ($daMaster) {
            $tipoEnte = $this->GetTipoEnte();
            if ($tipoEnte == "M") {
                $ditta = App::$utente->getKey('ditta');
                $PRAM_DB = $this->getPRAMDB();
            } else {
                if ($Anapra_rec['PRASLAVE'] == 1) {
                    $ditta = $this->GetEnteMaster();
                    $PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                } else {
                    $ditta = App::$utente->getKey('ditta');
                    $PRAM_DB = $this->getPRAMDB();
                }
            }
        } else {
            $ditta = App::$utente->getKey('ditta');
            $PRAM_DB = $this->getPRAMDB();
        }

        if ($senzaSuap) {
            $where = "AND ITEPUB = 0";
        }

        $sql = "SELECT
                ITEPAS.*,
                PRACLT.CLTDES AS CLTDES
            FROM ITEPAS
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
            WHERE ITECOD='" . $Proges_rec['GESPRO'] . "' $where ORDER BY ITESEQ";
        $Itepas_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        /*
         * Ultima sequenza utilizzata nel caso di aggiunta passi
         */
        $new_seq = 0;
        $propas_tab = $this->GetPropas($procedimento, "codice", true);
        if ($propas_tab) {
            $last_rec = end($propas_tab);
            $new_seq = $last_rec['PROSEQ'];
        }

        /*
         * Parametri aggiuntivi di controllo inserimento
         */
        $filent_valoResp_rec = $this->GetFilent(19);
        $filent_passoBO = $this->GetFilent(27);
        $profilo = proSoggetto::getProfileFromIdUtente();
        $extraParam = array();
        $extraParam['VALORESP'] = $filent_valoResp_rec['FILVAL'];
        $extraParam['PASSOBO'] = $filent_passoBO['FILDE1'];
        $extraParam['PROFILO'] = $profilo;

        /*
         * Inserimento Passi procedimento 
         */
        foreach ($Itepas_tab as $key => $Itepas_rec) {
            $registraPasso = false;
            if ($filent_passoBO['FILDE1'] == 1) {
                if ($Itepas_rec['ITECARICAAUTO'] == 1) {
                    $registraPasso = true;
                }
            } else {
                $registraPasso = true;
            }
            if ($registraPasso === true) {
                $seq = $new_seq += 10;
                $rowidPropas = $this->ribaltaPasso($Proges_rec, $allegati, $Itepas_rec, $Anapra_rec, $new_seq, $extraParam);
                $Propas_rec = $this->GetPropas($rowidPropas, "rowid");
                if ($allegati) {
                    if (!$this->ribaltaAllegatiPasso($allegati, $Propas_rec['PROPAK'], $seq, $procedimento)) {
                        return false;
                    }
                }
                if (!$this->ribaltaDatiAggEsterna($Itepas_rec['ITEKEY'], $Propas_rec['PROPAK'])) {
                    return false;
                }
            }
        }


//
// Controllo se ci sono PROPAS con PROKPRE valorizzato ma senza padre
//
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '" . $Proges_rec['GESNUM'] . "' AND PROKPRE <> ''";
        $propas_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        if ($propas_tab) {
            foreach ($propas_tab as $propas_rec) {
                $propas_padre = $this->GetPropas($propas_rec['PROKPRE'], 'propak', false);
                if (!$propas_padre) {
                    try {
                        $nrow = ItaDb::DBDelete($this->getPRAMDB(), 'PROPAS', 'ROWID', $propas_rec['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        Out::msgStop("Errore", $e->getMessage());
                        return false;
                    }
                }
            }
            if (!$this->ordinaPassi($Proges_rec['GESNUM'])) {
                Out::msgStop("Errore Ordinamento Passo", $this->getErrMessage());
            }
        }

        /*
         * Inseriti tutti i passi, ribalto la tabella di salto passo multiplo
         */
        $propas_tab = $this->GetPropas($procedimento, "codice", true);
        foreach ($propas_tab as $propas_rec) {
            if (!$this->ribaltaCondizioniSaltaPasso($propas_rec)) {
                return false;
            }
        }

        /*
         * Ribaltamento dati aggiuntivi di procedimento (senza passo)
         */

        $codiceModello = $Proges_rec['GESPRO'];
        $sql = "SELECT * FROM ITEDAG WHERE ITECOD = '{$codiceModello}' AND ITEKEY = '{$codiceModello}'";
        $itedag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql);
        foreach ($itedag_tab as $itedag_rec) {
            if (!$this->ribaltaDatoAggEsterna(array(
                        'PRONUM' => $procedimento,
                        'PROPRO' => $codiceModello,
                        'PROPAK' => $procedimento
                            ), $itedag_rec)) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * Ribalta passi e dati collegati da file XMLINFO.xml da PEC/richiesta FRONT-OFFICE
     * @param string $fileXML
     * @param string $procedimento
     * @param array $allegati
     * @return boolean
     */
    public function ribaltaPassiXML($fileXML, $procedimento, $allegati, $accorpata = false, $allegatiAccorpate = array()) {
        /*
         * Estrazione dati da XML
         *
         */
        $xmlObj = new QXML;
        $ret = $xmlObj->setXmlFromFile($fileXML);
        if ($ret == false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore apertura XML: $fileXML");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        $Proric_rec = $arrayXml['PRORIC'];
        $Ricite_tab_tmp = $arrayXml['RICITE']['RECORD'];
        $Ricdoc_tab = $arrayXml["RICDOC"]['RECORD'];
        $Ricdag_tab = $arrayXml['RICDAG']['RECORD'];

        $Ricite_tab = array();
        if ($Ricite_tab_tmp) {
            if (isset($Ricite_tab_tmp[0])) {
                $Ricite_tab = $Ricite_tab_tmp;
            } else {
                $Ricite_tab[0] = $Ricite_tab_tmp;
            }
        }

        /*
         * Controllo e popolamento Proges_rec chiave richiesta esterna
         *
         */
        if ($accorpata == false) {
            $Proges_rec = $this->GetProges($procedimento);
            if ($Proges_rec) {
                if (!$Proges_rec['GESEXTKEY']) {
                    $Proges_rec['GESEXTKEY'] = $Proric_rec['RICKEY'][itaXML::textNode];
                    try {
                        $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'PROGES', 'ROWID', $Proges_rec);
                        if ($nupd == -1) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Ribalta passi: Aggiornamento chiave richiesta esterna fascicolo n. {$procedimento} fallito");
                            return false;
                        }
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Ribalta passi: Aggiornamento chiave richiesta esterna fascicolo n. {$procedimento} fallito. " . $exc->getMessage());
                        return false;
                    }
                }
            }
        }


        $XML_RichiesteAccorpate_tab = array();
        $Accorpate_tab = $arrayXml['RICHIESTE_ACCORPATE']['XMLINFO'];
        if ($Accorpate_tab) {
            if (isset($Accorpate_tab[0])) {
                $XML_RichiesteAccorpate_tab = $Accorpate_tab;
            } else {
                $XML_RichiesteAccorpate_tab[0] = $Accorpate_tab;
            }
        }
//
        $Anapra_rec = $this->GetAnapra($Proric_rec['RICPRO'][itaXML::textNode]);
        if (!$Anapra_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ribalta passi: Impossibile trovare il codice procedimento nell'anagrafica.");
            return false;
        }

        /*
         * Se sto ribaltando una richiesta accorpata, creo il passo padre per contenere i passi figli
         */
        $progressivo = 0;
        $keyPassoPadre = "";
        if ($accorpata) {
            $keyPassoPadre = $this->CreaPassoPadreAccorpata($procedimento, $Anapra_rec, $Proric_rec);
            if (!$keyPassoPadre) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento passo padre per passi richiesta accorpata fallito");
                return false;
            }
            $propasSeqMax_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(PROSEQ) AS MAXSEQ FROM PROPAS WHERE PRONUM = '$procedimento'", false);
            $maxSeq = $propasSeqMax_rec['MAXSEQ'];
//
            $progressivoMax_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(PROGRESSIVO) AS MAXPROG FROM PROGESSUB WHERE PRONUM = '$procedimento'", false);
            $progressivo = $progressivoMax_rec['MAXPROG'] + 1;
        }


        if ($Ricdoc_tab) {
//            if (!$this->ribaltaAllegatiPassoRicdoc($Ricdoc_tab, $allegati, $Proric_rec['RICNUM'][itaXML::textNode], $procedimento, '', $procedimento, "GENERALE", "")) {
            if (!$this->ribaltaAllegatiPassoRicdoc($Ricdoc_tab, $allegati, $Proric_rec['RICKEY'][itaXML::textNode], $procedimento, '', $procedimento, "GENERALE", "")) {
                return false;
            }
        }



        $filent_valoResp_rec = $this->GetFilent(19);
        if ($Ricite_tab) {
            foreach ($Ricite_tab as $key => $Ricite_rec) {
                if ($accorpata) {
                    $maxSeq += 10;
                    $newSeqPasso = $maxSeq;
                } else {
                    $newSeqPasso = $Ricite_rec['ITESEQ'][itaXML::textNode];
                }
                //
                // Decodifico Tipo Passo
                //
                $Praclt_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT CLTDES FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['ITECLT'][itaXML::textNode] . "'", false);

                //
                // Carico Propas
                //
                $Propas_rec = array();
                $Propas_rec['PRONUM'] = $procedimento; //$Proric_rec['RICNUM'][itaXML::textNode];
                $Propas_rec['PROPRO'] = $Proric_rec['RICPRO'][itaXML::textNode];
                $Propas_rec['PRORES'] = $Proric_rec['RICRES'][itaXML::textNode];
                //$Propas_rec['PROSEQ'] = $seq = $Ricite_rec['ITESEQ'][itaXML::textNode];
                $Propas_rec['PROSEQ'] = $seq = $newSeqPasso;
                $Propas_rec['PRODOW'] = $Ricite_rec['ITEDOW'][itaXML::textNode];
                $Propas_rec['PROPUB'] = $Ricite_rec['ITEPUB'][itaXML::textNode];
                $Propas_rec['PROUPL'] = $Ricite_rec['ITEUPL'][itaXML::textNode];
                $Propas_rec['PROQST'] = $Ricite_rec['ITEQST'][itaXML::textNode];
                $Propas_rec['PRODAT'] = $Ricite_rec['ITEDAT'][itaXML::textNode];
                $Propas_rec['PROMLT'] = $Ricite_rec['ITEMLT'][itaXML::textNode];
                $Propas_rec['PRODRR'] = $Ricite_rec['ITEDRR'][itaXML::textNode];
                $Propas_rec['PROIDR'] = $Ricite_rec['ITEIDR'][itaXML::textNode];
                $Propas_rec['PROPDR'] = $Ricite_rec['ITEPDR'][itaXML::textNode];
                $Propas_rec['PROZIP'] = $Ricite_rec['ITEZIP'][itaXML::textNode];
                $Propas_rec['PRODIS'] = $Ricite_rec['ITEDIS'][itaXML::textNode];
                $Propas_rec['PROOBL'] = $Ricite_rec['ITEOBL'][itaXML::textNode];
                $Propas_rec['PROMETA'] = base64_decode($Ricite_rec['ITEMETA'][itaXML::textNode]);
                $Propas_rec['PROSTATO'] = $Ricite_rec['ITEDEFSTATO'][itaXML::textNode];
                $Propas_rec['PRORDM'] = $Ricite_rec['ITERDM'][itaXML::textNode];
                if ($keyPassoPadre) {
                    $Propas_rec['PROKPRE'] = $keyPassoPadre;
                }

                //
                //Se il responsabile del passo è diverso da quello del procedimento, prendo quello del procedimento
                //
                $Propas_rec['PRORPA'] = $Ricite_rec['ITERES'][itaXML::textNode];
                if ($Anapra_rec['PRARES'] != $Propas_rec['PRORPA']) {
                    $Propas_rec['PRORPA'] = $Anapra_rec['PRARES'];
                }

                if ($filent_valoResp_rec["FILVAL"] == 1) {
                    $Propas_rec['PRORPA'] = "";
                }

                $Propas_rec['PROUOP'] = $Ricite_rec['ITEOPE'][itaXML::textNode];
                $Propas_rec['PROSET'] = $Ricite_rec['ITESET'][itaXML::textNode];
                $Propas_rec['PROSER'] = $Ricite_rec['ITESER'][itaXML::textNode];
                $Propas_rec['PROGIO'] = $Ricite_rec['ITEGIO'][itaXML::textNode];
                $Propas_rec['PROCLT'] = $Ricite_rec['ITECLT'][itaXML::textNode];
                $Propas_rec['PROCOM'] = $Ricite_rec['ITECOM'][itaXML::textNode];
                $Propas_rec['PRODTP'] = $Praclt_rec['CLTDES'];
                $Propas_rec['PRODPA'] = $Ricite_rec['ITEDES'][itaXML::textNode];
                $Propas_rec['PROIRE'] = $Ricite_rec['ITEIRE'][itaXML::textNode];
                $Propas_rec['PRORICUNI'] = $Ricite_rec['ITERICUNI'][itaXML::textNode];
                $Propas_rec['PROPRI'] = $Ricite_rec['ITEPRI'][itaXML::textNode];
                $Propas_rec['PROPAK'] = $this->PropakGenerator($Propas_rec['PRONUM']);
                $Propas_rec['PROVPA'] = '';
                $Propas_rec['PROCTR'] = '';
                if ($Ricite_rec['ITEVPA'][itaXML::textNode] != '') {
                    $Propas_rec['PROVPA'] = $Propas_rec['PRONUM'] . substr($Ricite_rec['ITEVPA'][itaXML::textNode], 6);
                }
                if ($Ricite_rec['ITEVPN'][itaXML::textNode] != '') {
                    $Propas_rec['PROVPN'] = $Propas_rec['PRONUM'] . substr($Ricite_rec['ITEVPN'][itaXML::textNode], 6);
                }
                if ($Ricite_rec['ITECTR'][itaXML::textNode] != '') {
                    $Propas_rec['PROCTR'] = $Ricite_rec['ITECTR'][itaXML::textNode];
                }
                if ($Ricite_rec['ITECOM'][itaXML::textNode] != 0) {
                    if ($Ricite_rec['ITEINT'][itaXML::textNode] == 1)
                        $Propas_rec['PROINT'] = $Ricite_rec['ITEINT'][itaXML::textNode];
                    if ($Ricite_rec['ITECDE'][itaXML::textNode] != "")
                        $Propas_rec['PROCDE'] = $Ricite_rec['ITECDE'][itaXML::textNode];
                    if ($Ricite_rec['ITECOMDEST'][itaXML::textNode] != 0)
                        $Propas_rec['PROCOMDEST'] = $Ricite_rec['ITECOMDEST'][itaXML::textNode];
                    $Propas_rec['PROTBA'] = $Ricite_rec['ITETBA'][itaXML::textNode];
                }

                $alle = array();
                $tmp_arrayRicdoc = array();
                if (!$Ricdoc_tab) {
                    $seqPasso = str_repeat("0", 3 - strlen($Propas_rec['PROSEQ'])) . $Propas_rec['PROSEQ'];
                    foreach ($allegati as $key => $allegato) {
                        if (strpos($allegato['FILENAME'], "C" . $seqPasso) !== false) {
                            $alle[] = $allegato['FILENAME'];
                        }
                    }
                } else {
                    //Modifca per un ricdoc_tab con un solo elemento perchè andava in errore
                    $tmp_arrayRicdoc = $Ricdoc_tab;
                    if (!$tmp_arrayRicdoc[0]) {
                        $Ricdoc_tab = array();
                        $Ricdoc_tab[0] = $tmp_arrayRicdoc;
                    }

                    foreach ($Ricdoc_tab as $key => $Ricdoc_rec) {
                        if ($Ricdoc_rec['ITEKEY'][itaXML::textNode] === $Ricite_rec['ITEKEY'][itaXML::textNode]) {
                            $alle[] = $Ricdoc_rec['DOCNAME'][itaXML::textNode];
                        }
                    }
                }
                if ($alle) {
                    $Propas_rec['PROALL'] = serialize($alle);
                }
                if ($Ricite_rec['ITEPUB'][itaXML::textNode] == 1) {
                    //FIXME: $this->currItekey non esiste
                    $Propas_rec['PROITK'] = $this->currItekey;
                    $Propas_rec['PROINI'] = $Proric_rec['RICDRE'][itaXML::textNode];
                    $Propas_rec['PROFIN'] = $Proric_rec['RICDRE'][itaXML::textNode];
                    $Propas_rec['PROUTEADD'] = "@ADMIN@";
                    $Propas_rec['PROUTEEDIT'] = "@ADMIN@";
                    $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date('H:i:s');
                    $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date('H:i:s');
                    $Propas_rec['PROVISIBILITA'] = "Protetto";
                } else {
                    $Propas_rec['PROUTEADD'] = App::$utente->getKey('nomeUtente');
                    $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
                    $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date('H:i:s');
                    $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date('H:i:s');
                    $Propas_rec['PROVISIBILITA'] = "Aperto";
                }
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROPAS', 'ROWID', $Propas_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Inserimento passo fallito");
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($e->getMessage());
                    return false;
                }

                //
                //TODO: Versione senza tabella allegati alla pratica da integrare con una nuova versione
                //
                // Carico gli Allegati
                //
                if (!$Ricdoc_tab) {
                    if ($allegati) {
                        if (!$this->ribaltaAllegatiPasso($allegati, $Propas_rec['PROPAK'], $seq, $procedimento)) {
                            return false;
                        }
                    }
                } else {
                    if (!$this->ribaltaAllegatiPassoRicdoc($Ricdoc_tab, $allegati, $Ricite_rec['ITEKEY'][itaXML::textNode], $Propas_rec['PROPAK'], $seq, $procedimento, "", $Propas_rec['PRODPA'])) {
                        return false;
                    }
                }

                $keyfo = $Ricite_rec['ITEKEY'][itaXML::textNode];
                if (!$this->ribaltaDatiAggiuntiviRicdag($Propas_rec['PROPAK'], $Ricdag_tab, $keyfo)) {
                    return false;
                }
            }
        }

        /*
         * I dati aggiuntivi di pratica li ribalto solo la prima volta.
         * Non li ribalto per le accorpate
         */
        if (!$accorpata) {
            $keyfo = $Proric_rec['RICPRO'][itaXML::textNode];
            if (!$this->ribaltaDatiAggiuntiviRicdag($procedimento, $Ricdag_tab, $keyfo, $procedimento)) {
                return false;
            }
            if (!$this->ribaltaAllegatiPassoRicdoc($Ricdoc_tab, $allegati, $keyfo, $procedimento, '', $procedimento, 'GENERALE', '', '', 'PROGES')) {
                return false;
            }
        }

        /*
         * Inserimento record su Tabella PROGESSUB solo se accorpata, perchè il procedimento padre l'abbiamo già inserito
         */
        if ($accorpata) {
            if (!$this->RegistraSubFascicoli($procedimento, $Proric_rec, $progressivo)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento sub procedimento con n. richiesta on-line $procedimento.");
                return false;
            }
        }

        /*
         * Ribalto richieste accorpate se ci sono
         */
        $inc = 0;
        $pathAllegatiRichiste = dirname($fileXML);
        foreach ($XML_RichiesteAccorpate_tab as $key => $XML_RichiesteAccorpate_rec) {
            $inc++;
            $xmlAccorpata = $pathAllegatiRichiste . "/" . $XML_RichiesteAccorpate_rec["@textNode"];
            $posPunto = strpos($XML_RichiesteAccorpate_rec["@textNode"], ".xml");
            $strRichiesta = substr($XML_RichiesteAccorpate_rec["@textNode"], 0, $posPunto);
            $numRichiesta = substr($strRichiesta, 8);
            //$numRichiesta = substr($XML_RichiesteAccorpate_rec["@textNode"], 8, 10);
            //$allegatiAccorpata = $this->GetAllegatiPratica($numRichiesta);
            if (!$this->ribaltaPassiXML($xmlAccorpata, $procedimento, $allegatiAccorpate[$numRichiesta], true)) {
                $this->setErrCode($this->getErrCode());
                $this->setErrMessage($this->getErrMessage() . " - richiesta on-line accorpata N. $numRichiesta.");
                return false;
            }
        }
        return true;
    }

    function RegistraSubFascicoli($pronum, $proric_rec, $progressivo, $proges_rec = array()) {
        $progessub_rec = array();
        $progessub_rec['PRONUM'] = $pronum;
        $progessub_rec['PROGRESSIVO'] = $progressivo;
        if ($proges_rec) {
            /*
             * Quando inserisco da anagrafica procedimenti
             */
            $progessub_rec['PROPRO'] = $proges_rec['GESPRO'];
            $progessub_rec['RICHIESTA'] = $proges_rec['GESPRA'];
            $progessub_rec['EVENTO'] = $proges_rec['GESEVE'];
            $progessub_rec['SPORTELLO'] = $proges_rec['GESTSP'];
            $progessub_rec['SETTORE'] = $proges_rec['GESSTT'];
            $progessub_rec['ATTIVITA'] = $proges_rec['GESATT'];
            $progessub_rec['TIPSEG'] = $proges_rec['GESSEG'];
        } else {
            /*
             * Quando inserisco da controlla FO o pec richiesta on-line
             */
            $progessub_rec['PROPRO'] = $proric_rec['RICPRO'][itaXML::textNode];

            /*
             * RICKEY Vuoto solo per richiesta ITALSOFT
             */
            if ($proric_rec['RICKEY'][itaXML::textNode] == "") {
                $progessub_rec['RICHIESTA'] = $proric_rec['RICNUM'][itaXML::textNode];
            }
            $progessub_rec['EVENTO'] = $proric_rec['RICEVE'][itaXML::textNode];
            $progessub_rec['SPORTELLO'] = $proric_rec['RICTSP'][itaXML::textNode];
            $progessub_rec['SETTORE'] = $proric_rec['RICSTT'][itaXML::textNode];
            $progessub_rec['ATTIVITA'] = $proric_rec['RICATT'][itaXML::textNode];
            $progessub_rec['TIPSEG'] = $proric_rec['RICSEG'][itaXML::textNode];
            $progessub_rec['RICHKEY'] = $proric_rec['RICKEY'][itaXML::textNode];
        }
//
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROGESSUB', 'ROWID', $progessub_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    function CreaPassoPadreAccorpata($procedimento, $Anapra_rec, $Proric_rec) {
        $propasSeqMax_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(PROSEQ) AS MAXSEQ FROM PROPAS WHERE PRONUM = '$procedimento'", false);
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $procedimento;
        $Propas_rec['PROPRO'] = $Proric_rec['RICPRO'][itaXML::textNode];
        $Propas_rec['PRORPA'] = $Anapra_rec['PRARES'];
        $Propas_rec['PRODPA'] = "Richiesta on-line Accorpata " . $Proric_rec['RICNUM'][itaXML::textNode];
        $Propas_rec['PROSEQ'] = $propasSeqMax_rec['MAXSEQ'] + 10;
        $Propas_rec['PROINI'] = date("d/m/Y");
        $Propas_rec['PROFIN'] = date("d/m/Y");
        $Propas_rec['PROPUB'] = 1;
        $Propas_rec['PROPAK'] = $this->PropakGenerator($procedimento);
        $Propas_rec['PROUTEADD'] = "@ADMIN@";
        $Propas_rec['PROUTEEDIT'] = "@ADMIN@";
        $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date('H:i:s');
        $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date('H:i:s');
        $Propas_rec['PROVISIBILITA'] = "Protetto";
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return $Propas_rec['PROPAK'];
    }

    function CaricaPassoAnnullamento($Proric_rec, $fileEml = "", $Pramail_rec = "", $daMail = "") {
        $dataInvio = date("Ymd");
        $oraInvio = date("H:i:s");
        if ($Pramail_rec) {
            $emlLib = new emlLib();
            $mail_archivio_rec = $emlLib->getMailArchivio($Pramail_rec['ROWIDARCHIVIO'], "rowid");
            $arrInfoFO = unserialize($Pramail_rec['INFOFRONTOFFICE']);
            $motivo = $arrInfoFO['MOTIVO'];
            $dataInvio = $mail_archivio_rec['SENDRECDATE'];
            $oraInvio = $mail_archivio_rec['SENDRECTIME'];
        }

        if ($Proric_rec['RICRPA']) {
            /*
             * Se è una pratica in variante, va collegata con chiave RICNUM e non RICRPA
             */
            if ($Proric_rec['RICPC']) {
                $chiaveRichiesta = $Proric_rec['RICNUM'];
                $descrizionePasso = "Passo annullamento della richiesta on-line n. $chiaveRichiesta";
                $descrizioneAllegato = "mail_annullamento_richiesta_$chiaveRichiesta.eml";
                $noteAllegato = "File Originale: Eml annullamento richiesta n. $chiaveRichiesta";
            } else {
                $chiaveRichiesta = $Proric_rec['RICRPA'];
                $descrizionePasso = "Passo annullamento integrazione richiesta on-line n. {$Proric_rec['RICNUM']}";
                $descrizioneAllegato = "mail_annullamento_integrazione_{$Proric_rec['RICNUM']}.eml";
                $noteAllegato = "File Originale: Eml annullamento integrazione n. {$Proric_rec['RICNUM']}";
            }
        } else {
            $chiaveRichiesta = $Proric_rec['RICNUM'];
            $descrizionePasso = "Passo annullamento della richiesta on-line n. $chiaveRichiesta";
            $descrizioneAllegato = "mail_annullamento_richiesta_$chiaveRichiesta.eml";
            $noteAllegato = "File Originale: Eml annullamento richiesta n. $chiaveRichiesta";
        }
        $richiesta = substr($chiaveRichiesta, 5) . "/" . substr($chiaveRichiesta, 0, 4);
        $Proges_rec = $this->GetProges($chiaveRichiesta, "richiesta");
        if (!$Proges_rec) {
            Out::msgStop("Attenzione!!!!!", "Pratica con richiesta on-line n. " . $chiaveRichiesta . " non ancora acquisita.<br>Prima acquisire la pratica, poi caricare la mail d'annullamento");
            return false;
        }
        $Utenti_rec = $this->GetUtente(App::$utente->getKey('idUtente'), "codiceUtente");

//        $fileXML = "";
//        foreach ($allegati as $allegato) {
//            if (strpos($allegato['FILENAME'], 'XMLINFO') !== false) {
//                $fileXML = $allegato['DATAFILE'];
//                break;
//            }
//        }
//        if ($fileXML) {
//            $xmlObj = new QXML;
//            $ret = $xmlObj->setXmlFromFile($fileXML);
//            if ($ret == false) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Errore apertura XML: $fileXML");
//                return false;
//            }
//            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//        }
//
        //Inserisco passo annullamento
//
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROPAK'] = $this->PropakGenerator($Proges_rec['GESNUM']);
        $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PROALL'] = serialize($fileEml);

//        $Propas_rec['PRORPA'] = $Utenti_rec['UTEANA__3'];
//        $filent_valoResp_rec = $this->GetFilent(19);
//        if ($filent_valoResp_rec["FILVAL"] == 1) {
//            $Propas_rec['PRORPA'] = "";
//        }

        $Ananom_rec = $this->GetAnanom(proSoggetto::getCodiceUltimoResponsabile($Proges_rec['GESNUM']));
        if ($Ananom_rec) {
            $Propas_rec['PRORPA'] = $Ananom_rec['NOMRES'];
        } else {
            $Propas_rec['PRORPA'] = $Utenti_rec['UTEANA__3'];
        }

        $ananom_rec = $this->getAnanom($Utenti_rec['UTEANA__3']);
        $anauniRes_rec = $this->GetAnauniRes($ananom_rec['NOMRES']);
        if ($anauniRes_rec['UNISET'] == "")
            $anauniRes_rec['UNISET'] = "";
        $Propas_rec['PROSET'] = $anauniRes_rec['UNISET'];
        if ($anauniRes_rec['UNISER'] == "")
            $anauniRes_rec['UNISET'] = "";
        $Propas_rec['PROSER'] = $anauniRes_rec['UNISER'];
        if ($anauniRes_rec['UNIOPE'] == "")
            $anauniRes_rec['UNISET'] = $anauniRes_rec['UNISER'] = "";
        $Propas_rec['PROUOP'] = $anauniRes_rec['UNIOPE'];

        //$Propas_rec['PROINI'] = $Proric_rec['RICDRE'];
        $Propas_rec['PROINI'] = date("Ymd");

        $Propas_rec['PRODPA'] = $descrizionePasso;
        $Propas_rec['PRORIN'] = $Proric_rec['RICNUM'];
        $Propas_rec['PROVISIBILITA'] = "Protetto";
        $Propas_rec['PROUTEADD'] = $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $Propas_rec['PRODATEADD'] = $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }
        if (!$this->ordinaPassi($Proges_rec['GESNUM'])) {
            Out::msgStop("Errore", $this->getErrMessage());
        }

//
//Inserisco l'eml di annullamento
//
        if ($fileEml) {
            $destinazione = $this->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK']);
            if (!@copy($fileEml, $destinazione . '/' . pathinfo($fileEml, PATHINFO_BASENAME))) {
                Out::msgStop("File eml", "Errore copia file eml " . pathinfo($fileEml, PATHINFO_BASENAME));
                return false;
            }
            $Pasdoc_rec = array();
            $Pasdoc_rec['PASKEY'] = $Propas_rec['PROPAK'];
            $Pasdoc_rec['PASFIL'] = pathinfo($fileEml, PATHINFO_BASENAME);
            $Pasdoc_rec['PASLNK'] = "allegato://" . pathinfo($fileEml, PATHINFO_BASENAME);
            $Pasdoc_rec['PASNOT'] = $noteAllegato;
            $Pasdoc_rec['PASNAME'] = $descrizioneAllegato;
            $Pasdoc_rec['PASCLA'] = "ANNULLAMENTO N. $richiesta";
            $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $Pasdoc_rec['PASDATADOC'] = date("Ymd");
            $Pasdoc_rec['PASORADOC'] = date("H:i:s");
            $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $Pasdoc_rec['PASFIL']);

            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $exc) {
                Out::msgStop("Errore", $exc->getMessage());
                return false;
            }
        }
//
//Inserisco comunicazione in arrivo SU PRACOM
//
        $descMittente = $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM'];
        $codFisMittente = $Proric_rec['RICFIS'];

        $PracomArr_rec = array();
        $PracomArr_rec['COMNUM'] = $Propas_rec['PRONUM'];
        $PracomArr_rec['COMPAK'] = $Propas_rec['PROPAK'];
        $PracomArr_rec['COMTIP'] = "A";
        $PracomArr_rec['COMMLD'] = $Proric_rec['RICEMA'];
        $PracomArr_rec['COMCDE'] = "";
        $PracomArr_rec['COMNOM'] = $descMittente;
        $PracomArr_rec['COMFIS'] = $codFisMittente;
        $PracomArr_rec['COMNOT'] = "Passo annullamento della richiesta on-line n. $richiesta - $motivo";
        $PracomArr_rec['COMDAT'] = $dataInvio;
        $PracomArr_rec['COMORA'] = $oraInvio;
//        $PracomArr_rec['COMDAT'] = $Proric_rec['RICDAT'];
//        $PracomArr_rec['COMORA'] = $Proric_rec['RICTIM'];
        $PracomArr_rec['COMIDMAIL'] = $Pramail_rec['IDMAIL'];
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PRACOM', 'ROWID', $PracomArr_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }

//
//Inserisco comunicazione in arrivo su PRAMITDEST
//
        $praMitDest_rec = array();
        $praMitDest_rec['TIPOCOM'] = 'M';
        $praMitDest_rec['KEYPASSO'] = $Propas_rec['PROPAK'];
        $praMitDest_rec['DATAINVIO'] = $dataInvio;
        $praMitDest_rec['ORAINVIO'] = $oraInvio;
//        $praMitDest_rec['DATAINVIO'] = $Proric_rec['RICDAT'];
//        $praMitDest_rec['ORAINVIO'] = $Proric_rec['RICTIM'];
        $praMitDest_rec['CODICE'] = "";
        $praMitDest_rec['NOME'] = $descMittente;
        $praMitDest_rec['FISCALE'] = $codFisMittente;
        $praMitDest_rec['MAIL'] = $Proric_rec['RICEMA'];
        $praMitDest_rec['TIPOINVIO'] = "PEC";
        $pracom_recA = $this->GetPracomA($Propas_rec['PROPAK']);
        $praMitDest_rec['ROWIDPRACOM'] = $pracom_recA['ROWID'];
        $praMitDest_rec['IDMAIL'] = $Pramail_rec['IDMAIL'];
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PRAMITDEST', 'ROWID', $praMitDest_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }

//
//Aggiorno PRAMAIL e MAIL ARCHIVIO
//
        if ($Pramail_rec) {
            $this->setClasseCaricatoPasso($daMail, $Pramail_rec['IDMAIL'], $Proges_rec['GESNUM'], $Propas_rec['PROPAK']);
        }

        $propas_rec = $this->GetPropas($Propas_rec['PROPAK'], "propak");
        return $propas_rec;
    }

    function CaricaPassoIntegrazione($Proric_rec, $allegati, $fileEml = "", $Pramail_rec = "", $daMail = "", $parere = false, $row_idPrafolist = "") {
        $descMittente = $codFisMittente = $mail = $referente = $descParere = $comnot = "";
        $richiesta = substr($Proric_rec['RICNUM'], 5) . "-" . substr($Proric_rec['RICNUM'], 0, 4);

        if ($row_idPrafolist > 0) {
            // Imposto $richiesta con il campo PRAFOLIST.FOIDPRATICA

            $prafolist_rec = $this->GetPrafolist($row_idPrafolist);

            if ($prafolist_rec) {
                $richiesta = $prafolist_rec['FOIDPRATICA'];
            }
        }

        /*
         * Leggo Proges_rec
         */
        if ($parere === true) {
            $Proges_rec = $this->GetProges(substr($Proric_rec['PROPAK'], 0, 10));
            $pascla = "PARERE/RICHIESTA INTEGRAZIONE N. $richiesta";
        } else {
            $Proges_rec = $this->GetProges($Proric_rec['RICRPA'], "richiesta");
            $prorin = $Proric_rec['RICNUM'];
            if (!$Proges_rec) {
                $Proges_rec = $this->GetProges($Proric_rec['RICRPA'], "geskey");
                $prokey = $Proric_rec['RICNUM'];
                $prorin = "";
            }
            $pascla = "INTEGRAZIONE N. $richiesta";
        }
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Record PROGES non Trovato");
            return false;
        }

        /*
         * Controllo se l'integrazione è già caricata
         */
        $Propas_rec_ctr = $this->GetPropas($Proric_rec['RICNUM'], "prorin");
        if ($Propas_rec_ctr) {
            $Serie_rec = $this->ElaboraProgesSerie($Proges_rec['GESNUM'], $Proges_rec['SERIECODICE'], $Proges_rec['SERIEANNO'], $Proges_rec['SERIEPROGRESSIVO']);
            $this->setErrCode(-1);
            $this->setErrMessage("Integrazione già caricata nel fascicolo n. $Serie_rec");
            return false;
        }

        /*
         * Record utente loggato
         */
        $Utenti_rec = $this->GetUtente(App::$utente->getKey('idUtente'), "codiceUtente");

        /*
         * Inizializzo Propas_rec e lo inserisco
         */
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROPAK'] = $this->PropakGenerator($Proges_rec['GESNUM']);
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        $Propas_rec['PROALL'] = serialize($allegati);
        $Ananom_rec = $this->GetAnanom(proSoggetto::getCodiceUltimoResponsabile($Proges_rec['GESNUM']));
        if ($Ananom_rec) {
            $Propas_rec['PRORPA'] = $Ananom_rec['NOMRES'];
        } else {
            $Propas_rec['PRORPA'] = $Utenti_rec['UTEANA__3'];
        }

        $ananom_rec = $this->getAnanom($Utenti_rec['UTEANA__3']);
        $anauniRes_rec = $this->GetAnauniRes($ananom_rec['NOMRES']);
        if ($anauniRes_rec['UNISET'] == "")
            $anauniRes_rec['UNISET'] = "";
        $Propas_rec['PROSET'] = $anauniRes_rec['UNISET'];
        if ($anauniRes_rec['UNISER'] == "")
            $anauniRes_rec['UNISET'] = "";
        $Propas_rec['PROSER'] = $anauniRes_rec['UNISER'];
        if ($anauniRes_rec['UNIOPE'] == "")
            $anauniRes_rec['UNISET'] = $anauniRes_rec['UNISER'] = "";
        $Propas_rec['PROUOP'] = $anauniRes_rec['UNIOPE'];

        $Propas_rec['PROINI'] = $Proric_rec['RICDAT'];
        if ($parere === true) {
            $Propas_rec['PROKPRE'] = $Proric_rec['PROPAK'];
        }
        $Propas_rec['PRODPA'] = "";
        $Propas_rec['PROVISIBILITA'] = "Protetto";
        $Propas_rec['PROUTEADD'] = $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $Propas_rec['PRODATEADD'] = $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $Propas_rec['PRORIN'] = $prorin;
        $Propas_rec['PROKEY'] = $prokey;

        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        $rowidPropas = $this->getPRAMDB()->getLastId();
        if (!$this->ordinaPassi($Proges_rec['GESNUM'])) {
            
        }


        /*
         * Trovo il file XMLINFO
         */
        $fileXML = "";
        foreach ($allegati as $allegato) {
            if (strpos($allegato['FILENAME'], 'XMLINFO') !== false) {
                $fileXML = $allegato['DATAFILE'];
                break;
            }
        }

        /*
         * Leggo il file XMLINFO
         */
        if ($fileXML) {
            $xmlObj = new QXML;
            $ret = $xmlObj->setXmlFromFile($fileXML);
            if ($ret == false) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore apertura XML: $fileXML");
                return false;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            $Ricite_tab = $arrayXml['RICITE']['RECORD'];
            if (!isset($Ricite_tab[0])) {
                $Ricite_tab = array($arrayXml['RICITE']['RECORD']);
            }
            $Ricdoc_tab = $arrayXml["RICDOC"]['RECORD'];
            $Ricdag_tab = $arrayXml['RICDAG']['RECORD'];
            foreach ($Ricite_tab as $Ricite_rec) {
                $keyfo = $Ricite_rec['ITEKEY'][itaXML::textNode];

                /*
                 * Se è un passo Download, non ribalto i dati aggiuntivi
                 */
                if ($Ricite_rec['ITEDOW'][itaXML::textNode] == 1) {
                    continue;
                }
                if (!$this->ribaltaDatiAggiuntiviRicdag($Propas_rec['PROPAK'], $Ricdag_tab, $keyfo, "", $Ricite_rec['ITEDES'][itaXML::textNode])) {
                    return false;
                }
            }
        }

        if ($parere === true) {

            /*
             * Leggo il dato aggiuntivo tipizzato del tipo di comunicazione per capire se è un parere o un'itegrazione
             */
            $prodpa = "Espressione Parere on-line n. $richiesta";
            $pasnotEML = "File Originale: Eml parere n. $richiesta";
            $pasnameEML = "mail_parere_richiesta_$richiesta.eml";
            $Prodag_rec_tipoRichiesta = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='" . $Proges_rec['GESNUM'] . "' AND DAGPAK = '" . $Propas_rec['PROPAK'] . "' AND DAGTIP = 'TipoRich_EnteTerzo'", false);
            if ($Prodag_rec_tipoRichiesta['DAGVAL'] == 'Richiedi Integrazione') {
                $prodpa = "Richiesta Integrazione on-line n. $richiesta";
                $pasnotEML = "File Originale: Eml richiesta integrazione n. $richiesta";
                $pasnameEML = "mail_richiesta_integrazione_$richiesta.eml";
            }


            $Prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='" . $Proges_rec['GESNUM'] . "' AND DAGPAK = '" . $Propas_rec['PROPAK'] . "'
                                  AND (DAGTIP = 'Denom_EnteTerzo' OR 
                                       DAGTIP = 'Fiscale_EnteTerzo' OR
                                       DAGTIP = 'Pec_EnteTerzo' OR
                                       DAGTIP = 'Referente_EnteTerzo' OR
                                       DAGTIP = 'descrizioneParere')", true);

            if ($Prodag_tab) {
                foreach ($Prodag_tab as $Prodag_rec) {
                    if ($Prodag_rec['DAGTIP'] == "Denom_EnteTerzo")
                        $descMittente = $Prodag_rec['DAGVAL'];
                    if ($Prodag_rec['DAGTIP'] == "Fiscale_EnteTerzo")
                        $codFisMittente = $Prodag_rec['DAGVAL'];
                    if ($Prodag_rec['DAGTIP'] == "Pec_EnteTerzo")
                        $mail = $Prodag_rec['DAGVAL'];
                    if ($Prodag_rec['DAGTIP'] == "Referente_EnteTerzo")
                        $referente = $Prodag_rec['DAGVAL'];
                    if ($Prodag_rec['DAGTIP'] == "descrizioneParere")
                        $descParere = $Prodag_rec['DAGVAL'];
                }
            }
            $comnot = $descParere . " - Referente: $referente";
        } else {
            //$Proges_rec = $this->GetProges($Proric_rec['RICRPA'], "richiesta");
            $prodpa = "Passo integrazione richiesta on-line n. $richiesta";
            $pasnotEML = "File Originale: Eml integrazione n. $richiesta";
            $pasnameEML = "mail_integrazione_$richiesta.eml";
            //$pascla = "INTEGRAZIONE N. $richiesta";
            //$errProges = "Fascicolo con richiesta on-line n. " . $Proric_rec['RICRPA'] . " non trovato";
            $descMittente = $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM'];
            $codFisMittente = $Proric_rec['RICFIS'];
            $mail = $Proric_rec['RICEMA'];
            $comnot = $prodpa;
        }

        /*
         * Aggiorno la descrizione del passo
         */
        $propas_rec = $this->GetPropas($rowidPropas, 'rowid');
        $propas_rec['PRODPA'] = $prodpa;
        $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'PROPAS', 'ROWID', $propas_rec);
        if ($nupd == -1) {
            return false;
        }

        /*
         * Inserisco l'eml di integrazione
         */
//        if ($fileEml) {
//            $destinazione = $this->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK']);
//            if (!copy($fileEml, $destinazione . '/' . pathinfo($fileEml, PATHINFO_BASENAME))) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Errore copia file eml " . pathinfo($fileEml, PATHINFO_BASENAME));
//                return false;
//            }
//            $Pasdoc_rec = array();
//            $Pasdoc_rec['PASKEY'] = $Propas_rec['PROPAK'];
//            $Pasdoc_rec['PASFIL'] = pathinfo($fileEml, PATHINFO_BASENAME);
//            $Pasdoc_rec['PASLNK'] = "allegato://" . pathinfo($fileEml, PATHINFO_BASENAME);
//            $Pasdoc_rec['PASNOT'] = $pasnotEML;
//            $Pasdoc_rec['PASNAME'] = $pasnameEML;
//            $Pasdoc_rec['PASCLA'] = $pascla;
//            $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
//            $Pasdoc_rec['PASDATADOC'] = date("Ymd");
//            $Pasdoc_rec['PASORADOC'] = date("H:i:s");
//            $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $Pasdoc_rec['PASFIL']);
//
//            try {
//                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
//                if ($nrow != 1) {
//                    return false;
//                }
//            } catch (Exception $exc) {
//                $this->setErrCode(-1);
//                $this->setErrMessage($exc->getMessage());
//                return false;
//            }
//        }


        /*
         * Inserisco comunicazione in arrivo su PRACOM
         */
        $PracomArr_rec = array();
        $PracomArr_rec['COMNUM'] = $Propas_rec['PRONUM'];
        $PracomArr_rec['COMPAK'] = $Propas_rec['PROPAK'];
        $PracomArr_rec['COMTIP'] = "A";
        $PracomArr_rec['COMMLD'] = $mail;
        $PracomArr_rec['COMCDE'] = "";
        $PracomArr_rec['COMNOM'] = $descMittente;
        $PracomArr_rec['COMFIS'] = $codFisMittente;
        $PracomArr_rec['COMNOT'] = $comnot;
//$PracomArr_rec['COMNOT'] = "Passo integrazione della richiesta on-line n. $richiesta";
        $PracomArr_rec['COMDAT'] = $Proric_rec['RICDAT'];
        $PracomArr_rec['COMORA'] = $Proric_rec['RICTIM'];
        $PracomArr_rec['COMIDMAIL'] = $Pramail_rec['IDMAIL'];
        if ($Proric_rec['RICNPR'] != 0) {
            $PracomArr_rec['COMPRT'] = $Proric_rec['RICNPR'];
            $PracomArr_rec['COMDPR'] = $Proric_rec['RICDPR'];
            $PracomArr_rec['COMMETA'] = $Proric_rec['RICMETA'];
        }

        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PRACOM', 'ROWID', $PracomArr_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'inserimento PRACOM");
                return false;
            }
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }


        foreach ($Ricite_tab as $Ricite_rec) {
            if ($Ricdoc_tab) {
                if (!$this->ribaltaAllegatiPassoRicdoc($Ricdoc_tab, $allegati, $Ricite_rec['ITEKEY'][itaXML::textNode], $Propas_rec['PROPAK'], $Propas_rec['PROSEQ'], $Propas_rec['PRONUM'], $pascla, $Ricite_rec['ITEDES'][itaXML::textNode], $Ricite_rec['RICSHA2SOST'][itaXML::textNode])) {
                    return false;
                }
            }
        }

        /*
         * Inserisco comunicazione in arrivo su PRAMITDEST
         */
        $praMitDest_rec = array();
        $praMitDest_rec['TIPOCOM'] = 'M';
        $praMitDest_rec['KEYPASSO'] = $Propas_rec['PROPAK'];
        $praMitDest_rec['DATAINVIO'] = $Proric_rec['RICDAT'];
        $praMitDest_rec['ORAINVIO'] = $Proric_rec['RICTIM'];
        $praMitDest_rec['CODICE'] = "";
        $praMitDest_rec['NOME'] = $descMittente;
        $praMitDest_rec['FISCALE'] = $codFisMittente;
//$praMitDest_rec['MAIL'] = $Proric_rec['RICEMA'];
        $praMitDest_rec['MAIL'] = $mail;
        $praMitDest_rec['TIPOINVIO'] = "PEC";
        $pracom_recA = $this->GetPracomA($Propas_rec['PROPAK']);
        $praMitDest_rec['ROWIDPRACOM'] = $pracom_recA['ROWID'];
        $praMitDest_rec['IDMAIL'] = $Pramail_rec['IDMAIL'];
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PRAMITDEST', 'ROWID', $praMitDest_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'inserimento PRACOM");
                return false;
            }
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }


        /*
         * Aggiorno PRAMAIL e MAIL ARCHIVIO
         */

        if (!$this->insertMailIntegrazione($Pramail_rec, $fileEml, $daMail, $Propas_rec, $pasnotEML, $pasnameEML, $pascla)) {
            $this->setErrCode($this->getErrCode());
            $this->setErrMessage($this->getErrMessage());
            return false;
        }


//        if (!$Pramail_rec) {
//            $Pramail_rec = $this->GetPramailRecIntegrazione($Propas_rec['PRORIN']);
//            $daMail = "damail";
//        }
//        if ($Pramail_rec) {
//            if (!$this->setClasseCaricatoPasso($daMail, $Pramail_rec['IDMAIL'], $Proges_rec['GESNUM'], $Propas_rec['PROPAK'])) {
//                return false;
//            }
//        }

        $propas_recRiletto = $this->GetPropas($Propas_rec['PROPAK'], "propak");

        /*
         * Setto il campo FOPROPAK con la chiave del passo
         */
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
        if (!praFrontOfficeManager::setMarcaturaPrafolist($row_idPrafolist, $Propas_rec['PRONUM'], $Propas_rec['PROPAK'])) {
            $this->setErrCode(praFrontOfficeManager::$lasErrCode);
            $this->setErrMessage(praFrontOfficeManager::$lasErrMessage);
            return false;
        }


        return $propas_recRiletto;
    }

    function bloccaProgressivoPratica() {
        $retLock = $this->lockFilent("1");
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    function leggiProgressivoPratica($workYear) {
//$Filent_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM FILENT WHERE FILKEY=1", false);
        $Filent_rec = $this->GetFilent(1);
        if (!$Filent_rec) {
            $progressivo = 0;
        } else {
            $Proges_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(GESNUM) AS ULTIMO FROM PROGES", false);
            if (substr($Proges_rec['ULTIMO'], 0, 4) != $workYear) {
                $progressivo = 0;
            } else {
                $progressivo = intval($Filent_rec['FILDE1']);
            }
        }
        return str_pad($progressivo + 1, 6, "0", STR_PAD_LEFT);
    }

    function aggiornaProgressivoPratica($progressivo) {
        $Filent_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM FILENT WHERE FILKEY=1", false);
        if (!$Filent_rec) {
            $Filent_rec['FILKEY'] = 1;
            $Filent_rec['FILDE1'] = $progressivo;
            $nins = ItaDB::DBInsert($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
//if ($nins == 0) {
            if ($nins == -1) {
                return false;
            }
        } else {
            $Filent_rec['FILDE1'] = $progressivo;
            $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
//if ($nupd == 0) {
            if ($nupd == -1) {
                return false;
            }
        }
        return true;
    }

    function sbloccaProgressivoPratica($retLock) {
        return $this->unlockFilent($retLock);
    }

    function prenotaPratica($workYear) {
        $retLock = $this->bloccaProgressivoPratica();
        $progressivo = $this->leggiProgressivoPratica($workYear);
        $this->aggiornaProgressivoPratica(intval($progressivo));
        $this->sbloccaProgressivoPratica($retLock);
        return $progressivo;
    }

    function exprenotaPratica($modo = "ALL", $retLock = null) {
        switch ($modo) {
            case "ALL":
                $retLock = $this->lockFilent("1");
                if (!$retLock)
                    return false;
                $Filent_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM FILENT WHERE FILKEY=1", false);
                if (!$Filent_rec) {
                    $progressivo = str_pad(1, 6, "1", STR_PAD_LEFT);
                    $Filent_rec['FILKEY'] = 1;
                    $Filent_rec['FILDE1'] = $progressivo;
                    $nins = ItaDB::DBInsert($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
                    if ($nins == 0) {
                        $this->unlockFilent($retLock);
                        return false;
                    }
                } else {
                    $progressivo = str_pad(intval($Filent_rec['FILDE1']) + 1, 6, "0", STR_PAD_LEFT);
                    $Filent_rec['FILDE1'] = $Filent_rec['FILDE1'] + 1;
                    $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
                    if ($nupd == 0) {
                        $this->unlockFilent($retLock);
                        return false;
                    }
                }
                $this->unlockFilent($retLock);
                return $progressivo;
                break;
            case "LEGGI":
                $Filent_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM FILENT WHERE FILKEY=1", false);
                if (!$Filent_rec) {
                    $progressivo = 1;
                } else {
                    $Proges_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(GESNUM) AS ULTIMO FROM PROGES", false);
                    if (substr($Proges_rec['ULTIMO'], 0, 4) != $retLock) {
                        $progressivo = 1;
                    } else {
                        $progressivo = intval($Filent_rec['FILDE1']);
                    }
                }
                return str_pad($progressivo + 1, 6, "0", STR_PAD_LEFT);
                break;
            case "BLOCCA":
                $retLock = $this->lockFilent("1");
                if (!$retLock) {
                    return false;
                }
                return $retLock;
                break;
            case "SBLOCCA":
                $this->unlockFilent($retLock);
                return true;
                break;
            case "AGGIORNA":
                $Filent_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM FILENT WHERE FILKEY=1", false);
                if (!$Filent_rec) {
                    $progressivo = str_pad(1, 6, "1", STR_PAD_LEFT);
                    $Filent_rec['FILKEY'] = 1;
                    $Filent_rec['FILDE1'] = $progressivo;
                    $nins = ItaDB::DBInsert($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
                    if ($nins == 0) {
                        return false;
                    }
                } else {
                    $progressivo = str_pad(intval($Filent_rec['FILDE1']) + 1, 6, "0", STR_PAD_LEFT);
                    $Filent_rec['FILDE1'] = $Filent_rec['FILDE1'] + 1;
                    $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'FILENT', 'ROWID', $Filent_rec);
                    if ($nupd == 0) {
                        return false;
                    }
                }
                return true;
                break;
        }
    }

    function lockFilent($rowid) {
        $retLock = ItaDB::DBLock($this->getPRAMDB(), "FILENT", $rowid, "", 120);
        if ($retLock['status'] != 0) {
//Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVI PRATICHE non Riuscito.');
            $this->setErrCode($retLock['status']);
            $this->setErrMessage('Blocco Tabella PROGRESSIVI PRATICHE non Riuscito.');
            return false;
        }
        return $retLock;
    }

    function unlockFilent($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            return false;
        }
    }

    function getDestinatariRichiesta($Proric_rec) {
        $destinatari = array();
//Resp. procedimento.....
        if ($Proric_rec['RICRES']) {
            $Ananom_rec = $this->GetAnanom($Proric_rec['RICRES'], 'codice');
//            $Ananom_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
            if ($Ananom_rec) {
                $destinatari["PROCEDIMENTO"] = $Ananom_rec['NOMDEP'];
            }
        }
// Resp. Untità.....
        if ($Proric_rec['RICOPE']) {
            $sql = "SELECT * FROM ANAUNI
                        LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                        WHERE
                            ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                            ANAUNI.UNISER='" . $Proric_rec['RICSER'] . "' AND
                            ANAUNI.UNIOPE='" . $Proric_rec['RICOPE'] . "' AND
                            ANAUNI.UNIDAP='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
            if ($Ananom_rec) {
                $destinatari["UNITAOPERATIVA"] = $Ananom_rec['NOMDEP'];
            }
        }

// Resp. servizio.......
        if ($Proric_rec['RICSER']) {
            $sql = "SELECT * FROM ANAUNI
                        LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                        WHERE
                            ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                            ANAUNI.UNISER='" . $Proric_rec['RICSER'] . "' AND
                            ANAUNI.UNIOPE='' AND ANAUNI.UNIADD='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
            if ($Ananom_rec) {
                $destinatari["SERVIZIO"] = $Ananom_rec['NOMDEP'];
            }
        }

        if ($Proric_rec['RICSET']) {
            $sql = "SELECT * FROM ANAUNI
                    LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIRES=ANANOM.NOMRES
                    WHERE
                        ANAUNI.UNISET='" . $Proric_rec['RICSET'] . "' AND
                        ANAUNI.UNISER='' AND ANAUNI.UNIOPE='' AND ANAUNI.UNIADD='' AND ANAUNI.UNIAPE=''";
            $Ananom_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
            if ($Ananom_rec) {
                $destinatari["SETTORE"] = $Ananom_rec['NOMDEP'];
            }
        }
        return $destinatari;
    }

    /**
     *
     * @param type $procedimento
     * @param type $forzaChiusura
     * @return array|boolean|string
     */
    function sincronizzaStato($procedimento, $dataChiusura = "") {
        /*
         * Estraggo il record stato del pratica da aggiornare.
         */
        $Prasta_rec = $this->GetPrasta($procedimento, 'codice');
        if (!$Prasta_rec) {
            $fl_trovato = false;
            $Prasta_rec = array();
        } else {
            $fl_trovato = true;
        }


        /*
         * Estraggo il recod pratica
         */
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
            $Prasta_rec['STADES'] = 'Pratica Chiusa';
            return $this->registraPrasta($Prasta_rec, $fl_trovato);
        }


        /*
         * Estraggo i passi collegati alla pratica
         */
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '$procedimento' AND (PROINI<>'' OR PROFIN <>'' ) ORDER BY PROINI, PROSEQ";
        $Propas_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        if ($Propas_tab) {

            /*
             * Scorro i passi che si non aperti o chiusi
             */
            $pubblica = 0;
            foreach ($Propas_tab as $Propas_rec) {
                $Prasta_rec['STAPAS'] = $Propas_rec['PROCLT'];
                $Prasta_rec['STADIN'] = $Propas_rec['PROINI'];
                $Prasta_rec['STADFI'] = $Propas_rec['PROFIN'];
                $Prasta_rec['STAPAK'] = $Propas_rec['PROPAK'];
                if ($Propas_rec['PROPST'] != 0) {
                    $Prasta_rec['STADEX'] = $Propas_rec['PRODTP'] . " - " . $Propas_rec['PRODPA'] . " - " . $Propas_rec['PROANN'];
                    $pubblica = $Propas_rec['PROPST'];
                }
//$Prasta_rec['STAPST'] = $Propas_rec['PROPST'];
//                $Anatsp_rec = array();
                if ($Propas_rec['PROSTATO'] == 0) {
                    $Anastp_rec['STPFLAG'] = "In corso";
                    $Anastp_rec['STPDES'] = "In corso";
                } else {
                    $Anastp_rec = $this->GetAnastp($Propas_rec['PROSTATO']);
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
//Possibile modifica da fare per dare una validità alla sospensione
//if ($Propas_rec['PROFIN'] == '') {
                    $arrayStato['tipo'] = 'Sospesa';
                    $arrayStato['chiudi'] = false;
                    $arrayStato['descrizione'] = array($Anastp_rec['STPDES']);
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
//}
                } elseif ($Anastp_rec['STPFLAG'] == "In corso") {
                    $arrayStato['tipo'] = 'inCorso';
                    $arrayStato['chiudi'] = false;
                    if ($Propas_rec['PROFIN'] == '') {
                        $arrayStato['descrizione'][] = $Anastp_rec['STPDES'];
                    }
                    $arrayStato['PROPAK'] = $Propas_rec['PROPAK'];
                }
            }

            if ($pubblica != 0) {
                $Prasta_rec['STAPST'] = $pubblica;
            }
        }

        if ($arrayStato['chiudi'] == true) {
            if ($dataChiusura) {
                $Proges_rec['GESDCH'] = $dataChiusura;
            } else {
                $Proges_rec['GESDCH'] = date("Ymd");
            }
            $Proges_rec['GESCLOSE'] = $arrayStato['PROPAK'];
        } else {
            $Proges_rec['GESDCH'] = "";
            $Proges_rec['GESCLOSE'] = "";
        }


        try {
            $nrow = ItaDB::DBUpdate($this->getPRAMDB(), 'PROGES', 'ROWID', $Proges_rec);
            if ($nrow == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage('SincronizzaStato: Aggiornamento Stato Fascicolo Fallito.');
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage('SincronizzaStato: Aggiornamento Stato Fascicolo Fallito.' . $e->getMessage());
            return false;
        }

        if ($Proges_rec['GESDCH'] != '') {
            if ($Proges_rec['GESCLOSE'] != "") {
                if ($arrayStato['descrizione']) {
                    $Prasta_rec['STADES'] = implode(" , ", array_unique($arrayStato['descrizione']));
                } else {
                    $Prasta_rec['STADES'] = 'Procedimento Chiuso';
                }
            } else {
                $Prasta_rec['STADES'] = 'Procedimento Chiuso';
            }
        } else {
            if ($Prasta_rec['STADIN'] == '' && $Prasta_rec['STADFI'] == '') {
                $Prasta_rec['STADES'] = 'Procedimento Acquisito';
            } else {
                if ($arrayStato['descrizione']) {
                    $Prasta_rec['STADES'] = implode(" , ", array_unique($arrayStato['descrizione']));
                } else {
                    $Prasta_rec['STADES'] = 'Procedimento in Corso';
                }
            }
        }

        return $this->registraPrasta($Prasta_rec, $fl_trovato);
    }

    private function registraPrasta($Prasta_rec, $fl_trovato) {
        if ($fl_trovato) {
            try {
                $nrow = ItaDB::DBUpdate($this->getPRAMDB(), 'PRASTA', 'ROWID', $Prasta_rec);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Aggiornamento Stato Fascicolo Fallito.');
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('Aggiornamento Stato Fascicolo Fallito.' . $e->getMessage());
                return false;
            }
        } else {
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PRASTA', 'ROWID', $Prasta_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Aggiornamento Stato Fascicolo Fallito.');
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('Aggiornamento Stato Fascicolo Fallito.' . $e->getMessage());
                return false;
            }
        }
        return $Prasta_rec;
    }

    public function GetAnpdoc($codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANPDOC WHERE ANPKEY = '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM ANPDOC WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    function decodeXMLRichiesta($fileXml) {
        include_once (ITA_LIB_PATH . '/QXml/QXml.class.php');
        $Proric_rec = array();
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($fileXml);
//$arrayXml = $xmlObj->getArray();
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        $Proric_rec['RICNUM'] = $arrayXml['PRORIC']['RICNUM'][itaXML::textNode];
        $Proric_rec['RICDRE'] = $arrayXml['PRORIC']['RICDRE'][itaXML::textNode];
        $Proric_rec['RICPRO'] = $arrayXml['PRORIC']['RICPRO'][itaXML::textNode];
        $Proric_rec['RICRES'] = $arrayXml['PRORIC']['RICRES'][itaXML::textNode];
        $Proric_rec['RICANA'] = $arrayXml['PRORIC']['RICANA'][itaXML::textNode];
        $Proric_rec['RICCOG'] = $arrayXml['PRORIC']['RICCOG'][itaXML::textNode];
        $Proric_rec['RICNOM'] = $arrayXml['PRORIC']['RICNOM'][itaXML::textNode];
        $Proric_rec['RICFIS'] = $arrayXml['PRORIC']['RICFIS'][itaXML::textNode];
        $Proric_rec['RICSET'] = $arrayXml['PRORIC']['RICSET'][itaXML::textNode];
        $Proric_rec['RICSER'] = $arrayXml['PRORIC']['RICSER'][itaXML::textNode];
        $Proric_rec['RICOPE'] = $arrayXml['PRORIC']['RICOPE'][itaXML::textNode];
        return $Proric_rec;
    }

    public function GetIncDagset($datiAgg) {
        foreach ($datiAgg as $campo) {
            if (strlen($campo['DAGSET']) == 25) {
                $array1[] = $campo['DAGSET'];
            }
        }
        $array1 = $this->array_sort($array1, "DAGSET");
        $incDagset = substr(end($array1), -2) + 1;
        $incDagset = str_repeat("0", 2 - strlen($incDagset)) . $incDagset;
        return $incDagset;
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
                $file['PASSHA2'] = hash_file('sha256', $file['FILEPATH']);
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
            $allegato['PASSHA2'] = hash_file('sha256', $allegato['FILEPATH']);
            $allegato['level'] = 1;
            $allegato['parent'] = "seq_GEN";
            return array(
                "Allegato" => $allegato,
                "daFile" => true
            );
        }
    }

    function loadSelectDirFile($zipDir) {
//        $files = scandir($zipDir);
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

    function explodeZipDirPlain($zipDir, $arrayExplodeZip, $zipRoot = '', $provenienza = '') {
        if (!$zipRoot) {
            $zipRoot = $zipDir;
        }
        $ds = "/";
        $bad = array('.', '..');
        $files = array_diff(scandir($zipDir), $bad);
        foreach ($files as $ftmp) {
            $key = count($arrayExplodeZip) + 1;
            if (is_dir($zipDir . $ds . $ftmp) == true) {
                $arrayExplodeZip = $this->explodeZipDirPlain($zipDir . $ds . $ftmp, $arrayExplodeZip, $zipRoot, $provenienza);
            } elseif (is_file($zipDir . $ds . $ftmp) == true) {
                $innerDir = str_replace($zipRoot . "/", '', $zipDir);
                $arrayFile = array();
                $arrayDir['SEQ'] = $key;
                $arrayFile['NAME'] = str_replace($ds, '_', $innerDir) . "_" . $ftmp;
                ;
                $arrayFile['FILEPATH'] = $zipDir . $ds . $ftmp;
                $arrayFile['INFO'] = 'GENERALE';
                $arrayFile['PROVENIENZA'] = $provenienza;
                $arrayFile['NOTE'] = "File originale: " . $ftmp;
//Valorizzo Array
                $arrayFile['FILENAME'] = md5(rand() * time()) . "." . pathinfo($zipDir . $ds . $ftmp, PATHINFO_EXTENSION);
                $arrayFile['FILEINFO'] = $ftmp;
                $arrayFile['FILEORIG'] = str_replace($ds, '_', $innerDir) . "_" . $ftmp;
                $arrayFile['ROWID'] = 0;
                $arrayExplodeZip[$key] = $arrayFile;
            }
        }
        return $arrayExplodeZip;
    }

    function explodeZipDir($zipDir, $arrayExplodeZip, $level, $parent = 0, $provenienza = "") {
        $level += 1;
//        $files = scandir($zipDir);
        $ds = "/";
        $bad = array('.', '..');
        $files = array_diff(scandir($zipDir), $bad);
        foreach ($files as $ftmp) {
            $key = count($arrayExplodeZip) + 1;
            if (is_dir($zipDir . $ds . $ftmp) == true) {
                $arrayDir = array();
                $arrayDir['SEQ'] = $key;
                $arrayDir['NAME'] = "<span style=\"display:inline-block;vertical-align:bottom;\" class=\"ui-icon ui-icon-folder-open\"></span>$ftmp";
                $arrayDir['FILEPATH'] = $zipDir . $ds . $ftmp;
                $arrayDir['level'] = $level;
                $arrayDir['parent'] = $parent;
                $arrayDir['isLeaf'] = 'false';
                $arrayDir['expanded'] = 'true';
                $arrayDir['loaded'] = 'true';
                $arrayExplodeZip[$key] = $arrayDir;
                $arrayExplodeZip = $this->explodeZipDir($zipDir . $ds . $ftmp, $arrayExplodeZip, $level, $key, $provenienza);
            } elseif (is_file($zipDir . $ds . $ftmp) == true) {
                $arrayFile = array();
                $arrayFile['SEQ'] = $key;
                $arrayFile['NAME'] = $ftmp;
                $arrayFile['INFO'] = 'GENERALE';
                $arrayFile['PROVENIENZA'] = $provenienza;
                $arrayFile['NOTE'] = "File originale: " . $ftmp;
//Valorizzo Array
                $arrayFile['FILENAME'] = md5(rand() * time()) . "." . pathinfo($zipDir . $ds . $ftmp, PATHINFO_EXTENSION);
                $arrayFile['FILEINFO'] = $ftmp;
                $arrayFile['FILEORIG'] = $ftmp;
                $arrayFile['FILEPATH'] = $zipDir . $ds . $ftmp;
                $arrayFile['ROWID'] = 0;
                $arrayFile['level'] = $level;
                $arrayFile['parent'] = $parent;
                $arrayFile['expanded'] = 'false';
                $arrayFile['loaded'] = 'false';
                $arrayFile['isLeaf'] = 'true';
                $arrayExplodeZip[$key] = $arrayFile;
            }
        }
        return $arrayExplodeZip;
    }

// NOVITA IMPORTAZIONE FINE

    public function creaXML($passiSel, $pranum) {
        $dbsuffix = $this->getDbSuffix($pranum);
        if ($dbsuffix) {
            $praLib = new praLib($dbsuffix);
        } else {
            $praLib = $this;
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
        $xml .= "<PRAMXX>\r\n";
        /*
         * Export di ITEPAS
         */
        $xml .= "<!-- Tabella ITEPAS -->";
        foreach ($passiSel as $key => $passo) {
            $itepas_rec = $praLib->GetItepas($passo['ROWID'], 'rowid');
            if ($itepas_rec) {
                $xml .= "<ITEPAS>\r\n";
                foreach ($itepas_rec as $Chiave => $Campo) {
                    if ($Chiave == "ITEMETA") {
                        $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                    } else {
                        $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                    }
                }
                $xml .= "</ITEPAS>\r\n";

                /*
                 * Cerco i passi collegati e li inserisco nell'xml dei passi selezionati
                 */
                $passiCollegati = $praLib->AddPassiAntecedenti($itepas_rec['ROWID']);
                if ($passiCollegati) {
                    foreach ($passiCollegati as $passo) {
                        $xml .= "<ITEPAS>\r\n";
                        foreach ($passo as $Chiave => $Campo) {
                            if ($Chiave == "ITEMETA") {
                                $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                            } else {
                                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                            }
                        }
                        $xml .= "</ITEPAS>\r\n";
                    }
                }
            }
        }


        foreach ($passiSel as $key => $passo) {
            $itevpadett_tab = $praLib->GetItevpadett($passo['ITEKEY'], 'itekey');
            if ($itevpadett_tab) {
                $xml .= "<!-- Tabella ITEVPADETT -->";
                foreach ($itevpadett_tab as $key => $itevpadett_rec) {
                    $xml .= "<ITEVPADETT>\r\n";
                    foreach ($itevpadett_rec as $Chiave => $Campo) {
                        $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . htmlspecialchars($Chiave, ENT_COMPAT, 'ISO-8859-1') . ">\r\n";
                    }
                    $xml .= "</ITEVPADETT>\r\n";
                }
            }
        }


        /*
         * Export di ITEDAG
         */
        foreach ($passiSel as $key => $passo) {
            $itepas_rec = $praLib->GetItepas($passo['ROWID'], 'rowid');
            if ($itepas_rec) {
                $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "'";
                $itedag_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
                if ($itedag_tab) {
                    $xml .= "<!-- Tabella ITEDAG -->";
                    foreach ($itedag_tab as $Chiave => $itedag_rec) {
                        $xml .= "<ITEDAG>\r\n";
                        foreach ($itedag_rec as $Chiave => $Campo) {
                            if ($Chiave == "ITDMETA") {
                                $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                            } else {
                                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                            }
                        }
                        $xml .= "</ITEDAG>\r\n";
                    }
                }

                /*
                 * Cerco i passi collegati, poi mi trovo i dati aggiuntivi e li inserisco nell'xml dei passi selezionati
                 */
                $passiCollegati = $praLib->AddPassiAntecedenti($itepas_rec['ROWID']);
                if ($passiCollegati) {
                    foreach ($passiCollegati as $passo) {
                        $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $passo['ITEKEY'] . "'";
                        $itedag_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
                        if ($itedag_tab) {
                            $xml .= "<!-- Tabella ITEDAG -->";
                            foreach ($itedag_tab as $Chiave => $itedag_rec) {
                                $xml .= "<ITEDAG>\r\n";
                                foreach ($itedag_rec as $Chiave => $Campo) {
                                    if ($Chiave == "ITDMETA") {
                                        $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                                    } else {
                                        $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                                    }
                                }
                                $xml .= "</ITEDAG>\r\n";
                            }
                        }
                    }
                }
            }
        }

        /*
         * Export PRAAZIONI
         */
        foreach ($passiSel as $key => $passo) {
            $itepas_rec = $praLib->GetItepas($passo['ROWID'], 'rowid');
            if ($itepas_rec) {
                $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '" . $itepas_rec['ITECOD'] . "' AND ITEKEY = '" . $itepas_rec['ITEKEY'] . "'";
                $praazioni_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
                if ($praazioni_tab) {
                    $xml .= "<!-- Tabella PRAAZIONI -->";
                    foreach ($praazioni_tab as $Chiave => $praazioni_rec) {
                        $xml .= "<PRAAZIONI>\r\n";
                        foreach ($praazioni_rec as $Chiave => $Campo) {
                            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                        }
                        $xml .= "</PRAAZIONI>\r\n";
                    }
                }
            }
        }

        /*
         * Export ITECONTROLLI
         */
        foreach ($passiSel as $key => $passo) {
            $itepas_rec = $praLib->GetItepas($passo['ROWID'], 'rowid');
            if ($itepas_rec) {
                $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "'";
                $itecontrolli_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
                if ($itecontrolli_tab) {
                    $xml .= "<!-- Tabella ITECONTROLLI -->";
                    foreach ($itecontrolli_tab as $Chiave => $itecontrolli_rec) {
                        $xml .= "<ITECONTROLLI>\r\n";
                        foreach ($itecontrolli_rec as $Chiave => $Campo) {
                            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                        }
                        $xml .= "</ITECONTROLLI>\r\n";
                    }
                }
            }
        }

        /*
         * Export ITEDEST
         */
        foreach ($passiSel as $key => $passo) {
            $itepas_rec = $praLib->GetItepas($passo['ROWID'], 'rowid');
            if ($itepas_rec) {
                $sql = "SELECT * FROM ITEDEST WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "'";
                $itedest_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
                if ($itedest_tab) {
                    $xml .= "<!-- Tabella ITEDEST -->";
                    foreach ($itedest_tab as $Chiave => $itedest_rec) {
                        $xml .= "<ITEDEST>\r\n";
                        foreach ($itedest_rec as $Chiave => $Campo) {
                            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                        }
                        $xml .= "</ITEDEST>\r\n";
                    }
                }
            }
        }

        $xml .= "</PRAMXX>\r\n";
        return $xml;
    }

    public function DecodificaControllo($ctr, $tipo = "passo") {
        $msgCtr = '';
        if ($ctr) {
            $controlli = unserialize($ctr);
            if ($tipo == "filtri") {
                $controlli = $controlli['CONDIZIONI'];
            }
            foreach ($controlli as $campo) {
                switch ($campo['CONDIZIONE']) {
                    case '==':
                        $condizione = "è uguale a ";
                        break;
                    case '!=':
                        $condizione = "è diverso da ";
                        break;
                    case '>':
                        $condizione = "è maggiore a ";
                        break;
                    case '<':
                        $condizione = "è minore a ";
                        break;
                    case '>=':
                        $condizione = "è maggiore-uguale a ";
                        break;
                    case '<=':
                        $condizione = "è minore-uguale a ";
                        break;
                    case 'LIKE':
                        $condizione = "contiene ";
                        break;
                }
                if ($campo['VALORE'] == '') {
                    $valore = "vuoto";
                } else {
                    $valore = $campo['VALORE'];
                }
                switch ($campo['OPERATORE']) {
                    case 'AND':
                        $operatore = 'e ';
                        break;
                    case 'OR':
                        $operatore = 'oppure ';
                }

                if (!$campo['CONDIZIONE']) {
                    $condizione = $valore = '';
                }

                $prefix = 'il campo ';
                if (substr($campo['CAMPO'], 0, 1) === '#' || (isset($campo['TIPOCAMPO']) && $campo['TIPOCAMPO'] != 'D')) {
                    $prefix = '';
                }

                $msgCtr = $msgCtr . $operatore . $prefix . $campo['CAMPO'] . ' ' . $condizione . $valore . chr(10);
            }
        }
        return $msgCtr;
    }

    public function FillFormPdf($dati, $input, $output) {
        $xmlFill = itaLib::getAppsTempPath() . "/xmlFill.xml";
        $FileXml = fopen($xmlFill, "w");
        $outputFile = pathinfo($output, PATHINFO_FILENAME);
        $outputPath = itaLib::getAppsTempPath();
        $xml = $this->CreaXmlFillPdf($dati, $input, $output);
        fwrite($FileXml, $xml);
        fclose($FileXml);
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFill;
        exec($command, $ret);
        $taskDump = false;
        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskDump = true;
                break;
            }
        }
        if ($taskDump == false) {
            return false;
        } else {
            return true;
        }
        $Nome_file = pathinfo($output, PATHINFO_FILENAME) . ".pdf";
    }

    public function FlatFormPdf($input, $output) {
        $xmlFlat = itaLib::getAppsTempPath() . "/xmlFlat.xml";
        $FileXml = fopen($xmlFlat, "w");
        $outputFile = pathinfo($output, PATHINFO_FILENAME);
        $outputPath = itaLib::getAppsTempPath();
        $xml = $this->CreaXmlFlattenPdf($input, $output);
        fwrite($FileXml, $xml);
        fclose($FileXml);
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFlat;
        exec($command, $ret);
        $taskDump = false;
        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskDump = true;
                break;
            }
        }
        if ($taskDump == false) {
            return false;
        } else {
            return true;
        }
    }

    public function CreaFileInfo($filePDF) {
        $xmlDump = itaLib::getAppsTempPath() . "/xmlDump.xml";
        $FileXml = fopen($xmlDump, "w");
        $outputFile = pathinfo($filePDF, PATHINFO_FILENAME);
        $outputPath = itaLib::getAppsTempPath();
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"dump_data_fields\">\r\n";
        $xml .= "       <input>$filePDF</input>\r\n";
        $xml .= "       <output>$outputPath/$outputFile.info</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        fwrite($FileXml, $xml);
        fclose($FileXml);
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlDump;
        exec($command, $ret);
        $taskDump = false;
        foreach ($ret as $value) {
            $arrayExec = explode("|", $value);
            if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                $taskDump = true;
                break;
            } else {
                Out::msgStop("Decod file info", $arrayExec[0] . " - " . $arrayExec[2]);
            }
        }
        if ($taskDump == false) {
            return false;
        } else {
            return true;
        }
    }

    public function CreaXmlFlattenPdf($input, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"flatten\">\r\n";
        $xml .= "       <input>$input</input>\r\n";
        $xml .= "       <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function CreaXmlFillPdf($dati, $input, $output) {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<root>\r\n";
        $xml .= "    <task name=\"fill_form\">\r\n";
        $xml .= "        <input>$input</input>\r\n";
        $xml .= "        <output>$output</output>\r\n";
        $xml .= "          <fields>\r\n";
        foreach ($dati as $key => $value) {
            $xml .= "           <field>\r\n";
            $xml .= "               <name><![CDATA[" . $key . "]]></name>\r\n";
            $xml .= "               <value><![CDATA[$value]]></value>\r\n";
//            $xml .= "               <properties>\r\n";
//            if ($Ricdag_rec['DAGROL'] == 1) {
//                $xml .= "                   <property name=\"READ_ONLY\">1</property>\r\n";
//            }
//            $xml .= "               </properties>\r\n";
            $xml .= "           </field>\r\n";
        }

        $xml .= "          </fields>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>";
        return $xml;
    }

    public function getTitolarioProt() {
        return array('Valore' => "0001|0002|0001", 'Separatore' => '|');
    }

    public function getDestinatariProt() {
        $destinatari = array();
        $destinatari[] = array('CodiceDestinatario' => '900012',
            'Denominazione' => "BILO' PATRIZIA",
            'Indirizzo' => '',
            'CAP' => '',
            'Citta' => 'SEDE',
            'Provincia' => '');
        $destinatari[] = array('CodiceDestinatario' => '000720',
            'Denominazione' => "ANGELICO GESUALDO",
            'Indirizzo' => '',
            'CAP' => '',
            'Citta' => 'SEDE',
            'Provincia' => '');
        return $destinatari;
    }

    public function getUfficiProt() {
        $uffici = array();
        $uffici[]['CodiceUfficio'] = 'ANAG';
        $uffici[]['CodiceUfficio'] = '  UT';
        $uffici[]['CodiceUfficio'] = 'BIBL';
        return $uffici;
    }

    public function getCustodePaleo($numeroPratica) {
        $Proges_rec = $this->GetProges($numeroPratica);
//Responsabile del procedimento
        if ($Proges_rec['GESRES'] != '') {
            $Ananom_rec = $this->GetAnanom($Proges_rec['GESRES']);
            $custodeProges = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello aggregato
        if ($Proges_rec['GESSPA'] != '') {
            $Anaspa_rec = $this->GetAnaspa($Proges_rec['GESSPA']);
            $UteCod = $Anaspa_rec['SPARES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $custodeAnaspa = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello
        if ($Proges_rec['GESTSP'] != '') {
            $Anatsp_rec = $this->GetAnatsp($Proges_rec['GESTSP']);
            $UteCod = $Anatsp_rec['TSPRES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $custodeAnatsp = unserialize($Ananom_rec['NOMMETA']);
        }
//recupero dai parametri generali
        $this->devLib = new devLib();
        $tmp_rec['CodiceUO'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOUO', false);
        $custodeParm['CodiceUO'] = $tmp_rec['CodiceUO']['CONFIG'];
        $tmp_rec['Cognome'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOCOGNOME', false);
        $custodeParm['Cognome'] = $tmp_rec['Cognome']['CONFIG'];
        $tmp_rec['Nome'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEONOME', false);
        $custodeParm['Nome'] = $tmp_rec['Nome']['CONFIG'];
        $tmp_rec['Ruolo'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEORUOLO', false);
        $custodeParm['Ruolo'] = $tmp_rec['Ruolo']['CONFIG'];
//recupero parametri da utente login
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $custodeLogin = $this->accLib->GetOperatorePaleo($UteCod);
//$custodeLogin = unserialize($tmp_rec['METAVALUE']);
//scelta dei parametri da usare per la chiamata
        if ($custodeProges) {
            $custodePaleo = $custodeProges;
        } elseif ($custodeAnaspa) {
            $custodePaleo = $custodeAnaspa;
        } elseif ($custodeAnatsp) {
            $custodePaleo = $custodeAnatsp;
        } elseif ($custodeLogin) {
            $custodePaleo = $custodeLogin;
        } elseif ($custodeParm['CodiceUO']) {
            $custodePaleo = $custodeParm;
        }
        $OperatorePaleo = new itaOperatorePaleo();
        $OperatorePaleo->setCodiceUO($custodePaleo['CodiceUO']);
        $OperatorePaleo->setCognome($custodePaleo['Cognome']);
        $OperatorePaleo->setNome($custodePaleo['Nome']);
        $OperatorePaleo->setRuolo($custodePaleo['Ruolo']);

        return $OperatorePaleo;
    }

    public function getUfficioHWS($numeroPratica) {
        $Proges_rec = $this->GetProges($numeroPratica);
//Responsabile del procedimento
        if ($Proges_rec['GESRES'] != '') {
            $Ananom_rec = $this->GetAnanom($Proges_rec['GESRES']);
            $ufficioProges = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello aggregato
        if ($Proges_rec['GESSPA'] != '') {
            $Anaspa_rec = $this->GetAnaspa($Proges_rec['GESSPA']);
            $UteCod = $Anaspa_rec['SPARES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $ufficioAnaspa = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello
        if ($Proges_rec['GESTSP'] != '') {
            $Anatsp_rec = $this->GetAnatsp($Proges_rec['GESTSP']);
            $UteCod = $Anatsp_rec['TSPRES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $ufficioAnatsp = unserialize($Ananom_rec['NOMMETA']);
        }
//recupero dai parametri generali
        $this->devLib = new devLib();
        $tmp_rec['ufficio'] = $this->devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHUFFICIO', false);
        $ufficioParm['ufficio'] = $tmp_rec['ufficio']['CONFIG'];
////recupero parametri da utente login
//        $UteCod = App::$utente->getKey('idUtente');
//        $this->accLib = new accLib();
//        $custodeLogin = $this->accLib->GetOperatorePaleo($UteCod);
//$custodeLogin = unserialize($tmp_rec['METAVALUE']);
//scelta dei parametri da usare per la chiamata
        if ($ufficioProges) {
            $ufficioHWS = $ufficioProges;
        } elseif ($ufficioAnaspa) {
            $ufficioHWS = $ufficioAnaspa;
        } elseif ($ufficioAnatsp) {
            $ufficioHWS = $ufficioAnatsp;
        } elseif ($ufficioParm['ufficio']) {
            $ufficioHWS = $ufficioParm;
        }
        return $ufficioHWS['ufficio'];
    }

    public function getTrasmissioniUtentePaleo($numeroPratica, $Ragione = '') {//, $segueCartaceo = false) {
        $Proges_rec = $this->GetProges($numeroPratica);
//Responsabile del procedimento
        if ($Proges_rec['GESRES'] != '') {
            $Ananom_rec = $this->GetAnanom($Proges_rec['GESRES']);
            $destinatarioProges = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello aggregato
        if ($Proges_rec['GESSPA'] != '') {
            $Anaspa_rec = $this->GetAnaspa($Proges_rec['GESSPA']);
            $UteCod = $Anaspa_rec['SPARES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $destinatarioAnaspa = unserialize($Ananom_rec['NOMMETA']);
        }
//Responsabile sportello
        if ($Proges_rec['GESTSP'] != '') {
            $Anatsp_rec = $this->GetAnatsp($Proges_rec['GESTSP']);
            $UteCod = $Anatsp_rec['TSPRES'];
            $Ananom_rec = $this->GetAnanom($UteCod);
            $destinatarioAnatsp = unserialize($Ananom_rec['NOMMETA']);
        }
//recupero dai parametri generali
        $this->devLib = new devLib();
        $tmp_rec['CodiceUO'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOUO', false);
        $destinatarioParm['CodiceUO'] = $tmp_rec['CodiceUO']['CONFIG'];
        $tmp_rec['Cognome'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOCOGNOME', false);
        $destinatarioParm['Cognome'] = $tmp_rec['Cognome']['CONFIG'];
        $tmp_rec['Nome'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEONOME', false);
        $destinatarioParm['Nome'] = $tmp_rec['Nome']['CONFIG'];
        $tmp_rec['Ruolo'] = $this->devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEORUOLO', false);
        $destinatarioParm['Ruolo'] = $tmp_rec['Ruolo']['CONFIG'];
//recupero parametri da utente login
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $destinatarioLogin = $this->accLib->GetOperatorePaleo($UteCod);
//$destinatarioLogin = unserialize($tmp_rec['METAVALUE']);
//scelta dei parametri da usare per la chiamata
        $destinatariPaleo = array();
        if ($destinatarioProges) {
            $destinatariPaleo[] = $destinatarioProges;
        }
        if ($destinatarioAnaspa) {
            $destinatariPaleo[] = $destinatarioAnaspa;
        }
        if ($destinatarioAnatsp) {
            $destinatariPaleo[] = $destinatarioAnatsp;
        }
        if ($destinatarioLogin) {
            $destinatariPaleo[] = $destinatarioLogin;
        }
        if ($destinatarioParm['CodiceUO']) {
            $destinatariPaleo[] = $destinatarioParm;
        }
//TODO: eliminare doppioni
        $TrasmissioniPaleo = array();

        foreach ($destinatariPaleo as $key => $destinatarioPaleo) {

            $OperatorePaleo = new itaOperatorePaleo();
            $OperatorePaleo->setCodiceUO($destinatarioPaleo['CodiceUO']);
            $OperatorePaleo->setCognome($destinatarioPaleo['Cognome']);
            $OperatorePaleo->setNome($destinatarioPaleo['Nome']);
            $OperatorePaleo->setRuolo($destinatarioPaleo['Ruolo']);

            $TrasmissioniPaleo[$key]['TrasmissioneUtente']['OperatoreDestinatario'] = $OperatorePaleo->toArray();
            $TrasmissioniPaleo[$key]['TrasmissioneUtente']['Ragione'] = $Ragione;
        }

        return $TrasmissioniPaleo;
    }

    public function GetPracomP($compak) {//, $tipoRic = 'compak') {
        return ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '" . $compak . "' AND COMTIP = 'P'", false);
    }

    public function GetPracomA($compak, $rowid = false) {
        $whereRif = "";
        if ($rowid) {
            $whereRif = "AND COMRIF = '" . $rowid . "'";
        }
        return ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK = '" . $compak . "' AND COMTIP = 'A' " . $whereRif, false);
    }

    public function verificaPDFA($fileName) {
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
            return $ret;
        }
        $Filde2 = $this->getFlagPDFA();
        $verifyPDFA = substr($Filde2, 0, 1);
//        $convertPDFA = substr($Filde2, 1, 1);
        $PDFLevel = substr($Filde2, 2, 1);
        if ($verifyPDFA == "1") {
            $ret = itaPDFAUtil::verifyPDFSimple($fileName, 2, $PDFLevel);
        } else {
            $ret['status'] = 0;
            $ret['message'] = "Nulla da verificare";
        }
        return $ret;
    }

    public function convertiPDFA($fileName, $outputFile, $deleteFileName = false) {
        if ($fileName == $outputFile) {
            $ret['status'] = -99;
            $ret['message'] = "Nome file da convertire uguale al nome file convertito. Non ammesso.";
            return $ret;
        }
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
            $ret['status'] = -99;
            $ret['message'] = "file non adatto alla conversione";
            return $ret;
        }
        $Filde2 = $this->getFlagPDFA();
//        $verifyPDFA = substr($Filde2, 0, 1);
//        $convertPDFA = substr($Filde2, 1, 1);
        $PDFLevel = substr($Filde2, 2, 1);
        $ret = itaPDFAUtil::convertPDF($fileName, $outputFile, 2, $PDFLevel);
        if ($ret['status'] == 0) {
            if ($deleteFileName === true) {
                unlink($fileName);
            }
        }
        return $ret;
    }

    public function getFlagPDFA() {
        $Filent_rec = $this->GetFilent(1);
        $Filde2 = $Filent_rec['FILDE2'];
        if (!$Filde2) {
            $Filde2 = "00A";
        }
        return $Filde2;
    }

    public function getGenericTab($sql, $multi = true, $tipoDB = 'PRAM') {
        if ($tipoDB == 'PRAM') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
        }
        return $tabella_tab;
    }

    public function getPraMail($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PRAMAIL WHERE IDMAIL='" . $codice . "'";
        } elseif ($tipo == 'rowidarchivio') {
            $sql = "SELECT * FROM PRAMAIL WHERE ROWIDARCHIVIO='" . $codice . "'";
        } elseif ($tipo == 'gesnum') {
            $sql = "SELECT * FROM PRAMAIL WHERE GESNUM='" . $codice . "'";
        } elseif ($tipo == 'propak') {
            $sql = "SELECT * FROM PRAMAIL WHERE PROPAK='" . $codice . "'";
        } else {
            $sql = "SELECT * FROM PRAMAIL WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function GetIconAccettazioneConsegna($idMail, $chiavePasso, $tipo = "") {
        $praMail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAMAIL WHERE COMIDMAIL='$idMail' AND COMPAK='$chiavePasso' AND ISRICEVUTA=1", true);
        $icon = array();
//
//Per tabella passi su PRAGEST
//
        if ($tipo == "PASSO") {
            $icon['accettazione'] = "<div style=\"display:inline-block;\">
                                         <span class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;vertical-align:middle;\"></span>
                                         <div style=\"display:inline-block;vertical-align:middle;\">Accettazione non ricevuta</div> 
                                     </div>";
            $descAcc = "Accettazione Ricevuta";
            $icon['consegna'] = "<div style=\"display:inline-block;\">
                                         <span class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;vertical-align:middle;\"></span>
                                         <div style=\"display:inline-block;vertical-align:middle;\">Consegna non ricevuta</div> 
                                 </div>";
            $descCon = "Avvenuta Consegna";
        }

//
//Se c'è l'id mail metto la lentina su tabella destinatari
//
        if ($idMail) {
            $icon['vediMail'] = "<span title=\"Vedi Mail\" class=\"ui-icon ui-icon-mail-closed\" style=\"display:inline-block;\"></span>";
        }

//
// Quando la mail è inviata e non sono ancora collegate le ricevute metto la X
//
        if (!$praMail_tab) {
            if ($idMail) {
                $icon['accDest'] = "<span title=\"Accettazione non Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                $icon['conDest'] = "<span title=\"Consegna non Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
            }
        }

//
//Mi scorro PRAMAIL per trovare l' accettazione e mettere i bottoni nel dettaglio del destinatario e le icone nella tabela
//
        foreach ($praMail_tab as $praMail_rec) {
            if ($praMail_rec['TIPORICEVUTA'] == emlMessage::PEC_TIPO_ACCETTAZIONE) {
                if ($tipo == "PASSO") {
                    $icon['accettazione'] = "<button id=\"praGestDestinatari_VediMailAcc\" class=\"ita-button ita-element-animate ui-corner-all ui-state-default\"
                                               title=\"Vedi Mail Accettazione\" name=\"praPasso_VediMailAcc\" type=\"button\">
                                              <div id=\"praPasso_acc_icon_left\" class=\"ita-button-element
                                              ita-button-icon-left ui-icon ui-icon-check\" style=\"\"></div>
                                         </button>$descAcc";
                    $icon['IDMAILACC'] = $praMail_rec['IDMAIL'];
                } else {
                    $icon['accettazione'] = "<span title=\"Accettazione Ricevuta\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>$descAcc";
                    $icon['accDest'] = "<span title=\"Accettazione Ricevuta\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
                }
                break;
            }
        }

//
//Mi scorro PRAMAIL per trovare la consegna e mettere i bottoni nel dettaglio del destinatario e le icone nella tabela. Se non c'è metto la X
//
        foreach ($praMail_tab as $praMail_rec) {
            if ($praMail_rec['TIPORICEVUTA'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                if ($tipo == "PASSO") {
                    $icon['consegna'] = "<button id=\"praGestDestinatari_VediMailCons\" class=\"ita-button ita-element-animate ui-corner-all ui-state-default\"
                                               title=\"Vedi Mail Consegna\" name=\"praPasso_VediMailCons\" type=\"button\">
                                              <div id=\"praPasso_cons_icon_left\" class=\"ita-button-element
                                              ita-button-icon-left ui-icon ui-icon-check\" style=\"\"></div>
                                         </button>$descCon";
                    $icon['IDMAILCON'] = $praMail_rec['IDMAIL'];
                } else {
                    $icon['consegna'] = "<span title=\"Avvenuta Consegna\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>$descCon";
                    $icon['conDest'] = "<span title=\"Avvenuta Consegna\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
                }
                break;
            } else {
                $icon['conDest'] = "<span title=\"Consegna non ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
            }
        }
        return $icon;
    }

    public function clearFileName($fileName) {
        $lettere = str_split($fileName);
        $newFileName = '';
        foreach ($lettere as $lettera) {
            if (strpos('\/:*?"<>|', $lettera)) {
                $lettera = '_';
            }
            $newFileName .= $lettera;
        }
        return $newFileName;
    }

    public function setClasseCaricatoPasso($archivio, $idMail, $gesnum = '', $propak = '') {
        if ($archivio) {
            $praMail_rec = $this->getPraMail($idMail);
            if ($gesnum) {
                $praMail_rec['GESNUM'] = $gesnum;
            }

            if ($propak) {
                $pracom_rec = $this->GetPracomP($propak);
                $praMail_rec['PROPAK'] = $propak;
                $praMail_rec['COMPAK'] = $propak;
                $praMail_rec['COMIDMAIL'] = $pracom_rec['COMIDMAIL'];
            }
            $praMail_rec['MAILSTATO'] = 'CARICATA';
            $nRows = ItaDB::DBUpdate($this->getPRAMDB(), 'PRAMAIL', 'ROWID', $praMail_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Record su PRAMAIL con id $idMail non aggiornato");
                return false;
            }
            include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
            $emlLib = new emlLib();
            $mailArchivio_rec = $emlLib->getMailArchivio($idMail);
            if (!$mailArchivio_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Record mail non trovato");
                return false;
            }
            include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
            $emlDbMailBox = new emlDbMailBox();
            $risultato1 = $emlDbMailBox->updateClassForRowId($mailArchivio_rec['ROWID'], '@SPORTELLO_CARICATO@');
            if ($risultato1 === false) {
                $this->setErrCode(-1);
                $this->setErrMessage("Record mail non aggiornato");
                return false;
            }
//
//Cancello la mail sul server dopo che il set classificazione è andato a buon fine
//
            $filent_rec = $this->GetFilent(23);
            if ($filent_rec["FILVAL"] == 1) {
                if (!$this->DeleteMailFromServer($idMail)) {
                    $this->setErrCode($this->getErrCode());
                    $this->setErrMessage($this->getErrMessage());
                }
            }
        } else {
//CANCELLAZIONE SOSPESA PER POTER RIUTILIZZARE PIU VOLTE LA STESA MAIL
//            $praMail_rec = $this->getPraMail($idMail);
//            $fileName = $this->SetDirectoryPratiche('', '', 'PEC') . $idMail . '.eml';
//            if (is_file($fileName)) {
//                if (!@unlink($fileName)) {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("File:" . $fileName . " non Eliminato");
//                    return false;
//                }
//            }
//            $nRows = ItaDB::DBDelete($this->getPRAMDB(), 'PRAMAIL', 'ROWID', $praMail_rec['ROWID']);
        }
        return true;
    }

    function GetPramailRecPratica($pratica) {
        $proges_rec = $this->GetProges($pratica);
        if ($proges_rec['GESPRA']) {
            $where = " AND ISFRONTOFFICE = 1 AND RICNUM = '" . $proges_rec['GESPRA'] . "'";
        } else {
            $where = " AND ISGENERIC = 1";
        }
        $sql = "SELECT * FROM PRAMAIL WHERE GESNUM = '$pratica' AND MAILSTATO='CARICATA' AND COMPAK='' AND PROPAK='' $where";
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    function GetPramailRecRichiesta($richiesta) {
        $sql = "SELECT * FROM PRAMAIL WHERE ISFRONTOFFICE = 1 AND RICNUM = '$richiesta' AND GESNUM=''";
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    function GetPramailRecIntegrazione($richiesta) {
        $sql = "SELECT * FROM PRAMAIL WHERE ISFRONTOFFICE = 1 AND ISINTEGRATION = 1 AND RICNUM = '$richiesta'";
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
    }

    function formatFileSize($a_bytes) {
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

    function CheckSostFile($gesnum, $passha2, $passha2sost, $returnType = "icon") {
        if ($passha2sost) {
//$pasdoc_rec = $this->GetPasdoc($passha2sost, "passha2");
            $pasdoc_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY LIKE '$gesnum%' AND PASSHA2 = '$passha2sost'", false);
            if ($pasdoc_rec) {
                if ($returnType == "icon") {
                    return "<span class=\"ita-icon ita-icon-arrow-green-dx-16x16\">Allegato in Sostituzione</span>";
                } else {
                    return "Il seguente file è in sostituzione del file <b>" . $pasdoc_rec["PASNAME"] . "</b>";
                }
            }
        } else {
//$pasdoc_rec = $this->GetPasdoc($passha2, "passha2sost");
            $pasdoc_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY LIKE '$gesnum%' AND PASSHA2SOST = '$passha2'", false);
            if ($pasdoc_rec) {
                if ($returnType == "icon") {
                    return "<span class=\"ita-icon ita-icon-arrow-red-sx-16x16\">Allegato Sostituito</span>";
                } else {
                    return "Il seguente file è stato sostituito dal file <b>" . $pasdoc_rec["PASNAME"] . "</b>";
                }
            }
        }
    }

    function setStatoSostFile($gesnum, $passha2sost) {
        if ($passha2sost) {
            $pasdoc_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY LIKE '$gesnum%' AND PASSHA2 = '$passha2sost'", false);
            if ($pasdoc_rec) {
                $pasdoc_rec['PASSTA'] = "S";
            }
        }

        if ($pasdoc_rec) {
            try {
                $nrow = ItaDB::DBUpdate($this->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }
        }
        return true;
    }

    function CreaComboTipiCampi($idCampo) {
        Out::select($idCampo, 1, "", "1", "Generico");
//Out::select($idCampo, 1, "AcroFieldsGrid", "0", "Griglia campi Modulo PDF");
        Out::select($idCampo, 1, "Sportello_Aggregato", "0", "Sportello Aggregato");
        Out::select($idCampo, 1, "Evento_Richiesta", "0", "Evento Richiesta");
        Out::select($idCampo, 1, "Denom_Fiera", "0", "Denominazione Fiera");
        Out::select($idCampo, 1, "DenominazioneImpresa", "0", "Denominazione Impresa");
        Out::select($idCampo, 1, "Comune_InsProduttivo", "0", "Comune Insediamento Produttivo");
        Out::select($idCampo, 1, "Indir_InsProduttivo", "0", "Indirizzo Insediamento Produttivo");
        Out::select($idCampo, 1, "Civico_InsProduttivo", "0", "N. Civico Insediamento Produttivo");
        Out::select($idCampo, 1, "Cap_InsProduttivo", "0", "Cap Insediamento Produttivo");
        Out::select($idCampo, 1, "Prov_InsProduttivo", "0", "Provincia Insediamento Produttivo");
        Out::select($idCampo, 1, "Codfis_InsProduttivo", "0", "Codice Fiscale Insediamento Produttivo");
        Out::select($idCampo, 1, "Codfis_Dichiarante", "0", "Codice Fiscale Dichiarante");
        Out::select($idCampo, 1, "Codfis_Anades", "0", "Codice Fiscale Anagrafica Soggetti");
        Out::select($idCampo, 1, "Email_InsProduttivo", "0", "E-mail Insediamento Produttivo");
        Out::select($idCampo, 1, "Foglio_catasto", "0", "N. Foglio Catasto");
        Out::select($idCampo, 1, "Sub_catasto", "0", "N. Subalterno Catasto");
        Out::select($idCampo, 1, "Particella_catasto", "0", "N. Particella Catasto");
        Out::select($idCampo, 1, "Richiesta_padre", "0", "N. Richiesta on-line da Integrare");
        Out::select($idCampo, 1, "Luogo_segnalazione", "0", "Luogo Segnalazione");
        Out::select($idCampo, 1, "Desc_segnalazione", "0", "Descrizione Segnalazione");
        Out::select($idCampo, 1, "Priorita_richiesta", "0", "Priorità Richiesta");
        Out::select($idCampo, 1, "Posteggi_fiera", "0", "Posteggi Fiera");
        Out::select($idCampo, 1, "Istituti", "0", "Istituti");
        Out::select($idCampo, 1, "Iscrivendi", "0", "Iscrivendi");
        Out::select($idCampo, 1, "IscrivendiSint", "0", "Iscrivendi (richiesta sintetica)");
        Out::select($idCampo, 1, "Servizi", "0", "Servizi");
        Out::select($idCampo, 1, "Percorsi_ferm", "0", "Percorsi Fermate");
        Out::select($idCampo, 1, "Tipo_pasto", "0", "Tipo Pasto");
        Out::select($idCampo, 1, "Tipo_segnalazione", "0", "Tipologia Segnalazione");
        Out::select($idCampo, 1, "Denom_EnteTerzo", "0", "Denominazione Ente Terzo");
        Out::select($idCampo, 1, "Fiscale_EnteTerzo", "0", "Fiscale Ente Terzo");
        Out::select($idCampo, 1, "Pec_EnteTerzo", "0", "Pec Ente Terzo");
        Out::select($idCampo, 1, "Referente_EnteTerzo", "0", "Referente Ente Terzo");
        Out::select($idCampo, 1, "descrizioneParere", "0", "Descrizione Parere");
        Out::select($idCampo, 1, "Richiesta_unica", "0", "N. Richiesta Principale a cui Accorpare");
        Out::select($idCampo, 1, "Denom_FieraBando", "0", "Denominazione Fiera Bando");
        Out::select($idCampo, 1, "Denom_FieraPBando", "0", "Denominazione Fiera Pluriennale Bando");
        Out::select($idCampo, 1, "Denom_MercatoBando", "0", "Denominazione Mercato Bando");
        Out::select($idCampo, 1, "Denom_PIBando", "0", "Posteggi Isolati Bando");
        Out::select($idCampo, 1, "Forma_Giuridica", "0", "Forma Giuridica");
        Out::select($idCampo, 1, "Carica", "0", "Carica");
        Out::select($idCampo, 1, "Qualifica", "0", "Qualifica");
        Out::select($idCampo, 1, "NomeLegale", "0", "Nome Legale Rappresentante");
        Out::select($idCampo, 1, "CognomeLegale", "0", "Cognome Legale Rappresentante");
        Out::select($idCampo, 1, "FiscaleLegale", "0", "Codice Fiscale Legale Rappresentante");
        Out::select($idCampo, 1, "Rich_padre_variante", "0", "N. Richiesta on-line da variare");
        Out::select($idCampo, 1, "TipoPermessoZTL", "0", "Tipo Permesso per la ZTL");
        Out::select($idCampo, 1, "RinnovoPermessoZTL", "0", "Rinnovo Permesso per la ZTL");
        Out::select($idCampo, 1, "VariaTargaZTL", "0", "Variazione targhe per la ZTL");
        Out::select($idCampo, 1, "DataManifestazione", "0", "Data Manifestazione");
        Out::select($idCampo, 1, "Scambio_Posto", "0", "Scambio Posto");
        Out::select($idCampo, 1, "Giustificazione", "0", "Giustificazione");
        Out::select($idCampo, 1, "Ruolo", "0", "Ruolo");
        Out::select($idCampo, 1, "Comune", "0", "Comune");
        Out::select($idCampo, 1, "Ricerca_Generica", "0", "Ricerca Generica");
        Out::select($idCampo, 1, "Tabella_Generica", "0", "Tabella Generica");
    }

    function CreaComboTipiInput($idCampo) {
        Out::select($idCampo, 1, "Text", "0", "Text");
        Out::select($idCampo, 1, "Data", "0", "Data");
        Out::select($idCampo, 1, "Time", "0", "Time");
        Out::select($idCampo, 1, "Importo", "0", "Importo");
        Out::select($idCampo, 1, "TextArea", "0", "Text Area");
        Out::select($idCampo, 1, "Select", "0", "Select");
        Out::select($idCampo, 1, "Password", "0", "Password");
        Out::select($idCampo, 1, "CheckBox", "0", "CheckBox");
        Out::select($idCampo, 1, "RadioGroup", "0", "RadioGroup");
        Out::select($idCampo, 1, "RadioButton", "0", "RadioButton");
        Out::select($idCampo, 1, "Html", "0", "Html");
        Out::select($idCampo, 1, "Hidden", "0", "Hidden");
        Out::select($idCampo, 1, "Button", "0", "Button");
        Out::select($idCampo, 1, "FileUpload", "0", "FileUpload");
    }

    function CreaComboTipiValida($idCampo) {
        Out::select($idCampo, 1, "", "1", "");
        Out::select($idCampo, 1, "email", "0", "e-mail");
        Out::select($idCampo, 1, "Numeri", "0", "Solo Numeri");
        Out::select($idCampo, 1, "Data", "0", "Data");
        Out::select($idCampo, 1, "Lettere", "0", "Solo Lettere");
        Out::select($idCampo, 1, "CodiceFiscale", "0", "Codice Fiscale");
        Out::select($idCampo, 1, "PartitaIva", "0", "Partita Iva");
        Out::select($idCampo, 1, "Importo", "0", "Importo");
        Out::select($idCampo, 1, "Iban", "0", "Iban");
        Out::select($idCampo, 1, "RegularExpression", "0", "Espressione Regolare");
    }

    function CreaComboTipiAzioniValida($idCampo) {
        Out::select($idCampo, 1, '0', '1', 'Error');
        Out::select($idCampo, 1, '1', '0', 'Warning');
    }

    function CreaComboTipiPosizione($idCampo) {
        Out::select($idCampo, 1, "Sinistra", "1", "Sinistra");
        Out::select($idCampo, 1, "Destra", "0", "Destra");
        Out::select($idCampo, 1, "Sopra", "0", "Sopra");
        Out::select($idCampo, 1, "Sotto", "0", "Sotto");
        Out::select($idCampo, 1, "Nascosta", "0", "Nascosta");
    }

    function CreaComboTipiHtmlPosition($idCampo) {
        Out::select($idCampo, 1, "", "1", "Seleziona la posizione");
        Out::select($idCampo, 1, "Inizio", "0", "Inizio Raccolta");
        Out::select($idCampo, 1, "Default", "0", "Nella Raccolta");
        Out::select($idCampo, 1, "Fine", "0", "Fine Raccolta");
    }

    function CreaComboTipiValueCheck($idCampo) {
        Out::select($idCampo, 1, "", "1", "Seleziona un valore");
        Out::select($idCampo, 1, "1/0", "0", "1/0");
        Out::select($idCampo, 1, "On/Off", "0", "on/off");
        Out::select($idCampo, 1, "Si/No", "0", "si/no");
    }

    function InvioMailAlProtocollo($dati, $tipo = "") {
        $this->accLib = new accLib();
        $this->devLib = new devLib();

//
//Controllo se sono valorizzati i parametri mail
//
        if (!$this->accLib->CheckParametriMail()) {
            Out::msgStop("ERRORE", "Attenzione parametri di invio mail mancanti o non corretti");
            return false;
        }

//
//Controllo se esiste il parametro mail
//
        $enc_config_rec = $this->devLib->getEnv_config('PROTOCOLLOMANUALE', 'codice', 'EMAILPROTOCOLLO', false);
        if (!$enc_config_rec) {
            Out::msgStop("ERRORE", "Attenzione parametro e-mail protocollo non configurato");
            return false;
        }

//
//Controllo se il paramentro mail è valorizzato
//
        if ($enc_config_rec['CONFIG'] == "") {
            Out::msgStop("ERRORE", "Attenzione parametro e-mail vuoto");
            return false;
        }
        $dati["valori"]["Email"] = $enc_config_rec['CONFIG'];

//
//Apro Model per Invio Mail
//
        $model = 'utiGestMail';
        $_POST = array();
        $_POST['tipo'] = 'InviaProtocollo';
        $_POST['valori'] = $dati['valori'];
        $_POST['allegati'] = $dati['allegatiProt'];
        $_POST['returnModel'] = $dati['returnModel'];
        $_POST['returnEvent'] = 'returnMail' . $tipo;
        $_POST['event'] = 'openform';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    function elaboraDatiAggiuntiviPagamento($pratica, $dataReg) {
        $dagpak_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT DISTINCT DAGPAK FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGKEY LIKE 'PRAIMPO_%'", true);
        $Filent_Rec_51 = $this->GetFilent(51);
        $i = 0;
        foreach ($dagpak_tab as $dagpak_rec) {
            $codice = $tariffa = $iuv = "";
            $i++;
            $proimpo_rec = array();
            $prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGPAK = '" . $dagpak_rec['DAGPAK'] . "' AND DAGKEY LIKE 'PRAIMPO_%'", true);
            foreach ($prodag_tab as $prodag_rec) {
                if ($prodag_rec['DAGKEY'] == "PRAIMPO_CODICE") {
                    $codice = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "PRAIMPO_TARIFFA") {
                    $tariffa = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "PRAIMPO_IUV") {
                    $iuv = $prodag_rec['DAGVAL'];
                }
            }
            if ($codice == "") {
                $codice = $Filent_Rec_51['FILDE1'];
            }
            $proimpo_rec['IMPONUM'] = $pratica;
            $proimpo_rec['IMPOPROG'] = $i;
            $proimpo_rec['IMPOCOD'] = $codice;
            $proimpo_rec['DATAREG'] = $dataReg;
            $proimpo_rec['IMPORTO'] = $tariffa;
            $proimpo_rec['IUV'] = $iuv;
            $proimpo_rec['PAGATO'] = $tariffa;
//
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PROIMPO", 'ROWID', $proimpo_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Pagamento Fallito.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento Pagamento Fallito." . $exc->getMessage());
                return false;
            }

            $proconciliazione_rec = array();
            $proconciliazione_rec['IMPONUM'] = $pratica;
            $proconciliazione_rec['IMPOPROG'] = $i;
            $proconciliazione_rec['DATAQUIETANZA'] = $dataReg;
            $proconciliazione_rec['CONCILIAZIONE'] = "P";
            $proconciliazione_rec['QUIETANZA'] = $Filent_Rec_51['FILVAL'];
            $proconciliazione_rec['SOMMAPAGATA'] = $tariffa;
            $proconciliazione_rec['NUMEROQUIETANZA'] = $iuv;
            $proconciliazione_rec['IUV'] = $iuv;
            $proconciliazione_rec['DATAINSERIMENTO'] = date("Ymd");
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PROCONCILIAZIONE", 'ROWID', $proconciliazione_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Quietanza Fallito.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento Quietanza Fallito." . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    function elaboraDatiLocalizzazioneIntervento($pratica, $dataReg) {
        $dagset_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT DISTINCT DAGSET FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGKEY LIKE 'INTER_%'", true);
        $i = 0;
        foreach ($dagset_tab as $dagset_rec) {
            $prodag_rec_via = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGKEY LIKE 'INTER_VIA' AND DAGTIP = 'Indir_InsProduttivo'", true);
            if ($prodag_rec_via) {
                continue;
            }
            $localita = $via = $civico = $cap = $provincia = "";
            $i++;
            $anades_rec = array();
            $prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGKEY LIKE 'INTER_%'", true);
            foreach ($prodag_tab as $prodag_rec) {
                if ($prodag_rec['DAGKEY'] == "INTER_LOCALITA") {
                    $localita = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "INTER_VIA") {
                    $via = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "INTER_CIV") {
                    $civico = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "INTER_CAP") {
                    $cap = $prodag_rec['DAGVAL'];
                }
                if ($prodag_rec['DAGKEY'] == "INTER_PROVINCIA") {
                    $provincia = $prodag_rec['DAGVAL'];
                }
            }
            $anades_rec['DESNUM'] = $pratica;
            $anades_rec['DESDRE'] = $dataReg;
            $anades_rec['DESCIT'] = $localita;
            $anades_rec['DESIND'] = $via;
            $anades_rec['DESCIV'] = $civico;
            $anades_rec['DESCAP'] = $cap;
            $anades_rec['DESPRO'] = $provincia;
            $basLib = new basLib();
            $comuni_rec = $basLib->getComuni(strtoupper($localita));
            $anades_rec['DESPRO'] = $comuni_rec['PROVIN'];
            $anades_rec['DESPAK'] = $prodag_rec['DAGPAK'];
            $anades_rec['DESDSET'] = $prodag_rec['DAGSET'];
            $anades_rec['DESRUO'] = praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'];
//
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "ANADES", 'ROWID', $anades_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Dati Localizzazione Intervento fallito.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento Dati Localizzazione Intervento Fallito." . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    function elaboraDatiAggiuntiviInsProduttivo($pratica, $dataReg = '', $dataMod = '') {
        $proges_rec = $this->GetProges($pratica);
        if ($proges_rec['GESSPA']) {
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            $localita = $Anaspa_rec['SPACOM'];
            $provincia = $Anaspa_rec['SPAPRO'];
            $cap = $Anaspa_rec['SPACAP'];
        } else {
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            $localita = $Anatsp_rec['TSPCOM'];
            $provincia = $Anatsp_rec['TSPPRO'];
            $cap = $Anatsp_rec['TSPCAP'];
        }

        $dagset_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT DISTINCT DAGSET FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGTIP = 'Indir_InsProduttivo'", true);
        foreach ($dagset_tab as $dagset_rec) {
            $anades_rec = array();
            $prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGTIP = 'Indir_InsProduttivo' AND DAGVAL<>''", true);
            $prodag_rec_denominazione = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGTIP = 'DenominazioneImpresa' AND DAGVAL<>''", false);
            $prodag_rec_fiscale = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGTIP = 'Codfis_InsProduttivo' AND DAGVAL<>''", false);
            //$prodag_rec_civico = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGTIP = 'Civico_InsProduttivo' AND DAGVAL<>''", false);
            $prodag_rec_civico = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$pratica' AND DAGSET = '" . $dagset_rec['DAGSET'] . "' AND DAGVAL<>'' AND (DAGTIP = 'Civico_InsProduttivo' OR DAGKEY = 'INTER_CIV')", false);
            foreach ($prodag_tab as $prodag_rec) {
                $anades_rec['DESNUM'] = $pratica;
                $anades_rec['DESDRE'] = $dataReg;
                $anades_rec['DESDCH'] = $dataMod;
                $anades_rec['DESNOM'] = $prodag_rec_denominazione['DAGVAL'];
                if (strlen($prodag_rec_fiscale['DAGVAL']) == 11) {
                    $anades_rec['DESPIVA'] = $prodag_rec_fiscale['DAGVAL'];
                }
                if (strlen($prodag_rec_fiscale['DAGVAL']) == 16) {
                    $anades_rec['DESFIS'] = $prodag_rec_fiscale['DAGVAL'];
                }

                $anades_rec['DESCIT'] = $localita;
                $anades_rec['DESIND'] = $prodag_rec['DAGVAL'];
                $anades_rec['DESCIV'] = $prodag_rec_civico['DAGVAL'];
                $anades_rec['DESCAP'] = $cap;
                $anades_rec['DESPRO'] = $provincia;
                $basLib = new basLib();
                $comuni_rec = $basLib->getComuni(strtoupper($localita));
                $anades_rec['DESPRO'] = $comuni_rec['PROVIN'];
                $anades_rec['DESPAK'] = $prodag_rec['DAGPAK'];
                $anades_rec['DESDSET'] = $prodag_rec['DAGSET'];
                $anades_rec['DESRUO'] = praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'];
                $anades_rec['DESNUMISCRIZIONE'] = "DAINTERNO";
                //
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "ANADES", 'ROWID', $anades_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Inserimento Dati Localizzazione Intervento fallito.");
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Dati Localizzazione Intervento Fallito." . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    function elaboraDatiGoBid($procedimento) {
        $progesRec = $this->GetProges($procedimento, 'codice');
        if ($progesRec['GESCODPROC'] != '') {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';
            $praGobid = new praGobidManager();
            $resultRest = $praGobid->output($progesRec['GESCODPROC']);
            if ($resultRest) {
//
//  Dati anagrafici Impresa
//
                $anades_rec = array();
                $anades_rec['DESNUM'] = $progesRec['GESNUM'];
                $anades_rec['DESNOM'] = $resultRest['IMPRESA']['ragioneSociale'];
                $anades_rec['DESFIS'] = $resultRest['IMPRESA']['codiceFiscale'];
                $anades_rec['DESIND'] = $resultRest['IMPRESA']['indirizzo'];
                $anades_rec['DESCAP'] = $resultRest['IMPRESA']['cap'];
                $anades_rec['DESCIT'] = $resultRest['IMPRESA']['comune'];
                $anades_rec['DESPRO'] = $resultRest['IMPRESA']['provincia'];
                $anades_rec['DESRUO'] = praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUOCOD'];
                $anades_rec['DESPEC'] = $resultRest['IMPRESA']['email'];
                $anades_rec['DESTEL'] = $resultRest['IMPRESA']['telefono'];
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "ANADES", 'ROWID', $anades_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Inserimento Esibente Fallito.");
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Esibente Fallito." . $exc->getMessage());
                    return false;
                }
//
//  Dati anagrafici Curatore
//
                $anades_rec = array();
                $anades_rec['DESNUM'] = $progesRec['GESNUM'];
                $anades_rec['DESNOM'] = $resultRest['CURATORE']['cognome'] . ' ' . $resultRest['CURATORE']['titolo'] . ' ' . $resultRest['CURATORE']['nome'];
                $anades_rec['DESFIS'] = '';
                $anades_rec['DESIND'] = $resultRest['CURATORE']['indirizzo'];
                $anades_rec['DESCAP'] = $resultRest['CURATORE']['cap'];
                $anades_rec['DESCIT'] = $resultRest['CURATORE']['comune'];
                $anades_rec['DESPRO'] = $resultRest['CURATORE']['provincia'];
                $anades_rec['DESRUO'] = praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUOCOD'];
                $anades_rec['DESPEC'] = $resultRest['CURATORE']['email'];
                $anades_rec['DESTEL'] = $resultRest['CURATORE']['telefono'];
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "ANADES", 'ROWID', $anades_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Inserimento Esibente Fallito.");
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento Esibente Fallito." . $exc->getMessage());
                    return false;
                }
//
//  Controllo cartella temporanea per allegati
//
                if (!is_dir(itaLib::getAppsTempPath())) {
                    if (!itaLib::createAppsTempPath()) {
                        Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                        return false;
                    }
                }
//
//  Allegati
//
                foreach ($resultRest['DOCUMENTI'] as $key_tipo => $TipoDocumento) {
                    foreach ($TipoDocumento as $key => $documento) {
                        if ($documento['file'] != '') {
                            $result = $praGobid->getCallFile($documento['file'], '');
                            if ($result) {
                                $contentDisposition = $result['headers'][0]['Content-disposition'];
                                preg_match('/filename="([^"]*)"/', $contentDisposition, $pregMatches);
                                $nomeFileOrig = $pregMatches[1];
                                $ext = pathinfo($nomeFileOrig, PATHINFO_EXTENSION);
                                $file = strtolower($key_tipo) . '_' . ($key + 1) . '.' . $ext;
                                $nomeFileDest = itaLib::getAppsTempPath() . '/' . $file;
                                file_put_contents($nomeFileDest, $result['content']);
                                $allegato['FILENAME'] = $file;
                                $allegato['DATAFILE'] = $nomeFileDest;
                                $this->ribaltaAllegatiGobid($progesRec['GESNUM'], $allegato);
                            }
                        }
                    }
                }
                $resultJson = $praGobid->output($progesRec['GESCODPROC'], true);
                $nomeFileOrig = 'info_' . $progesRec['GESCODPROC'] . '.json';
                $nomeFileDest = itaLib::getAppsTempPath() . '/' . $nomeFileOrig;
                file_put_contents($nomeFileDest, $resultJson);
                $allegato['FILENAME'] = $nomeFileOrig;
                $allegato['DATAFILE'] = $nomeFileDest;
                $this->ribaltaAllegatiGobid($progesRec['GESNUM'], $allegato);
//
                itaLib::deleteAppsTempPath();
//
//  Inerimento dati aggiuntivi
//
//                $codice_cur = '';
//                if ($resultRest['CURATORE']['codice_cur'] != '') {
//                    $codice_cur = $resultRest['CURATORE']['codice_cur'];
//                } else {
//                    if ($resultRest['CURATORE']['codice'] != '') {
//                        $codice_cur = $resultRest['CURATORE']['codice'];
//                    }
//                }
//                $datiAggPratica[] = array(
//                    "ROWID" => 0,
//                    "DAGSEQ" => 0,
//                    "DAGNUM" => $progesRec['GESNUM'],
//                    "DAGPAK" => $progesRec['GESNUM'],
//                    "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_FORMAGIURIDICA',
//                    "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_FORMAGIURIDICA',
//                    "DAGDES" => 'Forma Giuridica Impresa',
//                    "DAGVAL" => $resultRest['IMPRESA']['formaGiuridica']
//                );
//                $datiAggPratica[] = array(
//                    "ROWID" => 0,
//                    "DAGSEQ" => 0,
//                    "DAGNUM" => $progesRec['GESNUM'],
//                    "DAGPAK" => $progesRec['GESNUM'],
//                    "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_CATEGORIA',
//                    "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_CATEGORIA',
//                    "DAGDES" => 'Categoria Impresa',
//                    "DAGVAL" => $resultRest['IMPRESA']['categoria']
//                );
//                $datiAggPratica[] = array(
//                    "ROWID" => 0,
//                    "DAGSEQ" => 0,
//                    "DAGNUM" => $progesRec['GESNUM'],
//                    "DAGPAK" => $progesRec['GESNUM'],
//                    "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_DESCRIZIONE',
//                    "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_DESCRIZIONE',
//                    "DAGDES" => 'Descrizione Impresa',
//                    "DAGVAL" => $resultRest['IMPRESA']['descrizione']
//                );
//                $datiAggPratica[] = array(
//                    "ROWID" => 0,
//                    "DAGSEQ" => 0,
//                    "DAGNUM" => $progesRec['GESNUM'],
//                    "DAGPAK" => $progesRec['GESNUM'],
//                    "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUODES']) . '_CODICE_CUR',
//                    "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUODES']) . '_CODICE_CUR',
//                    "DAGDES" => 'Codice CUR',
//                    "DAGVAL" => $codice_cur
//                );

                $datiAggPratica = array();
                foreach ($resultRest['IMPRESA'] as $key => $value) {
                    $datiAggPratica[] = array(
                        "ROWID" => 0,
                        "DAGSEQ" => 0,
                        "DAGNUM" => $progesRec['GESNUM'],
                        "DAGPAK" => $progesRec['GESNUM'],
                        "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_' . strtoupper($key),
                        "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUODES']) . '_' . strtoupper($key),
                        "DAGDES" => $key,
                        "DAGVAL" => $resultRest['IMPRESA'][$key]
                    );
                }
                foreach ($resultRest['CURATORE'] as $key => $value) {
                    $datiAggPratica[] = array(
                        "ROWID" => 0,
                        "DAGSEQ" => 0,
                        "DAGNUM" => $progesRec['GESNUM'],
                        "DAGPAK" => $progesRec['GESNUM'],
                        "DAGKEY" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUODES']) . '_' . strtoupper($key),
                        "DAGALIAS" => strtoupper(praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUODES']) . '_' . strtoupper($key),
                        "DAGDES" => $key,
                        "DAGVAL" => $resultRest['CURATORE'][$key]
                    );
                }
                $seq = 10;
                foreach ($datiAggPratica as $datiAgg) {
                    if ($datiAgg['DAGVAL'] != '') {
                        $datiAgg['DAGSEQ'] = $seq;
                        $seq = $seq + 10;
                        try {
                            $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRODAG", 'ROWID', $datiAgg);
                            if ($nrow != 1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Errore Inserimento dato aggiuntivo " . $datiAgg['DAGKEY']);
                                return false;
                            }
                        } catch (Exception $exc) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Errore Inserimento dato aggiuntivo " . $datiAgg['DAGKEY'] . ' ' . $exc->getMessage());
                            return false;
                        }
                    }
                }
            }
        }
    }

    function elaboraDatiAggiuntiviSoggetti($pratica, $dataReg = '', $dataMod = '') {
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php');
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php');
        $campiBase = praRuolo::$SUBJECT_BASE_FIELDS;
        $Anades_map = array();

        /*
         * Ciclo storico fatto per convenzione prefisso dati aggiuntivi ruolo
         */

        foreach (praRuolo::$SISTEM_SUBJECT_ROLES as $rolePrefix => $role) {
//
//Query per dati aggiuntivi di passo
//
            $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGALIAS AS DAGALIAS,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGSET AS DAGSET,                
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL,
                PROPAS.PRODAT,
                PROPAS.PRODOW,
                PROPAS.PROUPL                
            FROM
                PRODAG
            LEFT OUTER JOIN
                PROPAS ON PROPAS.PROPAK=PRODAG.DAGPAK
            LEFT OUTER JOIN
                PRACLT ON PROPAS.PROCLT = PRACLT.CLTCOD
            WHERE
                DAGNUM='" . $pratica . "'
            AND
                PROPAS.PRODOW=0
            AND
                (PROPAS.PROUPL=1 OR PROPAS.PROMLT=1 OR PROPAS.PRODAT=1)
            AND
                PRACLT.CLTOPEFO <> '" . praFunzionePassi::FUN_FO_ANA_SOGGETTO . "'
            AND 
                PRODAG.DAGKEY LIKE '" . $rolePrefix . "\_%'";
            $Prodag_tab_passi = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
//
//Query per i soli dati aggiuntivi di pratica (ESIBENTE)
//
            $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGALIAS AS DAGALIAS,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGSET AS DAGSET,                
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL
            FROM
                PRODAG
            WHERE
                DAGNUM='" . $pratica . "'
            AND
                DAGPAK='" . $pratica . "'
            AND 
                PRODAG.DAGKEY LIKE '" . $rolePrefix . "\_%'";
            $Prodag_tab_pratica = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);

            $Prodag_tab = array_merge($Prodag_tab_passi, $Prodag_tab_pratica);

            if ($Prodag_tab) {
                foreach ($Prodag_tab as $keyDag => $Prodag_rec) {
                    $dagPakKey = $Prodag_rec['DAGPAK'];
                    $dataSetKey = $Prodag_rec['DAGSET'];
                    list($baseName, $index) = explode("-", $Prodag_rec['DAGKEY']);
                    if ($index === null) {
                        $index = '';
                    }
                    list($prefisso, $campo, $tipo) = explode("_", $baseName);
                    switch ($tipo) {
                        case 'DATA':
                            if ($Prodag_rec['DAGVAL']) {
                                if (strlen($Prodag_rec['DAGVAL']) == 10) {
                                    $Prodag_rec['DAGVAL'] = substr($Prodag_rec['DAGVAL'], 6, 4) . substr($Prodag_rec['DAGVAL'], 3, 2) . substr($Prodag_rec['DAGVAL'], 0, 2);
                                } elseif (strlen($Prodag_rec['DAGVAL']) == 8) {
                                    $Prodag_rec['DAGVAL'] = substr($Prodag_rec['DAGVAL'], 4, 4) . substr($Prodag_rec['DAGVAL'], 2, 2) . substr($Prodag_rec['DAGVAL'], 0, 2);
                                }
                            }
                            break;
                        default:
                            break;
                    }

                    $tipo = ($tipo) ? "_" . $tipo : "";
                    if ($Prodag_rec['DAGVAL']) {
                        if (!isset($Anades_map[$dagPakKey])) {
                            $Anades_map[$dagPakKey] = array();
                        }
                        if (!isset($Anades_map[$dagPakKey][$dataSetKey])) {
                            $Anades_map[$dagPakKey][$dataSetKey] = array();
                        }
                        if (!isset($Anades_map[$dagPakKey][$dataSetKey][$rolePrefix])) {
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix] = array();
                        }
                        if (!isset($Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index])) {
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index] = array();
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDRE'] = $dataReg;
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDCH'] = $dataMod;
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESRUO'] = $role['RUOCOD'];
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESPAK'] = $dagPakKey;
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDSET'] = $dataSetKey;
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDIDX'] = $index;
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['ANADESDAG'] = array();
                        }

                        if ($campiBase[$campo . $tipo]) {
                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index][$campiBase[$campo . $tipo]] = $Prodag_rec['DAGVAL'];
                        } else {
                            $Anadesdag_rec = array(
                                'DESKEY' => $campo . $tipo,
                                'DESVAL' => $Prodag_rec['DAGVAL']
                            );

                            $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['ANADESDAG'][] = $Anadesdag_rec;
                        }
                    }
                }
            }
        }

        /*
         * Qui passi tipizzati per ANAGRAFICA SOGGETTI
         */
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';

        $sql_tipizzati = "
            SELECT
                PROPAS.*          
            FROM
                PROPAS
            LEFT OUTER JOIN
                PRACLT ON PROPAS.PROCLT=PRACLT.CLTCOD
            WHERE
                PRONUM='" . $pratica . "' AND PRACLT.CLTOPEFO='" . praFunzionePassi::FUN_FO_ANA_SOGGETTO . "'
            AND
                PROPAS.PRODOW=0
            AND
                (PROPAS.PROUPL=1 OR PROPAS.PROMLT=1 OR PROPAS.PRODAT=1)";

        $Propas_tipizzati_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql_tipizzati, true);
        foreach ($Propas_tipizzati_tab as $Propas_tipizzati_rec) {
            $Praclt_rec = $this->GetPraclt($Propas_tipizzati_rec['PROCLT']);
            if (!$Praclt_rec) {
                continue;
            }
            $cltmetaArr = unserialize($Praclt_rec['CLTMETA']);
            $fun_ana_soggettoMeta = $cltmetaArr['METAOPEFO'];
            $rolePrefix = $fun_ana_soggettoMeta['PREFISSO_CAMPI'];
            $aliasSoggettoMeta = array_flip($fun_ana_soggettoMeta);

            /*
             * Cerco il campo RUOLO per ogni DAGSET
             */

            $sql_campo_ruolo = "SELECT DAGVAL, DAGSET FROM PRODAG
                                WHERE
                                    DAGPAK = '{$Propas_tipizzati_rec['PROPAK']}'
                                AND 
                                    DAGKEY = '{$fun_ana_soggettoMeta['CAMPO_RUOLO']}'";

            $roles = array();
            $Prodag_ruolo_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql_campo_ruolo);
            foreach ($Prodag_ruolo_tab as $Prodag_ruolo_rec) {
                $roles[$Prodag_ruolo_rec['DAGSET']] = array('RUOCOD' => $Prodag_ruolo_rec['DAGVAL']);
            }

            $sql_dati_aggiuntivi = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGALIAS AS DAGALIAS,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGSET AS DAGSET,                
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL               
            FROM
                PRODAG
            WHERE
                DAGPAK='" . $Propas_tipizzati_rec['PROPAK'] . "'
            AND 
                DAGKEY LIKE '" . $rolePrefix . "\_%'";
            $Prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql_dati_aggiuntivi, true);

            /*
             * 
             * Completamento mappa
             * 
             */

            foreach ($Prodag_tab as $keyDag => $Prodag_rec) {
                $role = $roles[$Prodag_rec['DAGSET']];
                $dagPakKey = $Prodag_rec['DAGPAK'];
                $dataSetKey = $Prodag_rec['DAGSET'];
                list($baseName, $index) = explode("-", $Prodag_rec['DAGKEY']);
                if ($index === null) {
                    $index = '';
                }
                list($prefisso, $campo, $exploded_tipo) = explode("_", $baseName);

                $tipo = ($exploded_tipo) ? '_' . $exploded_tipo : '';
                if (isset($aliasSoggettoMeta[$campo . $tipo]) && substr($aliasSoggettoMeta[$campo . $tipo], 0, 6) === 'ALIAS_') {
                    list(, $campo, $exploded_tipo) = explode('_', $aliasSoggettoMeta[$campo . $tipo]);
                    $tipo = ($exploded_tipo) ? '_' . $exploded_tipo : '';
                }

                switch ($exploded_tipo) {
                    case 'DATA':
                        if ($Prodag_rec['DAGVAL']) {
                            if (strlen($Prodag_rec['DAGVAL']) == 10) {
                                $Prodag_rec['DAGVAL'] = substr($Prodag_rec['DAGVAL'], 6, 4) . substr($Prodag_rec['DAGVAL'], 3, 2) . substr($Prodag_rec['DAGVAL'], 0, 2);
                            } elseif (strlen($Prodag_rec['DAGVAL']) == 8) {
                                $Prodag_rec['DAGVAL'] = substr($Prodag_rec['DAGVAL'], 4, 4) . substr($Prodag_rec['DAGVAL'], 2, 2) . substr($Prodag_rec['DAGVAL'], 0, 2);
                            }
                        }
                        break;
                    default:
                        break;
                }

                if ($Prodag_rec['DAGVAL']) {
                    if (!isset($Anades_map[$dagPakKey])) {
                        $Anades_map[$dagPakKey] = array();
                    }
                    if (!isset($Anades_map[$dagPakKey][$dataSetKey])) {
                        $Anades_map[$dagPakKey][$dataSetKey] = array();
                    }
                    if (!isset($Anades_map[$dagPakKey][$dataSetKey][$rolePrefix])) {
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix] = array();
                    }
                    if (!isset($Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index])) {
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index] = array();
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDRE'] = $dataReg;
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDCH'] = $dataMod;
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESRUO'] = $role['RUOCOD'];
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESPAK'] = $dagPakKey;
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDSET'] = $dataSetKey;
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['DESDIDX'] = $index;
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['ANADESDAG'] = array();
                    }

                    if ($campiBase[$campo . $tipo]) {
                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index][$campiBase[$campo . $tipo]] = $Prodag_rec['DAGVAL'];
                    } else {
                        $Anadesdag_rec = array(
                            'DESKEY' => $campo . $tipo,
                            'DESVAL' => $Prodag_rec['DAGVAL']
                        );

                        $Anades_map[$dagPakKey][$dataSetKey][$rolePrefix][$index]['ANADESDAG'][] = $Anadesdag_rec;
                    }
                }
            }
        }


        if (!$Anades_map) {
            return true;
        }


        foreach ($Anades_map as $dagPakKey => $Anades_dataset) {
            foreach ($Anades_dataset as $dataSetKey => $Anades_forRole) {
                foreach ($Anades_forRole as $subjectRole => $Anades_tab) {
//
// Analisi campi automatici e
// Inserimanto di ANADES
//
                    foreach ($Anades_tab as $subjectIndex => $Anades_rec) {
                        if ($Anades_rec) {
                            $Anades_impresa = $Anades_map[$dagPakKey][$dataSetKey]["IMPRESA"][$subjectIndex];
                            $Anades_impresaind = $Anades_map[$dagPakKey][$dataSetKey]["IMPRESAINDIVIDUALE"][$subjectIndex];
                            $Anades_dichiarante = $Anades_map[$dagPakKey][$dataSetKey]["DICHIARANTE"][$subjectIndex];
//
                            $Anades_rec['DESNUM'] = $pratica;
                            if ($Anades_rec['DESNOM'] == "") {
                                $Anades_rec['DESNOM'] = ($Anades_rec['DESRAGSOC']) ? $Anades_rec['DESRAGSOC'] : $Anades_rec['DESCOGNOME'] . " " . $Anades_rec['DESNOME'];
                            }
                            if ($subjectRole == 'IMPRESAINDIVIDUALE') {
//                                $Anades_impresa = $Anades_map[$dagPakKey][$dataSetKey]["IMPRESA"][$subjectIndex];
//                                $Anades_dichiarante = $Anades_map[$dagPakKey][$dataSetKey]["DICHIARANTE"][$subjectIndex];
                                if ($Anades_dichiarante) {
                                    $Anades_rec['DESCOGNOME'] = $Anades_dichiarante['DESCOGNOME'];
                                    $Anades_rec['DESNOME'] = $Anades_dichiarante['DESNOME'];
                                    if ($Anades_rec['DESNOM'] == "") {
                                        $Anades_rec['DESNOM'] = $Anades_dichiarante['DESCOGNOME'] . " " . $Anades_dichiarante['DESNOME'];
                                    }
                                }
                                foreach ($campiBase as $key => $field) {
                                    if (!$Anades_rec[$field]) {
                                        $Anades_rec[$field] = $Anades_impresa[$field];
                                    }
                                }
                            }
                        }
                        $Anades_tab[$subjectIndex] = $Anades_rec;
                    }

                    foreach ($Anades_tab as $subjectIndex => $Anades_rec) {
                        /*
                         * Se è presente impresaIndividuale, il soggetto impresa non viene registrato.
                         * Fatto per ovviare al problema dei modelli con la sezione dei dati unica e che in fase di acquisizione
                         * entrava il soggetto impresa con tutti i dati e il soggetto impresaInd solo con la denominazione.
                         */
                        if ($Anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode("IMPRESA") && $Anades_impresaind) {
                            continue;
                        }
                        if ($Anades_rec) {
                            /*
                             * Se è presente impresaIndividuale, il soggetto impresa non viene registrato.
                             * Fatto per ovviare al problema dei modelli con la sezione dei dati unica e che in fase di acquisizione
                             * entrava il soggetto impresa con tutti i dati e il soggetto impresaInd solo con la denominazione.
                             */
                            if ($Anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode("IMPRESA") && $Anades_impresaind) {
                                continue;
                            }

                            $Anadesdeg_tab = $Anades_rec['ANADESDAG'];
                            unset($Anades_rec['ANADESDAG']);

                            try {
                                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "ANADES", 'ROWID', $Anades_rec);
                                if ($nrow != 1) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage("Inserimento Soggetto fallito.");
                                    return false;
                                }

                                $Anades_last_id = ItaDB::DBLastId($this->getPRAMDB());
                            } catch (Exception $exc) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Inserimento Soggetto fallito." . $exc->getMessage());
                                return false;
                            }

                            foreach ($Anadesdeg_tab as $Anadesdag_rec) {
                                try {
                                    $Anadesdag_rec['ANADES_ROWID'] = $Anades_last_id;

                                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'ANADESDAG', 'ROW_ID', $Anadesdag_rec);
                                    if ($nrow != 1) {
                                        $this->setErrCode(-1);
                                        $this->setErrMessage("Inserimento Dato Aggiuntivo Soggetto fallito.");
                                        return false;
                                    }
                                } catch (Exception $exc) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage("Inserimento Dato Aggiuntivo Soggetto fallito." . $exc->getMessage());
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

    function valorizzaDatiAggiuntivi($pratica, $dati) {
        $prodag_tab = $this->GetProdag($pratica, 'dagpak', true);
        $arrIndexDag = array_column($prodag_tab, 'DAGKEY');
        foreach ($dati['PRODAG_REC'] as $value) {
            $prodag_index = array_search($value['DAGKEY'], $arrIndexDag);
            if ($prodag_index === false) {
                continue;
            }
            if (!isset($prodag_tab[$prodag_index])) {
                continue;
            }
            $prodag_tab[$prodag_index]['DAGVAL'] = $value['DAGVAL'];
            try {
                $nrow = ItaDB::DBUpdate($this->getPRAMDB(), 'PRODAG', 'ROWID', $prodag_tab[$prodag_index]);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Valorizzzazione dati agguntivi fallita.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Valorizzzazione dati agguntivi fallita." . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    function elaboraDatiAggiuntiviCatasto($pratica) {

        /*
         * IMMOBILI DATI CATASTALI
         *
         */

        $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGALIAS AS DAGALIAS,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGSET AS DAGSET,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL,
                PROPAS.PRODAT,
                PROPAS.PRODOW,
                PROPAS.PROUPL                
            FROM
                PRODAG
            LEFT OUTER JOIN
                PROPAS ON PROPAS.PROPAK=PRODAG.DAGPAK
            WHERE
                DAGNUM='" . $pratica . "'
            AND
                PROPAS.PRODOW=0
            AND
                (PROPAS.PROUPL=1 OR PROPAS.PRODAT=1)
            AND (
                PRODAG.DAGKEY LIKE 'IMM\_TIPO%' OR            
                PRODAG.DAGKEY LIKE 'IMM\_SEZIONE%' OR
                PRODAG.DAGKEY LIKE 'IMM\_FOGLIO%' OR                
                PRODAG.DAGKEY LIKE 'IMM\_PARTICELLA%' OR                
                PRODAG.DAGKEY LIKE 'IMM\_SUBALTERNO%' OR
                PRODAG.DAGKEY LIKE 'IMM\_CTRRET%' OR
                PRODAG.DAGKEY LIKE 'IMM\_CTRMSG%'
                )";


        $Prodag_passo_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        /*
         * IMMOBILI DATI CATASTALI DI PRATICA
         *
         */
        $sql_pratica = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGALIAS AS DAGALIAS,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGSET AS DAGSET,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL
            FROM
                PRODAG
            WHERE
                PRODAG.DAGNUM='" . $pratica . "'
            AND
                PRODAG.DAGPAK='" . $pratica . "'
            AND (
                PRODAG.DAGKEY LIKE 'IMM\_TIPO%' OR
                PRODAG.DAGKEY LIKE 'IMM\_SEZIONE%' OR
                PRODAG.DAGKEY LIKE 'IMM\_FOGLIO%' OR
                PRODAG.DAGKEY LIKE 'IMM\_PARTICELLA%' OR
                PRODAG.DAGKEY LIKE 'IMM\_SUBALTERNO%' OR
                PRODAG.DAGKEY LIKE 'IMM\_CTRRET%' OR
                PRODAG.DAGKEY LIKE 'IMM\_CTRMSG%'

                )";


        $Prodag_pratica_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql_pratica, true);

        $Prodag_tab = array_merge($Prodag_passo_tab, $Prodag_pratica_tab);

        $campiPad = array(
            "TIPO" => false,
            "SEZIONE" => 3,
            "FOGLIO" => 4,
            "PARTICELLA" => 5,
            "SUBALTERNO" => 4
        );

        $Praimm_tab = array();
        if ($Prodag_tab) {
            foreach ($Prodag_tab as $Prodag_rec) {
                $dagPakKey = $Prodag_rec['DAGPAK'];
                $dataSetKey = $Prodag_rec['DAGSET'];
                $arr_tmp = explode("_", $Prodag_rec['DAGKEY']);
                list($tipo, $campo, $indice) = $arr_tmp;
                if ($indice === null) {
                    $indice = '';
                }
                if ($Prodag_rec['DAGVAL'] != "") {
                    if (!isset($Praimm_tab[$dagPakKey])) {
                        $Praimm_tab[$dagPakKey] = array();
                    }
                    if (!isset($Praimm_tab[$dagPakKey][$dataSetKey])) {
                        $Praimm_tab[$dagPakKey][$dataSetKey] = array();
                    }

                    $Praimm_tab[$dagPakKey][$dataSetKey][$indice]['PRONUM'] = $pratica;
                    $Praimm_tab[$dagPakKey][$dataSetKey][$indice]['SEQUENZA'] = $indice * 10;
                    if ($campiPad[$campo]) {
                        $Praimm_tab[$dagPakKey][$dataSetKey][$indice][$campo] = str_pad($Prodag_rec['DAGVAL'], $campiPad[$campo], "0", STR_PAD_LEFT);
                    } else {
                        $Praimm_tab[$dagPakKey][$dataSetKey][$indice][$campo] = $Prodag_rec['DAGVAL'];
                    }
                }
            }
        }

        if ($Praimm_tab) {
            if ($this->checkExistCatasto()) {
                include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';
                $catLib = new catLib();
                $cataDB = $catLib->getCATADB();
            }
            foreach ($Praimm_tab as $dagPakKey => $Praimm_dataset) {
                foreach ($Praimm_dataset as $dataSetKey => $Praimm_forIndex) {
                    foreach ($Praimm_forIndex as $Praimm_rec) {
                        if ($cataDB != false) {
                            $legame_rec = ItaDB::DBSQLSelect($cataDB, "SELECT * FROM LEGAME WHERE FOGLIO='" . $Praimm_rec['FOGLIO'] . "' AND NUMERO='" . $Praimm_rec['PARTICELLA'] . "' AND SUB='" . $Praimm_rec['SUBALTERNO'] . "'", false);
                            if ($legame_rec) {
                                $Praimm_rec['CODICE'] = $legame_rec['IMMOBILE'];
                            } else {
                                $legame_rec = ItaDB::DBSQLSelect($cataDB, "SELECT * FROM LEGAME WHERE FOGLIO='" . $Praimm_rec['FOGLIO'] . "' AND NUMERO='" . $Praimm_rec['PARTICELLA'] . "'", false);
                                if ($legame_rec) {
                                    $Praimm_rec['CODICE'] = $legame_rec['IMMOBILE'];
                                }
                            }
                        }
                        $ok_insert = false;
                        foreach ($Praimm_rec as $value) {
                            if ($value) {
                                $ok_insert = true;
                                break;
                            }
                        }
                        if ($ok_insert) {
                            try {
                                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRAIMM", 'ROWID', $Praimm_rec);
                                if ($nrow != 1) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage("Inserimento dati immobile fallito.");
                                    return false;
                                }
                            } catch (Exception $exc) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Inserimento dati immobile fallito." . $exc->getMessage());
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function checkExistCatasto() {
        try {
            $cataDB = ItaDB::DBOpen('CATA');
            $record = ItaDB::DBSQLSelect($cataDB, "SHOW TABLES FROM " . $cataDB->getDB() . " LIKE 'LEGAME'");
        } catch (Exception $exc) {
            App::log($exc->getMessage());
        }
        if ($cataDB == "") {
            return false;
        } else {
            if (!$record) {
                return false;
            }
        }
        return true;
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

    public function VisualizzaFirme($file, $fileOriginale, $segnatura = "", $rowidPasdoc = 0) {
        $_POST = array();
        $_POST['segnatura'] = $segnatura;
        $_POST['rowidPasdoc'] = $rowidPasdoc;
        itaLib::openForm('utiP7m');
        $model = itaModel::getInstance('utiP7m', 'utiP7m');
        $model->setEvent("openform");
        $model->setFile($file);
        $model->setFileOriginale($fileOriginale);
        //$model->setShowPreview(true);
        $model->parseEvent();
    }

    public function GetHtmlcancellaPratica($proges_rec) {
        if ($proges_rec['GESTSP']) {
            $anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
        }
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
        }
        $anapra_rec = $this->GetAnapra($proges_rec['GESPRO']);
        $anades_tab = $this->GetAnades($proges_rec['GESNUM'], "codice", true);

        $html = "<span style=\"font-size:1.4em;color:red;\"><b>Controllare Attentamente il riepilogo: il Bottone conferma cancellerà la pratica.</b></span>";
        $html .= "<div>";
        $html .= "<div>";
        $html .= "<br><span style=\"font-size:1.4em;color:red;text-decoration:underline;\"><b>Dati Principali</b></span>";
        $html .= "<br><span style=\"font-size:1.1em;font-weight:bold;\">N. Pratica: " . substr($proges_rec['GESNUM'], 5) . "/" . substr($proges_rec['GESNUM'], 0, 4) . "</span><br>";
        $html .= "<span style=\"font-size:1em;font-weight:bold;\">Procedimento: " . $anapra_rec['PRADES__1'] . "</span><br>";
        if ($proges_rec['GESPRA']) {
            $html .= "<span style=\"font-size:1.1em;font-weight:bold;\">N. Richiesta on-line: " . substr($proges_rec['GESPRA'], 5) . "/" . substr($proges_rec['GESPRA'], 0, 4) . "</span>";
        }
        $html .= "<br>";
        $html .= "</div>";
        $html .= "<div>";
        if ($anatsp_rec || $anaspa_rec) {
            $html .= "<br><span style=\"font-size:1.4em;color:red;text-decoration:underline;\"><b>Dati Sportello</b></span><br>";
            if ($anatsp_rec) {
                $html .= "<span style=\"font-size:1em;font-weight:bold;\">Sportello on-line: " . $anatsp_rec['TSPCOD'] . " - " . $anatsp_rec['TSPDES'] . "</span><br>";
            }
            if ($anaspa_rec) {
                $html .= "<span style=\"font-size:1.1em;font-weight:bold;\">Sportello aggregato: " . $anaspa_rec['SPACOD'] . " - " . $anaspa_rec['SPADES'] . "</span>";
            }
        }
        $html .= "</div>";
        $html .= "<div>";
        $html .= "<br><span style=\"font-size:1.4em;color:red;text-decoration:underline;\"><b>Soggetti</b></span><br>";
        foreach ($anades_tab as $anades_rec) {
            $desc = $anades_rec['DESNOM'];
            if ($anades_rec['DESRUO'] == praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']) {
                $desc = $anades_rec['DESIND'] . " " . $anades_rec['DESCIV'];
            }
            $anaruo_rec = $this->GetAnaruo($anades_rec['DESRUO']);
            $html .= "<span style=\"font-size:1.1em;font-weight:bold;\">" . $anaruo_rec['RUODES'] . ": $desc</span><br>";
        }
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }

    function getLoginDaNomres($nomres) {
        try {
            $ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        return ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTEANA__3 = '" . $nomres . "'", false);
    }

    function GetGiorno($giorno) {
        switch ($giorno) {
            case 'Mon':
                return 'Lunedi';
                break;
            case 'Tue':
                return 'Martedi';
                break;
            case 'Wed':
                return 'Mercoledi';
                break;
            case 'Thu':
                return 'Giovedi';
                break;
            case 'Fry':
                return 'Venerdi';
                break;
            case 'Sat':
                return 'Sabato';
                break;
            case 'Sun':
                return 'Domenica';
                break;
        }
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

    function AggiungiDocumentoCommercio($propas_rec, $doc, $ditta = "") {
        $baseName = pathinfo($doc["PRONAME"], PATHINFO_FILENAME);
        $dirName = pathinfo($doc["FILEPATH"], PATHINFO_DIRNAME);

        /*
         * Copio allegato da path COMMERCIO a path SUAP
         */
        $passoPath = $this->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", true, $ditta);
        $randName = md5(rand() * time());
        if (!copy($dirName . "/" . $baseName . ".pdf", $passoPath . "/" . $randName . ".pdf")) {
            Out::msgStop("Attenzione!!!", "Errore nell'importazione del testo nei Fascicoli Elettronici");
            return false;
        }

        /*
         * Inserisco nuovo record allegato su PASDOC
         */
        $PRAM_DB = $this->getPRAMDB();
        $pasdoc_rec = array();
        $pasdoc_rec['PASKEY'] = $propas_rec['PROPAK'];
        $pasdoc_rec['PASFIL'] = $randName . ".pdf";
        $pasdoc_rec['PASLNK'] = "allegato://$randName.pdf";
        $pasdoc_rec['PASNOT'] = $doc['TESTO'];
        $pasdoc_rec['PASCLA'] = "COMMERCIO";
        $pasdoc_rec['PASNAME'] = $doc['TESTO'] . ".pdf";
        $pasdoc_rec['PASSHA2'] = hash_file('sha256', $doc["FILEPATH"]);
        try {
            $nrowIns = ItaDB::DBInsert($PRAM_DB, "PASDOC", 'ROWID', $pasdoc_rec);
            if ($nrowIns != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }

        /*
         * Aggiorno variabile PROALL (array serializzato) con nuovo alleagato
         */
        $pasdoc_rec['FILEORIG'] = $doc['TESTO'] . ".pdf";
        $pasdoc_rec['FILENAME'] = $randName . ".pdf";
        $pasdoc_rec['FILEPATH'] = $passoPath . "/" . $randName . ".pdf";
        if ($propas_rec['PROALL']) {
            $allegati = unserialize($propas_rec['PROALL']);
        } else {
            $allegati = array();
        }
        $allegati[] = $pasdoc_rec;
        $propas_rec['PROALL'] = serialize($allegati);
        try {
            $nrow = ItaDB::DBUpdate($PRAM_DB, "PROPAS", "ROWID", $propas_rec);
            if ($nrow == -1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }
        return true;
    }

    public function Diff_Date_toGiorni($data1, $data2, $abs = true) {
        $giorni = strtotime($data1) / (86400) - strtotime($data2) / (86400);
        if ($abs) {
            return round(abs($giorni));
        } else {
            return round($giorni);
        }
    }

    function GetIconStatoCom($propak) {
        $noInviata = $siInviata = array();
        $iconStato = "";
        $praMitDest_tab = $this->GetPraDestinatari($propak, "codice", true);
        $invitaDaWs = false;

//
//Per ogni passo controllo se ci sono i destinatari
//
        if ($praMitDest_tab) {
            foreach ($praMitDest_tab as $praMitDest_rec) {
                if ($praMitDest_rec['IDMAIL']) {
//
//Controllo se sono arrivate tutte le ricevute di acc e cons
//
                    $ricevuteIcon = $this->GetIconAccettazioneConsegna($praMitDest_rec['IDMAIL'], $praMitDest_rec['KEYPASSO']);
                    if (isset($ricevuteIcon['accettazione'])) {
                        $siAccRicevuta[] = $praMitDest_rec['IDMAIL'];
                    } else {
                        $noAccRicevuta[] = $praMitDest_rec['IDMAIL'];
                    }
                    if (isset($ricevuteIcon['consegna'])) {
                        $siConsRicevuta[] = $praMitDest_rec['IDMAIL'];
                    } else {
                        $noConsRicevuta[] = $praMitDest_rec['IDMAIL'];
                    }

                    if (count($siAccRicevuta) == count($praMitDest_tab)) {
                        $iconaAcc = "<span title=\"Tutte le Accettazioni Ricevute\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    } else {
                        if (count($noAccRicevuta) == count($praMitDest_tab)) {
                            $iconaAcc = "<span title=\"Nessuna Accettazione Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                        }
                    }
                    if (count($siConsRicevuta) == count($praMitDest_tab)) {
                        $iconaCons = "<span title=\"Tutte le Consegne Ricevute\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    } else {
                        if (count($noConsRicevuta) == count($praMitDest_tab)) {
                            $iconaCons = "<span title=\"Nessuna Consegna Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                        }
                    }
                }

//
//Controllo se la mail è stata inviata a quel destinatario tramite IDMAIL e creo 2 array
//siInviata[] dove ci sono i destinatari a cui è stata inviata e noInviata[] dove ci sono quelli a cui non è stata inviata
//
                if ($praMitDest_rec['IDMAIL'] == "") {
                    $noInviata[] = $praMitDest_rec;
                } else {
                    $siInviata[] = $praMitDest_rec;
                }

// Controllo se c'è comunicazione in arrivo e metto doppia icona
                $praMitArrivo_rec = $this->GetPraArrivo($propak, "codice");
                if ($praMitArrivo_rec && $praMitArrivo_rec['DATAINVIO']) {
                    $iconStato = "<span class=\"ita-icon ita-icon-stock-mail-32x32\" style=\"display:inline-block;\">Comunicazione Inviata e risposta ricevuta</span>$alertIcon$iconaAcc$iconaCons";
                }
            }

            /*
             * Verifico se c'è stato l'invio tramite ws
             */
            $pracomP_rec = $this->GetPracomP($propak);
            $Metadati = unserialize($pracomP_rec['COMMETA']);
            if ($Metadati['DatiProtocollazione']['idMail']) {
                $invitaDaWs = true;
            }

            if ($siInviata == $praMitDest_tab || $invitaDaWs) {
                $iconStato = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-chiusagray-24x24\">Comunicazione Inviata a tutti i destinatari</span>$iconaAcc$iconaCons";
            } else {
                if ($noInviata == $praMitDest_tab) {
                    $iconStato = "<span class=\"ita-icon ita-icon-apertagreen-24x24\">Comunicazione non ancora Inviata a nessun Destinatario</span>";
                } else {
                    $alertIcon = "<span title=\"Comunicazione da Inviare ancora a " . count($noInviata) . " Destinatari\" class=\"ita-icon ita-icon-yellow-alert-24x24\" style=\"display:inline-block;\"></span>";
                    $iconStato = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-chiusagray-24x24\">Comunicazione Inviata Parzialmente</span>$alertIcon";
                }
            }

//            $praMitArrivo_rec = $this->GetPraArrivo($propak, "codice");
//            if ($praMitArrivo_rec && $praMitArrivo_rec['DATAINVIO']) {
//                $iconStato = "<span class=\"ita-icon ita-icon-stock-mail-32x32\" style=\"display:inline-block;\">Comunicazione Inviata e risposta ricevuta</span>$alertIcon$iconaAcc$iconaCons";
//            }
        } else {
            $praMitArrivo_rec = $this->GetPraArrivo($propak, "codice");
            if ($praMitArrivo_rec) {
                if ($praMitArrivo_rec['DATAINVIO']) {
                    $iconStato = "<span title=\"Comunicazione in Arrivo Registrata\" class=\"ita-icon ita-icon-chiusagray-24x24\" style=\"display:inline-block;\"></span>$iconaAcc$iconaCons";
                } else {
                    $iconStato = "<span title=\"Comunicazione in Arrivo inizializzata\" class=\"ita-icon ita-icon-apertagreen-24x24\"></span>$iconaAcc$iconaCons";
                }
            }
        }

        return $iconStato;
    }

    public function getRicevutaPECBreve() {
        $Filent_rec_12 = $this->GetFilent(12);
        return $Filent_rec_12['FILDE1'];
    }

    public function getBaseSMTPAccount() {
        $Filent_rec_12 = $this->GetFilent(12);
        return $Filent_rec_12['FILVAL'];
    }

    public function getEmlMailBox($sportello = 0) {
        /* @var $mailBox emlMailBox */
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
//
// Account base gestione fascicoli
//
        $baseAccount = $this->getBaseSMTPAccount();
        $baseRicevutaBreve = $this->getRicevutaPECBreve();
        $workAccount = $baseAccount;
//
// Account per Sportello
//
        if ($sportello) {
            $Anatsp_rec = $this->GetAnatsp($sportello);
            if ($Anatsp_rec && $Anatsp_rec['TSPPEC']) {
                $workAccount = $Anatsp_rec['TSPPEC'];
            }
        }
        if ($workAccount) {
            $mailBox = emlMailBox::getInstance($workAccount);
        }
        if (!$mailBox) {
            $mailBox = emlMailBox::getUserAccountInstance();
        }
        if (!$mailBox) {
            return false;
        }

//
// Se attivata ricevuta breve su parametri fasciolo elettronico si applica forzatamente e tutti gli account
//
        if ($baseRicevutaBreve == 1) {
            $mailBox->setCustomHeader(emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA, emlLib::CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE);
        }
        return $mailBox;
    }

    public function AddGiorniToData($data, $giorni = 0) {
        $risData = date('Ymd', strtotime("+$giorni day", strtotime($data)));
        return $risData;
    }

    function CalcolaDataScadenza($giorniScadenza, $dataInvio = "", $dataRiscontro = "") {

        if ($giorniScadenza) {
            if ($dataRiscontro != "" || $dataInvio != "") {
                if ($dataRiscontro && $dataInvio)
                    $da_ta = $dataRiscontro;
                if ($dataRiscontro == "" && $dataInvio)
                    $da_ta = $dataInvio;
                if ($dataRiscontro && $dataInvio == "")
                    $da_ta = $dataRiscontro;
                $scadenzaRiscontro = $this->AddGiorniToData($da_ta, $giorniScadenza);
            } else {
                $scadenzaRiscontro = "";
            }
            return $scadenzaRiscontro;
        } else {
            return "";
        }
    }

    function AggiornaMailPreferita($medcod, $rowidMail, $emailPreferita) {
        $anamed_rec = $this->proLib->GetAnamed($medcod);
        if ($anamed_rec) {
            $anamed_rec['MEDEMA'] = $emailPreferita;
//$update_Info = 'Oggetto: Aggiorno Mail ' . $anamed_rec['MEDEMA'] . " su utente " . $anamed_rec['MEDCOD'];
            try {
                ItaDB::DBUpdate($this->proLib->getPROTDB(), "ANAMED", "ROWID", $anamed_rec);
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return false;
            }
        }
        $Tabdag_tab_mail = $this->proLib->GetTabdag("ANMED", "chiave", $anamed_rec['ROWID'], "EMAIL", "", true);
        $Tabdag_tab_pec = $this->proLib->GetTabdag("ANAMED", "chiave", $anamed_rec['ROWID'], "EMAILPEC", "", true);
        $Tabdag_tab = array_merge($Tabdag_tab_pec, $Tabdag_tab_mail);
        foreach ($Tabdag_tab as $key => $Tabdag_rec) {
            if ($Tabdag_rec['ROWID'] == $rowidMail) {
                $Tabdag_tab[$key]["TDAGSEQ"] = 1;
                break;
            }
        }
        $Tabdag_tab_ord = $this->proLib->array_sort($Tabdag_tab, "TDAGSEQ");
        $this->proLib->RiordinaSequenzeMailDB($Tabdag_tab_ord);
        return true;
    }

    public function DatiImpresa($gesnum) {
        $Result_rec = array();
        $Anades_rec_impresa = $this->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESA']['RUOCOD']);
        if ($Anades_rec_impresa) {
            $Result_rec["IMPRESA"] = $Anades_rec_impresa['DESNOM'];
            $Result_rec["FISCALE"] = $Anades_rec_impresa['DESFIS'];
            $Result_rec["INDIRIZZO"] = $Anades_rec_impresa['DESIND'];
            $Result_rec["CIVICO"] = $Anades_rec_impresa['DESCIV'];
        } else {
            $Anades_rec_impresa_ind = $this->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['IMPRESAINDIVIDUALE']['RUOCOD']);
            if ($Anades_rec_impresa_ind) {
                if ($Anades_rec_impresa_ind['DESNOM']) {
                    $impresa = $Anades_rec_impresa_ind['DESNOM'];
                    if (trim($impresa) == "") {
                        $impresa = $Anades_rec_impresa_ind['DESCOGNOME'] . " " . $Anades_rec_impresa_ind['DESNOME'];
                    }
                }
                $Result_rec["IMPRESA"] = $impresa;
                $Result_rec["INDIRIZZO"] = $Anades_rec_impresa_ind['DESIND'];
                $Result_rec["CIVICO"] = $Anades_rec_impresa_ind['DESCIV'];
                $Result_rec["FISCALE"] = $Anades_rec_impresa_ind['DESPIVA'];
                if (!$Result_rec["FISCALE"]) {
                    $Result_rec["FISCALE"] = $Anades_rec_impresa_ind['DESFIS'];
                }
            } else {
                $Anades_rec_dichiarante = $this->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD']);
                if ($Anades_rec_dichiarante) {
                    $Result_rec["IMPRESA"] = $Anades_rec_dichiarante['DESNOM'];
                    $Result_rec["INDIRIZZO"] = $Anades_rec_dichiarante['DESIND'];
                    $Result_rec["CIVICO"] = $Anades_rec_dichiarante['DESCIV'];
                    $Result_rec["FISCALE"] = $Anades_rec_dichiarante['DESPIVA'];
                    if (!$Result_rec["FISCALE"]) {
                        $Result_rec["FISCALE"] = $Anades_rec_dichiarante['DESFIS'];
                    }
                } else {
                    $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "'
                                  AND (DAGTIP = 'DenominazioneImpresa' OR 
                                       DAGTIP = 'Codfis_InsProduttivo' OR
                                       DAGTIP = 'Indir_InsProduttivo' OR
                                       DAGTIP = 'Civico_InsProduttivo' OR
                                       DAGKEY = 'DENOMINAZIONE_IMPRESA' OR
                                       DAGKEY = 'CF_IMPRESA')", true);
                    if ($Prodag_tab) {
                        foreach ($Prodag_tab as $Prodag_rec) {
                            if ($Prodag_rec['DAGKEY'] == "DENOMINAZIONE_IMPRESA" || $Prodag_rec['DAGTIP'] == "DenominazioneImpresa")
                                $Result_rec["IMPRESA"] = $Prodag_rec['DAGVAL'];
                            if ($Prodag_rec['DAGKEY'] == "CF_IMPRESA" || $Prodag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                                $Result_rec["FISCALE"] = $Prodag_rec['DAGVAL'];
                            if ($Prodag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA" || $Prodag_rec['DAGKEY'] == "IMPRESA_SEDEVIA" || $Prodag_rec['DAGTIP'] == "Indir_InsProduttivo")
                                $Indirizzo = $Prodag_rec['DAGVAL'];
                            if ($Prodag_rec['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO" || $Prodag_rec['DAGKEY'] == "IMPRESA_SEDECIVICO" || $Prodag_rec['DAGTIP'] == "Civico_InsProduttivo")
                                $Civico = $Prodag_rec['DAGVAL'];
                            $Result_rec["INDIRIZZO"] = $Indirizzo;
                            $Result_rec["CIVICO"] = $Civico;
                        }
                    }
                }
            }
        }
        return $Result_rec;
    }

    public function DatiSoggettoRuolo($gesnum, $ruolo) {
        $Result_rec = array();
        $Anades_rec_curatore = $this->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['CURATORE']['RUOCOD']);
        if ($Anades_rec_curatore) {
            $Result_rec["DENOMINAZIONE"] = $Anades_rec_curatore['DESNOM'];
            $Result_rec["FISCALE"] = $Anades_rec_curatore['DESFIS'];
            $Result_rec["INDIRIZZO"] = $Anades_rec_curatore['DESIND'] . " " . $Anades_rec_curatore['DESCIV'];
            $Result_rec["TELEFONO"] = $Anades_rec_curatore['DESTEL'];
        }
        return $Result_rec;
    }

    public function AltriDatiImpresa($gesnum) {
        $altriDati = array();
        $Prodag_recMqTot = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "' AND DAGKEY = 'MODELLO_ESERCIZIO_MQTOTALI' AND DAGVAL<>''", false);
        if ($Prodag_recMqTot) {
            $altriDati['MQTOTALI'] = $Prodag_recMqTot['DAGVAL'];
        }
        $Prodag_recMqAlim = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "' AND DAGKEY = 'MODELLO_ESERCIZIO_SUPERFALIMENT' AND DAGVAL<>''", false);
        if ($Prodag_recMqAlim) {
            $altriDati['MQALIM'] = $Prodag_recMqAlim['DAGVAL'];
        }
        $Prodag_recMqNoAlim = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $gesnum . "' AND DAGKEY = 'MODELLO_ESERCIZIO_SUPERFNOALIMENT' AND DAGVAL<>''", false);
        if ($Prodag_recMqNoAlim) {
            $altriDati['MQNOALIM'] = $Prodag_recMqNoAlim['DAGVAL'];
        }
        return $altriDati;
    }

    public function SincDataScadenza($tipo, $codice, $dataSca, $pragio, $giorni, $dataReg, $sinc = false) {
        switch ($tipo) {
            case "PRATICA":
                $proges_rec = $this->GetProges($codice);
                $scadenzaDaRec = $proges_rec['GESDSC'];
                break;
            case "PASSO":
                $propas_rec = $this->GetPropas($codice, "propak");
                $scadenzaDaRec = $propas_rec['PRODSC'];
                break;
        }
        $scadenza = $dataSca;
        if ($giorni != 0) {
            $data1 = strtotime($dataReg);
            $data2 = $giorni * 3600 * 24;
            $somma = $data1 + $data2;
            $scadenza = date('Ymd', $somma);
        } else {
            if ($pragio != 0) {
                $data1 = strtotime($dataReg);
                $data2 = $pragio * 3600 * 24;
                $somma = $data1 + $data2;
                $scadenza = date('Ymd', $somma);
                $giorni = $pragio;
            } else {
                $scadenza = "";
                $giorni = 0;
            }
        }
        if ($dataSca && $scadenzaDaRec != $dataSca) {
            $scadenza = $dataSca;
            $data1 = strtotime($dataReg);
            $data2 = strtotime($dataSca);
            $giorni = intval((($data2 - $data1) / 3600) / 24);
        }

        switch ($tipo) {
            case "PRATICA":
//                Out::valore("praGest_PROGES[GESDSC]", $scadenza);
//                Out::valore("praGest_PROGES[GESGIO]", $giorni);
//
                if ($sinc == true) {
                    $proges_rec['GESDSC'] = $scadenza;
                    $proges_rec['GESGIO'] = $giorni;
                    try {
                        ItaDB::DBUpdate($this->getPRAMDB(), "PROGES", "ROWID", $proges_rec);
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Sincronizza data scadenza pratica.' . $e->getMessage());
                        return false;
                    }
//$update_Info = "Oggetto sincronizzazione data scadenza pratica numero: " . $proges_rec['GESNUM'];
                }
                break;
            case "PASSO":
//                Out::valore("praPasso_PROPAS[PRODSC]", $scadenza);
//                Out::valore("praPasso_PROPAS[PROGIO]", $giorni);
//
                if ($sinc == true) {
                    $propas_rec['PRODSC'] = $scadenza;
                    $propas_rec['PROGIO'] = $giorni;
                    try {
                        ItaDB::DBUpdate($this->getPRAMDB(), "PROPAS", "ROWID", $propas_rec);
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Sincronizza data scadenza passo.' . $e->getMessage());
                        return false;
                    }
//$update_Info = "Oggetto sincronizzazione data scadenza del passo " . $propas_rec['PROPAK'] . " e sequenza " . $propas_rec['PROSEQ'] . " della pratica numero: " . $propas_rec['PRONUM'];
                }
                break;
        }
        return array("SCADENZA" => $scadenza, "GIORNI" => $giorni);
    }

    function sincCalendar($tipo, $codice, $dataScadenza, $idCalendar = "") {
        if ($dataScadenza) {
            switch ($tipo) {
                case "PRATICA":
                    $proges_rec = $this->GetProges($codice);
                    $titEvento = "Fascicolo N. " . substr($proges_rec['GESNUM'], 4) . "/" . substr($proges_rec['GESNUM'], 0, 4);
                    $rowid = $proges_rec['ROWID'];
                    $classe = "SUAP_PRATICA";
                    $CodResp = $proges_rec['GESRES'];
                    break;
                case "PASSO":
                    $propas_rec = $this->GetPropas($codice, "propak");
                    $proges_rec = $this->GetProges($propas_rec['PRONUM']);
                    $titEvento = "Scadenza Passo Seq. " . $propas_rec['PROSEQ'];
                    $rowid = $propas_rec['ROWID'];
                    $classe = "PASSI_SUAP";
                    $CodResp = $propas_rec['PRORPA'];
                    break;
            }
            $Filent_Rec_13 = $this->GetFilent(13); //ogni quanto il promemoria
            $Filent_Rec_14 = $this->GetFilent(14); //inizio e durata promemoria
//            if ($Filent_Rec_14['FILVAL'] == "" || $Filent_Rec_14['FILDE1'] == "") {
//                return "Impossibile sincronizzare il calendario.<br>Compilare i parametri relativi al calendario nei parametri generali.";
//            }
            if ($Filent_Rec_13['FILDE1'] == "") {
                $Filent_Rec_13['FILDE1'] = "60";
            }
            if ($Filent_Rec_13['FILVAL'] == "") {
                $Filent_Rec_13['FILVAL'] = "30";
            }
            if ($Filent_Rec_14['FILDE1'] == "") {
                $Filent_Rec_14['FILDE1'] = "1";
            }
            if ($Filent_Rec_14['FILDE2'] == "") {
                $Filent_Rec_14['FILDE2'] = "3600";
            }
            if ($Filent_Rec_14['FILVAL'] == "") {
                $Filent_Rec_14['FILVAL'] = "08:00";
            }
            $oraInizio = str_replace(":", "", $Filent_Rec_14['FILVAL']) . "00";
            $somma = strtotime($oraInizio) + intval(trim($Filent_Rec_14['FILDE1'])) * intval(trim($Filent_Rec_14['FILDE2']));
            $oraFineEvento = date('His', $somma);
            $descEvento = $this->GetDescEvento($proges_rec, $propas_rec);
            include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
            $envLibCalendar = new envLibCalendar();
            $event_tab = $envLibCalendar->getAppEvents($classe, $rowid);
            if ($event_tab) {
                foreach ($event_tab as $event_rec) {
                    $accLib = new accLib();
                    $Utenti_rec = $accLib->GetUtenti($CodResp, "uteana3");
                    if (!$envLibCalendar->updateEventApp($event_rec['ROWID'], $titEvento, $descEvento, $dataScadenza . $oraInizio . "00", $dataScadenza . $oraFineEvento, false, "", "notifica", $Filent_Rec_13['FILVAL'], $Filent_Rec_13['FILDE1'], $Utenti_rec['UTECOD'], $idCalendar)) {
                        return $envLibCalendar->getErrMessage();
                    }
                }
            } else {
                if (!$envLibCalendar->insertEventApp($titEvento, $descEvento, $dataScadenza . $oraInizio . "00", $dataScadenza . $oraFineEvento, false, $classe, $rowid, "", "notifica", $Filent_Rec_13['FILVAL'], $Filent_Rec_13['FILDE1'], false, $idCalendar)) {
                    return $envLibCalendar->getErrMessage();
                }
            }
        }
    }

    public function GetDescEvento($proges_rec, $propas_rec) {
        $Anapra_rec = $this->GetAnapra($proges_rec['GESPRO']);
        $datiInsProd = $this->DatiImpresa($proges_rec['GESNUM']);
        if ($propas_rec) {
            $scadenza = substr($propas_rec['PRODSC'], 6, 2) . "/" . substr($propas_rec['PRODSC'], 4, 2) . "/" . substr($propas_rec['PRODSC'], 0, 4);
            $DataApertura = "Data Apertura Passo: " . substr($propas_rec['PROINI'], 6, 2) . "/" . substr($propas_rec['PROINI'], 4, 2) . "/" . substr($propas_rec['PROINI'], 0, 4) . "<br>";
            $DescPasso = "Descrizione Passo: " . $propas_rec['PRODPA'] . "<br>";
            if ($propas_rec['PROFIN'])
                $DataChiusura = "Data Chiusura Passo: " . substr($propas_rec['PROFIN'], 6, 2) . "/" . substr($propas_rec['PROFIN'], 4, 2) . "/" . substr($propas_rec['PROFIN'], 0, 4) . "<br>";
            $pracomP_rec = $this->GetPracomP($propas_rec['PROPAK']);
            if ($pracomP_rec['COMPRT'])
                $protocolloP = "N. Protocollo Partenza: " . substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4) . "<br>";
            if ($pracomP_rec['COMIDDOC'])
                $protocolloP = "Id Documento Partenza: " . $pracomP_rec['COMIDDOC'] . " del " . substr($pracomP_rec['COMDATADOC'], 6, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 4, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 0, 4) . "<br>";
            $pracomA_rec = $this->GetPracomA($propas_rec['PROPAK']);
            if ($pracomA_rec['COMPRT'])
                $protocolloA = "Protocollo Arrivo: " . substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4) . "<br>";
        } else {
            $scadenza = substr($proges_rec['GESDSC'], 6, 2) . "/" . substr($proges_rec['GESDSC'], 4, 2) . "/" . substr($proges_rec['GESDSC'], 0, 4);
            $protocollo = "Protocollo: " . substr($proges_rec['GESNPR'], 4) . "/" . substr($proges_rec['GESNPR'], 0, 4) . "<br>";
        }

        $descEvento = "Fascicolo N. " . substr($proges_rec['GESNUM'], 4) . "/" . substr($proges_rec['GESNUM'], 0, 4) . "<br>";
        $descEvento .= "Richiesta on-line N. " . substr($proges_rec['GESPRA'], 4) . "/" . substr($proges_rec['GESPRA'], 0, 4) . "<br>";
        $descEvento .= $DescPasso;
        $descEvento .= "Scadenza: " . $scadenza . "<br>";
        $descEvento .= "Impresa: " . $datiInsProd['IMPRESA'] . "<br>";
        $descEvento .= "Cod. Fisc./P. Iva: " . $datiInsProd['FISCALE'] . "<br>";
        $descEvento .= "Procedimento: " . $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "<br>";
        $descEvento .= "Data Registrazione Pratica: " . substr($proges_rec['GESDRE'], 6, 2) . "/" . substr($proges_rec['GESDRE'], 4, 2) . "/" . substr($proges_rec['GESDRE'], 0, 4) . "<br>";
        $descEvento .= "Data Ricezione Pratica: " . substr($proges_rec['GESDRI'], 6, 2) . "/" . substr($proges_rec['GESDRI'], 4, 2) . "/" . substr($proges_rec['GESDRI'], 0, 4) . "<br>";
        $descEvento .= $DataApertura;
        $descEvento .= $DataChiusura;
        $descEvento .= $protocollo;
        $descEvento .= $protocolloP;
        $descEvento .= $protocolloA;
        return $descEvento;
    }

    function GetStatoAllegati($passta) {
        switch ($passta) {
            case "":
                $stato = "";
                break;
            case "C":
                $stato = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"Da Controllare\"><span class=\"ita-icon ita-icon-bullet-orange-16x16\">Da Controllare</span></span></div>";
                break;
            case "V":
                $stato = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"Valido\"><span class=\"ita-icon ita-icon-bullet-green-16x16\">Da Controllare</span></span></div>";
                break;
            case "N":
                $stato = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"Non Valido\"><span class=\"ita-icon ita-icon-bullet-red-16x16\">Da Controllare</span></span></div>";
                break;
            case "S":
                $stato = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"Sostituito\"><span class=\"ita-icon ita-icon-bullet-yellow-16x16\">Da Controllare</span></span></div>";
                break;
            case "NP":
                $stato = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"Non Presentato\"><span class=\"ita-icon ita-icon-bullet-grey-16x16\">Da Controllare</span></span></div>";
                break;
        }
        return $stato;
    }

    function CheckUsagePassoTemplate($codice, $msg, $update = false, $form = "") {
        $Itepas_rec_template = $this->GetItepas($codice, "itekey");
        $Itepas_tab_usage = $this->GetItepas($codice, "templatekey", true);
        if ($Itepas_tab_usage) {
            $table = '<table id="tabletemplate">';
            $table .= "<tr>";
            $table .= '<th>Procedimento</th>';
            $table .= '<th>Passo</th>';
            $table .= "</tr>";
            $table .= "<tbody>";
            foreach ($Itepas_tab_usage as $Itepas_rec) {
                $table .= "<tr>";
                $table .= "<td>";
                $Anapra_rec = $this->GetAnapra($Itepas_rec['ITECOD']);
                $table .= $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];
                $table .= "</td>";
                $table .= "<td>";
                $table .= $Itepas_rec['ITESEQ'] . " - " . $Itepas_rec['ITEDES'];
                $table .= "</td>";
                $table .= "</tr>";
            }
            $table .= '</tbody>';
            $table .= '</table>';
            $messaggio = "<span style=\"font-weight:bold;\">Il passo " . $Itepas_rec_template['ITESEQ'] . " - " . $Itepas_rec_template['ITEDES'] . " è utilizzato nei seguenti procedimenti:</span>";
            if ($msg)
                $messaggio = $msg;
            if ($update) {
                Out::msgQuestion("ATTENZIONE!", "<br>$messaggio<br>$table", array(
                    'F8-Annulla' => array('id' => $form . '_AnnullaUdateTemplate', 'model' => $form, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $form . '_goDettaglio', 'model' => $form, 'shortCut' => "f5")
                        )
                );
            } else {
//Out::msgInfo("ATTENZIONE!", "<span>Impossibile cancellare il passo " . $Itepas_rec_template['ITESEQ'] . " - " . $Itepas_rec_template['ITEDES'] . " perche è utilizzato nei seguenti procedimenti:</span><br>$table");
                Out::msgInfo("ATTENZIONE!", "<br>$messaggio<br>$table");
            }
            Out::codice('tableToGrid("#tabletemplate", {});');
            return false;
        }
        return true;
    }

    public function creaComboCondizioni($nomeCampo) {
        Out::select($nomeCampo, 1, "uguale", "1", "Uguale a");
        Out::select($nomeCampo, 1, "diverso", "0", "Diverso da");
        Out::select($nomeCampo, 1, "maggiore", "0", "Maggiore a");
        Out::select($nomeCampo, 1, "minore", "0", "Minore a");
        Out::select($nomeCampo, 1, "maggiore-uguale", "0", "Maggiore/Uguale a");
        Out::select($nomeCampo, 1, "minore-uguale", "0", "Minore/Uguale a");
    }

    public function GetAnpdocDaMaster($Pram_db, $codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ANPDOC WHERE ANPKEY = '$codice'";
        } else {
            $sql = "SELECT * FROM ANPDOC WHERE ROWID = '$codice'";
        }
        return ItaDB::DBSQLSelect($Pram_db, $sql, $multi);
    }

    public function GetItereqDaMaster($Pram_db, $codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ITEREQ WHERE ITEPRA = '$codice'";
        } else {
            $sql = "SELECT * FROM ITEREQ WHERE ROWID = '$codice'";
        }
        return ItaDB::DBSQLSelect($Pram_db, $sql, $multi);
    }

    public function GetItenorDaMaster($Pram_db, $codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ITENOR WHERE ITEPRA = '$codice'";
        } else {
            $sql = "SELECT * FROM ITENOR WHERE ROWID = '$codice'";
        }
        return ItaDB::DBSQLSelect($Pram_db, $sql, $multi);
    }

    public function GetIteevtDaMaster($Pram_db, $codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM ITEEVT WHERE ITEPRA = '$codice'";
        } else {
            $sql = "SELECT * FROM ITEEVT WHERE ROWID = '$codice'";
        }
        return ItaDB::DBSQLSelect($Pram_db, $sql, $multi);
    }

    public function GetPraazioniDaMaster($Pram_db, $codice, $Tipo = 'codice', $multi = true) {
        if ($Tipo == 'codice') {
            $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '$codice'";
        } else {
            $sql = "SELECT * FROM PRAAZIONI WHERE ROWID = '$codice'";
        }
        return ItaDB::DBSQLSelect($Pram_db, $sql, $multi);
    }

    public function GetFileList($filePath) {
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
                'HASH' => hash_file('sha256', $filePath . '/' . $obj)
            );
        }
        closedir($dh);
        return $retListGen;
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

    function CheckUsage($Codice, $tipo) {
        switch ($tipo) {
            case "SETTORE":
//$Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ANAPRA WHERE PRASTT = $Codice", true);
                $Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ITEEVT WHERE IEVSTT = '$Codice'", true);
                $Result_tab_proges = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PROGES WHERE GESSTT = $Codice", true);
                break;
            case "ATTIVITA":
//$Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ANAPRA WHERE PRAATT = '$Codice'", true);
                $Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ITEEVT WHERE IEVATT = '$Codice'", true);
                $Result_tab_proges = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PROGES WHERE GESATT = '$Codice'", true);
                break;
            case "TIPOLOGIA":
//$Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ANAPRA WHERE PRATIP = '$Codice'", true);
                $Result_tab_anapra = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ITEEVT WHERE IEVTIP = '$Codice'", true);
                $Result_tab_proges = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PROGES WHERE GESTIP = '$Codice'", true);
                break;
        }
        return array(
            "Result_tab_anapra" => $Result_tab_anapra,
            "Result_tab_proges" => $Result_tab_proges,
        );
    }

    public function caricaTestoBase_Orig($passAlle, $codice, $tipo = "codice") {
        $docLib = new docLib();
        $praPasso = new praPasso();
        $keyPasso = $praPasso->getKeyPasso();
        $propas_rec = $this->getPropas($keyPasso);
        $allegato = $docLib->getDocumenti($codice, $tipo);
        $posInterno = -1;
        $numLevel0 = 0;
        foreach ($passAlle as $posI => $alle) {
            if ($alle['RANDOM'] == 'TESTOBASE') {
                $posInterno = $posI;
                $parent = $alle['PROV'];
                break;
            }
            if ($alle['level'] == 0) {
                $numLevel0++;
            }
        }

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }

        $percorsoTmp = itaLib::getPrivateUploadPath();
        $suffix = pathinfo($allegato['URI'], PATHINFO_EXTENSION);
        $randName = md5(rand() * time()) . "." . $suffix;

        switch ($allegato['TIPO']) {
            case 'XHTML':
                $contenuto = $allegato['CONTENT'];
                $contenuto = "<!-- itaTestoBase:" . $allegato['CODICE'] . " -->" . $contenuto;
                file_put_contents("$percorsoTmp/$randName", $contenuto);
                break;

            case 'DOCX':
                copy($docLib->getFilePath($allegato), "$percorsoTmp/$randName");
                break;
        }

        if ($posInterno == -1) {
            $allegatoLevel0['PROV'] = "L0_" . $numLevel0;
            $allegatoLevel0['RANDOM'] = 'TESTOBASE';
            $allegatoLevel0['NAME'] = 'TESTOBASE';
            $allegatoLevel0['level'] = 0;
            $allegatoLevel0['parent'] = null;
            $allegatoLevel0['isLeaf'] = 'false';
            $allegatoLevel0['expanded'] = 'true';
            $allegatoLevel0['loaded'] = 'true';
            $passAlle[] = $allegatoLevel0;
//Valorizzo Tabella
            $keyInc = count($passAlle);
            $allegatoLevel1['INFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['CODICE'] . "." . $suffix . '</span>';
            $allegatoLevel1['PROV'] = $keyInc;
            $allegatoLevel1['SIZE'] = $this->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera pdf\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
            $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
            $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
            $allegatoLevel1['PASPUB'] = 1;
            $allegatoLevel1['STATOALLE'] = $this->GetStatoAllegati("V");
            $allegatoLevel1['PASSTA'] = "V";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOBASE';
            $allegatoLevel1['TESTOBASE'] = $allegato['CODICE'];
            $allegatoLevel1['FILEINFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $allegato['CODICE'] . "." . $suffix;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName;
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->getIconCds(0, $propas_rec['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = "L0_" . $numLevel0;
            $allegatoLevel1['isLeaf'] = 'true';
            $passAlle[$keyInc] = $allegatoLevel1;
        } else {
            $i = $posInterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($passAlle)) {
                    $trovato = true;
                } else {
                    if ($passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $allegatoLevel1 = array();
            $arrayTop = array_slice($passAlle, 0, $i);
            $arrayDown = array_slice($passAlle, $i);
//Valorizzo Tabella
            $inc = count($passAlle);
            $allegatoLevel1['PROV'] = $inc;
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['CODICE'] . "." . $suffix . '</span>';
            $allegatoLevel1['INFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['SIZE'] = $this->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera pdf\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
            $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
            $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
            $allegatoLevel1['PASPUB'] = 1;
            $allegatoLevel1['STATOALLE'] = $this->GetStatoAllegati("V");
            $allegatoLevel1['PASSTA'] = "V";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOBASE';
            $allegatoLevel1['TESTOBASE'] = $allegato['CODICE'];
            $allegatoLevel1['FILEINFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $allegato['CODICE'] . "." . $suffix;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName;
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->getIconCds(0, $propas_rec['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = $parent;
            $allegatoLevel1['isLeaf'] = 'true';
            $passAlle[$inc] = $allegatoLevel1;
            $arrayTop[] = $allegatoLevel1;
            $inc++;
            foreach ($arrayDown as $chiave => $recordDown) {
                if ($recordDown['level'] == 1) {
                    $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                }
            }
            $passAlle = array_merge($arrayTop, $arrayDown);
        }
        return $passAlle;
    }

    public function caricaTestoBase($passAlleTmp, $codice, $tipo = "codice") {
        $praPasso = new praPasso();
        $keyPasso = $praPasso->getKeyPasso();
        $passAlle = $this->caricaTestoBase_Generico($keyPasso, $passAlleTmp, $codice, $tipo);
        return $passAlle;
    }

    public function caricaTestoBase_Generico($keyPasso, $passAlle, $codice, $tipo = "codice") {
        $docLib = new docLib();
//        $praPasso = new praPasso();
//        $keyPasso = $praPasso->getKeyPasso();
        $propas_rec = $this->getPropas($keyPasso);
        $allegato = $docLib->getDocumenti($codice, $tipo);
        $posInterno = -1;
        $numLevel0 = 0;
        foreach ($passAlle as $posI => $alle) {
            if ($alle['RANDOM'] == 'TESTOBASE') {
                $posInterno = $posI;
                $parent = $alle['PROV'];
                break;
            }
            if ($alle['level'] == 0) {
                $numLevel0++;
            }
        }

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }

        $percorsoTmp = itaLib::getPrivateUploadPath();
        $suffix = pathinfo($allegato['URI'], PATHINFO_EXTENSION);
        $randName = md5(rand() * time()) . "." . $suffix;

        switch ($allegato['TIPO']) {
            case 'XHTML':
                $contenuto = $allegato['CONTENT'];
                $contenuto = "<!-- itaTestoBase:" . $allegato['CODICE'] . " -->" . $contenuto;
                file_put_contents("$percorsoTmp/$randName", $contenuto);
                break;

            case 'DOCX':
                copy($docLib->getFilePath($allegato), "$percorsoTmp/$randName");
                break;
        }

        if ($posInterno == -1) {
            $allegatoLevel0['PROV'] = "L0_" . $numLevel0;
            $allegatoLevel0['RANDOM'] = 'TESTOBASE';
            $allegatoLevel0['NAME'] = 'TESTOBASE';
            $allegatoLevel0['level'] = 0;
            $allegatoLevel0['parent'] = null;
            $allegatoLevel0['isLeaf'] = 'false';
            $allegatoLevel0['expanded'] = 'true';
            $allegatoLevel0['loaded'] = 'true';
            $passAlle[] = $allegatoLevel0;
//Valorizzo Tabella
            $keyInc = count($passAlle);
            $allegatoLevel1['INFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['CODICE'] . "." . $suffix . '</span>';
            $allegatoLevel1['PROV'] = $keyInc;
            $allegatoLevel1['SIZE'] = $this->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera pdf\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
            $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
            $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
            $allegatoLevel1['PASPUB'] = 1;
            $allegatoLevel1['STATOALLE'] = $this->GetStatoAllegati("V");
            $allegatoLevel1['PASSTA'] = "V";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOBASE';
            $allegatoLevel1['TESTOBASE'] = $allegato['CODICE'];
            $allegatoLevel1['FILEINFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $allegato['CODICE'] . "." . $suffix;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName;
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->getIconCds(0, $propas_rec['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = "L0_" . $numLevel0;
            $allegatoLevel1['isLeaf'] = 'true';
            $passAlle[$keyInc] = $allegatoLevel1;
        } else {
            $i = $posInterno + 1;
            $trovato = false;
            while ($trovato == false) {
                if ($i >= count($passAlle)) {
                    $trovato = true;
                } else {
                    if ($passAlle[$i]['level'] == 0) {
                        $trovato = true;
                    } else {
                        $i++;
                    }
                }
            }
            $allegatoLevel1 = array();
            $arrayTop = array_slice($passAlle, 0, $i);
            $arrayDown = array_slice($passAlle, $i);
//Valorizzo Tabella
            $inc = count($passAlle);
            $allegatoLevel1['PROV'] = $inc;
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['CODICE'] . "." . $suffix . '</span>';
            $allegatoLevel1['INFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['SIZE'] = $this->formatFileSize(filesize($percorsoTmp . "/" . $randName));
            $allegatoLevel1['PREVIEW'] = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera pdf\"></span>";
            $allegatoLevel1['STATO'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
            $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
            $allegatoLevel1['PUBBLICA'] = "<span class=\"ita-icon ita-icon-publish-24x24\">Allegato pubblicato</span>";
            $allegatoLevel1['PASPUB'] = 1;
            $allegatoLevel1['STATOALLE'] = $this->GetStatoAllegati("V");
            $allegatoLevel1['PASSTA'] = "V";
//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'TESTOBASE';
            $allegatoLevel1['TESTOBASE'] = $allegato['CODICE'];
            $allegatoLevel1['FILEINFO'] = $allegato['OGGETTO'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
            $allegatoLevel1['FILENAME'] = $randName;
            $allegatoLevel1['FILEORIG'] = $allegato['CODICE'] . "." . $suffix;
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $allegatoLevel1['PASSHA2'] = hash_file('sha256', $allegatoLevel1['FILEPATH']);
            $allegatoLevel1['CODICE'] = $randName;
            $allegatoLevel1['ROWID'] = 0;
            $allegatoLevel1['CDS'] = $this->getIconCds(0, $propas_rec['PROFLCDS']);
            $allegatoLevel1['PASFLCDS'] = 0;
            $allegatoLevel1['level'] = 1;
            $allegatoLevel1['parent'] = $parent;
            $allegatoLevel1['isLeaf'] = 'true';
            $passAlle[$inc] = $allegatoLevel1;
            $arrayTop[] = $allegatoLevel1;
            $inc++;
            foreach ($arrayDown as $chiave => $recordDown) {
                if ($recordDown['level'] == 1) {
                    $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                }
            }
            $passAlle = array_merge($arrayTop, $arrayDown);
        }
        return $passAlle;
    }

    public function inviaNotificaResponsabileAssegnazione($nameForm, $codRespAss, $rowid, $titolo, $testo) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
//$codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
        if ($codRespAss) {
            include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
            $accLib = new accLib();
            $Utenti_rec = $accLib->GetUtenti($codRespAss, "uteana3");
            if (!$Utenti_rec) {
                return false;
            }
            $nomeUtente = $Utenti_rec['UTELOG'];
            $envLib = new envLib();
            if (!$envLib->inserisciNotifica($nameForm, $titolo, $testo, $nomeUtente, array(
                        'ACTIONMODEL' => $nameForm,
                        'ACTIONPARAM' => serialize(
                                array(
                                    'setOpenMode' => array('edit'),
                                    'setOpenRowid' => array($rowid)
//'setOpenRowid' => array($proges_rec['ROWID'])
                                )
                        )
                            )
                    )
            ) {
                return false;
            }
        }
        return true;
    }

    function DeleteMailFromServer($idMail) {
//
//Recupere il record della Mail su MAIL_ARCHIVIO
//
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $mailArchivio_rec = $emlLib->getMailArchivio($idMail);
        if (!$mailArchivio_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Record mail non trovato");
            return false;
        }

//
//Istanzio la casella mail presa dal record della mail
//
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($mailArchivio_rec['ACCOUNT']);
        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile accedere alle funzioni dell'account: " . $mailArchivio_rec['ACCOUNT']);
            return false;
        }

//
//Cancello il messaggio dal server
//
        App::log("Cancello id: $idMail UID: " . $mailArchivio_rec['STRUID']);
        if (!$emlMailBox->deleteMessageFromServer($mailArchivio_rec['STRUID'])) {
            $this->setErrCode($emlMailBox->getLastExitCode());
            $this->setErrMessage($emlMailBox->getLastMessage());
            return false;
        }
        return true;
    }

    function GetOggettoPratica($proges_rec) {
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->GetFilent(25);
        $praLibVar->setCodicePratica($proges_rec['GESNUM']);
        $dictionaryValues = $praLibVar->getVariabiliPratica(true)->getAllData();
        $oggetto = $this->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);

        $anapra_rec = $this->GetAnapra($proges_rec['GESPRO']);
        if ($proges_rec['GESPRA'] && $anapra_rec['PRAOGGTML_ACQ']) {
            $proric_rec = $this->GetProric($proges_rec['GESPRA']);
            $oggetto = $proric_rec['RICOGG'];
        }

        return strtoupper($oggetto);
    }

    function CheckAntecedente($propasSel_rec) {
        $propas_rec = $this->GetPropas($propasSel_rec['PROPAK'], "prokpre");
        if ($propas_rec) {
            $this->setErrCode("-1");
            $this->setErrMessage("Il passo con seq. <b>" . $propasSel_rec['PROSEQ'] . "</b> è già antecedente al passo con seq. <b>" . $propas_rec['PROSEQ'] . "</b>");
            return false;
        }
        return true;
    }

    function CheckAntecedenteITEPAS($itepasSel_rec) {
        $itepas_rec = $this->GetItepas($itepasSel_rec['ITEKEY'], "itekpre");
        if ($itepas_rec) {
            $this->setErrCode("-1");
            $this->setErrMessage("Il passo con seq. <b>" . $itepasSel_rec['ITESEQ'] . "</b> è già antecedente al passo con seq. <b>" . $itepas_rec['ITESEQ'] . "</b>");
            return false;
        }
        return true;
    }

//function AddPassiAntecedenti($passiSel, $table = "ITEPAS") {
    function AddPassiAntecedenti($rowid, $table = "ITEPAS") {
        switch ($table) {
            case "ITEPAS":
//                foreach ($passiSel as $passo) {
                $itepasSel_rec = $this->GetItepas($rowid, "rowid");
                $itepas_tab = $this->GetItepas($itepasSel_rec['ITEKEY'], "itekpre", true, " AND ITECOD = '" . $itepasSel_rec['ITECOD'] . "' ORDER BY ITESEQ");
                $passiSel = $itepas_tab;
//                    if ($itepas_tab) {
//                        foreach ($itepas_tab as $itepas_rec) {
//                            $passiSel[] = $itepas_rec;
//                        }
//                    }
//                }
//                $passiSel = $this->array_sort($passiSel, "ITESEQ");
                break;
            case "PROPAS":
//                foreach ($passiSel as $rowid) {
                $propasSel_rec = $this->GetPropas($rowid, "rowid");
                $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '" . $propasSel_rec['PRONUM'] . "' AND PROKPRE = '" . $propasSel_rec['PROPAK'] . "'  ORDER BY PROSEQ", true);
                $passiSel = $propas_tab;
//                if ($propas_tab) {
//                    foreach ($propas_tab as $propas_rec) {
//                        $passiSel[] = $propas_rec['ROWID'];
//                    }
//                }
////                }
//
//                /*
//                 * Riordino l'array in base alla sequenza originale appoggiandomi su una array temporaneo
//                 * e poi ricreando l'array originale
//                 */
//                $arrayTmp = array();
//                foreach ($passiSel as $key => $rowid) {
//                    $propasSel_rec = $this->GetPropas($rowid, "rowid");
//                    $arrayTmp[$key]['ROWID'] = $propasSel_rec['ROWID'];
//                    $arrayTmp[$key]['PROSEQ'] = $propasSel_rec['PROSEQ'];
//                }
//                $arrayTmp = $this->array_sort($arrayTmp, "PROSEQ");
//                Out::msgInfo("", print_r($arrayTmp, true));
//                $passiSel = array();
//                foreach ($arrayTmp as $key => $passo) {
//                    $passiSel[] = $passo['ROWID'];
//                }
                break;
        }
        return $passiSel;
    }

    function GetColorPortlet($filent_rec, $proges_rec) {
        $color = "";
        if ($proges_rec['GESPRA']) {
            $color = $filent_rec['FILDE1']; //Da portale
        } else {
            $praMail_rec = $this->getPraMail($proges_rec['GESNUM'], "gesnum");
            if ($praMail_rec) {
                $color = $filent_rec['FILDE2']; //Da pec
            } else {
                $color = $filent_rec['FILDE3']; //Da Altro 
            }
        }
        return $color;
    }

    function GetLegendaColoriPratiche($daPortale, $daPec, $altro) {
        $Filent_rec = $this->GetFilent(24);
        if ($Filent_rec['FILDE1']) {
            Out::css($daPortale, 'background-color', $Filent_rec['FILDE1']);
        }
        if ($Filent_rec['FILDE2']) {
            Out::css($daPec, 'background-color', $Filent_rec['FILDE2']);
        }
        if ($Filent_rec['FILDE3']) {
            Out::css($altro, 'background-color', $Filent_rec['FILDE3']);
        }
        Out::attributo($daPortale, "readonly", '0');
        Out::attributo($daPec, "readonly", '0');
        Out::attributo($altro, "readonly", '0');
    }

    function GetPartitaIva($Gesnum) {
        $Prodag_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='$Gesnum' AND DAGKEY='IC_CODFIS_IMPRESA'", false);
        if ($Prodag_rec['DAGVAL']) {
            if (strlen($Prodag_rec['DAGVAL']) > 11) {
                $prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='$Gesnum' AND DAGKEY='IMPRESAINDIVIDUALE_PARTITAIVA_PIVA'", true);
                foreach ($prodag_tab as $prodag_rec) {
                    if ($prodag_rec['DAGVAL']) {
                        $p_iva = $prodag_rec['DAGVAL'];
                        break;
                    }
                }
            } else {
                $p_iva = $Prodag_rec['DAGVAL'];
            }
        }
        if ($p_iva == "") {
            $prodag_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='$Gesnum' AND DAGKEY='IMPRESA_PARTITAIVA_PIVA'", true);
            foreach ($prodag_tab as $prodag_rec) {
                if ($prodag_rec['DAGVAL']) {
                    $p_iva = $prodag_rec['DAGVAL'];
                    break;
                }
            }
        }
//        if ($p_iva == "") {
//            $Anades_rec_impresa = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('IMPRESA'));
//            if ($Anades_rec_impresa) {
//                $p_iva = $Anades_rec_impresa['DESPIVA'];
//                if ($p_iva == "") {
//                    $p_iva = $Anades_rec_impresa['DESFIS'];
//                }
//            } else {
//                $Anades_rec_impresaInd = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('IMPRESAINDIVIDUALE'));
//                if ($Anades_rec_impresaInd) {
//                    $p_iva = $Anades_rec_impresaInd['DESPIVA'];
//                    if ($p_iva == "") {
//                        $p_iva = $Anades_rec_impresaInd['DESFIS'];
//                    }
//                } else {
//                    $Anades_rec_dichiarante = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('DICHIARANTE'));
//                    if ($Anades_rec_dichiarante) {
//                        $p_iva = $Anades_rec_dichiarante['DESPIVA'];
//                        if ($p_iva == "") {
//                            $p_iva = $Anades_rec_dichiarante['DESFIS'];
//                        }
//                    } else {
//                        $Anades_rec_esibente = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('ESIBENTE'));
//                        if ($Anades_rec_esibente) {
//                            $p_iva = $Anades_rec_esibente['DESPIVA'];
//                            if ($p_iva == "") {
//                                $p_iva = $Anades_rec_esibente['DESFIS'];
//                            }
//                        }
//                    }
//                }
//            }
        if ($p_iva == "") {
            $Anades_rec_impresa = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('IMPRESA'));
            if ($Anades_rec_impresa) {
                $p_iva = $Anades_rec_impresa['DESPIVA'];
                if ($p_iva == "") {
                    $p_iva = $Anades_rec_impresa['DESFIS'];
                }
            }
        }
        if ($p_iva == "") {
            $Anades_rec_impresaInd = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('IMPRESAINDIVIDUALE'));
            if ($Anades_rec_impresaInd) {
                $p_iva = $Anades_rec_impresaInd['DESPIVA'];
                if ($p_iva == "") {
                    $p_iva = $Anades_rec_impresaInd['DESFIS'];
                }
            }
        }
        if ($p_iva == "") {
            $Anades_rec_dichiarante = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('DICHIARANTE'));
            if ($Anades_rec_dichiarante) {
                $p_iva = $Anades_rec_dichiarante['DESPIVA'];
                if ($p_iva == "") {
                    $p_iva = $Anades_rec_dichiarante['DESFIS'];
                }
            }
        }
        if ($p_iva == "") {
            $Anades_rec_esibente = $this->GetAnades($Gesnum, 'ruolo', false, praRuolo::getSystemSubjectCode('ESIBENTE'));
            if ($Anades_rec_esibente) {
                $p_iva = $Anades_rec_esibente['DESPIVA'];
                if ($p_iva == "") {
                    $p_iva = $Anades_rec_esibente['DESFIS'];
                }
            }
        }

        if ($p_iva == "") {
            $prodag_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM='$Gesnum' AND (DAGKEY LIKE '%IVA%' OR DAGKEY LIKE '%iva%') AND DAGVAL<>''", false);
            $p_iva = $prodag_rec['DAGVAL'];
        }
        return $p_iva;
    }

    function caricaDati($procedimento) {
        $Dati_view = ItaDB::DBSQLSelect($this->getPRAMDB(), "
            SELECT
                PRODAG.ROWID,
                PROSEQ,
                PRODPA,
                PROPAK,
                DAGPAK,
                DAGKEY,
                DAGPRI,
                DAGVAL,
                DAGSEQ,
                DAGSET,
                DAGDES,
                DAGALIAS
            FROM
                PROPAS PROPAS
            LEFT OUTER JOIN
                PRODAG PRODAG            
            ON
                PRODAG.DAGPAK = PROPAS.PROPAK
            WHERE
                DAGNUM = '$procedimento' AND DAGVAL<>'' ORDER BY DAGPRI DESC,PROSEQ,DAGSEQ
            ", true);

        foreach ($Dati_view as $key => $dato) {
            $Dati_view[$key]['PROSEQ'] = $dato['PROSEQ'] . " - " . $dato['PRODPA'];
            $Dati_view[$key]['DAGPRI'] = $dato['DAGPRI'];
            if ($dato['DAGPRI'] != 0) {
                $Dati_view[$key]['DAGSEQ'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['DAGSEQ'] . "</p>";
                $Dati_view[$key]['DAGKEY'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['DAGKEY'] . "</p>";
                $Dati_view[$key]['PROSEQ'] = "<p style = 'color:red;font-weight:bold;font-size:1.3em;'>" . $dato['PROSEQ'] . " - " . $dato['PRODPA'] . "</p>";
            }
        }
        $proges_rec = $this->GetProges($procedimento);
        include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
        $praPerms = new praPerms();
        if (!$praPerms->checkSuperUser($proges_rec)) {
            return $praPerms->filtraDatiAggView($this->caricaPassiBO($procedimento), $Dati_view);
        } else {
            return $Dati_view;
        }
    }

    function caricaPassiBO($procedimento) {
        $sql = $this->CreaSqlCaricaPassi($procedimento, false);
        try {
            $passi_view = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql);
            if ($passi_view) {
                foreach ($passi_view as $keyPasso => $value) {
                    $Anastp_rec = array();
                    $data = $msgStato = $icon = $acc = $cons = "";
                    if ($value['PROSTATO'] != 0) {
                        $Anastp_rec = $this->GetAnastp($value['PROSTATO']);
                        $msgStato = $Anastp_rec['STPFLAG'];
                    }
                    if ($value['PROFIN']) {
                        if ($msgStato == 'In corso') {
                            $msgStato = "";
                        }
                        $msgStato = $Anastp_rec['STPDES'];
//$passi_view[$keyPasso]['STATOPASSO'] = "<div><p style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-green-24x24\">Passo Chiuso</p><p style=\"vertical-align:top;display:inline-block;\">$msgStato</p></div>";
                        $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-green-24x24\">Passo Chiuso</span><span style=\"vertical-align:top;display:inline-block;\">$msgStato</span>";
                    } elseif ($value['PROINI']) {
                        $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-red-24x24\">Passo Aperto</span><span style=\"display:inline-block;\">$msgStato</span>";
                    } else {
                        $passi_view[$keyPasso]['STATOPASSO'] = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-check-grey-24x24\">Passo non definito</span><span style=\"display:inline-block;\">$msgStato</span>";
                    }

                    $pracomP_rec = $this->GetPracomP($value['PROPAK']);
                    if ($pracomP_rec['COMIDDOC']) {
                        $numDoc = $pracomP_rec['COMIDDOC'];
                        $dataDoc = substr($pracomP_rec['COMDATADOC'], 6, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 4, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 0, 4);
                        $passi_view[$keyPasso]['PRPARTENZA'] = $numDoc . "<br>" . $dataDoc;
                    }
                    if ($pracomP_rec['COMPRT']) {
                        $numProt = substr($pracomP_rec['COMPRT'], 4) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                        $dataProt = substr($pracomP_rec['COMDPR'], 6, 2) . "/" . substr($pracomP_rec['COMDPR'], 4, 2) . "/" . substr($pracomP_rec['COMDPR'], 0, 4);
                        $passi_view[$keyPasso]['PRPARTENZA'] = $numProt . "<br>" . $dataProt;
                    }
                    $pracomA_rec = $this->GetPracomA($value['PROPAK']);
                    if ($pracomA_rec['COMPRT']) {
                        $numProt = substr($pracomA_rec['COMPRT'], 4) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                        $dataProt = substr($pracomA_rec['COMDPR'], 6, 2) . "/" . substr($pracomA_rec['COMDPR'], 4, 2) . "/" . substr($pracomA_rec['COMDPR'], 0, 4);
                        $passi_view[$keyPasso]['PRARRIVO'] = $numProt . "<br>" . $dataProt;
                    }

                    $passi_view[$keyPasso]['VAI'] = $this->decodificaImmagineSaltoPasso($value['PROQST'], $value['PROVPA'], $value['PROVPN'], $this->GetProvpadett($value['PROPAK'], 'propak'));

                    if ($value['PROPART'] != 0) {
                        $passi_view[$keyPasso]['ARTICOLO'] = '<span class="ita-icon ita-icon-rtf-24x24">Articolo</span>';
                    }
                    $passi_view[$keyPasso]['ORDERANT'] = str_pad($value['PROSEQ'], 4, "0", STR_PAD_LEFT) . str_pad($value['PROKPRE'], 4, "0", STR_PAD_LEFT);
                    if ($value['PROKPRE']) {
                        $propas_recAnt = $this->GetPropas($value['PROKPRE'], "propak");
//$passi_view[$keyPasso]['SEQANT'] = $propas_recAnt['PROSEQ'];
                        $passi_view[$keyPasso]['ORDERANT'] = str_pad($propas_recAnt['PROSEQ'], 4, "0", STR_PAD_LEFT) . str_pad($value['PROSEQ'], 4, "0", STR_PAD_LEFT);
                        $passi_view[$keyPasso]['PROSEQ'] = "";
                        $passi_view[$keyPasso]['SEQANT'] = $value['PROSEQ'];
                    }
                    if ($value['PROCTR'] != '') {
                        $passi_view[$keyPasso]['PROCEDURA'] = '<span class="ita-icon ita-icon-ingranaggio-16x16">Procedura di Controllo</span>';
                    }
                    if ($value['PROALL']) {
                        $passi_view[$keyPasso]['ALLEGATI'] = '<span class="ita-icon ita-icon-clip-16x16">allegati</span>';
                    }
                    $passi_view[$keyPasso]['STATOCOM'] = $this->GetIconStatoCom($value['PROPAK']);

//                    $fcolor = "white";
//                    $bgcolor = "blue";
//                    $passi_view[$keyPasso]['PROSEQ'] = "<p style='color:$fcolor;background-color:$bgcolor;font-weight:bolder;'>" . $passi_view[$keyPasso]['PROSEQ'] . "</p>";
//                    $passi_view[$keyPasso]['SEQANT'] = "<p style='color:$fcolor;background-color:$bgcolor;font-weight:bolder;'>" . $passi_view[$keyPasso]['SEQANT'] . "</p>";
                }
            }
            return $this->array_sort($passi_view, "ORDERANT");
//return $passi_view;
        } catch (Exception $e) {
            Out::msgStop('Errore DB', $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param string  $procedimento
     * @param boolean $perAllegati se true estrae passi per realizzare la vista allegati, includendo anche i passi FO con ALLEGATI
     * @return string
     */
    function CreaSqlCaricaPassi($procedimento, $perAllegati = false) {
        $Filent_rec = $this->GetFilent(15);
        if ($Filent_rec['FILVAL'] == 1) {
            if ($perAllegati == true) {
                $passi_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PROPAS WHERE PRONUM = '$procedimento' ORDER BY PROSEQ");
                foreach ($passi_tab as $passi_rec) {
                    if ($passi_rec['PRODRR'] == 1) {
                        $passoUploadRapporto = current($passi_tab);
                        break;
                    }
                }
                foreach ($passi_tab as $passi_rec) {
                    if ($passi_rec['PRORICUNI'] == 1) {
                        $passoAccorpa = $passi_rec;
                        break;
                    }
                }
                $wherePassiFO = " AND (PROPUB = 0 OR PROUPL = 1 OR PROMLT = 1 OR PRODAT = 1 OR PROPAK = '{$passoUploadRapporto['PROPAK']}') ";

                if ($passoAccorpa) {
                    $wherePassiFO .= " OR PROPAK = '{$passoAccorpa['PROPAK']}' ";
                }
            } else {
                $wherePassiFO = " AND PROPUB = 0 ";
            }
        }
        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROVPA AS PROVPA,
                    PROPAS.PROVPN AS PROVPN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROQST AS PROQST,
                    PROPAS.PROPUB AS PROPUB,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSTAP AS PROSTAP,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROSTCH AS PROSTCH,
                    PROPAS.PROSTATO AS PROSTATO,                    
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROOBL AS PROOBL,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PROOPE AS PROOPE,
                    PROPAS.PROKPRE AS PROKPRE,
                    PROPAS.PROINI AS PROINI,
                    PROPAS.ROWID_DOC_CLASSIFICAZIONE AS ROWID_DOC_CLASSIFICAZIONE,
                    PROPAS.PROCLT AS PROCLT,
                    PROPAS.PROITK AS PROITK," .
                $this->getPRAMDB()->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM PROPAS
                    LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
               WHERE 
                    PROPAS.PRONUM='$procedimento' AND 
                    PROPAS.PROOPE='' $wherePassiFO 
               ORDER BY
                    PROSEQ";
        return $sql;
    }

    function GetUtenteAggregato($utenti, $aggrPratica) {
        foreach ($utenti as $utente) {
            if ($utente['CODAGGREGATO'] == $aggrPratica) {
                return $utente['UTENTE'];
            }
        }
    }

    function GetContextToken($UserName, $UserPassword, $DomainCode) {
        if (!$UserName) {
            $this->setErrMessage("nome utente non trovato");
            return false;
        }
        $ret_verpass = ita_verpass($DomainCode, $UserName, $UserPassword);
        if (!$ret_verpass) {
            $this->setErrCode("-1");
            $this->setErrMessage("Autenticazione Annullata");
            return false;
        }

        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
            $this->setErrCode("-1");
            $this->setErrMessage($ret_verpass['messaggio']);
            return false;
        }
        if ($ret_verpass['status'] == '-99') {
            $this->setErrCode("-99");
            $this->setErrMessage($ret_verpass['messaggio']);
            return false;
        }

        $cod_ute = $ret_verpass['codiceUtente'];

        $itaToken = new ItaToken($DomainCode);
        $ret_token = $itaToken->createToken($cod_ute);
        if ($ret_token['status'] == '0') {
            return $itaToken->getTokenKey();
        } else {
            $this->setErrCode("-1");
            $this->setErrMessage("Impossibile reperire un token valido");
            return false;
        }
    }

    public static function CheckItaEngineContextToken($TokenKey, $DomainCode = '') {
        if (!$TokenKey) {
            $this->setErrMessage("Token Mancante");
            return false;
        }
//estrazione DomainCode dal token
        if ($DomainCode == '') {
            list($token, $DomainCode) = explode("-", $TokenKey);
        }
        if (!$DomainCode) {
            $this->setErrMessage("Codice Ente/Organizzazione Mancante");
            return false;
        }

        $itaToken = new ItaToken($DomainCode);
        $itaToken->setTokenKey($TokenKey);
        $ret_token = $itaToken->checkToken();
        if ($ret_token['status'] == '0') {
            return "Valid";
        } else {
            return false;
        }
    }

    function CreaSqlFascicoli($arrayCampi, $flagAssegnazioni = 0) {
        $Stato_proc = $arrayCampi['ParametriRicerca']['Stato_proc'];
        $Dal_num = $arrayCampi['ParametriRicerca']['Dal_num'];
        $al_num = $arrayCampi['ParametriRicerca']['Al_num'];
        $anno = $arrayCampi['ParametriRicerca']['Anno'];
        $Da_rich = $arrayCampi['ParametriRicerca']['Da_richiesta'];
        $A_rich = $arrayCampi['ParametriRicerca']['A_richiesta'];
        $annoRich = $arrayCampi['ParametriRicerca']['Anno_rich'];
        $Da_data = $arrayCampi['ParametriRicerca']['Da_data'];
        $a_data = $arrayCampi['ParametriRicerca']['A_data'];
        $Da_data_sc = $arrayCampi['ParametriRicerca']['Da_data_sc'];
        $a_data_sc = $arrayCampi['ParametriRicerca']['A_data_sc'];
        $Da_data_ch = $arrayCampi['ParametriRicerca']['Da_datach'];
        $a_data_ch = $arrayCampi['ParametriRicerca']['A_datach'];
        $Intestatario = $arrayCampi['ParametriRicerca']['Intestatario'];
        $procedimento = $arrayCampi['ParametriRicerca']['Procedimento'];
        $Stato_passo = $arrayCampi['ParametriRicerca']['Stato_passo'];
        $Stato_allegato = $arrayCampi['ParametriRicerca']['Stato_allegato'];
        $passo = $arrayCampi['ParametriRicerca']['Passo'];
        $Responsabile = $arrayCampi['ParametriRicerca']['Responsabile'];
        $Campo = $arrayCampi['ParametriRicerca']['Campo'];
        $NoteFascicolo = $arrayCampi['ParametriRicerca']['NoteFascicolo'];
        $aggregato = $arrayCampi['ParametriRicerca']['Aggregato'];
        $sportello = $arrayCampi['ParametriRicerca']['Sportello'];
        $tipologia = $arrayCampi['ParametriRicerca']['Tipologia'];
        $settore = $arrayCampi['ParametriRicerca']['Sett'];
        $attivita = $arrayCampi['ParametriRicerca']['Atti'];
        $articolo = $arrayCampi['ParametriRicerca']['Articolo'];
        $tipoArticolo = $arrayCampi['ParametriRicerca']['TipoArt'];
        $Tipo = $arrayCampi['ParametriRicerca']['Tipo'];
        $Sezione = $arrayCampi['ParametriRicerca']['Sezione'];
        $Foglio = $arrayCampi['ParametriRicerca']['Foglio'];
        $Sub = $arrayCampi['ParametriRicerca']['Sub'];
        $Note = $arrayCampi['ParametriRicerca']['Note'];
        $Codice = $arrayCampi['ParametriRicerca']['Codice'];
        $Particella = $arrayCampi['ParametriRicerca']['Particella'];
        $nomeCampo = $arrayCampi['ParametriRicerca']['NomeCampo'];
        $Ruolo = $arrayCampi['ParametriRicerca']['Ruolo'];
        $Nominativo = $arrayCampi['ParametriRicerca']['Nominativo'];
        $codFis = $arrayCampi['ParametriRicerca']['codFis'];
        $NumProt = $arrayCampi['ParametriRicerca']['NProt'];
        $AnnoProt = $arrayCampi['ParametriRicerca']['AnnoProt'];
        $Oggetto = $arrayCampi['ParametriRicerca']['Oggetto'];
        $TipologiaPasso = $arrayCampi['ParametriRicerca']['TipologiaPasso'];
        $maggGiorni = $arrayCampi['ParametriRicerca']['maggGiorni'];
        $Assegnatario = $arrayCampi['ParametriRicerca']['CodUtenteAss'];

        $D_gio = date('Ymd');
        if ($anno == '')
            if ($procedimento != '')
                $procedimento = str_pad($procedimento, 6, 0, STR_PAD_RIGHT);
        if ($passo != '')
            $passo = str_pad($passo, 6, 0, STR_PAD_RIGHT);
        if ($Dal_num == '')
            $Dal_num = "0";
        if ($al_num == '')
            $al_num = "999999";
        if ($Dal_num != '')
            $Dal_num = $anno . str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
        if ($al_num != '')
            $al_num = $anno . str_pad($al_num, 6, "0", STR_PAD_LEFT);
        if ($Da_rich != '') {
            $Da_rich = $annoRich . str_pad($Da_rich, 6, "0", STR_PAD_LEFT);
        } else {
            $Da_rich = "0";
        }
        if ($A_rich != '') {
            $A_rich = $annoRich . str_pad($A_rich, 6, "0", STR_PAD_LEFT);
        } else {
            $A_rich = "999999";
        }

//Ricerca Immobili
        if ($Tipo) {
            $join1 = " PRAIMM.TIPO =  '$Tipo'";
        }
        if ($Sezione) {
            $join2 = ($join1) ? " AND" : "";
            $join2 .= " PRAIMM.SEZIONE =  '$Sezione'";
        }
        if ($Foglio) {
            $join3 = ($join2 || $join1) ? " AND" : "";
            $join3 .= " PRAIMM.FOGLIO =  '$Foglio'";
        }
        if ($Particella) {
            $join4 = ($join3 || $join2 || $join1) ? " AND" : "";
            $join4 .= " PRAIMM.PARTICELLA =  '$Particella'";
        }
        if ($Sub) {
            $join5 = ($join3 || $join2 || $join1 || $join4) ? " AND" : "";
            $join5 .= " PRAIMM.SUBALTERNO =  '$Sub'";
        }
        if ($Note) {
            $join6 = ($join3 || $join2 || $join1 || $join4 || $join5) ? " AND" : "";
            $join6 .= $this->PRAM_DB->strLower('PRAIMM.NOTE') . " LIKE  '%" . strtolower($Note) . "%'";
        }
        if ($Codice) {
            $join7 = ($join3 || $join2 || $join1 || $join4 || $join5 || $join6) ? " AND" : "";
            $join7 .= " PRAIMM.CODICE =  '$Codice'";
        }
        if ($join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7) {
            $joinImmobili = "INNER JOIN PRAIMM ON PROGES.GESNUM = PRAIMM.PRONUM AND " . $join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7;
        }

        if ($Campo || $nomeCampo) {
            $joinCampo = "INNER JOIN PRODAG PRODAG1 ON PROGES.GESNUM = PRODAG1.DAGNUM"; //AND LOWER(PRODAG1.DAGVAL) LIKE LOWER('%$Campo%')";
            if ($Campo) {
                $joinCampo .= " AND " . $this->PRAM_DB->strLower('PRODAG1.DAGVAL') . " LIKE  '%" . strtolower($Campo) . "%'";
            }
            if ($nomeCampo) {
                $joinCampo .= " AND " . $this->PRAM_DB->strLower('PRODAG1.DAGKEY') . " LIKE  '%" . strtolower($nomeCampo) . "%'";
            }
        }
        if ($Ruolo) {
            $joinRuolo = "ANADES.DESRUO = '$Ruolo'";
        }
        if ($NumProt) {
            $joinProtocollo = "LEFT OUTER JOIN PRACOM PRACOM1 ON PRACOM1.COMNUM = PROGES.GESNUM"; // AND (SUBSTR(COMPRT,5) = '$NumProt' OR SUBSTR(PROGES.GESNPR,5) = '$NumProt')";
        }
        if ($Nominativo) {
            $joinNom = ($joinRuolo) ? " AND" : "";
            $joinNom .= $this->PRAM_DB->strLower('ANADES.DESNOM') . " LIKE  '%" . strtolower($Nominativo) . "%'";
        }
        if ($codFis) {
            $joinFis = ($joinNom || $joinRuolo) ? " AND" : "";
            $joinFis .= $this->PRAM_DB->strLower('ANADES.DESFIS') . " LIKE  '%" . strtolower($codFis) . "%'";
        }
        if ($Intestatario != '') {
            $joinInt = " INNER JOIN ANADES ANADES1 ON ANADES1.DESNUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('ANADES.DESNOM') . " LIKE  '%" . strtolower($Intestatario) . "%' AND (ANADES.DESRUO = '0001' OR ANADES.DESRUO = '')";
        }

        if ($joinRuolo . $joinNom . $joinFis) {
            $joinSoggetti = "INNER JOIN ANADES ANADES1 ON PROGES.GESNUM = ANADES1.DESNUM AND " . $joinRuolo . $joinNom . $joinFis;
        }

        if ($TipologiaPasso != '') {
            $joinTipoPasso = " INNER JOIN PROPAS PROPAS1 ON PROPAS1.PRONUM=PROGES.GESNUM AND " . $this->PRAM_DB->strLower('PROPAS1.PRODTP') . " LIKE  '%" . strtolower($TipologiaPasso) . "%'";
        }

        if ($Assegnatario != '') {
            $select_assegnatario = ",(SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=P.GESNUM AND PROOPE<>'')) AS ULTRES";
            $where_assegnatario = " WHERE U.ULTRES = '$Assegnatario'";
        }

        $oggi = date('Ymd');
        $sql = "SELECT 
                    *
                FROM(
                    SELECT
                        *
                        $select_assegnatario
                    FROM(
                        SELECT
                            DISTINCT PROGES.ROWID AS ROWID,
                            PROGES.GESNUM AS GESNUM,
                            PROGES.GESNUM AS ORDER_GESNUM,
                            PROGES.GESDRE AS GESDRE,
                            PROGES.GESDRE AS ORDER_GESDRE,
                            PROGES.GESDRI AS GESDRI," .
                $this->PRAM_DB->dateDiff(
                        $this->PRAM_DB->coalesce(
                                $this->PRAM_DB->nullIf("GESDCH", "''"), "'$oggi'"
                        ), 'GESDRI'
                ) . " AS NUMEROGIORNI,
                            PROGES.GESORA AS GESORA,
                            PROGES.GESDCH AS GESDCH,
                            PROGES.GESPRA AS GESPRA,
                            PROGES.GESPRA AS ORDER_GESPRA,
                            PROGES.GESTSP AS GESTSP,
                            PROGES.GESSPA AS GESSPA,            
                            PROGES.GESNOT AS GESNOT,
                            PROGES.GESPRE AS GESPRE,
                            PROGES.GESDSC AS GESDSC,
                            PROGES.GESSTT AS GESSTT,
                            PROGES.GESATT AS GESATT,
                            PROGES.GESOGG AS GESOGG,
                            PROGES.GESNPR AS GESNPR,
                            CAST(PROGES.GESNPR AS UNSIGNED),
                            PROGES.GESRES AS GESRES," .
                $this->PRAM_DB->strConcat("ANANOM.NOMCOG", "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
                            PROGES.GESPRO AS GESPRO,
                            ANAPRA.PRADES__1 AS PRADES__1,
                            ANADES.DESNOM AS DESNOM,
                            " . $this->PRAM_DB->coalesce('PRODAGPRIORITA.DAGVAL', "''") . " AS PRIORITA_RICH
                        FROM PROGES PROGES
                            LEFT OUTER JOIN ANANOM ANANOM ON PROGES.GESRES=ANANOM.NOMRES
                            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM 
                            LEFT OUTER JOIN PRODAG PRODAGPRIORITA ON PROGES.GESNUM = PRODAGPRIORITA.DAGNUM AND PRODAGPRIORITA.DAGTIP = 'Priorita_richiesta'
                            $joinImmobili
                            $joinCampo
                            $joinInt
                            $joinSoggetti
                            $joinProtocollo
                            $joinTipoPasso
                        WHERE 
                            (GESNUM BETWEEN '$Dal_num' AND '$al_num')";

        if ($Da_data && $a_data) {
            $sql .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
        }
        if ($Da_data_sc && $a_data_sc) {
            $sql .= " AND (GESDSC BETWEEN '$Da_data_sc' AND '$a_data_sc')";
        }
        if ($Da_data_ch && $a_data_ch) {
            $sql .= " AND (GESDCH BETWEEN '$Da_data_ch' AND '$a_data_ch')";
        }
        if ($Da_rich && $A_rich) {
            $sql .= " AND (GESPRA BETWEEN '$Da_rich' AND '$A_rich')";
        }
        if ($Stato_proc == 'C') {
            $sql .= " AND GESDCH <> ''";
        } else if ($Stato_proc == 'A') {
            $sql .= " AND GESDCH = ''";
        }
        if ($Stato_passo == 'A') {
            $sql .= " AND PROPAS.PROINI <> '' AND PROPAS.PROFIN = ''";
        } else if ($Stato_passo == 'C') {
            $sql .= "PROPAS.PROFIN <> ''";
        }
        if ($Responsabile != '') {
            $sql .= " AND (PROGES.GESRES = '$Responsabile')";
        }
//
//        $retVisibilta = $this->GetVisibiltaSportello();
//        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//            $sql .= " AND GESTSP = " . $retVisibilta['SPORTELLO'];
//        }
//        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
//            $sql .= " AND GESSPA = " . $retVisibilta['AGGREGATO'];
//        }
        $sql .= $this->GetWhereVisibilitaSportello();

        if ($aggregato) {
            $sql .= " AND GESSPA = " . $aggregato;
        }
        if ($sportello) {
            $sql .= " AND GESTSP = " . $sportello;
        }
        if ($tipologia) {
            $sql .= " AND GESTIP = " . $tipologia;
        }
        if ($settore) {
            $sql .= " AND GESSTT = " . $settore;
        }
        if ($attivita) {
            $sql .= " AND GESATT = " . $attivita;
        }
        if ($NoteFascicolo) {
            $sql .= " AND " . $this->PRAM_DB->strUpper('GESNOT') . " LIKE '%" . strtoupper($NoteFascicolo) . "%'";
        }
        if ($Oggetto) {
            $sql .= " AND " . $this->PRAM_DB->strLower('GESOGG') . " LIKE '%" . strtolower($Oggetto) . "%'";
        }
        if ($procedimento) {
            $sql .= " AND GESPRO = '$procedimento'";
        }

        if ($Stato_allegato != '') {
            $sql .= " AND GESNUM IN (SELECT " . $this->getPRAMDB()->subString('PASKEY', 1, 10) . " FROM PASDOC WHERE PASDOC.PASSTA = '$Stato_allegato')";
        }
        if ($articolo != '') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPAS.PROPCONT LIKE '%$articolo%')";
        }
        if ($tipoArticolo == 'T') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPAS.PROPART = 1)";
        }
        if ($tipoArticolo == 'I') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPDADATA<>'' AND PROPADDATA<>'' AND PROPDADATA <= $D_gio AND PROPADDATA >= $D_gio)";
        }
        if ($tipoArticolo == 'S') {
            $sql .= " AND GESNUM IN (SELECT PRONUM FROM PROPAS WHERE PROPADDATA <> '' AND PROPADDATA < $D_gio)";
        }

        if ($NumProt) {
            if ($AnnoProt == "") {
//                $sql .= " AND (".$this->getPRAMDB()->subString('COMPRT',5)." = '$NumProt' OR SUBSTR(PROGES.GESNPR,5) = '$NumProt' OR
//                               ".$this->getPRAMDB()->subString('COMPRT',5)." = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "' OR
//                               ".$this->getPRAMDB()->subString('COMPRT',5)." = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
                $sql .= " AND (SUBSTR(COMPRT,5) = '$NumProt' OR SUBSTR(PROGES.GESNPR,5) = '$NumProt' OR
                               SUBSTR(COMPRT,5) = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "' OR
                               SUBSTR(COMPRT,5) = '" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
            } else {
                $sql .= " AND (PRACOM1.COMPRT = '$AnnoProt$NumProt' OR PROGES.GESNPR = '$AnnoProt$NumProt' OR
                               PRACOM1.COMPRT='$AnnoProt" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "' OR 
                               PROGES.GESNPR='$AnnoProt" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
            }
        }
        $sql .= " GROUP BY GESNUM"; // Per non far vedere pratiche doppie a colpa della join con ANADES

        $sql .= ") P ";
        if ($maggGiorni) {
            $sql .= " WHERE P.NUMEROGIORNI > $maggGiorni";
        }

        $sql .= ") U ";
        if ($where_assegnatario) {
            $sql .= " $where_assegnatario";
        }

        if ($flagAssegnazioni) {
            $codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
            $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
            if ($Utenti_rec['UTEANA__3'] && $codRespAss != $Utenti_rec['UTEANA__3']) {
                $whereFascicoli = " WHERE 
                        (Y.GESRES = '{$Utenti_rec['UTEANA__3']}' OR
                        Y.FL_PRORPA = '{$Utenti_rec['UTEANA__3']}' OR
                        Y.ULT_RESP = '{$Utenti_rec['UTEANA__3']}') OR
                        Y.N_ASSEGNAZIONI = 0";
                $sql = "
            SELECT
                *
            FROM 
            (
                SELECT
                    *,
                    (SELECT COUNT(*) FROM PROPAS WHERE PRONUM=X.GESNUM AND PROOPE<>'') AS N_ASSEGNAZIONI,
                    (SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM=X.GESNUM AND PROOPE<>'')) AS ULT_RESP,
                    (SELECT PRORPA FROM PROPAS WHERE PRONUM=X.GESNUM AND PRORPA = {$Utenti_rec['UTEANA__3']} AND PROOPE='' GROUP BY PRORPA) AS FL_PRORPA
                FROM
                (
                    $sql
                ) X
            ) Y 
              $whereFascicoli";
            }
        }
        return $sql;
    }

    function AggiungiPassoAltreFunzioni($Proges_rec, $tipo) {
        switch ($tipo) {
            case "COMM":
                $desc = "Collegamento al Commercio";
                break;
            case "GAFIERE":
                $desc = "Inserimento Domanda sulla Fiera";
                break;
            default:
                break;
        }
//
        $profilo = proSoggetto::getProfileFromIdUtente();
//
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PRODPA'] = $desc;
        $Propas_rec['PRORPA'] = $profilo['COD_ANANOM'];
        $Propas_rec['PROPAK'] = $this->PropakGenerator($Propas_rec['PRONUM']);
        $Propas_rec['PROINI'] = date("Ymd");
        $Propas_rec['PROFIN'] = date("Ymd");
        $Propas_rec['PROUTEADD'] = $profilo['UTELOG'];
        $Propas_rec['PROUTEEDIT'] = $profilo['UTELOG'];
        $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date("H:i:s");
        $Propas_rec['PROVISIBILITA'] = "Protetto";
//
        try {
            $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PROPAS", 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
        if (!$this->ordinaPassi($Proges_rec['GESNUM'])) {
            Out::msgStop("Errore Ordinamento Passo", $this->getErrMessage());
        }
        return true;
    }

    function PrenotaProgressivoDaTipologia($rowid, $gesnum, $workYear) {
        $retLock = $this->bloccaProgressivoTipologia($rowid);
        if (!$retLock) {
            $this->setErrCode(-1);
            $this->setErrMessage("Accesso esclusivo al progressivo tipologia fallito.");
            return false;
        }
        $progressivo = $this->leggiProgressivoTipologia($rowid, $gesnum, $workYear);
        if ($progressivo == false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Record Tipologia selezionata non più disponibile.");
            return false;
        }
        if (!$this->aggiornaProgressivoTipologia($rowid, $progressivo)) {
            $this->sbloccaProgressivoTipologia($retLock);
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento progressivo Tipologia fallito.</br>Contattare l'assistenza Software.");
            return false;
        }
        $this->sbloccaProgressivoTipologia($retLock);
        return $progressivo;
    }

    function lockAnadoctipreg($rowid) {
        $retLock = ItaDB::DBLock($this->getPRAMDB(), "ANADOCTIPREG", $rowid, "", 120);
        if ($retLock['status'] != 0) {
            $this->setErrCode(-1);
            $this->setErrMessage("Blocco Tabella TIPOLOGIE PROGRESSIVI non Riuscito.");
            return false;
        }
        return $retLock;
    }

    function unlockAnadoctipreg($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            return false;
        }
    }

    function bloccaProgressivoTipologia($rowid) {
        $retLock = $this->lockAnadoctipreg($rowid);
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    function leggiProgressivoTipologia($rowid, $gesnum, $workYear) {
        $Anadoctipreg_rec = $this->GetAnadoctipreg($rowid, "rowid");
        if (!$Anadoctipreg_rec) {
            return false;
        } else {
            if ($Anadoctipreg_rec['TIPOPDOCPROG'] == "Annuale") {
                $Propas_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT MAX(PRODOCPROG) AS ULTIMO FROM PROPAS WHERE PRODOCTIPREG = '" . $Anadoctipreg_rec['CODDOCREG'] . "' AND PRODOCANNO = '$workYear'", false);
                if ($Propas_rec['ULTIMO'] == "") {
                    //if (substr($gesnum, 0, 4) != $workYear) {
                    $progressivo = 1; //Se cambia l'anno ricomincio da 1
                } else {
                    $progressivo = $Anadoctipreg_rec['ULTPROGDOCREG'] + 1;
                }
            } else {
                $progressivo = $Anadoctipreg_rec['ULTPROGDOCREG'] + 1;
            }
        }
        return $progressivo;
    }

    function aggiornaProgressivoTipologia($rowid, $progressivo) {
        $Anadoctipreg_rec = $this->GetAnadoctipreg($rowid, "rowid");
        if (!$Anadoctipreg_rec) {
            return false;
        } else {
            $Anadoctipreg_rec['ULTPROGDOCREG'] = $progressivo;
            $nupd = ItaDB::DBUpdate($this->getPRAMDB(), 'ANADOCTIPREG', 'ROWID', $Anadoctipreg_rec);
            if ($nupd == -1) {
                return false;
            }
        }
        return true;
    }

    function sbloccaProgressivoTipologia($retLock) {
        return $this->unlockAnadoctipreg($retLock);
    }

    /**
     *
     * Definisce i dati di classificazione principali in funzione dei dati che
     * arrivano al F.O.:
     *
     * - Codice Sportello
     * - Tipologia Procedimento
     * - Settore Commerciale
     * - Attivita Commerciale
     * - Descrizione Procedimento
     * - Evento
     * - Tipo Segnalazione
     *
     * @param type $dati
     * @param type $proges_rec
     * @return type
     */
    function GetClassificazioneFascicolo($dati, $proges_rec) {



        /*
         * VERIFICARE E TESTARE CHE SE PRESENTI I DATI DI CLASSIFICAZIONE PREIMPOSTATI  NON DEVE ATTIVARE LE REGOLE DI CLASSIFICAZIONE
         *
         *
         *
         */



        if ($dati['PRORIC_REC']) {
            if ($dati['PRORIC_REC']['RICSTT'] != 0 && $dati['PRORIC_REC']['RICATT'] != 0) {
                $proges_rec['GESTSP'] = $dati['PRORIC_REC']['RICTSP'];
                $proges_rec['GESTIP'] = $dati['PRORIC_REC']['RICTIP'];
                $proges_rec['GESSTT'] = $dati['PRORIC_REC']['RICSTT'];
                $proges_rec['GESATT'] = $dati['PRORIC_REC']['RICATT'];
                $proges_rec['GESDESCR'] = $dati['PRORIC_REC']['RICDESCR'];
//Assegno il codice evento e segnalazione comunica per statistiche
                $proges_rec['GESEVE'] = $dati['PRORIC_REC']['RICEVE'];
                $proges_rec['GESSEG'] = $dati['PRORIC_REC']['RICSEG'];

//                    /*
//                     * Se sportello condizionato cambio il valore
//                     */
//                    $anatsp_rec = $this->GetAnatsp($dati['PRORIC_REC']['RICTSP']);
//                    if ($anatsp_rec['TSPSCO'] == 1) {
//                        $proges_rec['GESSOR'] = $dati['PRORIC_REC']['RICTSP'];
//                        $ricdag_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM RICDAG WHERE RICNUM = '" . $dati['PRORIC_REC']['RICNUM'] . "' AND DAGKEY = 'TIPO_SPORTELLO'", false);
//                        $proges_rec['GESTSP'] = $anatsp_rec['TSPSDE'];
//                        if ($ricdag_rec && $ricdag_rec['RICADAT']) {
//                            $proges_rec['GESTSP'] = $ricdag_rec['RICDAT'];
//                        }
//                    }
            } else {

                /*
                 * Verificare
                 *
                 *
                 */
                if ($proges_rec['GESEVE']) {
                    $iteevt_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $proges_rec['GESPRO'] . "' AND IEVCOD = '" . $dati['PRORIC_REC']['RICEVE'] . "'", false);
                } else {
                    $iteevt_rec = $this->GetIteevt($proges_rec['GESPRO']);
                }
                $proges_rec['GESTSP'] = $iteevt_rec['IEVTSP'];
                $proges_rec['GESTIP'] = $iteevt_rec['IEVTIP'];
                $proges_rec['GESSTT'] = $iteevt_rec['IEVSTT'];
                $proges_rec['GESATT'] = $iteevt_rec['IEVATT'];
                $proges_rec['GESDESCR'] = $iteevt_rec['IEVDESCR'];
//Assegno il codice evento e segnalazione comunica per statistiche
                $proges_rec['GESEVE'] = $iteevt_rec['IEVCOD'];
                $anaeventi_rec = $this->GetAnaeventi($iteevt_rec['IEVCOD']);
                $proges_rec['GESSEG'] = $anaeventi_rec['EVTSEGCOMUNICA'];
            }
        } else {
            if ($dati["ITEEVT_REC"]) {
                $proges_rec['GESTSP'] = $dati["ITEEVT_REC"]['IEVTSP'];
                $proges_rec['GESTIP'] = $dati["ITEEVT_REC"]['IEVTIP'];
                $proges_rec['GESSTT'] = $dati["ITEEVT_REC"]['IEVSTT'];
                $proges_rec['GESATT'] = $dati["ITEEVT_REC"]['IEVATT'];
                $proges_rec['GESDESCR'] = $dati["ITEEVT_REC"]['IEVDESCR'];
//Assegno il codice evento e segnalazione comunica per statistiche
                $proges_rec['GESEVE'] = $dati["ITEEVT_REC"]['IEVCOD'];
                $anaeventi_rec = $this->GetAnaeventi($dati["ITEEVT_REC"]['IEVCOD']);
                $proges_rec['GESSEG'] = $anaeventi_rec['EVTSEGCOMUNICA'];
            }
        }
//}

        /*
         * Se sportello condizionato cambio il valore
         */

        /*
         *
         * FIX-ME: VA FATTO IN POST ELABORAZIONE !!!!!!
         *
         */

        $anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
        if ($anatsp_rec['TSPSCO'] == 1) {
            $proges_rec['GESSOR'] = $proges_rec['GESTSP'];

            /*
             *
             *
             * TODO: ANALISI DATI AGGIUNTIVI VA FATTA IN POST-ELABORAZIONE
             */
            $ricdag_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['PRORIC_REC']['RICNUM'] . "' AND DAGKEY = 'TIPO_SPORTELLO' AND RICDAT<>''", false);
            $proges_rec['GESTSP'] = $anatsp_rec['TSPSDE'];
            if ($ricdag_rec) {
                $proges_rec['GESTSP'] = $ricdag_rec['RICDAT'];
            }
        }
        return $proges_rec;
    }

    function DecodCalendar($rowid, $classe) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
        $envLibCalendar = new envLibCalendar();
        $event_rec = $envLibCalendar->getAppEvents($classe, $rowid, false);
        return $event_rec['ROWID_CALENDARIO'];
    }

    function GetHtmlLogRicevutePasso($valoreRetRicevute) {
        $html = "";
        if ($valoreRetRicevute['errPraticaParam']) {
            $filent_rec = $this->GetFilent(28);
            $html .= "<div style=\"overflow:auto; max-height:400px; max-width:600px;;margin:6px;padding:6px;\" class=\"ita-box ui-state-error ui-corner-all ita-Wordwrap\">";
            $html .= "ATTENZIONE!!<br>Il numero di pratica fornito nei parametri Suap, <b>" . $filent_rec['FILDE2'] . "-" . $filent_rec['FILDE3'] . "</b>, non è stato trovato nell'elenco.<br><b>La protocollazione automatica delle ricevute non è stata eseguita</b>";
            $html .= "</div>";
            return $html;
        }
        $html .= "<div style=\"text-decoration:underline;padding-bottom:3px;\"><b>PROTOCOLLAZIONE AUTOMATICA RICEVUTE</b></div>";
        $html .= "<div>Passi Elaborati: <b>" . $valoreRetRicevute['Totali']["PassiEstratti"] . "</b></div>";
        $html .= "<div>Passi con esito positivo: <b>" . $valoreRetRicevute['Totali']["PassiEsitoPos"] . "</b></div>";
        $html .= "<div>Passi con esito negativo: <b>" . $valoreRetRicevute['Totali']["PassiEsitoNeg"] . "</b></div>";
        $html .= "<br>";
        foreach ($valoreRetRicevute['DettaglioEsito'] as $compak => $value) {
            $propas_rec = $this->GetPropas($compak, "propak");
            $html .= "<div class=\"ita-box ui-corner-all ita-Wordwrap\" style=\"margin-bottom:5px;padding:4px;background-color:white;\">";
            $html .= "<b>Pratica N. " . $propas_rec['PRONUM'] . " Passo " . $propas_rec['PROSEQ'] . ": " . $propas_rec['PRODPA'] . "</b>";
            /*
             * Mi scorro i RetDetails
             */
            foreach ($value['RetDetails'] as $detail) {
//$html .= $detail;
                $html .= "<div style=\"padding:2px;\">- $detail </div>";
            }
            /*
             * Mi scorro gli ErrDetails
             */
            foreach ($value['ErrDetails'] as $errDetail) {
//$html .= $errDetail;
                $html .= "<div class=\"ui-state-error ui-corner-all\" style=\"padding:2px;margin-bottom:1px;\">- $errDetail </div>";
            }
            $html .= "</div>";
        }
        return $html;
    }

    public function GetRichiesteAccorpate($PRAM_DB, $procedimento) {
        $sql = "SELECT
                    PRORIC.*,
                    " . $PRAM_DB->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES
                FROM
                    PRORIC
                LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO = ANAPRA.PRANUM
                WHERE
                    PRORIC.RICRUN = '$procedimento'";

        return ItaDB::DBSQLSelect($PRAM_DB, $sql);
    }

    public function scollegaDaPraticaUnica($PRAM_DB, $RICNUM) {
        $proric_rec = $this->GetProric($RICNUM);

        if (!$proric_rec) {
            return false;
        }

        $proric_rec['RICRUN'] = '';

        if (!ItaDB::DBUpdate($PRAM_DB, 'PRORIC', 'ROWID', $proric_rec)) {
            return false;
        }

        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT ROWID, RICDAT FROM RICDAG WHERE DAGNUM = '$RICNUM' AND ( DAGKEY = 'RICHIESTA_UNICA' OR DAGKEY = 'RICHIESTA_UNICA_FORMATTED' )");

        foreach ($ricdag_tab as $ricdag_rec) {
            $ricdag_rec['RICDAT'] = '';
            if (!ItaDB::DBUpdate($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec)) {
                return false;
            }
        }

        return true;
    }

    function GetTariffaPasso($ricite_rec) {
        /*
         * Controllo se il passo è un upload
         * e se è abilitato il pagamento
         */

        if (($ricite_rec['ITEUPL'] == 1 || $ricite_rec['ITEMLT'] == 1) && $ricite_rec['ITEPAY'] == 1) {
            $proric_rec = $this->GetProric($ricite_rec['RICNUM'], 'codice', $this->getPRAMDB());

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

            $itelisval_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);

            if (!$itelisval_rec) {
                return false;
            }

            $CODVAL = $itelisval_rec['CODLISVAL'];

            $src_codicesportello = $proric_rec['RICTSP'];
            $src_settore = $proric_rec['RICSTT'];
            $src_attivita = $proric_rec['RICATT'];
            $src_procedimento = $ricite_rec['ITECOD'];
            $src_evento = $proric_rec['RICEVE'];
            $src_tipopasso = $ricite_rec['ITECLT'];
//            $src_itekey = $ricite_rec['ITEKEY'];

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

            $itelis_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);

            foreach ($itelis_tab as $itelis_rec) {
                if (
                        ($itelis_rec['CODICESPORTELLO'] == $src_codicesportello || $itelis_rec['CODICESPORTELLO'] == '0') &&
                        ($itelis_rec['SETTORE'] == $src_settore || $itelis_rec['SETTORE'] == '0') &&
                        ($itelis_rec['ATTIVITA'] == $src_attivita || $itelis_rec['ATTIVITA'] == '0') &&
                        (($itelis_rec['PROCEDIMENTO'] == $src_procedimento && $itelis_rec['PROCEDIMENTO'] != '') || $itelis_rec['PROCEDIMENTO'] == '*') &&
                        (($itelis_rec['EVENTO'] == $src_evento && $itelis_rec['EVENTO'] != '') || $itelis_rec['EVENTO'] == '*') &&
                        (($itelis_rec['TIPOPASSO'] == $src_tipopasso && $itelis_rec['TIPOPASSO'] != '') || $itelis_rec['TIPOPASSO'] == '*')
//                      && (($itelis_rec['ITEKEY'] == $src_itekey && $itelis_rec['ITEKEY'] != '') || $itelis_rec['ITEKEY'] == '*')
                ) {
                    return $itelis_rec;
                }
            }
        }

        return false;
    }

    public function GetAllegatiPratica($ricnum) {
        $Ricdoc_tab = $this->GetRicdoc($ricnum, "codice", true);
        $pathAllegatiRichiste = $this->getPathAllegatiRichieste();
        $listAllegati = $this->GetFileListCtrRichieste($pathAllegatiRichiste . "attachments/" . $ricnum);
        if ($listAllegati) {

//
//Rimuovo dall'array gli allegati che non verranno importati (
//
            foreach ($listAllegati as $key1 => $allegato) {
                if (strpos("|info|html|txt|", "|" . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION) . "|") !== false) {
                    unset($listAllegati[$key1]);
                }
                if (strpos($allegato['FILENAME'], "rapporto") != false) {
                    unset($listAllegati[$key1]);
                }
// Se ci sono P7M sbiustati tolgo il file contenuto che mi interessa
                $Ricdoc_rec_alle = $this->GetRicdoc($allegato['FILENAME'], "allegato");
                if ($Ricdoc_rec_alle && $Ricdoc_rec_alle['DOCFLSERVIZIO'] == 1) {
                    unset($listAllegati[$key1]);
                }
            }
        }

        $allegati = array();
        if ($Ricdoc_tab) {
            foreach ($listAllegati as $allegato) {
                $ext = strtolower(pathinfo($allegato['FILEINFO'], PATHINFO_EXTENSION));
                if ($ext == "xml") {
                    $allegati[] = array(
                        'DATAFILE' => $allegato['DATAFILE'],
                        'FILENAME' => $allegato['FILENAME'],
                        'FILEINFO' => $allegato['FILEINFO'],
                        'FIRMA' => ""
                    );
                }

                foreach ($Ricdoc_tab as $Ricdoc_rec) {
                    if ($Ricdoc_rec['DOCUPL'] == $allegato['FILEINFO']) {
                        $firma = "";
                        if ($ext == "p7m") {
                            $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                        }
                        $allegati[] = array(
                            'DATAFILE' => $allegato['DATAFILE'],
                            'FILENAME' => $Ricdoc_rec['DOCNAME'],
                            'FILEINFO' => $Ricdoc_rec['DOCUPL'],
                            'FIRMA' => $firma
                        );
                        break;
                    }
                }
            }
        } else {
            $allegati = $listAllegati;
        }
        return $allegati;
    }

    function getPathAllegatiRichieste() {
        return str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
    }

    function GetFileListCtrRichieste($filePath) {
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
                'DATAFILE' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    function GetDataProtNormalizzata($meta) {
        $arrayDati = array();
        $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = $meta['DatiProtocollazione']['TipoProtocollo']['value'];
        $arrayDati['RetValue']['DatiProtocollo']['Data'] = $meta['DatiProtocollazione']['Data']['value']; //formato 20160101
        if ($meta['DatiProtocollazione']['DataDoc']['value']) {
            $arrayDati['RetValue']['DatiProtocollo']['Data'] = $meta['DatiProtocollazione']['DataDoc']['value']; //formato 20160101
        }
        $arrayNormalizzato = proIntegrazioni::NormalizzaArray($arrayDati);
        return substr($arrayNormalizzato['RetValue']['DatiProtocollo']['Data'], 6, 2) . "-" . substr($arrayNormalizzato['RetValue']['DatiProtocollo']['Data'], 4, 2) . "-" . substr($arrayNormalizzato['RetValue']['DatiProtocollo']['Data'], 0, 4);
    }

    public function getNextPropas($keypasso) {
        $propas_rec = $this->GetPropas($keypasso);
        $currGesnum = $propas_rec['PRONUM'];
        $proges_rec = $this->GetProges($currGesnum);
        if ($proges_rec['PROKPRE'] == '') {
            $sql = " 
                SELECT * FROM PROPAS WHERE PRONUM='$currGesnum' AND PROKPRE= '' AND PROSEQ>{$propas_rec['PROSEQ']} ORDER BY PROSEQ;
            ";
            $nextPropas_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
        }
        return $nextPropas_rec;
    }

    function creaComboTipiProt($idCampo) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
        $arrProt = proWSClientHelper::getElencoProtocolliRemoti();
        Out::select($idCampo, 1, "", "1", "");
        foreach ($arrProt as $prot) {
            Out::select($idCampo, 1, $prot, "1", $prot);
        }
    }

    function getArrayIstanze($nameClass) {
        $istanze = array();
        if ($nameClass) {

            /*
             * Se il parametro è multiistanza, mi trovo le istanze altrimenti torno l'array vuoto
             */
            $devLib = new devLib();
            $configIni_tab = $devLib->getIta_config_ini($nameClass, "codice", false);
            if ($configIni_tab['MULTIISTANZA'] == 1) {
                $envLib = new envLib();
                $istanze = $envLib->getIstanze($nameClass);
            }
        }
        return $istanze;
    }

    function validatorFields($driver, $param) {
        switch ($driver) {
            case proWsClientHelper::CLIENT_PALEO4:
                if ($param["Docnumber"] == "" && $param["Segnatura"] == "") {
                    return "Compilare id documento o segnatura Protocollo";
                }
                $proges_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT GESNUM,GESMETA,GESNPR FROM PROGES WHERE GESMETA <> '' AND GESMETA LIKE '%$driver%'", true);
                foreach ($proges_tab as $proges_rec) {
                    $metadati = unserialize($proges_rec['GESMETA']);
                    $docnumber = $metadati['DatiProtocollazione']['DocNumber']['value'];
                    $segnatura = $metadati['DatiProtocollazione']['Segnatura']['value'];
                    if (($docnumber && $docnumber == $param["Docnumber"]) || ($segnatura && $segnatura == $param["Segnatura"])) {
                        $anno = substr($proges_rec['GESNUM'], 0, 4);
                        $numero = substr($proges_rec['GESNUM'], 4);
                        $annoProt = substr($proges_rec['GESNPR'], 0, 4);
                        $numProt = substr($proges_rec['GESNPR'], 4);
                        return "Il protocollo n. $numProt/$annoProt con idDoc " . $param["Docnumber"] . " risulta già assegnato alla pratica " . $numero . "/" . $anno;
                    }
                }
                $pracom_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT COMNUM,COMMETA,COMPRT FROM PRACOM WHERE COMMETA <> ''  AND COMMETA LIKE '%$driver%'", true);
                foreach ($pracom_tab as $pracom_rec) {
                    $metadati = unserialize($pracom_rec['COMMETA']);
                    $docnumber = $metadati['DatiProtocollazione']['DocNumber']['value'];
                    $segnatura = $metadati['DatiProtocollazione']['Segnatura']['value'];
                    if (($docnumber && $docnumber == $param["Docnumber"]) || ($segnatura && $segnatura == $param["Segnatura"])) {
                        $anno = substr($pracom_rec['COMNUM'], 0, 4);
                        $numero = substr($pracom_rec['COMNUM'], 4);
                        $pa = $pracom_rec['COMTIP'] == "P" ? "PARTENZA" : "ARRIVO";
                        $annoProt = substr($pracom_rec['COMPRT'], 0, 4);
                        $numProt = substr($pracom_rec['COMPRT'], 4);
                        return "Il protocollo n. $numProt/$annoProt con idDoc " . $param["Docnumber"] . " risulta già assegnato alla comunicazione in $pa della pratica " . $numero . "/" . $anno;
                    }
                }
                break;
            case proWsClientHelper::CLIENT_IRIDE:
            case proWsClientHelper::CLIENT_JIRIDE:
                if ($param["NumeroProtocollo"] == "" || $param["AnnoProtocollo"] == "") {
                    return "Compilare Numero e Anno Protocollo";
                }
                $sql = "SELECT * FROM PROGES WHERE "
                        . " (GESNPR = " . $param["AnnoProtocollo"] . $param["NumeroProtocollo"] . ""
                        . " OR GESNPR = " . $param["AnnoProtocollo"] . str_pad($param["NumeroProtocollo"], 6, "0", STR_PAD_LEFT) . ")";
                $find_proges = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
                if ($find_proges) {
                    $anno = substr($find_proges['GESNUM'], 0, 4);
                    $numero = substr($find_proges['GESNUM'], 4);
                    return "Il protocollo cercato risulta già assegnato alla pratica " . $numero . "/" . $anno;
                }
                $sql = "SELECT * FROM PRACOM WHERE "
                        . " (COMPRT = " . $param["AnnoProtocollo"] . $param["NumeroProtocollo"] . ""
                        . " OR COMPRT = " . $param["AnnoProtocollo"] . str_pad($param["NumeroProtocollo"], 6, "0", STR_PAD_LEFT) . ")";
                $find_pracom = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
                if ($find_pracom) {
                    $anno = substr($find_pracom['COMPAK'], 0, 4);
                    $numero = substr($find_pracom['COMPAK'], 4, 6);
                    $pa = $find_pracom['COMTIP'] == "P" ? "PARTENZA" : "ARRIVO";
                    return "Il protocollo cercato risulta già assegnato alla comunicazione in $pa della pratica " . $numero . "/" . $anno;
                }
                break;
        }
    }

    public function getDatiProtocolloAggregato($aggregato) {
        $arrayDati = array();

        /*
         * Verifico se l'aggregato del fascicolo è impostato per protocollare.
         * In quel caso prendo i parametri di quell'aggregato
         */
        $anaspa_rec = $this->GetAnaspa($aggregato);
        if ($anaspa_rec['SPATIPOPROT']) {
            $arrayDati['TipoProtocollo'] = $anaspa_rec['SPATIPOPROT'];
        }

        /*
         * dai metadati estraggo l'array che contiene l'istanza di protocollazione
         */
        if ($anaspa_rec['SPAMETAPROT']) {
            $arrayDati['MetadatiProtocollo'] = unserialize($anaspa_rec['SPAMETAPROT']);
        }
        return $arrayDati;
    }

    public function getAltriParametriBO($pranum, $tipo = '') {
        /*
         * dato un'anagrafica procedimento 
         * restituisce i suoi parametri per la gestione dei campi, dati aggiuntivi.
         */
        if ($tipo) {
            $sql = "SELECT $tipo FROM PARAMBO WHERE PRANUM = '$pranum'";
        } else {
            $sql = "SELECT * FROM PARAMBO WHERE PRANUM = '$pranum'";
        }

        $parambo_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);

        if (!$parambo_rec) {
            return false;
        }

        return $tipo !== '' ? $parambo_rec[$tipo] : $parambo_rec;
    }

//    function GetPraticaAntecedente($gesnum, $gespra) {
    function GetPraticaAntecedente($gesnum, $proric_rec) {
        $proges_recAntecedente = array();
        //$proric_rec = $this->GetProric($gespra);

        /*
         * Verifico se c'è il flag pratica collegata
         */
        if ($proric_rec && $proric_rec['RICPC'] == "1") {

            /*
             * Trovo il record del numero richiesta on-line antecedente
             */
            $prodag_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND (DAGKEY = 'PRATICA_INIZIALE' OR DAGKEY = 'RICHIESTA_PADRE_VARIANTE') AND DAGVAL<>''", false);
            if ($prodag_rec) {

                /*
                 * Trovo il record del fascicolo antecedente
                 */
                $proges_recAntecedente = $this->GetProges($prodag_rec['DAGVAL'], "richiesta");
            }
        }
        return $proges_recAntecedente;
    }

    function setDestinatarioProtocollo($gesres) {
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        //$proLib = new proLib();
        //
        $ananom_rec = $this->GetAnanom($gesres);
        $anamed_rec = $this->proLib->GetAnamed($ananom_rec['NOMDEP'], 'codice', 'no');
        $destinatario = array();
        if ($anamed_rec) {
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $destinatario['CodiceDestinatario'] = $anamed_rec['MEDCOD'];
                    $destinatario['Denominazione'] = $anamed_rec['MEDNOM'];
                    $destinatario['Indirizzo'] = $anamed_rec['MEDIND'];
                    $destinatario['CAP'] = $anamed_rec['MEDCAP'];
                    $destinatario['Citta'] = $anamed_rec['MEDCIT'];
                    $destinatario['Provincia'] = $anamed_rec['MEDPRO'];
                    $destinatario['Annotazioni'] = $anamed_rec['MEDNOTE'];
                    $destinatario['Email'] = $anamed_rec['MEDEMA'];
                    $destinatario['Ufficio'] = $anauff_rec['UFFCOD'];
                    break;
                }
            }
        }
        return $destinatario;
    }

    function setUfficiProtocollo($uffkey) {
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        //$proLib = new proLib();
        //
        $uffici = array();
        $uffdes_tab = $this->proLib->GetUffdes($uffkey, 'uffkey');
        foreach ($uffdes_tab as $uffdes_rec) {
            $ufficio = array();
            $ufficio['CodiceUfficio'] = $uffdes_rec['UFFCOD'];
            $ufficio['Scarica'] = $uffdes_rec['UFFSCA'];
            $uffici[] = $ufficio;
        }
        return $uffici;
    }

    function getColorNameAllegato($pasevi) {
        $color = "";
        if ($pasevi == 1) {
            $color = "red";
        } elseif ($pasevi != 1 && $pasevi != 0 && !empty($pasevi)) {
            $color = '#' . str_pad(dechex($pasevi), 6, "0", STR_PAD_LEFT);
        }
        return $color;
    }

    function getIconCds($value, $flCds, $pasTipo = "") {
        if ($flCds == 1) {
            include_once(ITA_LIB_PATH . "/itaPHPCore/itaComponents.class.php");
            if ($pasTipo == "FIR_CDS") {
                return '<span title="Firmato CDS">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-group-32x32', 'ita-icon ita-icon-shield-blue-24x24', 18) . '</span>';
            } elseif ($pasTipo == "FIR_CDS_DEFINITIVO") {
                return '<span title="Definitivo CDS">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-group-32x32', 'ita-icon ita-icon-check-green-24x24', 18) . '</span>';
            }
            if ($value == 1) {
                return '<span title="Disattiva CDS">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-group-32x32', 'ita-icon ita-icon-check-orange-24x24', 18) . '</span>';
            } else {
                return '<span title="Attiva CDS">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-group-32x32', 'ita-icon ita-icon-divieto-24x24', 18) . '</span>';
            }
        }
    }

    function GetOrdinamentoGridGest($ordinamento, $sord) {
        if ($ordinamento == 'PRIORITA_RICH') {
            if ($sord == 'desc') {
                $tmpSord = "DESC";
                $sord = 'desc';
            } else {
                $tmpSord = "ASC";
                $sord = 'asc';
            }
            $ordinamento = "PRIORITA_RICH $tmpSord, GESNUM";
        }
        if ($ordinamento == 'RICEZ') {
            $ordinamento = "GESDRI";
        }
        if ($ordinamento == 'GESNUM') {
            $ordinamento = "SERIEANNO $sord, SERIEPROGRESSIVO";
        }
        if ($ordinamento == 'DESCPROC') {
            $ordinamento = "PRADES__1";
        }
        if ($ordinamento == 'NOTE') {
            $ordinamento = "GESNOT";
        }
        if ($ordinamento == 'GIORNI') {
            $ordinamento = "NUMEROGIORNI";
        }
        if ($ordinamento == 'SPORTELLO') {
            $ordinamento = "GESTSP";
        }
        return array("sidx" => $ordinamento, "sord" => $sord);
    }

    function GetOggettoProtPartenza($gesnum, $keypasso) {
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->GetFilent(4);
        $praLibVar->setCodicePratica($gesnum);
        $praLibVar->setChiavePasso($keypasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        return $this->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
    }

    public function getFlagPASDWONLINE($value) {
        include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

        if ($value == 1) {
            return '<span title="Disattiva accesso online">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-qrcode-32x32', 'ita-icon ita-icon-check-green-24x24', 18) . '</span>';
        } else {
            return '<span title="Attiva accesso online">' . itaComponents::getHtmlSideIcon('ita-icon ita-icon-qrcode-32x32', 'ita-icon ita-icon-divieto-24x24', 18) . '</span>';
        }
    }

    public function decodParametriPasso($MetaPanel, $tipo = 'decode', $array = true) {
        // ritorna array contenente i parametri
        $parametro = json_decode($MetaPanel, $array);
        if ($tipo == 'decode') {
            return $parametro;
        }
        // elabora i parametri i parametri prendendo l'anagrafica dalla classe 
        $valueAnagrafica = praLibPasso::$PANEL_LIST;
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

    public function getParamCaricaDatiAggPassi() {
        $filent_rec = $this->GetFilent(44);
        return $filent_rec['FILVAL'];
    }

    public function ordinaPassiConAntecedenti($passi_view) {
        foreach ($passi_view as $keyPasso => $passo) {
            $passi_view[$keyPasso]['ORDERANT'] = str_pad($passo['PROSEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['PROKPRE'], 4, "0", STR_PAD_LEFT);
            if ($passo['PROKPRE']) {
                $propas_recAnt = $this->GetPropas($passo['PROKPRE'], "propak");
                $passi_view[$keyPasso]['ORDERANT'] = str_pad($propas_recAnt['PROSEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['PROSEQ'], 4, "0", STR_PAD_LEFT);
            }
        }
        return $this->array_sort($passi_view, "ORDERANT");
    }

    public function ribaltaCondizioniSaltaPasso($passoFascicolo) {
        $sql = "SELECT * FROM ITEVPADETT WHERE ITECOD = '{$passoFascicolo['PROPRO']}' AND ITEKEY = '{$passoFascicolo['PROITK']}'";
        $itevpadett_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql);

        foreach ($itevpadett_tab as $itevpadett_rec) {
            $sql = "SELECT * FROM PROPAS WHERE PRONUM = '{$passoFascicolo['PRONUM']}' AND PROITK = '{$itevpadett_rec['ITEVPA']}'";
            $passoFascicoloVaiA = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, false);
            if (!$passoFascicoloVaiA) {
                continue;
            }

            $provpadett_rec = array();
            $provpadett_rec['PRONUM'] = $passoFascicolo['PRONUM'];
            $provpadett_rec['PROPRO'] = $passoFascicolo['PROPRO'];
            $provpadett_rec['PROPAK'] = $passoFascicolo['PROPAK'];
            $provpadett_rec['PROSEQEXPR'] = $itevpadett_rec['ITESEQEXPR'];
            $provpadett_rec['PROVPA'] = $passoFascicoloVaiA['PROPAK'];
            $provpadett_rec['PROEXPRVPA'] = $itevpadett_rec['ITEEXPRVPA'];
            $provpadett_rec['PROVPADESC'] = $itevpadett_rec['ITEVPADESC'];

            try {
                $righeInserite = ItaDB::DBInsert($this->getPRAMDB(), 'PROVPADETT', 'ROW_ID', $provpadett_rec);
                if ($righeInserite != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('ribaltaCondizioniSaltaPasso: inserimento condizione salta passo fallito.');
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('ribaltaCondizioniSaltaPasso: ' . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    public function decodificaImmagineSaltoPasso($qst, $vpa, $vpn, $vpadett_tab) {
        switch ($qst) {
            case 0:
                if ($vpa != '') {
                    return '<div style="text-align: center;" title="Salto a passo"><span class="ui-icon ui-icon-arrow-r" style="font-size: 1.6em; color: green;"></span></div>';
                }
                break;

            case 1:
                if ($vpa != '' || $vpn != '') {
                    return '<div style="text-align: center;" title="Passo domanda"><span class="ui-icon ui-icon-help-plain" style="font-size: 1.2em; color: green;"></span></div>';
                }
                break;

            case 2:
                if (count($vpadett_tab)) {
                    return '<div style="text-align: center; white-space: nowrap;" title="Salto multiplo"><span class="ui-icon ui-icon-arrow-r" style="font-size: 1.6em; color: blue; margin-left: 2px;"></span><span class="ui-icon ui-icon-carat-1-e" style="font-size: 1.6em; color: blue; margin-left: -14px;"></span></div>';
                }
                break;
        }

        return false;
    }

    public function ribaltaDestinatario($Propas_rec, $Itemitdest_rec) {
        $sogg = array();
        $sogg["KEYPASSO"] = $Propas_rec["PROPAK"];
        $sogg["TIPOCOM"] = "D";
        if ($Itemitdest_rec["CODICE"]) {
            //$proLib = new proLib();
            $anamed_rec = $this->proLib->GetAnamed($Itemitdest_rec["CODICE"]);
            $sogg["CODICE"] = $Itemitdest_rec["CODICE"];
            $sogg['NOME'] = $anamed_rec['MEDNOM'];
            $sogg['FISCALE'] = $anamed_rec['MEDFIS'];
            $sogg['INDIRIZZO'] = $anamed_rec['MEDIND'];
            $sogg['COMUNE'] = $anamed_rec['MEDCIT'];
            $sogg['CAP'] = $anamed_rec['MEDCAP'];
            $sogg['DATAINVIO'] = "";
            $sogg['PROVINCIA'] = $anamed_rec['MEDPRO'];
            $sogg['MAIL'] = $anamed_rec['MEDEMA'];
            //
            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRAMITDEST", "ROWID", $sogg);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaDestinatari: inserimento destinatario da ANAMED codice " . $Itemitdest_rec["CODICE"] . " fallito");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("ribaltaDestinatari: " . $exc->getMessage());
                return false;
            }
        } elseif ($Itemitdest_rec["RUOLO"]) {
            $anades_tab = $this->GetAnades($Propas_rec['PRONUM'], "ruolo", true, $Itemitdest_rec["RUOLO"]);
            foreach ($anades_tab as $anades_rec) {
                $sogg['CODICE'] = $anades_rec['DESCOD'];
                $sogg['NOME'] = $anades_rec['DESNOM'];
                $sogg['FISCALE'] = $anades_rec['DESFIS'];
                $sogg['INDIRIZZO'] = $anades_rec['DESIND'] . " " . $anades_rec['DESCIV'];
                $sogg['COMUNE'] = $anades_rec['DESCIT'];
                $sogg['CAP'] = $anades_rec['DESCAP'];
                $sogg['DATAINVIO'] = "";
                $sogg['PROVINCIA'] = $anades_rec['DESPRO'];
                if ($anades_rec['DESPEC']) {
                    $sogg['MAIL'] = $anades_rec['DESPEC'];
                } else {
                    $sogg['MAIL'] = $anades_rec['DESEMA'];
                }
                //
                try {
                    $nrow = ItaDB::DBInsert($this->getPRAMDB(), "PRAMITDEST", "ROWID", $sogg);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("ribaltaDestinatari: inserimento destinatario da ruolo " . $Itemitdest_rec["RUOLO"] . " fallito");
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("ribaltaDestinatari: " . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function getDbSuffix($pranum) {
        $dbsuffix = '';
        $tipoEnte = $this->GetTipoEnte();
        if ($tipoEnte != "M") {
            $Anapra_rec = $this->GetAnapra($pranum);
            if ($Anapra_rec['PRASLAVE'] == 1) {
                $dbsuffix = $this->GetEnteMaster();
            }
        }
        return $dbsuffix;
    }

    function checkEventiCustom($Iteevt_tab) {
        $evtCustom = "";
        $tipoEnte = $this->GetTipoEnte();
        if ($tipoEnte == "M") {
            foreach ($Iteevt_tab as $Iteevt_rec) {
                if ($Iteevt_rec['PEREVT'] == 1) {
                    $evtCustom = $Iteevt_rec['IEVCOD'];
                    break;
                }
            }
        }
        return $evtCustom;
    }

    function setFirmatarioProtocolloDaSportello($proges_rec) {
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->GetAnaspa($proges_rec['GESSPA']);
            if ($Anaspa_rec['SPAFIRMPA']) {
                return $Anaspa_rec['SPAFIRMPA'];
            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec['TSPFIRMPA']) { //sportello
                return $Anatsp_rec['TSPFIRMPA'];
            }
        }
    }

    public function getTipoProtocollo($pratica) {

        /*
         * Leggo la pratica
         */
        $proges_rec = $this->GetProges($pratica);

        /*
         * Inizializzo il tipo di protocollo da istanziare automatico da param. ente
         */
        $tipoProt = proWsClientFactory::getAutoDriver();

        /*
         * Controllo se l'utente ha configurati i parametri per protocollare in altro ente
         */
        $accLib = new accLib();
        $enteProtRec_rec = $accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            if ($meta['TIPO'] && $meta['URLREMOTO']) {
                $tipoProt = $meta['TIPO'];
            }
        }

        /*
         * Controllo se l'utente ha visibilità per aggregato
         */
        if ($proges_rec['GESSPA'] != 0) {
            $retVisibilta = $this->GetVisibiltaSportello();
            if ($retVisibilta['AGGREGATO'] != 0) {
                if ($proges_rec['GESSPA'] == $retVisibilta['AGGREGATO']) {
                    $arrDatiProtAggr = $this->getDatiProtocolloAggregato($proges_rec['GESSPA']);

                    /*
                     * Se c'è il tipo protocolli nell'aggregato, sovrascrivo il tipo protocollo dell'ente
                     */
                    if ($arrDatiProtAggr['TipoProtocollo']) {
                        $tipoProt = $arrDatiProtAggr['TipoProtocollo'];
                    }
                }
            }
        }
        return $tipoProt;
    }

    function GetImgStatoPratica($Result_rec, $returnDesc = false) {
        $desc = "";
        if ($Result_rec['GESDCH']) {
            $prasta_rec = $this->GetPrasta($Result_rec['GESNUM']);
            if ($prasta_rec['STAFLAG'] == "Annullata") {
                $desc = "Pratica Annullata";
                $img = "<span class=\"ita-icon ita-icon-delete-24x24\">$desc</span>";
            } elseif ($prasta_rec['STAFLAG'] == "Chiusa Positivamente") {
                $desc = "Pratica chiusa positivamente";
                $img = "<span class=\"ita-icon ita-icon-check-green-24x24\">$desc</span>";
            } elseif ($prasta_rec['STAFLAG'] == "Chiusa Negativamente") {
                $desc = "Pratica chiusa negativamente";
                $img = "<span class=\"ita-icon ita-icon-check-red-24x24\">$desc</span>";
            }
        } else {

            $sql = $this->CreaSqlCaricaPassi($Result_rec['GESNUM']);
            $Propas_tab = $this->getGenericTab($sql);
            if ($Propas_tab) {
                $passi_BO_aperti = $passi_BO_chiusi = $passi_BO_daAprire = $passi_FO = $passi_BO = array();
                foreach ($Propas_tab as $Propas_rec) {
                    if ($Propas_rec['PROPUB'] == 1) {
                        $passi_FO[] = $Propas_rec;
                    } else {
                        $passi_BO[] = $Propas_rec;
                        if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'] == "") {
                            $passi_BO_aperti[] = $Propas_rec;
                        }
                        if ($Propas_rec['PROINI'] && $Propas_rec['PROFIN']) {
                            $passi_BO_chiusi[] = $Propas_rec;
                        }
                        if ($Propas_rec['PROINI'] == "" && $Propas_rec['PROFIN'] == "") {
                            $passi_BO_daAprire[] = $Propas_rec;
                        }
                    }
                }

                if ($passi_BO == $passi_BO_daAprire) {
                    $desc = "Pratica caricata";
                    $img = "<span class=\"ita-icon ita-icon-chiusagreen-24x24\">$desc</span>";
                }
                if ($passi_BO_aperti) {
                    $desc = "Pratica con passi aperti";
                    $img = "<span class=\"ita-icon ita-icon-apertagray-24x24\">$desc</span>";
                }
                if ($passi_BO_chiusi) {
                    $desc = "Pratica in corso";
                    $img = "<span class=\"ita-icon ita-icon-apertagreen-24x24\">$desc</span>";
                }
            } else {
                $desc = "Passi non Presenti";
                $img = "<span class=\"ita-icon ita-icon-bullet-red-24x24\">$desc</span>";
            }
        }
        if ($returnDesc) {
            return $desc;
        } else {
            return $img;
        }
    }

    public function GetItediaggruppi($codice, $tipoRic = 'rowid') {
        if ($tipoRic == 'rowid') {
            $sql = "SELECT * FROM ITEDIAGGRUPPI WHERE ROW_ID='$codice'";
            $multi = false;
        }
        if ($tipoRic == 'pranum') {
            $sql = "SELECT * FROM ITEDIAGGRUPPI WHERE PRANUM='$codice' ORDER BY ROW_ID";
            $multi = true;
        }
        return ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, $multi);
    }

    public function getArrayTipiFO() {
        $Filent_Rec_48 = $this->GetFilent(48);
        if ($Filent_Rec_48['FILVAL']) {
            $datiTipiFO = json_decode($Filent_Rec_48['FILVAL'], true);
            if (isset($datiTipiFO['TIPIFO'])) {
                return $datiTipiFO['TIPIFO'];
            }
        }
    }

    public function getParamTipoFO($tipo) {
        $arrTipiFO = $this->getArrayTipiFO();
        foreach ($arrTipiFO as $key => $tipoFO) {
            if ($tipoFO['TIPO'] == $tipo) {
                return $tipoFO;
            }
        }
    }

    public function getModelCtrRichieste() {
        $Filent_Rec = $this->GetFilent(42);
        if ($Filent_Rec['FILVAL'] == 1) {
            $model = 'praCtrRichiesteFO';
            $returnEvent = 'returnCtrRichiesteFO';
        } else {
            $model = 'praCtrRichieste';
            $returnEvent = 'returnCtrRichieste';
        }
        return array('model' => $model, 'returnEvent' => $returnEvent);
    }

    public function GetAllegatiAccorpate($fileXML) {
        /*
         * Estrazione dati da XML
         *
         */
        $xmlObj = new QXML;
        $ret = $xmlObj->setXmlFromFile($fileXML);
        if ($ret == false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore apertura XML: $fileXML");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());

        $arrAlleAccorpate = array();
        $XML_RichiesteAccorpate_tab = array();
        $Accorpate_tab = $arrayXml['RICHIESTE_ACCORPATE']['XMLINFO'];
        if ($Accorpate_tab) {
            if (isset($Accorpate_tab[0])) {
                $XML_RichiesteAccorpate_tab = $Accorpate_tab;
            } else {
                $XML_RichiesteAccorpate_tab[0] = $Accorpate_tab;
            }

            foreach ($XML_RichiesteAccorpate_tab as $XML_RichiesteAccorpate_rec) {
                $numRichiesta = substr($XML_RichiesteAccorpate_rec["@textNode"], 8, 10);
                $arrAlleAccorpate[$numRichiesta] = $this->GetAllegatiPratica($numRichiesta);
            }
        }
        return $arrAlleAccorpate;
    }

    public function insertMailIntegrazione($Pramail_rec, $fileEml, $daMail, $Propas_rec, $pasnotEML, $pasnameEML, $pascla) {

        if (!$Pramail_rec) {
            $Pramail_rec = $this->GetPramailRecIntegrazione($Propas_rec['PRORIN']);
            $daMail = true;
        }

        if ($Pramail_rec) {
            include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
            $emlLib = new emlLib();
            $mailArchivio_rec = $emlLib->getMailArchivio($Pramail_rec['ROWIDARCHIVIO'], "rowid");
            if ($mailArchivio_rec) {
                $dbMailBox = emlDbMailBox::getDbMailBoxInstance();
                $file_riletto = $dbMailBox->getEmlForROWId($mailArchivio_rec['ROWID']);
                $fileEml = $file_riletto;
            }
        }

        if ($fileEml) {
            $destinazione = $this->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK']);
            if (!copy($fileEml, $destinazione . '/' . pathinfo($fileEml, PATHINFO_BASENAME))) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore copia file eml " . pathinfo($fileEml, PATHINFO_BASENAME));
                return false;
            }
            $Pasdoc_rec = array();
            $Pasdoc_rec['PASKEY'] = $Propas_rec['PROPAK'];
            $Pasdoc_rec['PASFIL'] = pathinfo($fileEml, PATHINFO_BASENAME);
            $Pasdoc_rec['PASLNK'] = "allegato://" . pathinfo($fileEml, PATHINFO_BASENAME);
            $Pasdoc_rec['PASNOT'] = $pasnotEML;
            $Pasdoc_rec['PASNAME'] = $pasnameEML;
            $Pasdoc_rec['PASCLA'] = $pascla;
            $Pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $Pasdoc_rec['PASDATADOC'] = date("Ymd");
            $Pasdoc_rec['PASORADOC'] = date("H:i:s");
            $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $destinazione . "/" . $Pasdoc_rec['PASFIL']);

            try {
                $nrow = ItaDB::DBInsert($this->getPRAMDB(), 'PASDOC', 'ROWID', $Pasdoc_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }
        }

        if ($Pramail_rec) {
            if (!$this->setClasseCaricatoPasso($daMail, $Pramail_rec['IDMAIL'], $Propas_rec['PRONUM'], $Propas_rec['PROPAK'])) {
                return false;
            }
        }
        return true;
    }

}
