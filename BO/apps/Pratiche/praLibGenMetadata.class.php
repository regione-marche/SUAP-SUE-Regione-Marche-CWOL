<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    16.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibGenMetadata {

    public $PRAM_DB;
    public $praLib;

    function __construct() {
        try {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
        $this->praLib = new praLib();
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

    public function getGenMetadata($ricMetadata, $multi = false) {
        if (!$ricMetadata || $ricMetadata['CLASSE'] . $ricMetadata['CHIAVE'] . $ricMetadata['CAMPO'] == '') {
            return false;
        }
        $sql = "SELECT * FROM GENMETADATA WHERE 1=1";
        if ($ricMetadata['CLASSE'] != '') {
            $sql .= " AND CLASSE = '" . $ricMetadata['CLASSE'] . "'";
        }
        if ($ricMetadata['CHIAVE'] != '') {
            $sql .= " AND CHIAVE = '" . $ricMetadata['CHIAVE'] . "'";
        }
        if ($ricMetadata['CAMPO'] != '') {
            $sql .= " AND CAMPO = '" . $ricMetadata['CAMPO'] . "'";
        }

        return ItaDB::DBSQLSelect($this->PRAM_DB, $sql, $multi);
    }

    public function insertGenMetadata($insMetadata) {
        if ($insMetadata) {
            try {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, 'GENMETADATA', 'ROW_ID', $insMetadata);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore inserimento nella tabella GENMETADATA.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento nella tabella GENMETADATA. " . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    public function updateGenMetadata($updMetadata) {
        if ($updMetadata) {
            try {
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'GENMETADATA', 'ROW_ID', $updMetadata);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento nella tabella GENMETADATA.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore aggiornamento nella tabella GENMETADATA. " . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

    public function deleteGenMetadata($delMetadata) {
        if ($delMetadata) {
            try {
                $nrow = ItaDb::DBDelete($this->PRAM_DB, 'GENMETADATA', 'ROW_ID', $delMetadata['ROW_ID']);
                if ($nrow == 0) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore cancellazizone nella tabella GENMETADATA.");
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore cancellazione nella tabella GENMETADATA. " . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

}
