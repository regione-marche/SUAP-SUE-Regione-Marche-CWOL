<?php

require_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
//require_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeFepaUtils.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

/**
 *
 * Classe di utils per la comunicazione con alfresco
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    16.09.2016
 * @link
 * @see
 * 
 */
class itaDocumentaleAlfrescoUtils {

    const DOC_TYPE = 'ALFCITY';
    const ALFRESCO_START_PLACE = '/app:company_home/cm:cityware/';
    const FLUSSO_CP = 'sdi_cp_ricez';
    const FLUSSO_CP_TABLE = 'ffe_cp1_ricez';
    const ANNESSO_CP = 'sdi_cp_annessi';
    const ANNESSO_CP_TABLE = 'ffe_cp5_annessi';
    const FATTURA_CP = 'sdi_cp_fatt';
    const FATTURA_CP_TABLE = 'ffe_cp3_fatt';
    const ACCETTAZIONE_CP = 'sdi_cp_accettaz';
    const RIFIUTO_CP = 'sdi_cp_rifiuto';
    const PROTOCOLLO = 'doc_gen';
    const ERR_INSERT = 'Errore inserimento documento';
    const ERR_UPDATE = 'Errore aggiornamento documento';
    const ERR_UUID_MANCANTE = 'Errore uuid mancante';
    const ERR_IDSDI_MANCANTE = 'Errore idSdi mancante';
    const ERR_FLUSSO_SPACCHETTATO = 'Errore flusso già spacchettato';
    const ERR_FLUSSO_DOPPIO = 'Errore flusso doppio';
    const ERR_FLUSSO_NON_TROVATO = 'Errore flusso non trovato';
    const ERR_FATTURA_NON_TROVATA = 'Errore fattura non trovata';
    const ERR_FATTURA_ELABORATA = 'Errore fattura già elaborata';
    const ERR_ELABORAZIONE = 'Errore elaborazione';
    const ERR_ID_SDI_MANCANTE = 'Errore identificativo SDI mancante';
    const CITYWARE_DB = 'CITYWARE';
    const JSON_DECODE_PATH = '/lib/itaPHPAlfcity/cwbAlfrescoViewer.decode.json';

    private $documentale;
    private $dizionario;
    //private $omnisClient;
    private $cwbBgeFepaUtils;
    private $errMessage;
    private $result;
    private $codiceEnte;
    private $codiceAoo;
    private $descrizioneEnte;
    private $codiceUtente;
    private $dbCw;
    private $UuidFatturaPadre;
    private $alfrescoTypes;

    public function __construct() {
        $this->documentale = new itaDocumentale(self::DOC_TYPE);
        // $this->omnisClient = new itaOmnisClient();
        //$this->omnisClient->setParametersStatic();
        $this->cwbBgeFepaUtils = new cwbBgeFepaUtils();

        $this->dizionario = null;
        $this->codiceEnte = ''; // TODO dove li prendo?? 042002
        $this->codiceAoo = ''; // TODO dove li prendo?? atdaa
        $this->descrizioneEnte = ''; // TODO dove li prendo?? Ente ata
        $this->codiceUtente = ''; // TODO dove li prendo?? CED
        $this->UuidFatturaPadre = ''; // TODO dove li prendo?? CED

        $this->dbCw = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
    }

    public function getDizionario() {
        return $this->dizionario;
    }

    public function setDizionario($dizionario) {
        $this->dizionario = $dizionario;
    }

    public function getCodiceEnte() {
        return $this->codiceEnte;
    }

    public function setCodiceEnte($codiceEnte) {
        $this->codiceEnte = $codiceEnte;
    }

    public function getCodiceAoo() {
        return $this->codiceAoo;
    }

    public function setCodiceAoo($codiceAoo) {
        $this->codiceAoo = $codiceAoo;
    }

    public function getDescrizioneEnte() {
        return $this->descrizioneEnte;
    }

    public function setDescrizioneEnte($descrizioneEnte) {
        $this->descrizioneEnte = $descrizioneEnte;
    }

    public function getCodiceUtente() {
        return $this->codiceUtente;
    }

    public function setCodiceUtente($codiceUtente) {
        $this->codiceUtente = $codiceUtente;
    }

    public function getUuidFatturaPadre() {
        return $this->UuidFatturaPadre;
    }

    public function setUuidFatturaPadre($UuidFatturaPadre) {
        $this->UuidFatturaPadre = $UuidFatturaPadre;
    }

    /*
     * Inserisce un flusso di ciclo attivo su alfresco ed allinea i metadati nella tabella ffe_cp1_ricez
     * 
     * @param String $fileName Nome del file da inserire
     * @param binary $content Documento da inserire
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function inserisciFlussoCP($fileName, $content) {
        try {
            $this->result = null;
            $this->errMessage = null;

            // da dizionario TODO
            $aspects = array(
                'asp_prot' => 1,
                'asp_fasc' => 0,
                'asp_com' => 1,
            );

            // Data Acquisizione->Data Protocollo.
            $dt = date("d-m-Y", strtotime($this->dizionario['DATA']));
            // metadati
            $props = array(
                'codfiscale_fornitore' => $this->dizionario['CODICEFISCALE'], // da dizionario TODO
                'codice_destinatario' => $this->dizionario['CODICEDESTINATARIO'], // da dizionario TODO
                'data_acquisizione' => '*DATE*' . $dt, // da dizionario TODO
                'idfiscale_fornitore' => $this->dizionario['IDFISCALEIVA'], // da dizionario TODO
                'id_sdi' => $this->dizionario['IDENTIFICATIVOSDI'], // da dizionario TODO
                'modo_inserimento' => '1', // 1 = da pec
                'nome_flusso' => $fileName,
                //      'note_flusso' => ' ', // vuoto (lasciare uno spazio)
                'num_fatture_accettate' => '0', // all'inserimento sempre 0
                'num_fatture_accettate_dt' => '0', // all'inserimento sempre 0
                'num_fatture' => $this->dizionario['TOTFATTURE'],
                'num_fatture_rifiutate' => '0', // all'inserimento sempre 0
                'posizione_flusso' => '0', // all'inserimento sempre 0 (da accettare/rifiutare)
                'ragione_soc_fornitore' => $this->dizionario['FORNITORE_DENOMINAZIONE'], // da dizionario TODO
                'stato_flusso' => '0', // all'inserimento sempre 0 (da elaborare - non spacchettato)
                //     'uuid_collegato' => ' ', // vuoto (lasciare uno spazio)
                'versione_flusso' => $this->dizionario['VERSIONEFLUSSO'], // da dizionario TODO 
                //
                // aspetto protocollazione  
                //
                'prot_anno' => $this->dizionario['ANNO'], // da dizionario TODO
                'prot_data' => '*DATE*' . $dt, // da dizionario TODO
                //    'prot_destinatario' => ' ', // non usare su flusso
                'prot_mittente' => 'SDI', // fisso
                'prot_numero' => $this->dizionario['NUMERO'], // da dizionario TODO
                'prot_oggetto' => $this->dizionario['OGGETTO'], // da dizionario TODO
                'prot_riservato' => '*BOOL*' . $this->dizionario['RISERVATO'], // da dizionario TODO
                'prot_tipo' => $this->dizionario['TIPO'], // In inserimento flusso ciclo passivo sempre A (arrivo)
                //aspetto dati comuni            
                'com_aoo' => $this->codiceAoo,
                'com_area_cityware' => 'F', // fisso
                'com_codice_ipa' => $this->codiceAoo,
                'com_descrizione' => $fileName,
                'com_ente' => $this->codiceEnte,
                'com_modulo_cityware' => 'ES', // fisso
                'com_nomefile' => $fileName,
                'com_organigramma_corrente' => $this->descrizioneEnte,
                'com_ruolo_corrente' => $this->dizionario['RUOLO'], // mettere il ruolo dell'utente corrente TODO
                'com_utente_login' => $this->codiceUtente
                    // aspetto fascicolazione non usato per ora            
                    // 'fasc_uuid' => ''     // | per multivalue          
            );

            $mimeType = itaMimeTypeUtils::getMimeTypes(itaMimeTypeUtils::estraiEstensione($fileName));
            // inserisce documento su documentale
            if ($this->documentale->insertDocument(self::FLUSSO_CP, $this->calcPlace(self::FLUSSO_CP), $fileName, $mimeType, $content, $aspects, $props)) {
                $this->result = $this->documentale->getResult();

                if (!$this->result) {
                    $this->errMessage = self::ERR_INSERT;

                    return false;
                } else {
                    $props['uuid'] = $this->result;
                    $this->allineaMetadati(self::FLUSSO_CP_TABLE, array_merge($props, $aspects));

                    return true;
                }
            } else {
                $this->errMessage = $this->documentale->getErrMessage();

                return false;
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();

            return false;
        }

        return true;
    }

    /*
     * Inserisce un annesso di ciclo attivo su alfresco ed allinea i metadati nella tabella ffe_cp5_annessi
     * 
     * @param String $fileName Nome del file da inserire
     * @param binary $content Documento da inserire
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function inserisciAnnessoCP($fileName, $content) {
        try {
            $this->result = null;
            $this->errMessage = null;

            // da dizionario
            $aspects = array(
                'asp_ger' => 1,
                'asp_ud' => 1
            );

            // metadati
            $props = array(
                'desc_annesso' => $fileName,
                'id_sdi' => $this->dizionario['IDENTIFICATIVOSDI'], // da dizionario TODO
                'nome_annesso' => $fileName,
                'tipo_annesso' => $this->dizionario['TIPOANNESSO'], // da dizionario TODO
                // aspetto gerarchia
                'ger_uuid_padre' => $this->dizionario['UUIDPADRE'], // da dizionario TODO
                // aspetto unità documentaria
                'id_unita_doc' => $this->dizionario['IDUNITADOC'] // da dizionario TODO
            );

            $mimeType = itaMimeTypeUtils::getMimeTypes(itaMimeTypeUtils::estraiEstensione($fileName));
            // inserisce documento su documentale
            if ($this->documentale->insertDocument(self::ANNESSO_CP, $this->calcPlace(self::ANNESSO_CP), $fileName, $mimeType, $content, $aspects, $props)) {
                $this->result = $this->documentale->getResult();

                if (!$this->result) {
                    $this->errMessage = self::ERR_INSERT;

                    return false;
                } else {
                    $props['uuid'] = $this->result;
                    $this->allineaMetadati(self::ANNESSO_CP_TABLE, array_merge($props, $aspects));

                    return true;
                }
            } else {
                $this->errMessage = $this->documentale->getErrMessage();

                return false;
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();

            return false;
        }

        return true;
    }

    /*
     * Inserisce un protocollo su alfresco 
     * 
     * @param String $fileName Nome del file da inserire
     * @param binary $content Documento da inserire
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function inserisciProtocollo($fileName, $content) {
        try {
            $this->result = null;
            $this->errMessage = null;

            // da dizionario
            // da dizionario TODO
            $aspects = array(
                'asp_prot' => 1,
                'asp_fasc' => 0,
                'asp_com' => 1,
                'asp_sos_ud' => 1,
                'asp_ud' => 1
            );

            $dt = date("d-m-Y", strtotime($this->dizionario['DATA']));
            // metadati
            $props = array(
                'descrizione_documento' => $fileName,
                'titolo_documento' => $fileName,
                // aspetto protocollazione            
                'prot_anno' => $this->dizionario['ANNO'], // da dizionario TODO
                'prot_data' => '*DATE*' . $dt, // da dizionario TODO
                'prot_destinatario' => $this->dizionario['DESTINATARIO'], // da dizionario TODO
                'prot_mittente' => $this->dizionario['MITTENTE'], // da dizionario TODO
                'prot_numero' => $this->dizionario['NUMERO'], // da dizionario TODO
                'prot_oggetto' => $this->dizionario['OGGETTO'], // da dizionario TODO
                'prot_riservato' => '*BOOL*' . $this->dizionario['RISERVATO'], // da dizionario TODO
                'prot_tipo' => $this->dizionario['TIPO'], // da dizionario TODO
                //aspetto dati comuni            
                'com_aoo' => $this->codiceAoo,
                'com_area_cityware' => 'A', // fisso
                'com_codice_ipa' => $this->codiceAoo,
                'com_descrizione' => $fileName,
                'com_ente' => $this->codiceEnte,
                'com_modulo_cityware' => 'PI', // fisso
                'com_nomefile' => $fileName,
                'com_organigramma_corrente' => $this->descrizioneEnte,
                'com_ruolo_corrente' => $this->dizionario['RUOLO'], // mettere il ruolo dell'utente corrente TODO
                'com_utente_login' => $this->codiceUtente,
                    // archiviazione sostitutiva
                    //    'sost_ud_k_anno' => '', 
                    //    'sost_ud_k_numero' => '',
                    //    'sost_ud_k_tiporeg' => '', 
                    // aspetto fascicolazione non usato per ora            
                    //       'fasc_uuid' => '345-rfg-345-34|asdsd-34-da-34'     // | per multivalue  
            );

            $mimeType = itaMimeTypeUtils::getMimeTypes(itaMimeTypeUtils::estraiEstensione($fileName));
            // inserisce documento su documentale
            if ($this->documentale->insertDocument(self::PROTOCOLLO, $this->calcPlace(self::PROTOCOLLO), $fileName, $mimeType, $content, $aspects, $props)) {
                $this->result = $this->documentale->getResult();

                if (!$this->result) {
                    $this->errMessage = self::ERR_INSERT;

                    return false;
                } else {
                    return true;
                }
            } else {
                $this->errMessage = $this->documentale->getErrMessage();

                return false;
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();

            return false;
        }

        return true;
    }

    /*
     * passato l'uuid del flusso, spacchetta le sue fatture
     */

    public function spacchettaFlusso($uuidFlusso) {
        try {
            $this->result = null;
            $this->errMessage = null;

            if (!$uuidFlusso) {
                $this->errMessage = self::ERR_UUID_MANCANTE;

                return false;
            }

            if ($this->documentale->queryByUUID($uuidFlusso)) {
                $result = $this->estraiRisultato($this->documentale->getResult());
                return $this->spacchetta($result['RESULT'][0]);
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();
        }

        return false;
    }

    /*
     * spacchetta tutte le fatture di tutti i flussi ancora da spacchettare
     */

    public function spacchettaTuttiIFlussi() {
        $toReturn = array();
        try {
            $this->result = null;
            $this->errMessage = null;

            $props = array(
                'stato_flusso' => 0
            );

            // cerco tutti i flussi da spacchettare
            if ($this->documentale->query(self::FLUSSO_CP, $this->codiceEnte, $this->codiceAoo, array(), $props)) {
                $this->result = array();
                $result = $this->estraiRisultato($this->documentale->getResult());

                foreach ($result['RESULT'] as $value) {
                    // scorro i singoli flussi e li spacchetto
                    $res = array();

                    $res['UUID_FLUSSO'] = $value['UUID'][0]['@textNode'];
                    if ($this->spacchetta($value)) {
                        $res['ERRORE'] = 0;
                        // il metodo spacchetta popola this->result per ogni flusso spacchettato
                        $res['RISULTATO'] = $this->getResult();
                    } else {
                        $res['ERRORE'] = 1;
                        $res['MESSAGGIO_ERR'] = $this->getErrMessage();
                    }
                    $toReturn[] = $res;
                    $this->result = null;
                }
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();
        }

        // alla fine pulisco result dai singoli spacchettamenti e lo ripopolo con i totali
        $this->result = array();
        $this->result = $toReturn;

        return true;
    }

    private function spacchetta($flussoMetadata) {
        $metadata = $flussoMetadata['COLUMNS'][0]['COLUMN'];

        $uuid = $flussoMetadata['UUID'][0]['@textNode'];

        if ($metadata) {
            foreach ($metadata as $value) {
                if ($statoFlusso !== null && $idSdi !== null) {
                    break; // se li ho trovati tutti e 2 esco dal ciclo per fare prima
                }

                if ($value['NAME'][0]['@textNode'] === 'stato_flusso') {
                    $statoFlusso = $value['VALUE'][0]['@textNode'];
                } else if ($value['NAME'][0]['@textNode'] === 'id_sdi') {
                    $idSdi = $value['VALUE'][0]['@textNode'];
                }
            }
        }

        if ($statoFlusso === null) {
            // flusso già spacchettato
            $this->errMessage = self::ERR_FLUSSO_NON_TROVATO;

            return false;
        }

        if ($statoFlusso != 0) {
            // flusso già spacchettato
            $this->errMessage = self::ERR_FLUSSO_SPACCHETTATO;

            return false;
        }

        if ($this->controllaFlussiDoppi($idSdi)) {
            // stesso flusso inviato 2 volte da sdi
            $this->errMessage = self::ERR_FLUSSO_DOPPIO;

            return false;
        }

        // result o errMessage vengono settati dentro il metodo fepaFrazionaFlussoOmnis
        return $this->fepaFrazionaFlussoOmnis($uuid);
    }

    /*
     * Richiama Omnis Server per eseguire l'accettazione di una fattura
     * 
     * @param String $uuidFattura id della fattura da accettare
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function accettaFattura($uuidFattura) {
        return $this->accettaRifiutaFattura($uuidFattura, true);
    }

    /*
     * Richiama Omnis Server per eseguire il rifiuto di una fattura
     * 
     * @param String $uuidFattura id della fattura da rifiutare
     * @param String $motivo motivo rifiuto
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function rifiutaFattura($uuidFattura, $motivo) {
        return $this->accettaRifiutaFattura($uuidFattura, false, $motivo);
    }

    private function accettaRifiutaFattura($uuidFattura, $accettazione, $motivo = '') {
        $this->result = null;
        $this->errMessage = null;

        if (!$uuidFattura) {
            $this->errMessage = self::ERR_UUID_MANCANTE;

            return false;
        }

        if ($this->documentale->queryByUUID($uuidFattura)) {
            $fattura = $this->estraiRisultato($this->documentale->getResult());
            $fattura = $fattura['RESULT'][0]['COLUMNS'][0]['COLUMN'];

            if ($fattura) {
                foreach ($fattura as $value) {
                    if ($value['NAME'][0]['@textNode'] === 'stato_fattura') {
                        $statoFattura = $value['VALUE'][0]['@textNode'];
                        break;
                    }
                }
            }

            if ($statoFattura === null) {
                // fattura non trovata
                $this->errMessage = self::ERR_FATTURA_NON_TROVATA;

                return false;
            }

            if ($statoFattura != 0) {
                // fattura gia elaborata
                $this->errMessage = self::ERR_FATTURA_ELABORATA;

                return false;
            }

            if ($this->fepaEsitoCommittenteOmnis($uuidFattura, $accettazione, $motivo)) {
                try {
                    $props = array(
                        'ger_uuid_padre' => $uuidFattura
                    );

                    // cerco l'accettazione/rifiuto generato
                    if ($this->documentale->query($accettazione ? self::ACCETTAZIONE_CP : self::RIFIUTO_CP, $this->codiceEnte, $this->codiceAoo, array(), $props)) {
                        $result = $this->estraiRisultato($this->documentale->getResult());
                        $uuid = $result['RESULT'][0]['UUID'][0]['@textNode'];
                        if ($uuid) {
                            $this->result = $uuid;
//                            if ($this->documentale->contentByUUID($uuid)) {
//                                $this->result = $this->documentale->getResult();
//                            }
                        }
                    }
                } catch (Exception $ex) {
                    
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /*
     * Dopo aver spacchettato un flusso, se si decide di protocollare le singole fatture, 
     * vanno aggiunti i metadati di protocollazione
     * 
     * @param String $uuidFattura id della fattura protocollata
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiungiMetadatiProtocollazioneSuFattura($uuidFattura) {
        try {
            $this->result = null;
            $this->errMessage = null;

            $aspects = array(
                'asp_prot' => 1
            );

            // Data Acquisizione->Data Protocollo.
            $dt = date("d-m-Y", strtotime($this->dizionario['DATA']));
            // metadati
            $props = array(
                //
                // aspetto protocollazione  
                //
                'prot_anno' => $this->dizionario['ANNO'], // da dizionario TODO
                'prot_data' => '*DATE*' . $dt, // da dizionario TODO
                //    'prot_destinatario' => ' ', // non usare su flusso
                'prot_mittente' => 'SDI', // fisso
                'prot_numero' => $this->dizionario['NUMERO'], // da dizionario TODO
                'prot_oggetto' => $this->dizionario['OGGETTO'], // da dizionario TODO
                'prot_riservato' => '*BOOL*' . $this->dizionario['RISERVATO'], // da dizionario TODO
                'prot_tipo' => $this->dizionario['TIPO'], // In inserimento flusso ciclo passivo sempre A (arrivo)    
            );

            // inserisce documento su documentale
            if ($this->documentale->updateDocumentMetadata($uuidFattura, self::FATTURA_CP, $aspects, $props)) {
                $this->result = $this->documentale->getResult();

                if (!$this->result) {
                    $this->errMessage = self::ERR_INSERT . ' ' . $this->documentale->getErrMessage();

                    return false;
                } else {
                    $props['uuid'] = $uuidFattura;
                    $this->allineaMetadati(self::FATTURA_CP_TABLE, array_merge($props, $aspects), false);

                    return true;
                }
            } else {
                $this->errMessage = $this->documentale->getErrMessage();

                return false;
            }
        } catch (Exception $ex) {
            $this->errMessage = $ex->getMessage();

            return false;
        }

        return true;
    }

    /*
     * Dopo aver spedito una risposta (accetta/rifiuta), aggiorna il metadato stato_fattura (inviato)
     * su alfresco e sul db nella tabella ffe_cp3_fatt
     * 
     * @param String $uuidFattura id della fattura da accettare
     * @param boolean $accettazione true se è stata inviata un'accettazione, false per il rifiuto
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaMetadatiInvioEsitoFattura($uuidFattura, $accettazione) {
        $this->result = null;
        $this->errMessage = null;

        if (!$uuidFattura) {
            $this->errMessage = self::ERR_UUID_MANCANTE;

            return false;
        }

        if ($accettazione) {
            $props['stato_fattura'] = 5; // accettata e inviata
        } else {
            $props['stato_fattura'] = 6; // rifiutata e inviata
        }

        if ($this->documentale->updateDocumentMetadata($uuidFattura, self::FATTURA_CP, array(), $props)) {
            $this->result = $this->documentale->getResult();

            $props['uuid'] = $uuidFattura;
            $this->allineaMetadati(self::FATTURA_CP_TABLE, $props, false);

            return true;
        } else {
            $this->errMessage = $this->documentale->getErrMessage();

            return false;
        }

        return false;
    }

    /*
     * Se arriva una decorrenza termini, aggiorna il metadato stato_fattura = 7 (decorrenza termini)
     * su alfresco e sul db nella tabella ffe_cp3_fatt e i metadati 
     * posizione_flusso,num_fatture_accettate_dt,num_fatture_accettate su sdi_cp_ricez
     * 
     * @param String $idSdi idsdi preso dall'xml dt
     * @param String $numFattura se il dt è solo per una fattura prendere numFattura dall'xml se invece riguarda tutte le fatture del flusso lasciarlo null (tag RiferimentoFattura/NumeroFattura)
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaMetadatiRicezioneDTCP($idSdi, $numFattura = null) {
        $this->result = null;
        $this->errMessage = null;

        if (!$idSdi) {
            $this->errMessage = self::ERR_IDSDI_MANCANTE;

            return false;
        }

        // cerco il flusso corrispondente
        $props = array(
            'id_sdi' => $idSdi
        );

        if (!$this->documentale->query(self::FLUSSO_CP, $this->codiceEnte, $this->codiceAoo, array(), $props)) {
            $this->errMessage = self::ERR_FLUSSO_NON_TROVATO . ' ' . $this->documentale->getErrMessage();
            return false;
        }

        $result = $this->estraiRisultato($this->documentale->getResult());
        $result = $result['RESULT'];

        if (count($result) != 1) {
            $this->errMessage = self::ERR_FLUSSO_DOPPIO;
            return false;
        }
        $result = $result[0];

        foreach ($result['COLUMNS'][0]['COLUMN'] as $col) {
            if ($col['NAME'][0]['@textNode'] == 'num_fatture_rifiutate') {
                $fattureRifiutate = $col['VALUE'][0]['@textNode'];
            } else if ($col['NAME'][0]['@textNode'] == 'num_fatture') {
                $fatturePresenti = $col['VALUE'][0]['@textNode'];
            } else if ($col['NAME'][0]['@textNode'] == 'num_fatture_accettate') {
                $fattureAccettate = $col['VALUE'][0]['@textNode'] ? $col['VALUE'][0]['@textNode'] : 0;
            } else if ($col['NAME'][0]['@textNode'] == 'num_fatture_accettate_dt') {
                $fattureAccettateDt = $col['VALUE'][0]['@textNode'] ? $col['VALUE'][0]['@textNode'] : 0;
            }

            if ($fattureRifiutate && $fatturePresenti && $fattureAccettate && $fattureAccettateDt) {
                break;
            }
        }

        if ($numFattura) {
            // se il dt è per una sola fattura faccio + 1 su fatture accettate
            $fattureAccettate++;
            $fattureAccettateDt++;
        } else {
            // se il dt è per tutte allora accetto tutte le fatture presenti
            $fattureRifiutate = 0;
            $fattureAccettate = $fatturePresenti;
            $fattureAccettateDt = $fatturePresenti;
        }

        // aggiorno il flusso trovato
        $uuidFlusso = $result['UUID'][0]['@textNode'];

        $props = array(
            'num_fatture_accettate_dt' => $fattureAccettateDt,
            'num_fatture_accettate' => $fattureAccettate,
            'num_fatture_rifiutate' => $fattureRifiutate
        );

        if ($fatturePresenti == ($fattureAccettate + $fattureRifiutate)) {
            $props['posizione_flusso'] = 1;
        }

        if (!$this->documentale->updateDocumentMetadata($uuidFlusso, self::FLUSSO_CP, array(), $props)) {
            $this->errMessage = $this->documentale->getErrMessage();
            return false;
        }
        $this->allineaMetadati(self::FLUSSO_CP_TABLE, $props, false);

        // cerco le fatture presenti nel flusso
        $props = array(
            'ger_uuid_padre' => $uuidFlusso
        );
        // se il dt è solo per una fattura cerco quella specifica, senno' le cerco tutte
        if ($numFattura) {
            $props['numero_documento'] = $numFattura;
        }

        if (!$this->documentale->query(self::FATTURA_CP, $this->codiceEnte, $this->codiceAoo, array(), $props)) {
            $this->errMessage = self::ERR_FATTURA_NON_TROVATA . ' ' . $this->documentale->getErrMessage();
            return false;
        }

        $resultFat = $this->estraiRisultato($this->documentale->getResult());
        $resultFat = $resultFat['RESULT'];

        if (!$resultFat) {
            $this->errMessage = self::ERR_FATTURA_NON_TROVATA;
            return false;
        }

        $errori = '';
        foreach ($resultFat as $fattura) {
            $uuidFattura = $fattura['UUID'][0]['@textNode'];

            $props = array(
                'stato_fattura' => 7
            );

            if (!$this->documentale->updateDocumentMetadata($uuidFattura, self::FATTURA_CP, array(), $props)) {
                $errori .= 'Errore fattura:' . $uuidFattura . ' ';
            } else {
                $this->allineaMetadati(self::FATTURA_CP_TABLE, $props, false);
            }
        }

        if ($errori) {
            $this->errMessage = $errori;
            return false;
        }

        return true;
    }

    private function calcPlace($doc_model) {
        require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $basePath = $devLib->getEnv_config('ALFCITY', 'codice', 'ALFRESCO_BASEPLACE', false);
        $place = $basePath['CONFIG'];

        switch ($doc_model) {
            case self::FLUSSO_CP:
            case self::ANNESSO_CP:
                $place = $place . 'cm:base/cm:fepa-in';

                break;
            case self::PROTOCOLLO:
                $place = $place . 'cm:media/cm:protocollo';

                break;
            default:
                $place = $place . 'cm:media/cm:protocollo';

                break;
        }

        return $place;
    }

    /*
     * se su alfresco c'è un altro flusso già spacchettato con lo stesso id_sdi, 
     * significa che è arrivato un flusso doppio (caso di stesso flusso inviato 2 volte per errore da sdi)
     * 
     * return true se flusso doppio
     */

    private function controllaFlussiDoppi($idSdi) {
        try {
            if ($idSdi) {
                $props = array(
                    'id_sdi' => $idSdi,
                    'stato_flusso' => 1
                );

                if ($this->documentale->query(self::FLUSSO_CP, $this->codiceEnte, $this->codiceAoo, array(), $props)) {
                    $result = $this->estraiRisultato($this->documentale->getResult());

                    if ($result['RESULT']) {
                        return true; // flusso doppio
                    }
                }
            }
        } catch (Exception $ex) {
            // se c'è qualche errore nel dubbio torno false e lo spacchetto (meglio averlo doppio che per niente)
        }

        return false;
    }

    /*
     * Spacchetta i flussi tramite omnis
     */

    private function fepaFrazionaFlussoOmnis($uuidFlusso) {
        $methodArgs = array();
        $methodArgs[0] = $uuidFlusso;
        $methodArgs[1] = $this->codiceUtente;
        $methodArgs[2] = $this->descrizioneEnte;

        $result = $this->cwbBgeFepaUtils->fepa_fraziona_flusso($methodArgs);
        if ($result['RESULT']['EXITCODE'] === null || $result['RESULT']['EXITCODE'] === 'N') {
            $this->errMessage = $result['RESULT']['MESSAGE'];

            return false;
        } else {
            $this->result = array();
            $rows = $result['RESULT']['LIST']['ROW'];
            if (array_key_exists("UUID", $rows)) {
                // caso di singola fattura
                $this->result[] = $rows['UUID'];
            } else {
                // caso di flusso con n fatture
                foreach ($rows as $value) {
                    $this->result[] = $value['UUID'];
                }
            }

            return true;
        }
    }

    /*
     * accetta/rifiuta le fatture tramite omnis
     */

    private function fepaEsitoCommittenteOmnis($uuidFattura, $accettazione, $motivoRifiuto = '') {
        // chiamata ad omnis
        $methodArgs = array();
        $methodArgs[0] = $uuidFattura;
        $methodArgs[1] = $accettazione ? 1 : 2;
        $methodArgs[2] = $motivoRifiuto;
        $methodArgs[3] = $this->codiceUtente;
        $methodArgs[4] = $this->descrizioneEnte;

        $result = $this->cwbBgeFepaUtils->fepa_esito_committente($methodArgs);
        if ($result['RESULT']['EXITCODE'] === null || $result['RESULT']['EXITCODE'] === 'N') {
            $this->errMessage = $result['RESULT']['MESSAGE'];

            return false;
        } else {
            // non torna niente.
            return true;
        }
    }

    private function estraiRisultato($queryResult) {
        if ($queryResult['QUERYRESULT'] && $queryResult['QUERYRESULT'][0]['RESULTS']) {
            return $queryResult['QUERYRESULT'][0]['RESULTS'][0];
        }

        return null;
    }

    /*
     * allinea i metadati su db
     * 
     */

    private function allineaMetadati($tableName, $data, $toInsert = true) {
        try {
            foreach ($data as $key => $value) {
                if (0 === strpos($value, '*DATE*')) {
                    $data[$key] = str_replace("*DATE*", "", $value);
                    $data[$key] = date("Y-m-d", strtotime($data[$key]));
                } else if (0 === strpos($value, '*BOOL*')) {
                    $data[$key] = str_replace("*BOOL*", "", $data[$key]);
                }
            }

            $modelService = itaModelServiceFactory::newModelService($this->modelNameByTableName($tableName));

            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($tableName, $data);

            if ($toInsert) {
                $modelService->insertRecord($this->dbCw, $tableName, $modelServiceData->getData(), 'Allineamento metadati');
            } else {
                $modelService->updateRecord($this->dbCw, $tableName, $modelServiceData->getData(), 'Allineamento metadati');
            }
        } catch (Exception $ex) {
            // se va in errore vado avanti uguale                       
            error_log('******* Errore allineamento metadati documentaleAlfrescoUtils ' . $ex->getMessage());
        }
    }

    private function modelNameByTableName($tableName) {
        return "cw" . strtolower(substr($tableName, 0, 1)) . ucfirst(strtolower(substr($tableName, 0, 3))) . str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', substr($tableName, 4))))); // camelcase di tutte le parole spezzate da _ e poi rimuove _ con vuoto
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getResult() {
        return $this->result;
    }

    private function readAlfrescoTypes() {
        if (!isSet($this->alfrescoTypes)) {
            $this->alfrescoTypes = json_decode(file_get_contents(ITA_BASE_PATH . self::JSON_DECODE_PATH), true);
        }
    }

    public function findParent($uuid) {
        $return = null;

        if (!$this->documentale->queryByUUID($uuid)) {
            return null;
        }

        $document = $this->documentale->getResult();

        foreach ($document['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'] as $metadata) {
            if ($metadata['NAME'][0]['@textNode'] == 'ger_uuid_padre') {
                $return = trim($metadata['VALUE'][0]['@textNode']);
                break;
            }
        }

        return $return;
    }

    public function findChildren($uuid) {
        $return = array();

        $this->readAlfrescoTypes();
        $this->documentale->queryByUUID($uuid);

        $doc = $this->documentale->getResult();
        if (isSet($doc['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0])) {
            preg_match('/^{.*?}([A-Za-z0-9_]*)_type$/', $doc['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['TYPE'][0]['@textNode'], $matches);
            $type = strtoupper($matches[1]);

            foreach ($this->alfrescoTypes[$type]['CHILDREN'] as $childType) {
                $this->documentale->query($childType, cwbParGen::getCodente(), cwbParGen::getCodAoo(), array(), array('ger_uuid_padre' => $uuid));

                $children = $this->documentale->getResult();
                if (isSet($children['QUERYRESULT'][0]['RESULTS'][0]['RESULT'])) {
                    foreach ($children['QUERYRESULT'][0]['RESULTS'][0]['RESULT'] as $row) {
                        $return[] = $row['UUID'][0]['@textNode'];
                    }
                }
            }
        }

        return $return;
    }

    private function buildDocumentInfo($uuid) {
        $return = array(
            'UUID' => $uuid,
            'CHILDREN' => array()
        );

        $children = $this->findChildren($uuid);
        foreach ($children as $child) {
            $return['CHILDREN'][] = $this->buildDocumentInfo($child);
        }

        return $return;
    }

    public function buildAlfrescoHierarchy($uuid) {
        $tp = $parent = $uuid;
        while ($tp != null) {
            $tp = $this->findParent($parent);
            if ($tp != null) {
                $parent = $tp;
            }
        }

        return $this->buildDocumentInfo($parent);
    }

    /*
     * Aggiorno le informazioni relative all'ID SDI nella Fattura
     * 
     * @param String $uuidFattura id della fattura 
     * @param boolean $id_Sdi identificativo sdi 
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaIDSdiFattura($uuidFattura, $id_Sdi) {
        $this->result = null;
        $this->errMessage = null;

        if (!$uuidFattura) {
            $this->errMessage = self::ERR_UUID_MANCANTE;

            return false;
        }

        if (!$id_Sdi) {
            $this->errMessage = self::ERR_ID_SDI_MANCANTE;
            return false;
        }

        $props['id_sdi'] = $id_Sdi;

        if ($this->documentale->updateDocumentMetadata($uuidFattura, self::FATTURA_CP, array(), $props)) {
            $this->result = $this->documentale->getResult();

            $props['uuid'] = $uuidFattura;
            $this->allineaMetadati(self::FATTURA_CP_TABLE, $props, false);

            return true;
        } else {
            $this->errMessage = $this->documentale->getErrMessage();

            return false;
        }

        return false;
    }

    /*
     * Aggiorno le informazioni relative all'ID SDI nel flusso
     * 
     * @param String $uuidFlusso id del flusso 
     * @param boolean $id_Sdi identificativo sdi 
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaIDSdiFlusso($uuidFlusso, $id_Sdi) {
        $this->result = null;
        $this->errMessage = null;

        if (!$uuidFlusso) {
            $this->errMessage = self::ERR_UUID_MANCANTE;
            return false;
        }
        if (!$id_Sdi) {
            $this->errMessage = self::ERR_ID_SDI_MANCANTE;
            return false;
        }

        $props['id_sdi'] = $id_Sdi;

        if ($this->documentale->updateDocumentMetadata($uuidFlusso, self::FLUSSO_CP, array(), $props)) {
            $this->result = $this->documentale->getResult();

            $props['uuid'] = $uuidFlusso;
            $this->allineaMetadati(self::FLUSSO_CP_TABLE, $props, false);

            return true;
        } else {
            $this->errMessage = $this->documentale->getErrMessage();

            return false;
        }

        return false;
    }

}

?>