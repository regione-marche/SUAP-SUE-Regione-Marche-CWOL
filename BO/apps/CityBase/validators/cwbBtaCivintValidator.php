<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

class cwbBtaCivintValidator extends cwbBaseValidator {

    private $error;

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            $libDB = new cwbLibDB_BTA();
            $filtri = array(
                'PROGNCIV' => $data['PROGNCIV'],
                'SCALA' => $data['SCALA'],
                'INTERNO' => $data['INTERNO'],
//                'DATAINIZ' => $data['DATAINIZ']
            );
            $row = $libDB->leggiBtaCivint($filtri, false);
            $this->error = false;
            if ($row) {
                if ($data['PROGINT'] <> $row['PROGINT']) {
                    $datainiz = $this->controllo_data($data['DATAINIZ']);
                    if (cwbLibCheckInput::IsNBZ($data['DATAFINE'])) {
                        if (cwbLibCheckInput::IsNBZ($row['DATAFINE']) || $datainiz <= $row['DATAFINE']) {
                            $this->error = true;
                        }
                    } else {
                        $datafine = $this->controllo_data($data['DATAFINE']);
                        if ($datainiz < $row['DATAINIZ'] && $datafine >= $row['DATAINIZ']) {
                            $this->error = true;
                        }
                        if ($datafine > $row['DATAFINE'] && $datainiz <= $row['DATAFINE']) {
                            $this->error = true;
                        }
                        if ($datafine >= $row['DATAINIZ'] && cwbLibCheckInput::IsNBZ($row['DATAFINE'])) {
                            $this->error = true;
                        }
                    }
                    if ($this->error == true) {
                        $msg = "Esiste già un civico interno avente la stessa scala, interno e data inizio.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
            }
            if (strlen($data['PROGNCIV']) === 0) {
                $msg = "Progr.civico obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['TIPONCIV']) === 0) {
                $msg = "Tipo civico obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (cwbLibCheckInput::IsNBZ($data['DATAFINE'])) {
                //se è nulla non devo fare il controllo fra le date perchè sbaglierebbe
            } elseif ($data['DATAINIZ'] > $data['DATAFINE']) {
                $msg = "La data fine non può essere maggiore della data inizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

    private function controllo_data($data) {
        $anno = substr($data, 0, 4);
        $mese = substr($data, 4, 2);
        $giorno = substr($data, 6, 2);
        $data_form = cwbLibCalcoli::formatta_Data($giorno, $mese, $anno, '-', true);
        $data_out = cwbLibCalcoli::dataInvertita($data_form);
        return $data_out;
    }

}
