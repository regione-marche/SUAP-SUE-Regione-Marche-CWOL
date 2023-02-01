<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaNoteService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if(empty($data['PROGNOTE'])){
            $libDB_FTA = new cwfLibDB_FTA();
            $filtri = array(
                'PROGSOGG'=>$data['PROGSOGG']
            );
            $row = $libDB_FTA->leggiFtaClfor($filtri, false);
            if(!$row || empty($row['PROGNOTE'])){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Tabella FTA_NOTE: errore nell\'identificazione del progressivo PROGNOTE');
            }
            $data['PROGNOTE'] = $row['PROGNOTE'];
        }
        unset($data['PROGSOGG']);
        
        if ($operationType === itaModelService::OPERATION_INSERT) {
            $data['RIGANOTA'] = cwbLibCalcoli::trovaProgressivo('RIGANOTA', 'BTA_NOTE', 'PROGNOTE = ' . $data['PROGNOTE']);
        }
    }

}