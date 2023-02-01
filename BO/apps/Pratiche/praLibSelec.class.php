<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @author     
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    24.04.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */


class praLibSelec {
    public $SELEC_DB;
    
    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->SELEC_DB = ItaDB::DBOpen('SELEC', $ditta);
            } else {
                $this->SELEC_DB = ItaDB::DBOpen('SELEC');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
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

    public function setSELECDB($SELEC_DB) {
        $this->SELEC_DB = $SELEC_DB;
    }
    
    public function getSELECDB() {
        return $this->SELEC_DB;
    }


    
    /**
     * Restituisce unrecord pratica - Riepistru
     * @param type $Codice
     * @param type $tipoRic
     * @param Boolean $multi Non usato
     * @return type
     */
    public function getRiepistru($Codice, $tipoCodice = 'codice', $multi = false) {
        if ($tipoCodice == 'codice') {
            $sql = "SELECT * FROM RIEPISTRU WHERE PROGESNUM='" . $Codice . "'";
        } /*else if ($tipoCodice == 'richiesta') {
            $sql = "SELECT * FROM PROGES WHERE GESPRA='" . $Codice . "'";
        } else if ($tipoCodice == 'protocollo') {
            $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
        } else if ($tipoCodice == 'antecedente') {
            $sql = "SELECT * FROM PROGES WHERE GESPRE='" . $Codice . "'";
        } else if ($tipoCodice == 'codiceProcedimento') {
            $sql = "SELECT * FROM PROGES WHERE GESCODPROC='" . $Codice . "'";
        } */ else {
            $sql = "SELECT * FROM RIEPISTRU WHERE ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getSELECDB(), $sql, $multi);
    }
    
    public function getLastId($Tabella, $CampoId = 'ID') {
        $ultimoId = 1;
        $sql = "SELECT * FROM " . $Tabella . " ORDER BY " . $CampoId . " DESC";
        $record = ItaDB::DBSQLSelect($this->getSELECDB(), $sql, false);
        if ($record){
            $ultimoId = $record[$CampoId] + 1;
        }
        
        return $ultimoId;
    }

    public function getRecordSelec($tabella, $condizione, $multi = false) {

        $sql = "SELECT * FROM " . $tabella . " " . $condizione;
        return ItaDB::DBSQLSelect($this->getSELECDB(), $sql, $multi);
        
    }

    public function getIdComistat(){
        $idComistat = 0;
        
        $utiEnte = new utiEnte();
        
        $PARMENTE_rec = $utiEnte->GetParametriEnte();
        
        $sql = "SELECT * FROM COMISTAT WHERE COMISTAT.CODCOM = '" . $PARMENTE_rec['ISTAT'] . "'"; 
        
        $comune_rec = ItaDB::DBSQLSelect($this->getSELECDB(), $sql, false);
        
        if ($comune_rec) {
            $idComistat = $comune_rec['ID'];
        }
        
        return $idComistat;
    }
    

    function cancellaOperatore($idOperatore) {
        
        if (!$this->cancellaSfElQual($idOperatore)) return false;

        if (!$this->cancellaTelefoni($idOperatore, 'O')) return false;

        
        try {
            $nrow = ItaDb::DBDelete($this->getSELECDB(), 'SFOPERATORI', 'ID', $idOperatore);
//            if ($nrow == 0) {
//                return false;
//            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }
        
        return true;
    }

    
    function cancellaSfElQual($idOperatore) {
        $sql = "SELECT * FROM SFELQUAL WHERE IDOPERATORI=$idOperatore ";
        $sfelqual_tab = ItaDB::DBSQLSelect($this->getSELECDB(), $sql, true);
        if ($sfelqual_tab){
            foreach ($sfelqual_tab as $sfelqual_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->getSELECDB(), 'SFELQUAL', 'ID', $sfelqual_rec['ID']);
//                    Non ha cancellato niente, può essere  
//                    if ($nrow == 0) {
//                        return false;
//                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                    return false;
                }
                
            }
        }
        return true;
        
    }

    function cancellaTelefoni($idOperatore, $tipoRic = 'O') {
        $sql = "SELECT * FROM TELEFONI WHERE IDRIF=$idOperatore AND TIPORIF = '" . $tipoRic. "'";
        $telefoni_tab = ItaDB::DBSQLSelect($this->getSELECDB(), $sql, true);
        if ($telefoni_tab){
            foreach ($telefoni_tab as $telefoni_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->getSELECDB(), 'TELEFONI', 'ID', $telefoni_rec['ID']);
//                    Non ha cancellato niente, può essere  
//                    if ($nrow == 0) {
//                        return false;
//                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                    return false;
                }
                
            }
        }
        return true;
        
    }

    
}

