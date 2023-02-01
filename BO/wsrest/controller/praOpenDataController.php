<?php

require_once('RestController.class.php');

require_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
require_once ITA_BASE_PATH . '/apps/Pratiche/praWsOpenDataAgent.php';

class praOpenDataController extends RestController {

    /**
     * 
     * @param array $params     Codice Procedura
     * @return array            Array associativo
     * esito    boolean
     * return
     *      pratica_dati_simple array
     *
     *
     *
     * 
     *
     */
    public function getDataSet($params) {
        $this->resetLastError();

        if ($params['ente'] == "") {
            $toReturn = array(
                'esito' => false,
                'message' => "codice ditta mancante",
            );
            return $toReturn;
        }

        if ($params['anno'] == "") {
            $toReturn = array(
                'esito' => false,
                'message' => "anno mancante",
            );
            return $toReturn;
        }

        if ($params['includeDataSet'] == 0 && $params['includeMetadata'] == 0) {
            $toReturn = array(
                'esito' => false,
                'message' => "includeDataSet e includeMetadata non possono essere entrambi vuoti",
            );
            return $toReturn;
        }

        if ($params['includeDataSet'] == 1) {
            $retStatusDataSet = $this->GetDataSetData($params);
        }

        if ($params['includeMetadata'] == 1) {
            $restAgent = new praWsOpenDataAgent($params['ente']);
            $retStatusMetaData = $restAgent->GetMetaData($params);
            $retStatusMetaData['MetaData']['Dimensione'] = $this->getSizeDataSet($retStatusDataSet['DataSet']) . " byte";
        }

        $toReturn = array(
            'esito' => $retStatusDataSet['RetValue'],
            'message' => $retStatusDataSet['Message'],
            'return' => array(
                'DataSet' => array(
                    'Name' => $params['dataSetName'],
                    'Data' => $retStatusDataSet['DataSet'],
                    'MetaData' => $retStatusMetaData['MetaData'],
                )
            ),
            'params' => array(),
        );

        if ($params != null) {
            $toReturn['params'] = $params;
        }
        return $toReturn;
    }

    private function GetDataSetData($params) {
        $retStatus = array(
            'RetValue' => true,
            'Message' => "Dati Estratti con Successo",
            'DataSet' => array(),
        );
        $restAgent = new praWsOpenDataAgent($params['ente']);
        try {
            switch ($params['dataSetName']) {
                case 'SuapClassificazione':
                    $retData = $restAgent->getDataSetSuapClassificazione($params['anno']);
                    break;
                case 'SuapEventi':
                    $retData = $restAgent->getDataSetSuapEventi($params['anno']);
                    break;
                case 'SuapPeriodoEventi':
                    $retData = $restAgent->getDataSetSuapPeriodoEventi($params['anno']);
                    break;
                case 'SueNazionalitaProprietari':
                case 'SuapNazionalitaDichiaranti':
                    $retData = $restAgent->getDataSetSuapNazionalitaDichiaranti($params['anno']);
                    break;
                case 'SuapTempiMedi':
                    $retData = $restAgent->getDataSetSuapTempiMedi($params['anno']);
                    break;
                case 'SueClassificazione':
                    $retData = $restAgent->getDataSetSueClassificazione($params['anno']);
                    break;
                case 'SuePeriodoEventi':
                    $retData = $restAgent->getDataSetSuePeriodoEventi($params['anno']);
                    break;
                default:
                    $retStatus = array(
                        'RetValue' => false,
                        'Message' => "Data set " . $params['dataSetName'] . " non trovato",
                    );
                    return $retStatus;
            }
        } catch (Exception $exc) {
            $retStatus = array(
                'RetValue' => false,
                'Message' => $exc->getMessage(),
                'DataSet' => array(),
            );
            return $retStatus;
        }
        $retStatus['DataSet'] = $retData;
        return $retStatus;
    }

    private function getSizeDataSet($dataset) {
        $size = 0;
        foreach ($dataset as $key => $dataSetData) {
            if (is_array($dataSetData)) {
                $size += $this->getSizeDataSet($dataSetData);
            } else {
                $size += strlen((string) $dataSetData);
            }
            $size += strlen((string) $key);
        }
        return $size;
    }

}
