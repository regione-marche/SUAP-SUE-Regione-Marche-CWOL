<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';

class cwbBtaLocalValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CODNAZPRO']) === 0) {
                $msg = "Codice Località obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DESLOCAL']) === 0) {
                $msg = "Descrizione Località obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['CODLOCAL'] === 0 && $data['F_ITA_EST'] === 0) {
                $msg = "Codice Località obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['F_ECCEZIONALE'] === 0 && $data['CODBELFI'] === 0) {
                $msg = "Codice Belfiore obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['NREGIONE'] === 91 && $data['DATAFINE'] === 0 ) {
                $msg = "Per gli ex territori italiani è obbligatoria la data di cessazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio validità obbligatorio. Se non conosciuta può essere immesso 01/01/1900";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            //Controllo commentato dopo allineamento località da ANPR (nel file che andiamo a importare non sono presenti i codici attuali sulle località cessate)
//            if ((strlen($data['DATAFINE'])> 0 && $data['NREGIONE'] <> 91) && ($data['CODNAZPROA'] == 0 || $data['CODLOCALA'] == 0)) {
//                $msg = "Indicare i Codici località Attuali";
//                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//            }
            
            // TODO da convertire il pezzetto di codice qui sotto
            
//            ;  Lavoro 8673
//            ;  Se sono in modifica, se si tratta di comune estero se il codice consolato è diverso nella row attuale dalla row vecchia (e se nella row attuale è <> 0),
//            ;  allora emetto ok message "variato consolato: il programma varia ora automaticamente tutti gli iscritti
//            If iv_tipo_chiam<>1
//            If iv_TipoModifica='M'&iv_Row.F_ITA_EST=1&iv_old_row.CODCONSOL<>iv_Row.CODCONSOL
//            OK message [$cwind.$title()] {variato consolato: il programma varia ora automaticamente tutti gli iscritti!}
//            Calculate iv_var_consol as kTrue
//            Else
//            Calculate iv_var_consol as kFalse
//            End If
//            Else
//            If iv_TipoModifica='M'&iv_Row.F_ITA_EST=1&iv_old_row.CODCONSOL<>iv_Row.CODCONSOL
//            OK message [$cwind.$title()] {variato consolato: il programma NON varia automaticamente tutti gli iscritti!}
//            End If
//            End If
//
//            Quit method kTrue
        }
    }

}
