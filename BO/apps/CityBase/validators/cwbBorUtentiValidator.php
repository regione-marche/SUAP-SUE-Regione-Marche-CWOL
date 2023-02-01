<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorUtentiValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        $libDB_BGE = new cwbLibDB_BGE();
        $libDB_BOR = new cwbLibDB_BOR();
        
        if($operation === itaModelService::OPERATION_INSERT){
            $uteliv = array();
            if(isSet($modifiedFormData['BOR_UTELIV'])){
                foreach($modifiedFormData['BOR_UTELIV']['tableData'] as $respo){
                    $uteliv[] = $respo['data'];
                }
            }
        }
        
        if($operation === itaModelService::OPERATION_UPDATE) {
            $filtri = array(
                'CODUTENTE' => $data['CODUTE']
            );
            $uteliv = $libDB_BOR->leggiBorUteliv($filtri);

            if(isSet($modifiedFormData['BOR_UTELIV']['tableData'])){
                foreach($modifiedFormData['BOR_UTELIV']['tableData'] as $utelivMod){
                    if($utelivMod['operation'] === itaModelService::OPERATION_DELETE){
                        $toDelete = cwbLib::searchInMultiArray($uteliv, array('IDUTELIV'=>$utelivMod['data']['IDUTELIV']));
                        foreach(array_keys($toDelete) as $key){
                            unset($uteliv[$key]);
                        }
                    }
                    elseif($utelivMod['operation'] === itaModelService::OPERATION_UPDATE){
                        $toUpdate = cwbLib::searchInMultiArray($uteliv, array('IDUTELIV'=>$utelivMod['data']['IDUTELIV']));
                        foreach(array_keys($toUpdate) as $key){
                            $uteliv[$key]['DATAINIZ'] = $utelivMod['data']['DATAINIZ'];
                            $uteliv[$key]['DATAFINE'] = $utelivMod['data']['DATAFINE'];
                            $uteliv[$key]['IDLIVELL'] = $utelivMod['data']['IDLIVELL'];
                        }
                    }
                    elseif($utelivMod['operation'] === itaModelService::OPERATION_INSERT){
                        $uteliv[] = array(
                            'DATAINIZ'=>$utelivMod['data']['DATAINIZ'],
                            'DATAFINE'=>$utelivMod['data']['DATAFINE'],
                            'IDLIVELL'=>$utelivMod['data']['IDLIVELL']
                        );
                    }
                }
            }    
        }

        

        if ($operation !== itaModelService::OPERATION_DELETE) {
            $row = $libDB_BGE->leggiBgeAppli($filtri, true);
            
            if (strlen($data['CODUTE']) === 0) {
                $msg = "Codice Utente obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['NOMEUTE']) === 0) {
                $msg = "Nominativo obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['SIGLAUTE']) === 0) {
                $msg = "Sigla Utente obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['DATAINIZ']) === 0) {
                $msg = "Data inizio obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['UTEDB']) === 0) {
                $msg = "Codice utente database obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['PWDB']) === 0) {
                $msg = "Password del database obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (isSet($data['PWDUTE']) && strlen($data['PWDUTE']) === 0) {
                $msg = "Password obbligatoria.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($row['LUNGPWD'] > 0 && ($data['PWDUTE'] < $row['LUNGPWD'])) {
                $msg = "La password utente deve essere di almeno " . $row['LUNGPWD'] . " caratteri";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            
            $utelivOrder = array();
            foreach($uteliv as $key=>$value){
                $utelivOrder[$key] = preg_replace("/[^0-9]/","",$value['DATAINIZ']);
            }
            array_multisort($utelivOrder, SORT_ASC, $uteliv);
            for($i=1;$i<count($uteliv);$i++){
                $preDataFine = null;
                $attDataInizio = null;
                if(isSet($uteliv[$i-1]['DATAFINE']) && trim($uteliv[$i-1]['DATAFINE']) != ''){
                    $preDataFine = preg_replace("/[^0-9]/","",$uteliv[$i-1]['DATAFINE']);
                    $preDataFine = new DateTime(substr($preDataFine,0,4) . '-' . substr($preDataFine,4,2) . '-' . substr($preDataFine,6,2));
                }
                if(isSet($uteliv[$i]['DATAINIZ']) && trim($uteliv[$i]['DATAINIZ']) != ''){
                    $attDataInizio = preg_replace("/[^0-9]/","",$uteliv[$i]['DATAINIZ']);
                    $attDataInizio = new DateTime(substr($attDataInizio,0,4) . '-' . substr($attDataInizio,4,2) . '-' . substr($attDataInizio,6,2));
                }
                
                if(isSet($preDataFine) && $preDataFine>=$attDataInizio){
                    $msg = "Esiste una sovrapposizione tra i periodi di appartenenza dell'utente a diversi livelli di retribuzione.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }

}
