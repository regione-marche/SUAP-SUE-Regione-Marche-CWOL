<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of envDBCommand
 *
 * @author michele
 */
class envDBUtils {

    /**
     * Copia uno specifico schema da una ditta sorgete a una ditta destinazione
     * 
     * @param string $DBAlias => alias del database (es. ITALWEB)
     * @param string $ditta_SOR => codice ditta sorgente
     * @param string $ditta_DES => codice ditta destinazione
     * @return array() $ret => array('status', 'retSql', 'message')<br/>status=0 => operazione andata a buon fine
     */
    public static function cloneSchema($DBAlias, $ditta_SOR, $ditta_DES) {
        $ret = array(
            "status" => 0,
            "retSql" => '',
            "message" => ''
        );
        $objDBSor = ItaDB::DBOpen($DBAlias, $ditta_SOR);
        $objDBDes = ItaDB::DBOpen($DBAlias, $ditta_DES);

        $DBNameSor = $objDBSor->getDB();
        $DBNameDes = $objDBDes->getDB();
        $TableNamesSor = $objDBSor->getTablesInfo();
        foreach ($TableNamesSor as $TableName => $TableInfo) {
            $sqlClone = "CREATE TABLE IF NOT EXISTS $DBNameDes.$TableName LIKE " . $DBNameSor . "." . $TableName;
            try {
                $retSql = ItaDB::DBSQLExec(App::$itaEngineDB, $sqlClone);
            } catch (Exception $exc) {
                $ret['status'] = -1;
                $ret['retSql'] = $retSql;
                $ret['message'] = $exc->getMessage();
                return false;
            }
        }
        $ret['retSql'] = $retSql;
        return $ret;
    }

    /**
     * Crea un nuovo DB
     * 
     * @param string $DBAlias => alias del database (es. ITALWEB)
     * @param string $ditta => ditta per cui creare il DB
     * @param type $characterSet
     * @param type $collate
     * @return array() $ret => array('status', 'retSql', 'message')<br/>status=0 => operazione andata a buon fine
     */
    public static function creaDB($DBAlias, $ditta, $characterSet = '', $collate = '') {
        $objDB = ItaDB::DBOpen($DBAlias, $ditta);
        $nomeDB = $objDB->getDB();
        $ret = array(
            "status" => 0,
            "retSql" => '',
            "message" => ''
        );
        $sql = "CREATE DATABASE IF NOT EXISTS $nomeDB DEFAULT CHARACTER SET latin1 COLLATE latin1_general_cs";
        try {
            $retSql = ItaDB::DBSQLExec(App::$itaEngineDB, $sql);
        } catch (Exception $exc) {
            $ret['status'] = -1;
            $ret['message'] = $exc->getMessage();
        }
        $ret['retSql'] = $retSql;
        return $ret;
    }

    /**
     * 
     * @param string $DBAlias => alias del database (es. ITALWEB)
     * @param string $ditta_SOR => codice ditta sorgente
     * @param string $tblSor => nome tabella sorgente
     * @param string $ditta_DES => codice ditta destinazione
     * @param string $tblDes => nome tabella di destinazione
     * @param string $where => condizione per il popolamento dei dati
     * @return array() $ret => array('status', 'retSql', 'message')<br/>status=0 => operazione andata a buon fine
     */
    public static function copyTableData($DBAlias, $ditta_SOR, $tblSor, $ditta_DES, $tblDes, $where = '') {
        $ret = array(
            "status" => 0,
            "retSql" => '',
            "message" => ''
        );

        $objDBSor = ItaDB::DBOpen($DBAlias, $ditta_SOR);
        $objDBDes = ItaDB::DBOpen($DBAlias, $ditta_DES);

        $whereSor = ($where != '') ? "WHERE $where" : '';
        $sql = "INSERT INTO $tblDes SELECT * FROM " . $objDBSor->getDB() . ".$tblSor $whereSor";
        try {
            $retSQL = itaDB::DBSQLExec($objDBDes, $sql);
        } catch (Exception $exc) {
            $ret['status'] = -1;
            $ret['message'] = $exc->getMessage();
        }
        $ret['retSql'] = $retSQL;
        return $ret;
    }

    /**
     * Controlla esistenza del DB
     * 
     * @param string $db => alias del db (es. ITALWEB)
     * @param string $dittaCOMM => codice ditta
     * @return boolean
     */
    public static function checkExistDB($db, $dittaCOMM = "") {
        try {
            if ($dittaCOMM) {
                $DB = ItaDB::DBOpen($db, $dittaCOMM);
            } else {
                $DB = ItaDB::DBOpen($db);
            }
        } catch (Exception $exc) {
            return false;
        }
        if ($DB == "") {
            return false;
        } else {
            if (!$arrayTables) {
                return false;
            }
        }
        return true;
    }

}
