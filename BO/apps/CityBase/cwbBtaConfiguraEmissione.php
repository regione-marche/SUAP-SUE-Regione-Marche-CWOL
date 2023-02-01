<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaHelper.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';

function cwbBtaConfiguraEmissione() {
    $cwbBtaConfiguraEmissione = new cwbBtaConfiguraEmissione();
    $cwbBtaConfiguraEmissione->parseEvent();
    return;
}

class cwbBtaConfiguraEmissione extends itaFrontControllerCW {

    protected function postItaFrontControllerCostruct() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->apriForm();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->close = true;
                        cwbParGen::removeFormSessionVars($this->nameForm);
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->aggiungi();

                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPOSCADENZA':
                        $this->selectTipoScadenza($_POST[$this->nameForm . '_TIPOSCADENZA']);
                        break;
                }
                break;
        }
    }

    private function apriForm() {
        $this->initComboIntermediario();
        $this->initComboTipoServizio();
        Out::valore($this->nameForm . '_ANNO', date("Y"));
    }

    private function initComboIntermediario() {
        Out::select($this->nameForm . '_INTERMEDIARIO', 1, null, 1, "Selezionare..");

        foreach (cwbPagoPaHelper::$mappingIntermediari as $key => $value) {
            Out::select($this->nameForm . '_INTERMEDIARIO', 1, $key, 0, $value['DESCR']);
        }
    }

    private function initComboTipoServizio() {
        Out::select($this->nameForm . '_TIPOSCADENZA', 1, null, 1, "Selezionare..");
        foreach (cwbPagoPaHelper::$mappingServizi as $key => $value) {
            Out::select($this->nameForm . '_TIPOSCADENZA', 1, $key, 0, $value['DESCR']);
        }
    }

    private function selectTipoScadenza($select) {
        if (!$select) {
            Out::valore($this->nameForm . '_DESCRIZIONE', "");
        } else {
            $info = cwbPagoPaHelper::$mappingServizi[$select];
            Out::valore($this->nameForm . '_DESCRIZIONE', $info['DESCR']);
        }
    }

    private function aggiungi() {
        $info = cwbPagoPaHelper::$mappingServizi[$_POST[$this->nameForm . '_TIPOSCADENZA']];
        $codtipscad = $info['CODTIPSCAD'];
        $subtipscad = $info['SUBTIPSCAD'];
        $codmodulo = $info['CODMODULO'];
        $descrizione = $_POST[$this->nameForm . '_DESCRIZIONE'];
        $intermediario = $_POST[$this->nameForm . '_INTERMEDIARIO'];
        $anno = $_POST[$this->nameForm . '_ANNO'];
        $codServizio = $_POST[$this->nameForm . '_CODSERVIZIO'];
        $tipoRifcred = $_POST[$this->nameForm . '_TIPORIFCRED'];

        $esito = '<div style="background-color:white">';

        // BOR_IDBOL
        $libBor = new cwbLibDB_BOR();
        $borIdbol = $libBor->leggiBorIdbol(array('IDBOL_SERE' => $codtipscad));
        if (!$borIdbol) {
            $borIdbol = array(
                'IDBOL_SERE' => $codtipscad,
                'DES_SEREMI' => $descrizione,
                'CODMODULO' => $codmodulo
            );
            $this->insertUpdateRecord($borIdbol, "BOR_IDBOL");
            $esito .= '<span style="color:#33cc00">Tabella BOR_IDBOL inserita</span><br>';
        } else {
            $esito .= '<span style="color:#b3b300">Tabella BOR_IDBOL già presente</span><br>';
        }

        // BTA_EMI
        $libBta = new cwbLibDB_BTA();
        $filters = array(
            'IDBOL_SERE' => $codtipscad,
            'NUMEMI' => 1,
            'ANNOEMI' => $anno
        );
        $btaEmi = $libBta->leggiBtaEmi($filters);
        if (!$btaEmi) {
            $btaEmi = array(
                'IDBOL_SERE' => $codtipscad,
                'DES_GE60' => $descrizione,
                'NUMEMI' => 1,
                'ANNOEMI' => $anno
            );
            $this->insertUpdateRecord($btaEmi, "BTA_EMI");
            $esito .= '<span style="color:#33cc00">Tabella BTA_EMI inserita</span> <br>';
        } else {
            $esito .= '<span style="color:#b3b300">Tabella BTA_EMI già presente</span> <br>';
        }

        // BTA_SERVREND
        $filters = array(
            'IDBOL_SERE' => $codtipscad,
            'NUMEMI' => 1,
            'ANNOEMI' => $anno,
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad
        );
        $idServrend = null;
        $btaServrend = $libBta->leggiBtaServrendTabella($filters, false);
        if (!$btaServrend) {
            $progr = cwbLibCalcoli::trovaProgressivo("PROGKEYTAB", BTA_SERVREND);
            $btaServrend = array(
                'PROGKEYTAB' => $progr,
                'IDBOL_SERE' => $codtipscad,
                'CODTIPSCAD' => $codtipscad,
                'SUBTIPSCAD' => $subtipscad,
                'NUMEMI' => 1,
                'ANNOEMI' => $anno
            );
            $idServrend = $this->insertUpdateRecord($btaServrend, "BTA_SERVREND");
            $esito .= '<span style="color:#33cc00">Tabella BTA_SERVREND inserita</span> <br>';
        } else {
            $idServrend = $btaServrend['PROGKEYTAB'];
            $esito .= '<span style="color:#b3b300">Tabella BTA_SERVREND già presente</span> <br>';
        }

        // BTA_SERVRENDPPA
        if ($idServrend) {
            $btaServrendppa = $libBta->leggiBtaServrendppaChiave($idServrend);
            if (!$btaServrendppa) {
                $btaServrendppa = array(
                    'IDSERVREND' => $idServrend,
                    'INTERMEDIARIO' => $intermediario,
                    'CODSERVIZIO' => $codServizio,
                    'TIPORIFCRED' => $tipoRifcred
                );
                $this->insertUpdateRecord($btaServrendppa, "BTA_SERVRENDPPA");
                $esito .= '<span style="color:#33cc00">Tabella BTA_SERVRENDPPA inserita</span> <br>';
            } else {
                $esito .= '<span style="color:#b3b300">Tabella BTA_SERVRENDPPA già presente</span> <br>';
            }
        } else {
            $esito .= '<span style="color:#cc3300">Errore reperimento id BTA_SERVREND</span> <br>';
        }
        $esito .= '</div>';
        Out::msgInfo("Esito", $esito);
    }

    public function insertUpdateRecord($toInsert, $tableName, $insert = true, $startedTransaction = false) {
        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord($tableName, $toInsert);
        $recordInfo = itaModelHelper::impostaRecordInfo(($insert ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE), 'cwbPagoPaMaster', $toInsert);
        if ($insert) {
            $modelService->insertRecord($this->MAIN_DB, $tableName, $modelServiceData->getData(), $recordInfo);
        } else {
            $modelService->updateRecord($this->MAIN_DB, $tableName, $modelServiceData->getData(), $recordInfo);
        }
        return $modelService->getLastInsertId();
    }

}
