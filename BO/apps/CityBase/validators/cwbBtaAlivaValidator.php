<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaAlivaValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if(strlen($data['ANNO']) === 0){
                $msg = "Anno Aliquota obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen($data['IVAALIQ']) === 0){
                $msg = "Aliquota obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
//            if(strlen($data['DESBANCA']) === 0){
//                $msg = "Descrizione Banca obbligatoria.";
//                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//            }
            // TODO sarebbe il metodo "controlla_aliquota" su Cityware
            
//            Calculate par_str as trim(par_str)
//            If par_str<>''
//            Test for valid calculation {eval(par_str)}
//            If flag true     ;; è un numero
//            If eval(par_str)>0&eval(par_str)<=44
//            Calculate loc_Ret as kTrue
//            Else
//            Calculate loc_Ret as kFalse
//            End If
//            Else     ;; non è un numero
//            Calculate par_str as upp(par_str)
//            ;  If par_str='ES'|par_str='PP'|par_str='NI'|par_str='NS'|par_str='EB'
//            Calculate loc_Ret as kTrue
//            ;  Else
//            ;  Calculate loc_Ret as kFalse
//            ;  End If
//            End If
//            Else
//            Calculate loc_Ret as kTrue
//            End If
//
//            Quit method loc_Ret
        }
    }
}
