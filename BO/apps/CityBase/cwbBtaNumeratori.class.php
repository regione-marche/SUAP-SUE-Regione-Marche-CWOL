<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_LIB_PATH . '/itaException/ItaException.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

/**
 * Gestione numeratori 
 */
class cwbBtaNumeratori extends cwbLibDB_CITYWARE {
	const CONTEXT_FES_LIQATT = 'FES_LIQATT';
    
    private $fields = array(
        'FES_LIQATT'=>array('TABLE'=>'FES_LIQATT',  'YEAR_FIELD'=>'ANNO_LIQAT', 'COD_NRD_FIELD'=>'COD_NRD_DL',  'SETT_IVA_FIELD'=>'',   'NUM_FIELD'=>'NUM_DL')
    );

    private $modelService;
    private $tableName;

    public function __construct() {
        $this->tableName = "BTA_NRD_AN"; //tabella principale numeratori
        $this->modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($this->tableName), true, true);
    }

    /**
     * Aggiorna il numeratore +1 
     * @param <int> $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     * @param <boolean> $internalTransaction di default viene efettuato apertura transazione
     * @param <boolean> $manualCommit di default viene effettuata anche la commit
     * @return <array> di dati definiti sulla tabella 'BTA_NRD_AN'
     * @throws <ItaException>
     */
    public function avanzaNumeratore($anno, $codiceNumeratore, $settoreIva = '', $internalTransaction = true, $manualCommit = false, $context=null) {
        if (empty($anno) || empty($codiceNumeratore)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo calcolaNumeratore");
        } elseif (empty($settoreIva)) {
            $settoreIva = '00';
        }
        $numeratore = null;
        try {
            if ($internalTransaction) {
                ItaDB::DBBeginTransaction($this->getCitywareNumeratoriDB());
            }
            //Effettua il blocco database 
            $numeratore = $this->bloccaNumeratore($anno, $codiceNumeratore, $settoreIva);

            if (empty($numeratore)) {
                $this->creaNumeratore($anno, $codiceNumeratore, $settoreIva);
				$numeratore = $this->bloccaNumeratore($anno, $codiceNumeratore, $settoreIva);
            }
			
			if(empty($numeratore)){
				throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Impossibile inizializzare il numeratore");
			}
			
			if(!empty($context)){
                $filtri = array(
                    $this->fields[$context]['YEAR_FIELD']=>$anno,
                    $this->fields[$context]['COD_NRD_FIELD']=>$codiceNumeratore
                );
                if($settoreIva != '00' && !empty($this->fields[$context]['SETT_IVA_FIELD'])){
                    $filtri[$this->fields[$context]['SETT_IVA_FIELD']] = $settoreIva;
                }
                $select = "MAX(".$this->fields[$context]['NUM_FIELD'].") NUMULTDOC";
                
                $maxNum = $this->leggiGeneric($this->fields[$context]['TABLE'], $filtri, false, $select);
                
                if(!empty($maxNum['NUMULTDOC']) && $maxNum['NUMULTDOC']>$numeratore["NUMULTDOC"]){
                    $numeratore["NUMULTDOC"] = $maxNum['NUMULTDOC'];
                }
            }
			
            $this->eseguiAggiornaNumeratore($numeratore, ++$numeratore['NUMULTDOC']);
            if ($internalTransaction && !$manualCommit) {
                ItaDB::DBCommitTransaction($this->getCitywareNumeratoriDB());
            }
        } catch (ItaException $ex) {
            if ($internalTransaction) {
                ItaDB::DBRollbackTransaction($this->getCitywareNumeratoriDB());
            }
            throw $ex;
        }
        return $numeratore;
    }

    /**
     * Effetta il commit\Rollback della transazione sulla sessione separata
     * utilizzato in accoppiata al 'manualCommit' true
     * @param <boolean> true Effettura Commit false effettua Rollback
     */
    public function confermaNumeratore($esito = true) {
        if ($esito) {
            ItaDB::DBCommitTransaction($this->getCitywareNumeratoriDB());
        } else {
            ItaDB::DBRollbackTransaction($this->getCitywareNumeratoriDB());
        }
    }

    /**
     * Ritorna una array con dati di BTA_NRD_AN (+ la descrizione di BTA_NRD) 
     * che contiene il numero ultimo documento per il numeratore e l'anno emissione passati
     * @param <int> $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     * @param <boolean> $decodifica decodifica descrizione
     * @param <boolean> $crea se il movimento non esiste, lo crea automaticamente
     * @return <array> di dati definiti sulla tabella 'BTA_NRD_AN'
     * @throws <ItaException>
     */
    public function calcolaNumeratore($anno = 0, $codiceNumeratore = '', $settoreIva = '00', $decodifica = true, $crea = true) {
        if (!$anno || !$codiceNumeratore) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo calcolaNumeratore");
        } else if ($settoreIva === "") {
            $settoreIva = "00";
        }
        try {
            $numeratore = $this->leggiNumeratore($anno, $codiceNumeratore, $settoreIva);
            if(empty($numeratore)){
                $numeratore = array(
                    'ANNOEMI'=>$anno,
                    'COD_NRD_AN'=>$codiceNumeratore,
                    'SETT_IVA'=>$settoreIva,
                    'NUMULTDOC'=>0,
                    'NUMDOCBARA'=>''
                );
            }
            $numeratore['DES_NR_D'] = $this->decodificaDescrizioneNumeratore($codiceNumeratore);
            
            return $numeratore;
        } catch (ItaException $e) {
            throw $e;
        }
    }

    /**
     * Aggiorna il numeratore  prendondo come progressivo il parametro '$progressivo'
     * @param <int> $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     * @param <integer>  $progressivo con cui aggiornare il numeratore
     * @param <boolean> $internalTransaction di default viene efettuato apertura transazione
     * @return <array> di dati definiti sulla tabella 'BTA_NRD_AN'
     * @throws <ItaException>
     */
    public function aggiornaNumeratore($anno = 0, $codiceNumeratore = '', $settoreIva = '00', $progressivo = -1, $internalTransaction = true, $manualCommit = false) {
        if (!$anno || !$codiceNumeratore) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati nel metodo calcolaNumeratore");
        } else if ($settoreIva === "") {
            $settoreIva = "00";
        }
        $numeratore = null;
        try {
            if ($internalTransaction) {
                ItaDB::DBBeginTransaction($this->getCitywareNumeratoriDB());
            }
            $numeratore = $this->bloccaNumeratore($anno, $codiceNumeratore, $settoreIva);

            $this->eseguiAggiornaNumeratore($numeratore, $progressivo);

            if ($internalTransaction && !$manualCommit) {
                ItaDB::DBCommitTransaction($this->getCitywareNumeratoriDB());
            }
        } catch (ItaException $ex) {
            if ($internalTransaction) {
                ItaDB::DBRollbackTransaction($this->getCitywareNumeratoriDB());
            }
            throw $ex;
        }
        return $numeratore;
    }

    /**
     * Ritorna l'array del modello numeratore 
     * @param <int> $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     * @throws <ItaException>
     */
    public function leggiNumeratore($anno, $codiceNumeratore, $settoreIva = '') {
        try {
            if(empty($settoreIva)){
                $settoreIva = '00';
            }
            $filtri = array(
                'ANNOEMI'=>$anno,
                'COD_NR_D'=>$codiceNumeratore,
                'SETT_IVA'=>$settoreIva
            );
            $result = $this->leggiGeneric($this->tableName, $filtri, false);
        } catch (ItaException $ex) {
            throw $ex;
        }
        return $result;
    }

    /**
     * Ritorna count del modello numeratore 
     * @param <int> $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     * @throws <ItaException>
     */
    public function countNumeratore($anno = 0, $codiceNumeratore = '', $settoreIva = '', &$sqlParams = array()) {
		if(empty($settoreIva)){
			$settoreIva = '00';
		}
		$filtri = array(
			'ANNOEMI'=>$anno,
			'COD_NR_D'=>$codiceNumeratore,
			'SETT_IVA'=>$settoreIva
		);
		$select = 'COUNT(*) AS CONTA';
		return $this->leggiGeneric($this->tableName, $filtri, false, $select);
    }

    private function bloccaNumeratore($anno, $codiceNumeratore, $settoreIva = '') {
        try {
            $sqlParams = array();
            $this->getSqlParams($sqlParams, $anno, $codiceNumeratore, $settoreIva);
            $result = ItaDB::DBLockRowTable($this->getCitywareNumeratoriDB(), $this->tableName, $sqlParams);
        } catch (ItaException $ex) {
            throw $ex;
        }
        return $result;
    }

    /**
     * Creazione del modello numeratore
     * @param <int>  $anno Anno emissione
     * @param <string> $codiceNumeratore Codice numeratore
     * @param <string> $settoreIva Settore IVA
     */
    private function creaNumeratore($anno, $codiceNumeratore, $settoreIva) {
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $value["ANNOEMI"] = $anno;
        $value["SETT_IVA"] = $settoreIva;
        $value["COD_NR_D"] = $codiceNumeratore;
        $value["NUMULTDOC"] = 0;
        $modelServiceData->addMainRecord($this->tableName, $value);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, "cwbBtaNrdAn", $modelServiceData->getData());
        $this->modelService->insertRecord($this->getCitywareNumeratoriDB(), $this->tableName, $modelServiceData->getData(), $recordInfo);

        return $this->leggiNumeratore($anno, $codiceNumeratore, $settoreIva, false);
    }


    private function getSqlParams(&$sqlParams, $anno, $codiceNumeratore, $settoreIva) {
        $this->addSqlParam($sqlParams, 'ANNOEMI', $anno, PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, 'COD_NR_D', $codiceNumeratore);
        if (strlen($settoreIva)) {
            $this->addSqlParam($sqlParams, 'SETT_IVA', $settoreIva);
        }
    }

    private function decodificaDescrizioneNumeratore($codiceNumeratore = '') {
        $sql = "SELECT DES_NR_D FROM BTA_NRD WHERE COD_NR_D=:COD_NR_D";
        $this->addSqlParam($sqlParams, 'COD_NR_D', $codiceNumeratore);
        $result = ItaDB::DBQuery($this->getCitywareNumeratoriDB(), $sql, false, $sqlParams);
        return $result["DES_NR_D"];
    }

    private function eseguiAggiornaNumeratore($numeratore, $progressivo = -1) {
        if ($numeratore !== null && $progressivo > $numeratore["NUMULTDOC"]) {
            $numeratoreOld = $numeratore;
            $numeratore["NUMULTDOC"] = $progressivo;

            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($this->tableName, $numeratore);

            $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, "cwbBtaNrdAn", $modelServiceData->getData());
            $this->modelService->updateRecord($this->getCitywareNumeratoriDB(), $this->tableName, $modelServiceData->getData(), $recordInfo, $numeratoreOld);
        }
    }

}

