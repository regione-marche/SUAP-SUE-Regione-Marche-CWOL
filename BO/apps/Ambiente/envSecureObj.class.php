<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of envSecureObj
 *
 * @author michele
 */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibStrutture.class.php';

class envSecureObj {

    const SEC_META_KEY_CREATE = 'CREATE';
    const SEC_META_KEY_READ = 'READ';
    const SEC_META_KEY_UPDATE = 'UPDATE';
    const SEC_META_KEY_DELETE = 'DELETE';

    private $errCode;
    private $errMessage;
    private $objContext;
    private $objClass;
    private $objId;
    private $secObjGruppo_Id;
    private $secObj_tab;
    private $secMeta_tab;
    protected $ITALWEB_DB;
    private $sec_meta_key_list = array(
        self::SEC_META_KEY_CREATE => array("DESCRIZIONE" => "Creazione"),
        self::SEC_META_KEY_READ => array("DESCRIZIONE" => "Lettura"),
        self::SEC_META_KEY_UPDATE => array("DESCRIZIONE" => "Aggiornamento"),
        self::SEC_META_KEY_DELETE => array("DESCRIZIONE" => "Cancellazione")
    );

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getObjContext() {
        return $this->objContext;
    }

    public function setObjContext($objContext) {
        $this->objContext = $objContext;
    }

    public function getObjClass() {
        return $this->objClass;
    }

    public function setObjClass($objClass) {
        $this->objClass = $objClass;
    }

    public function getObjId() {
        return $this->objId;
    }

    public function setObjId($objId) {
        $this->objId = $objId;
    }

    public function getSecObjGruppo_Id() {
        return $this->secObjGruppo_Id;
    }

    public function setSecObjGruppo_Id($secObjGruppo_Id) {
        $this->secObjGruppo_Id = $secObjGruppo_Id;
    }

    public function getSecObj_tab() {
        return $this->secObj_tab;
    }

    public function setSecObj_tab($secObjTab) {
        $this->secObj_tab = $secObjTab;
    }

    public function setSecMeta_tab($secMeta_tab) {
        $this->$secMeta_tab = $secMeta_tab;
    }

    public function getSecMeta_tab() {
        return $this->secMeta_tab;
    }

    public function getSec_meta_key_list() {
        return $this->sec_meta_key_list;
    }

    public function addSec_meta_key_list($sec_meta_key_list) {
        // da implementare per il merge di funzioni custom 
        // si farà solo se serve in futuro
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

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * Carica la i dati dei record fng_secObj per i vari gruppi e fng_secmeta
     * relativi ai dati di classe e id definiti nelle proprietà
     * 
     * @return boolean ritorna true se il caricamento è andato a buon fine.
     */
    public function loadSecObjdata($type='') {
        $this->secObj_tab = null;
        $this->secMeta_tab = null;
        $sql = "
            SELECT
                *
            FROM 
                FNG_SECOBJ
            WHERE 
               OBJ_CLASS='{$this->objClass}' AND OBJ_ID={$this->objId}";

        if ($type == '') {
            $sql .= " AND TRASHED = 0 ";
        }

        try {
            $this->secObj_tab = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, true);
            $this->setSecObj_tab($this->secObj_tab);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }

        return true;
    }

    public function loadSecObjGroupMetaData($gruppo_id) {
        $this->setSecObjGruppo_Id($gruppo_id);
        $sql = " SELECT * FROM FNG_SECMETA WHERE SEC_OBJ_ID=" . $this->getSecObjGruppo_Id();
        try {
            $this->secMeta_tab = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, true);
            $this->setSecMeta_tab($this->secMeta_tab);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
    }

    /**
     * 
     */
    public function saveSecObjData($rec) {
        
       // ItaDB::DBInsert($this->getITALWEBDB(), 'FNG_SECOBJ', 'ROW_ID', $rec);
        $ItaModel = new itaModel();
        $insert_Info = "Inserimento SecObj";
        $ItaModel->insertRecord($this->getITALWEBDB(), 'FNG_SECOBJ', $rec, $insert_Info, 'ROW_ID');
        
    }

    public function saveSecObjMetaData($rec) {
      //  ItaDB::DBInsert($this->getITALWEBDB(), 'FNG_SECMETA', 'ROW_ID', $rec);
        $ItaModel = new itaModel();
        $insert_Info = "Inserimento SecMeta";
        $ItaModel->insertRecord($this->getITALWEBDB(), 'FNG_SECMETA', $rec, $insert_Info, 'ROW_ID');
    }

    public function updateSecObjData($rec) {
     //   ItaDB::DBUpdate($this->getITALWEBDB(), 'FNG_SECOBJ', 'ROW_ID', $rec);
        $ItaModel = new itaModel();
        $update_Info = "Aggiornamento SecObj : ".$rec['ROW_ID'];
        $ItaModel->updateRecord($this->getITALWEBDB(), 'FNG_SECOBJ', $rec, $update_Info, 'ROW_ID');
    }

    public function updateSecObjMetaData($rec) {
     //   ItaDB::DBUpdate($this->getITALWEBDB(), 'FNG_SECMETA', 'ROW_ID', $rec);
        $ItaModel = new itaModel();
        $update_Info = "Aggiornamento SecMeta : ".$rec['ROW_ID'];
        $ItaModel->updateRecord($this->getITALWEBDB(), 'FNG_SECMETA', $rec, $update_Info, 'ROW_ID');
    }

    /**
     * 
     * @return type
     */
    public function getObjContextGroups() {

        $sql = "
            SELECT
                *
            FROM
                FNG_GRUPPO
            WHERE
                CONTESTO='" . $this->objContext . "' AND TRASHED=0 AND ID NOT IN (SELECT GRUPPO_ID FROM FNG_SECOBJ WHERE OBJ_ID=" . $this->objId . " AND OBJ_CLASS='" . $this->objClass . "') ORDER BY DESCRIZIONE";
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);

        return $tab;
    }

    /* Metodo che ritorna i permessi associati all'utente per quell'ObjClass e ObjID */

    public function getPermission($user) {
        $ObjClass = $this->objClass;
        $ObjID = $this->objId;
        /* Cerco i gruppi associati a quella classe e id */
        $sql = "SELECT FNG_SECOBJ.ROW_ID ,FNG_SECOBJ.GRUPPO_ID,FNG_SECOBJ.DATAINI,FNG_SECOBJ.DATAEND,FNG_SECOBJ.TRASHED  FROM FNG_SECOBJ  WHERE OBJ_CLASS='" . $ObjClass . "' AND OBJ_ID ='" . $ObjID . "'";

        $tab = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, true);


        /* Per ogni gruppo controllo che non sia trashed e che non sia scaduto il permesso e aggiungo all'array i permessi */
        foreach ($tab as $key => $value) {
            if (($value['TRASHED'] == 0) && (($value['DATAEND'] > date("Ymd")) || !$value['DATAEND'])) {
                $sql1 = "SELECT CHIAVE,VALORE FROM FNG_SECMETA WHERE SEC_OBJ_ID =" . $value['ROW_ID'];
                $tab2 = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql1, true);
                foreach ($tab2 as $key2 => $value2) {
                    $tab[$key][$value2['CHIAVE']] = $value2['VALORE'];
                }
            }
        }

        /* Per ogni gruppo Cerco dentro la tabella membri se c'è l'utente loggato. Se non lo trovo o se è scaduto tolgo il gruppo dall'array gruppi  */
        foreach ($tab as $key => $value) {
            $sql = "SELECT * FROM FNG_MEMBRI WHERE TRASHED = 0 AND USERNAME='" . $user . "' AND IDGRUPPO='" . $value['GRUPPO_ID']."'";
            $tab4 = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, false);
            if (!$tab4) {
                unset($tab[$key]);
            }
            if ($tab4['DATAEND']) {
                if ((($tab4['DATAEND'] < date("Ymd")))) {
                    unset($tab[$key]);
                }
            }
        }

        /* Se rimane almeno un gruppo inizializzo i permessi a zero */
        if (count($tab) > 0) {
            /* Inizializzo i permessi a 0 */
            $permessi = $this->initializePermission();
        }

        /* Per ogni gruppo controllo se ha i permessi ad 1 . Se trovo un permesso a 1 lo setto. Ritorno l'array con i permessi e i valori associati */
        /* Quindi se un utente è associato a più gruppi faccio l'OR */
        foreach ($permessi as $key => &$permesso) {

            foreach ($tab as $tab_rec) {

                if ($tab_rec[$key] == 1) {
                    $permesso = 1;
                }
            }
        }
        
        
        return $permessi;
    }

    /* Funzione per controllare se un utente è un superUtente per quel contesto */

    public function checkSuper($context, $user) {

        $sql = "SELECT * FROM FNG_GRUPPO WHERE SUPER = 1 AND CONTESTO='" . $context . "'";
        $tab = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, true);
        foreach ($tab as $key => $value) {
            $sql = "SELECT * FROM FNG_MEMBRI WHERE TRASHED = 0 AND IDGRUPPO = " . $value['ID'] . " AND USERNAME='" . $user . "'";
            $tab4 = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, false);

            if ($tab4 && (($tab4['DATAEND'] > date("Ymd")) || !$tab4['DATAEND'])) {

                return true;
            }
        }
        return false;
    }

    /* Funzione per inizializzare i permessi a 0 */

    public function initializePermission() {
        $permessi = array();
        $list = $this->getSec_meta_key_list();
        foreach ($list as $key => $value) {

            $permessi[$key] = 0;
        }
        return $permessi;
    }

    /* Funzione per ottenere un resume dei gruppi/permessi a partire dall'obj_id .*/

    public function loadSecObjMeta($sec_obj_id) {
        $sql = " SELECT A.*,B.NOME FROM FNG_SECOBJ A LEFT JOIN FNG_GRUPPO B ON A.GRUPPO_ID = B.ID WHERE A.TRASHED= 0 AND  A.OBJ_ID =" . $sec_obj_id;
      
        $tab = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql, true);
        foreach ($tab as &$tab_rec) {
            $sql1 = " SELECT CHIAVE,VALORE FROM FNG_SECMETA WHERE SEC_OBJ_ID =" . $tab_rec['ROW_ID'];
            $tab1 = ItaDB::DBSQLSelect($this->getITALWEBDB(), $sql1, true);
            $per = array();
            foreach ($tab1 as $key => $value) {
                $per[$value['CHIAVE']] = $value['VALORE'];
            }
            $permessi = $this->initializePermission();
            if (count($permessi) <= count($per)) {
                foreach ($per as $key => $value) {
                    $tab_rec[$key] = $value;
                }
            }
            else {
                foreach ($permessi as $key => $value) {
                    $tab_rec[$key] = $value;
                }
            }
        }

        return $tab;
    }

}
