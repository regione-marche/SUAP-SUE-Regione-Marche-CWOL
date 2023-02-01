<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';

class cwbBorOrganRicbiService extends itaModelService {

    public function validate($DB, $tableName, $data, $operation, $oldData, $keyMapping = array(), $modifiedData = null, $inMemory = false) {
//        return array();
        return itaModelValidator::validate($DB, 'cwbBorOrganRicbi', $this->newTableDef($tableName, $DB), $this->silent, $data, $operation, $oldData, $keyMapping, $modifiedData, $inMemory);
    }

}
