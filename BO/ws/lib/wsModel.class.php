<?php

/**
 *
 * Classe gestione form
 *
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    10.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaProc.class.php');

class wsModel {

    protected $eqAudit;
    protected $lastInsertId;
    private $errMessage;
    private $errCode;

    function __construct() {
        $this->eqAudit = new eqAudit();
    }

    function __destruct() {
        
    }

    public static function getInstance($model) {
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        if (file_exists($modelSrc)) {
            require_once $modelSrc;
        } else {
            return false;
        }
        try {
            return new $model();
        } catch (Exception $exc) {
            return false;
        }
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

    public function getLastInsertId() {
        return $this->lastInsertId;
    }

    public function setLastInsertId($lastInsertId) {
        $this->lastInsertId = $lastInsertId;
    }

    public function insertAudit($DB, $table, $audit_Info, $recordKey = '', $opCode = '99') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $this->eqAudit->logEqEvent($this, array(
                'DB' => $DB->getDB(),
                'DSet' => $Dset,
                'Operazione' => $opCode,
                'Estremi' => $audit_Info,
                'Key' => $recordKey
            ));
            return true;
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
    }

    public function openRecord($DB, $table, $open_Info, $recordKey = '') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $this->eqAudit->logEqEvent($this, array(
                'DB' => $DB->getDB(),
                'DSet' => $Dset,
                'Operazione' => '02',
                'Estremi' => $open_Info,
                'Key' => $recordKey
            ));
            return true;
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
    }

    public function insertRecord($DB, $table, $insert_rec, $insert_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $nRows = ItaDB::DBInsert($DB, $table, $primaryKey, $insert_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento su: " . $Dset . " non avvenuto.");
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '07',
                        'Estremi' => $insert_Info,
                        'Key' => $recordKey
                    ));
                }
                return false;
            } else {
                $this->setLastInsertId(itaDB::DBLastId($DB));
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '04',
                        'Estremi' => $insert_Info,
                        'Key' => $recordKey
                    ));
                }
                return true;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in Inserimento $DB.$table " . $e->getMessage());
            return false;
        }
    }

    /**
     *  Aggiorna il log degli eventi con la modifica effettuata
     *
     * @param type $DB
     * @param String $table  Tabella del database modificata
     * @param Array $update_rec   Array contentente tutte le informazioni
     * @param String $update_Info   Stringa di log che si vuole inserire
     * @return type
     */
    public function updateRecord($DB, $table, $update_rec, $update_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $nRows = ItaDB::DBUpdate($DB, $table, $primaryKey, $update_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento su: " . $Dset . " non avvenuto.");
                //Out::msgStop("Aggiornamento", "Aggiornamento su: " . $Dset . " non avvenuto.");
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '09',
                        'Estremi' => $update_Info,
                        'Key' => $recordKey
                    ));
                }
                return false;
            } else {
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '06',
                        'Estremi' => $update_Info,
                        'Key' => $recordKey
                    ));
                }
                return true;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
    }

    public function deleteRecord($DB, $table, $rowid, $delete_Info, $primaryKey = 'ROWID', $audit = true, $recordKey = '') {
        if (is_string($table)) {
            $Dset = $table;
        } else {
            $Dset = $table->getName();
        }
        try {
            $nRows = ItaDB::DBDelete($DB, $table, $primaryKey, $rowid);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Cancellazione su: " . $Dset . " non avvenuto.");
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '08',
                        'Estremi' => $delete_Info,
                        'Key' => $recordKey
                    ));
                }
                return false;
            } else {
                if ($audit === true) {
                    $this->eqAudit->logEqEvent($this, array(
                        'DB' => $DB->getDB(),
                        'DSet' => $Dset,
                        'Operazione' => '05',
                        'Estremi' => $delete_Info,
                        'Key' => $recordKey
                    ));
                }
                return true;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
    }

}

?>
