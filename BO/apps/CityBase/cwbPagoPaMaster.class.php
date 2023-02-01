<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDBRequest.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once (ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once (ITA_LIB_PATH . '/itaPHPPagoPa/iPagoPa.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaHelper.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtTributiHelper.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TBA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaNumeratori.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

abstract class cwbPagoPaMaster implements iPagoPa {

    const URL_NOTIFICA = "/wsrest/service.php/pagoPA/rendicontazionePuntuale?token=@TOKEN&CodRiferimento=@CODRIF&urlverticale=@URL";
    const URL_NOTIFICA_GET = "/wsrest/service.php/pagoPA/rendicontazionePuntualeGet?token=@TOKEN&CodRiferimento=@CODRIF&urlverticale=@URL";
    const STATO_PAGAMENTO_PAGATO = "Pagata";
    const STATO_PAGAMENTO_NONPAGATO = "NonPagata";
    const CHIAVE_NUMERATORE_IUV = "KIUV11";

    private $CITYWARE_DB;
    private $libDB_BGE;
    private $libDB_BOR;
    private $libDB_BWE;
    private $libDB_BTA;
    private $simulazione;
    private $lastErrorCode;
    private $lastErrorDescription;
    private $statoRend = array(1 => 'Rendicontato puntuale (via ws)',
        2 => 'Rendicontato massivo',
        3 => 'Riversato massivo',
        11 => 'in sequenza 1(puntuale),2(rend),3(riv)',
        12 => 'in sequenza 1(puntuale),3(riv),2(rend)',
        13 => '2-rendicontato massivo,3-riversato massivo',
        14 => '3-riversato massivo,2-rendicontato massivo',
    );

    public function __construct() {
        $this->libDB_BGE = new cwbLibDB_BGE();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->libDB_BWE = new cwbLibDB_BWE();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $logPath = ITA_BASE_PATH . '/var/log/pagopa.txt';
        $this->logger = new itaPHPLogger('cwbPagoPaMaster', false);
        $this->logger->pushFile($logPath);
        $this->connettiDB();
    }

    public function inserisciBgeScadenze($toInsert, $startedTransaction = true) {
        $toInsert['DATACREAZ'] = date('Y-m-d');
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCADENZE', true, $startedTransaction);
    }

    public function inserisciBwePenden($toInsert) {
        return $this->insertUpdateRecord($toInsert, 'BWE_PENDEN', true, true);
    }

    public function inserisciBwePenddet($toInsert) {
        return $this->insertUpdateRecord($toInsert, 'BWE_PENDDET', true, true);
    }

    public function inserisciBgeAgidSoggetti($toInsert, $startedTransaction = false) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SOGGETTI', true, $startedTransaction);
    }

    public function aggiornaBgeAgidSoggetti($toUpdate, $startedTransaction = false) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SOGGETTI', false, $startedTransaction);
    }

    public function inserisciBgeAgidScainfo($progkeytab, $chiave, $valore, $startedTransaction = true) {
        $toInsert = array(
            'IDSCADENZA' => $progkeytab,
            'CHIAVE' => $chiave,
            'VALORE' => $valore
        );
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCAINFO', true, $startedTransaction);
    }

    public function aggiornaBgeScadenze($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCADENZE', false, $startedTransaction);
    }

//    public function inserisciBgeAgidScaNss($toInsert, $startedTransaction = true) {
//        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCA_NSS', true, $startedTransaction);
//    }

    public function inserisciBgeAgidInvii($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_INVII', true, $startedTransaction);
    }

    public function aggiornaBgeAgidInvii($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_INVII', false, $startedTransaction);
    }

    public function aggiornaBgeAgidRisco($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_RISCO', false, $startedTransaction);
    }

    public function aggiornaBgeAgidScadenze($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCADENZE', false, $startedTransaction);
    }

    public function inserisciAggiornaBgeAgidScaInfo($idScadenza, $listaScainfo, $startedTransaction = true) {
        try {
            if (!$startedTransaction) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
            }

            foreach ($listaScainfo as $keyScainfo => $scainfo) {
                $filtri = array(
                    'CHIAVE' => $keyScainfo,
                    'IDSCADENZA' => $idScadenza
                );
                $recScainfo = $this->libDB_BGE->leggiBgeAgidScainfo($filtri, false);
                if ($recScainfo) {
                    // se lo trova aggiorna
                    $recScainfo['VALORE'] = $scainfo;
                    $this->insertUpdateRecord($recScainfo, 'BGE_AGID_SCAINFO', false, true);
                } else {
                    // se non lo trova inserisce
                    $toInsert = array(
                        'CHIAVE' => $keyScainfo,
                        'IDSCADENZA' => $idScadenza,
                        'VALORE' => $scainfo,
                    );
                    $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCAINFO', true, true);
                }
            }

            if (!$startedTransaction) {
                cwbDBRequest::getInstance()->commitManualTransaction();
                return true;
            }
        } catch (Exception $ex) {
            if (!$startedTransaction) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            }
            return false;
        }
    }

    public function aggiornaBgeAgidScadet($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCADET', false, $startedTransaction);
    }

    public function aggiornaBgeAgidScadetiva($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCADETIVA', false, $startedTransaction);
    }

    public function inserisciBgeAgidAllegati($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_ALLEGATI', true, $startedTransaction);
    }

    public function inserisciBgeAgidRicez($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_RICEZ', true, $startedTransaction);
    }

    public function inserisciBgeAgidLog($toInsert, $startedTransaction = false) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_LOG', true, $startedTransaction);
    }

    public function inserisciBgeAgidRisco($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_RISCO', true, $startedTransaction);
    }

    public function inserisciBgeAgidStoscade($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_STOSCADE', true, $startedTransaction);
    }

    public function insertUpdateRecord($toInsert, $tableName, $insert = true, $startedTransaction = false) {
        if ($this->getSimulazioneSF() != true) {
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($tableName, $toInsert);
            $recordInfo = itaModelHelper::impostaRecordInfo(($insert ? itaModelService::OPERATION_INSERT : itaModelService::OPERATION_UPDATE), 'cwbPagoPaMaster', $toInsert);
            if ($insert) {
                $modelService->insertRecord($this->CITYWARE_DB, $tableName, $modelServiceData->getData(), $recordInfo);
            } else {
                $modelService->updateRecord($this->CITYWARE_DB, $tableName, $modelServiceData->getData(), $recordInfo);
            }
            return $modelService->getLastInsertId();
        }
        return null;
    }

    public function deleteRecord($toDelete, $tableName, $startedTransaction = false) {
        if ($this->getSimulazioneSF() != true) {
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, $startedTransaction);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($tableName, $toDelete);
            $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_DELETE, 'cwbPagoPaMaster', $toDelete);
            $modelService->deleteRecord($this->CITYWARE_DB, $tableName, $modelServiceData->getData(), $recordInfo);
        }
    }

    public function leggiScadenzaPerInfoAggiuntive($arrayFiltri, $multipla) {
        return cwbPagoPaHelper::getScadenzaPerInfoAggiuntive($arrayFiltri, $multipla);
    }

    protected function leggiScadenzePerPubblicazioni($progkeytabs = null, $stato = null, $page = null) {
        return null;
    }

    protected function customFormatoAnnoCodRiferimento() {
        return 4;
    }

    // BWE_PENDEN
    protected function leggiScadenzePerInserimento($filtri = array(), $page = null) {
        return null;
    }

    protected function getEmissioniPerPubblicazione($filtri = array()) {
        return null;
    }

    /**
     * Crea agid_scadenze con stato 1 o 3 e salva i dati dell'f24 su tba_dati_scambio
     * @return type false oppure array-> array(
     * 'ANNOEMI' => xxx,
     * 'NUMEMI' => xxx,
     * 'IDBOL_SERE' => xxx,
     * 'ESITO' => true/false,
     * 'SCADENZE_PUBBLICATE' => array(array(
     * 'PROGKEYTAB' => progkeytab scadenza,
     * 'ESITO' => true/false,
     * 'MESSAGGIO' => $msg errore eventuale
     * ));
     * );
     */
    public function inserimentoMassivo() {
        $this->rimuoviScadenzeDaRipristinare();
        $emissioni = $this->getEmissioniPerPubblicazione();
        $risultato = array();
        foreach ($emissioni as $emissione) {
            $ret = $this->pubblicazioneMassivaDaChiaveEmissioneBase($emissione['ANNOEMI'], $emissione['NUMEMI'], $emissione['IDBOL_SERE'], true);
            $risultato[] = array(
                'ANNOEMI' => $emissione['ANNOEMI'],
                'NUMEMI' => $emissione['NUMEMI'],
                'IDBOL_SERE' => $emissione['IDBOL_SERE'],
                'ESITO' => $ret ? true : false,
                'SCADENZE_PUBBLICATE' => $ret ? $ret : array()
            );
        }

        return $risultato;
    }

    /**
     * Pubblica sul nodo tutte le scadenze gia create e che hanno stato 1
     * @return type false oppure array-> array(
     * 'ANNOEMI' => xxx,
     * 'NUMEMI' => xxx,
     * 'IDBOL_SERE' => xxx,
     * 'ESITO' => true/false,
     * 'SCADENZE_PUBBLICATE' => array(array(
     * 'PROGKEYTAB' => progkeytab scadenza,
     * 'ESITO' => true/false,
     * 'MESSAGGIO' => $msg errore eventuale
     * ));
     * );
     */
    public function pubblicazioneScadenzeCreateMassiva() {
        $libDB_BGE = new cwbLibDB_BGE();
        // scadenze con stato = 1
        $results = array();
        $page = 0;
        // leggo sempre i primi n (customPaginatorSize) record tanto quelli con stato = 1 vengono spostati in altri stati
        while ($scadenzeDaPubbl = $this->leggiScadenzePerPubblicazioni(null, 1, $page)) {
            $result = $this->customPubblicazioneScadenzeCreateMassiva($scadenzeDaPubbl);
            if ($result) {
                $results[] = $result;
            }
            $page = $page + $this->customPaginatorSize();
        }

        return $results;
    }

    /**
     * Pubblica tutte le posizioni per ogni emissione
     * @return type false oppure array-> array(
     * 'ANNOEMI' => xxx,
     * 'NUMEMI' => xxx,
     * 'IDBOL_SERE' => xxx,
     * 'ESITO' => true/false,
     * 'SCADENZE_PUBBLICATE' => array(array(
     * 'PROGKEYTAB' => progkeytab scadenza,
     * 'ESITO' => true/false,
     * 'MESSAGGIO' => $msg errore eventuale
     * ));
     * );
     */
    public function pubblicazioneMassiva() {
        $this->rimuoviScadenzeDaRipristinare();
        $emissioni = $this->getEmissioniPerPubblicazione();
        $risultato = array();
        foreach ($emissioni as $emissione) {
            $ret = $this->pubblicazioneMassivaDaChiaveEmissione($emissione['ANNOEMI'], $emissione['NUMEMI'], $emissione['IDBOL_SERE']);
            $risultato[] = array(
                'ANNOEMI' => $emissione['ANNOEMI'],
                'NUMEMI' => $emissione['NUMEMI'],
                'IDBOL_SERE' => $emissione['IDBOL_SERE'],
                'ESITO' => $ret ? true : false,
                'SCADENZE_PUBBLICATE' => $ret ? $ret : array()
            );
        }

        return $risultato;
    }

    /**
     * Pubblica tutte le posizioni aperte di un emissione
     * @param type $annoEmi
     * @param type $numEmi
     * @param type $idBolSere
     * @return object false se non pubblica niente, array con esito per ogni pendenza se pubblica tutto o 
     * misto(caso di invio a blocchi in cui alcuni blocchi vengono pubblicati ed altri danno errore)
     * array(
     * 'PROGKEYTAB' => $scadenzaP['PROGKEYTAB'],
     * 'ESITO' => $esito,
     * 'MESSAGGIO' => $msg
     * );
     */
    public function pubblicazioneMassivaDaChiaveEmissione($annoEmi, $numEmi, $idBolSere) {
        return $this->pubblicazioneMassivaDaChiaveEmissioneBase($annoEmi, $numEmi, $idBolSere);
    }

    private function pubblicazioneMassivaDaChiaveEmissioneBase($annoEmi, $numEmi, $idBolSere, $saltaPubblicazione = false) {
        $filtri = array(
            'ANNOEMI' => $annoEmi,
            'NUMEMI' => $numEmi,
            'IDBOL_SERE' => $idBolSere
        );

        $this->rimuoviScadenzeDaRipristinare();
        if (!$saltaPubblicazione) {
            $this->rimuoviScadenzeCreate();
        }
        $results = array();
        $page = 0;
        // leggo sempre i primi n (customPaginatorSize) record tanto quelli con stato = 1 vengono spostati in altri stati
        while ($pendenze = $this->leggiScadenzePerInserimento($filtri, $page)) {
            $result = $this->pubblicazioneMassivaDaPendenzeBase($pendenze, false, $saltaPubblicazione);
            if ($result) {
                $results[] = $result;
            }
            $page = $page + $this->customPaginatorSize();
        }
        return $results;
    }

    /**
     * Pubblica n posizioni a partire da un array di pendenze
     * @param type $pendenze
     * @param type $pendDett
     * @param type $insertPenden true se le pendenze vanno inserite
     * @return object false se non pubblica niente, array con esito per ogni pendenza se pubblica tutto o 
     * misto(caso di invio a blocchi in cui alcuni blocchi vengono pubblicati ed altri danno errore)
     * array(
     * 'PROGKEYTAB' => $scadenzaP['PROGKEYTAB'],
     * 'ESITO' => $esito,
     * 'MESSAGGIO' => $msg
     * ); 
     */
    public function pubblicazioneMassivaDaPendenze($pendenze, $insertPenden = true) {
        return $this->pubblicazioneMassivaDaPendenzeBase($pendenze, $insertPenden);
    }

    private function pubblicazioneMassivaDaPendenzeBase($pendenze, $insertPenden = true, $saltaPubblicazione = false) {
        if (!$pendenze) {
            $this->handleError(-1, "Nessuna pendenza trovata");
            return false;
        }

        cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);

        $progkeytabScadenze = array();

        foreach ($pendenze as $key => $pendenza) {
            if ($insertPenden) {
                if (!$this->checkPendenza($pendenza)) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    return false;
                }
                $esito = $this->addBwePenden($pendenza);
                if (!$esito) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    return false;
                }
            }

            // inserisco su db
            $progkeytabScadenza = $this->eseguiInserimentoSingolo($pendenza, true);
            if (!$progkeytabScadenza) {
                // vado in rollbak perche una delle chiamate ha dato errore
                cwbDBRequest::getInstance()->rollBackManualTransaction();
                return false;
            }
            $progkeytabScadenze[] = $progkeytabScadenza;
        }

        $scadenzePerPubblicazione = $this->leggiScadenzePerPubblicazioni($progkeytabScadenze);

        if (count($scadenzePerPubblicazione) != count($pendenze)) {
            $this->handleError(-1, "Scadenze inserite incongruenti con le pendenze passate");
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        }
        cwbDBRequest::getInstance()->commitManualTransaction();

        cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
        $result = $this->eseguiPubblicazioneMassiva($scadenzePerPubblicazione, $saltaPubblicazione);
        if (!$result) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        } else {
            cwbDBRequest::getInstance()->commitManualTransaction();
        }

        return $result;
    }

    public function pubblicazioneSingolaDaChiavePendenza($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca = null, $infoAggiuntive = null, $progsoggex = null) {
        return $this->pubblicazioneSingolaDaChiavePendenzaBase($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca, $infoAggiuntive, $progsoggex);
    }

    private function pubblicazioneSingolaDaChiavePendenzaBase($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca = null, $infoAggiuntive = null, $progsoggex = null, $transazioneEsterna = false) {
        if (!$codtipscad && !$subtipscad) {
            $this->handleError(-1, "Parametri mancanti");
            return false;
        }
        if (!$progcitysc && $progcitysca) {
            $progcitysc = cwbPagoPaHelper::trovaProgCityscDaProgCitysca($progcitysca, $codtipscad, $subtipscad);
            if (!$progcitysc) {
                $this->handleError(-1, "Chiave applicativo non trovata");
                return false;
            }
        }
        $this->cancellaScadenzeSospese($codtipscad, $subtipscad, $progcitysc, $annorif);
        $filtri = array(
            "CODTIPSCAD" => $codtipscad,
            "SUBTIPSCAD" => $subtipscad,
            "PROGCITYSC" => $progcitysc,
            "ANNORIF" => $annorif
        );
        $this->rimuoviScadenzeCreate($filtri);
        $this->rimuoviScadenzeErrore($filtri);
        $pendenze = $this->leggiScadenzePerInserimento($filtri);

        if (!$pendenze) {
            $this->handleError(-1, "Pendenza non trovata");
            return false;
        } else {
            // ci possono essere piu rate di un documento
            $toReturn = array();
            foreach ($pendenze as $key => $pendenza) {
                $res = $this->inserimentoEInvioSingolo($pendenza, $transazioneEsterna, $infoAggiuntive, $progsoggex);
                if (!$res) {
                    $this->rispostaPubblicazione($toReturn, null, false, "Errore Inserimento");
                } else {
                    $toReturn[] = $res;
                }
            }

            return $toReturn;
        }
    }

    private function cancellaScadenzeSospese($codtipscad, $subtipscad, $progcitysc, $annorif) {
        $filtri = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad,
            'PROGCITYSC' => $progcitysc,
            'ANNORIF' => $annorif
        );
        $scadenza = $this->libDB_BGE->leggiBgeAgidScadenze($filtri, false);
        if ($scadenza && (intval($scadenza['STATO']) === 3 || intval($scadenza['STATO']) === 7)) {
            $this->deleteRecord($scadenza, 'BGE_AGID_SCADENZE');
            $scadet = $this->libDB_BGE->leggiBgeAgidScadet(array('IDSCADENZA' => $scadenza['PROGKEYTAB']));
            foreach ($scadet as $key => $value) {
                $this->deleteRecord($value, 'BGE_AGID_SCADET');
            }
        }
    }

    private function cancellaBwePenden($codtipscad, $subtipscad, $progcitysc, $annorif, $numrata) {
        $filtri = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad,
            'PROGCITYSC' => $progcitysc,
            'ANNORIF' => $annorif,
            'NUMRATA' => $numrata,
        );
        $pendenza = $this->libDB_BWE->leggiBwePenden($filtri, false);
        if ($pendenza) {
            $this->deleteRecord($pendenza, 'BWE_PENDEN');
            $penddet = $this->libDB_BWE->leggiBwePenddet(array("IDPENDEN" => $pendenza['PROGKEYTAB']));
            foreach ($penddet as $key => $value) {
                $this->deleteRecord($value, 'BWE_PENDDET');
            }
        }
    }

    // inserisci una posizione marcandola già pagata
    public function pubblicazioneSingolaPagataDaPendenza($pendenza) {
        if (!$pendenza) {
            $this->handleError(-1, "Pendenza da pubblicare non passata");
            return false;
        }

        if (!$pendenza['ANNOEMI'] && $pendenza['CODTIPSCAD'] && $pendenza['ANNORIF']) {
            $filtri = array(
                'CODTIPSCAD' => $pendenza['CODTIPSCAD'],
                'SUBTIPSCAD' => $pendenza['SUBTIPSCAD'],
                'ANNOEMI' => $pendenza['ANNORIF']
            );
            $emissione = $this->getLibDB_BTA()->leggiBtaServrendTabella($filtri, false);
            if ($emissione) {
                $pendenza['ANNOEMI'] = $emissione['ANNOEMI'];
                $pendenza['NUMEMI'] = $emissione['NUMEMI'];
                $pendenza['IDBOL_SERE'] = $emissione['IDBOL_SERE'];
            }
        }

        $pendenze[0] = $pendenza;
        $result = $this->pubblicazioneSingolaDaChiavePendenzaConRate($pendenze);

        if (!$result || !$result[0] || !$result[0]['PROGKEYTAB']) {
            return false;
        } else {
            // metto a pagato e scrivo la agid risco
            $progkeytab = $result[0]['PROGKEYTAB'];
            $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($progkeytab);
            if (!$scadenza) {
                $this->handleError(-1, "Scadenza non trovata");
                return false;
            }
            $scadenza['STATO'] = 12;
            $scadenza['DATAPAGAM'] = date("Ymd");

            $this->aggiornaBgeScadenze($scadenza, false);

            $riscossione = array(
                "IDSCADENZA" => $scadenza['PROGKEYTAB'],
                "PROGRIC" => 0,
                "PROGINTRIC" => 0,
                "IUV" => $scadenza['IUV'],
                "PROVPAGAM" => 2,
                "IMPPAGATO" => $scadenza['IMPDAPAGTO'],
                "DATAPAG" => date("Ymd"),
                "TIPOPERS" => $scadenza['TIPOPERS']
            );

            $this->inserisciBgeAgidRisco($riscossione, false);

            return $result[0];
        }
    }

    public function pubblicazioneSingolaDaChiavePendenzaConRate($pendenze) {
        $result = false;
        if (!$pendenze) {
            return false;
        }
        $progsoggex = null;
        // se non c'è il progsogg, inserisco il soggetto su bge_agid_soggetti
        if ($pendenze[0]['SOGGETTO'] && !$pendenze[0]['PROGSOGG']) {
            $progsoggAgidSogg = $this->inserisciAgidSoggetto($pendenze[0]['SOGGETTO']);
            $progsoggex = $progsoggAgidSogg;
        }

        cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);

        // pendenza con più rate, prima le inserisco tutte su bwe_penden e poi pubblico da chiave 
        // in modo che tiri su tutte le rate
        if ($this->inserisciPendenzeBase($pendenze, true)) {
            $result = $this->pubblicazioneSingolaDaChiavePendenzaBase($pendenze[0]['CODTIPSCAD'], $pendenze[0]['SUBTIPSCAD'], $pendenze[0]['PROGCITYSC'], $pendenze[0]['ANNORIF'], $pendenze[0]['PROGCITYSCA'], $pendenze[0]['INFOAGGIUNTIVE'], $progsoggex, true);
        }

        if (!$result) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        } else {
            $errore = false;
            foreach ($result as $key => $value) {
                if (intval($value['ESITO']) === 0) {
                    $errore = true;
                    break;
                }
            }
            if ($errore) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            } else {
                cwbDBRequest::getInstance()->commitManualTransaction();
            }
        }

        return $result;
    }

    public function pubblicazioneSingolaDaPendenza($pendenza, $insertPenden = true) {
        if ($insertPenden && !$this->checkPendenza($pendenza)) {
            return false;
        }

        // if (!$insertPenden) {
        // le cancello fuori transazione tanto vanno cancellate indipendentemente dall'esito del resto del codice
        $this->cancellaScadenzeSospese($pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD'], $pendenza['PROGCITYSC'], $pendenza['ANNORIF']);
        // }
        if ($insertPenden) {
            $this->cancellaBwePenden($pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD'], $pendenza['PROGCITYSC'], $pendenza['ANNORIF'], $pendenza['NUMRATA']);
        }
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
        if ($insertPenden) {
            if (!$pendenza['PROGCITYSC'] && $pendenza['PROGCITYSCA']) {
                $pendenza['PROGCITYSC'] = cwbPagoPaHelper::trovaProgCityscDaProgCitysca($pendenza['PROGCITYSCA'], $pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD']);
            }
            // inserisce bwe_penden
            $esito = $this->addBwePenden($pendenza);
            if (!$esito) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
                return false;
            }
        }

        // inserisce bge_agid_scadenza e derivati e invia ad intermediario
        $toReturn = $this->inserimentoEInvioSingolo($pendenza, true);
        if (!$toReturn) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        } else {
            cwbDBRequest::getInstance()->commitManualTransaction();
        }

        return $toReturn;
    }

    private function eseguiPubblicazioneMassiva($scadenzeGroup, $saltaPubblicazione = false) {
        //Calcolo qui il progkeytab dell'invio perchï¿½ mi servirï¿½ nel metodo "verificaPosizioniDebitorie" per poter aggiornare
        // il PROGINV di eventuali scadenze da scartare.
        $progkeytabInvio = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BGE_AGID_INVII');

        // Controllo su consistenza dati (metto direttamente STATO a 3 a tutte le scadenze che non hanno 1 o piï¿½ campi obbligatori
        // valorizzati!
        $scadenzeGroup = $this->verificaPosizioniDebitoriePerPubblicazione($scadenzeGroup, $progkeytabInvio);

        if ($scadenzeGroup) {
            // se non pubblica niente torna false e faccio rollback, 
            // se invece pubblica tutto o pubblica solo alcune cose(invio a blocchi) torna un array con i progkeytab pubblicati
            $result = $this->customPubblicazioneMassiva($progkeytabInvio, $scadenzeGroup, $saltaPubblicazione);
            if ($result === false) {
                // errore, i messaggi di errore sono settati su customPubblicazioneMassiva
                if ($this->getSimulazioneSF() != true) {
                    $log = array(
                        "LIVELLO" => 3,
                        "OPERAZIONE" => 1,
                        "ESITO" => 3,
                        "KEYOPER" => 0,
                    );
                    $this->scriviLog($log);
                    return false;
                }
            } else {
                if ($this->getSimulazioneSF() != true) {
                    $log = array(
                        "LIVELLO" => 5,
                        "OPERAZIONE" => 1,
                        "ESITO" => 1,
                        "KEYOPER" => $progkeytabInvio,
                    );
                    $this->scriviLog($log);
                }
            }

            return $result;
        } else {
            $this->handleError(-1, "Niente da pubblicare");
            return false;
        }
    }

    public function eseguiPagamentoDaIuv($IUV, $urlReturn, $redirectVerticale = 0) {
        $scadenza = $this->customCaricaScadenzaPagamento($IUV);
        if (!$scadenza) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        return $this->customEseguiPagamento($scadenza, $urlReturn, $redirectVerticale);
    }

    public function eseguiPagamentoPendenza($pendenza, $urlReturn) {
        if ($pendenza['FLAG_PUBBL'] == 3 || $pendenza['FLAG_PUBBL'] == 4 || $pendenza['FLAG_PUBBL'] == 5) {
//            // pago pa
            $iuv = $this->recuperaIUV($pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD'], $pendenza['ANNORIF'], $pendenza['PROGCITYSC'], $pendenza['NUMRATA']);

            if (!$iuv) {
                $this->handleError(-1, "Dati disallineati, Codice Identificativo (IUV) non trovato per la pendenza selezionata");
                return null;
            }
            return $this->eseguiPagamentoDaIuv($iuv, $urlReturn);
        } else {
            $this->handleError(-1, "Errore flag_pubblic non compatibile (" . $pendenza['FLAG_PUBBL'] . ')');
            return null;
        }
    }

    public function eseguiPagamentoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $urlReturn) {
        $filtri = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad,
            'PROGCITYSC' => $progcitysc,
            'ANNORIF' => $annorif,
            'NUMRATA' => $numrata
        );
        $pendenza = $this->getLibDB_BWE()->leggiBwePenden($filtri);

        if (count($pendenza) == 1) {
            $pendenza = $pendenza[0];
        } else if (!$pendenza) {
            $this->handleError(-1, "Pendenza non trovata");
            return null;
        } else if (count($pendenza) > 1) {
            $this->handleError(-1, "Pendenza non univoca");
            return null;
        }
        return $this->eseguiPagamentoPendenza($pendenza, $urlReturn);
    }

    public function generaBollettinoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata) {
        $params = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad,
            'PROGCITYSC' => $progcitysc,
            'ANNORIF' => $annorif,
            'NUMRATA' => $numrata
        );
        return $this->customGeneraBollettino($params);
//        return $this->generaBollettinoGenerico($params);
    }

    public function generaBollettinoDaIUV($iuv) {
        $params = array(
            'CodiceIdentificativo' => $iuv
        );
        return $this->customGeneraBollettino($params);
//        return $this->generaBollettinoGenerico($params);
    }

    // Genero il progressivo flusso da inserire nella generazione del nome file (solo efil) 
    // e nella tabella bge_agid_invii che ï¿½ in comune
    public function generaProgressivoFlusso($dataCreazione, $codServizio, $tipo) {
        $filtri['DATAINVIO'] = $dataCreazione;
        $filtri['CODSERVIZIO'] = intval($codServizio);
        $filtri['TIPO'] = intval($tipo);
        $progressivoFlusso = $this->libDB_BGE->leggiBgeAgidInviiMaxProgint($filtri);
        if ($progressivoFlusso['MAXPROGINT']) {
            return ++$progressivoFlusso['MAXPROGINT'];
        } else {
            return 1;
        }
    }

    private function checkPendenza($pendenza) {
        $errMsg = '';
        if (!$pendenza['ANNOEMI']) {
            $errMsg .= 'ANNOEMI Mancante';
        }
        if (!$pendenza['NUMEMI']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'NUMEMI Mancante';
        }
        if (!$pendenza['IDBOL_SERE']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'IDBOL_SERE Mancante';
        }
        if (!$pendenza['CODTIPSCAD']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'CODTIPSCAD Mancante';
        }
        if (!$pendenza['PROGCITYSC']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'PROGCITYSC Mancante';
        }
        if (!$pendenza['DESCRPEND']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'DESCRPEND Mancante';
        }
        if (!$pendenza['IMPDAPAGTO']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'IMPDAPAGTO Mancante';
        }
        if (!$pendenza['DATASCADE']) {
            if ($errMsg) {
                $errMsg .= ',';
            }
            $errMsg .= 'DATASCADE Mancante';
        }
        if ($errMsg) {
            $this->handleError(-1, $errMsg . ", su pendenza CODTIPSCAD:" . $pendenza['CODTIPSCAD'] . ' SUBTIPSCAD:' . $pendenza['SUBTIPSCAD'] . ' PROGCITYSC:' . $pendenza['PROGCITYSC'] . ' ANNORIF:' . $pendenza['ANNORIF']);
            return false;
        }

        return true;
    }

    public function ricevutaAccettazionePubblicazione() {
        return $this->customRicevutaAccettazionePubblicazione();
    }

    public function cancellazioneMassiva() {
        return $this->customCancellazioneMassiva();
    }

    public function ricevutaPubblicazione() {
        return $this->customRicevutaPubblicazione();
    }

    public function ricevutaArricchita() {
        return $this->customRicevutaArricchita();
    }

    public function ricevutaAccettazioneCancellazione() {
        return $this->customRicevutaAccettazioneCancellazione();
    }

    public function ricevutaCancellazione() {
        return $this->customRicevutaCancellazione();
    }

    public function rendicontazione($params) {
        return $this->customRendicontazione($params);
    }

    public function ricercaPosizioneDaIUV($iuv) {
        if (!$iuv) {
            $this->handleError(-1, "Iuv non passato");
            return false;
        }
        return $this->customRicercaPosizioneDaIUV($iuv);
    }

    public function ricercaPosizioniDaInfoAggiuntive($params) {
        if (!$params) {
            $this->handleError(-1, "Parametri non passati");
            return false;
        }

        $scadenze = $this->leggiScadenzaPerInfoAggiuntive($params, true);

        $toReturn['Posizioni'] = array();
        foreach ($scadenze as $scadenza) {
            $pagato = (intval($scadenza['STATO']) === 10 || intval($scadenza['STATO']) === 12);
            $stato = $pagato ? self::STATO_PAGAMENTO_PAGATO : self::STATO_PAGAMENTO_NONPAGATO;
            $toReturn['Posizioni'][] = $this->formatRispostaDaRicercaPosizione($scadenza['DESCRPEND'], $stato, $scadenza['DATASCADE'], ($scadenza['IMPDAPAGTO'] * 100), $scadenza['IUV'], $scadenza['CODRIFERIMENTO'], $scadenza['NUMRATA'], $scadenza['PROGKEYTAB']);
        }

        return $toReturn;
    }

    public function ricercaPosizioniDataPagamDaA($dataDa, $dataA, $codtipscad, $subtipscad) {
        if (!$dataDa || !$dataA) {
            $this->handleError(-1, "Parametri data non passati");
            return false;
        }

        if (!$codtipscad) {
            $this->handleError(-1, "Servizio non passato");
            return false;
        }

        $filtri = array(
            'DATAPAGAM_DA' => $dataDa,
            'DATAPAGAM_A' => $dataA,
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad
        );
        $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);

        $toReturn['Posizioni'] = array();
        foreach ($scadenze as $scadenza) {
            $pagato = (intval($scadenza['STATO']) === 10 || intval($scadenza['STATO']) === 12);
            $stato = $pagato ? self::STATO_PAGAMENTO_PAGATO : self::STATO_PAGAMENTO_NONPAGATO;
            $toReturn['Posizioni'][] = $this->formatRispostaDaRicercaPosizione($scadenza['DESCRPEND'], $stato, $scadenza['DATASCADE'], ($scadenza['IMPDAPAGTO'] * 100), $scadenza['IUV'], $scadenza['CODRIFERIMENTO'], $scadenza['NUMRATA'], $scadenza['PROGKEYTAB']);
        }

        return $toReturn;
    }

    public function formatRispostaDaRicercaPosizione($descrizione, $stato, $dataScade, $importo, $iuv, $codRif, $nrRata = null, $progkeytabScade = null) {
        if ($iuv || $codRif) {
            if (!$progkeytabScade) {
                $scade = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array("IUV" => $iuv, 'CODRIFERIMENTO' => $codRif), false);
                $progkeytabScade = $scade['PROGKEYTAB'];
            } else {
                $scade = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($progkeytabScade);
            }
            if (!$nrRata) {
                $nrRata = $scade['NUMRATA'];
            }

            $toReturn['Posizione'] = array(
                'Descrizione' => $descrizione,
                'StatoPosizione' => $stato,
                'DataScadenza' => $dataScade,
                'ImportoInCentesimi' => $importo,
                'Identificativo' => $iuv,
                'CodiceRiferimento' => $codRif,
                'NumRata' => $nrRata,
                'DataPagamento' => $scade['DATAPAGAM'] ? $scade['DATAPAGAM'] : ''
            );

            if ($progkeytabScade) {
                // alcuni campi stanno sulla tabella specifica ma sono uguali per tutti quindi li leggo da master
                // (es. dataverbale non è specifica di mpay ma sta su tutti gli intermediari usati da codice della strada)
                $riscoSpecifica = $this->customAgidRiscoSpecifica($progkeytabScade);
                if ($riscoSpecifica) {
                    $toReturn['Posizione']['Riscossione']['DataEffettivaPagamento'] = $riscoSpecifica['DATAVERS'];
                    $toReturn['Posizione']['Riscossione']['DataRiversamentoConto'] = $riscoSpecifica['DATAREGO'];
                    $toReturn['Posizione']['Riscossione']['DataContabile'] = $riscoSpecifica['DATAREGO'];
                    $toReturn['Posizione']['Riscossione']['TipoVerbale'] = $riscoSpecifica['TIPO_VERBALE'];
                    $toReturn['Posizione']['Riscossione']['DataVerbale'] = $riscoSpecifica['DATA_VERBALE'];
                    $toReturn['Posizione']['Riscossione']['Targa'] = $riscoSpecifica['TARGA'];
                    $toReturn['Posizione']['Riscossione']['NumVerbale'] = $riscoSpecifica['NUM_VERBALE'];
                }
            }
            if ($scade) {
                $this->formatSoggettoRispostaDaRicercaPosizione($toReturn, $scade);
            }

            return $toReturn;
        }
        return false;
    }

    public function ricercaPosizioneChiaveEsterna($codtipscad, $subtipscad, $progcitysc, $annorif, $numRata, $progcitysca = null) {
        if (!$codtipscad && !$subtipscad && !$progcitysc && !$annorif && !$progcitysca) {
            $this->handleError(-1, "Parametri non passati");
            return false;
        }

        $filtri = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad,
            'PROGCITYSC' => $progcitysc,
            'ANNORIF' => $annorif,
            'PROGCITYSCA' => $progcitysca,
            'NUMRATA' => $numRata,
        );

        $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);

        if (!$scadenze) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        if (count($scadenze) > 1) {
            $this->handleError(-1, "Errore. Scadenze multiple trovate");
            return false;
        }

        return $this->customRicercaPosizioneDaIUV($scadenze[0]['IUV']);
    }

    public function rendicontazionePuntuale($params) {
        $scadenza = $this->getScadenzaPagamento($params);
        $esito = $this->getEsitoPagamento($params);

        if ($esito == 'OK') {
            $scadenza['STATO'] = 12;
            $scadenza['DATAPAGAM'] = date("Ymd");

            $this->aggiornaBgeScadenze($scadenza, false);

            $riscossione = array(
                "IDSCADENZA" => $scadenza['PROGKEYTAB'],
                "PROGRIC" => 0,
                "PROGINTRIC" => 0,
                "IUV" => $scadenza['IUV'],
                "PROVPAGAM" => 2,
                "IMPPAGATO" => $scadenza['IMPDAPAGTO'],
                "DATAPAG" => date("Ymd"),
                "TIPOPERS" => $scadenza['TIPOPERS']
            );

            $this->inserisciBgeAgidRisco($riscossione, false);

            $this->customRendicontazionePuntuale($params);
            //TODO mappato su qualche tabella o fisso con un if?? es:
            if ($scadenza['CODTIPSCAD'] == 34) {
                // TODO gestire messaggio? vedere gli altri metodi per portarli in comune
                include_once ITA_BASE_PATH . '/apps/CityFee/cwsBorsLib.class.php';
                $cwsBorsLib = new cwsBorsLib();
                $ret = $cwsBorsLib->confermaPagamento($scadenza['IUV']);
                if (!$ret) {
                    $scadenza['STATO'] = 11;
                    $this->aggiornaBgeScadenze($scadenza, false);
                }
            } else {
                // chiama Omnis 'registraPagamentoCityware'
            }
        }

        if ($params['isWs'] && $params['token']) {
            try {
                $paramsDestroy = array(
                    'TokenKey' => $params['token']
                );
                $loginController = new loginController();
                $loginController->DestroyItaEngineContextToken($paramsDestroy);
            } catch (Exception $exc) {
                $this->logger->error("***** Errore PagoPa rendicontazionePuntuale");
                $this->logger->error($exc->getMessage());
            }
        }

        if ($params['urlverticale']) {
            // se c'è il parametro urlVerticale significa che la chiamata non è server to server ma 
            // via browser (pagopa richiama urlOk e urlKo quando l'utente clicca 'torna indietro' dopo aver pagato
            // non al momento effettivo del pagamento) 
            // quindi oltre ad aggiornare il db di cwol e richiamare il metodo specifico di notifica del verticale,
            // devo anche effettuare la redirect all'url specifico per tornare a video alla pagina del verticale
            $urlVerticale = urldecode($params['urlverticale']);
            $parsed = parse_url($urlVerticale);
            if ($parsed['query']) {
                // se ci sono gia params ne aggiungo un altro
                $urlVerticale .= '&';
            } else {
                // se non ci sono params aggiungo il primo
                $urlVerticale .= '?';
            }
            // aggiungo all'urlReturn l'esito della chiamata
            if ($esito == 'OK') {
                $urlVerticale .= 'esito=OK';
            } else {
                $urlVerticale .= 'esito=KO';
            }
            header("Location: " . $urlVerticale);
            die();
        }

        return $this->customRispostaRendicontazionePuntuale($params, $esito);
    }

    public function riversamenti($params) {
        return $this->customRiversamenti($params);
    }

    public function riconciliazione() {
        return $this->customRiconciliazione();
    }

    public function reinvioPubblicazione($progkeytabInvio) {
        $this->customReinvioPubblicazione($progkeytabInvio);
        // todo elaborazione torna generica
    }

    public function getDatiPagamentoDaIUV($IUV) {
        return $this->customEseguiInserimento($IUV);
    }

    protected function customRicevutaAccettazionePubblicazione() {
        return null;
    }

    protected function customReinvioPubblicazione() {
        return null;
    }

    protected function customRiconciliazione() {
        return null;
    }

    protected function customRiversamenti() {
        return null;
    }

    protected function verificaVariazione($filtri) {
        return null;
    }

    protected function customRicevutaPubblicazione() {
        return null;
    }

    protected function customRicevutaArricchita() {
        return null;
    }

    protected function customRicevutaAccettazioneCancellazione() {
        return null;
    }

    protected function customRicevutaCancellazione() {
        return null;
    }

    protected function customCancellazioneMassiva() {
        return null;
    }

    protected function customGetDatiPagamentoDaIUV($IUV) {
        return null;
    }

    protected function customRendicontazione($params) {
        return null;
    }

    protected function customRicercaPosizioneDaIUV($params) {
        return null;
    }

    protected function customRendicontazionePuntuale($params) {
        return null;
    }

    protected function customRispostaRendicontazionePuntuale($params, $esito) {
        return true;
    }

    public function testConnection($massivo = true, $tipoChiamata = null) {
        $this->customTestConnection($massivo, $tipoChiamata);
    }

    public function registraPagamentoCityware($bwePenden) {
        // Registra pagamento in Cityware.
        $omnisClient = new itaOmnisClient();
        $methodArgs = array();
        $methodArgs[0] = $bwePenden;
        return $omnisClient->callExecute('OBJ_BWE_PORTAL', 'registraPagamento', $methodArgs, 'CITYWARE', false);
    }

    public function rispostaPubblicazione(&$risposta, $scadenzePerPubbli, $esito, $msg = '') {
        if ($scadenzePerPubbli[0]) {
            foreach ($scadenzePerPubbli as $scadenzaP) {
                $risposta[] = array(
                    'PROGKEYTAB' => $scadenzaP['PROGKEYTAB'],
                    'IDPENDEN' => $scadenzaP['IDPENDEN'],
                    'IUV' => $scadenzaP['IUV'],
                    'NUMRATA' => $scadenzaP['NUMRATA'],
                    'IdentificativoPosizione' => $scadenzaP['IUV'], // PER RETROCOMPATIBILITA
                    'CodiceRiferimentoCreditore' => $scadenzaP['CODRIFERIMENTO'], // PER RETROCOMPATIBILITA
                    'ESITO' => $esito ? 1 : 0,
                    'MESSAGGIO' => $msg
                );
            }
        } else if ($scadenzePerPubbli) {
            $risposta[] = array(
                'PROGKEYTAB' => $scadenzePerPubbli['PROGKEYTAB'],
                'IDPENDEN' => $scadenzePerPubbli['IDPENDEN'],
                'IUV' => $scadenzePerPubbli['IUV'],
                'NUMRATA' => $scadenzePerPubbli['NUMRATA'],
                'IdentificativoPosizione' => $scadenzePerPubbli['IUV'], // PER RETROCOMPATIBILITA
                'CodiceRiferimentoCreditore' => $scadenzePerPubbli['CODRIFERIMENTO'], // PER RETROCOMPATIBILITA
                'ESITO' => $esito ? 1 : 0,
                'MESSAGGIO' => $msg
            );
        } else {
            $risposta[] = array(
                'PROGKEYTAB' => null,
                'IDPENDEN' => null,
                'IUV' => null,
                'NUMRATA' => null,
                'IdentificativoPosizione' => null, // PER RETROCOMPATIBILITA
                'CodiceRiferimentoCreditore' => null, // PER RETROCOMPATIBILITA
                'ESITO' => $esito ? 1 : 0,
                'MESSAGGIO' => $msg
            );
        }
    }

    // mantenuto per retrocompatibilita. Non usare
    public function rimuoviPosizioneEPenden($iuv) {
        $params['CodiceIdentificativo'] = $iuv;
        return $this->rimuoviPosizione($params);
    }

    public function inserisciAgidSoggetto($soggetto) {
        $progsogg = null;
        $datanasc = $soggetto['DATANASC'];
        $giorno = '';
        $mese = '';
        $anno = '';
        if ($datanasc) {
            $strtotime = strtotime($datanasc);
            $giorno = date("d", $strtotime);
            $mese = date("m", $strtotime);
            $anno = date("Y", $strtotime);
        }

        $soggettoDaInserire = array(
            'TIPOPERS' => $soggetto['TIPOPERS'],
            'CODFISCALE' => $soggetto['CODFISCALE'],
            'PARTIVA' => $soggetto['PARTIVA'],
            'PEC' => $soggetto['PEC'],
            'NOME' => $soggetto['NOME'],
            'COGNOME' => $soggetto['COGNOME'],
            'GIORNO' => $giorno,
            'MESE' => $mese,
            'ANNO' => $anno,
            'LUOGONASC' => $soggetto['LUOGONASC'],
            'COMUNERESID' => $soggetto['COMUNERESID'],
            'PROVINCIARESID' => $soggetto['PROVINCIARESID'],
            'INDIRIZZORESID' => $soggetto['INDIRIZZORESID'],
            'CAPRESID' => $soggetto['CAPRESID']
        );

        $cwbLibDB_BGE = new cwbLibDB_BGE();
        $agidSogg = $cwbLibDB_BGE->leggiBgeAgidSoggetti(array('CODFISCALE' => $soggetto['CODFISCALE'], 'PARTIVA' => $soggetto['PARTIVA']), false);

        if ($agidSogg) {
            // se il soggetto esiste già lo aggiorno
            $soggettoDaInserire['PROGSOGG'] = $agidSogg['PROGSOGG'];
            $this->aggiornaBgeAgidSoggetti($soggettoDaInserire);
            $progsogg = $agidSogg['PROGSOGG'];
        } else {
            // se non esiste lo creo
            $progsogg = $this->inserisciBgeAgidSoggetti($soggettoDaInserire);
        }

        return $progsogg;
    }

    public function formatSoggettoRispostaDaRicercaPosizione(&$toReturn, $scadenza) {
        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($scadenza['PROGSOGG'], $scadenza['PROGSOGGEX']);
        if ($soggetto) {
            $toReturn['Posizione']['Soggetto']['Nominativo'] = $soggetto['COGNOME'] . ' ' . $soggetto['NOME'];
            $toReturn['Posizione']['Soggetto']['DataNascita'] = $soggetto['ANNO'] . $soggetto['MESE'] . $soggetto['GIORNO'];
            $toReturn['Posizione']['Soggetto']['LuogoNasc'] = $soggetto['LUOGONASC'];
            $toReturn['Posizione']['Soggetto']['Residenza'] = $soggetto['COMUNERESID'];
            $toReturn['Posizione']['Soggetto']['Indirizzo'] = $soggetto['INDIRIZZORESID'];
            $toReturn['Posizione']['Soggetto']['Cap'] = $soggetto['CAPRESID'];
            $toReturn['Posizione']['Soggetto']['CodiceFiscale'] = $soggetto['CODFISCALE'];
            $toReturn['Posizione']['Soggetto']['Piva'] = $soggetto['PARTIVA'];
            $toReturn['Posizione']['Soggetto']['Pec'] = $soggetto['PEC'];
        }
    }

    public function inserisciPendenze(&$pendenze) {
        return $this->inserisciPendenzeBase($pendenze);
    }

    private function inserisciPendenzeBase(&$pendenze, $transazioneEsterna = false) {
        if (!$transazioneEsterna) {
            cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
        }
        $errore = false;

        foreach ($pendenze as $key => $pendenza) {
            if (!$pendenza['PROGCITYSC'] && $pendenza['PROGCITYSCA']) {
                $pendenza['PROGCITYSC'] = cwbPagoPaHelper::trovaProgCityscDaProgCitysca($pendenza['PROGCITYSCA'], $pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD']);
            }

            if (!$this->addBwePenden($pendenza)) {
                $errore = true;
                break;
            } else {
                $pendenze[$key] = $pendenza;
            }
        }

        if ($errore) {
            if (!$transazioneEsterna) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            }
        } else {
            if (!$transazioneEsterna) {
                cwbDBRequest::getInstance()->commitManualTransaction();
            }
            return true;
        }

        return false;
    }

    private function addBwePenden(&$pendenza) {
        try {
            if ($this->controlloCampiObb($pendenza)) {
                $bwe_penden = $this->valorizzaBwePenden($pendenza, $progkeytabPenden);
                $this->inserisciBwePenden($bwe_penden);
                $pendenza['PROGKEYTAB'] = $bwe_penden['PROGKEYTAB'];
                if (intval(count($pendenza['PENDETT'])) > 0) {
                    // sotto la chiave PENDETT mi trovo la lista delle sue pendet
                    foreach ($pendenza['PENDETT'] as $keyPenddet => $penddet) {
                        $this->addBwePenddet($progkeytabPenden, $penddet);
                    }
                }
            } else {
                $this->handleError(-1, "Non sono presenti tutti i dati obbligatori per poter creare la BWE_PENDEN!");
                return false;
            }
        } catch (Exception $exc) {
            if ($exc->getNativeErroreDesc()) {
                $msgError = $exc->getNativeErroreDesc();
            } else {
                $msgError = $exc->getMessage();
            }
            $this->handleError(-1, "Errore: " . $msgError);
            return false;
        }
        return true;
    }

    private function addBwePenddet($progkeytabPenden, $penddet) {
        $bwe_penddet = array();
        $progkeytabPenddet = cwbLibCalcoli::trovaProgressivo("PROGKEYTAB", "BWE_PENDDET");
        $bwe_penddet['PROGKEYTAB'] = $progkeytabPenddet;
        $bwe_penddet['CODICE'] = $penddet['CODICE'];
        $bwe_penddet['DESCRIZIONE'] = $penddet['DESCRIZIONE'];
        $bwe_penddet['IMPORTO'] = $penddet['IMPORTO'];
        $bwe_penddet['IDPENDEN'] = $progkeytabPenden;
        $bwe_penddet['PROGINT'] = $penddet['PROGINT'];
        $bwe_penddet['IDVCOSTO'] = $penddet['IDVCOSTO'];
        $bwe_penddet['COGNOME'] = $penddet['COGNOME'];
        $bwe_penddet['NOME'] = $penddet['NOME'];
        $bwe_penddet['CODFISCALE'] = $penddet['CODFISCALE'];
        $bwe_penddet['QUANTITA'] = $penddet['QUANTITA'];
        $bwe_penddet['IVA'] = $penddet['IVA'];
        $bwe_penddet['CAUSALEIMPORTO'] = $penddet['CAUSALEIMPORTO'];
        $bwe_penddet['ANNOCOMP'] = $penddet['ANNOCOMP'];
        $bwe_penddet['FLAG_DIS'] = $penddet['FLAG_DIS'];
        $bwe_penddet['PAR1NOME'] = $penddet['PAR1NOME'];
        $bwe_penddet['PAR1VALORE'] = $penddet['PAR1VALORE'];
        $bwe_penddet['PAR2NOME'] = $penddet['PAR2NOME'];
        $bwe_penddet['PAR2VALORE'] = $penddet['PAR2VALORE'];
        $bwe_penddet['PAR3NOME'] = $penddet['PAR3NOME'];
        $bwe_penddet['PAR3VALORE'] = $penddet['PAR3VALORE'];
        $bwe_penddet['PAR4NOME'] = $penddet['PAR4NOME'];
        $bwe_penddet['PAR4VALORE'] = $penddet['PAR4VALORE'];
        $bwe_penddet['PAR5NOME'] = $penddet['PAR5NOME'];
        $bwe_penddet['PAR5VALORE'] = $penddet['PAR5VALORE'];
        $this->inserisciBwePenddet($bwe_penddet);
    }

    private function valorizzaBwePenden($pendenza, &$progkeytabPenden) {
        $bwe_penden = array();

        $progkeytabPenden = cwbLibCalcoli::trovaProgressivo("PROGKEYTAB", "BWE_PENDEN");
        $bwe_penden['PROGKEYTAB'] = $progkeytabPenden;
        $bwe_penden['CODTIPSCAD'] = $pendenza['CODTIPSCAD'];
        $bwe_penden['SUBTIPSCAD'] = $pendenza['SUBTIPSCAD'];
        $bwe_penden['DESCRPEND'] = $pendenza['DESCRPEND'];
        $bwe_penden['TIPOPENDEN'] = $pendenza['TIPOPENDEN'];
        $bwe_penden['MODPROVEN'] = $pendenza['MODPROVEN'];
        $bwe_penden['PROGSOGG'] = $pendenza['PROGSOGG'];
        $bwe_penden['ANNORIF'] = $pendenza['ANNORIF'];
        $bwe_penden['PROGCITYSC'] = $pendenza['PROGCITYSC'];
        $bwe_penden['PROGCITYSCA'] = $pendenza['PROGCITYSCA'];
        $bwe_penden['NUMRATA'] = $pendenza['NUMRATA'];
        $bwe_penden['NUMDOC'] = $pendenza['NUMDOC'];
        $bwe_penden['DATASCADE'] = $pendenza['DATASCADE'];
        $bwe_penden['IMPDAPAGTO'] = $pendenza['IMPDAPAGTO'];
        $bwe_penden['IMPPAGTOT'] = $pendenza['IMPPAGTOT'];
        $bwe_penden['DATAPAG'] = $pendenza['DATAPAG'];
        $bwe_penden['MODPAGAM'] = $pendenza['MODPAGAM'];
        $bwe_penden['FLAG_PUBBL'] = $pendenza['FLAG_PUBBL'];
        $bwe_penden['ANNOEMI'] = $pendenza['ANNOEMI'];
        $bwe_penden['NUMEMI'] = $pendenza['NUMEMI'];
        $bwe_penden['IDBOL_SERE'] = $pendenza['IDBOL_SERE'];
        return $bwe_penden;
    }

    private function controlloCampiObb($pendenza) {
        if (!$pendenza['CODTIPSCAD'] || !$pendenza['PROGCITYSC'] || !$pendenza['DATASCADE'] || !$pendenza['IMPDAPAGTO']) {
            return false;
        }

        return true;
    }

    private function rimuoviScadenzeDaRipristinare() {
        $scadDaRipristinare = $this->getLibDB_BGE()->leggiBgeAgidScadenzeDaRipristinare();
        if ($scadDaRipristinare) {
            foreach ($scadDaRipristinare as $scadenza) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
                try {
                    $this->deleteRecord($scadenza, 'BGE_AGID_SCADENZE', true);
                } catch (Exception $exc) {
                    $errore = true;
                }
            }
            if ($errore) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            } else {
                cwbDBRequest::getInstance()->commitManualTransaction();
            }
        }
    }

    // scadenze con stato =1, create ma che non sono state pubblicate
    private function rimuoviScadenzeCreate($addictionalFilters = array()) {
        $filtriFissi = array('STATO' => 1);
        $filtri = array_merge($filtriFissi, $addictionalFilters);
        $this->rimuoviScadenze($filtri);
    }

    private function rimuoviScadenze($filtri) {
        $scadDaRipristinare = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);
        if ($scadDaRipristinare) {
            foreach ($scadDaRipristinare as $scadenza) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
                try {
                    $this->deleteRecord($scadenza, 'BGE_AGID_SCADENZE', true);
                } catch (Exception $exc) {
                    $errore = true;
                }
            }
            if ($errore) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            } else {
                cwbDBRequest::getInstance()->commitManualTransaction();
            }
        }
    }

    // scadenze con stato =1, create ma che non sono state pubblicate
    private function rimuoviScadenzeErrore($addictionalFilters = array()) {
        $filtriFissi = array('STATO' => 3);
        $filtri = array_merge($filtriFissi, $addictionalFilters);
        $this->rimuoviScadenze($filtri);
    }

    private function eseguiInserimentoSingolo($pendenza, $massivo = false, $infoAggiuntive = array(), $progsoggex = null) {
        try {
            $codRiferimento = $this->calcoloCodRiferimento($pendenza);

            if (intval($pendenza['TIPOPENDEN']) === 2) {
                // E' UNA RATA
                // calcolo CODICE RIFERIMENTO
                $idrataunica = $codRiferimento;
                $numrate = '';
            } elseif (intval($pendenza['TIPOPENDEN']) <> 2) {
                // E' UNA TESTATA
                $idrataunica = '';
                $filtri = array();
                $filtri['PROGCITYSC'] = $pendenza['PROGCITYSC'];
                $filtri['ANNORIF'] = $pendenza['ANNORIF'];
                $filtri['TIPOPENDEN'] = 2;
                $countRate = $this->libDB_BWE->leggiBwePendenCountRate($filtri);
                intval($countRate['COUNT']) === 1 ? $numrate = 0 : $numrate = $countRate['COUNT'];
            }
            $numrata = str_pad($pendenza['NUMRATA'], 2, "0", STR_PAD_LEFT);
            $codFiscale = '';
            if (!$pendenza['TIPOPERS']) {
                // vado a reperirmi le info dell'anagrafica con una select diretta su BTA_SOGG o bge_agid_soggetti
                $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($pendenza['PROGSOGG'], $progsoggex);
                $pendenza['TIPOPERS'] = $soggetto['TIPOPERS'];
                $pendenza['CODFISCALE'] = $soggetto['CODFISCALE'];
                $pendenza['PARTIVA'] = $soggetto['PARTIVA'];
                if (!$pendenza['RAGSOC']) {
                    $pendenza['RAGSOC'] = $soggetto['RAGSOC'];
                }
            }
            if ($pendenza['TIPOPERS'] === 'F') {
                $codFiscale = $pendenza['CODFISCALE'];
            } elseif ($pendenza['TIPOPERS'] === 'G') {
                if ($pendenza['PARTIVA']) {
                    $codFiscale = $pendenza['PARTIVA'];
                } else {
                    $codFiscale = $pendenza['CODFISCALE'];
                }
            }

            $impdapagto = $pendenza['IMPDAPAGTO'] - $pendenza['IMPPAGTOT'];

            $iuv = '';
            $conf = $this->getCustomConfIntermediario();
            if ($conf['GENERAIUV']) {
                $iuv = $this->generaIUV();
                if (!$iuv) {
                    return false;
                }
            }

            $row_Agid_scadenze = array(
                // 'PROGKEYTAB' => $progkeytab,
                'IDPENDEN' => $pendenza['PROGKEYTAB'],
                'CODTIPSCAD' => $pendenza['CODTIPSCAD'],
                'SUBTIPSCAD' => $pendenza['SUBTIPSCAD'],
                'PROGCITYSC' => $pendenza['PROGCITYSC'],
                'PROGCITYSCA' => $pendenza['PROGCITYSCA'],
                'ANNORIF' => $pendenza['ANNORIF'],
                'NUMDOC' => $pendenza['NUMDOC'],
                'IMPDAPAGTO' => $impdapagto,
                'DESCRPEND' => substr($pendenza['DESCRPEND'], 0, 99),
                'TIPOPENDEN' => $pendenza['TIPOPENDEN'],
                'MODPROVEN' => $pendenza['MODPROVEN'],
                'PROGSOGG' => $pendenza['PROGSOGG'],
                'PROGSOGGEX' => $progsoggex,
                'PROVENIENZA' => 1,
                'CODRIFERIMENTO' => $codRiferimento,
                'PROGINV' => 0,
                'IUV' => $iuv,
                'TIPOPERS' => $pendenza['TIPOPERS'],
                'CODFISCALE' => $codFiscale,
                'IDRATAUNICA' => $idrataunica,
                'NUMRATE' => $numrate,
                'NUMRATA' => $numrata,
                'ANNOEMI' => $pendenza['ANNOEMI'],
                'NUMEMI' => $pendenza['NUMEMI'],
                'IDBOL_SERE' => $pendenza['IDBOL_SERE'],
                'DATASCADE' => $pendenza['DATASCADE'],
                'TIP_INS' => $this->getCustomTipins($massivo),
                'DATACREAZ' => date('Ymd'),
                'TIMECREAZ' => date('H:i:s'),
                'STATO' => 1
            );
            // Inserimento su BGE_AGID_SCADENZE
            $progkeytab = $this->inserisciBgeScadenze($row_Agid_scadenze);
            if ($infoAggiuntive && $progkeytab) {
                foreach ($infoAggiuntive as $key => $infoAgg) {
                    $this->inserisciBgeAgidScainfo($progkeytab, $key, $infoAgg);
                }
            }
            $this->customEseguiInserimentoSingolo($progkeytab, $pendenza);
            return $progkeytab;
        } catch (Exception $exc) {
            $this->handleError(-1, "Errore inserimento scadenza " . $exc->getMessage());
            return false;
        }
    }

    private function invioPuntualeScadenzaSingola($progkeytabScadenza) {
        $risposta = $this->invioPuntualeScadenzaCustom($progkeytabScadenza);
        if ($risposta) {
            $log = array(
                "LIVELLO" => 5,
                "OPERAZIONE" => 0,
                "ESITO" => 1,
                "KEYOPER" => 0,
            );
            $this->scriviLog($log);
        } else {
            $log = array(
                "LIVELLO" => 3,
                "OPERAZIONE" => 0,
                "ESITO" => 3,
                "KEYOPER" => 0,
            );
            $this->scriviLog($log);
        }
        if ($risposta[0]) {
            return $risposta[0];
        }
        return $risposta;
    }

    private function inserimentoEInvioSingolo($pendenza, $transazioneEsterna = false, $infoAggiuntive = array(), $progsoggex = null) {
        if (!$transazioneEsterna) {
            cwbDBRequest::getInstance()->startManualTransaction(null, $this->CITYWARE_DB);
        }

        // inserisco su db
        $progkeytabScadenza = $this->eseguiInserimentoSingolo($pendenza, false, $infoAggiuntive, $progsoggex);
        if (!$progkeytabScadenza) {
            // vado in rollbak perchï¿½ una delle chiamate ha dato errore
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        }
        // invio ad intermediario                
        $risposta = $this->invioPuntualeScadenzaSingola($progkeytabScadenza);
        if (!$risposta || intval($risposta['ESITO']) === 0) {
            // vado in rollbak perchï¿½ una delle chiamate ha dato errore
            if (!$transazioneEsterna) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            }
            return false;
        }

        if (!$transazioneEsterna) {
            cwbDBRequest::getInstance()->commitManualTransaction();
        }
        return $risposta;
    }

    public function calcoloCodRiferimento($pendenza) {
        $formatAnno = $this->customFormatoAnnoCodRiferimento();
        $anno = $pendenza['ANNORIF'];
        if ($formatAnno == 2) {
            $anno = substr($anno, 2);
        }

        $codRiferimento = str_pad($pendenza['CODTIPSCAD'], 3, "0", STR_PAD_LEFT) . str_pad($pendenza['SUBTIPSCAD'], 2, "0", STR_PAD_LEFT) .
                str_pad($anno, $formatAnno, "0", STR_PAD_LEFT) . str_pad($pendenza['PROGCITYSC'], 7, "0", STR_PAD_LEFT) .
                str_pad($pendenza['NUMRATA'], 2, "0", STR_PAD_LEFT);

        $codRiferimento = $this->customCalcoloCodRiferimento($pendenza, $codRiferimento);

        return $codRiferimento;
    }

    function getCITYWARE_DB() {
        return $this->CITYWARE_DB;
    }

    function setCITYWARE_DB($CITYWARE_DB) {
        $this->CITYWARE_DB = $CITYWARE_DB;
    }

    public function insertBgeAgidInvii(&$progkeytabInvio, $invio, $startedTransaction = true) {
        $progkeytabInvio = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BGE_AGID_INVII');
        $invio['PROGKEYTAB'] = $progkeytabInvio;
        $this->inserisciBgeAgidInvii($invio, $startedTransaction);
    }

    public function updateBgeAgidInvii($progkeytab, $stato, $noteErrore = '', $progint, $dataInvio) {
        $toUpdate = array();
        $toUpdate['PROGKEYTAB'] = $progkeytab;
        $toUpdate['STATO'] = $stato;
        $toUpdate['NOTEERRORE'] = $noteErrore;
        if ($progint != null) {
            $toUpdate['PROGINT'] = $progint;
        }
        if ($dataInvio != null) {
            $toUpdate['DATAINVIO'] = $dataInvio;
        }
        $this->aggiornaBgeAgidInvii($toUpdate);
    }

    public function insertBgeAgidRicez($ricezione) {
        $progkeytabRicez = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BGE_AGID_RICEZ');
        $ricezione['PROGKEYTAB'] = $progkeytabRicez;
        return $this->inserisciBgeAgidRicez($ricezione);
    }

    public function insertBgeAgidAllegati($allegati, $startedTransaction = true) {
        $progkeytabAlle = cwbLibCalcoli::trovaProgressivo('PROGKEYTAB', 'BGE_AGID_ALLEGATI');
        $allegati['PROGKEYTAB'] = $progkeytabAlle;
        return $this->inserisciBgeAgidAllegati($allegati, $startedTransaction);
    }

    public function insertBgeAgidRisco($riscossione) {
        return $this->inserisciBgeAgidRisco($riscossione);
    }

    public function scriviLog($log, $startedTRansaction = false) {
// Scrittura su LOG
        $this->inserisciBgeAgidLog($log, $startedTRansaction);
    }

    protected function connettiDB() {
        try {
// Per utilizzare il database 'CITYWARE' senza suffisso, passare come secondo parametro ''
            $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');     // Cityware
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    private function verificaPosizioniDebitoriePerPubblicazione($scadenzePerPubbli, $progkeytabInvio) {
        foreach ($scadenzePerPubbli as $key => $value) {
            if (!$value['PROGCITYSC']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('PROGCITYSC', $errori);
            }
            if (!$value['ANNORIF']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('ANNORIF', $errori);
            }
            if (!$value['CODTIPSCAD']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('CODTIPSCAD', $errori);
            }
//            if (!$value['SUBTIPSCAD']) {
//                $daScartare = true;
//                $this->gestioneErrorePosDebitoria('SUBTIPSCAD', $errori);
//            }
            if (!$value['DESCRPEND']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('DESCRPEND', $errori);
            }
            if (!$value['IMPDAPAGTO']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('IMPDAPAGTO', $errori);
            }
            if (!$value['CODFISCALE']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('CODFISCALE', $errori);
            }
            if (!$value['TIPOPERS']) {
                $daScartare = true;
                $this->gestioneErrorePosDebitoria('TIPOPERS', $errori);
            }
            if ($daScartare) {
                $toUpdate = array();
                $toUpdate['PROGKEYTAB'] = $value['PROGKEYTAB'];
                $toUpdate['STATO'] = 3;
                $toUpdate['PROGINV'] = $progkeytabInvio;
                $toUpdate['DATASOSP'] = date('Ymd');
                $toUpdate['TIMESOSP'] = date('H:i:s');
                $toUpdate['NUMSOSP'] = ++$value['NUMSOSP'];
                $noteSosp = json_encode($errori);
                $toUpdate['NOTESOSP'] = $noteSosp;
                $this->aggiornaBgeScadenze($toUpdate);
                $daScartare = false;
                $errori = array();
                unset($scadenzePerPubbli[$key]);
            }
        }
        return $scadenzePerPubbli;
    }

    public function rimuoviPosizione($params) {
        if ($params['CodiceIdentificativo']) {
            $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IUV' => $params['CodiceIdentificativo']));
        } else if ($params['CodRiferimento']) {
            $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $params['CodRiferimento']));
        }

        if (!$scadenze) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        } else if (count($scadenze) > 1) {
            $this->handleError(-1, "Errore scadenze multiple");
            return false;
        } else if (intval($scadenze[0]['STATO']) >= 10) {
            $this->handleError(-1, "Scadenza pagata. Impossibile modificarla");
            return false;
        }
        $scadenza = $scadenze[0];
        // se devo fare una chiamata ws di cancellazione, la faccio prima di cancellare le tabelle,
        // almeno se va in errore non cancello
        if ($this->preRimuoviPosizione($scadenza)) {
            cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
            try {
                if ($scadenza) {
                    // cancello bwe_penden
                    $filtri = array(
                        'CODTIPSCAD' => $scadenza['CODTIPSCAD'],
                        'SUBTIPSCAD' => $scadenza['SUBTIPSCAD'],
                        'PROGCITYSC' => $scadenza['PROGCITYSC'],
                        'ANNORIF' => $scadenza['ANNORIF'],
                        'NUMRATA' => $scadenza['NUMRATA']
                    );
                    $pendenza = $this->getLibDB_BWE()->leggiBwePenden($filtri,false);
                    $this->deleteRecord($pendenza, "BWE_PENDEN", true);
                    // cancello bwe_penddet
                    $penddetts = $this->getLibDB_BWE()->leggiBwePenddet(array('IDPENDEN' => $pendenza['PROGKEYTAB']));
                    foreach ($penddetts as $penddet) {
                        $this->deleteRecord($penddet, "BWE_PENDDET", true);
                    }
                }
                // cancello bge_agid_scadet
                $scadetts = $this->getLibDB_BGE()->leggiBgeAgidScadet(array('IDSCADENZA' => $scadenza['PROGKEYTAB']));
                foreach ($scadetts as $scadett) {
                    $this->deleteRecord($scadett, "BGE_AGID_SCADET", true);
                }
                // cancello bge_agid_scainfo
                $infoAggiuntive = $this->getLibDB_BGE()->leggiBgeAgidScainfo(array('IDSCADENZA' => $scadenza['PROGKEYTAB']));
                foreach ($infoAggiuntive as $infoAggiuntiva) {
                    $this->deleteRecord($infoAggiuntiva, 'BGE_AGID_SCAINFO', true);
                }

                // scrivo storico cancellazioni
                $toInsert = $scadenza;
                $toInsert['STATO'] = 7;
                $where = ' PROGKEYTAB=' . $scadenza['PROGKEYTAB'];
                $progintStoscade = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BGE_AGID_STOSCADE', $where);
                $toInsert['PROGINT'] = $progintStoscade;
                $this->inserisciBgeAgidStoscade($toInsert, true);

                // cancello bge_agid_scadenze
                $this->deleteRecord($scadenza, "BGE_AGID_SCADENZE", true);

                $this->customRimuoviPosizione($scadenza);
            } catch (Exception $exc) {
                $this->setLastErrorDescription($exc->getMessage());
                cwbDBRequest::getInstance()->rollBackManualTransaction();
                return false;
            }

            cwbDBRequest::getInstance()->commitManualTransaction();
            return true;
        }

        return false;
    }

    public function rimuoviPosizioni($idRuolo) {
        return $this->customRimuoviPosizioni($idRuolo);
    }

    public function recuperaRicevutaPagamento($iuv, $arricchita) {
        return $this->customRecuperaRicevutaPagamento($iuv, $arricchita);
    }

    protected function inserisciBgeAgidScadetiva($progkeytabBwePenden, $progkeytabAgidScadenze) {
// Riversamento dati da BWE_PENDDETIVA in BGE_AGID_SCADETIVA
        $penddetiva = $this->getLibDB_BWE()->leggiBwePenddetiva(array('IDPENDEN' => $progkeytabBwePenden));
        if ($penddetiva) {
            foreach ($penddetiva as $value) {
                $toInsert = $value;
                $toInsert['IDSCADENZA'] = $progkeytabAgidScadenze;
                $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCADETIVA', true, true);
            }
        }
    }

    protected function inserisciBgeAgidScadet($progBwePenden, $progkeytabAgidScadenze) {
// Riversamento dati da BWE_PENDDET in BGE_AGID_SCADET
        $filtri['IDPENDEN'] = $progBwePenden;
        $penddet = $this->getLibDB_BWE()->leggiBwePenddet($filtri);
        if ($penddet) {
            foreach ($penddet as $value) {
                $toInsert['IDSCADENZA'] = $progkeytabAgidScadenze;
                $toInsert['PROGINT'] = $value['PROGINT'];
                $toInsert['CODICE'] = $value['CODICE'];
                $toInsert['DESCRIZIONE'] = $value['DESCRIZIONE'];
                $toInsert['IDPENDEN'] = $value['IDPENDEN'];
                $toInsert['IMPORTO'] = $value['IMPORTO'];
                $toInsert['IDVCOSTO'] = $value['IDVCOSTO'];
                $toInsert['COGNOME'] = $value['COGNOME'];
                $toInsert['NOME'] = $value['NOME'];
                $toInsert['CODFISCALE'] = $value['CODFISCALE'];
                $toInsert['QUANTITA'] = $value['QUANTITA'];
                $toInsert['IVA'] = $value['IVA'];
                $toInsert['CAUSALEIMPORTO'] = $value['CAUSALEIMPORTO'];
                $toInsert['ANNOCOMP'] = $value['ANNOCOMP'];
                $toInsert['FLAG_DIS'] = $value['FLAG_DIS'];
                $toInsert['PAR1NOME'] = $value['PAR1NOME'];
                $toInsert['PAR1VALORE'] = $value['PAR1VALORE'];
                $toInsert['PAR2NOME'] = $value['PAR2NOME'];
                $toInsert['PAR2VALORE'] = $value['PAR2VALORE'];
                $toInsert['PAR3NOME'] = $value['PAR3NOME'];
                $toInsert['PAR3VALORE'] = $value['PAR3VALORE'];
                $toInsert['PAR4NOME'] = $value['PAR4NOME'];
                $toInsert['PAR4VALORE'] = $value['PAR4VALORE'];
                $toInsert['PAR5NOME'] = $value['PAR5NOME'];
                $toInsert['PAR5VALORE'] = $value['PAR5VALORE'];
                $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCADET', true, true);
            }
        }
    }

    public function gestioneErrorePosDebitoria($campo, &$errori) {
        $coderrore = array("cod" => 'Campo obbligatorio non valorizzato', "desc" => $campo);
        $errori[] = $coderrore;
        $coderrore = null;
    }

// Zippa file 
    public function creaZip($nomeFile, $textFile) {
        $zipPath = itaLib::getUploadPath() . "/" . $nomeFile;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString($nomeFile, $textFile);
        $zip->close();

        return $zipPath;
    }

// Trasforma il contenuto di un xml in array
    public function xmlToArray($xml) {
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($xml);

        if (!$retXml) {
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            return false;
        }
        return $arrayXml;
    }

    public function getConfIntermediario() {
        return $this->getCustomConfIntermediario();
    }

    protected function customRimuoviPosizione($scadenza) {
        return null;
    }

    protected function preRimuoviPosizione($scadenza) {
        return true; // default true in modo che va avanti la cancellazione se non devo fare niente di specifico
    }

    protected function customRimuoviPosizioni($idRuolo) {
        return null;
    }

    protected function customRecuperaRicevutaPagamento($iuv, $arricchita) {
        return null;
    }

    protected function getCustomConfIntermediario() {
        return null;
    }

    protected function getCodiceSegregazione() {
        return null;
    }

    protected function invioPuntualeScadenzaCustom($progkeytabScadenza) {
        return null;
    }

    protected function customEseguiInserimentoSingolo($row_Agid_scadenze, $pendenza) {
        return null;
    }

    protected function customInserimentoMassivo($scadenza, $inviaBloccoUnico = false) {
        return null;
    }

    protected function customPubblicazioneMassiva($progkeytabInvio, $scadenzePerPubbli, $saltaPubblicazione = false) {
        return null;
    }

    protected function customPubblicazioneScadenzeCreateMassiva($scadenzeDaPubbl) {
        return null;
    }

    protected function customTestConnection($massivo, $tipoChiamata) {
        return true;
    }

    protected function customEseguiPagamento($scadenza, $urlReturn) {
        return null;
    }

    protected function customCaricaScadenzaPagamento($codiceIdentificativo) {
        return $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IUV' => $codiceIdentificativo), false);
    }

    protected function customCalcoloCodRiferimento($pendenza, $codRiferimento) {
        return $codRiferimento;
    }

    protected function customGeneraBollettino($params) {
        return null;
    }

    protected function customAgidRiscoSpecifica($progkeytabScade) {
        return null;
    }

    protected function getCustomTipins($massivo = false) {
        return 0;
    }

    protected function customPaginatorSize() {
        return 5000;
    }

    protected function customRecuperaIuv($res) {
        return $res['IUV'];
    }

    public function getCodiceIntermediario($codtipscad, $subtipscad, $annoemi, $numemi, $idbol_sere) {
        return cwbPagoPaHelper::getCodiceIntermediario($codtipscad, $subtipscad, $annoemi, $numemi, $idbol_sere);
    }

    public function getServizioIntermediario($codtipscad, $subtipscad) {
        return cwbPagoPaHelper::getServizioIntermediario($codtipscad, $subtipscad);
    }

    public function getIntermediarioDaIUV($IUV) {
        return cwbPagoPaHelper::getIntermediarioDaIUV($IUV);
    }

    public function getInfoGetIntermediarioDaIUV($IUV) {
        return cwbPagoPaHelper::getInfoGetIntermediarioDaIUV($IUV);
    }

    public function recuperaIUV($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $progcitysca = null) {
        $filtri = array();
        $filtri['CODTIPSCAD'] = $codtipscad;
        $filtri['SUBTIPSCAD'] = $subtipscad;
        $filtri['ANNORIF'] = $annorif;
        $filtri['PROGCITYSC'] = $progcitysc;
        $filtri['PROGCITYSCA'] = $progcitysca;
        $filtri['NUMRATA'] = $numrata;
        $scadenza = $this->libDB_BGE->leggiBgeAgidScadenze($filtri, false);
        return $this->customRecuperaIuv($scadenza);
    }

    public function getEmissioneDaIUV($IUV) {
        $filtri['IUV'] = $IUV;
        return $this->getLibDB_BTA()->leggiGetEmissioneDaIUV($filtri);
    }

    public function getEsitoPagamento($params) {
        return $params['Esito'];
    }

    public function getScadenzaPagamento($params) {
        if (!$params['CodRiferimento']) {
            return null;
        }
        return $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $params['CodRiferimento']), false);
    }

    public function getInfoAggiuntive($idScadenza) {
        $toReturn = array();
        if (!$idScadenza) {
            return null;
        }
        $results = $this->getLibDB_BGE()->leggiBgeAgidScainfo(array('IDSCADENZA' => $idScadenza));
        if ($results) {
            foreach ($results as $result) {
                $toReturn[$result['CHIAVE']] = $result['VALORE'];
            }
        }

        return $toReturn;
    }

    public function elaborazioneScadenzeScartate($filtri) {
        return $this->verificaVariazione($filtri);
    }

    public function salvaAllegato($configPathAllegati, $progkeytabAlleg, $file, &$nomeAllegato) {
        if (!file_exists($configPathAllegati['CONFIG'])) {
            mkdir($configPathAllegati['CONFIG']);
        }
        $nomeAllegato = $configPathAllegati['CONFIG'] . "/Allegato_" . $progkeytabAlleg;
        file_put_contents($nomeAllegato, $file);
    }

    public function handleError($code, $description) {
        $this->setLastErrorCode($code);
        $this->setLastErrorDescription($description);
    }

    public function calcolaStatoRend($statoRend, $tipo) {
        if ($tipo === 'REND') {
            switch (intval($statoRend)) {
                case 1:
                    $stato = 11;
                    break;
                case 3:
                    $stato = 16;
                    break;
                case 12:
                    $stato = 14;
                    break;
            }
        } elseif ($tipo === 'RIVERS') {
            switch (intval($statoRend)) {
                case 1:
                    $stato = 12;
                    break;
                case 2:
                    $stato = 15;
                    break;
                case 11:
                    $stato = 13;
                    break;
            }
        }
        return $stato;
    }

    public function getUrlPagamentoGet($codiceRiferimento, $urlVerticale = '', $esitoOk = null) {
        return $this->getUrlPagamentoBase(self::URL_NOTIFICA_GET, $codiceRiferimento, $urlVerticale, $esitoOk);
    }

    public function getUrlPagamento($codiceRiferimento, $urlVerticale = '', $esitoOk = null) {
        return $this->getUrlPagamentoBase(self::URL_NOTIFICA, $codiceRiferimento, $urlVerticale, $esitoOk);
    }

    private function getUrlPagamentoBase($urlBase, $codiceRiferimento, $urlVerticale = '', $esitoOk = null) {
        $url = str_replace("@URL", $urlVerticale, str_replace("@CODRIF", $codiceRiferimento, $urlBase));

        if (!$urlVerticale) {
            $url = str_replace("&urlverticale=", "", $url);
        }

        $token = App::$utente->getKey('TOKEN');
        $url = str_replace("@TOKEN", urlencode($token), $url);
        if ($esitoOk !== null) {
            // se $esitoOk null il parametro esito viene gestito dall'intermediario 
            // (tipo efill che vuole urlReturn e poi concatena il param esito su quell'url),
            //  sennò se true o false lo aggiungo qui (caso di efillZZ che vuole urlOk e urlKo e ti ritorna su quell'url senza manipolarlo)
            if ($esitoOk) {
                $url .= "?Esito=OK";
            } else {
                $url .= "?Esito=KO";
            }
        }

        $urlBase = '';
        $conf = $this->getCustomConfIntermediario();

        if ($conf && $conf['URL_BACKOFFICE']) {
            $urlBase = $conf['URL_BACKOFFICE'];
        } else {
            $nomeItaEngine = '/';
            if (isset($_SERVER['REQUEST_URI'])) {
                $exploded = explode("/", $_SERVER['REQUEST_URI']);
                $nomeItaEngine .= $exploded[1];
            } else {
                $nomeItaEngine .= 'itaEngine';
            }
            $protocollo = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) ? "https://" : "http://";

            if ($_SERVER['HTTP_PORT'] != '') {
                $urlBase = $protocollo . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['HTTP_PORT'] . $nomeItaEngine;
            } else {
                $urlBase = $protocollo . $_SERVER['HTTP_HOST'] . $nomeItaEngine;
            }
        }

        return $urlBase . $url;
    }

    // Genera codice identificativo unico riferito alla scadenza
    // 2 cifre cod segregazione + 2 cifre anno + 3 cifre codistat (ultime 3 univoche per provincia)
    // + 8 cifre progressivo per anno + 2 cifre modulo 93. totale 17 cifre
    public function generaIUV() {
        if (!cwbParGen::getCodente()) {
            $this->handleError(-1, "Errore generazione iuv, codice ente non settato");
            return false;
        }
        if (!$this->getCodiceSegregazione()) {
            $this->handleError(-1, "Errore generazione iuv, codice segregazione non settato");
            return false;
        }

        $auxDigit = 3;
        $codSegr = $this->getCodiceSegregazione();
        $codSegr = str_pad($codSegr, 2, "0", STR_PAD_LEFT);
        $anno = substr(date("Y", strtotime(date("y-m-d"))), 2, 2);
        $libNrd = new cwbBtaNumeratori();
        $progressivo = $libNrd->avanzaNumeratore(date('Y'), self::CHIAVE_NUMERATORE_IUV);
        $progressivo = str_pad($progressivo['NUMULTDOC'], 8, "0", STR_PAD_LEFT);
        $codIstatFinale = substr(cwbParGen::getCodente(), 3, 3);
        $iuvBase = $anno . $codIstatFinale . $progressivo;

        $concatPerCalcModulo93 = $auxDigit . $codSegr . $iuvBase;
        $modulo93 = bcmod($concatPerCalcModulo93, 93);
        $modulo93 = str_pad($modulo93, 2, "0", STR_PAD_LEFT);

        $IUV = $codSegr . $iuvBase . $modulo93;

        if (strlen($IUV) !== 17) {
            $this->handleError(-1, "Errore generazione iuv, lunghezza diversa da 17");
            return false;
        }

        return $IUV;
    }

//    private function generaNumeroAvviso($auxDigit, $appCode, $IUV) {
//        switch ($intermediario) {
//            case 0: //E-Fil
//
//                return $auxDigit . $appCode . $IUV;
//
//                break;
//
//            case 1: //NSS
//
//                return null;
//
//                break;
//        }
//    }

    function getLibDB_BGE() {
        return $this->libDB_BGE;
    }

    function getLibDB_BOR() {
        return $this->libDB_BOR;
    }

    function getLibDB_BTA() {
        return $this->libDB_BTA;
    }

    function getLibDB_BWE() {
        return $this->libDB_BWE;
    }

    function setLibDB_BGE($libDB_BGE) {
        $this->libDB_BGE = $libDB_BGE;
    }

    function setLibDB_BWE($libDB_BWE) {
        $this->libDB_BWE = $libDB_BWE;
    }

//    public abstract function riconciliazione();
// Simulazione di Pubblicazione viene creata esclusivamente la fornitura di pubblicazione senza invio
// ( Vengono alterate le tabelle AGID)
    function getSimulazione() {
        return $this->simulazione;
    }

    function setSimulazione($simulazione = false) {
        $this->simulazione = $simulazione;
    }

// Simulazione di Pubblicazione viene creata esclusivamente la fornitura di pubblicazione senza invio
// ( non vengono alterate le tabelle AGID)
    function getSimulazioneSF() {
        return $this->simulazioneSF;
    }

    function setSimulazioneSF($simulazioneSF = false) {
        $this->simulazioneSF = $simulazioneSF;
    }

    function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    function getLastErrorDescription() {
        return $this->lastErrorDescription;
    }

    function setLastErrorCode($lastErrorCode) {
        $this->lastErrorCode = $lastErrorCode;
    }

    function setLastErrorDescription($lastErrorDescription) {
        $this->lastErrorDescription = $lastErrorDescription;
    }

    public function generaBollettinoGenerico($params) {
        $iuv = null;
        if (isset($params['CodiceIdentificativo']) && $params['CodiceIdentificativo'] != '') {
            $iuv = $params['CodiceIdentificativo'];
            $filtri = array('IUV' => $iuv);
        } else {
            $filtri = array(
                'CODTIPSCAD' => $params['CodTipScad'],
                'SUBTIPSCAD' => $params['SubTipScad'],
                'PROGCITYSC' => $params['ProgCitySc'],
                'ANNORIF' => $params['AnnoRif'],
                'NUMRATA' => $params['NumRata']
            );
        }
        if (!$filtri) {
            $this->handleError(-1, "filtri non trovati");
            return false;
        }

        /*
         * NOTA: se la rata è 0 viene stampato solo l'avviso per la rata unica
         * Indicando il numero di rate TOTALI viene stampato l'avviso realtivo (rata unica + 2/3 rate)
         * La prima posizione cercata è SEMPRE quella con rata 0 dato che la stampa per rata unica va sempre fatta
         */
        $pdf_arr = array();

        $agidScadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
        if (!$agidScadenza) {
            $this->lastErrorDescription = "Scadenza non trovata";
            return false;
        }
        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza['PROGSOGG'], $agidScadenza['PROGSOGGEX']);
        $pdf_arr[] = $this->generaBollettinoRataUnica($agidScadenza, $soggetto);
        //vanno stampati il minor numero possibile di fogli (considerando 2 o 3 rate per foglio), calcolo massimo numero di pagine da 3 rate, poi da 2 rate
        $n_rate = $agidScadenza['NUMRATE'];
        if ($n_rate > 1) {
            $resto = $n_rate % 3;
            switch ($resto) {
                case 0:
                    $n_modelli3 = intval($n_rate / 3);
                    $n_modelli2 = 0;
                    break;
                case 1:
                    $n_modelli3 = intval($n_rate / 3) - 1;
                    $n_modelli2 = 2;
                    break;
                case 2:
                    $n_modelli3 = intval($n_rate / 3);
                    $n_modelli2 = 1;
                    break;
                default:
                    break;
            }

            $curr_rata = 0;
            for ($i = 1; $i <= $n_modelli2; $i++) {
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza1 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza2 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $pdf_arr[] = $this->generaBollettinoRate($rataScadenza1, $rataScadenza2);
            }
            for ($i = 1; $i <= $n_modelli3; $i++) {
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza1 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza2 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza3 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $pdf_arr[] = $this->generaBollettinoRate($rataScadenza1, $rataScadenza2, $rataScadenza3);
            }
        }

        if (!$pdf_arr) {
            $this->lastErrorDescription = "Errore nella creazione dei pdf: lista pdf vuota";
            return false;
        }

        return $this->concatenaPDF($pdf_arr);
    }

    private function concatenaPDF($pdf_arr) {
        if (!is_array($pdf_arr)) {
            $this->setErrMessage("Concatenazione impossibile: oggetto non array di PDF");
            return false;
        }
        $dir = pathinfo($pdf_arr[0], PATHINFO_DIRNAME);
        if (!$dir) {
            $this->lastErrorDescription = "Directory temporanea mancante";
            return false;
        }
        $OUTPUT_FILE = $dir . DIRECTORY_SEPARATOR . uniqid('CompletoPA_') . time() . ".pdf";
        //task di concatenazione dei bollettini
        $xmlTaskCatPath = $dir . "/xmlCtask_" . uniqid('TaskPA_') . time() . ".xml";
        $xthC = fopen($xmlTaskCatPath, 'w');
        if ($xthC === false) {
            $this->lastErrorDescription = 'Errore nella apertura del File TASK CONCAT.';
            return false;
        }
        fwrite($xthC, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($xthC, "<root>\n");
        fwrite($xthC, "<task name=\"cat\">\n");
        fwrite($xthC, "<inputs>\n");
        foreach ($pdf_arr as $pdf_file) {
            fwrite($xthC, "<input delete=\"1\" >{$pdf_file}</input>\n");
        }
        fwrite($xthC, "</inputs>\n");
        fwrite($xthC, "<output>$OUTPUT_FILE</output>\n");
        fwrite($xthC, "</task>\n");
        fwrite($xthC, "</root>\n");
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF2/itaJPDF.jar ' . $xmlTaskCatPath . $stdOutDest;
        exec($command, $out, $ret);
        if ($ret != '0') {
            $this->lastErrorDescription = "Errore in Composizione PDF <br><br><br>Out: $out<br><br>" . $command;
            return false;
        }
        if (!is_file($OUTPUT_FILE)) {
            $this->lastErrorDescription = "Errore nella concatenazione dei pdf";
            return false;
        }
        $content = file_get_contents($OUTPUT_FILE);
        @unlink($xmlTaskCatPath);
        @unlink($OUTPUT_FILE);
        return $content;
    }

    private function generaBollettinoRataUnica($agidScadenza) {
        if (!$agidScadenza) {
            $this->setErrMessage("Scadenza non definita");
            return false;
        }
        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza['PROGSOGG'], $agidScadenza['PROGSOGGEX']);
        if (!$soggetto) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        //parametrizzazioni
        $parametriInt = $this->getCustomConfIntermediario();
        $GLN = $parametriInt['GLN_ENTE'];
        $aux_digit = $parametriInt['AUXDIGIT'];
        $NumeroAvviso = $aux_digit . $agidScadenza['IUV'];
        $CFEnte = cwbParGen::getPIVAEnte();
        $LOGO = 'enti/ente' . App::$utente->getKey('ditta') . '/logo_' . App::$utente->getKey('ditta') . '.jpg';
        //parametri conto
        $parametriConto_arr = $this->getLibDB_BWE()->leggiBweTippenPPA(array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD']));
        $parametriConto = $parametriConto_arr[0];
        //QRCode
        $imp = (int) round($agidScadenza['IMPDAPAGTO'] * 100, 0);
        $QRCode = "PAGOPA|002|" . $NumeroAvviso . "|" . $CFEnte . "|" . ($imp);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo = explode(".", $agidScadenza['IMPDAPAGTO']);
        $Denominazione = trim($soggetto['COGNOME'] . " " . $soggetto['NOME']);
        $Code128 = "18" . $NumeroAvviso .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo['0'], 8, '0', STR_PAD_LEFT) . $impo['1'] .
                "3" . "896";
        $Datamatrix = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";

        //preparo array di parametri da passare al report
        $params = array(
            "Ente" => cwbParGen::getDesente(),
            "daPagina" => 1,
            "IUV" => $aux_digit . $agidScadenza['IUV'],
            "ImportoTot" => number_format($agidScadenza['IMPDAPAGTO'], 2, ",", "."),
            "SCADENZA" => substr($agidScadenza['DATASCADE'], 8, 2) . "/" . substr($agidScadenza['DATASCADE'], 5, 2) . "/" . substr($agidScadenza['DATASCADE'], 0, 4),
            "CFENTE" => $CFEnte,
            "NCCP" => $parametriConto['CONTO_CORRENTE'],
            "QRCODE" => $QRCode,
            "BARCODE" => '', // non più usato
            "Anno" => $agidScadenza['ANNORIF'],
            "LOGO" => $LOGO,
            "CODICE" => $soggetto['PROGSOGG'],
            "DENOMINAZIONE" => $Denominazione,
            "CODICEFISCALE" => $soggetto['CODFISCALE'],
            "COMUNE" => $soggetto['COMUNERESID'],
            "INDIRIZZO" => $soggetto['INDIRIZZORESID'], //INDIRIZZORESID contiene il civico
            "CIVICO" => '',
            "CAP" => $soggetto['CAPRESID'],
            "PROVINCIA" => $soggetto['PROVINCIARESID'],
            "PIVA" => $soggetto['PARTIVA'],
            "CAUSALE" => $agidScadenza['DESCRPEND'],
            "DATAMATRIX" => $Datamatrix,
            "NRATE" => 0,
            "AUTORIZZAZIONE" => $parametriConto['AUTORIZZAZIONE'], //@TODO: parametro da aggiungere in tabella
            "CBILL" => $parametriConto['CBILL'],
            "INTESTATO" => $parametriConto['CCINFO']
        );


        //creo il task per la generazione della lettera PA
        $subPath = itaLib::getAppsTempPath('STAMPEPA_' . time());
        if (!is_dir($subPath)) {
            $subPath = itaLib::createAppsTempPath('STAMPEPA_' . time());
        }
        if (!$subPath) {
            $this->setErrMessage("Errore nella creazione della path temporanea");
            return false;
        }
        $OUTPUT_FILE = $subPath . DIRECTORY_SEPARATOR . uniqid('AvvisoPA_') . time() . ".pdf";
        $xmlJrDefPAPath = $subPath . "/xmljrdefPA.xml";
        $xjhPA = fopen($xmlJrDefPAPath, 'w');
        if ($xjhPA === false) {
            $this->setErrMessage('Errore nella apertura del File.');
            return false;
        }
        if (fwrite($xjhPA, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n") === false) {
            Out::msgStop('Errore', 'Errore nella scrittura file: Testata xml.');
            return false;
        }
        if (fwrite($xjhPA, "<root>\n") === false) {
            $this->setErrMessage('Errore nella scrittura file: <root>');
            return false;
        }

        $nomeReport = 'PAGOPA_AVVISO';
        $path_file = ITA_BASE_PATH . "/reports/PagoPA/enti/ente" . App::$utente->getKey('ditta') . "/" . $nomeReport . "_" . App::$utente->getKey('ditta') . ".jrxml";
        if (!is_file($path_file)) {
            $path_file = ITA_BASE_PATH . "/reports/PagoPA/" . $nomeReport . ".jrxml"; //se il file non c'è
        }
        $PA_jrdef_xml = "<jrDefinition>\n";
        $PA_jrdef_xml .= "<ReportFile>" . $path_file . "</ReportFile>\n";
        $PA_jrdef_xml .= "<OutputFile>" . $OUTPUT_FILE . "</OutputFile>\n";
        $PA_jrdef_xml .= "<DataSource class=\"JREmptyDataSource\" count=\"1\"></DataSource>\n";
        foreach ($params as $key => $value) {
            $PA_jrdef_xml .= "<Parameter name=\"" . $key . "\" class=\"String\"><![CDATA[" . $value . "]]></Parameter>\n";
        }
        $PA_jrdef_xml .= "</jrDefinition>\n";
        if (fwrite($xjhPA, $PA_jrdef_xml) === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        /*
         * GENERAZIONE STAMPE PA
         */
        if (fwrite($xjhPA, "</root>") === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        if (fclose($xjhPA) === false) {
            $this->setErrMessage('Errore nella chiusura del file.');
            return false;
        }
        $stdOutDest = '';
        $commandJrPA = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJRGenerator/ItaJrGenerator.jar ' . $xmlJrDefPAPath . $stdOutDest;
        exec($commandJrPA, $outJrPA, $retJrPA);
        if ($retJrPA != '0') {
            $this->setErrMessage("Errore in Generazione AG <br><br> ($jrErrPA) - " . print_r($outJrPA, true) . " - $retJrPA<br><br>" . $commandJrPA);
            return false;
        }
        return $OUTPUT_FILE;
    }

    private function generaBollettinoRate($agidScadenza1, $agidScadenza2, $agidScadenza3 = array()) {
        if (!$agidScadenza1) {
            $this->setErrMessage("Scadenza non definita");
            return false;
        }
        $soggetto1 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza1['PROGSOGG'], $agidScadenza1['PROGSOGGEX']);
        if (!$soggetto1) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        $soggetto2 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza2['PROGSOGG'], $agidScadenza2['PROGSOGGEX']);
        if (!$soggetto2) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        if ($agidScadenza3) {
            $soggetto3 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza3['PROGSOGG'], $agidScadenza3['PROGSOGGEX']);
            if (!$soggetto3) {
                $this->setErrMessage("Soggetto non definito");
                return false;
            }
        }
        //parametrizzazioni
        $parametriInt = $this->getCustomConfIntermediario();
        $GLN = $parametriInt['GLN_ENTE'];
        $aux_digit = $parametriInt['AUXDIGIT'];
        $CFEnte = cwbParGen::getPIVAEnte();
        $LOGO = 'enti/ente' . App::$utente->getKey('ditta') . '/logo_' . App::$utente->getKey('ditta') . '.jpg';
        //parametri conto
        $parametriConto_arr = $this->getLibDB_BWE()->leggiBweTippenPPA(array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD']));
        $parametriConto = $parametriConto_arr[0];
        /*
         * PARAMETRI RATA 1
         */
        //QRCode
        $imp1 = (int) round($agidScadenza1['IMPDAPAGTO'] * 100, 0);
        $QRCode1 = "PAGOPA|002|" . $aux_digit . $agidScadenza1['IUV'] . "|" . $CFEnte . "|" . ($imp1);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo1 = explode(".", $agidScadenza1['IMPDAPAGTO']);
        $Denominazione1 = trim($soggetto1['COGNOME'] . " " . $soggetto1['NOME']);
        $Code128_1 = "18" . $aux_digit . $agidScadenza1['IUV'] .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo1['0'], 8, '0', STR_PAD_LEFT) . $impo1['1'] .
                "3" . "896";
        $Datamatrix1 = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128_1 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto1['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione1, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza1['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";
        /*
         * PARAMETRI RATA 2
         */
        //QRCode
        $imp2 = (int) round($agidScadenza2['IMPDAPAGTO'] * 100, 0);
        $QRCode2 = "PAGOPA|002|" . $aux_digit . $agidScadenza2['IUV'] . "|" . $CFEnte . "|" . ($imp2);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo2 = explode(".", $agidScadenza2['IMPDAPAGTO']);
        $Denominazione2 = trim($soggetto2['COGNOME'] . " " . $soggetto2['NOME']);
        $Code128_2 = "18" . $aux_digit . $agidScadenza2['IUV'] .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo2['0'], 8, '0', STR_PAD_LEFT) . $impo2['1'] .
                "3" . "896";
        $Datamatrix2 = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128_2 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto2['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione2, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza2['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";
        /*
         * PARAMETRI RATA 3
         */
        if ($agidScadenza3) {
            //QRCode
            $imp3 = (int) round($agidScadenza3['IMPDAPAGTO'] * 100, 0);
            $QRCode3 = "PAGOPA|002|" . $aux_digit . $agidScadenza3['IUV'] . "|" . $CFEnte . "|" . ($imp3);
            //DATAMATRIX
            $indirizzamento = 'codfase=';
            $cod_accettazione = 'NBPA';
            $separatore = ';';
            $impo3 = explode(".", $agidScadenza3['IMPDAPAGTO']);
            $Denominazione3 = trim($soggetto3['COGNOME'] . " " . $soggetto3['NOME']);
            $Code128_3 = "18" . $aux_digit . $agidScadenza3['IUV'] .
                    "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                    "10" . str_pad($impo3['0'], 8, '0', STR_PAD_LEFT) . $impo3['1'] .
                    "3" . "896";
            $Datamatrix3 = $indirizzamento .
                    $cod_accettazione .
                    $separatore .
                    $Code128_3 .
                    "1" .
                    "P1" .
                    str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                    str_pad($soggetto3['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                    str_pad($Denominazione3, 40, " ", STR_PAD_RIGHT) .
                    str_pad($agidScadenza3['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                    str_pad("", 12, " ", STR_PAD_RIGHT) .
                    "A";
        }

        //preparo array di parametri da passare al report
        $params = array(
            "Ente" => cwbParGen::getDesente(),
            "daPagina" => 1,
            "IUV1" => $aux_digit . $agidScadenza1['IUV'],
            "Importo1" => number_format($agidScadenza1['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza1" => substr($agidScadenza1['DATASCADE'], 8, 2) . "/" . substr($agidScadenza1['DATASCADE'], 5, 2) . "/" . substr($agidScadenza1['DATASCADE'], 0, 4),
            "QRcode1" => $QRCode1,
            "IUV2" => $aux_digit . $agidScadenza2['IUV'],
            "Importo2" => number_format($agidScadenza2['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza2" => substr($agidScadenza2['DATASCADE'], 8, 2) . "/" . substr($agidScadenza2['DATASCADE'], 5, 2) . "/" . substr($agidScadenza2['DATASCADE'], 0, 4),
            "QRcode2" => $QRCode2,
            "IUV3" => $aux_digit . $agidScadenza3['IUV'],
            "Importo3" => number_format($agidScadenza3['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza3" => substr($agidScadenza3['DATASCADE'], 8, 2) . "/" . substr($agidScadenza3['DATASCADE'], 5, 2) . "/" . substr($agidScadenza3['DATASCADE'], 0, 4),
            "QRcode3" => $QRCode3,
            "Anno" => $agidScadenza1['ANNORIF'],
            "LOGO" => $LOGO,
            "CODICE" => $soggetto1['PROGSOGG'],
            "DENOMINAZIONE" => $Denominazione1,
            "CODICEFISCALE" => $soggetto1['CODFISCALE'],
            "Causale1" => $agidScadenza1['DESCRPEND'],
            "Causale2" => $agidScadenza2['DESCRPEND'],
            "Causale3" => $agidScadenza3['DESCRPEND'],
            "CFENTE" => $CFEnte,
            "INTESTATO" => $parametriConto['CCINFO'],
            "NCCP" => $parametriConto['CONTO_CORRENTE'],
            "AUTORIZZAZIONE" => $parametriConto['AUTORIZZAZIONE'],
            "Datamatrix1" => $Datamatrix1,
            "Datamatrix2" => $Datamatrix2,
            "Datamatrix3" => $Datamatrix3,
            "CBILL" => $parametriConto['CBILL'],
            "RataI" => 1,
            "RataII" => 2,
            "RataIII" => 3,
        );



        //creo il task per la generazione della lettera PA
        $subPath = itaLib::getAppsTempPath('STAMPEPA_' . time());
        if (!is_dir($subPath)) {
            $subPath = itaLib::createAppsTempPath('STAMPEPA_' . time());
        }
        if (!$subPath) {
            $this->setErrMessage("Errore nella creazione della path temporanea");
            return false;
        }
        $OUTPUT_FILE = $subPath . DIRECTORY_SEPARATOR . uniqid('AvvisoPA_') . time() . ".pdf";
        $xmlJrDefPAPath = $subPath . "/xmljrdefPA.xml";
        $xjhPA = fopen($xmlJrDefPAPath, 'w');
        if ($xjhPA === false) {
            $this->setErrMessage('Errore nella apertura del File.');
            return false;
        }
        if (fwrite($xjhPA, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n") === false) {
            Out::msgStop('Errore', 'Errore nella scrittura file: Testata xml.');
            return false;
        }
        if (fwrite($xjhPA, "<root>\n") === false) {
            $this->setErrMessage('Errore nella scrittura file: <root>');
            return false;
        }

        if ($agidScadenza3) {
            $nomeReport = 'PAGOPA3Rate_AVVISO';
        } else {
            $nomeReport = 'PAGOPA2Rate_AVVISO';
        }

        $path_file = ITA_BASE_PATH . "/reports/PagoPA/enti/ente" . App::$utente->getKey('ditta') . "/" . $nomeReport . "_" . App::$utente->getKey('ditta') . ".jrxml";
        if (!is_file($path_file)) {
            $path_file = ITA_BASE_PATH . "/reports/PagoPA/" . $nomeReport . ".jrxml"; //se il file non c'è
        }
        $PA_jrdef_xml = "<jrDefinition>\n";
        $PA_jrdef_xml .= "<ReportFile>" . $path_file . "</ReportFile>\n";
        $PA_jrdef_xml .= "<OutputFile>" . $OUTPUT_FILE . "</OutputFile>\n";
        $PA_jrdef_xml .= "<DataSource class=\"JREmptyDataSource\" count=\"1\"></DataSource>\n";
        foreach ($params as $key => $value) {
            $PA_jrdef_xml .= "<Parameter name=\"" . $key . "\" class=\"String\"><![CDATA[" . $value . "]]></Parameter>\n";
        }
        $PA_jrdef_xml .= "</jrDefinition>\n";
        if (fwrite($xjhPA, $PA_jrdef_xml) === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        /*
         * GENERAZIONE STAMPE PA
         */
        if (fwrite($xjhPA, "</root>") === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        if (fclose($xjhPA) === false) {
            $this->setErrMessage('Errore nella chiusura del file.');
            return false;
        }
        $stdOutDest = '';
        $commandJrPA = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJRGenerator/ItaJrGenerator.jar ' . $xmlJrDefPAPath . $stdOutDest;
        exec($commandJrPA, $outJrPA, $retJrPA);
        if ($retJrPA != '0') {
            $this->setErrMessage("Errore in Generazione AG <br><br> ($jrErrPA) - " . print_r($outJrPA, true) . " - $retJrPA<br><br>" . $commandJrPA);
            return false;
        }
        return $OUTPUT_FILE;
    }

}
