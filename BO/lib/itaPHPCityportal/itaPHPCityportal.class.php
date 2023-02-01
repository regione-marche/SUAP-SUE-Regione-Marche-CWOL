<?php

require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_Cityportal.class.php';

/**
 * Classe di utils cityportal
 *
 * @author luca cardinali
 */
class itaPHPCityportal extends cwbLibDB_CITYWARE {

    public static function getParams() {
        return itaPHPCityportal::leggiBpoParams();
    }

    public static function getParamByKey($key) {
        return itaPHPCityportal::leggiBpoParams(array('PARAMETRO' => $key), false);
    }

    private static function leggiBpoParams($filtri = array(), $multipla = true) {
        $libDb = new cwbLibDB_Cityportal();

        return $libDb->leggiBpoParams($filtri, $multipla);
    }

    public function scriviLogCP($rowBpoLog) {
        try {
            $tableName = 'BPOLOG';
            $modelService = itaModelServiceFactory ::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
//            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $this->db = $this->getCitywareDB();
//            $modelService->assignRow($this->db, $tableName, $rowBpoLog, $row);

            $row['IDUTENTE'] = $rowBpoLog['IDUTENTE'];
            $row['IDFUNZIONE'] = $rowBpoLog['IDFUNZIONE'];
            $row['DETTAGLIO'] = $rowBpoLog['DETTAGLIO'];
            $row['PROGSOGG'] = $rowBpoLog['PROGSOGG'];
            $row['F_RISULTATO'] = $rowBpoLog['F_RISULTATO'];

            $modelServiceData->addMainRecord($tableName, $row);

            $modelService->insertRecord($this->db, $tableName, $modelServiceData->getData(), 'Insert in' . $tableName);
        } catch (ItaException $e) {
            $this->setMessaggio(0, 'scriviLogCP: Insert Record BPOLOG Errore in Insert di ' . $tableName . ': ' . $e->getCompleteErrorMessage() . ' Record info:' . print_r($modelServiceData->getData(), true));
            //throw $e;
            return false;
        } catch (Exception $e) {
            $this->setMessaggio(0, 'scriviLogCP: Insert Record BPOLOG Errore in Insert di ' . $tableName . ': ' . $e->getMessage() . ' Record info:' . print_r($modelServiceData->getData(), true));
           // throw $e;
            return false;
        }
        $modelService->getLastInsertId();
    }

}
