<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    18.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proLibDeleghe {

    const DELEFUNZIONE_PROTOCOLLO = 0;
    const DELEFUNZIONE_ATTI = 1;

    public $proLib;
    public $PROT_DB;
    private $errCode;
    private $errMessage;
    private $DelegatoFirmatario;

    function __construct() {
        $this->proLib = new proLib();
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

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getDelegatoFirmatario() {
        return $this->DelegatoFirmatario;
    }

    public function setDelegatoFirmatario($DelegatoFirmatario) {
        $this->DelegatoFirmatario = $DelegatoFirmatario;
    }

    /**
     * 
     * @param type $DestCod
     * @param type $UffCod
     * @param type $Data
     * @return boolean
     */
    public function CheckSostitutoDelega($DestCod, $UffCod, $Data, $delefunzione = 0, $multi = false) {
        $sql = "SELECT * FROM DELEGHEITER WHERE DELESRCCOD = '" . $DestCod . "' AND DELESRCUFF = '" . $UffCod . "' ";
        $sql .= " AND $Data BETWEEN DELEINIVAL AND DELEFINVAL AND DELEDATEANN = '' AND DELEFUNZIONE = $delefunzione ";
        // Indipendentemente dalla data di annullamento, se è annullato non è piu da considerare.
        //$sql.=" AND DELEINIVAL >= $Data AND DELEFINVAL >= $Data ";
        $Deleghiter_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
        if ($Deleghiter_rec) {
            return $Deleghiter_rec;
        }
        return false;
    }

    /**
     * 
     * @param type $DestCod
     * @param type $UffCod
     * @param type $Data
     * @return boolean
     */
    public function CheckUtenteDelegato($DestCod, $UffCod, $Data, $delefunzione = 0) {
        $sql = "SELECT * FROM DELEGHEITER WHERE DELEDSTCOD = '" . $DestCod . "' AND DELEDSTUFF = '" . $UffCod . "' ";
        $sql .= " AND $Data BETWEEN DELEINIVAL AND DELEFINVAL AND DELEDATEANN = ''  AND DELEFUNZIONE = $delefunzione  ";
        $Deleghiter_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        if ($Deleghiter_rec) {
            return $Deleghiter_rec;
        }
        return false;
    }

    /**
     * Funzione di Controllo per Utente/Ufficio attiva una delega.
     * @param type $CodiceDes
     * @param type $CodUfficio
     */
    public function CheckDelegaUtenteUfficio($CodiceDes = '', $CodUfficio = '', $delefunzione = 0) {
        if (!$CodiceDes) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice utente mancante. Non è possibile verificare la presenza di una delega.");
            return false;
        }
        if (!$CodUfficio) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice ufficio mancante. Non è possibile verificare la presenza di una delega.");
            return false;
        }
        $this->DelegatoFirmatario = array();
        $DataFir = date('Ymd');
        $Delegato_rec = $this->CheckSostitutoDelega($CodiceDes, $CodUfficio, $DataFir, $delefunzione);
        if ($Delegato_rec) {
            $this->DelegatoFirmatario['CODICE'] = $Delegato_rec['DELEDSTCOD'];
            $this->DelegatoFirmatario['UFFICIO'] = $Delegato_rec['DELEDSTUFF'];
            $this->DelegatoFirmatario['DELEGANTE'] = $CodiceDes;
            $this->DelegatoFirmatario['UFFDELEGANTE'] = $CodUfficio;
        }
        return $this->DelegatoFirmatario;
    }

    public function CheckOpenFormFirmatarioSostituto($CodiceDes = '', $CodUfficio = '', $nameform, $delefunzione = 0) {
        if (!$CodiceDes || !$CodUfficio) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice utente o ufficio mancanti.");
            return false;
        }
        if (!$this->CheckDelegaUtenteUfficio($CodiceDes, $CodUfficio, $delefunzione)) {
            return false;
        }
        /*
         * Nessun Delegato Presente.
         */
        if (!$this->DelegatoFirmatario) {
            return true;
        }
        /*
         * Preparo Messaggio Firmatario
         */
        $AnamedFir = $this->proLib->GetAnamed($CodiceDes, 'codice');
        $Anamed_rec = $this->proLib->GetAnamed($this->DelegatoFirmatario['CODICE'], 'codice');
        $this->DelegatoFirmatario['NOMINATIVO'] = $Anamed_rec['MEDNOM'];

        $messaggio = "<div><b><u>FIRMATARIO DEL PROTOCOLLO:</u></b><br><br>";
        $messaggio .= "É presente una Delega per <b>" . $AnamedFir['MEDNOM'] . "</b>";
        $messaggio .= "<br>ed stato indicato <b>{$Anamed_rec['MEDNOM']} </b>come <u>Sostituto/Delegato</u>.<br>";
        $messaggio .= "Il firmatario verrà quindi sostituito. Vuoi confermare l'operazione?</div>";

        Out::msgQuestion("Attenzione", $messaggio, array(
            'No' => array('id' => $nameform . '_MantieniFirmatario',
                'model' => $nameform),
            'F5 - Conferma' => array('id' => $nameform . '_ConfermaCambioFirm',
                'model' => $nameform, 'shortCut' => "f5")
                )
        );

        return true;
    }

    public function getDelegheAttive($DestCod, $extraParam = null, $delefunzione = 0) {
        $Data = date('Ymd');
        $whereDelegaScrivania = '';
        if (is_array($extraParam)) {
            if (array_key_exists("DELESCRIVANIA", $extraParam)) {
                $whereDelegaScrivania = "AND DELESCRIVANIA={$extraParam['DELESCRIVANIA']}";
            }
            if (array_key_exists("DELESCRUFF", $extraParam)) {
                $whereDelegaScrivania = "AND DELESCRUFF={$extraParam['DELESCRUFF']}";
            }
            if (array_key_exists("DELESCRCOD", $extraParam)) {
                $whereDelegaScrivania = "AND DELESCRCOD={$extraParam['DELESCRCOD']}";
            }
        }

        $sql = "  
                SELECT
                    * 
                FROM
                    DELEGHEITER
                WHERE
                    DELEDSTCOD = '$DestCod ' AND 
                    '$Data' BETWEEN DELEINIVAL AND DELEFINVAL AND
                    DELEDATEANN = '' AND DELEFUNZIONE = $delefunzione
                    $whereDelegaScrivania";
        $Deleghiter_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        return $Deleghiter_tab;
    }

}
