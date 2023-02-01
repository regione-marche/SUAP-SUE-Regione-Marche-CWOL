<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    25.07.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPQws/itaPHPQwsClient.class.php');

class praCtrDatiCatastaliItalsoft {

    private $CATA_DB;

    /**
     * 
     * @param type $elementi 
     * @return type
     */
    function getCatasto($elementi) {
        $ritorno = array();
        //
        $this->CATA_DB = ItaDB::DBOpen('CATA', frontOfficeApp::getEnte());
        //
        $msg = "";
        $ret = array();
        foreach ($elementi['raccolta'] as $key => $raccolta) {
            $sql = $this->CreaSql($raccolta);
            $immobili_tab = ItaDB::DBSQLSelect($this->CATA_DB, $sql, true);
            if (count($immobili_tab) > 0) {
                $ret[$key]["Status"] = "1";
                $ret[$key]["Message"] = "I dati catastali sono stati validati.";
            } else {
                $ret[$key]["Status"] = "2";
                $ret[$key]["Message"] = "Attenzione i dati catastali non sono stati validati. Verificare.";
                $msg .= "Attenzione i dati catastali della maschera $key non sono stati validati. Verificare.<br>";
            }
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $msg;
        $ritorno["RetValue"] = $ret;
        return $ritorno;
    }

    private function CreaSql($raccolta) {
        $sql = "SELECT * FROM LEGAME WHERE 1 = 1 ";

        if ($raccolta['IMM_TIPO']) {
            $sql .= " AND TIPOIMMOBILE = '" . $raccolta['IMM_TIPO'] . "'";
        }
        if ($raccolta['IMM_FOGLIO']) {
            $foglio = str_pad($raccolta['IMM_FOGLIO'], 4, "0", STR_PAD_LEFT);
            $sql .= " AND FOGLIO = '$foglio'";
        }
        if ($raccolta['IMM_PARTICELLA']) {
            $particella = str_pad($raccolta['IMM_PARTICELLA'], 5, "0", STR_PAD_LEFT);
            $sql .= " AND NUMERO = '$particella'";
        }
        if ($raccolta['IMM_SUBALTERNO']) {
            $sub = str_pad($raccolta['IMM_SUBALTERNO'], 4, "0", STR_PAD_LEFT);
            $sql .= " AND SUB = '$sub'";
        }

        return $sql;
    }

}
