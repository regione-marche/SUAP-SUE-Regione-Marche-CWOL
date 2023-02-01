<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

class cwbBorUteorgValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData = null, $inMemory = false) {
        $libDB = new cwbLibDB_BOR();

        if ($operation === itaModelService::OPERATION_INSERT || $operation === itaModelService::OPERATION_UPDATE) {
            if ($inMemory == false) {
                if (!empty($modifiedFormData['BOR_UTEORG'])) {
                    $uteorg = $this->getRelatedRecords($modifiedFormData, 'BOR_UTEORG');
                    $uteorg = $uteorg['BOR_UTEORG'];
                    foreach ($uteorg as $k => $v) {
                        if ($v['CODUTE'] != $data['CODUTE']) {
                            unset($uteorg[$k]);
                        }
                    }
                } else {
                    $filtri = array(
                        'CODUTE' => $data['CODUTE'],
                        'L1ORG' => $data['L1ORG'],
                        'L2ORG' => $data['L2ORG'],
                        'L3ORG' => $data['L3ORG'],
                        'L4ORG' => $data['L4ORG']
                    );
                    if ($operation == itaModelService::OPERATION_UPDATE) {
                        $filtri['IDUTEORG_diff'] = $data['IDUTEORG'];
                    }
                    $uteorg = $libDB->leggiGeneric('BOR_UTEORG', $filtri);
                    $uteorg[] = $data;
                }

                if (count($uteorg) > 1) {
                    //                $uteCoduteOrder = array();
                    $uteDataOrder = array();
                    foreach ($uteorg as $key => $ute) {
                        //                    $uteCoduteOrder[$key] = $ute['CODUTE'];
                        $uteDataOrder[$key] = (!empty($ute['DATAINIZ']) ? new DateTime($ute['DATAINIZ']) : new DateTime('1800-01-01'));
                    }
                    array_multisort($uteDataOrder, SORT_ASC, $uteorg);
                    $uteorg = array_values($uteorg);

                    for ($i = 1; $i < count($uteorg); $i++) {
                        if (trim($uteorg[$i - 1]['DATAFINE']) == '') {
                            $msg = "L'utente {$data['CODUTE']} risulta presente attivo più volte contemporaneamente sulla struttura organizzativa {$data['L1ORG']}.{$data['L2ORG']}.{$data['L3ORG']}.{$data['L4ORG']}.";
                            $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                        } else {
                            $datafinePrec = new DateTime($uteorg[$i - 1]['DATAFINE']);
                            $datainizAtt = new DateTime($uteorg[$i]['DATAINIZ']);

                            if ($datafinePrec >= $datainizAtt) {
                                $msg = "L'utente {$data['CODUTE']} risulta presente attivo più volte contemporaneamente sulla struttura organizzativa {$data['L1ORG']}.{$data['L2ORG']}.{$data['L3ORG']}.{$data['L4ORG']}.";
                                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                            }
                        }
                    }
                }

                if (!isSet($data['L1ORG']) || trim($data['L1ORG']) == '' || trim($data['L1ORG']) == '00') {
                    $msg = "E' necessario valorizzare la struttura organizzativa.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }

            if (!isSet($data['CODUTE']) || trim($data['CODUTE']) == '') {
                $msg = "E' necessario valorizzare il codice utente.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            ////////////////////////////////////////////
            ////////////Controlli aggiuntivi////////////
            ////////////////////////////////////////////
            //1
            if (isSet($data['CODUTE']) && !empty($data['KRUOLO'])) {
                $filtriutenti = array(
                    'CODUTE' => $data['CODUTE']
                );
                $databor = $libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
                $filtriruoli = array(
                    'KRUOLO' => $data['KRUOLO']
                );
                $dataruo = $libDB->leggiGeneric('BOR_RUOLI', $filtriruoli, false);
                if ($databor !== false && $databor['FLAG_GESAUT'] === 2 && $dataruo['DIRIGENTE'] == 3) {
                    $filtriutenti = array(
                        'CODUTE' => $data['CODUTE']
                    );
                    $datauteorg = $libDB->leggiGeneric('BOR_UTEORG', $filtriutenti, false);
                    if ($datauteorg !== false) {
                        for ($i = 0; $i < count($datauteorg); $i++) {
                            if ($data['CODUTE'] === $datauteorg[$i]['CODUTE']) {
                                $filtriruoli2 = array(
                                    'KRUOLO' => $datauteorg[$i]['KRUOLO']
                                );
                                $dataruo2 = $libDB->leggiGeneric('BOR_RUOLI', $filtriruoli2, false);
                                if ($dataruo2 !== false && $dataruo2['FLAG_GESAUT'] === 2) {
                                    $msg = "L'utente risulta gia associato con ruolo operatore ragioneria su un altro codice della pianta organica.";
                                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                                }
                            }
                        }
                    }
                }
            }
            ////////////////////////////////////////////
            //2
            if (isSet($data['CODUTE']) && !empty($data['KRUOLO']) && $data['FLAG_DEFAULT'] === 0) {
                $filtriutenti = array(
                    'CODUTE' => $data['CODUTE']
                );
                $databor = $libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
                $filtriruoli = array(
                    'KRUOLO' => $data['KRUOLO']
                );
                $dataruo = $libDB->leggiGeneric('BOR_RUOLI', $filtriruoli, false);

                if ($databor !== false && $databor['FLAG_GESAUT'] === 2 && $dataruo['DIRIGENTE'] == 3) {
                    $msg = "Si sta inserendo un operatore di tipo ragioneria e le autorizzazioni sono reperite da ruolo: quindi occorre indicare il servizio di default per reperire le autorizzazioni.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                } else if ($databor === false) {
                    $msg = "Autorizzazione non gestita, selezionare altro utente.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            ////////////////////////////////////////////
            //3
            if (isSet($data['CODUTE']) && !empty($data['KRUOLO']) && empty($data['ID_AUTUFF'])) {
                $filtriautute = array(
                    'CODUTE_OP' => $data['CODUTE']
                );
                $autute = $libDB->leggiGeneric('FTA_AUTUTE', $filtriautute, false);
                if ($databor !== false && ($databor['FLAG_GESAUT'] === 2 || $autute['MODO_NAVB']) === 1) {
                    $msg = "Sull'utente è previsto il riempimento delle autorizzazioni per ruolo: indicare il tipo di operatività.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
            ////////////////////////////////////////////            
            //4
            if (isSet($data['CODUTE']) && !empty($data['KRUOLO'])) {
                $filtriutenti = array(
                    'CODUTE' => $data['CODUTE']
                );
                $databor = $libDB->leggiGeneric('BOR_UTENTI', $filtriutenti, false);
                if ($databor !== false && $databor['FLAG_GESAUT'] === 2) {
                    if (isSet($data['L1ORG']) && !empty($data['L1ORG']) && $data['L1ORG'] != '00') {
                        $filtri = array('CODUTE' => $data['CODUTE'], 'L1ORG' => $data['L1ORG']);
                    }
                    if (isSet($data['L2ORG']) && !empty($data['L2ORG']) && $data['L2ORG'] != '00') {
                        $filtri['L2ORG'] = $data['L2ORG'];
                    } else {
                        $lvl = 1;
                    }
                    if (isSet($data['L3ORG']) && !empty($data['L3ORG']) && $data['L3ORG'] != '00') {
                        $filtri['L3ORG'] = $data['L3ORG'];
                    } else {
                        $lvl = 2;
                    }
                    if (isSet($data['L4ORG']) && !empty($data['L4ORG']) && $data['L4ORG'] != '00') {
                        $lvl = 4;
                    } else {
                        $lvl = 3;
                    }

                    if ($lvl === 1) {
                        $cl = $filtri['L1ORG'];
                        $filtri['L1ORG'] = null;
                        $l = 'L1ORG';
                    }
                    if ($lvl === 2) {
                        $cl = $filtri['L2ORG'];
                        $filtri['L2ORG'] = null;
                        $l = 'L2ORG';
                    }
                    if ($lvl === 3) {
                        $cl = $filtri['L3ORG'];
                        $filtri['L3ORG'] = null;
                        $l = 'L3ORG';
                    }
                    if ($lvl === 4) {
                        $cl = $filtri['L4ORG'];
                        $filtri['L4ORG'] = null;
                        $l = 'L4ORG';
                    }

                    $res = array($libDB->leggiGeneric('BOR_UTEORG', $filtri, false));
                    if ($res !== false) {
                        for ($i = 0; $i < count($res); $i++) {
                            if ($data['CODUTE'] === $res[$i]['CODUTE'] && $data['IDORGAN'] !== $res[$i]['IDORGAN'] && $data['KRUOLO'] === $res[$i]['KRUOLO'] && $res[$i][$l] > $cl) {
                                $msg = "L'utente risulta gia associato allo stesso ramo della struttura organizzativa con lo stesso ruolo.";
                                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                                break;
                            }
                        }
                    }
                }
            }
            ////////////////////////////////////////////            

            if (!isSet($data['DATAINIZ']) || trim($data['DATAINIZ']) == '') {
                $msg = "E' necessario valorizzare la data di inizio validità.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            $data['DATAINIZ'] = preg_replace("/[^0-9]/", "", $data['DATAINIZ']);

            //ruolo obbligatorio se autorizzazione reperita da ruolo 
            //(se flag_gesaut = 2  o se gesaut = 0 e bge_appli.modo_gesaut = 1) + 
            //warning ruolo vuoto ma gestito ugualmente, stessa cosa per tipo operatività.
            if (empty($data['KRUOLO'])) {
                $msg = "E' necessario valorizzare Ruolo.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            $f = array('CODUTE' => $data['CODUTE']);
            $risbor = $libDB->leggiGeneric('BOR_UTENTI', $f, false);
            $risbge = $libDB->leggiGeneric('BGE_APPLI', $f, false);

            if (empty($data['ID_AUTUFF'])) {
                if ($risbor['FLAG_GESAUT'] == 2 || ($risbor['FLAG_GESAUT'] == 0 && $risbge['MODO_GESAUT'] == 1)) {
                    $msg = "E' necessario valorizzare Tipo Operatività.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if ($risbor['FLAG_GESAUT'] == 1 || ($risbor['FLAG_GESAUT'] == 0 && $risbge['MODO_GESAUT'] != 1)) {
                    $msg = "Tipo Operatività non valorizzato.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_WARNING, $msg);
                }
            }

            if (!isSet($data['DATAFINE']) || trim($data['DATAFINE']) == '') {
                $data['DATAFINE'] = null;
            } else {
                $data['DATAFINE'] = preg_replace("/[^0-9]/", "", $data['DATAFINE']);
            }

            if (isSet($data['DATAFINE']) && trim($data['DATAFINE']) != '' && $data['DATAINIZ'] >= $data['DATAFINE']) {
                $msg = "La data di fine validità deve essere maggiore della data di inizio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }

            if (trim($data['DATAFINE']) == '' && trim($data['FLAG_DEFAULT']) == 1) {
                $filtrivalid['CODUTE'] = $data['CODUTE'];
                $filtrivalid['DATAINIZ_lt_eq'] = date('Ymd');
                $filtrivalid['DATAFINE_gt'] = date('Ymd');
                $filtrivalid['DATAFINE_or_null'] = true;
                $filtrivalid['IDUTEORG_diff_ignoreEmpty'] = $data['IDUTEORG'];
                $filtrivalid['FLAG_DEFAULT'] = $data['FLAG_DEFAULT'];
                $uteorg = $libDB->leggiGeneric('BOR_UTEORG', $filtrivalid, false, 'COUNT(*) CNT');

                if ($uteorg['CNT'] > 0) {
                    $msg = "Esiste già un utente attivo di default.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }

}
