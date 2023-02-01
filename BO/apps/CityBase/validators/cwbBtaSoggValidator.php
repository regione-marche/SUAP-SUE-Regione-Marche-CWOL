<?php
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBta.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggettoUtils.class.php';

class cwbBtaSoggValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        if ($operation !== itaModelService::OPERATION_DELETE) {
            $libDB_BTA = new cwbLibDB_BTA_SOGG();
            $libDB_BGE = new cwbLibDB_BGE();
            $libDB_FTA = new cwfLibDB_FTA();
            $bgeAppli = array_shift($libDB_BGE->leggiBgeAppli(array()));
            
            $nominativo = $data['COGNOME'] . " " . $data['NOME'];
//            if ($data['DITTAINDIV'] == 0 && trim($data['RAGSOC']) != '' && trim($data['COGNOME'] . ' ' . $data['NOME']) != trim($data['RAGSOC'])) {
            if ($data['DITTAINDIV'] == 0 && trim($data['RAGSOC']) != '' && trim($nominativo) != trim($data['RAGSOC'])) {            
                $msg = 'La ragione sociale è diversa dal nominativo (Cognome e Nome o Ragione Sociale) e non si tratta di ditta individuale. Se Rag.Sociale non è valorizzato verrà assegnato automaticamente.';
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }
            
            $cfLen = strlen(trim($data['CODFISCALE']));
            if ($cfLen != 0 && $cfLen != 11 && $cfLen != 16) {
                $msg = 'La lunghezza del codice fiscale è anomala.';
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }

            $pivaLen = strlen(trim($data['PARTIVA']));
            if ($pivaLen != 0 && $pivaLen != 11) {
                $msg = 'La lunghezza della partita IVA è anomala.';
                $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
            }

            if (!empty($data['PARTIVA'])) {
                if($data['CODNAZI'] == 1 || $data['CODNAZI'] == 0) {
                    if (!cwbLibBta::checkPIVAIta($data['PARTIVA'])) {
                        $msg = 'La partita IVA risulta non essere corretta.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    }
                } else {
                    if (!cwbLibBta::checkPIVALength($data['PARTIVA'], $data['CODNAZI'])) {
                        $msg = 'La lunghezza della partita IVA risulta essere diversa da quella specificata per lo stato.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    }
                }
            }
            
            if (!empty($data['CODFISCALE'])) {
                $filtri = array(
                    'CODFISCALE' => trim($data['CODFISCALE']),
                    'PROGSOGG_diff' => $data['PROGSOGG']
                );
                $cfCheck = $libDB_BTA->leggiBtaSogg($filtri, false, 1);
                if ($cfCheck) {
                    if ($bgeAppli['UNIV_CFIS'] == 0) {
                        $msg = 'Il soggetto ha un codice fiscale già usato da altri soggetti.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    } else {
                        $msg = 'Il soggetto ha un codice fiscale già usato da altri soggetti.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
            }
            
            if (!empty($data['PARTIVA'])) {
                $filtri = array(
                    'PARTIVA' => trim($data['PARTIVA']),
                    'PROGSOGG_diff' => $data['PROGSOGG']
                );
                $cfCheck = $libDB_BTA->leggiBtaSogg($filtri, false, 1);
                if ($cfCheck) {
                    if ($bgeAppli['UNIV_PIVA'] == 0) {
                        $msg = 'Il soggetto ha una partita IVA già usata da altri soggetti.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    } else {
                        $msg = 'Il soggetto ha una partita IVA già usata da altri soggetti.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
            }
            
            if($data['TIPOPERS'] == 'F'){
                if(!$data['COGNOME'] || !$data['NOME']){
                    $msg = "Inserire Cognome e Nome.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                
                if(!empty($data['GIORNO']) && !empty($data['MESE'])){
                    if(!checkdate($data['MESE'], $data['GIORNO'], $data['ANNO'])){
                        $msg = "La data di nascita ".$data['GIORNO']."/".$data['MESE']."/".$data['ANNO']." non è valida";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
                else{
                    if(!empty($data['ANNO']) && $data['ANNO'] < 1850){
                        $msg = "Anno di nascita non valido.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    
                    if(!empty($data['MESE']) && $data['MESE'] > 12){
                        $msg = "Mese di nascita non valido.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
                
                if (!empty($data['CODFISCALE'])) {
                    $filtri = array(
                        'CODNAZPRO'=>$data['CODNAZPRO'],
                        'CODLOCAL'=>$data['CODLOCAL']
                    );
                    $local = $libDB_FTA->leggiGeneric('BTA_LOCAL', $filtri, false);

                    $methodArgs = array();
                    $methodArgs[0] = $data['COGNOME'];
                    $methodArgs[1] = $data['NOME'];
                    $methodArgs[2] = $data['GIORNO'] . '-' . $data['MESE'] . '-' . $data['ANNO'];
                    $methodArgs[3] = $data['SESSO'];
                    $methodArgs[4] = $local['CODBELFI'];

                    $risultato = $this->calcolaCodFisc($methodArgs);
                    if (!empty($risultato['RESULT']['MESSAGE']) && $risultato['RESULT']['MESSAGE'] != $data['CODFISCALE']) {
                        $msg = 'Codice Fiscale non corretto o incongruente con dati anagrafici.';
                        $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                    }
                }
                else{
                    $msg = 'Il soggetto è persona fisica ma non è stato immesso il Codice Fiscale.';
                    $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                }
            }
            else{
                if(!$data['COGNOME']){
                    $msg = "Inserire la ragione sociale.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                
                if(empty($data['CODFISCALE']) && empty($data['PARTIVA'])) {
                    $msg = 'Il soggetto è persona giuridica ma non è stato immessa la Partita IVA né il Codice Fiscale.';
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            
            if(!empty($data['DATAMORTE'])){
                $dataMorte = str_replace(array('/','-'), '', $data['DATAMORTE']);
                $dataNascita = str_pad($data['ANNO'],4,'0',STR_PAD_RIGHT).str_pad($data['MESE'],2,'0',STR_PAD_RIGHT).str_pad($data['GIORNO'],2,'0',STR_PAD_RIGHT);
                if($dataMorte<$dataNascita){
                    $msg = "Data morte anteriore a data nascita.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            
            if(!empty($data['PROGSOGG'])){
                $fornitore = $libDB_FTA->leggiFtaClfor(array('PROGSOGG'=>$data['PROGSOGG']), false);
            }
            else{
                $fornitore = null;
            }
            
            if($fornitore || (isSet($modifiedFormData['FTA_CLFOR']) && $modifiedFormData['FTA_CLFOR']['tableData'][0]['operation'] !== itaModelService::OPERATION_DELETE)){
                if(!empty($data['PROGSOGG'])){
                    $clforp = $libDB_FTA->leggiFtaClforp(array('PROGSOGG'=>$data['PROGSOGG']));
                }
                else{
                    $clforp = array();
                }
                
                if(is_array($modifiedFormData['FTA_CLFORP']['tableData'])){
                    foreach($modifiedFormData['FTA_CLFORP']['tableData'] as $clforpRow){
                        switch($clforpRow['operation']){
                            case itaModelService::OPERATION_INSERT:
                                $clforp[] = $clforpRow['data'];
                                break;
                            case itaModelService::OPERATION_UPDATE:
                                foreach($clforp as $key=>$value){
                                    if($value['PROGSOGG'] == $clforpRow['PROGSOGG'] && $value['PAG_RIS'] == $clforpRow['PAG_RIS'] && $value['PROGINTAP'] == $clforpRow['PROGINTAP']){
                                        $clforp[$key] = $clforpRow;
                                        break;
                                    }
                                }
                                break;
                            case itaModelService::OPERATION_DELETE:
                                foreach($clforp as $key=>$value){
                                    if($value['PROGSOGG'] == $clforpRow['PROGSOGG'] && $value['PAG_RIS'] == $clforpRow['PAG_RIS'] && $value['PROGINTAP'] == $clforpRow['PROGINTAP']){
                                        unset($clforp[$key]);
                                        break;
                                    }
                                }
                                break;
                        }
                    }
                }
                
                if(empty($clforp)){
                    $msg = "Per un soggetto presente in finanziaria è necessario valorizzare almeno un metodo di pagamento/riscossione sotto albo pagamenti.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }
    
    protected function calcolaCodFisc($methodArgs) {
        $cwbBtaSoggettoUtils = new cwbBtaSoggettoUtils();        
        return $cwbBtaSoggettoUtils->calcolaCodFisc($methodArgs);
    }
    
}
