<?php

include_once ITA_LIB_PATH . '/itaPHPGit/itaGit.class.php';
include_once ITA_LIB_PATH . '/zip/itaZip.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFtpUtils.class.php';

class envLibPatch {

    protected $errCode;
    protected $errMessage;
    protected $ITALWEB;
    protected $ITALWEBDB;

    const CWOL_PATCH_DIR = 'cwol_patch';

    /*
     * Costanti applicazione PATCH
     */
    const APPL_MODE_MANUAL = 0;
    const APPL_MODE_AUTO = 1;
    const APPL_MODE_FORCED = 2;

    public function __construct() {
        itaLib::createAppsTempPath();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    protected function setErrorStatus($message, $code = -1) {
        $this->errCode = $code;
        $this->errMessage = $message;
        return false;
    }

    public function getITALWEB() {
        if (!$this->ITALWEB) {
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        }

        return $this->ITALWEB;
    }

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEBDB) {
            $this->ITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
        }

        return $this->ITALWEBDB;
    }

    public function setITALWEBDB($ITALWEBDB) {
        $this->ITALWEBDB = $ITALWEBDB;
    }

    /**
     * 
     * @param type $record
     * @param type $insert
     */
    public function setAuditFields(&$record, $insert = false) {
        $record['CODUTE'] = App::$utente->getKey('nomeUtente');
        $record['DATAOPER'] = date('Y-m-d');
        $record['TIMEOPER'] = date('H:i:s');

        if ($insert) {
            $record['CODUTEINS'] = App::$utente->getKey('nomeUtente');
            $record['DATAINSER'] = date('Y-m-d');
            $record['TIMEINSER'] = date('H:i:s');
        }
    }

    public function getPatchDeft($rowid) {
        $sql = "SELECT * FROM PATCH_DEFT WHERE ROW_ID = '" . addslashes($rowid) . "'";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function getPatchDefd($rowid, $tipo = 'rowid', $multi = false) {
        if ($tipo === 'deft') {
            $sql = "SELECT * FROM PATCH_DEFD WHERE PATCH_DEFT_ID = '" . addslashes($rowid) . "'";
        } else {
            $sql = "SELECT * FROM PATCH_DEFD WHERE ROW_ID = '" . addslashes($rowid) . "'";
        }

        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getPatchApplt($rowid, $type = 'rowid') {
        switch ($type) {
            case 'name':
                $sql = "SELECT * FROM PATCH_APPLT WHERE PATCH_NAME = '" . addslashes($rowid) . "'";
                break;

            case 'deft':
                $sql = "SELECT * FROM PATCH_APPLT WHERE PATCH_DEFT_ID = '" . addslashes($rowid) . "'";
                break;

            default:
                $sql = "SELECT * FROM PATCH_APPLT WHERE ROW_ID = '" . addslashes($rowid) . "'";
                break;
        }

        return ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, false);
    }

    public function getPatchAppld($rowid, $tipo = 'rowid', $multi = false) {
        if ($tipo === 'applt') {
            $sql = "SELECT * FROM PATCH_APPLD WHERE PATCH_APPLT_ID = '" . addslashes($rowid) . "'";
        } else {
            $sql = "SELECT * FROM PATCH_APPLD WHERE ROW_ID = '" . addslashes($rowid) . "'";
        }

        return ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, $multi);
    }

    /**
     * 
     * @return boolean
     */
    public function getFTPConnection() {
        if (isset($this->_ftp_connection)) {
            return $this->_ftp_connection;
        }

        $host = Config::getConf('updater.patchFTPHost');
        $user = Config::getConf('updater.patchFTPUser');
        $pass = Config::getConf('updater.patchFTPPwd');

        $this->_ftp_connection = itaFtpUtils::openFtpConnection($host, $user, $pass);

        if (!$this->_ftp_connection) {
            return $this->setErrorStatus('Errore in connessione FTP');
        }

        return $this->_ftp_connection;
    }

    /**
     * 
     * @return type
     */
    public function getPathRepository() {
        return $this->normalizePath(Config::getConf('updater.patchRepositoryLocal'));
    }

    /**
     * Esplode un filepattern in un array di percorsi,
     * ritornando soltanto i file.
     * @param type $filePattern
     * @return type
     */
    public function explodeFilePattern($filePattern) {
        $filePattern = ltrim($this->normalizePath($filePattern), DIRECTORY_SEPARATOR);
        $results = glob($this->getPathRepository() . DIRECTORY_SEPARATOR . ltrim($filePattern, DIRECTORY_SEPARATOR));
        return array_filter($results, 'is_file');
    }

    /**
     * Verifica che il pattern contenga almeno un filepath valido.
     * @param type $filePattern
     * @return type
     */
    public function checkFilePattern($filePattern) {
        foreach ($this->explodeFilePattern($filePattern) as $filePath) {
            if ($this->checkFilePath($filePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica che il filepath sia valido.
     * @param type $filePath
     * @return type
     */
    public function checkFilePath($filePath) {
        return is_file($filePath) && !is_dir($filePath);
    }

    /**
     * 
     * @param type $patch_deft_rec
     * @param type $patch_defd_tab
     * @return boolean
     */
    public function insertPatch($patch_deft_rec, $patch_defd_tab) {
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();

        /*
         * Lock delle patch
         */

        if (!$envLib->Semaforo(envLib::SEMAFORO_BLOCCA, 'PATCHBUILDER', 'CREAZIONE PATCH')) {
            return $this->setErrorStatus('Errore blocco semaforo: ' . $envLib->getErrMessage(), $envLib->getErrCode());
        }

        $this->setAuditFields($patch_deft_rec, true);

        $patch_deft_rec['PATCH_NAME'] = 'CWOL_TEMP_PATCH_' . date('ymdhis');
        $patch_deft_rec['AUTHOR'] = App::$utente->getKey('nomeUtente');

        try {
            ItaDB::DBInsert($this->getITALWEB(), 'PATCH_DEFT', 'ROW_ID', $patch_deft_rec);
        } catch (Exception $e) {
            return $this->setErrorStatus('Errore inserimento record PATCH_DEFT: ' . $e->getMessage());
        }

        $patch_deft_rowid = ItaDB::DBLastId($this->getITALWEB());

        foreach ($patch_defd_tab as $patch_defd_rec) {
            $this->setAuditFields($patch_defd_rec, true);
            $patch_defd_rec['PATCH_DEFT_ID'] = $patch_deft_rowid;

            try {
                ItaDB::DBInsert($this->getITALWEB(), 'PATCH_DEFD', 'ROW_ID', $patch_defd_rec);
            } catch (Exception $e) {
                return $this->setErrorStatus('Errore inserimento record PATCH_DEFD: ' . $e->getMessage());
            }
        }

        if (!$envLib->Semaforo(envLib::SEMAFORO_SBLOCCA, 'PATCHBUILDER', 'CREAZIONE PATCH')) {
            return $this->setErrorStatus('Errore sblocco semaforo: ' . $envLib->getErrMessage(), $envLib->getErrCode());
        }

        return $patch_deft_rowid;
    }

    /**
     * 
     * @param type $patch_applt_rec
     * @param type $patch_appld_tab
     * @return boolean
     */
    public function insertPatchAppl($patch_applt_rec, $patch_appld_tab = array()) {
        $this->setAuditFields($patch_applt_rec, true);

        try {
            ItaDB::DBInsert($this->getITALWEBDB(), 'PATCH_APPLT', 'ROW_ID', $patch_applt_rec);
        } catch (Exception $e) {
            return $this->setErrorStatus('Errore inserimento record PATCH_APPLT: ' . $e->getMessage());
        }

        $patch_applt_rowid = ItaDB::DBLastId($this->getITALWEBDB());

        foreach ($patch_appld_tab as $patch_appld_rec) {
            $this->setAuditFields($patch_appld_rec, true);
            $patch_appld_rec['PATCH_APPLT_ID'] = $patch_applt_rowid;

            try {
                ItaDB::DBInsert($this->getITALWEBDB(), 'PATCH_APPLD', 'ROW_ID', $patch_appld_rec);
            } catch (Exception $e) {
                return $this->setErrorStatus('Errore inserimento record PATCH_APPLD: ' . $e->getMessage());
            }
        }

        return $patch_applt_rowid;
    }

    /**
     * 
     * @param array $patch_deft_rec
     * @param type $patch_defd_tab
     * @return boolean
     */
    public function updatePatch($patch_deft_rec, $patch_defd_tab) {
        $this->setAuditFields($patch_deft_rec);

        if (!$patch_deft_rec['PATCH_NAME']) {
            $patch_deft_rec['PATCH_NAME'] = 'CWOL_TEMP_PATCH_' . date('ymdhis');
        }

        try {
            ItaDB::DBUpdate($this->getITALWEB(), 'PATCH_DEFT', 'ROW_ID', $patch_deft_rec);
        } catch (Exception $e) {
            return $this->setErrorStatus('Errore aggiornamento record PATCH_DEFT: ' . $e->getMessage());
        }

        $patch_defd_db_tab = $this->getPatchDefd($patch_deft_rec['ROW_ID'], 'deft', true);

        foreach ($patch_defd_tab as $patch_defd_rec) {
            if ($patch_defd_rec['ROW_ID'] && in_array($patch_defd_rec['ROW_ID'], array_column($patch_defd_db_tab, 'ROW_ID'))) {
                $this->setAuditFields($patch_defd_rec);

                try {
                    ItaDB::DBUpdate($this->getITALWEB(), 'PATCH_DEFD', 'ROW_ID', $patch_defd_rec);
                } catch (Exception $e) {
                    return $this->setErrorStatus('Errore aggiornamento record PATCH_DEFD: ' . $e->getMessage());
                }
            } else {
                $this->setAuditFields($patch_defd_rec, true);
                $patch_defd_rec['PATCH_DEFT_ID'] = $patch_deft_rec['ROW_ID'];

                try {
                    ItaDB::DBInsert($this->getITALWEB(), 'PATCH_DEFD', 'ROW_ID', $patch_defd_rec);
                } catch (Exception $e) {
                    return $this->setErrorStatus('Errore inserimento record PATCH_DEFD: ' . $e->getMessage());
                }
            }
        }

        foreach ($patch_defd_db_tab as $patch_defd_db_rec) {
            if (!in_array($patch_defd_db_rec['ROW_ID'], array_column($patch_defd_tab, 'ROW_ID'))) {
                try {
                    ItaDB::DBDelete($this->getITALWEB(), 'PATCH_DEFD', 'ROW_ID', $patch_defd_db_rec['ROW_ID']);
                } catch (Exception $e) {
                    return $this->setErrorStatus('Errore cancellazione record PATCH_DEFD: ' . $e->getMessage());
                }
            }
        }

        return true;
    }

    public function deletePatch($patch_deft_rowid) {
        $patch_defd_tab = $this->getPatchDefd($patch_deft_rowid, 'deft', true);

        try {
            ItaDB::DBDelete($this->getITALWEB(), 'PATCH_DEFT', 'ROW_ID', $patch_deft_rowid);
        } catch (Exception $e) {
            return $this->setErrorStatus('Errore cancellazione record PATCH_DEFT: ' . $e->getMessage());
        }

        foreach ($patch_defd_tab as $patch_defd_rec) {
            try {
                ItaDB::DBDelete($this->getITALWEB(), 'PATCH_DEFD', 'ROW_ID', $patch_defd_rec['ROW_ID']);
            } catch (Exception $e) {
                return $this->setErrorStatus('Errore cancellazione record PATCH_DEFD: ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * 
     * @param type $patchID
     * @return boolean
     */
    public function uploadPatch($patchID) {
        $patch_deft_rec = $this->getPatchDeft($patchID);
        $patch_defd_tab = $this->getPatchDefd($patchID, 'deft', true);

        if (!count($patch_defd_tab)) {
            return $this->setErrorStatus('La patch deve contenere almeno un file.');
        }

        /*
         * Connetto il repository ed effettuo lo switch del ramo
         * per prelevare i file.
         */

        $gitRepository = new itaGit(array(
            'remoteSource' => Config::getConf('updater.patchRepositoryRemote'),
            'workingDir' => Config::getConf('updater.patchRepositoryLocal'),
            'gitBinPath' => Config::getConf('updater.gitBinPath'),
            'defaultRemote' => 'origin',
            'defaultBranch' => 'dist-client'
        ));

        $releaseBranch = 'dist-clienti' . ($patch_deft_rec['RELEASE_RIF'] ? "-{$patch_deft_rec['RELEASE_RIF']}" : '');

//        $gitRepository->fetch();
        $gitRepository->checkout($releaseBranch);

        $appsTempPathFolder = 'patch_' . time();
        $buildTempPath = $this->normalizePath(itaLib::createAppsTempPath($appsTempPathFolder));
        $filesTempPath = $buildTempPath . DIRECTORY_SEPARATOR . 'patch_files';

        /*
         * Esplodo i pattern in percorsi esatti
         */

        $patchFileList = $patch_defd_exp_tab = array();

        foreach ($patch_defd_tab as $patch_defd_rec) {
            if (!$this->checkFilePattern($patch_defd_rec['FILENAME'])) {
                return $this->setErrorStatus("Percorso '{$patch_defd_rec['FILENAME']}' non valido.");
            }

            foreach ($this->explodeFilePattern($patch_defd_rec['FILENAME']) as $filePath) {
                $patchFileList[] = $filePath;
            }
        }

        if (!count($patchFileList)) {
            return $this->setErrorStatus('La patch deve contenere almeno un file.');
        }

        foreach ($patchFileList as $sourcePath) {
            $patchFilename = $this->normalizePath(substr($sourcePath, strlen($this->getPathRepository()) + 1));

            $targetPath = $filesTempPath . DIRECTORY_SEPARATOR . $patchFilename;

            /*
             * Creo prima la cartella
             */

            if (!is_dir(dirname($targetPath)) && !mkdir(dirname($targetPath), 0777, true)) {
                return $this->setErrorStatus("Errore in creazione cartella '" . dirname($targetPath) . "'.");
            }

            if (!copy($sourcePath, $targetPath)) {
                return $this->setErrorStatus("Errore in copia file '$targetPath'.");
            }

            $patch_defd_exp_tab[] = array(
                'PATCH_DEFT_ID' => $patch_deft_rec['ROW_ID'],
                'FILENAME' => $patchFilename,
                'HASH_FILE_NEW' => hash_file('sha256', $sourcePath)
            );
        }

        $this->setAuditFields($patch_deft_rec);

        $patch_name = 'CWOL_PATCH_' . date('ymdhis');
        $patch_deft_rec['PATCH_NAME'] = $patch_name;
        $patch_deft_rec['PATCH_UPLOAD_DATE'] = date('Y-m-d');
        $patch_deft_rec['PATCH_UPLOAD_TIME'] = date('H:i:s');

        if (!$this->updatePatch($patch_deft_rec, $patch_defd_exp_tab)) {
            return false;
        }

        /*
         * Creazione del file patch_info.xml
         */

        $this->createPatchInfoXML($buildTempPath, $patch_deft_rec, $patch_defd_exp_tab);

        /*
         * Creazione zip
         */

        itaZip::zipRecursive(null, $buildTempPath, "$buildTempPath.zip", 'zip', false, false);

        /*
         * Upload in FTP
         */

        $ftp = $this->getFTPConnection();
        if (!$ftp) {
            return false;
        }

        if (itaFtpUtils::getFilesList($ftp, dirname($this->getFTPPatchPath($patch_deft_rec))) === false) {
            if (!itaFtpUtils::makeDirectory($ftp, dirname($this->getFTPPatchPath($patch_deft_rec)))) {
                return $this->setErrorStatus('Errore creazione cartella FTP.');
            }
        }

        if (!itaFtpUtils::writeFileFromBinary($ftp, $this->getFTPPatchPath($patch_deft_rec), file_get_contents("$buildTempPath.zip"))) {
            return $this->setErrorStatus('Errore in scrittura su FTP.');
        }

        itaLib::clearAppsTempPathRecursive($appsTempPathFolder);
        rmdir($buildTempPath);
        unlink("$buildTempPath.zip");

        return true;
    }

    /**
     * 
     * @param type $xmlpath
     * @param type $patch_deft_rec
     * @param type $patch_defd_tab
     * @return type
     */
    public function createPatchInfoXML($xmlpath, $patch_deft_rec, $patch_defd_tab) {
        $XMLString = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";

        $XMLString .= "<PATCH>\n";

        $XMLString .= "<PATCH_DEFT>\n";

        foreach ($patch_deft_rec as $key => $value) {
            $XMLString .= "  <$key>$value</$key>\n";
        }

        $XMLString .= "</PATCH_DEFT>\n";
        $XMLString .= "<PATCH_DEFD>\n";

        foreach ($patch_defd_tab as $i => $record) {
            $XMLString .= "  <RECORD>\n";
            foreach ($record as $key => $value) {
                $XMLString .= "    <$key>$value</$key>\n";
            }
            $XMLString .= "  </RECORD>\n";
        }

        $XMLString .= "</PATCH_DEFD>";

        $XMLString .= "</PATCH>";

        file_put_contents($xmlpath . DIRECTORY_SEPARATOR . 'patch_info.xml', $XMLString);

        return $xmlpath . DIRECTORY_SEPARATOR . 'patch_info.xml';
    }

    /**
     * 
     * @param type $path
     * @param type $rowid
     * @return type
     */
    public function createPatchNotesTXT($path, $rowid) {
        $patch_deft_rec = $this->getPatchDeft($rowid);

        $patchNotes = <<<TEXT
PATCH:  {$patch_deft_rec['PATCH_NAME']}
AUTORE: {$patch_deft_rec['AUTHOR']}
DATA:   {$patch_deft_rec['DATAINSER']} {$patch_deft_rec['TIMEINSER']}
NOTE:
{$patch_deft_rec['PATCH_NOTES']}
TEXT;

        file_put_contents($path . DIRECTORY_SEPARATOR . 'patch_notes.txt', $patchNotes);

        return $path . DIRECTORY_SEPARATOR . 'patch_notes.txt';
    }

    /**
     * Restituisce lista di tutti gli ambiti applicativi
     * @return array
     */
    public function getContextList() {
        $contextList = include ITA_LIB_PATH . '/AppDefinitions/AppDefinitions.php';
        $contextList['framework'] = 'Framework';
        asort($contextList);
        return $contextList;
    }

    public function Ymd2Build($yyyymmdd) {
        return substr($yyyymmdd, 2, 2) . '.' . substr($yyyymmdd, 4, 2) . '.' . substr($yyyymmdd, 6);
    }

    public function Build2Ymd($build) {
        return '20' . substr($build, 0, 2) . substr($build, 3, 2) . substr($build, 6, 2);
    }

    public function Build2Time($build) {
        return substr($build, 9, 2) . ':' . substr($build, 11, 2);
    }

    public function getFTPPatchPath($patch_deft_rec) {
        $releaseFolder = $patch_deft_rec['RELEASE_RIF'] ? 'rel' . str_replace('.', '', $patch_deft_rec['RELEASE_RIF']) : 'norel';
        return self::CWOL_PATCH_DIR . DIRECTORY_SEPARATOR . $releaseFolder . DIRECTORY_SEPARATOR . $patch_deft_rec['PATCH_NAME'] . '.zip';
    }

    public function checkFTPPatch($patchID) {
        $patch_deft_rec = $this->getPatchDeft($patchID);
        $ftp = $this->getFTPConnection();
        if (!$ftp) {
            return false;
        }

        return (boolean) itaFtpUtils::getFilesList($ftp, $this->getFTPPatchPath($patch_deft_rec));
    }

    public function getFTPCurrentReleaseDirectory() {
        $releaseNum = AppUtility::getCitywareReleaseNumber();
        return $this->normalizePath(self::CWOL_PATCH_DIR . DIRECTORY_SEPARATOR . ($releaseNum === true ? 'norel' : 'rel' . str_replace('.', '', $releaseNum)));
    }

    public function getFTPPatchList($filterReleaseNumber = true) {
        $patchFiles = array();

        $ftp = $this->getFTPConnection();
        if (!$ftp) {
            return false;
        }
        if ($filterReleaseNumber === true) {
            $releaseDirs = array($this->getFTPCurrentReleaseDirectory());
        } else if (is_string($filterReleaseNumber)) {
            $releaseDirs = array(self::CWOL_PATCH_DIR . DIRECTORY_SEPARATOR . $filterReleaseNumber);
        } else {
            $releaseDirs = itaFtpUtils::getFilesList($ftp, self::CWOL_PATCH_DIR);
        }

        foreach ($releaseDirs as $releaseDir) {
            $fileList = itaFtpUtils::getFilesList($ftp, $releaseDir);
            $patchFiles = array_merge($patchFiles, is_array($fileList) ? array_map(array($this, 'normalizePath'), $fileList) : array());
        }

        return $patchFiles;
    }

    public function getFTPPatch($ftpPatchPath, $localPatchPath) {
        $ftp = $this->getFTPConnection();
        if (!$ftp) {
            return false;
        }

        file_put_contents($localPatchPath . '.zip', itaFtpUtils::getBinaryFileFromFtp($ftp, $ftpPatchPath));

        itaZip::Unzip($localPatchPath . '.zip', $localPatchPath);

        unlink($localPatchPath . '.zip');

        return true;
    }

    public function getFTPPatchInfo($ftpPatchPath) {
        $tempPatchDir = itaLib::getAppsTempPath(md5($ftpPatchPath));
        if (!$tempPatchDir) {
            itaLib::createAppsTempPath(md5($ftpPatchPath));
        }

        $tempPatchFile = $tempPatchDir . '.zip';

        if (!$tempPatchDir) {
            return $this->setErrorStatus('');
        }

        $ftp = $this->getFTPConnection();
        if (!$ftp) {
            return false;
        }

        file_put_contents($tempPatchFile, itaFtpUtils::getBinaryFileFromFtp($ftp, $ftpPatchPath));

        itaZip::Unzip($tempPatchFile, $tempPatchDir);

        $xmlPatch = simplexml_load_file($tempPatchDir . DIRECTORY_SEPARATOR . 'patch_info.xml');

        itaLib::clearAppsTempPathRecursive(md5($ftpPatchPath));
        rmdir($tempPatchDir);
        unlink($tempPatchFile);

        $patchInfo = array(
            'PATCH_PATH' => $ftpPatchPath,
            'PATCH_DEFT' => array(),
            'PATCH_DEFD' => array()
        );

        foreach ($xmlPatch as $element) {
            $arrayElement = (array) $element;

            if (isset($arrayElement['ROW_ID'])) {
                /*
                 * PATCH_DEFT
                 */

                $patchInfo['PATCH_DEFT'] = $this->cleanXMLArray($arrayElement);
            } else {
                /*
                 * PATCH_DEFD
                 */

                $arrayRecord = (array) $arrayElement['RECORD'];
                if (isset($arrayRecord['PATCH_DEFT_ID'])) {
                    $arrayRecord = array($arrayRecord);
                }

                foreach ($arrayRecord as $patchFileRecord) {
                    $arrayPatchFile = (array) $patchFileRecord;
                    $patchInfo['PATCH_DEFD'][] = $this->cleanXMLArray($arrayPatchFile);
                }
            }
        }

        $patchInfo['PATCH_APPL'] = $this->isPatchApplied($patchInfo['PATCH_DEFT']['ROW_ID']);

        return $patchInfo;
    }

    private function cleanXMLArray($el) {
        foreach ($el as &$v) {
            $v = is_object($v) ? '' : $v;
        }

        return $el;
    }

    public function checkPatchCompatibility($patch_deft_rec) {
        $currentReleaseNumber = AppUtility::getCitywareReleaseNumber();

        if (
            (is_string($currentReleaseNumber) && $patch_deft_rec['RELEASE_RIF'] !== $currentReleaseNumber) ||
            ($currentReleaseNumber === true && $patch_deft_rec['RELEASE_RIF'] != '' )
        ) {
            return $this->setErrorStatus('La patch non è applicabile per la release installata (' . $currentReleaseNumber . ').');
        }

        $buildInfo = AppUtility::getBuildInfo();
        $buildVersion = isset($buildInfo['latest']) && isset($buildInfo['latest']['version']) ? $buildInfo['latest']['version'] : '';

        if (
            !$buildVersion && ($patch_deft_rec['BUILD_TAG_MIN'] || $patch_deft_rec['BUILD_TAG_MAX'])
        ) {
            return $this->setErrorStatus('Versione build attuale non trovata.');
        }

        if ($buildVersion) {
            $releasePrefix = $patch_deft_rec['RELEASE_RIF'] ? $patch_deft_rec['RELEASE_RIF'] . '-' : '';
            $buildDate = DateTime::createFromFormat("{$releasePrefix}y.m.d-Hi", $buildVersion);

            if (!$buildDate) {
                return $this->setErrorStatus('Versione build attuale non valida (' . $buildVersion . ').');
            }

            if ($patch_deft_rec['BUILD_TAG_MIN']) {
                $minBuildDate = DateTime::createFromFormat("y.m.d-Hi", $patch_deft_rec['BUILD_TAG_MIN']);
                if ($minBuildDate > $buildDate) {
                    return $this->setErrorStatus('La versione della build attuale non rientra nei requisiti di build minima.');
                }
            }

            if ($patch_deft_rec['BUILD_TAG_MAX']) {
                $maxBuildDate = DateTime::createFromFormat("y.m.d-Hi", $patch_deft_rec['BUILD_TAG_MAX']);
                if ($buildDate > $maxBuildDate) {
                    return $this->setErrorStatus('La versione della build attuale non rientra nei requisiti di build massima.');
                }
            }
        }

        return true;
    }

    private function normalizePath($path) {
        return rtrim(str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public function applyPatch($patch_deft_rec, $patch_defd_tab) {
        if (!$this->checkPatchCompatibility($patch_deft_rec)) {
            return false;
        }

        $patch_applt_rec = array(
            'PATCH_DEFT_ID' => $patch_deft_rec['ROW_ID'],
            'PATCH_NAME' => $patch_deft_rec['PATCH_NAME'],
            'APPL_MODE' => self::APPL_MODE_MANUAL
        );

        $patch_appld_tab = array();

        $tempPatchDir = $this->normalizePath(itaLib::getAppsTempPath(md5($patch_deft_rec['PATCH_NAME'])));
        if (!is_dir($tempPatchDir)) {
            if (!itaLib::createAppsTempPath(md5($patch_deft_rec['PATCH_NAME']))) {
                $this->setErrorStatus('Impossibile creare cartella temporanea per patch.');
            }
        }

        $tempPatchSource = $tempPatchDir . DIRECTORY_SEPARATOR . 'patch_files' . DIRECTORY_SEPARATOR;
        $this->getFTPPatch($this->getFTPCurrentReleaseDirectory() . DIRECTORY_SEPARATOR . $patch_deft_rec['PATCH_NAME'] . '.zip', $tempPatchDir);

        foreach ($patch_defd_tab as $patch_defd_rec) {
            $targetFilepath = $this->normalizePath(ITA_BASE_PATH . DIRECTORY_SEPARATOR . $patch_defd_rec['FILENAME']);

            $hashFileOrig = hash_file('sha256', $targetFilepath);

            if (!copy($this->normalizePath($tempPatchSource . $patch_defd_rec['FILENAME']), $targetFilepath)) {
                return $this->setErrorStatus("Errore applicazione file patch '{$patch_defd_rec['FILENAME']}'.");
            }

            $patch_appld_tab[] = array(
                'FILENAME' => $patch_defd_rec['FILENAME'],
                'HASH_FILE_NEW' => $patch_defd_rec['HASH_FILE_NEW'],
                'HASH_FILE_ORIG' => $hashFileOrig
            );
        }

        if (!$this->insertPatchAppl($patch_applt_rec, $patch_appld_tab)) {
            return false;
        }

        return true;
    }

    public function isPatchApplied($patch_deft_rowid) {
        $patch_applt_rec = $this->getPatchApplt($patch_deft_rowid, 'deft');
        return (boolean) $patch_applt_rec;
    }

}
