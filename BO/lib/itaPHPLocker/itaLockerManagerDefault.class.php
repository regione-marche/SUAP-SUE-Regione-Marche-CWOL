<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of itaLockerManagerDefault
 *
 * @author l.pergolini
 */
require_once ITA_LIB_PATH . '/itaPHPLocker/itaLockerManager.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';

class itaLockerManagerDefault implements itaLockerManager {

    public function lock($db, $table, $Record, $mode = "", $wait = 0, $duration = 300) {
        if (ItaDB::usePDO(App::$itaEngineDB)) {
            return PDOFacade::DBLock($db, $table, $Record, $mode, $wait, $duration);
        } else {
            return ItaDBFacade::DBLock($db, $table, $Record, $mode, $wait, $duration);
        }
    }

    public function unlock($lockID) {
       if (ItaDB::usePDO(App::$itaEngineDB)) {
            return PDOFacade::DBUnLock($lockID);
        } else {
            return ItaDBFacade::DBUnLock($lockID);
        }
    }

    public function lockedBy($db, $tableLocked, $Record) {
        $result = array();
        $result["status"] = 0;
        $result["lockID"] = null;
        $result["token"] = null;
        $result["message"] = null;
        $result["utente"] = null;
        $result["scadenza"] = null;
        
        $sql = 'SELECT
                    *
                FROM LOCKTAB
                WHERE LOCKRECID = \''.$db->getDB().'.'.$tableLocked.'.'.$Record.'\'
                    AND LOCKTOKEN <> \''.App::$utente->getKey('TOKEN').'\'
                    AND LOCKEXP >= '.time();
        $token = ItaDBFacade::DBSQLSelect(App::$itaEngineDB, $sql);
        
        if(!empty($token)){
            $id = intval(substr($token[0]['LOCKTOKEN'],0,6));
            $sql = 'SELECT * FROM UTENTI WHERE UTECOD = '.$id;
            
            $itwDB = ItaDB::DBOpen('ITW');
            $utente = ItaDBFacade::DBSQLSelect($itwDB, $sql, false);
            
            if(!empty($utente)){
                $result['status'] = 1;
                $result['token'] = $token[0]['LOCKTOKEN'];
                $result['utente'] = $utente['UTELOG'];
                $result['scadenza'] = $token[0]['LOCKEXP'];
            }
        }
        
        return $result;
    }

    public function unlockForSession() {
        ItaDBFacade::DBUnLockForSession();
    }
}
