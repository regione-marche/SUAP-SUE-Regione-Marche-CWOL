<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DAN.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDBRequest.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

/**
 *
 * Utility Per salvataggio allegati/comunicazioni su documentale
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * 
 */
class cwbLibBGDGestComunicazioni {

    const ERR_INSERT_ALFRESCO = 'Errore inserimento in alfresco';
    const ERR_INSERT_DOCAL = 'errore inserimento documento bgdDocAl';
    const ERR_INSERT_DANANAGRA = 'errore inserimento documento DanAnagra';
    const ERR_DELETE_SPORCHI = 'errore cancellazione allegati sporchi';
    const TABELLA_BGD_DOC_AL = 'BGD_DOC_AL';
    const TABELLA_BGD_DOC_UD = 'BGD_DOC_UD';
    const TABELLA_DAN_ANAGRA = 'DAN_ANAGRA';
    const ASP_COM_ALIAS = 'ASP_COM';
    const ALFRESCO_PLACE = '/app:company_home/cm:cityware/';
    const CHIAVE_GRID_RANDOM = 'RANDOMGUID';

    private $documentale;
    private $modelServiceDocAl;
    private $modelServiceDocUd;
    private $libBgd;
    private $errore;

    public function __construct() {
        $this->libBgd = new cwbLibDB_BGD();
        $this->libDan = new cwdLibDB_DAN();
    }

    public function initData($tipoDocumentale = 'ALFCITY') {
        $this->documentale = new itaDocumentale($tipoDocumentale);
        $this->modelServiceDocAl = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::TABELLA_BGD_DOC_AL), true, true);
        $this->modelServiceDocUd = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName(self::TABELLA_BGD_DOC_AL), true, true);
    }

    /**
     * Inserisce l'unita documentaria (oppure la crea se non esiste) e tutti i suoi allegati 
     * 
     * @param array $recordsDocAl tutti gli allegati (il documento deve essere messo all'interno del record chiamato 'pathAllegato')
     * @param int $idDanAnagra id record dan_anagra padre da aggiornare con progcomu 
     * @param array $recordDocud bgdDocUd da inserire (se progcomu vuoto o $recordDocud vuoto lo crea facendo max+1) 
     * @param array $idsDocalCancellati id docal eliminati a video su cui va fatta cancellazione logica (flag_dis=true)
     * @param String $DBName  nome db defaul cityware
     * @return boolean true/false
     */
    public function insertRecordsUnitaDocumentaria($recordsDocAl, $idDanAnagra, $recordDocud = null, $idsDocalCancellati = array()) {
        $this->setErrore("");

        // apro transazione (lo faccio subito perché mi serve l'oggetto db sulla validazione)
        cwbDBRequest::getInstance()->startManualTransaction(cwbLib::getCitywareConnectionName());
        $db = cwbDBRequest::getInstance()->getCitywareDbSession();

        try {
            // inserisco/aggiorno bgdDocud(testata)
            $progcomu = $this->inserisciAggiornaDocUd($db, $recordDocud);
        } catch (Exception $e) {
            $this->rollBackAzioni($e->getMessage());
            return false;
        }

        $uuidInseriti = array();
        $pathsDaEliminare = array(); // unlink dei documenti salvati che erano appoggiati su disco
        // scorro tutti i record di allegati BgdDocAl
        foreach ($recordsDocAl as $key => $recordBgdDocAl) {
            // lo appoggio cosi faccio l'unset delle proprietà transient che non sono su db solo sulla copia, mantenendo inalterato l'originale
            // sennò se va in rollback l'ultimo record essendo tutto su unica transazione ed avendo già fatto l'unset dei record precedenti, mi perdo i dati
            $recordDocAl = $recordBgdDocAl;

            // TODO se c'è errore le chiavi rimosse sono perse. creare un array con key = RANDOMGUID e valore i campi non su db invece che appoggiarli sul record principale
            // rimuovo la chiave che non è su db
            unset($recordDocAl[self::CHIAVE_GRID_RANDOM]);
            if (!$recordDocAl['PROGCOMU']) {
                // caso in cui aggiungo un allegato e creo al volo anche l'unità documentaria docUd
                $recordDocAl['PROGCOMU'] = $progcomu;
            }

            // valido bgdDocAl
            $errValidazione = $this->erroriValidazioneDocAl($db, $recordDocAl, itaModelService::OPERATION_INSERT);

            if ($errValidazione) {
                // se true ci sono errori di validazione
                $this->rollBackAzioni("Record " . ($key + 1) . " " . $errValidazione, $uuidInseriti);
                return false;
            }

            // se uuid è vuoto faccio la insert in alfresco
            if ($recordDocAl['UUID_DOC'] == null) {
                try {
                    // inserisco su Documentale
                    $docResult = $this->inserisciDocumentoDocumentale($recordDocAl, $idDanAnagra);
                    if ($docResult) {
                        // se va a buon fine imposto l'uuid generato sul record bgdDocAl e lo salvo sulla 
                        // lista degli uuid inseriti (tengo traccia di tutti quelli inseriti nel caso vada fatto roolback)
                        $uuidInseriti[] = $this->documentale->getResult();
                        $recordDocAl['UUID_DOC'] = $this->documentale->getResult();
                        $pathsDaEliminare[] = $recordDocAl['pathAllegato']; // salvo i path per fare l'unlink a fine transazione
                        unset($recordDocAl['pathAllegato']); // faccio unset perche' non e' su db  (proprieta transient)                      
                    } else {
                        // errore inserimento alfresco   
                        $msg = '';
                        if ($this->documentale->getErrMessage() != null && $this->documentale->getErrMessage() != 'null') {
                            $msg = $this->documentale->getErrMessage();
                        }
                        $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_ALFRESCO . " " . $msg, $uuidInseriti);
                        return false;
                    }
                } catch (Exception $e) {
                    $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_ALFRESCO . " " . $e->getMessage(), $uuidInseriti);
                    return false;
                }
            } else if ($recordDocAl['pathAllegato']) {
                // se uuid è popolato ma stato_doc = 0 (in lavorazione) posso modificare il binario, se è stato modificato
                $docResult = $this->aggiornaDocumentoDocumentale($recordDocAl, $idDanAnagra);
                if ($docResult) {
                    $pathsDaEliminare[] = $recordDocAl['pathAllegato']; // salvo i path per fare l'unlink a fine transazione
                    unset($recordDocAl['pathAllegato']); // faccio unset perche' non e' su db  (proprieta transient)                      
                } else {
                    // errore inserimento alfresco   
                    $msg = '';
                    if ($this->documentale->getErrMessage() != null && $this->documentale->getErrMessage() != 'null') {
                        $msg = $this->documentale->getErrMessage();
                    }
                    $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_ALFRESCO . " " . $msg, $uuidInseriti);
                    return false;
                }
            }
            try {
                // inserisco il documento su bgdDocAl
                if (!$this->inserisciDocAl($db, $recordDocAl)) {
                    // se id vuoto c'è errore in inserimento
                    $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_DOCAL, $uuidInseriti);
                    return false;
                }
            } catch (ItaException $e) {
                $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_DOCAL . " " . $e->getMessage(), $uuidInseriti);
                return false;
            } catch (Exception $e) {
                $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_DOCAL . " " . $e->getMessage(), $uuidInseriti);
                return false;
            }
        }
        try {
            // cancellazione logica degli allegati tolti a video che erano su db
            $this->deleteLogicoAllegati($db, $idsDocalCancellati);
        } catch (Exception $e) {
            $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_DELETE_SPORCHI . " " . $e->getMessage(), $uuidInseriti);
            return false;
        }

        try {
            // Aggiorna il campo progcomu su dan_anagra per agganciare l'unita documentaria
            $this->updateDanAnagra($db, $progcomu, $idDanAnagra);
        } catch (Exception $e) {
            $this->rollBackAzioni("Record " . ($key + 1) . " " . self::ERR_INSERT_DANANAGRA . " " . $e->getMessage(), $uuidInseriti);
            return false;
        }

        // commit
        cwbDBRequest::getInstance()->commitManualTransaction();

        // una volta sicuro di aver salvato tutto, cancello dal disco i file appoggiati sulla temp
        foreach ($pathsDaEliminare as $pathToDelete) {
            unlink($pathToDelete);
        }

        return true;
    }

    /**
     * La cancellazione di un bgd_docal è solo logica (disabilitato)
     * @param String $iddocal
     * @param type $db
     */
    public function deleteAllegato($iddocal, $db = null) {
        if (!$db) {
            $db = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        }
        $value['IDDOCAL'] = $iddocal;
        $value['FLAG_DIS'] = 1; // disabilito record per cancellarlo
        $value['CODUTEANN'] = cwbParGen::getUtente();
        $value['DATAOPERANN'] = date('Ymd');
        $value['TIMEANN'] = date('H:i:s');

        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
        $modelServiceData->addMainRecord(self::TABELLA_BGD_DOC_AL, $value);
        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, "cwbLibBGDGestComunicazioni", $value);
        $this->modelServiceDocAl->updateRecord($db, self::TABELLA_BGD_DOC_AL, $modelServiceData->getData(), $recordInfo);
    }

    public function deleteUnitaDocumentaria($progcomu) {
        $this->libBgd->cancellaAllegatiDocAl($progcomu);
    }

    /**
     * Cancella BGD_DOC_AL per $progcomu e not in($idDocAlInseriti).
     * Serve per cancellare tutti gli allegati di bgd_doc_al su db con progcomu= $progcomu e che non sono presenti nella grid in grafica ($idDocAlInseriti)
     */
    private function deleteLogicoAllegati($db, $idsDocalCancellati) {
        if ($idsDocalCancellati) {
            foreach ($idsDocalCancellati as $idToDelete) {
                // scorro gli id da cancellare e li aggiorno a disabilitato
                $this->deleteAllegato($idToDelete, $db);
            }
        }
    }

    /**
     * inserisce in bgdDocAl
     * @param type $db
     * @param array $recordDocAl
     * @return int chiave inserita/aggiornata
     */
    private function inserisciDocAl($db, $recordDocAl) {
        $modelServiceDataAl = new itaModelServiceData(new cwbModelHelper());
        $modelServiceDataAl->addMainRecord(self::TABELLA_BGD_DOC_AL, $recordDocAl);
        if ($recordDocAl['IDDOCAL']) {
            // se la chiave è popolata vado in aggiorna
            $this->modelServiceDocAl->updateRecord($db, self::TABELLA_BGD_DOC_AL, $modelServiceDataAl->getData(), itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, 'cwbLibBGDGestComunicazioni', $modelServiceDataAl->getData()));

            return $recordDocAl['IDDOCAL'];
        } else {
            // senno' in insert
            $this->modelServiceDocAl->insertRecord($db, self::TABELLA_BGD_DOC_AL, $modelServiceDataAl->getData(), itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, 'cwbLibBGDGestComunicazioni', $modelServiceDataAl->getData()));
            return $this->modelServiceDocAl->getLastInsertId();
        }
    }

    /**
     * Aggiorna il campo progcomu su dan_anagra per agganciare l'unita documentaria
     * @param type $db
     * @param int $progcomu
     * @param int $idDanAnagra
     */
    private function updateDanAnagra($db, $progcomu, $idDanAnagra) {
        $modelServiceDataAl = new itaModelServiceData(new cwbModelHelper());
        $modelServiceDataAl->addMainRecord(self::TABELLA_DAN_ANAGRA, array('PROGCOMU' => $progcomu, 'PROGSOGG' => $idDanAnagra));

        $this->modelServiceDocAl->updateRecord($db, self::TABELLA_DAN_ANAGRA, $modelServiceDataAl->getData(), itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, 'cwbLibBGDGestComunicazioni', $modelServiceDataAl->getData()));
    }

    private function inserisciAggiornaDocUd($db, $recordDocud = null) {
        // inserisco/aggiorno la testata
        if (!$recordDocud) {
            // se null o vuoto lo inizializzo ad array
            $recordDocud = array();
        }

        if (!$recordDocud['PROGCOMU']) {
            // se progcomu vuoto lo calcolo con max+1 e imposto operazione insert
            // TODO valutare conflitti di codice in caso di chiamate ravvicinate
            $recordDocud['PROGCOMU'] = $this->libBgd->calcolaMaxProgComuBgdDocUd();

            $operazione = "insertRecord";
        } else {
            //se progcomu c'e', imposto operazione update
            $operazione = "updateRecord";
        }

        $modelServiceDataUd = new itaModelServiceData(new cwbModelHelper());

        $modelServiceDataUd->addMainRecord(self::TABELLA_BGD_DOC_UD, $recordDocud);
        $recordInfoUd = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, 'cwbLibBGDGestComunicazioni', $modelServiceDataUd->getData());

        $this->modelServiceDocUd->$operazione($db, self::TABELLA_BGD_DOC_UD, $modelServiceDataUd->getData(), $recordInfoUd);

        return $recordDocud['PROGCOMU'];
    }

    private function getMetadatiDocumentale($tipoDoc, $idDanAnagra, $recordDocAl) {
        // trovo tutti i metadati del documento
        $listBgdMetDoc = $this->libBgd->leggiBgdMetdoc(array('IDTIPDOC' => $tipoDoc['IDTIPDOC']));

        // trovo tutti i metadati dell'aspetto asp_com
        $listAspcom = $this->libBgd->leggiBgdMetdoc(array('ALIAS' => self::ASP_COM_ALIAS));

        $libDan = new cwdLibDB_DAN();
        $record = $libDan->leggiDanAnagraChiave($idDanAnagra);

        // creo il dizionario Ext
        $dizionarioExt = array('progsogg' => $idDanAnagra,
            'cognome' => $record['COGNOME'],
            'nome' => $record['NOME'],
            'com_area_cityware' => 'D',
            'com_modulo_cityware' => 'AN',
            'com_descrizione' => $recordDocAl['NOME_FILE'],
            'com_nomefile' => $recordDocAl['NOME_FILE'],
            'com_ente' => ''
        );

        $libBta = new cwbLibDB_BTA();

        // creo il dizionario con le giuste chiavi per leggere i metadati (vedi commento su getMetadata per info)
        $dizionario = array(
            'MAIN' => $recordDocAl,
            'EXT' => $dizionarioExt,
            'BTA_TIPCOM' => $libBta->leggiBtaTipcomChiave($recordDocAl['TIPO_COM'])
        );

        // elaboro i metadati
        return $this->documentale->getMetadata(array_merge($listBgdMetDoc, $listAspcom), $dizionario);
    }

    /*
     * inserisce su documentale
     */

    private function inserisciDocumentoDocumentale(&$recordDocAl, $idDanAnagra) {
        $fileName = $recordDocAl['NOME_FILE']; 
        $content = file_get_contents($recordDocAl['pathAllegato']); // proprietà transient da inserire nel record bgdDocAl
        // trovo il tipo documento associato alla comunicazione
        $tipoDoc = $this->libBgd->leggiTipoDocumentoDaTipCom($recordDocAl['TIPO_COM']);

        $metadati = $this->getMetadatiDocumentale($tipoDoc, $idDanAnagra, $recordDocAl);

        if (!$recordDocAl['MIMETYPE']) {
            $recordDocAl['MIMETYPE'] = itaMimeTypeUtils::estraiEstensione($fileName);
        }
        $mimeType = $recordDocAl['MIMETYPE'];

        // aggiungo solo aspetto dati comuni
        $aspects = array(('HAS_ASP_' . self::ASP_COM_ALIAS) => true);

        // insert in documentale
        $place = $this->getAlfrescoPlace($metadati['com_area_cityware'], $metadati['com_modulo_cityware']);
        return $this->documentale->insertDocument($tipoDoc['ALIAS'], $place, $fileName, $mimeType, $content, $aspects, $metadati);
    }

    private function aggiornaDocumentoDocumentale($recordDocAl, $idDanAnagra) {
        $tipoDoc = $this->libBgd->leggiTipoDocumentoDaTipCom($recordDocAl['TIPO_COM']);

        $fileName = $recordDocAl['NOME_FILE'];
        $content = file_get_contents($recordDocAl['pathAllegato']); // proprietà transient da inserire nel record bgdDocAl
        $mimeType = itaMimeTypeUtils::estraiEstensione($fileName);
        $recordDocAl['MIMETYPE'] = $mimeType;
        //aggiorno metadati
        $propsToEdit = $this->getMetadatiDocumentale($tipoDoc, $idDanAnagra, $recordDocAl);

        $this->documentale->updateDocumentMetadata($recordDocAl['UUID_DOC'], $tipoDoc['ALIAS'], array(), $propsToEdit);
        // update content
        return $this->documentale->updateDocumentContent($recordDocAl['UUID_DOC'], $content, $fileName, $recordDocAl['MIMETYPE']);
    }

    private function getAlfrescoPlace($area, $modulo) {
        $place = self::ALFRESCO_PLACE . 'cm:ENTE_' . cwbParGen::getCodente() . '/cm:AOO_' . cwbParGen::getCodAoo();
        if ($area == 'D') {
            $place .= '/cm:people';
        } else if ($area == 'A') {
            $place.='/cm:media';
            if ($modulo == 'PI') {
                $place.='/cm:protocollo';
            }
        }

        return $place;
    }

    /**
     * Torna il content di bgddocal leggendolo dal documentale
     * @param String $uuid uuid documentale
     * @return content binario
     */
    public function getAllegato($uuid) {
        $this->setErrore("");

        if ($this->documentale->contentByUUID($uuid)) {
            return $this->documentale->getResult();
        } else {
            $this->setErrore($this->documentale->getErrMessage());
            return false;
        }
    }

    private function rollBackAzioni($err, $uuidInseriti = null) {
        cwbDBRequest::getInstance()->rollBackManualTransaction();
        $this->setErrore($err);
        // se faccio rollback del db devo anche cancellare tutti i documenti inseriti su alfresco
        if ($uuidInseriti) {
            // scorro tutti gli uuid inseriti e li elimino
            foreach ($uuidInseriti as $uuid) {
                $this->documentale->deleteDocumentByUUID($uuid);
            }
        }
    }

    // esegue validazione bgdDocAl e torna true se non ci sono errori oppure false.
    // metodo pubblico chiamabile dall'esterno che risponde utilizzando setErrore se c'è errore
    public function validaBgdDocAl($db, $recordDocAl, $operation) {
        $this->setErrore("");

        $errore = $this->erroriValidazioneDocAl($db, $recordDocAl, $operation);

        if ($errore) {
            $this->setErrore($errore);
            return false;
        } else {
            return true;
        }
    }

    // esegue validazione bgdDocAl e torna false se non ci sono errori, oppure il messaggio di errore.
    // metodo privato utilizzato all'interno dell'utils
    private function erroriValidazioneDocAl($db, $recordDocAl, $operation) {
        $modelServiceDataAl = new itaModelServiceData(new cwbModelHelper());
        $modelServiceDataAl->addMainRecord(self::TABELLA_BGD_DOC_AL, $recordDocAl);

        if (itaModelService::OPERATION_INSERT !== $operation) {
            $oldCurrentRecord = $this->modelServiceDocAl->getByPks($db, self::TABELLA_BGD_DOC_AL, $recordDocAl);
        }

        $validationInfo = $this->modelServiceDocAl->validate($db, self::TABELLA_BGD_DOC_AL, $recordDocAl, $operation, $oldCurrentRecord);

        if (empty($validationInfo)) {
            // non ci sono errori di validazione            
            return false;
        } else {
            foreach ($validationInfo as $currentInfo) {
                if ($currentInfo['level'] === itaModelValidator::LEVEL_ERROR) {
                    $msg .= (strtolower($tableName) != strtolower($this->TABLE_NAME) ? "Tabella: $tableName - " : "");
                    $msg .= ($line != 0 ? "Riga: $line - " : "");
                    $msg .= $currentInfo['msg'] . '<br/>';
                }
            }

            return $msg;
        }
    }

    function getErrore() {
        return $this->errore;
    }

    function setErrore($errore) {
        $this->errore = $errore;
    }

}

?>