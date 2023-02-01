<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgeTestiValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['DESTESTO']) === 0) {
                $msg = "Inserire la descrizione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo TESTOALIAS
            if (strlen($data['TESTOALIAS']) === 0) {
                $msg = "Inserire l'Alias del testo";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controlla campo TIPDIRITTI
            if ($data['TESTOPROV'] === 1 && $data['F_V_TES_1'] === 1 && strlen($data['TIPDIRITTI']) === 0) {
                $msg = "Inserire il Tipo Importo Diritti.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['F_TABULATO'] === 1 && $data['NUMRIGHE'] < 2) {
                $msg = "Se il testo è di tipo tabulato, il numero righe per pagina deve essere maggiore di 1.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
        }
    }
}
