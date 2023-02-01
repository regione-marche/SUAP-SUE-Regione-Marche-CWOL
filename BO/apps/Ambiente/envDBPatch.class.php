<?php

/**
 * Description of envDBPatch
 *
 * @author Carlo <carlo@iesari.me>
 */
class envDBPatch {

    private $errCode;
    private $errMessage;
    private $results = array();
    private $DBs = array();

    const VERSION_TABLE_NAME = 'SCHEMA_VERSION';

    public function getDB($dbname, $domain) {
        if ( !isset( $this->DBs[$dbname] ) ) {
            $this->DBs[$dbname] = array();
        }

        if ( !isset( $this->DBs[$dbname][$domain] ) ) {
            try {
                $this->DBs[$dbname][$domain] = ItaDB::DBOpen($dbname, $domain);
            } catch (Exception $e) {
                $this->errCode = -1;
                $this->errMessage = $e->getMessage();
                $this->setResults($dbname, $domain, $this->errMessage);
                return false;
            }
        }
        return $this->DBs[$dbname][$domain];
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }
    
    private function setResults($dbname, $domain, $result) {
        if ( !isset( $this->results[$dbname] ) ) {
            $this->results[$dbname] = array();
        }
        
        $this->results[$dbname][$domain] = $result;
    }
    
    public function getResults() {
        return $this->results;
    }

    public function DBPatchPath($dbname, $domain) {
        if ( !($DB = $this->getDB($dbname, $domain)) ) {
            return false;
        }
        
        return ITA_BASE_PATH . "/schema/{$DB->getDBMS()}/{$dbname}";
    }

    public function DBPatch($dbname, $domain) {
        $currentVersion = false;
        $availableVersion = false;
        $workingVersion = false;

        if ( !($DB = $this->getDB($dbname, $domain)) ) {
            return false;
        }

        if (!$DB->exists()) {
            $this->errCode = -1;
            $this->errMessage = "Il DB {$dbname}{$domain} non esiste.";
            $this->setResults($dbname, $domain, $this->errMessage);
            return false;
        }

        if (($currentVersion = $this->DBVersion($dbname, $domain)) === false) {
            return false;
        }

        if ($currentVersion < ($availableVersion = $this->DBVersionAvailable($dbname, $domain))) {
            $workingVersion = $currentVersion + 1;

            while ($workingVersion <= $availableVersion) {

                if (( $result = $this->DBApplyPatch($dbname, $domain, $workingVersion) ) !== true) {
                    if (!$this->DBApplyVersion($dbname, $domain, $workingVersion, 'KO', $result)) {
                        return false;
                    }

                    $this->errCode = -1;
                    $this->errMessage = $result;
                    $this->setResults($dbname, $domain, $this->errMessage);
                    return false;
                }

                if (!$this->DBApplyVersion($dbname, $domain, $workingVersion)) {
                    return false;
                }

                $workingVersion++;
            }
            
            $this->setResults($dbname, $domain, "DB aggiornato. (dalla " . $this->DBVersionFormat($currentVersion) . " alla " . $this->DBVersionFormat($availableVersion) . ")");
        } else {
            $this->setResults($dbname, $domain, "Nessun aggiornamento disponibile.");
        }

        return true;
    }

    private function DBApplyPatch($dbname, $domain, $version) {
        $patchFile = $dbname . "_" . $this->DBVersionFormat($version) . ".sql";
        $patchPath = $this->DBPatchPath($dbname, $domain) . $patchFile;

        if (!file_exists($patchPath)) {
            $this->errCode = -1;
            $this->errMessage = "File di PATCH '$patchFile' mancante.\n";
            $this->setResults($dbname, $domain, $this->errMessage);
            return false;
        }

        $sql = file_get_contents($patchPath);
        $queries = explode(";\n", str_replace(array(";\r\n", ";\r"), ";\n", $sql));

        if ( !($DB = $this->getDB($dbname, $domain)) ) {
            return false;
        }
        
        try {
            foreach ($queries as $query) {
                ItaDB::DBSQLExec($DB, $query);
            }
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function DBApplyVersion($dbname, $domain, $versione, $stato = 'OK', $note = '') {
        if ( !($DB = $this->getDB($dbname, $domain)) ) {
            return false;
        }

        $version_rec = array(
            'VERSIONE' => $this->DBVersionFormat($versione),
            'TIMESTAMP' => time(),
            'UTENTE' => $DB->getUser(),
            'IP' => $_SERVER['REMOTE_ADDR'],
            'STATO' => $stato,
            'NOTE' => $note
        );

        if (!ItaDB::DBInsert($DB, envDBPatch::VERSION_TABLE_NAME, 'ROWID', $version_rec)) {
            $this->errCode = -1;
            $this->errMessage = "Errore nell'inserimento del record di versione.";
            $this->setResults($dbname, $domain, $this->errMessage);
            return false;
        }

        return true;
    }

    public function DBVersion($dbname, $domain) {
        if ( !($DB = $this->getDB($dbname, $domain)) ) {
            return false;
        }
        
        try {
            $schema_rec = ItaDB::DBSQLSelect($DB, "SELECT MAX(VERSIONE) AS VERSIONE FROM SCHEMA_VERSION WHERE STATO = 'OK' ORDER BY ROWID DESC", false);
            return intval($schema_rec['VERSIONE']);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = $e->getMessage();
            $this->setResults($dbname, $domain, $this->errMessage);
            return false;
        }
    }

    public function DBVersionAvailable($dbname, $domain) {
        $patches = glob($this->DBPatchPath($dbname, $domain) . "/{$dbname}_*");
        return intval(end(explode('_', pathinfo(end($patches), PATHINFO_BASENAME))));
    }

    public function DBVersionFormat($int) {
        return str_pad($int, 6, '0', STR_PAD_LEFT);
    }

}
