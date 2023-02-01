<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaCigValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE) {
            if (strlen($data['COD_CIG']) === 0) {
                $msg = "Codice CIG obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } else if (strlen($data['COD_CIG']) != 10) {
                $msg = "Il codice CIG deve essere formato da 10 caratteri.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } else {
//                if (count($results) > 0) {
//                    foreach ($results as $result) {
//                        if ($result['COD_CIG'] == $data['COD_CIG']) {
//                            if ($result['PROG_CIG'] != $data['PROG_CIG']) {
//                                $msg = "Codice CIG gia' esistente.";
//                                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//                                break;
//                            }
//                        }
//                    }
//                }
                // Controllo se CIG gia' inserito in nostra Tabella
                if ($operation === itaModelService::OPERATION_INSERT) {
                    $filtri = array(
                        'COD_CIG' => trim($data['COD_CIG']),
                        'FLAG_DIS' => 0
                    );
                    $this->libDB = new cwbLibDB_BTA();
                    $results = $this->libDB->leggiBtaCig($filtri);

                    if (!empty($results)) {
                        $msg = "Codice CIG gia' esistente.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
            }
            if (strlen($data['DES_BREVE']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DES_CIG']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['IMPO_AGGIUDIC'] === '0.00') {
                $msg = "Imponibile non inserito";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
            if (!empty($data['IMPO_AGGIUDIC']) && !empty($data['IMPO_GARA'])) {
                if ($data['IMPO_AGGIUDIC'] > $data['IMPO_GARA']) {
                    $msg = "Imponibile aggiudicato non può essere maggiore di Importo gara";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                }
            }
            if (strlen($data['DATAINIZ']) > 0 && strlen($data['DATAFINE']) > 0) {
                $datainizio = new DateTime($data['DATAINIZ']);
                $datafine = new DateTime($data['DATAFINE']);

                if ($datafine < $datainizio) {
                    $msg = "Controllare la data fine";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            if (strlen($data['IDORGAN']) === 0) {
                $msg = "Servizio Richiedente obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CODUTE_RUP']) === 0) {
                $msg = "RUP Firma obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
        }
        if ($operation === itaModelService::OPERATION_DELETE) {
            $filtrivalid['PROG_CIG'] = $data['PROG_CIG'];
            $this->libDB_BTA = new cwbLibDB_BTA();
            $this->libDB_FES = new cwfLibDB_FES();
            $this->libDB_FAC = new cwfLibDB_FAC();

            $read1 = $this->libDB_BTA->leggiGeneric('BTA_DURC', $filtrivalid);
            $read2 = $this->libDB_FAC->leggiGeneric('FAC_TESDOC', $filtrivalid);
            $read3 = $this->libDB_FES->leggiGeneric('FES_DOCASS', $filtrivalid);
            $read4 = $this->libDB_FES->leggiGeneric('FES_DOCLIQ', $filtrivalid);
            $read5 = $this->libDB_FES->leggiGeneric('FES_IMP', $filtrivalid);

            if ($read1 != null || $read2 != null || $read3 != null || $read4 != null || $read5 != null) {
                $msg = "CIG associato ad un altra tabella.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
