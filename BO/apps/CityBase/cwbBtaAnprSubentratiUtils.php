<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DAN.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBtaAnprSubentratiUtils() {
    $cwbBtaAnprSubentratiUtils = new cwbBtaAnprSubentratiUtils();
    $cwbBtaAnprSubentratiUtils->parseEvent();
    return;
}

class cwbBtaAnprSubentratiUtils extends cwbBpaGenTab {

    protected function initVars() {
        $this->searchOpenElenco = false;
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->libDB_DAN = new cwdLibDB_DAN();
        $this->elencaAutoAudit = false;
        $this->TABLE_NAME = 'BTA_ANPR_SUBENTRATI';
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Import':
                        $this->ImportFile();
                        break;
                }
                break;
            case 'returnFileElab':
                $this->elabFile($_POST['uploadedFile']);
                break;
        }
    }

    protected function ImportFile() {
        $model = "utiUploadDiag";
        itaLib::openForm($model);
        $objForm = itaModel::getInstance($model, $model);
        $objForm->setEvent('openform');
        $objForm->setReturnEvent('returnFileElab');
        $objForm->setReturnModel($this->nameForm);
        $objForm->parseEvent();
    }

    private function elabFile($sourceFile) {
        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
        if (strtolower($ext) != "xlsx") {
            Out::msgStop("Attenzione!", "L'estensione del file selezionato non corrisponde ad un .xlsx");
            return;
        } else {
            $document = itaDocumentFactory::getDocument('XLSX');
            if (!$document->loadContent($sourceFile)) {
                return false;
            } else {
                $arrayXLSX = $document->getSheetRowsArray($sheet, true);
                $esito = $this->bta_anpr_subentrati($arrayXLSX);
                if ($esito == true) {
                    Out::msgInfo("Allineamento tabella Comuni subentrati", "Operazione completata!");
                }
            }
        }
    }

    public function bta_anpr_subentrati($arrayData) {

        cwbDBRequest::getInstance()->startManualTransaction();  // cwbDBRequest::getInstance()->startManualTransaction($this->DBName);
        try {
            $sql = ' DELETE FROM BTA_ANPR_SUBENTRATI ';
            $esito = ItaDB::DBSQLExec($this->libDB_DAN->getCitywareDB(), $sql);
            cwbDBRequest::getInstance()->commitManualTransaction();
        } catch (Exception $exc) { //Entra qua in caso di crash dell'applicativo
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            //out::msgInfo('Attenzione', " Errore: " . $exc->getNativeErroreDesc() . $exc->getTraceAsString());
        } catch (Exception $ex) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            //out::msgInfo('Attenzione', ' Errore: ' . $e->getNativeErrorCode() . ':' . $e->getNativeErroreDesc());
        }


        $filtriVerif = array();
        $filtriVerif['IDANPRSUBENTRATI'] = 1;
        $verifRecord = $this->libDB_BTA->leggiBtaANPRSubentrati($filtriVerif, false, true);
        if (!cwbLibCheckInput::IsNBZ($verifRecord['IDANPRSUBENTRATI'])) {
            Out::msgStop('Importazione comuni subentrati', 'Attenzione! Per poter proseguire è necessario svuotare prima la tabella BTA_ANPR_SUBENTRATI!');
            return false;
        } else {
            $row = '';
            $row = $this->libDB_DAN->defineFromSqlClass($this->TABLE_NAME);

            array_shift($arrayData);
            foreach ($arrayData as $value) {
                $row['IDANPRSUBENTRATI'] = cwbLibCalcoli::trovaProgressivo('IDANPRSUBENTRATI', $this->TABLE_NAME);
                $row['ISTNAZPRO'] = substr($value['A'], 0, 3);
                $row['ISTLOCAL'] = substr($value['A'], 3, 3);
                $row['CODBELFI'] = $value['B'];
                $row['COMUNE'] = utf8_decode($value['C']);
                $row['DATASUBENTRO'] = cwbLibCalcoli::trasformaDataTime($value['G'], 'Y-m-d', 'd/m/Y');
                $filtri = array();
                $filtri['COD_PR_IST'] = $value['D'];
                $rowProvi = $this->libDB_BTA->leggiBtaProvi($filtri);
                if (!cwbLibCheckInput::IsNBZ($rowProvi['PROVINCIA'])) {
                    $row['PROVINCIA'] = $rowProvi['PROVINCIA'];
                }

                $this->initModel($modelService, $this->TABLE_NAME, $modelServiceData);
                $modelServiceData->addMainRecord($this->TABLE_NAME, $row);
                $modelService->insertRecord($this->MAIN_DB, $this->TABLE_NAME, $modelServiceData->getData(), '$cwbLibBtaLocalita: Errore Insert in' . $this->tableName);
            }
        }
        return true;
    }

    protected function initModel(&$modelService, $tableName, &$modelServiceData) {
        if (!isset($modelService)) {
            $transactionStarted = cwbDBRequest::getInstance()->getStartedTransaction() ? true : false;
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($this->TABLE_NAME), true, $transactionStarted);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        }
    }

}

?>
