<?php
/**
 * Description of cwbLibOrganigrammaSync
 *
 * @author f.margiotta
 */
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibOrganigramma.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSoggetti.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class cwbLibOrganigrammaSync {
    private $libDB_CITYWARE;
    private $dbPROT;
    private $dbITW;
    private $libOrganigramma;
    private $libSoggetti;
    
    private $anauffContainer;
    
    private $errors;
    
    /**
     * Costruttore della classe
     * @param string $anauffContainer chiave della struttura organizzativa del protocollo che ospita l'organigramma della finanziaria.
     *                                Se non passato viene letto dalla variabile d'ambiente ORGAN_SYNC.UFFCOD_PARENT, se passato '' sincronizza l'intero organigramma.
     */
    public function __construct($anauffContainer=null) {
        $this->libDB_CITYWARE = new cwbLibDB_BOR();
        $this->dbPROT = ItaDB::DBOpen('PROT', 'ditta');
        $this->dbITW = ItaDB::DBOpen('ITW', 'ditta');
        $this->libOrganigramma = new proLibOrganigramma();
        $this->libSoggetti = new proLibSoggetti();
        
        if(isSet($anauffContainer)){
            $this->anauffContainer = $anauffContainer;
        }
        else{
            $devLib = new devLib();
            
            $anauffContainer = $devLib->getEnv_config('ORGAN_SYNC', 'codice', 'UFFCOD_PARENT', false);
            $this->anauffContainer = !empty($anauffContainer['CONFIG']) ? $anauffContainer['CONFIG'] : '';
        }
    }
    
    /**
     * Sincronizza tutte le unità organizzative della finanziaria (BOR_ORGAN) in organigramma protocollo (PROT.ANAUFF)
     * @param boolean $updateUffdes Indica se aggiornare la descrizione dell'ufficio già presente
     * @throws ItaException
     */
    public function syncOrganToAnauff($updateUffdes=true, $resetErrors=true){
        if($resetErrors){
            $this->errors = array();
        }
        
        if(!empty($this->anauffContainer)){
            $result = ItaDB::DBSelect($this->dbPROT, 'ANAUFF', 'WHERE UFFCOD = \''.addslashes($this->anauffContainer).'\'', false);
            if(empty($result)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_DB, -1, 'Impossibile trovare la struttura contenitore su PROT.ANAUFF');
            }
        }
        
        $bor_organ = $this->libDB_CITYWARE->leggiGeneric('BOR_ORGAN', array(), true,  '*', array('L1ORG', 'L2ORG', 'L3ORG', 'L4ORG'));
        foreach($bor_organ as $row){
            try{
                $this->syncOrganToAnauffRow($row, $updateUffdes, false);
            }
            catch(ItaException $e){
                $this->errors[] = $e->getNativeErroreDesc();
            }
        }
        
        $idorgan_array = array_map(function($v){return $v['IDORGAN'];}, $bor_organ);
        
        $toDelete = ItaDB::DBSelect($this->dbPROT, 'ANAUFF', 'WHERE UFFANN <> 1 AND IDORGAN <> \'\' AND IDORGAN NOT IN ('.implode(',', $idorgan_array).')');
        foreach($toDelete as $row){
            $update = $row;
            $update['UFFANN'] = 1;
            ItaDB::DBUpdate($this->dbPROT, 'ANAUFF', 'ROWID', $update, $row);
        }
    }
    
    /**
     * Sincronizza una singola unità organizzativa della finanziaria (BOR_ORGAN) in organigramma protocollo (PROT.ANAUFF)
     * @param array $row riga di BOR_ORGAN
     * @param boolean $updateUffdes Indica se aggiornare o meno la descrizione di un'unità organizzativa preesistente
     * @param boolean $recursive Indica se aggiornare in maniera ricorsiva i genitori dell'unità organizzativa
     * @throws ItaException
     */
    public function syncOrganToAnauffRow($row, $updateUffdes=true, $recursive=true){
        $parentFilters = array();
        $parentFilters['L1ORG'] = $row['L2ORG'] != '00' ? $row['L1ORG'] : '00';
        $parentFilters['L2ORG'] = $row['L3ORG'] != '00' ? $row['L2ORG'] : '00';
        $parentFilters['L3ORG'] = $row['L4ORG'] != '00' ? $row['L3ORG'] : '00';
        $parentFilters['L4ORG'] = '00';

        if($parentFilters['L1ORG'] != '00'){
            $parent = $this->libDB_CITYWARE->leggiGeneric('BOR_ORGAN', $parentFilters, false);
            
            if($recursive){
                $this->syncOrganToAnauffRow($parent, false);
            }
            
            $result = ItaDB::DBSelect($this->dbPROT, 'ANAUFF', 'WHERE IDORGAN = '.intval($parent['IDORGAN']), false);
            if(empty($result)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non è presente il genitore della struttura con IDORGAN = '.$row['IDORGAN'].' e non è attiva la sincronizzazione ricorsiva');
            }
            $uffcodParent = $result['UFFCOD'];
        }
        else{
            $uffcodParent = $this->anauffContainer;
        }
        
        
        $data = array();
        $data['UFFDES'] = $row['DESPORG'];
            
        $uffAnn = 0;
        if(!empty($row['DATAFINE'])){
            $now = new DateTime();
            $datafine = new DateTime($row['DATAFINE']);
            if($now > $datafine){
                $uffAnn = 1;
            }
        }

        $data['UFFANN'] = $uffAnn;
        
        $filtri = array(
            'IDORGAN'=>$row['IDORGAN'],
            'DATAINIZ_lt_eq'=>date('Y-m-d'),
            'DATAFINE_gt'=>date('Y-m-d'),
            'DATAFINE_or_null'=>true
        );
        $res = $this->libDB_CITYWARE->leggiStoricoResponsabiliOrganigramma($filtri, false);
        if(!empty($res['CODUTE_RESP'])){
            $sql = 'SELECT
                        '.$this->dbPROT->getDB().'.ANAMED.*
                    FROM '.$this->dbITW->getDB().'.UTENTI
                    JOIN '.$this->dbPROT->getDB().'.ANAMED ON '.$this->dbITW->getDB().'.UTENTI.UTEANA__1 = '.$this->dbPROT->getDB().'.ANAMED.MEDCOD
                    WHERE UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) = \''.addslashes(strtoupper($res['CODUTE_RESP'])).'\'';
            $result = ItaDB::DBSQLSelect($this->dbPROT, $sql, false);
            
            if(!empty($result)){
                $data['UFFRES'] = $result['MEDCOD'];
            }
        }
        $data['UFFABB'] = $row['ALIAS'];
        $data['IDORGAN'] = $row['IDORGAN'];
        $data['CODICE_PADRE'] = $uffcodParent;
        
        $result = ItaDB::DBSelect($this->dbPROT, 'ANAUFF', 'WHERE IDORGAN = '.intval($row['IDORGAN']), false);
        if(empty($result)){
            $data['UFFCOD'] = $this->libOrganigramma->getProgANAUFF();
            
            ItaDB::DBInsert($this->dbPROT, 'ANAUFF', 'ROWID', $data);
        }
        else{
            $data['ROWID'] = $result['ROWID'];
            if(!$updateUffdes){
                unset($data['UFFDES']);
            }
            
            ItaDB::DBUpdate($this->dbPROT, 'ANAUFF', 'ROWID', $data, $result);
        }
    }
    
    /**
     * Sincronizza gli tutti utenti di cityware (BOR_UTENTI) nell'anagrafica del protocollo (PROT.ANAMED)
     */
    public function syncUtentiToAnamed($resetErrors=true){
        if($resetErrors){
            $this->errors = array();
        }
        
        $bor_utenti = $this->libDB_CITYWARE->leggiGeneric('BOR_UTENTI', array());
        foreach($bor_utenti as $row){
            try{
                $this->syncUtentiToAnamedRow($row);
            }
            catch(ItaException $e){
                $this->errors[] = $e->getNativeErroreDesc();
            }
        }
    }
    
    /**
     * Sincronizza un singolo utente di cityware (BOR_UTENTI) nell'anagrafica del protocollo (PROT.ANAMED)
     * @param array $row riga di BOR_UTENTI
     */
    public function syncUtentiToAnamedRow($row){
        $data = array();
        $data['MEDNOM'] = $row['NOMEUTE'];
        $data['MEDEMA'] = $row['E_MAIL'];
        $data['MEDFIS'] = $row['CODFISCALE'];
        
        $cnt = $this->libDB_CITYWARE->leggiBorUtentiInOrgan(array('CODUTE'=>$row['CODUTE']), false);
        $data['MEDUFF'] = ($cnt['CNT'] > 0 ? 'true' : '');
        
        $medann = 0;
        if(!empty($row['DATAFINE'])){
            $now = new DateTime();
            $datafine = new DateTime($row['DATAFINE']);
            if($now > $datafine){
                $medann = 1;
            }
        }
        $data['MEDANN'] = $medann;
        
        $sql = 'SELECT
                    '.$this->dbPROT->getDB().'.ANAMED.*
                FROM '.$this->dbITW->getDB().'.UTENTI
                JOIN '.$this->dbPROT->getDB().'.ANAMED ON '.$this->dbITW->getDB().'.UTENTI.UTEANA__1 = '.$this->dbPROT->getDB().'.ANAMED.MEDCOD
                WHERE UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) = \''.addslashes(strtoupper($row['CODUTE'])).'\'';
        
        $anamedRow = ItaDB::DBSQLSelect($this->dbPROT, $sql, false);
        if(empty($anamedRow)){
            $result = ItaDB::DBSelect($this->dbITW, 'UTENTI', 'WHERE UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) = \''.addslashes(strtoupper($row['CODUTE'])).'\'', false);
            if(empty($result)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non è presente un utente con UTELOG = '.strtoupper($row['CODUTE']).' in ITW.UTENTI');
            }
            else{
                $utenti = $result;
                
                $data['MEDCOD'] = $this->libSoggetti->getProgANAMED();
                $utenti['UTEANA__1'] = $data['MEDCOD'];
                
                ItaDB::DBUpdate($this->dbITW, 'UTENTI', 'ROWID', $utenti, $result);
            }
            
            ItaDB::DBInsert($this->dbPROT, 'ANAMED', 'ROWID', $data);
        }
        else{
            $data['ROWID'] = $anamedRow['ROWID'];
            
            ItaDB::DBUpdate($this->dbPROT, 'ANAMED', 'ROWID', $data, $anamedRow);
        }
    }
    
    /**
     * Sincronizza tutte le associazioni fra utenti e strutture organizzative della finanziaria (BOR_UTEORG) al protocollo (PROT.UFFDES)
     */
    public function syncUteorgToUffdes($resetErrors = true){
        if($resetErrors){
            $this->errors = array();
        }
        
        $bor_uteorg = $this->libDB_CITYWARE->leggiGeneric('BOR_UTEORG', array());
        
        foreach($bor_uteorg as $row){
            try{
                $this->syncUteorgToUffdesRow($row);
            }
            catch(ItaException $e){
                $this->errors[] = $e->getNativeErroreDesc();
            }
        }
        
        $sql = 'SELECT DISTINCT
                    '.$this->dbPROT->getDB().'.UFFDES.*
                FROM '.$this->dbPROT->getDB().'.UFFDES
                JOIN '.$this->dbPROT->getDB().'.ANAMED ON '.$this->dbPROT->getDB().'.UFFDES.UFFKEY = '.$this->dbPROT->getDB().'.ANAMED.MEDCOD
                JOIN '.$this->dbITW->getDB().'.UTENTI ON '.$this->dbPROT->getDB().'.ANAMED.MEDCOD = '.$this->dbITW->getDB().'.UTENTI.UTEANA__1
                JOIN '.$this->dbPROT->getDB().'.ANAUFF ON '.$this->dbPROT->getDB().'.UFFDES.UFFCOD = '.$this->dbPROT->getDB().'.ANAUFF.UFFCOD
                WHERE '.$this->dbPROT->getDB().'.ANAUFF.IDORGAN <> 0 AND '.$this->dbPROT->getDB().'.UFFDES.UFFCESVAL = \'\'';
        foreach($bor_uteorg as $row){
            $sql .= ' AND (UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) <> \''.addslashes(strtoupper($row['CODUTE'])).'\' OR '.$this->dbPROT->getDB().'.ANAUFF.IDORGAN <> '.(int)$row['IDORGAN'].')';
        }
        $toDelete = ItaDB::DBSQLSelect($this->dbPROT, $sql);
        foreach($toDelete as $row){
            $update = $row;
            $update['UFFCESVAL'] = date('Ymd');
            ItaDB::DBUpdate($this->dbPROT, 'UFFDES', 'ROWID', $update, $row);
        }
    }
    
    /**
     * Sincronizza una singola associazione fra utente e struttura organizzative della finanziaria (BOR_UTEORG) al protocollo (PROT.UFFDES)
     * @param type $row
     * @throws ItaException
     */
    public function syncUteorgToUffdesRow($row){
        $data = array();
        $uffinival = new DateTime($row['DATAINIZ']);
        $data['UFFINIVAL'] = $uffinival->format('Ymd');
        if(!empty($row['DATAFINE'])){
            $uffcesval = new DateTime($row['DATAFINE']);
            $data['UFFCESVAL'] = $uffcesval->format('Ymd');
        }
        else{
            $data['UFFCESVAL'] = '';
        }
        
        $sql = 'SELECT DISTINCT
                    '.$this->dbPROT->getDB().'.UFFDES.*
                FROM '.$this->dbPROT->getDB().'.UFFDES
                JOIN '.$this->dbPROT->getDB().'.ANAMED ON '.$this->dbPROT->getDB().'.UFFDES.UFFKEY = '.$this->dbPROT->getDB().'.ANAMED.MEDCOD
                JOIN '.$this->dbITW->getDB().'.UTENTI ON '.$this->dbPROT->getDB().'.ANAMED.MEDCOD = '.$this->dbITW->getDB().'.UTENTI.UTEANA__1
                JOIN '.$this->dbPROT->getDB().'.ANAUFF ON '.$this->dbPROT->getDB().'.UFFDES.UFFCOD = '.$this->dbPROT->getDB().'.ANAUFF.UFFCOD
                WHERE UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) = \''.addslashes(strtoupper($row['CODUTE'])).'\'
                    AND '.$this->dbPROT->getDB().'.ANAUFF.IDORGAN = '.(int)$row['IDORGAN'];
        
        $uffdesRow = ItaDB::DBSQLSelect($this->dbPROT, $sql, false);
        
        if(empty($uffdesRow)){
            $sql = 'SELECT
                        '.$this->dbPROT->getDB().'.ANAMED.*
                    FROM ANAMED
                    JOIN '.$this->dbITW->getDB().'.UTENTI ON '.$this->dbPROT->getDB().'.ANAMED.MEDCOD = '.$this->dbITW->getDB().'.UTENTI.UTEANA__1
                    WHERE UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) = \''.addslashes(strtoupper($row['CODUTE'])).'\'';
            $anamed = ItaDB::DBSQLSelect($this->dbPROT, $sql, false);
            if(empty($anamed)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non risulta essere presente l\'utente '.$row['CODUTE'].' su ITW.UTENTI/PROT.ANAMED');
            }
            
            $sql = 'SELECT * FROM ANAUFF WHERE ANAUFF.IDORGAN = '.(int)$row['IDORGAN'];
            $anauff = ItaDB::DBSQLSelect($this->dbPROT, $sql, false);
            if(empty($anauff)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non risulta essere presente la struttura organizzativa con IDORGAN = '.$row['IDORGAN']);
            }
            
            $data['UFFKEY'] = $anamed['MEDCOD'];
            $data['UFFCOD'] = $anauff['UFFCOD'];
            ItaDB::DBInsert($this->dbPROT, 'UFFDES', 'ROWID', $data);
        }
        else{
            $data['ROWID'] = $uffdesRow['ROWID'];
            ItaDB::DBUpdate($this->dbPROT, 'UFFDES', 'ROWID', $data, $uffdesRow);
        }
    }
    
    /**
     * Sincronizza tutto l'organigramma della finanziaria con quello del protocollo
     */
    public function syncCWtoProt(){
        $this->syncUtentiToAnamed(true);
        $this->syncOrganToAnauff(true, false);
        $this->syncUteorgToUffdes(false);
    }
    
    public function syncStrutturaCWtoProt($idorgan, $operation){
        if($operation === itaModelService::OPERATION_DELETE){
            $toDelete = ItaDB::DBSelect($this->dbPROT, 'ANAUFF', 'WHERE UFFANN <> 1 AND IDORGAN = '.intval($idorgan), false);
            if(!empty($toDelete)){
                $update = $toDelete;
                $update['UFFANN'] = 1;
                ItaDB::DBUpdate($this->dbPROT, 'ANAUFF', 'ROWID', $update, $toDelete);
            }
            
            $sql = 'SELECT DISTINCT
                    '.$this->dbPROT->getDB().'.UFFDES.*
                FROM '.$this->dbPROT->getDB().'.UFFDES
                JOIN '.$this->dbPROT->getDB().'.ANAUFF ON '.$this->dbPROT->getDB().'.UFFDES.UFFCOD = '.$this->dbPROT->getDB().'.ANAUFF.UFFCOD
                WHERE '.$this->dbPROT->getDB().'.ANAUFF.IDORGAN = '.intval($idorgan).'
                    AND '.$this->dbPROT->getDB().'.UFFDES.UFFCESVAL = \'\'';
            $toDelete = ItaDB::DBSQLSelect($this->dbPROT, $sql);
            foreach($toDelete as $row){
                $update = $row;
                $update['UFFCESVAL'] = date('Ymd');
                ItaDB::DBUpdate($this->dbPROT, 'UFFDES', 'ROWID', $update, $row);
            }
            
        }
        else{
            $filtri = array(
                'IDORGAN'=>$idorgan,
                'DATAINIZ_lt_eq'=>date('Ymd'),
                'DATAFINE_gt'=>date('Ymd'),
                'DATAFINE_or_null'=>true
            );
            $utenti = $this->libDB_CITYWARE->leggiBorUtentiFromUteorg($filtri, true);
            foreach($utenti as $utentiRow){
                $this->syncUtentiToAnamedRow($utentiRow);
            }
            
            $filtri = array(
                'IDORGAN'=>$idorgan
            );
            $organ = $this->libDB_CITYWARE->leggiGeneric('BOR_ORGAN', $filtri, false);
            $this->syncOrganToAnauffRow($organ);
            
            $filtri = array(
                'IDORGAN'=>$idorgan,
                'DATAINIZ_lt_eq'=>date('Ymd'),
                'DATAFINE_gt'=>date('Ymd'),
                'DATAFINE_or_null'=>true
            );
            $uteorg = $this->libDB_CITYWARE->leggiGeneric('BOR_UTEORG', $filtri, true);
            $codute = array();
            foreach($uteorg as $uteorgRow){
                $codute[$uteorgRow['CODUTE']] = true;
                $this->syncUteorgToUffdesRow($uteorgRow);
            }
            $sql = 'SELECT DISTINCT
                    '.$this->dbPROT->getDB().'.UFFDES.*
                FROM '.$this->dbPROT->getDB().'.UFFDES
                JOIN '.$this->dbPROT->getDB().'.ANAMED ON '.$this->dbPROT->getDB().'.UFFDES.UFFKEY = '.$this->dbPROT->getDB().'.ANAMED.MEDCOD
                JOIN '.$this->dbITW->getDB().'.UTENTI ON '.$this->dbPROT->getDB().'.ANAMED.MEDCOD = '.$this->dbITW->getDB().'.UTENTI.UTEANA__1
                JOIN '.$this->dbPROT->getDB().'.ANAUFF ON '.$this->dbPROT->getDB().'.UFFDES.UFFCOD = '.$this->dbPROT->getDB().'.ANAUFF.UFFCOD
                WHERE '.$this->dbPROT->getDB().'.ANAUFF.IDORGAN = '.intval($idorgan).'
                    AND '.$this->dbPROT->getDB().'.UFFDES.UFFCESVAL = \'\'
                    AND UPPER('.$this->dbITW->getDB().'.UTENTI.UTELOG) NOT IN (\''.implode('\',\'', array_keys($codute)).'\')';
            $toDelete = ItaDB::DBSQLSelect($this->dbPROT, $sql);
            foreach($toDelete as $row){
                $update = $row;
                $update['UFFCESVAL'] = date('Ymd');
                ItaDB::DBUpdate($this->dbPROT, 'UFFDES', 'ROWID', $update, $row);
            }
        }
    }
    
    public function getLastError(){
        return $this->errors;
    }
}
