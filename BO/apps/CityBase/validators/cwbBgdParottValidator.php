<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBgdParottValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {

        // Controlli validi solo x ADJED
        if ($operation !== itaModelService::OPERATION_DELETE && $data['F_GESTDOC'] === 1) {

            //Percorso eseguibile SDK INS
            if (strlen($data['PER_SDKINS']) === 0) {
                $msg = "Inserire il Percorso Eseguibile SDK INS.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Percorso eseguibile SDK WEB
            if (strlen($data['PER_SDKWEB']) === 0) {
                $msg = "Inserire il Percorso Eseguibile SDK WEB.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Nome campo indice
            if (strlen($data['NOMECA_SDK']) === 0) {
                $msg = "Inserire Nome Campo Indice.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
        
        // Controlli validi solo x CITYGESTDOC
        if ($operation !== itaModelService::OPERATION_DELETE && $data['F_GESTDOC'] === 2) {

            //Codice Web Service GestDoc AMBITO
            if ($data['KWS1'] === 0) {
                $msg = "Inserire il codice Web Service GestDoc AMBITO.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Codice Web Service GestDoc FASCICOLI
            if ($data['KWS2'] === 0) {
                $msg = "Inserire il codice Web Service GestDoc FASCICOLI.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Codice Web Service GestDoc METADATI
            if ($data['KWS3'] === 0) {
                $msg = "Inserire il codice Web Service GestDoc METADATI.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Codice Web Service GestDoc REPOSITORY
            if ($data['KWS4'] === 0) {
                $msg = "Inserire il codice Web Service GestDoc REPOSITORY.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            if ($data['KWS_FTP'] === 0) {
                $msg = "Inserire il codice FTP Server.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
