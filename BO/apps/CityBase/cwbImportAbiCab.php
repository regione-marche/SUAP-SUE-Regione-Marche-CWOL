<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbImportAbiCab() {
    $cwbImportAbiCab = new cwbImportAbiCab();
    $cwbImportAbiCab->parseEvent();
    return;
}

class cwbImportAbiCab extends cwbBpaGenModel {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbImportAbiCab';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 13;
        $this->libDB = new cwbLibDB_BTA();
        $this->LEN_RECFILE = 190; // Larghezza Record File da Importare FISSA (al 10-11-2017) 
        $this->pathTmp = itaLib::createAppsTempPath('ImportAbiCab');
        $this->csv_abi_add = $this->pathTmp . '/abi_add.csv'; 
        $this->csv_abi_upd = $this->pathTmp . '/abi_upd.csv'; 
        $this->csv_abi_del = $this->pathTmp . '/abi_del.csv'; 
        $this->csv_cab_add = $this->pathTmp . '/cab_add.csv'; 
        $this->csv_cab_upd = $this->pathTmp . '/cab_upd.csv'; 
        $this->csv_cab_del = $this->pathTmp . '/cab_del.csv'; 
        $this->creati = false; 
        // Separatore - Default = TAB
        $this->sepcsv = ","; //chr(9); // Tab per Separatore (non virgola!)
        $this->sep_testo = '"'; //chr(34); // Doppio Apice (Alfanumerici)
        $this->finerec = "\n"; //chr(10).chr(13); // Line Feed e Carriage return
        $this->noCrud = true;// controllo autenticazione pag non standard
    }

    public function parseEvent() {       
        switch ($_POST['event']) {
            case 'openform':
                $this->initComboGestione();
                // ATTRIBUTI: DA INSERIRE SU GENERATORE se possibile
                $spieg = "Verranno importati tutti i record del file selezionato.";
                $spieg .= "<br />- Se l'ABI e/o il CAB non viene trovato lo Aggiunge.";
                $spieg .= "<br />- Se lo trova ma ci sono delle differenze lo Aggiorna.";
                $spieg .= "<br />- Tutti quelli non piu' presenti nel flusso verranno Disabilitati.";
                $spieg .= "<br /><br />Verranno creati dei files .csv gestibili con Excel, Calc ecc. con i campi separati";
                $spieg .= "<br />dalla virgola e delimitati del doppio apice.";
                $spieg .= "<br /><br />Attenzione. L'operazione potrebbe essere lunga; attendere";
                $spieg .= " la segnalazione di fine lavoro.";
                Out::html($this->nameForm . '_spiegazione', $spieg);
                Out::attributo($this->nameForm . '_nome_file_conpath', 'readonly', '1');
                Out::hide($this->nameForm . '_nome_file_conpath');
                Out::attributo($this->nameForm . '_nome_file_ridotto', 'readonly', '1');
                Out::hide($this->nameForm . '_nome_file_ridotto');
                Out::hide($this->nameForm . '_risultato');
                Out::hide($this->nameForm . '_down_abi_add');
                Out::hide($this->nameForm . '_down_abi_upd');
                Out::hide($this->nameForm . '_down_abi_del');
                Out::hide($this->nameForm . '_down_cab_add');
                Out::hide($this->nameForm . '_down_cab_upd');
                Out::hide($this->nameForm . '_down_cab_del');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'cwbImportAbiCab_seleziona':
                        $this->seleziona();
                        break;
                    case 'cwbImportAbiCab_inizia':
                        // Controllo Campo caricato
                        if(empty($_POST[$this->nameForm . '_nome_file_conpath'])) {
                            Out::msgInfo('Errore','Occorre selezionare il file da Importare.');
                        } else {
                            // OK Procedo con il Lavoro
                            $orainix = (intval(date("H",time()))*10000) + (intval(date("i",time()))*100) + intval(date("s",time()));
                            $oraini = date("H",time()) . ':' . date("i",time()) . ':' . date("s",time());
                            $nomef = $_POST[$this->nameForm . '_nome_file_conpath'];
                            $modalita_import = $_POST[$this->nameForm . '_modalita_import'];
                            $struttura_descr = $_POST[$this->nameForm . '_struttura_descr'];
                            $csv_abi_add = ''; 
                            $csv_abi_upd = ''; 
                            $csv_abi_del = ''; 
                            $csv_cab_add = ''; 
                            $csv_cab_upd = ''; 
                            $csv_cab_del = ''; 
                            // Assegno Intestazione Colonne per il file CSV
                                // ABI
                            $x_csv = $this->csv_ca("ABI") . $this->sepcsv . $this->csv_ca("ABI_CIN") . $this->sepcsv . $this->csv_ca("DESCRIZIONE BANCA") . $this->finerec;
                            $csv_abi_add .= $x_csv;
                            $csv_abi_upd .= $x_csv;
                                // CAB
                            $x_csv = $this->csv_ca("ABI") . $this->sepcsv . $this->csv_ca("CAB") . $this->sepcsv . $this->csv_ca("SPORTELLO");
                            $x_csv .= $this->csv_ca("INDIRIZZO") . $this->sepcsv . $this->csv_ca("LOCALITA") . $this->sepcsv . $this->csv_ca("COMUNE");
                            $x_csv .= $this->csv_ca("CAP") . $this->sepcsv . $this->csv_ca("PROVINCIA") . $this->sepcsv . $this->csv_ca("TIPO_VISUALIZZAZIONE") . $this->finerec;
                            $csv_cab_add .= $x_csv;
                            $csv_cab_upd .= $x_csv;
                            $ris = $this->file_leggi($nomef);
                            if (!$ris['ce_erro']) {
                                $risultato_abi = '';
                                $risultato_cab = '';
                                $riepilogo = '<span style="font-weight: bold;;font-size:14px;">';
                                $riepilogo .= 'File composto da: ' . $ris['totr'] . ' record. ABI: '.$ris['tot_abi'].' CAB: '.$ris['tot_cab'];
                                $riepilogo .= '</span><br />';
                                if ($modalita_import) {
                                    // Apro la Transazione
                                    ItaDb::DBBeginTransaction($this->getCitywareDB());
                                }
                                // 1^ FASE: Tabella ABI
                                if ($modalita_import) {
                                    // Creo Model personalizzato
                                    $modelName = 'cwbBtaAbi';
                                    $modelService = cwbModelServiceFactory::newModelService($modelName, true);
                                    // Metto tutti i record attuali come Disabilitati (solo FLAG_DIS=1)
                                    $query_disab = 'update BTA_ABI set FLAG_DIS=1';
                                    $ok = ItaDB::DBSQLExec($this->getCitywareDB(), $query_disab);
                                    if (!$ok) {
                                        // Ha dato un qualche errore non trappato
                                        Out::msgInfo('Errore','Errore su Disabilitazione ABI.');
                                    }
                                }       
                                $n_add = 0;
                                $n_upd = 0;
                                $n_ok = 0;
                                $nr = 0;
                                foreach( $ris['v_abi'] as $key=>$valori ) {
                                    $nr ++;
                                    $lavoro = $valori['lavoro'];
                                    $deser = $valori['deser'];
                                    $desx = 'Abi:' . $valori['ABI'];
                                    $desx .= ' Cin: ' . $valori['ABI_CIN'];
                                    $desx .= ' DesBanca: ' . $valori['DESBANCA'];
                                    switch ($lavoro) {
                                        // Inserimento
                                        case 1:
                                            if ($modalita_import) {
                                                $array_ins = array('ABI'=>$valori['ABI']
                                                            ,'ABI_CIN'=>$valori['ABI_CIN']
                                                            ,'DESBANCA'=>$valori['DESBANCA']
                                                            ,'FLAG_DIS'=>0);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_ABI', $array_ins);
                                                // Inserimento
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, $modelName, $array_ins);
                                                $modelService->insertRecord($this->getCitywareDB(), 'BTA_ABI', $modelServiceData->getData(), $recordInfo);
                                            }                                           
                                            $n_add ++;
                                            // Assegno Record per il file CSV
                                            $x_csv = $this->csv_ca($valori['ABI']) . $this->sepcsv . $this->csv_ca($valori['ABI_CIN']) . $this->sepcsv . $this->csv_ca($valori['DESBANCA']) . $this->finerec;
                                            $csv_abi_add .= $x_csv;
                                            break;
                                        // Modifica
                                        case 2:
                                            if ($modalita_import) {
                                                $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BTA_ABI'), true, true);
                                                $array_upd = array('ABI'=>$valori['ABI']
                                                            ,'ABI_CIN'=>$valori['ABI_CIN']
                                                            ,'DESBANCA'=>$valori['DESBANCA']);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_ABI', $array_upd);
                                                // Aggiornamento
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $modelName, $array_upd);
                                                $modelService->updateRecord($this->getCitywareDB(), 'BTA_ABI', $modelServiceData->getData(), $recordInfo);
                                            }
                                            $n_upd ++;
                                            $x_csv = $this->csv_ca($valori['ABI']) . $this->sepcsv . $this->csv_ca($valori['ABI_CIN']) . $this->sepcsv . $this->csv_ca($valori['DESBANCA']) . $this->finerec;
                                            $csv_abi_upd .= $x_csv;
                                            break;
                                        // Uguale per cui lo Ri-Abilito (solo FLAG_DIS=0)
                                        default:
                                            if ($modalita_import) {
                                                $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BTA_ABI'), true, true);
                                                $array_upd = array('ABI'=>$valori['ABI'],
                                                                   'FLAG_DIS'=>0);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_ABI', $array_upd);
                                                // Aggiornamento (Ri-Abilitazione)
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $modelName, $array_upd);
                                                $modelService->updateRecord($this->getCitywareDB(), 'BTA_ABI', $modelServiceData->getData(), $recordInfo);
                                            }
                                            $n_ok ++;
                                            break;
                                    }
                                }
                                $risultato_abi .= '<u><i>AGGIUNTI:</i></u>(' .$n_add .')';
                                $risultato_abi .= '<br /><u><i>MODIFICATI:</i></u>(' .$n_upd .')';
                                $risultato_abi .= '<br /><u><i>NON VARIATI:</i></u>(' .$n_ok .')';
                                // 2^ FASE: Tabella CAB
                                if ($modalita_import) {
                                    // Creo Model personalizzato
                                    $modelName = 'cwbBtaCab';
                                    $modelService = cwbModelServiceFactory::newModelService($modelName, true);
                                    // Metto tutti i record attuali come Disabilitati (solo FLAG_DIS=1)
                                    $query_disab = 'update BTA_CAB set FLAG_DIS=1';
                                    $ok = ItaDB::DBSQLExec($this->getCitywareDB(), $query_disab);
                                    if (!$ok) {
                                        // Ha dato un qualche errore non trappato
                                        Out::msgInfo('Errore','Errore su Disabilitazione CAB.');
                                    }
                                }       
                                $n_add = 0;
                                $n_upd = 0;
                                $n_ok = 0;
                                $nr = 0;
                                foreach( $ris['v_cab'] as $key=>$valori ) {
                                    $nr ++;
                                    $lavoro = $valori['lavoro'];
                                    $deser = $valori['deser'];
                                    $desx = 'Abi:' . $valori['ABI'];
                                    $desx .= ' Cab: ' . $valori['CAB'];
                                    $desx .= ' Indir: ' . $valori['INDIRSPORT'];
                                    $desx .= ' Local: ' . $valori['LOCALSPORT'];
                                    $desx .= ' Comune: ' . $valori['COMUNEUBIC'];
                                    $desx .= ' Cap: ' . $valori['CAP'];
                                    $desx .= ' Prov: ' . $valori['PROVINCIA'];
                                    $desx .= ' Sportello: ' . $valori['DES_SPORT'];
                                    switch ($lavoro) {
                                        // Inserimento
                                        case 1:
                                            if ($modalita_import) {
                                                $array_ins = array('ABI'=>$valori['ABI']
                                                            ,'CAB'=>$valori['CAB']
                                                            ,'DES_SPORT'=>$valori['DES_SPORT']
                                                            ,'INDIRSPORT'=>$valori['INDIRSPORT']
                                                            ,'LOCALSPORT'=>$valori['LOCALSPORT']
                                                            ,'COMUNEUBIC'=>$valori['COMUNEUBIC']
                                                            ,'CAP'=>$valori['CAP']
                                                            ,'PROVINCIA'=>$valori['PROVINCIA']
                                                            ,'CAB_MODPRE'=>$struttura_descr
                                                            ,'FLAG_DIS'=>0);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_CAB', $array_ins);
                                                // Inserimento
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_INSERT, $modelName, $array_ins);
                                                $modelService->insertRecord($this->getCitywareDB(), 'BTA_CAB', $modelServiceData->getData(), $recordInfo);
                                            }
                                            $n_add ++;
                                            // Assegno Record per il file CSV
                                            $x_csv = $this->csv_ca($valori['ABI']) . $this->sepcsv . $this->csv_ca($valori['CAB']) . $this->sepcsv . $this->csv_ca($valori['DES_SPORT']);
                                            $x_csv .= $this->csv_ca($valori['INDIRSPORT']) . $this->sepcsv . $this->csv_ca($valori['LOCALSPORT']) . $this->sepcsv . $this->csv_ca($valori['COMUNEUBIC']);
                                            $x_csv .= $this->csv_ca($valori['CAP']) . $this->sepcsv . $this->csv_ca($valori['PROVINCIA']) . $this->sepcsv . $valori['CAB_MODPRE'] . $this->finerec;
                                            $csv_cab_add .= $x_csv;
                                            break;
                                        // Modifica
                                        case 2:
                                            if ($modalita_import) {
                                                $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BTA_CAB'), true, true);
                                                $array_upd = array('ABI'=>$valori['ABI']
                                                            ,'CAB'=>$valori['CAB']
                                                            ,'DES_SPORT'=>$valori['DES_SPORT']
                                                            ,'INDIRSPORT'=>$valori['INDIRSPORT']
                                                            ,'LOCALSPORT'=>$valori['LOCALSPORT']
                                                            ,'COMUNEUBIC'=>$valori['COMUNEUBIC']
                                                            ,'CAP'=>$valori['CAP']
                                                            ,'PROVINCIA'=>$valori['PROVINCIA']
                                                            ,'CAB_MODPRE'=>$struttura_descr);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_CAB', $array_upd);
                                                // Aggiornamento
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $modelName, $array_upd);
                                                $modelService->updateRecord($this->getCitywareDB(), 'BTA_CAB', $modelServiceData->getData(), $recordInfo);
                                            }                                            
                                            $n_upd ++;
                                            // Assegno Record per il file CSV
                                            $x_csv = $this->csv_ca($valori['ABI']) . $this->sepcsv . $this->csv_ca($valori['CAB']) . $this->sepcsv . $this->csv_ca($valori['DES_SPORT']);
                                            $x_csv .= $this->csv_ca($valori['INDIRSPORT']) . $this->sepcsv . $this->csv_ca($valori['LOCALSPORT']) . $this->sepcsv . $this->csv_ca($valori['COMUNEUBIC']);
                                            $x_csv .= $this->csv_ca($valori['CAP']) . $this->sepcsv . $this->csv_ca($valori['PROVINCIA']) . $this->sepcsv . $valori['CAB_MODPRE'] . $this->finerec;
                                            $csv_cab_upd .= $x_csv;
                                            break;
                                        // Uguale per cui lo Ri-Abilito (solo FLAG_DIS=0)
                                        default:
                                            if ($modalita_import) {
                                                $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName('BTA_CAB'), true, true);
                                                $array_upd = array('ABI'=>$valori['ABI']
                                                                  ,'CAB'=>$valori['CAB']
                                                                  ,'FLAG_DIS'=>0);
                                                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                                                $modelServiceData->addMainRecord('BTA_CAB', $array_upd);
                                                // Aggiornamento (Ri-Abilitazione)
                                                $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $modelName, $array_upd);
                                                $modelService->updateRecord($this->getCitywareDB(), 'BTA_CAB', $modelServiceData->getData(), $recordInfo);
                                            }                                           
                                            $n_ok ++;
                                            break;
                                    }
                                }
                                $risultato_cab .= '<u><i>AGGIUNTI:</i></u>(' .$n_add .')';
                                $risultato_cab .= '<br /><u><i>MODIFICATI:</i></u>(' .$n_upd .')';
                                $risultato_cab .= '<br /><u><i>NON VARIATI:</i></u>(' .$n_ok .')';
                                if ($modalita_import) {
                                    // Chiudo la Transazione
                                    ItaDb::DBCommitTransaction($this->getCitywareDB());
                                    // Nel caso di problemi eseguo il rollback
//??                                ItaDb::DBRollbackTransaction($this->getCitywareDB());
                                }
                                // Scrivo i Files csv su Temporaneo
                                $this->file_scrivi($csv_abi_add,$this->csv_abi_add);
                                $this->file_scrivi($csv_abi_upd,$this->csv_abi_upd);
                                $this->file_scrivi($csv_abi_del,$this->csv_abi_del);
                                $this->file_scrivi($csv_cab_add,$this->csv_cab_add);
                                $this->file_scrivi($csv_cab_upd,$this->csv_cab_upd);
                                $this->file_scrivi($csv_cab_del,$this->csv_cab_del);
                                $this->creati = true; 
                                // Completo Riepilogo e lo Visualizzo
                                $orafinx = (intval(date("H",time()))*10000) + (intval(date("i",time()))*100) + intval(date("s",time()));
                                $orafin = date("H",time()) . ':' . date("i",time()) . ':' . date("s",time());
                                $riepilogo .= '<br />Iniziato alle ' . $oraini . ' e terminato alle '. $orafin;
                                $riepilogo .= ' Ho impiegato '. ($orafinx-$orainix) . ' Secondi.';
                                Out::show($this->nameForm . '_down_abi_add');
                                Out::show($this->nameForm . '_down_abi_upd');
                                Out::show($this->nameForm . '_down_abi_del');
                                Out::show($this->nameForm . '_down_cab_add');
                                Out::show($this->nameForm . '_down_cab_upd');
                                Out::show($this->nameForm . '_down_cab_del');
                                Out::show($this->nameForm . '_risultato');
                                Out::html($this->nameForm . '_divRiepilogo', $riepilogo);
                                Out::html($this->nameForm . '_divRiepilogoAbi', $risultato_abi);
                                Out::html($this->nameForm . '_divRiepilogoCab', $risultato_cab);
                                Out::msgInfo('Fine Lavoro','Lavoro terminato correttamente.<br />Controlla il Riepilogo.');
                            } // Se Ritornato con Errore
                        }
                        break;
                    case 'cwbImportAbiCab_down_abi_add':
                        $this->apri_csv( basename($this->csv_abi_add) );
                        break;
                    case 'cwbImportAbiCab_down_abi_upd':
                        $this->apri_csv( basename($this->csv_abi_upd) );
                        break;
                    case 'cwbImportAbiCab_down_abi_del':
                        $this->apri_csv( basename($this->csv_abi_del) );
                        break;
                    case 'cwbImportAbiCab_down_cab_add':
                        $this->apri_csv( basename($this->csv_cab_add) );
                        break;
                    case 'cwbImportAbiCab_down_cab_upd':
                        $this->apri_csv( basename($this->csv_cab_upd) );
                        break;
                    case 'cwbImportAbiCab_down_cab_del':
                        $this->apri_csv( basename($this->csv_cab_del) );
                        break;
                    case 'close-portlet':
                        // Elimino i Files csv su Temporaneo
                        if ($this->creati) {
                            $this->file_cancella($csv_abi_add,$this->csv_abi_add);
                            $this->file_cancella($csv_abi_upd,$this->csv_abi_upd);
                            $this->file_cancella($csv_abi_del,$this->csv_abi_del);
                            $this->file_cancella($csv_cab_add,$this->csv_cab_add);
                            $this->file_cancella($csv_cab_upd,$this->csv_cab_upd);
                            $this->file_cancella($csv_cab_del,$this->csv_cab_del);
                        }
                        break;
                }
                break;
            case 'returnUpload':
                Out::valore($this->nameForm . '_nome_file_conpath', $_POST['uploadedFile']);
                Out::valore($this->nameForm . '_nome_file_ridotto', $_POST['file']);
                Out::show($this->nameForm . '_nome_file_ridotto');
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }    

    // Inizializzo ComboBox di Lavoro
    private function initComboGestione() {
        // Modalita Importazione
        Out::select($this->nameForm . '_modalita_import', 1, "0", 1, "Simulazione");
        Out::select($this->nameForm . '_modalita_import', 1, "1", 0, "Definitivo");

        // Struttura Descrizione Sportello
        Out::select($this->nameForm . '_struttura_descr', 1, "1", 1, "Comune / Indirizzo / Sportello");
        Out::select($this->nameForm . '_struttura_descr', 1, "2", 0, "Comune / Localita / Indirizzo");
        Out::select($this->nameForm . '_struttura_descr', 1, "3", 0, "Sportello / Localita / Comune");
        Out::select($this->nameForm . '_struttura_descr', 1, "4", 0, "Sportello / Comune / Indirizzo");
    }
    
    private function seleziona() {
// Vecchia versione non funzionante per Modali
//        $model = 'utiUploadDiag';
//        $_POST = Array();
//        $_POST['event'] = 'openform';
//        $_POST[$model . '_returnModel'] = 'cwbImportAbiCab';
//        $_POST[$model . '_returnEvent'] = "returnUpload";
//        itaLib::openForm($model);
//        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//        $model();
// -------------
        $model = "utiUploadDiag";
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model, $model);
        if (!$formObj) {
            Out::msgStop("Errore", "Apertura fallita");
            return;
        }

        $formObj->setReturnModelOrig($this->nameFormOrig);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnEvent('returnUpload');
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }
    
    // **************************************
    // Creo e scrivo un File
    // **************************************
    private function file_scrivi($contenuto,$nomefile) {
        // Scrivo il file (se posso!)
        $apri = fopen($nomefile,"w");
        // Test errore!
        if (!$apri) {
//            var_dump(error_get_last());
            Out::msgInfo('Errore','Non riesco a scrivere il file:<br />' . $nomefile);
        }
        fwrite($apri, $contenuto);
        fclose($apri);
    }
    // **************************************
    // Elimino un File
    // **************************************
    private function file_cancella($nomefile) {
        // Cancello il file (se posso!)
        if (!file_exists($nomefile)) {
//echo 'Non esiste file da cancellare';
        } else {
                $ris = unlink($nomefile);
//            var_dump( error_get_last() );
        }
    }
    // **************************************
    // Campo per CSV
    // **************************************
    private function csv_ca($valore) {
        return $this->sep_testo . str_replace($this->sepcsv, '\\' . $this->sepcsv, $valore) . $this->sep_testo;
    }
    // **************************************
    // Apertura CSV creato
    // **************************************
    private function apri_csv($fileName) {
// NON Funziona perche' cancella i files temporanei appena creati
        Out::openDocument(utiDownload::getUrl($fileName, $this->pathTmp));
    }
    // **************************************
    // Leggo contenuto del File da Importare
    // **************************************
    private function file_leggi($nomefile) {
        // Apro il file (se posso!)
        $ce_erro = false;
        $v_abi = array();
        $v_cab = array();
        $totr = 0; 
        $tot_abi = 0;
        $tot_cab = 0;
        $tot_errlen = 0;
        if (!file_exists($nomefile)) {
            // Il file non esiste!
            Out::msgInfo('Errore','File inesistente.');
            $ce_erro = true;
        } else {
            if (!$apri = fopen($nomefile,"r")){
                // "Non posso aprire il file";
                Out::msgInfo('Errore','Non riesco ad aprire il file.');
                $ce_erro = true;
            } else {
                // Restituisce un array con gli elementi uguali ad ogni riga del file di testo.
                $leggo = array();
                $f = file($nomefile);
                foreach($f as $v) {
                    $totr ++;
                    // Controllo Larghezza Record sia corretta
                    // Pulisco la stringa da ritorni a capo e tabulazioni
                    $v = str_replace(array("\n","\r"), "", $v);
                    if ( strlen($v) != $this->LEN_RECFILE ) {
                        $tot_errlen ++;
                    }
                    // A seconda del Tipo Record lo tratto
                    $tipo = substr($v,0,2);
                    switch ($tipo) {
                        // ABI: Unico
                        case "11":
                            $abi = intval(substr($v,2,5));
                            $abi_cin = trim(substr($v,12,1));
                            $desbanca = trim(substr($v,13,80));
                            // Tronco in quanto DB Cityware ha meno caratteri
                            if (strlen($desbanca) > 45) { 
                                $desbanca = trim(substr($desbanca, 0, 45)); 
                            } 
                            $deser = '';
                            $lavoro = 0; // Default Non Faccio Nulla
                            // Controlli sul Lavoro da effettuare
                            $rs = $this->libDB->leggiBtaAbiChiave($abi);
                            if ($rs) {
                                if ($rs['ABI_CIN'] != $abi_cin) {
                                    $deser .= '<br />______Abi Cin: [' . $rs['ABI_CIN'] . '] -> [' . $abi_cin .']';
                                    $lavoro = 2; 
                                }
                                if (trim($rs['DESBANCA']) != $desbanca) {
                                    $deser .= '<br />______Descrizione: [' . trim($rs['DESBANCA']) . '] -> [' . $desbanca .']';
                                    $lavoro = 2; 
                                }
                            } else {
                                $lavoro = 1; 
                            }
                            $vr = array('ABI'=>$abi
                                        ,'ABI_CIN'=>$abi_cin
                                        ,'DESBANCA'=>$desbanca
                                        ,'lavoro'=>$lavoro
                                        ,'deser'=>$deser);
                            $v_abi[$abi] = $vr;
                            $tot_abi ++;
                            break;
                        // CAB: 1^ parte
                        case "21":
                            $abi = intval(substr($v,2,5));
                            $cab = intval(substr($v,7,5));
                            $cab_cin = trim(substr($v,12,1));
                            $cab_localita = trim(substr($v,13,5));
                            $cin_cab_localita = trim(substr($v,18,1));
                            $indirizzo = trim(substr($v,20,80));
                            // Tronco in quanto DB Cityware ha meno caratteri
                            if (strlen($indirizzo) > 40) { 
                                $indirizzo = trim(substr($indirizzo, 0, 40)); 
                            } 
                            $localita = trim(substr($v,100,40));
                            $comune = trim(substr($v,140,40));
                            $cap = trim(substr($v,180,5));
                            $provincia = trim(substr($v,185,2));
                            $abicab = $abi . '-' . $cab;
                            $deser = '';
                            $lavoro = 0; // Default Non Faccio Nulla
                            // Controlli sul Lavoro da effettuare
                            $rs = $this->libDB->leggiBtaCabChiave($abi,$cab);
                            if ($rs) {
                                if (trim($rs['INDIRSPORT']) != $indirizzo) {
                                    $deser .= '<br />______Indirizzo: [' . trim($rs['INDIRSPORT']) . '] -> [' . $indirizzo . ']';
                                    $lavoro = 2; 
                                }
                                if (trim($rs['LOCALSPORT']) != $localita) {
                                    $deser .= '<br />______Localita: [' . trim($rs['LOCALSPORT']) . '] -> [' . $localita . ']';
                                    $lavoro = 2; 
                                }
                                if (trim($rs['COMUNEUBIC']) != $comune) {
                                    $deser .= '<br />______Comune: [' . trim($rs['COMUNEUBIC']) . '] -> [' . $comune .']';
                                    $lavoro = 2; 
                                }
                                if (trim($rs['CAP']) != $cap) {
                                    $deser .= '<br />______Cap: [' . trim($rs['CAP']) . '] -> [' . $cap . ']';
                                    $lavoro = 2; 
                                }
                                if (trim($rs['PROVINCIA']) != $provincia) {
                                    $deser .= '<br />______Provincia: [' . trim($rs['PROVINCIA']) . '] -> [' . $provincia . ']';
                                    $lavoro = 2; 
                                }
                            } else {
                                $lavoro = 1; 
                            }
                            $vr = array('ABI'=>$abi
                                        ,'CAB'=>$cab
                                        ,'cab_cin'=>$cab_cin
                                        ,'cab_localita'=>$cab_localita
                                        ,'cin_cab_localita'=>$cin_cab_localita
                                        ,'INDIRSPORT'=>$indirizzo
                                        ,'LOCALSPORT'=>$localita
                                        ,'COMUNEUBIC'=>$comune
                                        ,'CAP'=>$cap
                                        ,'PROVINCIA'=>$provincia
                                        ,'DES_SPORT'=>'' // Assegnato su Record successivo (Tipo=31)
                                        ,'lavoro'=>$lavoro
                                        ,'deser'=>$deser);
                            $v_cab[$abicab] = $vr;
                            $tot_cab ++;
                            break;
                        // CAB: 2^ parte
                        case "31":
                            // Aggiorna solo gli esistenti CAB!
                            $abi = intval(substr($v,2,5));
                            $cab = intval(substr($v,7,5));
                            $sportello = trim(substr($v,12,40));
                            $abicab = $abi . '-' . $cab;
                            $deser = '';
                            if (!isset($v_cab[$abicab])) {
                                // Non Trovato e' un PROBLEMA. Lo segnalo?
                                Out::msgInfo('Errore','Manca Tipo 21 per ABI-CAB: ' . $abicab);
                                $ce_erro = true;
                            } else {
                                $v_cab[$abicab]['DES_SPORT'] = $sportello; // Solo Descrizione Sportello
                                // Controllo anche la descrizione ma credo sia solo una
                                // modalita' visualizzata in modo particolare in base a CAB_MODRE
                                // Controlli sul Lavoro da effettuare
                                $rs = $this->libDB->leggiBtaCabChiave($abi,$cab);
                                if ($rs) {
                                    // Particolare in quanto DB Cityware ha piu' caratteri (45)
                                    if (strlen($rs['DES_SPORT']) > 40) { 
                                        $rs['DES_SPORT'] = trim(substr($rs['DES_SPORT'], 0, 40)); 
                                    } 
                                    if ($rs['DES_SPORT'] != $sportello) {
                                        $deser .= '<br />______Sportello: [' . $rs['DES_SPORT'] . '] -> [' . $sportello . ']';
                                        $v_cab[$abicab]['deser'] .= $deser;
                                        $lavoro = 2; 
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }
        fclose($apri);
        // Controllo larghezza dei record corretta
        if ($tot_errlen) {
            Out::msgInfo('Errore','Ci sono ' . $tot_errlen .' Record con larghezza errata.<br />La corretta è ' . $this->LEN_RECFILE . ' caratteri.');
            $ce_erro = true;
        }
        // Reinvio dato Letto
        return array('ce_erro' => $ce_erro,'v_abi' => $v_abi,'v_cab' => $v_cab,'totr' => $totr,'tot_abi'=>$tot_abi,'tot_cab'=>$tot_cab);   
    }
    // **************************************
    
}

?>