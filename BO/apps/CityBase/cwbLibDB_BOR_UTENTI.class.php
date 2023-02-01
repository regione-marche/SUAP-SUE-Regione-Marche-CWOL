<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCheckInput.class.php';

/**
 *
 * Utility DB Cityware (Modulo BOR_UTENTI)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibDB_BOR_UTENTI extends cwbLibDB_CITYWARE {

    const TABLE_NAME = 'BOR_UTENTI';

    public function leggiIndiceUtenti() {
        // Inizializza array di ritorno
        $results = array();

        // Legge BOR_UTENTI
        $sqlParams = array();
        $sql = 'SELECT CODUTE FROM BOR_UTENTI';
        $sql .= ' ORDER BY CODUTE';
        $utenti = ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
        return $utenti;
    }

    /**
     * Effettua lettura anagrafica utenti (o singolo utente, in funzione del parametro)
     * @param 
     * @return array Anagrafica utenti con tutte le relazioni:
     *      La chiave dell'array è il CODUTE, i valori sono degli array che 
     *      hanno come chiave il nome della tabella:
     *      - BOR_UTENTI
     *      - BOR_UTEFIR
     *      - BOR_FRMUTE
     *      - BOR_UTELIV (in join con BOR_LIVELL)
     */
    public function leggiUtenti($codute = '') {
        $codute = strtoupper(trim($codute));

        // Inizializza array di ritorno
        $results = array();

        // Legge BOR_UTENTI
        $sqlParams = array();
        $sql = 'SELECT * FROM BOR_UTENTI';
        if ($codute) {
            $sql .= ' WHERE CODUTE=:CODUTE';
            $this->addSqlParam($sqlParams, "CODUTE", $codute, PDO::PARAM_STR);
        } else {
            $sql .= ' ORDER BY CODUTE';
        }
        $utenti = ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);

        // Scorre utenti
        foreach ($utenti as $utente) {

            // Decripta campi
            $utente['PWDUTE'] = cwbLibOmnis::omnisDecrypt($utente['PWDUTE']);
            $utente['UTEDB'] = cwbLibOmnis::omnisDecrypt($utente['UTEDB']);
            $utente['PWDB'] = cwbLibOmnis::omnisDecrypt($utente['PWDB']);

            // Valorizza elemento principale
            $results[$utente['CODUTE']]['BOR_UTENTI'] = $utente;

            // Legge BOR_UTEFIR (Relazione 1:1 con BOR_UTENTI)
            $utefir = $this->leggiBorUtefir($utente['CODUTE']);

            // Decodifica Omnis Picture
            if ($utefir) {
                $utefir['IMMAGINE'] = cwbLibOmnis::fromOmnisPicture($utefir['IMMAGINE']);
            }

            $results[$utente['CODUTE']]['BOR_UTEFIR'] = $utefir;

            // Legge BOR_FRMUTE (Relazione 1:1 con BOR_UTENTI)
            $frmute = $this->leggiBorFrmute($utente['CODUTE']);

            // Decripta password
            if ($frmute) {
                $frmute['PASSW'] = cwbLibOmnis::omnisDecrypt($frmute['PASSW']);
            }

            $results[$utente['CODUTE']]['BOR_FRMUTE'] = $frmute;

            // Legge BOR_UTELIV (Relazione 1:n con BOR_UTENTI)
            $uteliv = $this->leggiBorUteliv($utente['CODUTE']);
            $results[$utente['CODUTE']]['BOR_UTELIV'] = $uteliv;
        }

        return $results;
    }

    private function leggiBorUtefir($codute) {        
        $sql = 'SELECT
                    CODUTE,
                    UTENTEDOM, ' .
                    $this->getCitywareDB()->adapterBlob("IMMAGINE") . ',
                    WIDTH,
                    HEIGHT,
                    COD_LIVELL,
                    PROGSOGG,
                    LDAP_USER
                FROM BOR_UTEFIR WHERE ' . $this->getCitywareDB()->strUpper("CODUTE") . '=:CODUTE'; 
        
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($codute)), PDO::PARAM_STR);
        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BOR_UTENTI", "leggiImmagineBorUtefir");
        $utefir = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, $infoBinaryCallback);
        return $utefir;
    }
    
    public function leggiImmagineBorUtefir($result = array()) {
        $sql = 'SELECT '.$this->getCitywareDB()->adapterBlob("IMMAGINE").' FROM BOR_UTEFIR WHERE ' . $this->getCitywareDB()->strUpper("CODUTE") . '=:CODUTE'; 
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", $result['CODUTE'], PDO::PARAM_STR);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "IMMAGINE", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['IMMAGINE'] = $resultBin['IMMAGINE'];
        
        return $result;
    }
    
    private function leggiBorFrmute($codute) {
        $sql = 'SELECT * FROM BOR_FRMUTE WHERE ' . $this->getCitywareDB()->strUpper("CODUTE") .'=:CODUTE';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($codute)), PDO::PARAM_STR);
        $frmute = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        return $frmute;
    }
    
    private function calcolaChiaveBorFrmute($codute) {
        $sql = 'SELECT MAX(IDFRMUTE) AS MAXID FROM BOR_FRMUTE';
        $sqlParams = array();        
        $frmute = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        if (!$frmute['MAXID']) {
            $frmute['MAXID'] = 0;
        }
        return $frmute['MAXID'] + 1;
    }
    
    private function leggiBorUteliv($codute) {
        $sql = 'SELECT BOR_UTELIV.*, BOR_LIVELL.DES_LIVELL
                FROM BOR_UTELIV 
                INNER JOIN BOR_LIVELL ON BOR_UTELIV.IDLIVELL=BOR_LIVELL.IDLIVELL
                WHERE ' . $this->getCitywareDB()->strUpper("BOR_UTELIV.CODUTENTE") . '=:CODUTE';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($codute)), PDO::PARAM_STR);
        $uteliv = ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
        return $uteliv;
    }

    /**
     * Effettua inserimento di un nuovo utente
     * @param string $codute Codice utente
     * @param array $data Dati utente + relazioni
     * @param string $errorMessage Eventuale messaggio di errore di validazione
     * @return array Dati inseriti
     */
    public function inserisciUtente($codute, $data, &$errorMessage) {
        return $this->inserisciOAggiornaUtente($codute, $data, itaModelService::OPERATION_INSERT, $errorMessage);
    }

    /**
     * Effettua aggiornamento di un utente presente
     * @param string $codute Codice utente
     * @param array $data Dati utente + relazioni
     * @param string $errorMessage Eventuale messaggio di errore di validazione
     * @return array Dati aggiornati
     */
    public function aggiornaUtente($codute, $data, &$errorMessage) {
        return $this->inserisciOAggiornaUtente($codute, $data, itaModelService::OPERATION_UPDATE, $errorMessage);
    }

    /**
     * Effettua la cancellazione di un utente
     * @param string $codute Codice utente
     * @param array $data Dati utente + relazioni
     * @param string $errorMessage Eventuale messaggio di errore di validazione
     * @return array Dati aggiornati
     */
    public function cancellaUtente($codute, $data, &$errorMessage) {
        return $this->inserisciOAggiornaUtente($codute, $data, itaModelService::OPERATION_DELETE, $errorMessage);
    }

    private function inserisciOAggiornaUtente($codute, $data, $operation, &$errorMessage) {
        // Azzera messaggio di errore
        $errorMessage = '';

        // Effettua il criptaggio dei campi che in Cityware devono essere criptati
        if(isSet($data[$codute]['BOR_UTENTI']['PWDUTE'])){
            $data[$codute]['BOR_UTENTI']['PWDUTE'] = cwbLibOmnis::omnisCrypt($data[$codute]['BOR_UTENTI']['PWDUTE']);
        }
        $data[$codute]['BOR_UTENTI']['UTEDB'] = cwbLibOmnis::omnisCrypt($data[$codute]['BOR_UTENTI']['UTEDB']);
        $data[$codute]['BOR_UTENTI']['PWDB'] = cwbLibOmnis::omnisCrypt($data[$codute]['BOR_UTENTI']['PWDB']);
        
        // Controlla relazioni
        if (isset($data[$codute]['BOR_FRMUTE']) && cwbLibCheckInput::IsNBZ($data[$codute]['BOR_FRMUTE']['CODUTE'])) {
            unset($data[$codute]['BOR_FRMUTE']);
        }
        if (isset($data[$codute]['BOR_UTEFIR']) && cwbLibCheckInput::IsNBZ($data[$codute]['BOR_UTEFIR']['CODUTE'])) {
            unset($data[$codute]['BOR_UTEFIR']);
        }
        if (isset($data[$codute]['BOR_UTELIV']) && empty($data[$codute]['BOR_UTELIV'])) {
            unset($data[$codute]['BOR_UTELIV']);
        }        
        
        if (array_key_exists('BOR_FRMUTE', $data[$codute])) {
            $data[$codute]['BOR_FRMUTE']['PASSW'] = cwbLibOmnis::omnisCrypt($data[$codute]['BOR_FRMUTE']['PASSW']);
            
            // Se si tratta di un inserimento, inserisce una nuova chiave
            if (!$data[$codute]['BOR_FRMUTE']['IDFRMUTE']) {
                $data[$codute]['BOR_FRMUTE']['IDFRMUTE'] = $this->calcolaChiaveBorFrmute($codute);
            }            
        }

        // Trasforma il campo immagine (se presente) in un campo Omnis Picture
        if ((array_key_exists('BOR_UTEFIR', $data[$codute])) && (!cwbLibCheckInput::IsNBZ($data[$codute]['BOR_UTEFIR']['IMMAGINE']))) {
            $pictureHeight = isset($data[$codute]['additionalInfo']['pictureHeight']) ? $data[$codute]['additionalInfo']['pictureHeight'] : $data[$codute]['BOR_UTEFIR']['HEIGHT'];
            $pictureWidth = isset($data[$codute]['additionalInfo']['pictureWidth']) ? $data[$codute]['additionalInfo']['pictureWidth'] : $data[$codute]['BOR_UTEFIR']['WIDTH'];
            $data[$codute]['BOR_UTEFIR']['IMMAGINE'] = cwbLibOmnis::toOmnisPicture($data[$codute]['BOR_UTEFIR']['IMMAGINE'], $pictureHeight, $pictureWidth);                       
        } else if ((array_key_exists('BOR_UTEFIR', $data[$codute]))) {
            $data[$codute]['BOR_UTEFIR']['IMMAGINE']='';           
        }
        
        //strtoupper dei nomi utenti (su db cityware sono tutti maiuscoli)
        $data[$codute]['BOR_UTENTI']['CODUTE'] = strtoupper(trim($codute));
        if(isSet($data[$codute]['BOR_FRMUTE'])){
            $data[$codute]['BOR_FRMUTE']['CODUTE'] = strtoupper(trim($codute));
        }
        if(isSet($data[$codute]['BOR_UTEFIR'])){
            $data[$codute]['BOR_UTEFIR']['CODUTE'] = strtoupper(trim($codute));
        }
        if(isSet($data[$codute]['BOR_UTELIV'])){
            foreach($data[$codute]['BOR_UTELIV'] as &$row){
                $row['CODUTE'] = strtoupper(trim($codute));
            }
        }

        // Crea modelService
        $modelName = cwbModelHelper::modelNameByTableName(self::TABLE_NAME);
        $recordInfo = itaModelHelper::impostaRecordInfo($operation, $modelName, $data[$codute]['BOR_UTENTI']);
        $modelService = cwbModelServiceFactory::newModelService($modelName, true);

        // Effettua validazione utente e relazioni
        $validationInfo = $this->validaUtenteERelazioni($codute, $data, $operation);
        if (empty($validationInfo)) {

            // Se non ci sono errori di validazione, procede con inserimento/aggiornamento dei dati
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord(self::TABLE_NAME, $data[$codute]['BOR_UTENTI']);

            // Inserisce dati relazione BOR_UTEFIR (1-1)
            if (isSet($data[$codute]['BOR_UTEFIR'])) {
                $modelServiceData->addRelationOneToOne("BOR_UTEFIR", $this->getBorUtefirTableData($data[$codute]['BOR_UTEFIR']), "", array("CODUTE" => "CODUTE"));
            }

            // Inserisce dati relazione BOR_FRMUTE (1-1)
            if (isSet($data[$codute]['BOR_FRMUTE'])) {
                $modelServiceData->addRelationOneToOne("BOR_FRMUTE", $this->getBorFrmuteTableData($data[$codute]['BOR_FRMUTE']), "", array("CODUTE" => "CODUTE"));
            }

            // Inserisce dati relazione BOR_FRMUTE (1-n)
            if (!empty($data[$codute]['BOR_UTELIV'])){
                $modelServiceData->addRelationOneToMany("BOR_UTELIV", $this->getBorUtelivTableData($data[$codute]['BOR_UTELIV']), "", array("CODUTE" => "CODUTENTE"), null, array('classname' => 'cwbModelServiceFactory', 'method' => 'newModelService'));
            }

            if ($operation == itaModelService::OPERATION_INSERT) {
                $modelService->insertRecord($this->getCitywareDB(), self::TABLE_NAME, $modelServiceData->getData(), $recordInfo);
            } else if ($operation == itaModelService::OPERATION_UPDATE) {
                $modelService->updateRecord($this->getCitywareDB(), self::TABLE_NAME, $modelServiceData->getData(), $recordInfo);
            } else if ($operation == itaModelService::OPERATION_DELETE) {
                $modelService->deleteRecord($this->getCitywareDB(), self::TABLE_NAME, $modelServiceData->getData(), $recordInfo);
            }

            // Rilegge utente e lo restituisce
            return $this->leggiUtenti($codute);
        } else {

            // Compone messaggio di errore di validazione
            foreach ($validationInfo as $currentInfo) {
                if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                    $errorMessage .= 'Tabella: ' . self::TABLE_NAME . ' - ';
                    $errorMessage .= $currentInfo['msg'] . '<br/>';
                }
            }

            // In caso di errore di validazione, non restituisce nulla
            return null;
        }
    }

    private function validaUtenteERelazioni($codute, $data, $operation) {
        $validationInfo = array();

        // BOR_UTENTI
        $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BOR_UTENTI'), true);
        $vi = $modelService->validate($this->getCitywareDB(), 'BOR_UTENTI', $data[$codute]['BOR_UTENTI'], $operation, null);
        $validationInfo = array_merge($validationInfo, $vi);

        // BOR_UTEFIR
        $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BOR_UTEFIR'), true);
        $vi = $modelService->validate($this->getCitywareDB(), 'BOR_UTEFIR', $data[$codute]['BOR_UTEFIR'], $operation, null);
        $validationInfo = array_merge($validationInfo, $vi);

        // BOR_FRMUTE
        $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BOR_FRMUTE'), true);
        $vi = $modelService->validate($this->getCitywareDB(), 'BOR_FRMUTE', $data[$codute]['BOR_FRMUTE'], $operation, null);
        $validationInfo = array_merge($validationInfo, $vi);

        // BOR_UTLIV
        $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BOR_UTELIV'), true);
        if(isSet($data[$codute]['BOR_UTELIV'])){
            foreach($data[$codute]['BOR_UTELIV'] as $uteliv){
                $vi = $modelService->validate($this->getCitywareDB(), 'BOR_UTELIV', $uteliv, $operation, null);
                $validationInfo = array_merge($validationInfo, $vi);
            }
        }

        return $validationInfo;
    }

    private function getBorUtefirTableData($data) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName("BOR_UTEFIR"), true);        
        $oldCurrentRecord = $modelService->getByPks($this->getCitywareDB(), "BOR_UTEFIR", $data);
        $operation = (!$oldCurrentRecord ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE);
        return $this->getBorUtentiRelazioneTableData($operation, $data);
    }

    private function getBorFrmuteTableData($data) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName("BOR_FRMUTE"), true);
        $oldCurrentRecord = $modelService->getByPks($this->getCitywareDB(), "BOR_FRMUTE", $data);
        $operation = (!$oldCurrentRecord ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE);
        return $this->getBorUtentiRelazioneTableData($operation, $data);
    }

    private function getBorUtelivTableData($data) {
        $tableData = array();
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName("BOR_UTELIV"), true);
        foreach ($data as $rec) {            
            $oldCurrentRecord = $modelService->getByPks($this->getCitywareDB(), "BOR_UTELIV", $rec);
            
            // Se l'operazione viene passata da fuori, la prende, altrimenti la calcola
            if (array_key_exists('operation', $rec)) {
                $operation = $rec['operation'];
                unset($rec['operation']);
            } else {
                $operation = (!$oldCurrentRecord ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE);
            }                       
            
            $tmp = $this->getBorUtentiRelazioneTableData($operation, $rec);
            $tableData[] = $tmp[0];
        }
        return $tableData;
    }

    private function getBorUtentiRelazioneTableData($operation, $data) {
        return array(
            array(
                'operation' => $operation,
                'data' => $data
            )
        );
    }

}
