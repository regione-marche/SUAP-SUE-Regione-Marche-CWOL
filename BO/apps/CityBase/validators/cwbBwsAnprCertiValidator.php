<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWS.class.php';

//

class cwbBwsAnprCertiValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            $libDB_BWS = new cwbLibDB_BWS();
            if (($data['F_CERT_SRV'] == 1) || ((!cwbLibCheckInput::IsNBZ($data['TERMINALE'])) || (!cwbLibCheckInput::IsNBZ($data['CODUTE'])))) {
                if ($data['F_CERT_SRV'] == 1) {
                    $filtri['F_CERT_SRV'] = $data['F_CERT_SRV'];
                } else {
                    if ((!cwbLibCheckInput::IsNBZ($data['TERMINALE'])) || (!cwbLibCheckInput::IsNBZ($data['CODUTE']))) {
                        $filtri['TERMINALE'] = $data['TERMINALE'];
                        $filtri['CODUTE'] = $data['CODUTE'];
                    }
                }
                $rowControl = $libDB_BWS->leggiBwsAnprCerti($filtri, false, $sqlParams);
                if (!cwbLibCheckInput::IsNBZ($rowControl['IDANPR_CERTI']) && $rowControl['IDANPR_CERTI'] != $data['IDANPR_CERTI']) {
                    if ($data['F_CERT_SRV'] == 1 && $rowControl['F_CERT_SRV'] == 1) {
                        $msg = 'Attenzione! Esiste già un record definito come postazione server';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    } else {
                        if ($data['TERMINALE'] == $rowControl['TERMINALE'] && $data['UTENTE'] == $rowControl['UTENTE']) {
                            $msg = 'Attenzione! Esiste già un record con lo stesso TERMINALE/UTENTE';
                            $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                        }
                    }
                }
            } else {
                if (cwbLibCheckInput::IsNBZ($data['TERMINALE']) && cwbLibCheckInput::IsNBZ($data['CODUTE'])) {
                    $msg = 'Attenzione! Indicare Codice Utente o Terminale!';
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            if (!cwbLibCheckInput::IsNBZ($data['TERMINALE']) && !cwbLibCheckInput::IsNBZ($data['CODUTE'])) {
                $msg = "Attenzione! Non possono essere inseriti sia l'utente che il Terminale";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ((!cwbLibCheckInput::IsNBZ($data['TERMINALE']) || !cwbLibCheckInput::IsNBZ($data['CODUTE'])) && $data['F_CERT_SRV'] > 0) {
                $msg = "Attenzione! Per la postazione di tipo server non va indicato né UTENTE e né TERMINALE";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
