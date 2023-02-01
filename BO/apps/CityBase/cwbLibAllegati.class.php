<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
include_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewerBootstrap.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';

abstract class cwbLibAllegati {

    const DOC_IMPLEMENTATION = "ALFCITY";

    private $errorCode;
    private $errorDescription;
    private $contesto;
    private $documentale; //libreria di interfaccia verso alfresco
    private $docViewer; //viewer
    protected $service; //istanzia il service per il salvataggio 
    protected $libDB_BGD;
    private $validationInfo = array();

    public function __construct($contesto = null) {
        $this->documentale = new itaDocumentale(self::DOC_IMPLEMENTATION);
        $this->docViewer = new itaDocViewerBootstrap();
        $this->libDB_BGD = new cwbLibDB_BGD();
        $this->contesto = $contesto;
        $this->initService();
        $this->postConstruct();
    }

    protected function postConstruct() {
        
    }

    protected function setError($errCode, $errDesc) {
        $this->errorCode = $errCode;
        $this->errorDescription = $errDesc;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }

    public function resetErrorMessage() {
        $this->setError(1, '');
    }

    public function defineRowModel() {
        return array(
            "ID" => null,
            "CONTESTO" => $this->getContesto(),
            "CHIAVE_ESTERNA" => '',
            "RIGA" => null,
            "DESCRIZIONE" => '',
            "ST_EXP_WEB" => null,
            "CORPO" => null,
            "NATURA" => '',
            "NOME" => '',
            "NODE_UUID" => '',
            'CODUTEINS' => '',
            'DATAINSER' => null,
            'TIMEINSER' => null,
            'CODUTE' => '',
            'DATAOPER' => null,
            'TIMEOPER' => null,
            'FLAG_DIS' => null,
            "METADATI" => array()
        );
    }

    public function getContesto() {
        return $this->contesto;
    }

    abstract protected function leggiTipoDocumentoSpecifico($idTipoDocumento);

    protected function aggiornaDocumentoDocumentale($uuid, $name, $content, $datiDoc, $mimeType = null) {
        //se devo aggiornare il binario  
        $status = $this->documentale->queryByUuid($uuid);
        if (!$status) {
            $this->setError($this->documentale->getErrCode(), $this->documentale->getErrMessage());
            return false;
        }
        $oldDatiDoc = $this->estraiRisultato($this->documentale->getResult());

        $isModifiedMetadataOrAspect = $this->verificaAspectMetadata($datiDoc, $oldDatiDoc[0]);
        if ($isModifiedMetadataOrAspect) {
            $tipoDoc = $this->leggiTipoDocumentoSpecifico($datiDoc['IDTIPDOC']);
            if (!$tipoDoc) {
                $this->setError(-1, "Tipo documento non valido per la chiave: " . $datiDoc['IDTIPDOC']);
            }
            $metadati = isset($datiDoc["METADATA"]) ? $datiDoc["METADATA"] : array();
            $aspects = isset($datiDoc["ASPECTS"]) ? $datiDoc["ASPECTS"] : array();

            $status = $this->documentale->updateDocumentMetadata($uuid, $tipoDoc['ALIAS'], $aspects, $metadati, $datiDoc['CODENTE']);
            if (!$status) {
                $this->setError($this->documentale->getErrCode(), $this->documentale->getErrMessage());
                return false;
            }
        }
        //Aggiorna Blob docuemntale 
        if (empty($content) == false && empty($uuid) == false) {
            if (is_null($mimeType)) {
                $mimeType = $this->getMimeType($name, $content);
            }

            //Aggiorno binary
            $status = $this->documentale->updateDocumentContent($uuid, $content, $name, $mimeType);
            if (!$status) {
                $this->setError($this->documentale->getErrCode(), $this->documentale->getErrMessage());
                return false;
            }
        }
        return $status;
    }

    private function verificaAspectMetadata($datiDoc, $oldDatiDoc) {
        foreach ($datiDoc["METADATA"] as $key => $value) {
            //Se è una chiave nuova metadata
            if (isset($oldDatiDoc[$key]) == false) {
                return true;
            } else if ($value !== $oldDatiDoc[$key]) {
                return true;
            }
        }
        if (isset($datiDoc["ASPECTS"])) {
            foreach ($datiDoc["ASPECTS"] as $key => $value) {
                //Se è una chiave nuova aspect
                if (isset($oldDatiDoc[$key]) == false) {
                    return true;
                } else if ($value !== $oldDatiDoc[$key]) {
                    return true;
                }
            }
        }

        return false;
    }

    private function estraiRisultato($queryResult) {
        $results = array();

        if ($queryResult['QUERYRESULT'] && $queryResult['QUERYRESULT'][0]['RESULTS']) {
            $data = $queryResult['QUERYRESULT'][0]['RESULTS'][0]['RESULT'];

            foreach ($data as $keyRec => $record) {
                $results[$keyRec]['UUID'] = $record['UUID'][0]['@textNode'];
                $results[$keyRec]['TIPO_DOC'] = substr($record['TYPE'][0]['@textNode'], strrpos($record['TYPE'][0]['@textNode'], '}') + 1);

                foreach ($record['COLUMNS'][0]['COLUMN'] as $keyMet => $metadato) {
                    $returnValue = null;
                    if ($metadato['ISMULTIVALUE'][0]['@textNode']) {
                        $values = explode("|", $metadato['VALUES'][0]['@textNode']);
                        $value = array();
                        foreach ($values as $value) {
                            $returnValue[] = array('VALORE' => $value);
                        }
                    } else {
                        $returnValue = $metadato['VALUE'][0]['@textNode'];
                    }
                    $results[$keyRec][$metadato['NAME'][0]['@textNode']] = $returnValue;
                }
            }
        }

        return $results;
    }

    protected function inserisciDocumentoDocumentale($name, $content, $datiDoc, $mimeType = null) {
        $tipoDoc = $this->leggiTipoDocumentoSpecifico($datiDoc['IDTIPDOC']);
        if (!$tipoDoc) {
            $this->setError(-1, "Tipo documento non valido per la chiave: " . $datiDoc['IDTIPDOC']);
        }
        $metadati = isset($datiDoc["METADATA"]) ? $datiDoc["METADATA"] : array();
        $aspects = isset($datiDoc["ASPECTS"]) ? $datiDoc["ASPECTS"] : array();

        if (is_null($mimeType)) {
            $mimeType = $this->getMimeType($name, $content);
        }
        
        if (is_resource($content)) {
            rewind($content);
            $strContent = stream_get_contents($content);
        } else {
            $strContent = $content;
        }
        
        $status = $this->documentale->insertDocument($tipoDoc['ALIAS'], $datiDoc['PLACE'], $name, $mimeType, 
                $strContent, $aspects, $metadati, $datiDoc['CODENTE']);
        if (!$status) {
            $this->setError($this->documentale->getErrCode(), $this->documentale->getErrMessage());
            return false;
        }


        return $this->documentale->getResult();
    }

    private function getMimeType($name, $content = null) {
        $tempPath = itaLib::getAppsTempPath();
        if(!is_dir($tempPath)){
            $tempPath = itaLib::createAppsTempPath();
            if (!is_dir($tempPath)) {
                $this->setError(-1, "Creazione cartella ambiente di lavoro temporaneo fallita");
                return false;
            }
        }

        $filename = $tempPath . "/" . md5(rand() * time()) . $name;
        file_put_contents($filename, $content);

        $mimeType = itaMimeTypeUtils::estraiEstensione($filename);
        unlink($filename);
        return $mimeType;
    }

    public function caricaAllegato($chiaveTestata, $riga, $datiAggiuntivi = array(), $startedTransaction = false, $modVis = false) {
        //Verifico le precondition 
        try {
            if (empty($chiaveTestata)) {
                $this->setError(-1, "chiaveTestata:Devi passare un array per caricare il documento ");
                return false;
            }
            return $this->caricaAllegatoCustom($chiaveTestata, $riga, $datiAggiuntivi, $startedTransaction, $modVis);
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
    }

    abstract protected function caricaAllegatoCustom($chiaveTestata, $riga, $datiAggiuntivi, $startedTransaction = false, $modVis = false);

    public function caricaAllegati($chiave = array()) {
        //Verifico le precondition 
        try {
            if (empty($chiave)) {
                $this->setError(-1, "chiave:Devi passare un array per caricare il documento ");
                return false;
            }
            return $this->caricaAllegatiCustom($chiave);
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
    }

    abstract protected function caricaAllegatiCustom($chiave);

    public function scarica($rowModel) {
        $bin = $rowModel['CORPO'];
        
        //Controllo precondition 
        try {
            if (empty($rowModel)) {
                $this->setError(-1, "Devi passare un modello valido");
                return false;
            }
            if (empty($rowModel["NOME"])) {
                $this->setError(-1, "Devi passare un nome valido per effettuare il download del documento");
                return false;
            }
            if ((is_resource($rowModel["CORPO"]) == false && empty($rowModel["CORPO"]) == true) && empty($rowModel["NODE_UUID"])) {
                $this->setError(-1, "Devi passare una resource o un uuid  valido per effettuare il download del documento ");
                return false;
            }
            if (empty($rowModel["NODE_UUID"]) == false) {
                $status = $this->recuperaRisorsaDocumentale($rowModel);                
                if ($status['ESITO'] === false) {
                    return false;
                }
                $bin = $status['CONTENT'];
            }
            
            $tempPath = itaLib::getAppsTempPath();
            if(!is_dir($tempPath)){
                $tempPath = itaLib::createAppsTempPath();
                if (!is_dir($tempPath)) {
                    $this->setError(-1, "Creazione cartella ambiente di lavoro temporaneo fallita");
                    return false;
                }
            }

            $path = $tempPath . "/" . $rowModel["NOME"];
            
            if (file_exists($path)) {
                unlink($path);
            }

            $file = file_put_contents($path, $bin);
            if (!$file) {
                $this->setError(-1, "Risorsa:" . $path . " non scrivibile");
                return false;
            }
            
            Out::openDocument(utiDownload::getOTR($rowModel["NOME"], $path, true));        
            return true;
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
    }

    public function apri($rowModel) {        
        $bin = $rowModel['CORPO'];
        
        //Controllo precondition 
        try {
            if (empty($rowModel)) {
                $this->setError(-1, "Devi passare un modello valido");
                return false;
            }
            if (empty($rowModel["NOME"])) {
                $this->setError(-1, "Devi passare un nome valido per visualizzare il documento");
                return false;
            }
            if ((is_resource($rowModel["CORPO"]) == false && empty($rowModel["CORPO"]) == true) && empty($rowModel["NODE_UUID"])) {
                $this->setError(-1, "Devi passare una resource o un uuid valido per visualizzare anteprima documento");
                return false;
            }
            if (!empty($rowModel["NODE_UUID"])) {
                $status = $this->recuperaRisorsaDocumentale($rowModel);                
                if ($status['ESITO'] === false) {
                    return false;
                }
                $bin = $status['CONTENT'];
            }

            $tempPath = itaLib::getAppsTempPath();
            if(!is_dir($tempPath)){
                $tempPath = itaLib::createAppsTempPath();
                if (!is_dir($tempPath)) {
                    $this->setError(-1, "Creazione cartella ambiente di lavoro temporaneo fallita");
                    return false;
                }
            }

            $path = $tempPath . "/" . $rowModel["NOME"];
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            $file = file_put_contents($path, $bin);
            if (!$file) {
                $this->setError(-1, "Risorsa:" . $path . " non scrivibile");
                return false;
            }

            $this->docViewer->addFile($path);
            $this->docViewer->openViewer(itaDocViewerBootstrap::DOCVIEWER_MODAL);
            return true;
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
    }

    public function salvaAllegato($rowModel, $datiAggiuntivi = array(), $startedTransaction = false) {
        $response = $this->valida($rowModel);
        if ($response) {
            if (empty($rowModel) == false && empty($rowModel["NODE_UUID"]) == true && $rowModel["CORPO"]) {
                $response = $this->spostaDocumentoToDocumentale($rowModel, $datiAggiuntivi, $startedTransaction);
            } else {
                $response = $this->salvaDocumento($rowModel, $datiAggiuntivi, $startedTransaction);
            }
        } else {
            //Compongo la stringa errori 
            $errorMessage = "";
            foreach ($this->validationInfo as $error) {
                $errorMessage .= " - " . $error . '<br/>';
            }
            $this->setError(-1, $errorMessage);
        }
        return $response;
    }

    public function eliminaAllegato($rowModel = array(), $startedTransaction = false) {
        try {
            //Controlla se è relazionato con il record padre 
            $response = true;
            $db = $this->libDB_BGD->getCitywareDB();
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $allegato = $this->service->define($db, $this->getContesto());
            $this->allegatoModelToAllegato($allegato, $rowModel);
            $modelServiceData->addMainRecord($this->getContesto(), $allegato);
            $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_DELETE, get_class($this), $allegato);
            $this->service->setStartedTransaction($startedTransaction);
            $this->service->deleteRecord($db, $this->getContesto(), $modelServiceData->getData(), $recordInfo);
            if ($rowModel["NODE_UUID"]) {
                $status = $this->documentale->deleteDocumentByUUID($rowModel["NODE_UUID"]);
                if (!$status) {
                    $this->setError(-1, $this->documentale->getErrMessage());
                    $status = false;
                }
            }
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
        return $response;
    }

    protected function valida($rowModel) {
        $this->validationInfo = array(); //Array di segnalazione anomalie
        if (!isset($rowModel["CONTESTO"])) {
            $this->addErrorValidazionInfo("E' obbligatorio inserire la tabella di gestione");
        }
        if (is_array($rowModel["CHIAVE_ESTERNA"]) == FALSE || empty($rowModel["CHIAVE_ESTERNA"])) {
            $this->addErrorValidazionInfo("E' obbligatorio gestire le chiavi esterne del record");
        }
        if (!isset($rowModel["DESCRIZIONE"])) {
            $this->addErrorValidazionInfo("E' obbligatorio inserire una descrizone valida");
        }
        if (!isset($rowModel["NOME"])) {
            $this->addErrorValidazionInfo("E' obbligatorio inserire un nome valido");
        }
        if (empty($rowModel["NODE_UUID"]) == true && is_null($rowModel["CORPO"])) {
            $this->addErrorValidazionInfo("Allegato non caricato");
        }
        $this->validaCustom($rowModel["METADATI"]);
        return count($this->validationInfo) === 0;
    }

    protected function addErrorValidazionInfo($errorMessage) {
        $this->validationInfo[] = $errorMessage;
    }

    protected function validaCustom($rowModel) {
        
    }

    public function popolaHeaderCustom($metadati, $nameform) {
        if (is_array($metadati)) {
            foreach ($metadati as $key => $value) {
                $nomeCampo = $nameform . '_HEADER[' . $key . ']';
                Out::valore($nomeCampo, $value);
            }
            $this->popolaHeaderCustomSpecifica($metadati, $nameform);
        }
    }
    
    public abstract function popolaHeaderCustomSpecifica($metadati, $nameform); 
    
    public function caricaNaturaNote($chiave) {
        $filtri = array('TABLENOTE' => $chiave);
        return $this->libDB_BGD->leggiGeneric('BTA_NTNOTE', $filtri, true);
    }

    private function recuperaRisorsaDocumentale($rowModel) {
        $toReturn = array();
        $toReturn['ESITO'] = true;
        $toReturn['CONTENT'] = '';
        
        //Effettuo il download della risorsa da alfresco        
        if ($rowModel["NODE_UUID"]) {
            $status = $this->documentale->contentByUUID($rowModel["NODE_UUID"]);
            if (!$status) {
                $this->setError(-1, $this->documentale->getErrMessage());
                $toReturn['ESITO'] = false;
            } else {
                $content = $this->documentale->getResult();
                if (empty($content)) {
                    $this->setError(-1, "Risorsa: " . $rowModel["NODE_UUID"] . "non trovata");
                    $toReturn['ESITO'] = false;
                }
                $toReturn['CONTENT'] = $content;
            }
        }
        return $toReturn;
    }

    private function salvaDocumento($rowModel, $datiAggiuntivi, $startedTransaction) {
        try {
            $db = $this->libDB_BGD->getCitywareDB();

            $allegato = $this->service->define($db, $this->getContesto());
            $configDocumentale = $this->getConfigDocumentale($datiAggiuntivi, $rowModel);
            $status = $this->aggiornaDocumentoDocumentale($rowModel["NODE_UUID"], $rowModel["NOME"], $rowModel["CORPO"], $configDocumentale, null);
            if (!$status) {
                return false;
            }

            $this->allegatoModelToAllegato($allegato, $rowModel);

            //Caricacamento OldREcord per vedere se sono in modifica 
            $oldCurrentRecord = $this->service->getByPks($db, $this->getContesto(), $allegato);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($this->getContesto(), $allegato);

            $this->service->setStartedTransaction($startedTransaction);
            $curentRecord = $modelServiceData->getData();
            if (!$oldCurrentRecord) {
                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, get_class($this), $allegato);
                $this->service->insertRecord($db, $this->getContesto(), $curentRecord, $recordInfo);
            } else {
                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, get_class($this), $allegato);
                $this->service->updateRecord($db, $this->getContesto(), $curentRecord, $recordInfo, $oldCurrentRecord);
            }

            return true;
        } catch (Exception $ex) {
            $this->setError(-1, $ex->getNativeErroreDesc());
            return false;
        }
    }

    /**
     * Ritorna esito spostamento documento
     * @param <array> $rowModel Array modello deocuemnto
     * @param <array> $datiAggiuntivi Array dei dati da passare al documentale
     * @param <bool> $startedTransaction se true non effettua la commit\Rollback delle transazioni per la connessione corrente  
     * @return <bool> true\false
     */
    protected function spostaDocumentoToDocumentale($rowModel, $datiAggiuntivi, $startedTransaction) {
        $response = true;
        if (empty($rowModel) == false && empty($rowModel["NODE_UUID"]) == true && $rowModel["CORPO"]) {
            $responsePrecondition = $this->preconditionSpostaDocumentoToDocumentale($rowModel, $datiAggiuntivi);
            if ($responsePrecondition) {
                $configDocumentale = $this->getConfigDocumentale($datiAggiuntivi, $rowModel);
                $uuid = $this->inserisciDocumentoDocumentale($rowModel["NOME"], $rowModel["CORPO"], $configDocumentale);
                if ($uuid) {
                    $rowModel["NODE_UUID"] = $uuid;
                    $rowModel["CORPO"] = null;
                    $status = $this->salvaDocumento($rowModel, $datiAggiuntivi, $startedTransaction);
                    if (!$status) {
                        //Messaggio di errore impostato sulla classe padre
                        $response = false;
                    }
                } else {
                    //Messaggio di errore impostato sulla classe padre
                    $response = false;
                }
            } else {
                //Messaggio di errore impostato sulla precondition
                $response = false;
            }
        }
        return $response;
    }

    private function preconditionSpostaDocumentoToDocumentale($rowModel, $datiAggiuntivi) {
//        if (empty($datiAggiuntivi)) {
//            $this->setError(-1, "Non è possibile inserire il documento sul documentale perchè mancano i metadati");
//            return false;
//        } else {
            $configDocumentale = $this->getConfigDocumentale($datiAggiuntivi, $rowModel);
            if (!$configDocumentale) {
                $this->setError(-1, "Per il salvataggio sul documentale devi impostare i valori custom 'getConfigDocumentale' ");
                return false;
            }
//        }
        return true;
    }

    private function initService() {
        $this->service = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($this->getContesto()), true);
    }

    /**
     * Effettua il caricamento degli aspetti per il documentale dl tipo documento
     * @param <string> $idtipdoc tipo documetno
     * @return <array> aspetti da insererire nel documentale
     */
    protected function getAspectsFromTipdoc($idtipdoc = null) {
        $aspects = array();
        if ($idtipdoc) {
            
        }
        $filtri = array();
        $filtri['IDTIPDOC'] = $idtipdoc;
        $aspettiTipdoc = $this->libDB_BGD->leggiBgdAsptdc($filtri, true);

        $filtri = array();
        $filtri['escludeAspetti'] = false;
        $filtri['IDTIPDOC_IN'] = array();

        foreach ($aspettiTipdoc as $key => $aspettoTipdoc) {
            array_push($filtri['IDTIPDOC_IN'], $aspettoTipdoc['IDASPECT']);
        }

        $aspetti = $this->libDB_BGD->leggiBgdTipdoc($filtri, true);

        $aspects = array();
        foreach ($aspetti as $key => $aspetto) {
            $aspects[$aspetto['ALIAS']] = true;
        }

        return $aspects;
    }

    public abstract function getAutorModulo($datiProvenienza = null);
    
    public abstract function getAutorNumero($datiProvenienza = null);
    
    public function getAutorLevel($datiProvenienza = null){
        $authHelper = new cwbAuthHelper();
        return $authHelper->checkAuthAutute(null, $this->getAutorModulo($datiProvenienza), $this->getAutorNumero($datiProvenienza));
    }
}
