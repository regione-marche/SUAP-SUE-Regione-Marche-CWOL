<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaAssivaValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['IVAASSOG']) === 0) {
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DES_ASS']) === 0) {
                $msg = "Descrizione obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            //TODO da convertire il pezzetto di codice qui sotto
//            If trim(con(iv_CODICE_SDI5;' '))<>''
//            Do iv_Obj_FATTURA4PA.$get_row_codsdi(iv_session_object;iv_Obj_Sql;5;iv_CODICE_SDI5) Returns loc_row_CODSDI
//            If loc_row_CODSDI.PROGKEYSDI<>0
//            Calculate iv_Row.PROGK_SDI5 as loc_row_CODSDI.PROGKEYSDI
//            Calculate iv_DESCR_SDI5 as loc_row_CODSDI.DESCR_SDI
//            Else
//            OK message CITYWARE ! (Icon) {ATTENZIONE!!!  Codice Causale SDI Insesistente}
//            Calculate iv_DESCR_SDI5 as ''
//            Calculate iv_Row.PROGK_SDI5 as 0
//            Calculate iv_CODICE_SDI5 as ''
//            Queue set current field {Tab2_io_PROGK_SDI5}
//            Quit method kFalse
//            End If
//            Else
//            Calculate iv_DESCR_SDI5 as ''
//            End If
//
//            If trim(con(iv_Row.IVAASS_VE;' '))<>''
//            Do iv_Obj_Bilancio.$get_assog_row(iv_session_object;iv_Obj_Sql;iv_Row.IVAASS_VE) Returns loc_row_assiva
//            Calculate iv_DES_ASS as loc_row_assiva.DES_ASS
//            If trim(con(iv_DES_ASS;' '))=''
//            OK message CITYWARE {ATTENZIONE! CODICE ASSOGGETTAMENTO IVA X REGISTRO VENDITE NON VALIDO!}
//            Queue set current field {Tab2_io_IVAASS_VE}
//            Quit method kFalse
//            End If
//            Else
//            Calculate iv_DES_ASS as ''
//            End If
        }
    }

}
