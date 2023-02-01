<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  
 * @license
 * @version    07.01.2020
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proLibregistro {

    public $proLib;
    private $errCode;
    private $errMessage;

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

    /**
     * 
     * Legge il record di registro
     * 
     * @param type $codice
     * @param type $tipo 'rowid' cerca pwr il campo rowid, 'sogla cerva per il campo sigla
     * @param type $multi
     * @return type
     */
    public function GetAnaRegistriArc($codice, $tipo = 'rowid', $multi = false) {
        switch ($tipo) {
            case 'sigla':
                $sql = "SELECT * FROM ANAREGISTRIARC WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($codice) . "'";
                break;
            case'rowid':
            default :
                $sql = "SELECT * FROM ANAREGISTRIARC WHERE ROW_ID = $codice ";
                break;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /**
     * 
     * Legge il record progressivo registro  selezionato per row_id
     * 
     * @param int $row_id row_id del record della tabella PROT.ANAREGISTRIARC (REGISTRO)
     * @param int $anno anno di selezione del progressivo, se l'anno è null fornisce una tabella 
     *            di progressivi di tutto l'anno
     * @return mixed false in caso di errore, il valore fi registro corrente in caso si successo
     */
    public function GetAnaRegistriProg($row_id, $anno = null) {
        if ($anno === null) {
            $sql = "SELECT * FROM ANAREGISTRIPROG WHERE ROW_ID_ANAREGISTRO=$row_id";
            $multi = true;
        } else {
            $sql = "SELECT * FROM ANAREGISTRIPROG WHERE ROWID_ANAREGISTRO=$row_id AND ANNO=$anno";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /**
     * 
     * Inserisce un nuovo record di registro relativo all'anapro di riferimento
     * 
     * @param array $Datiregistro
     *              ['SIGLA'] =>    "Sigla registro"
     *              ['ANNO'] =>     "Anno Registro
     * @param array $anapro_rec     Record anapro
     * @return array()              Record proregistroarc   
     */
    public function insertRegistroAnapro($Datiregistro, $anapro_rec) {
        $anaregistriarc_rec = $this->GetAnaRegistriArc($Datiregistro['SIGLA'], 'sigla');
        if (!$anaregistriarc_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Errore Registro inesistente.");
            return false;
        }
        $row_id_registro = $anaregistriarc_rec['ROW_ID'];
        $retLock = $this->bloccaProgressivoRegistro($row_id_registro);
        if (!$retLock) {
            $this->errCode = -1;
            $this->setErrMessage($this->getErrMessage());
            return false;
        }
        $row_id_anapro = $anapro_rec['ROWID'];

        $annoRegistro = $Datiregistro['ANNO'];
        $progressivoRegistro = $this->leggiProgressivoRegistro($row_id_registro, $annoRegistro);
        if (!$progressivoRegistro) {
            $this->sbloccaProgressivoRegistro($retLock);
            $this->errCode = -1;
            $this->setErrMessage($this->getErrMessage());
            return false;
        }
        try {
            $proregistroarc_rec = array();
            $proregistroarc_rec['ROWID_REGISTRO'] = $row_id_registro;
            $proregistroarc_rec['ROWID_ANAPRO'] = $row_id_anapro;
            $proregistroarc_rec['ANNO'] = $annoRegistro;
            $proregistroarc_rec['PROGRESSIVO'] = $progressivoRegistro;
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROREGISTROARC', 'ROW_ID', $proregistroarc_rec);
        } catch (Exception $exc) {
            $this->sbloccaProgressivoRegistro($retLock);
            $this->errCode = -1;
            $this->setErrMessage("Assegnazione progressivo registro Fallita: " . $exc->getMessage());
            return false;
        }
        $Progressivo_new = $progressivoRegistro + 1;
        if (!$this->aggiornaProgressivoRegistro($row_id_registro, $annoRegistro, $Progressivo_new)) {
            $this->sbloccaProgressivoRegistro($retLock);
            $this->errCode = -1;
            $this->setErrMessage("Aggiornamento Progressivo Registro fallito. Errore Grave.");
            return false;
        }
        $this->sbloccaProgressivoRegistro($retLock);
        return $proregistroarc_rec;
    }

    public function getProRegistroArcFormAnapro($anapro_rec) {
        $sql = "SELECT * FROM PROREGISTROARC WHERE ROWID_ANAPRO = {$anapro_rec['ROWID']}";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    /**
     * Blocca Progressivo Registro
     * 
     * @param type $CodiceRegistro
     * @return boolean
     * 
     */
    public function bloccaProgressivoRegistro($CodiceRegistro) {
        if (!$CodiceRegistro) {
            $this->setErrMessage('Indicare Codice Registro e Anno.');
            return false;
        }
        $Registro_rec = $this->GetAnaRegistriArc($CodiceRegistro);
        if (!$Registro_rec) {
            $this->setErrMessage('Lettura dati registro fallita.*');
            return false;
        }
        $retLock = $this->lockRegistro($Registro_rec['ROW_ID']);
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    /**
     * Sblocca progressivo Registro
     * 
     * @param array $retLock
     * @return boolean
     * 
     */
    public function sbloccaProgressivoRegistro($retLock) {
        return $this->unlockRegistro($retLock);
    }

    /**
     * Legge e inizializza a "1", se necessario, l'ultimo codice libero per il progressivo registro.
     * Se Annuale legge da ANAREGISTRIPROG altrimenti da ANAREGISTRIARC
     * 
     * @param type $CodiceRegistro
     * @param type $Anno    sempre obbligatorio anche se il tipo di progressiovo è assoluto o manuale 
     * @return boolean|int
     * 
     */
    public function leggiProgressivoRegistro($CodiceRegistro, $Anno) {
        if (!$CodiceRegistro || !$Anno) {
            $this->setErrMessage('Indicare Codice Registro e Anno.');
            return false;
        }

        $AnaRegistroArc_rec = $this->GetAnaRegistriArc($CodiceRegistro, 'codice');
        if (!$AnaRegistroArc_rec) {
            $this->setErrMessage('Lettura dati registro fallita.');
            return false;
        }

        if ($AnaRegistroArc_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            $this->setErrMessage('Progressivo non prenotablie. Inserimento Manuale.');
            return false;
        }

        switch ($AnaRegistroArc_rec['TIPOPROGRESSIVO']) {
            case 'ANNUALE':
                /*
                 * Controllo se inizializzo anno
                 */
                $Anaregistriprog_rec = $this->GetAnaRegistriProg($CodiceRegistro, $Anno);
                if (!$Anaregistriprog_rec) {
                    try {
                        $Anaregistriprog_rec = array();
                        $Anaregistriprog_rec['ROWID_ANAREGISTRO'] = $CodiceRegistro;
                        $Anaregistriprog_rec['ANNO'] = $Anno;
                        $Anaregistriprog_rec['PROGRESSIVO'] = 1;
                        ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAREGISTRIPROG', 'ROW_ID', $Anaregistriprog_rec);
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Inizializzazione ANAREGISTRIPROG " . $exc->getMessage());
                        return false;
                    }
                    $Anaregistriprog_rec = $this->GetAnaRegistriProg($CodiceRegistro, $Anno);
                    if (!$Anaregistriprog_rec) {
                        $this->setErrMessage('Lettura dati registro annuale fallita.*');
                        return false;
                    }
                }
                $progressivo = $Anaregistriprog_rec['PROGRESSIVO'];
                break;
            case 'ASSOLUTO':
                $progressivo = $AnaRegistroArc_rec['PROGRESSIVO'];
                if (!$AnaRegistroArc_rec['PROGRESSIVO']) {
                    $progressivo = 1;
                }
                break;
            case 'MANUALE':
                $progressivo = false;
                break;
        }
        return $progressivo;
    }

    /**
     * Aggiorna il record dei progressivi registro 
     * se annunale aggiorna la Tabella ANAREGISTRIPROG altrimenti la TABELLA ANAREGISTRIARC
     * 
     * @param type $CodiceRegistro
     * @param type $Anno sempre obbligatorio anche se il tipo di progressiovo è assoluto o manuale
     * @return boolean
     * 
     */
    function aggiornaProgressivoRegistro($CodiceRegistro, $Anno, $Progressivo) {
        if (!$CodiceRegistro || !$Anno) {
            $this->setErrMessage('Indicare Codice Registro e Anno.');
            return false;
        }
        $AnaRegistroArc_rec = $this->GetAnaRegistriArc($CodiceRegistro, 'codice');
        if (!$AnaRegistroArc_rec) {
            $this->setErrMessage('Lettura dati registro fallita.');
            return false;
        }

        if ($AnaRegistroArc_rec['TIPOPROGRESSIVO'] == 'MANUALE') {
            $this->setErrMessage('Progressivo non prenotablie. Inserimento Manuale.');
            return false;
        }
        switch ($AnaRegistroArc_rec['TIPOPROGRESSIVO']) {
            case 'ANNUALE':
                $Anaregistriprog_rec = $this->GetAnaRegistriProg($CodiceRegistro, $Anno);
                if (!$Anaregistriprog_rec) {
                    $this->setErrMessage('Lettura dati registro annuale fallita.*');
                    return false;
                }
                $Anaregistriprog_rec['PROGRESSIVO'] = $Progressivo;
                $Anaregistriprog_rec['DATAPROGRESSIVO'] = date("Ymd");
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAREGISTRIPROG', 'ROW_ID', $Anaregistriprog_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento ANAREGISTRIPROG " . $exc->getMessage());
                    return false;
                }
                break;
            case 'ASSOLUTO':
                $AnaRegistroArc_rec['PROGRESSIVO'] = $Progressivo;
                try {
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAREGISTRIARC', 'ROW_ID', $AnaRegistroArc_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore aggiornamento ANAREGISTRIARC " . $exc->getMessage());
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * 
     * @param type $rowid
     * @return boolean
     */
    public function lockRegistro($rowid) {
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAREGISTRIARC", $rowid, "", 120);
        if ($retLock['status'] != 0) {
            $this->setErrMessage('Blocco Tabella PROGRESSIVI non Riuscito per ANAREGISTRIARC.');
            return false;
        }
        return $retLock;
    }

    /**
     * 
     * @param type $retLock
     * @return boolean
     */
    public function unlockRegistro($retLock = '') {
        if (!$retLock) {
            $this->setErrMessage($this->getErrMessage() . ' - Sblocco Tabella Riferimento al lock mancante.');
            return false;
        }
        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            $this->setErrMessage($this->getErrMessage() . ' - Sblocco Tabella PROGRESSIVI non Riuscito per ANAREGISTRIARC.');
            return false;
        }
        return true;
    }

}
