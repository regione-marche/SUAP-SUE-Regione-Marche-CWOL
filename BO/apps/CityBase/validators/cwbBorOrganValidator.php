<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

class cwbBorOrganValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null) {
        $libDB = new cwbLibDB_BOR();

        if ($operation === itaModelService::OPERATION_INSERT) {
            $stores = array();
            if (itaHooks::isActive('citywareHook.php')) {
                if (isSet($modifiedFormData['BOR_STORES'])) {
                    foreach ($modifiedFormData['BOR_STORES']['tableData'] as $respo) {
                        $stores[] = $respo['data'];
                    }
                } else {
                    $msg = "Non sono stati impostati responsabili per la struttura organizzativa.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }

            $filtri = array(
                'PROGENTE' => $data['PROGENTE'],
                'L1ORG' => $data['L1ORG'],
                'L2ORG' => $data['L2ORG'],
                'L3ORG' => $data['L3ORG'],
                'L4ORG' => $data['L4ORG']
            );
            if ($libDB->leggiBorOrgan($filtri, false) !== false) {
                $msg = "Esiste già una struttura con gli stessi codici Area/Settore/Servizio/Sottoservizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }

        if ($operation === itaModelService::OPERATION_UPDATE) {
            if (strlen($data['IDORGAN']) === 0) {
                $msg = "Codice obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $stores = $this->getRelatedRecords($modifiedFormData, 'BOR_STORES');
            $stores = $stores['BOR_STORES'];

            if (empty($stores)) {
                $filtri = array(
                    'IDORGAN' => $data['IDORGAN']
                );
                $stores = $libDB->leggiStoricoResponsabiliOrganigramma($filtri);

                if ($modifiedFormData['CURRENT_RECORD']['tableData'] == $data && isSet($modifiedFormData['BOR_STORES']['tableData'])) {
                    foreach ($modifiedFormData['BOR_STORES']['tableData'] as $storMod) {
                        if ($storMod['operation'] === itaModelService::OPERATION_DELETE) {
                            $toDelete = cwbLib::searchInMultiArray($stores, array('IDSTORES' => $storMod['data']['IDSTORES']));
                            foreach (array_keys($toDelete) as $key) {
                                unset($stores[$key]);
                            }
                        } elseif ($storMod['operation'] === itaModelService::OPERATION_UPDATE) {
                            $toUpdate = cwbLib::searchInMultiArray($stores, array('IDSTORES' => $storMod['data']['IDSTORES']));
                            foreach (array_keys($toUpdate) as $key) {
                                $stores[$key]['DATAINIZ'] = $storMod['data']['DATAINIZ'];
                                $stores[$key]['DATAFINE'] = $storMod['data']['DATAFINE'];
                                $stores[$key]['IDRESPO'] = $storMod['data']['IDRESPO'];
                                $stores[$key]['NOMERES'] = $storMod['data']['NOMERES'];
                            }
                        } elseif ($storMod['operation'] === itaModelService::OPERATION_INSERT) {
                            $stores[] = array(
                                'DATAINIZ' => $storMod['data']['DATAINIZ'],
                                'DATAFINE' => $storMod['data']['DATAFINE'],
                                'IDRESPO' => $storMod['data']['IDRESPO'],
                                'NOMERES' => $storMod['data']['NOMERES']
                            );
                        }
                    }
                }
            }
        }

        if ($operation !== itaModelService::OPERATION_DELETE && $modifiedFormData['CURRENT_RECORD']['tableData'] == $data) { //TODO Da mantenere solo per il padre? Da verificare
            //CONTROLLO DESCRIZIONE
            if (!isSet($data['DESPORG']) || trim($data['DESPORG']) == '') {
                $msg = "Inserire una descrizione per la struttura organizzativa";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (!isSet($data['L1ORG']) || trim($data['L1ORG']) == '') {
                $msg = "Inserire il codice area.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } elseif (!isSet($data['L2ORG']) || trim($data['L2ORG']) == '') {
                $msg = "Inserire il codice settore.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } elseif (!isSet($data['L3ORG']) || trim($data['L3ORG']) == '') {
                $msg = "Inserire il codice servizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            } elseif (!isSet($data['L4ORG']) || trim($data['L4ORG']) == '') {
                $msg = "Inserire il codice sottoservizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //CONTROLLO VALIDITA' RESPONSABILI
            $storesOrder = array();
            foreach ($stores as $key => $resp) {
                $storesOrder[$key] = (!empty($resp['DATAINIZ']) ? strtotime(substr(str_replace(array('-', '/'), '', $resp['DATAINIZ']), 0, 8)) : 0);
            }
            array_multisort($storesOrder, SORT_ASC, $stores);

            for ($i = 1; $i < count($stores); $i++) {
                $preDataFine = null;
                $attDataInizio = null;

                if (isSet($stores[$i - 1]['DATAFINE']) && trim($stores[$i - 1]['DATAFINE']) != '') {
                    $preDataFine = (!empty($stores[$i - 1]['DATAFINE']) ? strtotime(substr(str_replace(array('-', '/'), '', $stores[$i - 1]['DATAFINE']), 0, 8)) : 0);
                    $preDataFine = new DateTime('@' . $preDataFine);
                }
                if (isSet($stores[$i]['DATAINIZ']) && trim($stores[$i]['DATAINIZ']) != '') {
                    $attDataInizio = (!empty($stores[$i]['DATAINIZ']) ? strtotime(substr(str_replace(array('-', '/'), '', $stores[$i]['DATAINIZ']), 0, 8)) : 0);
                    $attDataInizio = new DateTime('@' . $attDataInizio);
                }
                if (!isSet($preDataFine)) {
                    $msg = "La data di fine validità legata al responsabile " . $stores[$i - 1]['NOMERES'] . " non è impostata.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                } else {
                    $interval = $preDataFine->diff($attDataInizio);
                    if ($interval->format('%R%a') != '+1') {
                        $msg = "La data di fine validità di " . $stores[$i - 1]['NOMERES'] . " e quella di inizio validità di " . $stores[$i]['NOMERES'] . " non sono consecutive.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                    if ($stores[$i - 1]['NOMERES'] == $stores[$i]['NOMERES']) {
                        $msg = "E' stato inserito lo stesso reponsabile (" . $stores[$i]['NOMERES'] . ") più volte di seguito.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
            }

            $responsabileDataInizio = (!empty($stores[0]['DATAINIZ']) ? strtotime(substr(str_replace(array('-', '/'), '', $stores[0]['DATAINIZ']), 0, 8)) : 0);
            $data['DATAINIZ'] = (!empty($data['DATAINIZ']) ? strtotime(substr(str_replace(array('-', '/'), '', $data['DATAINIZ']), 0, 8)) : 0);

            $ultimoResp = end($stores);
            $responsabileDataFine = (!empty($ultimoResp['DATAFINE']) ? strtotime(substr(str_replace(array('-', '/'), '', $ultimoResp['DATAFINE']), 0, 8)) : 0);
            $data['DATAFINE'] = (!empty($data['DATAFINE']) ? strtotime(substr(str_replace(array('-', '/'), '', $data['DATAFINE']), 0, 8)) : 0);

            if ($responsabileDataInizio != $data['DATAINIZ']) {
                $msg = "La data di inizio di validità della struttura e quella del primo responsabile non coincidono.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($responsabileDataFine != $data['DATAFINE']) {
                $msg = "La data di fine di validità della struttura e quella del primo responsabile non coincidono.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            // CONTROLLO DATE MODELLI ORGANIZZATIVI
            $filtri = array(
                'IDMODORG' => $data['ID_MODORG']
            );
            $modorg = $libDB->leggiBorModorg($filtri, false);

            $modorg['DATAINIZ'] = (!empty($modorg['DATAINIZ']) ? strtotime(substr(str_replace(array('-', '/'), '', $modorg['DATAINIZ']), 0, 8)) : 0);
            $modorg['DATAFINE'] = (!empty($modorg['DATAFINE']) ? strtotime(substr(str_replace(array('-', '/'), '', $modorg['DATAFINE']), 0, 8)) : 0);

            if ($modorg['DATAINIZ'] > $data['DATAINIZ']) {
                $msg = "La data di inizio della struttura è antecedente a quella del modello organizzativo.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            if (isSet($data['DATAFINE']) && trim($data['DATAFINE']) == '') {
                $data['DATAFINE'] = null;
            }
            if (isSet($modorg['DATAFINE']) && trim($modorg['DATAFINE']) == '') {
                $modorg['DATAFINE'] = null;
            }
            if ((empty($data['DATAFINE']) && !empty($modorg['DATAFINE'])) ||
                    (!empty($data['DATAFINE']) && !empty($modorg['DATAFINE']) && $modorg['DATAFINE'] < $data['DATAFINE'])
            ) {
                $msg = "La data di fine della struttura è successiva a quella del modello organizzativo.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            //Controllo determine
            if ($data['DETIMPEGN'] == 1 && trim($data['COD_NR_DI']) == '') {
                $msg = "Selezionare un numeratore per le determine di riscossione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['DETLIQU'] == 1 && trim($data['COD_NR_DL']) == '') {
                $msg = "Selezionare un numeratore per le determine di liquidazione.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }

        if ($operation === itaModelService::OPERATION_DELETE) {
            if (count($libDB->leggiBorOrganFigli($data['IDORGAN'])) > 0) {
                $msg = "La struttura che si intende cancellare ha delle strutture al suo interno.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'IDORGAN' => $data['IDORGAN']
            );
            $cig = $libDB->leggiGeneric('BTA_CIG', $filtri, false, 'COUNT(*) CNT');
            if ($cig['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata nei CIG.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $cup = $libDB->leggiGeneric('BTA_CUP', $filtri, false, 'COUNT(*) CNT');
            if ($cup['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata nei CUP.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG_RIC' => $data['L1ORG'],
                'L2ORG_RIC' => $data['L2ORG'],
                'L3ORG_RIC' => $data['L3ORG'],
                'L4ORG_RIC' => $data['L4ORG']
            );
            $durc = $libDB->leggiGeneric('BTA_DURC', $filtri, false, 'COUNT(*) CNT');
            if ($durc['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata nei DURC.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'IDORGAN_AS' => $data['IDORGAN'],
                'IDORGAN_RS_or' => $data['IDORGAN']
            );
            $bilad = $libDB->leggiGeneric('FBA_BILAD', $filtri, false, 'COUNT(*) CNT');
            if ($bilad['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata nelle voci di bilancio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'IDORGAN' => $data['IDORGAN'],
                'IDORGAN_RS_or' => $data['IDORGAN']
            );
            $ricdet = $libDB->leggiGeneric('FBA_RICDET', $filtri, false, 'COUNT(*) CNT');
            if ($ricdet['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata nelle richieste/attribuzioni di bilancio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG_AS' => $data['L1ORG'],
                'L2ORG_AS' => $data['L2ORG'],
                'L3ORG_AS' => $data['L3ORG'],
                'L4ORG_AS' => $data['L4ORG']
            );
            $voci = $libDB->leggiGeneric('FBI_VOCI', $filtri, false, 'COUNT(*) CNT');
            if ($voci['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata come assegnatario nelle voci di bilancio (DPR 194).";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG_RS' => $data['L1ORG'],
                'L2ORG_RS' => $data['L2ORG'],
                'L3ORG_RS' => $data['L3ORG'],
                'L4ORG_RS' => $data['L4ORG']
            );
            $voci = $libDB->leggiGeneric('FBI_VOCI', $filtri, false, 'COUNT(*) CNT');
            if ($voci['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata come responsabile nelle voci di bilancio (DPR 194).";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG_DEST' => $data['L1ORG'],
                'L2ORG_DEST' => $data['L2ORG'],
                'L3ORG_DEST' => $data['L3ORG'],
                'L4ORG_DEST' => $data['L4ORG'],
                'IDORGAN_or' => $data['IDORGAN']
            );
            $diassf = $libDB->leggiGeneric('FES_DIASSF', $filtri, false, 'COUNT(*) CNT');
            if ($diassf['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata sulle distinte di assegnazione fatture.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG_ASS' => $data['L1ORG'],
                'L2ORG_ASS' => $data['L2ORG'],
                'L3ORG_ASS' => $data['L3ORG'],
                'L4ORG_ASS' => $data['L4ORG'],
                'IDORGAN_or' => $data['IDORGAN']
            );
            $docass = $libDB->leggiGeneric('FES_DOCASS', $filtri, false, 'COUNT(*) CNT');
            if ($docass['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata sulle assegnazioni dei documenti.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $filtri = array(
                'L1ORG' => $data['L1ORG'],
                'L2ORG' => $data['L2ORG'],
                'L3ORG' => $data['L3ORG'],
                'L4ORG' => $data['L4ORG'],
                'IDORGAN_or' => $data['IDORGAN']
            );
            $fesImp = $libDB->leggiGeneric('FES_IMP', $filtri, false, 'COUNT(*) CNT');
            if ($fesImp['CNT'] > 0) {
                $msg = "La struttura che si intende cancellare è usata negli impegni/accertamenti.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
        }
    }

}
