<?php

/**
 *
 * LIBRERIA PER APPLICATIVO GAFIERE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Base
 * @author     Marilungo Alessandro <alessandro.marilungo@italsoft.eu>
 * @author     Tania Angeloni <tania.angeloni@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    06.10.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php');
class basLibIPA {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private static $basLibIPA = array();
    private $errMessage;
    private $errCode;
    public $BASE_DB;
    public $COMUNI_DB;
    public $ITW_DB;
    public $tempPath;
    public $ITALWEB_DB;

    public static function getInstance($ditta = '') {
        if (!$ditta) {
            $ditta = App::$utente->getKey('ditta');
        }
        if (!isset(self::$basLibIPA[$ditta])) {
            try {
                self::$basLibIPA[$ditta] = new basLibIPA();
            } catch (Exception $exc) {
                $this->setErrMessage($exc->getMessage());
                return false;
            }
        }
        return self::$basLibIPA[$ditta];
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

    public function setBASEDB($BASE_DB) {
        $this->BASE_DB = $BASE_DB;
    }

    public function setCOMUNIDB($COMUNI_DB) {
        $this->COMUNI_DB = $COMUNI_DB;
    }

    public function setITWDB($ITW_DB) {
        $this->ITW_DB = $ITW_DB;
    }

    public function setITALWEBDB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getBASEDB() {
        if (!$this->BASE_DB) {
            try {
                $this->BASE_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->BASE_DB;
    }

    public function getCOMUNIDB() {
        if (!$this->COMUNI_DB) {
            try {
                $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->COMUNI_DB;
    }

    public function getITWDB() {
        if (!$this->ITW_DB) {
            try {
                $this->ITW_DB = ItaDB::DBOpen('ITW');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITW_DB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function getGenericTab($sql, $multi = true, $tipoDB = 'ITALWEB') {
        if ($tipoDB == 'ITALWEB') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
        } elseif ($tipoDB == 'COMUNI') {
            $tabella_tab = ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
        }
        return $tabella_tab;
    }

    public function getComana($codice, $tipo = 'anacat', $anacod = '') {
        $multi = false;
        if ($tipo == 'anacat') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice'";
            $multi = true;
        } else if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice' AND ANACOD='$anacod'";
        } else if ($tipo == 'descrizione') {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='$codice' AND ANADES='$anacod'";
        } else {
            $sql = "SELECT * FROM ANA_COMUNE WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getBASEDB(), $sql, $multi);
    }

    public function getNewAnacodComana($anacat = "VIE") {
        if (!$anacat)
            return false;
        $sql = "SELECT MAX(ANACOD) AS MASSIMO FROM `ANA_COMUNE` WHERE ANACAT = '$anacat'";
        $max_rec = ItaDB::DBSQLSelect($this->getBASEDB(), $sql, false);
        $codice = (int) $max_rec['MASSIMO'] + 1;
        $new_anacod = str_pad($codice, 6, "0", STR_PAD_LEFT);
        return $new_anacod;
    }

    public function getComuni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM COMUNI WHERE COMUNE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'nascit') {
            $sql = "SELECT * FROM COMUNI WHERE NASCIT = '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM COMUNI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getNazioni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM NAZIONI WHERE DESCRIZIONE='" . addslashes($codice) . "'";
        } elseif ($tipo == 'onu') {
            $sql = "SELECT * FROM NAZIONI WHERE CODICEONU= '" . $codice . "'";
        } else {
            $sql = "SELECT * FROM NAZIONI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getRegioni($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM REGIONI WHERE REGIONE='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM REGIONI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getProvince($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PROVINCE WHERE PROVINCIA='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PROVINCE WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getCittadinanze($codice, $tipo = 'codice') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM CITTADINANZA WHERE CITTADINANZA='" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM CITTADINANZA WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, false);
    }

    public function getAmministrazioni($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM AMMINISTRAZIONI WHERE COD_AMM = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM AMMINISTRAZIONI WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getAoo($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM AOO WHERE COD_AOO = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM AOO WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getUo($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM UO WHERE COD_OU = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM UO WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function getPec($codice, $tipo = 'codice', $multi = false) {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM PEC WHERE COD_AMM = '" . addslashes($codice) . "'";
        } else {
            $sql = "SELECT * FROM PEC WHERE ROWID = $codice";
        }
        return ItaDB::DBSQLSelect($this->getCOMUNIDB(), $sql, $multi);
    }

    public function SetMarcaturaRuolo($Anaruo_rec, $fl_ins = false) {
        if ($fl_ins) {
            $Anaruo_rec['RUOINSDATE'] = date('Ymd');
            $Anaruo_rec['RUOINSTIME'] = date('H:i:s');
        }
        $Anaruo_rec['RUOUPDDATE'] = date('Ymd');
        $Anaruo_rec['RUOUPDTIME'] = date('H:i:s');
        return $Anaruo_rec;
    }

    /**
     * Metodo per effettuare una generica chiamata CURL passando un set di CURL_OPTION
     * 
     * @param string $domain <p>Dominio o url che verrà settato dal curl_init.</p>
     * @param array $curl_opt <p>array di opzioni CURL composto da chiave valore:<br>ES:<br>array('CURLOPT_TIMEOUT' => 5, 'CURLOPT_HEADER' => false, 'CURLOPT_NOBODY' => true, 'CURLOPT_RETURNTRANSFER' => true)</p>
     * @return string
     */
    public function callCurl($domain, $curl_opt = array()) {
        $curlInit = curl_init($domain);
        foreach ($curl_opt as $option => $value) {
            curl_setopt($curlInit, $option, $value);
        }
        $response = curl_exec($curlInit);
        $response = curl_getinfo($curlInit, CURLINFO_HTTP_CODE);
        $response2 = curl_getinfo($curlInit, CURLINFO_HTTP_CONNECTCODE);
        $type = curl_getinfo($curlInit, CURLINFO_CONTENT_TYPE);
        curl_close($curlInit);
        return $response;
    }

    public static function leggiParametri_IPA($tipo) {
        include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
        $result = array();
        $devLib = new devLib();

        $result['AMM'] = $devLib->getEnv_config('IPADATA', 'codice', 'URL_AMMINISTRAZIONE', false);
        $result['AOO'] = $devLib->getEnv_config('IPADATA', 'codice', 'URL_AOO', false);
        $result['UO'] = $devLib->getEnv_config('IPADATA', 'codice', 'URL_UO', false);
        $result['PEC'] = $devLib->getEnv_config('IPADATA', 'codice', 'URL_PEC', false);
        //Out::msgInfo("array", print_r($result, true));
        //return;
        switch ($tipo) {
            case 'AMM':
                //Out::msgInfo("passo amm", $result['AMM']['CONFIG']);
                return $result['AMM']['CONFIG'];
                break;
            case 'AOO':
                return $result['AOO']['CONFIG'];
                break;
            case 'UO':
                return $result['UO']['CONFIG'];
                break;
            case 'PEC':
                return $result['PEC']['CONFIG'];
                break;
            case 'ALL':
                $result_tot = array();
                $result_tot['AMM'] = $result['AMM']['CONFIG'];
                $result_tot['AOO'] = $result['AOO']['CONFIG'];
                $result_tot['UO'] = $result['UO']['CONFIG'];
                $result_tot['PEC'] = $result['PEC']['CONFIG'];
                //Out::msgInfo("array", print_r($result_tot, true));
                return $result_tot;
                break;
        }
    }

    public function decodIPA($sourceFile) {
        list($url, $altro) = explode('.it', $sourceFile);
        //Out::msgInfo("url", $url);
        list($path, $altro) = explode('?', $altro);
        // Out::msgInfo("path", $path);
        list($scarto, $dataname) = explode('filename=', $altro);
        // Out::msgInfo("data", $dataname);
        // http://www.indicepa.gov.it/public-services/opendata-read-service.php?dstype=FS&filename=amministrazioni.txt
        //$url = "http://www.indicepa.gov.it";
        //$path = "/public-services/opendata-read-service.php";
        $data = array(
            'dstype' => 'FS',
            'filename' => $dataname
        );
        $restClient = new itaRestClient();
        $restClient->setTimeout(50);
        $restClient->setCurlopt_url($url . '.it');
        $restClient->setDebugLevel(true);
        $restClient->setCurlopt_followlocation(true);
        $restClient->setCurlopt_header(true);
        $restClient->setCurlopt_useragent('curl');        
        $restCall = $restClient->get($path, $data, $headers);
        if ($restCall) {
            if ($restClient->getHttpStatus() !== 200) {
                $this->setErrCode(-1);
                $this->setErrMessage("Chiamata non riuscita (" . $restClient->getHttpStatus() . ")\n$path");
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('ws: ' . $restClient->getErrMessage());
            return false;
        }
        //$contents = htmlspecialchars($restClient->getResult());
        $filedest = $this->createTempPath() . '/' . $dataname;
        file_put_contents($filedest, htmlspecialchars($restClient->getResult()));

        //Out::msgInfo("Risultato", htmlspecialchars($restClient->getResult()));
        //Out::msgInfo("Debug", htmlspecialchars($restClient->getDebug()));
        //Out::msgInfo("destinazione", $dataname);
        return $filedest;
    }

    private static function createTempPath() {
        $subPath = "bda-ipawork-" . md5(microtime());
        $tempPath = itaLib::getAppsTempPath($subPath);
        itaLib::deleteDirRecursive($tempPath);
        $filedest = itaLib::createAppsTempPath($subPath);
        return $filedest;
    }

    public function cleanData() {
        return itaLib::deleteDirRecursive($this->getTempPath());
    }

    public function AuditEvento($insert_Info, $db) {
        $eqAudit = new eqAudit();
        $eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_INS_RECORD,
            'DB' => $this->getCOMUNIDB()->getDB(),
            'DBset' => $db,
            'Estremi' => $insert_Info
        ));
    }

    public function importaALL($sourceFile) {
        $appoggioAMM = $this->decodIPA($sourceFile['AMM']);
        $appoggioAOO = $this->decodIPA($sourceFile['AOO']);
        $appoggioUO = $this->decodIPA($sourceFile['UO']);
        $appoggioPEC = $this->decodIPA($sourceFile['PEC']);
        // qui controlli.
        if (!$appoggioAMM || !$appoggioAOO || !$appoggioUO || !$appoggioPEC) {
            $this->cleanData();
            $this->AuditEvento("Errore caricamento delle risorse.");
            return false;
        }
        if (!$this->importaAmministrazione($appoggioAMM)) {
            $this->cleanData();
            return false;
        }
        if (!$this->importaAOO($appoggioAOO)) {
            $this->cleanData();
            return false;
        }
        if (!$this->importaUO($appoggioUO)) {
            $this->cleanData();
            return false;
        }
        if (!$this->importaPEC($appoggioPEC)) {
            $this->cleanData();
            return false;
        }
        $this->cleanData();
        return true;
    }

    public function importaAmministrazione($sourceFile) {
        $contents = file($sourceFile);
        if ($contents == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura contenuto file Amministrazioni. ");
            $this->AuditEvento("Errore in lettura contenuto file Amministrazioni.");
            return false;
        }
        $this->VuotaAmministrazioni();
        $this->AuditEvento("Inizio inserimento di " . count($contents) . " AMMINISTRAZIONI", 'AMMINISTRAZIONI');
        $c = 0;
        foreach ($contents as $riga) {
            $c++;
            if ($c == 1) {
                continue;   //record di testa, non inserisco
            }
            $dati = array();
            $dati = explode(chr(9), $riga);
            $tipo = trim($dati[11]);
            if (1 != 1) { // Temporaneamente bloccato
                if ($tipo != "Comuni" && $tipo != "Comunita' Montane" && $tipo != "Regioni e Province Autonome" && $tipo != "Regioni e Province Autonome" && $tipo != "Province" && $tipo != "Unioni di Comuni" && $tipo != "Altre Amministrazioni Locali" && $tipo != "Aziende Sanitarie Locali" && $tipo != "Comuni e loro Consorzi e Associazioni" && $tipo != "Camere di Commercio, Industria, Artigianato e Agricoltura e Unioni Regionali") {
                    continue;
                }
            }
            $Amm_rec = array();
            $Amm_rec['COD_AMM'] = $dati[0] == 'null' ? '' : trim($dati[0]);
            $Amm_rec['DES_AMM'] = $dati[1] == 'null' ? '' : trim($dati[1]);
            $Amm_rec['COMUNE'] = $dati[2] == 'null' ? '' : trim($dati[2]);
            $Amm_rec['NOME_RESP'] = $dati[3] == 'null' ? '' : trim($dati[3]);
            $Amm_rec['COGNOME_RESP'] = $dati[4] == 'null' ? '' : trim($dati[4]);
            $Amm_rec['CAP'] = $dati[5] == 'null' ? '' : trim($dati[5]);
            $Amm_rec['PROVINCIA'] = $dati[6] == 'null' ? '' : trim($dati[6]);
            $Amm_rec['REGIONE'] = $dati[7] == 'null' ? '' : trim($dati[7]);
            $Amm_rec['SITO_ISTITUZIONALE'] = $dati[8] == 'null' ? '' : trim($dati[8]);
            $Amm_rec['INDIRIZZO'] = $dati[9] == 'null' ? '' : trim($dati[9]);
            $Amm_rec['TITOLO_RESP'] = $dati[10] == 'null' ? '' : trim($dati[10]);
            $Amm_rec['TIPOLOGIA_ISTAT'] = $dati[11] == 'null' ? '' : trim($dati[11]);
            $Amm_rec['TIPOLOGIA_AMMINISTRAZIONE'] = $dati[12] == 'null' ? '' : trim($dati[12]);
            $Amm_rec['ACRONIMO'] = $dati[13] == 'null' ? '' : trim($dati[13]);
            $Amm_rec['CF_VALIDATO'] = $dati[14] == 'null' ? '' : trim($dati[14]);
            $Amm_rec['CF'] = $dati[15] == 'null' ? '' : trim($dati[15]);
            $Amm_rec['MAIL1'] = $dati[16] == 'null' ? '' : trim($dati[16]);
            $Amm_rec['TIPO_MAIL1'] = $dati[17] == 'null' ? '' : trim($dati[17]);
            $Amm_rec['MAIL2'] = $dati[18] == 'null' ? '' : trim($dati[18]);
            $Amm_rec['TIPO_MAIL2'] = $dati[19] == 'null' ? '' : trim($dati[19]);
            $Amm_rec['MAIL3'] = $dati[20] == 'null' ? '' : trim($dati[20]);
            $Amm_rec['TIPO_MAIL3'] = $dati[21] == 'null' ? '' : trim($dati[21]);
            $Amm_rec['MAIL4'] = $dati[22] == 'null' ? '' : trim($dati[22]);
            $Amm_rec['TIPO_MAIL4'] = $dati[23] == 'null' ? '' : trim($dati[23]);
            $Amm_rec['MAIL5'] = $dati[24] == 'null' ? '' : trim($dati[24]);
            $Amm_rec['TIPO_MAIL5'] = $dati[25] == 'null' ? '' : trim($dati[25]);
            $Amm_rec['URL_FACEBOOK'] = $dati[26] == 'null' ? '' : trim($dati[26]);
            $Amm_rec['URL_TWITTER'] = $dati[27] == 'null' ? '' : trim($dati[27]);
            $Amm_rec['URL_GOOGLEPLUS'] = $dati[28] == 'null' ? '' : trim($dati[28]);
            $Amm_rec['URL_YOUTUBE'] = $dati[29] == 'null' ? '' : trim($dati[29]);
            $Amm_rec['LIV_ACCESSIBILI'] = trim($dati[30]) == 'null' ? '' : trim($dati[30]);
            //ItaDB::DBInsert($this->getCOMUNIDB(), 'AMMINISTRAZIONI', 'ROWID', $Amm_rec
            try {
                ItaDB::DBInsert($this->getCOMUNIDB(), 'AMMINISTRAZIONI', 'ROWID', $Amm_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in fase di inserimento cod AMM: " . $Amm_rec['COD_AMM'] . " L'inserimento è stato Arrestato<br/>" . $e->getMessage());
                $this->AuditEvento("Errore importazione Amministrazioni " . $this->getErrMessage(), 'AMMINISTRAZIONI');
                return false;
            }
//            if ($c == 3) {
//                break;
//            }
        }
        $this->AuditEvento("Sono state importate " . $c . " Amministrazioni ", 'AMMINISTRAZIONI');
        return true;
    }

    public function importaAOO($sourceFile) {
        $contents = file($sourceFile);
        if ($contents == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura contenuto file AOO. ");
            $this->AuditEvento("Errore in lettura contenuto file AOO.");
            return false;
        }
        $this->VuotaAoo();
        $this->AuditEvento("Inizio inserimento di " . count($contents) . " AOO", 'AOO');
        $c = 0;
        foreach ($contents as $riga) {

            $c++;
            if ($c == 1) {
                continue;   //record di testa, non inserisco
            }
            $dati = array();
            $dati = explode(chr(9), $riga);

            $Aoo_rec = array();
            $Aoo_rec['COD_AMM'] = $dati[0] == 'null' ? '' : trim($dati[0]);
            $Aoo_rec['COD_AOO'] = $dati[1] == 'null' ? '' : trim($dati[1]);
            $Aoo_rec['DES_AOO'] = $dati[2] == 'null' ? '' : trim($dati[2]);
            $Aoo_rec['DAT_ISTITUZ'] = $dati[3] == 'null' ? '' : substr($dati[3], 0, 4) . substr($dati[3], 5, 2) . substr($dati[3], 8, 2); //2010-04-27
            $Aoo_rec['COMUNE'] = $dati[4] == 'null' ? '' : trim($dati[4]);
            $Aoo_rec['CAP'] = $dati[5] == 'null' ? '' : trim($dati[5]);
            $Aoo_rec['PROVINCIA'] = $dati[6] == 'null' ? '' : trim($dati[6]);
            $Aoo_rec['REGIONE'] = $dati[7] == 'null' ? '' : trim($dati[7]);
            $Aoo_rec['INDIRIZZO'] = $dati[8] == 'null' ? '' : trim($dati[8]);
            $Aoo_rec['TEL'] = $dati[9] == 'null' ? '' : trim($dati[9]);
            $Aoo_rec['NOME_RESP'] = $dati[10] == 'null' ? '' : trim($dati[10]);
            $Aoo_rec['COGNOME_RESP'] = $dati[11] == 'null' ? '' : trim($dati[11]);
            $Aoo_rec['MAIL_RESP'] = $dati[12] == 'null' ? '' : trim($dati[12]);
            $Aoo_rec['TEL_RESP'] = $dati[13] == 'null' ? '' : trim($dati[13]);
            $Aoo_rec['FAX'] = $dati[14] == 'null' ? '' : trim($dati[14]);
            $Aoo_rec['MAIL1'] = $dati[15] == 'null' ? '' : trim($dati[15]);
            $Aoo_rec['TIPO_MAIL1'] = $dati[16] == 'null' ? '' : trim($dati[16]);
            $Aoo_rec['MAIL2'] = $dati[17] == 'null' ? '' : trim($dati[17]);
            $Aoo_rec['TIPO_MAIL2'] = $dati[18] == 'null' ? '' : trim($dati[18]);
            $Aoo_rec['MAIL3'] = $dati[19] == 'null' ? '' : trim($dati[19]);
            $Aoo_rec['TIPO_MAIL3'] = trim($dati[20]) == 'null' ? '' : trim($dati[20]);
            //ItaDB::DBInsert($this->getCOMUNIDB(), 'AOO', 'ROWID', $Aoo_rec);
            try {
                ItaDB::DBInsert($this->getCOMUNIDB(), 'AOO', 'ROWID', $Aoo_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in fase di inserimento cod AOO: " . $Aoo_rec['COD_AOO'] . " L'inserimento è stato Arrestato<br/>" . $e->getMessage());
                $this->AuditEvento("Errore importazione AOO " . $this->getErrMessage(), 'AOO');
                return false;
            }

//            if ($c == 3) {
//                break;
//            }
        }
        $this->AuditEvento("Sono state importate " . $c . " AOO ", 'AOO');
        return true;
    }

    public function importaUO($sourceFile) {
        $contents = file($sourceFile);
        if ($contents == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura contenuto file UO. ");
            $this->AuditEvento("Errore in lettura contenuto file UO.");
            return false;
        }
        $this->VuotaUo();
        $this->AuditEvento("Inizio inserimento di " . count($contents) . " UO", 'UO');
        $c = 0;
        foreach ($contents as $riga) {
            $c++;
            if ($c == 1) {
                continue;   //record di testa, non inserisco
            }
            $dati = array();
            $dati = explode(chr(9), $riga);
            $Uo_rec = array();
            $Uo_rec['COD_OU'] = $dati[0] == 'null' ? '' : trim($dati[0]);
            $Uo_rec['COD_AOO'] = $dati[1] == 'null' ? '' : trim($dati[1]);
            $Uo_rec['DES_OU'] = $dati[2] == 'null' ? '' : trim($dati[2]);
            $Uo_rec['COMUNE'] = $dati[3] == 'null' ? '' : trim($dati[3]);
            $Uo_rec['CAP'] = $dati[4] == 'null' ? '' : trim($dati[4]);
            $Uo_rec['PROVINCIA'] = $dati[5] == 'null' ? '' : trim($dati[5]);
            $Uo_rec['REGIONE'] = $dati[6] == 'null' ? '' : trim($dati[6]);
            $Uo_rec['INDIRIZZO'] = $dati[7] == 'null' ? '' : trim($dati[7]);
            $Uo_rec['TEL'] = $dati[8] == 'null' ? '' : trim($dati[8]);
            $Uo_rec['NOME_RESP'] = $dati[9] == 'null' ? '' : trim($dati[9]);
            $Uo_rec['COGNOME_RESP'] = $dati[10] == 'null' ? '' : trim($dati[10]);
            $Uo_rec['MAIL_RESP'] = $dati[11] == 'null' ? '' : trim($dati[11]);
            $Uo_rec['TEL_RESP'] = $dati[12] == 'null' ? '' : trim($dati[12]);
            $Uo_rec['COD_AMM'] = $dati[13] == 'null' ? '' : trim($dati[13]);
            $Uo_rec['COD_OU_PADRE'] = $dati[14] == 'null' ? '' : trim($dati[14]);
            $Uo_rec['FAX'] = $dati[15] == 'null' ? '' : trim($dati[15]);
            $Uo_rec['COD_UNI_OU'] = $dati[16] == 'null' ? '' : trim($dati[16]);
            $Uo_rec['MAIL1'] = $dati[17] == 'null' ? '' : trim($dati[17]);
            $Uo_rec['TIPO_MAIL1'] = $dati[18] == 'null' ? '' : trim($dati[18]);
            $Uo_rec['MAIL2'] = $dati[19] == 'null' ? '' : trim($dati[19]);
            $Uo_rec['TIPO_MAIL2'] = $dati[20] == 'null' ? '' : trim($dati[20]);
            $Uo_rec['MAIL3'] = $dati[21] == 'null' ? '' : trim($dati[21]);
            $Uo_rec['TIPO_MAIL3'] = trim($dati[22]) == 'null' ? '' : trim($dati[22]);
            // ItaDB::DBInsert($this->getCOMUNIDB(), 'UO', 'ROWID', $Uo_rec);
            try {
                ItaDB::DBInsert($this->getCOMUNIDB(), 'UO', 'ROWID', $Uo_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in fase di inserimento cod UO: " . $Uo_rec['COD_OU'] . " L'inserimento è stato Arrestato<br/>" . $e->getMessage());
                $this->AuditEvento("Errore importazione UO  " . $this->getErrMessage(), 'UO');
                return false;
            }
//            if ($c == 2) {
//                break;
//            }
        }
        $this->AuditEvento("Sono state importate " . $c . " UO ", 'UO');
        return true;
    }

    public function importaPEC($sourceFile) {
        $contents = file($sourceFile);
        if ($contents == '') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura contenuto file PEC. ");
            $this->AuditEvento("Errore in lettura contenuto file PEC.");
            return false;
        }
        $this->VuotaPec();
        $this->AuditEvento("Inizio inserimento di " . count($contents) . " PEC", 'PEC');
        $c = 0;
        foreach ($contents as $riga) {
            $c++;
            if ($c == 1) {
                continue;   //record di testa, non inserisco
            }
            $dati = array();
            $dati = explode(chr(9), $riga);
            $Pec_rec = array();
            $Pec_rec['COD_AMM'] = $dati[0] == 'null' ? '' : trim($dati[0]);
            $Pec_rec['DESCRIZIONE'] = $dati[1] == 'null' ? '' : trim($dati[1]);
            $Pec_rec['TIPO'] = $dati[2] == 'null' ? '' : trim($dati[2]);
            $Pec_rec['TIPOLOGIA_AMMINISTRAZIONE'] = $dati[3] == 'null' ? '' : trim($dati[3]);
            $Pec_rec['REGIONE'] = $dati[4] == 'null' ? '' : trim($dati[4]);
            $Pec_rec['PROVINCIA'] = $dati[5] == 'null' ? '' : trim($dati[5]);
            $Pec_rec['COMUNE'] = $dati[6] == 'null' ? '' : trim($dati[6]);
            $Pec_rec['MAIL'] = $dati[7] == 'null' ? '' : trim($dati[7]);
            $Pec_rec['TIPO_MAIL'] = trim($dati[8]) == 'null' ? '' : trim($dati[8]);
            //$insert = ItaDB::DBInsert($this->getCOMUNIDB(), 'PEC', 'ROWID', $Pec_rec);
            try {
                ItaDB::DBInsert($this->getCOMUNIDB(), 'PEC', 'ROWID', $Pec_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in fase di inserimento cod PEC: " . $Pec_rec['COD_AMM'] . " L'inserimento è stato Arrestato<br/>" . $e->getMessage());
                $this->AuditEvento("Errore importazione PEC " . $this->getErrMessage(), 'PEC');
                return false;
            }
//            if ($c == 3) {
//                break;
//            }
        }
        $this->AuditEvento("Sono state importate " . $c . " PEC ", 'PEC');
        return true;
    }

    public function VuotaAmministrazioni() {
        $sql = "TRUNCATE AMMINISTRAZIONI";
        ItaDB::DBSQLExec($this->getCOMUNIDB(), $sql);
        //Out::msgInfo("vuota Amministrazione", "ci passo F cancella");
    }

    public function VuotaAOO() {
        $sql = "TRUNCATE AOO";
        ItaDB::DBSQLExec($this->getCOMUNIDB(), $sql);
        //Out::msgInfo("vuota Aoo", "ci passo F cancella");
    }

    public function VuotaUO() {
        $sql = "TRUNCATE UO";
        ItaDB::DBSQLExec($this->getCOMUNIDB(), $sql);
        // Out::msgInfo("vuota UO", "ci passo F cancella");
    }

    public function VuotaPEC() {
        $sql = "TRUNCATE PEC";
        ItaDB::DBSQLExec($this->getCOMUNIDB(), $sql);
    }

}

?>
