<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

class cwbBtaSoggService extends itaModelService {
    protected function preOperation($DB, &$data, $operationType) {
        if ($operationType !== itaModelService::OPERATION_DELETE) {
            if($data['PROGNOTE'] == 'new'){
                $data["PROGNOTE"] = cwbLibCalcoli::trovaProgressivo("PROGNOTE", "BTA_NOTE");
            }
            
            if($data['TIPOPERS'] == 'G'){
                $data['SESSO'] = null;
                $data['NOME'] = null;
                $data['DITTAINDIV'] = null;
                $data['GIORNO'] = null;
                $data['MESE'] = null;
                $data['ANNO'] = null;
                $data['CODNAZPRO'] = null;
                $data['CODLOCAL'] = null;
                
            }
        }
    }

    protected function customOperation($DB, $data, $operationType) {
        if($operationType === itaModelService::OPERATION_DELETE){
            $libDB = new cwbLibDB_GENERIC();
            $progsogg = (isSet($data['PROGSOGG']) ? $data['PROGSOGG'] : $data['CURRENT_RECORD']['tableData']['PROGSOGG']);
            $prognote = (isSet($data['PROGNOTE']) ? $data['PROGNOTE'] : $data['CURRENT_RECORD']['tableData']['PROGNOTE']);
            
            $filtri = array(
                'PROGSOGG'=>$progsogg
            );
            $fta_clfor = $libDB->leggiGeneric('FTA_CLFOR', $filtri, false);
            if(!empty($fta_clfor)){
                $modelData = new itaModelServiceData(new cwbModelHelper());
                $modelData->addMainRecord($this->TABLE_NAME, $fta_clfor);
                
                $modelService = itaModelServiceFactory::newModelService('cwfFtaClfor', false, true);
                $modelService->deleteRecord($DB, 'FTA_CLFOR', $modelData->getData(), 'Eliminazione a cascata a seguito di eliminazione soggetto '.$progsogg);
            }
            
            if(!empty($prognote)){
                $filtri = array(
                    'PROGNOTE' => $data['PROGNOTE']
                );
                $bta_note = $libDB->leggiGeneric('BTA_NOTE', $filtri);
                
                if(!empty($bta_note)){
                    foreach($bta_note as $row){
                        $modelData = new itaModelServiceData(new cwbModelHelper());
                        $modelData->addMainRecord($this->TABLE_NAME, $row);
                        
                        $modelService = itaModelServiceFactory::newModelService('cwbBtaNote', false, true);
                        $modelService->deleteRecord($DB, 'BTA_NOTE', $modelData->getData(), 'Eliminazione a cascata a seguito di eliminazione soggetto '.$progsogg);                        
                    }
                }
            }
        }
    }
}