<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeMailParamsLib.class.php';

class cwbLibSoggetti {
    
    /**
     * Apre finestra per invio estratto conto C/F
     * @param array $soggetto Chiave soggetto     
     * @param string $returnModel model di ritorno
     * @param string $returnEvent evento di ritorno
     * @param int $tipoGestione 0=Puntuale 1=Cumulativa
     * @return type
     */
    public function apriInvioEstrattoContoCF($soggetto, $returnModel, $returnModelOrig, $returnEvent, $tipoGestione = 0) {
        // Imposta parametri di default
        $notSaveDocinv = false;
        if (isSet($soggetto['NOT_SAVE_DOCINV'])) {
            $notSaveDocinv = $soggetto['NOT_SAVE_DOCINV'];
            unset($soggetto['NOT_SAVE_DOCINV']);
        }
        $parametriDefault = array();
        $parametriDefault['DATI_SOGG'] = $soggetto;
        $parametriDefault['INDIR_DEST'] = $this->getIndirizzoEmailDestinatario($soggetto['PROGSOGG'], $soggetto['FLAG_DINV']);
        $elementiMail = $this->getElementiTemplateMail('ESTR_CONTO_CF', $soggetto);
        $parametriDefault['MSG_OGGETTO'] = $elementiMail['OGGETTOMAIL'];
        $parametriDefault['MSG_CORPO'] = $elementiMail['BODYMAIL'];                
        $parametriDefault['MAILPARAMS'] = $elementiMail['MAILPARAMS'];
        $parametriDefault['INDIR_SPED'] = $this->getIndirizziEmailSped($elementiMail['MAILPARAMS']);
        $parametriDefault['OPZ_ZIP'] = 1;       
        $parametriDefault['TIPO_GESTIONE'] = $tipoGestione;
        $parametriDefault['NOT_SAVE_DOCINV'] = $notSaveDocinv;
        
        // Apre finestra gestione parametri
        $model = 'cwfInvioEstrattoContoCF';                            
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model, $model);
        if (!$formObj) {
            Out::msgStop("Errore", "apertura dettaglio fallita");
            return;
        }
        $formObj->setParametriDefault($parametriDefault);
        $formObj->setReturnModel($returnModel);        
        $formObj->setReturnModelOrig($returnModelOrig); // Per poter gestire l'Alias       
        $formObj->setReturnEvent($returnEvent);                
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }
    
    /**
     * Riscrive Testo Email per Invio Comunicazione se varia la Natura della Comunicazione
     * @param array $soggetto Chiave soggetto     
     * @return type
     */
    public function elaboraTestoMailEstrattoContoCF($soggetto) {
        // Rimanda tutto il necessario ma in realta' servono solamente:
        //  $elementiMail['OGGETTOMAIL'] -> $parametriDefault['MSG_OGGETTO']
        //  $elementiMail['BODYMAIL']    -> $parametriDefault['MSG_CORPO']
        $elementiMail = $this->getElementiTemplateMail('ESTR_CONTO_CF', $soggetto);
        return $elementiMail;
    }
    
    /*
     * Regola per reperimento indirizzo destinatario:
	1.  se esiste un recapito con FTA_RECAP.TIPO_RECAP= 1 (Sede amministrativa) 
            ed il campo FTA_RECAP.E_MAIL valorizzato  viene preso per primo questo 
            ed indicato come reprimento (da Sede Amministrativa)
	2.  se tipo persona fisica (FLAG_DINV = 0)  prendiamo FTA_RESID.EMAIL 
            e poi  FTA_RESID.EMAIL_PEC
            se tipo persona fisica (FLAG_DINV = 1)  
            prendiamo FTA_INDDIT.EMAIL e poi  FTA_RESID.EMAIL_PEC
     */
    public function getIndirizzoEmailDestinatario($PROGSOGG, $FLAG_DINV) {
        $toReturn = array();
        $indirizzi = array();
        $indirizzoDefault = null;
        
        $libDB_BTA = $this->getLibBta();        
               
        // Legge recapito        
        $filtri = array('PROGSOGG' => $PROGSOGG, 'TIPO_RECAP' => 1);
        $recap = $libDB_BTA->leggiGeneric('FTA_RECAP', $filtri, false);
        if (is_array($recap) && strlen(trim($recap['E_MAIL'])) > 0) {
            if ($indirizzoDefault === null) {
                $indirizzoDefault = $recap['E_MAIL'];
            }                
            $indirizzi[] = array('ID' => $recap['E_MAIL'], 'DES' => $recap['E_MAIL'] . ' (Da sede amministrativa)');                
        }        
        
        // Controlla se ditta individuale
        if ($FLAG_DINV == 1) {
            $tabellaResid = 'FTA_INDDIT';
        } else {
            $tabellaResid = 'FTA_RESID';
        }
        
        // Legge residenza        
        $filtri = array('PROGSOGG' => $PROGSOGG, 'DATAFINE_null' => true);
        $resid = $libDB_BTA->leggiGeneric($tabellaResid, $filtri, false);
        if (is_array($resid) && strlen(trim($resid['E_MAIL'])) > 0) {
            if ($indirizzoDefault === null) {
                $indirizzoDefault = $resid['E_MAIL'];
            }            
            $indirizzi[] = array('ID' => $resid['E_MAIL'], 'DES' => $resid['E_MAIL'] . ' (Da residenza Indirizzo Email)');                
        }
        if (is_array($resid) && strlen(trim($resid['E_MAIL_PEC'])) > 0) {
            if ($indirizzoDefault === null) {
                $indirizzoDefault = $resid['E_MAIL_PEC'];
            }            
            $indirizzi[] = array('ID' => $resid['E_MAIL_PEC'], 'DES' => $resid['E_MAIL_PEC'] . ' (Da residenza Cliente Email PEC)');                
        }
              
        // Aggiungere opzione "nessuna delle precedenti" per inserimento manuale indirizzo
        $indirizzi[] = array('ID' => 'CUSTOM', 'DES' => '*** NESSUNA DELLE PRECEDENTI ***');                
        
        $toReturn['TIPO_SPED'] = $resid['F_SPEDIZ'] == 0 ? 0 : 1;        
        $toReturn['DEFAULT'] = $indirizzoDefault;
        $toReturn['INDIRIZZI'] = $indirizzi;
        return $toReturn;
    }
    
    /**
     * Invia estratto conto CF
     * @param array $params Parametri:
     *      - DATI_SOGG: Array contenente i campi che identificano ikl soggetto (PROGSOGG, FLAG_DINV, NATURA_ALLEG, CONTENUTO)
     *      - INDIR_DEST: indirizzo destinatario      
     *      - MSG_OGGETTO: Oggetto email
     *      - MSG_CORPO: Corpo email
     *      - OPZ_ZIP: Se 1 = Allega uno zip con all'interno tutti gli allegati
     *      - OPZ_TIPOINVIO: 
     *               1 = Mist, elenco completo in funzione dei dati del destinatario
     *               2 = Cartacea, solo ai destinatari privi di indirizzo email
     *               3 = Invia solo ai destinatari con indirizzo email
     *               4 = Cartacea, elenco completo
     *      - MAILPARAMS: Array parametri mail     
     * @listFatture lista delle fatture
     * @return array Esito
     *      - CODICE: codice di errore
     *              0 = Esito positivo
     *              Codici di errore per invio mail:
     *              1 = Nessun indirizzo destinatario specificato (se invio puntuale)     
     *              2 = Account posta elettronica non configurato
     *              3 = Impossibile accedere alle funzioni dell'account
     *              4 = Impossibile creare un nuovo messaggio in uscita
     *              5 = Errore invio mail
     *              6 = Errore lettura allegati
     *              Codici di errore per invio cartaceo:
     *              1 = Errore lettura allegati
     *              2 = Nessun allegato presente
     *              3 = Errore creazione pdf
     *              4 = Errore reperimento pdf unito
     *      - MESSAGGIO: eventuale messaggio di errore
     *      - DATI: dati di ritorno
     */
    public function inviaEstrattoContoCF($params, $listFatture) {                
        if ($params['TIPO_GESTIONE'] == 0 && $params['OPZ_TIPOINVIO'] == 0) {
            // Puntuale - Carta
            $toReturn = $this->inviaEstrattoContoCFCarta($params);
        } else if ($params['TIPO_GESTIONE'] == 0 && $params['OPZ_TIPOINVIO'] == 1) {
            // Puntuale - Email
            $toReturn = $this->inviaEstrattoContoCFEmail($params);
        } else {                        
            $toReturn = array('ESITO' => 1, 'MESSAGGIO' => 'Casistica non gestita');
        }    
        
        if ($toReturn['ESITO'] === 0) {
            $toReturn['TIPO_GESTIONE'] = $params['TIPO_GESTIONE'];
            $toReturn['TIPO_INVIO'] = $params['OPZ_TIPOINVIO'];
        }
        
        // Scrive esito in BTA_SOGINV e record in FES_DOCINV
        if (isset($params['NOT_SAVE_DOCINV']) && $params['NOT_SAVE_DOCINV'] == true){
        } else {
            $ret = $this->scriviBtaSoginv($params, $listFatture, $toReturn);
            if ($ret['ESITO'] !== 0) {
                $toReturn = array('ESITO' => 1, 'MESSAGGIO' => 'Errore salvataggio su storico invii (' . $ret['MESSAGGIO'] . ')');
            }
        }
        
        return $toReturn;
    }
    private function scriviBtaSoginv($params, $listFatture, $esitoChiamata) {
        $toReturn = array();
        $toReturn['ESITO'] = 0;
        $toReturn['MESSAGGIO'] = '';
        
        try {
            $modelName = 'cwbBtaSoginv';
            $modelService = cwbModelServiceFactory::newModelService($modelName);
            $libDB_BTA = $this->getLibBta();  
            $TIPO_COM = 0;
//            if (!empty($params['FLAG_TIPCO'])){
//                $rowBtaTipcom = $libDB_BTA->leggiGeneric('BTA_TIPCOM', array('FLAG_TIPCO' => $params['FLAG_TIPCO']), false);
//                if (!empty($rowBtaTipcom))
//                    $TIPO_COM = $rowBtaTipcom['TIPO_COM']; // Tipologia Comunicazione
//            }
            if (!empty($params['TIPO_COM']))
                $TIPO_COM = $params['TIPO_COM'];
            $toInsert = array(
                'PROGSOGG' => $params['DATI_SOGG']['PROGSOGG'],
                'NATURACOMU' => $params['NATURA'],
                'TIPO_COM' => $TIPO_COM,
                'TIPO_INVIO' => $params['OPZ_TIPOINVIO']                
            );
            
            if ($params['OPZ_TIPOINVIO'] == 0) {    // Cartaceo
                $toInsert['IDMAIL'] = 0;
                $toInsert['ESITO_MAIL'] = '';
            } else {    // Email
                $toInsert['IDMAIL'] = $esitoChiamata['DATI']['ROWID'];
                $toInsert['ESITO_MAIL'] = $esitoChiamata['MESSAGGIO'];                
            }
            
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord('BTA_SOGINV', $toInsert);
            $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, $modelName, $toInsert);
            $citywareDB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
            $modelService->insertRecord($citywareDB, 'BTA_SOGINV', $modelServiceData->getData(), $recordInfo);            
            $idSoginv = $modelService->getLastInsertId();
            if (!empty($idSoginv) && !empty($params['TIPO_COM'])){  // scrive FES_DOCINV tranne per le comunicazioni generiche
                $tot_r = count($listFatture);
                for ($ns=0; $ns<$tot_r; $ns++) {
                    $fatt = $listFatture[$ns]; // Dati Fattura
                    if ($fatt['TIPO_RIGA'] == 'DOC' || $fatt['TIPO_RIGA'] == ''){ // scrivo documenti principali e NC collegate
                        $key_f = explode(" ",$fatt['KEYFATTURA']);

                        $modelName = 'cwfFesDocinv';
                        $modelService = cwbModelServiceFactory::newModelService($modelName);
                        $toInsert = array(
                            'E_S' => $key_f[0],
                            'PROGINT_GD' => $key_f[1],
                            'ANNORIF' => $key_f[2],
                            'TIPO_INVIO' => $params['OPZ_TIPOINVIO']
                        );
                        if ($params['OPZ_TIPOINVIO'] == 0) {    // Cartaceo
                            $toInsert['IDMAIL'] = 0;
                            $toInsert['ESITO_MAIL'] = '';
                        } else {    // Email
                            $toInsert['IDMAIL'] = $esitoChiamata['DATI']['ROWID'];
                            $toInsert['ESITO_MAIL'] = $esitoChiamata['MESSAGGIO'];                
                        }
                        $toInsert['ID_SOGINV'] = $idSoginv;

                        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                        $modelServiceData->addMainRecord('FES_DOCINV', $toInsert);
                        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, $modelName, $toInsert);
                        $citywareDB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
                        $modelService->insertRecord($citywareDB, 'FES_DOCINV', $modelServiceData->getData(), $recordInfo);
                    }
                }
            }
        } catch (Exception $ex) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = $ex->getMessage();
        }
        
        return $toReturn;
    }
    
    /**
     * Apre visualizzatore email
     * @param string $idMail ID Mail (Campo IDMAIL di FES_DOCINV)
     * @return mixec False in caso di errore, altrimenti apre visualizzatore
     */
    public function apriVisualizzatoreEmail($idMail) {
        $emlLib = new emlLib();
        $mailRec = $emlLib->getMailArchivio($idMail, "ROWID");
        if ($mailRec === false) {
            return false;
        }        
        $model = 'emlViewer';
        $_POST['event'] = 'openform';
        $_POST['codiceMail'] = $mailRec['IDMAIL'];
        $_POST['tipo'] = 'id';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }
    
    private function getElementiTemplateMail($contesto, $dati) {
        // Legge BGE_MAILPARAMS
        $libDB_BTA = $this->getLibBta();
        // Effettua la prima lettura per contesto e utente
        $mailParams = $libDB_BTA->leggiGeneric('BGE_MAILPARAMS', array('CONTESTO_APP' => $contesto, 'KCODUTE' => cwbParGen::getUtente()), false);
        if (!$mailParams) {
            // Se non trovato, effettua una seconda lettura per contesto
            $mailParams = $libDB_BTA->leggiGeneric('BGE_MAILPARAMS', array('CONTESTO_APP' => $contesto, 'KCODUTE_trim' => ''), false);
        }
        
        // Se non trovato, evita risoluzione del template
        if (!$mailParams) {
            $ArrElementi = array();
            $ArrElementi['OGGETTOMAIL'] = '';
            $ArrElementi['BODYMAIL'] = '';

            return $ArrElementi;
        }
        
        // Legge row account
        $mailParamsLib = new cwbBgeMailParamsLib();
        $rowAccount = $mailParamsLib->leggiRowAccount($mailParams['IDACCOUNT']);
        
        /* Contenuto Body */
        $ContenutoBody = $mailParams['TPL_CORPO'];        
        $contenutoRisolto = $this->sostituisciContenutoVariabili($dati, $ContenutoBody);
        $ContenutoBody = ($contenutoRisolto === false ? '' : $contenutoRisolto);
        
        /* Contenuto Oggetto */
        $ContenutoOggetto = $mailParams['TPL_OGGETTO'];
        $contenutoRisolto = $this->sostituisciContenutoVariabili($dati, $ContenutoOggetto);
        $ContenutoOggetto = ($contenutoRisolto === false ? '' : $contenutoRisolto);
        
        $ArrElementi = array();
        $ArrElementi['OGGETTOMAIL'] = $ContenutoOggetto;
        $ArrElementi['BODYMAIL'] = $ContenutoBody;
        $ArrElementi['MAILPARAMS'] = $mailParams;
        $ArrElementi['MAILPARAMS']['ROW_ACCOUNT'] = $rowAccount;
                
        return $ArrElementi;
    }
    
    private function sostituisciContenutoVariabili($dati, $Contenuto) {
        $mailParamsLib = new cwbBgeMailParamsLib();
        $libVar = $mailParamsLib->instanceLibVariabili('F');
        if (!$libVar) {
            return false;
        }
        
        // Carica dati par la risoluzione del testo                
//        $libVar->caricaDatiClfor($dati['PROGSOGG'], $dati['PROGINTAP'], $dati['PAG_RIS'], $dati['PROGRECA']);
        $libVar->caricaDatiEstrattoConto($dati['DATI_EC']['testa'], $dati['DATI_EC']['righe'], $dati['DATI_EC']['totali'], $dati['E_S'], $dati['PROGSOGG'], $dati['FLAG_DINV']);
        
        $dictionaryValues = $libVar->getVariabiliGenerico()->getAllData();
        
        $docLib = new docLib();
        $templateCompilato = $docLib->GetStringDecode($dictionaryValues, $Contenuto);
        return $templateCompilato;
    }    
    
    private function inviaEstrattoContoCFCarta($params) {
        $toReturn = array();
        $toReturn['ESITO'] = 0;
        $toReturn['MESSAGGIO'] = '';
                     
        // Legge allegati
        $params['OPZ_ZIP'] = 0;
        $attachments = $this->getAllegatiInvioMail($params, $error);
        if ($attachments === false) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Errore lettura allegati";
            return $toReturn;
        }
        if (!is_array($attachments) || count($attachments) == 0) {
            $toReturn['ESITO'] = 2;
            $toReturn['MESSAGGIO'] = "Nessun allegato presente";
            return $toReturn;
        } 
        
        // Crea array di file da unire
        $inputFiles = array();
        foreach ($attachments as $attachment) {
            $inputFiles[] = $attachment['FILEPATH'];
        }
        
        // Unisce pdf in un unico file
        $itaPDFUtils = new itaPDFUtils();
        if (!$itaPDFUtils->unisciPdf($inputFiles)) {
            $toReturn['ESITO'] = 3;
            $toReturn['MESSAGGIO'] = "Errore creazione pdf: impossibile completare l'operazione in quanto alcuni file risultano protetti";
            return $toReturn;
        }
        $outputPath = $itaPDFUtils->getRisultato();
        if (!$outputPath) {
            $toReturn['ESITO'] = 4;
            $toReturn['MESSAGGIO'] = "Errore reperimento pdf unito";
            return $toReturn;
        }
        $toReturn['DATI'] = $outputPath;
        return $toReturn;
    }
    
    private function inviaEstrattoContoCFEmail($params) {
        $toReturn = array();
        $toReturn['ESITO'] = 0;
        $toReturn['MESSAGGIO'] = '';
        
        // Imposta tipo gestione
        // 0 = Puntuale
        // 1 = Massiva
        $tipoGestione = $params['TIPO_GESTIONE'];
               
        // Controlla indirizzo destinatario
        if ($tipoGestione === 0 && strlen(trim($params['INDIR_DEST'])) === 0) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = "Indirizzo destinatario non specificato";
            return $toReturn;
        }
        
//        // Controlla che l'account sia configurato correttamente
//        if (strlen(trim($params['MAILPARAMS']['ROW_ACCOUNT']['MAILADDR'])) === 0) {
//            $toReturn['ESITO'] = 2;
//            $toReturn['MESSAGGIO'] = "Account di posta elettronica non configurato";
//            return $toReturn;            
//        }
        
        // Creazione oggetto mailbox
        $mailParamsLib = new cwbBgeMailParamsLib();
        $emlMailBoxRet = $mailParamsLib->getMailBox($params['MAILPARAMS'], $params['INDIR_SPED']);
        if ($emlMailBoxRet['ESITO'] == 0) {        
            $emlMailBox = $emlMailBoxRet['DATA'];
        } else {
            $toReturn['ESITO'] = $emlMailBoxRet['ESITO'];
            $toReturn['MESSAGGIO'] = $emlMailBoxRet['MESSAGGIO'];
            return $toReturn;
        }
        
        // Creazione di un nuovo messaggio in uscita
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $toReturn['ESITO'] = 4;
            $toReturn['MESSAGGIO'] = "Impossibile creare un nuovo messaggio in uscita";
            return $toReturn;
        }
        
        // Prepara messaggio in uscita
        $outgoingMessage->setSubject($params['MSG_OGGETTO']);
        $outgoingMessage->setBody($params['MSG_CORPO']);
        $outgoingMessage->setEmail($params['INDIR_DEST']);
        if (!empty($params['INDIR_DEST_CC'])) {
            $outgoingMessage->setCCAddresses($params['INDIR_DEST_CC']);
        }
        if (!empty($params['INDIR_DEST_CCN'])) {
            $outgoingMessage->setBCCAddresses($params['INDIR_DEST_CCN']);
        }        
        
        // Include allegati
        $error = '';
        $attachments = $this->getAllegatiInvioMail($params, $error);
        if ($attachments === false) {
            $toReturn['ESITO'] = 6;
            $toReturn['MESSAGGIO'] = $error;
            return $toReturn;
        }
                
        if (is_array($attachments) && count($attachments) > 0) {
            $outgoingMessage->setAttachments($attachments);
        }        
        
        // Invio mail
        $mailSent = $emlMailBox->sendMessage($outgoingMessage);
        if (!$mailSent) {
            $toReturn['ESITO'] = 5;
            $toReturn['MESSAGGIO'] = 'Errore invio mail: (' . $emlMailBox->getLastMessage() . ')';
            return $toReturn;
        }
        $toReturn['DATI'] = $mailSent;
                
        return $toReturn;
    }
    
    private function getAllegatiInvioMail($params, &$error) {                
        $error = '';
        $attachments = $params['ATTACHMENTS'];
        
        // Path input
        $subPath = "ec-alleg-" . md5(microtime());
        $tempInputPath = itaLib::createAppsTempPath($subPath);
        
        // Path output
        $subPath = "ec-zip-" . md5(microtime());
        $tempOutputPath = itaLib::createAppsTempPath($subPath);                        
        
        // Copia allegati nel path temporaneo
        foreach ($attachments as $attachment) {
            if (!@copy($attachment['FILEPATH'], $tempInputPath . '/' . $attachment['FILEORIG'])) {
                $errors = error_get_last();
                $error = $errors['message'];
                return false;
            }
        }
        
        // Controlla se gli allegati devono essere zippati
        if ($params['OPZ_ZIP'] == 1) {
            $zipName = 'allegati.zip';
            $zipPath = $tempOutputPath . '/' . $zipName;
            $output = pathinfo($zipPath, PATHINFO_DIRNAME);                                        
            itaZip::zipRecursive($output, $tempInputPath, $zipPath, 'zip', false, false);
            if (!file_exists($zipPath)) {
                $error = 'Errore creazione file zip';
                return false;
            }
            $attachments = array();
            $attachment = array();
            $attachment['FILEORIG'] = $zipName;
            $attachment['FILEPATH'] = $zipPath;            
            $attachments[] = $attachment;
            
            itaLib::clearAppsTempPath($tempInputPath);
        }
        
        return $attachments;
    }
    
    private function getIndirizziEmailSped($mailParams) {
        $toReturn = array();
                
        for($i = 1; $i <= 3; $i++) {            
            $modRepmail = $mailParams["MOD_REPMAIL$i"];      
            switch($modRepmail) {
                case 1:     // Utente                         
                    $accLib = new accLib();        
                    $parametriUser = $accLib->GetParamentriMail();
                    if (!empty($parametriUser['FROM'])) {
                        $toReturn[] = array(
                            'MOD_REPMAIL' => 1,
                            'INDIRIZZO_EMAIL' => $parametriUser['FROM'] . ' (Da utente)',
                            'FROM' => $parametriUser['FROM']                            
                        );                        
                    }
                    break;
                case 2:     // Account
                    $toReturn[] = array(
                        'MOD_REPMAIL' => 2,
                        'INDIRIZZO_EMAIL' => $mailParams['ROW_ACCOUNT']['MAILADDR'] . ' (Da account)',
                        'FROM' => $mailParams['ROW_ACCOUNT']['MAILADDR']
                    );
                    break;
                case 3:     // Organigramma
                    // NON GESTITO
                    break;
            }
        }
        
        // Se non impostata nessuna delle tre modalità, e se il campo account risulta valorizzato, 
        // considera comunque account
        if (count($toReturn) === 0) {
            if ($mailParams['ROW_ACCOUNT'] === false) {
                return false;
            }
            $toReturn[] = array(
                'MOD_REPMAIL' => 2,
                'INDIRIZZO_EMAIL' => $mailParams['ROW_ACCOUNT']['MAILADDR'] . ' (Da account)',
                'FROM' => $mailParams['ROW_ACCOUNT']['MAILADDR']
            );
        }
        
        return $toReturn;                
    }
    
    private function getLibBta() {
        return new cwbLibDB_BTA();
    }
       
}
