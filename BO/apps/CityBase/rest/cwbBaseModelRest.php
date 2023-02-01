<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/authenticators/cwbAuthenticatorFactory.class.php';

/**
 * Classe base di gestione ModelRest di Cityware
 */
class cwbBaseModelRest extends wsModelRest {

    public function loadByPkValues($model, $pkValues) {
        $lib = $this->createLib($model);
        $method = cwbModelHelper::loadMethodNameByModelName($model);
        $pkValues = $lib->$method($pkValues);
        return $pkValues[0];
    }

    public function countByParams($model, $params) {
        $lib = $this->createLib($model);
        $method = cwbModelHelper::sqlLoadMethodNameByModelName($model);
        $n = ItaDB::DBSQLCount($this->getDB(), $lib->$method($params, false, $sqlParams), $sqlParams);
        return array(
            "COUNT" => $n
        );
    }

    public function queryByParams($model, $params, $from, $to) {
        $lib = $this->createLib($model);
        $method = cwbModelHelper::sqlLoadMethodNameByModelName($model);
        return ItaDB::DBSQLSelect($this->getDB(), $lib->$method($params, false, $sqlParams), true, $from, $to, $sqlParams);
    }

    // TODO: sistemare i dati (trim, ecc ...)
    public function postLoad(&$result) {
        
    }

    public function caricaRecordPrincipale($modelName, $data) {
        $this->setModelData(new itaModelServiceData(new cwbModelHelper()));
        $this->getModelData()->addMainRecord(cwbModelHelper::tableNameByModelName($modelName), $data['CURRENTRECORD']);
    }

    private function createLib($model) {
        $libName = cwbModelHelper::libNameByModelName($model);
        include_once ITA_BASE_PATH . "/apps/CityBase/$libName.class.php";
        $lib = new $libName;
        $this->setDB($lib->getCitywareDB());
        return $lib;
    }

    public function impostaDB($model) {
        $this->createLib($model);
    }

    public function initModelService($model) {
        $this->setModelService(cwbModelServiceFactory::newModelService($model, true, false));
    }

    public function getPkValues($tableName, $lastInsertId) {
        $pkValues = array();
        $tableDef = $this->getModelService()->newTableDef($tableName, $this->getDB());
        $pks = $tableDef->getPKs();
        if (count($pks) === 1) {
            $pkValues[$pks[0]] = $lastInsertId;
        } else {
            foreach ($tableDef->getPKs() as $key => $value) {
                $pkValues[$key] = $lastInsertId[$key];
            }
        }
        return $pkValues;
    }

    public function getAuthenticator($model) {
        return cwbAuthenticatorFactory::getAuthenticator($model, $this->prepareAuthenticatorParams());
    }

    protected function prepareAuthenticatorParams() {
        return array(
            'username' => cwbParGen::getSessionVar('nomeUtente'),
            'modulo' => 'BGE',
            'num' => 35
        );
    }

    public function isActionAllowedCustom($method, $params) {
        
    }

    public function defineByModel($modelName, $infoRelation = false) {
        $tableName = cwbModelHelper::tableNameByModelName($modelName);
        return $this->getModelService()->define($this->getDB(), $tableName, $infoRelation = false);
    }

}

?>