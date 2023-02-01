<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    proConservazioneManagerNamirial
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    02.07.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManager.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocRepositoryClient.class.php';
include_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocTransferClient.class.php';

class proConservazioneManagerNamirial extends proConservazioneManager {

    const CLASSE_PARAMETRI = 'NAMIRIAL';
    const Versione = '1';
    const ApplicazioneNome = 'Italsoft Srl';
    const ApplicazioneProduttore = 'Italsoft Srl';

    /*
     * Keyes Base Dati/ Dizionario NAMIRIAL Elaborata 
     */
    const K_VERSIONPRODUCER = 'VERSIONPPRODUCER';
    const K_APPNAMEPRODUCER = 'APPNAMEPRODUCER';
    const K_APPLICAZIONEPRODUCER = 'APPLICAZIONEPRODUCER';
    const K_LOGINNAME = 'LOGINNAME';
    const K_PASSWORD = 'PASSWORD';
    const K_IDVERSAMENTO = 'K_IDVERSAMENTO';
    const K_ENTE = 'ENTE';
    const K_IPPRODUCER = 'IPPRODUCER';
    const K_CFPRODUCER = 'CFPRODUCER';
    const K_PIVAPRODUCER = 'PIVAPRODUCER';
    const K_URLSERVIZIO = 'URLSERVIZIO';
    const K_IDSETUP = 'IDSETUP'; // Nuovo parametro: id installazione comunicato dal conservatore assegnato all'ente
    const K_IDCOMPANY = 'IDCOMPANY'; // Nuovo parametro: identificativo azienda fornito
    const K_DOCYEAR = 'DOCYEAR';

    /*
     * Keys Principali:
     */
    const K_IDVDA = 'IDVDA';
    const K_LABELVDA = 'LABELVDA';
    const K_DESCRIPTIONVDA = 'DESCRIPTIONVDA';
    const K_DISTINTAMECCANOGRAFICAVDA = 'DISTINTAMECCANOGRAFICAVDA';
    const K_FILEDESCRIPTIONRULEVDA = 'FILEDESCRIPTIONRULEVDA';
    /*
     * Chiavi
     */
    const K_ALLEGATI = 'ALLEGATI';
    const K_HASHVERSATO = 'HASHVERSATO';
    const K_IDDOCUMENTO = 'IDDOCUMENTO';
    const K_IDDOCTYPE = 'IDDOCTYPE';
    const K_PATHFILE = 'PATHFILE';
    const K_MIMETYPE = 'MIMETYPE';
    const K_TYPESHAFILE = 'TYPESHAFILE';
    const K_FILEPATH = 'FILEPATH';
    const K_NOMECOMPONENTE = 'NOMECOMPONENTE';


    /*
     * Keys Metadati Principali
     */
    const K_NUMBERINDEX = 'NUMBERINDEX';
    const K_NAMEINDEX = 'NAMEINDEX';
    const K_REQUIREDINDEX = 'REQUIREDINDEX';
    const K_REGEXINDEX = 'REGEXINDEX';
    const K_TYPEINDEX = 'TYPEINDEX';
    /*
     *  Chiavi Aggiuntive Registro
     */
    const K_CODICEIPA = 'CODICEIPA';
    const K_CODICEAOO = 'CODICEAOO';
    const K_CODICEIDREGISTRO = 'CODICEIDREGISTRO';
    const K_DATAINIZIALE = 'K_METDATAINIZIALE';
    const K_DATAFINALE = 'DATAFINALE';
    const K_TIPODOCUMENTO = 'TIPODOCUMENTO'; //Personalizzato!?
    const K_NPRIMAREG = 'NPRIMAREG';
    const K_NULTIMAREG = 'NULTIMAREG';
    const K_RESPONSABILE = 'RESPONSABILE';
    const K_CFRESPONSABILE = 'CFRESPONSABILE';
    const K_OPERATORE = 'OPERATORE';
    const K_NDOCREG = 'NDOCREG';
    const K_NDOCANN = 'NDOCANN';
    const K_ELEUNITADOC = 'ELEUNITADOC';
    const K_UNITADOC = 'UNITADOC';
    const K_TIPOUNITADOC = 'TIPOUNITADOC';

    private $IDVersamento = '';
    private $IDPendingRequest = '';
    //
    public static $MetadatiRegistroProtocollo = array(
        self::K_CODICEIPA => array('Number' => '1', 'Name' => 'Codice IPA', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_CODICEAOO => array('Number' => '2', 'Name' => 'Codice AOO', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_CODICEIDREGISTRO => array('Number' => '3', 'Name' => 'Codice identificativo registro', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_DATAINIZIALE => array('Number' => '4', 'Name' => 'Data iniziale', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'Date'),
        self::K_DATAFINALE => array('Number' => '5', 'Name' => 'Data finale', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'Date'),
        self::K_TIPODOCUMENTO => array('Number' => '6', 'Name' => 'Tipo documento', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_NPRIMAREG => array('Number' => '7', 'Name' => 'Numero prima registrazione', 'Required' => 'false', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_NULTIMAREG => array('Number' => '8', 'Name' => 'Numero ultima registrazione', 'Required' => 'false', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_RESPONSABILE => array('Number' => '9', 'Name' => 'Denominazione Responsabile', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_CFRESPONSABILE => array('Number' => '10', 'Name' => 'Codice fiscale Responsabile', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_OPERATORE => array('Number' => '11', 'Name' => 'Operatore', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_NDOCREG => array('Number' => '12', 'Name' => 'Numero documenti registrati', 'Required' => 'false', 'RegEx' => '', 'IndexType' => 'Int64'),
        self::K_NDOCANN => array('Number' => '13', 'Name' => 'Numero documenti annullati', 'Required' => 'false', 'RegEx' => '', 'IndexType' => 'Int64'),
        self::K_ELEUNITADOC => array('Number' => '14', 'Name' => 'Elemento unità documentaria', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_UNITADOC => array('Number' => '15', 'Name' => 'Unita documentaria', 'Required' => 'true', 'RegEx' => '', 'IndexType' => 'String'),
        self::K_TIPOUNITADOC => array('Number' => '16', 'Name' => 'Tipologia unita documentaria', 'Required' => 'false', 'RegEx' => '', 'IndexType' => 'String')
    );

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
        $subPath = "proConsNamirialTmp-" . md5(microtime());
        $tempPathZip = itaLib::createAppsTempPath($subPath);
        $NomeFileZip = "pdv.zip";

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
         * Da aggiungere nella cartella "Files"!
         */
        $Path = 'Files/';
        foreach ($this->baseDati[self::K_ALLEGATI] as $keyAlle => $Allegato) {
            $archiv->addFromString($Path . $Allegato[self::K_NOMECOMPONENTE], file_get_contents($Allegato[self::K_FILEPATH]));
        }
        /*
         *  Aggiungo IPV
         */
        $archiv->addFromString('indexPdV.xml', $this->xmlRichiesta);

        $archiv->close();

        /*
         * Versamento in conservazione
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                $Anaent_59_rec = $this->proLib->getAnaent(59);
                if ($Anaent_59_rec['ENTDE4'] == '') {
                    if (!$this->versaDocumentoAsincrono($fileZip)) {
                        return false;
                    }
                } else {
                    if (!$this->versaDocumentoSincro($fileZip)) {
                        return false;
                    }
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
        if (!$this->parametriManager['NAMIRIAL_URL']) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Non è possibile procedere. Parametri NAMIRIAL Conservazione non definiti.";
            return false;
        }
        return true;
    }

    protected function getBaseDatiUnitaDocumentarie() {
        if (!parent::getBaseDatiUnitaDocumentarie()) {
            return false;
        }
        $this->logger->info('Carico il dizionario ASMEZDOC per la Conservazione');
        $this->baseDati[self::K_VERSIONPRODUCER] = self::Versione;
        $this->baseDati[self::K_APPNAMEPRODUCER] = $this->parametriManager['NAMIRIAL_ENTE'];
        $this->baseDati[self::K_APPLICAZIONEPRODUCER] = $this->parametriManager['NAMIRIAL_ENTE']; // . ' - ' . self::ApplicazioneNome;//COMUNE + ITALSOFT? @TODO Verificare!
        $this->baseDati[self::K_LOGINNAME] = $this->parametriManager['NAMIRIAL_UTENTE'];
        $this->baseDati[self::K_PASSWORD] = $this->parametriManager['NAMIRIAL_PASSWORD'];
        $this->baseDati[self::K_IDVERSAMENTO] = $this->CalcolaIDVersamento();
        $this->baseDati[self::K_ENTE] = $this->parametriManager['NAMIRIAL_ENTE'];
        $this->baseDati[self::K_IPPRODUCER] = $this->parametriManager['NAMIRIAL_IP'];
        $this->baseDati[self::K_CFPRODUCER] = $this->parametri['CFENTE'];
        $this->baseDati[self::K_PIVAPRODUCER] = $this->parametri['CFENTE'];
        $this->baseDati[self::K_URLSERVIZIO] = $this->parametriManager['NAMIRIAL_URL'];
        $this->baseDati[self::K_IDSETUP] = $this->parametriManager['NAMIRIAL_IDSETUP'];
        $this->baseDati[self::K_IDCOMPANY] = $this->parametriManager['NAMIRIAL_IDAZIENDA'];

        // Estrazione Altre Base Dati
        $this->getBaseDatiSpecifici();
        $this->getBaseDatiComponentiFile();
        return true;
    }

    private function CalcolaIDVersamento() {
        $md5Time = itaLib::getRandBaseName();
        $IDVersamento = $this->anapro_rec['ROWID'] . '_' . $md5Time;
        return $IDVersamento;
    }

    private function getBaseDatiSpecifici() {
        /*
         * Dati Unità Documentaria NAMIRIAL
         * Lettura in base della unità documentaria:
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                $SourceVDALabel = 'REGISTRO PROTOCOLLO';
                $SourceVDADescription = 'Registro di protocollo';
                if (!proConservazioneManagerHelper::getBaseDatiRegistroProtocollo($this->baseDati)) {
                    return false;
                }
                $this->baseDati[self::K_CODICEIDREGISTRO] = $this->anapro_rec['PRONUM'] . $this->anapro_rec['PROPAR'];
                $this->baseDati[self::K_TIPODOCUMENTO] = 'Registro Giornaliero'; // Fisso?
                $this->baseDati[self::K_NPRIMAREG] = $this->baseDati[proConservazioneManagerHelper::K_NUMEROPRIMAREGISTRAZIONE];
                $this->baseDati[self::K_NULTIMAREG] = $this->baseDati[proConservazioneManagerHelper::K_NUMEROULTIMAREGISTRAZIONE];
                $this->baseDati[self::K_DATAINIZIALE] = date("d/m/Y", strtotime($this->baseDati[proConservazioneManagerHelper::K_DATAPRIMAREGISTRAZIONE]));
                $this->baseDati[self::K_DATAFINALE] = date("d/m/Y", strtotime($this->baseDati[proConservazioneManagerHelper::K_DATAULTIMAREGISTRAZIONE]));
                $this->baseDati[self::K_RESPONSABILE] = $this->baseDati[proConservazioneManagerHelper::K_RESPONSABILE];
                $this->baseDati[self::K_CFRESPONSABILE] = $this->baseDati[proConservazioneManagerHelper::K_CFRESPONSABILE];
                $this->baseDati[self::K_ELEUNITADOC] = 'Documento principale';
                $this->baseDati[self::K_UNITADOC] = 'Registro protocollo';
                $this->baseDati[self::K_TIPOUNITADOC] = '';
                $this->baseDati[self::K_NDOCREG] = '';

                break;

            case proConservazioneManagerHelper::K_UNIT_PROT:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                $SourceVDALabel = '';
                $SourceVDADescription = '';
                break;

            default :
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = 'Unità documentaria non prevista nella conservazione.';
                return false;
                break;
        }
        $this->baseDati[self::K_DOCYEAR] = substr($this->anapro_rec['PRONUM'], 0, 4);
        $this->baseDati[self::K_IDVDA] = $this->anapro_rec['ROWID'];
        $this->baseDati[self::K_LABELVDA] = $SourceVDALabel;
        $this->baseDati[self::K_DESCRIPTIONVDA] = $SourceVDADescription;
        /*
         * Fissi per il momento: @TODO Verificare
         */
        $this->baseDati[self::K_DISTINTAMECCANOGRAFICAVDA] = 'false';
        $this->baseDati[self::K_FILEDESCRIPTIONRULEVDA] = '?3';
        /*
         * Altri Dati:
         */
        $Anaent_26_rec = $this->proLib->getAnaent(26);
        $this->baseDati[self::K_CODICEIPA] = $Anaent_26_rec['ENTDE1'];
        $this->baseDati[self::K_CODICEAOO] = $Anaent_26_rec['ENTDE2'];
        $Operatore = $this->GetOperatore();
        $this->baseDati[self::K_OPERATORE] = $Operatore;
        // Vedere se serve valorizzare qui: DISTINTAMECCANOGRAFICAVDA, FILEDESCRIPTIONRULEVDA    
        return true;
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
            //$AnadocChiave = 'ANADOC-' . $Anadoc_rec['ROWID'];
            $AnadocChiave = $Anadoc_rec['ROWID'];
            $Allegato = array();
            $Allegato[self::K_IDDOCUMENTO] = $AnadocChiave;
            $Allegato[self::K_IDDOCTYPE] = $this->anapro_rec['ROWID'];
            $Allegato[self::K_PATHFILE] = $Anadoc_rec['DOCNAME'];
            $Allegato[self::K_NOMECOMPONENTE] = $Anadoc_rec['DOCNAME'];
            $CType = itaMimeTypeUtils::estraiEstensione($Allegato[self::K_NOMECOMPONENTE]);
            $Allegato[self::K_MIMETYPE] = $CType;
            $Allegato[self::K_TYPESHAFILE] = 'SHA-256';
            $Allegato[self::K_HASHVERSATO] = strtoupper($Anadoc_rec['HASHFILE']);
            $Allegato[self::K_FILEPATH] = $Anadoc_rec['FILEPATH'];


            $this->baseDati[self::K_ALLEGATI][] = $Allegato;
        }
        /*
         * Elaborazione Base Dati Mail
         */
        foreach ($this->baseDati[proConservazioneManagerHelper::K_MAIL_ARCHIVIO_TAB] as $key => $Mail_rec) {
            /*
             * Lettura Dati.
             */
            //$AllegatoChiave = 'MAIL-' . $Mail_rec['ROWID'];
            $AllegatoChiave = $Mail_rec['ROWID'];
            $Allegato = array();
            $Allegato[self::K_IDDOCUMENTO] = $AllegatoChiave;
            $Allegato[self::K_IDDOCTYPE] = $AllegatoChiave; // Cosa è?
            $Allegato[self::K_PATHFILE] = $Anadoc_rec['DOCNAME'];
            $Allegato[self::K_MIMETYPE] = $CType;
            $Allegato[self::K_TYPESHAFILE] = 'SHA-256';
            $Allegato[self::K_HASHVERSATO] = $Mail_rec['HASHFILE'];
            $Allegato[self::K_NOMECOMPONENTE] = $Mail_rec['NOMEFILE'];
            $CType = itaMimeTypeUtils::estraiEstensione($Allegato[self::K_NOMECOMPONENTE]);
            $Allegato[self::K_FILEPATH] = $Mail_rec['FILEPATH'];

            /* Allegato */
            $this->baseDati[self::K_ALLEGATI][] = $Allegato;
            // Incremento conteggio allegati.
            $Ordine++;
        }
    }

    public function GetOperatore() {
        $Utenti_rec = $this->accLib->GetUtenti($this->anapro_rec['PROUTE'], 'utelog'); //
        $Anamed_rec = $this->proLib->GetAnamed($Utenti_rec['UTEANA__1'], 'codice');

        return $Anamed_rec['MEDNOM'];
    }

    private function creaXmlUnitaDocumentaria() {
        /*
         * Scrivo l'xml
         */
        $xmlAllegati = $this->getXmlAllegati(); //FILEGROUP
        $xmlMetadatiSpecifici = $this->getXmlMetadatiSpecifici();

        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                    <PdV>
                      <SelfDescription>
                        <ID>{$this->baseDati[self::K_IDVERSAMENTO]}</ID>
                        <CreatingApplication>
                          <Name>{$this->baseDati[self::K_APPNAMEPRODUCER]}</Name>
                          <Version>{$this->baseDati[self::K_VERSIONPRODUCER]}</Version>
                          <Producer>{$this->baseDati[self::K_APPNAMEPRODUCER]}></Producer>
                          <IpProducer>{$this->baseDati[self::K_IPPRODUCER]}</IpProducer>
                                 <CodFiscale>{$this->baseDati[self::K_CFPRODUCER]}</CodFiscale>
                            <PartitaIVA>IT:{$this->baseDati[self::K_PIVAPRODUCER]}</PartitaIVA>
                        </CreatingApplication>
                        <MoreInfo>
                          <IdSetup>{$this->baseDati[self::K_IDSETUP]}</IdSetup>
                          <IdCompany>{$this->baseDati[self::K_IDCOMPANY]}</IdCompany>
                        </MoreInfo>
                      </SelfDescription>
                      <VdA> 
                      $xmlMetadatiSpecifici
                        <MoreInfo>
                            <RdCMessageGroup />
                        </MoreInfo>
                      </VdA>";
        $xml.=$xmlAllegati;
        $xml.=" 
                    </PdV>";


        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function getXmlMetadatiSpecifici() {

        $XmlMetaData = $this->GetXmlMetadata();

        $xml = " 
            <SourceVdA>
                    <ID>{$this->baseDati[self::K_IDVDA]}</ID>
                    <Label>{$this->baseDati[self::K_LABELVDA]}</Label>
                    <Description>{$this->baseDati[self::K_DESCRIPTIONVDA]}</Description>
                        <DistintaMeccanografica>{$this->baseDati[self::K_DISTINTAMECCANOGRAFICAVDA]}</DistintaMeccanografica>
                        <FileDescriptionRule>{$this->baseDati[self::K_FILEDESCRIPTIONRULEVDA]}</FileDescriptionRule>
                    <MetaData>
                      $XmlMetaData
                 </MetaData>
            </SourceVdA>";
        return $xml;
    }

    /**
     * Elaboro i BaseDati per estrarre i dati necessari per i Metadati
     */
    public function GetXmlMetadata() {
        $xmlMetadati = '';
        foreach (self::$MetadatiRegistroProtocollo as $Numero => $DatiKey) {
            $xmlMetadati.= " 
                       <Index>
                        <Number>{$DatiKey['Number']}</Number>
                        <Name>{$DatiKey['Name']}</Name>
                        <Required>{$DatiKey['Required']}</Required>
                        <RegEx>{$DatiKey['RegEx']}</RegEx>
                        <IndexType>{$DatiKey['IndexType']}</IndexType>
                      </Index>";
        }

        return $xmlMetadati;
    }

    private function getXmlAllegati() {

// Contemplare anche dati principali.
// Usare chiave ordine allienata a 3 per l'id. Aggiugnere alla fine dell'idversamento.
        $xmlMetadatiAllegati = $this->getXmlMetadatiAllegati();
        $xmlAllegati = '';
        if ($this->baseDati[self::K_ALLEGATI]) {
            $xmlAllegati .= ''
                    . '<FileGroup>';
            foreach ($this->baseDati[self::K_ALLEGATI] as $Allegato) {
                $xmlAllegati .= '
                                <File>
                                    <IdDoc>' . $Allegato[self::K_IDDOCUMENTO] . '</IdDoc>
                                    <IdDocType>' . $Allegato[self::K_IDDOCTYPE] . '</IdDocType>
                                    <PathFile><![CDATA[' . $Allegato[self::K_PATHFILE] . ']]></PathFile>
                                    <MimeType>' . $Allegato[self::K_MIMETYPE] . '</MimeType>
                                    <Hash>
                                          <Type>' . $Allegato[self::K_TYPESHAFILE] . '</Type>
                                          <Value><![CDATA[' . $Allegato[self::K_HASHVERSATO] . ']]></Value>
                                    </Hash>
                                    <MoreInfo>
                                            <MetaData>
                                                ' . $xmlMetadatiAllegati . '
                                            <ReferenceDocYear>' . $this->baseDati[self::K_DOCYEAR] . '</ReferenceDocYear>
                                            </MetaData>
                                    </MoreInfo>
                               </File> 
                               ';
            }
            $xmlAllegati .= '</FileGroup>';
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
        $xmlMetadati = '';
        foreach (self::$MetadatiRegistroProtocollo as $Key => $DatiKey) {
            $xmlMetadati.=" 
                        <Index>
                               <Name>
                                  <![CDATA[{$DatiKey['Name']}]]>
                               </Name>
                                <Value>
                                  <![CDATA[{$this->baseDati[$Key]}]]>
                               </Value>
                        </Index>
                ";
        }
        return $xmlMetadati;
    }

    private function getclient() {
        $soapClient = new SDocRepository();
        $soapClient->setWebservices_uri($this->parametriManager['NAMIRIAL_URL']);
        $soapClient->setUsername($this->parametriManager['NAMIRIAL_UTENTE']);
        $soapClient->setPassword($this->parametriManager['NAMIRIAL_PASSWORD']);
        $soapClient->setTimeout(10);
        $soapClient->setDebugLevel(false);
        return $soapClient;
    }

    private function getclientTransfer() {
        $soapClient = new SDocTransfer();
        $soapClient->setWebservices_uri($this->parametriManager['NAMIRIAL_TRANSFER_URL']);
        $soapClient->setUsername($this->parametriManager['NAMIRIAL_UTENTE']);
        $soapClient->setPassword($this->parametriManager['NAMIRIAL_PASSWORD']);
        $soapClient->setTimeout(10);
        $soapClient->setDebugLevel(false);
        return $soapClient;
    }

    public function versaDocumentoSincro($fileZipConservazione) {

        $stream = base64_encode(file_get_contents($fileZipConservazione));
        $param = array(
            'repositoryId' => '1',
            'filename' => 'pdv.zip',
            'length' => strlen(file_get_contents($fileZipConservazione)),
            'mimeType' => '',
            'stream' => $stream
        );

        //* @var $soapClient itaNamirial */
        $soapClient = $this->getclient();
        $soapClient->ws_createDocument($param);
        $this->IDVersamento = '';
        if (!$soapClient->getResult()) {
            $falut = $soapClient->getFault();
            if ($falut) {
                $Stringa = $soapClient->getResponse();
                $this->xmlResponso = $Stringa;
                list($skip, $messaggio) = explode('<Reason>', $Stringa);
                list($descrizione, $skip) = explode('</Reason>', $messaggio);
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $falut['!'] . 'Errore: ' . $descrizione;
                return true;
            } else {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $soapClient->getError();
                $this->xmlResponso = $soapClient->getError();
                return true;
            }
        } else {
            $this->xmlResponso = $soapClient->getResult();
            $this->IDVersamento = $soapClient->getResult();
        }
        //
        $this->xmlResponso = $soapClient->getResult();

        return true;
    }

    private function ElaboraErrMessage($soapClient) {
        $falut = $soapClient->getFault();
        if ($falut) {
            $Stringa = $soapClient->getResponse();
            $this->xmlResponso = $Stringa;
            list($skip, $messaggio) = explode('<Reason>', $Stringa);
            list($descrizione, $skip) = explode('</Reason>', $messaggio);
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $falut['!'] . 'Errore: ' . $descrizione;
            return true;
        } else {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $soapClient->getError();
            $this->xmlResponso = $soapClient->getError();
            return true;
        }
    }

    public function versaDocumentoAsincrono($fileZipConservazione) {
        /*
         * 1. Inizialize Upload
         */
        $param = array('tem:fileName' => 'packagePdV.zip');
        $soapClient = $this->getclientTransfer();
        $soapClient->ws_initializeUploadFileTemp($param);
        if (!$soapClient->getResult()) {
            $this->ElaboraErrMessage($soapClient);
            return true;
        }
        $FileIDRequest = $soapClient->getResult();

        /*
         * 2. Upload File
         */
        $stream = base64_encode(file_get_contents($fileZipConservazione));
        $additionalSoapHeaders = array(
            'tem:FileId' => $FileIDRequest,
            'tem:FileName' => 'packagePdV.zip',
            'tem:Length' => strlen(file_get_contents($fileZipConservazione))
        );
        $param = array(
            'FileByteStream' => $stream
        );
        $attachments = null;
        $soapClient = $this->getclientTransfer();
        $soapClient->ws_uploadFileTemp($param, $additionalSoapHeaders, $attachments);
        /*
         * Controllo Fault o Errori:
         */
        $falut = $soapClient->getFault();

        if ($falut) {
            $Stringa = $soapClient->getResponse();
            $this->xmlResponso = $Stringa;
            list($skip, $messaggio) = explode('<Reason>', $Stringa);
            list($descrizione, $skip) = explode('</Reason>', $messaggio);
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $falut['!'] . 'Errore: ' . $descrizione;
            return true;
        }
        $Errore = $soapClient->getError();
        if ($Errore) {
            if (strpos($Errore, 'No root part found in multipart/related content') !== false) {
                $soapClient->setResult('OK');
            } else {
                /* Controllo se errore diverso da Esito corretto */
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $soapClient->getError();
                $this->xmlResponso = $soapClient->getError();
                return true;
            }
        }
        /*
         * 3. Request Create Document
         */
        $soapClient = $this->getclient();
        $soapClient->ws_requestCreateDocumentFile(array('fileId' => $FileIDRequest));
        if (!$soapClient->getResult()) {
            $this->ElaboraErrMessage($soapClient);
            return true;
        }

        $this->IDVersamento = $this->IDPendingRequest = '';
        $IdRequestCreateDocument = $soapClient->getResult();
        $this->IDPendingRequest = $IdRequestCreateDocument;
        $this->xmlResponso = $soapClient->getResult();
        return true;
    }

    public function parseEsitoVersamento() {

        $Esito = 'POSITIVO'; //O va positivo?
        if ($this->errCode == self::ERR_CODE_FATAL) {
            $Esito = 'NEGATIVO';
        }

        $this->datiMinimiEsitoVersamento = array();
        $ChiaveVersamento = '';
        $ChiaveVersamento.= "Ambiente:" . self::CLASSE_PARAMETRI . ',';
        $ChiaveVersamento.= "Ente:" . $this->parametriManager['NAMIRIAL_ENTE'] . ',';
        $ChiaveVersamento .= "Struttura:" . $this->parametriManager['NAMIRIAL_ENTE'] . ',';
        $ChiaveVersamento .= "Numero:" . $this->baseDati[self::K_CODICEIDREGISTRO] . ',';
        $ChiaveVersamento .= "Anno:" . $this->baseDati[self::K_NDOCANN] . ',';
        $ChiaveVersamento .= "TipoRegistro:" . $this->baseDati[proConservazioneManagerHelper::K_TIPOREGISTRO];
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] = $ChiaveVersamento;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CONSERVATORE] = self::CLASSE_PARAMETRI;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_DATAVERSAMENTO] = date('Ymd');
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_ESITO] = $Esito;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CODICEERRORE] = $this->errCode;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_MESSAGGIOERRORE] = $this->errMessage;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_NOTECONSERV] = "";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_IDVERSAMENTO] = $this->IDVersamento;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_IDPENDINGREQUEST] = $this->IDPendingRequest;

        return true;
    }

    public function SalvaDatiConservazione() {
// Provvisorio $model.
        $model = new itaModel();

        $xmlRichiesta = $this->xmlRichiesta;
        $xmlResponse = $this->xmlResponso;
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
        /*
         *  Salvo L'allegato esito di servizio (xml)
         */
        $AllegatoDiServizio = array();
        $errSalvaEsito = false;
        $randNameEsito = md5(rand() * time()) . ".xml";
        $DestinoXmlEsito = $tempPath . "/" . $randNameEsito;
        if (file_put_contents($DestinoXmlEsito, $xmlResponse)) {
            $datetime_esito = date('Ymd_His');
            $NomeFileResponso = $FileInfo = "ESITO_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml";
            $this->LastNomeFileResponso = $NomeFileResponso;
            $AllegatoDiServizio[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $DestinoXmlEsito,
                'FILENAME' => $randNameEsito,
                'FILEINFO' => $FileInfo,
                'DOCTIPO' => 'ALLEGATO',
                'DAMAIL' => '',
                'DOCNAME' => $NomeFileResponso,
                'DOCIDMAIL' => '',
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 1,
            );

            $risultato = $this->proLibAllegati->GestioneAllegati($model, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
            if (!$risultato) {
                $this->errCode = self::ERR_CODE_WARNING;
                $this->errMessage = $this->proLibAllegati->getErrMessage();
                $errSalvaEsito = true;
            }
        } else {
            $this->errCode = self::ERR_CODE_WARNING;
            $this->errMessage = "Errore in salvataggio contenuto file xml.";
            $errSalvaEsito = true;
        }


        return true;
    }

    public function getRDV($idPdV) {
        $param = array(
            'tem:idPdV' => trim($idPdV)
        );
        //* @var $soapClient itaNamirial */
        $soapClient = $this->getclient();
        $soapClient->ws_getPdV($param);
        if (!$soapClient->getResult()) {
            $falut = $soapClient->getFault();
            if ($falut) {
                $Stringa = $soapClient->getResponse();
                $this->xmlResponso = $Stringa;
                list($skip, $messaggio) = explode('<Reason>', $Stringa);
                list($descrizione, $skip) = explode('</Reason>', $messaggio);
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $falut['!'] . 'Errore: ' . $descrizione;
                return false;
            } else {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $soapClient->getError();
                return false;
            }
        }
        //
        $ArrayPDV = $soapClient->getResult();
        // Chiamata per estrazione file (Export) solo se.
        $IdRdV = $ArrayPDV['IdRdV'];
        if ($IdRdV == '000000000000000000000000' || $IdRdV == '') {
            $this->errCode = self::ERR_CODE_WARNING;
            $this->errMessage = 'Ricevuta di Versamento non ancora generata.';
            $this->xmlResponso = 'Ricevuta di Versamento non ancora generata.';
            return false;
        }
        /*
         * Extract RDV:
         */

        $param = array(
            'tem:idRdV' => $IdRdV
        );
        $soapClient = $this->getclient();
        $soapClient->ws_exportRdV($param);
        if (!$soapClient->getResult()) {
            $falut = $soapClient->getFault();
            if ($falut) {
                $Stringa = $soapClient->getResponse();
                $this->xmlResponso = $Stringa;
                list($skip, $messaggio) = explode('<Reason>', $Stringa);
                list($descrizione, $skip) = explode('</Reason>', $messaggio);
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $falut['!'] . 'Errore: ' . $descrizione;
                return false;
            } else {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $soapClient->getError();
                return false;
            }
        }
        $RDVContent = base64_decode($soapClient->getResult());
        return $RDVContent;
    }

    public function setRDVFileFromUUID($UUID, $PendingUUID = '') {
        if ($this->RDVFile && file_exists($this->RDVFile)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "RDV file già scaricato";
            return false;
        }

        $subPath = uniqid("work-rdv-");
        $path = itaLib::createAppsTempPath($subPath);
        $RDVContent = $this->getRDV($UUID, $PendingUUID);
        if (!$RDVContent) {
            return false;
        }
        $filename = "rdv_$UUID.zip";
        if (file_put_contents($path . '/' . $filename, $RDVContent) === false) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in salataggio RDV file: " . print_r(error_get_last(), true);
            return false;
        }
        $this->RDVFile = $path . '/' . $filename;
        return true;
    }

    public function setRDVFileFromPath($FilePath) {
        if (!$FilePath || !file_exists($FilePath)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "File RDV non disponibile.";
            return false;
        }

        $subPath = uniqid("work-rdv-");
        $path = itaLib::createAppsTempPath($subPath);
        $FileDest = $path . '/' . pathinfo($FilePath, PATHINFO_BASENAME);
        if (!copy($FilePath, $FileDest)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in copia File RDV temporaneo.";
            return false;
        }

        $this->RDVFile = $FileDest;
        return true;
    }

    public function parseXmlRDV($UUID, $PendingUUID = '') {
        if (!$UUID && !$PendingUUID) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Codice Versamento UUID e PendingUUID non definiti.";
            return false;
        }
        $this->logger->info('Inizio Controllo RDV. Prot: ' . $this->anapro_rec['PRONUM'] . $this->anapro_rec['PROPAR']);
        /*
         * Se ho Pending ma non UUID uso il Pending per ricavare l'UUID
         */
        if (!$UUID) {
            $UUID = $this->GetUUIDFromPendingUUID($PendingUUID);
            if (!$UUID) {
                return false;
            }
            // Aggiorno UUID pending:
            if (!$this->AggiornaUUIDFromPending($UUID, $PendingUUID)) {
                return false;
            }
        }

        if (!$this->setRDVFileFromUUID($UUID)) {
            return false;
        }
        $XML = $this->extractRDVXml();
        if (!$XML) {
            return false;
        }
        /*
         * Parso XML
         */
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($XML);
        if (!$retXml) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "File XML . Impossibile leggere il contenuto del Messaggio RDV xml.";
            return false;
        }
        $this->logger->info('Analizzo XML di ritorno.');
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Lettura XML. Impossibile estrarre i dati Messaggio RDV xml.";
            return false;
        }
        $this->RDVArrayXml = $arrayXml;
        /*
         * Salvataggio XML RDV
         */
        $this->logger->info('Salvataggio file XML RDV.');
        if (!$this->SalvataggioRDV()) {
            return false;
        }
        /*
         * Salvataggio Metadati
         */

        // Quali metadati occorre salvare? 
        $this->logger->info('Salvataggio Metadati RDV.');
        if (!$this->SalvataggioMetadatiRDV()) {
            return false;
        }
        $this->logger->info('Controllo RDV Terminato.');
        return true;
    }

    public function SalvataggioRDV() {
        $model = new itaModel();
        $Anapro_rec = $this->anapro_rec;
        /*
         *  Salvo L'allegato richiesta di servizio (xml)
         */
        $AllegatoDiServizio = array();
        $errSalvaRichiesta = false;

        if (!$this->RDVFile || !file_exists($this->RDVFile)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "File RDV non presente.";
            return false;
        }
        $Ext = proConservazioneManagerHelper::GetEstensione($this->RDVFile, 3);
        $datetime_esito = date('Ymd_His');
        $NomeFileRdv = $FileInfo = "RDV_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}." . $Ext;
        $this->LastNomeFileRDV = $NomeFileRdv;
        $AllegatoDiServizio[] = Array(
            'ROWID' => 0,
            'FILEPATH' => $this->RDVFile,
            'FILENAME' => pathinfo($this->RDVFile, PATHINFO_BASENAME),
            'FILEINFO' => $FileInfo,
            'DOCTIPO' => 'ALLEGATO',
            'DAMAIL' => '',
            'DOCNAME' => $NomeFileRdv,
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

        return true;
    }

    public function SalvataggioMetadatiRDV() {
        $sql = "SELECT * FROM PROCONSER WHERE ROWID_ANAPRO = {$this->rowidAnapro} AND FLSTORICO = 0";
        $Proconser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        try {
            $Proconser_rec['DOCRDV'] = $this->LastNomeFileRDV;
            //Tabella per definizione esiti?
            /*
             * Funzione di Controllo inserita per i Check. Provvisoria discutere con Michele.
             */
            $CheckGroup = $this->RDVArrayXml['PdVGruppo'][0]['PdV'][0]['CheckGroup'][0]['Check'];
            $EsitiPostivi = $EsitiNegativi = 0;
            $MsgEsitiNegativi = '';
            foreach ($CheckGroup as $CheckSingolo) {
                if ($CheckSingolo['Esito'][0]['@textNode'] == 'OK') {
                    $EsitiPostivi++;
                } else {
                    $EsitiNegativi++;
                    $MsgEsitiNegativi.=$CheckSingolo['Descrizione'][0]['@textNode'] . "<br>";
                }
            }
            $CodiceEsito = 'NEGATIVO';
            if ($EsitiNegativi == 0) {
                $CodiceEsito = 'POSITIVO';
            }
            $Proconser_rec['ESITOCONSERVAZIONE'] = $CodiceEsito;
            $Proconser_rec['CODICEESITOCONSERVAZIONE'] = $CodiceEsito;
            $Proconser_rec['MESSAGGIOCONSERVAZIONE'] = $MsgEsitiNegativi;

            $this->retEsito = $CodiceEsito;
            $this->retStatus = $Proconser_rec['MESSAGGIOCONSERVAZIONE'];

            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROCONSER', 'ROWID', $Proconser_rec);
            return true;
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in aggiornamento PROUPDATECONSER.<br> " . $e->getMessage();
            return false;
        }
    }

    public function extractRDVXml() {
        //ZIP
        $subPath = uniqid("work-rdv-zip");
        $zipPath = itaLib::createAppsTempPath($subPath);

        $ret = itaZip::Unzip($this->RDVFile, $zipPath);
        if ($ret != 1) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Estrazione file ZIP fallita";
            return false;
        }
        if (!is_dir($zipPath)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Cartella " . pathinfo($this->RDVFile, PATHINFO_FILENAME) . " non trovata";
            return false;
        }
        //
        $bad = array();
        $files = array_diff(scandir($zipPath), $bad);

        $ContentXml = '';
        foreach ($files as $ftmp) {
            $FilePath = $zipPath . '/' . $ftmp;
            $Ext = strtolower(pathinfo($FilePath, PATHINFO_EXTENSION));
            if ($Ext == 'xml') {
                $ContentXml = file_get_contents($FilePath);
                break;
            }
        }
        if (!$ContentXml) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore nella estrazione file xml.";
            return false;
        }

        return $ContentXml;
    }

    public function GetUUIDFromPendingUUID($PendingUUID) {
        if (!$PendingUUID) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'Pending UUID non definito. Impossibile ricavare UUID ';
            return false;
        }
        $IdRequest = $PendingUUID;
        $soapClient = $this->getclient();
        $soapClient->ws_getPendingRequest(array('tem:idRequest' => $IdRequest));
        if (!$soapClient->getResult()) {
            $this->ElaboraErrMessage($soapClient);
            return false;
        }
        $ArrayPending = $soapClient->getResult();

        //ID del Versamento:
        $idPdV = '';
        if ($ArrayPending['OpResult']) {
            $idPdV = $ArrayPending['OpResult'];
        } else {
            //Gestione Errori:
            if ($ArrayPending['OpErrors']) {
                $DescErrore = '';
                foreach ($ArrayPending['OpErrors'] as $Errore) {
                    $DescErrore.= $Errore;
                }
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $DescErrore;
                return false;
            }
            if ($ArrayPending['OpCompleted'] == false) {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = 'Operazioni di versamento documento non ancora completate. IdPdV non ancora generato.';
                return false;
            }
        }

        return $idPdV;
    }

    public function AggiornaUUIDFromPending($UUID, $PendingUUID = '') {
        if (!$PendingUUID) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'Pending UUID da aggiornare non presente.';
            return false;
        }
        if (!$UUID) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'UUID da aggiornare tramite PendingUUID non presente.';
            return false;
        }
        $sql = "SELECT * FROM PROCONSER WHERE PENDINGUUID = '$PendingUUID' AND FLSTORICO = 0";
        $Proconser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if (!$Proconser_rec) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = 'Versamento non trovato per il PendingUUID: ' . $PendingUUID . '.';
            return false;
        }
        try {
            $Proconser_rec['UUIDSIP'] = $UUID;
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROCONSER', 'ROWID', $Proconser_rec);
            return true;
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in aggiornamento PROCONSER.<br> " . $e->getMessage();
            return false;
        }
    }

}
