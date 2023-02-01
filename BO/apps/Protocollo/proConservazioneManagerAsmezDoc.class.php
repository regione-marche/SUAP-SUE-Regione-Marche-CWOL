<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    proConservazioneManagerAsmezDoc
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    17.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManager.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';

class proConservazioneManagerAsmezDoc extends proConservazioneManager {

    const CLASSE_PARAMETRI = 'ASMEZDOC';
    const Versione = '1.0.2';
    const ApplicazioneNome = 'Conservazione';
    const ApplicazioneProduttore = 'Asmenet Soc. Cons. a r.l.';

    /*
     * Keyes Base Dati/ Dizionario ASMEZ Elaborata 
     */
    const K_VERSIONE = 'VERSIONE';
    const K_LOGINNAME = 'LOGINNAME';
    const K_PASSWORD = 'PASSWORD';
    const K_IDVERSAMENTO = 'K_IDVERSAMENTO';
    const K_ENTE = 'ENTE';
    const K_CFENTE = 'CFENTE';
    const K_KEYENTE = 'CHIAVEENTE';
    const K_URLSERVIZIO = 'URLSERVIZIO';

    /* Keys Principali */
    const K_METADATICLASSID = 'METADATICLASSID';
    const K_METADATICLASSNAME = 'METADATICLASSNAME';
    const K_TIPODOCUMENTO = 'TIPODOCUMENTO';
    const K_DATETIMEVERSAMENTO = 'DATETIMEVERSAMENTO';

    /* Keys componente: Generiche */
    const K_IDCOMPONENTE = 'TIPOSTRUTTURA';
    const K_ORDINEPRESENTAZIONE = 'ORDINEPRESENTAZIONE';
    const K_TIPOSUPPORTOCOMPONENTE = 'TIPOSUPPORTOCOMPonente';
    const K_NOMECOMPONENTE = 'NOMECOMPONENTE';
    const K_FORMATOFILEVERSATO = 'FORMATOFILEVERSATO';
    const K_MIMETYPEFILE = 'MIMETYPEFILE';
    const K_FILEPATH = 'FILEPATH';
    const K_FILESIZE = 'FILESIZE';
    const K_DATADOCUMENTO = 'DATADOCUMENTO';
    const K_ALLEGATI = 'ALLEGATI';
    const K_ID = 'ID';
    const K_IDDOCUMENTO = 'IDDOCUMENTO';
    const K_IDUNIVOCO = 'IDUNIVOCO';
    const K_HASHVERSATO = 'HASHVERSATO';
    const K_IDCOMPONENTEVERSATO = 'IDCOMPONENTEVERSATO';
    const K_TIPOREGISTRO = 'TIPOREGISTRO';

    /*
     * Esiti Conservazione
     */

    function __construct($parametri) {
        parent::__construct($parametri);
        $this->parametriManager = $this->getParametriManager();
    }

    public function conservaAnapro() {
        if (!parent::conservaAnapro()) {
            return false;
        }
        /*
         * Prerequisiti specifici per il manager
         */
        if (!$this->ControllaPrerequisitiManager()) {
            return false;
        }

        /*
         * Crea base dati generali e per ASMEZDOC
         */
        if (!$this->getBaseDatiUnitaDocumentarie()) {
            return false;
        }

        /**
         * Creazione unità documentaria:
         */
        $this->logger->info('Creazione XML. Unita Doc: ' . $this->unitaDocumentaria);
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                $this->creaXmlUnitaDocumentaria();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                // DA FARE.
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                // DA FARE.
                break;
        }
        $this->logger->info('Chiamo la funzione di versamento. Unita Doc: ' . $this->unitaDocumentaria);
        /*
         * Preparazione dello ZIP
         */
        $subPath = "proConsAsmezTmp-" . md5(microtime());
        $tempPathZip = itaLib::createAppsTempPath($subPath);
        $NomeFileZip = "registroProt_zip_" . md5(microtime()) . ".zip";

        $fileZip = $tempPathZip . '/' . $NomeFileZip;
        if (file_exists($fileZip)) {
            if (!@unlink($fileZip)) {
                $this->errMessage = self::ERR_CODE_FATAL;
                $this->errMessage = "Errore in cancellazione file ZIP.";
                return false;
            }
        }
        /*
         * Creo il file zip
         */
        $archiv = new ZipArchive();
        if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
            $this->errMessage = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in creazione file ZIP.";
            return false;
        }
        /*
         * Carica Allegati:
         */

        foreach ($this->baseDati[self::K_ALLEGATI] as $keyAlle => $Allegato) {
            $archiv->addFromString($Allegato[self::K_NOMECOMPONENTE], file_get_contents($Allegato[self::K_FILEPATH]));
        }
        /*
         *  Aggiungo IPV
         */
        $archiv->addFromString('IPdV.xml', $this->xmlRichiesta);
        $archiv->close();

        /*
         * Versamento in conservazione
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                if (!$this->versaAsmezDoc($fileZip)) {
                    return false;
                }
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                break;
        }


        /*
         * Salvataggio Esiti
         */
        if (!$this->parseEsitoVersamento()) {
            return false;
        }
        /*
         * Storicizzo Proconservazione
         */
        if (!$this->StoricizzaProconser()) {
            return false; // Potrebbe continuare: errore possibile solo su db.
        }
        /*
         * Storicizzo ProUpdateConservazione
         */
        if (!$this->StoricizzaProUpdateConser()) {
            return false; // Potrebbe continuare: errore possibile solo su db.
        }
        /*
         * Salvataggio Dati Conservazione
         */
        $this->logger->info('Salvataggio dati di conservazione.');
        if (!$this->SalvaDatiConservazione()) {
            return false;
        }
        /*
         * Salvataggio Esiti di Conservazione
         */
        if (!$this->SalvaEsitoConservazione()) {
            return false;
        }
        $this->logger->info('Esito: ' . $this->retEsito . ' - Status: ' . $this->retStatus);
        $this->logger->info('Esito Salvato. Conservazione Terminata.');
        return true;
    }

    private function getParametriManager() {
        $Parametri = array();
        $EnvParametri = $this->devLib->getEnv_config(self::CLASSE_PARAMETRI, 'codice', '', true);
        foreach ($EnvParametri as $key => $Parametro) {
            $Parametri[$Parametro['CHIAVE']] = $Parametro['CONFIG'];
        }
        return $Parametri;
    }

    private function ControllaPrerequisitiManager() {
        if (!$this->parametriManager['ASMEZDOC_URL']) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Non è possibile procedere. Parametri ASMEZDOC Conservazione non definiti.";
            return false;
        }
        return true;
    }

    protected function getBaseDatiUnitaDocumentarie() {
        if (!parent::getBaseDatiUnitaDocumentarie()) {
            return false;
        }
        $this->logger->info('Carico il dizionario ASMEZDOC per la Conservazione');
        $this->baseDati[self::K_VERSIONE] = self::Versione;
        $this->baseDati[self::K_ENTE] = $this->parametriManager['ASMEZDOC_ENTEDESC'];
        $this->baseDati[self::K_CFENTE] = $this->parametri['CFENTE'];
        $this->baseDati[self::K_KEYENTE] = $this->parametriManager['ASMEZDOC_ENTEDESC'];
        $this->baseDati[self::K_LOGINNAME] = $this->parametriManager['ASMEZDOC_UTENTE'];
        $this->baseDati[self::K_PASSWORD] = $this->parametriManager['ASMEZDOC_PASSWORD'];
        $this->baseDati[self::K_URLSERVIZIO] = $this->parametriManager['ASMEZDOC_URL'];
        $this->baseDati[self::K_IDVERSAMENTO] = $this->CalcolaIDVersamento();
        $this->baseDati[self::K_DATETIMEVERSAMENTO] = date('Y-m-d H:i:s');

        // Estrazione Altre Base Dati
        $this->getBaseDatiSpecifici();
        $this->getBaseDatiComponentiFile();
        return true;
    }

    private function CalcolaIDVersamento() {
        $md5Time = itaLib::getRandBaseName();
        $CodiceEnte = $this->parametriManager['ASMEZDOC_ENTE'];
        $IDVersamento = 'STRG_' . $CodiceEnte . '_' . $this->anapro_rec['PRONUM'] . '_' . $md5Time;
        return $IDVersamento;
    }

    private function getBaseDatiComponentiFile() {
        /*
         * Allegati e Doc principale:
         */
        $Ordine = 2;
        foreach ($this->baseDati[proConservazioneManagerHelper::K_ANADOC_TAB] as $key => $Anadoc_rec) {
            /*
             * Lettura Dati.
             */
            $Allegato = array();
            if ($Anadoc_rec['DOCTIPO'] == 'PRINCIPALE') {
                $Allegato[self::K_ORDINEPRESENTAZIONE] = 1;
            } else {
                $Allegato[self::K_ORDINEPRESENTAZIONE] = $Ordine;
            }
            $AnadocChiave = 'ANADOC-' . $Anadoc_rec['ROWID'];
            $IdDocVersamento = $this->baseDati[self::K_IDVERSAMENTO] . '_' . str_pad($Allegato[self::K_ORDINEPRESENTAZIONE], '3', '0', STR_PAD_LEFT);
            $Allegato[self::K_IDDOCUMENTO] = $IdDocVersamento;
            $Allegato[self::K_IDUNIVOCO] = $AnadocChiave;
            /* Componenti */
//            $Allegato[self::K_ID] = $Anadoc_rec['ROWID'];
            $Allegato[self::K_ID] = $AnadocChiave;
            $Allegato[self::K_TIPOSUPPORTOCOMPONENTE] = 'FILE';
            $Allegato[self::K_NOMECOMPONENTE] = $Anadoc_rec['DOCNAME'];
            $Allegato[self::K_FORMATOFILEVERSATO] = $Anadoc_rec['ESTENSIONE'];
            $Allegato[self::K_HASHVERSATO] = $Anadoc_rec['HASHFILE'];

            $CType = itaMimeTypeUtils::estraiEstensione($Allegato[self::K_NOMECOMPONENTE]);
            $Allegato[self::K_MIMETYPEFILE] = $CType;
            $Allegato[self::K_FILEPATH] = $Anadoc_rec['FILEPATH'];
            $Allegato[self::K_FILESIZE] = filesize($Allegato[self::K_FILEPATH]);
            $Allegato[self::K_DATADOCUMENTO] = date("Y-m-d", strtotime($Anadoc_rec['DOCDATADOC'])) . ' ' . $Anadoc_rec['DOCORADOC'];


            if ($Anadoc_rec['DOCTIPO'] != 'PRINCIPALE') {
                // Incremento conteggio allegati.
                $Ordine++;
            }

            $this->baseDati[self::K_ALLEGATI][] = $Allegato;
        }
        /*
         * Elaborazione Base Dati Mail
         */
        foreach ($this->baseDati[proConservazioneManagerHelper::K_MAIL_ARCHIVIO_TAB] as $key => $Mail_rec) {
            /*
             * Lettura Dati.
             */
            $Allegato = array();
            $AllegatoChiave = 'MAIL-' . $Mail_rec['ROWID'];
            $Allegato[self::K_ORDINEPRESENTAZIONE] = $Ordine;
            $IdDocVersamento = $this->baseDati[self::K_IDVERSAMENTO] . '_' . str_pad($Allegato[self::K_ORDINEPRESENTAZIONE], '3', '0');
            $Allegato[self::K_IDDOCUMENTO] = $IdDocVersamento;
            $Allegato[self::K_IDUNIVOCO] = $AllegatoChiave;
            /* Componenti */
            $Allegato[self::K_ID] = $AllegatoChiave;
            $Allegato[self::K_TIPOSUPPORTOCOMPONENTE] = 'FILE';
            $Allegato[self::K_NOMECOMPONENTE] = $Mail_rec['NOMEFILE'];
            $Allegato[self::K_FORMATOFILEVERSATO] = $Mail_rec['ESTENSIONE'];
            $Allegato[self::K_HASHVERSATO] = $Mail_rec['HASHFILE'];

            $CType = itaMimeTypeUtils::estraiEstensione($Allegato[self::K_NOMECOMPONENTE]);
            $Allegato[self::K_MIMETYPEFILE] = $CType;
            $Allegato[self::K_FILEPATH] = $Mail_rec['FILEPATH'];
            $Allegato[self::K_FILESIZE] = filesize($Allegato[self::K_FILEPATH]);
            $Allegato[self::K_DATADOCUMENTO] = date("Y-m-d", strtotime($Anadoc_rec['DOCDATADOC'])) . ' ' . $Anadoc_rec['DOCORADOC'];

            /* Allegato */
            $this->baseDati[self::K_ALLEGATI][] = $Allegato;
            // Incremento conteggio allegati.
            $Ordine++;
        }
    }

    private function getBaseDatiSpecifici() {
        /*
         * Dati Unità Documentaria ASMEZ DOC
         * Lettura in base della unità documentaria:
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                $MetadataClassID = '4';
                $MetadataClassName = 'registro_giornaliero_di_protocollo_Informatico';
                // Se è registro servono dati base del registro.
                if (!proConservazioneManagerHelper::getBaseDatiRegistroProtocollo($this->baseDati)) {
                    return false;
                }
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                $MetadataClassID = '4';
                $MetadataClassName = '';
                break;

            default :
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = 'Unità documentaria non prevista nella conservazione.';
                return false;
                break;
        }
        // QUI EVENTUALI BASE DATI AGGIUNTIVI
        $this->baseDati[self::K_METADATICLASSID] = $MetadataClassID;
        $this->baseDati[self::K_METADATICLASSNAME] = $MetadataClassName;
        return true;
    }

    private function creaXmlUnitaDocumentaria() {
        /*
         * Scrivo l'xml
         */
        $xmlAllegati = $this->getXmlAllegati();
        //$ID = $BaseDatiDocPrincipale
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
                <IPdV>
                  <DescGenerale>
                    <ID>{$this->baseDati[self::K_IDVERSAMENTO]}</ID>
                    <Applicazione>
                      <ApplicazioneNome>" . self::ApplicazioneNome . "</ApplicazioneNome>
                      <ApplicazioneVersione>" . self::Versione . "</ApplicazioneVersione>
                      <ApplicazioneProduttore>" . self::ApplicazioneProduttore . "</ApplicazioneProduttore>
                    </Applicazione>
                  </DescGenerale>
                  <PdV>
                    <ID>{$this->baseDati[self::K_IDVERSAMENTO]}</ID>
                    <MetadataClassID>{$this->baseDati[self::K_METADATICLASSID]}</MetadataClassID>
                    <MetadataClassName>{$this->baseDati[self::K_METADATICLASSNAME]}</MetadataClassName>
                 </PdV>";
        $xml.=$xmlAllegati;
        $xml.=" 
            <Processo>
                <Soggetto>
                    <SoggettoId>{$this->baseDati[self::K_CFENTE]}</SoggettoId>
                    <SoggettoNome>{$this->baseDati[self::K_ENTE]}</SoggettoNome>
                </Soggetto>
                <Tempo>{$this->baseDati[self::K_DATETIMEVERSAMENTO]}</Tempo>
          </Processo>
          </IPdV>";


        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function getXmlAllegati() {

        // Contemplare anche dati principali.
        // Usare chiave ordine allienata a 3 per l'id. Aggiugnere alla fine dell'idversamento.
        $xmlMetadatiAllegati = $this->getXmlMetadatiAllegati();
        $xmlAllegati = '';
        if ($this->baseDati[self::K_ALLEGATI]) {
            $xmlAllegati .= ''
                    . '<FileGruppo>';
            foreach ($this->baseDati[self::K_ALLEGATI] as $Allegato) {
                $this->baseDati[self::K_IDVERSAMENTO] . '_' . str_pad($Allegato[self::K_ORDINEPRESENTAZIONE], '3', '0');
                $xmlAllegati .= '
                                <File>
                                    <ID>' . $Allegato[self::K_IDDOCUMENTO] . '</ID>
                                    <Impronta>' . $Allegato[self::K_HASHVERSATO] . '</Impronta>
                                    <ExtraInfo>
                                        <NomeFile>' . $Allegato[self::K_NOMECOMPONENTE] . '</NomeFile> 
                                        <Mimetype>' . $Allegato[self::K_MIMETYPEFILE] . '</Mimetype> 
                                        <Dimensione>' . $Allegato[self::K_FILESIZE] . '</Dimensione> 
                                        <DataDocumento>' . $Allegato[self::K_DATADOCUMENTO] . '</DataDocumento> 
                                           ' . $xmlMetadatiAllegati . '
                                    </ExtraInfo>
                               </File> 
                               ';
            }
            $xmlAllegati .= '</FileGruppo>';
        }

        return $xmlAllegati;
    }

    private function getXmlMetadatiAllegati() {

        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                return $this->getXmlMetaDatiRegistroGiornaliero();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                // DA FARE.
                break;
        }
    }

    private function getXmlMetaDatiRegistroGiornaliero() {
        $Anapro_rec = $this->baseDati[proConservazioneManagerHelper::K_ANAPRO_REC];
        $xml = "                     <Metadati>
                                        <Codiceregistro>{$this->baseDati[self::K_TIPOREGISTRO]}</Codiceregistro>
                                        <Numeroregistro>" . $Anapro_rec['PRONUM'] . "</Numeroregistro>
                                        <Dataregistro>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Dataregistro> 
                                        <Datacreazione>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Datacreazione>    
                                        <Numeroiniziale>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROPRIMAREGISTRAZIONE]}</Numeroiniziale>    
                                        <Numerofinale>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROULTIMAREGISTRAZIONE]}</Numerofinale>    
                                        <Datainizioregistrazione>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Datainizioregistrazione>    
                                        <Datafineregistrazione>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Datafineregistrazione>    
                                        <Datafirma></Datafirma>    
                                        <Responsabile>{$this->baseDati[proConservazioneManagerHelper::K_RESPONSABILE]}</Responsabile>
                                        <Codicefiscaleresponsabile></Codicefiscaleresponsabile>
                                        <Numerodocumentiregistrati></Numerodocumentiregistrati>
                                        <Soggettoproduttore>{$this->baseDati[proConservazioneManagerHelper::K_SOGGETTOPRODUTTORE]}</Soggettoproduttore>
                                        <Codicefiscalesoggettoproduttore></Codicefiscalesoggettoproduttore>
                                    </Metadati>";
        return $xml;
    }

    public function versaAsmezDoc($fileZipConservazione) {
        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $restClient = new itaRestClient();
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url($this->baseDati[self::K_URLSERVIZIO]);
        $restClient->setDebugLevel(true);
        /*
         * Chiamata al servizio
         */
        $username = $this->baseDati[self::K_LOGINNAME];
        $password = $this->baseDati[self::K_PASSWORD];

//        Out::msgInfo('zipFile', $fileZipConservazione);
//
//        return false;

        $restClientResult = $restClient->post(
                '', array(), array(
            "Authorization: Basic " . base64_encode("$username:$password"),
            'Accept-Language: it-it',
            'Accept-Encoding: gzip, deflate'
                ), file_get_contents($fileZipConservazione), 'application/zip'
        );
        //
        if (!$restClientResult) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $restClient->getErrMessage();
            return true;
        }
        $this->xmlResponso = $restClient->getResult();
        if ($restClient->getHttpStatus() !== 200) {
            $this->errCode = self::ERR_CODE_FATAL;
            $JsonReturn = json_decode($this->xmlResponso, true);
            if ($JsonReturn['error']) {
                
            } else {
                $this->errMessage = $restClient->getHttpStatus();
            }
            return true;
        }
        return true;
    }

    public function parseEsitoVersamento() {


        $JsonReturn = json_decode($this->xmlResponso, true);
        $CodiceErrore = $MessaggioErrore = '';
        if (!$JsonReturn['filesize']) {
            if ($JsonReturn['error']) {
                $MessaggioErrore = $JsonReturn['error'];
            }
            if ($JsonReturn['code']) {
                $CodiceErrore = $JsonReturn['code'];
            }
        }
        $this->datiMinimiEsitoVersamento = array();
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CONSERVATORE] = self::CLASSE_PARAMETRI;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_DATAVERSAMENTO] = date('Ymd');
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_ESITO] = 'DA_VERIFICARE';
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CODICEERRORE] = $CodiceErrore;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_MESSAGGIOERRORE] = $MessaggioErrore;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_NOTECONSERV] = "FileSize di Risposta:" . $JsonReturn['filesize'];
        return true;
    }

    public function SalvaDatiConservazione() {
        // Provvisorio $model.
        $model = new itaModel();

        $xmlRichiesta = $this->xmlRichiesta;
        $Anapro_rec = $this->baseDati[proConservazioneManagerHelper::K_ANAPRO_REC];

        $subPath = "proConservazione-work-" . itaLib::getRandBaseName();
        $tempPath = itaLib::createAppsTempPath($subPath);
        /*
         *  Salvo L'allegato richiesta di servizio (xml)
         */
        $AllegatoDiServizio = array();
        $errSalvaRichiesta = false;
        $randNameRichiesta = md5(rand() * time()) . ".xml";
        $DestinoXmlRichiesta = $tempPath . "/" . $randNameRichiesta;
        if (file_put_contents($DestinoXmlRichiesta, $xmlRichiesta)) {
            $datetime_esito = date('Ymd_His');
            $NomeFileRichiesta = $FileInfo = "RICHIESTA_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml";
            $this->LastNomeFileRichiesta = $NomeFileRichiesta;
            $AllegatoDiServizio[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $DestinoXmlRichiesta,
                'FILENAME' => $randNameRichiesta,
                'FILEINFO' => $FileInfo,
                'DOCTIPO' => 'ALLEGATO',
                'DAMAIL' => '',
                'DOCNAME' => $NomeFileRichiesta,
                'DOCIDMAIL' => '',
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 1,
            );

            $risultato = $this->proLibAllegati->GestioneAllegati($model, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
            if (!$risultato) {
                $this->errCode = self::ERR_CODE_WARNING;
                $this->errMessage = $this->proLibAllegati->getErrMessage();
                $errSalvaRichiesta = true;
            }
        } else {
            $this->errCode = self::ERR_CODE_WARNING;
            $this->errMessage = "Errore in salvataggio contenuto file xml.";
            $errSalvaRichiesta = true;
        }
        // Allegato Esito

        return true;
    }

}
