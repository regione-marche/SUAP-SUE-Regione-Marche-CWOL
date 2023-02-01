<?php
class AppUtility{
    static $version;
    
    /**
     * Ritorna il codice versione di itaEngine Framework base
     * @return String
     */
    public static function getVersion(){
        if(!isSet(self::$version)){
            if(file_exists(ITA_BASE_PATH . '/version')){
                self::$version = preg_replace('/[^0-9.]/','',file_get_contents(ITA_BASE_PATH . '/version'));
            }
        }
        
        return (isSet(self::$version))?self::$version:false;
    }        
    
    /**
     * Imposta o rimuove il file di lock dell'applicazione
     * @param boolean $lock true se va creato il file di lock, false se va rimosso
     * @param string $msg stringa di testo da mostrare sul blocco applicativo
     * return boolean
     */
    public static function setApplicationLock($lock,$msg=''){
        if($lock){
            $lockArray = array('timestamp'=>time(),'msg'=>$msg);
            file_put_contents(ITA_BASE_PATH . '/lock', json_encode($lockArray));
        }
        else{
            if(file_exists(ITA_BASE_PATH . '/lock')){
                unlink(ITA_BASE_PATH . '/lock');
            }
        }
    }
    
    /**
     * Se il blocco non Ã¨ presente restituisce false,
     * se il blocco Ã¨ presente restitusce un array con al suo interno 'timestamp' (timestamp unix) e 'msg' (stringa di testo)
     * @return boolean/array
     */
    public static function getApplicationLock(){
        if(!file_exists(ITA_BASE_PATH . '/lock')){
            return false;
        }
        return json_decode(file_get_contents(ITA_BASE_PATH . '/lock'),true);
    }
    
    public static function enforceApplicationLock($eventModel='envLogout',$eventId='envLogout_Termina'){
        $appLock = self::getApplicationLock();
        if($appLock === false ||
            ($_POST['model'] == 'envLogout' && $_POST['event'] == 'onClick' && $_POST['id'] == 'envLogout_Termina') ||
            ($_POST['model'] == 'menButton' && $_POST['event'] == 'openButton') ||
            ($_POST['model'] == 'menButton' && $_POST['event'] == 'onClick' && !isSet($_POST['prog']))){
            return false;
        }
            
        $titolo = "Blocco applicativo";
        $messaggio = "L'applicativo è sotto blocco: " . $appLock['msg']."<br>La sessione verrà terminata.";
        $bottoni = array('Conferma'=>array('id'=>$eventId,'model'=>$eventModel));
        $height = 'auto';
        $width = 'auto';
        $closeButton = 'false';
        $noRound = false;
        $closeAuto = true;
        $verticalButtons = false;
        $requestCall = "ItaCall";
        $modal = 'true';
        $parent = 'mainTabs';

        Out::msgQuestion($titolo,$messaggio,$bottoni,$height,$width,$closeButton,$noRound,$closeAuto,$verticalButtons,$requestCall,$modal,$parent);
        return true;
    }
    
    /**
     * Restituisce le informazioni della build
     * @return array build info
     */
    public static function getBuildInfo() {
        $buildInfo = array();
        $buildInfoPath = ITA_BASE_PATH . '/build/build-info.json';
        if(file_exists($buildInfoPath)) {
            $buildInfo = json_decode(file_get_contents($buildInfoPath), true);
        }
        return $buildInfo;
    }
    
    /**
     * Restituisce la build history
     * @return array build history
     */
    public static function getBuildHistory() {
        $buildHistory = array();
        $buildHistoryPath = ITA_BASE_PATH . '/build/build-history.json';
        if(file_exists($buildHistoryPath)) {
            $buildHistory = json_decode(file_get_contents($buildHistoryPath), true);            
        }
        return $buildHistory;
    }

    /**
     * Restituisce la versione di Cityware, se presente.
     * @return string|boolean True se non presente, string se presente.
     * @throws ItaException
     */
    public function getCitywareReleaseNumber() {
        if (!itaHooks::isActive('citywareHook.php')) {
            return true;
        }

        try {
            $ITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
            $domains_rec = ItaDB::DBSQLSelect($ITALWEBDB, 'SELECT CODICE FROM DOMAINS ORDER BY SEQUENZA ASC', false);
            $enteCityware = $domains_rec['CODICE'];
        } catch (Exception $e) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore durante la lettura dei DOMAINS, impossibile proseguire: ' . $e->getMessage());
        }

        try {
            require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
            $cwbLibBGE = new cwbLibDB_BGE();
            $cwbLibBGE->setEnte($enteCityware);
            $cwbLibBGE->leggi("SELECT * FROM BGE_RELEASE WHERE COD_PRODOTTO = 1", $result);
        } catch (Exception $e) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore durante la lettura della release BGE_RELEASE, impossibile proseguire: ' . $e->getMessage());
        }

        if (!count($result)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Record su BGE_RELEASE non presente, impossibile proseguire.');
        }

        if (!$result['VERSIONE']) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Versione software su BGE_RELEASE non presente, impossibile proseguire.');
        }

        $versioneParts = explode('.', $result['VERSIONE']);

        if (count($versioneParts) !== 2) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Versione software su BGE_RELEASE con formato non valido (' . $result['VERSIONE'] . ').<br>Verificare che il formato sia "XX.YY".');
        }

        $versioneCW = str_pad((int) $versioneParts[0], 2, '0', STR_PAD_LEFT) . '.' . str_pad((int) $versioneParts[1], 2, '0', STR_PAD_LEFT);

        return $versioneCW;
    }

}
