<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeMonitorHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMaster.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once(ITA_LIB_PATH . '/itaPHPEFill/itaEFillClient.class.php');
include_once(ITA_LIB_PATH . '/zip/itaZipCommandLine.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtTributiHelper.php';

/**
 *
 * Classe per la gestione dell'intermediario E-Fil (PagoPa)
 * 
 */
class cwbPagoPaEfil extends cwbPagoPaMaster {

    public function __construct() {
        parent::__construct();
        $this->sftpUtils = new itaSFtpUtils();
    }

    const TIPO_DATA = 1;
    const TIPO_STRINGA = 2;
    const TIPO_INTERO = 3;
    const TIPO_DECIMALE = 4;
    const SEPARATORE_DECIMALE = '.';
    const SUFFISSO_ACCETTAZIONE = 'RicevutaAccettazione_';
    const SUFFISSO_PUBBLICAZIONE = 'RicevutaPubblicazione_';
    const SUFFISSO_CANCELLAZIONE = 'RicevutaCancellazione_';
    // web service TODO vedere dove metterli
    const ENDPOINT_BOLLETTINO_TEST = 'https://generatorpdf.integrazione.plugandpay.it/GeneratorPdf.svc'; //test
    const ENDPOINT_BOLLETTINO_PRODUZIONE = 'https://generatorpdf.plugandpay.it/GeneratorPdf.svc'; // produzione
    const ENDPOINT_PAYMENT_TEST = 'https://pos.integrazione.plugandpay.it/WsPayment/DigitBusPayment.svc'; // test
    const ENDPOINT_PAYMENT_PRODUZIONE = 'https://pos.plugandpay.it/Payment/DigitBusPayment.svc'; // produzione
    const ENDPOINT_FEED_TEST = 'https://services.integrazione.plugandpay.it/Feed/DigitBusFeed.svc'; // test
    const ENDPOINT_FEED_PRODUZIONE = 'https://services.plugandpay.it/Feed/DigitBusFeed.svc'; // produzione
    const ENDPOINT_DELIVER_TEST = 'https://services.integrazione.plugandpay.it/Deliver/DigitBusDeliver.svc'; // test
    const ENDPOINT_DELIVER_PRODUZIONE = 'https://services.plugandpay.it/Deliver/DigitBusDeliver.svc'; // produzione
    const NAMESPACES_PAYMENT_PLUG1 = "http://e-fil.eu/PnP/PlugAndPayCommon";
    const NAMESPACES_PAYMENT_PLUG = "http://e-fil.eu/PnP/PlugAndPayPayment";
    const NAMESPACES_FEED_PLUG = "http://e-fil.eu/PnP/PlugAndPayFeed";
    const NAMESPACES_FEED_PLUG1 = "http://e-fil.eu/PnP/PlugAndPayCommon";
    const NAMESPACES_DELIVER_PLUG = "http://e-fil.eu/PnP/PlugAndPayDeliver";
    const NAMESPACES_BOLLETTINO_PLUG = "http://e-fil.eu/GeneratorPdf";
    const SOAP_ACTION_PAYMENT_PREFIX = 'http://e-fil.eu/PnP/PlugAndPayPayment/IPlugAndPayPayment/';
    const SOAP_ACTION_FEED_PREFIX = 'http://e-fil.eu/PnP/PlugAndPayFeed/IPlugAndPayFeed/';
    const SOAP_ACTION_DELIVER_PREFIX = 'http://e-fil.eu/PnP/PlugAndPayDeliver/IPlugAndPayDeliver/';
    const SOAP_ACTION_BOLLETTINO_PREFIX = 'http://e-fil.eu/GeneratorPdf/IGeneratorPdfBytes/';
    const ESITO_POSITIVO = 'Ok';
    const ESITO_NEGATIVO = 'Nok';

    // mapping con lunghezza e tipo di ogni campo dell'array usato per generare il txt finale, 
    // ordinato per posizione
    private $mappingCampiPubbl = array(
        'PROGKEYTAB' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 10, 'OBBLIGATORIO' => true), // progressivo record
        'TIPORIFCRED' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => true),
        'CODRIFERIMENTO' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => true),
        'NUMAVVISO' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'IUV' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE1' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE2' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE3' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE4' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE5' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'POSIZIONE6' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'DATASCADE' => array('TIPO' => self::TIPO_DATA, 'LUNGHEZZA' => 8, 'OBBLIGATORIO' => false), // AAAAMMGG
        'IMPDAPAGTO' => array('TIPO' => self::TIPO_DECIMALE, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => true),
        'CAUSALEVER' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 100, 'OBBLIGATORIO' => true),
        'FILLER' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 40, 'OBBLIGATORIO' => false),
        'TIPOPERS' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 2, 'OBBLIGATORIO' => true),
        'CODFISCALE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => true),
        'ANADEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 50, 'OBBLIGATORIO' => true),
        'INDIRDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 50, 'OBBLIGATORIO' => false),
        'NCIVIDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 5, 'OBBLIGATORIO' => false),
        'CAPDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 5, 'OBBLIGATORIO' => false),
        'LOCALDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 50, 'OBBLIGATORIO' => false),
        'PROVDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 2, 'OBBLIGATORIO' => false),
        'STATODEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'NOTEDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'CELLDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'EMAILDEBITORE' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 120, 'OBBLIGATORIO' => false),
        'DTINIVAL' => array('TIPO' => self::TIPO_DATA, 'LUNGHEZZA' => 8, 'OBBLIGATORIO' => false), // AAAAMMGG
        'DTFINVAL' => array('TIPO' => self::TIPO_DATA, 'LUNGHEZZA' => 8, 'OBBLIGATORIO' => false), // AAAAMMGG
        'TIPORIFCRED2' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'IDRATAUNICA' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => false),
        'NUMRATA' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 2, 'OBBLIGATORIO' => false),
        'CODACCERTAMENTO1' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO1' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO2' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO2' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO3' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO3' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO4' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO4' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO5' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO5' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO6' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO6' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO7' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO7' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO8' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO8' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO9' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO9' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO10' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO10' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO11' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO11' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO12' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO12' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO13' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO13' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO14' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO14' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'CODACCERTAMENTO15' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 30, 'OBBLIGATORIO' => false),
        'IMPORTOACCERTAMENTO15' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 12, 'OBBLIGATORIO' => false), // in centesimi
        'FILLER2' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 337, 'OBBLIGATORIO' => false),
        'FILLER3' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 1, 'OBBLIGATORIO' => false) // indica il carattere da usare come filler in tutta la riga del txt, da prendere su BGE_AGID_CONF_EFIL CAMPO FILLER
    );
    private $mappingCampiCanc = array(
        'PROGKEYTAB' => array('TIPO' => self::TIPO_INTERO, 'LUNGHEZZA' => 10, 'OBBLIGATORIO' => true), // progressivo record
        'TIPORIFCRED' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => true),
        'CODRIFERIMENTO' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 35, 'OBBLIGATORIO' => true),
        'FILLER' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 119, 'OBBLIGATORIO' => false),
        'FILLER2' => array('TIPO' => self::TIPO_STRINGA, 'LUNGHEZZA' => 1, 'OBBLIGATORIO' => false),
    );
    private $sftpUtils;

    public function inserisciBgeAgidStoscaEfil($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_STOSCA_EFIL', true, $startedTransaction);
    }

    public function aggiornaBgeScadenzeEfil($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCA_EFIL', false, $startedTransaction);
    }

    public function inserisciBgeAgidScaEfil($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCA_EFIL', true, $startedTransaction);
    }

    public function aggiornaBgeAgidRiscoEfil($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_RISCO_EFIL', false, $startedTransaction);
    }

    public function inserisciBgeAgidRiscoEfil($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_RISCO_EFIL', true, $startedTransaction);
    }

    // Creo array con tutte le posizioni debitorie, da passare poi alla routine che si occuperï¿½ di scorrere l'array e aggiungere su file.
    public function creaPosizioniDebitoriePubblicazione($scadenzePerPubbli) {
        $progressivoTxt = 0;
        foreach ($scadenzePerPubbli as $scadenza) {
            if ($scadenza['NUMRATE']) {
                $numrata = str_pad($scadenza['NUMRATE'], 2, "0", STR_PAD_LEFT);
                $tiporifcred2 = '';
            } else if (intval($scadenza['TIPOPENDEN']) === 2) {
                $numrata = '';
                $tiporifcred2 = $scadenza['TIPORIFCRED'];
            } else {
                $numrata = '';
                $tiporifcred2 = '';
            }

            $libDB_BTA = new cwbLibDB_BTA();
            // controllo se ci sono accertamenti (rate)           
            $bta_servrenddet = $libDB_BTA->leggiBtaServrendAccertamentiEmissione(array('ANNOEMI' => $scadenza['ANNOEMI'],
                'NUMEMI' => $scadenza['NUMEMI'],
                'IDBOL_SERE' => $scadenza['IDBOL_SERE'],
                'CODTIPSCAD' => $scadenza['CODTIPSCAD'],
                'SUBTIPSCAD' => $scadenza['SUBTIPSCAD'])
                    , true);
            $arrayAccertamenti = array();
            if (intval(count($bta_servrenddet)) >= 1) {
                $scadet = $this->getLibDB_BGE()->leggiBgeAgidScadet(array('IDSCADENZA' => $scadenza['PROGKEYTAB']));
                foreach ($scadet as $key => $value) {
                    $arrayAccertamenti[] = array(
                        'Accertamento' => array(
                            'Codice' => $value['CODICE'],
                            'ImportoInCentesimi' => $value['IMPORTO'] * 100
                        )
                    );
                }
            }
            $tipoPers = $scadenza['TIPOPERS'] === 'F' ? 'CF' : 'PI';
            //$codFiscale = $scadenza['TIPOPERS'] === 'F' ? $scadenza['CODFISCALE'] : $scadenza['PARTIVA'];
            $anaDebitore = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
            $anaDebitore = $anaDebitore['ANADEBITORE'] === '' ? 'ANONIMO' : $anaDebitore['ANADEBITORE'];
            $posizioneDebitoria = array(
                'PROGKEYTAB' => ++$progressivoTxt, // progressivo record 
                'TIPORIFCRED' => $scadenza['TIPORIFCRED'],
                'CODRIFERIMENTO' => $scadenza['CODRIFERIMENTO'],
                'NUMAVVISO' => $scadenza['NUMAVVISO'],
                'IUV' => $scadenza['IUV'],
                'DATASCADE' => $scadenza['DATASCADE'], // AAAAMMGG
                'IMPDAPAGTO' => $scadenza['IMPDAPAGTO'],
                'CAUSALEVER' => $scadenza['DESCRPEND'],
                'TIPOPERS' => $tipoPers,
                'CODFISCALE' => $scadenza['CODFISCALE'],
                'ANADEBITORE' => $anaDebitore,
                'DTINIVAL' => '', // AAAAMMGG
                'DTFINVAL' => '', // AAAAMMGG
                'TIPORIFCRED2' => $tiporifcred2,
                'IDRATAUNICA' => $scadenza['IDRATAUNICA'],
                'NUMRATA' => $numrata,
            );
            if ($arrayAccertamenti) {
                $index = 1;
                // aggiungo gli n accertamenti all'array
                foreach ($arrayAccertamenti as $value) {
                    $posizioneDebitoria['IMPORTOACCERTAMENTO' . $index] = $value['Accertamento']['ImportoInCentesimi'];
                    $posizioneDebitoria['CODACCERTAMENTO' . $index] = $value['Accertamento']['Codice'];
                    $index++;
                }
            }

            $posizioniDebitorie[] = $posizioneDebitoria;
        }
        return $posizioniDebitorie;
    }

    // Creo array con tutte le posizioni debitorie, da passare poi alla routine che si occuperï¿½ di scorrere l'array e aggiungere su file.
    public function creaPosizioniDebitorieCancellazione($scadenzePerCanc) {
        $index = 0;
        foreach ($scadenzePerCanc as $scadenza) {

            $posizioniDebitorie[] = array(
                'PROGKEYTAB' => ++$index, // progressivo record 
                'TIPORIFCRED' => $scadenza['TIPORIFCRED'],
                'CODRIFERIMENTO' => $scadenza['CODRIFERIMENTO'],
            );
        }
        return $posizioniDebitorie;
    }

//    public function cancellazioneMassiva() {
//    }
    // Cancellazione posizione debitorie su NODO.
    public function customCancellazioneMassiva() {
        // Metodo per cancellazione di un flusso per ogni SERVIZIO 
        $scadenzeGroup = $this->getScadenzePerCancellazione();
        if (!$scadenzeGroup) {
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 2,
                    "ESITO" => 2,
                    "KEYOPER" => $progkeytabInvio,
                );
                $this->scriviLog($log);
            }
            return true;
        } else {

            $errore = null;
            $msgError = null;
            foreach ($scadenzeGroup as $scadenza) {

                $scadenzePerCanc = $this->leggiScadenzePerCancellazione($scadenza['CODSERVIZIO']);
                if ($scadenzePerCanc) {
                    // Scorro array per flusso, verifico se BGE_AGID_SCADENZA.STATO = 3
                    // Se ï¿½ cosï¿½, aggiorno scadenza con STATO = 7 e lo elimino dall'array che servirï¿½ per la creazione del flusso di cancellazione
                    $scadenzePerCanc = $this->verificaSospesiPerCancellazione($scadenzePerCanc);
                    if ($scadenzePerCanc) {
                        // Prepara File (Genera nome + file vero e proprio).
                        if ($this->customPreparazionePerCancellazione($scadenzePerCanc, $nomeFile, $file, $numPosizioni, $scadenza['CODSERVIZIO'])) {
                            cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());

                            try {
                                // INSERT su BGE_AGID_INVII
                                $progint = $this->generaProgressivoFlusso(date('Ymd'), $scadenza['CODSERVIZIO'], 2);
                                $invio = array(
                                    'TIPO' => 2,
                                    'INTERMEDIARIO' => 1,
                                    'CODSERVIZIO' => $scadenza['CODSERVIZIO'],
                                    'DATAINVIO' => date('Ymd'),
                                    'PROGINT' => $progint,
                                    'NUMPOSIZIONI' => count($scadenzePerCanc),
                                    'STATO' => 1,
                                );
                                $this->insertBgeAgidInvii($progkeytabInvio, $invio);

                                $devLib = new devLib();
                                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                                // Costruisco array per salvataggio allegato
                                $allegati = array(
                                    'TIPO' => 2,
                                    'IDINVRIC' => $progkeytabInvio,
                                    'DATA' => date('Ymd'),
                                    'NOME_FILE' => $nomeFile,
                                );

                                if (intval($configGestBin['CONFIG']) === 0) {
                                    //INSERT su BGE_AGID_ALLEGATI
                                    $allegati['ZIPFILE'] = file_get_contents($file);
                                    $this->insertBgeAgidAllegati($allegati);
                                } else {
                                    $allegati['ZIPFILE'] = 'ffff';
                                    $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                    $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                    $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($file), $nomeAllegato);
                                }

                                //UPDATE su BGE_AGID_SCADENZE 
                                foreach ($scadenzePerCanc as $scadenzeC) {
                                    $filtri['PROGCITYSC'] = $scadenzeC['PROGCITYSC'];
                                    $count = $this->getLibDB_BGE()->leggiBgeAgidScadenzeCountPerStato($filtri);
                                    if (intval($count[0]['CONTA']) === 0) {
                                        $stato = 6;
                                    } else {
                                        $stato = 8;
                                    }
                                    $toUpdate['PROGKEYTAB'] = $scadenzeC['PROGKEYTAB'];
                                    $toUpdate['STATO'] = $stato;
                                    $toUpdate['PROGINV'] = $progkeytabInvio;
                                    $toUpdate['DATAINVIO'] = date('Ymd');
                                    $toUpdate['TIMEINVIO'] = date('H:i:s');
                                    $this->aggiornaBgeScadenze($toUpdate);
                                }
                                if (!$this->getSimulazione()) {
                                    if (!$this->customInvioPerCancellazione($nomeFile, $file, $scadenza['CODSERVIZIO'])) {
                                        $errore = 1;
                                        $msgError = 'Errore Invio Fornitura Cancellazione';
                                    }
                                }
                            } catch (Exception $exc) {
                                $errore = 1;
                                $msgError = "Errore: " . $exc->getMessage();
                            }

                            unlink($file);
                            if ($errore) {
                                // errore generico
                                cwbDBRequest::getInstance()->rollBackManualTransaction();
                                unlink($nomeAllegato);

                                if ($this->getSimulazione() != true) {
                                    $log = array(
                                        "LIVELLO" => 3,
                                        "OPERAZIONE" => 2,
                                        "ESITO" => 3,
                                        "KEYOPER" => 0,
                                    );
                                    $this->scriviLog($log);
                                }
                                $this->handleError(-1, $msgError);
                                return false;
                            } else {
                                // ok
                                cwbDBRequest::getInstance()->commitManualTransaction();

                                if ($this->getSimulazione() != true) {
                                    $log = array(
                                        "LIVELLO" => 5,
                                        "OPERAZIONE" => 2,
                                        "ESITO" => 1,
                                        "KEYOPER" => $progkeytabInvio,
                                    );
                                    $this->scriviLog($log);
                                }
                                //Reperisco informazioni dell'intermediario e del servizio
                                //$result = $this->trovaIntermediarioeServizio($scadenza['INTERMEDIARIO'], $scadenza['CODSERVIZIO']);
                                //if ($result) {
                                //    $arrayMonitor = $this->buildArrayMonitor('Cancellazione', $result['INTERMEDIARIO'], $result['TIPORIFCRED'], 'OK', $nomeFile, $numPosizioni);
                                //    cwbBgeMonitorHelper::generaInviaXmlMonitor($arrayMonitor, $nomeFile, $zipFile);
                                // }
                                return true;
                            }
                        } else {
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 2,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $this->handleError(-1, "Errore Preparazione Fornitura di Cancellazione");
                            return false;
                        }
                    } else {
                        // no errore ma nessuna elaborazione dati
                        if ($this->getSimulazione() != true) {
                            $log = array(
                                "LIVELLO" => 5,
                                "OPERAZIONE" => 2,
                                "ESITO" => 2,
                                "KEYOPER" => 0,
                            );
                            $this->scriviLog($log);
                        }
                        return true;
                    }
                } else {
                    // no errore ma nessuna elaborazione dati
                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 2,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                    return true;
                }
            }
            return $risultato;
        }
    }

    private function verificaSospesiPerCancellazione($scadenzePerCanc) {

        // Tutte le scadenze scartate (STATO=3) le elimino dall'array di fornitura e aggiorno lo STATO a 7 (Cancellata)
        foreach ($scadenzePerCanc as $key => $value) {
            if (intval($value['STATO']) === 3) {
                // Leggo la scadenza per aggiornarla (le scadenze nell'array $scadenzePerCanc sono il risultato di una JOIN... 
                // devo rileggerla per forza altrimenti mi darebbe errore in UPDATE
                $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($value['PROGKEYTAB']);
                $scadenza['STATO'] = 7;
                $this->aggiornaBgeScadenze($scadenza);
                unset($scadenzePerCanc[$key]);
            } elseif (intval($value['STATO'] === 5)) {
                // Verifico se la scadenza con STATO = 5 sul NODO risulta 'Pagata' o 'PagataParzialmente'
                // Se cosi fosse, tolgo l'elemento dall'array perchï¿½ essendo pagata non la posso eliminare dal NODO
                $params = array(
                    'CodiceIdentificativo' => $value['IUV'],
                    'FormatoRitorno' => 'json',
                );
                $result = $this->ricercaPosizioneDaIUV($value['IUV']);
                if ($result) {
                    //$jsonResult = json_decode(base64_decode($result), true);
                    $situazioneScadenza = $result['Posizione']['StatoPosizione'];
                    if ($situazioneScadenza !== 'NonPagata') {
                        unset($scadenzePerCanc[$key]);
                    }
                }
            }
        }
        return $scadenzePerCanc;
    }

    public function reinvioPubblicazione($progkeytabInvio) {
        
    }

    protected function verificaVariazione($filtri) {
        $arrayProgkeytab = array();
        $anagrafiche = $this->getLibDB_BGE()->leggiBgeAgidScadenzeVarAna($filtri);
        try {
            if ($anagrafiche) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                // aggiorno le tabelle BGE_AGID_SCADENZE e BGE_AGID_SCA_EFIL con i campi estratti dalla select
                foreach ($anagrafiche as $key => $value) {
                    if ($value['TIPOPERS'] === 'F' || !$value['PARTIVA']) {
                        $codFiscale = $value['CODFISCALE'];
                    } elseif ($value['TIPOPERS'] === 'G') {
                        $codFiscale = $value['PARTIVA'];
                    }
                    // Aggiorno la tabella BGE_AGID_SCADENZE 
                    $scadenzeUpdate = array();
                    $scadenzeUpdate = array(
                        "PROGKEYTAB" => $value['PROGKEYTAB'],
                        "TIPOPERS" => $value['TIPOPERS'],
                        "CODFISCALE" => $codFiscale,
                        "DATASCADE" => $value['DATASCADE'],
                        "STATO" => 1,
                    );
                    $this->aggiornaBgeScadenze($scadenzeUpdate);
                    $arrayProgkeytab[] = $value['PROGKEYTAB'];

                    // Aggiorno la tabella BGE_AGID_SCA_EFIL 
                    if ($this->getSimulazione() != true) {
                        // Array Bind Parameters
                        $sqlParams = array();
                        $sqlParams[] = array('name' => 'PROGKEYTAB', 'value' => $value['PROGKEYTAB'], 'type' => PDO::PARAM_INT);
                        $sqlParams[] = array('name' => 'ANADEBITORE', 'value' => $value['RAGSOC'], 'type' => PDO::PARAM_STR);
                        $sqlString = "UPDATE BGE_AGID_SCA_EFIL SET ANADEBITORE=:ANADEBITORE"
                                . " WHERE PROGKEYTAB=:PROGKEYTAB";
                        $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);
                    }
                }
                cwbDBRequest::getInstance()->commitManualTransaction();
                return $arrayProgkeytab;
            } else {
                return null;
            }
        } catch (Exception $exc) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            $this->handleError(-1, $exc->getMessage());
        }

        return false;
    }

    //Gestione ricevuta Accettazione di Pubblicazione
    public function customRicevutaAccettazionePubblicazione() {
        $risultato = true;
        $msgError = '';
        $confEfil = $this->leggiConfEfil();
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $key => $value) {

                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }
                $codServizio = str_pad($value['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathAccettazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTRIC'], $codServizio);

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                // Select per leggere dagli invii i file che dovrï¿½ leggere dall'sFTP
                $list = $this->nomeFileDaElaborare(1, 1, $value['CODSERVIZIO']);

                if ($list) {

                    // scorro i filename
                    foreach ($list as $value) {

                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            if ($sftp->downloadFile($pathAccettazione . self::SUFFISSO_ACCETTAZIONE . trim($value['NOME_FILE']) . ".xml")) {
                                $xml = $sftp->getResult();

                                $arrayXml = $this->xmlToArray($xml);

                                //reperimento dati invio
                                $datiInvio = $this->reperisciDatiInvio(trim($value['NOME_FILE']));

                                if ($datiInvio) {
                                    // Salvataggio record su BGE_AGID_RICEZ
                                    $ricezione = array(
                                        "TIPO" => 11,
                                        "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                                        "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                                        "DATARIC" => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        "IDINV" => $datiInvio['PROGKEYTAB'],
                                    );
                                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                    // Salvataggio record su BGE_AGID_ALLEGATI
                                    $pathZip = $this->creaZip(trim($value['NOME_FILE']) . '.xml', $xml);
                                    if (!$pathZip) {
                                        $errore = true;
                                    }
                                    $devLib = new devLib();
                                    $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                                    // Costruisco array per salvataggio allegato
                                    $allegati = array(
                                        'TIPO' => 11,
                                        'IDINVRIC' => $progkeytabRicez,
                                        'DATA' => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        'NOME_FILE' => trim($value['NOME_FILE']),
                                    );

                                    if (intval($configGestBin['CONFIG']) === 0) {
                                        //INSERT su BGE_AGID_ALLEGATI
                                        $allegati['ZIPFILE'] = file_get_contents($pathZip);
                                        $this->insertBgeAgidAllegati($allegati);
                                    } else {
                                        $allegati['ZIPFILE'] = 'ffff';
                                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                                    }

                                    unlink($pathZip);

                                    //Controllo esito ricevuta e aggiorno Invio
                                    if ($arrayXml['Esito'][0]['@textNode'] === 'Accettata') {

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 2);

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 5;
                                        $esitoLog = 1;

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        // if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //     $this->invioArrayMonitor('Ricevuta di Accettazone di Pubblicazione', 'OK', $result, $zipFile);
                                        // }
                                    } else if ($arrayXml['Esito'][0]['@textNode'] === 'Rifiutata') {

                                        // Se la fornitura risulta 'Rifiutata', richiamo metodo per gestione 
                                        $this->fornituraRifiutata($arrayXml, $datiInvio['PROGKEYTAB'], 'Pubblicazione');

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 3;
                                        $esitoLog = 3;
                                        //Reperisco informazioni dell'intermediario e del servizio
                                        // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        // if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //    $this->invioArrayMonitor('Ricevuta di Accettazione di Pubblicazione', 'KO', $result, $zipFile);
                                        //}
                                    }
                                    // $operazione = 11;
                                    if ($this->getSimulazione() != true) {
                                        $log = array(
                                            "LIVELLO" => $livelloLog,
                                            "OPERAZIONE" => 11,
                                            "ESITO" => $esitoLog,
                                            "KEYOPER" => $progkeytabRicez,
                                        );
                                        $this->scriviLog($log, true);
                                    }
                                } else {
                                    // TODO Errore lettura da sftp    
                                    $errore = true;
                                    $msgError .= "Errore: Reperimento Dati Invio Servizio " . $codServizio;
                                }
                            } else {
                                // TODO Errore lettura da sftp    
                                $errore = true;
                                $msgError .= ' File: ' . trim($value['NOME_FILE']) . $sftp->getErrMessage() . ' ' . "Servizio:" . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= "Errore: " . $exc->getMessage() . " Servizio " . $codServizio;
                        }

                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 11,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    //Reperisco informazioni dell'intermediario e del servizio
                    //   $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                    //   if ($result) {
                    // Metodo per invio XML a MONITOR
                    //       $this->invioArrayMonitor('Ricevuta di Accettazione di Pubblicazione', 'OK', $result, $zipFile);
                    //   }

                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 11,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
            }
        } else {
            // Se non trovo nessun servizio attivo per l'intermediario, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
            // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
            // if ($result) {
            // Metodo per invio XML a MONITOR
            //     $this->invioArrayMonitor('Ricevuta di Accettazione di Pubblicazione', 'OK', $result, $zipFile);
            // }

            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 11,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    // Gestione ricevuta Accettazione di Cancellazione
    public function customRicevutaAccettazioneCancellazione() {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil();
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $key => $value) {
                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                $codServizio = str_pad($value['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathAccettazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTRIC'], $codServizio);

                // Select per leggere dagli invii i file che dovrï¿½ leggere dall'sFTP
                $list = $this->nomeFileDaElaborare(1, 2, $value['CODSERVIZIO']);

                if ($list) {

                    // scorro i filename
                    foreach ($list as $value) {

                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            if ($sftp->downloadFile($pathAccettazione . self::SUFFISSO_ACCETTAZIONE . trim($value['NOME_FILE']) . ".xml")) {
                                $xml = $sftp->getResult();

                                $arrayXml = $this->xmlToArray($xml);
                                //reperimento dati invio
                                $datiInvio = $this->reperisciDatiInvio(trim($value['NOME_FILE']));

                                if ($datiInvio) {
                                    // Salvataggio record su BGE_AGID_RICEZ
                                    $ricezione = array(
                                        "TIPO" => 16,
                                        "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                                        "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                                        "DATARIC" => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        "IDINV" => $datiInvio['PROGKEYTAB'],
                                    );
                                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                    // Salvataggio record su BGE_AGID_ALLEGATI
                                    $pathZip = $this->creaZip(trim($value['NOME_FILE']) . '.xml', $xml);
                                    $devLib = new devLib();
                                    $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

                                    // Costruisco array per salvataggio allegato
                                    $allegati = array(
                                        'TIPO' => 16,
                                        'IDINVRIC' => $progkeytabRicez,
                                        'DATA' => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        'NOME_FILE' => trim($value['NOME_FILE']),
                                    );

                                    if (intval($configGestBin['CONFIG']) === 0) {
                                        //INSERT su BGE_AGID_ALLEGATI
                                        $allegati['ZIPFILE'] = file_get_contents($pathZip);
                                        $this->insertBgeAgidAllegati($allegati);
                                    } else {
                                        $allegati['ZIPFILE'] = 'ffff';
                                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                                    }

                                    unlink($pathZip);

                                    //Controllo esito ricevuta e aggiorno Invio
                                    if ($arrayXml['Esito'][0]['@textNode'] === 'Accettata') {

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 2);

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 5;
                                        $esitoLog = 1;

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        //  $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        //  if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //      $this->invioArrayMonitor('Ricevuta di Accettazione di Cancellazione', 'OK', $result, $zipFile);
                                        // }
                                    } else if ($arrayXml['Esito'][0]['@textNode'] === 'Rifiutata') {

                                        // Se la fornitura risulta 'Rifiutata', richiamo metodo per gestione 
                                        $this->fornituraRifiutata($arrayXml, $datiInvio['PROGKEYTAB'], 'Cancellazione');

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 3;
                                        $esitoLog = 3;
                                        //Reperisco informazioni dell'intermediario e del servizio
                                        // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        // if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //     $this->invioArrayMonitor('Ricevuta di Accettazioen di Cancellazione', 'KO', $result, $zipFile);
                                        // }
                                    }
                                    if ($this->getSimulazione() != true) {
                                        $log = array(
                                            "LIVELLO" => $livelloLog,
                                            "OPERAZIONE" => 16,
                                            "ESITO" => $esitoLog,
                                            "KEYOPER" => $progkeytabRicez,
                                        );
                                        $this->scriviLog($log, true);
                                    }
                                } else {
                                    // TODO Errore lettura da sftp    
                                    $errore = true;
                                    $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore: Reperimento Dati Invio Fallito. " . "Servizio:" . $codServizio;
                                }
                            } else {
                                // TODO Errore lettura da sftp    
                                $errore = true;
                                $msgError .= ' File: ' . trim($value['NOME_FILE']) . $sftp->getErrMessage() . ' ' . "Servizio:" . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore:" . $exc->getMessage();
                        }

                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 16,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    //Reperisco informazioni dell'intermediario e del servizio
//                    $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
//                    if ($result) {
                    // Metodo per invio XML a MONITOR
//                        $this->invioArrayMonitor('Ricevuta di Accettazione di Cancellazione', 'OK', $result, $zipFile);
//                    }

                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 16,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
            }
        } else {
            // Se non trovo nessun servizio attivo per l'intermediario, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
//            $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
//            if ($result) {
//                // Metodo per invio XML a MONITOR
//                $this->invioArrayMonitor('Ricevuta di Accettazione di Cancellazione', 'OK', $result, $zipFile);
//            }

            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 16,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    // Gestione ricevuta Pubblicazione
    public function customRicevutaPubblicazione() {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil();
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $key => $value) {
                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }

                $codServizio = str_pad($value['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathPubblicazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTRIC'], $codServizio);

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                // Select per leggere dagli invii i file che dovrï¿½ leggere dall'sFTP
                $list = $this->nomeFileDaElaborare(2, 1, $value['CODSERVIZIO']);
                // leggo la lista di file
                if ($list) {
                    // scorro i filename
                    foreach ($list as $value) {
                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            if ($sftp->downloadFile($pathPubblicazione . self::SUFFISSO_PUBBLICAZIONE . trim($value['NOME_FILE']) . ".xml")) {
                                $xml = $sftp->getResult();

                                $arrayXml = $this->xmlToArray($xml);
                                //reperimento dati invio
                                $datiInvio = $this->reperisciDatiInvio(trim($value['NOME_FILE']));

                                if ($datiInvio) {
                                    // Salvataggio record su BGE_AGID_RICEZ
                                    $ricezione = array(
                                        "TIPO" => 12,
                                        "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                                        "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                                        "DATARIC" => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        "IDINV" => $datiInvio['PROGKEYTAB'],
                                    );
                                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                    // Salvataggio record su BGE_AGID_ALLEGATI
                                    $pathZip = $this->creaZip(trim($value['NOME_FILE']) . '.xml', $xml);
                                    $devLib = new devLib();
                                    $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

                                    // Costruisco array per salvataggio allegato
                                    $allegati = array(
                                        'TIPO' => 12,
                                        'IDINVRIC' => $progkeytabRicez,
                                        'DATA' => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        'NOME_FILE' => trim($value['NOME_FILE']),
                                    );

                                    if (intval($configGestBin['CONFIG']) === 0) {
                                        //INSERT su BGE_AGID_ALLEGATI
                                        $allegati['ZIPFILE'] = file_get_contents($pathZip);
                                        $this->insertBgeAgidAllegati($allegati);
                                    } else {
                                        $allegati['ZIPFILE'] = 'ffff';
                                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                                    }

                                    unlink($pathZip);

                                    //Controllo esito ricevuta e aggiorno Invio
                                    if ($arrayXml['NumeroPosizioniPubblicate'][0]['@textNode'] > 0) {

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 4);

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        //      $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        //      if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //         $this->invioArrayMonitor('Ricevuta di Pubblicazione', 'OK', $result, $zipFile);
                                        //     }
                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 5;
                                        $esitoLog = 1;
                                    } else if (!$arrayXml['NumeroPosizioniPubblicate'][0]['@textNode']) {

                                        //Leggo gli errori dall'xml, trasformo in Json e salvo sul campo NOTEERRORE
                                        $errori = $this->xmlErrori($arrayXml, 'NumeroPosizioniPubblicate', 'NumeroPosizioniScartate');
                                        $noteErrore = json_encode($errori);

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 6, $noteErrore);

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 3;
                                        $esitoLog = 3;

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        //$result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        // if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //    $this->invioArrayMonitor('Ricevuta di Pubblicazione', 'KO', $result, $zipFile);
                                        // }
                                    }

                                    // Gestione Scarti
                                    $this->gestioneScarti($arrayXml, $confEfil['FILLER']);

                                    //Tutti i record scartati sono stati aggiornati al punto precedente, cambiando lo stato a 3 (Sospeso).
                                    //Le restanti scadenze saranno aggiornate a "pubblicate in attesa dello IUV".
                                    //UPDATE su BGE_AGID_SCADENZE 
                                    $data = date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
                                    $ora = date('H:i:s', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));

                                    // Array Bind Parameters
                                    $sqlParams = array();
                                    $sqlParams[] = array('name' => 'DATAPUBBL', 'value' => $data, 'type' => PDO::PARAM_STR);
                                    $sqlParams[] = array('name' => 'TIMEPUBBL', 'value' => $ora, 'type' => PDO::PARAM_STR);
                                    $sqlParams[] = array('name' => 'PROGINV', 'value' => $datiInvio['PROGKEYTAB'], 'type' => PDO::PARAM_INT);

                                    if ($this->getSimulazione() != true) {
                                        $sqlString = "UPDATE BGE_AGID_SCADENZE SET STATO=4,DATAPUBBL=:DATAPUBBL,TIMEPUBBL=:TIMEPUBBL"
                                                . " WHERE PROGINV=:PROGINV AND STATO=2";
                                        $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);
                                        // $operazione = 12;
                                        if ($this->getSimulazione() != true) {
                                            $log = array(
                                                "LIVELLO" => $livelloLog,
                                                "OPERAZIONE" => 12,
                                                "ESITO" => $esitoLog,
                                                "KEYOPER" => $progkeytabRicez,
                                            );
                                            $this->scriviLog($log, true);
                                        }
                                    }
                                } else {
                                    // TODO Errore lettura da sftp    
                                    $errore = true;
                                    $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore: Reperimento Dati Invio Fallito. " . "Servizio:" . $codServizio;
                                }
                            } else {
                                // TODO Errore lettura da sftp    
                                $errore = true;
                                $msgError .= ' File: ' . trim($value['NOME_FILE']) . $sftp->getErrMessage() . ' ' . "Servizio:" . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore:" . $exc->getMessage();
                        }
                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 12,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    //Reperisco informazioni dell'intermediario e del servizio
                    //   $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                    // Metodo per invio XML a MONITOR
                    //    $this->invioArrayMonitor('Ricevuta di Pubblicazione', 'OK', $result, $zipFile);

                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 12,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
            }
        } else {
            // Se non trovo nessun servizio attivo per l'intermediario, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
            //$result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
            // Metodo per invio XML a MONITOR
            //$this->invioArrayMonitor('Ricevuta di Pubblicazione', 'OK', $result, $zipFile);

            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 12,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    //Gestione ricevuta Arricchita
    public function customRicevutaArricchita($params = array()) {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil();
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $key => $value) {
                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }
                $codServizio = str_pad($value['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathArricchita = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTARRIC'], $codServizio);

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                // Select per leggere dagli invii i file che dovrï¿½ leggere dall'sFTP
                $list = $this->nomeFileDaElaborare(4, 1, $value['CODSERVIZIO']);

                // leggo la lista di file
                if ($list) {
                    // scorro i filename
                    foreach ($list as $value) {
                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            if ($sftp->downloadFile($pathArricchita . trim($value['NOME_FILE']))) {
                                $zipFile = $sftp->getResult();
                                //$zip = new ZipArchive;
                                $pathZip = itaLib::getUploadPath() . "/zipTemp" . time() . '.zip';
                                file_put_contents($pathZip, $zipFile);
                                try {
                                    itaZipCommandLine::unzip($pathZip);
                                } catch (ItaException $e) {
                                    $errore = true;
                                }
                                $result = file_get_contents(itaLib::getUploadPath() . "/" . trim($value['NOME_FILE']));
                                $arrayTxt = explode("\n", $result);
                                //reperimento dati invio
                                $datiInvio = $this->reperisciDatiInvio(trim($value['NOME_FILE']));

                                if ($datiInvio) {
                                    // Salvataggio record su BGE_AGID_RICEZ
                                    $ricezione = array(
                                        "TIPO" => 13,
                                        "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                                        "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                                        "DATARIC" => date('Ymd'),
                                        "IDINV" => $datiInvio['PROGKEYTAB'],
                                    );
                                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                    // Costruisco array per salvataggio allegato
                                    $allegati = array(
                                        'TIPO' => 13,
                                        'IDINVRIC' => $progkeytabRicez,
                                        'DATA' => date('Ymd'),
                                        'NOME_FILE' => trim($value['NOME_FILE']),
                                    );

                                    if (intval($configGestBin['CONFIG']) === 0) {
                                        //INSERT su BGE_AGID_ALLEGATI
                                        $allegati['ZIPFILE'] = $zipFile;
                                        $this->insertBgeAgidAllegati($allegati);
                                    } else {
                                        $allegati['ZIPFILE'] = 'ffff';
                                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, $zipFile, $nomeAllegato);
                                    }

                                    unlink($pathZip);

                                    // Aggiorno Invio
                                    $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 5);

                                    // scorro .txt per aggiornare le scadenze
                                    foreach ($arrayTxt as $key => $value) {
                                        if ($value) {
                                            $toUpdate = array();
                                            $filtri = array();

                                            // Aggiorno BGE_AGID_SCADENZE
                                            $codRiferimento = trim(str_replace($confEfil['FILLER'], "", substr($value, 45, 35)));
                                            $filtri['CODRIFERIMENTO'] = $codRiferimento;
                                            $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);
                                            $iuv = trim(str_replace($confEfil['FILLER'], "", substr($value, 115, 35)));
                                            $numavviso = trim(str_replace($confEfil['FILLER'], "", substr($value, 80, 35)));
                                            $toUpdate['PROGKEYTAB'] = $scadenza[0]['PROGKEYTAB'];
                                            $toUpdate['STATO'] = 5;
                                            $toUpdate['IUV'] = $iuv;
                                            $this->aggiornaBgeScadenze($toUpdate);

                                            // Aggiorno BGE_AGID_SCA_EFIL
                                            if ($this->getSimulazione() != true) {
                                                // Array Bind Parameters
                                                $sqlParams = array();
                                                $sqlParams[] = array('name' => 'NUMAVVISO', 'value' => $numavviso, 'type' => PDO::PARAM_STR);
                                                $sqlParams[] = array('name' => 'PROGKEYTAB', 'value' => $scadenza[0]['PROGKEYTAB'], 'type' => PDO::PARAM_INT);
                                                $sqlString = "UPDATE BGE_AGID_SCA_EFIL SET NUMAVVISO=:NUMAVVISO"
                                                        . " WHERE PROGKEYTAB=:PROGKEYTAB";
                                                $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);
                                            }
                                        }
                                    }
                                    //Reperisco informazioni dell'intermediario e del servizio
                                    //$result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                    // Ora verifico se devo inviare l'xml con esito OK o KO... verifico dunque se ci sono SCADENZE legate all'INVIO
                                    // con STATO = 4.
                                    //$filtri = array();
                                    //$filtri['PROGINV'] = $datiInvio['PROGKEYTAB'];
                                    //$countScadenzeNoIuv = $this->getLibDB_BGE()->leggiBgeAgidScadenzeArricchimento($filtri);
                                    //if (!$countScadenzeNoIuv['COUNT']) {
                                    // Metodo per invio XML a MONITOR
                                    //   $this->invioArrayMonitor('Ricevuta di Pubblicazione arricchita', 'OK', $result, $zipFile);
                                    //} else {
                                    // Metodo per invio XML a MONITOR
                                    //  $this->invioArrayMonitor('Ricevuta di Pubblicazione arricchita', 'KO', $result, $zipFile);
                                    //}
                                    if ($this->getSimulazione() != true) {
                                        $log = array(
                                            "LIVELLO" => 5,
                                            "OPERAZIONE" => 13,
                                            "ESITO" => 1,
                                            "KEYOPER" => $progkeytabRicez,
                                        );
                                        $this->scriviLog($log, true);
                                    }
                                } else {
                                    // TODO Errore lettura da sftp    
                                    $errore = true;
                                    $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore: Reperimento Dati Invio Fallito. " . "Servizio:" . $codServizio;
                                }
                            } else {
                                // TODO Errore lettura da sftp    
                                $errore = true;
                                $msgError .= ' File: ' . trim($value['NOME_FILE']) . $sftp->getErrMessage() . ' ' . "Servizio:" . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore:" . $exc->getMessage();
                        }
                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 13,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    //Reperisco informazioni dell'intermediario e del servizio
                    // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                    // Metodo per invio XML a MONITOR
                    //$this->invioArrayMonitor('Ricevuta di Pubblicazione arricchita', 'OK', $result, $zipFile);
                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 13,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
                unlink(itaLib::getUploadPath() . "/" . trim($value['NOME_FILE']));
            }
        } else {
            // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
            // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
            // Metodo per invio XML a MONITOR
            //$this->invioArrayMonitor('Ricevuta di Pubblicazione arricchita', 'OK', $result, $zipFile);
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 13,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    //Gestione ricevuta Cancellazione
    public function customRicevutaCancellazione() {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil();
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $key => $value) {
                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }

                $codServizio = str_pad($value['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathCancellazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTRIC'], $codServizio);

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                // Select per leggere dagli invii i file che dovrï¿½ leggere dall'sFTP
                $list = $this->nomeFileDaElaborare(2, 2, $value['CODSERVIZIO']);

                // leggo la lista di file
                if ($list) {
                    // scorro i filename
                    foreach ($list as $value) {
                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            if ($sftp->downloadFile($pathCancellazione . self::SUFFISSO_CANCELLAZIONE . trim($value['NOME_FILE']) . ".xml")) {
                                $xml = $sftp->getResult();

                                $arrayXml = $this->xmlToArray($xml);

                                //reperimento dati invio
                                $datiInvio = $this->reperisciDatiInvio(trim($value['NOME_FILE']));

                                if ($datiInvio) {
                                    // Salvataggio record su BGE_AGID_RICEZ
                                    $ricezione = array(
                                        "TIPO" => 14,
                                        "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                                        "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                                        "DATARIC" => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        "IDINV" => $datiInvio['PROGKEYTAB'],
                                    );
                                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                    // Salvataggio record su BGE_AGID_ALLEGATI
                                    $pathZip = $this->creaZip(trim($value['NOME_FILE']) . '.xml', $xml);

                                    // Costruisco array per salvataggio allegato
                                    $allegati = array(
                                        'TIPO' => 14,
                                        'IDINVRIC' => $progkeytabRicez,
                                        'DATA' => date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode'])),
                                        'NOME_FILE' => trim($value['NOME_FILE']),
                                    );

                                    if (intval($configGestBin['CONFIG']) === 0) {
                                        //INSERT su BGE_AGID_ALLEGATI
                                        $allegati['ZIPFILE'] = file_get_contents($pathZip);
                                        $this->insertBgeAgidAllegati($allegati);
                                    } else {
                                        $allegati['ZIPFILE'] = 'ffff';
                                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                                    }

                                    unlink($pathZip);

                                    if ($arrayXml['NumeroPosizioniCancellate'][0]['@textNode'] > 0) {

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 8);

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        //   $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        //  if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //    $this->invioArrayMonitor('Ricevuta di Cancellazione', 'OK', $result, $zipFile);
                                        //  }
                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 5;
                                        $esitoLog = 1;
                                    } else if (!$arrayXml['NumeroPosizioniCancellate'][0]['@textNode']) {

                                        //Leggo gli errori dall'xml, trasformo in Json e salvo sul campo NOTEERRORE
                                        $errori = $this->xmlErrori($arrayXml, 'NumeroPosizioniCancellate', 'NumeroPosizioniNonCancellate');
                                        $noteErrore = json_encode($errori);
                                        $errore = strip_tags($noteErrore);

                                        // UPDATE INVIO
                                        $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 9, $noteErrore);

                                        //Valorizzo alcuni campi del LOG
                                        $livelloLog = 3;
                                        $esitoLog = 3;

                                        //Reperisco informazioni dell'intermediario e del servizio
                                        //    $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                                        //    if ($result) {
                                        // Metodo per invio XML a MONITOR
                                        //       $this->invioArrayMonitor('Ricevuta di Cancellazione', 'KO', $result, $zipFile);
                                        //  }
                                    }

                                    // Gestione Scarti
                                    $this->gestioneScarti($arrayXml, $confEfil['FILLER']);

                                    //Tutti i record scartati sono stati aggiornati al punto precedente, cambiando lo stato a 3 (Sospeso).
                                    //Le restanti scadenze saranno aggiornate a "cancellate" e/o "sostituite".
                                    //UPDATE su BGE_AGID_SCADENZE 
                                    $data = date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
                                    $ora = date('H:i:s', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
                                    if ($this->getSimulazione() != true) {
//                                        $sqlParams = array();
//                                        $sqlParams[] = array('name' => 'PROGINV', 'value' => $datiInvio['PROGKEYTAB'], 'type' => PDO::PARAM_INT);
//                                        // Aggiorno scadenze con stato "Cancellata"
//                                        $sqlString = "DELETE FROM BGE_AGID_SCADENZE"
//                                                . " WHERE PROGINV=:PROGINV";
//                                        $this->getCITYWARE_DB()->query($sqlString, $sqlParams);
//                                        foreach ($arrayXml['SCARTI'][0]['SCARTO'] as $keyXml => $value) {
//                                            $codRiferimento = str_replace($confEfil['FILLER'], " ", $value['CodiceRiferimentoCreditore'][0]['@textNode']);
//                                            $this->scriviStoricoEfil($scadenza);
//                                        }
                                        // Array Bind Parameters
                                        $sqlParams = array();
                                        $sqlParams[] = array('name' => 'DATACANC', 'value' => $data, 'type' => PDO::PARAM_STR);
                                        $sqlParams[] = array('name' => 'TIMECANC', 'value' => $ora, 'type' => PDO::PARAM_STR);
                                        $sqlParams[] = array('name' => 'PROGINV', 'value' => $datiInvio['PROGKEYTAB'], 'type' => PDO::PARAM_INT);

                                        // Aggiorno scadenze con stato "Cancellata"
                                        $sqlString = "UPDATE BGE_AGID_SCADENZE SET STATO=7, DATACANC=:DATACANC,TIMECANC=:TIMECANC"
                                                . " WHERE PROGINV=:PROGINV AND STATO=6";
                                        $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);

                                        // Aggiorno scadenze con stato "Sostituita"
                                        $sqlString = "UPDATE BGE_AGID_SCADENZE SET STATO=9, DATASOST=:DATACANC,TIMESOST=:TIMECANC"
                                                . " WHERE PROGINV=:PROGINV AND STATO=8";
                                        $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);

                                        // $operazione = 12;
                                        if ($this->getSimulazione() != true) {
                                            $log = array(
                                                "LIVELLO" => $livelloLog,
                                                "OPERAZIONE" => 14,
                                                "ESITO" => $esitoLog,
                                                "KEYOPER" => $progkeytabRicez,
                                            );
                                            $this->scriviLog($log, true);
                                        }

                                        $filtri = array();
                                        $filtri = array(
                                            "STATO" => 7,
                                            "PROGINV" => $datiInvio['PROGKEYTAB']
                                        );
                                        $scadDaCancellare = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);
                                        if ($scadDaCancellare) {
                                            // Aggiorno scadenze scartate su tabella BGE_AGID_STOSCADE
                                            $this->deleteScadenzeCancellate($scadDaCancellare);
                                        }
                                    }
                                } else {
                                    // TODO Errore lettura da sftp    
                                    $errore = true;
                                    $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore: Reperimento Dati Invio Fallito. Servizio " . $codServizio;
                                }
                            } else {
                                // TODO Errore lettura da sftp    
                                $errore = true;
                                $msgError .= ' File: ' . trim($value['NOME_FILE']) . $sftp->getErrMessage() . ' ' . "Servizio:" . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= ' File: ' . trim($value['NOME_FILE']) . " Errore:" . $exc->getMessage();
                        }
                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 14,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    //Reperisco informazioni dell'intermediario e del servizio
                    // $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
                    // Metodo per invio XML a MONITOR
                    //$this->invioArrayMonitor('Ricevuta di Cancellazione', 'OK', $result, $zipFile);

                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 14,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
            }
        } else {
            // Se non trovo nessun servizio attivo per l'intermediario, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
//            $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
            // Metodo per invio XML a MONITOR
//            $this->invioArrayMonitor('Ricevuta di Cancellazione', 'OK', $result, $zipFile);

            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 14,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    private function deleteScadenzeCancellate($scadenze, $transaction = false) {
        if (!$scadenze[0]) {
            $scadenzeIns[] = $scadenze;
        } else {
            $scadenzeIns = $scadenze;
        }
        foreach ($scadenzeIns as $scadenza) {
            $this->scriviStoricoEfil($scadenza);
            $this->deleteRecord($scadenza, 'BGE_AGID_SCADENZE', $transaction);

            $sca_Efil = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
            $this->deleteRecord($sca_Efil, 'BGE_AGID_SCA_EFIL', $transaction);
        }
    }

    public function customRendicontazione($params = array()) {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil(array());
        // Select per capire se ho gia trattato altre rendicontazioni (se ne trovo qualcuna, le andrï¿½ a togliere dalla dir list
        // della cartella 'Rendicontazioni' dell'sFTP)
        // La select mi ritorna le rendicontazioni GIA' elaborate.
        $listElaborate = $this->getLibDB_BGE()->leggiBgeAgidAllegatiRend(array("TIPO" => 15));
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppaServizio(array());
        if ($serviziAttiviEnte) {
            foreach ($serviziAttiviEnte as $keyServizi => $value) {
                // Path certificato (per riferimento)
                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                if (intval($configGestBin['CONFIG']) === 0) {
                    // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                    $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
                } else {
                    $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
                }

                $codServizio = str_pad($serviziAttiviEnte[$keyServizi]['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
                // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
                $pathRendicontazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTREND'], $codServizio);

                //Settaggio paramentri sFTP
                $this->settaParametriSftp($sftp, $confEfil, $certPath);

                // Verifico se ci sono file nella cartella 'Rendicontazioni' dell'ambiente sFTP
                $esitoListFile = $sftp->listOfFiles($pathRendicontazione);
                if ($esitoListFile) {
                    $listOfFiles = $sftp->getResult();

                    // Il list della dir "Rendicontazioni" mi restituisce i file trovati nella chiave dell'array.
                    // Mi faccio restituire tutte le chiavi dell'array cosï¿½ da avere i nome_file presenti
                    //$listOfFiles = array_keys($listOfFiles);
                    // pulisco l'array da tutto ciï¿½ che non contiene "REND." perchï¿½ quando listo la dir, mi torna anche della "robaccia"
                    foreach ($listOfFiles as $key => $value) {
                        if (!strstr($value, "REND.")) {
                            // elimino l'elemento dall'array
                            unset($listOfFiles[$key]);
                        }
                    }

                    // Vado ad eliminare dall'array di list dir dell'sFTP, tutte le ricevute che mi ha restituito la select precedente.
                    // Essendo sicuro che il risultato della query indica le RENDICONTAZIONI giï¿½ elaborate,
                    // confronto il nome e se lo trovo lo elimino.
                    if ($listElaborate) {
                        foreach ($listOfFiles as $key => $list) {
                            foreach ($listElaborate as $elaborato) {
                                if (strstr($list, "REND.") === trim($elaborato['NOME_FILE'])) {
                                    // elimino l'elemento dall'array
                                    unset($listOfFiles[$key]);
                                }
                            }
                        }
                    }
                } else {
                    $listOfFiles = array();
                }

                // Ho RENDICONTAZIONI non ancora elaborate.
                if ($listOfFiles) {
                    // scorro i filename
                    foreach ($listOfFiles as $value) {
                        $errore = false;
                        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                        try {
                            // li scarico uno alla volta
                            $nomeFile = strstr($value, "REND.");
                            if ($sftp->downloadFile($pathRendicontazione . $nomeFile)) {
                                $txtRend = $sftp->getResult();

                                // Salvataggio record su BGE_AGID_ALLEGATI
                                $pathZip = $this->creaZip($nomeFile, $txtRend);

                                // Salvataggio record su BGE_AGID_RICEZ
                                // faccio explode, per reperire la data di creazione dal $nomeFile
                                $expNomeFile = explode(".", $nomeFile);
                                // sono sicuro che la data del file si trova alla posizione 3 dell'array
                                $dataFile = $expNomeFile[3];
                                $ricezione = array(
                                    "TIPO" => 15,
                                    "INTERMEDIARIO" => itaPagoPa::EFILL_TYPE,
                                    "CODSERVIZIO" => $serviziAttiviEnte[$keyServizi]['CODSERVIZIO'],
                                    "DATARIC" => $dataFile,
                                    "IDINV" => null,
                                );
                                $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                                // Costruisco array per salvataggio allegato
                                $allegati = array(
                                    'TIPO' => 15,
                                    'IDINVRIC' => $progkeytabRicez,
                                    'DATA' => $dataFile,
                                    'NOME_FILE' => $nomeFile,
                                );

                                if (intval($configGestBin['CONFIG']) === 0) {
                                    //INSERT su BGE_AGID_ALLEGATI
                                    $allegati['ZIPFILE'] = file_get_contents($pathZip);
                                    $this->insertBgeAgidAllegati($allegati);
                                } else {
                                    $allegati['ZIPFILE'] = 'ffff';
                                    $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                                    $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                                    $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                                }

                                // elimino file da disco
                                unlink($pathZip);

                                // Facendo l'explode di "\n", mi rimane un elemento vuoto in fondo nell'array... lo vado ad eliminare.
                                $arrayTxt = explode("\n", $txtRend);
                                foreach ($arrayTxt as $key => $value) {
                                    if ($value === '') {
                                        unset($arrayTxt[$key]);
                                    }
                                }

                                // scorro .txt per aggiornare le scadenze
                                foreach ($arrayTxt as $key => $value) {
                                    $IUV = trim(substr($value, 115, 35));
                                    $risco = array();
                                    $risco = $this->getLibDB_BGE()->leggiBgeAgidRisco(array("IUV" => $IUV), false);
                                    $codRiferimento = trim(substr($value, 45, 35));
                                    $filtri['CODRIFERIMENTO'] = $codRiferimento;
                                    $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);
                                    //   if (!$risco) {
                                    // Prendo dati da file (.txt) di rendicontazione a posizioni fisse
                                    $toUpdate = array();
                                    $filtri = array();
                                    $risco_efil = array();
                                    // Inserisco Riscossione BGE_AGID_RISCO
                                    $progintric = trim(substr($value, 0, 10));
                                    $imppagato = trim(substr($value, 376, 12)) / 100;
                                    if (strlen(trim(substr($value, 360, 8))) === 8) {
                                        $datapag = trim(substr($value, 360, 8));
                                    } else {
                                        $datapag = null;
                                    }
                                    $tipopers = trim(substr($value, 528, 2));
                                    $tipopers === 'CF' ? $tipopers = 'F' : $tipopers = 'G';

                                    // Inserimento su BGE_AGID_RISCO
                                    $riscossione = array(
                                        "IDSCADENZA" => $scadenza[0]['PROGKEYTAB'],
                                        "PROGRIC" => $progkeytabRicez,
                                        "PROGINTRIC" => $progintric,
                                        "IUV" => $IUV,
                                        "PROVPAGAM" => 2,
                                        "IMPPAGATO" => $imppagato,
                                        "DATAPAG" => $datapag,
                                        "TIPOPERS" => $tipopers
                                    );
                                    if ($risco) {
                                        $riscossione['PROGKEYTAB'] = $risco['PROGKEYTAB'];
                                        $riscossione['STATO_REND'] = $this->calcolaStatoRend($risco['STATO_REND'], 'REND');
                                        $this->aggiornaBgeAgidRisco($riscossione);
                                        $progkeytabRisco = $risco['PROGKEYTAB'];
                                        //$aggiornaStatoScad = false;
                                    } else {
                                        $riscossione['STATO_REND'] = 2;
                                        $progkeytabRisco = $this->insertBgeAgidRisco($riscossione);
                                        //$aggiornaStatoScad = true;
                                    }

                                    if ($scadenza) {
                                        $toUpdate['PROGKEYTAB'] = $scadenza[0]['PROGKEYTAB'];
                                        //if ($aggiornaStatoScad) {
                                        $toUpdate['STATO'] = 10;
                                        //}
                                        if (strlen(trim(substr($value, 360, 8))) === 8) {
                                            $toUpdate['DATAPAGAM'] = trim(substr($value, 360, 8));
                                        }
                                        $this->aggiornaBgeScadenze($toUpdate);
                                    }
                                    // Inserisco Riscossione EFil BGE_AGID_RISCO_EFIL
                                    $risco_efil['PROGKEYTAB'] = $progkeytabRisco;
                                    $risco_efil['NUMAVVISO'] = trim(substr($value, 80, 35));      // Numero avviso
                                    if (strlen(trim(substr($value, 368, 8))) === 8 && trim(substr($value, 368, 8)) > 0) {
                                        $risco_efil['DATAREG'] = trim(substr($value, 368, 8));        // Data Registrazione
                                    } else {
                                        $risco_efil['DATAREG'] = null;
                                    }
                                    $risco_efil['CAUSALE'] = trim(substr($value, 388, 140));      // Causale
                                    $risco_efil['CFPIDEBITORE'] = trim(substr($value, 530, 35));    // CF/PI Debitore
                                    $risco_efil['ANADEBITORE'] = trim(substr($value, 565, 50));   // Anagrafica Debitore
                                    $risco_efil['COMMPSP'] = trim(substr($value, 615, 12));       // Commissione PSP
                                    $risco_efilPresente = $this->getLibDB_BGE()->leggiBgeAgidRiscoEfil(array("PROGKEYTAB" => $progkeytabRisco), false);
                                    if ($risco_efilPresente) {
                                        // modifica 09/05/2019 dopo cambio tracciato rendicontazioni da parte di Efil
                                        if (!$risco_efilPresente['DATAVERS']) {
                                            $risco_efil['DATAVERS'] = $datapag;
                                        } else {
                                            $risco_efil['DATAVERS'] = $risco_efilPresente['DATAVERS'];
                                        }
                                        if (!$risco_efilPresente['DATAREGO']) {
                                            $risco_efil['DATAREGO'] = $datapag;
                                        } else {
                                            $risco_efil['DATAREGO'] = $risco_efilPresente['DATAREGO'];
                                        }
                                        if ($risco_efilPresente['DATAREG']) {
                                            $risco_efil['DATAREG'] = $risco_efilPresente['DATAREG'];       // Data Esito Versamento
                                        }
                                        $risco_efil['ID'] = $risco_efilPresente['ID'];
                                        $this->aggiornaBgeAgidRiscoEfil($risco_efil);
                                    } else {
                                        // $risco_efil['DATAREG'] = $datapag;
                                        $risco_efil['DATAVERS'] = $datapag;
                                        $risco_efil['DATAREGO'] = $datapag;
                                        $this->inserisciBgeAgidRiscoEfil($risco_efil);
                                    }
                                    // }
                                }
                                if ($this->getSimulazione() != true) {
                                    $log = array(
                                        "LIVELLO" => 5,
                                        "OPERAZIONE" => 15,
                                        "ESITO" => 1,
                                        "KEYOPER" => $progkeytabRicez,
                                    );
                                    $this->scriviLog($log, true);
                                }
                            } else {
                                // TODO Errore lettura da sftp   
                                $errore = true;
                                $risultato = false;
                                $msgError .= "Errore: Download File " . $nomeFile . " Servizio " . $codServizio;
                            }
                        } catch (Exception $exc) {
                            // TODO scrivere log con esito 'errore scrittura' ???
                            $errore = true;
                            $msgError .= "Errore:" . $exc->getMessage() . " Servizio " . $codServizio;
                        }
                        // TODO Vedi post-it sul rollback... 
                        if ($errore) {
                            cwbDBRequest::getInstance()->rollBackManualTransaction();
                            unlink($nomeAllegato);
                            if ($this->getSimulazione() != true) {
                                $log = array(
                                    "LIVELLO" => 3,
                                    "OPERAZIONE" => 15,
                                    "ESITO" => 3,
                                    "KEYOPER" => 0,
                                );
                                $this->scriviLog($log);
                            }
                            $risultato = false;
                        } else {
                            cwbDBRequest::getInstance()->commitManualTransaction();
                        }
                    }
                } else {
                    // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 5,
                            "OPERAZIONE" => 15,
                            "ESITO" => 2,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                }
                unlink($certPath);
            }
        } else {
            // Se non trovo nessun servizio attivo per l'intermediario, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            //Reperisco informazioni dell'intermediario e del servizio
//            $result = $this->trovaIntermediarioeServizio(itaPagoPa::EFILL_TYPE, $datiInvio['CODSERVIZIO']);
//            if ($result) {
//                //Valorizzo array da trasformare in XML e da passare poi al MONITOR
//                $arrayMonitor = $this->buildArrayMonitor('Rendicontazione Scadenze', $result['INTERMEDIARIO'], $result['TIPORIFCRED'], 'OK', $nomeFile);
//                cwbBgeMonitorHelper::generaInviaXmlMonitor($arrayMonitor, $nomeFile, $zipFile);
//            }

            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 15,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    public function customRiversamenti($params = array()) {
        $msgError = '';
        $risultato = true;
        $confEfil = $this->leggiConfEfil(array());
        // Select per capire se ho giï¿½ trattato altre rendicontazioni (se ne trovo qualcuna, le andrï¿½ a togliere dalla dir list
        // della cartella 'Rendicontazioni' dell'sFTP)
        // La select mi ritorna le rendicontazioni GIA' elaborate.
        $listElaborate = $this->getLibDB_BGE()->leggiBgeAgidAllegatiRend(array("TIPO" => 15));
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        //  $serviziAttiviEnte = $this->getLibDB_BTA()->leggiBtaServrendppa(array()); // non si trova piu la cartella sotto i servizi
        //  if ($serviziAttiviEnte) {
        //      foreach ($serviziAttiviEnte as $keyServizi => $value) {
        // Path certificato (per riferimento)
        $devLib = new devLib();
        $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
        if (intval($configGestBin['CONFIG']) === 0) {
            // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
            $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
        } else {
            $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
        }

        //   $codServizio = str_pad($serviziAttiviEnte[$keyServizi]['CODSERVIZIO'], 7, "0", STR_PAD_LEFT);
        // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
        //     $pathRendicontazione = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTREND'], $codServizio);
        $pathRendicontazione = '\Riversamenti';
        //Settaggio paramentri sFTP
        $this->settaParametriSftp($sftp, $confEfil, $certPath);

        // Verifico se ci sono file nella cartella 'Riversamenti' dell'ambiente sFTP
        $esitoListFile = $sftp->listOfFiles($pathRendicontazione);
        if ($esitoListFile) {
            $listOfFiles = $sftp->getResult();

            // Il list della dir "Rendicontazioni" mi restituisce i file trovati nella chiave dell'array.
            // Mi faccio restituire tutte le chiavi dell'array cosï¿½ da avere i nome_file presenti
            //$listOfFiles = array_keys($listOfFiles);
            // pulisco l'array da tutto ciï¿½ che non contiene "REND." perchï¿½ quando listo la dir, mi torna anche della "robaccia"
            foreach ($listOfFiles as $key => $value) {
                if (!strstr($value, ".xml")) {
                    // elimino l'elemento dall'array
                    unset($listOfFiles[$key]);
                }
            }

            // Vado ad eliminare dall'array di list dir dell'sFTP, tutte le ricevute che mi ha restituito la select precedente.
            // Essendo sicuro che il risultato della query indica le RENDICONTAZIONI giï¿½ elaborate,
            // confronto il nome e se lo trovo lo elimino.
            if ($listElaborate) {
                foreach ($listOfFiles as $key => $list) {
                    foreach ($listElaborate as $elaborato) {
                        if ($list === trim($elaborato['NOME_FILE'])) {
                            // elimino l'elemento dall'array
                            unset($listOfFiles[$key]);
                        }
                    }
                }
            }
        } else {
            $listOfFiles = array();
        }

        // Ho RENDICONTAZIONI non ancora elaborate.
        if ($listOfFiles) {
            // scorro i filename
            foreach ($listOfFiles as $value) {
                $errore = false;
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                try {
                    // li scarico uno alla volta
                    $nomeFile = $value;
                    if ($sftp->downloadFile($pathRendicontazione . '/' . $nomeFile)) {
                        $xmlRend = $sftp->getResult();
                        $xmlRend = str_replace("utf-16", "utf-8", $xmlRend);

                        // Salvataggio record su BGE_AGID_ALLEGATI
                        $nomeFileNoExt = str_replace(".xml", "", $nomeFile);
                        $pathZip = $this->creaZip($nomeFileNoExt, $xmlRend);

                        $arrayXml = $this->xmlToArray($xmlRend);

                        list($dataFile, $oraFile) = explode("T", $arrayXml['dataOraFlusso'][0]['@textNode']);
                        $ricezione = array(
                            "TIPO" => 15,
                            "INTERMEDIARIO" => itaPagoPa::EFILL_TYPE,
                            "DATARIC" => $dataFile,
                            "IDINV" => null,
                        );
                        $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                        // Costruisco array per salvataggio allegato
                        $allegati = array(
                            'TIPO' => 15,
                            'IDINVRIC' => $progkeytabRicez,
                            'DATA' => $dataFile,
                            'NOME_FILE' => $nomeFile,
                        );

                        if (intval($configGestBin['CONFIG']) === 0) {
                            //INSERT su BGE_AGID_ALLEGATI
                            $allegati['ZIPFILE'] = file_get_contents($pathZip);
                            $this->insertBgeAgidAllegati($allegati);
                        } else {
                            $allegati['ZIPFILE'] = 'ffff';
                            $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                            $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                            $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                        }

                        // elimino file da disco
                        unlink($pathZip);

                        foreach ($arrayXml['datiSingoliPagamenti'] as $key => $pagamento) {
                            $IUV = $pagamento['identificativoUnivocoVersamento'][0]['@textNode'];
                            $risco = array();
                            $toUpdate = array();
                            $filtri = array();
                            $risco_efil = array();
                            $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IUV' => $IUV), false);


                            // Aggiorno BGE_AGID_SCADENZE
                            //   $codRiferimento = trim(substr($value, 45, 35));
                            // Inserisco Riscossione BGE_AGID_RISCO
                            //     $progintric = trim(substr($value, 0, 10));
                            $imppagato = $pagamento['singoloImportoPagato'][0]['@textNode'];
                            //$tipopers = $arrayXml['istitutoRicevente'][0]['identificativoUnivocoRicevente'][0]['tipoIdentificativoUnivoco'][0]['@textNode'];
                            // Inserimento su BGE_AGID_RISCO
                            $datapag = $pagamento['dataEsitoSingoloPagamento'][0]['@textNode'];
                            $riscossione = array(
                                "IDSCADENZA" => $scadenza['PROGKEYTAB'],
                                "PROGRIC" => $progkeytabRicez,
                                "PROGINTRIC" => $progintric,
                                "IUV" => $IUV,
                                "PROVPAGAM" => 2,
                                "IMPPAGATO" => $imppagato,
                            );
                            $risco = $this->getLibDB_BGE()->leggiBgeAgidRisco(array("IUV" => $IUV), false);
                            if ($risco) {
                                if (!$risco['DATAPAG']) {
                                    $riscossione['DATAPAG'] = $datapag;
                                } else {
                                    $riscossione['DATAPAG'] = $risco['DATAPAG'];
                                }
                                $riscossione['PROGKEYTAB'] = $risco['PROGKEYTAB'];
                                $progkeytabRisco = $risco['PROGKEYTAB'];
                                $riscossione['STATO_REND'] = $this->calcolaStatoRend($risco['STATO_REND'], 'RIVERS');
                                $this->aggiornaBgeAgidRisco($riscossione);
                                // $aggiornaStatoScad = false;
                            } else {
                                $riscossione['DATAPAG'] = $datapag;
                                $riscossione['STATO_REND'] = 3;
                                $progkeytabRisco = $this->insertBgeAgidRisco($riscossione);
                                // $aggiornaStatoScad = true;
                            }
                            if ($scadenza) {
                                $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                                // if ($aggiornaStatoScad) {
                                $toUpdate['STATO'] = 10;
                                if (!$scadenza['DATAPAGAM']) {
                                    $toUpdate['DATAPAGAM'] = $datapag;
                                } else {
                                    $toUpdate['DATAPAGAM'] = $scadenza['DATAPAGAM'];
                                }
                                $this->aggiornaBgeScadenze($toUpdate);
                                //   }
                            }

                            // Inserisco Riscossione EFil BGE_AGID_RISCO_EFIL
                            $risco_efil['PROGKEYTAB'] = $progkeytabRisco;
                            //      $risco_efil['NUMAVVISO'] = trim(substr($value, 80, 35));      // Numero avviso
//                                if (strlen(trim(substr($value, 368, 8))) === 8) {
//                                    $risco_efil['DATAREG'] = trim(substr($value, 368, 8));        // Data Registrazione
//                                }
                            //     $risco_efil['CAUSALE'] = trim(substr($value, 388, 140));      // Causale
                            //   $risco_efil['CFPIDEBITORE'] = trim(substr($value, 530, 35));    // CF/PI Debitore
                            //     $risco_efil['ANADEBITORE'] = trim(substr($value, 565, 50));   // Anagrafica Debitore
                            //    $risco_efil['COMMPSP'] = trim(substr($value, 615, 12));       // Commissione PSP
                            //   $risco_efil['CANALEPAG'] = $arrayXml['denominazioneMittente'][0]['@textNode'];    // Canale Pagamento
                            //   $risco_efil['IDCANALEPAG'] = $arrayXml['codiceIdentificativoUnivoco'][0]['@textNode'];   // ID Canale Pagamento
                            //   $risco_efil['IDFLUSSO'] = $arrayXml['identificativoFlusso'][0]['@textNode'];      // Identificativo Flusso
                            // controllo validitï¿½ data.... altrimenti non le salvo e continuo
                            $risco_efil['DATAVERS'] = $pagamento['dataEsitoSingoloPagamento'][0]['@textNode'];       // Data Esito Versamento
                            $risco_efil['DATAREGO'] = $arrayXml['dataRegolamento'][0]['@textNode'];       // Data Regolamento
                            $risco_efilPresente = $this->getLibDB_BGE()->leggiBgeAgidRiscoEfil(array("PROGKEYTAB" => $progkeytabRisco), false);
                            if ($risco_efilPresente) {
                                $risco_efil['ID'] = $risco_efilPresente['ID'];
                                if (!$risco_efilPresente['DATAREG']) {
                                    $risco_efil['DATAREG'] = $pagamento['dataEsitoSingoloPagamento'][0]['@textNode'];       // Data Registrazione
                                } else {
                                    $risco_efil['DATAREG'] = $risco_efilPresente['DATAREG'];
                                }
                                $this->aggiornaBgeAgidRiscoEfil($risco_efil);
                            } else {
                                $this->inserisciBgeAgidRiscoEfil($risco_efil);
                            }
                        }
                        if ($this->getSimulazione() != true) {
                            $log = array(
                                "LIVELLO" => 5,
                                "OPERAZIONE" => 15,
                                "ESITO" => 1,
                                "KEYOPER" => $progkeytabRicez,
                            );
                            $this->scriviLog($log, true);
                        }
                    } else {
                        // TODO Errore lettura da sftp   
                        $errore = true;
                        $risultato = false;
                        $msgError .= "Errore: Download File " . $nomeFile . " Servizio " . $codServizio;
                    }
                } catch (Exception $exc) {
                    // TODO scrivere log con esito 'errore scrittura' ???
                    $errore = true;
                    $msgError .= "Errore:" . $exc->getMessage() . " Servizio " . $codServizio;
                }
                // TODO Vedi post-it sul rollback... 
                if ($errore) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    unlink($nomeAllegato);
                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 3,
                            "OPERAZIONE" => 15,
                            "ESITO" => 3,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                    $risultato = false;
                } else {
                    cwbDBRequest::getInstance()->commitManualTransaction();
                }
            }
        } else {
            // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 15,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        unlink($certPath);

        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    public function customRiconciliazione() {
        $risultato = true;
        $scadenzeRiconciliabili = $this->getLibDB_BWE()->leggiBwePendenPerRiconciliazione(array());
        if ($scadenzeRiconciliabili) {
            foreach ($scadenzeRiconciliabili as $value) {
                $errore = false;
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                try {
                    // Leggo BGE_AGID_SCADENZE con i dati della BWE_PENDEN
                    $filtri = array();
                    $filtri['CODTIPSCAD'] = $value['CODTIPSCAD'];
                    $filtri['SUBTIPSCAD'] = $value['SUBTIPSCAD'];
                    $filtri['PROGCITYSC'] = $value['PROGCITYSC'];
                    $filtri['ANNORIF'] = $value['ANNORIF'];
                    $filtri['NUMRATA'] = $value['NUMRATA'];
                    $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);
                    $value['IMPPAGTOT'] = cwbLibOmnis::toOmnisDecimal($value['IMPPAGATO']);
                    if ($value['DATARISCO']) {
                        $value['DATAPAG'] = date('d-m-Y', strtotime($value['DATARISCO']));
                    }
                    $value['MODPAGAM'] = 6;
                    $value['STATO'] = 4;
                    $value['FPAGONLINE'] = 0;
                    if ($value['DATAVALUTA']) {
                        $value['DATAVALUTA'] = date('d-m-Y', strtotime(trim(substr($value['IDFLUSSO'], 0, 10))));
                    }
                    if ($value['DATACREAZ']) {
                        $value['DATACREAZ'] = date('d-m-Y', strtotime(trim($value['DATACREAZ'])));
                    }
                    if ($value['DATAULTMOD']) {
                        $value['DATAULTMOD'] = date('d-m-Y', strtotime(trim($value['DATAULTMOD'])));
                    }
                    if ($value['DATASCADE']) {
                        $value['DATASCADE'] = date('d-m-Y', strtotime(trim($value['DATASCADE'])));
                    }
                    if ($value['DATAREG']) {
                        $value['DATAREG'] = date('d-m-Y', strtotime(trim($value['DATAREG'])));
                    } else {
                        $value['DATAREG'] = date('d-m-Y', strtotime(trim($value['DATARISCO'])));
                    }
                    if ($value['DATAVERS']) {
                        $value['DATAVERS'] = date('d-m-Y', strtotime(trim($value['DATAVERS'])));
                    }
                    if ($value['DATAREGO']) {
                        $value['DATAREGO'] = date('d-m-Y', strtotime(trim($value['DATAREGO'])));
                    }
                    $value['RIFOPER'] = 'PAGOPA';
                    $value['FLAGINSER'] = 'D';
                    if ($scadenza[0]['CODTIPSCAD'] == 34) {
                        include_once ITA_BASE_PATH . '/apps/CityFee/cwsBorsLib.class.php';
                        $cwsBorsLib = new cwsBorsLib();
                        $esito = $cwsBorsLib->confermaPagamento($scadenza[0]['IUV']);
                    } elseif ($scadenza[0]['CODTIPSCAD'] == 91) {
                        //todo aggiungere chiamata a CIE
                    } else {
                        $esito = $this->registraPagamentoCityware($value);
                    }

                    $toUpdate = array();
                    $toUpdate['PROGKEYTAB'] = $scadenza[0]['PROGKEYTAB'];

                    if ($esito['RESULT']['EXITCODE'] === 'S' || ($esito == true && $scadenza[0]['CODTIPSCAD'] == 34)) {
                        // OK
                        // Aggiorno la scadenza
                        $toUpdate['STATO'] = 12;
                        $toUpdate['DATARICON'] = date('Ymd');
                        $toUpdate['TIMERICON'] = date('H:i:s');
                    } elseif ($esito['RESULT']['EXITCODE'] === 'N') {
                        // KO
                        // Aggiorno la scadenza
                        $toUpdate['STATO'] = 11;
                        $toUpdate['NOTENONRICON'] = trim($esito['RESULT']['MESSAGE']);
                    } else {
                        $toUpdate['STATO'] = 11;
                        $toUpdate['NOTENONRICON'] = 'Omnis server in timeout!';
                    }
                    $this->aggiornaBgeScadenze($toUpdate);
                } catch (Exception $ex) {
                    $errore = true;
                    $msgError = "Errore: " . $ex->getMessage();
                }
                if ($errore) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    $log = array(
                        "LIVELLO" => 3,
                        "OPERAZIONE" => 22,
                        "ESITO" => 3,
                        "KEYOPER" => 0,
                        "NOTE" => $msgError
                    );
                    $this->scriviLog($log);
                    $risultato = false;
                } else {
                    cwbDBRequest::getInstance()->commitManualTransaction();
                    $log = array(
                        "LIVELLO" => 5,
                        "OPERAZIONE" => 22,
                        "ESITO" => 1,
                        "KEYOPER" => 0,
                    );
                    $this->scriviLog($log);
                }
            }
        } else {
            $log = array(
                "LIVELLO" => 5,
                "OPERAZIONE" => 22,
                "ESITO" => 2,
                "KEYOPER" => 0,
            );
            $this->scriviLog($log);
        }
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    /**
     * Genera il nome del file per invio fornitura pubblicazione o cancellazione
     * @param $numPosizioni numero di posizioni debitorie da inviare
     * @param $tipo nome da creare in base alla fornitura da inviare (pubblicazione,pubblicazione2 o cancellazione)
     * @return Nome file
     */
    public function generaNomeFile(&$numPosizioni, $tipo = 'Pubblicazione', $codServizio) {
        $confEfil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
        $codEnte = str_pad(trim($confEfil['CODENTECRED']), 7, "0", STR_PAD_LEFT);
        $codServizio = str_pad(trim($codServizio), 7, "0", STR_PAD_LEFT);
        $dataCreazione = date('Ymd');

        switch ($tipo) {
            case 'Pubblicazione2':
            case 'Pubblicazione':
                $progressivoFlusso = $this->generaProgressivoFlusso($dataCreazione, $codServizio, 1);
                $progressivoFlusso = str_pad($progressivoFlusso, 2, "0", STR_PAD_LEFT);

                $ext = 'N001';
                if ($tipo == 'Pubblicazione2') {
                    $ext = 'N002';
                }

                //Genero nome file
                $nomeFile = $codEnte . "." . $codServizio . "." . $numPosizioni . "." . $dataCreazione . "." .
                        $progressivoFlusso . "." . "INS." . $ext;
                break;

            case 'Cancellazione':
                $progressivoFlusso = $this->generaProgressivoFlusso($dataCreazione, $codServizio, 2);
                $progressivoFlusso = str_pad($progressivoFlusso, 2, "0", STR_PAD_LEFT);

                //Genero nome file
                $nomeFile = $codEnte . "." . $codServizio . "." . $numPosizioni . "." . $dataCreazione . "." .
                        $progressivoFlusso . "." . "CANC.N001";
                break;
        }
        return $nomeFile;
    }

    /**
     *  a partire da un array crea un file txt con posizioni fisse e poi lo zippa. 
     *  txt e zip si devono chiamare entrambi $nomeFile (estensione .N001)
     * 
     * @param array $data L'array con i record da pubblicare
     * @param String $nomeFile il nome del file
     * @return string|boolean il path in cui si trova lo zip oppure false
     */
    public function creaFileZipDaArray($data, $nomeFile, $flusso = 'Pubblicazione') {
        $textFile = '';
        // TODO metodo che controlla campi obbligatori

        foreach ($data as $record) {
            $this->aggiungiFiller($record, $textFile, $flusso);
            $textFile .= "\r\n"; // attenzione! \r\n viene riconosciuto solo mettendo le virgolette, con gli apici non viene riconosciuto (scrive \r\n a stringa invece di andare a capo)
        }

        // crea file txt formattato e poi zippalo con lo stesso nome di prima senza estenzione.zip
        return $this->creaZip($nomeFile, $textFile);
    }

    /**
     * Aggiunge i valori di filler nel txt a posizioni fisse da generare
     * 
     * @param array $record il record con i dati da pubblicare
     * @param String &$textFile la stringa con il txt valorizzata per riferimento
     */
    private function aggiungiFiller($record, &$textFile, $flusso) {
        $confEfil = $this->leggiConfEfil(array());
        switch ($flusso) {
            case 'Pubblicazione':
                $mappingCampi = $this->mappingCampiPubbl;
                break;
            case 'Cancellazione':
                $mappingCampi = $this->mappingCampiCanc;
                break;
        }
        // mi scorro l'array di mapping perchï¿½ ï¿½ ordinato per posizioni fisse
        foreach ($mappingCampi as $key => $mapping) {
            // se la key inizia per filler aggingo solo filler
            if (preg_match('/FILLER/', $key) === 1) {
                $textFile .= str_pad("", $mapping['LUNGHEZZA'], $confEfil['FILLER']);
                continue;
            }

            // prendo dall'array il campo $key
            $campo = $record[$key];
            if (!$campo) {
                if ($mapping['OBBLIGATORIO']) {// se il campo ï¿½ vuoto ed ï¿½ obbligatorio torno errore
                    throwException("Campo Obbligatorio Mancante. Il campo " . $key . " non puï¿½ essere vuoto!");
                } else {// se il campo non c'ï¿½ e non ï¿½ obbligatorio lo metto vuoto
                    if ($mapping['TIPO'] === self::TIPO_STRINGA || $mapping['TIPO'] === self::TIPO_DATA) {
                        $campo = '';
                    } else {
                        $campo = 0;
                    }
                }
            } else {
                $campo = trim($campo);
            }

            if ($mapping['TIPO'] === self::TIPO_INTERO) {
                if ($campo) {
                    // se c'ï¿½ un valore riempo gli spazi vuoti con zero
                    $negativo = '';
                    $lunghezza = $mapping['LUNGHEZZA'];
                    if ($campo < 0) {
                        $negativo = '-';
                        $campo = $campo * -1;
                        $lunghezza--;
                    }
                    $tempTextFile = str_pad($campo, $lunghezza, 0, STR_PAD_LEFT);
                    $textFile .= $negativo . $tempTextFile;
                } else {
                    // se invece ï¿½ vuoto oppure contiene 0 metto il filler da db ($)                    
                    $textFile .= str_pad('', $mapping['LUNGHEZZA'], $confEfil['FILLER'], STR_PAD_LEFT);
                }
            } else if ($mapping['TIPO'] === self::TIPO_STRINGA) {
                //'mozzo' i campi se sono troppo lunghi
                if (strlen($campo) > $mapping['LUNGHEZZA']) {
                    $campo = substr($campo, 0, $mapping['LUNGHEZZA']);
                }

                // metto il filler stabilito da db per gli spazi vuoti rimasti
                $textFile .= str_pad($campo, $mapping['LUNGHEZZA'], $confEfil['FILLER']);
            } else if ($mapping['TIPO'] === self::TIPO_DATA) {
                if ($campo) {
                    $campo = str_replace("/", "-", $campo);
                    $campo = date("Ymd", strtotime($campo));
                }
                // la data se ï¿½ vuota vuole il filelr non gli 0
                $textFile .= str_pad($campo, $mapping['LUNGHEZZA'], $confEfil['FILLER'], STR_PAD_LEFT);
            } else if ($mapping['TIPO'] === self::TIPO_DECIMALE) {
                $negativo = '';
                // arrotondo a 2 cifre
                $campo = round($campo, 2);
                $res = explode(self::SEPARATORE_DECIMALE, $campo); // separo i decimali dal numero   
                $num = $res[0];
                if ($num < 0) {
                    $negativo = '-';
                    $num = $num * -1;
                }
                $dec = $res[1];
                if (!$dec) {
                    $dec = 0; // se non c'ï¿½ decimale metto 0
                }

                // metto il filler stabilito da db per gli spazi vuoti rimasti
                // numero intero senza le ultime 2 cifre che sono decimali
                if ($negativo) {
                    $lunghezza = $mapping['LUNGHEZZA'] - 3;
                } else {
                    $lunghezza = $mapping['LUNGHEZZA'] - 2;
                }
                $tempTextFile = str_pad(trim($num), $lunghezza, 0, STR_PAD_LEFT);
                // decimale, fisso su ultime 2 cifre
                $tempTextFile .= str_pad(trim($dec), 2, 0);
                $textFile .= $negativo . $tempTextFile;
            }
        }
    }

    /**
     * Metodo per preparazione file da pubblicare sul NODO. Genera il nome del file da inviare e il file .zip vero e proprio
     * @param $scadenzePerPubbli array di scadenze da pubblicare
     * @param $nomeFile nome del file da pubblicare
     * @param $file .zip da inviare
     * @param $numPosizioni numero di pubblicazioni da inviare
     * @return se tutto ï¿½ stato generato nel modo corretto true oppure false
     */
    private function customPreparazionePerPubblicazione($progkeytabInvio, $scadenzePerPubbli, &$nomeFile, &$file) {
        try {
            // crea array con record da pubblicare
            $posizioniDebitorie = $this->creaPosizioniDebitoriePubblicazione($scadenzePerPubbli);
            if (!$posizioniDebitorie) {
                return false;
            }
            $posizioniDebitorie = $this->verificaPosizioniDebitorie($posizioniDebitorie, $progkeytabInvio);
            $numPosizioni = count($posizioniDebitorie);
            $tipo = 'Pubblicazione';
            if ($posizioniDebitorie[0]['CODACCERTAMENTO1']) {
                // se c'ï¿½ almeno un accertamento, ï¿½ tipo=Pubblicazione2 (deve mettere .N0002)
                $tipo = 'Pubblicazione2';
            }
            $nomeFile = $this->generaNomeFile($numPosizioni, $tipo, $scadenzePerPubbli[0]['CODSERVIZIO']);
            if (!$nomeFile) {
                return false;
            }
            if (!$posizioniDebitorie) {
                return false;
            } else {
                $file = $this->creaFileZipDaArray($posizioniDebitorie, $nomeFile);
                if (!$file) {
                    return false;
                } else {
                    return true;
                }
            }
            // crea zip con dentro il txt da inviare all'sftp
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Metodo per invio su ambiente sFTP E-Fil
     * @param $nomeFile nome del file da pubblicare
     * @param $file da inviare
     * @param $codServizio per genereare il path per l'invio (l'ambiente ufficiale ï¿½ diviso per servizi attivi)
     * @return true se l'invio ï¿½ andato a buon fine oppure false
     */
    private function customInvioPubblicazione($nomeFile, &$file, $scadenzeDaPubblicare) {
        // invio il file all'sftp
        $confEfil = $this->leggiConfEfil(array());
        try {
            $devLib = new devLib();
            $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
            if (intval($configGestBin['CONFIG']) === 0) {
                // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
            } else {
                $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
            }
            //Settaggio paramentri sFTP
            $this->settaParametriSftp($sftp, $confEfil, $certPath);

            // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
            $pathPubl = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTPUBBL'], $scadenzeDaPubblicare[0]['CODSERVIZIO']);

            $pathPubl .= $nomeFile;
            $esitoInvioSFTP = $sftp->uploadFile($pathPubl, $file);

            unlink($certPath);
            if ($this->getSimulazioneSF() != true || $this->getSimulazione() != true) {
                unlink($file);
            }

            if (!$esitoInvioSFTP) {
                $this->handleError(-1, $sftp->getErrMessage());
            }

            return $esitoInvioSFTP;
        } catch (Exception $exc) {
            $errMessage = $exc->getMessage();
            $this->handleError(-1, $errMessage);
            return false;
        }
    }

    protected function leggiScadenzePerPubblicazioni($progkeytabScadenze = null, $stato = null, $page = null) {
        if ($progkeytabScadenze) {
            $filtri['PROGKEYTAB_SCADENZA_IN'] = $progkeytabScadenze;
        }
        if ($stato) {
            $filtri['STATO'] = $stato;
        }
        if ($page === null) {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniEfil($filtri);
        } else {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniEfilBlocchi($filtri, $page, $this->customPaginatorSize());
        }
        return $scadenzePerPubbli;
    }

    // BWE_PENDEN
    private function leggiScadenzePerCancellazione($codServizio) {
        $filtri['CODSERVIZIO'] = $codServizio;
        $filtri['TIP_INS_IN'] = array(0, 1);
        $scadenzePerCanc = $this->getLibDB_BGE()->leggiBgeAgidScadenzePerCancellazione($filtri);
        return $scadenzePerCanc;
    }

    /**
     * Metodo per preparazione file contenente posizioni da cancellare sul NODO. 
     * Genera il nome del file da inviare e il file .zip vero e proprio
     * @param $scadenzePerCanc array di scadenze da cancellare 
     * @param $nomeFile nome del file contenente scadenze da cancellare
     * @param $file .zip per cancellazione     
     * @return se tutto ï¿½ stato generato nel modo corretto true oppure false
     */
    private function customPreparazionePerCancellazione($scadenzePerCanc, &$nomeFile, &$file, &$numPosizioni, $codServizio) {
        try {
            // crea array con record da pubblicare
            $posizioniDebitorie = $this->creaPosizioniDebitorieCancellazione($scadenzePerCanc);
            if (!$posizioniDebitorie) {
                return false;
            }
            $numPosizioni = count($posizioniDebitorie);
            $nomeFile = $this->generaNomeFile($numPosizioni, 'Cancellazione', $codServizio);
            if (!$nomeFile) {
                return false;
            }
            // crea zip con dentro il txt da inviare all'sftp
            $file = $this->creaFileZipDaArray($posizioniDebitorie, $nomeFile, 'Cancellazione');
            if (!$file) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Metodo per invio su ambiente sFTP E-Fil
     * @param $nomeFile nome del file 
     * @param $file da inviare
     * @param $codServizio per genereare il path per l'invio (l'ambiente ufficiale ï¿½ diviso per servizi attivi)
     * @return true se l'invio ï¿½ andato a buon fine oppure false
     */
    private function customInvioPerCancellazione($nomeFile, $file, $codServizio) {
        // invio il file all'sftp
        try {

            $confEfil = $this->leggiConfEfil(array());

            // Path certificato (per riferimento)

            $devLib = new devLib();
            $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
            if (intval($configGestBin['CONFIG']) === 0) {
                // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
                $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
            } else {
                $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
            }

            //Settaggio paramentri sFTP
            $this->settaParametriSftp($sftp, $confEfil, $certPath);

            // Genero path per reperimento ricevuta... il path cambia in base all'ambiente su cui sto lavorando (Test o Ufficiale)
            $pathCanc = $this->generaPathSftp($confEfil, $confEfil['SFTPCARTCANC'], $codServizio);

            $pathCanc .= $nomeFile;
            $esitoInvioSFTP = $sftp->uploadFile($pathCanc, $file);

            unlink($certPath);
            unlink($file);

            return $esitoInvioSFTP;
        } catch (Exception $exc) {
            return false;
        }
    }

    protected function customPubblicazioneMassiva($progkeytabInvio, $scadenzePerPubbli, $saltaPubblicazione = false) {
        $risposta = array();
        if ($this->customPreparazionePerPubblicazione($progkeytabInvio, $scadenzePerPubbli, $nomeFile, $file)) {
            try {
                // INSERT su BGE_AGID_INVII
                $progint = $this->generaProgressivoFlusso(date('Ymd'), $scadenzePerPubbli[0]['CODSERVIZIO'], 1);
                $invio = array(
                    'TIPO' => 1,
                    'INTERMEDIARIO' => 1,
                    'CODSERVIZIO' => $scadenzePerPubbli[0]['CODSERVIZIO'],
                    'DATAINVIO' => date('Ymd'),
                    'PROGINT' => $progint,
                    'NUMPOSIZIONI' => count($scadenzePerPubbli),
                    'STATO' => 1,
                );

                $this->insertBgeAgidInvii($progkeytabInvio, $invio);

                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

                // Costruisco record da salvare in BGE_AGID_ALLEGATI
                $allegati = array(
                    'TIPO' => 1,
                    //  'IDINVRIC' => intval($progkeytabInvio) > 0 ? $progkeytabInvio : $scadenza['PROGINV'],
                    'IDINVRIC' => $progkeytabInvio,
                    'DATA' => date('Ymd'),
                    'NOME_FILE' => $nomeFile,
                );

                if (intval($configGestBin['CONFIG']) === 0) {
                    //INSERT su BGE_AGID_ALLEGATI
                    $allegati['ZIPFILE'] = file_get_contents($file);
                    $this->insertBgeAgidAllegati($allegati);
                } else {
                    //$allegati['ZIPFILE'] = 'ffff';
                    $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                    $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                    if (!file_exists($configPathAllegati['CONFIG'])) {
                        mkdir($configPathAllegati['CONFIG']);
                    }
                    file_put_contents($configPathAllegati['CONFIG'] . "/Allegato_" . $progkeytabAlleg, file_get_contents($file));
                }

                if (!$this->getSimulazioneSF() || !$this->getSimulazione()) {
                    if (!$this->customInvioPubblicazione($nomeFile, $file, $scadenzePerPubbli)) {
                        return false; // i messaggi di errore li setta su customInvioPubblicazione
                    } else {
                        //UPDATE su BGE_AGID_SCADENZE 
                        foreach ($scadenzePerPubbli as $scadenzeP) {
                            $toUpdate['PROGKEYTAB'] = $scadenzeP['PROGKEYTAB'];
                            $toUpdate['STATO'] = 2;
                            $toUpdate['PROGINV'] = $progkeytabInvio;
                            $toUpdate['DATAINVIO'] = date('Ymd');
                            $toUpdate['TIMEINVIO'] = date('H:i:s');
                            $this->aggiornaBgeScadenze($toUpdate);

                            $this->rispostaPubblicazione($risposta, $scadenzeP, true);
                        }
                        return $risposta;
                    }
                }
            } catch (Exception $exc) {
                $msgError .= "Errore: " . $exc->getMessage() . " Servizio:" . $scadenza['CODSERVIZIO'];
                $this->handleError(-1, $msgError);
                return false;
            }
        } else {
            $this->handleError(-1, "Errore preparazione invio");
        }
        return false;
    }

    // Reinvio pubblicazione se sono passate piï¿½ di 24 ore
    public function customReinvioPubblicazione($progkeytabInvio) {
        $filtri['TIPO'] = 1;
        $filtri['IDINVRIC'] = $progkeytabInvio;
        $allegato = $this->getLibDB_BGE()->leggiBgeAgidAllegati($filtri);
        //$this->setSimulazione(true);
        $this->pubblicazioneReinvio($progkeytabInvio, $allegato);
    }

    /**
     * Legge il file txt a posizioni fisse e lo trasforma in array
     * @param String $file il file txt
     * @return array()
     */
    public function leggiFileTxt($file) {
        // arriva un txt con estensione .N001
        // va letto e trasformato in array() vedere come arriva $file e poi tirarci fuori la stringa
        $lib = new cwbLibDB_BGE();
        $confEfil = $lib->leggiBgeAgidConfEfil(array());

        // test
        $file = file_get_contents('C:\Users\luca.cardinali\Desktop\Z001.T0000001.03033.20160928.01.INS.N001');

        $res = preg_split('/\r\n|\n|\r/', trim($file));

        $toReturn = array();
        // divido il txt in un array di stringhe che contiene una riga per ogni key
        $posizione = 0;
        foreach ($res as $stringaRecord) {
// scorro le righe e per ogni riga scorro tutti i campi e tiro fuori l'array 
// prendendo i dati per dimensioni fisse
            $start = 0;
            foreach ($this->mappingCampi as $key => $mapping) {
                // prendo n caratteri in base alla lunghezza del campo
                $valore = substr($stringaRecord, $start, $mapping['LUNGHEZZA']);
                if ($mapping['TIPO'] === self::TIPO_INTERO) {
                    $valore = intval($valore);
                } else if ($mapping['TIPO'] === self::TIPO_STRINGA) {
                    $valore = str_replace($confEfil['FILLER'], "", $valore); // RIMUOVO FILLER
                } else if ($mapping['TIPO'] === self::TIPO_DATA) {
                    $valore = date("d-m-Y", strtotime($valore));
                } else if ($mapping['TIPO'] === self::TIPO_DECIMALE) {
                    $num = substr($valore, 0, $mapping['LUNGHEZZA'] - 2); // prime n-2 rige sono l'intero
                    $dec = substr($valore, $mapping['LUNGHEZZA'] - 2, $mapping['LUNGHEZZA']); // ultime 2 cifre decimali

                    $valore = intval($num) . self::SEPARATORE_DECIMALE . $dec;
                }

                $start += $mapping['LUNGHEZZA'];
                $toReturn[$posizione][$key] = $valore;
            }
            $posizione++;
        }

        return $toReturn;
    }

    private function getScadenzePerCancellazione() {
        $filtri = array(
            'INTERMEDIARIO' => itaPagoPa::EFILL_TYPE,
        );
        return $this->getLibDB_BGE()->leggiBgeAgidScadenzeGroupByServizioCanc($filtri);
    }

    private function leggiConfEfil() {
        $conf_Efil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil();
        return $conf_Efil;
    }

    private function generaPathSftp($configurazione, $cartella, $codServizio) {
        // Cambio i path in base all'ambiente su cui sto lavorando.
        if ($configurazione['SFTPHOST'] === 'filetransfer.integrazione.plugandpay.it') {// ambiente di test
            // prendo la cartella su cui pubblicare e se non finisce con / la metto
            $path = $cartella;
            if (substr($path, -1) != '/' || substr($path, -1) != '\\') {
                $path .= '/';
            }
        } else {
            $path = $this->creaPathUfficiale($codServizio, $cartella);
        }
        return $path;
    }

    private function creaPathUfficiale($codServizio, $pathCartella) {
        // prendo la cartella su cui pubblicare e se non finisce con / la metto
        $codServizio = str_pad($codServizio, 7, "0", STR_PAD_LEFT);
        $path = $codServizio . $pathCartella;
        if (substr($path, 0, 1) != '/' || substr($path, 0, 1) != '\\') {
            $path = '/' . $path;
        }

        if (substr($path, -1) != '/' || substr($path, -1) != '\\') {
            $path .= '/';
        }
        return $path;
    }

    protected function getCustomTipins($massivo = false) {
        if ($massivo) {
            return 1;
        }

        return 0;
    }

    private function scriviStoricoEfil($scadenza) {
        // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCADE PER MANTENERE LO STORICO
        $toInsert = array();
        $toInsert = $scadenza;
        $where = ' PROGKEYTAB=' . $scadenza['PROGKEYTAB'];
        $progintStoscade = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BGE_AGID_STOSCADE', $where);
        $toInsert['PROGINT'] = $progintStoscade;
        $this->inserisciBgeAgidStoscade($toInsert);

        // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCA_EFIL PER MANTENERE LO STORICO
        $toInsert = array();
        $toInsert = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
        $where = ' PROGKEYTAB=' . $scadenza['PROGKEYTAB'];
        $progintStoscaEfil = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BGE_AGID_STOSCA_EFIL', $where);
        $toInsert['PROGINT'] = $progintStoscaEfil;
        $this->inserisciBgeAgidStoscaEfil($toInsert);
    }

    private function xmlErrori($arrayXml, $tagXmlOk, $tagXmlKo) {
        $errori = null;
        // Metto valore a 0 per tutti, se sono empty o null
        if (!$arrayXml[$tagXmlOk][0]['@textNode'] || $arrayXml[$tagXmlOk][0]['@textNode'] === null) {
            $arrayXml[$tagXmlOk][0]['@textNode'] = 0;
        }
        if (!$arrayXml[$tagXmlKo][0]['@textNode'] || $arrayXml[$tagXmlKo][0]['@textNode'] === null) {
            $arrayXml[$tagXmlKo][0]['@textNode'] = 0;
        }
        if (!$arrayXml['NumeroTotaliPosizioni'][0]['@textNode'] || $arrayXml['NumeroTotaliPosizioni'][0]['@textNode'] === null) {
            $arrayXml['NumeroTotaliPosizioni'][0]['@textNode'] = 0;
        }
        $errori = array($tagXmlOk => $arrayXml[$tagXmlOk][0]['@textNode'],
            $tagXmlKo => $arrayXml[$tagXmlKo][0]['@textNode'],
            "NumeroTotaliPosizioni" => $arrayXml['NumeroTotaliPosizioni'][0]['@textNode'],
        );
        return $errori;
    }

    protected function generaArrayWsPubblicazioni($posizioni, $puntuale = true) {
        if ($posizioni) {
            $confIntermediario = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
            if ($puntuale) {
                return array(
                    'IdApplicazione' => $confIntermediario['IDAPPLICAZIONE'],
                    'Posizione' => $posizioni
                );
            } else {
                $pos = array(
                    'Posizione' => $posizioni
                );

                return array(
                    'IdApplicazione' => $confIntermediario['IDAPPLICAZIONE'],
                    'Posizioni' => $pos
                );
            }
        }

        return null;
    }

    private function certificatoEfil(&$certPath, $sftpfilekey, $password, $gestioneBinari = false, $devLib) {
        // il certificato va appoggiato su disco poi lo cancello una volta finito
        $certPath = itaLib::getUploadPath() . "/cert" . time() . '.pem';
        rewind($sftpfilekey);
        if (!$gestioneBinari) {
            file_put_contents($certPath, stream_get_contents($sftpfilekey));
        } else {
            $configCertPath = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_CERT', false);
            $nomeCert = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_NOME_CERT', false);
            $pathCertLocale = $configCertPath['CONFIG'] . "/" . $nomeCert['CONFIG'];
            //$itasftp = new itaSFtpUtils();
            //            $itasftp = new itaSFtpUtils();
//            if ($itasftp->convertPpkToPem($pathCertLocale, $password, $password, true)) {
//                file_put_contents($certPath, $itasftp->getResult());
            file_put_contents($certPath, file_get_contents($pathCertLocale));

            // TODO GESTIRE ANCHE LA PARTE SE RICHIAMATA sftpUtils_curl... scommentare le righe sopra 
        }
    }

    private function settaParametriSftp(&$sftp, $confEfil, $certPath) {
        $sftp = new itaSFtpUtils();
        // setto i parametri sftp
        $sftp->setParameters($confEfil['SFTPHOST'], $confEfil['SFTPUSER'], '', $certPath, $confEfil['SFTPPASSWORD']);
    }

    private function nomeFileDaElaborare($stato, $tipo, $codServizio) {
        $filtri = array();
        $filtri['STATO'] = $stato;
        $filtri['TIPO'] = $tipo;
        $filtri['CODSERVIZIO'] = $codServizio;
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        return $this->getLibDB_BGE()->leggiBgeAgidInviiNomeFile($filtri);
    }

    private function reperisciDatiInvio($nomeFile) {
        $filtri = array();
        $filtri['NOME_FILE'] = $nomeFile;
        return $this->getLibDB_BGE()->leggiBgeAgidInviiRicevuta($filtri);
    }

    // Verifica le posizioni debitorie... se campo obbligatorio non valorizzato, aggiorna la scadenza con STATO = 3 e 
    // rimuove l'elemento dall'array contenente tutte le posizioni debitorie da pubblicare
    private function verificaPosizioniDebitorie($posizioniDebitorie, $progkeytabInvio) {
        $mappingCampi = $this->mappingCampiPubbl;
        foreach ($posizioniDebitorie as $key => $value) {
            $flagUnset = false;
            $codriferimento = $value['CODRIFERIMENTO'];
            $errori = array();
            foreach ($value as $keyS => $valueS) {
                if (array_key_exists($keyS, $mappingCampi)) {
                    if ($mappingCampi[$keyS]['OBBLIGATORIO']) {
                        switch ($mappingCampi[$keyS]['TIPO']) {
                            case self::TIPO_INTERO:
                            case self::TIPO_DECIMALE:
                                if (intval($valueS) === 0) {
                                    $flagUnset = true;
                                    $this->gestioneErrorePosDebitoria($keyS, $errori);
                                }
                                break;

                            case self::TIPO_STRINGA:
                            case self::TIPO_DATA:
                                // metto anche 0 come condizione, perchï¿½ ad esempio CODRIFERIMENTO ï¿½ stringa ma potrebbe esserci 0
                                if ($valueS === '' || $valueS === '0') {
                                    $flagUnset = true;
                                    $this->gestioneErrorePosDebitoria($keyS, $errori);
                                }
                                break;
                        }
                    }
                }
            }
            if ($flagUnset) {
                $noteSosp = json_encode($errori);
                unset($posizioniDebitorie[$key]);
                $data = date('Ymd');
                $ora = date('H:i:s');

                //Aggiorno scadenza rimossa da array con NOTESOSP valorizzato da json con specificati i campi obb. mancanti
                $sqlParams = array();
                $sqlParams[] = array('name' => 'STATO', 'value' => 3, 'type' => PDO::PARAM_INT);
                $sqlParams[] = array('name' => 'PROGINV', 'value' => $progkeytabInvio, 'type' => PDO::PARAM_INT);
                $sqlParams[] = array('name' => 'TIMESOSP', 'value' => $ora, 'type' => PDO::PARAM_STR);
                $sqlParams[] = array('name' => 'DATASOSP', 'value' => $data, 'type' => PDO::PARAM_STR);
                $sqlParams[] = array('name' => 'NOTESOSP', 'value' => $noteSosp, 'type' => PDO::PARAM_STR);
                $sqlParams[] = array('name' => 'CODRIFERIMENTO', 'value' => $codriferimento, 'type' => PDO::PARAM_INT);
                //UPDATE su BGE_AGID_SCADENZE 
                if (!$this->getSimulazione()) {
                    $sqlString = "UPDATE BGE_AGID_SCADENZE SET STATO=:STATO,DATASOSP=:DATASOSP,TIMESOSP=:TIMESOSP"
                            . " ,NUMSOSP=NUMSOSP+1,NOTESOSP=:NOTESOSP, PROGINV=:PROGINV WHERE CODRIFERIMENTO=:CODRIFERIMENTO";
                    $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);
                }
            }
        }
        return $posizioniDebitorie;
    }

    private function gestioneScarti($arrayXml, $filler) {
        foreach ($arrayXml['SCARTI'][0]['SCARTO'] as $keyXml => $value) {
            $toUpdate = array();
            $codRiferimento = str_replace($filler, " ", $value['CodiceRiferimentoCreditore'][0]['@textNode']);
            $filtri = array();
            $filtri['CODRIFERIMENTO'] = trim($codRiferimento);
            $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);

            //Leggo gli errori dall'xml, trasformo in Json e salvo sul campo NOTEOSP
            $errori = null;
            foreach ($arrayXml['SCARTI'][0]['SCARTO'][$keyXml]['ERRORI'][0]['ERRORE'] as $Xml) {
                $coderrore = array("cod" => $Xml['CodiceErrore'][0]['@textNode'], "desc" => $Xml['DescrizioneErrore'][0]['@textNode']);
                $errori[] = $coderrore;
                $coderrore = null;
            }

            $noteOsp = $Xml['DescrizioneErrore'][0]['@textNode'];

            // Aggiorno la scadenza
            $toUpdate = array();
            $toUpdate['PROGKEYTAB'] = $scadenza[0]['PROGKEYTAB'];
            $toUpdate['STATO'] = 3;
            $toUpdate['DATASOSP'] = date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
            $toUpdate['TIMESOSP'] = date('H:i:s', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
            $toUpdate['NOTESOSP'] = $noteOsp;
            $toUpdate['NUMSOSP'] = ++$scadenza[0]['NUMSOSP'];
            $this->aggiornaBgeScadenze($toUpdate);

            // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCADE E SU BGE_AGID_STOSCA_EFIL
            //$this->scriviStoricoEfil($scadenza[0]);
        }
    }

    private function fornituraRifiutata($arrayXml, $proginvio, $tipoFornitura) {
        //Leggo gli errori dall'xml, trasformo in Json e salvo sul campo NOTEERRORE di BGE_AGID_INVII
        if ($tipoFornitura === 'Pubblicazione') {
            $errori = null;
            foreach ($arrayXml['ERRORI'][0]['ERRORE'] as $Xml) {
                $coderrore = array("cod" => $Xml['CodiceErrore'][0]['@textNode'], "desc" => $Xml['DescrizioneErrore'][0]['@textNode']);
                $errori[] = $coderrore;
                $coderrore = null;
            }
            $noteErrore = json_encode($errori);
            // UPDATE INVIO con STATO = Rifiutato
            $this->updateBgeAgidInvii($proginvio, 3, $noteErrore);

            // Se la Ricevuta di Accettazione ha ESITO = 'Rifiutata', allora devo aggiornare ogni scadenze coinvolta
            $data = date('Ymd', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
            $ora = date('H:i:s', strtotime($arrayXml['DataGenerazioneRicevuta'][0]['@textNode']));
            $noteSosp = 'Descrizione Errore: Fornitura rifiutata';


            $sqlParams = array();
            $sqlParams[] = array('name' => 'STATO', 'value' => 3, 'type' => PDO::PARAM_INT);
            $sqlParams[] = array('name' => 'DATASOSP', 'value' => $data, 'type' => PDO::PARAM_STR);
            $sqlParams[] = array('name' => 'TIMESOSP', 'value' => $ora, 'type' => PDO::PARAM_STR);
            $sqlParams[] = array('name' => 'NOTESOSP', 'value' => $noteSosp, 'type' => PDO::PARAM_STR);
            $sqlParams[] = array('name' => 'PROGINV', 'value' => $proginvio, 'type' => PDO::PARAM_INT);
            //update  BGE_AGID_SCADENZE 
            if ($this->getSimulazione() != true) {
                $sqlString = "UPDATE BGE_AGID_SCADENZE SET STATO=:STATO,DATASOSP=:DATASOSP,TIMESOSP=:TIMESOSP"
                        . " ,NUMSOSP=NUMSOSP+1,NOTESOSP=:NOTESOSP WHERE PROGINV=:PROGINV";
//                $sqlString = "DELETE FROM BGE_AGID_SCADENZE WHERE PROGINV=:PROGINV";
                $this->getCITYWARE_DB()->query($sqlString, false, $sqlParams);

                // Faccio SELECT su BGE_AGID_SCADENZE con PROGINV = BGE_AGID_INVII.PROGKEYTAB AND STATO = 3
                // TODO DA TESTARE SU AREA TEST sFTP EFIL
                $filtri = array();
                $filtri['STATO'] = 3;
                $filtri['PROGINV'] = $proginvio;
                $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);

                // Prendo il record, e faccio insert su BGE_AGID_STOSCADE.
                foreach ($scadenza as $value) {
                    // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCADE E SU BGE_AGID_STOSCA_EFIL
                    $this->scriviStoricoEfil($value);
                }
            }
        } elseif ($tipoFornitura === 'Cancellazione') {
            // UPDATE INVIO con STATO = TENTATIVO DI CANCELLAZIONE FALLITO
            $this->updateBgeAgidInvii($proginvio, 3);
            // Faccio SELECT su BGE_AGID_SCADENZE con PROGINV = BGE_AGID_INVII.PROGKEYTAB AND STATO = 13
            // TODO DA TESTARE SU AREA TEST sFTP EFIL
            $filtri = array();
            $filtri['STATO'] = 13;
            $filtri['PROGINV'] = $proginvio;
            $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri);

            // Prendo il record, e faccio insert su BGE_AGID_STOSCADE.
            foreach ($scadenza as $value) {
                // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCADE E SU BGE_AGID_STOSCA_EFIL
                $this->scriviStoricoEfil($value);
            }
        }
    }

    // Genera array Posizione Contribuente per Pubblicazione su Nodo da inviare tramite WS
    private function generaArrayPosizioneWsPubblicazioni($scadenza, $scaEfil, $confIntermediario, $bta_servrenddet) {
        $ente = $this->getLibDB_BWE()->leggiBweParam(array());

        $interm = $this->getServizioIntermediario($scadenza['CODTIPSCAD'], $scadenza['SUBTIPSCAD']);
        if ($scadenza['TIPOPERS'] === 'F') {
            $tipopers = 'PersonaFisica';
        } else {
            $tipopers = 'PersonaGiuridica';
        }


        $scadet = $this->getLibDB_BGE()->leggiBgeAgidScadet(array('IDSCADENZA' => $scadenza['PROGKEYTAB']));
        if ($scadet) {
            foreach ($scadet as $key => $value) {
                $arrayAccertamenti[] = array(
                    'Accertamento' => array(
                        'Codice' => $value['CODICE'],
                        'ImportoInCentesimi' => $value['IMPORTO'] * 100
                    )
                );
            }
        }
        $posizione = array();
        if ($arrayAccertamenti) {
            $posizione['Accertamenti'] = $arrayAccertamenti;
        }
        $posizione['Causale'] = trim($scadenza['DESCRPEND']);
        $posizione['CodiceRiferimentoCreditore'] = $scadenza['CODRIFERIMENTO'];
        $posizione['Creditore'] = array(
            'CodiceEnte' => str_pad(trim($confIntermediario['CODENTECRED']), 7, "0", STR_PAD_LEFT), // 7 cifre
            'Intestazione' => trim($ente['DESENTE'])
        );
        $posizione['DataScadenza'] = substr($scadenza['DATASCADE'], 0, 10);
        $posizione['Debitore'] = array(
            'CodiceFiscalePartitaIva' => $scadenza['CODFISCALE'],
            'Nazione' => array(
                'NomeNazione' => 'ITALIA'
            ),
            'Nominativo' => trim($scaEfil['ANADEBITORE']),
            'TipoPagatore' => $tipopers,
        );
        $posizione['ImportoInCentesimi'] = ($scadenza['IMPDAPAGTO'] * 100); // in centesimi
        $posizione['ParametriPosizione'] = '';
        $posizione['Servizio'] = array(
            'CodiceServizio' => str_pad(trim($interm['CODSERVIZIO']), 7, "0", STR_PAD_LEFT), // 7 cifre
            'Descrizione' => trim($interm['TIPORIFCRED'])
        );
        $posizione['TipoRiferimentoCreditore'] = trim($interm['TIPORIFCRED']);


//        $posizione = array(
//            'Causale' => trim($scadenza['DESCRPEND']),
//            'CodiceRiferimentoCreditore' => $scadenza['CODRIFERIMENTO'],
//            'Creditore' => array(
//                'CodiceEnte' => str_pad(trim($confIntermediario['CODENTECRED']), 7, "0", STR_PAD_LEFT), // 7 cifre
//                'Intestazione' => trim($ente['DESENTE'])
//            ),
//            'DataScadenza' => substr($scadenza['DATASCADE'], 0, 10),
//            'Debitore' => array(
//                'CodiceFiscalePartitaIva' => $scadenza['CODFISCALE'],
//                'Nazione' => array(
//                    'NomeNazione' => 'ITALIA'
//                ),
//                'Nominativo' => trim($scaEfil['ANADEBITORE']),
//                'TipoPagatore' => $tipopers,
//            ),
//            'ImportoInCentesimi' => ($scadenza['IMPDAPAGTO'] * 100), // in centesimi
//            'ParametriPosizione' => '',
//            'Servizio' => array(
//                'CodiceServizio' => str_pad(trim($interm['CODSERVIZIO']), 7, "0", STR_PAD_LEFT), // 7 cifre
//                'Descrizione' => trim($interm['TIPORIFCRED'])
//            ),
//            'TipoRiferimentoCreditore' => trim($interm['TIPORIFCRED']),
//        );
        return $posizione;
    }

    protected function getCustomConfIntermediario() {
        return $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
    }

    public function customGetDatiPagamentoDaIUV($IUV) {
        $posizione = array(
            'Posizione' =>
            array(
                'Causale' => $scadenza['DESCRPEND'],
                'CodiceRiferimentoCreditore' => '2133443554',
                'Creditore' => array(
                    'CodiceEnte' => str_pad($confIntermediario['CODENTECRED'], 7, "0", STR_PAD_LEFT), // 7 cifre
                    'CodiceFiscalePartitaIva' => '00332510429',
                    'IBAN' => 'IT60X054281110100000044444',
                    'Intestazione' => 'Comune di xxx',
                ),
                'DataScadenza' => $scadenza['DATASCADE'],
                'Debitore' => array(
                    'CodiceFiscalePartitaIva' => $scadenza['CODFISCALE'],
                    'Email' => 'prova@prova.it',
                    'Indirizzo' => 'Via',
                    'Localita' => 'Jesi',
                    'Nazione' => array(
                        'NomeNazione' => 'ITALIA'
                    ),
                    'Nominativo' => $scaEfil['ANADEBITORE'],
                    'TipoPagatore' => $tipopers,
                ),
                'ImportoInCentesimi' => ($scadenza['IMPDAPAGTO'] * 100), // in centesimi
                'ParametriPosizione' => '',
                'Servizio' => array(
                    'CodiceServizio' => str_pad($interm['CODSERVIZIO'], 7, "0", STR_PAD_LEFT) // 7 cifre
                ),
                'TipoRiferimentoCreditore' => $interm['TIPORIFCRED'],
            )
        );
        return array(
            'IdApplicazione' => $confIntermediario['IDAPPLICAZIONE'],
            'Carrello' => $posizione
        );
    }

    protected function customInvioPerPubblicazione($nomeFile, $file) {
        
    }

    protected function customTestConnection($massivo, $tipoChiamata) {
        $errorRoot = false;
        $devLib = new devLib();
        $confEfil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
        $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
        $uplFile = itaLib::getUploadPath() . "/" . time();
        $pathCertificatoCaricato = $uplFile;

        if (intval($configGestBin['CONFIG']) === 0) {
            // Path certificato (per riferimento) 
            file_put_contents($pathCertificatoCaricato, stream_get_contents($confEfil['SFTPFILEKEY']));
        } else {
            $configCertPath = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_CERT', false);
            $nomeCert = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_NOME_CERT', false);
            if (file_exists($configCertPath['CONFIG'] . "/" . $nomeCert['CONFIG'])) {
                $pathCertificatoLocal = $configCertPath['CONFIG'] . "/" . $nomeCert['CONFIG'];
//                if ($itasftp->convertPpkToPem($pathCertLocale, $confEfil['SFTPPASSWORD'], $confEfil['SFTPPASSWORD'], true)) {
                file_put_contents($pathCertificatoCaricato, file_get_contents($pathCertificatoLocal));
//                }
            } else {
                Out::msgStop('Attenzione', "Certificato mancante in " . $configCertPath['CONFIG']);
            }
        }

        // Per prima cosa testo la connessione posizionandomi direttamente nella root
        $pathTestConnection = '/';
        $server = $confEfil['SFTPHOST'];
        $user = $confEfil['SFTPUSER'];
        $certpassword = $confEfil['SFTPPASSWORD'];
        $params = array(
            'SERVER' => $server,
            'USER' => $user,
            'CERTPASS' => $certpassword,
            'PATHTESTCONN' => $pathTestConnection,
            'CONFEFIL' => $confEfil,
            'PATHCERT' => $pathCertificatoCaricato
        );

        $esitoConnection = $this->connection($params, $pathTestConnection);
        $listRootFTP = $this->sftpUtils->getResult();
        if (!$esitoConnection) {
            return $this->erroreConnRoot($this->sftpUtils->getErrCode());
        } else {
            // Inizio test di connessione per ogni singola cartella di configurazione.
            $filtri['INTERMEDIARIO'] = 1;
            $serviziAttiviEfil = $this->getLibDB_BTA()->leggiBtaServrendppa($filtri);
            if ($serviziAttiviEfil) {
                foreach ($serviziAttiviEfil as $value) {
                    $this->testConnectionServAttivi($params, $value['CODSERVIZIO'], $arrayErrori, $arrayMsg);
                    if (!empty($arrayErrori) && !empty($arrayMsg)) {
                        $Errori[$value['CODSERVIZIO']] = $arrayErrori;
                        $MsgErrori[] = $arrayMsg;
                    }
                }
            } elseif ($tipoChiamata != 'Default') {
                // Se sono richiamato dalla pagina di Gestione Nodo e non ho servizi attivi, devo dare il messaggio di configurare almeno un servizio. 
                $this->erroreConnRoot($codErrore, $tipoChiamata);
            } else {
                // Sono nella situazione in cui non sono stati attivati servizi, ma la connessione sulla root dell'ftp ha dato esito POSITIVO.
                // La pagina di E-Fil ï¿½ stata richiamata da menï¿½ e sono in fase di primo collaudo. Non do il messaggio di configurare almeno un servizio,
                // ma listo tutto l'ambiente per reperire i codici servizio presenti.
                // Pulisco array del listato in modo da avere solamente le cartelle dei servizi presenti nell'ambiente E-Fil.
                foreach ($listRootFTP as $key => $value) {
                    if (strstr($value, ".")) {
                        // elimino l'elemento dall'array
                        unset($listRootFTP[$key]);
                    }
                }
                foreach ($listRootFTP as $key => $codServizio) {
                    $this->testConnectionServAttivi($params, $codServizio, $arrayErrori, $arrayMsg);
                    if (!empty($arrayErrori) && !empty($arrayMsg)) {
                        $Errori[$value['CODSERVIZIO']] = $arrayErrori;
                        $MsgErrori[] = $arrayMsg;
                    }
                }
            }
        }
        unlink($pathCertificatoCaricato);
        return array(
            'Errori' => $Errori,
            'Messaggi' => $MsgErrori
        );
    }

    private function erroreConnRoot($codErrore, $tipoChiamata = null) {
        if ($tipoChiamata) {
            return $msg_error = "Nessun Servizio attivo per l'intermediario E-Fil";
        }
        switch ($codErrore) {
            case 28:
                $msg_error = 'Errore: Nodo PagoPA non raggiungibile (Codice Errore ' . $this->sftpUtils->getErrCode() . ' ' . $this->sftpUtils->getErrMessage() . ')';
                return $msg_error;
            default:
                $msg_error = 'Errore: ' . $this->sftpUtils->getErrMessage();
                return $msg_error;
        }
    }

    private function testConnectionServAttivi($params, $codservizio, &$arrayErrori = array(), &$arrayMsg = array()) {
        $arrayErrori = array();
        $arrayMsg = array();
        for ($index = 0; $index < 6; $index++) {
            switch ($index) {
                case 0:
                    //pubblicazione
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTPUBBL'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['PUBBL'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
                case 1:
                    //cancellazione
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTCANC'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['CANC'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
                case 2:
                    //arricchimento
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTARRIC'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['ARRIC'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
                case 3:
                    //rendicontazione
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTREND'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['REND'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
                case 4:
                    //ricevute telematiche
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTRT'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['RT'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
                case 5:
                    //ricevute
                    $pathTestConnection = $params['CONFEFIL']['SFTPCARTRIC'];
                    $esitoConnection = $this->customConnection($codservizio, $pathTestConnection, $params, $pathTestConnection);
                    if (!$esitoConnection) {
                        $arrayErrori['RIC'] = true;
                        $arrayMsg[] = "Impossibile connettersi al seguente path: " . $pathTestConnection;
                    }
                    break;
            }
        }
    }

    private function testSFTPpathSpecifico($codServizio, $path, $confEfil) {
        if ($confEfil['SFTPHOST'] === 'filetransfer.integrazione.plugandpay.it') {// ambiente di test
            // prendo la cartella su cui pubblicare e se non finisce con / la metto
            if (substr($path, -1) != '/' || substr($path, -1) != '\\') {
                $path .= '/';
                $pathTestConnection = $path;
            }
        } else {
            $pathTestConnection = str_pad($codServizio, 7, "0", STR_PAD_LEFT) . $path;
            if (substr($pathTestConnection, 0, 1) != '/' || substr($pathTestConnection, 0, 1) != '\\') {
                $pathTestConnection = '/' . $pathTestConnection;
            }
            if (substr($pathTestConnection, -1) != '/' || substr($pathTestConnection, -1) != '\\') {
                $pathTestConnection .= '/';
            }
        }
        return $pathTestConnection;
    }

    private function connection($params, $pathTestConnection) {
        $this->sftpUtils->setParameters($params['SERVER'], $params['USER'], null, $params['PATHCERT'], $params['CERTPASS']);
        return $this->sftpUtils->listOfFiles($pathTestConnection);
    }

    private function customConnection($codServizio, $pathTestConnection, $params, &$pathTestConnection) {
        $pathTestConnection = $this->testSFTPpathSpecifico($codServizio, $pathTestConnection, $params['CONFEFIL']);
        return $this->connection($params, $pathTestConnection);
    }

    protected function customEseguiInserimento($posizioni) {
        // se ï¿½ puntuale chiamo il ws di inserimento puntuale di efill
        $scadenza = $posizioni;
        $data = $this->generaArrayWsPubblicazioni($posizioni, true);

        if (!$data) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }

        $wsParams = array(
            'request' => $data
        );

        $customNamespace = array(
            'CodiceServizio' => 'plug1'
        );

        $namespaces = array(
            'plug' => self::NAMESPACES_FEED_PLUG,
            'plug1' => self::NAMESPACES_FEED_PLUG1
        );
        $result = $this->callWs($wsParams, 'CaricaPosizione', $this->isProduzione() ? self::ENDPOINT_FEED_PRODUZIONE : self::ENDPOINT_FEED_TEST, self::SOAP_ACTION_FEED_PREFIX, $namespaces, $customNamespace, $scadenza);

        if (!$result) {
            return false;
        }

        return $result;
    }

    protected function customEseguiInserimentoElaboraRisposta($risposta) {
        $esito = $risposta['EsitoDiCaricamento'];
        $filtro = array('CODRIFERIMENTO' => $esito['CodiceRiferimentoCreditore']);
        $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtro, false);
        $scaEfil = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
        return $this->gestisciEsitoPubblImmediata($esito, $scadenza, $scaEfil);
    }

    private function gestisciEsitoPubblImmediata($esito, $scadenza, $scaEfil) {
        $toReturn = false;

        if ($esito['Esito'] == self::ESITO_POSITIVO) {
            $scadenza['IUV'] = $esito['IdentificativoPosizione'];
            $scadenza['STATO'] = 5;
            $scadenza['TIP_INS'] = 2;
            $scadenza['DATAPUBBL'] = date('Ymd');
            $scadenza['TIMEPUBBL'] = date('H:i:s');
            $this->insertUpdateRecord($scadenza, "BGE_AGID_SCADENZE", false, true);

            $esitoVerifica = $this->verificaPubblicazionePuntuale($scadenza['CODFISCALE'], $scadenza['CODRIFERIMENTO'], $numavviso, $IUV);
            // aggiorno lo iuv sulla scadenza bge_agid_scadenze
            if ($esitoVerifica) {
                $scaEfil['NUMAVVISO'] = $numavviso;
                $this->insertUpdateRecord($scaEfil, "BGE_AGID_SCA_EFIL", false, true);

                $this->rispostaPubblicazione($toReturn, $scadenza, true);
            } else {
                $this->handleError(-1, "Verifica inserimento fallita");
                $this->rispostaPubblicazione($toReturn, $scadenza, false, "Verifica inserimento fallita");
            }
        } else if ($esito['Esito'] == self::ESITO_NEGATIVO) {
            $msgerrori = $this->manageEfillErrorMessage($esito);
            $this->handleError(-1, $msgerrori);
            // aggiorno bge_agid_scadenze con errore
            $data = date('Ymd');
            $ora = date('H:i:s');
            $noteSosp = 'Descrizione Errore:' . $msgerrori;

            $scadenza['STATO'] = 3;
            $scadenza['TIP_INS'] = 2;
            $scadenza['DATASOSP'] = $data;
            $scadenza['TIMESOSP'] = $ora;
            $scadenza['NOTESOSP'] = $noteSosp;
            $this->insertUpdateRecord($scadenza, "BGE_AGID_SCADENZE", false, true);

            $this->rispostaPubblicazione($toReturn, $scadenza, false, $noteSosp);
        } else {
            $esitoVerifica = $this->verificaPubblicazionePuntuale($scadenza['CODFISCALE'], $scadenza['CODRIFERIMENTO'], $numavviso, $IUV);
            if ($esitoVerifica) {
                $this->rispostaPubblicazione($toReturn, $scadenza, true);
                $scadenza['IUV'] = $IUV;
                $scadenza['STATO'] = 5;
                $scadenza['TIP_INS'] = 2;
                $scadenza['DATAPUBBL'] = date('Ymd');
                $scadenza['TIMEPUBBL'] = date('H:i:s');
                $this->insertUpdateRecord($scadenza, "BGE_AGID_SCADENZE", false, true);

                $scaEfil['NUMAVVISO'] = $numavviso;
                $this->insertUpdateRecord($scaEfil, "BGE_AGID_SCA_EFIL", false, true);
            } else {
                // aggiorno bge_agid_scadenze con errore
                $data = date('Ymd');
                $ora = date('H:i:s');
                $noteSosp = 'Descrizione Errore:' . $this->getLastErrorDescription();

                $scadenza['STATO'] = 3;
                $scadenza['TIP_INS'] = 2;
                $scadenza['DATASOSP'] = $data;
                $scadenza['TIMESOSP'] = $ora;
                $scadenza['NOTESOSP'] = $noteSosp;
                $this->insertUpdateRecord($scadenza, "BGE_AGID_SCADENZE", false, true);
                $this->rispostaPubblicazione($toReturn, $scadenza, false, $noteSosp);
            }
        }

        return $toReturn;
    }

    private function verificaPubblicazionePuntuale($codFiscale, $codRif, &$numAvviso, &$IUV) {
        $params = array(
            'CodiceFiscale' => trim($codFiscale),
            'FormatoRitorno' => 'json',
        );
        sleep(3);
        $result = $this->ricercaPosizioneCFPI($params);
        if (!empty($result['Posizioni']['Posizione'][0])) {
            foreach ($result['Posizioni']['Posizione'] as $key => $posizione) {
                if ($posizione['CodiceRiferimentoCreditore'] != $codRif) {
                    // elimino l'elemento dall'array
                    unset($result['Posizioni']['Posizione'][$key]);
                }
            }
            $arrayResult = array_pop($result['Posizioni']['Posizione']);
        } else {
            $arrayResult = array_pop($result['Posizioni']);
        }
        if ($arrayResult) {
            $numAvviso = $arrayResult['NumeroAvviso'];
            $IUV = $arrayResult['IdentificativoPosizione'];
            return true;
        } else {
            return false;
        }
    }

    private function callWs($wsParams, $method, $endpoint, $soapActionPrefix, $namespaces = array(), $customNamespacePrefix = array(), $posizioneDebitoria = null, $nameSpacePrefixDefault = 'plug') {
        $client = new itaEFillClient();
        $client->setWebservices_uri($endpoint);
        $client->setNamespacePrefix($nameSpacePrefixDefault);
        $client->setNamespaces($namespaces);
        $client->setSoapActionPrefix($soapActionPrefix);
        $client->setCustomNamespacePrefix($customNamespacePrefix);
        $result = $client->$method($wsParams);
        $xmlRequest = $client->getRequest();
        $xmlResponse = $client->getResponse();
        // todo salvare in tabella la request
        //$this->salvaRequestScadenze($posizioneDebitoria, $xmlRequest,$xmlResponse);
        if (!$result) {
            $this->handleError(-1, $client->getError());
            return false;
        } else {
            return $client->getResult();
        }
    }

    private function salvaRequestScadenze($posizioneDebitoria, $xmlRequest, $xmlResponse) {
        try {
            $sca_Efil = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($posizioneDebitoria['PROGKEYTAB']);
            $nomeFileRequest = 'RequestScadenza_' . $posizioneDebitoria['PROGKEYTAB'];
            $nomeFileResponse = 'ResponseScadenza_' . $posizioneDebitoria['PROGKEYTAB'];
            $pathZipRequest = $this->creaZip($nomeFileRequest, $xmlRequest);
            $pathZipResponse = $this->creaZip($nomeFileResponse, $xmlResponse);
            if (intval($configGestBin['CONFIG']) === 0) {
                //INSERT su BGE_AGID_ALLEGATI
                $sca_Efil['REQUEST'] = file_get_contents($pathZipRequest);
                $sca_Efil['RESPONSE'] = file_get_contents($pathZipResponse);
                $this->aggiornaBgeScadenzeEfil($sca_Efil, false);
            } else {
                $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                file_put_contents($configPathAllegati . "/" . $nomeFileRequest, file_get_contents($pathZipRequest));
                file_put_contents($configPathAllegati . "/" . $nomeFileResponse, file_get_contents($pathZipResponse));
            }
        } catch (Exception $exc) {
            $log = array(
                "LIVELLO" => 3,
                "OPERAZIONE" => 1,
                "ESITO" => 3,
                    //"NOTE" => $exc
            );
            $this->scriviLog($log);
        }
        unlink($pathZipRequest);
        unlink($pathZipResponse);
    }

    public function fileDaElaborare($stato, $tipo, $codServizio, $confEfil, $cartella) {
        $devLib = new devLib();
        $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
        if (intval($configGestBin['CONFIG']) === 0) {
            // Path certificato (per riferimento) // TODO RIPRISTINARE DOPO TEST SU CITYSANB
            $this->certificatoEfil($certPath, $confEfil['SFTPFILEKEY']);
        } else {
            $this->certificatoEfil($certPath, '', $confEfil['SFTPPASSWORD'], true, $devLib);
        }

        $sftp = new itaSFtpUtils();
        // setto i parametri sftp
        $sftp->setParameters($confEfil['SFTPHOST'], $confEfil['SFTPUSER'], '', $certPath, $confEfil['SFTPPASSWORD']);

        $daElaborare = array();
        if ($stato == 5) {
            //Rendicontazioni
            $codServizio = str_pad($codServizio, 7, "0", STR_PAD_LEFT);
            $path = $this->generaPathSftp($confEfil, $cartella, $codServizio);
            $daElaborare = $this->fileDaRendicontare($sftp, $path);
        } else {
            $list = $this->nomeFileDaElaborare($stato, $tipo, $codServizio);
            if ($list) {
                $codServizio = str_pad($codServizio, 7, "0", STR_PAD_LEFT);
                $path = $this->generaPathSftp($confEfil, $cartella, $codServizio);
                $esitoListFile = $sftp->listOfFiles($path);
                if ($esitoListFile) {
                    $listOfFiles = $sftp->getResult();
                    if ($listOfFiles) {
                        //$listOfFiles = array_keys($listOfFiles);
                        // pulisco l'array da tutto ci? che non contiene "REND." perch? quando listo la dir, mi torna anche della "robaccia"
                        foreach ($list as $valueList) {
                            foreach ($listOfFiles as $key => $value) {
                                if (strpos($listOfFiles[$key], $valueList['NOME_FILE']) !== false) {
                                    $daElaborare[] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $daElaborare;
    }

    public function fileDaRendicontare($sftp, $path) {
        $filtri = array();
        $filtri['TIPO'] = 15;
        $listElaborate = $this->getLibDB_BGE()->leggiBgeAgidAllegati($filtri);
        $esitoListFile = $sftp->listOfFiles($path);
        if ($esitoListFile) {
            $listOfFiles = $sftp->getResult();

            // Il list della dir "Rendicontazioni" mi restituisce i file trovati nella chiave dell'array.
            // Mi faccio restituire tutte le chiavi dell'array cosï¿½ da avere i nome_file presenti
            //$listOfFiles = array_keys($listOfFiles);
            // pulisco l'array da tutto ciï¿½ che non contiene "REND." perchï¿½ quando listo la dir, mi torna anche della "robaccia"
            foreach ($listOfFiles as $key => $value) {
                if (!strstr($value, "REND.")) {
                    // elimino l'elemento dall'array
                    unset($listOfFiles[$key]);
                }
            }

            // Vado ad eliminare dall'array di list dir dell'sFTP, tutte le ricevute che mi ha restituito la select precedente.
            // Essendo sicuro che il risultato della query indica le RENDICONTAZIONI giï¿½ elaborate,
            // confronto il nome e se lo trovo lo elimino.
            if ($listElaborate) {
                foreach ($listOfFiles as $key => $list) {
                    foreach ($listElaborate as $elaborato) {
                        if (strstr($list, "REND.") === $elaborato['NOME_FILE']) {
                            // elimino l'elemento dall'array
                            unset($listOfFiles[$key]);
                        }
                    }
                }
            }
        } else {
            $listOfFiles = array();
        }
        return $listOfFiles;
    }

    private function manageEfillErrorMessage($esitoCaricamento) {
        $esiti = $esitoCaricamento['ErroriDiValidazione']['ErroreDiValidazionePosizione'];

        $toReturn = '';
        if ($esiti['Codice']) {
            // se ci sono tanti errori torna un array di array (codice - descrizione) con gli n errori 
            // se invece c'ï¿½ un solo errore torna direttamente l'errore (codice - descrizione) invece che un array di array con size 1
            $esiti = array($esiti);
        }

        foreach ($esiti as $esito) {
            $toReturn .= ' - ' . $esito['Descrizione'];
        }

        return $toReturn;
    }

    /**
     * Ricerca una posizione da iuv
     * 
     * @param String $IUV
     * @return false se errore oppure array con la posizione
     */
    public function customRicercaPosizioneDaIUV($IUV) {
        $conf = $this->getConfIntermediario();

        $data = array(
            'IdApplicazione' => $conf['IDAPPLICAZIONE'],
            'CodiceEnte' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
            'CodiceIdentificativo' => $IUV
        );

        if (!$data['CodiceEnte']) {
            $this->handleError(-1, "Parametro CodiceEnte mancante");
            return false;
        }
        if (!$data['IdApplicazione']) {
            $this->handleError(-1, "Parametro IdApplicazione mancante");
            return false;
        }

        $wsParams = array('request' => $data);

        $namespaces = array(
            'plug' => self::NAMESPACES_DELIVER_PLUG,
        );

        // chiamo ws di efill ricercaPosizioneIUV
        $result = $this->callWs($wsParams, 'ricercaPosizioneIUV', $this->isProduzione() ? self::ENDPOINT_DELIVER_PRODUZIONE : self::ENDPOINT_DELIVER_TEST, self::SOAP_ACTION_DELIVER_PREFIX, $namespaces);

        return $this->formatRispDaRicercaPosizione($result);
    }

    private function formatRispDaRicercaPosizione($result) {
        if ($result && $result['Posizione']) {
            return $this->formatRispostaDaRicercaPosizione($result['Posizione']['Causale'], $result['Posizione']['StatoPosizione'], $result['Posizione']['DataScadenza'], $result['Posizione']['ImportoInCentesimi'], $result['Posizione']['IdentificativoPosizione'], $result['Posizione']['CodiceRiferimentoCreditore'], null, null);
        }
        return false;
    }

    /**
     * Ricerca una posizione da CF O PIVA
     * 
     * @param String CF O PIVA
     * @return false se errore oppure array con la posizione
     */
    public function ricercaPosizioneCFPI($params) {
        $conf = $this->getConfIntermediario();

        $data = array(
            'IdApplicazione' => $conf['IDAPPLICAZIONE'],
            'CodiceEnte' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
            'CodiceFiscale' => $params['CodiceFiscale'],
            'StatoPosizione' => "Tutti"
        );

        if (!$data['CodiceEnte']) {
            $this->handleError(-1, "Parametro CodiceEnte mancante");
            return false;
        }
        if (!$data['IdApplicazione']) {
            $this->handleError(-1, "Parametro IdApplicazione mancante");
            return false;
        }

        $wsParams = array('request' => $data);

        $namespaces = array(
            'plug' => self::NAMESPACES_DELIVER_PLUG,
        );

        // chiamo ws di efill ricercaPosizioneCFPI
        $result = $this->callWs($wsParams, 'ricercaPosizioneCFPI', $this->isProduzione() ? self::ENDPOINT_DELIVER_PRODUZIONE : self::ENDPOINT_DELIVER_TEST, self::SOAP_ACTION_DELIVER_PREFIX, $namespaces);

        return $result;
    }

    /*
     * aggiorna i dati di una posizione gia pubblicata
     * 
     * @param String $IUV
     * @param array $toUpdate chiave/valore dei dati da aggiornare
     * @return result false se errore, ok se positivo      
     *  
     */

    public function rettificaPosizioneDaIUV($IUV, $toUpdate) {
        if (!$toUpdate) {
            $this->handleError(-1, "Nessun dato da aggiornare passato");
            return false;
        }

        // prima di rettificare verifica che la posizione non sia gia stata pagata
        $resultRic = $this->ricercaPosizioneDaIUV($IUV);

        if (!$resultRic) {
            $this->handleError($this->getLastErrorCode(), "Errore ricerca: " + $this->getLastErrorDescription());
            return false;
        }

        // se gia pagata non rettifica
        if ($resultRic['Posizione']['StatoPosizione'] != 'NonPagata') {
            $this->handleError(-1, "Rettifica non consentita. Stato Posizione: " . $resultRic['Posizione']['StatoPosizione']);
            return false;
        }

        $conf = $this->getConfIntermediario();

        $data = array(
            'IdApplicazione' => $conf['IDAPPLICAZIONE'],
            'CodiceEnte' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
            'CodiceIdentificativo' => $IUV
        );

        if ($toUpdate['DataScadenza']) {
            // bug DataScandenza su ws efill (lo hanno scritto male)
            $data['DataScandenza'] = $toUpdate['DataScadenza'];
        }

        if ($toUpdate['Importo']) {
            $data['ImportoInCentesimi'] = ((double) str_replace(",", ".", $toUpdate['Importo'])) * 100; // trasformo in centesimi
        }

        // se viene passata la causale da modificare la imposto, sennï¿½ carico quella presente sul nodo 
        // perchï¿½ il campo ï¿½ obbligatorio anche se non viene modificato
        if ($toUpdate['Causale']) {
            $data['Causale'] = $toUpdate['Causale'];
        } else {
            $emissione = $this->getEmissioneDaIUV($IUV);
            $data['Causale'] = $emissione['TIPORIFCRED'];
        }

        // TODO GESTIRE INFO AGGIUNTIVE

        $wsParams = array('request' => $data);

        $namespaces = array(
            'plug' => self::NAMESPACES_FEED_PLUG,
            'plug1' => self::NAMESPACES_FEED_PLUG1
        );

        // chiama ws efill RettificaPosizione
        $result = $this->callWs($wsParams, 'RettificaPosizione', $this->isProduzione() ? self::ENDPOINT_FEED_PRODUZIONE : self::ENDPOINT_FEED_TEST, self::SOAP_ACTION_FEED_PREFIX, $namespaces);

        if ($result['Esito'] == self::ESITO_POSITIVO) {
            return true;
        } else {
            return false;
        }
    }

    private function generaBollettinoPagoPa($IUV) {
        // carico configurazioni
        $conf = $this->getConfIntermediario();
        $interm = $this->getInfoGetIntermediarioDaIUV($IUV);

        if (!$conf || !$interm) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsParams = array(
            'request' => array(
                'IdApplicazione' => $conf['IDAPPLICAZIONE'],
                'codiceEnteCreditore' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
                'codiceServizio' => str_pad(trim($interm['CODSERVIZIO']), 7, "0", STR_PAD_LEFT),
                'identificativoPosizione' => $IUV
        ));

        $namespaces = array(
            'plug' => self::NAMESPACES_BOLLETTINO_PLUG
        );

        // chiamo ws efill generaBollettino
        $result = $this->callWs($wsParams, 'generaBollettino', $this->isProduzione() ? self::ENDPOINT_BOLLETTINO_PRODUZIONE : self::ENDPOINT_BOLLETTINO_TEST, self::SOAP_ACTION_BOLLETTINO_PREFIX, $namespaces);
        if ($result) {
            return $result['AvvisoPdf'];
        } else {
            return $result;
        }
    }

    /**
     * genera il bollettino per il pagamento
     * 
     * @param array $params
     * @return binary il bollettino oppure false     
     *  
     */
    protected function customGeneraBollettino($params) {
        $iuv = null;
        if ($params['CodiceIdentificativo']) {
            $iuv = $params['CodiceIdentificativo'];
        } else {
            $filtri = array(
                'CODTIPSCAD' => $params['CodTipScad'],
                'SUBTIPSCAD' => $params['SubTipScad'],
                'PROGCITYSC' => $params['ProgCitySc'],
                'ANNORIF' => $params['AnnoRif'],
                'NUMRATA' => $params['NumRata']
            );
            $pendenza = $this->getLibDB_BWE()->leggiBwePenden($filtri);

            if (!$pendenza) {
                $this->handleError(-1, "Pendenza non trovata");
                return false;
            } else if (count($pendenza) > 1) {
                $this->handleError(-1, "Pendenza non univoca");
                return false;
            } else if (count($pendenza) == 1) {
                $pendenza = $pendenza[0];
            }

            // TODO GESTIRE CASO 1 2 CHE STAMPA F24 CLASSICO
            if ($pendenza['FLAG_PUBBL'] == 3 || $pendenza['FLAG_PUBBL'] == 4 || $pendenza['FLAG_PUBBL'] == 5) {
                $agidScadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IDPENDEN' => $pendenza['PROGKEYTAB'], true));
                $iuv = $agidScadenze['IUV'];
            } else {
                $this->handleError(-1, "Errore flag_pubblic non compatibile (" . $pendenza['FLAG_PUBBL'] . ')');
                return null;
            }
        }
        if (!$iuv) {
            $this->handleError(-1, "Errore reperimento CodiceIdentificativo (IUV)");
            return null;
        }

        return $this->generaBollettinoPagoPa($iuv);
    }

    protected function preRimuoviPosizione($scadenza) {
        $conf = $this->getConfIntermediario();

        $wsParams = array(
            'request' => array(
                'IdApplicazione' => $conf['IDAPPLICAZIONE'],
                'CodiceEnte' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
                'CodiceIdentificativo' => $scadenza['IUV']
        ));

        $namespaces = array(
            'plug' => self::NAMESPACES_FEED_PLUG
        );

        // chiamo ws efill generaBollettino
        $result = $this->callWs($wsParams, 'rimuoviPosizione', $this->isProduzione() ? self::ENDPOINT_FEED_PRODUZIONE : self::ENDPOINT_FEED_TEST, self::SOAP_ACTION_FEED_PREFIX, $namespaces);

        if ($result['Esito'] == self::ESITO_POSITIVO) {
            return true;
        }

        return false;
    }

    /**
     * rimuovi una posizione
     * 
     * @param String $IUV
     * @return 
     *  
     */
    protected function customRimuoviPosizione($scadenza) {
        // INSERT DEL RECORD SCARTATO SU BGE_AGID_STOSCA_EFIL PER MANTENERE LO STORICO
        $toInsert = array();
        $toInsert = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
        $where = ' PROGKEYTAB=' . $scadenza['PROGKEYTAB'];
        $progintStoscaEfil = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BGE_AGID_STOSCA_EFIL', $where);
        $toInsert['PROGINT'] = $progintStoscaEfil;
        $this->inserisciBgeAgidStoscaEfil($toInsert);

        $sca_Efil = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($scadenza['PROGKEYTAB']);
        $this->deleteRecord($sca_Efil, 'BGE_AGID_SCA_EFIL', true);
    }

    protected function customEseguiInserimentoSingolo($progkeytabAgidScadenze, $pendenza) {
        $row_Agid_Sca_Efil = array(
            'PROGKEYTAB' => $progkeytabAgidScadenze,
            'NUMAVVISO' => null,
            'ANADEBITORE' => $pendenza['RAGSOC']
        );

        $this->inserisciBgeAgidScaEfil($row_Agid_Sca_Efil);

        // Inserimento su BGE_AGID_SCADET
        $this->inserisciBgeAgidScadet($pendenza['PROGKEYTAB'], $progkeytabAgidScadenze);
    }

    private function eseguiPagamentoPosizioni($scadenza, $urlReturn, $redirectVerticale = 0) {
        // carico configurazioni
        $conf = $this->getConfIntermediario();
        $interm = $this->getInfoGetIntermediarioDaIUV($scadenza['IUV']);

        if (!$redirectVerticale) {
            // aggiungo in mezzo la chiamata a pagopa per allineare i dati su db cityware
            //   $urlOk = $this->getUrlPagamento($scadenza['CODRIFERIMENTO'], $urlOk);
            //    $urlKo = $this->getUrlPagamento($scadenza['CODRIFERIMENTO'], $urlKo, false);
            $urlReturn = $this->getUrlPagamento($scadenza['CODRIFERIMENTO'], $urlReturn);
        }

        $customNamespace = array(
            'CodiceServizio' => 'plug1'
        );

        $namespaces = array(
            'plug' => self::NAMESPACES_PAYMENT_PLUG,
            'plug1' => self::NAMESPACES_PAYMENT_PLUG1
        );

        // il ws funziona passando solo lo iuv, tutti gli altri dati vanno passati finti ma sono obbligatori
        // poi efill li ignora e legge solo lo iuv e i 2 url di ritorno
        $wsParams = array(
            'request' => array(
                'IdApplicazione' => $conf['IDAPPLICAZIONE'],
                'Carrello' => array(
                    'Posizione' => array(
                        'Causale' => 'dato finto',
                        'Creditore' => array(
                            'CodiceEnte' => str_pad(trim($conf['CODENTECRED']), 7, "0", STR_PAD_LEFT),
                            'Intestazione' => 'dato finto'
                        ),
                        'Debitore' => array(
                            'CodiceFiscalePartitaIva' => 111,
                            'Nominativo' => 'dato finto',
                            'TipoPagatore' => 'PersonaFisica'
                        ),
                        'IdentificativoPosizione' => $scadenza['IUV'],
                        'ImportoInCentesimi' => 10, // dato finto (0 non va bene)
                        'ParametriPosizione' => array(),
                        'Servizio' => array(
                            'CodiceServizio' => str_pad(trim($interm['CODSERVIZIO']), 7, "0", STR_PAD_LEFT) // 7 cifre
                        ),
                        'Spontaneo' => false
                    )
                ),
                'UrlBack' => "http://finto.it", // url non più usato ma efill mi obbliga a passarci un url sintatticamente valido
                'UrlReturn' => $urlReturn // url in cui efill fara redirect, concatenando il params esito = KO o OK
        ));

        $result = $this->callWs($wsParams, 'eseguiPagamento', $this->isProduzione() ? self::ENDPOINT_PAYMENT_PRODUZIONE : self::ENDPOINT_PAYMENT_TEST, self::SOAP_ACTION_PAYMENT_PREFIX, $namespaces, $customNamespace);

        return $result;
    }

    protected function customEseguiPagamento($scadenza, $urlReturn, $redirectVerticale = 0) {
        $result = $this->eseguiPagamentoPosizioni($scadenza, $urlReturn, $redirectVerticale);
        return $this->elabEsitoPagopaPagam($result);
    }

//
//    protected function customEseguiPagamento($pendenza, $urlReturn) {
//        if ($pendenza['FLAG_PUBBL'] == 3 || $pendenza['FLAG_PUBBL'] == 4 || $pendenza['FLAG_PUBBL'] == 5) {
//            // pago pa
//            $iuv = $this->recuperaIUV($pendenza['CODTIPSCAD'], $pendenza['SUBTIPSCAD'], $pendenza['ANNORIF'], $pendenza['PROGCITYSC'], $pendenza['NUMRATA']);
//
//            if (!$iuv) {
//                $this->handleError(-1, "Dati disallineati, Codice Identificativo (IUV) non trovato per la pendenza selezionata");
//                return null;
//            }
//            $result = $this->eseguiPagamentoPosizioni($iuv, $urlReturn);
//            return $this->elabEsitoPagopaPagam($result);
//        } else {
//            $this->handleError(-1, "Errore flag_pubblic non compatibile (" . $pendenza['FLAG_PUBBL'] . ')');
//            return null;
//        }
//
//        return null;
//    }

    private function elabEsitoPagopaPagam($result) {
        if (!$result) {
            return null;
        } else {
            $esito = $result['EsitiInvioCarrelloPosizioni']['EsitoInvioCarrelloPosizioni']['Esito'];
            $esitoPositivo = self::ESITO_POSITIVO;
            if ($esito == $esitoPositivo) {
                $url = $result['UrlRedirect'];
                return urlencode($url);
            } else {
                // errore di validazione del ws efill              
                $err = $this->manageEfillErrorMessage($result['EsitiInvioCarrelloPosizioni']['EsitoInvioCarrelloPosizioni']);
                $this->handleError(-1, $err);
                return null;
            }
        }

        return null;
    }

    protected function customRecuperaRicevutaPagamento($iuv, $arricchita) {
        $conf = $this->getConfIntermediario();

        $wsParams = array('request' => array(
                'IdApplicazione' => $conf['IDAPPLICAZIONE'],
                'CodiceEnteCreditore' => str_pad($conf['CODENTECRED'], 7, "0", STR_PAD_LEFT),
                'IdentificativoPosizione' => $iuv
        ));

        $namespaces = array(
            'plug' => self::NAMESPACES_PAYMENT_PLUG
        );

        $result = $this->callWs($wsParams, $arricchita ? 'recuperaRicevutaPagamentoArricchita' : 'recuperaRicevutaPagamento', $this->isProduzione() ? self::ENDPOINT_PAYMENT_PRODUZIONE : self::ENDPOINT_PAYMENT_TEST, self::SOAP_ACTION_PAYMENT_PREFIX, $namespaces);
        return $result;
    }

    protected function isProduzione() {
        $confEfil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
        if (strpos($confEfil['SFTPHOST'], '.integrazione.') == 0 && $confEfil['SFTPHOST']) {
            return true;
        } else {
            return false;
        }
    }

    protected function leggiScadenzePerPubblicazioniTEST($annoemi, $numemi, $idbol_sere) {
        
    }

    protected function customRimuoviPosizioni($idRuolo) {
        
    }

    protected function invioPuntualeScadenzaCustom($progkeytabScadenza) {
        if ($progkeytabScadenza) {
            $filtri['PROGKEYTAB'] = $progkeytabScadenza;
            $bta_servrenddet = $this->getLibDB_BTA()->leggiBtaServrendAccertamentiEmissione($filtri, true);

            try {
                // Leggo i record di BGE_AGID_SCADENZE e BGE_AGID_SCA_EFIL
                $row_Agid_Sca_Efil = $this->getLibDB_BGE()->leggiBgeAgidScaEfilChiave($progkeytabScadenza);
                $row_Agid_scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($progkeytabScadenza);
                //In base ai codici tipo scadenze, reperisco a quale intermediario fa riferimento la scadenza che sto trattando
                $confIntermediario = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());

                // Popolo array 'posizioni' da inviare in seguito
                $posizione = $this->generaArrayPosizioneWsPubblicazioni($row_Agid_scadenze, $row_Agid_Sca_Efil, $confIntermediario, $bta_servrenddet);

                $customData = $this->customEseguiInserimento($posizione);
                if ($customData) {
                    $risposta = $this->customEseguiInserimentoElaboraRisposta($customData);
                } else {
                    try {
                        // aggiorno la scadenza
                        $toUpdate = array();
                        $toUpdate['PROGKEYTAB'] = $row_Agid_scadenze['PROGKEYTAB'];
                        $toUpdate['STATO'] = 3;
                        $toUpdate['NOTESOSP'] = "Errore pubblicazione";
                        $this->aggiornaBgeScadenze($toUpdate);
                    } catch (Exception $exc) {
                        
                    }
                    $this->rispostaPubblicazione($risposta, $row_Agid_scadenze, false, "Errore pubblicazione " . $this->getLastErrorDescription());
                }
            } catch (Exception $exc) {
                $this->rispostaPubblicazione($risposta, $row_Agid_scadenze, false, "Errore " . $exc->getMessage());
            }

            return $risposta;
        } else {
            $log = array(
                "LIVELLO" => 5,
                "OPERAZIONE" => 0,
                "ESITO" => 2,
                "KEYOPER" => 0,
            );
            $this->scriviLog($log);
            $this->handleError(-1, "Nessuna scadenza trovata");
            return false;
        }
    }

    protected function customInserimentoMassivo($scadenze, $inviaBloccoUnico = false) {
        if ($inviaBloccoUnico) {
            // FTP
        } else {
            // insio singolo tramite ws di tutte le scadenze
        }
    }

    protected function leggiScadenzePerInserimento($filtri = array(), $page = null) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $filtri['FLAG_PUBBL_IN'] = array(3, 4, 5);
        if ($page === null) {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenze($filtri);
        } else {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenzeBlocchi($filtri, $page, $this->customPaginatorSize());
        }

        return $scadenze;
    }

    protected function getCodiceSegregazione() {
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $res = $this->getLibDB_BGE()->leggiBgeAgidInterm($filtri);
        return $res['CODSEGREG'];
    }

    protected function getEmissioniPerPubblicazione($filtri = array()) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_TYPE;
        $filtri['FLAG_PUBBL_IN'] = array(3, 4, 5);
        return $this->getLibDB_BGE()->leggiEmissioniDaPubblicare($filtri);
    }

    protected function customCalcoloCodRiferimento($pendenza, $codRiferimento) {
        $modulo93 = $codRiferimento % 93;
        $modulo93 = str_pad($modulo93, 2, "0", STR_PAD_LEFT);
        $codRiferimento = $codRiferimento . $modulo93;

        $numrata = str_pad($pendenza['NUMRATA'], 2, "0", STR_PAD_LEFT);
        $filtri = array(
            "CODTIPSCAD" => $pendenza['CODTIPSCAD'],
            "SUBTIPSCAD" => $pendenza['SUBTIPSCAD'],
            "PROGCITYSC" => $pendenza['PROGCITYSC'],
            "NUMRATA" => $numrata,
            "STATO" => 7,
        );
        $codRiferimentoNew = $this->verificaRipubblicazione($filtri);
        if ($codRiferimentoNew) {
            $codRiferimento = $codRiferimentoNew;
        }

        return $codRiferimento;
    }

    private function verificaRipubblicazione($filtri) {
        $codRiferimentoNew = '';
// select su BGE_AGID_STO_SCADE... chiave CODTIPSCAD, SUBTIPSCAD, PROGCITYSC, ANNORIF, NUMRATA e STATO.
        $scad = $this->getLibDB_BGE()->leggiBgeAgidStoScadeRipubblicazione($filtri);
        if ($scad['MAXCODRIFERIMENTO']) {
            $codRiferimentoNew = $scad['MAXCODRIFERIMENTO'] . '9';
        }

        return $codRiferimentoNew;
    }

    protected function customAgidRiscoSpecifica($progkeytabScade) {
        if (!$progkeytabScade) {
            return null;
        }
        return $this->getLibDB_BGE()->leggiBgeAgidRiscoEfil(array("PROGKEYTAB" => $progkeytabScade), false);
    }

}
