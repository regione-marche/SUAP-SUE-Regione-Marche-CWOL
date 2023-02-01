<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    20.06.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */

class praRestAgent {

    public $PRAM_DB;
    public $praLib;
    public $errCode;
    public $errMessage;

    function __construct() {
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        
    }

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

    function GetPraticaDatiPerProcedura($NumeroProcedura) {
        $sql = "SELECT
                    PROGES.GESNUM,
                    PROGES.GESPRA,
                    PROGES.GESPRO,
                    ANAPRA.PRADES__1 AS PRADES,                    
                    PROGES.GESRES,
                    ".$this->PRAM_DB->strConcat('NOMCOG' , "' '" , 'NOMNOM')." AS NOMERES,
                    PROGES.GESDRE,
                    PROGES.GESDRI,
                    PROGES.GESORA,
                    PROGES.GESDCH,
                    PROGES.GESNPR,
                    PROGES.GESNRC,
                    PROGES.GESTSP,
                    PROGES.GESSPA,
                    PROGES.GESNOT,
                    PROGES.GESDATAREG,
                    PROGES.GESORAREG,
                    PROGES.GESCODPROC
                FROM
                    PROGES PROGES
                LEFT OUTER JOIN
                    ANAPRA ANAPRA ON PROGES.GESPRO = ANAPRA.PRANUM                    
                LEFT OUTER JOIN
                    ANANOM ANANOM ON PROGES.GESRES = ANANOM.NOMRES                    
                WHERE
                    PROGES.GESCODPROC='$NumeroProcedura'";
        //CONCAT(NOMCOG , ' ' , NOMNOM) AS NOMERES,
        try {
            $Proges_tab = itaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        if ($Proges_tab) {
            foreach ($Proges_tab as $Proges_rec) {
                $result = array(
                    'GESCODPROC' => $Proges_rec['GESCODPROC'],
                    'GESNUM' => $Proges_rec['GESNUM'],
                    'GESPRA' => $Proges_rec['GESPRA'],
                    'GESPRO' => $Proges_rec['GESPRO'],
                    'PRADES' => ereg_replace("[^A-Za-z0-9 ]", " ", $Proges_rec['PRADES']),
                    'GESRES' => $Proges_rec['GESRES'],
                    'NOMERES' => $Proges_rec['NOMERES'],
                    'GESDRE' => $Proges_rec['GESDRE'],
                    'GESDRI' => $Proges_rec['GESDRI'],
                    'GESORA' => $Proges_rec['GESORA'],
                    'GESDCH' => $Proges_rec['GESDCH'],
                    'GESNPR' => $Proges_rec['GESNPR'],
                    'GESNRC' => $Proges_rec['GESNRC'],
                    'GESTSP' => $Proges_rec['GESTSP'],
                    'GESSPA' => $Proges_rec['GESSPA'],
                    'GESNOT' => $Proges_rec['GESNOT']
                );
                $returnArray[] = $result;
            }
        }
        return $returnArray;
    }

}

?>