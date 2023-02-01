<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

class cwbBorRuoliService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if($operationType === itaModelService::OPERATION_INSERT){
            $data['PROGENTE'] = 1;
        }
    }

}