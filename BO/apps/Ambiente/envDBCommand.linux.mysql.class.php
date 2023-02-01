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
class envDBCommand {

    public static function schemaDump($DB_SOR) {
        $UTENTE = '';
        $PASSWORD = '';
        $FILE_DUMP = '';

        $UTENTE = App::$itaEngineDB->getUser();
        $PASSWORD = App::$itaEngineDB->getPassword();
        $FILE_DUMP = sys_get_temp_dir() . "/" . App::$utente->getKey('TOKEN') . "_tmp_dump_schema_$DB_SOR.sql";
        $FILE_DUMP_BASE = sys_get_temp_dir() . "/" . App::$utente->getKey('TOKEN') . "_tmp_dump_schema_$DB_SOR.base.sql";
        $comando = "mysqldump --default-character-set=Latin1 --extended-insert --skip-add-drop-table --no-create-db --no-data $DB_SOR -u $UTENTE -p$PASSWORD > $FILE_DUMP_BASE "; //| sed 's/ AUTO_INCREMENT=[0-9]*\b//' > $FILE_DUMP ";        
        $retcmd = exec($comando, $retArr, $retExec);
        if ($retExec !== 0) {
            return false;
        }
        $comandoSed = "cat $FILE_DUMP_BASE | sed 's/ AUTO_INCREMENT=[0-9]*\b//' > $FILE_DUMP";
        exec($comandoSed, $retArrSed, $retExecSed);
        unlink($FILE_DUMP_BASE);           
        if ($retExecSed !== 0) {
            return false;
        }
        return $FILE_DUMP;        
    }

    public static function importDump($IMPORT_FILE, $DB_DES) {
        $UTENTE = '';
        $PASSWORD = '';
        $UTENTE = App::$itaEngineDB->getUser();
        $PASSWORD = App::$itaEngineDB->getPassword();
        $comando = "mysql < $IMPORT_FILE -u$UTENTE -p$PASSWORD --database $DB_DES";
        exec($comando, $retArr, $retExec);
        if ($retExec == 0) {
            return true;
        } else {
            return false;
        }
    }

}

?>
