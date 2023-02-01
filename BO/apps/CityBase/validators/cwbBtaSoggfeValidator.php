<?php
include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';

class cwbBtaSoggfeValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if($operation !== itaModelService::OPERATION_DELETE){
            $libDB = new cwbLibDB_GENERIC();
            $libDB_BTA_SOGG = new cwbLibDB_BTA_SOGG();
            
            if(empty($data['PROGSOGG']) && $operation !== itaModelService::OPERATION_INSERT){
                $msg = "Soggetto non specificato";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(empty($data['CODUFF_FE'])){
                $msg = "Ufficio non specificato";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            switch($data['TIPO_FORMATO']){
                case 0:
                    if(strlen(trim($data['CODUFF_FE'])) != 6){
                        $msg = "Codice Ufficio deve essere composto da 6 Caratteri";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    if(empty($data['DESCRIZIONE'])){
                        $msg = "Valorizzare la descrizione";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    break;
                case 1:
                    if(strlen(trim($data['CODUFF_FE'])) != 7){
                        $msg = "Codice Ufficio deve essere composto da 7 Caratteri";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    } elseif($data['CODUFF_FE'] == '0000000') {
                        $msg = "Codice Ufficio deve essere diverso da '0000000'";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    break;
                case 2:
                    if(empty($data['E_MAIL_PEC'])){
                        $msg = "Indirizzo Pec obbligatorio";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    break;
            }
            
            if($operation === itaModelService::OPERATION_UPDATE){
                if( $data['TIPO_FORMATO'] != $oldCurrentRecord['TIPO_FORMATO'] ||
                    $data['CODUFF_FE'] != $oldCurrentRecord['CODUFF_FE'] ||
                    $data['E_MAIL_PEC'] != $oldCurrentRecord['E_MAIL_PEC']){
                    $filtri = array(
                        'FLAG_DIS'=>0,
                        'ID_SOGGFE'=>$data['ID_SOGGFE']
                    );
                    $cnt = $libDB->leggiGeneric('FES_DOCTES', $filtri, false, 'COUNT(*) CNT');
                    if($cnt['CNT'] > 0){
                        $msg = "L'associazione soggetto/ufficio è in uso in alcuni documenti";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    }
                }
            }
            
            // Controlla se esiste un altro soggetto con i default selezionati
            $flag_01 = $data['FLAG_01'];
            $flag_02 = $data['FLAG_02'];
            $flag_03 = $data['FLAG_03'];
            $flag_04 = $data['FLAG_04'];
            
            $filtri = array();
            $filtri['PROGSOGG'] = $data['PROGSOGG'];
            if($data['ID_SOGGFE'] > 0) {
                $filtri['NOT_ID_SOGGFE'] = $data['ID_SOGGFE'];
            }
            
            if($flag_01) {
                $filtri['FLAG_01'] = $flag_01;
                $filtri['PROGSOGG'] = $data['PROGSOGG'];
                $record = $libDB_BTA_SOGG->leggiBtaSoggfe($filtri, false);
                if($record) {
                    $msg = "Esiste già un default per ragioneria per questo soggetto.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                unset($filtri['FLAG_01']);
            }
            
            if($flag_02) {
                $filtri['FLAG_02'] = $flag_02;
                $record = $libDB_BTA_SOGG->leggiBtaSoggfe($filtri, false);
                if($record) {
                    $msg = "Esiste già un default per tributi per questo soggetto.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                unset($filtri['FLAG_02']);
            }
            
            if($flag_03) {
                $filtri['FLAG_03'] = $flag_03;
                $record = $libDB_BTA_SOGG->leggiBtaSoggfe($filtri, false);
                if($record) {
                    $msg = "Esiste già un default per acquedotto per questo soggetto.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                unset($filtri['FLAG_03']);
            }
            
            if($flag_04) {
                $filtri['FLAG_04'] = $flag_04;
                $record = $libDB_BTA_SOGG->leggiBtaSoggfe($filtri, false);
                if($record) {
                    $msg = "Esiste già un default per servizi per questo soggetto.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                unset($filtri['FLAG_04']);
            }
            
            if(strlen(trim($data['CODICE_TIPO'])) < 2 || strlen(trim($data['CODICE_TIPO'])) > 10){
                $msg = "Titolo (1.2.1.3.4) può essere da 2 a 10 Caratteri";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if(strlen(trim($data['CODICE_VALORE'])) > 20){
                $msg = "Riferimento Amministrazione (1.2.6) non può superare i 20 Caratteri";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
//            if(!empty($modifiedFormData['BTA_SOGGFE'])){
//                $soggfe = $this->getRelatedRecords($modifiedFormData, 'BTA_SOGGFE');
//                
//                $i=0;
//                foreach($soggfe['BTA_SOGGFE'] as $row){
//                    if($soggfe['CODUFF_FE'] == $data['CODUFF_FE']){
//                        $i++;
//                    }
//                    if($i>1){
//                        $msg = "Ufficio già inserito per il soggetto";
//                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//                        break;
//                    }
//                }
//            }
//            elseif(!empty($data['PROGSOGG'])){
//                $filtri = array(
//                    'PROGSOGG'=>$data['PROGSOGG'],
//                    'CODUFF_FE'=>$data['CODUFF_FE']
//                );
//                if($operation === itaModelService::OPERATION_UPDATE){
//                    $filtri['ID_SOGGFE_diff'] = $data['ID_SOGGFE'];
//                }
//                if(!empty($libDB->leggiGeneric('BTA_SOGGFE', $filtri))){
//                    $msg = "Ufficio già inserito per il soggetto";
//                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
//                }
//            }
        }
    }

}
