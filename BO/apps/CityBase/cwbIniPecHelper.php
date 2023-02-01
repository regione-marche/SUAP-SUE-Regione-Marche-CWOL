<?php

include_once (ITA_BASE_PATH . '/apps/OpenData/opdLib.class.php');
include_once ITA_BASE_PATH . '/apps/OpenData/opdIniPecManager.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_LIB_PATH . '/zip/itaZipCommandLine.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TBA.class.php';
include_once (ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

class cwbIniPecHelper {

    public function __construct($codute = null) {
        $this->iniPecManager = opdIniPecManager::getInstance();
    }

    /**
     * Restituisce una selezione di matricole in base al nome tabella e ai filtri impostati
     * @param string $tableName Nome della tabella da cui estrarre le anagrafiche
     * @param string $filtri Filtri di ricerca
     * @return selezione di matricole
     */
    public function recuperaMatricole($tableName, $filtri = array()) {
        $modelName = cwbIniPecHelper::reperisciLib($tableName);
        $sql = cwbModelHelper::loadMethodNameByModelName($modelName) . "AggioCf";
        $matricole = $this->libDB->$sql($filtri);
        if (!empty($matricole)) {
            return $matricole;
        }
        return array();
    }

    public function reperisciLib($tableName) {
        $modelName = cwbModelHelper::modelNameByTableName($tableName);
        $libName = cwbModelHelper::libNameByModelName($modelName);
        $importLib = ITA_BASE_PATH . '/' . cwbModelHelper::moduleByModelName($modelName) . '/' . $libName . '.class.php';

        if (file_exists($importLib)) {
            include_once $importLib;
            $this->libDB = new $libName();
        } else {
            // todo lanciare eccezione?
        }
        return $modelName;
    }

    /**
     * Restituisce gli la richiesta fornitura PEC 
     * @param string $arrayCf array multi popolato da codici fiscale
     * @return file path del flusso restituito da iniPEC
     */
    public function richiestaFornituraPec($arrayCf, $context = 'CITYWARE') {
        $iniPecManager = opdIniPecManager::getInstance();
        $flusso_rec = $iniPecManager->RichiestaFornituraPec($arrayCf, $context);
        if (!$flusso_rec) {
            Out::msgStop('Errore', $this->iniPecManager->getLastMessage());
            return;
        }
        return $flusso_rec['IDRICHIESTA'];
    }

    /**
     * Restituisce gli indirizzi PEC tramite chiamata ws a iniPEC
     * @param string $arrayCf array multi popolato da codici fiscale
     * @return file path del flusso restituito da iniPEC
     */
    public function scaricoFornituraPec($idRichiesta, $download = false) {
        $iniPecManager = opdIniPecManager::getInstance();
        $nomeFile = $iniPecManager->ScaricoFornituraPec($idRichiesta);

        if ($nomeFile) {
            $opdLib = new opdLib();
            $dir = $opdLib->SetDirectoryOpd('INIPEC');
            $fullPath = $dir . '/' . $nomeFile;
            if ($download) {
                Out::openDocument(utiDownload::getUrl($nomeFile, $fullPath));
            } else {
                return $iniPecManager->leggiFileRispostaFromId($idRichiesta);
            }
        } else {
            Out::msgStop('Errore', $this->iniPecManager->getLastMessage());
        }

        //Out::msgInfo('Risultato', 'Richiesta effettuata, id: ' . $flusso_rec['IDRICHIESTA']);
    }

    /**
     * Restituisce gli indirizzi PEC tramite chiamata ws a iniPEC
     * @param string $arrayCf array multi popolato da codici fiscale
     * @return file path del flusso restituito da iniPEC
     */
    public function recuperaIndirizziPecDaCf($tableName, $filtri = array(), $context = 'CITYWARE', $downloadFornitura) {
        $matricole = cwbIniPecHelper::recuperaMatricole($tableName, $filtri);
        $arrayCf = cwbIniPecHelper::creaArrayCodiciFiscali($matricole);
        $flusso_rec = cwbIniPecHelper::richiestaFornituraPec($arrayCf, 'CITYWARE');
        sleep(30);
        return cwbIniPecHelper::scaricoFornituraPec($flusso_rec, $downloadFornitura);
    }

    public function creaArrayCodiciFiscali($matricole) {
        $arrayCf = array();
        foreach ($matricole as $key => $value) {
            $arrayCf[] = $value['CODFISCALE'];
        }
        return $arrayCf;
    }

    /**
     * Aggiorna le tabelle elaborando il .csv restituito da IniPEC
     * @param string §$tableName Nome tabella da aggiornare
     * @param string $pathFlusso path del flusso scaricato
     * @return file path del flusso restituito da iniPEC
     */
    public function aggiornaTabelleDopoScarico($arrayFlusso, $tableName, $db,$context = 'CITYWARE') {
        $esito = true;
        cwbDBRequest::getInstance()->startManualTransaction(null, $db);
        try {
            $modelName = cwbIniPecHelper::reperisciLib($tableName);
            foreach ($arrayFlusso as $key => $value) {
                if ($value['ESITO_PEC_I'] == 'OK' || $value['ESITO_PEC_C'] == 'OK' || $value['ESITO_PEC_P'] == 'OK') {
                    if ($value['ESITO_PEC_I'] == 'OK') {
                        $pec = $value['PEC_I'];
                    } elseif ($value['ESITO_PEC_C'] == 'OK') {
                        $pec = $value['PEC_C'];
                    } elseif ($value['ESITO_PEC_P'] == 'OK') {
                        $pec = $value['PEC_P'];
                    }
                    $libDB_BTA = new cwbLibDB_BTA();
                    $btaSogg = $libDB_BTA->leggiBtaSogg(array("CODFISCALE" => trim($value['CODICEFISCALE'])));
                    foreach ($btaSogg as $soggetto) {
                        cwbIniPecHelper::aggiornaBtaSogg($soggetto, $value['N_REA'],$db);

                        //$residenza = $this->libDB->leggiTbaResid(array("PROGSOGG" => $soggetto['PROGSOGG'], "DATAFINE_NULL" => true));
                        $sql = cwbModelHelper::loadMethodNameByModelName($modelName);
                        $residenza = $this->libDB->$sql(array("PROGSOGG" => $soggetto['PROGSOGG'], "DATAFINE_NULL" => true));
                        foreach ($residenza as $resid) {
                            cwbIniPecHelper::aggiornaTabDatiResidenza($resid, $pec, $value['DATAELABORAZIONE'], $tableName,$db);
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $errore = true;
        }

        if (!$errore) {
            cwbDBRequest::getInstance()->commitManualTransaction();
        } else {
            $esito = false;
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        }
        return $esito;
    }

    private function aggiornaBtaSogg($soggetto, $nRea,$db) {
        $updateSoggetto = $soggetto;
        $updateSoggetto['NR_ISC_REA'] = $nRea['N_REA'];
        $updateSoggetto['CODUTE'] = cwbParGen::getSessionVar('nomeUtente');
        $updateSoggetto['DATAOPER'] = date('Ymd');
        $updateSoggetto['TIMEOPER'] = date('H:i:s');
        cwbIniPecHelper::insertUpdateRecord($updateSoggetto, 'BTA_SOGG', false, true,$db);
    }

    private function aggiornaTabDatiResidenza($resid, $pec, $dataElab, $tableName,$db) {
        $updateResid = $resid;
        $updateResid['E_MAIL_PEC'] = $pec;
        $updateResid['F_SPEDIZ'] = 1;
        $updateResid['DATA_AGG_PEC'] = $dataElab;
        cwbIniPecHelper::insertUpdateRecord($updateResid, $tableName, false, true,$db);
    }

    public function leggiFileRispostaFromId($idRichiesta) {
        $iniPecManager = opdIniPecManager::getInstance();
        return $iniPecManager->leggiFileRispostaFromId($idRichiesta);
    }

    private function insertUpdateRecord($toInsert, $tableName, $insert = true, $startedTransaction = false,$db) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord($tableName, $toInsert);
        $recordInfo = itaModelHelper::impostaRecordInfo(($insert ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE), 'cwbIniPecHelper', $toInsert);
        if ($insert) {
            $modelService->insertRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
        } else {
            $modelService->updateRecord($db, $tableName, $modelServiceData->getData(), $recordInfo);
        }
        return $modelService->getLastInsertId();
    }
}
