<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once ITA_LIB_PATH . '/itaPHPLocker/itaLockerManager.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BDI.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
require_once ITA_LIB_PATH . '/DBPDO/PDODriver.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';

/**
 * Gestione del Locker lato cityware
 *
 * @author l.pergolini
 */
class itaLockerManagerCityware implements itaLockerManager {

    const FORMAT_DATE = "Ymd-His";
    const FORMAT_OUTPUT_DATE = "d/m/Y H:i:s";

    private $recordlockService;
    private $tablename;
    private $libDB_BWE;
    private $response; //array result composto da status, $token , $message 
    private $connectionLocker;

    public function __construct() {
        $this->setLibDB_BWE(new cwbLibDB_BWE());
        $this->setRecordlockService(cwbModelServiceFactory::newModelService('cwbBweReclck', true));
        $this->setTablename(cwbModelHelper::tableNameByModelName($this->recordlockService->getModelName()));
    }

    public function lock($db, $tableLocked, $Record, $mode = "", $wait = 0, $duration = 300) {
        $this->initResult();
        try {
            $locked = $this->isLocked($this->getConnectionLocker(), $tableLocked, $Record, $wait);
            if (!$locked) {
                $this->lockExecutor($this->getConnectionLocker(), $tableLocked, $Record, $duration);
            }
        } catch (Exception $e) {
            //caso di lettura contemporanea (violazione vincolo unique db) 
            if ($e->getNativeErrorCode() == PDODriver::KEY_CONSTRAINT_UNIQUE_EXCEPTION) {
                $locked = $this->isLocked($this->getConnectionLocker(), $tableLocked, $Record);
                return $this->response;
            }
            return array('status' => -1,"message"=>$e->getNativeErroreDesc());
        }
        return $this->response;
    }

    public function lockedBy($db, $tableLocked, $Record) {
        $this->initResult();
        $filtri = array();
//        $filtri["ID_RECORD"] = $db->getDB() . "." . $tableLocked . "." . $Record;
        $filtri["ID_RECORD"] = "CITYWARE." . $tableLocked . "." . $Record;
        $filtri["LOCKTOKENDIV"] = App::$utente->getKey('TOKEN');

        $lockedList = $this->getLibDB_BWE()->leggiBweRecLck($filtri);
        if (!empty($lockedList)) {
            $last = array_pop((array_slice($lockedList, -1)));
            $now = new DateTime(); //ora corrente per il controllo

            $expired = DateTime::createFromFormat(self::FORMAT_DATE, $last["DATAORAINI"]);
            $expired = $this->addSecondToDate($expired, $last["DURATA"]);

            $interval = date_diff($now, $expired);

            if ($interval->invert == 0 && $interval->format('%s') > 0) {
                $this->response["token"] = $last["TOKEN"];
                $this->response["utente"] = $last["UTENTE"];
                $this->response["scadenza"] = date(self::FORMAT_OUTPUT_DATE, $expired);
            }
        }
        return $this->response;
    }

    public function unlock($lockID, $db = null) {
        $locked = $this->getLibDB_BWE()->leggiBweRecLckChiave($lockID);
        if (!$locked) {
            return;
        }
        try {
            $this->getRecordlockService()->deleteRecord($this->getConnectionLocker(), $this->getTablename(), $locked, '');
            return array('status' => 0);
        } catch (Exception $e) {
            return array('status' => -1);
        }
    }

    //controlla se esistono del record  attivi di un altro utente rispetto a quello corrente 
    private function isLocked($db, $tableLocked, $Record, $wait = 0) {
        $filtri = array();
        $filtri["ID_RECORD"] = "CITYWARE." . $tableLocked . "." . $Record;
        
        $startTime = new DateTime();
        $now = $startTime;
        while($startTime->getTimestamp() + $wait >= $now->getTimestamp()) {
            $lockedList = $this->getLibDB_BWE()->leggiBweRecLck($filtri);
            if (count($lockedList) === 0) {
                return false;
            }
            
            //Controllo record bloccati attivi 
            $now = new DateTime(); //ora corrente per il controllo

            foreach ($lockedList as $recordLock) {
                $expired = DateTime::createFromFormat(self::FORMAT_DATE, $recordLock["DATAORAINI"]);
                $expired = $this->addSecondToDate($expired, $recordLock["DURATA"]);
                $interval = $now->diff($expired);
                if ($interval->invert == 0) {
                    if(App::$utente->getKey('TOKEN') == $recordLock['CONN_ID']){
                        $this->response["status"] = 0;
                        $this->response['lockID'] = $recordLock['ID_LOCK'];
                        return false;
                    }
                    else{
                        $this->response["status"] = -1;
                        $this->response['lockID'] = null;
                    }
                    $this->response["token"] = $recordLock["TOKEN"];
                    $this->response["utente"] = $recordLock["UTENTE"];
                    $this->response["scadenza"] = $expired->format(self::FORMAT_OUTPUT_DATE);
                    $this->response["message"] = "Record bloccato dal operatore '" . $recordLock["UTENTE"] . "'. Data\ora scadenza del blocco:" . $this->response["scadenza"];
                    //return true;
                } else {
                    //Effettuo la delete dei record scaduti 
                    $this->getRecordlockService()->deleteRecord($this->getConnectionLocker(), $this->getTablename(), $recordLock, null);
                    return false;
                }
            }
            sleep(1);
        }
        return true;
    }

    private function lockExecutor($db, $tableLocked, $Record, $duration = 300) {
        $recordLock = $this->getRecordlockService()->define($db, $this->getTablename());

        $recordLock["ID_RECORD"] = "CITYWARE." . $tableLocked . "." . $Record;
        $recordLock["DES_RECORD"] = "CITYWARE." . $tableLocked . "." . $Record;
        $recordLock["CLASSE"] = $tableLocked;
        $recordLock["UTENTE"] = App::$utente->getKey('nomeUtente');
        $recordLock["CONN_ID"] = App::$utente->getKey('TOKEN');
        $date = new DateTime(); //data Corrente 
        $recordLock["DATAORAINI"] = $date->format(self::FORMAT_DATE);
        $recordLock["DURATA"] = $duration;
        $this->getRecordlockService()->insertRecord($this->getConnectionLocker(), $this->getTablename(), $recordLock, '');

        //Ritorna le informazioni del lock 
        $this->response['status'] = 0; //Esito positivo 
        $this->response['lockID'] = $this->getRecordlockService()->getLastInsertId();
        $this->response['utente'] = $recordLock["UTENTE"];

        $expiredDateTime = DateTime::createFromFormat(self::FORMAT_DATE, $recordLock["DATAORAINI"]);
        $expiredDateTime = $this->addSecondToDate($expiredDateTime, $duration);

        $this->response['scadenza'] = $expiredDateTime->format(self::FORMAT_OUTPUT_DATE);
        $this->response['token'] = $recordLock["TOKEN"];
    }

    private function initResult() {
        $this->response["status"] = 0;
        $this->response["lockID"] = NULL;
        $this->response["token"] = null;
        $this->response["message"] = null;
        $this->response["utente"] = null;
        $this->response["scadenza"] = null;
    }

    private function getRecordlockService() {
        return $this->recordlockService;
    }

    private function setRecordlockService($recordlockService) {
        $this->recordlockService = $recordlockService;
    }

    private function getTablename() {
        return $this->tablename;
    }

    private function setTablename($tablename) {
        $this->tablename = $tablename;
    }

    private function getLibDB_BWE() {
        return $this->libDB_BWE;
    }

    private function setLibDB_BWE($libDB_BWE) {
        $this->libDB_BWE = $libDB_BWE;
    }

    private function addSecondToDate($date, $sec) {
        return $date->add(new DateInterval('PT' . $sec . 'S'));
    }

    private function loadPkPosition($tableLocked) {
        $lib = new cwbLibDB_BDI();
        $filtri = array("NOMETAB" => $tableLocked, "TIPOINDICE" => 1);
        return $lib->leggiBdiIndici($filtri);
    }

    private function getPkPositionDescriptor($db, $tableLocked, $Record) {
        try {
            $pkPosistionsCols = $this->loadPkPosition($tableLocked);
            if ($pkPosistionsCols) {
                $pks = $db->getPks($tableLocked);
                $array = explode('|', $Record);
                $filtri = array();
                $i = 0;
                foreach ($pks as $pk) {
                    $filtri[$pk] = $array[$i];
                    $i++;
                }
                $descriptionKey = "";
                foreach ($pkPosistionsCols as $pkPosition) {
                    $descriptionKey .= $filtri[$pkPosition["NOMECAMPO"]] . "|";
                }
                $descriptionKey = substr_replace($descriptionKey, '', -1);
            } else {
                $descriptionKey = $Record;
            }
            return $descriptionKey;
        } catch (Exception $ex) {
            //Se non esiste la tabella BDI_INDI
            return $Record;
        }
    }

    private function getConnectionLocker() {
        if (isset($this->connectionLocker)) {
            return $this->connectionLocker;
        } else {
            $this->connectionLocker = $this->getLibDB_BWE()->getCitywareLockDB();
            return $this->connectionLocker;
        }
    }

    public function unlockForSession() {
        $ret = array();
        $ret['status'] = 0;
        try {
            $filtri["CONN_ID"] = App::$utente->getKey('TOKEN');
            $lockedList = $this->getLibDB_BWE()->leggiBweRecLck($filtri);
            if (is_array($lockedList)) {
                foreach ($lockedList as $locked) {
                    $this->getRecordlockService()->deleteRecord($this->getConnectionLocker(), $this->getTablename(), $locked, '');   
                }           
            }            
        } catch (Exception $e) {
            $ret['status'] = -1;
        }
        return $ret;
    }

}
