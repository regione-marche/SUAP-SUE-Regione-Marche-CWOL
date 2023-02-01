<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFtpUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE_MONITOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';


define("CARTELLA_ENTRATA", ("assistenza" . DIRECTORY_SEPARATOR . "entrata"));
define("CARTELLA_SCARTATI", ("assistenza" . DIRECTORY_SEPARATOR . "scartati"));
define("CARTELLA_ELABORATI", ("assistenza" . DIRECTORY_SEPARATOR . "elaborati"));
define("DB_NAME", 'CW_MONITOR_CLIENTI');
define("LOG_TABLE_NAME", 'bge_log');

/**
 * Helper per funzioni del monitor 
 * 
 */
class cwbBgeMonitorHelper {

    const ROSSO = 1;
    const GIALLO = 50;
    const ARANCIONE = 90;
    const VIOLA = 91;
    const VERDE = 92;
    const ESCLUDI_VERDE = 99;
    const TUTTI = 0;
    const SOLO_ATTIVI = 200;
    const NON_ATTIVO = 100;
    const AMBITO_SIOPE_PLUS = 5;
    const AMBITO_UPDATER = 4;
    const AMBITO_PAGOPA = 3;
    const AMBITO_SCHEDULAZIONE = 2;
    const AMBITO_FATTURAZIONE = 1;
    
    const FTP_PASSIVO_FTPM = 0;
    const FTP_HOST_FTPM = 'ftp.cityware.it';
    const FTP_USER_FTPM = 'citywareit_master';

    private $lastErrorCode;
    private $lastErrorDescription;
    private $messaggiErrore = array(// ordinati per importanza
        1 => 'Errore di Connessione al Server Documentale',
        2 => 'Impossibile Connettersi al Server FTP',
        3 => 'Errore copia su backup',
        4 => 'Errore rimozione supporto da FTP',
        5 => 'Errore download supporto',
        6 => 'Errore inserimento su documentale',
        7 => "Errore allineamento indici su Database",
        8 => "Errore aggiornamento",
        9 => 'org.alfresco',
        10 => 'Aggiornamento fallito',
        91 => 'Errore interno SmartAgent',
        50 => 'Flusso già presente',
        51 => 'Flusso gia presente',
        53 => 'Sospese:',
        93 => 'Emissione non pronta per essere pubblicata' // questo errore deve tornare colore verde (errore non importante)
    );
    private $statoFattura = array(
        1 => 'Errore importazione',
        2 => 'Importazione eseguita',
        3 => 'Nessuna fattura importata'
    );
    private $customMethod = array(
        1 => 'parseRecordCustomFatturazione',
        3 => 'parseRecordCustomPagoPa'
    );

    /**
     * Controlla se ci sono dei documenti da elaborare
     */
    public function filesToProcess() {
        try {
            $this->resetLastError();

            // connetti ftp       
            $ftp_conn = $this->openConnection();
            if (!$ftp_conn) {
                return false;
            }

            $directory = CARTELLA_ENTRATA;
            $listOfFileSchedulazioni = itaFtpUtils::getFilesList($ftp_conn, $directory);

            $directory = CARTELLA_SCARTATI;
            $listOfFileSegnalazioni = itaFtpUtils::getFilesList($ftp_conn, $directory);

            $toCount = array_merge($listOfFileSchedulazioni, $listOfFileSegnalazioni);

            $toReturn = 0;
            foreach ($toCount as $value) {
                // controlla se finisce per .xml
                if (preg_match('/.xml$/', $value)) {
                    $toReturn++;
                }
            }

            return $toReturn;
        } catch (Exception $exc) {
            $this->handleError(-1, "Eccezione " + $exc->getMessage());
            return false;
        }
    }

    /**
     * Viene chiamato da Mule per avvertire che è arrivato qualcosa sull'ftp
     */
    public function wakeup() {
        try {
            $this->resetLastError();

            $toReturn = null;

            $ftp_conn = $this->openConnection();
            if (!$ftp_conn) {
                return false;
            }

            // leggo i file nella cartella entrata
            $directory = CARTELLA_ENTRATA;
            $listOfFileSchedulazioni = itaFtpUtils::getFilesList($ftp_conn, $directory);

            foreach ($listOfFileSchedulazioni as $fileToRead) {
                // controlla se finisce per .xml
                if (preg_match('/.xml$/', $fileToRead)) {
                    // se è un xml  
                    $this->eseguiOperazione($ftp_conn, $fileToRead, $toReturn);
                }
            }

            // leggo i file nella cartella scartati
            $directory = CARTELLA_SCARTATI;
            $listOfFileSegnalazioni = itaFtpUtils::getFilesList($ftp_conn, $directory);

            foreach ($listOfFileSegnalazioni as $fileToRead) {
                if (preg_match('/.xml$/', $fileToRead)) {
                    // se è un xml  
                    $this->eseguiOperazione($ftp_conn, $fileToRead, $toReturn, 1);
                }
            }

            itaFtpUtils::closeConnection($ftp_conn);

            if($toReturn === null){
                $toReturn = 'Niente da scaricare';
            }
            
            return $toReturn;
        } catch (Exception $exc) {
            $this->handleError(-1, "Eccezione " + $exc->getMessage());
            return false;
        }
    }

    private function openConnection() {
        $lib = new cwbLibDB_BGE_MONITOR();
        $params = $lib->leggiBgeParams();

        $host = $params['ftp_host'];
        $user = $params['ftp_user'];
        $password = $params['ftp_psw'];

        // connetti ftp        
        $ftp_conn = itaFtpUtils::openFtpConnection($host, $user, $password);

        if (!$ftp_conn) {
            $this->handleError(-1, "Impossibile connettersi con l'utente FTP $user all'host $host.");
            return false;
        }

        return $ftp_conn;
    }

    private function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    private function eseguiOperazione($ftp_conn, $fileToRead, &$toReturn, $scartati = 0) {
        try {
            $db = ItaDB::DBOpen(DB_NAME, '');

            $toReturn[$fileToRead] = array();

            $info = pathinfo($fileToRead);

            $nomeFile = $info['basename'];

            if ($this->checkXmlInsert($ftp_conn, $fileToRead, $db)) {
                $toReturn[$fileToRead]['ESITO'] = false;
                $toReturn[$fileToRead]['ERR'] = 'Gia inserito';

                return false;
            }

            $contentXml = itaFtpUtils::getBinaryFileFromFtp($ftp_conn, $fileToRead);
            if (!$contentXml) {
                $toReturn[$fileToRead]['ESITO'] = false;
                $toReturn[$fileToRead]['ERR'] = 'Errore caricamento binario xml da ftp';
                return false;
            } else {
                $modelService = itaModelServiceFactory::newModelService('');

                $xmlObj = new itaXML;
                $retXml = $xmlObj->setXmlFromString($contentXml);

                if (!$retXml) {
                    $err = "File XML . Impossibile leggere il testo nell'xml.";
                    $toReturn[$fileToRead]['ESITO'] = false;
                    $toReturn[$fileToRead]['ERR'] = $err;
                    return false;
                }
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                if (!$arrayXml) {
                    $err = "Lettura XML. Impossibile estrarre i dati.";
                    $toReturn[$fileToRead]['ESITO'] = false;
                    $toReturn[$fileToRead]['ERR'] = $err;
                    return false;
                }

                $dataOra = $this->getStringBetween($nomeFile, '-', '.');
                $data = DateTime::createFromFormat("YmdHis", $dataOra);

                $ente = strtok($nomeFile, '-');

                $nomeMetodo = null;
                $nomeObj = null;
                $desente = null;
                if ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva']) {
                    foreach ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva'] as $value) {
                        if ($value['chiave'][0]['@textNode'] == 'NOMEOBJ') {
                            $nomeObj = $value['valore'][0]['@textNode'];
                        } else if ($value['chiave'][0]['@textNode'] == 'NOMEMETODO') {
                            $nomeMetodo = $value['valore'][0]['@textNode'];
                        } else if ($value['chiave'][0]['@textNode'] == 'DESENTE') {
                            $desente = $value['valore'][0]['@textNode'];
                        }
                    }
                }

                // provo a caricare lo zip associato, se non c'e' getBinaryFileFromFtp ritorna false
                $fileToReadZip = str_replace(".xml", ".zip", $fileToRead);
                $contentZip = itaFtpUtils::getBinaryFileFromFtp($ftp_conn, $fileToReadZip);

                $metodo = ($nomeObj || $nomeMetodo) ? ($nomeObj . '.' . $nomeMetodo) : "";

                $esito = $arrayXml['esito'][0]['@textNode'];
                $ambito = $arrayXml['ambito'][0]['@textNode'];

                $templateToInsert = array(
                    'ENTE' => (int) $ente,
                    'DESENTE' => $desente,
                    'ORIGINE' => $arrayXml['origine'][0]['@textNode'],
                    'AMBITO' => $ambito,
                    'OGGETTO' => $arrayXml['oggetto'][0]['@textNode'],
                    'CORPO' => substr($arrayXml['corpo'][0]['@textNode'], 0, 1020),
                    'ESITO' => $esito,
                    'COLORE_ESITO' => $esito == 'OK' ? self::VERDE : $this->errorColor($contentXml), // se esito è positivo torno verde sennò mi calcolo il tipo di errore (il colore viola (basato su data esecuzione) viene calcolato a posteriori)
                    'NOMEMETODO' => $metodo,
                    'SCARTATI' => $scartati,
                    'XML' => $contentXml,
                    'ZIP' => $contentZip ? $contentZip : '',
                    'DATA' => $data->format('Y-m-d'),
                    'ORA' => $data->format('H:i:s'),
                    'PRESA_CARICO' => 0,
                    'UTE_PRESA_CARICO' => '',
                    'FILE_NAME' => $nomeFile
                );

                $toInsertList = array();

                $customMethod = $this->customMethod[$ambito];
                if ($customMethod) {
                    $arrayCustomData = $this->$customMethod($templateToInsert);

                    if ($ambito == 3) {
                        // se sono in ambito pagopa, da un xml genero n record in base alle info aggiuntive
                        foreach ($arrayCustomData as $value) {
                            // creo n record con gli stessi dati nella parte base ma con valori CUSTOM_DATA diversi
                            $jsonCustomData = json_encode($value);
                            $templateToInsert['CUSTOM_DATA'] = $jsonCustomData;
                            $toInsertList[] = $templateToInsert;
                        }
                    } else {
                        $jsonCustomData = json_encode($arrayCustomData);
                        $templateToInsert['CUSTOM_DATA'] = $jsonCustomData;
                        $toInsertList[] = $templateToInsert;
                    }
                } else {
                    $toInsertList[] = $templateToInsert;
                }

                foreach ($toInsertList as $toInsert) {
                    $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                    $modelServiceData->addMainRecord(LOG_TABLE_NAME, $toInsert);

                    $modelService->insertRecord($db, LOG_TABLE_NAME, $modelServiceData->getData(), "");
                }

                // sposto il file xml su elaborati
                $newFile = CARTELLA_ELABORATI . DIRECTORY_SEPARATOR . $nomeFile;
                if (itaFtpUtils::moveFile($ftp_conn, $fileToRead, $newFile)) {
                    $toReturn[$fileToRead]['ESITO'] = true;
                    $toReturn[$fileToRead]['ERR'] = '';
                } else {
                    $toReturn[$fileToRead]['ESITO'] = true;
                    $toReturn[$fileToRead]['ERR'] = 'Errore spostamento xml su cartella elaborati';
                }

                if ($contentZip) {
                    // sposto lo zip
                    $newFileZip = CARTELLA_ELABORATI . DIRECTORY_SEPARATOR . $info['filename'] . '.zip';
                    itaFtpUtils::moveFile($ftp_conn, $fileToReadZip, $newFileZip);
                }

                $this->insertEnte($ente, $desente, $db);

                return true;
            }
        } catch (Exception $exc) {
            $toReturn[$fileToRead]['ESITO'] = false;
            $toReturn[$fileToRead]['ERR'] = 'Eccezione ' . $exc->getMessage();
            return false;
        }
    }

    // chiamato in maniera dinamica da '$customMethod'
    private function parseRecordCustomFatturazione($Result_rec) {
        try {
            // se è fatturazione e c'è un xml leggo dall'xml stato e nr.fatture
            if ($Result_rec['XML']) {
                $xmlObj = new itaXML;
                $xmlObj->setXmlFromString($Result_rec['XML']);
                if ($xmlObj->asObject()) {
                    $arrayXml = $xmlObj->toArray($xmlObj->asObject());

                    $stato = null;

                    $listOfDettaglio = array();
                    if ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva']) {
                        foreach ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva'] as $value) {
                            if ($value['chiave'][0]['@textNode'] == 'STATO') {
                                $stato = $value['valore'][0]['@textNode'];
                            } else if (preg_match('#^DETTAGLIO_#', $value['chiave'][0]['@textNode']) === 1) {
                                // se inizia per DETTAGLIO_ aggiungo in lista il tag dettaglio_n_
                                preg_match('/DETTAGLIO_[0-9]+_/', $value['chiave'][0]['@textNode'], $return);
                                // inserisco il valore trovato come chiave di un array per evitare di avere
                                // valori doppi. Poi alla fine conto le chiavi per vedere quante ce ne sono
                                $listOfDettaglio[$return[0]] = 0;
                            }
                        }
                    }

                    $nfatture = count($listOfDettaglio);

                    return array('NFATTURE' => $nfatture, 'STATO' => $this->statoFattura[$stato]);
                }
            }
        } catch (Exception $ex) {
            
        }

        return array();
    }

    private function parseRecordCustomPagoPa($Result_rec) {
        try {
            // se è fatturazione e c'è un xml leggo dall'xml stato e nr.fatture
            if ($Result_rec['XML']) {
                $xmlObj = new itaXML;
                $xmlObj->setXmlFromString($Result_rec['XML']);
                if ($xmlObj->asObject()) {
                    $arrayXml = $xmlObj->toArray($xmlObj->asObject());

                    $listOfDettaglio = array();
                    if ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva']) {
                        foreach ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva'] as $value) {
                            if (preg_match('#^DETTAGLIO_#', $value['chiave'][0]['@textNode']) === 1) {
                                // se inizia per DETTAGLIO_ aggiungo in lista il tag dettaglio_n_
                                preg_match('/DETTAGLIO_[0-9]+_/', $value['chiave'][0]['@textNode'], $return);
                                // inserisco il valore trovato come chiave di un array per evitare di avere
                                // valori doppi. Poi alla fine conto le chiavi per vedere quante ce ne sono
                                $nomeCampoSenzaDetail = substr($value['chiave'][0]['@textNode'], strpos($value['chiave'][0]['@textNode'], $return[0]) + strlen($return[0]));

                                $listOfDettaglio[$return[0]][$nomeCampoSenzaDetail] = $value['valore'][0]['@textNode'];

                                if ($nomeCampoSenzaDetail == 'STATO') {
                                    // creo un xml con lo stato dentro des_errore da passare a errorColor (lo aspetta in quel formato)
                                    $stato = "<segnatura><info_aggiuntive><info_aggiuntiva><chiave><![CDATA[DETTAGLIO_1_DES_ERRORE]]></chiave><valore><![CDATA[" . $value['valore'][0]['@textNode'] . "]]></valore></info_aggiuntiva></info_aggiuntive></segnatura>";
                                }
                                if ($nomeCampoSenzaDetail == 'ESITO') {
                                    $esito = $value['valore'][0]['@textNode'];
                                    $listOfDettaglio[$return[0]]['COLORE_ESITO'] = ($esito == 'OK') ? self::VERDE : $this->errorColor($stato);
                                }
                            }
                        }
                    }
                    return $listOfDettaglio;
                }
            }
        } catch (Exception $ex) {
            
        }

        return array();
    }

    // in alcuni casi da errore lo spostamento da entrata a elaborati e quindi
    // l'xml, rimanendo su entrata, verrebbe letto 2 volte. Quindi controllo per nome
    // se è già stato letto
    private function checkXmlInsert($ftp_conn, $fileToRead, $db) {
        if (!$db) {
            $db = ItaDB::DBOpen(DB_NAME, '');
        }
        try {
            $info = pathinfo($fileToRead);
            $fileName = $info['basename'];

            // se non trovo l'ente su bge_enti lo inserisco
            $ente = strtok($fileName, '-');
            $lib = new cwbLibDB_BGE_MONITOR();
            $logGiaLetto = $lib->leggiBgeLog(array('FILE_NAME' => $fileName, 'ENTE' => $ente));
            if ($logGiaLetto) {
                // se trovo su db il fileName significa che è già stato letto ma 
                // ha dato errore la move su cartella elaborati
                try {
                    $newFile = CARTELLA_ELABORATI . DIRECTORY_SEPARATOR . $fileName;
                    itaFtpUtils::moveFile($ftp_conn, $fileToRead, $newFile);

                    // sposto lo zip, se non c'è non fa niente
                    $newFileZip = CARTELLA_ELABORATI . DIRECTORY_SEPARATOR . $info['filename'] . '.zip';
                    $fileToReadZip = str_replace(".xml", ".zip", $fileToRead);
                    itaFtpUtils::moveFile($ftp_conn, $fileToReadZip, $newFileZip);
                } catch (Exception $e) {
                    $e->getMessage();
                }
                return true;
            }
        } catch (Exception $ex) {
            $ex->getMessage();
        }

        return false;
    }

    public function insertEnte($ente, $desente, $db) {
        if (!$db) {
            $db = ItaDB::DBOpen(DB_NAME, '');
        }
        try {
            // se non trovo l'ente su bge_enti lo inserisco
            $lib = new cwbLibDB_BGE_MONITOR();
            $enti = $lib->leggiBgeEnti(array('ENTE' => $ente));
            if (!$enti) {
                $val = array('DESENTE' => $desente, 'ENTE' => $ente, 'ATTIVO' => 1);
                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                $modelServiceData->addMainRecord("bge_enti", $val);
                $modelService = cwbModelServiceFactory::newModelService("");

                $modelService->insertRecord($db, "bge_enti", $modelServiceData->getData(), "");
            } else {
                // se trovo l'ente verifico che sia attivo, sennò lo attivo io 
                // (ho ricevuto un xml da lui quindi è attivo)
                if (!$enti[0]['ATTIVO']) {
                    $toUpdate = $enti[0];
                    $toUpdate['ATTIVO'] = 1;
                    $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                    $modelServiceData->addMainRecord("bge_enti", $toUpdate);
                    $modelService = cwbModelServiceFactory::newModelService("");

                    $modelService->updateRecord($db, "bge_enti", $modelServiceData->getData(), "");
                }
            }
        } catch (Exception $ex) {
            $ex->getMessage();
        }
    }

    // scorre i messaggi di errore per capire la gravità e di conseguenza il colore 
    private function errorColor($xml) {
        // scorro l'xml per cercare DETTAGLIO_n e da li vedere la gravità dell'errore
        if ($xml) {
            $xmlObj = new itaXML;
            $xmlObj->setXmlFromString($xml);
            $obj = $xmlObj->asObject();
            if ($obj) {
                $arrayXml = $xmlObj->toArray($obj);

                $errMsgs = array();

                if ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva']) {
                    foreach ($arrayXml['info_aggiuntive'][0]['info_aggiuntiva'] as $value) {
                        if (preg_match('/DETTAGLIO_[0-9]+_DES_ERRORE/', $value['chiave'][0]['@textNode']) === 1 && $value['valore'][0]['@textNode']) {
                            // se sto su DETTAGLIO_n_DES_ERRORE e' c'e' un value, me lo salvo (array con i msg di errore).
                            // converto da utf-8 a iso perché l'oggetto itaXml è utf
                            $errMsgs[] = strtolower(mb_convert_encoding($value['valore'][0]['@textNode'], 'ISO-8859-1', 'UTF-8'));
                        }
                    }
                }

                if ($errMsgs) {
                    // scorro tutti i messaggi di errore censiti in ordine di gravità e 
                    // controllo se sono presenti nell'array $errMsgs calcolato dall'xml
                    foreach ($this->messaggiErrore as $key => $err) {
                        foreach ($errMsgs as $messaggioErr) {
                            if (preg_match('/' . strtolower($err) . '/', $messaggioErr)) { // controllo in like se contiene messaggio
                                return $key; //se ne trovo uno lo ritorno (il primo in ordine di gravita)
                            }
                        }
                    }

                    // se ci sono errori ma non sono tra quelli censiti torno direttamente arancione
                    return self::ARANCIONE;
                }
            }
        }

        // se per qualche motivo non ho trovato niente torno rosso
        return self::ROSSO;
    }

    public function generaInviaXmlMonitor($data, $nomeFile, $zipFile = null) {
        $xmlObj = new itaXML;
        $xmlObj->toXML($data, "segnatura");
        $xml = $xmlObj->asXML();

        $passivo = array('CONFIG'=>self::FTP_PASSIVO_FTPM);
        $host = array('CONFIG'=>self::FTP_HOST_FTPM);
        $user = array('CONFIG'=>self::FTP_USER_FTPM);
        
        if(isSet($passivo['CONFIG'])){
            $passivo = $passivo['CONFIG'];
        }
        if(isSet($host['CONFIG'])){
            $host = $host['CONFIG'];
        }
        if(isSet($user['CONFIG'])){
            $user = $user['CONFIG'];
        }

        $ftp_conn = itaFtpUtils::openFtpConnection($host, $user, '5KM80bx6TG', $passivo);
        //todo prendere da DB cartella destinazione FTP
        if (!itaFtpUtils::writeFileFromBinary($ftp_conn, 'assistenza/entrata/' . $nomeFile . '.xml', $xml)) {
            itaFtpUtils::closeConnection($ftp_conn);
            return false;
        }
        if ($zipFile) {
            //todo prendere da DB cartella destinazione FTP
            itaFtpUtils::writeFileFromPath($ftp_conn, 'assistenza/entrata/' . $nomeFile . '.zip', $zipFile);
        }
        itaFtpUtils::closeConnection($ftp_conn);
        return true;
    }

    // Crea XML con info schedulazione pagopa
    public function creaXmlMonitorSchedulazionePagoPa($esito, $corpo) {
        $oggetto = 'Schedulazione Pago Pa: ' . $esito ? 'Esito Positivo' : 'Esito Negativo';

        // genero xml per la schedulazione di pagopa (tab schedulazione del monitor)
        $result = $this->buildArrayMonitorBase($oggetto, $corpo, $esito, $nomeFile, 2);

        $this->setInfoAggiuntiva('NOMEMETODO', "sincronizzazioneNodoPagoPA", $result);
        $this->setInfoAggiuntiva('NOMEOBJ', "CWOL", $result);
        $this->setInfoAggiuntiva('DETTAGLIO_1_DES_ERRORE', $esito ? "" : $corpo, $result);

        $this->generaInviaXmlMonitor($result, $nomeFile);
    }

    // Crea XML con info aggiornamento con Updater
    public function creaXmlMonitorUpdater($esito, $corpo) {
        $oggetto = 'Aggiornamento tramite updater: ' . ($esito ? 'Esito Positivo' : 'Esito Negativo');

        $result = $this->buildArrayMonitorBase($oggetto, $corpo, $esito, $nomeFile, 4);

        $this->setInfoAggiuntiva('NOMEMETODO', "applyDiff", $result);
        $this->setInfoAggiuntiva('NOMEOBJ', "CWOL", $result);
        $this->setInfoAggiuntiva('DETTAGLIO_1_DES_ERRORE', $esito ? "" : utf8_encode($corpo), $result);

        $this->generaInviaXmlMonitor($result, $nomeFile);
    }

    // Crea XML con info aggiornamento con Siope Plus
    public function creaXmlMonitorSiopePlus($esito, $corpo) {
        $oggetto = 'Log Esiti Siope Plus: ' . ($esito ? 'Esito Positivo' : 'Esito Negativo');

        $result = $this->buildArrayMonitorBase($oggetto, $corpo, $esito, $nomeFile, 5);

        $this->setInfoAggiuntiva('NOMEMETODO', "resultEsiti", $result);
        $this->setInfoAggiuntiva('NOMEOBJ', "CWOL", $result);
        $this->setInfoAggiuntiva('DETTAGLIO_1_DES_ERRORE', $esito ? "" : utf8_encode($corpo), $result);

        $this->generaInviaXmlMonitor($result, $nomeFile);
    }

    private function setInfoAggiuntiva($key, $value, &$result) {
        $infoAggiuntive = array();
        $infoAggiuntive['chiave']['@textNode'] = $key;
        $infoAggiuntive['valore']['@textNode'] = utf8_encode($value);
        $result['segnatura']['info_aggiuntive']['info_aggiuntiva'][] = $infoAggiuntive;
    }

    // Crea XML con info stato emissioni da visualizzare nel monitor eventi (tab pagopa).
    public function creaXmlMonitorRiepilogoPagoPa() {
        $libDB_BTA = new cwbLibDB_BTA();
        $libDB_BGE = new cwbLibDB_BGE();
        $libDB_BWE = new cwbLibDB_BWE();

        $esitoGlobale = true;

        $emissioni = $libDB_BTA->leggiBtaServrend();
        foreach ($emissioni as $emissione) {
            $esitoSingolo = true;
            $filtri = array();
            $filtri['ANNOEMI'] = $emissione['ANNOEMI'];
            $filtri['NUMEMI'] = $emissione['NUMEMI'];
            $filtri['IDBOL_SERE'] = $emissione['IDBOL_SERE'];
            $situazEmissioni = $libDB_BGE->leggiBgeAgidScadenzePerEmissione($filtri, false);
            $dataInvio = $libDB_BGE->leggiBgeAgidInviiDatainvio($filtri, false);
            $scadenzeNoBwePenden = count($libDB_BGE->leggiBgeAgidScadenzeNoBwePenden());
            if ($situazEmissioni) {
                $statoEmissione = "Totale Scadenze:" . $situazEmissioni['TOTALE'] . " - ";

                if ($situazEmissioni['PUBBLICATO'] > 0) {
                    $statoEmissione .= "<font color='green'>Pubblicate:" . $situazEmissioni['PUBBLICATO'] . "</font>";
                    $dataInvio ? $statoEmissione .= ", <u>inviate il " . date('d-m-Y', strtotime($dataInvio['DATAINVIO'])) . "</u>" : "";
                } else {
                    $statoEmissione .= "Inviate:" . $situazEmissioni['INVIATO'];
                    $dataInvio ? $statoEmissione .= ", <u>il " . date('d-m-Y', strtotime($dataInvio['DATAINVIO'])) . "</u>" : "";
                }
                if ($situazEmissioni['SOSPESO'] > 0) {
                    $esitoGlobale = false;
                    $esitoSingolo = false;
                    $statoEmissione .= "<font color='red'>" . "Sospese:" . $situazEmissioni['SOSPESO'] . '</font>';
                }

                if ($situazEmissioni['RICONCILIATO'] > 0) {
                    $statoEmissione .= " - <font color='green'>" . "Riconciliate:" . $situazEmissioni['RICONCILIATO'] . '</font>';
                }

                if ($situazEmissioni['CANCELLATO'] > 0) {
                    $statoEmissione .= " - " . "Cancellate:" . $situazEmissioni['CANCELLATO'];
                }

                if ($situazEmissioni['RENDICONTATO'] > 0) {
                    $esitoGlobale = false;
                    $esitoSingolo = false;
                    $statoEmissione .= " - <font color='red'>Non Riconciliate: " . $situazEmissioni['RENDICONTATO'] . '</font>';
                }
                if ($situazEmissioni['CANCFALL'] > 0) {
                    $esitoGlobale = false;
                    $esitoSingolo = false;
                    $statoEmissione .= " - <font color='red'>Canc.Fallita: " . $situazEmissioni['CANCFALL'] . '</font>';
                }
                if (intval($scadenzeNoBwePenden) > 0) {
                    $esitoGlobale = false;
                    $esitoSingolo = false;
                    $statoEmissione .= " - <font color='red'>Scad.NO BWE_PENDEN: " . $scadenzeNoBwePenden . '</font>';
                }
            } else {
                $scadenzeDaPubbl = $libDB_BWE->leggiBwePendenScadenze($filtri, false);
                if ($scadenzeDaPubbl) {
                    $statoEmissione = 'Sono presenti ' . count($scadenzeDaPubbl) . ' Scadenze da Pubblicare';
                } else {
                    $esitoGlobale = false;
                    $esitoSingolo = false;
                    $statoEmissione = 'Emissione non pronta per essere pubblicata.'
                            . 'Verificare la tabella BWE_PENDEN e il flag pubblicabile!';
                }
            }

            $servRend[$emissione['PROGKEYTAB']] = array(
                'TIPORIFCRED' => $emissione['TIPORIFCRED'],
                'SITUAZEMISSIONE' => $statoEmissione,
                'ANNOEMI' => $emissione['ANNOEMI'],
                'NUMEMI' => $emissione['NUMEMI'],
                'IDBOL_SERE' => $emissione['IDBOL_SERE'],
                'DES_GE60' => utf8_encode($emissione['DES_GE60']),
                'CODSERVIZIO' => $emissione['CODSERVIZIO'],
                'ESITO' => $esitoSingolo ? 'OK' : 'KO'
            );
        }
        if ($servRend) {
            $arrayMonitor = $this->buildArrayMonitorRiepilogo('Riepilogo Operazioni Pago Pa', $esitoGlobale ? 'Nessun Errore' : 'Presenti Errori', $esitoGlobale, $nomeFile, $servRend);
            $this->generaInviaXmlMonitor($arrayMonitor, $nomeFile);
        } else {
            $servRend[0] = array(
                'TIPORIFCRED' => 'Nessuna',
                'SITUAZEMISSIONE' => 'Nessuna emissione pubblicata sul nodo PagoPA',
                'ANNOEMI' => 0,
                'NUMEMI' => 0,
                'IDBOL_SERE' => 0,
                'DES_GE60' => 'Nessuna',
                'CODSERVIZIO' => 0,
                'ESITO' => 'OK'
            );
            $arrayMonitor = $this->buildArrayMonitorRiepilogo('Riepilogo Operazioni Pago Pa', 'Nessun Errore', true, $nomeFile, $servRend);
            $this->generaInviaXmlMonitor($arrayMonitor, $nomeFile);
        }
    }

    private function buildArrayMonitorBase($oggetto, $corpo, $esito, &$nomeFile, $ambito) {
        $result = array();
        $ente = cwbParGen::getBorEnti();
        if(empty($ente)){
            $ITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
            $domains_rec = ItaDB::DBSQLSelect($ITALWEBDB, 'SELECT CODICE FROM DOMAINS ORDER BY SEQUENZA ASC', false);
            $enteCityware = $domains_rec['CODICE'];
            
            include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
            
            $libDB_BOR = new cwbLibDB_BOR();
            $libDB_BOR->setEnte($enteCityware);
            $clienti = $libDB_BOR->leggiBorClient();
            
            $ente = $libDB_BOR->leggiBorEntiClient($clienti[0]['PROGCLIENT']);
        }
        $progclient = str_pad(trim($ente[0]['PROGCLIENT']), 5, "0", STR_PAD_LEFT);
        $nomeFile = $progclient . "-" . date('Ymd') . date("His");

        $result['segnatura']['nomefile']['@textNode'] = $nomeFile . ".xml";
        $result['segnatura']['origine']['@textNode'] = 4;
        $result['segnatura']['ambito']['@textNode'] = $ambito;
        $result['segnatura']['oggetto']['@textNode'] = utf8_encode($oggetto);
        $result['segnatura']['corpo']['@textNode'] = utf8_encode($corpo);
        $result['segnatura']['esito']['@textNode'] = $esito ? 'OK' : 'KO';

        $this->setInfoAggiuntiva('PROGCLIENT', $ente[0]['PROGCLIENT'], $result);
        $this->setInfoAggiuntiva('DESENTE', $ente[0]['DESENTE'], $result);

        return $result;
    }

    // Genera array per inviare dati all'FTP di Cityware
    private function buildArrayMonitorRiepilogo($oggetto, $corpo, $esito, &$nomeFile, $servRend) {
        $result = $this->buildArrayMonitorBase($oggetto, $corpo, $esito, $nomeFile, 3);

        $count = 1;
        foreach ($servRend as $progkeytab => $emissione) {
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_PROGKEYTAB_SERVIZIO', $progkeytab, $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_TIPORIFCRED', $emissione['TIPORIFCRED'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_STATO', $emissione['SITUAZEMISSIONE'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_ANNOEMI', $emissione['ANNOEMI'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_NUMEMI', $emissione['NUMEMI'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_IDBOL_SERE', $emissione['IDBOL_SERE'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_DES_GE60', $emissione['DES_GE60'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_CODSERVIZIO', $emissione['CODSERVIZIO'], $result);
            $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_ESITO', $emissione['ESITO'], $result);
            if ($emissione['ESITO'] === 'KO') {
                $this->setInfoAggiuntiva('DETTAGLIO_' . $count . '_DES_ERRORE', $emissione['SITUAZEMISSIONE'], $result);
            }
            $count++;
        }
        return $result;
    }

    public function handleError($code, $description) {
        $this->setLastErrorCode($code);
        $this->setLastErrorDescription($description);
    }

    public function resetLastError() {
        $this->setLastErrorCode(0);
        $this->setLastErrorDescription("");
    }

    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    public function getLastErrorDescription() {
        return $this->lastErrorDescription;
    }

    public function setLastErrorCode($lastErrorCode) {
        $this->lastErrorCode = $lastErrorCode;
    }

    public function setLastErrorDescription($lastErrorDescription) {
        $this->lastErrorDescription = $lastErrorDescription;
    }

}
