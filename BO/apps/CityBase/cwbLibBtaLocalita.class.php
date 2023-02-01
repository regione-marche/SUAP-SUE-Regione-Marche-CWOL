<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbLibBtaLocalita extends cwbLibDB_CITYWARE {

    private $tableName = 'BTA_LOCAL';
    private $codnazproE_DA = 950;
    private $codnazproE_A = 995;
    private $codnazproI_DA = 150;
    private $codnazproI_A = 195;
    private $ita_est = 0;

    private function getSqlLocal($filtri, $excludeOrderBy = false, &$sqlParams, $ita_est = 0, $VerifNascita = false, $VerifResid = false, $soloValidati = 1) {
        $sqlParams = array();
        if ($ita_est == 1) {
            $sql = "SELECT BTA_LOCAL.* FROM BTA_LOCAL_V01 BTA_LOCAL ";
        } else {
            $sql = "SELECT BTA_LOCAL.* FROM BTA_LOCAL BTA_LOCAL ";
            $sql .= " LEFT JOIN BTA_LOCAL_ANPR BTA_LOCAL_ANPR ON BTA_LOCAL.CODNAZPRO= BTA_LOCAL_ANPR.CODNAZPRO ";
            $sql .= "AND BTA_LOCAL.CODLOCAL = BTA_LOCAL_ANPR.CODLOCAL";
        }
        $where = " WHERE";

        if (array_key_exists('ISTNAZPRO', $filtri) && intval($filtri['ISTNAZPRO']) > -1 && is_numeric($filtri['ISTNAZPRO'])) {
            $this->addSqlParam($sqlParams, "ISTNAZPRO", $filtri['ISTNAZPRO'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.ISTNAZPRO=:ISTNAZPRO";
            $where = 'AND';
        }

        if (array_key_exists('ANPR_ISTAT', $filtri) && intval($filtri['ANPR_ISTAT']) > -1 && is_numeric($filtri['ANPR_ISTAT'])) {
            $this->addSqlParam($sqlParams, "ANPR_ISTAT", $filtri['ANPR_ISTAT'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.ANPR_ISTAT=:ANPR_ISTAT";
            $where = 'AND';
        }

        if (array_key_exists('ISTLOCAL', $filtri) && intval($filtri['ISTLOCAL']) > -1 && is_numeric($filtri['ISTLOCAL'])) {
            $this->addSqlParam($sqlParams, "ISTLOCAL", $filtri['ISTLOCAL'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.ISTLOCAL=:ISTLOCAL";
            $where = 'AND';
        }

        if (array_key_exists('F_ITA_EST', $filtri)) {
            $this->addSqlParam($sqlParams, "F_ITA_EST", $filtri['F_ITA_EST'], PDO::PARAM_INT);
            $sql .= " $where BTA_LOCAL.F_ITA_EST=:F_ITA_EST";
        }

        if ($ita_est == 0 && $soloValidati == 1) {
            $sql .= " $where BTA_LOCAL_ANPR.ID_COMUANPR>0 and BTA_LOCAL_ANPR.DATAVERIF IS NOT NULL";
            $where = 'AND';
        }

        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", strtoupper(trim($filtri['DESLOCAL'])), PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.DESLOCAL =:DESLOCAL";
            $where = 'AND';
        }

        if (array_key_exists('ANPR_DESNAZ', $filtri) && $filtri['ANPR_DESNAZ'] != null) {
            $this->addSqlParam($sqlParams, "ANPR_DESNAZ", $filtri['ANPR_DESNAZ'], PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.ANPR_DESNAZ =:ANPR_DESNAZ";
            $where = 'AND';
        }

        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", $filtri['PROVINCIA'], PDO::PARAM_STR);
            $sql .= " $where BTA_LOCAL.PROVINCIA =:PROVINCIA";
        }

        if ($ita_est == 0) {
            if (array_key_exists('DATACOMPARE', $filtri) && $filtri['DATACOMPARE'] != null) {
                $dataCompare = $this->getCitywareDB()->formatDate($filtri["DATACOMPARE"], 'YYYY.MM.DD');
                $sql .= " $where DATAINIZ <= $dataCompare  AND  ($dataCompare  > DATAFINE OR DATAFINE IS NULL)";
            }
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY DATAINIZ DESC ';
        return $sql;
    }

    private function leggiBtaLocal($filtri, $ita_est = 0, $VerifNascita = false, $VerifResid = false, $soloValidati = 0) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLocal($filtri, false, $sqlParams, $ita_est, $VerifNascita, $VerifResid, $soloValidati), true, $sqlParams);
    }

    public function ricAutoLocDaAnpr($params, $VerifNascita = false, $VerifResid = false, &$rowLocal) {
        // trattasi di residenza e il comune in questione è quello di iscrizione, non faccio nessun controllo
        if ($VerifResid == true && $params['F_RESIDENZA_COM'] == 1) {
            //ritorno la row relativa al mio comune
            $resultLocal = cwbParGen::getBorEnte();
            $rowLocal = $resultLocal;
            return true;
        }

        $filtri = array();
        if ($VerifResid == true) { // se si tratta di riconoscimento residenza
            $filtri['ANPR_ISTAT'] = $params['RESCOD'];
            if (array_key_exists('ANPR_DESNAZ_RES', $params)) {
                $this->ita_est = 1;
                $filtri['DESLOCAL'] = $params['ANPR_DESNAZ_RES'];
                $filtri['ANPR_DESNAZ'] = $params['STAINDES'];
            }
            if (array_key_exists('DESL_RES', $params)) {
                $this->ita_est = 0;
                $filtri['DESLOCAL'] = $params['DESL_RES'];
                $filtri['PROVINCIA'] = $params['PRO_RES'];
                $filtri['DATACOMPARE'] = $params['DATA_DEC_RESID'];
            }
        }

        if ($VerifNascita == true) { // se si tratta di riconoscimento nascita
            $this->ita_est = $params['F_ESTERO'];
            if ($this->ita_est == 0) {
                $filtri['ISTNAZPRO'] = substr($params['ANPR_ISTAT_NAS'], 0, 3);
                $filtri['ISTLOCAL'] = substr($params['ANPR_ISTAT_NAS'], 3, 3);
                $filtri['PROVINCIA'] = $params['ANPR_PROVNAZ_NAS'];
                $filtri['DATACOMPARE'] = $params['ANNO'] . '/' . $params['MESE'] . '/' . $params['GIORNO'];
            } else {
                $filtri['ANPR_ISTAT'] = substr($params['ANPR_ISTAT_NAS'], 0, 3);
                if ($filtri['ANPR_ISTAT'] != 998) {
                    $filtri['ANPR_DESNAZ'] = $params['ANPR_PROVNAZ_NAS'];
                }
            }

            $filtri['DESLOCAL'] = $params['ANPR_DESNAZ_NAS'];
            $filtri['F_ITA_EST'] = $params['F_ESTERO'];
        }

        $resultLocal = $this->leggiBtaLocal($filtri, $this->ita_est, $VerifNascita, $VerifResid);

//        $VerifNascita $VerifResid - is_array
        if ($this->ita_est == 0 && empty($resultLocal)) {
            if ($VerifNascita == true) {
                Out::msgInfo('Riconoscimento automatico località da ANPR', 'Attenzione! La località ' . $filtri['DESLOCAL'] . ' non è presente. Gestirla manualmente');
            } else {
                Out::msgInfo('Riconoscimento automatico località da ANPR', 'Attenzione! La località  ' . $filtri['DESLOCAL'] . ' non è presente. Gestirla manualmente');
            }
            return true;
        }

        if (cwbLibCheckInput::IsNBZ($resultLocal[0]['CODNAZPRO'])) {
            $filtri = array();
            if ($this->ita_est == 1) {
                $filtri['CODNAZPRO_DA'] = $this->codnazproE_DA;
                $filtri['CODNAZPRO_A'] = $this->codnazproE_A;
            }
//            } else {
//                $filtri['CODNAZPRO_DA'] = $this->codnazproI_DA;
//                $filtri['CODNAZPRO_A'] = $this->codnazproI_A;
//            }
            // metodo per trovare i codici liberi per l'inserimento
            $fillerLiberi = $this->trovaFiller($filtri);
            $codiciLiberi = array();
            if (cwbLibCheckInput::IsNBZ($fillerLiberi[0]['CODNAZPRO'])) {
                $codiciLiberi['CODNAZPRO'] = $filtri['CODNAZPRO_DA'];
                $codiciLiberi['CODLOCAL'] = 1;
            } else {
                if (count($fillerLiberi) >= 1) {
                    foreach ($fillerLiberi as $key => $value) {
                        $appCodnazpro = $value['CODNAZPRO'];
                        if ($value['CONTA'] != 999) {
                            $codiciLiberi['CODNAZPRO'] = $fillerLiberi[$key]['CODNAZPRO'];
                            $codiciLiberi['CODLOCAL'] = $fillerLiberi[$key]['CONTA'] + 1;
                            break;
                        }
                    }
                    if (cwbLibCheckInput::IsNBZ($codiciLiberi['CODNAZPRO'])) {
                        $codiciLiberi['CODNAZPRO'] = $appCodnazpro + 1;
                        $codiciLiberi['CODLOCAL'] = 1;
                    }
                }
            }

            // Effettuo l'inserimento in bta_local
            $this->insertLocal($codiciLiberi, $params, $this->ita_est, $VerifNascita, $VerifResid, $rowLocal);
        } else {
            $rowLocal = $resultLocal[0];
        }
        return true;
    }

    public function getSqlTrovaFiller($filtri, $excludeOrderBy = false, &$sqlParams) {
        $sqlParams = array();
//        $sql = "SELECT CODNAZPRO,COUNT(*) as CONTA FROM BTA_LOCAL "; //Non li deve contare, altrimenti se c'è un buco, poi sbglia il valore massimo
        $sql = "SELECT CODNAZPRO, max(CODLOCAL) as CONTA FROM BTA_LOCAL "; //Prendo il massimo
        $sql .= " WHERE ";
        if (array_key_exists('CODNAZPRO_DA', $filtri) && $filtri['CODNAZPRO_DA'] != null) {
            $this->addSqlParam($sqlParams, "CODNAZPRO_DA", $filtri['CODNAZPRO_DA'], PDO::PARAM_INT);
            $this->addSqlParam($sqlParams, "CODNAZPRO_A", $filtri['CODNAZPRO_A'], PDO::PARAM_INT);
            $sql .= " CODNAZPRO BETWEEN :CODNAZPRO_DA AND :CODNAZPRO_A ";
        }
        $sql .= " GROUP BY CODNAZPRO ORDER BY CODNAZPRO";
        return $sql;
    }

// Trovo il CODNAZPRO, CODLOCAL per poter eseguire l'inserimento della nuova località italiana/estera
    protected function trovaFiller($filtri) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlTrovaFiller($filtri, false, $sqlParams), true, $sqlParams);
    }

    protected function initModel(&$modelService, $tableName, &$modelServiceData) {
        if (!isset($modelService)) {
            $transactionStarted = cwbDBRequest::getInstance()->getStartedTransaction() ? true : false;
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $transactionStarted);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        }
    }

    protected function insertLocal($codiciLiberi, $params, $ita_est = 0, $VerifNascita = false, $VerifResid = false, &$rowLocal) {
        $arrayInsert = array();
        $this->cwbLibDB_BTA = new cwbLibDB_BTA();
        $arrayInsert['CODNAZPRO'] = $codiciLiberi['CODNAZPRO'];
        $arrayInsert['CODLOCAL'] = $codiciLiberi['CODLOCAL'];
        $arrayInsert['DATAINIZ'] = date("Y/m/d");
        $arrayInsert['F_ITA_EST'] = $ita_est;

        if ($ita_est == 1) { // località estera
            $filtri = array();
            if ($VerifNascita == true) {
                $filtri['NOME'] = $params['ANPR_PROVNAZ_NAS'];
                $arrayInsert['DESLOCAL'] = $params['ANPR_DESNAZ_NAS'];
            }
            if ($VerifResid == true) {
                $filtri['NOME'] = $params['STAINDES'];
                $arrayInsert['DESLOCAL'] = $params['ANPR_DESNAZ_RES'];
            }

            $StatoRec = $this->cwbLibDB_BTA->leggiBtaANPRStati($filtri);
            $arrayInsert['ISTNAZPRO'] = $StatoRec[0]['CODISTAT'];
            $arrayInsert['ISTLOCAL'] = 0;

            $arrayInsert['CODBELFI'] = $StatoRec[0]['CODCATASTALE'];
            $filtri = array();
            $filtri['CODNAZI'] = $StatoRec[0]['CODISTAT'];

            // Verifico se si tratta di un luogo eccezionale 
            if ($params['F_ECCEZIONALE'] == 0) {
                $nazion = $this->cwbLibDB_BTA->leggiBtaNazion($filtri);
                $arrayInsert['PROVINCIA'] = $nazion[0]['SIGLANAZ'];
            } else {
                $arrayInsert['ISTNAZPRO'] = $params['ISTNASPRO'];
                $arrayInsert['F_ECCEZIONALE'] = 1;
                $arrayInsert['F_ITA_EST'] = 1;
            }

            //COMMENTATO PERCHE' AL MOMENTO ABBIAMO DECISO DI NON INSERIRE I COMUNI ITALIANI
//        } else {
//            if ($VerifResid == true) {
//                $arrayInsert['ISTNAZPRO'] = substr($params['ANPR_ISTAT_RES'], 0, 3);
//                $arrayInsert['ISTLOCAL'] = substr($params['ANPR_ISTAT_RES'], 3, 3);
//                $arrayInsert['DESLOCAL'] = $params['DESL_RES'];
//                $arrayInsert['PROVINCIA'] = $params['PRO_RES'];
//            }
//            if ($VerifNascita == true) {
//                $arrayInsert['ISTNAZPRO'] = substr($params['ANPR_ISTAT_NAS'], 0, 3);
//                $arrayInsert['ISTLOCAL'] = substr($params['ANPR_ISTAT_NAS'], 3, 3);
//                $arrayInsert['DESLOCAL'] = $params['ANPR_DESNAZ_NAS'];
//                $arrayInsert['PROVINCIA'] = $params['ANPR_PROVNAZ_NAS'];
//            }
        }

        $this->initModel($modelService, $this->tableName, $modelServiceData);
        $modelService->assignRow($this->getCitywareDB(), $this->tableName, $arrayInsert, $row);
        $modelServiceData->addMainRecord($this->tableName, $row);
        $modelService->insertRecord($this->getCitywareDB(), $this->tableName, $modelServiceData->getData(), '$cwbLibBtaLocalita: Errore Insert in' . $this->tableName);

        $rowLocal = $row; //ritorno la row appena inserita
    }

}
