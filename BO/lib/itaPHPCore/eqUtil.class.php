<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of eqUtilclass
 *
 * @author utente
 */
class eqUtil {

    function openDB() {
        try {
            $ITW_DB=ItaDB::DBOpen('ITW');
            return  $ITW_DB;
        }catch(Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param <string> $token          TOKEN
     * @param <string> $varName        Nome Variabile
     * @param <boolean> $exactMatch     true = Ricerca esatta per nome, false = Ricerca Nome che inizia per
     * @return <array>                  Array associativo con chiave uguale a nome variabile
     */
    static public function getEqSession($token,$varName='',$exactMatch=true) {
        $ITW_DB=self::openDB();
        if (!$ITW_DB) {
            return false;
        }
        
        $Sessio_tab=self::getRecords($ITW_DB,$token,$varName,$exactMatch);
        if (!$Sessio_tab) {
            return false;
        }
        $Sessio_values=array();
        foreach ($Sessio_tab as $Sessio_rec) {
            $key=$Sessio_rec['SESNOM'];
            if (!array_key_exists($key, $Sessio_values)) {
                $Sessio_values[$key]=$Sessio_rec['SESVAL'];
            }else {
                $Sessio_values[$key] = $Sessio_values[$key].$Sessio_rec['SESVAL'];
            }
        }
        return $Sessio_values;
    }

    static public function setEqSession($token,$valName,$value) {

    }


    /**
     *
     * @param <string> $token          TOKEN
     * @param <string> $varName        Nome Variabile
     * @param <boolean> $exactMatch    true = Ricerca esatta per nome, false = Ricerca Nome che inizia per
     * @return <boolean>               true = Cancellazione effettuata, False = Cancellazione Fallita
     */
    static public function delEqSession($token,$varName='',$exactMatch=true) {
        $ITW_DB=self::openDB();
        if (!$ITW_DB) {
            return false;
        }
        $Sessio_tab=self::getRecords($ITW_DB,$token,$varName,$exactMatch);
        if (!$Sessio_tab) {
            return false;
        }
        foreach ($Sessio_tab as $Sessio_rec) {
            try {
                $nrow=ItaDB::DBDelete($ITW_DB, "SESSIO", 'ROWID', $Sessio_rec['ROWID']);                
            }catch(Exception $e) {
                return false;
            }
        }
        return true;
    }

    private function getRecords($ITW_DB,$token,$varName='',$exactMatch=true) {
        $where="SESTOK = '$token'";
        if ($varName) {
            if ($exactMatch == true) {
                $where .= " AND SESNOM ='$varName'";
            }else {
                $where .= " AND SESNOM LIKE '$varName%'";
            }
        }

        $Sessio_tab=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM SESSIO WHERE $where ORDER BY SESNOM,SESSEQ,SESSUB");
        if (!$Sessio_tab) {
            return false;
        }
        return $Sessio_tab;

    }
}
?>
