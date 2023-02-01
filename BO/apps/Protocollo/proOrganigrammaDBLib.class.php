<?php

/**
 *
 * LIBRERIA PER APPLICATIVO BDAP
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Protocollo
 * @author     Andimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    30.07.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLibOrganigramma.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSoggetti.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/eqAudit.class.php';

class proOrganigrammaDBLib {

    public $PROT_DB;
    public $ITW_DB;
    public $proLib;
    public $proLibOrganigramma;
    public $proLibSoggetti;
    public $eqAudit;
    public $errCode;
    public $errMessage;

    function __construct() {
        try {
            $this->eqAudit = new eqAudit();
            $this->proLib = new proLib();
            $this->proLibOrganigramma = new proLibOrganigramma();
            $this->proLibSoggetti = new proLibSoggetti();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            
        }
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
     * Validazione per inserimento/aggiornamento ANAUFF
     * 
     * @param type $Anauff_rec
     * @return boolean
     */
    public function validateUfficio($Anauff_rec, $operation) {
        switch ($operation) {
            case 'INSERT':
                if (!$Anauff_rec['UFFCOD']) {
                    $Anauff_rec['UFFCOD'] = $this->proLibOrganigramma->getProgANAUFF();
                    if ($Anauff_rec['UFFCOD'] === false) {
                        $this->setErrMessage('Codice Ufficio non assegnato automaticamente');
                        return array(
                            'esito' => false
                        );
                    }
                } else {
                    $Anauff_rec['UFFCOD'] = $this->allineaCodiceUfficio($Anauff_rec['UFFCOD']);
                }
                if (!$Anauff_rec['UFFDES']) {
                    $this->setErrMessage('Indicare Descrizione Ufficio');
                    return array(
                        'esito' => false
                    );
                }
                if (!$this->ControlloDatiUfficio($Anauff_rec)) {
                    return array(
                        'esito' => false
                    );
                }
                if ($this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice')) {
                    $this->setErrMessage('Codice Ufficio esistente');
                    return array(
                        'esito' => false
                    );
                }
                break;
            case 'UPDATE':
                if (!$Anauff_rec['UFFCOD']) {
                    $this->setErrMessage('Indicare Codice Ufficio');
                    return array(
                        'esito' => false
                    );
                }
                if (!$Anauff_rec['UFFDES']) {
                    $this->setErrMessage('Indicare Descrizione Ufficio');
                    return array(
                        'esito' => false
                    );
                }
                if (!$this->ControlloDatiUfficio($Anauff_rec)) {
                    return array(
                        'esito' => false
                    );
                }
                if (!$this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice')) {
                    $this->setErrMessage('Codice Ufficio non trovato');
                    return array(
                        'esito' => false
                    );
                }
                break;
            case 'DELETE':
                if (!$Anauff_rec['UFFCOD']) {
                    $this->setErrMessage('Indicare Codice Ufficio');
                    return array(
                        'esito' => false
                    );
                }
                $result = $this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice');
                if (!$result) {
                    $this->setErrMessage('Codice Ufficio non trovato');
                    return array(
                        'esito' => false
                    );
                }
                $esito = $this->ControllaCancellaUfficio($result);
                if ($esito !== true) {
                    $this->setErrMessage('Ufficio in uso. Non è possibile procedere con la cancellazione.');
                    return array(
                        'esito' => false
                    );
                }

                break;
        }

        if ($Anauff_rec['CODICE_PADRE']) {
            $Anauff_rec['CODICE_PADRE'] = str_repeat("0", 4 - strlen(trim($Anauff_rec['CODICE_PADRE']))) . trim($Anauff_rec['CODICE_PADRE']);
        }

        return array(
            'esito' => true,
            'dati' => $Anauff_rec
        );
    }

    /**
     * 
     * @param array $Anauff_rec
     * @param string $InsertInfo
     * @return boolean
     */
    public function insertUfficio($Anauff_rec, $InsertInfo = '') {
        $validazione = $this->validateUfficio($Anauff_rec, 'INSERT');
        if ($validazione['esito'] === false) {
            return false;
        }
        $Anauff_rec = $validazione['dati'];

        /*
         * insert ANAUFF
         */
        if (!$InsertInfo) {
            $InsertInfo = "Inserimento nuovo record su ANAUFF - codice " . $Anauff_rec['UFFCOD'];
        }
        try {
            ItaDB::DBInsert($this->PROT_DB, 'ANAUFF', 'ROWID', $Anauff_rec);
            $LastID = ItaDB::DBLastId($this->PROT_DB);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_INS_RECORD,
                'DB' => $this->PROT_DB->getDB(),
                'DSet' => 'ANAUFF',
                'Estremi' => $InsertInfo
            ));
            return $LastID;
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Inserimento su ANAGRAFICA UFFICI. " . $e->getMessage());
            return false;
        }
    }

    public function updateUfficio($Anauff_rec, $UpdateInfo = '') {
        $validazione = $this->validateUfficio($Anauff_rec, 'UPDATE');
        if ($validazione['esito'] === false) {
            return false;
        }
        $Anauff_rec = $validazione['dati'];
        $result = $this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice');
        //
        foreach ($Anauff_rec as $key => $value) {
            $result[$key] = $value;
        }
        /*
         * update ANAUFF
         */
        if (!$UpdateInfo) {
            $UpdateInfo = "Aggiornamento record su ANAUFF - codice " . $Anauff_rec['UFFCOD'];
        }
        try {
            ItaDB::DBUpdate($this->PROT_DB, 'ANAUFF', 'ROWID', $result);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $this->PROT_DB->getDB(),
                'DSet' => 'ANAUFF',
                'Estremi' => $UpdateInfo
            ));
            return true;
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Aggiornamento su ANAGRAFICA UFFICI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function deleteUfficio($Anauff_rec, $DeleteInfo = '') {
        $validazione = $this->validateUfficio($Anauff_rec, 'DELETE');
        if ($validazione['esito'] === false) {
            return false;
        }
        $Anauff_rec = $validazione['dati'];
        $result = $this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice');
        /*
         * delete ANAUFF
         */
        $sqlufftit = "SELECT * FROM UFFTIT WHERE UFFCOD='" . $Anauff_rec['UFFCOD'] . "'";
        $ufftit_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlufftit);
        foreach ($ufftit_tab as $ufftit_rec) {
            try {
                ItaDB::DBDelete($this->PROT_DB, 'UFFTIT', 'ROWID', $ufftit_rec['ROWID']);
            } catch (Exception $e) {
                $this->setErrMessage("Errore di Cancellazione su tabella UFFTIT. " . $e->getMessage());
                return false;
            }
        }
        if (!$DeleteInfo) {
            $DeleteInfo = "Cancellazione record su ANAUFF - codice " . $Anauff_rec['UFFCOD'];
        }
        try {
            ItaDB::DBDelete($this->PROT_DB, 'ANAUFF', 'ROWID', $result['ROWID']);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_DEL_RECORD,
                'DB' => $this->PROT_DB->getDB(),
                'DSet' => 'ANAUFF',
                'Estremi' => $DeleteInfo
            ));
            return true;
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Cancellazione su ANAGRAFICA UFFICI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getUfficio($Anauff_rec) {
        if (!$Anauff_rec['UFFCOD']) {
            $this->setErrMessage('Indicare Codice Ufficio');
            return false;
        }
        $Anauff_rec['UFFCOD'] = $this->allineaCodiceUfficio($Anauff_rec['UFFCOD']);
        $anauff = $this->proLib->GetAnauff($Anauff_rec['UFFCOD'], 'codice');
        if (!$anauff) {
            $this->setErrMessage('Codice Ufficio non trovato');
            return false;
        }
        $nodo = $this->elaboraUfficio($anauff);
        //
        $xmlObj = new itaXML();
        $xmlObj->noCDATA();
        $xmlObj->toXML($nodo);
        $retXml = $xmlObj->getXml();
        //
        $xmlB64 = base64_encode($retXml);

        return $xmlB64;
    }

    public function getUffici() {
        $sql = "SELECT * FROM ANAUFF";
        $anauffTab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, 'true');
        if ($anauffTab) {
            foreach ($anauffTab as $anauff) {
                $nodo = $this->elaboraUfficio($anauff);
                $arrayUffici['protocollo'][0]['ufficio'][] = $nodo;
            }
            //
            $xmlObj = new itaXML();
            $xmlObj->noCDATA();
            $xmlObj->toXML($arrayUffici);
            $retXml = $xmlObj->getXml();
            //
            $xmlB64 = base64_encode($retXml);

            return $xmlB64;
        }
        $this->setErrMessage('Non ci sono uffici da Elencare.');
        return '';
    }

    public function getSoggetto($Anamed_rec) {
        if (!$Anamed_rec['MEDCOD']) {
            $this->setErrMessage('Indicare Codice Soggetto');
            return false;
        }
        $Anamed_rec['MEDCOD'] = $this->allineaCodiceSoggetto($Anamed_rec['MEDCOD']);
        $anamed = $this->proLib->GetAnamed($Anamed_rec['MEDCOD'], 'codice');
        if (!$anamed) {
            $this->setErrMessage('Codice Soggetto non trovato');
            return false;
        }
        $nodo = $this->elaboraSoggetto($anamed);
        //
        $xmlObj = new itaXML();
        $xmlObj->noCDATA();
        $xmlObj->toXML($nodo);
        $retXml = $xmlObj->getXml();
        //
        $xmlB64 = base64_encode($retXml);

        return $xmlB64;
    }

    public function getSoggetti() {
        $sql = "SELECT * FROM ANAMED WHERE MEDUFF " . $this->PROT_DB->isNotBlank() . " AND MEDANN = 0";
        $anamedTab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, 'true');
        if ($anamedTab) {
            foreach ($anamedTab as $anamed) {
                $nodo = $this->elaboraSoggetto($anamed);
                $arraySoggetti['protocollo'][0]['soggetti'][] = $nodo;
            }
            //
            $xmlObj = new itaXML();
            $xmlObj->noCDATA();
            $xmlObj->toXML($arraySoggetti);
            $retXml = $xmlObj->getXml();
            //
            $xmlB64 = base64_encode($retXml);

            return $xmlB64;
        }
        $this->setErrMessage('Non ci sono soggetti da Elencare.');
        return '';
    }

    /**
     * Validazione per inserimento/aggiornamento ANAMED
     * 
     * @param type $Anamed_rec
     * @return type
     */
    public function validateSoggetto($Anamed_rec) {
        if (strlen(trim($Anamed_rec['MEDFIS'])) && (strlen(trim($Anamed_rec['MEDFIS'])) != 11 && strlen(trim($Anamed_rec['MEDFIS'])) != 16)) {
            $this->setErrMessage('Inserite una Partita Iva o un Codice Fiscale valido.');
            return array(
                'esito' => false
            );
        }
        if (!$Anamed_rec['MEDNOM']) {
            $this->setErrMessage('Indicare Denominazioen soggetto.');
            return array(
                'esito' => false
            );
        }
        if (!$Anamed_rec['MEDCOD']) {
            $Anamed_rec['MEDCOD'] = $this->proLibSoggetti->getProgANAMED();
            if ($Anamed_rec['MEDCOD'] === false) {
                $this->setErrMessage('Codice Soggetto non assegnato automaticamente');
                return array(
                    'esito' => false
                );
            }
        } else {
            $Anamed_rec['MEDCOD'] = $this->allineaCodiceSoggetto($Anamed_rec['MEDCOD']);
        }
        return array(
            'esito' => true,
            'dati' => $Anamed_rec
        );
    }

    public function insertSoggetto($Anamed_rec, $InsertInfo = '') {
        $validazione = $this->validateSoggetto($Anamed_rec);
        if ($validazione['esito'] === false) {
            return false;
        }
        $Anamed_rec = $validazione['dati'];

        $result = $this->proLib->GetAnamed($Anamed_rec['MEDCOD'], 'codice');
        if ($result) {
            $this->setErrMessage('Codice Soggetto esistente');
            return false;
        }
        /*
         * insert ANAMED
         */
        if (!$InsertInfo) {
            $InsertInfo = "Inserimento nuovo record su ANAMED - codice " . $Anamed_rec['MEDCOD'];
        }
        try {
            ItaDB::DBInsert($this->PROT_DB, 'ANAMED', 'ROWID', $Anamed_rec);
            $LastID = ItaDB::DBLastId($this->PROT_DB);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_INS_RECORD,
                'DB' => $this->PROT_DB->getDB(),
                'DSet' => 'ANAMED',
                'Estremi' => $InsertInfo
            ));
            return $LastID;
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Inserimento su ANAGRAFICA SOGGETTI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function updateSoggetto($Anamed_rec, $UpdateInfo = '') {
        $validazione = $this->validateSoggetto($Anamed_rec);
        if ($validazione['esito'] === false) {
            return false;
        }
        $Anamed_rec = $validazione['dati'];

        $result = $this->proLib->GetAnamed($Anamed_rec['MEDCOD'], 'codice');
        if (!$result) {
            $this->setErrMessage('Codice Soggetto non trovato');
            return false;
        }
        //
        foreach ($Anamed_rec as $key => $value) {
            $result[$key] = $value;
        }
        /*
         * update ANAMED
         */
        if (!$UpdateInfo) {
            $UpdateInfo = "Aggiornamento record su ANAMED - codice " . $Anamed_rec['MEDCOD'];
        }
        try {
            ItaDB::DBUpdate($this->PROT_DB, 'ANAMED', 'ROWID', $result);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $this->PROT_DB->getDB(),
                'DSet' => 'ANAMED',
                'Estremi' => $UpdateInfo
            ));
            return true;
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Aggiornamento su ANAGRAFICA SOGGETTI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function deleteSoggetto($Anamed_rec) {
        if (!$Anamed_rec['MEDCOD']) {
            $this->setErrMessage('Indicare Codice Soggetto');
            return false;
        }
        $Anamed_rec['MEDCOD'] = $this->allineaCodiceSoggetto($Anamed_rec['MEDCOD']);
        $result = $this->proLib->GetAnamed($Anamed_rec['MEDCOD'], 'codice');
        if (!$result) {
            $this->setErrMessage('Codice Soggetto non trovato');
            return false;
        }
        $esito = $this->ControllaCancellaSoggetto($result);
        if ($esito !== true) {
            $this->setErrMessage('Soggetto usato. Non è possibile procedere con la cancellazione.');
            return false;
        }
        $sql = "SELECT UFFDES.ROWID AS ROWID FROM UFFDES UFFDES LEFT OUTER JOIN ANAUFF ANAUFF
                                ON UFFDES.UFFCOD = ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='" . $Anamed_rec['MEDCOD'] . "'";
        $uffici_tab = $this->proLib->getGenericTab($sql);
        foreach ($uffici_tab as $uffici_rec) {
            try {
                ItaDB::DBDelete($this->PROT_DB, 'UFFDES', 'ROWID', $uffici_rec['ROWID']);
            } catch (Exception $e) {
                $this->setErrMessage("Errore di Cancellazione su tabella UFFDES. " . $e->getMessage());
                return false;
            }
        }
        try {
            ItaDB::DBDelete($this->PROT_DB, 'ANAMED', 'ROWID', $result['ROWID']);
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Cancellazione su ANAGRAFICA SOGGETTI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function associaSoggetto2Uffici($dati) {
        $soggetto = $dati['SOGGETTO'];
        $uffici = $dati['UFFICI'];
        if (!$soggetto) {
            $this->setErrMessage('Indicare Codice Soggetto');
            return false;
        }
        $soggetto = $this->allineaCodiceSoggetto($soggetto);
        $anamed = $this->proLib->GetAnamed($soggetto, 'codice');
        if (!$anamed) {
            $this->setErrMessage('Codice Soggetto non trovato');
            return false;
        }
        if ($uffici) {
            $codiciUffici = $uffici['uffici']['codiceUfficio'];
            $codiciRuolo = $uffici['uffici']['codiceRuolo'];
        }
        $esitoCancellazione = $this->cancellaUfficiAssociatiAlSoggetto($anamed['MEDCOD']);
        if (!$esitoCancellazione) {
            return false;
        }
        if ($codiciUffici) {
            foreach ($codiciUffici as $key => $ufficio) {
                $ufficio = $this->allineaCodiceUfficio($ufficio);
                $anauff = $this->proLib->GetAnauff($ufficio, 'codice');
                if (!$anauff) {
                    $this->setErrMessage('Codice Ufficio ' . $ufficio . ' non trovato');
                    return false;
                }
                if ($codiciRuolo[$key]) {
                    $anaruolo = $this->proLib->getAnaruoli($codiciRuolo[$key], 'codice');
                    if (!$anaruolo) {
                        $this->setErrMessage('Codice Ruolo ' . $codiciRuolo[$key] . ' associato all\'ufficio ' . $ufficio . ' non trovato');
                        return false;
                    }
                }
                $uffdes_new = array(
                    'UFFKEY' => $anamed['MEDCOD'],
                    'UFFCOD' => $ufficio,
                    'UFFFI1__2' => $codiciRuolo[$key]
                );
                try {
                    ItaDB::DBInsert($this->PROT_DB, 'UFFDES', 'ROWID', $uffdes_new);
                } catch (Exception $e) {
                    $this->setErrMessage("Errore di Inserimento su ANAGRAFICA UFFICI. " . $e->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function insertRuolo($Anaruoli_rec) {
        if (!$Anaruoli_rec['RUOCOD']) {
            $this->setErrMessage('Indicare Codice Ruolo');
            return false;
        }
        if (!$Anaruoli_rec['RUODES']) {
            $this->setErrMessage('Indicare Descrizione Ruolo');
            return false;
        }
        $result = $this->proLib->getAnaruoli($Anaruoli_rec['RUOCOD'], 'codice');
        if ($result) {
            $this->setErrMessage('Codice Ruolo esistente');
            return false;
        }
        /*
         * insert ANARUOLI
         */
        try {
            ItaDB::DBInsert($this->PROT_DB, 'ANARUOLI', 'ROWID', $Anaruoli_rec);
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Inserimento su ANAGRAFICA ANARUOLI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function updateRuolo($Anaruoli_rec) {
        if (!$Anaruoli_rec['RUOCOD']) {
            $this->setErrMessage('Indicare Codice Ruolo');
            return false;
        }
        $result = $this->proLib->getAnaruoli($Anaruoli_rec['RUOCOD'], 'codice');
        if (!$result) {
            $this->setErrMessage('Codice Ruolo non trovato');
            return false;
        }
        //
        $result['RUODES'] = $Anaruoli_rec['RUODES'];
        /*
         * update ANARUOLI
         */
        try {
            ItaDB::DBUpdate($this->PROT_DB, 'ANARUOLI', 'ROWID', $result);
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Aggiornamento su ANAGRAFICA ANARUOLI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function deleteRuolo($Anaruoli_rec) {
        if (!$Anaruoli_rec['RUOCOD']) {
            $this->setErrMessage('Indicare Codice Ruolo');
            return false;
        }
        $result = $this->proLib->getAnaruoli($Anaruoli_rec['RUOCOD'], 'codice');
        if (!$result) {
            $this->setErrMessage('Codice Ruolo non trovato');
            return false;
        }
        $esito = $this->ControllaCancellaRuoli($result);
        if ($esito !== true) {
            $this->setErrMessage('Ruolo in uso. Non è possibile procedere con la cancellazione.');
            return false;
        }
        /*
         * delete ANARUOLO
         */
        try {
            ItaDB::DBDelete($this->PROT_DB, 'ANARUOLI', 'ROWID', $result['ROWID']);
        } catch (Exception $e) {
            $this->setErrMessage("Errore di Cancellazione su ANAGRAFICA ANARUOLI. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getRuolo($Anaruoli_rec) {
        if (!$Anaruoli_rec['RUOCOD']) {
            $this->setErrMessage('Indicare Codice Ruolo');
            return false;
        }
        $anaruoli = $this->proLib->getAnaruoli($Anaruoli_rec['RUOCOD'], 'codice');
        if (!$anaruoli) {
            $this->setErrMessage('Codice Ruolo non trovato');
            return false;
        }
        $nodo = $this->elaboraRuolo($anaruoli);
        //
        $xmlObj = new itaXML();
        $xmlObj->noCDATA();
        $xmlObj->toXML($nodo);
        $retXml = $xmlObj->getXml();
        //
        $xmlB64 = base64_encode($retXml);

        return $xmlB64;
    }

    public function getRuoli() {
        $sql = "SELECT * FROM ANARUOLI";
        $anaruoliTab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, 'true');
        if ($anaruoliTab) {
            foreach ($anaruoliTab as $anaruolo) {
                $nodo = $this->elaboraRuolo($anaruolo);
                $arrayRuoli['protocollo'][0]['ruolo'][] = $nodo;
            }
            //
            $xmlObj = new itaXML();
            $xmlObj->noCDATA();
            $xmlObj->toXML($arrayRuoli);
            $retXml = $xmlObj->getXml();
            //
            $xmlB64 = base64_encode($retXml);

            return $xmlB64;
        }
        $this->setErrMessage('Non ci sono ruoli da Elencare.');
        return '';
    }

    /*
     * function di servizio
     */

    public function allineaCodiceUfficio($codice) {
        if (is_numeric($codice)) {
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
        } else {
            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
        }
        return $codice;
    }

    public function allineaCodiceSoggetto($codice) {
        if (is_numeric($codice)) {
            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
        }
        return $codice;
    }

    public function ControlloDatiUfficio($Anauff_rec) {
        if ($Anauff_rec['UFFRES']) {
            $sql = "SELECT * FROM UFFDES 
                        WHERE UFFCOD = '" . $Anauff_rec['UFFCOD'] . "' 
                        AND UFFKEY = '" . $Anauff_rec['UFFRES'] . "'";
            $Uffdes_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if (!$Uffdes_rec) {
                $this->setErrMessage("Il responsabile inserito non appartiene all'ufficio " . $Anauff_rec['UFFDES'] . " .");
                return false;
            }
        }
        if ($Anauff_rec['CODICE_PADRE']) {
            if ($Anauff_rec['UFFCOD'] == $Anauff_rec['CODICE_PADRE']) {
                $this->setErrMessage("Il Codice dell'ufficio padre deve essere diverso dal codice ufficio.");
                return false;
            }
        }
        return true;
    }

    public function ControllaCancellaUfficio($anauff_rec) {
        //CONTROLLO SU UFFDES
        $sql = "SELECT ROWID FROM UFFDES WHERE UFFCOD = '" . $anauff_rec['UFFCOD'] . "'";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false, 0, 1);
        if ($rec)
            return false;
        //CONTROLLO SU ARCITE
        $sql = "SELECT ROWID FROM ARCITE WHERE ITEUFF = '" . $anauff_rec['UFFCOD'] . "'";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false, 0, 1);
        if ($rec)
            return false;
        //CONTROLLO SU ANADES
        $sql = "SELECT ROWID FROM ANADES WHERE DESCUF = '" . $anauff_rec['UFFCOD'] . "'";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false, 0, 1);
        if ($rec)
            return false;
        //CONTROLLO SU ANAPRO
        $sql = "SELECT PROUFF FROM ANAPRO WHERE PROUFF = '" . $anauff_rec['UFFCOD'] . "'";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec)
            return false;

        return true;
    }

    public function elaboraUfficio($anauff) {
        $desCodPadre = '';
        if ($anauff['CODICE_PADRE']) {
            $Anauff_rec = $this->proLib->GetAnauff($anauff['CODICE_PADRE'], 'codice');
            if ($Anauff_rec) {
                $desCodPadre = $Anauff_rec['UFFDES'];
            }
        }
        $desResponsabile = '';
        if ($anauff['UFFRES']) {
            $anamed_rec = $this->proLib->GetAnamed($anauff['UFFRES'], 'codice', 'no');
            if ($anamed_rec) {
                $desResponsabile = $anamed_rec['MEDNOM'];
            }
        }
        $nodo = array();
        $nodo['codice'][0] = array('@textNode' => $anauff['UFFCOD']);
        $nodo['descrizione'][0] = array('@textNode' => $anauff['UFFDES']);
        $nodo['abbreviazione'][0] = array('@textNode' => $anauff['UFFABB']);
        $nodo['annullamento'][0] = array('@textNode' => $anauff['UFFANN']);
        $codPadre = array();
        $codPadre['codice'] = array('@textNode' => $anauff['CODICE_PADRE']);
        $codPadre['descrizione'] = array('@textNode' => $desCodPadre);
        $nodo['codice_padre'][0] = $codPadre;
        $responsabile = array();
        $responsabile['codice'] = array('@textNode' => $anauff['UFFRES']);
        $responsabile['descrizione'] = array('@textNode' => $desResponsabile);
        $nodo['responsabile'][0] = $responsabile;

        $soggettiUfficio = $this->caricaSoggettiPresenti($anauff['UFFCOD']);
        if ($soggettiUfficio) {
            foreach ($soggettiUfficio as $soggetto) {
                $componente = array();
                $componente['codice'] = array('@textNode' => $soggetto['UFFKEY']);
                $componente['descrizione'] = array('@textNode' => $soggetto['MEDNOM']);
                $soggetti['componente'][] = $componente;
            }
        }
        $nodo['componenti'][0] = $soggetti;

        return $nodo;
    }

    public function caricaSoggettiPresenti($Ufficio) {
        $sql = "SELECT 
                    ANAMED.ROWID AS ROWID,
                    ANAMED.MEDNOM AS MEDNOM, 
                    UFFICI.UFFKEY AS UFFKEY,
                    UFFICI.UFFFI1__1 AS UFFFI1__1,
                    UFFICI.UFFCESVAL AS UFFCESVAL,
                    UFFICI.UFFCOD AS UFFCOD,
                    UFFICI.UFFSCA AS UFFSCA
                    FROM UFFDES UFFICI
                LEFT OUTER JOIN ANAMED ON UFFICI.UFFKEY=ANAMED.MEDCOD
                WHERE UFFICI.UFFCOD = '$Ufficio'  AND MEDANN = 0 ";
        $soggettiTab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

        return $soggettiTab;
    }

    public function elaboraSoggetto($anamed) {
        $nodo = array();
        $nodo['codice'][0] = array('@textNode' => $anamed['MEDCOD']);
        $nodo['titolo'][0] = array('@textNode' => $anamed['MEDTIT']);
        $nodo['denominazione'][0] = array('@textNode' => $anamed['MEDNOM']);
        $nodo['codice_fiscale'][0] = array('@textNode' => $anamed['MEDFIS']);
        $nodo['indirizzo'][0] = array('@textNode' => $anamed['MEDIND']);
        $nodo['citta'][0] = array('@textNode' => $anamed['MEDCIT']);
        $nodo['cap'][0] = array('@textNode' => $anamed['MEDCAP']);
        $nodo['provincia'][0] = array('@textNode' => $anamed['MEDPRO']);
        $nodo['posta_elettronica'][0] = array('@textNode' => $anamed['MEDEMA']);

        $ufficiSoggetto = $this->decodUffdes($anamed['MEDCOD']);
        if ($ufficiSoggetto) {
            foreach ($ufficiSoggetto as $ufficio) {
                $componente = array();
                $componente['codice'] = array('@textNode' => $ufficio['UFFCOD']);
                $componente['descrizione'] = array('@textNode' => $ufficio['UFFDES']);
                $componente['ruolo'] = array('@textNode' => $ufficio['UFFFI1__2']);
                $uffici['ufficio'][] = $componente;
            }
        }
        $nodo['uffici'][0] = $uffici;

        return $nodo;
    }

    public function decodUffdes($medcod) {
        $sql = "SELECT UFFDES.ROWID AS ROWID, UFFDES.UFFKEY AS UFFKEY, UFFDES.UFFCOD AS UFFCOD,
        UFFDES.UFFSCA AS UFFSCA, ANAUFF.UFFDES AS UFFDES, UFFDES.UFFFI1__1 AS UFFFI1__1, UFFDES.UFFFI1__2 AS UFFFI1__2, 
        UFFDES.UFFFI1__3 AS UFFFI1__3, UFFDES.UFFCESVAL AS UFFCESVAL, UFFANN AS UFFANN, UFFPROTECT AS UFFPROTECT
        FROM UFFDES UFFDES LEFT OUTER JOIN ANAUFF ANAUFF
        ON UFFDES.UFFCOD = ANAUFF.UFFCOD
        WHERE UFFDES.UFFKEY='$medcod' ORDER BY UFFFI1__3 DESC, UFFSCA DESC, UFFFI1__1 DESC, UFFDES ASC";
        $uffici = ItaDB::DBSQLSelect($this->PROT_DB, $sql);

        return $uffici;
    }

    public function ControllaCancellaSoggetto($anamed_rec) {
        $sql = "SELECT ROWID FROM ARCITE WHERE ITEDES = '" . $anamed_rec['MEDCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM ANADES WHERE DESCOD = '" . $anamed_rec['MEDCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM UTENTI WHERE UTEANA__1 = '" . $anamed_rec['MEDCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM ANAPRO WHERE PROCON = '" . $anamed_rec['MEDCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        return true;
    }

    public function cancellaUfficiAssociatiAlSoggetto($soggetto) {
        $uffdesTab = $this->proLib->GetUffdes($soggetto);
        if ($uffdesTab) {
            foreach ($uffdesTab as $uffdes) {
                try {
                    ItaDB::DBDelete($this->PROT_DB, 'UFFDES', 'ROWID', $uffdes['ROWID']);
                } catch (Exception $e) {
                    $this->setErrMessage("Errore di Cancellazione su tabella UFFDES. " . $e->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function ControllaCancellaRuoli($anaruoli) {
        $sql = "SELECT UFFFI1__2 FROM UFFDES WHERE UFFFI1__2='" . $anaruoli['RUOCOD'] . "'";
        $uffdes_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        if ($uffdes_tab) {
            return false;
        }
        return true;
    }

    public function elaboraRuolo($anaruoli) {
        $nodo = array();
        $nodo['codice'][0] = array('@textNode' => $anaruoli['RUOCOD']);
        $nodo['descrizione'][0] = array('@textNode' => $anaruoli['RUODES']);

        return $nodo;
    }

}
