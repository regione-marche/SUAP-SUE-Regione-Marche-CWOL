<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaVieValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        $libDB = new cwbLibDB_BTA();
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CODVIA']) === 0) {
                $msg = "Codice Via obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DESVIA']) === 0) {
                $msg = "Descrizione Via obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['TOPONIMO']) === 0) {
                $msg = "Toponimo obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }     
        
        if ($operation === itaModelService::OPERATION_INSERT) {
            $data['TOPONIMO'] = trim($data['TOPONIMO']);
            $data['DESVIA'] = strtoupper(trim($data['DESVIA']));
            $data['PROGENTE'] = trim($data['PROGENTE']);
            $row = $libDB->leggiBtaVie($data, false);
            if ($row) {
                $msg = "Esiste già una via con la stessa denominazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }
    //TODO: va gestito anche questo controllo, ma non so come prendere il CODLOCAL e il CODNAZPRO per fare il metodo $reperisci_belf
    
//    ;  Controlli sul Codice Strada Nazionale
//If not(tv_Obj_CheckInput.$IsNullBlankZero(iv_cod_belf))
//Do method reperisci_belf Returns loc_codbelfi_confr
//If iv_cod_belf<>loc_codbelfi_confr
//Yes/No message  {Attenzione!Il Codice Strada Nazionale non comincia con il seguente Codice Belfiore "[loc_codbelfi_confr]". Proseguire?}
//If flag false
//Quit method kFalse
//End If
//Else
//If tv_Obj_CheckInput.$IsNullBlankZero(iv_cod_numer)
//OK message  {Attenzione! La seconda parte del Codice Strada Nazionale deve essere valorizzata!}
//Quit method kFalse
//End If
//End If
//End If
//
//If tv_Obj_CheckInput.$IsNullBlankZero(iv_cod_belf)&not(tv_Obj_CheckInput.$IsNullBlankZero(iv_cod_numer))
//OK message  {Attenzione! la prima parte del Codice Strada Nazionale non può essere vuota!}
//Quit method kFalse
//End If
//
//If tv_Obj_CheckInput.$IsNullBlankZero(iv_cod_numer)
//Else
//If isnumber(iv_cod_numer)
//Else
//Yes/No message  {Attenzione! il codice Strada Nazionale non è numerico! Proseguire?}
//If flag false
//Quit method kFalse
//End If
//End If
//End If
//
//Quit method kTrue
//
//
//
//
//
//Begin text block
//Text: SELECT CODBELFI FROM BTA_LOCAL 
//Text:  WHERE CODNAZPRO=[tv_Obj_Par_Gen.iv_row_ente.CODNAZPRO] 
//Text:  AND CODLOCAL=[tv_Obj_Par_Gen.iv_row_ente.CODLOCAL] 
//End text block
//Get text block loc_sql
//Do iv_Obj_Sql.$leggi(iv_cursor_gen;loc_sql;loc_row) Returns loc_esito
//If loc_esito>0
//OK message  {Attenzione! Non è stato possibile reperire il codice belfiore del Comune}
//Quit method kFalse
//End If
//
//Quit method loc_row.CODBELFI
    
    
}
