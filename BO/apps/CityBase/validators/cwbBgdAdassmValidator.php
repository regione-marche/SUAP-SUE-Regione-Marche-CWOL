<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
$this->libDB = new cwbLibDB_BGD(); 


class cwbBgdAdassmValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['TIPO_DOC']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            $filtri = array(
                'TIPO_DOC' => array(
                    'OPERATORE' => '<>',
                    'VALORE' => $data['IDADASSM']
                ),
                'TIPO_DOC' => $data['TIPO_DOC']
            );

            if ($this->libDB->leggiBgdAdassm($filtri)) {
                $msg = 'Tipo Documento già esistente';
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
